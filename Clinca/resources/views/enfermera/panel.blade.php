@extends('layouts.app')
@section('title','Panel de Enfermería')

@section('content')
<style>
    .suggestions-dropdown {
        position: absolute;
        top: 100%;
        left: 0;
        right: 0;
        background: white;
        border: 1px solid #ddd;
        border-radius: 4px;
        box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
        max-height: 200px;
        overflow-y: auto;
        z-index: 1000;
    }
    
    .suggestion-item {
        padding: 10px;
        cursor: pointer;
        border-bottom: 1px solid #eee;
    }
    
    .suggestion-item:hover {
        background-color: #f8f9fa;
    }
    
    .suggestion-item:last-child {
        border-bottom: none;
    }
    
    .search-row {
        position: relative;
    }
    
    .hidden {
        display: none;
    }
</style>

<main class="dashboard">

  <h2>Panel de Enfermería</h2>

  {{-- ===========================
        BUSCAR PACIENTE
     ============================ --}}
  <h3 class="panel-subtitle">Buscar Paciente</h3>

  <form id="frmBuscar" class="form-container" onsubmit="return false;">
    <div class="search-row">
      <input id="txtPaciente" placeholder="Ingrese el nombre o ID del paciente" autocomplete="off">
      <div id="patient-suggestions" class="suggestions-dropdown hidden"></div>
      <button id="btnBuscar" class="confirm-btn" type="button">
        <img src="/img/buscar.png" alt="Buscar" width="22" height="22" >
      </button>
    </div>
  </form>


  {{-- ==========================================================
        PANEL COMPLETO DE ENFERMERÍA (2 columnas)
     =========================================================== --}}
  <section id="nurse-layout" class="nurse-layout" style="display:none;">

    {{-- ========== IZQUIERDA (Datos + Signos Vitales) ========== --}}
    <div class="nurse-left">

      {{-- DATOS DEL PACIENTE --}}
      <div class="nurse-card">
        <h3 class="nurse-card-title" id="hdrPaciente">Paciente: —</h3>

        <table class="nurse-table">
          <tbody>
          <tr><th>Edad</th><td id="pEdad">—</td></tr>
          <tr><th>Género</th><td id="pGenero">—</td></tr>
          <tr><th>Diagnóstico</th><td id="pDx">—</td></tr>
          <tr><th>Última Consulta</th><td id="pUltima">—</td></tr>
          </tbody>
        </table>
      </div>

      {{-- HISTORIAL DE SIGNOS VITALES --}}
      <div class="nurse-card">
        <div class="nurse-card-header">
          <h3 class="nurse-card-title">Historial de Signos Vitales</h3>

          {{-- Botón agregar / registrar signos (icono) --}}
          <button id="lnkSignos" type="button" class="confirm-btn nurse-header-btn">
            <img src="/img/agregar.png" class="btn-icon" alt="Registrar signos vitales" style="width:22px; height:22px;">
          </button>
        </div>

        <div class="table-container">
          <table>
            <thead>
            <tr>
              <th>Fecha</th>
              <th>Temperatura (°C)</th>
              <th>Presión Arterial (mmHg)</th>
              <th>Pulso (lpm)</th>
              <th>Frecuencia resp. (rpm)</th>
              <th>SpO₂ (%)</th>
              <th>Peso (kg)</th>
              <th>Altura (cm)</th>
            </tr>
            </thead>
            <tbody id="vitalsTableBody">
            <tr>
              <td colspan="8" class="empty-cell">Sin registros.</td>
            </tr>
            </tbody>
          </table>
        </div>
      </div>

    </div>

    {{-- ========== DERECHA (Tratamientos) ========== --}}
    <div class="nurse-right">

      {{-- TRATAMIENTO ACTUAL --}}
      <div class="nurse-card">
        <div class="nurse-card-header">
          <h3 class="nurse-card-title">Tratamiento Actual</h3>

          {{-- Botón crear nuevo tratamiento (icono +) --}}
          <button id="lnkTrat" type="button" class="confirm-btn nurse-header-btn">
            <img src="/img/agregar.png" class="btn-icon" alt="Nuevo tratamiento" style="width:22px; height:22px;">
          </button>
        </div>

        <div id="treatmentsContainer">
          {{-- Se rellena por JS con los tratamientos reales --}}
        </div>
      </div>

    </div>

  </section>
</main>

{{-- ===========================
      Modal: Editar tratamiento
   ============================ --}}
<div id="tratModal" class="modal hidden">
  <div class="modal-content modal-lg">

    <h3 class="modal-title">Editar tratamiento</h3>

    <form id="nurse-trat-form" class="form-container">

      {{-- Tratamiento actual --}}
      <div class="trat-section">
        <label for="t_actual">Tratamiento actual</label>
        <input
          id="t_actual"
          type="text"
          placeholder="Ej. Amoxicilina 500 mg c/8h"
        >
      </div>

      <hr class="section-divider">

      {{-- Nuevo tratamiento: detalles estructurados --}}
      <div class="trat-section">
        <h4 class="trat-section-title">Nuevo tratamiento</h4>

        {{-- Campo combinado (medicina + cantidad) --}}
        <div class="field" style="margin-bottom:10px;">
          <label for="t_nuevo">Resumen del tratamiento nuevo</label>
          <input
            id="t_nuevo"
            type="text"
            placeholder="Ej. Amoxicilina 500 mg c/8h por 7 días"
          >
        </div>

        {{-- Campos desglosados (opcionales / apoyo) --}}
        <div class="trat-grid">
          <div class="field">
            <label for="med_name">Medicamento</label>
            <input id="med_name" placeholder="Ej. Amoxicilina">
          </div>

          <div class="field">
            <label for="med_dose">Dosis</label>
            <input id="med_dose" placeholder="Ej. 500">
          </div>

          <div class="field">
            <label for="med_unit">Unidad</label>
            <select id="med_unit">
              <option value="">Seleccione...</option>
              <option>mg</option>
              <option>ml</option>
              <option>g</option>
              <option>UI</option>
            </select>
          </div>

          <div class="field">
            <label for="med_freq">Frecuencia</label>
            <input id="med_freq" placeholder="Ej. cada 8 horas">
          </div>

          <div class="field">
            <label for="med_day">Día de inicio</label>
            <input type="date" id="med_day">
          </div>

          <div class="field">
            <label for="med_time">Hora</label>
            <input type="time" id="med_time">
          </div>
        </div>
      </div>

      <hr class="section-divider">

      {{-- Resultados relacionados (lab, rayos X, etc.) --}}
      <div class="trat-section">
        <h4 class="trat-section-title">Resultados relacionados</h4>

        <div class="trat-grid">
          <div class="field">
            <label for="tipo_resultado">Tipo de resultado</label>
            <select id="tipo_resultado">
              <option value="">Seleccione...</option>
              <option>Laboratorio</option>
              <option>Rayos X</option>
              <option>Ultrasonido</option>
              <option>Tomografía</option>
              <option>Otro</option>
            </select>
          </div>

          <div class="field">
            <label for="fecha_resultado">Fecha del estudio</label>
            <input type="date" id="fecha_resultado">
          </div>
        </div>

        <label for="t_notas" style="margin-top:10px;">Notas / interpretación de resultados</label>
        <textarea id="t_notas" rows="3"
                  placeholder="Ej. Neumonía en Rx, leucocitos elevados, etc."></textarea>
      </div>

      <div class="btn-container" style="margin-top:12px; gap:10px;">
        {{-- Botón EDITAR (naranja) --}}
        <button type="button" id="tratEditBtn" class="confirm-btn hidden" style="background-color:orange;">
          <img src="/img/editar.png" class="btn-icon" alt="Editar" style="width:22px; height:22px;">
        </button>

        {{-- Botón GUARDAR (verde) --}}
        <button type="submit" id="tratSaveBtn" class="confirm-btn hidden">
          <img src="/img/guardar.png" class="btn-icon" alt="Guardar" style="width:22px; height:22px;">
        </button>

        {{-- Botón CANCELAR (rojo) --}}
        <button type="button" class="modal-cancel-btn" id="tratCancelBtn">
          <img src="/img/cancelar.png" class="btn-icon" alt="Cancelar">
        </button>
      </div>
    </form>

  </div>
</div>


{{-- ===========================
      Modal: Registrar signos vitales
   ============================ --}}
<div id="vitalsModal" class="modal hidden">
  <div class="modal-content modal-lg">

    <h3 class="modal-title">Registrar signos vitales</h3>

    <form id="vitals-form" class="form-container">

      <div class="trat-section">
        <h4 class="trat-section-title">Datos de signos vitales</h4>

        <div class="trat-grid">
          <div class="field">
            <label for="v_fecha">Fecha</label>
            <input type="date" id="v_fecha">
          </div>

          <div class="field">
            <label for="v_temp">Temperatura (°C)</label>
            <input id="v_temp" type="number" step="0.1" placeholder="Ej. 36.5">
          </div>

          <div class="field">
            <label for="v_press">Presión Arterial (mmHg)</label>
            <input id="v_press" placeholder="Ej. 120/80">
          </div>

          <div class="field">
            <label for="v_pulse">Pulso (lpm)</label>
            <input id="v_pulse" type="number" placeholder="Ej. 75">
          </div>

          <div class="field">
            <label for="v_resp">Frecuencia resp. (rpm)</label>
            <input id="v_resp" type="number" placeholder="Ej. 16">
          </div>

          <div class="field">
            <label for="v_spo2">Saturación de oxígeno (%)</label>
            <input id="v_spo2" type="number" placeholder="Ej. 98">
          </div>

          <div class="field">
            <label for="v_peso">Peso (kg)</label>
            <input id="v_peso" type="number" step="0.1" placeholder="Ej. 70.5">
          </div>

          <div class="field">
            <label for="v_altura">Altura (cm)</label>
            <input id="v_altura" type="number" placeholder="Ej. 170">
          </div>
        </div>
      </div>

      <div class="btn-container" style="margin-top:12px; gap:10px;">
        <button class="confirm-btn" type="submit">
          <img src="/img/guardar.png" class="btn-icon" alt="Guardar" style="width:22px; height:22px;">
        </button>
        <button type="button" class="modal-cancel-btn" id="vitalsCancelBtn">
          <img src="/img/cancelar.png" class="btn-icon" alt="Cancelar">
        </button>
      </div>
    </form>

  </div>
</div>

{{-- ====== ALERTA GLOBAL ====== --}}
<div id="appAlertOverlay" class="app-alert-overlay app-alert-hidden">
    <div class="app-alert">
        <div class="app-alert-top"></div>

        <div class="app-alert-card">
            <div class="app-alert-icon-wrapper">
                <img src="/img/templogo.jpg" alt="OK" class="app-alert-icon">
            </div>

            <p id="appAlertText" class="app-alert-text">
                Texto de ejemplo
            </p>

            <button id="appAlertClose" class="app-alert-btn">
                <span>OK</span>
            </button>
        </div>
    </div>
</div>

{{-- ====== CONFIRM GLOBAL ====== --}}
<div id="appConfirmOverlay" class="app-alert-overlay app-alert-hidden">
    <div class="app-alert app-confirm">
        <div class="app-alert-top"></div>

        <div class="app-alert-card">
            <div class="app-alert-icon-wrapper">
                <img src="/img/templogo.jpg" alt="OK" class="app-alert-icon">
            </div>

            <p id="appConfirmText" class="app-alert-text">
                ¿Estás seguro?
            </p>

            <div class="app-confirm-buttons">
                <button id="appConfirmCancel" class="app-alert-btn cancel-btn">
                    <span>Cancelar</span>
                </button>

                <button id="appConfirmOK" class="app-alert-btn">
                    <span>OK</span>
                </button>
            </div>
        </div>
    </div>
</div>


{{-- ==========================================================
      JS
   =========================================================== --}}
<script>
// ====== ALERTA GLOBAL REUTILIZABLE ======
function showAppAlert(message, type = 'success') {
    const overlay = document.getElementById('appAlertOverlay');
    const textEl  = document.getElementById('appAlertText');
    const wrapper = overlay?.querySelector('.app-alert');

    if (!overlay || !textEl || !wrapper) {
        alert(message);
        return;
    }

    textEl.textContent = message;

    wrapper.classList.remove('app-alert--success', 'app-alert--error');
    wrapper.classList.add(
        type === 'error' ? 'app-alert--error' : 'app-alert--success'
    );

    overlay.classList.remove('app-alert-hidden');

    const closeBtn = document.getElementById('appAlertClose');
    const close = () => {
        overlay.classList.add('app-alert-hidden');
        closeBtn.removeEventListener('click', close);
    };

    closeBtn.addEventListener('click', close);
}

// ====== CONFIRM GLOBAL (por si lo necesitas) ======
function showAppConfirm(message, callback) {
    const overlay = document.getElementById('appConfirmOverlay');
    const textEl  = document.getElementById('appConfirmText');
    const okBtn   = document.getElementById('appConfirmOK');
    const cancelBtn = document.getElementById('appConfirmCancel');

    if (!overlay || !textEl || !okBtn || !cancelBtn) {
        const result = confirm(message);
        callback(result);
        return;
    }

    textEl.textContent = message;
    overlay.classList.remove('app-alert-hidden');

    function cleanup() {
        overlay.classList.add('app-alert-hidden');
        okBtn.removeEventListener('click', onOk);
        cancelBtn.removeEventListener('click', onCancel);
    }

    function onOk() {
        cleanup();
        callback(true);
    }

    function onCancel() {
        cleanup();
        callback(false);
    }

    okBtn.addEventListener('click', onOk);
    cancelBtn.addEventListener('click', onCancel);
}

(() => {
  const $ = (id) => document.getElementById(id);

  const txtPaciente = $('txtPaciente');
  const btnBuscar   = $('btnBuscar');
  const patientSuggestions = $('patient-suggestions');
  const nurseLayout = $('nurse-layout');
  const hdrPaciente = $('hdrPaciente');

  const pEdad   = $('pEdad');
  const pGenero = $('pGenero');
  const pDx     = $('pDx');
  const pUltima = $('pUltima');

  const vitalsTableBody = $('vitalsTableBody');
  const treatmentsContainer = $('treatmentsContainer');

  let currentPatientId = null;
  let selectedPatient = null;
  let currentTreatments = [];

  const lnkSignos = $('lnkSignos');
  const lnkTrat   = $('lnkTrat');

  function pacienteSeleccionado() {
    return !(hdrPaciente.textContent.endsWith('—'));
  }

  // ==========================
  //    DYNAMIC PATIENT SEARCH
  // ==========================
  async function searchPatients(query) {
    if (query.length < 2) {
      patientSuggestions.classList.add('hidden');
      return;
    }
    
    try {
      const res = await fetch(`/enfermera/api/paciente?query=${encodeURIComponent(query)}`, {
        credentials: 'same-origin',
        headers: {'Accept': 'application/json'}
      });
      
      if (!res.ok) throw new Error('Search failed');
      
      const patients = await res.json();
      displayPatientSuggestions(patients);
    } catch(err) {
      console.error('Error searching patients:', err);
    }
  }
  
  function displayPatientSuggestions(patients) {
    patientSuggestions.innerHTML = '';
    
    if (patients.length === 0) {
      patientSuggestions.innerHTML = '<div class="suggestion-item">No se encontraron pacientes</div>';
    } else {
      patients.slice(0, 5).forEach(patient => {
        const div = document.createElement('div');
        div.className = 'suggestion-item';
        div.innerHTML = `
          <strong>${patient.name}</strong><br>
          <small>Edad: ${patient.age || 'N/A'} | Género: ${patient.gender || 'N/A'}</small>
        `;
        div.addEventListener('click', () => selectPatient(patient));
        patientSuggestions.appendChild(div);
      });
    }
    
    patientSuggestions.classList.remove('hidden');
  }
  
  async function selectPatient(patient) {
    selectedPatient = patient;
    txtPaciente.value = patient.name;
    patientSuggestions.classList.add('hidden');
    
    // Immediately load patient data
    await loadPatientData(patient);
  }
  
  // Hide suggestions when clicking outside
  document.addEventListener('click', (e) => {
    if (!txtPaciente.contains(e.target) && !patientSuggestions.contains(e.target)) {
      patientSuggestions.classList.add('hidden');
    }
  });
  
  // Real-time search as user types
  txtPaciente.addEventListener('input', (e) => {
    const query = e.target.value.trim();
    searchPatients(query);
  });

  // ==========================
  //    LOAD PATIENT DATA
  // ==========================
  async function loadPatientData(patient) {
    try {
      currentPatientId = patient.id;
      hdrPaciente.textContent = `Paciente: ${patient.name}`;
      pEdad.textContent   = patient.age || '—';

      const genderMap = { 'M':'Masculino', 'F':'Femenino', 'I':'Indefinido' };
      pGenero.textContent = genderMap[patient.gender] || patient.gender || '—';

      pDx.textContent = patient.diagnosis || '—';
      pUltima.textContent = patient.last_consult || '—';

      await loadVitals(currentPatientId);
      await loadTreatments(currentPatientId);

      nurseLayout.style.display = 'grid';
    } catch(err) {
      console.error('Error loading patient data:', err);
      showAppAlert('Error al cargar los datos del paciente.', 'error');
    }
  }

  // ==========================
  //    BUSCAR PACIENTE
  // ==========================
  async function buscarPaciente() {
    const nombre = txtPaciente.value.trim();
    if (!nombre) { 
      showAppAlert('Escribe un nombre o ID de paciente.', 'error');
      return; 
    }

    try {
      const res = await fetch(`/enfermera/api/paciente?query=${encodeURIComponent(nombre)}`, {
        credentials:'same-origin',
        headers:{'Accept':'application/json'}
      });

      if (!res.ok) throw new Error('no remote');
      const list = await res.json();
      if (!list || !list.length) throw new Error('no results');
      const patient = list[0];

      currentPatientId = patient.id;
      hdrPaciente.textContent = `Paciente: ${patient.name}`;
      pEdad.textContent   = patient.age || '—';

      const genderMap = { 'M':'Masculino', 'F':'Femenino', 'I':'Indefinido' };
      pGenero.textContent = genderMap[patient.gender] || patient.gender || '—';

      pDx.textContent = patient.diagnosis || '—';
      pUltima.textContent = patient.last_consult || '—';

      await loadVitals(currentPatientId);
      await loadTreatments(currentPatientId);

    } catch(err) {
      console.error(err);
      showAppAlert('No se encontró ningún paciente.', 'error');
    } finally {
      nurseLayout.style.display = 'grid';
    }
  }

  // Load vitals from API
  async function loadVitals(patientId) {
    try {
      const res = await fetch(`/enfermera/api/vitals?patient_id=${patientId}`, {
        credentials: 'same-origin',
        headers: {'Accept': 'application/json'}
      });
      if (!res.ok) throw new Error('Failed to load vitals');
      const vitals = await res.json();
      renderVitals(vitals);
    } catch(err) {
      console.error('Error loading vitals:', err);
    }
  }

  // Render vitals table
  function renderVitals(vitals) {
    if (!vitals || vitals.length === 0) {
      vitalsTableBody.innerHTML = '<tr><td colspan="8" class="empty-cell">Sin registros.</td></tr>';
      return;
    }

    vitalsTableBody.innerHTML = '';
    vitals.forEach(v => {
      const fecha = v.fecha || '—';
      const temp = v.temp ? v.temp + ' °C' : '—';
      const ta = (v.sbp && v.dbp) ? `${v.sbp}/${v.dbp}` : (v.ta || '—');
      const pulso = v.pulso ? v.pulso + ' lpm' : '—';
      const fr = v.fr ? v.fr + ' rpm' : '—';
      const spo2 = v.spo2 ? v.spo2 + ' %' : '—';
      const peso = v.peso ? v.peso + ' kg' : '—';
      const altura = v.altura ? v.altura + ' cm' : '—';
      
      const tr = document.createElement('tr');
      tr.innerHTML = `
        <td>${fecha}</td>
        <td>${temp}</td>
        <td>${ta}</td>
        <td>${pulso}</td>
        <td>${fr}</td>
        <td>${spo2}</td>
        <td>${peso}</td>
        <td>${altura}</td>
      `;
      vitalsTableBody.appendChild(tr);
    });
  }

  // Load treatments from API
  async function loadTreatments(patientId) {
    try {
      const res = await fetch(`/enfermera/api/treatments?patient_id=${patientId}`, {
        credentials: 'same-origin',
        headers: {'Accept': 'application/json'}
      });
      if (!res.ok) throw new Error('Failed to load treatments');
      const treatments = await res.json();
      currentTreatments = treatments;
      renderTreatments(treatments);
    } catch(err) {
      console.error('Error loading treatments:', err);
    }
  }

  // Render treatments
  function renderTreatments(treatments) {
    if (!treatments || treatments.length === 0) {
      treatmentsContainer.innerHTML = '<p class="muted" style="padding: 20px; text-align: center;">Sin tratamientos registrados</p>';
      return;
    }

    treatmentsContainer.innerHTML = '';
    treatments.forEach(t => {
      const div = document.createElement('div');
      div.className = 'treat-card treatment-item';
      div.dataset.treatmentId = t.id;
      div.dataset.date = t.start_dt || '';
      div.dataset.summary = t.summary || '';
      div.dataset.name = t.name || '';
      div.dataset.dose = t.dose || '';
      div.dataset.instructions = t.instructions || '';
      div.dataset.route = t.route || '';
      div.dataset.resultType = t.result_type || '';
      div.dataset.resultDate = t.result_date || '';
      div.dataset.notes = t.notes || '';
      
      div.innerHTML = `
        <div class="treat-icon">
          <img src="/img/medicina.png" alt="med">
        </div>
        <div class="treat-content">
          <p class="treat-title">Tratamiento del ${t.start_dt_formatted || t.start_dt || 'Sin fecha'}</p>
          <p class="treat-desc">${t.summary || t.name || 'Sin descripción'}</p>
        </div>
      `;
      
      div.addEventListener('click', () => openTratModalFromCard(div));
      treatmentsContainer.appendChild(div);
    });
  }

  btnBuscar.addEventListener('click', buscarPaciente);
  txtPaciente.addEventListener('keydown', (e)=>{ 
    if(e.key === 'Enter'){ 
      e.preventDefault(); 
      buscarPaciente(); 
    } 
  });

  const q = new URLSearchParams(location.search).get('p');
  if (q) { txtPaciente.value = q; buscarPaciente(); }

  // ==========================
  //    MODAL TRATAMIENTO
  // ==========================
  const tratModal    = $('tratModal');
  const tratForm     = $('nurse-trat-form');
  const tratCancel   = $('tratCancelBtn');
  const tratEditBtn  = $('tratEditBtn');
  const tratSaveBtn  = $('tratSaveBtn');

  const tActual      = $('t_actual');
  const tNuevo       = $('t_nuevo');

  let currentTreatmentId = null;

  const allTratFields = tratForm.querySelectorAll('input, textarea, select');

  function setTratReadOnly(isReadOnly) {
    allTratFields.forEach(el => {
      if (el.type !== 'submit' && el.type !== 'button') {
        el.disabled = isReadOnly;
      }
    });
  }

  function setModeView() {
    setTratReadOnly(true);
    tratEditBtn.classList.remove('hidden');
    tratEditBtn.style.display = 'inline-flex';
    tratSaveBtn.classList.add('hidden');
    tratSaveBtn.style.display = 'none';
  }

  function setModeEdit() {
    setTratReadOnly(false);
    tratEditBtn.classList.add('hidden');
    tratEditBtn.style.display = 'none';
    tratSaveBtn.classList.remove('hidden');
    tratSaveBtn.style.display = 'inline-flex';
  }

  function openTratModalCreate() {
    if (!pacienteSeleccionado()) {
      showAppAlert('Primero selecciona un paciente.', 'error');
      return;
    }
    tratForm.reset();
    currentTreatmentId = null;
    
    // Auto-fill tratamiento actual with latest treatment
    if (currentTreatments && currentTreatments.length > 0) {
      const latest = currentTreatments[0];
      const fecha = latest.start_dt || 'Sin fecha';
      const summary = latest.summary || latest.name || 'Sin descripción';
      tActual.value = `Tratamiento del ${fecha}: ${summary}`;
    }
    
    setModeEdit();
    tratModal.classList.remove('hidden');
  }

  function openTratModalFromCard(card) {
    if (!pacienteSeleccionado()) {
      showAppAlert('Primero selecciona un paciente.', 'error');
      return;
    }

    const fecha   = card.dataset.date   || '';
    const summary = card.dataset.summary || card.querySelector('.treat-desc')?.textContent || '';
    const name = card.dataset.name || '';
    const dose = card.dataset.dose || '';
    const instructions = card.dataset.instructions || '';
    const route = card.dataset.route || '';
    const resultType = card.dataset.resultType || '';
    const resultDate = card.dataset.resultDate || '';
    const notes = card.dataset.notes || '';

    currentTreatmentId = card.dataset.treatmentId || null;

    tActual.value = `Tratamiento del ${fecha}: ${summary}`;
    tNuevo.value  = name;
    $('med_name').value = name;
    
    // Parse dose field back into parts (e.g., "500 mg cada 8 horas" -> dosis=500, unidad=mg, freq=cada 8 horas)
    if (dose) {
      const parts = dose.split(' ');
      if (parts.length >= 3) {
        // Has all parts: dose unit frequency...
        $('med_dose').value = parts[0]; // First part is dose
        $('med_unit').value = parts[1]; // Second part is unit
        $('med_freq').value = parts.slice(2).join(' '); // Rest is frequency
      } else if (parts.length === 2) {
        // Has dose and unit only
        $('med_dose').value = parts[0];
        $('med_unit').value = parts[1];
        $('med_freq').value = '';
      } else {
        // Only has dose
        $('med_dose').value = dose;
        $('med_unit').value = '';
        $('med_freq').value = '';
      }
    } else {
      $('med_dose').value = '';
      $('med_unit').value = ''; 
      $('med_freq').value = '';
    }
    
    // Set date and time from fecha (format: YYYY-MM-DD or YYYY-MM-DD HH:MM:SS)
    if (fecha) {
      const dateTimeParts = fecha.split(' ');
      $('med_day').value = dateTimeParts[0]; // Date part
      if (dateTimeParts.length > 1) {
        // Has time component
        const timePart = dateTimeParts[1].substring(0, 5); // Get HH:MM
        $('med_time').value = timePart;
      } else {
        $('med_time').value = '';
      }
    } else {
      $('med_day').value  = '';
      $('med_time').value = '';
    }
    
    $('tipo_resultado').value = resultType;
    $('fecha_resultado').value = resultDate;
    $('t_notas').value = notes || '';

    setModeView();
    tratModal.classList.remove('hidden');
  }

  function closeTratModal() {
    tratModal.classList.add('hidden');
    tratForm.reset();
  }

  lnkTrat.addEventListener('click', (e) => {
    e.preventDefault();
    openTratModalCreate();
  });

  tratEditBtn.addEventListener('click', () => {
    setModeEdit();
  });

  tratCancel.addEventListener('click', closeTratModal);

  tratModal.addEventListener('click', (e) => {
    if (e.target === tratModal) closeTratModal();
  });

  tratForm.addEventListener('submit', async (e) => {
    e.preventDefault();
    
    const name = $('med_name').value.trim() || tNuevo.value.trim();
    const dose = $('med_dose').value.trim();
    const unit = $('med_unit').value;
    const startDate = $('med_day').value;
    const frequency = $('med_freq').value.trim();
    const resultType = $('tipo_resultado').value;
    const resultDate = $('fecha_resultado').value;
    const resultNotes = $('t_notas').value.trim();
    
    if (!name) {
      showAppAlert('Por favor ingresa el nombre del medicamento o tratamiento.', 'error');
      return;
    }
    
    // Always combine dose, unit, and frequency into the dose field
    const finalDose = [dose, unit, frequency].filter(v => v).join(' ');
    
    const treatmentData = {
      patient_id: currentPatientId,
      name: name,
      dose: finalDose,
      instructions: tNuevo.value.trim(),
      start_dt: startDate || new Date().toISOString().split('T')[0],
      route: frequency || null,
      result_type: resultType || null,
      result_date: resultDate || null,
      notes: resultNotes || null,
    };
    
    try {
      let response;
      if (currentTreatmentId) {
        response = await fetch(`/enfermera/api/treatments/${currentTreatmentId}`, {
          method: 'PUT',
          credentials: 'same-origin',
          headers: {
            'Accept': 'application/json',
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content')
          },
          body: JSON.stringify(treatmentData)
        });
      } else {
        response = await fetch('/enfermera/api/treatments', {
          method: 'POST',
          credentials: 'same-origin',
          headers: {
            'Accept': 'application/json',
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content')
          },
          body: JSON.stringify(treatmentData)
        });
      }
      
      if (!response.ok) {
        const errorData = await response.json().catch(() => ({}));
        console.error('Server error:', errorData);
        throw new Error(errorData.message || 'Failed to save treatment');
      }
      
      showAppAlert('✅ Tratamiento guardado exitosamente.', 'success');
      closeTratModal();
      await loadTreatments(currentPatientId);
    } catch(err) {
      console.error('Error saving treatment:', err);
      showAppAlert('❌ Error al guardar el tratamiento: ' + err.message, 'error');
    }
  });

  // ==========================
  //    MODAL SIGNOS VITALES
  // ==========================
  const vitalsModal   = $('vitalsModal');
  const vitalsForm    = $('vitals-form');
  const vitalsCancel  = $('vitalsCancelBtn');
  const vFecha        = $('v_fecha');

  function openVitalsModal() {
    if (!pacienteSeleccionado()) {
      showAppAlert('Primero selecciona un paciente.', 'error');
      return;
    }

    const hoy = new Date().toISOString().slice(0,10);
    vFecha.value = hoy;

    vitalsModal.classList.remove('hidden');
  }

  function closeVitalsModal() {
    vitalsModal.classList.add('hidden');
    vitalsForm.reset();
  }

  lnkSignos.addEventListener('click', (e) => {
    e.preventDefault();
    openVitalsModal();
  });

  vitalsCancel.addEventListener('click', closeVitalsModal);

  vitalsModal.addEventListener('click', (e) => {
    if (e.target === vitalsModal) closeVitalsModal();
  });

  vitalsForm.addEventListener('submit', async (e) => {
    e.preventDefault();
    
    const fecha = $('v_fecha').value;
    const temp = $('v_temp').value;
    const press = $('v_press').value;
    const pulse = $('v_pulse').value;
    const resp = $('v_resp').value;
    const spo2 = $('v_spo2').value;
    const peso = $('v_peso').value;
    const altura = $('v_altura').value;
    
    if (!press) {
      showAppAlert('La presión arterial es obligatoria.', 'error');
      return;
    }
    
    const bpPattern = /^\d{2,3}\/\d{2,3}$/;
    if (!bpPattern.test(press)) {
      showAppAlert('La presión arterial debe seguir el formato: XX/XX o XXX/XXX (Ej. 120/80, 95/60)', 'error');
      return;
    }
    
    const vitalData = {
      patient_id: currentPatientId,
      taken_at: fecha || new Date().toISOString().split('T')[0],
      temp: temp || null,
      ta: press,
      pulso: pulse || null,
      fr: resp || null,
      spo2: spo2 || null,
      peso: peso || null,
      altura: altura || null,
    };
    
    try {
      const response = await fetch('/enfermera/api/vitals', {
        method: 'POST',
        credentials: 'same-origin',
        headers: {
          'Accept': 'application/json',
          'Content-Type': 'application/json',
          'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content')
        },
        body: JSON.stringify(vitalData)
      });
      
      if (!response.ok) throw new Error('Failed to save vitals');
      
      showAppAlert('✅ Signos vitales registrados exitosamente.', 'success');
      closeVitalsModal();
      await loadVitals(currentPatientId);
    } catch(err) {
      console.error('Error saving vitals:', err);
      showAppAlert('❌ Error al registrar signos vitales.', 'error');
    }
  });

})();
</script>
@endsection
