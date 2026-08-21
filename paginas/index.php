<?php
require_once "login/auth.php";
$rol = $_SESSION["Rol"] ?? "";
?>
<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="icon" href="../img/logo.png" type="image/png">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/@tabler/icons-webfont@latest/dist/tabler-icons.min.css">
    <link href="../estilos.css" rel="stylesheet">
    <title>LeagueFlow — Inicio</title>
</head>

<body class="fondo-body">

    <?php include 'diseños/navbar.php'; ?>

    <main>
         <div class="container" style="padding-bottom: 80px;">

        <div class="home-hero">
            <div>
                <h1>Bienvenido a <b>LeagueFlow</b></h1>
                <p>Gestioná jugadores, clubes y posiciones de tu liga desde un solo lugar.</p>
            </div>
            <img src="../img/Login-foto0.png" alt="LeagueFlow" class="home-hero-logo">
        </div>

        <?php if ($rol === "Admin"): ?>
            <div class="home-cards">
                <a href="jugadores.php" class="home-card">
                    <h3>Jugadores</h3>
                    <p>Agregá, editá y eliminá jugadores de la liga.</p>
                </a>
                <a href="clubes.php" class="home-card">
                    <h3>Clubes</h3>
                    <p>Administrá los clubes participantes.</p>
                </a>
                <a href="posiciones.php" class="home-card">
                    <h3>Posiciones</h3>
                    <p>Consultá la tabla de posiciones actualizada.</p>
                </a>
            </div>
        <?php else: ?>
            <div class="home-cards">
                <a href="jugadores.php" class="home-card">
                    <h3>Jugadores</h3>
                    <p>Explorá los jugadores de la liga.</p>
                </a>
                <a href="clubes.php" class="home-card">
                    <h3>Clubes</h3>
                    <p>Conocé los clubes de la competencia.</p>
                </a>
                <a href="posiciones.php" class="home-card">
                    <h3>Posiciones</h3>
                    <p>Mirá cómo va la tabla de tu liga.</p>
                </a>
            </div>
        <?php endif; ?>

        </div>
</body>
</main>
<footer>
    &copy; 2026 MateTech. Todos los derechos reservados.
    <img class="foot" src="../img/logo.png" alt="Logo">
</footer>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>

</html>