@section('pageTitle' , __('dashboard.payment_link_transactions'))

@extends('dashboard.layouts.app')
@section('content')

<!--begin::Post-->
<div class="post d-flex flex-column-fluid" id="kt_post">
    <!--begin::Container-->
    <div id="kt_content_container" class="container-xxl">

        <!--begin::Category-->
        <div class="card card-flush">
            <!--begin::Card header-->
            <div class="card-header align-items-center py-5 gap-2 gap-md-5">
                <!--begin::Card title-->
                <div class="card-title">
                    <h3>@lang('dashboard.payment_link_transactions')</h3>
                </div>
                <!--end::Card title-->
            </div>
            <!--end::Card header-->

            <!--begin::Card body-->
            <div class="card-body pt-0">
                <!--begin::Table-->
                <table class="table align-middle table-row-dashed fs-6 gy-5" id="kt_payment_links_table">
                    <!--begin::Table head-->
                    <thead>
                        <!--begin::Table row-->
                        <tr class="text-center fw-bolder fs-6 text-uppercase gs-0" style="background-color: #f8f9fa; font-weight: 900 !important;">
                            <th class="fw-bolder">#</th>
                            <th class="fw-bolder">@lang('dashboard.order_id')</th>
                            <th class="fw-bolder">@lang('dashboard.customer_name')</th>
                            <th class="fw-bolder">@lang('dashboard.amount')</th>
                            <th class="fw-bolder">@lang('dashboard.account_type')</th>
                            <th class="fw-bolder">@lang('dashboard.paid_at')</th>
                            <th class="fw-bolder">@lang('dashboard.handled_by')</th>
                            <th class="text-end min-w-70px fw-bolder">@lang('dashboard.actions')</th>
                        </tr>
                        <!--end::Table row-->
                    </thead>
                    <!--end::Table head-->

                    <!--begin::Table body-->
                    <tbody class="fw-bold text-gray-600">
                        @forelse ($paymentLinks as $index => $paymentLink)
                            <tr>
                                <td class="text-center">{{ $index + 1 }}</td>
                                <td>
                                    @if($paymentLink->order)
                                        <a href="{{ route('orders.show', $paymentLink->order->id) }}" class="text-hover-primary">
                                            #{{ $paymentLink->order->id }}
                                        </a>
                                    @else
                                        <span class="text-muted">-</span>
                                    @endif
                                </td>
                                <td>
                                    @if($paymentLink->customer)
                                        <a href="{{ route('customers.index') }}?name={{ $paymentLink->customer->name }}" class="text-hover-primary">
                                            {{ $paymentLink->customer->name }}
                                        </a>
                                    @else
                                        <span class="text-muted">-</span>
                                    @endif
                                </td>
                                <td>
                                    <span class="fw-bolder">{{ number_format($paymentLink->amount, 2) }}</span>
                                    <small class="text-muted">@lang('dashboard.currency_aed')</small>
                                </td>
                                <td>
                                    @if($paymentLink->order && $paymentLink->order->payment)
                                        @if($paymentLink->order->payment->bank_account)
                                            <span class="badge badge-light-primary">
                                                {{ $paymentLink->order->payment->bank_account->name ?? '-' }}
                                            </span>
                                        @else
                                            <span class="text-muted">-</span>
                                        @endif
                                    @else
                                        <span class="badge badge-light-warning">@lang('dashboard.paid_via_link')</span>
                                    @endif
                                </td>
                                <td>
                                    @if($paymentLink->paid_at)
                                        <div>
                                            <span class="d-block">{{ $paymentLink->paid_at->format('Y-m-d') }}</span>
                                            <small class="text-muted">{{ $paymentLink->paid_at->format('H:i:s') }}</small>
                                        </div>
                                    @else
                                        <span class="text-muted">-</span>
                                    @endif
                                </td>
                                <td>
                                    @if($paymentLink->handledBy)
                                        <span class="badge badge-light-success">
                                            {{ $paymentLink->handledBy->name }}
                                        </span>
                                    @else
                                        <span class="text-muted">@lang('dashboard.system')</span>
                                    @endif
                                </td>
                                <td class="text-end">
                                    <a href="#" class="btn btn-sm btn-light btn-active-light-primary" data-kt-menu-trigger="click" data-kt-menu-placement="bottom-end">
                                        @lang('dashboard.actions')
                                        <!--begin::Svg Icon | path: icons/duotune/arrows/arr072.svg-->
                                        <span class="svg-icon svg-icon-5 m-0">
                                            <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none">
                                                <path d="M11.4343 12.7344L7.25 8.55005C6.83579 8.13583 6.16421 8.13584 5.75 8.55005C5.33579 8.96426 5.33579 9.63583 5.75 10.05L11.2929 15.5929C11.6834 15.9835 12.3166 15.9835 12.7071 15.5929L18.25 10.05C18.6642 9.63584 18.6642 8.96426 18.25 8.55005C17.8358 8.13584 17.1642 8.13584 16.75 8.55005L12.5657 12.7344C12.2533 13.0468 11.7467 13.0468 11.4343 12.7344Z" fill="currentColor" />
                                            </svg>
                                        </span>
                                        <!--end::Svg Icon-->
                                    </a>
                                    <!--begin::Menu-->
                                    <div class="menu menu-sub menu-sub-dropdown menu-column menu-rounded menu-gray-600 menu-state-bg-light-primary fw-bold fs-7 w-150px py-4" data-kt-menu="true">
                                        @if($paymentLink->order)
                                        <div class="menu-item px-3">
                                            <a href="{{ route('orders.show', $paymentLink->order->id) }}" class="menu-link px-3">
                                                <i class="bi bi-eye fs-5 me-2"></i>@lang('dashboard.view_order')
                                            </a>
                                        </div>
                                        @endif
                                        
                                        @if($paymentLink->customer)
                                        <div class="menu-item px-3">
                                            <a href="{{ route('customers.index') }}?name={{ $paymentLink->customer->name }}" class="menu-link px-3">
                                                <i class="bi bi-person fs-5 me-2"></i>@lang('dashboard.view_customer')
                                            </a>
                                        </div>
                                        @endif

                                    </div>
                                    <!--end::Menu-->
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="9" class="text-center py-10">
                                    <div class="d-flex flex-column align-items-center">
                                        <i class="bi bi-inbox fs-3x text-muted mb-3"></i>
                                        <span class="text-muted fs-5">@lang('dashboard.no_data')</span>
                                    </div>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                    <!--end::Table body-->
                </table>
                <!--end::Table-->
            </div>
            <!--end::Card body-->
        </div>
        <!--end::Category-->

    </div>
    <!--end::Container-->
</div>
<!--end::Post-->

@endsection

@section('js')
<script>
    $(document).ready(function() {
        // Search functionality
        $('[data-kt-ecommerce-category-filter="search"]').on('keyup', function() {
            var value = $(this).val().toLowerCase();
            $('#kt_payment_links_table tbody tr').filter(function() {
                $(this).toggle($(this).text().toLowerCase().indexOf(value) > -1);
            });
        });
    });
</script>
@endsection
