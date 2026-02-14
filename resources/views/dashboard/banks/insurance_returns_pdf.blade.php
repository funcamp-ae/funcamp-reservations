<!DOCTYPE html>
<html lang="{{ app()->getLocale() }}" dir="{{ app()->getLocale() == 'ar' ? 'rtl' : 'ltr' }}">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ __('dashboard.insurance_returns') }}</title>
    <style>
        body {
            font-family: 'DejaVu Sans', Arial, sans-serif;
            direction: {{ app()->getLocale() == 'ar' ? 'rtl' : 'ltr' }};
            font-size: 12px;
        }
        .header {
            text-align: center;
            margin-bottom: 30px;
            border-bottom: 2px solid #333;
            padding-bottom: 15px;
        }
        .header h1 {
            margin: 0;
            padding: 0;
            font-size: 24px;
            color: #333;
        }
        .header p {
            margin: 5px 0;
            color: #666;
        }
        .summary {
            margin-bottom: 20px;
            background-color: #f5f5f5;
            padding: 15px;
            border-radius: 5px;
        }
        .summary-row {
            display: flex;
            justify-content: space-between;
            margin-bottom: 10px;
        }
        .summary-label {
            font-weight: bold;
            color: #333;
        }
        .summary-value {
            color: #0066cc;
            font-weight: bold;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
        }
        th {
            background-color: #0066cc;
            color: white;
            padding: 10px;
            text-align: center;
            font-weight: bold;
            border: 1px solid #0066cc;
        }
        td {
            padding: 8px;
            border: 1px solid #ddd;
            text-align: center;
        }
        tr:nth-child(even) {
            background-color: #f9f9f9;
        }
        .badge {
            padding: 4px 8px;
            border-radius: 3px;
            font-size: 10px;
            font-weight: bold;
        }
        .badge-success {
            background-color: #28a745;
            color: white;
        }
        .badge-danger {
            background-color: #dc3545;
            color: white;
        }
        .badge-warning {
            background-color: #ffc107;
            color: #000;
        }
        .footer {
            margin-top: 30px;
            text-align: center;
            font-size: 10px;
            color: #666;
            border-top: 1px solid #ddd;
            padding-top: 10px;
        }
    </style>
</head>
<body>
    <div class="header">
        <h1>{{ __('dashboard.insurance_returns') }}</h1>
        <p>{{ __('dashboard.date') }}: {{ now()->format('Y-m-d H:i') }}</p>
        @if($filters)
            <p style="font-size: 10px;">
                @if(isset($filters['customer_name']))
                    {{ __('dashboard.customer') }}: {{ $filters['customer_name'] }} |
                @endif
                @if(isset($filters['order_id']))
                    {{ __('dashboard.order_id') }}: {{ $filters['order_id'] }} |
                @endif
                @if(isset($filters['insurance_status']))
                    {{ __('dashboard.insurance_status') }}: {{ $filters['insurance_status'] }} |
                @endif
                @if(isset($filters['date_from']) && isset($filters['date_to']))
                    {{ __('dashboard.date_from') }}: {{ $filters['date_from'] }} - {{ __('dashboard.date_to') }}: {{ $filters['date_to'] }}
                @endif
            </p>
        @endif
    </div>

    <div class="summary">
        <div class="summary-row">
            <span class="summary-label">{{ __('dashboard.total_insurance_paid') }}:</span>
            <span class="summary-value">{{ number_format($totals['total_insurance_paid'], 2) }} {{ __('dashboard.' . config('app.currency')) }}</span>
        </div>
        <div class="summary-row">
            <span class="summary-label">{{ __('dashboard.total_deducted') }}:</span>
            <span class="summary-value">{{ number_format($totals['total_deducted'], 2) }} {{ __('dashboard.' . config('app.currency')) }}</span>
        </div>
        <div class="summary-row">
            <span class="summary-label">{{ __('dashboard.total_returned') }}:</span>
            <span class="summary-value">{{ number_format($totals['total_returned'], 2) }} {{ __('dashboard.' . config('app.currency')) }}</span>
        </div>
        <div class="summary-row">
            <span class="summary-label">{{ __('dashboard.total_records') }}:</span>
            <span class="summary-value">{{ $payments->count() }}</span>
        </div>
    </div>

    <table>
        <thead>
            <tr>
                <th>{{ __('dashboard.booking_number') }}</th>
                <th>{{ __('dashboard.date') }}</th>
                <th>{{ __('dashboard.customer_name') }}</th>
                <th>{{ __('dashboard.insurance_amount_paid') }}</th>
                <th>{{ __('dashboard.payment_method') }}</th>
                <th>{{ __('dashboard.deducted_amount') }}</th>
                <th>{{ __('dashboard.return_amount') }}</th>
                <th>{{ __('dashboard.insurance_status') }}</th>
            </tr>
        </thead>
        <tbody>
            @foreach($payments as $payment)
                @php
                    if($payment->order && $payment->order->insurance_status) {
                        $deductedAmount = $payment->transaction ? $payment->transaction->amount : 0;
                        $returnAmount = $payment->price - $deductedAmount;
                    } else {
                        $deductedAmount = 0;
                        $returnAmount = 0;
                    }
                    
                    $insuranceStatus = '';
                    $badgeClass = '';
                    if ($payment->order) {
                        switch ($payment->order->insurance_status) {
                            case 'returned':
                                $insuranceStatus = __('dashboard.insurance_returned');
                                $badgeClass = 'badge-success';
                                break;
                            case 'confiscated_full':
                                $insuranceStatus = __('dashboard.insurance_confiscated_full');
                                $badgeClass = 'badge-danger';
                                break;
                            case 'confiscated_partial':
                                $insuranceStatus = __('dashboard.insurance_confiscated_partial');
                                $badgeClass = 'badge-warning';
                                break;
                            default:
                                $insuranceStatus = __('dashboard.insurance_not_returned');
                                $badgeClass = 'badge-danger';
                                break;
                        }
                    }
                @endphp
                <tr>
                    <td>{{ $payment->order ? '#' . $payment->order->id : 'N/A' }}</td>
                    <td>{{ $payment->created_at ? $payment->created_at->format('Y-m-d') : 'N/A' }}</td>
                    <td>{{ $payment->order && $payment->order->customer ? $payment->order->customer->name : 'N/A' }}</td>
                    <td>{{ number_format($payment->price, 2) }}</td>
                    <td>{{ $payment->payment_method ? __('dashboard.' . $payment->payment_method) : 'N/A' }}</td>
                    <td>{{ number_format($deductedAmount, 2) }}</td>
                    <td>{{ number_format($returnAmount, 2) }}</td>
                    <td><span class="badge {{ $badgeClass }}">{{ $insuranceStatus }}</span></td>
                </tr>
            @endforeach
        </tbody>
    </table>

    <div class="footer">
        <p>{{ now()->format('Y-m-d H:i:s') }}</p>
        <p>{{ config('app.name') }}</p>
    </div>
</body>
</html>
