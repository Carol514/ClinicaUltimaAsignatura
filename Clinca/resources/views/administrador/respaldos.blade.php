@extends('layouts.app')
@section('title','Respaldos de la Base de Datos')

@section('content')
<main class="dashboard">
  <h2>Respaldos de la Base de Datos</h2>

  <section class="panel" style="max-width:900px;">
    <p class="muted">
      Aquí puedes generar un respaldo del sistema.  
      El archivo generado contendrá la información principal en formato Excel (.xlsx) o CSV (.csv).
    </p>

    <div style="text-align:center;margin-top:24px;">
      <button class="confirm-btn" id="btnGenerar">Generar respaldo</button>
      <a class="cancel-btn" href="{{ route('admin.panel') }}" style="margin-left:10px;">Volver</a>
    </div>

    <div id="msgContainer" style="display:none;margin-top:20px;text-align:center;">
      <p class="muted">✅ Respaldo generado correctamente.</p>
      <a href="#" class="confirm-btn" id="btnDescargar">Descargar archivo</a>
    </div>
  </section>
</main>

<script>
(() => {
  const btnGenerar = document.getElementById('btnGenerar');
  const msgContainer = document.getElementById('msgContainer');
  const btnDescargar = document.getElementById('btnDescargar');

  btnGenerar.onclick = () => {
    // Simulación de generación de respaldo (descarga demo)
    btnGenerar.disabled = true;
    btnGenerar.textContent = "Generando respaldo...";
    setTimeout(() => {
      btnGenerar.disabled = false;
      btnGenerar.textContent = "Generar respaldo";
      msgContainer.style.display = "block";
      btnDescargar.href = "#"; // Diego implementará aquí la ruta real de descarga
    }, 1500);
  };
})();
</script>
@endsection
