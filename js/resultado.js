/* resultado.js
   Todo lo relacionado al modal "Resultado y goles": arma las filas de gol,
   deja agregar/quitar goles y carga los goles ya guardados si el partido
   ya tenía resultado. Necesita que comun.js esté cargado antes. */

let jugadoresLocal = [];
let jugadoresVisitante = [];

function opcionesDeJugadores(lista) {
    const opciones = lista.map(j => `<option value="${esc(j.id)}">${esc(j.apellido)}, ${esc(j.nombre)}</option>`);
    return '<option value="">Sin asignar</option>' + opciones.join('');
}

// Fila de "gol" dentro del modal de resultado
function filaDeGol(opcionesJugadores) {
    return `<div class="gol-row mb-3 p-2" style="background:#f8fafc;border-radius:8px;border:1px solid #e5e7eb">
        <div class="d-flex justify-content-between align-items-center mb-1">
            <small class="text-muted fw-bold">GOL</small>
            <button type="button" class="btn btn-sm btn-outline-danger btn-remove py-0 px-1"><i class="ti ti-x"></i></button>
        </div>
        <select name="gol_jugador[]" class="form-select form-select-sm mb-1">${opcionesJugadores}</select>
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

// Hace que el botón "X" de cada fila de gol la borre
function activarBotonesBorrar(contenedor) {
    contenedor.querySelectorAll('.btn-remove').forEach(boton => {
        boton.onclick = () => boton.closest('.gol-row').remove();
    });
}

// Redibuja "cantidad" filas de gol vacías dentro de un contenedor
function redibujarFilas(contenedor, cantidad, opcionesJugadores) {
    contenedor.innerHTML = '';
    for (let i = 0; i < cantidad; i++) {
        contenedor.insertAdjacentHTML('beforeend', filaDeGol(opcionesJugadores));
    }
    activarBotonesBorrar(contenedor);
}

// Agrega una fila de gol suelta y actualiza el contador/badge
function agregarGol(idContenedor, idBadge, idInputCantidad, opcionesJugadores) {
    $(idContenedor).insertAdjacentHTML('beforeend', filaDeGol(opcionesJugadores));
    activarBotonesBorrar($(idContenedor));
    $(idInputCantidad).value = parseInt($(idInputCantidad).value || 0) + 1;
    $(idBadge).textContent = $(idInputCantidad).value;
}

// Cuando cambia el número de goles del local/visitante, se ajustan las filas
$('res_gl')?.addEventListener('input', function () {
    const cantidad = Math.max(0, parseInt(this.value) || 0);
    $('bdg_l').textContent = cantidad;
    redibujarFilas($('wrap_l'), cantidad, opcionesDeJugadores(jugadoresLocal));
});
$('res_gv')?.addEventListener('input', function () {
    const cantidad = Math.max(0, parseInt(this.value) || 0);
    $('bdg_v').textContent = cantidad;
    redibujarFilas($('wrap_v'), cantidad, opcionesDeJugadores(jugadoresVisitante));
});
$('add_l')?.addEventListener('click', () => agregarGol('wrap_l', 'bdg_l', 'res_gl', opcionesDeJugadores(jugadoresLocal)));
$('add_v')?.addEventListener('click', () => agregarGol('wrap_v', 'bdg_v', 'res_gv', opcionesDeJugadores(jugadoresVisitante)));

/* Al abrir el modal: trae los jugadores de ambos clubes y, si ya había
   resultado cargado, trae también los goles guardados para poder editarlos. */
$('modalResultado')?.addEventListener('show.bs.modal', async e => {
    const boton = e.relatedTarget;
    $('res_id').value = boton.dataset.id;
    $('res_local_nombre').textContent = $('res_local_lbl').textContent = boton.dataset.local;
    $('res_visit_nombre').textContent = $('res_visit_lbl').textContent = boton.dataset.visitante;

    const golesLocal = parseInt(boton.dataset.golesLocal) || 0;
    const golesVisit = parseInt(boton.dataset.golesVisitante) || 0;
    $('res_gl').value = $('bdg_l').textContent = golesLocal;
    $('res_gv').value = $('bdg_v').textContent = golesVisit;
    $('wrap_l').innerHTML = $('wrap_v').innerHTML = '';

    [jugadoresLocal, jugadoresVisitante] = await Promise.all([
        pedirJugadores(boton.dataset.localId, boton.dataset.categoriaId),
        pedirJugadores(boton.dataset.visitanteId, boton.dataset.categoriaId),
    ]);

    if (golesLocal > 0 || golesVisit > 0) {
        try {
            const detalle = await fetch('../sql/obtener_detalle_partido.php?id=' + boton.dataset.id).then(r => r.json());
            const idLocal = String(boton.dataset.localId);
            const golesDelLocal     = detalle.goles.filter(g => String(g.club_id) === idLocal);
            const golesDelVisitante = detalle.goles.filter(g => String(g.club_id) !== idLocal);

            const llenarFilas = (contenedor, golesGuardados, jugadores, totalFilas) => {
                golesGuardados.forEach(gol => {
                    contenedor.insertAdjacentHTML('beforeend', filaDeGol(opcionesDeJugadores(jugadores)));
                    const fila = contenedor.lastElementChild;
                    fila.querySelector('[name="gol_jugador[]"]').value = gol.jugador_id || '';
                    fila.querySelector('[name="gol_tipo[]"]').value = gol.tipo || 'normal';
                    fila.querySelector('[name="gol_minuto[]"]').value = gol.minuto || '';
                });
                for (let i = golesGuardados.length; i < totalFilas; i++) {
                    contenedor.insertAdjacentHTML('beforeend', filaDeGol(opcionesDeJugadores(jugadores)));
                }
                activarBotonesBorrar(contenedor);
            };

            llenarFilas($('wrap_l'), golesDelLocal, jugadoresLocal, golesLocal);
            llenarFilas($('wrap_v'), golesDelVisitante, jugadoresVisitante, golesVisit);
        } catch (_) {
            // Si falla la carga del detalle, al menos se muestran filas vacías
            redibujarFilas($('wrap_l'), golesLocal, opcionesDeJugadores(jugadoresLocal));
            redibujarFilas($('wrap_v'), golesVisit, opcionesDeJugadores(jugadoresVisitante));
        }
    }
});
