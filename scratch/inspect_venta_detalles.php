<?php
try {
    $db = new PDO('mysql:host=localhost;dbname=botica_db', 'root', '');
    $db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    echo "========================================\n";
    echo "TABLE: venta_detalles\n";
    echo "========================================\n";
    $columns = $db->query("DESCRIBE `venta_detalles`")->fetchAll(PDO::FETCH_ASSOC);
    foreach ($columns as $col) {
        echo sprintf("  %-20s | %-15s | %-4s | %-3s | %-8s | %s\n", 
            $col['Field'], 
            $col['Type'], 
            $col['Null'], 
            $col['Key'], 
            $col['Default'] ?? 'NULL', 
            $col['Extra']
        );
    }
    echo "\n";
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
}
