@extends('dashboard.layouts.app')
@section('pageTitle', __('dashboard.create_camp_reports_new_camps'))

@section('content')
    <div class="card card-flush">
        <div class="card-header align-items-center py-5 gap-2 gap-md-5">
            <h3 class="mb-0">@lang('dashboard.create_camp_reports_new_camps')</h3>
            <button type="submit"
                    class="btn btn-success"
                    form="new-camps-form"
                    formmethod="GET"
                    formtarget="_blank"
                    formaction="{{ route('camp-reports.new-camps.export') }}">
                <i class="bi bi-file-earmark-pdf"></i> @lang('dashboard.export_pdf')
            </button>
    </div>

        <div class="card-body pt-0">
            <form method="POST" action="{{ route('camp-reports.new-camps.store') }}" class="mb-0" id="new-camps-form">
                @csrf
                <input type="hidden" name="editing_plan_id" value="{{ old('editing_plan_id', $editingPlan->id ?? '') }}">

                    @if (isset($editingPlan))
                        <a href="{{ route('camp-reports.new-camps') }}" class="btn btn-light-danger">
                            @lang('dashboard.cancel')
                        </a>
                    @endif

                <div class="row g-4 mb-4">
                        <label class="form-label">@lang('dashboard.new_camp_name')</label>
                        <div class="d-flex gap-2 justify-content-between">
                            <input type="text"
                                class="form-control"
                                name="new_camp_name"
                                value="{{ old('new_camp_name', $editingPlan->new_camp_name ?? '') }}"
                                placeholder="@lang('dashboard.new_camp_name')" required>
                                <button type="button" class="btn btn-light-primary text-nowrap d-inline-flex align-items-center" id="add-row-btn">
                                    <i class="bi bi-plus-circle"></i> @lang('dashboard.add_row')
                                </button>
                        </div>
                </div>


                <div class="table-responsive">
                    <table class="table align-middle table-row-dashed fs-6 gy-5" id="new-camps-table">
                        <thead>
                        <tr class="text-center fw-bolder fs-6 text-uppercase gs-0"
                            style="background-color: #f8f9fa; font-weight: 900 !important;">
                            <th style="width: 60px;">@lang('dashboard.serial')</th>
                            <th class="fw-bolder">@lang('dashboard.item_name')</th>
                            <th class="fw-bolder">@lang('dashboard.item_status')</th>
                            <th class="fw-bolder">@lang('dashboard.requested_qty')</th>
                            <th class="fw-bolder">@lang('dashboard.additional_notes')</th>
                        </tr>
                        </thead>
                        <tbody>
                        @php
                            $rows = old('items', $editingPlan->items ?? []);
                            $rows = is_array($rows) ? $rows : [];
                            $rowsCount = max(1, count($rows));
                        @endphp

                        @for ($i = 1; $i <= $rowsCount; $i++)
                            @php
                                $row = $rows[$i] ?? $rows[$i - 1] ?? [];
                            @endphp
                            <tr class="text-center">
                                <td class="fw-bold">{{ $i }}</td>
                                <td>
                                    <input type="text" class="form-control" name="items[{{ $i }}][item_name]" value="{{ $row['item_name'] ?? '' }}" required>
                                </td>
                                <td>
                                    <select class="form-select" name="items[{{ $i }}][sales_status]" required>
                                        <option value="">—</option>
                                        <option value="buy" {{ ($row['sales_status'] ?? '') === 'buy' ? 'selected' : '' }}>@lang('dashboard.sales_status_buy')</option>
                                        <option value="available" {{ ($row['sales_status'] ?? '') === 'available' ? 'selected' : '' }}>@lang('dashboard.sales_status_available')</option>
                                        <option value="partial" {{ ($row['sales_status'] ?? '') === 'partial' ? 'selected' : '' }}>@lang('dashboard.sales_status_partial')</option>
                                    </select>
                                </td>
                                <td>
                                    <input type="number" min="0" step="1" class="form-control" name="items[{{ $i }}][requested_qty]" value="{{ $row['requested_qty'] ?? '' }}"  required>
                                </td>
                                <td>
                                    <input type="text" class="form-control" name="items[{{ $i }}][notes]" value="{{ $row['notes'] ?? '' }}" required>
                                </td>
                            </tr>
                        @endfor
                        </tbody>
                    </table>
                </div>
                <div class="d-flex justify-content-end mt-4">
                    <button type="submit" class="btn btn-primary text-nowrap d-inline-flex align-items-center px-4">
                        <i class="bi bi-check2-circle me-2"></i> @lang('dashboard.save_changes')
                    </button>
                </div>
            </form>

            <div class="separator my-8"></div>

            <h4 class="mb-4">@lang('dashboard.camp_reports_new_camps')</h4>
            @php
                $nonEmptyPlans = collect($plans ?? [])->filter(function ($createdPlan) {
                    $items = is_array($createdPlan->items ?? null) ? $createdPlan->items : [];
                    return count($items) > 0;
                });
            @endphp

            @forelse($nonEmptyPlans as $index => $createdPlan)
                @php
                    $createdItems = is_array($createdPlan->items ?? null) ? $createdPlan->items : [];
                @endphp
                <div class="mb-8 p-4 border rounded">
                    <div class="d-flex flex-wrap justify-content-between align-items-center mb-3 gap-2">
                        <div>
                            <h5 class="mb-1">{{ $createdPlan->new_camp_name ?: '-' }}</h5>
                            <span class="text-muted">{{ optional($createdPlan->created_at)->format('Y-m-d H:i') }}</span>
                        </div>
                        <div class="d-flex gap-2">
                            <a href="{{ route('camp-reports.new-camps', ['edit_plan' => $createdPlan->id]) }}#new-camps-form" class="btn btn-sm btn-light-primary">
                                @lang('dashboard.edit')
                            </a>
                            <form method="POST" action="{{ route('camp-reports.new-camps.destroy', $createdPlan) }}" class="m-0 js-delete-camp-plan-form">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="btn btn-sm btn-light-danger">@lang('dashboard.delete')</button>
                            </form>
                        </div>
                    </div>

                    <div class="table-responsive">
                        <table class="table align-middle table-row-dashed fs-6 gy-4">
                            <thead>
                            <tr class="text-center fw-bolder fs-7 text-uppercase gs-0" style="background-color: #f8f9fa;">
                                <th style="width: 60px;">@lang('dashboard.serial')</th>
                                <th>@lang('dashboard.item_name')</th>
                                <th>@lang('dashboard.item_status')</th>
                                <th>@lang('dashboard.requested_qty')</th>
                                <th>@lang('dashboard.additional_notes')</th>
                            </tr>
                            </thead>
                            <tbody>
                            @foreach($createdItems as $itemIndex => $createdItem)
                                <tr class="text-center">
                                    <td class="fw-bold">{{ $itemIndex + 1 }}</td>
                                    <td>{{ $createdItem['item_name'] ?? '-' }}</td>
                                    <td>
                                        @if (($createdItem['sales_status'] ?? '') === 'buy')
                                            @lang('dashboard.sales_status_buy')
                                        @elseif (($createdItem['sales_status'] ?? '') === 'available')
                                            @lang('dashboard.sales_status_available')
                                        @elseif (($createdItem['sales_status'] ?? '') === 'partial')
                                            @lang('dashboard.sales_status_partial')
                                        @else
                                            -
                                        @endif
                                    </td>
                                    <td>{{ $createdItem['requested_qty'] ?? '-' }}</td>
                                    <td>{{ $createdItem['notes'] ?? '-' }}</td>
                                </tr>
                            @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            @empty
                <div class="text-center text-muted py-6">@lang('dashboard.no_created_camps_yet')</div>
            @endforelse
        </div>
    </div>
@endsection

@push('js')
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const tableBody = document.querySelector('#new-camps-table tbody');
            const addBtn = document.getElementById('add-row-btn');
            const deleteForms = document.querySelectorAll('.js-delete-camp-plan-form');

            function nextIndex() {
                return (tableBody?.querySelectorAll('tr')?.length || 0) + 1;
            }

            addBtn?.addEventListener('click', () => {
                const i = nextIndex();
                const row = document.createElement('tr');
                row.className = 'text-center';
                row.innerHTML = `
                    <td class="fw-bold">${i}</td>
                    <td><input type="text" class="form-control" name="items[${i}][item_name]" required></td>
                    <td>
                        <select class="form-select" name="items[${i}][sales_status]" required>
                            <option value="">—</option>
                            <option value="buy">{{ __('dashboard.sales_status_buy') }}</option>
                            <option value="available">{{ __('dashboard.sales_status_available') }}</option>
                            <option value="partial">{{ __('dashboard.sales_status_partial') }}</option>
                        </select>
                    </td>
                    <td><input type="number" min="0" step="1" class="form-control" name="items[${i}][requested_qty]" required></td>
                    <td><input type="text" class="form-control" name="items[${i}][notes]" required></td>
                `;
                tableBody.appendChild(row);
            });

            deleteForms.forEach((form) => {
                form.addEventListener('submit', function (event) {
                    event.preventDefault();

                    Swal.fire({
                        title: window.deleteTranslations.confirm_delete,
                        text: window.deleteTranslations.delete_warning_message,
                        icon: 'warning',
                        showCancelButton: true,
                        confirmButtonText: window.deleteTranslations.yes_delete,
                        cancelButtonText: window.deleteTranslations.cancel,
                        reverseButtons: true,
                    }).then((result) => {
                        if (result.isConfirmed) {
                            form.submit();
                        }
                    });
                });
            });
        });
    </script>
@endpush

