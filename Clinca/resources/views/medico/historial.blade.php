@extends('layouts.app')
@section('title','Consulta de historial')

@section('content')
<main class="dashboard">
  <h2>Consulta de historial</h2>

  <div class="form-container">
    <label>Paciente</label>
    <input id="paciente" readonly placeholder="(de ?p=)">
    <div class="grid" style="display:grid;gap:10px;grid-template-columns:1fr 1fr;">
      <div>
        <label>Desde</label>
        <input type="date" id="from">
      </div>
      <div>
        <label>Hasta</label>
        <input type="date" id="to">
      </div>
    </div>
    <div class="btn-container" style="margin-top:8px;">
      <button class="confirm-btn" id="btnBuscar">Buscar</button>
      <a class="cancel-btn" href="{{ route('medico.panel') }}">Volver al panel</a>
    </div>
  </div>

  <div class="panel" style="margin-top:14px;">
    <h3>Resultados (demo)</h3>
    <div id="result" class="list-container">
      <p class="muted">Sin datos. Usa “Buscar”.</p>
    </div>
  </div>
</main>

<script>
  const q = new URLSearchParams(location.search);
  const nombre = q.get('p') || '';
  paciente.value = nombre;

  btnBuscar.onclick = () => {
    if(!paciente.value.trim()){ alert('Indica el paciente'); return; }
    result.innerHTML = `
      <div class="list-item">
        <strong>${paciente.value}</strong><br>
        2025-11-08 10:30 — Consulta general<br>
        Signos: TA 118/76, FC 74, Temp 36.5°C<br>
        Documento: Radiografía de tórax (PDF)
      </div>
    `;
  };
</script>
@endsection
