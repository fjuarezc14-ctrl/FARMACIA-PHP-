<div class="page-content">
    <?php if (isset($_SESSION['error'])): ?>
        <div class="alert alert-danger mb-4"><i class="bi bi-exclamation-triangle-fill"></i> <?php echo $_SESSION['error']; unset($_SESSION['error']); ?></div>
    <?php endif; ?>
    <?php if (isset($_SESSION['mensaje'])): ?>
        <div class="alert alert-success mb-4"><i class="bi bi-check-circle-fill"></i> <?php echo $_SESSION['mensaje']; unset($_SESSION['mensaje']); ?></div>
    <?php endif; ?>

    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h1 class="page-title">Catálogo de Productos</h1>
            <div class="page-subtitle">Gestiona medicamentos, equipos e insumos de la botica.</div>
        </div>
        <a href="<?php echo BASE_URL; ?>producto/create" class="btn-primary-custom" style="width: auto; padding: 10px 20px; text-decoration: none; display: inline-block;">
            <i class="bi bi-plus-lg"></i> Nuevo Producto
        </a>
    </div>

    <div class="card-metric mb-3 p-3">
        <div class="row align-items-center">
            <div class="col-md-6">
                <div class="search-box w-100">
                    <i class="bi bi-search"></i>
                    <input type="text" id="searchInput" onkeyup="tableSearch()" placeholder="Buscar por código de barras, nombre comercial o genérico...">
                </div>
            </div>
            <div class="col-md-2 offset-md-4 text-end">
                <button class="btn btn-outline-secondary" style="color: var(--text-secondary); border-color: var(--border-color);"><i class="bi bi-filter"></i> Filtros</button>
            </div>
        </div>
    </div>

    <div class="card-metric">
        <div class="table-responsive">
            <table class="table table-hover align-middle" id="productosTable" style="width: 100%; font-size: 14px;">
                <thead>
                    <tr>
                        <th width="120">Código</th>
                        <th>Producto</th>
                        <th>U.M.</th>
                        <th>Precio Venta</th>
                        <th>Stock Disp.</th>
                        <th width="160" class="text-end">Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if(empty($data['productos'])): ?>
                    <tr><td colspan="6" class="text-center text-muted">No hay productos registrados</td></tr>
                    <?php else: ?>
                    <?php foreach($data['productos'] as $prod): ?>
                    <tr>
                        <td style="color:#A0A0A0; font-family:monospace;"><?php echo htmlspecialchars($prod['codigo_barras'] ?? ''); ?></td>
                        <td>
                            <div style="font-weight:700; color:var(--text-primary);"><?php echo htmlspecialchars($prod['nombre_comercial'] ?? ''); ?></div>
                            <div style="font-size:12px; color:var(--text-secondary);">
                                <?php echo htmlspecialchars($prod['nombre_generico'] ?? ''); ?> 
                                <?php echo !empty($prod['concentracion']) ? ' - ' . htmlspecialchars($prod['concentracion']) : ''; ?>
                                <?php if(!empty($prod['registro_sanitario'])): ?>
                                    <span class="ms-2" style="font-size: 11px; background: rgba(0,0,0,0.1); padding: 2px 6px; border-radius: 4px; border: 1px solid var(--border-color); color:var(--text-secondary);">RS: <?php echo htmlspecialchars($prod['registro_sanitario'] ?? ''); ?></span>
                                <?php endif; ?>
                            </div>
                            <div class="mt-1">
                                <?php 
                                $cond = $prod['condicion_venta'] ?? 'Venta Libre';
                                if($cond === 'Receta Médica Retenida') {
                                    echo '<span class="badge bg-danger" style="font-size:10px; font-weight:700;"><i class="bi bi-file-earmark-medical"></i> Receta Retenida</span>';
                                } elseif($cond === 'Receta Médica Simple') {
                                    echo '<span class="badge bg-warning text-dark" style="font-size:10px; font-weight:700;"><i class="bi bi-file-earmark-text"></i> Receta Simple</span>';
                                } else {
                                    echo '<span class="badge bg-success" style="font-size:10px; font-weight:700;"><i class="bi bi-bookmark-fill"></i> Venta Libre</span>';
                                }
                                ?>
                            </div>
                        </td>
                        <td><?php echo htmlspecialchars($prod['unidad_medida'] ?? 'Caja'); ?></td>
                        <td style="font-weight:600; color:var(--accent-primary);">S/ <?php echo number_format($prod['precio_venta'], 2); ?></td>
                        <td>
                            <?php if($prod['stock_actual'] <= $prod['stock_minimo']): ?>
                                <span class="badge-status status-pending"><?php echo $prod['stock_actual']; ?> (Crítico)</span>
                            <?php else: ?>
                                <span class="badge-status status-delivered"><?php echo $prod['stock_actual']; ?> Disp.</span>
                            <?php endif; ?>
                        </td>
                        <td class="text-end">
                            <?php if(isset($prod['estado']) && $prod['estado'] == 0): ?>
                                <span class="badge bg-secondary me-1" style="font-size: 10px;">Inactivo</span>
                                <a href="<?php echo BASE_URL; ?>producto/edit/<?php echo $prod['id']; ?>" class="btn btn-sm" style="color: #00CFE8;" title="Editar">
                                     <i class="bi bi-pencil-square"></i>
                                </a>
                                <form action="<?php echo BASE_URL; ?>producto/toggle" method="POST" class="d-inline" onsubmit="return confirm('¿Seguro que deseas activar este producto para su venta?');">
                                    <?php echo Controller::csrfField(); ?>
                                    <input type="hidden" name="id" value="<?php echo $prod['id']; ?>">
                                    <button type="submit" class="btn btn-sm" style="color: var(--success);" title="Activar Producto">
                                        <i class="bi bi-check-circle-fill"></i>
                                    </button>
                                </form>
                            <?php else: ?>
                                <span class="badge bg-success me-1" style="font-size: 10px;">Activo</span>
                                <a href="<?php echo BASE_URL; ?>producto/edit/<?php echo $prod['id']; ?>" class="btn btn-sm" style="color: #00CFE8;" title="Editar">
                                     <i class="bi bi-pencil-square"></i>
                                </a>
                                <form action="<?php echo BASE_URL; ?>producto/toggle" method="POST" class="d-inline" onsubmit="return confirm('¿Seguro que deseas desactivar este producto? Ya no aparecerá en el POS.');">
                                    <?php echo Controller::csrfField(); ?>
                                    <input type="hidden" name="id" value="<?php echo $prod['id']; ?>">
                                    <button type="submit" class="btn btn-sm" style="color: var(--warning);" title="Desactivar Producto">
                                        <i class="bi bi-x-circle-fill"></i>
                                    </button>
                                </form>
                            <?php endif; ?>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<script>
function tableSearch() {
  var input, filter, table, tr, td, i, txtValue;
  input = document.getElementById("searchInput");
  filter = input.value.toUpperCase();
  table = document.getElementById("productosTable");
  tr = table.getElementsByTagName("tr");
  for (i = 1; i < tr.length; i++) {
    // Buscar en Código (0) o Producto (1)
    var tdCode = tr[i].getElementsByTagName("td")[0];
    var tdName = tr[i].getElementsByTagName("td")[1];
    if (tdCode || tdName) {
      txtValue = (tdCode.textContent || tdCode.innerText) + " " + (tdName.textContent || tdName.innerText);
      if (txtValue.toUpperCase().indexOf(filter) > -1) {
        tr[i].style.display = "";
      } else {
        tr[i].style.display = "none";
      }
    }       
  }
}
</script>
