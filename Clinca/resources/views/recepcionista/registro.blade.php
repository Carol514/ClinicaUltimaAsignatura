@extends('layouts.app')
@section('title', 'Registrar Paciente')

@section('content')
<main class="dashboard">
  <h2>Registrar nuevo paciente</h2>

  <div class="form-container">
    <form>
      <label>Nombre completo</label>
      <input type="text" placeholder="Ej. Juan Pérez" required>

      <label>Edad</label>
      <input type="number" min="0" max="120" placeholder="Ej. 34" required>

      <label>Sexo</label>
      <select required>
        <option value="" disabled selected>Seleccione…</option>
        <option value="Masculino">Masculino</option>
        <option value="Femenino">Femenino</option>
        <option value="Otro">Otro</option>
      </select>

      <label>Dirección</label>
      <input type="text" placeholder="Calle, colonia, ciudad">

      <label>Teléfono o contacto</label>
      <input type="tel" placeholder="Ej. 3221234567">

      <div class="btn-container">
        <button type="submit" class="confirm-btn">Guardar paciente</button>
        <a href="{{ route('recepcionista.panel') }}" class="cancel-btn">Volver</a>
      </div>
    </form>
  </div>
</main>
@endsection
