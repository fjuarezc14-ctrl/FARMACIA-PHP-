USE `botica_db`;

-- ==========================================================
-- Fase 13: Pagos Mixtos en POS (Efectivo + Yape/Plin + Tarjeta)
-- Columnas NULL para ventas antiguas: los reportes y el cierre de caja
-- usan COALESCE(monto_x, CASE metodo_pago ...) como respaldo.
-- ==========================================================
ALTER TABLE `ventas`
  ADD COLUMN `monto_efectivo` decimal(12,2) DEFAULT NULL AFTER `metodo_pago`,
  ADD COLUMN `monto_transferencia` decimal(12,2) DEFAULT NULL AFTER `monto_efectivo`,
  ADD COLUMN `monto_tarjeta` decimal(12,2) DEFAULT NULL AFTER `monto_transferencia`,
  ADD COLUMN `num_operacion_trans` varchar(50) COLLATE utf8mb4_unicode_ci DEFAULT NULL AFTER `monto_tarjeta`,
  ADD COLUMN `num_operacion_tarj` varchar(50) COLLATE utf8mb4_unicode_ci DEFAULT NULL AFTER `num_operacion_trans`;
