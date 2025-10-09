<!--begin::Theme mode-->
<div class="d-flex align-items-center ms-1 ms-lg-3">
    <!--begin::Menu toggle-->
    <a href="#" class="btn btn-icon btn-custom btn-active-light btn-active-color-primary w-30px h-30px w-md-40px h-md-40px" data-kt-menu-trigger="click" data-kt-menu-attach="parent" data-kt-menu-placement="bottom-end">
            @if (session('theme') === 'dark')
               <i class="fas fa-sun fs-2 theme-dark-show"></i>
           @else
               <i class="fas fa-moon fs-2 theme-light-show"></i>
           @endif
    </a>
    <!--begin::Menu-->
    <div class="menu menu-sub menu-sub-dropdown menu-column menu-rounded menu-title-gray-700 menu-hover-bg-light-primary menu-active-bg-light-primary fw-semibold py-4 fs-base w-150px" data-kt-menu="true">
        <!--begin::Menu item-->
        <div class="menu-item px-3 mb-2">
            <a href="{{ route('theme.switch', ['theme' => 'light']) }}" class="menu-link px-3 py-2" data-theme="light">
                <span class="menu-icon" data-kt-element="icon">
                    <i class="fas fa-sun fs-3"></i>
                </span>
                <span class="fs-12">@lang('dashboard.light')</span>
            </a>
        </div>
        <!--end::Menu item-->
        <!--begin::Menu item-->
        <div class="menu-item px-3 mb-2">
            <a href="{{ route('theme.switch', ['theme' => 'dark']) }}" class="menu-link px-3 py-2" >
                <span class="menu-icon" data-kt-element="icon">
                    <i class="fas fa-moon fs-3"></i>
                </span>
                <span class="fs-12">@lang('dashboard.dark')</span>
            </a>
        </div>
        <!--end::Menu item-->
       
    </div>
    <!--end::Menu-->
</div>
<!--end::Theme mode-->

