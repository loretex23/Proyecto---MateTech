<?php 
session_start();
session_destroy();
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
    <nav class="navbar navbar-expand-lg" ID="navbar">
    <div class="container-fluid">
        <a class="navbar-brand" href="#">
           <img src="../../img/LogoFlow.png" alt="Logo" class="rounded-pill nav-logo">
            <img src="../../img/LeagueFlow.png" alt="LeagueFlow" class="rounded-pill web-logo">
        </a>
    </div>
</nav>
    <body>

<div class="container mt-4">
    <div class="logout-caja">
        <h2>Sesión cerrada</h2>
        <p class="form-label">Has cerrado sesión correctamente. Esperamos verte pronto de nuevo.</p>
        <br>
        <br>
        <a href="login.php" class="btn-submit btn-primary">Volver a inicio de sesión</a>
        </div>