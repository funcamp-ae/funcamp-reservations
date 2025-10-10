@extends('dashboard.layouts.app')
@section('pageTitle', __('dashboard.logout'))
@section('content')
    @include('dashboard.orders.nav')

    @php
        // صور الخروج
        $photoUrls = $order->relationLoaded('signoutPhotos')
            ? $order->signoutPhotos
                ->map(fn($img) => Storage::disk('hetzner')->url($img->image))
                ->filter()
                ->values()
                ->all()
            : $order
                ->signoutPhotos()
                ->get()
                ->map(fn($img) => Storage::disk('hetzner')->url($img->image))
                ->filter()
                ->values()
                ->all();

        // فيديوهات الخروج
        $initialVideos = $order
            ->signoutVideos()
            ->get()
            ->map(fn($v) => Storage::disk('hetzner')->url($v->video))
            ->filter()
            ->values()
            ->all();

        // أصوات الخروج
        $audioUrls = $order->relationLoaded('signoutAudios')
            ? $order->signoutAudios
                ->map(fn($aud) => Storage::disk('hetzner')->url($aud->audio))
                ->filter()
                ->values()
                ->all()
            : $order
                ->signoutAudios()
                ->get()
                ->map(fn($aud) => Storage::disk('hetzner')->url($aud->audio))
                ->filter()
                ->values()
                ->all();
    @endphp

    <div class="card mb-5 mb-xl-10">
        <form method="POST" id="media-form" action="{{ route('orders.updatesignout', $order->id) }}"
            enctype="multipart/form-data">
            @csrf
            @method('PUT')
          <div class=" py-5 ">
                           <!-- customer information -->
                           <div class="pt-5 px-9 gap-2 gap-md-5">
                    <div class="row g-3 small">
                        <div class="col-md-1 text-center">
                            <div class="fw-semibold text-muted">{{ __('dashboard.order_id') }}</div>
                            <div class="fw-bold">{{ $order->id }}</div>
                        </div>
                        <div class="col-md-3 text-center">
                            <div class="fw-semibold text-muted">{{ __('dashboard.customer_name') }}</div>
                            <div class="fw-bold">{{ $order->customer->name }}</div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="card-body pt-0">
                <div class="form-group mb-4">
                    <label for="delivery_time">{{ __('dashboard.delivery_time') }}</label>
                    <input type="time" name="delivery_time" id="delivery_time" class="form-control"
                        value="{{ old('delivery_time', $order->delivery_time) }}">
                </div>
                <div class="form-group mb-5">
                    <label for="delivery_time_notes">{{ __('dashboard.delivery_time_notes') }}</label>
                    <textarea name="delivery_time_notes" id="delivery_time_notes" class="form-control">{{ old('delivery_time_notes', $order->delivery_time_notes) }}</textarea>
                </div>

                <div class="row g-3">
                    <!-- PHOTO -->
                    <div class="col-md-4">
                        <label class="form-label">📷 @lang('dashboard.photo_attachment')</label>
                        <div class="media-upload-container" data-type="photo" data-photos='@json($photoUrls)'>
                            <div class="preview-image-container mb-2"
                                style="{{ count($photoUrls) ? '' : 'display:none;' }}">
                            </div>
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
                                <input type="file" id="camera-photo-input-logout" accept="image/*" capture="environment" hidden>

                            </div>
                            <input type="file" name="image_after_delivery[]" class="media-input d-none"
                                accept="image/*"multiple>
                            <input type="hidden" name="remove_photo_logout" value="0" class="remove-flag">
                        </div>
                    </div>

                    <!-- AUDIO -->
                    <div class="col-md-4">
                        <label class="form-label">🎵 @lang('dashboard.audio_attachment')</label>
                        <div class="media-upload-container" data-type="audio">
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
                            <input type="file" name="voice_notes_logout[]" class="media-input d-none" accept="audio/*"
                                multiple>
                            <input type="hidden" name="remove_audio_logout" value="0" class="remove-flag">
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
                        <div class="d-grid gap-2 mt-2">
                            <button type="button" id="capture-video-btn" class="btn btn-sm btn-primary">
                                🎥 {{ __('dashboard.start_recording_video') }}
                            </button>
                            <input type="file" id="camera-video-input" accept="video/*" capture hidden>
                            <input type="file" id="pond-video" name="signout_videos[]" accept="video/*" multiple>
                            <input type="hidden" name="remove_video_logout" id="remove_video_flag" value="0">

                            <!-- ✅ مضافين هنا -->
                            <input type="hidden" id="video_upload_id" name="video_upload_id" value="">
                            <input type="hidden" id="video_note_direct_key" name="video_note_direct_key"
                                value="">

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
    </div>
@endsection




@push('css')
    <link href="https://unpkg.com/filepond/dist/filepond.min.css" rel="stylesheet">
    <style>
        .media-upload-container {
            border: 1px dashed #ddd;
            border-radius: 4px;
            padding: 10px;
        }

        .remove-media {
            width: 100%;
        }

        .preview-audio-container audio,
        .preview-video-container video {
            max-width: 100%;
            border-radius: 8px;
            box-shadow: 0 2px 6px rgba(0, 0, 0, .2);
        }

        /* PHOTO SLIDER */
        .photo-slider {
            position: relative;
            border: 1px solid #ddd;
            border-radius: 8px;
            overflow: hidden;
            box-shadow: 0 2px 6px rgba(0, 0, 0, .06);
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
            background: rgba(0, 0, 0, .45);
            color: #fff;
            font-size: 18px;
            display: flex;
            align-items: center;
            justify-content: center;
            cursor: pointer;
        }

        .photo-slider .slider-nav:hover {
            background: rgba(0, 0, 0, .55);
        }

        .photo-slider .prev {
            left: 8px;
        }

        .photo-slider .next {
            right: 8px;
        }

        .photo-slider .slider-dots {
            display: flex;
            justify-content: center;
            gap: 6px;
            padding: 8px;
        }

        .photo-slider .slider-dot {
            width: 8px;
            height: 8px;
            border-radius: 50%;
            cursor: pointer;
        }

        .photo-slider .slider-dot.active {
            background: #333;
        }

        /* VIDEO SLIDER */
        .video-slider {
            position: relative;
            border: 1px solid #ddd;
            border-radius: 8px;
            overflow: hidden;
            box-shadow: 0 2px 6px rgba(0, 0, 0, .06);
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
            background: rgba(0, 0, 0, .45);
            color: #fff;
            font-size: 18px;
            display: flex;
            align-items: center;
            justify-content: center;
            cursor: pointer;
        }

        .video-slider .slider-nav:hover {
            background: rgba(0, 0, 0, .55);
        }

        .video-slider .prev {
            left: 8px;
        }

        .video-slider .next {
            right: 8px;
        }

        .video-slider .slider-dots {
            display: flex;
            justify-content: center;
            gap: 6px;
            padding: 8px;
        }

        .video-slider .slider-dot {
            width: 8px;
            height: 8px;
            border-radius: 50%;
            cursor: pointer;
        }

        .video-slider .slider-dot.active {
            background: #333;
        }

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
            background: rgba(0, 0, 0, 0.9);
            display: flex;
            align-items: center;
            justify-content: center;
            z-index: 9999;
        }

        .lightbox.d-none {
            display: none;
        }

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
        .lightbox .nav.prev {
            left: 20px;
            right: auto;
        }

        .lightbox .nav.next {
            right: 20px;
            left: auto;
        }


        /* RTL fix */
        [dir="rtl"] .lightbox .nav.prev {
            left: 20px;
            left: auto;
        }

        [dir="rtl"] .lightbox .nav.next {
            right: 20px;
            right: auto;
        }



        /* ✅ RTL FIX: عكس الأسهم */
        [dir="rtl"] .photo-slider .prev,
        [dir="rtl"] .video-slider .prev,
        [dir="rtl"] .lightbox .nav.prev {
            right: 8px;
            left: auto;
            transform: translateY(-50%) scaleX(-1);
            /* قلب السهم أفقياً */
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
    'video/mp4',
    'video/quicktime',   // 🎯 خاص بـ iPhone/iPad (MOV)
    'video/hevc',        // لو كان مصور HEVC
    'video/webm',
    'video/avi',
    'video/x-matroska',
    'video/x-flv',
    'video/3gpp',
    'video/3gpp2',
    'video/mpeg',
    'video/mpg',
    'video/x-ms-wmv',
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

                const input = box.querySelector('.media-input'); // نخزّن فيه الملفات للـ form
                const previewSingle = box.querySelector('.preview-image-container');
                const captureBtn = box.querySelector('.capture-media');
                const uploadBtn = box.querySelector('.upload-media');
                const removeFlag = box.querySelector('.remove-flag');
                const actionsWrap = box.querySelector('.d-grid, .btn-group');

                // ===== سلايدر الصور =====
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

                const isRTL = document.documentElement.dir === 'rtl' || getComputedStyle(document.body)
                    .direction === 'rtl';
                prevBtn.innerHTML = isRTL ? '›' : '‹';
                nextBtn.innerHTML = isRTL ? '‹' : '›';

                // ===== Lightbox =====
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
                    lightImg.src = photos[current];
                }

                function closeLightbox() {
                    lightbox.classList.add('d-none');
                }
                closeBtn.addEventListener('click', closeLightbox);
                lbPrev.addEventListener('click', () => {
                    current = (current - 1 + photos.length) % photos.length;
                    lightImg.src = photos[current];
                });
                lbNext.addEventListener('click', () => {
                    current = (current + 1) % photos.length;
                    lightImg.src = photos[current];
                });
                document.addEventListener('keydown', e => {
                    if (e.key === 'Escape') closeLightbox();
                    if (e.key === 'ArrowLeft') lbPrev.click();
                    if (e.key === 'ArrowRight') lbNext.click();
                });

                // ===== ضغط الصور (يحّل HEIC بتحويله JPG عند الإمكان) =====
                function compressImage(file, quality = 0.7, maxW = 1280, maxH = 1280) {
                    return new Promise((resolve, reject) => {
                        const img = new Image();
                        const url = URL.createObjectURL(file);
                        img.onload = () => {
                            let {
                                width,
                                height
                            } = img;
                            if (width > maxW || height > maxH) {
                                const s = Math.min(maxW / width, maxH / height);
                                width = Math.round(width * s);
                                height = Math.round(height * s);
                            }
                            const canvas = document.createElement('canvas');
                            canvas.width = width;
                            canvas.height = height;
                            const ctx = canvas.getContext('2d');
                            ctx.drawImage(img, 0, 0, width, height);
                            canvas.toBlob(b => {
                                URL.revokeObjectURL(url);
                                if (!b) return reject('Compression failed');
                                const name = (file.name || 'photo').replace(/\.[^.]+$/,
                                    '') + '.jpg';
                                resolve(new File([b], name, {
                                    type: 'image/jpeg'
                                }));
                            }, 'image/jpeg', quality);
                        };
                        img.onerror = () => {
                            URL.revokeObjectURL(url);
                            reject('Decode failed');
                        };
                        img.src = url;
                    });
                }

                // ===== إدارة الحالة لمنع التكرار + مزامنة input =====
                const fileKey = f => `${f.name}__${f.size}__${f.lastModified || 0}__${f.type}`;
                let photoFilesState = [];
                let blobUrls = []; // نخزّن blob: لنعمل revoke عند الحذف

                function syncInputFromState() {
                    const dt = new DataTransfer();
                    photoFilesState.forEach(f => dt.items.add(f));
                    input.files = dt.files;
                }

                function addFileUnique(finalFile) {
                    const k = fileKey(finalFile);
                    if (photoFilesState.some(f => fileKey(f) === k)) return false;
                    photoFilesState.push(finalFile);
                    syncInputFromState();
                    return true;
                }

                function clearAll() {
                    // Revoke blob URLs
                    blobUrls.forEach(u => {
                        try {
                            URL.revokeObjectURL(u);
                        } catch {}
                    });
                    blobUrls = [];
                    photos = [];
                    photoFilesState = [];
                    syncInputFromState();
                    render();
                }

                // ===== صور أولية من السيرفر (عرض فقط) =====
                let photos = [];
                try {
                    const initial = box.dataset.photos ? JSON.parse(box.dataset.photos) : [];
                    if (Array.isArray(initial) && initial.length) {
                        photos = initial.slice();
                        if (previewSingle) {
                            previewSingle.style.display = 'none';
                            previewSingle.innerHTML = '';
                        }
                    }
                } catch (_) {}
                let current = 0;

                function renderDots() {
                    dotsBox.innerHTML = '';
                    photos.forEach((_, i) => {
                        const d = document.createElement('div');
                        d.className = 'slider-dot' + (i === current ? ' active' : '');
                        d.addEventListener('click', () => {
                            current = i;
                            render();
                        });
                        dotsBox.appendChild(d);
                    });
                }

                function render() {
                    if (!photos.length) {
                        sliderWrap.classList.add('d-none');
                        return;
                    }
                    sliderWrap.classList.remove('d-none');
                    imgEl.src = photos[current];
                    renderDots();
                }

                function addPhotoToSlider(url, isBlob = false) {
                    photos.push(url);
                    if (isBlob) blobUrls.push(url);
                    current = photos.length - 1;
                    render();
                }

                function goPrev() {
                    if (photos.length) {
                        current = (current - 1 + photos.length) % photos.length;
                        render();
                    }
                }

                function goNext() {
                    if (photos.length) {
                        current = (current + 1) % photos.length;
                        render();
                    }
                }
                prevBtn.addEventListener('click', goPrev);
                nextBtn.addEventListener('click', goNext);
                imgEl.addEventListener('click', openLightbox);

                // سحب بالإصبع
                let startX = 0;
                viewport.addEventListener('touchstart', e => startX = e.changedTouches[0].clientX, {
                    passive: true
                });
                viewport.addEventListener('touchend', e => {
                    const dx = e.changedTouches[0].clientX - startX;
                    if (Math.abs(dx) > 40) {
                        if (isRTL) {
                            if (dx < 0) goPrev();
                            else goNext();
                        } else {
                            if (dx < 0) goNext();
                            else goPrev();
                        }
                    }
                }, {
                    passive: true
                });

                // ===== فتح المعرض عبر input مؤقّت (يخدم على الموبايل والكمبيوتر) =====
                function openGalleryPicker() {
                    const picker = document.createElement('input');
                    picker.type = 'file';
                    picker.accept = 'image/*';
                    picker.multiple = true; // iOS يعرض Photo Library + Take Photo

                    picker.addEventListener('change', async () => {
                        const files = Array.from(picker.files || []);
                        for (const f of files) {
                            let finalFile = f;
                            try {
                                finalFile = await compressImage(f, 0.7);
                            } catch {
                                /* نخلي الأصلي */ }
                            const added = addFileUnique(finalFile);
                            if (added) {
                                const url = URL.createObjectURL(finalFile);
                                addPhotoToSlider(url, true);
                            }
                        }
                        if (removeFlag) removeFlag.value = '0';
                        picker.value = '';
                    });

                    picker.click();
                }

                // زر "رفع" → المعرض
                uploadBtn?.addEventListener('click', openGalleryPicker);

                // مهم: لا نستعمل change على input الأصلي حتى ما نكرّروش الملفات
                // لو كان عندك listeners قدام مرتبطين بـ .media-input، انزعهم.



                // ===== التقاط =====
                // ===== التقاط =====
const camPhotoInputLogout = document.getElementById('camera-photo-input-logout');
const isMobileUA = /Mobi|Android|iPhone|iPad/i.test(navigator.userAgent);

captureBtn?.addEventListener('click', () => {
    // 📱 على الهاتف → افتح كاميرا الهاتف الأصلية
    if (isMobileUA && camPhotoInputLogout) {
        camPhotoInputLogout.click();
        return;
    }

    // 💻 ديسكتوب: كاميرا ويب + Snap
    (async () => {
        try {
            const stream = await navigator.mediaDevices.getUserMedia({
                video: {
                    facingMode: { ideal: 'environment' },
                    width: { ideal: 1280 },
                    height: { ideal: 720 },
                    aspectRatio: { ideal: 16 / 9 }
                },
                audio: false
            });

            const overlay = document.createElement('div');
            overlay.style.cssText =
                'position:absolute;inset:0;background:#000;display:flex;align-items:center;justify-content:center;z-index:2;';
            const v = document.createElement('video');
            v.autoplay = v.muted = true;
            v.playsInline = true;
            v.srcObject = stream;
            v.style.cssText = 'width:100%;height:100%;object-fit:contain;';

            const snapBtn = document.createElement('button');
            snapBtn.type = 'button';
            snapBtn.className = 'btn btn-sm btn-success';
            snapBtn.innerHTML = '<i class="bi bi-camera"></i> Snap';
            Object.assign(snapBtn.style, {
                position: 'absolute',
                bottom: '10px',
                left: '50%',
                transform: 'translateX(-50%)'
            });

            overlay.appendChild(v);
            overlay.appendChild(snapBtn);
            viewport.appendChild(overlay);
            sliderWrap.classList.remove('d-none');

            snapBtn.addEventListener('click', async () => {
                const canvas = document.createElement('canvas');
                canvas.width = v.videoWidth || 640;
                canvas.height = v.videoHeight || 480;
                canvas.getContext('2d').drawImage(v, 0, 0);
                try { stream.getTracks().forEach(t => t.stop()); } catch {}

                canvas.toBlob(async blob => {
                    if (!blob) {
                        overlay.remove();
                        return;
                    }
                    let file = new File([blob], 'photo_' + Date.now() + '.jpg', { type: 'image/jpeg' });
                    try { file = await compressImage(file, 0.7); } catch {}
                    const added = addFileUnique(file);
                    if (added) {
                        const url = URL.createObjectURL(file);
                        addPhotoToSlider(url, true);
                    }
                    if (removeFlag) removeFlag.value = '0';
                    overlay.remove();
                }, 'image/jpeg', 0.95);
            });
        } catch (err) {
            alert('Camera error: ' + (err?.message || err));
        }
    })();
});

// 📱 معالجة الصورة من كاميرا الهاتف
camPhotoInputLogout?.addEventListener('change', async (e) => {
    const files = Array.from(e.target.files || []);
    for (const f of files) {
        let finalFile = f;
        try { finalFile = await compressImage(f, 0.7); } catch {}
        const added = addFileUnique(finalFile);
        if (added) {
            const url = URL.createObjectURL(finalFile);
            addPhotoToSlider(url, true);
        }
    }
    if (removeFlag) removeFlag.value = '0';
    e.target.value = '';
});


                // ===== حذف الكل =====
                box.querySelector('.remove-media')?.addEventListener('click', () => {
                    clearAll();
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
                                        ideal: "environment"
                                    },
                                    width: {
                                        ideal: 1280
                                    },
                                    height: {
                                        ideal: 720
                                    },
                                    aspectRatio: {
                                        ideal: 16 / 9
                                    } // يخلي الكاميرا تحاول ترجع صورة عريضة
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
                                videoBitsPerSecond: 800000,
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
                    labelIdle: '{{ __('dashboard.upload') }}',

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

    const input = box.querySelector('.media-input');
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

    let recorder = null, chunks = [], indicator = null, stream = null;
    let fallbackRecorder = null;

    // زر الرفع
    uploadBtn?.addEventListener('click', () => input?.click());

    // ✅ لما يختار ملفات صوت من الجهاز
    input?.addEventListener('change', () => {
        list.innerHTML = ''; // نمسح المعاينة القديمة
        Array.from(input.files).forEach(file => {
            const audio = document.createElement('audio');
            audio.controls = true;
            audio.className = 'w-100 mb-2';
            audio.src = URL.createObjectURL(file);
            list.appendChild(audio);
        });
        if (input.files.length) {
            previewBox.style.display = 'block';
            removeFlag.value = '0';
        }
    });

    // زر التسجيل
    captureBtn?.addEventListener('click', async () => {
        if ((recorder && recorder.state === 'recording') || fallbackRecorder) {
            if (recorder) recorder.stop();
            if (fallbackRecorder) fallbackRecorder.stop();
            captureBtn.className = 'btn btn-sm btn-primary';
            captureBtn.innerHTML = '<i class="bi bi-mic"></i> تسجيل';
            if (indicator) {
                indicator.remove();
                indicator = null;
            }
            return;
        }

        try {
            stream = await navigator.mediaDevices.getUserMedia({ audio: true });
            chunks = [];

            let mimeType = 'audio/webm';
            if (!MediaRecorder.isTypeSupported(mimeType)) mimeType = 'audio/mp4';
            if (!MediaRecorder.isTypeSupported(mimeType)) mimeType = 'audio/aac';

            if (window.MediaRecorder && MediaRecorder.isTypeSupported(mimeType)) {
                recorder = new MediaRecorder(stream, { mimeType });
                recorder.ondataavailable = (e) => { if (e.data.size > 0) chunks.push(e.data); };
                recorder.onstop = () => {
                    try { stream.getTracks().forEach(t => t.stop()); } catch (_) {}
                    const blob = new Blob(chunks, { type: mimeType });
                    const ext = mimeType.includes('mp4') ? '.m4a' : mimeType.includes('aac') ? '.aac' : '.webm';
                    saveFile(blob, ext, mimeType);
                };
                recorder.start();
            } else {
                fallbackRecorder = startWavRecorder(stream, saveFile);
            }

            captureBtn.className = 'btn btn-sm btn-danger';
            captureBtn.innerHTML = '<i class="bi bi-stop"></i> إيقاف';

            previewBox.style.display = 'block';
            indicator = document.createElement('div');
            indicator.className = 'text-danger mt-2 small';
            indicator.innerText = '🔴 Recording...';
            list.appendChild(indicator);

        } catch (e) {
            alert("Mic error: " + (e?.message || e));
        }
    });

    function saveFile(blob, ext, type) {
        const file = new File([blob], 'audio_' + Date.now() + ext, { type });
        addFileToInputMultiple(input, file);

        const audio = document.createElement('audio');
        audio.controls = true;
        audio.className = 'w-100 mb-2';
        audio.src = URL.createObjectURL(file);
        list.appendChild(audio);

        previewBox.style.display = 'block';
        removeFlag.value = '0';
    }

    // Polyfill wav
    function startWavRecorder(stream, callback) {
        const audioCtx = new(window.AudioContext || window.webkitAudioContext)();
        const source = audioCtx.createMediaStreamSource(stream);
        const processor = audioCtx.createScriptProcessor(4096, 1, 1);

        let buffer = [];
        processor.onaudioprocess = (e) => {
            buffer.push(new Float32Array(e.inputBuffer.getChannelData(0)));
        };

        source.connect(processor);
        processor.connect(audioCtx.destination);

        return {
            stop: () => {
                source.disconnect();
                processor.disconnect();
                audioCtx.close();
                const wavBlob = encodeWav(buffer, audioCtx.sampleRate);
                callback(wavBlob, '.wav', 'audio/wav');
                fallbackRecorder = null;
            }
        };
    }

    function encodeWav(chunks, sampleRate) {
        const length = chunks.reduce((acc, cur) => acc + cur.length, 0);
        const buffer = new Float32Array(length);
        let offset = 0;
        chunks.forEach(cur => { buffer.set(cur, offset); offset += cur.length; });

        const wavBuffer = new ArrayBuffer(44 + buffer.length * 2);
        const view = new DataView(wavBuffer);

        function writeString(view, offset, string) {
            for (let i = 0; i < string.length; i++) view.setUint8(offset + i, string.charCodeAt(i));
        }

        writeString(view, 0, 'RIFF');
        view.setUint32(4, 36 + buffer.length * 2, true);
        writeString(view, 8, 'WAVE');
        writeString(view, 12, 'fmt ');
        view.setUint32(16, 16, true);
        view.setUint16(20, 1, true);
        view.setUint16(22, 1, true);
        view.setUint32(24, sampleRate, true);
        view.setUint32(28, sampleRate * 2, true);
        view.setUint16(32, 2, true);
        view.setUint16(34, 16, true);
        writeString(view, 36, 'data');
        view.setUint32(40, buffer.length * 2, true);

        let index = 44;
        for (let i = 0; i < buffer.length; i++, index += 2) {
            const s = Math.max(-1, Math.min(1, buffer[i]));
            view.setInt16(index, s < 0 ? s * 0x8000 : s * 0x7FFF, true);
        }

        return new Blob([view], { type: 'audio/wav' });
    }

    // زر الحذف
    box.querySelector('.remove-media')?.addEventListener('click', () => {
        previewBox.style.display = 'none';
        list.innerHTML = '';
        if (input) input.value = '';
        removeFlag.value = '1';
    });
})();

    </script>
@endpush
