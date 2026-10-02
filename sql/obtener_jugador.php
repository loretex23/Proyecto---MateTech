<?php
require_once "basededatos.php";
header("Content-Type: application/json; charset=UTF-8");

if (empty($_GET["id"])) {
    echo json_encode(["error" => "ID no proporcionado"]);
    exit();
}

$stmt = $pdo->prepare("
    SELECT j.id, j.nombre, j.apellido, j.ci, j.fecha_nacimiento,
           DATE_FORMAT(j.carnet_vencimiento, '%Y-%m-%d') AS carnet_vencimiento,
           j.foto_url, j.club_id, j.categoria_id, j.masa, j.altura,
           c.nombre AS club_nombre,
           cat.nombre AS categoria_nombre
    FROM jugadores j
    LEFT JOIN club c ON j.club_id = c.id
    LEFT JOIN categorias cat ON j.categoria_id = cat.id
    WHERE j.id = ?
");
$stmt->execute([(int)$_GET["id"]]);
$jugador = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$jugador) {
    echo json_encode(["error" => "Jugador no encontrado"]);
    exit();
}

$jugador['fuerza_peso'] = $jugador['masa'] !== null
    ? round((float)$jugador['masa'] * 9.8, 2)
    : null;

echo json_encode($jugador);