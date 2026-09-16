<div class="modal fade" id="modalResultado" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-xl"><div class="modal-content"><form method="POST">
        <input type="hidden" name="partido_id" id="res_id">
        <div class="modal-header"><h5 class="modal-title"><i class="ti ti-ball-football"></i> Resultado y goles</h5><button type="button" class="btn-close" data-bs-dismiss="modal"></button></div>
        <div class="modal-body">
            <div class="d-flex align-items-center justify-content-center gap-3 mb-4 p-3" style="background:#f8fafc;border-radius:10px;border:1px solid #e5e7eb">
                <div class="text-center flex-fill">
                    <div class="fw-bold mb-1" id="res_local_nombre" style="font-size:.9rem"></div>
                    <input type="number" name="goles_local" id="res_gl" class="form-control text-center fs-3 fw-bold" min="0" value="0" required style="max-width:80px;margin:auto">
                </div>
                <span class="fs-2 fw-bold text-muted">–</span>
                <div class="text-center flex-fill">
                    <div class="fw-bold mb-1" id="res_visit_nombre" style="font-size:.9rem"></div>
                    <input type="number" name="goles_visitante" id="res_gv" class="form-control text-center fs-3 fw-bold" min="0" value="0" required style="max-width:80px;margin:auto">
                </div>
            </div>
            <div class="row g-3">
                <div class="col-md-6">
                    <div class="d-flex align-items-center gap-2 mb-2">
                        <span class="fw-bold" id="res_local_lbl" style="font-size:.85rem"></span>
                        <span class="badge bg-success" id="bdg_l">0</span>
                    </div>
                    <div id="wrap_l"></div>
                    <button type="button" class="btn btn-sm btn-outline-success mt-1" id="add_l"><i class="ti ti-plus"></i> Agregar gol</button>
                </div>
                <div class="col-md-6">
                    <div class="d-flex align-items-center gap-2 mb-2">
                        <span class="fw-bold" id="res_visit_lbl" style="font-size:.85rem"></span>
                        <span class="badge bg-success" id="bdg_v">0</span>
                    </div>
                    <div id="wrap_v"></div>
                    <button type="button" class="btn btn-sm btn-outline-success mt-1" id="add_v"><i class="ti ti-plus"></i> Agregar gol</button>
                </div>
            </div>
            <p class="text-muted mt-2 mb-0" style="font-size:.73rem"><i class="ti ti-info-circle"></i> Podés dejar goles sin jugador asignado.</p>
        </div>
        <div class="modal-footer"><button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button><button type="submit" name="btn_resultado" value="1" class="btn btn-success"><i class="ti ti-device-floppy"></i> Guardar resultado</button></div>
    </form></div></div>
</div>
