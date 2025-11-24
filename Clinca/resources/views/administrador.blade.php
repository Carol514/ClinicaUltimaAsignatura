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
      <p class="admin-stat-number" id="statPacientes">...</p>
      <p class="admin-stat-caption">En toda la clínica</p>
    </div>

    <div class="admin-card admin-card--stat">
      <p class="admin-stat-label">Citas programadas hoy</p>
      <p class="admin-stat-number" id="statCitasHoyAdmin">...</p>
      <p class="admin-stat-caption">Incluye programadas, confirmadas y atendidas</p>
    </div>

    <div class="admin-card admin-card--stat">
      <p class="admin-stat-label">Personal activo</p>
      <p class="admin-stat-number" id="statPersonal">...</p>
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
          {{-- Loaded dynamically from API --}}
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
        Reportes enfocados en <strong>pacientes atendidos</strong>.
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
            Selecciona un tipo de reporte y haz clic en "Generar".
          </p>
        </div>
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
            Generar un respaldo manual de la base de datos.
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
        Gestión de usuarios del sistema.
      </p>

      {{-- Filtro por rol --}}
      <div class="admin-filter-row">
        <div class="admin-filter-left">
          <span>Filtrar por rol:</span>
          <select id="filterRol">
            <option value="">Todos</option>
            <option value="Administrador">Administrador</option>
            <option value="Médico">Médico</option>
            <option value="Enfermera">Enfermera</option>
            <option value="Recepcionista">Recepcionista</option>
          </select>
        </div>

        <button type="button"
                class="confirm-btn admin-inline-btn"
                id="btnOpenNewUser">
          <img src="/img/agregar.png" class="btn-icon" alt="Agregar" style="width:24px; height:24px;">
          <span>Agregar usuario</span>
        </button>
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
            {{-- Loaded dynamically from API --}}
          </tbody>
        </table>
      </div>

      <p class="muted" style="font-size:13px;margin-top:8px;">
        Gestión de usuarios y roles del sistema.
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

<!-- =======================
     MODAL NUEVO USUARIO
======================= -->
<div id="modalNewUser" class="modal hidden">
  <div class="modal-content modal-sm">

    <h3 class="modal-title">Nuevo usuario</h3>

    <div class="modal-body">
      <div class="field">
        <label for="newUserName">Nombre</label>
        <input id="newUserName"
               type="text"
               class="modal-input"
               placeholder="Nombre del usuario">
      </div>

      <div class="field">
        <label for="newUserEmail">Correo electrónico</label>
        <input id="newUserEmail"
               type="email"
               class="modal-input"
               placeholder="correo@ejemplo.com">
      </div>

      <div class="field">
        <label for="newUserRole">Rol</label>
        <select id="newUserRole" class="modal-input">
          <option value="">Seleccione un rol...</option>
          <option value="Administrador">Administrador</option>
          <option value="Médico">Médico</option>
          <option value="Enfermera">Enfermera</option>
          <option value="Recepcionista">Recepcionista</option>
        </select>
      </div>

      <div class="field">
        <label for="newUserPass">Contraseña</label>
        <input id="newUserPass"
               type="text"
               class="modal-input"
               placeholder="ContraseñaUsuario123">
      </div>
    </div>

    <div class="btn-container" style="margin-top:20px;">
      <button id="btnSaveNewUser" class="confirm-btn">
        <img src="/img/guardar.png" class="btn-icon" alt="Guardar" style="width:28px; height:28px;">
      </button>

      <button id="btnCancelNew" class="cancel-btn">
        <img src="/img/cancelar.png" class="btn-icon" alt="Cancelar" style="width:20px; height:20px;">
      </button>
    </div>

  </div>
</div>

  <!-- ====== ALERTA GLOBAL ====== -->
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

  <!-- ====== CONFIRM GLOBAL ====== -->
  <div id="appConfirmOverlay" class="app-alert-overlay app-alert-hidden">
    <div class="app-alert app-confirm">
      <div class="app-alert-top"></div>

      <div class="app-alert-card">
        <div class="app-alert-icon-wrapper">
          <img src="/img/templogo.jpg" alt="OK" class="app-alert-icon">
        </div>

        <p id="appConfirmText" class="app-alert-text">
          Texto de confirmación
        </p>

        <div class="app-confirm-buttons">
          <button id="appConfirmCancel" class="cancel-btn app-alert-btn">
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

{{-- ================= JS WITH BACKEND INTEGRATION ================= --}}
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

  wrapper.classList.remove('app-alert--success', 'app-alert--error', 'app-alert--warning');
  if (type === 'error') {
    wrapper.classList.add('app-alert--error');
  } else if (type === 'warning') {
    wrapper.classList.add('app-alert--warning');
  } else {
    wrapper.classList.add('app-alert--success');
  }

  overlay.classList.remove('app-alert-hidden');

  const closeBtn = document.getElementById('appAlertClose');

  function close() {
    overlay.classList.add('app-alert-hidden');
    overlay.removeEventListener('click', outsideHandler);
    if (closeBtn) closeBtn.removeEventListener('click', close);
  }

  function outsideHandler(e) {
    if (e.target === overlay) close();
  }

  if (closeBtn) closeBtn.addEventListener('click', close);
  overlay.addEventListener('click', outsideHandler);
}

// ====== CONFIRM GLOBAL (warning) ======
function confirmApp(message, callback) {
  const overlay   = document.getElementById('appConfirmOverlay');
  const textEl    = document.getElementById('appConfirmText');
  const okBtn     = document.getElementById('appConfirmOK');
  const cancelBtn = document.getElementById('appConfirmCancel');
  const wrapper   = overlay?.querySelector('.app-alert');

  if (!overlay || !textEl || !okBtn || !cancelBtn || !wrapper) {
    // Si algo falla, usar confirm normal
    const res = confirm(message);
    callback(res);
    return;
  }

  // estilo warning (amarillo)
  wrapper.classList.remove('app-alert--success', 'app-alert--error');
  wrapper.classList.add('app-alert--warning');

  textEl.textContent = message;
  overlay.classList.remove('app-alert-hidden');

  function clean() {
    okBtn.removeEventListener('click', okHandler);
    cancelBtn.removeEventListener('click', cancelHandler);
    overlay.removeEventListener('click', outsideHandler);
  }

  function okHandler() {
    overlay.classList.add('app-alert-hidden');
    clean();
    callback(true);
  }

  function cancelHandler() {
    overlay.classList.add('app-alert-hidden');
    clean();
    callback(false);
  }

  function outsideHandler(e) {
    if (e.target === overlay) cancelHandler();
  }

  okBtn.addEventListener('click', okHandler);
  cancelBtn.addEventListener('click', cancelHandler);
  overlay.addEventListener('click', outsideHandler);
}

document.addEventListener('DOMContentLoaded', async () => {

  // ----------- Load Dashboard Stats -----------
  async function loadDashboardStats() {
    try {
      const response = await fetch('/administrador/api/stats', {
        headers: { 'X-CSRF-TOKEN': '{{ csrf_token() }}' }
      });
      const data = await response.json();
      
      document.getElementById('statPacientes').textContent = data.pacientes || 0;
      document.getElementById('statCitasHoyAdmin').textContent = data.citas_hoy || 0;
      document.getElementById('statPersonal').textContent = data.personal || 0;
    } catch (err) {
      console.error('Error loading dashboard stats:', err);
    }
  }

  // ----------- Load Pie Chart (Appointments by Status) -----------
  async function loadPieChart() {
    try {
      const response = await fetch('/administrador/api/appointments/by-status', {
        headers: { 'X-CSRF-TOKEN': '{{ csrf_token() }}' }
      });
      const result = await response.json();
      const data = result.data || result;
      
      const legendList = document.querySelector('.admin-legend');
      legendList.innerHTML = '';
      
      const colors = {
        'programada': '#5bc0de',
        'confirmada': '#007bff',
        'atendida': '#28a745',
        'no_asistio': '#ffc107',
      };
      
      const pieDiv = document.querySelector('.admin-pie');
      
      if (Array.isArray(data) && data.length > 0) {
        data.forEach(item => {
          const color = colors[item.label] || colors[item.status] || '#6c757d';
          const li = document.createElement('li');
          li.innerHTML = `
            <span class="legend-dot" style="background:${color};"></span>
            <span class="legend-label">${item.label}</span>
            <span class="legend-value">${item.count} (${item.percentage}%)</span>
          `;
          legendList.appendChild(li);
        });
        
        let gradientStops = [];
        let cumulative = 0;
        data.forEach(item => {
          const color = colors[item.label] || colors[item.status] || '#6c757d';
          const percent = parseFloat(item.percentage);
          gradientStops.push(`${color} ${cumulative}% ${cumulative + percent}%`);
          cumulative += percent;
        });
        
        const gradient = `conic-gradient(${gradientStops.join(', ')})`;
        pieDiv.style.setProperty('background', gradient, 'important');
      } else {
        legendList.innerHTML = '<li style="list-style:none;color:#6c757d;text-align:center;">No hay datos de citas disponibles</li>';
        pieDiv.style.setProperty('background', '#e8f6f0', 'important');
      }
    } catch (err) {
      console.error('Error loading pie chart:', err);
    }
  }

  // ----------- Load Line Chart (Last 7 Days) -----------
  async function loadLineChart() {
    try {
      const response = await fetch('/administrador/api/appointments/last-7-days', {
        headers: { 'X-CSRF-TOKEN': '{{ csrf_token() }}' }
      });
      const lineData = await response.json();
      
      const lineChart = document.getElementById('adminLineChart');
      lineChart.innerHTML = '';
      
      if (!Array.isArray(lineData) || lineData.length === 0) {
        lineChart.innerHTML = '<p style="text-align:center;color:#6c757d;margin:20px 0;">No hay datos disponibles</p>';
        return;
      }
      
      const maxVal = Math.max(...lineData.map(d => d.value)) || 1;
      const maxHeight = 150;

      lineData.forEach(d => {
        const bar = document.createElement('div');
        bar.className = 'line-bar';
        const heightPx = d.value > 0 ? Math.round((d.value / maxVal) * maxHeight) : 3;
        const opacity = d.value === 0 ? '0.3' : '1';
        bar.innerHTML = `
          <div class="line-bar-inner" style="height: ${heightPx}px; opacity: ${opacity};"></div>
          <span class="line-value">${d.value}</span>
          <span class="line-label">${d.label}</span>
        `;
        lineChart.appendChild(bar);
      });
    } catch (err) {
      console.error('Error loading line chart:', err);
    }
  }

  await Promise.all([
    loadDashboardStats(),
    loadPieChart(),
    loadLineChart()
  ]);

  // ----------- Botón de respaldo -----------
  const btnBackup  = document.getElementById('btnBackupDemo');
  btnBackup.addEventListener('click', async () => {
    const originalText = btnBackup.querySelector('span').textContent;
    btnBackup.disabled = true;
    btnBackup.querySelector('span').textContent = 'Generando...';
    
    try {
      const response = await fetch('/administrador/api/backups', {
        method: 'POST',
        headers: {
          'Content-Type': 'application/json',
          'X-CSRF-TOKEN': '{{ csrf_token() }}'
        },
        body: JSON.stringify({ format: 'xlsx' })
      });
      const data = await response.json();
      
      btnBackup.disabled = false;
      btnBackup.querySelector('span').textContent = originalText;
      
      const tablesCount = data.tables_count || 0;
      showAppAlert(`Respaldo completado exitosamente. ${tablesCount} tablas respaldadas.`, 'success');
      
      if (data.download) {
        window.location.href = data.download;
      }
    } catch (err) {
      console.error('Error generating backup:', err);
      btnBackup.disabled = false;
      btnBackup.querySelector('span').textContent = originalText;
      showAppAlert('Error al generar el respaldo.', 'error');
    }
  });

  // ----------- Reportes (tabla) -----------
  const btnReporte = document.getElementById('btnGenerarReporteDemo');
  const selTipoRep = document.getElementById('reporteTipo');
  const repHead  = document.getElementById('adminReportHead');
  const repBody  = document.getElementById('adminReportBody');
  const repEmpty = document.getElementById('adminReportEmpty');

  btnReporte.addEventListener('click', async () => {
    const tipo = selTipoRep.value;
    if (!tipo) {
      showAppAlert('Selecciona un tipo de reporte.', 'error');
      return;
    }

    try {
      const response = await fetch('/administrador/api/reports', {
        method: 'POST',
        headers: {
          'Content-Type': 'application/json',
          'X-CSRF-TOKEN': '{{ csrf_token() }}'
        },
        body: JSON.stringify({ report_type: tipo })
      });
      const data = await response.json();
      
      if (data.headers && data.rows) {
        repHead.innerHTML = `<tr>${data.headers.map(h => `<th>${h}</th>`).join('')}</tr>`;
        repBody.innerHTML = data.rows
          .map(row => `<tr>${row.map(col => `<td>${col}</td>`).join('')}</tr>`)
          .join('');
        repEmpty.style.display = 'none';
      } else {
        repHead.innerHTML = '';
        repBody.innerHTML = '';
        repEmpty.style.display = 'block';
      }

      showAppAlert('Reporte generado con éxito.', 'success');
    } catch (err) {
      console.error('Error generating report:', err);
      showAppAlert('Error al generar el reporte.', 'error');
    }
  });

  // ----------- Load Users Table -----------
  async function loadUsersTable() {
    try {
      const response = await fetch('/administrador/api/users', {
        headers: { 'X-CSRF-TOKEN': '{{ csrf_token() }}' }
      });
      const users = await response.json();
      
      const rolesBody = document.getElementById('adminRolesBody');
      rolesBody.innerHTML = '';
      
      users.forEach(user => {
        const tr = document.createElement('tr');
        tr.setAttribute('data-rol', user.role_name);
        tr.innerHTML = `
          <td>${user.name}</td>
          <td>${user.role_name}</td>
          <td class="admin-actions">
            <button type="button"
                    class="confirm-btn admin-inline-btn admin-edit-btn"
                    data-user-id="${user.id}"
                    data-user="${user.name}"
                    data-role="${user.role_name}"
                    style="background-color: orange;"
                    onmouseover="this.style.backgroundColor='darkorange'"
                    onmouseout="this.style.backgroundColor='orange'">
              <img src="/img/editar.png" class="btn-icon" alt="Editar" style="width:24px; height:24px;">
            </button>

            <button type="button"
                    class="cancel-btn admin-inline-btn admin-delete-btn"
                    data-user-id="${user.id}"
                    data-user="${user.name}"
                    style="background-color:#e74c3c;"
                    onmouseover="this.style.backgroundColor='#c0392b'"
                    onmouseout="this.style.backgroundColor='#e74c3c'">
              <img src="/img/cancelar.png" class="btn-icon" alt="Eliminar" style="width:24px; height:24px;">
            </button>
          </td>
        `;
        rolesBody.appendChild(tr);
      });
      
      attachUserEventListeners();
    } catch (err) {
      console.error('Error loading users:', err);
    }
  }

  await loadUsersTable();

  // ----------- Filtro por rol en la tabla -----------
  const filterRol = document.getElementById('filterRol');
  filterRol.addEventListener('change', () => {
    const val = filterRol.value.toLowerCase();
    const rolesBody = document.getElementById('adminRolesBody');
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

let currentUserId = null;

function attachUserEventListeners() {
  // Edit buttons
  document.querySelectorAll(".admin-edit-btn").forEach(btn => {
    btn.addEventListener("click", () => {
      currentUserId = btn.dataset.userId;
      const user = btn.dataset.user;
      const role = btn.dataset.role;

      inputUser.value = user;
      inputRole.value = role;

      modalEdit.classList.remove("hidden");
    });
  });

  // Delete buttons
  document.querySelectorAll(".admin-delete-btn").forEach(btn => {
    btn.addEventListener("click", () => {
      const userId = btn.dataset.userId;
      const userName = btn.dataset.user;
      
      confirmApp(`¿Está seguro de eliminar al usuario "${userName}"?`, async (accepted) => {
        if (!accepted) return;

        try {
          const response = await fetch(`/administrador/api/users/${userId}`, {
            method: 'DELETE',
            headers: {
              'X-CSRF-TOKEN': '{{ csrf_token() }}'
            }
          });
          const data = await response.json();
          showAppAlert(data.message || 'Usuario eliminado exitosamente.', 'success');
          
          await loadUsersTable();
        } catch (err) {
          console.error('Error deleting user:', err);
          showAppAlert('Error al eliminar el usuario.', 'error');
        }
      });
    });
  });
}

// Cerrar modal
btnCancel.addEventListener("click", () => {
    modalEdit.classList.add("hidden");
});

// Guardar cambios
btnSave.addEventListener("click", async () => {
    if (!currentUserId) return;
    
    try {
      const response = await fetch(`/administrador/api/users/${currentUserId}/role`, {
        method: 'PUT',
        headers: {
          'Content-Type': 'application/json',
          'X-CSRF-TOKEN': '{{ csrf_token() }}'
        },
        body: JSON.stringify({ role_name: inputRole.value })
      });
      const data = await response.json();
      showAppAlert(data.message || 'Rol actualizado exitosamente.', 'success');
      
      modalEdit.classList.add("hidden");
      
      await loadUsersTable();
    } catch (err) {
      console.error('Error updating user role:', err);
      showAppAlert('Error al actualizar el rol.', 'error');
    }
});

// Cerrar al hacer click fuera
modalEdit.addEventListener("click", e => {
    if (e.target === modalEdit) modalEdit.classList.add("hidden");
});

// ======================
// MODAL NUEVO USUARIO
// ======================
const modalNewUser   = document.getElementById("modalNewUser");
const btnOpenNewUser = document.getElementById("btnOpenNewUser");
const btnCancelNew   = document.getElementById("btnCancelNew");
const btnSaveNewUser = document.getElementById("btnSaveNewUser");
const inputNewName   = document.getElementById("newUserName");
const inputNewEmail  = document.getElementById("newUserEmail");
const inputNewRole   = document.getElementById("newUserRole");
const inputNewPass   = document.getElementById("newUserPass");

// Abrir modal "Nuevo usuario"
if (btnOpenNewUser) {
  btnOpenNewUser.addEventListener("click", () => {
    inputNewName.value  = "";
    inputNewEmail.value = "";
    inputNewRole.value  = "";
    inputNewPass.value  = "";

    modalNewUser.classList.remove("hidden");
  });
}

// Cerrar modal
if (btnCancelNew) {
  btnCancelNew.addEventListener("click", () => {
    modalNewUser.classList.add("hidden");
  });
}

// Guardar nuevo usuario
if (btnSaveNewUser) {
  btnSaveNewUser.addEventListener("click", async () => {
    const name = inputNewName.value.trim();
    const email = inputNewEmail.value.trim();
    const role = inputNewRole.value;
    const password = inputNewPass.value;

    if (!name || !email || !role || !password) {
      showAppAlert('Por favor complete todos los campos.', 'error');
      return;
    }

    try {
      const response = await fetch('/administrador/api/users', {
        method: 'POST',
        headers: {
          'Content-Type': 'application/json',
          'X-CSRF-TOKEN': '{{ csrf_token() }}'
        },
        body: JSON.stringify({ name, email, role_name: role, password })
      });
      const data = await response.json();
      showAppAlert(data.message || 'Usuario creado exitosamente.', 'success');
      
      modalNewUser.classList.add("hidden");
      
      await loadUsersTable();
    } catch (err) {
      console.error('Error creating user:', err);
      showAppAlert('Error al crear el usuario.', 'error');
    }
  });
}

// Cerrar haciendo click fuera del contenido
if (modalNewUser) {
  modalNewUser.addEventListener("click", (e) => {
    if (e.target === modalNewUser) {
      modalNewUser.classList.add("hidden");
    }
  });
}

// Make loadUsersTable available globally for event handlers
window.loadUsersTable = async function() {
  try {
    const response = await fetch('/administrador/api/users', {
      headers: { 'X-CSRF-TOKEN': '{{ csrf_token() }}' }
    });
    const users = await response.json();
    
    const rolesBody = document.getElementById('adminRolesBody');
    rolesBody.innerHTML = '';
    
    users.forEach(user => {
      const tr = document.createElement('tr');
      tr.setAttribute('data-rol', user.role_name);
      tr.innerHTML = `
        <td>${user.name}</td>
        <td>${user.role_name}</td>
        <td class="admin-actions">
          <button type="button"
                  class="confirm-btn admin-inline-btn admin-edit-btn"
                  data-user-id="${user.id}"
                  data-user="${user.name}"
                  data-role="${user.role_name}"
                  style="background-color: orange;"
                  onmouseover="this.style.backgroundColor='darkorange'"
                  onmouseout="this.style.backgroundColor='orange'">
            <img src="/img/editar.png" class="btn-icon" alt="Editar" style="width:24px; height:24px;">
          </button>

          <button type="button"
                  class="cancel-btn admin-inline-btn admin-delete-btn"
                  data-user-id="${user.id}"
                  data-user="${user.name}"
                  style="background-color:#e74c3c;"
                  onmouseover="this.style.backgroundColor='#c0392b'"
                  onmouseout="this.style.backgroundColor='#e74c3c'">
            <img src="/img/cancelar.png" class="btn-icon" alt="Eliminar" style="width:24px; height:24px;">
          </button>
        </td>
      `;
      rolesBody.appendChild(tr);
    });
    
    attachUserEventListeners();
  } catch (err) {
    console.error('Error loading users:', err);
  }
};
</script>
@endsection
