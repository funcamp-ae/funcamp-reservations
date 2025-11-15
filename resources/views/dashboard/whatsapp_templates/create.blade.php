@extends('dashboard.layouts.app')

@section('pageTitle' , __('dashboard.add_new_template'))

@section('content')
    <div class="card">
        <div class="card-header">
            <div class="card-title">
                <h3 class="fw-bold">@lang('dashboard.add_new_template')</h3>
            </div>
        </div>

        <form action="{{ route('whatsapp-templates.store') }}" method="POST">
            @csrf
            <div class="card-body">

                <div class="row">
                    <div class="col-md-6">
                        <div class="fv-row mb-7">
                            <label class="required fw-semibold fs-6 mb-2">@lang('dashboard.template_name')</label>
                            <input type="text" name="name" class="form-control form-control-solid mb-3 mb-lg-0"
                                   placeholder="@lang('dashboard.enter_template_name')" value="{{ old('name') }}" required />
                        </div>
                    </div>

                    <div class="col-md-6">
                        <div class="fv-row mb-7">
                            <div class="d-flex justify-content-between align-items-center mb-2">
                                <label class="required fw-semibold fs-6 mb-0">@lang('dashboard.template_type')</label>
                                <button type="button" id="btnAddType" class="btn btn-sm btn-light-primary">@lang('dashboard.add_type')</button>
                            </div>
                            <select name="type" id="typeSelect" class="form-select form-select-solid" required>
                                <option value="">@lang('dashboard.select_type')</option>
                                @foreach($types as $key => $value)
                                    <option value="{{ $key }}" {{ old('type') == $key ? 'selected' : '' }}>
                                        {{ $value }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                </div>

                <div class="fv-row mb-7">
                    <label class="fw-semibold fs-6 mb-2">@lang('dashboard.description')</label>
                    <textarea name="description" class="form-control form-control-solid" rows="3"
                              placeholder="@lang('dashboard.enter_description')">{{ old('description') }}</textarea>
                </div>

                <div class="row">
                    <div class="col-md-6">
                        <div class="fv-row mb-7">
                            <label class="required fw-semibold fs-6 mb-2">@lang('dashboard.arabic_message')</label>
                            <textarea id="message_ar" name="message_ar" class="form-control form-control-solid" style="min-height: 200px;">{{ old('message_ar') }}</textarea>
                        </div>
                    </div>

                    <div class="col-md-6">
                        <div class="fv-row mb-7">
                            <label class="required fw-semibold fs-6 mb-2">@lang('dashboard.english_message')</label>
                            <textarea id="message_en" name="message_en" class="form-control form-control-solid" style="min-height: 200px;">{{ old('message_en') }}</textarea>
                        </div>
                    </div>
                </div>

                <div class="fv-row mb-7">
                    <div class="form-check form-switch">
                        <input class="form-check-input" type="checkbox" name="is_active" id="is_active"
                               style="width: 3rem !important; height: 1.5rem !important;"
                               {{ old('is_active', true) ? 'checked' : '' }}>
                        <label class="form-check-label fw-semibold fs-6" for="is_active">
                            @lang('dashboard.active_template')
                        </label>
                    </div>
                </div>

                <div class="alert alert-info">
                    <div class="d-flex justify-content-between align-items-center">
                        <h5 class="mb-0">@lang('dashboard.available_placeholders'):</h5>
                        <button type="button" id="btnAddPlaceholder" class="btn btn-sm btn-light-primary">@lang('dashboard.add_placeholder')</button>
                    </div>
                    <div class="mt-3">
                        <h6 class="fw-bold mb-2">@lang('dashboard.default_placeholders')</h6>
                        <ul class="mb-3" id="defaultPlaceholdersList">
                            @foreach(($defaultPlaceholders ?? []) as $ph)
                                <li><strong>{{ $ph }}</strong></li>
                            @endforeach
                        </ul>
                        <h6 class="fw-bold mb-2">@lang('dashboard.custom_placeholders')</h6>
                        <ul class="mb-0" id="customPlaceholdersList">
                            @foreach(($dbPlaceholders ?? []) as $ph => $var)
                                <li><strong>{{ $ph }}</strong> → <code>{{ $var }}</code></li>
                            @endforeach
                        </ul>
                    </div>
                </div>
            </div>

            <div class="card-footer">
                <div class="d-flex justify-content-end">
                    <a href="{{ route('whatsapp-templates.index') }}" class="btn btn-light me-3">
                        @lang('dashboard.cancel')
                    </a>
                    <button type="submit" class="btn btn-primary">
                        @lang('dashboard.save')
                    </button>
                </div>
            </div>
        </form>
    </div>

    <!-- Add Type Modal -->
    <div class="modal fade" id="addTypeModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">@lang('dashboard.add_type')</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label">@lang('dashboard.type_key')</label>
                        <input type="text" id="typeKeyInput" class="form-control" placeholder="reservation_confirmation" />
                        <small class="text-muted">@lang('dashboard.type_key_hint')</small>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-light" data-bs-dismiss="modal">@lang('dashboard.cancel')</button>
                    <button type="button" id="saveTypeBtn" class="btn btn-primary">@lang('dashboard.add')</button>
                </div>
            </div>
        </div>
    </div>

    <!-- Add Placeholder Modal -->
    <div class="modal fade" id="addPlaceholderModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">@lang('dashboard.add_placeholder')</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label">@lang('dashboard.placeholder')</label>
                        <input type="text" id="placeholderInput" class="form-control" placeholder="[Camp Address]" />
                    </div>
                    <div class="mb-3">
                        <label class="form-label">@lang('dashboard.variable_key')</label>
                        <input type="text" id="variableKeyInput" class="form-control" placeholder="service_location" list="availableVariableKeys" />
                        <small class="text-muted">@lang('dashboard.variable_key_hint')</small>
                        @if(!empty($variableKeys))
                        <datalist id="availableVariableKeys">
                            @foreach($variableKeys as $key)
                                <option value="{{ $key }}"></option>
                            @endforeach
                        </datalist>
                        @endif
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-light" data-bs-dismiss="modal">@lang('dashboard.cancel')</button>
                    <button type="button" id="savePlaceholderBtn" class="btn btn-primary">@lang('dashboard.add')</button>
                </div>
            </div>
        </div>
    </div>
@endsection

@push('scripts')
<script src="https://cdn.ckeditor.com/ckeditor5/41.4.2/classic/ckeditor.js"></script>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Initialize CKEditor 5 for Arabic message
    ClassicEditor
        .create(document.querySelector('#message_ar'), {
            toolbar: {
                items: [
                    'bold', 'italic', 'underline', '|',
                    'bulletedList', 'numberedList', '|',
                    'link', '|',
                    'undo', 'redo'
                ]
            },
            height: '200px'
        })
        .then(editor => {
            // Set editor height
            editor.editing.view.change(writer => {
                writer.setStyle('height', '200px', editor.editing.view.document.getRoot());
            });
        })
        .catch(error => {
            console.error('CKEditor 5 Arabic initialization error:', error);
        });

    // Initialize CKEditor 5 for English message
    ClassicEditor
        .create(document.querySelector('#message_en'), {
            toolbar: {
                items: [
                    'bold', 'italic', 'underline', '|',
                    'bulletedList', 'numberedList', '|',
                    'link', '|',
                    'undo', 'redo'
                ]
            },
            height: '200px'
        })
        .then(editor => {
            // Set editor height
            editor.editing.view.change(writer => {
                writer.setStyle('height', '200px', editor.editing.view.document.getRoot());
            });
        })
        .catch(error => {
            console.error('CKEditor 5 English initialization error:', error);
        });

    // jQuery global CSRF setup
    (function(){
        var token = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');
        if (token && window.$) {
            $.ajaxSetup({ headers: { 'X-CSRF-TOKEN': token, 'Accept':'application/json', 'X-Requested-With': 'XMLHttpRequest' } });
        }
    })();

    // Bootstrap modal helpers
    function showModalById(id) {
        const el = document.getElementById(id);
        if (!el) return;
        if (window.bootstrap && bootstrap.Modal) {
            const m = bootstrap.Modal.getOrCreateInstance(el);
            m.show();
        } else {
            el.classList.add('show');
            el.style.display = 'block';
            el.removeAttribute('aria-hidden');
        }
    }
    function hideModalById(id) {
        const el = document.getElementById(id);
        if (!el) return;
        if (window.bootstrap && bootstrap.Modal) {
            const m = bootstrap.Modal.getOrCreateInstance(el);
            m.hide();
        } else {
            el.classList.remove('show');
            el.style.display = 'none';
            el.setAttribute('aria-hidden', 'true');
        }
    }

    // Add Type via modal
    const btnAddType = document.getElementById('btnAddType');
    const typeSelect = document.getElementById('typeSelect');
    const saveTypeBtn = document.getElementById('saveTypeBtn');
    const typeKeyInput = document.getElementById('typeKeyInput');
    if (btnAddType && saveTypeBtn && typeKeyInput) {
        btnAddType.addEventListener('click', function() {
            typeKeyInput.value = '';
            showModalById('addTypeModal');
        });
        saveTypeBtn.addEventListener('click', function() {
            const key = (typeKeyInput.value || '').trim();
            if (!key) return;
            $.ajax({
                url: '{{ route('dashboard.whatsapp-meta.type.store') }}',
                method: 'POST',
                contentType: 'application/json; charset=UTF-8',
                dataType: 'json',
                data: JSON.stringify({ key }),
                success: function(data){
                    if (!data.success) {
                        Swal.fire({ icon:'error', title:'Error', html: `<pre>${$('<div>').text(JSON.stringify(data.errors || data)).html()}</pre>` });
                        return;
                    }
                    const opt = document.createElement('option');
                    opt.value = data.key;
                    opt.textContent = data.label || data.key;
                    typeSelect.appendChild(opt);
                    typeSelect.value = data.key;
                    hideModalById('addTypeModal');
                    Swal.fire({ icon:'success', title:'@lang('dashboard.success')' });
                },
                error: function(xhr){
                    Swal.fire({ icon:'error', title:'Error', html: xhr.responseText || 'Request failed' });
                }
            });
        });
    }

    // Add Placeholder via modal
    const btnAddPlaceholder = document.getElementById('btnAddPlaceholder');
    const savePlaceholderBtn = document.getElementById('savePlaceholderBtn');
    const placeholderInput = document.getElementById('placeholderInput');
    const variableKeyInput = document.getElementById('variableKeyInput');
    if (btnAddPlaceholder && savePlaceholderBtn && placeholderInput && variableKeyInput) {
        btnAddPlaceholder.addEventListener('click', function() {
            placeholderInput.value = '';
            variableKeyInput.value = '';
            showModalById('addPlaceholderModal');
        });
        savePlaceholderBtn.addEventListener('click', function() {
            const placeholder = (placeholderInput.value || '').trim();
            const variable_key = (variableKeyInput.value || '').trim();
            if (!placeholder || !variable_key) return;
            $.ajax({
                url: '{{ route('dashboard.whatsapp-meta.placeholder.store') }}',
                method: 'POST',
                contentType: 'application/json; charset=UTF-8',
                dataType: 'json',
                data: JSON.stringify({ placeholder, variable_key }),
                success: function(data){
                    if (!data.success) {
                        Swal.fire({ icon:'error', title:'Error', html: `<pre>${$('<div>').text(JSON.stringify(data.errors || data)).html()}</pre>` });
                        return;
                    }
                    const li = document.createElement('li');
                    li.innerHTML = `<strong>${data.placeholder}</strong> → <code>${data.variable_key}</code>`;
                    document.getElementById('customPlaceholdersList').appendChild(li);
                    hideModalById('addPlaceholderModal');
                    Swal.fire({ icon:'success', title:'@lang('dashboard.success')' });
                },
                error: function(xhr){
                    Swal.fire({ icon:'error', title:'Error', html: xhr.responseText || 'Request failed' });
                }
            });
        });
    }
});
</script>
@endpush
