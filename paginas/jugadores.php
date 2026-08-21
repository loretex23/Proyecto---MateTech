<?php include 'login/auth.php'; ?>
<?php require_once "login/auth.php";?>

<!DOCTYPE html>
<html lang="en">
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
       <?php if ($rol === "Admin") { ?> 
        <button type="button" class="btn btn-primary mb-3" data-bs-toggle="modal" style= "max-width: 425px; background-color: #226846; border-color: #1a4731;" data-bs-target="#modalCrear">
            <i class="ti ti-plus" style="align-middle"></i> Agregar Jugador
        </button>
          <?php } ?>
        <table class="table table-striped text-center align-middle">
            <thead>
                <tr>
                    <th id="foto">Foto</th>
                    <th>Nombre</th>
                    <th>Apellido</th>
                    <th>Cédula de Identidad</th>
                    <th>Fecha de Nacimiento</th>
                    <th>Carnet de Vencimiento</th>
                      <?php if ($rol === "Admin") { ?> 
                    <th> </th>
                    <?php } ?>
                </tr>
            </thead>
            <tbody>

        <?php
        include '../sql/basededatos.php';
        $sql=$pdo->query("SELECT * FROM jugadores");
        while ($datos = $sql->fetch(PDO::FETCH_OBJ)){ ?>
        <tr>
            <td><?php echo $datos->foto_url ?></td>
            <td><?php echo $datos->nombre ?></td>
            <td><?php echo $datos->apellido ?></td>
            <td><?php echo $datos->ci ?></td>
            <td><?php echo $datos->fecha_nacimiento ?></td>
            <td><?php echo $datos->carnet_vencimiento ?></td>
            <?php if ($rol === "Admin") { ?> 
            <td><button type="button" class="btn btn-small btn-warning" data-bs-toggle="modal" data-bs-target="#modalEditar" data-id="<?php echo $datos->id; ?>">
                    <i class="ti ti-edit"></i>
                </button></td>
            <?php } ?>
        </tr>


        <?php } ?>
                </tbody>
        </table>
</div>  
</main>
<div class="modal fade" id="modalCrear" tabindex="-1" aria-labelledby="modalCrearLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content modal-login-caja" id="cont">
            <div class="modal-header">
                <h5 class="modal-title" id="modalCrearLabel">Agregar Jugador</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <form action="../sql/registrar_jugador.php" method="POST" enctype="multipart/form-data">
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
                        <input type="date" class="form-control" id="fecha_nacimiento" name="fecha_nacimiento" required>
                    </div>
                    <div class="mb-3">
                        <label for="carnet_vencimiento" class="form-label">Carnet de Vencimiento</label>
                        <input type="date" class="form-control" id="carnet_vencimiento" name="carnet_vencimiento" required>
                    </div>
                    <div class="mb-3">
                        <label for="foto_url" class="form-label">Foto</label>
                        <input type="file" class="form-control foto form-control-sm " id="" name="" accept=".jpg, .jpeg, .png">
                    </div>
                    <div>
                    <button style="background-color: #226846; border-color: #1a4731;" class="btn btn-primary" type="submit"> Confirmar Registro</button>    
                    </div>
                    </form>
 </div>
</div>
</div>
</div>

<div class="modal fade" id="modalEditar" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-center">
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
                        <input type="date" class="form-control" id="editar_fecha_nacimiento" name="fecha_nacimiento" required>
                    </div>
                    <div class="mb-3">
                        <label for="editar_carnet_vencimiento" class="form-label">Carnet de Vencimiento</label>
                        <input type="date" class="form-control" id="editar_carnet_vencimiento" name="carnet_vencimiento" required>
                    </div>
                    <div class="mb-3">
                        <label for="" class="">Foto</label>
                        <input type="file" class="form-control" id="" name="" accept=".jpg, .jpeg, .png">
                    </div>             
                    <div>
                    <button style="background-color: #226846; border-color: #1a4731;" class="btn btn-primary" type="submit"> Confirmar Registro</button>    
                    </div>
            </form>
            </div>
            </div>
            </div>
            </div>
            </div>
</body>
<footer>
        &copy; 2026 MateTech. Todos los derechos reservados.
        <img class="foot" src="../img/logo.png" alt="Logo">
    </footer>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>

</html>