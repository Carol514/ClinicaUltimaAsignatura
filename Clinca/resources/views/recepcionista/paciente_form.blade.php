@extends('layouts.app')
@section('title', 'Paciente · Formulario')

@section('content')
<main class="dashboard">
  <h2>Registrar / Editar paciente</h2>

  <form class="form-container" id="pacForm" onsubmit="event.preventDefault(); alert('✅ Guardado (demo)');">
    <label>Nombre completo</label>
    <input id="name" placeholder="Ej. Ana Pérez" required>

    <label>CURP</label>
    <input id="curp" placeholder="Ej. PEAA010101HDFRNS09">

    <label>Teléfono</label>
    <input id="phone" placeholder="Ej. 3221234567">

    <label>Correo</label>
    <input id="email" type="email" placeholder="ejemplo@correo.com">

    <label>Fecha de nacimiento</label>
    <input id="dob" type="date">

    <label>Dirección</label>
    <textarea id="address" rows="3" placeholder="Calle, número, colonia, ciudad"></textarea>

    <div class="btn-container" style="margin-top:10px;">
      <button class="confirm-btn" type="submit">Guardar</button>
      <a class="cancel-btn" href="{{ route('recepcionista.pacientes') }}">Volver</a>
    </div>
  </form>
</main>
@endsection
