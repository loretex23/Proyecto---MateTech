<?php

require_once "login/auth.php";
include "../sql/basededatos.php";

$rol = $_SESSION["Rol"] ?? "";
$club_id_usuario = $_SESSION["ClubID"] ?? null;

// Capturar filtros de competición y categoría
$filtro_competencia = $_GET['competencia'] ?? '';
$filtro_categoria   = $_GET['categoria_id'] ?? '';

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

// Construcción dinámica de condiciones WHERE
$where = [];
$parametros = [];

// Restricción por rol de club
if ($rol === "Club") {
    $where[] = "(p.club_local_id = ? OR p.club_visitante_id = ?)";
    $parametros[] = $club_id_usuario;
    $parametros[] = $club_id_usuario;
}

// Filtro por competición
if (!empty($filtro_competencia)) {
    $where[] = "p.competencia = ?";
    $parametros[] = $filtro_competencia;
}

// Filtro por categoría
if (!empty($filtro_categoria)) {
    $where[] = "p.categoria_id = ?";
    $parametros[] = $filtro_categoria;
}

$sql = "
    SELECT p.*, cl.nombre AS local, cv.nombre AS visitante, cat.nombre AS categoria
    FROM partidos p
    JOIN club cl ON cl.id = p.club_local_id
    JOIN club cv ON cv.id = p.club_visitante_id
    JOIN categorias cat ON cat.id = p.categoria_id
";

if (!empty($where)) {
    $sql .= " WHERE " . implode(" AND ", $where);
}

$sql .= " ORDER BY p.fecha_partido DESC, p.id DESC";

$consulta = $pdo->prepare($sql);
$consulta->execute($parametros);
$partidos = $consulta->fetchAll(PDO::FETCH_OBJ);

if ($rol === "Admin") {
    $clubes     = $pdo->query("SELECT id, nombre FROM club WHERE rol='Club' ORDER BY nombre")->fetchAll(PDO::FETCH_OBJ);
}

// Obtener categorías para el selector
$categorias = $pdo->query("SELECT id, nombre FROM categorias ORDER BY nombre")->fetchAll(PDO::FETCH_OBJ);

// Obtener competencias únicas existentes en la tabla partidos para el selector de competición
$competencias = $pdo->query("SELECT DISTINCT competencia FROM partidos WHERE competencia IS NOT NULL AND competencia != '' ORDER BY competencia")->fetchAll(PDO::FETCH_OBJ);

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
    <img src="../img/Login-foto0.png" alt="LeagueFlow" class="home-hero-logo">
</div>

<div class="container mt-4">
    <h2 class="text-center mb-4 font-weight-bold">Fixture de Partidos</h2>

    <!-- Formulario de Filtros -->
    <div class="card mb-4 shadow-sm p-3 bg-body-tertiary rounded">
        <form method="GET" action="partidos.php" class="row g-3 align-items-end">
            
            <!-- Selector de Competición -->
            <div class="col-md-4">
                <label for="competencia" class="form-label font-weight-bold">Competición</label>
                <select class="form-select" id="competencia" name="competencia">
                    <option value="">Todas las competiciones</option>
                    <?php foreach ($competencias as $comp): ?>
                        <option value="<?= htmlspecialchars($comp->competencia) ?>" <?= $filtro_competencia === $comp->competencia ? 'selected' : '' ?>>
                            <?= htmlspecialchars($comp->competencia) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>

            <!-- Selector de Categoría -->
            <div class="col-md-4">
                <label for="filtro_categoria" class="form-label font-weight-bold">Categoría</label>
                <select class="form-select" id="filtro_categoria" name="categoria_id">
                    <option value="">Todas las categorías</option>
                    <?php foreach ($categorias as $c): ?>
                        <option value="<?= $c->id ?>" <?= $filtro_categoria == $c->id ? 'selected' : '' ?>>
                            <?= htmlspecialchars($c->nombre) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div class="col-md-4 d-flex justify-content-start gap-2">
                <button type="submit" class="btn btn-primary d-inline-flex align-items-center gap-1 text-nowrap">
                    <i class="ti ti-zoom"></i> Filtrar
                </button>
                <a href="partidos.php" class="btn btn-secondary d-inline-flex align-items-center gap-1 text-nowrap">
                    <i class="ti ti-refresh"></i> Limpiar
                </a>
                <?php if ($rol === "Admin"): ?>
                    <button type="button" class="btn btn-success d-inline-flex align-items-center gap-1 text-nowrap"
                            data-bs-toggle="modal" data-bs-target="#modalCrear">
                        <i class="ti ti-plus"></i> Nuevo partido
                    </button>
                <?php endif; ?>
            </div>
        </form>
    </div>

    <div class="table-responsive">
        <table class="table table-striped text-center align-middle">
            <thead class="table-dark">
                <tr>
                    <th>Competición</th>
                    <th>Categoría</th>
                    <th>Local</th>
                    <th>Resultado</th>
                    <th>Visitante</th>
                    <th>Fecha</th>
                    <th>Estado</th>
                    <th>Detalle</th>
                    <?php if ($rol === "Admin"): ?><th>Acciones</th><?php endif; ?>
                </tr>
            </thead>
            <tbody>
            <?php if (empty($partidos)): ?>
                <tr><td colspan="<?= $rol === "Admin" ? 9 : 8 ?>" class="text-muted py-4">No hay partidos registrados que coincidan con los filtros.</td></tr>
            <?php else: foreach ($partidos as $p): ?>
                <tr>
                    <td><span class="badge bg-warning text-dark"><?= htmlspecialchars($p->competencia ?? 'Liga') ?></span></td>
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