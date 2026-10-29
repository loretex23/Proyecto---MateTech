<div class="modal fade" id="modalCrear" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content modal-login-caja">
            <div class="modal-header">
                <h5 class="modal-title">Agregar Jugador</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <form method="POST" enctype="multipart/form-data">
                    
                    <div class="mb-3">
                        <label for="crear_nombre" class="form-label">Nombre</label>
                        <input type="text" class="form-control" id="crear_nombre"
                            name="nombre" minlength="3" maxlength="120" required>
                    </div>

                    <div class="mb-3">
                        <label for="crear_apellido" class="form-label">Apellido</label>
                        <input type="text" class="form-control" id="crear_apellido"
                            name="apellido" minlength="3" maxlength="120" required>
                    </div>

                    <div class="mb-3">
                        <label for="crear_ci" class="form-label">Cédula de Identidad</label>
                        <input type="text" class="form-control" id="crear_ci"
                            name="ci" required>
                    </div>

                    <div class="mb-3">
                        <label for="crear_fecha_nacimiento" class="form-label">Fecha de Nacimiento</label>
                        <input type="date" class="form-control" id="crear_fecha_nacimiento"
                            name="fecha_nacimiento" min="2013-01-01" max="2020-12-31" required>
                    </div>

                    <div class="mb-3">
                        <label for="crear_carnet_vencimiento" class="form-label">Carnet de Vencimiento</label>
                        <input type="date" class="form-control" id="crear_carnet_vencimiento"
                            name="carnet_vencimiento" min="<?= date('Y-m-d', strtotime('+1 day')) ?>" required>
                    </div>

                    <div class="mb-3">
                        <label for="crear_club_id" class="form-label">Club</label>
                        <select class="form-select" id="crear_club_id" name="club_id" required>
                            <?php 
                            $clubes_filtrados = array_filter($clubes, function($club) {
                                $rol = is_array($club) ? ($club['rol'] ?? '') : ($club->rol ?? '');
                                $nombre = is_array($club) ? ($club['nombre'] ?? '') : ($club->nombre ?? 'Administrador');

                                return strtolower($rol) !== 'admin' && strtolower($nombre) !== 'administrador';
                            });
                            ?>
                            <?= options_club($clubes_filtrados) ?>
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
                            accept=".jpg,.jpeg,.png">
                    </div>

                    <button class="btn btn-primary" style="background-color:#226846;border-color:#1a4731;"
                        name="btnregistrar" value="ok">Confirmar Registro</button>
                </form>
            </div>
        </div>
    </div>
</div>