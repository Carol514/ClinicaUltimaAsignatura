{{-- resources/views/enfermera/panel.blade.php --}}
@extends('layouts.app')
@section('title','Panel de Enfermería')

@section('content')
<main class="dashboard">
  <h2>Panel de Enfermería</h2>
  <p class="muted">Selecciona una acción para continuar.</p>

  <div class="card-container">
    <a class="card" style="text-decoration: none;" href="{{ route('enfermera.signos') }}?p=Paciente%20DEMO">
      Registrar signos vitales
    </a>
  </div>
</main>
@endsection
