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
  <form id="docs-form" class="form-container form-docs" enctype="multipart/form-data">
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
      <button class="btn" type="submit">Subir documentos</button>
      <button class="btn-secondary" type="button" id="btnReset">Limpiar</button>
      <a class="btn-secondary" href="{{ route('medico.panel') }}">Volver</a>
    </div>

    {{-- PROGRESO (demo) --}}
    <div id="progressWrap" style="display:none;margin-top:10px;">
      <div style="height:10px;background:#e9f4f0;border-radius:8px;overflow:hidden;">
        <div id="progressBar" style="height:100%;width:0%;background:#7bc3ab;transition:width .2s;"></div>
      </div>
      <small id="progressText" class="muted">0%</small>
    </div>
  </form>

  {{-- LISTA DE SUBIDOS (demo) --}}
  <section class="panel" style="margin-top:16px;">
    <h3>Subidos</h3>
    <div id="docs" class="list-container">
      <p class="muted">Aún no hay documentos.</p>
    </div>
  </section>
</main>

{{-- JS (demo sin backend) --}}
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

  // ------ Subida (demo)
  docsForm.addEventListener('submit', async (e)=>{
    e.preventDefault();
    if (!docPaciente.value.trim()){ alert('Indica el paciente (en la URL ?p=...)'); return; }
    if (!docTipo.value){ alert('Selecciona el tipo'); return; }
    if (!docTitulo.value.trim()){ alert('Escribe un título'); return; }
    if (!queue.length){ alert('Agrega al menos un archivo'); return; }

    barWrap.style.display = 'block';
    barW.style.width = '0%'; barT.textContent = '0%';

    for (let i=0;i<queue.length;i++){
      await fakeProgress((i+1)/queue.length);
      addToDocs(queue[i]);
    }

    queue = [];
    renderPreview();
    barW.style.width = '100%'; barT.textContent = '100%';
    setTimeout(()=>{ barWrap.style.display='none'; barW.style.width='0%'; barT.textContent='0%'; }, 700);
    alert('✅ Documentos subidos (demo).');

    docTipo.value = ''; docTitulo.value = '';
  });

  function addToDocs(item){
    if (docs.querySelector('.muted')) docs.innerHTML = '';
    const row = document.createElement('div');
    row.className = 'list-item';
    row.style.display = 'flex';
    row.style.justifyContent = 'space-between';
    row.style.alignItems = 'center';
    const when = new Date().toLocaleString();
    row.innerHTML = `
      <div style="display:flex;gap:10px;align-items:center;">
        <span style="font-size:18px">${iconFor(item.type, item.name)}</span>
        <div>
          <div><strong>${docTitulo.value || '(sin título)'}</strong> — <span class="muted">${docTipo.options[docTipo.selectedIndex]?.text || 'Documento'}</span></div>
          <small class="muted">${docPaciente.value} · ${when} · ${item.name}</small>
        </div>
      </div>
      <button class="btn-secondary" onclick="alert('Abrir ${item.name} (demo)')">Ver</button>
    `;
    docs.prepend(row);
  }

  function fakeProgress(overall){
    return new Promise(res=>{
      let pct = Math.round((overall-0.1)*100);
      pct = Math.max(0, Math.min(pct,100));
      const start = parseInt(barW.style.width||'0');
      let cur = start;
      const t = setInterval(()=>{
        cur += 3;
        if (cur >= pct){ cur = pct; clearInterval(t); res(); }
        barW.style.width = cur + '%'; barT.textContent = cur + '%';
      }, 20);
    });
  }

  // ------ Limpiar
  document.getElementById('btnReset').addEventListener('click', ()=>{
    queue = [];
    renderPreview();
    docTipo.value = ''; docTitulo.value = '';
  });
</script>
@endsection
