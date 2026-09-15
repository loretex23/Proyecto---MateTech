<?php
/* Se ejecuta al guardar el modal "Registrar tarjeta" (modal_evento.php). */

$pdo->prepare(
    "INSERT INTO sanciones (partido_id, jugador_id, tipo_tarjeta, minuto) VALUES (?, ?, ?, ?)"
)->execute([
    $_POST["partido_id"],
    $_POST["jugador_id"],
    $_POST["tipo_tarjeta"],
    $_POST["minuto"] ?: null,
]);

redirigir_a_partidos();
