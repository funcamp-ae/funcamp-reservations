<?php

namespace App\Http\Controllers\Dashboard;

use App\Http\Controllers\Controller;
use App\Models\EquipmentDirectory;
use App\Models\EquipmentDirectoryItem;
use App\Models\EquipmentDirectoryMedia;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Mpdf\Mpdf;

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
            'name' => 'required|string|max:255',
            'is_active' => 'boolean',
        ]);

        EquipmentDirectory::create([
            'name' => $request->name,
            'is_active' => $request->is_active ?? true,
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
            'name' => 'required|string|max:255',
            'is_active' => 'boolean',
        ]);

        $equipmentDirectory->update([
            'name' => $request->name,
            'is_active' => $request->is_active ?? $equipmentDirectory->is_active,
        ]);

        return redirect()->route('equipment-directories.index')
            ->with('success', __('Directory updated successfully'));
    }

    public function destroy(EquipmentDirectory $equipmentDirectory)
    {
        $equipmentDirectory->delete();
        return back()->with('success', __('Directory deleted successfully'));
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
        $request->validate([
    'type' => 'required|string|max:255',
    'name' => 'required|string|max:255',
    'location' => 'required|string|max:255',
    'quantity' => 'required|integer|min:1',
    'notes' => 'nullable|string',
    'is_active' => 'boolean',

    // ✅ فاليديشن نوع الملفات فقط (بلا حجم)
    'photo_attachments.*' => 'nullable|mimes:jpeg,png,jpg,gif,webp',
    'audio_attachments.*' => 'nullable|mimetypes:audio/mpeg,audio/wav,audio/ogg',
    'video_attachments.*' => 'nullable|mimetypes:video/mp4,video/quicktime,video/webm,video/x-matroska',
]);


        $item = EquipmentDirectoryItem::create([
            'directory_id' => $equipmentDirectory->id,
            'type' => $request->type,
            'name' => $request->name,
            'location' => $request->location,
            'quantity' => $request->quantity,
            'notes' => $request->notes,
            'is_active' => $request->is_active ?? true,
            'created_by' => auth()->id(),
        ]);

        $this->storeMedia($item, $request, $equipmentDirectory);

        return redirect()->route('equipment-directories.items.index', $equipmentDirectory)
            ->with('success', __('Item added successfully'));
    }

    public function editItem(EquipmentDirectory $equipmentDirectory, EquipmentDirectoryItem $item)
    {
        return view('dashboard.equipment_directories.items.create', compact('equipmentDirectory','item'));
    }

    public function updateItem(Request $request, EquipmentDirectory $equipmentDirectory, EquipmentDirectoryItem $item)
    {
        $request->validate([
    'type' => 'required|string|max:255',
    'name' => 'required|string|max:255',
    'location' => 'required|string|max:255',
    'quantity' => 'required|integer|min:1',
    'notes' => 'nullable|string',
    'is_active' => 'boolean',

    // ✅ فاليديشن نوع الملفات فقط (بلا حجم)
    'photo_attachments.*' => 'nullable|mimes:jpeg,png,jpg,gif,webp',
    'audio_attachments.*' => 'nullable|mimetypes:audio/mpeg,audio/wav,audio/ogg',
    'video_attachments.*' => 'nullable|mimetypes:video/mp4,video/quicktime,video/webm,video/x-matroska',
]);


        $item->update([
            'type' => $request->type,
            'name' => $request->name,
            'location' => $request->location,
            'quantity' => $request->quantity,
            'notes' => $request->notes,
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

    // ====== PRIVATE ======
    private function storeMedia(EquipmentDirectoryItem $item, Request $request, EquipmentDirectory $directory)
    {
        // ---- PHOTO ----
        if ($request->boolean('remove_photo')) {
            foreach ($item->media()->where('file_type', 'image')->get() as $m) {
                Storage::disk('hetzner')->delete($m->file_path);
                $m->delete();
            }
        }
        if ($request->hasFile('photo_attachments')) {
            foreach ($request->file('photo_attachments') as $file) {
                $path = $file->store("equipment-directory/{$directory->id}/{$item->id}", 'hetzner');
                $item->media()->create([
                    'file_path' => $path,
                    'file_type' => 'image',
                ]);
            }
        }

        // ---- AUDIO ----
        if ($request->boolean('remove_audio')) {
            foreach ($item->media()->where('file_type', 'audio')->get() as $m) {
                Storage::disk('hetzner')->delete($m->file_path);
                $m->delete();
            }
        }
        if ($request->hasFile('audio_attachments')) {
            foreach ($request->file('audio_attachments') as $file) {
                $path = $file->store("equipment-directory/{$directory->id}/{$item->id}", 'hetzner');
                $item->media()->create([
                    'file_path' => $path,
                    'file_type' => 'audio',
                ]);
            }
        }

        // ---- VIDEO ----
        if ($request->boolean('remove_video')) {
            foreach ($item->media()->where('file_type', 'video')->get() as $m) {
                Storage::disk('hetzner')->delete($m->file_path);
                $m->delete();
            }
        }
        if ($request->hasFile('video_attachments')) {
            foreach ($request->file('video_attachments') as $file) {
                $path = $file->store("equipment-directory/{$directory->id}/{$item->id}", 'hetzner');
                $item->media()->create([
                    'file_path' => $path,
                    'file_type' => 'video',
                ]);
            }
        }

        // ---- VIDEO (multipart FilePond) ----
        if ($request->has('uploaded_videos')) {
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
