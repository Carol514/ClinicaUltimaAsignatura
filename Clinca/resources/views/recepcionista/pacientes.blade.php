@extends('layouts.app')
@section('title','Pacientes')

@section('content')
<main class="dashboard">
  <h2>Pacientes</h2>

  <section class="panel" style="max-width:1000px;">
  <form class="form-container agenda-filtros" onsubmit="return false;" id="searchForm">
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
        <a href="{{ route('recepcionista.registro') }}" class="confirm-btn" style="margin-left:auto">+ Nuevo</a>
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
  (function(){
    const q       = document.getElementById('q');
    const estado  = document.getElementById('estado');
    const rows    = document.getElementById('rows');
    const noRows  = document.getElementById('noRows');

    async function fetchPatients(){
      const params = new URLSearchParams();
      if (q.value) params.set('q', q.value);
      try{
        const res = await fetch('/recepcionista/api/patients?'+params.toString());
        if (!res.ok){
          const txt = await res.text();
          console.error('fetchPatients error', res.status, txt);
          return [];
        }
        // parse JSON safely using clone to allow text fallback
        let body;
        try{ body = await res.clone().json(); }catch(e){
          const txt = await res.clone().text(); console.error('Non-JSON response', txt); return []; }
        return body.data || [];
      }catch(e){ console.error('fetchPatients', e); return [];}
    }

    function render(list){
      rows.innerHTML = '';
      if (!list.length){ noRows.style.display='block'; return; }
      noRows.style.display='none';

      list.forEach(p=>{
        const tr = document.createElement('tr');
        tr.innerHTML = `
          <td>${p.nombre || (p.first_name+' '+p.last_name)}</td>
          <td>${p.curp||''}</td>
          <td>${p.phone||''}</td>
          <td>${p.email||''}</td>
          <td>${p.estado || ''}</td>
          <td>
            <a class="btn-secondary" href="{{ route('recepcionista.registro') }}?id=${p.id}">Editar</a>
            <a class="btn-secondary" href="{{ route('recepcionista.citas') }}?p=${encodeURIComponent(p.id)}">Agendar</a>
          </td>
        `;
        rows.appendChild(tr);
      });
    }

    async function apply(){
      const list = await fetchPatients();
      // simple client-side state filter if backend doesn't provide
      const st = (estado.value||'').toLowerCase();
      const filtered = list.filter(it => { if (!st) return true; return (it.estado||'').toLowerCase() === st; });
      render(filtered);
    }

    document.getElementById('btnBuscar').onclick = apply;
    document.getElementById('btnLimpiar').onclick = () => { q.value=''; estado.value=''; apply(); };

    // load on start
    apply();
  })();
  </script>
@endsection
