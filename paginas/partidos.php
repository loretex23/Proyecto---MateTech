<?php
require_once "login/auth.php";
include "../sql/basededatos.php";

$rol             = $_SESSION["Rol"] ?? "";
$club_id_usuario = $_SESSION["ClubID"] ?? null;

if ($rol === "Admin") {
    if (isset($_POST["btn_crear"])) {
        $pdo->prepare("INSERT INTO partidos (categoria_id,club_local_id,club_visitante_id,fecha_partido,estado) VALUES (?,?,?,?,?)")
            ->execute([$_POST["categoria_id"],$_POST["club_local_id"],$_POST["club_visitante_id"],$_POST["fecha_partido"]?:null,$_POST["estado"]??"sin_fecha"]);
        header("Location: partidos.php"); exit();
    }
    if (isset($_POST["btn_estado"])) {
        $pdo->prepare("UPDATE partidos SET estado=?,fecha_partido=? WHERE id=?")
            ->execute([$_POST["estado"],$_POST["fecha_partido"]?:null,$_POST["partido_id"]]);
        header("Location: partidos.php"); exit();
    }
    if (isset($_POST["btn_resultado"])) {
        $pid = (int)$_POST["partido_id"];
        $pdo->prepare("UPDATE partidos SET goles_local=?,goles_visitante=?,estado='jugado' WHERE id=?")
            ->execute([$_POST["goles_local"],$_POST["goles_visitante"],$pid]);
        $pdo->prepare("DELETE FROM goles WHERE partido_id=?")->execute([$pid]);
        $ins = $pdo->prepare("INSERT INTO goles (partido_id,jugador_id,minuto,tipo) VALUES (?,?,?,?)");
        foreach (($_POST["gol_jugador"] ?? []) as $i => $jid) {
            if (!$jid) continue;
            $ins->execute([$pid,(int)$jid,$_POST["gol_minuto"][$i]?:null,$_POST["gol_tipo"][$i]??"normal"]);
        }
        header("Location: partidos.php"); exit();
    }
    if (isset($_POST["btn_tarjeta"])) {
        $pdo->prepare("INSERT INTO sanciones (partido_id,jugador_id,tipo_tarjeta,minuto) VALUES (?,?,?,?)")
            ->execute([$_POST["partido_id"],$_POST["jugador_id"],$_POST["tipo_tarjeta"],$_POST["minuto"]?:null]);
        header("Location: partidos.php"); exit();
    }
    if (isset($_POST["btn_lesion"])) {
        $pdo->prepare("INSERT INTO lesiones (partido_id,jugador_id,descripcion,minuto) VALUES (?,?,?,?)")
            ->execute([$_POST["partido_id"],$_POST["jugador_id"],trim($_POST["descripcion"]??""),$_POST["minuto"]?:null]);
        header("Location: partidos.php"); exit();
    }
}

$where = $rol === "Club" ? "WHERE p.club_local_id=? OR p.club_visitante_id=?" : "";
$params = $rol === "Club" ? [$club_id_usuario,$club_id_usuario] : [];
$stmt = $pdo->prepare("SELECT p.*,cl.nombre AS local,cv.nombre AS visitante,cat.nombre AS categoria
    FROM partidos p JOIN club cl ON cl.id=p.club_local_id JOIN club cv ON cv.id=p.club_visitante_id
    JOIN categorias cat ON cat.id=p.categoria_id $where ORDER BY p.fecha_partido DESC,p.id DESC");
$stmt->execute($params);
$partidos = $stmt->fetchAll(PDO::FETCH_OBJ);

if ($rol === "Admin") {
    $clubes     = $pdo->query("SELECT id,nombre FROM club WHERE rol='Club' ORDER BY nombre")->fetchAll(PDO::FETCH_OBJ);
    $categorias = $pdo->query("SELECT id,nombre FROM categorias ORDER BY nombre")->fetchAll(PDO::FETCH_OBJ);
}

$estados = ["sin_fecha"=>"Sin fecha","programado"=>"Programado","jugado"=>"Jugado","pendiente"=>"Pendiente","suspendido"=>"Suspendido"];
$badge   = ["sin_fecha"=>"secondary","programado"=>"primary","jugado"=>"success","pendiente"=>"warning","suspendido"=>"danger"];
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
<style>
.match-header{display:flex;align-items:center;gap:16px;padding:18px 12px;background:linear-gradient(135deg,#1a3a2a,#0f2018);border-radius:10px 10px 0 0;margin:-16px -16px 16px}
.match-team{flex:1;text-align:center;color:#fff;font-weight:700;font-size:.95rem}
.match-score{font-size:2.2rem;font-weight:900;color:#fff;letter-spacing:4px;min-width:90px;text-align:center;line-height:1}
.match-score small{display:block;font-size:.6rem;font-weight:400;letter-spacing:1px;color:rgba(255,255,255,.45);margin-top:3px}
.timeline{position:relative;padding-bottom:4px}
.timeline::before{content:'';position:absolute;left:50%;top:0;bottom:0;width:2px;background:#e5e7eb;transform:translateX(-50%)}
.tl-row{display:flex;align-items:center;margin-bottom:7px;position:relative;min-height:42px}
.tl-row.local{flex-direction:row}.tl-row.visitante{flex-direction:row-reverse}
.tl-card{display:flex;align-items:center;gap:6px;background:#fff;border:1px solid #e5e7eb;border-radius:8px;padding:5px 8px;width:calc(50% - 25px);box-shadow:0 1px 3px rgba(0,0,0,.05)}
.tl-row.visitante .tl-card{flex-direction:row-reverse}
.tl-icon{width:24px;height:24px;border-radius:50%;display:flex;align-items:center;justify-content:center;font-size:12px;flex-shrink:0}
.tl-icon.gol{background:#dcfce7}.tl-icon.penal{background:#dbeafe}.tl-icon.autogol,.tl-icon.roja{background:#fee2e2}.tl-icon.amarilla{background:#fef9c3}.tl-icon.lesion{background:#fce7f3}
.tl-info{min-width:0}
.tl-info .player{font-size:.78rem;font-weight:600;color:#111;white-space:nowrap;overflow:hidden;text-overflow:ellipsis}
.tl-info .detail{font-size:.68rem;color:#6b7280}
.tl-min{width:32px;height:32px;background:#f3f4f6;border:2px solid #e5e7eb;border-radius:50%;display:flex;align-items:center;justify-content:center;font-size:.65rem;font-weight:700;color:#374151;flex-shrink:0;z-index:1;position:absolute;left:50%;transform:translateX(-50%)}
.gol-row{display:flex;gap:6px;align-items:center;margin-bottom:5px}
.gol-row select{flex:2}.gol-row .tipo-sel{flex:1}.gol-row input{flex:0 0 65px}.gol-row .btn-remove{flex-shrink:0}
.no-events{text-align:center;color:#9ca3af;font-size:.8rem;padding:8px 0}
</style>
</head>
<body class="fondo-body">
<?php include "diseños/navbar.php"; ?>

<main>
<div class="home-hero">
    <div><h1>Partidos</h1><p>Fixture y resultados de la liga.</p></div>
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
            <thead><tr>
                <th>Categoría</th><th>Local</th><th>Resultado</th><th>Visitante</th>
                <th>Fecha</th><th>Estado</th><th>Detalle</th>
                <?php if ($rol==="Admin"): ?><th>Acciones</th><?php endif; ?>
            </tr></thead>
            <tbody>
            <?php if (empty($partidos)): ?>
                <tr><td colspan="<?= $rol==="Admin"?8:7 ?>" class="text-muted py-4">No hay partidos registrados.</td></tr>
            <?php else: foreach ($partidos as $p): ?>
                <tr>
                    <td><span class="badge bg-secondary"><?= htmlspecialchars($p->categoria) ?></span></td>
                    <td><?= htmlspecialchars($p->local) ?></td>
                    <td><?= $p->estado==="jugado" ? "<strong>{$p->goles_local} - {$p->goles_visitante}</strong>" : '<span class="text-muted">-</span>' ?></td>
                    <td><?= htmlspecialchars($p->visitante) ?></td>
                    <td><?= $p->fecha_partido ? date("d/m/Y H:i",strtotime($p->fecha_partido)) : '<span class="text-muted">Sin fecha</span>' ?></td>
                    <td><span class="badge bg-<?= $badge[$p->estado]??"secondary" ?>"><?= $estados[$p->estado]??"" ?></span></td>
                    <td>
                        <button class="btn btn-sm btn-outline-info" data-bs-toggle="modal"
                            data-bs-target="#modalDetalle" data-id="<?= $p->id ?>">
                            <i class="ti ti-info-circle"></i> Ver más
                        </button>
                    </td>
                    <?php if ($rol==="Admin"): ?>
                    <td class="d-flex gap-1 justify-content-center">
                        <button class="btn btn-sm btn-warning" data-bs-toggle="modal" data-bs-target="#modalEstado"
                            data-id="<?= $p->id ?>" data-estado="<?= $p->estado ?>"
                            data-fecha="<?= htmlspecialchars($p->fecha_partido??"",ENT_QUOTES) ?>">
                            <i class="ti ti-edit"></i></button>
                        <button class="btn btn-sm btn-success" data-bs-toggle="modal" data-bs-target="#modalResultado"
                            data-id="<?= $p->id ?>" data-local-id="<?= $p->club_local_id ?>"
                            data-visitante-id="<?= $p->club_visitante_id ?>" data-categoria-id="<?= $p->categoria_id ?>"
                            data-local="<?= htmlspecialchars($p->local,ENT_QUOTES) ?>"
                            data-visitante="<?= htmlspecialchars($p->visitante,ENT_QUOTES) ?>"
                            data-goles-local="<?= $p->goles_local ?>" data-goles-visitante="<?= $p->goles_visitante ?>">
                            <i class="ti ti-ball-football"></i></button>
                        <button class="btn btn-sm btn-danger" data-bs-toggle="modal" data-bs-target="#modalEvento"
                            data-evento="tarjeta" data-id="<?= $p->id ?>"
                            data-local-id="<?= $p->club_local_id ?>" data-visitante-id="<?= $p->club_visitante_id ?>"
                            data-categoria-id="<?= $p->categoria_id ?>"
                            data-local="<?= htmlspecialchars($p->local,ENT_QUOTES) ?>"
                            data-visitante="<?= htmlspecialchars($p->visitante,ENT_QUOTES) ?>">
                            <i class="ti ti-cards"></i></button>
                        <button class="btn btn-sm btn-secondary" data-bs-toggle="modal" data-bs-target="#modalEvento"
                            data-evento="lesion" data-id="<?= $p->id ?>"
                            data-local-id="<?= $p->club_local_id ?>" data-visitante-id="<?= $p->club_visitante_id ?>"
                            data-categoria-id="<?= $p->categoria_id ?>"
                            data-local="<?= htmlspecialchars($p->local,ENT_QUOTES) ?>"
                            data-visitante="<?= htmlspecialchars($p->visitante,ENT_QUOTES) ?>">
                            <i class="ti ti-first-aid-kit"></i></button>
                    </td>
                    <?php endif; ?>
                </tr>
            <?php endforeach; endif; ?>
            </tbody>
        </table>
    </div>
</div>
</main>

<footer>&copy; 2026 MateTech. Todos los derechos reservados.
    <img class="foot" src="../img/logo.png" alt="Logo"></footer>

<!-- Detalle -->
<div class="modal fade" id="modalDetalle" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg"><div class="modal-content">
        <div class="modal-header border-0 pb-0">
            <h5 class="modal-title"><i class="ti ti-info-circle"></i> Detalle del partido</h5>
            <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
        </div>
        <div class="modal-body" id="detalle_contenido"><p class="text-muted text-center py-3">Cargando...</p></div>
        <div class="modal-footer"><button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cerrar</button></div>
    </div></div>
</div>

<?php if ($rol==="Admin"): ?>

<!-- Crear -->
<div class="modal fade" id="modalCrear" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered"><div class="modal-content"><form method="POST">
        <div class="modal-header"><h5 class="modal-title"><i class="ti ti-plus"></i> Nuevo partido</h5><button type="button" class="btn-close" data-bs-dismiss="modal"></button></div>
        <div class="modal-body">
            <div class="mb-3"><label class="form-label">Categoría</label>
                <select name="categoria_id" class="form-select" required><option value="">Seleccionar...</option>
                <?php foreach($categorias as $c): ?><option value="<?=$c->id?>"><?=htmlspecialchars($c->nombre)?></option><?php endforeach;?>
                </select></div>
            <div class="mb-3"><label class="form-label">Club local</label>
                <select name="club_local_id" class="form-select" required><option value="">Seleccionar...</option>
                <?php foreach($clubes as $c): ?><option value="<?=$c->id?>"><?=htmlspecialchars($c->nombre)?></option><?php endforeach;?>
                </select></div>
            <div class="mb-3"><label class="form-label">Club visitante</label>
                <select name="club_visitante_id" class="form-select" required><option value="">Seleccionar...</option>
                <?php foreach($clubes as $c): ?><option value="<?=$c->id?>"><?=htmlspecialchars($c->nombre)?></option><?php endforeach;?>
                </select></div>
            <div class="mb-3"><label class="form-label">Fecha y hora <small class="text-muted">(opcional)</small></label><input type="datetime-local" name="fecha_partido" class="form-control"></div>
            <div class="mb-3"><label class="form-label">Estado</label>
                <select name="estado" class="form-select"><?php foreach($estados as $v=>$l): ?><option value="<?=$v?>"><?=$l?></option><?php endforeach;?></select></div>
        </div>
        <div class="modal-footer"><button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button><button type="submit" name="btn_crear" value="1" class="btn btn-primary">Crear</button></div>
    </form></div></div>
</div>

<!-- Estado -->
<div class="modal fade" id="modalEstado" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered"><div class="modal-content"><form method="POST">
        <input type="hidden" name="partido_id" id="est_id">
        <div class="modal-header"><h5 class="modal-title"><i class="ti ti-edit"></i> Editar partido</h5><button type="button" class="btn-close" data-bs-dismiss="modal"></button></div>
        <div class="modal-body">
            <div class="mb-3"><label class="form-label">Estado</label><select name="estado" id="est_select" class="form-select"><?php foreach($estados as $v=>$l): ?><option value="<?=$v?>"><?=$l?></option><?php endforeach;?></select></div>
            <div class="mb-3"><label class="form-label">Fecha y hora</label><input type="datetime-local" name="fecha_partido" id="est_fecha" class="form-control"></div>
        </div>
        <div class="modal-footer"><button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button><button type="submit" name="btn_estado" value="1" class="btn btn-warning">Guardar</button></div>
    </form></div></div>
</div>

<!-- Resultado + Goles -->
<div class="modal fade" id="modalResultado" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-xl"><div class="modal-content"><form method="POST">
        <input type="hidden" name="partido_id" id="res_id">
        <div class="modal-header"><h5 class="modal-title"><i class="ti ti-ball-football"></i> Resultado y goles</h5><button type="button" class="btn-close" data-bs-dismiss="modal"></button></div>
        <div class="modal-body">
            <div class="d-flex align-items-center justify-content-center gap-3 mb-4 p-3" style="background:#f8fafc;border-radius:10px;border:1px solid #e5e7eb">
                <div class="text-center flex-fill">
                    <div class="fw-bold mb-1" id="res_local_nombre" style="font-size:.9rem"></div>
                    <input type="number" name="goles_local" id="res_gl" class="form-control text-center fs-3 fw-bold" min="0" value="0" required style="max-width:80px;margin:auto">
                </div>
                <span class="fs-2 fw-bold text-muted">–</span>
                <div class="text-center flex-fill">
                    <div class="fw-bold mb-1" id="res_visit_nombre" style="font-size:.9rem"></div>
                    <input type="number" name="goles_visitante" id="res_gv" class="form-control text-center fs-3 fw-bold" min="0" value="0" required style="max-width:80px;margin:auto">
                </div>
            </div>
            <div class="row g-3">
                <div class="col-md-6">
                    <div class="d-flex align-items-center gap-2 mb-2">
                        <span class="fw-bold" id="res_local_lbl" style="font-size:.85rem"></span>
                        <span class="badge bg-success" id="bdg_l">0</span>
                    </div>
                    <div id="wrap_l"></div>
                    <button type="button" class="btn btn-sm btn-outline-success mt-1" id="add_l"><i class="ti ti-plus"></i> Agregar gol</button>
                </div>
                <div class="col-md-6">
                    <div class="d-flex align-items-center gap-2 mb-2">
                        <span class="fw-bold" id="res_visit_lbl" style="font-size:.85rem"></span>
                        <span class="badge bg-success" id="bdg_v">0</span>
                    </div>
                    <div id="wrap_v"></div>
                    <button type="button" class="btn btn-sm btn-outline-success mt-1" id="add_v"><i class="ti ti-plus"></i> Agregar gol</button>
                </div>
            </div>
            <p class="text-muted mt-2 mb-0" style="font-size:.73rem"><i class="ti ti-info-circle"></i> Podés dejar goles sin jugador asignado.</p>
        </div>
        <div class="modal-footer"><button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button><button type="submit" name="btn_resultado" value="1" class="btn btn-success"><i class="ti ti-device-floppy"></i> Guardar resultado</button></div>
    </form></div></div>
</div>

<!-- Tarjeta / Lesión -->
<div class="modal fade" id="modalEvento" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg"><div class="modal-content"><form method="POST">
        <input type="hidden" name="partido_id" id="ev_pid">
        <div class="modal-header"><h5 class="modal-title" id="ev_titulo"></h5><button type="button" class="btn-close" data-bs-dismiss="modal"></button></div>
        <div class="modal-body">
            <div class="mb-3"><label class="form-label">Club</label><select id="ev_club" class="form-select"><option value="">Seleccionar club...</option></select></div>
            <div class="mb-3"><label class="form-label">Jugador</label><select name="jugador_id" id="ev_jugador" class="form-select" required><option value="">Primero seleccioná un club</option></select></div>
            <div class="row">
                <div class="col-md-6 mb-3" id="ev_tipo_wrap"><label class="form-label" id="ev_tipo_lbl">Tipo</label><select name="tipo_tarjeta" id="ev_tipo" class="form-select"></select></div>
                <div class="col-md-6 mb-3"><label class="form-label">Minuto <small class="text-muted">(opcional)</small></label><input type="number" name="minuto" class="form-control" min="1" max="120" placeholder="Ej: 45"></div>
                <div class="col-12 mb-3 d-none" id="ev_desc_wrap"><label class="form-label">Descripción <small class="text-muted">(opcional)</small></label><input type="text" name="descripcion" class="form-control" placeholder="Ej: Esguince de tobillo"></div>
            </div>
        </div>
        <div class="modal-footer"><button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button><button type="submit" id="ev_submit" class="btn"></button></div>
    </form></div></div>
</div>

<?php endif; ?>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script>
const $ = id => document.getElementById(id);
const esc = s => String(s??'').replace(/[&<>"']/g,c=>({'&':'&amp;','<':'&lt;','>':'&gt;','"':'&quot;',"'":'&#039;'}[c]));

// Estado
$('modalEstado')?.addEventListener('show.bs.modal', e => {
    const b = e.relatedTarget;
    $('est_id').value     = b.dataset.id;
    $('est_select').value = b.dataset.estado;
    $('est_fecha').value  = (b.dataset.fecha||'').slice(0,16);
});

// ── Resultado + Goles ──
let jLocal=[], jVisit=[];

const fetchJ = (club,cat) => fetch(`../sql/obtener_jugadores_club.php?club_id=${club}&categoria_id=${cat}`).then(r=>r.json());
const optsJ  = lista => '<option value="">Sin asignar</option>'+lista.map(j=>`<option value="${esc(j.id)}">${esc(j.apellido)}, ${esc(j.nombre)}</option>`).join('');

function golRow(equipo, opts) {
    return `<div class="gol-row mb-3 p-2" style="background:#f8fafc;border-radius:8px;border:1px solid #e5e7eb">
        <div class="d-flex justify-content-between align-items-center mb-1">
            <small class="text-muted fw-bold">GOL</small>
            <button style="right-margin:10px;" type="button" class="btn btn-sm btn-outline-danger btn-remove py-0 px-1"><i class="ti ti-x"></i></button>
        </div>
        <select name="gol_jugador[]" class="form-select form-select-sm mb-1">${opts}</select>
        <div class="d-flex gap-2">
            <select name="gol_tipo[]" class="form-select form-select-sm tipo-sel">
                <option value="normal">Normal</option>
                <option value="penal">Penal</option>
                <option value="autogol">Autogol</option>
            </select>
            <input type="number" name="gol_minuto[]" class="form-control form-control-sm" min="1" max="120" placeholder="Min" style="max-width:70px">
        </div>
    </div>`;
}

function bindRemove(wrap) {
    wrap.querySelectorAll('.btn-remove').forEach(b => b.onclick = () => b.closest('.gol-row').remove());
}

function renderN(wrap, equipo, n, opts) {
    wrap.innerHTML = '';
    for(let i=0;i<n;i++) wrap.insertAdjacentHTML('beforeend', golRow(equipo,opts));
    bindRemove(wrap);
}

function addGol(wrapId, badgeId, inputId, equipo, opts) {
    $(wrapId).insertAdjacentHTML('beforeend', golRow(equipo, opts));
    bindRemove($(wrapId));
    $(inputId).value = parseInt($(inputId).value||0)+1;
    $(badgeId).textContent = $(inputId).value;
}

$('res_gl')?.addEventListener('input', function() {
    const n=Math.max(0,parseInt(this.value)||0);
    $('bdg_l').textContent=n; renderN($('wrap_l'),'local',n,optsJ(jLocal));
});
$('res_gv')?.addEventListener('input', function() {
    const n=Math.max(0,parseInt(this.value)||0);
    $('bdg_v').textContent=n; renderN($('wrap_v'),'visit',n,optsJ(jVisit));
});
$('add_l')?.addEventListener('click', () => addGol('wrap_l','bdg_l','res_gl','local',optsJ(jLocal)));
$('add_v')?.addEventListener('click', () => addGol('wrap_v','bdg_v','res_gv','visit',optsJ(jVisit)));

$('modalResultado')?.addEventListener('show.bs.modal', async e => {
    const b=e.relatedTarget;
    $('res_id').value = b.dataset.id;
    $('res_local_nombre').textContent = $('res_local_lbl').textContent = b.dataset.local;
    $('res_visit_nombre').textContent = $('res_visit_lbl').textContent = b.dataset.visitante;

    const gl=parseInt(b.dataset.golesLocal)||0, gv=parseInt(b.dataset.golesVisitante)||0;
    $('res_gl').value=$('bdg_l').textContent=gl;
    $('res_gv').value=$('bdg_v').textContent=gv;
    $('wrap_l').innerHTML=$('wrap_v').innerHTML='';

    [jLocal,jVisit] = await Promise.all([
        fetchJ(b.dataset.localId, b.dataset.categoriaId),
        fetchJ(b.dataset.visitanteId, b.dataset.categoriaId)
    ]);

    if (gl>0||gv>0) {
        try {
            const data = await fetch('../sql/obtener_detalle_partido.php?id='+b.dataset.id).then(r=>r.json());
            const lid  = String(b.dataset.localId);
            const golesL = data.goles.filter(g=>String(g.club_id)===lid);
            const golesV = data.goles.filter(g=>String(g.club_id)!==lid);

            const fill = (wrap, goles, jugadores, equipo, total) => {
                goles.forEach(g => {
                    wrap.insertAdjacentHTML('beforeend', golRow(equipo, optsJ(jugadores)));
                    const row = wrap.lastElementChild;
                    row.querySelector('[name="gol_jugador[]"]').value = g.jugador_id||'';
                    row.querySelector('[name="gol_tipo[]"]').value    = g.tipo||'normal';
                    row.querySelector('[name="gol_minuto[]"]').value  = g.minuto||'';
                });
                for(let i=goles.length;i<total;i++)
                    wrap.insertAdjacentHTML('beforeend', golRow(equipo, optsJ(jugadores)));
                bindRemove(wrap);
            };
            fill($('wrap_l'), golesL, jLocal, 'local', gl);
            fill($('wrap_v'), golesV, jVisit, 'visit', gv);
        } catch(_) {
            renderN($('wrap_l'),'local',gl,optsJ(jLocal));
            renderN($('wrap_v'),'visit',gv,optsJ(jVisit));
        }
    }
});

// ── Tarjeta / Lesión ──
const EVENTO_CFG = {
    tarjeta:{titulo:'Registrar tarjeta',btn:'Registrar tarjeta',cls:'btn-danger',name:'btn_tarjeta',
             opts:'<option value="amarilla">🟨 Amarilla</option><option value="roja">🟥 Roja</option>',desc:false},
    lesion: {titulo:'Registrar lesión', btn:'Registrar lesión', cls:'btn-secondary',name:'btn_lesion',opts:'',desc:true}
};

$('ev_club')?.addEventListener('change', function() {
    const sel=$('ev_jugador');
    if(!this.value){sel.innerHTML='<option value="">Primero seleccioná un club</option>';return;}
    const [club,cat]=this.value.split('|');
    sel.innerHTML='<option value="">Cargando...</option>';
    fetchJ(club,cat)
        .then(data=>sel.innerHTML=data.length
            ?'<option value="">Seleccionar...</option>'+data.map(j=>`<option value="${esc(j.id)}">${esc(j.apellido)}, ${esc(j.nombre)} — CI: ${esc(j.ci)}</option>`).join('')
            :'<option value="">Sin jugadores</option>')
        .catch(()=>sel.innerHTML='<option value="">Error</option>');
});

$('modalEvento')?.addEventListener('show.bs.modal', e => {
    const b=e.relatedTarget, tipo=b.dataset.evento, c=EVENTO_CFG[tipo];
    $('ev_pid').value        = b.dataset.id;
    $('ev_titulo').innerHTML = c.titulo;
    $('ev_submit').textContent=c.btn; $('ev_submit').className='btn '+c.cls; $('ev_submit').name=c.name;
    $('ev_tipo').innerHTML   = c.opts;
    $('ev_tipo_wrap').classList.toggle('d-none', tipo==='lesion');
    $('ev_desc_wrap').classList.toggle('d-none', !c.desc);
    $('ev_club').innerHTML   = `<option value="">Seleccionar club...</option>
        <option value="${b.dataset.localId}|${b.dataset.categoriaId}">${esc(b.dataset.local)}</option>
        <option value="${b.dataset.visitanteId}|${b.dataset.categoriaId}">${esc(b.dataset.visitante)}</option>`;
    $('ev_jugador').innerHTML='<option value="">Primero seleccioná un club</option>';
});

// ── Detalle timeline ──
const GOL_ICON={normal:'⚽',penal:'🥅',autogol:'🔴'};
const GOL_CLS ={normal:'gol',penal:'penal',autogol:'autogol'};

$('modalDetalle')?.addEventListener('show.bs.modal', e => {
    const id=e.relatedTarget.dataset.id, cont=$('detalle_contenido');
    cont.innerHTML='<p class="text-muted text-center py-3">Cargando...</p>';
    fetch('../sql/obtener_detalle_partido.php?id='+encodeURIComponent(id))
        .then(r=>r.json())
        .then(d=>{
            if(d.error){cont.innerHTML=`<p class="text-danger">${esc(d.error)}</p>`;return;}
            const p=d.partido, lid=String(p.local_id);

            let html=`<div class="match-header">
                <div class="match-team">${esc(p.local)}</div>
                <div class="match-score">${p.estado==='jugado'
                    ?`${esc(p.goles_local)} <span style="opacity:.35">–</span> ${esc(p.goles_visitante)}`
                    :'<span style="font-size:1rem;opacity:.5">vs</span>'}
                <small>${p.estado==='jugado'?'RESULTADO FINAL':esc(p.estado.replace('_',' ').toUpperCase())}</small>
                </div>
                <div class="match-team">${esc(p.visitante)}</div>
            </div>`;

            const eventos=[
                ...d.goles.map(g=>({minuto:g.minuto,nombre:`${g.apellido}, ${g.nombre}`,club:g.club,
                    esLocal:String(g.club_id)===lid,icon:GOL_ICON[g.tipo]??'⚽',cls:GOL_CLS[g.tipo]??'gol',
                    detalle:g.tipo!=='normal'?g.tipo:''})),
                ...d.sanciones.map(s=>({minuto:s.minuto,nombre:`${s.apellido}, ${s.nombre}`,club:s.club,
                    esLocal:s.club===p.local,icon:s.tipo_tarjeta==='roja'?'🟥':'🟨',cls:s.tipo_tarjeta,
                    detalle:`tarjeta ${s.tipo_tarjeta}`})),
                ...d.lesiones.map(l=>({minuto:l.minuto,nombre:`${l.apellido}, ${l.nombre}`,club:l.club,
                    esLocal:l.club===p.local,icon:'🩹',cls:'lesion',detalle:l.descripcion||'lesión'})),
            ].sort((a,b)=>(a.minuto===null)-(b.minuto===null)||((a.minuto??Infinity)-(b.minuto??Infinity)));

            if(!eventos.length){
                html+='<p class="no-events" style="margin-top:10px">No hay eventos registrados.</p>';
            } else {
                html+='<div class="timeline" style="margin-top:8px">';
                eventos.forEach(ev=>{
                    const lado=ev.esLocal?'local':'visitante';
                    html+=`<div class="tl-row ${lado}">
                        <div class="tl-card">
                            <div class="tl-icon ${ev.cls}">${ev.icon}</div>
                            <div class="tl-info">
                                <div class="player">${esc(ev.nombre)}</div>
                                <div class="detail">${esc(ev.club)}${ev.detalle?' · '+esc(ev.detalle):''}</div>
                            </div>
                        </div>
                        <div class="tl-min">${ev.minuto!==null?esc(ev.minuto)+"'":'—'}</div>
                    </div>`;
                });
                html+='</div>';
            }
            cont.innerHTML=html;
        })
        .catch(()=>cont.innerHTML='<p class="text-danger">Error al cargar el detalle.</p>');
});
</script>
</body>
</html>