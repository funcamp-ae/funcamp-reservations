@extends('layouts.app')

@section('content')
@push('css')
<style>
    /* Custom styles for the reset password page */
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
    
    /* Card glass effect */
    .reset-password-card {
        backdrop-filter: blur(20px);
        background: rgba(255, 255, 255, 0.1) !important;
        border: 1px solid rgba(255, 255, 255, 0.2) !important;
        box-shadow: 0 8px 32px rgba(0, 0, 0, 0.1);
    }
    
    /* Alert styles */
    .alert {
        border-radius: 14px;
        backdrop-filter: blur(10px);
        color: white !important;
        font-weight: 500;
    }
    
    .alert-success {
        background: rgba(40, 167, 69, 0.2) !important;
        border: 2px solid rgba(40, 167, 69, 0.4);
    }
    
    .alert-danger {
        background: rgba(220, 53, 69, 0.2) !important;
        border: 2px solid rgba(220, 53, 69, 0.4);
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
    
    /* Password toggle button */
    .password-toggle {
        position: absolute;
        left: 15px;
        top: 50%;
        transform: translateY(-50%);
        background: none;
        border: none;
        color: rgba(255, 255, 255, 0.7);
        cursor: pointer;
        padding: 5px;
        transition: color 0.3s ease;
    }
    
    .password-toggle:hover {
        color: white;
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
                    <h2 class="text-white fw-bold mb-2" style="text-shadow: 0 2px 4px rgba(0, 0, 0, 0.2); font-size: 28px;">إعادة تعيين كلمة المرور</h2>
                    <p class="text-white" style="opacity: 0.85; font-size: 15px;">أدخل كلمة المرور الجديدة</p>
                </div>

                <!-- Reset Password Card -->
                <div class="card border-0 shadow-lg reset-password-card animate-entrance" style="border-radius: 24px; animation-delay: 0.1s;">
                    <div class="card-body p-4 p-lg-5">
                        <!-- Success Message -->
                        @if(session('success'))
                            <div class="alert alert-success d-flex align-items-center mb-4">
                                <i class="fas fa-check-circle me-3" style="font-size: 20px;"></i>
                                <div>{{ session('success') }}</div>
                            </div>
                        @endif

                        <!-- Error Message -->
                        @if(session('danger'))
                            <div class="alert alert-danger d-flex align-items-center mb-4">
                                <i class="fas fa-exclamation-triangle me-3" style="font-size: 20px;"></i>
                                <div>{{ session('danger') }}</div>
                            </div>
                        @endif

                        <form class="form w-100" novalidate="novalidate" method="POST" action="{{ route('password.update') }}">
                            @csrf
                            
                            <!-- Hidden Token Field -->
                            <input type="hidden" name="token" value="{{ $token }}">

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
                                    placeholder="أدخل بريدك الإلكتروني أو رقم الهاتف"
                                    style="border-radius: 14px; padding: 14px 18px;"
                                    required
                                />
                                @error('email')
                                    <div class="invalid-feedback d-block">
                                        <i class="fas fa-exclamation-circle me-2"></i>{{ $message }}
                                    </div>
                                @enderror
                            </div>

                            <!-- New Password Field -->
                            <div class="mb-4">
                                <label class="form-label mb-2">
                                    كلمة المرور الجديدة
                                </label>
                                <div class="position-relative">
                                    <input 
                                        class="form-control form-control-lg @error('password') is-invalid @enderror" 
                                        type="password" 
                                        name="password" 
                                        id="password"
                                        autocomplete="new-password" 
                                        placeholder="أدخل كلمة المرور الجديدة (8 أحرف على الأقل)"
                                        style="border-radius: 14px; padding: 14px 45px 14px 18px;"
                                        required
                                    />
                                    <button type="button" class="password-toggle" onclick="togglePassword('password')">
                                        <i class="fas fa-eye" id="password-icon"></i>
                                    </button>
                                    @error('password')
                                        <div class="invalid-feedback d-block">
                                            <i class="fas fa-exclamation-circle me-2"></i>{{ $message }}
                                        </div>
                                    @enderror
                                </div>
                            </div>

                            <!-- Confirm Password Field -->
                            <div class="mb-4">
                                <label class="form-label mb-2">
                                    تأكيد كلمة المرور
                                </label>
                                <div class="position-relative">
                                    <input 
                                        class="form-control form-control-lg @error('password_confirmation') is-invalid @enderror" 
                                        type="password" 
                                        name="password_confirmation" 
                                        id="password_confirmation"
                                        autocomplete="new-password" 
                                        placeholder="أعد إدخال كلمة المرور"
                                        style="border-radius: 14px; padding: 14px 45px 14px 18px;"
                                        required
                                    />
                                    <button type="button" class="password-toggle" onclick="togglePassword('password_confirmation')">
                                        <i class="fas fa-eye" id="password_confirmation-icon"></i>
                                    </button>
                                    @error('password_confirmation')
                                        <div class="invalid-feedback d-block">
                                            <i class="fas fa-exclamation-circle me-2"></i>{{ $message }}
                                        </div>
                                    @enderror
                                </div>
                            </div>

                            <!-- Reset Password Button -->
                            <button 
                                type="submit" 
                                class="btn btn-primary btn-lg w-100 mb-3 text-white fw-bold position-relative" 
                                style="border-radius: 14px; padding: 14px; box-shadow: 0 4px 15px rgba(139, 69, 19, 0.3);"
                            >
                                <span class="indicator-label">
                                    <i class="fas fa-key me-2"></i>
                                    إعادة تعيين كلمة المرور
                                </span>
                                <span class="indicator-progress" style="display: none;">
                                    <span class="spinner-border spinner-border-sm align-middle me-2"></span>
                                    جاري التحديث...
                                </span>
                            </button>

                            <!-- Back to Login -->
                            <div class="text-center">
                                <a href="{{ route('login') }}" class="text-white text-decoration-none">
                                    <i class="fas fa-arrow-right me-2"></i>
                                    العودة إلى تسجيل الدخول
                                </a>
                            </div>
                        </form>
                    </div>
                </div>

                <!-- Security Info -->
                <div class="text-center mt-4">
                    <p class="text-white" style="opacity: 0.8; font-size: 14px;">
                        <i class="fas fa-shield-alt me-2"></i>
                        يجب أن تتكون كلمة المرور من 8 أحرف على الأقل
                    </p>
                </div>
            </div>
        </div>
    </div>
</div>

@endsection

@section('scripts')
<script>
function togglePassword(fieldId) {
    const field = document.getElementById(fieldId);
    const icon = document.getElementById(fieldId + '-icon');
    
    if (field.type === 'password') {
        field.type = 'text';
        icon.classList.remove('fa-eye');
        icon.classList.add('fa-eye-slash');
    } else {
        field.type = 'password';
        icon.classList.remove('fa-eye-slash');
        icon.classList.add('fa-eye');
    }
}

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
    
    // Password match validation
    const password = document.getElementById('password');
    const passwordConfirmation = document.getElementById('password_confirmation');
    
    passwordConfirmation.addEventListener('input', function() {
        if (password.value !== passwordConfirmation.value) {
            passwordConfirmation.setCustomValidity('كلمات المرور غير متطابقة');
        } else {
            passwordConfirmation.setCustomValidity('');
        }
    });
});
</script>
@endsection
