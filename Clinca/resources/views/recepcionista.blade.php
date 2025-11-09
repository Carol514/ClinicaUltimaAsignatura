{{-- resources/views/recepcionista.blade.php --}}
@extends('layouts.app')

@section('title','Panel de Recepción')

@section('content')
<main class="dashboard">
  <h2>Panel de Recepción</h2>

  {{-- ===== Registro rápido de paciente ===== --}}
  <section class="panel" style="margin-top:8px;">
    <h3>Registrar paciente</h3>
    <form id="frmPaciente" class="form-container">
      <div class="grid" style="display:grid;gap:10px;grid-template-columns:1fr 1fr;">
        <div>
          <label for="p_nombre">Nombre(s)</label>
          <input id="p_nombre" placeholder="Ej. Ana Sofía" required>
        </div>
        <div>
          <label for="p_apellidos">Apellidos</label>
          <input id="p_apellidos" placeholder="Ej. Pérez Díaz" required>
        </div>
        <div>
          <label for="p_curp">CURP</label>
          <input id="p_curp" placeholder="Ej. GAXX900101HDF" maxlength="18">
        </div>
        <div>
          <label for="p_tel">Teléfono</label>
          <input id="p_tel" placeholder="Ej. 3221234567" inputmode="numeric" pattern="\d*">
        </div>
        <div>
          <label for="p_email">Email</label>
          <input id="p_email" type="email" placeholder="correo@dominio.com">
        </div>
        <div>
          <label for="p_fecha">Fecha de nacimiento</label>
          <input id="p_fecha" type="date">
        </div>
      </div>

      <label for="p_dir" style="margin-top:8px;">Dirección</label>
      <input id="p_dir" placeholder="Calle, número, colonia, ciudad">

      <div class="btn-container" style="margin-top:10px;">
        <button class="confirm-btn" type="submit">Guardar paciente</button>
        <button class="cancel-btn" type="reset">Limpiar</button>
      </div>
    </form>
  </section>

  {{-- ===== Agendar cita ===== --}}
  <section class="panel" style="margin-top:16px;">
    <h3>Agendar cita</h3>
    <form id="frmCita" class="form-container">
      <div class="grid" style="display:grid;gap:10px;grid-template-columns:1fr 1fr;">
        <div>
          <label for="c_paciente">Paciente</label>
          <input id="c_paciente" placeholder="Nombre completo (existente)" required>
        </div>
        <div>
          <label for="c_doctor">Médico</label>
          <input id="c_doctor" placeholder="Ej. Dr. Hernández" required>
        </div>
        <div>
          <label for="c_fecha">Fecha</label>
          <input id="c_fecha" type="date" required>
        </div>
        <div>
          <label for="c_hora">Hora</label>
          <input id="c_hora" type="time" required>
        </div>
      </div>

      <label for="c_motivo" style="margin-top:8px;">Motivo</label>
      <input id="c_motivo" placeholder="Ej. Consulta general / Control / Resultados" required>

      <div class="btn-container" style="margin-top:10px;">
        <button class="confirm-btn" type="submit">Crear cita</button>
        <button class="cancel-btn" type="reset">Cancelar</button>
      </div>
    </form>
  </section>

  {{-- ===== Agenda del día (demo) ===== --}}
  <section class="panel" style="margin-top:16px;">
    <h3>Agenda del día</h3>
    <div class="form-container" style="margin-bottom:8px;">
      <div class="grid" style="display:grid;gap:10px;grid-template-columns:1fr 1fr;">
        <div>
          <label for="a_fecha">Fecha</label>
          <input id="a_fecha" type="date">
        </div>
        <div>
          <label for="a_doctor">Filtrar por médico (opcional)</label>
          <input id="a_doctor" placeholder="Ej. Dr. Hernández">
        </div>
      </div>
      <div class="btn-container" style="margin-top:8px;">
        <button class="confirm-btn" id="btnAgenda">Ver agenda</button>
      </div>
    </div>

    <div id="agendaLista" class="list-container">
      <p class="muted">Sin resultados. Selecciona fecha y/o médico y presiona “Ver agenda”.</p>
    </div>
  </section>
</main>

{{-- ===== JS (demo sin backend) ===== --}}
<script>
  // Guardar paciente (DEMO)
  document.getElementById('frmPaciente').addEventListener('submit', (e)=>{
    e.preventDefault();
    const nombre = (p_nombre.value || '').trim();
    const ap    = (p_apellidos.value || '').trim();
    if(!nombre || !ap){ alert('Completa nombre y apellidos'); return; }
    alert('✅ Paciente almacenado (demo): ' + nombre + ' ' + ap);
    e.target.reset();
  });

  // Agendar cita (DEMO)
  document.getElementById('frmCita').addEventListener('submit', (e)=>{
    e.preventDefault();
    if(!(c_paciente.value && c_doctor.value && c_fecha.value && c_hora.value)){
      alert('Completa los datos de la cita');
      return;
    }
    // En real: POST a /appointments
    const item = document.createElement('div');
    item.className = 'list-item';
    item.innerHTML = `<strong>${c_fecha.value} ${c_hora.value}</strong> — ${c_paciente.value} con ${c_doctor.value}<br>
      Motivo: ${c_motivo.value}`;
    agendaLista.prepend(item);
    e.target.reset();
  });

  // Ver agenda del día (DEMO)
  document.getElementById('btnAgenda').addEventListener('click', ()=>{
    const fecha = a_fecha.value || new Date().toISOString().slice(0,10);
    const doc   = (a_doctor.value || '').trim();
    agendaLista.innerHTML = `
      <div class="list-item">
        <strong>${fecha} 09:00</strong> — Paciente DEMO 1 con ${doc || 'Dr. Hernández'}<br>Motivo: Consulta general
      </div>
      <div class="list-item">
        <strong>${fecha} 11:30</strong> — Paciente DEMO 2 con ${doc || 'Dra. López'}<br>Motivo: Control
      </div>
    `;
  });
</script>
@endsection
