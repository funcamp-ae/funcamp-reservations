<?php

namespace App\Http\Controllers\Dashboard;

use App\Exports\TasksExport;
use App\Http\Controllers\Controller;
use App\Models\Task;
use App\Models\TaskType;
use App\Models\User;
use App\Repositories\IUserRepository;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Maatwebsite\Excel\Facades\Excel;

class TaskController extends Controller
{
    private $userRepository;

    public function __construct(IUserRepository $userRepository)
    {
        $this->userRepository = $userRepository;
    }

    public function index()
    {
        $this->authorize('viewAny', Task::class);
        $tasks = Task::with(['assignedUser', 'creator', 'taskType'])->latest()->get();
        return view('dashboard.tasks.index', compact('tasks'));
    }

    public function ordersTasksIndex(Request $request)
    {
        $this->authorize('viewAny', Task::class);
        $query = Task::with(['assignedUser', 'creator', 'taskType']);
        $status = $request->query('status', 'all');

        if (isset($status) && $status === 'all') {
            $query->where('status', '!=', 'completed');
        } elseif ($status && $status !== 'completed') {
            $query->where('status', $status);
        }

        if ($request->query('from')) {
            $query->whereDate('created_at', '>=', $request->query('from'));
        }
        if ($request->query('to')) {
            $query->whereDate('created_at', '<=', $request->query('to'));
        }

        $tasks = $query->latest()->get();
        return view('dashboard.orders.tasks', compact('tasks', 'status'));
    }

    public function create()
    {
        $this->authorize('create', Task::class);
        $users = $this->userRepository->getAll();
        $taskTypes = TaskType::active()->get();
        return view('dashboard.tasks.create', compact('users', 'taskTypes'));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'title'        => 'required|string|max:255',
            'description'  => 'nullable|string',
            'assigned_to'  => 'required|integer|exists:users,id',
            'due_date'     => 'required|date',
            'priority'     => 'required|in:low,medium,high',
            'task_type_id' => 'nullable|exists:task_types,id',

            // === المرفقات (نفس قوانين OrderCheckin) ===
            'photos'       => 'nullable|array',
            'photos.*'     => 'file|mimes:jpg,jpeg,png,gif,webp|max:20480',

            'audios'       => 'nullable|array',
            'audios.*'     => 'file|max:51200|
                               mimes:mp3,wav,ogg,webm,aac,m4a,mp4|
                               mimetypes:audio/mpeg,audio/wav,audio/ogg,
                                          audio/webm,video/webm,
                                          audio/aac,audio/x-m4a,
                                          audio/mp4,video/mp4,
                                          audio/caf',

            // فيديوهات FilePond كمفاتيح
            'uploaded_videos'   => 'nullable|array',
            'uploaded_videos.*' => 'string',
        ]);

        $task = null;

        DB::transaction(function () use ($request, &$task, $data) {
            $task = Task::create([
                'title'        => $data['title'],
                'description'  => $data['description'] ?? null,
                'assigned_to'  => $data['assigned_to'],
                'created_by'   => auth()->id(),
                'due_date'     => $data['due_date'],
                'priority'     => $data['priority'],
                'status'       => 'pending',
                'task_type_id' => $data['task_type_id'] ?? null,
            ]);

            // صور
            foreach ((array) $request->file('photos', []) as $photo) {
                $path = Storage::disk('hetzner')->putFile('task_attachments/photos', $photo);
                $task->attachments()->create(['type' => 'photo', 'path' => $path]);
            }

            // صوتيات
            foreach ((array) $request->file('audios', []) as $audio) {
                $path = Storage::disk('hetzner')->putFile('task_attachments/audio', $audio);
                $task->attachments()->create(['type' => 'audio', 'path' => $path]);
            }

            // فيديوهات من FilePond (مفاتيح جاهزة)
            foreach ((array) $request->input('uploaded_videos', []) as $key) {
                $task->attachments()->create(['type' => 'video', 'path' => $key]);
            }
        });

        return redirect()->route('tasks.index', $task)->with('success', 'تم إنشاء المهمة بنجاح.');
    }

    public function update(Request $request, Task $task)
    {
        $data = $request->validate([
            'title'        => 'required|string|max:255',
            'description'  => 'nullable|string',
            'assigned_to'  => 'required|integer|exists:users,id',
            'due_date'     => 'required|date',
            'priority'     => 'required|in:low,medium,high',
            'task_type_id' => 'nullable|exists:task_types,id',

            // flags (نفس نمط 0/1)
            'remove_photos' => 'nullable|in:0,1',
            'remove_audios' => 'nullable|in:0,1',
            'remove_videos' => 'nullable|in:0,1',

            // الملفات (نفس قوانين OrderCheckin)
            'photos'       => 'nullable|array',
            'photos.*'     => 'file|mimes:jpg,jpeg,png,gif,webp|max:20480',

            'audios'       => 'nullable|array',
            'audios.*'     => 'file|max:51200|
                               mimes:mp3,wav,ogg,webm,aac,m4a,mp4|
                               mimetypes:audio/mpeg,audio/wav,audio/ogg,
                                          audio/webm,video/webm,
                                          audio/aac,audio/x-m4a,
                                          audio/mp4,video/mp4,
                                          audio/caf',

            'uploaded_videos'   => 'nullable|array',
            'uploaded_videos.*' => 'string',
        ]);

        DB::transaction(function () use ($request, $task, $data) {
            $task->update([
                'title'        => $data['title'],
                'description'  => $data['description'] ?? null,
                'assigned_to'  => $data['assigned_to'],
                'due_date'     => $data['due_date'],
                'priority'     => $data['priority'],
                'task_type_id' => $data['task_type_id'] ?? null,
            ]);

            // حذف حسب الفلاج + حذف فعلي من التخزين
            if ($request->input('remove_photos') === '1') {
                $task->photos()->each(function ($att) {
                    try { Storage::disk('hetzner')->delete($att->path); } catch (\Throwable $e) {}
                    $att->delete();
                });
            }
            if ($request->input('remove_audios') === '1') {
                $task->audios()->each(function ($att) {
                    try { Storage::disk('hetzner')->delete($att->path); } catch (\Throwable $e) {}
                    $att->delete();
                });
            }
            if ($request->input('remove_videos') === '1') {
                $task->videos()->each(function ($att) {
                    // إن أردت حذف الفيديو من التخزين فعل السطر التالي
                    // try { Storage::disk('hetzner')->delete($att->path); } catch (\Throwable $e) {}
                    $att->delete();
                });
            }

            // صور جديدة
            foreach ((array) $request->file('photos', []) as $photo) {
                $path = Storage::disk('hetzner')->putFile('task_attachments/photos', $photo);
                $task->attachments()->create(['type' => 'photo', 'path' => $path]);
            }

            // صوتيات جديدة
            foreach ((array) $request->file('audios', []) as $audio) {
                $path = Storage::disk('hetzner')->putFile('task_attachments/audio', $audio);
                $task->attachments()->create(['type' => 'audio', 'path' => $path]);
            }

            // فيديوهات جديدة (FilePond)
            foreach ((array) $request->input('uploaded_videos', []) as $key) {
                $task->attachments()->create(['type' => 'video', 'path' => $key]);
            }
        });

        return back()->with('success', 'تم تحديث المهمة بنجاح.');
    }

    public function edit(Task $task)
    {
        $this->authorize('update', $task);
        $users = $this->userRepository->getAll();
        $taskTypes = TaskType::active()->get();
        return view('dashboard.tasks.create', compact('task', 'users', 'taskTypes'));
    }

    public function destroy(Task $task)
    {
        // حذف كل المرفقات من التخزين ثم من الداتابيس
        foreach ($task->attachments as $att) {
            try { Storage::disk('hetzner')->delete($att->path); } catch (\Throwable $e) {}
            $att->delete();
        }

        $task->delete();

        return redirect()
            ->route('tasks.index')
            ->with('success', __('dashboard.task_deleted_successfully'));
    }

    public function myTasks()
{
    $tasks = Task::where('assigned_to', auth()->id())
        ->with([
            'creator',
            'notifications',
            'taskType',
            // حمّل المرفقات المفلترة لتقليل الاستعلامات
            'photos:id,task_id,path,type',
            'audios:id,task_id,path,type',
            'videos:id,task_id,path,type',
        ])
        ->latest()
        ->get();

    return view('dashboard.tasks.my_tasks', compact('tasks'));
}


    public function updateTaskStatus(Request $request, Task $task)
    {
        $validated = $request->validate([
            'status'          => 'required|in:pending,in_progress,completed,failed',
            'failure_reason'  => 'required_if:status,failed|nullable|string',

            // flags 0/1
            'remove_photos'   => 'nullable|in:0,1',
            'remove_audios'   => 'nullable|in:0,1',
            'remove_videos'   => 'nullable|in:0,1',

            // مرفقات متعددة (نفس قوانين OrderCheckin)
            'photos'          => 'nullable|array',
            'photos.*'        => 'file|mimes:jpg,jpeg,png,gif,webp|max:20480',

            'audios'          => 'nullable|array',
            'audios.*'        => 'file|max:51200|
                                  mimes:mp3,wav,ogg,webm,aac,m4a,mp4|
                                  mimetypes:audio/mpeg,audio/wav,audio/ogg,
                                             audio/webm,video/webm,
                                             audio/aac,audio/x-m4a,
                                             audio/mp4,video/mp4,
                                             audio/caf',

            // مفاتيح فيديو FilePond
            'uploaded_videos'   => 'nullable|array',
            'uploaded_videos.*' => 'string',
        ]);

        DB::transaction(function () use ($request, $task, $validated) {
            $task->update([
                'status'         => $validated['status'],
                'failure_reason' => $validated['failure_reason'] ?? null,
            ]);

            // حذف حسب الفلاج + حذف من التخزين
            if ($request->input('remove_photos') === '1') {
                $task->photos()->each(function ($att) {
                    try { Storage::disk('hetzner')->delete($att->path); } catch (\Throwable $e) {}
                    $att->delete();
                });
            }
            if ($request->input('remove_audios') === '1') {
                $task->audios()->each(function ($att) {
                    try { Storage::disk('hetzner')->delete($att->path); } catch (\Throwable $e) {}
                    $att->delete();
                });
            }
            if ($request->input('remove_videos') === '1') {
                $task->videos()->each(function ($att) {
                    // try { Storage::disk('hetzner')->delete($att->path); } catch (\Throwable $e) {}
                    $att->delete();
                });
            }

            // صور جديدة
            foreach ((array) $request->file('photos', []) as $photo) {
                $path = Storage::disk('hetzner')->putFile('task_attachments/photos', $photo);
                $task->attachments()->create(['type' => 'photo', 'path' => $path]);
            }

            // صوتيات جديدة
            foreach ((array) $request->file('audios', []) as $audio) {
                $path = Storage::disk('hetzner')->putFile('task_attachments/audio', $audio);
                $task->attachments()->create(['type' => 'audio', 'path' => $path]);
            }

            // فيديوهات FilePond (مفاتيح جاهزة)
            foreach ((array) $request->input('uploaded_videos', []) as $key) {
                $task->attachments()->create(['type' => 'video', 'path' => $key]);
            }
        });

        return response()->json(['success' => true]);
    }

    public function reports()
    {
        $filters = [
            'status'      => request('status'),
            'employee_id' => request('employee_id'),
            'date_from'   => request('date_from'),
            'date_to'     => request('date_to')
        ];

        $query = Task::query()->with(['assignedUser', 'creator', 'taskType']);

        if ($filters['status']) {
            $query->where('status', $filters['status']);
        }
        if ($filters['employee_id']) {
            $query->where('assigned_to', $filters['employee_id']);
        }
        if ($filters['date_from']) {
            $query->whereDate('due_date', '>=', $filters['date_from']);
        }
        if ($filters['date_to']) {
            $query->whereDate('due_date', '<=', $filters['date_to']);
        }

        $tasks = $query->latest()->get();
        $employees = User::get();

        $completionStats = [
            'total'       => Task::count(),
            'completed'   => Task::where('status', 'completed')->count(),
            'pending'     => Task::where('status', 'pending')->count(),
            'in_progress' => Task::where('status', 'in_progress')->count(),
            'failed'      => Task::where('status', 'failed')->count(),
        ];

        return view('dashboard.tasks.reports', compact('tasks', 'employees', 'filters', 'completionStats'));
    }

    public function exportReports()
    {
        return Excel::download(new TasksExport(
            request('status'),
            request('employee_id'),
            request('date_from'),
            request('date_to')
        ), 'tasks-report.xlsx');
    }
}
