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

      <div style="display:flex;align-items:center;gap:10px;padding:10px 0;border-bottom:1px solid #e5e5e5;">
        <span style="color:#2a6b5f;">📅</span>
        <div>Cita programada con Dr. López — <small>30/10/2025, 10:00 AM</small></div>
      </div>

      <div style="display:flex;align-items:center;gap:10px;padding:10px 0;">
        <span style="color:#2a6b5f;">⏰</span>
        <div>Recordatorio: chequeo anual el 15/11/2025</div>
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

  toggleNotif.addEventListener('change', applyVisibility);
  applyVisibility(); // inicial
})();
</script>
@endsection
