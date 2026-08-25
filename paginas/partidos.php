<?php
require_once "login/auth.php";
include '../sql/basededatos.php';

$rol             = $_SESSION["Rol"] ?? "";
$club_id_usuario = $_SESSION["ClubID"] ?? null;

// Crear partido
if ($rol === "Admin" && isset($_POST["btn_crear"])) {
    $pdo->prepare("INSERT INTO partidos (categoria_id, club_local_id, club_visitante_id, fecha_partido, estado) VALUES (?, ?, ?, ?, ?)")
        ->execute([$_POST["categoria_id"], $_POST["club_local_id"], $_POST["club_visitante_id"],
                   !empty($_POST["fecha_partido"]) ? $_POST["fecha_partido"] : null, $_POST["estado"] ?? "sin_fecha"]);
    header("Location: partidos.php"); exit();
}

// Cambiar estado
if ($rol === "Admin" && isset($_POST["btn_estado"])) {
    $pdo->prepare("UPDATE partidos SET estado=?, fecha_partido=? WHERE id=?")
        ->execute([$_POST["estado"], !empty($_POST["fecha_partido"]) ? $_POST["fecha_partido"] : null, $_POST["partido_id"]]);
    header("Location: partidos.php"); exit();
}

// Registrar resultado
if ($rol === "Admin" && isset($_POST["btn_resultado"])) {
    $pdo->prepare("UPDATE partidos SET goles_local=?, goles_visitante=?, estado='jugado' WHERE id=?")
        ->execute([$_POST["goles_local"], $_POST["goles_visitante"], $_POST["partido_id"]]);
    header("Location: partidos.php"); exit();
}

// Registrar tarjeta
if ($rol === "Admin" && isset($_POST["btn_tarjeta"])) {
    $pdo->prepare("INSERT INTO sanciones (partido_id, jugador_id, tipo_tarjeta, minuto) VALUES (?, ?, ?, ?)")
        ->execute([$_POST["partido_id"], $_POST["jugador_id"], $_POST["tipo_tarjeta"],
                   !empty($_POST["minuto"]) ? $_POST["minuto"] : null]);
    header("Location: partidos.php"); exit();
}

// Obtener partidos
$where  = "";
$params = [];
if ($rol === "Club") {
    $where  = "WHERE p.club_local_id = ? OR p.club_visitante_id = ?";
    $params = [$club_id_usuario, $club_id_usuario];
}

$partidos = $pdo->prepare("
    SELECT p.*, cl.nombre AS local, cv.nombre AS visitante, cat.nombre AS categoria
    FROM partidos p
    JOIN club cl  ON cl.id  = p.club_local_id
    JOIN club cv  ON cv.id  = p.club_visitante_id
    JOIN categorias cat ON cat.id = p.categoria_id
    $where
    ORDER BY p.fecha_partido DESC, p.id DESC
");
$partidos->execute($params);
$partidos = $partidos->fetchAll(PDO::FETCH_OBJ);

if ($rol === "Admin") {
    $clubes     = $pdo->query("SELECT id, nombre FROM club WHERE rol='Club' ORDER BY nombre")->fetchAll(PDO::FETCH_OBJ);
    $categorias = $pdo->query("SELECT id, nombre FROM categorias ORDER BY nombre")->fetchAll(PDO::FETCH_OBJ);
}

$estados = ["sin_fecha", "programado", "jugado", "pendiente", "suspendido"];
$badge   = ["sin_fecha" => "secondary", "programado" => "primary", "jugado" => "success", "pendiente" => "warning", "suspendido" => "danger"];
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/@tabler/icons-webfont@latest/dist/tabler-icons.min.css">
    <link rel="icon" href="../img/logo.png" type="image/png">
    <link href="../estilos.css" rel="stylesheet">
    <title>LeagueFlow - Partidos</title>
</head>
<body class="fondo-body">

<?php include 'diseños/navbar.php'; ?>

<main>
    <div class="home-hero">
        <div>
            <h1>Partidos</h1>
            <p>Fixture y resultados de la liga.</p>
        </div>
        <img src="../img/Login-foto0.png" alt="LeagueFlow" class="home-hero-logo">
    </div>

    <div class="container mt-4">
        <?php if ($rol === "Admin"): ?>
            <button class="btn btn-primary mb-4" data-bs-toggle="modal" data-bs-target="#modalCrear">
                <i class="ti ti-plus"></i> Nuevo partido
            </button>
        <?php endif; ?>

        <div class="table-responsive">
            <table class="table table-striped text-center align-middle">
                <thead>
                    <tr>
                        <th>Categoría</th>
                        <th>Local</th>
                        <th>Resultado</th>
                        <th>Visitante</th>
                        <th>Fecha</th>
                        <th>Estado</th>
                        <?php if ($rol === "Admin"): ?><th>Acciones</th><?php endif; ?>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($partidos)): ?>
                        <tr><td colspan="7" class="text-muted py-4">No hay partidos registrados.</td></tr>
                    <?php else: foreach ($partidos as $p): ?>
                        <tr>
                            <td><span class="badge bg-secondary"><?= htmlspecialchars($p->categoria) ?></span></td>
                            <td><?= htmlspecialchars($p->local) ?></td>
                            <td>
                                <?php if ($p->estado === "jugado"): ?>
                                    <strong><?= $p->goles_local ?> - <?= $p->goles_visitante ?></strong>
                                <?php else: ?>
                                    <span class="text-muted">-</span>
                                <?php endif; ?>
                            </td>
                            <td><?= htmlspecialchars($p->visitante) ?></td>
                            <td><?= $p->fecha_partido ? date("d/m/Y H:i", strtotime($p->fecha_partido)) : "<span class='text-muted'>Sin fecha</span>" ?></td>
                            <td><span class="badge bg-<?= $badge[$p->estado] ?>"><?= ucfirst(str_replace("_", " ", $p->estado)) ?></span></td>
                            <?php if ($rol === "Admin"): ?>
                                <td class="d-flex gap-1 justify-content-center">
                                    <button class="btn btn-sm btn-warning" data-bs-toggle="modal" data-bs-target="#modalEstado"
                                        data-id="<?= $p->id ?>" data-estado="<?= $p->estado ?>" data-fecha="<?= $p->fecha_partido ?>">
                                        <i class="ti ti-edit"></i>
                                    </button>
                                    <button class="btn btn-sm btn-success" data-bs-toggle="modal" data-bs-target="#modalResultado"
                                        data-id="<?= $p->id ?>" data-local="<?= htmlspecialchars($p->local) ?>" data-visitante="<?= htmlspecialchars($p->visitante) ?>">
                                        <i class="ti ti-ball-football"></i>
                                    </button>
                                    <button class="btn btn-sm btn-danger" data-bs-toggle="modal" data-bs-target="#modalTarjeta"
                                        data-id="<?= $p->id ?>"
                                        data-local-id="<?= $p->club_local_id ?>"
                                        data-visitante-id="<?= $p->club_visitante_id ?>"
                                        data-categoria-id="<?= $p->categoria_id ?>"
                                        data-local="<?= htmlspecialchars($p->local) ?>"
                                        data-visitante="<?= htmlspecialchars($p->visitante) ?>">
                                        <i class="ti ti-cards"></i>
                                    </button>
                                </td>
                            <?php endif; ?>
                        </tr>
                    <?php endforeach; endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</main>

<footer>
    &copy; 2026 MateTech. Todos los derechos reservados.
    <img class="foot" src="../img/logo.png" alt="Logo">
</footer>

<?php if ($rol === "Admin"): ?>

<!-- Modal: Crear partido -->
<div class="modal fade" id="modalCrear" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <form method="POST">
                <div class="modal-header">
                    <h5 class="modal-title"><i class="ti ti-plus"></i> Nuevo partido</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label">Categoría</label>
                        <select name="categoria_id" class="form-select" required>
                            <option value="">Seleccionar...</option>
                            <?php foreach ($categorias as $c): ?>
                                <option value="<?= $c->id ?>"><?= htmlspecialchars($c->nombre) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Club local</label>
                        <select name="club_local_id" class="form-select" required>
                            <option value="">Seleccionar...</option>
                            <?php foreach ($clubes as $c): ?>
                                <option value="<?= $c->id ?>"><?= htmlspecialchars($c->nombre) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Club visitante</label>
                        <select name="club_visitante_id" class="form-select" required>
                            <option value="">Seleccionar...</option>
                            <?php foreach ($clubes as $c): ?>
                                <option value="<?= $c->id ?>"><?= htmlspecialchars($c->nombre) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Fecha y hora <small class="text-muted">(opcional)</small></label>
                        <input type="datetime-local" name="fecha_partido" class="form-control">
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Estado</label>
                        <select name="estado" class="form-select">
                            <?php foreach ($estados as $e): ?>
                                <option value="<?= $e ?>"><?= ucfirst(str_replace("_", " ", $e)) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                    <button type="submit" name="btn_crear" value="1" class="btn btn-primary">Crear partido</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Modal: Cambiar estado -->
<div class="modal fade" id="modalEstado" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <form method="POST">
                <input type="hidden" name="partido_id" id="estado_partido_id">
                <div class="modal-header">
                    <h5 class="modal-title"><i class="ti ti-edit"></i> Editar partido</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label">Estado</label>
                        <select name="estado" id="estado_select" class="form-select">
                            <?php foreach ($estados as $e): ?>
                                <option value="<?= $e ?>"><?= ucfirst(str_replace("_", " ", $e)) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Fecha y hora</label>
                        <input type="datetime-local" name="fecha_partido" id="estado_fecha" class="form-control">
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                    <button type="submit" name="btn_estado" value="1" class="btn btn-warning">Guardar</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Modal: Registrar resultado -->
<div class="modal fade" id="modalResultado" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <form method="POST">
                <input type="hidden" name="partido_id" id="res_partido_id">
                <div class="modal-header">
                    <h5 class="modal-title"><i class="ti ti-ball-football"></i> Registrar resultado</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="d-flex align-items-center gap-3">
                        <div class="text-center flex-fill">
                            <label class="form-label fw-bold" id="res_local_label">Local</label>
                            <input type="number" name="goles_local" class="form-control text-center fs-4" min="0" value="0" required>
                        </div>
                        <span class="fs-3 fw-bold">-</span>
                        <div class="text-center flex-fill">
                            <label class="form-label fw-bold" id="res_visitante_label">Visitante</label>
                            <input type="number" name="goles_visitante" class="form-control text-center fs-4" min="0" value="0" required>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                    <button type="submit" name="btn_resultado" value="1" class="btn btn-success">Confirmar resultado</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Modal: Tarjetas -->
<div class="modal fade" id="modalTarjeta" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content">
            <form method="POST">
                <input type="hidden" name="partido_id" id="tarjeta_partido_id">
                <div class="modal-header">
                    <h5 class="modal-title"><i class="ti ti-cards"></i> Registrar tarjeta</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">

                    <!-- Selector de club -->
                    <div class="mb-3">
                        <label class="form-label">Club</label>
                        <select id="tarjeta_club_select" class="form-select">
                            <option value="">Seleccionar club...</option>
                            <!-- Se llena por JS -->
                        </select>
                    </div>

                    <!-- Jugadores del club seleccionado -->
                    <div class="mb-3">
                        <label class="form-label">Jugador</label>
                        <select name="jugador_id" id="tarjeta_jugador_select" class="form-select" required>
                            <option value="">Primero seleccioná un club</option>
                        </select>
                    </div>

                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Tipo de tarjeta</label>
                            <select name="tipo_tarjeta" class="form-select" required>
                                <option value="amarilla">🟨 Amarilla</option>
                                <option value="roja">🟥 Roja</option>
                            </select>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Minuto <small class="text-muted">(opcional)</small></label>
                            <input type="number" name="minuto" class="form-control" min="1" max="120" placeholder="Ej: 45">
                        </div>
                    </div>

                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                    <button type="submit" name="btn_tarjeta" value="1" class="btn btn-danger">Registrar tarjeta</button>
                </div>
            </form>
        </div>
    </div>
</div>

<?php endif; ?>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script>
document.getElementById('modalEstado')?.addEventListener('show.bs.modal', function(e) {
    const btn = e.relatedTarget;
    document.getElementById('estado_partido_id').value = btn.dataset.id;
    document.getElementById('estado_select').value     = btn.dataset.estado;
    document.getElementById('estado_fecha').value      = btn.dataset.fecha?.slice(0, 16) ?? '';
});

document.getElementById('modalResultado')?.addEventListener('show.bs.modal', function(e) {
    const btn = e.relatedTarget;
    document.getElementById('res_partido_id').value            = btn.dataset.id;
    document.getElementById('res_local_label').textContent     = btn.dataset.local;
    document.getElementById('res_visitante_label').textContent = btn.dataset.visitante;
});

// Modal tarjeta — cargar clubs del partido y jugadores por AJAX
document.getElementById('modalTarjeta')?.addEventListener('show.bs.modal', function(e) {
    const btn         = e.relatedTarget;
    const partidoId   = btn.dataset.id;
    const localId     = btn.dataset.localId;
    const visitanteId = btn.dataset.visitanteId;
    const categoriaId = btn.dataset.categoriaId;
    const localNombre     = btn.dataset.local;
    const visitanteNombre = btn.dataset.visitante;

    document.getElementById('tarjeta_partido_id').value = partidoId;

    // Llenar selector de clubes
    const clubSelect = document.getElementById('tarjeta_club_select');
    clubSelect.innerHTML = `
        <option value="">Seleccionar club...</option>
        <option value="${localId}|${categoriaId}">${localNombre}</option>
        <option value="${visitanteId}|${categoriaId}">${visitanteNombre}</option>
    `;

    document.getElementById('tarjeta_jugador_select').innerHTML = '<option value="">Primero seleccioná un club</option>';
});

// Cuando cambia el club — cargar jugadores por fetch
document.getElementById('tarjeta_club_select')?.addEventListener('change', function() {
    const jugadorSelect = document.getElementById('tarjeta_jugador_select');
    if (!this.value) {
        jugadorSelect.innerHTML = '<option value="">Primero seleccioná un club</option>';
        return;
    }

    const [clubId, categoriaId] = this.value.split('|');
    jugadorSelect.innerHTML = '<option value="">Cargando...</option>';

    fetch(`../sql/obtener_jugadores_club.php?club_id=${clubId}&categoria_id=${categoriaId}`)
        .then(r => r.json())
        .then(jugadores => {
            if (!jugadores.length) {
                jugadorSelect.innerHTML = '<option value="">Sin jugadores en esta categoría</option>';
                return;
            }
            jugadorSelect.innerHTML = '<option value="">Seleccionar jugador...</option>' +
                jugadores.map(j => `<option value="${j.id}">${j.apellido}, ${j.nombre} — CI: ${j.ci}</option>`).join('');
        })
        .catch(() => {
            jugadorSelect.innerHTML = '<option value="">Error al cargar jugadores</option>';
        });
});
</script>
</body>
</html>