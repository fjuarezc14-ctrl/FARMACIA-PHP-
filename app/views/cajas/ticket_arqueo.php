<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Ticket de Arqueo #<?php echo $caja['id']; ?></title>
    <style>
        @page { margin: 0; }
        body { margin: 0; padding: 10px; font-family: 'Courier New', Courier, monospace; font-size: 12px; width: 300px; }
        .center { text-align: center; }
        .bold { font-weight: bold; }
        .line { border-bottom: 1px dashed #000; margin: 10px 0; }
        .row { display: flex; justify-content: space-between; margin-bottom: 5px; }
        @media print { body { width: 100%; margin: 0; padding: 0; padding-right:15px; } }
    </style>
</head>
<body onload="window.print()">

<div class="center bold" style="font-size: 16px; margin-bottom:5px;">CENGFARMA</div>
<div class="center">TICKET DE ARQUEO CAJA</div>
<div class="center bold" style="font-size: 14px; margin-top:5px;">#CAJ-<?php echo str_pad($caja['id'], 6, '0', STR_PAD_LEFT); ?></div>
<div class="line"></div>

<div class="row"><span>Cajero:</span> <span class="bold"><?php echo htmlspecialchars(($caja['nombres'] ?? '') . ' ' . ($caja['apellidos'] ?? '')); ?></span></div>
<div class="row"><span>Apertura:</span> <span><?php echo !empty($caja['fecha_apertura']) ? date('d/m/Y H:i', strtotime($caja['fecha_apertura'])) : '-'; ?></span></div>
<div class="row"><span>Cierre:</span> <span><?php echo !empty($caja['fecha_cierre']) ? date('d/m/Y H:i', strtotime($caja['fecha_cierre'])) : 'Abierta / En Curso'; ?></span></div>

<div class="line"></div>
<div class="center bold" style="margin-bottom: 5px;">SISTEMA / ESPERADO</div>

<div class="row"><span>Saldo Inicial:</span> <span>S/ <?php echo number_format((float)($caja['monto_inicial'] ?? 0), 2); ?></span></div>
<div class="row"><span>Ventas Efectivo:</span> <span>S/ <?php echo number_format((float)($caja['ingresos_efectivo'] ?? 0), 2); ?></span></div>
<div class="row"><span>Ventas Trans/Tarj:</span> <span>S/ <?php echo number_format((float)($caja['ingresos_transferencia'] ?? 0), 2); ?></span></div>
<div class="row bold"><span>EFECTIVO ESPERADO:</span> <span>S/ <?php echo number_format((float)($caja['monto_final_esperado'] ?? 0), 2); ?></span></div>

<div class="line"></div>
<div class="center bold" style="margin-bottom: 5px;">DECLARADO / REAL</div>

<div class="row bold text-lg"><span>EFECTIVO CONTADO:</span> <span>S/ <?php echo number_format((float)($caja['monto_final_real'] ?? 0), 2); ?></span></div>

<?php 
$diferencia = (float)($caja['diferencia'] ?? 0);
if($diferencia != 0): 
?>
    <div class="row bold" style="margin-top:5px;">
        <span><?php echo $diferencia > 0 ? "SOBRANTE:" : "FALTANTE:"; ?></span> 
        <span>S/ <?php echo number_format(abs($diferencia), 2); ?></span>
    </div>
<?php else: ?>
    <div class="row bold" style="margin-top:5px; text-align:center; width:100%">
        <span>CUADRE PERFECTO</span>
    </div>
<?php endif; ?>

<?php if(!empty($caja['observacion'])): ?>
    <div class="line"></div>
    <div><span class="bold">Obs:</span> <?php echo htmlspecialchars($caja['observacion']); ?></div>
<?php endif; ?>

<div class="line" style="margin-top: 50px;"></div>
<div class="center">Firma de Administrador</div>

<div class="line" style="margin-top: 50px;"></div>
<div class="center">Firma de Cajero</div>

<div class="center" style="margin-top: 20px; font-size:10px;">Impreso: <?php echo date('d/m/Y H:i:s'); ?></div>

</body>
</html>
