@extends('dashboard.layouts.app')
@section('pageTitle', isset($item) ? __('dashboard.edit_item') : __('dashboard.add_item'))

@php
    $photoUrls = isset($item)
        ? $item->media
            ->where('file_type', 'image')
            ->map(fn($m) => Storage::disk('hetzner')->url($m->file_path))
            ->values()
            ->all()
        : [];

    $videoUrls = isset($item)
        ? $item->media
            ->where('file_type', 'video')
            ->map(fn($m) => Storage::disk('hetzner')->url($m->file_path))
            ->values()
            ->all()
        : [];

    $audioUrls = isset($item)
        ? $item->media
            ->where('file_type', 'audio')
            ->map(fn($m) => Storage::disk('hetzner')->url($m->file_path))
            ->values()
            ->all()
        : [];
@endphp

@section('content')
    <div class="card mb-5 mb-xl-10">
        <div class="card-header">
            <h3 class="card-title">
                @isset($item)
                    @lang('dashboard.edit_item') - {{ $equipmentDirectory->name }}
                @else
                    @lang('dashboard.add_item') - {{ $equipmentDirectory->name }}
                @endisset
            </h3>
        </div>

        <div class="card-body">
            <form method="POST"
                action="{{ isset($item) ? route('equipment-directories.items.update', [$equipmentDirectory, $item]) : route('equipment-directories.items.store', $equipmentDirectory) }}"
                enctype="multipart/form-data">
                @csrf
                @isset($item)
                    @method('PUT')
                @endisset

                {{-- TYPE --}}
                <div class="row mb-6">
                    <label class="col-lg-4 col-form-label required fw-bold fs-6">@lang('dashboard.item_type')</label>
                    <div class="col-lg-8">
                        <input type="text" name="type" class="form-control"
                            value="{{ old('type', $item->type ?? '') }}" required>
                    </div>
                </div>

                {{-- NAME --}}
                <div class="row mb-6">
                    <label class="col-lg-4 col-form-label required fw-bold fs-6">@lang('dashboard.item_name')</label>
                    <div class="col-lg-8">
                        <input type="text" name="name" class="form-control"
                            value="{{ old('name', $item->name ?? '') }}" required>
                    </div>
                </div>

                {{-- LOCATION --}}
                <div class="row mb-6">
                    <label class="col-lg-4 col-form-label required fw-bold fs-6">@lang('dashboard.location')</label>
                    <div class="col-lg-8">
                        <input type="text" name="location" class="form-control"
                            value="{{ old('location', $item->location ?? '') }}" required>
                    </div>
                </div>

                {{-- QUANTITY --}}
                <div class="row mb-6">
                    <label class="col-lg-4 col-form-label required fw-bold fs-6">@lang('dashboard.quantity')</label>
                    <div class="col-lg-8">
                        <input type="number" name="quantity" class="form-control no-arrows" min="1"
                            value="{{ old('quantity', $item->quantity ?? 1) }}" required>
                    </div>
                </div>

                {{-- NOTES --}}
                <div class="row mb-6">
                    <label class="col-lg-4 col-form-label fw-bold fs-6">@lang('dashboard.notes')</label>
                    <div class="col-lg-8">
                        <textarea name="notes" rows="3" class="form-control">{{ old('notes', $item->notes ?? '') }}</textarea>
                    </div>
                </div>

                {{-- STATUS --}}
                <div class="row mb-6">
                    <label class="col-lg-4 col-form-label fw-bold fs-6">@lang('dashboard.status')</label>
                    <div class="col-lg-8">
                        <div class="form-check form-switch form-check-custom form-check-solid">
                            <input type="hidden" name="is_active" value="0">
                            <input class="form-check-input" type="checkbox" name="is_active" value="1" id="is_active"
                                @checked(old('is_active', $item->is_active ?? true))>
                            <label class="form-check-label" for="is_active">
                                @lang('dashboard.active')
                            </label>
                        </div>
                    </div>
                </div>

                {{-- MEDIA --}}
                <div class="row g-3">
                    {{-- PHOTO --}}
                    <div class="col-md-4">
                        <label class="form-label">📷 @lang('dashboard.photo_attachment')</label>
                        <div class="media-upload-container" data-type="photo" data-photos='@json($photoUrls)'>

                            <div class="preview-image-container mb-2"
                                style="{{ count($photoUrls) ? '' : 'display:none;' }}"></div>

                            <div class="d-grid gap-2">
                                <button type="button" class="btn btn-sm btn-primary capture-media">
                                    <i class="bi bi-camera"></i> @lang('dashboard.capture_photo')
                                </button>
                                <button type="button" class="btn btn-sm btn-secondary upload-media">
                                    <i class="bi bi-upload"></i> @lang('dashboard.upload')
                                </button>
                                <button type="button" class="btn btn-sm btn-danger remove-media">
                                    <i class="bi bi-trash"></i> @lang('dashboard.delete')
                                </button>
                            </div>

                            <input type="file" name="photo_attachments[]" class="media-input d-none" accept="image/*"
                                multiple>
                            <input type="file" id="camera-photo-input" accept="image/*" capture="environment"
                                class="d-none">
                            <input type="hidden" class="remove-flag" name="remove_photo" value="0">
                        </div>
                    </div>

                    {{-- AUDIO --}}
                    <div class="col-md-4">
                        <label class="form-label">🎵 @lang('dashboard.audio_attachment')</label>
                        <div class="media-upload-container" data-type="audio" data-audios='@json($audioUrls)'>

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
                                <button type="button" class="btn btn-sm btn-primary capture-media">
                                    <i class="bi bi-mic"></i> @lang('dashboard.record')
                                </button>
                                <button type="button" class="btn btn-sm btn-secondary upload-media">
                                    <i class="bi bi-upload"></i> @lang('dashboard.upload')
                                </button>
                                <button type="button" class="btn btn-sm btn-danger remove-media">
                                    <i class="bi bi-trash"></i> @lang('dashboard.delete')
                                </button>
                            </div>

                            <input type="file" name="audio_attachments[]" class="media-input d-none" accept="audio/*"
                                multiple>
                            <input type="hidden" class="remove-flag" name="remove_audio" value="0">
                        </div>
                    </div>

                    {{-- VIDEO --}}
                    <div class="col-md-4">
                        <label class="form-label">🎬 @lang('dashboard.video_attachment')</label>
                        <div id="video-slider" class="video-slider {{ count($videoUrls) ? '' : 'd-none' }}"
                            data-initial='@json($videoUrls)'>
                            <div class="slider-viewport">
                                <video class="slider-video" playsinline controls
                                    style="width:100%;height:auto;object-fit:contain;"></video>
                            </div>
                            <button type="button" class="slider-nav prev">‹</button>
                            <button type="button" class="slider-nav next">›</button>
                            <div class="slider-dots"></div>
                        </div>

                        <div class="d-grid gap-2 mt-2">
                            <button type="button" id="capture-video-btn" class="btn btn-sm btn-primary">
                                🎥 @lang('dashboard.start_recording_video')
                            </button>
                            <input type="hidden" name="uploaded_videos[]" id="uploaded_videos">

                            <input type="file" id="camera-video-input" accept="video/*" capture class="d-none">
                            <input type="file" id="pond-video" name="video_attachments[]" accept="video/*" multiple>
                            <input type="hidden" class="remove-flag" name="remove_video" value="0">
                            <button type="button" class="btn btn-sm btn-danger remove-video-all">
                                <i class="bi bi-trash"></i> @lang('dashboard.delete')
                            </button>
                        </div>
                        <div id="video-error" class="text-danger small mt-1" style="display:none"></div>
                    </div>
                </div>

                {{-- SUBMIT --}}
                <div class="card-footer d-flex justify-content-end py-6 px-9">
                    <button type="submit" class="btn btn-primary">
                        @lang('dashboard.save_changes')
                    </button>
                </div>
            </form>
        </div>
    </div>
    <div id="photo-lightbox" class="lightbox d-none">
        <span class="close">&times;</span>
        <img class="lightbox-img" alt="photo">
        <button class="nav prev"></button>
        <button class="nav next"></button>
    </div>

@endsection


@push('css')
    <link href="https://unpkg.com/filepond/dist/filepond.min.css" rel="stylesheet">
    <style>
        .media-upload-container {
            border: 1px dashed #ddd;
            border-radius: 4px;
            padding: 10px;
            background: #f9f9f9
        }

        .remove-media {
            width: 100%
        }

        .preview-audio-container audio,
        .preview-video-container video {
            max-width: 100%;
            border-radius: 8px;
            box-shadow: 0 2px 6px rgba(0, 0, 0, .2)
        }

        /* PHOTO SLIDER */
        .photo-slider {
            position: relative;
            border: 1px solid #ddd;
            border-radius: 8px;
            overflow: hidden;
            background: #fff;
            box-shadow: 0 2px 6px rgba(0, 0, 0, .06)
        }

        .photo-slider .slider-viewport {
            height: 220px;
            display: flex;
            align-items: center;
            justify-content: center;
            background: #111 !important
        }

        .photo-slider .slider-img {
            max-width: 100%;
            max-height: 100%;
            display: block;
            object-fit: contain;
            user-select: none;
            -webkit-user-drag: none
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
            cursor: pointer
        }

        .photo-slider .slider-nav:hover {
            background: rgba(0, 0, 0, .55)
        }

        .photo-slider .prev {
            left: 8px
        }

        .photo-slider .next {
            right: 8px
        }

        .photo-slider .slider-dots {
            display: flex;
            justify-content: center;
            gap: 6px;
            padding: 8px;
            background: #fff
        }

        .photo-slider .slider-dot {
            width: 8px;
            height: 8px;
            border-radius: 50%;
            background: #cfcfcf;
            cursor: pointer
        }

        .photo-slider .slider-dot.active {
            background: #333
        }

        /* VIDEO SLIDER */
        .video-slider {
            position: relative;
            border: 1px solid #ddd;
            border-radius: 8px;
            overflow: hidden;
            background: #fff;
            box-shadow: 0 2px 6px rgba(0, 0, 0, .06)
        }

        .video-slider .slider-viewport {
            height: 220px;
            display: flex;
            align-items: center;
            justify-content: center;
            background: #000
        }

        .video-slider .slider-video {
            width: 100%;
            max-height: 100%;
            object-fit: contain;
            background: #000
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
            cursor: pointer
        }

        .video-slider .slider-nav:hover {
            background: rgba(0, 0, 0, .55)
        }

        .video-slider .prev {
            left: 8px
        }

        .video-slider .next {
            right: 8px
        }

        .video-slider .slider-dots {
            display: flex;
            justify-content: center;
            gap: 6px;
            padding: 8px;
            background: #fff
        }

        .video-slider .slider-dot {
            width: 8px;
            height: 8px;
            border-radius: 50%;
            background: #cfcfcf;
            cursor: pointer
        }

        .video-slider .slider-dot.active {
            background: #333
        }

        /* AUDIO STYLE */
        .preview-audio-list audio {
            display: block;
            width: 100%;
            margin-top: .25rem
        }

        /* LIGHTBOX */
        .lightbox {
            position: fixed;
            inset: 0;
            background: rgba(0, 0, 0, .9);
            display: flex;
            align-items: center;
            justify-content: center;
            z-index: 9999
        }

        .lightbox.d-none {
            display: none
        }

        .lightbox img {
            max-width: 90%;
            max-height: 90%;
            object-fit: contain
        }

        .lightbox .close {
            position: absolute;
            top: 20px;
            right: 30px;
            font-size: 36px;
            color: #fff;
            cursor: pointer
        }

        .lightbox .nav {
            position: absolute;
            top: 50%;
            transform: translateY(-50%);
            font-size: 48px;
            color: #fff;
            background: transparent;
            border: none;
            cursor: pointer
        }

        .lightbox .nav.prev {
            left: 20px;
            right: auto
        }

        .lightbox .nav.next {
            right: 20px;
            left: auto
        }

        /* RTL */
        [dir="rtl"] .lightbox .nav.prev {
            right: auto;
            left: 20px
        }

        [dir="rtl"] .lightbox .nav.next {
            left: auto;
            right: 20px
        }

        [dir="rtl"] .photo-slider .prev,
        [dir="rtl"] .video-slider .prev,
        [dir="rtl"] .lightbox .nav.prev {
            right: 8px;
            left: auto;
            transform: translateY(-50%) scaleX(-1)
        }

        [dir="rtl"] .photo-slider .next,
        [dir="rtl"] .video-slider .next,
        [dir="rtl"] .lightbox .nav.next {
            left: 8px;
            right: auto;
            transform: translateY(-50%) scaleX(-1)
        }

        .photo-slider .slider-nav,
        .video-slider .slider-nav {
            font-size: 20px;
            font-weight: bold
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
                'video/mp4', 'video/quicktime', 'video/hevc', 'video/webm', 'video/avi',
                'video/x-matroska', 'video/x-flv', 'video/3gpp', 'video/3gpp2', 'video/mpeg',
                'video/mpg', 'video/x-ms-wmv', 'video/x-m4v'
            ];

            function cleanMime(type) {
                const t = (type || '').split(';')[0].trim();
                return t || 'video/webm';
            }

            // ===== Helper لإيجاد أقرب remove-flag محلياً =====
            function findRemoveFlagFrom(el) {
                if (!el) return null;
                const root = el.closest('.col-md-4') || el.closest('.media-upload-container') || document;
                return root.querySelector('.remove-flag');
            }

            /* =================== PHOTO =================== */
            (function initPhoto() {
                const box = document.querySelector('[data-type="photo"]');
                if (!box) return;

                const input = box.querySelector('.media-input');
                const previewSingle = box.querySelector('.preview-image-container');
                const captureBtn = box.querySelector('.capture-media');
                const uploadBtn = box.querySelector('.upload-media');
                const removeFlag = box.querySelector('.remove-flag');
                const actionsWrap = box.querySelector('.d-grid, .btn-group');

                // slider
                const sliderWrap = document.createElement('div');
                sliderWrap.className = 'photo-slider mb-2 d-none';
                sliderWrap.innerHTML = `
      <div class="slider-viewport"><img class="slider-img" alt="photo"></div>
      <button type="button" class="slider-nav prev" aria-label="Prev">‹</button>
      <button type="button" class="slider-nav next" aria-label="Next">›</button>
      <div class="slider-dots"></div>`;
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

                // lightbox (نضمن وجوده)
                let lightbox = document.getElementById('photo-lightbox');
                if (!lightbox) {
                    lightbox = document.createElement('div');
                    lightbox.id = 'photo-lightbox';
                    lightbox.className = 'lightbox d-none';
                    lightbox.innerHTML = `
        <span class="close">&times;</span>
        <img class="lightbox-img" alt="photo">
        <button class="nav prev"></button>
        <button class="nav next"></button>`;
                    document.body.appendChild(lightbox);
                }
                const lightImg = lightbox.querySelector('.lightbox-img');
                const lbClose = lightbox.querySelector('.close');
                const lbPrev = lightbox.querySelector('.nav.prev');
                const lbNext = lightbox.querySelector('.nav.next');

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

                function addPhotoToSlider(url) {
                    photos.push(url);
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
                imgEl.addEventListener('click', () => {
                    if (!photos.length) return;
                    lightbox.classList.remove('d-none');
                    lightImg.src = photos[current];
                });
                lbClose.addEventListener('click', () => lightbox.classList.add('d-none'));
                lbPrev.addEventListener('click', () => {
                    current = (current - 1 + photos.length) % photos.length;
                    lightImg.src = photos[current];
                });
                lbNext.addEventListener('click', () => {
                    current = (current + 1) % photos.length;
                    lightImg.src = photos[current];
                });
                document.addEventListener('keydown', e => {
                    if (e.key === 'Escape') lightbox.classList.add('d-none');
                    if (e.key === 'ArrowLeft') lbPrev.click();
                    if (e.key === 'ArrowRight') lbNext.click();
                });

                // compression
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

                const fileKey = f => `${f.name}__${f.size}__${f.lastModified||0}__${f.type}`;
                let photoFilesState = [];

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

                function resetState() {
                    photoFilesState = [];
                    syncInputFromState();
                }

                function openGalleryPicker() {
                    const picker = document.createElement('input');
                    picker.type = 'file';
                    picker.accept = 'image/*';
                    picker.multiple = true;
                    picker.addEventListener('change', async () => {
                        const files = Array.from(picker.files || []);
                        for (const f of files) {
                            let finalFile = f;
                            try {
                                finalFile = await compressImage(f, 0.7);
                            } catch {}
                            const added = addFileUnique(finalFile);
                            if (added) addPhotoToSlider(URL.createObjectURL(finalFile));
                        }
                        if (removeFlag) removeFlag.value = '0';
                        picker.value = '';
                    });
                    picker.click();
                }
                uploadBtn?.addEventListener('click', openGalleryPicker);

                const camPhotoInput = document.getElementById('camera-photo-input');
                captureBtn?.addEventListener('click', () => {
                    if (isMobile && camPhotoInput) {
                        camPhotoInput.click();
                        return;
                    }
                    (async () => {
                        try {
                            const stream = await navigator.mediaDevices.getUserMedia({
                                video: {
                                    facingMode: {
                                        ideal: 'environment'
                                    },
                                    width: {
                                        ideal: 1280
                                    },
                                    height: {
                                        ideal: 720
                                    },
                                    aspectRatio: {
                                        ideal: 16 / 9
                                    }
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
                                try {
                                    stream.getTracks().forEach(t => t.stop());
                                } catch {}
                                canvas.toBlob(async blob => {
                                    if (!blob) {
                                        overlay.remove();
                                        return;
                                    }
                                    let file = new File([blob],
                                        'photo_' + Date.now() +
                                        '.jpg', {
                                            type: 'image/jpeg'
                                        });
                                    try {
                                        file = await compressImage(file,
                                            0.7);
                                    } catch {}
                                    const added = addFileUnique(file);
                                    if (added) addPhotoToSlider(URL
                                        .createObjectURL(file));
                                    if (removeFlag) removeFlag.value =
                                        '0';
                                    overlay.remove();
                                }, 'image/jpeg', .95);
                            });
                        } catch (err) {
                            alert('Camera error: ' + (err?.message || err));
                        }
                    })();
                });

                camPhotoInput?.addEventListener('change', async (e) => {
                    const files = Array.from(e.target.files || []);
                    for (const f of files) {
                        let finalFile = f;
                        try {
                            finalFile = await compressImage(f, 0.7);
                        } catch {}
                        const added = addFileUnique(finalFile);
                        if (added) addPhotoToSlider(URL.createObjectURL(finalFile));
                    }
                    if (removeFlag) removeFlag.value = '0';
                    camPhotoInput.value = '';
                });

                box.querySelector('.remove-media')?.addEventListener('click', () => {
                    photos = [];
                    current = 0;
                    render();
                    resetState();
                    if (removeFlag) removeFlag.value = '1';
                    if (previewSingle) previewSingle.style.display = 'none';
                });

                // swipe
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

                render();
            })();




            /* =============== VIDEO (FilePond + Slider) =============== */
            if (window.FilePond) {
                try {
                    FilePond.registerPlugin(
                        window.FilePondPluginFileValidateType || (() => {}),
                        window.FilePondPluginFileValidateSize || (() => {})
                    );
                } catch (_) {}
            }

            const elVideo = document.getElementById('pond-video');
            const sliderRoot = document.getElementById('video-slider');
            const player = sliderRoot?.querySelector('.slider-video');
            const btnPrev = sliderRoot?.querySelector('.prev');
            const btnNext = sliderRoot?.querySelector('.next');
            const dotsRoot = sliderRoot?.querySelector('.slider-dots');
            const removeVideoFlag = findRemoveFlagFrom(sliderRoot) || findRemoveFlagFrom(elVideo);
            const formEl = elVideo?.closest('form');

            const isRTL_V = document.documentElement.dir === 'rtl' || getComputedStyle(document.body).direction ===
                'rtl';
            if (btnPrev && btnNext) {
                btnPrev.innerHTML = isRTL_V ? '›' : '‹';
                btnNext.innerHTML = isRTL_V ? '‹' : '›';
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
                            if (isRTL_V) {
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

            /* التقاط/تسجيل/اختيار فيديو (مختصر – نفس منطقك) */
            (function initVideoUniversal() {
                const btn = document.getElementById('capture-video-btn');
                const camInput = document.getElementById('camera-video-input');
                if (camInput) camInput.multiple = true;

                function handlePickedVideos(e) {
                    const files = Array.from(e.target.files || []);
                    if (!files.length) return;
                    files.forEach(f => {
                        if (window.pondVideo) window.pondVideo.addFile(f);
                    });
                    if (removeVideoFlag) removeVideoFlag.value = '0';
                    e.target.value = '';
                }

                if (!btn) {
                    camInput?.addEventListener('change', handlePickedVideos);
                    return;
                }

                const isMobile = /Mobi|Android|iPhone|iPad/i.test(navigator.userAgent);
                if (isMobile) {
                    btn.addEventListener('click', () => camInput?.click());
                    camInput?.addEventListener('change', handlePickedVideos);
                } else {
                    let recorder = null,
                        chunks = [],
                        stream = null;
                    const playerLive = document.querySelector('#video-slider .slider-video');

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
                                        ideal: 'environment'
                                    },
                                    width: {
                                        ideal: 1280
                                    },
                                    height: {
                                        ideal: 720
                                    },
                                    aspectRatio: {
                                        ideal: 16 / 9
                                    }
                                },
                                audio: true
                            });

                            if (sliderRoot) sliderRoot.classList.remove('d-none');
                            if (playerLive) {
                                playerLive.srcObject = stream;
                                playerLive.muted = true;
                                playerLive.play();
                            }

                            chunks = [];
                            let mimeType = 'video/webm;codecs=vp8,opus';
                            if (!MediaRecorder.isTypeSupported(mimeType)) {
                                mimeType = 'video/webm';
                                if (!MediaRecorder.isTypeSupported(mimeType)) mimeType =
                                'video/mp4';
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
                                if (playerLive) {
                                    playerLive.srcObject = null;
                                }
                                const rawBlob = new Blob(chunks, {
                                    type: mimeType
                                });
                                const type = (rawBlob.type || '').split(';')[0] || 'video/webm';
                                const file = new File([rawBlob], 'recorded_' + Date.now() + (
                                    type === 'video/mp4' ? '.mp4' : '.webm'), {
                                    type
                                });
                                if (window.pondVideo) window.pondVideo.addFile(file);
                                if (removeVideoFlag) removeVideoFlag.value = '0';
                            };

                            recorder.start();
                            btn.className = 'btn btn-sm btn-danger w-100';
                            btn.innerHTML =
                            '<i class="bi bi-stop"></i> {{ __('dashboard.stop') }}';
                        } catch (e) {
                            alert('Camera error: ' + e.message);
                        }
                    });
                }
            })();

            /* Helpers لإدارة hidden inputs */
            function addHiddenVideoKey(key) {
                if (!formEl || !key) return;
                // تفادي التكرار
                if (formEl.querySelector(`input[name="uploaded_videos[]"][value="${key.replace(/"/g,'&quot;')}"]`))
                    return;
                const hidden = document.createElement('input');
                hidden.type = 'hidden';
                hidden.name = 'uploaded_videos[]';
                hidden.value = key;
                hidden.dataset.videoKey = key;
                formEl.appendChild(hidden);
            }

            function removeHiddenVideoKey(key) {
                if (!formEl || !key) return;
                const el = formEl.querySelector(
                    `input[name="uploaded_videos[]"][value="${key.replace(/"/g,'&quot;')}"]`);
                if (el) el.remove();
            }

            /* FilePond */
            if (elVideo && window.FilePond) {
                const ACCEPTED_VIDEOS = [
                    'video/mp4', 'video/quicktime', 'video/hevc', 'video/webm', 'video/avi',
                    'video/x-matroska', 'video/x-flv', 'video/3gpp', 'video/3gpp2', 'video/mpeg',
                    'video/mpg', 'video/x-ms-wmv', 'video/x-m4v'
                ];
                const CSRF = '{{ csrf_token() }}';

                const pondVideo = FilePond.create(elVideo, {
                    allowMultiple: true,
                    credits: false,
                    acceptedFileTypes: ACCEPTED_VIDEOS,
                    maxFileSize: '500MB',
                    instantUpload: true,
                    allowRevert: false, // ⛔ ما عناش endpoint revert
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
                                    const cleanType = (file.type || '').split(';')[0].trim() ||
                                        'video/webm';
                                    // 👇 وحّد prefix على مزاجك؛ ينجم يبقى القديم، المهم نفس الـ key يتخزّن في DB
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
                                                prefix: 'uploads/equipment-directory/videos'
                                            })
                                        });
                                    if (!createRes.ok) throw new Error(
                                    'createMultipart failed');
                                    const createJson = await createRes.json();
                                    uploadId = createJson.uploadId;
                                    key = createJson.key;

                                    const CHUNK_SIZE = {{ 10 * 1024 * 1024 }}; // 10MB
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
                                    load(key); // ✅ serverId = key
                                    addHiddenVideoKey(key); // ✅ حقن hidden input في الفورم
                                    if (removeVideoFlag) removeVideoFlag.value = '0';
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
                    if (removeVideoFlag) removeVideoFlag.value = '0';
                });

                // 🧹 عند إزالة ملف من FilePond، نحذف الـ hidden المقابل (serverId = key)
                pondVideo.on('removefile', (fileItem) => {
                    if (!fileItem) return;
                    removeVideoItem(fileItem.id);
                    const key = fileItem.serverId;
                    if (key) removeHiddenVideoKey(key);
                });

                window.pondVideo = pondVideo;

                document.querySelector('.remove-video-all')?.addEventListener('click', () => {
                    if (window.pondVideo) window.pondVideo.removeFiles();
                    // 🧹 امسح كل hidden inputs الخاصة بالفيديوهات المرفوعة
                    formEl?.querySelectorAll('input[name="uploaded_videos[]"]').forEach(i => i.remove());
                    videoItems = [];
                    vCurrent = 0;
                    renderVideo();
                    if (removeVideoFlag) removeVideoFlag.value = '1';
                });
            }






            /* =============== AUDIO (Record/Upload + Preview) =============== */
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

                let recorder = null,
                    chunks = [],
                    indicator = null,
                    stream = null;
                let fallbackRecorder = null;

                uploadBtn?.addEventListener('click', () => input?.click());

                input?.addEventListener('change', () => {
                    if (!input.files?.length) return;
                    Array.from(input.files).forEach(file => {
                        const audio = document.createElement('audio');
                        audio.controls = true;
                        audio.className = 'w-100 mb-2';
                        audio.src = URL.createObjectURL(file);
                        list.appendChild(audio);
                    });
                    previewBox.style.display = 'block';
                    if (removeFlag) removeFlag.value = '0';
                });

                captureBtn?.addEventListener('click', async () => {
                    if ((recorder && recorder.state === 'recording') || fallbackRecorder) {
                        if (recorder) recorder.stop();
                        if (fallbackRecorder) fallbackRecorder.stop();
                        captureBtn.className = 'btn btn-sm btn-primary';
                        captureBtn.innerHTML =
                            '<i class="bi bi-mic"></i> {{ __('dashboard.record') }}';
                        if (indicator) {
                            indicator.remove();
                            indicator = null;
                        }
                        return;
                    }
                    try {
                        stream = await navigator.mediaDevices.getUserMedia({
                            audio: true
                        });
                        chunks = [];
                        let mimeType = 'audio/webm';
                        if (!window.MediaRecorder || !MediaRecorder.isTypeSupported(mimeType)) {
                            if (window.MediaRecorder && MediaRecorder.isTypeSupported('audio/mp4'))
                                mimeType = 'audio/mp4';
                            else if (window.MediaRecorder && MediaRecorder.isTypeSupported(
                                    'audio/aac')) mimeType = 'audio/aac';
                            else mimeType = ''; // نطيح للفولباك WAV
                        }

                        if (window.MediaRecorder && mimeType) {
                            recorder = new MediaRecorder(stream, {
                                mimeType
                            });
                            recorder.ondataavailable = (e) => {
                                if (e.data.size > 0) chunks.push(e.data);
                            };
                            recorder.onstop = () => {
                                try {
                                    stream.getTracks().forEach(t => t.stop());
                                } catch (_) {}
                                const blob = new Blob(chunks, {
                                    type: mimeType
                                });
                                const ext = mimeType.includes('mp4') ? '.m4a' : mimeType
                                    .includes('aac') ? '.aac' : '.webm';
                                saveFile(blob, ext, mimeType);
                            };
                            recorder.start();
                        } else {
                            // fallback WAV
                            fallbackRecorder = startWavRecorder(stream, saveFile);
                        }

                        captureBtn.className = 'btn btn-sm btn-danger';
                        captureBtn.innerHTML =
                            '<i class="bi bi-stop"></i> {{ __('dashboard.stop') }}';

                        previewBox.style.display = 'block';
                        indicator = document.createElement('div');
                        indicator.className = 'text-danger mt-2 small';
                        indicator.innerText = '🔴 Recording...';
                        list.appendChild(indicator);
                    } catch (e) {
                        alert('Mic error: ' + (e?.message || e));
                    }
                });

                function saveFile(blob, ext, type) {
                    const file = new File([blob], 'audio_' + Date.now() + ext, {
                        type
                    });
                    const dt = new DataTransfer();
                    if (input.files) Array.from(input.files).forEach(f => dt.items.add(f));
                    dt.items.add(file);
                    input.files = dt.files;

                    if (indicator) {
                        indicator.remove();
                        indicator = null;
                    }
                    const audio = document.createElement('audio');
                    audio.controls = true;
                    audio.className = 'w-100 mb-2';
                    audio.src = URL.createObjectURL(file);
                    list.appendChild(audio);
                    previewBox.style.display = 'block';
                    if (removeFlag) removeFlag.value = '0';
                }

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
                            const wavBlob = encodeWav(buffer, 44100);
                            callback(wavBlob, '.wav', 'audio/wav');
                            fallbackRecorder = null;
                        }
                    };
                }

                function encodeWav(chunks, sampleRate) {
                    const length = chunks.reduce((acc, cur) => acc + cur.length, 0);
                    const buff = new Float32Array(length);
                    let off = 0;
                    chunks.forEach(cur => {
                        buff.set(cur, off);
                        off += cur.length;
                    });
                    const wavBuffer = new ArrayBuffer(44 + buff.length * 2);
                    const view = new DataView(wavBuffer);

                    function writeString(view, offset, str) {
                        for (let i = 0; i < str.length; i++) view.setUint8(offset + i, str.charCodeAt(i));
                    }
                    writeString(view, 0, 'RIFF');
                    view.setUint32(4, 36 + buff.length * 2, true);
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
                    view.setUint32(40, buff.length * 2, true);
                    let idx = 44;
                    for (let i = 0; i < buff.length; i++, idx += 2) {
                        const s = Math.max(-1, Math.min(1, buff[i]));
                        view.setInt16(idx, s < 0 ? s * 0x8000 : s * 0x7FFF, true);
                    }
                    return new Blob([view], {
                        type: 'audio/wav'
                    });
                }

                box.querySelector('.remove-media')?.addEventListener('click', () => {
                    previewBox.style.display = 'none';
                    list.innerHTML = '';
                    if (input) input.value = '';
                    if (removeFlag) removeFlag.value = '1';
                });
            })();

        });
    </script>
@endpush
