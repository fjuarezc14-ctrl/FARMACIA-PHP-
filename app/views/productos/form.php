    <?php $p = $data['producto']; ?>

    <div class="d-flex justify-content-between align-items-center mb-4 flex-wrap gap-3">
        <div>
            <a href="<?php echo BASE_URL; ?>producto/index" style="color: var(--text-secondary); text-decoration: none; font-size: 14px;">
                <i class="bi bi-arrow-left"></i> Volver al listado
            </a>
            <h1 class="page-title mt-2"><?php echo htmlspecialchars($data['title']); ?></h1>
        </div>

        <?php if($p): 
            $stockActual = (int)($p['stock_actual'] ?? 0);
            $stockMinimo = (int)($p['stock_minimo'] ?? 10);
            $esCritico = $stockActual <= $stockMinimo;
            $esAgotado = $stockActual <= 0;
        ?>
        <div class="p-3 d-flex align-items-center gap-3 border <?php echo $esAgotado ? 'border-danger' : ($esCritico ? 'border-warning' : 'border-success'); ?>" style="background: rgba(255,255,255,0.03); border-radius: 12px;">
            <div style="font-size: 28px; color: <?php echo $esAgotado ? '#ef4444' : ($esCritico ? '#f59e0b' : '#10b981'); ?>;">
                <i class="bi <?php echo $esAgotado ? 'bi-x-octagon-fill' : ($esCritico ? 'bi-exclamation-triangle-fill' : 'bi-boxes'); ?>"></i>
            </div>
            <div>
                <div class="text-muted small fw-bold" style="letter-spacing: 0.5px;">STOCK FÍSICO EN ALMACÉN</div>
                <div class="d-flex align-items-baseline gap-2">
                    <span class="fs-4 fw-bold text-white"><?php echo $stockActual; ?> unidades</span>
                    <span class="badge <?php echo $esAgotado ? 'bg-danger' : ($esCritico ? 'bg-warning text-dark' : 'bg-success'); ?>">
                        <?php echo $esAgotado ? 'Agotado (0)' : ($esCritico ? 'Bajo Stock Mínimo' : 'Stock Disponible'); ?>
                    </span>
                </div>
            </div>
        </div>
        <?php endif; ?>
    </div>

    <form action="<?php echo BASE_URL; ?>producto/save" method="POST">
        <?php echo Controller::csrfField(); ?>
        <input type="hidden" name="id" value="<?php echo $p ? $p['id'] : ''; ?>">
        
        <div class="row g-4">
            <!-- Izquierda: Datos Principales -->
            <div class="col-md-8">
                <div class="card-metric">
                    <h5 style="color: var(--text-primary); font-weight: 700; font-size: 16px; margin-bottom: 20px;">
                        <i class="bi bi-info-circle text-primary me-2"></i> Información Comercial
                    </h5>
                    
                    <div class="row g-3">
                        <div class="col-md-8 form-group">
                            <label class="form-label">Nombre Comercial del Producto <span class="text-danger">*</span></label>
                            <input type="text" class="form-control-custom" name="nombre_comercial" id="nombre_comercial" value="<?php echo $p ? htmlspecialchars($p['nombre_comercial']) : ''; ?>" placeholder="Ej: Panadol Antigripal, Amoxicilina, etc." required>
                        </div>
                        <div class="col-md-4 form-group">
                            <label class="form-label">Código de Barras</label>
                            <input type="text" class="form-control-custom font-monospace" name="codigo_barras" value="<?php echo $p ? htmlspecialchars($p['codigo_barras']) : ''; ?>" placeholder="Escanear o digitar...">
                        </div>
                        
                        <div class="col-md-6 form-group">
                            <label class="form-label">Categoría</label>
                            <select class="form-control-custom" name="id_categoria">
                                <option value="">Seleccionar Categoría...</option>
                                <?php foreach($data['categorias'] as $cat): 
                                    $sel = ($p && $p['id_categoria'] == $cat['id']) ? 'selected' : '';
                                ?>
                                <option value="<?php echo $cat['id']; ?>" <?php echo $sel; ?>><?php echo htmlspecialchars($cat['nombre']); ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>

                        <div class="col-md-6 form-group">
                            <label class="form-label">Laboratorio / Marca</label>
                            <select class="form-control-custom" name="id_laboratorio">
                                <option value="">Seleccionar Laboratorio...</option>
                                <?php foreach($data['laboratorios'] as $lab): 
                                    $sel = ($p && $p['id_laboratorio'] == $lab['id']) ? 'selected' : '';
                                ?>
                                <option value="<?php echo $lab['id']; ?>" <?php echo $sel; ?>><?php echo htmlspecialchars($lab['nombre']); ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>

                        <div class="col-md-6 form-group">
                            <label class="form-label">Unidad de Medida</label>
                            <select class="form-control-custom" name="unidad_medida">
                                <?php 
                                $ums = ['Unidad', 'Caja', 'Blister', 'Frasco', 'Tubo'];
                                foreach($ums as $u): 
                                    $sel = ($p && $p['unidad_medida'] == $u) ? 'selected' : '';
                                ?>
                                <option value="<?php echo $u; ?>" <?php echo $sel; ?>><?php echo $u; ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>

                        <div class="col-md-6 form-group">
                            <label class="form-label">Condición de Venta</label>
                            <select class="form-control-custom" name="condicion_venta" id="condicion_venta">
                                <?php 
                                $conds = ['Venta Libre', 'Receta Médica Simple', 'Receta Médica Retenida'];
                                foreach($conds as $c): 
                                    $sel = ($p && isset($p['condicion_venta']) && $p['condicion_venta'] == $c) ? 'selected' : '';
                                    if(!$p && $c == 'Venta Libre') $sel = 'selected';
                                ?>
                                <option value="<?php echo $c; ?>" <?php echo $sel; ?>><?php echo $c; ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        
                        <div class="col-md-12 form-group" id="alertaReceta" style="display:none;">
                            <div class="alert alert-danger mb-0 p-2 d-flex align-items-center gap-2" style="background: rgba(220, 53, 69, 0.1); border: 1px solid rgba(220, 53, 69, 0.3); color: var(--danger); font-size: 13px; border-radius: 8px;">
                                <i class="bi bi-exclamation-triangle-fill"></i>
                                <span id="textoAlertaReceta">Atención: Este producto requerirá obligatoriamente el código CMP del médico al venderse en el POS.</span>
                            </div>
                        </div>
                    </div>

                    <?php 
                    $tieneDatosFarm = ($p && (!empty($p['concentracion']) || !empty($p['registro_sanitario']) || !empty($p['codigo_prin_activo']) || (!empty($p['forma_farmaceutica']))));
                    ?>

                    <!-- SECCIÓN PLEGABLE OPCIONAL: DATOS TÉCNICOS FARMACÉUTICOS (DIGEMID) -->
                    <div class="mt-4 pt-3 border-top border-secondary border-opacity-25">
                        <button class="btn btn-sm btn-outline-secondary w-100 d-flex justify-content-between align-items-center py-2 px-3 text-white" type="button" data-bs-toggle="collapse" data-bs-target="#secFarmaceutica" aria-expanded="<?php echo $tieneDatosFarm ? 'true' : 'false'; ?>" style="border-radius: 8px; background: rgba(255,255,255,0.02);">
                            <span class="d-flex align-items-center gap-2">
                                <i class="bi bi-capsule text-info"></i>
                                <strong>Datos Farmacéuticos DIGEMID (Opcional)</strong>
                                <?php if($tieneDatosFarm): ?>
                                    <span class="badge bg-info text-dark" style="font-size: 10px;">Cargados</span>
                                <?php endif; ?>
                            </span>
                            <span class="text-muted small">Concentración, Genérico, Registro Sanitario <i class="bi bi-chevron-down ms-1"></i></span>
                        </button>

                        <div class="collapse <?php echo $tieneDatosFarm ? 'show' : ''; ?> mt-3" id="secFarmaceutica">
                            <div class="p-3 rounded-3" style="background: rgba(0,0,0,0.15); border: 1px solid var(--border-color);">
                                <div class="row g-3">
                                    <div class="col-md-6 form-group">
                                        <label class="form-label">Concentración <small class="text-muted">(Dosis / Fuerza)</small></label>
                                        <input type="text" class="form-control-custom" name="concentracion" value="<?php echo $p ? htmlspecialchars($p['concentracion'] ?? '') : ''; ?>" placeholder="Ej: 500mg, 1g, 250mg/5ml">
                                        <small style="color:var(--text-secondary); font-size: 11px;">Opcional: Para diferenciar tabletas, jarabes o dosis.</small>
                                    </div>

                                    <div class="col-md-6 form-group">
                                        <label class="form-label">Nombre Genérico <small class="text-muted">(Principio Activo)</small></label>
                                        <input type="text" class="form-control-custom" name="nombre_generico" id="nombre_generico" value="<?php echo $p ? htmlspecialchars($p['nombre_generico'] ?? '') : ''; ?>" placeholder="Ej: Paracetamol (o igual al comercial)">
                                    </div>

                                    <div class="col-md-4 form-group">
                                        <label class="form-label">Forma Farmacéutica</label>
                                        <select class="form-control-custom" name="forma_farmaceutica">
                                            <option value="">Seleccionar...</option>
                                            <?php 
                                            $formas = ['Tableta', 'Cápsula', 'Jarabe', 'Suspensión', 'Ampolla', 'Crema', 'Gel', 'Inyectable', 'Gotas'];
                                            foreach($formas as $f): 
                                                $sel = ($p && ($p['forma_farmaceutica'] ?? '') == $f) ? 'selected' : '';
                                            ?>
                                            <option value="<?php echo $f; ?>" <?php echo $sel; ?>><?php echo $f; ?></option>
                                            <?php endforeach; ?>
                                        </select>
                                    </div>

                                    <div class="col-md-4 form-group">
                                        <label class="form-label">Registro Sanitario (DIGEMID)</label>
                                        <input type="text" class="form-control-custom" name="registro_sanitario" value="<?php echo ($p && isset($p['registro_sanitario'])) ? htmlspecialchars($p['registro_sanitario']) : ''; ?>" placeholder="Ej: N-24536-PER o EE-12345">
                                    </div>

                                    <div class="col-md-4 form-group">
                                        <label class="form-label">Cód. Principio Activo</label>
                                        <input type="number" class="form-control-custom" name="codigo_prin_activo" value="<?php echo ($p && isset($p['codigo_prin_activo'])) ? htmlspecialchars($p['codigo_prin_activo']) : ''; ?>" placeholder="Ej: 530, 81">
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Fraccionamiento Inteligente -->
                <div class="card-metric mt-4">
                    <h5 style="color: var(--text-primary); font-weight: 700; font-size: 16px; margin-bottom: 20px;">
                        <i class="bi bi-box-seam"></i> Venta Fraccionada
                    </h5>
                    <div class="form-group form-check mb-3">
                        <input type="checkbox" class="form-check-input" id="fraccionable" name="fraccionable" value="1" <?php echo ($p && isset($p['fraccionable']) && $p['fraccionable'] == 1) ? 'checked' : ''; ?>>
                        <label class="form-check-label" style="color:var(--text-primary);" for="fraccionable">Este producto se vende por fracciones (Ej. por Blíster, por Pastilla)</label>
                    </div>
                    
                    <div class="row g-3" id="fraccion_config" style="display: none; background: rgba(0,0,0,0.2); padding: 15px; border-radius: 10px; border: 1px solid var(--border-color);">
                        <div class="col-md-4 form-group">
                            <label class="form-label">Unidades por Caja <span class="text-danger">*</span></label>
                            <input type="number" class="form-control-custom" name="unidades_por_caja" id="uCaja" value="<?php echo ($p && isset($p['unidades_por_caja'])) ? $p['unidades_por_caja'] : '1'; ?>" min="1">
                            <small style="color:var(--text-secondary); font-size: 11px;">Ej: 100 pastillas en la caja.</small>
                        </div>
                        <div class="col-md-4 form-group">
                            <label class="form-label">Nombre Fracción <span class="text-danger">*</span></label>
                            <select class="form-control-custom" name="unidad_fraccion">
                                <option value="">Seleccionar...</option>
                                <?php 
                                $ufracs = ['Pastilla', 'Sobre', 'Ampolla', 'Blister'];
                                foreach($ufracs as $uf): 
                                    $sel = ($p && isset($p['unidad_fraccion']) && $p['unidad_fraccion'] == $uf) ? 'selected' : '';
                                ?>
                                <option value="<?php echo $uf; ?>" <?php echo $sel; ?>><?php echo $uf; ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="col-md-4 form-group">
                            <div class="d-flex justify-content-between align-items-center mb-1">
                                <label class="form-label mb-0">Precio x Fracción (S/)</label>
                                <button type="button" class="btn btn-link p-0 text-warning text-decoration-none" style="font-size: 11px;" onclick="sugerirPrecioFraccion(true)" title="Calcular automáticamente precio proporcional">
                                    <i class="bi bi-magic"></i> Sugerir
                                </button>
                            </div>
                            <input type="number" step="0.01" min="0" class="form-control-custom text-warning font-weight-bold" name="precio_fraccion" id="pFraccion" value="<?php echo ($p && isset($p['precio_fraccion'])) ? $p['precio_fraccion'] : '0.00'; ?>">
                            <small style="color:var(--text-secondary); font-size: 11px;">Calculado proporcional según unidades por caja.</small>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Derecha: Finanzas e Inventario -->
            <div class="col-md-4">
                <div class="card-metric mb-4">
                    <h5 style="color: var(--text-primary); font-weight: 700; font-size: 16px; margin-bottom: 20px;">Precios y Márgenes</h5>
                    
                    <div class="form-group">
                        <label class="form-label">Precio Compra (S/)</label>
                        <input type="number" step="0.01" min="0" class="form-control-custom calc-in" name="precio_compra" id="pCompra" value="<?php echo $p ? $p['precio_compra'] : '0.00'; ?>" required>
                    </div>
                    <div class="form-group">
                        <label class="form-label">Margen de Ganancia (%)</label>
                        <div class="input-group" style="border-radius:10px; overflow:hidden;">
                            <input type="number" step="0.01" class="form-control-custom calc-in" name="margen_ganancia" id="pMargen" value="<?php echo $p ? $p['margen_ganancia'] : '40.00'; ?>" required style="border-radius: 10px 0 0 10px;">
                            <span class="input-group-text" style="background-color: var(--border-color); border:none; color:#fff;">%</span>
                        </div>
                    </div>
                    <div class="form-group mb-3">
                        <label class="form-label" style="color: var(--accent-primary);">Precio Venta Sugerido (PVP1 - S/)</label>
                        <input type="number" step="0.01" min="0" class="form-control-custom" name="precio_venta" id="pVenta" value="<?php echo $p ? $p['precio_venta'] : '0.00'; ?>" style="border-color: var(--accent-primary); font-size: 18px; font-weight: 700;" required>
                        
                        <!-- ALERTA DE VENTA A PÉRDIDA -->
                        <div id="alertaPerdida" class="alert alert-danger p-2 mt-2 mb-0 d-flex align-items-center gap-2" style="display:none; font-size: 12px; border-radius: 8px;">
                            <i class="bi bi-exclamation-triangle-fill"></i>
                            <span id="textoAlertaPerdida"></span>
                        </div>
                    </div>
                    <div class="form-group mb-0">
                        <label class="form-label" style="color: #3B82F6;">Precio Venta Mayorista (PVP2 - S/)</label>
                        <input type="number" step="0.01" min="0" class="form-control-custom" name="precio_mayor" id="pMayor" value="<?php echo ($p && isset($p['precio_mayor']) && $p['precio_mayor'] !== null) ? $p['precio_mayor'] : ''; ?>" placeholder="Opcional (Ej: 99.50)">
                        <small style="color:var(--text-secondary); font-size: 11px;">Precio especial por volumen / mayorista.</small>
                    </div>
                </div>

                <div class="card-metric">
                    <h5 style="color: var(--text-primary); font-weight: 700; font-size: 16px; margin-bottom: 20px;">Configuración de Stock</h5>
                    <div class="form-group">
                        <label class="form-label">Alerta de Stock Mínimo</label>
                        <input type="number" class="form-control-custom" name="stock_minimo" value="<?php echo $p ? $p['stock_minimo'] : '10'; ?>" required>
                        <small style="color:var(--text-secondary); font-size: 11px;">El sistema avisará cuando llegue a esta cantidad.</small>
                    </div>
                </div>
                
                <button type="submit" class="btn-primary-custom mt-4 d-flex justify-content-center align-items-center gap-2">
                    <i class="bi bi-save"></i> Guardar Producto
                </button>
            </div>
        </div>
    </form>
</div>

<script>
// Algoritmo de cálculo de margen (como solicitado por el usuario)
const cCompra = document.getElementById('pCompra');
const cMargen = document.getElementById('pMargen');
const cVenta = document.getElementById('pVenta');

function calcVenta() {
    let compra = parseFloat(cCompra.value) || 0;
    let margen = parseFloat(cMargen.value) || 0;
    let venta = compra + (compra * (margen / 100));
    cVenta.value = venta.toFixed(2);
}

function calcMargen() {
    let compra = parseFloat(cCompra.value) || 0;
    let venta = parseFloat(cVenta.value) || 0;
    if (compra > 0) {
        let margen = ((venta / compra) - 1) * 100;
        cMargen.value = margen.toFixed(2);
    }
}

// Eventos
cCompra.addEventListener('input', () => { calcVenta(); verificarPerdida(); });
cMargen.addEventListener('input', () => { calcVenta(); verificarPerdida(); });
cVenta.addEventListener('input', () => { calcMargen(); verificarPerdida(); sugerirPrecioFraccion(false); });

function verificarPerdida() {
    let compra = parseFloat(cCompra.value) || 0;
    let venta = parseFloat(cVenta.value) || 0;
    let alerta = document.getElementById('alertaPerdida');
    let texto = document.getElementById('textoAlertaPerdida');
    if (compra > 0 && venta > 0 && venta < compra) {
        let perdida = (compra - venta).toFixed(2);
        texto.innerHTML = `<strong>⚠️ Venta a Pérdida:</strong> El precio de venta (S/ ${venta.toFixed(2)}) es menor al costo (S/ ${compra.toFixed(2)}). Pierdes S/ ${perdida} por unidad.`;
        alerta.style.display = 'flex';
    } else {
        alerta.style.display = 'none';
    }
}
verificarPerdida(); // init

// Autocálculo de precio por fracción
const uCajaInput = document.getElementById('uCaja');
const pFracInput = document.getElementById('pFraccion');

function sugerirPrecioFraccion(forzar = false) {
    const venta = parseFloat(cVenta.value) || 0;
    const uCaja = parseInt(uCajaInput.value) || 1;
    if (venta > 0 && uCaja > 0) {
        const prop = (venta / uCaja).toFixed(2);
        const actual = parseFloat(pFracInput.value) || 0;
        if (forzar || actual <= 0) {
            pFracInput.value = prop;
        }
    }
}

uCajaInput.addEventListener('input', () => sugerirPrecioFraccion(false));

// Fraccionamiento toggle
const chkFraccion = document.getElementById('fraccionable');
const panelFraccion = document.getElementById('fraccion_config');
function toggleFraccion() {
    if(chkFraccion.checked) {
        panelFraccion.style.display = 'flex';
        sugerirPrecioFraccion(false);
    } else {
        panelFraccion.style.display = 'none';
        document.getElementById('uCaja').value = '1';
    }
}
chkFraccion.addEventListener('change', toggleFraccion);
toggleFraccion(); // init

// Receta toggle alert
const selCondicion = document.getElementById('condicion_venta');
const alertaReceta = document.getElementById('alertaReceta');
const textoAlertaReceta = document.getElementById('textoAlertaReceta');
function mostrarMensajeReceta() {
    let cond = selCondicion.value;
    if(cond === 'Receta Médica Simple') {
        alertaReceta.style.display = 'block';
        textoAlertaReceta.innerText = 'Atención: Esta condición requerirá obligatoriamente ingresar el código CMP del médico en el POS.';
    } else if(cond === 'Receta Médica Retenida') {
        alertaReceta.style.display = 'block';
        textoAlertaReceta.innerHTML = '<strong>CRÍTICO (RECETA RETENIDA):</strong> Exigirá CMP del médico en el POS y requerirá retener la receta física para archivarla en el Libro de Control Regulatorio.';
    } else {
        alertaReceta.style.display = 'none';
    }
}
selCondicion.addEventListener('change', mostrarMensajeReceta);
mostrarMensajeReceta(); // init

// Validaciones al enviar formulario
document.querySelector('form').addEventListener('submit', function(e) {
    const com = document.getElementById('nombre_comercial');
    const gen = document.getElementById('nombre_generico');
    if (gen && (!gen.value || gen.value.trim() === '') && com && com.value) {
        gen.value = com.value.trim();
    }

    // Si fraccionable está activo y el precio de fracción es 0, forzar el proporcional
    if (chkFraccion.checked) {
        let pf = parseFloat(pFracInput.value) || 0;
        if (pf <= 0) {
            sugerirPrecioFraccion(true);
        }
    }

    // Advertencia interactiva si venta es menor a costo
    let compra = parseFloat(cCompra.value) || 0;
    let venta = parseFloat(cVenta.value) || 0;
    if (compra > 0 && venta > 0 && venta < compra) {
        if (!confirm(`⚠️ ALERTA FINANCIERA: El precio de venta (S/ ${venta.toFixed(2)}) es MENOR al costo de compra (S/ ${compra.toFixed(2)}).\n\n¿Estás completamente seguro de que deseas guardar este producto vendiéndolo a pérdida?`)) {
            e.preventDefault();
            return false;
        }
    }
});
</script>
