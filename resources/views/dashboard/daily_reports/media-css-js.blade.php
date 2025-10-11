

@push('css')
<link href="https://unpkg.com/filepond/dist/filepond.min.css" rel="stylesheet">
<style>
  .media-upload-container{border:1px dashed #ddd;border-radius:4px;padding:10px;}
  .remove-media{width:100%}
  .preview-audio-container audio,.preview-video-container video{max-width:100%;border-radius:8px;box-shadow:0 2px 6px rgba(0,0,0,.2)}

  /* PHOTO SLIDER */
  .photo-slider{position:relative;border:1px solid #ddd;border-radius:8px;overflow:hidden;;box-shadow:0 2px 6px rgba(0,0,0,.06)}
  .photo-slider .slider-viewport{height:220px;display:flex;align-items:center;justify-content:center;background:#111!important}
  .photo-slider .slider-img{max-width:100%;max-height:100%;display:block;object-fit:contain;user-select:none;-webkit-user-drag:none}
  .photo-slider .slider-nav{position:absolute;top:50%;transform:translateY(-50%);border:0;width:36px;height:36px;border-radius:50%;background:rgba(0,0,0,.45);color:#fff;font-size:18px;display:flex;align-items:center;justify-content:center;cursor:pointer}
  .photo-slider .slider-nav:hover{background:rgba(0,0,0,.55)}
  .photo-slider .prev{left:8px}
  .photo-slider .next{right:8px}
  .photo-slider .slider-dots{display:flex;justify-content:center;gap:6px;padding:8px;}
  .photo-slider .slider-dot{width:8px;height:8px;border-radius:50%;cursor:pointer}
  .photo-slider .slider-dot.active{background:#333}

  /* VIDEO SLIDER */
  .video-slider{position:relative;border:1px solid #ddd;border-radius:8px;overflow:hidden;box-shadow:0 2px 6px rgba(0,0,0,.06)}
  .video-slider .slider-viewport{height:220px;display:flex;align-items:center;justify-content:center;background:#000}
  .video-slider .slider-video{width:100%;max-height:100%;object-fit:contain;background:#000}
  .video-slider .slider-nav{position:absolute;top:50%;transform:translateY(-50%);border:0;width:36px;height:36px;border-radius:50%;background:rgba(0,0,0,.45);color:#fff;font-size:18px;display:flex;align-items:center;justify-content:center;cursor:pointer}
  .video-slider .slider-nav:hover{background:rgba(0,0,0,.55)}
  .video-slider .prev{left:8px}
  .video-slider .next{right:8px}
  .video-slider .slider-dots{display:flex;justify-content:center;gap:6px;padding:8px;}
  .video-slider .slider-dot{width:8px;height:8px;border-radius:50%;cursor:pointer}
  .video-slider .slider-dot.active{background:#333}

  /* AUDIO STYLE */
  .preview-audio-list audio{display:block;width:100%;margin-top:.25rem}

  /* LIGHTBOX */
  .lightbox{position:fixed;inset:0;background:rgba(0,0,0,.9);display:flex;align-items:center;justify-content:center;z-index:9999}
  .lightbox.d-none{display:none}
  .lightbox img{max-width:90%;max-height:90%;object-fit:contain}
  .lightbox .close{position:absolute;top:20px;right:30px;font-size:36px;color:#fff;cursor:pointer}
  .lightbox .nav{position:absolute;top:50%;transform:translateY(-50%);font-size:48px;color:#fff;background:transparent;border:none;cursor:pointer}
  .lightbox .nav.prev{left:20px;right:auto}
  .lightbox .nav.next{right:20px;left:auto}

  /* RTL */
  [dir="rtl"] .lightbox .nav.prev{right:auto;left:20px}
  [dir="rtl"] .lightbox .nav.next{left:auto;right:20px}
  [dir="rtl"] .photo-slider .prev,
  [dir="rtl"] .video-slider .prev,
  [dir="rtl"] .lightbox .nav.prev{right:8px;left:auto;transform:translateY(-50%) scaleX(-1)}
  [dir="rtl"] .photo-slider .next,
  [dir="rtl"] .video-slider .next,
  [dir="rtl"] .lightbox .nav.next{left:8px;right:auto;transform:translateY(-50%) scaleX(-1)}

  .photo-slider .slider-nav,.video-slider .slider-nav{font-size:20px;font-weight:bold}
</style>
@endpush


@push('js')
<script src="https://unpkg.com/filepond/dist/filepond.min.js"></script>
<script src="https://unpkg.com/filepond-plugin-file-validate-type/dist/filepond-plugin-file-validate-type.min.js"></script>
<script src="https://unpkg.com/filepond-plugin-file-validate-size/dist/filepond-plugin-file-validate-size.min.js"></script>

<script>
document.addEventListener('DOMContentLoaded', function () {
  const CSRF = '{{ csrf_token() }}';
  const CHUNK_SIZE = 10 * 1024 * 1024; // 10MB
  const isMobile = /Mobi|Android|iPhone|iPad/i.test(navigator.userAgent);
  const PREFIX = 'daily_reports/videos';

  const ACCEPTED_VIDEOS = [
    'video/mp4','video/quicktime','video/hevc','video/webm','video/avi',
    'video/x-matroska','video/x-flv','video/3gpp','video/3gpp2','video/mpeg',
    'video/mpg','video/x-ms-wmv','video/x-m4v'
  ];

  const isRTL = () => document.documentElement.dir === 'rtl' || getComputedStyle(document.body).direction === 'rtl';

  // Safari fix: استدلال الـ MIME من الامتداد عند غيابه
  function inferMime(file){
    let t = (file?.type || '').split(';')[0].trim();
    if (!t){
      const ext = (file?.name || '').split('.').pop()?.toLowerCase();
      const map = { mp4:'video/mp4', m4v:'video/x-m4v', mov:'video/quicktime', webm:'video/webm',
        mkv:'video/x-matroska', avi:'video/avi', flv:'video/x-flv', mpg:'video/mpeg', mpeg:'video/mpeg',
        '3gp':'video/3gpp','3gpp':'video/3gpp','3gpp2':'video/3gpp2', hevc:'video/hevc' };
      t = map[ext] || 'video/mp4';
    }
    return t;
  }
  const cleanMime = (type)=> (type || '').split(';')[0].trim() || 'video/webm';

  /* =================== PHOTO =================== */
  (function initPhoto(){
    const box = document.querySelector('[data-type="photo"]');
    if(!box) return;

    const input = box.querySelector('.media-input');
    const previewSingle = box.querySelector('.preview-image-container');
    const captureBtn = box.querySelector('.capture-media');
    const uploadBtn  = box.querySelector('.upload-media');
    const removeFlag = box.querySelector('.remove-flag');

    // slider
    const sliderWrap = document.createElement('div');
    sliderWrap.className = 'photo-slider mb-2 d-none';
    sliderWrap.innerHTML = `
      <div class="slider-viewport"><img class="slider-img" alt="photo"></div>
      <button type="button" class="slider-nav prev" aria-label="Prev">‹</button>
      <button type="button" class="slider-nav next" aria-label="Next">›</button>
      <div class="slider-dots"></div>`;
    const actionsWrap = box.querySelector('.d-grid, .btn-group');
    (actionsWrap ? actionsWrap.parentNode.insertBefore(sliderWrap, actionsWrap) : box.prepend(sliderWrap));

    const viewport = sliderWrap.querySelector('.slider-viewport');
    const imgEl = sliderWrap.querySelector('.slider-img');
    const prevBtn = sliderWrap.querySelector('.prev');
    const nextBtn = sliderWrap.querySelector('.next');
    const dotsBox = sliderWrap.querySelector('.slider-dots');

    prevBtn.textContent = isRTL() ? '›' : '‹';
    nextBtn.textContent = isRTL() ? '‹' : '›';

    // lightbox
    const lightbox = document.getElementById('photo-lightbox');
    const lightImg = lightbox.querySelector('.lightbox-img');
    const lbClose = lightbox.querySelector('.close');
    const lbPrev  = lightbox.querySelector('.nav.prev');
    const lbNext  = lightbox.querySelector('.nav.next');
    // حط أسهم لو كانت أزرار اللايت بوكس فاضية
    if(!lbPrev.textContent.trim()) lbPrev.textContent = isRTL() ? '›' : '‹';
    if(!lbNext.textContent.trim()) lbNext.textContent = isRTL() ? '‹' : '›';

    let photos = [];
    try{
      const initial = box.dataset.photos ? JSON.parse(box.dataset.photos) : [];
      if (Array.isArray(initial) && initial.length){
        photos = initial.slice();
        if (previewSingle){ previewSingle.style.display='none'; previewSingle.innerHTML=''; }
      }
    }catch{}

    let current = 0;

    function renderDots(){
      dotsBox.innerHTML='';
      photos.forEach((_, i)=>{
        const d=document.createElement('div');
        d.className='slider-dot'+(i===current?' active':'');
        d.addEventListener('click', ()=>{ current=i; render(); });
        dotsBox.appendChild(d);
      });
    }
    function render(){
      if(!photos.length){ sliderWrap.classList.add('d-none'); imgEl.removeAttribute('src'); return; }
      sliderWrap.classList.remove('d-none'); imgEl.src=photos[current]; renderDots();
    }
    const addPhotoToSlider = (url)=>{ photos.push(url); current=photos.length-1; render(); };
    const goPrev = ()=>{ if(photos.length){ current=(current-1+photos.length)%photos.length; render(); } };
    const goNext = ()=>{ if(photos.length){ current=(current+1)%photos.length; render(); } };

    prevBtn.addEventListener('click', goPrev);
    nextBtn.addEventListener('click', goNext);

    imgEl.addEventListener('click', ()=>{ if(!photos.length) return; lightbox.classList.remove('d-none'); lightImg.src=photos[current]; });
    lbClose.addEventListener('click', ()=> lightbox.classList.add('d-none'));
    lbPrev.addEventListener('click', ()=>{ current=(current-1+photos.length)%photos.length; lightImg.src=photos[current]; });
    lbNext.addEventListener('click', ()=>{ current=(current+1)%photos.length; lightImg.src=photos[current]; });
    document.addEventListener('keydown', e=>{
      if(e.key==='Escape') lightbox.classList.add('d-none');
      if(e.key==='ArrowLeft') lbPrev.click();
      if(e.key==='ArrowRight') lbNext.click();
    });

    // compression
    function compressImage(file, quality=0.7, maxW=1280, maxH=1280){
      return new Promise((resolve,reject)=>{
        const img=new Image(); const url=URL.createObjectURL(file);
        img.onload=()=>{
          let {width,height}=img;
          if(width>maxW || height>maxH){ const s=Math.min(maxW/width, maxH/height); width=Math.round(width*s); height=Math.round(height*s); }
          const canvas=document.createElement('canvas'); canvas.width=width; canvas.height=height;
          canvas.getContext('2d').drawImage(img,0,0,width,height);
          canvas.toBlob(b=>{ URL.revokeObjectURL(url); if(!b) return reject('Compression failed');
            const name=(file.name||'photo').replace(/\.[^.]+$/,'')+'.jpg';
            resolve(new File([b], name, {type:'image/jpeg'}));
          }, 'image/jpeg', quality);
        };
        img.onerror=()=>{ URL.revokeObjectURL(url); reject('Decode failed'); };
        img.src=url;
      });
    }

    const fileKey=f=>`${f.name}__${f.size}__${f.lastModified||0}__${f.type}`;
    let fileState=[];
    const syncInput = ()=>{ const dt=new DataTransfer(); fileState.forEach(f=>dt.items.add(f)); input.files=dt.files; };
    const addFileUnique = f=>{ const k=fileKey(f); if(fileState.some(x=>fileKey(x)===k)) return false; fileState.push(f); syncInput(); return true; };

    // upload from gallery
    box.querySelector('.upload-media')?.addEventListener('click', ()=>{
      const picker=document.createElement('input');
      picker.type='file'; picker.accept='image/*'; picker.multiple=true;
      picker.addEventListener('change', async ()=>{
        const files=Array.from(picker.files||[]);
        for(const f of files){
          let ff=f; try{ ff=await compressImage(f, .7);}catch{}
          if(addFileUnique(ff)) addPhotoToSlider(URL.createObjectURL(ff));
        }
        removeFlag.value='0'; picker.value='';
      });
      picker.click();
    });

    // mobile camera
    const camPhotoInput=document.getElementById('camera-photo-input');
    camPhotoInput?.addEventListener('change', async (e)=>{
      const files=Array.from(e.target.files||[]);
      for(const f of files){
        let ff=f; try{ ff=await compressImage(f,.7);}catch{}
        if(addFileUnique(ff)) addPhotoToSlider(URL.createObjectURL(ff));
      }
      removeFlag.value='0'; camPhotoInput.value='';
    });

    // desktop camera snap
    captureBtn?.addEventListener('click', ()=>{
      if(isMobile && camPhotoInput){ camPhotoInput.click(); return; }
      (async ()=>{
        try{
          const stream=await navigator.mediaDevices.getUserMedia({video:{facingMode:{ideal:'environment'},width:{ideal:1280},height:{ideal:720},aspectRatio:{ideal:16/9}}, audio:false});
          const overlay=document.createElement('div');
          overlay.style.cssText='position:absolute;inset:0;background:#000;display:flex;align-items:center;justify-content:center;z-index:2;';
          const v=document.createElement('video'); v.autoplay=v.muted=true; v.playsInline=true; v.srcObject=stream; v.style.cssText='width:100%;height:100%;object-fit:contain;';
          const btn=document.createElement('button'); btn.type='button'; btn.className='btn btn-success btn-sm'; btn.textContent='Snap';
          Object.assign(btn.style,{position:'absolute',bottom:'10px',left:'50%',transform:'translateX(-50%)'});
          overlay.appendChild(v); overlay.appendChild(btn); sliderWrap.classList.remove('d-none'); viewport.appendChild(overlay);
          btn.addEventListener('click', ()=>{
            const canvas=document.createElement('canvas'); canvas.width=v.videoWidth||640; canvas.height=v.videoHeight||480;
            canvas.getContext('2d').drawImage(v,0,0);
            try{ stream.getTracks().forEach(t=>t.stop()); }catch{}
            canvas.toBlob(async blob=>{
              if(!blob){ overlay.remove(); return; }
              let file=new File([blob], 'photo_'+Date.now()+'.jpg', {type:'image/jpeg'});
              try{ file=await compressImage(file,.7);}catch{}
              if(addFileUnique(file)) addPhotoToSlider(URL.createObjectURL(file));
              removeFlag.value='0'; overlay.remove();
            }, 'image/jpeg', .95);
          });
        }catch(e){ alert('Camera error: '+(e?.message||e)); }
      })();
    });

    // remove all
    box.querySelector('.remove-media')?.addEventListener('click', ()=>{
      photos=[]; current=0; render(); fileState=[]; syncInput(); removeFlag.value='1';
      if(previewSingle){ previewSingle.style.display='none'; previewSingle.innerHTML=''; }
    });

    // swipe
    let startX=0;
    viewport.addEventListener('touchstart', e=> startX=e.changedTouches[0].clientX, {passive:true});
    viewport.addEventListener('touchend', e=>{
      const dx=e.changedTouches[0].clientX - startX;
      if(Math.abs(dx)>40){ isRTL()? (dx<0?goPrev():goNext()) : (dx<0?goNext():goPrev()); }
    }, {passive:true});

    render();
  })();

  /* =================== VIDEO (FilePond + Slider) =================== */
  if (window.FilePond){
    try{
      FilePond.registerPlugin(
        window.FilePondPluginFileValidateType || (()=>{}),
        window.FilePondPluginFileValidateSize || (()=>{})
      );
    }catch{}
  }

  const elVideo = document.getElementById('pond-video');
  const sliderRoot = document.getElementById('video-slider');
  const player = sliderRoot?.querySelector('.slider-video');
  const btnPrev = sliderRoot?.querySelector('.prev');
  const btnNext = sliderRoot?.querySelector('.next');
  const dotsRoot = sliderRoot?.querySelector('.slider-dots');

  if(btnPrev && btnNext){
    btnPrev.textContent = isRTL() ? '›' : '‹';
    btnNext.textContent = isRTL() ? '‹' : '›';
  }

  let videoItems=[], vCurrent=0;
  function renderVideoDots(){
    if(!dotsRoot) return;
    dotsRoot.innerHTML='';
    videoItems.forEach((_,i)=>{
      const d=document.createElement('div');
      d.className='slider-dot'+(i===vCurrent?' active':'');
      d.addEventListener('click',()=>{ vCurrent=i; renderVideo(); });
      dotsRoot.appendChild(d);
    });
  }
  function renderVideo(){
    if(!sliderRoot || !player) return;
    if(!videoItems.length){ sliderRoot.classList.add('d-none'); player.removeAttribute('src'); player.load(); return; }
    sliderRoot.classList.remove('d-none'); player.src = videoItems[vCurrent].url; player.load(); renderVideoDots();
  }
  function addVideoItem(id,url,revoke=false){ videoItems.push({id,url,revoke}); vCurrent=videoItems.length-1; renderVideo(); }
  function removeVideoItem(id){
    const idx=videoItems.findIndex(x=>x.id===id);
    if(idx>-1){ const it=videoItems[idx]; if(it.revoke){ try{ URL.revokeObjectURL(it.url);}catch{} } videoItems.splice(idx,1); if(vCurrent>=videoItems.length) vCurrent=Math.max(0,videoItems.length-1); renderVideo(); }
  }

  (function initSliderFromServer(){
    if(!sliderRoot) return;
    let initial=[]; try{ initial = JSON.parse(sliderRoot.dataset.initial || '[]'); }catch{ initial=[]; }
    initial.forEach((url,i)=> addVideoItem('server-'+i, url, false));

    const viewport=sliderRoot.querySelector('.slider-viewport');
    btnPrev?.addEventListener('click', ()=>{ if(!videoItems.length) return; vCurrent=(vCurrent-1+videoItems.length)%videoItems.length; renderVideo(); });
    btnNext?.addEventListener('click', ()=>{ if(!videoItems.length) return; vCurrent=(vCurrent+1)%videoItems.length; renderVideo(); });
    if(viewport){
      let sx=0;
      viewport.addEventListener('touchstart', e=> sx=e.changedTouches[0].clientX, {passive:true});
      viewport.addEventListener('touchend', e=>{
        const dx=e.changedTouches[0].clientX - sx;
        if (Math.abs(dx)>40) isRTL()? (dx<0?btnPrev?.click():btnNext?.click()) : (dx<0?btnNext?.click():btnPrev?.click());
      }, {passive:true});
    }
    renderVideo();
  })();

  // زر التقاط/تسجيل فيديو (موبايل/ديسكتوب)
  (function initVideoCapture(){
    const btn = document.getElementById('capture-video-btn');
    const camInput = document.getElementById('camera-video-input');
    if (camInput) camInput.multiple = true;

    function pushFilesToPond(files){
      if (!window.pondVideo) return;
      files.forEach(f=> window.pondVideo.addFile(f));
      document.getElementById('remove_video_flag').value='0';
    }

    function onPick(e){ const files = Array.from(e.target.files || []); if(!files.length) return; pushFilesToPond(files); e.target.value=''; }

    if(!btn){ camInput?.addEventListener('change', onPick); return; }

    if (isMobile){
      btn.addEventListener('click', ()=> camInput?.click());
      camInput?.addEventListener('change', onPick);
    } else {
      let recorder=null, chunks=[], stream=null;
      const live = document.querySelector('#video-slider .slider-video');
      btn.addEventListener('click', async ()=>{
        if (recorder && recorder.state==='recording'){
          recorder.stop();
          btn.className='btn btn-sm btn-primary';
          btn.innerHTML='🎥 {{ __('dashboard.start_recording_video') }}';
          return;
        }
        try{
          stream = await navigator.mediaDevices.getUserMedia({ video:{facingMode:{ideal:'environment'},width:{ideal:1280},height:{ideal:720},aspectRatio:{ideal:16/9}}, audio:true });
          sliderRoot?.classList.remove('d-none');
          if (live){ live.srcObject = stream; live.muted = true; await live.play().catch(()=>{}); }

          chunks=[];
          let mimeType='video/webm;codecs=vp8,opus';
          if(!MediaRecorder.isTypeSupported?.(mimeType)) mimeType = MediaRecorder.isTypeSupported?.('video/webm') ? 'video/webm' : 'video/mp4';

          recorder = new MediaRecorder(stream, { mimeType, videoBitsPerSecond:800000, audioBitsPerSecond:64000 });
          recorder.ondataavailable = e=>{ if(e.data.size>0) chunks.push(e.data); };
          recorder.onstop = ()=>{
            try{ stream.getTracks().forEach(t=>t.stop()); }catch{}
            if (live) live.srcObject = null;
            const rawBlob = new Blob(chunks, {type:mimeType});
            const type = cleanMime(rawBlob.type);
            const file = new File([rawBlob], 'recorded_'+Date.now()+(type==='video/mp4'?'.mp4':'.webm'), {type});
            pushFilesToPond([file]);
          };

          recorder.start();
          btn.className='btn btn-sm btn-danger';
          btn.innerHTML='<i class="bi bi-stop"></i> {{ __('dashboard.stop') }}';
        }catch(e){ alert('Camera error: '+(e?.message||e)); }
      });
    }
  })();

  // FilePond للفيديو
  const submitBtn = document.querySelector('button[type="submit"].btn.btn-primary'); // زر حفظ التقرير
  function bindSaveButtonToPond(pond){
    const FP = window.FilePond;
    if (!pond || !FP || !submitBtn) return;
    const update = ()=>{
      const pending = pond.getFiles().some(f => f.status !== FP.FileStatus.PROCESSING_COMPLETE && f.status !== FP.FileStatus.IDLE);
      submitBtn.disabled = pending;
      submitBtn.classList.toggle('disabled', pending);
      submitBtn.dataset.pending = pending ? '1' : '0';
    };
    ['addfile','processfilestart','processfileprogress','processfile','processfileabort','processfileundo','removefile','error']
      .forEach(ev=> pond.on(ev, update));
    update();

    // امنع الإرسال أثناء الرفع
    const form = submitBtn.closest('form');
    form?.addEventListener('submit', (e)=>{
      if (submitBtn.dataset.pending === '1'){
        e.preventDefault();
        alert('انتظر حتى يكتمل رفع الفيديوهات…');
      }
    });
  }

  if (elVideo && window.FilePond){
    const pondVideo = FilePond.create(elVideo, {
      allowMultiple: true,
      credits: false,
      acceptedFileTypes: ACCEPTED_VIDEOS,
      maxFileSize: '500MB',
      instantUpload: true,
      allowRevert: true,
      fileValidateTypeDetectType: (source, type) => new Promise(resolve => resolve(type || inferMime(source))),
      labelIdle: '{{ __('dashboard.upload') }}',
      server: {
        process: (fieldName, file, metadata, load, error, progress, abort)=>{
          let cancelled=false, currentXhr=null, uploadId='', key='';
          const parts=[]; let uploadedBase=0;
          progress(true,0,file.size);

          (async ()=>{
            try{
              const createRes = await fetch(`{{ route('multipart.create') }}`, {
                method:'POST',
                headers:{'Content-Type':'application/json','X-CSRF-TOKEN':CSRF},
                body: JSON.stringify({ fileName:file.name, contentType: inferMime(file), fileSize:file.size, prefix: PREFIX })
              });
              if(!createRes.ok) throw new Error('createMultipart failed');
              const { uploadId:uid, key:k } = await createRes.json(); uploadId=uid; key=k;
              document.getElementById('video_upload_id').value = uploadId;
              document.getElementById('video_note_direct_key').value = key;

              const totalParts = Math.ceil(file.size / CHUNK_SIZE);
              for(let partNumber=1; partNumber<=totalParts; partNumber++){
                if(cancelled) return;
                const start=(partNumber-1)*CHUNK_SIZE, end=Math.min(start+CHUNK_SIZE, file.size);
                const blob=file.slice(start,end);

                const signRes = await fetch(`{{ route('multipart.sign') }}`, {
                  method:'POST',
                  headers:{'Content-Type':'application/json','X-CSRF-TOKEN':CSRF},
                  body: JSON.stringify({ key, uploadId, partNumber })
                });
                if(!signRes.ok) throw new Error('signPart failed');
                const { url } = await signRes.json();

                await new Promise((resolve,reject)=>{
                  const xhr=new XMLHttpRequest(); currentXhr=xhr;
                  xhr.open('PUT', url, true);
                  xhr.upload.onprogress=(e)=>{ if(e.lengthComputable) progress(true, uploadedBase + e.loaded, file.size); };
                  xhr.onload=()=>{
                    currentXhr=null;
                    if(xhr.status>=200 && xhr.status<300){
                      const etag=(xhr.getResponseHeader('ETag')||'').replace(/"/g,'');
                      if(!etag) return reject(new Error('Missing ETag'));
                      parts.push({ETag:etag, PartNumber:partNumber});
                      uploadedBase += blob.size; progress(true, uploadedBase, file.size);
                      resolve();
                    } else reject(new Error('PUT failed: '+xhr.status));
                  };
                  xhr.onerror=()=>reject(new Error('Network error'));
                  xhr.onabort=()=>reject(new Error('Aborted'));
                  xhr.send(blob);
                });
              }

              const completeRes = await fetch(`{{ route('multipart.complete') }}`, {
                method:'POST',
                headers:{'Content-Type':'application/json','X-CSRF-TOKEN':CSRF},
                body: JSON.stringify({ key, uploadId, parts })
              });
              if(!completeRes.ok) throw new Error('completeMultipart failed');

              progress(true, file.size, file.size);
              load(key); // القيمة اللي حيرسلها input[name="video_attachments[]"] في الفورم
              document.getElementById('remove_video_flag').value='0';
            }catch(e){
              if(cancelled) return;
              console.error(e);
              error(e.message || 'upload failed');
              const ve = document.getElementById('video-error');
              if(ve){ ve.style.display='block'; ve.textContent='فشل رفع الفيديو. حاول من جديد.'; }
            }
          })();

          return { abort: ()=>{ cancelled=true; try{ currentXhr?.abort(); }catch{} abort(); } };
        }
      }
    });

    // أضف المعاينات للسلايدر
    pondVideo.on('addfile', (err, fileItem)=>{
      if(err || !fileItem) return;
      const blobURL = URL.createObjectURL(fileItem.file);
      addVideoItem(fileItem.id, blobURL, true);
      document.getElementById('remove_video_flag').value='0';
    });
    pondVideo.on('removefile', (fileItem)=>{ if(!fileItem) return; removeVideoItem(fileItem.id); });

    window.pondVideo = pondVideo;
    bindSaveButtonToPond(pondVideo);

    // حذف الكل
    document.querySelector('.remove-video-all')?.addEventListener('click', ()=>{
      try{ pondVideo.removeFiles(); }catch{}
      videoItems=[]; vCurrent=0; renderVideo();
      document.getElementById('remove_video_flag').value='1';
    });
  }

  /* =================== AUDIO =================== */
  (function initAudio(){
    const box = document.querySelector('[data-type="audio"]');
    if(!box) return;
    const input = box.querySelector('.media-input');
    const previewBox = box.querySelector('.preview-audio-container');
    const list = box.querySelector('.preview-audio-list') || (()=>{ const d=document.createElement('div'); d.className='preview-audio-list'; previewBox?.insertBefore(d, previewBox.firstChild); return d; })();
    const captureBtn = box.querySelector('.capture-media');
    const uploadBtn  = box.querySelector('.upload-media');
    const removeFlag = box.querySelector('.remove-flag');

    let recorder=null, chunks=[], indicator=null, stream=null, fallback=null;

    uploadBtn?.addEventListener('click', ()=> input?.click());
    input?.addEventListener('change', ()=>{
      if(!input.files?.length) return;
      Array.from(input.files).forEach(file=>{
        const audio=document.createElement('audio'); audio.controls=true; audio.className='w-100 mb-2';
        audio.src=URL.createObjectURL(file); list.appendChild(audio);
      });
      previewBox.style.display='block'; removeFlag.value='0';
    });

    captureBtn?.addEventListener('click', async ()=>{
      if ((recorder && recorder.state==='recording') || fallback){
        try{ recorder?.stop(); fallback?.stop(); }catch{}
        captureBtn.className='btn btn-sm btn-primary';
        captureBtn.innerHTML='<i class="bi bi-mic"></i> {{ __('dashboard.record') }}';
        indicator?.remove(); indicator=null; return;
      }
      try{
        stream = await navigator.mediaDevices.getUserMedia({audio:true});
        chunks=[];
        let mime='audio/webm';
        if(!MediaRecorder.isTypeSupported?.(mime)) mime = MediaRecorder.isTypeSupported?.('audio/mp4') ? 'audio/mp4' : (MediaRecorder.isTypeSupported?.('audio/aac') ? 'audio/aac' : '');

        if (mime){
          recorder = new MediaRecorder(stream,{mimeType:mime});
          recorder.ondataavailable = e=>{ if(e.data.size>0) chunks.push(e.data); };
          recorder.onstop = ()=>{
            try{ stream.getTracks().forEach(t=>t.stop()); }catch{}
            const blob=new Blob(chunks,{type:mime});
            const ext = mime.includes('mp4')?'.m4a' : (mime.includes('aac')?'.aac':'.webm');
            appendAudioFile(blob, ext, mime);
          };
          recorder.start();
        } else {
          fallback = startWavRecorder(stream, (wavBlob)=> appendAudioFile(wavBlob, '.wav', 'audio/wav'));
        }

        captureBtn.className='btn btn-sm btn-danger';
        captureBtn.innerHTML='<i class="bi bi-stop"></i> {{ __('dashboard.stop') }}';
        previewBox.style.display='block';
        indicator=document.createElement('div'); indicator.className='text-danger mt-2 small'; indicator.textContent='🔴 Recording...';
        list.appendChild(indicator);
      }catch(e){ alert('Mic error: '+(e?.message||e)); }
    });

    function appendAudioFile(blob, ext, type){
      const file = new File([blob], 'audio_'+Date.now()+ext, {type});
      const dt=new DataTransfer(); if (input.files) Array.from(input.files).forEach(f=>dt.items.add(f)); dt.items.add(file); input.files=dt.files;
      indicator?.remove(); indicator=null;
      const a=document.createElement('audio'); a.controls=true; a.className='w-100 mb-2'; a.src=URL.createObjectURL(file); list.appendChild(a);
      previewBox.style.display='block'; removeFlag.value='0';
      try{ stream?.getTracks()?.forEach(t=>t.stop()); }catch{} recorder=null; fallback=null; stream=null;
    }

    function startWavRecorder(stream, cb){
      const ctx=new (window.AudioContext||window.webkitAudioContext)();
      const src=ctx.createMediaStreamSource(stream);
      const proc=ctx.createScriptProcessor(4096,1,1);
      let buf=[]; proc.onaudioprocess=e=> buf.push(new Float32Array(e.inputBuffer.getChannelData(0)));
      src.connect(proc); proc.connect(ctx.destination);
      return { stop:()=>{ src.disconnect(); proc.disconnect(); ctx.close(); const blob=encodeWav(buf,44100); cb(blob); } };
    }
    function encodeWav(chunks, rate){
      const len=chunks.reduce((a,c)=>a+c.length,0), data=new Float32Array(len); let off=0; chunks.forEach(c=>{ data.set(c,off); off+=c.length; });
      const ab=new ArrayBuffer(44+data.length*2), v=new DataView(ab), w=(o,s)=>{ for(let i=0;i<s.length;i++) v.setUint8(o+i, s.charCodeAt(i)); };
      w(0,'RIFF'); v.setUint32(4,36+data.length*2,true); w(8,'WAVE'); w(12,'fmt '); v.setUint32(16,16,true); v.setUint16(20,1,true); v.setUint16(22,1,true);
      v.setUint32(24,rate,true); v.setUint32(28,rate*2,true); v.setUint16(32,2,true); v.setUint16(34,16,true); w(36,'data'); v.setUint32(40,data.length*2,true);
      let idx=44; for(let i=0;i<data.length;i++,idx+=2){ const s=Math.max(-1,Math.min(1,data[i])); v.setInt16(idx, s<0? s*0x8000 : s*0x7FFF, true); }
      return new Blob([v],{type:'audio/wav'});
    }

    box.querySelector('.remove-media')?.addEventListener('click', ()=>{
      previewBox.style.display='none'; list.innerHTML=''; if(input) input.value=''; removeFlag.value='1';
    });
  })();

  // تأكد أن أزرار السلايدر/اللايت بوكس ليست submit
  document.querySelectorAll('#photo-lightbox button, .photo-slider button, .video-slider button').forEach(b=>{
    if (!b.hasAttribute('type')) b.setAttribute('type','button');
  });
});
</script>
@endpush
