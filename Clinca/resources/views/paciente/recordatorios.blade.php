@extends('layouts.app')
@section('title','Recordatorios')

@section('content')
<main class="dashboard">
  <h2>Recordatorios y citas</h2>

  <section class="panel" style="max-width: 980px;">
    {{-- === Filtros directamente en el panel === --}}
    <div style="display:grid;gap:12px;grid-template-columns:1fr 1fr 1fr; margin-bottom:10px;">
      <div>
        <label>Paciente</label>
        <input id="f_paciente" readonly>
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
  // Paciente desde ?p=... o demo
  const params   = new URLSearchParams(location.search);
  const paciente = params.get('p') || 'Paciente DEMO';
  document.getElementById('f_paciente').value = paciente;


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
      const res = await fetch(`/paciente/api/reminders?patient_id=${encodeURIComponent(patientId)}`, { credentials:'same-origin', headers:{'Accept':'application/json'} });
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

  // If patient parameter provided, try remote fetch first
  const params = new URLSearchParams(location.search);
  const patient = params.get('p');
  if (patient){
    fetchRemoteReminders(patient).then(remote => {
      if (remote) {
        // normalize remote to expected fields if needed
        remote.forEach(r => { if (!r.fecha && r.date) r.fecha = r.date; if (!r.hora && r.time) r.hora = r.time; });
        render(remote);
      } else {
        applyFilters();
      }
    });
  } else {
    applyFilters();
  }
})();
</script>
@endsection
