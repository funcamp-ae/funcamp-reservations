@extends('dashboard.layouts.app')
@section('pageTitle', isset($campReport) ? __('dashboard.edit_camp_report') : __('dashboard.create_camp_report'))

@section('content')
    <div class="card mb-5 mb-xl-10">
        <div class="card-header">
            <h3 class="
            -title">@lang('dashboard.' . (isset($campReport) ? 'edit_camp_report' : 'create_camp_report'))</h3>
        </div>

        <div class="card-body">
            <form method="POST"
                action="{{ isset($campReport) ? route('camp-reports.update', $campReport) : route('camp-reports.store') }}"
                enctype="multipart/form-data">
                @csrf
                @if (isset($campReport))
                    @method('PUT')
                @endif

                <div class="row g-5 mb-8">
                    <div class="col-md-6">
                        <div class="mb-6">
                            <label class="form-label required">@lang('dashboard.report_date')</label>
                            <input type="date" name="report_date" class="form-control" required
                                value="{{ old('report_date', isset($campReport) ? $campReport->report_date->format('Y-m-d') : '') }}">
                        </div>

                        <div class="mb-6">
                            <label class="form-label ">@lang('dashboard.service')</label>
                            <select name="service_id" class="form-select">
                                <option value="">@lang('dashboard.select_service')</option>
                                @foreach ($services as $service)
                                    <option value="{{ $service->id }}"
                                        {{ old('service_id', isset($campReport) ? $campReport->service_id : '') == $service->id ? 'selected' : '' }}>
                                        {{ $service->name }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                    </div>

                    <div class="col-md-6">
                        <div class="mb-6">
                            <label class="form-label ">@lang('dashboard.camp_name')</label>
                            <input type="text" name="camp_name" class="form-control"
                                value="{{ old('camp_name', $campReport->camp_name ?? '') }}">
                        </div>

                        <div class="mb-6">
                            <label class="form-label">@lang('dashboard.general_notes')</label>
                            <textarea name="general_notes" rows="1" class="form-control">{{ old('general_notes', $campReport->general_notes ?? '') }}</textarea>
                        </div>
                    </div>
                </div>

                <div class="mb-8">
                    <h4 class="mb-4">@lang('dashboard.report_items')</h4>
                    <div id="items-container">
                        @if (old('items', null) || isset($campReport))
                            @foreach (old('items', isset($campReport) ? $campReport->items : []) as $index => $item)
                                @php
                                    $isModel = is_object($item) && method_exists($item, 'getAttribute');
                                    $itemId = $isModel ? $item->id : $item['id'] ?? null;

                                    // ✅ استخدم الأعمدة والقيم الجديدة
                                    $photos = $isModel ? $item->media->where('type', 'photo')->values() : collect();
                                    $audios = $isModel ? $item->media->where('type', 'audio')->values() : collect();
                                    $videos = $isModel ? $item->media->where('type', 'video')->values() : collect();

                                    // ✅ استعمل path بدل file_path
                                    $photoUrls = json_encode(
                                        $photos
                                            ->pluck('path')
                                            ->map(fn($p) => Storage::disk('hetzner')->url($p))
                                            ->values(),
                                    );
                                    $audioUrls = $audios
                                        ->map(fn($m) => Storage::disk('hetzner')->url($m->path))
                                        ->values()
                                        ->toJson();
                                    $videoUrls = $videos
                                        ->map(fn($m) => Storage::disk('hetzner')->url($m->path))
                                        ->values()
                                        ->toJson();
                                @endphp


                                <div class="card mb-4 item-row" data-index="{{ $index }}">
                                    <div class="card-header d-flex justify-content-between align-items-center">
                                        <h5 class="mb-0">#{{ $index + 1 }}</h5>
                                        <button type="button" class="btn btn-sm btn-icon btn-danger remove-item">
                                            <i class="bi bi-trash"></i>
                                        </button>
                                    </div>

                                    <div class="card-body">
                                        @if ($itemId)
                                            <input type="hidden" name="items[{{ $index }}][id]"
                                                value="{{ $itemId }}">
                                        @endif

                                        <div class="row">
                                            <div class="col-md-6 mb-3">
                                                <label class="form-label required">@lang('dashboard.item_name')</label>
                                                <input type="text" name="items[{{ $index }}][item_name]"
                                                    class="form-control" required
                                                    value="{{ $isModel ? $item->item_name : $item['item_name'] ?? '' }}">
                                            </div>
                                            <div class="col-md-6 mb-3">
                                                <label class="form-label">@lang('dashboard.notes')</label>
                                                <textarea name="items[{{ $index }}][notes]" rows="1" class="form-control">{{ $isModel ? $item->notes ?? '' : $item['notes'] ?? '' }}</textarea>
                                            </div>
                                        </div>

                                        <div class="row g-4">
                                            {{-- PHOTOS --}}
                                            <div class="col-md-4">
                                                <label class="form-label">@lang('dashboard.photo_attachment')</label>
                                                <div class="media-upload-container photo-box" data-type="photo"
                                                    data-existing='@json(json_decode($photoUrls, true))'>
                                                    <input type="hidden" name="items[{{ $index }}][remove_photos]"
                                                        value="0" class="remove-flag">

                                                    {{-- Slider --}}
                                                    <div class="photo-slider mb-2 d-none">
                                                        <div class="slider-viewport">
                                                            <img class="slider-img" alt="photo">
                                                        </div>
                                                        <button type="button" class="slider-nav prev"
                                                            aria-label="Prev">‹</button>
                                                        <button type="button" class="slider-nav next"
                                                            aria-label="Next">›</button>
                                                        <div class="slider-dots"></div>
                                                    </div>

                                                    <div class="d-grid gap-2">
                                                        <button type="button"
                                                            class="btn btn-sm btn-primary capture-media w-100">
                                                            <i class="bi bi-camera"></i> @lang('dashboard.capture_photo')
                                                        </button>
                                                        <button type="button"
                                                            class="btn btn-sm btn-secondary upload-media w-100">
                                                            <i class="bi bi-upload"></i> @lang('dashboard.upload')
                                                        </button>
                                                        <button type="button"
                                                            class="btn btn-sm btn-outline-danger remove-media-all w-100">
                                                            <i class="bi bi-trash"></i> @lang('dashboard.delete')
                                                        </button>
                                                    </div>


                                                    <input type="file" name="items[{{ $index }}][photos][]"
                                                        class="media-input d-none" accept="image/*" multiple>
                                                    {{-- مدخل منفصل للكاميرا للموبايل --}}
                                                    <input type="file" class="d-none camera-photo-input" accept="image/*"
                                                        capture="environment" multiple>
                                                </div>
                                            </div>

                                            {{-- AUDIOS --}}
                                            <div class="col-md-4">
                                                <label class="form-label">@lang('dashboard.audio_attachment')</label>
                                                <div class="media-upload-container audio-box" data-type="audio"
                                                    data-existing='@json(json_decode($audioUrls, true))'>
                                                    <input type="hidden"
                                                        name="items[{{ $index }}][remove_audios]" value="0"
                                                        class="remove-flag">

                                                    <div class="preview-audio-container mb-2" style="display:none;">
                                                        <div class="preview-audio-list"></div>
                                                    </div>

                                                    <div class="d-grid gap-2">
                                                        <button type="button"
                                                            class="btn btn-sm btn-primary capture-media w-100">
                                                            <i class="bi bi-mic"></i> @lang('dashboard.record')
                                                        </button>
                                                        <button type="button"
                                                            class="btn btn-sm btn-secondary upload-media w-100">
                                                            <i class="bi bi-upload"></i> @lang('dashboard.upload')
                                                        </button>
                                                        <button type="button"
                                                            class="btn btn-sm btn-outline-danger remove-media w-100">
                                                            <i class="bi bi-trash"></i> @lang('dashboard.delete')
                                                        </button>
                                                    </div>


                                                    <input type="file" name="items[{{ $index }}][audios][]"
                                                        class="media-input d-none" accept="audio/*" multiple>
                                                </div>
                                            </div>

                                            {{-- VIDEOS --}}
                                            <div class="col-md-4">
                                                <label class="form-label">@lang('dashboard.video_attachment')</label>
                                                <div class="media-upload-container video-box" data-type="video"
                                                    data-index="{{ $index }}"
                                                    data-existing='@json(json_decode($videoUrls, true))'>

                                                    <input type="hidden"
                                                        name="items[{{ $index }}][remove_videos]" value="0"
                                                        class="remove-flag">

                                                    {{-- Slider للفيديوهات --}}
                                                    <div class="video-slider mb-2 d-none">
                                                        <div class="slider-viewport">
                                                            <video class="slider-video" controls playsinline></video>
                                                        </div>
                                                        <button type="button" class="slider-nav prev"
                                                            aria-label="Prev">‹</button>
                                                        <button type="button" class="slider-nav next"
                                                            aria-label="Next">›</button>
                                                        <div class="slider-dots"></div>
                                                    </div>

                                                    <div class="d-grid gap-2 mb-2">
                                                        <button type="button"
                                                            class="btn btn-sm btn-primary capture-media w-100"
                                                            id="capture-video-btn-{{ $index }}">
                                                            🎥 @lang('dashboard.start_recording_video')
                                                        </button>

                                                        {{-- هذا هو الـ FilePond UI (بدلاً من زر upload) --}}
                                                        <input type="file" class="pond-video" accept="video/*"
                                                            multiple />

                                                        <button type="button"
                                                            class="btn btn-sm btn-outline-danger remove-video-all w-100">
                                                            <i class="bi bi-trash"></i> @lang('dashboard.delete')
                                                        </button>
                                                    </div>

                                                    {{-- input احتياطي + كاميرا الهاتف (يُضاف إلى FilePond) --}}
                                                    <input type="file" name="items[{{ $index }}][videos][]"
                                                        class="media-input d-none" accept="video/*" multiple>
                                                    <input type="file" class="d-none camera-video-input"
                                                        accept="video/*" capture="environment" multiple>

                                                    {{-- هنا تتجمع مفاتيح الـ multipart --}}
                                                    <div class="uploaded-keys"></div>
                                                    <input type="hidden" class="remove-video-flag" value="0">
                                                    <div class="text-danger small d-none video-error"></div>
                                                </div>
                                            </div>

                                        </div>
                                    </div>
                                </div>
                            @endforeach
                        @else
                            <div class="alert alert-info">@lang('dashboard.no_items_added')</div>
                        @endif
                    </div>

                    <button type="button" id="add-item" class="btn btn-primary">
                        <i class="bi bi-plus-circle"></i> @lang('dashboard.add_item')
                    </button>
                </div>

                <div class="card-footer d-flex justify-content-end py-6 px-9">
                    <button type="submit" class="btn btn-primary">
                        @lang('dashboard.' . (isset($campReport) ? 'update_report' : 'save_report'))
                    </button>
                </div>
            </form>
        </div>
    </div>

    {{-- LIGHTBOX للصور --}}
    <div id="photo-lightbox" class="lightbox d-none" aria-hidden="true">
        <button type="button" class="close" aria-label="Close">&times;</button>
        <button type="button" class="nav prev" aria-label="Prev">‹</button>
        <img class="lightbox-img" alt="lightbox">
        <button type="button" class="nav next" aria-label="Next">›</button>
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

        .remove-media,
        .remove-video-all {
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
        }

        .photo-slider .slider-dot {
            width: 8px;
            height: 8px;
            border-radius: 50%;
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
        }

        .video-slider .slider-dot {
            width: 8px;
            height: 8px;
            border-radius: 50%;
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

            // ===== Helpers عامة =====
            function isRTL() {
                return document.documentElement.dir === 'rtl' || getComputedStyle(document.body).direction ===
                    'rtl';
            }

            function createHidden(name, value) {
                const i = document.createElement('input');
                i.type = 'hidden';
                i.name = name;
                i.value = value;
                return i;
            }

            // ====== PHOTO PER BOX ======
            function initPhotoBox(box) {

                const uploadBtn = box.querySelector('.upload-media');
                const captureBtn = box.querySelector('.capture-media');
                const removeBtn = box.querySelector('.remove-media-all');
                const input = box.querySelector('.media-input');
                const camInput = box.querySelector('.camera-photo-input');
                const removeFlg = box.querySelector('.remove-flag');

                const slider = box.querySelector('.photo-slider');
                const viewport = slider.querySelector('.slider-viewport');
                const imgEl = slider.querySelector('.slider-img');
                const prevBtn = slider.querySelector('.prev');
                const nextBtn = slider.querySelector('.next');
                const dotsBox = slider.querySelector('.slider-dots');

                prevBtn.innerHTML = isRTL() ? '›' : '‹';
                nextBtn.innerHTML = isRTL() ? '‹' : '›';

                // State
                const existing = (() => {
                    try {
                        const j = JSON.parse(box.dataset.existing || '[]');
                        return Array.isArray(j) ? j : [];
                    } catch (_) {
                        return [];
                    }
                })();
                let photos = existing.slice(); // urls
                let current = 0;
                let fileState = []; // Files selected (to sync input.files)

                function syncInput() {
                    const dt = new DataTransfer();
                    fileState.forEach(f => dt.items.add(f));
                    input.files = dt.files;
                }

                function addFileToStateUnique(file) {
                    const key = `${file.name}__${file.size}__${file.lastModified||0}__${file.type}`;
                    if (fileState.some(f => `${f.name}__${f.size}__${f.lastModified||0}__${f.type}` === key))
                        return false;
                    fileState.push(file);
                    syncInput();
                    return true;
                }

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
                        slider.classList.add('d-none');
                        return;
                    }
                    slider.classList.remove('d-none');
                    imgEl.src = photos[current];
                    renderDots();
                }

                function addPhotoUrl(url) {
                    photos.push(url);
                    current = photos.length - 1;
                    render();
                }

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
                            canvas.getContext('2d').drawImage(img, 0, 0, width, height);
                            canvas.toBlob(b => {
                                URL.revokeObjectURL(url);
                                if (!b) return reject('Compression failed');
                                const name = (file.name || 'photo').replace(/\.[^.]+$/, '') +
                                    '.jpg';
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

                // events
                uploadBtn?.addEventListener('click', () => input.click());
                input?.addEventListener('change', async () => {
                    if (!input.files?.length) return;
                    for (const f of Array.from(input.files)) {
                        let ff = f;
                        try {
                            ff = await compressImage(f, 0.7);
                        } catch (_) {}
                        const ok = addFileToStateUnique(ff);
                        if (ok) addPhotoUrl(URL.createObjectURL(ff));
                    }
                    if (removeFlg) removeFlg.value = '0';
                });

                captureBtn?.addEventListener('click', () => {
                    if (isMobile) {
                        camInput?.click();
                        return;
                    }
                    // Desktop camera snap
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
                                    }
                                },
                                audio: false
                            });
                            const overlay = document.createElement('div');
                            overlay.style.cssText =
                                'position:absolute;inset:0;background:#000;display:flex;align-items:center;justify-content:center;z-index:5;';
                            const v = document.createElement('video');
                            v.autoplay = v.muted = true;
                            v.playsInline = true;
                            v.srcObject = stream;
                            v.style.cssText = 'width:100%;height:100%;object-fit:contain;';
                            const snap = document.createElement('button');
                            snap.type = 'button';
                            snap.className = 'btn btn-success';
                            snap.textContent = 'Snap';
                            Object.assign(snap.style, {
                                position: 'absolute',
                                bottom: '12px',
                                left: '50%',
                                transform: 'translateX(-50%)'
                            });
                            viewport.appendChild(overlay);
                            overlay.appendChild(v);
                            overlay.appendChild(snap);
                            slider.classList.remove('d-none');

                            snap.addEventListener('click', async () => {
                                const canvas = document.createElement('canvas');
                                canvas.width = v.videoWidth || 640;
                                canvas.height = v.videoHeight || 480;
                                canvas.getContext('2d').drawImage(v, 0, 0);
                                try {
                                    stream.getTracks().forEach(t => t.stop());
                                } catch (_) {}
                                canvas.toBlob(async blob => {
                                    if (!blob) {
                                        overlay.remove();
                                        return;
                                    }
                                    let file = new File([blob], 'photo_' +
                                        Date.now() + '.jpg', {
                                            type: 'image/jpeg'
                                        });
                                    try {
                                        file = await compressImage(file,
                                            0.7);
                                    } catch (_) {}
                                    const ok = addFileToStateUnique(file);
                                    if (ok) {
                                        addPhotoUrl(URL.createObjectURL(
                                            file));
                                        if (removeFlg) removeFlg.value =
                                            '0';
                                    }
                                    overlay.remove();
                                }, 'image/jpeg', .95);
                            });
                        } catch (e) {
                            alert('Camera error: ' + (e?.message || e));
                        }
                    })();
                });

                camInput?.addEventListener('change', async (e) => {
                    if (!e.target.files?.length) return;
                    for (const f of Array.from(e.target.files)) {
                        let ff = f;
                        try {
                            ff = await compressImage(f, 0.7);
                        } catch (_) {}
                        const ok = addFileToStateUnique(ff);
                        if (ok) addPhotoUrl(URL.createObjectURL(ff));
                    }
                    if (removeFlg) removeFlg.value = '0';
                    e.target.value = '';
                });

                removeBtn?.addEventListener('click', () => {
                    photos = [];
                    current = 0;
                    slider.classList.add('d-none');
                    imgEl.removeAttribute('src');
                    fileState = [];
                    syncInput();
                    if (removeFlg) removeFlg.value = '1';
                });

                // lightbox
                (function lightboxInit() {
                    const lb = document.getElementById('photo-lightbox');
                    const lbImg = lb.querySelector('.lightbox-img');
                    const lbClose = lb.querySelector('.close');
                    const lbPrev = lb.querySelector('.nav.prev');
                    const lbNext = lb.querySelector('.nav.next');
                    prevBtn.innerHTML = isRTL() ? '›' : '‹';
                    nextBtn.innerHTML = isRTL() ? '‹' : '›';

                    function goPrev() {
                        if (!photos.length) return;
                        current = (current - 1 + photos.length) % photos.length;
                        render();
                    }

                    function goNext() {
                        if (!photos.length) return;
                        current = (current + 1) % photos.length;
                        render();
                    }

                    prevBtn.addEventListener('click', goPrev);
                    nextBtn.addEventListener('click', goNext);

                    imgEl.addEventListener('click', () => {
                        if (!photos.length) return;
                        lb.classList.remove('d-none');
                        lbImg.src = photos[current];
                    });
                    lbClose.addEventListener('click', () => lb.classList.add('d-none'));
                    lbPrev.addEventListener('click', () => {
                        current = (current - 1 + photos.length) % photos.length;
                        lbImg.src = photos[current];
                    });
                    lbNext.addEventListener('click', () => {
                        current = (current + 1) % photos.length;
                        lbImg.src = photos[current];
                    });
                    document.addEventListener('keydown', e => {
                        if (lb.classList.contains('d-none')) return;
                        if (e.key === 'Escape') lb.classList.add('d-none');
                        if (e.key === 'ArrowLeft') lbPrev.click();
                        if (e.key === 'ArrowRight') lbNext.click();
                    });

                    // swipe
                    let sx = 0;
                    viewport.addEventListener('touchstart', e => sx = e.changedTouches[0].clientX, {
                        passive: true
                    });
                    viewport.addEventListener('touchend', e => {
                        const dx = e.changedTouches[0].clientX - sx;
                        if (Math.abs(dx) > 40) {
                            if (isRTL()) {
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
                })();

                // initial render
                if (photos.length) {
                    slider.classList.remove('d-none');
                    render();
                }
                // ====== LIGHTBOX للصور ======
(function lightboxInit() {
    const lb = document.getElementById('photo-lightbox');
    const lbImg = lb.querySelector('.lightbox-img');
    const lbClose = lb.querySelector('.close');
    const lbPrev = lb.querySelector('.nav.prev');
    const lbNext = lb.querySelector('.nav.next');
    let lbCurrent = 0;

    function openLightbox(index) {
        if (!photos.length) return;
        lbCurrent = index;
        lb.classList.remove('d-none');
        lbImg.src = photos[lbCurrent];
    }

    function showNext() {
        if (!photos.length) return;
        lbCurrent = (lbCurrent + 1) % photos.length;
        lbImg.src = photos[lbCurrent];
    }

    function showPrev() {
        if (!photos.length) return;
        lbCurrent = (lbCurrent - 1 + photos.length) % photos.length;
        lbImg.src = photos[lbCurrent];
    }

    imgEl.addEventListener('click', () => openLightbox(current));
    lbClose.addEventListener('click', () => lb.classList.add('d-none'));
    lbNext.addEventListener('click', showNext);
    lbPrev.addEventListener('click', showPrev);

    // ⌨️ الأسهم من لوحة المفاتيح
    document.addEventListener('keydown', e => {
        if (lb.classList.contains('d-none')) return;
        if (e.key === 'Escape') lb.classList.add('d-none');
        if (e.key === 'ArrowRight') showNext();
        if (e.key === 'ArrowLeft') showPrev();
    });

    // 📱 السحب (Swipe)
    let startX = 0;
    lb.addEventListener('touchstart', e => startX = e.touches[0].clientX, { passive: true });
    lb.addEventListener('touchend', e => {
        const dx = e.changedTouches[0].clientX - startX;
        if (Math.abs(dx) > 40) {
            if (dx > 0) showPrev();
            else showNext();
        }
    }, { passive: true });
})();

            }

            // ====== AUDIO PER BOX ======
            function initAudioBox(box) {
                const input = box.querySelector('.media-input');
                const uploadBtn = box.querySelector('.upload-media');
                const captureBtn = box.querySelector('.capture-media');
                const removeBtn = box.querySelector('.remove-media');
                const removeFlg = box.querySelector('.remove-flag');

                const previewBox = box.querySelector('.preview-audio-container');
                const list = box.querySelector('.preview-audio-list');


                async function compressAudio(blob, targetBitrate = 64000) {
                    const audioCtx = new(window.AudioContext || window.webkitAudioContext)();
                    const srcBuf = await blob.arrayBuffer();
                    const audioBuf = await audioCtx.decodeAudioData(srcBuf);

                    const offlineCtx = new OfflineAudioContext(
                        audioBuf.numberOfChannels,
                        audioBuf.length,
                        audioBuf.sampleRate
                    );
                    const source = offlineCtx.createBufferSource();
                    source.buffer = audioBuf;
                    source.connect(offlineCtx.destination);
                    source.start(0);
                    const renderedBuf = await offlineCtx.startRendering();

                    // إعادة التشفير بجودة أقل
                    return new Promise(resolve => {
                        const wavBlob = encodeWav([renderedBuf.getChannelData(0)], renderedBuf
                            .sampleRate);
                        resolve(wavBlob);
                    });
                }

                // حمّل الموجود من السيرفر
                const existing = (() => {
                    try {
                        const j = JSON.parse(box.dataset.existing || '[]');
                        return Array.isArray(j) ? j : [];
                    } catch (_) {
                        return [];
                    }
                })();
                if (existing.length) {
                    previewBox.style.display = 'block';
                    existing.forEach(u => {
                        const a = document.createElement('audio');
                        a.controls = true;
                        a.className = 'w-100 mb-2';
                        a.src = u;
                        list.appendChild(a);
                    });
                }

                uploadBtn?.addEventListener('click', () => input.click());
                input?.addEventListener('change', () => {
                    if (!input.files?.length) return;
                    previewBox.style.display = 'block';
                    Array.from(input.files).forEach(f => {
                        const a = document.createElement('audio');
                        a.controls = true;
                        a.className = 'w-100 mb-2';
                        a.src = URL.createObjectURL(f);
                        list.appendChild(a);
                    });
                    if (removeFlg) removeFlg.value = '0';
                });

                // تسجيل صوت
                let recorder = null,
                    fallback = null,
                    chunks = [],
                    stream = null,
                    indicator = null;
                captureBtn?.addEventListener('click', async () => {
                    if ((recorder && recorder.state === 'recording') || fallback) {
                        if (recorder) recorder.stop();
                        if (fallback) fallback.stop();
                        captureBtn.className = 'btn btn-sm btn-secondary';
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
                        let mime = 'audio/webm';
                        if (window.MediaRecorder && MediaRecorder.isTypeSupported(mime)) {
                            recorder = new MediaRecorder(stream, {
                                mimeType: mime,
                                videoBitsPerSecond: 800000, //  لتقليل حجم الملف ↓ من 4 ميغابت إلى 1 ميغابت
                                audioBitsPerSecond: 64000

                            });
                            recorder.ondataavailable = e => {
                                if (e.data.size > 0) chunks.push(e.data);
                            };
                            recorder.onstop = () => {
                                try {
                                    stream.getTracks().forEach(t => t.stop());
                                } catch (_) {}
                                const blob = new Blob(chunks, {
                                    type: mime
                                });
                                // بعد إنشاء blob
                                compressAudio(blob).then(compressedBlob => {
                                    const file = new File([compressedBlob], 'rec_' + Date
                                        .now() + ext, {
                                            type: mime
                                        });

                                });

                                const ext = '.webm';
                                const file = new File([blob], 'rec_' + Date.now() + ext, {
                                    type: mime
                                });
                                const dt = new DataTransfer();
                                if (input.files) Array.from(input.files).forEach(f => dt.items.add(
                                    f));
                                dt.items.add(file);
                                input.files = dt.files;
                                const a = document.createElement('audio');
                                a.controls = true;
                                a.className = 'w-100 mb-2';
                                a.src = URL.createObjectURL(file);
                                list.appendChild(a);
                                previewBox.style.display = 'block';
                                if (removeFlg) removeFlg.value = '0';
                            };
                            recorder.start();
                        } else {
                            // WAV fallback
                            fallback = startWavRecorder(stream, (blob) => {
                                const file = new File([blob], 'rec_' + Date.now() + '.wav', {
                                    type: 'audio/wav'
                                });
                                const dt = new DataTransfer();
                                if (input.files) Array.from(input.files).forEach(f => dt.items
                                    .add(f));
                                dt.items.add(file);
                                input.files = dt.files;
                                const a = document.createElement('audio');
                                a.controls = true;
                                a.className = 'w-100 mb-2';
                                a.src = URL.createObjectURL(file);
                                list.appendChild(a);
                                previewBox.style.display = 'block';
                                if (removeFlg) removeFlg.value = '0';
                            });
                        }
                        captureBtn.className = 'btn btn-sm btn-danger';
                        captureBtn.innerHTML = '<i class="bi bi-stop"></i> {{ __('dashboard.stop') }}';
                        previewBox.style.display = 'block';
                        indicator = document.createElement('div');
                        indicator.className = 'text-danger mt-2 small';
                        indicator.textContent = '🔴 Recording...';
                        list.appendChild(indicator);
                    } catch (e) {
                        alert('Mic error: ' + (e?.message || e));
                    }
                });

                function startWavRecorder(stream, cb) {
                    const ctx = new(window.AudioContext || window.webkitAudioContext)();
                    const src = ctx.createMediaStreamSource(stream);
                    const proc = ctx.createScriptProcessor(4096, 1, 1);
                    let buf = [];
                    proc.onaudioprocess = (e) => buf.push(new Float32Array(e.inputBuffer.getChannelData(0)));
                    src.connect(proc);
                    proc.connect(ctx.destination);
                    return {
                        stop: () => {
                            src.disconnect();
                            proc.disconnect();
                            ctx.close();
                            const blob = encodeWav(buf, 44100);
                            cb(blob);
                            fallback = null;
                            try {
                                stream.getTracks().forEach(t => t.stop());
                            } catch (_) {}
                        }
                    };
                }

                function encodeWav(chunks, rate) {
                    const len = chunks.reduce((a, c) => a + c.length, 0);
                    const buf = new Float32Array(len);
                    let off = 0;
                    chunks.forEach(c => {
                        buf.set(c, off);
                        off += c.length;
                    });
                    const ab = new ArrayBuffer(44 + buf.length * 2);
                    const v = new DataView(ab);

                    function ws(o, s) {
                        for (let i = 0; i < s.length; i++) v.setUint8(o + i, s.charCodeAt(i));
                    }
                    ws(0, 'RIFF');
                    v.setUint32(4, 36 + buf.length * 2, true);
                    ws(8, 'WAVE');
                    ws(12, 'fmt ');
                    v.setUint32(16, 16, true);
                    v.setUint16(20, 1, true);
                    v.setUint16(22, 1, true);
                    v.setUint32(24, rate, true);
                    v.setUint32(28, rate * 2, true);
                    v.setUint16(32, 2, true);
                    v.setUint16(34, 16, true);
                    ws(36, 'data');
                    v.setUint32(40, buf.length * 2, true);
                    let idx = 44;
                    for (let i = 0; i < buf.length; i++, idx += 2) {
                        const s = Math.max(-1, Math.min(1, buf[i]));
                        v.setInt16(idx, s < 0 ? s * 0x8000 : s * 0x7FFF, true);
                    }
                    return new Blob([v], {
                        type: 'audio/wav'
                    });
                }

                removeBtn?.addEventListener('click', () => {
                    list.innerHTML = '';
                    previewBox.style.display = 'none';
                    if (input) input.value = '';
                    if (removeFlg) removeFlg.value = '1';
                });
            }




            // ====== VIDEO + FILEPOND PER BOX ======================================================================
            if (window.FilePond) {
                try {
                    FilePond.registerPlugin(
                        window.FilePondPluginFileValidateType || (() => {}),
                        window.FilePondPluginFileValidateSize || (() => {})
                    );
                } catch (_) {}
            }

            function initVideoBox(box) {
                //const uploadBtn = box.querySelector('.upload-media');
                const captureBtn = box.querySelector('.capture-media');
                const removeAll = box.querySelector('.remove-video-all');
                const input = box.querySelector('.media-input'); // input احتياطي (لن نرفع منه)
                const camInput = box.querySelector('.camera-video-input'); // كاميرا الهاتف
                const pondInput = box.querySelector('.pond-video'); // FilePond
                const keysWrap = box.querySelector('.uploaded-keys'); // مفاتيح multipart
                const removeFlg = box.querySelector('.remove-flag');
                const errorBox = box.querySelector('.video-error');

                const idx = box.dataset.index;

                // Slider
                const slider = box.querySelector('.video-slider');
                const player = slider.querySelector('.slider-video');
                const prevBtn = slider.querySelector('.prev');
                const nextBtn = slider.querySelector('.next');
                const dotsRoot = slider.querySelector('.slider-dots');
                const viewport = slider.querySelector('.slider-viewport');

                prevBtn.innerHTML = isRTL() ? '›' : '‹';
                nextBtn.innerHTML = isRTL() ? '‹' : '›';

                const existing = (() => {
                    try {
                        const j = JSON.parse(box.dataset.existing || '[]');
                        return Array.isArray(j) ? j : [];
                    } catch (_) {
                        return [];
                    }
                })();
                let items = existing.map((u, i) => ({
                    id: 'server-' + i,
                    url: u,
                    revoke: false
                }));
                let current = 0;

                function renderDots() {
                    dotsRoot.innerHTML = '';
                    items.forEach((_, i) => {
                        const d = document.createElement('div');
                        d.className = 'slider-dot' + (i === current ? ' active' : '');
                        d.addEventListener('click', () => {
                            current = i;
                            renderVideo();
                        });
                        dotsRoot.appendChild(d);
                    });
                }

                function renderVideo() {
                    if (!items.length) {
                        slider.classList.add('d-none');
                        player.removeAttribute('src');
                        player.load();
                        return;
                    }
                    slider.classList.remove('d-none');
                    const it = items[current];
                    player.src = it.url;
                    player.load();
                    renderDots();
                }

                function addItem(id, url, revoke) {
                    items.push({
                        id,
                        url,
                        revoke
                    });
                    current = items.length - 1;
                    renderVideo();
                }

                function removeItem(id) {
                    const pos = items.findIndex(x => x.id === id);
                    if (pos > -1) {
                        const it = items[pos];
                        if (it.revoke) {
                            try {
                                URL.revokeObjectURL(it.url);
                            } catch (_) {}
                        }
                        items.splice(pos, 1);
                        if (current >= items.length) current = Math.max(0, items.length - 1);
                        renderVideo();
                    }
                }

                prevBtn.addEventListener('click', () => {
                    if (!items.length) return;
                    current = (current - 1 + items.length) % items.length;
                    renderVideo();
                });
                nextBtn.addEventListener('click', () => {
                    if (!items.length) return;
                    current = (current + 1) % items.length;
                    renderVideo();
                });

                // swipe
                (function() {
                    let sx = 0;
                    const vp = slider.querySelector('.slider-viewport');
                    vp.addEventListener('touchstart', e => sx = e.changedTouches[0].clientX, {
                        passive: true
                    });
                    vp.addEventListener('touchend', e => {
                        const dx = e.changedTouches[0].clientX - sx;
                        if (Math.abs(dx) > 40) {
                            if (isRTL()) {
                                if (dx < 0) prevBtn.click();
                                else nextBtn.click();
                            } else {
                                if (dx < 0) nextBtn.click();
                                else prevBtn.click();
                            }
                        }
                    }, {
                        passive: true
                    });
                })();

                // فتح متصفح الملفات عبر FilePond
                // uploadBtn?.addEventListener('click', () => {
                //     if (pond) pond.browse();
                // });

                // لو اختيرت ملفات من الـ input لأي سبب، أضفها إلى FilePond
                input?.addEventListener('change', (e) => {
                    if (!pond) return;
                    const files = Array.from(e.target.files || []);
                    files.forEach(f => pond.addFile(f));
                    e.target.value = '';
                    if (removeFlg) removeFlg.value = '0';
                });

                // ==== تسجيل فيديو: مباشر على الحاسوب، وفتح كاميرا على الهاتف ====
                let recStream = null,
                    recorder = null,
                    recChunks = [],
                    recOverlay = null,
                    recTimer = null,
                    recStartTs = 0;

                function bestVideoMime() {
                    const cands = ['video/webm;codecs=vp9,opus', 'video/webm;codecs=vp8,opus', 'video/webm'];
                    for (const m of cands) {
                        if (window.MediaRecorder && MediaRecorder.isTypeSupported && MediaRecorder.isTypeSupported(
                                m)) return m;
                    }
                    return 'video/webm';
                }

                function stopStream(s) {
                    try {
                        s?.getTracks()?.forEach(t => t.stop());
                    } catch (_) {}
                }

                function formatTime(ms) {
                    const sec = Math.floor(ms / 1000);
                    const m = String(Math.floor(sec / 60)).padStart(2, '0');
                    const s = String(sec % 60).padStart(2, '0');
                    return `${m}:${s}`;
                }

                // كاميرا الهاتف → أضف مباشرة لملفات FilePond
                camInput?.addEventListener('change', (e) => {
                    const files = Array.from(e.target.files || []);
                    if (!files.length || !pond) return;
                    files.forEach(f => pond.addFile(f)); // رفع مباشر عبر FilePond
                    if (removeFlg) removeFlg.value = '0';
                    e.target.value = '';
                });

                captureBtn?.addEventListener('click', async () => {
                    // على الهاتف أو لو ما في MediaRecorder → افتح كاميرا الهاتف (native)
                    if (isMobile || !('MediaRecorder' in window)) {
                        camInput?.click();
                        return;
                    }

                    // إذا كان يسجّل حالياً -> أوقف التسجيل
                    if (recorder && recorder.state === 'recording') {
                        captureBtn.disabled = true;
                        try {
                            recorder.stop();
                        } catch (_) {}
                        return;
                    }

                    // ابدأ المعاينة + التسجيل على الحاسوب
                    try {
                        // 1) قيود getUserMedia — خفّض الدقة والفريم ريت
                        recStream = await navigator.mediaDevices.getUserMedia({
                            video: {
                                facingMode: {
                                    ideal: 'environment'
                                },
                                width: {
                                    ideal: 854,
                                    max: 854
                                }, // 480p عريض
                                height: {
                                    ideal: 480,
                                    max: 480
                                },
                                frameRate: {
                                    ideal: 24,
                                    max: 24
                                }
                            },
                            audio: true
                        });

                        slider.classList.remove('d-none');
                        recOverlay = document.createElement('div');
                        recOverlay.style.cssText =
                            'position:absolute;inset:0;background:#000;display:flex;align-items:center;justify-content:center;z-index:5;';
                        const live = document.createElement('video');
                        live.autoplay = true;
                        live.muted = true;
                        live.playsInline = true;
                        live.srcObject = recStream;
                        live.style.cssText = 'width:100%;height:100%;object-fit:contain;';
                        viewport.appendChild(recOverlay);
                        recOverlay.appendChild(live);

                        const indicator = document.createElement('div');
                        indicator.className = 'text-danger small';
                        indicator.style.cssText =
                            'position:absolute;bottom:10px;left:50%;transform:translateX(-50%);';
                        recOverlay.appendChild(indicator);
                        recStartTs = Date.now();
                        indicator.textContent = '🔴 Recording... 00:00';
                        recTimer = setInterval(() => {
                            indicator.textContent = '🔴 Recording... ' + formatTime(Date.now() -
                                recStartTs);
                        }, 1000);

                        const mime = (function choose() {
                            const cands = ['video/webm;codecs=vp8,opus', 'video/webm'];
                            for (const m of cands) {
                                if (window.MediaRecorder?.isTypeSupported?.(m)) return m;
                            }
                            return 'video/webm';
                        })();
                        recChunks = [];



                        recorder = new MediaRecorder(recStream, {
                            mimeType: mime,
                            videoBitsPerSecond: 800_000, // 1.2 Mbps بدل 4 Mbps
                            audioBitsPerSecond: 64_000 // 64 Kbps
                        });

                        recorder.ondataavailable = (e) => {
                            if (e.data && e.data.size > 0) recChunks.push(e.data);
                        };

                        recorder.onstop = () => {
                            clearInterval(recTimer);
                            recTimer = null;
                            if (recOverlay) {
                                try {
                                    recOverlay.remove();
                                } catch (_) {}
                                recOverlay = null;
                            }
                            stopStream(recStream);
                            recStream = null;

                            const type = recorder.mimeType || mime || 'video/webm';
                            const ext = type.includes('mp4') ? 'mp4' : 'webm';
                            const blob = new Blob(recChunks, {
                                type
                            });
                            const file = new File([blob], `rec_${Date.now()}.${ext}`, {
                                type
                            });

                            // الرفع عبر FilePond مباشرة (لا نضيف للـ input)
                            if (pond) pond.addFile(file);
                            if (removeFlg) removeFlg.value = '0';

                            captureBtn.disabled = false;
                            captureBtn.classList.remove('btn-danger');
                            captureBtn.classList.add('btn-secondary');
                            captureBtn.innerHTML =
                                '🎥 {{ __('dashboard.start_recording_video') }}';
                            recorder = null;
                        };

                        recorder.start();
                        captureBtn.classList.remove('btn-secondary');
                        captureBtn.classList.add('btn-danger');
                        captureBtn.innerHTML = '<i class="bi bi-stop"></i> {{ __('dashboard.stop') }}';

                    } catch (e) {
                        stopStream(recStream);
                        recStream = null;
                        if (recOverlay) {
                            try {
                                recOverlay.remove();
                            } catch (_) {}
                            recOverlay = null;
                        }
                        if (recTimer) {
                            clearInterval(recTimer);
                            recTimer = null;
                        }
                        recorder = null;
                        alert('Camera/Mic error: ' + (e?.message || e));
                    }
                });

                // ===== FilePond لكل صندوق فيديو =====
                let pond = null;
                // خريطة لربط ملف FilePond بمعرّف العنصر في السلايدر
                const pondFileToSliderId = new WeakMap();

                if (window.FilePond) {
                    pond = FilePond.create(pondInput, {
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
                                        const createRes = await fetch(
                                            `{{ route('multipart.create') }}`, {
                                                method: 'POST',
                                                headers: {
                                                    'Content-Type': 'application/json',
                                                    'X-CSRF-TOKEN': CSRF
                                                },
                                                body: JSON.stringify({
                                                    fileName: file.name,
                                                    contentType: cleanMime(file
                                                        .type),
                                                    fileSize: file.size,
                                                    prefix: 'uploads/signin/videos'
                                                })
                                            });
                                        if (!createRes.ok) throw new Error(
                                            'createMultipart failed');
                                        const createJson = await createRes.json();
                                        uploadId = createJson.uploadId;
                                        key = createJson.key;

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
                                                    if (e.lengthComputable)
                                                        progress(true,
                                                            uploadedBase + e.loaded,
                                                            file.size);
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
                                                                'Missing ETag'
                                                            ));
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

                                        // خزّن key كـ input مخفي
                                        keysWrap.appendChild(createHidden(
                                            `items[${idx}][uploaded_videos][]`, key));

                                        // أضف المعاينة في السلايدر بعد نجاح الرفع
                                        const blobURL = URL.createObjectURL(file);
                                        const sliderId = 'pond-' + Math.random().toString(36)
                                            .slice(2);
                                        addItem(sliderId, blobURL, true);
                                        pondFileToSliderId.set(file, sliderId);

                                        if (removeFlg) removeFlg.value = '0';
                                        load(key);
                                    } catch (e) {
                                        if (cancelled) return;
                                        console.error(e);
                                        error(e.message || 'upload failed');
                                        if (errorBox) {
                                            errorBox.classList.remove('d-none');
                                            errorBox.textContent =
                                                'فشل رفع الفيديو. حاول من جديد.';
                                        }
                                    }
                                })();

                                return {
                                    abort: () => {
                                        cancelled = true;
                                        try {
                                            if (currentXhr) currentXhr.abort();
                                        } catch (_) {}
                                        abort();
                                    }
                                };
                            },
                            revert: (uniqueFileId, load) => {
                                load();
                            } // المفاتيح نخزنها يدويًا
                        }
                    });

                    // عند حذف ملف من FilePond → احذف معاينته من السلايدر
                    pond.on('removefile', (fileItem) => {
                        if (!fileItem) return;
                        const sliderId = pondFileToSliderId.get(fileItem.file);
                        if (sliderId) {
                            removeItem(sliderId);
                            pondFileToSliderId.delete(fileItem.file);
                        }
                    });

                    // زر حذف الكل
                    removeAll?.addEventListener('click', () => {
                        try {
                            pond.removeFiles();
                        } catch (_) {}
                        input.value = '';
                        keysWrap.innerHTML = '';
                        items = [];
                        current = 0;
                        renderVideo();
                        removeFlg.value = '1';
                    });
                }

                // أول عرض (موجود من السيرفر)
                renderVideo();
            }


            // ====== INIT لكل العناصر الحالية ======
            document.querySelectorAll('.photo-box').forEach(initPhotoBox);
            document.querySelectorAll('.audio-box').forEach(initAudioBox);
            document.querySelectorAll('.video-box').forEach(initVideoBox);

            // ====== إضافة عنصر جديد ======
            const itemsContainer = document.getElementById('items-container');
            const addItemBtn = document.getElementById('add-item');
            let itemIndex = {{ isset($campReport) ? $campReport->items->count() : 0 }};

            addItemBtn?.addEventListener('click', function() {
                const tpl = `
<div class="card mb-4 item-row" data-index="${itemIndex}">
  <div class="card-header d-flex justify-content-between align-items-center">
    <h5 class="mb-0">#${itemIndex + 1}</h5>
    <button type="button" class="btn btn-sm btn-icon btn-danger remove-item"><i class="bi bi-trash"></i></button>
  </div>
  <div class="card-body">
    <div class="row">
      <div class="col-md-6 mb-3">
        <label class="form-label required">{{ __('dashboard.item_name') }}</label>
        <input type="text" name="items[${itemIndex}][item_name]" class="form-control" required>
      </div>
      <div class="col-md-6 mb-3">
        <label class="form-label">{{ __('dashboard.notes') }}</label>
        <textarea name="items[${itemIndex}][notes]" rows="1" class="form-control"></textarea>
      </div>
    </div>

    <div class="row g-4">
      <!-- PHOTOS -->
      <div class="col-md-4">
        <label class="form-label">{{ __('dashboard.photo_attachment') }}</label>
        <div class="media-upload-container photo-box" data-type="photo" data-existing="[]">
          <input type="hidden" name="items[${itemIndex}][remove_photos]" value="0" class="remove-flag">
          <div class="photo-slider mb-2 d-none">
            <div class="slider-viewport"><img class="slider-img" alt="photo"></div>
            <button type="button" class="slider-nav prev" aria-label="Prev">‹</button>
            <button type="button" class="slider-nav next" aria-label="Next">›</button>
            <div class="slider-dots"></div>
          </div>
          <div class="d-grid gap-2">
  <button type="button" class="btn btn-sm btn-primary capture-media w-100">
    <i class="bi bi-camera"></i> @lang('dashboard.capture_photo')
  </button>
  <button type="button" class="btn btn-sm btn-secondary upload-media w-100">
    <i class="bi bi-upload"></i> @lang('dashboard.upload')
  </button>
  <button type="button" class="btn btn-sm btn-outline-danger remove-media-all w-100">
    <i class="bi bi-trash"></i> @lang('dashboard.delete')
  </button>
</div>

          <input type="file" name="items[${itemIndex}][photos][]" class="media-input d-none" accept="image/*" multiple>
          <input type="file" class="d-none camera-photo-input" accept="image/*" capture="environment" multiple>
        </div>
      </div>

      <!-- AUDIOS -->
      <div class="col-md-4">
        <label class="form-label">{{ __('dashboard.audio_attachment') }}</label>
        <div class="media-upload-container audio-box" data-type="audio" data-existing="[]">
          <input type="hidden" name="items[${itemIndex}][remove_audios]" value="0" class="remove-flag">
          <div class="preview-audio-container mb-2" style="display:none;">
            <div class="preview-audio-list"></div>
          </div>
          <div class="d-grid gap-2">
  <button type="button" class="btn btn-sm btn-primary capture-media w-100">
    <i class="bi bi-mic"></i> @lang('dashboard.record')
  </button>
  <button type="button" class="btn btn-sm btn-secondary upload-media w-100">
    <i class="bi bi-upload"></i> @lang('dashboard.upload')
  </button>
  <button type="button" class="btn btn-sm btn-outline-danger remove-media w-100">
    <i class="bi bi-trash"></i> @lang('dashboard.delete')
  </button>
</div>

          <input type="file" name="items[${itemIndex}][audios][]" class="media-input d-none" accept="audio/*" multiple>
        </div>
      </div>

      <!-- VIDEOS -->
<div class="col-md-4">
  <label class="form-label">{{ __('dashboard.video_attachment') }}</label>
  <div class="media-upload-container video-box" data-type="video" data-index="${itemIndex}" data-existing="[]">
    <input type="hidden" name="items[${itemIndex}][remove_videos]" value="0" class="remove-flag">

    <div class="video-slider mb-2 d-none">
      <div class="slider-viewport"><video class="slider-video" controls playsinline></video></div>
      <button type="button" class="slider-nav prev" aria-label="Prev">‹</button>
      <button type="button" class="slider-nav next" aria-label="Next">›</button>
      <div class="slider-dots"></div>
    </div>

    <div class="d-grid gap-2 mb-2">
      <button type="button" class="btn btn-sm btn-primary capture-media w-100" id="capture-video-btn-${itemIndex}">
        🎥 @lang('dashboard.start_recording_video')
      </button>

      <!-- واجهة FilePond -->
      <input type="file" class="pond-video" accept="video/*" multiple>

      <button type="button" class="btn btn-sm btn-outline-danger remove-video-all w-100">
        <i class="bi bi-trash"></i> @lang('dashboard.delete')
      </button>
    </div>

    <!-- احتياطي + كاميرا الهاتف (تُضاف للـ FilePond) -->
    <input type="file" name="items[${itemIndex}][videos][]" class="media-input d-none" accept="video/*" multiple>
    <input type="file" class="d-none camera-video-input" accept="video/*" capture="environment" multiple>

    <div class="uploaded-keys"></div>
    <input type="hidden" class="remove-video-flag" value="0">
    <div class="text-danger small d-none video-error"></div>
  </div>
</div>

    </div>
  </div>
</div>`;
                itemsContainer.insertAdjacentHTML('beforeend', tpl);

                const newCard = itemsContainer.lastElementChild;
                initPhotoBox(newCard.querySelector('.photo-box'));
                initAudioBox(newCard.querySelector('.audio-box'));
                initVideoBox(newCard.querySelector('.video-box'));

                itemIndex++;
            });

            // حذف عنصر كامل
            itemsContainer.addEventListener('click', function(e) {
                if (e.target.closest('.remove-item')) {
                    const itemRow = e.target.closest('.item-row');
                    const existingId = itemRow.querySelector('input[name$="[id]"]')?.value;
                    if (existingId) {
                        const deleted = document.createElement('input');
                        deleted.type = 'hidden';
                        deleted.name = 'deleted_items[]';
                        deleted.value = existingId;
                        document.querySelector('form').appendChild(deleted);
                    }
                    itemRow.remove();
                }
            });
        });
    </script>
@endpush
