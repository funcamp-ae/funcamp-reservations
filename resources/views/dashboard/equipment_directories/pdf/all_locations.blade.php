<!DOCTYPE html>
<html lang="{{ app()->getLocale() }}" dir="{{ app()->getLocale() == 'ar' ? 'rtl' : 'ltr' }}">
<head>
    <meta charset="utf-8">
    <title>@lang('dashboard.equipment_locations_statistics')</title>
    <style>
        body { font-family: XBRiyaz, Arial, sans-serif; }
        h2 { text-align: center; color: red; }
        p { text-align: center; font-size: 14px; font-weight: bold; }
        table { width: 100%; border-collapse: collapse; margin-top: 20px; margin-bottom: 80px; }
        th, td { border: 1px solid #000; padding: 10px; text-align: center; font-size: 14px; }
        th { font-weight: bold; background-color: #f2f2f2; }

        /* PDF footer: fixed at page bottom */
        .pdf-footer {
            position: fixed;
            bottom: 12px;
            left: 0;
            right: 0;
            width: 100%;
            text-align: center;
            font-size: 12px;
            color: #444;
            padding: 6px 12px;
        }
    </style>
</head>
<body>

<h2>@lang('dashboard.equipment_locations_statistics')</h2>

<table>
    <thead>
        <tr>
            <th>#</th>
            <th>@lang('dashboard.directory_name')</th>
            <th>@lang('dashboard.item_name')</th>
            <th>@lang('dashboard.item_contents')</th>
            <th>@lang('dashboard.location')</th>
            <th>@lang('dashboard.media')</th>
            <th>@lang('dashboard.quantity')</th>
            <th>@lang('dashboard.additional_notes')</th>
        </tr>
    </thead>
    <tbody>
        @foreach($items as $item)
            <tr>
                <td>{{ $loop->iteration }}</td>
                <td>{{ $item->directory->name ?? '-' }}</td>
                <td><a href="{{ route('equipment-directories.items.edit', [$item->directory_id, $item->id]) }}" class="text-decoration-none">{{ $item->name }}</a></td>
                <td>{{ $item->contents }}</td>
                <td>{{ $item->location }}</td>
                <td>
                    <div style="text-align: center;">
                        @forelse($item->media->where('file_type', 'image') as $media)
                            <a href="{{ $media->file_url ?? env('AWS_URL') . '/' . $media->file_path }}" target="_blank" rel="noopener noreferrer">
                                <img src="{{ $media->file_url ?? env('AWS_URL') . '/' . $media->file_path }}" alt="photo" style="width:40px;height:40px;object-fit:cover;border-radius:4px;border:1px solid #ddd; margin: 2px;">
                            </a>
                            <!--PDF_CHUNK_SPLIT-->
                        @empty
                            <span style="color: #6c757d; font-size: 12px;">-</span>
                        @endforelse
                    </div>
                </td>
                <td>{{ $item->quantity }}</td>
                <td>{{ $item->notes }}</td>
            </tr>
        @endforeach
    </tbody>
</table>

<div class="pdf-footer">
    @lang('dashboard.generated_at'): {{ $generatedAt ?? '-' }} |
    @lang('dashboard.user'): {{ $generatedByName ?? '-' }} (@lang('dashboard.id'): {{ $generatedById ?? '-' }})
</div>
</body>
</html>
