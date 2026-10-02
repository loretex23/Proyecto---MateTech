<?php

$competencia = $_POST["competencia"] ?? "Liga";

$pdo->prepare(
    "INSERT INTO partidos (competencia, categoria_id, club_local_id, club_visitante_id, fecha_partido, estado)
     VALUES (?, ?, ?, ?, ?, ?)"
)->execute([
    $competencia,
    $_POST["categoria_id"],
    $_POST["club_local_id"],
    $_POST["club_visitante_id"],
    $_POST["fecha_partido"] ?: null,
    $_POST["estado"] ?? "sin_fecha",
]);

redirigir_a_partidos();