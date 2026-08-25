<?php
require_once "login/auth.php";
include "../sql/basededatos.php";

$rol             = $_SESSION["Rol"] ?? "";
$club_id_usuario = $_SESSION["ClubID"] ?? null;

$comunicados = $pdo->query("SELECT * FROM comunicados ORDER BY fecha_publicacion DESC LIMIT 5")->fetchAll(PDO::FETCH_OBJ);

// Próximos partidos — solo para Club
$proximos = [];
if ($rol === "Club" && $club_id_usuario) {
    $stmt = $pdo->prepare("
        SELECT p.*, cl.nombre AS local, cv.nombre AS visitante, cat.nombre AS categoria
        FROM partidos p
        JOIN club cl  ON cl.id  = p.club_local_id
        JOIN club cv  ON cv.id  = p.club_visitante_id
        JOIN categorias cat ON cat.id = p.categoria_id
        WHERE (p.club_local_id = ? OR p.club_visitante_id = ?)
          AND p.estado IN ('programado', 'pendiente', 'sin_fecha')
        ORDER BY p.fecha_partido ASC
        LIMIT 5
    ");
    $stmt->execute([$club_id_usuario, $club_id_usuario]);
    $proximos = $stmt->fetchAll(PDO::FETCH_OBJ);
}

$cards = [
    ["href" => "jugadores.php",  "titulo" => "Jugadores",  "Admin" => "Agregá, editá y eliminá jugadores de la liga.", "Club" => "Explorá los jugadores de la liga."],
    ["href" => "clubes.php",     "titulo" => "Clubes",     "Admin" => "Administrá los clubes participantes.",          "Club" => "Conocé los clubes de la competencia."],
    ["href" => "posiciones.php", "titulo" => "Posiciones", "Admin" => "Consultá la tabla de posiciones actualizada.",  "Club" => "Mirá cómo va la tabla de tu liga."],
];

$badge_estado = [
    "sin_fecha"  => "secondary",
    "programado" => "primary",
    "pendiente"  => "warning",
];
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/@tabler/icons-webfont@latest/dist/tabler-icons.min.css">
    <link rel="icon" href="../img/logo.png" type="image/png">
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

        <!-- Comunicados + Próximos partidos -->
        <section class="my-5">
            <div class="row g-4">

                <!-- Comunicados -->
                <div class="<?= $rol === 'Club' ? 'col-lg-7' : 'col-12' ?>">
                    <div style="color:white;" class="mb-3">
                        <h2>Noticias y comunicados</h2>
                        <p style="color:white;">Información oficial de la liga</p>
                    </div>
                    <div class="row g-3">
                        <?php if (empty($comunicados)): ?>
                            <div class="col-12"><p class="text-muted">No hay comunicados publicados actualmente.</p></div>
                        <?php endif; ?>
                        <?php foreach ($comunicados as $c): ?>
                            <div class="col-12">
                                <article class="card shadow-sm">
                                    <div class="card-body">
                                        <span class="badge bg-primary"><?= htmlspecialchars($c->tipo) ?></span>
                                        <small class="text-muted d-block mt-2"><?= date("d/m/Y", strtotime($c->fecha_publicacion)) ?></small>
                                        <h5 class="mt-2"><?= htmlspecialchars($c->titulo) ?></h5>
                                        <p class="text-secondary mb-1"><?= nl2br(htmlspecialchars($c->contenido)) ?></p>
                                        <?php if (!empty($c->pdf_url)): ?>
                                            <a href="../<?= htmlspecialchars($c->pdf_url) ?>" target="_blank" class="btn btn-sm btn-outline-secondary">Ver documento</a>
                                        <?php endif; ?>
                                    </div>
                                </article>
                            </div>
                        <?php endforeach; ?>
                    </div>
                </div>

                <!-- Próximos partidos (solo Club) -->
                <?php if ($rol === "Club"): ?>
                <div class="col-lg-5">
                    <div style="color:white;" class="mb-3">
                        <h2>Tus próximos partidos</h2>
                        <p style="color:white;">Partidos pendientes de tu club</p>
                    </div>
                    <?php if (empty($proximos)): ?>
                        <p class="text-muted">No tenés partidos próximos registrados.</p>
                    <?php else: foreach ($proximos as $p): ?>
                        <div class="card shadow-sm mb-3">
                            <div class="card-body">
                                <div class="d-flex justify-content-between align-items-start mb-1">
                                    <span class="badge bg-secondary"><?= htmlspecialchars($p->categoria) ?></span>
                                    <span class="badge bg-<?= $badge_estado[$p->estado] ?>">
                                        <?= ucfirst(str_replace("_", " ", $p->estado)) ?>
                                    </span>
                                </div>
                                <div class="d-flex justify-content-between align-items-center mt-2">
                                    <span class="fw-bold <?= $p->club_local_id == $club_id_usuario ? 'text-primary' : '' ?>">
                                        <?= htmlspecialchars($p->local) ?>
                                    </span>
                                    <span class="text-muted mx-2">vs</span>
                                    <span class="fw-bold <?= $p->club_visitante_id == $club_id_usuario ? 'text-primary' : '' ?>">
                                        <?= htmlspecialchars($p->visitante) ?>
                                    </span>
                                </div>
                                <?php if ($p->fecha_partido): ?>
                                    <small class="text-muted d-block mt-2">
                                        <i class="ti ti-calendar"></i>
                                        <?= date("d/m/Y H:i", strtotime($p->fecha_partido)) ?>
                                    </small>
                                <?php else: ?>
                                    <small class="text-muted d-block mt-2">Sin fecha definida</small>
                                <?php endif; ?>
                            </div>
                        </div>
                    <?php endforeach; endif; ?>
                    <a href="partidos.php" class="btn btn-outline-light btn-sm mt-1">Ver todos los partidos</a>
                </div>
                <?php endif; ?>

            </div>
        </section>

        <div class="home-cards">
            <?php foreach ($cards as $card): ?>
                <a href="<?= $card['href'] ?>" class="home-card">
                    <h3><?= $card['titulo'] ?></h3>
                    <p><?= $rol === "Admin" ? $card['Admin'] : $card['Club'] ?></p>
                </a>
            <?php endforeach; ?>
        </div>

    </div>
</main>

<footer>
    &copy; 2026 MateTech. Todos los derechos reservados.
    <img class="foot" src="../img/logo.png" alt="Logo">
</footer>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>