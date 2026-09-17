<div class="page-content">
    <?php if (isset($_SESSION['error'])): ?>
        <div class="alert alert-danger mb-4"><i class="bi bi-exclamation-triangle-fill"></i> <?php echo $_SESSION['error']; unset($_SESSION['error']); ?></div>
    <?php endif; ?>
    <?php if (isset($_SESSION['mensaje'])): ?>
        <div class="alert alert-success mb-4"><i class="bi bi-check-circle-fill"></i> <?php echo $_SESSION['mensaje']; unset($_SESSION['mensaje']); ?></div>
    <?php endif; ?>

    <?php if (isset($_SESSION['import_stats'])): 
        $st = $_SESSION['import_stats'];
        unset($_SESSION['import_stats']);
    ?>
        <div class="card p-3 mb-4 border-0 shadow-sm" style="background: linear-gradient(135deg, rgba(16, 185, 129, 0.1), rgba(59, 130, 246, 0.05)); border: 1px solid rgba(16, 185, 129, 0.3) !important; border-radius: 12px;">
            <div class="d-flex align-items-center justify-content-between mb-2">
                <div class="d-flex align-items-center gap-2">
                    <i class="bi bi-file-earmark-check-fill text-success fs-4"></i>
                    <h6 class="mb-0 fw-bold text-success">Resumen de Carga Masiva de Catálogo</h6>
                </div>
                <span class="badge bg-success px-2 py-1">Éxito</span>
            </div>
            <div class="row g-2 text-center mt-1">
                <div class="col-4 col-md-2">
                    <div class="p-2 bg-white rounded shadow-sm border">
                        <small class="text-muted d-block" style="font-size:11px;">Filas Leídas</small>
                        <strong class="fs-6 text-dark"><?php echo number_format($st['total_filas'] ?? 0); ?></strong>
                    </div>
                </div>
                <div class="col-4 col-md-2">
                    <div class="p-2 bg-white rounded shadow-sm border border-success">
                        <small class="text-muted d-block" style="font-size:11px;">Nuevos</small>
                        <strong class="fs-6 text-success">+<?php echo number_format($st['creados'] ?? 0); ?></strong>
                    </div>
                </div>
                <div class="col-4 col-md-2">
                    <div class="p-2 bg-white rounded shadow-sm border border-primary">
                        <small class="text-muted d-block" style="font-size:11px;">Actualizados</small>
                        <strong class="fs-6 text-primary"><?php echo number_format($st['actualizados'] ?? 0); ?></strong>
                    </div>
                </div>
                <div class="col-4 col-md-2">
                    <div class="p-2 bg-white rounded shadow-sm border border-info">
                        <small class="text-muted d-block" style="font-size:11px;">Lotes Creados</small>
                        <strong class="fs-6 text-info"><?php echo number_format($st['lotes_creados'] ?? 0); ?></strong>
                    </div>
                </div>
                <div class="col-4 col-md-2">
                    <div class="p-2 bg-white rounded shadow-sm border">
                        <small class="text-muted d-block" style="font-size:11px;">Categorías</small>
                        <strong class="fs-6 text-dark">+<?php echo number_format($st['categorias_creadas'] ?? 0); ?></strong>
                    </div>
                </div>
                <div class="col-4 col-md-2">
                    <div class="p-2 bg-white rounded shadow-sm border">
                        <small class="text-muted d-block" style="font-size:11px;">Laboratorios</small>
                        <strong class="fs-6 text-dark">+<?php echo number_format($st['laboratorios_creados'] ?? 0); ?></strong>
                    </div>
                </div>
            </div>
        </div>
    <?php endif; ?>

    <div class="d-flex flex-column flex-md-row justify-content-between align-items-start align-items-md-center gap-3 mb-4">
        <div>
            <h1 class="page-title mb-1">Catálogo de Productos</h1>
            <div class="page-subtitle">Gestiona medicamentos, precios mayoristas, stock e insumos de la botica.</div>
        </div>
        <div class="d-flex align-items-center gap-2">
            <button type="button" class="btn btn-outline-success fw-bold d-flex align-items-center gap-2 px-3 py-2 shadow-sm" data-bs-toggle="modal" data-bs-target="#modalImportarExcel" style="border-radius: 10px; font-size: 13px; background: rgba(16, 185, 129, 0.1); border-color: #10B981; color: #10B981;">
                <i class="bi bi-file-earmark-excel-fill" style="font-size: 16px;"></i> Importar Excel (.xlsx)
            </button>
            <a href="<?php echo BASE_URL; ?>producto/create" class="btn-primary-custom" style="width: auto; padding: 10px 20px; text-decoration: none; display: inline-block;">
                <i class="bi bi-plus-lg"></i> Nuevo Producto
            </a>
        </div>
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

<!-- ======================================================= -->
<!-- MODAL: IMPORTACIÓN MASIVA DE CATÁLOGO DESDE EXCEL (.XLSX) -->
<!-- ======================================================= -->
<div class="modal fade" id="modalImportarExcel" tabindex="-1" aria-labelledby="modalImportarExcelLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content border-0 shadow-lg" style="border-radius: 16px; overflow: hidden;">
            
            <div class="modal-header bg-success text-white py-3 px-4">
                <div class="d-flex align-items-center gap-2">
                    <i class="bi bi-file-earmark-excel-fill fs-4"></i>
                    <div>
                        <h5 class="modal-title fw-bold mb-0" id="modalImportarExcelLabel">Importar Catálogo desde Excel (.xlsx)</h5>
                        <small class="opacity-75" style="font-size: 11px;">Carga masiva rápida y segura para miles de medicamentos y productos</small>
                    </div>
                </div>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Cerrar"></button>
            </div>

            <form action="<?php echo BASE_URL; ?>producto/importarExcel" method="POST" enctype="multipart/form-data" id="formImportarExcel" onsubmit="mostrarCargandoImportacion()">
                <?php echo Controller::csrfField(); ?>
                
                <div class="modal-body p-4">
                    
                    <!-- Guía de Formato Aceptado -->
                    <div class="alert alert-light border border-secondary border-opacity-25 rounded-3 p-3 mb-3" style="font-size: 12px; background: rgba(0,0,0,0.02);">
                        <div class="d-flex align-items-center gap-2 mb-2">
                            <i class="bi bi-info-circle-fill text-primary"></i>
                            <strong class="text-dark">Estructura esperada en el archivo Excel:</strong>
                        </div>
                        <div class="text-muted mb-2">
                            El sistema reconoce automáticamente las columnas del catálogo de botica:
                        </div>
                        <div class="table-responsive">
                            <table class="table table-bordered table-sm mb-0 text-center" style="font-size: 11px;">
                                <thead class="table-light">
                                    <tr>
                                        <th>Codigo</th>
                                        <th>Categoria</th>
                                        <th>Laboratorio</th>
                                        <th>Nombre</th>
                                        <th>PVP1 (Venta)</th>
                                        <th>PVP2 (Mayor)</th>
                                        <th>Costo Unit.</th>
                                        <th>Stock</th>
                                        <th>Lote</th>
                                        <th>F. Vencim.</th>
                                        <th>Prin A.</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <tr class="text-secondary">
                                        <td>775605...</td>
                                        <td>MEDICINA</td>
                                        <td>IQ FARMA</td>
                                        <td>SIMILAC...</td>
                                        <td>165.50</td>
                                        <td>93.50</td>
                                        <td>159.00</td>
                                        <td>10</td>
                                        <td>15156</td>
                                        <td>2027-10-30</td>
                                        <td>530</td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>

                    <!-- Dropzone / Input de archivo -->
                    <div class="mb-4">
                        <label class="form-label fw-bold text-dark mb-2">
                            Seleccionar archivo Excel (.xlsx) <span class="text-danger">*</span>
                        </label>
                        <div class="p-4 text-center border-2 border-dashed rounded-3" style="border: 2px dashed #10B981; background: rgba(16, 185, 129, 0.03); cursor: pointer;" onclick="document.getElementById('inputArchivoExcel').click()">
                            <i class="bi bi-cloud-arrow-up-fill text-success" style="font-size: 42px;"></i>
                            <div class="mt-2 fw-semibold text-dark" id="txtNombreArchivo">
                                Haz clic aquí para elegir tu archivo o arrástralo
                            </div>
                            <small class="text-muted d-block mt-1">Formato admitido: <strong>.xlsx</strong> (hasta 20 MB)</small>
                            <input type="file" name="archivo_excel" id="inputArchivoExcel" accept=".xlsx,application/vnd.openxmlformats-officedocument.spreadsheetml.sheet" class="d-none" required onchange="actualizarNombreArchivo(this)">
                        </div>
                    </div>

                    <!-- Opciones de Importación -->
                    <div class="card p-3 border-0 bg-light rounded-3">
                        <span class="fw-bold text-dark mb-2" style="font-size: 13px;">Opciones de procesamiento:</span>
                        
                        <div class="form-check mb-2">
                            <input class="form-check-input" type="checkbox" name="actualizar_existentes" id="optActualizar" value="1" checked>
                            <label class="form-check-label text-dark" for="optActualizar" style="font-size: 13px;">
                                <strong>Actualizar productos existentes:</strong> Si el código de barras o nombre ya existe, actualiza sus precios y suma el stock.
                            </label>
                        </div>
                        
                        <div class="form-check mb-2">
                            <input class="form-check-input" type="checkbox" name="crear_categorias" id="optCat" value="1" checked>
                            <label class="form-check-label text-dark" for="optCat" style="font-size: 13px;">
                                <strong>Crear categorías automáticamente:</strong> Si la categoría del Excel no existe, se registrará en el sistema.
                            </label>
                        </div>

                        <div class="form-check">
                            <input class="form-check-input" type="checkbox" name="crear_laboratorios" id="optLab" value="1" checked>
                            <label class="form-check-label text-dark" for="optLab" style="font-size: 13px;">
                                <strong>Crear laboratorios automáticamente:</strong> Si el laboratorio no existe, se registrará en el sistema.
                            </label>
                        </div>
                    </div>

                    <!-- Alerta de Progreso (Oculta al inicio) -->
                    <div id="alertaCargando" class="alert alert-info mt-3 d-none align-items-center gap-3">
                        <div class="spinner-border text-primary" role="status" style="width: 2rem; height: 2rem;">
                            <span class="visually-hidden">Procesando...</span>
                        </div>
                        <div>
                            <strong>Procesando catálogo en la base de datos...</strong>
                            <div style="font-size: 12px;" class="text-muted">Leyendo registros, creando productos, lotes y actualizando Kardex. Por favor espere.</div>
                        </div>
                    </div>

                </div>

                <div class="modal-footer bg-light px-4 py-3 border-top-0">
                    <button type="button" class="btn btn-outline-secondary px-3" data-bs-dismiss="modal" id="btnCancelarImport">
                        Cancelar
                    </button>
                    <button type="submit" class="btn btn-success fw-bold px-4 d-flex align-items-center gap-2" id="btnEjecutarImport">
                        <i class="bi bi-box-arrow-in-down"></i> Iniciar Importación
                    </button>
                </div>
            </form>

        </div>
    </div>
</div>

<script>
function actualizarNombreArchivo(input) {
    if (input.files && input.files[0]) {
        const file = input.files[0];
        document.getElementById('txtNombreArchivo').innerHTML = 
            `<span class="text-success"><i class="bi bi-file-earmark-check"></i> <strong>${file.name}</strong> (${(file.size / 1024).toFixed(1)} KB)</span>`;
    }
}

function mostrarCargandoImportacion() {
    const alertDiv = document.getElementById('alertaCargando');
    const btnSubmit = document.getElementById('btnEjecutarImport');
    const btnCancel = document.getElementById('btnCancelarImport');
    
    alertDiv.classList.remove('d-none');
    alertDiv.classList.add('d-flex');
    btnSubmit.disabled = true;
    btnSubmit.innerHTML = '<span class="spinner-border spinner-border-sm me-1"></span> Importando...';
    btnCancel.disabled = true;
}
</script>
