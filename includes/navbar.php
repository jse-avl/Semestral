<?php if (!isset($_SESSION)) session_start(); ?>
<head>
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css" />
</head>
<nav class="navbar">
  <button class="menu-toggle" onclick="toggleMenu()">☰</button>
  <ul class="ul">
    <li><a href="index.php"><i class="fa-solid fa-house"></i>Inicio</a></li>
    <li id="cat">
      <a href="#"><i class="fa-solid fa-layer-group"></i>Categorías ▾</a>
      <ul class="dropdown-menu">
        <li><a href="index.php?set_genre=28">Acción</a></li>
        <li><a href="index.php?set_genre=35">Comedia</a></li>
        <li><a href="index.php?set_genre=18">Drama</a></li>
        <li><a href="index.php?set_genre=27">Terror</a></li>
        <li><a href="index.php?set_genre=10749">Romance</a></li>
      </ul>
    </li>
    <li id="cat">
      <a href="perfil.php"><i class="fa-solid fa-user"></i>Mi Perfil ▾</a>
      <ul class="dropdown-menu">
        <li><a href="mis_calificaciones.php">Mis Calificaciones</a></li>
         <li><a href="favoritos.php">Mis favoritos</a></li>
        <li><a href="logout.php">Cerrar sesión</a></li>
       
      </ul>
    </li>
  </ul>

  
    <form method="GET" action="buscar.php" class="nav-search-form">
  <input class="flip-card__input" type="text" name="q" placeholder="Buscar películas..." maxlength="100" required>
  <button class="flip-card__btn"  type="submit"><i class="fa-solid fa-magnifying-glass"></i></button>
</form>

 <div class="logo">
  <a href="index.php">  <img src="css/logo.png" alt="MovieMate Logo"></a>
</div>
<div class="user-info">
  Hola 👋 <?= htmlspecialchars($_SESSION['user'] ?? 'Invitado') ?>
</div>

  <div class="theme-switch-wrapper">
  <label class="theme-switch">
    <input type="checkbox" id="themeToggle" onchange="toggleTheme()" />
    <span class="slider"></span>
  </label>
</div>

</nav>
