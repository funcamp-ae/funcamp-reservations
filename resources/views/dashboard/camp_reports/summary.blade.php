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
                    <button
                        type="button"
                        class="btn btn-success"
                        id="prepare-summary-export-btn"
                        data-prepare-url="{{ route('camp-reports.summary.prepare') }}"
                        data-export-url="{{ route('camp-reports.summary.export') }}"
                        data-default-label="@lang('dashboard.export_pdf')"
                        data-loading-label="Preparing data..."
                    >
                        <i class="bi bi-file-earmark-pdf"></i>
                        <span>@lang('dashboard.export_pdf')</span>
                    </button>
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
                    <label class="form-label">@lang('dashboard.itemName')</label>
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

@push('js')
<script>
    (function () {
        const button = document.getElementById('prepare-summary-export-btn');
        if (!button) {
            return;
        }

        const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '';
        const originalLabel = button.dataset.defaultLabel || 'Export PDF';
        const loadingLabel = button.dataset.loadingLabel || 'Preparing data...';

        button.addEventListener('click', async function () {
            if (button.disabled) {
                return;
            }

            button.disabled = true;
            button.querySelector('span').textContent = loadingLabel;

            const params = new URLSearchParams(window.location.search);

            try {
                const response = await fetch(button.dataset.prepareUrl, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/x-www-form-urlencoded; charset=UTF-8',
                        'Accept': 'application/json',
                        'X-CSRF-TOKEN': csrfToken,
                        'X-Requested-With': 'XMLHttpRequest'
                    },
                    body: params.toString()
                });

                const payload = await response.json().catch(function () {
                    return {};
                });

                if (!response.ok || !payload.success) {
                    throw new Error(payload.message || 'Failed to prepare export data.');
                }

                if (payload.export_url) {
                    window.location.href = payload.export_url;
                    return;
                }

                if (payload.token) {
                    const exportUrl = new URL(button.dataset.exportUrl, window.location.origin);
                    exportUrl.searchParams.set('token', payload.token);
                    window.location.href = exportUrl.toString();
                    return;
                }

                throw new Error('Missing export token.');
            } catch (error) {
                alert(error.message || 'Failed to prepare export data.');
                button.disabled = false;
                button.querySelector('span').textContent = originalLabel;
            }
        });
    })();
</script>
@endpush