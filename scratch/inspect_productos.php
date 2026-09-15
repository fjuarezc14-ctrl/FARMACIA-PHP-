<?php
$db = new PDO('mysql:host=localhost;dbname=botica_db', 'root', '');
foreach($db->query('DESCRIBE productos')->fetchAll() as $col) echo $col['Field'] . ' | ' . $col['Type'] . PHP_EOL;
