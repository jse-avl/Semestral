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
      <h2>Películas y series</h2>
      <p>Bloquear o Desbloquear</p>
      <a href="/Semestral/admin/admin_movies.php">Ver más →</a>
    </div>
  </div>
</div>

<!-- Swiper + Temas -->
<script src="https://cdn.jsdelivr.net/npm/swiper@10/swiper-bundle.min.js"></script>
<script>
  new Swiper('.swiper', {
    slidesPerView: 3,
    spaceBetween: 10,
    loop: true,
    pagination: { el: '.swiper-pagination' },
    autoplay: { delay: 3000 }
  });

  function toggleTheme() {
    const isDark = document.body.classList.contains('dark');
    const next = isDark ? 'light' : 'dark';
    document.body.classList.remove('light', 'dark');
    document.body.classList.add(next);
    document.cookie = "theme=" + next + "; path=/; max-age=31536000";
    document.getElementById('themeToggle').checked = next === 'dark';
  }

  function applyThemeFromCookie() {
    const match = document.cookie.match(/theme=(light|dark)/);
    const theme = match ? match[1] : 'light';
    document.body.classList.add(theme);
    if (theme === 'dark') {
      document.getElementById('themeToggle').checked = true;
    }
  }
  applyThemeFromCookie();
</script>

<script>
  function toggleMenu() {
    const menu = document.querySelector('.ul');
    menu.classList.toggle('show');
  }
</script>

<!-- Footer -->
<footer class="main-footer">
  <div class="footer-container">
    <div class="footer-logo">
      <img src="../css/logo2.png" alt="Logo" />
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