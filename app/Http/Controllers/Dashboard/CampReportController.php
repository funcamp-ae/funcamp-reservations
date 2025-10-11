<?php

namespace App\Http\Controllers\Dashboard;

use App\Http\Controllers\Controller;
use App\Models\CampReport;
use App\Models\CampReportItem;
use App\Models\Service;
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

    public function store(Request $request)
    {
        $this->authorize('create', CampReport::class);

        [$imgMimes, $imgExts, $audMimes, $audExts, $vidMimes, $vidExts] = $this->allowedLists();

        $validated = $request->validate([
            'report_date'            => ['required','date'],
            'service_id'             => ['nullable', Rule::exists('services','id')],
            'camp_name'              => ['nullable','string','max:255'],
            'general_notes'          => ['nullable','string'],

            'items'                  => ['nullable','array','min:1'],
            'items.*.item_name'      => ['required','string','max:255'],
            'items.*.notes'          => ['nullable','string'],

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
            'service_id'    => $validated['service_id'] ?? null,
            'camp_name'     => $validated['camp_name'] ?? null,
            'general_notes' => $validated['general_notes'] ?? null,
            'created_by'    => auth()->id(),
        ]);

        if (!empty($validated['items'])) {
            foreach ($validated['items'] as $itemData) {
                /** @var \App\Models\CampReportItem $item */
                $item = $report->items()->create([
                    'item_name' => $itemData['item_name'],
                    'notes'     => $itemData['notes'] ?? null,
                ]);

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
            'service_id'             => ['nullable', Rule::exists('services','id')],
            'camp_name'              => ['nullable','string','max:255'],
            'general_notes'          => ['nullable','string'],

            'items'                  => ['nullable','array','min:1'],
            'items.*.id'             => ['nullable', Rule::exists('camp_report_items','id')->where('camp_report_id', $campReport->id)],
            'items.*.item_name'      => ['required','string','max:255'],
            'items.*.notes'          => ['nullable','string'],

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
            'service_id'    => $validated['service_id'] ?? null,
            'camp_name'     => $validated['camp_name'] ?? null,
            'general_notes' => $validated['general_notes'] ?? null,
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
                    $item->update([
                        'item_name' => $itemData['item_name'],
                        'notes'     => $itemData['notes'] ?? null,
                    ]);
                    $this->handleItemAttachments($item, $itemData);
                } else {
                    $item = $campReport->items()->create([
                        'item_name' => $itemData['item_name'],
                        'notes'     => $itemData['notes'] ?? null,
                    ]);
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

        $campReport->delete();

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
                ]);
            }
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
