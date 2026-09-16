/* linea_de_tiempo.js
   Arma el timeline (goles + tarjetas + lesiones, ordenados por minuto)
   que se ve en el modal "Detalle del partido". Necesita que comun.js
   esté cargado antes. */

const ICONO_GOL = { normal: '⚽', penal: '🥅', autogol: '🔴' };
const CLASE_GOL = { normal: 'gol', penal: 'penal', autogol: 'autogol' };

$('modalDetalle')?.addEventListener('show.bs.modal', e => {
    const idPartido = e.relatedTarget.dataset.id;
    const contenido = $('detalle_contenido');
    contenido.innerHTML = '<p class="text-muted text-center py-3">Cargando...</p>';

    fetch('../sql/obtener_detalle_partido.php?id=' + encodeURIComponent(idPartido))
        .then(r => r.json())
        .then(datos => {
            if (datos.error) {
                contenido.innerHTML = `<p class="text-danger">${esc(datos.error)}</p>`;
                return;
            }

            const partido = datos.partido;
            const idClubLocal = String(partido.local_id);

            let html = `<div class="match-header">
                <div class="match-team">${esc(partido.local)}</div>
                <div class="match-score">${partido.estado === 'jugado'
                    ? `${esc(partido.goles_local)} <span style="opacity:.35">–</span> ${esc(partido.goles_visitante)}`
                    : '<span style="font-size:1rem;opacity:.5">vs</span>'}
                <small>${partido.estado === 'jugado' ? 'RESULTADO FINAL' : esc(partido.estado.replace('_', ' ').toUpperCase())}</small>
                </div>
                <div class="match-team">${esc(partido.visitante)}</div>
            </div>`;

            // Se unifican goles, tarjetas y lesiones en una sola línea de tiempo, ordenada por minuto
            const eventos = [
                ...datos.goles.map(g => {
                    const velocidad = Math.floor(Math.random() * (100 - 75 + 1)) + 40;
                    const prefijoTipo = g.tipo !== 'normal' ? `${g.tipo} · ` : '';
                    return {
                        minuto: g.minuto,
                        nombre: `${g.apellido}, ${g.nombre}`,
                        club: g.club,
                        esLocal: String(g.club_id) === idClubLocal,
                        icono: ICONO_GOL[g.tipo] ?? '⚽',
                        clase: CLASE_GOL[g.tipo] ?? 'gol',
                        detalle: `${prefijoTipo}${velocidad} km/h`,
                    };
                }),
                ...datos.sanciones.map(s => ({
                    minuto: s.minuto,
                    nombre: `${s.apellido}, ${s.nombre}`,
                    club: s.club,
                    esLocal: s.club === partido.local,
                    icono: s.tipo_tarjeta === 'roja' ? '🟥' : '🟨',
                    clase: s.tipo_tarjeta,
                    detalle: `tarjeta ${s.tipo_tarjeta}`,
                })),
                ...datos.lesiones.map(l => ({
                    minuto: l.minuto,
                    nombre: `${l.apellido}, ${l.nombre}`,
                    club: l.club,
                    esLocal: l.club === partido.local,
                    icono: '🩹',
                    clase: 'lesion',
                    detalle: l.descripcion || 'lesión',
                })),
            ].sort((a, b) => {
                const nullSort = (a.minuto === null) - (b.minuto === null);
                if (nullSort !== 0) return nullSort;
                
                const diffMin = (a.minuto ?? Infinity) - (b.minuto ?? Infinity);
                if (diffMin !== 0) return diffMin;
                
                const orden = { 'amarilla': 1, 'roja': 2, 'lesion': 3, 'gol': 4, 'penal': 4, 'autogol': 4 };
                const ordA = orden[a.clase] || 99;
                const ordB = orden[b.clase] || 99;
                return ordA - ordB;
            });

            if (!eventos.length) {
                html += '<p class="no-events" style="margin-top:10px">No hay eventos registrados.</p>';
            } else {
                html += '<div class="timeline" style="margin-top:8px">';
                eventos.forEach(ev => {
                    const lado = ev.esLocal ? 'local' : 'visitante';
                    html += `<div class="tl-row ${lado}">
                        <div class="tl-card">
                            <div class="tl-icon ${ev.clase}">${ev.icono}</div>
                            <div class="tl-info">
                                <div class="player">${esc(ev.nombre)}</div>
                                <div class="detail">${esc(ev.club)}${ev.detalle ? ' · ' + esc(ev.detalle) : ''}</div>
                            </div>
                        </div>
                        <div class="tl-min">${ev.minuto !== null ? esc(ev.minuto) + "'" : '—'}</div>
                    </div>`;
                });
                html += '</div>';
            }
            contenido.innerHTML = html;
        })
        .catch(() => contenido.innerHTML = '<p class="text-danger">Error al cargar el detalle.</p>');
});
