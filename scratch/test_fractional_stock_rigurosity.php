<?php
require_once 'app/config/database.php';
require_once 'app/models/Venta.php';
require_once 'app/models/Producto.php';

try {
    echo "=========================================================\n";
    echo " PRUEBA DE INTEGRACIÓN: RIGUROSIDAD DE FRACCIONAMIENTO\n";
    echo "=========================================================\n";
    
    $db = new Database();
    $conn = $db->getConnection();
    
    // 1. Obtener un producto fraccionable para el test (Amoxil ID #5 o similar)
    $prodStmt = $conn->query("SELECT * FROM productos WHERE fraccionable = 1 AND stock_actual > 20 LIMIT 1");
    $prod = $prodStmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$prod) {
        throw new Exception("No hay productos fraccionables con suficiente stock para la prueba.");
    }
    
    $id_producto = $prod['id'];
    $nombre = $prod['nombre_comercial'];
    $factor = $prod['unidades_por_caja'];
    $stock_inicial = $prod['stock_actual'];
    
    echo "Producto seleccionado: $nombre (ID: $id_producto)\n";
    echo "  -> Stock Inicial (unidades mínimas): $stock_inicial\n";
    echo "  -> Unidades por Caja (factor): $factor\n";
    
    // 2. Registrar una venta de prueba:
    //    Queremos vender 1 Caja (que equivale a $factor unidades)
    //    y 3 Fracciones individuales (que equivalen a 3 unidades).
    $caja_id = 1; // Caja abierta por defecto o existente
    $ventaModel = new Venta();
    
    $cabecera = [
        'caja_id' => $caja_id,
        'id_cliente' => 1, // Cliente genérico
        'tipo_comprobante' => 'Boleta',
        'serie_comprobante' => 'TEST',
        'num_comprobante' => '999999',
        'subtotal' => 50.00,
        'descuento' => 0.00,
        'igv' => 9.00,
        'total' => 59.00,
        'metodo_pago' => 'Efectivo',
        'pago_recibido' => 100.00,
        'vuelto' => 41.00,
        'puntos_ganados' => 0,
        'puntos_usados' => 0,
        'medico_cmp' => null
    ];
    
    $detalles = [
        // Fila 1: 1 Caja
        [
            'id_producto' => $id_producto,
            'cantidad' => 1,
            'precio_unitario' => $prod['precio_venta'],
            'subtotal' => $prod['precio_venta'],
            'tipo_unidad' => 'CAJA'
        ],
        // Fila 2: 3 Fracciones
        [
            'id_producto' => $id_producto,
            'cantidad' => 3,
            'precio_unitario' => $prod['precio_fraccion'],
            'subtotal' => 3 * $prod['precio_fraccion'],
            'tipo_unidad' => 'FRACCION'
        ]
    ];
    
    echo "[PASO 1] Registrando venta...\n";
    $id_venta = $ventaModel->registrarVenta($cabecera, $detalles, 1);
    
    if (!$id_venta) {
        throw new Exception("Error al registrar la venta de prueba. Puede ser por falta de stock del lote FEFO.");
    }
    echo "  -> Éxito: Venta registrada con ID #$id_venta.\n";
    
    // 3. Validar stock actual
    $prodStmt->execute();
    $prodLego = $prodStmt->fetch(PDO::FETCH_ASSOC);
    $stock_esperado = $stock_inicial - ($factor + 3);
    echo "[PASO 2] Verificando decremento de stock...\n";
    echo "  -> Stock después de venta (unidades mínimas): " . $prodLego['stock_actual'] . "\n";
    echo "  -> Stock esperado: $stock_esperado\n";
    
    if ($prodLego['stock_actual'] != $stock_esperado) {
        throw new Exception("¡DISCREPANCIA EN STOCK DECREMENTADO!");
    } else {
        echo "  -> Éxito: El stock decrementó exactamente en " . ($factor + 3) . " unidades mínimas.\n";
    }
    
    // 4. Validar unidades en venta_detalles
    echo "[PASO 3] Verificando registros en venta_detalles...\n";
    $detStmt = $conn->prepare("SELECT * FROM venta_detalles WHERE id_venta = :id");
    $detStmt->bindParam(':id', $id_venta);
    $detStmt->execute();
    $rows = $detStmt->fetchAll(PDO::FETCH_ASSOC);
    
    foreach ($rows as $row) {
        echo "  -> Detalle ID #" . $row['id'] . ":\n";
        echo "     * Cantidad (BD): " . $row['cantidad'] . " (unidades mínimas)\n";
        echo "     * Tipo Unidad (BD): " . $row['tipo_unidad'] . "\n";
        echo "     * Subtotal (BD): S/ " . $row['subtotal'] . "\n";
    }
    
    // 5. Anular la venta y verificar restauración de stock
    echo "[PASO 4] Anulando venta de prueba...\n";
    $anulada = $ventaModel->anularVenta($id_venta, 1);
    
    if (!$anulada) {
        throw new Exception("Error al anular la venta.");
    }
    echo "  -> Éxito: Venta anulada.\n";
    
    // Validar stock restaurado
    $prodStmt->execute();
    $prodFinal = $prodStmt->fetch(PDO::FETCH_ASSOC);
    echo "[PASO 5] Verificando restauración de stock...\n";
    echo "  -> Stock Final: " . $prodFinal['stock_actual'] . "\n";
    echo "  -> Stock Inicial Esperado: $stock_inicial\n";
    
    if ($prodFinal['stock_actual'] != $stock_inicial) {
        throw new Exception("¡DISCREPANCIA EN STOCK RESTAURADO!");
    } else {
        echo "  -> Éxito: El stock se restauró perfectamente al valor original de $stock_inicial.\n";
    }
    
    // Limpieza de venta física en BD para no dejar residuos del test
    $conn->exec("DELETE FROM venta_detalles WHERE id_venta = $id_venta");
    $conn->exec("DELETE FROM ventas WHERE id = $id_venta");
    echo "[PASO 6] Limpieza de registros completada.\n";
    
    echo "=========================================================\n";
    echo " ¡PRUEBA DE FRACCIONAMIENTO EXITOSA AL 100%!\n";
    echo "=========================================================\n";

} catch (Exception $e) {
    echo "[ERROR CRÍTICO] " . $e->getMessage() . "\n";
}
