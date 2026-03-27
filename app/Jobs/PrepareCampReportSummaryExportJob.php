<?php

namespace App\Jobs;

use App\Helpers\CampReportHelper;
use App\Events\CampReportSummaryExportReady;
use App\Events\CampReportSummaryExportProgressUpdated;
use App\Models\CampReport;
use App\Models\CampReportItem;
use App\Models\CampReportSummaryExport;
use App\Models\User;
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
        \Log::info('Starting ');
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

            $photoCounter = 0;

            $rows = $this->buildSummaryRows($export);
            $exportUser = User::query()->find($export->user_id);
            $html = view('dashboard.camp_reports.summary_export', [
                'rows' => collect($rows)->map(fn ($row) => (object) $row),
                'generatedAt' => now()->format('Y-m-d H:i:s'),
                'generatedByName' => $exportUser->name ?? '-',
                'generatedById' => $exportUser->id ,
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
        \Log::info('middle ');

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
                    Log::warning('Skipping broken image while generating camp report summary PDF.', [
                        'export_id' => $this->exportId,
                        'error' => $imageException->getMessage(),
                    ]);

                    // Retry this chunk without <img> tags so the export can finish.
                    $chunkWithoutImages = preg_replace('/<img\b[^>]*>/i', '', $chunk);
                    $mpdf->WriteHTML((string) $chunkWithoutImages);
                }

                if ($export->user_id && $export->token) {
                    event(new CampReportSummaryExportProgressUpdated(
                        (int) $export->user_id,
                        (string) $export->token,
                        $index + 1,
                        $totalChunks
                    ));
                }
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

    private function buildSummaryRows(CampReportSummaryExport $export)
    {
        $filters = is_array($export->filters) ? $export->filters : [];
        $customOrder = $filters['custom_report_subtype_order'] ?? [];

        // If filter was saved as JSON string, decode it
        if (is_string($customOrder)) {
            $decoded = json_decode($customOrder, true);
            $customOrder = is_array($decoded) ? $decoded : [$customOrder];
        }

        // Build position map: subtype => position index (if an array was provided)
        $positionMap = [];
        if (is_array($customOrder)) {
            foreach ($customOrder as $pos => $id) {
                $positionMap[(string) $id] = $pos;
            }
        }
        $statusLabels = CampReportHelper::itemStatuses();

        return CampReportItem::with(['report.service', 'media'])
            ->latest()
            ->get()
            ->map(function (CampReportItem $item) use ($statusLabels) {
                $report = $item->report;
                $photos = $item->media->where('type', 'photo')->values();
                $photoPaths = $photos->pluck('path')->values()->all();
                $photoUrls = $photos
                    ->map(fn ($photo) => $photo->full_url ?: env('AWS_URL') . '/' . $photo->path)
                    ->filter()
                    ->unique()
                    ->values();

                $itemStatus = $item->item_status;
                $itemStatusLabel = $itemStatus && isset($statusLabels[$itemStatus]) ? __($statusLabels[$itemStatus]) : '-';

                if ($itemStatus === 'other' && $item->item_status_other) {
                    $itemStatusLabel = $itemStatusLabel . ' - ' . $item->item_status_other;
                }

                return (object) [
                    'id' => $item->id,
                    'report_id' => $report?->id,
                    'report_type' => $report?->report_type,
                    'report_type_label' => $report?->report_type ? __('dashboard.' . $report->report_type) : '-',
                    'report_subtype' => $report ? $this->formatReportSubtype($report) : '-',
                    'camp_name' => $report?->service?->name ?? '-',
                    'item_name' => $item->item_name,
                    'item_status' => $itemStatus,
                    'item_status_label' => $itemStatusLabel,
                    'damaged_count' => $item->damaged_count,
                    'notes' => $item->notes,
                    'photo_paths' => $photoPaths,
                    'photo_urls' => $photoUrls,
                    'photo_base64' => [],
                ];
            })
            ->filter(function ($row) use ($filters) {
                $reportType = trim((string) ($filters['report_type'] ?? ''));
                $reportSubtype = trim((string) ($filters['report_subtype'] ?? ''));
                $itemStatus = trim((string) ($filters['item_status'] ?? ''));
                $itemName = mb_strtolower(trim((string) ($filters['item_name'] ?? '')));

                if ($reportType !== '' && $row->report_type !== $reportType) {
                    return false;
                }

                if ($reportSubtype !== '' && $row->report_subtype !== $reportSubtype) {
                    return false;
                }

                if ($itemStatus !== '' && $row->item_status !== $itemStatus) {
                    return false;
                }

                if ($itemName !== '' && ! str_contains(mb_strtolower((string) $row->item_name), $itemName)) {
                    return false;
                }

                return true;
            })
            // If a custom ordering was provided as an array, apply it
            ->when(!empty($positionMap), function ($collection) use ($positionMap) {
                return $collection->sortBy(function ($row) use ($positionMap) {
                    return $positionMap[(string) $row->report_subtype] ?? PHP_INT_MAX;
                });
            })
            ->values();
    }

    private function formatReportSubtype(CampReport $report): ?string
    {
        if ($report->report_type === 'camp') {
            return $report->service?->name;
        }

        if ($report->report_type === 'general') {
            return $report->custom_report_type;
        }

        return $report->service?->name ?? $report->custom_report_type;
    }
}
