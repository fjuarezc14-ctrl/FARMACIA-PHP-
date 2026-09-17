<div class="page-content">
    <?php if (isset($_SESSION['error'])): ?>
        <div class="alert alert-danger mb-4"><i class="bi bi-exclamation-triangle-fill"></i> <?php echo $_SESSION['error']; unset($_SESSION['error']); ?></div>
    <?php endif; ?>
    <?php if (isset($_SESSION['mensaje'])): ?>
        <div class="alert alert-success mb-4"><i class="bi bi-check-circle-fill"></i> <?php echo $_SESSION['mensaje']; unset($_SESSION['mensaje']); ?></div>
    <?php endif; ?>

    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h1 class="page-title">Catálogo de Productos</h1>
            <div class="page-subtitle">Gestiona medicamentos, equipos e insumos de la botica.</div>
        </div>
        <a href="<?php echo BASE_URL; ?>producto/create" class="btn-primary-custom" style="width: auto; padding: 10px 20px; text-decoration: none; display: inline-block;">
            <i class="bi bi-plus-lg"></i> Nuevo Producto
        </a>
    </div>

    <div class="card-metric mb-3 p-3">
        <form method="GET" action="<?php echo BASE_URL; ?>producto/index" class="row g-2 align-items-end">
            <div class="col-md-4">
                <label class="form-label mb-1" style="font-size: 11px; font-weight: 700; color: var(--text-secondary);">Búsqueda Rápida:</label>
                <div class="search-box w-100">
                    <i class="bi bi-search"></i>
                    <input type="text" name="search" value="<?php echo htmlspecialchars($data['filtros']['search'] ?? ''); ?>" placeholder="Código de barras, comercial o genérico...">
                </div>
            </div>
            <div class="col-md-3 col-sm-6">
                <label class="form-label mb-1" style="font-size: 11px; font-weight: 700; color: var(--text-secondary);">Categoría:</label>
                <select name="id_categoria" class="form-select form-select-sm">
                    <option value="">-- Todas las Categorías --</option>
                    <?php if(!empty($data['categorias'])): foreach($data['categorias'] as $cat): ?>
                        <option value="<?php echo $cat['id']; ?>" <?php echo (isset($data['filtros']['id_categoria']) && $data['filtros']['id_categoria'] == $cat['id']) ? 'selected' : ''; ?>>
                            <?php echo htmlspecialchars($cat['nombre']); ?>
                        </option>
                    <?php endforeach; endif; ?>
                </select>
            </div>
            <div class="col-md-3 col-sm-6">
                <label class="form-label mb-1" style="font-size: 11px; font-weight: 700; color: var(--text-secondary);">Laboratorio:</label>
                <select name="id_laboratorio" class="form-select form-select-sm">
                    <option value="">-- Todos los Laboratorios --</option>
                    <?php if(!empty($data['laboratorios'])): foreach($data['laboratorios'] as $lab): ?>
                        <option value="<?php echo $lab['id']; ?>" <?php echo (isset($data['filtros']['id_laboratorio']) && $data['filtros']['id_laboratorio'] == $lab['id']) ? 'selected' : ''; ?>>
                            <?php echo htmlspecialchars($lab['nombre']); ?>
                        </option>
                    <?php endforeach; endif; ?>
                </select>
            </div>
            <div class="col-md-2 d-flex gap-2">
                <button type="submit" class="btn btn-sm btn-success fw-bold flex-grow-1" style="height: 38px;">
                    <i class="bi bi-search"></i> Buscar
                </button>
                <a href="<?php echo BASE_URL; ?>producto/index" class="btn btn-sm btn-outline-secondary" style="height: 38px; display: flex; align-items: center;" title="Limpiar Filtros">
                    <i class="bi bi-x-circle"></i>
                </a>
            </div>
        </form>
    </div>

    <div class="card-metric">
        <div class="table-responsive">
            <table class="table table-hover align-middle" id="productosTable" style="width: 100%; font-size: 14px;">
                <thead>
                    <tr>
                        <th width="120">Código</th>
                        <th>Producto</th>
                        <th>U.M.</th>
                        <th>Precio Venta</th>
                        <th>Stock Disp.</th>
                        <th width="160" class="text-end">Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if(empty($data['productos'])): ?>
                    <tr><td colspan="6" class="text-center text-muted">No hay productos registrados</td></tr>
                    <?php else: ?>
                    <?php foreach($data['productos'] as $prod): ?>
                    <tr>
                        <td style="color:#A0A0A0; font-family:monospace;"><?php echo htmlspecialchars($prod['codigo_barras'] ?? ''); ?></td>
                        <td>
                            <div style="font-weight:700; color:var(--text-primary);"><?php echo htmlspecialchars($prod['nombre_comercial'] ?? ''); ?></div>
                            <div style="font-size:12px; color:var(--text-secondary);">
                                <?php echo htmlspecialchars($prod['nombre_generico'] ?? ''); ?> 
                                <?php echo !empty($prod['concentracion']) ? ' - ' . htmlspecialchars($prod['concentracion']) : ''; ?>
                                <?php if(!empty($prod['registro_sanitario'])): ?>
                                    <span class="ms-2" style="font-size: 11px; background: rgba(0,0,0,0.1); padding: 2px 6px; border-radius: 4px; border: 1px solid var(--border-color); color:var(--text-secondary);">RS: <?php echo htmlspecialchars($prod['registro_sanitario'] ?? ''); ?></span>
                                <?php endif; ?>
                            </div>
                            <div class="mt-1">
                                <?php 
                                $cond = $prod['condicion_venta'] ?? 'Venta Libre';
                                if($cond === 'Receta Médica Retenida') {
                                    echo '<span class="badge bg-danger" style="font-size:10px; font-weight:700;"><i class="bi bi-file-earmark-medical"></i> Receta Retenida</span>';
                                } elseif($cond === 'Receta Médica Simple') {
                                    echo '<span class="badge bg-warning text-dark" style="font-size:10px; font-weight:700;"><i class="bi bi-file-earmark-text"></i> Receta Simple</span>';
                                } else {
                                    echo '<span class="badge bg-success" style="font-size:10px; font-weight:700;"><i class="bi bi-bookmark-fill"></i> Venta Libre</span>';
                                }
                                ?>
                            </div>
                        </td>
                        <td><?php echo htmlspecialchars($prod['unidad_medida'] ?? 'Caja'); ?></td>
                        <td style="font-weight:600; color:var(--accent-primary);">S/ <?php echo number_format($prod['precio_venta'], 2); ?></td>
                        <td>
                            <?php if($prod['stock_actual'] <= $prod['stock_minimo']): ?>
                                <span class="badge-status status-pending"><?php echo $prod['stock_actual']; ?> (Crítico)</span>
                            <?php else: ?>
                                <span class="badge-status status-delivered"><?php echo $prod['stock_actual']; ?> Disp.</span>
                            <?php endif; ?>
                        </td>
                        <td class="text-end">
                            <?php if(isset($prod['estado']) && $prod['estado'] == 0): ?>
                                <span class="badge bg-secondary me-1" style="font-size: 10px;">Inactivo</span>
                                <a href="<?php echo BASE_URL; ?>producto/edit/<?php echo $prod['id']; ?>" class="btn btn-sm" style="color: #00CFE8;" title="Editar">
                                     <i class="bi bi-pencil-square"></i>
                                </a>
                                <form action="<?php echo BASE_URL; ?>producto/toggle" method="POST" class="d-inline" onsubmit="return confirm('¿Seguro que deseas activar este producto para su venta?');">
                                    <?php echo Controller::csrfField(); ?>
                                    <input type="hidden" name="id" value="<?php echo $prod['id']; ?>">
                                    <button type="submit" class="btn btn-sm" style="color: var(--success);" title="Activar Producto">
                                        <i class="bi bi-check-circle-fill"></i>
                                    </button>
                                </form>
                            <?php else: ?>
                                <span class="badge bg-success me-1" style="font-size: 10px;">Activo</span>
                                <a href="<?php echo BASE_URL; ?>producto/edit/<?php echo $prod['id']; ?>" class="btn btn-sm" style="color: #00CFE8;" title="Editar">
                                     <i class="bi bi-pencil-square"></i>
                                </a>
                                <form action="<?php echo BASE_URL; ?>producto/toggle" method="POST" class="d-inline" onsubmit="return confirm('¿Seguro que deseas desactivar este producto? Ya no aparecerá en el POS.');">
                                    <?php echo Controller::csrfField(); ?>
                                    <input type="hidden" name="id" value="<?php echo $prod['id']; ?>">
                                    <button type="submit" class="btn btn-sm" style="color: var(--warning);" title="Desactivar Producto">
                                        <i class="bi bi-x-circle-fill"></i>
                                    </button>
                                </form>
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
                Mostrando página <strong><?php echo $data['pagina_actual'] ?? 1; ?></strong> de <strong><?php echo $data['total_paginas'] ?? 1; ?></strong> (Total: <strong><?php echo $data['total_registros'] ?? count($data['productos']); ?></strong> productos)
            </div>
            <?php if(($data['total_paginas'] ?? 1) > 1): ?>
            <nav aria-label="Paginación de productos">
                <ul class="pagination pagination-sm mb-0">
                    <?php
                    $queryParams = $_GET;
                    if(($data['pagina_actual'] ?? 1) > 1):
                        $queryParams['page'] = $data['pagina_actual'] - 1;
                        $prevUrl = BASE_URL . 'producto/index?' . http_build_query($queryParams);
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
                        $pageUrl = BASE_URL . 'producto/index?' . http_build_query($queryParams);
                    ?>
                        <li class="page-item <?php echo $i == ($data['pagina_actual'] ?? 1) ? 'active' : ''; ?>">
                            <a class="page-link" href="<?php echo $pageUrl; ?>"><?php echo $i; ?></a>
                        </li>
                    <?php endfor; ?>

                    <?php
                    if(($data['pagina_actual'] ?? 1) < ($data['total_paginas'] ?? 1)):
                        $queryParams['page'] = $data['pagina_actual'] + 1;
                        $nextUrl = BASE_URL . 'producto/index?' . http_build_query($queryParams);
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
