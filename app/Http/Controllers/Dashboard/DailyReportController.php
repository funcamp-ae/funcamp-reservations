<?php

namespace App\Http\Controllers\Dashboard;

use App\Http\Controllers\Controller;
use App\Models\DailyReport;
use App\Models\User;
use App\Notifications\NewDailyReportNotification;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Mpdf\Mpdf;
use Mpdf\Output\Destination;

class DailyReportController extends Controller
{
    public function index()
    {
        $this->authorize('viewAny', DailyReport::class);

        $reports = DailyReport::with(['employee','media'])
            ->filter(request()->only(['employee_id', 'date_from', 'date_to', 'search']))
            ->latest()
            ->get();

        $employees = User::get();

        return view('dashboard.daily_reports.index', compact('reports', 'employees'));
    }

    public function create()
    {
        return view('dashboard.daily_reports.create');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'title'   => 'required|string|max:255',
            'details' => 'required|string',
            'notes'   => 'nullable|string',

            'photo_attachments.*' => 'nullable|image|max:5120',
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

            'video_attachments.*' => 'nullable',
        ]);

        $validated['employee_id'] = auth()->id();
        $report = DailyReport::create($validated);

        $this->storeMedia($report, $request);

        $admins = User::where('user_type', 1)->get();
        Notification::send($admins, new NewDailyReportNotification($report));

        return redirect()->route('daily-reports.index')
            ->with('success', __('dashboard.report_created'));
    }

    public function edit(DailyReport $dailyReport)
    {
        $dailyReport->load('media');
        return view('dashboard.daily_reports.create', compact('dailyReport'));
    }

    public function update(Request $request, DailyReport $dailyReport)
    {
        $validated = $request->validate([
            'title'   => 'required|string|max:255',
            'details' => 'required|string',
            'notes'   => 'nullable|string',

            'photo_attachments.*' => 'nullable|image|max:5120',
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

            'video_attachments.*' => 'nullable',
            'remove_photo'        => 'nullable|in:0,1',
            'remove_audio'        => 'nullable|in:0,1',
            'remove_video'        => 'nullable|in:0,1',
        ]);

        $dailyReport->update($validated);

        $this->storeMedia($dailyReport, $request);

        return redirect()->route('daily-reports.index')
            ->with('success', __('dashboard.report_updated'));
    }

    public function destroy(DailyReport $dailyReport)
    {
        foreach ($dailyReport->media as $m) {
            Storage::disk('hetzner')->delete($m->path);
            $m->delete();
        }
        $dailyReport->delete();

        return back()->with('success', __('dashboard.report_deleted'));
    }

    public function exportToPdf()
    {
        $reports = DailyReport::with(['employee','media'])
            ->filter(request()->only(['employee_id', 'date_from', 'date_to', 'search']))
            ->latest()
            ->get();

        $html = view('dashboard.daily_reports.pdf', compact('reports'))->render();

        $mpdf = new Mpdf(['mode' => 'utf-8', 'format' => 'A4', 'orientation' => 'L']);
        $mpdf->WriteHTML($html);

        return response($mpdf->Output('daily_reports.pdf', Destination::STRING_RETURN))
            ->header('Content-Type', 'application/pdf')
            ->header('Content-Disposition', 'attachment; filename="daily_reports.pdf"');
    }

    private function storeMedia(DailyReport $report, Request $request)
    {
        // 👇 جذر حفظ موحّد يتضمن اسم رافع الملف (userSlug) + التاريخ + رقم التقرير
        $userId   = auth()->id() ?? 0;
        $userName = optional(auth()->user())->name ?? 'guest';
        $userSlug = 'u' . $userId . '-' . Str::slug($userName, '-');
        $baseRoot = "uploads/daily-reports/{$userSlug}/" . date('Y/m') . "/{$report->id}";

        foreach (['photo' => 'photo_attachments', 'audio' => 'audio_attachments', 'video' => 'video_attachments'] as $type => $field) {

            $removeFlag = $request->input("remove_{$type}", "0");

            // لو ضغط حذف أو رفع ملفات جديدة → نمسح القديم
            if ($removeFlag == "1" || $request->has($field)) {
                foreach ($report->media()->where('type', $type)->get() as $m) {
                    Storage::disk('hetzner')->delete($m->path);
                    $m->delete();
                }
            }

            // رفع جديد
            if ($request->has($field)) {
                foreach ($request->$field as $item) {
                    $path = null;

                    if ($item instanceof \Illuminate\Http\UploadedFile) {
                        // 👈 نحفظ تحت المسار الذي يحتوي على اسم الرافع
                        $path = $item->store("{$baseRoot}/{$type}s", 'hetzner');
                    } elseif (is_string($item)) {
                        // في حالة مفاتيح الرفع المُجزأ (S3) نتركها كما هي (المسار مُكوّن مسبقاً في سيرفر الرفع)
                        $path = $item;
                    }

                    if ($path) {
                        $report->media()->create([
                            'type' => $type,
                            'path' => $path,
                        ]);
                    }
                }
            }
        }
    }

    public function show(DailyReport $dailyReport)
    {
        // نحمّل العلاقة media باش نعرض الصور/الفيديو/الصوت
        $dailyReport->load(['employee','media']);

        return view('dashboard.daily_reports.show', compact('dailyReport'));
    }
}
