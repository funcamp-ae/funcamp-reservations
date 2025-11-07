<!DOCTYPE html>
<html dir="@lang('dashboard.direction')" lang="{{ app()->getLocale() }}">
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
    <title>@lang('dashboard.invoice')</title>
    <style>
        body {
            font-family: 'Cairo', sans-serif;
            color: #333;
            line-height: 1.6;
            margin: 0;
            padding: 0;
            background-color: #f9f9f9;
        }

        .container {
            width: 100%;
            margin: 0 auto;
            background-color: #fff;
            box-shadow: 0 0 5px rgba(0,0,0,0.1);
        }

        .header {
            display: block;
            padding: 20px;
            background: linear-gradient(135deg, #B98220 0%, #6A3D1C 100%);
            color: white;
            border-radius: 10px;
            margin-bottom: 20px;
            overflow: hidden;
        }

        .company-info {
            text-align: left;
            float: left;
            width: 40%;
        }

        .logo {
            text-align: center;
            float: left;
            width: 20%;
        }

        .logo img {
            max-height: 80px;
        }

        .show_price_info {
            text-align: right;
            float: right;
            width: 30%;
        }

        .section {
            padding-left: 20px;
            padding-right: 20px;
            border-radius: 8px;
            background-color: #f8f9fa;
            border-left: 5px solid #65391B;
        }

        .section-title {
            color: #65391B;
            margin-bottom: 5px;
            font-size: 18px;
            font-weight: bold;
            border-bottom: 2px solid #eee;
            padding-bottom: 8px;
        }

        /* Table Styles */
        .details-table {
            width: 100%;
            border-collapse: collapse;
            margin: 5px 0;
        }

        .details-table th {
            background-color: #65391B;
            color: white;
            padding: 5px;
            text-align: center;
            border: 1px solid #65391B;
        }

        .details-table td {
            padding: 10px;
            text-align: center;
            border: 1px solid #ddd;
            background-color: white;
        }

        .details-table tr:nth-child(even) td {
            background-color: #f2f2f2;
        }

        .footer {
            position: relative;
            text-align: center;
            display: block;
            overflow: hidden;
        }

        .thank-you {
            font-size: 18px;
            color: #fff;
            font-weight: bold;
            text-align: center;
            padding: 5px 15px;
            background: linear-gradient(135deg, #B98220 0%, #6A3D1C 100%);
            border-radius: 8px;
        }

        .social-icons {
            position: absolute;
            top: 50%;
            transform: translateY(-50%);
            width: 30%;
        }

        .left-social {
            left: 0;
            text-align: left;
        }

        .right-social {
            right: 0;
            text-align: right;
        }

        .social-item {
            display: inline-block;
            vertical-align: middle;
        }

        .social-icon {
            width: 30px;
            height: 30px;
            vertical-align: middle;
            margin: 0 10px;
        }

        .social-number {
            font-size: 14px;
            vertical-align: middle;
            color: #333;
        }

        .seal {
            width: 80px;
            height: 80px;
        }

        .middle-section {
            width: 40%;
            margin: 0 auto;
            text-align: center;
        }

        .export-date {
            text-align: center;
            color: #777;
        }

        .seal-container {
            text-align: center;
        }

        .seal {
            width: 80px;
            height: 80px;
        }

        /* Clearfix for floated elements */
        .clearfix:after {
            content: "";
            display: table;
            clear: both;
        }

        .footer-row {
            display: flex;
            justify-content: space-between;
            align-items: center;
            width: 100%;
        }

        .footer-item {
            display: flex;
            align-items: center;
            flex: 1;
        }

        .footer-item:first-child {
            justify-content: flex-start;
        }

        .footer-item:nth-child(2) {
            justify-content: center;
            flex-direction: column;
        }

        .footer-item:last-child {
            justify-content: flex-end;
        }

        .social-icon {
            width: 30px;
            height: 30px;
            margin: 0 10px;
        }

        .social-number {
            font-size: 14px;
            color: #333;
        }

        .export-date {
            text-align: center;
            color: #392d2d;
        }

        .seal-container {
            text-align: center;
        }

        .seal {
            width: 120px;
            height: auto
        }

        /* RTL/LTR direction support */
        .rtl-text {
            direction: rtl;
            text-align: right;
        }

        .ltr-text {
            direction: ltr;
            text-align: left;
        }
    </style>
</head>
<body>
<div class="container">
    <!-- Header Section -->
    <div class="header clearfix">
        <div class="company-info">
            <div>{{ $termsSittng->company_name }}</div>
            <div>U.A.E Dubai</div>
            <div>0566674766</div>
            <div>www.funcamp.ae</div>
        </div>

        <div class="logo">
            <img class="seal" src="{{ public_path('imgs/funcamp_remove.png') }}" alt="Funcamp Logo">
            <P style="font-size: 20px;margin: 0"><b>@lang('dashboard.invoice') / {{ trans_get('dashboard.invoice', [], 'opposite_locale') }}</b></P>
        </div>

        <div class="show_price_info">
            <strong> @lang('dashboard.invoice') / {{ trans_get('dashboard.invoice', [], 'opposite_locale') }} @lang('dashboard.order_number') / {{ trans_get('dashboard.order_number', [], 'opposite_locale') }}:</strong><br>
            {{ 'INV-' . $order->order_number }}<br>
            <strong>@lang('dashboard.Receipt_Date') / {{ trans_get('dashboard.Receipt_Date', [], 'opposite_locale') }}:</strong><br>
            {{ now()->format('d-m-Y H:i') }}
        </div>
    </div>

    <!-- Reservation Details -->
    <div class="section @if(app()->getLocale() == 'en') ltr-text @else rtl-text @endif">
        <!-- Table for reservation details -->
        <table class="details-table">
            <thead>
            <tr>
                <th>@lang('dashboard.reservation_number') / {{ trans_get('dashboard.reservation_number', [], 'opposite_locale') }}</th>
                <th>@lang('dashboard.order_date') / {{ trans_get('dashboard.order_date', [], 'opposite_locale') }}</th>
                <th>@lang('dashboard.Customer_Name') / {{ trans_get('dashboard.Customer_Name', [], 'opposite_locale') }}</th>
                <th>@lang('dashboard.phone') / {{ trans_get('dashboard.phone', [], 'opposite_locale') }}</th>
                <th>@lang('dashboard.email') / {{ trans_get('dashboard.email', [], 'opposite_locale') }}</th>
            </tr>
            </thead>
            <tbody>
            <tr>
                <td>{{ $order->id }}</td>
                <td>{{ $order->date ? \Carbon\Carbon::parse($order->date)->format('Y-m-d') : __('dashboard.not_available') }}</td>
                <td>{{ $order->customer->name ?? __('dashboard.not_available') }}</td>
                <td>{{ $order->customer->phone ?? __('dashboard.not_available') }}</td>
                <td>{{ $order->customer->email ?? __('dashboard.not_available') }}</td>
            </tr>
            </tbody>
        </table>
    </div>
    @php $totalPrice = 0 @endphp
    @if(!empty($order->services))
        <div class="section @if(app()->getLocale() == 'en') ltr-text @else rtl-text @endif">
            <!-- Table for reservation details -->
            <table class="details-table">
                <thead>
                <tr>
                    <th>@lang('dashboard.serial') / {{ trans_get('dashboard.serial', [], 'opposite_locale') }}</th>
                    <th>@lang('dashboard.service') / {{ trans_get('dashboard.service', [], 'opposite_locale') }}</th>
                    <th>@lang('dashboard.count') / {{ trans_get('dashboard.count', [], 'opposite_locale') }}</th>
                    <th>@lang('dashboard.Rate') / {{ trans_get('dashboard.Rate', [], 'opposite_locale') }} / @lang('dashboard.RS') / {{ trans_get('dashboard.RS', [], 'opposite_locale') }}</th>
                </tr>
                </thead>
                <tbody>
                @php $totalPrice += $order->price; @endphp
                @foreach ($order->services as $index => $service)
                    <tr>
                        <td>{{ $index + 1 }}</td>
                        <td>{{ $service->name }}</td>
                        <td>1</td>
                        <td>{{ $order->price }} @lang('dashboard.RS')</td>
                    </tr>
                @endforeach
                </tbody>
            </table>
        </div>
    @endif
    @if($order->addons->isNotEmpty())
        <div class="section @if(app()->getLocale() == 'en') ltr-text @else rtl-text @endif">
            <!-- Table for reservation details -->
            <table class="details-table">
                <thead>
                <tr>
                    <th>@lang('dashboard.serial') / {{ trans_get('dashboard.serial', [], 'opposite_locale') }}</th>
                    <th>@lang('dashboard.addons') / {{ trans_get('dashboard.addons', [], 'opposite_locale') }}</th>
                    <th>@lang('dashboard.Rate') / {{ trans_get('dashboard.Rate', [], 'opposite_locale') }}</th>
                    <th>@lang('dashboard.count') / {{ trans_get('dashboard.count', [], 'opposite_locale') }}</th>
                    <th>@lang('dashboard.Total') / {{ trans_get('dashboard.Total', [], 'opposite_locale') }} / @lang('dashboard.RS') / {{ trans_get('dashboard.RS', [], 'opposite_locale') }}</th>
                </tr>
                </thead>
                <tbody>
                @foreach ($order->addons as $index => $addon)
                        <tr>
                            <td>{{ $index + 1 }}</td>
                            <td>{{ $addon->name }}</td>
                            <td>{{ $addon->pivot->price }}</td>
                            <td>{{ $addon->pivot->count }}</td>
                            <td>{{ $addon->pivot->price * $addon->pivot->count }} @lang('dashboard.RS')</td>
                        </tr>
                        @php $totalPrice += $addon->pivot->price * $addon->pivot->count; @endphp
                @endforeach
                </tbody>
            </table>
        </div>
    @endif
    @if(!empty($order->deposit) || !empty($order->insurance_amount))
        <div class="section @if(app()->getLocale() == 'en') ltr-text @else rtl-text @endif">
            <!-- Table for reservation details -->
            <table class="details-table">
                <thead>
                <tr>
                    @if(!empty($order->deposit))
                        <th>@lang('dashboard.deposit') / {{ trans_get('dashboard.deposit', [], 'opposite_locale') }}</th>
                    @endif
                    @if(!empty($order->insurance_amount))
                        <th>@lang('dashboard.insurance') / {{ trans_get('dashboard.insurance', [], 'opposite_locale') }}</th>
                    @endif
                </tr>
                </thead>
                <tbody>
                <tr>
                    @if(!empty($order->deposit))
                        <td>{{ $order->deposit }}</td>
                    @endif
                    @if(!empty($order->insurance_amount))
                        <td>{{ $order->insurance_amount }}</td>
                        @php $totalPrice += $order->insurance_amount @endphp
                    @endif
                </tr>
                </tbody>
            </table>
        </div>
    @endif
    <div class="section @if(app()->getLocale() == 'en') ltr-text @else rtl-text @endif">
        <!-- Table for reservation details -->
        <table class="details-table">
            <thead>
            <tr>
                <th>@lang('dashboard.total_amount') / {{ trans_get('dashboard.total_amount', [], 'opposite_locale') }}</th>
                <th>@lang('dashboard.Amount_Received') / {{ trans_get('dashboard.Amount_Received', [], 'opposite_locale') }}</th>
                <th>@lang('dashboard.Balance_Due') / {{ trans_get('dashboard.Balance_Due', [], 'opposite_locale') }}</th>
            </tr>
            </thead>
            <tbody>
            <tr>
                <td>{{ $totalPrice }} @lang('dashboard.RS') </td>
                @php $totalPaid = 0; @endphp
                @if(!empty($order->payments))
                    @foreach($order->payments as $payment)
                            @php $totalPaid += $payment->price @endphp
                    @endforeach
                @endif
                <td>{{ $totalPaid }} @lang('dashboard.RS') </td>
                <td>{{ $totalPrice - $totalPaid }} @lang('dashboard.RS') </td>
            </tr>
            </tbody>
        </table>
    </div>
    <div class="section @if(app()->getLocale() == 'en') ltr-text @else rtl-text @endif">
        <div class="section-title">@lang('dashboard.notes') / {{ trans_get('dashboard.notes', [], 'opposite_locale') }}</div>
        <ul>
            <li> @lang('dashboard.payment_remaining') </li>
            <li> {{ trans_get('dashboard.payment_remaining', [], 'opposite_locale') }}</li>
            <li> @lang('dashboard.security_deposit') </li>
            <li> {{ trans_get('dashboard.security_deposit', [], 'opposite_locale') }}</li>
            <li> @lang('dashboard.terms1') </li>
            <li> {{ trans_get('dashboard.terms1', [], 'opposite_locale') }}</li>
        </ul>
    </div>

    <!-- Additional Notes -->
    @if(!empty($order->invoice_notes))
        <div class="section @if(app()->getLocale() == 'en') ltr-text @else rtl-text @endif">
            <div class="section-title">@lang('dashboard.additional_notes') / {{ trans_get('dashboard.additional_notes', [], 'opposite_locale') }}</div>
            <div>{!! $order->invoice_notes !!}</div>
        </div>
    @endif

    <!-- Thank You Message -->
    <div class="thank-you">
        <span style="display: block;">@lang('dashboard.thanks')</span>
        <span style="display: block;">{{ trans_get('dashboard.thanks', [], 'opposite_locale') }}</span>
    </div>

    <!-- Footer -->
    <div class="footer" style="display: block; padding: 20px; border-radius: 10px; margin-top: 30px; overflow: hidden;">
        <div style="text-align: left; float: left; width: 30%;">
            <span style="font-size: 14px; vertical-align: middle; display: inline-block;">
                0566674766
            </span>
            <img src="{{ public_path('imgs/whatsapp.png') }}"
                 alt="WhatsApp"
                 style="width: 22px; height: 22px; vertical-align: middle; margin-right: 4px;">
        </div>


        <div style="text-align: center; float: left; width: 40%;">
            <div>
                <img src="{{ public_path('imgs/funcamp_seal.jpg') }}" alt="Company Seal" style="width: 120px; height: auto;">
            </div>
            <div style="margin-bottom: 10px;">
                {{ now()->format('Y-m-d H:i') }}
            </div>
        </div>

        <div style="text-align: right; float: right; width: 30%;">
            <span style="font-size: 14px; vertical-align: middle; display: inline-block;">
                funcamp.ae@
            </span>
            <img src="{{ public_path('imgs/instagram.png') }}"
                 alt="Instagram"
                 style="width: 22px; height: 22px; vertical-align: middle;">
        </div>
    </div>
</div>
</body>
</html>
