<!-- Modal para editar estado/fecha. Se llena con JS (js/comun.js) al abrirse.
     Envía a acciones/cambiar_estado.php -->
<div class="modal fade" id="modalEstado" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered"><div class="modal-content"><form method="POST">
        <input type="hidden" name="partido_id" id="est_id">
        <div class="modal-header"><h5 class="modal-title"><i class="ti ti-edit"></i> Editar partido</h5><button type="button" class="btn-close" data-bs-dismiss="modal"></button></div>
        <div class="modal-body">
            <div class="mb-3"><label class="form-label">Estado</label><select name="estado" id="est_select" class="form-select"><?php foreach ($estados as $v => $l): ?><option value="<?= $v ?>"><?= $l ?></option><?php endforeach; ?></select></div>
            <div class="mb-3"><label class="form-label">Fecha y hora</label><input type="datetime-local" name="fecha_partido" id="est_fecha" class="form-control"></div>
        </div>
        <div class="modal-footer"><button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button><button type="submit" name="btn_estado" value="1" class="btn btn-warning">Guardar</button></div>
    </form></div></div>
</div>
