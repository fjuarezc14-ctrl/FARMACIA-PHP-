USE `botica_db`;

-- ==========================================================
-- Fase 14: Trazabilidad de descuentos en ventas
-- tipo_descuento: 'Manual' (requiere motivo) | 'Puntos' (canje de fidelización)
-- ==========================================================
ALTER TABLE `ventas`
  ADD COLUMN `tipo_descuento` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT NULL AFTER `descuento`,
  ADD COLUMN `motivo_descuento` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL AFTER `tipo_descuento`;

-- Ventas anteriores con descuento y puntos usados: se marcan como canje de puntos
UPDATE `ventas`
   SET `tipo_descuento` = 'Puntos',
       `motivo_descuento` = CONCAT('Canje de ', `puntos_usados`, ' puntos')
 WHERE `descuento` > 0 AND `puntos_usados` > 0 AND `tipo_descuento` IS NULL;
