<div class="page-content">
    <div class="page-header">
        <h1 class="page-title">Hola, <?php echo isset($_SESSION['nombre']) ? explode(' ', $_SESSION['nombre'])[0] : 'Admin'; ?>!</h1>
        <div class="page-subtitle">Este es el resumen general de la botica al día de hoy.</div>
    </div>

    <!-- TARJETAS SUPERIORES -->
    <div class="row g-4 mb-4">
        <!-- Ingresos Totales -->
        <div class="col-md-3">
            <div class="card-metric h-100" style="position: relative; overflow: hidden; background: linear-gradient(135deg, var(--accent-primary) 0%, var(--success) 100%); color: white; border: none;">
                <!-- Watermark -->
                <i class="bi bi-cash-stack position-absolute" style="font-size: 110px; color: rgba(255,255,255,0.15); bottom: -20px; right: -10px; transform: rotate(-10deg);"></i>
                
                <div class="metric-header position-relative z-1">
                    <span style="font-size: 15px; font-weight: 600; opacity: 0.9; text-transform: uppercase; letter-spacing: 0.5px;">Ingresos Hoy</span>
                    <div class="metric-icon" style="background: rgba(255,255,255,0.25); color: white; backdrop-filter: blur(5px);">
                        <i class="bi bi-wallet2"></i>
                    </div>
                </div>
                <div class="position-relative z-1" style="font-size: 40px; font-weight: 800; margin-bottom: 12px; letter-spacing: -1.5px; text-shadow: 0 2px 10px rgba(0,0,0,0.1);">
                    S/ <?php echo number_format($data['metricas']['ingresos_hoy'], 2); ?>
                </div>
                <div class="position-relative z-1" style="font-size: 13px; font-weight: 600; background: rgba(0,0,0,0.15); display: inline-flex; align-items: center; gap: 6px; padding: 6px 14px; border-radius: 30px; backdrop-filter: blur(5px);">
                    <i class="bi bi-graph-up-arrow text-white"></i> Ingresos del día actual
                </div>
            </div>
        </div>
        
        <!-- Medicamentos Activos / Riesgo -->
        <div class="col-md-3">
            <div class="card-metric h-100" style="position: relative; overflow: hidden; background: linear-gradient(135deg, #3B82F6 0%, #2563EB 100%); color: white; border: none;">
                <!-- Watermark -->
                <i class="bi bi-capsule-pill position-absolute" style="font-size: 110px; color: rgba(255,255,255,0.12); bottom: -20px; right: -10px; transform: rotate(15deg);"></i>
                
                <div class="metric-header position-relative z-1">
                    <span style="font-size: 15px; font-weight: 600; opacity: 0.9; text-transform: uppercase; letter-spacing: 0.5px;">Stock Botica</span>
                    <div class="metric-icon" style="background: rgba(255,255,255,0.25); color: white; backdrop-filter: blur(5px);">
                        <i class="bi bi-box-seam"></i>
                    </div>
                </div>
                <div class="position-relative z-1" style="font-size: 40px; font-weight: 800; margin-bottom: 12px; letter-spacing: -1.5px; text-shadow: 0 2px 10px rgba(0,0,0,0.1);">
                    <?php echo number_format($data['metricas']['productos_total']); ?> <span style="font-size: 16px; font-weight: 600; opacity: 0.8; letter-spacing: 0;">ítems</span>
                </div>
                <div class="d-flex flex-column gap-2 align-items-start mt-1 position-relative z-1">
                    <?php if($data['metricas']['lotes_riesgo'] > 0): ?>
                    <div style="font-size: 12px; font-weight: 600; background: rgba(239, 68, 68, 0.95); display: inline-flex; align-items: center; gap: 6px; padding: 6px 14px; border-radius: 30px; box-shadow: 0 4px 10px rgba(239, 68, 68, 0.3);">
                        <i class="bi bi-exclamation-triangle-fill"></i> <?php echo $data['metricas']['lotes_riesgo']; ?> lotes por vencer
                    </div>
                    <?php endif; ?>
                    
                    <?php if($data['metricas']['productos_riesgo_stock'] > 0): ?>
                    <div style="font-size: 12px; font-weight: 600; background: rgba(245, 158, 11, 0.95); display: inline-flex; align-items: center; gap: 6px; padding: 6px 14px; border-radius: 30px; box-shadow: 0 4px 10px rgba(245, 158, 11, 0.3);">
                        <i class="bi bi-box-arrow-down"></i> <?php echo $data['metricas']['productos_riesgo_stock']; ?> prod. bajo stock
                    </div>
                    <?php endif; ?>

                    <?php if($data['metricas']['lotes_riesgo'] == 0 && $data['metricas']['productos_riesgo_stock'] == 0): ?>
                    <div style="font-size: 13px; font-weight: 600; background: rgba(0,0,0,0.15); display: inline-flex; align-items: center; gap: 6px; padding: 6px 14px; border-radius: 30px; backdrop-filter: blur(5px);">
                        <i class="bi bi-check-circle-fill text-white"></i> Inventario saludable
                    </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>

        <!-- Ventas Hoy -->
        <div class="col-md-3">
             <div class="card-metric h-100" style="position: relative; overflow: hidden; background: linear-gradient(135deg, #F59E0B 0%, #D97706 100%); color: white; border: none;">
                <!-- Watermark -->
                <i class="bi bi-receipt-cutoff position-absolute" style="font-size: 110px; color: rgba(255,255,255,0.12); bottom: -15px; right: -10px; transform: rotate(-5deg);"></i>
                
                <div class="metric-header position-relative z-1">
                    <span style="font-size: 15px; font-weight: 600; opacity: 0.9; text-transform: uppercase; letter-spacing: 0.5px;">Tickets / Ventas</span>
                    <div class="metric-icon" style="background: rgba(255,255,255,0.25); color: white; backdrop-filter: blur(5px);">
                        <i class="bi bi-bag-check-fill"></i>
                    </div>
                </div>
                <div class="position-relative z-1" style="font-size: 40px; font-weight: 800; margin-bottom: 12px; letter-spacing: -1.5px; text-shadow: 0 2px 10px rgba(0,0,0,0.1);">
                    <?php echo number_format($data['metricas']['ventas_hoy']); ?>
                </div>
                <div class="position-relative z-1" style="font-size: 13px; font-weight: 600; background: rgba(0,0,0,0.15); display: inline-flex; align-items: center; gap: 6px; padding: 6px 14px; border-radius: 30px; backdrop-filter: blur(5px);">
                    <i class="bi bi-clock-history text-white"></i> Transacciones de hoy
                </div>
            </div>
        </div>

        <!-- Clientes Activos -->
        <div class="col-md-3">
            <div class="card-metric h-100" style="position: relative; overflow: hidden; background: linear-gradient(135deg, #8B5CF6 0%, #6D28D9 100%); color: white; border: none;">
                <!-- Watermark -->
                <i class="bi bi-people-fill position-absolute" style="font-size: 110px; color: rgba(255,255,255,0.12); bottom: -15px; right: -15px; transform: rotate(10deg);"></i>
                
                <div class="metric-header position-relative z-1">
                    <span style="font-size: 15px; font-weight: 600; opacity: 0.9; text-transform: uppercase; letter-spacing: 0.5px;">Clientes Registrados</span>
                    <div class="metric-icon" style="background: rgba(255,255,255,0.25); color: white; backdrop-filter: blur(5px);">
                        <i class="bi bi-person-hearts"></i>
                    </div>
                </div>
                <div class="position-relative z-1" style="font-size: 40px; font-weight: 800; margin-bottom: 12px; letter-spacing: -1.5px; text-shadow: 0 2px 10px rgba(0,0,0,0.1);">
                    <?php echo number_format($data['metricas']['clientes_total']); ?>
                </div>
                <div class="position-relative z-1" style="font-size: 13px; font-weight: 600; background: rgba(0,0,0,0.15); display: inline-flex; align-items: center; gap: 6px; padding: 6px 14px; border-radius: 30px; backdrop-filter: blur(5px);">
                    <i class="bi bi-database-check text-white"></i> Base de datos total
                </div>
            </div>
        </div>
    </div>

    <!-- ZONA GRÁFICOS: Fila 1 -->
    <div class="row g-4 mb-4">
        <!-- Gráfico Crecimiento Ventas -->
        <div class="col-lg-8 col-12">
            <div class="card-metric h-100 d-flex flex-column">
                <h5 style="color: var(--text-primary); font-size: 16px; margin-bottom: 25px; font-weight: 700;">Ingresos de los últimos 7 días</h5>
                <div style="height: 260px; position: relative; width: 100%;">
                    <canvas id="barChart"></canvas>
                </div>
            </div>
        </div>

        <!-- Gráfico Medios de Pago -->
        <div class="col-lg-4 col-12">
            <div class="card-metric h-100 d-flex flex-column">
                <h5 style="color: var(--text-primary); font-size: 16px; margin-bottom: 25px; font-weight: 700;">Distribución de Ingresos</h5>
                
                <div style="height: 200px; position:relative; width: 100%;">
                    <canvas id="pieChart"></canvas>
                </div>
                
                <div class="mt-4 pt-3 border-top">
                    <!-- Detalle rápido debajo del pie chart -->
                    <?php 
                        $total_pagos = 0;
                        foreach($data['pagos'] as $p) $total_pagos += (float)$p['value'];
                        if($total_pagos == 0) $total_pagos = 1; // Evitar división por cero
                    ?>
                    <?php foreach($data['pagos'] as $i => $p): ?>
                        <div class="d-flex justify-content-between align-items-center mb-2">
                            <span class="text-secondary font-weight-500" style="font-size: 13px;">
                                <i class="bi bi-circle-fill me-1" style="font-size: 8px; color: <?php echo ['#00A896', '#02C39A', '#3498DB', '#F4A261', '#E63946'][$i % 5]; ?>"></i>
                                <?php echo htmlspecialchars($p['label']); ?>
                            </span>
                            <span class="font-weight-600" style="font-size: 13px;">S/ <?php echo number_format($p['value'], 2); ?></span>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>
        </div>
    </div>

    <!-- ZONA GRÁFICOS: Fila 2 -->
    <div class="row g-4">
        <!-- Gráfico Top Productos -->
        <div class="col-lg-6 col-12">
            <div class="card-metric h-100 d-flex flex-column">
                <h5 style="color: var(--text-primary); font-size: 16px; margin-bottom: 25px; font-weight: 700;">Top 5: Productos Más Vendidos (Unds)</h5>
                <div style="height: 260px; position: relative; width: 100%;">
                    <canvas id="topProductosChart"></canvas>
                </div>
            </div>
        </div>

        <!-- Gráfico Top Categorías -->
        <div class="col-lg-6 col-12">
            <div class="card-metric h-100 d-flex flex-column">
                <h5 style="color: var(--text-primary); font-size: 16px; margin-bottom: 25px; font-weight: 700;">Top 5: Categorías con Mayores Ingresos</h5>
                <div style="height: 260px; position:relative; width: 100%;">
                    <canvas id="topCategoriasChart"></canvas>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Lógica JavaScript de ChartJS -->
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
    // Variables Generadas desde PHP
    const grafLabels = <?php echo json_encode($data['grafico']['labels']); ?>;
    const grafData = <?php echo json_encode($data['grafico']['data']); ?>;

    <?php
        $pagosLabels = [];
        $pagosData = [];
        foreach($data['pagos'] as $p) {
            $pagosLabels[] = $p['label'];
            $pagosData[] = (float)$p['value'];
        }
    ?>
    const pieLabels = <?php echo json_encode($pagosLabels); ?>;
    const pieData = <?php echo json_encode($pagosData); ?>;

    // Config Inicial UI ChartJS (Claro/Médico)
    Chart.defaults.color = '#7F8C8D';
    Chart.defaults.font.family = "'Inter', sans-serif";
    Chart.defaults.font.size = 12;

    // 1. Gráfico de Barras - Ingresos 7 días
    const ctxBar = document.getElementById('barChart').getContext('2d');
    
    // Crear Gradiente para las barras
    let gradientBar = ctxBar.createLinearGradient(0, 0, 0, 400);
    gradientBar.addColorStop(0, '#00A896');
    gradientBar.addColorStop(1, '#02C39A');

    new Chart(ctxBar, {
        type: 'bar',
        data: {
            labels: grafLabels,
            datasets: [{
                label: 'Ingresos S/',
                data: grafData,
                backgroundColor: gradientBar,
                borderRadius: 6,
                borderSkipped: false,
                barPercentage: 0.4
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: { display: false },
                tooltip: {
                    backgroundColor: '#2C3E50',
                    padding: 12,
                    titleFont: { size: 14, family: 'Inter' },
                    bodyFont: { size: 14, weight: 'bold' },
                    displayColors: false,
                    callbacks: {
                        label: function(context) {
                            return 'S/ ' + context.parsed.y.toFixed(2);
                        }
                    }
                }
            },
            scales: {
                y: {
                    beginAtZero: true,
                    grid: { color: '#E2E8F0', drawBorder: false },
                    border: { display: false },
                    ticks: {
                        callback: function(value) { return 'S/ ' + value; },
                        padding: 10
                    }
                },
                x: {
                    grid: { display: false, drawBorder: false },
                    border: { display: false }
                }
            }
        }
    });

    // 2. Gráfico Donut - Medios de Pago
    const ctxPie = document.getElementById('pieChart').getContext('2d');
    
    let realData = pieData;
    let realLabels = pieLabels;
    if(realData.length === 0) {
        realData = [100];
        realLabels = ['Sin Movimientos'];
    }

    new Chart(ctxPie, {
        type: 'doughnut',
        data: {
            labels: realLabels,
            datasets: [{
                data: realData,
                backgroundColor: ['#00A896', '#02C39A', '#3498DB', '#F4A261', '#E63946'],
                borderWidth: 2,
                borderColor: '#ffffff',
                hoverOffset: 6
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            cutout: '72%',
            plugins: {
                legend: {
                    display: false // Oculto el legend automático para usar el HTML de abajo
                },
                tooltip: {
                    backgroundColor: '#2C3E50',
                    padding: 12,
                    callbacks: {
                        label: function(context) {
                            return ' S/ ' + context.parsed.toFixed(2);
                        }
                    }
                }
            }
        }
    });

    // 3. Gráfico de Barras Horizontales - Top Productos
    <?php
        $prodLabels = [];
        $prodData = [];
        if (isset($data['topProductos'])) {
            foreach($data['topProductos'] as $p) {
                // Acortar nombres muy largos
                $label = strlen($p['label']) > 25 ? substr($p['label'], 0, 22) . '...' : $p['label'];
                $prodLabels[] = $label;
                $prodData[] = (int)$p['value'];
            }
        }
    ?>
    const topProdLabels = <?php echo json_encode($prodLabels); ?>;
    const topProdData = <?php echo json_encode($prodData); ?>;

    const ctxTopProd = document.getElementById('topProductosChart').getContext('2d');
    
    // Gradiente horizontal
    let gradientProd = ctxTopProd.createLinearGradient(0, 0, 400, 0);
    gradientProd.addColorStop(0, '#8E44AD');
    gradientProd.addColorStop(1, '#9B59B6');

    new Chart(ctxTopProd, {
        type: 'bar',
        data: {
            labels: topProdLabels,
            datasets: [{
                label: 'Unidades Vendidas',
                data: topProdData,
                backgroundColor: gradientProd,
                borderRadius: 4,
                barPercentage: 0.5
            }]
        },
        options: {
            indexAxis: 'y', // Convertir a barras horizontales
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: { display: false },
                tooltip: {
                    backgroundColor: '#2C3E50',
                    padding: 10,
                    titleFont: { size: 13, family: 'Inter' },
                    bodyFont: { size: 13, weight: 'bold' }
                }
            },
            scales: {
                x: {
                    beginAtZero: true,
                    grid: { color: '#E2E8F0', drawBorder: false },
                    border: { display: false }
                },
                y: {
                    grid: { display: false, drawBorder: false },
                    border: { display: false },
                    ticks: {
                        font: { size: 11 }
                    }
                }
            }
        }
    });

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

    const ctxTopCat = document.getElementById('topCategoriasChart').getContext('2d');
    new Chart(ctxTopCat, {
        type: 'polarArea',
        data: {
            labels: topCatLabels,
            datasets: [{
                data: topCatData,
                backgroundColor: [
                    'rgba(243, 156, 18, 0.75)', 
                    'rgba(52, 152, 219, 0.75)', 
                    'rgba(46, 204, 113, 0.75)', 
                    'rgba(155, 89, 182, 0.75)', 
                    'rgba(231, 76, 60, 0.75)'
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
                    labels: { boxWidth: 12, padding: 15, font: { size: 11 } }
                },
                tooltip: {
                    backgroundColor: '#2C3E50',
                    callbacks: {
                        label: function(context) {
                            return ' S/ ' + context.parsed.r.toFixed(2);
                        }
                    }
                }
            },
            scales: {
                r: {
                    ticks: { display: false },
                    grid: { color: '#E2E8F0' }
                }
            }
        }
    });
</script>
