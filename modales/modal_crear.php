<!-- Modal para crear un partido. Envía a acciones/crear_partido.php -->
<div class="modal fade" id="modalCrear" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered"><div class="modal-content"><form method="POST">
        <div class="modal-header"><h5 class="modal-title"><i class="ti ti-plus"></i> Nuevo partido</h5><button type="button" class="btn-close" data-bs-dismiss="modal"></button></div>
        <div class="modal-body">
            <div class="mb-3"><label class="form-label">Categoría</label>
                <select name="categoria_id" class="form-select" required><option value="">Seleccionar...</option>
                <?php foreach ($categorias as $c): ?><option value="<?= $c->id ?>"><?= htmlspecialchars($c->nombre) ?></option><?php endforeach; ?>
                </select></div>
            <div class="mb-3"><label class="form-label">Club local</label>
                <select name="club_local_id" class="form-select" required><option value="">Seleccionar...</option>
                <?php foreach ($clubes as $c): ?><option value="<?= $c->id ?>"><?= htmlspecialchars($c->nombre) ?></option><?php endforeach; ?>
                </select></div>
            <div class="mb-3"><label class="form-label">Club visitante</label>
                <select name="club_visitante_id" class="form-select" required><option value="">Seleccionar...</option>
                <?php foreach ($clubes as $c): ?><option value="<?= $c->id ?>"><?= htmlspecialchars($c->nombre) ?></option><?php endforeach; ?>
                </select></div>
            <div class="mb-3"><label class="form-label">Fecha y hora <small class="text-muted">(opcional)</small></label><input type="datetime-local" name="fecha_partido" class="form-control"></div>
            <div class="mb-3"><label class="form-label">Estado</label>
                <select name="estado" class="form-select"><?php foreach ($estados as $v => $l): ?><option value="<?= $v ?>"><?= $l ?></option><?php endforeach; ?></select></div>
        </div>
        <div class="modal-footer"><button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button><button type="submit" name="btn_crear" value="1" class="btn btn-primary">Crear</button></div>
    </form></div></div>
</div>
