<!-- Modal Crear Partido -->
<div class="modal fade" id="modalCrear" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title fw-bold"><i class="ti ti-plus"></i> Nuevo partido</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <form method="POST">
        <div class="modal-body">
          <div class="mb-3">
            <label class="form-label fw-semibold">Competición</label>
            <select name="competencia" class="form-select" required>
              <option value="Liga" selected>Liga</option>
              <option value="Copa de Oro">Copa de Oro</option>
              <option value="Copa de Plata">Copa de Plata</option>
            </select>
          </div>

          <div class="mb-3">
            <label class="form-label fw-semibold">Categoría</label>
            <select name="categoria_id" class="form-select" required>
              <?php foreach ($categorias as $cat): ?>
                <option value="<?= $cat->id ?>"><?= htmlspecialchars($cat->nombre) ?></option>
              <?php endforeach; ?>
            </select>
          </div>

          <div class="row g-2 mb-3">
            <div class="col-6">
              <label class="form-label fw-semibold">Club Local</label>
              <select name="club_local_id" class="form-select" required>
                <?php foreach ($clubes as $c): ?>
                  <option value="<?= $c->id ?>"><?= htmlspecialchars($c->nombre) ?></option>
                <?php endforeach; ?>
              </select>
            </div>
            <div class="col-6">
              <label class="form-label fw-semibold">Club Visitante</label>
              <select name="club_visitante_id" class="form-select" required>
                <?php foreach ($clubes as $c): ?>
                  <option value="<?= $c->id ?>"><?= htmlspecialchars($c->nombre) ?></option>
                <?php endforeach; ?>
              </select>
            </div>
          </div>

          <div class="mb-3">
            <label class="form-label fw-semibold">Fecha y Hora</label>
            <input type="datetime-local" name="fecha_partido" class="form-control">
          </div>

          <div class="mb-3">
            <label class="form-label fw-semibold">Estado</label>
            <select name="estado" class="form-select">
              <?php foreach ($estados as $val => $lbl): ?>
                <option value="<?= $val ?>" <?= $val === 'sin_fecha' ? 'selected' : '' ?>><?= $lbl ?></option>
              <?php endforeach; ?>
            </select>
          </div>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
          <button type="submit" name="btn_crear" class="btn btn-primary">
            <i class="ti ti-device-floppy"></i> Guardar partido
          </button>
        </div>
      </form>
    </div>
  </div>
</div>