/* comun.js
   Funciones que usan los otros archivos JS (resultado.js, evento.js, linea_de_tiempo.js).
   Este archivo se debe cargar PRIMERO. */

const $ = id => document.getElementById(id);

const esc = s => String(s ?? '').replace(/[&<>"']/g, c => ({
    '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#039;'
}[c]));

// Pide por AJAX la lista de jugadores de un club en una categoría
function pedirJugadores(clubId, categoriaId) {
    return fetch(`../sql/obtener_jugadores_club.php?club_id=${clubId}&categoria_id=${categoriaId}`)
        .then(r => r.json());
}

/* ---- Modal "Editar estado" ---- */
$('modalEstado')?.addEventListener('show.bs.modal', e => {
    const boton = e.relatedTarget;
    $('est_id').value     = boton.dataset.id;
    $('est_select').value = boton.dataset.estado;
    $('est_fecha').value  = (boton.dataset.fecha || '').slice(0, 16);
});
