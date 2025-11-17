@extends('dashboard.layouts.app')
@section('pageTitle' , __('dashboard.expense-items'))

@section('content')


<!--begin::Post-->
<div class="post d-flex flex-column-fluid" id="kt_post">
    <!--begin::Container-->
    <div id="kt_content_container" class="container-xxl">
        <!--begin::Category-->
        <div class="card card-flush">





                    <!--begin::Category-->
         <div class="card card-flush mt-10">
            <!--begin::Card header-->
            <div class="card-header align-items-center py-5 gap-2 gap-md-5">
                <!--begin::Card title-->
                <div class="card-title">
                    <!--begin::Search-->
                    <div class="d-flex align-items-center position-relative my-1">
                        <!--begin::Svg Icon | path: icons/duotune/general/gen021.svg-->
                        <span class="svg-icon svg-icon-1 position-absolute ms-4">
                            <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none">
                                <rect opacity="0.5" x="17.0365" y="15.1223" width="8.15546" height="2" rx="1" transform="rotate(45 17.0365 15.1223)" fill="currentColor" />
                                <path d="M11 19C6.55556 19 3 15.4444 3 11C3 6.55556 6.55556 3 11 3C15.4444 3 19 6.55556 19 11C19 15.4444 15.4444 19 11 19ZM11 5C7.53333 5 5 7.53333 5 11C5 14.4667 7.53333 17 11 17C14.4667 17 17 14.4667 17 11C17 7.53333 14.4667 5 11 5Z" fill="currentColor" />
                            </svg>
                        </span>
                        <!--end::Svg Icon-->
                        <input type="text" data-kt-ecommerce-category-filter="search" class="form-control form-control-solid w-250px ps-14" placeholder="@lang('dashboard.search_title', ['page_title' => __('dashboard.expense-items')])" />
                    </div>
                    <!--end::Search-->
                </div>
                <!--end::Card title-->
                <!--begin::Card toolbar-->
                <div class="card-toolbar">
                    @can('expense-items.create')
                    <!--begin::Add customer-->
                    <a href="{{ route('expense-items.create')}}" class="btn btn-success">
                        @lang('dashboard.create_title', ['page_title' => __('dashboard.expense-items')])
                        <i class="fa fa-plus"></i>
                    </a>
                    <!--end::Add customer-->
                    @endcan

                </div>
                <!--end::Card toolbar-->
            </div>

            <!--end::Card header-->
            <!--begin::Card body-->
            <div class="card-body pt-0">

                <!--begin::Table-->
                <table class="table align-middle table-row-dashed fs-6 gy-5" id="kt_ecommerce_category_table">
                    <!--begin::Table head-->
                    <thead>
                        <!--begin::Table row-->
                        <tr class="text-center    fw-bolder fs-6 text-uppercase gs-0" style="background-color: #f8f9fa; font-weight: 900 !important;">
                            <th class="w-10px pe-2 text-center">
                                <div class="form-check form-check-sm form-check-custom form-check-solid me-3" >
                                    <input class="form-check-input" id="checkedAll"  type="checkbox" data-kt-check="true" data-kt-check-target="#kt_ecommerce_category_table .form-check-input" value="1" />
                                </div>
                            </th>
                            <th class="fw-bolder">#</th>
                            <th class="fw-bolder">@lang('dashboard.name')</th>
                            <th class="fw-bolder">@lang('dashboard.number_of_times')</th>
                            <th class="fw-bolder">@lang('dashboard.notes')</th>
                            
                            <th class="fw-bolder">@lang('dashboard.created_date')</th>
                            <th class="fw-bolder">@lang('dashboard.created_time')</th>
                            <th class="text-end min-w-70px fw-bolder">@lang('dashboard.actions')</th>
                        </tr>
                        <!--end::Table row-->
                    </thead>
                    <!--end::Table head-->
                    <!--begin::Table body-->
                    <tbody class="fw-bold
                        @foreach ($expenseItems as $item)
                            <!--begin::Table row-->
                            <tr data-id="{{$item->id}}">
                                <!--begin::Checkbox-->
                                <td>
                                    <div class="form-check form-check-sm form-check-custom form-check-solid">
                                        <input class="form-check-input checkSingle" type="checkbox" value="1" id="{{$item->id}}"/>
                                    </div>
                                </td>
                                <td class="ordered-col">
                                    <div>
                                        <button class="btn btn-sm btn-light move-up" data-id="{{$item->id}}" data-order="{{$item->ordered_count}}" title="@lang('dashboard.up')">▲</button>
                                        <button class="btn btn-sm btn-light move-down" data-id="{{$item->id}}" data-order="{{$item->ordered_count}}" title="@lang('dashboard.down')">▼</button>
                                    </div>
                                </td>
                                <td>
                                    <div class="d-flex">
                                        <div class="ms-5">
                                            <span >@translate($item->name)</span>
                                        </div>
                                    </div>
                                </td>
                                <td>{{$item->expenses->count() }} </td>
                                <td>@translate($item->description) </td>
                                <td>{{ $item->created_at->format('Y-m-d') }}</td>
                                <td>{{ $item->created_at->format('h:i A') }}</td>

                                <!--begin::Action=-->
                                <td class="text-end">
                                    <a href="#" class="btn btn-sm btn-light btn-active-light-primary" data-kt-menu-trigger="click" data-kt-menu-placement="bottom-end">@lang('dashboard.actions')
                                    <!--begin::Svg Icon | path: icons/duotune/arrows/arr072.svg-->
                                    <span class="svg-icon svg-icon-5 m-0">
                                        <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none">
                                            <path d="M11.4343 12.7344L7.25 8.55005C6.83579 8.13583 6.16421 8.13584 5.75 8.55005C5.33579 8.96426 5.33579 9.63583 5.75 10.05L11.2929 15.5929C11.6834 15.9835 12.3166 15.9835 12.7071 15.5929L18.25 10.05C18.6642 9.63584 18.6642 8.96426 18.25 8.55005C17.8358 8.13584 17.1642 8.13584 16.75 8.55005L12.5657 12.7344C12.2533 13.0468 11.7467 13.0468 11.4343 12.7344Z" fill="currentColor" />
                                        </svg>
                                    </span>
                                    <!--end::Svg Icon--></a>
                                    <!--begin::Menu-->
                                    <div class="menu menu-sub menu-sub-dropdown menu-column menu-rounded menu-gray-600 menu-state-bg-light-primary fw-bold fs-7 w-125px py-4" data-kt-menu="true">
                                        <!--begin::Menu item-->
                                        @can('expense-items.show')
                                        <div class="menu-item px-3">
                                            <a href="{{route('expense-items.show', $item->id)}}" class="menu-link px-3">@lang('dashboard.show')</a>
                                        </div>
                                        @endcan
                                        @can('expense-items.edit')
                                        <div class="menu-item px-3">
                                            <a href="{{route('expense-items.edit', $item->id)}}" class="menu-link px-3">@lang('dashboard.edit')</a>
                                        </div>
                                        @endcan
                                        <!--end::Menu item-->
                                        <!--begin::Menu item-->
                                        @can('expense-items.destroy')
                                        <div class="menu-item px-3">
                                            <a href="#" class="menu-link px-3" data-kt-ecommerce-category-filter="delete_row" data-url="{{route('expense-items.destroy', $item->id)}}" data-id="{{$item->id}}">@lang('dashboard.delete')</a>
                                        </div>
                                        @endcan
                                        <!--end::Menu item-->
                                    </div>
                                    <!--end::Menu-->
                                </td>
                                <!--end::Action=-->
                            </tr>
                            <!--end::Table row-->
                        @endforeach
                    </tbody>
                    <!--end::Table body-->
                </table>
                <!--end::Table-->
            </div>
            <!--end::Card body-->
        </div>
        <!--end::Category-->

            <!--end::Card body-->
        </div>
        <!--end::Category-->
    </div>
    <!--end::Container-->
</div>
<!--end::Post-->

@endsection
@push('css')
<style>
    .total-price-h {
        padding: 20px;
    width: 30%;
    text-align: center;
    border-radius: 5%;
    border: 1px solid #000;
    }
</style>
@endpush

@push('scripts')
<script>
    (function(){
        const token = document.querySelector('meta[name="csrf-token"]').getAttribute('content');

        function moveItem(id, direction, order, $tr) {
            if ((direction === 'down' && order <= 1) || (direction === 'up' && $tr.is(':last-child'))) {
                return; // No movement possible
            }
            // Optimistically swap rows in the DOM
            if (direction === 'up') {
                const $prev = $tr.prev();
                if ($prev && $prev.length) {
                    $tr.insertBefore($prev);

                    // Update data-order attributes
                    const prevOrder = $prev.find('.move-up').data('order');
                    $prev.find('.move-up, .move-down').data('order', order);
                    $tr.find('.move-up, .move-down').data('order', prevOrder);
                }
            } else {
                const $next = $tr.next();
                if ($next && $next.length) {
                    $tr.insertAfter($next);

                    // Update data-order attributes
                    const nextOrder = $next.find('.move-up').data('order');
                    $next.find('.move-up, .move-down').data('order', order);
                    $tr.find('.move-up, .move-down').data('order', nextOrder);
                }
            }

            // Send the request to the server
            axios.post(`{{ url('expense-items') }}/${id}/move`, { direction: direction }, { headers: { 'X-CSRF-TOKEN': token } })
                .then(function(res){
                    if (res.data.moved) {
                        // Update ordered_count displays if provided
                        if (res.data.current) {
                            const cur = res.data.current;
                            $(`.ordered-count[data-id='${cur.id}']`).text(cur.ordered_count);
                        }
                        if (res.data.neighbor) {
                            const nei = res.data.neighbor;
                            $(`.ordered-count[data-id='${nei.id}']`).text(nei.ordered_count);
                        }
                    } else {
                        console.log(res);
                        // Revert the optimistic swap if the server indicates failure
                        if (direction === 'up') {
                            const $prev = $tr.next();
                            if ($prev && $prev.length) {
                                $tr.insertAfter($prev);

                                // Revert data-order attributes
                                const prevOrder = $prev.find('.move-up').data('order');
                                $prev.find('.move-up, .move-down').data('order', order);
                                $tr.find('.move-up, .move-down').data('order', prevOrder);
                            }
                        } else {
                            const $next = $tr.prev();
                            if ($next && $next.length) {
                                $tr.insertBefore($next);

                                // Revert data-order attributes
                                const nextOrder = $next.find('.move-up').data('order');
                                $next.find('.move-up, .move-down').data('order', order);
                                $tr.find('.move-up, .move-down').data('order', nextOrder);
                            }
                        }
                    }
                })
                .catch(function(err){
                    console.error(err);
                    Swal.fire({ icon: 'error', title: '{{ __('dashboard.error') }}', text: err.response?.data?.message || err.message });

                    // Revert the optimistic swap on error
                    if (direction === 'up') {
                        const $prev = $tr.next();
                        if ($prev && $prev.length) {
                            $tr.insertAfter($prev);
                        }
                    } else {
                        const $next = $tr.prev();
                        if ($next && $next.length) {
                            $tr.insertBefore($next);
                        }
                    }
                });
        }

        $(document).on('click', '.move-up, .move-down', function(e){
            e.preventDefault();
            const id = $(this).data('id');
            const order = $(this).data('order');
            const dir = $(this).hasClass('move-up') ? 'up' : 'down';
            const $tr = $(this).closest('tr');
            moveItem(id, dir, order, $tr);
        });
    })();
</script>
@endpush
