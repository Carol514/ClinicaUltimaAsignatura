@extends('layouts.app')

@section('title','Panel de Enfermería')

@section('content')
<main class="dashboard">
  <h2>Panel de Enfermería</h2>

  {{-- ====== CONTEXTO DE PACIENTE ====== --}}
  <div class="form-container" style="margin-bottom:16px;">
    <h3>Seleccionar paciente</h3>
    <label for="pacienteCtx">Paciente</label>
    <input id="pacienteCtx" placeholder="Ej. Ana Pérez" />
  </div>

  {{-- ====== SIGNOS VITALES ====== --}}
  <section class="panel" style="margin-top:10px;">
    <h3>Registro de Signos Vitales</h3>

    <form id="vitalForm">
      <label for="vitalPaciente">Paciente</label>
      <input id="vitalPaciente" readonly placeholder="(Se autollenará)" />

      <label for="vitalFecha" style="margin-top:6px;">Fecha</label>
      <input type="date" id="vitalFecha" required />

      <label for="vitalTemp" style="margin-top:6px;">Temperatura (°C)</label>
      <input type="number" id="vitalTemp" step="0.1" placeholder="Ej. 36.6" required />

      <label for="vitalPA" style="margin-top:6px;">Presión arterial (mmHg)</label>
      <input id="vitalPA" placeholder="Ej. 120/80" required />

      <label for="vitalPulso" style="margin-top:6px;">Pulso (lpm)</label>
      <input type="number" id="vitalPulso" placeholder="Ej. 76" required />

      <label for="vitalFR" style="margin-top:6px;">Frecuencia respiratoria (rpm)</label>
      <input type="number" id="vitalFR" placeholder="Ej. 16" required />

      <label for="vitalSpO2" style="margin-top:6px;">Saturación O₂ (%)</label>
      <input type="number" id="vitalSpO2" placeholder="Ej. 98" required />

      <div class="btn-container" style="margin-top:10px;">
        <button class="confirm-btn" type="submit">Guardar signos</button>
        <button class="cancel-btn" type="reset">Cancelar</button>
      </div>
    </form>
  </section>

  {{-- ====== REGISTRO DE ADMINISTRACIÓN DE MEDICAMENTO ====== --}}
  <section class="panel" style="margin-top:16px;">
    <h3>Registro de administración de medicamento</h3>
    <p class="muted">Este módulo NO modifica tratamientos; solo registra la administración (qué, cuándo, vía y quién).</p>

    <form id="admForm">
      <label for="admPaciente">Paciente</label>
      <input id="admPaciente" readonly placeholder="(Se autollenará)" />

      <label for="admMedicamento" style="margin-top:6px;">Medicamento</label>
      <input id="admMedicamento" placeholder="Ej. Paracetamol 500 mg" required />

      <label for="admHora" style="margin-top:6px;">Hora de administración</label>
      <input id="admHora" type="datetime-local" required />

      <label for="admVia" style="margin-top:6px;">Vía</label>
      <select id="admVia" required>
        <option value="" selected disabled>Seleccione…</option>
        <option>Oral</option>
        <option>Intravenosa</option>
        <option>Intramuscular</option>
        <option>Subcutánea</option>
        <option>Tópica</option>
        <option>Otra</option>
      </select>

      <label for="admQuien" style="margin-top:6px;">Quién la administró</label>
      <input id="admQuien" placeholder="Ej. Enf. Sofía H." required />

      <div class="btn-container" style="margin-top:10px;">
        <button class="confirm-btn" type="submit">Registrar</button>
        <button class="cancel-btn" type="reset">Cancelar</button>
      </div>
    </form>
  </section>
</main>

{{-- ====== JS (demo sin backend) ====== --}}
<script>
  // 1) Inicializar paciente desde query ?p=Nombre o mantener de sesión simple
  const urlParams = new URLSearchParams(window.location.search);
  const pacienteInicial = urlParams.get('p') || '';

  // Inputs de contexto y campos atados
  const $ctx = document.getElementById('pacienteCtx');
  const $vitalPaciente = document.getElementById('vitalPaciente');
  const $admPaciente   = document.getElementById('admPaciente');

  // Quien administra (pre-llenar con un nombre guardado si existiera)
  const nurseName = (sessionStorage.getItem('nurseName') || localStorage.getItem('nurseName') || 'Enfermería');
  document.getElementById('admQuien').value = nurseName;

  // Sincroniza el nombre del paciente a ambos formularios (solo lectura)
  function syncPaciente(nombre) {
    $vitalPaciente.value = nombre || '';
    $admPaciente.value   = nombre || '';
  }

  // Cambios en el campo de contexto
  $ctx.addEventListener('input', () => syncPaciente($ctx.value));

  // Set inicial
  $ctx.value = pacienteInicial;
  syncPaciente(pacienteInicial);

  // 2) Submit signos vitales (demo)
  document.getElementById('vitalForm').addEventListener('submit', (e) => {
    e.preventDefault();
    if (!$vitalPaciente.value.trim()) { alert('Primero indique el paciente.'); return; }
    alert('✅ Signos vitales registrados (demo).');
    e.target.reset();
    // mantener nombre del paciente
    syncPaciente($ctx.value);
  });

  // 3) Submit administración de medicamento (demo)
  document.getElementById('admForm').addEventListener('submit', (e) => {
    e.preventDefault();
    if (!$admPaciente.value.trim()) { alert('Primero indique el paciente.'); return; }
    const med  = document.getElementById('admMedicamento').value;
    const hora = document.getElementById('admHora').value;
    const via  = document.getElementById('admVia').value;
    const who  = document.getElementById('admQuien').value;
    alert(`✅ Administración registrada:\n\nPaciente: ${$admPaciente.value}\nMedicamento: ${med}\nHora: ${hora}\nVía: ${via}\nAplicó: ${who}`);
    e.target.reset();
    // mantener nombre del paciente
    syncPaciente($ctx.value);
    document.getElementById('admQuien').value = nurseName;
  });
</script>
@endsection
