<!-- Modal unificado de gestión de partido. Tabs: Estado/Fecha · Resultado · Tarjeta · Lesión -->
<div class="modal fade" id="modalGestionar" tabindex="-1" aria-hidden="true">
<div class="modal-dialog modal-dialog-centered modal-xl modal-dialog-scrollable">
<div class="modal-content">

  <!-- Cabecera con el nombre del partido -->
  <div class="modal-header border-0 pb-0">
    <div>
      <h5 class="modal-title fw-bold" id="mg_titulo">Gestionar partido</h5>
      <small class="text-muted" id="mg_subtitulo"></small>
    </div>
    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
  </div>

  <!-- Pestañas de navegación -->
  <div class="modal-body pt-2">
    <ul class="nav nav-tabs mb-4" id="mgTabs" role="tablist">
      <li class="nav-item" role="presentation">
        <button class="nav-link active" id="tab-estado-btn" data-bs-toggle="tab"
                data-bs-target="#tab-estado" type="button" role="tab">
          <i class="ti ti-edit"></i> Estado y fecha
        </button>
      </li>
      <li class="nav-item" role="presentation">
        <button class="nav-link" id="tab-resultado-btn" data-bs-toggle="tab"
                data-bs-target="#tab-resultado" type="button" role="tab">
          <i class="ti ti-ball-football"></i> Resultado
        </button>
      </li>
      <li class="nav-item" role="presentation">
        <button class="nav-link" id="tab-tarjeta-btn" data-bs-toggle="tab"
                data-bs-target="#tab-tarjeta" type="button" role="tab">
          <i class="ti ti-cards"></i> Tarjeta
        </button>
      </li>
      <li class="nav-item" role="presentation">
        <button class="nav-link" id="tab-lesion-btn" data-bs-toggle="tab"
                data-bs-target="#tab-lesion" type="button" role="tab">
          <i class="ti ti-first-aid-kit"></i> Lesión
        </button>
      </li>
    </ul>

    <div class="tab-content">

      <!-- ── TAB 1: Estado y fecha ────────────────────────────── -->
      <div class="tab-pane fade show active" id="tab-estado" role="tabpanel">
        <p class="text-muted small mb-3">Cambiá el estado del partido y/o su fecha programada.</p>
        <form method="POST">
          <input type="hidden" name="partido_id" id="est_id">
          <div class="row g-3">
            <div class="col-md-6">
              <label class="form-label fw-semibold">Estado</label>
              <select name="estado" id="est_select" class="form-select">
                <?php foreach ($estados as $v => $l): ?>
                  <option value="<?= $v ?>"><?= $l ?></option>
                <?php endforeach; ?>
              </select>
            </div>
            <div class="col-md-6">
              <label class="form-label fw-semibold">Fecha y hora</label>
              <input type="datetime-local" name="fecha_partido" id="est_fecha" class="form-control">
            </div>
          </div>
          <div class="mt-4 d-flex justify-content-end">
            <button type="submit" name="btn_estado" value="1" class="btn btn-warning px-4">
              <i class="ti ti-device-floppy"></i> Guardar cambios
            </button>
          </div>
        </form>
      </div>

      <!-- ── TAB 2: Resultado y goles ─────────────────────────── -->
      <div class="tab-pane fade" id="tab-resultado" role="tabpanel">
        <p class="text-muted small mb-3">Ingresá el marcador final y los autores de cada gol.</p>
        <form method="POST">
          <input type="hidden" name="partido_id" id="res_id">

          <!-- Marcador -->
          <div class="d-flex align-items-center justify-content-center gap-3 mb-4 p-3"
               style="background:#f8fafc;border-radius:10px;border:1px solid #e5e7eb">
            <div class="text-center flex-fill">
              <div class="fw-bold mb-1" id="res_local_nombre" style="font-size:.9rem"></div>
              <input type="number" name="goles_local" id="res_gl"
                     class="form-control text-center fs-3 fw-bold" min="0" value="0" required
                     style="max-width:80px;margin:auto">
            </div>
            <span class="fs-2 fw-bold text-muted">–</span>
            <div class="text-center flex-fill">
              <div class="fw-bold mb-1" id="res_visit_nombre" style="font-size:.9rem"></div>
              <input type="number" name="goles_visitante" id="res_gv"
                     class="form-control text-center fs-3 fw-bold" min="0" value="0" required
                     style="max-width:80px;margin:auto">
            </div>
          </div>

          <!-- Goles por equipo -->
          <div class="row g-3">
            <div class="col-md-6">
              <div class="d-flex align-items-center gap-2 mb-2">
                <span class="fw-bold" id="res_local_lbl" style="font-size:.85rem"></span>
                <span class="badge bg-success" id="bdg_l">0</span>
              </div>
              <div id="wrap_l"></div>
              <button type="button" class="btn btn-sm btn-outline-success mt-1" id="add_l">
                <i class="ti ti-plus"></i> Agregar gol
              </button>
            </div>
            <div class="col-md-6">
              <div class="d-flex align-items-center gap-2 mb-2">
                <span class="fw-bold" id="res_visit_lbl" style="font-size:.85rem"></span>
                <span class="badge bg-success" id="bdg_v">0</span>
              </div>
              <div id="wrap_v"></div>
              <button type="button" class="btn btn-sm btn-outline-success mt-1" id="add_v">
                <i class="ti ti-plus"></i> Agregar gol
              </button>
            </div>
          </div>
          <p class="text-muted mt-2 mb-0" style="font-size:.73rem">
            <i class="ti ti-info-circle"></i> Podés dejar goles sin jugador asignado.
          </p>

          <div class="mt-4 d-flex justify-content-end">
            <button type="submit" name="btn_resultado" value="1" class="btn btn-success px-4">
              <i class="ti ti-device-floppy"></i> Guardar resultado
            </button>
          </div>
        </form>
      </div>

      <!-- ── TAB 3: Tarjeta ───────────────────────────────────── -->
      <div class="tab-pane fade" id="tab-tarjeta" role="tabpanel">
        <p class="text-muted small mb-3">Registrá tarjetas amarillas o rojas (máx 2 amarillas y 1 roja por jugador).</p>
        <form method="POST">
          <input type="hidden" name="partido_id" id="tar_id">
          
          <div class="row g-3">
            <div class="col-md-6">
              <div class="d-flex align-items-center gap-2 mb-2">
                <span class="fw-bold" id="tar_local_lbl" style="font-size:.85rem"></span>
                <span class="badge bg-danger" id="bdg_tar_l">0</span>
              </div>
              <div id="wrap_tar_l"></div>
              <button type="button" class="btn btn-sm btn-outline-danger mt-1" id="add_tar_l">
                <i class="ti ti-plus"></i> Agregar tarjeta
              </button>
            </div>
            <div class="col-md-6">
              <div class="d-flex align-items-center gap-2 mb-2">
                <span class="fw-bold" id="tar_visit_lbl" style="font-size:.85rem"></span>
                <span class="badge bg-danger" id="bdg_tar_v">0</span>
              </div>
              <div id="wrap_tar_v"></div>
              <button type="button" class="btn btn-sm btn-outline-danger mt-1" id="add_tar_v">
                <i class="ti ti-plus"></i> Agregar tarjeta
              </button>
            </div>
          </div>
          <p class="text-muted mt-2 mb-0" style="font-size:.73rem" id="tar_info_text">
            <i class="ti ti-info-circle"></i> La segunda amarilla de un mismo jugador generará una roja automática al guardar.
          </p>

          <div class="mt-4 d-flex justify-content-end">
            <button type="submit" name="btn_tarjetas" value="1" class="btn btn-danger px-4" id="btn_save_tarjetas">
              <i class="ti ti-device-floppy"></i> Guardar tarjetas
            </button>
          </div>
        </form>
      </div>

      <!-- ── TAB 4: Lesión ────────────────────────────────────── -->
      <div class="tab-pane fade" id="tab-lesion" role="tabpanel">
        <p class="text-muted small mb-3">Registrá las lesiones ocurridas durante el partido.</p>
        <form method="POST">
          <input type="hidden" name="partido_id" id="les_id">
          
          <div class="row g-3">
            <div class="col-md-6">
              <div class="d-flex align-items-center gap-2 mb-2">
                <span class="fw-bold" id="les_local_lbl" style="font-size:.85rem"></span>
                <span class="badge bg-secondary" id="bdg_les_l">0</span>
              </div>
              <div id="wrap_les_l"></div>
              <button type="button" class="btn btn-sm btn-outline-secondary mt-1" id="add_les_l">
                <i class="ti ti-plus"></i> Agregar lesión
              </button>
            </div>
            <div class="col-md-6">
              <div class="d-flex align-items-center gap-2 mb-2">
                <span class="fw-bold" id="les_visit_lbl" style="font-size:.85rem"></span>
                <span class="badge bg-secondary" id="bdg_les_v">0</span>
              </div>
              <div id="wrap_les_v"></div>
              <button type="button" class="btn btn-sm btn-outline-secondary mt-1" id="add_les_v">
                <i class="ti ti-plus"></i> Agregar lesión
              </button>
            </div>
          </div>

          <div class="mt-4 d-flex justify-content-end">
            <button type="submit" name="btn_lesiones" value="1" class="btn btn-secondary px-4">
              <i class="ti ti-device-floppy"></i> Guardar lesiones
            </button>
          </div>
        </form>
      </div>

    </div><!-- /tab-content -->
  </div><!-- /modal-body -->

</div>
</div>
</div>