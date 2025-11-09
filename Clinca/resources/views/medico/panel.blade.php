@extends('layouts.app')
@section('title','Panel del Médico')

@section('content')
<main class="dashboard">
  <h2>Panel del Médico</h2>

  {{-- Buscar paciente (sin panel contenedor) --}}
  <h3 style="margin:20px 0 10px; text-align:center;">Buscar Paciente</h3>
  <form id="frmBuscar" class="form-container" onsubmit="return false;" style="max-width:600px; margin:0 auto 20px;">
    <div class="fields" style="display:grid; gap:10px; grid-template-columns:1fr auto;">
      <input id="txtPaciente" placeholder="Nombre / ID del paciente">
      <button id="btnBuscar" class="confirm-btn" type="button">Buscar</button>
    </div>
  </form>

  {{-- Datos del paciente + módulos del médico --}}
  <section id="boxPaciente" class="panel" style="max-width:1000px; display:none;">
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
      <a id="lnkHist" class="card" style="text-decoration:none;" href="#">
        Consultar Historial Médico
      </a>
      <a id="lnkDocs" class="card" style="text-decoration:none;" href="#">
        Subir Documentos
      </a>
      <a id="lnkTrat" class="card" style="text-decoration:none;" href="#">
        Editar Tratamientos
      </a>
      <a id="lnkAlta" class="card" style="text-decoration:none;" href="#">
        Alta de Historial
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

  const lnkHist = $('lnkHist');
  const lnkDocs = $('lnkDocs');
  const lnkTrat = $('lnkTrat');
  const lnkAlta = $('lnkAlta');

  // Rutas Laravel de los 4 módulos
  const RUTA_HIST = @json(route('medico.historial'));
  const RUTA_DOCS = @json(route('medico.documentos'));
  const RUTA_TRAT = @json(route('medico.tratamientos'));
  const RUTA_ALTA = @json(route('medico.alta'));

  function buscarPaciente() {
    const nombre = txtPaciente.value.trim();
    if (!nombre){ alert('Escribe el nombre o ID del paciente.'); return; }

    // Datos DEMO (mientras no hay backend)
    const demo = {
      nombre,
      edad: '35 años',
      genero: 'Femenino',
      dx: 'Hipertensión',
      ultima: '25/10/2025'
    };

    // Pintar ficha
    hdrPaciente.textContent = `Paciente: ${demo.nombre}`;
    pEdad.textContent   = demo.edad;
    pGenero.textContent = demo.genero;
    pDx.textContent     = demo.dx;
    pUltima.textContent = demo.ultima;

    // Pasar ?p=Nombre a cada módulo
    const qp = encodeURIComponent(demo.nombre);
    lnkHist.href = `${RUTA_HIST}?p=${qp}`;
    lnkDocs.href = `${RUTA_DOCS}?p=${qp}`;
    lnkTrat.href = `${RUTA_TRAT}?p=${qp}`;
    lnkAlta.href = `${RUTA_ALTA}?p=${qp}`;

    boxPaciente.style.display = 'block';
  }

  btnBuscar.addEventListener('click', buscarPaciente);
  txtPaciente.addEventListener('keydown', (e)=>{ if(e.key === 'Enter'){ e.preventDefault(); buscarPaciente(); } });

  // Autollenado si llega ?p= en la URL
  const q = new URLSearchParams(location.search).get('p');
  if (q){ txtPaciente.value = q; buscarPaciente(); }
})();
</script>
@endsection
