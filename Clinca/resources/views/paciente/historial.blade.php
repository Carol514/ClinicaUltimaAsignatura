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
      <a class="btn-secondary" id="volverBtn" href="{{ route('paciente.panel') }}">Volver</a>
    </div>
  </section>
</main>

<script>
(() => {

  const rows   = document.getElementById('rows');
  const noRows = document.getElementById('noRows');

  async function fetchRemote(patientId){
    try{
      const url = patientId ? `/paciente/api/history?patient_id=${encodeURIComponent(patientId)}` : '/paciente/api/history';
      const res = await fetch(url, { credentials: 'same-origin', headers:{ 'Accept':'application/json' } });
      if (!res.ok) throw new Error('no remote');
      const json = await res.json();
      return Array.isArray(json) ? json : [];
    }catch(e){
      console.warn('Remote history not available', e);
      return [];
    }
  }

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

  // If a patient id is provided via ?p= we attempt to fetch server-side history
  // Otherwise, fetch history for the currently authenticated patient
  const params = new URLSearchParams(location.search);
  const patient = params.get('p');
  
  fetchRemote(patient).then(remote => {
    render(remote || []);
  });
})();
</script>
@endsection
