<?php
// Pre-clasificación de datos para métricas rápidas
$hoy = new DateTime();
$countVencidos = 0;
$countCriticos30 = 0;
$countRiesgo90 = 0;

if (!empty($data['lotes'])) {
    foreach ($data['lotes'] as $l) {
        $fv = new DateTime($l['fecha_vencimiento']);
        if ($fv < $hoy) {
            $countVencidos++;
        } else {
            $diff = $hoy->diff($fv)->days;
            if ($diff <= 30) {
                $countCriticos30++;
            } else {
                $countRiesgo90++;
            }
        }
    }
}

$countQuiebreCritico = 0;
if (!empty($data['bajos'])) {
    foreach ($data['bajos'] as $b) {
        if ($b['stock'] <= 5) {
            $countQuiebreCritico++;
        }
    }
}
$countTotalBajos = count($data['bajos'] ?? []);
?>

<div class="page-content">
    <!-- Header -->
    <div class="d-flex flex-wrap justify-content-between align-items-center gap-3 mb-4">
        <div>
            <h2 class="page-title mb-1" style="font-weight: 800; color: var(--text-primary);">
                <i class="bi bi-shield-exclamation text-danger me-2"></i> Centro de Alertas Sanitarias
            </h2>
            <p class="page-subtitle mb-0" style="color: var(--text-secondary); font-size: 14px;">
                Supervisión sanitaria FEFO, control de caducidades reglamentarias DIGEMID y prevención de quiebres de stock.
            </p>
        </div>
        <div class="d-flex gap-2">
            <a href="<?php echo BASE_URL; ?>inventario/lotes" class="btn btn-outline-secondary btn-sm d-flex align-items-center gap-1" style="border-radius: 8px; font-weight: 600;">
                <i class="bi bi-box-seam"></i> Inventario de Lotes
            </a>
            <a href="<?php echo BASE_URL; ?>compra/create" class="btn btn-primary btn-sm d-flex align-items-center gap-1" style="border-radius: 8px; font-weight: 600; background: var(--accent-primary); border-color: var(--accent-primary);">
                <i class="bi bi-cart-plus"></i> Ordenar Compra
            </a>
        </div>
    </div>

    <!-- Tarjetas de Métricas Sanitarias (KPIs) -->
    <div class="row g-3 mb-4">
        <div class="col-6 col-md-3">
            <div class="card p-3 h-100 border-0 shadow-sm" style="background: var(--bg-card); border-left: 4px solid #ef4444 !important; border-radius: 10px;">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <span class="text-uppercase" style="font-size: 11px; font-weight: 700; color: #ef4444; letter-spacing: 0.5px;">Lotes Vencidos</span>
                        <h3 class="mb-0 mt-1 fw-bold" style="color: var(--text-primary);"><?php echo $countVencidos; ?></h3>
                    </div>
                    <div style="width: 42px; height: 42px; border-radius: 8px; background: rgba(239, 68, 68, 0.12); display: flex; align-items: center; justify-content: center;">
                        <i class="bi bi-x-octagon-fill text-danger" style="font-size: 20px;"></i>
                    </div>
                </div>
                <small class="mt-2 text-muted" style="font-size: 11px;">Retirar de exhibición inmediata</small>
            </div>
        </div>

        <div class="col-6 col-md-3">
            <div class="card p-3 h-100 border-0 shadow-sm" style="background: var(--bg-card); border-left: 4px solid #f97316 !important; border-radius: 10px;">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <span class="text-uppercase" style="font-size: 11px; font-weight: 700; color: #f97316; letter-spacing: 0.5px;">Vence &le; 30 días</span>
                        <h3 class="mb-0 mt-1 fw-bold" style="color: var(--text-primary);"><?php echo $countCriticos30; ?></h3>
                    </div>
                    <div style="width: 42px; height: 42px; border-radius: 8px; background: rgba(249, 115, 22, 0.12); display: flex; align-items: center; justify-content: center;">
                        <i class="bi bi-exclamation-triangle-fill" style="color: #f97316; font-size: 20px;"></i>
                    </div>
                </div>
                <small class="mt-2 text-muted" style="font-size: 11px;">Prioridad alta en POS (FEFO)</small>
            </div>
        </div>

        <div class="col-6 col-md-3">
            <div class="card p-3 h-100 border-0 shadow-sm" style="background: var(--bg-card); border-left: 4px solid #eab308 !important; border-radius: 10px;">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <span class="text-uppercase" style="font-size: 11px; font-weight: 700; color: #ca8a04; letter-spacing: 0.5px;">Vence &le; 90 días</span>
                        <h3 class="mb-0 mt-1 fw-bold" style="color: var(--text-primary);"><?php echo $countRiesgo90; ?></h3>
                    </div>
                    <div style="width: 42px; height: 42px; border-radius: 8px; background: rgba(234, 179, 8, 0.12); display: flex; align-items: center; justify-content: center;">
                        <i class="bi bi-clock-history text-warning" style="font-size: 20px;"></i>
                    </div>
                </div>
                <small class="mt-2 text-muted" style="font-size: 11px;">Monitoreo preventivo de rotación</small>
            </div>
        </div>

        <div class="col-6 col-md-3">
            <div class="card p-3 h-100 border-0 shadow-sm" style="background: var(--bg-card); border-left: 4px solid #8b5cf6 !important; border-radius: 10px;">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <span class="text-uppercase" style="font-size: 11px; font-weight: 700; color: #8b5cf6; letter-spacing: 0.5px;">Quiebres de Stock</span>
                        <h3 class="mb-0 mt-1 fw-bold" style="color: var(--text-primary);"><?php echo $countTotalBajos; ?></h3>
                    </div>
                    <div style="width: 42px; height: 42px; border-radius: 8px; background: rgba(139, 92, 246, 0.12); display: flex; align-items: center; justify-content: center;">
                        <i class="bi bi-box-arrow-down" style="color: #8b5cf6; font-size: 20px;"></i>
                    </div>
                </div>
                <small class="mt-2 text-muted" style="font-size: 11px;"><?php echo $countQuiebreCritico; ?> en nivel crítico (&le; 5 und)</small>
            </div>
        </div>
    </div>

    <!-- Barra de Búsqueda Rápida e Interactiva -->
    <div class="card border-0 shadow-sm p-3 mb-4" style="background: var(--bg-card); border-radius: 12px;">
        <div class="row g-2 align-items-center">
            <div class="col-md-6 col-lg-5">
                <div class="input-group">
                    <span class="input-group-text border-0" style="background: var(--bg-dark); color: var(--text-secondary);">
                        <i class="bi bi-search"></i>
                    </span>
                    <input type="text" id="filtroAlertas" class="form-control border-0" placeholder="Filtrar por producto o código de lote..." style="background: var(--bg-dark); color: var(--text-primary); font-size: 14px;" onkeyup="filtrarTablasAlertas()">
                </div>
            </div>
            <div class="col-md-6 col-lg-7 text-md-end">
                <div class="btn-group" role="group" aria-label="Filtro de visualización">
                    <button type="button" class="btn btn-sm btn-outline-secondary active" id="btnTabTodos" onclick="cambiarVista('todos')">Todos</button>
                    <button type="button" class="btn btn-sm btn-outline-secondary" id="btnTabVenc" onclick="cambiarVista('vencidos')">Solo Vencimientos</button>
                    <button type="button" class="btn btn-sm btn-outline-secondary" id="btnTabStock" onclick="cambiarVista('stock')">Solo Stock Bajo</button>
                </div>
            </div>
        </div>
    </div>

    <!-- Contenedores de Tablas -->
    <div class="row g-4">
        <!-- Columna 1: Lotes Sanitarios con Vencimiento -->
        <div class="col-12 col-xl-7" id="secVencimientos">
            <div class="card border-0 shadow-sm h-100" style="background: var(--bg-card); border-radius: 12px; overflow: hidden;">
                <div class="card-header bg-transparent border-bottom d-flex justify-content-between align-items-center py-3 px-4">
                    <div class="d-flex align-items-center gap-2">
                        <i class="bi bi-calendar-x-fill text-danger fs-5"></i>
                        <h5 class="mb-0 fw-bold" style="color: var(--text-primary); font-size: 16px;">Control Sanitario de Lotes</h5>
                    </div>
                    <span class="badge bg-danger-subtle text-danger border border-danger-subtle px-2 py-1" style="font-size: 12px;">
                        <?php echo count($data['lotes'] ?? []); ?> lotes evaluados
                    </span>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-hover align-middle mb-0" id="tablaLotes" style="color: var(--text-primary);">
                            <thead style="background: var(--bg-dark); font-size: 12px; text-transform: uppercase; color: var(--text-secondary); letter-spacing: 0.5px;">
                                <tr>
                                    <th class="ps-4">Producto</th>
                                    <th>Lote</th>
                                    <th>Fecha Vencimiento</th>
                                    <th>Estado / Plazo</th>
                                    <th class="text-end pe-4">Stock U.M.</th>
                                </tr>
                            </thead>
                            <tbody style="font-size: 13px;">
                                <?php if(empty($data['lotes'])): ?>
                                    <tr>
                                        <td colspan="5" class="text-center py-5 text-muted">
                                            <i class="bi bi-check-circle-fill text-success fs-1 d-block mb-2"></i>
                                            Excelente. No existen lotes vencidos ni próximos a vencer en los próximos 90 días.
                                        </td>
                                    </tr>
                                <?php else: ?>
                                    <?php 
                                    foreach($data['lotes'] as $l): 
                                        $fv = new DateTime($l['fecha_vencimiento']);
                                        $isVencido = $fv < $hoy;
                                        $diff = $hoy->diff($fv)->days;

                                        if ($isVencido) {
                                            $badgeClass = 'bg-danger text-white';
                                            $badgeText = 'VENCIDO';
                                            $rowStatus = 'status-vencido';
                                        } elseif ($diff <= 30) {
                                            $badgeClass = 'bg-warning text-dark';
                                            $badgeText = "En $diff días";
                                            $rowStatus = 'status-critico';
                                        } else {
                                            $badgeClass = 'bg-info-subtle text-info-emphasis border';
                                            $badgeText = "En $diff días";
                                            $rowStatus = 'status-riesgo';
                                        }
                                    ?>
                                    <tr class="fila-alerta <?php echo $rowStatus; ?>">
                                        <td class="ps-4">
                                            <span class="fw-bold d-block" style="color: var(--text-primary);"><?php echo htmlspecialchars($l['producto']); ?></span>
                                            <small style="color: var(--text-secondary); font-size: 11px;">Ref ID: #<?php echo $l['id_producto'] ?? '-'; ?></small>
                                        </td>
                                        <td>
                                            <span class="badge font-monospace" style="background: var(--bg-dark); color: var(--text-primary); border: 1px solid rgba(128,128,128,0.25); font-size: 12px;">
                                                <?php echo htmlspecialchars($l['lote']); ?>
                                            </span>
                                        </td>
                                        <td>
                                            <span class="fw-semibold" style="color: var(--text-primary);"><?php echo date('d/m/Y', strtotime($l['fecha_vencimiento'])); ?></span>
                                        </td>
                                        <td>
                                            <span class="badge rounded-pill <?php echo $badgeClass; ?> px-2 py-1" style="font-size: 11px; font-weight: 700;">
                                                <?php echo $badgeText; ?>
                                            </span>
                                        </td>
                                        <td class="text-end pe-4">
                                            <span class="fw-bold fs-6" style="color: var(--accent-primary);"><?php echo number_format($l['stock'], 0); ?></span>
                                        </td>
                                    </tr>
                                    <?php endforeach; ?>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>

        <!-- Columna 2: Quiebre de Stock -->
        <div class="col-12 col-xl-5" id="secStock">
            <div class="card border-0 shadow-sm h-100" style="background: var(--bg-card); border-radius: 12px; overflow: hidden;">
                <div class="card-header bg-transparent border-bottom d-flex justify-content-between align-items-center py-3 px-4">
                    <div class="d-flex align-items-center gap-2">
                        <i class="bi bi-box-arrow-down text-warning fs-5"></i>
                        <h5 class="mb-0 fw-bold" style="color: var(--text-primary); font-size: 16px;">Abastecimiento Crítico</h5>
                    </div>
                    <span class="badge bg-warning-subtle text-warning border border-warning-subtle px-2 py-1" style="font-size: 12px;">
                        <?php echo count($data['bajos'] ?? []); ?> productos
                    </span>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-hover align-middle mb-0" id="tablaStock" style="color: var(--text-primary);">
                            <thead style="background: var(--bg-dark); font-size: 12px; text-transform: uppercase; color: var(--text-secondary); letter-spacing: 0.5px;">
                                <tr>
                                    <th class="ps-4">Producto</th>
                                    <th>Unidad</th>
                                    <th class="text-end pe-4">Stock Restante</th>
                                </tr>
                            </thead>
                            <tbody style="font-size: 13px;">
                                <?php if(empty($data['bajos'])): ?>
                                    <tr>
                                        <td colspan="3" class="text-center py-5 text-muted">
                                            <i class="bi bi-check2-all text-success fs-1 d-block mb-2"></i>
                                            Inventario equilibrado. No hay productos con stock menor a 20 unidades.
                                        </td>
                                    </tr>
                                <?php else: ?>
                                    <?php foreach($data['bajos'] as $b): 
                                        $esCritico = $b['stock'] <= 5;
                                    ?>
                                    <tr class="fila-stock">
                                        <td class="ps-4">
                                            <span class="fw-bold d-block" style="color: var(--text-primary);"><?php echo htmlspecialchars($b['producto']); ?></span>
                                            <?php if($esCritico): ?>
                                                <span class="badge bg-danger-subtle text-danger px-1 py-0" style="font-size: 10px;">QUIEBRE INMINENTE</span>
                                            <?php else: ?>
                                                <span class="badge bg-secondary-subtle text-secondary px-1 py-0" style="font-size: 10px;">STOCK BAJO</span>
                                            <?php endif; ?>
                                        </td>
                                        <td>
                                            <span class="badge bg-dark-subtle text-secondary border px-2 py-1" style="font-size: 11px;">
                                                <?php echo htmlspecialchars($b['unidad_medida'] ?: 'Unidad'); ?>
                                            </span>
                                        </td>
                                        <td class="text-end pe-4">
                                            <span class="fw-bold fs-6 <?php echo $esCritico ? 'text-danger' : 'text-warning'; ?>">
                                                <?php echo number_format($b['stock'], 0); ?>
                                            </span>
                                        </td>
                                    </tr>
                                    <?php endforeach; ?>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
function filtrarTablasAlertas() {
    const query = document.getElementById('filtroAlertas').value.toLowerCase().trim();
    
    // Filtrar tabla lotes
    const filasLotes = document.querySelectorAll('#tablaLotes tbody tr.fila-alerta');
    filasLotes.forEach(row => {
        const text = row.innerText.toLowerCase();
        row.style.display = text.includes(query) ? '' : 'none';
    });

    // Filtrar tabla stock
    const filasStock = document.querySelectorAll('#tablaStock tbody tr.fila-stock');
    filasStock.forEach(row => {
        const text = row.innerText.toLowerCase();
        row.style.display = text.includes(query) ? '' : 'none';
    });
}

function cambiarVista(modo) {
    const secVenc = document.getElementById('secVencimientos');
    const secStock = document.getElementById('secStock');
    const btnTodos = document.getElementById('btnTabTodos');
    const btnVenc = document.getElementById('btnTabVenc');
    const btnStock = document.getElementById('btnTabStock');

    [btnTodos, btnVenc, btnStock].forEach(b => b.classList.remove('active'));

    if (modo === 'todos') {
        secVenc.style.display = '';
        secStock.style.display = '';
        secVenc.className = 'col-12 col-xl-7';
        secStock.className = 'col-12 col-xl-5';
        btnTodos.classList.add('active');
    } else if (modo === 'vencidos') {
        secVenc.style.display = '';
        secStock.style.display = 'none';
        secVenc.className = 'col-12';
        btnVenc.classList.add('active');
    } else if (modo === 'stock') {
        secVenc.style.display = 'none';
        secStock.style.display = '';
        secStock.className = 'col-12';
        btnStock.classList.add('active');
    }
}
</script>
