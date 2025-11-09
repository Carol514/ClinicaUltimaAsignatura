@extends('layouts.app')
@section('title','Editar tratamientos')

@section('content')
<main class="dashboard">
  <h2>Editar tratamientos</h2>

  {{-- Contexto de paciente (desde ?p=Nombre) --}}
  <div id="trat-context" class="form-container" style="margin-bottom:10px;">
    <label>Paciente</label>
    <input id="tratPaciente" readonly placeholder="(de ?p=)">
    <p class="muted" style="margin:6px 0 0;">Pasamos el paciente por URL con <code>?p=Nombre</code>.</p>
  </div>

  {{-- Formulario: solo 3 campos (actual, nuevo, observaciones) --}}
  <form id="trat-form" class="form-container">
    <label for="t_actual">Tratamiento actual</label>
    <input id="t_actual" placeholder="Ej. Amoxicilina 500 mg c/8h" required>

    <label for="t_nuevo" style="margin-top:8px;">Tratamiento nuevo</label>
    <input id="t_nuevo" placeholder="Ej. Azitromicina 500 mg c/24h x 3d" required>

    <label for="t_notas" style="margin-top:8px;">Observaciones</label>
    <textarea id="t_notas" rows="3" placeholder="Motivo del cambio, indicaciones, etc."></textarea>

    <div class="btn-container" style="margin-top:12px;">
      <button class="confirm-btn" type="submit">Guardar cambio</button>
      <a class="cancel-btn" href="{{ route('medico.panel') }}">Volver</a>
    </div>
  </form>

  {{-- Bitácora (demo) --}}
  <section class="panel" style="margin-top:16px;">
    <h3>Bitácora de cambios</h3>
    <div id="bitacora" class="list-container">
      <p class="muted">Aún no hay cambios registrados.</p>
    </div>
  </section>
</main>

<script>
  // Cargar paciente desde la URL (?p=)
  const p = new URLSearchParams(location.search).get('p') || '';
  document.getElementById('tratPaciente').value = p;

  const bitacora = document.getElementById('bitacora');
  function pushBitacora(oldTxt, newTxt, note){
    if (bitacora.querySelector('.muted')) bitacora.innerHTML = '';
    const row = document.createElement('div');
    row.className = 'list-item';
    const when = new Date().toLocaleString();
    row.innerHTML = `
      <div style="display:flex;flex-direction:column;gap:4px;">
        <div><b>${when}</b> — <span class="muted">${p || '(paciente)'}</span></div>
        <div><b>Actual:</b> ${oldTxt || '(vacío)'}</div>
        <div><b>Nuevo:</b> ${newTxt || '(vacío)'}</div>
        <div><b>Notas:</b> ${note || '(sin notas)'}</div>
      </div>
    `;
    bitacora.prepend(row);
  }

  // Guardar (demo sin backend)
  document.getElementById('trat-form').addEventListener('submit', (e)=>{
    e.preventDefault();
    const oldTxt = document.getElementById('t_actual').value.trim();
    const newTxt = document.getElementById('t_nuevo').value.trim();
    const note   = document.getElementById('t_notas').value.trim();
    if (!p) return alert('Indica el paciente con ?p= en la URL.');
    if (!oldTxt || !newTxt) return alert('Completa “actual” y “nuevo”.');

    // Aquí iría el POST real al backend.
    pushBitacora(oldTxt, newTxt, note);
    alert('✅ Cambio de tratamiento registrado (demo).');

    // Limpia solo los campos, conserva el paciente
    document.getElementById('t_actual').value = '';
    document.getElementById('t_nuevo').value  = '';
    document.getElementById('t_notas').value  = '';
  });
</script>
@endsection
