<?php
require_once "../paginas/login/auth.php";
include "basededatos.php";
header("Content-Type: application/json");

$id = isset($_GET["id"]) ? (int)$_GET["id"] : 0;
if (!$id) { echo json_encode(["error" => "ID inválido"]); exit(); }

$stmt = $pdo->prepare("
    SELECT p.goles_local, p.goles_visitante, p.estado,
           cl.nombre AS local, cv.nombre AS visitante,
           cl.id AS local_id, cv.id AS visitante_id
    FROM partidos p
    JOIN club cl ON cl.id = p.club_local_id
    JOIN club cv ON cv.id = p.club_visitante_id
    WHERE p.id = ?
");
$stmt->execute([$id]);
$partido = $stmt->fetch(PDO::FETCH_ASSOC);

$goles = $pdo->prepare("
    SELECT g.jugador_id, g.minuto, g.tipo, j.nombre, j.apellido, j.club_id, c.nombre AS club
    FROM goles g
    JOIN jugadores j ON j.id = g.jugador_id
    JOIN club c ON c.id = j.club_id
    WHERE g.partido_id = ?
    ORDER BY g.minuto ASC
");
$goles->execute([$id]);

$sanciones = $pdo->prepare("
    SELECT s.tipo_tarjeta, s.minuto, j.nombre, j.apellido, c.nombre AS club
    FROM sanciones s
    JOIN jugadores j ON j.id = s.jugador_id
    JOIN club c ON c.id = j.club_id
    WHERE s.partido_id = ?
    ORDER BY s.minuto ASC
");
$sanciones->execute([$id]);

$lesiones = $pdo->prepare("
    SELECT l.descripcion, l.minuto, j.nombre, j.apellido, c.nombre AS club
    FROM lesiones l
    JOIN jugadores j ON j.id = l.jugador_id
    JOIN club c ON c.id = j.club_id
    WHERE l.partido_id = ?
    ORDER BY l.minuto ASC
");
$lesiones->execute([$id]);

echo json_encode([
    "partido"   => $partido,
    "goles"     => $goles->fetchAll(PDO::FETCH_ASSOC),
    "sanciones" => $sanciones->fetchAll(PDO::FETCH_ASSOC),
    "lesiones"  => $lesiones->fetchAll(PDO::FETCH_ASSOC),
]);