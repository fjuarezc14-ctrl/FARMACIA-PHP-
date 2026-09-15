<?php
class NotificacionController extends Controller {

    public function __construct() {
        if (!isset($_SESSION['user_id'])) {
            header('Location: ' . BASE_URL . 'auth/login');
            exit;
        }
    }

    public function index() {
        $invModel = $this->model('Inventario');
        $lotesVencer = $invModel->getLotesProximosVencer(90); // a 90 dias
        $stockBajo = $invModel->getProductosBajoStock(20);    // stock <= 20
        
        $data = [
            'title' => 'Centro de Alertas Sanitarias',
            'lotes' => $lotesVencer,
            'bajos' => $stockBajo
        ];
        
        $this->view('notificaciones/index', $data);
    }
}
