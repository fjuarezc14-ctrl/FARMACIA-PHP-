<?php
class AuditoriaController extends Controller {

    public function __construct() {
        $this->requireRole(1, 'venta/pos');
    }

    public function index() {
        $modelo = $this->model('Auditoria');
        
        $data = [
            'title' => 'Panel de Auditoría y Seguridad',
            'accesos' => $modelo->getAccesos(150),
            'acciones' => $modelo->getAcciones(200)
        ];
        
        $this->view('auditoria/index', $data);
    }
}

