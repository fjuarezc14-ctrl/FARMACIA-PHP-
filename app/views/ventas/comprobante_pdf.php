<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Comprobante <?php echo $data['venta']['serie_comprobante'].'-'.$data['venta']['num_comprobante']; ?></title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: 'Arial', sans-serif; font-size: 13px; background: #f5f5f5; color: #1a1a2e; }
        
        .page-wrapper { max-width: 800px; margin: 0 auto; padding: 20px; }
        
        /* Botones de acción (solo pantalla) */
        .action-bar { display: flex; gap: 10px; margin-bottom: 20px; justify-content: flex-end; }
        .btn-pdf { padding: 10px 24px; border: none; border-radius: 8px; cursor: pointer; font-weight: 700; font-size: 14px; display: flex; align-items: center; gap: 8px; }
        .btn-print { background: linear-gradient(135deg, #1e293b, #334155); color: #fff; }
        .btn-close-win { background: #e2e8f0; color: #475569; }
        
        /* Comprobante */
        .comprobante { background: #fff; border-radius: 16px; box-shadow: 0 10px 40px rgba(0,0,0,0.12); overflow: hidden; }
        
        /* Header */
        .header { background: linear-gradient(135deg, #0f172a 0%, #1e3a5f 100%); color: white; padding: 30px 36px; display: flex; justify-content: space-between; align-items: flex-start; }
        .empresa-info h1 { font-size: 22px; font-weight: 800; letter-spacing: -0.5px; margin-bottom: 4px; }
        .empresa-info p { font-size: 12px; opacity: 0.8; line-height: 1.6; }
        .doc-box { background: rgba(255,255,255,0.12); border: 1px solid rgba(255,255,255,0.2); border-radius: 12px; padding: 16px 22px; text-align: center; min-width: 180px; }
        .doc-tipo { font-size: 11px; font-weight: 700; letter-spacing: 1.5px; text-transform: uppercase; opacity: 0.8; margin-bottom: 4px; }
        .doc-serie { font-size: 18px; font-weight: 800; letter-spacing: 1px; }
        .doc-ruc { font-size: 11px; opacity: 0.7; margin-top: 4px; }
        
        /* Datos cliente */
        .datos-grid { display: grid; grid-template-columns: 1fr 1fr; gap: 0; border-bottom: 1px solid #e2e8f0; }
        .dato-bloque { padding: 20px 36px; border-right: 1px solid #e2e8f0; }
        .dato-bloque:last-child { border-right: none; }
        .dato-label { font-size: 10px; font-weight: 700; text-transform: uppercase; letter-spacing: 1px; color: #94a3b8; margin-bottom: 6px; }
        .dato-valor { font-size: 14px; font-weight: 700; color: #1e293b; }
        .dato-sub { font-size: 12px; color: #64748b; margin-top: 2px; }
        
        /* Tabla productos */
        .section-title { padding: 14px 36px 8px; font-size: 10px; font-weight: 800; text-transform: uppercase; letter-spacing: 1.5px; color: #94a3b8; background: #f8fafc; border-bottom: 1px solid #e2e8f0; }
        table.items { width: 100%; border-collapse: collapse; }
        table.items thead tr { background: #f8fafc; }
        table.items thead th { padding: 10px 14px; font-size: 11px; font-weight: 700; text-transform: uppercase; letter-spacing: 0.8px; color: #64748b; border-bottom: 2px solid #e2e8f0; }
        table.items tbody tr { border-bottom: 1px solid #f1f5f9; transition: background 0.15s; }
        table.items tbody tr:hover { background: #f8fafc; }
        table.items tbody td { padding: 12px 14px; font-size: 13px; color: #1e293b; }
        .prod-name { font-weight: 700; display: block; }
        .prod-unit { font-size: 11px; color: #94a3b8; margin-top: 2px; display: inline-block; background: #f1f5f9; padding: 1px 6px; border-radius: 4px; }
        .text-right { text-align: right; }
        .text-center { text-align: center; }
        
        /* Totales */
        .totales-wrapper { display: flex; justify-content: flex-end; padding: 20px 36px; border-top: 2px solid #e2e8f0; gap: 40px; }
        .totales-table { min-width: 260px; }
        .totales-table tr td { padding: 5px 0; font-size: 13px; color: #475569; }
        .totales-table tr td:last-child { text-align: right; font-weight: 600; color: #1e293b; }
        .total-row td { padding-top: 10px !important; border-top: 2px solid #e2e8f0; font-size: 18px !important; font-weight: 800 !important; color: #0f172a !important; }
        
        /* Footer */
        .footer { padding: 20px 36px; border-top: 1px solid #e2e8f0; display: flex; justify-content: space-between; align-items: center; background: #f8fafc; }
        .qr-section { display: flex; align-items: center; gap: 12px; }
        .qr-box { width: 68px; height: 68px; background: white; border: 1px solid #cbd5e1; border-radius: 6px; display: flex; align-items: center; justify-content: center; overflow: hidden; padding: 2px; }
        .qr-box img { width: 100%; height: 100%; object-fit: contain; mix-blend-mode: multiply; }
        .qr-text { font-size: 11px; color: #64748b; line-height: 1.5; max-width: 180px; }
        .footer-right { text-align: right; }
        .footer-right p { font-size: 11px; color: #94a3b8; line-height: 1.7; }
        .estado-badge { display: inline-block; padding: 4px 12px; border-radius: 20px; font-size: 11px; font-weight: 700; margin-bottom: 6px; }
        .estado-generado { background: linear-gradient(135deg, #3B82F6, #6D28D9); color: white; }
        .estado-ticket { background: #e2e8f0; color: #475569; }
        
        /* Sello decorativo */
        .sello { width: 80px; height: 80px; border: 3px solid rgba(59,130,246,0.3); border-radius: 50%; display: flex; align-items: center; justify-content: center; flex-direction: column; text-align: center; font-size: 9px; font-weight: 800; color: #3B82F6; text-transform: uppercase; letter-spacing: 0.5px; line-height: 1.3; }

        /* PRINT STYLES */
        @media print {
            body { background: white; font-size: 12px; }
            .action-bar { display: none !important; }
            .page-wrapper { padding: 0; max-width: 100%; }
            .comprobante { box-shadow: none; border-radius: 0; }
            .header { -webkit-print-color-adjust: exact; print-color-adjust: exact; }
            .estado-badge { -webkit-print-color-adjust: exact; print-color-adjust: exact; }
            @page { margin: 8mm; size: A4; }
        }
    </style>
</head>
<body>
<div class="page-wrapper">
    
    <!-- Botones de acción -->
    <div class="action-bar no-print">
        <button class="btn-pdf btn-print" onclick="window.print()">
            &#128438; Guardar / Imprimir PDF
        </button>
        <button class="btn-pdf btn-close-win" onclick="window.close()">
            ✕ Cerrar
        </button>
    </div>

    <div class="comprobante">

        <!-- HEADER -->
        <div class="header">
            <div class="empresa-info">
                <?php 
                $pdfLogo = $data['config']['logo']['valor'] ?? '';
                if(!empty($pdfLogo)): 
                    $pdfLogoSrc = (strpos($pdfLogo, 'http') === 0) ? $pdfLogo : BASE_URL . $pdfLogo;
                ?>
                    <img src="<?php echo htmlspecialchars($pdfLogoSrc); ?>" alt="CENGFARMA" style="max-height:55px; max-width:220px; object-fit:contain; margin-bottom:8px;">
                <?php endif; ?>
                <h1><?php echo htmlspecialchars($data['config']['nombre_botica']['valor'] ?? 'CENGFARMA'); ?></h1>
                <p>
                    RUC: <?php echo htmlspecialchars($data['config']['ruc']['valor'] ?? '---'); ?><br>
                    <?php echo htmlspecialchars($data['config']['direccion']['valor'] ?? ''); ?><br>
                    <?php if(!empty($data['config']['telefono']['valor'])): ?>Tel: <?php echo htmlspecialchars($data['config']['telefono']['valor']); ?><?php endif; ?>
                </p>
            </div>
            <div class="doc-box">
                <?php
                $tipo = $data['venta']['tipo_comprobante'];
                $tipoLabel = 'TICKET INTERNO';
                if ($tipo === 'Boleta') $tipoLabel = 'BOLETA ELECTRÓNICA';
                if ($tipo === 'Factura') $tipoLabel = 'FACTURA ELECTRÓNICA';
                ?>
                <div class="doc-tipo"><?php echo $tipoLabel; ?></div>
                <div class="doc-serie"><?php echo htmlspecialchars($data['venta']['serie_comprobante'].'-'.$data['venta']['num_comprobante']); ?></div>
                <div class="doc-ruc">RUC <?php echo htmlspecialchars($data['config']['ruc']['valor'] ?? ''); ?></div>
            </div>
        </div>

        <!-- DATOS CLIENTE / EMISIÓN -->
        <div class="datos-grid">
            <div class="dato-bloque">
                <div class="dato-label">Cliente</div>
                <div class="dato-valor"><?php echo htmlspecialchars($data['venta']['cliente']); ?></div>
                <?php if(!empty($data['venta']['medico_cmp'])): ?>
                    <div class="dato-sub">CMP Médico: <?php echo htmlspecialchars($data['venta']['medico_cmp']); ?></div>
                <?php endif; ?>
            </div>
            <div class="dato-bloque">
                <div class="dato-label">Fecha y Hora de Emisión</div>
                <div class="dato-valor"><?php echo date('d/m/Y', strtotime($data['venta']['fecha_venta'])); ?></div>
                <div class="dato-sub"><?php echo date('H:i:s', strtotime($data['venta']['fecha_venta'])); ?> hrs</div>
            </div>
            <div class="dato-bloque">
                <div class="dato-label">Forma de Pago</div>
                <div class="dato-valor"><?php echo htmlspecialchars($data['venta']['metodo_pago']); ?></div>
                <?php if($data['venta']['metodo_pago'] === 'Mixto'): ?>
                    <div class="dato-sub">
                        Efe: S/ <?php echo number_format($data['venta']['monto_efectivo'] ?? 0, 2); ?> | 
                        Yape: S/ <?php echo number_format($data['venta']['monto_transferencia'] ?? 0, 2); ?> | 
                        Tarj: S/ <?php echo number_format($data['venta']['monto_tarjeta'] ?? 0, 2); ?>
                    </div>
                <?php endif; ?>
                <?php if(!empty($data['venta']['num_operacion_trans'])): ?>
                    <div class="dato-sub">Op. Yape/Plin: <?php echo htmlspecialchars($data['venta']['num_operacion_trans']); ?></div>
                <?php endif; ?>
                <?php if(!empty($data['venta']['num_operacion_tarj'])): ?>
                    <div class="dato-sub">Ref. Tarjeta: <?php echo htmlspecialchars($data['venta']['num_operacion_tarj']); ?></div>
                <?php endif; ?>
                <div class="dato-sub">Recibido: S/ <?php echo number_format($data['venta']['pago_recibido'], 2); ?> · Vuelto: S/ <?php echo number_format($data['venta']['vuelto'], 2); ?></div>
            </div>
            <div class="dato-bloque">
                <div class="dato-label">Atendido por</div>
                <div class="dato-valor"><?php echo htmlspecialchars($data['venta']['cajero']); ?></div>
            </div>
        </div>

        <!-- TABLA DE PRODUCTOS -->
        <div class="section-title">Detalle de Productos</div>
        <table class="items">
            <thead>
                <tr>
                    <th style="width:40px" class="text-center">#</th>
                    <th>Descripción</th>
                    <th class="text-center" style="width:80px">Cant.</th>
                    <th class="text-right" style="width:100px">P. Unitario</th>
                    <th class="text-right" style="width:110px">Subtotal</th>
                </tr>
            </thead>
            <tbody>
                <?php
                $grouped = [];
                foreach($data['detalles'] as $det) {
                    $key = $det['id_producto'] . '_' . $det['tipo_unidad'];
                    if (!isset($grouped[$key])) {
                        $grouped[$key] = [
                            'nombre'           => $det['nombre_comercial'],
                            'tipo_unidad'      => $det['tipo_unidad'],
                            'unidades_por_caja'=> $det['unidades_por_caja'] ?? 1,
                            'fraccionable'     => $det['fraccionable'] ?? 0,
                            'unidad_medida'    => $det['unidad_medida'] ?? 'Caja',
                            'unidad_fraccion'  => $det['unidad_fraccion'] ?? 'Unidad',
                            'cant_minimas'     => 0,
                            'precio_unitario'  => $det['precio_unitario'],
                            'subtotal'         => 0.00
                        ];
                    }
                    $grouped[$key]['cant_minimas'] += $det['cantidad'];
                    $grouped[$key]['subtotal']     += $det['subtotal'];
                }

                $rowNum = 1;
                foreach($grouped as $item):
                    $factor = ($item['fraccionable'] == 1 && $item['unidades_por_caja'] > 0) ? $item['unidades_por_caja'] : 1;
                    if ($item['tipo_unidad'] === $item['unidad_medida']) {
                        $cant_d  = $item['cant_minimas'] / $factor;
                        $precio_d = $item['precio_unitario'] * $factor;
                        $unit_label = $item['unidad_medida'];
                    } else {
                        $cant_d  = $item['cant_minimas'];
                        $precio_d = $item['precio_unitario'];
                        $unit_label = $item['unidad_fraccion'];
                    }
                ?>
                <tr>
                    <td class="text-center" style="color:#94a3b8;"><?php echo $rowNum++; ?></td>
                    <td>
                        <span class="prod-name"><?php echo htmlspecialchars($item['nombre']); ?></span>
                        <span class="prod-unit"><?php echo htmlspecialchars($unit_label); ?></span>
                    </td>
                    <td class="text-center"><?php echo number_format($cant_d, ($cant_d == floor($cant_d) ? 0 : 2)); ?></td>
                    <td class="text-right">S/ <?php echo number_format($precio_d, 2); ?></td>
                    <td class="text-right" style="font-weight:700;">S/ <?php echo number_format($item['subtotal'], 2); ?></td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>

        <!-- TOTALES -->
        <div class="totales-wrapper">
            <table class="totales-table">
                <tr>
                    <td>Operaciones Gravadas:</td>
                    <td>S/ <?php echo number_format($data['venta']['subtotal'], 2); ?></td>
                </tr>
                <?php if(!empty($data['venta']['descuento']) && $data['venta']['descuento'] > 0): ?>
                <tr>
                    <td>Descuento aplicado:</td>
                    <td style="color:#ef4444;">- S/ <?php echo number_format($data['venta']['descuento'], 2); ?></td>
                </tr>
                <?php endif; ?>
                <tr>
                    <td>IGV (<?php echo $data['config']['igv']['valor'] ?? '18'; ?>%):</td>
                    <td>S/ <?php echo number_format($data['venta']['igv'], 2); ?></td>
                </tr>
                <tr class="total-row">
                    <td>TOTAL A PAGAR:</td>
                    <td>S/ <?php echo number_format($data['venta']['total'], 2); ?></td>
                </tr>
            </table>
        </div>

        <!-- FOOTER CON QR Y ESTADO SUNAT -->
        <div class="footer">
            <div class="qr-section">
                <!-- QR dinámico para validación -->
                <div class="qr-box">
                    <?php 
                        $ruc_em = $data['config']['ruc']['valor'] ?? '';
                        $tipo_c = $data['venta']['tipo_comprobante'] === 'Factura' ? '01' : '03';
                        $ser_c = $data['venta']['serie_comprobante'];
                        $num_c = $data['venta']['num_comprobante'];
                        $igv_c = number_format($data['venta']['igv'], 2, '.', '');
                        $tot_c = number_format($data['venta']['total'], 2, '.', '');
                        $fec_c = date('Y-m-d', strtotime($data['venta']['fecha_venta']));
                        $qr_str = "$ruc_em|$tipo_c|$ser_c|$num_c|$igv_c|$tot_c|$fec_c|||";
                        $qr_url = "https://api.qrserver.com/v1/create-qr-code/?size=150x150&margin=0&data=" . urlencode($qr_str);
                    ?>
                    <img src="<?php echo $qr_url; ?>" alt="Código QR SUNAT">
                </div>
                <div class="qr-text">
                    <strong>Consulta este comprobante en:</strong><br>
                    https://ww1.sunat.gob.pe/ol-ti-itconsultaunificada<br>
                    <span style="font-family:monospace; color:#1e293b; font-weight:700;">
                        <?php echo ($data['config']['ruc']['valor'] ?? ''); ?>|<?php echo ($data['venta']['tipo_comprobante'] === 'Factura' ? '01' : '03'); ?>|<?php echo $data['venta']['serie_comprobante']; ?>-<?php echo $data['venta']['num_comprobante']; ?>
                    </span>
                </div>
            </div>
            <div class="footer-right">
                <?php
                $es = $data['venta']['estado_sunat'] ?? 'Pendiente';
                $badgeCls = ($es === 'Generado Local') ? 'estado-generado' : 'estado-ticket';
                ?>
                <span class="estado-badge <?php echo $badgeCls; ?>">
                    <?php echo $es === 'Generado Local' ? '&#10003; XML Generado' : 'Comprobante Interno'; ?>
                </span>
                <p>
                    Representación impresa del comprobante electrónico.<br>
                    Conserve este documento para cualquier consulta.<br>
                    <strong><?php echo htmlspecialchars($data['config']['nombre_botica']['valor'] ?? ''); ?></strong> — RUC <?php echo htmlspecialchars($data['config']['ruc']['valor'] ?? ''); ?>
                </p>
            </div>
        </div>

    </div><!-- fin .comprobante -->
</div><!-- fin .page-wrapper -->

<script>
    // Auto abrir el diálogo de impresión/PDF
    window.addEventListener('load', function() {
        setTimeout(function() { window.print(); }, 600);
    });
    window.addEventListener('afterprint', function() {
        window.close();
    });
</script>
</body>
</html>
