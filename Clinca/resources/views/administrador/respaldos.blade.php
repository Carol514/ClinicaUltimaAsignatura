@extends('layouts.app')
@section('title','Respaldos de la Base de Datos')

@section('content')
<main class="dashboard">
  <h2>Respaldos de la Base de Datos</h2>

  <section class="panel" style="max-width:900px;">
    <p class="muted">
      Aquí puedes generar un respaldo completo del sistema.  
      El archivo generado contendrá TODAS las tablas de la base de datos en formato Excel (.xlsx).
    </p>

    <div id="statusContainer" style="margin-top:16px;text-align:center;">
      <p id="statusMsg" class="muted" style="display:none;"></p>
    </div>

    <div style="text-align:center;margin-top:24px;">
      <button class="confirm-btn" id="btnGenerar">Generar respaldo completo</button>
      <a class="cancel-btn" href="{{ route('admin.panel') }}" style="margin-left:10px;">Volver</a>
    </div>

    <div id="msgContainer" style="display:none;margin-top:20px;text-align:center;">
      <p style="color:green;margin-bottom:10px;">✅ Respaldo generado exitosamente</p>
      <a href="#" class="confirm-btn" id="btnDescargar" style="text-decoration: none;">Descargar archivo completo</a>
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

  const statusMsg = document.getElementById('statusMsg');
  
  function showStatus(message, isError = false) {
    statusMsg.textContent = message;
    statusMsg.style.color = isError ? 'crimson' : '#6c757d';
    statusMsg.style.display = 'block';
  }

  btnGenerar.onclick = () => {
    btnGenerar.disabled = true;
    btnGenerar.textContent = "Generando respaldo completo...";
    showStatus("Obteniendo todas las tablas de la base de datos...");
    
    // Request backup of all tables in xlsx format
    api('/administrador/api/backups', { method: 'POST', body: { format: 'xlsx' } })
      .then(res => {
        btnGenerar.disabled = false;
        btnGenerar.textContent = "Generar respaldo completo";
        
        // Show detailed status with table count
        const tablesCount = res.tables_count || 0;
        showStatus(`Respaldo completado exitosamente. ${tablesCount} tablas respaldadas.`);
        
        msgContainer.style.display = "block";
        if (res.download) btnDescargar.href = res.download;
        
        // Log detailed info about the backup
        console.log('Backup completed:', {
          tables: res.tables || [],
          files: res.files || [],
          total_tables: tablesCount
        });
        
        // Update the download text to show table count
        if (tablesCount > 0) {
          btnDescargar.textContent = `Descargar respaldo (${tablesCount} tablas)`;
        }
      })
      .catch(err => {
        btnGenerar.disabled = false;
        btnGenerar.textContent = "Generar respaldo completo";
        showStatus('Error generando respaldo: ' + (err.body?.message || err.body || 'Error desconocido'), true);
        console.error('Backup error:', err);
      });
  };
})();
</script>
@endsection
