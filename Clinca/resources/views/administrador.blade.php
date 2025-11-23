{{-- resources/views/administrador/panel.blade.php --}}
@extends('layouts.app')
@section('title','Panel del Administrador')

@section('content')
<main class="dashboard admin-dashboard">
  <h2>Panel del Administrador</h2>
  <p class="muted">
    Resumen general del sistema y acceso rápido a las funciones administrativas.
  </p>

  {{-- ==========================
        CARDS SUPERIORES
      =========================== --}}
  <section class="admin-top-grid">
    <div class="admin-card admin-card--stat">
      <p class="admin-stat-label">Pacientes registrados</p>
      <p class="admin-stat-number" id="statPacientes">4</p>
      <p class="admin-stat-caption">En toda la clínica</p>
    </div>

    <div class="admin-card admin-card--stat">
      <p class="admin-stat-label">Citas programadas hoy</p>
      <p class="admin-stat-number" id="statCitasHoyAdmin">3</p>
      <p class="admin-stat-caption">Incluye programadas, confirmadas y atendidas</p>
    </div>

    <div class="admin-card admin-card--stat">
      <p class="admin-stat-label">Personal activo</p>
      <p class="admin-stat-number" id="statPersonal">4</p>
      <p class="admin-stat-caption">Médicos, enfermería y administrativos</p>
    </div>
  </section>

  {{-- ==========================
        GRÁFICAS (MISMA FILA)
      =========================== --}}
  <section class="admin-charts-section">

    {{-- Donut / pastel por estado --}}
    <div class="admin-card admin-card--chart">
      <h3 class="admin-card-title">Distribución de citas por estado</h3>

      <div class="admin-chart-row">
        <div class="admin-pie-wrapper">
          <div class="admin-pie"></div>
        </div>

        <ul class="admin-legend">
          <li>
            <span class="legend-dot" style="background:#5bc0de;"></span>
            <span class="legend-label">Programadas</span>
            <span class="legend-value">2 (17%)</span>
          </li>
          <li>
            <span class="legend-dot" style="background:#007bff;"></span>
            <span class="legend-label">Confirmadas</span>
            <span class="legend-value">2 (17%)</span>
          </li>
          <li>
            <span class="legend-dot" style="background:#28a745;"></span>
            <span class="legend-label">Atendidas</span>
            <span class="legend-value">5 (42%)</span>
          </li>
          <li>
            <span class="legend-dot" style="background:#ffc107;"></span>
            <span class="legend-label">No asistió</span>
            <span class="legend-value">1 (8%)</span>
          </li>
          <li>
            <span class="legend-dot" style="background:#dc3545;"></span>
            <span class="legend-label">Canceladas</span>
            <span class="legend-value">2 (17%)</span>
          </li>
        </ul>
      </div>

      <p class="admin-chart-caption">
        Vista rápida de cuántas citas están programadas, confirmadas, canceladas,
        no asistidas o atendidas.
      </p>
    </div>

    {{-- Gráfica lineal (barras simuladas) --}}
    <div class="admin-card admin-card--chart">
      <h3 class="admin-card-title">Citas atendidas por día (últimos 7 días)</h3>

      <div id="adminLineChart" class="admin-line-chart">
        {{-- Se llena por JS para la maqueta --}}
      </div>

      <p class="admin-chart-caption">
        Tendencia diaria de atención para apoyar la toma de decisiones y la planeación de capacidad.
      </p>
    </div>
  </section>

  {{-- ==========================
        GENERAR REPORTES
      =========================== --}}
  <section class="admin-actions-section">
    <div class="admin-card">
      <h3 class="admin-card-title" style="text-align:center;">Generar reportes</h3>
      <p class="admin-module-text" style="text-align:center;">
        Reportes enfocados en <strong>pacientes atendidos</strong>. Esta sección está maquetada para
        conectar después con la generación real de PDFs desde la base de datos.
      </p>

      <div class="admin-report-row">
        <select id="reporteTipo">
          <option value="">Seleccione tipo de reporte...</option>
          <option value="citas">Pacientes atendidos por día</option>
          <option value="usuarios">Usuarios por rol (pacientes atendidos por área)</option>
          <option value="tratamientos">Pacientes atendidos y tratamientos aplicados</option>
        </select>

        <button type="button" class="confirm-btn admin-inline-btn" id="btnGenerarReporteDemo">
          <img src="/img/descargar.png" class="btn-icon" alt="Descargar" style="width:24px; height:24px;">
          <span>Generar</span>
        </button>
      </div>

      {{-- Vista previa del reporte en tabla (demo) --}}
      <div id="adminReportResult" class="admin-report-result" style="margin-top:10px;">
        <div class="table-container">
          <table>
            <thead id="adminReportHead"></thead>
            <tbody id="adminReportBody"></tbody>
          </table>
          <p id="adminReportEmpty" class="muted" style="text-align:center;margin-top:8px;">
            Selecciona un tipo de reporte y haz clic en "Generar" para ver un ejemplo.
          </p>
        </div>
      </div>

      <hr class="section-divider" style="margin-top:16px;">

      {{-- Lista de reportes generados (simulando PDFs descargables) --}}
      <h4 class="admin-subtitle" style="text-align:left;margin-bottom:8px;">Reportes generados</h4>
      <div class="table-container">
        <table>
          <thead>
            <tr>
              <th>Fecha de reporte</th>
              <th>Tipo</th>
              <th>Formato</th>
              <th></th>
            </tr>
          </thead>
          <tbody id="adminReportListBody">
            <tr>
              <td>21/11/2025 10:15</td>
              <td>Pacientes atendidos (última semana)</td>
              <td>PDF</td>
              <td>
                <button type="button" class="confirm-btn admin-inline-btn admin-download-btn">
                  <img src="/img/descargar.png" class="btn-icon" alt="Descargar" style="width:24px; height:24px;">
                </button>
              </td>
            </tr>
            <tr>
              <td>18/11/2025 09:40</td>
              <td>Pacientes atendidos (último mes)</td>
              <td>PDF</td>
              <td>
                <button type="button" class="confirm-btn admin-inline-btn admin-download-btn">
                  <img src="/img/descargar.png" class="btn-icon" alt="Descargar" style="width:24px; height:24px;">
                </button>
              </td>
            </tr>
          </tbody>
        </table>
        <p class="muted" style="font-size:13px;margin-top:6px;">
          Los archivos listados son ejemplos. En una versión conectada a la BD, aquí aparecerían
          los PDFs reales generados por el administrador.
        </p>
      </div>
    </div>
  </section>

  {{-- ==========================
        RESPALDOS DE BD
      =========================== --}}
  <section class="admin-actions-section">
    <div class="admin-card">
      <div class="admin-module-inline">
        <div>
          <h4 style="margin:0;">Respaldos de BD</h4>
          <p class="admin-module-text">
            Generar un respaldo manual de la base de datos (solo demostración visual).
          </p>
        </div>

        <button type="button" class="confirm-btn admin-inline-btn" id="btnBackupDemo">
          <img src="/img/descargar.png" class="btn-icon" alt="Descargar" style="width:24px; height:24px;">
          <span>Generar respaldo</span>
        </button>
      </div>
    </div>
  </section>

  {{-- ==========================
        PANEL ADMINISTRATIVO
      =========================== --}}
  <section class="admin-panel-section">
    <div class="admin-card">
      <h3 class="admin-card-title" style="text-align:center;">Panel administrativo</h3>
      <p class="admin-module-text" style="text-align:center;">
        Resumen rápido de los usuarios del sistema. Los botones son demostración de edición y eliminación.
      </p>

      {{-- Filtro por rol --}}
      <div class="admin-filter-row">
        <span>Filtrar por rol:</span>
        <select id="filterRol">
          <option value="">Todos</option>
          <option value="Administrador">Administrador</option>
          <option value="Médico">Médico</option>
          <option value="Enfermera">Enfermera</option>
          <option value="Recepcionista">Recepcionista</option>
        </select>
      </div>

      {{-- Tabla de usuarios / roles --}}
      <div class="admin-roles-table table-container">
        <table>
          <thead>
            <tr>
              <th>Usuario</th>
              <th>Rol</th>
              <th>Acciones</th>
            </tr>
          </thead>
          <tbody id="adminRolesBody">
  <tr data-rol="Administrador">
    <td>admin</td>
    <td>Administrador</td>
    <td class="admin-actions">
      <button type="button"
              class="confirm-btn admin-inline-btn admin-edit-btn"
              data-user="admin"
              data-role="Administrador"
              style="background-color: orange;"
              onmouseover="this.style.backgroundColor='darkorange'"
              onmouseout="this.style.backgroundColor='orange'">
        <img src="/img/editar.png" class="btn-icon" alt="Editar" style="width:24px; height:24px;">
      </button>

      <button type="button"
              class="cancel-btn admin-inline-btn admin-delete-btn"
              style="background-color:#e74c3c;"
              onmouseover="this.style.backgroundColor='#c0392b'"
              onmouseout="this.style.backgroundColor='#e74c3c'">
        <img src="/img/cancelar.png" class="btn-icon" alt="Eliminar" style="width:24px; height:24px;">
      </button>
    </td>
  </tr>

  <tr data-rol="Médico">
    <td>medico01</td>
    <td>Médico</td>
    <td class="admin-actions">
      <button type="button"
              class="confirm-btn admin-inline-btn admin-edit-btn"
              data-user="medico01"
              data-role="Médico"
              style="background-color: orange;"
              onmouseover="this.style.backgroundColor='darkorange'"
              onmouseout="this.style.backgroundColor='orange'">
        <img src="/img/editar.png" class="btn-icon" alt="Editar" style="width:24px; height:24px;">
      </button>

      <button type="button"
              class="cancel-btn admin-inline-btn admin-delete-btn"
              style="background-color:#e74c3c;"
              onmouseover="this.style.backgroundColor='#c0392b'"
              onmouseout="this.style.backgroundColor='#e74c3c'">
        <img src="/img/cancelar.png" class="btn-icon" alt="Eliminar" style="width:24px; height:24px;">
      </button>
    </td>
  </tr>

  <tr data-rol="Enfermera">
    <td>enfermera01</td>
    <td>Enfermera</td>
    <td class="admin-actions">
      <button type="button"
              class="confirm-btn admin-inline-btn admin-edit-btn"
              data-user="enfermera01"
              data-role="Enfermera"
              style="background-color: orange;"
              onmouseover="this.style.backgroundColor='darkorange'"
              onmouseout="this.style.backgroundColor='orange'">
        <img src="/img/editar.png" class="btn-icon" alt="Editar" style="width:24px; height:24px;">
      </button>

      <button type="button"
              class="cancel-btn admin-inline-btn admin-delete-btn"
              style="background-color:#e74c3c;"
              onmouseover="this.style.backgroundColor='#c0392b'"
              onmouseout="this.style.backgroundColor='#e74c3c'">
        <img src="/img/cancelar.png" class="btn-icon" alt="Eliminar" style="width:24px; height:24px;">
      </button>
    </td>
  </tr>

  <tr data-rol="Recepcionista">
    <td>recepcion01</td>
    <td>Recepcionista</td>
    <td class="admin-actions">
      <button type="button"
              class="confirm-btn admin-inline-btn admin-edit-btn"
              data-user="recepcion01"
              data-role="Recepcionista"
              style="background-color: orange;"
              onmouseover="this.style.backgroundColor='darkorange'"
              onmouseout="this.style.backgroundColor='orange'">
        <img src="/img/editar.png" class="btn-icon" alt="Editar" style="width:24px; height:24px;">
      </button>

      <button type="button"
              class="cancel-btn admin-inline-btn admin-delete-btn"
              style="background-color:#e74c3c;"
              onmouseover="this.style.backgroundColor='#c0392b'"
              onmouseout="this.style.backgroundColor='#e74c3c'">
        <img src="/img/cancelar.png" class="btn-icon" alt="Eliminar" style="width:24px; height:24px;">
      </button>
    </td>
  </tr>
</tbody>

        </table>
      </div>

      <p class="muted" style="font-size:13px;margin-top:8px;">
        Más adelante este panel se puede conectar a la gestión real de usuarios, roles y permisos.
      </p>
    </div>
  </section>

  <!-- =======================
     MODAL EDITAR USUARIO
======================= -->
<div id="modalEditUser" class="modal hidden">
  <div class="modal-content modal-sm">

    <h3 class="modal-title">Editar usuario</h3>

    <div class="modal-body">
      <div class="field">
        <label>Usuario</label>
        <input id="editUserName" readonly>
      </div>

      <div class="field" style="margin-top:12px;">
        <label>Rol</label>
        <select id="editUserRole">
          <option value="Médico">Médico</option>
          <option value="Enfermera">Enfermera</option>
          <option value="Recepcionista">Recepcionista</option>
        </select>
      </div>
    </div>

    <div class="btn-container" style="margin-top:20px;">
      <button id="btnSaveUser" class="confirm-btn">
        <img src="/img/guardar.png" class="btn-icon" alt="Guardar" style="width:28px; height:28px;">
      </button>

      <button id="btnCancelEdit" class="cancel-btn">
        <img src="/img/cancelar.png" class="btn-icon" alt="Cancelar" style="width:20px; height:20px;">
      </button>
    </div>

  </div>
</div>

</main>

{{-- ================= JS MAQUETADO ================= --}}
<script>
document.addEventListener('DOMContentLoaded', () => {

  // ----------- Gráfica lineal demo -----------
  const lineChart = document.getElementById('adminLineChart');
  const lineData = [
    { label: 'lun', value: 0 },
    { label: 'mar', value: 0 },
    { label: 'mié', value: 0 },
    { label: 'jue', value: 1 },
    { label: 'vie', value: 2 },
    { label: 'sáb', value: 2 },
    { label: 'dom', value: 1 },
  ];
  const maxVal = Math.max(...lineData.map(d => d.value)) || 1;

  lineData.forEach(d => {
    const bar = document.createElement('div');
    bar.className = 'line-bar';
    bar.innerHTML = `
      <div class="line-bar-inner" style="height:${(d.value / maxVal) * 100}%"></div>
      <span class="line-value">${d.value}</span>
      <span class="line-label">${d.label}</span>
    `;
    lineChart.appendChild(bar);
  });

  // ----------- Botón de respaldo (demo) -----------
  const btnBackup  = document.getElementById('btnBackupDemo');
  btnBackup.addEventListener('click', () => {
    alert('Aquí se conectará la generación real de respaldos.\nPor ahora es solo demostración visual.');
  });

  // ----------- Reportes demo (tabla) -----------
  const btnReporte = document.getElementById('btnGenerarReporteDemo');
  const selTipoRep = document.getElementById('reporteTipo');
  const repHead  = document.getElementById('adminReportHead');
  const repBody  = document.getElementById('adminReportBody');
  const repEmpty = document.getElementById('adminReportEmpty');
  const reportListBody = document.getElementById('adminReportListBody');

  const REPORT_DEMO = {
    usuarios: {
      encabezado: ['Usuario', 'Rol'],
      filas: [
        ['admin',       'Administrador'],
        ['medico01',    'Médico'],
        ['enfermera01', 'Enfermera'],
        ['recepcion01', 'Recepcionista'],
      ],
    },
    citas: {
      encabezado: ['Fecha', 'Pacientes atendidos'],
      filas: [
        ['2025-11-18', 12],
        ['2025-11-19', 9],
        ['2025-11-20', 15],
        ['2025-11-21', 11],
        ['2025-11-22', 8],
      ],
    },
    tratamientos: {
      encabezado: ['Paciente', 'Tratamiento', 'Fecha'],
      filas: [
        ['Hugo García',   'Omeprazol 20 mg c/12h',    '2025-11-21'],
        ['María López',   'Metformina 850 mg c/12h',  '2025-11-10'],
        ['Juan Pérez',    'Ibuprofeno 400 mg c/8h',   '2025-11-18'],
        ['Ana Díaz',      'Salbutamol inhalado',      '2025-11-19'],
      ],
    },
  };

  const REPORT_LABELS = {
    citas: 'Pacientes atendidos por día',
    usuarios: 'Usuarios por rol (pacientes atendidos por área)',
    tratamientos: 'Pacientes atendidos y tratamientos aplicados',
  };

  function renderReport(tipo) {
    const cfg = REPORT_DEMO[tipo];
    if (!cfg) {
      repHead.innerHTML = '';
      repBody.innerHTML = '';
      repEmpty.style.display = 'block';
      return;
    }

    repHead.innerHTML = `
      <tr>${cfg.encabezado.map(h => `<th>${h}</th>`).join('')}</tr>
    `;
    repBody.innerHTML = cfg.filas
      .map(row => `<tr>${row.map(col => `<td>${col}</td>`).join('')}</tr>`)
      .join('');
    repEmpty.style.display = 'none';
  }

  btnReporte.addEventListener('click', () => {
    const tipo = selTipoRep.value;
    if (!tipo) {
      alert('Selecciona un tipo de reporte.');
      return;
    }

    // Vista previa
    renderReport(tipo);

    // Simular que se generó un PDF y se agrega a la lista
    const now = new Date();
    const fecha = now.toLocaleDateString('es-MX', {
      day:'2-digit', month:'2-digit', year:'numeric'
    });
    const hora = now.toLocaleTimeString('es-MX', {
      hour:'2-digit', minute:'2-digit'
    });
    const tipoTexto = REPORT_LABELS[tipo] || 'Reporte de pacientes atendidos';

    const tr = document.createElement('tr');
    tr.innerHTML = `
      <td>${fecha} ${hora}</td>
      <td>${tipoTexto}</td>
      <td>PDF</td>
      <td>
        <button type="button" class="confirm-btn admin-inline-btn admin-download-btn">
          <img src="/img/descargar.png" class="btn-icon" alt="Descargar" style="width:24px; height:24px;">
        </button>
      </td>
    `;
    reportListBody.prepend(tr);
  });

  // ----------- Filtro por rol en la tabla -----------
  const filterRol = document.getElementById('filterRol');
  const rolesBody = document.getElementById('adminRolesBody');

  filterRol.addEventListener('change', () => {
    const val = filterRol.value.toLowerCase();
    rolesBody.querySelectorAll('tr').forEach(tr => {
      const rol = (tr.getAttribute('data-rol') || '').toLowerCase();
      tr.style.display = !val || rol === val ? '' : 'none';
    });
  });

});

// ======================
// MODAL EDITAR USUARIO
// ======================
const modalEdit   = document.getElementById("modalEditUser");
const btnCancel   = document.getElementById("btnCancelEdit");
const btnSave     = document.getElementById("btnSaveUser");

const inputUser   = document.getElementById("editUserName");
const inputRole   = document.getElementById("editUserRole");

// Abrir modal
document.querySelectorAll(".admin-edit-btn").forEach(btn => {
    btn.addEventListener("click", () => {

        const user = btn.dataset.user;
        const role = btn.dataset.role;

        inputUser.value = user;

        // Preseleccionar el rol
        inputRole.value = role;

        modalEdit.classList.remove("hidden");
    });
});

// Cerrar modal
btnCancel.addEventListener("click", () => {
    modalEdit.classList.add("hidden");
});

// Guardar (demo)
btnSave.addEventListener("click", () => {
    alert("Cambios guardados (maquetado). Aquí se conectará al backend.");
    modalEdit.classList.add("hidden");
});

// Cerrar al hacer click fuera
modalEdit.addEventListener("click", e => {
    if (e.target === modalEdit) modalEdit.classList.add("hidden");
});

</script>
@endsection
