<?php
require_once 'app/config/database.php';
require_once 'app/models/Producto.php';
require_once 'app/models/Dashboard.php';

try {
    echo "=========================================================\n";
    echo " PRUEBA DE ALERTA DE REPOSICIÓN Y STOCK MÍNIMO\n";
    echo "=========================================================\n";
    
    $db = new Database();
    $conn = $db->getConnection();
    
    // 1. Forzar algunos productos a stock crítico
    echo "[PASO 1] Forzando stock crítico en 3 productos...\n";
    // Seleccionar 3 productos al azar
    $stmt = $conn->query("SELECT id, nombre_comercial, stock_actual, stock_minimo FROM productos LIMIT 3");
    $productos = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    foreach ($productos as $p) {
        // Establecer stock_actual igual a stock_minimo - 1
        $nuevo_stock = max(0, $p['stock_minimo'] - 1);
        $conn->exec("UPDATE productos SET stock_actual = $nuevo_stock WHERE id = " . $p['id']);
        echo "  -> Producto ID {$p['id']} ({$p['nombre_comercial']}) actualizado a stock: $nuevo_stock (Min: {$p['stock_minimo']})\n";
    }
    
    // 2. Probar Dashboard Model
    echo "\n[PASO 2] Verificando Métricas del Dashboard...\n";
    $dashModel = new Dashboard();
    $metricas = $dashModel->getMetricasHoy();
    echo "  -> Metrica 'productos_riesgo_stock': " . $metricas['productos_riesgo_stock'] . "\n";
    if ($metricas['productos_riesgo_stock'] >= 3) {
        echo "  -> ¡ÉXITO! El dashboard detectó los productos en riesgo.\n";
    } else {
        throw new Exception("El dashboard no contó correctamente los productos en riesgo.");
    }
    
    // 3. Probar Producto Model (Reporte)
    echo "\n[PASO 3] Verificando Extracción de Reporte...\n";
    $prodModel = new Producto();
    $criticos = $prodModel->getProductosBajoStock();
    echo "  -> Total de productos críticos extraídos: " . count($criticos) . "\n";
    
    if (count($criticos) >= 3) {
        echo "  -> ¡ÉXITO! La lista contiene los productos esperados.\n";
    } else {
         throw new Exception("La consulta no devolvió los productos correctos.");
    }
    
    echo "=========================================================\n";
    echo " PRUEBA FINALIZADA CORRECTAMENTE\n";
    echo "=========================================================\n";
} catch (Exception $e) {
    echo "ERROR: " . $e->getMessage() . "\n";
}
