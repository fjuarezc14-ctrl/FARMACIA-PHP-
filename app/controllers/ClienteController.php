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

    public function delete($id = null) {
        $this->requireRole(1, 'cliente/index');

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $_SESSION['error'] = 'Método no permitido.';
            header('Location: ' . BASE_URL . 'cliente/index');
            exit;
        }

        $this->validateCsrf();

        $clientId = (int)($_POST['id'] ?? $id ?? 0);
        if ($clientId <= 0) {
            $_SESSION['error'] = 'ID de cliente inválido.';
            header('Location: ' . BASE_URL . 'cliente/index');
            exit;
        }

        $modelo = $this->model('Cliente');
        if ($modelo->delete($clientId)) {
            $_SESSION['mensaje'] = 'Cliente eliminado correctamente.';
        } else {
            $_SESSION['error'] = 'No se pudo eliminar el cliente especificado.';
        }
        
        header('Location: ' . BASE_URL . 'cliente/index');
        exit;
    }
}

