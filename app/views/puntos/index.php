<div class="page-content">
    <!-- Header del Módulo -->
    <div class="d-flex flex-wrap justify-content-between align-items-center gap-3 mb-4">
        <div>
            <h2 class="page-title mb-1" style="font-weight: 800; color: var(--text-primary);">
                <i class="bi bi-star-fill text-warning me-2"></i> Gestión y Fidelización de Puntos
            </h2>
            <p class="page-subtitle mb-0" style="color: var(--text-secondary); font-size: 14px;">
                Administración de recompensas por consumo, auditoría de ajustes manuales y configuración de tasas de acumulación y canje.
            </p>
        </div>
        <div>
            <span class="badge px-3 py-2" style="background: <?php echo $data['configPuntos']['habilitado'] ? 'rgba(16, 185, 129, 0.15)' : 'rgba(239, 68, 68, 0.15)'; ?>; color: <?php echo $data['configPuntos']['habilitado'] ? '#10b981' : '#ef4444'; ?>; border: 1px solid <?php echo $data['configPuntos']['habilitado'] ? '#10b981' : '#ef4444'; ?>; font-size: 13px; font-weight: 700; border-radius: 20px;">
                <i class="bi <?php echo $data['configPuntos']['habilitado'] ? 'bi-check-circle-fill' : 'bi-dash-circle-fill'; ?> me-1"></i>
                Programa <?php echo $data['configPuntos']['habilitado'] ? 'Activo' : 'Desactivado'; ?>
            </span>
        </div>
    </div>

    <!-- Notificaciones Flash -->
    <?php if(isset($_SESSION['mensaje'])): ?>
        <div class="alert alert-success alert-dismissible fade show border-0 shadow-sm mb-4" role="alert" style="background: rgba(16, 185, 129, 0.12); color: #047857;">
            <i class="bi bi-check-circle-fill me-2"></i> <?php echo htmlspecialchars($_SESSION['mensaje']); unset($_SESSION['mensaje']); ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Cerrar"></button>
        </div>
    <?php endif; ?>

    <?php if(isset($_SESSION['error'])): ?>
        <div class="alert alert-danger alert-dismissible fade show border-0 shadow-sm mb-4" role="alert" style="background: rgba(239, 68, 68, 0.12); color: #b91c1c;">
            <i class="bi bi-exclamation-triangle-fill me-2"></i> <?php echo htmlspecialchars($_SESSION['error']); unset($_SESSION['error']); ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Cerrar"></button>
        </div>
    <?php endif; ?>

    <!-- KPIs Rápidos -->
    <div class="row g-3 mb-4">
        <div class="col-12 col-md-4">
            <div class="card p-3 h-100 border-0 shadow-sm" style="background: var(--bg-card); border-left: 4px solid #fbbf24 !important; border-radius: 10px;">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <span class="text-uppercase" style="font-size: 11px; font-weight: 700; color: #d97706; letter-spacing: 0.5px;">Puntos en Circulación</span>
                        <h3 class="mb-0 mt-1 fw-bold" style="color: var(--text-primary);"><?php echo number_format($data['metricas']['total_puntos']); ?> pts</h3>
                    </div>
                    <div style="width: 44px; height: 44px; border-radius: 10px; background: rgba(251, 191, 36, 0.15); display: flex; align-items: center; justify-content: center;">
                        <i class="bi bi-coin text-warning" style="font-size: 22px;"></i>
                    </div>
                </div>
                <small class="mt-2 text-muted" style="font-size: 11px;">
                    Equivalente aprox: S/ <?php echo number_format($data['metricas']['total_puntos'] * $data['configPuntos']['valor_canje'], 2); ?> en descuentos
                </small>
            </div>
        </div>

        <div class="col-12 col-md-4">
            <div class="card p-3 h-100 border-0 shadow-sm" style="background: var(--bg-card); border-left: 4px solid #3b82f6 !important; border-radius: 10px;">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <span class="text-uppercase" style="font-size: 11px; font-weight: 700; color: #2563eb; letter-spacing: 0.5px;">Clientes Fidelizados</span>
                        <h3 class="mb-0 mt-1 fw-bold" style="color: var(--text-primary);"><?php echo number_format($data['metricas']['total_clientes']); ?></h3>
                    </div>
                    <div style="width: 44px; height: 44px; border-radius: 10px; background: rgba(59, 130, 246, 0.15); display: flex; align-items: center; justify-content: center;">
                        <i class="bi bi-people-fill text-primary" style="font-size: 22px;"></i>
                    </div>
                </div>
                <small class="mt-2 text-muted" style="font-size: 11px;">Clientes registrados con perfil activo</small>
            </div>
        </div>

        <div class="col-12 col-md-4">
            <div class="card p-3 h-100 border-0 shadow-sm" style="background: var(--bg-card); border-left: 4px solid #10b981 !important; border-radius: 10px;">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <span class="text-uppercase" style="font-size: 11px; font-weight: 700; color: #059669; letter-spacing: 0.5px;">Canjeados este Mes</span>
                        <h3 class="mb-0 mt-1 fw-bold" style="color: var(--text-primary);"><?php echo number_format($data['metricas']['canjeados_mes']); ?> pts</h3>
                    </div>
                    <div style="width: 44px; height: 44px; border-radius: 10px; background: rgba(16, 185, 129, 0.15); display: flex; align-items: center; justify-content: center;">
                        <i class="bi bi-bag-check-fill text-success" style="font-size: 22px;"></i>
                    </div>
                </div>
                <small class="mt-2 text-muted" style="font-size: 11px;">
                    Ahorro otorgado: S/ <?php echo number_format($data['metricas']['canjeados_mes'] * $data['configPuntos']['valor_canje'], 2); ?>
                </small>
            </div>
        </div>
    </div>

    <!-- Reglas de Puntos (Solo Administradores) -->
    <?php if($_SESSION['rol_id'] == 1): ?>
    <div class="card border-0 shadow-sm mb-4" style="background: var(--bg-card); border-radius: 12px;">
        <div class="card-header bg-transparent border-bottom py-3 px-4 d-flex justify-content-between align-items-center">
            <div class="d-flex align-items-center gap-2">
                <i class="bi bi-sliders2 text-primary fs-5"></i>
                <h5 class="mb-0 fw-bold" style="color: var(--text-primary); font-size: 15px;">Parámetros del Algoritmo de Puntos</h5>
            </div>
            <small class="text-muted" style="font-size: 11px;">
                <i class="bi bi-shield-lock-fill text-secondary"></i> Configuración Inmutable para Históricos
            </small>
        </div>
        <div class="card-body px-4 py-3">
            <form action="<?php echo BASE_URL; ?>puntos/guardarConfig" method="POST">
                <input type="hidden" name="csrf_token" value="<?php echo Controller::generateCsrfToken(); ?>">
                
                <div class="row g-3 align-items-end">
                    <div class="col-md-4">
                        <label class="form-label mb-1" style="font-size: 13px; font-weight: 600; color: var(--text-primary);">
                            <i class="bi bi-cart3 text-info me-1"></i> Consumo requerido por cada 1 Punto (S/)
                        </label>
                        <div class="input-group">
                            <span class="input-group-text border-0" style="background: var(--bg-dark); color: var(--text-secondary);">S/</span>
                            <input type="number" step="0.50" min="1" name="consumo_base" class="form-control border-0 fw-bold" style="background: var(--bg-dark); color: var(--text-primary);" value="<?php echo htmlspecialchars($data['configPuntos']['consumo_base']); ?>" required>
                        </div>
                        <small class="text-muted" style="font-size: 11px;">Ejemplo: S/ 10.00 = 1 punto ganado en POS</small>
                    </div>

                    <div class="col-md-4">
                        <label class="form-label mb-1" style="font-size: 13px; font-weight: 600; color: var(--text-primary);">
                            <i class="bi bi-tag text-success me-1"></i> Valor de Descuento por cada 1 Punto (S/)
                        </label>
                        <div class="input-group">
                            <span class="input-group-text border-0" style="background: var(--bg-dark); color: var(--text-secondary);">S/</span>
                            <input type="number" step="0.01" min="0.01" name="valor_canje" class="form-control border-0 fw-bold" style="background: var(--bg-dark); color: var(--text-primary);" value="<?php echo htmlspecialchars($data['configPuntos']['valor_canje']); ?>" required>
                        </div>
                        <small class="text-muted" style="font-size: 11px;">Ejemplo: 0.10 significa 10 puntos = S/ 1.00 de descuento</small>
                    </div>

                    <div class="col-md-2">
                        <label class="form-label mb-1" style="font-size: 13px; font-weight: 600; color: var(--text-primary);">
                            <i class="bi bi-power text-warning me-1"></i> Estado
                        </label>
                        <select name="habilitado" class="form-select border-0" style="background: var(--bg-dark); color: var(--text-primary);">
                            <option value="1" <?php echo $data['configPuntos']['habilitado'] == 1 ? 'selected' : ''; ?>>Habilitado</option>
                            <option value="0" <?php echo $data['configPuntos']['habilitado'] == 0 ? 'selected' : ''; ?>>Deshabilitado</option>
                        </select>
                    </div>

                    <div class="col-md-2 text-end">
                        <button type="submit" class="btn btn-primary w-100 py-2 d-flex align-items-center justify-content-center gap-1" style="border-radius: 8px; font-weight: 600; background: var(--accent-primary); border: none;">
                            <i class="bi bi-check2-circle"></i> Guardar Reglas
                        </button>
                    </div>
                </div>

                <div class="alert mt-3 mb-0 py-2 px-3 border-0 d-flex align-items-center gap-2" style="background: rgba(59, 130, 246, 0.08); border-radius: 8px; font-size: 12px; color: var(--text-secondary);">
                    <i class="bi bi-info-circle-fill text-primary fs-6"></i>
                    <span>
                        <strong>Garantía de Integridad Histórica:</strong> Al modificar estos factores, todas las ventas anteriores conservan de forma estática e inmutable los puntos ganados o canjeados en su respectivo momento, protegiendo las finanzas de la botica.
                    </span>
                </div>
            </form>
        </div>
    </div>
    <?php endif; ?>

    <!-- Tabla de Clientes y Puntos Acumulados -->
    <div class="card border-0 shadow-sm" style="background: var(--bg-card); border-radius: 12px; overflow: hidden;">
        <div class="card-header bg-transparent border-bottom py-3 px-4">
            <form action="<?php echo BASE_URL; ?>puntos/index" method="GET" class="row g-2 align-items-center">
                <div class="col-md-5">
                    <div class="input-group">
                        <span class="input-group-text border-0" style="background: var(--bg-dark); color: var(--text-secondary);">
                            <i class="bi bi-search"></i>
                        </span>
                        <input type="text" name="search" class="form-control border-0" placeholder="Buscar por cliente, DNI/RUC o teléfono..." value="<?php echo htmlspecialchars($data['search'] ?? '', ENT_QUOTES, 'UTF-8'); ?>" style="background: var(--bg-dark); color: var(--text-primary); font-size: 14px;">
                        <?php if(!empty($data['search'])): ?>
                            <a href="<?php echo BASE_URL; ?>puntos/index" class="btn btn-outline-secondary border-0" style="background: var(--bg-dark);"><i class="bi bi-x-lg"></i></a>
                        <?php endif; ?>
                    </div>
                </div>
                <div class="col-md-3">
                    <button type="submit" class="btn btn-secondary btn-sm px-3 py-2" style="border-radius: 8px;">
                        <i class="bi bi-filter"></i> Filtrar Clientes
                    </button>
                </div>
                <div class="col-md-4 text-md-end text-muted" style="font-size: 13px;">
                    Total: <strong><?php echo $data['total_registros']; ?></strong> clientes registrados
                </div>
            </form>
        </div>

        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0" style="color: var(--text-primary);">
                    <thead style="background: var(--bg-dark); font-size: 12px; text-transform: uppercase; color: var(--text-secondary); letter-spacing: 0.5px;">
                        <tr>
                            <th class="ps-4">Documento</th>
                            <th>Cliente</th>
                            <th>Teléfono</th>
                            <th class="text-center">Puntos Acumulados</th>
                            <th class="text-center">Equivalente Canje (S/)</th>
                            <th class="text-center">Auditorías / Movs</th>
                            <th class="text-end pe-4">Acciones</th>
                        </tr>
                    </thead>
                    <tbody style="font-size: 13px;">
                        <?php if(empty($data['clientes'])): ?>
                            <tr>
                                <td colspan="7" class="text-center py-5 text-muted">
                                    <i class="bi bi-inbox fs-1 d-block mb-2"></i>
                                    No se encontraron clientes registrados con los criterios de búsqueda.
                                </td>
                            </tr>
                        <?php else: ?>
                            <?php foreach($data['clientes'] as $c): 
                                $equivSoles = (float)$c['puntos_acumulados'] * (float)$data['configPuntos']['valor_canje'];
                            ?>
                            <tr>
                                <td class="ps-4">
                                    <span class="badge" style="background: var(--bg-dark); color: var(--text-primary); border: 1px solid rgba(128,128,128,0.25); font-size: 11px;">
                                        <?php echo htmlspecialchars($c['tipo_documento']); ?>: <?php echo htmlspecialchars($c['num_documento']); ?>
                                    </span>
                                </td>
                                <td>
                                    <span class="fw-bold d-block" style="color: var(--text-primary);"><?php echo htmlspecialchars($c['nombres']); ?></span>
                                    <small class="text-muted" style="font-size: 11px;">ID: #<?php echo $c['id']; ?></small>
                                </td>
                                <td>
                                    <?php echo !empty($c['telefono']) ? htmlspecialchars($c['telefono']) : '<span class="text-muted">-</span>'; ?>
                                </td>
                                <td class="text-center">
                                    <span class="badge px-2 py-1" style="background: rgba(251, 191, 36, 0.15); color: #d97706; border: 1px solid rgba(251, 191, 36, 0.4); font-size: 13px; font-weight: 700;">
                                        <i class="bi bi-star-fill me-1"></i> <?php echo number_format($c['puntos_acumulados']); ?>
                                    </span>
                                </td>
                                <td class="text-center">
                                    <span class="fw-bold" style="color: #059669;">
                                        S/ <?php echo number_format($equivSoles, 2); ?>
                                    </span>
                                </td>
                                <td class="text-center">
                                    <span class="badge bg-secondary-subtle text-secondary border px-2 py-1" style="font-size: 11px;">
                                        <?php echo (int)$c['total_movimientos']; ?> registros
                                    </span>
                                </td>
                                <td class="text-end pe-4">
                                    <button type="button" class="btn btn-sm btn-outline-warning me-1" title="Ajuste Manual de Puntos" onclick="abrirModalAjuste(<?php echo $c['id']; ?>, '<?php echo htmlspecialchars(addslashes($c['nombres'])); ?>', <?php echo (int)$c['puntos_acumulados']; ?>)">
                                        <i class="bi bi-pencil-square"></i> Ajustar
                                    </button>
                                    <button type="button" class="btn btn-sm btn-outline-info" title="Ver Historial de Puntos" onclick="cargarHistorialCliente(<?php echo $c['id']; ?>, '<?php echo htmlspecialchars(addslashes($c['nombres'])); ?>')">
                                        <i class="bi bi-clock-history"></i> Historial
                                    </button>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Paginación -->
        <?php if($data['total_paginas'] > 1): ?>
        <div class="card-footer bg-transparent border-top py-3 px-4 d-flex justify-content-between align-items-center">
            <span class="text-muted" style="font-size: 13px;">
                Página <?php echo $data['pagina_actual']; ?> de <?php echo $data['total_paginas']; ?>
            </span>
            <nav aria-label="Navegación de páginas">
                <ul class="pagination pagination-sm mb-0">
                    <?php if($data['pagina_actual'] > 1): ?>
                    <li class="page-item">
                        <a class="page-link" href="<?php echo BASE_URL; ?>puntos/index?page=<?php echo $data['pagina_actual'] - 1; ?>&search=<?php echo urlencode($data['search']); ?>">&laquo; Anterior</a>
                    </li>
                    <?php endif; ?>

                    <?php for($i = max(1, $data['pagina_actual'] - 2); $i <= min($data['total_paginas'], $data['pagina_actual'] + 2); $i++): ?>
                    <li class="page-item <?php echo $i == $data['pagina_actual'] ? 'active' : ''; ?>">
                        <a class="page-link" href="<?php echo BASE_URL; ?>puntos/index?page=<?php echo $i; ?>&search=<?php echo urlencode($data['search']); ?>"><?php echo $i; ?></a>
                    </li>
                    <?php endfor; ?>

                    <?php if($data['pagina_actual'] < $data['total_paginas']): ?>
                    <li class="page-item">
                        <a class="page-link" href="<?php echo BASE_URL; ?>puntos/index?page=<?php echo $data['pagina_actual'] + 1; ?>&search=<?php echo urlencode($data['search']); ?>">Siguiente &raquo;</a>
                    </li>
                    <?php endif; ?>
                </ul>
            </nav>
        </div>
        <?php endif; ?>
    </div>
</div>

<!-- Modal: Ajuste Manual de Puntos -->
<div class="modal fade" id="modalAjustePuntos" tabindex="-1" aria-labelledby="modalAjusteLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow-lg" style="background: var(--bg-card); color: var(--text-primary); border-radius: 14px;">
            <form action="<?php echo BASE_URL; ?>puntos/ajustar" method="POST">
                <input type="hidden" name="csrf_token" value="<?php echo Controller::generateCsrfToken(); ?>">
                <input type="hidden" name="id_cliente" id="ajusteIdCliente">

                <div class="modal-header border-bottom py-3 px-4">
                    <h5 class="modal-title fw-bold" id="modalAjusteLabel">
                        <i class="bi bi-pencil-square text-warning me-2"></i> Ajuste Manual de Puntos
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body px-4 py-3">
                    <div class="p-3 mb-3" style="background: var(--bg-dark); border-radius: 8px;">
                        <span class="text-muted d-block" style="font-size: 11px; text-transform: uppercase;">Cliente Seleccionado</span>
                        <strong id="ajusteClienteNombre" class="fs-6" style="color: var(--text-primary);">-</strong>
                        <div class="d-flex justify-content-between align-items-center mt-2 pt-2 border-top border-secondary border-opacity-25" style="font-size: 12px;">
                            <span class="text-muted">Saldo Actual:</span>
                            <span id="ajusteSaldoActual" class="badge bg-warning text-dark fw-bold">0 pts</span>
                        </div>
                    </div>

                    <div class="mb-3">
                        <label class="form-label mb-1" style="font-size: 13px; font-weight: 600;">Tipo de Operación *</label>
                        <div class="btn-group w-100" role="group">
                            <input type="radio" class="btn-check" name="operacion" id="opSumar" value="sumar" checked>
                            <label class="btn btn-outline-success" for="opSumar"><i class="bi bi-plus-circle-fill me-1"></i> Sumar / Acreditar</label>

                            <input type="radio" class="btn-check" name="operacion" id="opRestar" value="restar">
                            <label class="btn btn-outline-danger" for="opRestar"><i class="bi bi-dash-circle-fill me-1"></i> Restar / Deducir</label>
                        </div>
                    </div>

                    <div class="mb-3">
                        <label class="form-label mb-1" style="font-size: 13px; font-weight: 600;">Cantidad de Puntos a Ajustar *</label>
                        <input type="number" min="1" step="1" name="puntos" class="form-control border-0 fw-bold" style="background: var(--bg-dark); color: var(--text-primary); font-size: 16px;" placeholder="Ej. 50" required>
                    </div>

                    <div class="mb-3">
                        <label class="form-label mb-1" style="font-size: 13px; font-weight: 600;">Motivo / Justificación del Ajuste *</label>
                        <textarea name="motivo" class="form-control border-0" rows="3" placeholder="Ej. Compensación por falla en sistema, bono de cortesía, corrección por canje erróneo..." style="background: var(--bg-dark); color: var(--text-primary); font-size: 13px;" required></textarea>
                        <small class="text-muted" style="font-size: 11px;">Este motivo quedará auditado con su usuario para trazabilidad.</small>
                    </div>
                </div>
                <div class="modal-footer border-top py-2 px-4">
                    <button type="button" class="btn btn-secondary btn-sm" data-bs-dismiss="modal">Cancelar</button>
                    <button type="submit" class="btn btn-warning btn-sm px-3 fw-bold">
                        <i class="bi bi-check-lg"></i> Confirmar Ajuste
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Modal: Historial y Auditoría de Puntos -->
<div class="modal fade" id="modalHistorialPuntos" tabindex="-1" aria-labelledby="modalHistorialLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content border-0 shadow-lg" style="background: var(--bg-card); color: var(--text-primary); border-radius: 14px;">
            <div class="modal-header border-bottom py-3 px-4">
                <div>
                    <h5 class="modal-title fw-bold" id="modalHistorialLabel">
                        <i class="bi bi-clock-history text-info me-2"></i> Historial y Auditoría de Puntos
                    </h5>
                    <small id="historialClienteNombre" class="text-muted" style="font-size: 12px;">Cliente: -</small>
                </div>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body p-0">
                <div class="table-responsive" style="max-height: 420px;">
                    <table class="table table-hover align-middle mb-0" style="color: var(--text-primary);">
                        <thead style="background: var(--bg-dark); font-size: 11px; text-transform: uppercase; color: var(--text-secondary); position: sticky; top: 0; z-index: 1;">
                            <tr>
                                <th class="ps-4">Fecha / Hora</th>
                                <th>Tipo</th>
                                <th>Motivo / Justificación</th>
                                <th class="text-center">Puntos</th>
                                <th class="text-center">Saldo</th>
                                <th class="text-end pe-4">Usuario</th>
                            </tr>
                        </thead>
                        <tbody id="bodyHistorialPuntos" style="font-size: 12px;">
                            <tr>
                                <td colspan="6" class="text-center py-4 text-muted">Cargando movimientos...</td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
            <div class="modal-footer border-top py-2 px-4">
                <button type="button" class="btn btn-secondary btn-sm" data-bs-dismiss="modal">Cerrar</button>
            </div>
        </div>
    </div>
</div>

<script>
function abrirModalAjuste(id, nombre, saldo) {
    document.getElementById('ajusteIdCliente').value = id;
    document.getElementById('ajusteClienteNombre').innerText = nombre;
    document.getElementById('ajusteSaldoActual').innerText = saldo + ' pts';
    const modal = new bootstrap.Modal(document.getElementById('modalAjustePuntos'));
    modal.show();
}

function cargarHistorialCliente(id, nombre) {
    document.getElementById('historialClienteNombre').innerText = 'Cliente: ' + nombre;
    const tbody = document.getElementById('bodyHistorialPuntos');
    tbody.innerHTML = '<tr><td colspan="6" class="text-center py-4 text-muted"><div class="spinner-border spinner-border-sm text-info me-2"></div> Consultando historial...</td></tr>';
    
    const modal = new bootstrap.Modal(document.getElementById('modalHistorialPuntos'));
    modal.show();

    fetch('<?php echo BASE_URL; ?>puntos/historialAjax/' + id)
        .then(r => r.json())
        .then(res => {
            if (res.status === 'success' && res.data.length > 0) {
                let html = '';
                res.data.forEach(item => {
                    let badgeTipo = 'bg-secondary';
                    let sign = item.puntos > 0 ? '+' : '';
                    let colorPuntos = item.puntos > 0 ? '#10b981' : '#ef4444';

                    if (item.tipo === 'ACUMULACION') badgeTipo = 'bg-success-subtle text-success border border-success-subtle';
                    else if (item.tipo === 'CANJE') badgeTipo = 'bg-warning-subtle text-warning border border-warning-subtle';
                    else if (item.tipo === 'AJUSTE_MANUAL') badgeTipo = 'bg-primary-subtle text-primary border border-primary-subtle';
                    else if (item.tipo === 'ANULACION') badgeTipo = 'bg-danger-subtle text-danger border border-danger-subtle';

                    let refVenta = '';
                    if (item.tipo_comprobante && item.num_comprobante) {
                        refVenta = `<br><small class="text-muted">${item.tipo_comprobante} ${item.serie_comprobante || ''}-${item.num_comprobante}</small>`;
                    }

                    html += `<tr>
                        <td class="ps-4 text-muted">${item.created_at}</td>
                        <td><span class="badge ${badgeTipo}" style="font-size:10px;">${item.tipo}</span></td>
                        <td>${item.motivo}${refVenta}</td>
                        <td class="text-center fw-bold" style="color: ${colorPuntos}; font-size:13px;">${sign}${item.puntos}</td>
                        <td class="text-center fw-semibold text-muted">${item.saldo_anterior} &rarr; <strong class="text-white">${item.saldo_nuevo}</strong></td>
                        <td class="text-end pe-4 text-muted"><i class="bi bi-person-circle me-1"></i>${item.usuario_nombre}</td>
                    </tr>`;
                });
                tbody.innerHTML = html;
            } else {
                tbody.innerHTML = '<tr><td colspan="6" class="text-center py-4 text-muted">El cliente no registra movimientos de puntos aún.</td></tr>';
            }
        })
        .catch(err => {
            tbody.innerHTML = '<tr><td colspan="6" class="text-center py-4 text-danger">Error al cargar el historial.</td></tr>';
        });
}
</script>
