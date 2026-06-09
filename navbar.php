<nav class="navbar navbar-expand-sm bg-dark navbar-dark">
    <div class="container-fluid">
        <a class="navbar-brand" href="index.php">
            <img src="img/logo.jpg" alt="Avatar Logo" class="rounded-pill nav-logo"> 
            <span class="navbar-text">MateTech</span>
        </a>
        <div class="collapse navbar-collapse" id="navbarNav">
            <ul class="navbar-nav">
                <li class="nav-item"><a class="nav-link active" href="index.php">Home</a></li>
                <li class="nav-item"><a class="nav-link" href="jugadores.php">Jugadores</a></li>
                <li class="nav-item"><a class="nav-link" href="clubes.php">Clubes</a></li>
                <li class="nav-item"><a class="nav-link" href="posiciones.php">Posiciones</a></li>
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
            <img src="https://static.vecteezy.com/..." alt="Avatar Logo" class="rounded-pill nav-avatar"> 
        </a>
    </div>
</nav>