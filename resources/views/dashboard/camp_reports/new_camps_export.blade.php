@php
    $dir = app()->getLocale() === 'ar' ? 'rtl' : 'ltr';
@endphp
<!doctype html>
<html lang="{{ app()->getLocale() }}" dir="{{ $dir }}">
<head>
    <meta charset="utf-8">
    <style>
        body {
            font-family: DejaVu Sans, sans-serif;
            font-size: 12px;
            margin-bottom: 34px;
        }
        h2 { margin: 0 0 12px 0; }
        .meta { margin-bottom: 14px; }
        table { width: 100%; border-collapse: collapse; }
        th, td { border: 1px solid #444; padding: 6px; vertical-align: middle; }
        th { background: #f2f2f2; font-weight: 700; text-align: center; }
        td { text-align: center; }
        .text-start { text-align: left; }
        [dir="rtl"] .text-start { text-align: right; }
        .footer {
            position: fixed;
            left: 0;
            right: 0;
            bottom: 0;
            width: 100%;
            padding-top: 6px;
        }
        .footer-table td {
            border: 0;
            text-align: left;
            padding: 0;
            font-size: 10px;
            color: #666;
        }
        [dir="rtl"] .footer-table td { text-align: right; }
    </style>
</head>
<body>
    <h2>{{ __('dashboard.camp_reports_new_camps') }}</h2>

    @if(isset($plansForExport) && $plansForExport->isNotEmpty())
        @foreach($plansForExport as $plan)
            <div class="meta">
                <strong>{{ __('dashboard.new_camp_name') }}:</strong>
                <span>{{ $plan->camp_name ?: '-' }}</span>
                <br>
                <strong>{{ __('dashboard.date') }}:</strong>
                <span>{{ date('Y-m-d') }}</span>
            </div>

            <table>
                <thead>
                <tr >
                    <th style="width: 42px;">{{ __('dashboard.serial') }}</th>
                    <th>{{ __('dashboard.item_name') }}</th>
                    <th style="width: 140px;">{{ __('dashboard.itemStatus') }}</th>
                    <th style="width: 120px;">{{ __('dashboard.requested_qty') }}</th>
                    <th>{{ __('dashboard.additional_notes') }}</th>
                </tr>
                </thead>
                <tbody>
                @foreach($plan->items as $idx => $item)
                    <tr>
                        <td>{{ $idx + 1 }}</td>
                        <td class="text-center">{{ $item->item_name ?: '-' }}</td>
                        <td>
                            @if($item->sales_status === 'buy')
                                {{ __('dashboard.sales_status_buy') }}
                            @elseif($item->sales_status === 'available')
                                {{ __('dashboard.sales_status_available') }}
                            @elseif($item->sales_status === 'partial')
                                {{ __('dashboard.sales_status_partial') }}
                            @else
                                -
                            @endif
                        </td>
                        <td>{{ $item->requested_qty === null || $item->requested_qty === '' ? '-' : $item->requested_qty }}</td>
                        <td class="text-center">{{ $item->notes ?: '-' }}</td>
                    </tr>
                @endforeach
                </tbody>
            </table>

            @if(! $loop->last)
                <div style="height: 20px;"></div>
            @endif
        @endforeach
    @else
        <div class="meta">
            <strong>{{ __('dashboard.new_camp_name') }}:</strong>
            <span>{{ $campName ?: '-' }}</span>
            <br>
            <strong>{{ __('dashboard.date') }}:</strong>
            <span>{{ date('Y-m-d') }}</span>
        </div>

        <table>
            <thead>
            <tr style="text-align:center;">
                <th style="width: 42px;">{{ __('dashboard.serial') }}</th>
                <th>{{ __('dashboard.item_name') }}</th>
                <th style="width: 140px;">{{ __('dashboard.sales_status') }}</th>
                <th style="width: 120px;">{{ __('dashboard.requested_qty') }}</th>
                <th>{{ __('dashboard.additional_notes') }}</th>
            </tr>
            </thead>
            <tbody>
            @forelse($items as $idx => $item)
                <tr style="text-align:center;">
                    <td>{{ $idx + 1 }}</td>
                    <td class="text-center">{{ $item->item_name ?: '-' }}</td>
                    <td>
                        @if($item->sales_status === 'buy')
                            {{ __('dashboard.sales_status_buy') }}
                        @elseif($item->sales_status === 'available')
                            {{ __('dashboard.sales_status_available') }}
                        @elseif($item->sales_status === 'partial')
                            {{ __('dashboard.sales_status_partial') }}
                        @else
                            -
                        @endif
                    </td>
                    <td>{{ $item->requested_qty === null || $item->requested_qty === '' ? '-' : $item->requested_qty }}</td>
                    <td class="text-start">{{ $item->notes ?: '-' }}</td>
                </tr>
            @empty
                <tr>
                    <td colspan="5" style="text-align:center; padding: 18px;">
                        {{ __('dashboard.no_data_available') }}
                    </td>
                </tr>
            @endforelse
            </tbody>
        </table>
    @endif

    <div class="footer">
        <table class="footer-table">
            <tr>
                <td>
                    @lang('dashboard.generated_at'): {{ $generatedAt ?? '-' }} |
                    @lang('dashboard.user'): {{ $generatedByName ?? '-' }} (@lang('dashboard.id'): {{ $generatedById ?? '-' }})
                </td>
            </tr>
        </table>
    </div>
</body>
</html>

