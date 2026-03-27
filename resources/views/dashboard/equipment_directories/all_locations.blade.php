@extends('dashboard.layouts.app')
@section('pageTitle', __('dashboard.equipment_locations_statistics'))

@section('content')
    <div class="card card-flush">
        <div class="card-header align-items-center py-5 gap-2 gap-md-5">
            <div class="card-title d-flex align-items-center flex-column gap-3 ">
                <h3>@lang('dashboard.equipment_locations_statistics')</h3>
                <div class="d-flex align-items-center position-relative my-1">
                    <input type="text" class="form-control form-control-solid w-250px ps-14" 
                        placeholder="@lang('dashboard.search_items')" id="search-input" />
                </div>
            </div>
            <div class="card-toolbar">
                <div class="gap-2">
                    <div class="d-flex flex-wrap gap-2">
                        <button
                            type="button"
                            class="btn btn-success d-inline-flex align-items-center gap-2"
                            id="prepare-equipment-export-btn"
                            data-user-id="{{ auth()->id() }}"
                            data-prepare-url="{{ route('equipment.all-locations.export.prepare') }}"
                            data-export-url="{{ route('equipment.all-locations.export') }}"
                            data-default-label="@lang('dashboard.export_pdf')"
                            data-loading-label="@lang('dashboard.preparing_data')"
                            data-timeout-message="@lang('dashboard.export_taking_too_long')"
                            data-realtime-unavailable-message="@lang('dashboard.export_realtime_unavailable')"
                            data-prepare-failed-message="@lang('dashboard.export_prepare_failed')"
                            data-missing-token-message="@lang('dashboard.export_missing_token')"
                            data-progress-initial-label="@lang('dashboard.export_preparing_pdf')"
                            data-progress-ready-label="@lang('dashboard.export_ready_starting_download')"
                            data-progress-template="@lang('dashboard.export_preparing_pdf_progress')"
                        >
                            <i class="fas fa-file-pdf"></i>
                            <span>@lang('dashboard.export_pdf')</span>
                        </button>       
                        <div class="form-check form-check-inline align-items-center ms-3">
                            <input class="form-check-input" type="checkbox" id="notify-whatsapp" value="1">
                            <label class="form-check-label small ms-1" for="notify-whatsapp">@lang('dashboard.send_me_on_whatsapp_when_finished')</label>
                        </div>
                        <a href="{{ route('equipment.all-locations.export') }}" class="btn btn-outline-secondary">@lang('dashboard.download_last_prepared_pdf')</a>
                    </div>
                    <div id="equipment-export-progress" class="mt-3 d-none">
                        <div class="d-flex justify-content-between align-items-center mb-1">
                            <small id="equipment-export-progress-text" class="text-muted">@lang('dashboard.export_preparing_pdf')</small>
                            <small id="equipment-export-progress-percent" class="text-muted">0%</small>
                        </div>
                        <div class="progress" style="height: 8px;">
                            <div
                            id="equipment-export-progress-bar"
                            class="progress-bar progress-bar-striped progress-bar-animated"
                            role="progressbar"
                            style="width: 0%; transition: width 0.35s ease;"
                            aria-valuemin="0"
                            aria-valuemax="100"
                            aria-valuenow="0"
                            ></div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="card-body pt-0">
            <div class="table-responsive">
                <table class="table align-middle table-row-dashed fs-6 gy-5" id="kt_table_equipment_locations">
                    <thead>
                        <tr class="text-start text-muted fw-bolder fs-7 text-uppercase gs-0">
                            <th>#</th>
                            <th class="min-w-100px">@lang('dashboard.directory_name')</th>
                            <th class="min-w-100px">@lang('dashboard.item_name')</th>
                            <th class="min-w-100px">@lang('dashboard.item_contents')</th>
                            <th class="min-w-100px">@lang('dashboard.location')</th>
                            <th class="min-w-100px">@lang('dashboard.media')</th>
                            <th class="min-w-100px">@lang('dashboard.quantity')</th>
                            <th class="min-w-100px">@lang('dashboard.additional_notes')</th>
                        </tr>
                    </thead>
                    <tbody class="text-gray-600 fw-bold">
                        @foreach ($items as $item)
                            <tr>
                                <td>{{ $loop->iteration }}</td>
                                <td>{{ $item->directory->name ?? '-' }}</td>
                                <td><a href="{{ route('equipment-directories.items.edit', [$item->directory_id, $item->id]) }}" class="text-decoration-none">{{ $item->name }}</a></td>
                                <td>{{ $item->contents }}</td>
                                <td>{{ $item->location }}</td>
                                <td>
                                    <div class="d-flex gap-2 justify-content-center flex-wrap">
                                        @forelse($item->media->where('file_type', 'image') as $media)
                                            <a href="{{  $media->file_url ?? env('AWS_URL') . '/' . $media->file_path }}" target="_blank" rel="noopener noreferrer" onclick="event.stopPropagation();">
                                                <img src="{{ $media->file_url ?? env('AWS_URL') . '/' . $media->file_path }}" alt="photo" style="width:52px;height:52px;object-fit:cover;border-radius:6px;border:1px solid #ddd;">
                                            </a>
                                        @empty
                                            <span class="text-muted small">-</span>
                                        @endforelse
                                    </div>
                                </td>
                                <td>{{ $item->quantity }}</td>
                                <td>{{ $item->notes }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>
@endsection

@push('scripts')
<script>
    document.addEventListener("DOMContentLoaded", function () {
        var table = document.getElementById('kt_table_equipment_locations');
        if (!table) return;

        var searchInput = document.getElementById('search-input');
        if (searchInput) {
            searchInput.addEventListener('keyup', function (e) {
                var searchPattern = new RegExp(e.target.value, 'i');
                var rows = table.querySelectorAll('tbody tr');
                
                rows.forEach(function (row) {
                    var show = false;
                    var cells = row.querySelectorAll('td');
                    cells.forEach(function (cell) {
                        if (searchPattern.test(cell.textContent)) {
                            show = true;
                        }
                    });
                    row.style.display = show ? '' : 'none';
                });
            });
        }
    });
</script>
@endpush

@push('js')
<script>
    (function () {
        const button = document.getElementById('prepare-equipment-export-btn');
        if (!button) {
            return;
        }

        const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '';
        const originalLabel = button.dataset.defaultLabel || 'Export PDF';
        const loadingLabel = button.dataset.loadingLabel || 'Preparing data...';
        const timeoutMessage = button.dataset.timeoutMessage || 'Export is taking too long. Please try again in a moment.';
        const realtimeUnavailableMessage = button.dataset.realtimeUnavailableMessage || 'Real-time export notifications are unavailable.';
        const prepareFailedMessage = button.dataset.prepareFailedMessage || 'Failed to prepare export data.';
        const missingTokenMessage = button.dataset.missingTokenMessage || 'Missing export token.';
        const progressInitialLabel = button.dataset.progressInitialLabel || 'Preparing PDF...';
        const progressReadyLabel = button.dataset.progressReadyLabel || 'Export ready. Starting download...';
        const progressTemplate = button.dataset.progressTemplate || 'Preparing PDF... (:processed/:total chunks)';
        const userId = button.dataset.userId;
        const progressContainer = document.getElementById('equipment-export-progress');
        const progressBar = document.getElementById('equipment-export-progress-bar');
        const progressPercentText = document.getElementById('equipment-export-progress-percent');
        const progressText = document.getElementById('equipment-export-progress-text');
        let currentChannel = null;

        function setProgress(percent, labelText) {
            const normalized = Math.max(0, Math.min(100, Number(percent) || 0));

            if (progressContainer) {
                progressContainer.classList.remove('d-none');
            }

            if (progressBar) {
                progressBar.style.width = normalized + '%';
                progressBar.setAttribute('aria-valuenow', String(normalized));
            }

            if (progressPercentText) {
                progressPercentText.textContent = normalized + '%';
            }

            if (progressText && labelText) {
                progressText.textContent = labelText;
            }
        }

        function resetProgress() {
            if (progressContainer) {
                progressContainer.classList.add('d-none');
            }

            if (progressBar) {
                progressBar.style.width = '0%';
                progressBar.setAttribute('aria-valuenow', '0');
            }

            if (progressPercentText) {
                progressPercentText.textContent = '0%';
            }

            if (progressText) {
                progressText.textContent = progressInitialLabel;
            }
        }

        function stopListening() {
            if (currentChannel && typeof Echo !== 'undefined') {
                Echo.leave(currentChannel);
            }

            currentChannel = null;
        }

        function waitUntilReadyByEvent(token, exportUrl) {
            return new Promise(function (resolve, reject) {
                if (typeof Echo === 'undefined' || !userId) {
                    reject(new Error(realtimeUnavailableMessage));
                    return;
                }

                currentChannel = `user.${userId}`;

                Echo.private(`user.${userId}`)
                    .listen('.equipment-location-export.progress', function (eventPayload) {
                        if (!eventPayload || eventPayload.token !== token) {
                            return;
                        }

                        const processed = Number(eventPayload.processedChunks || eventPayload.processed_chunks || 0);
                        const total = Number(eventPayload.totalChunks || eventPayload.total_chunks || 0);
                        const percent = Number(eventPayload.progressPercent || eventPayload.progress_percent || 0);
                        const label = total > 0
                            ? progressTemplate
                                .replace(':processed', String(processed))
                                .replace(':total', String(total))
                            : progressInitialLabel;

                        setProgress(percent, label);
                    })
                    .listen('.equipment-location-export.ready', function (eventPayload) {
                        if (!eventPayload || eventPayload.token !== token) {
                            return;
                        }
                        stopListening();
                        setProgress(100, progressReadyLabel);
                        button.disabled = false;
                        button.querySelector('span').textContent = originalLabel;
                          const downloadUrl = eventPayload.downloadUrl || exportUrl || ("{{ route('equipment.all-locations.export') }}?token=" + token);
                        window.location.href = downloadUrl;
                        resolve();
                    });
            });
        }

        button.addEventListener('click', async function () {
            if (button.disabled) {
                return;
            }

            button.disabled = true;
            button.querySelector('span').textContent = loadingLabel;
            setProgress(0, progressInitialLabel);

            const params = new URLSearchParams(window.location.search);

            // include notify_whatsapp checkbox value
            const notifyCheckbox = document.getElementById('notify-whatsapp');
            if (notifyCheckbox) {
                params.set('notify_whatsapp', notifyCheckbox.checked ? '1' : '0');
            }

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
                    throw new Error(payload.message || prepareFailedMessage);
                }

                if (!payload.token) {
                    throw new Error(missingTokenMessage);
                }

                const exportUrl = payload.export_url || (function () {
                    const fallbackExportUrl = new URL(button.dataset.exportUrl, window.location.origin);
                    fallbackExportUrl.searchParams.set('token', payload.token);
                    return fallbackExportUrl.toString();
                })();

                await waitUntilReadyByEvent(payload.token, exportUrl);
            } catch (error) {
                stopListening();
                alert(error.message || prepareFailedMessage);
                button.disabled = false;
                button.querySelector('span').textContent = originalLabel;
                resetProgress();
            }
        });
    })();
</script>
@endpush
