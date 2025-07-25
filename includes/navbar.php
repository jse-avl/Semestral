<?php if (!isset($_SESSION)) session_start(); ?>

<nav class="navbar">
  <button class="menu-toggle" onclick="toggleMenu()">☰</button>
  <ul class="ul">
    <li><a href="/Semestral/index.php"><i class="fa-solid fa-house"></i>Inicio</a></li>

    <?php if (isset($_SESSION['role']) && $_SESSION['role'] === 'admin'): ?>
      <li id="cat">
        <a href="/Semestral/admin/dashboard.php"><i class="fa-solid fa-shield-halved"></i>Moderación</a>
      </li>
      <li>
        <a href="/Semestral/logout.php"><i class="fa-solid fa-right-from-bracket"></i>Salir</a>
      </li>
    <?php elseif (isset($_SESSION['user'])): ?>
      <li id="cat">
        <a href="/Semestral/perfil.php"><i class="fa-solid fa-user"></i>Mi Perfil ▾</a>
        <ul class="dropdown-menu">
          <li><a href="/Semestral/mis_calificaciones.php">Mis Calificaciones</a></li>
          <li><a href="/Semestral/favoritos.php">Mis favoritos</a></li>
          <li><a href="/Semestral/logout.php">Cerrar sesión</a></li>
        </ul>
      </li>
    <?php else: ?>
      <li><a href="/Semestral/login.php">Iniciar sesión</a></li>
    <?php endif; ?>

    <li id="cat">
      <a href="#"><i class="fa-solid fa-layer-group"></i>Categorías ▾</a>
      <ul class="dropdown-menu">
        <li><a href="/Semestral/index.php?set_genre=28">Acción</a></li>
        <li><a href="/Semestral/index.php?set_genre=35">Comedia</a></li>
        <li><a href="/Semestral/index.php?set_genre=18">Drama</a></li>
        <li><a href="/Semestral/index.php?set_genre=27">Terror</a></li>
        <li><a href="/Semestral/index.php?set_genre=10749">Romance</a></li>
      </ul>
    </li>
  </ul>

  <form method="GET" action="/Semestral/buscar.php" class="nav-search-form">
    <input class="flip-card__input" type="text" name="q" placeholder="Buscar películas..." maxlength="100" required>
    <button class="flip-card__btn" type="submit">🔍</button>
  </form>

  <div class="logo">
    <a href="/Semestral/index.php"><img src="/Semestral/css/logo.png" alt="MovieMate Logo"></a>
  </div>

  <div class="user-info">
    Hola 👋 <?= htmlspecialchars($_SESSION['user'] ?? ($_SESSION['role'] === 'admin' ? 'Administrador' : 'Invitado')) ?>
  </div>

  <div class="theme-switch-wrapper">
    <label class="theme-switch">
      <input type="checkbox" id="themeToggle" onchange="toggleTheme()" />
      <span class="slider"></span>
    </label>
  </div>
</nav>