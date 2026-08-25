<?php
require_once "login/auth.php";
include '../sql/basededatos.php';

$rol = $_SESSION["Rol"] ?? "";

if ($rol === "Admin") {

    if (isset($_POST["btn_crear"])) {
        $nombre = trim($_POST["nombre"]);
        $usuario = trim($_POST["usuario"]);
        $pass = password_hash(trim($_POST["password"]), PASSWORD_DEFAULT);
        if ($nombre && $usuario) {
            $pdo->prepare("INSERT INTO club (nombre, Usuario, Contraseña_hash, rol) VALUES (?, ?, ?, 'Club')")
                ->execute([$nombre, $usuario, $pass]);
        }
        header("Location: clubes.php"); exit();
    }

    if (isset($_POST["btn_editar"])) {
        $id = (int)$_POST["id"];
        $nombre = trim($_POST["nombre"]);
        $usuario = trim($_POST["usuario"]);
        $nombre_dt = trim($_POST["nombre_dt"]);
        $kinesiologo = trim($_POST["kinesiologo"]);
        $ayudante_tecnico = trim($_POST["ayudante_tecnico"]);
        $delegado = trim($_POST["delegado"]);

        if (!empty($_POST["password"])) {
            $pdo->prepare("UPDATE club SET nombre=?, Usuario=?, Contraseña_hash=?, nombre_dt=?, kinesiologo=?, ayudante_tecnico=?, delegado=? WHERE id=?")
                ->execute([$nombre, $usuario, password_hash($_POST["password"], PASSWORD_DEFAULT), $nombre_dt, $kinesiologo, $ayudante_tecnico, $delegado, $id]);
        } else {
            $pdo->prepare("UPDATE club SET nombre=?, Usuario=?, nombre_dt=?, kinesiologo=?, ayudante_tecnico=?, delegado=? WHERE id=?")
                ->execute([$nombre, $usuario, $nombre_dt, $kinesiologo, $ayudante_tecnico, $delegado, $id]);
        }
        header("Location: clubes.php"); exit();
    }

    if (isset($_POST["btn_eliminar"])) {
        $pdo->prepare("DELETE FROM club WHERE id=? AND rol='Club'")->execute([(int)$_POST["id"]]);
        header("Location: clubes.php"); exit();
    }
}

$clubes = $pdo->query("SELECT * FROM club WHERE rol='Club' ORDER BY nombre ASC")->fetchAll(PDO::FETCH_OBJ);
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
    <title>LeagueFlow - Clubes</title>
</head>
<body class="fondo-body">

<?php include 'diseños/navbar.php'; ?>

<main>
    <div class="home-hero">
        <div>
            <h1>Clubes</h1>
            <p>Clubes participantes de la liga.</p>
        </div>
        <img src="../img/Login-foto0.png" alt="LeagueFlow" class="home-hero-logo">
    </div>

    <div class="container mt-4">
        <?php if ($rol === "Admin"): ?>
            <button class="btn btn-primary mb-4" data-bs-toggle="modal" data-bs-target="#modalCrear">
                <i class="ti ti-plus"></i> Nuevo club
            </button>
        <?php endif; ?>

        <div class="table-responsive">
            <table class="table table-striped text-center align-middle">
                <thead>
                    <tr>
                        <th>Nombre</th>
                        <th>Usuario</th>
                        <th>DT</th>
                        <th>Kinesiólogo</th>
                        <th>Ayudante Técnico</th>
                        <th>Delegado</th>
                        <th>Jugadores</th>
                        <?php if ($rol === "Admin"): ?><th>Acciones</th><?php endif; ?>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($clubes)): ?>
                        <tr><td colspan="8" class="text-muted py-4">No hay clubes registrados.</td></tr>
                    <?php else: foreach ($clubes as $c): ?>
                        <tr>
                            <td><?= htmlspecialchars($c->nombre) ?></td>
                            <td><?= htmlspecialchars($c->Usuario) ?></td>
                            <td><?= htmlspecialchars($c->nombre_dt ?? '-') ?></td>
                            <td><?= htmlspecialchars($c->kinesiologo ?? '-') ?></td>
                            <td><?= htmlspecialchars($c->ayudante_tecnico ?? '-') ?></td>
                            <td><?= htmlspecialchars($c->delegado ?? '-') ?></td>
                            <td>
                                <a href="jugadores.php?club_id=<?= $c->id ?>" class="btn btn-sm btn-outline-primary">
                                    <i class="ti ti-users"></i>
                                </a>
                            </td>
                            <?php if ($rol === "Admin"): ?>
                                <td class="d-flex gap-1 justify-content-center">
                                    <button class="btn btn-sm btn-warning" data-bs-toggle="modal" data-bs-target="#modalEditar"
                                        data-id="<?= $c->id ?>"
                                        data-nombre="<?= htmlspecialchars($c->nombre) ?>"
                                        data-usuario="<?= htmlspecialchars($c->Usuario) ?>"
                                        data-dt="<?= htmlspecialchars($c->nombre_dt ?? '') ?>"
                                        data-kine="<?= htmlspecialchars($c->kinesiologo ?? '') ?>"
                                        data-ayudante="<?= htmlspecialchars($c->ayudante_tecnico ?? '') ?>"
                                        data-delegado="<?= htmlspecialchars($c->delegado ?? '') ?>">
                                        <i class="ti ti-edit"></i>
                                    </button>
                                    <button class="btn btn-sm btn-danger" data-bs-toggle="modal" data-bs-target="#modalEliminar"
                                        data-id="<?= $c->id ?>" data-nombre="<?= htmlspecialchars($c->nombre) ?>">
                                        <i class="ti ti-trash"></i>
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

<?php if ($rol === "Admin"): ?>

<div class="modal fade" id="modalCrear" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <form method="POST">
                <div class="modal-header">
                    <h5 class="modal-title"><i class="ti ti-plus"></i> Nuevo club</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label">Nombre del club</label>
                        <input type="text" name="nombre" class="form-control" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Email / Usuario</label>
                        <input type="text" name="usuario" class="form-control" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Contraseña</label>
                        <input type="password" name="password" class="form-control" required>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                    <button type="submit" name="btn_crear" value="1" class="btn btn-primary">Crear club</button>
                </div>
            </form>
        </div>
    </div>
</div>

<div class="modal fade" id="modalEditar" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <form method="POST">
                <input type="hidden" name="id" id="editar_id">
                <div class="modal-header">
                    <h5 class="modal-title"><i class="ti ti-edit"></i> Editar club</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label">Nombre del club</label>
                        <input type="text" name="nombre" id="editar_nombre" class="form-control" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Email / Usuario</label>
                        <input type="text" name="usuario" id="editar_usuario" class="form-control" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Nueva contraseña <small class="text-muted">(dejar vacío para no cambiar)</small></label>
                        <input type="password" name="password" class="form-control">
                    </div>
                    <hr>
                    <div class="mb-3">
                        <label class="form-label">Nombre DT</label>
                        <input type="text" name="nombre_dt" id="editar_dt" class="form-control">
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Kinesiólogo</label>
                        <input type="text" name="kinesiologo" id="editar_kine" class="form-control">
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Ayudante Técnico</label>
                        <input type="text" name="ayudante_tecnico" id="editar_ayudante" class="form-control">
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Delegado</label>
                        <input type="text" name="delegado" id="editar_delegado" class="form-control">
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                    <button type="submit" name="btn_editar" value="1" class="btn btn-warning">Guardar cambios</button>
                </div>
            </form>
        </div>
    </div>
</div>

<div class="modal fade" id="modalEliminar" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <form method="POST">
                <input type="hidden" name="id" id="eliminar_id">
                <div class="modal-header">
                    <h5 class="modal-title text-danger"><i class="ti ti-trash"></i> Eliminar club</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <p>¿Estás seguro que querés eliminar a <strong id="eliminar_nombre"></strong>? Esta acción eliminará también todos sus jugadores.</p>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                    <button type="submit" name="btn_eliminar" value="1" class="btn btn-danger">Eliminar</button>
                </div>
            </form>
        </div>
    </div>
</div>

<?php endif; ?>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script>
document.getElementById('modalEditar')?.addEventListener('show.bs.modal', function(e) {
    const b = e.relatedTarget;
    document.getElementById('editar_id').value       = b.dataset.id;
    document.getElementById('editar_nombre').value   = b.dataset.nombre;
    document.getElementById('editar_usuario').value  = b.dataset.usuario;
    document.getElementById('editar_dt').value       = b.dataset.dt;
    document.getElementById('editar_kine').value     = b.dataset.kine;
    document.getElementById('editar_ayudante').value = b.dataset.ayudante;
    document.getElementById('editar_delegado').value = b.dataset.delegado;
});

document.getElementById('modalEliminar')?.addEventListener('show.bs.modal', function(e) {
    const b = e.relatedTarget;
    document.getElementById('eliminar_id').value   = b.dataset.id;
    document.getElementById('eliminar_nombre').textContent = b.dataset.nombre;
});
</script>
</body>
</html>