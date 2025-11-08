<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>Panel del Administrador</title>
  <link rel="stylesheet" href="{{ asset('css/panel.css') }}">
</head>

<body>
  <!-- Navbar -->
  <header class="navbar">
    <div class="logo-container">
      <img src="{{ asset('img/templogo.jpg') }}" alt="Hospital Logo" class="logo">
    </div>
    <div class="greeting">Bienvenido, Administrador</div>
    <button class="logout-btn" onclick="logout()">Cerrar Sesión</button>
  </header>

  <!-- Main Dashboard -->
  <main class="dashboard">
    <h2>Panel del Administrador</h2>

    <h3 class="section-title">Administración de Roles</h3>
    <div class="card-container">
      <div class="card" onclick="window.location.href='{{ url('administrar_roles') }}'">
        <h3>Administrar Usuarios</h3>
      </div>
    </div>

    <hr>

    <h3 class="section-title">Reportes Globales</h3>
    <div class="card-container">
      <div class="card" onclick="window.location.href='{{ url('generador_reportes') }}'">
        <h3>Generador de Reportes</h3>
      </div>
      <div class="card" onclick="window.location.href='{{ url('respaldo_bd') }}'">
        <h3>Respaldo de Base de Datos</h3>
      </div>
    </div>
  </main>

  <script>
    const name = localStorage.getItem('adminName') || 'Administrador';
    document.querySelector('.greeting').textContent = `Bienvenido, ${name}`;

    function logout() {
      localStorage.removeItem('adminName');
      window.location.href = '{{ url('login') }}';
    }
  </script>
</body>
</html>
