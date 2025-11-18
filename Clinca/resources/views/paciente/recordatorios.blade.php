@extends('layouts.app')
@section('title','Recordatorios')

@section('content')
<main class="dashboard">
  <h2>Recordatorios y citas</h2>

  <section class="panel" style="max-width: 980px;">
    {{-- === Filtros directamente en el panel === --}}
    <div style="display:grid;gap:12px;grid-template-columns:1fr 1fr 1fr; margin-bottom:10px;">
      @php
        $patientName = '';
        $patientIdVal = '';
        if (\Illuminate\Support\Facades\Auth::check()) {
            $p = \App\Models\Patient::where('user_id', \Illuminate\Support\Facades\Auth::id())->first();
            if ($p) {
                $patientName = trim(($p->first_name ?? '') . ' ' . ($p->last_name ?? ''));
                $patientIdVal = $p->id;
            }
        }
      @endphp
      <div>
        <label>Paciente</label>
        <input id="f_paciente" readonly value="{{ $patientName }}" data-patient-id="{{ $patientIdVal }}">
      </div>
      <div>
        <label>Desde</label>
        <input id="f_desde" type="date">
      </div>
      <div>
        <label>Hasta</label>
        <input id="f_hasta" type="date">
      </div>
    </div>

    <div class="btn-container" style="margin-top:12px;margin-bottom:16px;">
      <button class="confirm-btn" id="btnBuscar">Filtrar</button>
      <button class="cancel-btn" type="reset" id="btnLimpiar">Limpiar</button>
      <a class="cancel-btn" href="{{ route('paciente.panel') }}">Volver</a>
    </div>

    {{-- === Tabla de recordatorios/citas === --}}
    <div class="table-container">
      <table>
        <thead>
          <tr>
            <th>Fecha</th>
            <th>Hora</th>
            <th>Tipo</th>
            <th>Detalle</th>
            <th>Estado</th>
          </tr>
        </thead>
        <tbody id="rows"></tbody>
      </table>
      <p id="noRows" class="muted" style="text-align:center;margin-top:10px;">No hay recordatorios en el rango.</p>
    </div>
  </section>
</main>

<script>
  (() => {
  const rows   = document.getElementById('rows');
  const noRows = document.getElementById('noRows');
  const fDesde = document.getElementById('f_desde');
  const fHasta = document.getElementById('f_hasta');

  function render(list){
    rows.innerHTML = '';
    if (!list.length){ noRows.style.display='block'; return; }
    noRows.style.display='none';
    list
      .sort((a,b)=> (a.fecha+a.hora).localeCompare(b.fecha+b.hora))
      .forEach(it=>{
        const tr = document.createElement('tr');
        tr.innerHTML = `
          <td>${it.fecha}</td>
          <td>${it.hora}</td>
          <td>${it.tipo}</td>
          <td>${it.detalle}</td>
          <td>${it.estado}</td>
        `;
        rows.appendChild(tr);
      });
  }

  async function fetchRemoteReminders(patientId){
    try{
      let url = '/paciente/api/reminders';
      if (patientId) url += '?patient_id=' + encodeURIComponent(patientId);
      const res = await fetch(url, { credentials:'same-origin', headers:{'Accept':'application/json'} });
      if (!res.ok) throw new Error('no remote');
      const json = await res.json();
      // Expect array of reminders with fecha/hora/tipo/detalle/estado
      return Array.isArray(json) ? json : [];
    }catch(e){
      console.warn('Remote reminders not available, using demo data.', e);
      return null;
    }
  }

  function applyFilters(){
    let list = DATA.slice();
    if (fDesde.value) list = list.filter(x => x.fecha >= fDesde.value);
    if (fHasta.value) list = list.filter(x => x.fecha <= fHasta.value);
    render(list);
  }

  document.getElementById('btnBuscar').onclick = (e)=>{ e.preventDefault(); applyFilters(); };
  document.getElementById('btnLimpiar').onclick = ()=>{ fDesde.value = fHasta.value = ''; applyFilters(); };

  // Determine patient id: URL ?p= takes precedence, otherwise use server-rendered patient id (data attribute)
  const params = new URLSearchParams(location.search);
  const patientParam = params.get('p');
  const patientIdAttr = document.getElementById('f_paciente').dataset.patientId || '';
  const patientToUse = patientParam || patientIdAttr || '';

  if (patientToUse) {
    fetchRemoteReminders(patientToUse).then(remote => {
      if (remote) {
        remote.forEach(r => { if (!r.fecha && r.date) r.fecha = r.date; if (!r.hora && r.time) r.hora = r.time; });
        render(remote);
      } else {
        applyFilters();
      }
    });
  } else {
    // Try fetching for authenticated patient (no patient_id param)
    fetchRemoteReminders().then(remote => {
      if (remote) {
        remote.forEach(r => { if (!r.fecha && r.date) r.fecha = r.date; if (!r.hora && r.time) r.hora = r.time; });
        render(remote);
      } else {
        applyFilters();
      }
    });
  }
})();
</script>
@endsection
