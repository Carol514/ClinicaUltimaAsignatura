@extends('layouts.app')
@section('title','Alta de historial')

@section('content')
<main class="dashboard">
  <h2>Alta de historial</h2>

  {{-- === FORMULARIO PRINCIPAL === --}}
  <form id="altaForm" class="panel form-container alta-panel" style="max-width: 900px;">

    {{-- Datos básicos --}}
    <div class="grid-2">
      <div>
        <label>Paciente</label>
        <input id="pacienteNombre" placeholder="Nombre completo" required>
        <input type="hidden" id="pacienteId" name="patient_id">
      </div>
      <div>
        <label>Fecha</label>
        <input id="fechaAlta" type="date" required>
      </div>
    </div>

    <div class="sep"></div>

    <div>
      <label>Motivo / Observaciones</label>
      <textarea id="motivo" rows="3" placeholder="Motivo de la consulta / resumen"></textarea>
    </div>

    {{-- Alergias y antecedentes --}}
    <div class="grid-2">
      <div>
        <label>Alergias</label>
        <textarea id="alergias" rows="4" placeholder="Ej. Penicilina, mariscos, polen..."></textarea>
      </div>
      <div>
        <label>Antecedentes</label>
        <textarea id="antecedentes" rows="4" placeholder="Ej. Diabetes, hipertensión, cirugías previas..."></textarea>
      </div>
    </div>

    <div class="sep"></div>

    {{-- Signos vitales --}}
    <div class="vitals-grid">
      <div>
        <label>Temperatura (°C)</label>
        <input id="v_temp" type="number" step="0.1" placeholder="36.5">
      </div>
      <div>
        <label>Presión (mmHg)</label>
        <input id="v_ta" placeholder="120/80">
      </div>
      <div>
        <label>Pulso (lpm)</label>
        <input id="v_pulso" type="number" inputmode="numeric" placeholder="75">
      </div>
      <div>
        <label>Respiración (rpm)</label>
        <input id="v_resp" type="number" inputmode="numeric" placeholder="16">
      </div>
      <div>
        <label>SpO₂ (%)</label>
        <input id="v_spo2" type="number" inputmode="numeric" placeholder="98">
      </div>
      <div>
        <label>Peso (kg)</label>
        <input id="v_peso" type="number" step="0.1" inputmode="decimal" placeholder="70.0">
      </div>
    </div>

    {{-- Botones --}}
    <div class="btn-row" style="margin-top:16px;">
      <button class="confirm-btn" type="submit" id="btnGuardar">Guardar alta</button>
      <button class="cancel-btn"  type="button" id="btnLimpiar">Limpiar</button>
      <a class="cancel-btn btn-wide" id="volverBtn" href="{{ route('medico.panel') }}">Volver</a>
    </div>
  </form>

  {{-- === Resumen demo === --}}
  <section class="panel" style="max-width: 900px; margin-top:16px;">
    <h3 style="text-align:left;margin-top:0;">Resumen (demo)</h3>
    <div id="preview" class="list-container">
      <p class="muted">Completa el formulario y guarda para ver el resumen.</p>
    </div>
  </section>
</main>

<script>
(() => {
  const form = document.getElementById('altaForm');
  const preview = document.getElementById('preview');
  const btnLimpiar = document.getElementById('btnLimpiar');
  const $ = id => document.getElementById(id);

  // Cargar ?p= del URL si existe
  const p = new URLSearchParams(location.search).get('p') || '';
  
  // Update Volver button to preserve patient context
  if (p) {
    const volverBtn = document.getElementById('volverBtn');
    if (volverBtn) {
      volverBtn.href = `{{ route('medico.panel') }}?p=${encodeURIComponent(p)}`;
    }
  }

  // Auto-fill patient data and vital signs if patient parameter provided
  if (p) {
    loadPatientData(p);
  }

  async function resolvePatientId(query) {
    if (!query) return null;
    // If numeric id or UUID-like id passed, return it directly
    const uuidRe = /^[0-9a-fA-F]{8}-[0-9a-fA-F]{4}-[0-9a-fA-F]{4}-[0-9a-fA-F]{4}-[0-9a-fA-F]{12}$/;
    if (/^\d+$/.test(query) || uuidRe.test(query)) return query;
    
    try {
      const r = await fetch(`/medico/api/patients?query=${encodeURIComponent(query)}`, { 
        credentials: 'same-origin', 
        headers: { 'Accept': 'application/json' } 
      });
      if (!r.ok) return null;
      const list = await r.json();
      return (list && list.length) ? list[0].id : null;
    } catch (e) { 
      console.error('Error resolving patient:', e);
      return null; 
    }
  }

  async function loadPatientData(patientQuery) {
    try {
      // First resolve the patient ID and get patient details
      const patientId = await resolvePatientId(patientQuery);
      if (!patientId) {
        console.warn('Could not resolve patient ID for:', patientQuery);
        return;
      }

      // Get patient details
      const patientRes = await fetch(`/medico/api/patients?query=${encodeURIComponent(patientId)}`, {
        credentials: 'same-origin',
        headers: { 'Accept': 'application/json' }
      });

      if (patientRes.ok) {
        const patients = await patientRes.json();
        if (patients && patients.length > 0) {
          const patient = patients[0];
          // Fill patient name and ID
          $('pacienteNombre').value = patient.name || '';
          $('pacienteId').value = patient.id || '';
          console.log('Loaded patient:', patient.name);
        }
      }

      // Get latest vital signs using medico endpoint
      const vitalsRes = await fetch(`/medico/api/vitals?patient_id=${encodeURIComponent(patientId)}`, {
        credentials: 'same-origin',
        headers: { 'Accept': 'application/json' }
      });

      if (vitalsRes.ok) {
        const vitals = await vitalsRes.json();
        if (vitals && vitals.length > 0) {
          // Use the most recent vital signs (first in array since they're ordered by date desc)
          const latest = vitals[0];
          
          if (latest.temp) $('v_temp').value = latest.temp;
          if (latest.sbp && latest.dbp) $('v_ta').value = `${latest.sbp}/${latest.dbp}`;
          if (latest.pulso) $('v_pulso').value = latest.pulso;
          if (latest.fr) $('v_resp').value = latest.fr;
          if (latest.spo2) $('v_spo2').value = latest.spo2;
          if (latest.peso) $('v_peso').value = latest.peso;
          
          console.log('Loaded vital signs from:', latest.fecha);
        }
      }

      // Load preview with database data after patient is loaded
      await loadPreviewData(patientId);
    } catch (error) {
      console.error('Error loading patient data:', error);
    }
  }

  async function loadPreviewData(patientId) {
    if (!patientId) return;
    
    const mockData = {
      paciente: $('pacienteNombre').value || 'Cargando...',
      patient_id: patientId,
      fecha: $('fechaAlta').value || new Date().toISOString().split('T')[0],
      alergias: '',
      antecedentes: '',
      v: { temp: '', ta: '', pulso: '', resp: '', spo2: '', peso: '' }
    };
    
    await renderPreview(mockData);
  }

  async function renderPreview(data){
    if (!data.patient_id) {
      // Fallback for demo data without patient ID
      preview.innerHTML = `
        <div class="list-item">
          <strong>Paciente:</strong> ${data.paciente} <br>
          <strong>Fecha:</strong> ${data.fecha}
        </div>
        <div class="list-item">
          <strong>Alergias:</strong> ${data.alergias || 'Ninguna'}<br>
          <strong>Antecedentes:</strong> ${data.antecedentes || 'Ninguno'}
        </div>
        <div class="list-item">
          <strong>Signos vitales:</strong><br>
          Temp: ${data.v.temp || '—'} °C · TA: ${data.v.ta || '—'} · Pulso: ${data.v.pulso || '—'} lpm ·
          Resp: ${data.v.resp || '—'} rpm · SpO₂: ${data.v.spo2 || '—'} % · Peso: ${data.v.peso || '—'} kg
        </div>
      `;
      return;
    }

    try {
      // Fetch real data from database
      const [vitalsRes, allergiesRes, historyRes] = await Promise.all([
        fetch(`/medico/api/vitals?patient_id=${encodeURIComponent(data.patient_id)}`, {
          credentials: 'same-origin',
          headers: { 'Accept': 'application/json' }
        }),
        fetch(`/medico/api/allergies?patient_id=${encodeURIComponent(data.patient_id)}`, {
          credentials: 'same-origin',
          headers: { 'Accept': 'application/json' }
        }).catch(() => ({ ok: false })), // Handle if endpoint doesn't exist yet
        fetch(`/medico/api/medical-history?patient_id=${encodeURIComponent(data.patient_id)}`, {
          credentials: 'same-origin',
          headers: { 'Accept': 'application/json' }
        }).catch(() => ({ ok: false })) // Handle if endpoint doesn't exist yet
      ]);

      let vitalsData = [], allergiesData = [], historyData = [];
      
      if (vitalsRes.ok) {
        vitalsData = await vitalsRes.json();
      }
      
      if (allergiesRes.ok) {
        allergiesData = await allergiesRes.json();
      }
      
      if (historyRes.ok) {
        historyData = await historyRes.json();
      }

      // Display real database data
      const latestVitals = vitalsData.length > 0 ? vitalsData[0] : null;
      const allergiesList = allergiesData.map(a => a.allergen || a.name).join(', ') || 'Ninguna';
      
      // Group and deduplicate medical history
      const antecedentes = historyData
        .filter(h => h.condition === 'Antecedentes' && h.details)
        .map(h => h.details.trim())
        .filter((value, index, self) => self.indexOf(value) === index) // Remove duplicates
        .join('; ');
        
      const motivos = historyData
        .filter(h => h.condition !== 'Antecedentes' && h.condition)
        .map(h => h.condition.trim())
        .slice(0, 3) // Show only last 3 motivos
        .join('; ');
        
      const historyDisplay = antecedentes || 'Ninguno';

      preview.innerHTML = `
        <div class="list-item">
          <strong>Paciente:</strong> ${data.paciente} <br>
          <strong>Fecha:</strong> ${data.fecha}
        </div>
        <div class="list-item">
          <strong>Alergias (BD):</strong> ${allergiesList}<br>
          <strong>Antecedentes (BD):</strong> ${historyDisplay}
          ${motivos ? `<br><strong>Motivos recientes:</strong> ${motivos}` : ''}
        </div>
        <div class="list-item">
          <strong>Signos vitales más recientes (BD):</strong><br>
          ${latestVitals ? 
            `Temp: ${latestVitals.temp || '—'} °C · TA: ${latestVitals.sbp && latestVitals.dbp ? latestVitals.sbp + '/' + latestVitals.dbp : '—'} · Pulso: ${latestVitals.pulso || '—'} lpm · Resp: ${latestVitals.fr || '—'} rpm · SpO₂: ${latestVitals.spo2 || '—'} % · Peso: ${latestVitals.peso || '—'} kg<br><small>Fecha: ${latestVitals.fecha || '—'}</small>` 
            : 'No hay signos vitales registrados'
          }
        </div>
      `;
    } catch (error) {
      console.error('Error fetching preview data:', error);
      // Fallback to form data on error
      preview.innerHTML = `
        <div class="list-item">
          <strong>Paciente:</strong> ${data.paciente} <br>
          <strong>Fecha:</strong> ${data.fecha}
        </div>
        <div class="list-item">
          <strong>Alergias:</strong> ${data.alergias || 'Ninguna'}<br>
          <strong>Antecedentes:</strong> ${data.antecedentes || 'Ninguno'}
        </div>
        <div class="list-item">
          <strong>Signos vitales:</strong><br>
          Temp: ${data.v.temp || '—'} °C · TA: ${data.v.ta || '—'} · Pulso: ${data.v.pulso || '—'} lpm ·
          Resp: ${data.v.resp || '—'} rpm · SpO₂: ${data.v.spo2 || '—'} % · Peso: ${data.v.peso || '—'} kg
          <br><small>(Error cargando datos de BD)</small>
        </div>
      `;
    }
  }

  form.addEventListener('submit', (e) => {
    e.preventDefault();
    const data = {
      paciente: $('pacienteNombre').value.trim(),
      patient_id: $('pacienteId').value || null,
      fecha: $('fechaAlta').value,
      alergias: $('alergias').value.trim(),
      antecedentes: $('antecedentes').value.trim(),
      motivo: $('motivo').value.trim(),
      v: {
        temp: $('v_temp').value,
        ta: $('v_ta').value.trim(),
        pulso: $('v_pulso').value,
        resp: $('v_resp').value,
        spo2: $('v_spo2').value,
        peso: $('v_peso').value
      }
    };
    if (!data.paciente || !data.fecha){
      alert('Paciente y Fecha son obligatorios.');
      return;
    }
    // If patient_id present, submit to backend medico API. If not, try resolving by name first.
    (async ()=>{
      if (!data.patient_id && data.paciente) {
        // try resolve by name (search endpoint)
        try{
          const r = await fetch(`/medico/api/patients?query=${encodeURIComponent(data.paciente)}`, { credentials:'same-origin', headers:{'Accept':'application/json'} });
          if (r.ok){
            const list = await r.json();
            if (list && list.length){
              data.patient_id = list[0].id;
              $('pacienteId').value = data.patient_id;
            }
          }
        }catch(e){ /* ignore, will fallback to demo */ }
      }

      if (data.patient_id){
        try{
          const tokenMeta = document.querySelector('meta[name="csrf-token"]');
          const headers = { 'Accept':'application/json', 'Content-Type':'application/json' };
          if (tokenMeta) headers['X-CSRF-TOKEN'] = tokenMeta.getAttribute('content');
          
          // Parse blood pressure
          let sbp = null, dbp = null;
          if (data.v.ta && data.v.ta.includes('/')) {
            const bp = data.v.ta.split('/');
            sbp = parseInt(bp[0]) || null;
            dbp = parseInt(bp[1]) || null;
          }
          
          const payload = {
            patient_id: data.patient_id,
            encounter_dt: data.fecha,
            reason: data.motivo || null, // motivo/observaciones -> condition
            allergies: data.alergias || null,
            medical_history: data.antecedentes || null, // antecedentes -> details
            vitals: {
              temp: parseFloat(data.v.temp) || null,
              sbp: sbp,
              dbp: dbp,
              hr: parseInt(data.v.pulso) || null,
              rr: parseInt(data.v.resp) || null,
              spo2: parseInt(data.v.spo2) || null,
              weight: parseFloat(data.v.peso) || null
            }
          };
          
          // Debug: log the vitals data being sent
          console.log('Vitals being sent:', payload.vitals);
          console.log('Peso field value:', data.v.peso);
          
          const res = await fetch('/medico/api/alta-historial', { method:'POST', credentials:'same-origin', headers, body: JSON.stringify(payload) });
          if (!res.ok) {
            const errorData = await res.json();
            throw new Error(errorData.message || 'Save failed');
          }
          const j = await res.json();
          await renderPreview(data);
          alert('✅ Alta de historial guardada exitosamente.');
          console.log('Save results:', j.results);
          return;
        }catch(err){
          console.error('Save error:', err);
          await renderPreview(data);
          alert('❌ Error guardando: ' + err.message);
          return;
        }
      } else {
        await renderPreview(data);
        alert('✅ Alta de historial guardada (demo).');
      }
    })();
  });

  btnLimpiar.addEventListener('click', () => {
    form.reset();
    preview.innerHTML = '<p class="muted">Formulario limpio.</p>';
    // Reset date to today after clearing
    setTodayDate();
  });

  // Set today's date as default
  function setTodayDate() {
    const today = new Date().toISOString().split('T')[0];
    $('fechaAlta').value = today;
  }

  // Set initial date
  setTodayDate();
})();
</script>
@endsection
