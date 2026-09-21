<?php
class VentaController extends Controller {

    public function __construct() {
        $this->requireAuth();
    }

    public function pos() {
        $cajaModel = $this->model('Caja');
        $cajaAbierta = $cajaModel->getCajaAbiertaPorUsuario($_SESSION['user_id']);
        if (!$cajaAbierta) {
            $_SESSION['error'] = "Debe aperturar su caja antes de poder realizar ventas.";
            header('Location: ' . BASE_URL . 'caja/apertura');
            exit;
        }
        
        $cliModel = $this->model('Cliente');
        $prodModel = $this->model('Producto');
        
        $configModel = $this->model('Configuracion');
        $puntoModel = $this->model('Punto');
        
        $data = [
            'title' => 'Punto de Venta',
            'clientes' => $cliModel->getAll(),
            'productos' => $prodModel->getAll(),
            'igv' => $configModel->get('igv'),
            'configPuntos' => $puntoModel->getConfig()
        ];
        
        // Vista directa para el POS (usa layout de main)
        $this->view('ventas/pos', $data);
    }
    
    public function index() {
        $modelo = $this->model('Venta');
        $configModel = $this->model('Configuracion');
        $cliModel = $this->model('Cliente');
        $usrModel = $this->model('User');

        $filtros = [
            'fecha_inicio' => !empty($_GET['fecha_inicio']) ? trim($_GET['fecha_inicio']) : '',
            'fecha_fin'    => !empty($_GET['fecha_fin']) ? trim($_GET['fecha_fin']) : '',
            'id_cliente'   => !empty($_GET['id_cliente']) ? (int)$_GET['id_cliente'] : '',
            'id_usuario'   => !empty($_GET['id_usuario']) ? (int)$_GET['id_usuario'] : '',
            'metodo_pago'  => !empty($_GET['metodo_pago']) ? trim($_GET['metodo_pago']) : ''
        ];

        $page = isset($_GET['page']) && is_numeric($_GET['page']) ? max(1, (int)$_GET['page']) : 1;
        $limit = isset($_GET['limit']) && in_array((int)$_GET['limit'], [15, 25, 50, 100]) ? (int)$_GET['limit'] : 25;
        $offset = ($page - 1) * $limit;

        $totalRegistros = $modelo->contarVentas($filtros);
        $totalPaginas = max(1, ceil($totalRegistros / $limit));
        if ($page > $totalPaginas) {
            $page = $totalPaginas;
            $offset = ($page - 1) * $limit;
        }

        $ventas = $modelo->getVentasPaginadas($filtros, $limit, $offset);

        $this->view('ventas/index', [
            'title'           => 'Historial de Ventas',
            'ventas'          => $ventas,
            'config'          => $configModel->getAll(),
            'clientes'        => $cliModel->getAll(),
            'cajeros'         => $usrModel->getAll(),
            'filtros'         => $filtros,
            'pagina_actual'   => $page,
            'total_paginas'   => $totalPaginas,
            'total_registros' => $totalRegistros,
            'limit'           => $limit,
            'ultima_fecha'    => $modelo->getUltimaFechaVenta()
        ]);
    }

    public function ticket($id) {
        $id = (int)$id;
        $modelo = $this->model('Venta');
        $venta_actual = $modelo->getById($id);
        
        if(!$venta_actual) {
            $_SESSION['error'] = "El ticket de venta solicitado (#$id) no fue encontrado.";
            header('Location: ' . BASE_URL . 'venta/index');
            exit;
        }
        
        $detalles = $modelo->getDetalles($id);
        $configModel = $this->model('Configuracion');
        
        $data = [
            'venta'    => $venta_actual,
            'detalles' => $detalles,
            'config'   => $configModel->getAll()
        ];
        
        // Cargar vista HTML plana (sin layout)
        require_once '../app/views/ventas/ticket.php';
        exit;
    }

    public function pdf($id) {
        $id = (int)$id;
        $modelo = $this->model('Venta');
        $venta_actual = $modelo->getById($id);
        
        if(!$venta_actual) {
            $_SESSION['error'] = "El comprobante solicitado (#$id) no fue encontrado.";
            header('Location: ' . BASE_URL . 'venta/index');
            exit;
        }
        
        $detalles    = $modelo->getDetalles($id);
        $configModel = $this->model('Configuracion');
        
        $data = [
            'venta'    => $venta_actual,
            'detalles' => $detalles,
            'config'   => $configModel->getAll()
        ];
        
        // Cargar vista PDF (sin layout del sistema)
        require_once '../app/views/ventas/comprobante_pdf.php';
        exit;
    }

    public function save() {
        if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['id_cliente'])) {
            $this->validateCsrf();
            $modelo = $this->model('Venta');
            
            $cajaModel = $this->model('Caja');
            $cajaAbierta = $cajaModel->getCajaAbiertaPorUsuario($_SESSION['user_id']);
            if(!$cajaAbierta) {
                $_SESSION['error_pos'] = "Error: La caja no está abierta.";
                header('Location: ' . BASE_URL . 'venta/pos');
                exit;
            }
            
            $tipo_comprobante = $_POST['tipo_comprobante'];
            $serie = 'T001';
            if ($tipo_comprobante === 'Boleta') $serie = 'B001';
            if ($tipo_comprobante === 'Factura') $serie = 'F001';

            // SUNAT: Obtener el último correlativo real de la serie
            $db = new Database();
            $conn = $db->getConnection();
            $stmt = $conn->prepare("SELECT MAX(CAST(num_comprobante AS UNSIGNED)) as ultimo FROM ventas WHERE serie_comprobante = ?");
            $stmt->execute([$serie]);
            $row = $stmt->fetch(PDO::FETCH_ASSOC);
            $ultimo = $row['ultimo'] ? (int)$row['ultimo'] : 0;
            $nuevo_num = $ultimo + 1;
            
            $numero_t = str_pad($nuevo_num, 6, '0', STR_PAD_LEFT);
            $total = (float)$_POST['total_venta'];
            $id_cliente = (int)$_POST['id_cliente'];

            // Boleta y Factura requieren cliente identificado; Factura además requiere RUC
            if (!in_array($tipo_comprobante, ['Ticket', 'Boleta', 'Factura'], true)) {
                $_SESSION['error_pos'] = "Error: Tipo de comprobante no válido.";
                header('Location: ' . BASE_URL . 'venta/pos');
                exit;
            }
            if ($tipo_comprobante !== 'Ticket') {
                $stCli = $conn->prepare("SELECT tipo_documento, num_documento FROM clientes WHERE id = ? AND estado = 1");
                $stCli->execute([$id_cliente]);
                $cliDoc = $stCli->fetch(PDO::FETCH_ASSOC);
                $esRuc = $cliDoc && (strtoupper((string)$cliDoc['tipo_documento']) === 'RUC' || preg_match('/^(10|15|17|20)\d{9}$/', (string)$cliDoc['num_documento']));
                $errorComp = null;
                if (!$cliDoc || $id_cliente == 1) {
                    $errorComp = "Error: La $tipo_comprobante requiere seleccionar un cliente identificado.";
                } elseif ($tipo_comprobante === 'Factura' && !$esRuc) {
                    $errorComp = "Error: La Factura requiere un cliente con RUC.";
                }
                if ($errorComp) {
                    $_SESSION['error_pos'] = $errorComp;
                    header('Location: ' . BASE_URL . 'venta/pos');
                    exit;
                }
            }
            
            // 1. Procesar y blindar detalles con precios oficiales de la base de datos
            $detalles = [];
            $subtotalSuma = 0.00;
            $productos = $_POST['producto_id'] ?? [];
            $dbPrecios = new Database();
            $connPrecios = $dbPrecios->getConnection();
            $stmtProd = $connPrecios->prepare("SELECT id, nombre_comercial, precio_venta, precio_fraccion, fraccionable, unidades_por_caja FROM productos WHERE id = ? AND estado = 1");

            foreach ($productos as $i => $id_prod) {
                $idProd = (int)$id_prod;
                $cantRaw = str_replace(',', '.', trim($_POST['cantidad'][$i] ?? '0'));
                $cant = (float)preg_replace('/[^\d.]/', '', $cantRaw);
                if ($idProd <= 0 || $cant <= 0) continue;

                $stmtProd->execute([$idProd]);
                $pInfo = $stmtProd->fetch(PDO::FETCH_ASSOC);
                if (!$pInfo) continue;

                $tipoUnidad = ($_POST['tipo_unidad'][$i] ?? 'CAJA') === 'FRACCION' ? 'FRACCION' : 'CAJA';
                if ($tipoUnidad === 'FRACCION' && (int)$pInfo['fraccionable'] === 1) {
                    $preUnit = (float)$pInfo['precio_fraccion'];
                    if ($preUnit <= 0 && (int)$pInfo['unidades_por_caja'] > 0) {
                        $preUnit = round((float)$pInfo['precio_venta'] / (int)$pInfo['unidades_por_caja'], 2);
                    }
                } else {
                    $tipoUnidad = 'CAJA';
                    $preUnit = (float)$pInfo['precio_venta'];
                }

                $subItem = round($cant * $preUnit, 2);
                $subtotalSuma += $subItem;

                $detalles[] = [
                    'id_producto' => $idProd,
                    'cantidad' => $cant,
                    'precio_unitario' => $preUnit,
                    'subtotal' => $subItem,
                    'tipo_unidad' => $tipoUnidad
                ];
            }

            if (count($detalles) === 0) {
                $_SESSION['error_pos'] = "Carrito de compras vacío o sin productos válidos.";
                header('Location: ' . BASE_URL . 'venta/pos');
                exit;
            }

            // 2. Trazabilidad y validación del descuento
            $puntoModel = $this->model('Punto');
            $configPuntos = $puntoModel->getConfig();
            $puntos_usados = isset($_POST['puntos_usados']) ? max(0, (int)$_POST['puntos_usados']) : 0;
            $descuentoSolicitado = isset($_POST['descuento_venta']) ? max(0.00, (float)$_POST['descuento_venta']) : 0.00;

            $tipo_descuento = null;
            $motivo_descuento = null;
            $descuento = 0.00;

            if ($descuentoSolicitado > 0) {
                if ($puntos_usados > 0 && $id_cliente != 1) {
                    $valorCanje = (float)($configPuntos['valor_canje'] ?? 0.10);
                    $descuentoMaxPuntos = round($puntos_usados * $valorCanje, 2);
                    $descuento = min($subtotalSuma, $descuentoMaxPuntos);
                    $tipo_descuento = 'Puntos';
                    $motivo_descuento = "Canje de $puntos_usados puntos (-S/ " . number_format($descuento, 2) . ")";
                } else {
                    $puntos_usados = 0;
                    $descuento = min($subtotalSuma, $descuentoSolicitado);
                    $tipo_descuento = 'Manual';
                    $motivo_descuento = mb_substr(trim($_POST['motivo_descuento'] ?? ''), 0, 255);
                    if ($motivo_descuento === '') {
                        $_SESSION['error_pos'] = "Error: Debe indicar el motivo del descuento manual.";
                        header('Location: ' . BASE_URL . 'venta/pos');
                        exit;
                    }
                }
            } else {
                $puntos_usados = 0;
            }

            // 3. Recálculo oficial de Total, IGV y Subtotal neto
            $configModel = $this->model('Configuracion');
            $total = round(max(0, $subtotalSuma - $descuento), 2);
            $igvPct = (float)($configModel->get('igv') ?: 18);
            $factorIgv = ($igvPct / 100) + 1;
            $igv = round($total - ($total / $factorIgv), 2);
            $subtotalNeto = round($total - $igv, 2);

            // 4. Fidelización de puntos calculada sobre el total real
            $puntos_ganados = 0;
            if ($id_cliente != 1 && !empty($configPuntos['habilitado'])) {
                $consumoBase = (float)($configPuntos['consumo_base'] ?? 10);
                if ($consumoBase > 0) {
                    $puntos_ganados = (int)floor($total / $consumoBase);
                }
            }

            // 5. Procesamiento y verificación estricta de formas de pago
            $metodo_pago = trim($_POST['metodo_pago'] ?? 'Efectivo');
            $monto_efectivo = 0.00;
            $monto_transferencia = 0.00;
            $monto_tarjeta = 0.00;
            $num_operacion_trans = !empty($_POST['num_operacion_trans']) ? trim($_POST['num_operacion_trans']) : null;
            $num_operacion_tarj  = !empty($_POST['num_operacion_tarj']) ? trim($_POST['num_operacion_tarj']) : null;

            if ($metodo_pago === 'Efectivo') {
                $monto_efectivo = $total;
                $pago_recibido = isset($_POST['pago_recibido']) && is_numeric($_POST['pago_recibido']) ? (float)$_POST['pago_recibido'] : 0.00;
                
                if ($pago_recibido < $total) {
                    $_SESSION['error_pos'] = "Error: El efectivo recibido (S/ " . number_format($pago_recibido, 2) . ") debe ser igual o mayor al total (S/ " . number_format($total, 2) . ").";
                    header('Location: ' . BASE_URL . 'venta/pos');
                    exit;
                }
                $vuelto = round($pago_recibido - $total, 2);
            } elseif ($metodo_pago === 'Yape/Plin') {
                $monto_transferencia = $total;
                $pago_recibido = $total;
                $vuelto = 0.00;
            } elseif ($metodo_pago === 'Tarjeta') {
                $monto_tarjeta = $total;
                $pago_recibido = $total;
                $vuelto = 0.00;
            } elseif ($metodo_pago === 'Mixto') {
                $monto_efectivo = isset($_POST['monto_efectivo']) ? max(0, (float)$_POST['monto_efectivo']) : 0.00;
                $monto_transferencia = isset($_POST['monto_transferencia']) ? max(0, (float)$_POST['monto_transferencia']) : 0.00;
                $monto_tarjeta = isset($_POST['monto_tarjeta']) ? max(0, (float)$_POST['monto_tarjeta']) : 0.00;
                
                $sumaMixta = round($monto_efectivo + $monto_transferencia + $monto_tarjeta, 2);
                if (abs($sumaMixta - $total) > 0.01) {
                    $_SESSION['error_pos'] = "Error en Pago Mixto: La suma de montos (S/ $sumaMixta) no coincide con el total real de la venta (S/ $total).";
                    header('Location: ' . BASE_URL . 'venta/pos');
                    exit;
                }

                $pago_recibido_efe = isset($_POST['pago_recibido']) && is_numeric($_POST['pago_recibido']) ? (float)$_POST['pago_recibido'] : $monto_efectivo;
                if ($monto_efectivo > 0 && $pago_recibido_efe < $monto_efectivo) {
                    $_SESSION['error_pos'] = "Error: El efectivo recibido (S/ $pago_recibido_efe) es menor a la porción en efectivo (S/ $monto_efectivo).";
                    header('Location: ' . BASE_URL . 'venta/pos');
                    exit;
                }
                $vuelto = $monto_efectivo > 0 ? round($pago_recibido_efe - $monto_efectivo, 2) : 0.00;
                $pago_recibido = $pago_recibido_efe + $monto_transferencia + $monto_tarjeta;
            } else {
                $monto_efectivo = $total;
                $pago_recibido = $total;
                $vuelto = 0.00;
            }

            $cabecera = [
                'caja_id' => $cajaAbierta['id'],
                'id_cliente' => $id_cliente,
                'tipo_comprobante' => $tipo_comprobante,
                'serie_comprobante' => $serie,
                'num_comprobante' => $numero_t,
                'subtotal' => $subtotalNeto,
                'descuento' => $descuento,
                'tipo_descuento' => $tipo_descuento,
                'motivo_descuento' => $motivo_descuento,
                'igv' => $igv,
                'total' => $total,
                'monto_efectivo' => $monto_efectivo,
                'monto_transferencia' => $monto_transferencia,
                'monto_tarjeta' => $monto_tarjeta,
                'num_operacion_trans' => $num_operacion_trans,
                'num_operacion_tarj' => $num_operacion_tarj,
                'metodo_pago' => $metodo_pago,
                'pago_recibido' => $pago_recibido,
                'vuelto' => $vuelto,
                'puntos_ganados' => $puntos_ganados,
                'puntos_usados' => $puntos_usados,
                'medico_cmp' => $_POST['medico_cmp'] ?? null
            ];
            
            if (count($detalles) > 0) {
                $id_venta = $modelo->registrarVenta($cabecera, $detalles, $_SESSION['user_id']);
                if ($id_venta) {
                    if($id_cliente != 1) {
                        if ($puntos_ganados > 0) {
                            $puntoModel->registrarMovimiento(
                                $id_cliente,
                                $_SESSION['user_id'],
                                'ACUMULACION',
                                $puntos_ganados,
                                "Compra {$cabecera['tipo_comprobante']} {$cabecera['serie_comprobante']}-{$cabecera['num_comprobante']}",
                                $id_venta
                            );
                        }
                        if ($puntos_usados > 0) {
                            $puntoModel->registrarMovimiento(
                                $id_cliente,
                                $_SESSION['user_id'],
                                'CANJE',
                                -$puntos_usados,
                                "Canje de puntos en {$cabecera['tipo_comprobante']} {$cabecera['serie_comprobante']}-{$cabecera['num_comprobante']}",
                                $id_venta
                            );
                        }
                    }

                    // Generar XML SUNAT si es Boleta o Factura
                    if ($tipo_comprobante === 'Boleta' || $tipo_comprobante === 'Factura') {
                        require_once '../app/services/SunatUblGenerator.php';
                        $configModel = $this->model('Configuracion');
                        $empresa = $configModel->getAll();

                        // Enriquecer los detalles con el nombre del producto y valores calculados
                        $detallesXml = [];
                        $db2 = new Database();
                        $conn2 = $db2->getConnection();
                        foreach ($detalles as $detItem) {
                            $pq = $conn2->prepare("SELECT nombre_comercial FROM productos WHERE id = ?");
                            $pq->execute([$detItem['id_producto']]);
                            $pName = $pq->fetchColumn();
                            $detallesXml[] = [
                                'nombre_comercial' => $pName ?: 'Producto ' . $detItem['id_producto'],
                                'cantidad'         => $detItem['cantidad'],
                                'precio_unitario'  => $detItem['precio_unitario'],
                                'subtotal'         => $detItem['subtotal'],
                                'tipo_unidad'      => $detItem['tipo_unidad']
                            ];
                        }

                        // Obtener datos fiscales reales del cliente para SUNAT
                        $cliQ = $conn2->prepare("SELECT num_documento, tipo_documento, nombres FROM clientes WHERE id = ?");
                        $cliQ->execute([$id_cliente]);
                        $cliData = $cliQ->fetch(PDO::FETCH_ASSOC);

                        $nombreCliente = !empty($cliData['nombres']) ? $cliData['nombres'] : 'PUBLICO GENERAL';
                        $docCliente    = !empty($cliData['num_documento']) ? $cliData['num_documento'] : '00000000';
                        $tipoDocCli    = $cliData['tipo_documento'] ?? 'DNI';
                        
                        $sunatTipoDoc = '1'; // Default DNI
                        if ($tipoDocCli === 'RUC') {
                            $sunatTipoDoc = '6';
                        } elseif ($tipoDocCli === 'CE' || $tipoDocCli === 'Pasaporte') {
                            $sunatTipoDoc = '4';
                        } elseif ($id_cliente == 1) {
                            $sunatTipoDoc = '0';
                        }

                        // Usar los datos de cabecera para generar el XML
                        $ventaXml = $cabecera;
                        $ventaXml['igv']              = (float)$_POST['igv_venta'];
                        $ventaXml['total']            = (float)$_POST['total_venta'];
                        $ventaXml['cliente']          = $nombreCliente;
                        $ventaXml['doc_cliente']      = $docCliente;
                        $ventaXml['tipo_doc_cliente'] = $sunatTipoDoc;
                        $ventaXml['fecha_venta']      = date('Y-m-d H:i:s');

                        try {
                            // 1. GENERAR XML UBL 2.1
                            require_once '../app/services/SunatUblGenerator.php';
                            $xmlFilename = SunatUblGenerator::generarXML($ventaXml, $detallesXml, $empresa);

                            $xmlDir      = (defined('BASE_PATH') ? BASE_PATH : $_SERVER['DOCUMENT_ROOT'] . '/sistema-botica/') . 'public/sunat/xml/';
                            $xmlFilePath = $xmlDir . $xmlFilename;

                            $nuevoEstado = 'Generado Local';

                            // 2. ENVIAR A SUNAT (si está habilitado)
                            $sunatHabilitado = ($empresa['sunat_habilitado']['valor'] ?? '0') === '1';
                            if ($sunatHabilitado) {
                                require_once '../app/services/SunatApiClient.php';
                                $sunatClient = new SunatApiClient($empresa);

                                if ($sunatClient->estaHabilitado()) {
                                    $resultado = $sunatClient->enviarComprobante($xmlFilePath);
                                    if ($resultado['success']) {
                                        $nuevoEstado = 'Aceptado SUNAT';
                                        // Guardar CDR si viene
                                        if (!empty($resultado['cdr'])) {
                                            $cdrPath = $xmlDir . str_replace('.xml', '_CDR.zip', $xmlFilename);
                                            file_put_contents($cdrPath, base64_decode($resultado['cdr']));
                                        }
                                    } else {
                                        $nuevoEstado = 'Rechazado: ' . substr($resultado['descripcion'], 0, 50);
                                        error_log("SUNAT Reject [{$resultado['codigo']}]: " . $resultado['descripcion']);
                                    }
                                }
                            }

                            // 3. ACTUALIZAR ESTADO EN BD
                            $conn2->prepare("UPDATE ventas SET estado_sunat = ?, hash_sunat = ? WHERE id = ?")
                                  ->execute([$nuevoEstado, $xmlFilename, $id_venta]);

                        } catch(Exception $xe) {
                            error_log("Error SUNAT: " . $xe->getMessage());
                        }
                    }

                    $_SESSION['mensaje_pos'] = "Venta Procesada Exitosamente. {$serie}-{$numero_t}";
                    $_SESSION['last_ticket'] = $id_venta;
                } else {
                    $_SESSION['error_pos'] = "Error de Servidor: Stock Insuficiente o Lote en conflicto FEFO.";
                }
            } else {
                $_SESSION['error_pos'] = "Carrito de compras vacío.";
            }
        }
        header('Location: ' . BASE_URL . 'venta/pos');
    }

    public function anular($id = null) {
        $this->requireRole(1, 'venta/index');

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $_SESSION['error'] = 'Método no permitido.';
            header('Location: ' . BASE_URL . 'venta/index');
            exit;
        }

        $token = $_POST['csrf_token'] ?? '';
        if (empty($token) || !hash_equals($_SESSION['csrf_token'] ?? '', $token)) {
            $_SESSION['error'] = 'Token CSRF inválido o expirado.';
            header('Location: ' . BASE_URL . 'venta/index');
            exit;
        }

        $saleId = (int)($_POST['id_venta'] ?? $id ?? 0);
        if ($saleId <= 0) {
            $_SESSION['error'] = 'ID de venta inválido.';
            header('Location: ' . BASE_URL . 'venta/index');
            exit;
        }

        $modelo = $this->model('Venta');
        if ($modelo->anularVenta($saleId, $_SESSION['user_id'])) {
            $this->logAccion('Ventas', 'ANULAR', "Anulación de venta ID #$saleId por el usuario.");
            $_SESSION['success'] = "Venta anulada correctamente. El stock ha sido devuelto al inventario.";
        } else {
            $_SESSION['error'] = "No se pudo anular la venta. Verifique que no esté ya anulada.";
        }
        header('Location: ' . BASE_URL . 'venta/index');
        exit;
    }
}
