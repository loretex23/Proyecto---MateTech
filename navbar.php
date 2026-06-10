<nav class="navbar navbar-expand-sm bg-dark navbar-dark">
    <div class="container-fluid">
        <a class="navbar-brand" href="index.php">
            <img src="img/logo.jpg" alt="Avatar Logo" class="rounded-pill nav-logo"> 
            <span class="navbar-text">MateTech</span>
        </a>
        <div class="collapse navbar-collapse" id="navbarNav">
           <ul class="navbar-nav">
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
    
    <div class="container-fluid-right">
        <a class="navbar-brand" href="#">
            <span class="navbar-text">
                <?php 
                    echo "Nombre del usuario"; 
                ?>
            </span>
             <img src="https://static.vecteezy.com/system/resources/previews/006/303/647/non_2x/job-waiter-logo-icon-symbol-designs-vector.jpg" 
             alt="Avatar Logo" style="width:40px;" class="rounded-pill">
        </a>
    </div>
</nav>