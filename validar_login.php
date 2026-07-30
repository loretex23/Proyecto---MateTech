<?php
session_start();

if ($_SERVER["REQUEST_METHOD"] !== "POST") {
    header("Location: paginas/login/login.php");
    exit;
}

require_once "sql/basededatos.php";

$email = trim($_POST["email"]);
$password = $_POST["password"];

$sql = $pdo->prepare("SELECT * FROM club WHERE Usuario = ?");
$sql->execute([$email]);

$club = $sql->fetch(PDO::FETCH_ASSOC);

if (!$club) {
    $_SESSION["error"] = "El usuario no existe.";
    header("Location: paginas/login/login.php");
    exit;
}

if (!password_verify($password, $club["Contraseña_hash"])) {
    $_SESSION["error"] = "Contraseña incorrecta.";
    header("Location: paginas/login/login.php");
    exit;
}

$_SESSION["ClubID"] = $club["ClubID"];
$_SESSION["NombreClub"] = $club["NombreClub"];
$_SESSION["Usuario"] = $club["Usuario"];

$_SESSION["Rol"] = $club["Rol"];

header("Location: paginas/index.php");
exit;