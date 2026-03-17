<?php

namespace App\Jobs;

use App\Events\EquipmentLocationExportProgress;
use App\Events\EquipmentLocationExportReady;
use App\Models\EquipmentDirectoryItem;
use App\Models\EquipmentLocationExport;
use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Mpdf\Mpdf;

class PrepareEquipmentLocationsExportJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries = 1;
    public int $timeout = 1200;

    public function __construct(public int $exportId)
    {
    }

    public function handle(): void
    {
        Log::info('Starting Equipment Locations Export');
        ini_set('max_execution_time', '300');
        ini_set('pcre.backtrack_limit', '5000000');
        
        $export = EquipmentLocationExport::query()->find($this->exportId);

        if (!$export) {
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
            if (!empty($export->locale)) {
                app()->setLocale($export->locale);
            }

            $items = EquipmentDirectoryItem::with(['directory', 'media'])
                ->latest()
                ->get();

            $exportUser = User::query()->find($export->user_id);

            $html = view('dashboard.equipment_directories.pdf.all_locations', [
                'items' => $items,
                'generatedAt' => now()->format('Y-m-d H:i:s'),
                'generatedByName' => $exportUser->name ?? '-',
                'generatedById' => $exportUser->id ?? '-',
            ])->render();

            $mpdf = new Mpdf([
                'mode' => 'utf-8',
                'format' => 'A4-L',
                'orientation' => 'L',
                'margin_left' => 10,
                'margin_right' => 10,
                'margin_top' => 14,
                'margin_bottom' => 30,
                'dir' => app()->getLocale() === 'ar' ? 'rtl' : 'ltr',
                'default_font' => 'DejaVuSans',
            ]);

            Log::info('Middle Equipment Locations Export');

            $mpdf->shrink_tables_to_fit = 1;
            $mpdf->showImageErrors = false;
            $mpdf->SetFooter('<div style="text-align:center;">{PAGENO} / {nbpg}</div>');          
            
            $chunks = array_values(array_filter(
                explode('<!--PDF_CHUNK_SPLIT-->', $html),
                static fn (string $chunk): bool => trim($chunk) !== ''
            ));
            
            $totalChunks = count($chunks);
            foreach ($chunks as $index => $chunk) {
                try {
                    $mpdf->WriteHTML($chunk);
                } catch (\Mpdf\MpdfImageException $imageException) {
                    Log::warning('Skipping broken image while generating equipment locations summary PDF.', [
                        'export_id' => $this->exportId,
                        'error' => $imageException->getMessage(),
                    ]);

                    $chunkWithoutImages = preg_replace('/<img\b[^>]*>/i', '', $chunk);
                    $mpdf->WriteHTML((string) $chunkWithoutImages);
                }
                if ($export->user_id && $export->token) {
                    event(new EquipmentLocationExportProgress(
                        (int) $export->user_id,
                        (string) $export->token,
                        $index + 1,
                        $totalChunks,
                        round((($index + 1) / $totalChunks) * 100, 2)
                    ));
                }
            }

            $pdfPath = sprintf(
                'exports/equipment_locations/%s/equipment_locations-%s.pdf',
                now()->format('Y/m'),
                $export->token
            );

            Storage::disk('local')->put($pdfPath, $mpdf->Output('', 'S'));

            $export->update([
                'status' => 'ready',
                'pdf_path' => $pdfPath,
                'prepared_at' => now(),
                'finished_at' => now(),
                'error_message' => null,
            ]);

            if ($export->user_id && $export->token) {
                event(new EquipmentLocationExportReady(
                    (int) $export->user_id,
                    (string) $export->token,
                    route('equipment.all-locations.export', ['token' => (string) $export->token])
                ));
            }

            app()->setLocale($originalLocale);

            Log::info('Equipment locations summary export prepared.', [
                'export_id' => $this->exportId,
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