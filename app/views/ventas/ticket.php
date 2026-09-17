<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Ticket de Venta #<?php echo htmlspecialchars($data['venta']['num_comprobante']); ?></title>
    <style>
        body { font-family: 'Courier New', Courier, monospace; font-size: 12px; margin: 0; padding: 0; background-color: #f0f0f0; }
        .ticket { width: 80mm; max-width: 80mm; background-color: white; margin: 0 auto; padding: 5mm; box-sizing: border-box; }
        .center { text-align: center; }
        .right { text-align: right; }
        .bold { font-weight: bold; }
        table { width: 100%; border-collapse: collapse; margin-top: 10px; margin-bottom: 10px; font-size: 11px; }
        th { border-bottom: 1px dashed #000; border-top: 1px dashed #000; padding: 4px 0; text-align: left; }
        td { padding: 3px 0; }
        .divider { border-bottom: 1px dashed #000; margin: 10px 0; }
        .total-row { font-size: 14px; font-weight: bold; }
        
        /* Ocultar elementos en la impresión */
        @media print {
            body { background-color: white; }
            .no-print { display: none !important; }
            .ticket { margin: 0; padding: 0; width: 100%; }
            @page { margin: 0; }
        }
    </style>
</head>
<body>
    <div class="ticket">
        <div class="no-print center" style="margin-bottom: 15px;">
            <button onclick="window.print()" style="padding: 10px 20px; cursor: pointer; background: #000; color:#fff; border:none; font-weight:bold;">IMPRIMIR TICKET</button>
            <button onclick="window.close()" style="padding: 10px; cursor: pointer; background: #ccc; border:none;">X Cerrar</button>
        </div>

        <div class="center">
            <h2 style="margin: 0; padding: 0;"><?php echo htmlspecialchars($data['config']['nombre_botica']['valor']); ?></h2>
            <p style="margin: 3px 0;">RUC: <?php echo htmlspecialchars($data['config']['ruc']['valor']); ?></p>
            <p style="margin: 3px 0;"><?php echo htmlspecialchars($data['config']['direccion']['valor']); ?></p>
            <?php if(!empty($data['config']['telefono']['valor'])): ?>
            <p style="margin: 3px 0;">Tel: <?php echo htmlspecialchars($data['config']['telefono']['valor']); ?></p>
            <?php endif; ?>
            <p style="margin: 3px 0;">--------------------------------</p>
        </div>
        
        <?php
        $tipo_doc_titulo = 'TICKET INTERNO';
        if($data['venta']['tipo_comprobante'] === 'Boleta') $tipo_doc_titulo = 'BOLETA DE VENTA ELECTRÓNICA';
        if($data['venta']['tipo_comprobante'] === 'Factura') $tipo_doc_titulo = 'FACTURA ELECTRÓNICA';
        ?>
        <p style="margin: 3px 0;"><span class="bold"><?php echo $tipo_doc_titulo; ?></span></p>
        <p style="margin: 3px 0;">Nro: <?php echo htmlspecialchars($data['venta']['serie_comprobante']); ?>-<?php echo htmlspecialchars($data['venta']['num_comprobante']); ?></p>
        <p style="margin: 3px 0;">Fecha: <?php echo date('d/m/Y H:i:s', strtotime($data['venta']['fecha_venta'])); ?></p>
        <p style="margin: 3px 0;">Cajero: <?php echo htmlspecialchars($data['venta']['cajero']); ?></p>
        <p style="margin: 3px 0;">Cliente: <?php echo htmlspecialchars($data['venta']['cliente']); ?></p>

        <div class="divider"></div>

        <table>
            <thead>
                <tr>
                    <th>CANT</th>
                    <th>PRODUCTO</th>
                    <th class="right">SUBT</th>
                </tr>
            </thead>
            <tbody>
                <?php 
                // Group details to handle lot split transparently and format quantities beautifully
                $grouped = [];
                foreach($data['detalles'] as $det) {
                    $key = $det['id_producto'] . '_' . $det['tipo_unidad'];
                    if (!isset($grouped[$key])) {
                        $grouped[$key] = [
                            'nombre' => $det['nombre_comercial'],
                            'tipo_unidad' => $det['tipo_unidad'],
                            'unidades_por_caja' => $det['unidades_por_caja'] ?? 1,
                            'fraccionable' => $det['fraccionable'] ?? 0,
                            'unidad_medida' => $det['unidad_medida'] ?? 'Caja',
                            'unidad_fraccion' => $det['unidad_fraccion'] ?? 'Unidad',
                            'cant_minimas' => 0,
                            'precio_unitario_minimo' => $det['precio_unitario'],
                            'subtotal' => 0.00
                        ];
                    }
                    $grouped[$key]['cant_minimas'] += $det['cantidad'];
                    $grouped[$key]['subtotal'] += $det['subtotal'];
                }
                
                foreach($grouped as $item):
                    $factor = ($item['fraccionable'] == 1 && $item['unidades_por_caja'] > 0) ? $item['unidades_por_caja'] : 1;
                    
                    // Si la unidad vendida coincide con la unidad de medida (Caja), se muestra en Cajas
                    if ($item['tipo_unidad'] === $item['unidad_medida']) {
                        $cant_display = $item['cant_minimas'] / $factor;
                        $precio_display = $item['precio_unitario_minimo'] * $factor;
                    } else {
                        // Se muestra en Fracciones/Pastillas
                        $cant_display = $item['cant_minimas'];
                        $precio_display = $item['precio_unitario_minimo'];
                    }
                ?>
                <tr>
                    <td valign="top"><?php echo number_format($cant_display, 0); ?></td>
                    <td>
                        <?php echo htmlspecialchars($item['nombre']); ?>
                        <?php if(!empty($item['tipo_unidad']) && $item['tipo_unidad'] !== 'Unidad'): ?>
                            <small class="bold" style="background:#000; color:#fff; padding:1px 3px; border-radius:3px;">(<?php echo htmlspecialchars($item['tipo_unidad']); ?>)</small>
                        <?php endif; ?>
                        <br>
                        <small>P.U: <?php echo number_format($precio_display, 2); ?></small>
                    </td>
                    <td valign="top" class="right"><?php echo number_format($item['subtotal'], 2); ?></td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>

        <div class="divider"></div>

        <table style="font-size: 12px; margin-top:0;">
            <tr>
                <td>OP. GRAVADA</td>
                <td class="right">S/ <?php echo number_format($data['venta']['subtotal'], 2); ?></td>
            </tr>
            <?php if(isset($data['venta']['descuento']) && $data['venta']['descuento'] > 0): ?>
            <tr>
                <td>
                    DESCUENTO
                    <?php if(!empty($data['venta']['motivo_descuento'])): ?>
                        <br><small style="font-size: 9px; font-weight: normal;">(<?php echo htmlspecialchars($data['venta']['motivo_descuento']); ?>)</small>
                    <?php endif; ?>
                </td>
                <td class="right">-S/ <?php echo number_format($data['venta']['descuento'], 2); ?></td>
            </tr>
            <?php endif; ?>
            <tr>
                <td>I.G.V. (<?php echo htmlspecialchars($data['config']['igv']['valor']); ?>%)</td>
                <td class="right">S/ <?php echo number_format($data['venta']['igv'], 2); ?></td>
            </tr>
            <tr class="total-row">
                <td>TOTAL PERCIBIDO</td>
                <td class="right">S/ <?php echo number_format($data['venta']['total'], 2); ?></td>
            </tr>
        </table>
        
        <div class="divider"></div>
        
        <table style="font-size: 11px;">
            <tr>
                <td>Forma de Pago:</td>
                <td class="right bold"><?php echo htmlspecialchars($data['venta']['metodo_pago']); ?></td>
            </tr>
            <?php if($data['venta']['metodo_pago'] === 'Mixto'): ?>
                <?php if(($data['venta']['monto_efectivo'] ?? 0) > 0): ?>
                <tr>
                    <td> - Efectivo:</td>
                    <td class="right">S/ <?php echo number_format($data['venta']['monto_efectivo'], 2); ?></td>
                </tr>
                <?php endif; ?>
                <?php if(($data['venta']['monto_transferencia'] ?? 0) > 0): ?>
                <tr>
                    <td> - Yape/Plin:</td>
                    <td class="right">S/ <?php echo number_format($data['venta']['monto_transferencia'], 2); ?></td>
                </tr>
                <?php if(!empty($data['venta']['num_operacion_trans'])): ?>
                <tr>
                    <td style="font-size: 9px; padding-left: 10px;">Op. Yape:</td>
                    <td class="right" style="font-size: 9px;"><?php echo htmlspecialchars($data['venta']['num_operacion_trans']); ?></td>
                </tr>
                <?php endif; ?>
                <?php endif; ?>
                <?php if(($data['venta']['monto_tarjeta'] ?? 0) > 0): ?>
                <tr>
                    <td> - Tarjeta:</td>
                    <td class="right">S/ <?php echo number_format($data['venta']['monto_tarjeta'], 2); ?></td>
                </tr>
                <?php if(!empty($data['venta']['num_operacion_tarj'])): ?>
                <tr>
                    <td style="font-size: 9px; padding-left: 10px;">Ref. Tarj:</td>
                    <td class="right" style="font-size: 9px;"><?php echo htmlspecialchars($data['venta']['num_operacion_tarj']); ?></td>
                </tr>
                <?php endif; ?>
                <?php endif; ?>
            <?php else: ?>
                <?php if(!empty($data['venta']['num_operacion_trans'])): ?>
                <tr>
                    <td>N° Op. Yape/Plin:</td>
                    <td class="right"><?php echo htmlspecialchars($data['venta']['num_operacion_trans']); ?></td>
                </tr>
                <?php endif; ?>
                <?php if(!empty($data['venta']['num_operacion_tarj'])): ?>
                <tr>
                    <td>N° Ref. Tarjeta:</td>
                    <td class="right"><?php echo htmlspecialchars($data['venta']['num_operacion_tarj']); ?></td>
                </tr>
                <?php endif; ?>
            <?php endif; ?>
            <tr>
                <td>Total Recibido:</td>
                <td class="right">S/ <?php echo number_format($data['venta']['pago_recibido'], 2); ?></td>
            </tr>
            <tr>
                <td>Vuelto:</td>
                <td class="right">S/ <?php echo number_format($data['venta']['vuelto'], 2); ?></td>
            </tr>
        </table>

        <div class="divider"></div>

        <div class="center" style="margin-top: 15px;">
            <?php 
                $ruc_em = $data['config']['ruc']['valor'] ?? '';
                $tipo_c = $data['venta']['tipo_comprobante'] === 'Factura' ? '01' : '03';
                $ser_c = $data['venta']['serie_comprobante'];
                $num_c = $data['venta']['num_comprobante'];
                $igv_c = number_format($data['venta']['igv'], 2, '.', '');
                $tot_c = number_format($data['venta']['total'], 2, '.', '');
                $fec_c = date('Y-m-d', strtotime($data['venta']['fecha_venta']));
                $qr_str = "$ruc_em|$tipo_c|$ser_c|$num_c|$igv_c|$tot_c|$fec_c|||";
                $qr_url = "https://api.qrserver.com/v1/create-qr-code/?size=120x120&margin=0&data=" . urlencode($qr_str);
            ?>
            <img src="<?php echo $qr_url; ?>" alt="QR SUNAT" style="width: 120px; height: 120px; margin-bottom: 5px;">
            <p style="font-size: 9px; line-height: 1.3;">
                Representación impresa del comprobante electrónico.<br>
                Consulte en: www.sunat.gob.pe
            </p>
        </div>

        <div class="center" style="margin-top: 15px;">
            <p>*** GRACIAS POR SU COMPRA ***</p>
            <p>Conserve este ticket para <br>cualquier reclamo.</p>
        </div>
        
        <?php if($data['venta']['id_cliente'] != 1 && (isset($data['venta']['puntos_ganados']) || isset($data['venta']['puntos_usados']))): ?>
        <div class="divider"></div>
        <div class="center" style="font-size:10px; margin-top:5px; border:1px solid #000; padding:5px; border-radius:5px;">
            <p style="margin:2px 0;"><strong>-- CLUB DE CLIENTES --</strong></p>
            <?php if($data['venta']['puntos_ganados'] > 0): ?>
                <p style="margin:2px 0;">Puntos Ganados Hoy: <span class="bold">+<?php echo $data['venta']['puntos_ganados']; ?></span></p>
            <?php endif; ?>
            <?php if($data['venta']['puntos_usados'] > 0): ?>
                <p style="margin:2px 0;">Puntos Usados Hoy: <span class="bold">-<?php echo $data['venta']['puntos_usados']; ?></span></p>
            <?php endif; ?>
        </div>
        <?php endif; ?>
        
        <br>
    </div>
    
    <script>
        // Auto-imprimir para Tiqueteras Térmicas
        window.onload = function() {
            // Un pequeño timeout asegura que los estilos se apliquen
            setTimeout(function() {
                window.print();
            }, 500);
        };
        
        // Auto-cerrar el popup de ticket al finalizar la impresión
        window.addEventListener('afterprint', function() {
            window.close();
        });
    </script>
</body>
</html>
