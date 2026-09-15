<?php
/* Se ejecuta al guardar el modal "Registrar lesión" (modal_evento.php). */

$pdo->prepare(
    "INSERT INTO lesiones (partido_id, jugador_id, descripcion, minuto) VALUES (?, ?, ?, ?)"
)->execute([
    $_POST["partido_id"],
    $_POST["jugador_id"],
    trim($_POST["descripcion"] ?? ""),
    $_POST["minuto"] ?: null,
]);

redirigir_a_partidos();
