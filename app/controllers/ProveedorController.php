<?php
class ProveedorController extends Controller {

    public function __construct() {
        $this->requireRole(1, 'venta/pos');
    }

    public function index() {
        $modelo = $this->model('Proveedor');
        $proveedores = $modelo->getAll();
        
        $this->view('proveedores/index', ['title' => 'Proveedores', 'proveedores' => $proveedores]);
    }

    public function save() {
        if ($_SERVER['REQUEST_METHOD'] == 'POST') {
            $this->validateCsrf();
            $modelo = $this->model('Proveedor');
            $data = [
                'ruc' => $_POST['ruc'],
                'razon_social' => $_POST['razon_social'],
                'representante' => $_POST['representante'],
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
        header('Location: ' . BASE_URL . 'proveedor/index');
    }

    public function delete($id) {
        $modelo = $this->model('Proveedor');
        $modelo->delete($id);
        
        header('Location: ' . BASE_URL . 'proveedor/index');
    }
}

