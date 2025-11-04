@extends('layouts.app')

@section('content')
@push('css')
<style>
    /* Custom styles for the login page */
    body {
        background-color: #f8f9fa;
        font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
    }
    
    /* Input fields with glass effect - consistent state */
    .form-control {
        background: rgba(255, 255, 255, 0.15) !important;
        border: 2px solid rgba(255, 255, 255, 0.3) !important;
        color: white !important;
        backdrop-filter: blur(10px);
        transition: all 0.3s ease;
    }
    
    .form-control::placeholder {
        color: rgba(255, 255, 255, 0.6) !important;
    }
    
    .form-control:focus {
        background: rgba(255, 255, 255, 0.2) !important;
        border-color: rgba(255, 255, 255, 0.6) !important;
        box-shadow: 0 0 0 0.2rem rgba(255, 255, 255, 0.15) !important;
        transform: translateY(-2px);
    }
    
    .form-control:hover {
        background: rgba(255, 255, 255, 0.18) !important;
        border-color: rgba(255, 255, 255, 0.5) !important;
    }
    
    /* Autofill override to prevent white background */
    .form-control:-webkit-autofill,
    .form-control:-webkit-autofill:hover,
    .form-control:-webkit-autofill:focus,
    .form-control:-webkit-autofill:active {
        -webkit-background-clip: text;
        -webkit-text-fill-color: white !important;
        transition: background-color 5000s ease-in-out 0s;
        box-shadow: inset 0 0 20px 20px rgba(255, 255, 255, 0.15) !important;
        border: 2px solid rgba(255, 255, 255, 0.3) !important;
    }
    
    /* Labels */
    .form-label {
        color: white !important;
        font-weight: 600;
        font-size: 14px;
        letter-spacing: 0.5px;
        text-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
    }
    
    /* Custom checkbox */
    .form-check-input {
        width: 20px;
        height: 20px;
        background: rgba(255, 255, 255, 0.15) !important;
        border: 2px solid rgba(255, 255, 255, 0.4) !important;
        backdrop-filter: blur(10px);
        cursor: pointer;
        transition: all 0.3s ease;
    }
    
    .form-check-input:checked {
        background: linear-gradient(135deg, #8B4513 0%, #b65d1dff 100%) !important;
        border-color: rgba(255, 255, 255, 0.6) !important;
        box-shadow: 0 4px 12px rgba(139, 69, 19, 0.4);
    }
    
    
    .form-check-label {
        color: white !important;
        font-weight: 500;
        margin-right: 8px;
        cursor: pointer;
        user-select: none;
    }
    
    /* Primary button */
    .btn-primary {
        background: linear-gradient(135deg, #8B4513 0%, #b65d1dff 100%);
        border: none;
        position: relative;
        overflow: hidden;
    }
    
    .btn-primary::before {
        content: '';
        position: absolute;
        top: 0;
        left: -100%;
        width: 100%;
        height: 100%;
        background: linear-gradient(90deg, transparent, rgba(255, 255, 255, 0.2), transparent);
        transition: left 0.5s;
    }
    
    .btn-primary:hover::before {
        left: 100%;
    }
    
    .btn-primary:hover {
        background: linear-gradient(135deg, #7d4a19 0%, #a0522dff 100%);
        transform: translateY(-2px);
    }
    
    .btn-primary:active {
        transform: translateY(0);
    }
    
    /* Forgot password link */
    .forgot-password-link {
        color: white !important;
        text-decoration: none;
        font-weight: 500;
        position: relative;
        transition: all 0.3s ease;
    }
    
    .forgot-password-link::after {
        content: '';
        position: absolute;
        width: 0;
        height: 2px;
        bottom: -2px;
        left: 0;
        background-color: white;
        transition: width 0.3s ease;
    }
    
    .forgot-password-link:hover {
        color: rgba(255, 255, 255, 0.9) !important;
        transform: translateX(-3px);
    }
    
    .forgot-password-link:hover::after {
        width: 100%;
    }
    
    /* Google button */
    .btn-google {
        border: 2px solid rgba(255, 255, 255, 0.3) !important;
        backdrop-filter: blur(10px);
        transition: all 0.3s ease;
    }
    
    .btn-google img {
        transition: transform 0.3s ease;
    }
    
    .btn-google:hover img {
        transform: scale(1.2);
    }
    
    /* Card glass effect */
    .login-card {
        backdrop-filter: blur(20px);
        background: rgba(255, 255, 255, 0.1) !important;
        border: 1px solid rgba(255, 255, 255, 0.2) !important;
        box-shadow: 0 8px 32px rgba(0, 0, 0, 0.1);
    }
    
    /* Invalid feedback */
    .invalid-feedback {
        background: rgba(220, 53, 69, 0.15) !important;
        border-left: 3px solid #dc3545;
        padding: 8px 12px;
        border-radius: 6px;
        margin-top: 8px;
        color: #fff !important;
        font-weight: 500;
    }
    
    /* Divider */
    .divider-text {
        background: rgba(255, 255, 255, 0.1) !important;
        backdrop-filter: blur(10px);
        color: white !important;
        padding: 0 15px;
        font-weight: 500;
    }
    
    /* Animated circles */
    @keyframes float {
        0%, 100% {
            transform: translateY(0) rotate(0deg);
        }
        50% {
            transform: translateY(-20px) rotate(180deg);
        }
    }
    
    .floating-circle-1 {
        animation: float 8s ease-in-out infinite;
    }
    
    .floating-circle-2 {
        animation: float 6s ease-in-out infinite reverse;
    }
    
    /* Loading spinner */
    .spinner-border {
        border-color: rgba(255, 255, 255, 0.3);
        border-right-color: white;
    }
    
    /* Smooth entrance animation */
    @keyframes fadeInUp {
        from {
            opacity: 0;
            transform: translateY(30px);
        }
        to {
            opacity: 1;
            transform: translateY(0);
        }
    }
    
    .animate-entrance {
        animation: fadeInUp 0.6s ease-out;
    }
</style>
@endpush

<div class="min-vh-100 d-flex align-items-center justify-content-center position-relative overflow-hidden" style="background: linear-gradient(135deg, #855839 0%, #bb6d35 50%, #7d4a19 100%);">
    <!-- Animated background elements -->
    <div class="position-absolute w-100 h-100" style="opacity: 0.08;">
        <div class="position-absolute rounded-circle floating-circle-1" style="width: 300px; height: 300px; background: white; top: -100px; right: -100px;"></div>
        <div class="position-absolute rounded-circle floating-circle-2" style="width: 200px; height: 200px; background: white; bottom: -50px; left: -50px;"></div>
        <div class="position-absolute rounded-circle floating-circle-1" style="width: 150px; height: 150px; background: white; top: 50%; left: 10%;"></div>
    </div>

    <div class="container position-relative" style="z-index: 1;">
        <div class="row justify-content-center">
            <div class="col-md-5 col-lg-4">
                <!-- Logo -->
                <div class="text-center mb-4 animate-entrance">
                    <img alt="Logo" src="{{asset('images/logo.png')}}" class="mb-3" style="height: 100px; filter: drop-shadow(0 4px 8px rgba(0, 0, 0, 0.2));" />
                    <h2 class="text-white fw-bold mb-2" style="text-shadow: 0 2px 4px rgba(0, 0, 0, 0.2); font-size: 28px;">مرحباً بعودتك</h2>
                    <p class="text-white" style="opacity: 0.85; font-size: 15px;">سجل دخولك للمتابعة</p>
                </div>

                <!-- Login Card -->
                <div class="card border-0 shadow-lg login-card animate-entrance" style="border-radius: 24px; animation-delay: 0.1s;">
                    <div class="card-body p-4 p-lg-5">
                        <form class="form w-100" novalidate="novalidate" method="POST" action="{{ route('admin-login')}}">
                            @csrf
                            
                            <!-- Error Message -->
                            @if(session('error'))
                                <div class="alert alert-danger d-flex align-items-center mb-4" style="border-radius: 14px; background: rgba(220, 53, 69, 0.15) !important; border: 2px solid rgba(220, 53, 69, 0.3); color: white !important; backdrop-filter: blur(10px);">
                                    <i class="fas fa-exclamation-triangle me-3" style="font-size: 20px;"></i>
                                    <div>
                                        <strong>{{ session('error') }}</strong>
                                    </div>
                                </div>
                            @endif
                            
                            <!-- Email or Phone Field -->
                            <div class="mb-4">
                                <label class="form-label mb-2">
                                    البريد الإلكتروني أو رقم الهاتف
                                </label>
                                <input 
                                    class="form-control form-control-lg @error('email') is-invalid @enderror" 
                                    type="text" 
                                    name="email" 
                                    value="{{ old('email') }}"
                                    autocomplete="off" 
                                    placeholder="أدخل بريدك الإلكتروني أو رقم الهاتف"
                                    style="border-radius: 14px; padding: 14px 18px;"
                                />
                                @error('email')
                                    <div class="invalid-feedback d-block">
                                        <i class="fas fa-exclamation-circle me-2"></i>{{ $message }}
                                    </div>
                                @enderror
                            </div>

                            <!-- Password Field -->
                            <div class="mb-4">
                                <label class="form-label mb-2">
                                    كلمة المرور
                                </label>
                                <div class="position-relative">
                                    <input 
                                        class="form-control form-control-lg @error('password') is-invalid @enderror" 
                                        type="password" 
                                        name="password" 
                                        autocomplete="off" 
                                        placeholder="أدخل كلمة المرور"
                                        style="border-radius: 14px; padding: 14px 18px;"
                                    />
                                    @error('password')
                                        <div class="invalid-feedback d-block">
                                            <i class="fas fa-exclamation-circle me-2"></i>{{ $message }}
                                        </div>
                                    @enderror
                                </div>
                            </div>

                            <!-- Remember Me & Forgot Password -->
                            <div class="d-flex justify-content-between align-items-center mb-4">
                                <div class="form-check">
                                    <input 
                                        class="form-check-input" 
                                        type="checkbox" 
                                        value="1" 
                                        name="remember_me" 
                                        id="remember_me" 
                                        {{ old('remember_me') ? 'checked' : '' }}
                                        style="border-radius: 6px;"
                                    />
                                    <label class="form-check-label" for="remember_me">
                                        تذكرني
                                    </label>
                                </div>
                                <a href="#" class="forgot-password-link">
                                    نسيت كلمة المرور؟
                                </a>
                            </div>

                            <!-- Login Button -->
                            <button 
                                type="submit" 
                                class="btn btn-primary btn-lg w-100 mb-3 text-white fw-bold position-relative" 
                                style="border-radius: 14px; padding: 14px; box-shadow: 0 4px 15px rgba(139, 69, 19, 0.3);"
                            >
                                <span class="indicator-label">
                                    <i class="fas fa-sign-in-alt me-2"></i>
                                    تسجيل الدخول
                                </span>
                                <span class="indicator-progress" style="display: none;">
                                    <span class="spinner-border spinner-border-sm align-middle me-2"></span>
                                    جاري تسجيل الدخول...
                                </span>
                            </button>

                            <!-- Divider -->
                            <div class="position-relative my-4">
                                <hr style="border-color: rgba(255, 255, 255, 0.2); margin: 0;">
                                <span class="position-absolute top-50 start-50 translate-middle divider-text" style="font-size: 13px; border-radius: 20px;">
                                    أو
                                </span>
                            </div>

                            <!-- Google Login Button -->
                            <a 
                                href="{{ route('auth.google') }}" 
                                class="btn-google btn-lg d-flex align-items-center justify-content-center"
                                style="border-radius: 14px; padding: 14px;"
                            >
                                <img 
                                    src="https://developers.google.com/identity/images/g-logo.png" 
                                    alt="Google" 
                                    style="width: 22px; height: 22px;border-radius: 14px;"
                                    class="me-3"
                                />
                                <!-- <span class="fw-semibold" style="color: #5f6368;">تسجيل الدخول بحساب Google</span> -->
                            </a>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

@endsection

@section('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Form submission handling
    const form = document.querySelector('form');
    const submitBtn = form.querySelector('button[type="submit"]');
    
    form.addEventListener('submit', function(e) {
        const indicator = submitBtn.querySelector('.indicator-label');
        const progress = submitBtn.querySelector('.indicator-progress');
        
        indicator.style.display = 'none';
        progress.style.display = 'inline-block';
        submitBtn.disabled = true;
    });
    
    // Add focus effects to inputs
    const inputs = document.querySelectorAll('.form-control');
    inputs.forEach(input => {
        input.addEventListener('focus', function() {
            this.parentElement.style.transform = 'scale(1.01)';
            this.parentElement.style.transition = 'transform 0.2s ease';
        });
        
        input.addEventListener('blur', function() {
            this.parentElement.style.transform = 'scale(1)';
        });
    });
});
</script>
@endsection