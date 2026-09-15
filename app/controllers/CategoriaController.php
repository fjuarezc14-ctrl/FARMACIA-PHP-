<?php
class CategoriaController extends Controller {

    public function index() {
        if (!isset($_SESSION['user_id'])) { header('Location: ' . BASE_URL . 'auth/login'); exit; }
        
        $modelo = $this->model('Categoria');
        $categorias = $modelo->getAll();
        
        $this->view('categorias/index', ['title' => 'Categorías', 'categorias' => $categorias]);
    }

    public function save() {
        if (!isset($_SESSION['user_id'])) { header('Location: ' . BASE_URL . 'auth/login'); exit; }
        
        if ($_SERVER['REQUEST_METHOD'] == 'POST') {
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

    public function delete($id) {
        if (!isset($_SESSION['user_id'])) { header('Location: ' . BASE_URL . 'auth/login'); exit; }
        
        $modelo = $this->model('Categoria');
        $modelo->delete($id);
        
        header('Location: ' . BASE_URL . 'categoria/index');
    }
}
