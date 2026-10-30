<?php
if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $usuario = $_SESSION["Usuario"] ?? ($esAdmin ? "Admin" : "Club");
    $flash = ["danger", "No se pudo completar la operación."];
    
    $in = fn(string $k) => (int) ($_POST[$k] ?? 0);
    $fin = fn(bool $ok) => $ok ? $pdo->commit() : $pdo->rollBack();
    
    $responder = fn(int $id, string $est) => q($pdo, "UPDATE solicitudes_transferencia SET estado = ?, fecha_respuesta = NOW(), respondida_por = ? WHERE id = ?", [$est, $usuario, $id]);
    $getTransf = fn(int $id) => q($pdo, "SELECT jugador_id, club_origen_id, club_destino_id FROM transferencias WHERE id = ? FOR UPDATE", [$id])->fetch(PDO::FETCH_ASSOC);
    $getSol = fn(int $id) => q($pdo, "SELECT * FROM solicitudes_transferencia WHERE id = ? FOR UPDATE", [$id])->fetch(PDO::FETCH_ASSOC);

    try {
        if ($esAdmin) {
            // ACCIONES DE ADMIN
            if (isset($_POST["btn_transferir"])) {
                $pdo->beginTransaction();
                [$ok, $tipo, $msg] = ejecutarTransferencia($pdo, $in("jugador_id"), $in("club_destino_id"), $usuario);
                $fin($ok);
                $flash = [$tipo, $msg];
            } 
            elseif (isset($_POST["btn_editar"])) {
                $transf_id = $in("transferencia_id");
                $nuevo = $in("club_destino_id");
                $ok = false;
                $pdo->beginTransaction();
                $t = $getTransf($transf_id);
                
                if (!$t || !clubValido($pdo, $nuevo)) {
                    $flash = ["danger", "Transferencia o club de destino inválido."];
                } elseif ((int)$t['club_origen_id'] === $nuevo) {
                    $flash = ["warning", "El destino no puede ser igual al club de origen."];
                } elseif ((int)$t['club_destino_id'] === $nuevo) {
                    $flash = ["info", "No hubo cambios: ese ya es el club de destino."];
                } else {
                    $club_jugador = (int)clubDeJugador($pdo, $t['jugador_id']);
                    q($pdo, "UPDATE transferencias SET club_destino_id = ?, realizada_por = ? WHERE id = ?", [$nuevo, $usuario, $transf_id]);
                    
                    if ($club_jugador === (int)$t['club_destino_id']) {
                        q($pdo, "UPDATE jugadores SET club_id = ? WHERE id = ?", [$nuevo, $t['jugador_id']]);
                        $flash = ["success", "Transferencia editada y jugador movido al nuevo club."];
                    } else {
                        $flash = ["warning", "Transferencia editada, pero el jugador ya no estaba en el club anterior."];
                    }
                    $ok = true;
                }
                $fin($ok);
            } 
            elseif (isset($_POST["btn_eliminar"])) {
                $transf_id = $in("transferencia_id");
                $ok = false;
                $pdo->beginTransaction();
                $t = $getTransf($transf_id);
                
                if (!$t) {
                    $flash = ["danger", "La transferencia no existe."];
                } else {
                    $revertido = (int)clubDeJugador($pdo, $t['jugador_id']) === (int)$t['club_destino_id'];
                    if ($revertido) {
                        q($pdo, "UPDATE jugadores SET club_id = ? WHERE id = ?", [$t['club_origen_id'], $t['jugador_id']]);
                    }
                    q($pdo, "DELETE FROM transferencias WHERE id = ?", [$transf_id]);
                    $ok = true;
                    $flash = $revertido 
                        ? ["success", "Transferencia anulada: el jugador volvió a su club de origen."] 
                        : ["warning", "Transferencia eliminada del historial."];
                }
                $fin($ok);
            } 
            elseif (isset($_POST["btn_sol_aceptar"]) || isset($_POST["btn_sol_rechazar"])) {
                $sol_id = $in("solicitud_id");
                $aceptar = isset($_POST["btn_sol_aceptar"]);
                $ok = false;
                $pdo->beginTransaction();
                $s = $getSol($sol_id);
                
                if (!$s || $s['estado'] !== 'pendiente') {
                    $flash = ["warning", "La solicitud no existe o ya fue respondida."];
                } elseif ($aceptar) {
                    [$r, $tipo, $msg] = ejecutarTransferencia($pdo, (int)$s['jugador_id'], (int)$s['club_solicitante_id'], $usuario, (int)$s['club_origen_id']);
                    if ($r) {
                        $responder($sol_id, 'aceptada');
                        $ok = true;
                        $flash = ["success", "Solicitud aceptada y transferencia realizada."];
                    } else {
                        $flash = [$tipo, $msg];
                    }
                } else {
                    $responder($sol_id, 'rechazada');
                    $ok = true;
                    $flash = ["success", "Solicitud rechazada."];
                }
                $fin($ok);
            } 
            elseif (isset($_POST["btn_sol_eliminar"])) {
                $flash = q($pdo, "DELETE FROM solicitudes_transferencia WHERE id = ?", [$in("solicitud_id")])->rowCount()
                    ? ["success", "Solicitud eliminada."] : ["danger", "La solicitud no existe."];
            }
        } elseif ($id_club_actual) {
            // ACCIONES DE CLUBES
            if (isset($_POST["btn_solicitar"])) {
                $jugador_id = $in("jugador_id");
                $ok = false;
                $pdo->beginTransaction();
                $club_j = clubDeJugador($pdo, $jugador_id);
                
                if ($club_j === false) {
                    $flash = ["danger", "El jugador no existe."];
                } elseif ((int)$club_j === $id_club_actual) {
                    $flash = ["warning", "Ese jugador ya pertenece a tu club."];
                } elseif (q($pdo, "SELECT COUNT(*) FROM solicitudes_transferencia WHERE jugador_id = ? AND club_solicitante_id = ? AND estado = 'pendiente'", [$jugador_id, $id_club_actual])->fetchColumn() > 0) {
                    $flash = ["info", "Ya enviaste una solicitud pendiente por este jugador."];
                } else {
                    q($pdo, "INSERT INTO solicitudes_transferencia (jugador_id, club_solicitante_id, club_origen_id) VALUES (?, ?, ?)", [$jugador_id, $id_club_actual, $club_j]);
                    $ok = true;
                    $flash = ["success", "Solicitud enviada al club del jugador."];
                }
                $fin($ok);
            } 
            elseif (isset($_POST["btn_aceptar"])) {
                $sol_id = $in("solicitud_id");
                $ok = false;
                $pdo->beginTransaction();
                $s = $getSol($sol_id);
                
                if (!$s || (int)$s['club_origen_id'] !== $id_club_actual || $s['estado'] !== 'pendiente') {
                    $flash = ["danger", "Solicitud inválida o ya respondida."];
                } else {
                    [$r, $tipo, $msg] = ejecutarTransferencia($pdo, (int)$s['jugador_id'], (int)$s['club_solicitante_id'], $usuario, $id_club_actual);
                    if ($r) {
                        $responder($sol_id, 'aceptada');
                        $ok = true;
                        $flash = ["success", "Solicitud aceptada: el jugador fue transferido."];
                    } else {
                        $flash = [$tipo, $msg];
                    }
                }
                $fin($ok);
            } 
            elseif (isset($_POST["btn_rechazar"])) {
                $flash = q($pdo, "UPDATE solicitudes_transferencia SET estado = 'rechazada', fecha_respuesta = NOW(), respondida_por = ? WHERE id = ? AND club_origen_id = ? AND estado = 'pendiente'", [$usuario, $in("solicitud_id"), $id_club_actual])->rowCount()
                    ? ["success", "Solicitud rechazada."] : ["danger", "Solicitud inválida o ya respondida."];
            } 
            elseif (isset($_POST["btn_cancelar"])) {
                $flash = q($pdo, "DELETE FROM solicitudes_transferencia WHERE id = ? AND club_solicitante_id = ? AND estado = 'pendiente'", [$in("solicitud_id"), $id_club_actual])->rowCount()
                    ? ["success", "Solicitud cancelada."] : ["danger", "No se pudo cancelar la solicitud."];
            }
        }
    } catch (PDOException $e) {
        if ($pdo->inTransaction()) $pdo->rollBack();
        $flash = ["danger", "Error en la operación: " . $e->getMessage()];
    }

    $_SESSION['flash'] = $flash;
    header("Location: transferencias.php" . ((isset($_GET['cat']) && (int)$_GET['cat'] > 0) ? "?cat=" . (int)$_GET['cat'] : ""));
    exit();
}