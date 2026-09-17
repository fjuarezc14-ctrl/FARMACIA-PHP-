<?php
class DashboardController extends Controller {

    public function __construct() {
        $this->requireRole(1, 'venta/pos');
    }

    public function index() {

        $modelo = $this->model('Dashboard');
        $metricas = $modelo->getMetricasHoy();
        $graficoHoy = $modelo->getGraficoHoy();
        $grafico = $modelo->getGraficoSemanal();
        $graficoMensual = $modelo->getGraficoMensual();
        $pagos = $modelo->getMediosPago();
        $topProductos = $modelo->getTopProductos();
        $topCategorias = $modelo->getTopCategorias();

        $data = [
            'title' => 'Dashboard Gerencial',
            'metricas' => $metricas,
            'graficoHoy' => $graficoHoy,
            'grafico' => $grafico,
            'graficoMensual' => $graficoMensual,
            'pagos' => $pagos,
            'topProductos' => $topProductos,
            'topCategorias' => $topCategorias
        ];

        $this->view('dashboard/index', $data);
    }
}
