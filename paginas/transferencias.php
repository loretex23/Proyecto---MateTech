<?php
require_once "login/auth.php";
include '../sql/basededatos.php';

$esAdmin = ($_SESSION["Rol"] ?? "") === "Admin";
$id_club_actual = isset($_SESSION['ClubID']) ? (int)$_SESSION['ClubID'] : null;
$flash = $_SESSION['flash'] ?? null;
unset($_SESSION['flash']);

// Helpers y Funciones (pueden mantenerse aquí o moverse a un helpers.php global)
function q(PDO $pdo, string $sql, array $p = []): PDOStatement { $s = $pdo->prepare($sql); $s->execute($p); return $s; }
function h($v): string { return htmlspecialchars((string) $v, ENT_QUOTES, 'UTF-8'); }
function badge(string $c, string $t): string { return "<span class=\"badge bg-$c\">" . h($t) . '<span>'; }
function badgeEstado(string $e): string {
    [$c, $t] = ['pendiente' => ['warning text-dark', 'Pendiente'], 'aceptada' => ['success', 'Aceptada'], 'rechazada' => ['danger', 'Rechazada']][$e] ?? ['secondary', $e];
    return badge($c, $t);
}
function fmtFecha(?string $d): string { return $d ? date("d/m/Y H:i", strtotime($d)) : '-'; }
function clubValido(PDO $pdo, int $id): bool { return (bool)q($pdo, "SELECT COUNT(*) FROM club WHERE id = ? AND rol = 'Club'", [$id])->fetchColumn(); }
function clubDeJugador(PDO $pdo, int $id) { return q($pdo, "SELECT club_id FROM jugadores WHERE id = ? FOR UPDATE", [$id])->fetchColumn(); }
function btnForm(string $name, int $id, string $cls, string $icon, string $title, string $confirm = '', string $label = '', string $field = 'solicitud_id'): string {
    $c = $confirm ? ' onsubmit="return confirm(\'' . $confirm . '\');"' : '';
    return "<form method=\"POST\"$c><input type=\"hidden\" name=\"$field\" value=\"$id\"><button type=\"submit\" name=\"$name\" value=\"1\" class=\"btn $cls btn-sm\" title=\"$title\" aria-label=\"$title\"><i class=\"ti ti-$icon\"></i>$label</button></form>";
}
function thead(array $cols): string {
    $o = '<thead><tr>';
    foreach ($cols as [$w, $t, $c]) $o .= "<th style=\"width:$w%\"" . ($c ? " class=\"$c\"" : '') . ">$t</th>";
    return $o . '</tr></thead>';
}
function vacio(int $cols, string $msg): string { return "<tr><td colspan=\"$cols\" class=\"text-center text-muted py-4\">$msg</td></tr>"; }
function pendTitulo(int $n): string { return $n ? ' <span class="badge bg-warning text-dark">' . $n . ' pendiente' . ($n > 1 ? 's' : '') . '</span>' : ''; }
function cardIni(string $titulo, string $mb = 'mb-4'): void { echo "<div class=\"card shadow-sm border-0 bg-dark text-white p-3 $mb\"><div class=\"card-body\"><h3 class=\"card-title mb-4\">$titulo</h3>"; }
function cardFin(): void { echo '</div></div>'; }
function filtroCategoria(array $categorias, int $actual): void { ?>
    <form method="GET" class="d-flex align-items-center gap-2 mb-3">
        <label for="cat" class="form-label mb-0"><i class="ti ti-filter"></i> Categoria</label>
        <select name="cat" id="cat" class="form-select form-select-sm w-auto" onchange="this.form.submit()">
            <option value="0">Todas</option>
            <?php foreach ($categorias as $c): ?>
                <option value="<?= (int)$c['id'] ?>" <?= $actual === (int)$c['id'] ? 'selected' : '' ?>><?= h($c['nombre']) ?></option>
            <?php endforeach; ?>
        </select>
        <?php if ($actual > 0): ?><a href="transferencias.php" class="btn btn-outline-light btn-sm">Limpiar</a><?php endif; ?>
    </form>
<?php }

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


include '../acciones/transfer_acc.php';

// CARGA DE DATOS
$cat_filtro = (int)($_GET['cat'] ?? 0);
$jugadores_transferibles = $jugadores_admin = $clubes = $categorias = $historial = [];
$solicitudes_admin = $solicitudes_recibidas = $solicitudes_enviadas = [];

$baseSol = "SELECT s.id, s.fecha, s.estado, s.fecha_respuesta, s.respondida_por, s.club_origen_id, s.jugador_id, s.club_solicitante_id, j.ci, j.nombre, j.apellido, cat.nombre AS categoria, cs.nombre AS solicitante, co.nombre AS origen FROM solicitudes_transferencia s INNER JOIN jugadores j ON j.id = s.jugador_id INNER JOIN categorias cat ON cat.id = j.categoria_id INNER JOIN club cs ON cs.id = s.club_solicitante_id INNER JOIN club co ON co.id = s.club_origen_id";
$ordenSol = " ORDER BY FIELD(s.estado, 'pendiente', 'aceptada', 'rechazada'), s.fecha DESC, s.id DESC LIMIT 100";

try {
    $categorias = $pdo->query("SELECT id, nombre FROM categorias ORDER BY nombre ASC")->fetchAll(PDO::FETCH_ASSOC);
    if ($esAdmin) {
        $jugadores_admin = $pdo->query("SELECT j.id, j.ci, j.nombre, j.apellido, j.club_id, j.categoria_id, c.nombre AS club FROM jugadores j INNER JOIN club c ON c.id = j.club_id ORDER BY c.nombre ASC, j.apellido ASC, j.nombre ASC")->fetchAll(PDO::FETCH_ASSOC);
        $clubes = $pdo->query("SELECT id, nombre FROM club WHERE rol = 'Club' ORDER BY nombre ASC")->fetchAll(PDO::FETCH_ASSOC);
        $solicitudes_admin = $pdo->query($baseSol . $ordenSol)->fetchAll(PDO::FETCH_ASSOC);
    } elseif ($id_club_actual) {
        $sql = "SELECT j.id, j.ci, j.nombre, j.apellido, j.carnet_vencimiento, c.nombre AS club_actual, cat.nombre AS categoria, (SELECT COUNT(*) FROM solicitudes_transferencia s WHERE s.jugador_id = j.id AND s.club_solicitante_id = :club2 AND s.estado = 'pendiente') AS ya_solicitado FROM jugadores j INNER JOIN club c ON j.club_id = c.id INNER JOIN categorias cat ON cat.id = j.categoria_id WHERE j.club_id != :club";
        $params = ['club' => $id_club_actual, 'club2' => $id_club_actual];
        if ($cat_filtro > 0) { $sql .= " AND j.categoria_id = :cat"; $params['cat'] = $cat_filtro; }
        $sql .= " ORDER BY cat.nombre ASC, c.nombre ASC, j.apellido ASC, j.nombre ASC";
        $jugadores_transferibles = q($pdo, $sql, $params)->fetchAll(PDO::FETCH_ASSOC);
        $solicitudes_recibidas = q($pdo, $baseSol . " WHERE s.club_origen_id = :club" . $ordenSol, ['club' => $id_club_actual])->fetchAll(PDO::FETCH_ASSOC);
        $solicitudes_enviadas = q($pdo, $baseSol . " WHERE s.club_solicitante_id = :club" . $ordenSol, ['club' => $id_club_actual])->fetchAll(PDO::FETCH_ASSOC);
    }

    $sql = "SELECT t.id, t.fecha, t.jugador_id, t.club_origen_id, t.club_destino_id, j.ci, j.nombre, j.apellido, cat.nombre AS categoria, co.nombre AS origen, cd.nombre AS destino FROM transferencias t INNER JOIN jugadores j ON j.id = t.jugador_id INNER JOIN categorias cat ON cat.id = j.categoria_id INNER JOIN club co ON co.id = t.club_origen_id INNER JOIN club cd ON cd.id = t.club_destino_id";
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
    <link href="../estilos.css" rel="stylesheet">
    <title>LeagueFlow - Transferencias</title>
</head>
<body class="fondo-body">
    <?php include 'diseños/navbar.php'; ?>
    <main class="container my-4 pagina-transferencias">
        <div class="home-hero mb-5">
            <div>
                <h1>Transferencias</h1>
                <p><?= $esAdmin ? "Gestioná los pases de jugadores entre clubes." : "Mostrá interés por jugadores de otros clubes y segui tus solicitudes." ?></p>
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
                    <label for="filtro_categoria" class="form-label">Categoria</label>
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
                                        data-jugador="<?= h($s['nombre'] . ' ' . $s['apellido']) ?>" data-ci="<?= h($s['ci']) ?>"
                                        data-cat="<?= h($s['categoria']) ?>" data-solicitante="<?= h($s['solicitante']) ?>"
                                        data-origen="<?= h($s['origen']) ?>" data-estado="<?= h(ucfirst($s['estado'])) ?>"
                                        data-fecha="<?= h(fmtFecha($s['fecha'])) ?>" data-respuesta="<?= h(fmtFecha($s['fecha_respuesta'])) ?>"
                                        data-por="<?= h($s['respondida_por'] ?? '-') ?>"><i class="ti ti-eye"></i></button>
                                    <?php if ($s['estado'] === 'pendiente'): ?>
                                        <?= btnForm('btn_sol_aceptar', (int)$s['id'], 'btn-success', 'check', 'Aceptar solicitud', '¿Aceptar la solicitud y transferir el jugador?') ?>
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
                        <tr data-jugador data-nombre="<?= h($j['nombre'] . ' ' . $j['apellido']) ?>" data-ci="<?= h($j['ci']) ?>" data-categoria="<?= h($j['categoria']) ?>" data-club="<?= h($j['club_actual']) ?>" <?= $i >= 3 ? 'hidden' : '' ?>>
                            <td><?= h($j['ci']) ?></td>
                            <td><?= h($j['nombre'] . ' ' . $j['apellido']) ?></td>
                            <td><?= h($j['carnet_vencimiento'] ?: 'Sin fecha registrada') ?></td>
                            <td>
                                <div class="acciones">
                                    <?php if ((int)$j['ya_solicitado'] > 0): ?>
                                        <button type="button" class="btn btn-outline-light btn-sm" disabled><i class="ti ti-clock"></i> Solicitud enviada</button>
                                    <?php else: ?>
                                        <?= btnForm('btn_solicitar', (int)$j['id'], 'btn-success', 'user-plus', 'Solicitar Fichaje', '', '', 'jugador_id') ?>
                                    <?php endif; ?>
                                </div>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                        <tr id="sin_resultados_filtro" hidden><td colspan="4" class="text-center text-muted py-4">No se encontraron jugadores con esos filtros.</td></tr>
                        <?php else: echo vacio(4, 'No hay jugadores de otros clubes disponibles.'); endif; ?>
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
                                    <?php if ($s['estado'] === 'pendiente') echo btnForm('btn_cancelar', (int)$s['id'], 'btn-outline-danger', 'trash', 'Cancelar', '¿Cancelar esta solicitud?', '', 'solicitud_id'); ?>
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
                                    data-id="<?= (int)$r['id'] ?>" data-jugador="<?= h($r['nombre'] . ' ' . $r['apellido']) ?>"
                                    data-origen-id="<?= (int)$r['club_origen_id'] ?>" data-destino-id="<?= (int)$r['club_destino_id'] ?>">
                                    <i class="ti ti-edit"></i>
                                </button>
                                <?= btnForm('btn_eliminar', (int)$r['id'], 'btn-danger', 'trash', 'Eliminar / anular', '¿Anular esta transferencia?', '', 'transferencia_id') ?>
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

    <!-- INCLUIR MODALES -->
    <?php include '../modales/modal_transferencias.php'; ?>

    <footer>
        &copy; 2026 MateTech. Todos los derechos reservados.
        <img class="foot" src="../img/logo.png" alt="Logo">
    </footer>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    
    <?php if ($esAdmin): ?>
        <script>
            // Inyectar datos de PHP a JS de forma limpia
            window.jugadoresAdmin = <?= json_encode($jugadores_admin, JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT) ?>;
        </script>
    <?php endif; ?>
    
    <script src="../js/transferencias.js"></script>
</body>
</html>