{{-- resources/views/medico/panel.blade.php --}}
@extends('layouts.app')
@section('title','Panel del Médico')

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
    margin-top: 2px;
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
  
  .hidden {
    display: none;
  }
</style>

<main class="dashboard medico-dashboard">
  <h2>Panel del Médico</h2>

  {{-- ==========================
        RESUMEN + GRÁFICA
      =========================== --}}
  <section class="med-top-grid">

    {{-- Citas de hoy --}}
    <div class="med-card med-card--stat">
      <p class="med-stat-label">Citas de hoy</p>
      <p class="med-stat-number" id="statCitasHoy" style="color:#7bc3ab; font-size:90px;">5</p>
      <p class="med-stat-caption">Pacientes pendientes de atender</p>
    </div>

    {{-- Gráfica simple (maquetada) --}}
    <div class="med-card med-card--chart">
      <h3 class="med-card-title">Citas por día (este mes)</h3>
      <div id="miniChart" class="mini-chart">
        {{-- Se rellena / ajusta por JS sólo para maqueta --}}
      </div>
      <p class="med-chart-caption">
        Vista rápida de cuántas citas se han atendido por día.
      </p>
    </div>

  </section>


  {{-- ==========================
        BUSCADOR + HISTORIAL
      =========================== --}}
  <section class="med-history-section">

    {{-- FILTROS --}}
    <div class="med-filters-row">

      <div class="field" style="position: relative;">
        <label for="f_paciente">Buscar paciente</label>
        <div class="search-row">
          <input id="f_paciente" placeholder="Nombre o ID del paciente" autocomplete="off">
          <button id="btnBuscarHist" class="confirm-btn" type="button">
            <img src="/img/buscar.png" class="btn-icon" alt="Buscar">
          </button>
        </div>
        <div id="hist-patient-suggestions" class="suggestions-dropdown hidden"></div>
      </div>

      {{-- Filtro por enfermedad / diagnóstico --}}
      <div class="field" style="position: relative;">
        <label for="f_enfermedad">Filtrar por enfermedad / diagnóstico</label>
        <input id="f_enfermedad" placeholder="Ej. Gastritis, Diabetes..." autocomplete="off">
        <div id="dxSuggestions" class="suggestions-dropdown hidden"></div>
      </div>

    </div>

    {{-- TABLA HISTORIAL --}}
    <div class="med-card med-card--history">
      <h3 class="med-card-title">Historial de expedientes</h3>

      <div class="table-container" style="margin-top:10px;">
        <table>
          <thead>
            <tr>
              <th>Fecha</th>
              <th>Paciente</th>
              <th>Motivo</th>
              <th>Diagnóstico</th>
              <th></th>
            </tr>
          </thead>
          <tbody id="medHistoryRows">
            {{-- Table will be populated by JavaScript from API --}}
          </tbody>
        </table>

        <p id="medHistoryEmpty" class="muted" style="text-align:center;margin-top:10px;">
          Busque un paciente para ver su historial.
        </p>
      </div>
      
      {{-- Pagination Controls --}}
      <div id="historyPagination" class="pagination-container" style="display: none; margin-top: 15px; text-align: center;">
        <button id="prevPage" class="icon-btn" style="margin: 0 5px;">
          <img src="/img/flecha-izquierda.png" alt="Anterior" style="width:20px; height:20px;">
        </button>
        <span id="pageInfo" style="margin: 0 15px; font-weight: 600;">Página 1 de 1</span>
        <button id="nextPage" class="icon-btn" style="margin: 0 5px;">
          <img src="/img/flecha-derecha.png" alt="Siguiente" style="width:20px; height:20px;">
        </button>
      </div>
    </div>

  </section>


  {{-- ==========================
        ALTA DE HISTORIAL
      =========================== --}}
  <section class="med-form-section hidden" id="altaHistorialSection">

    <div class="med-card">
      <h3 class="med-card-title">Alta de historial médico</h3>

      <form id="altaHistForm" class="form-container">

        {{-- Paciente + Fecha --}}
        <div class="trat-grid" style="margin-bottom:12px;">
          <div class="field" style="position: relative;">
            <label for="ah_paciente">Paciente</label>
            <input id="ah_paciente" placeholder="Seleccione un paciente primero" autocomplete="off" disabled>
            <div id="ah-patient-suggestions" class="suggestions-dropdown hidden"></div>
          </div>

          <div class="field">
            <label for="ah_fecha">Fecha</label>
            <input id="ah_fecha" type="date" disabled>
          </div>
        </div>

        <hr class="section-divider">

        {{-- Motivo --}}
        <div class="trat-section">
          <h4 class="trat-section-title">Motivo / Observaciones</h4>
          <textarea id="ah_motivo" rows="2"
                    placeholder="Motivo de la consulta / resumen"></textarea>
        </div>

        <hr class="section-divider">

        {{-- Alergias / Antecedentes --}}
        <div class="trat-section history-grid">
          <div class="field">
            <h4 class="trat-section-title">Alergias</h4>
            <textarea id="ah_alergias" rows="2"
                      placeholder="Ej. Penicilina, mariscos..."></textarea>
          </div>

          <div class="field">
            <h4 class="trat-section-title">Antecedentes</h4>
            <textarea id="ah_antecedentes" rows="2"
                      placeholder="Ej. Diabetes, hipertensión..."></textarea>
          </div>
        </div>

        <hr class="section-divider">

        {{-- Signos vitales básicos --}}
        <div class="trat-section">
          <h4 class="trat-section-title">Signos vitales</h4>

          <div class="trat-grid">
            <div class="field">
              <label for="ah_temp">Temperatura (°C)</label>
              <input id="ah_temp" type="number" step="0.1" placeholder="36.5">
            </div>
            <div class="field">
              <label for="ah_press">Presión arterial (mmHg)</label>
              <input id="ah_press" placeholder="120/80">
            </div>
            <div class="field">
              <label for="ah_pulse">Pulso (lpm)</label>
              <input id="ah_pulse" type="number" placeholder="75">
            </div>
            <div class="field">
              <label for="ah_resp">Frecuencia resp. (rpm)</label>
              <input id="ah_resp" type="number" placeholder="16">
            </div>
            <div class="field">
              <label for="ah_spo2">SpO₂ (%)</label>
              <input id="ah_spo2" type="number" placeholder="98">
            </div>
            <div class="field">
              <label for="ah_peso">Peso (kg)</label>
              <input id="ah_peso" type="number" step="0.1" placeholder="70.0">
            </div>
            <div class="field">
              <label for="ah_altura">Altura (cm)</label>
              <input id="ah_altura" type="number" placeholder="170">
            </div>
          </div>
          <hr class="section-divider">

          {{-- Diagnostico --}}
          <div class="trat-section">
            <h4 class="trat-section-title">Diagnóstico</h4>
            <input id="ah_diagnostico" placeholder="Ej. Gastritis aguda, Diabetes mellitus tipo 2">
          </div>
        </div>
        <div class="btn-container" style="margin-top:14px;">
          <button type="submit" class="confirm-btn">
            <img src="/img/guardar.png" class="btn-icon" alt="Guardar" style="width:24px; height:24px;">
          </button>
          <button type="button" id="altaClearBtn" class="cancel-btn" style="background-color: orange;" onmouseover="this.style.backgroundColor='darkorange'" onmouseout="this.style.backgroundColor='orange'">
            <img src="/img/limpiar.png" class="btn-icon" alt="Limpiar">
          </button>
        </div>

      </form>
    </div>

  </section>


  {{-- ==========================
        SUBIR DOCUMENTOS
      =========================== --}}
  <section class="med-docs-section hidden" id="subirDocumentosSection">

    <div class="med-card">
      <h3 class="med-card-title">Subir documentos</h3>

      <div class="med-docs-patient">
        <p><strong>Paciente:</strong> <span id="docsPaciente">—</span></p>
      </div>

      <form id="docsForm" class="form-container">

        <div class="trat-grid" style="margin-bottom:12px;">
          <div class="field">
            <label for="doc_tipo">Tipo</label>
            <select id="doc_tipo">
              <option value="">Seleccione...</option>
              <option>Radiografía</option>
              <option>Análisis</option>
              <option>Receta</option>
              <option>Referencia</option>
              <option>Otro</option>
            </select>
          </div>

          <div class="field">
            <label for="doc_titulo">Título</label>
            <input id="doc_titulo" placeholder="Ej. Radiografía de tórax">
          </div>
        </div>

        <div class="med-dropzone">
          <p>Arrastra y suelta archivos aquí o <span class="med-link">haz clic</span> para seleccionarlos.</p>
          <p class="med-dropzone-hint">Acepta PDF, imágenes y documentos comunes. Máx. 10 MB por archivo.</p>
          <input type="file" id="doc_files" multiple style="display:none;">
        </div>

        <p id="docEmpty" class="muted" style="text-align:center;margin-top:8px;">
          Sin archivos seleccionados.
        </p>

        <div class="btn-container" style="margin-top:14px;">
          <button type="submit" class="confirm-btn">
            <img src="/img/guardar.png" class="btn-icon" alt="Subir" style="width:24px; height:24px;">
          </button>
          <button type="button" class="cancel-btn" id="docClearBtn" style="background-color: orange;" onmouseover="this.style.backgroundColor='darkorange'" onmouseout="this.style.backgroundColor='orange'">
            <img src="/img/limpiar.png" class="btn-icon" alt="Limpiar">
          </button>
        </div>

      </form>
    </div>

    {{-- Documentos ya subidos (demo) --}}
    <div class="med-card">
      <h3 class="med-card-title">Documentos del paciente</h3>

      <div id="docsList" class="docs-list">
        {{-- Documents will be loaded dynamically --}}
      </div>

      <p id="docsListEmpty" class="muted" style="text-align:center;margin-top:8px;">
        Aún no hay documentos.
      </p>
    </div>

  </section>


  {{-- ==========================
        MODAL DETALLE HISTORIAL
      =========================== --}}
  <div id="medHistoryModal" class="modal hidden">
    <div class="modal-content modal-lg">

      <h3 class="modal-title">Detalle de historial</h3>

      <div class="history-modal-body">

        <div class="history-section">
          <h4>Datos de la consulta</h4>
          <p><strong>Fecha:</strong> <span id="mh_fecha">—</span></p>
          <p><strong>Paciente:</strong> <span id="mh_paciente">—</span></p>
          <p><strong>Tipo:</strong> <span id="mh_dx">—</span></p>
        </div>

        <hr class="section-divider">

        <div class="history-section">
          <h4>Motivo / Observaciones</h4>
          <p id="mh_motivo">—</p>
        </div>

        <hr class="section-divider">

        <div class="history-section history-grid">
          <div>
            <h4>Alergias</h4>
            <p id="mh_alergias">—</p>
          </div>
          <div>
            <h4>Antecedentes</h4>
            <p id="mh_antecedentes">—</p>
          </div>
        </div>

        <hr class="section-divider">

        <div class="history-section">
          <h4>Signos vitales</h4>
          <ul class="vitals-list">
            <li><strong>Temperatura:</strong> <span id="mh_temp">—</span></li>
            <li><strong>Presión arterial:</strong> <span id="mh_press">—</span></li>
            <li><strong>Pulso:</strong> <span id="mh_pulse">—</span></li>
            <li><strong>Frecuencia respiratoria:</strong> <span id="mh_fr">—</span></li>
            <li><strong>Saturación de oxígeno:</strong> <span id="mh_spo2">—</span></li>
            <li><strong>Peso:</strong> <span id="mh_peso">—</span></li>
            <li><strong>Altura:</strong> <span id="mh_altura">—</span></li>
          </ul>
        </div>

        <hr class="section-divider">

        <div class="history-section">
          <h4>Tratamiento indicado</h4>
          <p id="mh_trat">—</p>
        </div>

        <hr class="section-divider">

        <div class="history-section">
          <h4>Documentos asociados</h4>
          <p id="mh_docs">—</p>
        </div>

      </div>

      <div class="btn-container" style="margin-top:14px;">
        <button type="button" class="modal-cancel-btn" id="medHistoryCloseBtn">
          <img src="/img/cancelar.png" class="btn-icon" alt="Cerrar">
        </button>
      </div>

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

</main>

{{-- ================= JS ================= --}}
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

document.addEventListener('DOMContentLoaded', async () => {
  // ----------- Load Dashboard Stats -----------
  const statCitasHoy = document.getElementById('statCitasHoy');
  const miniChart = document.getElementById('miniChart');

  async function loadDashboardStats() {
    try {
      const response = await fetch('/medico/api/dashboard', {
        headers: {
          'X-Requested-With': 'XMLHttpRequest',
          'Accept': 'application/json',
          'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content')
        }
      });
      
      if (!response.ok) throw new Error('Failed to load dashboard stats');
      
      const data = await response.json();
      
      // Update today's appointments count
      statCitasHoy.textContent = data.citas_hoy || 0;
      
      // Render chart
      miniChart.innerHTML = '';
      const maxVal = Math.max(...data.chart_data.map(d => d.value)) || 1;
      
      data.chart_data.forEach(d => {
        const bar = document.createElement('div');
        bar.className = 'mini-chart-bar';
        bar.style.height = (d.value / maxVal * 100) + '%';
        bar.innerHTML = `<span class="mini-chart-value">${d.value}</span>
                         <span class="mini-chart-label">${d.label}</span>`;
        miniChart.appendChild(bar);
      });
      
    } catch (error) {
      console.error('Error loading dashboard stats:', error);
      statCitasHoy.textContent = '0';
    }
  }

  // Load stats on page load
  await loadDashboardStats();

  // ----------- Patient Search and History -----------
  const fPaciente    = document.getElementById('f_paciente');
  const fEnfermedad  = document.getElementById('f_enfermedad');
  const btnBuscarHist = document.getElementById('btnBuscarHist');
  const histRows     = document.getElementById('medHistoryRows');
  const histEmpty    = document.getElementById('medHistoryEmpty');
  const histPatientSuggestions = document.getElementById('hist-patient-suggestions');

  let currentPatientId = null;
  let currentPatientName = null;
  let allHistoryData = [];
  let filteredHistoryData = [];
  let histSearchTimeout = null;
  let currentPage = 1;
  const itemsPerPage = 5;
  
  const historyPagination = document.getElementById('historyPagination');
  const pageInfo = document.getElementById('pageInfo');
  const prevPageBtn = document.getElementById('prevPage');
  const nextPageBtn = document.getElementById('nextPage');
  const docsPacienteSpan = document.getElementById('docsPaciente');
  const docsList = document.getElementById('docsList');
  const docsListEmpty = document.getElementById('docsListEmpty');

  async function searchAndLoadHistory() {
    const query = fPaciente.value.trim();
    if (!query) {
      showAppAlert('Por favor ingrese un nombre o ID de paciente', 'error');
      return;
    }

    try {
      const response = await fetch(`/medico/api/patients?query=${encodeURIComponent(query)}`, {
        headers: {
          'X-Requested-With': 'XMLHttpRequest',
          'Accept': 'application/json',
          'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content')
        }
      });
      
      if (!response.ok) throw new Error('Failed to search patients');
      
      const patients = await response.json();
      
      if (patients.length === 0) {
        showAppAlert('No se encontró ningún paciente con ese nombre o ID', 'error');
        return;
      }
      
      if (patients.length === 1) {
        // Load history for this patient
        currentPatientId = patients[0].id;
        currentPatientName = patients[0].name;
        await loadPatientHistory(currentPatientId);
      } else {
        // Multiple matches - show selection
        const names = patients.map((p, i) => `${i+1}. ${p.name} (${p.age || 'Sin edad'})`).join('\n');
        const selection = prompt(`Se encontraron ${patients.length} pacientes:\n${names}\n\nIngrese el número del paciente:`);
        const idx = parseInt(selection) - 1;
        if (idx >= 0 && idx < patients.length) {
          currentPatientId = patients[idx].id;
          currentPatientName = patients[idx].name;
          await loadPatientHistory(currentPatientId);
        }
      }
      
    } catch (error) {
      console.error('Error searching patients:', error);
      showAppAlert('Error al buscar pacientes', 'error');
    }
  }

  async function loadPatientHistory(patientId) {
    try {
      const response = await fetch(`/medico/api/history?patient_id=${patientId}`, {
        headers: {
          'X-Requested-With': 'XMLHttpRequest',
          'Accept': 'application/json',
          'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content')
        }
      });
      
      if (!response.ok) throw new Error('Failed to load history');
      
      allHistoryData = await response.json();
      
      // Get patient name from first record if available
      if (allHistoryData.length > 0 && allHistoryData[0].paciente) {
        currentPatientName = allHistoryData[0].paciente;
        docsPacienteSpan.textContent = currentPatientName;
      }
      
      renderHistoryTable(allHistoryData);
      
      // Show the sections after patient is selected
      document.getElementById('altaHistorialSection').classList.remove('hidden');
      document.getElementById('subirDocumentosSection').classList.remove('hidden');
      
      // Autofill and enable the alta historial form
      const ahPacienteInput = document.getElementById('ah_paciente');
      const ahFechaInput = document.getElementById('ah_fecha');
      
      ahPacienteInput.value = currentPatientName;
      ahPacienteInput.disabled = true;
      
      // Set current date
      const today = new Date().toISOString().split('T')[0];
      ahFechaInput.value = today;
      ahFechaInput.disabled = true;
      
      // Load patient documents and vital signs
      await loadPatientDocuments(patientId);
      await loadAndAutofillVitals(patientId);
      
    } catch (error) {
      console.error('Error loading history:', error);
      showAppAlert('Error al cargar el historial', 'error');
    }
  }

  async function loadAndAutofillVitals(patientId) {
    // Clear all vital signs fields first
    document.getElementById('ah_temp').value = '';
    document.getElementById('ah_press').value = '';
    document.getElementById('ah_pulse').value = '';
    document.getElementById('ah_resp').value = '';
    document.getElementById('ah_spo2').value = '';
    document.getElementById('ah_peso').value = '';
    document.getElementById('ah_altura').value = '';
    
    try {
      const response = await fetch(`/medico/api/vitals?patient_id=${patientId}`, {
        headers: {
          'X-Requested-With': 'XMLHttpRequest',
          'Accept': 'application/json',
          'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content')
        }
      });
      
      if (!response.ok) throw new Error('Failed to load vitals');
      
      const vitals = await response.json();
      
      // If there are vitals, autofill with the most recent one
      if (vitals && vitals.length > 0) {
        const mostRecent = vitals[0]; // Already ordered by taken_at desc
        
        // Autofill vital signs fields
        if (mostRecent.temp) document.getElementById('ah_temp').value = mostRecent.temp;
        if (mostRecent.sbp && mostRecent.dbp) {
          document.getElementById('ah_press').value = `${mostRecent.sbp}/${mostRecent.dbp}`;
        }
        if (mostRecent.pulso) document.getElementById('ah_pulse').value = mostRecent.pulso;
        if (mostRecent.fr) document.getElementById('ah_resp').value = mostRecent.fr;
        if (mostRecent.spo2) document.getElementById('ah_spo2').value = mostRecent.spo2;
        if (mostRecent.peso) document.getElementById('ah_peso').value = mostRecent.peso;
        if (mostRecent.talla) document.getElementById('ah_altura').value = mostRecent.talla;
      }
      
    } catch (error) {
      console.error('Error loading vitals:', error);
      // Don't alert, just leave fields empty
    }
  }

  async function loadPatientDocuments(patientId) {
    try {
      const response = await fetch(`/medico/api/documentos?patient_id=${patientId}`, {
        headers: {
          'X-Requested-With': 'XMLHttpRequest',
          'Accept': 'application/json',
          'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content')
        }
      });
      
      if (!response.ok) throw new Error('Failed to load documents');
      
      const documents = await response.json();
      renderDocumentsList(documents);
      
    } catch (error) {
      console.error('Error loading documents:', error);
      // Don't alert, just show empty state
      renderDocumentsList([]);
    }
  }

  function renderDocumentsList(documents) {
    docsList.innerHTML = '';
    
    if (!documents || documents.length === 0) {
      docsListEmpty.style.display = 'block';
      return;
    }
    
    docsListEmpty.style.display = 'none';
    
    documents.forEach(doc => {
      const div = document.createElement('div');
      div.className = 'doc-item';
      div.innerHTML = `
        <img src="/img/documento.png" class="doc-icon" alt="Doc" style="width:24px; height:24px;">
        <span class="doc-name">${doc.title || doc.doc_type || 'Documento'}</span>
        <a href="/medico/api/documentos/${doc.id}/download" class="doc-btn" target="_blank">
          <img src="/img/visualizar.png" alt="Ver" style="width:20px; height:20px;">
        </a>
      `;
      docsList.appendChild(div);
    });
  }

  function renderHistoryTable(historyData, resetPage = true) {
    filteredHistoryData = historyData || [];
    
    if (resetPage) {
      currentPage = 1;
    }
    
    histRows.innerHTML = '';
    
    if (!filteredHistoryData || filteredHistoryData.length === 0) {
      histEmpty.style.display = 'block';
      histEmpty.textContent = 'No se encontró historial para este paciente.';
      historyPagination.style.display = 'none';
      return;
    }
    
    histEmpty.style.display = 'none';
    
    // Calculate pagination
    const totalPages = Math.ceil(filteredHistoryData.length / itemsPerPage);
    const startIndex = (currentPage - 1) * itemsPerPage;
    const endIndex = startIndex + itemsPerPage;
    const pageData = filteredHistoryData.slice(startIndex, endIndex);
    
    // Render current page data
    pageData.forEach(h => {
      const tr = document.createElement('tr');
      tr.innerHTML = `
        <td>${h.fecha || '—'}</td>
        <td>${h.paciente || '—'}</td>
        <td>${h.detalle || h.tipo || '—'}</td>
        <td>${h.diagnostico || '—'}</td>
        <td class="history-actions">
          <button type="button" class="icon-btn med-history-detail"
                  data-id="${h.id || ''}"
                  data-fecha="${h.fecha || ''}"
                  data-tipo="${h.tipo || ''}"
                  data-detalle="${h.detalle || ''}"
                  data-diagnostico="${h.diagnostico || ''}"
                  data-autor="${h.autor || ''}"
                  data-motivo="${h.motivo || ''}"
                  data-antecedentes="${h.antecedentes || ''}"
                  data-alergias="${h.alergias || ''}"
                  data-tratamiento="${h.tratamiento || ''}"
                  data-notas="${h.notas || ''}"
                  data-temperatura="${h.temperatura || ''}"
                  data-presion="${h.presion || ''}"
                  data-pulso="${h.pulso || ''}"
                  data-frecuencia-respiratoria="${h.frecuencia_respiratoria || ''}"
                  data-spo2="${h.spo2 || ''}"
                  data-peso="${h.peso || ''}"
                  data-altura="${h.altura || ''}">
            <a href="#" class="doc-btn" style="font-size: 23px;">
              <img src="/img/visualizar.png" alt="Ver detalle" style="width:20px; height:20px;">
            </a>
          </button>
        </td>
      `;
      histRows.appendChild(tr);
    });
    
    // Update pagination controls
    if (totalPages > 1) {
      historyPagination.style.display = 'block';
      pageInfo.textContent = `Página ${currentPage} de ${totalPages}`;
      prevPageBtn.disabled = currentPage === 1;
      nextPageBtn.disabled = currentPage === totalPages;
    } else {
      historyPagination.style.display = 'none';
    }
    
    // Re-attach event listeners to new buttons
    document.querySelectorAll('.med-history-detail').forEach(btn => {
      btn.addEventListener('click', (e) => {
        e.preventDefault();
        openHistoryModal(btn);
      });
    });
  }
  
  // Pagination event listeners
  prevPageBtn.addEventListener('click', () => {
    if (currentPage > 1) {
      currentPage--;
      renderHistoryTable(filteredHistoryData, false);
    }
  });
  
  nextPageBtn.addEventListener('click', () => {
    const totalPages = Math.ceil(filteredHistoryData.length / itemsPerPage);
    if (currentPage < totalPages) {
      currentPage++;
      renderHistoryTable(filteredHistoryData, false);
    }
  });

  function applyHistoryFilter() {
    const termPac = fPaciente.value.trim().toLowerCase();
    const termEnf = fEnfermedad.value.trim().toLowerCase();

    // If only disease filter is active and no data loaded, search for patients with that diagnosis
    if (!termPac && termEnf && allHistoryData.length === 0) {
      searchByDiagnosis(termEnf);
      return;
    }

    if (!termPac && !termEnf) {
      // No filters, show all data
      renderHistoryTable(allHistoryData, true);
      return;
    }

    // Filter the data
    const filtered = allHistoryData.filter(h => {
      const fecha = (h.fecha || '').toLowerCase();
      const pac = (h.paciente || '').toLowerCase();
      const mot = (h.detalle || '').toLowerCase();
      const dx = (h.diagnostico || '').toLowerCase();

      const okPac = !termPac || pac.includes(termPac) || fecha.includes(termPac);
      const okEnf = !termEnf || dx.includes(termEnf) || mot.includes(termEnf);

      return okPac && okEnf;
    });

    renderHistoryTable(filtered, true);
  }

  // Search for patients by diagnosis
  async function searchByDiagnosis(diagnosis) {
    try {
      // Get all patients with this diagnosis
      const response = await fetch(`/medico/api/history?diagnosis=${encodeURIComponent(diagnosis)}`, {
        headers: {
          'X-Requested-With': 'XMLHttpRequest',
          'Accept': 'application/json',
          'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content')
        }
      });

      if (!response.ok) throw new Error('Failed to search by diagnosis');

      const historyData = await response.json();
      
      if (historyData.length === 0) {
        histEmpty.style.display = 'block';
        histEmpty.textContent = 'No se encontraron pacientes con este diagnóstico.';
        histRows.innerHTML = '';
        historyPagination.style.display = 'none';
        return;
      }

      allHistoryData = historyData;
      renderHistoryTable(allHistoryData, true);

      // Show the sections
      document.getElementById('altaHistorialSection').classList.remove('hidden');
      document.getElementById('subirDocumentosSection').classList.remove('hidden');

    } catch (error) {
      console.error('Error searching by diagnosis:', error);
      showAppAlert('Error al buscar por diagnóstico', 'error');
    }
  }

  // Real-time search for history patient field
  async function searchPatientsForHistory(query) {
    if (query.length < 2) {
      histPatientSuggestions.classList.add('hidden');
      return;
    }

    try {
      const response = await fetch(`/medico/api/patients?query=${encodeURIComponent(query)}`, {
        headers: {
          'X-Requested-With': 'XMLHttpRequest',
          'Accept': 'application/json',
          'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content')
        }
      });

      if (!response.ok) throw new Error('Failed to search patients');

      const patients = await response.json();
      displayHistoryPatientSuggestions(patients);

    } catch (error) {
      console.error('Error searching patients:', error);
    }
  }

  function displayHistoryPatientSuggestions(patients) {
    histPatientSuggestions.innerHTML = '';

    if (patients.length === 0) {
      histPatientSuggestions.innerHTML = '<div class="suggestion-item">No se encontraron pacientes</div>';
    } else {
      patients.slice(0, 5).forEach(patient => {
        const div = document.createElement('div');
        div.className = 'suggestion-item';
        div.innerHTML = `
          <strong>${patient.name}</strong><br>
          <small>Edad: ${patient.age || 'N/A'} | Género: ${patient.gender || 'N/A'}</small>
        `;
        div.addEventListener('click', () => selectHistoryPatient(patient));
        histPatientSuggestions.appendChild(div);
      });
    }

    histPatientSuggestions.classList.remove('hidden');
  }

  async function selectHistoryPatient(patient) {
    fPaciente.value = patient.name;
    currentPatientId = patient.id;
    currentPatientName = patient.name;
    histPatientSuggestions.classList.add('hidden');
    // Automatically load the patient's history and documents
    await loadPatientHistory(currentPatientId);
    await loadPatientDocuments(currentPatientId);
    
    // Apply disease filter if it exists
    const termEnf = fEnfermedad.value.trim().toLowerCase();
    if (termEnf) {
      applyHistoryFilter();
    }
  }

  fPaciente.addEventListener('input', () => {
    clearTimeout(histSearchTimeout);
    
    // If input is cleared, reset patient data
    if (fPaciente.value.trim() === '') {
      histPatientSuggestions.classList.add('hidden');
      currentPatientId = null;
      currentPatientName = '';
      
      // Check if there's a disease filter active
      const termEnf = fEnfermedad.value.trim().toLowerCase();
      if (termEnf) {
        // Search by disease only
        searchByDiagnosis(termEnf);
      } else {
        // Clear history table and hide sections
        allHistoryData = [];
        histRows.innerHTML = '';
        histEmpty.style.display = 'block';
        histEmpty.textContent = 'No se ha buscado ningún paciente aún.';
        historyPagination.style.display = 'none';
        
        // Hide sections until a new search
        document.getElementById('altaHistorialSection').classList.add('hidden');
        document.getElementById('subirDocumentosSection').classList.add('hidden');
        const docPacSec = document.getElementById('documentosPacienteSection');
        if (docPacSec) {
          docPacSec.classList.add('hidden');
        }
      }
      
      return;
    }
    
    histSearchTimeout = setTimeout(() => {
      searchPatientsForHistory(fPaciente.value.trim());
    }, 300);
  });

  // Close suggestions when clicking outside
  document.addEventListener('click', (e) => {
    if (!fPaciente.contains(e.target) && !histPatientSuggestions.contains(e.target)) {
      histPatientSuggestions.classList.add('hidden');
    }
  });

  btnBuscarHist.addEventListener('click', searchAndLoadHistory);
  fEnfermedad.addEventListener('input', applyHistoryFilter);

  // ----------- Autocomplete de diagnóstico -----------
  const dxInput = document.getElementById('f_enfermedad');
  const dxBox = document.getElementById('dxSuggestions');
  let dxSearchTimeout = null;
  let allDiagnoses = [];

  // Load all diagnoses from the database on page load
  async function loadAllDiagnoses() {
    try {
      const response = await fetch('/medico/api/diagnoses', {
        headers: {
          'X-Requested-With': 'XMLHttpRequest',
          'Accept': 'application/json',
          'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content')
        }
      });

      if (!response.ok) throw new Error('Failed to load diagnoses');

      allDiagnoses = await response.json();
    } catch (error) {
      console.error('Error loading diagnoses:', error);
      allDiagnoses = [];
    }
  }

  // Call on page load
  loadAllDiagnoses();

  function renderDxSuggestions(term) {
    dxBox.innerHTML = '';

    const clean = term.trim().toLowerCase();
    let matches = [];

    if (!clean) {
      matches = allDiagnoses.slice(0, 10); // Show first 10 when no search term
    } else {
      matches = allDiagnoses.filter(dx =>
        dx.toLowerCase().includes(clean)
      );
    }

    if (!matches.length) {
      if (allDiagnoses.length === 0) {
        dxBox.innerHTML = '<div class="suggestion-item">Cargando diagnósticos...</div>';
      } else {
        dxBox.innerHTML = '<div class="suggestion-item">No se encontraron coincidencias</div>';
      }
      dxBox.classList.remove('hidden');
      return;
    }

    matches.forEach(dx => {
      const div = document.createElement('div');
      div.className = 'suggestion-item';
      div.textContent = dx;
      div.addEventListener('click', () => {
        dxInput.value = dx;
        dxBox.classList.add('hidden');
        applyHistoryFilter();
      });
      dxBox.appendChild(div);
    });

    dxBox.classList.remove('hidden');
  }

  // Show suggestions when focusing
  dxInput.addEventListener('focus', () => {
    renderDxSuggestions(dxInput.value);
  });

  // Filter while typing with debounce
  dxInput.addEventListener('input', () => {
    clearTimeout(dxSearchTimeout);
    dxSearchTimeout = setTimeout(() => {
      renderDxSuggestions(dxInput.value);
      applyHistoryFilter();
    }, 300);
  });

  // Close suggestions when clicking outside
  document.addEventListener('click', (e) => {
    if (!dxBox.contains(e.target) && e.target !== dxInput) {
      dxBox.classList.add('hidden');
    }
  });

  // ----------- Modal detalle historial -----------
  const historyModal   = document.getElementById('medHistoryModal');
  const historyClose   = document.getElementById('medHistoryCloseBtn');

  const spanFecha   = document.getElementById('mh_fecha');
  const spanPac     = document.getElementById('mh_paciente');
  const spanDx      = document.getElementById('mh_dx');
  const spanMotivo  = document.getElementById('mh_motivo');
  const spanAlerg   = document.getElementById('mh_alergias');
  const spanAnteced = document.getElementById('mh_antecedentes');
  const spanTemp    = document.getElementById('mh_temp');
  const spanPress   = document.getElementById('mh_press');
  const spanPulse   = document.getElementById('mh_pulse');
  const spanFR      = document.getElementById('mh_fr');
  const spanSpO2    = document.getElementById('mh_spo2');
  const spanPeso    = document.getElementById('mh_peso');
  const spanAltura  = document.getElementById('mh_altura');
  const spanTrat    = document.getElementById('mh_trat');
  const spanDocs    = document.getElementById('mh_docs');

  async function openHistoryModal(btn) {
    const tipo = btn.dataset.tipo || '';
    const historyId = btn.dataset.id || '';
    
    // Common fields
    spanFecha.textContent = btn.dataset.fecha || '—';
    spanPac.textContent = currentPatientName || '—';
    spanDx.textContent = tipo || '—';
    
    // Reset all fields first
    spanMotivo.textContent = '—';
    spanAlerg.textContent = '—';
    spanAnteced.textContent = '—';
    spanTemp.textContent = '—';
    spanPress.textContent = '—';
    spanPulse.textContent = '—';
    spanFR.textContent = '—';
    spanSpO2.textContent = '—';
    spanPeso.textContent = '—';
    spanAltura.textContent = '—';
    spanTrat.textContent = '—';
    spanDocs.textContent = '—';
    
    // Fill based on tipo
    if (tipo === 'Historial') {
      spanMotivo.textContent = btn.dataset.motivo || '—';
      spanAlerg.textContent = btn.dataset.alergias || '—';
      spanAnteced.textContent = btn.dataset.antecedentes || '—';
      
      // Fetch additional vital signs and treatments
      if (historyId) {
        try {
          const response = await fetch(`/medico/api/history-detail?history_id=${historyId}&tipo=${tipo}`, {
            headers: {
              'X-Requested-With': 'XMLHttpRequest',
              'Accept': 'application/json',
              'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content')
            }
          });
          
          if (response.ok) {
            const data = await response.json();
            
            // Fill vital signs if available
            if (data.vitals) {
              spanTemp.textContent = data.vitals.temperatura ? data.vitals.temperatura + ' °C' : '—';
              spanPress.textContent = data.vitals.presion ? data.vitals.presion + ' mmHg' : '—';
              spanPulse.textContent = data.vitals.pulso ? data.vitals.pulso + ' lpm' : '—';
              spanFR.textContent = data.vitals.frecuencia_respiratoria ? data.vitals.frecuencia_respiratoria + ' rpm' : '—';
              spanSpO2.textContent = data.vitals.spo2 ? data.vitals.spo2 + ' %' : '—';
              spanPeso.textContent = data.vitals.peso ? data.vitals.peso + ' kg' : '—';
              spanAltura.textContent = data.vitals.altura ? data.vitals.altura + ' cm' : '—';
            }
            
            // Fill treatments if available
            if (data.tratamientos && data.tratamientos.length > 0) {
              spanTrat.textContent = data.tratamientos.join('; ');
            }
          }
        } catch (error) {
          console.error('Error fetching history details:', error);
        }
      }
    } else if (tipo === 'Signos vitales') {
      spanTemp.textContent = btn.dataset.temperatura ? btn.dataset.temperatura + ' °C' : '—';
      spanPress.textContent = btn.dataset.presion ? btn.dataset.presion + ' mmHg' : '—';
      spanPulse.textContent = btn.dataset.pulso ? btn.dataset.pulso + ' lpm' : '—';
      spanFR.textContent = btn.dataset.frecuenciaRespiratoria ? btn.dataset.frecuenciaRespiratoria + ' rpm' : '—';
      spanSpO2.textContent = btn.dataset.spo2 ? btn.dataset.spo2 + ' %' : '—';
      spanPeso.textContent = btn.dataset.peso ? btn.dataset.peso + ' kg' : '—';
      spanAltura.textContent = btn.dataset.altura ? btn.dataset.altura + ' cm' : '—';
    } else if (tipo === 'Tratamiento') {
      spanTrat.textContent = btn.dataset.tratamiento || '—';
      spanMotivo.textContent = btn.dataset.notas || '—';
    } else if (tipo === 'Cita' || tipo === 'Encuentro') {
      spanMotivo.textContent = btn.dataset.motivo || '—';
      if (btn.dataset.notas) {
        spanMotivo.textContent += ' - ' + btn.dataset.notas;
      }
    } else {
      spanMotivo.textContent = btn.dataset.detalle || '—';
    }
    
    // Always show author
    spanDocs.textContent = btn.dataset.autor ? 'Autor: ' + btn.dataset.autor : '—';

    historyModal.classList.remove('hidden');
  }

  document.querySelectorAll('.med-history-detail').forEach(btn => {
    btn.addEventListener('click', () => openHistoryModal(btn));
  });

  function closeHistoryModal() {
    historyModal.classList.add('hidden');
  }

  historyClose.addEventListener('click', closeHistoryModal);
  historyModal.addEventListener('click', e => {
    if (e.target === historyModal) closeHistoryModal();
  });

  // ----------- Subir documentos (maqueta) -----------
  const dropzone = document.querySelector('.med-dropzone');
  const fileInput = document.getElementById('doc_files');
  const docEmpty  = document.getElementById('docEmpty');
  const docClear  = document.getElementById('docClearBtn');

  dropzone.addEventListener('click', () => fileInput.click());

  fileInput.addEventListener('change', () => {
    if (fileInput.files.length) {
      docEmpty.textContent = `${fileInput.files.length} archivo(s) seleccionado(s).`;
    } else {
      docEmpty.textContent = 'Sin archivos seleccionados.';
    }
  });

  docClear.addEventListener('click', () => {
    docTipo.value = '';
    docTitulo.value = '';
    fileInput.value = '';
    docEmpty.textContent = 'Sin archivos seleccionados.';
  });

  // ----------- Subir documentos form submission -----------
  const docsForm = document.getElementById('docsForm');
  const docTipo = document.getElementById('doc_tipo');
  const docTitulo = document.getElementById('doc_titulo');

  docsForm.addEventListener('submit', async (e) => {
    e.preventDefault();

    if (!currentPatientId) {
      showAppAlert('Por favor seleccione un paciente primero', 'error');
      return;
    }

    if (!fileInput.files.length) {
      showAppAlert('Por favor seleccione al menos un archivo', 'error');
      return;
    }

    const formData = new FormData();
    formData.append('patient_id', currentPatientId);
    formData.append('doc_type', docTipo.value || 'Otro');
    formData.append('title', docTitulo.value || 'Documento');

    // Append all selected files
    for (let i = 0; i < fileInput.files.length; i++) {
      formData.append('files[]', fileInput.files[i]);
    }

    try {
      const response = await fetch('/medico/api/upload-documentos', {
        method: 'POST',
        headers: {
          'X-Requested-With': 'XMLHttpRequest',
          'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content')
        },
        body: formData
      });

      if (!response.ok) {
        const errorData = await response.json();
        throw new Error(errorData.error || 'Failed to upload documents');
      }

      const result = await response.json();
      showAppAlert('Documentos subidos exitosamente', 'success');

      // Clear form
      docTipo.value = '';
      docTitulo.value = '';
      fileInput.value = '';
      docEmpty.textContent = 'Sin archivos seleccionados.';

      // Reload documents list
      await loadPatientDocuments(currentPatientId);

    } catch (error) {
      console.error('Error uploading documents:', error);
      showAppAlert('Error al subir documentos: ' + error.message, 'error');
    }
  });

  // ----------- Alta de historial clear button -----------
  const altaClearBtn = document.getElementById('altaClearBtn');
  
  altaClearBtn.addEventListener('click', () => {
    // Clear only the specified fields
    document.getElementById('ah_motivo').value = '';
    document.getElementById('ah_alergias').value = '';
    document.getElementById('ah_antecedentes').value = '';
    document.getElementById('ah_diagnostico').value = '';
  });

  // ----------- Alta de historial patient search -----------
  const ahPacienteInput = document.getElementById('ah_paciente');
  const ahPatientSuggestions = document.getElementById('ah-patient-suggestions');
  let ahSearchTimeout = null;

  async function searchPatientsForAlta(query) {
    if (query.length < 2) {
      ahPatientSuggestions.classList.add('hidden');
      return;
    }

    try {
      const response = await fetch(`/medico/api/patients?query=${encodeURIComponent(query)}`, {
        headers: {
          'X-Requested-With': 'XMLHttpRequest',
          'Accept': 'application/json',
          'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content')
        }
      });

      if (!response.ok) throw new Error('Failed to search patients');

      const patients = await response.json();
      displayAltaPatientSuggestions(patients);

    } catch (error) {
      console.error('Error searching patients:', error);
    }
  }

  function displayAltaPatientSuggestions(patients) {
    ahPatientSuggestions.innerHTML = '';

    if (patients.length === 0) {
      ahPatientSuggestions.innerHTML = '<div class="suggestion-item">No se encontraron pacientes</div>';
    } else {
      patients.slice(0, 5).forEach(patient => {
        const div = document.createElement('div');
        div.className = 'suggestion-item';
        div.innerHTML = `
          <strong>${patient.name}</strong><br>
          <small>Edad: ${patient.age || 'N/A'} | Género: ${patient.gender || 'N/A'}</small>
        `;
        div.addEventListener('click', () => selectAltaPatient(patient));
        ahPatientSuggestions.appendChild(div);
      });
    }

    ahPatientSuggestions.classList.remove('hidden');
  }

  function selectAltaPatient(patient) {
    ahPacienteInput.value = patient.name;
    currentPatientId = patient.id;
    ahPatientSuggestions.classList.add('hidden');
  }

  ahPacienteInput.addEventListener('input', () => {
    clearTimeout(ahSearchTimeout);
    ahSearchTimeout = setTimeout(() => {
      searchPatientsForAlta(ahPacienteInput.value.trim());
    }, 300);
  });

  // Hide suggestions when clicking outside
  document.addEventListener('click', (e) => {
    if (!ahPacienteInput.contains(e.target) && !ahPatientSuggestions.contains(e.target)) {
      ahPatientSuggestions.classList.add('hidden');
    }
  });

  // ----------- Alta de historial form submission -----------
  const altaHistForm = document.getElementById('altaHistForm');
  
  altaHistForm.addEventListener('submit', async (e) => {
    e.preventDefault();
    
    const paciente = document.getElementById('ah_paciente').value.trim();
    const fecha = document.getElementById('ah_fecha').value;
    const motivo = document.getElementById('ah_motivo').value.trim();
    const alergias = document.getElementById('ah_alergias').value.trim();
    const antecedentes = document.getElementById('ah_antecedentes').value.trim();
    const diagnostico = document.getElementById('ah_diagnostico').value.trim();
    
    // Vital signs
    const temp = document.getElementById('ah_temp').value;
    const press = document.getElementById('ah_press').value;
    const pulse = document.getElementById('ah_pulse').value;
    const resp = document.getElementById('ah_resp').value;
    const spo2 = document.getElementById('ah_spo2').value;
    const peso = document.getElementById('ah_peso').value;
    const altura = document.getElementById('ah_altura').value;
    
    if (!currentPatientId) {
      showAppAlert('Por favor busque y seleccione un paciente primero usando el buscador de historial', 'error');
      return;
    }
    
    if (!fecha) {
      showAppAlert('Por favor seleccione una fecha', 'error');
      return;
    }
    
    if (!diagnostico) {
      showAppAlert('El diagnóstico es obligatorio', 'error');
      return;
    }
    
    // Parse blood pressure
    let sbp = null, dbp = null;
    if (press) {
      const bpMatch = press.match(/(\d{2,3})\/(\d{2,3})/);
      if (bpMatch) {
        sbp = parseInt(bpMatch[1]);
        dbp = parseInt(bpMatch[2]);
      }
    }
    
    const payload = {
      patient_id: currentPatientId,
      encounter_dt: fecha,
      reason: motivo || 'Consulta médica',
      diagnosis: diagnostico,
      allergies: alergias || null,
      antecedentes: antecedentes || null,
      vitals: {
        temp: temp ? parseFloat(temp) : null,
        sbp: sbp,
        dbp: dbp,
        hr: pulse ? parseInt(pulse) : null,
        rr: resp ? parseInt(resp) : null,
        spo2: spo2 ? parseInt(spo2) : null,
        weight: peso ? parseFloat(peso) : null,
        height: altura ? parseFloat(altura) : null
      }
    };
    
    try {
      const response = await fetch('/medico/api/alta-historial', {
        method: 'POST',
        headers: {
          'Content-Type': 'application/json',
          'X-Requested-With': 'XMLHttpRequest',
          'Accept': 'application/json',
          'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content')
        },
        body: JSON.stringify(payload)
      });
      
      if (!response.ok) {
        const error = await response.json();
        throw new Error(error.error || 'Error al guardar');
      }
      
      const result = await response.json();
      showAppAlert('✅ Historial médico guardado exitosamente', 'success');
      
      // Clear form
      altaHistForm.reset();
      
      // Reload history if we have a patient selected
      if (currentPatientId) {
        await loadPatientHistory(currentPatientId);
      }
      
    } catch (error) {
      console.error('Error saving medical history:', error);
      showAppAlert('❌ Error al guardar el historial: ' + error.message, 'error');
    }
  });
});
</script>

@endsection
