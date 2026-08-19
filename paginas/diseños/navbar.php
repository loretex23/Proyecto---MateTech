<?php
require_once __DIR__ . "/../login/auth.php";
$rol = $_SESSION["Rol"] ?? "";
$pagina_actual = basename($_SERVER['PHP_SELF']);
?>

<nav class="navbar navbar-expand-lg" id="navbar">
    
    <div class="container-fluid">
        <a class="navbar-brand" href="../paginas/index.php">
            <img src="../img/Login-foto0.png" class="nav-logo">
            <img src="../img/LeagueFlow.png" class="web-logo">
        </a>
        <div class="collapse navbar-collapse" id="navbarNav">
            <ul class="navbar-nav" id="nav-list">
                <li class="nav-item">
                    <a class="nav-link <?php echo ($pagina_actual == 'index.php') ? 'active' : ''; ?>" href="../paginas/index.php">Home</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link <?php echo ($pagina_actual == 'jugadores.php') ? 'active' : ''; ?>" href="../paginas/jugadores.php">
                        <?php echo ($rol === "Admin") ? "Jugadores CRUD" : "Jugadores"; ?>
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link <?php echo ($pagina_actual == 'clubes.php') ? 'active' : ''; ?>" href="../paginas/clubes.php">
                        <?php echo ($rol === "Admin") ? "Clubes CRUD" : "Clubes"; ?>
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link <?php echo ($pagina_actual == 'posiciones.php') ? 'active' : ''; ?>" href="../paginas/posiciones.php">Posiciones</a>
                </li>
            </ul>
        </div>

       <div class="dropdown me-3">
    <button type="button" 
            class="nav-user-btn" 
            data-bs-toggle="dropdown" 
            aria-expanded="false"
            style="background: rgba(255,255,255,0.10); border: 1px solid rgba(255,255,255,0.25); border-radius: 8px; color: #fff; padding: 6px 12px; display: flex; align-items: center; gap: 6px; cursor: pointer;">
        <i class="ti ti-user-circle" style="font-size:20px;"></i>
        <i class="ti ti-chevron-down" style="font-size:13px; opacity:0.7;"></i>
    </button>
    <ul class="dropdown-menu dropdown-menu-end">
        <li><a class="dropdown-item" href="../paginas/ajustes.php"><i class="ti ti-settings"></i> Ajustes</a></li>
        <li><a class="dropdown-item" href="../paginas/noticias.php"><i class="ti ti-news"></i> Noticias</a></li>
        <li><a class="dropdown-item" href="../paginas/transferencias.php"><i class="ti ti-exchange"></i> Transferencias</a></li>
        <li><a class="dropdown-item text-danger" style="border: 1px solid rgb(248, 113, 113);" href="../paginas/login/ssn_closed.php"><i class="ti ti-logout"></i> Cerrar sesión</a></li>
    </ul>
</div>
    </div>
</nav>

