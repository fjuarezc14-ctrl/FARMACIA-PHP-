<?php
class Dashboard {
    private $conn;

    public function __construct() {
        $db = new Database();
        $this->conn = $db->getConnection();
    }

    public function getMetricasHoy() {
        $hoy = date('Y-m-d');
        
        // 1. Ingresos y Cantidad Hoy
        $stmtVentas = $this->conn->prepare("SELECT SUM(total) as ingresos, COUNT(id) as transacciones FROM ventas WHERE DATE(fecha_venta) = :hoy AND estado = 'Completada'");
        $stmtVentas->bindParam(':hoy', $hoy);
        $stmtVentas->execute();
        $ventas_data = $stmtVentas->fetch(PDO::FETCH_ASSOC);

        // 2. Clientes Activos
        $stmt_c = $this->conn->prepare("SELECT COUNT(id) as total FROM clientes WHERE estado = 1 AND id > 1");
        $stmt_c->execute();
        $clientes_total = $stmt_c->fetch(PDO::FETCH_ASSOC)['total'];

        // 3. Medicamentos en Riesgo FEFO (< 90 días)
        $limite = date('Y-m-d', strtotime('+90 days'));
        $stmt_l = $this->conn->prepare("SELECT COUNT(id) as cant FROM inventario_lotes WHERE fecha_vencimiento <= :limite AND cantidad_disponible > 0 AND estado = 1");
        $stmt_l->bindParam(':limite', $limite);
        $stmt_l->execute();
        $riesgo_fefo = $stmt_l->fetch(PDO::FETCH_ASSOC)['cant'];

        // 4. Catálogo Master Count
        $stmt_p = $this->conn->prepare("SELECT COUNT(id) as total FROM productos WHERE estado = 1");
        $stmt_p->execute();
        $productos_total = $stmt_p->fetch(PDO::FETCH_ASSOC)['total'];

        // 5. Productos en Riesgo de Stock (Agotados / Críticos)
        $stmt_s = $this->conn->prepare("SELECT COUNT(id) as total FROM productos WHERE estado = 1 AND stock_actual <= stock_minimo");
        $stmt_s->execute();
        $productos_riesgo_stock = $stmt_s->fetch(PDO::FETCH_ASSOC)['total'];

        return [
            'ingresos_hoy' => $ventas_data['ingresos'] ?: 0.00,
            'ventas_hoy' => $ventas_data['transacciones'] ?: 0,
            'clientes_total' => $clientes_total,
            'lotes_riesgo' => $riesgo_fefo,
            'productos_total' => $productos_total,
            'productos_riesgo_stock' => $productos_riesgo_stock
        ];
    }
    
    public function getGraficoHoy() {
        $hoy = date('Y-m-d');
        $query = "SELECT HOUR(fecha_venta) as hora, SUM(total) as suma_hora 
                  FROM ventas 
                  WHERE DATE(fecha_venta) = :hoy 
                  AND estado = 'Completada'
                  GROUP BY HOUR(fecha_venta)
                  ORDER BY hora ASC";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':hoy', $hoy);
        $stmt->execute();
        $res = $stmt->fetchAll(PDO::FETCH_ASSOC);

        $ventasPorHora = [];
        foreach ($res as $fila) {
            $ventasPorHora[(int)$fila['hora']] = (float)$fila['suma_hora'];
        }

        $bloquesDef = [
            '08:00 - 10:00' => [0, 1, 2, 3, 4, 5, 6, 7, 8, 9],
            '10:00 - 12:00' => [10, 11],
            '12:00 - 14:00' => [12, 13],
            '14:00 - 16:00' => [14, 15],
            '16:00 - 18:00' => [16, 17],
            '18:00 - 20:00' => [18, 19],
            '20:00 - 22:00' => [20, 21, 22, 23]
        ];

        $labels = [];
        $data = [];
        $total = 0.0;

        foreach ($bloquesDef as $label => $horas) {
            $sumaBloque = 0.0;
            foreach ($horas as $h) {
                if (isset($ventasPorHora[$h])) {
                    $sumaBloque += $ventasPorHora[$h];
                }
            }
            $labels[] = $label;
            $data[] = round($sumaBloque, 2);
            $total += $sumaBloque;
        }

        return [
            'labels' => $labels,
            'data' => $data,
            'total' => round($total, 2)
        ];
    }

    public function getGraficoSemanal() {
        // Últimos 7 días continuos
        $query = "SELECT DATE(fecha_venta) as fecha, SUM(total) as suma_dia 
                  FROM ventas 
                  WHERE fecha_venta >= DATE(NOW()) - INTERVAL 6 DAY 
                  AND estado = 'Completada'
                  GROUP BY DATE(fecha_venta)
                  ORDER BY fecha ASC";
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        $res = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        $ventasPorFecha = [];
        foreach ($res as $fila) {
            $ventasPorFecha[$fila['fecha']] = (float)$fila['suma_dia'];
        }

        $dias = ['Sun'=>'Dom','Mon'=>'Lun','Tue'=>'Mar','Wed'=>'Mié','Thu'=>'Jue','Fri'=>'Vie','Sat'=>'Sáb'];
        $fechas = [];
        $valores = [];
        $total = 0.0;

        for ($i = 6; $i >= 0; $i--) {
            $f = date('Y-m-d', strtotime("-$i days"));
            $dia_str = date('D', strtotime($f));
            $fechas[] = $dias[$dia_str] . ' ' . date('d', strtotime($f));
            $val = $ventasPorFecha[$f] ?? 0.0;
            $valores[] = $val;
            $total += $val;
        }
        
        return [
            'labels' => $fechas,
            'data' => $valores,
            'total' => round($total, 2)
        ];
    }

    public function getGraficoMensual() {
        // Últimos 30 días continuos
        $query = "SELECT DATE(fecha_venta) as fecha, SUM(total) as suma_dia 
                  FROM ventas 
                  WHERE fecha_venta >= DATE(NOW()) - INTERVAL 29 DAY 
                  AND estado = 'Completada' 
                  GROUP BY DATE(fecha_venta) 
                  ORDER BY fecha ASC";
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        $res = $stmt->fetchAll(PDO::FETCH_ASSOC);

        $ventasPorFecha = [];
        foreach ($res as $fila) {
            $ventasPorFecha[$fila['fecha']] = (float)$fila['suma_dia'];
        }

        $mesesCortos = [1=>'Ene', 2=>'Feb', 3=>'Mar', 4=>'Abr', 5=>'May', 6=>'Jun', 7=>'Jul', 8=>'Ago', 9=>'Set', 10=>'Oct', 11=>'Nov', 12=>'Dic'];
        $labels = [];
        $data = [];
        $total = 0.0;

        for ($i = 29; $i >= 0; $i--) {
            $f = date('Y-m-d', strtotime("-$i days"));
            $mNum = (int)date('n', strtotime($f));
            $dNum = date('d', strtotime($f));
            $labels[] = $dNum . ' ' . $mesesCortos[$mNum];
            $val = $ventasPorFecha[$f] ?? 0.0;
            $data[] = $val;
            $total += $val;
        }

        return [
            'labels' => $labels,
            'data' => $data,
            'total' => round($total, 2)
        ];
    }
    
    public function getMediosPago() {
        $query = "SELECT metodo_pago as label, SUM(total) as value FROM ventas WHERE estado = 'Completada' GROUP BY metodo_pago";
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getTopProductos() {
        $query = "SELECT p.nombre_comercial as label, SUM(vd.cantidad) as value 
                  FROM venta_detalles vd 
                  INNER JOIN productos p ON vd.id_producto = p.id 
                  INNER JOIN ventas v ON vd.id_venta = v.id 
                  WHERE v.estado = 'Completada' 
                  GROUP BY p.id 
                  ORDER BY value DESC 
                  LIMIT 5";
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getTopCategorias() {
        $query = "SELECT c.nombre as label, SUM(vd.subtotal) as value 
                  FROM venta_detalles vd 
                  INNER JOIN productos p ON vd.id_producto = p.id 
                  INNER JOIN categorias c ON p.id_categoria = c.id 
                  INNER JOIN ventas v ON vd.id_venta = v.id 
                  WHERE v.estado = 'Completada' 
                  GROUP BY c.id 
                  ORDER BY value DESC 
                  LIMIT 5";
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
}
