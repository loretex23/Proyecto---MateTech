<nav class="navbar navbar-expand-lg" ID="navbar">
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.7/dist/js/bootstrap.bundle.min.js"></script>
    <div class="container-fluid">
        <a class="navbar-brand" href="index.php">
            <img src="img/LogoFlow.png" alt="Avatar Logo" class="rounded-pill nav-logo" id="nav-logo"> 
            <img src="img/LeagueFlow.png" alt="Avatar Logo" class="rounded-pill web-logo" id="web-logo">
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
  <button type="button" class="btn btn-primary dropdown-toggle" data-bs-toggle="dropdown">
    <img src="https://static.vecteezy.com/system/resources/previews/006/303/647/non_2x/job-waiter-logo-icon-symbol-designs-vector.jpg" 
    alt="Avatar Logo" style="width:40px;" class="rounded-pill">
  </button>
  <ul class="dropdown-menu dropdown-menu-end">
    <li><a class="dropdown-item" href="#">Link 1</a></li>
    <li><a class="dropdown-item" href="#">Link 2</a></li>
    <li><a class="dropdown-item" href="#">Link 3</a></li>
  </ul>
</div>
</nav>