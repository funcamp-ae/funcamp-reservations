<?php

namespace App\Http\Controllers\Dashboard;

use App\Http\Controllers\Controller;
use App\Models\EquipmentDirectory;
use App\Models\EquipmentDirectoryItem;
use App\Models\EquipmentDirectoryMedia;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Mpdf\Mpdf;
use App\Models\EquipmentLocationExport;
use App\Jobs\PrepareEquipmentLocationsExportJob;

class EquipmentDirectoryController extends Controller
{
    // ====== Main Directories ======
    public function index()
    {
        $directories = EquipmentDirectory::with(['creator', 'items'])
            ->latest()
            ->get();

        return view('dashboard.equipment_directories.index', compact('directories'));
    }

    public function create()
    {
        return view('dashboard.equipment_directories.create');
    }

    public function store(Request $request)
    {
        $request->validate([
            'name'      => ['required', 'string', 'max:255'],
            'is_active' => ['boolean'],
        ]);

        EquipmentDirectory::create([
            'name'       => $request->name,
            'is_active'  => $request->is_active ?? true,
            'created_by' => auth()->id(),
        ]);

        return redirect()->route('equipment-directories.index')
            ->with('success', __('Directory created successfully'));
    }

    public function edit(EquipmentDirectory $equipmentDirectory)
    {
        return view('dashboard.equipment_directories.create', compact('equipmentDirectory'));
    }

    public function update(Request $request, EquipmentDirectory $equipmentDirectory)
    {
        $request->validate([
            'name'      => ['required', 'string', 'max:255'],
            'is_active' => ['boolean'],
        ]);

        $equipmentDirectory->update([
            'name'      => $request->name,
            'is_active' => $request->is_active ?? $equipmentDirectory->is_active,
        ]);

        return redirect()->route('equipment-directories.index')
            ->with('success', __('Directory updated successfully'));
    }

    public function destroy(EquipmentDirectory $equipmentDirectory)
    {
        try {
            DB::transaction(function () use ($equipmentDirectory) {
                // Delete each item's media files and records
                foreach ($equipmentDirectory->items()->with('media')->get() as $item) {
                    foreach ($item->media as $media) {
                        Storage::disk('hetzner')->delete($media->file_path);
                        $media->delete();
                    }

                    $item->delete();
                }

                // Finally delete the directory record
                $equipmentDirectory->delete();
            });

            return back()->with('success', __('Directory deleted successfully'));
        } catch (\Throwable $e) {
            return back()->withErrors(['general' => __('An error occurred while deleting the directory.'), 'details' => $e->getMessage()]);
        }
    }

    // ====== Directory Items ======
    public function itemsIndex(EquipmentDirectory $equipmentDirectory)
    {
        $items = $equipmentDirectory->items()
            ->with(['creator', 'media'])
            ->latest()
            ->get();

        return view('dashboard.equipment_directories.items.index', compact('equipmentDirectory', 'items'));
    }

    public function createItem(EquipmentDirectory $equipmentDirectory)
    {
        return view('dashboard.equipment_directories.items.create', compact('equipmentDirectory'));
    }

    public function storeItem(Request $request, EquipmentDirectory $equipmentDirectory)
    {
        // ✅ فاليديشن على نفس ستايل OrderCheckinController
        $request->validate([
            'name'        => ['required', 'string', 'max:255'],
            'contents'    => ['required', 'string'],
            'location'    => ['required', 'string', 'max:255'],
            'quantity'    => ['required', 'integer', 'min:1'],
            'notes'       => ['nullable', 'string'],
            'is_active'   => ['boolean'],

            'photo_attachments'   => ['nullable', 'array'],
            'photo_attachments.*' => 'file|mimes:jpg,jpeg,png,gif,webp',

            'audio_attachments'   => ['nullable', 'array'],
            'audio_attachments.*' => 'file|
                                       mimes:mp3,wav,ogg,webm,aac,m4a,mp4|
                                       mimetypes:audio/mpeg,audio/mp3,
                                                 audio/wav,audio/x-wav,audio/vnd.wave,
                                                 audio/ogg,application/ogg,
                                                 audio/webm,video/mp4,video/webm,
                                                 audio/aac,audio/x-aac,
                                                 audio/mp4,audio/x-m4a,audio/m4a,
                                                 audio/opus,
                                                 audio/3gpp,audio/3gpp2,audio/amr,
                                                 application/octet-stream',

            // 🎬 مفاتيح الفيديو (FilePond/S3) — نفس فكرة الصفحات الأخرى
            'uploaded_videos'   => ['nullable', 'array'],
            'uploaded_videos.*' => ['nullable', 'string'],

            // (اختياري) دعم رفع ملفات فيديو مباشرةً لو تستعملوه في بعض النماذج
            'video_attachments'   => ['nullable', 'array'],
            'video_attachments.*' => ['nullable','string'],

            // ❌ حقول الحذف
            'remove_photo'        => ['nullable', 'in:0,1'],
            'remove_audio'        => ['nullable', 'in:0,1'],
            'remove_video'        => ['nullable', 'in:0,1'],
        ]);

        // ✅ في حال حدوث أي خطأ أثناء إنشاء الـ item أو حفظ الوسائط نرجع بالخطأ مع البيانات القديمة
        try {
            $item = EquipmentDirectoryItem::create([
                'directory_id' => $equipmentDirectory->id,
                'type'         => '',
                'name'         => $request->name,
                'contents'     => $request->contents,
                'location'     => $request->location,
                'quantity'     => $request->quantity,
                'notes'        => $request->notes,
                'is_active'    => $request->is_active ?? true,
                'created_by'   => auth()->id(),
            ]);

            $this->storeMedia($item, $request, $equipmentDirectory);

        } catch (\Throwable $e) {
            return back()
                ->withErrors(['general' => __('An error occurred while adding the item.'), 'details' => $e->getMessage()])
                ->withInput();
        }

        return redirect()->route('equipment-directories.items.index', $equipmentDirectory)
            ->with('success', __('Item added successfully'));
    }

    public function editItem(EquipmentDirectory $equipmentDirectory, EquipmentDirectoryItem $item)
    {
        return view('dashboard.equipment_directories.items.create', compact('equipmentDirectory', 'item'));
    }

    public function updateItem(Request $request, EquipmentDirectory $equipmentDirectory, EquipmentDirectoryItem $item)
    {
        // ✅ نفس الفاليديشن كما في storeItem (نفس ستايل OrderCheckinController)
        $request->validate([
            'name'        => ['required', 'string', 'max:255'],
            'contents'    => ['required', 'string'],
            'location'    => ['required', 'string', 'max:255'],
            'quantity'    => ['required', 'integer', 'min:1'],
            'notes'       => ['nullable', 'string'],
            'is_active'   => ['boolean'],

            // 🖼️ الصور
            'photo_attachments'   => ['nullable', 'array'],
            'photo_attachments.*' => 'file|mimes:jpg,jpeg,png,gif,webp',

            // 🎵 الصوت
            'audio_attachments'   => ['nullable', 'array'],
            'audio_attachments.*' => 'file|
                                       mimes:mp3,wav,ogg,webm,aac,m4a,mp4|
                                       mimetypes:audio/mpeg,audio/mp3,
                                                 audio/wav,audio/x-wav,audio/vnd.wave,
                                                 audio/ogg,application/ogg,
                                                 audio/webm,video/mp4,video/webm,
                                                 audio/aac,audio/x-aac,
                                                 audio/mp4,audio/x-m4a,audio/m4a,
                                                 audio/opus,
                                                 audio/3gpp,audio/3gpp2,audio/amr,
                                                 application/octet-stream',

            // 🎬 مفاتيح فيديو جاهزة
            'uploaded_videos'   => ['nullable', 'array'],
            'uploaded_videos.*' => ['nullable', 'string'],

            // (اختياري) فيديو ملفات مباشرةً
            'video_attachments'   => ['nullable', 'array'],
            'video_attachments.*' => ['nullable','string'],

            // ❌ حقول الحذف
            'remove_photo'        => ['nullable', 'in:0,1'],
            'remove_audio'        => ['nullable', 'in:0,1'],
            'remove_video'        => ['nullable', 'in:0,1'],
        ]);

        $item->update([
            'type'      => $item->type ?? '',
            'name'      => $request->name,
            'contents'  => $request->contents,
            'location'  => $request->location,
            'quantity'  => $request->quantity,
            'notes'     => $request->notes,
            'is_active' => $request->is_active ?? $item->is_active,
        ]);

        $this->storeMedia($item, $request, $equipmentDirectory);

        return redirect()->route('equipment-directories.items.index', $item->directory)
            ->with('success', __('Item updated successfully'));
    }

    public function destroyItem(EquipmentDirectory $equipmentDirectory, EquipmentDirectoryItem $item)
    {
        if ($item->directory_id !== $equipmentDirectory->id) {
            abort(404);
        }

        foreach ($item->media as $media) {
            // إن كانت على نفس مخزن hetzner هذي تكفي
            Storage::disk('hetzner')->delete($media->file_path);
            $media->delete();
        }

        $item->delete();
        return back()->with('success', __('Item deleted successfully'));
    }

    public function destroyMedia(EquipmentDirectoryMedia $media)
    {
        Storage::disk('hetzner')->delete($media->file_path);
        $media->delete();
        return back()->with('success', __('Media deleted successfully'));
    }

    // ====== PDF EXPORT ======
    public function exportDirectoriesPdf()
    {
        $directories = EquipmentDirectory::with(['creator', 'items'])
            ->latest()
            ->get();

        $html = view('dashboard.equipment_directories.pdf.directories', compact('directories'))->render();

        $mpdf = new Mpdf([
            'mode' => 'utf-8',
            'format' => 'A4',
            'orientation' => 'L'
        ]);

        $mpdf->WriteHTML($html);
        return $mpdf->Output('equipment-directories.pdf', 'D');
    }

    public function exportItemsPdf(EquipmentDirectory $equipmentDirectory)
    {
        $items = $equipmentDirectory->items()
            ->with(['creator', 'media'])
            ->latest()
            ->get();

        $html = view('dashboard.equipment_directories.pdf.items', compact('equipmentDirectory', 'items'))->render();

        $mpdf = new Mpdf([
            'mode' => 'utf-8',
            'format' => 'A4',
            'orientation' => 'L'
        ]);

        $mpdf->WriteHTML($html);
        return $mpdf->Output("equipment-items-{$equipmentDirectory->id}.pdf", 'D');
    }

    // ====== All Locations Statistics ======
    public function allLocationsStatistics()
    {
        $items = EquipmentDirectoryItem::with(['directory', 'media'])
            ->latest()
            ->get();

        return view('dashboard.equipment_directories.all_locations', compact('items'));
    }

    public function exportAllLocationsStatisticsPdf(Request $request)
    {
        $token = trim((string) $request->query('token', ''));
        $preparedExport = $token
            ? EquipmentLocationExport::query()
                ->where('token', $token)
                ->first()
            : EquipmentLocationExport::query()
                ->where('status', 'ready')
                ->latest()
                ->first();

        if (! $preparedExport || $preparedExport->status !== 'ready' || empty($preparedExport->pdf_path) || ! Storage::disk('local')->exists($preparedExport->pdf_path)) {
            return back()->with('error', 'Please try again in a moment with export PDF.')->withInput();
        }

        return Storage::disk('local')->download(
            $preparedExport->pdf_path,
            __('إحصائية جميع مواقع المعدات') . '-' . date('Y-m-d') . '.pdf'
        );
    }

    public function prepareAllLocationsExport(Request $request)
    {
        $export = EquipmentLocationExport::query()->create([
            'user_id' => auth()->id(),
            'notify_whatsapp' => $request->boolean('notify_whatsapp'),
            'token' => (string) Str::uuid(),
            'status' => 'pending',
            'pdf_path' => null,
            'error_message' => null,
            'locale' => app()->getLocale(),
            'prepared_at' => null,
            'started_at' => null,
            'finished_at' => null,
            'expires_at' => now()->addHours(2),
        ]);

        PrepareEquipmentLocationsExportJob::dispatch($export->id);

        return response()->json([
            'success' => true,
            'message' => 'Preparing data ... ',
            'token' => $export->token,
            'status' => $export->status,
            'status_url' => route('equipment.all-locations.export.status', ['token' => $export->token]),
            'export_url' => route('equipment.all-locations.export', ['token' => $export->token]),
        ]);
    }

    public function allLocationsExportStatus(Request $request)
    {
        $token = trim((string) $request->query('token', ''));

        if ($token === '') {
            return response()->json([
                'success' => false,
                'message' => 'Missing export token.',
            ], 422);
        }

        $export = EquipmentLocationExport::query()
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
            'export_url' => route('equipment.all-locations.export', ['token' => $export->token]),
        ]);
    }

    // ====== PRIVATE ======
    private function storeMedia(EquipmentDirectoryItem $item, Request $request, EquipmentDirectory $directory)
    {
        // 👇 مسار جذر موحّد يحتوي على اسم رافع الملف (userSlug) + التاريخ
        $userId   = auth()->id() ?? 0;
        $userName = optional(auth()->user())->name ?? 'guest';
        $userSlug = 'u' . $userId . '-' . Str::slug($userName, '-');
        $baseRoot = "uploads/equipment-directory/{$userSlug}/" . date('Y/m') . "/{$directory->id}/{$item->id}";

        // 🖼️ الصور
        if ($request->boolean('remove_photo')) {
            foreach ($item->media()->where('file_type', 'image')->get() as $m) {
                Storage::disk('hetzner')->delete($m->file_path);
                $m->delete();
            }
        }
        if ($request->hasFile('photo_attachments')) {
            foreach ($request->file('photo_attachments') as $file) {
                $path = $file->store($baseRoot, 'hetzner');
                $item->media()->create([
                    'file_path' => $path,
                    'file_type' => 'image',
                ]);
            }
        }

        // 🎵 الصوت
        if ($request->boolean('remove_audio')) {
            foreach ($item->media()->where('file_type', 'audio')->get() as $m) {
                Storage::disk('hetzner')->delete($m->file_path);
                $m->delete();
            }
        }
        if ($request->hasFile('audio_attachments')) {
            foreach ($request->file('audio_attachments') as $file) {
                $path = $file->store($baseRoot, 'hetzner');
                $item->media()->create([
                    'file_path' => $path,
                    'file_type' => 'audio',
                ]);
            }
        }

        // 🎬 الفيديو (ملفات مباشرةً - إن وُجدت)
        if ($request->boolean('remove_video')) {
            foreach ($item->media()->where('file_type', 'video')->get() as $m) {
                Storage::disk('hetzner')->delete($m->file_path);
                $m->delete();
            }
        }
        if ($request->hasFile('video_attachments')) {
            foreach ($request->file('video_attachments') as $file) {
                $path = $file->store($baseRoot, 'hetzner');
                $item->media()->create([
                    'file_path' => $path,
                    'file_type' => 'video',
                ]);
            }
        }

        // 🎬 الفيديوهات المرفوعة مسبقًا (FilePond/S3) كمفاتيح — تُحفظ كما هي (المفتاح مكوَّن مسبقاً في سيرفر الرفع)
        if ($request->filled('uploaded_videos')) {
            $keys = collect((array) $request->input('uploaded_videos'))
                ->filter(fn($v) => filled($v))
                ->unique()
                ->values();

            foreach ($keys as $key) {
                $item->media()->create([
                    'file_path' => $key,
                    'file_type' => 'video',
                ]);
            }
        }
    }
}
