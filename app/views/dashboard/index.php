<?php
// Formateo de fecha en español
$diasSemana = ['Domingo', 'Lunes', 'Martes', 'Miércoles', 'Jueves', 'Viernes', 'Sábado'];
$meses = ['', 'Enero', 'Febrero', 'Marzo', 'Abril', 'Mayo', 'Junio', 'Julio', 'Agosto', 'Septiembre', 'Octubre', 'Noviembre', 'Diciembre'];
$fechaHoy = $diasSemana[(int)date('w')] . ', ' . date('j') . ' de ' . $meses[(int)date('n')] . ' de ' . date('Y');

// Cálculo de totales acumulados por período
$totalSemana = (float)($data['grafico']['total'] ?? 0);
if ($totalSemana == 0 && !empty($data['grafico']['data'])) {
    foreach ($data['grafico']['data'] as $val) {
        $totalSemana += (float)$val;
    }
}
$totalHoy = (float)($data['graficoHoy']['total'] ?? ($data['metricas']['ingresos_hoy'] ?? 0));
$totalMes = (float)($data['graficoMensual']['total'] ?? 0);
?>

<div class="page-content">
    
    <!-- ENCABEZADO Y ACCIONES RÁPIDAS -->
    <div class="d-flex flex-column flex-md-row justify-content-between align-items-start align-items-md-center gap-3 mb-4">
        <div>
            <div class="d-flex align-items-center gap-2">
                <h1 class="page-title mb-0">Hola, <?php echo isset($_SESSION['nombre']) ? htmlspecialchars(explode(' ', $_SESSION['nombre'])[0]) : 'Admin'; ?> 👋</h1>
                <span class="badge rounded-pill bg-success-subtle text-success border border-success-subtle px-2.5 py-1" style="font-size: 11px; font-weight: 700;">
                    <span class="d-inline-block rounded-circle bg-success me-1" style="width: 6px; height: 6px;"></span> En Vivo
                </span>
            </div>
            <div class="page-subtitle text-muted mt-1" style="font-size: 13px;">
                <i class="bi bi-calendar3 me-1"></i> <?php echo $fechaHoy; ?> &bull; Panel de control ejecutivo
            </div>
        </div>

        <!-- Botones de Acción Inmediata (Quick Actions) -->
        <div class="d-flex flex-wrap align-items-center gap-2">
            <a href="<?php echo BASE_URL; ?>venta/pos" class="ceng-pos-btn" title="Ir a Venta (Atajo F1)">
                <i class="bi bi-cart-fill"></i>
                <span>Vender</span>
                <span class="ceng-kbd-tag">F1</span>
            </a>
            <a href="<?php echo BASE_URL; ?>caja/cierre" class="btn btn-outline-secondary fw-semibold px-3 py-2 rounded-3 d-flex align-items-center gap-2 bg-white text-dark shadow-sm" style="border-color: #E2E8F0; font-size: 13px;">
                <i class="bi bi-cash-stack text-warning"></i>
                <span>Arqueo Caja</span>
            </a>
            <a href="<?php echo BASE_URL; ?>compra/index" class="btn btn-outline-secondary fw-semibold px-3 py-2 rounded-3 d-flex align-items-center gap-2 bg-white text-dark shadow-sm" style="border-color: #E2E8F0; font-size: 13px;">
                <i class="bi bi-bag-plus text-primary"></i>
                <span>Ingreso Stock</span>
            </a>
            <a href="<?php echo BASE_URL; ?>reporte/index" class="btn btn-outline-secondary fw-semibold px-3 py-2 rounded-3 d-flex align-items-center gap-2 bg-white text-dark shadow-sm" style="border-color: #E2E8F0; font-size: 13px;">
                <i class="bi bi-file-earmark-bar-graph text-info"></i>
                <span>Reportes</span>
            </a>
        </div>
    </div>

    <!-- 4 TARJETAS MÉTRICAS EJECUTIVAS -->
    <div class="row g-3 g-lg-4 mb-4">
        
        <!-- Tarjeta 1: Ingresos de Hoy -->
        <div class="col-12 col-sm-6 col-xl-3">
            <div class="ceng-stat-card border-top-emerald">
                <div class="d-flex justify-content-between align-items-start mb-2">
                    <span class="ceng-stat-label">Ingresos de Hoy</span>
                    <div class="ceng-stat-icon-wrap bg-emerald-light text-emerald">
                        <i class="bi bi-wallet2"></i>
                    </div>
                </div>
                <div class="ceng-stat-number text-emerald">
                    S/ <?php echo number_format($data['metricas']['ingresos_hoy'], 2); ?>
                </div>
                <div class="ceng-stat-footer mt-2">
                    <span class="badge bg-emerald-light text-emerald px-2 py-1 rounded-pill fw-semibold" style="font-size: 11px;">
                        <i class="bi bi-arrow-up-right"></i> Facturado hoy
                    </span>
                    <span class="text-muted ms-auto" style="font-size: 11px;">Completadas</span>
                </div>
            </div>
        </div>

        <!-- Tarjeta 2: Ventas y Tickets de Hoy -->
        <div class="col-12 col-sm-6 col-xl-3">
            <div class="ceng-stat-card border-top-amber">
                <div class="d-flex justify-content-between align-items-start mb-2">
                    <span class="ceng-stat-label">Tickets Cobrados</span>
                    <div class="ceng-stat-icon-wrap bg-amber-light text-amber">
                        <i class="bi bi-receipt-cutoff"></i>
                    </div>
                </div>
                <div class="ceng-stat-number text-slate-800">
                    <?php echo number_format($data['metricas']['ventas_hoy']); ?>
                    <span class="ceng-stat-unit">ventas</span>
                </div>
                <div class="ceng-stat-footer mt-2">
                    <span class="badge bg-amber-light text-amber px-2 py-1 rounded-pill fw-semibold" style="font-size: 11px;">
                        <i class="bi bi-clock-history"></i> Turno activo
                    </span>
                    <span class="text-muted ms-auto" style="font-size: 11px;">Mostrador</span>
                </div>
            </div>
        </div>

        <!-- Tarjeta 3: Stock y Alertas FEFO -->
        <div class="col-12 col-sm-6 col-xl-3">
            <div class="ceng-stat-card <?php echo ($data['metricas']['lotes_riesgo'] > 0 || $data['metricas']['productos_riesgo_stock'] > 0) ? 'border-top-danger' : 'border-top-blue'; ?>">
                <div class="d-flex justify-content-between align-items-start mb-2">
                    <span class="ceng-stat-label">Catálogo &amp; FEFO</span>
                    <div class="ceng-stat-icon-wrap <?php echo ($data['metricas']['lotes_riesgo'] > 0) ? 'bg-danger-light text-danger' : 'bg-blue-light text-blue'; ?>">
                        <i class="bi bi-boxes"></i>
                    </div>
                </div>
                <div class="ceng-stat-number text-slate-800">
                    <?php echo number_format($data['metricas']['productos_total']); ?>
                    <span class="ceng-stat-unit">ítems activos</span>
                </div>
                <div class="ceng-stat-footer d-flex flex-wrap gap-1 mt-2">
                    <?php if($data['metricas']['lotes_riesgo'] > 0): ?>
                    <a href="<?php echo BASE_URL; ?>inventario/lotes" class="ceng-interactive-chip chip-danger" title="Clic para revisar lotes próximos a vencer">
                        <i class="bi bi-exclamation-triangle-fill"></i> <?php echo $data['metricas']['lotes_riesgo']; ?> por vencer
                    </a>
                    <?php endif; ?>

                    <?php if($data['metricas']['productos_riesgo_stock'] > 0): ?>
                    <a href="<?php echo BASE_URL; ?>inventario/kardex" class="ceng-interactive-chip chip-warning" title="Clic para ver productos bajo stock mínimo">
                        <i class="bi bi-box-arrow-down"></i> <?php echo $data['metricas']['productos_riesgo_stock']; ?> bajo stock
                    </a>
                    <?php endif; ?>

                    <?php if($data['metricas']['lotes_riesgo'] == 0 && $data['metricas']['productos_riesgo_stock'] == 0): ?>
                    <span class="badge bg-emerald-light text-emerald px-2 py-1 rounded-pill fw-semibold" style="font-size: 11px;">
                        <i class="bi bi-check-circle-fill"></i> Inventario óptimo
                    </span>
                    <?php endif; ?>
                </div>
            </div>
        </div>

        <!-- Tarjeta 4: Directorio de Clientes -->
        <div class="col-12 col-sm-6 col-xl-3">
            <div class="ceng-stat-card border-top-purple">
                <div class="d-flex justify-content-between align-items-start mb-2">
                    <span class="ceng-stat-label">Clientes &amp; Club</span>
                    <div class="ceng-stat-icon-wrap bg-purple-light text-purple">
                        <i class="bi bi-people-fill"></i>
                    </div>
                </div>
                <div class="ceng-stat-number text-slate-800">
                    <?php echo number_format($data['metricas']['clientes_total']); ?>
                    <span class="ceng-stat-unit">registrados</span>
                </div>
                <div class="ceng-stat-footer mt-2">
                    <a href="<?php echo BASE_URL; ?>puntos/index" class="text-decoration-none d-flex align-items-center gap-1 text-purple fw-semibold" style="font-size: 11px;">
                        <i class="bi bi-star-fill text-warning"></i> Club de Puntos y Fidelización &rarr;
                    </a>
                </div>
            </div>
        </div>

    </div>

    <!-- ZONA DE ANÁLISIS Y GRÁFICOS: FILA 1 -->
    <div class="row g-3 g-lg-4 mb-4">
        
        <!-- Gráfico 1: Evolución de Ingresos con Filtro de Período Google-style -->
        <div class="col-12 col-lg-8">
            <div class="card border-0 rounded-4 shadow-sm p-4 h-100 bg-white">
                <div class="d-flex flex-wrap justify-content-between align-items-center gap-3 mb-4 pb-3 border-bottom">
                    <div>
                        <h5 class="fw-bold text-slate-800 mb-1" style="font-size: 16px;">Evolución de Ingresos</h5>
                        <p class="text-muted mb-0" id="chartPeriodSubtitle" style="font-size: 12px;">Comportamiento de ventas brutas de los últimos 7 días</p>
                    </div>
                    
                    <div class="d-flex align-items-center gap-3">
                        <!-- Menú Desplegable Estilo Google (Selector de Rango) -->
                        <div class="dropdown">
                            <button class="btn btn-sm dropdown-toggle d-flex align-items-center gap-2 bg-white text-slate-700 shadow-sm border px-3 py-1.5 rounded-3" 
                                    type="button" 
                                    id="periodDropdownBtn" 
                                    data-bs-toggle="dropdown" 
                                    aria-expanded="false" 
                                    style="font-size: 12px; font-weight: 600; border-color: #E2E8F0 !important;">
                                <i class="bi bi-calendar-range text-emerald"></i>
                                <span id="periodDropdownLabel">Últimos 7 días</span>
                            </button>
                            <ul class="dropdown-menu dropdown-menu-end shadow-sm border-0 mt-1 py-1" style="min-width: 180px; border-radius: 12px; font-size: 12px; box-shadow: 0 10px 25px rgba(0,0,0,0.1) !important;">
                                <li>
                                    <a class="dropdown-item py-2 d-flex align-items-center justify-content-between text-dark" href="javascript:void(0)" onclick="switchChartPeriod('today', 'Último día', 'Comportamiento de ventas brutas de hoy por franja horaria')">
                                        <span><i class="bi bi-clock me-2 text-warning"></i> Último día (Hoy)</span>
                                        <i class="bi bi-check2 text-success fw-bold check-period d-none" id="check-today"></i>
                                    </a>
                                </li>
                                <li>
                                    <a class="dropdown-item py-2 d-flex align-items-center justify-content-between active-period text-dark fw-bold" href="javascript:void(0)" onclick="switchChartPeriod('week', 'Últimos 7 días', 'Comportamiento de ventas brutas de los últimos 7 días')">
                                        <span><i class="bi bi-calendar2-week me-2 text-success"></i> Últimos 7 días</span>
                                        <i class="bi bi-check2 text-success fw-bold check-period" id="check-week"></i>
                                    </a>
                                </li>
                                <li>
                                    <a class="dropdown-item py-2 d-flex align-items-center justify-content-between text-dark" href="javascript:void(0)" onclick="switchChartPeriod('month', 'Últimos 30 días', 'Comportamiento de ventas brutas de los últimos 30 días')">
                                        <span><i class="bi bi-calendar3 me-2 text-primary"></i> Últimos 30 días</span>
                                        <i class="bi bi-check2 text-success fw-bold check-period d-none" id="check-month"></i>
                                    </a>
                                </li>
                            </ul>
                        </div>

                        <!-- Total Acumulado del Período -->
                        <div class="text-end ps-3 border-start">
                            <span class="text-muted d-block" id="chartTotalLabel" style="font-size: 10px; font-weight: 700; text-transform: uppercase; letter-spacing: 0.5px;">Total 7 días</span>
                            <span class="fw-bold text-emerald" id="chartTotalValue" style="font-size: 16px;">S/ <?php echo number_format($totalSemana, 2); ?></span>
                        </div>
                    </div>
                </div>
                <div style="height: 270px; position: relative; width: 100%;">
                    <canvas id="barChart"></canvas>
                </div>
            </div>
        </div>

        <!-- Gráfico 2: Distribución por Medio de Pago -->
        <div class="col-12 col-lg-4">
            <div class="card border-0 rounded-4 shadow-sm p-4 h-100 bg-white d-flex flex-column">
                <div class="mb-3 pb-2 border-bottom">
                    <h5 class="fw-bold text-slate-800 mb-1" style="font-size: 16px;">Medios de Pago</h5>
                    <p class="text-muted mb-0" style="font-size: 12px;">Canal de recaudación de las ventas</p>
                </div>
                
                <div style="height: 190px; position: relative; width: 100%;" class="my-auto">
                    <canvas id="pieChart"></canvas>
                </div>
                
                <div class="mt-3 pt-3 border-top">
                    <?php 
                        $total_pagos = 0;
                        foreach($data['pagos'] as $p) $total_pagos += (float)$p['value'];
                        if($total_pagos == 0) $total_pagos = 1;
                        $colorPalette = ['#047B07', '#02C39A', '#3B82F6', '#F59E0B', '#EF4444'];
                    ?>
                    <div class="d-flex flex-column gap-2">
                        <?php foreach($data['pagos'] as $i => $p): 
                            $pct = round(((float)$p['value'] / $total_pagos) * 100);
                        ?>
                            <div class="d-flex justify-content-between align-items-center" style="font-size: 12px;">
                                <span class="text-slate-700 fw-medium d-flex align-items-center gap-1.5">
                                    <span class="d-inline-block rounded-circle" style="width: 8px; height: 8px; background-color: <?php echo $colorPalette[$i % count($colorPalette)]; ?>;"></span>
                                    <?php echo htmlspecialchars($p['label']); ?>
                                </span>
                                <div class="d-flex align-items-center gap-2">
                                    <span class="text-muted" style="font-size: 11px;"><?php echo $pct; ?>%</span>
                                    <span class="fw-bold text-slate-800">S/ <?php echo number_format($p['value'], 2); ?></span>
                                </div>
                            </div>
                        <?php endforeach; ?>
                        <?php if(empty($data['pagos'])): ?>
                            <div class="text-center text-muted py-2" style="font-size: 12px;">Sin ventas registradas hoy</div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>

    </div>

    <!-- ZONA DE ANÁLISIS Y GRÁFICOS: FILA 2 -->
    <div class="row g-3 g-lg-4">
        
        <!-- Gráfico 3: Top 5 Productos Más Vendidos -->
        <div class="col-12 col-lg-6">
            <div class="card border-0 rounded-4 shadow-sm p-4 h-100 bg-white">
                <div class="d-flex justify-content-between align-items-center mb-4 pb-2 border-bottom">
                    <div>
                        <h5 class="fw-bold text-slate-800 mb-1" style="font-size: 16px;">Top 5: Productos de Mayor Rotación</h5>
                        <p class="text-muted mb-0" style="font-size: 12px;">Medicamentos e insumos con mayor demanda en unidades</p>
                    </div>
                    <span class="badge bg-light text-dark border px-2 py-1 rounded-pill" style="font-size: 11px;">Unidades</span>
                </div>
                <div style="height: 250px; position: relative; width: 100%;">
                    <canvas id="topProductosChart"></canvas>
                </div>
            </div>
        </div>

        <!-- Gráfico 4: Top 5 Categorías con Mayores Ingresos -->
        <div class="col-12 col-lg-6">
            <div class="card border-0 rounded-4 shadow-sm p-4 h-100 bg-white">
                <div class="d-flex justify-content-between align-items-center mb-4 pb-2 border-bottom">
                    <div>
                        <h5 class="fw-bold text-slate-800 mb-1" style="font-size: 16px;">Top 5: Categorías más Rentables</h5>
                        <p class="text-muted mb-0" style="font-size: 12px;">Ingresos generados por línea farmacéutica y perfumería</p>
                    </div>
                    <span class="badge bg-light text-dark border px-2 py-1 rounded-pill" style="font-size: 11px;">Soles (S/)</span>
                </div>
                <div style="height: 250px; position: relative; width: 100%;">
                    <canvas id="topCategoriasChart"></canvas>
                </div>
            </div>
        </div>

    </div>

</div>

<!-- ESTILOS EXCLUSIVOS DEL DASHBOARD EJECUTIVO -->
<style>
/* Tarjetas de Métricas Ejecutivas */
.ceng-stat-card {
    background: #ffffff;
    border-radius: 16px;
    padding: 20px 22px;
    border: 1px solid #E8DFD8;
    box-shadow: 0 2px 12px rgba(26, 34, 56, 0.04);
    transition: transform 0.2s ease, box-shadow 0.2s ease;
    height: 100%;
    display: flex;
    flex-direction: column;
    justify-content: space-between;
}
.ceng-stat-card:hover {
    transform: translateY(-3px);
    box-shadow: 0 10px 24px rgba(4, 123, 7, 0.09);
}

/* Bordes Superiores Distintivos */
.border-top-emerald { border-top: 3.5px solid #047B07 !important; }
.border-top-amber { border-top: 3.5px solid #F59E0B !important; }
.border-top-blue { border-top: 3.5px solid #3B82F6 !important; }
.border-top-danger { border-top: 3.5px solid #EF4444 !important; }
.border-top-purple { border-top: 3.5px solid #8B5CF6 !important; }

/* Tipografía y Números */
.ceng-stat-label {
    font-size: 12px;
    font-weight: 700;
    text-transform: uppercase;
    letter-spacing: 0.6px;
    color: #64748b;
}
.ceng-stat-number {
    font-size: 28px;
    font-weight: 800;
    line-height: 1.15;
    letter-spacing: -0.8px;
    margin: 4px 0;
}
.ceng-stat-unit {
    font-size: 13px;
    font-weight: 600;
    color: #94a3b8;
    letter-spacing: 0;
}

/* Envoltorios de Íconos */
.ceng-stat-icon-wrap {
    width: 40px;
    height: 40px;
    border-radius: 10px;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 19px;
}

/* Paletas Semánticas Suaves */
.bg-emerald-light { background-color: rgba(4, 123, 7, 0.1); }
.text-emerald { color: #047B07 !important; }

.bg-amber-light { background-color: rgba(245, 158, 11, 0.12); }
.text-amber { color: #D97706 !important; }

.bg-blue-light { background-color: rgba(59, 130, 246, 0.1); }
.text-blue { color: #2563EB !important; }

.bg-danger-light { background-color: rgba(239, 68, 68, 0.1); }
.text-danger { color: #DC2626 !important; }

.bg-purple-light { background-color: rgba(139, 92, 246, 0.1); }
.text-purple { color: #7C3AED !important; }

.text-slate-800 { color: #1E293B !important; }
.text-slate-700 { color: #334155 !important; }

/* Chips Interactivos para Lotes y Stock Crítico */
.ceng-interactive-chip {
    display: inline-flex;
    align-items: center;
    gap: 5px;
    padding: 3px 10px;
    border-radius: 20px;
    font-size: 11px;
    font-weight: 700;
    text-decoration: none;
    transition: all 0.15s ease;
}
.ceng-interactive-chip.chip-danger {
    background-color: #FEF2F2;
    color: #DC2626;
    border: 1px solid #FECACA;
}
.ceng-interactive-chip.chip-danger:hover {
    background-color: #DC2626;
    color: #ffffff;
    transform: scale(1.03);
}

.ceng-interactive-chip.chip-warning {
    background-color: #FFFBEB;
    color: #D97706;
    border: 1px solid #FDE68A;
}
.ceng-interactive-chip.chip-warning:hover {
    background-color: #D97706;
    color: #ffffff;
    transform: scale(1.03);
}
</style>

<!-- LÓGICA JAVASCRIPT DE CHARTJS -->
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
document.addEventListener("DOMContentLoaded", function() {
    // Configuración base estética para ChartJS
    Chart.defaults.font.family = "'Inter', sans-serif";
    // 1. Gráfico de Barras - Ingresos con Selector de Período Google-Style
    let incomeBarChart = null;

    const periodsData = {
        today: {
            labels: <?php echo json_encode($data['graficoHoy']['labels'] ?? []); ?>,
            data: <?php echo json_encode($data['graficoHoy']['data'] ?? []); ?>,
            totalLabel: 'Total Hoy',
            totalValue: 'S/ <?php echo number_format($totalHoy, 2); ?>'
        },
        week: {
            labels: <?php echo json_encode($data['grafico']['labels'] ?? []); ?>,
            data: <?php echo json_encode($data['grafico']['data'] ?? []); ?>,
            totalLabel: 'Total 7 días',
            totalValue: 'S/ <?php echo number_format($totalSemana, 2); ?>'
        },
        month: {
            labels: <?php echo json_encode($data['graficoMensual']['labels'] ?? []); ?>,
            data: <?php echo json_encode($data['graficoMensual']['data'] ?? []); ?>,
            totalLabel: 'Total 30 días',
            totalValue: 'S/ <?php echo number_format($totalMes, 2); ?>'
        }
    };

    window.switchChartPeriod = function(type, label, subtitle) {
        if (!incomeBarChart || !periodsData[type]) return;

        // Actualizar textos en el encabezado
        const labelEl = document.getElementById('periodDropdownLabel');
        const subEl = document.getElementById('chartPeriodSubtitle');
        const totLbl = document.getElementById('chartTotalLabel');
        const totVal = document.getElementById('chartTotalValue');

        if (labelEl) labelEl.innerText = label;
        if (subEl) subEl.innerText = subtitle;
        if (totLbl) totLbl.innerText = periodsData[type].totalLabel;
        if (totVal) totVal.innerText = periodsData[type].totalValue;

        // Alternar marcas de check en el menú
        document.querySelectorAll('.check-period').forEach(el => el.classList.add('d-none'));
        const activeCheck = document.getElementById('check-' + type);
        if (activeCheck) activeCheck.classList.remove('d-none');

        // Actualizar datos del gráfico y animar
        incomeBarChart.data.labels = periodsData[type].labels;
        incomeBarChart.data.datasets[0].data = periodsData[type].data;
        incomeBarChart.data.datasets[0].barPercentage = type === 'month' ? 0.75 : 0.45;
        incomeBarChart.update();
    };

    const ctxBar = document.getElementById('barChart');
    if (ctxBar) {
        const ctxBar2d = ctxBar.getContext('2d');
        let gradientBar = ctxBar2d.createLinearGradient(0, 0, 0, 300);
        gradientBar.addColorStop(0, '#047B07');
        gradientBar.addColorStop(1, '#10B981');

        incomeBarChart = new Chart(ctxBar2d, {
            type: 'bar',
            data: {
                labels: periodsData.week.labels,
                datasets: [{
                    label: 'Ingresos',
                    data: periodsData.week.data,
                    backgroundColor: gradientBar,
                    borderRadius: 6,
                    borderSkipped: false,
                    barPercentage: 0.45
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: { display: false },
                    tooltip: {
                        backgroundColor: '#0E172A',
                        padding: 12,
                        cornerRadius: 8,
                        titleFont: { size: 13, weight: '600' },
                        bodyFont: { size: 14, weight: 'bold' },
                        callbacks: {
                            label: function(context) {
                                return ' S/ ' + Number(context.parsed.y).toLocaleString('es-PE', {minimumFractionDigits: 2, maximumFractionDigits: 2});
                            }
                        }
                    }
                },
                scales: {
                    y: {
                        beginAtZero: true,
                        grid: { color: 'rgba(226, 232, 240, 0.7)', drawBorder: false },
                        border: { display: false },
                        ticks: {
                            callback: function(value) { return 'S/ ' + value; },
                            padding: 8
                        }
                    },
                    x: {
                        grid: { display: false, drawBorder: false },
                        border: { display: false },
                        ticks: {
                            maxRotation: 0,
                            autoSkip: true,
                            maxTicksLimit: 10,
                            font: { size: 11, weight: '500' }
                        }
                    }
                }
            }
        });
    }

    // 2. Gráfico Doughnut - Medios de Pago
    <?php
        $pagosLabels = [];
        $pagosData = [];
        foreach($data['pagos'] as $p) {
            $pagosLabels[] = $p['label'];
            $pagosData[] = (float)$p['value'];
        }
    ?>
    let pieLabels = <?php echo json_encode($pagosLabels); ?>;
    let pieData = <?php echo json_encode($pagosData); ?>;

    if (pieData.length === 0 || pieData.every(v => v === 0)) {
        pieData = [1];
        pieLabels = ['Sin Movimientos'];
    }

    const ctxPie = document.getElementById('pieChart');
    if (ctxPie) {
        new Chart(ctxPie.getContext('2d'), {
            type: 'doughnut',
            data: {
                labels: pieLabels,
                datasets: [{
                    data: pieData,
                    backgroundColor: ['#047B07', '#02C39A', '#3B82F6', '#F59E0B', '#EF4444'],
                    borderWidth: 3,
                    borderColor: '#ffffff',
                    hoverOffset: 6
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                cutout: '72%',
                plugins: {
                    legend: { display: false },
                    tooltip: {
                        backgroundColor: '#0E172A',
                        padding: 10,
                        cornerRadius: 8,
                        callbacks: {
                            label: function(context) {
                                return ' S/ ' + Number(context.parsed).toLocaleString('es-PE', {minimumFractionDigits: 2});
                            }
                        }
                    }
                }
            }
        });
    }

    // 3. Gráfico de Barras Horizontales - Top Productos
    <?php
        $prodLabels = [];
        $prodData = [];
        if (isset($data['topProductos'])) {
            foreach($data['topProductos'] as $p) {
                $lbl = strlen($p['label']) > 24 ? substr($p['label'], 0, 21) . '...' : $p['label'];
                $prodLabels[] = $lbl;
                $prodData[] = (int)$p['value'];
            }
        }
    ?>
    const topProdLabels = <?php echo json_encode($prodLabels); ?>;
    const topProdData = <?php echo json_encode($prodData); ?>;

    const ctxTopProd = document.getElementById('topProductosChart');
    if (ctxTopProd) {
        const ctxP2d = ctxTopProd.getContext('2d');
        let gradProd = ctxP2d.createLinearGradient(0, 0, 350, 0);
        gradProd.addColorStop(0, '#047B07');
        gradProd.addColorStop(1, '#34D399');

        new Chart(ctxP2d, {
            type: 'bar',
            data: {
                labels: topProdLabels,
                datasets: [{
                    label: 'Unidades Vendidas',
                    data: topProdData,
                    backgroundColor: gradProd,
                    borderRadius: 5,
                    barPercentage: 0.55
                }]
            },
            options: {
                indexAxis: 'y',
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: { display: false },
                    tooltip: {
                        backgroundColor: '#0E172A',
                        padding: 10,
                        cornerRadius: 8
                    }
                },
                scales: {
                    x: {
                        beginAtZero: true,
                        grid: { color: 'rgba(226, 232, 240, 0.6)', drawBorder: false },
                        border: { display: false }
                    },
                    y: {
                        grid: { display: false, drawBorder: false },
                        border: { display: false },
                        ticks: { font: { size: 11, weight: '500' } }
                    }
                }
            }
        });
    }

    // 4. Gráfico Polar Area - Top Categorías
    <?php
        $catLabels = [];
        $catData = [];
        if (isset($data['topCategorias'])) {
            foreach($data['topCategorias'] as $c) {
                $catLabels[] = $c['label'];
                $catData[] = (float)$c['value'];
            }
        }
    ?>
    const topCatLabels = <?php echo json_encode($catLabels); ?>;
    const topCatData = <?php echo json_encode($catData); ?>;

    const ctxTopCat = document.getElementById('topCategoriasChart');
    if (ctxTopCat) {
        new Chart(ctxTopCat.getContext('2d'), {
            type: 'polarArea',
            data: {
                labels: topCatLabels,
                datasets: [{
                    data: topCatData,
                    backgroundColor: [
                        'rgba(4, 123, 7, 0.75)', 
                        'rgba(59, 130, 246, 0.75)', 
                        'rgba(245, 158, 11, 0.75)', 
                        'rgba(139, 92, 246, 0.75)', 
                        'rgba(239, 68, 68, 0.75)'
                    ],
                    borderColor: '#ffffff',
                    borderWidth: 2
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: { 
                        position: 'right',
                        labels: { boxWidth: 10, padding: 12, font: { size: 11, family: "'Inter', sans-serif" } }
                    },
                    tooltip: {
                        backgroundColor: '#0E172A',
                        cornerRadius: 8,
                        callbacks: {
                            label: function(context) {
                                return ' S/ ' + Number(context.parsed.r).toLocaleString('es-PE', {minimumFractionDigits: 2});
                            }
                        }
                    }
                },
                scales: {
                    r: {
                        ticks: { display: false },
                        grid: { color: 'rgba(226, 232, 240, 0.8)' }
                    }
                }
            }
        });
    }
});
</script>
