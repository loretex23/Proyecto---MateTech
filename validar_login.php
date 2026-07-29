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
    die("El usuario no existe.");
}

if (password_verify($password, $club["Contraseña_hash"])) {

    $_SESSION["ClubID"] = $club["ClubID"];
    $_SESSION["NombreClub"] = $club["NombreClub"];
    $_SESSION["Usuario"] = $club["Usuario"];

    header("Location: paginas/index.php");
    exit;

} else {

    die("Contraseña incorrecta.");

}