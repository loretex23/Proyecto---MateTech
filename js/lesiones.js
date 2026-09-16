/* lesiones.js
   Lógica del tab "Lesiones" en el modal de gestión. */

function filaLesion(opcionesJugadores) {
    return `<div class="gol-row mb-2 align-items-center">
        <span class="text-muted fw-bold" style="font-size:11px;">LESIÓN</span>
        <button type="button" class="btn btn-sm btn-secondary btn-remove p-0 d-flex align-items-center justify-content-center" style="width:22px;height:22px"><i class="ti ti-x"></i></button>
        <select name="les_jugador[]" class="form-select form-select-sm">${opcionesJugadores}</select>
        <input type="text" name="les_desc[]" class="form-control form-control-sm" placeholder="Descripción (opcional)" style="flex:1">
        <input type="number" name="les_minuto[]" class="form-control form-control-sm" min="1" max="120" placeholder="Min" required>
    </div>`;
}

function redibujarLesiones(contenedor, idBadge) {
    $(idBadge).textContent = contenedor.children.length;
    contenedor.querySelectorAll('.btn-remove').forEach(btn => {
        btn.onclick = () => {
            btn.closest('.gol-row').remove();
            $(idBadge).textContent = contenedor.children.length;
        };
    });
}

function agregarLesion(idContenedor, idBadge, jugadores) {
    $(idContenedor).insertAdjacentHTML('beforeend', filaLesion(opcionesDeJugadores(jugadores)));
    redibujarLesiones($(idContenedor), idBadge);
}

$('add_les_l')?.addEventListener('click', () => agregarLesion('wrap_les_l', 'bdg_les_l', jugadoresLocal));
$('add_les_v')?.addEventListener('click', () => agregarLesion('wrap_les_v', 'bdg_les_v', jugadoresVisitante));

$('modalGestionar')?.addEventListener('show.bs.modal', async e => {
    const boton = e.relatedTarget;
    $('les_id').value = boton.dataset.id;
    $('les_local_lbl').textContent = boton.dataset.local;
    $('les_visit_lbl').textContent = boton.dataset.visitante;

    $('wrap_les_l').innerHTML = $('wrap_les_v').innerHTML = '';
    $('bdg_les_l').textContent = $('bdg_les_v').textContent = '0';

    try {
        const detalle = await fetch('../sql/obtener_detalle_partido.php?id=' + boton.dataset.id).then(r => r.json());
        const idLocal = String(boton.dataset.localId);
        const lesLocal = detalle.lesiones.filter(l => String(l.club_id) === idLocal);
        const lesVisit = detalle.lesiones.filter(l => String(l.club_id) !== idLocal);

        // Espera a que los jugadores estén listos (los carga resultado.js)
        await new Promise(r => setTimeout(r, 100)); // Hack rápido, ideal usar Promise compartida

        const llenar = (contenedor, idBadge, datos, jugadores) => {
            datos.forEach(les => {
                contenedor.insertAdjacentHTML('beforeend', filaLesion(opcionesDeJugadores(jugadores)));
                const fila = contenedor.lastElementChild;
                fila.querySelector('[name="les_jugador[]"]').value = les.jugador_id || '';
                fila.querySelector('[name="les_desc[]"]').value = les.descripcion || '';
                fila.querySelector('[name="les_minuto[]"]').value = les.minuto || '';
            });
            redibujarLesiones(contenedor, idBadge);
        };

        llenar($('wrap_les_l'), 'bdg_les_l', lesLocal, jugadoresLocal);
        llenar($('wrap_les_v'), 'bdg_les_v', lesVisit, jugadoresVisitante);
    } catch (_) {}
});
