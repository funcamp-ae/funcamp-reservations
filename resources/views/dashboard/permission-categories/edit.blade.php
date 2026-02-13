@extends('dashboard.layouts.app')
@section('pageTitle', __('dashboard.edit_title', ['page_title' => __('dashboard.permission_category')]))

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
                <h1 class="d-flex fw-bolder fs-3 align-items-center my-1">@lang('dashboard.edit_title', ['page_title' => __('dashboard.permission_category')])</h1>
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
                        <a href="{{ route('permission-categories.index') }}"
                            class="text-muted text-hover-primary">@lang('dashboard.permission_categories')</a>
                    </li>
                    <!--end::Item-->
                    <!--begin::Item-->
                    <li class="breadcrumb-item">
                        <span class="bullet bg-gray-300 w-5px h-2px"></span>
                    </li>
                    <!--end::Item-->
                    <!--begin::Item-->
                    <li class="breadcrumb-item">@lang('dashboard.edit', ['page_title' => __('dashboard.permission_category')])</li>
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
                        <h3 class="fw-bolder m-0">@lang('dashboard.edit', ['page_title' => __('dashboard.permission_category')])</h3>
                    </div>
                    <!--end::Card title-->
                </div>
                <!--begin::Card header-->

                <!--begin::Content-->
                <div id="kt_content_container" class="container-xxl">
                    <!--begin::Form-->
                    <form id="kt_ecommerce_add_product_form" method="POST" action="{{ route('permission-categories.update', $category->id) }}" class="form fv-plugins-bootstrap5 fv-plugins-framework store"
                        novalidate="novalidate" data-kt-redirect="{{ route('permission-categories.index') }}">
                        @csrf
                        @method('PUT')

                        <div class="card-body border-top p-9">

                            <!--begin::Input group - Slug-->
                            <div class="row mb-6">
                                <!--begin::Label-->
                                <label
                                    class="col-lg-4 col-form-label required fw-bold fs-6">@lang('dashboard.slug')</label>
                                <!--end::Label-->
                                <!--begin::Col-->
                                <div class="col-lg-8 fv-row fv-plugins-icon-container">
                                    <input type="text" name="slug" id="slug"
                                        class="form-control form-control-lg form-control-solid"
                                        placeholder="@lang('dashboard.slug')"
                                        value="{{ $category->slug }}">
                                    <div class="fv-plugins-message-container invalid-feedback"></div>
                                </div>
                                <!--end::Col-->
                            </div>
                            <!--end::Input group-->

                            <!--begin::Input group - Names-->
                            <div class="row mb-6">
                                <!--begin::Label-->
                                <label class="col-lg-4 col-form-label required fw-bold fs-6">@lang('dashboard.name')</label>
                                <!--end::Label-->
                                <!--begin::Col-->
                                <div class="col-lg-8">
                                    <!--begin::Row-->
                                    <div class="row">
                                        <!--begin::Col-->
                                        <div class="col-lg-6 fv-row fv-plugins-icon-container">
                                            <input type="text" name="name_ar"
                                                class="form-control form-control-lg form-control-solid mb-3 mb-lg-0"
                                                placeholder="@lang('dashboard.name_ar')" value="{{ $category->name_ar }}">
                                            <div class="fv-plugins-message-container invalid-feedback"></div>
                                        </div>
                                        <!--end::Col-->
                                        <!--begin::Col-->
                                        <div class="col-lg-6 fv-row fv-plugins-icon-container">
                                            <input type="text" name="name_en"
                                                class="form-control form-control-lg form-control-solid"
                                                placeholder="@lang('dashboard.name_en')" value="{{ $category->name_en }}">
                                            <div class="fv-plugins-message-container invalid-feedback"></div>
                                        </div>
                                        <!--end::Col-->
                                    </div>
                                    <!--end::Row-->
                                </div>
                                <!--end::Col-->
                            </div>
                            <!--end::Input group-->

                            <!--begin::Input group - Icon-->
                            <div class="row mb-6">
                                <!--begin::Label-->
                                <label
                                    class="col-lg-4 col-form-label fw-bold fs-6">@lang('dashboard.icon')</label>
                                <!--end::Label-->
                                <!--begin::Col-->
                                <div class="col-lg-8 fv-row fv-plugins-icon-container">
                                    <input type="text" name="icon"
                                        class="form-control form-control-lg form-control-solid"
                                        placeholder="@lang('dashboard.icon')"
                                        value="{{ $category->icon }}">
                                    <div class="fv-plugins-message-container invalid-feedback"></div>
                                </div>
                                <!--end::Col-->
                            </div>
                            <!--end::Input group-->

                            <!--begin::Input group - Sort Order-->
                            <div class="row mb-6">
                                <!--begin::Label-->
                                <label
                                    class="col-lg-4 col-form-label fw-bold fs-6">@lang('dashboard.sort_order')</label>
                                <!--end::Label-->
                                <!--begin::Col-->
                                <div class="col-lg-8 fv-row fv-plugins-icon-container">
                                    <input type="number" name="sort_order"
                                        class="form-control form-control-lg form-control-solid"
                                        placeholder="@lang('dashboard.sort_order')"
                                        value="{{ $category->sort_order }}">
                                    <div class="fv-plugins-message-container invalid-feedback"></div>
                                </div>
                                <!--end::Col-->
                            </div>
                            <!--end::Input group-->

                            <!--begin::Input group - Is Active-->
                            <div class="row mb-6">
                                <!--begin::Label-->
                                <label class="col-lg-4 col-form-label fw-bold fs-6">@lang('dashboard.status')</label>
                                <!--end::Label-->
                                <!--begin::Col-->
                                <div class="col-lg-8 d-flex align-items-center">
                                    <div class="form-check form-check-solid form-switch fv-row">
                                        <input class="form-check-input w-45px h-30px" type="checkbox" name="is_active" id="is_active" value="1" {{ $category->is_active ? 'checked' : '' }}>
                                        <label class="form-check-label" for="is_active"></label>
                                    </div>
                                </div>
                                <!--end::Col-->
                            </div>
                            <!--end::Input group-->

                        </div>
                        <!--end::Card body-->

                        <div class="card-footer d-flex justify-content-end py-6 px-9">
                            <!--begin::Button-->
                            <a href="{{ route('permission-categories.index') }}" id="kt_ecommerce_add_product_cancel"
                                class="btn btn-light me-5">@lang('dashboard.cancel')</a>
                            <!--end::Button-->
                            <!--begin::Button-->
                            <button type="submit" id="kt_ecommerce_add_product_submit" class="btn btn-primary">
                                <span class="indicator-label">@lang('dashboard.save_changes')</span>
                                <span class="indicator-progress">@lang('dashboard.please_wait')
                                    <span class="spinner-border spinner-border-sm align-middle ms-2"></span></span>
                            </button>
                            <!--end::Button-->
                        </div>
                    </form>
                    <!--end::Form-->
                </div>
                <!--end::Content-->

            </div>
        </div>
        <!--end::Container-->
    </div>
</div>
@endsection
