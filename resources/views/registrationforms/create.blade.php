<!doctype html>
<html lang="en" id="htmlElement" dir="ltr">
<head>
    <meta charset="utf-8">
    <title>Booking Form | {{ settings('app_name_en') }}
    </title>

    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="author" content="https://www.linkedin.com/in/ahmed-elgabry-073582241/">

    {{-- Styles packages --}}
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css" rel="stylesheet">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Cairo:wght@200..1000&display=swap" rel="stylesheet">
    <link rel="shortcut icon" href="{{ asset('images/logo.png') }}" type="image/x-icon">
    <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
    <link href="https://cdn.jsdelivr.net/npm/select2-bootstrap-5-theme@1.3.0/dist/select2-bootstrap-5-theme.min.css" rel="stylesheet" />
    <link href="https://cdn.jsdelivr.net/npm/flatpickr@4.6.13/dist/flatpickr.min.css" rel="stylesheet" />
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/flag-icon-css/6.6.6/css/flag-icons.min.css">


    <style type="text/css">
        :root{
            --primary: #C4956C;
            --primary-dark: #8B6F47;
            --primary-light: #D4B896;
            --accent: #E8D5C4;
            --surface: #FFFBF5;
            --surface-2: #FFF8F0;
            --text: #3E2C1F;
            --text-muted: #7D6B5A;
            --success: #6FAF8D;
            --border: #E8DED1;
            --shadow-sm: 0 2px 8px rgba(139, 111, 71, 0.08);
            --shadow-md: 0 8px 24px rgba(139, 111, 71, 0.12);
            --shadow-lg: 0 16px 48px rgba(139, 111, 71, 0.15);
            --radius: 20px;
            --radius-sm: 12px;
        }
        
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        body {
            background: linear-gradient(135deg, #FFF8F0 0%, #b1a38c 50%, #E8DED1 100%);
            font-family: 'Poppins', 'Cairo', sans-serif;
            min-height: 100vh;
            color: var(--text);
            position: relative;
            overflow-x: hidden;
        }
        
        body::before {
            content: '';
            position: fixed;
            top: -50%;
            right: -20%;
            width: 800px;
            height: 800px;
            background: radial-gradient(circle, rgba(196, 149, 108, 0.15) 0%, transparent 70%);
            border-radius: 50%;
            animation: float 20s ease-in-out infinite;
            z-index: 0;
        }
        
        body::after {
            content: '';
            position: fixed;
            bottom: -30%;
            left: -15%;
            width: 600px;
            height: 600px;
            background: radial-gradient(circle, rgba(232, 213, 196, 0.2) 0%, transparent 70%);
            border-radius: 50%;
            animation: float 25s ease-in-out infinite reverse;
            z-index: 0;
        }
        
        @keyframes float {
            0%, 100% { transform: translate(0, 0) scale(1); }
            50% { transform: translate(30px, -30px) scale(1.1); }
        }
        
        .container-narrow { 
            max-width: 1000px; 
            margin-inline: auto; 
            position: relative;
            z-index: 1;
        }
        
        .glass {
            background: rgba(255, 251, 245, 0.85);
            backdrop-filter: blur(20px) saturate(180%);
            border: 1px solid rgba(232, 222, 209, 0.5);
            border-radius: var(--radius);
            box-shadow: var(--shadow-md);
            transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
        }
        
        .glass:hover {
            box-shadow: var(--shadow-lg);
            transform: translateY(-2px);
        }
        
        .header {
            padding: 20px 28px;
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 20px;
            border-bottom: 2px solid var(--accent);
        }
        
        .logo-section {
            display: flex;
            align-items: center;
            gap: 16px;
        }
        
        .logo {
            height: 64px;
            width: 64px;
            object-fit: cover;
            border-radius: 16px;
            box-shadow: var(--shadow-sm);
            border: 2px solid var(--accent);
        }
        
        .brand-text .company-name {
            font-weight: 700;
            font-size: 1.5rem;
            color: var(--primary-dark);
            letter-spacing: -0.02em;
        }
        
        .brand-text .tagline {
            color: var(--text-muted);
            font-size: 0.875rem;
            font-weight: 400;
        }
        
        .hero {
            padding: 32px 28px;
            border-radius: var(--radius);
            background: linear-gradient(135deg, rgba(196, 149, 108, 0.1) 0%, rgb(231 218 199) 100%);
            position: relative;
            overflow: hidden;
        }
        
        .hero::before {
            content: '';
            position: absolute;
            top: -50%;
            right: -20%;
            width: 400px;
            height: 400px;
            background: radial-gradient(circle, rgba(196, 149, 108, 0.2) 0%, transparent 70%);
            border-radius: 50%;
        }
        
        .stepper {
            display: flex;
            align-items: center;
            gap: 12px;
            flex-wrap: wrap;
            position: relative;
            z-index: 1;
        }
        
        .step {
            display: flex;
            align-items: center;
            gap: 10px;
            padding: 12px 20px;
            border-radius: 50px;
            background: rgba(255, 255, 255, 0.6);
            color: var(--text-muted);
            font-size: 0.875rem;
            font-weight: 500;
            border: 1px solid var(--border);
            transition: all 0.3s ease;
        }
        
        .step i {
            font-size: 1.1rem;
        }
        
        .progress-container {
            background: rgba(255, 255, 255, 0.6);
            border-radius: 50px;
            padding: 6px;
            border: 1px solid var(--border);
            position: relative;
            z-index: 1;
        }
        
        .progress {
            height: 32px;
            border-radius: 50px;
            overflow: hidden;
            background: rgba(232, 222, 209, 0.5);
        }
        
        .progress-bar {
            background: linear-gradient(90deg, var(--primary-light) 0%, var(--primary) 50%, var(--primary-dark) 100%);
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: 600;
            font-size: 0.875rem;
            color: white;
            transition: width 0.6s cubic-bezier(0.4, 0, 0.2, 1);
            box-shadow: inset 0 1px 2px rgba(255, 255, 255, 0.3);
        }
        
        .card-form {
            background: var(--surface);
            border-radius: var(--radius);
            padding: 36px;
            box-shadow: var(--shadow-md);
            border: 1px solid var(--border);
            transition: all 0.3s ease;
        }
        
        .card-form:hover {
            box-shadow: var(--shadow-lg);
        }
        
        .form-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 32px;
            padding-bottom: 20px;
            border-bottom: 2px solid var(--accent);
        }
        
        .form-header h4 {
            display: flex;
            align-items: center;
            gap: 12px;
            font-weight: 700;
            color: var(--primary-dark);
            margin: 0;
        }
        
        .form-header h4 i {
            font-size: 1.5rem;
            color: var(--primary);
        }
        
        .badge {
            padding: 8px 16px;
            border-radius: 50px;
            font-weight: 500;
            font-size: 0.8rem;
        }
        
        .form-section-title {
            display: flex;
            align-items: center;
            gap: 12px;
            font-weight: 600;
            font-size: 1.1rem;
            color: var(--primary-dark);
            margin: 28px 0 20px;
        }
        
        .form-section-title i {
            font-size: 1.3rem;
            color: var(--primary);
        }
        
        .divider {
            height: 2px;
            background: linear-gradient(90deg, transparent, var(--accent), transparent);
            margin: 28px 0;
        }
        
        .form-label {
            font-weight: 500;
            color: var(--text);
            margin-bottom: 8px;
            font-size: 0.95rem;
        }
        
        .required::after {
            content: " *";
            color: #D87D7D;
        }
        
        .form-control, .form-select {
            border: 2px solid var(--border);
            border-radius: var(--radius-sm);
            padding: 12px 16px;
            font-size: 0.95rem;
            transition: all 0.3s ease;
            background: white;
            color: var(--text);
        }
        
        .form-control:focus, .form-select:focus {
            border-color: var(--primary);
            box-shadow: 0 0 0 4px rgba(196, 149, 108, 0.1);
            outline: none;
        }
        
        .input-group-text {
            background: var(--surface-2);
            border: 2px solid var(--border);
            border-right: none;
            color: var(--primary);
            border-radius: var(--radius-sm) 0 0 var(--radius-sm);
        }
        
        .input-group .form-control {
            border-left: none;
            border-radius: 0 var(--radius-sm) var(--radius-sm) 0;
        }
        
        .slot-pill {
            border-radius: 50px;
            border: 2px solid var(--border);
            padding: 14px 24px;
            cursor: pointer;
            transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
            user-select: none;
            background: white;
            font-weight: 500;
            color: var(--text);
            display: flex;
            align-items: center;
            gap: 8px;
        }
        
        .slot-pill:hover {
            border-color: var(--primary-light);
            background: var(--surface-2);
            transform: translateY(-2px);
            box-shadow: var(--shadow-sm);
        }
        
        .slot-pill.active {
            background: linear-gradient(135deg, var(--primary-light) 0%, var(--primary) 100%);
            color: white;
            border-color: var(--primary);
            box-shadow: 0 8px 24px rgba(196, 149, 108, 0.3);
            transform: translateY(-2px);
        }
        
        .slot-pill input {
            display: none;
        }
        
        .btn {
            padding: 12px 28px;
            border-radius: 50px;
            font-weight: 600;
            font-size: 0.95rem;
            transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
            border: none;
            display: inline-flex;
            align-items: center;
            gap: 8px;
        }
        
        .btn-primary {
            background: linear-gradient(135deg, var(--primary-light) 0%, var(--primary) 100%);
            color: white;
            box-shadow: 0 4px 16px rgba(196, 149, 108, 0.3);
        }
        
        .btn-primary:hover {
            background: linear-gradient(135deg, var(--primary) 0%, var(--primary-dark) 100%);
            box-shadow: 0 8px 24px rgba(196, 149, 108, 0.4);
            transform: translateY(-2px);
        }
        
        .btn-outline-secondary {
            background: white;
            color: var(--text);
            border: 2px solid var(--border);
        }
        
        .btn-outline-secondary:hover {
            background: var(--surface-2);
            border-color: var(--primary-light);
            color: var(--primary-dark);
            transform: translateY(-2px);
        }
        
        .btn-outline-success {
            background: white;
            color: var(--success);
            border: 2px solid var(--success);
        }
        
        .btn-outline-success:hover {
            background: var(--success);
            color: white;
            transform: translateY(-2px);
        }
        
        .success-card {
            background: linear-gradient(135deg, rgba(111, 175, 141, 0.1) 0%, rgba(111, 175, 141, 0.6) 100%);
            border-left: 6px solid var(--success);
            border-radius: var(--radius);
            box-shadow: var(--shadow-md);
            padding: 28px;
            animation: slideInDown 0.5s ease-out;
        }
        
        @keyframes slideInDown {
            from {
                opacity: 0;
                transform: translateY(-20px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }
        
        .code-box {
            font-weight: 700;
            letter-spacing: 0.1em;
            font-size: 1.2rem;
            background: white;
            border: 2px dashed var(--success);
            padding: 12px 20px;
            border-radius: var(--radius-sm);
            color: var(--success);
            display: inline-block;
        }
        
        .form-check-input {
            width: 1.35rem;
            height: 1.35rem;
            border: 2px solid var(--border);
            border-radius: 6px;
            cursor: pointer;
            transition: all 0.3s ease;
            background-color: white;
        }
        
        .form-check-input:hover {
            border-color: var(--primary-light);
            box-shadow: 0 0 0 3px rgba(196, 149, 108, 0.1);
        }
        
        .form-check-input:focus {
            border-color: var(--primary);
            box-shadow: 0 0 0 4px rgba(196, 149, 108, 0.15);
            outline: none;
        }
        
        .form-check-input:checked {
            background-color: var(--primary);
            border-color: var(--primary);
            box-shadow: 0 2px 8px rgba(196, 149, 108, 0.3);
        }
        
        .form-check-input:checked:hover {
            background-color: var(--primary-dark);
            border-color: var(--primary-dark);
        }
        
        .form-check-label {
            cursor: pointer;
            user-select: none;
            color: var(--text);
            font-weight: 500;
        }
        
        .help {
            font-size: 0.875rem;
            color: var(--text-muted);
        }
        
        .footer-note {
            font-size: 0.875rem;
            color: var(--text-muted);
            display: flex;
            align-items: center;
            gap: 8px;
            margin-top: 24px;
            padding: 16px;
            background: var(--surface-2);
            border-radius: var(--radius-sm);
        }
        
        .alert {
            border-radius: var(--radius-sm);
            border: none;
            box-shadow: var(--shadow-sm);
        }
        
        .alert-danger {
            background: rgba(216, 125, 125, 0.6);
            color: #A44545;
            border-left: 4px solid #D87D7D;
        }
        
        .modal-content {
            border-radius: var(--radius);
            border: none;
            box-shadow: var(--shadow-lg);
        }
        
        .modal-header {
            background: linear-gradient(135deg, var(--primary-light) 0%, var(--primary) 100%);
            color: white;
            border-radius: var(--radius) var(--radius) 0 0;
            border-bottom: none;
            padding: 20px 28px;
        }
        
        .modal-body {
            padding: 28px;
        }
        
        /* Select2 Modern Styling */
        .select2-container--bootstrap-5 .select2-selection {
            border: 2px solid var(--border);
            border-radius: var(--radius-sm);
            min-height: 48px;
            background: white;
            transition: all 0.3s ease;
        }
        
        .select2-container--bootstrap-5 .select2-selection:hover {
            border-color: var(--primary-light);
            background: var(--surface-2);
        }
        
        .select2-container--bootstrap-5.select2-container--focus .select2-selection,
        .select2-container--bootstrap-5.select2-container--open .select2-selection {
            border-color: var(--primary);
            box-shadow: 0 0 0 4px rgba(196, 149, 108, 0.1);
            background: white;
        }
        
        .select2-container--bootstrap-5 .select2-selection__rendered {
            color: var(--text);
            padding-left: 16px;
            line-height: 44px;
        }
        
        .select2-container--bootstrap-5 .select2-selection__placeholder {
            color: var(--text-muted);
        }
        
        .select2-container--bootstrap-5 .select2-selection__arrow {
            height: 44px;
            right: 8px;
        }
        
        .select2-container--bootstrap-5 .select2-selection__arrow b {
            border-color: var(--primary) transparent transparent transparent;
        }
        
        .select2-container--bootstrap-5.select2-container--open .select2-selection__arrow b {
            border-color: transparent transparent var(--primary) transparent;
        }
        
        /* Select2 Dropdown */
        .select2-dropdown {
            border: 2px solid var(--border);
            border-radius: var(--radius-sm);
            box-shadow: var(--shadow-lg);
            background: white;
            overflow: hidden;
        }
        
        .select2-container--bootstrap-5 .select2-dropdown {
            border-top: none;
            border-radius: 0 0 var(--radius-sm) var(--radius-sm);
        }
        
        .select2-container--bootstrap-5.select2-container--open.select2-container--below .select2-selection {
            border-bottom-left-radius: 0;
            border-bottom-right-radius: 0;
        }
        
        .select2-container--bootstrap-5.select2-container--open.select2-container--above .select2-selection {
            border-top-left-radius: 0;
            border-top-right-radius: 0;
        }
        
        /* Select2 Search */
        .select2-search--dropdown {
            padding: 12px;
            background: var(--surface-2);
        }
        
        .select2-search--dropdown .select2-search__field {
            border: 2px solid var(--border);
            border-radius: var(--radius-sm);
            padding: 8px 12px;
            background: white;
            color: var(--text);
        }
        
        .select2-search--dropdown .select2-search__field:focus {
            border-color: var(--primary);
            outline: none;
            box-shadow: 0 0 0 3px rgba(196, 149, 108, 0.1);
        }
        
        /* Select2 Results */
        .select2-results {
            background: white;
        }
        
        .select2-results__options {
            max-height: 280px;
        }
        
        .select2-results__option {
            padding: 12px 16px;
            color: var(--text);
            transition: all 0.2s ease;
        }
        
        .select2-results__option:hover,
        .select2-results__option--highlighted {
            background: var(--surface-2) !important;
            color: var(--primary-dark) !important;
        }
        
        .select2-results__option--selected {
            background: linear-gradient(135deg, var(--primary-light) 0%, var(--primary) 100%) !important;
            color: white !important;
            font-weight: 600;
        }
        
        .select2-results__option[aria-selected="true"]:not(.select2-results__option--highlighted) {
            background: var(--accent);
            color: var(--primary-dark);
        }
        
        /* Select2 Country Flags */
        .select2-results__option .flag-icon {
            margin-right: 10px;
            border-radius: 4px;
            box-shadow: 0 1px 3px rgba(0,0,0,0.1);
        }
        
        .select2-selection__rendered .flag-icon {
            margin-right: 10px;
        }
        
        /* Country Select Dropdown Custom */
        .country-select-dropdown {
            border: 2px solid var(--border) !important;
            box-shadow: var(--shadow-lg) !important;
            border-radius: var(--radius-sm) !important;
        }
        
        @media (max-width: 768px) {
            .header {
                flex-direction: column;
                text-align: center;
            }
            
            .stepper {
                flex-direction: column;
                width: 100%;
            }
            
            .step {
                width: 100%;
                justify-content: center;
            }
            
            .card-form {
                padding: 24px;
            }
            
            .form-header {
                flex-direction: column;
                gap: 16px;
            }
        }
        
        .invalid-feedback {
            display: block;
            color: #D87D7D;
            font-size: 0.875rem;
            margin-top: 6px;
        }
        
        .is-invalid {
            border-color: #D87D7D !important;
        }
        
        .is-invalid:focus {
            box-shadow: 0 0 0 4px rgba(216, 125, 125, 0.1) !important;
        }

        .select2-container { direction: inherit; }
        .flatpickr-calendar { font-family: "Cairo", sans-serif; }
        .select2-container--open .select2-dropdown.country-select-dropdown {
            min-width: 230px !important;
        }
        .mr-2 {
            margin-right: 8px;
        }
        .country-select-wrap{
            gap: 10px;
            flex-direction: row-reverse;
        }
        .select2-container--default .select2-selection--single .select2-selection__arrow {
            height: 26px;
            position: absolute;
            top: 50%;
            right: -10px;
            width: 20px;
            transform: translateY(-50%);
        }
        .country-select-wrap .select2-container--default .select2-selection--single {
            border: none;
            background: transparent;
        }
        .country-select-wrap {
        border: 2px solid #e8ded1;
        border-radius: 10px;
        }       
        input[type="tel"] {
    border-top: none;
    border-right: none;
    border-bottom: none;
    border-radius: 10px;
    border-top-left-radius: 0px;
    border-bottom-left-radius: 0px;
    }
        
        /* Modern Link Styling */
        a {
            color: var(--primary);
            text-decoration: none;
            font-weight: 500;
            transition: all 0.3s ease;
            position: relative;
        }
        
        a:hover {
            color: var(--primary-dark);
        }
        
        a[data-bs-toggle="modal"] {
            color: var(--primary);
            font-weight: 600;
            padding: 4px 12px;
            border-radius: 20px;
            background: rgba(196, 149, 108, 0.1);
            border: 1px solid var(--border);
            transition: all 0.3s ease;
            display: inline-flex;
            align-items: center;
            gap: 6px;
        }
        
        a[data-bs-toggle="modal"]:hover {
            background: var(--primary-light);
            color: white;
            border-color: var(--primary);
            transform: translateY(-1px);
            box-shadow: 0 4px 12px rgba(196, 149, 108, 0.3);
        }
    
    </style>
</head>
<body>
<div class="container container-narrow py-3">

    {{-- Header --}}
    <div class="glass header mb-3">
        <div class="logo-section">
            <img src="{{ asset('images/logo.png') }}" class="logo" alt="Logo"
                 onerror="this.replaceWith(Object.assign(document.createElement('div'),{textContent:'Company',className:'fw-bold fs-4'}));">
            <div class="brand-text">
                <div class="company-name">{{ settings('app_name_en') }}</div>
                <div class="tagline">Book your appointment in seconds</div>
            </div>
        </div>
    </div>

    {{-- Hero --}}
    <div class="glass hero mb-3">
        <div class="d-flex flex-wrap align-items-center justify-content-between gap-2">
            <div class="stepper">
                <span class="step"><i class="bi bi-geo-alt"></i> Choose Service</span>
                <span class="step"><i class="bi bi-calendar3"></i> Date & Time</span>
                <span class="step"><i class="bi bi-person-vcard"></i> Your Details</span>
                <span class="step"><i class="bi bi-check2-circle"></i> Confirm Request</span>
            </div>
            <div class="progress-container flex-fill" style="max-width:300px">
                <div class="progress" role="progressbar" aria-label="progress">
                    <div class="progress-bar" style="width:0%" id="formProgress">0%</div>
                </div>
            </div>
        </div>
    </div>

    {{-- Success --}}
    @if (session('request_code'))
        <div class="success-card p-3 mb-3">
            <div class="d-flex flex-wrap justify-content-between align-items-start gap-3">
                <div>
                    <div class="fw-bold mb-1">Your request has been received</div>
                    <div class="mb-1">We saved your request and will contact you soon.</div>
                    <div class="mt-3">Request code:
                        <span class="code-box">{{ session('request_code') }}</span>
                    </div>
                    <div class="divider"></div>
                </div>
                <div class="d-flex flex-column align-items-stretch gap-2">
                    <button class="btn btn-outline-secondary copy-btn" data-code="{{ session('request_code') }}">
                        <i class="bi bi-clipboard"></i> Copy code
                    </button>
                    <a target="_blank" rel="noopener" class="btn btn-outline-success"
                       href="https://wa.me/?text={{ urlencode('My booking request code: '.session('request_code')) }}">
                        <i class="bi bi-whatsapp"></i> WhatsApp
                    </a>
                </div>
            </div>
        </div>
    @endif

    {{-- Errors --}}
    @if ($errors->any())
        <div class="alert alert-danger">
            <div class="mb-1"><i class="bi bi-exclamation-octagon"></i> Please fix the following:</div>
            <ul class="mb-0">
                @foreach ($errors->all() as $e)
                    <li>{{ $e }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    {{-- Form --}}
    <div class="card-form">
        <div class="form-header">
            <h4><i class="bi bi-journal-text"></i> Booking Details</h4>
            <span class="badge bg-light text-muted border">All fields marked * are required</span>
        </div>

        <form method="POST" action="{{ route('registrationforms.store') }}" novalidate id="bookingForm">
            @csrf

            {{-- Section: Camp & Date --}}
            <div class="form-section-title"><i class="bi bi-geo-alt"></i> When & Where</div>
            <div class="row g-3">

                {{-- Booking Date --}}
                <div class="col-md-6">
                    <label class="form-label required">Booking date</label>
                    <div class="input-group">
                        <span class="input-group-text"><i class="bi bi-calendar3"></i></span>
                        <input type="date" name="booking_date" class="form-control @error('booking_date') is-invalid @enderror"
                               value="{{ old('booking_date') }}" required>
                    </div>
                    @error('booking_date') <div class="invalid-feedback">{{ $message }}</div> @enderror
                </div>

                {{-- Service --}}
                <div class="col-md-6">
                    <label class="form-label required">Service / Camp</label>
                    <select name="service_id" id="service_id" class="form-select @error('service_id') is-invalid @enderror" required>
                        <option value="" disabled selected>Select a service</option>
                        @foreach($services as $s)
                            <option value="{{ $s->id }}"
                                    data-hour-from="{{ $s->hour_from }}"
                                    data-hour-to="{{ $s->hour_to }}"
                                @selected(old('service_id')==$s->id)>
                                {{ $s->name }}
                            </option>
                        @endforeach
                    </select>
                    <small id="serviceHoursHint" class="help d-block mt-1"></small>
                    @error('service_id') <div class="invalid-feedback">{{ $message }}</div> @enderror
                </div>

                {{-- Time Slot --}}
                <div class="col-12">
                    <label class="form-label required">Time slot</label>
                    <div class="d-flex gap-2 flex-wrap" id="slotGroup">
                        @foreach(['4-12','5-1','other'] as $val)
                            @php $checked = old('time_slot')===$val; @endphp
                            <label class="slot-pill @if($checked) active @endif">
                                <input type="radio" name="time_slot" value="{{ $val }}" @checked($checked) required>
                                @if($val==='4-12') <i class="bi bi-moon-stars me-1"></i> @endif
                                @if($val==='5-1')  <i class="bi bi-alarm me-1"></i> @endif
                                @if($val==='other')<i class="bi bi-sliders me-1"></i> @endif
                                @if($val==='4-12') 4 PM – 12 AM @endif
                                @if($val==='5-1') 5 PM – 1 AM @endif
                                @if($val==='other') Other (custom times) @endif
                            </label>
                        @endforeach
                    </div>
                    @error('time_slot') <div class="invalid-feedback d-block">{{ $message }}</div> @enderror
                </div>

                {{-- Other times --}}
                <div class="col-12" id="otherTimesWrap" style="display:none;">
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label required">Check-in time</label>
                            <div class="input-group">
                                <span class="input-group-text"><i class="bi bi-box-arrow-in-right"></i></span>
                                <input type="time" name="checkin_time" class="form-control @error('checkin_time') is-invalid @enderror" value="{{ old('checkin_time') }}">
                            </div>
                            @error('checkin_time') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>
                        <div class="col-md-6">
                            <label class="form-label required">Check-out time</label>
                            <div class="input-group">
                                <span class="input-group-text"><i class="bi bi-box-arrow-left"></i></span>
                                <input type="time" name="checkout_time" class="form-control @error('checkout_time') is-invalid @enderror" value="{{ old('checkout_time') }}">
                            </div>
                            @error('checkout_time') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>
                    </div>
                    <small class="help text-danger">Subject to terms and conditions regarding price differences.</small>
                </div>
            </div>

            <div class="divider"></div>

            {{-- Personal --}}
            <div class="form-section-title"><i class="bi bi-person-vcard"></i> Personal Information</div>
            <div class="row g-3">
                <div class="col-md-4">
                    <label class="form-label required">First name</label>
                    <input type="text" name="first_name" class="form-control @error('first_name') is-invalid @enderror"
                           value="{{ old('first_name') }}" required>
                    @error('first_name') <div class="invalid-feedback">{{ $message }}</div> @enderror
                </div>
                <div class="col-md-4">
                    <label class="form-label required">Last name</label>
                    <input type="text" name="last_name" class="form-control @error('last_name') is-invalid @enderror"
                           value="{{ old('last_name') }}" required>
                    @error('last_name') <div class="invalid-feedback">{{ $message }}</div> @enderror
                </div>
                <div class="col-md-4">
                    <label class="form-label required">Number of persons</label>
                    <input type="number" min="1" name="persons" class="form-control @error('persons') is-invalid @enderror"
                           value="{{ old('persons') }}" required>
                    @error('persons') <div class="invalid-feedback">{{ $message }}</div> @enderror
                </div>

                <div class="col-md-6">
                    <label class="form-label required">Mobile phone</label>
                    <input type="tel" name="mobile_phone" class="form-control @error('mobile_phone') is-invalid @enderror"
                           placeholder="e.g., +971010123467" value="{{ old('mobile_phone') }}" required>
                    @error('mobile_phone') <div class="invalid-feedback">{{ $message }}</div> @enderror
                </div>

                <div class="col-md-6">
                    <label class="form-label required">Email</label>
                    <div class="input-group">
                        <span class="input-group-text"><i class="bi bi-envelope"></i></span>
                        <input type="email" name="email" class="form-control @error('email') is-invalid @enderror"
                               placeholder="exmple@exmple.com" value="{{ old('email') }}" required>
                    </div>
                    @error('email') <div class="invalid-feedback">{{ $message }}</div> @enderror
                </div>

                <div class="col-12">
                    <label class="form-label">Notes</label>
                    <textarea name="notes" class="form-control @error('notes') is-invalid @enderror" rows="3"
                              placeholder="Any special requests or notes">{{ old('notes') }}</textarea>
                    @error('notes') <div class="invalid-feedback">{{ $message }}</div> @enderror
                    <div class="help mt-1" id="notesCount"></div>
                </div>

                <div class="col-12 form-check mt-2 d-flex align-items-center justify-content-start gap-2">
                    <input class="form-check-input @error('terms_accepted') is-invalid @enderror" type="checkbox" id="terms" name="terms_accepted" value="1" {{ old('terms_accepted') ? 'checked' : '' }} required>
                    <label class="form-check-label" for="terms">I agree to the terms and conditions</label>
                    <a href="#" data-bs-toggle="modal" data-bs-target="#termsModal" class="ms-2">View terms</a>
                    @error('terms_accepted') <div class="invalid-feedback">{{ $message }}</div> @enderror
                </div>

                <div class="col-12 d-flex gap-2 mt-3">
                    <button type="submit" class="btn btn-primary px-4">
                        <i class="bi bi-send"></i> Submit request
                    </button>
                    <button type="reset" class="btn btn-outline-secondary">
                        <i class="bi bi-arrow-counterclockwise"></i> Reset
                    </button>
                </div>
            </div>

            <div class="footer-note mt-3">
                <i class="bi bi-info-circle"></i> We respect your privacy. Your data is used only to process this request.
            </div>
        </form>
    </div>
</div>

{{-- Terms Modal --}}
<div class="modal fade" id="termsModal" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog modal-dialog-scrollable">
    <div class="modal-content">
      <div class="modal-header">
        <h6 class="modal-title">Terms and Conditions</h6>
      </div>
      <div class="modal-body">
        <div class="rtl-text" dir="rtl" style="text-align: right;">
            {!! (\App\Models\TermsSittng::first()?->commercial_license_ar) !!}
        </div>
        <div class="ltr-text" dir="ltr" style="text-align: left;">
            {!! (\App\Models\TermsSittng::first()?->commercial_license_en) !!}
        </div>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
      </div>
    </div>
  </div>
</div>

{{-- Style packages --}}
<script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/flatpickr@4.6.13/dist/flatpickr.min.js"></script>
<script src="{{ asset('js/country-select.js') }}"></script>


<script>
	// phone handling moved to country-select.js (Select2 based)
	</script>
<script type="text/javascript">
    (function(){
        function syncSlots(){
            document.querySelectorAll('#slotGroup .slot-pill').forEach(lbl=>{
                const input = lbl.querySelector('input');
                lbl.classList.toggle('active', input.checked);
            });
            const selected = document.querySelector('#slotGroup input[name="time_slot"]:checked');
            document.getElementById('otherTimesWrap').style.display = (selected && selected.value==='other') ? 'block':'none';
        }
        document.querySelectorAll('#slotGroup input[name="time_slot"]').forEach(r=>r.addEventListener('change', syncSlots));
        syncSlots();

        const sel = document.getElementById('service_id');
        const hint = document.getElementById('serviceHoursHint');
        function updateHint(){
            const opt = sel.options[sel.selectedIndex];
            const from = opt?.dataset?.hourFrom || '';
            const to   = opt?.dataset?.hourTo || '';
            hint.textContent = (from && to) ? `Service hours: ${from} - ${to}` : '';
        }
        sel.addEventListener('change', updateHint); updateHint();

        document.querySelectorAll('.copy-btn').forEach(btn=>{
            btn.addEventListener('click', ()=>{
                const code = btn.getAttribute('data-code');
                navigator.clipboard.writeText(code).then(()=>{
                    btn.innerHTML = '<i class="bi bi-check2"></i> Copied';
                    setTimeout(()=>btn.innerHTML = '<i class="bi bi-clipboard"></i> Copy code',1400);
                });
            });
        });

        const notes = document.querySelector('textarea[name="notes"]');
        const notesCount = document.getElementById('notesCount');
        if(notes){
            const max = 500; notes.setAttribute('maxlength', max);
            const sync = ()=> notesCount.textContent = notes.value.length + ' / ' + max;
            notes.addEventListener('input', sync); sync();
        }

        const requiredSelectors = [
            'select[name="service_id"]',
            'input[name="booking_date"]',
            'input[name="time_slot"]',
            'input[name="persons"]',
            'input[name="first_name"]',
            'input[name="last_name"]',
            'input[name="mobile_phone"]',
            'input[name="email"]',
            'input[name="terms_accepted"]'
        ];
        const bar = document.getElementById('formProgress');
        function updateProgress(){
            let total = requiredSelectors.length;
            let done = 0;
            const ts = document.querySelector('input[name="time_slot"]:checked');
            requiredSelectors.forEach(sel=>{
                const el = (sel==='input[name="time_slot"]') ? ts : document.querySelector(sel);
                if(!el) return;
                if(el.type==='checkbox') done += el.checked ? 1 : 0;
                else if(el.tagName==='SELECT') done += el.value ? 1 : 0;
                else done += el.value?.trim() ? 1 : 0;
            });
            if(ts && ts.value==='other'){
                if(document.querySelector('input[name="checkin_time"]').value) done++; else total++;
                if(document.querySelector('input[name="checkout_time"]').value) done++; else total++;
            }
            const pct = Math.max(0, Math.min(100, Math.round(100*done/total)));
            bar.style.width = pct+'%'; bar.textContent = pct+'%';
        }
        document.getElementById('bookingForm').addEventListener('input', updateProgress);
        updateProgress();

        // Phone merging is handled by country-select.js which prefixes the selected dial code

        const placeholder = "Select a service";
        $('#service_id').select2({
            theme: 'bootstrap-5',
            dir: 'ltr',
            placeholder: placeholder,
            allowClear: true,
            width: '100%',
            dropdownParent: $('.card-form')
        }).on('change', function(){
            updateHint();
            document.getElementById('bookingForm').dispatchEvent(new Event('input', { bubbles: true }));
        });

        const dateEl = document.querySelector('input[name="booking_date"]');
        if(dateEl){
            try { dateEl.type = 'text'; } catch(e) {}
            const opts = {
                dateFormat: 'Y-m-d',
                altInput: true,
                altFormat: 'F j, Y',
                minDate: 'today',
                disableMobile: true,
                locale: flatpickr.l10ns.default,
                onChange: function(){
                    document.getElementById('bookingForm').dispatchEvent(new Event('input', { bubbles: true }));
                }
            };
            flatpickr(dateEl, opts);
        }
    })();

</script>
</body>
</html>
