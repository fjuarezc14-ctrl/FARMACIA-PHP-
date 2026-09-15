<style>
.compras-table { width: 100%; border-collapse: separate; border-spacing: 0; font-size: 14px; }
.compras-table thead tr { background: linear-gradient(135deg, #1e293b, #0f172a); }
.compras-table thead th {
    padding: 14px 20px;
    font-size: 11px;
    font-weight: 700;
    text-transform: uppercase;
    letter-spacing: 1px;
    color: #94a3b8;
    border-bottom: 2px solid rgba(255,255,255,0.06);
    white-space: nowrap;
}
.compras-table tbody tr {
    border-bottom: 1px solid var(--border-color);
    transition: background 0.2s;
}
.compras-table tbody tr:hover { background: rgba(255,255,255,0.03); }
.compras-table tbody td { padding: 16px 20px; vertical-align: middle; }
.compras-table tbody tr:last-child td { border-bottom: none; }
</style>

<div class="page-content">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h1 class="page-title">Historial de Compras</h1>
            <div class="page-subtitle">Facturas y recepciones de mercadería al almacén.</div>
        </div>
        <a href="<?php echo BASE_URL; ?>compra/create" class="btn-primary-custom" style="width: auto; padding: 10px 20px; text-decoration: none; display: inline-block;">
            <i class="bi bi-cart-plus"></i> Ingresar Mercadería
        </a>
    </div>

    <?php if(isset($_SESSION['mensaje'])): ?>
        <div class="alert alert-success" style="background-color: var(--success-bg); color: var(--accent-primary); border: 1px solid var(--accent-primary);">
            <i class="bi bi-check-circle"></i> <?php echo $_SESSION['mensaje']; unset($_SESSION['mensaje']); ?>
        </div>
    <?php endif; ?>
    <?php if(isset($_SESSION['error'])): ?>
        <div class="alert alert-danger" style="background-color: var(--danger-bg); color: var(--danger); border: 1px solid var(--danger);">
            <i class="bi bi-exclamation-triangle"></i> <?php echo $_SESSION['error']; unset($_SESSION['error']); ?>
        </div>
    <?php endif; ?>

    <div class="card-metric" style="padding: 0; overflow: hidden;">
        <div class="table-responsive">
            <table class="compras-table">
                <thead>
                    <tr>
                        <th style="width:100px;"># Orden</th>
                        <th style="min-width:180px;">Proveedor</th>
                        <th style="min-width:160px;">Comprobante</th>
                        <th style="width:130px;">Fecha Compra</th>
                        <th style="width:80px;">Ítems</th>
                        <th style="width:150px;" class="text-end">Total Compra</th>
                        <th style="width:110px;" class="text-center">Estado</th>
                        <th style="width:180px;" class="text-center">Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if(empty($data['compras'])): ?>
                    <tr><td colspan="8" class="text-center text-muted" style="padding:40px;">No hay compras registradas en el sistema.</td></tr>
                    <?php else: ?>
                    <?php foreach($data['compras'] as $compra): ?>
                    <tr>
                        <!-- # Orden -->
                        <td>
                            <span style="font-family:monospace; font-weight:700; color: var(--accent-primary); font-size:15px;">
                                #<?php echo str_pad($compra['id'], 5, '0', STR_PAD_LEFT); ?>
                            </span><br>
                            <small style="color:var(--text-secondary); font-size:11px;">
                                <i class="bi bi-person-fill"></i>
                                <?php echo htmlspecialchars(explode(' ', $compra['cajero'] ?? '')[0]); ?>
                            </small>
                        </td>

                        <!-- Proveedor -->
                        <td>
                            <div style="display:flex; align-items:center; gap:10px;">
                                <div style="width:38px; height:38px; border-radius:10px; background: linear-gradient(135deg,#3B82F6,#6D28D9); display:flex; align-items:center; justify-content:center; font-size:16px; color:white; flex-shrink:0;">
                                    <i class="bi bi-truck"></i>
                                </div>
                                <div>
                                    <div style="font-weight:700; color:var(--text-primary); font-size:14px;"><?php echo htmlspecialchars($compra['proveedor'] ?? ''); ?></div>
                                    <div style="font-size:11px; color:var(--text-secondary);">Proveedor registrado</div>
                                </div>
                            </div>
                        </td>

                        <!-- Comprobante -->
                        <td>
                            <div style="font-weight:700; color: var(--accent-primary); font-size:13px; margin-bottom:3px;">
                                <?php echo htmlspecialchars($compra['tipo_comprobante'] ?? ''); ?>
                            </div>
                            <div style="font-family:monospace; font-size:12px; background: rgba(255,255,255,0.05); padding: 3px 8px; border-radius:6px; display:inline-block; color:var(--text-secondary); border: 1px solid var(--border-color);">
                                <?php echo htmlspecialchars(($compra['serie_comprobante'] ?? '') . '-' . ($compra['num_comprobante'] ?? '')); ?>
                            </div>
                        </td>

                        <!-- Fecha -->
                        <td>
                            <div style="font-weight:600; color:var(--text-primary);">
                                <?php echo date('d/m/Y', strtotime($compra['fecha_compra'])); ?>
                            </div>
                            <div style="font-size:11px; color:var(--text-secondary);">
                                <?php echo date('H:i', strtotime($compra['fecha_compra'])); ?> hrs
                            </div>
                        </td>

                        <!-- Ítems -->
                        <td>
                            <span style="font-size:13px; font-weight:600; color:var(--text-secondary);">
                                <?php echo $compra['total_items'] ?? '—'; ?>
                            </span>
                        </td>

                        <!-- Total -->
                        <td class="text-end">
                            <div style="font-size:20px; font-weight:800; color: var(--accent-primary); letter-spacing:-0.5px;">
                                S/ <?php echo number_format($compra['total'], 2); ?>
                            </div>
                        </td>

                        <!-- Estado -->
                        <td class="text-center">
                            <?php if($compra['estado'] == 'Pendiente'): ?>
                                <span class="badge" style="background: rgba(245,158,11,0.15); color:#F59E0B; border:1px solid rgba(245,158,11,0.3); padding:7px 12px; border-radius:20px; font-size:12px; font-weight:700;">
                                    <i class="bi bi-clock-history"></i> Pendiente
                                </span>
                            <?php elseif($compra['estado'] == 'Completada'): ?>
                                <span class="badge" style="background: rgba(16,185,129,0.15); color:#10B981; border:1px solid rgba(16,185,129,0.3); padding:7px 12px; border-radius:20px; font-size:12px; font-weight:700;">
                                    <i class="bi bi-check-all"></i> Recibida
                                </span>
                            <?php else: ?>
                                <span class="badge bg-secondary" style="padding:7px 12px; border-radius:20px;"><?php echo $compra['estado']; ?></span>
                            <?php endif; ?>
                        </td>

                        <!-- Acciones -->
                        <td class="text-center">
                            <div class="d-flex gap-2 justify-content-center">
                                <?php if($compra['estado'] == 'Pendiente'): ?>
                                    <a href="<?php echo BASE_URL; ?>compra/recepcion/<?php echo $compra['id']; ?>" class="btn btn-sm btn-success d-flex align-items-center gap-1" title="Registrar Ingreso Físico">
                                        <i class="bi bi-box-seam"></i> Recibir
                                    </a>
                                <?php endif; ?>
                                <a href="<?php echo BASE_URL; ?>compra/devolver/<?php echo $compra['id']; ?>" class="btn btn-sm btn-outline-warning d-flex align-items-center gap-1" title="Devolver a Proveedor">
                                    <i class="bi bi-arrow-return-left"></i> Devolver
                                </a>
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


