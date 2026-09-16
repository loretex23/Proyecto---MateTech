<?php
/* Reemplaza registrar_lesion.php.
   Borra todas las lesiones del partido y las reinserta desde el formulario. */

$partido_id = (int) $_POST['partido_id'];
$jugadores  = $_POST['les_jugador'] ?? [];
$descripciones = $_POST['les_desc'] ?? [];
$minutos    = $_POST['les_minuto']  ?? [];

// Borrar y reinsertar
$pdo->prepare("DELETE FROM lesiones WHERE partido_id = ?")->execute([$partido_id]);

$insertar = $pdo->prepare(
    "INSERT INTO lesiones (partido_id, jugador_id, descripcion, minuto) VALUES (?, ?, ?, ?)"
);

foreach ($jugadores as $i => $jid) {
    if (!$jid) continue;
    $desc   = trim($descripciones[$i] ?? '');
    $minuto = $minutos[$i] ?: null;

    $insertar->execute([$partido_id, (int)$jid, $desc, $minuto]);
}

redirigir_a_partidos();

