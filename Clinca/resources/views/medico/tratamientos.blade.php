@extends('layouts.app')
@section('title','Editar tratamientos')

@section('content')
<main class="dashboard">
  <h2>Editar tratamientos</h2>

    {{-- Formulario de tratamiento --}}
  <form id="trat-form" class="form-container">

    {{-- Tratamiento actual --}}
    <div class="trat-section">
      <label for="t_actual">Tratamiento actual</label>
      <textarea id="t_actual" rows="2" placeholder="Ej. Amoxicilina 500 mg c/8h" required></textarea>
    </div>

    {{-- Nuevo tratamiento: detalles estructurados --}}
    <div class="trat-section">
      <h3 class="trat-section-title">Nuevo tratamiento</h3>

      <div class="trat-grid">
        <div class="field">
          <label for="med_name">Medicamento</label>
          <input id="med_name" placeholder="Ej. Amoxicilina" />
        </div>

        <div class="field">
          <label for="med_dose">Dosis</label>
          <input id="med_dose" placeholder="Ej. 500" />
        </div>

        <div class="field">
          <label for="med_unit">Unidad</label>
          <select id="med_unit">
            <option value="">Seleccione...</option>
            <option>mg</option>
            <option>ml</option>
            <option>g</option>
            <option>UI</option>
          </select>
        </div>

        <div class="field">
          <label for="med_freq">Frecuencia</label>
          <input id="med_freq" placeholder="Ej. cada 8 horas" />
        </div>

        <div class="field">
          <label for="med_day">Día de inicio</label>
          <input type="date" id="med_day" />
        </div>

        <div class="field">
          <label for="med_time">Hora</label>
          <input type="time" id="med_time" />
        </div>
      </div>

      <label for="t_nuevo" style="margin-top:10px;">Resumen del tratamiento nuevo</label>
      <textarea id="t_nuevo" rows="2"
        placeholder="Ej. Amoxicilina 500 mg c/8h por 7 días"></textarea>
    </div>

    {{-- Resultados relacionados (lab, rayos X, etc.) --}}
    <div class="trat-section">
      <h3 class="trat-section-title">Resultados relacionados</h3>

      <div class="trat-grid">
        <div class="field">
          <label for="tipo_resultado">Tipo de resultado</label>
          <select id="tipo_resultado">
            <option value="">Seleccione...</option>
            <option>Laboratorio</option>
            <option>Rayos X</option>
            <option>Ultrasonido</option>
            <option>Tomografía</option>
            <option>Otro</option>
          </select>
        </div>

        <div class="field">
          <label for="fecha_resultado">Fecha del estudio</label>
          <input type="date" id="fecha_resultado" />
        </div>
      </div>

      <label for="t_notas" style="margin-top:10px;">Notas / interpretación de resultados</label>
      <textarea id="t_notas" rows="3"
        placeholder="Ej. Neumonía en Rx, leucocitos elevados, etc."></textarea>
    </div>

    <div class="btn-container" style="margin-top:12px;">
      <button class="confirm-btn" type="submit">Guardar cambio</button>
      <a class="cancel-btn" id="volverBtn" href="{{ route('medico.panel') }}">Volver</a>
    </div>
  </form>

</main>

<script>
  // Cargar paciente desde la URL (?p=)
  const p = new URLSearchParams(location.search).get('p') || '';
  document.getElementById('tratPaciente').value = p;
  
  // Update Volver button to preserve patient context and handle different user roles
  if (p) {
    const volverBtn = document.getElementById('volverBtn');
    if (volverBtn) {
      // Check if there's a 'from' parameter to determine where to go back
      const fromParam = new URLSearchParams(location.search).get('from');
      let backUrl;
      
      if (fromParam === 'enfermera') {
        backUrl = `{{ route('enfermera.panel') }}?p=${encodeURIComponent(p)}`;
      } else {
        backUrl = `{{ route('medico.panel') }}?p=${encodeURIComponent(p)}`;
      }
      
      volverBtn.href = backUrl;
    }
  }

  const bitacora = document.getElementById('bitacora');
   const tratPacienteInput = document.getElementById('tratPaciente');
  function pushBitacora(oldTxt, newTxt, note){
    if (bitacora.querySelector('.muted')) bitacora.innerHTML = '';
    const row = document.createElement('div');
    row.className = 'list-item';
    const when = new Date().toLocaleString();
    row.innerHTML = `
      <div style="display:flex;flex-direction:column;gap:4px;">
        <div><b>${when}</b> — <span class="muted">${p || '(paciente)'}</span></div>
        <div><b>Actual:</b> ${oldTxt || '(vacío)'}</div>
        <div><b>Nuevo:</b> ${newTxt || '(vacío)'}</div>
        <div><b>Notas:</b> ${note || '(sin notas)'}</div>
      </div>
    `;
    bitacora.prepend(row);
  }

  // Guardar (POST to /medico/api/tratamientos)
  document.getElementById('trat-form').addEventListener('submit', async (e)=>{
    e.preventDefault();
    const oldTxt = document.getElementById('t_actual').value.trim();
    const newTxt = document.getElementById('t_nuevo').value.trim();
    const note   = document.getElementById('t_notas').value.trim();
    if (!p) return alert('Indica el paciente con ?p= en la URL.');
    if (!oldTxt || !newTxt) return alert('Completa “actual” y “nuevo”.');

    const pid = await resolvePatientId(p);
    if (!pid) {
      // fallback to demo behavior
      pushBitacora(oldTxt, newTxt, note);
      alert('✅ Cambio de tratamiento registrado (demo).');
      document.getElementById('t_actual').value = '';
      document.getElementById('t_nuevo').value  = '';
      document.getElementById('t_notas').value  = '';
      return;
    }

    // Extract name and dose from the new treatment text
    const treatmentParts = newTxt.split(' ');
    const treatmentName = treatmentParts[0] || newTxt;
    const treatmentDose = treatmentParts.slice(1).join(' ') || '';
    
    const payload = { 
      patient_id: pid, 
      name: treatmentName, 
      dose: treatmentDose,
      start_dt: new Date().toISOString().split('T')[0], // current date
      notes: note 
    };
    
    const tokenMeta = document.querySelector('meta[name="csrf-token"]');
    const headers = { 'Accept':'application/json', 'Content-Type':'application/json' };
    if (tokenMeta) headers['X-CSRF-TOKEN'] = tokenMeta.getAttribute('content');

    try{
      const res = await fetch('/medico/api/tratamientos', { method:'POST', credentials:'same-origin', headers, body: JSON.stringify(payload) });
      if (!res.ok) {
        const errorText = await res.text();
        console.error('Server error:', errorText);
        throw new Error('server error: ' + res.status);
      }
      const j = await res.json();
      
      // Update the current treatment field with the new treatment
      document.getElementById('t_actual').value = newTxt;
      
      pushBitacora(oldTxt, newTxt, note);
      alert('✅ Cambio de tratamiento registrado. ID: ' + (j.id||'--'));
      
      // Clear the new treatment and notes fields, but keep current treatment
      document.getElementById('t_nuevo').value  = '';
      document.getElementById('t_notas').value  = '';
    }catch(err){
      console.warn('Could not save treatment remotely', err);
      pushBitacora(oldTxt, newTxt, note);
      alert('⚠️ Error guardando en servidor, cambio registrado localmente. Revisa la consola para detalles.');
    }
  });

   // Try to load current treatments from backend when ?p= provided
   async function resolvePatientId(p){
     if (!p) return null;
     if (/^\d+$/.test(p)) return p;
     try{
       const r = await fetch(`/medico/api/patients?query=${encodeURIComponent(p)}`, { credentials:'same-origin', headers:{'Accept':'application/json'} });
       if (!r.ok) return null;
       const list = await r.json();
       return (list && list.length) ? list[0].id : null;
     }catch(e){ return null; }
   }

   async function fetchTreatments(pid){
     try{
       const res = await fetch(`/medico/api/tratamientos?patient_id=${encodeURIComponent(pid)}`, { credentials:'same-origin', headers:{'Accept':'application/json'} });
       if (!res.ok) throw new Error('no remote');
       const arr = await res.json();
       if (!arr || !arr.length) {
         console.log('No treatments found for patient');
         return;
       }
       
       // Sort treatments by start_dt (most recent first)
       arr.sort((a, b) => new Date(b.start_dt || b.created_at) - new Date(a.start_dt || a.created_at));
       
       // Set the most recent treatment as "current"
       const current = arr[0];
       if (current) {
         const currentText = `${current.name || ''} ${current.dose || ''}`.trim();
         document.getElementById('t_actual').value = currentText;
         console.log('Loaded current treatment:', currentText);
       }
       
       // Populate bitacora with treatment history
       arr.forEach(t => {
         const treatmentText = `${t.name || '(sin nombre)'} ${t.dose || ''}`.trim();
         const date = t.start_dt ? new Date(t.start_dt).toLocaleString() : 
                     (t.created_at ? new Date(t.created_at).toLocaleString() : 'Fecha desconocida');
         
         // Create bitacora entry showing treatment history
         if (bitacora.querySelector('.muted')) bitacora.innerHTML = '';
         const row = document.createElement('div');
         row.className = 'list-item';
         row.innerHTML = `
           <div style="display:flex;flex-direction:column;gap:4px;">
             <div><b>${date}</b> — <span class="muted">${p || '(paciente)'}</span></div>
             <div><b>Tratamiento:</b> ${treatmentText}</div>
             <div><b>Notas:</b> ${t.route || '(sin notas)'}</div>
           </div>
         `;
         bitacora.append(row);
       });
     }catch(e){ 
       console.warn('Could not fetch remote treatments', e); 
     }
   }

   (async ()=>{
     const p0 = new URLSearchParams(location.search).get('p') || '';
     if (!p0) return;
     const pid = await resolvePatientId(p0);
     if (pid) fetchTreatments(pid);
   })();
</script>
@endsection
