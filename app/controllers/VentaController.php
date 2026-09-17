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
        
        $data = [
            'title' => 'Punto de Venta',
            'clientes' => $cliModel->getAll(),
            'productos' => $prodModel->getAll(),
            'igv' => $configModel->get('igv')
        ];
        
        // Vista directa para el POS (usa layout de main)
        $this->view('ventas/pos', $data);
    }
    
    public function index() {
        $modelo = $this->model('Venta');
        $configModel = $this->model('Configuracion');
        $this->view('ventas/index', [
            'title'  => 'Historial de Ventas',
            'ventas' => $modelo->getAll(),
            'config' => $configModel->getAll()
        ]);
    }

    public function ticket($id) {
        $modelo = $this->model('Venta');
        $ventas = $modelo->getAll();
        
        $venta_actual = null;
        foreach($ventas as $v) {
            if($v['id'] == $id) {
                $venta_actual = $v; break;
            }
        }
        
        if(!$venta_actual) die("Ticket no encontrado.");
        
        $detalles = $modelo->getDetalles($id);
        $configModel = $this->model('Configuracion');
        
        $data = [
            'venta'    => $venta_actual,
            'detalles' => $detalles,
            'config'   => $configModel->getAll()
        ];
        
        // Cargar vista HTML plana (sin layout)
        require_once '../app/views/ventas/ticket.php';
    }

    public function pdf($id) {
        $modelo = $this->model('Venta');
        $ventas = $modelo->getAll();
        
        $venta_actual = null;
        foreach($ventas as $v) {
            if($v['id'] == $id) { $venta_actual = $v; break; }
        }
        
        if(!$venta_actual) die("Comprobante no encontrado.");
        
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
            
            $puntos_ganados = 0;
            if($id_cliente != 1) {
                // 1 Sol = 1 Punto (basado en el total final)
                $puntos_ganados = floor($total);
            }
            $puntos_usados = isset($_POST['puntos_usados']) ? (int)$_POST['puntos_usados'] : 0;
            $descuento = isset($_POST['descuento_venta']) ? (float)$_POST['descuento_venta'] : 0.00;
            
            $cabecera = [
                'caja_id' => $cajaAbierta['id'],
                'id_cliente' => $id_cliente,
                'tipo_comprobante' => $tipo_comprobante,
                'serie_comprobante' => $serie,
                'num_comprobante' => $numero_t,
                'subtotal' => (float)$_POST['subtotal_venta'],
                'descuento' => $descuento,
                'igv' => (float)$_POST['igv_venta'],
                'total' => $total,
                'metodo_pago' => $_POST['metodo_pago'],
                'pago_recibido' => (float)($_POST['pago_recibido'] ?: $total),
                'vuelto' => (float)($_POST['vuelto_venta'] ?: 0.00),
                'puntos_ganados' => $puntos_ganados,
                'puntos_usados' => $puntos_usados,
                'medico_cmp' => $_POST['medico_cmp'] ?? null
            ];
            
            // Detalles paralelos por arrays
            $detalles = [];
            $productos = $_POST['producto_id'] ?? [];
            foreach ($productos as $i => $id_prod) {
                if(empty($id_prod) || empty($_POST['cantidad'][$i])) continue;
                $detalles[] = [
                    'id_producto' => (int)$id_prod,
                    'cantidad' => (int)$_POST['cantidad'][$i],
                    'precio_unitario' => (float)$_POST['precio_d'][$i],
                    'subtotal' => (float)$_POST['subtotal_d'][$i],
                    'tipo_unidad' => $_POST['tipo_unidad'][$i] ?? 'CAJA'
                ];
            }
            
            if (count($detalles) > 0) {
                $id_venta = $modelo->registrarVenta($cabecera, $detalles, $_SESSION['user_id']);
                if ($id_venta) {
                    if($id_cliente != 1) {
                        $cliModel = $this->model('Cliente');
                        $delta = $puntos_ganados - $puntos_usados;
                        $cliModel->actualizarPuntos($id_cliente, $delta);
                    }

                    // Generar XML SUNAT si es Boleta o Factura
                    if ($tipo_comprobante === 'Boleta' || $tipo_comprobante === 'Factura') {
                        require_once '../app/services/SunatUblGenerator.php';
                        $configModel = $this->model('Configuracion');
                        $empresa = $configModel->getAll();

                        // Enriquecer los detalles con el nombre del producto
                        $detallesXml = [];
                        $db2 = new Database();
                        $conn2 = $db2->getConnection();
                        for ($i = 0; $i < count($_POST['producto_id']); $i++) {
                            $pq = $conn2->prepare("SELECT nombre_comercial FROM productos WHERE id = ?");
                            $pq->execute([$_POST['producto_id'][$i]]);
                            $pName = $pq->fetchColumn();
                            $detallesXml[] = [
                                'nombre_comercial' => $pName,
                                'cantidad'         => $_POST['cantidad'][$i],
                                'precio_unitario'  => $_POST['precio_d'][$i],
                                'subtotal'         => $_POST['subtotal_d'][$i],
                                'tipo_unidad'      => $_POST['tipo_unidad'][$i] ?? 'CAJA'
                            ];
                        }

                        // Usar los datos de cabecera para generar el XML
                        $ventaXml = $cabecera;
                        $ventaXml['igv']         = (float)$_POST['igv_venta'];
                        $ventaXml['total']        = (float)$_POST['total_venta'];
                        $ventaXml['cliente']      = $venta_cliente ?? 'PUBLICO GENERAL';
                        $ventaXml['fecha_venta']  = date('Y-m-d H:i:s');

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
