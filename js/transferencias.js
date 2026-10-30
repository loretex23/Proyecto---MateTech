document.addEventListener('DOMContentLoaded', () => {
    // Lógica para vista de Clubes (Filtros de mercado de pases)
    const filas = [...document.querySelectorAll('tr[data-jugador]')];
    const cuerpo = filas[0]?.parentElement;
    const fNombre = document.getElementById('filtro_nombre_jugador');
    const fCi = document.getElementById('filtro_ci_jugador');
    const btnMas = document.getElementById('btn_mostrar_jugadores');
    const sinRes = document.getElementById('sin_resultados_filtro');
    let mostrarTodos = false;

    if (cuerpo && fNombre && fCi) {
        const lc = s => s.toLocaleLowerCase('es');
        const actualizar = () => {
            const n = lc(fNombre.value.trim());
            const c = lc(fCi.value.trim());
            const filtrados = filas.filter(f => lc(f.dataset.nombre).includes(n) && lc(f.dataset.ci).includes(c));
            const visibles = mostrarTodos ? filtrados : filtrados.slice(0, 3);
            
            cuerpo.querySelectorAll('.grupo-jugadores').forEach(g => g.remove());
            filas.forEach(f => f.hidden = true);
            
            let grupoActual = null;
            visibles.forEach(f => {
                const grupo = `${f.dataset.categoria}\u0000${f.dataset.club}`;
                if (grupo !== grupoActual) {
                    grupoActual = grupo;
                    const tr = document.createElement('tr');
                    tr.className = 'grupo-jugadores';
                    const th = document.createElement('th');
                    th.colspan = 4;
                    th.scope = 'colgroup';
                    th.textContent = `Categoría: ${f.dataset.categoria} - ${f.dataset.club}`;
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

    // Lógica para vista de Administrador (Modales y Selects dinámicos)
    const modalEditar = document.getElementById('modalEditar');
    if (modalEditar) {
        const $id = id => document.getElementById(id);
        const selCategoria = $id('filtro_categoria');
        const selJugador = $id('jugador_id');
        const selDestino = $id('club_destino_id');

        if (selCategoria && window.jugadoresAdmin) {
            const renderJugadores = () => {
                const cat = parseInt(selCategoria.value, 10);
                selJugador.innerHTML = '';
                const ph = new Option('Seleccionar jugador', '', true, true);
                ph.disabled = true;
                selJugador.add(ph);
                
                const lista = window.jugadoresAdmin.filter(j => cat === 0 || parseInt(j.categoria_id, 10) === cat);
                let clubActual = null, grupo = null;
                
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
            };

            const actualizarDestinos = () => {
                const clubActual = selJugador.selectedOptions[0]?.dataset.club;
                [...selDestino.options].forEach(o => {
                    o.disabled = (o.value !== '' && o.value === clubActual);
                });
                if (selDestino.selectedOptions[0]?.disabled) selDestino.value = '';
            };

            selCategoria.addEventListener('change', renderJugadores);
            selJugador.addEventListener('change', actualizarDestinos);
            renderJugadores();
        }

        modalEditar.addEventListener('show.bs.modal', e => {
            const b = e.relatedTarget;
            const sel = $id('edit_club_destino');
            $id('edit_transferencia_id').value = b.dataset.id;
            $id('edit_jugador').textContent = b.dataset.jugador;
            [...sel.options].forEach(o => { o.disabled = (o.value === b.dataset.origenId); });
            sel.value = b.dataset.destinoId;
        });

        const modalVerSol = document.getElementById('modalVerSol');
        if (modalVerSol) {
            modalVerSol.addEventListener('show.bs.modal', e => {
                ['jugador', 'ci', 'fecha', 'respuesta', 'cat', 'solicitante', 'origen', 'estado', 'por']
                    .forEach(k => { $id('ver_' + k).textContent = e.relatedTarget.dataset[k] || '-'; });
            });
        }
    }
});