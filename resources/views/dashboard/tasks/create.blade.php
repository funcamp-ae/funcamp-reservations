@extends('dashboard.layouts.app')
@section('pageTitle', isset($task) ? __('dashboard.edit_task') : __('dashboard.add_task'))

@section('content')
    <div class="content d-flex flex-column flex-column-fluid" id="kt_content">
        <!--begin::Toolbar-->
        <div class="toolbar" id="kt_toolbar">
            <!--begin::Container-->
            <div id="kt_toolbar_container" class="container-fluid d-flex flex-stack">
                <!--begin::Page title-->
                <div data-kt-swapper="true" data-kt-swapper-mode="prepend"
                     data-kt-swapper-parent="{default: '#kt_content_container', 'lg': '#kt_toolbar_container'}"
                     class="page-title d-flex align-items-center flex-wrap me-3 mb-5 mb-lg-0">
                    <!--begin::Title-->
                    <h1 class="d-flex  fw-bolder fs-3 align-items-center my-1">
                        {{ isset($task) ? __('dashboard.edit_task') : __('dashboard.add_task') }}
                    </h1>
                    <!--end::Title-->
                    <!--begin::Separator-->
                    <span class="h-20px border-gray-300 border-start mx-4"></span>
                    <!--end::Separator-->
                    <!--begin::Breadcrumb-->
                    <ul class="breadcrumb breadcrumb-separatorless fw-bold fs-7 my-1">
                        <!--begin::Item-->
                        <li class="breadcrumb-item text-muted">
                            <a href="{{ route('home') }}" class="text-muted text-hover-primary">@lang('dashboard.home')</a>
                        </li>
                        <!--end::Item-->
                        <!--begin::Item-->
                        <li class="breadcrumb-item">
                            <span class="bullet bg-gray-300 w-5px h-2px"></span>
                        </li>
                        <!--end::Item-->
                        <!--begin::Item-->
                        <li class="breadcrumb-item text-muted">
                            <a href="{{ route('tasks.index') }}" class="text-muted text-hover-primary">@lang('dashboard.tasks')</a>
                        </li>
                        <!--end::Item-->
                        <!--begin::Item-->
                        <li class="breadcrumb-item">
                            <span class="bullet bg-gray-300 w-5px h-2px"></span>
                        </li>
                        <!--end::Item-->
                        <!--begin::Item-->
                        <li class="breadcrumb-item ">
                            {{ isset($task) ? __('dashboard.edit_task') : __('dashboard.add_task') }}
                        </li>
                        <!--end::Item-->
                    </ul>
                    <!--end::Breadcrumb-->
                </div>
                <!--end::Page title-->
            </div>
            <!--end::Container-->
        </div>
        <!--end::Toolbar-->

        <div class="post d-flex flex-column-fluid" id="kt_post">
            <!--begin::Container-->
            <div id="kt_content_container" class="container-xxl">
                <!--begin::Basic info-->
                <div class="card mb-5 mb-xl-10">
                    <!--begin::Card header-->
                    <div class="card-header border-0 cursor-pointer" role="button" data-bs-toggle="collapse"
                         data-bs-target="#kt_account_profile_details" aria-expanded="true"
                         aria-controls="kt_account_profile_details">
                        <!--begin::Card title-->
                        <div class="card-title m-0">
                            <h3 class="fw-bolder m-0">{{ isset($task) ? __('dashboard.edit_task') : __('dashboard.add_task') }}</h3>
                        </div>
                        <!--end::Card title-->
                    </div>
                    <!--begin::Card header-->
                    <!--begin::Content-->
                    <div id="kt_account_settings_profile_details" class="collapse show">
                        <!--begin::Form-->
                        <form id="kt_task_form" method="POST"
                              action="{{ isset($task) ? route('tasks.update', $task->id) : route('tasks.store') }}"
                              class="form fv-plugins-bootstrap5 fv-plugins-framework"
                              data-kt-redirect="{{ route('tasks.index') }}"
                              novalidate="novalidate">
                            @csrf
                            @if(isset($task)) @method('PUT') @endif

                            <!--begin::Card body-->
                            <div class="card-body border-top p-9">
                                <!--begin::Input group-->
                                <div class="row mb-6">
                                    <!--begin::Label-->
                                    <label class="col-lg-4 col-form-label required fw-bold fs-6">@lang('dashboard.title')</label>
                                    <!--end::Label-->
                                    <!--begin::Col-->
                                    <div class="col-lg-8 fv-row fv-plugins-icon-container">
                                        <input type="text" name="title" required
                                               class="form-control form-control-lg form-control-solid"
                                               placeholder="@lang('dashboard.task_title')"
                                               value="{{ isset($task) ? $task->title : old('title') }}">
                                        <div class="fv-plugins-message-container invalid-feedback"></div>
                                    </div>
                                    <!--end::Col-->
                                </div>
                                <!--end::Input group-->

                                <!--begin::Input group-->
                                <div class="row mb-6">
                                    <!--begin::Label-->
                                    <label class="col-lg-4 col-form-label fw-bold fs-6">@lang('dashboard.description')</label>
                                    <!--end::Label-->
                                    <!--begin::Col-->
                                    <div class="col-lg-8 fv-row fv-plugins-icon-container">
                                    <textarea name="description" rows="3"
                                              class="form-control form-control-lg form-control-solid"
                                              placeholder="@lang('dashboard.task_description')">{{
                                                preg_replace('/\R+/', "\n",
                                                    isset($task) ?
                                                    strip_tags($task->description) :
                                                    strip_tags(old('description'))
                                                )
                                             }}</textarea>
                                        <div class="fv-plugins-message-container invalid-feedback"></div>
                                    </div>
                                    <!--end::Col-->
                                </div>
                                <!--end::Input group-->

                                <!--begin::Input group-->
                                <div class="row mb-6">
                                    <!--begin::Label-->
                                    <label class="col-lg-4 col-form-label fw-bold fs-6">@lang('dashboard.task_type')</label>
                                    <!--end::Label-->
                                    <!--begin::Col-->
                                    <div class="col-lg-8 fv-row fv-plugins-icon-container">
                                        <div class="d-flex gap-2">
                                            <select name="task_type_id" id="task_type_select" aria-label="Select Task Type"
                                                    data-control="select2" data-placeholder="@lang('dashboard.select_task_type')"
                                                    class="form-select form-select-solid form-select-lg fw-bold flex-grow-1">
                                                <option value="">@lang('dashboard.select_task_type')</option>
                                                @foreach ($taskTypes as $taskType)
                                                    <option value="{{ $taskType->id }}"
                                                    @if(isset($task))
                                                        {{ $task->task_type_id == $taskType->id ? 'selected' : '' }}
                                                        @else
                                                        {{ old('task_type_id') == $taskType->id ? 'selected' : '' }}
                                                        @endif
                                                    >{{ $taskType->name }}</option>
                                                @endforeach
                                            </select>
                                            <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#createTaskTypeModal">
                                                <i class="bi bi-plus-lg"></i>
                                            </button>
                                        </div>
                                        <div class="fv-plugins-message-container invalid-feedback"></div>
                                        @error('task_type_id')
                                            <div class="invalid-feedback d-block">{{ $message }}</div>
                                        @enderror
                                    </div>
                                    <!--end::Col-->
                                </div>
                                <!--end::Input group-->

                                <!--begin::Input group-->
                                <div class="row mb-6">
                                    <!--begin::Label-->
                                    <label class="col-lg-4 col-form-label required fw-bold fs-6">@lang('dashboard.assigned_to')</label>
                                    <!--end::Label-->
                                    <!--begin::Col-->
                                    <div class="col-lg-8 fv-row fv-plugins-icon-container">
                                        <select name="assigned_to" required aria-label="Select User"
                                                data-control="select2" data-placeholder="@lang('dashboard.select_user')"
                                                class="form-select form-select-solid form-select-lg fw-bold">
                                            <option value="">@lang('dashboard.select_user')</option>
                                            @foreach ($users as $user)
                                                <option value="{{ $user->id }}"
                                                @if(isset($task))
                                                    {{ $task->assigned_to == $user->id ? 'selected' : '' }}
                                                    @else
                                                    {{ old('assigned_to') == $user->id ? 'selected' : '' }}
                                                    @endif
                                                >{{ $user->name }}</option>
                                            @endforeach
                                        </select>
                                        <div class="fv-plugins-message-container invalid-feedback"></div>
                                    </div>
                                    <!--end::Col-->
                                </div>
                                <!--end::Input group-->

                                <!--begin::Input group-->
                                <div class="row mb-6">
                                    <!--begin::Label-->
                                    <label class="col-lg-4 col-form-label required fw-bold fs-6">@lang('dashboard.due_date')</label>
                                    <!--end::Label-->
                                    <!--begin::Col-->
                                    <div class="col-lg-8 fv-row fv-plugins-icon-container">
                                        <input type="date" name="due_date" required
                                               class="form-control form-control-lg form-control-solid"
                                               value="{{ isset($task) ? $task->due_date->format('Y-m-d') : old('due_date') }}">
                                        <div class="fv-plugins-message-container invalid-feedback"></div>
                                    </div>
                                    <!--end::Col-->
                                </div>
                                <!--end::Input group-->

                                <!--begin::Input group-->
                                <div class="row mb-6">
                                    <!--begin::Label-->
                                    <label class="col-lg-4 col-form-label required fw-bold fs-6">@lang('dashboard.priority')</label>
                                    <!--end::Label-->
                                    <!--begin::Col-->
                                    <div class="col-lg-8 fv-row fv-plugins-icon-container">
                                        <select name="priority" required aria-label="Select Priority"
                                                class="form-select form-select-solid form-select-lg fw-bold">
                                            <option value="low"
                                                    @if(isset($task) && $task->priority == 'low') selected @endif
                                            >@lang('dashboard.low')</option>
                                            <option value="medium"
                                                    @if(isset($task) && $task->priority == 'medium') selected @elseif(!isset($task)) selected @endif
                                            >@lang('dashboard.medium')</option>
                                            <option value="high"
                                                    @if(isset($task) && $task->priority == 'high') selected @endif
                                            >@lang('dashboard.high')</option>
                                        </select>
                                        <div class="fv-plugins-message-container invalid-feedback"></div>
                                    </div>
                                    <!--end::Col-->
                                </div>
                                <!--end::Input group-->

                                @if(isset($task))
                                    <!--begin::Input group-->
                                    <div class="row mb-6">
                                        <!--begin::Label-->
                                        <label class="col-lg-4 col-form-label required fw-bold fs-6">@lang('dashboard.task_status')</label>
                                        <!--end::Label-->
                                        <!--begin::Col-->
                                        <div class="col-lg-8 fv-row fv-plugins-icon-container">
                                            <select name="status" required aria-label="Select Status"
                                                    class="form-select form-select-solid form-select-lg fw-bold">
                                                <option value="pending"
                                                        @if($task->status == 'pending') selected @endif
                                                >@lang('dashboard.pending')</option>
                                                <option value="in_progress"
                                                        @if($task->status == 'in_progress') selected @endif
                                                >@lang('dashboard.in_progress')</option>
                                                <option value="completed"
                                                        @if($task->status == 'completed') selected @endif
                                                >@lang('dashboard.completed')</option>
                                                <option value="failed"
                                                        @if($task->status == 'failed') selected @endif
                                                >@lang('dashboard.failed')</option>
                                            </select>
                                            <div class="fv-plugins-message-container invalid-feedback"></div>
                                        </div>
                                        <!--end::Col-->
                                    </div>
                                    <!--end::Input group-->

                                    @if(isset($task))
                                        <!--begin::Input group-->
                                        <div class="row mb-6" id="failure_reason_row" style="display: none;">
                                            <!--begin::Label-->
                                            <label class="col-lg-4 col-form-label fw-bold fs-6">@lang('dashboard.failure_reason')</label>
                                            <!--end::Label-->
                                            <!--begin::Col-->
                                            <div class="col-lg-8 fv-row fv-plugins-icon-container">
                                                <textarea name="failure_reason" rows="3"
                                                          class="form-control form-control-lg form-control-solid"
                                                          placeholder="@lang('dashboard.failure_reason')">{{ isset($task) ? $task->failure_reason : '' }}</textarea>
                                                <div class="fv-plugins-message-container invalid-feedback"></div>
                                            </div>
                                            <!--end::Col-->
                                        </div>
                                        <!--end::Input group-->
                                    @endif
                                @endif
                            </div>
                            <!--end::Card body-->

                            <!--begin::Card footer-->
                            <div class="card-footer d-flex justify-content-end py-6 px-9">
                                <!--begin::Button-->
                                <a href="{{ route('tasks.index') }}" id="kt_task_cancel"
                                   class="btn btn-light me-5">@lang('dashboard.cancel')</a>
                                <!--end::Button-->
                                <!--begin::Button-->
                                <button type="submit" id="kt_task_submit" class="btn btn-primary">
                                    <span class="indicator-label">@lang('dashboard.save_changes')</span>
                                    <span class="indicator-progress">@lang('dashboard.please_wait')
                                    <span class="spinner-border spinner-border-sm align-middle ms-2"></span></span>
                                </button>
                                <!--end::Button-->
                            </div>
                            <!--end::Card footer-->
                        </form>
                        <!--end::Form-->
                    </div>
                    <!--end::Content-->
                </div>
                <!--end::Basic info-->
            </div>
            <!--end::Container-->
        </div>
    </div>

    <!--begin::Create Task Type Modal-->
    <div class="modal fade" id="createTaskTypeModal" tabindex="-1" aria-labelledby="createTaskTypeModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="createTaskTypeModalLabel">@lang('dashboard.create_task_type')</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <form id="createTaskTypeForm" method="POST" action="javascript:void(0);">
                    @csrf
                    <div class="modal-body">
                        <!--begin::Input group-->
                        <div class="row mb-6">
                            <!--begin::Label-->
                            <label class="col-lg-4 col-form-label required fw-bold fs-6">@lang('dashboard.task_title')</label>
                            <!--end::Label-->
                            <!--begin::Col-->
                            <div class="col-lg-8 fv-row">
                                <input type="text" name="name" id="modal_task_type_name" required
                                       class="form-control form-control-lg form-control-solid"
                                       placeholder="@lang('dashboard.task_type_name')">
                                <div class="invalid-feedback"></div>
                            </div>
                            <!--end::Col-->
                        </div>
                        <!--end::Input group-->

                        <!--begin::Input group-->
                        <div class="row mb-6">
                            <!--begin::Label-->
                            <label class="col-lg-4 col-form-label fw-bold fs-6">@lang('dashboard.detailed_task_description')</label>
                            <!--end::Label-->
                            <!--begin::Col-->
                            <div class="col-lg-8 fv-row">
                                <textarea name="description" id="modal_task_type_description" rows="3"
                                          class="form-control form-control-lg form-control-solid"
                                          placeholder="@lang('dashboard.task_type_description')"></textarea>
                                <div class="invalid-feedback"></div>
                            </div>
                            <!--end::Col-->
                        </div>
                        <!--end::Input group-->

                        <!--begin::Input group-->
                        <div class="row mb-6">
                            <!--begin::Label-->
                            <label class="col-lg-4 col-form-label required fw-bold fs-6">@lang('dashboard.task_type_status')</label>
                            <!--end::Label-->
                            <!--begin::Col-->
                            <div class="col-lg-8 fv-row">
                                <select name="status" id="modal_task_type_status" required
                                        class="form-select form-select-solid form-select-lg fw-bold">
                                    <option value="active" selected>@lang('dashboard.active')</option>
                                    <option value="inactive">@lang('dashboard.inactive')</option>
                                </select>
                                <div class="invalid-feedback"></div>
                            </div>
                            <!--end::Col-->
                        </div>
                        <!--end::Input group-->
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-light" data-bs-dismiss="modal">@lang('dashboard.cancel')</button>
                        <button type="submit" class="btn btn-primary" id="saveTaskTypeBtn">
                            <span class="indicator-label">@lang('dashboard.save_changes')</span>
                            <span class="indicator-progress d-none">@lang('dashboard.please_wait')
                                <span class="spinner-border spinner-border-sm align-middle ms-2"></span></span>
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
    <!--end::Create Task Type Modal-->
@endsection

@section('scripts')
    <script>
        // Show/hide failure reason based on status selection
        function toggleFailureReason() {
            const statusSelect = $('select[name="status"]');
            const failureReasonRow = $('#failure_reason_row');

            if (statusSelect.val() === 'failed') {
                failureReasonRow.show();
                $('textarea[name="failure_reason"]').attr('required', true);
            } else {
                failureReasonRow.hide();
                $('textarea[name="failure_reason"]').attr('required', false);
            }
        }

        // Initialize on page load
        $(document).ready(function() {
            // Add ID to failure reason row for easier targeting
            @if(isset($task))
            const failureReasonRow = $('textarea[name="failure_reason"]').closest('.row');
            failureReasonRow.attr('id', 'failure_reason_row');

            // Initial state
            toggleFailureReason();

            // Listen for status changes
            $('select[name="status"]').on('change', function() {
                toggleFailureReason();
            });
            @endif

            // Handle Create Task Type Modal Form Submission
            $(document).ready(function() {
                $('#createTaskTypeForm').on('submit', function(e) {
                    e.preventDefault();
                
                const form = $(this);
                const submitBtn = $('#saveTaskTypeBtn');
                const indicator = submitBtn.find('.indicator-label');
                const progress = submitBtn.find('.indicator-progress');
                
                // Clear previous errors
                form.find('.is-invalid').removeClass('is-invalid');
                form.find('.invalid-feedback').text('');
                
                // Show loading state
                submitBtn.prop('disabled', true);
                indicator.removeClass('d-none');
                progress.addClass('d-none');
                
                $.ajax({
                    url: '{{ route("task-types.store") }}',
                    method: 'POST',
                    data: form.serialize(),
                    success: function(response) {                        
                        // Add new option to Select2 dropdown
                        var newOption = new Option(response.name, response.id, true, true);
                        var $select = $('#task_type_select');
                        
                        // Append the option and update Select2
                        $select.append(newOption);
                        $select.val(response.id);
                        $select.trigger('change');
                        
                        // Close modal
                        $('#createTaskTypeModal').modal('hide');
                        
                        // Reset form
                        form[0].reset();
                        
                        // Show success message
                        Swal.fire({
                            icon: 'success',
                            title: '@lang("dashboard.success")',
                            text: '@lang("dashboard.task_type_created_successfully")',
                            timer: 2000,
                            showConfirmButton: false
                        });
                    },
                    error: function(xhr) {
                        if (xhr.status === 422) {
                            // Validation errors
                            const errors = xhr.responseJSON.errors;
                            $.each(errors, function(field, messages) {
                                const input = form.find(`[name="${field}"]`);
                                input.addClass('is-invalid');
                                input.siblings('.invalid-feedback').text(messages[0]);
                            });
                        } else {
                            Swal.fire({
                                icon: 'error',
                                title: '@lang("dashboard.error")',
                                text: '@lang("dashboard.something_went_wrong")',
                            });
                        }
                    },
                    complete: function() {
                        // Reset button state
                        submitBtn.prop('disabled', false);
                        indicator.removeClass('d-none');
                        progress.addClass('d-none');
                    }
                });
                });
                
                // Reset form when modal is closed
                $('#createTaskTypeModal').on('hidden.bs.modal', function() {
                    $('#createTaskTypeForm')[0].reset();
                    $('#createTaskTypeForm').find('.is-invalid').removeClass('is-invalid');
                    $('#createTaskTypeForm').find('.invalid-feedback').text('');
                });
            });
        });

            // Form validation
            $('#kt_task_form').validate({
                rules: {
                    title: {
                        required: true,
                        maxlength: 255
                    },
                    assigned_to: {
                        required: true
                    },
                    due_date: {
                        required: true,
                        date: true
                    },
                    priority: {
                        required: true
                    },
                    @if(isset($task))
                    status: {
                        required: true
                    },
                    failure_reason: {
                        required: function() {
                            return $('select[name="status"]').val() === 'failed';
                        }
                    }
                    @endif
                }
            });
    </script>
@endsection
