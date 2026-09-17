<?php
class ProductoController extends Controller {

    public function __construct() {
        $this->requireRole([1, 2, 4], 'venta/pos');
    }

    public function index() {
        $modelo = $this->model('Producto');
        $catModel = $this->model('Categoria');
        $labModel = $this->model('Laboratorio');

        $filtros = [
            'search'         => !empty($_GET['search']) ? trim($_GET['search']) : '',
            'id_categoria'   => !empty($_GET['id_categoria']) ? (int)$_GET['id_categoria'] : '',
            'id_laboratorio' => !empty($_GET['id_laboratorio']) ? (int)$_GET['id_laboratorio'] : '',
            'estado'         => isset($_GET['estado']) && $_GET['estado'] !== '' ? (int)$_GET['estado'] : ''
        ];

        $page = isset($_GET['page']) && is_numeric($_GET['page']) ? max(1, (int)$_GET['page']) : 1;
        $limit = isset($_GET['limit']) && in_array((int)$_GET['limit'], [15, 25, 50, 100]) ? (int)$_GET['limit'] : 25;
        $offset = ($page - 1) * $limit;

        $totalRegistros = $modelo->contarProductosAdmin($filtros);
        $totalPaginas = max(1, ceil($totalRegistros / $limit));
        if ($page > $totalPaginas) {
            $page = $totalPaginas;
            $offset = ($page - 1) * $limit;
        }

        $productos = $modelo->getPaginadosAdmin($filtros, $limit, $offset);

        $this->view('productos/index', [
            'title'           => 'Productos',
            'productos'       => $productos,
            'categorias'      => $catModel->getAll(),
            'laboratorios'    => $labModel->getAll(),
            'filtros'         => $filtros,
            'pagina_actual'   => $page,
            'total_paginas'   => $totalPaginas,
            'total_registros' => $totalRegistros,
            'limit'           => $limit
        ]);
    }

    public function create() {
        // Cargar combos para el formulario
        $catModel = $this->model('Categoria');
        $labModel = $this->model('Laboratorio');
        
        $data = [
            'title' => 'Nuevo Producto',
            'categorias' => $catModel->getAll(),
            'laboratorios' => $labModel->getAll(),
            'producto' => null // null significa que es creación
        ];
        
        $this->view('productos/form', $data);
    }
    
    public function edit($id) {
        $modelo = $this->model('Producto');
        $catModel = $this->model('Categoria');
        $labModel = $this->model('Laboratorio');
        
        $data = [
            'title' => 'Editar Producto',
            'categorias' => $catModel->getAll(),
            'laboratorios' => $labModel->getAll(),
            'producto' => $modelo->getById($id)
        ];
        
        $this->view('productos/form', $data);
    }

    public function save() {
        
        if ($_SERVER['REQUEST_METHOD'] == 'POST') {
            $this->validateCsrf();
            $modelo = $this->model('Producto');
            
            $condicion_venta = $_POST['condicion_venta'] ?? 'Venta Libre';
            $requiere_receta = ($condicion_venta === 'Receta Médica Simple' || $condicion_venta === 'Receta Médica Retenida') ? 1 : 0;
            
            $data = [
                'codigo_barras' => $_POST['codigo_barras'] ?: null,
                'nombre_generico' => $_POST['nombre_generico'],
                'nombre_comercial' => $_POST['nombre_comercial'],
                'concentracion' => $_POST['concentracion'],
                'forma_farmaceutica' => $_POST['forma_farmaceutica'],
                'registro_sanitario' => $_POST['registro_sanitario'] ?: null,
                'condicion_venta' => $condicion_venta,
                'id_laboratorio' => empty($_POST['id_laboratorio']) ? null : $_POST['id_laboratorio'],
                'id_categoria' => empty($_POST['id_categoria']) ? null : $_POST['id_categoria'],
                'precio_compra' => $_POST['precio_compra'],
                'precio_venta' => $_POST['precio_venta'],
                'margen_ganancia' => $_POST['margen_ganancia'],
                'unidad_medida' => $_POST['unidad_medida'],
                'requiere_receta' => $requiere_receta,
                'stock_minimo' => $_POST['stock_minimo'] ?: 10,
                'fraccionable' => isset($_POST['fraccionable']) ? 1 : 0,
                'unidades_por_caja' => isset($_POST['fraccionable']) && !empty($_POST['unidades_por_caja']) ? $_POST['unidades_por_caja'] : 1,
                'unidad_fraccion' => isset($_POST['fraccionable']) && !empty($_POST['unidad_fraccion']) ? $_POST['unidad_fraccion'] : null,
                'precio_fraccion' => isset($_POST['fraccionable']) && !empty($_POST['precio_fraccion']) ? $_POST['precio_fraccion'] : 0.00,
                'id' => $_POST['id'] ?? null
            ];
            
            if (empty($data['id'])) {
                $modelo->create($data);
                $this->logAccion('Productos', 'CREAR', "Nuevo producto creado: " . $data['nombre_comercial']);
            } else {
                $modelo->update($data);
                $this->logAccion('Productos', 'EDITAR', "Producto ID #" . $data['id'] . " editado. Precios: S/ " . $data['precio_venta'] . " (Caja) / S/ " . $data['precio_fraccion'] . " (Frac)");
            }
        }
        header('Location: ' . BASE_URL . 'producto/index');
    }

    public function toggle($id = null) {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $_SESSION['error'] = 'Método no permitido.';
            header('Location: ' . BASE_URL . 'producto/index');
            exit;
        }

        $this->validateCsrf();

        $productId = (int)($_POST['id'] ?? $id ?? 0);
        if ($productId <= 0) {
            $_SESSION['error'] = 'ID de producto inválido.';
            header('Location: ' . BASE_URL . 'producto/index');
            exit;
        }

        $modelo = $this->model('Producto');
        if ($modelo->toggleEstado($productId)) {
            $this->logAccion('Productos', 'ESTADO', "Se cambió el estado (Activo/Inactivo) del producto ID #" . $productId);
            $_SESSION['mensaje'] = 'Estado del producto actualizado correctamente.';
        } else {
            $_SESSION['error'] = 'No se pudo actualizar el estado del producto.';
        }
        
        header('Location: ' . BASE_URL . 'producto/index');
        exit;
    }
}
