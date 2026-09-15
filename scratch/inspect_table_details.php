<?php
try {
    $db = new PDO('mysql:host=localhost;dbname=botica_db', 'root', '');
    $db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    $tables = [
        'clientes', 'cajas', 'caja_movimientos', 'compras', 'compra_detalles', 
        'compras_devoluciones', 'compras_devolucion_detalles', 'inventario_auditorias', 
        'inventario_auditoria_detalles', 'kardex', 'audit_accesos', 'audit_acciones'
    ];
    
    foreach ($tables as $table) {
        echo "========================================\n";
        echo "TABLE: $table\n";
        echo "========================================\n";
        $columns = $db->query("DESCRIBE `$table`")->fetchAll(PDO::FETCH_ASSOC);
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
    }
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
}
