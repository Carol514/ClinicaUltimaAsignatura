@extends('layouts.app')
@section('title','Reportes del Sistema')

@section('content')
<main class="dashboard">
  <h2>Reportes del Sistema</h2>

  <section class="panel" style="max-width:950px;">
    <p class="muted">
      En esta sección puedes visualizar y generar reportes sobre la actividad del sistema:
      usuarios registrados, citas, tratamientos, y más.
    </p>

    {{-- Filtros --}}
    <form class="form-container" onsubmit="return false;" style="margin-top:14px;">
      <div class="fields" style="display:grid;gap:12px;grid-template-columns:1fr 1fr 1fr;">
        <div>
          <label>Tipo de reporte</label>
          <select id="tipoReporte">
            <option value="">Selecciona...</option>
            <option value="usuarios">Usuarios por rol</option>
            <option value="citas">Citas por día</option>
            <option value="tratamientos">Tratamientos aplicados</option>
          </select>
        </div>

        <div>
          <label>Desde</label>
          <input type="date" id="desde">
        </div>

        <div>
          <label>Hasta</label>
          <input type="date" id="hasta">
        </div>
      </div>

      <div class="btn-container" style="margin-top:12px;">
        <button class="confirm-btn" id="btnGenerar">Generar</button>
        <button class="cancel-btn" type="reset" id="btnLimpiar">Limpiar</button>
        <a class="cancel-btn" href="{{ route('admin.panel') }}">Volver</a>
      </div>
    </form>

    {{-- Resultados --}}
    <div class="table-container" style="margin-top:20px;">
      <table>
        <thead id="thead"></thead>
        <tbody id="tbody"></tbody>
      </table>
      <p id="noData" class="muted" style="text-align:center;margin-top:12px;">Selecciona un reporte para ver los datos.</p>
    </div>
  </section>
</main>

<script>
(() => {
  const tipoReporte = document.getElementById('tipoReporte');
  const thead = document.getElementById('thead');
  const tbody = document.getElementById('tbody');
  const noData = document.getElementById('noData');
  const CSRF = '{{ csrf_token() }}';

  function api(path, opts = {}){
    opts.headers = Object.assign({ 'Accept':'application/json', 'Content-Type':'application/json', 'X-CSRF-TOKEN': CSRF }, opts.headers || {});
    if (opts.body && typeof opts.body !== 'string') opts.body = JSON.stringify(opts.body);
    return fetch(path, opts).then(async res => {
      const txt = await res.text(); let json = null;
      try{ json = txt ? JSON.parse(txt) : null; }catch(e){ json = txt; }
      if (!res.ok) throw { status: res.status, body: json };
      return json;
    });
  }

  document.getElementById('btnGenerar').onclick = (e)=>{
    e.preventDefault();
    const tipo = tipoReporte.value;
    if(!tipo){ alert('Selecciona un tipo de reporte'); return; }
    const desde = document.getElementById('desde').value || null;
    const hasta = document.getElementById('hasta').value || null;

    api('/administrador/api/reports', { method: 'POST', body: { type: tipo, from: desde, to: hasta } })
      .then(rpt => {
        const encabezado = rpt.encabezado || (rpt[0] && Object.keys(rpt[0])) || [];
        const datos = rpt.datos || rpt;
        thead.innerHTML = `<tr>${encabezado.map(h=>`<th>${h}</th>`).join('')}</tr>`;
        tbody.innerHTML = (datos || []).map(row=>`<tr>${Object.values(row).map(c=>`<td>${c}</td>`).join('')}</tr>`).join('');
        noData.style.display = 'none';
      })
      .catch(err=>{ console.error(err); alert(err.body?.message || 'Error generando reporte'); });
  };

  document.getElementById('btnLimpiar').onclick = ()=>{
    thead.innerHTML = ''; tbody.innerHTML = ''; 
    tipoReporte.value = ''; noData.style.display = 'block';
  };
})();
</script>
@endsection
