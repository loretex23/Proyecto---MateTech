<?php if ($esAdmin): ?>
<!-- MODAL EDITAR TRANSFERENCIA -->
<div class="modal fade" id="modalEditar" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog">
        <form method="POST" class="modal-content bg-dark text-white">
            <div class="modal-header border-secondary">
                <h5 class="modal-title"><i class="ti ti-edit"></i> Editar transferencia</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Cerrar"></button>
            </div>
            <div class="modal-body">
                <input type="hidden" name="transferencia_id" id="edit_transferencia_id">
                <p class="mb-3">Jugador: <strong id="edit_jugador"></strong></p>
                <label for="edit_club_destino" class="form-label">Nuevo club de destino</label>
                <select name="club_destino_id" id="edit_club_destino" class="form-select" required>
                    <?php foreach ($clubes as$c): ?>
                        <option value="<?= (int)$c['id'] ?>"><?= h($c['nombre']) ?></option>
                    <?php endforeach; ?>
                </select>
                <div class="form-text text-light">El club de origen no se puede elegir como destino.</div>
            </div>
            <div class="modal-footer border-secondary">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                <button type="submit" name="btn_editar" value="1" class="btn btn-success">Guardar cambios</button>
            </div>
        </form>
    </div>
</div>

<!-- MODAL VER SOLICITUD -->
<div class="modal fade" id="modalVerSol" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content bg-dark text-white">
            <div class="modal-header border-secondary">
                <h5 class="modal-title"><i class="ti ti-eye"></i> Detalle de la solicitud</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Cerrar"></button>
            </div>
            <div class="modal-body">
                <dl class="row mb-0">
                    <?php foreach ([
                        'jugador' => 'Jugador', 'ci' => 'CI', 'cat' => 'Categoría', 
                        'solicitante' => 'Club solicitante', 'origen' => 'Club actual', 
                        'estado' => 'Estado', 'fecha' => 'Fecha de solicitud', 
                        'respuesta' => 'Fecha de respuesta', 'por' => 'Respondida por'
                    ] as $k =>$l): ?>
                        <dt class="col-5"><?= $l ?></dt>
                        <dd class="col-7" id="ver_<?= $k ?>"></dd>
                    <?php endforeach; ?>
                </dl>
            </div>
            <div class="modal-footer border-secondary">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cerrar</button>
            </div>
        </div>
    </div>
</div>
<?php endif; ?>