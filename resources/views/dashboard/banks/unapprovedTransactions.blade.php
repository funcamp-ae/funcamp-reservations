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
                            @php
                                // Determine linked item id and type for verification route
                                $linkId = null; $linkType = null;
                                if(isset($t->payment_id) && $t->payment_id) { $linkId = $t->payment_id; $linkType = 'payment'; }
                                elseif(isset($t->expense_id) && $t->expense_id) { $linkId = $t->expense_id; $linkType = 'expense'; }
                                elseif(isset($t->order_addon_id) && $t->order_addon_id) { $linkId = $t->order_addon_id; $linkType = 'addon'; }
                                elseif(isset($t->order_item_id) && $t->order_item_id) { $linkId = $t->order_item_id; $linkType = 'warehouse_sales'; }
                                elseif(isset($t->general_payment_id) && $t->general_payment_id) { $linkId = $t->general_payment_id; $linkType = 'general_revenue_deposit'; }
                            @endphp
                            <tr data-item-id="{{ $linkId }}" data-item-type="{{ $linkType }}">
                                <td>{{$loop->iteration }}</td>
                                <td>{!! nl2br(e($t->getDetailedSource() ?? '-')) !!}</td>
                                <td>{{ __('dashboard.' . $t->type) ?? '-' }}</td>
                                <td>{{ $t->account->name ?? '-' }}</td>
                                <td>
                                    @if($t->image_path || $t->images)
                                        <a href="javascript:void(0)" class="view-images" data-images="{{ e(json_encode($t->images ?? [$t->image_path])) }}">{{ __('dashboard.view') }}</a>
                                    @else
                                        -
                                    @endif
                                </td>
                                <td>
                                    @if($t->description)
                                        <a href="javascript:void(0)" class="view-notes" data-notes="{{ e($t->description) }}">{{ __('dashboard.view') }}</a>
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

<!-- Modal for images/notes preview -->
<div class="modal fade" id="previewModal" tabindex="-1" role="dialog">
  <div class="modal-dialog modal-lg" role="document">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title">{{ __('dashboard.preview') }}</h5>
        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
          <span aria-hidden="true">&times;</span>
        </button>
      </div>
      <div class="modal-body" id="previewBody"></div>
      <div class="modal-footer">
        <button type="button" class="btn btn-secondary" data-dismiss="modal">{{ __('dashboard.close') }}</button>
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
            var data = $(this).data('images');
            var images = typeof data === 'string' ? JSON.parse(data) : data;
            var html = '';
            images = images || [];
            images.forEach(function(src){
                if(!src) return;
                html += '<img src="' + src + '" class="img-fluid mb-2" style="max-width:100%;">';
            });
            if(!html) html = '<p>-</p>';
            $('#previewBody').html(html);
            $('#previewModal').modal('show');
        });

        // View notes
        $(document).on('click', '.view-notes', function(){
            var notes = $(this).data('notes') || '';
            $('#previewBody').html('<pre style="white-space:pre-wrap;">'+notes+'</pre>');
            $('#previewModal').modal('show');
        });
    });
</script>
@endpush
