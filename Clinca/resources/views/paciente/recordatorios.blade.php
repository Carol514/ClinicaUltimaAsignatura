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

  // Datos DEMO
  const DATA = [
    { fecha:'2025-11-10', hora:'09:00', tipo:'Cita',        detalle:'Consulta con Dra. López', estado:'Programada' },
    { fecha:'2025-11-10', hora:'20:00', tipo:'Medicamento', detalle:'Tomar Metformina 850 mg', estado:'Pendiente'  },
    { fecha:'2025-11-11', hora:'08:00', tipo:'Estudio',     detalle:'Análisis de sangre',       estado:'Programada' },
    { fecha:'2025-11-12', hora:'07:30', tipo:'Medicamento', detalle:'Tomar Enalapril 10 mg',    estado:'Pendiente'  },
  ];

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

  function applyFilters(){
    let list = DATA.slice();
    if (fDesde.value) list = list.filter(x => x.fecha >= fDesde.value);
    if (fHasta.value) list = list.filter(x => x.fecha <= fHasta.value);
    render(list);
  }

  document.getElementById('btnBuscar').onclick = (e)=>{ e.preventDefault(); applyFilters(); };
  document.getElementById('btnLimpiar').onclick = ()=>{ fDesde.value = fHasta.value = ''; applyFilters(); };

  applyFilters();
})();
</script>
@endsection
