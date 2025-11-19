@extends('layouts.app')
@section('title','Subir documentos')

@section('content')
<main class="dashboard">
  <h2>Subir documentos</h2>

  {{-- CONTEXTO --}}
  <div class="form-container" style="margin-bottom:10px;">
    <label>Paciente</label>
    <input id="docPaciente" readonly placeholder="(de ?p=)">
    <p class="muted" style="margin:6px 0 0;">Pasamos el paciente por URL con <code>?p=Nombre</code>.</p>
  </div>

  {{-- FORMULARIO --}}
  <form id="docs-form" class="form-container panel" method="POST" enctype="multipart/form-data">
    {{-- Fila: Tipo / Título --}}
    <div class="grid-2">
      <div>
        <label for="docTipo">Tipo</label>
        <select id="docTipo" required>
          <option value="" disabled selected>Seleccione…</option>
          <option value="radiografia">Radiografía</option>
          <option value="analisis">Análisis</option>
          <option value="receta">Receta</option>
          <option value="referencia">Referencia</option>
          <option value="otro">Otro</option>
        </select>
      </div>
      <div>
        <label for="docTitulo">Título</label>
        <input id="docTitulo" placeholder="Ej. Radiografía de tórax" required>
      </div>
    </div>

    {{-- DROPZONE --}}
    <label for="docFiles" style="margin-top:10px;">Archivo(s)</label>
    <div id="dropzone"
         style="border:2px dashed #cfe6de;border-radius:12px;padding:18px;text-align:center;cursor:pointer;background:#f7fbfa;">
      Arrastra y suelta archivos aquí o <b>haz clic</b> para seleccionarlos.
      <input id="docFiles" type="file" multiple style="display:none"
             accept=".pdf,.png,.jpg,.jpeg,.gif,.webp,.svg,.txt,.csv,.doc,.docx,.xls,.xlsx">
    </div>
    <small class="muted">Acepta PDF, imágenes y documentos comunes. Máx 10 MB por archivo.</small>

    {{-- PREVIEW LIST --}}
    <div id="preview" class="list-container" style="margin-top:12px;">
      <p class="muted">Sin archivos seleccionados.</p>
    </div>

    {{-- ACCIONES --}}
    <div class="acciones" style="margin-top:12px;">
      <button style="background-color:#7bc3ab; color:white;" class="btn-secondary" type="submit">Subir documentos</button>
      <button class="btn-secondary" type="button" id="btnReset">Limpiar</button>
      <a class="btn-secondary" id="volverBtn" href="{{ route('medico.panel') }}">Volver</a>
    </div>

    {{-- PROGRESO --}}
    <div id="progressWrap" style="display:none;margin-top:10px;">
      <div style="height:10px;background:#e9f4f0;border-radius:8px;overflow:hidden;">
        <div id="progressBar" style="height:100%;width:0%;background:#7bc3ab;transition:width .2s;"></div>
      </div>
      <small id="progressText" class="muted">0%</small>
    </div>
  </form>

  {{-- LISTA DE DOCUMENTOS --}}
  <section class="panel" style="margin-top:16px;">
    <h3>Documentos del paciente</h3>
    <div id="docs" class="list-container">
      <p class="muted">Aún no hay documentos.</p>
    </div>
  </section>
</main>

<script>
  // ------ Elementos
  const docPaciente = document.getElementById('docPaciente');
  const docsForm    = document.getElementById('docs-form');
  const docTipo     = document.getElementById('docTipo');
  const docTitulo   = document.getElementById('docTitulo');
  const drop        = document.getElementById('dropzone');
  const inputFiles  = document.getElementById('docFiles');
  const preview     = document.getElementById('preview');
  const docs        = document.getElementById('docs');
  const barW        = document.getElementById('progressBar');
  const barT        = document.getElementById('progressText');
  const barWrap     = document.getElementById('progressWrap');

  // ------ Estado inicial
  const p = new URLSearchParams(location.search).get('p') || '';
  docPaciente.value = p;
  
  // Update Volver button to preserve patient context
  if (p) {
    const volverBtn = document.getElementById('volverBtn');
    if (volverBtn) {
      volverBtn.href = `{{ route('medico.panel') }}?p=${encodeURIComponent(p)}`;
    }
  }
  
  let queue = []; // {file, id, name, size, type}
  const MAX_SIZE = 10 * 1024 * 1024; // 10 MB

  // ------ Helpers
  function fmtBytes(b){
    const u = ['B','KB','MB','GB']; let i=0; while(b>=1024 && i<u.length-1){ b/=1024; i++; }
    return b.toFixed(i===0?0:1) + ' ' + u[i];
  }
  function iconFor(type, name){
    const ext = (name.split('.').pop() || '').toLowerCase();
    if (type?.startsWith('image/')) return '🖼️';
    if (ext==='pdf') return '📄';
    if (/(doc|docx)/.test(ext)) return '📝';
    if (/(xls|xlsx|csv)/.test(ext)) return '📊';
    if (ext==='txt') return '📃';
    return '📁';
  }
  function renderPreview(){
    preview.innerHTML = '';
    if (!queue.length){
      preview.innerHTML = '<p class="muted">Sin archivos seleccionados.</p>';
      return;
    }
    queue.forEach(item=>{
      const row = document.createElement('div');
      row.className = 'list-item';
      row.style.display = 'flex';
      row.style.justifyContent = 'space-between';
      row.style.alignItems = 'center';
      row.innerHTML = `
        <div style="display:flex;gap:10px;align-items:center;">
          <span style="font-size:20px">${iconFor(item.type, item.name)}</span>
          <div>
            <div><strong>${item.name}</strong></div>
            <small class="muted">${fmtBytes(item.size)} · ${item.type || 'archivo'}</small>
          </div>
        </div>
        <button class="btn-secondary" data-id="${item.id}">Quitar</button>
      `;
      preview.appendChild(row);
    });
    preview.querySelectorAll('button[data-id]').forEach(btn=>{
      btn.onclick = ()=>{
        queue = queue.filter(x=> x.id !== btn.dataset.id);
        renderPreview();
      };
    });
  }
  function validateFile(file){
    if (file.size > MAX_SIZE) return `Archivo muy grande (${fmtBytes(file.size)}). Máximo 10MB.`;
    return null;
  }
  function addFiles(files){
    let skipped=[];
    [...files].forEach(f=>{
      const err = validateFile(f);
      if (err){ skipped.push(`${f.name}: ${err}`); return; }
      queue.push({ file:f, id:crypto.randomUUID(), name:f.name, size:f.size, type:f.type });
    });
    renderPreview();
    if (skipped.length) alert('Algunos archivos se omitieron:\n\n' + skipped.join('\n'));
  }

  // ------ Drag & drop / click
  drop.addEventListener('click', ()=> inputFiles.click());
  drop.addEventListener('dragover', (e)=>{ e.preventDefault(); drop.style.background='#eef7f3'; });
  drop.addEventListener('dragleave', ()=>{ drop.style.background='#f7fbfa'; });
  drop.addEventListener('drop', (e)=>{
    e.preventDefault();
    drop.style.background='#f7fbfa';
    addFiles(e.dataTransfer.files);
  });
  inputFiles.addEventListener('change', ()=> addFiles(inputFiles.files));

  // ------ Subida (real -> POST to medico/api/documentos) with FormData and progress
  docsForm.addEventListener('submit', async (e)=>{
    e.preventDefault();
    if (!docPaciente.value.trim()){ alert('Indica el paciente (en la URL ?p=...)'); return; }
    if (!docTipo.value){ alert('Selecciona el tipo'); return; }
    if (!docTitulo.value.trim()){ alert('Escribe un título'); return; }
    if (!queue.length){ alert('Agrega al menos un archivo'); return; }

    // resolve patient id (name or id)
    const pid = await resolvePatientId(docPaciente.value.trim());
    if (!pid){ return alert('No se pudo resolver el paciente.'); }

    const form = new FormData();
    form.append('patient_id', pid);
    form.append('title', docTitulo.value.trim());
    form.append('doc_type', docTipo.value);
    queue.forEach((it, idx)=> form.append('files[]', it.file, it.name));

    // CSRF token if available
    const tokenMeta = document.querySelector('meta[name="csrf-token"]');
    const headers = {};
    if (tokenMeta) headers['X-CSRF-TOKEN'] = tokenMeta.getAttribute('content');

    // Use XHR to get progress events
    const xhr = new XMLHttpRequest();
    xhr.open('POST', '/medico/api/documentos', true);
    xhr.withCredentials = true;
    Object.entries(headers).forEach(([k,v])=> xhr.setRequestHeader(k, v));

    xhr.upload.onprogress = function(evt){
      if (evt.lengthComputable){
        const pct = Math.round((evt.loaded/evt.total)*100);
        barWrap.style.display = 'block'; barW.style.width = pct + '%'; barT.textContent = pct + '%';
      }
    };

    xhr.onload = function(){
      try{
        const res = JSON.parse(xhr.responseText || '{}');
        if (xhr.status >=200 && xhr.status <300){
          // render saved documents returned by backend
          if (res.saved && res.saved.length){ 
            // Add new documents to the list
            const currentDocs = [];
            docs.querySelectorAll('.list-item').forEach(item => {
              if (!item.querySelector('.muted')) currentDocs.push(item);
            });
            renderDocs(res.saved.concat(currentDocs));
          }
          alert('✅ Documentos subidos exitosamente.');
          queue = []; renderPreview(); docTitulo.value='';
          // Refresh the document list
          setTimeout(async () => {
            const pid = await resolvePatientId(docPaciente.value.trim());
            if (pid) fetchDocsForPatient(pid);
          }, 500);
        } else {
          const errorMsg = res.error || 'Error del servidor';
          alert('❌ Error al subir documentos: ' + errorMsg);
        }
      }catch(err){
        console.error('Upload failed:', err);
        alert('❌ Error al subir documentos. Verifique su conexión.');
      } finally {
        setTimeout(()=>{ barWrap.style.display='none'; barW.style.width='0%'; barT.textContent='0%'; }, 700);
      }
    };

    xhr.onerror = function(){
      console.error('Network error during upload');
      alert('❌ Error de conexión. Verifique su conexión a internet.');
      setTimeout(()=>{ barWrap.style.display='none'; barW.style.width='0%'; barT.textContent='0%'; }, 700);
    };

    xhr.send(form);
  });



  // ------ Limpiar
  document.getElementById('btnReset').addEventListener('click', ()=>{
    queue = [];
    renderPreview();
    docTipo.value = ''; docTitulo.value = '';
  });

  // Attempt to load documents for patient id (resolve name -> id if needed)
  async function resolvePatientId(p){
    if (!p) return null;
    if (/^\d+$/.test(p)) return p;
    try{
      const r = await fetch(`/medico/api/patients?query=${encodeURIComponent(p)}`, { credentials:'same-origin', headers:{'Accept':'application/json'} });
      if (!r.ok) return null;
      const list = await r.json();
      return (list && list.length) ? list[0].id : null;
    }catch(e){ return null; }
  }

  async function fetchDocsForPatient(pid){
    try{
      const res = await fetch(`/medico/api/documentos?patient_id=${encodeURIComponent(pid)}`, { credentials:'same-origin', headers:{'Accept':'application/json'} });
      if (!res.ok) throw new Error('no remote');
      const arr = await res.json();
      renderDocs(arr);
    }catch(e){ console.warn('Could not fetch remote documents', e); }
  }

  function renderDocs(list){
    docs.innerHTML = '';
    if (!list || !list.length){ docs.innerHTML = '<p class="muted">Aún no hay documentos.</p>'; return; }
    list.forEach(d=>{
      const row = document.createElement('div');
      row.className = 'list-item';
      const when = d.created_at ? d.created_at.slice(0,16).replace('T',' ') : '';
      const fileIcon = getFileIcon(d.doc_type, d.title);
      row.innerHTML = `
        <div style="display:flex;gap:10px;align-items:center;">
          <span style="font-size:18px">${fileIcon}</span>
          <div>
            <div><strong>${d.title || d.doc_type || 'Documento'}</strong></div>
            <small class="muted">${when} · ${d.uploader_name || 'Usuario'}</small>
          </div>
        </div>
        <div>
          <a class="btn-secondary" href="/medico/api/documentos/${d.id}/download" target="_blank">Ver</a>
        </div>
      `;
      docs.appendChild(row);
    });
  }
  
  function getFileIcon(docType, title) {
    const type = (docType || '').toLowerCase();
    const name = (title || '').toLowerCase();
    
    if (type === 'pdf' || name.includes('.pdf')) return '📄';
    if (type === 'radiografia' || name.includes('radio')) return '🩻';
    if (type === 'analisis' || name.includes('análisis')) return '🧪';
    if (type === 'receta' || name.includes('receta')) return '💊';
    if (name.includes('.jpg') || name.includes('.png') || name.includes('.jpeg')) return '🖼️';
    if (name.includes('.doc') || name.includes('.docx')) return '📝';
    if (name.includes('.xls') || name.includes('.xlsx')) return '📊';
    return '📄';
  }

  (async ()=>{
    const p0 = new URLSearchParams(location.search).get('p');
    if (!p0) return;
    const pid = await resolvePatientId(p0);
    if (pid) fetchDocsForPatient(pid);
  })();
</script>
@endsection
