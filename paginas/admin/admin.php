<?php
include 'proteger.php';

$conexion = new mysqli('localhost', 'root', '', 'matetech');
$mensaje = '';
$error = '';

function h($texto) {
    return htmlspecialchars($texto ?? '', ENT_QUOTES, 'UTF-8');
}

function dato($nombre) {
    return trim($_POST[$nombre] ?? '');
}

if ($conexion->connect_error) {
    $error = 'No se pudo conectar con la base de datos.';
} else {
    $conexion->set_charset('utf8mb4');
    $conexion->query("ALTER TABLE usuarios ADD COLUMN IF NOT EXISTS rol ENUM('Administrador', 'Usuario') NOT NULL DEFAULT 'Usuario'");

    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $accion = $_POST['accion'] ?? '';

        if ($accion === 'crear_usuario') {
            $nombre = dato('nombre');
            $email = dato('email');
            $password = $_POST['password'] ?? '';
            $rol = dato('rol');

            if ($rol !== 'Administrador' && $rol !== 'Usuario') {
                $rol = 'Usuario';
            }

            if ($nombre === '' || $email === '' || $password === '') {
                $error = 'Completa nombre, email y contrasena del usuario.';
            } else {
                $hash = password_hash($password, PASSWORD_DEFAULT);
                $consulta = $conexion->prepare('INSERT INTO usuarios (nombre, email, password_hash, rol) VALUES (?, ?, ?, ?)');
                $consulta->bind_param('ssss', $nombre, $email, $hash, $rol);
                $mensaje = $consulta->execute() ? 'Usuario creado.' : 'No se pudo crear el usuario.';
                $consulta->close();
            }
        }

        if ($accion === 'editar_usuario') {
            $id = (int) ($_POST['id'] ?? 0);
            $nombre = dato('nombre');
            $email = dato('email');
            $password = $_POST['password'] ?? '';
            $rol = dato('rol');

            if ($rol !== 'Administrador' && $rol !== 'Usuario') {
                $rol = 'Usuario';
            }

            if ($nombre === '' || $email === '') {
                $error = 'Completa nombre y email del usuario.';
            } elseif ($password === '') {
                $consulta = $conexion->prepare('UPDATE usuarios SET nombre = ?, email = ?, rol = ? WHERE id = ?');
                $consulta->bind_param('sssi', $nombre, $email, $rol, $id);
                $mensaje = $consulta->execute() ? 'Usuario actualizado.' : 'No se pudo actualizar el usuario.';
                $consulta->close();
            } else {
                $hash = password_hash($password, PASSWORD_DEFAULT);
                $consulta = $conexion->prepare('UPDATE usuarios SET nombre = ?, email = ?, password_hash = ?, rol = ? WHERE id = ?');
                $consulta->bind_param('ssssi', $nombre, $email, $hash, $rol, $id);
                $mensaje = $consulta->execute() ? 'Usuario actualizado.' : 'No se pudo actualizar el usuario.';
                $consulta->close();
            }
        }

        if ($accion === 'borrar_usuario') {
            $id = (int) ($_POST['id'] ?? 0);

            if ($id === (int) $_SESSION['usuario_id']) {
                $error = 'No puedes borrar tu propio usuario mientras estas logeado.';
            } else {
                $consulta = $conexion->prepare('DELETE FROM usuarios WHERE id = ?');
                $consulta->bind_param('i', $id);
                $mensaje = $consulta->execute() ? 'Usuario borrado.' : 'No se pudo borrar el usuario.';
                $consulta->close();
            }
        }

        if ($accion === 'crear_club') {
            $nombre = dato('nombre');
            $email = dato('email');
            $password = $_POST['password'] ?? '';

            if ($nombre === '' || $email === '') {
                $error = 'Completa nombre y email del club.';
            } else {
                $hash = $password === '' ? '' : password_hash($password, PASSWORD_DEFAULT);
                $consulta = $conexion->prepare('INSERT INTO clubes (nombre, email, password_hash) VALUES (?, ?, ?)');
                $consulta->bind_param('sss', $nombre, $email, $hash);
                $mensaje = $consulta->execute() ? 'Club creado.' : 'No se pudo crear el club.';
                $consulta->close();
            }
        }

        if ($accion === 'editar_club') {
            $id = (int) ($_POST['id'] ?? 0);
            $nombre = dato('nombre');
            $email = dato('email');
            $password = $_POST['password'] ?? '';

            if ($nombre === '' || $email === '') {
                $error = 'Completa nombre y email del club.';
            } elseif ($password === '') {
                $consulta = $conexion->prepare('UPDATE clubes SET nombre = ?, email = ? WHERE id = ?');
                $consulta->bind_param('ssi', $nombre, $email, $id);
                $mensaje = $consulta->execute() ? 'Club actualizado.' : 'No se pudo actualizar el club.';
                $consulta->close();
            } else {
                $hash = password_hash($password, PASSWORD_DEFAULT);
                $consulta = $conexion->prepare('UPDATE clubes SET nombre = ?, email = ?, password_hash = ? WHERE id = ?');
                $consulta->bind_param('sssi', $nombre, $email, $hash, $id);
                $mensaje = $consulta->execute() ? 'Club actualizado.' : 'No se pudo actualizar el club.';
                $consulta->close();
            }
        }

        if ($accion === 'borrar_club') {
            $id = (int) ($_POST['id'] ?? 0);
            $consulta = $conexion->prepare('DELETE FROM clubes WHERE id = ?');
            $consulta->bind_param('i', $id);
            $mensaje = $consulta->execute() ? 'Club borrado.' : 'No se pudo borrar el club.';
            $consulta->close();
        }

        if ($accion === 'crear_jugador') {
            $nombre = dato('nombre');
            $apellido = dato('apellido');
            $posicion = dato('posicion');
            $fuerza = (int) ($_POST['fuerza'] ?? 0);
            $club_id = $_POST['club_id'] === '' ? null : (int) $_POST['club_id'];
            $fecha_nacimiento = dato('fecha_nacimiento');
            $carnet = dato('carnet');

            if ($carnet === '') {
                $carnet = 'CAR-' . date('YmdHis') . rand(100, 999);
            }

            if ($nombre === '' || $apellido === '' || $posicion === '' || $fecha_nacimiento === '') {
                $error = 'Completa nombre, apellido, posicion y fecha de nacimiento del jugador.';
            } else {
                $consulta = $conexion->prepare('INSERT INTO jugadores (nombre, apellido, posicion, fuerza, club_id, fecha_nacimiento, carnet) VALUES (?, ?, ?, ?, ?, ?, ?)');
                $consulta->bind_param('sssiiss', $nombre, $apellido, $posicion, $fuerza, $club_id, $fecha_nacimiento, $carnet);
                $mensaje = $consulta->execute() ? 'Jugador creado.' : 'No se pudo crear el jugador.';
                $consulta->close();
            }
        }

        if ($accion === 'borrar_jugador') {
            $id = (int) ($_POST['id'] ?? 0);
            $consulta = $conexion->prepare('DELETE FROM jugadores WHERE id = ?');
            $consulta->bind_param('i', $id);
            $mensaje = $consulta->execute() ? 'Jugador borrado.' : 'No se pudo borrar el jugador.';
            $consulta->close();
        }

        if ($accion === 'editar_jugador') {
            $id = (int) ($_POST['id'] ?? 0);
            $nombre = dato('nombre');
            $apellido = dato('apellido');
            $posicion = dato('posicion');
            $fuerza = (int) ($_POST['fuerza'] ?? 0);
            $club_id = $_POST['club_id'] === '' ? null : (int) $_POST['club_id'];
            $fecha_nacimiento = dato('fecha_nacimiento');
            $carnet = dato('carnet');

            if ($nombre === '' || $apellido === '' || $posicion === '' || $fecha_nacimiento === '' || $carnet === '') {
                $error = 'Completa todos los datos del jugador.';
            } else {
                $consulta = $conexion->prepare('UPDATE jugadores SET nombre = ?, apellido = ?, posicion = ?, fuerza = ?, club_id = ?, fecha_nacimiento = ?, carnet = ? WHERE id = ?');
                $consulta->bind_param('sssiissi', $nombre, $apellido, $posicion, $fuerza, $club_id, $fecha_nacimiento, $carnet, $id);
                $mensaje = $consulta->execute() ? 'Jugador actualizado.' : 'No se pudo actualizar el jugador.';
                $consulta->close();
            }
        }
    }

    $usuarios = $conexion->query('SELECT id, nombre, email, rol, creado_en FROM usuarios ORDER BY id DESC');
    $clubes = $conexion->query('SELECT id, nombre, email, creado_en FROM clubes ORDER BY nombre');
    $jugadores = $conexion->query('SELECT jugadores.id, jugadores.nombre, jugadores.apellido, jugadores.posicion, jugadores.fuerza, jugadores.club_id, jugadores.fecha_nacimiento, jugadores.carnet, clubes.nombre AS club FROM jugadores LEFT JOIN clubes ON jugadores.club_id = clubes.id ORDER BY jugadores.id DESC');
    $clubes_opciones = $conexion->query('SELECT id, nombre FROM clubes ORDER BY nombre');
    $clubes_lista = [];

    while ($club = $clubes_opciones->fetch_assoc()) {
        $clubes_lista[] = $club;
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="estilos.css?v=<?php echo filemtime('estilos.css'); ?>" rel="stylesheet">
    <title>MateTech - Admin</title>
</head>
<body>
    <?php include __DIR__ . '/navbaradmin.php'; ?>

    <main class="container admin-panel">
        <h1>Menu admin</h1>

        <?php if ($mensaje !== '') : ?>
            <div class="alert alert-success"><?php echo h($mensaje); ?></div>
        <?php endif; ?>

        <?php if ($error !== '') : ?>
            <div class="alert alert-danger"><?php echo h($error); ?></div>
        <?php endif; ?>

        <section class="admin-section">
            <h2>Usuarios para login</h2>
            <form method="post" class="admin-form">
                <input type="hidden" name="accion" value="crear_usuario">
                <input class="form-control" type="text" name="nombre" placeholder="Nombre">
                <input class="form-control" type="email" name="email" placeholder="Email">
                <select class="form-control" name="rol">
                    <option value="Usuario">Usuario</option>
                    <option value="Administrador">Administrador</option>
                </select>
                <div class="password-field">
                    <input class="form-control" type="password" name="password" placeholder="Contrasena">
                    <button type="button" class="password-toggle">Ver</button>
                </div>
                <button class="btn" type="submit">Crear usuario</button>
            </form>

            <div class="table-responsive">
                <table class="table table-striped align-middle">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Nombre</th>
                            <th>Email</th>
                            <th>Rol</th>
                            <th>Nueva contrasena</th>
                            <th>Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php while ($usuario = $usuarios->fetch_assoc()) : ?>
                            <?php $form_usuario = 'form-usuario-' . $usuario['id']; ?>
                            <tr>
                                <td><?php echo h($usuario['id']); ?></td>
                                <td><input form="<?php echo h($form_usuario); ?>" class="form-control" name="nombre" value="<?php echo h($usuario['nombre']); ?>"></td>
                                <td><input form="<?php echo h($form_usuario); ?>" class="form-control" type="email" name="email" value="<?php echo h($usuario['email']); ?>"></td>
                                <td>
                                    <select form="<?php echo h($form_usuario); ?>" class="form-control" name="rol">
                                        <option value="Usuario" <?php echo $usuario['rol'] === 'Usuario' ? 'selected' : ''; ?>>Usuario</option>
                                        <option value="Administrador" <?php echo $usuario['rol'] === 'Administrador' ? 'selected' : ''; ?>>Administrador</option>
                                    </select>
                                </td>
                                <td>
                                    <div class="password-field">
                                        <input form="<?php echo h($form_usuario); ?>" class="form-control" type="password" name="password" placeholder="Dejar vacio">
                                        <button type="button" class="password-toggle">Ver</button>
                                    </div>
                                </td>
                                <td class="admin-actions">
                                    <form id="<?php echo h($form_usuario); ?>" method="post">
                                        <input type="hidden" name="id" value="<?php echo h($usuario['id']); ?>">
                                    </form>
                                    <button form="<?php echo h($form_usuario); ?>" class="btn btn-sm" name="accion" value="editar_usuario" type="submit">Guardar</button>
                                    <button form="<?php echo h($form_usuario); ?>" class="btn btn-sm btn-danger" name="accion" value="borrar_usuario" type="submit">Borrar</button>
                                </td>
                            </tr>
                        <?php endwhile; ?>
                    </tbody>
                </table>
            </div>
        </section>

        <section class="admin-section">
            <h2>Clubes</h2>
            <form method="post" class="admin-form">
                <input type="hidden" name="accion" value="crear_club">
                <input class="form-control" type="text" name="nombre" placeholder="Nombre del club">
                <input class="form-control" type="email" name="email" placeholder="Email del club">
                <div class="password-field">
                    <input class="form-control" type="password" name="password" placeholder="Contrasena opcional">
                    <button type="button" class="password-toggle">Ver</button>
                </div>
                <button class="btn" type="submit">Crear club</button>
            </form>

            <div class="table-responsive">
                <table class="table table-striped align-middle">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Nombre</th>
                            <th>Email</th>
                            <th>Nueva contrasena</th>
                            <th>Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php while ($club = $clubes->fetch_assoc()) : ?>
                            <?php $form_club = 'form-club-' . $club['id']; ?>
                            <tr>
                                <td><?php echo h($club['id']); ?></td>
                                <td><input form="<?php echo h($form_club); ?>" class="form-control" name="nombre" value="<?php echo h($club['nombre']); ?>"></td>
                                <td><input form="<?php echo h($form_club); ?>" class="form-control" type="email" name="email" value="<?php echo h($club['email']); ?>"></td>
                                <td>
                                    <div class="password-field">
                                        <input form="<?php echo h($form_club); ?>" class="form-control" type="password" name="password" placeholder="Dejar vacio">
                                        <button type="button" class="password-toggle">Ver</button>
                                    </div>
                                </td>
                                <td class="admin-actions">
                                    <form id="<?php echo h($form_club); ?>" method="post">
                                        <input type="hidden" name="id" value="<?php echo h($club['id']); ?>">
                                    </form>
                                    <button form="<?php echo h($form_club); ?>" class="btn btn-sm" name="accion" value="editar_club" type="submit">Guardar</button>
                                    <button form="<?php echo h($form_club); ?>" class="btn btn-sm btn-danger" name="accion" value="borrar_club" type="submit">Borrar</button>
                                </td>
                            </tr>
                        <?php endwhile; ?>
                    </tbody>
                </table>
            </div>
        </section>

        <section class="admin-section">
            <h2>Jugadores</h2>
            <form method="post" class="admin-form admin-form-wide">
                <input type="hidden" name="accion" value="crear_jugador">
                <input class="form-control" type="text" name="nombre" placeholder="Nombre">
                <input class="form-control" type="text" name="apellido" placeholder="Apellido">
                <input class="form-control" type="text" name="posicion" placeholder="Posicion">
                <input class="form-control" type="number" name="fuerza" placeholder="Fuerza" min="0" max="100">
                <select class="form-control" name="club_id">
                    <option value="">Sin club</option>
                    <?php foreach ($clubes_lista as $club) : ?>
                        <option value="<?php echo h($club['id']); ?>"><?php echo h($club['nombre']); ?></option>
                    <?php endforeach; ?>
                </select>
                <input class="form-control" type="date" name="fecha_nacimiento">
                <input class="form-control" type="text" name="carnet" placeholder="Carnet opcional">
                <button class="btn" type="submit">Crear jugador</button>
            </form>

            <div class="table-responsive">
                <table class="table table-striped align-middle">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Nombre</th>
                            <th>Apellido</th>
                            <th>Posicion</th>
                            <th>Fuerza</th>
                            <th>Club</th>
                            <th>Nacimiento</th>
                            <th>Carnet</th>
                            <th>Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php while ($jugador = $jugadores->fetch_assoc()) : ?>
                            <?php $form_jugador = 'form-jugador-' . $jugador['id']; ?>
                            <tr>
                                <td><?php echo h($jugador['id']); ?></td>
                                <td><input form="<?php echo h($form_jugador); ?>" class="form-control" name="nombre" value="<?php echo h($jugador['nombre']); ?>"></td>
                                <td><input form="<?php echo h($form_jugador); ?>" class="form-control" name="apellido" value="<?php echo h($jugador['apellido']); ?>"></td>
                                <td><input form="<?php echo h($form_jugador); ?>" class="form-control" name="posicion" value="<?php echo h($jugador['posicion']); ?>"></td>
                                <td><input form="<?php echo h($form_jugador); ?>" class="form-control" type="number" name="fuerza" min="0" max="100" value="<?php echo h($jugador['fuerza']); ?>"></td>
                                <td>
                                    <select form="<?php echo h($form_jugador); ?>" class="form-control" name="club_id">
                                        <option value="">Sin club</option>
                                        <?php foreach ($clubes_lista as $club) : ?>
                                            <option value="<?php echo h($club['id']); ?>" <?php echo (int) $jugador['club_id'] === (int) $club['id'] ? 'selected' : ''; ?>>
                                                <?php echo h($club['nombre']); ?>
                                            </option>
                                        <?php endforeach; ?>
                                    </select>
                                </td>
                                <td><input form="<?php echo h($form_jugador); ?>" class="form-control" type="date" name="fecha_nacimiento" value="<?php echo h($jugador['fecha_nacimiento']); ?>"></td>
                                <td><input form="<?php echo h($form_jugador); ?>" class="form-control" name="carnet" value="<?php echo h($jugador['carnet']); ?>"></td>
                                <td class="admin-actions">
                                    <form id="<?php echo h($form_jugador); ?>" method="post">
                                        <input type="hidden" name="id" value="<?php echo h($jugador['id']); ?>">
                                    </form>
                                    <button form="<?php echo h($form_jugador); ?>" class="btn btn-sm" name="accion" value="editar_jugador" type="submit">Guardar</button>
                                    <button form="<?php echo h($form_jugador); ?>" class="btn btn-sm btn-danger" name="accion" value="borrar_jugador" type="submit">Borrar</button>
                                </td>
                            </tr>
                        <?php endwhile; ?>
                    </tbody>
                </table>
            </div>
        </section>
    </main>
    <script>
        document.querySelectorAll('.password-toggle').forEach(function (boton) {
            boton.addEventListener('click', function () {
                var campo = boton.parentElement.querySelector('input');

                if (campo.type === 'password') {
                    campo.type = 'text';
                    boton.textContent = 'Ocultar';
                } else {
                    campo.type = 'password';
                    boton.textContent = 'Ver';
                }
            });
        });
    </script>
</body>
</html>
