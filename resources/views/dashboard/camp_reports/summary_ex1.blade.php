<!DOCTYPE html>
<html lang="{{ app()->getLocale() }}" dir="{{ app()->getLocale() === 'ar' ? 'rtl' : 'ltr' }}">
<head>
    <meta charset="UTF-8">
    <title>@lang('dashboard.camp_reports_summary')</title>
    <style>
        body { font-family: DejaVu Sans, sans-serif; font-size: 10px; color: #222; margin: 0; }
        h1 { text-align: center; margin: 0 0 8px 0; font-size: 14px; }
        table { width: 100%; border-collapse: collapse; table-layout: fixed; }
        th, td { border: 1px solid #999; padding: 4px; text-align: center; vertical-align: middle; word-wrap: break-word; }
        th { background: #f3f3f3; font-size: 9px; }
        td { line-height: 1.25; }
        .photos { white-space: nowrap; }
        .photo-thumb { width: 32px; height: 32px; margin: 1px; border: 1px solid #ccc; display: inline-block; }
        .notes { text-align: right; }
    </style>
</head>
<body>
    <h1>@lang('dashboard.camp_reports_summary')</h1>
        <table>
        <thead>
            <tr>
                <th>#</th>
                <th>@lang('dashboard.report_type')</th>
                <th>@lang('dashboard.report_subtype')</th>
                <th>@lang('dashboard.itemName')</th>
                <th>@lang('dashboard.item_status')</th>
                <th>@lang('dashboard.photos')</th>
                <th>@lang('dashboard.damaged_count')</th>
                <th>@lang('dashboard.additional_notes')</th>
            </tr>
        </thead>
    </table>
</body>
</html>