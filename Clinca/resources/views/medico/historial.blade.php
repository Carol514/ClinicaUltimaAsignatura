@extends('layouts.app')
@section('title','Consulta de historial')

@section('content')
<main class="dashboard">

  <h2>Consulta de historial</h2>

<section class="panel filtros" style="max-width:980px;margin-inline:auto;">
  <div class="filtros-grid">
    <div>
      <label>Paciente</label>
      <input id="f_paciente" value="Paciente DEMO" placeholder="Nombre del paciente">
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
    </div>
  </section>
</main>

<script>
  // Demo de datos
  const rows = [
    {ts:'2025-11-08 11:10', tipo:'Tratamiento', detalle:'De Amoxicilina → Azitromicina (observ: faringitis)', autor:'Dr. Hernández', action:'Detalle'},
    {ts:'2025-11-08 11:00', tipo:'Documento',  detalle:'Radiografía de tórax (PDF)', autor:'Recepción', action:'Ver doc'},
    {ts:'2025-11-08 10:45', tipo:'Signos',      detalle:'TA 118/76, FC 74, Temp 36.5°C', autor:'Enf. Sofía', action:'Detalle'},
    {ts:'2025-11-08 10:30', tipo:'Visita',      detalle:'Consulta general', autor:'Dr. Hernández', action:'Detalle'},
  ];

  const tbody = document.getElementById('tbody');
  const count = document.getElementById('count');

  function render(list){
    tbody.innerHTML = '';
    list.forEach(r=>{
      const tr = document.createElement('tr');
      tr.innerHTML = `
        <td style="padding:10px 12px;border-bottom:1px solid #eee;white-space:nowrap">${r.ts}</td>
        <td style="padding:10px 12px;border-bottom:1px solid #eee">${r.tipo}</td>
        <td style="padding:10px 12px;border-bottom:1px solid #eee">${r.detalle}</td>
        <td style="padding:10px 12px;border-bottom:1px solid #eee">${r.autor}</td>
        <td style="padding:10px 12px;border-bottom:1px solid #eee">
          <button class="cancel-btn" onclick="alert('${r.action} (demo)')">${r.action}</button>
        </td>`;
      tbody.appendChild(tr);
    });
    count.textContent = `${list.length} resultado${list.length!==1?'s':''}`;
  }

  function filtrar(){
    const texto = (document.getElementById('f_texto').value||'').toLowerCase();
    const tipo  = document.getElementById('f_tipo').value;
    const d1    = document.getElementById('f_desde').value;
    const d2    = document.getElementById('f_hasta').value;

    const out = rows.filter(r=>{
      const okTipo = !tipo || r.tipo===tipo;
      const okTxt  = !texto || (r.detalle.toLowerCase().includes(texto) || r.autor.toLowerCase().includes(texto));
      const date   = r.ts.slice(0,10);
      const okD1   = !d1 || date >= d1;
      const okD2   = !d2 || date <= d2;
      return okTipo && okTxt && okD1 && okD2;
    });

    render(out);
  }

  document.getElementById('btnBuscar').onclick = filtrar;
  document.getElementById('btnLimpiar').onclick = ()=>{
    ['f_texto','f_paciente'].forEach(id=>document.getElementById(id).value='');
    document.getElementById('f_tipo').value='';
    document.getElementById('f_desde').value='';
    document.getElementById('f_hasta').value='';
    render(rows);
  };

  render(rows);
</script>
@endsection
