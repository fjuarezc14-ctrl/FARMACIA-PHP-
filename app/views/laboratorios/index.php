<div class="page-content">
    <?php if (isset($_SESSION['error'])): ?>
        <div class="alert alert-danger mb-4"><i class="bi bi-exclamation-triangle-fill"></i> <?php echo $_SESSION['error']; unset($_SESSION['error']); ?></div>
    <?php endif; ?>
    <?php if (isset($_SESSION['mensaje'])): ?>
        <div class="alert alert-success mb-4"><i class="bi bi-check-circle-fill"></i> <?php echo $_SESSION['mensaje']; unset($_SESSION['mensaje']); ?></div>
    <?php endif; ?>

    <div class="d-flex justify-content-between align-items-center mb-4 flex-wrap gap-2">
        <div>
            <h1 class="page-title">Laboratorios y Fabricantes</h1>
            <div class="page-subtitle">Gestiona las marcas, laboratorios farmacéuticos y fabricantes de productos.</div>
        </div>
        <button class="btn-primary-custom" style="width: auto; padding: 10px 20px;" data-bs-toggle="modal" data-bs-target="#modalLaboratorio" onclick="nuevoRegistro()">
            <i class="bi bi-plus-lg"></i> Nuevo Laboratorio
        </button>
    </div>

    <!-- Buscador y Filtro -->
    <div class="card-metric mb-3 p-3">
        <form action="<?php echo BASE_URL; ?>laboratorio/index" method="GET" class="row g-2 align-items-center">
            <div class="col-md-7 col-sm-12">
                <div class="search-box w-100">
                    <i class="bi bi-search"></i>
                    <input type="text" name="search" value="<?php echo htmlspecialchars($data['search'] ?? ''); ?>" placeholder="Buscar por nombre o descripción de laboratorio...">
                </div>
            </div>
            <div class="col-md-2 col-sm-6">
                <select name="limit" class="form-select form-select-sm" onchange="this.form.submit()">
                    <option value="15" <?php echo ($data['limit'] ?? 15) == 15 ? 'selected' : ''; ?>>15 por pág.</option>
                    <option value="25" <?php echo ($data['limit'] ?? 15) == 25 ? 'selected' : ''; ?>>25 por pág.</option>
                    <option value="50" <?php echo ($data['limit'] ?? 15) == 50 ? 'selected' : ''; ?>>50 por pág.</option>
                    <option value="100" <?php echo ($data['limit'] ?? 15) == 100 ? 'selected' : ''; ?>>100 por pág.</option>
                </select>
            </div>
            <div class="col-md-3 col-sm-6 d-flex gap-2">
                <button type="submit" class="btn btn-sm btn-success fw-bold flex-grow-1" style="height: 38px;">
                    <i class="bi bi-search"></i> Buscar
                </button>
                <?php if (!empty($data['search'])): ?>
                    <a href="<?php echo BASE_URL; ?>laboratorio/index" class="btn btn-sm btn-outline-secondary" style="height: 38px; display: flex; align-items: center;" title="Limpiar búsqueda">
                        <i class="bi bi-x-circle"></i>
                    </a>
                <?php endif; ?>
            </div>
        </form>
    </div>

    <!-- Tabla de Laboratorios -->
    <div class="card-metric">
        <div class="table-responsive">
            <table class="table table-hover align-middle" style="width: 100%; font-size: 14px;">
                <thead>
                    <tr>
                        <th width="70">ID</th>
                        <th>Nombre del Laboratorio</th>
                        <th>Descripción</th>
                        <th width="150" class="text-center">Medicamentos</th>
                        <th width="160" class="text-end">Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if(empty($data['laboratorios'])): ?>
                    <tr><td colspan="5" class="text-center text-muted py-4"><i class="bi bi-inbox fs-4 d-block mb-2"></i>No se encontraron laboratorios registrados</td></tr>
                    <?php else: ?>
                    <?php foreach($data['laboratorios'] as $lab): ?>
                    <tr>
                        <td style="color: var(--text-secondary); font-family: monospace; font-weight: 600;">#<?php echo $lab['id']; ?></td>
                        <td>
                            <div style="font-weight:700; color:var(--text-primary);"><?php echo htmlspecialchars($lab['nombre']); ?></div>
                        </td>
                        <td style="color:var(--text-secondary); font-size: 13px;">
                            <?php echo !empty($lab['descripcion']) ? htmlspecialchars($lab['descripcion']) : '<span class="text-muted fst-italic">Sin descripción</span>'; ?>
                        </td>
                        <td class="text-center">
                            <?php if (($lab['total_productos'] ?? 0) > 0): ?>
                                <span class="badge" style="background: rgba(0, 207, 232, 0.15); color: #00CFE8; border: 1px solid rgba(0, 207, 232, 0.3); font-size: 12px; padding: 5px 10px;">
                                    <i class="bi bi-capsule"></i> <?php echo number_format($lab['total_productos']); ?> prods
                                </span>
                            <?php else: ?>
                                <span class="badge" style="background: rgba(108, 117, 125, 0.15); color: #a0a0a0; border: 1px solid rgba(108, 117, 125, 0.3); font-size: 12px; padding: 5px 10px;">
                                    <i class="bi bi-box"></i> 0 prods
                                </span>
                            <?php endif; ?>
                        </td>
                        <td class="text-end">
                            <!-- Editar -->
                            <button class="btn btn-sm" style="color: #00CFE8;" onclick="editarRegistro(<?php echo htmlspecialchars(json_encode($lab)); ?>)" title="Editar laboratorio">
                                <i class="bi bi-pencil-square"></i>
                            </button>

                            <?php if (($lab['total_productos'] ?? 0) == 0): ?>
                                <!-- Eliminar directo (permitido porque tiene 0 productos) -->
                                <form action="<?php echo BASE_URL; ?>laboratorio/delete" method="POST" class="d-inline" onsubmit="return confirm('¿Seguro que deseas eliminar este laboratorio? No tiene productos asociados.');">
                                    <?php echo Controller::csrfField(); ?>
                                    <input type="hidden" name="id" value="<?php echo $lab['id']; ?>">
                                    <button type="submit" class="btn btn-sm" style="color: var(--danger);" title="Eliminar laboratorio vacío">
                                        <i class="bi bi-trash"></i>
                                    </button>
                                </form>
                            <?php else: ?>
                                <!-- Reasignar y Eliminar (seguro para laboratorios con productos) -->
                                <button type="button" class="btn btn-sm text-warning" onclick="abrirModalReasignar(<?php echo (int)$lab['id']; ?>, '<?php echo htmlspecialchars(addslashes($lab['nombre'])); ?>', <?php echo (int)$lab['total_productos']; ?>)" title="Reasignar <?php echo $lab['total_productos']; ?> productos y eliminar laboratorio">
                                    <i class="bi bi-arrow-left-right"></i>
                                </button>
                            <?php endif; ?>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>

        <!-- Paginación Server-Side -->
        <?php if(($data['total_paginas'] ?? 1) > 1 || ($data['total_registros'] ?? 0) > 0): ?>
        <div class="d-flex justify-content-between align-items-center p-3 border-top border-secondary border-opacity-25 flex-wrap gap-2">
            <div class="text-muted" style="font-size: 13px;">
                Mostrando página <strong><?php echo $data['pagina_actual'] ?? 1; ?></strong> de <strong><?php echo $data['total_paginas'] ?? 1; ?></strong> (Total: <strong><?php echo $data['total_registros'] ?? count($data['laboratorios']); ?></strong> laboratorios)
            </div>
            <?php if(($data['total_paginas'] ?? 1) > 1): ?>
            <nav aria-label="Paginación de laboratorios">
                <ul class="pagination pagination-sm mb-0">
                    <?php
                    $queryParams = $_GET;
                    if(($data['pagina_actual'] ?? 1) > 1):
                        $queryParams['page'] = $data['pagina_actual'] - 1;
                        $prevUrl = BASE_URL . 'laboratorio/index?' . http_build_query($queryParams);
                    ?>
                        <li class="page-item"><a class="page-link" href="<?php echo $prevUrl; ?>">&laquo; Anterior</a></li>
                    <?php else: ?>
                        <li class="page-item disabled"><span class="page-link">&laquo; Anterior</span></li>
                    <?php endif; ?>

                    <?php
                    $inicio = max(1, ($data['pagina_actual'] ?? 1) - 2);
                    $fin = min($data['total_paginas'], ($data['pagina_actual'] ?? 1) + 2);
                    for($i = $inicio; $i <= $fin; $i++):
                        $queryParams['page'] = $i;
                        $pageUrl = BASE_URL . 'laboratorio/index?' . http_build_query($queryParams);
                    ?>
                        <li class="page-item <?php echo $i == ($data['pagina_actual'] ?? 1) ? 'active' : ''; ?>">
                            <a class="page-link" href="<?php echo $pageUrl; ?>"><?php echo $i; ?></a>
                        </li>
                    <?php endfor; ?>

                    <?php
                    if(($data['pagina_actual'] ?? 1) < ($data['total_paginas'] ?? 1)):
                        $queryParams['page'] = $data['pagina_actual'] + 1;
                        $nextUrl = BASE_URL . 'laboratorio/index?' . http_build_query($queryParams);
                    ?>
                        <li class="page-item"><a class="page-link" href="<?php echo $nextUrl; ?>">Siguiente &raquo;</a></li>
                    <?php else: ?>
                        <li class="page-item disabled"><span class="page-link">Siguiente &raquo;</span></li>
                    <?php endif; ?>
                </ul>
            </nav>
            <?php endif; ?>
        </div>
        <?php endif; ?>
    </div>
</div>

<!-- Modal Crear / Editar Laboratorio -->
<div class="modal fade" id="modalLaboratorio" tabindex="-1" data-bs-backdrop="static">
  <div class="modal-dialog">
    <div class="modal-content" style="background-color: var(--bg-card); border: 1px solid var(--border-color);">
      <form action="<?php echo BASE_URL; ?>laboratorio/save" method="POST">
          <?php echo Controller::csrfField(); ?>
          <input type="hidden" name="id" id="txtId">
          <div class="modal-header" style="border-bottom: 1px solid var(--border-color);">
            <h5 class="modal-title" id="modalTitle" style="color: var(--text-primary); font-size: 16px; font-weight:700;">Nuevo Laboratorio</h5>
            <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
          </div>
          <div class="modal-body">
              <div class="form-group mb-3">
                  <label class="form-label" style="font-weight:600;">Nombre del Laboratorio <span class="text-danger">*</span></label>
                  <input type="text" class="form-control-custom" name="nombre" id="txtNombre" required placeholder="Ej. LABORATORIOS PORTUGAL S.A.">
              </div>
              <div class="form-group mb-0">
                  <label class="form-label" style="font-weight:600;">Descripción (Opcional)</label>
                  <textarea class="form-control-custom" name="descripcion" id="txtDesc" rows="3" placeholder="País de origen, contacto o notas internas..."></textarea>
              </div>
          </div>
          <div class="modal-footer" style="border-top: 1px solid var(--border-color);">
            <button type="button" class="btn btn-secondary" style="border-radius:10px;" data-bs-dismiss="modal">Cancelar</button>
            <button type="submit" class="btn-primary-custom" style="width: auto; padding: 8px 20px;">Guardar Cambios</button>
          </div>
      </form>
    </div>
  </div>
</div>

<!-- Modal Reasignar y Eliminar Laboratorio -->
<div class="modal fade" id="modalReasignar" tabindex="-1" data-bs-backdrop="static">
  <div class="modal-dialog">
    <div class="modal-content" style="background-color: var(--bg-card); border: 1px solid var(--border-color);">
      <form action="<?php echo BASE_URL; ?>laboratorio/reasignar" method="POST">
          <?php echo Controller::csrfField(); ?>
          <input type="hidden" name="id_origen" id="reasignarIdOrigen">
          <div class="modal-header" style="border-bottom: 1px solid var(--border-color);">
            <h5 class="modal-title" style="color: #ffc107; font-size: 16px; font-weight:700;">
                <i class="bi bi-arrow-left-right"></i> Reasignar Productos y Eliminar
            </h5>
            <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
          </div>
          <div class="modal-body">
              <div class="alert alert-warning mb-3" style="font-size: 13px;">
                  <i class="bi bi-shield-exclamation me-1"></i> El laboratorio <strong id="lblNombreOrigen"></strong> tiene <strong id="lblCantProductos"></strong> medicamento(s) asociado(s). 
                  Para no dejarlos sin fabricante, seleccione a qué laboratorio desea transferirlos:
              </div>
              <div class="form-group mb-3">
                  <label class="form-label" style="font-weight:600;">Laboratorio Destino <span class="text-danger">*</span></label>
                  <select name="id_destino" id="selectLaboratorioDestino" class="form-select" required>
                      <option value="">-- Seleccionar laboratorio de destino --</option>
                      <?php if (!empty($data['todosLaboratorios'])): foreach($data['todosLaboratorios'] as $tl): ?>
                          <option value="<?php echo $tl['id']; ?>" data-id="<?php echo $tl['id']; ?>">
                              <?php echo htmlspecialchars($tl['nombre']); ?>
                          </option>
                      <?php endforeach; endif; ?>
                  </select>
              </div>
          </div>
          <div class="modal-footer" style="border-top: 1px solid var(--border-color);">
            <button type="button" class="btn btn-secondary" style="border-radius:10px;" data-bs-dismiss="modal">Cancelar</button>
            <button type="submit" class="btn btn-warning fw-bold text-dark" style="border-radius:10px; padding: 8px 20px;">
                <i class="bi bi-check2-circle"></i> Confirmar y Eliminar
            </button>
          </div>
      </form>
    </div>
  </div>
</div>

<script>
function nuevoRegistro() {
    document.getElementById('txtId').value = '';
    document.getElementById('txtNombre').value = '';
    document.getElementById('txtDesc').value = '';
    document.getElementById('modalTitle').innerText = 'Nuevo Laboratorio';
}

function editarRegistro(obj) {
    document.getElementById('txtId').value = obj.id;
    document.getElementById('txtNombre').value = obj.nombre;
    document.getElementById('txtDesc').value = obj.descripcion || '';
    document.getElementById('modalTitle').innerText = 'Editar Laboratorio';
    var modal = new bootstrap.Modal(document.getElementById('modalLaboratorio'));
    modal.show();
}

function abrirModalReasignar(idOrigen, nombreOrigen, cantProds) {
    document.getElementById('reasignarIdOrigen').value = idOrigen;
    document.getElementById('lblNombreOrigen').innerText = '"' + nombreOrigen + '"';
    document.getElementById('lblCantProductos').innerText = cantProds;

    var select = document.getElementById('selectLaboratorioDestino');
    for (var i = 0; i < select.options.length; i++) {
        var opt = select.options[i];
        if (opt.getAttribute('data-id') == idOrigen) {
            opt.disabled = true;
            opt.hidden = true;
        } else {
            opt.disabled = false;
            opt.hidden = false;
        }
    }
    select.value = '';

    var modal = new bootstrap.Modal(document.getElementById('modalReasignar'));
    modal.show();
}
</script>
