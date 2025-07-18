<?php
session_start();
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
</head>
<body>
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
    const error = new URLSearchParams(window.location.search).get('error');
    if (error === 'invalid_login') {
      alert('Correo o contraseña incorrectos.');
    } else if (error === 'duplicate') {
      alert('El correo o nombre de usuario ya está registrado.');
    } else if (error === 'locked') {
      alert('Demasiados intentos. Intenta más tarde.');
    }
  </script>
</body>
</html>