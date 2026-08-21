<?php
session_start();
session_destroy();
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="icon" href="../../img/logo.png" type="image/png">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/@tabler/icons-webfont@latest/dist/tabler-icons.min.css">
    <link href="../../estilos.css" rel="stylesheet">
    <title>Sesión cerrada — LeagueFlow</title>
</head>
<body class="login-body">


<div class="login-wrapper" style="margin:auto; justify-content: center;">
    <div class="login-caja" style="width: 420px; text-align: center;">
        <div class="brand-row" style="justify-content: center;">
            <img src="../../img/logo.png" class="brand-icon-img" alt="MateTech">
            <div>
                <div class="brand-name">MateTech</div>
                <div class="brand-sub">Liga de Fútbol</div>
            </div>
        </div>

        <i class="ti ti-circle-check" style="font-size: 48px; color: #4ade80; margin-bottom: 12px; display: block;"></i>

        <h2 class="session" style="font-size: 1.4rem;">Sesión cerrada</h2>

        <p style="color: rgba(255,255,255,0.65); font-size: 14px; margin: 12px 0 28px;">
            Has cerrado sesión correctamente. Esperamos verte pronto de nuevo.
        </p>

        <a href="login.php" class="btn-submit" style="display: block; text-decoration: none; text-align: center;">
            <i class="ti ti-login"></i> Volver a iniciar sesión
        </a>
    </div>
</div>



<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.7/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>