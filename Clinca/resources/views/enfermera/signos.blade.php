@extends('layouts.app')
@section('title','Signos vitales')

@section('content')
<main class="dashboard">
  <h2>Registrar signos vitales</h2>

  {{-- Paciente desde la URL (?p=) --}}
  <section id="sv-context" class="form-container" style="margin-bottom:10px;">
    <label>Paciente</label>
    <input id="svPaciente" placeholder="(de ?p=)" readonly>
    <p class="muted" style="margin:6px 0 0;">Pasamos el paciente por URL con <code>?p=Nombre</code>.</p>
  </section>

  {{-- Formulario --}}
  <form id="sv-form" class="form-container">
    <label for="svFecha">Fecha</label>
    <input type="date" id="svFecha" required>

    <label for="svTemp">Temperatura (°C)</label>
    <input type="number" step="0.1" id="svTemp" placeholder="36.5" required>

    <label for="svTA">Presión Arterial (mmHg)</label>
    <input type="text" id="svTA" placeholder="120/80" required>

    <label for="svPulso">Pulso (lpm)</label>
    <input type="number" id="svPulso" placeholder="75" required>

    <label for="svFR">Frecuencia respiratoria (rpm)</label>
    <input type="number" id="svFR" placeholder="16" required>

    <label for="svSpO2">Saturación de oxígeno (%)</label>
    <input type="number" id="svSpO2" placeholder="98" required>

    <div class="btn-container">
      <button type="submit" class="confirm-btn">Guardar signos</button>
      <button type="button" id="svReset" class="cancel-btn">Limpiar</button>
      <a href="{{ route('enfermera.panel') }}" class="cancel-btn">Volver</a>
    </div>
  </form>

  {{-- Últimos registros (demo) --}}
  <section id="sv-list" class="panel" style="margin-top:16px;">
    <h3>Últimos registros</h3>
    <div class="table-container">
      <table>
        <thead>
          <tr>
            <th>Fecha/Hora</th>
            <th>T°</th>
            <th>TA</th>
            <th>Pulso</th>
            <th>FR</th>
            <th>SpO₂</th>
            <th>Autor</th>
          </tr>
        </thead>
        <tbody id="svTbody">
          <tr><td colspan="7" class="muted">Aún no hay registros.</td></tr>
        </tbody>
      </table>
    </div>
  </section>
</main>

{{-- JS (demo sin backend) --}}
<script>
  // Cargar paciente desde ?p
  const q = new URLSearchParams(location.search);
  const paciente = q.get('p') || '';
  const $pac = document.getElementById('svPaciente');
  $pac.value = paciente;

  const $form = document.getElementById('sv-form');
  const $tbody = document.getElementById('svTbody');
  const $reset = document.getElementById('svReset');

  // Helpers
  function nowDate() {
    const d = new Date();
    const iso = d.toISOString().slice(0,10);
    return iso;
  }
  document.getElementById('svFecha').value = nowDate();

  function addRow({fecha, hora, temp, ta, pulso, fr, spo2, autor}) {
    // si estaba el placeholder, lo quitamos
    if ($tbody.children.length === 1 && $tbody.children[0].children.length === 1) {
      $tbody.innerHTML = '';
    }
    const tr = document.createElement('tr');
    // build a sensible display for date/time
    let displayDate = fecha || '';
    if (hora) {
      // hora may be HH:MM; combine into ISO-ish string for consistent display
      try {
        const iso = (fecha ? fecha : new Date().toISOString().slice(0,10)) + 'T' + hora + ':00';
        displayDate = new Date(iso).toLocaleString();
      } catch(e) { displayDate = (fecha + ' ' + hora).trim(); }
    }
    tr.innerHTML = `
      <td>${displayDate}</td>
      <td>${temp ?? ''} °C</td>
      <td>${ta ?? ''}</td>
      <td>${pulso ?? ''} lpm</td>
      <td>${fr ?? ''} rpm</td>
      <td>${spo2 ?? ''} %</td>
      <td>${autor || '—'}</td>
    `;
    $tbody.prepend(tr);
  }
  async function resolvePatientId(p){
    if (!p) return null;
    // If numeric id or UUID-like id passed, return it directly
    const uuidRe = /^[0-9a-fA-F]{8}-[0-9a-fA-F]{4}-[0-9a-fA-F]{4}-[0-9a-fA-F]{4}-[0-9a-fA-F]{12}$/;
    if (/^\d+$/.test(p) || uuidRe.test(p)) return p;
    try{
      const r = await fetch(`/enfermera/api/paciente?query=${encodeURIComponent(p)}`, { credentials:'same-origin', headers:{'Accept':'application/json'} });
      if (!r.ok) {
        console.error('Patient resolve failed', r.status);
        return null;
      }
      const list = await r.json();
      return (list && list.length) ? list[0].id : null;
    }catch(e){ console.error('Patient resolve exception', e); return null; }
  }

  async function fetchVitalsForPatient(pid){
    try{
      const res = await fetch(`/enfermera/api/vitals?patient_id=${encodeURIComponent(pid)}`, { credentials:'same-origin', headers:{'Accept':'application/json'} });
      if (!res.ok) throw new Error('no remote');
      const arr = await res.json();
  if (!arr || !arr.length) return;
  $tbody.innerHTML = '';
  arr.forEach(r=> addRow({ fecha: r.fecha, hora: r.hora, temp: r.temp, ta: (r.sbp? r.sbp : '') + '/' + (r.dbp? r.dbp : ''), pulso: r.pulso, fr: r.fr, spo2: r.spo2, autor: r.nurse_name || r.nurse_id }));
    }catch(e){ console.warn('Could not fetch remote vitals', e); }
  }

  $form.addEventListener('submit', async (e)=>{
    e.preventDefault();
    if (!$pac.value.trim()) { alert('Indica el paciente en la URL (?p=...)'); return; }

    const data = {
      taken_at: document.getElementById('svFecha').value,
      temp : parseFloat(document.getElementById('svTemp').value),
      ta   : document.getElementById('svTA').value.trim(),
      pulso: parseInt(document.getElementById('svPulso').value,10),
      fr   : parseInt(document.getElementById('svFR').value,10),
      spo2 : parseInt(document.getElementById('svSpO2').value,10),
    };

    // quick validation
    if (isNaN(data.temp) || isNaN(data.pulso) || isNaN(data.fr) || isNaN(data.spo2) || !data.ta) {
      alert('Verifica los campos de signos vitales.'); return;
    }

    // resolve patient id
    const pid = await resolvePatientId($pac.value.trim());
    if (!pid){
      // fallback to demo local add
      console.warn('Patient not resolved, saving locally');
      addRow({ fecha: data.taken_at, temp: data.temp, ta: data.ta, pulso: data.pulso, fr: data.fr, spo2: data.spo2 });
      $form.reset(); document.getElementById('svFecha').value = nowDate();
      alert('⚠️ Paciente no encontrado en el servidor — el registro se guardó localmente en la vista.');
      return;
    }

    const payload = { patient_id: pid, taken_at: data.taken_at, temp: data.temp, ta: data.ta, pulso: data.pulso, fr: data.fr, spo2: data.spo2 };
    const tokenMeta = document.querySelector('meta[name="csrf-token"]');
    const headers = { 'Accept':'application/json','Content-Type':'application/json' };
    if (tokenMeta) headers['X-CSRF-TOKEN'] = tokenMeta.getAttribute('content');

    try{
      const res = await fetch('/enfermera/api/vitals', { method:'POST', credentials:'same-origin', headers, body: JSON.stringify(payload) });
      if (!res.ok) {
        let body = '';
        try { body = await res.text(); } catch(e){ body = '(no body)'; }
        console.error('Save vitals failed', res.status, body);
        alert('Error guardando signos (status ' + res.status + '). Revisa la consola para más detalles.');
        return;
      }
      const j = await res.json();
      // refresh vitals list
      fetchVitalsForPatient(pid);
      alert('✅ Signos guardados. ID: ' + (j.id||'--'));
      $form.reset(); document.getElementById('svFecha').value = nowDate();
    }catch(err){
      console.warn('Could not save vitals remotely', err);
      addRow({ fecha: data.taken_at, temp: data.temp, ta: data.ta, pulso: data.pulso, fr: data.fr, spo2: data.spo2 });
      $form.reset(); document.getElementById('svFecha').value = nowDate();
      alert('⚠️ No se pudo guardar en el servidor — el registro se agregó localmente. Revisa la consola para más detalles.');
    }
  });

  $reset.addEventListener('click', ()=>{
    $form.reset();
    document.getElementById('svFecha').value = nowDate();
  });

  // on load, try to fetch vitals when ?p=
  (async ()=>{
    const p0 = new URLSearchParams(location.search).get('p');
    if (!p0) return;
    const pid = await resolvePatientId(p0);
    if (pid) fetchVitalsForPatient(pid);
  })();
</script>
@endsection
