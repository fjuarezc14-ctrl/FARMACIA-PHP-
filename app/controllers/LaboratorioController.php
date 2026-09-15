<?php
class LaboratorioController extends Controller {

    public function index() {
        if (!isset($_SESSION['user_id'])) { header('Location: ' . BASE_URL . 'auth/login'); exit; }
        
        $modelo = $this->model('Laboratorio');
        $laboratorios = $modelo->getAll();
        
        $this->view('laboratorios/index', ['title' => 'Laboratorios', 'laboratorios' => $laboratorios]);
    }

    public function save() {
        if (!isset($_SESSION['user_id'])) { header('Location: ' . BASE_URL . 'auth/login'); exit; }
        
        if ($_SERVER['REQUEST_METHOD'] == 'POST') {
            $modelo = $this->model('Laboratorio');
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
        header('Location: ' . BASE_URL . 'laboratorio/index');
    }

    public function delete($id) {
        if (!isset($_SESSION['user_id'])) { header('Location: ' . BASE_URL . 'auth/login'); exit; }
        
        $modelo = $this->model('Laboratorio');
        $modelo->delete($id);
        
        header('Location: ' . BASE_URL . 'laboratorio/index');
    }
}
