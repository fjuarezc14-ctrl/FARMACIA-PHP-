<?php
require_once 'app/config/database.php';

try {
    $database = new Database();
    $conn = $database->getConnection();
    
    echo "=========================================================\n";
    echo " EJECUTANDO ALTERACIÓN DE TABLA PRODUCTOS\n";
    echo "=========================================================\n";
    
    // 1. Verificar si las columnas ya existen
    $checkQuery = "SHOW COLUMNS FROM productos LIKE 'registro_sanitario'";
    $stmt = $conn->query($checkQuery);
    if ($stmt->rowCount() > 0) {
        echo "[ALERTA] La columna 'registro_sanitario' ya existe.\n";
    } else {
        $alterQuery1 = "ALTER TABLE `productos` ADD COLUMN `registro_sanitario` VARCHAR(100) NULL AFTER `forma_farmaceutica`";
        $conn->exec($alterQuery1);
        echo "[ÉXITO] Columna 'registro_sanitario' creada con éxito.\n";
    }
    
    $checkQuery2 = "SHOW COLUMNS FROM productos LIKE 'condicion_venta'";
    $stmt2 = $conn->query($checkQuery2);
    if ($stmt2->rowCount() > 0) {
        echo "[ALERTA] La columna 'condicion_venta' ya existe.\n";
    } else {
        $alterQuery2 = "ALTER TABLE `productos` ADD COLUMN `condicion_venta` ENUM('Venta Libre', 'Receta Médica Simple', 'Receta Médica Retenida') NOT NULL DEFAULT 'Venta Libre' AFTER `registro_sanitario`";
        $conn->exec($alterQuery2);
        echo "[ÉXITO] Columna 'condicion_venta' creada con éxito.\n";
    }
    
    echo "=========================================================\n";
    echo " ¡PROCESO COMPLETADO EXITOSAMENTE!\n";
    echo "=========================================================\n";
    
} catch (PDOException $e) {
    echo "[ERROR] Ocurrió un error al ejecutar la alteración: " . $e->getMessage() . "\n";
}
