<?php
session_start(); // Inicia o recupera la sesion del usuario.

if (!empty($_SESSION['usuario_id'])) { // Revisa si ya existe un usuario logeado en la sesion.
    if (($_SESSION['usuario_rol'] ?? '') === 'Administrador') { // Revisa si el usuario logeado es administrador.
        header('Location: /web/paginas/admin/indexadmin.php'); // Si es administrador, lo manda a la pagina admin.
    } else { // Si no es administrador, entra como usuario/club.
        header('Location: /web/paginas/clubes/indexclub.php'); // Si es usuario, lo manda a la pagina de clubes.
    } // Cierra la decision de redireccion segun rol.
    exit; // Detiene el codigo para que no siga cargando el login.
} // Cierra el if que verifica si el usuario ya esta logeado.

$error = ''; // Guarda mensajes de error para mostrarlos en el formulario.
$usuario_o_email = ''; // Guarda el nombre o email escrito para no borrarlo si hay un error.

if ($_SERVER['REQUEST_METHOD'] === 'POST') { // Revisa si el formulario fue enviado por POST.
    $usuario_o_email = trim($_POST['usuario'] ?? $_POST['email'] ?? ''); // Toma el nombre o email enviado y le quita espacios al inicio y al final.
    $password = $_POST['pswd'] ?? ''; // Toma la contrasena enviada, o deja vacio si no llego nada.

    if ($usuario_o_email === '' || $password === '') { // Revisa si falta completar nombre/email o contrasena.
        $error = 'Ingresa nombre o email y contrasena.'; // Guarda el mensaje de error si faltan datos.
    } else { // Si nombre/email y contrasena tienen datos, intenta validar el login.
        $conexion = new mysqli('localhost', 'root', '', 'matetech'); // Crea la conexion a la base matetech.

        if ($conexion->connect_error) { // Revisa si hubo un error al conectar con MySQL.
            $error = 'No se pudo conectar con la base de datos.'; // Guarda el mensaje si la conexion fallo.
        } else { // Si la conexion funciono, sigue con la consulta.
            $conexion->set_charset('utf8mb4'); // Usa una codificacion que soporta caracteres especiales.
            $conexion->query("ALTER TABLE usuarios ADD COLUMN IF NOT EXISTS rol ENUM('Administrador', 'Usuario') NOT NULL DEFAULT 'Usuario'"); // Agrega el rol si la base vieja todavia no lo tiene.
            $consulta = $conexion->prepare('SELECT id, nombre, password_hash, rol FROM usuarios WHERE email = ? OR nombre = ? LIMIT 1'); // Prepara una consulta segura para buscar el usuario por email o nombre.
            $consulta->bind_param('ss', $usuario_o_email, $usuario_o_email); // Reemplaza los signos ? de la consulta por el nombre o email.
            $consulta->execute(); // Ejecuta la consulta en la base de datos.
            $resultado = $consulta->get_result(); // Obtiene el resultado de la consulta.
            $usuario = $resultado->fetch_assoc(); // Convierte el resultado en un array asociativo.

            if ($usuario && password_verify($password, $usuario['password_hash'])) { // Revisa si existe el usuario y si la contrasena coincide con el hash guardado.
                $_SESSION['usuario_id'] = $usuario['id']; // Guarda el id del usuario en la sesion.
                $_SESSION['usuario_nombre'] = $usuario['nombre']; // Guarda el nombre del usuario en la sesion.
                $_SESSION['usuario_rol'] = $usuario['rol']; // Guarda si es Administrador o Usuario.
                if ($usuario['rol'] === 'Administrador') { // Revisa si el rol guardado es Administrador.
                    header('Location: /web/paginas/admin/indexadmin.php'); // Si es administrador, entra al panel admin.
                } else { // Si el rol no es Administrador, entra como usuario normal.
                    header('Location: /web/paginas/clubes/indexclub.php'); // Si es usuario, entra a la pagina de clubes.
                } // Cierra la redireccion segun rol.
                exit; // Detiene el codigo para que no siga mostrando el login.
            } // Cierra el if del login correcto.

            $error = 'Nombre, email o contrasena incorrectos.'; // Guarda este error si el usuario no existe o la contrasena esta mal.
            $consulta->close(); // Cierra la consulta preparada.
            $conexion->close(); // Cierra la conexion a la base de datos.
        } // Cierra el else donde la conexion funciono.
    } // Cierra el else donde los campos no estaban vacios.
} // Cierra el if que revisa si el formulario fue enviado.
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="/web/paginas/admin/estilos.css?v=<?php echo filemtime(__DIR__ . '/../admin/estilos.css'); ?>" rel="stylesheet">
    <title>MateTech - Login</title>
</head>
<body>

    <?php include 'navbarlogin.php'; ?>

    <div class="container mt-4">
        <h1>Bienvenidos a MateTech</h1>
        <p>Este es el contenido principal de la pagina de inicio.</p>
    </div>
    <div>
    <?php include __DIR__ . '/../diseño/form.php'; ?>
    </div>
    <div>
    <?php include __DIR__ . '/../diseño/footer.php'; ?>    
</div>
</body>
</html>
