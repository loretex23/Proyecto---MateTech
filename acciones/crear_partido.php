<?php
/* Se ejecuta cuando se envía el formulario "Nuevo partido" (modal_crear.php).
   Usa $pdo, que ya viene cargado desde partidos.php. */

$pdo->prepare(
    "INSERT INTO partidos (categoria_id, club_local_id, club_visitante_id, fecha_partido, estado)
     VALUES (?, ?, ?, ?, ?)"
)->execute([
    $_POST["categoria_id"],
    $_POST["club_local_id"],
    $_POST["club_visitante_id"],
    $_POST["fecha_partido"] ?: null,
    $_POST["estado"] ?? "sin_fecha",
]);

redirigir_a_partidos();
