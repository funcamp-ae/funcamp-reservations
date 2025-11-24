    <!-- // This view expects either a collection named $payments or $transactions
    // Each item should expose: id, verified, and optionally a 'type' string.
    // For payments, we assume type 'payment'. -->
@section('pageTitle' , __('dashboard.bank-accounts'))
@extends('dashboard.layouts.app')

@section('content')
<div class="card">
    <div class="card-header">
        <h3 class="card-title">{{ __('dashboard.unapproved_transactions') }}</h3>
    </div>
    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-bordered" id="unapproved-transactions-table">
                <thead>
                    <tr>
                        <th>#</th>
                        <th>{{ __('dashboard.source') }}</th>
                        <th>{{ __('dashboard.type') }}</th>
                        <th>{{ __('dashboard.amount') }}</th>
                        <th>{{ __('dashboard.bank_account') }}</th>
                        <th>{{ __('dashboard.image') }}</th>
                        <th>{{ __('dashboard.notes') }}</th>
                        <th>{{ __('dashboard.reservation_number') }}</th>
                        <th>{{ __('dashboard.action') }}</th>
                    </tr>
                </thead>
                <tbody>
                    @if(isset($transactions) && $transactions->count())
                        @foreach($transactions as $t)
                            <!-- @php
                                // Determine linked item id and type for verification route
                                $linkId = null; $linkType = null;
                                if(isset($t->payment_id) && $t->payment_id) { $linkId = $t->payment_id; $linkType = 'payment'; }
                                elseif(isset($t->expense_id) && $t->expense_id) { $linkId = $t->expense_id; $linkType = 'expense'; }
                                elseif(isset($t->order_addon_id) && $t->order_addon_id) { $linkId = $t->order_addon_id; $linkType = 'addon'; }
                                elseif(isset($t->order_item_id) && $t->order_item_id) { $linkId = $t->order_item_id; $linkType = 'warehouse_sales'; }
                                elseif(isset($t->general_payment_id) && $t->general_payment_id) { $linkId = $t->general_payment_id; $linkType = 'general_revenue_deposit'; }
                            @endphp -->
                            <tr>
                                <td>{{$loop->iteration }}</td>
                                <td>{!! nl2br(e($t->getDetailedSource() ?? '-')) !!}</td>
                                <td>{{ $t->type == 'deposit' ? __('dashboard.moneyDeposit') : __('dashboard.' . $t->type) ?? '-' }}</td>
                                <td>{{ $t->amount ?? '-' }}</td>
                                <td>{{ $t->account->name ?? '-' }}</td>
                                <td>
                                    @php
                                        // Normalize images to an array for JSON encoding in the attribute.
                                        if (is_string($t->images)) {
                                            $previewImages = json_decode($t->images, true) ?: [];
                                        } else {
                                            $previewImages = $t->images ?? ($t->image_path ? [$t->image_path] : []);
                                        }
                                    @endphp
                                    @if(!empty($previewImages))
                                        <a href="javascript:void(0)" class="view-images" data-images='@json($previewImages)'>{{ __('dashboard.view') }}</a>
                                    @else
                                        -
                                    @endif
                                </td>
                                <td>
                                    @if($t->description)
                                        <a href="javascript:void(0)" class="view-notes" data-notes='@json($t->description)'>{{ __('dashboard.view') }}</a>
                                    @else - @endif
                                </td>
                                <td>@if($t->order_id)<a href="{{ route('orders.edit', $t->order_id) }}">{{ $t->order_id }}</a>@else - @endif</td>
                                <td>
                                    <span class="verified-status">{{ $t->verified ? __('dashboard.yes') : __('dashboard.no') }}</span> <br>
                                    @can('payments.approve')
                                        @if($t->verified)
                                            <button class="btn btn-sm btn-danger verify-btn" data-id="{{ $linkId }}" data-type="{{ $linkType }}">{{ __('dashboard.mark') }} {{ __('dashboard.unverifyed') }}</button>
                                        @else
                                            <button class="btn btn-sm btn-success verify-btn" data-id="{{ $linkId }}" data-type="{{ $linkType }}">{{ __('dashboard.mark') }} {{ __('dashboard.verified') }}</button>
                                        @endif
                                    @endcan
                                </td>
                            </tr>
                        @endforeach
                    @else
                        <tr>
                            <td colspan="8">{{ __('dashboard.no_records') }}</td>
                        </tr>
                    @endif
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- Modal for images preview -->
<div class="modal fade" id="imagesModal" tabindex="-1" role="dialog">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">{{ __('dashboard.images_preview') ?? __('dashboard.preview') }}</h5>
                <button type="button border-0" class="close" data-dismiss="modal" data-bs-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body" id="imagesPreviewBody">
                <div id="previewCarousel" class="carousel slide" data-ride="carousel" data-bs-ride="carousel">
                    <ol class="carousel-indicators"></ol>
                    <div class="carousel-inner"></div>
                    <a class="carousel-control-prev" href="#previewCarousel" role="button" data-slide="prev" data-bs-slide="prev">
                        <span class="carousel-control-prev-icon" aria-hidden="true"></span>
                        <span class="sr-only">Previous</span>
                    </a>
                    <a class="carousel-control-next" href="#previewCarousel" role="button" data-slide="next" data-bs-slide="next">
                        <span class="carousel-control-next-icon" aria-hidden="true"></span>
                        <span class="sr-only">Next</span>
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Modal for notes preview -->
<div class="modal fade" id="notesModal" tabindex="-1" role="dialog">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">{{ __('dashboard.notes') }}</h5>
                <button type="button" class="close" style="border: none;" data-dismiss="modal" data-bs-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body" id="notesPreviewBody"></div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal" data-bs-dismiss="modal">{{ __('dashboard.close') }}</button>
            </div>
        </div>
    </div>
</div>
@endsection
@push('scripts')
<script>
    $(function(){
        $.ajaxSetup({ headers: { 'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content') } });

        // Handle verify toggle
        $('#unapproved-transactions-table').on('click', '.verify-btn', function(e){
            e.preventDefault();
            var btn = $(this);
            var id = btn.data('id');
            var type = btn.data('type');
            if (!id || !type) return alert('{{ __('dashboard.invalid_request') }}');
            btn.prop('disabled', true);
            var url = window.location.origin + '/order/verified/' + id + '/' + type;
            $.get(url, {}, function(resp){
                if (resp && resp.success) {
                    // On success remove the table row for this item (fade out for a smoother UX)
                    var row = btn.closest('tr');
                    row.fadeOut(200, function(){ $(this).remove(); });
                } else {
                    alert(resp.message || '{{ __('dashboard.something_went_wrong') }}');
                }
            }).fail(function(xhr){
                var msg = xhr.responseJSON && xhr.responseJSON.message ? xhr.responseJSON.message : '{{ __('dashboard.something_went_wrong') }}';
                alert(msg);
            }).always(function(){ btn.prop('disabled', false); });
        });

        // View images
        $(document).on('click', '.view-images', function(){
            var raw = $(this).attr('data-images');
            var images = [];
            try {
                images = raw ? JSON.parse(raw) : [];
                // Handle double-encoded JSON (string that contains JSON array)
                if (typeof images === 'string') {
                    images = images ? JSON.parse(images) : [];
                }
            } catch (e) {
                images = [];
            }
            // Ensure we have an array
            if (!Array.isArray(images)) {
                images = images ? [images] : [];
            }
            images = images || [];
            // populate existing carousel skeleton
            var indicatorsHtml = '';
            var innerHtml = '';
            images.forEach(function(src, idx){
                if(!src) return;
                indicatorsHtml += '<li data-target="#previewCarousel" data-slide-to="'+idx+'"'+(idx===0? ' class="active"' : '')+'></li>';
                innerHtml += '<div class="carousel-item'+(idx===0? ' active' : '')+'">';
                innerHtml += '<img src="' + "{{ env('APP_URL') }}/storage/" + src + '" class="d-block w-100" style="max-height:70vh; object-fit:contain;">';
                innerHtml += '</div>';
            });

            // If no images, show placeholder instead of carousel
            if (!indicatorsHtml && !innerHtml) {
                $('#previewCarousel').hide();
                $('#imagesPreviewBody').html('<p>-</p>');
            } else {
                $('#previewCarousel').show();
                $('#previewCarousel .carousel-indicators').html(indicatorsHtml);
                $('#previewCarousel .carousel-inner').html(innerHtml);
                // show carousel (but disable controls if single image)
                var $prev = $('#previewCarousel .carousel-control-prev');
                var $next = $('#previewCarousel .carousel-control-next');
                if (images.length < 2) {
                    $prev.css({'pointer-events':'none','opacity':0.5});
                    $next.css({'pointer-events':'none','opacity':0.5});
                } else {
                    $prev.css({'pointer-events':'','opacity':''});
                    $next.css({'pointer-events':'','opacity':''});
                }

                // initialize carousel (support both BS4 and BS5)
                try {
                    if (typeof $().carousel === 'function') { $('#previewCarousel').carousel(0); }
                    else if (window.bootstrap && window.bootstrap.Carousel) { new bootstrap.Carousel(document.getElementById('previewCarousel'));
                    }
                } catch (err) {
                    // ignore initialization errors
                }
            }

            $('#imagesModal').modal('show');
        });

        // View notes (plain content, no extra wrappers)
        $(document).on('click', '.view-notes', function(){
            var raw = $(this).attr('data-notes');
            var notes = '';
            try { notes = raw ? JSON.parse(raw) : ''; } catch (e) { notes = raw || ''; }
            // Insert plain text (no HTML wrappers) to avoid extra formatting
            $('#notesPreviewBody').text(notes);
            $('#notesModal').modal('show');
        });

        // Ensure modal close works across Bootstrap versions and if data attributes don't bind (apply to both modals)
        $('#imagesModal, #notesModal').on('click', '[data-dismiss="modal"], [data-bs-dismiss="modal"], .close, .btn-close', function(e){
            e.preventDefault();
            try { $(this).closest('.modal').modal('hide'); } catch(err) { $(this).closest('.modal').hide(); }
        });
    });
</script>
@endpush
