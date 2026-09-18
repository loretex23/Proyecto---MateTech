<?php

require_once "login/auth.php";
include "../sql/basededatos.php";

$rol = $_SESSION["Rol"] ?? "";
$club_id_usuario = $_SESSION["ClubID"] ?? null;

function redirigir_a_partidos() {
    header("Location: partidos.php");
    exit();
}
if ($rol === "Admin") {
    if (isset($_POST["btn_crear"]))     include "../acciones/crear_partido.php";
    if (isset($_POST["btn_estado"]))    include "../acciones/cambiar_estado.php";
    if (isset($_POST["btn_resultado"])) include "../acciones/guardar_resultado.php";
    if (isset($_POST["btn_tarjetas"]))  include "../acciones/guardar_tarjetas.php";
    if (isset($_POST["btn_lesiones"]))  include "../acciones/guardar_lesiones.php";
}

$filtro_club = $rol === "Club" ? "WHERE p.club_local_id = ? OR p.club_visitante_id = ?" : "";
$parametros  = $rol === "Club" ? [$club_id_usuario, $club_id_usuario] : [];

$consulta = $pdo->prepare("
    SELECT p.*, cl.nombre AS local, cv.nombre AS visitante, cat.nombre AS categoria
    FROM partidos p
    JOIN club cl ON cl.id = p.club_local_id
    JOIN club cv ON cv.id = p.club_visitante_id
    JOIN categorias cat ON cat.id = p.categoria_id
    $filtro_club
    ORDER BY p.fecha_partido DESC, p.id DESC
");
$consulta->execute($parametros);
$partidos = $consulta->fetchAll(PDO::FETCH_OBJ);

if ($rol === "Admin") {
    $clubes     = $pdo->query("SELECT id, nombre FROM club WHERE rol='Club' ORDER BY nombre")->fetchAll(PDO::FETCH_OBJ);
    $categorias = $pdo->query("SELECT id, nombre FROM categorias ORDER BY nombre")->fetchAll(PDO::FETCH_OBJ);
}

$estados = [
    "sin_fecha"  => "Sin fecha",
    "programado" => "Programado",
    "jugado"     => "Jugado",
    "pendiente"  => "Pendiente",
    "suspendido" => "Suspendido",
];
$badge = [
    "sin_fecha"  => "secondary",
    "programado" => "primary",
    "jugado"     => "success",
    "pendiente"  => "warning",
    "suspendido" => "danger",
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
<title>LeagueFlow - Partidos</title>
</head>
<body class="fondo-body">
<?php include "diseños/navbar.php"; ?>
<main>

<div class="home-hero">
    <div><h1>Partidos</h1><p>Fixture y resultados de la liga.</p></div>
    <img src="../img/Login-foto0.png" alt="LeagueFlow" class="home-hero-logo">
</div>

<div class="container mt-4">
    <?php if ($rol === "Admin"): ?>
        <button class="btn btn-primary mb-4" data-bs-toggle="modal" data-bs-target="#modalCrear">
            <i class="ti ti-plus"></i> Nuevo partido
        </button>
    <?php endif; ?>

    <div class="table-responsive">
        <table class="table table-striped text-center align-middle">
            <thead>
                <tr>
                    <th>Categoría</th><th>Local</th><th>Resultado</th><th>Visitante</th>
                    <th>Fecha</th><th>Estado</th><th>Detalle</th>
                    <?php if ($rol === "Admin"): ?><th>Acciones</th><?php endif; ?>
                </tr>
            </thead>
            <tbody>
            <?php if (empty($partidos)): ?>
                <tr><td colspan="<?= $rol === "Admin" ? 8 : 7 ?>" class="text-muted py-4">No hay partidos registrados.</td></tr>
            <?php else: foreach ($partidos as $p): ?>
                <tr>
                    <td><span class="badge bg-secondary"><?= htmlspecialchars($p->categoria) ?></span></td>
                    <td><?= htmlspecialchars($p->local) ?></td>
                    <td>
                        <?php if ($p->estado === "jugado"): ?>
                            <strong><?= $p->goles_local ?> - <?= $p->goles_visitante ?></strong>
                        <?php else: ?>
                            <span class="text-muted">-</span>
                        <?php endif; ?>
                    </td>
                    <td><?= htmlspecialchars($p->visitante) ?></td>
                    <td>
                        <?= $p->fecha_partido
                            ? date("d/m/Y H:i", strtotime($p->fecha_partido))
                            : '<span class="text-muted">Sin fecha</span>' ?>
                    </td>
                    <td><span class="badge bg-<?= $badge[$p->estado] ?? "secondary" ?>"><?= $estados[$p->estado] ?? "" ?></span></td>
                    <td>
                        <button class="btn btn-sm btn-outline-info" data-bs-toggle="modal" data-bs-target="#modalDetalle"
                                data-id="<?= $p->id ?>">
                            <i class="ti ti-info-circle"></i> Ver más
                        </button>
                    </td>
                  <?php if ($rol === "Admin"): ?>
<td>
    <button id="gestionar" class="btn btn-sm btn-warning"
        data-bs-toggle="modal" data-bs-target="#modalGestionar"
        data-id="<?= $p->id ?>"
        data-estado="<?= $p->estado ?>"
        data-fecha="<?= htmlspecialchars($p->fecha_partido ?? '', ENT_QUOTES) ?>"
        data-local-id="<?= $p->club_local_id ?>"
        data-visitante-id="<?= $p->club_visitante_id ?>"
        data-categoria-id="<?= $p->categoria_id ?>"
        data-local="<?= htmlspecialchars($p->local, ENT_QUOTES) ?>"
        data-visitante="<?= htmlspecialchars($p->visitante, ENT_QUOTES) ?>"
        data-goles-local="<?= $p->goles_local ?>"
        data-goles-visitante="<?= $p->goles_visitante ?>">
        <i class="ti ti-settings"></i> Gestionar
    </button>
</td>
<?php endif; ?>
                </tr>
            <?php endforeach; endif; ?>
            </tbody>
        </table>
    </div>
</div>
</main>

<footer>&copy; 2026 MateTech. Todos los derechos reservados.
    <img class="foot" src="../img/logo.png" alt="Logo"></footer>

<?php
include "../modales/modal_detalle.php";

if ($rol === "Admin") {
    include "../modales/modal_crear.php";
    include "../modales/modal_gestionar.php";
}
?>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script src="../js/comun.js"></script>
<script src="../js/linea_de_tiempo.js"></script>
<?php if ($rol === "Admin"): ?>
<script src="../js/resultado.js"></script>
<script src="../js/tarjetas.js"></script>
<script src="../js/lesiones.js"></script>
<?php endif; ?>
</body>
</html>
