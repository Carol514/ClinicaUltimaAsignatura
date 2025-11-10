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
  // Use the lightweight admin API implemented in routes (no CSS or model changes)
  const $ = sel => document.querySelector(sel);
  const usersTbody = $('#usersTbody');
  const rolesTbody = $('#rolesTbody');
  const noUsers = $('#noUsers');
  const noRoles = $('#noRoles');
  const permModal  = $('#permModal');
  const permList   = $('#permList');
  const modalRoleName = $('#modalRoleName');
  const userModal = $('#userModal');

  const CSRF = '{{ csrf_token() }}';

  function api(path, opts = {}){
    opts.headers = Object.assign({
      'Accept':'application/json',
      'Content-Type':'application/json',
      'X-CSRF-TOKEN': CSRF,
    }, opts.headers || {});
    if (opts.body && typeof opts.body !== 'string') opts.body = JSON.stringify(opts.body);
    return fetch(path, opts).then(async res => {
      const txt = await res.text();
      let json = null;
      try{ json = txt ? JSON.parse(txt) : null; }catch(e){ json = txt; }
      if (!res.ok) throw { status: res.status, body: json };
      return json;
    });
  }

  function renderUsers(list = []){
    usersTbody.innerHTML = '';
    if (!list.length){ noUsers.style.display='block'; return; }
    noUsers.style.display='none';
    list.forEach(u=>{
      const rolesStr = (u.roles || []).map(r=>r.name || r.code).join(', ');
      const tr = document.createElement('tr');
      tr.innerHTML = `
        <td>${u.name}</td>
        <td>${u.email || ''}</td>
        <td>${rolesStr}</td>
        <td>
          <button class="confirm-btn" data-action="cambiar" data-id="${u.id}">Cambiar rol</button>
          <button class="cancel-btn"  data-action="eliminar" data-id="${u.id}" style="margin-left:8px;">Eliminar</button>
        </td>
      `;
      usersTbody.appendChild(tr);
    });
  }

  function resumenPermisos(perms){
    if(!perms) return 'Sin permisos';
    const activos = Object.keys(perms).filter(k=>perms[k]);
    if (!activos.length) return 'Sin permisos';
    return activos.slice(0,3).join(', ') + (activos.length>3 ? ` +${activos.length-3} más` : '');
  }

  function renderRoles(list = []){
    rolesTbody.innerHTML='';
    if(!list.length){ noRoles.style.display='block'; return; }
    noRoles.style.display='none';
    list.forEach(r=>{
      const tr = document.createElement('tr');
      tr.innerHTML = `
        <td>${r.name}</td>
        <td><span class="perm-chip">${resumenPermisos(r.permissions)}</span></td>
        <td>
          <button class="confirm-btn" data-action="permisos" data-roleid="${r.id}" data-rolename="${r.name}">Editar permisos</button>
          <button class="cancel-btn"  data-action="delrole"  data-roleid="${r.id}" style="margin-left:8px;">Eliminar rol</button>
        </td>
      `;
      rolesTbody.appendChild(tr);
    });
  }

  function loadAll(){
    Promise.all([
      api('/administrador/api/roles'),
      api('/administrador/api/users')
    ]).then(([roles, users])=>{
      renderRoles(roles || []);
      renderUsers(users || []);
      updateRoleSelect(roles || []);
    }).catch(err=>{
      console.error('Error loading admin data', err);
      alert('Error al cargar datos administrativos. Revisa la consola.');
    });
  }

  $('#searchUser').addEventListener('input', e=>{
    const q = e.target.value.toLowerCase();
    const rows = Array.from(usersTbody.querySelectorAll('tr'));
    rows.forEach(row => {
      const text = row.textContent.toLowerCase();
      row.style.display = text.includes(q) ? '' : 'none';
    });
  });
  $('#btnResetFiltro').onclick = ()=>{ $('#searchUser').value=''; loadAll(); };

  rolesTbody.addEventListener('click', e=>{
    const btn = e.target.closest('button'); if(!btn) return;
    const action = btn.dataset.action;
    const roleId = btn.dataset.roleid;
    const roleName = btn.dataset.rolename;
    if(action==='delrole'){
      if(!confirm('¿Eliminar este rol?')) return;
      api(`/administrador/api/roles/${roleId}`, { method: 'DELETE' })
        .then(()=> loadAll())
        .catch(err=> alert(err.body?.message || 'Error eliminando rol'));
    }
    if(action==='permisos'){
      api(`/administrador/api/roles`).then(list=>{
        const role = list.find(r=>r.id==roleId);
        if(!role) return alert('Rol no encontrado');
        modalRoleName.textContent = role.name;
        // render permission checkboxes from keys
        permList.innerHTML = Object.keys(role.permissions || {}).map(k=>{
          const checked = role.permissions[k] ? 'checked' : '';
          return `<label><input type="checkbox" data-perm="${k}" ${checked}> ${k}</label>`;
        }).join('') || '<p class="muted">No hay permisos definidos.</p>';
        permModal.dataset.editingRole = roleId;
        permModal.style.display='flex';
      });
    }
  });
  $('#permCancel').onclick = ()=> permModal.style.display='none';
  $('#permSave').onclick = ()=>{
    const roleId = permModal.dataset.editingRole;
    const inputs = Array.from(permList.querySelectorAll('input[data-perm]'));
    const perms = {};
    inputs.forEach(i=> perms[i.dataset.perm] = !!i.checked);
    api(`/administrador/api/roles/${roleId}`, { method: 'PUT', body: { permissions: perms } })
      .then(()=> { permModal.style.display='none'; loadAll(); })
      .catch(err=> alert(err.body?.message || 'Error guardando permisos'));
  };

  $('#btnAgregarRol').onclick = ()=>{
    const name = $('#newRoleName').value.trim();
    if(!name) return alert('Ingresa un nombre');
    api('/administrador/api/roles', { method: 'POST', body: { code: name.toLowerCase().replace(/[^a-z0-9_]+/g,'_'), name } })
      .then(()=> { $('#newRoleName').value=''; loadAll(); })
      .catch(err=> alert(err.body?.message || 'Error creando rol'));
  };

  usersTbody.addEventListener('click', e=>{
    const btn = e.target.closest('button'); if(!btn) return;
    const id = btn.dataset.id;
    const action = btn.dataset.action;
    if(action==='eliminar'){
      if(!confirm('¿Eliminar este usuario?')) return;
      api(`/administrador/api/users/${id}`, { method: 'DELETE' })
        .then(()=> loadAll())
        .catch(err=> alert(err.body?.message || 'Error eliminando usuario'));
    }else if(action==='cambiar'){
      const nuevo = prompt('Asignar nuevo rol (nombre o código)');
      if(!nuevo) return;
      api(`/administrador/api/users/${id}/role`, { method:'PUT', body: { role: nuevo } })
        .then(()=> { alert('Rol actualizado.'); loadAll(); })
        .catch(err=> alert(err.body?.message || 'Error actualizando rol'));
    }
  });

  function updateRoleSelect(list = []){
    const sel = $('#userRol');
    sel.innerHTML = list.map(r=>`<option value="${r.name}">${r.name}</option>`).join('');
  }
  $('#btnNuevoUsuario').onclick = ()=>{ $('#userNombre').value=''; $('#userCorreo').value=''; userModal.style.display='flex'; };
  $('#userCancel').onclick = ()=> userModal.style.display='none';
  $('#userSave').onclick = ()=>{
    const nombre = $('#userNombre').value.trim();
    const correo = $('#userCorreo').value.trim();
    const rol    = $('#userRol').value;
    if(!nombre || !correo) return alert('Completa todos los campos');
    api('/administrador/api/users', { method:'POST', body: { name: nombre, email: correo, role: rol } })
      .then(()=> { userModal.style.display='none'; loadAll(); })
      .catch(err=> alert(err.body?.message || 'Error creando usuario'));
  };

  // Init
  loadAll();
})();
</script>
@endsection
