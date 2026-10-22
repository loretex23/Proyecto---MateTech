<?php
require_once "login/auth.php";
include '../sql/basededatos.php';

$rol = $_SESSION["Rol"] ?? "";
$esAdmin = $rol === "Admin";

function campo($k) {
    return trim($_POST[$k] ?? "");
}

function opcional($k) {
    return campo($k) ?: null;
}

if ($esAdmin && $_SERVER["REQUEST_METHOD"] === "POST") {
    $id = (int)($_POST["id"] ?? 0);

    if (isset($_POST["btn_crear"])) {
        $nombre = campo("nombre");
        $usuario = campo("usuario");
        $password = campo("password");
        if ($nombre && $usuario && $password) {
            $pdo->prepare("INSERT INTO club (nombre, Usuario, Contraseña_hash, nombre_dt, kinesiologo, ayudante_tecnico, delegado, rol) VALUES (?, ?, ?, ?, ?, ?, ?, 'Club')")
                ->execute([
                    $nombre,
                    $usuario,
                    password_hash($password, PASSWORD_DEFAULT),
                    opcional("nombre_dt"),
                    opcional("kinesiologo"),
                    opcional("ayudante_tecnico"),
                    opcional("delegado")
                ]);
        }
    } elseif (isset($_POST["btn_editar"])) {
        $nombre = campo("nombre");
        $usuario = campo("usuario");
        if ($id && $nombre && $usuario) {
            $sql = "UPDATE club SET nombre=?, Usuario=?, nombre_dt=?, kinesiologo=?, ayudante_tecnico=?, delegado=?";
            $params = [
                $nombre,
                $usuario,
                opcional("nombre_dt"),
                opcional("kinesiologo"),
                opcional("ayudante_tecnico"),
                opcional("delegado")
            ];
            if ($_POST["password"] !== "") {
                $sql .= ", Contraseña_hash=?";
                $params[] = password_hash($_POST["password"], PASSWORD_DEFAULT);
            }
            $params[] = $id;
            $pdo->prepare($sql . " WHERE id=? AND rol='Club'")->execute($params);
        }
    } elseif (isset($_POST["btn_eliminar"]) && $id) {
        $pdo->prepare("DELETE FROM club WHERE id=? AND rol='Club'")->execute([$id]);
    }

    header("Location: clubes.php");
    exit();
}

$clubes = $pdo->query("SELECT * FROM club WHERE rol='Club' ORDER BY nombre ASC")->fetchAll(PDO::FETCH_OBJ);

$tecnico = [
    "nombre_dt" => "Nombre DT",
    "kinesiologo" => "Kinesiólogo",
    "ayudante_tecnico" => "Ayudante Técnico",
    "delegado" => "Delegado"
];

function input($name, $label, $prefix = "", $type = "text", $required = false, $hint = "") {
    $id = $prefix ? "{$prefix}_{$name}" : "";
    echo '<div class="mb-3"><label class="form-label">' . $label . ($hint ? " <small class=\"text-muted\">$hint</small>" : "") . '</label>'
        . '<input type="' . $type . '" name="' . $name . '"' . ($id ? " id=\"$id\"" : "") . ' class="form-control"' . ($required ? " required" : "") . '></div>';
}

function modal($id, $titulo, $icono, $clase, $accion, $boton, $cuerpo) {
    echo '<div class="modal fade" id="' . $id . '" tabindex="-1" aria-hidden="true"><div class="modal-dialog modal-dialog-centered"><div class="modal-content"><form method="POST">'
        . '<div class="modal-header"><h5 class="modal-title ' . ($clase === "danger" ? "text-danger" : "") . '"><i class="ti ' . $icono . '"></i> ' . $titulo . '</h5><button type="button" class="btn-close" data-bs-dismiss="modal"></button></div>'
        . '<div class="modal-body">';
    $cuerpo();
    echo '</div><div class="modal-footer"><button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>'
        . '<button type="submit" name="' . $accion . '" value="1" class="btn btn-' . $clase . '">' . $boton . '</button></div></form></div></div></div>';
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
        <?php if ($esAdmin): ?>
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
                        <?php if ($esAdmin): ?><th>Acciones</th><?php endif; ?>
                    </tr>
                </thead>
                <tbody>
                    <?php if (!$clubes): ?>
                        <tr><td colspan="<?= $esAdmin ? 8 : 7 ?>" class="text-muted py-4">No hay clubes registrados.</td></tr>
                    <?php else: foreach ($clubes as $c): ?>
                        <tr>
                            <td><?= htmlspecialchars($c->nombre) ?></td>
                            <td><?= htmlspecialchars($c->Usuario) ?></td>
                            <?php foreach ($tecnico as $k => $_): ?>
                                <td><?= htmlspecialchars($c->$k ?: '-') ?></td>
                            <?php endforeach; ?>
                            <td>
                                <a href="jugadores.php?club_id=<?= (int)$c->id ?>" class="btn btn-sm btn-outline-primary">
                                    <i class="ti ti-users"></i>
                                </a>
                            </td>
                            <?php if ($esAdmin): ?>
                                <td class="d-flex gap-1 justify-content-center">
                                    <button class="btn btn-sm btn-warning" data-bs-toggle="modal" data-bs-target="#modalEditar"
                                        data-id="<?= (int)$c->id ?>"
                                        data-nombre="<?= htmlspecialchars($c->nombre) ?>"
                                        data-usuario="<?= htmlspecialchars($c->Usuario) ?>"
                                        <?php foreach ($tecnico as $k => $_): ?>
                                        data-<?= $k ?>="<?= htmlspecialchars($c->$k ?? '') ?>"
                                        <?php endforeach; ?>>
                                        <i class="ti ti-edit"></i>
                                    </button>
                                    <button class="btn btn-sm btn-danger" data-bs-toggle="modal" data-bs-target="#modalEliminar"
                                        data-id="<?= (int)$c->id ?>" data-nombre="<?= htmlspecialchars($c->nombre) ?>">
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

<?php if ($esAdmin):
    modal("modalCrear", "Nuevo club", "ti-plus", "primary", "btn_crear", "Crear club", function () use ($tecnico) {
        input("nombre", "Nombre del club", "", "text", true);
        input("usuario", "Email / Usuario", "", "text", true);
        input("password", "Contraseña", "", "password", true);
        echo "<hr>";
        foreach ($tecnico as $k => $l) input($k, $l);
    });

    modal("modalEditar", "Editar club", "ti-edit", "warning", "btn_editar", "Guardar cambios", function () use ($tecnico) {
        echo '<input type="hidden" name="id" id="editar_id">';
        input("nombre", "Nombre del club", "editar", "text", true);
        input("usuario", "Email / Usuario", "editar", "text", true);
        input("password", "Nueva contraseña", "", "password", false, "(dejar vacío para no cambiar)");
        echo "<hr>";
        foreach ($tecnico as $k => $l) input($k, $l, "editar");
    });

    modal("modalEliminar", "Eliminar club", "ti-trash", "danger", "btn_eliminar", "Eliminar", function () {
        echo '<input type="hidden" name="id" id="eliminar_id">'
            . '<p>¿Estás seguro que querés eliminar a <strong id="eliminar_nombre"></strong>? Esta acción eliminará también todos sus jugadores.</p>';
    });
endif; ?>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script>
const campos = ['id', 'nombre', 'usuario', 'nombre_dt', 'kinesiologo', 'ayudante_tecnico', 'delegado'];

document.getElementById('modalEditar')?.addEventListener('show.bs.modal', e => {
    campos.forEach(c => document.getElementById('editar_' + c).value = e.relatedTarget.dataset[c] ?? '');
    document.querySelector('#modalEditar [name="password"]').value = '';
});

document.getElementById('modalEliminar')?.addEventListener('show.bs.modal', e => {
    document.getElementById('eliminar_id').value = e.relatedTarget.dataset.id;
    document.getElementById('eliminar_nombre').textContent = e.relatedTarget.dataset.nombre;
});
</script>
</body>
</html>