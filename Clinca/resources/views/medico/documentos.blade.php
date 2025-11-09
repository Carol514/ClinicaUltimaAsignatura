@extends('layouts.app')
@section('title','Subir documentos')

@section('content')
<main class="dashboard">
  <h2>Subir documentos</h2>

  <form class="form-container" id="docForm" enctype="multipart/form-data">
    <label>Paciente</label>
    <input id="docPaciente" readonly placeholder="(de ?p=)">

    <label style="margin-top:6px;">Tipo</label>
    <select id="docTipo" required>
      <option value="" disabled selected>Seleccione…</option>
      <option>Radiografía</option><option>Análisis</option><option>Otro</option>
    </select>

    <label style="margin-top:6px;">Título</label>
    <input id="docTitulo" placeholder="Ej. Radiografía de tórax" required>

    <label style="margin-top:6px;">Archivo</label>
    <input id="docFile" type="file" required>

    <div class="btn-container" style="margin-top:8px;">
      <button class="confirm-btn" type="submit">Subir</button>
      <a class="cancel-btn" href="{{ route('medico.panel') }}">Volver</a>
    </div>
  </form>

  <div class="panel" style="margin-top:14px;">
    <h3>Subidos (demo)</h3>
    <div id="docs" class="list-container"></div>
  </div>
</main>

<script>
  const p = new URLSearchParams(location.search).get('p') || '';
  docPaciente.value = p;

  docForm.addEventListener('submit', (e)=>{
    e.preventDefault();
    if(!docPaciente.value.trim()){ alert('Indica el paciente'); return; }
    const item = document.createElement('div');
    item.className = 'list-item';
    item.innerHTML = `<strong>${docPaciente.value}</strong><br>
      ${new Date().toLocaleString()} — ${docTipo.value}: ${docTitulo.value}`;
    docs.prepend(item);
    docForm.reset();
    docPaciente.value = p;
  });
</script>
@endsection
