{{-- resources/views/medico/panel.blade.php --}}
@extends('layouts.app')
@section('title','Panel del Médico')

@section('content')
<main class="dashboard medico-dashboard">
  <h2>Panel del Médico</h2>

  {{-- ==========================
        RESUMEN + GRÁFICA
      =========================== --}}
  <section class="med-top-grid">

    {{-- Citas de hoy --}}
    <div class="med-card med-card--stat">
      <p class="med-stat-label">Citas de hoy</p>
      <p class="med-stat-number" id="statCitasHoy" style="color:#7bc3ab; font-size:90px;">5</p>
      <p class="med-stat-caption">Pacientes pendientes de atender</p>
    </div>

    {{-- Gráfica simple (maquetada) --}}
    <div class="med-card med-card--chart">
      <h3 class="med-card-title">Citas por día (este mes)</h3>
      <div id="miniChart" class="mini-chart">
        {{-- Se rellena / ajusta por JS sólo para maqueta --}}
      </div>
      <p class="med-chart-caption">
        Vista rápida de cuántas citas se han atendido por día.
      </p>
    </div>

  </section>


  {{-- ==========================
        BUSCADOR + HISTORIAL
      =========================== --}}
  <section class="med-history-section">

    {{-- FILTROS --}}
<div class="med-filters-row">

  <div class="field">
    <label for="f_paciente">Buscar paciente</label>
    <div class="search-row">
      <input id="f_paciente" placeholder="Nombre o ID del paciente">
      <button id="btnBuscarHist" class="confirm-btn" type="button">
        <img src="/img/buscar.png" class="btn-icon" alt="Buscar">
      </button>
    </div>
  </div>

  {{-- Filtro por enfermedad / diagnóstico con autocomplete propio --}}
  <div class="field med-dx-field">
    <label for="f_enfermedad">Filtrar por enfermedad / diagnóstico</label>
    <input id="f_enfermedad" placeholder="Ej. Gastritis, Diabetes...">
    <div id="dxSuggestions" class="dx-suggestions hidden"></div>
  </div>

</div>




    {{-- TABLA HISTORIAL --}}
    <div class="med-card med-card--history">
      <h3 class="med-card-title">Historial de expedientes</h3>

      <div class="table-container" style="margin-top:10px;">
        <table>
          <thead>
            <tr>
              <th>Fecha</th>
              <th>Paciente</th>
              <th>Motivo</th>
              <th>Diagnóstico</th>
              <th></th>
            </tr>
          </thead>
          <tbody id="medHistoryRows">
            {{-- Ejemplos para que el profe vea el diseño --}}
            <tr>
              <td>21/11/2025</td>
              <td>Hugo García</td>
              <td>Dolor de estómago</td>
              <td>Gastritis aguda</td>
              <td class="history-actions">
                <button type="button"
                        class="icon-btn med-history-detail"
                        data-fecha="21/11/2025"
                        data-paciente="Hugo García"
                        data-motivo="Dolor de estómago de 3 días, ardor después de comer."
                        data-dx="Gastritis aguda"
                        data-alergias="Penicilina"
                        data-antecedentes="Gastritis previa, tabaquismo ocasional."
                        data-temp="36.5 °C"
                        data-press="120/80 mmHg"
                        data-pulse="75 lpm"
                        data-fr="16 rpm"
                        data-spo2="98 %"
                        data-peso="70 kg"
                        data-altura="170 cm"
                        data-trat="Omeprazol 20 mg cada 12 horas por 14 días."
                        data-docs="Laboratorio general; Endoscopía (PDF)">
                  <a href="#" class="doc-btn" target="_blank" style="font-size: 23px;"><img src="/img/visualizar.png" alt="Ver detalle" style="width:20px; height:20px;"></a>
                </button>
              </td>
            </tr>

            <tr>
              <td>10/11/2025</td>
              <td>María López</td>
              <td>Control de diabetes</td>
              <td>Diabetes mellitus tipo 2</td>
              <td class="history-actions">
                <button type="button"
                        class="icon-btn med-history-detail"
                        data-fecha="10/11/2025"
                        data-paciente="María López"
                        data-motivo="Consulta de seguimiento, revisión de glucosa."
                        data-dx="Diabetes mellitus tipo 2"
                        data-alergias="Ninguna conocida"
                        data-antecedentes="Diabetes en madre, hipertensión en padre."
                        data-temp="36.8 °C"
                        data-press="130/85 mmHg"
                        data-pulse="80 lpm"
                        data-fr="18 rpm"
                        data-spo2="97 %"
                        data-peso="82 kg"
                        data-altura="160 cm"
                        data-trat="Metformina 850 mg cada 12 horas."
                        data-docs="Perfil de lípidos; Glucosa en ayunas">
                  <a href="#" class="doc-btn" target="_blank" style="font-size: 23px;"><img src="/img/visualizar.png" alt="Ver detalle" style="width:20px; height:20px;"></a>
                </button>
              </td>
            </tr>

          </tbody>
        </table>

        <p id="medHistoryEmpty" class="muted" style="text-align:center;margin-top:10px; display:none;">
          No se encontraron registros con ese filtro.
        </p>
      </div>
    </div>

  </section>


  {{-- ==========================
        ALTA DE HISTORIAL
      =========================== --}}
  <section class="med-form-section">

    <div class="med-card">
      <h3 class="med-card-title">Alta de historial médico</h3>

      <form id="altaHistForm" class="form-container">

        {{-- Paciente + Fecha --}}
        <div class="trat-grid" style="margin-bottom:12px;">
          <div class="field">
            <label for="ah_paciente">Paciente</label>
            <input id="ah_paciente" placeholder="Nombre del paciente">
          </div>

          <div class="field">
            <label for="ah_fecha">Fecha</label>
            <input id="ah_fecha" type="date">
          </div>
        </div>

        <hr class="section-divider">

        {{-- Motivo --}}
        <div class="trat-section">
          <h4 class="trat-section-title">Motivo / Observaciones</h4>
          <textarea id="ah_motivo" rows="2"
                    placeholder="Motivo de la consulta / resumen"></textarea>
        </div>

        <hr class="section-divider">

        {{-- Alergias / Antecedentes --}}
        <div class="trat-section history-grid">
          <div class="field">
            <h4 class="trat-section-title">Alergias</h4>
            <textarea id="ah_alergias" rows="2"
                      placeholder="Ej. Penicilina, mariscos..."></textarea>
          </div>

          <div class="field">
            <h4 class="trat-section-title">Antecedentes</h4>
            <textarea id="ah_antecedentes" rows="2"
                      placeholder="Ej. Diabetes, hipertensión..."></textarea>
          </div>
        </div>

        <hr class="section-divider">

        {{-- Signos vitales básicos --}}
        <div class="trat-section">
          <h4 class="trat-section-title">Signos vitales</h4>

          <div class="trat-grid">
            <div class="field">
              <label for="ah_temp">Temperatura (°C)</label>
              <input id="ah_temp" type="number" step="0.1" placeholder="36.5">
            </div>
            <div class="field">
              <label for="ah_press">Presión arterial (mmHg)</label>
              <input id="ah_press" placeholder="120/80">
            </div>
            <div class="field">
              <label for="ah_pulse">Pulso (lpm)</label>
              <input id="ah_pulse" type="number" placeholder="75">
            </div>
            <div class="field">
              <label for="ah_resp">Frecuencia resp. (rpm)</label>
              <input id="ah_resp" type="number" placeholder="16">
            </div>
            <div class="field">
              <label for="ah_spo2">SpO₂ (%)</label>
              <input id="ah_spo2" type="number" placeholder="98">
            </div>
            <div class="field">
              <label for="ah_peso">Peso (kg)</label>
              <input id="ah_peso" type="number" step="0.1" placeholder="70.0">
            </div>
            <div class="field">
              <label for="ah_altura">Altura (cm)</label>
              <input id="ah_altura" type="number" placeholder="170">
            </div>
          </div>
          <hr class="section-divider">

          {{-- Diagnostico --}}
          <div class="trat-section">
            <h4 class="trat-section-title">Diagnóstico</h4>
            <input id="ah_diagnostico" placeholder="Ej. Gastritis aguda, Diabetes mellitus tipo 2">
          </div>
        </div>
        <div class="btn-container" style="margin-top:14px;">
          <button type="submit" class="confirm-btn">
            <img src="/img/guardar.png" class="btn-icon" alt="Guardar" style="width:24px; height:24px;">
          </button>
          <button type="button" class="cancel-btn" style="background-color: orange;" onmouseover="this.style.backgroundColor='darkorange'" onmouseout="this.style.backgroundColor='orange'">
            <img src="/img/limpiar.png" class="btn-icon" alt="Limpiar">
          </button>
        </div>

      </form>
    </div>

  </section>


  {{-- ==========================
        SUBIR DOCUMENTOS
      =========================== --}}
  <section class="med-docs-section">

    <div class="med-card">
      <h3 class="med-card-title">Subir documentos</h3>

      <div class="med-docs-patient">
        <p><strong>Paciente:</strong> <span id="docsPaciente">Hugo García</span></p>
        <p class="muted">En el futuro se llenará automáticamente según el expediente seleccionado.</p>
      </div>

      <form id="docsForm" class="form-container">

        <div class="trat-grid" style="margin-bottom:12px;">
          <div class="field">
            <label for="doc_tipo">Tipo</label>
            <select id="doc_tipo">
              <option value="">Seleccione...</option>
              <option>Radiografía</option>
              <option>Análisis</option>
              <option>Receta</option>
              <option>Referencia</option>
              <option>Otro</option>
            </select>
          </div>

          <div class="field">
            <label for="doc_titulo">Título</label>
            <input id="doc_titulo" placeholder="Ej. Radiografía de tórax">
          </div>
        </div>

        <div class="med-dropzone">
          <p>Arrastra y suelta archivos aquí o <span class="med-link">haz clic</span> para seleccionarlos.</p>
          <p class="med-dropzone-hint">Acepta PDF, imágenes y documentos comunes. Máx. 10 MB por archivo.</p>
          <input type="file" id="doc_files" multiple style="display:none;">
        </div>

        <p id="docEmpty" class="muted" style="text-align:center;margin-top:8px;">
          Sin archivos seleccionados.
        </p>

        <div class="btn-container" style="margin-top:14px;">
          <button type="submit" class="confirm-btn">
            <img src="/img/guardar.png" class="btn-icon" alt="Subir" style="width:24px; height:24px;">
          </button>
          <button type="button" class="cancel-btn" id="docClearBtn" style="background-color: orange;" onmouseover="this.style.backgroundColor='darkorange'" onmouseout="this.style.backgroundColor='orange'">
            <img src="/img/limpiar.png" class="btn-icon" alt="Limpiar">
          </button>
        </div>

      </form>
    </div>

    {{-- Documentos ya subidos (demo) --}}
    <div class="med-card">
      <h3 class="med-card-title">Documentos del paciente</h3>

      <div id="docsList" class="docs-list">
        <div class="doc-item">
          <img src="/img/documento.png" class="doc-icon" alt="PDF" style="width:24px; height:24px;">
          <span class="doc-name">Laboratorio_general.pdf</span>
          <a href="#" class="doc-btn" target="_blank"><img src="/img/visualizar.png" alt="Ver detalle" style="width:20px; height:20px;"></a>
        </div>

        <div class="doc-item">
          <img src="/img/documento.png" class="doc-icon" alt="IMG" style="width:24px; height:24px;">
          <span class="doc-name">Rx_Torax_2025.jpg</span>
          <a href="#" class="doc-btn" target="_blank"><img src="/img/visualizar.png" alt="Ver detalle" style="width:20px; height:20px;"></a>
        </div>
      </div>

      <p id="docsListEmpty" class="muted" style="text-align:center;margin-top:8px; display:none;">
        Aún no hay documentos.
      </p>
    </div>

  </section>


  {{-- ==========================
        MODAL DETALLE HISTORIAL
      =========================== --}}
  <div id="medHistoryModal" class="modal hidden">
    <div class="modal-content modal-lg">

      <h3 class="modal-title">Detalle de historial</h3>

      <div class="history-modal-body">

        <div class="history-section">
          <h4>Datos de la consulta</h4>
          <p><strong>Fecha:</strong> <span id="mh_fecha">—</span></p>
          <p><strong>Paciente:</strong> <span id="mh_paciente">—</span></p>
          <p><strong>Diagnóstico:</strong> <span id="mh_dx">—</span></p>
        </div>

        <hr class="section-divider">

        <div class="history-section">
          <h4>Motivo / Observaciones</h4>
          <p id="mh_motivo">—</p>
        </div>

        <hr class="section-divider">

        <div class="history-section history-grid">
          <div>
            <h4>Alergias</h4>
            <p id="mh_alergias">—</p>
          </div>
          <div>
            <h4>Antecedentes</h4>
            <p id="mh_antecedentes">—</p>
          </div>
        </div>

        <hr class="section-divider">

        <div class="history-section">
          <h4>Signos vitales</h4>
          <ul class="vitals-list">
            <li><strong>Temperatura:</strong> <span id="mh_temp">—</span></li>
            <li><strong>Presión arterial:</strong> <span id="mh_press">—</span></li>
            <li><strong>Pulso:</strong> <span id="mh_pulse">—</span></li>
            <li><strong>Frecuencia respiratoria:</strong> <span id="mh_fr">—</span></li>
            <li><strong>Saturación de oxígeno:</strong> <span id="mh_spo2">—</span></li>
            <li><strong>Peso:</strong> <span id="mh_peso">—</span></li>
            <li><strong>Altura:</strong> <span id="mh_altura">—</span></li>
          </ul>
        </div>

        <hr class="section-divider">

        <div class="history-section">
          <h4>Tratamiento indicado</h4>
          <p id="mh_trat">—</p>
        </div>

        <hr class="section-divider">

        <div class="history-section">
          <h4>Documentos asociados</h4>
          <p id="mh_docs">—</p>
        </div>

      </div>

      <div class="btn-container" style="margin-top:14px;">
        <button type="button" class="modal-cancel-btn" id="medHistoryCloseBtn">
          <img src="/img/cancelar.png" class="btn-icon" alt="Cerrar">
        </button>
      </div>

    </div>
  </div>

</main>

{{-- ================= JS (SOLO MAQUETA) ================= --}}
<script>
document.addEventListener('DOMContentLoaded', () => {
  // ----------- Estadística de citas de hoy (demo) -----------
  const statCitasHoy = document.getElementById('statCitasHoy');
  const hoy = new Date().toISOString().slice(0,10);

  const DEMO_CITAS = [
    { fecha: hoy, paciente: 'Hugo García' },
    { fecha: hoy, paciente: 'María López' },
    { fecha: hoy, paciente: 'Juan Pérez' },
    { fecha: hoy, paciente: 'Ana Díaz' },
    { fecha: hoy, paciente: 'Pedro Torres' },
  ];
  statCitasHoy.textContent = DEMO_CITAS.length;

  // ----------- Gráfica simple (barras) -----------
  const miniChart = document.getElementById('miniChart');
  const dataSemana = [
    { label: 'Lun', value: 3 },
    { label: 'Mar', value: 4 },
    { label: 'Mié', value: 5 },
    { label: 'Jue', value: 2 },
    { label: 'Vie', value: 6 },
    { label: 'Sáb', value: 1 },
    { label: 'Dom', value: 0 },
  ];
  const maxVal = Math.max(...dataSemana.map(d => d.value)) || 1;

  dataSemana.forEach(d => {
    const bar = document.createElement('div');
    bar.className = 'mini-chart-bar';
    bar.style.height = (d.value / maxVal * 100) + '%';
    bar.innerHTML = `<span class="mini-chart-value">${d.value}</span>
                     <span class="mini-chart-label">${d.label}</span>`;
    miniChart.appendChild(bar);
  });

  // ----------- Filtro por paciente / enfermedad -----------
  const fPaciente    = document.getElementById('f_paciente');
  const fEnfermedad  = document.getElementById('f_enfermedad');
  const btnBuscarHist = document.getElementById('btnBuscarHist');
  const histRows     = document.getElementById('medHistoryRows');
  const histEmpty    = document.getElementById('medHistoryEmpty');

  function applyHistoryFilter() {
    const termPac = fPaciente.value.trim().toLowerCase();
    const termEnf = fEnfermedad.value.trim().toLowerCase();

    let visibleCount = 0;
    histRows.querySelectorAll('tr').forEach(tr => {
      const tds = tr.querySelectorAll('td');
      if (!tds.length) return;
      const fecha = tds[0].textContent.toLowerCase();
      const pac   = tds[1].textContent.toLowerCase();
      const mot   = tds[2].textContent.toLowerCase();
      const dx    = tds[3].textContent.toLowerCase();

      const okPac = !termPac || pac.includes(termPac) || fecha.includes(termPac);
      const okEnf = !termEnf || dx.includes(termEnf) || mot.includes(termEnf);

      const show = okPac && okEnf;
      tr.style.display = show ? '' : 'none';
      if (show) visibleCount++;
    });

    histEmpty.style.display = visibleCount ? 'none' : 'block';
  }

  btnBuscarHist.addEventListener('click', applyHistoryFilter);
  fEnfermedad.addEventListener('input', applyHistoryFilter);
  fPaciente.addEventListener('input', applyHistoryFilter);

  // ----------- Autocomplete de diagnóstico (maqueta) -----------
  const DX_DEMO = [
    "Gastritis aguda",
    "Diabetes mellitus tipo 2",
    "Hipertensión arterial",
    "Asma persistente",
    "Migraña crónica",
    "Colitis nerviosa",
    "Lumbalgia mecánica",
    "Infección urinaria",
    "Resfriado común",
    "Amigdalitis aguda"
  ];

  const dxInput = document.getElementById('f_enfermedad');
  const dxBox   = document.getElementById('dxSuggestions');

  function renderDxSuggestions(term) {
    dxBox.innerHTML = '';

    const clean = term.trim().toLowerCase();
    let matches = [];

    // Si no hay texto, mostrar todas las opciones
    if (!clean) {
      matches = DX_DEMO;
    } else {
      matches = DX_DEMO.filter(dx =>
        dx.toLowerCase().includes(clean)
      );
    }

    if (!matches.length) {
      dxBox.classList.add('hidden');
      return;
    }

    matches.forEach(dx => {
      const div = document.createElement('div');
      div.className = 'dx-suggestion-item';
      div.textContent = dx;
      div.addEventListener('click', () => {
        dxInput.value = dx;
        dxBox.classList.add('hidden');
        applyHistoryFilter();
      });
      dxBox.appendChild(div);
    });

    dxBox.classList.remove('hidden');
  }

  // Mostrar lista completa al enfocar o hacer click
  dxInput.addEventListener('focus', () => {
    renderDxSuggestions('');
  });

  dxInput.addEventListener('click', () => {
    renderDxSuggestions('');
  });

  // Filtrar mientras escribe
  dxInput.addEventListener('input', () => {
    renderDxSuggestions(dxInput.value);
    applyHistoryFilter();
  });

  // Cerrar el cuadrito si hace click fuera
  document.addEventListener('click', (e) => {
    if (!dxBox.contains(e.target) && e.target !== dxInput) {
      dxBox.classList.add('hidden');
    }
  });

  // ----------- Modal detalle historial -----------
  const historyModal   = document.getElementById('medHistoryModal');
  const historyClose   = document.getElementById('medHistoryCloseBtn');

  const spanFecha   = document.getElementById('mh_fecha');
  const spanPac     = document.getElementById('mh_paciente');
  const spanDx      = document.getElementById('mh_dx');
  const spanMotivo  = document.getElementById('mh_motivo');
  const spanAlerg   = document.getElementById('mh_alergias');
  const spanAnteced = document.getElementById('mh_antecedentes');
  const spanTemp    = document.getElementById('mh_temp');
  const spanPress   = document.getElementById('mh_press');
  const spanPulse   = document.getElementById('mh_pulse');
  const spanFR      = document.getElementById('mh_fr');
  const spanSpO2    = document.getElementById('mh_spo2');
  const spanPeso    = document.getElementById('mh_peso');
  const spanAltura  = document.getElementById('mh_altura');
  const spanTrat    = document.getElementById('mh_trat');
  const spanDocs    = document.getElementById('mh_docs');

  function openHistoryModal(btn) {
    spanFecha.textContent   = btn.dataset.fecha || '—';
    spanPac.textContent     = btn.dataset.paciente || '—';
    spanDx.textContent      = btn.dataset.dx || '—';
    spanMotivo.textContent  = btn.dataset.motivo || '—';
    spanAlerg.textContent   = btn.dataset.alergias || '—';
    spanAnteced.textContent = btn.dataset.antecedentes || '—';
    spanTemp.textContent    = btn.dataset.temp || '—';
    spanPress.textContent   = btn.dataset.press || '—';
    spanPulse.textContent   = btn.dataset.pulse || '—';
    spanFR.textContent      = btn.dataset.fr || '—';
    spanSpO2.textContent    = btn.dataset.spo2 || '—';
    spanPeso.textContent    = btn.dataset.peso || '—';
    spanAltura.textContent  = btn.dataset.altura || '—';
    spanTrat.textContent    = btn.dataset.trat || '—';
    spanDocs.textContent    = btn.dataset.docs || '—';

    historyModal.classList.remove('hidden');
  }

  document.querySelectorAll('.med-history-detail').forEach(btn => {
    btn.addEventListener('click', () => openHistoryModal(btn));
  });

  function closeHistoryModal() {
    historyModal.classList.add('hidden');
  }

  historyClose.addEventListener('click', closeHistoryModal);
  historyModal.addEventListener('click', e => {
    if (e.target === historyModal) closeHistoryModal();
  });

  // ----------- Subir documentos (maqueta) -----------
  const dropzone = document.querySelector('.med-dropzone');
  const fileInput = document.getElementById('doc_files');
  const docEmpty  = document.getElementById('docEmpty');
  const docClear  = document.getElementById('docClearBtn');

  dropzone.addEventListener('click', () => fileInput.click());

  fileInput.addEventListener('change', () => {
    if (fileInput.files.length) {
      docEmpty.textContent = `${fileInput.files.length} archivo(s) seleccionado(s).`;
    } else {
      docEmpty.textContent = 'Sin archivos seleccionados.';
    }
  });

  docClear.addEventListener('click', () => {
    fileInput.value = '';
    docEmpty.textContent = 'Sin archivos seleccionados.';
  });
});
</script>

@endsection
