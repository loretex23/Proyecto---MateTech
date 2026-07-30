

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="../estilos.css" rel="stylesheet">

    <title>LeagueFlow - Inicio</title>
</head>
<body>

<?php include 'diseños/navbar.php'; ?>

<?php if ($rol === "Admin"): ?>

  <div class="container mt-4">

    <h1>Bienvenidos a <b>LeagueFlow</b></h1>

    <p>Este es el contenido principal de la página de inicio de Admin.</p>

    <br>

    <p>En esta página encontrarás información sobre jugadores, clubes y posiciones de la liga.</p>

    <p>Explora las diferentes secciones para conocer más sobre el mundo del fútbol.</p>

    <p><b>¡Disfruta tu visita!</b></p>

</div>
    <?php else: ?>

<div class="container mt-4">

    <h1>Bienvenidos a <b>LeagueFlow</b></h1>

    <p>Este es el contenido principal de la página de inicio de Club.</p>

    <br>

    <p>En esta página encontrarás información sobre jugadores, clubes y posiciones de la liga.</p>

    <p>Explora las diferentes secciones para conocer más sobre el mundo del fútbol.</p>

    <p><b>¡Disfruta tu visita!</b></p>

</div>

    <?php endif; ?>

</body>
<footer>
        &copy; 2026 MateTech. Todos los derechos reservados.
        <img class="foot" src="../img/logo.png" alt="Logo">
    </footer>
</html>