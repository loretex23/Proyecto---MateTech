<div class="modal fade" id="modalEditarClub" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content modal-login-caja">
            <div class="modal-header">
                <h5 class="modal-title">Editar Jugador</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <?php 
                $es_admin = (isset($_SESSION['rol']) && $_SESSION['rol'] === 'Admin'); 
                ?>

                <form action="../sql/editar_jugador.php" method="POST" enctype="multipart/form-data">
                    <input type="hidden" id="editar_id" name="id">

                    <?php foreach ([
                        ['editar_nombre',            'nombre',            'Nombre',              'text'],
                        ['editar_apellido',          'apellido',          'Apellido',            'text'],
                        ['editar_ci',                'ci',                'Cédula de Identidad', 'text'],
                        ['editar_fecha_nacimiento',  'fecha_nacimiento',  'Fecha de Nacimiento', 'date'],
                        ['editar_carnet_vencimiento','carnet_vencimiento','Carnet de Vencimiento','date'],
                    ] as [$id, $name, $label, $type]): ?>
                        
                        <?php 
                            $oculto = (!$es_admin && $name !== 'carnet_vencimiento') ? 'd-none' : '';
                        ?>
                        <div class="mb-3 <?= $oculto ?>">
                            <label for="<?= $id ?>" class="form-label"><?= $label ?></label>
                            <input type="<?= $type ?>" class="form-control" id="<?= $id ?>"
                                   name="<?= $name ?>" <?= ($oculto ? '' : 'required') ?>>
                        </div>
                    <?php endforeach; ?>

                    <!-- Club, Categoría, Peso, Altura y Foto ocultos para rol Club pero presentes en el DOM -->
                    <div class="mb-3 <?= !$es_admin ? 'd-none' : '' ?>">
                        <label for="editar_club_id" class="form-label">Club</label>
                        <select class="form-select" id="editar_club_id" name="club_id" <?= $es_admin ? 'required' : '' ?>>
                            <?= options_club($clubes) ?>
                        </select>
                    </div>

                    <div class="mb-3 <?= !$es_admin ? 'd-none' : '' ?>">
                        <label for="editar_categoria_id" class="form-label">Categoría</label>
                        <select class="form-select" id="editar_categoria_id" name="categoria_id" <?= $es_admin ? 'required' : '' ?>>
                            <?= options_cat($categorias) ?>
                        </select>
                    </div>

                    <div class="row g-2 mb-3 <?= !$es_admin ? 'd-none' : '' ?>">
                        <div class="col-md-4">
                            <label class="form-label">Peso (kg)</label>
                            <input type="number" step="0.01" min="0" class="form-control"
                                id="editar_masa" name="masa" placeholder="Ej: 72.5">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Altura (m)</label>
                            <input type="number" step="0.01" min="0" max="3.00" class="form-control"
                                id="editar_altura" name="altura" placeholder="Ej: 1.75">
                        </div>
                    </div>

                    <div class="mb-3 <?= !$es_admin ? 'd-none' : '' ?>">
                        <label class="form-label">Foto actual</label><br>
                        <img id="editar_foto_actual" src="" alt="Foto actual" width="80" height="80"
                            style="border-radius:50%;object-fit:cover;margin-bottom:8px;">
                        <label for="editar_foto" class="form-label d-block">Cambiar foto</label>
                        <input type="file" class="form-control" id="editar_foto" name="foto_url"
                            accept=".jpg,.jpeg,.png">
                    </div>

                    <button class="btn btn-primary" style="background-color:#226846;border-color:#1a4731;"
                        name="btneditar" value="ok">Confirmar Registro</button>
                </form>
            </div>
        </div>
    </div>
</div>