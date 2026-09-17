<?php
class CategoriaController extends Controller {

    public function __construct() {
        $this->requireRole(1, 'venta/pos');
    }

    public function index() {
        $modelo = $this->model('Categoria');
        $categorias = $modelo->getAll();
        
        $this->view('categorias/index', ['title' => 'Categorías', 'categorias' => $categorias]);
    }

    public function save() {
        if ($_SERVER['REQUEST_METHOD'] == 'POST') {
            $this->validateCsrf();
            $modelo = $this->model('Categoria');
            $data = [
                'nombre' => $_POST['nombre'],
                'descripcion' => $_POST['descripcion'],
                'id' => $_POST['id'] ?? null
            ];
            
            if (empty($data['id'])) {
                $modelo->create($data);
            } else {
                $modelo->update($data);
            }
        }
        header('Location: ' . BASE_URL . 'categoria/index');
    }

    public function delete($id = null) {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $_SESSION['error'] = 'Método no permitido.';
            header('Location: ' . BASE_URL . 'categoria/index');
            exit;
        }

        $this->validateCsrf();

        $categoryId = (int)($_POST['id'] ?? $id ?? 0);
        if ($categoryId <= 0) {
            $_SESSION['error'] = 'ID de categoría inválido.';
            header('Location: ' . BASE_URL . 'categoria/index');
            exit;
        }

        $modelo = $this->model('Categoria');
        if ($modelo->delete($categoryId)) {
            $_SESSION['mensaje'] = 'Categoría eliminada correctamente.';
        } else {
            $_SESSION['error'] = 'No se pudo eliminar la categoría.';
        }
        
        header('Location: ' . BASE_URL . 'categoria/index');
        exit;
    }
}

