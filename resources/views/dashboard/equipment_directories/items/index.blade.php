@extends('dashboard.layouts.app')
@section('pageTitle', $equipmentDirectory->name . ' - ' . __('dashboard.items'))

@section('content')
    <div class="card card-flush">
        <div class="card-header align-items-center py-5 gap-2 gap-md-5">
            <div class="card-title">
                <h3>{{ $equipmentDirectory->name }}</h3>
                <div class="d-flex align-items-center position-relative my-1">
                    <input type="text" class="form-control form-control-solid w-250px ps-14" placeholder="@lang('dashboard.search_items')"
                        id="search-input" />
                </div>
            </div>
            <div class="card-toolbar">
                <a href="{{ route('equipment-directories.items.export', $equipmentDirectory) }}" class="btn btn-success me-2"
                    title="@lang('dashboard.export_pdf')">
                    <i class="fas fa-file-pdf"></i> @lang('dashboard.export_pdf')
                </a>
                @if (Gate::allows('equipment.create'))
                    <a href="{{ route('equipment-directories.items.create', $equipmentDirectory) }}"
                        class="btn btn-primary">
                        @lang('dashboard.add_item')
                    </a>
                @endif
                <a href="{{ route('equipment-directories.index') }}" class="btn btn-secondary ms-2">
                    @lang('dashboard.back_to_directories')
                </a>
            </div>
        </div>

        <div class="card-body pt-0">
            <div class="table-responsive">
                <table class="table align-middle table-row-dashed fs-6 gy-5">
                    <thead>
                        <tr class="text-start  fw-bolder fs-7 text-uppercase gs-0">
                            <th>#</th>
                            <th class="min-w-100px">@lang('dashboard.item_type')</th>
                            <th class="min-w-100px">@lang('dashboard.item_name')</th>
                            <th class="min-w-100px">@lang('dashboard.location')</th>
                            <th class="min-w-100px">@lang('dashboard.quantity')</th>
                            <th class="min-w-100px">@lang('dashboard.notes')</th>
                            <th class="min-w-100px">@lang('dashboard.creator') / @lang('dashboard.date')</th>
                            <th class="min-w-100px">@lang('dashboard.media')</th>
                            <th class="min-w-100px">@lang('dashboard.directory_status')</th>
                            <th class="text-end min-w-70px">@lang('dashboard.actions')</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($items as $item)
                            <tr>
                                <td>{{ $loop->iteration }}</td>
                                <td>{{ $item->type }}</td>
                                <td>{{ $item->name }}</td>
                                <td>{{ $item->location }}</td>
                                <td>{{ $item->quantity }}</td>
                                <td>{{ Str::limit($item->notes, 30) }}</td>
                                <td>
                                    {{ $item->creator->name }}<br>
                                    {{ $item->created_at->format('Y-m-d h:i A') }}
                                </td>
                                <td>
                                    @if ($item->media->count() > 0)
                                        <button class="btn btn-sm btn-icon btn-light-primary" data-bs-toggle="modal"
                                            data-bs-target="#mediaModal{{ $item->id }}">
                                            <i class="fas fa-images"></i> ({{ $item->media->count() }})
                                        </button>
                                    @else
                                        <span class="text-muted">@lang('dashboard.no_media')</span>
                                    @endif
                                </td>
                                <td>
                                    <span class="badge badge-{{ $item->is_active ? 'success' : 'danger' }}">
                                        {{ $item->is_active ? __('dashboard.active') : __('dashboard.inactive') }}
                                    </span>
                                </td>
                                <td>
                                    @if (Gate::allows('equipment.edit'))
                                        <a href="{{ route('equipment-directories.items.edit', [$equipmentDirectory, $item]) }}"
                                            class="btn btn-sm btn-primary" title="@lang('dashboard.edit')">
                                            <i class="fas fa-edit"></i>
                                        </a>
                                    @endif
                                    @if (Gate::allows('equipment.destroy'))
                                        <form
                                            action="{{ route('equipment-directories.items.destroy', [$equipmentDirectory, $item]) }}"
                                            method="POST" class="d-inline">
                                            @csrf @method('DELETE')
                                            <button type="submit" class="btn btn-sm btn-danger" title="@lang('dashboard.delete')"
                                                onclick="return confirm('@lang('dashboard.confirm_delete')')">
                                                <i class="fas fa-trash"></i>
                                            </button>
                                        </form>
                                    @endif
                                </td>
                            </tr>

                            <!-- Media Modal -->
                            <!-- Media Modal -->
                            <div class="modal fade" id="mediaModal{{ $item->id }}" tabindex="-1" aria-hidden="true">
                                <div class="modal-dialog modal-xl">
                                    <div class="modal-content">
                                        <div class="modal-header">
                                            <h5 class="modal-title">{{ $item->name }} - @lang('dashboard.media')</h5>
                                            <button type="button" class="btn-close" data-bs-dismiss="modal"
                                                aria-label="Close"></button>
                                        </div>
                                        <div class="modal-body">

                                            @if ($item->media->count() > 0)
                                                <!-- Nav Tabs -->
                                                <ul class="nav nav-tabs mb-3" role="tablist">
                                                    <li class="nav-item">
                                                        <a class="nav-link active" data-bs-toggle="tab"
                                                            href="#images-{{ $item->id }}" role="tab">
                                                            📷 صور
                                                            ({{ $item->media->where('file_type', 'image')->count() }})
                                                        </a>
                                                    </li>
                                                    <li class="nav-item">
                                                        <a class="nav-link" data-bs-toggle="tab"
                                                            href="#videos-{{ $item->id }}" role="tab">
                                                            🎬 فيديوهات
                                                            ({{ $item->media->where('file_type', 'video')->count() }})
                                                        </a>
                                                    </li>
                                                    <li class="nav-item">
                                                        <a class="nav-link" data-bs-toggle="tab"
                                                            href="#audios-{{ $item->id }}" role="tab">
                                                            🎵 صوتيات
                                                            ({{ $item->media->where('file_type', 'audio')->count() }})
                                                        </a>
                                                    </li>
                                                </ul>

                                                <!-- Tab Content -->
                                                <div class="tab-content">
                                                    <!-- Images -->
                                                    <div class="tab-pane fade show active" id="images-{{ $item->id }}"
                                                        role="tabpanel">
                                                        <div class="row g-3">
                                                            @forelse($item->media->where('file_type', 'image') as $media)
                                                                <div class="col-md-4">
                                                                    <img src="{{ $media->file_url }}"
                                                                        class="img-fluid rounded" alt="">
                                                                </div>
                                                            @empty
                                                                <p class="text-muted">@lang('dashboard.no_media')</p>
                                                            @endforelse
                                                        </div>
                                                    </div>

                                                    <!-- Videos -->
                                                    <div class="tab-pane fade" id="videos-{{ $item->id }}"
                                                        role="tabpanel">
                                                        <div class="row g-3">
                                                            @forelse($item->media->where('file_type', 'video') as $media)
                                                                @php
                                                                    $ext = strtolower(
                                                                        pathinfo($media->file_path, PATHINFO_EXTENSION),
                                                                    );
                                                                    $mimeMap = [
                                                                        'mp4' => 'video/mp4',
                                                                        'mov' => 'video/quicktime',
                                                                        'm4v' => 'video/x-m4v',
                                                                        'webm' => 'video/webm',
                                                                        'mkv' => 'video/x-matroska',
                                                                        'avi' => 'video/avi',
                                                                        'flv' => 'video/x-flv',
                                                                        '3gp' => 'video/3gpp',
                                                                        '3gpp' => 'video/3gpp',
                                                                        '3gpp2' => 'video/3gpp2',
                                                                        'wmv' => 'video/x-ms-wmv',
                                                                        'hevc' => 'video/hevc',
                                                                    ];
                                                                    $type = $mimeMap[$ext] ?? 'video/mp4';
                                                                    $url = $media->file_url; // مهم يكون https
                                                                @endphp

                                                                <div class="col-md-6">
                                                                    <video class="w-100 rounded equip-video" controls
                                                                        playsinline webkit-playsinline preload="none"
                                                                        {{-- نخلي التحميل بعد ما يبان --}} crossorigin="anonymous"
                                                                        {{-- سفاري + CORS --}}
                                                                        data-src="{{ $url }}">
                                                                        {{-- نأخر تعيين src لوقت فتح المودال --}}
                                                                        <source data-src="{{ $url }}"
                                                                            type="{{ $type }}">
                                                                        @lang('dashboard.video_not_supported')
                                                                    </video>
                                                                </div>
                                                            @empty
                                                                <p class="text-muted">@lang('dashboard.no_media')</p>
                                                            @endforelse
                                                        </div>
                                                    </div>



                                                    <!-- Audios -->
                                                    <div class="tab-pane fade" id="audios-{{ $item->id }}"
                                                        role="tabpanel">
                                                        <div class="row g-3">
                                                            @forelse($item->media->where('file_type', 'audio') as $media)
                                                                <div class="col-md-6">
                                                                    <audio controls class="w-100">
                                                                        <source src="{{ $media->file_url }}">
                                                                    </audio>
                                                                </div>
                                                            @empty
                                                                <p class="text-muted">@lang('dashboard.no_media')</p>
                                                            @endforelse
                                                        </div>
                                                    </div>
                                                </div>
                                            @else
                                                <p class="text-muted text-center">@lang('dashboard.no_media')</p>
                                            @endif

                                        </div>
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>
@endsection





@push('js')
<script>
document.addEventListener('shown.bs.modal', function (e) {
    const modal = e.target;
    if (!modal || !modal.id || !modal.id.startsWith('mediaModal')) return;

    // فعّل السورس عند الفتح ثم reload (سفاري يحتاجه)
    modal.querySelectorAll('video.equip-video').forEach(function(v){
        const s = v.querySelector('source');
        const src = (s && s.getAttribute('data-src')) || v.getAttribute('data-src');
        if (src) {
            if (s && !s.getAttribute('src')) s.setAttribute('src', src);
            if (!v.getAttribute('src')) v.setAttribute('src', src);
            try { v.load(); } catch(_) {}
        }
    });
});

// لما يتقفل المودال، وقّف ونظّف (يمنع علوق في سفاري)
document.addEventListener('hide.bs.modal', function (e) {
    const modal = e.target;
    if (!modal || !modal.id || !modal.id.startsWith('mediaModal')) return;

    modal.querySelectorAll('video.equip-video').forEach(function(v){
        try { v.pause(); } catch(_){}
        // لا نحذف data-src؛ بس نفرّغ src والـ <source> لتستعد للجولة الجاية
        v.removeAttribute('src');
        const s = v.querySelector('source');
        if (s) s.removeAttribute('src');
        try { v.load(); } catch(_){}
    });
});

// لو تنقّل المستخدم بين التبويبات داخل المودال، فعّل الفيديوهات في تاب "فيديو"
document.querySelectorAll('[data-bs-toggle="tab"]').forEach(function(tab){
    tab.addEventListener('shown.bs.tab', function (ev) {
        const pane = document.querySelector(ev.target.getAttribute('href'));
        if (!pane) return;
        pane.querySelectorAll('video.equip-video').forEach(function(v){
            const s = v.querySelector('source');
            const src = (s && s.getAttribute('data-src')) || v.getAttribute('data-src');
            if (src && !v.getAttribute('src')) {
                if (s && !s.getAttribute('src')) s.setAttribute('src', src);
                v.setAttribute('src', src);
                try { v.load(); } catch(_){}
            }
        });
    });
});
</script>

@endphp
