@extends('layouts.app')
@section('title','Agenda')

@section('content')
<main class="dashboard">
  <h2>Agenda</h2>

  <section class="panel">
    {{-- FILTROS --}}
    <form class="form-container agenda-filtros" onsubmit="return false;">
      <div class="fields">
        <div class="field">
          <label for="desde">Desde</label>
          <input type="date" id="desde">
        </div>

        <div class="field">
          <label for="hasta">Hasta</label>
          <input type="date" id="hasta">
        </div>

        <div class="field">
          <label for="doc">Doctor</label>
          <select id="doc">
            <option value="">Todos</option>
            <option>Dr. Hernández</option>
            <option>Dra. López</option>
            <option>Dr. Ramírez</option>
          </select>
        </div>

        <div class="field">
          <label for="q">Paciente</label>
          <input id="q" placeholder="Nombre / motivo">
        </div>
      </div>

      <div class="btn-container acciones">
        <button id="btnBuscar" class="confirm-btn">Buscar</button>
        <button id="btnLimpiar" class="cancel-btn" type="button">Limpiar</button>
        <a href="{{ route('recepcionista.panel') }}" class="cancel-btn">Volver</a>
      </div>
    </form>

    {{-- TABLA --}}
    <div class="table-container" style="margin-top:18px;">
      <table>
        <thead>
          <tr>
            <th>Fecha</th>
            <th>Hora</th>
            <th>Paciente</th>
            <th>Doctor</th>
            <th>Motivo</th>
            <th>Estado</th>
            <th>Acciones</th>
          </tr>
        </thead>
        <tbody id="rows"></tbody>
      </table>
      <p id="noRows" class="muted" style="text-align:center;margin-top:10px;">Sin resultados.</p>
    </div>
  </section>
</main>

<script>
(() => {
  const sample = [
    {fecha:'2025-11-10', hora:'09:00',  pac:'Ana Pérez',      doc:'Dr. Hernández', motivo:'Control',     estado:'Programada'},
    {fecha:'2025-11-10', hora:'10:30',  pac:'Luis Mora',      doc:'Dra. López',    motivo:'Resultados',  estado:'Programada'},
    {fecha:'2025-11-11', hora:'11:00',  pac:'Paciente DEMO',  doc:'Dr. Ramírez',   motivo:'Dolor',       estado:'Reprogramada'},
  ];

  const rows   = document.getElementById('rows');
  const noRows = document.getElementById('noRows');
  const desde  = document.getElementById('desde');
  const hasta  = document.getElementById('hasta');
  const doc    = document.getElementById('doc');
  const q      = document.getElementById('q');

  function render(list){
    rows.innerHTML = '';
    if (!list.length){ noRows.style.display='block'; return; }
    noRows.style.display='none';
    list.forEach(it=>{
      const tr = document.createElement('tr');
      tr.innerHTML = `
        <td>${it.fecha}</td>
        <td>${it.hora}</td>
        <td>${it.pac}</td>
        <td>${it.doc}</td>
        <td>${it.motivo}</td>
        <td>${it.estado}</td>
        <td>
          <button class="btn-secondary" onclick="alert('Marcar llegada (demo)')">Llegó</button>
          <button class="btn-secondary" onclick="alert('Reprogramar (demo)')">Reprog.</button>
        </td>
      `;
      rows.appendChild(tr);
    });
  }

  function applyFilters(){
    let list = sample.slice();
    if (desde.value) list = list.filter(x => x.fecha >= desde.value);
    if (hasta.value) list = list.filter(x => x.fecha <= hasta.value);
    if (doc.value)   list = list.filter(x => x.doc === doc.value);
    if (q.value.trim()){
      const s = q.value.toLowerCase();
      list = list.filter(x => (x.pac + ' ' + x.motivo).toLowerCase().includes(s));
    }
    render(list);
  }

  document.getElementById('btnBuscar').onclick = applyFilters;
  document.getElementById('btnLimpiar').onclick = () => {
    desde.value = hasta.value = ''; doc.value = ''; q.value = '';
    applyFilters();
  };

  applyFilters();
})();
</script>
@endsection
