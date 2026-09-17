-- Migración Fase 13: Fidelización Puntos, Historial de Puntos y Motivo de Descuento
USE `botica_db`;

-- 1. Agregar columnas a tabla ventas si no existen
SET @dbname = 'botica_db';
SET @tablename = 'ventas';

-- monto_efectivo
SET @col = 'monto_efectivo';
SET @sql = (SELECT IF((SELECT COUNT(*) FROM INFORMATION_SCHEMA.COLUMNS WHERE table_name = @tablename AND table_schema = @dbname AND column_name = @col) > 0, "SELECT 1", "ALTER TABLE ventas ADD COLUMN monto_efectivo DECIMAL(12,2) DEFAULT 0.00 AFTER total;"));
PREPARE stmt FROM @sql; EXECUTE stmt; DEALLOCATE PREPARE stmt;

-- monto_transferencia
SET @col = 'monto_transferencia';
SET @sql = (SELECT IF((SELECT COUNT(*) FROM INFORMATION_SCHEMA.COLUMNS WHERE table_name = @tablename AND table_schema = @dbname AND column_name = @col) > 0, "SELECT 1", "ALTER TABLE ventas ADD COLUMN monto_transferencia DECIMAL(12,2) DEFAULT 0.00 AFTER monto_efectivo;"));
PREPARE stmt FROM @sql; EXECUTE stmt; DEALLOCATE PREPARE stmt;

-- monto_tarjeta
SET @col = 'monto_tarjeta';
SET @sql = (SELECT IF((SELECT COUNT(*) FROM INFORMATION_SCHEMA.COLUMNS WHERE table_name = @tablename AND table_schema = @dbname AND column_name = @col) > 0, "SELECT 1", "ALTER TABLE ventas ADD COLUMN monto_tarjeta DECIMAL(12,2) DEFAULT 0.00 AFTER monto_transferencia;"));
PREPARE stmt FROM @sql; EXECUTE stmt; DEALLOCATE PREPARE stmt;

-- num_operacion_trans
SET @col = 'num_operacion_trans';
SET @sql = (SELECT IF((SELECT COUNT(*) FROM INFORMATION_SCHEMA.COLUMNS WHERE table_name = @tablename AND table_schema = @dbname AND column_name = @col) > 0, "SELECT 1", "ALTER TABLE ventas ADD COLUMN num_operacion_trans VARCHAR(50) NULL AFTER monto_tarjeta;"));
PREPARE stmt FROM @sql; EXECUTE stmt; DEALLOCATE PREPARE stmt;

-- num_operacion_tarj
SET @col = 'num_operacion_tarj';
SET @sql = (SELECT IF((SELECT COUNT(*) FROM INFORMATION_SCHEMA.COLUMNS WHERE table_name = @tablename AND table_schema = @dbname AND column_name = @col) > 0, "SELECT 1", "ALTER TABLE ventas ADD COLUMN num_operacion_tarj VARCHAR(50) NULL AFTER num_operacion_trans;"));
PREPARE stmt FROM @sql; EXECUTE stmt; DEALLOCATE PREPARE stmt;

-- motivo_descuento
SET @col = 'motivo_descuento';
SET @sql = (SELECT IF((SELECT COUNT(*) FROM INFORMATION_SCHEMA.COLUMNS WHERE table_name = @tablename AND table_schema = @dbname AND column_name = @col) > 0, "SELECT 1", "ALTER TABLE ventas ADD COLUMN motivo_descuento VARCHAR(255) NULL AFTER descuento;"));
PREPARE stmt FROM @sql; EXECUTE stmt; DEALLOCATE PREPARE stmt;

-- 2. Asegurar que clientes tiene puntos_acumulados
SET @tablenameC = 'clientes';
SET @colC = 'puntos_acumulados';
SET @sqlC = (SELECT IF((SELECT COUNT(*) FROM INFORMATION_SCHEMA.COLUMNS WHERE table_name = @tablenameC AND table_schema = @dbname AND column_name = @colC) > 0, "SELECT 1", "ALTER TABLE clientes ADD COLUMN puntos_acumulados INT DEFAULT 0 AFTER direccion;"));
PREPARE stmtC FROM @sqlC; EXECUTE stmtC; DEALLOCATE PREPARE stmtC;

-- 3. Crear tabla de auditoría e historial de puntos por cliente
CREATE TABLE IF NOT EXISTS `cliente_puntos_historial` (
  `id` INT NOT NULL AUTO_INCREMENT,
  `id_cliente` INT NOT NULL,
  `id_usuario` INT NOT NULL,
  `tipo` ENUM('ACUMULACION', 'CANJE', 'AJUSTE_MANUAL', 'ANULACION') COLLATE utf8mb4_unicode_ci NOT NULL,
  `puntos` INT NOT NULL,
  `saldo_anterior` INT NOT NULL DEFAULT 0,
  `saldo_nuevo` INT NOT NULL DEFAULT 0,
  `motivo` VARCHAR(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `id_venta` INT DEFAULT NULL,
  `created_at` TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_cli_puntos` (`id_cliente`),
  KEY `idx_usr_puntos` (`id_usuario`),
  KEY `idx_venta_puntos` (`id_venta`),
  CONSTRAINT `fk_puntos_cliente` FOREIGN KEY (`id_cliente`) REFERENCES `clientes` (`id`) ON DELETE CASCADE,
  CONSTRAINT `fk_puntos_usuario` FOREIGN KEY (`id_usuario`) REFERENCES `usuarios` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 4. Parámetros de configuración de puntos en configuracion
INSERT INTO `configuracion` (`clave`, `valor`, `descripcion`) VALUES
  ('puntos_consumo_base', '10.00', 'Monto en soles de consumo requerido para acumular 1 punto'),
  ('puntos_valor_canje', '0.10', 'Valor en soles de descuento por cada punto canjeado'),
  ('puntos_habilitado', '1', 'Habilita el programa de fidelización por puntos (1=Activo, 0=Inactivo)')
ON DUPLICATE KEY UPDATE `descripcion` = VALUES(`descripcion`);
