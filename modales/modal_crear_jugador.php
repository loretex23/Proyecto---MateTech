<!-- Modal: Crear jugador (Admin) -->
<div class="modal fade" id="modalCrear" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content modal-login-caja">
            <div class="modal-header">
                <h5 class="modal-title">Agregar Jugador</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <form method="POST" enctype="multipart/form-data">
                    <?php foreach ([
                        ['nombre',            'Nombre',              'text'],
                        ['apellido',          'Apellido',            'text'],
                        ['ci',                'Cédula de Identidad', 'text'],
                        ['fecha_nacimiento',  'Fecha de Nacimiento', 'date'],
                        ['carnet_vencimiento','Carnet de Vencimiento','date'],
                    ] as [$name, $label, $type]): ?>
                        <div class="mb-3">
                            <label for="crear_<?= $name ?>" class="form-label"><?= $label ?></label>
                            <input type="<?= $type ?>" class="form-control" id="crear_<?= $name ?>"
                                name="<?= $name ?>" required>
                        </div>
                    <?php endforeach; ?>

                    <div class="mb-3">
                        <label for="crear_club_id" class="form-label">Club</label>
                        <select class="form-select" id="crear_club_id" name="club_id" required>
                            <?= options_club($clubes) ?>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label for="crear_categoria_id" class="form-label">Categoría</label>
                        <select class="form-select" id="crear_categoria_id" name="categoria_id" required>
                            <?= options_cat($categorias) ?>
                        </select>
                    </div>

                    <div class="row g-2 mb-3">
                        <div class="col-md-4">
                            <label class="form-label">Peso (kg) <small class="text-muted">opcional</small></label>
                            <input type="number" step="0.01" min="0" class="form-control" name="masa" placeholder="Ej: 72.5">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Altura (m) <small class="text-muted">opcional</small></label>
                            <input type="number" step="0.01" min="0" max='3.00' class="form-control" name="altura" placeholder="Ej: 1.75">
                        </div>
                    </div>

                    <div class="mb-3">
                        <label for="crear_foto" class="form-label">Foto</label>
                        <input type="file" class="form-control form-control-sm" id="crear_foto" name="foto_url"
                            accept=".jpg,.jpeg,.png" required>
                    </div>

                    <button class="btn btn-primary" style="background-color:#226846;border-color:#1a4731;"
                        name="btnregistrar" value="ok">Confirmar Registro</button>
                </form>
            </div>
        </div>
    </div>
</div>