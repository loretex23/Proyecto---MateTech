/* tarjetas.js
   Lógica del tab "Tarjetas" con reglas estrictas por jugador. */

function filaTarjeta(opcionesJugadores, esAutomatica = false) {
    const readonly = esAutomatica ? 'readonly disabled' : '';
    const nameSfx = esAutomatica ? '' : '[]'; // Las automáticas no se envían al backend
    return `<div class="gol-row mb-2 align-items-center tar-row ${esAutomatica ? 'auto-red opacity-75' : ''}">
        <span class="text-muted fw-bold" style="font-size:11px;">TARJETA${esAutomatica ? ' (Auto)' : ''}</span>
        ${!esAutomatica ? `<button type="button" class="btn btn-sm btn-danger btn-remove p-0 d-flex align-items-center justify-content-center" style="width:22px;height:22px"><i class="ti ti-x"></i></button>` : '<span style="width:22px"></span>'}
        <select name="${esAutomatica ? '' : 'tar_jugador[]'}" class="form-select form-select-sm tar-jugador" ${readonly}>${opcionesJugadores}</select>
        <select name="${esAutomatica ? '' : 'tar_tipo[]'}" class="form-select form-select-sm tar-tipo" ${readonly} style="flex:1">
            <option value="amarilla" ${esAutomatica ? 'disabled' : ''}>🟨 Amarilla</option>
            <option value="roja" ${esAutomatica ? 'selected' : ''}>🟥 Roja</option>
        </select>
        <input type="number" name="${esAutomatica ? '' : 'tar_minuto[]'}" class="form-control form-control-sm tar-min" min="1" max="120" placeholder="Min" required ${readonly}>
    </div>`;
}

function obtenerEstadoTarjetas(contenedor) {
    const estado = {}; // { jid: { am: 0, ro: 0 } }
    contenedor.querySelectorAll('.tar-row:not(.auto-red)').forEach(row => {
        const jid = row.querySelector('.tar-jugador').value;
        const tipo = row.querySelector('.tar-tipo').value;
        if (!jid) return;
        if (!estado[jid]) estado[jid] = { am: 0, ro: 0 };
        if (tipo === 'amarilla') estado[jid].am++;
        if (tipo === 'roja') estado[jid].ro++;
    });
    return estado;
}

function validarYActualizarTarjetas(contenedor, opcionesJugadores) {
    // 1. Quitar todas las rojas automáticas primero para recalcular
    contenedor.querySelectorAll('.auto-red').forEach(r => r.remove());

    const estado = obtenerEstadoTarjetas(contenedor);
    let hayErrores = false;

    // 2. Procesar cada fila manual
    contenedor.querySelectorAll('.tar-row:not(.auto-red)').forEach(row => {
        const selectJugador = row.querySelector('.tar-jugador');
        const selectTipo = row.querySelector('.tar-tipo');
        const inputMin = row.querySelector('.tar-min');
        const jid = selectJugador.value;

        // Deshabilitar opciones no válidas para cada select
        Array.from(selectJugador.options).forEach(opt => {
            const id = opt.value;
            if (!id) return;
            const e = estado[id] || { am: 0, ro: 0 };
            const tipoRow = selectTipo.value;
            // Si es amarilla y ya tiene 2 (otras), o tiene roja directa -> inhabilitar
            let disable = false;
            if (id !== jid) {
                if (tipoRow === 'amarilla' && (e.am >= 2 || e.ro >= 1)) disable = true;
                if (tipoRow === 'roja' && (e.ro >= 1 || e.am >= 2)) disable = true;
            }
            opt.disabled = disable;
        });

        if (jid) {
            const e = estado[jid];
            // Validar error en la fila actual
            const tipoRow = selectTipo.value;
            const esInvalido = (tipoRow === 'amarilla' && (e.am > 2 || e.ro > 0)) ||
                               (tipoRow === 'roja' && (e.ro > 1 || e.am >= 2));
            
            if (esInvalido) {
                row.style.border = '2px solid red';
                hayErrores = true;
            } else {
                row.style.border = '1px solid #e5e7eb';
            }

            // Si tiene 2 amarillas válidas y esta es la segunda, agregar roja automática
            if (e.am === 2 && tipoRow === 'amarilla' && !esInvalido) {
                const isUltimaAmarilla = Array.from(contenedor.querySelectorAll('.tar-row:not(.auto-red)'))
                    .filter(r => r.querySelector('.tar-jugador').value === jid && r.querySelector('.tar-tipo').value === 'amarilla')
                    .pop() === row;

                if (isUltimaAmarilla) {
                    const min = parseInt(inputMin.value) || 0;
                    row.insertAdjacentHTML('afterend', filaTarjeta(opcionesJugadores, true));
                    const rowAuto = row.nextElementSibling;
                    rowAuto.querySelector('.tar-jugador').value = jid;
                    rowAuto.querySelector('.tar-min').value = min ? min + 1 : '';
                }
            }
        }
    });

    $('btn_save_tarjetas').disabled = hayErrores;
    $(contenedor.id === 'wrap_tar_l' ? 'bdg_tar_l' : 'bdg_tar_v').textContent = contenedor.querySelectorAll('.tar-row:not(.auto-red)').length;

    // Reactivar eventos para las filas
    contenedor.querySelectorAll('.btn-remove').forEach(btn => {
        btn.onclick = () => {
            btn.closest('.tar-row').remove();
            validarYActualizarTarjetas(contenedor, opcionesJugadores);
        };
    });
    contenedor.querySelectorAll('.tar-jugador, .tar-tipo, .tar-min').forEach(input => {
        input.onchange = () => validarYActualizarTarjetas(contenedor, opcionesJugadores);
    });
}

function agregarTarjeta(idContenedor, opcionesJugadores) {
    const contenedor = $(idContenedor);
    contenedor.insertAdjacentHTML('beforeend', filaTarjeta(opcionesJugadores));
    validarYActualizarTarjetas(contenedor, opcionesJugadores);
}

$('add_tar_l')?.addEventListener('click', () => agregarTarjeta('wrap_tar_l', opcionesDeJugadores(jugadoresLocal)));
$('add_tar_v')?.addEventListener('click', () => agregarTarjeta('wrap_tar_v', opcionesDeJugadores(jugadoresVisitante)));

$('modalGestionar')?.addEventListener('show.bs.modal', async e => {
    const boton = e.relatedTarget;
    $('tar_id').value = boton.dataset.id;
    $('tar_local_lbl').textContent = boton.dataset.local;
    $('tar_visit_lbl').textContent = boton.dataset.visitante;

    $('wrap_tar_l').innerHTML = $('wrap_tar_v').innerHTML = '';
    $('bdg_tar_l').textContent = $('bdg_tar_v').textContent = '0';

    try {
        const detalle = await fetch('../sql/obtener_detalle_partido.php?id=' + boton.dataset.id).then(r => r.json());
        const idLocal = String(boton.dataset.localId);
        const tarLocal = detalle.sanciones.filter(t => String(t.club_id) === idLocal);
        const tarVisit = detalle.sanciones.filter(t => String(t.club_id) !== idLocal);

        await new Promise(r => setTimeout(r, 100)); // Esperar jugadores

        const llenar = (contenedor, datos, opciones) => {
            datos.forEach(tar => {
                contenedor.insertAdjacentHTML('beforeend', filaTarjeta(opciones));
                const fila = contenedor.lastElementChild;
                fila.querySelector('.tar-jugador').value = tar.jugador_id || '';
                fila.querySelector('.tar-tipo').value = tar.tipo_tarjeta || 'amarilla';
                fila.querySelector('.tar-min').value = tar.minuto || '';
            });
            validarYActualizarTarjetas(contenedor, opciones);
        };

        llenar($('wrap_tar_l'), tarLocal, opcionesDeJugadores(jugadoresLocal));
        llenar($('wrap_tar_v'), tarVisit, opcionesDeJugadores(jugadoresVisitante));
    } catch (_) {}
});
