<?php require_once "login/auth.php";
include '../sql/basededatos.php';
include '../sql/registrar_jugador.php';

// Capturar parámetros de búsqueda y filtrado
$busqueda = isset($_GET['busqueda']) ? trim($_GET['busqueda']) : '';
$filtro_categoria = isset($_GET['categoria_id']) ? $_GET['categoria_id'] : '';
$filtro_club = isset($_GET['club_id']) ? $_GET['club_id'] : '';

// Obtener el ID del club del usuario en sesión (asumiendo $_SESSION['club_id'])
$club_id_usuario = isset($_SESSION['club_id']) ? $_SESSION['club_id'] : null;
?>

<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/@tabler/icons-webfont@latest/dist/tabler-icons.min.css">
    <link href="../estilos.css" rel="stylesheet" type="text/css">
    <title>MateTech - Jugadores</title>
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

            <div class="card mb-4 shadow-sm p-3 bg-body-tertiary rounded">
    <form method="GET" action="jugadores.php" class="row g-3 align-items-end">
        
        <div class="col-md-3">
            <label for="busqueda" class="form-label font-weight-bold">Buscar Nombre, Apellido o CI</label>
            <div class="input-group">
                <span class="input-group-text"><i class="ti ti-search"></i></span>
                <input type="text" class="form-control" id="busqueda" name="busqueda"
                    placeholder="Ej: Juan, Pérez"
                    value="<?php echo htmlspecialchars($busqueda); ?>">
            </div>
        </div>

        <div class="col-md-3" style="width: 250px;">
            <label for="filtro_categoria" class="form-label font-weight-bold">Categoría</label>
            <select class="form-select" id="filtro_categoria" name="categoria_id">
                <option value="">Todas las categorías</option>
                <?php
                $cat_query = $pdo->query("SELECT id, nombre FROM categorias ORDER BY nombre ASC");
                while ($cat = $cat_query->fetch(PDO::FETCH_OBJ)) {
                    $selected = ($filtro_categoria == $cat->id) ? 'selected' : '';
                    echo "<option value='{$cat->id}' {$selected}>{$cat->nombre}</option>";
                }
                ?>
            </select>
        </div>

        <?php if ($rol !== 'Club') { ?>
            <div class="col-md-3">
                <label for="filtro_club" class="form-label font-weight-bold">Club</label>
                <select class="form-select" id="filtro_club" name="club_id">
                    <option value="">Todos los clubes</option>
                    <?php
                    $club_query = $pdo->query("SELECT id, nombre FROM club ORDER BY nombre ASC");
                    while ($c = $club_query->fetch(PDO::FETCH_OBJ)) {
                        $selected = ($filtro_club == $c->id) ? 'selected' : '';
                        echo "<option value='{$c->id}' {$selected}>{$c->nombre}</option>";
                    }
                    ?>
                </select>
            </div>
        <?php } ?>

        <div class="col-md-3 d-flex justify-content-center gap-2">
            <button type="submit"
                class="btn btn-primary d-inline-flex align-items-center justify-content-center gap-1 text-nowrap"
                style="background-color: #226846; border-color: #1a4731; margin-left: 15px;">
                <i class="ti ti-zoom"></i>
                <span>Filtrar</span>
            </button>

            <?php if ($rol === "Admin") { ?>
                <button type="button"
                    class="btn btn-primary d-inline-flex align-items-center justify-content-center gap-1 text-nowrap"
                    data-bs-toggle="modal" style="background-color: #226846; border-color: #1a4731;"
                    data-bs-target="#modalCrear">
                    <i class="ti ti-plus"></i>
                    <span>Agregar Jugador</span>
                </button>
            <?php } ?>
        </div>
    </form>
</div>



            <div class="table-responsive">
                <table class="table table-striped text-center align-middle">
                    <thead>
                        <tr>
                            <th id="foto">Foto</th>
                            <th>Nombre</th>
                            <th>Apellido</th>
                            <th>Cédula de Identidad</th>
                            <th>Fecha de Nacimiento</th>
                            <th>Carnet de Vencimiento</th>
                            <?php if ($rol === "Admin" || $rol === "Club") { ?>
                                <th>Acciones</th>
                            <?php } ?>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                        $whereClauses = [];
                        $params = [];

                        if ($rol === 'Club') {
                            $whereClauses[] = "j.club_id = :club_usuario";
                            $params[':club_usuario'] = $club_id_usuario;
                        } elseif (!empty($filtro_club)) {
                            $whereClauses[] = "j.club_id = :club_id";
                            $params[':club_id'] = $filtro_club;
                        }

                        if (!empty($filtro_categoria)) {
                            $whereClauses[] = "j.categoria_id = :categoria_id";
                            $params[':categoria_id'] = $filtro_categoria;
                        }

                        if (!empty($busqueda)) {
                            $whereClauses[] = "(j.nombre LIKE :busqueda OR j.apellido LIKE :busqueda OR j.ci LIKE :busqueda)";
                            $params[':busqueda'] = '%' . $busqueda . '%';
                        }

                        $sqlQuery = "SELECT j.* FROM jugadores j";
                        if (count($whereClauses) > 0) {
                            $sqlQuery .= " WHERE " . implode(" AND ", $whereClauses);
                        }
                        $sqlQuery .= " ORDER BY j.id DESC";

                        $stmt = $pdo->prepare($sqlQuery);
                        $stmt->execute($params);

                        if ($stmt->rowCount() > 0) {
                            while ($datos = $stmt->fetch(PDO::FETCH_OBJ)) { ?>
                                <tr>
                                    <td>
                                        <img src="../<?php echo htmlspecialchars($datos->foto_url); ?>" width="50" height="50"
                                            style="border-radius:50%; object-fit:cover;" alt="Foto">
                                    </td>
                                    <td><?php echo htmlspecialchars($datos->nombre); ?></td>
                                    <td><?php echo htmlspecialchars($datos->apellido); ?></td>
                                    <td><?php echo htmlspecialchars($datos->ci); ?></td>
                                    <td><?php echo htmlspecialchars($datos->fecha_nacimiento); ?></td>
                                    <td><?php echo htmlspecialchars($datos->carnet_vencimiento); ?></td>

                                    <?php if ($rol === 'Club') { ?>
                                        <td>
                                            <button type="button" class="btn btn-sm btn-warning" data-bs-toggle="modal"
                                                data-bs-target="#modalEditarFecha" data-id="<?php echo $datos->id; ?>">
                                                <i class="ti ti-edit"></i>
                                            </button>
                                        </td>
                                    <?php } elseif ($rol === "Admin") { ?>
                                        <td>
                                            <button type="button" class="btn btn-sm btn-warning" data-bs-toggle="modal"
                                                data-bs-target="#modalEditar" data-id="<?php echo $datos->id; ?>">
                                                <i class="ti ti-edit"></i>
                                            </button>
                                        </td>
                                    <?php } ?>
                                </tr>
                            <?php }
                        } else { ?>
                            <tr>
                                <td colspan="7" class="text-muted py-4">No se encontraron jugadores que coincidan con los
                                    criterios de búsqueda.</td>
                            </tr>
                        <?php } ?>
                    </tbody>
                </table>
            </div>
        </div>
    </main>

    <div class="modal fade" id="modalEditarFecha" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content modal-login-caja">
                <div class="modal-header">
                    <h5 class="modal-title">Editar Fecha de Vencimiento</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
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
                        <div>
                            <button style="background-color: #226846; border-color: #1a4731;" class="btn btn-primary"
                                name="btneditarfecha" value="ok" type="submit">Confirmar Registro</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <div class="modal fade" id="modalCrear" tabindex="-1" aria-labelledby="modalCrearLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content modal-login-caja" id="cont">
                <div class="modal-header">
                    <h5 class="modal-title" id="modalCrearLabel">Agregar Jugador</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <form method="POST" enctype="multipart/form-data">
                        <div class="mb-3">
                            <label for="nombre" class="form-label">Nombre</label>
                            <input type="text" class="form-control" id="nombre" name="nombre" required>
                        </div>
                        <div class="mb-3">
                            <label for="apellido" class="form-label">Apellido</label>
                            <input type="text" class="form-control" id="apellido" name="apellido" required>
                        </div>
                        <div class="mb-3">
                            <label for="ci" class="form-label">Cédula de Identidad</label>
                            <input type="text" class="form-control" id="ci" name="ci" required>
                        </div>
                        <div class="mb-3">
                            <label for="fecha_nacimiento" class="form-label">Fecha de Nacimiento</label>
                            <input type="date" class="form-control" id="fecha_nacimiento" name="fecha_nacimiento"
                                required>
                        </div>
                        <div class="mb-3">
                            <label for="carnet_vencimiento" class="form-label">Carnet de Vencimiento</label>
                            <input type="date" class="form-control" id="carnet_vencimiento" name="carnet_vencimiento"
                                required>
                        </div>
                        <div class="mb-3">
                            <label for="club_id" class="form-label">Club</label>
                            <select class="form-select" id="club_id" name="club_id" required>
                                <option value="">Seleccionar club...</option>
                                <?php
                                $clubes = $pdo->query("SELECT id, nombre FROM club");
                                while ($club = $clubes->fetch(PDO::FETCH_OBJ)) {
                                    echo "<option value='{$club->id}'>{$club->nombre}</option>";
                                }
                                ?>
                            </select>
                        </div>
                        <div class="mb-3">
                            <label for="categoria_id" class="form-label">Categoría</label>
                            <select class="form-select" id="categoria_id" name="categoria_id" required>
                                <option value="">Seleccionar categoría...</option>
                                <?php
                                $categorias = $pdo->query("SELECT id, nombre FROM categorias");
                                while ($cat = $categorias->fetch(PDO::FETCH_OBJ)) {
                                    echo "<option value='{$cat->id}'>{$cat->nombre}</option>";
                                }
                                ?>
                            </select>
                        </div>
                        <div class="mb-3">
                            <label for="foto_url" class="form-label">Foto</label>
                            <input type="file" class="form-control form-control-sm" name="foto_url"
                                accept=".jpg, .jpeg, .png" required>
                        </div>
                        <div>
                            <button style="background-color: #226846; border-color: #1a4731;" class="btn btn-primary"
                                name="btnregistrar" value="ok">Confirmar Registro</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <div class="modal fade" id="modalEditar" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content modal-login-caja">
                <div class="modal-header">
                    <h5 class="modal-title" id="modalEditarLabel">Editar Jugador</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <form action="../sql/editar_jugador.php" method="POST" enctype="multipart/form-data">
                        <input type="hidden" id="editar_id" name="id">

                        <div class="mb-3">
                            <label for="editar_nombre" class="form-label">Nombre</label>
                            <input type="text" class="form-control" id="editar_nombre" name="nombre" required>
                        </div>
                        <div class="mb-3">
                            <label for="editar_apellido" class="form-label">Apellido</label>
                            <input type="text" class="form-control" id="editar_apellido" name="apellido" required>
                        </div>
                        <div class="mb-3">
                            <label for="editar_ci" class="form-label">Cédula de Identidad</label>
                            <input type="text" class="form-control" id="editar_ci" name="ci" required>
                        </div>
                        <div class="mb-3">
                            <label for="editar_fecha_nacimiento" class="form-label">Fecha de Nacimiento</label>
                            <input type="date" class="form-control" id="editar_fecha_nacimiento" name="fecha_nacimiento"
                                required>
                        </div>
                        <div class="mb-3">
                            <label for="editar_carnet_vencimiento" class="form-label">Carnet de Vencimiento</label>
                            <input type="date" class="form-control" id="editar_carnet_vencimiento"
                                name="carnet_vencimiento" required>
                        </div>
                        <div class="mb-3">
                            <label for="editar_club_id" class="form-label">Club</label>
                            <select class="form-select" id="editar_club_id" name="club_id" required>
                                <option value="">Seleccionar club...</option>
                                <?php
                                $clubes = $pdo->query("SELECT id, nombre FROM club");
                                while ($club = $clubes->fetch(PDO::FETCH_OBJ)) {
                                    echo "<option value='{$club->id}'>{$club->nombre}</option>";
                                }
                                ?>
                            </select>
                        </div>
                        <div class="mb-3">
                            <label for="editar_categoria_id" class="form-label">Categoría</label>
                            <select class="form-select" id="editar_categoria_id" name="categoria_id" required>
                                <option value="">Seleccionar categoría...</option>
                                <?php
                                $categorias = $pdo->query("SELECT id, nombre FROM categorias");
                                while ($cat = $categorias->fetch(PDO::FETCH_OBJ)) {
                                    echo "<option value='{$cat->id}'>{$cat->nombre}</option>";
                                }
                                ?>
                            </select>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Foto actual</label><br>
                            <img id="editar_foto_actual" src="" alt="Foto actual" width="100" height="100"
                                style="border-radius: 50%; object-fit: cover; margin-bottom: 10px;">
                            <label for="editar_foto" class="form-label d-block">Cambiar foto</label>
                            <input type="file" class="form-control" id="editar_foto" name="foto_url"
                                accept=".jpg, .jpeg, .png">
                        </div>
                        <div>
                            <button style="background-color: #226846; border-color: #1a4731;" class="btn btn-primary"
                                name="btneditar" value="ok" type="submit">Confirmar Registro</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>

    <script>
        const modalEditar = document.getElementById('modalEditar');
        if (modalEditar) {
            modalEditar.addEventListener('show.bs.modal', function (event) {
                const button = event.relatedTarget;
                const id = button.getAttribute('data-id');

                fetch('../sql/obtener_jugador.php?id=' + id)
                    .then(response => response.json())
                    .then(jugador => {
                        if (jugador.error) {
                            alert(jugador.error);
                            return;
                        }

                        document.getElementById('editar_id').value = jugador.id;
                        document.getElementById('editar_nombre').value = jugador.nombre;
                        document.getElementById('editar_apellido').value = jugador.apellido;
                        document.getElementById('editar_ci').value = jugador.ci;
                        document.getElementById('editar_fecha_nacimiento').value = jugador.fecha_nacimiento;
                        document.getElementById('editar_carnet_vencimiento').value = jugador.carnet_vencimiento;
                        document.getElementById('editar_foto_actual').src = '../' + jugador.foto_url;

                        if (document.getElementById('editar_club_id')) {
                            document.getElementById('editar_club_id').value = jugador.club_id || '';
                        }
                        if (document.getElementById('editar_categoria_id')) {
                            document.getElementById('editar_categoria_id').value = jugador.categoria_id || '';
                        }
                    })
                    .catch(error => {
                        console.error('Error:', error);
                        alert('No se pudieron cargar los datos del jugador.');
                    });
            });
        }

        const modalEditarFecha = document.getElementById('modalEditarFecha');
        if (modalEditarFecha) {
            modalEditarFecha.addEventListener('show.bs.modal', function (event) {
                const button = event.relatedTarget;
                const id = button.getAttribute('data-id');
                document.getElementById('editar_id_fecha').value = id;
            });
        }

        const editarFoto = document.getElementById('editar_foto');
        if (editarFoto) {
            editarFoto.addEventListener('change', function (event) {
                const archivo = event.target.files[0];
                if (archivo) {
                    const lector = new FileReader();
                    lector.onload = function (e) {
                        document.getElementById('editar_foto_actual').src = e.target.result;
                    };
                    lector.readAsDataURL(archivo);
                }
            });
        }
    </script>

    <footer>
        &copy; 2026 MateTech. Todos los derechos reservados.
        <img class="foot" src="../img/logo.png" alt="Logo">
    </footer>
</body>

</html>