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

    public function delete($id = null) {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $_SESSION['error'] = 'Método no permitido.';
            header('Location: ' . BASE_URL . 'laboratorio/index');
            exit;
        }

        $this->validateCsrf();

        $labId = (int)($_POST['id'] ?? $id ?? 0);
        if ($labId <= 0) {
            $_SESSION['error'] = 'ID de laboratorio inválido.';
            header('Location: ' . BASE_URL . 'laboratorio/index');
            exit;
        }

        $modelo = $this->model('Laboratorio');
        if ($modelo->delete($labId)) {
            $_SESSION['mensaje'] = 'Laboratorio eliminado correctamente.';
        } else {
            $_SESSION['error'] = 'No se pudo eliminar el laboratorio.';
        }
        
        header('Location: ' . BASE_URL . 'laboratorio/index');
        exit;
    }
}

