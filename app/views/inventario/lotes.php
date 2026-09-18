<div class="page-content">
    <div class="d-flex justify-content-between align-items-center mb-4 flex-wrap gap-2">
        <div>
            <h1 class="page-title">Control de Vencimientos (FEFO)</h1>
            <div class="page-subtitle">Supervisión estratégica de lotes farmacéuticos según política First Expired, First Out.</div>
        </div>
        <div class="d-flex gap-2">
            <a href="<?php echo BASE_URL; ?>reporte/vencimientos_pdf?rango=<?php echo urlencode($data['filtros']['alerta'] ?? '90'); ?>&id_laboratorio=<?php echo urlencode($data['filtros']['id_laboratorio'] ?? ''); ?>" target="_blank" class="btn btn-warning btn-sm fw-bold text-dark" style="height: 38px; display: inline-flex; align-items: center;">
                <i class="bi bi-file-earmark-pdf me-1"></i> Imprimir PDF
            </a>
            <a href="<?php echo BASE_URL; ?>reporte/vencimientos_excel?rango=<?php echo urlencode($data['filtros']['alerta'] ?? '90'); ?>&id_laboratorio=<?php echo urlencode($data['filtros']['id_laboratorio'] ?? ''); ?>" class="btn btn-outline-success btn-sm fw-bold" style="height: 38px; display: inline-flex; align-items: center;">
                <i class="bi bi-file-earmark-spreadsheet me-1"></i> Exportar CSV
            </a>
        </div>
    </div>

    <!-- Tarjetas Resumen KPI -->
    <div class="row g-3 mb-4">
        <div class="col-md-2 col-sm-4 col-6">
            <div class="card card-metric p-3 text-center h-100" style="border-top: 3px solid var(--accent-primary);">
                <div class="text-muted" style="font-size: 11px; font-weight: 700; text-transform: uppercase;">Lotes con Stock</div>
                <div class="mt-1" style="font-size: 22px; font-weight: 800; color: var(--text-primary);">
                    <?php echo number_format($data['kpis']['total_lotes'] ?? 0); ?>
                </div>
                <small class="text-muted" style="font-size: 11px;"><?php echo number_format($data['kpis']['unidades_totales'] ?? 0); ?> unidades</small>
            </div>
        </div>
        <div class="col-md-2 col-sm-4 col-6">
            <a href="<?php echo BASE_URL; ?>inventario/lotes?alerta=vencidos" class="text-decoration-none">
                <div class="card card-metric p-3 text-center h-100" style="border-top: 3px solid #ea5455; background: rgba(234, 84, 85, 0.04);">
                    <div style="font-size: 11px; font-weight: 700; text-transform: uppercase; color: #ea5455;">
                        <i class="bi bi-exclamation-octagon-fill"></i> Vencidos
                    </div>
                    <div class="mt-1" style="font-size: 22px; font-weight: 800; color: #ea5455;">
                        <?php echo number_format($data['kpis']['vencidos'] ?? 0); ?>
                    </div>
                    <small style="font-size: 11px; color: #ea5455;">Retirar urgente</small>
                </div>
            </a>
        </div>
        <div class="col-md-3 col-sm-4 col-6">
            <a href="<?php echo BASE_URL; ?>inventario/lotes?alerta=criticos_30" class="text-decoration-none">
                <div class="card card-metric p-3 text-center h-100" style="border-top: 3px solid #ff9f43; background: rgba(255, 159, 67, 0.04);">
                    <div style="font-size: 11px; font-weight: 700; text-transform: uppercase; color: #ff9f43;">
                        <i class="bi bi-hourglass-bottom"></i> Críticos (≤ 30 Días)
                    </div>
                    <div class="mt-1" style="font-size: 22px; font-weight: 800; color: #ff9f43;">
                        <?php echo number_format($data['kpis']['criticos_30'] ?? 0); ?>
                    </div>
                    <small style="font-size: 11px; color: #ff9f43;">Promoción / Merma</small>
                </div>
            </a>
        </div>
        <div class="col-md-3 col-sm-6 col-6">
            <a href="<?php echo BASE_URL; ?>inventario/lotes?alerta=riesgo_90" class="text-decoration-none">
                <div class="card card-metric p-3 text-center h-100" style="border-top: 3px solid #ffc107; background: rgba(255, 193, 7, 0.04);">
                    <div style="font-size: 11px; font-weight: 700; text-transform: uppercase; color: #d4a000;">
                        <i class="bi bi-clock-history"></i> Riesgo (≤ 90 Días)
                    </div>
                    <div class="mt-1" style="font-size: 22px; font-weight: 800; color: #d4a000;">
                        <?php echo number_format($data['kpis']['riesgo_90'] ?? 0); ?>
                    </div>
                    <small style="font-size: 11px; color: #d4a000;">Priorizar en mostrador</small>
                </div>
            </a>
        </div>
        <div class="col-md-2 col-sm-6 col-12">
            <a href="<?php echo BASE_URL; ?>inventario/lotes?alerta=sanos" class="text-decoration-none">
                <div class="card card-metric p-3 text-center h-100" style="border-top: 3px solid #28c76f; background: rgba(40, 199, 111, 0.04);">
                    <div style="font-size: 11px; font-weight: 700; text-transform: uppercase; color: #28c76f;">
                        <i class="bi bi-shield-check"></i> Sanos (> 90 Días)
                    </div>
                    <div class="mt-1" style="font-size: 22px; font-weight: 800; color: #28c76f;">
                        <?php echo number_format($data['kpis']['sanos'] ?? 0); ?>
                    </div>
                    <small style="font-size: 11px; color: #28c76f;">Stock saludable</small>
                </div>
            </a>
        </div>
    </div>

    <!-- Barra de Filtros Avanzados -->
    <div class="card-metric mb-3 p-3">
        <form action="<?php echo BASE_URL; ?>inventario/lotes" method="GET" class="row g-2 align-items-end">
            <!-- Búsqueda texto -->
            <div class="col-md-3 col-sm-6">
                <label class="form-label mb-1" style="font-size: 11px; font-weight: 700; color: var(--text-secondary);">Producto o N° Lote:</label>
                <div class="search-box w-100">
                    <i class="bi bi-search"></i>
                    <input type="text" name="search" value="<?php echo htmlspecialchars($data['filtros']['search'] ?? ''); ?>" placeholder="Nombre, genérico o lote...">
                </div>
            </div>

            <!-- Alerta / Estado FEFO -->
            <div class="col-md-2 col-sm-6">
                <label class="form-label mb-1" style="font-size: 11px; font-weight: 700; color: var(--text-secondary);">Estado de Alerta:</label>
                <select name="alerta" class="form-select form-select-sm">
                    <option value="">-- Todos los Estados --</option>
                    <option value="vencidos" <?php echo ($data['filtros']['alerta'] ?? '') === 'vencidos' ? 'selected' : ''; ?>>🔴 Vencidos</option>
                    <option value="criticos_30" <?php echo ($data['filtros']['alerta'] ?? '') === 'criticos_30' ? 'selected' : ''; ?>>🟠 Críticos (≤ 30 días)</option>
                    <option value="riesgo_90" <?php echo ($data['filtros']['alerta'] ?? '') === 'riesgo_90' ? 'selected' : ''; ?>>🟡 En Riesgo (≤ 90 días)</option>
                    <option value="sanos" <?php echo ($data['filtros']['alerta'] ?? '') === 'sanos' ? 'selected' : ''; ?>>🟢 Sanos (> 90 días)</option>
                </select>
            </div>

            <!-- Laboratorio -->
            <div class="col-md-3 col-sm-6">
                <label class="form-label mb-1" style="font-size: 11px; font-weight: 700; color: var(--text-secondary);">Laboratorio:</label>
                <select name="id_laboratorio" class="form-select form-select-sm">
                    <option value="">-- Todos los Laboratorios --</option>
                    <?php if(!empty($data['laboratorios'])): foreach($data['laboratorios'] as $lab): ?>
                        <option value="<?php echo $lab['id']; ?>" <?php echo ($data['filtros']['id_laboratorio'] ?? '') == $lab['id'] ? 'selected' : ''; ?>>
                            <?php echo htmlspecialchars($lab['nombre']); ?>
                        </option>
                    <?php endforeach; endif; ?>
                </select>
            </div>

            <!-- Categoría -->
            <div class="col-md-2 col-sm-6">
                <label class="form-label mb-1" style="font-size: 11px; font-weight: 700; color: var(--text-secondary);">Categoría:</label>
                <select name="id_categoria" class="form-select form-select-sm">
                    <option value="">-- Todas las Categorías --</option>
                    <?php if(!empty($data['categorias'])): foreach($data['categorias'] as $cat): ?>
                        <option value="<?php echo $cat['id']; ?>" <?php echo ($data['filtros']['id_categoria'] ?? '') == $cat['id'] ? 'selected' : ''; ?>>
                            <?php echo htmlspecialchars($cat['nombre']); ?>
                        </option>
                    <?php endforeach; endif; ?>
                </select>
            </div>

            <!-- Botones -->
            <div class="col-md-2 col-sm-12 d-flex gap-2">
                <button type="submit" class="btn btn-sm btn-success fw-bold flex-grow-1" style="height: 38px;">
                    <i class="bi bi-funnel"></i> Filtrar
                </button>
                <a href="<?php echo BASE_URL; ?>inventario/lotes" class="btn btn-sm btn-outline-secondary" style="height: 38px; display: flex; align-items: center;" title="Limpiar Filtros">
                    <i class="bi bi-x-circle"></i>
                </a>
            </div>
        </form>
    </div>

    <!-- Tabla de Lotes FEFO -->
    <div class="card-metric">
        <div class="table-responsive">
            <table class="table table-hover align-middle" style="width: 100%; font-size: 14px;">
                <thead>
                    <tr>
                        <th>Medicamento / Producto</th>
                        <th width="150">Código Lote</th>
                        <th width="140" class="text-center">Vencimiento</th>
                        <th width="160" class="text-center">Semáforo FEFO</th>
                        <th width="130" class="text-end">Stock Actual</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if(empty($data['lotes'])): ?>
                    <tr><td colspan="5" class="text-center text-muted py-4"><i class="bi bi-check2-circle fs-4 d-block mb-2 text-success"></i>No hay lotes que coincidan con los filtros seleccionados</td></tr>
                    <?php else: ?>
                    <?php foreach($data['lotes'] as $lote): 
                        $dias = (int)($lote['dias_restantes'] ?? 0);
                        if ($dias < 0) {
                            $badgeClass = 'bg-danger text-white border-0';
                            $badgeText = '¡VENCIDO! (' . abs($dias) . 'd atrás)';
                            $rowStyle = 'background-color: rgba(234, 84, 85, 0.05);';
                        } elseif ($dias <= 30) {
                            $badgeClass = 'bg-danger text-white border-0';
                            $badgeText = 'CRÍTICO: ' . $dias . ' días';
                            $rowStyle = 'background-color: rgba(234, 84, 85, 0.03);';
                        } elseif ($dias <= 90) {
                            $badgeClass = 'bg-warning text-dark border-0';
                            $badgeText = 'RIESGO: ' . $dias . ' días';
                            $rowStyle = 'background-color: rgba(255, 193, 7, 0.03);';
                        } else {
                            $badgeClass = 'status-delivered';
                            $badgeText = 'Sano (' . $dias . ' días)';
                            $rowStyle = '';
                        }
                    ?>
                    <tr style="<?php echo $rowStyle; ?>">
                        <td>
                            <div style="font-weight:700; color:var(--text-primary);"><?php echo htmlspecialchars($lote['nombre_comercial']); ?></div>
                            <div style="font-size:12px; color:var(--text-secondary);">
                                <?php echo htmlspecialchars($lote['nombre_generico'] ?? ''); ?>
                                <?php echo !empty($lote['concentracion']) ? ' - ' . htmlspecialchars($lote['concentracion']) : ''; ?>
                                <?php echo !empty($lote['forma_farmaceutica']) ? ' (' . htmlspecialchars($lote['forma_farmaceutica']) . ')' : ''; ?>
                            </div>
                            <div class="mt-1 d-flex gap-1 flex-wrap" style="font-size: 11px;">
                                <?php if(!empty($lote['laboratorio'])): ?>
                                    <span class="badge" style="background: rgba(255, 255, 255, 0.06); color: var(--text-secondary); border: 1px solid var(--border-color);">
                                        <i class="bi bi-building"></i> <?php echo htmlspecialchars($lote['laboratorio']); ?>
                                    </span>
                                <?php endif; ?>
                                <?php if(!empty($lote['categoria'])): ?>
                                    <span class="badge" style="background: rgba(0, 207, 232, 0.08); color: #00CFE8; border: 1px solid rgba(0, 207, 232, 0.2);">
                                        <i class="bi bi-tag"></i> <?php echo htmlspecialchars($lote['categoria']); ?>
                                    </span>
                                <?php endif; ?>
                            </div>
                        </td>
                        <td style="color:var(--text-secondary); font-family:monospace; font-weight: 700; font-size: 13px;">
                            <?php echo htmlspecialchars($lote['codigo_lote']); ?>
                        </td>
                        <td class="text-center" style="font-weight: 600;">
                            <?php echo date('d/m/Y', strtotime($lote['fecha_vencimiento'])); ?>
                        </td>
                        <td class="text-center">
                            <span class="badge-status <?php echo $badgeClass; ?>" style="font-size: 11px; padding: 4px 8px;">
                                <?php echo $badgeText; ?>
                            </span>
                        </td>
                        <td class="text-end" style="font-size: 16px; font-weight:800; color: var(--accent-primary);">
                            <?php echo number_format($lote['cantidad_disponible']); ?> <span style="font-size: 11px; font-weight: 500; color: var(--text-secondary);">uds</span>
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
                Mostrando página <strong><?php echo $data['pagina_actual'] ?? 1; ?></strong> de <strong><?php echo $data['total_paginas'] ?? 1; ?></strong> (Total: <strong><?php echo number_format($data['total_registros'] ?? count($data['lotes'])); ?></strong> lotes)
            </div>
            <?php if(($data['total_paginas'] ?? 1) > 1): ?>
            <nav aria-label="Paginación de lotes">
                <ul class="pagination pagination-sm mb-0">
                    <?php
                    $queryParams = $_GET;
                    if(($data['pagina_actual'] ?? 1) > 1):
                        $queryParams['page'] = $data['pagina_actual'] - 1;
                        $prevUrl = BASE_URL . 'inventario/lotes?' . http_build_query($queryParams);
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
                        $pageUrl = BASE_URL . 'inventario/lotes?' . http_build_query($queryParams);
                    ?>
                        <li class="page-item <?php echo $i == ($data['pagina_actual'] ?? 1) ? 'active' : ''; ?>">
                            <a class="page-link" href="<?php echo $pageUrl; ?>"><?php echo $i; ?></a>
                        </li>
                    <?php endfor; ?>

                    <?php
                    if(($data['pagina_actual'] ?? 1) < ($data['total_paginas'] ?? 1)):
                        $queryParams['page'] = $data['pagina_actual'] + 1;
                        $nextUrl = BASE_URL . 'inventario/lotes?' . http_build_query($queryParams);
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

