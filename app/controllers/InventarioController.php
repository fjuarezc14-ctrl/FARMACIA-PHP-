<?php
class InventarioController extends Controller {

    public function __construct() {
        $this->requireRole([1, 2, 4], 'venta/pos');
    }

    public function lotes() {
        $modelo = $this->model('Inventario');
        $catModel = $this->model('Categoria');
        $labModel = $this->model('Laboratorio');

        $filtros = [
            'search'         => trim($_GET['search'] ?? ''),
            'alerta'         => trim($_GET['alerta'] ?? ''),
            'id_laboratorio' => !empty($_GET['id_laboratorio']) ? (int)$_GET['id_laboratorio'] : '',
            'id_categoria'   => !empty($_GET['id_categoria']) ? (int)$_GET['id_categoria'] : '',
            'stock'          => trim($_GET['stock'] ?? 'activos')
        ];

        $page = isset($_GET['page']) && is_numeric($_GET['page']) ? max(1, (int)$_GET['page']) : 1;
        $limit = isset($_GET['limit']) && in_array((int)$_GET['limit'], [15, 25, 50, 100]) ? (int)$_GET['limit'] : 25;
        $offset = ($page - 1) * $limit;

        $totalRegistros = $modelo->contarLotes($filtros);
        $totalPaginas = max(1, ceil($totalRegistros / $limit));
        if ($page > $totalPaginas) {
            $page = $totalPaginas;
            $offset = ($page - 1) * $limit;
        }

        $lotes = $modelo->getLotesPaginados($filtros, $limit, $offset);
        $kpis = $modelo->getResumenKpisLotes();

        $this->view('inventario/lotes', [
            'title'           => 'Control de Vencimientos (FEFO)',
            'lotes'           => $lotes,
            'kpis'            => $kpis,
            'filtros'         => $filtros,
            'categorias'      => $catModel->getAll(),
            'laboratorios'    => $labModel->getAll(),
            'pagina_actual'   => $page,
            'total_paginas'   => $totalPaginas,
            'total_registros' => $totalRegistros,
            'limit'           => $limit
        ]);
    }

    public function kardex() {
        $modelo = $this->model('Inventario');
        $prodModel = $this->model('Producto');
        
        $id_producto = isset($_GET['producto']) && !empty($_GET['producto']) ? (int)$_GET['producto'] : null;
        
        $movimientos = $modelo->getKardex($id_producto);
        $productos = $prodModel->getAll();
        
        $this->view('inventario/kardex', [
            'title' => 'Kardex General', 
            'movimientos' => $movimientos,
            'productos' => $productos,
            'filtro_producto' => $id_producto
        ]);
    }

    public function entrada_manual() {
        if ($_SERVER['REQUEST_METHOD'] == 'POST') {
            $this->validateCsrf();
            $id_producto = (int)($_POST['id_producto'] ?? 0);
            $cantidad = (int)preg_replace('/[^\d]/', '', $_POST['cantidad'] ?? 0);
            $lote = trim($_POST['lote'] ?? '') ?: 'P. SIN LOTE';
            $vencimiento = !empty($_POST['fecha_vencimiento']) ? $_POST['fecha_vencimiento'] : date('Y-m-d', strtotime('+365 days'));
            $motivo = "Ajuste/Ingreso Manual: " . trim($_POST['motivo'] ?? 'Ajuste de inventario');

            if ($id_producto <= 0 || $cantidad <= 0) {
                $_SESSION['error'] = "Debe seleccionar un producto válido y una cantidad mayor a 0.";
                header('Location: ' . BASE_URL . 'inventario/kardex');
                exit;
            }

            if ($vencimiento !== '2099-12-31' && $vencimiento < date('Y-m-d')) {
                $_SESSION['error'] = "Validación Sanitaria Rechazada: No se permite registrar ingresos manuales con lote expirado ($vencimiento).";
                header('Location: ' . BASE_URL . 'inventario/kardex');
                exit;
            }
            
            $db = new Database();
            $conn = $db->getConnection();
            if (!$conn) {
                $_SESSION['error'] = "No se pudo conectar a la base de datos.";
                header('Location: ' . BASE_URL . 'inventario/kardex');
                exit;
            }
            require_once dirname(__DIR__) . '/models/Inventario.php';
            $modelo = new Inventario($conn);
            
            try {
                $conn->beginTransaction();
                
                $modelo->registrarEntrada($id_producto, $_SESSION['user_id'], $cantidad, $motivo, $lote, $vencimiento);
                
                $conn->commit();
                $this->logAccion('Inventario', 'AJUSTE_STOCK', "Ajuste manual de stock: $motivo, Cant: $cantidad, Prod ID: $id_producto");
                $_SESSION['mensaje'] = "El ingreso de stock fue insertado en el Kardex y en Lotes satisfactoriamente.";
            } catch (Exception $e) {
                $conn->rollBack();
                error_log("[InventarioController::entrada_manual] Error: " . $e->getMessage());
                $_SESSION['error'] = "Hubo un problema al registrar la entrada de inventario: " . $e->getMessage();
            }
        }
        
        header('Location: ' . BASE_URL . 'inventario/kardex');
        exit;
    }
}

