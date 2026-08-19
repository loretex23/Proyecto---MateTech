<?php
session_start();
include "../../sql/basededatos.php";
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/@tabler/icons-webfont@latest/dist/tabler-icons.min.css">
    <link href="../../estilos.css" rel="stylesheet">
    <title>Iniciar sesión — LeagueFlow</title>
</head>
<body class="login-body">

<div class="login-wrapper">
    <form action="../../validar_login.php" style="margin-left: 14%;" class="login-caja" method="POST">

        <div class="brand-row">
            <img src="../../img/logo.png" class="brand-icon-img" alt="MateTech">
            <div>
                <div class="brand-name">MateTech</div>
                <div class="brand-sub">Liga de Fútbol</div>
            </div>
        </div>

        <h2 class="session">Iniciar sesión</h2>

        <div class="welcome-hint">
            <strong>Bienvenido de vuelta</strong>
            <p>Ingresá para gestionar tu liga</p>
        </div>

        <div class="email">
            <label><i class="ti ti-mail"></i> Email</label>
            <input type="email" class="form-control" name="email" placeholder="tucorreo@ejemplo.com" required>
        </div>

        <div class="password">
            <label><i class="ti ti-lock"></i> Contraseña</label>
            <input type="password" class="form-control" name="password" placeholder="••••••••" required>
        </div>

        <button type="submit" class="btn-submit">
            <i class="ti ti-login"></i> Iniciar sesión
        </button>

        <?php
        if (isset($_SESSION["error"])) {
            echo '<div class="alert alert-danger mt-3" style="margin-bottom: 2px;" role="alert">
                <strong>Error:</strong> ' . $_SESSION["error"] . '
            </div>';
            unset($_SESSION["error"]);
        }
        ?>
    </form>

    <img class="login-logo-lateral" src="../../img/Logo-login.png" alt="LeagueFlow">
</div>


</body>
</html>