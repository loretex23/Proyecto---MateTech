<?php
require_once "basededatos.php";
header("Content-Type: application/json; charset=UTF-8");

if (empty($_GET["id"])) {
    echo json_encode(["error" => "ID no proporcionado"]);
    exit();
}

$stmt = $pdo->prepare("
    SELECT id, nombre, apellido, ci, fecha_nacimiento,
           DATE_FORMAT(carnet_vencimiento, '%Y-%m-%d') AS carnet_vencimiento,
           foto_url, club_id, categoria_id, masa, altura
    FROM jugadores WHERE id = ?
");
$stmt->execute([(int)$_GET["id"]]);
$jugador = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$jugador) {
    echo json_encode(["error" => "Jugador no encontrado"]);
    exit();
}

// Fuerza peso = masa * g (g = 10 N/kg, redondeado)
$jugador['fuerza_peso'] = $jugador['masa'] !== null
    ? round((float)$jugador['masa'] * 10, 2)
    : null;

echo json_encode($jugador);