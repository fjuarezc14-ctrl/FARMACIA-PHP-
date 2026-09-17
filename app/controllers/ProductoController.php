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
            
            // Sanitización y blindaje numérico robusto (evita caídas si se ingresan letras)
            $pCompra = (float)str_replace(',', '.', preg_replace('/[^\d.,\-]/', '', $_POST['precio_compra'] ?? 0));
            $pVenta = (float)str_replace(',', '.', preg_replace('/[^\d.,\-]/', '', $_POST['precio_venta'] ?? 0));
            $pMayor = (isset($_POST['precio_mayor']) && trim($_POST['precio_mayor']) !== '') 
                      ? (float)str_replace(',', '.', preg_replace('/[^\d.,\-]/', '', $_POST['precio_mayor'])) 
                      : null;
            $pMargen = (float)str_replace(',', '.', preg_replace('/[^\d.,\-]/', '', $_POST['margen_ganancia'] ?? 0));
            $pMargen = min(999999.99, max(0.00, $pMargen));
            $stockMin = (int)preg_replace('/[^\d]/', '', $_POST['stock_minimo'] ?? 10);
            if ($stockMin <= 0) $stockMin = 10;

            $fraccionable = isset($_POST['fraccionable']) ? 1 : 0;
            $uCaja = $fraccionable ? (int)preg_replace('/[^\d]/', '', $_POST['unidades_por_caja'] ?? 1) : 1;
            if ($uCaja <= 0) $uCaja = 1;
            $uFraccion = $fraccionable && !empty($_POST['unidad_fraccion']) ? trim($_POST['unidad_fraccion']) : null;
            $pFraccion = $fraccionable ? (float)str_replace(',', '.', preg_replace('/[^\d.,\-]/', '', $_POST['precio_fraccion'] ?? 0)) : 0.00;

            $codPrinActivo = !empty($_POST['codigo_prin_activo']) ? (int)preg_replace('/[^\d]/', '', $_POST['codigo_prin_activo']) : null;
            $idLab = !empty($_POST['id_laboratorio']) ? (int)$_POST['id_laboratorio'] : null;
            $idCat = !empty($_POST['id_categoria']) ? (int)$_POST['id_categoria'] : null;

            $data = [
                'codigo_barras' => !empty(trim($_POST['codigo_barras'] ?? '')) ? trim($_POST['codigo_barras']) : null,
                'nombre_generico' => trim($_POST['nombre_generico'] ?? ''),
                'codigo_prin_activo' => $codPrinActivo,
                'nombre_comercial' => trim($_POST['nombre_comercial'] ?? ''),
                'concentracion' => !empty(trim($_POST['concentracion'] ?? '')) ? trim($_POST['concentracion']) : null,
                'forma_farmaceutica' => !empty(trim($_POST['forma_farmaceutica'] ?? '')) ? trim($_POST['forma_farmaceutica']) : null,
                'registro_sanitario' => !empty(trim($_POST['registro_sanitario'] ?? '')) ? trim($_POST['registro_sanitario']) : null,
                'condicion_venta' => $condicion_venta,
                'id_laboratorio' => $idLab,
                'id_categoria' => $idCat,
                'precio_compra' => $pCompra,
                'precio_venta' => $pVenta,
                'precio_mayor' => $pMayor,
                'margen_ganancia' => $pMargen,
                'unidad_medida' => !empty($_POST['unidad_medida']) ? trim($_POST['unidad_medida']) : 'Unidad',
                'requiere_receta' => $requiere_receta,
                'stock_minimo' => $stockMin,
                'fraccionable' => $fraccionable,
                'unidades_por_caja' => $uCaja,
                'unidad_fraccion' => $uFraccion,
                'precio_fraccion' => $pFraccion,
                'id' => !empty($_POST['id']) ? (int)$_POST['id'] : null
            ];
            
            try {
                if (empty($data['id'])) {
                    $modelo->create($data);
                    $this->logAccion('Productos', 'CREAR', "Nuevo producto creado: " . $data['nombre_comercial']);
                    $_SESSION['mensaje'] = "Producto '" . htmlspecialchars($data['nombre_comercial']) . "' creado exitosamente.";
                } else {
                    $modelo->update($data);
                    $this->logAccion('Productos', 'EDITAR', "Producto ID #" . $data['id'] . " editado. Precios: S/ " . $data['precio_venta'] . " (Caja) / S/ " . $data['precio_fraccion'] . " (Frac)");
                    $_SESSION['mensaje'] = "Producto actualizado correctamente.";
                }
            } catch (Exception $e) {
                error_log("[ProductoController::save] Error: " . $e->getMessage());
                $_SESSION['error'] = "Ocurrió un error al guardar el producto: " . $e->getMessage();
            }
        }
        header('Location: ' . BASE_URL . 'producto/index');
        exit;
    }

    public function importarExcel() {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $_SESSION['error'] = 'Método no permitido.';
            header('Location: ' . BASE_URL . 'producto/index');
            exit;
        }

        $this->validateCsrf();

        if (empty($_FILES['archivo_excel']) || $_FILES['archivo_excel']['error'] !== UPLOAD_ERR_OK) {
            $_SESSION['error'] = 'Por favor seleccione un archivo Excel (.xlsx) válido.';
            header('Location: ' . BASE_URL . 'producto/index');
            exit;
        }

        $fileInfo = pathinfo($_FILES['archivo_excel']['name']);
        $ext = strtolower($fileInfo['extension'] ?? '');

        if ($ext !== 'xlsx') {
            $_SESSION['error'] = 'Formato inválido. Debe subir un archivo con extensión .xlsx (Excel).';
            header('Location: ' . BASE_URL . 'producto/index');
            exit;
        }

        $tmpFile = $_FILES['archivo_excel']['tmp_name'];

        require_once '../app/services/ExcelProductImporter.php';
        $db = (new Database())->getConnection();
        $importer = new ExcelProductImporter($db, $_SESSION['user_id'] ?? 1);

        $options = [
            'crear_categorias' => isset($_POST['crear_categorias']),
            'crear_laboratorios' => isset($_POST['crear_laboratorios']),
            'actualizar_existentes' => isset($_POST['actualizar_existentes'])
        ];

        try {
            $stats = $importer->procesarArchivo($tmpFile, $options);

            $this->logAccion(
                'Productos',
                'IMPORTAR',
                "Carga masiva Excel: {$stats['creados']} creados, {$stats['actualizados']} actualizados, {$stats['lotes_creados']} lotes generados."
            );

            $_SESSION['import_stats'] = $stats;
            $_SESSION['mensaje'] = "¡Importación completada con éxito! Se procesaron {$stats['total_filas']} registros ({$stats['creados']} nuevos, {$stats['actualizados']} actualizados).";

        } catch (Exception $e) {
            error_log("[ProductoController::importarExcel] Error: " . $e->getMessage());
            $_SESSION['error'] = "Error al procesar el archivo Excel: " . $e->getMessage();
        }

        header('Location: ' . BASE_URL . 'producto/index');
        exit;
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
