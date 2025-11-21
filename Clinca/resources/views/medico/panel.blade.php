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
      <button id="btnBuscar" class="confirm-btn" type="button"><img src="/img/buscar.png" alt="Limpiar" width="22" height="22"></button>
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

    // Try backend search to resolve patient id and details
    (async ()=>{
      try{
        const res = await fetch(`/medico/api/patients?query=${encodeURIComponent(nombre)}`, { credentials:'same-origin', headers:{'Accept':'application/json'} });
        if (res.status === 403) {
          alert('Acceso denegado: debes iniciar sesión como médico o administrador.');
          return;
        }
        if (res.status === 401) {
          alert('No autenticado: por favor inicia sesión.');
          return;
        }
        if (!res.ok) {
          // try to read server response for a helpful message
          let text = '';
          try { text = await res.text(); } catch(e){ text = '(no body)'; }
          console.error('Patient search failed', res.status, text);
          alert(`Error buscando paciente (status ${res.status}). Revise la consola para más detalles.\nServidor: ${text.slice(0,200)}`);
          boxPaciente.style.display = 'block';
          return;
        }
        const list = await res.json();
        if (!list || !list.length) {
          alert('No se encontró ningún paciente con ese nombre o ID. Prueba menos o más partes del nombre.');
          boxPaciente.style.display = 'block';
          return;
        }
        const patient = list[0];

        hdrPaciente.textContent = `Paciente: ${patient.name}`;
        pEdad.textContent   = patient.age || '—';
        // Map short sex codes to readable labels
        const genderMap = { 'M':'Masculino', 'F':'Femenino', 'I':'Indefinido' };
        pGenero.textContent = genderMap[patient.gender] || patient.gender || '—';
        pDx.textContent = patient.diagnosis || '—';
        pUltima.textContent = patient.last_consult || '—';

        // Use patient id when linking to modules
        const qp = encodeURIComponent(patient.id);
        lnkHist.href = `${RUTA_HIST}?p=${qp}`;
        lnkDocs.href = `${RUTA_DOCS}?p=${qp}`;
        lnkTrat.href = `${RUTA_TRAT}?p=${qp}`;
        lnkAlta.href = `${RUTA_ALTA}?p=${qp}`;
      }catch(err){
        console.error(err);
        alert('Error buscando paciente. Revisa la conexión y si tu sesión es válida.');
      } finally {
        boxPaciente.style.display = 'block';
      }
    })();
  }

  btnBuscar.addEventListener('click', buscarPaciente);
  txtPaciente.addEventListener('keydown', (e)=>{ if(e.key === 'Enter'){ e.preventDefault(); buscarPaciente(); } });

  // Autollenado si llega ?p= en la URL
  const q = new URLSearchParams(location.search).get('p');
  if (q){ txtPaciente.value = q; buscarPaciente(); }
})();
</script>
@endsection
