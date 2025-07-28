<?php
if (isset($_SESSION['user'])) {
    header('Location: index.php');
    exit;
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8">
  <title>Login o Registro</title>
  <link rel="stylesheet" href="css/stylelogin.css">
  <style>
    .toast {
      font-family: 'Courier New', Courier, monospace;
      position: fixed;
      top: 20px;
      right: 20px;
      padding: 15px 25px;
      border-radius: 5px;
      color: white;
      opacity: 0;
      transition: opacity 0.3s ease-in-out;
      z-index: 1000;
    }
    .toast.error {
      background-color:rgb(204, 75, 75);
    }
    .toast.show {
      opacity: 1;
    }
  </style>
</head>
<body>
  <div id="toast" class="toast"></div>
  <div class="wrapper">
    <div class="card-switch">
      <label class="switch">
        <input type="checkbox" class="toggle">
        <span class="slider"></span>
        <span class="card-side"></span>
        <div class="flip-card__inner">
          <div class="flip-card__front">
            <div class="title">Inicia sesión</div>
            <form method="POST" action="auth.php" class="flip-card__form">
              <input class="flip-card__input" name="email" placeholder="Email" type="email" required>
              <input class="flip-card__input" name="password" placeholder="Contraseña" type="password" required>
              <button class="flip-card__btn" type="submit" name="action" value="login">Ingresar</button>
              <a href="index.php" class="flip-card__btn" style="text-decoration: none; text-align: center; margin-top: 10px;">Continuar como invitado</a>
            </form>
          </div>
          <div class="flip-card__back">
            <div class="title">Regístrate</div>
            <form method="POST" action="auth.php" class="flip-card__form">
              <input class="flip-card__input" name="username" placeholder="Nombre" type="text" required pattern="[a-zA-Z0-9_]{3,20}">
              <input class="flip-card__input" name="email" placeholder="Email" type="email" required>
              <label>Género favorito:</label>
              <select name="genre" required>
                <option value="28">Acción</option>
                <option value="35">Comedia</option>
                <option value="18">Drama</option>
                <option value="27">Terror</option>
                <option value="10749">Romance</option>
              </select>
              <input class="flip-card__input" name="password" placeholder="Contraseña" type="password" required>
              <button class="flip-card__btn" type="submit" name="action" value="register">Confirmar</button>
            </form>
          </div>
        </div>
      </label>
    </div>
  </div>

  <script>
    function showToast(message) {
      const toast = document.getElementById('toast');
      toast.textContent = message;
      toast.classList.add('error', 'show');
      
      setTimeout(() => {
        toast.classList.remove('show');
      }, 3000);
    }

    const error = new URLSearchParams(window.location.search).get('error');
    if (error === 'invalid_login') {
      showToast('Correo o contraseña incorrectos.');
    } else if (error === 'duplicate') {
      showToast('El correo o nombre de usuario ya está registrado.');
    } else if (error === 'locked') {
      showToast('Demasiados intentos. Intenta más tarde.');
    }else if (error === 'invalid_email') {
      showToast('El correo no es válido.');
    }else{
      showToast('Registro exitoso');
    }
  </script>
</body>
</html>