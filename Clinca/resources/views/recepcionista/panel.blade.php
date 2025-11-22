{{-- resources/views/recepcionista/panel.blade.php --}}
@extends('layouts.app')
@section('title', 'Panel de Recepción')

@section('content')
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
                        <label for="doctorSelect">Doctor</label>
                        <select id="doctorSelect">
                            <option value="">Todos</option>
                            <option>Dr. Hernández</option>
                            <option>Dra. Gómez</option>
                            <option>Dr. López</option>
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
        <div class="modal-content">
            <h3 class="modal-title">Agendar cita</h3>

            <p id="modalInfo"></p>

            <label>Paciente</label>
            <input type="text" class="modal-input" placeholder="Nombre del paciente">

            <label>Hora</label>
            <input type="time" class="modal-input" id="modalHora">

            <label>Motivo</label>
            <input type="text" class="modal-input" placeholder="Motivo de consulta">

            <div class="modal-actions" style="margin-top: 15px;">
                <button class="confirm-btn" type="button">
                    <img src="/img/guardar.png" style="width:24px; height:24px;">
                </button>

                <button id="cerrarModalAgendar" class="cancel-btn modal-cancel-btn" type="button">
                    <img src="/img/cancelar.png" width="15" height="15" alt="Cancelar">
                </button>
            </div>
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
                            <input id="p_nombre" type="text" placeholder="Ej. Hugo Abraham">
                        </div>

                        <div class="field">
                            <label for="p_apellidos">Apellidos</label>
                            <input id="p_apellidos" type="text" placeholder="Ej. García Tovar">
                        </div>

                        <div class="field">
                            <label for="p_fecha_nac">Fecha de nacimiento</label>
                            <input id="p_fecha_nac" type="date">
                        </div>

                        <div class="field">
                            <label for="p_fecha_nac">Edad</label>
                            <input id="p_apellidos" type="text" placeholder="18">
                        </div>

                        <div class="field">
                            <label for="p_genero">Género</label>
                            <select id="p_genero">
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
                            <input id="p_telefono" type="tel" placeholder="Ej. 3111234567">
                        </div>

                        <div class="field">
                            <label for="p_email">Correo electrónico</label>
                            <input id="p_email" type="email" placeholder="Ej. paciente@correo.com">
                        </div>

                        <div class="field">
                            <label for="p_direccion">Dirección</label>
                            <input id="p_direccion" type="text" placeholder="Calle, número, colonia">
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
    // DEMO: citas de ejemplo
    // --------------------------
    const CITAS = [
        {
            fecha:"2025-11-01",
            hora:"09:00",
            paciente:"Ana López",
            motivo:"Dolor abdominal",
            doctor:"Dr. Hernández"
        },
        {
            fecha:"2025-11-24",
            hora:"11:00",
            paciente:"Juan Pérez",
            motivo:"Control de seguimiento",
            doctor:"Dr. Hernández"
        },
        {
            fecha:"2025-11-28",
            hora:"10:30",
            paciente:"María Gómez",
            motivo:"Entrega de resultados",
            doctor:"Dra. Gómez"
        }
    ];

    // --------------------------
    // Renderizar calendario
    // --------------------------
    function renderCalendar() {
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
                (doctorSelect.value === "" || c.doctor === doctorSelect.value)
            );

            const cell = document.createElement("div");
            cell.className = "calendar-cell calendar-cell--day";
            if (hasEvents) {
                cell.classList.add("calendar-cell--has-events");
            }
            cell.dataset.fecha = fechaStr;

            cell.innerHTML = `
                <div class="day-number">${dia}</div>
                ${hasEvents ? `<div class="day-dot"></div>` : ``}
            `;

            cell.addEventListener("click", () => openDayModal(fechaStr));
            calendarGrid.appendChild(cell);
        }
    }

    // --------------------------
    // Modal: citas del día
    // --------------------------
    function openDayModal(fechaStr) {
        fechaSeleccionada = fechaStr;

        const citas = CITAS.filter(c =>
            c.fecha === fechaStr &&
            (doctorSelect.value === "" || c.doctor === doctorSelect.value)
        );

        modalDiaTitulo.textContent = `Citas del ${fechaStr}`;
        listaCitasDia.innerHTML = "";

        if (!citas.length) {
            listaCitasDia.innerHTML = `<p class="muted">No hay citas para este día.</p>`;
        } else {
            citas
              .sort((a,b)=> (a.hora||'').localeCompare(b.hora||''))

              .forEach(c => {
                const div = document.createElement("div");
                div.className = "cita-item";
                div.innerHTML = `
                    <div class="cita-main">
                        <span class="cita-hora"><strong>${c.hora}</strong></span>
                        <span class="cita-paciente">${c.paciente}</span>
                    </div>
                    <div class="cita-motivo">${c.motivo}</div>
                    <div class="cita-doctor">${c.doctor}</div>
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
    // Abrir modal de agendar desde el día
    // --------------------------
    btnAgregarCitaDia.addEventListener("click", () => {
        if (!fechaSeleccionada) return;
        modalInfo.textContent = `Fecha seleccionada: ${fechaSeleccionada}`;
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

    pacienteForm.addEventListener("submit", (e) => {
        e.preventDefault();
        // Aquí luego haces POST al backend. Por ahora, solo demo:
        alert('✅ Paciente registrado (demo, solo maquetado).');
        closePacienteModal();
    });

    // --------------------------
    // Eventos de cambio en filtros
    // --------------------------
    monthSelect.addEventListener("change", renderCalendar);
    yearSelect.addEventListener("change", renderCalendar);
    doctorSelect.addEventListener("change", renderCalendar);

    // Primer render
    renderCalendar();
});
</script>
@endsection
