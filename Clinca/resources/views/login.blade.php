<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>Iniciar Sesión</title>
  <link rel="stylesheet" href="{{ asset('css/logindesign.css') }}" />
</head>
<body>
  <div class="container">
    <!-- Izquierda: imagen -->
    <div class="image-section">
      <img src="{{ asset('img/loginimage.jpg') }}" alt="Login Image" />
    </div>

    <!-- Derecha: formulario -->
    <div class="login-section">
      <div class="logo-container">
        <img src="{{ asset('img/templogo.jpg') }}" alt="Hospital Logo" class="logo" />
      </div>

      <form id="loginForm" method="POST" action="{{ route('login.post') }}">
        @csrf

        <input type="text" id="username" name="username" placeholder="Usuario" value="{{ old('username') }}" required />
        <input type="password" id="password" name="password" placeholder="Contraseña" required />

        <div class="show-password">
          <input type="checkbox" id="showPass" />
          <label for="showPass">Mostrar contraseña</label>
        </div>

        <button type="submit">Iniciar Sesión</button>
      </form>
    </div>
  </div>

  <script>
    // Toggle contraseña
    window.onload = function () {
      const showPass = document.getElementById("showPass");
      const passwordInput = document.getElementById("password");
      if (showPass && passwordInput) {
        showPass.addEventListener("change", function () {
          passwordInput.type = this.checked ? "text" : "password";
        });
      }
    };
  </script>
</body>
</html>
