{{-- resources/views/enfermera/panel.blade.php --}}
@extends('layouts.app')
@section('title','Panel de Enfermería')

@section('content')
<main class="dashboard">
  <h2>Panel de Enfermería</h2>

  {{-- Buscar paciente (sin contenedor panel) --}}
  <h3 style="margin: 20px 0 10px; text-align:center;">Buscar Paciente</h3>

  <form id="frmBuscar" class="form-container" onsubmit="return false;" style="max-width: 600px; margin: 0 auto 20px;">
    <div class="fields" style="display:grid; gap:10px; grid-template-columns: 1fr auto;">
      <input id="txtPaciente" placeholder="Ingrese el nombre o ID del paciente">
      <button id="btnBuscar" class="confirm-btn" type="button">Buscar</button>
    </div>
  </form>

  {{-- Datos del paciente + accesos de enfermería --}}
  <section id="boxPaciente" class="panel" style="max-width: 1000px; display:none;">
    <h3 id="hdrPaciente" style="text-align:center; margin-top:0;">Paciente: —</h3>

    <div class="table-container">
      <table>
        <thead>
          <tr>
            <th>Campo</th>
            <th>Información</th>
          </tr>
        </thead>
        <tbody>
          <tr><td>Edad</td><td id="pEdad">—</td></tr>
          <tr><td>Género</td><td id="pGenero">—</td></tr>
          <tr><td>Diagnóstico</td><td id="pDx">—</td></tr>
          <tr><td>Última Consulta</td><td id="pUltima">—</td></tr>
        </tbody>
      </table>
    </div>

    <div class="card-container" style="margin-top:14px;">
      <a id="lnkSignos" class="card" style="text-decoration:none;" href="#">
        Registro de Signos Vitales
      </a>
      <a id="lnkTrat" class="card" style="text-decoration:none;" href="#">
        Editar Tratamientos Aplicados
      </a>
    </div>
  </section>
</main>

<script>
(() => {
  const $ = (id) => document.getElementById(id);

  const txtPaciente = $('txtPaciente');
  const btnBuscar   = $('btnBuscar');
  const boxPaciente = $('boxPaciente');
  const hdrPaciente = $('hdrPaciente');

  const pEdad   = $('pEdad');
  const pGenero = $('pGenero');
  const pDx     = $('pDx');
  const pUltima = $('pUltima');

  const lnkSignos = $('lnkSignos');
  const lnkTrat   = $('lnkTrat');

  // Rutas base (Laravel)
  const RUTA_SIGNOS = @json(route('enfermera.signos'));
  const RUTA_TRAT   = @json(route('medico.tratamientos'));

  function buscarPaciente() {
    const nombre = txtPaciente.value.trim();
    if (!nombre) { alert('Escribe un nombre o ID de paciente.'); return; }

    // Datos simulados (demo)
    const demo = {
      nombre,
      edad: '42 años',
      genero: 'Masculino',
      dx: 'Diabetes Tipo 2',
      ultima: '22/10/2025'
    };

    hdrPaciente.textContent = `Paciente: ${demo.nombre}`;
    pEdad.textContent   = demo.edad;
    pGenero.textContent = demo.genero;
    pDx.textContent     = demo.dx;
    pUltima.textContent = demo.ultima;

    const qp = encodeURIComponent(demo.nombre);
    lnkSignos.href = `${RUTA_SIGNOS}?p=${qp}`;
    lnkTrat.href   = `${RUTA_TRAT}?p=${qp}`;

    boxPaciente.style.display = 'block';
  }

  btnBuscar.addEventListener('click', buscarPaciente);
  txtPaciente.addEventListener('keydown', (e)=>{ if(e.key === 'Enter'){ e.preventDefault(); buscarPaciente(); } });

  const q = new URLSearchParams(location.search).get('p');
  if (q) { txtPaciente.value = q; buscarPaciente(); }
})();
</script>
@endsection
