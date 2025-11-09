@extends('layouts.app')

@section('title', 'Panel del Médico')

@section('content')
  {{-- NOTA: el navbar ya viene desde layouts.app, aquí NO lo repetimos --}}

  <main class="dashboard">
    <h2>Panel del Médico</h2>

    {{-- ===== BUSCADOR / ESTADO DEL PACIENTE ===== --}}
    <div class="form-container" style="margin-bottom: 18px;">
      <form id="search-form">
        <label for="searchBox">Buscar paciente (CURP / Nombre / Teléfono):</label>
        <input type="text" id="searchBox" placeholder="Ej. GAXX900101HDF / Ana Pérez / 322..." />
        <div class="btn-container">
          <button type="submit" class="confirm-btn">Buscar</button>
          {{-- DEMO: botones para simular estados --}}
          <button type="button" class="cancel-btn" onclick="setEstado('no_registrado')">Simular NO registrado</button>
          <button type="button" class="cancel-btn" onclick="setEstado('con_expediente')">Simular CON expediente</button>
        </div>
      </form>
      <p id="estadoLabel" style="margin-top:8px;">Estado: —</p>
    </div>

    {{-- =========================================================
         1) SIN REGISTRO -> ALTA DE HISTORIAL MÉDICO (AGRUPADO)
         Incluye: (A) Alergias & Antecedentes  +  (B) Signos Vitales
       ========================================================= --}}
    <section id="alta-historial" class="panel hidden">
      <h2>Alta de historial médico</h2>

      {{-- Paciente compartido para todo el flujo --}}
      <div class="form-container" style="margin-bottom: 12px;">
        <label for="pacienteNombre">Paciente:</label>
        <input type="text" id="pacienteNombre" placeholder="Nombre del paciente" required>
      </div>

      {{-- (A) Alergias y Antecedentes --}}
      <div class="panel" style="margin-top: 10px;">
        <h3>Registro de Alergias y Antecedentes</h3>

        <form id="alergiasForm">
          <label for="nombrePaciente">Nombre del Paciente:</label>
          <input type="text" id="nombrePaciente" placeholder="Ej. Juan Pérez" required readonly>

          <label for="alergias">Alergias:</label>
          <textarea id="alergias" rows="3" placeholder="Ej. Penicilina, mariscos, polen..."></textarea>

          <label for="antecedentes">Antecedentes Médicos:</label>
          <textarea id="antecedentes" rows="3" placeholder="Ej. Diabetes, hipertensión, cirugías previas..."></textarea>

          <button type="button" class="confirm-btn" onclick="guardarAlergiasAntecedentes()">Guardar Alergias/Antecedentes</button>
        </form>

        <div class="list-container" id="listaAlergias" style="margin-top:10px;">
          <h3>Registros Guardados:</h3>
        </div>
      </div>

      {{-- (B) Signos Vitales --}}
      <div class="form-container" style="margin-top: 14px;">
        <h3>Registro de Signos Vitales</h3>
        <form id="vitals-form">
          <label for="patient">Paciente:</label>
          <input type="text" id="patient" name="patient" placeholder="Nombre del paciente" required readonly>

          <label for="date">Fecha:</label>
          <input type="date" id="date" name="date" required>

          <label for="temperature">Temperatura (°C):</label>
          <input type="number" step="0.1" id="temperature" name="temperature" placeholder="Ej. 36.5" required>

          <label for="pressure">Presión Arterial (mmHg):</label>
          <input type="text" id="pressure" name="pressure" placeholder="Ej. 120/80" required>

          <label for="pulse">Pulso (lpm):</label>
          <input type="number" id="pulse" name="pulse" placeholder="Ej. 75" required>

          <label for="respiration">Frecuencia Respiratoria (rpm):</label>
          <input type="number" id="respiration" name="respiration" placeholder="Ej. 16" required>

          <label for="oxygen">Saturación de Oxígeno (%):</label>
          <input type="number" id="oxygen" name="oxygen" placeholder="Ej. 98" required>

          <div class="btn-container">
            <button type="submit" class="confirm-btn">Guardar signos</button>
            <button type="button" class="cancel-btn" onclick="limpiarSignos()">Cancelar</button>
          </div>
        </form>
      </div>

      {{-- Acciones del flujo de alta --}}
      <div class="btn-container" style="margin-top: 12px;">
        <button type="button" class="confirm-btn" onclick="finalizarAlta()">Finalizar alta</button>
      </div>
    </section>

    {{-- =========================================================
         2) CON EXPEDIENTE -> SOLO 3 MÓDULOS
            - Consulta de historial
            - Subir documentos
            - Editar tratamientos (actual, nuevo, observaciones)
       ========================================================= --}}
    <section id="panel-expediente" class="panel hidden">
      <h2>Expediente del paciente</h2>

      {{-- A) Consulta de historial --}}
      <div class="form-container">
        <h3>Consulta de historial</h3>
        <p class="muted">Ver visitas, signos, documentos y cambios de tratamiento.</p>
        <button class="confirm-btn" type="button" onclick="verHistorial()">Ver historial</button>
      </div>

      {{-- B) Subir documentos --}}
      <div class="form-container">
        <h3>Subir documentos</h3>
        <form id="docs-form" enctype="multipart/form-data">
          <label for="doc_type">Tipo de documento</label>
          <select id="doc_type" name="doc_type" required>
            <option value="radiografia">Radiografía</option>
            <option value="analisis">Análisis</option>
            <option value="otro">Otro</option>
          </select>

          <label for="doc_title" style="margin-top:8px;">Título</label>
          <input id="doc_title" name="title" placeholder="Ej. Radiografía de tórax" required>

          <label for="doc_file" style="margin-top:8px;">Archivo</label>
          <input id="doc_file" name="file" type="file" required>

          <div class="btn-container" style="margin-top:12px;">
            <button class="confirm-btn" type="submit">Subir documento</button>
            <button class="cancel-btn" type="reset">Cancelar</button>
          </div>
        </form>
      </div>

      {{-- C) Editar tratamientos (3 campos) --}}
      <div class="form-container">
        <h3>Editar tratamientos</h3>
        <form id="trat-form">
          {{-- Hidden que luego rellenará backend --}}
          <input type="hidden" id="record_id_trat" name="record_id" value="">
          <input type="hidden" id="treatment_id"   name="treatment_id" value="">

          <label for="t_actual">Tratamiento actual</label>
          <input id="t_actual" name="old_name" placeholder="Ej. Amoxicilina 500 mg c/8h" required>

          <label for="t_nuevo" style="margin-top:8px;">Tratamiento nuevo</label>
          <input id="t_nuevo" name="new_name" placeholder="Ej. Azitromicina 500 mg c/24h x 3d" required>

          <label for="t_notas" style="margin-top:8px;">Observaciones</label>
          <textarea id="t_notas" name="note" rows="3" placeholder="Motivo del cambio, indicaciones, etc."></textarea>

          <div class="btn-container" style="margin-top:12px;">
            <button class="confirm-btn" type="submit">Guardar cambio</button>
            <button type="button" class="cancel-btn" onclick="verBitacora()">Ver bitácora</button>
          </div>
        </form>
      </div>
    </section>
  </main>

  {{-- ===== JS de página (demo sin backend) ===== --}}
  <script>
    let estado = 'no_registrado'; // 'no_registrado' | 'con_expediente'
    let pacienteActivo = null;     // { nombre, record_id }

    const $estadoLabel     = document.getElementById('estadoLabel');
    const $altaHistorial   = document.getElementById('alta-historial');
    const $panelExpediente = document.getElementById('panel-expediente');

    // Compartidos
    const $pacienteNombre = document.getElementById('pacienteNombre');
    const $nombrePaciente = document.getElementById('nombrePaciente'); // (Alergias)
    const $patientSignos  = document.getElementById('patient');        // (Signos)

    function pintar() {
      $estadoLabel.textContent = 'Estado: ' + (estado === 'no_registrado'
        ? 'Paciente NO registrado (alta de historial)'
        : 'Paciente con expediente');

      if (estado === 'no_registrado') {
        $altaHistorial.classList.remove('hidden');
        $panelExpediente.classList.add('hidden');
        syncNombrePaciente();
      } else {
        $altaHistorial.classList.add('hidden');
        $panelExpediente.classList.remove('hidden');
        document.getElementById('record_id_trat').value = pacienteActivo?.record_id || '';
      }
    }

    function setEstado(e) {
      estado = e;
      pintar();
    }

    function setPaciente(nombre, recordId = '') {
      pacienteActivo = { nombre, record_id: recordId };
      $pacienteNombre.value = nombre || '';
      syncNombrePaciente();
    }

    function syncNombrePaciente() {
      $nombrePaciente.value = $pacienteNombre.value;
      $patientSignos.value  = $pacienteNombre.value;
      $nombrePaciente.readOnly = true;
      $patientSignos.readOnly  = true;
    }

    document.getElementById('search-form').addEventListener('submit', (e) => {
      e.preventDefault();
      const q = document.getElementById('searchBox').value.trim();
      if (!q) return alert('Introduce un criterio de búsqueda');

      // DEMO: "demo" => encontrado; otro => no encontrado
      if (q.toLowerCase().includes('demo')) {
        setPaciente('Paciente DEMO', 'uuid-expediente-demo');
        setEstado('con_expediente');
      } else {
        setPaciente('');
        setEstado('no_registrado');
      }
    });

    function guardarAlergiasAntecedentes() {
      const nombre = document.getElementById('nombrePaciente').value;
      const alerg  = document.getElementById('alergias').value;
      const ant    = document.getElementById('antecedentes').value;
      const lista  = document.getElementById('listaAlergias');

      if (!nombre) return alert('Primero escribe el nombre del paciente en la parte superior.');
      const item = document.createElement('div');
      item.classList.add('list-item');
      item.innerHTML = `<strong>${nombre}</strong><br>
        <b>Alergias:</b> ${alerg || 'Ninguna'}<br>
        <b>Antecedentes:</b> ${ant || 'Ninguno'}`;
      lista.appendChild(item);
      alert('Alergias/Antecedentes guardados (demo).');
    }

    document.getElementById('vitals-form').addEventListener('submit', function (e) {
      e.preventDefault();
      const nombre = document.getElementById('patient').value;
      if (!nombre) return alert('Primero escribe/selecciona el paciente.');
      alert('✅ Signos vitales registrados (demo).');
      this.reset();
      syncNombrePaciente();
    });

    function limpiarSignos(){
      document.getElementById('vitals-form').reset();
      syncNombrePaciente();
    }

    function finalizarAlta(){
      if (!$pacienteNombre.value.trim()) return alert('Escribe el nombre del paciente.');
      alert('Alta de historial completada (demo). Pasando a modo "Con expediente".');
      setPaciente($pacienteNombre.value, 'uuid-expediente-creado');
      setEstado('con_expediente');
    }

    function verHistorial(){ alert('Abrir historial (demo)'); }
    function verBitacora(){ alert('Abrir bitácora (demo)'); }

    // Init
    pintar();
  </script>
@endsection
