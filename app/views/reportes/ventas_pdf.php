<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reporte Financiero de Ventas - CENGFARMA</title>
    <style>
        @page {
            size: A4 portrait;
            margin: 12mm 12mm 15mm 12mm;
        }
        * { box-sizing: border-box; }
        body {
            font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, "Helvetica Neue", Arial, sans-serif;
            font-size: 11px;
            line-height: 1.4;
            color: #1e293b;
            background: #fff;
            margin: 0;
            padding: 10px 20px;
        }
        .no-print {
            text-align: right;
            padding: 10px 0 20px;
            border-bottom: 1px solid #e2e8f0;
            margin-bottom: 15px;
        }
        .btn {
            display: inline-block;
            padding: 8px 16px;
            font-size: 12px;
            font-weight: 700;
            border-radius: 6px;
            cursor: pointer;
            border: 1px solid transparent;
            text-decoration: none;
        }
        .btn-print { background: #1FA95B; color: #fff; }
        .btn-print:hover { background: #178a49; }
        .btn-close { background: #f1f5f9; color: #475569; border-color: #cbd5e1; margin-left: 8px; }
        
        /* Encabezado */
        .header-box {
            display: flex;
            justify-content: space-between;
            align-items: center;
            border-bottom: 3px solid #1FA95B;
            padding-bottom: 15px;
            margin-bottom: 15px;
        }
        .logo-wrap {
            max-width: 200px;
        }
        .logo-wrap img {
            max-height: 65px;
            max-width: 100%;
            object-fit: contain;
        }
        .company-info {
            text-align: right;
        }
        .company-name {
            font-size: 18px;
            font-weight: 800;
            color: #1FA95B;
            letter-spacing: -0.5px;
            margin: 0 0 4px;
            text-transform: uppercase;
        }
        .company-meta {
            font-size: 11px;
            color: #64748b;
            margin: 2px 0;
        }

        /* Título del reporte */
        .report-title-bar {
            background: linear-gradient(135deg, #0f172a 0%, #1e3a5f 100%);
            color: #fff;
            padding: 10px 16px;
            border-radius: 8px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 15px;
        }
        .report-title {
            font-size: 14px;
            font-weight: 800;
            letter-spacing: 0.5px;
            margin: 0;
            text-transform: uppercase;
        }
        .report-period {
            font-size: 11px;
            font-weight: 600;
            color: #93c5fd;
        }

        /* KPI Cards */
        .kpi-grid {
            display: flex;
            gap: 10px;
            margin-bottom: 15px;
        }
        .kpi-card {
            flex: 1;
            background: #f8fafc;
            border: 1px solid #e2e8f0;
            border-radius: 8px;
            padding: 10px;
            text-align: center;
        }
        .kpi-label {
            font-size: 10px;
            font-weight: 700;
            text-transform: uppercase;
            color: #64748b;
            margin-bottom: 4px;
        }
        .kpi-value {
            font-size: 16px;
            font-weight: 800;
            color: #0f172a;
        }
        .kpi-card.success .kpi-value { color: #1FA95B; }
        .kpi-card.info .kpi-value { color: #0284c7; }
        .kpi-card.warning .kpi-value { color: #d97706; }

        /* Tabla de ventas */
        table.report-table {
            width: 100%;
            border-collapse: collapse;
            font-size: 10px;
            margin-bottom: 15px;
        }
        table.report-table th {
            background-color: #1FA95B;
            color: #ffffff;
            font-weight: 700;
            text-transform: uppercase;
            padding: 8px 6px;
            text-align: left;
            border: 1px solid #1FA95B;
            font-size: 10px;
        }
        table.report-table td {
            padding: 6px 6px;
            border: 1px solid #e2e8f0;
            vertical-align: middle;
        }
        table.report-table tbody tr:nth-child(even) {
            background-color: #f8fafc;
        }
        .text-end { text-align: right; }
        .text-center { text-align: center; }
        .badge-method {
            display: inline-block;
            padding: 2px 6px;
            border-radius: 4px;
            font-size: 9px;
            font-weight: 700;
        }
        .badge-efe { background: #dcfce7; color: #15803d; }
        .badge-yape { background: #e0f2fe; color: #0369a1; }
        .badge-tarj { background: #ede9fe; color: #6d28d9; }
        .badge-mix { background: #fef3c7; color: #b45309; }

        /* Totales Box */
        .bottom-summary {
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
            gap: 20px;
            margin-top: 10px;
        }
        .payment-breakdown {
            flex: 1;
            background: #f8fafc;
            border: 1px solid #e2e8f0;
            border-radius: 8px;
            padding: 10px 14px;
            font-size: 10px;
        }
        .payment-breakdown h4 {
            margin: 0 0 6px;
            font-size: 11px;
            color: #334155;
            border-bottom: 1px solid #cbd5e1;
            padding-bottom: 4px;
        }
        .breakdown-row {
            display: flex;
            justify-content: space-between;
            margin: 3px 0;
        }
        .totales-box {
            width: 280px;
            border: 2px solid #0f172a;
            border-radius: 8px;
            overflow: hidden;
        }
        .totales-row {
            display: flex;
            justify-content: space-between;
            padding: 6px 12px;
            border-bottom: 1px solid #e2e8f0;
            font-size: 11px;
        }
        .totales-row.grand-total {
            background: #0f172a;
            color: #ffffff;
            font-weight: 800;
            font-size: 13px;
            border-bottom: none;
        }

        /* Firmas */
        .signatures {
            margin-top: 50px;
            display: flex;
            justify-content: space-around;
            text-align: center;
        }
        .sign-block {
            width: 200px;
            border-top: 1px solid #475569;
            padding-top: 6px;
            font-size: 10px;
            font-weight: 600;
            color: #475569;
        }

        /* Footer */
        .report-footer {
            margin-top: 30px;
            text-align: center;
            font-size: 9px;
            color: #94a3b8;
            border-top: 1px solid #e2e8f0;
            padding-top: 8px;
        }

        @media print {
            .no-print { display: none !important; }
            body { padding: 0; }
            .report-table th { -webkit-print-color-adjust: exact; print-color-adjust: exact; }
            .report-title-bar { -webkit-print-color-adjust: exact; print-color-adjust: exact; }
            .grand-total { -webkit-print-color-adjust: exact; print-color-adjust: exact; }
        }
    </style>
</head>
<body>

    <div class="no-print">
        <button class="btn btn-print" onclick="window.print()">🖨️ IMPRIMIR / GUARDAR EN PDF</button>
        <button class="btn btn-close" onclick="window.close()">✕ Cerrar Ventana</button>
    </div>

    <!-- Encabezado de la Empresa -->
    <div class="header-box">
        <div class="logo-wrap">
            <?php 
                $rawLogo = !empty($data['config']['logo']['valor']) ? $data['config']['logo']['valor'] : 'img/cengfarma_banner.png';
                $logoUrl = (strpos($rawLogo, 'http') === 0) ? $rawLogo : BASE_URL . ltrim($rawLogo, '/');
            ?>
            <img src="<?php echo htmlspecialchars($logoUrl); ?>" alt="Logo CENGFARMA">
        </div>
        <div class="company-info">
            <h1 class="company-name"><?php echo htmlspecialchars($data['config']['nombre_botica']['valor'] ?? 'BOTICA CENGFARMA'); ?></h1>
            <p class="company-meta"><strong>RUC:</strong> <?php echo htmlspecialchars($data['config']['ruc']['valor'] ?? '-'); ?></p>
            <p class="company-meta"><?php echo htmlspecialchars($data['config']['direccion']['valor'] ?? '-'); ?></p>
            <p class="company-meta"><strong>Teléfono:</strong> <?php echo htmlspecialchars($data['config']['telefono']['valor'] ?? '-'); ?></p>
        </div>
    </div>

    <!-- Título y Rango de Fechas -->
    <div class="report-title-bar">
        <h2 class="report-title">REPORTE FINANCIERO Y AUDITORÍA DE VENTAS</h2>
        <span class="report-period">
            Periodo: <?php echo date('d/m/Y', strtotime($data['fecha_inicio'])); ?> al <?php echo date('d/m/Y', strtotime($data['fecha_fin'])); ?>
        </span>
    </div>

    <?php
    $sum_sub = 0; $sum_igv = 0; $sum_desc = 0; $sum_tot = 0;
    $sum_efe = 0; $sum_tra = 0; $sum_tar = 0;
    $count_ventas = count($data['ventas']);

    foreach($data['ventas'] as $v) {
        $sum_sub += (float)$v['subtotal'];
        $sum_igv += (float)$v['igv'];
        $sum_desc += (float)($v['descuento'] ?? 0);
        $sum_tot += (float)$v['total'];

        // Acumular métodos
        $m_efe = isset($v['monto_efectivo']) ? (float)$v['monto_efectivo'] : ($v['metodo_pago'] === 'Efectivo' ? (float)$v['total'] : 0);
        $m_tra = isset($v['monto_transferencia']) ? (float)$v['monto_transferencia'] : (in_array($v['metodo_pago'], ['Yape', 'Yape/Plin']) ? (float)$v['total'] : 0);
        $m_tar = isset($v['monto_tarjeta']) ? (float)$v['monto_tarjeta'] : ($v['metodo_pago'] === 'Tarjeta' ? (float)$v['total'] : 0);

        $sum_efe += $m_efe;
        $sum_tra += $m_tra;
        $sum_tar += $m_tar;
    }
    $moneda = $data['config']['moneda']['valor'] ?? 'S/';
    ?>

    <!-- Resumen Gerencial en Tarjetas KPI -->
    <div class="kpi-grid">
        <div class="kpi-card success">
            <div class="kpi-label">Ingresos Totales Brutos</div>
            <div class="kpi-value"><?php echo $moneda; ?> <?php echo number_format($sum_tot, 2); ?></div>
        </div>
        <div class="kpi-card">
            <div class="kpi-label">Operaciones Registradas</div>
            <div class="kpi-value"><?php echo $count_ventas; ?> ventas</div>
        </div>
        <div class="kpi-card warning">
            <div class="kpi-label">Total en Efectivo Físico</div>
            <div class="kpi-value"><?php echo $moneda; ?> <?php echo number_format($sum_efe, 2); ?></div>
        </div>
        <div class="kpi-card info">
            <div class="kpi-label">Total Bancario / Digital</div>
            <div class="kpi-value"><?php echo $moneda; ?> <?php echo number_format($sum_tra + $sum_tar, 2); ?></div>
        </div>
    </div>

    <!-- Tabla Detallada -->
    <table class="report-table">
        <thead>
            <tr>
                <th width="85">FECHA/HORA</th>
                <th width="105">COMPROBANTE</th>
                <th>CLIENTE</th>
                <th width="80">CAJERO</th>
                <th width="110">MÉTODO PAGO</th>
                <th width="65" class="text-end">SUBTOTAL</th>
                <th width="55" class="text-end">IGV</th>
                <th width="75" class="text-end">TOTAL</th>
            </tr>
        </thead>
        <tbody>
            <?php if(empty($data['ventas'])): ?>
                <tr>
                    <td colspan="8" class="text-center" style="padding: 20px; color: #94a3b8;">
                        No se registraron transacciones de venta en el rango de fechas seleccionado.
                    </td>
                </tr>
            <?php else: ?>
                <?php foreach($data['ventas'] as $v): 
                    $badgeClass = 'badge-efe';
                    if ($v['metodo_pago'] === 'Yape' || $v['metodo_pago'] === 'Yape/Plin') $badgeClass = 'badge-yape';
                    elseif ($v['metodo_pago'] === 'Tarjeta') $badgeClass = 'badge-tarj';
                    elseif ($v['metodo_pago'] === 'Mixto') $badgeClass = 'badge-mix';
                ?>
                <tr>
                    <td style="font-family: monospace; font-size: 9px;">
                        <?php echo date('d/m/Y', strtotime($v['fecha_venta'])); ?><br>
                        <span style="color:#64748b;"><?php echo date('H:i:s', strtotime($v['fecha_venta'])); ?></span>
                    </td>
                    <td>
                        <strong><?php echo htmlspecialchars($v['serie_comprobante'] . '-' . $v['num_comprobante']); ?></strong><br>
                        <small style="color:#64748b; font-size: 8.5px;"><?php echo htmlspecialchars($v['tipo_comprobante']); ?></small>
                    </td>
                    <td><?php echo htmlspecialchars($v['cliente']); ?></td>
                    <td><?php echo htmlspecialchars($v['cajero']); ?></td>
                    <td>
                        <span class="badge-method <?php echo $badgeClass; ?>"><?php echo htmlspecialchars($v['metodo_pago']); ?></span>
                        <?php if(!empty($v['num_operacion_trans'])): ?>
                            <br><span style="font-size: 8px; color:#64748b;">Op: <?php echo htmlspecialchars($v['num_operacion_trans']); ?></span>
                        <?php endif; ?>
                        <?php if(!empty($v['num_operacion_tarj'])): ?>
                            <br><span style="font-size: 8px; color:#64748b;">Ref: <?php echo htmlspecialchars($v['num_operacion_tarj']); ?></span>
                        <?php endif; ?>
                    </td>
                    <td class="text-end"><?php echo number_format($v['subtotal'], 2); ?></td>
                    <td class="text-end"><?php echo number_format($v['igv'], 2); ?></td>
                    <td class="text-end"><strong><?php echo number_format($v['total'], 2); ?></strong></td>
                </tr>
                <?php endforeach; ?>
            <?php endif; ?>
        </tbody>
    </table>

    <!-- Bloque de Totales y Desglose Financiero -->
    <div class="bottom-summary">
        <div class="payment-breakdown">
            <h4>CONSOLIDADO POR CANAL DE PAGO</h4>
            <div class="breakdown-row">
                <span>💵 Efectivo en Caja:</span>
                <strong><?php echo $moneda; ?> <?php echo number_format($sum_efe, 2); ?></strong>
            </div>
            <div class="breakdown-row">
                <span>📱 Yape / Plin (Billeteras Digitales):</span>
                <strong><?php echo $moneda; ?> <?php echo number_format($sum_tra, 2); ?></strong>
            </div>
            <div class="breakdown-row">
                <span>💳 Tarjetas de Débito / Crédito:</span>
                <strong><?php echo $moneda; ?> <?php echo number_format($sum_tar, 2); ?></strong>
            </div>
            <div class="breakdown-row" style="border-top: 1px dashed #cbd5e1; margin-top: 5px; padding-top: 4px; font-weight:700;">
                <span>Total de Ingresos Auditados:</span>
                <span style="color:#1FA95B;"><?php echo $moneda; ?> <?php echo number_format($sum_tot, 2); ?></span>
            </div>
        </div>

        <div class="totales-box">
            <div class="totales-row">
                <span>Subtotal Neto:</span>
                <span><?php echo $moneda; ?> <?php echo number_format($sum_sub, 2); ?></span>
            </div>
            <?php if($sum_desc > 0): ?>
            <div class="totales-row" style="color: #dc2626;">
                <span>Total Descuentos Otorgados:</span>
                <span>- <?php echo $moneda; ?> <?php echo number_format($sum_desc, 2); ?></span>
            </div>
            <?php endif; ?>
            <div class="totales-row">
                <span>Impuesto I.G.V. (18%):</span>
                <span><?php echo $moneda; ?> <?php echo number_format($sum_igv, 2); ?></span>
            </div>
            <div class="totales-row grand-total">
                <span>TOTAL RECAUDADO:</span>
                <span><?php echo $moneda; ?> <?php echo number_format($sum_tot, 2); ?></span>
            </div>
        </div>
    </div>

    <!-- Sección de Firmas de Auditoría -->
    <div class="signatures">
        <div class="sign-block">
            Administración General / Gerencia<br>
            <small style="font-weight:normal;">V°B° Financiero</small>
        </div>
        <div class="sign-block">
            Químico Farmacéutico Regente<br>
            <small style="font-weight:normal;">Sello y Firma de Conformidad</small>
        </div>
    </div>

    <!-- Pie de Página Legal -->
    <div class="report-footer">
        Documento oficial generado por el Sistema de Farmacia CENGFARMA el <?php echo date('d/m/Y H:i:s'); ?>.<br>
        La información contenida en este reporte es estrictamente confidencial para fines contables y tributarios.
    </div>

</body>
</html>
