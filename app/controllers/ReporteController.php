<?php
class ReporteController extends Controller {

    public function __construct() {
        if (!isset($_SESSION['user_id'])) {
            header('Location: ' . BASE_URL . 'auth/login');
            exit;
        }
        // Solo el administrador debe poder ver reportes gerenciales
        if ($_SESSION['rol_id'] != 1) {
            header('Location: ' . BASE_URL . 'dashboard/index');
            exit;
        }
    }

    public function index() {
        $this->view('reportes/index', ['title' => 'Reportes Gerenciales']);
    }

    public function exportar_ventas() {
        $fecha_inicio = $_GET['fecha_inicio'] ?? date('Y-m-d');
        $fecha_fin = $_GET['fecha_fin'] ?? date('Y-m-d');
        
        $ventaModel = $this->model('Venta');
        $ventas = $ventaModel->getAll(); // En un caso real se filtraría por fecha en BD ->getVentasPorFecha($fi, $ff)
        // Como estamos simplificando y 'getAll' trae las ventas, filtramos en PHP
        $filtradas = [];
        
        $ts_inicio = strtotime($fecha_inicio . " 00:00:00");
        $ts_fin = strtotime($fecha_fin . " 23:59:59");
        
        foreach($ventas as $v) {
            $ts_v = strtotime($v['fecha_venta']);
            if($ts_v >= $ts_inicio && $ts_v <= $ts_fin) {
                $filtradas[] = $v;
            }
        }
        
        // Cabeceras Excel CSV
        header("Content-Type: text/csv; charset=utf-8");
        header("Content-Disposition: attachment; filename=Reporte_Ventas_{$fecha_inicio}_al_{$fecha_fin}.csv");
        
        $output = fopen("php://output", "w");
        // UTF-8 BOM para soporte en Excel
        fwrite($output, "\xEF\xBB\xBF");
        
        fputcsv($output, ['ID Venta', 'Fecha', 'Cajero', 'Cliente', 'Doc', 'Numero', 'Subtotal', 'IGV', 'Total', 'Forma Pago']);
        
        foreach($filtradas as $v) {
            fputcsv($output, [
                $v['id'],
                $v['fecha_venta'],
                $v['cajero'],
                $v['cliente'],
                $v['tipo_comprobante'],
                $v['num_comprobante'],
                $v['subtotal'],
                $v['igv'],
                $v['total'],
                $v['metodo_pago']
            ], ";"); // Usamos punto y coma para Excel español
        }
        fclose($output);
        exit;
    }

    public function vencimientos_excel() {
        $inventarioModel = $this->model('Inventario');
        $lotes = $inventarioModel->getLotesProximosVencer(90); // a 90 dias
        
        header("Content-Type: text/csv; charset=utf-8");
        header("Content-Disposition: attachment; filename=Reporte_Lotes_Vencer.csv");
        
        $output = fopen("php://output", "w");
        fwrite($output, "\xEF\xBB\xBF");
        fputcsv($output, ['Producto', 'Lote', 'Fecha Vencimiento', 'Stock', 'Dias Restantes']);
        
        $hoy = new DateTime();
        foreach($lotes as $l) {
            $fv = new DateTime($l['fecha_vencimiento']);
            $diff = $hoy->diff($fv)->days;
            $f_status = ($fv < $hoy) ? 'VENCIDO' : $diff;
            
            fputcsv($output, [
                $l['producto'],
                $l['lote'],
                $l['fecha_vencimiento'],
                $l['stock'],
                $f_status
            ], ";");
        }
        fclose($output);
        exit;
    }

    public function ventas_pdf() {
        $fecha_inicio = $_GET['fecha_inicio'] ?? date('Y-m-d');
        $fecha_fin = $_GET['fecha_fin'] ?? date('Y-m-d');
        
        $ventaModel = $this->model('Venta');
        $ventas = $ventaModel->getAll(); 
        $filtradas = [];
        
        $ts_inicio = strtotime($fecha_inicio . " 00:00:00");
        $ts_fin = strtotime($fecha_fin . " 23:59:59");
        
        foreach($ventas as $v) {
            $ts_v = strtotime($v['fecha_venta']);
            if($ts_v >= $ts_inicio && $ts_v <= $ts_fin) {
                $filtradas[] = $v;
            }
        }

        $configModel = $this->model('Configuracion');
        
        $data = [
            'fecha_inicio' => $fecha_inicio,
            'fecha_fin' => $fecha_fin,
            'ventas' => $filtradas,
            'config' => $configModel->getAll()
        ];
        
        // Vista estricta para impresión (sin layout main)
        require_once '../app/views/reportes/ventas_pdf.php';
    }

    public function vencimientos_pdf() {
        $inventarioModel = $this->model('Inventario');
        $lotes = $inventarioModel->getLotesProximosVencer(90); 
        $configModel = $this->model('Configuracion');
        
        $data = [
            'lotes' => $lotes,
            'config' => $configModel->getAll()
        ];
        
        require_once '../app/views/reportes/vencimientos_pdf.php';
    }

    public function reposicion_excel() {
        $productoModel = $this->model('Producto');
        $productos = $productoModel->getProductosBajoStock();
        
        header("Content-Type: text/csv; charset=utf-8");
        header("Content-Disposition: attachment; filename=Reporte_Reposicion_Stock_" . date('Y-m-d') . ".csv");
        
        $output = fopen("php://output", "w");
        fwrite($output, "\xEF\xBB\xBF"); // UTF-8 BOM
        fputcsv($output, ['Codigo Barras', 'Producto', 'Laboratorio', 'Stock Minimo', 'Stock Actual', 'Faltante']);
        
        foreach($productos as $p) {
            $faltante = $p['stock_minimo'] - $p['stock_actual'];
            if ($faltante < 0) $faltante = 0;
            fputcsv($output, [
                $p['codigo_barras'],
                $p['nombre_comercial'] . ' ' . $p['concentracion'],
                $p['laboratorio'],
                $p['stock_minimo'],
                $p['stock_actual'],
                $faltante
            ], ";");
        }
        fclose($output);
        exit;
    }

    public function reposicion_pdf() {
        $productoModel = $this->model('Producto');
        $productos = $productoModel->getProductosBajoStock();
        $configModel = $this->model('Configuracion');
        
        $data = [
            'productos' => $productos,
            'config' => $configModel->getAll()
        ];
        
        require_once '../app/views/reportes/reposicion_pdf.php';
    }
}
