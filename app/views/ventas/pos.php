<style>
/* Estilos extra para la experiencia POS full screen */
body { overflow-x: hidden; }
.pos-layout { display: flex; height: calc(100vh - 80px); gap: 20px; }
.pos-left { flex: 0 0 65%; display: flex; flex-direction: column; }
.pos-right { flex: 0 0 calc(35% - 20px); display: flex; flex-direction: column; }
.pos-cart { flex-grow: 1; overflow-y: auto; background: var(--bg-card); border-radius: 12px; border: 1px solid var(--border-color); padding: 15px;}
.pos-totals { background: var(--bg-card); border-radius: 12px; border: 1px solid var(--border-color); padding: 20px; margin-top: 15px; }

/* Carrito Table */
.tr-cart { border-bottom: 1px solid rgba(255,255,255,0.05); }
.tr-cart td { padding: 12px 5px; vertical-align: middle; }
.qty-btn { background: #e9ecef; color: #222; border: 1px solid #ced4da; border-radius: 5px; width: 30px; height: 30px; display: inline-flex; justify-content: center; align-items: center; cursor: pointer; font-weight: bold; font-size: 18px; }
.qty-btn:hover { background: var(--accent-primary); color: #fff; border-color: var(--accent-primary); }
.qty-input { width: 50px; text-align: center; background: transparent; border: none; color: #222; font-weight: bold;}

/* Pay Button */
.btn-pay { background: linear-gradient(135deg, var(--accent-primary) 0%, #1FA95B 100%); width: 100%; color: #ffffff !important; font-weight: 800; font-size: 22px; padding: 20px; border-radius: 12px; border: none; cursor: pointer; transition: transform 0.2s; text-shadow: 0 1px 2px rgba(0,0,0,0.3); }
/* Catalogo Buscar */
.pos-catalog { flex-grow: 1; overflow-y: auto; background: var(--bg-card); border-radius: 12px; border: 1px solid var(--border-color); padding: 15px; }
.item-card { background: rgba(0,0,0,0.02); border: 1px solid var(--border-color); border-radius: 8px; padding: 12px; margin-bottom: 10px; cursor: pointer; transition: all 0.2s; display: flex; justify-content: space-between; align-items: center; }
.item-card:hover { border-color: var(--accent-primary); background: rgba(40, 199, 111, 0.05); transform: translateX(2px); }
.item-card.disabled { opacity: 0.5; pointer-events: none; }

/* Responsive layout */
@media (max-width: 991px) {
    .pos-layout { flex-direction: column; height: auto; }
    .pos-left, .pos-right { flex: 0 0 100%; width: 100%; }
    .pos-mobile-tabs { display: flex !important; }
    .pos-panel { display: none; }
    .pos-panel.active { display: flex !important; }
}
@media (min-width: 992px) {
    .pos-mobile-tabs { display: none !important; }
    .pos-panel { display: flex !important; }
}
.pos-tab-btn { flex: 1; padding: 10px; font-weight: 700; border: 1px solid var(--border-color); background: var(--bg-card); color: var(--text-secondary); border-radius: 8px; cursor: pointer; text-align: center; }
.pos-tab-btn.active { background: var(--accent-primary); color: #fff; border-color: var(--accent-primary); }
</style>


<!-- Pestañas para vista Móvil / Tablet -->
<div class="pos-mobile-tabs mb-2" style="display: none; gap: 8px;">
    <button type="button" class="pos-tab-btn active" id="btnTabCart" onclick="switchPosTab('cart')"><i class="bi bi-cart3"></i> 🛒 Carrito y Cobro</button>
    <button type="button" class="pos-tab-btn" id="btnTabCatalog" onclick="switchPosTab('catalog')"><i class="bi bi-grid"></i> 📦 Catálogo y Búsqueda</button>
</div>

<div class="pos-layout">
    <!-- LADO IZQUIERDO: CARRITO DE COMPRA -->
    <div class="pos-left pos-panel active">
        <!-- Notificaciones PHP -->
        <?php if(isset($_SESSION['mensaje_pos'])): ?>
            <div class="alert alert-success mt-2 mb-2 p-2 px-3" style="background-color: var(--success-bg); color: var(--accent-primary); border: 1px solid var(--accent-primary); font-weight:600; display:flex; justify-content:space-between; align-items:center; flex-wrap:wrap; gap:8px;">
                <span><i class="bi bi-check-circle-fill"></i> <?php echo $_SESSION['mensaje_pos']; unset($_SESSION['mensaje_pos']); ?></span>
                <?php if(isset($_SESSION['last_ticket'])): ?>
                    <div class="d-flex gap-2">
                        <button class="btn btn-sm btn-outline-success border-0" onclick="window.open('<?php echo BASE_URL; ?>venta/ticket/<?php echo $_SESSION['last_ticket']; ?>', 'Ticket', 'width=400,height=600')"><i class="bi bi-printer"></i> Tiquetera</button>
                        <button class="btn btn-sm btn-success" onclick="window.open('<?php echo BASE_URL; ?>venta/pdf/<?php echo $_SESSION['last_ticket']; unset($_SESSION['last_ticket']); ?>', 'PDF', 'width=900,height=700')"><i class="bi bi-file-earmark-pdf-fill"></i> PDF A4</button>
                    </div>
                <?php endif; ?>
            </div>
        <?php endif; ?>
        <?php if(isset($_SESSION['error_pos']) || isset($_SESSION['error'])): ?>
            <div class="alert alert-danger mt-2 mb-2 p-2 px-3" style="background-color: var(--danger-bg); color: var(--danger); border: 1px solid var(--danger);">
                <i class="bi bi-exclamation-triangle-fill"></i> 
                <?php 
                $err = $_SESSION['error_pos'] ?? $_SESSION['error'];
                unset($_SESSION['error_pos'], $_SESSION['error']);
                echo htmlspecialchars($err, ENT_QUOTES, 'UTF-8'); 
                ?>
            </div>
        <?php endif; ?>

        <form action="<?php echo BASE_URL; ?>venta/save" method="POST" id="formVenta" style="display:flex; flex-direction:column; height: 100%;">
            <?php echo Controller::csrfField(); ?>
            <!-- Header Carrito: Seleccion y Búsqueda de Cliente y Tipo de Comprobante -->
            <div class="row g-2 mb-3 align-items-center">
                <div class="col-md-7">
                    <div class="input-group">
                        <span class="input-group-text bg-light text-muted border-end-0"><i class="bi bi-person-fill"></i></span>
                        <select class="form-select form-control-custom border-start-0" name="id_cliente" id="selectCliente" required style="font-size: 14px; font-weight: 600;">
                            <?php foreach($data['clientes'] as $cli): ?>
                                <option value="<?php echo $cli['id']; ?>" data-puntos="<?php echo $cli['puntos_acumulados'] ?? 0; ?>"><?php echo htmlspecialchars($cli['num_documento'] . ' - ' . $cli['nombres']); ?></option>
                            <?php endforeach; ?>
                        </select>
                        <button type="button" class="btn btn-outline-success fw-bold" data-bs-toggle="modal" data-bs-target="#modalNuevoClientePos" title="Registrar nuevo cliente">
                            <i class="bi bi-person-plus-fill"></i> + Nuevo
                        </button>
                    </div>
                    <!-- Buscador predictivo rápido de clientes -->
                    <div class="mt-1 position-relative">
                        <input type="text" id="filtroClientePos" class="form-control form-control-sm" placeholder="🔍 Escribe DNI/RUC o Nombre de cliente..." autocomplete="off" style="font-size: 11px; background: rgba(0,0,0,0.02);">
                        <div id="resultadosClientePos" class="list-group position-absolute w-100 shadow-lg" style="z-index: 1050; display: none; max-height: 200px; overflow-y: auto;"></div>
                    </div>
                    <div id="puntosBlock" class="mt-1" style="display:none; font-size:12px; font-weight:600; color: var(--accent-primary);">
                        <i class="bi bi-star-fill text-warning"></i> Puntos Disponibles: <span id="lblPuntos">0</span> pts.
                    </div>
                </div>
                <div class="col-md-5">
                    <select class="form-control-custom w-100" name="tipo_comprobante" required style="height: 38px; font-size:13px; font-weight:600;">
                        <option value="Ticket">Ticket de Venta</option>
                        <option value="Boleta">Boleta Electrónica</option>
                        <option value="Factura">Factura Electrónica</option>
                    </select>
                </div>
            </div>

            <!-- Area de lista de productos (Carrito) -->
            <div class="pos-cart" id="cartContainer">
                <table style="width: 100%; color: #222;">
                    <thead>
                        <tr style="border-bottom: 2px solid var(--border-color); color:var(--text-secondary); font-size:12px; text-transform:uppercase;">
                            <th class="pb-2">Producto</th>
                            <th class="pb-2 text-center" width="120">Cantidad</th>
                            <th class="pb-2 text-end" width="100">P. Unit</th>
                            <th class="pb-2 text-end" width="100">Subtotal</th>
                            <th class="pb-2 text-center" width="50">X</th>
                        </tr>
                    </thead>
                    <tbody id="cartItems">
                        <!-- JS Inyecta Filas -->
                    </tbody>
                </table>
                <div id="cartHiddenInputs"></div>
                <div id="cartEmpty" class="text-center text-muted mt-5 mt-md-5 pt-5">
                    <i class="bi bi-cart" style="font-size: 48px; opacity:0.3;"></i>
                    <p class="mt-2">El carrito está vacío.<br>Busca un producto a la derecha para iniciar la venta.</p>
                </div>
            </div>

            <!-- Area de Totales y Pago -->
            <div class="pos-totals">
                <div class="row">
                    <div class="col-md-7">
                        <div class="d-flex flex-column gap-2 mb-3">
                            <label class="form-label mb-0" style="color:var(--text-secondary); font-size:12px; font-weight:600;">Método de Pago</label>
                            <div class="btn-group w-100" role="group" aria-label="Metodo de pago">
                                <input type="radio" class="btn-check" name="metodo_pago" id="btnEfecti" autocomplete="off" value="Efectivo" checked onchange="cambiarMetodoPago('Efectivo')">
                                <label class="btn btn-outline-success btn-sm fw-bold" for="btnEfecti">Efectivo</label>

                                <input type="radio" class="btn-check" name="metodo_pago" id="btnYape" autocomplete="off" value="Yape/Plin" onchange="cambiarMetodoPago('Yape/Plin')">
                                <label class="btn btn-outline-info btn-sm fw-bold" for="btnYape">Yape / Plin</label>

                                <input type="radio" class="btn-check" name="metodo_pago" id="btnTarj" autocomplete="off" value="Tarjeta" onchange="cambiarMetodoPago('Tarjeta')">
                                <label class="btn btn-outline-primary btn-sm fw-bold" for="btnTarj">Tarjeta</label>

                                <input type="radio" class="btn-check" name="metodo_pago" id="btnMixto" autocomplete="off" value="Mixto" onchange="cambiarMetodoPago('Mixto')">
                                <label class="btn btn-outline-warning btn-sm fw-bold" for="btnMixto">Mixto (Dividido)</label>
                            </div>
                        </div>

                        <!-- Panel: Efectivo Puro -->
                        <div id="panelEfectivo" class="payment-panel">
                            <div class="row g-2">
                                <div class="col-6">
                                    <label class="form-label mb-0" style="color:var(--text-secondary); font-size:12px; font-weight:600;">Efectivo Recibido *</label>
                                    <div class="input-group">
                                        <span class="input-group-text bg-dark border-secondary text-white">S/</span>
                                        <input type="number" step="0.01" class="form-control bg-dark border-secondary text-white fw-bold" name="pago_recibido" id="inPago" placeholder="0.00" oninput="calcularVuelto()">
                                    </div>
                                </div>
                                <div class="col-6">
                                    <label class="form-label mb-0" style="color:var(--text-secondary); font-size:12px; font-weight:600;">Vuelto</label>
                                    <div class="input-group">
                                        <span class="input-group-text bg-dark border-secondary text-white">S/</span>
                                        <input type="number" class="form-control bg-dark border-secondary text-warning fw-bold" name="vuelto_venta" id="inVuelto" value="0.00" readonly>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Panel: Yape / Plin Puro -->
                        <div id="panelYape" class="payment-panel" style="display:none;">
                            <div class="mb-2">
                                <label class="form-label mb-1" style="color:var(--text-secondary); font-size:12px; font-weight:600;">N° de Operación Yape/Plin *</label>
                                <div class="input-group">
                                    <span class="input-group-text bg-dark border-secondary text-info"><i class="bi bi-qr-code"></i></span>
                                    <input type="text" class="form-control bg-dark border-secondary text-white fw-bold" name="num_operacion_trans" id="inOpTrans" placeholder="Ej. 084920">
                                </div>
                                <small class="text-muted" style="font-size:11px;">El monto total se registrará como transferencia digital.</small>
                            </div>
                        </div>

                        <!-- Panel: Tarjeta Puro -->
                        <div id="panelTarjeta" class="payment-panel" style="display:none;">
                            <div class="mb-2">
                                <label class="form-label mb-1" style="color:var(--text-secondary); font-size:12px; font-weight:600;">N° de Referencia / Voucher Tarjeta *</label>
                                <div class="input-group">
                                    <span class="input-group-text bg-dark border-secondary text-primary"><i class="bi bi-credit-card-2-front-fill"></i></span>
                                    <input type="text" class="form-control bg-dark border-secondary text-white fw-bold" name="num_operacion_tarj" id="inOpTarj" placeholder="Ej. 102948">
                                </div>
                                <small class="text-muted" style="font-size:11px;">El monto total se registrará como cobro electrónico en POS.</small>
                            </div>
                        </div>

                        <!-- Panel: Pago Mixto (Combinado Efectivo + Yape + Tarjeta) -->
                        <div id="panelMixto" class="payment-panel" style="display:none; background: rgba(255,193,7,0.05); border: 1px solid rgba(255,193,7,0.3); border-radius: 8px; padding: 10px;">
                            <div class="d-flex justify-content-between align-items-center mb-2">
                                <span style="font-size: 12px; font-weight:700; color: #ffc107;"><i class="bi bi-pie-chart-fill"></i> Desglose de Montos Mixtos</span>
                                <span id="badgeEstadoMixto" class="badge bg-secondary" style="font-size: 11px;">Incompleto</span>
                            </div>

                            <!-- Fila Efectivo Mixto -->
                            <div class="row g-1 align-items-center mb-1">
                                <div class="col-4">
                                    <span style="font-size:11px; font-weight:600;"><i class="bi bi-cash-stack text-success"></i> Efectivo:</span>
                                </div>
                                <div class="col-4">
                                    <input type="number" step="0.01" min="0" class="form-control form-control-sm bg-dark text-white border-secondary" name="monto_efectivo" id="inMontoEfeMixto" placeholder="Monto S/" oninput="calcularMixto()">
                                </div>
                                <div class="col-4">
                                    <input type="number" step="0.01" min="0" class="form-control form-control-sm bg-dark text-white border-secondary" id="inPagoEfeMixto" placeholder="Recibido S/" oninput="calcularMixto()" title="Efectivo entregado por el cliente">
                                </div>
                            </div>

                            <!-- Fila Yape / Plin Mixto -->
                            <div class="row g-1 align-items-center mb-1">
                                <div class="col-4">
                                    <span style="font-size:11px; font-weight:600;"><i class="bi bi-phone-fill text-info"></i> Yape/Plin:</span>
                                </div>
                                <div class="col-4">
                                    <input type="number" step="0.01" min="0" class="form-control form-control-sm bg-dark text-white border-secondary" name="monto_transferencia" id="inMontoTransMixto" placeholder="Monto S/" oninput="calcularMixto()">
                                </div>
                                <div class="col-4">
                                    <input type="text" class="form-control form-control-sm bg-dark text-white border-secondary" id="inOpTransMixto" placeholder="N° Op. Yape" title="Código de operación Yape/Plin">
                                </div>
                            </div>

                            <!-- Fila Tarjeta Mixto -->
                            <div class="row g-1 align-items-center mb-2">
                                <div class="col-4">
                                    <span style="font-size:11px; font-weight:600;"><i class="bi bi-credit-card text-primary"></i> Tarjeta:</span>
                                </div>
                                <div class="col-4">
                                    <input type="number" step="0.01" min="0" class="form-control form-control-sm bg-dark text-white border-secondary" name="monto_tarjeta" id="inMontoTarjMixto" placeholder="Monto S/" oninput="calcularMixto()">
                                </div>
                                <div class="col-4">
                                    <input type="text" class="form-control form-control-sm bg-dark text-white border-secondary" id="inOpTarjMixto" placeholder="N° Op. Tarj." title="Código de autorización tarjeta">
                                </div>
                            </div>

                            <div class="d-flex justify-content-between pt-1 border-top border-secondary" style="font-size: 11px;">
                                <span>Total ingresado: <strong id="lblSumaMixta" class="text-white">S/ 0.00</strong></span>
                                <span id="lblVueltoMixto" class="text-warning fw-bold">Vuelto Ef.: S/ 0.00</span>
                            </div>
                        </div>
                        
                        <!-- Bloque CMP Oculto -->
                        <div id="cmpBlock" class="mt-3" style="display:none; background: rgba(220, 53, 69, 0.1); padding: 10px; border-radius: 8px; border: 1px solid rgba(220, 53, 69, 0.5);">
                            <label class="form-label mb-0" style="color:var(--danger); font-size:13px; font-weight:bold;"><i class="bi bi-file-medical"></i> CMP Médico (Requerido para controlados)</label>
                            <input type="text" class="form-control bg-dark border-danger text-white mt-1" name="medico_cmp" id="inCmp" placeholder="Ej. 12345">
                        </div>
                    </div>
                    
                    <div class="col-md-5 d-flex flex-column justify-content-between text-end">
                        <div>
                            <div class="d-flex justify-content-between mb-1" style="font-size:14px; color:var(--text-secondary);">
                                <span>Subtotal:</span>
                                <span id="txtSub">S/ 0.00</span>
                                <input type="hidden" id="fiSub" name="subtotal_venta" value="0">
                            </div>
                            <div class="d-flex justify-content-between mb-1" style="font-size:14px; color:var(--text-secondary);">
                                <div>
                                    <span>Descuento:</span>
                                    <button type="button" id="btnCanjear" class="btn btn-sm btn-outline-warning ms-1 py-0 px-1" style="font-size:10px; display:none;" onclick="canjearPuntos()"><i class="bi bi-star"></i> Usar Pts</button>
                                </div>
                                <div>
                                    <span>S/</span>
                                    <input type="number" step="0.01" min="0" id="inDesc" style="width: 70px; text-align:right; border: none; border-bottom: 1px solid var(--text-secondary); background: transparent; color: var(--danger); font-weight:bold; outline:none;" value="0.00" oninput="renderCarrito()">
                                    <input type="hidden" id="fiDesc" name="descuento_venta" value="0">
                                    <input type="hidden" id="fiPuso" name="puntos_usados" value="0">
                                </div>
                            </div>
                            <div class="d-flex justify-content-between mb-2 pb-2 border-bottom border-secondary" style="font-size:14px; color:var(--text-secondary);">
                                <span>IGV (<?php echo htmlspecialchars($data['igv']); ?>% ref):</span>
                                <span id="txtIgv">S/ 0.00</span>
                                <input type="hidden" id="fiIgv" name="igv_venta" value="0">
                            </div>
                            <div class="d-flex justify-content-between">
                                <span style="font-size: 20px; font-weight:700; color:#333;">Total:</span>
                                <span style="font-size: 28px; font-weight:800; color:var(--accent-primary);" id="txtTot">S/ 0.00</span>
                                <input type="hidden" id="fiTot" name="total_venta" value="0">
                            </div>
                        </div>
                        <button type="button" class="btn-pay mt-2" onclick="confirmarVenta()">
                            <i class="bi bi-wallet2"></i> COBRAR
                        </button>
                    </div>
                </div>
            </div>
        </form>
    </div>

    <!-- LADO DERECHO: BUSCADOR CATALOGO -->
    <div class="pos-right pos-panel">
        <div class="search-box mb-3">
            <i class="bi bi-upc-scan"></i>
            <input type="text" id="buscadorPOS" placeholder="Código de barras o Nombre..." onkeyup="filtrarCatalogo()" autofocus>
        </div>
        
        <div class="pos-catalog" id="catList">
            <!-- Renderizado de catálogo disponible -->
            <?php foreach($data['productos'] as $prod): 
                $stock = $prod['stock_actual'];
                $disabledClass = $stock <= 0 ? 'disabled' : '';
                $cond = $prod['condicion_venta'] ?? 'Venta Libre';
                // Escapar para JS data
                $jsonP = json_encode([
                    'id' => $prod['id'],
                    'codigo_barras' => $prod['codigo_barras'],
                    'nombre' => $prod['nombre_comercial'],
                    'precio' => $prod['precio_venta'],
                    'precio_fraccion' => isset($prod['precio_fraccion']) ? $prod['precio_fraccion'] : 0,
                    'fraccionable' => isset($prod['fraccionable']) ? $prod['fraccionable'] : 0,
                    'unidad_fraccion' => isset($prod['unidad_fraccion']) ? $prod['unidad_fraccion'] : 'Fracción',
                    'unidad_medida' => $prod['unidad_medida'] ? $prod['unidad_medida'] : 'Caja',
                    'unidades_por_caja' => isset($prod['unidades_por_caja']) ? $prod['unidades_por_caja'] : 1,
                    'stock' => $stock,
                    'requiere_receta' => $prod['requiere_receta'],
                    'condicion_venta' => $cond,
                    'registro_sanitario' => $prod['registro_sanitario'] ?? ''
                ]);
            ?>
            <div class="item-card <?php echo $disabledClass; ?>" data-busqueda="<?php echo strtolower($prod['codigo_barras'] . ' ' . $prod['nombre_comercial'] . ' ' . $prod['nombre_generico']); ?>" onclick='agregarAlCarrito(<?php echo htmlspecialchars($jsonP, ENT_QUOTES); ?>)'>
                <div style="flex-grow:1;">
                    <strong style="color:#222; display:block; font-size: 14px;">
                        <?php echo htmlspecialchars($prod['nombre_comercial']); ?>
                        <?php 
                        if($cond === 'Receta Médica Retenida') {
                            echo ' <span class="badge bg-danger" style="font-size: 9px; padding: 2px 4px; font-weight:800;">R. RETENIDA</span>';
                        } elseif($cond === 'Receta Médica Simple') {
                            echo ' <span class="badge bg-warning text-dark" style="font-size: 9px; padding: 2px 4px; font-weight:800;">R. SIMPLE</span>';
                        }
                        ?>
                    </strong>
                    <span style="font-size: 11px; color:var(--text-secondary);"><?php echo htmlspecialchars($prod['unidad_medida'] . ' ' . $prod['concentracion']); ?> | <?php echo $stock > 0 ? "Stock U.Mín: $stock" : "<span class='text-danger'>Agotado</span>"; ?></span>
                </div>
                <div style="font-weight: 700; color: var(--accent-primary); font-size: 16px;">
                    S/ <?php echo number_format($prod['precio_venta'], 2); ?>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
    </div>
</div>

<!-- MODAL REGISTRO RÁPIDO DE CLIENTE DESDE POS -->
<div class="modal fade" id="modalNuevoClientePos" tabindex="-1" aria-labelledby="modalNuevoClienteLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow-lg" style="border-radius: 12px; overflow: hidden;">
            <div class="modal-header bg-success text-white py-2">
                <h6 class="modal-title fw-bold" id="modalNuevoClienteLabel"><i class="bi bi-person-plus-fill me-2"></i> Nuevo Cliente Rápido</h6>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form id="formNuevoClientePos" onsubmit="guardarClientePos(event)">
                <div class="modal-body p-3">
                    <div id="alertaErrorClientePos" class="alert alert-danger py-2 d-none" style="font-size: 12px;"></div>
                    
                    <div class="row g-2 mb-2">
                        <div class="col-5">
                            <label class="form-label mb-1" style="font-size: 11px; font-weight: 700; color: #444;">Tipo Doc *</label>
                            <select class="form-select form-select-sm" name="tipo_documento" id="nuevoCliTipoDoc">
                                <option value="DNI" selected>DNI</option>
                                <option value="RUC">RUC</option>
                                <option value="CE">Carnet Ext.</option>
                                <option value="Pasaporte">Pasaporte</option>
                            </select>
                        </div>
                        <div class="col-7">
                            <label class="form-label mb-1" style="font-size: 11px; font-weight: 700; color: #444;">N° Documento *</label>
                            <input type="text" class="form-control form-control-sm" name="num_documento" id="nuevoCliNumDoc" required placeholder="Ej. 70854120">
                        </div>
                    </div>
                    
                    <div class="mb-2">
                        <label class="form-label mb-1" style="font-size: 11px; font-weight: 700; color: #444;">Nombres y Apellidos / Razón Social *</label>
                        <input type="text" class="form-control form-control-sm" name="nombres" id="nuevoCliNombres" required placeholder="Ej. Juan Pérez García">
                    </div>
                    
                    <div class="row g-2 mb-1">
                        <div class="col-6">
                            <label class="form-label mb-1" style="font-size: 11px; font-weight: 700; color: #444;">Teléfono / Celular</label>
                            <input type="text" class="form-control form-control-sm" name="telefono" id="nuevoCliTelefono" placeholder="Ej. 987654321">
                        </div>
                        <div class="col-6">
                            <label class="form-label mb-1" style="font-size: 11px; font-weight: 700; color: #444;">Dirección</label>
                            <input type="text" class="form-control form-control-sm" name="direccion" id="nuevoCliDireccion" placeholder="Ej. Av. Principal 123">
                        </div>
                    </div>
                </div>
                <div class="modal-footer bg-light py-2">
                    <button type="button" class="btn btn-secondary btn-sm" data-bs-dismiss="modal">Cancelar</button>
                    <button type="submit" class="btn btn-success btn-sm fw-bold" id="btnGuardarClientePos"><i class="bi bi-check-circle"></i> Guardar y Asignar</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
let carrito = {};

// Catálogo Global en JS
const catalogoGlobal = [
<?php foreach($data['productos'] as $p): ?>
    <?php echo json_encode([
        'id' => $p['id'],
        'codigo_barras' => $p['codigo_barras'],
        'nombre' => $p['nombre_comercial'],
        'precio' => $p['precio_venta'],
        'precio_fraccion' => isset($p['precio_fraccion']) ? $p['precio_fraccion'] : 0,
        'fraccionable' => isset($p['fraccionable']) ? $p['fraccionable'] : 0,
        'unidad_fraccion' => isset($p['unidad_fraccion']) ? $p['unidad_fraccion'] : 'Fracción',
        'unidad_medida' => $p['unidad_medida'] ? $p['unidad_medida'] : 'Caja',
        'unidades_por_caja' => isset($p['unidades_por_caja']) ? $p['unidades_por_caja'] : 1,
        'stock' => $p['stock_actual'],
        'requiere_receta' => $p['requiere_receta'],
        'condicion_venta' => $p['condicion_venta'] ?? 'Venta Libre',
        'registro_sanitario' => $p['registro_sanitario'] ?? ''
    ]); ?>,
<?php endforeach; ?>
];

// Fidelidad
let ratioCanje = 10; // 10 puntos = 1 Sol

document.querySelector('select[name="id_cliente"]').addEventListener('change', function() {
    evaluarClientePuntos();
});

function evaluarClientePuntos() {
    let sel = document.querySelector('select[name="id_cliente"]');
    let opt = sel.options[sel.selectedIndex];
    let pts = parseInt(opt.getAttribute('data-puntos')) || 0;
    
    if(sel.value == 1) { // Publico General
        document.getElementById('puntosBlock').style.display = 'none';
        document.getElementById('btnCanjear').style.display = 'none';
        // Reset 
        document.getElementById('fiPuso').value = 0;
    } else {
        document.getElementById('puntosBlock').style.display = 'block';
        document.getElementById('lblPuntos').innerText = pts;
        
        if(pts >= ratioCanje) {
            document.getElementById('btnCanjear').style.display = 'inline-block';
        } else {
            document.getElementById('btnCanjear').style.display = 'none';
        }
    }
    renderCarrito(); // Por si habíamos aplicado descuento por puntos, validar si cambia
}

function canjearPuntos() {
    let sel = document.querySelector('select[name="id_cliente"]');
    let pts = parseInt(sel.options[sel.selectedIndex].getAttribute('data-puntos')) || 0;
    let maxSoles = pts / ratioCanje; // EJ: 15 / 10 = 1.5 soles
    
    // Obtenemos el total sin descuento actual
    let arrCart = Object.values(carrito);
    let sum = 0;
    arrCart.forEach(i => { sum += (i.tipo_unidad == 'CAJA' ? i.precio_caja : i.precio_fraccion) * i.cantidad; });
    
    if(sum <= 0) { alert("Primero agrega productos al carrito."); return; }
    
    // Queremos usar la máxima cantidad de puntos para el total, pero no pasarnos del total
    let dsctoSoles = maxSoles;
    if(dsctoSoles > sum) dsctoSoles = sum;
    
    let puntosAUsar = Math.floor(dsctoSoles * ratioCanje);
    dsctoSoles = puntosAUsar / ratioCanje;
    
    document.getElementById('inDesc').value = dsctoSoles.toFixed(2);
    document.getElementById('fiPuso').value = puntosAUsar;
    
    renderCarrito();
}

// Inicializar select
evaluarClientePuntos();

document.getElementById("buscadorPOS").addEventListener('keydown', function(e) {
    if (e.key === 'Enter') {
        e.preventDefault();
        let codigo = this.value.trim();
        if (codigo === '') return;
        
        let productoList = catalogoGlobal.filter(p => p.codigo_barras && p.codigo_barras.trim().toLowerCase() === codigo.toLowerCase());
        if(productoList.length > 0) {
            let prod = productoList[0];
            if (prod.stock <= 0) {
                alert("Producto sin stock o agotado: " + prod.nombre);
                this.value = '';
                filtrarCatalogo();
                return;
            }
            agregarAlCarrito(prod);
            this.value = '';
            filtrarCatalogo();
        } else {
            // Verificar si hay coincidencia exacta de nombre o exactamente 1 tarjeta visible
            let coincidencias = catalogoGlobal.filter(p => p.nombre && p.nombre.toLowerCase() === codigo.toLowerCase());
            if (coincidencias.length === 1) {
                if (coincidencias[0].stock <= 0) {
                    alert("Producto sin stock o agotado: " + coincidencias[0].nombre);
                    this.value = '';
                    filtrarCatalogo();
                    return;
                }
                agregarAlCarrito(coincidencias[0]);
                this.value = '';
                filtrarCatalogo();
                return;
            }
            
            let visibleCards = Array.from(document.querySelectorAll('#catList .item-card')).filter(el => el.style.display !== 'none');
            if (visibleCards.length === 1 && !visibleCards[0].classList.contains('disabled')) {
                visibleCards[0].click();
                this.value = '';
                filtrarCatalogo();
                return;
            }

            alert('No se encontró ningún producto con el código de barras o término: "' + codigo + '"');
            this.select();
        }
    }
});

function filtrarCatalogo() {
    let input = document.getElementById("buscadorPOS").value.toLowerCase();
    let items = document.getElementsByClassName("item-card");
    for (let i = 0; i < items.length; i++) {
        let keyword = items[i].getAttribute("data-busqueda");
        if (keyword.indexOf(input) > -1) {
            items[i].style.display = "flex";
        } else {
            items[i].style.display = "none";
        }
    }
}

function agregarAlCarrito(producto) {
    let id = producto.id;
    if(carrito[id]) {
        let stockRequerido = carrito[id].cantidad;
        if (carrito[id].tipo_unidad == 'CAJA' && carrito[id].fraccionable == 1) {
            stockRequerido = carrito[id].cantidad * carrito[id].unidades_por_caja;
        }
        let stockFuturo = stockRequerido + (carrito[id].tipo_unidad == 'CAJA' && carrito[id].fraccionable == 1 ? carrito[id].unidades_por_caja : 1);

        if(stockFuturo <= producto.stock) {
            carrito[id].cantidad++;
        } else {
            alert('¡Límite de stock original alcanzado para este producto (Stock en unidades: '+producto.stock+')!');
        }
    } else {
        carrito[id] = {
            id: producto.id,
            nombre: producto.nombre,
            precio_caja: parseFloat(producto.precio),
            precio_fraccion: parseFloat(producto.precio_fraccion),
            fraccionable: producto.fraccionable,
            unidad_medida: producto.unidad_medida,
            unidad_fraccion: producto.unidad_fraccion,
            unidades_por_caja: parseInt(producto.unidades_por_caja) || 1,
            tipo_unidad: 'CAJA', // Por defecto compra como se vende normalmente
            cantidad: 1,
            stock: parseInt(producto.stock),
            requiere_receta: producto.requiere_receta,
            condicion_venta: producto.condicion_venta || 'Venta Libre',
            registro_sanitario: producto.registro_sanitario || ''
        };
    }
    renderCarrito();
    document.getElementById("buscadorPOS").value = ''; // limpiar
    document.getElementById("buscadorPOS").focus();
}

function cambiarUnidad(id, tipo) {
    if(carrito[id]) {
        carrito[id].tipo_unidad = tipo;
        let stockRequerido = carrito[id].cantidad;
        if (tipo == 'CAJA' && carrito[id].fraccionable == 1) {
            stockRequerido = carrito[id].cantidad * carrito[id].unidades_por_caja;
        }
        if (stockRequerido > carrito[id].stock) {
            alert("No hay suficiente stock para vender en este formato (Stock disponible: " + carrito[id].stock + " unidades mínimas).");
            carrito[id].tipo_unidad = 'FRACCION';
        }
    }
    renderCarrito();
}

function modQty(id, delta) {
    if(carrito[id]) {
        let nueva = carrito[id].cantidad + delta;
        if(nueva <= 0) {
            delete carrito[id];
        } else {
            let stock_req = nueva;
            if (carrito[id].tipo_unidad == 'CAJA' && carrito[id].fraccionable == 1) {
                stock_req = nueva * carrito[id].unidades_por_caja;
            }
            if (stock_req > carrito[id].stock) {
                alert('Stock insuficiente (Disponibles: ' + carrito[id].stock + ' unidades mínimas).');
            } else {
                carrito[id].cantidad = nueva;
            }
        }
    }
    renderCarrito();
}

function removeRow(id) {
    delete carrito[id];
    renderCarrito();
}

function renderCarrito() {
    let tbody = document.getElementById('cartItems');
    let emptyMsg = document.getElementById('cartEmpty');
    tbody.innerHTML = '';
    
    let sum = 0;
    let formsHtml = ''; // Inputs ocultos
    let arrCart = Object.values(carrito);
    let requiereCmp = false;
    let medicamentosRetenidos = [];
    
    if(arrCart.length === 0) {
        emptyMsg.style.display = 'block';
    } else {
        emptyMsg.style.display = 'none';
        
        arrCart.forEach(item => {
            if(item.requiere_receta == 1) requiereCmp = true;
            if(item.condicion_venta === 'Receta Médica Retenida') {
                medicamentosRetenidos.push(item.nombre);
            }
            
            let precioUnit = item.tipo_unidad == 'CAJA' ? item.precio_caja : item.precio_fraccion;
            let subtotal = precioUnit * item.cantidad;
            sum += subtotal;

            let comboUnidad = item.fraccionable == 1 
                ? `<select class="bg-dark text-white border-0 py-1 rounded" style="font-size:11px;" onchange="cambiarUnidad(${item.id}, this.value)">
                    <option value="CAJA" ${item.tipo_unidad == 'CAJA' ? 'selected' : ''}>${item.unidad_medida}</option>
                    <option value="FRACCION" ${item.tipo_unidad == 'FRACCION' ? 'selected' : ''}>${item.unidad_fraccion}</option>
                   </select>` 
                : `<span style="font-size:11px; color:#aaa;">${item.unidad_medida}</span>`;
            
            // Fila Visual
            tbody.innerHTML += `
            <tr class="tr-cart">
                <td>
                    <strong style="color:#222;font-size:14px;display:block;">${item.nombre}</strong>
                    ${comboUnidad}
                </td>
                <td class="text-center" style="vertical-align:top; pt-2;">
                    <div style="display:flex; justify-content:center; align-items:center; gap:5px;">
                        <span class="qty-btn" onclick="modQty(${item.id}, -1)">-</span>
                        <input type="text" readonly class="qty-input" value="${item.cantidad}">
                        <span class="qty-btn" onclick="modQty(${item.id}, 1)">+</span>
                    </div>
                </td>
                <td class="text-end" style="color:var(--text-secondary); vertical-align:top; pt-2;">${precioUnit.toFixed(2)}</td>
                <td class="text-end" style="font-weight:700; vertical-align:top; pt-2;">${subtotal.toFixed(2)}</td>
                <td class="text-center" style="vertical-align:top; pt-2;"><button type="button" class="btn btn-sm text-danger border-0 p-0" onclick="removeRow(${item.id})"><i class="bi bi-x-circle-fill fs-5"></i></button></td>
            </tr>
            `;
            
            // Inputs invisibles para el POST PHP
            formsHtml += `
                <input type="hidden" name="producto_id[]" value="${item.id}">
                <input type="hidden" name="cantidad[]" value="${item.cantidad}">
                <input type="hidden" name="precio_d[]" value="${precioUnit}">
                <input type="hidden" name="subtotal_d[]" value="${subtotal}">
                <input type="hidden" name="tipo_unidad[]" value="${item.tipo_unidad}">
            `;
        });
    }
    
    // Anexar inputs form ocultos en su contenedor válido
    let hiddenContainer = document.getElementById('cartHiddenInputs');
    if (hiddenContainer) {
        hiddenContainer.innerHTML = formsHtml;
    }
    
    // Toggle CMP validation field
    if(requiereCmp) {
        document.getElementById('cmpBlock').style.display = 'block';
        document.getElementById('inCmp').setAttribute('required', 'required');
        
        // Agregar advertencia de receta retenida si existen medicamentos
        let advertenciaRetenidosHtml = '';
        if(medicamentosRetenidos.length > 0) {
            advertenciaRetenidosHtml = `
                <div class="mt-2 alert alert-danger p-2" style="font-size: 11px; background: rgba(220,53,69,0.2); border: 1px solid var(--danger); border-radius: 5px; color: var(--danger); font-weight:700; text-align: left;">
                    <i class="bi bi-exclamation-triangle-fill"></i> OBLIGATORIO RETENER RECETA FÍSICA para: ${medicamentosRetenidos.join(', ')}. Archivar en el Libro de Control.
                </div>
            `;
        }
        
        // Inyectar o remover la advertencia
        let advCmp = document.getElementById('advRetenidos');
        if(!advCmp) {
            let container = document.getElementById('cmpBlock');
            let advDiv = document.createElement('div');
            advDiv.id = 'advRetenidos';
            advDiv.innerHTML = advertenciaRetenidosHtml;
            container.appendChild(advDiv);
        } else {
            advCmp.innerHTML = advertenciaRetenidosHtml;
        }
    } else {
        document.getElementById('cmpBlock').style.display = 'none';
        document.getElementById('inCmp').removeAttribute('required');
        document.getElementById('inCmp').value = '';
        let advCmp = document.getElementById('advRetenidos');
        if(advCmp) advCmp.remove();
    }
    
    // Validar descuento
    let inDescObj = document.getElementById('inDesc');
    let descuento = parseFloat(inDescObj.value) || 0;
    if(descuento < 0) { descuento = 0; inDescObj.value = '0.00'; }
    if(descuento > sum) { descuento = sum; inDescObj.value = descuento.toFixed(2); }
    
    // Si se modifica manualmente el descuento y difiere de lo usado en puntos, reseteamos puntos usados
    let sel = document.querySelector('select[name="id_cliente"]');
    if(sel.value != 1) {
        let ptsObj = document.getElementById('fiPuso');
        let dEsperado = (parseFloat(ptsObj.value) || 0) / ratioCanje;
        if(Math.abs(dEsperado - descuento) > 0.01) {
             ptsObj.value = 0; // Se anuló el canje automático o se sobreescribió a mano
        }
    } else {
        document.getElementById('fiPuso').value = 0;
    }
    
    let totalCobrar = sum - descuento;
    
    // Totales global dinámico por config
    let igvLocal = <?php echo floatval($data['igv']); ?>;
    let factorIgv = (igvLocal / 100) + 1;
    let mIgv = totalCobrar - (totalCobrar / factorIgv);
    let mSubSec = totalCobrar - mIgv;
    
    document.getElementById('txtSub').innerText = 'S/ ' + mSubSec.toFixed(2);
    document.getElementById('txtIgv').innerText = 'S/ ' + mIgv.toFixed(2);
    document.getElementById('txtTot').innerText = 'S/ ' + totalCobrar.toFixed(2);
    
    document.getElementById('fiSub').value = mSubSec.toFixed(2);
    document.getElementById('fiIgv').value = mIgv.toFixed(2);
    document.getElementById('fiTot').value = totalCobrar.toFixed(2);
    document.getElementById('fiDesc').value = descuento.toFixed(2);
    
    calcularVuelto();
    if(document.getElementById('btnMixto') && document.getElementById('btnMixto').checked) {
        calcularMixto();
    }
}

// -----------------------------------------
// PESTAÑAS RESPONSIVE (MÓVIL / TABLET)
// -----------------------------------------
function switchPosTab(tab) {
    const leftPanel = document.querySelector('.pos-left');
    const rightPanel = document.querySelector('.pos-right');
    const btnCart = document.getElementById('btnTabCart');
    const btnCatalog = document.getElementById('btnTabCatalog');
    if (tab === 'cart') {
        leftPanel.classList.add('active');
        rightPanel.classList.remove('active');
        btnCart.classList.add('active');
        btnCatalog.classList.remove('active');
    } else {
        rightPanel.classList.add('active');
        leftPanel.classList.remove('active');
        btnCatalog.classList.add('active');
        btnCart.classList.remove('active');
        setTimeout(() => { document.getElementById('buscadorPOS').focus(); }, 150);
    }
}

// -----------------------------------------
// BÚSQUEDA PREDICTIVA Y REGISTRO DE CLIENTE
// -----------------------------------------
let searchCliTimeout = null;
const inputFiltroCli = document.getElementById('filtroClientePos');
const resDivCli = document.getElementById('resultadosClientePos');
const selCli = document.getElementById('selectCliente');

if (inputFiltroCli) {
    inputFiltroCli.addEventListener('input', function() {
        clearTimeout(searchCliTimeout);
        const query = this.value.trim();
        if (query.length === 0) {
            resDivCli.style.display = 'none';
            resDivCli.innerHTML = '';
            return;
        }
        searchCliTimeout = setTimeout(() => {
            fetch('<?php echo BASE_URL; ?>cliente/searchAjax?q=' + encodeURIComponent(query))
                .then(r => r.json())
                .then(data => {
                    if (data.success && data.clientes && data.clientes.length > 0) {
                        let html = '';
                        data.clientes.forEach(c => {
                            let docSafe = (c.num_documento || '').replace(/'/g, "\\'");
                            let nomSafe = (c.nombres || '').replace(/'/g, "\\'");
                            html += `
                                <button type="button" class="list-group-item list-group-item-action py-2 d-flex justify-content-between align-items-center" onclick="seleccionarClientePredictivo(${c.id}, '${docSafe}', '${nomSafe}', ${c.puntos_acumulados || 0})">
                                    <div>
                                        <span class="badge bg-secondary me-1">${c.tipo_documento || 'DOC'}: ${c.num_documento}</span>
                                        <strong class="text-dark">${c.nombres}</strong>
                                    </div>
                                    <span class="badge bg-success">${c.puntos_acumulados || 0} pts</span>
                                </button>
                            `;
                        });
                        resDivCli.innerHTML = html;
                        resDivCli.style.display = 'block';
                    } else {
                        resDivCli.innerHTML = '<div class="list-group-item text-muted py-2">No se encontraron clientes coincidentes</div>';
                        resDivCli.style.display = 'block';
                    }
                })
                .catch(() => {
                    resDivCli.style.display = 'none';
                });
        }, 200);
    });

    document.addEventListener('click', function(e) {
        if (inputFiltroCli && resDivCli && !inputFiltroCli.contains(e.target) && !resDivCli.contains(e.target)) {
            resDivCli.style.display = 'none';
        }
    });
}

function seleccionarClientePredictivo(id, numDoc, nombres, puntos) {
    let opt = Array.from(selCli.options).find(o => o.value == id);
    if (!opt) {
        opt = document.createElement('option');
        opt.value = id;
        opt.textContent = numDoc + ' - ' + nombres;
        opt.setAttribute('data-puntos', puntos);
        selCli.appendChild(opt);
    }
    selCli.value = id;
    if (resDivCli) resDivCli.style.display = 'none';
    if (inputFiltroCli) inputFiltroCli.value = numDoc + ' - ' + nombres;
    evaluarClientePuntos();
}

function guardarClientePos(e) {
    e.preventDefault();
    const btn = document.getElementById('btnGuardarClientePos');
    const alertBox = document.getElementById('alertaErrorClientePos');
    alertBox.classList.add('d-none');
    btn.disabled = true;
    btn.innerHTML = '<span class="spinner-border spinner-border-sm me-1"></span> Guardando...';

    const form = document.getElementById('formNuevoClientePos');
    const formData = new FormData(form);
    const csrfEl = document.querySelector('input[name="csrf_token"]');
    if (csrfEl) formData.append('csrf_token', csrfEl.value);

    fetch('<?php echo BASE_URL; ?>cliente/saveAjax', {
        method: 'POST',
        body: formData
    })
    .then(r => r.json())
    .then(data => {
        btn.disabled = false;
        btn.innerHTML = '<i class="bi bi-check-circle"></i> Guardar y Asignar';
        if (data.success && data.cliente) {
            const c = data.cliente;
            seleccionarClientePredictivo(c.id, c.num_documento, c.nombres, 0);
            
            const modalEl = document.getElementById('modalNuevoClientePos');
            const modal = bootstrap.Modal.getInstance(modalEl) || new bootstrap.Modal(modalEl);
            modal.hide();
            form.reset();
        } else {
            alertBox.textContent = data.error || 'Error al registrar cliente.';
            alertBox.classList.remove('d-none');
        }
    })
    .catch(err => {
        btn.disabled = false;
        btn.innerHTML = '<i class="bi bi-check-circle"></i> Guardar y Asignar';
        alertBox.textContent = 'Error de comunicación con el servidor.';
        alertBox.classList.remove('d-none');
    });
}

// -----------------------------------------
// GESTIÓN DE MÉTODOS DE PAGO Y VUELTO
// -----------------------------------------
function cambiarMetodoPago(metodo) {
    document.getElementById('panelEfectivo').style.display = (metodo === 'Efectivo') ? 'block' : 'none';
    document.getElementById('panelYape').style.display = (metodo === 'Yape/Plin') ? 'block' : 'none';
    document.getElementById('panelTarjeta').style.display = (metodo === 'Tarjeta') ? 'block' : 'none';
    document.getElementById('panelMixto').style.display = (metodo === 'Mixto') ? 'block' : 'none';

    if (metodo === 'Efectivo') {
        document.getElementById('inPago').focus();
        calcularVuelto();
    } else if (metodo === 'Yape/Plin') {
        document.getElementById('inOpTrans').focus();
    } else if (metodo === 'Tarjeta') {
        document.getElementById('inOpTarj').focus();
    } else if (metodo === 'Mixto') {
        calcularMixto();
    }
}

function calcularVuelto() {
    let total = parseFloat(document.getElementById('fiTot').value) || 0;
    let pago = parseFloat(document.getElementById('inPago').value) || 0;
    let vuelto = 0;
    if(pago >= total && pago > 0) {
        vuelto = pago - total;
    }
    document.getElementById('inVuelto').value = vuelto.toFixed(2);
}

function calcularMixto() {
    let total = parseFloat(document.getElementById('fiTot').value) || 0;
    let efe = parseFloat(document.getElementById('inMontoEfeMixto').value) || 0;
    let tra = parseFloat(document.getElementById('inMontoTransMixto').value) || 0;
    let tar = parseFloat(document.getElementById('inMontoTarjMixto').value) || 0;
    let suma = Math.round((efe + tra + tar) * 100) / 100;
    let dif = Math.round((total - suma) * 100) / 100;

    document.getElementById('lblSumaMixta').innerText = 'S/ ' + suma.toFixed(2);
    let badge = document.getElementById('badgeEstadoMixto');

    if (Math.abs(dif) < 0.01 && total > 0) {
        badge.className = 'badge bg-success';
        badge.innerText = 'Cuadrado (100%)';
    } else if (dif > 0) {
        badge.className = 'badge bg-warning text-dark';
        badge.innerText = 'Falta: S/ ' + dif.toFixed(2);
    } else {
        badge.className = 'badge bg-danger';
        badge.innerText = 'Excede: S/ ' + Math.abs(dif).toFixed(2);
    }

    let recibidoEfe = parseFloat(document.getElementById('inPagoEfeMixto').value) || 0;
    let vueltoEfe = 0;
    if (recibidoEfe >= efe && efe > 0) {
        vueltoEfe = recibidoEfe - efe;
    }
    document.getElementById('lblVueltoMixto').innerText = 'Vuelto Ef.: S/ ' + vueltoEfe.toFixed(2);
}

function confirmarVenta() {
    let total = parseFloat(document.getElementById('fiTot').value) || 0;
    if(total <= 0) {
        alert("El carrito está vacío. Agregue productos antes de cobrar.");
        return;
    }

    let metodo = document.querySelector('input[name="metodo_pago"]:checked').value;

    // VALIDACIÓN ESTRICTA: EFECTIVO
    if(metodo === 'Efectivo') {
        let pago = parseFloat(document.getElementById('inPago').value) || 0;
        if (pago <= 0) {
            alert("Atención: Debe ingresar el efectivo recibido antes de procesar la venta.");
            document.getElementById('inPago').focus();
            return;
        }
        if (pago < total) {
            alert("Error: El efectivo recibido (S/ " + pago.toFixed(2) + ") es menor al total de la venta (S/ " + total.toFixed(2) + ").");
            document.getElementById('inPago').focus();
            return;
        }
    } 
    // VALIDACIÓN ESTRICTA: YAPE / PLIN
    else if (metodo === 'Yape/Plin') {
        let op = document.getElementById('inOpTrans').value.trim();
        if (op === '') {
            alert("Atención: Por favor ingrese el N° de Operación de Yape / Plin.");
            document.getElementById('inOpTrans').focus();
            return;
        }
    } 
    // VALIDACIÓN ESTRICTA: TARJETA
    else if (metodo === 'Tarjeta') {
        let op = document.getElementById('inOpTarj').value.trim();
        if (op === '') {
            alert("Atención: Por favor ingrese el N° de Referencia / Voucher del POS Tarjeta.");
            document.getElementById('inOpTarj').focus();
            return;
        }
    } 
    // VALIDACIÓN ESTRICTA: MIXTO
    else if (metodo === 'Mixto') {
        let efe = parseFloat(document.getElementById('inMontoEfeMixto').value) || 0;
        let tra = parseFloat(document.getElementById('inMontoTransMixto').value) || 0;
        let tar = parseFloat(document.getElementById('inMontoTarjMixto').value) || 0;
        let suma = Math.round((efe + tra + tar) * 100) / 100;
        let totRedondo = Math.round(total * 100) / 100;

        if (Math.abs(suma - totRedondo) > 0.01) {
            alert("Error en Pago Mixto: La suma de montos (S/ " + suma.toFixed(2) + ") debe ser exactamente igual al total a pagar (S/ " + totRedondo.toFixed(2) + ").");
            return;
        }

        if (efe > 0) {
            let pagoEfe = parseFloat(document.getElementById('inPagoEfeMixto').value) || 0;
            if (pagoEfe <= 0) {
                alert("Atención: Debe ingresar el efectivo recibido para la porción en efectivo.");
                document.getElementById('inPagoEfeMixto').focus();
                return;
            }
            if (pagoEfe < efe) {
                alert("Error en Pago Mixto: El efectivo recibido (S/ " + pagoEfe.toFixed(2) + ") no cubre la porción en efectivo (S/ " + efe.toFixed(2) + ").");
                document.getElementById('inPagoEfeMixto').focus();
                return;
            }
            document.getElementById('inPago').value = pagoEfe;
        } else {
            document.getElementById('inPago').value = 0;
        }

        if (tra > 0) {
            let opTra = document.getElementById('inOpTransMixto').value.trim();
            if (opTra === '') {
                alert("Atención: Ingrese el N° de Operación de Yape/Plin para el monto transferido.");
                document.getElementById('inOpTransMixto').focus();
                return;
            }
            document.getElementById('inOpTrans').value = opTra;
        }

        if (tar > 0) {
            let opTar = document.getElementById('inOpTarjMixto').value.trim();
            if (opTar === '') {
                alert("Atención: Ingrese el N° de Operación de Tarjeta para el monto con tarjeta.");
                document.getElementById('inOpTarjMixto').focus();
                return;
            }
            document.getElementById('inOpTarj').value = opTar;
        }
    }
    
    // Validar CMP visible
    let cmp = document.getElementById('inCmp');
    if(cmp && cmp.hasAttribute('required') && cmp.value.trim() === '') {
        alert("Atención: Ha incluido productos controlados. Debe ingresar la colegiatura médica (CMP) del doctor.");
        cmp.focus();
        return;
    }

    if(confirm('¿Procesar venta por S/ ' + total.toFixed(2) + ' mediante ' + metodo + '?')) {
        document.getElementById('formVenta').submit();
    }
}

// -----------------------------------------
// OPTIMIZACIÓN DE HARDWARE (Atajos y Lector)
// -----------------------------------------
document.addEventListener('keydown', function(e) {
    // F2: Enfocar Buscador (Lector láser)
    if (e.key === 'F2') {
        e.preventDefault();
        document.getElementById('buscadorPOS').focus();
    }
    // F4: Enfocar Efectivo Recibido
    if (e.key === 'F4') {
        e.preventDefault();
        document.getElementById('btnEfecti').checked = true;
        cambiarMetodoPago('Efectivo');
        document.getElementById('inPago').focus();
        document.getElementById('inPago').select();
    }
    // F10: Cobrar
    if (e.key === 'F10') {
        e.preventDefault();
        confirmarVenta();
    }
    
    // Lector de Código de Barras (Captura global pasiva)
    // Si el usuario escanea y no está enfocado en ningún input
    if (!['INPUT', 'TEXTAREA', 'SELECT'].includes(e.target.tagName)) {
        if (e.key.length === 1 && /[a-zA-Z0-9\-]/.test(e.key)) {
            let buscador = document.getElementById('buscadorPOS');
            buscador.focus();
        }
    }
});

// Cobrar con Enter desde el campo de Efectivo
document.getElementById('inPago').addEventListener('keydown', function(e) {
    if (e.key === 'Enter') {
        e.preventDefault();
        confirmarVenta();
    }
});
</script>
