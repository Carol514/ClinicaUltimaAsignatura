@extends('layouts.app')
@section('title','Panel de Enfermería')

@section('content')
<main class="dashboard">

  <h2>Panel de Enfermería</h2>

  {{-- ===========================
        BUSCAR PACIENTE
     ============================ --}}
  <h3 class="panel-subtitle">Buscar Paciente</h3>

  <form id="frmBuscar" class="form-container" onsubmit="return false;">
    <div class="search-row">
      <input id="txtPaciente" placeholder="Ingrese el nombre o ID del paciente">
      <button id="btnBuscar" class="confirm-btn" type="button"><img src="/img/buscar.png" alt="Limpiar" width="22" height="22" ></button>
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
            <tbody>
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

        {{-- Tratamientos de ejemplo (clicables) --}}
        <div class="treat-card treatment-item"
             data-date="21/11/2025"
             data-summary="Amoxicilina 500 mg c/8h por 7 días.">
          <div class="treat-icon">
            <img src="/img/medicina.png" alt="med">
          </div>

          <div class="treat-content">
            <p class="treat-title">Tratamiento del 21/11/2025</p>
            <p class="treat-desc">
              Amoxicilina 500 mg c/8h.
            </p>
          </div>
        </div>

        <div class="treat-card treatment-item"
             data-date="15/11/2025"
             data-summary="Paracetamol 800 mg cada 8 horas por 3 días.">
          <div class="treat-icon">
            <img src="/img/medicina.png" alt="med">
          </div>

          <div class="treat-content">
            <p class="treat-title">Tratamiento del 15/11/2025</p>
            <p class="treat-desc">
              Paracetamol 800 mg c/8h.
            </p>
          </div>
        </div>

        <div class="treat-card treatment-item"
             data-date="25/10/2025"
             data-summary="Ibuprofeno 400 mg cada 8 horas por 5 días.">
          <div class="treat-icon">
            <img src="/img/medicina.png" alt="med">
          </div>

          <div class="treat-content">
            <p class="treat-title">Tratamiento del 25/10/2025</p>
            <p class="treat-desc">
              Ibuprofeno 400 mg c/8h.
            </p>
          </div>
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


{{-- ==========================================================
      JS
   =========================================================== --}}
<script>
(() => {
  const $ = (id) => document.getElementById(id);

  const txtPaciente = $('txtPaciente');
  const btnBuscar   = $('btnBuscar');
  const nurseLayout = $('nurse-layout');
  const hdrPaciente = $('hdrPaciente');

  const pEdad   = $('pEdad');
  const pGenero = $('pGenero');
  const pDx     = $('pDx');
  const pUltima = $('pUltima');

  const lnkSignos = $('lnkSignos');
  const lnkTrat   = $('lnkTrat');

  const treatmentItems = document.querySelectorAll('.treatment-item');

  function pacienteSeleccionado() {
    return !(hdrPaciente.textContent.endsWith('—'));
  }

  // ==========================
  //    BUSCAR PACIENTE
  // ==========================
  async function buscarPaciente() {
    const nombre = txtPaciente.value.trim();
    if (!nombre) { alert('Escribe un nombre o ID de paciente.'); return; }

    try {
      const res = await fetch(`/enfermera/api/paciente?query=${encodeURIComponent(nombre)}`, {
        credentials:'same-origin',
        headers:{'Accept':'application/json'}
      });

      if (!res.ok) throw new Error('no remote');
      const list = await res.json();
      if (!list || !list.length) throw new Error('no results');
      const patient = list[0];

      hdrPaciente.textContent = `Paciente: ${patient.name}`;
      pEdad.textContent   = patient.age || '—';

      const genderMap = { 'M':'Masculino', 'F':'Femenino', 'I':'Indefinido' };
      pGenero.textContent = genderMap[patient.gender] || patient.gender || '—';

      pDx.textContent = patient.diagnosis || '—';
      pUltima.textContent = patient.last_consult || '—';

    } catch(err) {
      console.error(err);
      alert('No se encontró ningún paciente.');
    } finally {
      nurseLayout.style.display = 'grid';
    }
  }

  btnBuscar.addEventListener('click', buscarPaciente);
  txtPaciente.addEventListener('keydown', (e)=>{ if(e.key === 'Enter'){ e.preventDefault(); buscarPaciente(); } });

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

  const allTratFields = tratForm.querySelectorAll('input, textarea, select');

  function setTratReadOnly(isReadOnly) {
    allTratFields.forEach(el => {
      // solo campos, no tocamos los botones
      if (el.type !== 'submit' && el.type !== 'button') {
        el.disabled = isReadOnly;
      }
    });
  }

  // 👀 MODO SOLO LECTURA: Editar + Cancelar
  function setModeView() {
    setTratReadOnly(true);

    // Editar visible
    tratEditBtn.classList.remove('hidden');
    tratEditBtn.style.display = 'inline-flex';

    // Guardar oculto
    tratSaveBtn.classList.add('hidden');
    tratSaveBtn.style.display = 'none';
  }

  // ✍️ MODO EDICIÓN: Guardar + Cancelar
  function setModeEdit() {
    setTratReadOnly(false);

    // Editar oculto
    tratEditBtn.classList.add('hidden');
    tratEditBtn.style.display = 'none';

    // Guardar visible
    tratSaveBtn.classList.remove('hidden');
    tratSaveBtn.style.display = 'inline-flex';
  }

  // Abrir modal en modo CREAR (botón +)
  function openTratModalCreate() {
    if (!pacienteSeleccionado()) {
      alert('Primero selecciona un paciente.');
      return;
    }
    tratForm.reset();
    setModeEdit();   // solo Guardar + Cancelar
    tratModal.classList.remove('hidden');
  }

  // Abrir modal en modo VER (clic tarjeta)
  function openTratModalFromCard(card) {
    if (!pacienteSeleccionado()) {
      alert('Primero selecciona un paciente.');
      return;
    }

    const fecha   = card.dataset.date   || '';
    const summary = card.dataset.summary || card.querySelector('.treat-desc')?.textContent || '';

    tActual.value = `Tratamiento del ${fecha}: ${summary}`;
    tNuevo.value  = summary;

    setModeView();  // solo Editar + Cancelar
    tratModal.classList.remove('hidden');
  }

  function closeTratModal() {
    tratModal.classList.add('hidden');
    tratForm.reset();
  }

  // Eventos tratamientos
  lnkTrat.addEventListener('click', (e) => {
    e.preventDefault();
    openTratModalCreate();
  });

  treatmentItems.forEach(card => {
    card.addEventListener('click', () => openTratModalFromCard(card));
  });

  tratEditBtn.addEventListener('click', () => {
    setModeEdit();
  });

  tratCancel.addEventListener('click', closeTratModal);

  tratModal.addEventListener('click', (e) => {
    if (e.target === tratModal) closeTratModal();
  });

  tratForm.addEventListener('submit', (e) => {
    e.preventDefault();
    alert('✅ Tratamiento guardado (demo, solo maquetado).');
    closeTratModal();
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
      alert('Primero selecciona un paciente.');
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

  vitalsForm.addEventListener('submit', (e) => {
    e.preventDefault();
    alert('✅ Signos vitales registrados (demo, solo maquetado).');
    closeVitalsModal();
  });

})();
</script>
@endsection
