{{-- resources/views/recepcionista/panel.blade.php --}}
@extends('layouts.app')
@section('title', 'Panel de Recepción')

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
    
    .field {
        position: relative;
    }
    
    .hidden {
        display: none;
    }
</style>

<main class="recep-layout">

    {{-- ====== HEADER ====== --}}
    <header class="recep-header">
        <h1 class="recep-page-title" style="color:#2a6b5f;">Panel de Recepcionista</h1>
    </header>

    {{-- ====== GRID PRINCIPAL ====== --}}
    <section class="recep-main-grid">

        {{-- ========== IZQUIERDA: AGENDA MENSUAL ========== --}}
        <div class="recep-card recep-card--agenda">

            <div class="recep-card-header">
                <h2 class="recep-card-title">Agenda mensual</h2>

                <div class="recep-month-controls">
                    <div class="recep-control">
                        <label for="monthSelect">Mes</label>
                        <select id="monthSelect"></select>
                    </div>

                    <div class="recep-control">
                        <label for="yearSelect">Año</label>
                        <select id="yearSelect"></select>
                    </div>

                    <div class="recep-control">
                        <label for="doctorSelect">Médico</label>
                        <select id="doctorSelect">
                            <option value="">Todos los médicos</option>
                            <!-- Doctors will be loaded dynamically -->
                        </select>
                    </div>
                </div>
            </div>

            {{-- CALENDARIO MENSUAL --}}
            <div id="calendarGrid" class="calendar-grid">
                {{-- Se rellena por JS --}}
            </div>

            <p class="recep-hint">
                * Los días con punto verde tienen citas agendadas.  
                * Haz clic en un día para ver o agregar citas.
            </p>
        </div>

        {{-- ========== DERECHA: ACCIONES ========== --}}
        <div class="recep-column-right">

            {{-- Registrar paciente --}}
            <div class="recep-card recep-card--side recep-card--spaced">
                <h2 class="recep-card-title">Paciente</h2>
                <p class="recep-muted">Registra un nuevo paciente antes de agendar su cita.</p>
                <button type="button" id="btnOpenPaciente" class="recep-btn recep-btn--primary">
                    Registrar nuevo paciente
                </button>
            </div>
        </div>

    </section>


    {{-- ========== MODAL: CITAS DEL DÍA ========== --}}
    <div id="modalCitasDia" class="modal hidden">
        <div class="modal-content modal-lg">
            <h3 id="modalDiaTitulo" class="modal-title">Citas del día</h3>

            <div id="listaCitasDia" class="day-appointments">
                {{-- Se llena por JS --}}
            </div>

            <div class="btn-container" style="margin-top:15px;">
                {{-- Botón agregar cita (+) --}}
                <button id="btnAgregarCitaDia" type="button" class="confirm-btn">
                    <img src="/img/agregar.png" alt="Agregar" style="width:20px; height:20px;">
                </button>

                {{-- Cerrar --}}
                <button id="btnCerrarCitasDia" type="button" class="modal-cancel-btn">
                    <img src="/img/cancelar.png" alt="Cerrar" width="24" height="24">
                </button>
            </div>
        </div>
    </div>

    {{-- ========== MODAL: AGENDAR CITA ========== --}}
<div id="modalAgendar" class="modal hidden">
    <div class="modal-content modal-lg">
        <h3 class="modal-title">Agendar cita</h3>

        <form id="agendar-form" class="form-container">
            <div class="trat-section">
                <h4 class="trat-section-title">Información de la cita</h4>
                <p id="modalInfo" style="margin-bottom: 15px; color: #666; font-weight: 500;"></p>

                <div class="trat-grid">
                    <div class="field">
                        <label for="cita_paciente">Paciente *</label>
                        <input type="text" id="cita_paciente" name="cita_paciente"
                               placeholder="Buscar paciente..." required>
                        <input type="hidden" id="cita_patient_id" name="cita_patient_id">
                        <div id="patient-suggestions" class="suggestions-dropdown hidden"></div>
                    </div>

                    <div class="field">
                        <label for="cita_doctor">Médico *</label>
                        <select id="cita_doctor" name="cita_doctor" required>
                            <option value="">Seleccione un médico</option>
                            <!-- Doctors se llenan por JS -->
                        </select>
                    </div>

                    <div class="field">
                        <label for="cita_hora">Hora *</label>
                        <input type="time" id="cita_hora" name="cita_hora" required>
                    </div>

                    <div class="field">
                        <label for="cita_duracion">Duración (minutos)</label>
                        <select id="cita_duracion" name="cita_duracion">
                            <option value="30">30 minutos</option>
                            <option value="45">45 minutos</option>
                            <option value="60">60 minutos</option>
                            <option value="90">90 minutos</option>
                        </select>
                    </div>

                    <!-- Motivo de consulta -->
                    <div class="field" style="grid-column: span 2;">
                        <label for="cita_motivo">Motivo de consulta *</label>
                        <textarea id="cita_motivo" name="cita_motivo" rows="3"
                                  placeholder="Describe el motivo de la consulta..." required></textarea>
                    </div>
                </div> <!-- /trat-grid -->
            </div> <!-- /trat-section -->

            <div class="btn-container" style="margin-top: 20px;">
                <button type="submit" class="confirm-btn">
                    <img src="/img/guardar.png" class="btn-icon" alt="Guardar"
                         style="width:24px; height:24px;">
                </button>
                <button type="button" id="cerrarModalAgendar" class="modal-cancel-btn">
                    <img src="/img/cancelar.png" class="btn-icon" alt="Cancelar">
                </button>
            </div>
        </form>
    </div>
</div>


    {{-- ========== MODAL: REGISTRAR PACIENTE ========== --}}
    <div id="modalPaciente" class="modal hidden">
        <div class="modal-content modal-lg">
            <h3 class="modal-title">Registrar nuevo paciente</h3>

            <form id="paciente-form" class="form-container">

                <div class="trat-section">
                    <h4 class="trat-section-title">Datos personales</h4>

                    <div class="trat-grid">
                        <div class="field">
                            <label for="p_nombre">Nombre(s)</label>
                            <input id="p_nombre" type="text" placeholder="Ej. Hugo Abraham" required>
                        </div>

                        <div class="field">
                            <label for="p_apellidos">Apellidos</label>
                            <input id="p_apellidos" type="text" placeholder="Ej. García Tovar" required>
                        </div>

                        <div class="field">
                            <label for="p_fecha_nac">Fecha de nacimiento</label>
                            <input id="p_fecha_nac" type="date" required>
                        </div>

                        <div class="field">
                            <label for="p_edad">Edad</label>
                            <input id="p_edad" type="number" min="0" max="120" placeholder="18" required>
                        </div>

                        <div class="field">
                            <label for="p_genero">Género</label>
                            <select id="p_genero" required>
                                <option value="">Seleccione...</option>
                                <option>Masculino</option>
                                <option>Femenino</option>
                                <option>Otro</option>
                            </select>
                        </div>
                    </div>
                </div>

                <hr class="section-divider">

                <div class="trat-section">
                    <h4 class="trat-section-title">Contacto</h4>

                    <div class="trat-grid">
                        <div class="field">
                            <label for="p_telefono">Teléfono</label>
                            <input id="p_telefono" type="tel" placeholder="Ej. 3111234567" maxlength="10" required>
                        </div>

                        <div class="field">
                            <label for="p_email">Correo electrónico</label>
                            <input id="p_email" type="email" placeholder="Ej. paciente@correo.com" required>
                        </div>

                        <div class="field">
                            <label for="p_direccion">Dirección</label>
                            <input id="p_direccion" type="text" placeholder="Calle, número, colonia" required>
                        </div>

                    </div>
                </div>

                <div class="btn-container" style="margin-top:12px;">
                    <button class="confirm-btn" type="submit">
                        <img src="/img/guardar.png" class="btn-icon" alt="Guardar" style="width:24px; height:24px;">
                    </button>
                    <button type="button" class="modal-cancel-btn" id="cerrarModalPaciente">
                        <img src="/img/cancelar.png" class="btn-icon" alt="Cancelar">
                    </button>
                </div>
            </form>
        </div>
    </div>

    {{-- ========== MODAL: EDITAR CITA ========== --}}
<div id="modalEditarCita" class="modal hidden">
  <div class="modal-content modal-lg">
    <h3 class="modal-title">Detalle de la cita</h3>

    <p id="editInfo" style="margin-bottom: 15px; color:#666; font-weight:500;">
      <!-- Se llena por JS -->
    </p>

    <form id="editar-cita-form" class="form-container">
      <div class="trat-section">
        <h4 class="trat-section-title">Información de la cita</h4>

        <div class="trat-grid">
          <div class="field">
            <label>Paciente</label>
            <input id="edit_paciente" type="text" readonly>
          </div>

          <div class="field">
            <label for="edit_doctor">Médico</label>
            <select id="edit_doctor" disabled>
              <option value="">Seleccione un médico</option>
            </select>
          </div>

          <div class="field">
            <label for="edit_fecha">Fecha</label>
            <input id="edit_fecha" type="date" disabled>
          </div>

          <div class="field">
            <label for="edit_hora">Hora</label>
            <input id="edit_hora" type="time" disabled>
          </div>

          <div class="field">
            <label for="edit_duracion">Duración (minutos)</label>
            <select id="edit_duracion" disabled>
              <option value="30">30 minutos</option>
              <option value="45">45 minutos</option>
              <option value="60">60 minutos</option>
              <option value="90">90 minutos</option>
            </select>
          </div>

          <div class="field" style="grid-column: span 2;">
            <label for="edit_motivo">Motivo de consulta</label>
            <textarea id="edit_motivo" rows="3" disabled></textarea>
          </div>

          {{-- NUEVO: estado de la cita --}}
          <div class="field" style="grid-column: span 2;">
            <label for="edit_status">Estado de la cita</label>
            <select id="edit_status" disabled>
              <option value="programada">Programada</option>
              <option value="confirmada">Confirmada</option>
              <option value="no_asistio">No asistió</option>
              <option value="cancelada">Cancelada</option>
              <option value="atendida">Atendida</option>
            </select>
          </div>
        </div>
      </div>

      {{-- Botones modo SOLO LECTURA --}}
      <div class="btn-container" id="editCitaViewButtons" style="margin-top:20px;">
        <button type="button" id="btnEditarCita" class="confirm-btn">
          <img src="/img/editar.png" class="btn-icon" alt="Editar" style="width:24px;height:24px;">
        </button>
        <button type="button" id="btnCerrarEditarCita" class="modal-cancel-btn">
          <img src="/img/cancelar.png" class="btn-icon" alt="Cerrar">
        </button>
      </div>

      {{-- Botones modo EDICIÓN --}}
      <div class="btn-container hidden" id="editCitaEditButtons" style="margin-top:20px;">
        <button type="submit" id="btnGuardarCita" class="confirm-btn">
          <img src="/img/guardar.png" class="btn-icon" alt="Guardar" style="width:24px;height:24px;">
        </button>
        <button type="button" id="btnCancelarEdicionCita" class="modal-cancel-btn">
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

// ====== CONFIRM GLOBAL ======
function showAppConfirm(message, callback) {
    const overlay   = document.getElementById('appConfirmOverlay');
    const textEl    = document.getElementById('appConfirmText');
    const okBtn     = document.getElementById('appConfirmOK');
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

document.addEventListener("DOMContentLoaded", () => {

    const calendarGrid      = document.getElementById("calendarGrid");
    const monthSelect       = document.getElementById("monthSelect");
    const yearSelect        = document.getElementById("yearSelect");
    const doctorSelect      = document.getElementById("doctorSelect");

    const modalCitasDia     = document.getElementById("modalCitasDia");
    const modalDiaTitulo    = document.getElementById("modalDiaTitulo");
    const listaCitasDia     = document.getElementById("listaCitasDia");
    const btnCerrarCitasDia = document.getElementById("btnCerrarCitasDia");
    const btnAgregarCitaDia = document.getElementById("btnAgregarCitaDia");

    const modalAgendar      = document.getElementById("modalAgendar");
    const modalInfo         = document.getElementById("modalInfo");
    const cerrarModalAgendar= document.getElementById("cerrarModalAgendar");
    const agendarForm       = document.getElementById("agendar-form");
    
    // Appointment form elements (AGENDAR)
    const citaPaciente      = document.getElementById("cita_paciente");
    const citaPatientId     = document.getElementById("cita_patient_id");
    const citaDoctor        = document.getElementById("cita_doctor");
    const citaHora          = document.getElementById("cita_hora");
    const citaDuracion      = document.getElementById("cita_duracion");
    const citaMotivo        = document.getElementById("cita_motivo");
    const patientSuggestions = document.getElementById("patient-suggestions");

    // --- modal paciente ---
    const btnOpenPaciente     = document.getElementById("btnOpenPaciente");
    const modalPaciente       = document.getElementById("modalPaciente");
    const pacienteForm        = document.getElementById("paciente-form");
    const cerrarModalPaciente = document.getElementById("cerrarModalPaciente");

    // --- modal editar cita ---
    const modalEditarCita        = document.getElementById("modalEditarCita");
    const editInfo               = document.getElementById("editInfo");
    const editPaciente           = document.getElementById("edit_paciente");
    const editDoctor             = document.getElementById("edit_doctor");
    const editFecha              = document.getElementById("edit_fecha");
    const editHora               = document.getElementById("edit_hora");
    const editDuracion           = document.getElementById("edit_duracion");
    const editMotivo             = document.getElementById("edit_motivo");
    const editStatus             = document.getElementById("edit_status");
    const editCitaForm           = document.getElementById("editar-cita-form");
    const btnEditarCita          = document.getElementById("btnEditarCita");
    const btnCerrarEditarCita    = document.getElementById("btnCerrarEditarCita");
    const btnCancelarEdicionCita = document.getElementById("btnCancelarEdicionCita");
    const editCitaViewButtons    = document.getElementById("editCitaViewButtons");
    const editCitaEditButtons    = document.getElementById("editCitaEditButtons");

    let fechaSeleccionada = null;
    let citaEditActual    = null;

    const meses = [
        "Enero", "Febrero", "Marzo", "Abril", "Mayo", "Junio",
        "Julio", "Agosto", "Septiembre", "Octubre", "Noviembre", "Diciembre"
    ];

    // Llenar select de meses
    meses.forEach((m, i) => {
        const op = document.createElement("option");
        op.value = i;
        op.textContent = m;
        monthSelect.appendChild(op);
    });

    const hoy = new Date();
    const añoActual = hoy.getFullYear();

    // Llenar select de años (+/- 3 años)
    for (let y = añoActual - 3; y <= añoActual + 3; y++) {
        const op = document.createElement("option");
        op.value = y;
        op.textContent = y;
        yearSelect.appendChild(op);
    }

    monthSelect.value = hoy.getMonth();
    yearSelect.value  = añoActual;

    // --------------------------
    // DATA - loaded from API
    // --------------------------
    let CITAS   = [];
    let DOCTORS = [];
    
    async function loadAppointments() {
        try {
            const mes = parseInt(monthSelect.value, 10);
            const año = parseInt(yearSelect.value, 10);
            
            const currentDate   = new Date(año, mes);
            const startOfMonth  = new Date(currentDate.getFullYear(), currentDate.getMonth(), 1);
            const endOfMonth    = new Date(currentDate.getFullYear(), currentDate.getMonth() + 1, 0);
            const fromDate      = startOfMonth.toISOString().split('T')[0];
            const toDate        = endOfMonth.toISOString().split('T')[0];
            
            let url = `/recepcionista/api/appointments?from=${fromDate}&to=${toDate}`;
            if (doctorSelect.value) {
                url += `&clinician_id=${doctorSelect.value}`;
            }
            
            const response = await fetch(url, {
                headers: {
                    'X-Requested-With': 'XMLHttpRequest',
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content')
                }
            });
            
            if (!response.ok) {
                const errorText = await response.text();
                console.error('API Error Response:', errorText);
                throw new Error(`Failed to load appointments: ${response.status} ${response.statusText}`);
            }
            
            const data = await response.json();
            
            CITAS = (data.data || []).map(appointment => {
                const scheduledAtStr = appointment.scheduled_at || '';
                const dateTimeParts  = scheduledAtStr.split(' ');
                const datePart       = dateTimeParts[0] || '';
                const timePart       = dateTimeParts[1] ? dateTimeParts[1].slice(0, 5) : '00:00';
                
                return {
                    id:            appointment.id,
                    fecha:         datePart,
                    hora:          timePart,
                    paciente:      appointment.patient_name || 'Paciente sin nombre',
                    patient_id:    appointment.patient_id || null,
                    motivo:        appointment.reason || 'Sin motivo especificado',
                    doctor:        appointment.clinician_name || 'Doctor no asignado',
                    clinician_id:  appointment.clinician_id,
                    duration_min:  appointment.duration_min || 30,
                    status:        appointment.status || 'programada'
                };
            });
            
        } catch (error) {
            console.error('Error loading appointments:', error);
            CITAS = [];
        }
    }
    
    async function loadDoctors() {
        try {
            const response = await fetch('/recepcionista/api/medicos', {
                headers: {
                    'X-Requested-With': 'XMLHttpRequest',
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content')
                }
            });
            
            if (!response.ok) {
                throw new Error(`Failed to load doctors: ${response.status}`);
            }
            
            const data = await response.json();
            DOCTORS    = data.data || [];
            populateDoctorSelector();
            
        } catch (error) {
            console.error('Error loading doctors:', error);
            DOCTORS = [];
        }
    }
    
    function populateDoctorSelector() {
        doctorSelect.innerHTML = '<option value="">Todos los médicos</option>';
        DOCTORS.forEach(doctor => {
            const option   = document.createElement('option');
            option.value   = doctor.id;
            option.textContent = doctor.name;
            doctorSelect.appendChild(option);
        });
        
        citaDoctor.innerHTML = '<option value="">Seleccione un médico</option>';
        DOCTORS.forEach(doctor => {
            const option   = document.createElement('option');
            option.value   = doctor.id;
            option.textContent = doctor.name;
            citaDoctor.appendChild(option);
        });

        // select del modal de edición
        if (editDoctor) {
            editDoctor.innerHTML = '<option value="">Seleccione un médico</option>';
            DOCTORS.forEach(doctor => {
                const option   = document.createElement('option');
                option.value   = doctor.id;
                option.textContent = doctor.name;
                editDoctor.appendChild(option);
            });
        }
    }
    
    async function searchPatients(query) {
        if (query.length < 2) {
            patientSuggestions.classList.add('hidden');
            return;
        }
        
        try {
            const response = await fetch(`/recepcionista/api/patients?q=${encodeURIComponent(query)}`, {
                headers: {
                    'X-Requested-With': 'XMLHttpRequest',
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content')
                }
            });
            
            if (!response.ok) throw new Error('Failed to search patients');
            
            const data = await response.json();
            displayPatientSuggestions(data.data || []);
            
        } catch (error) {
            console.error('Error searching patients:', error);
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
                    <strong>${patient.nombre}</strong><br>
                    <small>📞 ${patient.phone || 'Sin teléfono'} | 📧 ${patient.email || 'Sin email'}</small>
                `;
                div.addEventListener('click', () => selectPatient(patient));
                patientSuggestions.appendChild(div);
            });
        }
        
        patientSuggestions.classList.remove('hidden');
    }
    
    function selectPatient(patient) {
        citaPaciente.value  = patient.nombre;
        citaPatientId.value = patient.id;
        patientSuggestions.classList.add('hidden');
    }
    
    function checkTimeConflict(doctorId, date, startTime, durationMinutes, excludeAppointmentId = null) {
        const [startHour, startMinute]   = startTime.split(':').map(Number);
        const appointmentStartMinutes    = startHour * 60 + startMinute;
        const appointmentEndMinutes      = appointmentStartMinutes + durationMinutes;
        
        const conflictingAppointments = CITAS.filter(appointment => {
            if (excludeAppointmentId && appointment.id === excludeAppointmentId) {
                return false;
            }
            if (appointment.clinician_id != doctorId || appointment.fecha !== date) {
                return false;
            }
            
            // Ignore cancelled and no-show appointments - they free up the time slot
            if (appointment.status === 'cancelada' || appointment.status === 'no_asistio') {
                return false;
            }
            
            const [existingHour, existingMinute] = appointment.hora.split(':').map(Number);
            const existingStartMinutes           = existingHour * 60 + existingMinute;
            const existingDurationMinutes        = appointment.duration_min || 30;
            const existingEndMinutes             = existingStartMinutes + existingDurationMinutes;
            
            const hasOverlap = (appointmentStartMinutes < existingEndMinutes && 
                               appointmentEndMinutes > existingStartMinutes);
            return hasOverlap;
        });
        
        return conflictingAppointments;
    }
    
    function formatConflictMessage(conflicts, doctorName, newTime, duration) {
        if (conflicts.length === 0) return '';
        
        const conflictList = conflicts.map(c => {
            const endTime = calculateEndTime(c.hora, c.duration_min || 30);
            return `${c.hora}-${endTime} (${c.paciente}, ${c.duration_min || 30}min)`;
        }).join('\n• ');
        
        return `⚠️ Conflicto de horario detectado.\n\nEl/La ${doctorName} ya tiene cita(s) programada(s):\n• ${conflictList}\n\nLa nueva cita de ${duration} minutos a las ${newTime} se solaparía con la(s) cita(s) existente(s).\n\nPor favor, selecciona otro horario.`;
    }
    
    function calculateEndTime(startTime, durationMinutes) {
        const [hour, minute] = startTime.split(':').map(Number);
        const totalMinutes   = hour * 60 + minute + durationMinutes;
        const endHour        = Math.floor(totalMinutes / 60);
        const endMinute      = totalMinutes % 60;
        return `${endHour.toString().padStart(2, '0')}:${endMinute.toString().padStart(2, '0')}`;
    }

    // --------------------------
    // Renderizar calendario
    // --------------------------
    async function renderCalendar() {
        await loadAppointments();
        
        calendarGrid.innerHTML = "";

        const mes = parseInt(monthSelect.value, 10);
        const año = parseInt(yearSelect.value, 10);

        const primerDia = new Date(año, mes, 1).getDay(); // 0 = Domingo
        const diasEnMes = new Date(año, mes + 1, 0).getDate();

        const offset = (primerDia === 0 ? 6 : primerDia - 1);

        ["Lun","Mar","Mié","Jue","Vie","Sáb","Dom"].forEach(d => {
            const h = document.createElement("div");
            h.className = "calendar-header";
            h.textContent = d;
            calendarGrid.appendChild(h);
        });

        for (let i = 0; i < offset; i++) {
            const empty = document.createElement("div");
            empty.className = "calendar-cell calendar-cell--empty";
            calendarGrid.appendChild(empty);
        }

        for (let dia = 1; dia <= diasEnMes; dia++) {

            const fechaStr = `${año}-${String(mes + 1).padStart(2,'0')}-${String(dia).padStart(2,'0')}`;

            const hasEvents = CITAS.some(c =>
                c.fecha === fechaStr &&
                (doctorSelect.value === "" || c.clinician_id == doctorSelect.value)
            );

            const cell = document.createElement("div");
            cell.className = "calendar-cell calendar-cell--day";
            if (hasEvents) {
                cell.classList.add("calendar-cell--has-events");
            }
            cell.dataset.fecha = fechaStr;
            cell.dataset.dia   = dia;

            cell.innerHTML = `
                <div class="day-number">${dia}</div>
                ${hasEvents ? `<div class="day-dot"></div>` : ``}
            `;

            cell.addEventListener("click", () => openDayModal(fechaStr));
            calendarGrid.appendChild(cell);
        }
    }

    // --------------------------
    // Helper status / date
    // --------------------------
    function getAppointmentStatusInfo(status) {
        const statusMap = {
            'programada': { color: '#17a2b8', text: 'Programada' },
            'confirmada': { color: '#28a745', text: 'Confirmada' },
            'no_asistio': { color: '#fd7e14', text: 'No asistió' },
            'cancelada':  { color: '#dc3545', text: 'Cancelada' },
            'atendida':   { color: '#6c757d', text: 'Atendida' }
        };
        return statusMap[status] || { color: '#6c757d', text: status || 'Sin estado' };
    }

    function formatDate(dateStr) {
        const [year, month, day] = dateStr.split('-').map(Number);
        const date = new Date(year, month - 1, day);
        const options = { 
            weekday: 'long', 
            year: 'numeric', 
            month: 'long', 
            day: 'numeric' 
        };
        return date.toLocaleDateString('es-ES', options);
    }

    // --------------------------
    // Modal: citas del día
    // --------------------------
    function openDayModal(fechaStr) {
        fechaSeleccionada = fechaStr;

        const citas = CITAS.filter(c =>
            c.fecha === fechaStr &&
            (doctorSelect.value === "" || c.clinician_id == doctorSelect.value)
        );

        modalDiaTitulo.textContent = `Citas del ${formatDate(fechaStr)}`;
        listaCitasDia.innerHTML    = "";

        if (!citas.length) {
            listaCitasDia.innerHTML = `
                <div style="text-align: center; padding: 40px 20px; color: #6c757d;">
                    <div style="font-size: 3em; margin-bottom: 10px;">📅</div>
                    <p style="margin: 0; font-size: 1.1em;">No hay citas programadas para este día</p>
                    <p style="margin: 5px 0 0 0; font-size: 0.9em; color: #adb5bd;">Puede agregar una nueva cita usando el botón (+)</p>
                </div>
            `;
        } else {
            citas
              .sort((a,b)=> (a.hora||'').localeCompare(b.hora||''))

              .forEach(c => {
                const statusInfo = getAppointmentStatusInfo(c.status);
                
                const div = document.createElement("div");
                div.className = "cita-item";
                div.innerHTML = `
                    <div class="cita-main">
                        <span class="cita-hora"><strong>${c.hora}</strong></span>
                        <span class="cita-paciente">${c.paciente}</span>
                        <span class="cita-status" style="background-color: ${statusInfo.color}; color: white; padding: 2px 8px; border-radius: 12px; font-size: 0.8em; margin-left: auto;">
                            ${statusInfo.text}
                        </span>
                    </div>
                    <div class="cita-motivo" style="color: #666; margin-top: 4px;">${c.motivo}</div>
                    <div class="cita-doctor" style="color: #888; font-size: 0.9em; margin-top: 2px;">👨‍⚕️ ${c.doctor} • ⏱️ ${c.duration_min || 30} min</div>
                `;
                // click para abrir modal de edición
                div.addEventListener('click', () => openEditAppointmentModal(c));
                listaCitasDia.appendChild(div);
            });
        }

        modalCitasDia.classList.remove("hidden");
    }

    function closeDayModal() {
        modalCitasDia.classList.add("hidden");
    }

    btnCerrarCitasDia.addEventListener("click", closeDayModal);

    modalCitasDia.addEventListener("click", (e) => {
        if (e.target === modalCitasDia) closeDayModal();
    });

    // --------------------------
    // Modal EDITAR CITA
    // --------------------------
    function setEditInputsDisabled(disabled) {
        if (!editDoctor) return; // por si algo no existe
        editDoctor.disabled   = disabled;
        editFecha.disabled    = disabled;
        editHora.disabled     = disabled;
        editDuracion.disabled = disabled;
        editMotivo.disabled   = disabled;
        editStatus.disabled   = disabled;
    }

    function closeEditarCitaModal() {
        if (!modalEditarCita) return;
        modalEditarCita.classList.add('hidden');
        setEditInputsDisabled(true);
        editCitaViewButtons.classList.remove('hidden');
        editCitaEditButtons.classList.add('hidden');
        citaEditActual = null;
    }

    function openEditAppointmentModal(cita) {
        if (!modalEditarCita) return;
        citaEditActual = {...cita};

        editInfo.textContent  = `📅 ${formatDate(cita.fecha)} • ⏰ ${cita.hora}`;
        editPaciente.value    = cita.paciente || '';
        editFecha.value       = cita.fecha || '';
        editHora.value        = cita.hora || '';
        editMotivo.value      = cita.motivo || '';
        editDuracion.value    = String(cita.duration_min || 30);
        editStatus.value      = cita.status || 'programada';

        // llenar doctores
        editDoctor.innerHTML = '<option value="">Seleccione un médico</option>';
        DOCTORS.forEach(doc => {
            const opt   = document.createElement('option');
            opt.value   = doc.id;
            opt.textContent = doc.name;
            if (doc.id == cita.clinician_id) opt.selected = true;
            editDoctor.appendChild(opt);
        });

        setEditInputsDisabled(true);
        editCitaViewButtons.classList.remove('hidden');
        editCitaEditButtons.classList.add('hidden');

        modalEditarCita.classList.remove('hidden');
    }

    if (btnEditarCita) {
        btnEditarCita.addEventListener('click', () => {
            if (!citaEditActual) return;
            setEditInputsDisabled(false);
            editCitaViewButtons.classList.add('hidden');
            editCitaEditButtons.classList.remove('hidden');
        });
    }

    if (btnCerrarEditarCita) {
        btnCerrarEditarCita.addEventListener('click', closeEditarCitaModal);
    }

    if (btnCancelarEdicionCita) {
        btnCancelarEdicionCita.addEventListener('click', () => {
            if (!citaEditActual) return;
            editStatus.value   = citaEditActual.status || 'programada';
            editFecha.value    = citaEditActual.fecha || '';
            editHora.value     = citaEditActual.hora || '';
            editMotivo.value   = citaEditActual.motivo || '';
            editDuracion.value = String(citaEditActual.duration_min || 30);
            editDoctor.value   = citaEditActual.clinician_id || '';
            setEditInputsDisabled(true);
            editCitaViewButtons.classList.remove('hidden');
            editCitaEditButtons.classList.add('hidden');
        });
    }

    if (modalEditarCita) {
        modalEditarCita.addEventListener('click', e => {
            if (e.target === modalEditarCita) {
                closeEditarCitaModal();
            }
        });
    }

    if (editCitaForm) {
        editCitaForm.addEventListener('submit', async (e) => {
            e.preventDefault();
            if (!citaEditActual) return;

            const doctorId = editDoctor.value;
            const fecha    = editFecha.value;
            const hora     = editHora.value;
            const motivo   = editMotivo.value.trim();
            const dur      = parseInt(editDuracion.value) || 30;

            if (!doctorId) {
                showAppAlert('Por favor selecciona un médico.', 'error');
                return;
            }
            if (!fecha) {
                showAppAlert('Por favor selecciona una fecha.', 'error');
                return;
            }
            if (!hora) {
                showAppAlert('Por favor selecciona una hora.', 'error');
                return;
            }
            if (!motivo) {
                showAppAlert('Por favor describe el motivo de la consulta.', 'error');
                return;
            }

            const conflicts = checkTimeConflict(doctorId, fecha, hora, dur, citaEditActual.id);
            if (conflicts.length > 0) {
                const selectedDoctor = DOCTORS.find(d => d.id == doctorId);
                const doctorName = selectedDoctor ? selectedDoctor.name : 'Doctor seleccionado';
                const msg = formatConflictMessage(conflicts, doctorName, hora, dur);
                showAppAlert(msg, 'error');
                return;
            }

            try {
                const token = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '';
                const scheduledDateTime = `${fecha} ${hora}:00`;

                const payload = {
                    patient_id:   citaEditActual.patient_id,
                    clinician_id: doctorId,
                    scheduled_at: scheduledDateTime,
                    duration_min: dur,
                    reason:       motivo,
                    status:       editStatus.value
                };

                const response = await fetch(`/recepcionista/api/appointments/${citaEditActual.id}`, {
                    method: 'PUT',
                    headers: {
                        'Content-Type': 'application/json',
                        'Accept': 'application/json',
                        'X-Requested-With': 'XMLHttpRequest',
                        'X-CSRF-TOKEN': token
                    },
                    body: JSON.stringify(payload)
                });

                if (!response.ok) {
                    const errorText = await response.text();
                    console.error('Error updating appointment:', errorText);
                    showAppAlert('Error al actualizar la cita.', 'error');
                    return;
                }

                const data = await response.json();
                showAppAlert(data.message || 'Cita actualizada exitosamente.', 'success');

                closeEditarCitaModal();
                await renderCalendar();

                if (!modalCitasDia.classList.contains('hidden') && fechaSeleccionada) {
                    openDayModal(fechaSeleccionada);
                }

            } catch (err) {
                console.error('Error updating appointment:', err);
                showAppAlert('Error al actualizar la cita.', 'error');
            }
        });
    }

    // --------------------------
    // Patient search functionality
    // --------------------------
    citaPaciente.addEventListener('input', (e) => {
        const query = e.target.value.trim();
        searchPatients(query);
    });
    
    document.addEventListener('click', (e) => {
        if (!citaPaciente.contains(e.target) && !patientSuggestions.contains(e.target)) {
            patientSuggestions.classList.add('hidden');
        }
    });

    // --------------------------
    // Abrir modal de agendar desde el día
    // --------------------------
    btnAgregarCitaDia.addEventListener("click", () => {
        if (!fechaSeleccionada) return;
        
        agendarForm.reset();
        citaPatientId.value = '';
        
        // Auto-fill doctor if filter is active
        if (doctorSelect.value) {
            citaDoctor.value = doctorSelect.value;
        }
        
        modalInfo.textContent = `📅 Fecha seleccionada: ${formatDate(fechaSeleccionada)}`;
        modalAgendar.classList.remove("hidden");
    });

    function closeAgendarModal() {
        modalAgendar.classList.add("hidden");
    }

    cerrarModalAgendar.addEventListener("click", closeAgendarModal);
    modalAgendar.addEventListener("click", (e)=>{
        if (e.target === modalAgendar) closeAgendarModal();
    });
    
    // --------------------------
    // Form submit: AGENDAR
    // --------------------------
    agendarForm.addEventListener('submit', async (e) => {
        e.preventDefault();
        
        const patientId = citaPatientId.value;
        const doctorId  = citaDoctor.value;
        const hora      = citaHora.value;
        const motivo    = citaMotivo.value.trim();
        
        if (!patientId) {
            showAppAlert('Por favor selecciona un paciente válido de la lista de sugerencias.', 'error');
            return;
        }
        
        if (!doctorId) {
            showAppAlert('Por favor selecciona un médico.', 'error');
            return;
        }
        
        if (!hora) {
            showAppAlert('Por favor selecciona una hora para la cita.', 'error');
            return;
        }
        
        if (!motivo) {
            showAppAlert('Por favor describe el motivo de la consulta.', 'error');
            return;
        }
        
        const duration = parseInt(citaDuracion.value) || 30;
        const conflicts = checkTimeConflict(doctorId, fechaSeleccionada, hora, duration);
        
        if (conflicts.length > 0) {
            const selectedDoctor = DOCTORS.find(d => d.id == doctorId);
            const doctorName = selectedDoctor ? selectedDoctor.name : 'Doctor seleccionado';
            const conflictMessage = formatConflictMessage(conflicts, doctorName, hora, duration);
            showAppAlert(conflictMessage, 'error');
            return;
        }
        
        try {
            const scheduledDateTime = `${fechaSeleccionada} ${hora}:00`;
            
            const appointmentData = {
                patient_id:   patientId,
                clinician_id: doctorId,
                scheduled_at: scheduledDateTime,
                duration_min: duration,
                reason:       motivo,
                status:       'programada' // siempre empieza programada
            };
            
            const response = await fetch('/recepcionista/api/appointments', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-Requested-With': 'XMLHttpRequest',
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content')
                },
                body: JSON.stringify(appointmentData)
            });
            
            if (!response.ok) {
                const errorData = await response.text();
                throw new Error(`Error ${response.status}: ${errorData}`);
            }
            
            const result = await response.json();
            
            showAppAlert(`Cita agendada exitosamente para ${citaPaciente.value} el ${formatDate(fechaSeleccionada)} a las ${hora}.`, 'success');
            
            closeAgendarModal();
            await renderCalendar();
            
            if (!modalCitasDia.classList.contains('hidden') && fechaSeleccionada) {
                openDayModal(fechaSeleccionada);
            }
            
        } catch (error) {
            console.error('Error creating appointment:', error);
            showAppAlert('Error al agendar la cita. Por favor verifica los datos e inténtalo de nuevo.', 'error');
        }
    });

    // --------------------------
    // Modal: registrar paciente
    // --------------------------
    function openPacienteModal() {
        modalPaciente.classList.remove("hidden");
    }

    function closePacienteModal() {
        modalPaciente.classList.add("hidden");
        pacienteForm.reset();
    }

    btnOpenPaciente.addEventListener("click", openPacienteModal);
    cerrarModalPaciente.addEventListener("click", closePacienteModal);

    modalPaciente.addEventListener("click", (e) => {
        if (e.target === modalPaciente) closePacienteModal();
    });

    pacienteForm.addEventListener("submit", async (e) => {
        e.preventDefault();
        
        const nombre    = document.getElementById('p_nombre').value.trim();
        const apellidos = document.getElementById('p_apellidos').value.trim();
        const fechaNac  = document.getElementById('p_fecha_nac').value;
        const edadInput = document.getElementById('p_edad').value;
        const genero    = document.getElementById('p_genero').value;
        const telefono  = document.getElementById('p_telefono').value.trim();
        const email     = document.getElementById('p_email').value.trim();
        const direccion = document.getElementById('p_direccion').value.trim();
        
        if (!nombre || !apellidos || !telefono || !email || !fechaNac || !edadInput || !genero || !direccion) {
            showAppAlert('Por favor completa todos los campos del formulario.', 'error');
            return;
        }
        
        const nameRegex = /^[a-zA-ZáéíóúÁÉÍÓÚñÑüÜ\s\-']+$/;
        if (!nameRegex.test(nombre)) {
            showAppAlert('El nombre no debe contener números o caracteres especiales.', 'error');
            return;
        }
        
        if (!nameRegex.test(apellidos)) {
            showAppAlert('Los apellidos no deben contener números o caracteres especiales.', 'error');
            return;
        }
        
        const phoneRegex = /^\d{10}$/;
        if (!phoneRegex.test(telefono)) {
            showAppAlert('El teléfono debe contener exactamente 10 dígitos sin letras ni espacios.', 'error');
            return;
        }
        
        if (!validateEmail(email)) {
            showAppAlert('Por favor, ingrese un email válido.', 'error');
            return;
        }
        
        const ageNum = parseInt(edadInput);
        if (isNaN(ageNum) || ageNum < 0 || ageNum > 120) {
            showAppAlert('La edad debe ser un número válido entre 0 y 120 años.', 'error');
            return;
        }
        
        if (!genero || genero === '') {
            showAppAlert('Por favor selecciona el género del paciente.', 'error');
            return;
        }
        
        let sex = '';
        if (genero === 'Masculino')      sex = 'M';
        else if (genero === 'Femenino') sex = 'F';
        else if (genero === 'Otro')     sex = 'I';
        
        let age = null;
        if (edadInput && !isNaN(edadInput) && parseInt(edadInput) > 0) {
            age = parseInt(edadInput);
        } else if (fechaNac) {
            const birthDate = new Date(fechaNac);
            const today     = new Date();
            age = today.getFullYear() - birthDate.getFullYear();
            if (today.getMonth() < birthDate.getMonth() || 
                (today.getMonth() === birthDate.getMonth() && today.getDate() < birthDate.getDate())) {
                age--;
            }
        }
        
        const payload = {
            first_name: nombre,
            last_name:  apellidos,
            sex:        sex,
            phone:      telefono,
            email:      email || null,
            address:    direccion,
            age:        age
        };
        
        try {
            const token = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '';
            
            const response = await fetch('/recepcionista/api/patients', {
                method: 'POST',
                credentials: 'same-origin',
                headers: {
                    'Accept': 'application/json',
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': token,
                    'X-Requested-With': 'XMLHttpRequest'
                },
                body: JSON.stringify(payload)
            });
            
            if (!response.ok) {
                const errorData = await response.json().catch(() => null);
                if (response.status === 422 && errorData?.errors) {
                    const messages = [];
                    for (const field in errorData.errors) {
                        messages.push(errorData.errors[field].join(', '));
                    }
                    showAppAlert('Errores de validación:\n' + messages.join('\n'), 'error');
                } else {
                    showAppAlert('Error al registrar paciente: ' + (errorData?.message || `HTTP ${response.status}`), 'error');
                }
                return;
            }
            
            const result = await response.json();
            
            let message = 'Paciente registrado exitosamente.';
            if (result.data?.id) {
                message += ` (ID: ${result.data.id})`;
            }
            if (result.created_user) {
                message += `\n\nUsuario creado:\nEmail: ${result.created_user.email}\nContraseña temporal: ${result.created_user.temp_password}`;
            }
            
            showAppAlert(message, 'success');
            closePacienteModal();
            
        } catch (error) {
            console.error('Error registering patient:', error);
            showAppAlert('Error de conexión al registrar paciente. Verifica tu conexión e inténtalo de nuevo.', 'error');
        }
    });

    // --------------------------
    // Eventos de cambio en filtros
    // --------------------------
    monthSelect.addEventListener("change", async () => await renderCalendar());
    yearSelect.addEventListener("change", async () => await renderCalendar());
    doctorSelect.addEventListener("change", async () => await renderCalendar());

    // --------------------------
    // Validaciones de inputs
    // --------------------------
    function validateName(input) {
        input.value = input.value.replace(/[0-9]/g, '');
        input.value = input.value.replace(/[^a-zA-ZáéíóúÁÉÍÓÚñÑüÜ\s\-']/g, '');
    }
    
    function validatePhone(input) {
        input.value = input.value.replace(/[^0-9]/g, '');
        if (input.value.length > 10) {
            input.value = input.value.slice(0, 10);
        }
    }
    
    function validateEmail(email) {
        const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
        return emailRegex.test(email);
    }
    
    const nombreInput    = document.getElementById('p_nombre');
    const apellidosInput = document.getElementById('p_apellidos');
    const telefonoInput  = document.getElementById('p_telefono');
    const emailInput     = document.getElementById('p_email');
    
    nombreInput.addEventListener('input',   function() { validateName(this); });
    apellidosInput.addEventListener('input',function() { validateName(this); });
    telefonoInput.addEventListener('input', function() { validatePhone(this); });
    
    emailInput.addEventListener('blur', function() {
        if (this.value && !validateEmail(this.value)) {
            this.style.borderColor = '#dc3545';
            this.style.boxShadow   = '0 0 0 0.2rem rgba(220, 53, 69, 0.25)';
        } else {
            this.style.borderColor = '';
            this.style.boxShadow   = '';
        }
    });
    
    emailInput.addEventListener('focus', function() {
        this.style.borderColor = '';
        this.style.boxShadow   = '';
    });

    // --------------------------
    // Auto-fill edad / DOB
    // --------------------------
    const fechaNacInput = document.getElementById('p_fecha_nac');
    const edadInput     = document.getElementById('p_edad');
    
    fechaNacInput.addEventListener('change', function() {
        if (this.value) {
            const birthDate = new Date(this.value);
            const today     = new Date();
            
            if (birthDate <= today) {
                let age = today.getFullYear() - birthDate.getFullYear();
                if (today.getMonth() < birthDate.getMonth() || 
                    (today.getMonth() === birthDate.getMonth() && today.getDate() < birthDate.getDate())) {
                    age--;
                }
                if (age >= 0 && age <= 120) {
                    edadInput.value = age;
                }
            } else {
                edadInput.value = '';
            }
        } else {
            edadInput.value = '';
        }
    });
    
    edadInput.addEventListener('input', function() {
        const age = parseInt(this.value);
        
        if (!isNaN(age) && age >= 0 && age <= 120) {
            const today        = new Date();
            const birthYear    = today.getFullYear() - age;
            const approximateDOB = new Date(birthYear, 0, 1);
            const dobString    = approximateDOB.toISOString().split('T')[0];
            fechaNacInput.value = dobString;
        } else if (this.value === '' || isNaN(age)) {
            fechaNacInput.value = '';
        }
    });

    // Init
    (async () => {
        await loadDoctors();
        await renderCalendar();
    })();
});
</script>
@endsection