<?php
class LaboratorioController extends Controller {

    public function __construct() {
        $this->requireRole(1, 'venta/pos');
    }

    public function index() {
        $modelo = $this->model('Laboratorio');
        $laboratorios = $modelo->getAll();
        
        $this->view('laboratorios/index', ['title' => 'Laboratorios', 'laboratorios' => $laboratorios]);
    }

    public function save() {
        if ($_SERVER['REQUEST_METHOD'] == 'POST') {
            $this->validateCsrf();
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
        $modelo = $this->model('Laboratorio');
        $modelo->delete($id);
        
        header('Location: ' . BASE_URL . 'laboratorio/index');
    }
}

