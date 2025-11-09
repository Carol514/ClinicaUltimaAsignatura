@extends('layouts.app')
@section('title','Editar tratamientos')

@section('content')
<main class="dashboard">
  <h2>Editar tratamientos</h2>

  <form class="form-container" id="tratForm">
    {{-- estos se mapearán a record/treatment cuando haya backend --}}
    <input type="hidden" id="record_id">
    <input type="hidden" id="treatment_id">

    <label>Paciente</label>
    <input id="tPaciente" readonly placeholder="(de ?p=)">

    <label style="margin-top:6px;">Tratamiento actual</label>
    <input id="tActual" placeholder="Ej. Amoxicilina 500 mg c/8h" required>

    <label style="margin-top:6px;">Tratamiento nuevo</label>
    <input id="tNuevo"  placeholder="Ej. Azitromicina 500 mg c/24h x 3d" required>

    <label style="margin-top:6px;">Observaciones</label>
    <textarea id="tNotas" rows="3" placeholder="Motivo del cambio, indicaciones, etc."></textarea>

    <div class="btn-container" style="margin-top:8px;">
      <button class="confirm-btn" type="submit">Guardar cambio</button>
      <a class="cancel-btn" href="{{ route('medico.panel') }}">Volver</a>
    </div>
  </form>

  <div class="panel" style="margin-top:14px;">
    <h3>Bitácora (demo)</h3>
    <div id="log" class="list-container"></div>
  </div>
</main>

<script>
  const q = new URLSearchParams(location.search);
  const nombre = q.get('p') || '';
  tPaciente.value = nombre;

  tratForm.addEventListener('submit', (e)=>{
    e.preventDefault();
    if(!tPaciente.value.trim()){ alert('Indica el paciente'); return; }
    const row = document.createElement('div');
    row.className = 'list-item';
    row.innerHTML = `<strong>${tPaciente.value}</strong><br>
      ${new Date().toLocaleString()} — <b>De:</b> ${tActual.value} <b>→ A:</b> ${tNuevo.value}<br>
      <i>${tNotas.value || '(sin notas)'}</i>`;
    log.prepend(row);
    tratForm.reset();
    tPaciente.value = nombre; // mantener paciente
  });
</script>
@endsection
