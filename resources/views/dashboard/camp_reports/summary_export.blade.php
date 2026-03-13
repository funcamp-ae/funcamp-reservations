<!DOCTYPE html>
<html lang="{{ app()->getLocale() }}" dir="{{ app()->getLocale() === 'ar' ? 'rtl' : 'ltr' }}">
<head>
    <meta charset="UTF-8">
    <title>@lang('dashboard.camp_reports_summary')</title>
    <style>
        body { font-family: DejaVu Sans, sans-serif; font-size: 11px; color: #222; }
        h1 { text-align: center; margin-bottom: 14px; }
        table { width: 100%; border-collapse: collapse; }
        th, td { border: 1px solid #999; padding: 6px; text-align: center; vertical-align: middle; }
        th { background: #f3f3f3; }
        .photos img { width: 42px; height: 42px; object-fit: cover; margin: 2px; border: 1px solid #ccc; }
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
                <th>@lang('dashboard.item_name')</th>
                <th>@lang('dashboard.item_status')</th>
                <th>@lang('dashboard.photos')</th>
                <th>@lang('dashboard.damaged_count')</th>
                <th>@lang('dashboard.additional_notes')</th>
            </tr>
        </thead>
        <tbody>
            @forelse($rows as $index => $row)
                <tr>
                    <td>{{ $index + 1 }}</td>
                    <td>{{ $row->report_type_label }}</td>
                    <td>{{ $row->report_subtype ?: '-' }}</td>
                    <td>{{ $row->item_name }}</td>
                    <td>{{ $row->item_status_label }}</td>
                    <td class="photos">
                        @forelse(array_slice($row->photo_base64, 0, 3) as $photo)
                            <img src="{{ $photo }}" alt="photo">
                        @empty
                            -
                        @endforelse
                    </td>
                    <td>{{ $row->damaged_count ?? '-' }}</td>
                    <td class="notes">{{ $row->notes ?: '-' }}</td>
                </tr>
            @empty
                <tr>
                    <td colspan="8">@lang('dashboard.no_matching_records')</td>
                </tr>
            @endforelse
        </tbody>
    </table>
</body>
</html>