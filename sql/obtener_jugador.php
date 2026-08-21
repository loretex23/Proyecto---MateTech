<?php

require_once "basededatos.php";

header("Content-Type: application/json; charset=UTF-8");

if (!isset($_GET["id"]) || empty($_GET["id"])) {
    echo json_encode([
        "error" => "ID no proporcionado"
    ]);
    exit();
}

$id = $_GET["id"];

$stmt = $pdo->prepare("
    SELECT
        id,
        nombre,
        apellido,
        ci,
        fecha_nacimiento,
        carnet_vencimiento,
        foto_url,
        club_id,
        categoria_id
    FROM jugadores
    WHERE id = ?
");

$stmt->execute([$id]);

$jugador = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$jugador) {
    echo json_encode([
        "error" => "Jugador no encontrado"
    ]);
    exit();
}

echo json_encode($jugador);