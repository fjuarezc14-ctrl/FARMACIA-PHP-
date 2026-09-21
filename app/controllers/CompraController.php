<?php
class CompraController extends Controller {

    public function __construct() {
        $this->requireRole([1, 2, 4], 'venta/pos');
    }

    public function index() {
        $modelo = $this->model('Compra');
        $compras = $modelo->getAll();
        
        $this->view('compras/index', ['title' => 'Historial de Compras', 'compras' => $compras]);
    }

    public function create() {
        $provModel = $this->model('Proveedor');
        $prodModel = $this->model('Producto');
        
        $data = [
            'title' => 'Registrar Nueva Compra',
            'proveedores' => $provModel->getAll(),
            // Productos se cargarán en JS, o los pasamos todos para un array rápido
            'productos' => $prodModel->getAll()
        ];
        
        $this->view('compras/create', $data);
    }

    /**
     * Alias de create() para asegurar retrocompatibilidad con enlaces antiguos
     */
    public function nueva() {
        $this->create();
    }
    
    public function detalle($id) {
        $modelo = $this->model('Compra');
        $detalles = $modelo->getDetallesConLotes($id);
        echo json_encode($detalles);
        exit;
    }

    public function devolver($id) {
        $modelo = $this->model('Compra');
        $compra = $modelo->getCompraPorId($id);
        if (!$compra) {
            $_SESSION['error'] = "Compra no encontrada.";
            header('Location: ' . BASE_URL . 'compra/index');
            exit;
        }
        
        $detalles = $modelo->getDetallesConLotes($id);
        
        $this->view('compras/devolucion', [
            'title' => 'Devolver Productos al Proveedor',
            'compra' => $compra,
            'detalles' => $detalles
        ]);
    }

    public function save() {
        if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['id_proveedor'])) {
            $this->validateCsrf();
            $modelo = $this->model('Compra');
            
            // Sanitización y blindaje de cabecera
            $totalCompra = (float)str_replace(',', '.', preg_replace('/[^\d.,\-]/', '', $_POST['total_compra'] ?? 0));
            $impuesto = (float)str_replace(',', '.', preg_replace('/[^\d.,\-]/', '', $_POST['impuesto'] ?? 0));

            $id_proveedor = (int)($_POST['id_proveedor'] ?? 0);
            if ($id_proveedor <= 0) {
                $provModel = $this->model('Proveedor');
                $todosProv = $provModel->getAll();
                if (!empty($todosProv)) {
                    $id_proveedor = (int)$todosProv[0]['id'];
                }
            }

            $cabecera = [
                'id_proveedor' => $id_proveedor,
                'tipo_comprobante' => trim($_POST['tipo_comprobante'] ?? 'Factura'),
                'serie_comprobante' => trim($_POST['serie_comprobante'] ?? ''),
                'num_comprobante' => trim($_POST['num_comprobante'] ?? ''),
                'fecha_compra' => !empty($_POST['fecha_compra']) ? $_POST['fecha_compra'] : date('Y-m-d'),
                'impuesto' => $impuesto,
                'total' => $totalCompra,
                'estado' => $_POST['estado'] ?? 'Completada'
            ];
            
            // 2. Detalles de los productos (llegan en arrays paralelos)
            $detalles = [];
            $productos = $_POST['producto_id'] ?? [];
            foreach ($productos as $i => $id_prod) {
                $idProd = (int)$id_prod;
                $cant = (int)preg_replace('/[^\d]/', '', $_POST['cantidad'][$i] ?? 0);
                if ($idProd <= 0 || $cant <= 0) continue;
                
                $preUnit = (float)str_replace(',', '.', preg_replace('/[^\d.,\-]/', '', $_POST['precio_c_unitario'][$i] ?? 0));
                $sub = (float)str_replace(',', '.', preg_replace('/[^\d.,\-]/', '', $_POST['subtotal'][$i] ?? ($cant * $preUnit)));

                $lote = trim($_POST['lote'][$i] ?? '');
                if (empty($lote) || $lote === '0') $lote = 'P. SIN LOTE';

                $venc = !empty($_POST['vencimiento'][$i]) ? trim($_POST['vencimiento'][$i]) : '2099-12-31';
                $hoy = date('Y-m-d');
                if ($venc !== '2099-12-31' && $venc < $hoy) {
                    $_SESSION['error'] = "Validación Sanitaria Rechazada: El lote ingresado '$lote' tiene fecha de vencimiento expirada ($venc). No se permite el ingreso de medicamentos caducados.";
                    header('Location: ' . BASE_URL . 'compra/create');
                    exit;
                }

                $detalles[] = [
                    'id_producto' => $idProd,
                    'cantidad' => $cant,
                    'precio_unitario' => $preUnit,
                    'subtotal' => $sub,
                    'lote' => $lote,
                    'vencimiento' => $venc,
                    'actualizar_precio' => isset($_POST['actualizar_precio']) ? 1 : 0
                ];
            }
            
            if (count($detalles) > 0) {
                try {
                    $resultado = $modelo->registrarCompra($cabecera, $detalles, $_SESSION['user_id']);
                    if ($resultado) {
                        $logMsg = ($cabecera['estado'] == 'Pendiente') ? "Registro de Orden de Compra Pendiente" : "Registro de Compra con Ingreso Directo";
                        $this->logAccion('Compras', 'CREAR', "$logMsg. Prov: " . $cabecera['id_proveedor'] . ", Total: " . $cabecera['total'], $cabecera['total']);
                        $_SESSION['mensaje'] = "Compra y Lotes generados correctamente.";
                    } else {
                        $_SESSION['error'] = "Error al registrar la transacción en base de datos.";
                    }
                } catch (Exception $e) {
                    error_log("[CompraController::save] Error: " . $e->getMessage());
                    $_SESSION['error'] = "Ocurrió un error inesperado al procesar la compra: " . $e->getMessage();
                }
            } else {
                $_SESSION['error'] = "Debe agregar al menos un producto válido con cantidad mayor a 0.";
            }
        }
        header('Location: ' . BASE_URL . 'compra/index');
        exit;
    }

    public function save_devolucion() {
        if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['id_compra'])) {
            $this->validateCsrf();
            $modelo = $this->model('Compra');
            
            $cabecera = [
                'id_compra' => (int)$_POST['id_compra'],
                'num_documento_prov' => $_POST['num_documento_prov'],
                'motivo' => $_POST['motivo'],
                'total_devuelto' => (float)$_POST['total_devolucion'],
                'fecha_devolucion' => $_POST['fecha_devolucion']
            ];
            
            $detalles = [];
            $productos = $_POST['producto_id'] ?? [];
            foreach ($productos as $i => $id_prod) {
                $cant = (int)$_POST['cantidad_dev'][$i];
                if ($cant <= 0) continue;
                
                $detalles[] = [
                    'id_producto' => (int)$id_prod,
                    'id_lote' => (int)$_POST['lote_id'][$i],
                    'cantidad' => $cant,
                    'precio_costo' => (float)$_POST['precio_costo'][$i],
                    'subtotal' => (float)$_POST['subtotal_dev'][$i]
                ];
            }
            
            if (count($detalles) > 0) {
                $resultado = $modelo->registrarDevolucion($cabecera, $detalles, $_SESSION['user_id']);
                if ($resultado === true) {
                    $this->logAccion('Compras', 'DEVOLUCION', "Nota de Crédito/Devolución de Compra ID #" . $cabecera['id_compra'] . ", NC: " . $cabecera['num_documento_prov'], $cabecera['total_devuelto']);
                    $_SESSION['mensaje'] = "Nota de Crédito y devolución de stock registradas.";
                } else {
                    $_SESSION['error'] = "Error: " . $resultado;
                }
            } else {
                $_SESSION['error'] = "No se marcó ningún producto para devolver.";
            }
        }
        header('Location: ' . BASE_URL . 'compra/index');
        exit;
    }

    public function recepcion($id) {
        $modelo = $this->model('Compra');
        $compra = $modelo->getCompraPorId($id);
        if (!$compra || $compra['estado'] !== 'Pendiente') {
            $_SESSION['error'] = "La compra no está pendiente de recepción.";
            header('Location: ' . BASE_URL . 'compra/index');
            exit;
        }
        
        $detalles = $modelo->getDetallesConLotes($id);
        
        $this->view('compras/recepcion', [
            'title' => 'Recibir Mercadería',
            'compra' => $compra,
            'detalles' => $detalles
        ]);
    }

    public function procesar_recepcion() {
        if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['id_compra'])) {
            $this->validateCsrf();
            $modelo = $this->model('Compra');
            $id_compra = (int)$_POST['id_compra'];
            
            $lotes_data = [];
            $hoy = date('Y-m-d');
            foreach ($_POST['detalle_id'] as $i => $id_det) {
                $venc = !empty($_POST['vencimiento'][$i]) ? trim($_POST['vencimiento'][$i]) : '2099-12-31';
                $lote = trim($_POST['lote'][$i] ?? 'P. SIN LOTE');
                if (empty($lote)) $lote = 'P. SIN LOTE';

                if ($venc !== '2099-12-31' && $venc < $hoy) {
                    $_SESSION['error'] = "Validación Sanitaria Rechazada: El lote '$lote' tiene fecha de vencimiento expirada ($venc). No se permite recibir mercadería caducada.";
                    header('Location: ' . BASE_URL . 'compra/recepcion/' . $id_compra);
                    exit;
                }

                $lotes_data[] = [
                    'id_detalle' => (int)$id_det,
                    'lote' => $lote,
                    'vencimiento' => $venc
                ];
            }
            
            $resultado = $modelo->procesarRecepcion($id_compra, $_SESSION['user_id'], $lotes_data);
            if ($resultado === true) {
                $this->logAccion('Compras', 'RECEPCION', "Recepción física de productos de la Orden ID #" . $id_compra);
                $_SESSION['mensaje'] = "Mercadería recibida y stock actualizado.";
            } else {
                $_SESSION['error'] = "Error: " . $resultado;
            }
        }
        header('Location: ' . BASE_URL . 'compra/index');
        exit;
    }
}
