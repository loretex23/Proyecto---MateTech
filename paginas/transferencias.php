<?php
require_once "login/auth.php";
include '../sql/basededatos.php';

$rol = $_SESSION["Rol"] ?? "";
$esAdmin = $rol === "Admin";
$id_club_actual = isset($_SESSION['ClubID']) ? (int)$_SESSION['ClubID'] : null;

$flash = $_SESSION['flash'] ?? null;
unset($_SESSION['flash']);

/* ---------------------------------------------------------
 * HELPERS
 * --------------------------------------------------------- */
function badgeEstado(string $e): string {
    $map = [
        'pendiente' => ['warning text-dark', 'Pendiente'],
        'aceptada'  => ['success', 'Aceptada'],
        'rechazada' => ['danger', 'Rechazada'],
    ];
    [$c, $t] = $map[$e] ?? ['secondary', $e];
    return '<span class="badge bg-' . $c . '">' . htmlspecialchars($t) . '</span>';
}

function fmtFecha(?string $d): string {
    return $d ? date("d/m/Y H:i", strtotime($d)) : '—';
}

function filtroCategoria(array $categorias, int $actual): void { ?>
    <form method="GET" class="d-flex align-items-center gap-2 mb-3">
        <label for="cat" class="form-label mb-0"><i class="ti ti-filter"></i> Categoría</label>
        <select name="cat" id="cat" class="form-select form-select-sm w-auto" onchange="this.form.submit()">
            <option value="0">Todas</option>
            <?php foreach ($categorias as $c): ?>
                <option value="<?= (int)$c['id'] ?>" <?= $actual === (int)$c['id'] ? 'selected' : '' ?>>
                    <?= htmlspecialchars($c['nombre']) ?>
                </option>
            <?php endforeach; ?>
        </select>
        <?php if ($actual > 0): ?>
            <a href="transferencias.php" class="btn btn-outline-light btn-sm">Limpiar</a>
        <?php endif; ?>
    </form>
<?php }

/**
 * Ejecuta un pase dentro de una transacción ya abierta (el llamador hace commit/rollback).
 * Devuelve [ok, tipoAlerta, mensaje].
 * Al mover al jugador, las solicitudes pendientes sobre él quedan rechazadas.
 */
function ejecutarTransferencia(PDO $pdo, int $jugador_id, int $destino_id, string $usuario, ?int $origen_esperado = null): array {
    $stmt = $pdo->prepare("SELECT club_id FROM jugadores WHERE id = ? FOR UPDATE");
    $stmt->execute([$jugador_id]);
    $origen_id = $stmt->fetchColumn();

    $stmt = $pdo->prepare("SELECT COUNT(*) FROM club WHERE id = ? AND rol = 'Club'");
    $stmt->execute([$destino_id]);
    $destinoValido = (bool)$stmt->fetchColumn();

    if ($origen_id === false || !$destinoValido) {
        return [false, "danger", "Jugador o club de destino inválido."];
    }
    if ((int)$origen_id === $destino_id) {
        return [false, "warning", "El jugador ya pertenece a ese club."];
    }
    if ($origen_esperado !== null && (int)$origen_id !== $origen_esperado) {
        return [false, "warning", "El jugador ya no pertenece al club de origen de la solicitud."];
    }

    $pdo->prepare("UPDATE jugadores SET club_id = ? WHERE id = ?")->execute([$destino_id, $jugador_id]);
    $pdo->prepare("INSERT INTO transferencias (jugador_id, club_origen_id, club_destino_id, realizada_por) VALUES (?, ?, ?, ?)")
        ->execute([$jugador_id, $origen_id, $destino_id, $usuario]);
    $pdo->prepare("UPDATE solicitudes_transferencia
                   SET estado = 'rechazada', fecha_respuesta = NOW(), respondida_por = ?
                   WHERE jugador_id = ? AND estado = 'pendiente'")
        ->execute([$usuario, $jugador_id]);

    return [true, "success", "Transferencia realizada correctamente."];
}

/* ---------------------------------------------------------
 * ACCIONES (POST)
 * --------------------------------------------------------- */
if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $usuario = $_SESSION["Usuario"] ?? ($esAdmin ? "Admin" : "Club");
    $flash = ["danger", "No se pudo completar la operación."];

    try {
        /* ======================= ADMIN ======================= */
        if ($esAdmin) {

            /* ---------- NUEVA TRANSFERENCIA ---------- */
            if (isset($_POST["btn_transferir"])) {
                $jugador_id = (int)($_POST["jugador_id"] ?? 0);
                $destino_id = (int)($_POST["club_destino_id"] ?? 0);

                $pdo->beginTransaction();
                [$ok, $tipo, $msg] = ejecutarTransferencia($pdo, $jugador_id, $destino_id, $usuario);
                $ok ? $pdo->commit() : $pdo->rollBack();
                $flash = [$tipo, $msg];
            }

            /* ---------- EDITAR TRANSFERENCIA (cambiar destino) ---------- */
            elseif (isset($_POST["btn_editar"])) {
                $transf_id = (int)($_POST["transferencia_id"] ?? 0);
                $nuevo_destino = (int)($_POST["club_destino_id"] ?? 0);

                $pdo->beginTransaction();

                $stmt = $pdo->prepare("SELECT jugador_id, club_origen_id, club_destino_id FROM transferencias WHERE id = ? FOR UPDATE");
                $stmt->execute([$transf_id]);
                $t = $stmt->fetch(PDO::FETCH_ASSOC);

                $stmt = $pdo->prepare("SELECT COUNT(*) FROM club WHERE id = ? AND rol = 'Club'");
                $stmt->execute([$nuevo_destino]);
                $destinoValido = (bool)$stmt->fetchColumn();

                if (!$t || !$destinoValido) {
                    $pdo->rollBack();
                    $flash = ["danger", "Transferencia o club de destino inválido."];
                } elseif ((int)$t['club_origen_id'] === $nuevo_destino) {
                    $pdo->rollBack();
                    $flash = ["warning", "El destino no puede ser igual al club de origen."];
                } elseif ((int)$t['club_destino_id'] === $nuevo_destino) {
                    $pdo->rollBack();
                    $flash = ["info", "No hubo cambios: ese ya es el club de destino."];
                } else {
                    $stmt = $pdo->prepare("SELECT club_id FROM jugadores WHERE id = ? FOR UPDATE");
                    $stmt->execute([$t['jugador_id']]);
                    $club_jugador = (int)$stmt->fetchColumn();

                    $pdo->prepare("UPDATE transferencias SET club_destino_id = ?, realizada_por = ? WHERE id = ?")
                        ->execute([$nuevo_destino, $usuario, $transf_id]);

                    if ($club_jugador === (int)$t['club_destino_id']) {
                        $pdo->prepare("UPDATE jugadores SET club_id = ? WHERE id = ?")
                            ->execute([$nuevo_destino, $t['jugador_id']]);
                        $flash = ["success", "Transferencia editada y jugador movido al nuevo club."];
                    } else {
                        $flash = ["warning", "Transferencia editada, pero el jugador ya no estaba en el club de destino anterior, por lo que su club actual no se modificó."];
                    }
                    $pdo->commit();
                }
            }

            /* ---------- ELIMINAR / ANULAR TRANSFERENCIA ---------- */
            elseif (isset($_POST["btn_eliminar"])) {
                $transf_id = (int)($_POST["transferencia_id"] ?? 0);

                $pdo->beginTransaction();

                $stmt = $pdo->prepare("SELECT jugador_id, club_origen_id, club_destino_id FROM transferencias WHERE id = ? FOR UPDATE");
                $stmt->execute([$transf_id]);
                $t = $stmt->fetch(PDO::FETCH_ASSOC);

                if (!$t) {
                    $pdo->rollBack();
                    $flash = ["danger", "La transferencia no existe."];
                } else {
                    $stmt = $pdo->prepare("SELECT club_id FROM jugadores WHERE id = ? FOR UPDATE");
                    $stmt->execute([$t['jugador_id']]);
                    $club_jugador = (int)$stmt->fetchColumn();

                    $revertido = false;
                    if ($club_jugador === (int)$t['club_destino_id']) {
                        $pdo->prepare("UPDATE jugadores SET club_id = ? WHERE id = ?")
                            ->execute([$t['club_origen_id'], $t['jugador_id']]);
                        $revertido = true;
                    }

                    $pdo->prepare("DELETE FROM transferencias WHERE id = ?")->execute([$transf_id]);
                    $pdo->commit();

                    $flash = $revertido
                        ? ["success", "Transferencia anulada: el jugador volvió a su club de origen."]
                        : ["warning", "Transferencia eliminada del historial. El jugador no volvió al club de origen porque ya se encontraba en otro club."];
                }
            }

            /* ---------- RESPONDER SOLICITUD (ADMIN) ---------- */
            elseif (isset($_POST["btn_sol_aceptar"]) || isset($_POST["btn_sol_rechazar"])) {
                $sol_id = (int)($_POST["solicitud_id"] ?? 0);
                $aceptar = isset($_POST["btn_sol_aceptar"]);

                $pdo->beginTransaction();
                $stmt = $pdo->prepare("SELECT * FROM solicitudes_transferencia WHERE id = ? FOR UPDATE");
                $stmt->execute([$sol_id]);
                $solicitud = $stmt->fetch(PDO::FETCH_ASSOC);

                if (!$solicitud || $solicitud['estado'] !== 'pendiente') {
                    $pdo->rollBack();
                    $flash = ["warning", "La solicitud no existe o ya fue respondida."];
                } elseif ($aceptar) {
                    [$ok, $tipo, $msg] = ejecutarTransferencia(
                        $pdo,
                        (int)$solicitud['jugador_id'],
                        (int)$solicitud['club_solicitante_id'],
                        $usuario,
                        (int)$solicitud['club_origen_id']
                    );

                    if (!$ok) {
                        $pdo->rollBack();
                        $flash = [$tipo, $msg];
                    } else {
                        $pdo->prepare("UPDATE solicitudes_transferencia
                                       SET estado = 'aceptada', fecha_respuesta = NOW(), respondida_por = ?
                                       WHERE id = ?")
                            ->execute([$usuario, $sol_id]);
                        $pdo->commit();
                        $flash = ["success", "Solicitud aceptada y transferencia realizada."];
                    }
                } else {
                    $pdo->prepare("UPDATE solicitudes_transferencia
                                   SET estado = 'rechazada', fecha_respuesta = NOW(), respondida_por = ?
                                   WHERE id = ?")
                        ->execute([$usuario, $sol_id]);
                    $pdo->commit();
                    $flash = ["success", "Solicitud rechazada."];
                }
            }

            /* ---------- ELIMINAR SOLICITUD ---------- */
            elseif (isset($_POST["btn_sol_eliminar"])) {
                $sol_id = (int)($_POST["solicitud_id"] ?? 0);
                $stmt = $pdo->prepare("DELETE FROM solicitudes_transferencia WHERE id = ?");
                $stmt->execute([$sol_id]);
                $flash = $stmt->rowCount()
                    ? ["success", "Solicitud eliminada."]
                    : ["danger", "La solicitud no existe."];
            }
        }

        /* ======================= CLUB ======================= */
        elseif ($id_club_actual) {
            $id_club_actual = (int)$id_club_actual;

            /* ---------- SOLICITAR FICHAJE ---------- */
            if (isset($_POST["btn_solicitar"])) {
                $jugador_id = (int)($_POST["jugador_id"] ?? 0);

                $pdo->beginTransaction();

                $stmt = $pdo->prepare("SELECT club_id FROM jugadores WHERE id = ? FOR UPDATE");
                $stmt->execute([$jugador_id]);
                $j = $stmt->fetch(PDO::FETCH_ASSOC);

                if (!$j) {
                    $pdo->rollBack();
                    $flash = ["danger", "El jugador no existe."];
                } elseif ((int)$j['club_id'] === $id_club_actual) {
                    $pdo->rollBack();
                    $flash = ["warning", "Ese jugador ya pertenece a tu club."];
                } else {
                    $stmt = $pdo->prepare("SELECT COUNT(*) FROM solicitudes_transferencia
                                           WHERE jugador_id = ? AND club_solicitante_id = ? AND estado = 'pendiente'");
                    $stmt->execute([$jugador_id, $id_club_actual]);
                    if ($stmt->fetchColumn() > 0) {
                        $pdo->rollBack();
                        $flash = ["info", "Ya enviaste una solicitud pendiente por este jugador."];
                    } else {
                        $pdo->prepare("INSERT INTO solicitudes_transferencia (jugador_id, club_solicitante_id, club_origen_id) VALUES (?, ?, ?)")
                            ->execute([$jugador_id, $id_club_actual, $j['club_id']]);
                        $pdo->commit();
                        $flash = ["success", "Solicitud enviada al club del jugador."];
                    }
                }
            }

            /* ---------- ACEPTAR SOLICITUD RECIBIDA ---------- */
            elseif (isset($_POST["btn_aceptar"])) {
                $sol_id = (int)($_POST["solicitud_id"] ?? 0);

                $pdo->beginTransaction();

                $stmt = $pdo->prepare("SELECT * FROM solicitudes_transferencia WHERE id = ? FOR UPDATE");
                $stmt->execute([$sol_id]);
                $s = $stmt->fetch(PDO::FETCH_ASSOC);

                if (!$s || (int)$s['club_origen_id'] !== $id_club_actual || $s['estado'] !== 'pendiente') {
                    $pdo->rollBack();
                    $flash = ["danger", "Solicitud inválida o ya respondida."];
                } else {
                    [$ok, $tipo, $msg] = ejecutarTransferencia($pdo, (int)$s['jugador_id'], (int)$s['club_solicitante_id'], $usuario, $id_club_actual);
                    if (!$ok) {
                        $pdo->rollBack();
                        $flash = [$tipo, $msg];
                    } else {
                        $pdo->prepare("UPDATE solicitudes_transferencia
                                       SET estado = 'aceptada', fecha_respuesta = NOW(), respondida_por = ?
                                       WHERE id = ?")->execute([$usuario, $sol_id]);
                        $pdo->commit();
                        $flash = ["success", "Solicitud aceptada: el jugador fue transferido."];
                    }
                }
            }

            /* ---------- RECHAZAR SOLICITUD RECIBIDA ---------- */
            elseif (isset($_POST["btn_rechazar"])) {
                $sol_id = (int)($_POST["solicitud_id"] ?? 0);
                $stmt = $pdo->prepare("UPDATE solicitudes_transferencia
                                       SET estado = 'rechazada', fecha_respuesta = NOW(), respondida_por = ?
                                       WHERE id = ? AND club_origen_id = ? AND estado = 'pendiente'");
                $stmt->execute([$usuario, $sol_id, $id_club_actual]);
                $flash = $stmt->rowCount()
                    ? ["success", "Solicitud rechazada."]
                    : ["danger", "Solicitud inválida o ya respondida."];
            }

            /* ---------- CANCELAR SOLICITUD ENVIADA ---------- */
            elseif (isset($_POST["btn_cancelar"])) {
                $sol_id = (int)($_POST["solicitud_id"] ?? 0);
                $stmt = $pdo->prepare("DELETE FROM solicitudes_transferencia
                                       WHERE id = ? AND club_solicitante_id = ? AND estado = 'pendiente'");
                $stmt->execute([$sol_id, $id_club_actual]);
                $flash = $stmt->rowCount()
                    ? ["success", "Solicitud cancelada."]
                    : ["danger", "No se pudo cancelar la solicitud."];
            }
        }
    } catch (PDOException $e) {
        if ($pdo->inTransaction()) $pdo->rollBack();
        $flash = ["danger", "Error en la operación: " . $e->getMessage()];
    }

    $_SESSION['flash'] = $flash;
    $qs = isset($_GET['cat']) && (int)$_GET['cat'] > 0 ? "?cat=" . (int)$_GET['cat'] : "";
    header("Location: transferencias.php" . $qs);
    exit();
}

/* ---------------------------------------------------------
 * DATOS
 * --------------------------------------------------------- */
$cat_filtro = (int)($_GET['cat'] ?? 0);

$jugadores_transferibles = [];
$jugadores_admin = [];
$clubes = [];
$categorias = [];
$historial = [];
$solicitudes_admin = [];
$solicitudes_recibidas = [];
$solicitudes_enviadas = [];

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
                                        FROM jugadores j
                                        INNER JOIN club c ON c.id = j.club_id
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
        if ($cat_filtro > 0) {
            $sql .= " AND j.categoria_id = :cat";
            $params['cat'] = $cat_filtro;
        }
        $sql .= " ORDER BY cat.nombre ASC, c.nombre ASC, j.apellido ASC, j.nombre ASC";
        $stmt = $pdo->prepare($sql);
        $stmt->execute($params);
        $jugadores_transferibles = $stmt->fetchAll(PDO::FETCH_ASSOC);

        $stmt = $pdo->prepare($baseSol . " WHERE s.club_origen_id = :club" . $ordenSol);
        $stmt->execute(['club' => $id_club_actual]);
        $solicitudes_recibidas = $stmt->fetchAll(PDO::FETCH_ASSOC);

        $stmt = $pdo->prepare($baseSol . " WHERE s.club_solicitante_id = :club" . $ordenSol);
        $stmt->execute(['club' => $id_club_actual]);
        $solicitudes_enviadas = $stmt->fetchAll(PDO::FETCH_ASSOC);
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
    if ($cat_filtro > 0) {
        $sql .= " WHERE j.categoria_id = :cat";
        $params['cat'] = $cat_filtro;
    }
    $sql .= " ORDER BY t.fecha DESC, t.id DESC LIMIT 50";
    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);
    $historial = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    $error_db = "Error al cargar los datos: " . $e->getMessage();
}

$pendientes_recibidas = count(array_filter($solicitudes_recibidas, fn($s) => $s['estado'] === 'pendiente'));
$pendientes_admin = count(array_filter($solicitudes_admin, fn($s) => $s['estado'] === 'pendiente'));
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="icon" href="../img/logo.png" type="image/png">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/@tabler/icons-webfont@latest/dist/tabler-icons.min.css">
    <link href="../estilos.css" rel="stylesheet">
    <style>
        /* Tablas que ocupan todo el ancho de la tarjeta, sin scroll horizontal */
        .card { overflow: visible; }
        .tabla-fit { table-layout: fixed; width: 100%; font-size: .85rem; margin-bottom: 0; }
        .tabla-fit th,
        .tabla-fit td { white-space: normal; overflow-wrap: anywhere; word-break: break-word; padding: .5rem .4rem; vertical-align: middle; }
        .tabla-fit .badge { white-space: normal; text-align: left; font-size: .75rem; }
        .tabla-fit .btn-sm { padding: .2rem .45rem; }
        .tabla-fit .acciones { display: flex; flex-wrap: wrap; gap: .25rem; justify-content: flex-end; }
        .tabla-fit .acciones form { margin: 0; }
        .tabla-solicitudes-wrap { width: 100%; overflow-x: auto; }
        .tabla-solicitudes { min-width: 900px; }
        .tabla-solicitudes .acciones { flex-wrap: nowrap; }
        .tabla-solicitudes .acciones .btn { min-width: 2rem; }
        .pagina-transferencias { min-width: 0; }
        .pagina-transferencias .tabla-wrap { width: 100%; overflow-x: auto; border-radius: .5rem; }
        .pagina-transferencias .tabla-wrap > .tabla-fit { min-width: 650px; }
        .pagina-transferencias .acciones .btn { width: auto; min-width: 2rem; }
        .pagina-transferencias .acciones form { flex: 0 0 auto; }
        .pagina-transferencias .acciones form .btn { width: auto; }
        .pagina-transferencias .card { border-radius: 1rem; }
        .pagina-transferencias .form-label { color: inherit; }
        @media (max-width: 767.98px) {
            .pagina-transferencias {
                width: calc(100% - 1.5rem);
                max-width: 540px;
                padding: .75rem 0 1.5rem;
            }
            .pagina-transferencias .home-hero {
                padding: 1.5rem 1rem;
                margin: .75rem 0 1.25rem !important;
                gap: 1rem;
                text-align: center;
            }
            .pagina-transferencias .home-hero h1 { font-size: 1.8rem; }
            .pagina-transferencias .home-hero-logo { width: 76px; }
            .pagina-transferencias .card {
                padding: .5rem !important;
                margin-bottom: 1rem !important;
            }
            .pagina-transferencias .card-body { padding: .75rem; }
            .pagina-transferencias .card-title {
                margin-bottom: 1rem !important;
                text-align: center;
                line-height: 1.35;
            }
            .pagina-transferencias .form-label { font-size: .95rem; }
            .pagina-transferencias form.row { text-align: center; }
            .pagina-transferencias form.d-flex {
                justify-content: center;
                flex-wrap: wrap;
                padding: .75rem;
                border-radius: .75rem;
                background: rgba(255, 255, 255, .04);
            }
            .pagina-transferencias form.d-flex .form-select { max-width: 100%; }
            .pagina-transferencias .tabla-wrap > .tabla-fit { min-width: 680px; }
            .pagina-transferencias .tabla-solicitudes { min-width: 900px; }
            .pagina-transferencias .acciones { gap: .5rem; }
            .pagina-transferencias .acciones .btn { min-height: 2.25rem; }
            .pagina-transferencias #btn_mostrar_jugadores { width: auto; }
        }
        @media (max-width: 380px) {
            .pagina-transferencias { width: calc(100% - 1rem); }
            .pagina-transferencias .home-hero h1 { font-size: 1.55rem; }
            .pagina-transferencias .card-body { padding: .5rem; }
        }
    </style>
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

    <?php if ($flash): ?>
        <div class="alert alert-<?= htmlspecialchars($flash[0]) ?>" role="alert"><?= htmlspecialchars($flash[1]) ?></div>
    <?php endif; ?>

    <?php if (isset($error_db)): ?>
        <div class="alert alert-danger" role="alert"><?= htmlspecialchars($error_db) ?></div>
    <?php endif; ?>

    <?php if ($esAdmin): ?>
        <!-- NUEVA TRANSFERENCIA -->
        <div class="card shadow-sm border-0 bg-dark text-white p-3 mb-4">
            <div class="card-body">
                <h3 class="card-title mb-4"><i class="ti ti-transfer"></i> Nueva transferencia</h3>
                <form method="POST" class="row g-3 align-items-end">
                    <div class="col-md-2">
                        <label for="filtro_categoria" class="form-label">Categoría</label>
                        <select id="filtro_categoria" class="form-select">
                            <option value="0">Todas</option>
                            <?php foreach ($categorias as $c): ?>
                                <option value="<?= (int)$c['id'] ?>"><?= htmlspecialchars($c['nombre']) ?></option>
                            <?php endforeach; ?>
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
                            <?php foreach ($clubes as $c): ?>
                                <option value="<?= (int)$c['id'] ?>"><?= htmlspecialchars($c['nombre']) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="col-md-3">
                        <button type="submit" name="btn_transferir" value="1" class="btn btn-success w-100">
                            <i class="ti ti-arrows-exchange"></i> Transferir
                        </button>
                    </div>
                </form>
            </div>
        </div>

        <!-- SOLICITUDES (ADMIN) -->
        <div class="card shadow-sm border-0 bg-dark text-white p-3 mb-4">
            <div class="card-body">
                <h3 class="card-title mb-4">
                    <i class="ti ti-inbox"></i> Solicitudes de Transferencia
                    <?php if ($pendientes_admin): ?>
                        <span class="badge bg-warning text-dark"><?= $pendientes_admin ?> pendiente<?= $pendientes_admin > 1 ? 's' : '' ?></span>
                    <?php endif; ?>
                </h3>
                <div class="tabla-solicitudes-wrap">
                <table class="table table-dark table-hover align-middle tabla-fit tabla-solicitudes">
                    <thead>
                        <tr>
                            <th style="width:12%">Fecha</th>
                            <th style="width:17%">Jugador</th>
                            <th style="width:9%">Categoría</th>
                            <th style="width:15%">Club solicitante</th>
                            <th style="width:15%">Club actual</th>
                            <th style="width:10%">Estado</th>
                            <th style="width:22%" class="text-end">Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if ($solicitudes_admin): foreach ($solicitudes_admin as $s): ?>
                            <tr>
                                <td><?= htmlspecialchars(fmtFecha($s['fecha'])) ?></td>
                                <td><?= htmlspecialchars($s['nombre'] . ' ' . $s['apellido']) ?></td>
                                <td><span class="badge bg-info text-dark"><?= htmlspecialchars($s['categoria']) ?></span></td>
                                <td><span class="badge bg-success"><?= htmlspecialchars($s['solicitante']) ?></span></td>
                                <td><span class="badge bg-secondary"><?= htmlspecialchars($s['origen']) ?></span></td>
                                <td><?= badgeEstado($s['estado']) ?></td>
                                <td>
                                    <div class="acciones">
                                        <button type="button" class="btn btn-info btn-sm" title="Ver"
                                                data-bs-toggle="modal" data-bs-target="#modalVerSol"
                                                data-jugador="<?= htmlspecialchars($s['nombre'] . ' ' . $s['apellido']) ?>"
                                                data-ci="<?= htmlspecialchars($s['ci']) ?>"
                                                data-cat="<?= htmlspecialchars($s['categoria']) ?>"
                                                data-solicitante="<?= htmlspecialchars($s['solicitante']) ?>"
                                                data-origen="<?= htmlspecialchars($s['origen']) ?>"
                                                data-estado="<?= htmlspecialchars(ucfirst($s['estado'])) ?>"
                                                data-fecha="<?= htmlspecialchars(fmtFecha($s['fecha'])) ?>"
                                                data-respuesta="<?= htmlspecialchars(fmtFecha($s['fecha_respuesta'])) ?>"
                                                data-por="<?= htmlspecialchars($s['respondida_por'] ?? '—') ?>">
                                            <i class="ti ti-eye"></i>
                                        </button>
                                        <?php if ($s['estado'] === 'pendiente'): ?>
                                            <form method="POST" onsubmit="return confirm('¿Aceptar la solicitud y transferir el jugador al club solicitante?');">
                                                <input type="hidden" name="solicitud_id" value="<?= (int)$s['id'] ?>">
                                                <button type="submit" name="btn_sol_aceptar" value="1" class="btn btn-success btn-sm" title="Aceptar solicitud" aria-label="Aceptar solicitud">
                                                    <i class="ti ti-check"></i>
                                                </button>
                                            </form>
                                            <form method="POST" onsubmit="return confirm('¿Rechazar esta solicitud?');">
                                                <input type="hidden" name="solicitud_id" value="<?= (int)$s['id'] ?>">
                                                <button type="submit" name="btn_sol_rechazar" value="1" class="btn btn-danger btn-sm" title="Rechazar solicitud" aria-label="Rechazar solicitud">
                                                    <i class="ti ti-x"></i>
                                                </button>
                                            </form>
                                        <?php endif; ?>
                                        <form method="POST" onsubmit="return confirm('¿Eliminar esta solicitud?');">
                                            <input type="hidden" name="solicitud_id" value="<?= (int)$s['id'] ?>">
                                            <button type="submit" name="btn_sol_eliminar" value="1" class="btn btn-danger btn-sm" title="Eliminar">
                                                <i class="ti ti-trash"></i>
                                            </button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        <?php endforeach; else: ?>
                            <tr><td colspan="7" class="text-center text-muted py-4">No hay solicitudes de transferencia.</td></tr>
                        <?php endif; ?>
                    </tbody>
                </table>
                </div>
            </div>
        </div>



    <?php else: ?>
        <!-- MERCADO DE PASES -->
        <div class="card shadow-sm border-0 bg-dark text-white p-3 mb-4">
            <div class="card-body">
                <h3 class="card-title mb-4">Jugadores de otros clubes</h3>
                <?php filtroCategoria($categorias, $cat_filtro); ?>
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
                    <thead>
                        <tr>
                            <th style="width:18%">Cédula (CI)</th>
                            <th style="width:32%">Jugador</th>
                            <th style="width:20%">Venc. carnet</th>
                            <th style="width:30%" class="text-end">Acción</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if ($jugadores_transferibles): foreach ($jugadores_transferibles as $indice => $jugador): ?>
                            <tr data-jugador
                                data-nombre="<?= htmlspecialchars($jugador['nombre'] . ' ' . $jugador['apellido'], ENT_QUOTES, 'UTF-8') ?>"
                                data-ci="<?= htmlspecialchars($jugador['ci'], ENT_QUOTES, 'UTF-8') ?>"
                                data-categoria="<?= htmlspecialchars($jugador['categoria'], ENT_QUOTES, 'UTF-8') ?>"
                                data-club="<?= htmlspecialchars($jugador['club_actual'], ENT_QUOTES, 'UTF-8') ?>"
                                <?= $indice >= 3 ? 'hidden' : '' ?>>
                                <td><?= htmlspecialchars($jugador['ci']) ?></td>
                                <td><?= htmlspecialchars($jugador['nombre'] . ' ' . $jugador['apellido']) ?></td>
                                <td><?= htmlspecialchars($jugador['carnet_vencimiento'] ?: 'Sin fecha registrada') ?></td>
                                <td>
                                    <div class="acciones">
                                        <?php if ((int)$jugador['ya_solicitado'] > 0): ?>
                                            <button type="button" class="btn btn-outline-light btn-sm" disabled>
                                                <i class="ti ti-clock"></i> Solicitud enviada
                                            </button>
                                        <?php else: ?>
                                            <form method="POST">
                                                <input type="hidden" name="jugador_id" value="<?= (int)$jugador['id'] ?>">
                                                <button type="submit" name="btn_solicitar" value="1" class="btn btn-success btn-sm">
                                                    <i class="ti ti-user-plus"></i> Solicitar Fichaje
                                                </button>
                                            </form>
                                        <?php endif; ?>
                                    </div>
                                </td>
                            </tr>
                        <?php endforeach; else: ?>
                            <tr>
                                <td colspan="4" class="text-center text-muted py-4">
                                    No hay jugadores de otros clubes disponibles en este momento.
                                </td>
                            </tr>
                        <?php endif; ?>
                        <?php if ($jugadores_transferibles): ?>
                            <tr id="sin_resultados_filtro" hidden>
                                <td colspan="4" class="text-center text-muted py-4">No se encontraron jugadores con esos filtros.</td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
                </div>
                <?php if (count($jugadores_transferibles) > 3): ?>
                    <div class="text-center mt-3">
                        <button type="button" id="btn_mostrar_jugadores" class="btn btn-outline-light btn-sm">
                            Mostrar más
                        </button>
                    </div>
                <?php endif; ?>
            </div>
        </div>

        <!-- SOLICITUDES RECIBIDAS -->
        <div class="card shadow-sm border-0 bg-dark text-white p-3 mb-4">
            <div class="card-body">
                <h3 class="card-title mb-4">
                    <i class="ti ti-inbox"></i> Solicitudes de Transferencia
                    <?php if ($pendientes_recibidas): ?>
                        <span class="badge bg-warning text-dark"><?= $pendientes_recibidas ?> pendiente<?= $pendientes_recibidas > 1 ? 's' : '' ?></span>
                    <?php endif; ?>
                </h3>
                <p class="text-white-50 small">Otros clubes quieren fichar a jugadores de tu club.</p>
                <div class="tabla-wrap">
                <table class="table table-dark table-hover align-middle tabla-fit">
                    <thead>
                        <tr>
                            <th style="width:13%">Fecha</th>
                            <th style="width:22%">Jugador</th>
                            <th style="width:12%">CI</th>
                            <th style="width:10%">Categoría</th>
                            <th style="width:18%">Club solicitante</th>
                            <th style="width:11%">Estado</th>
                            <th style="width:14%" class="text-end">Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if ($solicitudes_recibidas): foreach ($solicitudes_recibidas as $s): ?>
                            <tr>
                                <td><?= htmlspecialchars(fmtFecha($s['fecha'])) ?></td>
                                <td><?= htmlspecialchars($s['nombre'] . ' ' . $s['apellido']) ?></td>
                                <td><?= htmlspecialchars($s['ci']) ?></td>
                                <td><span class="badge bg-info text-dark"><?= htmlspecialchars($s['categoria']) ?></span></td>
                                <td><span class="badge bg-success"><?= htmlspecialchars($s['solicitante']) ?></span></td>
                                <td><?= badgeEstado($s['estado']) ?></td>
                                <td>
                                    <div class="acciones">
                                        <?php if ($s['estado'] === 'pendiente'): ?>
                                            <form method="POST" onsubmit="return confirm('¿Aceptar? El jugador pasará al club solicitante.');">
                                                <input type="hidden" name="solicitud_id" value="<?= (int)$s['id'] ?>">
                                                <button type="submit" name="btn_aceptar" value="1" class="btn btn-success btn-sm" title="Aceptar">
                                                    <i class="ti ti-check"></i>
                                                </button>
                                            </form>
                                            <form method="POST" onsubmit="return confirm('¿Rechazar esta solicitud?');">
                                                <input type="hidden" name="solicitud_id" value="<?= (int)$s['id'] ?>">
                                                <button type="submit" name="btn_rechazar" value="1" class="btn btn-danger btn-sm" title="Rechazar">
                                                    <i class="ti ti-x"></i>
                                                </button>
                                            </form>
                                        <?php else: ?>
                                            <span class="text-muted small"><?= htmlspecialchars(fmtFecha($s['fecha_respuesta'])) ?></span>
                                        <?php endif; ?>
                                    </div>
                                </td>
                            </tr>
                        <?php endforeach; else: ?>
                            <tr><td colspan="7" class="text-center text-muted py-4">No recibiste solicitudes de transferencia.</td></tr>
                        <?php endif; ?>
                    </tbody>
                </table>
                </div>
            </div>
        </div>

        <!-- SOLICITUDES ENVIADAS -->
        <div class="card shadow-sm border-0 bg-dark text-white p-3 mb-4">
            <div class="card-body">
                <h3 class="card-title mb-4"><i class="ti ti-send"></i> Mis solicitudes enviadas</h3>
                <div class="tabla-wrap">
                <table class="table table-dark table-hover align-middle tabla-fit">
                    <thead>
                        <tr>
                            <th style="width:15%">Fecha</th>
                            <th style="width:25%">Jugador</th>
                            <th style="width:11%">Categoría</th>
                            <th style="width:21%">Club actual</th>
                            <th style="width:14%">Estado</th>
                            <th style="width:14%" class="text-end">Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if ($solicitudes_enviadas): foreach ($solicitudes_enviadas as $s): ?>
                            <tr>
                                <td><?= htmlspecialchars(fmtFecha($s['fecha'])) ?></td>
                                <td><?= htmlspecialchars($s['nombre'] . ' ' . $s['apellido']) ?></td>
                                <td><span class="badge bg-info text-dark"><?= htmlspecialchars($s['categoria']) ?></span></td>
                                <td><span class="badge bg-secondary"><?= htmlspecialchars($s['origen']) ?></span></td>
                                <td><?= badgeEstado($s['estado']) ?></td>
                                <td>
                                    <div class="acciones">
                                        <?php if ($s['estado'] === 'pendiente'): ?>
                                            <form method="POST" onsubmit="return confirm('¿Cancelar esta solicitud?');">
                                                <input type="hidden" name="solicitud_id" value="<?= (int)$s['id'] ?>">
                                                <button type="submit" name="btn_cancelar" value="1" class="btn btn-outline-danger btn-sm" title="Cancelar">
                                                    <i class="ti ti-trash"></i> Cancelar
                                                </button>
                                            </form>
                                        <?php endif; ?>
                                    </div>
                                </td>
                            </tr>
                        <?php endforeach; else: ?>
                            <tr><td colspan="6" class="text-center text-muted py-4">Todavía no enviaste solicitudes.</td></tr>
                        <?php endif; ?>
                    </tbody>
                </table>
                </div>
            </div>
        </div>
    <?php endif; ?>

    <!-- HISTORIAL -->
    <div class="card shadow-sm border-0 bg-dark text-white p-3">
        <div class="card-body">
            <h3 class="card-title mb-4"><i class="ti ti-history"></i> Historial de transferencias</h3>
            <?php if ($esAdmin) filtroCategoria($categorias, $cat_filtro); ?>
            <div class="tabla-wrap">
            <table class="table table-dark table-hover align-middle tabla-fit">
                <thead>
                    <tr>
                        <th style="width:<?= $esAdmin ? 12 : 14 ?>%">Fecha</th>
                        <th style="width:<?= $esAdmin ? 18 : 22 ?>%">Jugador</th>
                        <th style="width:<?= $esAdmin ? 10 : 12 ?>%">CI</th>
                        <th style="width:<?= $esAdmin ? 8 : 10 ?>%">Cat.</th>
                        <th style="width:<?= $esAdmin ? 17 : 19 ?>%">Club origen</th>
                        <th style="width:4%"></th>
                        <th style="width:<?= $esAdmin ? 17 : 19 ?>%">Club destino</th>
                        <?php if ($esAdmin): ?><th style="width:14%" class="text-end">Acciones</th><?php endif; ?>
                    </tr>
                </thead>
                <tbody>
                    <?php if ($historial): foreach ($historial as $h): ?>
                        <tr>
                            <td><?= htmlspecialchars(fmtFecha($h['fecha'])) ?></td>
                            <td><?= htmlspecialchars($h['nombre'] . ' ' . $h['apellido']) ?></td>
                            <td><?= htmlspecialchars($h['ci']) ?></td>
                            <td><span class="badge bg-info text-dark"><?= htmlspecialchars($h['categoria']) ?></span></td>
                            <td><span class="badge bg-secondary"><?= htmlspecialchars($h['origen']) ?></span></td>
                            <td class="text-center"><i class="ti ti-arrow-right"></i></td>
                            <td><span class="badge bg-success"><?= htmlspecialchars($h['destino']) ?></span></td>
                            <?php if ($esAdmin): ?>
                                <td>
                                    <div class="acciones">
                                        <button type="button" class="btn btn-warning btn-sm"
                                                data-bs-toggle="modal" data-bs-target="#modalEditar"
                                                data-id="<?= (int)$h['id'] ?>"
                                                data-jugador="<?= htmlspecialchars($h['nombre'] . ' ' . $h['apellido']) ?>"
                                                data-origen-id="<?= (int)$h['club_origen_id'] ?>"
                                                data-destino-id="<?= (int)$h['club_destino_id'] ?>"
                                                title="Editar">
                                            <i class="ti ti-edit"></i>
                                        </button>
                                        <form method="POST"
                                              onsubmit="return confirm('¿Anular esta transferencia? Si el jugador sigue en el club de destino, volverá al club de origen.');">
                                            <input type="hidden" name="transferencia_id" value="<?= (int)$h['id'] ?>">
                                            <button type="submit" name="btn_eliminar" value="1" class="btn btn-danger btn-sm" title="Eliminar / anular">
                                                <i class="ti ti-trash"></i>
                                            </button>
                                        </form>
                                    </div>
                                </td>
                            <?php endif; ?>
                        </tr>
                    <?php endforeach; else: ?>
                        <tr>
                            <td colspan="<?= $esAdmin ? 8 : 7 ?>" class="text-center text-muted py-4">Todavía no se registraron transferencias.</td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
            </div>
        </div>
    </div>
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
                    <?php foreach ($clubes as $c): ?>
                        <option value="<?= (int)$c['id'] ?>"><?= htmlspecialchars($c['nombre']) ?></option>
                    <?php endforeach; ?>
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
                    <dt class="col-5">Jugador</dt><dd class="col-7" id="ver_jugador"></dd>
                    <dt class="col-5">CI</dt><dd class="col-7" id="ver_ci"></dd>
                    <dt class="col-5">Categoría</dt><dd class="col-7" id="ver_cat"></dd>
                    <dt class="col-5">Club solicitante</dt><dd class="col-7" id="ver_solicitante"></dd>
                    <dt class="col-5">Club actual</dt><dd class="col-7" id="ver_origen"></dd>
                    <dt class="col-5">Estado</dt><dd class="col-7" id="ver_estado"></dd>
                    <dt class="col-5">Fecha de solicitud</dt><dd class="col-7" id="ver_fecha"></dd>
                    <dt class="col-5">Fecha de respuesta</dt><dd class="col-7" id="ver_respuesta"></dd>
                    <dt class="col-5">Respondida por</dt><dd class="col-7" id="ver_por"></dd>
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
const filasJugadores = [...document.querySelectorAll('tr[data-jugador]')];
const cuerpoJugadores = filasJugadores[0]?.parentElement;
const filtroNombreJugador = document.getElementById('filtro_nombre_jugador');
const filtroCiJugador = document.getElementById('filtro_ci_jugador');
const botonMostrarJugadores = document.getElementById('btn_mostrar_jugadores');
const sinResultadosFiltro = document.getElementById('sin_resultados_filtro');
let mostrarTodosJugadores = false;

if (cuerpoJugadores && filasJugadores.length) {
    function actualizarJugadores() {
        const nombreBuscado = filtroNombreJugador.value.trim().toLocaleLowerCase('es');
        const ciBuscada = filtroCiJugador.value.trim().toLocaleLowerCase('es');
        const filtrados = filasJugadores.filter(fila =>
            fila.dataset.nombre.toLocaleLowerCase('es').includes(nombreBuscado)
            && fila.dataset.ci.toLocaleLowerCase('es').includes(ciBuscada)
        );
        const visibles = mostrarTodosJugadores ? filtrados : filtrados.slice(0, 3);

        cuerpoJugadores.querySelectorAll('.grupo-jugadores').forEach(grupo => grupo.remove());
        filasJugadores.forEach(fila => { fila.hidden = true; });

        let grupoActual = '';
        visibles.forEach(fila => {
            const grupo = `${fila.dataset.categoria}\u0000${fila.dataset.club}`;
            if (grupo !== grupoActual) {
                grupoActual = grupo;
                const encabezado = document.createElement('tr');
                encabezado.className = 'grupo-jugadores';
                const titulo = document.createElement('th');
                titulo.colSpan = 4;
                titulo.scope = 'colgroup';
                titulo.textContent = `Categoría ${fila.dataset.categoria} · ${fila.dataset.club}`;
                encabezado.appendChild(titulo);
                cuerpoJugadores.insertBefore(encabezado, fila);
            }
            fila.hidden = false;
        });

        if (sinResultadosFiltro) {
            sinResultadosFiltro.hidden = filtrados.length !== 0;
        }
        if (botonMostrarJugadores) {
            botonMostrarJugadores.hidden = filtrados.length <= 3;
            botonMostrarJugadores.textContent = mostrarTodosJugadores
                ? 'Mostrar menos'
                : `Mostrar más (${filtrados.length - 3})`;
        }
    }

    filtroNombreJugador.addEventListener('input', () => {
        mostrarTodosJugadores = false;
        actualizarJugadores();
    });
    filtroCiJugador.addEventListener('input', () => {
        mostrarTodosJugadores = false;
        actualizarJugadores();
    });
    botonMostrarJugadores?.addEventListener('click', () => {
        mostrarTodosJugadores = !mostrarTodosJugadores;
        actualizarJugadores();
    });
    actualizarJugadores();
}
</script>
<?php endif; ?>
<?php if ($esAdmin): ?>
<script>
const jugadores = <?= json_encode($jugadores_admin, JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT) ?>;

const selCategoria = document.getElementById('filtro_categoria');
const selJugador   = document.getElementById('jugador_id');
const selDestino   = document.getElementById('club_destino_id');

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

    if (lista.length === 0) {
        const vacio = new Option('No hay jugadores en esta categoría', '', false, false);
        vacio.disabled = true;
        selJugador.add(vacio);
    }
    actualizarDestinos();
}

function actualizarDestinos() {
    const opt = selJugador.selectedOptions[0];
    const clubActual = opt ? opt.dataset.club : undefined;
    [...selDestino.options].forEach(o => {
        o.disabled = o.value !== '' && o.value === clubActual;
    });
    if (selDestino.selectedOptions[0]?.disabled) selDestino.value = '';
}

selCategoria.addEventListener('change', renderJugadores);
selJugador.addEventListener('change', actualizarDestinos);
renderJugadores();

// Modal editar transferencia
document.getElementById('modalEditar').addEventListener('show.bs.modal', e => {
    const b = e.relatedTarget;
    document.getElementById('edit_transferencia_id').value = b.dataset.id;
    document.getElementById('edit_jugador').textContent = b.dataset.jugador;

    const sel = document.getElementById('edit_club_destino');
    [...sel.options].forEach(o => { o.disabled = o.value === b.dataset.origenId; });
    sel.value = b.dataset.destinoId;
});

// Modal ver solicitud
document.getElementById('modalVerSol').addEventListener('show.bs.modal', e => {
    const b = e.relatedTarget;
    ['jugador', 'ci', 'cat', 'solicitante', 'origen', 'estado', 'fecha', 'respuesta', 'por'].forEach(k => {
        document.getElementById('ver_' + k).textContent = b.dataset[k] || '—';
    });
});

</script>
<?php endif; ?>
</body>
</html>