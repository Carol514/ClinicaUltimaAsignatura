@extends('layouts.app')
@section('title','Consulta de historial')

@section('content')
<main class="dashboard">

  <h2>Consulta de historial</h2>

<section class="panel filtros" style="max-width:980px;margin-inline:auto;">
  <div class="filtros-grid">
    <div>
      <label>Paciente</label>
      <input id="f_paciente" value="" placeholder="Nombre del paciente">
    </div>
    <div>
      <label>Desde</label>
      <input id="f_desde" type="date">
    </div>
    <div>
      <label>Hasta</label>
      <input id="f_hasta" type="date">
    </div>
    <div>
      <label>Tipo</label>
      <select id="f_tipo">
        <option value="">Todos</option>
        <option>Visita</option>
        <option>Signos</option>
        <option>Documento</option>
        <option>Tratamiento</option>
      </select>
    </div>

    <!-- fila 2: buscador + botones (todo dentro del mismo panel) -->
    <div class="span-3">
      <label>Buscar (texto)</label>
      <input id="f_texto" placeholder="Ej. tórax / TA 120/80 / Azitro">
    </div>
    <div class="acciones">
      <button id="btnBuscar" class="confirm-btn" type="button">Buscar</button>
      <button id="btnLimpiar" class="cancel-btn" type="button">Limpiar</button>
    </div>
  </div>
</section>


  {{-- RESULTADOS --}}
  <section class="panel" style="max-width:1100px;margin:14px auto 0">
    <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:8px">
      <h3 style="margin:0">Resultados</h3>
      <small id="count" class="muted"></small>
    </div>

    <div style="overflow-x:auto">
      <table style="width:100%;border-collapse:collapse">
        <thead>
          <tr style="background:#7bc3ab;color:#fff">
            <th style="text-align:left;padding:10px 12px;white-space:nowrap">Fecha/Hora</th>
            <th style="text-align:left;padding:10px 12px">Tipo</th>
            <th style="text-align:left;padding:10px 12px">Detalle</th>
            <th style="text-align:left;padding:10px 12px">Autor</th>
            <th style="text-align:left;padding:10px 12px">Acciones</th>
          </tr>
        </thead>
        <tbody id="tbody"></tbody>
      </table>
      <a class="btn-secondary" id="volverBtn" href="{{ route('medico.panel') }}">Volver</a>
    </div>
  </section>
</main>

<script>
  const tbody = document.getElementById('tbody');
  const count = document.getElementById('count');

  async function resolvePatientInfo(p){
    if (!p) return null;
    
    // If it looks like a UUID, fetch patient info by ID
    if (/^[0-9a-f]{8}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{12}$/i.test(p)) {
      try{
        const r = await fetch(`/medico/api/patients?query=${encodeURIComponent(p)}`, { credentials:'same-origin', headers:{'Accept':'application/json'} });
        if (!r.ok) return null;
        const list = await r.json();
        return (list && list.length) ? list[0] : null;
      }catch(e){ return null; }
    }
    
    // Otherwise, search by name
    try{
      const r = await fetch(`/medico/api/patients?query=${encodeURIComponent(p)}`, { credentials:'same-origin', headers:{'Accept':'application/json'} });
      if (!r.ok) return null;
      const list = await r.json();
      return (list && list.length) ? list[0] : null;
    }catch(e){ return null; }
  }

  async function fetchRemote(patientId){
    try{
      const res = await fetch(`/medico/api/history?patient_id=${encodeURIComponent(patientId)}`, { credentials:'same-origin', headers:{ 'Accept':'application/json' } });
      if (!res.ok) throw new Error('no remote');
      const json = await res.json();
      return Array.isArray(json) ? json : [];
    }catch(e){
      console.warn('Remote history not available, using demo data.', e);
      return null; // signal fallback
    }
  }

  function render(list){
    tbody.innerHTML = '';
    if (!list || !list.length){
      count.textContent = '0 resultados';
      tbody.innerHTML = '<tr><td colspan="5" style="padding:12px;text-align:center" class="muted">Sin registros</td></tr>';
      return;
    }
    list.forEach(r=>{
      const tr = document.createElement('tr');
      const ts = (r.fecha ? r.fecha : (r.ts || '')) + (r.hora ? (' ' + r.hora) : '');
      tr.innerHTML = `
        <td style="padding:10px 12px;border-bottom:1px solid #eee;white-space:nowrap">${ts}</td>
        <td style="padding:10px 12px;border-bottom:1px solid #eee">${r.tipo || ''}</td>
        <td style="padding:10px 12px;border-bottom:1px solid #eee">${r.detalle || ''}</td>
        <td style="padding:10px 12px;border-bottom:1px solid #eee">${r.autor || '—'}</td>
        <td style="padding:10px 12px;border-bottom:1px solid #eee">&nbsp;</td>`;
      tbody.appendChild(tr);
    });
    count.textContent = `${list.length} resultado${list.length!==1?'s':''}`;
  }

  // Filters will be applied client-side on the returned dataset
  function filtrar(list){
    const texto = (document.getElementById('f_texto').value||'').toLowerCase();
    const tipo  = document.getElementById('f_tipo').value;
    const d1    = document.getElementById('f_desde').value;
    const d2    = document.getElementById('f_hasta').value;

    let out = (list || []).filter(r=>{
      const okTipo = !tipo || (r.tipo && r.tipo===tipo);
      const okTxt  = !texto || ((r.detalle||'').toLowerCase().includes(texto) || (r.autor||'').toLowerCase().includes(texto));
      const date   = (r.fecha || '').slice(0,10);
      const okD1   = !d1 || date >= d1;
      const okD2   = !d2 || date <= d2;
      return okTipo && okTxt && okD1 && okD2;
    });

    render(out);
  }

  document.getElementById('btnBuscar').onclick = async ()=>{
    const p = new URLSearchParams(location.search).get('p');
    const patientInfo = await resolvePatientInfo(p);
    const remote = patientInfo ? await fetchRemote(patientInfo.id) : null;
    filtrar(remote);
  };
  document.getElementById('btnLimpiar').onclick = ()=>{
    ['f_texto','f_paciente'].forEach(id=>document.getElementById(id).value='');
    document.getElementById('f_tipo').value='';
    document.getElementById('f_desde').value='';
    document.getElementById('f_hasta').value='';
    // Try to render remote again if possible
    (async ()=>{
      const p = new URLSearchParams(location.search).get('p');
      const patientInfo = await resolvePatientInfo(p);
      const remote = patientInfo ? await fetchRemote(patientInfo.id) : null;
      if (remote) render(remote); else render([]);
    })();
  };

  // On load, attempt remote if ?p= provided
  (async ()=>{
    const p = new URLSearchParams(location.search).get('p');
    if (p){
      const patientInfo = await resolvePatientInfo(p);
      if (patientInfo) {
        // Set the patient's full name in the filter textbox
        const fullName = patientInfo.name || '';
        document.getElementById('f_paciente').value = fullName;
        
        const remote = await fetchRemote(patientInfo.id);
        if (remote) render(remote); else render([]);
      } else {
        // If we can't resolve patient info, just show the parameter as-is
        document.getElementById('f_paciente').value = p;
        render([]);
      }
    } else {
      render([]);
    }
  })();
</script>
@endsection
