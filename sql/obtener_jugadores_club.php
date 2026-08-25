<?php
require_once "../paginas/login/auth.php";
include "basededatos.php";

header("Content-Type: application/json");

$club_id     = isset($_GET["club_id"])     ? (int)$_GET["club_id"]     : 0;
$categoria_id = isset($_GET["categoria_id"]) ? (int)$_GET["categoria_id"] : 0;

if (!$club_id || !$categoria_id) {
    echo json_encode([]);
    exit();
}

$stmt = $pdo->prepare("SELECT id, nombre, apellido, ci FROM jugadores WHERE club_id = ? AND categoria_id = ? ORDER BY apellido ASC");
$stmt->execute([$club_id, $categoria_id]);
echo json_encode($stmt->fetchAll(PDO::FETCH_ASSOC));