<div class="page-content">
    <?php if (isset($_SESSION['error'])): ?>
        <div class="alert alert-danger mb-4"><i class="bi bi-exclamation-triangle-fill"></i> <?php echo $_SESSION['error']; unset($_SESSION['error']); ?></div>
    <?php endif; ?>
    <?php if (isset($_SESSION['mensaje'])): ?>
        <div class="alert alert-success mb-4"><i class="bi bi-check-circle-fill"></i> <?php echo $_SESSION['mensaje']; unset($_SESSION['mensaje']); ?></div>
    <?php endif; ?>

    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h1 class="page-title">Directorio de Clientes</h1>
            <div class="page-subtitle">Gestiona a tus clientes habituales para facturación.</div>
        </div>
        <button class="btn-primary-custom" style="width: auto; padding: 10px 20px;" data-bs-toggle="modal" data-bs-target="#modalCliente" onclick="nuevoRegistro()">
            <i class="bi bi-person-plus"></i> Nuevo Cliente
        </button>
    </div>

    <div class="card-metric mb-3 p-3">
        <form method="GET" action="<?php echo BASE_URL; ?>cliente/index" class="row g-2 align-items-center">
            <div class="col-md-9">
                <div class="search-box w-100">
                    <i class="bi bi-search"></i>
                    <input type="text" name="search" value="<?php echo htmlspecialchars($data['search'] ?? ''); ?>" placeholder="Buscar cliente por DNI, RUC, Nombres o Teléfono...">
                </div>
            </div>
            <div class="col-md-3 d-flex gap-2">
                <button type="submit" class="btn btn-sm btn-success fw-bold flex-grow-1" style="height: 38px;">
                    <i class="bi bi-search"></i> Buscar
                </button>
                <a href="<?php echo BASE_URL; ?>cliente/index" class="btn btn-sm btn-outline-secondary" style="height: 38px; display: flex; align-items: center;" title="Limpiar Búsqueda">
                    <i class="bi bi-x-circle"></i>
                </a>
            </div>
        </form>
    </div>

    <div class="card-metric">
        <div class="table-responsive">
            <table class="table table-hover align-middle" style="width: 100%; font-size: 14px;">
                <thead>
                    <tr>
                        <th width="50">ID</th>
                        <th>Documento</th>
                        <th>Nombres / Razón Social</th>
                        <th>Teléfono</th>
                        <th>Dirección</th>
                        <th width="150" class="text-end">Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if(empty($data['clientes'])): ?>
                    <tr><td colspan="6" class="text-center text-muted py-4">No se encontraron clientes</td></tr>
                    <?php else: ?>
                    <?php foreach($data['clientes'] as $cli): ?>
                    <tr>
                        <td><?php echo $cli['id']; ?></td>
                        <td><span class="badge bg-secondary"><?php echo $cli['tipo_documento']; ?></span> <?php echo htmlspecialchars($cli['num_documento']); ?></td>
                        <td style="font-weight:700; color:var(--text-primary);"><?php echo htmlspecialchars($cli['nombres']); ?></td>
                        <td style="color:var(--text-secondary);"><?php echo htmlspecialchars($cli['telefono'] ?? '-'); ?></td>
                        <td style="color:var(--text-secondary);"><?php echo htmlspecialchars($cli['direccion'] ?? '-'); ?></td>
                        <td class="text-end">
                            <?php if($cli['id'] == 1): ?>
                            <span class="badge bg-secondary">Genérico</span>
                            <?php else: ?>
                            <button class="btn btn-sm" style="color: #00CFE8;" onclick="editarRegistro(<?php echo htmlspecialchars(json_encode($cli)); ?>)" title="Editar Cliente">
                                <i class="bi bi-pencil-square"></i>
                            </button>
                            <?php if(($_SESSION['rol_id'] ?? 0) == 1): ?>
                            <form action="<?php echo BASE_URL; ?>cliente/delete" method="POST" class="d-inline" onsubmit="return confirm('¿Seguro que deseas eliminar este cliente?');">
                                <?php echo Controller::csrfField(); ?>
                                <input type="hidden" name="id" value="<?php echo $cli['id']; ?>">
                                <button type="submit" class="btn btn-sm" style="color: var(--danger);" title="Eliminar">
                                    <i class="bi bi-trash"></i>
                                </button>
                            </form>
                            <?php endif; ?>
                            <?php endif; ?>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>

        <!-- PAGINACIÓN SERVER-SIDE -->
        <?php if(($data['total_paginas'] ?? 1) > 1 || ($data['total_registros'] ?? 0) > 0): ?>
        <div class="d-flex justify-content-between align-items-center p-3 border-top border-secondary border-opacity-25 flex-wrap gap-2">
            <div class="text-muted" style="font-size: 13px;">
                Mostrando página <strong><?php echo $data['pagina_actual'] ?? 1; ?></strong> de <strong><?php echo $data['total_paginas'] ?? 1; ?></strong> (Total: <strong><?php echo $data['total_registros'] ?? count($data['clientes']); ?></strong> clientes)
            </div>
            <?php if(($data['total_paginas'] ?? 1) > 1): ?>
            <nav aria-label="Paginación de clientes">
                <ul class="pagination pagination-sm mb-0">
                    <?php
                    $queryParams = $_GET;
                    if(($data['pagina_actual'] ?? 1) > 1):
                        $queryParams['page'] = $data['pagina_actual'] - 1;
                        $prevUrl = BASE_URL . 'cliente/index?' . http_build_query($queryParams);
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
                        $pageUrl = BASE_URL . 'cliente/index?' . http_build_query($queryParams);
                    ?>
                        <li class="page-item <?php echo $i == ($data['pagina_actual'] ?? 1) ? 'active' : ''; ?>">
                            <a class="page-link" href="<?php echo $pageUrl; ?>"><?php echo $i; ?></a>
                        </li>
                    <?php endfor; ?>

                    <?php
                    if(($data['pagina_actual'] ?? 1) < ($data['total_paginas'] ?? 1)):
                        $queryParams['page'] = $data['pagina_actual'] + 1;
                        $nextUrl = BASE_URL . 'cliente/index?' . http_build_query($queryParams);
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

<!-- Modal Cliente -->
<div class="modal fade" id="modalCliente" tabindex="-1" data-bs-backdrop="static">
  <div class="modal-dialog modal-lg">
    <div class="modal-content" style="background-color: var(--bg-card); border: 1px solid var(--border-color);">
      <form action="<?php echo BASE_URL; ?>cliente/save" method="POST">
          <?php echo Controller::csrfField(); ?>
          <input type="hidden" name="id" id="txtId">
          <div class="modal-header" style="border-bottom: 1px solid var(--border-color);">
            <h5 class="modal-title" id="modalTitle" style="color: var(--text-primary); font-size: 16px; font-weight:700;">Nuevo Cliente</h5>
            <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
          </div>
          <div class="modal-body row g-3">
              <div class="col-md-4 form-group">
                  <label class="form-label">Tipo Documento</label>
                  <select class="form-control-custom" name="tipo_documento" id="txtTipo">
                      <option value="DNI">DNI (Boleta)</option>
                      <option value="RUC">RUC (Factura)</option>
                      <option value="Pasaporte">Pasaporte</option>
                  </select>
              </div>
              <div class="col-md-8 form-group">
                  <label class="form-label">Número Documento</label>
                  <input type="text" class="form-control-custom" name="num_documento" id="txtNum" required>
              </div>
              <div class="col-md-6 form-group">
                  <label class="form-label">Nombres Completos / Razón Social</label>
                  <input type="text" class="form-control-custom" name="nombres" id="txtNombres" required>
              </div>
              <div class="col-md-6 form-group">
                  <label class="form-label">Teléfono Celular</label>
                  <input type="text" class="form-control-custom" name="telefono" id="txtTel">
              </div>
              <div class="col-12 form-group mb-0">
                  <label class="form-label">Dirección Fiscal / Envío</label>
                  <input type="text" class="form-control-custom" name="direccion" id="txtDir">
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

<script>
function nuevoRegistro() {
    document.getElementById('txtId').value = '';
    document.getElementById('txtTipo').value = 'DNI';
    document.getElementById('txtNum').value = '';
    document.getElementById('txtNombres').value = '';
    document.getElementById('txtTel').value = '';
    document.getElementById('txtDir').value = '';
    document.getElementById('modalTitle').innerText = 'Nuevo Cliente';
}

function editarRegistro(obj) {
    document.getElementById('txtId').value = obj.id;
    document.getElementById('txtTipo').value = obj.tipo_documento;
    document.getElementById('txtNum').value = obj.num_documento;
    document.getElementById('txtNombres').value = obj.nombres;
    document.getElementById('txtTel').value = obj.telefono;
    document.getElementById('txtDir').value = obj.direccion;
    document.getElementById('modalTitle').innerText = 'Editar Cliente';
    var modal = new bootstrap.Modal(document.getElementById('modalCliente'));
    modal.show();
}
</script>
