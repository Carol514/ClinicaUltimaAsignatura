<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>Hospital Login</title>
  <!-- apúntale al CSS en public -->
  <link rel="stylesheet" href="/css/logindesign.css" />
</head>
<body>
  <div class="container">
    <!-- Left image -->
    <div class="image-section">
      <img src="/img/loginimage.jpg" alt="Login Image" />
    </div>

    <!-- Right login form -->
    <div class="login-section">
      <div class="logo-container">
        <img src="/img/templogo.jpg" alt="Hospital Logo" class="logo" />
      </div>

      <form id="loginForm" method="POST" action="/login">
        @csrf
        <input type="text" id="username" name="username" placeholder="Usuario" required />
        <input type="password" id="password" name="password" placeholder="Contraseña" required />

        <div class="show-password">
          <input type="checkbox" id="showPass" />
          <label for="showPass">Mostrar contraseña</label>
        </div>

        <button type="submit">Iniciar Sesión</button>
      </form>
    </div>
  </div>

  <!-- ✅ Password toggle script -->
  <script>
    window.onload = function() {
      const showPass = document.getElementById("showPass");
      const passwordInput = document.getElementById("password");
      if (showPass && passwordInput) {
        showPass.addEventListener("change", function() {
          passwordInput.type = this.checked ? "text" : "password";
        });
      }
    };
  </script>
</body>
</html>
