<nav class="navbar navbar-expand-lg" ID="navbar">
    <div class="container-fluid">
        <a class="navbar-brand" href="/web/paginas/clubes/indexclub.php">
            <img src="/web/img/Logo_no_letters.jpeg" alt="Avatar Logo" class="rounded-pill nav-logo" id="nav-logo"> 
            <span class="navbar-text" id="navbar-text">MateTech</span>
        </a>
        <div class="collapse navbar-collapse" id="navbarNav">
           <ul class="navbar-nav" ID="nav-list">
    <?php
    $pagina_actual = basename($_SERVER['PHP_SELF']);
    ?>

    <li class="nav-item">
        <a class="nav-link <?php echo ($pagina_actual == 'indexclub.php') ? 'active' : ''; ?>" href="/web/paginas/clubes/indexclub.php">Home</a></li>
    <li class="nav-item">
        <a class="nav-link <?php echo ($pagina_actual == 'jugadoresclub.php') ? 'active' : ''; ?>" href="/web/paginas/clubes/jugadoresclub.php">Jugadores</a> </li>
    <li class="nav-item">
        <a class="nav-link <?php echo ($pagina_actual == 'clubesclub.php') ? 'active' : ''; ?>" href="/web/paginas/clubes/clubesclub.php">Clubes</a></li>
    <li class="nav-item">
        <a class="nav-link <?php echo ($pagina_actual == 'posicionesclub.php') ? 'active' : ''; ?>" href="/web/paginas/clubes/posicionesclub.php">Posiciones</a></li>
</ul>
        </div>
    </div>
    
    <div class="container-fluid-right">
        <details class="user-menu">
            <summary>
                <img src="https://static.vecteezy.com/system/resources/previews/006/303/647/non_2x/job-waiter-logo-icon-symbol-designs-vector.jpg" alt="Avatar Logo" class="rounded-pill nav-avatar">
            </summary>
            <div class="user-dropdown">
                <strong><?php echo htmlspecialchars($_SESSION['usuario_nombre'] ?? 'Usuario', ENT_QUOTES, 'UTF-8'); ?></strong>
                <a href="/web/paginas/login/logout.php">Cerrar sesion</a>
            </div>
        </details>
    </div>
</nav>
