@section('pageTitle', __('dashboard.insurance_returns'))
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
                <div class="card-title">
                    <h1 class="fw-bolder fs-3 mb-0">@lang('dashboard.insurance_returns')</h1>
                </div>
                
                <!--begin::Card toolbar-->
                <div class="card-toolbar">
                    <div class="d-flex justify-content-end gap-2" data-kt-customer-table-toolbar="base">
                        @can('bank-accounts.returns.export')
                        <a href="{{ route('bank-accounts.returns.export', array_merge(['format' => 'xlsx'], request()->all())) }}" class="btn btn-sm btn-success">
                            <i class="fas fa-file-excel"></i> {{ __('dashboard.export_excel') }}
                        </a>
                        <a href="{{ route('bank-accounts.returns.export', array_merge(['format' => 'pdf'], request()->all())) }}" class="btn btn-sm btn-danger">
                            <i class="fas fa-file-pdf"></i> {{ __('dashboard.export_pdf') }}
                        </a>
                        @endcan
                    </div>
                </div>
                <!--end::Card toolbar-->
            </div>
            <!--end::Card header-->
            
            <!--begin::Filter Section-->
            <div class="card-body pt-0">
                <div class="card card-flush mb-5">
                    <div class="card-body">
                        <form action="" method="GET" class="row">
                        <div class="form-group col-6 mt-3">
                            <label for="customer_id">{{ __('dashboard.customer') }}</label>
                            <select name="customer_id" id="customer_id" class="form-control">
                                <option value="">{{ __('dashboard.all_customers') }}</option>
                                @foreach($customers as $customer)
                                    <option value="{{ $customer->id }}" {{ request('customer_id') == $customer->id ? 'selected' : '' }}>
                                        {{ $customer->name }}
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        <div class="form-group col-6 mt-3">
                            <label for="order_id">{{ __('dashboard.order_id') }}</label>
                            <select name="order_id" id="order_id" class="form-control">
                                <option value="">{{ __('dashboard.all_orders') }}</option>
                                @foreach($orders as $order)
                                    <option value="{{ $order->id }}" {{ request('order_id') == $order->id ? 'selected' : '' }}>
                                        {{ $order->id }} [{{ $order->customer->name ?? '' }}]
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        <div class="form-group col-6 mt-3">
                            <label for="insurance_status">{{ __('dashboard.insurance_status') }}</label>
                            <select name="insurance_status" id="insurance_status" class="form-control">
                                <option value="">{{ __('dashboard.all_statuses') }}</option>
                                <option value="returned" {{ request('insurance_status') == 'returned' ? 'selected' : '' }}>
                                    {{ __('dashboard.insurance_returned') }}
                                </option>
                                <option value="confiscated_full" {{ request('insurance_status') == 'confiscated_full' ? 'selected' : '' }}>
                                    {{ __('dashboard.insurance_confiscated_full') }}
                                </option>
                                <option value="confiscated_partial" {{ request('insurance_status') == 'confiscated_partial' ? 'selected' : '' }}>
                                    {{ __('dashboard.insurance_confiscated_partial') }}
                                </option>
                                <option value="not_returned" {{ request('insurance_status') == 'not_returned' ? 'selected' : '' }}>
                                    {{ __('dashboard.insurance_not_returned') }}
                                </option>
                            </select>
                        </div>

                        <div class="form-group col-3 mt-3">
                            <label for="date_from">{{ __('dashboard.date_from') }}</label>
                            <input type="date" name="date_from" id="date_from" class="form-control" value="{{ request('date_from') }}">
                        </div>

                        <div class="form-group col-3 mt-3">
                            <label for="date_to">{{ __('dashboard.date_to') }}</label>
                            <input type="date" name="date_to" id="date_to" class="form-control" value="{{ request('date_to') }}">
                        </div>

                        <div class="col-12 mt-3">
                            <button type="submit" class="btn btn-primary">
                                <i class="fa fa-search"></i> {{ __('dashboard.search') }}
                            </button>
                            @if(count(request()->all()))
                                <a href="{{ route('bank-accounts.returns') }}" class="btn btn-light-primary ms-2">
                                    <i class="fa fa-list"></i> {{ __('dashboard.showall') }}
                                </a>
                            @endif
                        </div>
                    </form>
                    </div>
                </div>
            </div>
            <!--end::Filter Section-->

            <!--begin::Summary Cards-->
            <div class="card-body pt-0">
                <div class="row g-5 mb-7">
                    <div class="col-md-3">
                        <div class="card card-flush h-100">
                            <div class="card-body d-flex flex-column justify-content-between">
                                <div class="fs-6 fw-bold text-gray-400">{{ __('dashboard.total_insurance_paid') }}</div>
                                <div class="fs-2 fw-bolder text-primary">{{ number_format($totalInsurancePaid, 2) }}</div>
                                <div class="fs-7 text-gray-600">{{ __('dashboard.' . config('app.currency')) }}</div>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="card card-flush h-100">
                            <div class="card-body d-flex flex-column justify-content-between">
                                <div class="fs-6 fw-bold text-gray-400">{{ __('dashboard.total_deducted') }}</div>
                                <div class="fs-2 fw-bolder text-danger">{{ number_format($totalDeducted, 2) }}</div>
                                <div class="fs-7 text-gray-600">{{ __('dashboard.' . config('app.currency')) }}</div>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="card card-flush h-100">
                            <div class="card-body d-flex flex-column justify-content-between">
                                <div class="fs-6 fw-bold text-gray-400">{{ __('dashboard.total_returned') }}</div>
                                <div class="fs-2 fw-bolder text-success">{{ number_format($totalReturned, 2) }}</div>
                                <div class="fs-7 text-gray-600">{{ __('dashboard.' . config('app.currency')) }}</div>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="card card-flush h-100">
                            <div class="card-body d-flex flex-column justify-content-between">
                                <div class="fs-6 fw-bold text-gray-400">{{ __('dashboard.total_records') }}</div>
                                <div class="fs-2 fw-bolder text-info">{{ $payments->total() }}</div>
                                <div class="fs-7 text-gray-600">
                                    <span class="badge badge-light-success me-1">{{ $statusCounts['returned'] }}</span>
                                    <span class="badge badge-light-danger me-1">{{ $statusCounts['confiscated_full'] }}</span>
                                    <span class="badge badge-light-warning me-1">{{ $statusCounts['confiscated_partial'] }}</span>
                                    <span class="badge badge-danger">{{ $statusCounts['not_returned'] }}</span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <!--end::Summary Cards-->

            <!--begin::Card body-->
            <div class="card-body pt-0">
                <!--begin::Table-->
                <div class="table-responsive">
                    <table class="table align-middle table-row-dashed fs-6 gy-5" id="kt_insurance_returns_table">
                        <!--begin::Table head-->
                        <thead>
                            <tr class="text-center fw-bolder fs-6 text-uppercase gs-0" style="background-color: #f8f9fa; font-weight: 900 !important;">
                                <th class="fw-bolder">{{ __('dashboard.booking_number') }}</th>
                                <th class="fw-bolder">{{ __('dashboard.date') }}</th>
                                <th class="fw-bolder">{{ __('dashboard.customer_name') }}</th>
                                <th class="fw-bolder">{{ __('dashboard.insurance_amount_paid') }}</th>
                                <th class="fw-bolder">{{ __('dashboard.payment_method') }}</th>
                                <th class="fw-bolder">{{ __('dashboard.deducted_amount') }}</th>
                                <th class="fw-bolder">{{ __('dashboard.return_amount') }}</th>
                                <th class="fw-bolder">{{ __('dashboard.insurance_status') }}</th>
                                <th class="fw-bolder">{{ __('dashboard.bank_account') }}</th>
                            </tr>
                        </thead>
                        <!--end::Table head-->

                        <!--begin::Table body-->
                        <tbody class="fw-bold text-gray-600">
                            @forelse($payments as $payment)
                                @php
                                    if($payment->order->insurance_status) {
                                        $deductedAmount = $payment->transaction ? $payment->transaction->amount : 0;
                                        $returnAmount = $payment->price - $deductedAmount;
                                    } else {
                                        $deductedAmount = 0;
                                        $returnAmount = 0;
                                    }
                                @endphp
                                <tr>
                                    <!--begin::Order Number-->
                                    <td class="text-center">
                                        @if($payment->order)
                                            <a href="{{ route('orders.show', $payment->order->id) }}" class="text-gray-800 text-hover-primary fw-bolder">
                                                #{{ $payment->order->id }}
                                            </a>
                                        @else
                                            <span class="text-gray-400">N/A</span>
                                        @endif
                                    </td>
                                    <!--end::Order Number-->

                                    <!--begin::Date-->
                                    <td class="text-center">
                                        <span class="fw-bold">{{ $payment->created_at->format('Y-m-d') }}</span>
                                        <br>
                                        <span class="text-muted fs-7">{{ $payment->created_at->format('H:i') }}</span>
                                    </td>
                                    <!--end::Date-->

                                    <!--begin::Customer-->
                                    <td>
                                        @if($payment->order && $payment->order->customer)
                                            <div class="d-flex align-items-center">
                                                <div class="d-flex flex-column">
                                                    <span class="text-gray-800 fw-bolder">{{ $payment->order->customer->name }}</span>
                                                    <span class="text-muted fs-7">{{ $payment->order->customer->phone ?? '' }}</span>
                                                </div>
                                            </div>
                                        @else
                                            <span class="text-gray-400">N/A</span>
                                        @endif
                                    </td>
                                    <!--end::Customer-->

                                    <!--begin::Insurance Paid-->
                                    <td class="text-center">
                                        <span class="fw-bold text-primary">{{ number_format($payment->price, 2) }}</span>
                                        <span class="text-muted fs-7">{{ __('dashboard.' . config('app.currency')) }}</span>
                                    </td>
                                    <!--end::Insurance Paid-->

                                    <!--begin::Payment Method-->
                                    <td class="text-center">
                                        <span class="badge badge-light-info">{{ __('dashboard.' . $payment->payment_method) }}</span>
                                    </td>
                                    <!--end::Payment Method-->

                                    <!--begin::Deducted Amount-->
                                    <td class="text-center">
                                        <span class="fw-bold text-danger">{{ number_format($deductedAmount, 2) }}</span>
                                        <span class="text-muted fs-7">{{ __('dashboard.' . config('app.currency')) }}</span>
                                    </td>
                                    <!--end::Deducted Amount-->

                                    <!--begin::Return Amount-->
                                    <td class="text-center">
                                        <span class="fw-bold text-success">{{ number_format($returnAmount, 2) }}</span>
                                        <span class="text-muted fs-7">{{ __('dashboard.' . config('app.currency')) }}</span>
                                    </td>
                                    <!--end::Return Amount-->

                                    <!--begin::Status-->
                                    <td class="text-center">
                                        @if($payment->order)
                                            @if($payment->order->insurance_status === 'returned')
                                                <span class="badge badge-success">{{ __('dashboard.insurance_returned') }}</span>
                                            @elseif($payment->order->insurance_status === 'confiscated_full')
                                                <span class="badge badge-danger">{{ __('dashboard.insurance_confiscated_full') }}</span>
                                            @elseif($payment->order->insurance_status === 'confiscated_partial')
                                                <span class="badge badge-warning">{{ __('dashboard.insurance_confiscated_partial') }}</span>
                                            @else
                                                <span class="badge badge-danger">{{ __('dashboard.insurance_not_returned') }}</span>
                                            @endif
                                        @else
                                            <span class="text-gray-400">N/A</span>
                                        @endif
                                    </td>
                                    <!--end::Status-->

                                    <!--begin::Bank Account-->
                                    <td class="text-center">
                                        @if($payment->account)
                                            <span class="text-gray-800">{{ $payment->account->name }}</span>
                                        @else
                                            <span class="text-gray-400">N/A</span>
                                        @endif
                                    </td>
                                    <!--end::Bank Account-->
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="9" class="text-center py-10">
                                        <div class="text-gray-400 fs-4">
                                            <i class="fas fa-inbox fs-2x mb-3"></i>
                                            <p>{{ __('dashboard.no_data_available') }}</p>
                                        </div>
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                        <!--end::Table body-->
                    </table>
                </div>
                <!--end::Table-->

                <!--begin::Pagination-->
                <div class="d-flex justify-content-center mt-5">
                    {{ $payments->appends(request()->all())->links() }}
                </div>
                <!--end::Pagination-->
            </div>
            <!--end::Card body-->
        </div>
        <!--end::Category-->
    </div>
    <!--end::Container-->
</div>
<!--end::Post-->
@endsection

@push('scripts')
<script>
    // Select2 initialization if needed
    $(document).ready(function() {
        $('#customer_id, #order_id, #insurance_status').select2({
            placeholder: "{{ __('dashboard.select') }}",
            allowClear: true
        });
    });
</script>
@endpush
