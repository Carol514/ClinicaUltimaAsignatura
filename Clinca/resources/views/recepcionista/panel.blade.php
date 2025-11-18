@extends('layouts.app')
@section('title', 'Panel de Recepción')

@section('content')
<style>
.btn-warning {
  background-color: #ffc107 !important;
  color: #212529 !important;
  border-color: #ffc107 !important;
}
.btn-warning:hover {
  background-color: #ffca2c !important;
  border-color: #ffc720 !important;
}
.btn-danger {
  background-color: #dc3545 !important;
  color: white !important;
  border-color: #dc3545 !important;
}
.btn-danger:hover {
  background-color: #c82333 !important;
  border-color: #bd2130 !important;
}
.text-muted {
  color: #6c757d !important;
  font-style: italic;
}
</style>
<main class="dashboard">
  <h2>Panel de Recepción</h2>
  <p class="muted">Selecciona una acción para comenzar.</p>

  {{-- Acciones rápidas --}}
  <div class="card-container">
    <a class="card" href="{{ route('recepcionista.registro') }}" style="text-decoration:none;">
      Registrar paciente
    </a>
    <a class="card" href="{{ route('recepcionista.citas') }}" style="text-decoration:none;">
      Agendar cita
    </a>
  </div>

  {{-- ===== Agenda embebida ===== --}}
  <section class="panel" style="max-width:1000px; margin-top:18px;">
    <h3 style="margin-top:0;">Agenda</h3>

    {{-- Filtros --}}
    <form class="form-container agenda-filtros" onsubmit="return false;">
      <div class="fields" style="display:grid; gap:12px; grid-template-columns:1fr 1fr 1fr 1fr;">
        <div class="field">
          <label for="desde">Desde</label>
          <input type="date" id="desde">
        </div>
        <div class="field">
          <label for="hasta">Hasta</label>
          <input type="date" id="hasta">
        </div>
        <div class="field">
          <label for="doc">Doctor</label>
          <select id="doc">
            <option value="">Todos</option>
          </select>
        </div>
        <div class="field">
          <label for="q">Paciente</label>
          <input id="q" placeholder="Nombre / motivo">
        </div>
      </div>

      <div class="btn-container acciones" style="margin-top:12px;">
        <button id="btnBuscar" class="confirm-btn" type="button">Buscar</button>
        <button id="btnLimpiar" class="cancel-btn" type="button">Limpiar</button>
      </div>
    </form>

    {{-- Tabla --}}
    <div class="table-container" style="margin-top:16px;">
      <table>
        <thead>
          <tr>
            <th>Fecha</th>
            <th>Hora</th>
            <th>Paciente</th>
            <th>Doctor</th>
            <th>Motivo</th>
            <th>Estado</th>
            <th>Acciones</th>
          </tr>
        </thead>
        <tbody id="rows"></tbody>
      </table>
      <p id="noRows" class="muted" style="text-align:center;margin-top:10px;">Sin resultados.</p>
    </div>
  </section>

  {{-- Modal para reprogramar cita --}}
  <div id="rescheduleModal" style="display:none; position:fixed; top:0; left:0; width:100%; height:100%; background:rgba(0,0,0,0.5); z-index:1000;">
    <div style="position:absolute; top:50%; left:50%; transform:translate(-50%, -50%); background:white; padding:20px; border-radius:8px; min-width:400px;">
      <h3 style="margin-top:0;">Reprogramar Cita</h3>
      
      <form id="rescheduleForm" class="form-container">
        <div class="field">
          <label for="newDate">Nueva Fecha</label>
          <input type="date" id="newDate" required>
        </div>
        
        <div class="field">
          <label for="newTime">Nueva Hora</label>
          <input type="time" id="newTime" required>
        </div>
        
        <div class="btn-container" style="margin-top:16px;">
          <button type="submit" class="confirm-btn">Guardar Cambios</button>
          <button type="button" class="cancel-btn" onclick="closeRescheduleModal()">Cancelar</button>
        </div>
      </form>
    </div>
  </div>
</main>

<script>
(() => {
  // Live agenda: fetch appointments from server
  const rows   = document.getElementById('rows');
  const noRows = document.getElementById('noRows');
  const desde  = document.getElementById('desde');
  const hasta  = document.getElementById('hasta');
  const doc    = document.getElementById('doc');
  const q      = document.getElementById('q');

  function render(list){
    rows.innerHTML = '';
    if (!list || !list.length){ noRows.style.display='block'; return; }
    noRows.style.display='none';
    list.forEach(it=>{
      const date = (it.scheduled_at||'').split(' ')[0] || '';
      const time = (it.scheduled_at||'').split(' ')[1] || '';
      const tr = document.createElement('tr');
      tr.dataset.appId = it.id;
      // map DB status values to human labels
      const statusMap = {
        'programada':'Programada',
        'confirmada':'Confirmada',
        'no_asistio':'No asistió',
        'cancelada':'Cancelada',
        'atendida':'Atendida'
      };
      const humanStatus = statusMap[(it.status||'') ] || (it.status || '');
      
      // Generate buttons based on current status
      let buttonsHtml = '';
      const currentStatus = it.status || 'programada';
      
      if (currentStatus === 'atendida' || currentStatus === 'cancelada' || currentStatus === 'no_asistio') {
        // No buttons for final states (completed, cancelled, or no-show)
        const finalStateLabels = {
          'atendida': 'Finalizada',
          'cancelada': 'Cancelada',
          'no_asistio': 'No Asistió'
        };
        buttonsHtml = `<span class="text-muted">${finalStateLabels[currentStatus]}</span>`;
      } else {
        // Show action buttons for active appointments (programada, confirmada)
        const buttons = [];
        
        // Active appointments can be marked as attended
        buttons.push(`<button class="btn-secondary" onclick="updateAppointmentStatus('${it.id}', 'atendida', this)">Llegó</button>`);
        
        // Active appointments can be marked as no-show or cancelled
        buttons.push(`<button class="btn-secondary btn-warning" onclick="updateAppointmentStatus('${it.id}', 'no_asistio', this)">No Asistió</button>`);
        buttons.push(`<button class="btn-secondary btn-danger" onclick="updateAppointmentStatus('${it.id}', 'cancelada', this)">Cancelar</button>`);
        
        // Reschedule button (only for programada and confirmada)
        if (currentStatus === 'programada' || currentStatus === 'confirmada') {
          buttons.push(`<button class="btn-secondary" onclick="rescheduleAppointment('${it.id}', '${date}', '${time}')">Reprog.</button>`);
        }
        
        buttonsHtml = buttons.join(' ');
      }

      tr.innerHTML = `
        <td>${date}</td>
        <td>${time}</td>
        <td>${it.patient_name || it.patient_id || ''}</td>
        <td>${it.clinician_name || ''}</td>
        <td>${it.reason || ''}</td>
        <td class="status-cell">${humanStatus}</td>
        <td>
          ${buttonsHtml}
        </td>
      `;
      rows.appendChild(tr);
    });
  }

  async function loadMedicosForPanel(){
    try{
      const res = await fetch('/recepcionista/api/medicos', { headers:{ 'Accept':'application/json' }, credentials: 'same-origin' });
      if (!res.ok) return;
      const body = await res.clone().json().catch(()=>null);
      const list = (body && body.data) ? body.data : [];
      const sel = document.getElementById('doc');
      // keep first 'Todos' option (value='') and remove others
      Array.from(sel.querySelectorAll('option')).forEach((o,i)=>{ if (i>0) o.remove(); });
      list.forEach(m=>{
        const opt = document.createElement('option'); opt.value = m.id; opt.textContent = m.name; sel.appendChild(opt);
      });
    }catch(err){ console.error('loadMedicosForPanel', err); }
  }

  async function loadAppointments(){
    try{
      const params = new URLSearchParams();
      if (desde.value) params.set('from', desde.value);
      if (hasta.value) params.set('to', hasta.value);
      if (q.value.trim()) params.set('q', q.value.trim());
      // doc select value is clinician id; API expects clinician filter via q or we already adjusted listAppointments to ignore clinician filter. We'll filter client-side if doc set.
      const res = await fetch('/recepcionista/api/appointments?'+params.toString(), { headers:{ 'Accept':'application/json' }, credentials: 'same-origin' });
      if (!res.ok){ const txt = await res.clone().text().catch(()=>null); console.error('appointments fetch error', res.status, txt); render([]); return; }
      const body = await res.clone().json().catch(()=>null);
      let list = (body && body.data) ? body.data : [];
      const docVal = document.getElementById('doc').value || '';
      if (docVal) list = list.filter(x => (x.clinician_id || '') == docVal);
      render(list);
    }catch(err){ console.error('loadAppointments', err); render([]); }
  }

  document.getElementById('btnBuscar').onclick = loadAppointments;
  document.getElementById('btnLimpiar').onclick = () => {
    desde.value = hasta.value = ''; doc.value = ''; q.value = '';
    // reload with no filters
    loadAppointments();
  };

  // populate medicos and load appointments on page ready
  loadMedicosForPanel().then(()=> loadAppointments());

  // Update appointment status
  window.updateAppointmentStatus = async function(id, newStatus, btn){
    const statusMessages = {
      'atendida': '¿Confirmar que el paciente llegó y fue atendido?',
      'no_asistio': '¿Confirmar que el paciente no asistió a la cita?', 
      'cancelada': '¿Confirmar que desea cancelar esta cita?'
    };
    
    const statusLabels = {
      'atendida': 'Atendida',
      'no_asistio': 'No asistió',
      'cancelada': 'Cancelada'
    };
    
    if (!confirm(statusMessages[newStatus] || 'Confirmar cambio de estado?')) return;
    
    try{
      btn.disabled = true; 
      const orig = btn.textContent; 
      btn.textContent = '...';
      
      const token = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '';
      const res = await fetch('/recepcionista/api/appointments/'+encodeURIComponent(id), {
        method: 'PUT',
        credentials: 'same-origin',
        headers: { 'Accept':'application/json','Content-Type':'application/json','X-CSRF-TOKEN': token },
        body: JSON.stringify({ status: newStatus })
      });
      
      if (!res.ok){ 
        const txt = await res.clone().text().catch(()=>null); 
        alert('Error al actualizar: '+res.status+' '+txt); 
        btn.disabled = false; 
        btn.textContent = orig; 
        return; 
      }
      
      const body = await res.clone().json().catch(()=>null);
      
      // Update the row status and buttons
      const tr = document.querySelector('tr[data-app-id="'+id+'"]');
      if (tr){ 
        const sc = tr.querySelector('.status-cell'); 
        if (sc) sc.textContent = statusLabels[newStatus];
        
        // If marked as final state (attended, cancelled, or no-show), remove all buttons
        if (newStatus === 'atendida' || newStatus === 'cancelada' || newStatus === 'no_asistio') {
          const actionCell = tr.querySelector('td:last-child');
          const finalStateLabels = {
            'atendida': 'Finalizada',
            'cancelada': 'Cancelada', 
            'no_asistio': 'No Asistió'
          };
          if (actionCell) actionCell.innerHTML = `<span class="text-muted">${finalStateLabels[newStatus]}</span>`;
        }
      }
      
      alert('✅ Estado de la cita actualizado exitosamente');
      
    }catch(err){ 
      console.error(err); 
      alert('Error de red: '+(err.message||err)); 
      btn.disabled = false; 
      btn.textContent = orig;
    }
  }

  // Reschedule appointment functionality
  let currentRescheduleId = null;

  window.rescheduleAppointment = function(id, currentDate, currentTime) {
    currentRescheduleId = id;
    document.getElementById('newDate').value = currentDate;
    document.getElementById('newTime').value = currentTime;
    document.getElementById('rescheduleModal').style.display = 'block';
  }

  window.closeRescheduleModal = function() {
    document.getElementById('rescheduleModal').style.display = 'none';
    currentRescheduleId = null;
  }

  // Handle reschedule form submission
  document.getElementById('rescheduleForm').addEventListener('submit', async function(e) {
    e.preventDefault();
    
    if (!currentRescheduleId) {
      alert('Error: No hay cita seleccionada');
      return;
    }

    const newDate = document.getElementById('newDate').value;
    const newTime = document.getElementById('newTime').value;
    
    if (!newDate || !newTime) {
      alert('Por favor complete fecha y hora');
      return;
    }

    const newScheduledAt = newDate + ' ' + newTime;

    try {
      const token = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '';
      const res = await fetch('/recepcionista/api/appointments/' + encodeURIComponent(currentRescheduleId), {
        method: 'PUT',
        credentials: 'same-origin',
        headers: { 
          'Accept': 'application/json',
          'Content-Type': 'application/json',
          'X-CSRF-TOKEN': token 
        },
        body: JSON.stringify({ scheduled_at: newScheduledAt })
      });

      if (!res.ok) {
        const txt = await res.clone().text().catch(() => null);
        alert('Error al reprogramar: ' + res.status + ' ' + txt);
        return;
      }

      const body = await res.clone().json().catch(() => null);
      
      // Update the table row with new date/time
      const tr = document.querySelector('tr[data-app-id="' + currentRescheduleId + '"]');
      if (tr) {
        const cells = tr.querySelectorAll('td');
        cells[0].textContent = newDate; // Date column
        cells[1].textContent = newTime; // Time column
      }

      alert('✅ Cita reprogramada exitosamente');
      closeRescheduleModal();
      
    } catch (err) {
      console.error(err);
      alert('Error de red: ' + (err.message || err));
    }
  });

  // Close modal when clicking outside
  document.getElementById('rescheduleModal').addEventListener('click', function(e) {
    if (e.target === this) {
      closeRescheduleModal();
    }
  });
})();
</script>
@endsection
