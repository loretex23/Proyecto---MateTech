<?php
require_once "login/auth.php";
include '../sql/basededatos.php';
include '../sql/registrar_jugador.php';

$rol = $_SESSION["Rol"] ?? "";
$busqueda = trim($_GET['busqueda'] ?? '');
$filtro_categoria = $_GET['categoria_id'] ?? '';
$filtro_club = $_GET['club_id'] ?? '';
$club_id_usuario = $_SESSION['ClubID'] ?? null;

$where = [];
$params = [];

if ($rol === 'Club') {
    $where[] = "j.club_id = :club_usuario";
    $params[':club_usuario'] = $club_id_usuario;
} elseif (!empty($filtro_club)) {
    $where[] = "j.club_id = :club_id";
    $params[':club_id'] = $filtro_club;
}
if (!empty($filtro_categoria)) {
    $where[] = "j.categoria_id = :categoria_id";
    $params[':categoria_id'] = $filtro_categoria;
}
if (!empty($busqueda)) {
    $where[] = "(j.nombre LIKE :busqueda OR j.apellido LIKE :busqueda OR j.ci LIKE :busqueda)";
    $params[':busqueda'] = "%$busqueda%";
}

$sql = "SELECT j.* FROM jugadores j" . ($where ? " WHERE " . implode(" AND ", $where) : "") . " ORDER BY j.id DESC";
$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$jugadores = $stmt->fetchAll(PDO::FETCH_OBJ);

$clubes     = $pdo->query("SELECT id, nombre FROM club ORDER BY nombre ASC")->fetchAll(PDO::FETCH_OBJ);
$categorias = $pdo->query("SELECT id, nombre FROM categorias ORDER BY nombre ASC")->fetchAll(PDO::FETCH_OBJ);

function options_club(array $clubes, string $selected_id = ''): string {
    $html = '<option value="">Seleccionar club...</option>';
    foreach ($clubes as $c) {
        $sel = $selected_id == $c->id ? ' selected' : '';
        $html .= "<option value='{$c->id}'{$sel}>{$c->nombre}</option>";
    }
    return $html;
}
function options_cat(array $categorias, string $selected_id = ''): string {
    $html = '<option value="">Seleccionar categoría...</option>';
    foreach ($categorias as $c) {
        $sel = $selected_id == $c->id ? ' selected' : '';
        $html .= "<option value='{$c->id}'{$sel}>{$c->nombre}</option>";
    }
    return $html;
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
    <title>LeagueFlow - Jugadores</title>
</head>
<body class="fondo-body">

<?php include 'diseños/navbar.php'; ?>

<main>
    <div class="home-hero">
        <div>
            <h1>Jugadores</h1>
            <p>Listado de jugadores de la liga.</p>
        </div>
        <img src="../img/Login-foto0.png" alt="LeagueFlow" class="home-hero-logo">
    </div>

    <div class="container mt-4">
        <h2 class="text-center mb-4 font-weight-bold">Lista de Jugadores</h2>

        <div class="card mb-4 shadow-sm p-3 bg-body-tertiary rounded">
            <form method="GET" action="jugadores.php" class="row g-3 align-items-end">
                <div class="col-md-3">
                    <label for="busqueda" class="form-label font-weight-bold">Buscar Nombre, Apellido o CI</label>
                    <div class="input-group">
                        <span class="input-group-text"><i class="ti ti-search"></i></span>
                        <input type="text" class="form-control" id="busqueda" name="busqueda"
                            placeholder="Ej: Juan, Pérez" value="<?= htmlspecialchars($busqueda) ?>">
                    </div>
                </div>

                <div class="col-md-3" style="width:250px;">
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

                <?php if ($rol !== 'Club'): ?>
                    <div class="col-md-3">
                        <label for="filtro_club" class="form-label font-weight-bold">Club</label>
                        <select class="form-select" id="filtro_club" name="club_id">
                            <option value="">Todos los clubes</option>
                            <?php foreach ($clubes as $c): ?>
                                <option value="<?= $c->id ?>" <?= $filtro_club == $c->id ? 'selected' : '' ?>>
                                    <?= htmlspecialchars($c->nombre) ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                <?php endif; ?>

                <div class="col-md-3 d-flex justify-content-center gap-2">
                    <button type="submit" class="btn btn-primary d-inline-flex align-items-center gap-1 text-nowrap"
                        style="background-color:#226846;border-color:#1a4731;margin-left:15px;">
                        <i class="ti ti-zoom"></i> Filtrar
                    </button>
                    <?php if ($rol === "Admin"): ?>
                        <button type="button" class="btn btn-primary d-inline-flex align-items-center gap-1 text-nowrap"
                            style="background-color:#226846;border-color:#1a4731;" data-bs-toggle="modal"
                            data-bs-target="#modalCrear">
                            <i class="ti ti-plus"></i> Agregar Jugador
                        </button>
                    <?php endif; ?>
                </div>
            </form>
        </div>

        <div class="table-responsive">
            <table class="table table-striped text-center align-middle">
                <thead>
                    <tr>
                        <th>Foto</th>
                        <th>Nombre</th>
                        <th>Apellido</th>
                        <th>Cédula de Identidad</th>
                        <th>Fecha de Nacimiento</th>
                        <th>Carnet</th>
                        <?php if ($rol === "Admin" || $rol === "Club"): ?>
                            <th>Acciones</th>
                        <?php endif; ?>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($jugadores)): ?>
                        <tr>
                            <td colspan="7" class="text-muted py-4">No se encontraron jugadores que coincidan con los criterios de búsqueda.</td>
                        </tr>
                    <?php else: foreach ($jugadores as $j): ?>
                        <tr>
                            <td>
                                <img src="../<?= htmlspecialchars($j->foto_url ?? '') ?>" width="50" height="50"
                                    style="border-radius:50%;object-fit:cover;" alt="Foto">
                            </td>
                            <td><?= htmlspecialchars($j->nombre) ?></td>
                            <td><?= htmlspecialchars($j->apellido) ?></td>
                            <td><?= htmlspecialchars($j->ci) ?></td>
                            <td><?= htmlspecialchars($j->fecha_nacimiento) ?></td>
                            <td>
                                <button class="btn btn-sm btn-info" data-bs-toggle="modal"
                                    data-bs-target="#modalCarnet" data-id="<?= $j->id ?>">
                                    <i class="ti ti-id-badge"></i> Ver Carnet
                                </button>
                            </td>
                            <?php if ($rol === 'Club'): ?>
                                <td>
                                    <button class="btn btn-sm btn-warning" data-bs-toggle="modal"
                                        data-bs-target="#modalEditarFecha" data-id="<?= $j->id ?>"
                                        data-vencimiento="<?= htmlspecialchars($j->carnet_vencimiento ?? '', ENT_QUOTES) ?>">
                                        <i class="ti ti-edit"></i>
                                    </button>
                                </td>
                            <?php elseif ($rol === "Admin"): ?>
                                <td>
                                    <button class="btn btn-sm btn-warning" data-bs-toggle="modal"
                                        data-bs-target="#modalEditar" data-id="<?= $j->id ?>">
                                        <i class="ti ti-edit"></i>
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

<footer>
    &copy; 2026 MateTech. Todos los derechos reservados.
    <img class="foot" src="../img/logo.png" alt="Logo">
</footer>

<!-- Modales -->
<?php 
    include '../modales/modal_editar_fecha.php';
    include '../modales/modal_crear_jugador.php';
    include '../modales/modal_editar_jugador.php';
    include '../modales/modal_carnet_jugador.php';
?>

<!-- Scripts -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script src="../js/jugadores.js"></script>

</body>
</html>