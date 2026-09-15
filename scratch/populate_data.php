<?php
/**
 * Script de poblamiento masivo para Sistema Botica
 * Genera datos para los últimos 7 días con el fin de mostrar un gráfico ideal.
 */

$host = "localhost";
$db_name = "botica_db";
$username = "root";
$password = "";

try {
    $pdo = new PDO("mysql:host=$host;dbname=$db_name;charset=utf8", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    echo "Conexión establecida correctamente.\n";
} catch (PDOException $e) {
    die("Error de conexión: " . $e->getMessage());
}

// 0. Obtener IDs válidos
$productos_ids = $pdo->query("SELECT id FROM productos LIMIT 20")->fetchAll(PDO::FETCH_COLUMN);
$clientes_ids = $pdo->query("SELECT id FROM clientes LIMIT 20")->fetchAll(PDO::FETCH_COLUMN);

if (empty($productos_ids)) {
    die("No hay productos en la base de datos. Por favor, crea algunos primero.\n");
}

// 1. Asegurar Categorías y Laboratorios
$categorias = ['Analgésicos', 'Antibióticos', 'Cuidado Personal', 'Suplementos', 'Infantil', 'Dermatología', 'Gastro', 'Respiratorio', 'Oftalmológico', 'Cardiovascular'];
foreach ($categorias as $c) {
    $pdo->prepare("INSERT IGNORE INTO categorias (nombre, estado) VALUES (?, 1)")->execute([$c]);
}

$laboratorios = ['Bayer', 'Pfizer', 'Genfar', 'FarmaIndustria', 'Roche', 'Novartis', 'Sanofi', 'Abbott', 'GSK', 'Merck'];
foreach ($laboratorios as $l) {
    $pdo->prepare("INSERT IGNORE INTO laboratorios (nombre, estado) VALUES (?, 1)")->execute([$l]);
}

// 2. Provocar Alerta de Stock (3 productos con stock bajo)
$pdo->query("UPDATE productos SET stock_actual = 3 WHERE id = " . $productos_ids[0]);
if (isset($productos_ids[1])) $pdo->query("UPDATE productos SET stock_actual = 5 WHERE id = " . $productos_ids[1]);
if (isset($productos_ids[2])) $pdo->query("UPDATE productos SET stock_actual = 8 WHERE id = " . $productos_ids[2]);

// 3. Cajas para los últimos 7 días
$hoy_str = '2026-04-25';
for ($i = 6; $i >= 0; $i--) {
    $fecha = date('Y-m-d', strtotime("$hoy_str -$i days"));
    
    // Abrir y cerrar caja para ese día
    $stmt = $pdo->prepare("INSERT INTO cajas (usuario_id, fecha_apertura, fecha_cierre, monto_inicial, ingresos_efectivo, estado) VALUES (?, ?, ?, ?, ?, ?)");
    $stmt->execute([1, "$fecha 08:00:00", "$fecha 22:00:00", 200.00, rand(500, 1500), 0]);
    $caja_id = $pdo->lastInsertId();

    // 4. Ventas para ese día (variando montos para el gráfico)
    $num_ventas = rand(4, 6);
    // Valores diarios para que el gráfico tenga una curva bonita (Subiendo y bajando)
    $curva = [1200, 800, 1500, 950, 1300, 1100, 1400];
    $meta_dia = $curva[$i];
    
    for ($v = 1; $v <= $num_ventas; $v++) {
        $hora = str_pad(rand(9, 21), 2, "0", STR_PAD_LEFT) . ":" . str_pad(rand(0, 59), 2, "0", STR_PAD_LEFT);
        $total = ($meta_dia / $num_ventas) + rand(-50, 50);
        if ($total < 10) $total = 45.00;
        
        $subtotal = $total / 1.18;
        $igv = $total - $subtotal;

        $stmt = $pdo->prepare("INSERT INTO ventas (id_cliente, caja_id, id_usuario, tipo_comprobante, serie_comprobante, num_comprobante, fecha_venta, subtotal, igv, total, metodo_pago, estado) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
        $stmt->execute([
            $clientes_ids[array_rand($clientes_ids)],
            $caja_id,
            1,
            'Ticket',
            'T001',
            "99" . str_pad($caja_id, 3, "0", STR_PAD_LEFT) . $v,
            "$fecha $hora:00",
            $subtotal,
            $igv,
            $total,
            ($v % 2 == 0 ? 'Efectivo' : 'Tarjeta'),
            'Completada'
        ]);
        $venta_id = $pdo->lastInsertId();

        // Detalle de venta (2 productos por venta)
        for ($d = 0; $d < 2; $d++) {
            $prod_id = $productos_ids[array_rand($productos_ids)];
            $cant = rand(1, 5);
            $pu = rand(10, 50);
            $st = $cant * $pu;
            $pdo->prepare("INSERT INTO venta_detalles (id_venta, id_producto, cantidad, precio_unitario, subtotal) VALUES (?, ?, ?, ?, ?)")->execute([
                $venta_id, $prod_id, $cant, $pu, $st
            ]);
        }
    }
}

echo "Poblamiento masivo completado.\n";
echo "- Ventas generadas para los últimos 7 días con curva dinámica.\n";
echo "- Alertas de stock crítico activadas.\n";
echo "- Registros agregados a Categorías, Laboratorios y Clientes.\n";

?>
