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
                        <th>Motivo</th>
                        <th>Diagnóstico</th>
                        <th>Médico</th>
                        <th></th>
                    </tr>
                </thead>
                <tbody id="historyRows">

                    {{-- EJEMPLO DE REGISTRO PARA QUE SE VEA EL DISEÑO --}}
                    <tr>
                        <td>21/11/2025</td>
                        <td>Dolor de estómago</td>
                        <td>Gastritis aguda</td>
                        <td>Dr. Abraham García</td>
                        <td class="history-actions">
                            <button
                                style="background-color:#7bc3ab;"
                                type="button"
                                class="icon-btn history-detail-btn"
                                data-date="21/11/2025"
                                data-doctor="Dr. Abraham García"
                                data-dx="Gastritis aguda"
                                data-motive="Dolor de estómago de 3 días, ardor después de comer."
                                data-allergies="Penicilina"
                                data-antecedentes="Gastritis previa, tabaquismo ocasional."
                                data-temp="36.5 °C"
                                data-press="120/80 mmHg"
                                data-pulse="75 lpm"
                                data-fr="16 rpm"
                                data-spo2="98 %"
                                data-treatment="Omeprazol 20 mg cada 12 horas por 14 días."
                                data-docs="Laboratorio general; Endoscopía (PDF)">
                                <img src="/img/visualizar.png" alt="Ver detalle" style="width:22px; height:22px;">
                            </button>
                        </td>
                    </tr>
                    <tr>
                        <td>21/11/2025</td>
                        <td>Dolor de estómago</td>
                        <td>Gastritis aguda</td>
                        <td>Dr. Abraham García</td>
                        <td class="history-actions">
                            <button
                                style="background-color:#7bc3ab;"
                                type="button"
                                class="icon-btn history-detail-btn"
                                data-date="21/11/2025"
                                data-doctor="Dr. Abraham García"
                                data-dx="Gastritis aguda"
                                data-motive="Dolor de estómago de 3 días, ardor después de comer."
                                data-allergies="Penicilina"
                                data-antecedentes="Gastritis previa, tabaquismo ocasional."
                                data-temp="36.5 °C"
                                data-press="120/80 mmHg"
                                data-pulse="75 lpm"
                                data-fr="16 rpm"
                                data-spo2="98 %"
                                data-treatment="Omeprazol 20 mg cada 12 horas por 14 días."
                                data-docs="Laboratorio general; Endoscopía (PDF)">
                                <img src="/img/visualizar.png" alt="Ver detalle" style="width:22px; height:22px;">
                            </button>
                        </td>
                    </tr>

                    <tr>
                        <td>21/11/2025</td>
                        <td>Dolor de estómago</td>
                        <td>Gastritis aguda</td>
                        <td>Dr. Abraham García</td>
                        <td class="history-actions">
                            <button
                                style="background-color:#7bc3ab;"
                                type="button"
                                class="icon-btn history-detail-btn"
                                data-date="21/11/2025"
                                data-doctor="Dr. Abraham García"
                                data-dx="Gastritis aguda"
                                data-motive="Dolor de estómago de 3 días, ardor después de comer."
                                data-allergies="Penicilina"
                                data-antecedentes="Gastritis previa, tabaquismo ocasional."
                                data-temp="36.5 °C"
                                data-press="120/80 mmHg"
                                data-pulse="75 lpm"
                                data-fr="16 rpm"
                                data-spo2="98 %"
                                data-treatment="Omeprazol 20 mg cada 12 horas por 14 días."
                                data-docs="Laboratorio general; Endoscopía (PDF)">
                                <img src="/img/visualizar.png" alt="Ver detalle" style="width:22px; height:22px;">
                            </button>
                        </td>
                    </tr>

                    <tr>
                        <td>21/11/2025</td>
                        <td>Dolor de estómago</td>
                        <td>Gastritis aguda</td>
                        <td>Dr. Abraham García</td>
                        <td class="history-actions">
                            <button
                                style="background-color:#7bc3ab;"
                                type="button"
                                class="icon-btn history-detail-btn"
                                data-date="21/11/2025"
                                data-doctor="Dr. Abraham García"
                                data-dx="Gastritis aguda"
                                data-motive="Dolor de estómago de 3 días, ardor después de comer."
                                data-allergies="Penicilina"
                                data-antecedentes="Gastritis previa, tabaquismo ocasional."
                                data-temp="36.5 °C"
                                data-press="120/80 mmHg"
                                data-pulse="75 lpm"
                                data-fr="16 rpm"
                                data-spo2="98 %"
                                data-treatment="Omeprazol 20 mg cada 12 horas por 14 días."
                                data-docs="Laboratorio general; Endoscopía (PDF)">
                                <img src="/img/visualizar.png" alt="Ver detalle" style="width:22px; height:22px;">
                            </button>
                        </td>
                    </tr>

                </tbody>
            </table>

            <p id="noHistoryRows" class="muted" style="text-align:center;margin-top:10px; display:none;">
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

{{-- Modal: Detalle de historial médico --}}
<div id="historyModal" class="modal hidden">
  <div class="modal-content modal-lg">

    <h3 class="modal-title">Detalle de historial</h3>

    <div class="history-modal-body">

      <div class="history-section">
        <h4>Datos de la consulta</h4>
        <p><strong>Fecha:</strong> <span id="hDetFecha">—</span></p>
        <p><strong>Médico:</strong> <span id="hDetMedico">—</span></p>
        <p><strong>Diagnóstico:</strong> <span id="hDetDx">—</span></p>
      </div>

      <hr class="section-divider">

      <div class="history-section">
        <h4>Motivo / Observaciones</h4>
        <p id="hDetMotivo">—</p>
      </div>

      <hr class="section-divider">

      <div class="history-section history-grid">
        <div>
          <h4>Alergias</h4>
          <p id="hDetAlergias">—</p>
        </div>
        <div>
          <h4>Antecedentes</h4>
          <p id="hDetAntecedentes">—</p>
        </div>
      </div>

      <hr class="section-divider">

      <div class="history-section">
        <h4>Signos vitales</h4>
        <ul class="vitals-list">
          <li><strong>Temperatura:</strong> <span id="hDetTemp">—</span></li>
          <li><strong>Presión arterial:</strong> <span id="hDetPress">—</span></li>
          <li><strong>Pulso:</strong> <span id="hDetPulse">—</span></li>
          <li><strong>Frecuencia respiratoria:</strong> <span id="hDetFR">—</span></li>
          <li><strong>Saturación de oxígeno:</strong> <span id="hDetSpO2">—</span></li>
        </ul>
      </div>

      <hr class="section-divider">

      <div class="history-section">
        <h4>Tratamiento indicado</h4>
        <p id="hDetTrat">—</p>
      </div>

      <hr class="section-divider">

      <div class="history-section">
  <h4>Documentos asociados</h4>

  <div id="hDetDocs" class="docs-list">

      <!-- EJEMPLOS PARA QUE TU PROFE LO VEA -->
      <div class="doc-item">
          <span class="doc-name">Laboratorio_general.pdf</span>
          <a href="#" class="doc-btn" target="_blank"><img src="/img/visualizar.png" class="doc-icon" alt="IMG"></a>
      </div>

      <div class="doc-item">
          <span class="doc-name">Endoscopia_2025.jpg</span>
          <a href="#" class="doc-btn" target="_blank"><img src="/img/visualizar.png" class="doc-icon" alt="IMG"></a>
      </div>

  </div>
</div>



      

    </div>

    <div class="btn-container" style="margin-top:14px;">
      <button type="button" class="modal-cancel-btn" id="historyCloseBtn">
        <img src="/img/cancelar.png" alt="Cerrar">
      </button>
    </div>

  </div>
</div>


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

<script>
(() => {
  // ----- Modal de detalle de historial -----
  const historyModal   = document.getElementById('historyModal');
  const historyClose   = document.getElementById('historyCloseBtn');
  const detailButtons  = document.querySelectorAll('.history-detail-btn');

  // Campos del modal
  const spanFecha   = document.getElementById('hDetFecha');
  const spanMedico  = document.getElementById('hDetMedico');
  const spanDx      = document.getElementById('hDetDx');
  const spanMotivo  = document.getElementById('hDetMotivo');
  const spanAlerg   = document.getElementById('hDetAlergias');
  const spanAnteced = document.getElementById('hDetAntecedentes');
  const spanTemp    = document.getElementById('hDetTemp');
  const spanPress   = document.getElementById('hDetPress');
  const spanPulse   = document.getElementById('hDetPulse');
  const spanFR      = document.getElementById('hDetFR');
  const spanSpO2    = document.getElementById('hDetSpO2');
  const spanTrat    = document.getElementById('hDetTrat');

  // Contenedor de documentos
  const docsContainer = document.getElementById('hDetDocs');


  function openHistoryModal(btn) {

    // Rellenar campos del modal
    spanFecha.textContent   = btn.dataset.date || '—';
    spanMedico.textContent  = btn.dataset.doctor || '—';
    spanDx.textContent      = btn.dataset.dx || '—';
    spanMotivo.textContent  = btn.dataset.motive || '—';
    spanAlerg.textContent   = btn.dataset.allergies || '—';
    spanAnteced.textContent = btn.dataset.antecedentes || '—';
    spanTemp.textContent    = btn.dataset.temp || '—';
    spanPress.textContent   = btn.dataset.press || '—';
    spanPulse.textContent   = btn.dataset.pulse || '—';
    spanFR.textContent      = btn.dataset.fr || '—';
    spanSpO2.textContent    = btn.dataset.spo2 || '—';
    spanTrat.textContent    = btn.dataset.treatment || '—';


    // -----------------------------
    //     DOCUMENTOS ASOCIADOS
    // -----------------------------
    docsContainer.innerHTML = ''; // limpiar

    const rawDocs = btn.dataset.docs || '';
    const docs = rawDocs.split(';').map(d => d.trim()).filter(Boolean);

    if (!docs.length) {
      docsContainer.innerHTML = `<p class="muted">Sin documentos asociados.</p>`;
    } else {
      docs.forEach(name => {
        
        // Por ahora usamos cancelar.png como ícono de ejemplo
        const icon = '/img/visualizar.png'; // luego lo cambias a visualizar.png

        docsContainer.insertAdjacentHTML('beforeend', `
          <div class="doc-item">
            <span class="doc-name">${name}</span>

            <a href="#" class="doc-btn" target="_blank">
              <img src="${icon}" class="doc-icon" alt="Ver">
            </a>
          </div>
        `);
      });
    }

    historyModal.classList.remove('hidden');
  }


  function closeHistoryModal() {
    historyModal.classList.add('hidden');
  }


  // Abrir modal al tocar ícono
  detailButtons.forEach(btn => {
    btn.addEventListener('click', () => openHistoryModal(btn));
  });

  // Botón cerrar
  historyClose.addEventListener('click', closeHistoryModal);

  // Cerrar al tocar fuera del modal
  historyModal.addEventListener('click', (e) => {
    if (e.target === historyModal) closeHistoryModal();
  });

})();

</script>


@endsection
