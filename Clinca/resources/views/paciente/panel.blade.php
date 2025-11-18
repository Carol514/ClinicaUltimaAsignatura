@extends('layouts.app')
@section('title','Panel del Paciente')

@section('content')
<main class="dashboard">
  <h2>Panel del Paciente</h2>

  {{-- Botones directos, sin contenedor de fondo --}}
  <div class="card-container" style="margin-top:16px;">
    <a class="card" href="{{ route('paciente.historial') }}" style="text-decoration:none;">
      Consultar historial
    </a>
    <a class="card" href="{{ route('paciente.recordatorios') }}" style="text-decoration:none;">
      Recordatorios
    </a>
  </div>

  {{-- Bloque de notificaciones limpio --}}
  <section class="panel" style="max-width:900px; margin-top:24px;">
    <h3 style="text-align:center;margin-bottom:14px;">Recibir Notificaciones de Cita</h3>

    {{-- Toggle principal --}}
    <div style="display:flex;justify-content:center;align-items:center;gap:10px;margin-bottom:18px;">
      <label for="toggleNotif" style="font-weight:500;">¿Desea recibir notificaciones?</label>
      <input type="checkbox" id="toggleNotif" style="transform:scale(1.3);accent-color:#2a6b5f;" checked>
    </div>

    {{-- Métodos de notificación --}}
    <div id="notifMethods" style="margin-bottom:20px;">
      <p style="font-weight:600;color:#2a6b5f;margin-bottom:6px;">Seleccione el método de notificación:</p>
      <label style="display:flex;align-items:center;gap:8px;margin-bottom:6px;">
        <input type="checkbox" id="emailNotif" style="accent-color:#2a6b5f;"> Correo electrónico
      </label>
      <label style="display:flex;align-items:center;gap:8px;">
        <input type="checkbox" id="phoneNotif" style="accent-color:#2a6b5f;"> Teléfono (SMS o llamada)
      </label>
    </div>

    {{-- Lista de notificaciones recientes --}}
    <div id="notificationsList">
      <p style="font-weight:600;color:#2a6b5f;margin-bottom:8px;">Notificaciones recientes:</p>
      <div id="notificationsContent">
        <div style="text-align:center;padding:20px;color:#666;">
          Cargando notificaciones...
        </div>
      </div>
    </div>
  </section>
</main>

<script>
(() => {
  const toggleNotif   = document.getElementById('toggleNotif');
  const notifMethods  = document.getElementById('notifMethods');
  const notifications = document.getElementById('notificationsList');

  function applyVisibility(){
    const on = toggleNotif.checked;
    notifMethods.style.display  = on ? 'block' : 'none';
    notifications.style.display = on ? 'block' : 'none';
  }
  // Load existing preferences from server
  async function loadPrefs(){
    try{
      const res = await fetch('/paciente/api/notifications', { credentials: 'same-origin', headers: {'Accept':'application/json'} });
      if (res.ok) {
        const prefs = await res.json();
        toggleNotif.checked = !!prefs.enabled;
        document.getElementById('emailNotif').checked = !!prefs.email;
        document.getElementById('phoneNotif').checked = !!prefs.phone;
        applyVisibility();
      }
    }catch(e){ console.warn('Could not load notification prefs', e); }
  }

  // Load recent notifications/appointments
  async function loadRecentNotifications(){
    try{
      const res = await fetch('/paciente/api/reminders', { credentials: 'same-origin', headers: {'Accept':'application/json'} });
      if (res.ok) {
        const data = await res.json();
        renderNotifications(data);
      } else {
        renderNotifications([]);
      }
    }catch(e){ 
      console.warn('Could not load recent notifications', e);
      renderNotifications([]);
    }
  }

  // Render the notifications list
  function renderNotifications(notifications) {
    const content = document.getElementById('notificationsContent');
    
    if (!notifications || notifications.length === 0) {
      content.innerHTML = '<div style="text-align:center;padding:20px;color:#666;">No hay notificaciones recientes</div>';
      return;
    }

    content.innerHTML = '';
    notifications.slice(0, 5).forEach(notif => { // Show only first 5
      const div = document.createElement('div');
      div.style.cssText = 'display:flex;align-items:center;gap:10px;padding:10px 0;border-bottom:1px solid #e5e5e5;';
      
      const icon = notif.tipo === 'Cita' ? '📅' : '⏰';
      const date = notif.fecha || '';
      const time = notif.hora || '';
      const timeStr = time ? `, ${time}` : '';
      
      div.innerHTML = `
        <span style="color:#2a6b5f;">${icon}</span>
        <div>${notif.detalle} — <small>${date}${timeStr}</small></div>
      `;
      content.appendChild(div);
    });
  }

  // Persist preference (best-effort) and update UI
  async function persistPrefs(){
    const payload = {
      enabled: !!toggleNotif.checked,
      email: !!document.getElementById('emailNotif').checked,
      phone: !!document.getElementById('phoneNotif').checked,
    };
    try{
      const token = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');
      const res = await fetch('/paciente/api/notifications', { 
        method: 'POST', 
        credentials: 'same-origin', 
        headers: {
          'Content-Type':'application/json',
          'Accept':'application/json',
          'X-CSRF-TOKEN': token || ''
        }, 
        body: JSON.stringify(payload) 
      });
      if (res.ok) {
        console.log('Notification preferences saved successfully');
      }
    }catch(e){ console.warn('Could not persist notification prefs', e); }
  }

  toggleNotif.addEventListener('change', ()=>{ applyVisibility(); persistPrefs(); });
  document.getElementById('emailNotif').addEventListener('change', persistPrefs);
  document.getElementById('phoneNotif').addEventListener('change', persistPrefs);
  
  // Load preferences and notifications on page load
  loadPrefs();
  loadRecentNotifications();
})();
</script>
@endsection
