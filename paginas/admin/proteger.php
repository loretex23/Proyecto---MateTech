<?php
session_start();

if (empty($_SESSION['usuario_id'])) {
    header('Location: /web/paginas/login/login.php');
    exit;
}

if (($_SESSION['usuario_rol'] ?? '') !== 'Administrador') {
    header('Location: /web/paginas/clubes/indexclub.php');
    exit;
}
