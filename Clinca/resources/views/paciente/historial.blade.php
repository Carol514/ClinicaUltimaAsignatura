@extends('layouts.app')
@section('title','Mi historial')

@section('content')
<main class="dashboard">
  <h2>Mi historial</h2>

  <section class="panel" style="max-width: 1000px;">
    <div class="table-container" style="margin-top:10px;">
      <table>
        <thead>
          <tr>
            <th>Fecha</th>
            <th>Evento</th>
            <th>Detalle</th>
          </tr>
        </thead>
        <tbody id="rows"></tbody>
      </table>
      <p id="noRows" class="muted" style="text-align:center;margin-top:10px;">
        Sin registros en el historial.
      </p>
    </div>
  </section>
</main>

<script>
(() => {
  const DATA = [
    {fecha:'2025-11-04', tipo:'Consulta',    detalle:'Valoración general'},
    {fecha:'2025-11-05', tipo:'Signos',      detalle:'TA 118/76 · Temp 36.8 °C · SpO₂ 98%'},
    {fecha:'2025-11-06', tipo:'Documento',   detalle:'Radiografía de tórax (PDF)'},
    {fecha:'2025-11-06', tipo:'Tratamiento', detalle:'Cambio: Amoxicilina → Azitromicina'},
  ];

  const rows   = document.getElementById('rows');
  const noRows = document.getElementById('noRows');

  function render(list){
    rows.innerHTML = '';
    if (!list.length){ noRows.style.display = 'block'; return; }
    noRows.style.display = 'none';

    list
      .sort((a,b)=> a.fecha.localeCompare(b.fecha))
      .forEach(it => {
        const tr = document.createElement('tr');
        tr.innerHTML = `
          <td>${it.fecha}</td>
          <td>${it.tipo}</td>
          <td>${it.detalle}</td>
        `;
        rows.appendChild(tr);
      });
  }

  render(DATA);
})();
</script>
@endsection
