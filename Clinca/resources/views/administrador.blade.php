@extends('layouts.app')
@section('title','Panel del Administrador')

@section('content')
<main class="dashboard">
  <h2>Panel del Administrador</h2>

  {{-- Atajos en tarjetas --}}
  <div class="card-container">
    <a class="card" href="#usuarios"   onclick="showSec('usuarios')">Usuarios y roles</a>
    <a class="card" href="#respaldos"  onclick="showSec('respaldos')">Respaldos</a>
    <a class="card" href="#accesos"    onclick="showSec('accesos')">Control de accesos</a>
    <a class="card" href="#reportes"   onclick="showSec('reportes')">Reportes</a>
    <a class="card" href="#bitacora"   onclick="showSec('bitacora')">Bitácora</a>
  </div>

  {{-- ====================== USUARIOS & ROLES (HU-04) ====================== --}}
  <section id="sec-usuarios" class="panel" style="margin-top:18px;">
    <h3 style="margin-top:0;">Usuarios y roles</h3>

    <div class="btn-container" style="margin-bottom:10px;">
      <button class="confirm-btn" onclick="openUserForm()">Nuevo usuario</button>
      <button class="cancel-btn" onclick="seedDemo()">Cargar demo</button>
    </div>

    <div class="table-container">
      <table>
        <thead>
          <tr>
            <th>Nombre</th>
            <th>Correo</th>
            <th>Rol</th>
            <th>Estado</th>
            <th style="width:160px;">Acciones</th>
          </tr>
        </thead>
        <tbody id="usersBody">
          <tr><td colspan="5" class="muted" style="text-align:center;">Sin usuarios.</td></tr>
        </tbody>
      </table>
    </div>

    {{-- Modal sencillo (sin librería) --}}
    <dialog id="userModal" style="border:none;border-radius:16px;padding:0;max-width:520px;width:100%;">
      <form id="userForm" method="dialog" class="form-container" style="box-shadow:none;margin:0;max-width:none;">
        <h3 style="margin:0 0 8px;">@<span id="formMode">Crear</span> usuario</h3>
        <input type="hidden" id="uId">
        <label>Nombre</label>
        <input id="uName" required placeholder="Ej. Ana Pérez">
        <label>Correo</label>
        <input id="uEmail" type="email" required placeholder="ana@dominio.com">
        <label>Rol</label>
        <select id="uRole" required>
          <option value="doctor">Médico</option>
          <option value="nurse">Enfermera</option>
          <option value="receptionist">Recepcionista</option>
          <option value="admin">Administrador</option>
        </select>
        <label>Estado</label>
        <select id="uStatus" required>
          <option value="activo">Activo</option>
          <option value="inactivo">Inactivo</option>
        </select>
        <div class="btn-container">
          <button class="confirm-btn" type="submit">Guardar</button>
          <button class="cancel-btn" type="button" onclick="closeUserForm()">Cancelar</button>
        </div>
      </form>
    </dialog>
  </section>

  {{-- ====================== RESPALDOS (HU-08) ====================== --}}
  <section id="sec-respaldos" class="panel" style="margin-top:18px; display:none;">
    <h3 style="margin-top:0;">Respaldos de la base de datos</h3>

    <div class="btn-container" style="margin-bottom:10px;">
      <button class="confirm-btn" onclick="runBackup()">Generar respaldo ahora</button>
      <button class="cancel-btn"  onclick="scheduleBackup()">Programar respaldo diario</button>
    </div>

    <div class="table-container">
      <table>
        <thead>
          <tr><th>Fecha</th><th>Tamaño</th><th>Archivo</th><th>Acciones</th></tr>
        </thead>
        <tbody id="bkBody">
          <tr><td colspan="4" class="muted" style="text-align:center;">Sin respaldos aún.</td></tr>
        </tbody>
      </table>
    </div>
  </section>

  {{-- ====================== ACCESOS (HU-14) ====================== --}}
  <section id="sec-accesos" class="panel" style="margin-top:18px; display:none;">
    <h3 style="margin-top:0;">Control de accesos por rol</h3>
    <p class="muted" style="margin-top:-6px;">Matriz de permisos (solo demo visual).</p>

    <div class="table-container">
      <table>
        <thead>
          <tr>
            <th>Módulo</th>
            <th>Admin</th>
            <th>Médico</th>
            <th>Enfermera</th>
            <th>Recepción</th>
            <th>Paciente</th>
          </tr>
        </thead>
        <tbody id="aclBody">
          <!-- se llena por JS -->
        </tbody>
      </table>
    </div>
  </section>

  {{-- ====================== REPORTES (HU-11) ====================== --}}
  <section id="sec-reportes" class="panel" style="margin-top:18px; display:none;">
    <h3 style="margin-top:0;">Reportes</h3>

    <form class="form-container" style="max-width:1000px;margin:0 0 14px;">
      <div class="filtros-grid" style="display:grid;gap:12px;grid-template-columns:1fr 1fr 1fr 1fr;">
        <div>
          <label>Tipo</label>
          <select id="repTipo">
            <option value="citas">Citas</option>
            <option value="pacientes">Pacientes</option>
            <option value="documentos">Documentos</option>
            <option value="tratamientos">Tratamientos</option>
          </select>
        </div>
        <div>
          <label>Desde</label>
          <input id="repDesde" type="date">
        </div>
        <div>
          <label>Hasta</label>
          <input id="repHasta" type="date">
        </div>
        <div>
          <label>Buscar</label>
          <input id="repQ" placeholder="Texto / nombre / CURP">
        </div>
      </div>
      <div class="btn-container acciones">
        <button class="confirm-btn" type="button" onclick="runReport()">Generar</button>
        <button class="cancel-btn"  type="reset" onclick="clearReport()">Limpiar</button>
      </div>
    </form>

    <div class="table-container">
      <table>
        <thead id="repHead">
          <tr><th>Resultado</th></tr>
        </thead>
        <tbody id="repBody">
          <tr><td class="muted">Sin datos.</td></tr>
        </tbody>
      </table>
    </div>
  </section>

  {{-- ====================== BITÁCORA ====================== --}}
  <section id="sec-bitacora" class="panel" style="margin-top:18px; display:none;">
    <h3 style="margin-top:0;">Bitácora</h3>
    <div class="table-container">
      <table>
        <thead>
          <tr><th>Fecha/Hora</th><th>Usuario</th><th>Acción</th><th>Detalle</th></tr>
        </thead>
        <tbody id="logBody">
          <tr><td colspan="4" class="muted" style="text-align:center;">Sin registros.</td></tr>
        </tbody>
      </table>
    </div>
  </section>
</main>

{{-- ====================== JS DEMO ====================== --}}
<script>
  // Navegación por secciones
  const sections = ['usuarios','respaldos','accesos','reportes','bitacora'];
  function showSec(id){
    sections.forEach(s => {
      const el = document.getElementById('sec-'+s);
      if (!el) return;
      el.style.display = (s===id) ? 'block' : 'none';
    });
    // scroll suave
    const target = document.getElementById('sec-'+id);
    if (target) target.scrollIntoView({behavior:'smooth', block:'start'});
  }

  // ===== Usuarios (demo) =====
  let users = [];
  const usersBody = document.getElementById('usersBody');
  const userModal = document.getElementById('userModal');
  const formMode  = document.getElementById('formMode');
  const uId = document.getElementById('uId');
  const uName = document.getElementById('uName');
  const uEmail = document.getElementById('uEmail');
  const uRole = document.getElementById('uRole');
  const uStatus = document.getElementById('uStatus');

  function renderUsers(){
    usersBody.innerHTML = '';
    if (!users.length){
      usersBody.innerHTML = `<tr><td colspan="5" class="muted" style="text-align:center;">Sin usuarios.</td></tr>`;
      return;
    }
    users.forEach((u,i)=>{
      const tr = document.createElement('tr');
      tr.innerHTML = `
        <td>${u.name}</td>
        <td>${u.email}</td>
        <td>${roleLabel(u.role)}</td>
        <td>${u.status}</td>
        <td>
          <button class="btn-secondary" onclick="editUser(${i})">Editar</button>
          <button class="btn-secondary" onclick="toggleUser(${i})">${u.status==='activo'?'Desactivar':'Activar'}</button>
        </td>`;
      usersBody.appendChild(tr);
    });
  }
  function roleLabel(r){
    return ({
      admin:'Administrador',
      doctor:'Médico',
      nurse:'Enfermera',
      receptionist:'Recepcionista'
    })[r] || r;
  }
  function openUserForm(){
    formMode.textContent = 'Crear';
    uId.value = '';
    uName.value = '';
    uEmail.value = '';
    uRole.value = 'doctor';
    uStatus.value = 'activo';
    userModal.showModal();
  }
  function closeUserForm(){ userModal.close(); }
  function editUser(idx){
    const u = users[idx];
    formMode.textContent = 'Editar';
    uId.value = idx;
    uName.value = u.name;
    uEmail.value = u.email;
    uRole.value = u.role;
    uStatus.value = u.status;
    userModal.showModal();
  }
  document.getElementById('userForm').addEventListener('submit',(e)=>{
    e.preventDefault();
    const data = { name:uName.value.trim(), email:uEmail.value.trim(), role:uRole.value, status:uStatus.value };
    if (!data.name || !data.email){ alert('Nombre y correo son obligatorios'); return; }
    if (uId.value===''){ users.push(data); } else { users[+uId.value] = data; }
    renderUsers();
    userModal.close();
  });
  function toggleUser(i){
    users[i].status = users[i].status==='activo' ? 'inactivo' : 'activo';
    renderUsers();
  }
  function seedDemo(){
    users = [
      {name:'Dra. López', email:'d.lopez@clinica.com', role:'doctor', status:'activo'},
      {name:'Enf. Sofía', email:'sofia@clinica.com', role:'nurse', status:'activo'},
      {name:'Recep. Hugo', email:'recep@clinica.com', role:'receptionist', status:'activo'},
      {name:'Admin', email:'admin@clinica.com', role:'admin', status:'activo'}
    ];
    renderUsers();
  }

  // ===== Respaldos (demo) =====
  const bkBody = document.getElementById('bkBody');
  function renderBackups(list){
    bkBody.innerHTML = '';
    if (!list.length){
      bkBody.innerHTML = `<tr><td colspan="4" class="muted" style="text-align:center;">Sin respaldos aún.</td></tr>`;
      return;
    }
    list.forEach(b=>{
      const tr = document.createElement('tr');
      tr.innerHTML = `<td>${b.fecha}</td><td>${b.size}</td><td>${b.file}</td>
        <td><button class="btn-secondary" onclick="alert('Descargar ${b.file} (demo)')">Descargar</button></td>`;
      bkBody.appendChild(tr);
    });
  }
  let backups = [];
  function runBackup(){
    const now = new Date();
    const name = `backup_${now.toISOString().slice(0,19).replaceAll(':','-')}.zip`;
    backups.unshift({fecha:now.toLocaleString(), size:'12.4 MB', file:name});
    renderBackups(backups);
    alert('✅ Respaldo generado (demo).');
  }
  function scheduleBackup(){ alert('🗓️ Respaldo diario programado (demo).'); }

  // ===== Accesos (demo) =====
  const aclBody = document.getElementById('aclBody');
  const aclMatrix = [
    {mod:'Historial médico',     admin:1, doctor:1, nurse:0, receptionist:0, patient:1},
    {mod:'Signos vitales',       admin:1, doctor:1, nurse:1, receptionist:0, patient:0},
    {mod:'Tratamientos',         admin:1, doctor:1, nurse:1, receptionist:0, patient:0},
    {mod:'Documentos',           admin:1, doctor:1, nurse:0, receptionist:0, patient:0},
    {mod:'Agenda/Citas',         admin:1, doctor:0, nurse:0, receptionist:1, patient:1},
    {mod:'Reportes',             admin:1, doctor:0, nurse:0, receptionist:0, patient:0},
  ];
  function renderACL(){
    aclBody.innerHTML = '';
    aclMatrix.forEach(r=>{
      const tr = document.createElement('tr');
      tr.innerHTML = `
        <td>${r.mod}</td>
        <td>${r.admin?'✔️':'—'}</td>
        <td>${r.doctor?'✔️':'—'}</td>
        <td>${r.nurse?'✔️':'—'}</td>
        <td>${r.receptionist?'✔️':'—'}</td>
        <td>${r.patient?'✔️':'—'}</td>`;
      aclBody.appendChild(tr);
    });
  }

  // ===== Reportes (demo) =====
  const repHead = document.getElementById('repHead');
  const repBody = document.getElementById('repBody');
  function runReport(){
    const tipo = document.getElementById('repTipo').value;
    // cabecera
    let head = '';
    if (tipo==='citas')       head = '<tr><th>Fecha</th><th>Hora</th><th>Paciente</th><th>Doctor</th><th>Estado</th></tr>';
    if (tipo==='pacientes')   head = '<tr><th>Nombre</th><th>Edad</th><th>CURP</th><th>Última visita</th></tr>';
    if (tipo==='documentos')  head = '<tr><th>Fecha</th><th>Título</th><th>Tipo</th><th>Paciente</th></tr>';
    if (tipo==='tratamientos')head = '<tr><th>Fecha</th><th>Paciente</th><th>Actual → Nuevo</th><th>Médico</th></tr>';
    repHead.innerHTML = head;

    // datos demo
    let rows = [];
    if (tipo==='citas'){
      rows = [
        ['2025-11-10','09:00','Ana Pérez','Dr. Hernández','Programada'],
        ['2025-11-10','10:30','Luis Mora','Dra. López','Programada']
      ];
    } else if (tipo==='pacientes'){
      rows = [
        ['Paciente DEMO','32','GAXX900101HDF','2025-11-08'],
        ['Juan Torres','45','TOJJ800202MDF','2025-11-01']
      ];
    } else if (tipo==='documentos'){
      rows = [
        ['2025-11-08','Radiografía de tórax','PDF','Paciente DEMO'],
        ['2025-11-07','Análisis sangre','Imagen','Luis Mora']
      ];
    } else {
      rows = [
        ['2025-11-08','Paciente DEMO','Amoxicilina → Azitromicina','Dr. Hernández'],
        ['2025-11-05','Ana Pérez','Ibuprofeno → Naproxeno','Dra. López']
      ];
    }

    repBody.innerHTML = '';
    rows.forEach(r=>{
      const tr = document.createElement('tr');
      tr.innerHTML = r.map(c=>`<td>${c}</td>`).join('');
      repBody.appendChild(tr);
    });
  }
  function clearReport(){
    repHead.innerHTML = '<tr><th>Resultado</th></tr>';
    repBody.innerHTML = '<tr><td class="muted">Sin datos.</td></tr>';
  }

  // ===== Bitácora (demo) =====
  const logBody = document.getElementById('logBody');
  function seedLog(){
    const now = new Date().toLocaleString();
    const rows = [
      [now,'Admin','Login','Ingreso al panel'],
      [now,'Admin','Respaldo','Generó backup manual'],
      [now,'Admin','ACL','Actualizó permisos (demo)']
    ];
    logBody.innerHTML = '';
    rows.forEach(r=>{
      const tr = document.createElement('tr');
      tr.innerHTML = r.map(c=>`<td>${c}</td>`).join('');
      logBody.appendChild(tr);
    });
  }

  // Init
  showSec('usuarios');
  renderUsers();
  renderBackups(backups);
  renderACL();
  seedLog();
})();
</script>
@endsection
