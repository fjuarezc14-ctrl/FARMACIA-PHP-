<div class="page-content">
    <div class="mb-4">
        <h1 class="page-title">Boletas y Facturas Emitidas</h1>
        <div class="page-subtitle">Historial de tickets y ventas procesadas en caja.</div>
    </div>
    
    <?php if(isset($_SESSION['success'])): ?>
    <div class="alert alert-success alert-dismissible fade show" role="alert">
        <i class="bi bi-check-circle-fill"></i> <?php echo $_SESSION['success']; unset($_SESSION['success']); ?>
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
    <?php endif; ?>
    
    <?php if(isset($_SESSION['error'])): ?>
    <div class="alert alert-danger alert-dismissible fade show" role="alert">
        <i class="bi bi-exclamation-triangle-fill"></i> <?php echo $_SESSION['error']; unset($_SESSION['error']); ?>
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
    <?php endif; ?>

    <div class="card-metric">
        <div class="table-responsive">
            <table class="table table-hover align-middle" style="width: 100%; font-size: 14px;">
                <thead>
                    <tr>
                        <th width="120">Fecha/Hora</th>
                        <th>Cliente</th>
                        <th>Cajero</th>
                        <th class="text-center">Comprobante</th>
                        <th class="text-center">Método Pago</th>
                        <th class="text-center">SUNAT</th>
                        <th class="text-end">Monto Cobrado</th>
                        <th width="100" class="text-center">Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if(empty($data['ventas'])): ?>
                    <tr><td colspan="7" class="text-center text-muted">Aún no hay ventas efectuadas.</td></tr>
                    <?php else: ?>
                    <?php foreach($data['ventas'] as $v): ?>
                    <tr>
                        <td style="color:var(--text-secondary); font-family:monospace;">
                            <?php echo date('d/m/Y', strtotime($v['fecha_venta'])) . "<br><small>" . date('H:i', strtotime($v['fecha_venta'])) . "</small>"; ?>
                        </td>
                        <td style="font-weight:700; color:var(--text-primary); font-size: 13px;">
                            <?php echo htmlspecialchars($v['cliente']); ?>
                        </td>
                        <td style="color:var(--text-secondary); font-size: 13px;">
                            <?php echo explode(' ', $v['cajero'])[0]; ?>
                        </td>
                        <td class="text-center">
                            <?php if ($v['estado'] == 'Anulada'): ?>
                                <span class="badge bg-danger shadow-sm"><i class="bi bi-x-circle"></i> Anulada</span><br>
                                <strike style="font-size:10px; color:var(--text-secondary);"><?php echo htmlspecialchars($v['serie_comprobante'] . '-' . $v['num_comprobante']); ?></strike>
                            <?php else: ?>
                                <span style="border: 1px solid var(--accent-primary); color: var(--accent-primary); padding: 2px 8px; border-radius: 4px; font-size: 11px; font-weight:700;">
                                    <?php echo htmlspecialchars($v['serie_comprobante'] . '-' . $v['num_comprobante']); ?>
                                </span><br>
                                <span style="font-size:10px; color:var(--text-secondary);"><?php echo htmlspecialchars($v['tipo_comprobante']); ?></span>
                            <?php endif; ?>
                        </td>
                        <td class="text-center">
                            <?php 
                                $bColor = 'bg-secondary';
                                if($v['metodo_pago'] == 'Yape' || $v['metodo_pago'] == 'Yape/Plin') $bColor = 'bg-info text-dark';
                                else if($v['metodo_pago'] == 'Tarjeta') $bColor = 'bg-primary';
                                else $bColor = 'bg-success';
                            ?>
                            <span class="badge <?php echo $bColor; ?>"><?php echo $v['metodo_pago']; ?></span>
                        </td>
                        <td class="text-center">
                            <?php
                            $sunat_estado = $v['estado_sunat'] ?? 'N/A';
                            if ($sunat_estado === 'Generado Local'):
                            ?>
                                <span class="badge" style="background: linear-gradient(135deg, #3B82F6, #6D28D9); font-size: 10px;">
                                    <i class="bi bi-file-earmark-check-fill"></i> XML Listo
                                </span>
                            <?php elseif ($v['tipo_comprobante'] === 'Ticket'): ?>
                                <span class="badge bg-secondary" style="font-size:10px;">Interno</span>
                            <?php else: ?>
                                <span class="badge bg-warning text-dark" style="font-size:10px;">Pendiente</span>
                            <?php endif; ?>
                        </td>
                        <td class="text-end" style="font-size: 16px; font-weight:700; color: var(--accent-primary);">
                            S/ <?php echo number_format($v['total'], 2); ?>
                        </td>
                        <td class="text-center">
                            <div class="d-flex gap-1 justify-content-center flex-wrap">
                                <a href="<?php echo BASE_URL; ?>venta/ticket/<?php echo $v['id']; ?>" target="_blank" class="btn btn-sm btn-outline-secondary" title="Ver Ticket (tiquetera)">
                                    <i class="bi bi-printer"></i>
                                </a>
                                <a href="<?php echo BASE_URL; ?>venta/pdf/<?php echo $v['id']; ?>" target="_blank" class="btn btn-sm btn-outline-success" title="Ver / Descargar PDF Comprobante A4">
                                    <i class="bi bi-file-earmark-pdf-fill"></i>
                                </a>
                                <?php
                                // Check if XML file exists for this sale
                                $configVals = $data['config'] ?? [];
                                $ruc_empresa = !empty($configVals['ruc']['valor']) ? $configVals['ruc']['valor'] : '';
                                $tipoDocCode = ($v['tipo_comprobante'] === 'Factura') ? '01' : '03';
                                $xmlFile = "{$ruc_empresa}-{$tipoDocCode}-{$v['serie_comprobante']}-{$v['num_comprobante']}.xml";
                                $xmlPath = (defined('BASE_PATH') ? BASE_PATH : dirname(dirname(dirname(__DIR__))) . DIRECTORY_SEPARATOR) . "public/sunat/xml/{$xmlFile}";
                                if (($v['tipo_comprobante'] === 'Boleta' || $v['tipo_comprobante'] === 'Factura') && file_exists($xmlPath)):
                                ?>
                                <a href="<?php echo BASE_URL; ?>sunat/xml/<?php echo $xmlFile; ?>" download class="btn btn-sm btn-outline-primary" title="Descargar XML SUNAT">
                                    <i class="bi bi-file-earmark-code"></i>
                                </a>
                                <?php endif; ?>
                                <?php if ($v['estado'] != 'Anulada'): ?>
                                <form action="<?php echo BASE_URL; ?>venta/anular" method="POST" class="d-inline" onsubmit="return confirm('¿Está seguro de ANULAR esta venta? El stock se devolverá al almacén de forma íntegra.')">
                                    <?php echo Controller::csrfField(); ?>
                                    <input type="hidden" name="id_venta" value="<?php echo $v['id']; ?>">
                                    <button type="submit" class="btn btn-sm btn-outline-danger" title="Anular Venta">
                                        <i class="bi bi-x-circle"></i>
                                    </button>
                                </form>
                                <?php endif; ?>
                            </div>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>
