<?php
class DashboardController extends Controller {

    public function __construct() {
        $this->requireRole(1, 'venta/pos');
    }

    public function index() {

        $modelo = $this->model('Dashboard');
        $metricas = $modelo->getMetricasHoy();
        $grafico = $modelo->getGraficoSemanal();
        $pagos = $modelo->getMediosPago();
        $topProductos = $modelo->getTopProductos();
        $topCategorias = $modelo->getTopCategorias();

        $data = [
            'title' => 'Dashboard Gerencial',
            'metricas' => $metricas,
            'grafico' => $grafico,
            'pagos' => $pagos,
            'topProductos' => $topProductos,
            'topCategorias' => $topCategorias
        ];

        $this->view('dashboard/index', $data);
    }
}
