@extends('dashboard.layouts.app')
@section('pageTitle', __('dashboard.service_site_customer_service'))
@section('content')
<meta name="csrf-token" content="{{ csrf_token() }}">
<div class="container">
    <h1>{{ __('dashboard.service_site_customer_service') }}</h1>

    @if(session('success'))
        <div class="alert alert-success">{{ session('success') }}</div>
    @endif
    <div class="d-flex flex-coulmn">

        <form id="kt_ecommerce_add_product_form"
        data-kt-redirect="{{ isset($item) ? route('bookings.service-site-customer-service.edit', $item->id) : route('bookings.service-site-customer-service.create') }}"
        action="{{ isset($item) ? route('bookings.service-site-customer-service.update', $item->id) : route('bookings.service-site-customer-service.store') }}"
        method="POST" enctype="multipart/form-data"
                class="form d-flex flex-column store" dir="rtl">
                @csrf
                @if(isset($item))
                @method('PUT')
                @endif

                <div class="form-group mb-25">
                    <label for="serviceSite">{{ __('dashboard.service_site') }}</label>
                    <div id="editor-serviceSite" dir="rtl" style="min-height:150px; direction: rtl; text-align: right;">{!! old('serviceSite', isset($item) ? $item->service_site : '') !!}</div>
                    <input type="hidden" name="serviceSite" id="input-serviceSite" value="{!! old('serviceSite', isset($item) ? $item->service_site : '') !!}">
                </div>
                    <div class="d-flex flex-row flex-wrap w-100 justify-content-between overflow-hidden mb-25">
                        <div class="form-group w-45 mb-25 ">
                            <label for="workername_en">{{ __('dashboard.worker_name_en') }}</label>
                            <div id="editor-workername-en" class="w-100" dir="ltr" style="min-height:120px; direction: ltr; text-align: left;">{!! old('workername_en', isset($item) ? $item->workername_en : '') !!}</div>
                            <input type="hidden" name="workername_en" id="input-workername-en" value="{!! old('workername_en', isset($item) ? $item->workername_en : '') !!}">
                        </div>

                        <div class="form-group mb-15 w-45">
                            <label for="workername_ar">{{ __('dashboard.worker_name_ar') }}</label>
                            <div id="editor-workername-ar" class="w-100" dir="rtl" style="min-height:120px; direction: rtl; text-align: right;">{!! old('workername_ar', isset($item) ? $item->workername_ar : '') !!}</div>
                            <input type="hidden" name="workername_ar" id="input-workername-ar" value="{!! old('workername_ar', isset($item) ? $item->workername_ar : '') !!}">
                        </div>
                    </div>
                <div class="form-group mb-15  w-100 d-flex flex-column">
                    <label for="workerphone">{{ __('dashboard.worker_phone') }}</label>
                    <div class="d-flex flex-column align-items-start position-relative w-100">
                        <input type="tel" name="workerphone" class="form-control w-100 ltr-input" dir="ltr" style="direction: ltr; text-align: left;" value="{{ old('workerphone', isset($item) ? $item->workerphone : '') }}">
                    </div>
                </div>

                <!-- Guidance Images -->
                <div class="form-group mb-25 w-100">
                    <label for="guidance_images">{{ __('dashboard.guidance_images') }}</label>
                    <div id="guidance-images-container">
                        @if(isset($item) && $item->guidance_images)
                            @foreach($item->guidance_images as $index => $image)
                                <div class="image-upload-item mb-2 d-flex align-items-center gap-2">
                                    <input type="file" name="guidance_images[]" class="form-control" accept="image/*">
                                    <img src="{{ asset('storage/' . $image) }}" alt="Guidance Image" style="width: 50px; height: 50px; object-fit: cover;">
                                    <button type="button" class="btn btn-sm btn-danger remove-image">{{ __('dashboard.remove') }}</button>
                                </div>
                            @endforeach
                        @endif
                    </div>
                    <button type="button" id="add-image-btn" class="btn btn-sm btn-secondary mt-2">{{ __('dashboard.add_image') }}</button>
                </div>

                <!-- Guidance Videos (Upload with Plupload) -->
                <div class="form-group mb-25 w-100">
                    <label for="guidance_videos">{{ __('dashboard.guidance_videos') }}</label>
                    <div id="video-uploader" class="border rounded p-3" style="width: 100%; min-height: 150px;">
                        <div class="mb-2">
                            <button type="button" id="pickfiles" class="btn btn-primary">
                                <i class="fas fa-video"></i> {{ __('dashboard.select_videos') ?? 'Select Videos' }}
                            </button>
                            <!-- <button type="button" id="uploadfiles" class="btn btn-success">
                                <i class="fas fa-upload"></i> {{ __('dashboard.start_upload') ?? 'Start Upload' }}
                            </button> -->
                        </div>
                        <div id="filelist" class="mt-2">
                            <!-- Files to be uploaded will appear here -->
                        </div>
                    </div>
                    <div id="uploaded-videos-list" class="mt-3">
                        @if(isset($item) && $item->guidance_videos)
                            @foreach($item->guidance_videos as $index => $video)
                                <div class="uploaded-video-item mb-3 p-3 border rounded">
                                    <div class="d-flex justify-content-between align-items-start mb-2">
                                        <strong style="flex: 1; word-break: break-all;">Video {{ $index + 1 }}</strong>
                                        <button type="button" class="btn btn-sm btn-danger remove-uploaded-video ms-2">{{ __('dashboard.remove') }}</button>
                                    </div>
                                    <div class="video-preview mb-2">
                                        <video controls style="width: 400px; height: 225px; border-radius: 5px; background: #000; object-fit: contain;">
                                            <source src="{{ $video }}" type="video/mp4">
                                            Your browser does not support the video tag.
                                        </video>
                                    </div>
                                    <small class="text-muted d-block" style="word-break: break-all;">{{ $video }}</small>
                                    <input type="hidden" name="guidance_videos[]" value="{{ $video }}">
                                </div>
                            @endforeach
                        @endif
                    </div>
                </div>

                <!-- Important Notes -->
                <div class="form-group mb-25 w-100">
                    <label for="important_notes">{{ __('dashboard.important_notes') }}</label>
                    <div id="editor-important-notes" dir="rtl" style="min-height:150px; direction: rtl; text-align: right;">{!! old('important_notes', isset($item) ? $item->important_notes : '') !!}</div>
                    <input type="hidden" name="important_notes" id="input-important-notes" value="{!! old('important_notes', isset($item) ? $item->important_notes : '') !!}">
                </div>

                <button type="submit" id="kt_ecommerce_add_product_submit" class="btn btn-primary w-fit mb-10">
                    <span class="indicator-label">{{ isset($item) ? __('dashboard.update_item') : __('dashboard.create_item') }}</span>
                    <span class="indicator-progress">{{ __('dashboard.please_wait') }}
                        <span class="spinner-border spinner-border-sm align-middle ms-2"></span>
                    </span>
                </button>
            </form>
    </div>
 @if(!isset($item))
        <div class="col-md-12">
            <div class="table-responsive">
                <table class="table table-bordered ">
                    <thead class="thead-light">
                        <tr>
                            <th>{{ __('dashboard.service_site') }}</th>
                            <th>{{ __('dashboard.worker_name_en') }}</th>
                            <th>{{ __('dashboard.worker_name_ar') }}</th>
                            <th>{{ __('dashboard.phone') }}</th>
                            <th>{{ __('dashboard.email') }}</th>
                            <th>{{ __('dashboard.guidance_images') }}</th>
                            <th>{{ __('dashboard.guidance_videos') }}</th>
                            <th>{{ __('dashboard.important_notes') }}</th>
                            <th class="text-center">{{ __('dashboard.actions') }}</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse(isset($items) ? $items : [] as $row)
                            <tr>
                                <td>{!! $row->service_site !!}</td>
                                <td>{!! $row->workername_en !!}</td>
                                <td>{!! $row->workername_ar !!}</td>
                                <td>{{ $row->workerphone }}</td>
                                <td>{{ $row->email ?? '-' }}</td>
                                <td>
                                    @if($row->guidance_images && count($row->guidance_images) > 0)
                                        <button type="button" class="btn btn-sm btn-primary view-images" data-bs-toggle="modal" data-bs-target="#imageModal" data-images="{{ json_encode($row->guidance_images) }}">
                                            <i class="fas fa-eye"></i>
                                        </button>
                                    @else
                                        <span class="text-muted">-</span>
                                    @endif
                                </td>
                                <td>
                                    @if($row->guidance_videos && count($row->guidance_videos) > 0)
                                        <ul class="list-unstyled mb-0">
                                            @foreach($row->guidance_videos as $video)
                                                <li><a href="{{ $video }}" target="_blank" class="text-primary" style="font-size: 12px;">{{ Str::limit($video, 30) }}</a></li>
                                            @endforeach
                                        </ul>
                                    @else
                                        <span class="text-muted">-</span>
                                    @endif
                                </td>
                                <td>
                                    @if($row->important_notes)
                                        <div style="max-width: 200px; max-height: 60px; overflow: hidden;">
                                            {!! Str::limit(strip_tags($row->important_notes), 100) !!}
                                        </div>
                                    @else
                                        <span class="text-muted">-</span>
                                    @endif
                                </td>
                                <td class="text-center d-flex flex-row flex-nowrap">
                                    @can('bookings.service-site-customer-service.edit')
                                    <a href="{{ route('bookings.service-site-customer-service.edit', $row->id) }}" class="btn btn-sm btn-secondary">{{ __('dashboard.edit') }}</a>
                                    @endcan
                                    @can('bookings.service-site-customer-service.destroy')
                                    <form action="{{ route('bookings.service-site-customer-service.destroy', $row->id) }}" method="POST" style="display:inline-block;" onsubmit="return confirm('{{ __('dashboard.delete_confirmation') }}');">
                                        @csrf
                                        @method('DELETE')
                                        <button class="btn btn-sm btn-danger">{{ __('dashboard.delete') }}</button>
                                    </form>
                                    @endcan
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="9" class="text-center">{{ __('dashboard.no_data_found') }}</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    @endif

    <!-- Image Gallery Modal -->
    <div class="modal fade" id="imageModal" tabindex="-1" aria-labelledby="imageModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="imageModalLabel">{{ __('dashboard.guidance_images') }}</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <!-- Carousel -->
                    <div id="imageCarousel" class="carousel slide" data-bs-ride="carousel">
                        <div class="carousel-inner" id="carouselInner">
                            <!-- Images will be loaded here dynamically -->
                        </div>
                        <button class="carousel-control-prev" type="button" data-bs-target="#imageCarousel" data-bs-slide="prev">
                            <span class="carousel-control-prev-icon" aria-hidden="true"></span>
                            <span class="visually-hidden">Previous</span>
                        </button>
                        <button class="carousel-control-next" type="button" data-bs-target="#imageCarousel" data-bs-slide="next">
                            <span class="carousel-control-next-icon" aria-hidden="true"></span>
                            <span class="visually-hidden">Next</span>
                        </button>
                    </div>
                    <!-- Thumbnails -->
                    <div id="imageThumbnails" class="row g-2 mt-3">
                        <!-- Thumbnails will be loaded here -->
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

@section('scripts')
<!-- Plupload JS (Native API - no jQuery UI needed) -->
<script src="https://cdnjs.cloudflare.com/ajax/libs/plupload/2.3.6/plupload.full.min.js"></script>

<script>
    document.addEventListener('DOMContentLoaded', function () {
        const toolbarOptions = [
            [{ 'font': [] }, { 'size': ['small', '14px', '18px', '32px'] }],
            [{ 'header': [1, 2, 3, 4, 5, 6, false] }],
            ['bold', 'italic', 'underline', 'strike'],
            [{ 'color': [] }, { 'background': [] }],
            [{ 'align': [] }],
            [{ 'list': 'ordered'}, { 'list': 'bullet' }],
            ['link', 'clean']
        ];

        // Register fonts and sizes if needed
        var Font = Quill.import('formats/font');
        Font.whitelist = ['sans-serif', 'serif', 'monospace'];
        Quill.register(Font, true);

        var Size = Quill.import('attributors/style/size');
        Size.whitelist = ['small', '14px', '18px', '32px'];
        Quill.register(Size, true);

        // Initialize Quill editors
        const quillService = new Quill('#editor-serviceSite', { theme: 'snow', modules: { toolbar: toolbarOptions } });
        const quillEn = new Quill('#editor-workername-en', { theme: 'snow', modules: { toolbar: toolbarOptions } });
        const quillAr = new Quill('#editor-workername-ar', { theme: 'snow', modules: { toolbar: toolbarOptions } });
        const quillNotes = new Quill('#editor-important-notes', { theme: 'snow', modules: { toolbar: toolbarOptions } });

        // Enforce RTL direction on Arabic editors and serviceSite editor
        // The worker name EN field should remain LTR (quillEn)
        var setEditorRTL = function(quill){
            var root = quill.root;
            root.setAttribute('dir', 'rtl');
            root.style.direction = 'rtl';
            root.style.textAlign = 'right';
            // add caret support class
            root.classList.add('rtl-input');
        };
        setEditorRTL(quillService);
        setEditorRTL(quillAr);
        setEditorRTL(quillNotes);

        const form = document.querySelector('form');
        form.addEventListener('submit', function (e) {
            document.getElementById('input-serviceSite').value = quillService.root.innerHTML;
            document.getElementById('input-workername-en').value = quillEn.root.innerHTML;
            document.getElementById('input-workername-ar').value = quillAr.root.innerHTML;
            document.getElementById('input-important-notes').value = quillNotes.root.innerHTML;
            
            // Debug: Log all guidance_videos inputs before submission
            const videoInputs = document.querySelectorAll('input[name="guidance_videos[]"]');
            console.log('Total video inputs found:', videoInputs.length);
            videoInputs.forEach((input, index) => {
                console.log(`Video ${index + 1}:`, input.value);
            });
        });

        // Add Image functionality
        document.getElementById('add-image-btn').addEventListener('click', function() {
            const container = document.getElementById('guidance-images-container');
            const div = document.createElement('div');
            div.className = 'image-upload-item mb-2 d-flex align-items-center gap-2';
            div.innerHTML = `
                <input type="file" name="guidance_images[]" class="form-control" accept="image/*">
                <button type="button" class="btn btn-sm btn-danger remove-image">{{ __('dashboard.remove') }}</button>
            `;
            container.appendChild(div);
        });

        // Remove Image
        document.addEventListener('click', function(e) {
            if (e.target.classList.contains('remove-image')) {
                e.target.closest('.image-upload-item').remove();
            }
        });

        // Initialize Plupload for video uploads (Native API without jQuery UI)
        var uploader = new plupload.Uploader({
            runtimes: 'html5',
            browse_button: 'pickfiles',
            container: document.getElementById('video-uploader'),
            url: '{{ route("dashboard.upload-guidance-video") }}',
            chunk_size: '2mb',
            max_file_size: '1000mb',
            filters: {
                mime_types: [
                    { title: "Video files", extensions: "mp4,mov,avi,wmv,flv,mkv,webm,m4v,3gp" }
                ]
            },
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            },
            
            init: {
                FilesAdded: function(up, files) {
                    var fileList = document.getElementById('filelist');
                    plupload.each(files, function(file) {
                        fileList.innerHTML += '<div id="' + file.id + '" class="mb-2 p-2 border rounded">' + 
                            file.name + ' (' + plupload.formatSize(file.size) + ') <b></b>' +
                        '</div>';
                    });
                    // Auto-start upload when files are added
                    uploader.start();
                },
                
                UploadProgress: function(up, file) {
                    document.getElementById(file.id).getElementsByTagName('b')[0].innerHTML = '<span>' + file.percent + "%</span>";
                },
                
                Error: function(up, err) {
                    console.error('Error:', err.message);
                    alert('Error: ' + err.message);
                },
                
                FileUploaded: function(up, file, response) {
                    console.log('FileUploaded callback triggered');
                    console.log('Response:', response.response);
                    
                    var data = JSON.parse(response.response);
                    console.log('Parsed data:', data);
                    
                    // Handle nested jsonrpc response structure
                    var uploadData = data.result && data.result.original ? data.result.original : data;
                    console.log('Upload data:', uploadData);
                    
                    if (uploadData.status === 'success') {
                        // Remove the upload progress item
                        document.getElementById(file.id).remove();
                        
                        // Add the uploaded video URL to the form with preview
                        var videoListContainer = document.getElementById('uploaded-videos-list');
                        console.log('Video list container:', videoListContainer);
                        
                        var div = document.createElement('div');
                        div.className = 'uploaded-video-item mb-3 p-3 border rounded';
                        div.innerHTML = `
                            <div class="d-flex justify-content-between align-items-start mb-2">
                                <strong style="flex: 1; word-break: break-all;">${uploadData.filename || 'Video'}</strong>
                                <button type="button" class="btn btn-sm btn-danger remove-uploaded-video ms-2">{{ __('dashboard.remove') }}</button>
                            </div>
                            <div class="video-preview mb-2">
                                <video controls style="width: 400px; height: 225px; border-radius: 5px; background: #000; object-fit: contain;">
                                    <source src="${uploadData.url}" type="video/mp4">
                                    Your browser does not support the video tag.
                                </video>
                            </div>
                            <small class="text-muted d-block" style="word-break: break-all;">${uploadData.url}</small>
                            <input type="hidden" name="guidance_videos[]" value="${uploadData.url}">
                        `;
                        videoListContainer.appendChild(div);
                        
                        console.log('Video div appended');
                        console.log('Hidden inputs now:', document.querySelectorAll('input[name="guidance_videos[]"]').length);
                    } else {
                        console.error('Upload failed or unexpected response structure');
                    }
                }
            }
        });

        uploader.init();

        // Remove uploaded video
        document.addEventListener('click', function(e) {
            if (e.target.classList.contains('remove-uploaded-video')) {
                const videoItem = e.target.closest('.uploaded-video-item');
                const videoInput = videoItem.querySelector('input[name="guidance_videos[]"]');
                const videoUrl = videoInput ? videoInput.value : null;
                
                if (!videoUrl) {
                    videoItem.remove();
                    return;
                }
                
                // Confirm deletion
                if (!confirm('{{ __("dashboard.delete_confirmation") ?? "Are you sure you want to delete this video?" }}')) {
                    return;
                }
                
                // Send delete request to server
                fetch('{{ route("dashboard.delete-guidance-video") }}', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                    },
                    body: JSON.stringify({
                        video_url: videoUrl
                    })
                })
                .then(response => response.json())
                .then(data => {
                    if (data.status === 'success') {
                        videoItem.remove();
                        console.log('Video deleted successfully');
                    } else {
                        alert(data.message || 'Failed to delete video');
                        console.error('Delete failed:', data);
                    }
                })
                .catch(error => {
                    console.error('Error deleting video:', error);
                    alert('An error occurred while deleting the video');
                });
            }
        });

        // Handle Image Gallery Modal
        document.addEventListener('click', function(e) {
            if (e.target.closest('.view-images')) {
                const button = e.target.closest('.view-images');
                const images = JSON.parse(button.getAttribute('data-images'));
                const carouselInner = document.getElementById('carouselInner');
                const thumbnails = document.getElementById('imageThumbnails');
                
                // Clear previous images
                carouselInner.innerHTML = '';
                thumbnails.innerHTML = '';
                
                // Add images to carousel
                images.forEach(function(image, index) {
                    // Create carousel item
                    const carouselItem = document.createElement('div');
                    carouselItem.className = 'carousel-item' + (index === 0 ? ' active' : '');
                    carouselItem.innerHTML = `
                        <img src="${'{{ asset("storage/") }}/' + image}" class="d-block w-100" alt="Guidance Image" style="max-height: 500px; object-fit: contain;">
                    `;
                    carouselInner.appendChild(carouselItem);
                    
                    // Create thumbnail
                    const thumbnailCol = document.createElement('div');
                    thumbnailCol.className = 'col-3';
                    thumbnailCol.innerHTML = `
                        <img src="${'{{ asset("storage/") }}/' + image}" 
                             class="img-thumbnail" 
                             alt="Thumbnail" 
                             style="width: 100%; height: 80px; object-fit: cover; cursor: pointer;" 
                             data-bs-target="#imageCarousel" 
                             data-bs-slide-to="${index}">
                    `;
                    thumbnails.appendChild(thumbnailCol);
                });
            }
        });
    });
</script>
@endsection

@section('styles')
<style>
    /* RTL input/editor adjustments */
    .rtl-input{ font-size:14px; caret-color: auto; }
    /* Make sure Quill placeholder text aligns right in RTL editors */
    .ql-editor[dir="rtl"]::before{ right: 0; left: auto; }
    /* If you have inline country select next to phone input, ensure it stays inline */
    .country-select-wrap { display: inline-flex; align-items: center; gap:8px; }
    
    /* Carousel styling */
    #imageCarousel .carousel-control-prev-icon,
    #imageCarousel .carousel-control-next-icon {
        background-color: rgba(0, 0, 0, 0.5);
        border-radius: 50%;
        padding: 20px;
    }
    
    #imageCarousel .carousel-control-prev,
    #imageCarousel .carousel-control-next {
        width: 5%;
    }
    
    #imageThumbnails .img-thumbnail {
        border: 2px solid #ddd;
        transition: border-color 0.3s;
    }
    
    #imageThumbnails .img-thumbnail:hover {
        border-color: #007bff;
    }
    
    .carousel-inner {
        background-color: #000;
        min-height: 400px;
        display: flex;
        align-items: center;
    }
</style>
@endsection
