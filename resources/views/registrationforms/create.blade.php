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
            --radius: 16px;
            --shadow: 0 12px 32px rgba(0,0,0,.08);
            --brand: #B98220;
            --brand-2:#6A3D1C;
            --success: #20c997;
            --surface:#ffffff;
            --muted:#6c757d;
            --bg: linear-gradient(180deg,#f7f9fc 0%,#eef2f7 100%);
        }
        body{
            background:
              radial-gradient(800px 400px at 15% -10%, rgba(13,110,253,.08), transparent 40%),
              radial-gradient(700px 350px at 85% -15%, rgba(102,16,242,.08), transparent 45%),
              var(--bg);
            font-family: "Cairo", sans-serif;
            min-height: 100dvh;
        }
        .container-narrow{ max-width: 980px; margin-inline:auto; }
        .glass{
            border-radius: var(--radius);
            background: rgba(255,255,255,.9);
            border: 1px solid rgba(255,255,255,.6);
            box-shadow: var(--shadow);
            backdrop-filter: blur(6px);
        }
        .header{ padding: 16px 18px; display:flex; align-items:center; justify-content:space-between; gap:16px; }
        .logo{ height:54px; object-fit:contain; }
        .lang-switch .btn{ border-radius:999px }
        .hero{ position: relative; overflow:hidden; border-radius: var(--radius); padding: 18px 20px; }
        .hero::before{ content:""; position:absolute; inset:0; background: radial-gradient(600px 220px at 10% 20%, rgba(13,110,253,.07), transparent 50%), radial-gradient(500px 200px at 90% 0%, rgba(102,16,242,.07), transparent 55%); pointer-events:none; }
        .stepper{ display:flex; align-items:center; gap:10px; }
        .step{ display:flex; align-items:center; gap:8px; padding:.4rem .7rem; border-radius:999px; background:#f1f3f5; color:#495057; font-size:.9rem; }
        .step i{ font-size:1rem; }
        .progress-wrap{ background:#f8f9fa; border-radius:999px; padding:6px; }
        .progress-bar{ background: linear-gradient(90deg, var(--brand), var(--brand-2)); }
        .card-form{ border-radius: var(--radius); background: var(--surface); box-shadow: var(--shadow); padding: 20px; border: 1px solid #f1f3f5; }
        .required::after{ content:" *"; color:#d63384; }
        .slot-pill{ border-radius: 999px; border:1px solid #ced4da; padding:.45rem .9rem; cursor: pointer; transition: all .2s ease; user-select: none; }
        .slot-pill.active{ background: linear-gradient(90deg, var(--brand), var(--brand-2)); color:#fff; border-color: transparent; box-shadow: 0 6px 18px rgba(13,110,253,.25); }
        .slot-pill input{ display:none; }
        .help{ font-size:.9rem; color:var(--muted); }
        .success-card{ background: #eefaf3; border-left:6px solid var(--success); border-radius: var(--radius); box-shadow: var(--shadow); }
        .code-box{ font-weight:700; letter-spacing:.08em; font-size:1.15rem; background:#fff; border:1px dashed #a6e9d5; padding:.4rem .7rem; border-radius:10px; }
        .copy-btn{ white-space: nowrap; }
        .input-group-text i{ opacity:.8 }
        .form-section-title{ font-weight:600; display:flex; align-items:center; gap:8px; margin: 6px 0 8px; }
        .divider{ height:1px; background:linear-gradient(90deg,transparent,#e9ecef,transparent); margin: 8px 0 12px; }
        .footer-note{ font-size:.9rem; color:var(--muted); }
        .invalid-feedback{ display:block }
        .select2-container { direction: inherit; }
        .select2-container--bootstrap-5 .select2-selection { border-radius: .375rem; }
        .flatpickr-calendar { font-family: "Cairo", sans-serif; }
        .select2-container--open .select2-dropdown.country-select-dropdown  {
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
	</style>
</head>
<body>
<div class="container container-narrow py-3">

    {{-- Header --}}
    <div class="glass header mb-3">
        <div class="d-flex align-items-center gap-3">
            <img src="{{ asset('images/logo.png') }}" class="logo" alt="Logo"
                 onerror="this.replaceWith(Object.assign(document.createElement('div'),{textContent:'Company',className:'fw-bold fs-4'}));">
            <div>
                <div class="fw-bold">{{ settings('app_name_en') }}</div>
                <div class="text-muted">Book your appointment in seconds</div>
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
            <div class="progress-wrap flex-fill" style="max-width:300px">
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
        <div class="d-flex justify-content-between align-items-center mb-2">
            <h4 class="mb-0"><i class="bi bi-journal-text"></i> Booking Details</h4>
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
                    <small class="help text-danger">Custom times must be within the default service hours.</small>
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

                <div class="col-12 form-check mt-2">
                    <input class=" @error('terms_accepted') is-invalid @enderror" type="checkbox" id="terms" name="terms_accepted" value="1" {{ old('terms_accepted') ? 'checked' : '' }} required>
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
