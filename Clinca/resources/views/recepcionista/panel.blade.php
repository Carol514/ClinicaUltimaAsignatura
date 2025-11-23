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
        <h1 class="recep-page-title">Bienvenid@, Recepcionista</h1>
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
                            <input type="text" id="cita_paciente" name="cita_paciente" placeholder="Buscar paciente..." required>
                            <input type="hidden" id="cita_patient_id" name="cita_patient_id">
                            <div id="patient-suggestions" class="suggestions-dropdown hidden"></div>
                        </div>

                        <div class="field">
                            <label for="cita_doctor">Médico *</label>
                            <select id="cita_doctor" name="cita_doctor" required>
                                <option value="">Seleccione un médico</option>
                                <!-- Doctors will be loaded dynamically -->
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

                        <div class="field" style="grid-column: span 2;">
                            <label for="cita_motivo">Motivo de consulta *</label>
                            <textarea id="cita_motivo" name="cita_motivo" rows="3" placeholder="Describe el motivo de la consulta..." required></textarea>
                        </div>
                    </div>
                </div>

                <div class="btn-container" style="margin-top: 20px;">
                    <button type="submit" class="confirm-btn">
                        <img src="/img/guardar.png" class="btn-icon" alt="Guardar" style="width:24px; height:24px;">
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

</main>


{{-- ================= JS ================= --}}
<script>
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
    
    // Appointment form elements
    const citaPaciente      = document.getElementById("cita_paciente");
    const citaPatientId     = document.getElementById("cita_patient_id");
    const citaDoctor        = document.getElementById("cita_doctor");
    const citaHora          = document.getElementById("cita_hora");
    const citaDuracion      = document.getElementById("cita_duracion");
    const citaMotivo        = document.getElementById("cita_motivo");
    const patientSuggestions = document.getElementById("patient-suggestions");

    // --- nuevo: modal paciente ---
    const btnOpenPaciente   = document.getElementById("btnOpenPaciente");
    const modalPaciente     = document.getElementById("modalPaciente");
    const pacienteForm      = document.getElementById("paciente-form");
    const cerrarModalPaciente = document.getElementById("cerrarModalPaciente");

    let fechaSeleccionada   = null; // para pasarla al modal de agendar

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

    // Valores por defecto
    monthSelect.value = hoy.getMonth();
    yearSelect.value  = añoActual;

    // --------------------------
    // DATA - loaded from API
    // --------------------------
    let CITAS = [];
    let DOCTORS = [];
    
    // Function to load appointments from API
    async function loadAppointments() {
        try {
            // Get current month and year for filtering
            const mes = parseInt(monthSelect.value, 10);
            const año = parseInt(yearSelect.value, 10);
            console.log('🗓️ Loading appointments for month:', mes + 1, 'year:', año);
            
            const currentDate = new Date(año, mes);
            const startOfMonth = new Date(currentDate.getFullYear(), currentDate.getMonth(), 1);
            const endOfMonth = new Date(currentDate.getFullYear(), currentDate.getMonth() + 1, 0);
            
            const fromDate = startOfMonth.toISOString().split('T')[0];
            const toDate = endOfMonth.toISOString().split('T')[0];
            
            let url = `/recepcionista/api/appointments?from=${fromDate}&to=${toDate}`;
            
            // Add clinician filter if selected
            if (doctorSelect.value) {
                url += `&clinician_id=${doctorSelect.value}`;
            }
            
            console.log('📡 Fetching from URL:', url);
            
            const response = await fetch(url, {
                headers: {
                    'X-Requested-With': 'XMLHttpRequest',
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content')
                }
            });
            
            console.log('📨 Response status:', response.status, response.statusText);
            
            if (!response.ok) {
                const errorText = await response.text();
                console.error('❌ API Error Response:', errorText);
                throw new Error(`Failed to load appointments: ${response.status} ${response.statusText}`);
            }
            
            const data = await response.json();
            console.log('API Response:', data); // Debug log
            
            // Transform API data to calendar format
            CITAS = data.data.map(appointment => {
                // Parse the datetime string and extract date/time components locally
                // to avoid timezone shifts
                const scheduledAtStr = appointment.scheduled_at;
                console.log('🕐 Raw scheduled_at:', scheduledAtStr);
                
                // Extract date and time parts from the string directly
                const dateTimeParts = scheduledAtStr.split(' ');
                const datePart = dateTimeParts[0]; // YYYY-MM-DD
                const timePart = dateTimeParts[1] ? dateTimeParts[1].slice(0, 5) : '00:00'; // HH:MM
                
                console.log('📅 Extracted date:', datePart, 'time:', timePart);
                
                return {
                    id: appointment.id,
                    fecha: datePart,
                    hora: timePart,
                    paciente: appointment.patient_name || 'Paciente sin nombre',
                    motivo: appointment.reason || 'Sin motivo especificado',
                    doctor: appointment.clinician_name || 'Doctor no asignado',
                    clinician_id: appointment.clinician_id,
                    duration_min: appointment.duration_min || 30,
                    status: appointment.status || 'pending'
                };
            });
            
            console.log(`Loaded ${CITAS.length} appointments for ${fromDate} to ${toDate}`);
            console.log('Transformed CITAS:', CITAS); // Debug log
            
        } catch (error) {
            console.error('Error loading appointments:', error);
            // Keep empty array on error
            CITAS = [];
        }
    }
    
    // Function to load doctors from API
    async function loadDoctors() {
        try {
            console.log('👨‍⚕️ Loading doctors from API...');
            
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
            DOCTORS = data.data || [];
            
            console.log(`✅ Loaded ${DOCTORS.length} doctors:`, DOCTORS);
            
            // Populate doctor selector
            populateDoctorSelector();
            
        } catch (error) {
            console.error('❌ Error loading doctors:', error);
            DOCTORS = [];
        }
    }
    
    // Function to populate doctor selector dropdown
    function populateDoctorSelector() {
        // Clear existing options except "Todos" for the main filter
        doctorSelect.innerHTML = '<option value="">Todos los médicos</option>';
        
        // Add doctors to main filter selector
        DOCTORS.forEach(doctor => {
            const option = document.createElement('option');
            option.value = doctor.id;
            option.textContent = doctor.name;
            doctorSelect.appendChild(option);
        });
        
        // Also populate the appointment modal doctor selector
        citaDoctor.innerHTML = '<option value="">Seleccione un médico</option>';
        DOCTORS.forEach(doctor => {
            const option = document.createElement('option');
            option.value = doctor.id;
            option.textContent = doctor.name;
            citaDoctor.appendChild(option);
        });
        
        console.log(`📋 Doctor selectors populated with ${DOCTORS.length} doctors`);
    }
    
    // Function to search patients
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
            console.error('❌ Error searching patients:', error);
        }
    }
    
    // Function to display patient suggestions
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
    
    // Function to select a patient
    function selectPatient(patient) {
        citaPaciente.value = patient.nombre;
        citaPatientId.value = patient.id;
        patientSuggestions.classList.add('hidden');
    }
    
    // Function to check for appointment time conflicts
    function checkTimeConflict(doctorId, date, startTime, durationMinutes) {
        // Convert start time to minutes for easier calculation
        const [startHour, startMinute] = startTime.split(':').map(Number);
        const appointmentStartMinutes = startHour * 60 + startMinute;
        const appointmentEndMinutes = appointmentStartMinutes + durationMinutes;
        
        // Check existing appointments for the same doctor on the same date
        const conflictingAppointments = CITAS.filter(appointment => {
            // Same doctor and same date
            if (appointment.clinician_id != doctorId || appointment.fecha !== date) {
                return false;
            }
            
            // Convert existing appointment time to minutes
            const [existingHour, existingMinute] = appointment.hora.split(':').map(Number);
            const existingStartMinutes = existingHour * 60 + existingMinute;
            
            // Use actual duration from appointment data
            const existingDurationMinutes = appointment.duration_min || 30;
            const existingEndMinutes = existingStartMinutes + existingDurationMinutes;
            
            // Check for overlap:
            // Two appointments overlap if one starts before the other ends
            const hasOverlap = (appointmentStartMinutes < existingEndMinutes && 
                               appointmentEndMinutes > existingStartMinutes);
            
            if (hasOverlap) {
                console.log('⚠️ Time conflict detected:', {
                    existing: `${appointment.hora} (${existingDurationMinutes}min) - ${appointment.paciente}`,
                    new: `${startTime} (${durationMinutes}min)`,
                    doctor: appointment.doctor
                });
            }
            
            return hasOverlap;
        });
        
        return conflictingAppointments;
    }
    
    // Function to format time conflicts for user display
    function formatConflictMessage(conflicts, doctorName, newTime, duration) {
        if (conflicts.length === 0) return '';
        
        const conflictList = conflicts.map(c => {
            const endTime = calculateEndTime(c.hora, c.duration_min || 30);
            return `${c.hora}-${endTime} (${c.paciente}, ${c.duration_min || 30}min)`;
        }).join('\n• ');
        
        return `⚠️ Conflicto de horario detectado!\n\nEl/La ${doctorName} ya tiene cita(s) programada(s):\n• ${conflictList}\n\nLa nueva cita de ${duration} minutos a las ${newTime} se solaparía con la(s) cita(s) existente(s).\n\nPor favor, selecciona otro horario.`;
    }
    
    // Helper function to calculate end time
    function calculateEndTime(startTime, durationMinutes) {
        const [hour, minute] = startTime.split(':').map(Number);
        const totalMinutes = hour * 60 + minute + durationMinutes;
        const endHour = Math.floor(totalMinutes / 60);
        const endMinute = totalMinutes % 60;
        return `${endHour.toString().padStart(2, '0')}:${endMinute.toString().padStart(2, '0')}`;
    }

    // --------------------------
    // Renderizar calendario
    // --------------------------
    async function renderCalendar() {
        // Load appointments data before rendering
        await loadAppointments();
        
        calendarGrid.innerHTML = "";

        const mes = parseInt(monthSelect.value, 10);
        const año = parseInt(yearSelect.value, 10);

        const primerDia = new Date(año, mes, 1).getDay(); // 0 = Domingo
        const diasEnMes = new Date(año, mes + 1, 0).getDate();

        // Ajuste para que la semana empiece en Lunes
        const offset = (primerDia === 0 ? 6 : primerDia - 1);

        // Encabezados (Lun - Dom)
        ["Lun","Mar","Mié","Jue","Vie","Sáb","Dom"].forEach(d => {
            const h = document.createElement("div");
            h.className = "calendar-header";
            h.textContent = d;
            calendarGrid.appendChild(h);
        });

        // Celdas vacías antes del primer día
        for (let i = 0; i < offset; i++) {
            const empty = document.createElement("div");
            empty.className = "calendar-cell calendar-cell--empty";
            calendarGrid.appendChild(empty);
        }

        // Celdas de los días del mes
        for (let dia = 1; dia <= diasEnMes; dia++) {

            const fechaStr = `${año}-${String(mes + 1).padStart(2,'0')}-${String(dia).padStart(2,'0')}`;

            // Revisar si hay citas ese día (con filtro de doctor)
            const hasEvents = CITAS.some(c =>
                c.fecha === fechaStr &&
                (doctorSelect.value === "" || c.clinician_id == doctorSelect.value)
            );

            // Debug logging for specific days
            if (dia <= 5 || hasEvents) {
                console.log(`📅 Day ${dia} (${fechaStr}): hasEvents=${hasEvents}, CITAS.length=${CITAS.length}`);
                if (hasEvents) {
                    const dayAppointments = CITAS.filter(c => c.fecha === fechaStr);
                    console.log(`🎯 Appointments for ${fechaStr}:`, dayAppointments);
                }
            }

            const cell = document.createElement("div");
            cell.className = "calendar-cell calendar-cell--day";
            if (hasEvents) {
                cell.classList.add("calendar-cell--has-events");
                console.log(`✅ Added has-events class to day ${dia}`);
            }
            cell.dataset.fecha = fechaStr;
            cell.dataset.dia = dia;

            cell.innerHTML = `
                <div class="day-number">${dia}</div>
                ${hasEvents ? `<div class="day-dot"></div>` : ``}
            `;

            cell.addEventListener("click", () => openDayModal(fechaStr));
            calendarGrid.appendChild(cell);
        }
        
        // Final debug log
        console.log('🏁 Calendar rendering complete. Total CITAS:', CITAS.length);
        if (CITAS.length > 0) {
            console.log('📋 All appointment dates:', CITAS.map(c => c.fecha));
        }
    }

    // --------------------------
    // Helper functions
    // --------------------------
    function getAppointmentStatusInfo(status) {
        const statusMap = {
            'pending': { color: '#ffc107', text: 'Pendiente' },
            'confirmed': { color: '#28a745', text: 'Confirmada' },
            'cancelled': { color: '#dc3545', text: 'Cancelada' },
            'completed': { color: '#6c757d', text: 'Completada' },
            'no_show': { color: '#fd7e14', text: 'No asistió' },
            'rescheduled': { color: '#17a2b8', text: 'Reprogramada' }
        };
        return statusMap[status] || { color: '#6c757d', text: status || 'Sin estado' };
    }
    
    function formatDate(dateStr) {
        // Create date with explicit parsing to avoid timezone issues
        const [year, month, day] = dateStr.split('-').map(Number);
        const date = new Date(year, month - 1, day); // month is 0-based in Date constructor
        
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
        console.log('Opening modal for date:', fechaStr); // Debug log
        console.log('Available CITAS:', CITAS); // Debug log
        
        fechaSeleccionada = fechaStr;

        const citas = CITAS.filter(c =>
            c.fecha === fechaStr &&
            (doctorSelect.value === "" || c.clinician_id == doctorSelect.value)
        );

        console.log('Filtered citas for this date:', citas); // Debug log
        modalDiaTitulo.textContent = `Citas del ${formatDate(fechaStr)}`;
        listaCitasDia.innerHTML = "";

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
                // Get status color and text
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
    // Patient search functionality
    // --------------------------
    citaPaciente.addEventListener('input', (e) => {
        const query = e.target.value.trim();
        searchPatients(query);
    });
    
    // Hide suggestions when clicking outside
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
        
        // Reset form
        agendarForm.reset();
        citaPatientId.value = '';
        
        // Set date info
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
    // Form submission handler
    // --------------------------
    agendarForm.addEventListener('submit', async (e) => {
        e.preventDefault();
        
        // Validate required fields
        const patientId = citaPatientId.value;
        const doctorId = citaDoctor.value;
        const hora = citaHora.value;
        const motivo = citaMotivo.value.trim();
        
        if (!patientId) {
            alert('❌ Por favor selecciona un paciente válido de la lista de sugerencias.');
            return;
        }
        
        if (!doctorId) {
            alert('❌ Por favor selecciona un médico.');
            return;
        }
        
        if (!hora) {
            alert('❌ Por favor selecciona una hora para la cita.');
            return;
        }
        
        if (!motivo) {
            alert('❌ Por favor describe el motivo de la consulta.');
            return;
        }
        
        // Check for time conflicts (client-side pre-validation)
        const duration = parseInt(citaDuracion.value) || 30;
        const conflicts = checkTimeConflict(doctorId, fechaSeleccionada, hora, duration);
        
        if (conflicts.length > 0) {
            const selectedDoctor = DOCTORS.find(d => d.id == doctorId);
            const doctorName = selectedDoctor ? selectedDoctor.name : 'Doctor seleccionado';
            const conflictMessage = formatConflictMessage(conflicts, doctorName, hora, duration);
            alert(conflictMessage);
            return;
        }
        
        // Additional server-side conflict check for accuracy
        try {
            const scheduledDateTime = `${fechaSeleccionada} ${hora}:00`;
            const conflictCheckResponse = await fetch(`/recepcionista/api/appointments?from=${fechaSeleccionada}&to=${fechaSeleccionada}&clinician_id=${doctorId}`, {
                headers: {
                    'X-Requested-With': 'XMLHttpRequest',
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content')
                }
            });
            
            if (conflictCheckResponse.ok) {
                const existingData = await conflictCheckResponse.json();
                const existingAppointments = existingData.data || [];
                
                // Check for server-side conflicts with actual durations
                const serverConflicts = existingAppointments.filter(appointment => {
                    const existingDateTime = new Date(appointment.scheduled_at);
                    const newDateTime = new Date(scheduledDateTime);
                    const existingDuration = appointment.duration_min || 30;
                    
                    const existingEndTime = new Date(existingDateTime.getTime() + existingDuration * 60000);
                    const newEndTime = new Date(newDateTime.getTime() + duration * 60000);
                    
                    // Check for overlap
                    return (newDateTime < existingEndTime && newEndTime > existingDateTime);
                });
                
                if (serverConflicts.length > 0) {
                    const selectedDoctor = DOCTORS.find(d => d.id == doctorId);
                    const doctorName = selectedDoctor ? selectedDoctor.name : 'Doctor seleccionado';
                    
                    const conflictDetails = serverConflicts.map(c => {
                        const time = new Date(c.scheduled_at).toLocaleTimeString('es-ES', {hour: '2-digit', minute: '2-digit'});
                        const patientName = c.patient_name || 'Paciente';
                        const duration = c.duration_min || 30;
                        return `${time} (${patientName}, ${duration} min)`;
                    }).join(', ');
                    
                    alert(`⚠️ Conflicto de horario confirmado!\n\nEl/La ${doctorName} ya tiene cita(s) programada(s):\n${conflictDetails}\n\nLa nueva cita se solaparía. Por favor, selecciona otro horario.`);
                    return;
                }
            }
        } catch (conflictError) {
            console.warn('⚠️ Could not verify conflicts on server:', conflictError);
            // Continue with creation if server check fails, client-side check already passed
        }
        
        try {
            // Prepare appointment data
            const scheduledDateTime = `${fechaSeleccionada} ${hora}:00`;
            
            const appointmentData = {
                patient_id: patientId,
                clinician_id: doctorId,
                scheduled_at: scheduledDateTime,
                duration_min: duration,
                reason: motivo,
                status: 'pending'
            };
            
            console.log('📝 Creating appointment:', appointmentData);
            
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
            console.log('✅ Appointment created:', result);
            
            // Success feedback
            alert(`✅ Cita agendada exitosamente para ${citaPaciente.value} el ${formatDate(fechaSeleccionada)} a las ${hora}`);
            
            // Close modal, refresh calendar, and update the day modal
            closeAgendarModal();
            await renderCalendar();
            
            // If the day modal is still open for the same date, refresh its content
            if (!modalCitasDia.classList.contains('hidden') && fechaSeleccionada) {
                openDayModal(fechaSeleccionada);
            }
            
        } catch (error) {
            console.error('❌ Error creating appointment:', error);
            alert('❌ Error al agendar la cita. Por favor verifica los datos e inténtalo de nuevo.');
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
        
        // Get form data
        const nombre = document.getElementById('p_nombre').value.trim();
        const apellidos = document.getElementById('p_apellidos').value.trim();
        const fechaNac = document.getElementById('p_fecha_nac').value;
        const edadInput = document.getElementById('p_edad').value;
        const genero = document.getElementById('p_genero').value;
        const telefono = document.getElementById('p_telefono').value.trim();
        const email = document.getElementById('p_email').value.trim();
        const direccion = document.getElementById('p_direccion').value.trim();
        
        // Validate all required fields
        if (!nombre || !apellidos || !telefono || !email || !fechaNac || !edadInput || !genero || !direccion) {
            alert('❌ Por favor completa todos los campos del formulario.');
            return;
        }
        
        // Validate name fields (no numbers)
        const nameRegex = /^[a-zA-ZáéíóúÁÉÍÓÚñÑüÜ\s\-']+$/;
        if (!nameRegex.test(nombre)) {
            alert('❌ El nombre no debe contener números o caracteres especiales.');
            return;
        }
        
        if (!nameRegex.test(apellidos)) {
            alert('❌ Los apellidos no deben contener números o caracteres especiales.');
            return;
        }
        
        // Validate phone (exactly 10 digits)
        const phoneRegex = /^\d{10}$/;
        if (!phoneRegex.test(telefono)) {
            alert('❌ El teléfono debe contener exactamente 10 dígitos sin letras ni espacios.');
            return;
        }
        
        // Validate email format
        if (!validateEmail(email)) {
            alert('❌ Por favor, ingrese un email válido.');
            return;
        }
        
        // Validate age
        const ageNum = parseInt(edadInput);
        if (isNaN(ageNum) || ageNum < 0 || ageNum > 120) {
            alert('❌ La edad debe ser un número válido entre 0 y 120 años.');
            return;
        }
        
        // Validate gender selection
        if (!genero || genero === '') {
            alert('❌ Por favor selecciona el género del paciente.');
            return;
        }
        
        // Map gender to backend format
        let sex = '';
        if (genero === 'Masculino') sex = 'M';
        else if (genero === 'Femenino') sex = 'F';
        else if (genero === 'Otro') sex = 'I';
        
        // Use age from direct input or calculate from birth date
        let age = null;
        if (edadInput && !isNaN(edadInput) && parseInt(edadInput) > 0) {
            age = parseInt(edadInput);
        } else if (fechaNac) {
            const birthDate = new Date(fechaNac);
            const today = new Date();
            age = today.getFullYear() - birthDate.getFullYear();
            if (today.getMonth() < birthDate.getMonth() || 
                (today.getMonth() === birthDate.getMonth() && today.getDate() < birthDate.getDate())) {
                age--;
            }
        }
        
        // Prepare payload
        const payload = {
            first_name: nombre,
            last_name: apellidos,
            sex: sex,
            phone: telefono,
            email: email || null,
            address: direccion,
            age: age
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
                    // Validation errors
                    const messages = [];
                    for (const field in errorData.errors) {
                        messages.push(errorData.errors[field].join(', '));
                    }
                    alert('❌ Errores de validación:\n' + messages.join('\n'));
                } else {
                    alert('❌ Error al registrar paciente: ' + (errorData?.message || `HTTP ${response.status}`));
                }
                return;
            }
            
            const result = await response.json();
            
            // Success message
            let message = '✅ Paciente registrado exitosamente';
            if (result.data?.id) {
                message += ` (ID: ${result.data.id})`;
            }
            if (result.created_user) {
                message += `\n\n🔐 Usuario creado:\nEmail: ${result.created_user.email}\nContraseña temporal: ${result.created_user.temp_password}`;
            }
            
            alert(message);
            closePacienteModal();
            
        } catch (error) {
            console.error('Error registering patient:', error);
            alert('❌ Error de conexión al registrar paciente. Verifica tu conexión e inténtalo de nuevo.');
        }
    });

    // --------------------------
    // Eventos de cambio en filtros
    // --------------------------
    monthSelect.addEventListener("change", async () => await renderCalendar());
    yearSelect.addEventListener("change", async () => await renderCalendar());
    doctorSelect.addEventListener("change", async () => await renderCalendar());

    // --------------------------
    // Input validation functions
    // --------------------------
    function validateName(input) {
        // Remove any numbers from name fields
        input.value = input.value.replace(/[0-9]/g, '');
        
        // Optional: Also remove special characters except spaces, hyphens, and apostrophes
        input.value = input.value.replace(/[^a-zA-ZáéíóúÁÉÍÓÚñÑüÜ\s\-']/g, '');
    }
    
    function validatePhone(input) {
        // Remove any non-digit characters
        input.value = input.value.replace(/[^0-9]/g, '');
        
        // Limit to 10 digits
        if (input.value.length > 10) {
            input.value = input.value.slice(0, 10);
        }
    }
    
    function validateEmail(email) {
        const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
        return emailRegex.test(email);
    }
    
    // --------------------------
    // Apply validation to form inputs
    // --------------------------
    const nombreInput = document.getElementById('p_nombre');
    const apellidosInput = document.getElementById('p_apellidos');
    const telefonoInput = document.getElementById('p_telefono');
    const emailInput = document.getElementById('p_email');
    
    // Real-time validation for names (remove numbers as user types)
    nombreInput.addEventListener('input', function() {
        validateName(this);
    });
    
    apellidosInput.addEventListener('input', function() {
        validateName(this);
    });
    
    // Real-time validation for phone (keep only numbers, max 10 digits)
    telefonoInput.addEventListener('input', function() {
        validatePhone(this);
    });
    
    // Email validation on blur
    emailInput.addEventListener('blur', function() {
        if (this.value && !validateEmail(this.value)) {
            this.style.borderColor = '#dc3545';
            this.style.boxShadow = '0 0 0 0.2rem rgba(220, 53, 69, 0.25)';
        } else {
            this.style.borderColor = '';
            this.style.boxShadow = '';
        }
    });
    
    // Clear email validation styling on focus
    emailInput.addEventListener('focus', function() {
        this.style.borderColor = '';
        this.style.boxShadow = '';
    });

    // --------------------------
    // Auto-fill age/DOB fields
    // --------------------------
    const fechaNacInput = document.getElementById('p_fecha_nac');
    const edadInput = document.getElementById('p_edad');
    
    // When date of birth changes, calculate and fill age
    fechaNacInput.addEventListener('change', function() {
        if (this.value) {
            const birthDate = new Date(this.value);
            const today = new Date();
            
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
                // Future date - clear age
                edadInput.value = '';
            }
        } else {
            // No date - clear age
            edadInput.value = '';
        }
    });
    
    // When age changes, calculate and fill date of birth
    edadInput.addEventListener('input', function() {
        const age = parseInt(this.value);
        
        if (!isNaN(age) && age >= 0 && age <= 120) {
            const today = new Date();
            const birthYear = today.getFullYear() - age;
            
            // Use January 1st as approximate birth date
            const approximateDOB = new Date(birthYear, 0, 1); // January 1st
            const dobString = approximateDOB.toISOString().split('T')[0];
            
            fechaNacInput.value = dobString;
        } else if (this.value === '' || isNaN(age)) {
            // Clear DOB if age is empty or invalid
            fechaNacInput.value = '';
        }
    });

    // Initialize page - load doctors first, then render calendar
    (async () => {
        await loadDoctors();
        await renderCalendar();
    })();
});
</script>
@endsection
