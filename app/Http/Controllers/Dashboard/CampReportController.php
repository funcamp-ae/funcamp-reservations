<?php

namespace App\Http\Controllers\Dashboard;

use App\Helpers\CampReportHelper;
use App\Http\Controllers\Controller;
use App\Jobs\PrepareCampReportSummaryExportJob;
use App\Models\CampReport;
use App\Models\CampReportItem;
use App\Models\CampReportSummaryExport;
use App\Models\NewCampPlan;
use App\Models\Service;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;
use Illuminate\Support\Str;
use Mpdf\Mpdf;

class CampReportController extends Controller
{
    public function index()
    {
        $this->authorize('viewAny', CampReport::class);

        $reports = CampReport::with(['service', 'creator'])
            ->latest()
            ->get();

        return view('dashboard.camp_reports.index', compact('reports'));
    }

    public function create()
    {
        $this->authorize('create', CampReport::class);

        $services = Service::all();
        return view('dashboard.camp_reports.create', compact('services'));
    }

    public function summary(Request $request)
    {
        $rows = $this->buildSummaryRows($request);
        $reportSubtypes = CampReport::with('service')
            ->get()
            ->map(fn (CampReport $report) => $this->formatReportSubtype($report))
            ->filter()
            ->unique()
            ->values();
        $itemNames = CampReportItem::query()->select('item_name')->distinct()->orderBy('item_name')->pluck('item_name');

        return view('dashboard.camp_reports.summary', [
            'rows' => $rows,
            'itemStatuses' => CampReportHelper::itemStatuses(),
            'itemNames' => $itemNames,
            'reportSubtypes' => $reportSubtypes,
            'filters' => $request->only(['report_type', 'report_subtype', 'item_status', 'item_name', 'custom_report_subtype_order']),
        ]);
    }


    /**
     * New Camps page (upcoming camping): record future camps and needed purchases.
     */
    public function upcomingcamping(Request $request)
    {
        $this->authorize('viewAny', CampReport::class);

        $plans = NewCampPlan::query()
            ->latest()
            ->get();
        $editingPlan = null;
        $isCopyMode = false;
        $editPlanId = $request->integer('edit_plan') ?? null;
        $copyPlanId = $request->integer('copy_plan') ?? null;

        if ($copyPlanId > 0) {
            $editingPlan = NewCampPlan::query()->find($copyPlanId) ?? null;
            $isCopyMode = $editingPlan !== null;
        } elseif ($editPlanId > 0) {
            $editingPlan = NewCampPlan::query()->find($editPlanId) ?? null;
        }

        return view('dashboard.camp_reports.new_camps', compact('plans', 'editingPlan', 'isCopyMode'));
    }

    public function storeUpcomingcamping(Request $request)
    {
        $this->authorize('create', CampReport::class);

        $validated = $request->validate([
            'editing_plan_id' => ['nullable', 'integer'],
            'new_camp_name' => ['nullable', 'string', 'max:255'],
            'items' => ['nullable', 'array'],
            'items.*.item_name' => ['nullable', 'string', 'max:255'],
            'items.*.sales_status' => ['nullable', Rule::in(['buy', 'available', 'partial'])],
            'items.*.requested_qty' => ['nullable', 'integer', 'min:0'],
            'items.*.notes' => ['nullable', 'string'],
        ]);

        $items = collect($validated['items'] ?? [])
            ->map(function ($row) {
                $row = is_array($row) ? $row : [];

                return [
                    'item_name' => trim((string) ($row['item_name'] ?? '')),
                    'sales_status' => (string) ($row['sales_status'] ?? ''),
                    'requested_qty' => $row['requested_qty'] ?? null,
                    'notes' => trim((string) ($row['notes'] ?? '')),
                ];
            })
            ->filter(function (array $row) {
                return $row['item_name'] !== '' || $row['sales_status'] !== '' || $row['requested_qty'] !== null || $row['notes'] !== '';
            })
            ->values()
            ->all();

        $editingPlanId = (int) ($validated['editing_plan_id'] ?? 0);
        $plan = null;

        if ($editingPlanId > 0) {
            $plan = NewCampPlan::query()
                ->find($editingPlanId) ;
        }

        if ($plan) {
            $plan->update([
                'new_camp_name' => $validated['new_camp_name'] ?? null,
                'items' => $items,
            ]);
        } else {
            $plan = NewCampPlan::query()->create([
                'new_camp_name' => $validated['new_camp_name'] ?? null,
                'items'         => $items,
                'created_by'    => auth()->id(),
            ]);
        }

        return redirect()
            ->route('camp-reports.new-camps')
            ->with('success', __('dashboard.save_changes'));
    }

    public function destroyUpcomingcamping(NewCampPlan $newCampPlan)
    {
        $newCampPlan->delete();
        return redirect()
            ->route('camp-reports.new-camps')
            ->with('success', __('dashboard.deleted_successfully'));
    }

    public function upcomingcampingPdf(Request $request)
    {
        $this->authorize('viewAny', CampReport::class);

        $campName = $request->string('new_camp_name')->toString();
        $editingPlanId = $request->integer('editing_plan_id') ?: $request->integer('edit_plan');
        $plansForExport = collect();
        $items = collect($request->input('items', []))
            ->map(function ($row) {
                $row = is_array($row) ? $row : [];
                return (object) [
                    'item_name' => trim((string) ($row['item_name'] ?? '')),
                    'sales_status' => (string) ($row['sales_status'] ?? ''),
                    'requested_qty' => $row['requested_qty'] ?? null,
                    'notes' => trim((string) ($row['notes'] ?? '')),
                ];
            })
            ->filter(function ($row) {
                return $row->item_name !== '' || $row->sales_status !== '' || $row->requested_qty !== null || $row->notes !== '';
            })
            ->values();

        // If form is empty, export edited plan or all saved plans for this user.
        if ($items->isEmpty()) {
            $planQuery = NewCampPlan::query()->oldest();

            if ($editingPlanId > 0) {
                $plan = (clone $planQuery)->whereKey($editingPlanId)->first();

                if ($plan) {
                    if ($campName === '') {
                        $campName = (string) ($plan->new_camp_name ?? '');
                    }

                    $items = collect($plan->items ?? [])
                        ->map(function ($row) {
                            $row = is_array($row) ? $row : [];

                            return (object) [
                                'item_name' => trim((string) ($row['item_name'] ?? '')),
                                'sales_status' => (string) ($row['sales_status'] ?? ''),
                                'requested_qty' => $row['requested_qty'] ?? null,
                                'notes' => trim((string) ($row['notes'] ?? '')),
                            ];
                        })
                        ->filter(function ($row) {
                            return $row->item_name !== '' || $row->sales_status !== '' || $row->requested_qty !== null || $row->notes !== '';
                        })
                        ->values();
                }
            } else {
                $plansForExport = (clone $planQuery)
                    ->latest()
                    ->get()
                    ->map(function (NewCampPlan $plan) {
                        $planItems = collect($plan->items ?? [])
                            ->map(function ($row) {
                                $row = is_array($row) ? $row : [];

                                return (object) [
                                    'item_name' => trim((string) ($row['item_name'] ?? '')),
                                    'sales_status' => (string) ($row['sales_status'] ?? ''),
                                    'requested_qty' => $row['requested_qty'] ?? null,
                                    'notes' => trim((string) ($row['notes'] ?? '')),
                                ];
                            })
                            ->filter(function ($row) {
                                return $row->item_name !== '' || $row->sales_status !== '' || $row->requested_qty !== null || $row->notes !== '';
                            })
                            ->values();

                        return (object) [
                            'camp_name' => (string) ($plan->new_camp_name ?? ''),
                            'items' => $planItems,
                        ];
                    })
                    ->filter(fn ($plan) => $plan->items->isNotEmpty())
                    ->values();
            }
        }

        $html = view('dashboard.camp_reports.new_camps_export', [
            'campName' => $campName,
            'items' => $items,
            'plansForExport' => $plansForExport,
            'generatedAt' => now()->format('Y-m-d H:i:s'),
            'generatedByName' => auth()->user()?->name ?? '-',
            'generatedById' => auth()->id() ?? '-',
        ])->render();

        $mpdf = new Mpdf([
            'mode'           => 'utf-8',
            'format'         => 'A4',
            'orientation'    => 'P',
            'margin_left'    => 12,
            'margin_right'   => 12,
            'margin_top'     => 14,
            'margin_bottom'  => 14,
            'dir'            => app()->getLocale() === 'ar' ? 'rtl' : 'ltr',
            'default_font'   => 'DejaVuSans',
        ]);
        $mpdf->SetFooter('<div style="text-align:center;">{PAGENO} / {nbpg}</div>');          
        $mpdf->WriteHTML($html);

        $filename = __('dashboard.camp_reports_new_camps') . '-' . date('Y-m-d') . '.pdf';
        return $mpdf->Output($filename, 'D');
    }

    public function exportSummaryPdf(Request $request)
    {
        $token = trim((string) $request->query('token', ''));
        $preparedExport = $token
            ? CampReportSummaryExport::query()
                ->where('token', $token)
                ->where('user_id', auth()->id())
                ->first()
            : CampReportSummaryExport::query()
                ->where('user_id', auth()->id())
                ->where('status', 'ready')
                ->latest()
                ->first();

        if (! $preparedExport || $preparedExport->status !== 'ready' || empty($preparedExport->pdf_path) || ! Storage::disk('local')->exists($preparedExport->pdf_path)) {
            return back()->with('error', 'Please try again in a moment with export PDF.')->withInput();
        }

        return Storage::disk('local')->download(
            $preparedExport->pdf_path,
            __('dashboard.camp_reports_summary') . '-' . date('Y-m-d') . '.pdf'
        );

    }

    public function prepareSummaryExport(Request $request)
    {
        $incoming = $request->only(['report_type', 'report_subtype', 'item_status', 'item_name']);

        // Normalize custom_report_subtype_order: accept array or JSON string
        $custom = $request->input('custom_report_subtype_order');
        if (is_string($custom)) {
            $decoded = json_decode($custom, true);
            $custom = is_array($decoded) ? $decoded : [$custom];
        }
        if (!is_array($custom)) {
            $custom = [];
        }

        $filters = $incoming;
        $filters['custom_report_subtype_order'] = array_values($custom);

        $export = CampReportSummaryExport::query()->create([
            'user_id' => auth()->id(),
            'token' => (string) Str::uuid(),
            'filters' => $filters,
            'rows' => [],
            'status' => 'pending',
            'pdf_path' => null,
            'error_message' => null,
            'locale' => app()->getLocale(),
            'prepared_at' => null,
            'started_at' => null,
            'finished_at' => null,
            'expires_at' => now()->addHours(2),
        ]);
        PrepareCampReportSummaryExportJob::dispatch($export->id);
        return response()->json([
            'success' => true,
            'message' => 'Preparing data ... ',
            'token' => $export->token,
            'status' => $export->status,
            'status_url' => route('camp-reports.summary.export.status', ['token' => $export->token]),
            'export_url' => route('camp-reports.summary.export', ['token' => $export->token]),
        ]);
    }

    public function summaryExportStatus(Request $request)
    {
        $token = trim((string) $request->query('token', ''));

        if ($token === '') {
            return response()->json([
                'success' => false,
                'message' => 'Missing export token.',
            ], 422);
        }

        $export = CampReportSummaryExport::query()
            ->where('token', $token)
            ->where('user_id', auth()->id())
            ->first();

        if (! $export) {
            return response()->json([
                'success' => false,
                'message' => 'Export request not found.',
            ], 404);
        }

        return response()->json([
            'success' => true,
            'status' => $export->status,
            'ready' => $export->status === 'ready',
            'failed' => $export->status === 'failed',
            'error_message' => $export->error_message,
            'export_url' => route('camp-reports.summary.export', ['token' => $export->token]),
        ]);
    }

    public function store(Request $request)
    {
        $this->authorize('create', CampReport::class);

        [$imgMimes, $imgExts, $audMimes, $audExts, $vidMimes, $vidExts] = $this->allowedLists();

        $validated = $request->validate([
            'report_date'            => ['required','date'],
            'report_type'            => ['required', Rule::in(['camp', 'general'])],
            'service_id'             => ['nullable', Rule::exists('services','id')],
            'custom_report_type'     => ['nullable','string','max:255'],
            'camp_name'              => ['nullable','string','max:255'],
            'additional_notes'       => ['nullable','string'],
            'general_notes'          => ['nullable','string'],

            'items'                  => ['nullable','array','min:1'],
            'items.*.item_name'      => ['required','string','max:255'],
            'items.*.notes'          => ['nullable','string'],
            'items.*.item_status'    => ['nullable','string', Rule::in(CampReportHelper::itemStatusKeys())],
            'items.*.item_status_other' => ['nullable','string'],
            'items.*.damaged_count'  => ['nullable','integer','min:0'],

            // صور
            'items.*.photos'         => ['nullable','array'],
            'items.*.photos.*'       => ['file', $this->mimeOrExtRule($imgMimes, $imgExts)],

            // صوت
            'items.*.audios'         => ['nullable','array'],
            'items.*.audios.*'       => ['file', $this->mimeOrExtRule($audMimes, $audExts)],

            // فيديو
            'items.*.videos'         => ['nullable','array'],
            'items.*.videos.*'       => ['file', $this->mimeOrExtRule($vidMimes, $vidExts)],

            // مفاتيح الرفع المُجزّأ (FilePond)
            'items.*.uploaded_videos'    => ['nullable','array'],
            'items.*.uploaded_videos.*'  => ['string'],

            // فلاق مسح القديم
            'items.*.remove_photos'  => ['nullable','boolean'],
            'items.*.remove_audios'  => ['nullable','boolean'],
            'items.*.remove_videos'  => ['nullable','boolean'],
        ]);

        $report = CampReport::create([
            'report_date'   => $validated['report_date'],
            'report_type'   => $validated['report_type'],
            'service_id'    => $validated['service_id'] ?? null,
            'custom_report_type' => $validated['custom_report_type'] ?? null,
            'camp_name'     => $validated['camp_name'] ?? null,
            'general_notes' => $validated['additional_notes'] ?? $validated['general_notes'] ?? null,
            'created_by'    => auth()->id(),
        ]);

        if (!empty($validated['items'])) {
            foreach ($validated['items'] as $itemData) {
                /** @var \App\Models\CampReportItem $item */
                $item = $report->items()->create($this->prepareItemPayload($itemData));

                $this->handleItemAttachments($item, $itemData);
            }
        }

        return redirect()->route('camp-reports.index')
            ->with('success', __('dashboard.report_created'));
    }

    public function show(CampReport $campReport)
    {
        $this->authorize('view', $campReport);

        $campReport->load(['service','creator','items.media']);
        return view('dashboard.camp_reports.show', compact('campReport'));
    }

    public function edit(CampReport $campReport)
    {
        $this->authorize('update', $campReport);

        $services = Service::all();
        $campReport->load(['items.media']);
        return view('dashboard.camp_reports.create', compact('campReport', 'services'));
    }

    public function update(Request $request, CampReport $campReport)
    {
        $this->authorize('update', $campReport);

        [$imgMimes, $imgExts, $audMimes, $audExts, $vidMimes, $vidExts] = $this->allowedLists();

        $validated = $request->validate([
            'report_date'            => ['required','date'],
            'report_type'            => ['required', Rule::in(['camp', 'general'])],
            'service_id'             => ['nullable', Rule::exists('services','id')],
            'custom_report_type'     => ['nullable','string','max:255'],
            'camp_name'              => ['nullable','string','max:255'],
            'additional_notes'       => ['nullable','string'],
            'general_notes'          => ['nullable','string'],

            'items'                  => ['nullable','array','min:1'],
            'items.*.id'             => ['nullable', Rule::exists('camp_report_items','id')->where('camp_report_id', $campReport->id)],
            'items.*.item_name'      => ['required','string','max:255'],
            'items.*.notes'          => ['nullable','string'],
            'items.*.item_status'    => ['nullable','string', Rule::in(CampReportHelper::itemStatusKeys())],
            'items.*.item_status_other' => ['nullable','string'],
            'items.*.damaged_count'  => ['nullable','integer','min:0'],

            'items.*.photos'         => ['nullable','array'],
            'items.*.photos.*'       => ['file', $this->mimeOrExtRule($imgMimes, $imgExts)],

            'items.*.audios'         => ['nullable','array'],
            'items.*.audios.*'       => ['file', $this->mimeOrExtRule($audMimes, $audExts)],

            'items.*.videos'         => ['nullable','array'],
            'items.*.videos.*'       => ['file', $this->mimeOrExtRule($vidMimes, $vidExts)],

            'items.*.uploaded_videos'    => ['nullable','array'],
            'items.*.uploaded_videos.*'  => ['string'],

            'deleted_items'          => ['nullable','array'],
            'deleted_items.*'        => [Rule::exists('camp_report_items','id')->where('camp_report_id', $campReport->id)],

            'items.*.remove_photos'  => ['nullable','boolean'],
            'items.*.remove_audios'  => ['nullable','boolean'],
            'items.*.remove_videos'  => ['nullable','boolean'],
        ]);

        $campReport->update([
            'report_date'   => $validated['report_date'],
            'report_type'   => $validated['report_type'],
            'service_id'    => $validated['service_id'] ?? null,
            'custom_report_type' => $validated['custom_report_type'] ?? null,
            'camp_name'     => $validated['camp_name'] ?? null,
            'general_notes' => $validated['additional_notes'] ?? $validated['general_notes'] ?? null,
        ]);

        // حذف العناصر المزالة
        if (!empty($validated['deleted_items'])) {
            $itemsToDelete = $campReport->items()->whereIn('id', $validated['deleted_items'])->get();
            foreach ($itemsToDelete as $item) {
                $this->deleteItemAttachments($item);
                $item->delete();
            }
        }

        // تعديل/إضافة العناصر
        if (!empty($validated['items'])) {
            foreach ($validated['items'] as $itemData) {
                if (!empty($itemData['id'])) {
                    /** @var \App\Models\CampReportItem $item */
                    $item = $campReport->items()->findOrFail($itemData['id']);
                    $item->update($this->prepareItemPayload($itemData));
                    $this->handleItemAttachments($item, $itemData);
                } else {
                    $item = $campReport->items()->create($this->prepareItemPayload($itemData));
                    $this->handleItemAttachments($item, $itemData);
                }
            }
        }

        return redirect()->route('camp-reports.index')
            ->with('success', __('dashboard.report_updated'));
    }

    public function destroy(CampReport $campReport)
    {
        $this->authorize('delete', $campReport);

        $campReport->load('items.media');

        foreach ($campReport->items as $item) {
            $this->deleteItemAttachments($item);

            // حذف مجلد العنصر من قرص hetzner لو موجود (قد يبقى فارغًا بعد حذف الملفات)
            $itemFolder = "camp-reports/{$item->id}";
            if (Storage::disk('hetzner')->exists($itemFolder)) {
                Storage::disk('hetzner')->deleteDirectory($itemFolder);
            }
        }
        $campReport->forceDelete();


        return back()->with('success', __('dashboard.report_deleted'));
    }

    /**
     * إدارة مرفقات عنصر واحد (إضافة/حذف/استبدال)
     */
    protected function handleItemAttachments(CampReportItem $item, array $itemData): void
    {
        $disk = Storage::disk('hetzner');

        // مسار أساسي يتضمن رافع الملف + التاريخ
        $userId   = auth()->id() ?? 0;
        $userName = optional(auth()->user())->name ?? 'guest';
        $userSlug = 'u' . $userId . '-' . Str::slug($userName, '-');
        $baseRoot = "uploads/camp-reports/{$userSlug}/" . date('Y/m') . "/{$item->id}";

        // 1) حذف القديم حسب الفلاق
        if (!empty($itemData['remove_photos'])) {
            $this->deleteType($item, 'photo', $disk);
        }
        if (!empty($itemData['remove_audios'])) {
            $this->deleteType($item, 'audio', $disk);
        }
        if (!empty($itemData['remove_videos'])) {
            $this->deleteType($item, 'video', $disk);
        }

        // 2) رفع جديد (صور -> type=photo)
        if (!empty($itemData['photos']) && is_array($itemData['photos'])) {
            foreach ($itemData['photos'] as $file) {
                $path = $file->store("{$baseRoot}/images", 'hetzner');
                $item->media()->create([
                    'path' => $path,
                    'type' => 'photo',
                    'full_url' => $this->resolveMediaFullUrl($path),
                ]);
            }
        }

        // 3) رفع جديد (صوت)
        if (!empty($itemData['audios']) && is_array($itemData['audios'])) {
            foreach ($itemData['audios'] as $file) {
                $path = $file->store("{$baseRoot}/audios", 'hetzner');
                $item->media()->create([
                    'path' => $path,
                    'type' => 'audio',
                    'full_url' => $this->resolveMediaFullUrl($path),
                ]);
            }
        }

        // 4) رفع جديد (فيديو)
        if (!empty($itemData['videos']) && is_array($itemData['videos'])) {
            foreach ($itemData['videos'] as $file) {
                $path = $file->store("{$baseRoot}/videos", 'hetzner');
                $item->media()->create([
                    'path' => $path,
                    'type' => 'video',
                    'full_url' => $this->resolveMediaFullUrl($path),
                ]);
            }
        }

        // 5) فيديوهات مرفوعة عبر FilePond (multipart) — حفظ المفتاح كما هو
        if (!empty($itemData['uploaded_videos']) && is_array($itemData['uploaded_videos'])) {
            $keys = collect($itemData['uploaded_videos'])->filter()->unique()->values();
            foreach ($keys as $key) {
                $item->media()->create([
                    'path' => $key,
                    'type' => 'video',
                    'full_url' => $this->resolveMediaFullUrl($key),
                ]);
            }
        }
    }

    protected function resolveMediaFullUrl(string $path): ?string
    {
        if ($path === '') {
            return null;
        }

        if (preg_match('/^https?:\/\//i', $path)) {
            return $path;
        }

        try {
            return Storage::disk('hetzner')->url($path);
        } catch (\Throwable $e) {
            return null;
        }
    }

    /**
     * حذف كل مرفقات عنصر
     */
    protected function deleteItemAttachments(CampReportItem $item): void
    {
        $disk = Storage::disk('hetzner');

        foreach ($item->media as $media) {
            try {
                $disk->delete($media->path);
            } catch (\Throwable $e) {
                // الملف قد لا يكون موجودًا — تجاهل
            }
            $media->delete();
        }
    }

    /**
     * حذف مرفقات نوع معيّن لعنصر
     */
    protected function deleteType(CampReportItem $item, string $type, $disk): void
    {
        $medias = $item->media()->where('type', $type)->get();
        foreach ($medias as $m) {
            try {
                $disk->delete($m->path);
            } catch (\Throwable $e) {}
            $m->delete();
        }
    }

    protected function prepareItemPayload(array $itemData): array
    {
        $itemStatus = $itemData['item_status'] ?? null;

        return [
            'item_name' => $itemData['item_name'],
            'notes' => $itemData['notes'] ?? null,
            'item_status' => $itemStatus,
            'item_status_other' => $itemStatus === 'other' ? ($itemData['item_status_other'] ?? null) : null,
            'damaged_count' => $itemData['damaged_count'] ?? null,
        ];
    }


    protected function buildSummaryRows(Request $request, bool $forExport = false)
    {
        $statusLabels = CampReportHelper::itemStatuses();
        $items = CampReportItem::with(['report.service', 'media'])->latest()->get();

        return $items
            ->map(function (CampReportItem $item) use ($statusLabels, $forExport) {
                $report = $item->report;
                $photos = $item->media->where('type', 'photo')->values();

                $photoPaths = $photos
                    ->pluck('path')
                    ->values()
                    ->all();

                $photoUrls = $photos
                    ->map(fn ($photo) => $photo->full_url ?: Storage::disk('hetzner')->url($photo->path))
                    ->values()
                    ->all();

                $photoBase64 = [];
                if ($forExport) {
                    foreach ($photos as $photo) {
                        try {
                            if (Storage::disk('hetzner')->exists($photo->path)) {
                                $mime = Storage::disk('hetzner')->mimeType($photo->path);
                                $photoBase64[] = 'data:' . $mime . ';base64,' . base64_encode(Storage::disk('hetzner')->get($photo->path));
                            }
                        } catch (\Throwable $e) {
                        }
                    }
                }

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
                    'photo_base64' => $photoBase64,
                ];
            })
            ->filter(function ($row) use ($request) {
                if ($request->filled('report_type') && $row->report_type !== $request->string('report_type')->toString()) {
                    return false;
                }

                if ($request->filled('report_subtype') && $row->report_subtype !== $request->string('report_subtype')->toString()) {
                    return false;
                }

                if ($request->filled('item_status') && $row->item_status !== $request->string('item_status')->toString()) {
                    return false;
                }

                if ($request->filled('item_name') && ! str_contains(mb_strtolower($row->item_name), mb_strtolower($request->string('item_name')->toString()))) {
                    return false;
                }

                return true;
            })
            ->values();
    }

    protected function formatReportSubtype(CampReport $report): ?string
    {
        if ($report->report_type === 'camp') {
            return $report->service->name;
        }

        if ($report->report_type === 'general') {
            return $report->custom_report_type;
        }

        return $report->service->name ?? $report->custom_report_type;
    }

    public function exportPdf()
    {
        $reports = CampReport::with(['service', 'creator', 'items.media'])
            ->latest()
            ->get();

        // تضمين أول صورة لكل عنصر في الـ PDF إن وُجدت
        foreach ($reports as $report) {
            foreach ($report->items as $item) {
                $img = $item->media->firstWhere('type','photo');
                if ($img && Storage::disk('hetzner')->exists($img->path)) {
                    try {
                        $imageData = Storage::disk('hetzner')->get($img->path);
                        $item->photo_base64 = base64_encode($imageData);
                    } catch (\Throwable $e) {
                        $item->photo_base64 = null;
                    }
                }
            }
        }

        $html = view('dashboard.camp_reports.export', compact('reports'))->render();

        $mpdf = new Mpdf([
            'mode'           => 'utf-8',
            'format'         => 'A4',
            'orientation'    => 'P',
            'margin_left'    => 15,
            'margin_right'   => 15,
            'margin_top'     => 16,
            'margin_bottom'  => 16,
            'margin_header'  => 9,
            'margin_footer'  => 9,
            'dir'            => app()->getLocale() === 'ar' ? 'rtl' : 'ltr',
            'default_font'   => 'DejaVuSans',
        ]);

        $mpdf->showImageErrors = true;
        $mpdf->WriteHTML($html);

        $filename = __('dashboard.camp_reports') . "-" . date('Y-m-d') . ".pdf";
        return $mpdf->Output($filename, 'D');
    }

    /**
     * قاعدة تحقق: نقبل الملف إذا كان الـ MIME ضمن المسموح أو الامتداد ضمن المسموح
     */
    protected function mimeOrExtRule(array $mimes, array $exts): \Closure
    {
        return function ($attribute, $file, $fail) use ($mimes, $exts) {
            $mime = explode(';', $file->getClientMimeType() ?: $file->getMimeType() ?: '')[0];
            $ext  = strtolower($file->getClientOriginalExtension());

            $mimeOk = in_array($mime, $mimes, true);
            $extOk  = in_array($ext,  $exts,  true);

            if (!$mimeOk && !$extOk) {
                // نعرض قائمة الـ MIME المسموح بها (كمرجع)
                $fail(__('validation.mimetypes', ['values' => implode(', ', $mimes)]));
            }
        };
    }

    /**
     * اللوائح المسموح بها (MIME + الامتدادات) لصور/صوت/فيديو
     */
    protected function allowedLists(): array
    {
        $imgMimes = [
            'image/jpeg','image/png','image/webp','image/gif',
            'image/heic','image/heif','image/bmp','image/tiff',
        ];
        $imgExts  = ['jpg','jpeg','png','webp','gif','heic','heif','bmp','tif','tiff'];

        $audMimes = [
            'audio/mpeg','audio/mp3',
            'audio/wav','audio/x-wav','audio/vnd.wave',
            'audio/ogg','audio/opus','application/ogg',
            'audio/webm',
            'audio/aac','audio/x-aac','audio/aacp',
            'audio/mp4','audio/mp4a-latm','audio/x-m4a',
            'audio/3gpp','audio/3gpp2',
            'audio/amr','audio/x-amr',
            'audio/aiff','audio/x-aiff','audio/x-caf',
            'audio/flac',
            'application/octet-stream', // بعض الأجهزة ترسله في التسجيل
        ];
        $audExts  = [
            'mp3','wav','ogg','opus','webm','aac','m4a','mp4',
            '3gp','3gpp','amr','aiff','aif','caf','flac',
        ];

        $vidMimes = [
            'video/mp4','video/quicktime',
            'video/webm',
            'video/x-matroska',
            'video/x-flv',
            'video/3gpp','video/3gpp2',
            'video/avi',
            'video/mpeg','video/mpg',
            'video/x-ms-wmv',
            'video/x-m4v',
            'video/hevc',
            'application/octet-stream', // fallback في بعض المتصفحات
        ];
        $vidExts  = ['mp4','mov','webm','mkv','flv','3gp','avi','mpeg','mpg','wmv','m4v'];

        return [$imgMimes, $imgExts, $audMimes, $audExts, $vidMimes, $vidExts];
    }
}
