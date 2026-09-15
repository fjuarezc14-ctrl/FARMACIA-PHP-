<?php
class ProductoController extends Controller {

    public function index() {
        if (!isset($_SESSION['user_id'])) { header('Location: ' . BASE_URL . 'auth/login'); exit; }
        
        $modelo = $this->model('Producto');
        $productos = $modelo->getAllAdmin();
        
        $this->view('productos/index', ['title' => 'Productos', 'productos' => $productos]);
    }

    public function create() {
        if (!isset($_SESSION['user_id'])) { header('Location: ' . BASE_URL . 'auth/login'); exit; }
        
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
        if (!isset($_SESSION['user_id'])) { header('Location: ' . BASE_URL . 'auth/login'); exit; }
        
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
        if (!isset($_SESSION['user_id'])) { header('Location: ' . BASE_URL . 'auth/login'); exit; }
        
        if ($_SERVER['REQUEST_METHOD'] == 'POST') {
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

    public function toggle($id) {
        if (!isset($_SESSION['user_id'])) { header('Location: ' . BASE_URL . 'auth/login'); exit; }
        
        $modelo = $this->model('Producto');
        $modelo->toggleEstado($id);
        $this->logAccion('Productos', 'ESTADO', "Se cambió el estado (Activo/Inactivo) del producto ID #" . $id);
        
        header('Location: ' . BASE_URL . 'producto/index');
    }
}
