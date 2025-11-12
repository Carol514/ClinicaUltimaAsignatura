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
      
      <a href="#" class="confirm-btn" id="btnDescargar" style="text-decoration: none;">Descargar archivo</a>
    </div>
  </section>
</main>

<script>
(() => {
  const btnGenerar = document.getElementById('btnGenerar');
  const msgContainer = document.getElementById('msgContainer');
  const btnDescargar = document.getElementById('btnDescargar');
  const CSRF = '{{ csrf_token() }}';

  function api(path, opts = {}){
    opts.headers = Object.assign({ 'Accept':'application/json', 'Content-Type':'application/json', 'X-CSRF-TOKEN': CSRF }, opts.headers || {});
    if (opts.body && typeof opts.body !== 'string') opts.body = JSON.stringify(opts.body);
    return fetch(path, opts).then(async res => {
      const txt = await res.text(); let json = null;
      try{ json = txt ? JSON.parse(txt) : null; }catch(e){ json = txt; }
      if (!res.ok) throw { status: res.status, body: json };
      return json;
    });
  }

    btnGenerar.onclick = () => {
    btnGenerar.disabled = true;
    btnGenerar.textContent = "Generando respaldo...";
    // default to xlsx
    api('/administrador/api/backups', { method: 'POST', body: { format: 'xlsx' } })
      .then(res => {
        btnGenerar.disabled = false;
        btnGenerar.textContent = "Generar respaldo";
        msgContainer.style.display = "block";
        if (res.download) btnDescargar.href = res.download;
      })
      .catch(err => {
        btnGenerar.disabled = false;
        btnGenerar.textContent = "Generar respaldo";
        console.error(err);
        alert(err.body?.message || 'Error generando respaldo');
      });
  };
})();
</script>
@endsection
