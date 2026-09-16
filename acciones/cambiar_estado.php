<?php

$pdo->prepare(
    "UPDATE partidos SET estado = ?, fecha_partido = ? WHERE id = ?"
)->execute([
    $_POST["estado"],
    $_POST["fecha_partido"] ?: null,
    $_POST["partido_id"],
]);

redirigir_a_partidos();
