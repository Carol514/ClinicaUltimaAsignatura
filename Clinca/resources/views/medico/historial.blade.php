@extends('layouts.app')
@section('title','Consulta de historial')

@section('content')
<main class="dashboard">
  <h2>Consulta de historial</h2>

  {{-- FILTROS --}}
  <form id="filters" class="form-container" style="margin-bottom:12px;">
    <div class="grid" style="display:grid;gap:10px;grid-template-columns:1.2fr 1fr 1fr 1fr;">
      <div>
        <label>Paciente</label>
        <input id="f_paciente" readonly placeholder="(de ?p=)">
      </div>
      <div>
        <label>Desde</label>
        <input type="date" id="f_from">
      </div>
      <div>
        <label>Hasta</label>
        <input type="date" id="f_to">
      </div>
      <div>
        <label>Tipo</label>
        <select id="f_tipo">
          <option value="">Todos</option>
          <option value="visita">Visita</option>
          <option value="signos">Signos</option>
          <option value="documento">Documento</option>
          <option value="tratamiento">Cambio de tratamiento</option>
        </select>
      </div>
    </div>

    <div class="grid" style="display:grid;gap:10px;grid-template-columns:1fr auto auto;">
      <div>
        <label>Buscar (texto)</label>
        <input id="f_q" placeholder="Ej. tórax / TA 120/80 / Azitromicina">
      </div>
      <div class="btn-container" style="align-self:end;">
        <button class="confirm-btn" id="btnBuscar" type="submit">Buscar</button>
        <button class="cancel-btn" id="btnLimpiar" type="button">Limpiar</button>
      </div>
    </div>
  </form>

  {{-- RESULTADOS --}}
  <section class="panel" style="margin-top:6px;">
    <div style="display:flex;justify-content:space-between;align-items:center;">
      <h3 style="margin:0;">Resultados</h3>
      <small id="resultMeta" class="muted">0 resultados</small>
    </div>

    <div class="table-wrapper" style="overflow:auto;margin-top:10px; border-radius:12px; border:1px solid #eee;">
      <table id="tbl" style="width:100%; border-collapse:collapse; min-width:760px;">
        <thead style="position:sticky;top:0;background:#f9fafb;z-index:1;">
          <tr>
            <th data-col="fecha" class="th-sort">Fecha/Hora ▾</th>
            <th data-col="tipo" class="th-sort">Tipo</th>
            <th data-col="detalle">Detalle</th>
            <th data-col="autor" class="th-sort">Autor</th>
            <th style="width:140px;">Acciones</th>
          </tr>
        </thead>
        <tbody id="tbody">
          <tr><td colspan="5" class="muted" style="text-align:center;padding:14px;">Sin datos. Usa “Buscar”.</td></tr>
        </tbody>
      </table>
    </div>

    {{-- Paginación (demo) --}}
    <div id="pager" style="display:flex;gap:8px;justify-content:center;margin-top:12px;">
      <button class="cancel-btn" id="prev" disabled>Anterior</button>
      <span id="pageLabel" class="muted">Página 1/1</span>
      <button class="cancel-btn" id="next" disabled>Siguiente</button>
    </div>
  </section>

  <div class="btn-container" style="margin-top:12px;">
    <a class="cancel-btn" href="{{ route('medico.panel') }}">Volver al panel</a>
  </div>
</main>

{{-- JS (demo sin backend) --}}
<script>
  // ====== Data demo ======
  const seed = [
    { fecha:'2025-11-08 10:30', tipo:'visita',      detalle:'Consulta general', autor:'Dr. Hernández' },
    { fecha:'2025-11-08 10:45', tipo:'signos',      detalle:'TA 118/76, FC 74, Temp 36.5°C', autor:'Enf. Sofía' },
    { fecha:'2025-11-08 11:00', tipo:'documento',   detalle:'Radiografía de tórax (PDF)', autor:'Recepción' },
    { fecha:'2025-11-08 11:10', tipo:'tratamiento', detalle:'De Amoxicilina → Azitromicina (observ: faringitis)', autor:'Dr. Hernández' },
    { fecha:'2025-11-07 16:00', tipo:'documento',   detalle:'Análisis de laboratorio (hemograma)', autor:'Recepción' },
    { fecha:'2025-11-06 09:15', tipo:'signos',      detalle:'TA 120/80, FC 76, SpO₂ 98%', autor:'Enf. Sofía' },
  ];

  // ====== Estado UI ======
  const pageSize = 5;
  let page = 1;
  let sortCol = 'fecha';     // fecha | tipo | autor
  let sortDir = 'desc';      // asc | desc
  let current = [];          // dataset luego de filtros

  // Paciente desde query
  const qs = new URLSearchParams(location.search);
  const paciente = qs.get('p') || 'Paciente DEMO';
  const $pac = document.getElementById('f_paciente');
  $pac.value = paciente;

  // Helpers
  function parseDate(s){ return new Date(s.replace(' ', 'T')); }

  function applyFilters(){
    const from = f_from.value ? new Date(f_from.value + 'T00:00:00') : null;
    const to   = f_to.value   ? new Date(f_to.value   + 'T23:59:59') : null;
    const tipo = f_tipo.value;
    const q    = (f_q.value || '').toLowerCase().trim();

    let rows = seed.slice();

    if (from) rows = rows.filter(r => parseDate(r.fecha) >= from);
    if (to)   rows = rows.filter(r => parseDate(r.fecha) <= to);
    if (tipo) rows = rows.filter(r => r.tipo === tipo);
    if (q)    rows = rows.filter(r =>
      r.detalle.toLowerCase().includes(q) ||
      r.autor.toLowerCase().includes(q) ||
      r.tipo.toLowerCase().includes(q)
    );

    // Orden
    rows.sort((a,b)=>{
      let va, vb;
      if (sortCol === 'fecha'){ va=parseDate(a.fecha); vb=parseDate(b.fecha); }
      else if (sortCol === 'tipo'){ va=a.tipo; vb=b.tipo; }
      else if (sortCol === 'autor'){ va=a.autor; vb=b.autor; }
      if (va<vb) return sortDir==='asc'?-1:1;
      if (va>vb) return sortDir==='asc'? 1:-1;
      return 0;
    });

    current = rows;
    page = 1;
    render();
  }

  function render(){
    const $body = document.getElementById('tbody');
    $body.innerHTML = '';
    const total = current.length;
    const totalPages = Math.max(1, Math.ceil(total / pageSize));
    page = Math.min(page, totalPages);

    if (total === 0){
      $body.innerHTML = `<tr><td colspan="5" class="muted" style="text-align:center;padding:14px;">Sin resultados con los filtros actuales.</td></tr>`;
    } else {
      const start = (page-1)*pageSize;
      current.slice(start, start+pageSize).forEach(r=>{
        const acc = actionCell(r);
        $body.insertAdjacentHTML('beforeend', `
          <tr style="border-top:1px solid #eee;">
            <td style="white-space:nowrap;padding:10px;">${r.fecha}</td>
            <td style="text-transform:capitalize;padding:10px;">${r.tipo}</td>
            <td style="padding:10px;">${r.detalle}</td>
            <td style="padding:10px;">${r.autor}</td>
            <td style="padding:10px;">${acc}</td>
          </tr>
        `);
      });
    }

    // Meta y paginación
    resultMeta.textContent = `${total} resultado${total!==1?'s':''}`;
    pageLabel.textContent  = `Página ${page}/${Math.max(1, Math.ceil(total / pageSize))}`;
    prev.disabled = (page<=1);
    next.disabled = (page>=Math.ceil(total / pageSize));
    paintSortIndicators();
  }

  function actionCell(r){
    if (r.tipo === 'documento'){
      return `<button class="confirm-btn" onclick="verDoc('${r.detalle}')">Ver doc</button>`;
    }
    return `<button class="cancel-btn" onclick="verDetalle('${r.tipo}','${r.fecha}')">Detalle</button>`;
  }

  function verDoc(titulo){
    alert('Abrir documento: ' + titulo + ' (demo)');
  }

  function verDetalle(tipo, fecha){
    alert(`Detalle (${tipo}) — ${fecha} (demo)`);
  }

  // Orden clickable
  document.querySelectorAll('.th-sort').forEach(th=>{
    th.style.cursor = 'pointer';
    th.addEventListener('click', ()=>{
      const col = th.dataset.col;
      if (sortCol === col){
        sortDir = (sortDir==='asc') ? 'desc' : 'asc';
      } else {
        sortCol = col;
        sortDir = (col==='fecha') ? 'desc' : 'asc';
      }
      render();
    });
  });

  function paintSortIndicators(){
    document.querySelectorAll('.th-sort').forEach(th=>{
      const col = th.dataset.col;
      let icon = '';
      if (col === sortCol) icon = (sortDir==='asc') ? '▴' : '▾';
      th.textContent = th.textContent.replace(/[▴▾]/g,'').trim() + ' ' + icon;
    });
  }

  // Eventos
  document.getElementById('filters').addEventListener('submit', (e)=>{
    e.preventDefault();
    applyFilters();
  });
  document.getElementById('btnLimpiar').addEventListener('click', ()=>{
    f_from.value = ''; f_to.value=''; f_tipo.value=''; f_q.value='';
    applyFilters();
  });
  prev.onclick = ()=>{ if(page>1){ page--; render(); } };
  next.onclick = ()=>{ if(page<Math.ceil(current.length/pageSize)){ page++; render(); } };

  // Init
  applyFilters();
</script>
@endsection
