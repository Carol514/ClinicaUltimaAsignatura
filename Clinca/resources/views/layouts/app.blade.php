<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
  <title>@yield('title','Clínica')</title>
  <link rel="stylesheet" href="{{ asset('css/panel.css') }}">
</head>
<body>
  <header class="navbar">
    <div class="logo-container">
      <img src="{{ asset('img/templogo.jpg') }}" alt="Hospital Logo" class="logo">
    </div>
    <div class="greeting">@yield('greeting','Bienvenid@')</div>
    <button class="logout-btn" id="logoutBtn">Cerrar Sesión</button>
  </header>

  <main class="dashboard">
    @yield('content')
  </main>

  {{-- Scripts específicos de cada vista --}}
  @yield('scripts')

  {{-- Fallback simple para logout si no hay JS propio --}}
  <script>
    (function () {
      const btn = document.getElementById('logoutBtn');
      if (btn) {
        btn.addEventListener('click', () => {
          // Aquí luego cambiamos por route('logout') cuando haya auth
          window.location.href = '{{ url('/login') }}';
        });
      }
    })();
  </script>
</body>
</html>
