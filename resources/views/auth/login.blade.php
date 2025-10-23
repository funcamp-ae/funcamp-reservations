@extends('layouts.app')

@section('content')

<div class="d-flex flex-column flex-root">
    <div class="d-flex flex-column flex-column-fluid bgi-position-y-bottom position-x-center bgi-no-repeat bgi-size-contain bgi-attachment-fixed" style="">
        <div class="d-flex flex-center flex-column flex-column-fluid p-10 pb-lg-20">
            <a href="#" class="mb-12">
                <img alt="Logo" src="{{asset('images/logo.png')}}" class="h-200px" />
            </a>
            <div class="w-lg-500px bg-body rounded shadow-sm p-10 p-lg-15 mx-auto">
                <form class="form w-100" novalidate="novalidate"  method="POST"  action="{{ route('admin-login')}}">
                    @csrf
                     
                  
                    @if ($errors->has('email'))
                        <span class="invalid-feedback">
                            <strong>{{ $errors->first('email') }}</strong>
                        </span>
                    @endif
                    <div class="fv-row mb-10">
                        <label class="form-label fs-6 fw-bolder">البريد الالكتروني</label>
                        <input class="form-control form-control-lg form-control-solid" type="email" name="email" autocomplete="off" />
                    </div>
                   

                    @if ($errors->has('password'))
                    <span class="invalid-feedback">
                        <strong>{{ $errors->first('password') }}</strong>
                    </span>
                    @endif
                    <div class="fv-row mb-10">
                        <div class="d-flex flex-stack mb-2">
                            <label class="form-label fw-bolder  fs-6 mb-0">كلمة المرور</label>
                        </div>
                        <input class="form-control form-control-lg form-control-solid" type="password" name="password" autocomplete="off" />
                    </div>

                    <!--begin::Remember Me-->
                    <div class="fv-row mb-10">
                        <div class="form-check form-check-custom form-check-solid">
                            <input class="form-check-input" type="checkbox" value="1" name="remember_me" id="remember_me" {{ old('remember_me') ? 'checked' : '' }} />
                            <label class="form-check-label fs-6 fw-bolder" for="remember_me">
                                تذكرني
                            </label>
                        </div>
                    </div>
                    <!--end::Remember Me-->
                   
                    <div class="text-center">
                        <button type="submit" id="" class="btn btn-lg btn-primary w-100 mb-5">
                            <span class="indicator-label">تسجيل الدخول <i class="fa fa-door-open"></i></span>
                            <span class="indicator-progress">جاري التسجيل...
                            <span class="spinner-border spinner-border-sm align-middle ms-2"></span></span>
                        </button>
                        
                        <!-- Google OAuth Button -->
                        <div class="separator separator-content my-6">
                            <span class="text-muted fs-7">أو</span>
                        </div>
                        
                        <a href="{{ route('auth.google') }}" class="btn btn-light btn-lg w-100">
                            <img src="https://developers.google.com/identity/images/g-logo.png" alt="Google" class="me-2" style="width: 20px; height: 20px;">
                            تسجيل الدخول بحساب Google
                        </a>
                    </div>
                </form>
            </div>
        </div>
        <!--end::Content-->
        <!--begin::Footer-->
        
    </div>
</div>
	
@endsection
@section('scripts')

@endsection