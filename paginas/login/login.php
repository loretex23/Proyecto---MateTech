   <?php 
   session_start();
   include "../../sql/basededatos.php";  
   ?>

   <!DOCTYPE html>
    <html lang="en">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
        <link href="../../estilos.css" rel="stylesheet">
        <title>Inicio de sesión</title>
    </head>
    <body>
   <nav class="navbar navbar-expand-lg" ID="navbar">
    <div class="container-fluid">
        <a class="navbar-brand" href="#">
            <img src="../../img/LogoFlow.png" alt="Logo" class="rounded-pill nav-logo">
            <img src="../../img/LeagueFlow.png" alt="LeagueFlow" class="rounded-pill web-logo"> 
        </a>
    </div>
</nav>

    <div>
    <form action="/Proyecto---MateTech/validar_login.php" class="login-caja" method="POST">

   

    <div class="mb-3">
        <label>Email</label>

        <input
            type="email"
            class="form-control"
            name="email"
            required>
    </div>

    <div class="mb-3">
        <label>Contraseña</label>

        <input
            type="password"
            class="form-control"
            name="password"
            required>
    </div>

    <button type="submit" class="btn-submit">
        Iniciar sesión
    </button>

    <br>
    <br>

     <?php
    if (isset($_SESSION["error"])) {
        echo '
        <div class="alert alert-danger" role="alert">
            <strong>¡Error!</strong> ' . $_SESSION["error"] . '
        </div>';
        unset($_SESSION["error"]);
    }
    ?>

</form>
    </div> 

<footer>
        &copy; 2026 MateTech. Todos los derechos reservados.
        <img class="foot" src="../img/logo.png" alt="Logo">
    </footer>

    </body>
    </html>
    