<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
  <meta name="csrf-token" content="{{ csrf_token() }}">
  <title>@yield('title','Clínica')</title>
  <link rel="stylesheet" href="{{ asset('css/panel.css') }}">
</head>
<body>
  <header class="navbar">
    <div class="logo-container">
        <img src="{{ asset('img/templogo.jpg') }}" alt="Hospital Logo" class="logo">
    </div>

    <div class="greeting">@yield('greeting','Bienvenid@')</div>

    <form method="POST" action="{{ route('logout') }}" style="margin:0;">
        @csrf
        <a href="{{ route('logout') }}" class="cancel-btn btn-volver logout-btn">
          <img src="/img/logout.png" width="22">
        </a>

    </form>
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
            // Navigate to the logout route so server-side logout runs
            window.location.href = '{{ route('logout') }}';
          });
        }
    })();
  </script>
</body>
</html>
