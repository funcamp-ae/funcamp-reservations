@extends('dashboard.layouts.app')
@section('pageTitle', __('dashboard.camp_report_details'))

@section('content')
    <div class="card mb-5 mb-xl-10">
        <div class="card-header">
            <h3 class="card-title">@lang('dashboard.camp_report_details')</h3>
            <div class="card-toolbar">
                <a href="{{ route('camp-reports.index') }}" class="btn btn-secondary">
                    @lang('dashboard.back_to_list')
                </a>
            </div>
        </div>

        <div class="card-body">
            <!-- معلومات عامة عن التقرير -->
            <div class="row g-5 mb-8">
                <div class="col-md-6">
                    <div class="mb-4">
                        <span class="fw-bold">@lang('dashboard.report_date'):</span>
                        <span>{{ $campReport->report_date->format('Y-m-d') }}</span>
                    </div>
                    <div class="mb-4">
                        <span class="fw-bold">@lang('dashboard.service'):</span>
                        <span>{{ $campReport->service->name ?? '-' }}</span>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="mb-4">
                        <span class="fw-bold">@lang('dashboard.camp_name'):</span>
                        <span>{{ $campReport->camp_name ?? '-' }}</span>
                    </div>
                    <div class="mb-4">
                        <span class="fw-bold">@lang('dashboard.created_by'):</span>
                        <span>{{ $campReport->creator->name }}</span>
                    </div>
                </div>
            </div>

            {{-- ملاحظات عامة إن وجدت --}}
            @if($campReport->general_notes)
                <div class="mb-4">
                    <p class="fw-bold text-primary">@lang('dashboard.general_notes'):</p>
                    <span class="text-dark">{!! nl2br(e($campReport->general_notes)) !!}</span>
                </div>
            @endif

            <hr>

            {{-- عناصر التقرير --}}
            @if($campReport->items->count())
                <div class="mt-4">
                    <h5 class="fw-bold mb-3">@lang('dashboard.report_items')</h5>
                    <div class="list-group">
                        @foreach($campReport->items as $item)
                            <div class="list-group-item border rounded p-3 mb-3">
                                <strong class="text-primary d-block mb-2">{{ $item->item_name }}</strong>

                                {{-- ملاحظات العنصر --}}
                                @if($item->notes)
                                    <div class="mb-3">
                                        <small class="text-muted d-block">@lang('dashboard.notes')</small>
                                        <div class="small">{!! nl2br(e($item->notes)) !!}</div>
                                    </div>
                                @endif

                                @php
                                    $photoUrls = $item->media->where('type','photo')->pluck('path')->map(fn($p) => Storage::url($p))->values();
                                    $audioUrls = $item->media->where('type','audio')->pluck('path')->map(fn($p) => Storage::url($p))->values();
                                    $videoUrls = $item->media->where('type','video')->pluck('path')->map(fn($p) => Storage::url($p))->values();
                                @endphp

                                <div class="row">
                                    {{-- صور --}}
                                    @if($photoUrls->count())
                                        <div class="col-md-4 mb-4">
                                            <small class="text-muted d-block">@lang('dashboard.photo_attachment')</small>
                                            <div class="media-slider photo-slider" data-items='@json($photoUrls)'>
                                                <div class="slider-viewport">
                                                    <img class="slider-media" alt="photo" style="width:100%; height:200px; object-fit:contain; border-radius:4px;">
                                                </div>
                                                @if($photoUrls->count() > 1)
                                                    <button class="slider-nav prev">‹</button>
                                                    <button class="slider-nav next">›</button>
                                                    <div class="slider-dots"></div>
                                                @endif
                                            </div>
                                        </div>
                                    @endif

                                    {{-- صوت (كل ملف تحت الآخر) --}}
                                    @if($audioUrls->count())
                                        <div class="col-md-4 mb-4">
                                            <small class="text-muted d-block">@lang('dashboard.audio_attachment')</small>
                                            <div class="audio-list">
                                                @foreach($audioUrls as $url)
                                                    <div class="mb-2">
                                                        <audio controls style="width:100%; border-radius:6px;">
                                                            <source src="{{ $url }}" type="audio/mpeg">
                                                        </audio>
                                                    </div>
                                                @endforeach
                                            </div>
                                        </div>
                                    @endif

                                    {{-- فيديو --}}
                                    @if($videoUrls->count())
                                        <div class="col-md-4 mb-4">
                                            <small class="text-muted d-block">@lang('dashboard.video_attachment')</small>
                                            <div class="media-slider video-slider" data-items='@json($videoUrls)'>
                                                <div class="slider-viewport">
                                                    <video class="slider-media" controls playsinline style="width:100%; height:200px; object-fit:contain; border-radius:4px;"></video>
                                                </div>
                                                @if($videoUrls->count() > 1)
                                                    <button class="slider-nav prev">‹</button>
                                                    <button class="slider-nav next">›</button>
                                                    <div class="slider-dots"></div>
                                                @endif
                                            </div>
                                        </div>
                                    @endif
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>
            @endif
        </div>
    </div>
@endsection

@push('css')
    <style>
        /* السلايدر */
        .media-slider {
            position: relative;
            border: 1px dashed #ddd;
            border-radius: 4px;
            padding: 8px;
            overflow: hidden;
        }
        .media-slider .slider-viewport {
            display: flex;
            align-items: center;
            justify-content: center;
        }
        .media-slider .slider-nav {
            position: absolute;
            top: 50%;
            transform: translateY(-50%);
            background: rgba(0, 0, 0, 0.45);
            color: #fff;
            border: none;
            border-radius: 50%;
            width: 32px;
            height: 32px;
            display: flex;
            align-items: center;
            justify-content: center;
            cursor: pointer;
            z-index: 2;
        }
        .media-slider .slider-nav:hover { background: rgba(0, 0, 0, 0.6); }

        /* الاتجاه حسب اللغة */
        [dir="ltr"] .media-slider .slider-nav.prev { left: 8px; }
        [dir="ltr"] .media-slider .slider-nav.next { right: 8px; }
        [dir="rtl"] .media-slider .slider-nav.prev { right: 8px; }
        [dir="rtl"] .media-slider .slider-nav.next { left: 8px; }

        .media-slider .slider-dots {
            display: flex;
            justify-content: center;
            gap: 5px;
            margin-top: 5px;
        }
        .media-slider .slider-dot {
            width: 8px;
            height: 8px;
            border-radius: 50%;
            cursor: pointer;
        }
        .media-slider .slider-dot.active {
            background: #333;
        }

        /* الصوت تحت بعض */
        .audio-list {
            display: flex;
            flex-direction: column;
            gap: 10px;
        }
        .audio-list audio {
            width: 100%;
            box-shadow: 0 2px 5px rgba(0,0,0,.1);
            border: 1px solid #ddd;
            border-radius: 8px;
        }
    </style>
@endpush

@push('js')
    <script>
        // تهيئة السلايدرات (الصور والفيديو فقط)
        document.addEventListener('DOMContentLoaded', function () {
            const isRTL = document.documentElement.getAttribute('dir') === 'rtl';

            document.querySelectorAll('.media-slider').forEach(function (slider) {
                const urls = JSON.parse(slider.dataset.items || '[]');
                const mediaEl = slider.querySelector('.slider-media');
                const dotsBox = slider.querySelector('.slider-dots');
                const prevBtn = slider.querySelector('.slider-nav.prev');
                const nextBtn = slider.querySelector('.slider-nav.next');
                let current = 0;

                if (!urls.length) {
                    slider.style.display = 'none';
                    return;
                }

                function loadMedia() {
                    const url = urls[current];
                    if (mediaEl.tagName.toLowerCase() === 'img') {
                        mediaEl.src = url;
                    } else {
                        mediaEl.innerHTML = '';
                        const source = document.createElement('source');
                        source.src = url;
                        mediaEl.appendChild(source);
                        mediaEl.load();
                    }
                    dotsBox.querySelectorAll('.slider-dot').forEach((dot, idx) => {
                        dot.classList.toggle('active', idx === current);
                    });
                }

                dotsBox.innerHTML = '';
                urls.forEach((_, idx) => {
                    const dot = document.createElement('div');
                    dot.className = 'slider-dot' + (idx === 0 ? ' active' : '');
                    dot.addEventListener('click', function () {
                        current = idx;
                        loadMedia();
                    });
                    dotsBox.appendChild(dot);
                });

                if (prevBtn) {
                    prevBtn.addEventListener('click', function () {
                        current = isRTL
                            ? (current + 1) % urls.length  // RTL: السهم الأيسر = التالي
                            : (current - 1 + urls.length) % urls.length;
                        loadMedia();
                    });
                }

                if (nextBtn) {
                    nextBtn.addEventListener('click', function () {
                        current = isRTL
                            ? (current - 1 + urls.length) % urls.length // RTL: السهم الأيمن = السابق
                            : (current + 1) % urls.length;
                        loadMedia();
                    });
                }

                loadMedia();
            });
        });
    </script>
@endpush
