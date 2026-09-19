<?php
class ReporteController extends Controller {

    public function __construct() {
        $this->requireRole([1, 2, 4], 'venta/pos');
    }

    public function index() {
        $labModel = $this->model('Laboratorio');
        $this->view('reportes/index', [
            'title'        => 'Reportes Gerenciales',
            'laboratorios' => $labModel->getAll()
        ]);
    }

    public function exportar_ventas() {
        $this->requireRole(1, 'reporte/index');
        $fecha_inicio = $_GET['fecha_inicio'] ?? date('Y-m-d');
        $fecha_fin = $_GET['fecha_fin'] ?? date('Y-m-d');
        
        $ventaModel = $this->model('Venta');
        $filtradas = $ventaModel->getByDateRange($fecha_inicio, $fecha_fin);
        $configModel = $this->model('Configuracion');
        $configs = $configModel->getAll();
        $nombreBotica = $configs['nombre_botica']['valor'] ?? 'FARMACIA PRUEBA';
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

    public function ventas_pdf() {
        $this->requireRole(1, 'reporte/index');
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
        
        require_once '../app/views/reportes/ventas_pdf.php';
    }

    public function vencimientos_excel() {
        $rango = $_GET['rango'] ?? '90';
        $id_laboratorio = !empty($_GET['id_laboratorio']) ? (int)$_GET['id_laboratorio'] : null;

        $inventarioModel = $this->model('Inventario');
        $lotes = $inventarioModel->getLotesProximosVencer($rango, $id_laboratorio);
        
        $configModel = $this->model('Configuracion');
        $configs = $configModel->getAll();
        $nombreBotica = $configs['nombre_botica']['valor'] ?? 'FARMACIA PRUEBA';
        $rucBotica = $configs['ruc']['valor'] ?? '';

        header("Content-Type: text/csv; charset=utf-8");
        header("Content-Disposition: attachment; filename=Reporte_Vencimientos_" . date('Y-m-d') . ".csv");
        
        $output = fopen("php://output", "w");
        fwrite($output, "\xEF\xBB\xBF");
        
        fputcsv($output, [$nombreBotica . ' - CONTROL DE VENCIMIENTOS'], ";");
        fputcsv($output, ["RUC: $rucBotica", "Filtro: " . strtoupper($rango), "Generado: " . date('d/m/Y H:i:s')], ";");
        fputcsv($output, [], ";");
        
        fputcsv($output, ['Producto', 'Laboratorio', 'Lote', 'Fecha Vencimiento', 'Stock Disponible', 'Días Restantes', 'Estado FEFO'], ";");
        
        foreach($lotes as $l) {
            $diff = (int)($l['dias_restantes'] ?? 0);
            if ($diff < 0) {
                $status = 'VENCIDO (' . abs($diff) . ' días atrás)';
            } elseif ($diff <= 30) {
                $status = 'CRÍTICO (' . $diff . ' días)';
            } elseif ($diff <= 90) {
                $status = 'RIESGO (' . $diff . ' días)';
            } else {
                $status = 'SANO (' . $diff . ' días)';
            }
            
            fputcsv($output, [
                $l['producto'],
                $l['laboratorio'] ?? 'Sin Laboratorio',
                $l['lote'],
                date('d/m/Y', strtotime($l['fecha_vencimiento'])),
                $l['stock'],
                $diff,
                $status
            ], ";");
        }
        fclose($output);
        exit;
    }

    public function vencimientos_pdf() {
        $rango = $_GET['rango'] ?? '90';
        $id_laboratorio = !empty($_GET['id_laboratorio']) ? (int)$_GET['id_laboratorio'] : null;

        $inventarioModel = $this->model('Inventario');
        $lotes = $inventarioModel->getLotesProximosVencer($rango, $id_laboratorio); 
        $configModel = $this->model('Configuracion');
        
        $data = [
            'rango' => $rango,
            'id_laboratorio' => $id_laboratorio,
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
