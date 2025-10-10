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
                    <span class="">{!! nl2br(e($campReport->general_notes)) !!}</span>
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
                                                @else
                                                    <div class="slider-dots d-none"></div>
                                                @endif
                                            </div>
                                        </div>
                                    @endif

                                    {{-- صوت --}}
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
                                                @else
                                                    <div class="slider-dots d-none"></div>
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
    <!-- Lightbox للصور فقط -->
<div id="img-lightbox" class="img-lightbox d-none" aria-hidden="true">
  <button type="button" class="lightbox-close" aria-label="Close">&times;</button>
  <button type="button" class="lightbox-nav prev" aria-label="Prev">‹</button>
  <img class="lightbox-img" alt="preview">
  <button type="button" class="lightbox-nav next" aria-label="Next">›</button>
</div>

@endsection

@push('css')
    <style>
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
        .media-slider .slider-dot.active { background: #333; }

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
        /* Lightbox */
.img-lightbox{
  position:fixed; inset:0; background:rgba(0,0,0,.9);
  display:flex; align-items:center; justify-content:center;
  z-index:9999;
}
.img-lightbox.d-none{ display:none; }
.img-lightbox .lightbox-img{
  max-width:92%; max-height:92%; object-fit:contain; border-radius:6px;
}
.img-lightbox .lightbox-close{
  position:absolute; top:16px; right:24px; font-size:36px;
  background:transparent; border:none; color:#fff; cursor:pointer;
}
.img-lightbox .lightbox-nav{
  position:absolute; top:50%; transform:translateY(-50%);
  background:rgba(255,255,255,.15); color:#fff; border:none;
  width:44px; height:44px; border-radius:50%; font-size:22px; cursor:pointer;
}
[dir="ltr"] .img-lightbox .lightbox-nav.prev{ left:24px; }
[dir="ltr"] .img-lightbox .lightbox-nav.next{ right:24px; }
[dir="rtl"] .img-lightbox .lightbox-nav.prev{ right:24px; }
[dir="rtl"] .img-lightbox .lightbox-nav.next{ left:24px; }
.img-lightbox .lightbox-nav:hover{ background:rgba(255,255,255,.25); }
@push('css')
<style>
/* === تحسين ظهور نقاط السلايدر (صور + فيديو) === */
.media-slider { position: relative; }

.media-slider .slider-dots{
  position: absolute;
  bottom: 10px;
  left: 0; right: 0;
  display: flex;
  justify-content: center;
  gap: 8px;
  padding: 0;
  pointer-events: none; /* ما تمنع التفاعل مع الفيديو/الصورة */
}

.media-slider .slider-dot{
  width: 10px; height: 10px; border-radius: 50%;
  background: rgba(255,255,255,0.35);
  border: 2px solid rgba(255,255,255,0.9);
  box-shadow: 0 0 0 2px rgba(0,0,0,.25);
  transition: transform .15s ease, background .15s ease, border-color .15s ease;
  pointer-events: auto; /* قابلة للنقر */
  cursor: pointer;
}

.media-slider .slider-dot:hover,
.media-slider .slider-dot:focus-visible{
  background: #fff; border-color:#fff; transform: scale(1.15); outline: none;
}

.media-slider .slider-dot.active{
  background: #fff; border-color:#fff; transform: scale(1.25);
}
</style>
@endpush

    </style>
@endpush






@push('js')
<script>
document.addEventListener('DOMContentLoaded', function () {
  const isRTL = document.documentElement.getAttribute('dir') === 'rtl';

  // ====== Lightbox عام للصور ======
  const Lightbox = (() => {
    const box   = document.getElementById('img-lightbox');
    const imgEl = box.querySelector('.lightbox-img');
    const btnX  = box.querySelector('.lightbox-close');
    const btnP  = box.querySelector('.lightbox-nav.prev');
    const btnN  = box.querySelector('.lightbox-nav.next');

    let urls = [], idx = 0;

    function show(arr, start = 0) {
      urls = Array.isArray(arr) ? arr : [];
      if (!urls.length) return;
      idx  = Math.max(0, Math.min(start, urls.length - 1));
      imgEl.src = urls[idx];

      // إخفاء الأسهم إذا صورة واحدة فقط
      const disp = urls.length > 1 ? '' : 'none';
      btnP.style.display = disp;
      btnN.style.display = disp;

      box.classList.remove('d-none');
    }

    function hide() {
      imgEl.removeAttribute('src');
      box.classList.add('d-none');
      urls = [];
      idx  = 0;
    }

    function step(delta) {
      if (!urls.length) return;
      const n = urls.length;
      idx = (idx + delta + n) % n;
      imgEl.src = urls[idx];
    }

    btnX.addEventListener('click', hide);
    btnP.addEventListener('click', () => step(isRTL ? +1 : -1));
    btnN.addEventListener('click', () => step(isRTL ? -1 : +1));

    // إغلاق عند الضغط خارج الصورة
    box.addEventListener('click', e => { if (e.target === box) hide(); });

    // مفاتيح الكيبورد
    document.addEventListener('keydown', e => {
      if (box.classList.contains('d-none')) return;
      if (e.key === 'Escape') hide();
      if (e.key === 'ArrowLeft')  step(isRTL ? +1 : -1);
      if (e.key === 'ArrowRight') step(isRTL ? -1 : +1);
    });

    return { show, hide };
  })();

  // ====== سلايدر الوسائط (صور/فيديو) ======
  document.querySelectorAll('.media-slider').forEach(function (slider) {
    const urls   = JSON.parse(slider.dataset.items || '[]');
    const mediaEl = slider.querySelector('.slider-media');
    const dotsBox = slider.querySelector('.slider-dots');
    const prevBtn = slider.querySelector('.slider-nav.prev');
    const nextBtn = slider.querySelector('.slider-nav.next');
    const viewport = slider.querySelector('.slider-viewport');

    let current = 0;
    if (!urls.length) return;

    function loadMedia() {
      const url = urls[current];
      if (mediaEl.tagName.toLowerCase() === 'img') {
        mediaEl.src = url;
      } else {
        // فيديو: نظّف واستبدل السورس
        try { mediaEl.pause(); } catch(_) {}
        mediaEl.innerHTML = '';
        const source = document.createElement('source');
        source.src = url;
        mediaEl.appendChild(source);
        mediaEl.load();
      }

      if (dotsBox) {
        dotsBox.querySelectorAll('.slider-dot').forEach((dot, idx) => {
          dot.classList.toggle('active', idx === current);
        });
      }
    }

    // النقاط
    if (dotsBox) {
      dotsBox.innerHTML = '';
      if (urls.length > 1) {
        urls.forEach((_, idx) => {
          const dot = document.createElement('div');
          dot.className = 'slider-dot' + (idx === 0 ? ' active' : '');
          dot.addEventListener('click', function () {
            current = idx;
            loadMedia();
          });
          dotsBox.appendChild(dot);
        });
      }
    }

    // أزرار السابق/التالي
    prevBtn?.addEventListener('click', function () {
      current = isRTL ? (current + 1) % urls.length
                      : (current - 1 + urls.length) % urls.length;
      loadMedia();
    });

    nextBtn?.addEventListener('click', function () {
      current = isRTL ? (current - 1 + urls.length) % urls.length
                      : (current + 1) % urls.length;
      loadMedia();
    });

    // فتح اللايت بوكس عند الضغط على الصورة فقط
    if (mediaEl.tagName.toLowerCase() === 'img') {
      mediaEl.style.cursor = 'zoom-in';
      mediaEl.addEventListener('click', () => Lightbox.show(urls, current));
    }

    // سحب بالإصبع (موبايل)
    if (viewport) {
      let startX = 0;
      viewport.addEventListener('touchstart', e => { startX = e.changedTouches[0].clientX; }, { passive: true });
      viewport.addEventListener('touchend', e => {
        const dx = e.changedTouches[0].clientX - startX;
        if (Math.abs(dx) > 40) {
          if (isRTL) {
            if (dx < 0) prevBtn?.click(); else nextBtn?.click();
          } else {
            if (dx < 0) nextBtn?.click(); else prevBtn?.click();
          }
        }
      }, { passive: true });
    }

    // أول تحميل
    loadMedia();
  });
});
</script>
@endpush
