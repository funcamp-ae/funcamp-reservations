@extends('dashboard.layouts.app')
@section('pageTitle', __('dashboard.daily_report_details'))

@php
    $photoUrls = $dailyReport->media
        ->where('type', 'photo')
        ->map(fn($m) => $m->url ?? Storage::disk('hetzner')->url($m->path))
        ->filter()
        ->values()
        ->all();

    $videoUrls = $dailyReport->media
        ->where('type', 'video')
        ->map(fn($m) => $m->url ?? Storage::disk('hetzner')->url($m->path))
        ->filter()
        ->values()
        ->all();

    $audioUrls = $dailyReport->media
        ->where('type', 'audio')
        ->map(fn($m) => $m->url ?? Storage::disk('hetzner')->url($m->path))
        ->filter()
        ->values()
        ->all();
@endphp

@section('content')
<div class="card mb-5 mb-xl-10">
  <div class="card-header">
    <h3 class="card-title">@lang('dashboard.daily_report_details')</h3>
  </div>

  <div class="card-body">
      {{-- تفاصيل أساسية --}}
      <div class="row mb-5">
          <div class="col-md-6">
              <p><strong>@lang('dashboard.title'):</strong> {{ $dailyReport->title }}</p>
              <p><strong>@lang('dashboard.details'):</strong> {{ $dailyReport->details }}</p>
          </div>
          <div class="col-md-6">
              <p><strong>@lang('dashboard.employee'):</strong> {{ $dailyReport->employee->name }}</p>
              <p><strong>@lang('dashboard.created_at'):</strong> {{ $dailyReport->created_at->format('Y-m-d H:i') }}</p>
          </div>
      </div>

      {{-- Notes --}}
      @if($dailyReport->notes)
          <div class="row mb-5">
              <div class="col-md-12">
                  <p><strong>@lang('dashboard.notes'):</strong></p>
                  <p>{{ $dailyReport->notes }}</p>
              </div>
          </div>
      @endif

      <div class="row g-3">
        {{-- PHOTO --}}
        @if(count($photoUrls))
        <div class="col-md-4">
          <label class="form-label">📷 @lang('dashboard.photo_attachment')</label>
          <div class="media-upload-container"
               data-type="photo"
               data-photos='@json($photoUrls)'>

            <div class="preview-image-container mb-2" style="{{ count($photoUrls) ? '' : 'display:none;' }}"></div>

            {{-- أزرار مخفية --}}
            <div class="d-none">
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

            <input type="file" name="photo_attachments[]" class="media-input d-none" accept="image/*" multiple>
            <input type="file" id="camera-photo-input" accept="image/*" capture="environment" class="d-none">
            <input type="hidden" class="remove-flag" name="remove_photo" value="0">
          </div>
        </div>
        @endif

        {{-- AUDIO --}}
        @if(count($audioUrls))
        <div class="col-md-4">
          <label class="form-label">🎵 @lang('dashboard.audio_attachment')</label>
          <div class="media-upload-container" data-type="audio">
            <div class="preview-audio-container mb-2" style="{{ count($audioUrls) ? '' : 'display:none;' }}">
              <div class="preview-audio-list">
                @foreach ($audioUrls as $url)
                  <audio controls class="w-100 mb-2">
                    <source src="{{ $url }}">
                  </audio>
                @endforeach
              </div>
            </div>

            {{-- أزرار مخفية --}}
            <div class="d-none">
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

            <input type="file" name="audio_attachments[]" class="media-input d-none" accept="audio/*" multiple>
            <input type="hidden" class="remove-flag" name="remove_audio" value="0">
          </div>
        </div>
        @endif

        {{-- VIDEO --}}
        @if(count($videoUrls))
        <div class="col-md-4">
          <label class="form-label">🎬 @lang('dashboard.video_attachment')</label>
          <div id="video-slider"
               class="video-slider {{ count($videoUrls) ? '' : 'd-none' }}"
               data-initial='@json($videoUrls)'>
            <div class="slider-viewport">
              <video class="slider-video" playsinline controls style="width:100%;height:auto;object-fit:contain;"></video>
            </div>
            <button type="button" class="slider-nav prev">‹</button>
            <button type="button" class="slider-nav next">›</button>
            <div class="slider-dots"></div>
          </div>

          {{-- أزرار مخفية --}}
          <div class="d-none">
            <button type="button" id="capture-video-btn" class="btn btn-sm btn-primary">
              🎥 @lang('dashboard.start_recording_video')
            </button>
            <button type="button" class="btn btn-sm btn-danger remove-video-all">
              <i class="bi bi-trash"></i> @lang('dashboard.delete')
            </button>
          </div>

          <input type="file" id="camera-video-input" accept="video/*" capture class="d-none">
          <input type="hidden" name="remove_video" id="remove_video_flag" value="0">
          <input type="hidden" id="video_note_direct_key" name="video_note_direct_key" value="">
          <input type="hidden" id="video_upload_id" value="">
          <div id="video-error" class="text-danger small mt-1" style="display:none"></div>
        </div>
        @endif
      </div>
  </div>
</div>

{{-- Lightbox للصور --}}
<div id="photo-lightbox" class="lightbox d-none">
  <span class="close">&times;</span>
  <img class="lightbox-img" alt="photo">
  <button class="nav prev"></button>
  <button class="nav next"></button>
</div>
@endsection

@include('dashboard.daily_reports.media-css-js')
