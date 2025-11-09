{{-- resources/views/enfermera.blade.php --}}
@extends('layouts.app')

@section('title', 'Panel de Enfermería')

@section('content')
  <h2>Panel de Enfermería</h2>

  {{-- Buscar / seleccionar paciente --}}
  <div class="form-container" style="margin-bottom:16px">
    <h3>Seleccionar paciente</h3>
    <form id="buscarPacienteForm">
      <label for="buscarPaciente">Buscar (CURP / Nombre / Teléfono):</label>
      <input id="buscarPaciente" placeholder="Ej. GAXX900101HDF / Ana Pérez / 322..." />
      <div class="btn-container">
        <button type="submit" class="confirm-btn">Buscar</button>
        <button type="button" class="cancel-btn" id="simularSelPaciente">Simular selección</button>
      </div>
    </form>
    <p class="muted" id="pacienteEstado">Paciente no seleccionado.</p>
  </div>

  {{-- Registro de Signos Vitales (paciente autollenado) --}}
  <div class="form-container" style="margin-bottom:18px">
    <h3>Registrar signos vitales</h3>
    <form id="vitalsForm">
      <label for="sv_paciente">Paciente:</label>
      <input id="sv_paciente" readonly placeholder="Seleccione un paciente" required />

      <label for="sv_fecha">Fecha:</label>
      <input type="date" id="sv_fecha" required />

      <label for="sv_temp">Temperatura (°C):</label>
      <input type="number" step="0.1" id="sv_temp" placeholder="Ej. 36.5" required />

      <label for="sv_pa">Presión Arterial (mmHg):</label>
      <input id="sv_pa" placeholder="Ej. 120/80" required />

      <label for="sv_pulso">Pulso (lpm):</label>
      <input type="number" id="sv_pulso" placeholder="Ej. 75" required />

      <label for="sv_fr">Frecuencia Respiratoria (rpm):</label>
      <input type="number" id="sv_fr" placeholder="Ej. 16" required />

      <label for="sv_spo2">Saturación de Oxígeno (%):</label>
      <input type="number" id="sv_spo2" placeholder="Ej. 98" required />

      <div class="btn-container">
        <button type="submit" class="confirm-btn">Guardar</button>
        <button type="reset" class="cancel-btn" id="sv_reset">Limpiar</button>
      </div>
    </form>
  </div>

  {{-- Registro de administración de medicamentos (NO edita tratamiento) --}}
  <div class="form-container">
    <h3>Registro de administración de medicamento</h3>
    <p class="muted" style="margin-top:-6px">
      Aquí solo se registra la administración (qué, cuánto, vía, cuándo, quién y observaciones).
    </p>

    <form id="adminMedForm">
      <label for="adm_paciente">Paciente:</label>
      <input id="adm_paciente" readonly placeholder="Seleccione un paciente" required />

      <label for="adm_medicamento">Medicamento:</label>
      <input id="adm_medicamento" placeholder="Ej. Paracetamol" required />

      <label for="adm_dosis" style="margin-top:8px;">Dosis:</label>
      <input id="adm_dosis" placeholder="Ej. 500 mg" required />

      <label for="adm_via" style="margin-top:8px;">Vía:</label>
      <select id="adm_via" required>
        <option value="">Seleccione…</option>
        <option>Oral</option>
        <option>Intravenosa (IV)</option>
        <option>Intramuscular (IM)</option>
        <option>Subcutánea (SC)</option>
        <option>Tópica</option>
        <option>Inhalada</option>
      </select>

      <label for="adm_hora" style="margin-top:8px;">Hora de administración:</label>
      <input type="time" id="adm_hora" required />

      <label for="adm_quien" style="margin-top:8px;">Quién administró:</label>
      <input id="adm_quien" placeholder="Nombre de la enfermera(o)" required
             value="{{ session('userName', '') }}" />

      <label for="adm_obs" style="margin-top:8px;">Observaciones:</label>
      <textarea id="adm_obs" rows="3" placeholder="Notas adicionales…"></textarea>

      <div class="btn-container" style="margin-top:12px;">
        <button type="submit" class="confirm-btn">Registrar administración</button>
        <button type="reset" class="cancel-btn" id="adm_reset">Limpiar</button>
      </div>
    </form>
  </div>

  {{-- JS de página (demo sin backend) --}}
  <script>
    // Estado simple de paciente seleccionado
    let pacienteActual = null; // { nombre, id }

    const $estado = document.getElementById('pacienteEstado');
    const $svPaciente  = document.getElementById('sv_paciente');
    const $admPaciente = document.getElementById('adm_paciente');

    function pintarPaciente() {
      if (pacienteActual?.nombre) {
        $estado.textContent = 'Paciente seleccionado: ' + pacienteActual.nombre;
        $svPaciente.value   = pacienteActual.nombre;
        $admPaciente.value  = pacienteActual.nombre;
      } else {
        $estado.textContent = 'Paciente no seleccionado.';
        $svPaciente.value   = '';
        $admPaciente.value  = '';
      }
    }

    // Buscar paciente (DEMO)
    document.getElementById('buscarPacienteForm').addEventListener('submit', (e) => {
      e.preventDefault();
      const q = document.getElementById('buscarPaciente').value.trim();
      if (!q) return alert('Ingresa un criterio de búsqueda');
      // Aquí llamarías al backend. DEMO: si incluye "demo", lo encuentra.
      if (q.toLowerCase().includes('demo')) {
        pacienteActual = { nombre: 'Paciente DEMO', id: 'uuid-demo' };
      } else {
        alert('No se encontró. Selecciona manualmente o prueba "demo".');
        pacienteActual = null;
      }
      pintarPaciente();
    });

    // Botón de simulación rápida
    document.getElementById('simularSelPaciente').addEventListener('click', () => {
      pacienteActual = { nombre: 'Paciente DEMO', id: 'uuid-demo' };
      pintarPaciente();
    });

    // Enviar signos vitales
    document.getElementById('vitalsForm').addEventListener('submit', (e) => {
      e.preventDefault();
      if (!pacienteActual) return alert('Selecciona primero un paciente.');
      alert('✅ Signos vitales guardados (demo).');
      // Aquí harías fetch POST al backend.
    });

    // Reset signos → mantener paciente
    document.getElementById('sv_reset').addEventListener('click', () => {
      setTimeout(pintarPaciente, 0);
    });

    // Enviar administración de medicamento
    document.getElementById('adminMedForm').addEventListener('submit', (e) => {
      e.preventDefault();
      if (!pacienteActual) return alert('Selecciona primero un paciente.');
      alert('💉 Administración registrada (demo).');
      // Aquí harías fetch POST al backend.
    });

    // Reset administración → mantener paciente
    document.getElementById('adm_reset').addEventListener('click', () => {
      setTimeout(pintarPaciente, 0);
    });

    // Init
    pintarPaciente();
  </script>
@endsection
