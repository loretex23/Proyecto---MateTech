<?php
require_once __DIR__ . "/../login/auth.php";
$rol = $_SESSION["Rol"] ?? "";
$pagina_actual = basename($_SERVER['PHP_SELF']);
$nombre_club = $_SESSION["NombreClub"] ?? "";
?>

<style>
    /* ── Navbar responsive (celulares y tablets) ── */
    @media (max-width: 991.98px) {
        /* El menú se despliega como panel debajo de la barra de 68px */
        #navbar .navbar-collapse {
            position: absolute;
            top: 68px;
            left: 12px;
            right: 12px;
            background: rgba(15, 40, 25, 0.97);
            backdrop-filter: blur(18px);
            -webkit-backdrop-filter: blur(18px);
            border-bottom: 1px solid rgba(255, 255, 255, 0.12);
            border-radius: 0 0 14px 14px;
            padding: 12px 16px 16px;
            box-shadow: 0 10px 24px rgba(0, 0, 0, 0.35);
        }
        #navbar #nav-list {
            flex-direction: column;
            align-items: center;
            margin-right: 0;
            width: 100%;
        }
        #navbar .nav-item { padding: 0; width: 100%; text-align: center; }
        #navbar .nav-link {
            display: block;
            padding: 12px 8px;
            border-bottom: 1px solid rgba(255, 255, 255, 0.08);
        }
        #navbar .nav-item:last-child .nav-link { border-bottom: none; }
        #navbar .navbar-nav .nav-link.active {
            border-bottom: 1px solid rgba(255, 255, 255, 0.08);
            border-left: 3px solid #fff4cc;
            padding-left: 12px;
        }
        #navbar .web-logo { width: 120px; margin-left: 10px; }
        #navbar .navbar-brand { padding-inline: 4px; }
        #navbar .dropdown-menu { min-width: 190px; }
    }

    @media (max-width: 575.98px) {
        #navbar .web-logo { width: 100px; margin-left: 6px; }
        #navbar .nav-logo { width: 32px; }
        #navbar .navbar-brand { min-width: 0; }
        #navbar .container-fluid { padding-inline: 10px; }
        #navbar .navbar-collapse { left: 8px; right: 8px; }
        #navbar .d-flex.align-items-center { gap: 6px !important; margin-right: 0 !important; }
        #navbar .nav-user-btn { padding: 6px 9px; }
        #navbar .dropdown-menu {
            min-width: 180px;
            margin-top: 8px !important;
        }
    }

    #navbar .nav-user-btn { max-width: min(44vw, 320px); }
    #navbar .nav-user-name {
        overflow: hidden;
        text-overflow: ellipsis;
        white-space: nowrap;
        font-size: .9rem;
    }
</style>

<nav class="navbar navbar-expand-lg" id="navbar">

    <div class="container-fluid">
        <a class="navbar-brand" href="../paginas/index.php">
            <img src="../img/Login-foto0.png" class="nav-logo" alt="LeagueFlow">
            <img src="../img/LeagueFlow.png" class="web-logo" alt="LeagueFlow">
        </a>

        <!-- Menú de páginas (en celular se abre con el botón hamburguesa) -->
        <div class="collapse navbar-collapse" id="navbarNav">
            <ul class="navbar-nav" id="nav-list">
                <li class="nav-item">
                    <a class="nav-link <?php echo ($pagina_actual == 'index.php') ? 'active' : ''; ?>" href="../paginas/index.php">Home</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link <?php echo ($pagina_actual == 'jugadores.php') ? 'active' : ''; ?>" href="../paginas/jugadores.php">Jugadores</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link <?php echo ($pagina_actual == 'clubes.php') ? 'active' : ''; ?>" href="../paginas/clubes.php">Clubes</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link <?php echo ($pagina_actual == 'partidos.php') ? 'active' : ''; ?>" href="../paginas/partidos.php">Partidos</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link <?php echo ($pagina_actual == 'posiciones.php') ? 'active' : ''; ?>" href="../paginas/posiciones.php">Posiciones</a>
                </li>
            </ul>
        </div>

        <!-- Usuario + botón hamburguesa -->
        <div class="d-flex align-items-center gap-2 ms-auto me-2 me-lg-3">
            <div class="dropdown">
                <button type="button"
                        class="nav-user-btn"
                        data-bs-toggle="dropdown"
                        aria-expanded="false"
                        aria-label="Menú de usuario: <?= htmlspecialchars($nombre_club, ENT_QUOTES, 'UTF-8') ?>">
                    <i class="ti ti-user-circle" style="font-size:20px;"></i>
                    <span class="nav-user-name"><?= htmlspecialchars($nombre_club, ENT_QUOTES, 'UTF-8') ?></span>
                    <i class="ti ti-chevron-down" style="font-size:13px; opacity:0.7;"></i>
                </button>
                <ul class="dropdown-menu dropdown-menu-end">
                    <li><a class="dropdown-item" href="../paginas/noticias.php"><i class="ti ti-news"></i> Noticias</a></li>
                    <li><a class="dropdown-item" href="../paginas/transferencias.php"><i class="ti ti-exchange"></i> Transferencias</a></li>
                    <li><a class="dropdown-item text-danger" style="border: 1px solid rgb(248, 113, 113);" href="../paginas/login/ssn_closed.php"><i class="ti ti-logout"></i> Cerrar sesión</a></li>
                </ul>
            </div>

            <!-- Solo visible en celular / tablet -->
            <button type="button"
                    class="nav-user-btn d-lg-none"
                    data-bs-toggle="collapse"
                    data-bs-target="#navbarNav"
                    aria-controls="navbarNav"
                    aria-expanded="false"
                    aria-label="Abrir menú de navegación">
                <i class="ti ti-menu-2" style="font-size:22px;"></i>
            </button>
        </div>
    </div>
</nav>