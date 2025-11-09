@extends('layouts.app')
@section('title','Agendar cita')

@section('content')
<main class="dashboard">
  <h2>Agendar cita</h2>

  <section class="panel">
    <form class="form-container agendar-cita" id="citaForm">
      <div class="fields">
        <div class="field">
          <label for="pac">Paciente</label>
          <input id="pac" placeholder="Nombre completo / CURP / Teléfono">
        </div>

        <div class="field">
          <label for="doctor">Doctor</label>
          <select id="doctor" required>
            <option value="" selected disabled>Seleccione…</option>
            <option>Dr. Hernández</option>
            <option>Dra. López</option>
            <option>Dr. Ramírez</option>
          </select>
        </div>

        <div class="field">
          <label for="fecha">Fecha</label>
          <input type="date" id="fecha" required>
        </div>

        <div class="field">
          <label for="hora">Hora</label>
          <input type="time" id="hora" required>
        </div>

        <div class="field">
          <label for="motivo">Motivo</label>
          <input id="motivo" placeholder="Ej. control, dolor, resultados">
        </div>
      </div>

      <div class="btn-container acciones">
        <button class="confirm-btn" type="submit">Guardar cita</button>
        <button class="cancel-btn" type="reset">Limpiar</button>
        <a href="{{ route('recepcionista.panel') }}" class="cancel-btn">Volver</a>
      </div>
    </form>
  </section>
</main>

<script>
(() => {
  // Autorrellenar paciente desde ?p= en la URL, si llega
  const p = new URLSearchParams(location.search).get('p') || '';
  if (p) document.getElementById('pac').value = p;

  document.getElementById('citaForm').addEventListener('submit', e=>{
    e.preventDefault();
    const pac    = document.getElementById('pac').value.trim();
    const doctor = document.getElementById('doctor').value;
    const fecha  = document.getElementById('fecha').value;
    const hora   = document.getElementById('hora').value;
    const motivo = document.getElementById('motivo').value.trim();

    if (!pac)      return alert('Escribe el paciente.');
    if (!doctor)   return alert('Selecciona el doctor.');
    if (!fecha)    return alert('Selecciona la fecha.');
    if (!hora)     return alert('Selecciona la hora.');

    // Aquí iría tu POST real al backend
    alert(`✅ Cita guardada (demo):\n\nPaciente: ${pac}\nDoctor: ${doctor}\nFecha: ${fecha} ${hora}\nMotivo: ${motivo||'(no especificado)'}`);

    e.target.reset();
    if (p) document.getElementById('pac').value = p; // mantener si venía por URL
  });
})();
</script>
@endsection
