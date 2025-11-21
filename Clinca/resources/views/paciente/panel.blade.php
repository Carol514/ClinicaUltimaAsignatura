@extends('layouts.app')
@section('title','Panel del Paciente')

@section('content')
<main class="dashboard patient-dashboard">
  <h2>Panel del Paciente</h2>

  {{-- CONTENEDOR 2 COLUMNAS: HISTORIAL (2fr) + NOTIFICACIONES (1fr) --}}
  @php
    $user = \Illuminate\Support\Facades\Auth::user();
    $patientName = $user?->name ?? 'Paciente';
    $today = \Carbon\Carbon::now()->format('d/m/Y');
@endphp

<section class="patient-layout">
    
    {{-- IZQUIERDA: HISTORIAL --}}
    <div class="patient-card patient-card--history">
        <h3>Historial</h3>

        <div class="table-container" style="margin-top:10px;">
            <table>
                <thead>
                    <tr>
                        <th>Fecha</th>
                        <th>Evento</th>
                        <th>Detalle</th>
                    </tr>
                </thead>
                <tbody id="historyRows"></tbody>
            </table>
            <p id="noHistoryRows" class="muted" style="text-align:center;margin-top:10px;">
                Sin registros en el historial.
            </p>
        </div>
    </div>

    {{-- DERECHA: COLUMNA CON INFO + RECORDATORIOS --}}
    <div class="patient-side">

        {{-- TARJETA SUPERIOR: INFO DEL PACIENTE --}}
        <div class="patient-card patient-card--summary">
            <div class="patient-summary-name">Bienvenido, {{ $patientName }}</div>
            <div class="patient-summary-date">{{ $today }}</div>
        </div>

        {{-- TARJETA INFERIOR: RECORDATORIOS --}}
        <div class="patient-card patient-card--notifs">
            <h3>Recordatorios</h3>

            <div class="reminders-list">

                <div class="reminder-card reminder-card--info">
                    <div class="reminder-icon">
                        <img src="/img/calendario.png" width="22">
                    </div>
                    <div class="reminder-content">
                        <div class="reminder-text">
                            Cita con Dr. Abraham el 25/11/2025 a las 12:00 PM
                        </div>
                    </div>
                </div>

                <div class="reminder-card reminder-card--info">
                    <div class="reminder-icon">
                        <img src="/img/calendario.png" width="22">
                    </div>
                    <div class="reminder-content">
                        <div class="reminder-text">
                            Cita con Dr. Gomez el 26/11/2025 a las 2:00 AM
                        </div>
                    </div>
                </div>

                <div class="reminder-card reminder-card--danger">
                    <div class="reminder-icon">✖</div>
                    <div class="reminder-content">
                        <div class="reminder-text">
                            Faltaste a tu cita con Dr. Gomez el 20/11/2025
                        </div>
                    </div>
                </div>

            </div>
        </div>

    </div>
</section>

</main>

<script>
(() => {
  // === HISTORIAL ===
  const historyRows   = document.getElementById('historyRows');
  const noHistoryRows = document.getElementById('noHistoryRows');

  async function fetchHistory() {
    try {
      const res = await fetch('/paciente/api/history', {
        credentials: 'same-origin',
        headers: { 'Accept': 'application/json' }
      });
      if (!res.ok) throw new Error('history error');
      const json = await res.json();
      return Array.isArray(json) ? json : [];
    } catch (e) {
      console.warn('No se pudo cargar historial remoto', e);
      return [];
    }
  }

  function renderHistory(list) {
    historyRows.innerHTML = '';
    if (!list.length) {
      noHistoryRows.style.display = 'block';
      return;
    }
    noHistoryRows.style.display = 'none';

    list
      .sort((a, b) => (a.fecha || '').localeCompare(b.fecha || ''))
      .forEach(it => {
        const tr = document.createElement('tr');
        tr.innerHTML = `
          <td>${it.fecha || ''}</td>
          <td>${it.tipo || it.evento || ''}</td>
          <td>${it.detalle || ''}</td>
        `;
        historyRows.appendChild(tr);
      });
  }

  // === NOTIFICACIONES ===
  const notifEnabled   = document.getElementById('notifEnabled');
  const remindersList  = document.getElementById('remindersList');
  const remindersEmpty = document.getElementById('remindersEmpty');

  async function fetchReminders() {
    try {
      const res = await fetch('/paciente/api/reminders', {
        credentials: 'same-origin',
        headers: { 'Accept': 'application/json' }
      });
      if (!res.ok) throw new Error('reminders error');
      const json = await res.json();
      return Array.isArray(json) ? json : [];
    } catch (e) {
      console.warn('No se pudieron cargar recordatorios, usando demo.', e);
      const hoy = new Date().toISOString().slice(0,10);
      const mañana = new Date(Date.now() + 86400000).toISOString().slice(0,10);
      // Fallback de ejemplo
      return [
        { tipo:'Cita', detalle:'Tienes una cita HOY con tu médico.', fecha:hoy, estado:'Próxima' },
        { tipo:'Cita', detalle:'Tienes una cita programada para mañana.', fecha:mañana, estado:'Próxima' },
        { tipo:'Cita', detalle:'Faltaste a tu cita anterior.', fecha:hoy, estado:'No asistió' },
      ];
    }
  }

  function renderReminders(list) {
    remindersList.innerHTML = '';

    if (!list.length) {
      remindersEmpty.textContent = 'No hay notificaciones recientes.';
      remindersEmpty.style.display = 'block';
      return;
    }

    remindersEmpty.style.display = 'none';

    list.slice(0, 5).forEach(it => {
      const card = document.createElement('div');

      let cardClass = 'reminder-card';
      const estado = (it.estado || '').toLowerCase();
      if (estado.includes('no asist') || estado.includes('falt')) {
        cardClass += ' reminder-card--danger';
      } else {
        cardClass += ' reminder-card--info';
      }

      const date = it.fecha || '';
      const time = it.hora || '';
      const timeStr = time ? ` • ${time}` : '';

      card.className = cardClass;
      card.innerHTML = `
        <div class="reminder-icon">${estado.includes('no asist') || estado.includes('falt') ? '✖' : '📅'}</div>
        <div class="reminder-content">
          <div class="reminder-text">${it.detalle || ''}</div>
          <div class="reminder-meta">${date}${timeStr}</div>
        </div>
      `;
      remindersList.appendChild(card);
    });
  }

  notifEnabled.addEventListener('change', () => {
    const on = notifEnabled.checked;
    remindersList.style.opacity = on ? '1' : '0.4';
    remindersList.style.pointerEvents = on ? 'auto' : 'none';
  });

  // Cargar datos al entrar
  fetchHistory().then(renderHistory);
  fetchReminders().then(renderReminders);
})();
</script>
@endsection
