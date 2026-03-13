<!DOCTYPE html>
<html dir="{{ app()->getLocale() === 'ar' ? 'rtl' : 'ltr' }}">
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
    <title>@lang('dashboard.camp_reports') @lang('dashboard.export')</title>
    <style>
        body {
            font-family: DejaVu Sans, sans-serif;
            font-size: 12px;
            direction: {{ app()->getLocale() === 'ar' ? 'rtl' : 'ltr' }};
        }
        .header {
            text-align: center;
            margin-bottom: 20px;
            border-bottom: 2px solid #333;
            padding-bottom: 10px;
        }
        .report-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 20px;
        }
        .report-table th, .report-table td {
            border: 1px solid #ddd;
            padding: 8px;
            text-align: {{ app()->getLocale() === 'ar' ? 'right' : 'left' }};
        }
        .report-table th {
            background-color: #f2f2f2;
            font-weight: bold;
        }
        .item-table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 10px;
        }
        .item-table th, .item-table td {
            border: 1px solid #ddd;
            padding: 5px;
            font-size: 11px;
            text-align: {{ app()->getLocale() === 'ar' ? 'right' : 'left' }};
        }
        .item-table th {
            background-color: #f8f8f8;
        }
        .item-image {
            max-width: 150px;
            max-height: 100px;
            width: auto;
            height: auto;
            border: 1px solid #ddd;
            margin: 5px 0;
        }
        .attachment-info {
            font-size: 10px;
            color: #666;
            margin: 2px 0;
        }
        .page-break {
            page-break-after: always;
        }
        .text-center {
            text-align: center;
        }
        .text-right {
            text-align: right;
        }
        .mb-3 {
            margin-bottom: 15px;
        }
        .mt-3 {
            margin-top: 15px;
        }
        .attachments-cell {
            vertical-align: top;
        }
    </style>
</head>
<body>
<div class="header">
    <h1>@lang('dashboard.camp_reports') - {{ date('Y-m-d') }}</h1>
</div>

@foreach($reports as $report)
    <div class="report mb-3">
        <table class="report-table">
            <tr>
                <th width="20%">@lang('dashboard.report_date')</th>
                <td width="30%">{{ $report->report_date->format('Y-m-d') }}</td>
                <th width="20%">@lang('dashboard.report_type')</th>
                <td width="30%">{{ __('dashboard.' . $report->report_type) }}</td>
            </tr>
            <tr>
                <th>@lang('dashboard.report_subtype')</th>
                <td>{{ $report->report_type === 'camp' ? ($report->service->name ?? '-') : ($report->custom_report_type ?? '-') }}</td>
                <th>@lang('dashboard.camp_name')</th>
                <td>{{ $report->camp_name ?? '-' }}</td>
            </tr>
            <tr>
                <th>@lang('dashboard.created_by')</th>
                <td>{{ $report->creator->name }}</td>
                <th>@lang('dashboard.general_notes')</th>
                <td>{{ $report->general_notes ?? '-' }}</td>
            </tr>
        </table>

        @if($report->items->count() > 0)
            <h3>@lang('dashboard.report_items')</h3>
            <table class="item-table">
                <thead>
                <tr>
                    <th width="20%">@lang('dashboard.item_name')</th>
                    <th width="15%">@lang('dashboard.item_status')</th>
                    <th width="10%">@lang('dashboard.damaged_count')</th>
                    <th width="30%">@lang('dashboard.notes')</th>
                    <th width="25%">@lang('dashboard.attachments')</th>
                </tr>
                </thead>
                <tbody>
                @foreach($report->items as $item)
                    @php
                        $statusKey = \App\Helpers\CampReportHelper::itemStatuses()[$item->item_status] ?? null;
                        $itemStatusText = $statusKey ? __($statusKey) : ($item->item_status ?: '-');
                        $hasAttachments = $item->media->count() > 0;
                    @endphp
                    <tr>
                        <td>{{ $item->item_name }}</td>
                        <td>{{ $itemStatusText }}</td>
                        <td>{{ $item->damaged_count ?? '-' }}</td>
                        <td>{{ $item->notes ?? '-' }}</td>
                        <td class="attachments-cell">
                            {{-- Display Photo --}}
                            @if(isset($item->photo_base64))
                                <div>
                                    <img src="data:image/jpeg;base64,{{ $item->photo_base64 }}"
                                         class="item-image" alt="@lang('dashboard.item_photo')">
                                    <div class="attachment-info">@lang('dashboard.photo')</div>
                                </div>
                            @elseif($hasAttachments)
                                <div class="attachment-info">@lang('dashboard.attachments_available_in_system')</div>
                            @else
                                <div class="attachment-info">@lang('dashboard.no_attachments')</div>
                            @endif
                        </td>
                    </tr>
                @endforeach
                </tbody>
            </table>
        @else
            <p>@lang('dashboard.no_items_found')</p>
        @endif
    </div>

    @if(!$loop->last)
        <hr>
    @endif
@endforeach
</body>
</html>
