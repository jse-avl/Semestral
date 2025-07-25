<?php
session_start();
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
  header("Location: /Semestral/login.php");
  exit;
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8">
  <title>Panel Administrativo</title>
  <link rel="stylesheet" href="/Semestral/css/style.css">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css" />
</head>
<body>
<script>
  // Aplica el tema automáticamente según la cookie
  const match = document.cookie.match(/theme=(light|dark)/);
  const theme = match ? match[1] : 'light';
  document.body.classList.add(theme);
</script>

<?php include $_SERVER['DOCUMENT_ROOT'] . "/Semestral/includes/navbar.php"; ?>

<div class="admin-container">
  <h1>🔧 Panel Administrativo</h1>

  <div class="admin-cards">
    <div class="admin-card">
      <i class="fa-solid fa-users fa-2x"></i>
      <h2>Usuarios</h2>
      <p>Gestionar cuentas</p>
      <a href="/Semestral/admin/admin_users.php">Ver más →</a>
    </div>

    <div class="admin-card">
      <i class="fa-solid fa-film fa-2x"></i>
      <h2>Películas</h2>
      <p>Agregar, editar o eliminar</p>
      <a href="/Semestral/admin/admin_movies.php">Ver más →</a>
    </div>

    <div class="admin-card">
      <i class="fa-solid fa-comment-dots fa-2x"></i>
      <h2>Reseñas</h2>
      <p>Moderación de contenido</p>
      <a href="/Semestral/admin/admin_comments.php">Ver más →</a>
    </div>
  </div>
</div>

<footer class="main-footer">
  <div class="footer-container">
    <div class="footer-logo">
      <img src="/Semestral/css/logo2.png" alt="Logo" />
      <h3>RateMyMovie</h3>
    </div>
    <div class="footer-social">
      <p>© <?= date('Y') ?> RateMyMovie. Todos los derechos reservados.</p>
      <div class="social-icons">
        <a href="https://facebook.com" target="_blank" aria-label="Facebook"><i class="fab fa-facebook-f"></i></a>
        <a href="https://twitter.com" target="_blank" aria-label="Twitter"><i class="fab fa-x-twitter"></i></a>
        <a href="https://instagram.com" target="_blank" aria-label="Instagram"><i class="fab fa-instagram"></i></a>
      </div>
    </div>
  </div>
</footer>

</body>
</html>