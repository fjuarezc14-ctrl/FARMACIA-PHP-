<div class="page-content">
    <div class="mb-4">
        <a href="<?php echo BASE_URL; ?>compra/index" style="color: var(--text-secondary); text-decoration: none; font-size: 14px;">
            <i class="bi bi-arrow-left"></i> Historial de Compras
        </a>
        <h1 class="page-title mt-2"><?php echo htmlspecialchars($data['title']); ?></h1>
    </div>

    <!-- Carga segura de catálogos en JavaScript -->
    <script>
        const productosData = <?php echo json_encode($data['productos'], JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT); ?>;
        const proveedoresData = <?php echo json_encode($data['proveedores'], JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT); ?>;
    </script>

    <form action="<?php echo BASE_URL; ?>compra/save" method="POST" id="formCompra">
        <?php echo Controller::csrfField(); ?>
        <!-- CABECERA -->
        <div class="card-metric mb-4">
            <h5 style="color: var(--text-primary); font-weight: 700; font-size: 16px; margin-bottom: 20px;">Datos del Documento y Proveedor</h5>
            <div class="row g-3">
                <div class="col-md-4 position-relative">
                    <label class="form-label">Proveedor <span class="text-danger">*</span></label>
                    <input type="hidden" name="id_proveedor" id="id_proveedor" required>
                    <div class="input-group">
                        <span class="input-group-text" style="background: var(--bg-card); border-color: var(--border-color); color: var(--text-secondary);">
                            <i class="bi bi-building"></i>
                        </span>
                        <input type="text" class="form-control-custom" id="busc_proveedor" placeholder="Escribe RUC o Razón Social..." autocomplete="off" required style="border-top-left-radius: 0; border-bottom-left-radius: 0;">
                        <button class="btn btn-outline-secondary" type="button" id="btnLimpiarProv" onclick="limpiarProveedor()" style="display: none; border-color: var(--border-color);" title="Cambiar proveedor">
                            <i class="bi bi-x-lg"></i>
                        </button>
                    </div>
                    <div id="sug_proveedor" class="dropdown-menu w-100 shadow-lg" style="display: none; position: absolute; top: 100%; left: 0; z-index: 1050; max-height: 260px; overflow-y: auto; background: var(--bg-card); border: 1px solid var(--border-color);"></div>
                    <small id="provSeleccionadoInfo" class="text-success fw-bold d-block mt-1" style="font-size: 12px; display: none;"></small>
                </div>
                <div class="col-md-2">
                    <label class="form-label">Comprobante <span class="text-danger">*</span></label>
                    <select class="form-control-custom" name="tipo_comprobante" required>
                        <option value="Factura">Factura</option>
                        <option value="Boleta">Boleta</option>
                        <option value="Guia Remision">Guía de Remisión</option>
                    </select>
                </div>
                <div class="col-md-2">
                    <label class="form-label">Serie (F001)</label>
                    <input type="text" class="form-control-custom" name="serie_comprobante" placeholder="Ej: F001" required>
                </div>
                <div class="col-md-2">
                    <label class="form-label">N° de Documento</label>
                    <input type="text" class="form-control-custom" name="num_comprobante" placeholder="Ej: 00012450" required>
                </div>
                <div class="col-md-2">
                    <label class="form-label">Fecha Emisión</label>
                    <input type="date" class="form-control-custom" name="fecha_compra" value="<?php echo date('Y-m-d'); ?>" required>
                </div>
                <div class="col-md-2">
                    <label class="form-label">Estado de la Compra</label>
                    <select class="form-control-custom" name="estado" id="selEstado" onchange="actualizarInfoEstado()" required>
                        <option value="Completada" selected>Completada (Recibida)</option>
                        <option value="Pendiente">Pendiente (Borrador)</option>
                    </select>
                </div>
            </div>
            
            <div class="row mt-3">
                <div class="col-md-12 form-check ms-1">
                    <input type="checkbox" class="form-check-input" id="act_precio" name="actualizar_precio" value="1" checked>
                    <label class="form-check-label" for="act_precio" style="color: var(--accent-primary); font-size: 13px;">
                        Actualizar automáticamente el "Precio Compra" en el catálogo general si cambió para estos productos.
                    </label>
                </div>
            </div>
        </div>

        <!-- DETALLE MULTILINEA -->
        <div class="card-metric mb-4">
            <div class="d-flex justify-content-between align-items-center mb-3">
                <h5 style="color: var(--text-primary); font-weight: 700; font-size: 16px; margin: 0;">Detalle de Productos a Ingresar (Lotes)</h5>
                <button type="button" class="btn btn-sm" style="background-color: var(--success-bg); color: var(--accent-primary); border:none; font-weight: 700; padding: 6px 14px;" onclick="agregarFila()">
                    <i class="bi bi-plus-circle-fill me-1"></i> Añadir Ítem
                </button>
            </div>
            
            <div class="table-responsive" style="overflow-x: visible;">
                <table class="table-custom" id="tablaDetalles" style="min-width: 820px;">
                    <thead>
                        <tr>
                            <th width="32%">Búsqueda de Producto (Nombre o Código)</th>
                            <th width="14%">Lote</th>
                            <th width="14%">Vencimiento</th>
                            <th width="10%">Cant. (Unid)</th>
                            <th width="12%">Precio Comp. (S/)</th>
                            <th width="12%">Subtotal (S/)</th>
                            <th width="6%" class="text-center">Quitar</th>
                        </tr>
                    </thead>
                    <tbody id="tbodyDetalles">
                        <!-- Filas dinamicas se inyectan aqui -->
                    </tbody>
                </table>
            </div>

            <!-- TOTALES -->
            <div class="row mt-4 align-items-center">
                <div class="col-md-8 text-secondary" style="font-size: 13px;">
                    <i class="bi bi-info-circle"></i> <span id="infoEstado">Atención: Esto generará y activará el stock con el respectivo código Lote FEFO inmediatamente.</span>
                </div>
                <div class="col-md-4">
                    <div class="d-flex justify-content-between mb-2">
                        <span style="color: var(--text-secondary); font-size: 14px;">Subtotal gravado:</span>
                        <span style="font-weight: 600;" id="spSubtotalGlobal">S/ 0.00</span>
                    </div>
                    <div class="d-flex justify-content-between mb-3 border-bottom border-secondary pb-2">
                        <span style="color: var(--text-secondary); font-size: 14px;">IGV (Referencial):</span>
                        <span style="font-weight: 600;" id="spIgvGlobal">S/ 0.00</span>
                        <input type="hidden" name="impuesto" id="fiIgv" value="0.00">
                    </div>
                    <div class="d-flex justify-content-between">
                        <span style="color: var(--text-primary); font-size: 18px; font-weight: 700;">Total Compra:</span>
                        <span style="font-weight: 700; font-size: 18px; color: var(--accent-primary);" id="spTotalGlobal">S/ 0.00</span>
                        <input type="hidden" name="total_compra" id="fiTotal" value="0">
                    </div>
                </div>
            </div>
        </div>

        <div class="text-end">
            <button type="submit" class="btn-primary-custom" id="btnSubmit" style="width: 280px; font-size: 16px; padding: 15px;">
                <i class="bi bi-check-circle"></i> Confirmar y Generar Lotes
            </button>
        </div>
    </form>
</div>

<style>
/* Estilos para los dropdowns predictivos en Compras */
.dropdown-item-ceng {
    padding: 10px 14px;
    cursor: pointer;
    border-bottom: 1px solid var(--border-color);
    transition: background 0.15s ease;
}
.dropdown-item-ceng:hover, .dropdown-item-ceng.active {
    background: var(--accent-light, rgba(4, 123, 7, 0.12));
}
.dropdown-item-ceng:last-child {
    border-bottom: none;
}
</style>

<script>
function escHtml(s) {
    if (s === null || s === undefined) return '';
    return String(s)
        .replace(/&/g, '&amp;')
        .replace(/</g, '&lt;')
        .replace(/>/g, '&gt;')
        .replace(/"/g, '&quot;')
        .replace(/'/g, '&#039;');
}

// -------------------------------------------------------------
// BUSCADOR PREDICTIVO DE PROVEEDOR
// -------------------------------------------------------------
const buscProv = document.getElementById('busc_proveedor');
const sugProv = document.getElementById('sug_proveedor');
const idProv = document.getElementById('id_proveedor');
const btnLimpiarProv = document.getElementById('btnLimpiarProv');
const infoProv = document.getElementById('provSeleccionadoInfo');

function filtrarProveedores(q) {
    q = (q || '').toLowerCase().trim();
    if (!q) {
        sugProv.style.display = 'none';
        return;
    }
    const matches = proveedoresData.filter(p => {
        const ruc = (p.ruc || '').toLowerCase();
        const rs = (p.razon_social || '').toLowerCase();
        return ruc.includes(q) || rs.includes(q);
    }).slice(0, 15);

    if (!matches.length) {
        sugProv.innerHTML = '<div class="p-3 text-center text-muted" style="font-size:13px;">No se encontraron proveedores</div>';
        sugProv.style.display = 'block';
        return;
    }

    sugProv.innerHTML = matches.map(p => `
        <div class="dropdown-item-ceng d-flex justify-content-between align-items-center" onmousedown="event.preventDefault(); seleccionarProveedor(${p.id})">
            <div>
                <strong style="color: var(--text-primary); font-size: 13px;">${escHtml(p.razon_social)}</strong>
                <div style="font-size: 11px; color: var(--text-secondary);">
                    <span class="badge bg-secondary me-1 font-monospace">RUC: ${escHtml(p.ruc)}</span>
                    ${p.representante ? '<span>Cont: ' + escHtml(p.representante) + '</span>' : ''}
                </div>
            </div>
            <i class="bi bi-chevron-right text-muted"></i>
        </div>
    `).join('');
    sugProv.style.display = 'block';
}

function seleccionarProveedor(id) {
    const p = proveedoresData.find(x => x.id == id);
    if (!p) return;
    idProv.value = p.id;
    buscProv.value = p.ruc + ' - ' + p.razon_social;
    buscProv.readOnly = true;
    buscProv.style.backgroundColor = 'var(--bg-card)';
    btnLimpiarProv.style.display = 'inline-block';
    sugProv.style.display = 'none';
    infoProv.textContent = '✓ Proveedor seleccionado: ' + p.razon_social;
    infoProv.style.display = 'block';
}

function limpiarProveedor() {
    idProv.value = '';
    buscProv.value = '';
    buscProv.readOnly = false;
    btnLimpiarProv.style.display = 'none';
    infoProv.style.display = 'none';
    sugProv.style.display = 'none';
    buscProv.focus();
}

buscProv.addEventListener('input', function() {
    idProv.value = '';
    filtrarProveedores(this.value);
});
buscProv.addEventListener('focus', function() {
    if (!buscProv.readOnly && this.value.trim()) {
        filtrarProveedores(this.value);
    }
});
buscProv.addEventListener('blur', function() {
    setTimeout(() => { sugProv.style.display = 'none'; }, 200);
});

// -------------------------------------------------------------
// ESTADO DE COMPRA
// -------------------------------------------------------------
function actualizarInfoEstado() {
    let estado = document.getElementById('selEstado').value;
    let info = document.getElementById('infoEstado');
    let btn = document.getElementById('btnSubmit');
    let chkPrecio = document.getElementById('act_precio');

    if (estado === 'Pendiente') {
        info.innerHTML = '<strong>Modo Borrador:</strong> La compra se guardará pero <u>NO se cargará stock</u> ni se crearán lotes hasta que marque la recepción física.';
        btn.innerHTML = '<i class="bi bi-save me-1"></i> Guardar como Pendiente';
        btn.style.background = 'linear-gradient(135deg, #f39c12 0%, #e67e22 100%)';
        chkPrecio.disabled = true;
        chkPrecio.checked = false;
    } else {
        info.innerHTML = 'Atención: Esto generará y activará el stock con el respectivo código Lote FEFO inmediatamente.';
        btn.innerHTML = '<i class="bi bi-check-circle me-1"></i> Confirmar y Generar Lotes';
        btn.style.background = 'var(--accent-primary)';
        chkPrecio.disabled = false;
        chkPrecio.checked = true;
    }
}

// -------------------------------------------------------------
// TABLA DE PRODUCTOS DINÁMICOS CON BUSCADOR PREDICTIVO
// -------------------------------------------------------------
let rowIndex = 0;

function agregarFila() {
    rowIndex++;
    let tr = document.createElement('tr');
    tr.id = 'fila_' + rowIndex;
    tr.innerHTML = `
        <td style="position: relative;">
            <input type="hidden" name="producto_id[]" class="fila-prod-id" required>
            <div class="input-group input-group-sm">
                <span class="input-group-text" style="background: var(--bg-card); border-color: var(--border-color); color: var(--text-secondary);">
                    <i class="bi bi-search"></i>
                </span>
                <input type="text" class="form-control-custom fila-prod-busq" placeholder="Buscar por nombre o código de barras..." autocomplete="off" required oninput="filtrarProductoFila(this, ${rowIndex})" onfocus="abrirListaProductoFila(this, ${rowIndex})" onblur="cerrarListaProductoFila(${rowIndex})">
            </div>
            <div id="sug_prod_${rowIndex}" class="dropdown-menu w-100 shadow-lg" style="display: none; position: absolute; top: 100%; left: 0; z-index: 1060; max-height: 250px; overflow-y: auto; background: var(--bg-card); border: 1px solid var(--border-color);"></div>
        </td>
        <td><input type="text" class="form-control-custom" name="lote[]" placeholder="EJ: L-123" required></td>
        <td><input type="date" class="form-control-custom" name="vencimiento[]" min="<?php echo date('Y-m-d'); ?>" required></td>
        <td><input type="number" class="form-control-custom fila-cant" name="cantidad[]" value="1" min="1" oninput="calcularFila(${rowIndex})" required></td>
        <td><input type="number" class="form-control-custom fila-precio" name="precio_c_unitario[]" step="0.01" value="0.00" oninput="calcularFila(${rowIndex})" required></td>
        <td><input type="number" class="form-control-custom fila-subtotal bg-dark text-white border-0" name="subtotal[]" value="0.00" readonly></td>
        <td class="text-center">
            <button type="button" class="btn btn-sm btn-outline-danger border-0" onclick="quitarFila(${rowIndex})" title="Quitar ítem">
                <i class="bi bi-trash"></i>
            </button>
        </td>
    `;
    document.getElementById('tbodyDetalles').appendChild(tr);

    // Autoenfocar el buscador del nuevo producto
    const nuevoInput = tr.querySelector('.fila-prod-busq');
    if (nuevoInput && rowIndex > 1) {
        nuevoInput.focus();
    }
}

function filtrarProductoFila(input, index) {
    const q = (input.value || '').toLowerCase().trim();
    const sug = document.getElementById('sug_prod_' + index);
    const tr = document.getElementById('fila_' + index);
    const hiddenId = tr.querySelector('.fila-prod-id');
    
    // Invalida la selección previa si se modifica el texto
    hiddenId.value = '';

    if (q.length === 0) {
        sug.innerHTML = '';
        sug.style.display = 'none';
        return;
    }

    const matches = productosData.filter(p => {
        const cb = (p.codigo_barras || '').toLowerCase();
        const nom = (p.nombre_comercial || '').toLowerCase();
        const gen = (p.nombre_generico || '').toLowerCase();
        return cb.includes(q) || nom.includes(q) || gen.includes(q);
    }).slice(0, 20);

    if (!matches.length) {
        sug.innerHTML = '<div class="p-3 text-center text-muted" style="font-size:12px;">No se encontraron productos coincidentes</div>';
        sug.style.display = 'block';
        return;
    }

    sug.innerHTML = matches.map(p => `
        <div class="dropdown-item-ceng d-flex justify-content-between align-items-center" onmousedown="event.preventDefault(); seleccionarProductoData(${index}, ${p.id})">
            <div>
                <strong style="color: var(--text-primary); font-size: 13px;">${escHtml(p.nombre_comercial)}</strong>
                <div style="font-size: 11px; color: var(--text-secondary);">
                    ${p.codigo_barras ? '<span class="font-monospace text-info me-2">' + escHtml(p.codigo_barras) + '</span>' : ''}
                    <span>${escHtml(p.laboratorio || '')}</span>
                    <span class="badge bg-secondary ms-1" style="font-size: 10px;">${escHtml(p.unidad_medida || 'Unidad')}</span>
                </div>
            </div>
            <div class="text-end ps-2">
                <small class="text-muted d-block" style="font-size:10px;">Costo Sug.</small>
                <strong style="color: var(--accent-primary); font-size: 12px;">S/ ${parseFloat(p.precio_compra || 0).toFixed(2)}</strong>
            </div>
        </div>
    `).join('');
    sug.style.display = 'block';
}

function abrirListaProductoFila(input, index) {
    if (input.value.trim().length > 0) {
        filtrarProductoFila(input, index);
    }
}

function cerrarListaProductoFila(index) {
    setTimeout(() => {
        const sug = document.getElementById('sug_prod_' + index);
        if (sug) sug.style.display = 'none';
    }, 250);
}

function seleccionarProductoData(index, id) {
    const p = productosData.find(x => x.id == id);
    if (!p) return;

    const tr = document.getElementById('fila_' + index);
    if (!tr) return;

    const hiddenId = tr.querySelector('.fila-prod-id');
    const inputBusq = tr.querySelector('.fila-prod-busq');
    const sug = document.getElementById('sug_prod_' + index);
    const precioInput = tr.querySelector('.fila-precio');
    const loteInput = tr.querySelector('input[name="lote[]"]');

    hiddenId.value = p.id;
    inputBusq.value = (p.codigo_barras ? p.codigo_barras + ' - ' : '') + p.nombre_comercial + ' (' + (p.unidad_medida || 'Unidad') + ')';
    sug.style.display = 'none';

    // Autollenar costo de compra de referencia
    precioInput.value = parseFloat(p.precio_compra || 0).toFixed(2);
    calcularFila(index);

    // Mover foco al campo Lote
    if (loteInput) loteInput.focus();
}

function calcularFila(index) {
    let tr = document.getElementById('fila_' + index);
    if (tr) {
        let cant = parseFloat(tr.querySelector('.fila-cant').value) || 0;
        let precio = parseFloat(tr.querySelector('.fila-precio').value) || 0;
        let subtotal = cant * precio;
        tr.querySelector('.fila-subtotal').value = subtotal.toFixed(2);
        calcularTotales();
    }
}

function quitarFila(index) {
    let tr = document.getElementById('fila_' + index);
    if (tr) {
        tr.remove();
        calcularTotales();
    }
}

function calcularTotales() {
    let sum = 0;
    document.querySelectorAll('.fila-subtotal').forEach(input => {
        sum += parseFloat(input.value) || 0;
    });
    
    let totalC = sum;
    let igvC = sum - (sum / 1.18);
    let subNeto = sum - igvC;

    document.getElementById('spSubtotalGlobal').innerText = 'S/ ' + subNeto.toFixed(2);
    document.getElementById('spIgvGlobal').innerText = 'S/ ' + igvC.toFixed(2);
    document.getElementById('spTotalGlobal').innerText = 'S/ ' + totalC.toFixed(2);
    
    document.getElementById('fiIgv').value = igvC.toFixed(2);
    document.getElementById('fiTotal').value = totalC.toFixed(2);
}

// Validación previa al envío
document.getElementById('formCompra').addEventListener('submit', function(e) {
    if (!idProv.value) {
        e.preventDefault();
        alert('⚠️ Por favor seleccione un Proveedor válido de la lista predictiva.');
        buscProv.focus();
        return false;
    }

    const filas = document.querySelectorAll('#tbodyDetalles tr');
    if (!filas.length) {
        e.preventDefault();
        alert('⚠️ Debe agregar al menos un producto a la compra.');
        agregarFila();
        return false;
    }

    for (let i = 0; i < filas.length; i++) {
        const fila = filas[i];
        const prodId = fila.querySelector('.fila-prod-id').value;
        const busqInput = fila.querySelector('.fila-prod-busq');
        if (!prodId) {
            e.preventDefault();
            alert('⚠️ Debe seleccionar un producto de la lista en la fila ' + (i + 1) + '.');
            busqInput.focus();
            return false;
        }
    }
});

// Iniciar con 1 fila
window.onload = function() {
    agregarFila();
};
</script>
