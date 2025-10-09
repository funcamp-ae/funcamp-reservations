@extends('dashboard.layouts.app')
@section('pageTitle', isset($dailyReport) ? __('dashboard.edit_daily_report') : __('dashboard.create_daily_report'))

@php
    $photoUrls = isset($dailyReport)
        ? $dailyReport->media
            ->where('type', 'photo')
            ->map(fn($m) => $m->url ?? Storage::disk('hetzner')->url($m->path))
            ->filter()
            ->values()
            ->all()
        : [];

    $videoUrls = isset($dailyReport)
        ? $dailyReport->media
            ->where('type', 'video')
            ->map(fn($m) => $m->url ?? Storage::disk('hetzner')->url($m->path))
            ->filter()
            ->values()
            ->all()
        : [];

    $audioUrls = isset($dailyReport)
        ? $dailyReport->media
            ->where('type', 'audio')
            ->map(fn($m) => $m->url ?? Storage::disk('hetzner')->url($m->path))
            ->filter()
            ->values()
            ->all()
        : [];
@endphp

@section('content')
    <div class="card mb-5 mb-xl-10">
        <div class="card-header">
            <h3 class="card-title">@lang('dashboard.' . (isset($dailyReport) ? 'edit_daily_report' : 'create_daily_report'))</h3>
        </div>

        <div class="card-body">
            <form method="POST"
                action="{{ isset($dailyReport) ? route('daily-reports.update', $dailyReport) : route('daily-reports.store') }}"
                enctype="multipart/form-data">
                @csrf
                @if (isset($dailyReport))
                    @method('PUT')
                @endif

                {{-- Title --}}
                <div class="mb-4">
                    <label class="required form-label">@lang('dashboard.title')</label>
                    <input type="text" name="title" class="form-control" required
                        value="{{ old('title', $dailyReport->title ?? '') }}">
                </div>

                {{-- Details --}}
                <div class="mb-4">
                    <label class="required form-label">@lang('dashboard.details')</label>
                    <textarea name="details" rows="5" class="form-control" required>{{ old('details', $dailyReport->details ?? '') }}</textarea>
                </div>

                {{-- Notes --}}
                <div class="mb-4">
                    <label class="form-label">@lang('dashboard.notes')</label>
                    <textarea name="notes" rows="3" class="form-control">{{ old('notes', $dailyReport->notes ?? '') }}</textarea>
                </div>

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
                            <button type="button" class="slider-nav prev" aria-label="Prev">‹</button>
                            <button type="button" class="slider-nav next" aria-label="Next">›</button>
                            <div class="slider-dots"></div>
                        </div>

                        <div class="d-grid gap-2 mt-2">
                            <button type="button" id="capture-video-btn" class="btn btn-sm btn-primary">
                                🎥 @lang('dashboard.start_recording_video')
                            </button>

                            <input type="file" id="camera-video-input" accept="video/*" capture class="d-none">
                            <input type="file" id="pond-video" name="video_attachments[]" accept="video/*" multiple>

                            <input type="hidden" name="remove_video" id="remove_video_flag" value="0">
                            <input type="hidden" id="video_note_direct_key" name="video_note_direct_key"
                                value="">
                            <input type="hidden" id="video_upload_id" value="">

                            <button type="button" class="btn btn-sm btn-danger remove-video-all">
                                <i class="bi bi-trash"></i> @lang('dashboard.delete')
                            </button>
                        </div>
                        <div id="video-error" class="text-danger small mt-1" style="display:none"></div>
                    </div>
                </div>

                <div class="mt-4 text-end">
                    <button type="submit" class="btn btn-primary">
                        @lang('dashboard.' . (isset($dailyReport) ? 'update_report' : 'save_report'))
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


@include('dashboard.daily_reports.media-css-js')
