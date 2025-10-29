@extends('dashboard.layouts.app')
@section('pageTitle', __('dashboard.internal_notes'))
@section('content')
<div class="container">
    @if(isset($internalNote))
    <h2>{{ __('dashboard.edit_internal_note') }}</h2>
    @else
    <h2>{{ __('dashboard.create_internal_note') }}</h2>
    @endif

@if(isset($internalNote))
    @can('bookings.internal-notes.edit')
        <div class="row mb-4">
            <div class="col-md-6">
                <form class="form store" id="internal-note-form" action="{{ isset($internalNote) ? route('bookings.internal-notes.update', $internalNote->id) : route('bookings.internal-notes.store') }}" method="POST" enctype="multipart/form-data" data-kt-redirect="{{ url()->current() }}">
                    @csrf
                    @if(isset($internalNote))
                        @method('PUT')
                    @endif
                    <input type="hidden" name="id" id="note-id" value="{{ isset($internalNote) ? $internalNote->id : '' }}">
                    <div class="mb-3">
                        <label class="form-label">{{ __('dashboard.note_name') }}</label>
                        <input type="text" name="note_name" id="note-name" class="form-control" value="{{ old('note_name', isset($internalNote) ? $internalNote->note_name : '') }}" />
                    </div>
                    <div class="mb-3">
                        <label class="form-label">{{ __('dashboard.note_content') }}</label>
                        <!-- Quill editor container -->
                        <div id="editor-note-content" class="form-control" style="min-height:140px">{!! old('note_content', isset($internalNote) ? $internalNote->note_content : '') !!}</div>
                        <!-- Hidden textarea that will receive HTML from Quill on submit -->
                        <textarea name="note_content" id="note-content" class="d-none">{{ old('note_content', isset($internalNote) ? $internalNote->note_content : '') }}</textarea>
                    </div>
                    @can('bookings.internal-notes.update')
                        <button type="submit" class="btn btn-primary" id="kt_ecommerce_add_product_submit">{{ isset($internalNote) ? __('dashboard.update_item') : __('dashboard.create_item') }}</button>
                    @endcan 
                </form>
            </div>
        </div>
    @endcan
@else
    @can('bookings.internal-notes.create')
    <div class="row mb-4">
        <div class="col-md-6">
            <form class="form store" id="internal-note-form" action="{{ isset($internalNote) ? route('bookings.internal-notes.update', $internalNote->id) : route('bookings.internal-notes.store') }}" method="POST" enctype="multipart/form-data" data-kt-redirect="{{ url()->current() }}">
                @csrf
                @if(isset($internalNote))
                    @method('PUT')
                @endif
                <input type="hidden" name="id" id="note-id" value="{{ isset($internalNote) ? $internalNote->id : '' }}">
                <div class="mb-3">
                    <label class="form-label">{{ __('dashboard.note_name') }}</label>
                    <input type="text" name="note_name" id="note-name" class="form-control" value="{{ old('note_name', isset($internalNote) ? $internalNote->note_name : '') }}" />
                </div>
                <div class="mb-3">
                    <label class="form-label">{{ __('dashboard.note_content') }}</label>
                    <!-- Quill editor container -->
                    <div id="editor-note-content" class="form-control" style="min-height:140px">{!! old('note_content', isset($internalNote) ? $internalNote->note_content : '') !!}</div>
                    <!-- Hidden textarea that will receive HTML from Quill on submit -->
                    <textarea name="note_content" id="note-content" class="d-none">{{ old('note_content', isset($internalNote) ? $internalNote->note_content : '') }}</textarea>
                </div>
                @can('bookings.internal-notes.store')
                    <button type="submit" class="btn btn-primary" id="kt_ecommerce_add_product_submit">{{ isset($internalNote) ? __('dashboard.update_item') : __('dashboard.create_item') }}</button>
                @endcan
            </form>
        </div>
    </div>
    @endcan
@endif
@if(!isset($internalNote))
    <div class="row">
        <div class="col-md-12">
            <div class="table-responsive">
                <table class="table table-striped">
                    <thead>
                        <tr>
                            <th>{{ __('dashboard.note_name') }}</th>
                            <th>{{ __('dashboard.note_content') }}</th>
                            <th>{{ __('dashboard.actions') }}</th>
                        </tr>
                    </thead>
                    <tbody id="notes-table-body">
                        @forelse(isset($notes) ? $notes : [] as $note)
                            <tr data-id="{{ $note->id }}">
                                <td class="note-name">@translate($note->note_name)</td>
                            
                                <td class="note-content">@translate($note->note_content)</td>
                                <td>
                                    @can('bookings.internal-notes.edit')

                                    <a href="{{ route('internal-notes.edit', $note->id) }}" class="btn btn-sm btn-secondary">{{ __('dashboard.edit') }}</a>
                                    @endcan
                                    @can('bookings.internal-notes.destroy')
                                    <button
                                        type="button"
                                        class="btn btn-sm btn-danger delete-note-btn"
                                        data-id="{{ $note->id }}"
                                        data-url="{{ route('internal-notes.destroy', $note->id) }}"
                                        data-name="{{ $note->note_name }}"
                                    >{{ __('dashboard.delete') }}</button>
                                    @endcan
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="3" class="text-center">{{ __('dashboard.no_data_found') }}</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
@endif
@endsection

@section('scripts')
    <script>
    document.addEventListener('DOMContentLoaded', function(){
        // Initialize Quill for the note content with extended toolbar, fonts, colors and background support
        if (typeof Quill !== 'undefined') {
            // Register custom font whitelist
            var Font = Quill.import('formats/font');
            Font.whitelist = ['arial', 'roboto', 'times-new-roman', 'courier-new', 'open-sans', 'serif', 'monospace'];
            Quill.register(Font, true);
            // Add CSS so the font names in the picker map to actual font-family values
            var style = document.createElement('style');
            style.type = 'text/css';
            style.innerHTML = "\
                .ql-font-roboto { font-family: 'Roboto', sans-serif; }\
                .ql-font-arial { font-family: Arial, Helvetica, sans-serif; }\
                .ql-font-times-new-roman { font-family: 'Times New Roman', Times, serif; }\
                .ql-font-courier-new { font-family: 'Courier New', Courier, monospace; }\
                .ql-font-open-sans { font-family: 'Open Sans', sans-serif; }\
                .ql-font-serif { font-family: serif; }\
                .ql-font-monospace { font-family: monospace; }";
            document.head.appendChild(style);

            // Toolbar with many options: fonts, sizes, colors, background, align, lists, scripts, media, code, clean...
            var toolbarOptions = [
                [{ 'font': Font.whitelist }],
                [{ 'header': [1, 2, 3, 4, 5, 6, false] }],
                ['bold', 'italic', 'underline', 'strike'],
                [{ 'script': 'sub' }, { 'script': 'super' }],
                [{ 'size': ['small', false, 'large', 'huge'] }],
                [{ 'color': [] }, { 'background': [] }],
                [{ 'align': [] }],
                [{ 'list': 'ordered' }, { 'list': 'bullet' }, { 'indent': '-1' }, { 'indent': '+1' }],
                ['blockquote', 'code-block', 'formula'],
                ['link', 'image', 'video'],
                ['clean']
            ];

            var quill = new Quill('#editor-note-content', {
                theme: 'snow',
                modules: {
                    toolbar: toolbarOptions,
                    clipboard: {
                        matchVisual: false
                    }
                }
            });

            // If there's initial HTML in the hidden textarea, load it into Quill
            var hiddenTextarea = document.getElementById('note-content');
            if (hiddenTextarea && hiddenTextarea.value.trim() !== '') {
                quill.root.innerHTML = hiddenTextarea.value;
            }

            // On form submit, copy Quill HTML into the hidden textarea
            var form = document.getElementById('internal-note-form');
            if (form) {
                form.addEventListener('submit', function(e){
                    // Put the editor HTML into the hidden textarea so it posts with the form
                    hiddenTextarea.value = quill.root.innerHTML;
                });
            }
        }

        // Custom delete functionality with SweetAlert
        document.addEventListener('click', function(e) {
            if (e.target.classList.contains('delete-note-btn')) {
                e.preventDefault();
                
                const button = e.target;
                const noteId = button.getAttribute('data-id');
                const deleteUrl = button.getAttribute('data-url');
                const noteName = button.getAttribute('data-name');
                
                console.log('Delete URL:', deleteUrl); // Debug log
                console.log('Note ID:', noteId); // Debug log
                
                // Show SweetAlert confirmation
                Swal.fire({
                    title: '{{ __("dashboard.are_you_sure") }}',
                    text: `{{ __("dashboard.confirm_delete_note") }}: "${noteName}"`,
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonColor: '#8e4716',
                    cancelButtonColor: 'gray',
                    confirmButtonText: '{{ __("dashboard.yes_delete") }}',
                    cancelButtonText: '{{ __("dashboard.cancel") }}',
                    reverseButtons: true
                }).then((result) => {
                    if (result.isConfirmed) {
                        // Disable button and show loading
                        button.disabled = true;
                        button.innerHTML = '<i class="fas fa-spinner fa-spin"></i> {{ __("dashboard.deleting") }}...';
                        
                        // Perform AJAX delete request
                        fetch(deleteUrl, {
                            method: 'DELETE',
                            headers: {
                                'Content-Type': 'application/json',
                                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                                'Accept': 'application/json'
                            }
                        })
                        .then(response => {
                            console.log('Response status:', response.status);
                            console.log('Response headers:', response.headers);
                            
                            if (!response.ok) {
                                throw new Error(`HTTP error! status: ${response.status}`);
                            }
                            return response.json();
                        })
                        .then(data => {
                            if (data.success) {
                                // Success - show success message and remove row
                                Swal.fire({
                                    title: '{{ __("dashboard.deleted") }}!',
                                    text: data.message || '{{ __("dashboard.item_deleted_successfully") }}',
                                    icon: 'success',
                                    timer: 2000,
                                    showConfirmButton: false
                                });
                                
                                // Remove the row from table
                                const row = button.closest('tr');
                                row.style.transition = 'opacity 0.3s';
                                row.style.opacity = '0';
                                setTimeout(() => {
                                    row.remove();
                                    
                                    // Check if table is empty and show "no data" message
                                    const tbody = document.getElementById('notes-table-body');
                                    if (tbody.children.length === 0) {
                                        tbody.innerHTML = '<tr><td colspan="3" class="text-center">{{ __("dashboard.no_data_found") }}</td></tr>';
                                    }
                                }, 300);
                            } else {
                                // Error from server
                                Swal.fire({
                                    title: '{{ __("dashboard.error") }}!',
                                    text: data.message || '{{ __("dashboard.something_went_wrong") }}',
                                    icon: 'error'
                                });
                                
                                // Re-enable button
                                button.disabled = false;
                                button.innerHTML = '{{ __("dashboard.delete") }}';
                            }
                        })
                        .catch(error => {
                            console.error('Delete error:', error);
                            
                            // Network or other error
                            Swal.fire({
                                title: '{{ __("dashboard.error") }}!',
                                text: '{{ __("dashboard.network_error") }}',
                                icon: 'error'
                            });
                            
                            // Re-enable button
                            button.disabled = false;
                            button.innerHTML = '{{ __("dashboard.delete") }}';
                        });
                    }
                });
            }
        });
    });
    </script>
@endsection
