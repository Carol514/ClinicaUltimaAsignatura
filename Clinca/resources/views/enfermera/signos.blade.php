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
      <a href="{{ route('medico.panel') }}" class="cancel-btn">Volver</a>
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

  function addRow({fecha, temp, ta, pulso, fr, spo2}) {
    // si estaba el placeholder, lo quitamos
    if ($tbody.children.length === 1 && $tbody.children[0].children.length === 1) {
      $tbody.innerHTML = '';
    }
    const tr = document.createElement('tr');
    tr.innerHTML = `
      <td>${new Date(fecha + 'T00:00').toLocaleString()}</td>
      <td>${temp} °C</td>
      <td>${ta}</td>
      <td>${pulso} lpm</td>
      <td>${fr} rpm</td>
      <td>${spo2} %</td>
      <td>Enf. Sofía</td>
    `;
    $tbody.prepend(tr);
  }

  $form.addEventListener('submit', (e)=>{
    e.preventDefault();
    if (!$pac.value.trim()) { alert('Indica el paciente en la URL (?p=...)'); return; }

    const data = {
      fecha: document.getElementById('svFecha').value,
      temp : parseFloat(document.getElementById('svTemp').value),
      ta   : document.getElementById('svTA').value.trim(),
      pulso: parseInt(document.getElementById('svPulso').value,10),
      fr   : parseInt(document.getElementById('svFR').value,10),
      spo2 : parseInt(document.getElementById('svSpO2').value,10),
    };

    // Validación rápida
    if (isNaN(data.temp) || isNaN(data.pulso) || isNaN(data.fr) || isNaN(data.spo2) || !data.ta) {
      alert('Verifica los campos de signos vitales.'); return;
    }

    // (Aquí iría fetch/POST real)
    addRow(data);

    // Reset del form, conservando paciente y fecha
    $form.reset();
    document.getElementById('svFecha').value = nowDate();
  });

  $reset.addEventListener('click', ()=>{
    $form.reset();
    document.getElementById('svFecha').value = nowDate();
  });
</script>
@endsection
