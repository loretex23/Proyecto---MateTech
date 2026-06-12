<?php
session_start();

if (empty($_SESSION['usuario_id'])) {
    header('Location: /web/paginas/login/login.php');
    exit;
}
