/* evento.js
   Todo lo relacionado al modal compartido de "Tarjeta / Lesión".
   Necesita que comun.js esté cargado antes. */

const CONFIGURACION_EVENTO = {
    tarjeta: {
        titulo: 'Registrar tarjeta', boton: 'Registrar tarjeta', clase: 'btn-danger', nombreInput: 'btn_tarjeta',
        opciones: '<option value="amarilla">🟨 Amarilla</option><option value="roja">🟥 Roja</option>',
        tieneDescripcion: false,
    },
    lesion: {
        titulo: 'Registrar lesión', boton: 'Registrar lesión', clase: 'btn-secondary', nombreInput: 'btn_lesion',
        opciones: '', tieneDescripcion: true,
    },
};

// Al elegir un club en el modal, se cargan sus jugadores por AJAX
$('ev_club')?.addEventListener('change', function () {
    const selectJugador = $('ev_jugador');
    if (!this.value) {
        selectJugador.innerHTML = '<option value="">Primero seleccioná un club</option>';
        return;
    }
    const [clubId, categoriaId] = this.value.split('|');
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
});

// Al abrir el modal, se configura según sea "tarjeta" o "lesión"
$('modalEvento')?.addEventListener('show.bs.modal', e => {
    const boton = e.relatedTarget;
    const tipoEvento = boton.dataset.evento;
    const config = CONFIGURACION_EVENTO[tipoEvento];

    $('ev_pid').value = boton.dataset.id;
    $('ev_titulo').innerHTML = config.titulo;
    $('ev_submit').textContent = config.boton;
    $('ev_submit').className = 'btn ' + config.clase;
    $('ev_submit').name = config.nombreInput;
    $('ev_tipo').innerHTML = config.opciones;
    $('ev_tipo_wrap').classList.toggle('d-none', tipoEvento === 'lesion');
    $('ev_desc_wrap').classList.toggle('d-none', !config.tieneDescripcion);

    $('ev_club').innerHTML = `<option value="">Seleccionar club...</option>
        <option value="${boton.dataset.localId}|${boton.dataset.categoriaId}">${esc(boton.dataset.local)}</option>
        <option value="${boton.dataset.visitanteId}|${boton.dataset.categoriaId}">${esc(boton.dataset.visitante)}</option>`;
    $('ev_jugador').innerHTML = '<option value="">Primero seleccioná un club</option>';
});
