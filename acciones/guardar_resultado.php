<?php
/* Se ejecuta al guardar el modal "Resultado y goles" (modal_resultado.php).
   1) Guarda el marcador final y marca el partido como jugado.
   2) Borra los goles anteriores y carga de nuevo los del formulario. */

$partido_id = (int) $_POST["partido_id"];

$pdo->prepare(
    "UPDATE partidos SET goles_local = ?, goles_visitante = ?, estado = 'jugado' WHERE id = ?"
)->execute([
    $_POST["goles_local"],
    $_POST["goles_visitante"],
    $partido_id,
]);

$pdo->prepare("DELETE FROM goles WHERE partido_id = ?")->execute([$partido_id]);

$insertar_gol = $pdo->prepare(
    "INSERT INTO goles (partido_id, jugador_id, minuto, tipo) VALUES (?, ?, ?, ?)"
);

$jugadores_goleadores = $_POST["gol_jugador"] ?? [];
foreach ($jugadores_goleadores as $indice => $jugador_id) {
    if (!$jugador_id) continue; // fila sin jugador asignado, se ignora

    $insertar_gol->execute([
        $partido_id,
        (int) $jugador_id,
        $_POST["gol_minuto"][$indice] ?: null,
        $_POST["gol_tipo"][$indice] ?? "normal",
    ]);
}

redirigir_a_partidos();
