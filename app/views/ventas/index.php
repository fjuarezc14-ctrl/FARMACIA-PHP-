<div class="page-content">
    <div class="d-flex justify-content-between align-items-center mb-4 flex-wrap gap-2">
        <div>
            <h1 class="page-title">Boletas y Facturas Emitidas</h1>
            <div class="page-subtitle">Historial de tickets y ventas procesadas en caja con filtros optimizados.</div>
        </div>
        <div class="d-flex gap-2">
            <a href="<?php echo BASE_URL; ?>venta/pos" class="btn btn-success fw-bold">
                <i class="bi bi-cart-plus-fill me-1"></i> Ir al Punto de Venta
            </a>
        </div>
    </div>
    
    <?php if(isset($_SESSION['success'])): ?>
    <div class="alert alert-success alert-dismissible fade show" role="alert">
        <i class="bi bi-check-circle-fill"></i> <?php echo $_SESSION['success']; unset($_SESSION['success']); ?>
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
    <?php endif; ?>
    
    <?php if(isset($_SESSION['error'])): ?>
    <div class="alert alert-danger alert-dismissible fade show" role="alert">
        <i class="bi bi-exclamation-triangle-fill"></i> <?php echo $_SESSION['error']; unset($_SESSION['error']); ?>
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
    <?php endif; ?>

    <!-- PANEL DE FILTROS MULTICRITERIO -->
    <div class="card-metric mb-3 p-3" style="border-radius: 12px; background: var(--bg-card);">
        <form method="GET" action="<?php echo BASE_URL; ?>venta/index" class="row g-2 align-items-end">
            <div class="col-md-2 col-sm-6">
                <label class="form-label mb-1" style="font-size: 11px; font-weight: 700; color: var(--text-secondary);">Desde:</label>
                <input type="date" name="fecha_inicio" class="form-control form-control-sm" value="<?php echo htmlspecialchars($data['filtros']['fecha_inicio'] ?? ''); ?>">
            </div>
            <div class="col-md-2 col-sm-6">
                <label class="form-label mb-1" style="font-size: 11px; font-weight: 700; color: var(--text-secondary);">Hasta:</label>
                <input type="date" name="fecha_fin" class="form-control form-control-sm" value="<?php echo htmlspecialchars($data['filtros']['fecha_fin'] ?? ''); ?>">
            </div>
            <div class="col-md-3 col-sm-6">
                <label class="form-label mb-1" style="font-size: 11px; font-weight: 700; color: var(--text-secondary);">Cliente:</label>
                <select name="id_cliente" class="form-select form-select-sm">
                    <option value="">-- Todos los Clientes --</option>
                    <?php if(!empty($data['clientes'])): foreach($data['clientes'] as $cli): ?>
                        <option value="<?php echo $cli['id']; ?>" <?php echo (isset($data['filtros']['id_cliente']) && $data['filtros']['id_cliente'] == $cli['id']) ? 'selected' : ''; ?>>
                            <?php echo htmlspecialchars($cli['num_documento'] . ' - ' . $cli['nombres']); ?>
                        </option>
                    <?php endforeach; endif; ?>
                </select>
            </div>
            <div class="col-md-2 col-sm-6">
                <label class="form-label mb-1" style="font-size: 11px; font-weight: 700; color: var(--text-secondary);">Cajero:</label>
                <select name="id_usuario" class="form-select form-select-sm">
                    <option value="">-- Todos --</option>
                    <?php if(!empty($data['cajeros'])): foreach($data['cajeros'] as $caj): ?>
                        <option value="<?php echo $caj['id']; ?>" <?php echo (isset($data['filtros']['id_usuario']) && $data['filtros']['id_usuario'] == $caj['id']) ? 'selected' : ''; ?>>
                            <?php echo htmlspecialchars($caj['nombres']); ?>
                        </option>
                    <?php endforeach; endif; ?>
                </select>
            </div>
            <div class="col-md-2 col-sm-6">
                <label class="form-label mb-1" style="font-size: 11px; font-weight: 700; color: var(--text-secondary);">Método:</label>
                <select name="metodo_pago" class="form-select form-select-sm">
                    <option value="">-- Todos --</option>
                    <option value="Efectivo" <?php echo ($data['filtros']['metodo_pago'] ?? '') === 'Efectivo' ? 'selected' : ''; ?>>Efectivo</option>
                    <option value="Yape/Plin" <?php echo ($data['filtros']['metodo_pago'] ?? '') === 'Yape/Plin' ? 'selected' : ''; ?>>Yape / Plin</option>
                    <option value="Tarjeta" <?php echo ($data['filtros']['metodo_pago'] ?? '') === 'Tarjeta' ? 'selected' : ''; ?>>Tarjeta</option>
                    <option value="Mixto" <?php echo ($data['filtros']['metodo_pago'] ?? '') === 'Mixto' ? 'selected' : ''; ?>>Mixto</option>
                </select>
            </div>
            <div class="col-md-1 col-sm-6">
                <label class="form-label mb-1" style="font-size: 11px; font-weight: 700; color: var(--text-secondary);">Límite:</label>
                <select name="limit" class="form-select form-select-sm">
                    <option value="15" <?php echo ($data['limit'] ?? 25) == 15 ? 'selected' : ''; ?>>15</option>
                    <option value="25" <?php echo ($data['limit'] ?? 25) == 25 ? 'selected' : ''; ?>>25</option>
                    <option value="50" <?php echo ($data['limit'] ?? 25) == 50 ? 'selected' : ''; ?>>50</option>
                    <option value="100" <?php echo ($data['limit'] ?? 25) == 100 ? 'selected' : ''; ?>>100</option>
                </select>
            </div>
            <div class="col-12 d-flex justify-content-between align-items-center mt-2 pt-2 border-top border-secondary border-opacity-25 flex-wrap gap-2">
                <div class="text-muted" style="font-size: 12px;">
                    <i class="bi bi-funnel-fill text-success"></i> Se encontraron <strong><?php echo $data['total_registros'] ?? count($data['ventas']); ?></strong> ventas registradas.
                </div>
                <div class="d-flex gap-2">
                    <a href="<?php echo BASE_URL; ?>venta/index" class="btn btn-sm btn-outline-secondary">
                        <i class="bi bi-x-circle"></i> Limpiar
                    </a>
                    <button type="submit" class="btn btn-sm btn-success fw-bold">
                        <i class="bi bi-search"></i> Filtrar Historial
                    </button>
                </div>
            </div>
        </form>
    </div>

    <div class="card-metric">
        <div class="table-responsive">
            <table class="table table-hover align-middle" style="width: 100%; font-size: 14px;">
                <thead>
                    <tr>
                        <th width="120">Fecha/Hora</th>
                        <th>Cliente</th>
                        <th>Cajero</th>
                        <th class="text-center">Comprobante</th>
                        <th class="text-center">Método Pago</th>
                        <th class="text-center">SUNAT</th>
                        <th class="text-end">Monto Cobrado</th>
                        <th width="100" class="text-center">Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if(empty($data['ventas'])): ?>
                    <tr><td colspan="8" class="text-center text-muted py-4">No se encontraron ventas para los filtros seleccionados.</td></tr>
                    <?php else: ?>
                    <?php foreach($data['ventas'] as $v): ?>
                    <tr>
                        <td style="color:var(--text-secondary); font-family:monospace;">
                            <?php echo date('d/m/Y', strtotime($v['fecha_venta'])) . "<br><small>" . date('H:i', strtotime($v['fecha_venta'])) . "</small>"; ?>
                        </td>
                        <td style="font-weight:700; color:var(--text-primary); font-size: 13px;">
                            <?php echo htmlspecialchars($v['cliente']); ?>
                        </td>
                        <td style="color:var(--text-secondary); font-size: 13px;">
                            <?php echo explode(' ', $v['cajero'])[0]; ?>
                        </td>
                        <td class="text-center">
                            <?php if ($v['estado'] == 'Anulada'): ?>
                                <span class="badge bg-danger shadow-sm"><i class="bi bi-x-circle"></i> Anulada</span><br>
                                <strike style="font-size:10px; color:var(--text-secondary);"><?php echo htmlspecialchars($v['serie_comprobante'] . '-' . $v['num_comprobante']); ?></strike>
                            <?php else: ?>
                                <span style="border: 1px solid var(--accent-primary); color: var(--accent-primary); padding: 2px 8px; border-radius: 4px; font-size: 11px; font-weight:700;">
                                    <?php echo htmlspecialchars($v['serie_comprobante'] . '-' . $v['num_comprobante']); ?>
                                </span><br>
                                <span style="font-size:10px; color:var(--text-secondary);"><?php echo htmlspecialchars($v['tipo_comprobante']); ?></span>
                            <?php endif; ?>
                        </td>
                        <td class="text-center">
                            <?php 
                                $bColor = 'bg-secondary';
                                if($v['metodo_pago'] == 'Yape' || $v['metodo_pago'] == 'Yape/Plin') $bColor = 'bg-info text-dark';
                                else if($v['metodo_pago'] == 'Tarjeta') $bColor = 'bg-primary';
                                else if($v['metodo_pago'] == 'Mixto') $bColor = 'bg-warning text-dark';
                                else $bColor = 'bg-success';
                            ?>
                            <span class="badge <?php echo $bColor; ?>"><?php echo $v['metodo_pago']; ?></span>
                            <?php if(!empty($v['num_operacion_trans'])): ?>
                                <br><small style="font-size:9px; color:var(--text-secondary);">Op: <?php echo htmlspecialchars($v['num_operacion_trans']); ?></small>
                            <?php endif; ?>
                            <?php if(!empty($v['num_operacion_tarj'])): ?>
                                <br><small style="font-size:9px; color:var(--text-secondary);">Ref: <?php echo htmlspecialchars($v['num_operacion_tarj']); ?></small>
                            <?php endif; ?>
                        </td>
                        <td class="text-center">
                            <?php
                            $sunat_estado = $v['estado_sunat'] ?? 'N/A';
                            if ($sunat_estado === 'Generado Local'):
                            ?>
                                <span class="badge" style="background: linear-gradient(135deg, #3B82F6, #6D28D9); font-size: 10px;">
                                    <i class="bi bi-file-earmark-check-fill"></i> XML Listo
                                </span>
                            <?php elseif ($v['tipo_comprobante'] === 'Ticket'): ?>
                                <span class="badge bg-secondary" style="font-size:10px;">Interno</span>
                            <?php else: ?>
                                <span class="badge bg-warning text-dark" style="font-size:10px;">Pendiente</span>
                            <?php endif; ?>
                        </td>
                        <td class="text-end">
                            <div style="font-size: 15px; font-weight:700; color: var(--accent-primary);">
                                S/ <?php echo number_format($v['total'], 2); ?>
                            </div>
                            <?php if(!empty($v['descuento']) && (float)$v['descuento'] > 0): ?>
                                <small class="text-danger d-block mt-1" style="font-size: 11px; font-weight: 600;">
                                    <i class="bi bi-tag-fill me-1"></i>Desc: -S/ <?php echo number_format($v['descuento'], 2); ?>
                                    <?php if(!empty($v['motivo_descuento'])): ?>
                                        <br><span class="text-muted fst-italic" style="font-size: 10px; font-weight: normal;"><?php echo htmlspecialchars($v['motivo_descuento']); ?></span>
                                    <?php endif; ?>
                                </small>
                            <?php endif; ?>
                        </td>
                        <td class="text-center">
                            <div class="d-flex gap-1 justify-content-center flex-wrap">
                                <a href="<?php echo BASE_URL; ?>venta/ticket/<?php echo $v['id']; ?>" target="_blank" class="btn btn-sm btn-outline-secondary" title="Ver Ticket (tiquetera)">
                                    <i class="bi bi-printer"></i>
                                </a>
                                <a href="<?php echo BASE_URL; ?>venta/pdf/<?php echo $v['id']; ?>" target="_blank" class="btn btn-sm btn-outline-success" title="Ver / Descargar PDF Comprobante A4">
                                    <i class="bi bi-file-earmark-pdf-fill"></i>
                                </a>
                                <?php
                                // Check if XML file exists for this sale
                                $configVals = $data['config'] ?? [];
                                $ruc_empresa = !empty($configVals['ruc']['valor']) ? $configVals['ruc']['valor'] : '';
                                $tipoDocCode = ($v['tipo_comprobante'] === 'Factura') ? '01' : '03';
                                $xmlFile = "{$ruc_empresa}-{$tipoDocCode}-{$v['serie_comprobante']}-{$v['num_comprobante']}.xml";
                                $xmlPath = (defined('BASE_PATH') ? BASE_PATH : dirname(dirname(dirname(__DIR__))) . DIRECTORY_SEPARATOR) . "public/sunat/xml/{$xmlFile}";
                                if (($v['tipo_comprobante'] === 'Boleta' || $v['tipo_comprobante'] === 'Factura') && file_exists($xmlPath)):
                                ?>
                                <a href="<?php echo BASE_URL; ?>sunat/xml/<?php echo $xmlFile; ?>" download class="btn btn-sm btn-outline-primary" title="Descargar XML SUNAT">
                                    <i class="bi bi-file-earmark-code"></i>
                                </a>
                                <?php endif; ?>
                                <?php if ($v['estado'] != 'Anulada'): ?>
                                <form action="<?php echo BASE_URL; ?>venta/anular" method="POST" class="d-inline" onsubmit="return confirm('¿Está seguro de ANULAR esta venta? El stock se devolverá al almacén de forma íntegra.')">
                                    <?php echo Controller::csrfField(); ?>
                                    <input type="hidden" name="id_venta" value="<?php echo $v['id']; ?>">
                                    <button type="submit" class="btn btn-sm btn-outline-danger" title="Anular Venta">
                                        <i class="bi bi-x-circle"></i>
                                    </button>
                                </form>
                                <?php endif; ?>
                            </div>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>

        <!-- PAGINACIÓN SERVER-SIDE -->
        <?php if(($data['total_paginas'] ?? 1) > 1): ?>
        <div class="d-flex justify-content-between align-items-center p-3 border-top border-secondary border-opacity-25 flex-wrap gap-2">
            <div class="text-muted" style="font-size: 13px;">
                Página <strong><?php echo $data['pagina_actual']; ?></strong> de <strong><?php echo $data['total_paginas']; ?></strong>
            </div>
            <nav aria-label="Paginación de ventas">
                <ul class="pagination pagination-sm mb-0">
                    <?php
                    // Construir query string de filtros
                    $queryParams = $_GET;
                    
                    // Botón Anterior
                    if($data['pagina_actual'] > 1):
                        $queryParams['page'] = $data['pagina_actual'] - 1;
                        $prevUrl = BASE_URL . 'venta/index?' . http_build_query($queryParams);
                    ?>
                        <li class="page-item"><a class="page-link" href="<?php echo $prevUrl; ?>">&laquo; Anterior</a></li>
                    <?php else: ?>
                        <li class="page-item disabled"><span class="page-link">&laquo; Anterior</span></li>
                    <?php endif; ?>

                    <?php
                    // Rango de páginas (máximo 5 visibles)
                    $inicio = max(1, $data['pagina_actual'] - 2);
                    $fin = min($data['total_paginas'], $data['pagina_actual'] + 2);
                    for($i = $inicio; $i <= $fin; $i++):
                        $queryParams['page'] = $i;
                        $pageUrl = BASE_URL . 'venta/index?' . http_build_query($queryParams);
                    ?>
                        <li class="page-item <?php echo $i == $data['pagina_actual'] ? 'active' : ''; ?>">
                            <a class="page-link" href="<?php echo $pageUrl; ?>"><?php echo $i; ?></a>
                        </li>
                    <?php endfor; ?>

                    <?php
                    // Botón Siguiente
                    if($data['pagina_actual'] < $data['total_paginas']):
                        $queryParams['page'] = $data['pagina_actual'] + 1;
                        $nextUrl = BASE_URL . 'venta/index?' . http_build_query($queryParams);
                    ?>
                        <li class="page-item"><a class="page-link" href="<?php echo $nextUrl; ?>">Siguiente &raquo;</a></li>
                    <?php else: ?>
                        <li class="page-item disabled"><span class="page-link">Siguiente &raquo;</span></li>
                    <?php endif; ?>
                </ul>
            </nav>
        </div>
        <?php endif; ?>
    </div>
</div>
