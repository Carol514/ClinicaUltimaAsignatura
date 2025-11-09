{{-- resources/views/administrador/usuarios_roles.blade.php --}}
@extends('layouts.app')
@section('title','Usuarios y roles')

@section('content')
<main class="dashboard">
  <h2>Usuarios y roles</h2>

  {{-- ====== Sección: Usuarios ====== --}}
  <section class="panel" style="max-width:1100px;">
    <h3 style="margin-top:0;">Usuarios</h3>

    <div style="text-align:center;margin-bottom:12px;">
      <button class="confirm-btn" id="btnNuevoUsuario">Agregar nuevo usuario</button>
    </div>

    <form class="form-container" onsubmit="return false;">
      <div class="fields" style="display:grid;grid-template-columns:1fr auto;gap:10px;">
        <div>
          <label>Filtrar usuarios por nombre/correo/rol</label>
          <input id="searchUser" placeholder="Ej. Ana, doctor, @hospital.local">
        </div>
        <div class="btn-container" style="align-self:end;">
          <button class="cancel-btn" id="btnResetFiltro" type="button">Restablecer</button>
        </div>
      </div>
    </form>

    <div class="table-container" style="margin-top:12px;">
      <table>
        <thead>
          <tr>
            <th>Usuario</th>
            <th>Correo / ID</th>
            <th>Rol actual</th>
            <th style="width:220px;">Acciones</th>
          </tr>
        </thead>
        <tbody id="usersTbody"></tbody>
      </table>
      <p id="noUsers" class="muted" style="text-align:center;margin-top:10px;display:none;">Sin usuarios.</p>
    </div>
  </section>

  {{-- ====== Sección: Roles y permisos ====== --}}
  <section class="panel" style="max-width:1100px;">
    <h3 style="margin-top:0;">Roles y permisos</h3>

    <div class="table-container" style="margin-top:12px;">
      <table>
        <thead>
          <tr>
            <th>Rol</th>
            <th>Permisos (resumen)</th>
            <th style="width:220px;">Acciones</th>
          </tr>
        </thead>
        <tbody id="rolesTbody"></tbody>
      </table>
      <p id="noRoles" class="muted" style="text-align:center;margin-top:10px;display:none;">Sin roles.</p>
    </div>

    <form class="form-container" onsubmit="return false;" style="margin-top:14px;">
      <div class="fields" style="display:grid;grid-template-columns:1fr auto;gap:10px;">
        <div>
          <label>Nombre de nuevo rol</label>
          <input id="newRoleName" placeholder="Ej. Coordinador">
        </div>
        <div class="btn-container" style="align-self:end;">
          <button class="confirm-btn" id="btnAgregarRol" type="button">Agregar rol</button>
        </div>
      </div>
    </form>
  </section>

  <div style="max-width:1100px;margin:8px auto 40px;text-align:center;">
    <a href="{{ route('admin.panel') }}" class="cancel-btn">Volver al panel</a>
  </div>
</main>

{{-- ====== Modales (estilos mínimos) ====== --}}
<style>
  .modal-backdrop{position:fixed;inset:0;background:rgba(0,0,0,.35);display:none;align-items:center;justify-content:center;z-index:1000;}
  .modal{background:#fff;border-radius:14px;max-width:520px;width:92%;padding:18px;box-shadow:0 10px 30px rgba(0,0,0,.2);}
  .modal h4{margin:0 0 10px;color:#2a6b5f;}
  .perm-chip{display:inline-block;background:#f1fbf7;color:#2a6b5f;border-radius:10px;padding:4px 8px;margin:2px 4px 0 0;font-size:.92em;}
</style>

{{-- Modal: editar permisos --}}
<div class="modal-backdrop" id="permModal">
  <div class="modal">
    <h4>Editar permisos — <span id="modalRoleName"></span></h4>
    <div id="permList" style="display:flex;flex-direction:column;gap:8px;margin:8px 0 4px;"></div>
    <div class="btn-container" style="justify-content:flex-end;margin-top:12px;">
      <button class="cancel-btn" id="permCancel">Cancelar</button>
      <button class="confirm-btn" id="permSave">Guardar</button>
    </div>
  </div>
</div>

{{-- Modal: nuevo usuario --}}
<div class="modal-backdrop" id="userModal">
  <div class="modal">
    <h4>Agregar nuevo usuario</h4>
    <div class="form-container">
      <div class="fields" style="display:grid;grid-template-columns:1fr;gap:10px;">
        <div>
          <label>Nombre</label>
          <input id="userNombre" placeholder="Nombre completo">
        </div>
        <div>
          <label>Correo / ID</label>
          <input id="userCorreo" placeholder="usuario@hospital.local">
        </div>
        <div>
          <label>Rol</label>
          <select id="userRol"></select>
        </div>
      </div>
    </div>
    <div class="btn-container" style="justify-content:flex-end;margin-top:12px;">
      <button class="cancel-btn" id="userCancel">Cancelar</button>
      <button class="confirm-btn" id="userSave">Guardar</button>
    </div>
  </div>
</div>

<script>
(() => {
  // ===== Datos demo =====
  const samplePermissions = [
    { key:'ver_expediente',      label:'Ver expediente' },
    { key:'editar_expediente',   label:'Editar expediente' },
    { key:'ver_signos',          label:'Ver signos vitales' },
    { key:'registrar_signos',    label:'Registrar signos vitales' },
    { key:'gestionar_citas',     label:'Gestionar citas' },
    { key:'generar_reportes',    label:'Generar reportes' },
    { key:'respaldo_bd',         label:'Respaldo de base de datos' },
    { key:'administrar_roles',   label:'Administrar roles / permisos' }
  ];

  let roles = {
    'Administrador': { permissions: Object.fromEntries(samplePermissions.map(p=>[p.key,true])) },
    'Doctor':        { permissions: { ver_expediente:true, editar_expediente:true, ver_signos:true, registrar_signos:false, gestionar_citas:true } },
    'Enfermera':     { permissions: { ver_expediente:true, ver_signos:true, registrar_signos:true } },
    'Recepcionista': { permissions: { gestionar_citas:true } },
    'Paciente':      { permissions: { ver_expediente:true } }
  };

  let users = [
    { nombre:'Juan Pérez',   correo:'juan.perez@hospital.local', rol:'Doctor' },
    { nombre:'Ana López',    correo:'ana.lopez@hospital.local',  rol:'Enfermera' },
    { nombre:'Carlos Ruiz',  correo:'c.ruiz@hospital.local',     rol:'Recepcionista' },
    { nombre:'Lucía García', correo:'lucia.garcia@hospital.local', rol:'Paciente' },
    { nombre:'Roberto Díaz', correo:'roberto@hospital.local',     rol:'Administrador' }
  ];

  // ===== Helpers DOM =====
  const $ = sel => document.querySelector(sel);
  const usersTbody = $('#usersTbody');
  const rolesTbody = $('#rolesTbody');
  const noUsers = $('#noUsers');
  const noRoles = $('#noRoles');

  // ===== Render usuarios =====
  function renderUsers(list = users){
    usersTbody.innerHTML = '';
    if (!list.length){ noUsers.style.display='block'; return; }
    noUsers.style.display='none';
    list.forEach(u=>{
      const tr = document.createElement('tr');
      tr.innerHTML = `
        <td>${u.nombre}</td>
        <td>${u.correo}</td>
        <td>${u.rol}</td>
        <td>
          <button class="confirm-btn" data-action="cambiar" data-id="${u.correo}">Cambiar rol</button>
          <button class="cancel-btn"  data-action="eliminar" data-id="${u.correo}" style="margin-left:8px;">Eliminar</button>
        </td>
      `;
      usersTbody.appendChild(tr);
    });
  }

  // ===== Render roles =====
  function resumenPermisos(perms){
    const activos = Object.keys(perms||{}).filter(k=>perms[k]);
    if (!activos.length) return 'Sin permisos';
    const labels = activos.map(k => (samplePermissions.find(p=>p.key===k)||{}).label || k);
    return labels.slice(0,3).join(', ') + (labels.length>3 ? ` +${labels.length-3} más` : '');
  }

  function renderRoles(){
    rolesTbody.innerHTML='';
    const names = Object.keys(roles);
    if(!names.length){ noRoles.style.display='block'; return; }
    noRoles.style.display='none';
    names.forEach(r=>{
      const tr = document.createElement('tr');
      tr.innerHTML = `
        <td>${r}</td>
        <td><span class="perm-chip">${resumenPermisos(roles[r].permissions)}</span></td>
        <td>
          <button class="confirm-btn" data-action="permisos" data-role="${r}">Editar permisos</button>
          <button class="cancel-btn"  data-action="delrole"  data-role="${r}" style="margin-left:8px;">Eliminar rol</button>
        </td>
      `;
      rolesTbody.appendChild(tr);
    });
  }

  // ===== Buscar / reset =====
  $('#searchUser').addEventListener('input', e=>{
    const q = e.target.value.toLowerCase();
    renderUsers(users.filter(u =>
      u.nombre.toLowerCase().includes(q) ||
      u.correo.toLowerCase().includes(q) ||
      u.rol.toLowerCase().includes(q)
    ));
  });
  $('#btnResetFiltro').onclick = ()=>{ $('#searchUser').value=''; renderUsers(); };

  // ===== Eventos tabla usuarios =====
  usersTbody.addEventListener('click', e=>{
    const btn = e.target.closest('button'); if(!btn) return;
    const id = btn.dataset.id;
    const action = btn.dataset.action;
    if(action==='eliminar'){
      if(!confirm('¿Eliminar este usuario?')) return;
      users = users.filter(u=>u.correo!==id);
      renderUsers();
    }else if(action==='cambiar'){
      const u = users.find(x=>x.correo===id);
      const opciones = Object.keys(roles).join(', ');
      const nuevo = prompt(`Asignar nuevo rol a ${u.nombre}\nOpciones: ${opciones}`, u.rol);
      if(!nuevo || !roles[nuevo]) return alert('Rol inexistente.');
      u.rol = nuevo; renderUsers();
      alert(`Rol actualizado a ${nuevo}.`);
    }
  });

  // ===== Alta de rol =====
  $('#btnAgregarRol').onclick = ()=>{
    const name = $('#newRoleName').value.trim();
    if(!name) return alert('Ingresa un nombre');
    if(roles[name]) return alert('Ese rol ya existe');
    roles[name] = { permissions:{} };
    $('#newRoleName').value='';
    renderRoles(); updateRoleSelect();
  };

  // ===== Modales permisos =====
  const permModal  = $('#permModal');
  const permList   = $('#permList');
  const modalRoleName = $('#modalRoleName');
  let editingRole = null;

  rolesTbody.addEventListener('click', e=>{
    const btn = e.target.closest('button'); if(!btn) return;
    const action = btn.dataset.action;
    const role   = btn.dataset.role;
    if(action==='delrole'){
      if(!confirm('¿Eliminar este rol?')) return;
      delete roles[role];
      renderRoles(); updateRoleSelect();
    }
    if(action==='permisos'){
      editingRole = role;
      modalRoleName.textContent = role;
      permList.innerHTML = samplePermissions.map(p=>{
        const checked = roles[role]?.permissions?.[p.key] ? 'checked' : '';
        return `<label><input type="checkbox" id="perm_${p.key}" ${checked}> ${p.label}</label>`;
      }).join('');
      permModal.style.display='flex';
    }
  });
  $('#permCancel').onclick = ()=> permModal.style.display='none';
  $('#permSave').onclick = ()=>{
    const newPerms = {};
    samplePermissions.forEach(p=>{
      newPerms[p.key] = document.getElementById(`perm_${p.key}`).checked;
    });
    roles[editingRole].permissions = newPerms;
    permModal.style.display='none';
    renderRoles();
  };

  // ===== Modal nuevo usuario =====
  const userModal = $('#userModal');
  function updateRoleSelect(){
    const sel = $('#userRol');
    sel.innerHTML = Object.keys(roles).map(r=>`<option>${r}</option>`).join('');
  }
  $('#btnNuevoUsuario').onclick = ()=>{ updateRoleSelect(); $('#userNombre').value=''; $('#userCorreo').value=''; userModal.style.display='flex'; };
  $('#userCancel').onclick = ()=> userModal.style.display='none';
  $('#userSave').onclick = ()=>{
    const nombre = $('#userNombre').value.trim();
    const correo = $('#userCorreo').value.trim();
    const rol    = $('#userRol').value;
    if(!nombre || !correo) return alert('Completa todos los campos');
    if(users.some(u=>u.correo===correo)) return alert('Ya existe un usuario con ese correo');
    users.push({nombre, correo, rol});
    renderUsers(); userModal.style.display='none';
  };

  // ===== Init =====
  renderUsers();
  renderRoles();
})();
</script>
@endsection
