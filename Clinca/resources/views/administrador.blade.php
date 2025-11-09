@extends('layouts.app')
@section('title','Panel del Administrador')

@section('content')
<main class="dashboard">
  <h2>Panel del Administrador</h2>
  <p class="muted">Elige un módulo.</p>

  <section class="panel" style="max-width:1000px;">
    <div class="card-container" style="margin-top:8px;">
      <a class="card" href="{{ route('admin.roles') }}" style="text-decoration:none;">
        Usuarios y roles
      </a>

      {{-- Placeholders para cubrir HU-08 y HU-11 (puedes crearlos después) --}}
      <a class="card" href="{{ route('admin.respaldos') }}" style="text-decoration:none;">
        Respaldos de BD
      </a>

      <a class="card" href="{{ route('admin.reportes') }}" style="text-decoration:none;">
        Reportes
      </a>
    </div>
  </section>
</main>
@endsection
