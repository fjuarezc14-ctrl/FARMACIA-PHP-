<?php
class ReporteController extends Controller {

    public function __construct() {
        $this->requireRole(1, 'venta/pos');
    }

    public function index() {
        $this->view('reportes/index', ['title' => 'Reportes Gerenciales']);
    }

    public function exportar_ventas() {
        $fecha_inicio = $_GET['fecha_inicio'] ?? date('Y-m-d');
        $fecha_fin = $_GET['fecha_fin'] ?? date('Y-m-d');
        
        $ventaModel = $this->model('Venta');
        $filtradas = $ventaModel->getByDateRange($fecha_inicio, $fecha_fin);
        $configModel = $this->model('Configuracion');
        $configs = $configModel->getAll();
        $nombreBotica = $configs['nombre_botica']['valor'] ?? 'BOTICA CENGFARMA';
        $rucBotica = $configs['ruc']['valor'] ?? '';
        
        // Cabeceras Excel CSV
        header("Content-Type: text/csv; charset=utf-8");
        header("Content-Disposition: attachment; filename=Reporte_Ventas_{$fecha_inicio}_al_{$fecha_fin}.csv");
        
        $output = fopen("php://output", "w");
        // UTF-8 BOM para apertura perfecta en Excel
        fwrite($output, "\xEF\xBB\xBF");
        
        // Metadatos y Encabezado Corporativo
        fputcsv($output, [$nombreBotica . ' - REPORTE OFICIAL DE VENTAS'], ";");
        fputcsv($output, ["RUC: $rucBotica", "Periodo: $fecha_inicio al $fecha_fin", "Generado: " . date('d/m/Y H:i:s')], ";");
        fputcsv($output, [], ";"); // Línea en blanco
        
        // Encabezados de Columnas
        fputcsv($output, [
            'ID Venta',
            'Fecha y Hora',
            'Tipo Comprobante',
            'Serie-Número',
            'Cliente',
            'Cajero',
            'Método Pago',
            'N° Op. Yape/Plin',
            'N° Ref. Tarjeta',
            'Efectivo (S/)',
            'Yape/Plin (S/)',
            'Tarjeta (S/)',
            'Subtotal (S/)',
            'Descuento (S/)',
            'IGV (S/)',
            'Total Cobrado (S/)',
            'Estado'
        ], ";");
        
        $totEfe = 0; $totTra = 0; $totTar = 0;
        $totSub = 0; $totDesc = 0; $totIgv = 0; $totFinal = 0;

        foreach($filtradas as $v) {
            $mEfe = isset($v['monto_efectivo']) ? (float)$v['monto_efectivo'] : ($v['metodo_pago'] === 'Efectivo' ? (float)$v['total'] : 0);
            $mTra = isset($v['monto_transferencia']) ? (float)$v['monto_transferencia'] : (in_array($v['metodo_pago'], ['Yape', 'Yape/Plin']) ? (float)$v['total'] : 0);
            $mTar = isset($v['monto_tarjeta']) ? (float)$v['monto_tarjeta'] : ($v['metodo_pago'] === 'Tarjeta' ? (float)$v['total'] : 0);
            $mSub = (float)$v['subtotal'];
            $mDesc = (float)($v['descuento'] ?? 0);
            $mIgv = (float)$v['igv'];
            $mTot = (float)$v['total'];

            $totEfe += $mEfe;
            $totTra += $mTra;
            $totTar += $mTar;
            $totSub += $mSub;
            $totDesc += $mDesc;
            $totIgv += $mIgv;
            $totFinal += $mTot;

            fputcsv($output, [
                $v['id'],
                date('d/m/Y H:i:s', strtotime($v['fecha_venta'])),
                $v['tipo_comprobante'],
                $v['serie_comprobante'] . '-' . $v['num_comprobante'],
                $v['cliente'],
                $v['cajero'],
                $v['metodo_pago'],
                $v['num_operacion_trans'] ?? '-',
                $v['num_operacion_tarj'] ?? '-',
                number_format($mEfe, 2, '.', ''),
                number_format($mTra, 2, '.', ''),
                number_format($mTar, 2, '.', ''),
                number_format($mSub, 2, '.', ''),
                number_format($mDesc, 2, '.', ''),
                number_format($mIgv, 2, '.', ''),
                number_format($mTot, 2, '.', ''),
                $v['estado'] ?? 'Emitida'
            ], ";");
        }

        // Fila de Totales
        fputcsv($output, [], ";");
        fputcsv($output, [
            'TOTALES GENERALES',
            count($filtradas) . ' ventas',
            '',
            '',
            '',
            '',
            '',
            '',
            '',
            number_format($totEfe, 2, '.', ''),
            number_format($totTra, 2, '.', ''),
            number_format($totTar, 2, '.', ''),
            number_format($totSub, 2, '.', ''),
            number_format($totDesc, 2, '.', ''),
            number_format($totIgv, 2, '.', ''),
            number_format($totFinal, 2, '.', ''),
            ''
        ], ";");

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
        $filtradas = $ventaModel->getByDateRange($fecha_inicio, $fecha_fin); 

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
