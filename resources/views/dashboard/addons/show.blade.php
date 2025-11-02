@extends('dashboard.layouts.app')
@section('pageTitle', __('dashboard.addons'))
@section('content')
<!-------------->
<div class="post d-flex flex-column-fluid" id="kt_post">
    <!--begin::Container-->
    <div id="kt_content_container" class="container-xxl">

        <!--begin::Basic info-->
        <div class="card mb-5 mb-xl-10">
            <!--begin::Card header-->
            <div class="card-header border-0 cursor-pointer" role="button" data-bs-toggle="collapse" data-bs-target="#kt_account_profile_details" aria-expanded="true" aria-controls="kt_account_profile_details">
                <!--begin::Card title-->
                <div class="card-title m-0">
                    <h3 class="fw-bolder m-0">{{ $addon->name }}</h3>
                </div>
                <!--end::Card title-->
                <!--begin::Actions-->
                <div class="card-toolbar">
                    <a href="{{ route('addons.edit', $addon->id) }}" class="btn btn-sm btn-secondary me-2">
                        <i class="fas fa-edit"></i> <span>@lang('dashboard.edit')</span>
                    </a>
                </div>
                <!--end::Actions-->
            </div>
            <!--begin::Card header-->
            <!--begin::Content-->
            <div id="kt_account_settings_profile_details" class="collapse show">
                <!--begin::Card body-->
                <div class="card-body border-top p-9">

                    <div class="row mb-6">
                        <label class="col-lg-4 col-form-label fw-bold fs-6">@lang('dashboard.name')</label>
                        <div class="col-lg-8">
                            <div class="form-control form-control-lg form-control-solid mb-3 mb-lg-0 bg-light">
                                {{ $addon->name }}
                            </div>
                        </div>
                    </div>

                    <div class="row mb-6">
                        <label class="col-lg-4 col-form-label fw-bold fs-6">@lang('dashboard.price')</label>
                        <div class="col-lg-8">
                            <div class="form-control form-control-lg form-control-solid bg-light">
                                {{ number_format($addon->price, 2) }} @lang('dashboard.currency_aed')
                            </div>
                        </div>
                    </div>

                    <div class="row mb-6">
                        <label class="col-lg-4 col-form-label fw-bold fs-6">@lang('dashboard.notes')</label>
                        <div class="col-lg-8">
                            <div class="form-control form-control-lg form-control-solid bg-light" style="min-height: 100px; white-space: pre-wrap;">{{ $addon->description ?? __('dashboard.no_description') }}</div>
                        </div>
                    </div>

                    <div class="row mb-6">
                        <label class="col-lg-4 col-form-label fw-bold fs-6">@lang('dashboard.created_at')</label>
                        <div class="col-lg-8">
                            <div class="form-control form-control-lg form-control-solid bg-light">
                                {{ $addon->created_at->format('Y-m-d H:i:s') }}
                            </div>
                        </div>
                    </div>

                    <div class="row mb-6">
                        <label class="col-lg-4 col-form-label fw-bold fs-6">@lang('dashboard.updated_at')</label>
                        <div class="col-lg-8">
                            <div class="form-control form-control-lg form-control-solid bg-light">
                                {{ $addon->updated_at->format('Y-m-d H:i:s') }}
                            </div>
                        </div>
                    </div>

                    @if($addon->orders()->count() > 0)
                    <div class="row mb-6">
                        <label class="col-lg-4 col-form-label fw-bold fs-6">@lang('dashboard.related_orders')</label>
                        <div class="col-lg-8">
                            <div class="badge badge-light-info">
                                {{ $addon->orders()->count() }} @lang('dashboard.orders')
                            </div>
                        </div>
                    </div>
                    @endif

                </div>
                <!--end::Card body-->
            </div>
            <!--end::Content-->
        </div>
        <!--end::Basic info-->

    </div>
    <!--end::Container-->
</div>

@endsection
