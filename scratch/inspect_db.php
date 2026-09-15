<?php
try {
    $db = new PDO('mysql:host=localhost;dbname=botica_db', 'root', '');
    $db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    $tables = [];
    $q = $db->query("SHOW TABLES");
    while ($row = $q->fetch(PDO::FETCH_NUM)) {
        $tables[] = $row[0];
    }
    
    echo "RECORD COUNTS:\n";
    foreach ($tables as $t) {
        $count = $db->query("SELECT COUNT(*) FROM `$t`")->fetchColumn();
        echo "- $t: $count\n";
    }
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
}
