<div class="modal fade" id="modalEvento" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg"><div class="modal-content"><form method="POST">
        <input type="hidden" name="partido_id" id="ev_pid">
        <div class="modal-header"><h5 class="modal-title" id="ev_titulo"></h5><button type="button" class="btn-close" data-bs-dismiss="modal"></button></div>
        <div class="modal-body">
            <div class="mb-3"><label class="form-label">Club</label><select id="ev_club" class="form-select"><option value="">Seleccionar club...</option></select></div>
            <div class="mb-3"><label class="form-label">Jugador</label><select name="jugador_id" id="ev_jugador" class="form-select" required><option value="">Primero seleccioná un club</option></select></div>
            <div class="row">
                <div class="col-md-6 mb-3" id="ev_tipo_wrap"><label class="form-label" id="ev_tipo_lbl">Tipo</label><select name="tipo_tarjeta" id="ev_tipo" class="form-select"></select></div>
                <div class="col-md-6 mb-3"><label class="form-label">Minuto <small class="text-muted">(opcional)</small></label><input type="number" name="minuto" class="form-control" min="1" max="120" placeholder="Ej: 45"></div>
                <div class="col-12 mb-3 d-none" id="ev_desc_wrap"><label class="form-label">Descripción <small class="text-muted">(opcional)</small></label><input type="text" name="descripcion" class="form-control" placeholder="Ej: Esguince de tobillo"></div>
            </div>
        </div>
        <div class="modal-footer"><button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button><button type="submit" id="ev_submit" class="btn"></button></div>
    </form></div></div>
</div>
