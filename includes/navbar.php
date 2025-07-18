<?php if (!isset($_SESSION)) session_start(); ?>
<nav class="navbar">
  <button class="menu-toggle" onclick="toggleMenu()">☰</button>
  <ul class="ul">
    <li><a href="index.php">Inicio</a></li>
    <li><a href="buscar.php">Buscar</a></li>
    <li id="cat">
      <a href="#">Categorías ▾</a>
      <ul class="dropdown-menu">
        <li><a href="index.php?set_genre=28">Acción</a></li>
        <li><a href="index.php?set_genre=35">Comedia</a></li>
        <li><a href="index.php?set_genre=18">Drama</a></li>
        <li><a href="index.php?set_genre=27">Terror</a></li>
        <li><a href="index.php?set_genre=10749">Romance</a></li>
      </ul>
    </li>
    <li id="cat">
      <a href="perfil.php">Mi Perfil ▾</a>
      <ul class="dropdown-menu">
        <li><a href="mis_calificaciones.php">Mis Calificaciones</a></li>
         <li><a href="favoritos.php">Mis favoritos</a></li>
        <li><a href="logout.php">Cerrar sesión</a></li>
       
      </ul>
    </li>
  </ul>

 <div class="logo">
  <a href="index.php">  <img src="css/logo.png" alt="MovieMate Logo"></a>
</div>
<div class="user-info">
  👋 <?= htmlspecialchars($_SESSION['user'] ?? 'Invitado') ?>
</div>

  <div class="theme-switch-wrapper">
  <label class="theme-switch">
    <input type="checkbox" id="themeToggle" onchange="toggleTheme()" />
    <span class="slider"></span>
  </label>
</div>

</nav>
