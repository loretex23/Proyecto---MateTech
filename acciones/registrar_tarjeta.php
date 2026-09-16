<?php
/* Se ejecuta al guardar el modal "Registrar tarjeta" (modal_gestionar.php).
   Si el jugador ya tenía una amarilla en este partido, la segunda amarilla
   dispara automáticamente una roja en el mismo minuto. */

$partido_id  = $_POST["partido_id"];
$jugador_id  = $_POST["jugador_id"];
$tipo        = $_POST["tipo_tarjeta"];
$minuto      = $_POST["minuto"] ?: null;

// Insertar la tarjeta recibida
$pdo->prepare(
    "INSERT INTO sanciones (partido_id, jugador_id, tipo_tarjeta, minuto) VALUES (?, ?, ?, ?)"
)->execute([$partido_id, $jugador_id, $tipo, $minuto]);

// Si fue amarilla, verificar si el jugador ya tenía otra amarilla en este partido
if ($tipo === "amarilla") {
    $stmt = $pdo->prepare(
        "SELECT COUNT(*) FROM sanciones
         WHERE partido_id = ? AND jugador_id = ? AND tipo_tarjeta = 'amarilla'"
    );
    $stmt->execute([$partido_id, $jugador_id]);
    $totalAmarillas = (int) $stmt->fetchColumn();

    // Con 2 amarillas en el mismo partido → insertar roja automática en el minuto siguiente
    if ($totalAmarillas >= 2) {
        $minuto_roja = $minuto !== null ? $minuto + 1 : null;
        $pdo->prepare(
            "INSERT INTO sanciones (partido_id, jugador_id, tipo_tarjeta, minuto) VALUES (?, ?, 'roja', ?)"
        )->execute([$partido_id, $jugador_id, $minuto_roja]);
    }
}

redirigir_a_partidos();
