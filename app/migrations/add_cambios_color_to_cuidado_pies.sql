-- ============================================================================
-- MIGRACIÓN: Agregar campo cambios_color a cuidado_pies
-- Fecha: 2026-02-26
-- ============================================================================

ALTER TABLE cuidado_pies
  ADD COLUMN cambios_color TINYINT(1) DEFAULT 0
  COMMENT 'Cambios de color en la piel de piernas o pies'
  AFTER perdida_sensacion;

