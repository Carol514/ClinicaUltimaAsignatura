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
    <div id="permList" style="display:none;"></div>
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
          <br>
          <input id="userNombre" placeholder="Nombre completo">
        </div>
        <div>
          <label>Correo / ID</label>
          <br>
          <input id="userCorreo" placeholder="usuario@hospital.local">
        </div>
        <div>
          <label>Contraseña</label>
          <br>
          <input id="userContraseña" placeholder="********">
        </div>
        <div>
          <label>Rol</label>
          <br>
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

  
  

  function renderRoles(list = []){
    rolesTbody.innerHTML='';
    if(!list.length){ noRoles.style.display='block'; return; }
    noRoles.style.display='none';
    list.forEach(r=>{
      const tr = document.createElement('tr');
      tr.innerHTML = `
        <td>${r.name}</td>
        
        <td>
          
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
    }else if(action === 'cambiar'){
  // create popup
  const popup = document.createElement('div');
  popup.style.position = 'absolute';
  popup.style.zIndex = 10000;
  popup.style.background = '#fff';
  popup.style.padding = '10px';
  popup.style.border = '1px solid rgba(0,0,0,0.12)';
  popup.style.borderRadius = '8px';
  popup.style.boxShadow = '0 6px 18px rgba(0,0,0,0.12)';
  popup.style.minWidth = '220px';
  popup.style.fontSize = '14px';

  // position near clicked button (btn is available in this scope)
  const rect = btn.getBoundingClientRect();
  popup.style.top = (rect.bottom + window.scrollY + 6) + 'px';
  popup.style.left = (rect.left + window.scrollX) + 'px';

  // build select and actions
  const select = document.createElement('select');
  select.style.width = '100%';
  select.style.marginBottom = '8px';
  select.innerHTML = '<option value="">Cargando roles...</option>';

  const actions = document.createElement('div');
  actions.style.display = 'flex';
  actions.style.justifyContent = 'flex-end';
  actions.style.gap = '8px';

  const cancelBtn = document.createElement('button');
  cancelBtn.type = 'button';
  cancelBtn.textContent = 'Cancelar';
  cancelBtn.style.cursor = 'pointer';

  const saveBtn = document.createElement('button');
  saveBtn.type = 'button';
  saveBtn.textContent = 'Guardar';
  saveBtn.style.cursor = 'pointer';
  saveBtn.style.background = '#007bff';
  saveBtn.style.color = '#fff';
  saveBtn.style.border = 'none';
  saveBtn.style.padding = '6px 10px';
  saveBtn.style.borderRadius = '4px';

  actions.appendChild(cancelBtn);
  actions.appendChild(saveBtn);
  popup.appendChild(select);
  popup.appendChild(actions);
  document.body.appendChild(popup);

  // close helper
  const removePopup = () => {
    if (popup && popup.parentNode) popup.parentNode.removeChild(popup);
    document.removeEventListener('click', outsideListener);
    window.removeEventListener('resize', removePopup);
    window.removeEventListener('scroll', removePopup, true);
  };
  const outsideListener = (ev) => {
    if (!popup.contains(ev.target) && ev.target !== btn) removePopup();
  };
  setTimeout(() => document.addEventListener('click', outsideListener), 0);
  window.addEventListener('resize', removePopup);
  window.addEventListener('scroll', removePopup, true);

  // Load roles from your endpoint (use api() if you prefer)
  // Using fetch here in case api() wraps fetch — change to api(...) if that's your helper.
  fetch('/administrador/api/roles')
    .then(res => {
      if (!res.ok) throw new Error('HTTP ' + res.status);
      return res.json();
    })
    .then(roles => {
      console.log('Roles fetched:', roles);
      if (!roles || roles.length === 0) {
        select.innerHTML = '<option value="">No hay roles disponibles</option>';
        return;
      }

      select.innerHTML = '<option value="">-- Selecciona un rol --</option>';

      roles.forEach((r, idx) => {
        const opt = document.createElement('option');

        // If role is primitive (string/number)
        if (typeof r === 'string' || typeof r === 'number') {
          opt.value = String(r);
          opt.textContent = String(r);
          opt.dataset.roleName = String(r);
          opt.dataset.roleId = '';
        } else if (typeof r === 'object' && r !== null) {
          // Prefer user-friendly text fields, fallback to id or index
          const name = r.nombre ?? r.name ?? r.rol ?? r.codigo ?? r.label ?? r.displayName ?? null;
          const idVal = (r.id !== undefined && r.id !== null) ? String(r.id) : '';
          opt.value = idVal || name || String(idx);
          opt.textContent = name || idVal || (`Rol ${idx+1}`);
          opt.dataset.roleId = idVal;
          opt.dataset.roleName = name || '';
        } else {
          opt.value = String(idx);
          opt.textContent = String(r);
        }

        select.appendChild(opt);
      });
    })
    .catch(err => {
      console.error('Error loading roles:', err);
      removePopup();
      // fallback: show simple prompt with number list (safe fallback)
      fetch('/administrador/api/roles')
        .then(r => r.json())
        .catch(() => null)
        .then(roles => {
          if (!roles || roles.length === 0) {
            alert('No se pudieron cargar roles.');
            return;
          }
          const opciones = roles.map((r, i) => `${i+1}. ${ (typeof r === 'object') ? (r.nombre ?? r.name ?? r.id ?? String(r)) : String(r) }`).join('\n');
          const seleccion = prompt(`Seleccione el nuevo rol escribiendo el número:\n${opciones}`);
          const index = parseInt(seleccion) - 1;
          if (isNaN(index) || index < 0 || index >= roles.length) { alert('Selección inválida.'); return; }
          const selRole = roles[index];
          const newRoleValue = selRole.nombre ?? selRole.name ?? selRole.codigo ?? selRole.id ?? selRole;
          api(`/administrador/api/users/${id}/role`, { method: 'PUT', body: { role: newRoleValue } })
            .then(()=> { alert('Rol actualizado.'); loadAll(); })
            .catch(e => alert(e.body?.message || 'Error actualizando rol'));
        });
    });

  // cancel and save handlers
  cancelBtn.onclick = () => removePopup();

  saveBtn.onclick = () => {
    const chosen = select.options[select.selectedIndex];
    if (!chosen || !chosen.value) return alert('Selecciona un rol válido.');
    // Prefer roleName if available (human readable), otherwise send roleId or value
    const payloadRole = chosen.dataset.roleName || chosen.dataset.roleId || chosen.value;
    console.log('Assigning role payload:', payloadRole, 'option dataset:', chosen.dataset);
    removePopup();
    api(`/administrador/api/users/${id}/role`, { method:'PUT', body: { role: payloadRole } })
      .then(()=> { alert('Rol actualizado.'); loadAll(); })
      .catch(err => {
        console.error('Error updating role:', err);
        alert(err.body?.message || 'Error actualizando rol');
      });
  };
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
