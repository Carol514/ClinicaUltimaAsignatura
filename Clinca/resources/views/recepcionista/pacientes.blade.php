@extends('layouts.app')
@section('title','Pacientes')

@section('content')
<main class="dashboard">
  <h2>Pacientes</h2>

  <section class="panel" style="max-width:1000px;">
    <form class="form-container agenda-filtros" onsubmit="return false;">
      <div class="fields">
        <div class="field">
          <label>Buscar</label>
          <input id="q" placeholder="Nombre / CURP / teléfono">
        </div>
        <div class="field">
          <label>Estado</label>
          <select id="estado">
            <option value="">Todos</option>
            <option value="activo">Activo</option>
            <option value="inactivo">Inactivo</option>
          </select>
        </div>
      </div>

      <div class="btn-container acciones">
        <button id="btnBuscar" class="confirm-btn" type="button">Buscar</button>
        <button id="btnLimpiar" class="cancel-btn" type="button">Limpiar</button>
        <a href="{{ route('recepcionista.panel') }}" class="cancel-btn">Volver</a>
        <a href="{{ route('recepcion.paciente.form') }}" class="confirm-btn" style="margin-left:auto">+ Nuevo</a>
      </div>
    </form>

    <div class="table-container" style="margin-top:18px;">
      <table>
        <thead>
          <tr>
            <th>Nombre</th>
            <th>CURP</th>
            <th>Teléfono</th>
            <th>Correo</th>
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
  // Mock de pacientes
  const data = [
    { id:'p1', nombre:'Ana Pérez',      curp:'PEAA900101MDF', tel:'322-111-2233', mail:'ana@demo.com', estado:'activo' },
    { id:'p2', nombre:'Luis Mora',      curp:'MORL850202HDF', tel:'322-222-3344', mail:'luis@demo.com', estado:'activo' },
    { id:'p3', nombre:'Paciente DEMO',  curp:'DEMO000000XXX', tel:'322-333-4455', mail:'demo@demo.com', estado:'inactivo' },
  ];

  const q       = document.getElementById('q');
  const estado  = document.getElementById('estado');
  const rows    = document.getElementById('rows');
  const noRows  = document.getElementById('noRows');

  function render(list){
    rows.innerHTML = '';
    if (!list.length){ noRows.style.display='block'; return; }
    noRows.style.display='none';

    list.forEach(p=>{
      const tr = document.createElement('tr');
      tr.innerHTML = `
        <td>${p.nombre}</td>
        <td>${p.curp}</td>
        <td>${p.tel}</td>
        <td>${p.mail}</td>
        <td>${p.estado === 'activo' ? 'Activo' : 'Inactivo'}</td>
        <td>
          <a class="btn-secondary" href="{{ route('recepcion.paciente.form') }}?id=${p.id}">Editar</a>
          <button class="btn-secondary" onclick="alert('Abrir historial clínico (demo)')">Historial</button>
        </td>
      `;
      rows.appendChild(tr);
    });
  }

  function apply(){
    let list = data.slice();
    const s = (q.value||'').toLowerCase();
    if (s){
      list = list.filter(p =>
        (p.nombre+p.curp+p.tel+p.mail).toLowerCase().includes(s)
      );
    }
    if (estado.value){
      list = list.filter(p => p.estado === estado.value);
    }
    render(list);
  }

  document.getElementById('btnBuscar').onclick = apply;
  document.getElementById('btnLimpiar').onclick = () => { q.value=''; estado.value=''; apply(); };

  apply();
})();
</script>
@endsection
