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
      </div>
      <div>
        <label>Fecha</label>
        <input id="fechaAlta" type="date" required>
      </div>
    </div>

    <div class="sep"></div>

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
      <a class="cancel-btn btn-wide" href="{{ route('medico.panel') }}">Volver</a>
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

  function renderPreview(data){
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
  }

  form.addEventListener('submit', (e) => {
    e.preventDefault();
    const data = {
      paciente: $('pacienteNombre').value.trim(),
      fecha: $('fechaAlta').value,
      alergias: $('alergias').value.trim(),
      antecedentes: $('antecedentes').value.trim(),
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
    renderPreview(data);
    alert('✅ Alta de historial guardada (demo).');
  });

  btnLimpiar.addEventListener('click', () => {
    form.reset();
    preview.innerHTML = '<p class="muted">Formulario limpio.</p>';
  });
})();
</script>
@endsection
