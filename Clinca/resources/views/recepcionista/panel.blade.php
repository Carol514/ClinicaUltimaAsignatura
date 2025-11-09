@extends('layouts.app')
@section('title', 'Panel de Recepción')

@section('content')
<main class="dashboard">
  <h2>Panel de Recepción</h2>
  <p class="muted">Selecciona una acción para comenzar.</p>

  <div class="card-container" >
    <a class="card" href="{{ route('recepcionista.registro') }}" style="text-decoration: none;">
      Registrar paciente
    </a>
    <a class="card" href="{{ route('recepcionista.citas') }}" style="text-decoration: none;">
      Agendar cita
    </a>
    <a class="card" href="{{ route('recepcionista.agenda') }}" style="text-decoration: none;">
      Ver agenda del día
    </a>
  </div>
</main>
@endsection
