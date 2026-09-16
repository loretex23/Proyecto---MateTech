const $ = id => document.getElementById(id);

const esc = s => String(s ?? '').replace(/[&<>"']/g, c => ({
    '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#039;'
}[c]));

function pedirJugadores(clubId, categoriaId) {
    return fetch(`../sql/obtener_jugadores_club.php?club_id=${clubId}&categoria_id=${categoriaId}`)
        .then(r => r.json());
}

// Cuando se abre el modal unificado: carga todos los datos del partido en cada tab
$('modalGestionar')?.addEventListener('show.bs.modal', e => {
    const b = e.relatedTarget;

    // Cabecera
    $('mg_titulo').textContent    = 'Gestionar partido';
    $('mg_subtitulo').textContent = `${b.dataset.local} vs ${b.dataset.visitante}`;

    // Tab Estado
    $('est_id').value     = b.dataset.id;
    $('est_select').value = b.dataset.estado;
    $('est_fecha').value  = (b.dataset.fecha || '').slice(0, 16);

    // Tab Resultado (lo inicializa resultado.js — solo precarga el partido_id)
    $('res_id').value = b.dataset.id;

    // Tabs Tarjeta y Lesión — arma las opciones de club
    const opcionesClub = `<option value="">Seleccionar club...</option>
        <option value="${b.dataset.localId}|${b.dataset.categoriaId}">${esc(b.dataset.local)}</option>
        <option value="${b.dataset.visitanteId}|${b.dataset.categoriaId}">${esc(b.dataset.visitante)}</option>`;

    $('ev_pid_t').value = $('ev_pid_l').value = b.dataset.id;
    $('ev_club_t').innerHTML = $('ev_club_l').innerHTML = opcionesClub;
    $('ev_jugador_t').innerHTML = $('ev_jugador_l').innerHTML =
        '<option value="">Primero seleccioná un club</option>';
});

// Carga jugadores al elegir club en tab Tarjeta
$('ev_club_t')?.addEventListener('change', function () {
    cargarJugadores(this, $('ev_jugador_t'));
});

// Carga jugadores al elegir club en tab Lesión
$('ev_club_l')?.addEventListener('change', function () {
    cargarJugadores(this, $('ev_jugador_l'));
});

function cargarJugadores(selectClub, selectJugador) {
    if (!selectClub.value) {
        selectJugador.innerHTML = '<option value="">Primero seleccioná un club</option>';
        return;
    }
    const [clubId, categoriaId] = selectClub.value.split('|');
    selectJugador.innerHTML = '<option value="">Cargando...</option>';
    pedirJugadores(clubId, categoriaId)
        .then(jugadores => {
            selectJugador.innerHTML = jugadores.length
                ? '<option value="">Seleccionar...</option>' + jugadores.map(j =>
                    `<option value="${esc(j.id)}">${esc(j.apellido)}, ${esc(j.nombre)} — CI: ${esc(j.ci)}</option>`
                  ).join('')
                : '<option value="">Sin jugadores</option>';
        })
        .catch(() => selectJugador.innerHTML = '<option value="">Error</option>');
}