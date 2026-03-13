@extends('dashboard.layouts.app')
@section('pageTitle', __('dashboard.camp_reports_summary'))

@section('content')
    <div class="card card-flush">
        <div class="card-header align-items-center py-5 gap-2 gap-md-5">
            <div class="card-title">
                <h3 class="mb-0">@lang('dashboard.camp_reports_summary')</h3>
            </div>
            <div class="card-toolbar">
                @can('camp-reports.summary.export')
                    <a href="{{ route('camp-reports.summary.export', request()->query()) }}" class="btn btn-success">
                        <i class="bi bi-file-earmark-pdf"></i> @lang('dashboard.export_pdf')
                    </a>
                @endcan
            </div>
        </div>

        <div class="card-body">
            <form method="GET" action="{{ route('camp-reports.summary') }}" class="row g-4 mb-8">
                <div class="col-md-3">
                    <label class="form-label">@lang('dashboard.report_type')</label>
                    <select name="report_type" class="form-select">
                        <option value="">@lang('dashboard.all_title', ['page_title' => __('dashboard.report_type')])</option>
                        <option value="camp" {{ ($filters['report_type'] ?? '') === 'camp' ? 'selected' : '' }}>@lang('dashboard.camp')</option>
                        <option value="general" {{ ($filters['report_type'] ?? '') === 'general' ? 'selected' : '' }}>@lang('dashboard.general')</option>
                    </select>
                </div>
                <div class="col-md-3">
                    <label class="form-label">@lang('dashboard.report_subtype')</label>
                    <select name="report_subtype" class="form-select">
                        <option value="">@lang('dashboard.all_title', ['page_title' => __('dashboard.report_subtype')])</option>
                        @foreach ($reportSubtypes as $reportSubtype)
                            <option value="{{ $reportSubtype }}" {{ ($filters['report_subtype'] ?? '') === $reportSubtype ? 'selected' : '' }}>{{ $reportSubtype }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-3">
                    <label class="form-label">@lang('dashboard.item_status')</label>
                    <select name="item_status" class="form-select">
                        <option value="">@lang('dashboard.all_title', ['page_title' => __('dashboard.item_status')])</option>
                        @foreach ($itemStatuses as $statusValue => $statusLabel)
                            <option value="{{ $statusValue }}" {{ ($filters['item_status'] ?? '') === $statusValue ? 'selected' : '' }}>{{ __($statusLabel) }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-3">
                    <label class="form-label">@lang('dashboard.item_name')</label>
                    <input type="text" name="item_name" class="form-control" value="{{ $filters['item_name'] ?? '' }}" list="item-name-options">
                    <datalist id="item-name-options">
                        @foreach ($itemNames as $itemName)
                            <option value="{{ $itemName }}"></option>
                        @endforeach
                    </datalist>
                </div>
                <div class="col-12 d-flex gap-2 justify-content-end">
                    <a href="{{ route('camp-reports.summary') }}" class="btn btn-light">@lang('dashboard.reset')</a>
                    <button type="submit" class="btn btn-primary">@lang('dashboard.filter')</button>
                </div>
            </form>

            <div class="table-responsive">
                <table class="table align-middle table-row-dashed fs-6 gy-5">
                    <thead>
                        <tr class="text-center fw-bolder fs-6 text-uppercase gs-0" style="background-color: #f8f9fa; font-weight: 900 !important;">
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
                    <tbody>
                        @forelse($rows as $index => $row)
                        <tr class="text-center">
                            <td>{{ $index + 1 }}</td>
                            <td>{{ __('dashboard.' . $row->report_type) }}</td>
                            <td>{{ $row->report_subtype ?? '-' }}</td>
                            <td>
                                @if($row->report_id)
                                    <a href="{{ route('camp-reports.edit', $row->report_id) }}">{{ $row->item_name }}</a>
                                @else
                                    {{ $row->item_name }}
                                @endif
                            </td>
                            <td>{{ $row->item_status_label }}</td>
                            <td>
                                <div class="d-flex gap-2 justify-content-center flex-wrap">
                                    @forelse(array_slice($row->photo_urls, 0, 3) as $photoUrl)
                                        <a href="{{ $photoUrl }}" target="_blank" rel="noopener noreferrer">
                                            <img src="{{ $photoUrl }}" alt="photo" style="width:52px;height:52px;object-fit:cover;border-radius:6px;border:1px solid #ddd;">
                                        </a>
                                    @empty
                                        <span class="text-muted small">-</span>
                                    @endforelse
                                </div>
                            </td>
                            <td>{{ $row->damaged_count ?? '-' }}</td>
                            <td>{{ $row->notes ?: '-' }}</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8" class="text-center text-muted py-10">@lang('dashboard.no_matching_records')</td>
                        </tr>
                    @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
@endsection