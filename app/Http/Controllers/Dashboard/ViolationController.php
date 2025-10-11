<?php

namespace App\Http\Controllers\Dashboard;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\Violation;
use App\Models\ViolationAttachment;
use App\Models\ViolationType;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class ViolationController extends Controller
{
    public function index()
    {
        $this->authorize('viewAny', Violation::class);

        $violations = Violation::with(['type', 'employee', 'creator'])
            ->latest()
            ->filter(request()->all())
            ->get();

        $employees = User::all();
        $types = ViolationType::active()->get();

        return view('dashboard.violations.index', compact('violations', 'employees', 'types'));
    }

    public function create()
    {
        $types = ViolationType::active()->get();
        $employees = User::all();
        return view('dashboard.violations.create', compact('types', 'employees'));
    }

    public function store(Request $request)
    {
        // === نفس ستايل الفاليديشن المستعمل في OrderCheckinController ===
        $request->validate([
            // البيانات الأساسية
            'violation_type_id'      => ['required', 'exists:violation_types,id'],
            'employee_ids'           => ['required', 'array', 'min:1'],
            'employee_ids.*'         => ['exists:users,id'],
            'violation_date'         => ['required', 'date'],
            'violation_time'         => ['required', 'regex:/^\d{2}:\d{2}(:\d{2})?$/'],
            'violation_place'        => ['required', 'string', 'max:255'],
            'employee_justification' => ['nullable', 'string'],
            'action_taken'           => ['required', 'in:warning,allowance,deduction'],
            'deduction_amount'       => ['required_if:action_taken,deduction', 'numeric', 'min:0'],
            'notes'                  => ['nullable', 'string'],

            // المرفقات
            'photos'                 => ['nullable', 'array'],
            'photos.*'               => 'file|mimes:jpg,jpeg,png,gif,webp|max:20480', // 20MB

            'audios'                 => ['nullable', 'array'],
            'audios.*'               => 'file|
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

            'uploaded_videos'        => ['nullable', 'array'],
            'uploaded_videos.*'      => ['nullable', 'string'],
        ]);

        // ⚠️ ملاحظة: سنرفع الملفات داخل الحلقة لكل مخالفة بحيث يتضمّن المسار اسم رافع الملف ومعرّف المخالفة
        $photoFiles = (array) $request->file('photos', []);
        $audioFiles = (array) $request->file('audios', []);
        $videoKeys  = (array) $request->input('uploaded_videos', []);

        DB::beginTransaction();
        try {
            $createdCount = 0;

            foreach ($request->employee_ids as $employeeId) {
                $violation = Violation::create([
                    'violation_type_id'      => $request->violation_type_id,
                    'employee_id'            => $employeeId,
                    'created_by'             => auth()->id(),
                    'violation_date'         => $request->violation_date,
                    'violation_time'         => $request->violation_time,
                    'violation_place'        => $request->violation_place,
                    'employee_justification' => $request->employee_justification,
                    'action_taken'           => $request->action_taken,
                    'deduction_amount'       => $request->action_taken === 'deduction' ? $request->deduction_amount : null,
                    'notes'                  => $request->notes,
                ]);

                // ✅ جذر الحفظ متضمّن اسم رافع الملف
                $base = $this->violationBaseRoot($violation->id);

                // صور
                foreach ($photoFiles as $photo) {
                    $path = $photo->store($base . '/photos', 'hetzner');
                    $violation->attachments()->create(['type' => 'photo', 'path' => $path]);
                }

                // صوت
                foreach ($audioFiles as $audio) {
                    $path = $audio->store($base . '/audio', 'hetzner');
                    $violation->attachments()->create(['type' => 'audio', 'path' => $path]);
                }

                // فيديو (مفاتيح من رفع مُجزّأ)
                foreach ($videoKeys as $key) {
                    $violation->attachments()->create(['type' => 'video', 'path' => $key]);
                }

                $createdCount++;
            }

            DB::commit();

            $message = $createdCount > 1
                ? __('dashboard.violation_recorded_for_employees', ['count' => $createdCount])
                : __('dashboard.violation_recorded_successfully');

            return redirect()->route('violations.index')->with('success', $message);
        } catch (\Throwable $e) {
            DB::rollBack();
            Log::error('Error creating violations: ' . $e->getMessage());
            return back()->withInput()->with('error', __('dashboard.error_occurred_while_creating_violations'));
        }
    }

    public function show(Violation $violation)
    {
        return view('dashboard.violations.show', compact('violation'));
    }

    public function edit(Violation $violation)
    {
        $types = ViolationType::active()->get();
        $employees = User::all();
        $violation->load('attachments');
        return view('dashboard.violations.edit', compact('violation', 'types', 'employees'));
    }

    public function update(Request $request, Violation $violation)
    {
        // === نفس ستايل الفاليديشن المستعمل في OrderCheckinController + تصحيح photos.* ===
        $request->validate([
            // البيانات الأساسية
            'violation_type_id'      => ['required', 'exists:violation_types,id'],
            'employee_id'            => ['required', 'exists:users,id'],
            'violation_date'         => ['required', 'date'],
            'violation_time'         => ['required', 'regex:/^\d{2}:\d{2}(:\d{2})?$/'],
            'violation_place'        => ['required', 'string', 'max:255'],
            'employee_justification' => ['nullable', 'string'],
            'action_taken'           => ['required', 'in:warning,allowance,deduction'],
            'deduction_amount'       => ['nullable', 'required_if:action_taken,deduction', 'numeric', 'min:0'],
            'notes'                  => ['nullable', 'string'],

            // فلاج الحذف
            'remove_photos'          => ['nullable', 'in:0,1'],
            'remove_audios'          => ['nullable', 'in:0,1'],
            'remove_videos'          => ['nullable', 'in:0,1'],

            // المرفقات
            'photos'                 => ['nullable', 'array'],
            'photos.*'               => 'file|mimes:jpg,jpeg,png,gif,webp|max:20480',

            'audios'                 => ['nullable', 'array'],
            'audios.*'               => 'file|
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

            'uploaded_videos'        => ['nullable', 'array'],
            'uploaded_videos.*'      => ['nullable', 'string'],
        ]);

        DB::beginTransaction();
        try {
            // تحديث البيانات الأساسية
            $violation->update([
                'violation_type_id'      => $request->violation_type_id,
                'employee_id'            => $request->employee_id,
                'violation_date'         => $request->violation_date,
                'violation_time'         => $request->violation_time,
                'violation_place'        => $request->violation_place,
                'employee_justification' => $request->employee_justification,
                'action_taken'           => $request->action_taken,
                'deduction_amount'       => $request->action_taken === 'deduction' ? $request->deduction_amount : null,
                'notes'                  => $request->notes,
            ]);

            // حذف المرفقات حسب الفلاج
            if ($request->boolean('remove_photos')) {
                $violation->photos()->each(function ($att) {
                    try { Storage::disk('hetzner')->delete($att->path); } catch (\Throwable $e) {}
                    $att->delete();
                });
            }
            if ($request->boolean('remove_audios')) {
                $violation->audios()->each(function ($att) {
                    try { Storage::disk('hetzner')->delete($att->path); } catch (\Throwable $e) {}
                    $att->delete();
                });
            }
            if ($request->boolean('remove_videos')) {
                // مفاتيح فيديو خارجية (S3)، فقط نحذف السجلات
                $violation->videos()->each(fn($att) => $att->delete());
            }

            // ✅ جذر الحفظ متضمّن اسم رافع الملف
            $base = $this->violationBaseRoot($violation->id);

            // إضافة مرفقات جديدة (لو وُجدت)
            foreach ((array) $request->file('photos', []) as $photo) {
                $path = $photo->store($base . '/photos', 'hetzner');
                $violation->attachments()->create(['type' => 'photo', 'path' => $path]);
            }

            foreach ((array) $request->file('audios', []) as $audio) {
                $path = $audio->store($base . '/audio', 'hetzner');
                $violation->attachments()->create(['type' => 'audio', 'path' => $path]);
            }

            foreach ((array) $request->input('uploaded_videos', []) as $key) {
                $violation->attachments()->create(['type' => 'video', 'path' => $key]);
            }

            DB::commit();
            return redirect()->route('violations.index')->with('success', __('dashboard.updated_successfully'));
        } catch (\Throwable $e) {
            DB::rollBack();
            Log::error('Error updating violation: ' . $e->getMessage());
            return back()->withInput()->with('error', __('dashboard.error_occurred_while_updating'));
        }
    }

    public function destroy(Violation $violation)
    {
        $violation->load('attachments');

        foreach ($violation->attachments as $att) {
            if (in_array($att->type, ['photo', 'audio'])) {
                try { Storage::disk('hetzner')->delete($att->path); } catch (\Throwable $e) {}
            }
            // الفيديو مفاتيح خارجية—نحذف السجل فقط
            $att->delete();
        }

        $violation->delete();
        return redirect()->route('violations.index')->with('success', __('dashboard.deleted_successfully'));
    }

    /**
     * يبني جذر الحفظ لمرفقات المخالفات مع تضمين هوية واسم رافع الملف.
     * مثال: uploads/violations/u5-ahmed-ali/2025/10/violations/123
     */
    private function violationBaseRoot(int $violationId): string
    {
        $user     = auth()->user();
        $userId   = $user?->id ?? 0;
        $userName = $user?->name ?? 'guest';
        $userSlug = 'u' . $userId . '-' . Str::slug($userName, '-');

        return "uploads/violations/{$userSlug}/" . date('Y/m') . "/violations/{$violationId}";
    }
}
