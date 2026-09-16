USE `botica_db`;

-- ==========================================================
-- Fase 12: Suite Nativa de Accesibilidad Web (WCAG 2.1 / ADA)
-- Inserción de configuraciones globales para el widget nativo
-- ==========================================================

INSERT INTO `configuracion` (`clave`, `valor`, `descripcion`) VALUES
('a11y_habilitado', '1', 'Habilita o deshabilita la suite nativa de accesibilidad en el sistema (1=Activo, 0=Inactivo)'),
('a11y_posicion', 'bottom-right', 'Posición del botón flotante de accesibilidad (bottom-right, bottom-left, top-right, top-left)'),
('a11y_lector_voz', '1', 'Habilita la herramienta nativa de síntesis de voz / lector de pantalla (1=Activo, 0=Inactivo)')
ON DUPLICATE KEY UPDATE 
    `descripcion` = VALUES(`descripcion`);
