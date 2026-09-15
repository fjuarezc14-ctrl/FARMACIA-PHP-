<?php
require_once 'app/config/database.php';
require_once 'app/models/Producto.php';

try {
    echo "=========================================================\n";
    echo " PRUEBA DE INTEGRACIÓN: REGISTRO SANITARIO & DIGEMID\n";
    echo "=========================================================\n";
    
    $model = new Producto();
    
    // 1. Crear producto de prueba con Registro Sanitario y Receta Médica Retenida
    $testData = [
        'codigo_barras' => '9999999999999',
        'nombre_generico' => 'TEST GENERIL',
        'nombre_comercial' => 'TEST MEDICINA DIGEMID',
        'concentracion' => '100mg',
        'forma_farmaceutica' => 'Tableta',
        'registro_sanitario' => 'N-99999-TEST',
        'condicion_venta' => 'Receta Médica Retenida',
        'id_laboratorio' => null,
        'id_categoria' => null,
        'precio_compra' => 5.00,
        'precio_venta' => 10.00,
        'margen_ganancia' => 100.00,
        'unidad_medida' => 'Caja',
        'requiere_receta' => 1, // Sincronizado en controlador
        'stock_minimo' => 5,
        'fraccionable' => 0,
        'unidades_por_caja' => 1,
        'unidad_fraccion' => null,
        'precio_fraccion' => 0.00
    ];
    
    echo "[PASO 1] Insertando producto de prueba...\n";
    $inserted = $model->create($testData);
    
    if ($inserted) {
        echo "  -> Éxito: Producto creado.\n";
    } else {
        throw new Exception("Error al insertar el producto de prueba.");
    }
    
    // 2. Recuperar producto de prueba
    echo "[PASO 2] Consultando base de datos para validar campos...\n";
    $db = new Database();
    $conn = $db->getConnection();
    $stmt = $conn->prepare("SELECT * FROM productos WHERE codigo_barras = '9999999999999'");
    $stmt->execute();
    $prod = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if ($prod) {
        echo "  -> Éxito: Registro Sanitario recuperado: " . $prod['registro_sanitario'] . "\n";
        echo "  -> Éxito: Condición de Venta recuperada: " . $prod['condicion_venta'] . "\n";
        echo "  -> Éxito: Requiere Receta (Auto-Sincro): " . $prod['requiere_receta'] . "\n";
    } else {
        throw new Exception("No se encontró el producto de prueba tras la inserción.");
    }
    
    // 3. Modificar/Editar el producto
    echo "[PASO 3] Modificando producto de prueba...\n";
    $prod['nombre_comercial'] = 'TEST MEDICINA MODIFICADA';
    $prod['registro_sanitario'] = 'EE-99999-MOD';
    $prod['condicion_venta'] = 'Venta Libre';
    $prod['requiere_receta'] = 0; // Sincronizado
    
    // El modelo espera recibir los campos mapeados a bindParams
    $updateData = [
        'codigo_barras' => $prod['codigo_barras'],
        'nombre_generico' => $prod['nombre_generico'],
        'nombre_comercial' => $prod['nombre_comercial'],
        'concentracion' => $prod['concentracion'],
        'forma_farmaceutica' => $prod['forma_farmaceutica'],
        'registro_sanitario' => $prod['registro_sanitario'],
        'condicion_venta' => $prod['condicion_venta'],
        'id_laboratorio' => $prod['id_laboratorio'],
        'id_categoria' => $prod['id_categoria'],
        'precio_compra' => $prod['precio_compra'],
        'precio_venta' => $prod['precio_venta'],
        'margen_ganancia' => $prod['margen_ganancia'],
        'unidad_medida' => $prod['unidad_medida'],
        'requiere_receta' => $prod['requiere_receta'],
        'stock_minimo' => $prod['stock_minimo'],
        'fraccionable' => $prod['fraccionable'],
        'unidades_por_caja' => $prod['unidades_por_caja'],
        'unidad_fraccion' => $prod['unidad_fraccion'],
        'precio_fraccion' => $prod['precio_fraccion'],
        'id' => $prod['id']
    ];
    
    $updated = $model->update($updateData);
    if ($updated) {
        echo "  -> Éxito: Producto modificado.\n";
    } else {
        throw new Exception("Error al actualizar el producto de prueba.");
    }
    
    // Validar actualización
    $stmt->execute();
    $prodUpdated = $stmt->fetch(PDO::FETCH_ASSOC);
    echo "  -> Registro Sanitario nuevo: " . $prodUpdated['registro_sanitario'] . "\n";
    echo "  -> Condición de Venta nueva: " . $prodUpdated['condicion_venta'] . "\n";
    
    // 4. Limpieza del registro de prueba
    echo "[PASO 4] Eliminando producto de prueba...\n";
    $conn->exec("DELETE FROM productos WHERE id = " . $prod['id']);
    echo "  -> Éxito: Limpieza de base de datos terminada.\n";
    
    echo "=========================================================\n";
    echo " ¡PRUEBA DE MODELO E INTEGRACIÓN EXITOSA!\n";
    echo "=========================================================\n";

} catch (Exception $e) {
    echo "[ERROR CRÍTICO] " . $e->getMessage() . "\n";
}
