@extends('layouts.app')
@section('title','Agendar cita')

@section('content')
<main class="dashboard">
  <h2>Agendar cita</h2>

  <section class="panel">
    <form class="form-container agendar-cita" id="citaForm">
      <div class="fields">
        <div class="field">
          <label for="pac">Paciente</label>
          <input id="pac" placeholder="Nombre completo / CURP / Teléfono">
        </div>

        <div class="field">
          <label for="doctor">Doctor</label>
          <select id="doctor" required>
            <option value="" selected disabled>Seleccione…</option>
            <!-- options populated from server: /recepcionista/api/medicos -->
          </select>
        </div>

        <div class="field">
          <label for="fecha">Fecha</label>
          <input type="date" id="fecha" required>
        </div>

        <div class="field">
          <label for="hora">Hora</label>
          <input type="time" id="hora" required>
        </div>

        <div class="field">
          <label for="motivo">Motivo</label>
          <input id="motivo" placeholder="Ej. control, dolor, resultados">
        </div>
      </div>

      <div class="btn-container acciones">
        <button class="confirm-btn" type="submit">Guardar cita</button>
        <button class="cancel-btn" type="reset">Limpiar</button>
        <a href="{{ route('recepcionista.panel') }}" class="cancel-btn">Volver</a>
      </div>
    </form>
  </section>
</main>

  <script>
  (function(){
    const pParam = new URLSearchParams(location.search).get('p') || '';
    const pacInput = document.getElementById('pac');
    if (pParam) pacInput.value = pParam;

    async function findPatientByText(text){
      if (!text) return [];
      try{
        const res = await fetch('/recepcionista/api/patients?'+new URLSearchParams({ q: text }), { headers:{ 'Accept':'application/json' }, credentials: 'same-origin' });
        if (!res.ok){ const txt = await res.text(); console.error('findPatientByText error', res.status, txt); return []; }
  try{ const body = await res.clone().json(); return body.data || []; }catch(e){ const txt = await res.clone().text(); console.error('Non-JSON findPatientByText', txt); return []; }
      }catch(e){ console.error(e); return []; }
    }

    // populate doctor select from backend
    async function loadMedicos(){
      try{
        const res = await fetch('/recepcionista/api/medicos', { headers:{ 'Accept':'application/json' }, credentials: 'same-origin' });
        if (!res.ok){ console.error('Failed to load medicos', res.status); return; }
        const body = await res.clone().json().catch(()=>null);
        const list = (body && body.data) ? body.data : [];
        const sel = document.getElementById('doctor');
        // remove existing options except the placeholder
        Array.from(sel.querySelectorAll('option')).forEach(o=>{ if (o.value) o.remove(); });
        list.forEach(m => {
          const opt = document.createElement('option');
          opt.value = m.id;
          opt.textContent = m.name;
          sel.appendChild(opt);
        });
      }catch(err){ console.error('loadMedicos error', err); }
    }

    // call on load
    loadMedicos();

    document.getElementById('citaForm').addEventListener('submit', async e=>{
      e.preventDefault();
      const pac    = pacInput.value.trim();
      const doctor = document.getElementById('doctor').value;
      const fecha  = document.getElementById('fecha').value;
      const hora   = document.getElementById('hora').value;
      const motivo = document.getElementById('motivo').value.trim();

      if (!pac)      return alert('Escribe el paciente.');
      if (!doctor)   return alert('Selecciona el doctor.');
      if (!fecha)    return alert('Selecciona la fecha.');
      if (!hora)     return alert('Selecciona la hora.');

      // try to resolve patient id by searching
      const matches = await findPatientByText(pac);
      if (matches.length === 0) return alert('No se encontró el paciente. Busca por nombre/CURP/teléfono y selecciona el registro correcto.');
      if (matches.length > 1) return alert('Se encontraron varios pacientes. Precisa más (CURP o teléfono) para identificar al paciente.');

  const patient = matches[0];
  const scheduled_at = fecha + ' ' + hora;
  const clinician_id = document.getElementById('doctor').value || null;
  const payload = { patient_id: patient.id, scheduled_at, reason: motivo, clinician_id };

      try{
        const token = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');
        const r = await fetch('/recepcionista/api/appointments', {
          method: 'POST',
          credentials: 'same-origin',
          headers: { 'Accept':'application/json','Content-Type':'application/json', 'X-CSRF-TOKEN': token || '' },
          body: JSON.stringify(payload)
        });
        if (r.ok){
          alert('✅ Cita creada correctamente');
          e.target.reset();
          if (pParam) pacInput.value = pParam;
          return;
        }
        if (r.status === 422){
          const err = await r.json();
          const messages = [];
          for (const k in err.errors || {}) messages.push((err.errors[k]||[]).join(', '));
          return alert('Errores: ' + messages.join(' • '));
        }
        const txt = await r.text();
        alert('Error al crear cita: '+r.status+' '+txt);
      }catch(ex){ alert('Error de red: '+ex.message); }
    });
  })();
  </script>
@endsection
