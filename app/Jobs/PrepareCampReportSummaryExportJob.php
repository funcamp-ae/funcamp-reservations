<?php

namespace App\Jobs;

use App\Events\CampReportSummaryExportReady;
use App\Models\CampReportSummaryExport;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Mpdf\Mpdf;

class PrepareCampReportSummaryExportJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries = 1;
    public int $timeout = 1200;

    public function __construct(public int $exportId)
    {
    }

    public function handle(): void
    {
        ini_set('max_execution_time', '300');
        ini_set('pcre.backtrack_limit', '5000000');
        $export = CampReportSummaryExport::query()->find($this->exportId);

        if (! $export) {
            return;
        }

        $export->update([
            'status' => 'processing',
            'pdf_path' => null,
            'started_at' => now(),
            'error_message' => null,
        ]);

        $originalLocale = app()->getLocale();

        try {
            if (! empty($export->locale)) {
                app()->setLocale($export->locale);
            }

            $encodedPhotosCount = 0;
            $photoCounter = 0;

            $rows = collect($export->rows ?? [])
                ->map(fn ($row) => (object) $row)
                ->map(function ($row) use (&$encodedPhotosCount, &$photoCounter) {
                    if (!empty($row->photo_base64)) {
                        return $row;
                    }

                    $row->photo_base64 = [];
                    $photoPaths = (array) ($row->photo_paths ?? []);

                    foreach ($photoPaths as $path) {
                        $photoCounter++;

                        // Keep the queue DB connection alive in long-running exports.
                        if ($photoCounter % 20 === 0) {
                            $this->keepDatabaseConnectionAlive();
                        }

                        try {
                            if (!Storage::disk('hetzner')->exists($path)) {
                                continue;
                            }

                            $mime = Storage::disk('hetzner')->mimeType($path) ?: 'image/jpeg';
                            $row->photo_base64[] = 'data:' . $mime . ';base64,' . base64_encode(Storage::disk('hetzner')->get($path));
                            $encodedPhotosCount++;
                        } catch (\Throwable $e) {
                            Log::warning('Failed to encode export photo.', [
                                'export_id' => $this->exportId,
                                'path' => $path,
                                'error' => $e->getMessage(),
                            ]);
                        }
                    }

                    return $row;
                })
                ->map(fn ($row) => (array) $row)
                ->values()
                ->all();
            $html = view('dashboard.camp_reports.summary_export', [
                'rows' => collect($rows)->map(fn ($row) => (object) $row),
            ])->render();

            $mpdf = new Mpdf([
                'mode' => 'utf-8',
                'format' => 'A4-L',
                'orientation' => 'L',
                'margin_left' => 10,
                'margin_right' => 10,
                'margin_top' => 14,
                'margin_bottom' => 14,
                'dir' => app()->getLocale() === 'ar' ? 'rtl' : 'ltr',
                'default_font' => 'DejaVuSans',
            ]);

            $mpdf->shrink_tables_to_fit = 1;
            $mpdf->showImageErrors = true;
            $chunks = explode('<!--PDF_CHUNK_SPLIT-->', $html);
            foreach ($chunks as $chunk) {
                if (trim($chunk) === '') {
                    continue;
                }

                $mpdf->WriteHTML($chunk);
            }

            $pdfPath = sprintf(
                'exports/camp-summary/%s/camp-report-summary-%s.pdf',
                now()->format('Y/m'),
                $export->token
            );

            Storage::disk('local')->put($pdfPath, $mpdf->Output('', 'S'));

            $export->update([
                'rows' => [],
                'status' => 'ready',
                'pdf_path' => $pdfPath,
                'prepared_at' => now(),
                'finished_at' => now(),
                'error_message' => null,
            ]);

            if ($export->user_id && $export->token) {
                event(new CampReportSummaryExportReady(
                    (int) $export->user_id,
                    (string) $export->token,
                    route('camp-reports.summary.export', ['token' => (string) $export->token])
                ));
            }

            app()->setLocale($originalLocale);

            Log::info('Camp report summary export prepared.', [
                'export_id' => $this->exportId,
                'encoded_photos' => $encodedPhotosCount,
                'pdf_path' => $pdfPath,
            ]);
        } catch (\Throwable $e) {
            app()->setLocale($originalLocale);
            $this->keepDatabaseConnectionAlive();

            $export->update([
                'status' => 'failed',
                'pdf_path' => null,
                'finished_at' => now(),
                'error_message' => mb_substr($e->getMessage(), 0, 1900),
            ]);

            throw $e;
        }
    }

    private function keepDatabaseConnectionAlive(): void
    {
        try {
            DB::connection()->select('select 1');
        } catch (\Throwable $e) {
            DB::purge();
            DB::reconnect();
        }
    }
}
