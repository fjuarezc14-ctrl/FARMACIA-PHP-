<?php
class ClienteController extends Controller {

    public function __construct() {
        $this->requireAuth();
    }

    public function index() {
        $modelo = $this->model('Cliente');
        $clientes = $modelo->getAll();
        
        $this->view('clientes/index', ['title' => 'Directorio de Clientes', 'clientes' => $clientes]);
    }

    public function save() {
        if ($_SERVER['REQUEST_METHOD'] == 'POST') {
            $this->validateCsrf();
            $modelo = $this->model('Cliente');
            $data = [
                'tipo_documento' => $_POST['tipo_documento'],
                'num_documento' => $_POST['num_documento'],
                'nombres' => $_POST['nombres'],
                'telefono' => $_POST['telefono'],
                'direccion' => $_POST['direccion'],
                'id' => $_POST['id'] ?? null
            ];
            
            if (empty($data['id'])) {
                $modelo->create($data);
            } else {
                $modelo->update($data);
            }
        }
        header('Location: ' . BASE_URL . 'cliente/index');
    }

    public function delete($id) {
        $this->requireRole(1, 'cliente/index');
        
        $modelo = $this->model('Cliente');
        $modelo->delete($id);
        
        header('Location: ' . BASE_URL . 'cliente/index');
    }
}

