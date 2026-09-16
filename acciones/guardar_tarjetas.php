<?php
/* Reemplaza registrar_tarjeta.php.
   Borra todas las sanciones del partido y las reinserta desde el formulario.
   Validación por jugador: máx 2 amarillas, máx 1 roja, no amarilla tras roja directa. */

$partido_id = (int) $_POST['partido_id'];
$jugadores  = $_POST['tar_jugador'] ?? [];
$tipos      = $_POST['tar_tipo']    ?? [];
$minutos    = $_POST['tar_minuto']  ?? [];

// Borrar y reinsertar
$pdo->prepare("DELETE FROM sanciones WHERE partido_id = ?")->execute([$partido_id]);

$insertar = $pdo->prepare(
    "INSERT INTO sanciones (partido_id, jugador_id, tipo_tarjeta, minuto) VALUES (?, ?, ?, ?)"
);

// Validar por jugador: acumular estado antes de insertar
$estado = []; // jid => ['am' => n, 'ro' => n]

foreach ($jugadores as $i => $jid) {
    if (!$jid) continue;
    $tipo   = $tipos[$i]   ?? 'amarilla';
    $minuto = $minutos[$i] ?: null;

    if (!isset($estado[$jid])) $estado[$jid] = ['am' => 0, 'ro' => 0];
    $c = &$estado[$jid];

    if ($tipo === 'amarilla') {
        if ($c['ro'] > 0)   continue; // tiene roja directa, no puede recibir amarilla
        if ($c['am'] >= 2)  continue; // ya tiene máx amarillas
        $c['am']++;
    } elseif ($tipo === 'roja') {
        if ($c['ro'] >= 1)  continue; // ya tiene roja
        if ($c['am'] >= 2)  continue; // ya tiene 2 amarillas → la roja es automática, viene como fila propia
        $c['ro']++;
    } else {
        continue;
    }

    $insertar->execute([$partido_id, (int)$jid, $tipo, $minuto]);
}

redirigir_a_partidos();

