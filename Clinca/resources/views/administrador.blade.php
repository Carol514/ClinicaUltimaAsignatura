@extends('layouts.app')

@section('title','Panel del Administrador')
@section('greeting','Bienvenido, Administrador')

@section('content')
  <h2>Panel del Administrador</h2>

  <h3 class="section-title">Administración de Roles</h3>
  <div class="card-container">
    <div class="card" data-link="{{ url('/administrar-roles') }}">
      <h3>Administrar Usuarios</h3>
    </div>
  </div>

  <hr>

  <h3 class="section-title">Reportes Globales</h3>
  <div class="card-container">
    <div class="card" data-link="{{ url('/reportes') }}">
      <h3>Generador de Reportes</h3>
    </div>
    <div class="card" data-link="{{ url('/respaldo-bd') }}">
      <h3>Respaldo de Base de Datos</h3>
    </div>
  </div>
@endsection

@section('scripts')
  {{-- JS específico de la vista Administrador --}}
  <script>
    // convierte todas las tarjetas con data-link en links clicables
    document.querySelectorAll('.card[data-link]').forEach(card => {
      card.style.cursor = 'pointer';
      card.addEventListener('click', () => {
        window.location.href = card.getAttribute('data-link');
      });
    });
  </script>
@endsection
