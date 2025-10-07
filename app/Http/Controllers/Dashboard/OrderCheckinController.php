<?php

namespace App\Http\Controllers\Dashboard;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use App\Models\Order;
use App\Models\PreLoginImage;
use App\Models\PreLoginVideo;
use App\Models\PreLoginAudio;
use App\Models\PreLogoutImage;
use App\Models\PreLogoutVideo;
use App\Models\PreLogoutAudio;
use Illuminate\Support\Facades\DB;

class OrderCheckinController extends Controller
{
    // ================= شاشة تسجيل الدخول =================
    public function signin($id)
    {
        $order = Order::with(['signinPhotos', 'signinVideos', 'signinAudios'])->findOrFail($id);
        return view('dashboard.orders.signin', compact('order'));
    }

    // ================= شاشة تسجيل الخروج =================
    public function logout($id)
    {
        $order = Order::with(['signoutPhotos', 'signoutVideos', 'signoutAudios'])->findOrFail($id);
        return view('dashboard.orders.logout', compact('order'));
    }

    // ================= حفظ تسجيل الدخول =================
    public function updatesignin(Request $request, $id)
    {
        $order = Order::findOrFail($id);
//dd($request->file('voice_notes'));

        $request->validate([
            'time_of_receipt'          => 'required|regex:/^\d{2}:\d{2}(:\d{2})?$/',
            'time_of_receipt_notes'    => 'nullable|string',
            'image_before_receiving'   => 'nullable|array',
            'image_before_receiving.*' => 'file|mimes:jpg,jpeg,png,gif,webp|max:20480',
            'voice_notes'              => 'nullable|array',
            'voice_notes.*'            => 'file|max:51200|
                                            mimes:mp3,wav,ogg,webm,aac,m4a,mp4|
                                            mimetypes:audio/mpeg,audio/wav,audio/ogg,
                                                    audio/webm,video/webm,
                                                    audio/aac,audio/x-m4a,
                                                    audio/mp4,video/mp4,
                                                    audio/caf',
            'signin_videos'            => 'nullable|array',
            'signin_videos.*'          => 'string',
            'remove_photo'             => 'nullable|in:0,1',
            'remove_audio'             => 'nullable|in:0,1',
            'remove_video'             => 'nullable|in:0,1',
        ]);

        DB::transaction(function () use ($request, $order) {
            $order->time_of_receipt       = $request->input('time_of_receipt');
            $order->time_of_receipt_notes = $request->input('time_of_receipt_notes');

            // 🖼 الصور
            if ($request->hasFile('image_before_receiving')) {
                $this->deleteSigninPhotos($order);
                foreach ($request->file('image_before_receiving') as $file) {
                    PreLoginImage::create(['order_id' => $order->id, 'image' => $file]);
                }
            } elseif ($request->input('remove_photo') == '1') {
                $this->deleteSigninPhotos($order);
            }

            // 🎵 الأصوات
            if ($request->input('remove_audio') == '1') {
                $this->deleteSigninAudios($order);
            } elseif ($request->hasFile('voice_notes')) {
                $this->deleteSigninAudios($order);
                foreach ($request->file('voice_notes') as $file) {
                    PreLoginAudio::create(['order_id' => $order->id, 'audio' => $file]);
                }
            }

            // 🎬 الفيديوهات
            if ($request->filled('signin_videos')) {
                $this->deleteSigninVideos($order);
                foreach ($request->input('signin_videos') as $key) {
                    PreLoginVideo::create(['order_id' => $order->id, 'video' => $key]);
                }
            } elseif ($request->input('remove_video') == '1') {
                $this->deleteSigninVideos($order);
            }

            $order->save();
        });

        return back()->with('success', __('dashboard.success'));
    }

    // ================= حفظ تسجيل الخروج =================
    public function updatesignout(Request $request, $id)
    {
        $order = Order::findOrFail($id);

        $request->validate([
            'delivery_time'            => 'nullable|regex:/^\d{2}:\d{2}(:\d{2})?$/',
            'delivery_time_notes'      => 'nullable|string',
            'image_after_delivery'     => 'nullable|array',
            'image_after_delivery.*'   => 'file|mimes:jpg,jpeg,png,gif,webp|max:20480',
            'voice_notes_logout'       => 'nullable|array',
            'voice_notes_logout.*'     => 'file|max:51200|
                                            mimes:mp3,wav,ogg,webm,aac,m4a,mp4|
                                            mimetypes:audio/mpeg,audio/wav,audio/ogg,
                                                    audio/webm,video/webm,
                                                    audio/aac,audio/x-m4a,
                                                    audio/mp4,video/mp4,
                                                    audio/caf',
            'signout_videos'           => 'nullable|array',
            'signout_videos.*'         => 'string',
            'remove_photo_logout'      => 'nullable|in:0,1',
            'remove_audio_logout'      => 'nullable|in:0,1',
            'remove_video_logout'      => 'nullable|in:0,1',
        ]);

        DB::transaction(function () use ($request, $order) {
            $order->delivery_time       = $request->input('delivery_time');
            $order->delivery_time_notes = $request->input('delivery_time_notes');

            // 🖼 الصور
            if ($request->hasFile('image_after_delivery')) {
                $this->deleteSignoutPhotos($order);
                foreach ($request->file('image_after_delivery') as $file) {
                    PreLogoutImage::create(['order_id' => $order->id, 'image' => $file]);
                }
            } elseif ($request->input('remove_photo_logout') == '1') {
                $this->deleteSignoutPhotos($order);
            }

            // 🎵 الأصوات
            if ($request->input('remove_audio_logout') == '1') {
                $this->deleteSignoutAudios($order);
            } elseif ($request->hasFile('voice_notes_logout')) {
                $this->deleteSignoutAudios($order);
                foreach ($request->file('voice_notes_logout') as $file) {
                    PreLogoutAudio::create(['order_id' => $order->id, 'audio' => $file]);
                }
            }

            // 🎬 الفيديوهات
            if ($request->filled('signout_videos')) {
                $this->deleteSignoutVideos($order);
                foreach ($request->input('signout_videos') as $key) {
                    PreLogoutVideo::create(['order_id' => $order->id, 'video' => $key]);
                }
            } elseif ($request->input('remove_video_logout') == '1') {
                $this->deleteSignoutVideos($order);
            }

            $order->save();
        });

        return back()->with('success', __('dashboard.success'));
    }

    // ================= رفع / حذف الملفات المؤقتة =================
    public function uploadTemporaryImageLogout(Request $request)
    {
        if ($request->hasFile('file')) {
            $path = $request->file('file')->store('uploads/signout/images/tmp', 'hetzner');
            return response()->json(['path' => $path, 'url' => Storage::disk('hetzner')->url($path)]);
        }
        return response()->json(['error' => 'No file uploaded'], 400);
    }

    public function removeImageLogout($id)
    {
        Storage::disk('hetzner')->delete($id);
        return response()->json(['success' => true]);
    }

    public function uploadTemporaryVideoLogout(Request $request)
    {
        if ($request->hasFile('file')) {
            $path = $request->file('file')->store('uploads/signout/videos/tmp', 'hetzner');
            return response()->json(['path' => $path, 'url' => Storage::disk('hetzner')->url($path)]);
        }
        return response()->json(['error' => 'No file uploaded'], 400);
    }

    public function removeVideoLogout($id)
    {
        Storage::disk('hetzner')->delete($id);
        return response()->json(['success' => true]);
    }

    // ================= Helpers =================
    private function deleteSigninPhotos(Order $order): void
    {
        $order->loadMissing('signinPhotos');
        foreach ($order->signinPhotos as $img) {
            Storage::disk('hetzner')->delete($img->getRawOriginal('image'));
            $img->delete();
        }
    }

    private function deleteSigninVideos(Order $order): void
    {
        $order->loadMissing('signinVideos');
        foreach ($order->signinVideos as $vid) {
            Storage::disk('hetzner')->delete($vid->getRawOriginal('video'));
            $vid->delete();
        }
    }

    private function deleteSigninAudios(Order $order): void
    {
        $order->loadMissing('signinAudios');
        foreach ($order->signinAudios as $aud) {
            Storage::disk('hetzner')->delete($aud->getRawOriginal('audio'));
            $aud->delete();
        }
    }

    private function deleteSignoutPhotos(Order $order): void
    {
        $order->loadMissing('signoutPhotos');
        foreach ($order->signoutPhotos as $img) {
            Storage::disk('hetzner')->delete($img->getRawOriginal('image'));
            $img->delete();
        }
    }

    private function deleteSignoutVideos(Order $order): void
    {
        $order->loadMissing('signoutVideos');
        foreach ($order->signoutVideos as $vid) {
            Storage::disk('hetzner')->delete($vid->getRawOriginal('video'));
            $vid->delete();
        }
    }

    private function deleteSignoutAudios(Order $order): void
    {
        $order->loadMissing('signoutAudios');
        foreach ($order->signoutAudios as $aud) {
            Storage::disk('hetzner')->delete($aud->getRawOriginal('audio'));
            $aud->delete();
        }
    }
}
