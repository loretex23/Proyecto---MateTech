<?php
require_once "login/auth.php";

$rol = $_SESSION["Rol"] ?? "";
?>



        <?php if ($rol === "Club"): ?> 


            
<nav class="navbar navbar-expand-lg" ID="navbar">
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.7/dist/js/bootstrap.bundle.min.js"></script>
    <div class="container-fluid">
        <a class="navbar-brand" href="index.php">
            <img src="../img/LogoFlow.png" alt="Avatar Logo" class="rounded-pill nav-logo" id="nav-logo"> 
            <img src="../img/LeagueFlow.png" alt="Avatar Logo" class="rounded-pill web-logo" id="web-logo">
        </a>
        <div class="collapse navbar-collapse" id="navbarNav">
           <ul class="navbar-nav" ID="nav-list">
    <?php
    $pagina_actual = basename($_SERVER['PHP_SELF']);
    ?>

    <li class="nav-item">
        <a class="nav-link <?php echo ($pagina_actual == 'index.php') ? 'active' : ''; ?>" href="index.php">Home</a></li>
    <li class="nav-item">
        <a class="nav-link <?php echo ($pagina_actual == 'jugadores.php') ? 'active' : ''; ?>" href="jugadores.php">Jugadores</a> </li>
    <li class="nav-item">
        <a class="nav-link <?php echo ($pagina_actual == 'clubes.php') ? 'active' : ''; ?>" href="clubes.php">Clubes</a></li>
    <li class="nav-item">
        <a class="nav-link <?php echo ($pagina_actual == 'posiciones.php') ? 'active' : ''; ?>" href="posiciones.php">Posiciones</a></li>
</ul>
        </div>
    </div>

<div class="dropdown">
  <button type="button" class="btn-primary dropdown-toggle" data-bs-toggle="dropdown">
    <img src="https://static.vecteezy.com/system/resources/previews/006/303/647/non_2x/job-waiter-logo-icon-symbol-designs-vector.jpg" 
    alt="Avatar Logo" style="width:40px;" class="rounded-pill">
  </button>
  <ul class="dropdown-menu dropdown-menu-end">
    <li><a class="dropdown-item" href="ajustes.php">Ajustes</a></li>
    <li><a class="dropdown-item" href="login/ssn_closed.php">Cerrar sesión</a></li>
  </ul>
</div>
</nav>

        <span class="badge bg-primary mb-3">
            Club
        </span>







    <?php else: ?>  




<nav class="navbar navbar-expand-lg" ID="navbar">
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.7/dist/js/bootstrap.bundle.min.js"></script>
    <div class="container-fluid">
        <a class="navbar-brand" href="index.php">
            <img src="../img/LogoFlow.png" alt="Avatar Logo" class="rounded-pill nav-logo" id="nav-logo"> 
            <img src="../img/LeagueFlow.png" alt="Avatar Logo" class="rounded-pill web-logo" id="web-logo">
        </a>
        <div class="collapse navbar-collapse" id="navbarNav">
           <ul class="navbar-nav" ID="nav-list">
    <?php
    $pagina_actual = basename($_SERVER['PHP_SELF']);
    ?>

    <li class="nav-item">
        <a class="nav-link <?php echo ($pagina_actual == 'index.php') ? 'active' : ''; ?>" href="index.php">Home</a></li>
    <li class="nav-item">
        <a class="nav-link <?php echo ($pagina_actual == 'jugadores.php') ? 'active' : ''; ?>" href="jugadores.php">Jugadores CRUD</a> </li>
    <li class="nav-item">
        <a class="nav-link <?php echo ($pagina_actual == 'clubes.php') ? 'active' : ''; ?>" href="clubes.php">Clubes CRUD</a></li>
    <li class="nav-item">
        <a class="nav-link <?php echo ($pagina_actual == 'posiciones.php') ? 'active' : ''; ?>" href="posiciones.php">Posiciones</a></li>
</ul>
        </div>
    </div>

<div class="dropdown">
  <button type="button" class="btn-primary dropdown-toggle" data-bs-toggle="dropdown">
    <img src="https://static.vecteezy.com/system/resources/previews/006/303/647/non_2x/job-waiter-logo-icon-symbol-designs-vector.jpg" 
    alt="Avatar Logo" style="width:40px;" class="rounded-pill">
  </button>
  <ul class="dropdown-menu dropdown-menu-end">
    <li><a class="dropdown-item" href="ajustes.php">Ajustes</a></li>
    <li><a class="dropdown-item" href="login/ssn_closed.php">Cerrar sesión</a></li>
  </ul>
</div>
</nav>

        <span class="badge bg-danger mb-3">
            Administrador
        </span>

    <?php endif; ?>

