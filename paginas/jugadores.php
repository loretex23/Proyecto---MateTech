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

$clubes = $pdo->query("SELECT id, nombre FROM club ORDER BY nombre ASC")->fetchAll(PDO::FETCH_OBJ);
$categorias = $pdo->query("SELECT id, nombre FROM categorias ORDER BY nombre ASC")->fetchAll(PDO::FETCH_OBJ);

function options_club(array $clubes, string $selected_id = ''): string
{
    $html = '<option value="">Seleccionar club...</option>';
    foreach ($clubes as $c) {
        $sel = $selected_id == $c->id ? ' selected' : '';
        $html .= "<option value='{$c->id}'{$sel}>{$c->nombre}</option>";
    }
    return $html;
}
function options_cat(array $categorias, string $selected_id = ''): string
{
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
                <p>Este es el contenido principal de la página de jugadores.</p>
            </div>
            <img src="../img/Login-foto0.png" alt="LeagueFlow" class="home-hero-logo">
        </div>

        <div class="container mt-4">
            <h2 class="text-center mb-4 font-weight-bold">Lista de Jugadores</h2>
+
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
                                <option value="<?= $c->id ?>" <?= $filtro_categoria == $c->id ? ' selected' : '' ?>>
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
                                    <option value="<?= $c->id ?>" <?= $filtro_club == $c->id ? ' selected' : '' ?>>
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

            <!-- Tabla -->
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
                                <th>Acciones</th><?php endif; ?>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($jugadores)): ?>
                            <tr>
                                <td colspan="7" class="text-muted py-4">No se encontraron jugadores que coincidan con los
                                    criterios de búsqueda.</td>
                            </tr>
                        <?php else:
                            foreach ($jugadores as $j): ?>
                                <tr>
                                    <td><img src="../<?= htmlspecialchars($j->foto_url) ?>" width="50" height="50"
                                            style="border-radius:50%;object-fit:cover;" alt="Foto"></td>
                                    <td><?= htmlspecialchars($j->nombre) ?></td>
                                    <td><?= htmlspecialchars($j->apellido) ?></td>
                                    <td><?= htmlspecialchars($j->ci) ?></td>
                                    <td><?= htmlspecialchars($j->fecha_nacimiento) ?></td>
                                    
                                    <td>
                                        <button class="btn btn-sm btn-info" data-bs-toggle="modal"
                                            data-bs-target="#modalCarnet" data-id="<?= $j->id ?>"
                                            data-vencimiento="<?= htmlspecialchars($j->carnet_vencimiento, ENT_QUOTES) ?>">
                                            <i class="ti ti-id-badge"></i> Ver Carnet
                                        </button>
                                    </td>
                                    <?php if ($rol === 'Club'): ?>
                                        <td>
                                            <button class="btn btn-sm btn-warning" data-bs-toggle="modal"
                                                data-bs-target="#modalEditarFecha" data-id="<?= $j->id ?>"
                                                data-vencimiento="<?= htmlspecialchars($j->carnet_vencimiento, ENT_QUOTES) ?>">
                                                <i class="ti ti-edit"></i>
                                            </button>
                                        </td>
                                    <?php elseif ($rol === "Admin"): ?>
                                        <td><button class="btn btn-sm btn-warning" data-bs-toggle="modal"
                                                data-bs-target="#modalEditar" data-id="<?= $j->id ?>"><i
                                                    class="ti ti-edit"></i></button></td>
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

    <div class="modal fade" id="modalEditarFecha" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content modal-login-caja">
                <div class="modal-header">
                    <h5 class="modal-title">Editar Fecha de Vencimiento</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <form action="../sql/editar_fecha_vencimiento.php" method="POST">
                        <input type="hidden" id="editar_id_fecha" name="id">
                        <div class="mb-3">
                            <label for="editar_carnet_vencimiento_fecha" class="form-label">Carnet de
                                Vencimiento</label>
                            <input type="date" class="form-control" id="editar_carnet_vencimiento_fecha"
                                name="carnet_vencimiento" required>
                        </div>
                        <button class="btn btn-primary" style="background-color:#226846;border-color:#1a4731;"
                            name="btneditarfecha" value="ok">Confirmar Registro</button>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <div class="modal fade" id="modalCrear" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content modal-login-caja">
                <div class="modal-header">
                    <h5 class="modal-title">Agregar Jugador</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <form method="POST" enctype="multipart/form-data">
                        <?php foreach ([['nombre', 'Nombre', 'text'], ['apellido', 'Apellido', 'text'], ['ci', 'Cédula de Identidad', 'text'], ['fecha_nacimiento', 'Fecha de Nacimiento', 'date'], ['carnet_vencimiento', 'Carnet de Vencimiento', 'date']] as [$name, $label, $type]): ?>
                            <div class="mb-3">
                                <label for="<?= $name ?>" class="form-label"><?= $label ?></label>
                                <input type="<?= $type ?>" class="form-control" id="<?= $name ?>" name="<?= $name ?>"
                                    required>
                            </div>
                        <?php endforeach; ?>
                        <div class="mb-3">
                            <label for="club_id" class="form-label">Club</label>
                            <select class="form-select" id="club_id" name="club_id"
                                required><?= options_club($clubes) ?></select>
                        </div>
                        <div class="mb-3">
                            <label for="categoria_id" class="form-label">Categoría</label>
                            <select class="form-select" id="categoria_id" name="categoria_id"
                                required><?= options_cat($categorias) ?></select>
                        </div>
                        <div class="mb-3">
                            <label for="foto_url" class="form-label">Foto</label>
                            <input type="file" class="form-control form-control-sm" name="foto_url"
                                accept=".jpg,.jpeg,.png" required>
                        </div>
                        <button class="btn btn-primary" style="background-color:#226846;border-color:#1a4731;"
                            name="btnregistrar" value="ok">Confirmar Registro</button>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <div class="modal fade" id="modalEditar" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content modal-login-caja">
                <div class="modal-header">
                    <h5 class="modal-title">Editar Jugador</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <form action="../sql/editar_jugador.php" method="POST" enctype="multipart/form-data">
                        <input type="hidden" id="editar_id" name="id">
                        <?php foreach ([['editar_nombre', 'nombre', 'Nombre', 'text'], ['editar_apellido', 'apellido', 'Apellido', 'text'], ['editar_ci', 'ci', 'Cédula de Identidad', 'text'], ['editar_fecha_nacimiento', 'fecha_nacimiento', 'Fecha de Nacimiento', 'date'], ['editar_carnet_vencimiento', 'carnet_vencimiento', 'Carnet de Vencimiento', 'date']] as [$id, $name, $label, $type]): ?>
                            <div class="mb-3">
                                <label for="<?= $id ?>" class="form-label"><?= $label ?></label>
                                <input type="<?= $type ?>" class="form-control" id="<?= $id ?>" name="<?= $name ?>"
                                    required>
                            </div>
                        <?php endforeach; ?>
                        <div class="mb-3">
                            <label for="editar_club_id" class="form-label">Club</label>
                            <select class="form-select" id="editar_club_id" name="club_id"
                                required><?= options_club($clubes) ?></select>
                        </div>
                        <div class="mb-3">
                            <label for="editar_categoria_id" class="form-label">Categoría</label>
                            <select class="form-select" id="editar_categoria_id" name="categoria_id"
                                required><?= options_cat($categorias) ?></select>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Foto actual</label><br>
                            <img id="editar_foto_actual" src="" alt="Foto actual" width="100" height="100"
                                style="border-radius:50%;object-fit:cover;margin-bottom:10px;">
                            <label for="editar_foto" class="form-label d-block">Cambiar foto</label>
                            <input type="file" class="form-control" id="editar_foto" name="foto_url"
                                accept=".jpg,.jpeg,.png">
                        </div>
                        <button class="btn btn-primary" style="background-color:#226846;border-color:#1a4731;"
                            name="btneditar" value="ok">Confirmar Registro</button>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <div class="modal fade" id="modalCarnet" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title"><i class="ti ti-id-badge"></i> Carnet del jugador</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body text-center">
                    <img id="carnet_foto" src="" alt="Foto" width="100" height="100"
                        style="border-radius:50%;object-fit:cover;margin-bottom:12px;">
                    <table class="table table-bordered text-start mt-2">
                        <tr>
                            <th>Nombre</th>
                            <td id="carnet_nombre"></td>
                        </tr>
                        <tr>
                            <th>Apellido</th>
                            <td id="carnet_apellido"></td>
                        </tr>
                        <tr>
                            <th>Cédula</th>
                            <td id="carnet_ci"></td>
                        </tr>
                        <tr>
                            <th>Fecha de nacimiento</th>
                            <td id="carnet_nacimiento"></td>
                        </tr>
                        <tr>
                            <th>Vencimiento carnet</th>
                            <td id="carnet_fecha_vencimiento"></td>
                        </tr>
                    </table>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cerrar</button>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        document.getElementById('modalEditar')?.addEventListener('show.bs.modal', function (e) {
            fetch('../sql/obtener_jugador.php?id=' + e.relatedTarget.dataset.id)
                .then(r => r.json())
                .then(j => {
                    if (j.error) return alert(j.error);
                    ['id', 'nombre', 'apellido', 'ci', 'fecha_nacimiento', 'carnet_vencimiento'].forEach(k => {
                        document.getElementById('editar_' + k).value = j[k] ?? '';
                    });
                    document.getElementById('editar_foto_actual').src = '../' + j.foto_url;
                    document.getElementById('editar_club_id').value = j.club_id ?? '';
                    document.getElementById('editar_categoria_id').value = j.categoria_id ?? '';
                })
                .catch(() => alert('No se pudieron cargar los datos del jugador.'));
        });


        document.getElementById('modalEditarFecha')?.addEventListener('show.bs.modal', function (e) {
            const button = e.relatedTarget;
            document.getElementById('editar_id_fecha').value = button.dataset.id;
            document.getElementById('editar_carnet_vencimiento_fecha').value = button.dataset.vencimiento || '';
        });
        document.getElementById('editar_foto')?.addEventListener('change', function (e) {
            const f = e.target.files[0];
            if (!f) return;
            const r = new FileReader();
            r.onload = e => document.getElementById('editar_foto_actual').src = e.target.result;
            r.readAsDataURL(f);
        });


document.getElementById('modalCarnet')?.addEventListener('show.bs.modal', function (e) {
    const b = e.relatedTarget;

    fetch('../sql/obtener_jugador.php?id=' + b.dataset.id)
        .then(r => r.json())
        .then(j => {
            if (j.error) return alert(j.error);

            document.getElementById('carnet_foto').src = '../' + (j.foto_url || '');
            document.getElementById('carnet_nombre').textContent = j.nombre || '';
            document.getElementById('carnet_apellido').textContent = j.apellido || '';
            document.getElementById('carnet_ci').textContent = j.ci || '';
            document.getElementById('carnet_nacimiento').textContent = j.fecha_nacimiento || '';
            document.getElementById('carnet_fecha_vencimiento').textContent = j.carnet_vencimiento || 'Sin fecha asignada';
        })
        .catch(() => alert('No se pudieron cargar los datos del jugador.'));
});
    </script>

</body>

</html>