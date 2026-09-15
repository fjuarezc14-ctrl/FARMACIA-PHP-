<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Reporte de Reposición de Stock PDF</title>
    <style>
        body { font-family: 'Helvetica Neue', Helvetica, Arial, sans-serif; font-size: 12px; margin: 0; padding: 20px; color: #333; }
        .cabecera { display: flex; justify-content: space-between; align-items: center; border-bottom: 2px solid #000; padding-bottom: 10px; margin-bottom: 20px; }
        .logo { max-height: 60px; max-width: 150px; object-fit: contain; }
        .info-empresa { text-align: right; }
        .info-empresa h2 { margin: 0 0 5px 0; font-size: 18px; color: #1FA95B; }
        .info-empresa p { margin: 0; font-size: 11px; color: #666; }
        h1.titulo-reporte { text-align: center; font-size: 16px; margin: 20px 0; padding: 5px; background: #fdf2e9; color: #e67e22; border: 1px solid #f8c471; }
        table { width: 100%; border-collapse: collapse; margin-bottom: 20px; }
        th, td { border: 1px solid #ccc; padding: 6px 8px; text-align: left; }
        th { background-color: #f8f9fa; font-weight: bold; font-size: 11px; text-transform: uppercase; }
        td { font-size: 11px; }
        .text-center { text-align: center; }
        .badge-rojo { background: #e74c3c; color:#fff; padding:3px 8px; border-radius:3px; font-weight:bold; }
        .badge-naranja { background: #f39c12; color:#fff; padding:3px 8px; border-radius:3px; font-weight:bold; }
        
        .no-print { text-align: center; margin-bottom: 20px; }
        .btn-print { background: #000; color: #fff; padding: 10px 20px; font-weight: bold; border: none; cursor: pointer; border-radius: 5px; }
        
        @media print {
            .no-print { display: none !important; }
            body { padding: 0; }
        }
    </style>
</head>
<body>

    <div class="no-print">
        <button class="btn-print" onclick="window.print()">🖨️ IMPRIMIR / GUARDAR COMO PDF</button>
        <button onclick="window.close()" style="padding: 10px; margin-left: 10px;">Cerrar</button>
    </div>

    <div class="cabecera">
        <div>
            <?php 
                $logoUrl = !empty($data['config']['logo']['valor']) ? $data['config']['logo']['valor'] : BASE_URL . 'img/default_logo.png';
            ?>
            <img src="<?php echo htmlspecialchars($logoUrl); ?>" class="logo" alt="Logo">
        </div>
        <div class="info-empresa">
            <h2><?php echo htmlspecialchars($data['config']['nombre_botica']['valor']); ?></h2>
            <p>RUC: <?php echo htmlspecialchars($data['config']['ruc']['valor']); ?></p>
            <p><?php echo htmlspecialchars($data['config']['direccion']['valor']); ?></p>
            <p>Tel: <?php echo htmlspecialchars($data['config']['telefono']['valor']); ?></p>
        </div>
    </div>

    <h1 class="titulo-reporte">REPORTE DE REPOSICIÓN: PRODUCTOS BAJO STOCK MÍNIMO <br><small style="font-size:11px; font-weight:normal;">Sugerencia de Compras</small></h1>

    <table>
        <thead>
            <tr>
                <th width="12%">CÓDIGO</th>
                <th width="35%">PRODUCTO / CONCENTRACIÓN</th>
                <th width="18%">LABORATORIO</th>
                <th class="text-center" width="10%">STOCK MÍNIMO</th>
                <th class="text-center" width="10%">STOCK ACTUAL</th>
                <th class="text-center" width="15%">CANTIDAD A PEDIR</th>
            </tr>
        </thead>
        <tbody>
            <?php 
            if(empty($data['productos'])): ?>
                <tr><td colspan="6" style="text-align:center; padding: 20px;">✓ Excelente. Ningún producto se encuentra por debajo de su stock mínimo de seguridad.</td></tr>
            <?php else: ?>
                <?php foreach($data['productos'] as $p): 
                    $faltante = $p['stock_minimo'] - $p['stock_actual'];
                    if ($faltante < 0) $faltante = 0;
                    
                    // Sugerimos pedir el déficit + 50% de margen de seguridad
                    $sugerido = ceil($faltante * 1.5);
                    if ($sugerido == 0) $sugerido = 1; // al menos 1
                ?>
                <tr>
                    <td style="font-family: monospace;"><?php echo htmlspecialchars($p['codigo_barras']); ?></td>
                    <td><strong><?php echo htmlspecialchars($p['nombre_comercial']); ?></strong> <?php echo htmlspecialchars($p['concentracion']); ?></td>
                    <td><?php echo htmlspecialchars($p['laboratorio']); ?></td>
                    <td class="text-center"><?php echo $p['stock_minimo']; ?></td>
                    <td class="text-center">
                        <?php if($p['stock_actual'] <= 0): ?>
                            <span class="badge-rojo">AGOTADO (0)</span>
                        <?php else: ?>
                            <span class="badge-naranja"><?php echo $p['stock_actual']; ?></span>
                        <?php endif; ?>
                    </td>
                    <td class="text-center">
                        <div style="border-bottom: 1px dashed #666; margin: 0 10px; color: #e67e22; font-weight: bold;">
                            Sugerido: <?php echo $sugerido; ?>
                        </div>
                    </td>
                </tr>
                <?php endforeach; ?>
            <?php endif; ?>
        </tbody>
    </table>

    <p style="font-size: 11px; font-style: italic; color: #555;">
        * Este documento sugiere las cantidades de abastecimiento ("Cantidad a Pedir") basándose en el déficit actual para alcanzar el stock mínimo más un colchón de seguridad sugerido del 50%.
    </p>

    <div style="margin-top: 50px; text-align: center; font-size: 10px; color: #999; border-top: 1px solid #eee; padding-top: 10px;">
        Documento generado por el Sistema de Botica el <?php echo date('d/m/Y H:i:s'); ?>. Todos los derechos reservados.
    </div>
</body>
</html>
