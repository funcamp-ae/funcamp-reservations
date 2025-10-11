@extends('dashboard.layouts.app')
@section('pageTitle', __('dashboard.violation_details'))

@section('content')
@php
    use Illuminate\Support\Facades\Storage;
    use Illuminate\Support\Str;

    // نحضّر روابط المرفقات (صور/صوت/فيديو)
    $attachments = $violation->relationLoaded('attachments') ? $violation->attachments : $violation->attachments()->get();

    $photos = $attachments->where('type','photo')->values();
    $audios = $attachments->where('type','audio')->values();
    $videos = $attachments->where('type','video')->values();

    $hetzUrl = function ($path) {
        try { return Storage::disk('hetzner')->url($path); }
        catch (\Throwable $e) { return Storage::url($path); }
    };

    $videoUrl = function ($path) {
        // مفاتيح S3 أو روابط مباشرة
        if (Str::startsWith($path, ['http://','https://'])) return $path;
        try { return Storage::disk('s3')->url($path); }  // مفاتيح الرفع المجمّع
        catch (\Throwable $e) {
            try { return Storage::disk('hetzner')->url($path); } // احتياط
            catch (\Throwable $e2) { return Storage::url($path); }
        }
    };

    $photoUrls = $photos->map(fn($m) => $hetzUrl($m->path))->values();
    $audioUrls = $audios->map(fn($m) => $hetzUrl($m->path))->values();
    $videoUrls = $videos->map(fn($m) => $videoUrl($m->path))->values();
@endphp

<div class="card mb-5 mb-xl-10">
    <div class="card-header">
        <h3 class="card-title">@lang('dashboard.violation_details')</h3>
        <div class="card-toolbar">
            <a href="{{ route('violations.index') }}" class="btn btn-secondary">
                @lang('dashboard.back_to_list')
            </a>
        </div>
    </div>

    <div class="card-body">
        <!-- معلومات عامة -->
        <div class="row g-5 mb-8">
            <div class="col-md-6">
                <div class="mb-4">
                    <span class="fw-bold">@lang('dashboard.employee'):</span>
                    <span>{{ $violation->employee->name }}</span>
                </div>
                <div class="mb-4">
                    <span class="fw-bold">@lang('dashboard.violation_type'):</span>
                    <span>{{ $violation->type->name }}</span>
                </div>
                <div class="mb-4">
                    <span class="fw-bold">@lang('dashboard.created_by'):</span>
                    <span>{{ $violation->creator->name }}</span>
                </div>
            </div>
            <div class="col-md-6">
                <div class="mb-4">
                    <span class="fw-bold">@lang('dashboard.violation_date'):</span>
                    <span>{{ $violation->violation_date? $violation->violation_date->format('Y-m-d') : '-' }}</span>
                </div>
                <div class="mb-4">
                    <span class="fw-bold">@lang('dashboard.violation_time'):</span>
                    <span>{{ $violation->violation_time ?: '-' }}</span>
                </div>
                <div class="mb-4">
                    <span class="fw-bold">@lang('dashboard.violation_place'):</span>
                    <span>{{ $violation->violation_place ?: '-' }}</span>
                </div>
            </div>
        </div>

        @if($violation->employee_justification)
            <div class="mb-4">
                <p class="fw-bold text-primary">@lang('dashboard.employee_justification'):</p>
                <span>{!! nl2br(e($violation->employee_justification)) !!}</span>
            </div>
        @endif

        @if($violation->notes)
            <div class="mb-4">
                <p class="fw-bold text-primary">@lang('dashboard.notes'):</p>
                <span>{!! nl2br(e($violation->notes)) !!}</span>
            </div>
        @endif

        <hr>

        {{-- مرفقات المخالفة --}}
        <div class="row g-5 mt-1">

            {{-- الصور: سلايدر --}}
            @if($photoUrls->count())
                <div class="col-md-4">
                    <small class="text-muted d-block mb-2">@lang('dashboard.photo_attachment')</small>
                    <div class="media-slider photo-slider" data-items='@json($photoUrls)'>
                        <div class="slider-viewport" style="height: 220px;">
                            <img class="slider-media" alt="photo" style="width:100%; height:100%; object-fit:contain; border-radius:4px; cursor: zoom-in;">
                        </div>
                        @if($photoUrls->count() > 1)
                            <button class="slider-nav prev" aria-label="Prev">‹</button>
                            <button class="slider-nav next" aria-label="Next">›</button>
                            <div class="slider-dots"></div>
                        @else
                            <div class="slider-dots d-none"></div>
                        @endif
                    </div>
                </div>
            @endif

            {{-- الصوت: قائمة (بدون سلايدر) --}}
            @if($audioUrls->count())
                <div class="col-md-4">
                    <small class="text-muted d-block mb-2">@lang('dashboard.audio_attachment')</small>
                    <div class="audio-list">
                        @foreach($audioUrls as $url)
                            @php
                                $ext = strtolower(pathinfo(parse_url($url, PHP_URL_PATH) ?? '', PATHINFO_EXTENSION));
                                $mime = match($ext){
                                    'mp3' => 'audio/mpeg',
                                    'wav' => 'audio/wav',
                                    'aac' => 'audio/aac',
                                    'm4a','mp4' => 'audio/mp4',
                                    'webm' => 'audio/webm',
                                    'ogg' => 'audio/ogg',
                                    'opus' => 'audio/opus',
                                    '3gp','3gpp' => 'audio/3gpp',
                                    'amr' => 'audio/amr',
                                    default => ''
                                };
                            @endphp
                            <div class="mb-2">
                                <audio controls preload="none" style="width:100%; border-radius:6px;">
                                    <source src="{{ $url }}" @if($mime) type="{{ $mime }}" @endif>
                                </audio>
                            </div>
                        @endforeach
                    </div>
                </div>
            @endif

            {{-- الفيديو: سلايدر --}}
            @if($videoUrls->count())
                <div class="col-md-4">
                    <small class="text-muted d-block mb-2">@lang('dashboard.video_attachment')</small>
                    <div class="media-slider video-slider" data-items='@json($videoUrls)'>
                        <div class="slider-viewport" style="height: 220px;">
                            <video class="slider-media" controls playsinline preload="metadata" style="width:100%; height:100%; object-fit:contain; border-radius:4px; background:#000;"></video>
                        </div>
                        @if($videoUrls->count() > 1)
                            <button class="slider-nav prev" aria-label="Prev">‹</button>
                            <button class="slider-nav next" aria-label="Next">›</button>
                            <div class="slider-dots"></div>
                        @else
                            <div class="slider-dots d-none"></div>
                        @endif
                    </div>
                </div>
            @endif

            @if(!$photoUrls->count() && !$audioUrls->count() && !$videoUrls->count())
                <div class="col-12">
                    <div class="alert alert-secondary mb-0">@lang('dashboard.no_attachments')</div>
                </div>
            @endif

        </div>
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
/* === هيكل السلايدر (مطابق لفكرة صفحة الكامب) === */
.media-slider {
  position: relative;
  border: 1px dashed #ddd;
  border-radius: 4px;
  padding: 8px;
  overflow: hidden;
}
.media-slider .slider-viewport {
  display: flex; align-items: center; justify-content: center;
}
.media-slider .slider-nav {
  position: absolute; top: 50%; transform: translateY(-50%);
  background: rgba(0,0,0,0.45); color: #fff; border: none; border-radius: 50%;
  width: 32px; height: 32px; display: flex; align-items: center; justify-content: center;
  cursor: pointer; z-index: 2;
}
.media-slider .slider-nav:hover { background: rgba(0,0,0,0.6); }
[dir="ltr"] .media-slider .slider-nav.prev { left: 8px; }
[dir="ltr"] .media-slider .slider-nav.next { right: 8px; }
[dir="rtl"] .media-slider .slider-nav.prev { right: 8px; }
[dir="rtl"] .media-slider .slider-nav.next { left: 8px; }

/* نقاط السلايدر (صور + فيديو) تطفو فوق */
.media-slider { position: relative; }
.media-slider .slider-dots{
  position: absolute; bottom: 10px; left: 0; right: 0;
  display: flex; justify-content: center; gap: 8px; padding: 0;
  pointer-events: none;
}
.media-slider .slider-dot{
  width: 10px; height: 10px; border-radius: 50%;
  background: rgba(255,255,255,0.35);
  border: 2px solid rgba(255,255,255,0.9);
  box-shadow: 0 0 0 2px rgba(0,0,0,.25);
  transition: transform .15s ease, background .15s ease, border-color .15s ease;
  pointer-events: auto; cursor: pointer;
}
.media-slider .slider-dot:hover,
.media-slider .slider-dot:focus-visible{
  background: #fff; border-color:#fff; transform: scale(1.15); outline: none;
}
.media-slider .slider-dot.active{ background:#fff; border-color:#fff; transform: scale(1.25); }

/* قائمة الصوت */
.audio-list{ display: flex; flex-direction: column; gap: 10px; }
.audio-list audio{
  width: 100%; box-shadow: 0 2px 5px rgba(0,0,0,.1);
  border: 1px solid #ddd; border-radius: 8px;
}

/* Lightbox للصور */
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
</style>
@endpush

@push('js')
<script>
document.addEventListener('DOMContentLoaded', function () {
  const isRTL = document.documentElement.getAttribute('dir') === 'rtl';

  // ===== Helpers للمتصفحات (سفاري) =====
  function extFromUrl(url){
    try{
      const u = new URL(url, window.location.href);
      const p = u.pathname || '';
      const parts = p.split('.');
      return parts.length>1 ? parts.pop().toLowerCase() : '';
    }catch(_){
      const parts = (url||'').split('?')[0].split('#')[0].split('.');
      return parts.length>1 ? parts.pop().toLowerCase() : '';
    }
  }
  function videoMime(url){
    const e = extFromUrl(url);
    switch(e){
      case 'mp4': return 'video/mp4';
      case 'm4v': return 'video/x-m4v';
      case 'mov': return 'video/quicktime';
      case 'webm': return 'video/webm';
      case 'mkv': return 'video/x-matroska';
      case 'avi': return 'video/avi';
      case 'flv': return 'video/x-flv';
      case 'mpg': case 'mpeg': return 'video/mpeg';
      case '3gp': case '3gpp': return 'video/3gpp';
      case '3gpp2': return 'video/3gpp2';
      case 'hevc': return 'video/hevc';
      default: return '';
    }
  }
  function audioMime(url){
    const e = extFromUrl(url);
    switch(e){
      case 'mp3': return 'audio/mpeg';
      case 'wav': return 'audio/wav';
      case 'aac': return 'audio/aac';
      case 'm4a': case 'mp4': return 'audio/mp4';
      case 'webm': return 'audio/webm';
      case 'ogg': return 'audio/ogg';
      case 'opus': return 'audio/opus';
      case '3gp': case '3gpp': return 'audio/3gpp';
      case 'amr': return 'audio/amr';
      default: return '';
    }
  }
  function canPlay(el, mime){
    if (!mime) return true;
    try { const v = el.canPlayType(mime); return v === 'probably' || v === 'maybe'; }
    catch(_) { return true; }
  }

  // ===== Lightbox للصور =====
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
      idx = Math.max(0, Math.min(start, urls.length - 1));
      imgEl.src = urls[idx];
      const disp = urls.length > 1 ? '' : 'none';
      btnP.style.display = disp; btnN.style.display = disp;
      box.classList.remove('d-none');
    }
    function hide(){ imgEl.removeAttribute('src'); box.classList.add('d-none'); urls=[]; idx=0; }
    function step(delta){
      if (!urls.length) return;
      const n = urls.length;
      idx = (idx + delta + n) % n;
      imgEl.src = urls[idx];
    }

    btnX.addEventListener('click', hide);
    btnP.addEventListener('click', () => step(isRTL ? +1 : -1));
    btnN.addEventListener('click', () => step(isRTL ? -1 : +1));
    box.addEventListener('click', e => { if (e.target === box) hide(); });
    document.addEventListener('keydown', e => {
      if (box.classList.contains('d-none')) return;
      if (e.key === 'Escape') hide();
      if (e.key === 'ArrowLeft')  step(isRTL ? +1 : -1);
      if (e.key === 'ArrowRight') step(isRTL ? -1 : +1);
    });

    return { show, hide };
  })();

  // ===== سلايدر موحّد (صور/فيديو) =====
  document.querySelectorAll('.media-slider').forEach(function (slider) {
    let urls = [];
    try { urls = JSON.parse(slider.dataset.items || '[]'); } catch(_) { urls = []; }
    if (!Array.isArray(urls) || !urls.length) return;

    const viewport = slider.querySelector('.slider-viewport');
    const mediaEl  = slider.querySelector('.slider-media'); // img أو video
    const dotsBox  = slider.querySelector('.slider-dots');
    const prevBtn  = slider.querySelector('.slider-nav.prev');
    const nextBtn  = slider.querySelector('.slider-nav.next');

    // Safari: فلترة الصيغ غير المدعومة
    if (mediaEl && mediaEl.tagName.toLowerCase() === 'video') {
      const testV = document.createElement('video');
      urls = urls.filter(u => canPlay(testV, videoMime(u)));
    }
    if (!urls.length) { slider.classList.add('d-none'); return; }

    let current = 0;

    function loadMedia() {
      const url = urls[current];
      if (mediaEl.tagName.toLowerCase() === 'img') {
        mediaEl.src = url;
      } else {
        try { mediaEl.pause(); } catch(_) {}
        mediaEl.innerHTML = '';
        const src = document.createElement('source');
        src.src = url;
        const m = videoMime(url);
        if (m) src.type = m;        // مهم لسفاري
        mediaEl.appendChild(src);
        mediaEl.load();
      }

      if (dotsBox) {
        dotsBox.querySelectorAll('.slider-dot').forEach((dot, idx) => {
          dot.classList.toggle('active', idx === current);
        });
      }
    }

    // نقاط
    if (dotsBox) {
      dotsBox.innerHTML = '';
      if (urls.length > 1) {
        urls.forEach((_, i) => {
          const d = document.createElement('div');
          d.className = 'slider-dot' + (i === 0 ? ' active' : '');
          d.addEventListener('click', () => { current = i; loadMedia(); });
          dotsBox.appendChild(d);
        });
      }
    }

    // أزرار
    prevBtn?.addEventListener('click', () => {
      current = isRTL ? (current + 1) % urls.length
                      : (current - 1 + urls.length) % urls.length;
      loadMedia();
    });
    nextBtn?.addEventListener('click', () => {
      current = isRTL ? (current - 1 + urls.length) % urls.length
                      : (current + 1) % urls.length;
      loadMedia();
    });

    // تكبير الصور في اللايت بوكس
    if (mediaEl.tagName.toLowerCase() === 'img') {
      mediaEl.addEventListener('click', () => Lightbox.show(urls, current));
    }

    // سوايب
    if (viewport) {
      let startX = 0;
      viewport.addEventListener('touchstart', e => { startX = e.changedTouches[0].clientX; }, { passive: true });
      viewport.addEventListener('touchend', e => {
        const dx = e.changedTouches[0].clientX - startX;
        if (Math.abs(dx) > 40) {
          if (isRTL) { if (dx < 0) prevBtn?.click(); else nextBtn?.click(); }
          else       { if (dx < 0) nextBtn?.click(); else prevBtn?.click(); }
        }
      }, { passive: true });
    }

    // أول تحميل
    loadMedia();
  });
});
</script>
@endpush
