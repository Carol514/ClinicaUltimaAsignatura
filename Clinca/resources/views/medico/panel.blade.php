@extends('layouts.app')
@section('title','Panel del Médico')

@section('content')
<main class="dashboard">
  <h2>Panel del Médico</h2>

  <div class="form-container" style="margin-bottom:14px;">
    <label>Paciente (contexto)</label>
    <input id="ctxPaciente" placeholder="Ej. Paciente DEMO">
    <p class="muted">Este nombre se pasará por URL a las otras pantallas.</p>
    <div class="btn-container">
      <button class="confirm-btn" onclick="go('historial')">Consultar historial</button>
      <button class="confirm-btn" onclick="go('documentos')">Subir documentos</button>
      <button class="confirm-btn" onclick="go('tratamientos')">Editar tratamientos</button>
      
    </div>
  </div>
</main>

<script>
  // Rellena desde ?p=...
  const params = new URLSearchParams(location.search);
  const $ctx = document.getElementById('ctxPaciente');
  $ctx.value = params.get('p') || '';

  function go(dest){
    const p = encodeURIComponent($ctx.value || '');
    const url = `/medico/${dest}${p ? `?p=${p}` : ''}`;
    location.href = url;
  }
</script>
@endsection
