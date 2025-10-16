@extends('dashboard.layouts.app')
@section('pageTitle', __('dashboard.my_tasks'))

@section('content')
    <div class="card card-flush shadow-sm">
        <div class="card-header border-0 pt-6">
            <h3 class="card-title align-items-start flex-column">
                <span class="card-label fw-bold fs-3 mb-1">
                    @lang('dashboard.my_tasks')
                </span>
            </h3>
            <div class="card-toolbar">
                <a href="{{ url()->previous() }}" class="btn btn-light-primary">
                    <i class="fas fa-arrow-left me-2"></i> @lang('dashboard.back_to_list')
                </a>
            </div>
        </div>

        <div class="card-body pt-0">
            <div class="table-responsive">
                <table class="table align-middle table-row-dashed fs-6 gy-5" id="kt_employee_tasks_table">
                    <thead>
                    <tr class="text-start fw-bolder fs-7 text-uppercase gs-0">
                        <th>@lang('dashboard.title')</th>
                        <th>@lang('dashboard.creator')</th>
                        <th>@lang('dashboard.due_date')</th>
                        <th>@lang('dashboard.task_status')</th>
                        <th>@lang('dashboard.actions')</th>
                    </tr>
                    </thead>
                    <tbody>
                    @foreach ($tasks as $task)
                        <tr>
                            <td>@translate($task->title)</td>
                            <td>{{ $task->creator->name }}</td>
                            <td>{{ $task->due_date->format('Y-m-d') }}</td>
                            <td>
                                <span class="badge badge-light-{{ $task->status == 'completed' ? 'success' : ($task->status == 'failed' ? 'danger' : ($task->status == 'in_progress' ? 'info' : 'warning')) }}">
                                    {{ str_replace('_', ' ', ucfirst(__('dashboard.' . $task->status))) }}
                                </span>
                            </td>
                            <td>
                                <button class="btn btn-sm btn-primary view-task"
                                        data-task='@json($task)'
                                        data-notification-id="{{ $task->notifications->first()->id ?? '' }}">
                                    @lang('dashboard.view')
                                </button>
                            </td>
                        </tr>
                    @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- Task Modal -->
    <div class="modal fade" id="taskModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="taskTitle"></h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>

                <div class="modal-body">
                    <div class="row mb-5">
                        <div class="col-md-6">
                            <p><strong>@lang('dashboard.description'):</strong></p>
                            <p id="taskDescription"></p>
                            <p><strong>@lang('dashboard.due_date'):</strong></p>
                            <p id="taskDueDate"></p>
                        </div>
                        <div class="col-md-6">
                            <p><strong>@lang('dashboard.priority'):</strong></p>
                            <p id="taskPriority"></p>
                            <p><strong>@lang('dashboard.created_at'):</strong></p>
                            <p id="taskCreatedAt"></p>
                        </div>
                    </div>

                    <form id="statusForm">
                        @csrf
                        <input type="hidden" name="task_id" id="taskId">

                        <div class="row g-5">
                            <div class="col-md-6">
                                <label class="form-label">@lang('dashboard.status')</label>
                                <select name="status" class="form-select form-select-solid" id="taskStatus">
                                    <option value="pending">@lang('dashboard.pending')</option>
                                    <option value="in_progress">@lang('dashboard.in_progress')</option>
                                    <option value="completed">@lang('dashboard.completed')</option>
                                    <option value="failed">@lang('dashboard.failed')</option>
                                </select>
                            </div>
                        </div>

                        <div class="row g-5 mt-3" id="failureReasonContainer" style="display:none;">
                            <div class="col-md-12">
                                <label class="form-label">@lang('dashboard.failure_reason')</label>
                                <textarea name="failure_reason" class="form-control form-control-solid" rows="3"></textarea>
                            </div>
                        </div>

                        <div class="row g-4 mt-5">
                            {{-- PHOTOS --}}
                            <div class="col-md-4">
                                <label class="form-label">@lang('dashboard.photo_attachment')</label>
                                <div class="media-upload-container photo-box" data-existing="[]">
                                    <input type="hidden" name="remove_photos" value="0" class="remove-flag">

                                    <div class="photo-slider mb-2 d-none">
                                        <div class="slider-viewport"><img class="slider-img" alt="photo"></div>
                                        <button type="button" class="slider-nav prev" aria-label="Prev">‹</button>
                                        <button type="button" class="slider-nav next" aria-label="Next">›</button>
                                        <div class="slider-dots"></div>
                                    </div>

                                    <div class="media-actions">
                                        <button type="button" class="btn btn-sm btn-brown capture-media">
                                            <i class="bi bi-camera"></i> @lang('dashboard.capture_photo')
                                        </button>
                                        <button type="button" class="btn btn-sm btn-secondary upload-media">
                                            <i class="bi bi-upload"></i> @lang('dashboard.upload')
                                        </button>
                                        <button type="button" class="btn btn-sm btn-danger remove-media-all">
                                            <i class="bi bi-trash"></i> @lang('dashboard.delete')
                                        </button>
                                    </div>

                                    <input type="file" name="photos[]" class="media-input d-none" accept="image/*" multiple>
                                    <input type="file" class="d-none camera-photo-input" accept="image/*" capture="environment" multiple>
                                </div>
                            </div>

                            {{-- AUDIOS --}}
                            <div class="col-md-4">
                                <label class="form-label">@lang('dashboard.audio_attachment')</label>
                                <div class="media-upload-container audio-box" data-existing="[]">
                                    <input type="hidden" name="remove_audios" value="0" class="remove-flag">

                                    <div class="preview-audio-container mb-2" style="display:none;">
                                        <div class="preview-audio-list"></div>
                                    </div>

                                    <div class="media-actions">
                                        <button type="button" class="btn btn-sm btn-brown capture-media">
                                            <i class="bi bi-mic"></i> @lang('dashboard.record')
                                        </button>
                                        <button type="button" class="btn btn-sm btn-secondary upload-media">
                                            <i class="bi bi-upload"></i> @lang('dashboard.upload')
                                        </button>
                                        <button type="button" class="btn btn-sm btn-danger remove-media">
                                            <i class="bi bi-trash"></i> @lang('dashboard.delete')
                                        </button>
                                    </div>

                                    <input type="file" name="audios[]" class="media-input d-none" accept="audio/*" multiple>
                                </div>
                            </div>

                            {{-- VIDEOS (FilePond) --}}
                            <div class="col-md-4">
                                <label class="form-label">@lang('dashboard.video_attachment')</label>
                                <div class="media-upload-container video-box" data-existing="[]">
                                    <input type="hidden" name="remove_videos" value="0" class="remove-flag">

                                    <div class="video-slider mb-2 d-none">
                                        <div class="slider-viewport">
                                            <video class="slider-video" controls playsinline></video>
                                        </div>
                                        <button type="button" class="slider-nav prev" aria-label="Prev">‹</button>
                                        <button type="button" class="slider-nav next" aria-label="Next">›</button>
                                        <div class="slider-dots"></div>
                                    </div>

                                    <div class="media-actions">
                                        <button type="button" class="btn btn-sm btn-brown capture-media">
                                            🎥 @lang('dashboard.start_recording_video')
                                        </button>

                                        <input type="file" class="pond-video" accept="video/*" multiple />

                                        <button type="button" class="btn btn-sm btn-danger remove-video-all">
                                            <i class="bi bi-trash"></i> @lang('dashboard.delete')
                                        </button>
                                    </div>

                                    <div class="uploaded-keys"></div>

                                    <input type="file" class="media-input d-none" accept="video/*" multiple>
                                    <input type="file" class="d-none camera-video-input" accept="video/*" capture="environment" multiple>

                                    <div class="text-danger small d-none video-error"></div>
                                </div>
                            </div>
                        </div>

                        <div class="mt-5 d-flex justify-content-end">
                            <button type="button" class="btn btn-light" data-bs-dismiss="modal">@lang('dashboard.cancel')</button>
                            <button type="button" class="btn btn-primary ms-2" id="saveStatus">@lang('dashboard.save_changes')</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    {{-- Lightbox الصور (مرة واحدة) --}}
    <div id="photo-lightbox" class="lightbox d-none" aria-hidden="true">
        <button type="button" class="close" aria-label="Close">&times;</button>
        <button type="button" class="nav prev" aria-label="Prev">‹</button>
        <img class="lightbox-img" alt="lightbox">
        <button type="button" class="nav next" aria-label="Next">›</button>
    </div>
@endsection


@push('css')
    <link href="https://unpkg.com/filepond/dist/filepond.min.css" rel="stylesheet">
    <style>
        /* زر بني موحّد */
        .btn-brown{background:#8B5E3C;border-color:#8B5E3C;color:#fff}
        .btn-brown:hover{filter:brightness(.95);color:#fff}

        /* أزرار الوسائط بشكل عمودي وبمقاس موحّد */
        .media-actions{display:grid;gap:.5rem;grid-template-columns:1fr;margin-bottom:.5rem}
        .media-actions .btn{width:100%;height:42px;display:flex;align-items:center;justify-content:center;gap:.4rem}
        .media-actions .btn i{margin-inline-end:.35rem}
        [dir="rtl"] .media-actions .btn i{margin-inline-end:0;margin-inline-start:.35rem}

        .media-upload-container{border:1px dashed #ddd;border-radius:4px;padding:10px}
        .preview-audio-list audio{display:block;width:100%;margin-top:.25rem}

        /* PHOTO SLIDER */
        .photo-slider{position:relative;border:1px solid #ddd;border-radius:8px;overflow:hidden;box-shadow:0 2px 6px rgba(0,0,0,.06)}
        .photo-slider .slider-viewport{height:220px;display:flex;align-items:center;justify-content:center;background:#111}
        .photo-slider .slider-img{max-width:100%;max-height:100%;display:block;object-fit:contain;-webkit-user-drag:none;user-select:none}

        .photo-slider .slider-nav,
        .video-slider .slider-nav{
            position:absolute;top:50%;transform:translateY(-50%);
            border:0;width:36px;height:36px;border-radius:50%;
            background:rgba(0,0,0,.45);color:#fff;font-size:18px;
            display:flex;align-items:center;justify-content:center;cursor:pointer
        }
        .photo-slider .slider-nav:hover,
        .video-slider .slider-nav:hover{background:rgba(0,0,0,.55)}

        .photo-slider .prev,.video-slider .prev{left:8px}
        .photo-slider .next,.video-slider .next{right:8px}

        /* نقاط السلايدر: مرئية دائمًا + تفاعل */
        .photo-slider .slider-dots,
        .video-slider .slider-dots{
            display:flex;justify-content:center;gap:8px;padding:8px
        }
        .photo-slider .slider-dot,
        .video-slider .slider-dot{
            width:10px;height:10px;border-radius:50%;
            background:rgba(255,255,255,.25);
            border:2px solid rgba(255,255,255,.9);
            box-shadow:0 0 0 2px rgba(0,0,0,.25);
            transition:transform .15s ease, background .15s ease, border-color .15s ease;
            cursor:pointer
        }
        .photo-slider .slider-dot:hover,.video-slider .slider-dot:hover,
        .photo-slider .slider-dot:focus-visible,.video-slider .slider-dot:focus-visible{
            background:#fff;border-color:#fff;transform:scale(1.15);outline:none
        }
        .photo-slider .slider-dot.active,
        .video-slider .slider-dot.active{
            background:#fff;border-color:#fff;transform:scale(1.25)
        }

        /* VIDEO SLIDER */
        .video-slider{position:relative;border:1px solid #ddd;border-radius:8px;overflow:hidden;box-shadow:0 2px 6px rgba(0,0,0,.06)}
        .video-slider .slider-viewport{height:220px;display:flex;align-items:center;justify-content:center;background:#000}
        .video-slider .slider-video{width:100%;max-height:100%;object-fit:contain;background:#000}

        /* LIGHTBOX */
        .lightbox{position:fixed;inset:0;background:rgba(0,0,0,.9);display:flex;align-items:center;justify-content:center;z-index:9999}
        .lightbox.d-none{display:none}
        .lightbox img{max-width:90%;max-height:90%;object-fit:contain}
        .lightbox .close{position:absolute;top:20px;right:30px;font-size:36px;color:#fff;cursor:pointer}
        .lightbox .nav{position:absolute;top:50%;transform:translateY(-50%);font-size:48px;color:#fff;background:transparent;border:none;cursor:pointer}
        .lightbox .nav.prev{left:20px}
        .lightbox .nav.next{right:20px}

        /* RTL (مطابق لباقي الصفحات) */
        [dir="rtl"] .lightbox .nav.prev{right:auto;left:20px}
        [dir="rtl"] .lightbox .nav.next{left:auto;right:20px}
        [dir="rtl"] .photo-slider .prev,
        [dir="rtl"] .video-slider .prev,
        [dir="rtl"] .lightbox .nav.prev{right:8px;left:auto;transform:translateY(-50%) scaleX(-1)}
        [dir="rtl"] .photo-slider .next,
        [dir="rtl"] .video-slider .next,
        [dir="rtl"] .lightbox .nav.next{left:8px;right:auto;transform:translateY(-50%) scaleX(-1)}

        /* زر إغلاق بسيط لتسجيل الفيديو داخل المعاينة */
        .record-close{
            position:absolute;top:10px;right:10px;width:36px;height:36px;border-radius:50%;
            background:rgba(0,0,0,.35);color:#fff;border:1px solid rgba(255,255,255,.3);
            display:flex;align-items:center;justify-content:center;cursor:pointer;
            line-height:1;font-size:20px
        }
        [dir="rtl"] .record-close{right:auto;left:10px}
    </style>
@endpush


@push('js')
    <script src="https://unpkg.com/filepond/dist/filepond.min.js"></script>
    <script src="https://unpkg.com/filepond-plugin-file-validate-type/dist/filepond-plugin-file-validate-type.min.js"></script>
    <script src="https://unpkg.com/filepond-plugin-file-validate-size/dist/filepond-plugin-file-validate-size.min.js"></script>

    <script>
        document.addEventListener('DOMContentLoaded', () => {
            const CSRF = '{{ csrf_token() }}';
            const CHUNK_SIZE = 10 * 1024 * 1024; // 10MB
            const isMobile = /Mobi|Android|iPhone|iPad/i.test(navigator.userAgent);
            const ACCEPTED_VIDEOS = [
                'video/mp4','video/quicktime','video/hevc','video/webm','video/avi',
                'video/x-matroska','video/x-flv','video/3gpp','video/3gpp2','video/mpeg',
                'video/mpg','video/x-ms-wmv','video/x-m4v'
            ];

            const createHidden = (name, val) => { const i=document.createElement('input'); i.type='hidden'; i.name=name; i.value=val; return i; };
            const isRTL = () => document.documentElement.dir === 'rtl' || getComputedStyle(document.body).direction === 'rtl';

            const inferMime = (file) => {
                let t = (file?.type || '').split(';')[0].trim();
                if (!t) {
                    const ext = (file?.name || '').split('.').pop()?.toLowerCase();
                    const map = { mp4:'video/mp4', m4v:'video/x-m4v', mov:'video/quicktime', webm:'video/webm',
                                  mkv:'video/x-matroska', avi:'video/avi', flv:'video/x-flv', mpg:'video/mpeg',
                                  mpeg:'video/mpeg', '3gp':'video/3gpp', '3gpp':'video/3gpp', '3gpp2':'video/3gpp2', hevc:'video/hevc' };
                    t = map[ext] || 'video/mp4';
                }
                return t;
            };
            const cleanMime = (type) => (type || '').split(';')[0].trim() || 'video/webm';

            /* ===== PHOTO BOX ===== */
            function initPhotoBox(box) {
                const uploadBtn = box.querySelector('.upload-media');
                const captureBtn = box.querySelector('.capture-media');
                const removeBtnAll = box.querySelector('.remove-media-all');
                const input = box.querySelector('.media-input');
                const camInput = box.querySelector('.camera-photo-input');
                const removeFlg = box.querySelector('.remove-flag');

                const slider = box.querySelector('.photo-slider');
                const viewport = slider.querySelector('.slider-viewport');
                const imgEl = slider.querySelector('.slider-img');
                const prevBtn = slider.querySelector('.prev');
                const nextBtn = slider.querySelector('.next');
                const dotsBox = slider.querySelector('.slider-dots');
                prevBtn.innerHTML = isRTL() ? '›' : '‹';
                nextBtn.innerHTML = isRTL() ? '‹' : '›';

                const lb = document.getElementById('photo-lightbox');
                const lbImg = lb?.querySelector('.lightbox-img');
                const lbClose = lb?.querySelector('.close');
                const lbPrev = lb?.querySelector('.nav.prev');
                const lbNext = lb?.querySelector('.nav.next');

                let photos = (() => { try { const j=JSON.parse(box.dataset.existing||'[]'); return Array.isArray(j)?j:[]; } catch { return []; } })();
                let current = 0;
                let fileState = [];

                function syncInput(){ const dt=new DataTransfer(); fileState.forEach(f=>dt.items.add(f)); input.files=dt.files; }
                const fileKey = f => `${f.name}__${f.size}__${f.lastModified||0}__${f.type}`;
                const addFileUnique = f => { const k=fileKey(f); if (fileState.some(x=>fileKey(x)===k)) return false; fileState.push(f); syncInput(); return true; };

                function renderDots(){ dotsBox.innerHTML=''; photos.forEach((_,i)=>{ const d=document.createElement('div'); d.className='slider-dot'+(i===current?' active':''); d.addEventListener('click',()=>{current=i; render();}); dotsBox.appendChild(d); }); }
                function render(){ if(!photos.length){ slider.classList.add('d-none'); imgEl.removeAttribute('src'); return; } slider.classList.remove('d-none'); imgEl.src=photos[current]; renderDots(); }

                // Lightbox
                if (lb && lbImg && lbClose && lbPrev && lbNext){
                    imgEl.addEventListener('click', ()=>{ if(!photos.length) return; lb.classList.remove('d-none'); lbImg.src=photos[current]; });
                    lbClose.addEventListener('click', ()=> lb.classList.add('d-none'));
                    lbPrev.addEventListener('click', ()=>{ current=(current-1+photos.length)%photos.length; lbImg.src=photos[current]; });
                    lbNext.addEventListener('click', ()=>{ current=(current+1)%photos.length; lbImg.src=photos[current]; });
                    document.addEventListener('keydown', e=>{ if(lb.classList.contains('d-none')) return; if(e.key==='Escape') lb.classList.add('d-none'); if(e.key==='ArrowLeft') lbPrev.click(); if(e.key==='ArrowRight') lbNext.click(); });
                }

                // swipe
                (function(){ let sx=0; viewport.addEventListener('touchstart', e=> sx=e.changedTouches[0].clientX, {passive:true}); viewport.addEventListener('touchend', e=>{ const dx=e.changedTouches[0].clientX-sx; if(Math.abs(dx)>40) (dx<0?nextBtn.click():prevBtn.click()); }, {passive:true}); })();

                prevBtn.addEventListener('click', ()=>{ if(!photos.length) return; current=(current-1+photos.length)%photos.length; render(); });
                nextBtn.addEventListener('click', ()=>{ if(!photos.length) return; current=(current+1)%photos.length; render(); });

                // compress
                function compressImage(file, quality=.7, maxW=1280, maxH=1280){
                    return new Promise((resolve,reject)=>{
                        const img=new Image(); const url=URL.createObjectURL(file);
                        img.onload=()=>{
                            let {width,height}=img;
                            if (width>maxW || height>maxH){ const s=Math.min(maxW/width,maxH/height); width=Math.round(width*s); height=Math.round(height*s); }
                            const canvas=document.createElement('canvas'); canvas.width=width; canvas.height=height;
                            canvas.getContext('2d').drawImage(img,0,0,width,height);
                            canvas.toBlob(b=>{ URL.revokeObjectURL(url); if(!b) return reject('Compression failed'); const name=(file.name||'photo').replace(/\.[^.]+$/,'')+'.jpg'; resolve(new File([b],name,{type:'image/jpeg'})); },'image/jpeg',quality);
                        };
                        img.onerror=()=>{ URL.revokeObjectURL(url); reject('Decode failed'); };
                        img.src=url;
                    });
                }
                const addPhotoUrl = (u)=>{ photos.push(u); current=photos.length-1; render(); };

                // upload
                uploadBtn?.addEventListener('click', ()=> input?.click());
                input?.addEventListener('change', async e=>{
                    for (const f of Array.from(e.target.files||[])){ let ff=f; try{ ff=await compressImage(f,.7);}catch{} if(addFileUnique(ff)) addPhotoUrl(URL.createObjectURL(ff)); }
                    if (removeFlg) removeFlg.value='0'; e.target.value='';
                });

                // camera (mobile)
                camInput?.addEventListener('change', async e=>{
                    for (const f of Array.from(e.target.files||[])){ let ff=f; try{ ff=await compressImage(f,.7);}catch{} if(addFileUnique(ff)) addPhotoUrl(URL.createObjectURL(ff)); }
                    if (removeFlg) removeFlg.value='0'; e.target.value='';
                });

                // desktop capture (صورة) — بدون عدّاد مخصّص
                captureBtn?.addEventListener('click', ()=>{
                    if (camInput && isMobile){ camInput.click(); return; }
                    (async ()=>{
                        try{
                            const stream = await navigator.mediaDevices.getUserMedia({ video:{facingMode:{ideal:'environment'}, width:{ideal:1280}, height:{ideal:720}}, audio:false });
                            const overlay=document.createElement('div'); overlay.style.cssText='position:absolute;inset:0;background:#000;display:flex;align-items:center;justify-content:center;z-index:5;';
                            const v=document.createElement('video'); v.autoplay=v.muted=true; v.playsInline=true; v.srcObject=stream; v.style.cssText='width:100%;height:100%;object-fit:contain;';
                            const snap=document.createElement('button'); snap.type='button'; snap.className='btn btn-success btn-sm'; snap.textContent='Snap'; Object.assign(snap.style,{position:'absolute',bottom:'12px',left:'50%',transform:'translateX(-50%)'});
                            slider.classList.remove('d-none'); viewport.appendChild(overlay); overlay.appendChild(v); overlay.appendChild(snap);

                            const close=document.createElement('button'); close.type='button'; close.className='record-close'; close.setAttribute('aria-label','Close'); close.innerHTML='&times;'; overlay.appendChild(close);
                            const stop=()=>{ try{ stream.getTracks().forEach(t=>t.stop()); }catch{} overlay.remove(); };

                            close.addEventListener('click', stop);
                            snap.addEventListener('click', async ()=>{
                                const canvas=document.createElement('canvas'); canvas.width=v.videoWidth||640; canvas.height=v.videoHeight||480; canvas.getContext('2d').drawImage(v,0,0);
                                canvas.toBlob(async blob=>{
                                    stop(); if(!blob) return;
                                    let file = new File([blob], 'photo_'+Date.now()+'.jpg', {type:'image/jpeg'});
                                    try{ file = await compressImage(file,.7);}catch{}
                                    const dt=new DataTransfer(); if (input.files) Array.from(input.files).forEach(f=>dt.items.add(f)); dt.items.add(file); input.files=dt.files;
                                    addPhotoUrl(URL.createObjectURL(file)); if (removeFlg) removeFlg.value='0';
                                }, 'image/jpeg', .95);
                            });
                        }catch(e){ alert('Camera error: ' + (e?.message||e)); }
                    })();
                });

                // remove all
                removeBtnAll?.addEventListener('click', ()=>{
                    photos.forEach(u=>{ try{ if(u.startsWith('blob:')) URL.revokeObjectURL(u); }catch{} });
                    photos=[]; current=0; render(); fileState=[]; syncInput(); if (removeFlg) removeFlg.value='1';
                });

                // refresh
                box.addEventListener('media:refresh', ()=>{
                    try{ const arr=JSON.parse(box.dataset.existing||'[]'); photos=Array.isArray(arr)?arr.slice():[]; }catch{ photos=[]; }
                    current=0; fileState=[]; syncInput(); render();
                });

                if (photos.length) render();
            }

            /* ===== AUDIO BOX ===== */
            function initAudioBox(box){
                const input = box.querySelector('.media-input');
                const uploadBtn = box.querySelector('.upload-media');
                const captureBtn = box.querySelector('.capture-media');
                const removeBtn = box.querySelector('.remove-media');
                const removeFlg = box.querySelector('.remove-flag');

                const previewBox = box.querySelector('.preview-audio-container');
                const list = box.querySelector('.preview-audio-list');

                function addAudios(urls){
                    urls.forEach(u=>{ const a=document.createElement('audio'); a.controls=true; a.className='w-100 mb-2'; a.src=u; list.appendChild(a); });
                    previewBox.style.display = urls.length ? 'block' : 'none';
                }

                let existing = (()=>{ try{ return JSON.parse(box.dataset.existing||'[]')||[]; }catch{return []} })();
                if (existing.length) addAudios(existing);

                box.addEventListener('media:refresh', ()=>{
                    list.innerHTML=''; try{ existing=JSON.parse(box.dataset.existing||'[]')||[]; }catch{ existing=[]; }
                    addAudios(existing);
                    if (input) input.value=''; if (removeFlg) removeFlg.value='0';
                });

                uploadBtn?.addEventListener('click', ()=> input?.click());
                input?.addEventListener('change', ()=>{
                    if (!input.files?.length) return;
                    Array.from(input.files).forEach(f=>{
                        const a=document.createElement('audio'); a.controls=true; a.className='w-100 mb-2'; a.src=URL.createObjectURL(f);
                        list.appendChild(a);
                    });
                    previewBox.style.display='block'; if (removeFlg) removeFlg.value='0';
                });

                // تسجيل صوت بدون عدّاد/نقطة مخصّصة
                let recorder=null, fallback=null, chunks=[], stream=null;
                captureBtn?.addEventListener('click', async ()=>{
                    if ((recorder && recorder.state==='recording') || fallback){
                        try{ recorder?.stop(); fallback?.stop(); }catch{}
                        captureBtn.className='btn btn-sm btn-brown';
                        captureBtn.innerHTML='<i class="bi bi-mic"></i> {{ __('dashboard.record') }}';
                        return;
                    }
                    try{
                        stream = await navigator.mediaDevices.getUserMedia({audio:true});
                        chunks=[];
                        let mime='audio/webm';
                        if (!(window.MediaRecorder && MediaRecorder.isTypeSupported?.(mime))){
                            if (MediaRecorder?.isTypeSupported?.('audio/mp4')) mime='audio/mp4';
                            else if (MediaRecorder?.isTypeSupported?.('audio/ogg')) mime='audio/ogg';
                            else mime='';
                        }
                        if (mime){
                            recorder=new MediaRecorder(stream,{mimeType:mime});
                            recorder.ondataavailable=e=>{ if (e.data.size>0) chunks.push(e.data); };
                            recorder.onstop=()=>{
                                try{ stream.getTracks().forEach(t=>t.stop()); }catch{}
                                const blob=new Blob(chunks,{type:mime});
                                const ext = mime.includes('mp4')?'.m4a':(mime.includes('ogg')?'.ogg':'.webm');
                                appendAudioFile(blob, ext, mime);
                            };
                            recorder.start();
                        } else {
                            fallback = startWavRecorder(stream, wavBlob => appendAudioFile(wavBlob,'.wav','audio/wav'));
                        }
                        captureBtn.className='btn btn-sm btn-danger';
                        captureBtn.innerHTML='<i class="bi bi-stop"></i> {{ __('dashboard.stop') }}';
                    }catch(e){ alert('Mic error: ' + (e?.message||e)); }
                });

                function appendAudioFile(blob, ext, type){
                    const file = new File([blob], 'rec_'+Date.now()+ext, {type});
                    const dt=new DataTransfer(); if (input.files) Array.from(input.files).forEach(f=>dt.items.add(f)); dt.items.add(file); input.files=dt.files;
                    const a=document.createElement('audio'); a.controls=true; a.className='w-100 mb-2'; a.src=URL.createObjectURL(file); list.appendChild(a);
                    previewBox.style.display='block'; if (removeFlg) removeFlg.value='0';
                    try{ stream?.getTracks()?.forEach(t=>t.stop()); }catch{} recorder=null; fallback=null; stream=null;
                }

                function startWavRecorder(stream, cb){
                    const ctx=new (window.AudioContext||window.webkitAudioContext)();
                    const src=ctx.createMediaStreamSource(stream);
                    const proc=ctx.createScriptProcessor(4096,1,1);
                    let buf=[]; proc.onaudioprocess=e=> buf.push(new Float32Array(e.inputBuffer.getChannelData(0)));
                    src.connect(proc); proc.connect(ctx.destination);
                    return { stop:()=>{ src.disconnect(); proc.disconnect(); ctx.close(); const blob=encodeWav(buf,44100); cb(blob); try{ stream.getTracks().forEach(t=>t.stop()); }catch{} } };
                }
                function encodeWav(chunks, rate){
                    const len=chunks.reduce((a,c)=>a+c.length,0), data=new Float32Array(len); let off=0; chunks.forEach(c=>{data.set(c,off); off+=c.length;});
                    const ab=new ArrayBuffer(44+data.length*2); const v=new DataView(ab); const w=(o,s)=>{ for(let i=0;i<s.length;i++) v.setUint8(o+i,s.charCodeAt(i)); };
                    w(0,'RIFF'); v.setUint32(4,36+data.length*2,true); w(8,'WAVE'); w(12,'fmt '); v.setUint32(16,16,true); v.setUint16(20,1,true); v.setUint16(22,1,true);
                    v.setUint32(24,rate,true); v.setUint32(28,rate*2,true); v.setUint16(32,2,true); v.setUint16(34,16,true); w(36,'data'); v.setUint32(40,data.length*2,true);
                    let idx=44; for(let i=0;i<data.length;i++,idx+=2){ const s=Math.max(-1,Math.min(1,data[i])); v.setInt16(idx,s<0?s*0x8000:s*0x7FFF,true); }
                    return new Blob([v],{type:'audio/wav'});
                }

                removeBtn?.addEventListener('click', ()=>{
                    list.innerHTML=''; previewBox.style.display='none'; if (input) input.value=''; if (removeFlg) removeFlg.value='1';
                });
            }

            // FilePond plugins
            if (window.FilePond){
                try{
                    FilePond.registerPlugin(
                        window.FilePondPluginFileValidateType || (()=>{}),
                        window.FilePondPluginFileValidateSize || (()=>{})
                    );
                }catch{}
            }

            /* ===== VIDEO BOX ===== */
            function initVideoBox(box){
                const captureBtn = box.querySelector('.capture-media');
                const removeAll  = box.querySelector('.remove-video-all');
                const input      = box.querySelector('.media-input');
                const camInput   = box.querySelector('.camera-video-input');
                const pondInput  = box.querySelector('.pond-video');
                const keysWrap   = box.querySelector('.uploaded-keys');
                const removeFlg  = box.querySelector('.remove-flag');
                const errorBox   = box.querySelector('.video-error');

                const slider   = box.querySelector('.video-slider');
                const player   = slider.querySelector('.slider-video');
                const prevBtn  = slider.querySelector('.prev');
                const nextBtn  = slider.querySelector('.next');
                const dots     = slider.querySelector('.slider-dots');
                const viewport = slider.querySelector('.slider-viewport');
                prevBtn.innerHTML = isRTL() ? '›' : '‹';
                nextBtn.innerHTML = isRTL() ? '‹' : '›';

                let items = (()=>{ try{ const j=JSON.parse(box.dataset.existing||'[]'); return (Array.isArray(j)?j:[]).map((u,i)=>({id:'server-'+i,url:u,revoke:false})); }catch{return []} })();
                let current = 0;

                function renderDots(){ dots.innerHTML=''; items.forEach((_,i)=>{ const d=document.createElement('div'); d.className='slider-dot'+(i===current?' active':''); d.addEventListener('click',()=>{current=i; renderVideo();}); dots.appendChild(d); }); }
                function renderVideo(){ if(!items.length){ slider.classList.add('d-none'); player.removeAttribute('src'); player.load(); return; } slider.classList.remove('d-none'); player.src=items[current].url; player.load(); renderDots(); }
                function addItem(id,url,revoke){ items.push({id,url,revoke}); current=items.length-1; renderVideo(); }
                function removeItem(id){ const pos=items.findIndex(x=>x.id===id); if(pos>-1){ const it=items[pos]; if(it.revoke){ try{ URL.revokeObjectURL(it.url);}catch{} } items.splice(pos,1); if(current>=items.length) current=Math.max(0,items.length-1); renderVideo(); } }

                // swipe
                (function(){ let sx=0; viewport.addEventListener('touchstart', e=> sx=e.changedTouches[0].clientX, {passive:true}); viewport.addEventListener('touchend', e=>{ const dx=e.changedTouches[0].clientX-sx; if(Math.abs(dx)>40) (dx<0?nextBtn.click():prevBtn.click()); }, {passive:true}); })();
                prevBtn.addEventListener('click', ()=>{ if(!items.length) return; current=(current-1+items.length)%items.length; renderVideo(); });
                nextBtn.addEventListener('click', ()=>{ if(!items.length) return; current=(current+1)%items.length; renderVideo(); });

                // FilePond
                let pond=null; const mapFileToSliderId=new WeakMap();
                if (window.FilePond){
                    pond = FilePond.create(pondInput, {
                        allowMultiple:true, credits:false, acceptedFileTypes:ACCEPTED_VIDEOS, maxFileSize:'500MB',
                        instantUpload:true, allowRevert:true, labelIdle:'{{ __('dashboard.upload') }}',
                        fileValidateTypeDetectType: (source, type) => new Promise(resolve => resolve(type || inferMime(source))),
                        server:{
                            process:(fieldName,file,metadata,load,error,progress,abort)=>{
                                let cancelled=false, currentXhr=null, uploadId='', key=''; const parts=[]; let uploadedBase=0; progress(true,0,file.size);
                                (async ()=>{
                                    try{
                                        const createRes = await fetch(`{{ route('multipart.create') }}`,{
                                            method:'POST', headers:{'Content-Type':'application/json','X-CSRF-TOKEN':CSRF},
                                            body: JSON.stringify({ fileName:file.name, contentType: inferMime(file), fileSize:file.size, prefix:'task_attachments/videos' })
                                        });
                                        if(!createRes.ok) throw new Error('createMultipart failed');
                                        const { uploadId:uid, key:k } = await createRes.json(); uploadId=uid; key=k;

                                        const totalParts=Math.ceil(file.size/CHUNK_SIZE);
                                        for(let partNumber=1; partNumber<=totalParts; partNumber++){
                                            if (cancelled) return;
                                            const start=(partNumber-1)*CHUNK_SIZE, end=Math.min(start+CHUNK_SIZE,file.size);
                                            const blob=file.slice(start,end);
                                            const signRes = await fetch(`{{ route('multipart.sign') }}`,{
                                                method:'POST', headers:{'Content-Type':'application/json','X-CSRF-TOKEN':CSRF},
                                                body: JSON.stringify({ key, uploadId, partNumber })
                                            });
                                            if(!signRes.ok) throw new Error('signPart failed');
                                            const { url } = await signRes.json();

                                            await new Promise((resolve,reject)=>{
                                                const xhr=new XMLHttpRequest(); currentXhr=xhr; xhr.open('PUT',url,true);
                                                xhr.upload.onprogress=e=>{ if(e.lengthComputable) progress(true, uploadedBase + e.loaded, file.size); };
                                                xhr.onload=()=>{ currentXhr=null; if(xhr.status>=200 && xhr.status<300){
                                                    const etag=(xhr.getResponseHeader('ETag')||'').replace(/"/g,''); if(!etag) return reject(new Error('Missing ETag'));
                                                    parts.push({ETag:etag, PartNumber:partNumber}); uploadedBase+=blob.size; progress(true,uploadedBase,file.size); resolve();
                                                } else reject(new Error('PUT failed: '+xhr.status)); };
                                                xhr.onerror=()=>reject(new Error('Network error')); xhr.onabort=()=>reject(new Error('Aborted')); xhr.send(blob);
                                            });
                                        }

                                        const completeRes = await fetch(`{{ route('multipart.complete') }}`,{
                                            method:'POST', headers:{'Content-Type':'application/json','X-CSRF-TOKEN':CSRF},
                                            body: JSON.stringify({ key, uploadId, parts })
                                        });
                                        if(!completeRes.ok) throw new Error('completeMultipart failed');

                                        keysWrap.appendChild(createHidden('uploaded_videos[]', key));
                                        if (removeFlg) removeFlg.value='0';
                                        load(key);
                                    }catch(e){
                                        if (cancelled) return;
                                        console.error(e); error(e.message||'upload failed');
                                        errorBox?.classList.remove('d-none'); if (errorBox) errorBox.textContent='فشل رفع الفيديو. حاول من جديد.';
                                    }
                                })();
                                return { abort:()=>{ cancelled=true; try{ currentXhr?.abort(); }catch{} abort(); } };
                            },
                            revert:(id,load)=>{ load(); }
                        }
                    });

                    pond.on('addfile', (err,fileItem)=>{ if(err||!fileItem) return; const blobURL=URL.createObjectURL(fileItem.file); const sid='pond-'+Math.random().toString(36).slice(2); addItem(sid,blobURL,true); mapFileToSliderId.set(fileItem.file,sid); if(removeFlg) removeFlg.value='0'; });
                    pond.on('removefile', (fileItem)=>{ if(!fileItem) return; const sid=mapFileToSliderId.get(fileItem.file); if(sid){ removeItem(sid); mapFileToSliderId.delete(fileItem.file); } });
                }

                // تسجيل فيديو ديسكتوب — بدون عدّاد مخصّص، مع زر X بسيط
                let recStream=null, recorder=null, chunks=[];
                let overlay=null;
                function stopTracks(s){ try{ s?.getTracks()?.forEach(t=>t.stop()); }catch{} }

                captureBtn?.addEventListener('click', async ()=>{
                    if (isMobile || !('MediaRecorder' in window)){ camInput?.click(); return; }

                    if (recorder && recorder.state==='recording'){ try{ recorder.stop(); }catch{} return; }

                    try{
                        recStream = await navigator.mediaDevices.getUserMedia({ video:{facingMode:{ideal:'environment'}, width:{ideal:1280}, height:{ideal:720}}, audio:true });
                        overlay=document.createElement('div'); overlay.style.cssText='position:absolute;inset:0;background:#000;display:flex;align-items:center;justify-content:center;z-index:5;';
                        const live=document.createElement('video'); live.autoplay=true; live.muted=true; live.playsInline=true; live.srcObject=recStream; live.style.cssText='width:100%;height:100%;object-fit:contain;';
                        const close=document.createElement('button'); close.type='button'; close.className='record-close'; close.setAttribute('aria-label','Close'); close.innerHTML='&times;';

                        viewport.appendChild(overlay); overlay.appendChild(live); overlay.appendChild(close);
                        slider.classList.remove('d-none');

                        let chosenMime='video/webm;codecs=vp8,opus';
                        if (!MediaRecorder.isTypeSupported?.(chosenMime)) chosenMime = MediaRecorder.isTypeSupported?.('video/webm') ? 'video/webm' : (MediaRecorder.isTypeSupported?.('video/mp4') ? 'video/mp4' : '');
                        recorder = new MediaRecorder(recStream, { mimeType: chosenMime || undefined, videoBitsPerSecond: 800_000, audioBitsPerSecond: 64_000 });
                        recorder.ondataavailable=e=>{ if(e.data && e.data.size>0) chunks.push(e.data); };
                        recorder.onstop=()=>{
                            try{ overlay.remove(); }catch{}
                            stopTracks(recStream); recStream=null;
                            const type = cleanMime(recorder.mimeType || chosenMime || 'video/webm');
                            const ext = type.includes('mp4') ? 'mp4' : 'webm';
                            const blob = new Blob(chunks,{type});
                            const file = new File([blob], `rec_${Date.now()}.${ext}`, {type});
                            try{ pond && pond.addFile(file); }catch{}
                            recorder=null; chunks=[];
                            if (removeFlg) removeFlg.value='0';
                            captureBtn.classList.remove('btn-danger'); captureBtn.classList.add('btn-brown'); captureBtn.innerHTML='🎥 {{ __('dashboard.start_recording_video') }}';
                        };
                        recorder.start();
                        captureBtn.classList.remove('btn-brown'); captureBtn.classList.add('btn-danger'); captureBtn.innerHTML='<i class="bi bi-stop"></i> {{ __('dashboard.stop') }}';

                        close.addEventListener('click', ()=>{
                            try{ recorder.stop(); }catch(e){ stopTracks(recStream); overlay.remove(); recorder=null; chunks=[]; captureBtn.classList.remove('btn-danger'); captureBtn.classList.add('btn-brown'); captureBtn.innerHTML='🎥 {{ __('dashboard.start_recording_video') }}'; }
                        });
                    }catch(e){
                        stopTracks(recStream); recStream=null; try{ overlay?.remove(); }catch{} recorder=null; chunks=[];
                        captureBtn.classList.remove('btn-danger'); captureBtn.classList.add('btn-brown'); captureBtn.innerHTML='🎥 {{ __('dashboard.start_recording_video') }}';
                        alert('Camera/Mic error: ' + (e?.message||e));
                    }
                });

                // system camera → pond
                camInput?.addEventListener('change', e=>{
                    const files = Array.from(e.target.files||[]);
                    if (!files.length || !pond) return;
                    files.forEach(f=> pond.addFile(f));
                    if (removeFlg) removeFlg.value='0';
                    e.target.value='';
                });

                // fallback input
                input?.addEventListener('change', e=>{
                    if (!pond) return;
                    Array.from(e.target.files||[]).forEach(f=> pond.addFile(f));
                    e.target.value='';
                    if (removeFlg) removeFlg.value='0';
                });

                // remove all
                removeAll?.addEventListener('click', ()=>{
                    try{ pond?.removeFiles(); }catch{}
                    keysWrap.innerHTML=''; items=[]; current=0; renderVideo(); if (removeFlg) removeFlg.value='1';
                });

                // refresh
                box.addEventListener('media:refresh', ()=>{
                    try{
                        const arr = JSON.parse(box.dataset.existing||'[]');
                        items = (Array.isArray(arr)?arr:[]).map((u,i)=>({id:'server-'+i,url:u,revoke:false}));
                    }catch{ items=[]; }
                    current=0; renderVideo();
                });

                renderVideo();
            }

            // init all media boxes
            document.querySelectorAll('.photo-box').forEach(initPhotoBox);
            document.querySelectorAll('.audio-box').forEach(initAudioBox);
            document.querySelectorAll('.video-box').forEach(initVideoBox);

            // نوع الأزرار
            document.querySelectorAll('#photo-lightbox button, .photo-slider button, .video-slider button')
              .forEach(btn=>{ if(!btn.hasAttribute('type')) btn.setAttribute('type','button'); });
        });
    </script>
@endpush


@section('scripts')
    <script>
        $(function () {
            // فتح المودال + تعبئة
            $('.view-task').on('click', function () {
                const task = $(this).data('task');
                const notificationId = $(this).data('notification-id');

                const formatDate = d => new Date(d).toLocaleDateString('en-US', {year:'numeric', month:'short', day:'numeric'});

                const photos = task.photos_urls || [];
                const audios = task.audios_urls || [];
                const videos = task.videos_urls || [];

                const photoBox = document.querySelector('.photo-box');
                const audioBox = document.querySelector('.audio-box');
                const videoBox = document.querySelector('.video-box');

                if (photoBox) { photoBox.dataset.existing = JSON.stringify(photos); photoBox.dispatchEvent(new Event('media:refresh')); }
                if (audioBox) { audioBox.dataset.existing = JSON.stringify(audios); audioBox.dispatchEvent(new Event('media:refresh')); }
                if (videoBox) { videoBox.dataset.existing = JSON.stringify(videos); videoBox.dispatchEvent(new Event('media:refresh')); }

                // علّم الإشعار مقروء
                if (notificationId) {
                    $.ajax({ url: `/notifications/${notificationId}/read`, type: 'POST', data: { _token: '{{ csrf_token() }}' } });
                }

                // تعبئة معلومات المهمة
                $('#taskTitle').text(task.title);
                $('#taskDescription').text(task.description || 'N/A');
                $('#taskDueDate').text(formatDate(task.due_date));
                $('#taskCreatedAt').text(formatDate(task.created_at));
                $('#taskId').val(task.id);
                $('#taskStatus').val(task.status);

                const priorityColors = { low: 'primary', medium: 'warning', high: 'danger' };
                const priorityTranslations = {
                    low: @json(__('dashboard.low')),
                    medium: @json(__('dashboard.medium')),
                    high: @json(__('dashboard.high'))
                };
                $('#taskPriority').html(
                    `<span class="badge bg-${priorityColors[task.priority] || 'secondary'}">
                        ${priorityTranslations[task.priority] || task.priority}
                    </span>`
                );

                if (task.status === 'failed') {
                    $('#failureReasonContainer').show();
                    $('textarea[name="failure_reason"]').val(task.failure_reason || '');
                } else {
                    $('#failureReasonContainer').hide();
                    $('textarea[name="failure_reason"]').val('');
                }

                $('#taskModal').modal('show');
            });

            // تبديل الحالة
            $('#taskStatus').on('change', function () {
                $(this).val() === 'failed' ? $('#failureReasonContainer').show() : $('#failureReasonContainer').hide();
            });

            // حفظ الحالة + المرفقات (ينتظر اكتمال FilePond)
            $('#saveStatus').on('click', async function () {
                const ponds = Array.from(document.querySelectorAll('.pond-video'))
                    .map(input => FilePond.find(input))
                    .filter(Boolean);
                const FP = window.FilePond;
                const hasPending = ponds.some(p =>
                    p.getFiles().some(f => f.status !== FP.FileStatus.PROCESSING_COMPLETE && f.status !== FP.FileStatus.IDLE)
                );
                if (hasPending) {
                    alert('انتظر حتى يكتمل رفع الفيديوهات…');
                    return;
                }

                const $btn = $(this).prop('disabled', true).addClass('disabled');
                const form = document.getElementById('statusForm');
                const formData = new FormData(form);

                try {
                    const id = $('#taskId').val();
                    await $.ajax({
                        url: `/tasks/${id}/status`,
                        method: 'POST',
                        data: formData,
                        processData: false,
                        contentType: false
                    });
                    $('#taskModal').modal('hide');
                    location.reload();
                } catch (xhr) {
                    $('.invalid-feedback').remove();
                    $('.is-invalid').removeClass('is-invalid');

                    const errors = xhr?.responseJSON?.errors || {};
                    if (errors.status) $('#taskStatus').addClass('is-invalid').after(`<div class="invalid-feedback">${errors.status[0]}</div>`);
                    if (errors.failure_reason) $('textarea[name="failure_reason"]').addClass('is-invalid').after(`<div class="invalid-feedback">${errors.failure_reason[0]}</div>`);
                    if (errors.photos || errors['photos.0']) $('input[name="photos[]"]').addClass('is-invalid').closest('.media-upload-container').append(`<div class="invalid-feedback d-block">${(errors.photos?.[0] || errors['photos.0']?.[0])}</div>`);
                    if (errors.audios || errors['audios.0']) $('input[name="audios[]"]').addClass('is-invalid').closest('.media-upload-container').append(`<div class="invalid-feedback d-block">${(errors.audios?.[0] || errors['audios.0']?.[0])}</div>`);
                    if (errors.uploaded_videos || errors['uploaded_videos.0']) $('.video-box').append(`<div class="invalid-feedback d-block">${(errors.uploaded_videos?.[0] || errors['uploaded_videos.0']?.[0])}</div>`);

                    if (!Object.keys(errors).length) alert(xhr?.responseJSON?.message || 'حدث خطأ أثناء الحفظ');
                } finally {
                    $btn.prop('disabled', false).removeClass('disabled');
                }
            });

            // type=button لأزرار السلايدر/اللايت بوكس
            $('#photo-lightbox button, .photo-slider button, .video-slider button').each(function(){
                if (!this.hasAttribute('type')) this.setAttribute('type','button');
            });
        });
    </script>
@endsection
