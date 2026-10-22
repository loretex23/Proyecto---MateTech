<?php
require_once "login/auth.php";
include "../sql/basededatos.php";

$rol             = $_SESSION["Rol"] ?? "";
$club_id_usuario = $_SESSION["ClubID"] ?? null;

/* ───────────── Comunicados ───────────── */
$comunicados = $pdo->query("SELECT * FROM comunicados ORDER BY fecha_publicacion DESC LIMIT 6")->fetchAll(PDO::FETCH_OBJ);
$tipos = array_values(array_unique(array_map(fn($c) => $c->tipo, $comunicados)));

/* ───────────── Próximos partidos (solo Club) ───────────── */
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
        ORDER BY p.fecha_partido IS NULL, p.fecha_partido ASC
        LIMIT 4
    ");
    $stmt->execute([$club_id_usuario, $club_id_usuario]);
    $proximos = $stmt->fetchAll(PDO::FETCH_OBJ);
}

/* ───────────── Mini tabla de posiciones ───────────── */
$categorias = $pdo->query("SELECT id, nombre FROM categorias ORDER BY nombre ASC")->fetchAll(PDO::FETCH_OBJ);

// Categoría: GET > categoría del próximo partido del club > primera
$categoria_id = isset($_GET['cat']) ? (int)$_GET['cat'] : 0;
if ($categoria_id === 0 && !empty($proximos)) {
    $categoria_id = (int)$proximos[0]->categoria_id;
}
if ($categoria_id === 0) {
    $categoria_id = (int)($categorias[0]->id ?? 0);
}
$competencia = 'Liga';
$TOP = 5;

$posiciones = [];
if ($categoria_id > 0) {
    $sql = "
        SELECT
            c.id AS club_id,
            c.nombre AS club_nombre,
            COUNT(p.id) AS PJ,
            SUM(CASE WHEN p.club_local_id = c.id THEN p.goles_local ELSE p.goles_visitante END) AS GF,
            SUM(CASE WHEN p.club_local_id = c.id THEN (p.goles_local - p.goles_visitante)
                     ELSE (p.goles_visitante - p.goles_local) END) AS DG,
            SUM(CASE
                WHEN (p.club_local_id = c.id AND p.goles_local > p.goles_visitante) OR
                     (p.club_visitante_id = c.id AND p.goles_visitante > p.goles_local) THEN 3
                WHEN p.goles_local = p.goles_visitante THEN 1
                ELSE 0
            END) AS Pts
        FROM club c
        LEFT JOIN partidos p ON (c.id = p.club_local_id OR c.id = p.club_visitante_id)
            AND p.categoria_id = :categoria_id
            AND p.competencia = :competencia
            AND p.estado = 'jugado'
        WHERE c.rol = 'Club'
        GROUP BY c.id, c.nombre
        ORDER BY Pts DESC, DG DESC, GF DESC, c.nombre ASC
    ";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([':categoria_id' => $categoria_id, ':competencia' => $competencia]);
    $posiciones = $stmt->fetchAll(PDO::FETCH_OBJ);
}

// Top N + fila del club del usuario si quedó afuera del top
$tabla_mostrar = [];
$mi_fila = null;
foreach ($posiciones as $i => $row) {
    $row->pos = $i + 1;
    if ($i < $TOP) $tabla_mostrar[] = $row;
    elseif ($club_id_usuario && $row->club_id == $club_id_usuario) $mi_fila = $row;
}

$cards = [
    ["href" => "jugadores.php",  "icono" => "ti-users",        "titulo" => "Jugadores",  "Admin" => "Agregá, editá y eliminá jugadores de la liga.", "Club" => "Explorá los jugadores de la liga."],
    ["href" => "clubes.php",     "icono" => "ti-shield",       "titulo" => "Clubes",     "Admin" => "Administrá los clubes participantes.",          "Club" => "Conocé los clubes de la competencia."],
    ["href" => "posiciones.php", "icono" => "ti-list-numbers", "titulo" => "Posiciones", "Admin" => "Consultá la tabla de posiciones actualizada.",  "Club" => "Mirá cómo va la tabla de tu liga."],
];

$badge_estado = [
    "sin_fecha"  => "secondary",
    "programado" => "primary",
    "pendiente"  => "warning",
];

// Fila de la tabla (se usa para top y para "mi fila")
function fila_posicion($p, $club_id_usuario) {
    $dg = (int)($p->DG ?? 0);
    $cls = ($club_id_usuario && $club_id_usuario == $p->club_id) ? 'table-primary fw-bold' : '';
    $dg_html = $dg > 0 ? "<span class='text-success'>+$dg</span>"
             : ($dg < 0 ? "<span class='text-danger'>$dg</span>" : "0");
    return "<tr class='$cls'>
                <td><strong>{$p->pos}</strong></td>
                <td class='text-start'>" . htmlspecialchars($p->club_nombre) . "</td>
                <td>" . (int)$p->PJ . "</td>
                <td>$dg_html</td>
                <td class='fw-bold text-primary'>" . (int)($p->Pts ?? 0) . "</td>
            </tr>";
}
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

        <!-- ═════════ Noticias y comunicados ═════════ -->
        <section class="my-5">
            <div class="d-flex justify-content-between align-items-end flex-wrap gap-3 mb-3" style="color:white;">
                <div>
                    <small class="text-uppercase fw-bold" style="letter-spacing:1px; color:#c8e6c9;">
                        <i class="ti ti-news"></i> Boletín oficial
                    </small>
                    <h2 class="mb-1">Noticias y comunicados</h2>
                    <p class="mb-0" style="color:white;">Información oficial, sanciones y novedades directas de la liga</p>
                </div>

                <?php if (!empty($comunicados)): ?>
                <div class="d-flex flex-wrap gap-2" id="filtros">
                    <button type="button" class="badge rounded-pill border-0 bg-light text-dark" data-tipo="todos">Todos</button>
                    <?php foreach ($tipos as $t): ?>
                        <button type="button" class="badge rounded-pill border-0 bg-secondary" data-tipo="<?= htmlspecialchars($t) ?>">
                            <?= htmlspecialchars($t) ?>
                        </button>
                    <?php endforeach; ?>
                </div>
                <?php endif; ?>
            </div>

            <div class="row g-3" id="listaComunicados">
                <?php if (empty($comunicados)): ?>
                    <div class="col-12"><p style="color:white;">No hay comunicados publicados actualmente.</p></div>
                <?php endif; ?>

                <?php foreach ($comunicados as $c): ?>
                    <div class="col-md-6 col-lg-4 item-comunicado" data-tipo="<?= htmlspecialchars($c->tipo) ?>">
                        <article class="card shadow-sm h-100">
                            <div class="card-body d-flex flex-column">
                                <div class="d-flex justify-content-between align-items-center">
                                    <span class="badge bg-primary"><?= htmlspecialchars($c->tipo) ?></span>
                                    <small class="text-muted"><i class="ti ti-calendar"></i> <?= date("d/m/Y", strtotime($c->fecha_publicacion)) ?></small>
                                </div>
                                <h5 class="mt-3"><?= htmlspecialchars($c->titulo) ?></h5>
                                <p class="text-secondary mb-3"><?= nl2br(htmlspecialchars($c->contenido)) ?></p>

                                <?php if (!empty($c->pdf_url)): ?>
                                    <div class="mt-auto border-top pt-2">
                                        <a href="../<?= htmlspecialchars($c->pdf_url) ?>" target="_blank"
                                           class="small fw-bold text-decoration-none d-flex justify-content-between align-items-center"
                                           style="color:#1a4731;">
                                            Ver documento <i class="ti ti-arrow-right"></i>
                                        </a>
                                    </div>
                                <?php endif; ?>
                            </div>
                        </article>
                    </div>
                <?php endforeach; ?>
            </div>
        </section>

        <!-- ═════════ Tabla de posiciones + Próximos partidos ═════════ -->
        <section class="my-5">
            <div class="row g-4">

                <!-- Mini tabla de posiciones -->
                <div class="<?= $rol === 'Club' ? 'col-lg-7' : 'col-12' ?>">
                    <div class="card shadow-sm h-100">
                        <div class="card-body">
                            <div class="d-flex justify-content-between align-items-center flex-wrap gap-2 mb-3">
                                <h5 class="mb-0" style="color:#1a4731;"><i class="ti ti-list-numbers"></i> Tabla de posiciones</h5>
                                <div class="d-flex align-items-center gap-3">
                                    <form method="GET" action="index.php" class="m-0">
                                        <select name="cat" class="form-select form-select-sm" onchange="this.form.submit()" aria-label="Categoría">
                                            <?php foreach ($categorias as $cat): ?>
                                                <option value="<?= $cat->id ?>" <?= $cat->id == $categoria_id ? 'selected' : '' ?>>
                                                    <?= htmlspecialchars($cat->nombre) ?>
                                                </option>
                                            <?php endforeach; ?>
                                        </select>
                                    </form>
                                    <a href="posiciones.php?categoria_id=<?= $categoria_id ?>&competencia=<?= urlencode($competencia) ?>"
                                       class="small fw-bold text-decoration-none text-nowrap" style="color:#1a4731;">
                                        Ver tabla completa →
                                    </a>
                                </div>
                            </div>

                            <div class="table-responsive">
                                <table class="table table-striped table-hover mb-0 text-center align-middle">
                                    <thead class="table-dark">
                                        <tr>
                                            <th style="width:50px;">#</th>
                                            <th class="text-start">Club</th>
                                            <th>PJ</th>
                                            <th>DIF</th>
                                            <th>Pts</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php if (empty($tabla_mostrar)): ?>
                                            <tr><td colspan="5" class="text-muted py-4">No hay datos registrados para esta categoría.</td></tr>
                                        <?php else: ?>
                                            <?php foreach ($tabla_mostrar as $p) echo fila_posicion($p, $club_id_usuario); ?>
                                            <?php if ($mi_fila): ?>
                                                <tr><td colspan="5" class="text-muted py-0">⋮</td></tr>
                                                <?= fila_posicion($mi_fila, $club_id_usuario) ?>
                                            <?php endif; ?>
                                        <?php endif; ?>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Próximos partidos (solo Club) -->
                <?php if ($rol === "Club"): ?>
                <div class="col-lg-5">
                    <div class="card shadow-sm h-100">
                        <div class="card-body">
                            <div class="d-flex justify-content-between align-items-center mb-3">
                                <h5 class="mb-0" style="color:#1a4731;"><i class="ti ti-calendar-event"></i> Tus próximos partidos</h5>
                                <a href="partidos.php" class="small fw-bold text-decoration-none text-nowrap" style="color:#1a4731;">Ver todos →</a>
                            </div>

                            <?php if (empty($proximos)): ?>
                                <p class="text-muted mb-0">No tenés partidos próximos registrados.</p>
                            <?php else: foreach ($proximos as $p): ?>
                                <div class="border rounded p-3 mb-2">
                                    <div class="d-flex justify-content-between align-items-start mb-1">
                                        <span class="badge bg-secondary"><?= htmlspecialchars($p->categoria) ?></span>
                                        <span class="badge bg-<?= $badge_estado[$p->estado] ?? 'secondary' ?>">
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
                                    <small class="text-muted d-block mt-2">
                                        <?php if ($p->fecha_partido): ?>
                                            <i class="ti ti-calendar"></i> <?= date("d/m/Y H:i", strtotime($p->fecha_partido)) ?>
                                        <?php else: ?>
                                            Sin fecha definida
                                        <?php endif; ?>
                                    </small>
                                </div>
                            <?php endforeach; endif; ?>
                        </div>
                    </div>
                </div>
                <?php endif; ?>

            </div>
        </section>

        <!-- ═════════ Accesos rápidos (clases originales home-card) ═════════ -->
        <div class="home-cards">
            <?php foreach ($cards as $card): ?>
                <a href="<?= $card['href'] ?>" class="home-card">
                    <div class="home-card-icon"><i class="ti <?= $card['icono'] ?>"></i></div>
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
<script>
    // Filtro de comunicados por tipo
    const botones = document.querySelectorAll('#filtros button');
    const items   = document.querySelectorAll('.item-comunicado');
    botones.forEach(btn => {
        btn.addEventListener('click', () => {
            botones.forEach(b => {
                b.classList.remove('bg-light', 'text-dark');
                b.classList.add('bg-secondary');
            });
            btn.classList.remove('bg-secondary');
            btn.classList.add('bg-light', 'text-dark');

            const tipo = btn.dataset.tipo;
            items.forEach(it => {
                it.style.display = (tipo === 'todos' || it.dataset.tipo === tipo) ? '' : 'none';
            });
        });
    });
</script>
</body>
</html>