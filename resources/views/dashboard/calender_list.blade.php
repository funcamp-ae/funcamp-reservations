@extends('dashboard.layouts.app')
@section('pageTitle' , __('dashboard.calender'))
@section('content')
<div id="kt_app_content" class="app-content flex-column-fluid">
    <div id="kt_app_content_container" class="app-container container-xxl">
        <div class="card card-flush">
            <div class="card-body">
                <div class="row mb-4">
                    <div class="col-md-12">
                        <form class="row g-2 align-items-center" onsubmit="return false;">
                            <div class="col-auto">
                                <input id="filterDate" type="date" class="form-control" placeholder="{{ __('dashboard.calendar.filter.date_placeholder') }}" aria-label="{{ __('dashboard.calendar.filter.date_placeholder') }}">
                            </div>
                            <div class="col-auto">
                                <input id="searchBooking" type="text" class="form-control" placeholder="{{ __('dashboard.calendar.filter.booking_placeholder') }}" aria-label="{{ __('dashboard.calendar.filter.booking_placeholder') }}">
                            </div>
                            <div class="col-auto">
                                <input id="searchName" type="text" class="form-control" placeholder="{{ __('dashboard.calendar.filter.name_placeholder') }}" aria-label="{{ __('dashboard.calendar.filter.name_placeholder') }}">
                            </div>
                            <div class="col-auto">
                                <input id="searchPhone" type="text" class="form-control" placeholder="{{ __('dashboard.calendar.filter.phone_placeholder') }}" aria-label="{{ __('dashboard.calendar.filter.phone_placeholder') }}">
                            </div>
                            <div class="col-auto">
                                <input id="searchEmail" type="text" class="form-control" placeholder="{{ __('dashboard.calendar.filter.email_placeholder') }}" aria-label="{{ __('dashboard.calendar.filter.email_placeholder') }}">
                            </div>
                            <div class="col-auto">
                                <button id="clearFilters" class="btn btn-light">{{ __('dashboard.calendar.filter.clear') }}</button>
                            </div>
                        </form>
                    </div>
                </div>

                <div class="table-responsive">
                    <table class="table table-bordered" id="ordersTable" style="direction: rtl; text-align: right">
                        <thead>
                            <tr>
                                <th style="width:160px">{{ __('dashboard.calendar.table.header.date') }}</th>
                                <th>{{ __('dashboard.calendar.table.header.booked_services') }}</th>
                                <th style="width:360px">{{ __('dashboard.calendar.table.header.available_camps') }}</th>
                            </tr>
                        </thead>
                        <tbody>
                            @php $grouped = $orders->where('date', '>=', now()->startOfDay())->orderBy('date' , 'asc')->groupBy('date'); @endphp
                            @foreach($grouped as $date => $group)
                            <tr class="date-row">
                                <td rowspan="1">
                                    <div style="font-weight:700">{{ \Carbon\Carbon::parse($date)->format('Y-m-d') }}
                                        <div style="font-size:12px;color:#666">{{ \Carbon\Carbon::parse($date)->translatedFormat('l') }}</div>
                                    </div>
                                </td>
                                        <td>
                                            <table class="table mb-0 nested-table">
                                                <thead style="background:#b37314;color:#fff">
                                                    <tr>
                                                        <th style="width:30px">{{ __('dashboard.calendar.table.subheader.index') }}</th>
                                                        <th>{{ __('dashboard.calendar.table.subheader.camp_type') }}</th>
                                                        <th>{{ __('dashboard.calendar.table.subheader.client_name') }}</th>
                                                        <th>{{ __('dashboard.calendar.table.subheader.booking_number') }}</th>
                                                    </tr>
                                                </thead>
                                                <tbody>
                                                    @foreach($group as $index => $order)
                                                    <tr class="order-row" data-date="{{ $date }}" data-booking="{{ $order->booking_number ?? $order->id }}" data-name="{{ strtolower($order->customer->name ?? '') }}" data-phone="{{ $order->customer->phone ?? '' }}" data-email="{{ $order->customer->email ?? '' }}">
                                                        <td>{{ $loop->iteration }}</td>
                                                        <td>{{ $order->services->first()->name ?? '---' }}</td>
                                                        <td>{{ $order->customer->name ?? '---' }}</td>
                                                        <td>
                                                            @can('bookings.edit')
                                                                <a href="{{ route('orders.edit', $order->id) }}">{{ $order->booking_number ?? $order->id }}</a>
                                                            @else
                                                                {{ $order->booking_number ?? $order->id }}
                                                            @endcan
                                                        </td>
                                                    </tr>
                                                    @endforeach
                                                </tbody>
                                            </table>
                                        </td>
                                        {{-- Available camps column for the first row of the date group --}}
                                            <td style="vertical-align: top;">
                                                <div style="min-height:40px;padding:6px">
                                                    @php
                                                        $services = \App\Models\Service::availableOn($date);
                                                    @endphp
                                                    @if($services && $services->count())
                                                        <select class="form-select available-services-select" data-date="{{ $date }}">
                                                            <option value="">{{ __('dashboard.calendar.select.choose_available') }}</option>
                                                            @foreach($services as $service)
                                                                <option value="{{ $service->id }}">{{ $service->name }}</option>
                                                            @endforeach
                                                        </select>
                                                    @else
                                                        <div style="color:green">{{ __('dashboard.calendar.no_services') }}</div>
                                                    @endif
                                                </div>
                                            </td>
                                    </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@section('style')
<link href="{{ asset('css/fullcalendar.css') }}" rel="stylesheet" type="text/css" />
<link href="{{ asset('css/fullcalendar.min.css') }}" rel="stylesheet" type="text/css" />
<style>
    table thead tr { 
        background: linear-gradient(135deg, #ba7321 0%, #b16e17ff 100%) !important ; 
        box-shadow : none !important; 
    }
    /* nested table (bookings per date) styling */
    .nested-table {
        border-radius: 8px;
        overflow: hidden;
        border: 1px solid #e2e8f0;
        background: #fff;
    }
    .nested-table thead tr {
        background: linear-gradient(135deg, #b37314 0%, #a85f13 100%) !important;
        color: #fff;
    }
    .nested-table th, .nested-table td {
        padding: 0.6rem 0.75rem;
        vertical-align: middle;
    }
    .available-services-select {
        border-radius: 8px;
        padding: 0.35rem 0.6rem;
    }
</style>
@endsection

@section('scripts')
<script>
    $(document).ready(function() {
        // Hover highlight for visible order rows
        $('table tbody').on('mouseenter', 'tr.order-row', function() {
            $(this).css({'background-color': '#e3f2fd', 'transition': 'background-color 0.15s'});
        }).on('mouseleave', 'tr.order-row', function() {
            $(this).css('background-color', '');
        });

        // Filtering logic
        function applyFilters() {
            var booking = $('#searchBooking').val().trim().toLowerCase();
            var name = $('#searchName').val().trim().toLowerCase();
            var phone = $('#searchPhone').val().trim();
            var email = $('#searchEmail').val().trim().toLowerCase();
            var date = $('#filterDate').val(); // YYYY-MM-DD

            $('.order-row').each(function() {
                var $tr = $(this);
                var tBooking = String($tr.data('booking') || '').toLowerCase();
                var tName = String($tr.data('name') || '').toLowerCase();
                var tPhone = String($tr.data('phone') || '');
                var tEmail = String($tr.data('email') || '').toLowerCase();
                var tDate = String($tr.data('date') || '');

                var match = true;
                if (booking && tBooking.indexOf(booking) === -1) match = false;
                if (name && tName.indexOf(name) === -1) match = false;
                if (phone && tPhone.indexOf(phone) === -1) match = false;
                if (email && tEmail.indexOf(email) === -1) match = false;
                if (date && tDate !== date) match = false; // exact date match

                if (match) {
                    $tr.show();
                    $tr.addClass('matches');
                } else {
                    $tr.hide();
                    $tr.removeClass('matches');
                }
            });

            // After toggling individual order rows, toggle the parent date-row
            // Use the 'matches' marker instead of :visible so ancestor-hidden rows
            // are counted correctly when clearing filters.
            $('.date-row').each(function() {
                var $dateRow = $(this);
                // any order-row that matched the filters inside this date group?
                var anyMatched = $dateRow.find('.order-row.matches').length > 0;
                if (anyMatched) {
                    $dateRow.show();
                } else {
                    $dateRow.hide();
                }
            });
        }

        $('#searchBooking,#searchName,#searchPhone,#searchEmail').on('keyup', applyFilters);
        $('#filterDate').on('change', applyFilters);
        $('#clearFilters').on('click', function(e) {
            e.preventDefault();
            $('#filterDate').val('');
            $('#searchBooking,#searchName,#searchPhone,#searchEmail').val('');
            applyFilters();
        });

        // When user selects an available service, redirect to orders.create with service_ids[0]=<id>
        $(document).on('change', '.available-services-select', function() {
            var serviceId = $(this).val();
            if (!serviceId) return;
            var date = $(this).data('date');
            var params = new URLSearchParams();
            params.append('service_ids[0]', serviceId);
            if (date) params.append('scheduledDate', date);
            var url = '{{ route('orders.create') }}' + '?' + params.toString();
            window.location.href = url;
        });

        // run once to ensure initial state
        applyFilters();
    });
</script>
@endsection
