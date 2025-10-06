@extends('dashboard.layouts.app')
@section('pageTitle', __('dashboard.signin'))
@section('content')
    @include('dashboard.orders.nav')

    @php
        // صور
        $photoUrls = $order->relationLoaded('signinPhotos')
            ? $order->signinPhotos
                ->map(fn($img) => Storage::disk('hetzner')->url($img->image))
                ->filter()
                ->values()
                ->all()
            : $order
                ->signinPhotos()
                ->get()
                ->map(fn($img) => Storage::disk('hetzner')->url($img->image))
                ->filter()
                ->values()
                ->all();

        if (empty($photoUrls) && !empty($order->image_before_receiving_url)) {
            $photoUrls = [$order->image_before_receiving_url];
        }

        // فيديوهات (قديمة + legacy)
        $initialVideos = $order
            ->signinVideos()
            ->get()
            ->map(fn($v) => Storage::disk('hetzner')->url($v->video))
            ->filter()
            ->values()
            ->all();

        if (!empty($order->video_note_direct_key)) {
            $initialVideos[] = Storage::disk('hetzner')->url($order->video_note_direct_key);
        }

        // أصوات
        $audioUrls = $order->relationLoaded('signinAudios')
            ? $order->signinAudios
                ->map(fn($aud) => Storage::disk('hetzner')->url($aud->audio))
                ->filter()
                ->values()
                ->all()
            : $order
                ->signinAudios()
                ->get()
                ->map(fn($aud) => Storage::disk('hetzner')->url($aud->audio))
                ->filter()
                ->values()
                ->all();
    @endphp

    <div class="card mb-5 mb-xl-10">
        <form method="POST" id="media-form" action="{{ route('orders.updatesignin', $order->id) }}"
            enctype="multipart/form-data">
            @csrf
            @method('PUT')

            <div class="card-header align-items-center py-5 gap-2 gap-md-5">
                {{ __('dashboard.edit_time_and_media') }}
            </div>

            <div class="card-body pt-0">
                <div class="form-group mb-4">
                    <label for="time_of_receipt">{{ __('dashboard.signin_time') }}</label>
                    <input type="time" name="time_of_receipt" id="time_of_receipt" class="form-control"
                        value="{{ old('time_of_receipt', $order->time_of_receipt) }}">
                </div>
                <div class="form-group mb-5">
                    <label for="time_of_receipt_notes">{{ __('dashboard.receiving_time_notes') }}</label>
                    <textarea name="time_of_receipt_notes" id="time_of_receipt_notes" class="form-control">{{ old('time_of_receipt_notes', $order->time_of_receipt_notes) }}</textarea>
                </div>

                <div class="row g-3">
                    <!-- PHOTO -->


                    <div class="col-md-4">
                        <label class="form-label">📷 @lang('dashboard.photo_attachment')</label>
                        <div class="media-upload-container" data-type="photo" data-photos='@json($photoUrls)'>

                            <!-- السلايدر / المعاينة -->
                            <div class="preview-image-container mb-2"
                                style="{{ $order->image_before_receiving_url || count($photoUrls) ? '' : 'display:none;' }}">
                                @if ($order->image_before_receiving_url)
                                    <img src="{{ $order->image_before_receiving_url }}" class="preview-image img-thumbnail"
                                        style="max-width: 100%;">
                                @endif
                            </div>

                            <!-- الأزرار (بعد المعاينة) -->
                            <div class="d-grid gap-2 mt-2">
                                <button type="button" class="btn btn-sm btn-primary capture-media" data-media-type="photo">
                                    <i class="bi bi-camera"></i> {{ __('dashboard.capture_photo') }}
                                </button>
                                <button type="button" class="btn btn-sm btn-secondary upload-media">
                                    <i class="bi bi-upload"></i> {{ __('dashboard.upload') }}
                                </button>
                                <button type="button" class="btn btn-sm btn-danger remove-media">
                                    <i class="bi bi-trash"></i> {{ __('dashboard.remove') }}
                                </button>
                            </div>

                            <input type="file" name="image_before_receiving[]" class="media-input d-none"
                                accept="image/*" capture="environment" multiple>
                            <input type="hidden" name="remove_photo" value="0" class="remove-flag">
                        </div>
                    </div>

                    <!-- AUDIO -->
                    <div class="col-md-4">
                        <label class="form-label">🎵 @lang('dashboard.audio_attachment')</label>
                        <div class="media-upload-container" data-type="audio">

                            <!-- معاينة الأصوات -->
                            <div class="preview-audio-container mb-2"
                                style="{{ count($audioUrls) ? '' : 'display:none;' }}">
                                <div class="preview-audio-list">
                                    @foreach ($audioUrls as $url)
                                        <audio controls class="w-100 mb-2">
                                            <source src="{{ $url }}">
                                        </audio>
                                    @endforeach
                                </div>
                            </div>

                            <!-- الأزرار تحت بعض -->
                            <div class="d-grid gap-2">
                                <button type="button" class="btn btn-sm btn-primary capture-media" data-media-type="audio">
                                    <i class="bi bi-mic"></i> {{ __('dashboard.record') }}
                                </button>
                                <button type="button" class="btn btn-sm btn-secondary upload-media">
                                    <i class="bi bi-upload"></i> {{ __('dashboard.upload') }}
                                </button>
                                <button type="button" class="btn btn-sm btn-danger remove-media">
                                    <i class="bi bi-trash"></i> {{ __('dashboard.remove') }}
                                </button>
                            </div>

                            <input type="file" name="voice_notes[]" class="media-input d-none" accept="audio/*" multiple>
                            <input type="hidden" name="remove_audio" value="0" class="remove-flag">
                        </div>
                    </div>


                    <!-- VIDEO -->
                    <div class="col-md-4">
                        <label class="form-label">🎬 @lang('dashboard.video_attachment')</label>
                        <div id="video-slider" class="video-slider mb-2 {{ empty($initialVideos) ? 'd-none' : '' }}"
                            data-initial='@json($initialVideos)'>
                            <div class="slider-viewport">
                                <video class="slider-video" playsinline controls
                                    style="width:100%; height:auto; object-fit:contain;"></video>
                            </div>
                            <button type="button" class="slider-nav prev" aria-label="Prev">‹</button>
                            <button type="button" class="slider-nav next" aria-label="Next">›</button>
                            <div class="slider-dots"></div>
                        </div>

                        <!-- الأزرار تحت بعض -->
                        <div class="d-grid gap-2 mt-2">
                            <button type="button" id="capture-video-btn" class="btn btn-sm btn-primary">
                                🎥 {{ __('dashboard.start_recording_video') }}
                            </button>


                            <input type="file" id="camera-video-input" accept="video/*" capture hidden>

                            <input type="file" id="pond-video" name="signin_videos[]" accept="video/*" multiple>
                            <input type="hidden" name="remove_video" id="remove_video_flag" value="0">
                            <input type="hidden" id="video_note_direct_key" name="video_note_direct_key"
                                value="">
                            <input type="hidden" id="video_upload_id" value="">
                            <button type="button" class="btn btn-sm btn-danger remove-video-all">
                                <i class="bi bi-trash"></i> {{ __('dashboard.remove') }}
                            </button>
                        </div>
                        <div id="video-error" class="text-danger small mt-1" style="display:none;"></div>
                    </div>

                </div>
            </div>

            <div class="card-footer d-flex justify-content-end py-6 px-9">
                <a href="{{ route('orders.show', $order->id) }}" class="btn btn-light me-2">⬅
                    {{ __('dashboard.cancel') }}</a>
                <button type="submit" id="submit-btn" class="btn btn-success">💾
                    {{ __('dashboard.save_changes') }}</button>
            </div>
        </form>
        <!-- Lightbox Modal -->
<div id="photo-lightbox" class="lightbox d-none">
  <span class="close">&times;</span>
  <img class="lightbox-img" alt="photo">
  <button class="nav prev"></button>
  <button class="nav next"></button>
</div>

    </div>
@endsection





@push('css')
<link href="https://unpkg.com/filepond/dist/filepond.min.css" rel="stylesheet">
<style>
.media-upload-container {
  border: 1px dashed #ddd;
  border-radius: 4px;
  padding: 10px;
  background: #f9f9f9;
}

.remove-media { width: 100%; }

.preview-audio-container audio,
.preview-video-container video {
  max-width: 100%;
  border-radius: 8px;
  box-shadow: 0 2px 6px rgba(0,0,0,.2);
}

/* PHOTO SLIDER */
.photo-slider {
  position: relative;
  border: 1px solid #ddd;
  border-radius: 8px;
  overflow: hidden;
  background: #fff;
  box-shadow: 0 2px 6px rgba(0,0,0,.06);
}
.photo-slider .slider-viewport {
  height: 220px;
  display: flex;
  align-items: center;
  justify-content: center;
  background: #111 !important;
}
.photo-slider .slider-img {
  max-width: 100%;
  max-height: 100%;
  display: block;
  object-fit: contain;
  user-select: none;
  -webkit-user-drag: none;
}
.photo-slider .slider-nav {
  position: absolute;
  top: 50%;
  transform: translateY(-50%);
  border: 0;
  width: 36px;
  height: 36px;
  border-radius: 50%;
  background: rgba(0,0,0,.45);
  color: #fff;
  font-size: 18px;
  display: flex;
  align-items: center;
  justify-content: center;
  cursor: pointer;
}
.photo-slider .slider-nav:hover { background: rgba(0,0,0,.55); }
.photo-slider .prev { left: 8px; }
.photo-slider .next { right: 8px; }
.photo-slider .slider-dots {
  display: flex;
  justify-content: center;
  gap: 6px;
  padding: 8px;
  background: #fff;
}
.photo-slider .slider-dot {
  width: 8px;
  height: 8px;
  border-radius: 50%;
  background: #cfcfcf;
  cursor: pointer;
}
.photo-slider .slider-dot.active { background: #333; }

/* VIDEO SLIDER */
.video-slider {
  position: relative;
  border: 1px solid #ddd;
  border-radius: 8px;
  overflow: hidden;
  background: #fff;
  box-shadow: 0 2px 6px rgba(0,0,0,.06);
}
.video-slider .slider-viewport {
  height: 220px;
  display: flex;
  align-items: center;
  justify-content: center;
  background: #000;
}
.video-slider .slider-video {
  width: 100%;
  max-height: 100%;
  object-fit: contain;
  background: #000;
}
.video-slider .slider-nav {
  position: absolute;
  top: 50%;
  transform: translateY(-50%);
  border: 0;
  width: 36px;
  height: 36px;
  border-radius: 50%;
  background: rgba(0,0,0,.45);
  color: #fff;
  font-size: 18px;
  display: flex;
  align-items: center;
  justify-content: center;
  cursor: pointer;
}
.video-slider .slider-nav:hover { background: rgba(0,0,0,.55); }
.video-slider .prev { left: 8px; }
.video-slider .next { right: 8px; }
.video-slider .slider-dots {
  display: flex;
  justify-content: center;
  gap: 6px;
  padding: 8px;
  background: #fff;
}
.video-slider .slider-dot {
  width: 8px;
  height: 8px;
  border-radius: 50%;
  background: #cfcfcf;
  cursor: pointer;
}
.video-slider .slider-dot.active { background: #333; }

/* AUDIO STYLE */
.preview-audio-list audio {
  display: block;
  width: 100%;
  margin-top: .25rem;
}

/* LIGHTBOX */
.lightbox {
  position: fixed;
  inset: 0;
  background: rgba(0,0,0,0.9);
  display: flex;
  align-items: center;
  justify-content: center;
  z-index: 9999;
}
.lightbox.d-none { display: none; }
.lightbox img {
  max-width: 90%;
  max-height: 90%;
  object-fit: contain;
}
.lightbox .close {
  position: absolute;
  top: 20px;
  right: 30px;
  font-size: 36px;
  color: #fff;
  cursor: pointer;
}
.lightbox .nav {
  position: absolute;
  top: 50%;
  transform: translateY(-50%);
  font-size: 48px;
  color: #fff;
  background: transparent;
  border: none;
  cursor: pointer;
}
/* Lightbox افتراضي LTR */
.lightbox .nav.prev { left: 20px; right: auto; }
.lightbox .nav.next { right: 20px; left: auto; }


/* RTL fix */
[dir="rtl"] .lightbox .nav.prev { left: 20px; left: auto; }
[dir="rtl"] .lightbox .nav.next { right: 20px; right: auto; }



/* ✅ RTL FIX: عكس الأسهم */
[dir="rtl"] .photo-slider .prev,
[dir="rtl"] .video-slider .prev,
[dir="rtl"] .lightbox .nav.prev {
  right: 8px;
  left: auto;
  transform: translateY(-50%) scaleX(-1); /* قلب السهم أفقياً */
}
[dir="rtl"] .photo-slider .next,
[dir="rtl"] .video-slider .next,
[dir="rtl"] .lightbox .nav.next {
  left: 8px;
  right: auto;
  transform: translateY(-50%) scaleX(-1);
}

/* Style nav buttons unified */
.photo-slider .slider-nav,
.video-slider .slider-nav {
  font-size: 20px;
  font-weight: bold;
}
</style>
@endpush







@push('js')
    <script src="https://unpkg.com/filepond/dist/filepond.min.js"></script>
    <script src="https://unpkg.com/filepond-plugin-file-validate-type/dist/filepond-plugin-file-validate-type.min.js">
    </script>
    <script src="https://unpkg.com/filepond-plugin-file-validate-size/dist/filepond-plugin-file-validate-size.min.js">
    </script>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const CSRF = '{{ csrf_token() }}';
            const CHUNK_SIZE = 10 * 1024 * 1024; // 10MB
            const isMobile = /Mobi|Android|iPhone|iPad/i.test(navigator.userAgent);

            const ACCEPTED_VIDEOS = [
                'video/mp4', 'video/webm', 'video/avi', 'video/quicktime', 'video/x-matroska',
                'video/x-flv', 'video/3gpp', 'video/mpeg', 'video/mpg', 'video/x-ms-wmv', 'video/3gpp2',
                'video/x-m4v'
            ];

            // ===== Helpers =====
            function addFileToInputMultiple(input, file) {
                if (!input) return;
                const dt = new DataTransfer();
                if (input.files && input.files.length) Array.from(input.files).forEach(f => dt.items.add(f));
                dt.items.add(file);
                input.files = dt.files;
            }

            function setInputFile(input, file) {
                if (!input) return;
                const dt = new DataTransfer();
                dt.items.add(file);
                input.files = dt.files;
            }

            function cleanMime(type) {
                const t = (type || '').split(';')[0].trim();
                return t || 'video/webm';
            }
// ================== PHOTO =================//
(function initPhoto() {
    const box = document.querySelector('[data-type="photo"]');
    if (!box) return;

    const input = box.querySelector('.media-input');
    const previewSingle = box.querySelector('.preview-image-container');
    const captureBtn = box.querySelector('.capture-media');
    const uploadBtn = box.querySelector('.upload-media');
    const removeFlag = box.querySelector('.remove-flag');
    const actionsWrap = box.querySelector('.d-grid, .btn-group');

    // سلايدر الصور
    const sliderWrap = document.createElement('div');
    sliderWrap.className = 'photo-slider mb-2 d-none';
    sliderWrap.innerHTML = `
        <div class="slider-viewport">
          <img class="slider-img" alt="photo">
        </div>
        <button type="button" class="slider-nav prev" aria-label="Prev">‹</button>
        <button type="button" class="slider-nav next" aria-label="Next">›</button>
        <div class="slider-dots"></div>
    `;
    if (actionsWrap) box.insertBefore(sliderWrap, actionsWrap);
    else box.appendChild(sliderWrap);

    const viewport = sliderWrap.querySelector('.slider-viewport');
    const imgEl = sliderWrap.querySelector('.slider-img');
    const prevBtn = sliderWrap.querySelector('.prev');
    const nextBtn = sliderWrap.querySelector('.next');
    const dotsBox = sliderWrap.querySelector('.slider-dots');

    const isRTL = document.documentElement.dir === 'rtl' || getComputedStyle(document.body).direction === 'rtl';
    prevBtn.innerHTML = isRTL ? '›' : '‹';
    nextBtn.innerHTML = isRTL ? '‹' : '›';

    // 📌 Lightbox (عرض الصور بحجم كبير)
    const lightbox = document.createElement('div');
    lightbox.className = 'lightbox d-none';
    lightbox.innerHTML = `
        <span class="close">&times;</span>
        <img class="lightbox-img" alt="photo">
        <button class="nav prev">‹</button>
        <button class="nav next">›</button>
    `;
    document.body.appendChild(lightbox);
    const lightImg = lightbox.querySelector('.lightbox-img');
    const closeBtn = lightbox.querySelector('.close');
    const lbPrev = lightbox.querySelector('.nav.prev');
    const lbNext = lightbox.querySelector('.nav.next');

    function openLightbox() {
        if (!photos.length) return;
        lightbox.classList.remove('d-none');
        renderLightbox();
    }
    function closeLightbox() { lightbox.classList.add('d-none'); }
    function renderLightbox() { if (photos.length) lightImg.src = photos[current]; }

    closeBtn.addEventListener('click', closeLightbox);
    lbPrev.addEventListener('click', () => { current = (current - 1 + photos.length) % photos.length; renderLightbox(); });
    lbNext.addEventListener('click', () => { current = (current + 1) % photos.length; renderLightbox(); });
    document.addEventListener('keydown', e => {
        if (e.key === 'Escape') closeLightbox();
        if (e.key === 'ArrowLeft') lbPrev.click();
        if (e.key === 'ArrowRight') lbNext.click();
    });

    // 📌 ضغط الصور
    function compressImage(file, quality = 0.7, maxWidth = 1280, maxHeight = 1280) {
        return new Promise((resolve, reject) => {
            const img = new Image();
            img.onload = () => {
                let { width, height } = img;
                if (width > maxWidth || height > maxHeight) {
                    const scale = Math.min(maxWidth / width, maxHeight / height);
                    width = Math.round(width * scale);
                    height = Math.round(height * scale);
                }
                const canvas = document.createElement('canvas');
                canvas.width = width;
                canvas.height = height;
                const ctx = canvas.getContext('2d');
                ctx.drawImage(img, 0, 0, width, height);
                canvas.toBlob(blob => {
                    if (!blob) return reject("Compression failed");
                    const compressed = new File([blob], file.name.replace(/\.[^.]+$/, '') + '.jpg', { type: 'image/jpeg' });
                    resolve(compressed);
                }, 'image/jpeg', quality);
            };
            img.onerror = reject;
            img.src = URL.createObjectURL(file);
        });
    }

    // بيانات أولية
    let photos = [];
    try {
        const initial = box.dataset.photos ? JSON.parse(box.dataset.photos) : [];
        if (Array.isArray(initial) && initial.length) {
            photos = initial;
            if (previewSingle) { previewSingle.style.display = 'none'; previewSingle.innerHTML = ''; }
        }
    } catch (_) {}
    let current = 0;

    function renderDots() {
        dotsBox.innerHTML = '';
        photos.forEach((_, i) => {
            const d = document.createElement('div');
            d.className = 'slider-dot' + (i === current ? ' active' : '');
            d.addEventListener('click', () => { current = i; render(); });
            dotsBox.appendChild(d);
        });
    }
    function render() {
        if (!photos.length) { sliderWrap.classList.add('d-none'); return; }
        sliderWrap.classList.remove('d-none');
        imgEl.src = photos[current];
        renderDots();
    }
    function addPhotoToSlider(url) { photos.push(url); current = photos.length - 1; if (removeFlag) removeFlag.value = '0'; render(); }
    function goPrev() { if (photos.length) { current = (current - 1 + photos.length) % photos.length; render(); } }
    function goNext() { if (photos.length) { current = (current + 1) % photos.length; render(); } }

    prevBtn.addEventListener('click', goPrev);
    nextBtn.addEventListener('click', goNext);
    imgEl.addEventListener('click', openLightbox);

    // سحب بالإصبع
    let startX = 0;
    viewport.addEventListener('touchstart', e => startX = e.changedTouches[0].clientX, { passive: true });
    viewport.addEventListener('touchend', e => {
        const dx = e.changedTouches[0].clientX - startX;
        if (Math.abs(dx) > 40) {
            if (isRTL) { if (dx < 0) goPrev(); else goNext(); }
            else { if (dx < 0) goNext(); else goPrev(); }
        }
    }, { passive: true });

    // رفع ملفات
    uploadBtn?.addEventListener('click', () => input?.click());
    input?.addEventListener('change', async e => {
        const files = Array.from(e.target.files || []);
        for (const f of files) {
            try {
                const compressed = await compressImage(f, 0.7);
                addPhotoToSlider(URL.createObjectURL(compressed));
                const dt = new DataTransfer();
                (input.files ? Array.from(input.files).forEach(ff => dt.items.add(ff)) : 0);
                dt.items.add(compressed);
                input.files = dt.files;
            } catch {
                addPhotoToSlider(URL.createObjectURL(f));
            }
        }
        if (removeFlag) removeFlag.value = '0';
    });

    // التقاط بالكاميرا (ديسكتوب)
    const isMobile = /Mobi|Android|iPhone|iPad/i.test(navigator.userAgent);
    captureBtn?.addEventListener('click', () => {
        if (isMobile) { input?.click(); return; }
        (async () => {
            try {
                const stream = await navigator.mediaDevices.getUserMedia({ video: true, audio: false });
                const overlay = document.createElement('div');
                overlay.style.cssText = 'position:absolute;inset:0;background:#000;display:flex;align-items:center;justify-content:center;z-index:2;';
                const v = document.createElement('video');
                v.autoplay = v.muted = true; v.playsInline = true;
                v.srcObject = stream; v.style.cssText = 'width:100%;height:100%;object-fit:contain;';
                const snapBtn = document.createElement('button');
                snapBtn.type = 'button'; snapBtn.className = 'btn btn-sm btn-success';
                snapBtn.innerHTML = '<i class="bi bi-camera"></i> Snap';
                Object.assign(snapBtn.style, { position: 'absolute', bottom: '10px', left: '50%', transform: 'translateX(-50%)' });
                overlay.appendChild(v); overlay.appendChild(snapBtn);
                viewport.appendChild(overlay);
                sliderWrap.classList.remove('d-none');

                snapBtn.addEventListener('click', async () => {
                    const canvas = document.createElement('canvas');
                    canvas.width = v.videoWidth || 640;
                    canvas.height = v.videoHeight || 480;
                    canvas.getContext('2d').drawImage(v, 0, 0);
                    stream.getTracks().forEach(t => t.stop());
                    canvas.toBlob(async blob => {
                        if (!blob) { overlay.remove(); return; }
                        let file = new File([blob], 'photo_' + Date.now() + '.jpg', { type: 'image/jpeg' });
                        try { file = await compressImage(file, 0.7); } catch(_) {}
                        const dt = new DataTransfer();
                        (input.files ? Array.from(input.files).forEach(ff => dt.items.add(ff)) : 0);
                        dt.items.add(file);
                        input.files = dt.files;
                        addPhotoToSlider(URL.createObjectURL(file));
                        overlay.remove();
                    }, 'image/jpeg', 0.9);
                });
            } catch (err) { alert('Camera error: ' + (err?.message || err)); }
        })();
    });

    // حذف الكل
    box.querySelector('.remove-media')?.addEventListener('click', () => {
        photos = []; current = 0; render();
        if (input) input.value = '';
        if (removeFlag) removeFlag.value = '1';
        if (previewSingle) previewSingle.style.display = 'none';
    });

    render();
})();





            // ================== VIDEO: زر موحّد (موبايل/ديسكتوب) ==================
            (function initVideoUniversal() {
                const btn = document.getElementById('capture-video-btn');
                let camInput = document.getElementById('camera-video-input');
                if (camInput) camInput.multiple = true;

                if (!btn) {
                    camInput?.addEventListener('change', handlePickedVideos);
                    return;
                }

                if (isMobile) {
                    // موبايل: نفتح input الكاميرا العادي
                    btn.addEventListener('click', () => camInput?.click());
                    camInput?.addEventListener('change', handlePickedVideos);
                } else {
                    // ديسكتوب: تسجيل مباشر مع preview
                    let recorder = null,
                        chunks = [],
                        stream = null;
                    const player = document.querySelector(
                    '#video-slider .slider-video'); // نفس البلاصة تاع العرض

                    btn.addEventListener('click', async () => {
                        if (recorder && recorder.state === 'recording') {
                            recorder.stop();
                            btn.className = 'btn btn-sm btn-primary w-100';
                            btn.innerHTML = '🎥 {{ __('dashboard.start_recording_video') }}';
                            return;
                        }
                        try {
                            stream = await navigator.mediaDevices.getUserMedia({
                                video: {
                                    facingMode: {
                                        ideal: "user"
                                    },
                                    width: {
                                        ideal: 1280
                                    },
                                    height: {
                                        ideal: 720
                                    }
                                },
                                audio: true
                            });

                            // ✅ نضمن ظهور السلايدر حتى لو ماكانش فيه فيديوهات
                            if (sliderRoot) sliderRoot.classList.remove('d-none');

                            // 👇 عرض مباشر أثناء التسجيل
                            if (player) {
                                player.srcObject = stream;
                                player.muted = true;
                                player.play();
                            }

                            chunks = [];
                            let mimeType = 'video/webm;codecs=vp8,opus';
                            if (!MediaRecorder.isTypeSupported(mimeType)) {
                                mimeType = 'video/webm';
                                if (!MediaRecorder.isTypeSupported(mimeType)) {
                                    mimeType = 'video/mp4';
                                }
                            }

                            recorder = new MediaRecorder(stream, {
                                mimeType,
                                videoBitsPerSecond: 1000000,
                                audioBitsPerSecond: 64000
                            });
                            recorder.ondataavailable = e => {
                                if (e.data.size > 0) chunks.push(e.data);
                            };
                            recorder.onstop = () => {
                                try {
                                    stream.getTracks().forEach(t => t.stop());
                                } catch (_) {}
                                if (player) { // نحبس العرض المباشر
                                    player.srcObject = null;
                                }
                                const rawBlob = new Blob(chunks, {
                                    type: mimeType
                                });
                                const type = cleanMime(rawBlob.type);
                                const file = new File([rawBlob], 'recorded_' + Date.now() + (
                                    type === 'video/mp4' ? '.mp4' : '.webm'), {
                                    type
                                });
                                if (window.pondVideo) window.pondVideo.addFile(file);
                                document.getElementById('remove_video_flag').value = '0';
                            };

                            recorder.start();
                            btn.className = 'btn btn-sm btn-danger w-100';
                            btn.innerHTML =
                            '<i class="bi bi-stop"></i> {{ __('dashboard.stop') }}';
                        } catch (e) {
                            alert("Camera error: " + e.message);
                        }
                    });

                }

                function handlePickedVideos(e) {
                    const files = Array.from(e.target.files || []);
                    if (!files.length) return;
                    files.forEach(f => {
                        if (window.pondVideo) window.pondVideo.addFile(f);
                    });
                    document.getElementById('remove_video_flag').value = '0';
                    e.target.value = '';
                }
            })();


            // ================== VIDEO SLIDER + FILEPOND ==================
            if (window.FilePond) {
                try {
                    FilePond.registerPlugin(
                        window.FilePondPluginFileValidateType || (() => {}),
                        window.FilePondPluginFileValidateSize || (() => {})
                    );
                } catch (_) {}
            }

            const elVideo = document.getElementById('pond-video');
            let pondVideo = null;

            const sliderRoot = document.getElementById('video-slider');
            const player = sliderRoot?.querySelector('.slider-video');
            const btnPrev = sliderRoot?.querySelector('.prev');
            const btnNext = sliderRoot?.querySelector('.next');
            const dotsRoot = sliderRoot?.querySelector('.slider-dots');

            const isRTL = document.documentElement.dir === 'rtl' || getComputedStyle(document.body).direction ===
                'rtl';
            if (btnPrev && btnNext) {
                btnPrev.innerHTML = isRTL ? '›' : '‹';
                btnNext.innerHTML = isRTL ? '‹' : '›';
            }

            let videoItems = [];
            let vCurrent = 0;

            function renderVideoDots() {
                if (!dotsRoot) return;
                dotsRoot.innerHTML = '';
                videoItems.forEach((_, i) => {
                    const d = document.createElement('div');
                    d.className = 'slider-dot' + (i === vCurrent ? ' active' : '');
                    d.addEventListener('click', () => {
                        vCurrent = i;
                        renderVideo();
                    });
                    dotsRoot.appendChild(d);
                });
            }

            function renderVideo() {
                if (!sliderRoot || !player) return;
                if (!videoItems.length) {
                    sliderRoot.classList.add('d-none');
                    player.removeAttribute('src');
                    player.load();
                    return;
                }
                sliderRoot.classList.remove('d-none');
                const item = videoItems[vCurrent];
                player.src = item.url;
                player.load();
                renderVideoDots();
            }

            function addVideoItem(id, url, revoke = false) {
                videoItems.push({
                    id,
                    url,
                    revoke
                });
                vCurrent = videoItems.length - 1;
                renderVideo();
            }

            function removeVideoItem(id) {
                const idx = videoItems.findIndex(it => it.id === id);
                if (idx > -1) {
                    const item = videoItems[idx];
                    if (item.revoke) {
                        try {
                            URL.revokeObjectURL(item.url);
                        } catch (_) {}
                    }
                    videoItems.splice(idx, 1);
                    if (vCurrent >= videoItems.length) vCurrent = Math.max(0, videoItems.length - 1);
                    renderVideo();
                }
            }

            function initUnifiedVideoSlider() {
                if (!sliderRoot) return;
                let initial = [];
                try {
                    initial = JSON.parse(sliderRoot.dataset.initial || '[]');
                } catch (_) {
                    initial = [];
                }
                initial.forEach((url, i) => addVideoItem('server-' + i, url, false));

                btnPrev?.addEventListener('click', () => {
                    if (!videoItems.length) return;
                    vCurrent = (vCurrent - 1 + videoItems.length) % videoItems.length;
                    renderVideo();
                });
                btnNext?.addEventListener('click', () => {
                    if (!videoItems.length) return;
                    vCurrent = (vCurrent + 1) % videoItems.length;
                    renderVideo();
                });

                const viewport = sliderRoot.querySelector('.slider-viewport');
                if (viewport) {
                    let startX = 0;
                    viewport.addEventListener('touchstart', e => startX = e.changedTouches[0].clientX, {
                        passive: true
                    });
                    viewport.addEventListener('touchend', e => {
                        const dx = e.changedTouches[0].clientX - startX;
                        if (Math.abs(dx) > 40) {
                            if (isRTL) {
                                if (dx < 0) btnPrev?.click();
                                else btnNext?.click();
                            } else {
                                if (dx < 0) btnNext?.click();
                                else btnPrev?.click();
                            }
                        }
                    }, {
                        passive: true
                    });
                }

                renderVideo();
            }
            initUnifiedVideoSlider();

            if (elVideo && window.FilePond) {
                pondVideo = FilePond.create(elVideo, {
                    allowMultiple: true,
                    credits: false,
                    acceptedFileTypes: ACCEPTED_VIDEOS,
                    maxFileSize: '500MB',
                    instantUpload: true,
                    allowRevert: true,
                    server: {
                        process: (fieldName, file, metadata, load, error, progress, abort) => {
                            let cancelled = false,
                                currentXhr = null,
                                uploadId = '',
                                key = '';
                            const parts = [];
                            let uploadedBase = 0;
                            progress(true, 0, file.size);

                            (async () => {
                                try {
                                    const cleanType = cleanMime(file.type);
                                    const createRes = await fetch(
                                        `{{ route('multipart.create') }}`, {
                                            method: 'POST',
                                            headers: {
                                                'Content-Type': 'application/json',
                                                'X-CSRF-TOKEN': CSRF
                                            },
                                            body: JSON.stringify({
                                                fileName: file.name,
                                                contentType: cleanType,
                                                fileSize: file.size,
                                                prefix: 'uploads/signin/videos'
                                            })
                                        });
                                    if (!createRes.ok) throw new Error(
                                    'createMultipart failed');
                                    const createJson = await createRes.json();
                                    uploadId = createJson.uploadId;
                                    key = createJson.key;
                                    document.getElementById('video_upload_id').value = uploadId;
                                    document.getElementById('video_note_direct_key').value =
                                    key;

                                    const totalParts = Math.ceil(file.size / CHUNK_SIZE);
                                    for (let partNumber = 1; partNumber <=
                                        totalParts; partNumber++) {
                                        if (cancelled) return;
                                        const start = (partNumber - 1) * CHUNK_SIZE;
                                        const end = Math.min(start + CHUNK_SIZE, file.size);
                                        const blob = file.slice(start, end);

                                        const signRes = await fetch(
                                            `{{ route('multipart.sign') }}`, {
                                                method: 'POST',
                                                headers: {
                                                    'Content-Type': 'application/json',
                                                    'X-CSRF-TOKEN': CSRF
                                                },
                                                body: JSON.stringify({
                                                    key,
                                                    uploadId,
                                                    partNumber
                                                })
                                            });
                                        if (!signRes.ok) throw new Error('signPart failed');
                                        const {
                                            url
                                        } = await signRes.json();

                                        await new Promise((resolve, reject) => {
                                            const xhr = new XMLHttpRequest();
                                            currentXhr = xhr;
                                            xhr.open('PUT', url, true);
                                            xhr.upload.onprogress = (e) => {
                                                if (e.lengthComputable) progress(
                                                    true, uploadedBase + e
                                                    .loaded, file.size);
                                            };
                                            xhr.onload = () => {
                                                currentXhr = null;
                                                if (xhr.status >= 200 && xhr
                                                    .status < 300) {
                                                    const etag = (xhr
                                                            .getResponseHeader(
                                                                'ETag') || '')
                                                        .replace(/"/g, '');
                                                    if (!etag) return reject(
                                                        new Error(
                                                            'Missing ETag'));
                                                    parts.push({
                                                        ETag: etag,
                                                        PartNumber: partNumber
                                                    });
                                                    uploadedBase += blob.size;
                                                    progress(true, uploadedBase,
                                                        file.size);
                                                    resolve();
                                                } else reject(new Error(
                                                    'PUT failed: ' + xhr
                                                    .status));
                                            };
                                            xhr.onerror = () => reject(new Error(
                                                'Network error'));
                                            xhr.onabort = () => reject(new Error(
                                                'Aborted'));
                                            xhr.send(blob);
                                        });
                                    }

                                    if (cancelled) return;
                                    const completeRes = await fetch(
                                        `{{ route('multipart.complete') }}`, {
                                            method: 'POST',
                                            headers: {
                                                'Content-Type': 'application/json',
                                                'X-CSRF-TOKEN': CSRF
                                            },
                                            body: JSON.stringify({
                                                key,
                                                uploadId,
                                                parts
                                            })
                                        });
                                    if (!completeRes.ok) throw new Error(
                                        'completeMultipart failed');

                                    progress(true, file.size, file.size);
                                    load(key);
                                } catch (e) {
                                    if (cancelled) return;
                                    console.error(e);
                                    error(e.message || 'upload failed');
                                    const ve = document.getElementById('video-error');
                                    if (ve) {
                                        ve.style.display = 'block';
                                        ve.textContent = 'فشل رفع الفيديو. حاول من جديد.';
                                    }
                                }
                            })();

                            return {
                                abort: async () => {
                                    cancelled = true;
                                    try {
                                        if (currentXhr) currentXhr.abort();
                                    } catch (_) {}
                                    abort();
                                }
                            };
                        }
                    }
                });

                pondVideo.on('addfile', (err, fileItem) => {
                    if (err || !fileItem) return;
                    const blobURL = URL.createObjectURL(fileItem.file);
                    addVideoItem(fileItem.id, blobURL, true);
                    document.getElementById('remove_video_flag').value = '0';
                });
                pondVideo.on('removefile', (fileItem) => {
                    if (!fileItem) return;
                    removeVideoItem(fileItem.id);
                });

                window.pondVideo = pondVideo;
                // زر حذف كل الفيديوهات
                document.querySelector('.remove-video-all')?.addEventListener('click', () => {
                    if (window.pondVideo) {
                        window.pondVideo.removeFiles(); // يحذف الملفات من FilePond
                    }
                    videoItems = [];
                    vCurrent = 0;
                    renderVideo(); // يعاود يرسم السلايدر (فارغ)
                    document.getElementById('remove_video_flag').value = '1';
                });

            }
        });



        // ================== AUDIO (متعدد) ==================
        function addFileToInputMultiple(input, file) {
            if (!input) return;
            const dt = new DataTransfer();
            if (input.files && input.files.length) {
                Array.from(input.files).forEach(f => dt.items.add(f));
            }
            dt.items.add(file);
            input.files = dt.files;
        }
        (function initAudio() {
            const box = document.querySelector('[data-type="audio"]');
            if (!box) return;

            const input = box.querySelector('.media-input'); // name="voice_notes[]"
            const previewBox = box.querySelector('.preview-audio-container');
            const list = box.querySelector('.preview-audio-list') || (() => {
                const d = document.createElement('div');
                d.className = 'preview-audio-list';
                previewBox?.insertBefore(d, previewBox.firstChild);
                return d;
            })();
            const captureBtn = box.querySelector('.capture-media');
            const uploadBtn = box.querySelector('.upload-media');
            const removeFlag = box.querySelector('.remove-flag');

            let recorder = null,
                chunks = [],
                indicator = null;

            // زر الرفع → يفتح input
            uploadBtn?.addEventListener('click', () => input?.click());

            // زر التسجيل
            captureBtn?.addEventListener('click', async () => {
                // لو راه يسجّل، نوقف ونحفظ
                if (recorder && recorder.state === 'recording') {
                    recorder.stop();
                    captureBtn.className = 'btn btn-sm btn-primary';
                    captureBtn.innerHTML = '<i class="bi bi-mic"></i> {{ __('dashboard.record') }}';
                    if (indicator) {
                        indicator.remove();
                        indicator = null;
                    }
                    return;
                }

                try {
                    const stream = await navigator.mediaDevices.getUserMedia({
                        audio: true
                    });
                    chunks = [];
                    recorder = new MediaRecorder(stream);

                    recorder.ondataavailable = (e) => {
                        if (e.data.size > 0) chunks.push(e.data);
                    };

                    recorder.onstop = () => {
                        // نوقف الميك
                        try {
                            stream.getTracks().forEach(t => t.stop());
                        } catch (_) {}
                        // نكون ملف
                        const blob = new Blob(chunks, {
                            type: 'audio/webm'
                        });
                        const file = new File([blob], 'audio_' + Date.now() + '.webm', {
                            type: 'audio/webm'
                        });

                        // نضيفو للـ input (متعدد)
                        addFileToInputMultiple(input, file);

                        // نعرض معاينة جديدة (ما نستبدلش القديمة)
                        const audio = document.createElement('audio');
                        audio.controls = true;
                        audio.className = 'w-100 mb-2';
                        audio.src = URL.createObjectURL(file);
                        list.appendChild(audio);

                        previewBox.style.display = 'block';
                        removeFlag.value = '0';
                    };

                    recorder.start();
                    captureBtn.className = 'btn btn-sm btn-danger';
                    captureBtn.innerHTML = '<i class="bi bi-stop"></i> {{ __('dashboard.stop') }}';

                    // مؤشر تسجيل
                    previewBox.style.display = 'block';
                    indicator = document.createElement('div');
                    indicator.className = 'text-danger mt-2 small';
                    indicator.innerText = '🔴 Recording...';
                    list.appendChild(indicator);

                } catch (e) {
                    alert("Mic error: " + (e?.message || e));
                }
            });

            // لما يختار ملفات يدويًا (واحد أو أكثر)
            input?.addEventListener('change', (e) => {
                const files = Array.from(e.target.files || []);
                if (!files.length) return;

                // نعرضهم كلهم (من غير تبديل)
                files.forEach(f => {
                    const a = document.createElement('audio');
                    a.controls = true;
                    a.className = 'w-100 mb-2';
                    a.src = URL.createObjectURL(f);
                    list.appendChild(a);
                });

                previewBox.style.display = 'block';
                removeFlag.value = '0';
            });

            // زر الحذف (ينظّف الكل)
            box.querySelector('.remove-media')?.addEventListener('click', () => {
                previewBox.style.display = 'none';
                list.innerHTML = '';
                if (input) input.value = '';
                removeFlag.value = '1';
            });
        })();
    </script>
@endpush
