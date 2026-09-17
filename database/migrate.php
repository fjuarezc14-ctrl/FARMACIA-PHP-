<?php
/**
 * Migraciones automáticas de base de datos - CENGFARMA
 *
 * Se ejecuta al iniciar el contenedor (docker/entrypoint.sh) y también a mano:
 *   docker exec sistema-botica-app php database/migrate.php
 *
 * - Registra lo aplicado en la tabla `schema_migrations`.
 * - Cada migración es idempotente (revisa information_schema antes de alterar),
 *   por lo que es segura en BDs nuevas (bk_basededatos.sql) y en producción.
 *
 * Para agregar un cambio de esquema: añade una entrada al final de $migraciones
 * con un nombre nuevo. Nunca modifiques ni borres una migración ya desplegada.
 */

if (PHP_SAPI !== 'cli') {
    http_response_code(404);
    exit;
}

// ---------------------------------------------------------------------------
// Conexión (mismas variables de entorno que app/config/database.php)
// ---------------------------------------------------------------------------
$host = getenv('DB_HOST') ?: 'localhost';
$port = getenv('DB_PORT') ?: '3306';
$name = getenv('DB_NAME') ?: 'botica_db';
$user = getenv('DB_USER') ?: 'root';
$pass = getenv('DB_PASSWORD') !== false ? getenv('DB_PASSWORD') : '';
$reintentos = (int)(getenv('MIGRATE_RETRIES') ?: 30);

function logm($msg) { fwrite(STDOUT, '[migrate] ' . $msg . PHP_EOL); }

$pdo = null;
for ($i = 1; $i <= $reintentos; $i++) {
    try {
        $pdo = new PDO("mysql:host=$host;port=$port;dbname=$name;charset=utf8mb4", $user, $pass, [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        ]);
        break;
    } catch (PDOException $e) {
        logm("Esperando base de datos ($i/$reintentos): " . $e->getMessage());
        sleep(2);
    }
}
if (!$pdo) {
    logm('ERROR: no se pudo conectar a la base de datos.');
    exit(1);
}

// ---------------------------------------------------------------------------
// Helpers idempotentes
// ---------------------------------------------------------------------------
function existeTabla(PDO $pdo, $tabla) {
    $st = $pdo->prepare("SELECT COUNT(*) FROM information_schema.tables WHERE table_schema = DATABASE() AND table_name = ?");
    $st->execute([$tabla]);
    return (int)$st->fetchColumn() > 0;
}

function existeColumna(PDO $pdo, $tabla, $columna) {
    $st = $pdo->prepare("SELECT COUNT(*) FROM information_schema.columns WHERE table_schema = DATABASE() AND table_name = ? AND column_name = ?");
    $st->execute([$tabla, $columna]);
    return (int)$st->fetchColumn() > 0;
}

/** Agrega la columna solo si no existe. $definicion: "decimal(12,2) DEFAULT NULL AFTER `x`" */
function agregarColumna(PDO $pdo, $tabla, $columna, $definicion) {
    if (existeColumna($pdo, $tabla, $columna)) return;
    $pdo->exec("ALTER TABLE `$tabla` ADD COLUMN `$columna` $definicion");
    logm("  + $tabla.$columna");
}

// ---------------------------------------------------------------------------
// Tabla de control
// ---------------------------------------------------------------------------
$pdo->exec("CREATE TABLE IF NOT EXISTS `schema_migrations` (
    `id` int NOT NULL AUTO_INCREMENT,
    `migracion` varchar(150) COLLATE utf8mb4_unicode_ci NOT NULL,
    `aplicada_en` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    UNIQUE KEY `uk_migracion` (`migracion`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci");

// ---------------------------------------------------------------------------
// Migraciones (en orden, nombre único)
// ---------------------------------------------------------------------------
$migraciones = [

    '2026_09_17_fase13_pagos_mixtos' => function (PDO $pdo) {
        agregarColumna($pdo, 'ventas', 'monto_efectivo', "decimal(12,2) DEFAULT NULL AFTER `metodo_pago`");
        agregarColumna($pdo, 'ventas', 'monto_transferencia', "decimal(12,2) DEFAULT NULL AFTER `monto_efectivo`");
        agregarColumna($pdo, 'ventas', 'monto_tarjeta', "decimal(12,2) DEFAULT NULL AFTER `monto_transferencia`");
        agregarColumna($pdo, 'ventas', 'num_operacion_trans', "varchar(50) COLLATE utf8mb4_unicode_ci DEFAULT NULL AFTER `monto_tarjeta`");
        agregarColumna($pdo, 'ventas', 'num_operacion_tarj', "varchar(50) COLLATE utf8mb4_unicode_ci DEFAULT NULL AFTER `num_operacion_trans`");
    },

    '2026_09_17_fase14_motivo_descuento' => function (PDO $pdo) {
        agregarColumna($pdo, 'ventas', 'tipo_descuento', "varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT NULL AFTER `descuento`");
        agregarColumna($pdo, 'ventas', 'motivo_descuento', "varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL AFTER `tipo_descuento`");
        $n = $pdo->exec("UPDATE `ventas`
                            SET `tipo_descuento` = 'Puntos',
                                `motivo_descuento` = CONCAT('Canje de ', `puntos_usados`, ' puntos')
                          WHERE `descuento` > 0 AND `puntos_usados` > 0 AND `tipo_descuento` IS NULL");
        if ($n) logm("  ~ $n ventas antiguas marcadas como descuento por puntos");
    },

    '2026_09_17_fase15_puntos_fidelizacion' => function (PDO $pdo) {
        agregarColumna($pdo, 'clientes', 'puntos_acumulados', "int NOT NULL DEFAULT 0 AFTER `direccion`");
        agregarColumna($pdo, 'ventas', 'puntos_ganados', "int DEFAULT 0 AFTER `vuelto`");
        agregarColumna($pdo, 'ventas', 'puntos_usados', "int DEFAULT 0 AFTER `puntos_ganados`");

        $pdo->exec("CREATE TABLE IF NOT EXISTS `cliente_puntos_historial` (
            `id` int NOT NULL AUTO_INCREMENT,
            `id_cliente` int NOT NULL,
            `id_usuario` int NOT NULL,
            `tipo` enum('ACUMULACION','CANJE','AJUSTE_MANUAL','ANULACION') COLLATE utf8mb4_unicode_ci NOT NULL,
            `puntos` int NOT NULL,
            `saldo_anterior` int NOT NULL DEFAULT '0',
            `saldo_nuevo` int NOT NULL DEFAULT '0',
            `motivo` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
            `id_venta` int DEFAULT NULL,
            `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
            PRIMARY KEY (`id`),
            KEY `idx_cph_cliente` (`id_cliente`),
            KEY `idx_cph_tipo` (`tipo`),
            KEY `idx_cph_fecha` (`created_at`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci");

        $pdo->exec("INSERT IGNORE INTO `configuracion` (`clave`, `valor`) VALUES 
            ('puntos_consumo_base', '10.00'),
            ('puntos_valor_canje', '0.10'),
            ('puntos_habilitado', '1')");
        logm("  + cliente_puntos_historial y configuracion de puntos listos");
    },

    '2026_09_17_fase16_productos_catalogo_extendido' => function (PDO $pdo) {
        agregarColumna($pdo, 'productos', 'precio_mayor', "decimal(10,2) DEFAULT NULL AFTER `precio_venta`");
        agregarColumna($pdo, 'productos', 'codigo_prin_activo', "int DEFAULT NULL AFTER `nombre_generico`");
        logm("  + productos.precio_mayor y productos.codigo_prin_activo");
    },

    '2026_09_17_fase17_productos_margen_precision' => function (PDO $pdo) {
        $pdo->exec("ALTER TABLE `productos` MODIFY COLUMN `margen_ganancia` decimal(8,2) NOT NULL DEFAULT '0.00'");
        logm("  + productos.margen_ganancia ampliado a decimal(8,2)");
    },

];

// ---------------------------------------------------------------------------
// Ejecución
// ---------------------------------------------------------------------------
if (!existeTabla($pdo, 'ventas')) {
    logm('La tabla `ventas` no existe todavía (BD sin inicializar). Se omite.');
    exit(0);
}

$aplicadas = $pdo->query("SELECT migracion FROM schema_migrations")->fetchAll(PDO::FETCH_COLUMN);
$pendientes = array_diff(array_keys($migraciones), $aplicadas);

if (!$pendientes) {
    logm('Base de datos al día.');
    exit(0);
}

foreach ($pendientes as $nombre) {
    logm("Aplicando $nombre ...");
    try {
        // Nota: ALTER TABLE hace commit implícito en MySQL; por eso cada paso es idempotente.
        $migraciones[$nombre]($pdo);
        $pdo->prepare("INSERT INTO schema_migrations (migracion) VALUES (?)")->execute([$nombre]);
        logm("OK $nombre");
    } catch (Throwable $e) {
        logm("ERROR en $nombre: " . $e->getMessage());
        exit(1);
    }
}

logm('Migraciones completadas.');
