<?php
require_once "login/auth.php";
include '../sql/basededatos.php';

$esAdmin = ($_SESSION["Rol"] ?? "") === "Admin";
$id_club_actual = isset($_SESSION['ClubID']) ? (int)$_SESSION['ClubID'] : null;
$flash = $_SESSION['flash'] ?? null;
unset($_SESSION['flash']);

/* ---------------------------------------------------------
 * HELPERS
 * --------------------------------------------------------- */
function q(PDO $pdo, string $sql, array $p = []): PDOStatement { $s = $pdo->prepare($sql); $s->execute($p); return $s; }
function h($v): string { return htmlspecialchars((string)$v, ENT_QUOTES, 'UTF-8'); }
function badge(string $c, string $t): string { return "<span class=\"badge bg-$c\">" . h($t) . '</span>'; }
function badgeEstado(string $e): string {
    [$c, $t] = ['pendiente' => ['warning text-dark', 'Pendiente'], 'aceptada' => ['success', 'Aceptada'], 'rechazada' => ['danger', 'Rechazada']][$e] ?? ['secondary', $e];
    return badge($c, $t);
}
function fmtFecha(?string $d): string { return $d ? date("d/m/Y H:i", strtotime($d)) : '—'; }
function clubValido(PDO $pdo, int $id): bool { return (bool)q($pdo, "SELECT COUNT(*) FROM club WHERE id = ? AND rol = 'Club'", [$id])->fetchColumn(); }
function clubDeJugador(PDO $pdo, int $id) { return q($pdo, "SELECT club_id FROM jugadores WHERE id = ? FOR UPDATE", [$id])->fetchColumn(); }

/** Formulario con un botón de acción (confirmación opcional). */
function btnForm(string $name, int $id, string $cls, string $icon, string $title, string $confirm = '', string $label = '', string $field = 'solicitud_id'): string {
    $c = $confirm ? ' onsubmit="return confirm(\'' . $confirm . '\');"' : '';
    return "<form method=\"POST\"$c><input type=\"hidden\" name=\"$field\" value=\"$id\">"
         . "<button type=\"submit\" name=\"$name\" value=\"1\" class=\"btn $cls btn-sm\" title=\"$title\" aria-label=\"$title\"><i class=\"ti ti-$icon\"></i>$label</button></form>";
}
function thead(array $cols): string {
    $o = '<thead><tr>';
    foreach ($cols as [$w, $t, $c]) $o .= "<th style=\"width:$w%\"" . ($c ? " class=\"$c\"" : '') . ">$t</th>";
    return $o . '</tr></thead>';
}
function vacio(int $cols, string $msg): string { return "<tr><td colspan=\"$cols\" class=\"text-center text-muted py-4\">$msg</td></tr>"; }
function pendTitulo(int $n): string { return $n ? ' <span class="badge bg-warning text-dark">' . $n . ' pendiente' . ($n > 1 ? 's' : '') . '</span>' : ''; }
function cardIni(string $titulo, string $mb = 'mb-4'): void {
    echo "<div class=\"card shadow-sm border-0 bg-dark text-white p-3 $mb\"><div class=\"card-body\"><h3 class=\"card-title mb-4\">$titulo</h3>";
}
function cardFin(): void { echo '</div></div>'; }

function filtroCategoria(array $categorias, int $actual): void { ?>
    <form method="GET" class="d-flex align-items-center gap-2 mb-3">
        <label for="cat" class="form-label mb-0"><i class="ti ti-filter"></i> Categoría</label>
        <select name="cat" id="cat" class="form-select form-select-sm w-auto" onchange="this.form.submit()">
            <option value="0">Todas</option>
            <?php foreach ($categorias as $c): ?>
                <option value="<?= (int)$c['id'] ?>" <?= $actual === (int)$c['id'] ? 'selected' : '' ?>><?= h($c['nombre']) ?></option>
            <?php endforeach; ?>
        </select>
        <?php if ($actual > 0): ?><a href="transferencias.php" class="btn btn-outline-light btn-sm">Limpiar</a><?php endif; ?>
    </form>
<?php }

/**
 * Ejecuta un pase dentro de una transacción ya abierta (el llamador hace commit/rollback).
 * Devuelve [ok, tipoAlerta, mensaje]. Las solicitudes pendientes sobre el jugador quedan rechazadas.
 */
function ejecutarTransferencia(PDO $pdo, int $jugador_id, int $destino_id, string $usuario, ?int $origen_esperado = null): array {
    $origen_id = clubDeJugador($pdo, $jugador_id);

    if ($origen_id === false || !clubValido($pdo, $destino_id)) return [false, "danger", "Jugador o club de destino inválido."];
    if ((int)$origen_id === $destino_id) return [false, "warning", "El jugador ya pertenece a ese club."];
    if ($origen_esperado !== null && (int)$origen_id !== $origen_esperado) return [false, "warning", "El jugador ya no pertenece al club de origen de la solicitud."];

    q($pdo, "UPDATE jugadores SET club_id = ? WHERE id = ?", [$destino_id, $jugador_id]);
    q($pdo, "INSERT INTO transferencias (jugador_id, club_origen_id, club_destino_id, realizada_por) VALUES (?, ?, ?, ?)", [$jugador_id, $origen_id, $destino_id, $usuario]);
    q($pdo, "UPDATE solicitudes_transferencia SET estado = 'rechazada', fecha_respuesta = NOW(), respondida_por = ? WHERE jugador_id = ? AND estado = 'pendiente'", [$usuario, $jugador_id]);

    return [true, "success", "Transferencia realizada correctamente."];
}

/* ---------------------------------------------------------
 * ACCIONES (POST)
 * --------------------------------------------------------- */
if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $usuario = $_SESSION["Usuario"] ?? ($esAdmin ? "Admin" : "Club");
    $flash = ["danger", "No se pudo completar la operación."];
    $in = fn(string $k) => (int)($_POST[$k] ?? 0);
    $fin = fn(bool $ok) => $ok ? $pdo->commit() : $pdo->rollBack();
    $responder = fn(int $id, string $est) => q($pdo, "UPDATE solicitudes_transferencia SET estado = ?, fecha_respuesta = NOW(), respondida_por = ? WHERE id = ?", [$est, $usuario, $id]);
    $getTransf = fn(int $id) => q($pdo, "SELECT jugador_id, club_origen_id, club_destino_id FROM transferencias WHERE id = ? FOR UPDATE", [$id])->fetch(PDO::FETCH_ASSOC);
    $getSol = fn(int $id) => q($pdo, "SELECT * FROM solicitudes_transferencia WHERE id = ? FOR UPDATE", [$id])->fetch(PDO::FETCH_ASSOC);

    try {
        /* ======================= ADMIN ======================= */
        if ($esAdmin) {

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

                if (!$t || !clubValido($pdo, $nuevo)) $flash = ["danger", "Transferencia o club de destino inválido."];
                elseif ((int)$t['club_origen_id'] === $nuevo) $flash = ["warning", "El destino no puede ser igual al club de origen."];
                elseif ((int)$t['club_destino_id'] === $nuevo) $flash = ["info", "No hubo cambios: ese ya es el club de destino."];
                else {
                    $club_jugador = (int)clubDeJugador($pdo, $t['jugador_id']);
                    q($pdo, "UPDATE transferencias SET club_destino_id = ?, realizada_por = ? WHERE id = ?", [$nuevo, $usuario, $transf_id]);

                    if ($club_jugador === (int)$t['club_destino_id']) {
                        q($pdo, "UPDATE jugadores SET club_id = ? WHERE id = ?", [$nuevo, $t['jugador_id']]);
                        $flash = ["success", "Transferencia editada y jugador movido al nuevo club."];
                    } else {
                        $flash = ["warning", "Transferencia editada, pero el jugador ya no estaba en el club de destino anterior, por lo que su club actual no se modificó."];
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

                if (!$t) $flash = ["danger", "La transferencia no existe."];
                else {
                    $revertido = (int)clubDeJugador($pdo, $t['jugador_id']) === (int)$t['club_destino_id'];
                    if ($revertido) q($pdo, "UPDATE jugadores SET club_id = ? WHERE id = ?", [$t['club_origen_id'], $t['jugador_id']]);
                    q($pdo, "DELETE FROM transferencias WHERE id = ?", [$transf_id]);
                    $ok = true;
                    $flash = $revertido
                        ? ["success", "Transferencia anulada: el jugador volvió a su club de origen."]
                        : ["warning", "Transferencia eliminada del historial. El jugador no volvió al club de origen porque ya se encontraba en otro club."];
                }
                $fin($ok);
            }

            elseif (isset($_POST["btn_sol_aceptar"]) || isset($_POST["btn_sol_rechazar"])) {
                $sol_id = $in("solicitud_id");
                $aceptar = isset($_POST["btn_sol_aceptar"]);
                $ok = false;

                $pdo->beginTransaction();
                $s = $getSol($sol_id);

                if (!$s || $s['estado'] !== 'pendiente') $flash = ["warning", "La solicitud no existe o ya fue respondida."];
                elseif ($aceptar) {
                    [$r, $tipo, $msg] = ejecutarTransferencia($pdo, (int)$s['jugador_id'], (int)$s['club_solicitante_id'], $usuario, (int)$s['club_origen_id']);
                    if ($r) {
                        $responder($sol_id, 'aceptada');
                        $ok = true;
                        $flash = ["success", "Solicitud aceptada y transferencia realizada."];
                    } else $flash = [$tipo, $msg];
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
        }

        /* ======================= CLUB ======================= */
        elseif ($id_club_actual) {

            if (isset($_POST["btn_solicitar"])) {
                $jugador_id = $in("jugador_id");
                $ok = false;

                $pdo->beginTransaction();
                $club_j = clubDeJugador($pdo, $jugador_id);

                if ($club_j === false) $flash = ["danger", "El jugador no existe."];
                elseif ((int)$club_j === $id_club_actual) $flash = ["warning", "Ese jugador ya pertenece a tu club."];
                elseif (q($pdo, "SELECT COUNT(*) FROM solicitudes_transferencia WHERE jugador_id = ? AND club_solicitante_id = ? AND estado = 'pendiente'", [$jugador_id, $id_club_actual])->fetchColumn() > 0)
                    $flash = ["info", "Ya enviaste una solicitud pendiente por este jugador."];
                else {
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

                if (!$s || (int)$s['club_origen_id'] !== $id_club_actual || $s['estado'] !== 'pendiente') $flash = ["danger", "Solicitud inválida o ya respondida."];
                else {
                    [$r, $tipo, $msg] = ejecutarTransferencia($pdo, (int)$s['jugador_id'], (int)$s['club_solicitante_id'], $usuario, $id_club_actual);
                    if ($r) {
                        $responder($sol_id, 'aceptada');
                        $ok = true;
                        $flash = ["success", "Solicitud aceptada: el jugador fue transferido."];
                    } else $flash = [$tipo, $msg];
                }
                $fin($ok);
            }

            elseif (isset($_POST["btn_rechazar"])) {
                $flash = q($pdo, "UPDATE solicitudes_transferencia SET estado = 'rechazada', fecha_respuesta = NOW(), respondida_por = ?
                                  WHERE id = ? AND club_origen_id = ? AND estado = 'pendiente'", [$usuario, $in("solicitud_id"), $id_club_actual])->rowCount()
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
    header("Location: transferencias.php" . (isset($_GET['cat']) && (int)$_GET['cat'] > 0 ? "?cat=" . (int)$_GET['cat'] : ""));
    exit();
}

/* ---------------------------------------------------------
 * DATOS
 * --------------------------------------------------------- */
$cat_filtro = (int)($_GET['cat'] ?? 0);
$jugadores_transferibles = $jugadores_admin = $clubes = $categorias = $historial = [];
$solicitudes_admin = $solicitudes_recibidas = $solicitudes_enviadas = [];

$baseSol = "SELECT s.id, s.fecha, s.estado, s.fecha_respuesta, s.respondida_por,
                   s.jugador_id, s.club_solicitante_id, s.club_origen_id,
                   j.ci, j.nombre, j.apellido, cat.nombre AS categoria,
                   cs.nombre AS solicitante, co.nombre AS origen
            FROM solicitudes_transferencia s
            INNER JOIN jugadores j ON j.id = s.jugador_id
            INNER JOIN categorias cat ON cat.id = j.categoria_id
            INNER JOIN club cs ON cs.id = s.club_solicitante_id
            INNER JOIN club co ON co.id = s.club_origen_id";
$ordenSol = " ORDER BY FIELD(s.estado, 'pendiente', 'aceptada', 'rechazada'), s.fecha DESC, s.id DESC LIMIT 100";

try {
    $categorias = $pdo->query("SELECT id, nombre FROM categorias ORDER BY nombre ASC")->fetchAll(PDO::FETCH_ASSOC);

    if ($esAdmin) {
        $jugadores_admin = $pdo->query("SELECT j.id, j.ci, j.nombre, j.apellido, j.club_id, j.categoria_id, c.nombre AS club
                                        FROM jugadores j INNER JOIN club c ON c.id = j.club_id
                                        ORDER BY c.nombre ASC, j.apellido ASC, j.nombre ASC")->fetchAll(PDO::FETCH_ASSOC);
        $clubes = $pdo->query("SELECT id, nombre FROM club WHERE rol = 'Club' ORDER BY nombre ASC")->fetchAll(PDO::FETCH_ASSOC);
        $solicitudes_admin = $pdo->query($baseSol . $ordenSol)->fetchAll(PDO::FETCH_ASSOC);
    } elseif ($id_club_actual) {
        $sql = "SELECT j.id, j.ci, j.nombre, j.apellido, j.carnet_vencimiento,
                       c.nombre AS club_actual, cat.nombre AS categoria,
                       (SELECT COUNT(*) FROM solicitudes_transferencia s
                        WHERE s.jugador_id = j.id AND s.club_solicitante_id = :club2 AND s.estado = 'pendiente') AS ya_solicitado
                FROM jugadores j
                INNER JOIN club c ON j.club_id = c.id
                INNER JOIN categorias cat ON cat.id = j.categoria_id
                WHERE j.club_id != :club";
        $params = ['club' => $id_club_actual, 'club2' => $id_club_actual];
        if ($cat_filtro > 0) { $sql .= " AND j.categoria_id = :cat"; $params['cat'] = $cat_filtro; }
        $sql .= " ORDER BY cat.nombre ASC, c.nombre ASC, j.apellido ASC, j.nombre ASC";
        $jugadores_transferibles = q($pdo, $sql, $params)->fetchAll(PDO::FETCH_ASSOC);

        $solicitudes_recibidas = q($pdo, $baseSol . " WHERE s.club_origen_id = :club" . $ordenSol, ['club' => $id_club_actual])->fetchAll(PDO::FETCH_ASSOC);
        $solicitudes_enviadas  = q($pdo, $baseSol . " WHERE s.club_solicitante_id = :club" . $ordenSol, ['club' => $id_club_actual])->fetchAll(PDO::FETCH_ASSOC);
    }

    $sql = "SELECT t.id, t.fecha, t.jugador_id, t.club_origen_id, t.club_destino_id,
                   j.ci, j.nombre, j.apellido, cat.nombre AS categoria,
                   co.nombre AS origen, cd.nombre AS destino
            FROM transferencias t
            INNER JOIN jugadores j ON j.id = t.jugador_id
            INNER JOIN categorias cat ON cat.id = j.categoria_id
            INNER JOIN club co ON co.id = t.club_origen_id
            INNER JOIN club cd ON cd.id = t.club_destino_id";
    $params = [];
    if ($cat_filtro > 0) { $sql .= " WHERE j.categoria_id = :cat"; $params['cat'] = $cat_filtro; }
    $historial = q($pdo, $sql . " ORDER BY t.fecha DESC, t.id DESC LIMIT 50", $params)->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    $error_db = "Error al cargar los datos: " . $e->getMessage();
}

$pend = fn(array $a) => count(array_filter($a, fn($s) => $s['estado'] === 'pendiente'));
$pendientes_recibidas = $pend($solicitudes_recibidas);
$pendientes_admin = $pend($solicitudes_admin);
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="icon" href="../img/logo.png" type="image/png">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/@tabler/icons-webfont@latest/dist/tabler-icons.min.css">
    <link rel="icon" href="../img/logo.png" type="image/png">
    <link href="../estilos.css" rel="stylesheet">
    <title>LeagueFlow - Transferencias</title>
</head>
<body class="fondo-body">

<?php include 'diseños/navbar.php'; ?>

<main class="container my-4 pagina-transferencias">
    <div class="home-hero mb-5">
        <div>
            <h1>Transferencias</h1>
            <p><?= $esAdmin ? "Gestioná los pases de jugadores entre clubes." : "Mostrá interés por jugadores de otros clubes y seguí tus solicitudes." ?></p>
        </div>
        <img src="../img/Login-foto0.png" alt="LeagueFlow" class="home-hero-logo">
    </div>

    <?php if ($flash): ?><div class="alert alert-<?= h($flash[0]) ?>" role="alert"><?= h($flash[1]) ?></div><?php endif; ?>
    <?php if (isset($error_db)): ?><div class="alert alert-danger" role="alert"><?= h($error_db) ?></div><?php endif; ?>

    <?php if ($esAdmin): ?>
        <!-- NUEVA TRANSFERENCIA -->
        <?php cardIni('<i class="ti ti-transfer"></i> Nueva transferencia'); ?>
            <form method="POST" class="row g-3 align-items-end">
                <div class="col-md-2">
                    <label for="filtro_categoria" class="form-label">Categoría</label>
                    <select id="filtro_categoria" class="form-select">
                        <option value="0">Todas</option>
                        <?php foreach ($categorias as $c): ?><option value="<?= (int)$c['id'] ?>"><?= h($c['nombre']) ?></option><?php endforeach; ?>
                    </select>
                </div>
                <div class="col-md-4">
                    <label for="jugador_id" class="form-label">Jugador</label>
                    <select name="jugador_id" id="jugador_id" class="form-select" required></select>
                </div>
                <div class="col-md-3">
                    <label for="club_destino_id" class="form-label">Club de destino</label>
                    <select name="club_destino_id" id="club_destino_id" class="form-select" required>
                        <option value="" selected disabled>Seleccionar club</option>
                        <?php foreach ($clubes as $c): ?><option value="<?= (int)$c['id'] ?>"><?= h($c['nombre']) ?></option><?php endforeach; ?>
                    </select>
                </div>
                <div class="col-md-3">
                    <button type="submit" name="btn_transferir" value="1" class="btn btn-success w-100"><i class="ti ti-arrows-exchange"></i> Transferir</button>
                </div>
            </form>
        <?php cardFin(); ?>

        <!-- SOLICITUDES (ADMIN) -->
        <?php cardIni('<i class="ti ti-inbox"></i> Solicitudes de Transferencia' . pendTitulo($pendientes_admin)); ?>
            <div class="tabla-solicitudes-wrap">
            <table class="table table-dark table-hover align-middle tabla-fit tabla-solicitudes">
                <?= thead([[12, 'Fecha', ''], [17, 'Jugador', ''], [9, 'Categoría', ''], [15, 'Club solicitante', ''], [15, 'Club actual', ''], [10, 'Estado', ''], [22, 'Acciones', 'text-end']]) ?>
                <tbody>
                    <?php if ($solicitudes_admin): foreach ($solicitudes_admin as $s): ?>
                        <tr>
                            <td><?= h(fmtFecha($s['fecha'])) ?></td>
                            <td><?= h($s['nombre'] . ' ' . $s['apellido']) ?></td>
                            <td><?= badge('info text-dark', $s['categoria']) ?></td>
                            <td><?= badge('success', $s['solicitante']) ?></td>
                            <td><?= badge('secondary', $s['origen']) ?></td>
                            <td><?= badgeEstado($s['estado']) ?></td>
                            <td>
                                <div class="acciones">
                                    <button type="button" class="btn btn-info btn-sm" title="Ver" data-bs-toggle="modal" data-bs-target="#modalVerSol"
                                            data-jugador="<?= h($s['nombre'] . ' ' . $s['apellido']) ?>"
                                            data-ci="<?= h($s['ci']) ?>"
                                            data-cat="<?= h($s['categoria']) ?>"
                                            data-solicitante="<?= h($s['solicitante']) ?>"
                                            data-origen="<?= h($s['origen']) ?>"
                                            data-estado="<?= h(ucfirst($s['estado'])) ?>"
                                            data-fecha="<?= h(fmtFecha($s['fecha'])) ?>"
                                            data-respuesta="<?= h(fmtFecha($s['fecha_respuesta'])) ?>"
                                            data-por="<?= h($s['respondida_por'] ?? '—') ?>"><i class="ti ti-eye"></i></button>
                                    <?php if ($s['estado'] === 'pendiente'): ?>
                                        <?= btnForm('btn_sol_aceptar', (int)$s['id'], 'btn-success', 'check', 'Aceptar solicitud', '¿Aceptar la solicitud y transferir el jugador al club solicitante?') ?>
                                        <?= btnForm('btn_sol_rechazar', (int)$s['id'], 'btn-danger', 'x', 'Rechazar solicitud', '¿Rechazar esta solicitud?') ?>
                                    <?php endif; ?>
                                    <?= btnForm('btn_sol_eliminar', (int)$s['id'], 'btn-danger', 'trash', 'Eliminar', '¿Eliminar esta solicitud?') ?>
                                </div>
                            </td>
                        </tr>
                    <?php endforeach; else: echo vacio(7, 'No hay solicitudes de transferencia.'); endif; ?>
                </tbody>
            </table>
            </div>
        <?php cardFin(); ?>

    <?php else: ?>
        <!-- MERCADO DE PASES -->
        <?php cardIni('Jugadores de otros clubes'); filtroCategoria($categorias, $cat_filtro); ?>
            <div class="row g-2 mb-3">
                <div class="col-md-6">
                    <label for="filtro_nombre_jugador" class="form-label">Buscar por nombre</label>
                    <input type="search" id="filtro_nombre_jugador" class="form-control" placeholder="Nombre o apellido">
                </div>
                <div class="col-md-6">
                    <label for="filtro_ci_jugador" class="form-label">Buscar por cédula de identidad</label>
                    <input type="search" id="filtro_ci_jugador" class="form-control" placeholder="Cédula de identidad">
                </div>
            </div>
            <div class="tabla-wrap">
            <table class="table table-dark table-hover align-middle tabla-fit">
                <?= thead([[18, 'Cédula (CI)', ''], [32, 'Jugador', ''], [20, 'Venc. carnet', ''], [30, 'Acción', 'text-end']]) ?>
                <tbody>
                    <?php if ($jugadores_transferibles): foreach ($jugadores_transferibles as $i => $j): ?>
                        <tr data-jugador
                            data-nombre="<?= h($j['nombre'] . ' ' . $j['apellido']) ?>"
                            data-ci="<?= h($j['ci']) ?>"
                            data-categoria="<?= h($j['categoria']) ?>"
                            data-club="<?= h($j['club_actual']) ?>"
                            <?= $i >= 3 ? 'hidden' : '' ?>>
                            <td><?= h($j['ci']) ?></td>
                            <td><?= h($j['nombre'] . ' ' . $j['apellido']) ?></td>
                            <td><?= h($j['carnet_vencimiento'] ?: 'Sin fecha registrada') ?></td>
                            <td>
                                <div class="acciones">
                                    <?php if ((int)$j['ya_solicitado'] > 0): ?>
                                        <button type="button" class="btn btn-outline-light btn-sm" disabled><i class="ti ti-clock"></i> Solicitud enviada</button>
                                    <?php else: ?>
                                        <?= btnForm('btn_solicitar', (int)$j['id'], 'btn-success', 'user-plus', 'Solicitar Fichaje', '', ' Solicitar Fichaje', 'jugador_id') ?>
                                    <?php endif; ?>
                                </div>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                        <tr id="sin_resultados_filtro" hidden><td colspan="4" class="text-center text-muted py-4">No se encontraron jugadores con esos filtros.</td></tr>
                    <?php else: echo vacio(4, 'No hay jugadores de otros clubes disponibles en este momento.'); endif; ?>
                </tbody>
            </table>
            </div>
            <?php if (count($jugadores_transferibles) > 3): ?>
                <div class="text-center mt-3"><button type="button" id="btn_mostrar_jugadores" class="btn btn-outline-light btn-sm">Mostrar más</button></div>
            <?php endif; ?>
        <?php cardFin(); ?>

        <!-- SOLICITUDES RECIBIDAS -->
        <?php cardIni('<i class="ti ti-inbox"></i> Solicitudes de Transferencia' . pendTitulo($pendientes_recibidas)); ?>
            <p class="text-white-50 small">Otros clubes quieren fichar a jugadores de tu club.</p>
            <div class="tabla-wrap">
            <table class="table table-dark table-hover align-middle tabla-fit">
                <?= thead([[13, 'Fecha', ''], [22, 'Jugador', ''], [12, 'CI', ''], [10, 'Categoría', ''], [18, 'Club solicitante', ''], [11, 'Estado', ''], [14, 'Acciones', 'text-end']]) ?>
                <tbody>
                    <?php if ($solicitudes_recibidas): foreach ($solicitudes_recibidas as $s): ?>
                        <tr>
                            <td><?= h(fmtFecha($s['fecha'])) ?></td>
                            <td><?= h($s['nombre'] . ' ' . $s['apellido']) ?></td>
                            <td><?= h($s['ci']) ?></td>
                            <td><?= badge('info text-dark', $s['categoria']) ?></td>
                            <td><?= badge('success', $s['solicitante']) ?></td>
                            <td><?= badgeEstado($s['estado']) ?></td>
                            <td>
                                <div class="acciones">
                                    <?php if ($s['estado'] === 'pendiente'): ?>
                                        <?= btnForm('btn_aceptar', (int)$s['id'], 'btn-success', 'check', 'Aceptar', '¿Aceptar? El jugador pasará al club solicitante.') ?>
                                        <?= btnForm('btn_rechazar', (int)$s['id'], 'btn-danger', 'x', 'Rechazar', '¿Rechazar esta solicitud?') ?>
                                    <?php else: ?>
                                        <span class="text-muted small"><?= h(fmtFecha($s['fecha_respuesta'])) ?></span>
                                    <?php endif; ?>
                                </div>
                            </td>
                        </tr>
                    <?php endforeach; else: echo vacio(7, 'No recibiste solicitudes de transferencia.'); endif; ?>
                </tbody>
            </table>
            </div>
        <?php cardFin(); ?>

        <!-- SOLICITUDES ENVIADAS -->
        <?php cardIni('<i class="ti ti-send"></i> Mis solicitudes enviadas'); ?>
            <div class="tabla-wrap">
            <table class="table table-dark table-hover align-middle tabla-fit">
                <?= thead([[15, 'Fecha', ''], [25, 'Jugador', ''], [11, 'Categoría', ''], [21, 'Club actual', ''], [14, 'Estado', ''], [14, 'Acciones', 'text-end']]) ?>
                <tbody>
                    <?php if ($solicitudes_enviadas): foreach ($solicitudes_enviadas as $s): ?>
                        <tr>
                            <td><?= h(fmtFecha($s['fecha'])) ?></td>
                            <td><?= h($s['nombre'] . ' ' . $s['apellido']) ?></td>
                            <td><?= badge('info text-dark', $s['categoria']) ?></td>
                            <td><?= badge('secondary', $s['origen']) ?></td>
                            <td><?= badgeEstado($s['estado']) ?></td>
                            <td>
                                <div class="acciones">
                                    <?php if ($s['estado'] === 'pendiente') echo btnForm('btn_cancelar', (int)$s['id'], 'btn-outline-danger', 'trash', 'Cancelar', '¿Cancelar esta solicitud?', ' Cancelar'); ?>
                                </div>
                            </td>
                        </tr>
                    <?php endforeach; else: echo vacio(6, 'Todavía no enviaste solicitudes.'); endif; ?>
                </tbody>
            </table>
            </div>
        <?php cardFin(); ?>
    <?php endif; ?>

    <!-- HISTORIAL -->
    <?php cardIni('<i class="ti ti-history"></i> Historial de transferencias', 'mb-0'); if ($esAdmin) filtroCategoria($categorias, $cat_filtro); ?>
        <div class="tabla-wrap">
        <table class="table table-dark table-hover align-middle tabla-fit">
            <?php
            $cols = $esAdmin
                ? [[12, 'Fecha', ''], [18, 'Jugador', ''], [10, 'CI', ''], [8, 'Cat.', ''], [17, 'Club origen', ''], [4, '', ''], [17, 'Club destino', ''], [14, 'Acciones', 'text-end']]
                : [[14, 'Fecha', ''], [22, 'Jugador', ''], [12, 'CI', ''], [10, 'Cat.', ''], [19, 'Club origen', ''], [4, '', ''], [19, 'Club destino', '']];
            echo thead($cols);
            ?>
            <tbody>
                <?php if ($historial): foreach ($historial as $r): ?>
                    <tr>
                        <td><?= h(fmtFecha($r['fecha'])) ?></td>
                        <td><?= h($r['nombre'] . ' ' . $r['apellido']) ?></td>
                        <td><?= h($r['ci']) ?></td>
                        <td><?= badge('info text-dark', $r['categoria']) ?></td>
                        <td><?= badge('secondary', $r['origen']) ?></td>
                        <td class="text-center"><i class="ti ti-arrow-right"></i></td>
                        <td><?= badge('success', $r['destino']) ?></td>
                        <?php if ($esAdmin): ?>
                            <td>
                                <div class="acciones">
                                    <button type="button" class="btn btn-warning btn-sm" title="Editar" data-bs-toggle="modal" data-bs-target="#modalEditar"
                                            data-id="<?= (int)$r['id'] ?>"
                                            data-jugador="<?= h($r['nombre'] . ' ' . $r['apellido']) ?>"
                                            data-origen-id="<?= (int)$r['club_origen_id'] ?>"
                                            data-destino-id="<?= (int)$r['club_destino_id'] ?>"><i class="ti ti-edit"></i></button>
                                    <?= btnForm('btn_eliminar', (int)$r['id'], 'btn-danger', 'trash', 'Eliminar / anular', '¿Anular esta transferencia? Si el jugador sigue en el club de destino, volverá al club de origen.', '', 'transferencia_id') ?>
                                </div>
                            </td>
                        <?php endif; ?>
                    </tr>
                <?php endforeach; else: echo vacio($esAdmin ? 8 : 7, 'Todavía no se registraron transferencias.'); endif; ?>
            </tbody>
        </table>
        </div>
    <?php cardFin(); ?>
</main>

<?php if ($esAdmin): ?>
<!-- MODAL EDITAR TRANSFERENCIA -->
<div class="modal fade" id="modalEditar" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog">
        <form method="POST" class="modal-content bg-dark text-white">
            <div class="modal-header border-secondary">
                <h5 class="modal-title"><i class="ti ti-edit"></i> Editar transferencia</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Cerrar"></button>
            </div>
            <div class="modal-body">
                <input type="hidden" name="transferencia_id" id="edit_transferencia_id">
                <p class="mb-3">Jugador: <strong id="edit_jugador"></strong></p>
                <label for="edit_club_destino" class="form-label">Nuevo club de destino</label>
                <select name="club_destino_id" id="edit_club_destino" class="form-select" required>
                    <?php foreach ($clubes as $c): ?><option value="<?= (int)$c['id'] ?>"><?= h($c['nombre']) ?></option><?php endforeach; ?>
                </select>
                <div class="form-text text-light">El club de origen no se puede elegir como destino.</div>
            </div>
            <div class="modal-footer border-secondary">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                <button type="submit" name="btn_editar" value="1" class="btn btn-success">Guardar cambios</button>
            </div>
        </form>
    </div>
</div>

<!-- MODAL VER SOLICITUD -->
<div class="modal fade" id="modalVerSol" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content bg-dark text-white">
            <div class="modal-header border-secondary">
                <h5 class="modal-title"><i class="ti ti-eye"></i> Detalle de la solicitud</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Cerrar"></button>
            </div>
            <div class="modal-body">
                <dl class="row mb-0">
                    <?php foreach (['jugador' => 'Jugador', 'ci' => 'CI', 'cat' => 'Categoría', 'solicitante' => 'Club solicitante', 'origen' => 'Club actual', 'estado' => 'Estado', 'fecha' => 'Fecha de solicitud', 'respuesta' => 'Fecha de respuesta', 'por' => 'Respondida por'] as $k => $l): ?>
                        <dt class="col-5"><?= $l ?></dt><dd class="col-7" id="ver_<?= $k ?>"></dd>
                    <?php endforeach; ?>
                </dl>
            </div>
            <div class="modal-footer border-secondary">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cerrar</button>
            </div>
        </div>
    </div>
</div>
<?php endif; ?>

<footer>
    &copy; 2026 MateTech. Todos los derechos reservados.
    <img class="foot" src="../img/logo.png" alt="Logo">
</footer>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<?php if (!$esAdmin): ?>
<script>
const filas = [...document.querySelectorAll('tr[data-jugador]')];
const cuerpo = filas[0]?.parentElement;
const fNombre = document.getElementById('filtro_nombre_jugador');
const fCi = document.getElementById('filtro_ci_jugador');
const btnMas = document.getElementById('btn_mostrar_jugadores');
const sinRes = document.getElementById('sin_resultados_filtro');
let mostrarTodos = false;

if (cuerpo) {
    const lc = s => s.toLocaleLowerCase('es');
    const actualizar = () => {
        const n = lc(fNombre.value.trim()), c = lc(fCi.value.trim());
        const filtrados = filas.filter(f => lc(f.dataset.nombre).includes(n) && lc(f.dataset.ci).includes(c));
        const visibles = mostrarTodos ? filtrados : filtrados.slice(0, 3);

        cuerpo.querySelectorAll('.grupo-jugadores').forEach(g => g.remove());
        filas.forEach(f => f.hidden = true);

        let grupoActual = '';
        visibles.forEach(f => {
            const grupo = `${f.dataset.categoria}\u0000${f.dataset.club}`;
            if (grupo !== grupoActual) {
                grupoActual = grupo;
                const tr = document.createElement('tr');
                tr.className = 'grupo-jugadores';
                const th = document.createElement('th');
                th.colSpan = 4;
                th.scope = 'colgroup';
                th.textContent = `Categoría ${f.dataset.categoria} · ${f.dataset.club}`;
                tr.appendChild(th);
                cuerpo.insertBefore(tr, f);
            }
            f.hidden = false;
        });

        if (sinRes) sinRes.hidden = filtrados.length !== 0;
        if (btnMas) {
            btnMas.hidden = filtrados.length <= 3;
            btnMas.textContent = mostrarTodos ? 'Mostrar menos' : `Mostrar más (${filtrados.length - 3})`;
        }
    };

    [fNombre, fCi].forEach(i => i.addEventListener('input', () => { mostrarTodos = false; actualizar(); }));
    btnMas?.addEventListener('click', () => { mostrarTodos = !mostrarTodos; actualizar(); });
    actualizar();
}
</script>
<?php else: ?>
<script>
const jugadores = <?= json_encode($jugadores_admin, JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT) ?>;
const $ = id => document.getElementById(id);
const selCategoria = $('filtro_categoria'), selJugador = $('jugador_id'), selDestino = $('club_destino_id');

function renderJugadores() {
    const cat = parseInt(selCategoria.value, 10);
    selJugador.innerHTML = '';

    const ph = new Option('Seleccionar jugador', '', true, true);
    ph.disabled = true;
    selJugador.add(ph);

    const lista = jugadores.filter(j => cat === 0 || parseInt(j.categoria_id, 10) === cat);
    let grupo = null, clubActual = null;

    lista.forEach(j => {
        if (clubActual !== j.club_id) {
            clubActual = j.club_id;
            grupo = document.createElement('optgroup');
            grupo.label = j.club;
            selJugador.appendChild(grupo);
        }
        const o = new Option(`${j.apellido}, ${j.nombre} (${j.ci})`, j.id);
        o.dataset.club = j.club_id;
        grupo.appendChild(o);
    });

    if (!lista.length) {
        const vacio = new Option('No hay jugadores en esta categoría', '', false, false);
        vacio.disabled = true;
        selJugador.add(vacio);
    }
    actualizarDestinos();
}

function actualizarDestinos() {
    const clubActual = selJugador.selectedOptions[0]?.dataset.club;
    [...selDestino.options].forEach(o => { o.disabled = o.value !== '' && o.value === clubActual; });
    if (selDestino.selectedOptions[0]?.disabled) selDestino.value = '';
}

selCategoria.addEventListener('change', renderJugadores);
selJugador.addEventListener('change', actualizarDestinos);
renderJugadores();

// Modal editar transferencia
$('modalEditar').addEventListener('show.bs.modal', e => {
    const b = e.relatedTarget, sel = $('edit_club_destino');
    $('edit_transferencia_id').value = b.dataset.id;
    $('edit_jugador').textContent = b.dataset.jugador;
    [...sel.options].forEach(o => { o.disabled = o.value === b.dataset.origenId; });
    sel.value = b.dataset.destinoId;
});

// Modal ver solicitud
$('modalVerSol').addEventListener('show.bs.modal', e => {
    ['jugador', 'ci', 'cat', 'solicitante', 'origen', 'estado', 'fecha', 'respuesta', 'por']
        .forEach(k => $('ver_' + k).textContent = e.relatedTarget.dataset[k] || '—');
});
</script>
<?php endif; ?>
</body>
</html>