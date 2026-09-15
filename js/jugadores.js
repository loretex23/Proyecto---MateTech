document.addEventListener('DOMContentLoaded', function() {
    document.getElementById('modalEditar')?.addEventListener('show.bs.modal', function(e) {
        fetch('../sql/obtener_jugador.php?id=' + e.relatedTarget.dataset.id)
            .then(r => r.json())
            .then(j => {
                if (j.error) return alert(j.error);
                ['id','nombre','apellido','ci','fecha_nacimiento','carnet_vencimiento','masa','altura'].forEach(k => {
                    const el = document.getElementById('editar_' + k);
                    if (el) el.value = j[k] ?? '';
                });
                document.getElementById('editar_foto_actual').src = '../' + (j.foto_url || '');
                document.getElementById('editar_club_id').value     = j.club_id ?? '';
                document.getElementById('editar_categoria_id').value = j.categoria_id ?? '';
            })
            .catch(() => alert('No se pudieron cargar los datos del jugador.'));
    });

    document.getElementById('modalEditarFecha')?.addEventListener('show.bs.modal', function(e) {
        document.getElementById('editar_id_fecha').value = e.relatedTarget.dataset.id;
        document.getElementById('editar_carnet_vencimiento_fecha').value = e.relatedTarget.dataset.vencimiento || '';
    });

    document.getElementById('editar_foto')?.addEventListener('change', function(e) {
        const f = e.target.files[0];
        if (!f) return;
        const r = new FileReader();
        r.onload = ev => document.getElementById('editar_foto_actual').src = ev.target.result;
        r.readAsDataURL(f);
    });

    document.getElementById('modalCarnet')?.addEventListener('show.bs.modal', function(e) {
        fetch('../sql/obtener_jugador.php?id=' + e.relatedTarget.dataset.id)
            .then(r => r.json())
            .then(j => {
                if (j.error) return alert(j.error);
                document.getElementById('carnet_foto').src                    = '../' + (j.foto_url || '');
                document.getElementById('carnet_nombre').textContent           = j.nombre || '';
                document.getElementById('carnet_apellido').textContent         = j.apellido || '';
                document.getElementById('carnet_ci').textContent               = j.ci || '';
                document.getElementById('carnet_nacimiento').textContent       = j.fecha_nacimiento || '';
                document.getElementById('carnet_club').textContent              = j.club_id || '';
                document.getElementById('carnet_categoria').textContent         = j.categoria_nombre || '';
                document.getElementById('carnet_fecha_vencimiento').textContent = j.carnet_vencimiento || 'Sin fecha asignada';
                document.getElementById('carnet_masa').textContent             = j.masa    ? j.masa + 'kg'   : '—';
                document.getElementById('carnet_altura').textContent           = j.altura  ? j.altura + 'm' : '—';
                document.getElementById('carnet_fuerza_peso').textContent      = j.fuerza_peso + 'N ';
            })
            .catch(() => alert('No se pudieron cargar los datos del jugador.'));
    });
});