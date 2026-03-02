-- ============================================================================
-- MIGRACIÓN: Agregar BDI-2 y estado de cambio a educacion_diabetes
-- Fecha: 2026-02-23
-- ============================================================================

ALTER TABLE educacion_diabetes
  ADD COLUMN estado_cambio ENUM(
    'Precontemplación',
    'Contemplación',
    'Preparación',
    'Acción',
    'Mantenimiento',
    'Recaída'
  ) DEFAULT NULL AFTER nivel_autonomia;

ALTER TABLE educacion_diabetes
  ADD COLUMN bdi2_item_01 TINYINT(4) DEFAULT NULL AFTER estado_cambio,
  ADD COLUMN bdi2_item_02 TINYINT(4) DEFAULT NULL AFTER bdi2_item_01,
  ADD COLUMN bdi2_item_03 TINYINT(4) DEFAULT NULL AFTER bdi2_item_02,
  ADD COLUMN bdi2_item_04 TINYINT(4) DEFAULT NULL AFTER bdi2_item_03,
  ADD COLUMN bdi2_item_05 TINYINT(4) DEFAULT NULL AFTER bdi2_item_04,
  ADD COLUMN bdi2_item_06 TINYINT(4) DEFAULT NULL AFTER bdi2_item_05,
  ADD COLUMN bdi2_item_07 TINYINT(4) DEFAULT NULL AFTER bdi2_item_06,
  ADD COLUMN bdi2_item_08 TINYINT(4) DEFAULT NULL AFTER bdi2_item_07,
  ADD COLUMN bdi2_item_09 TINYINT(4) DEFAULT NULL AFTER bdi2_item_08,
  ADD COLUMN bdi2_item_10 TINYINT(4) DEFAULT NULL AFTER bdi2_item_09,
  ADD COLUMN bdi2_item_11 TINYINT(4) DEFAULT NULL AFTER bdi2_item_10,
  ADD COLUMN bdi2_item_12 TINYINT(4) DEFAULT NULL AFTER bdi2_item_11,
  ADD COLUMN bdi2_item_13 TINYINT(4) DEFAULT NULL AFTER bdi2_item_12,
  ADD COLUMN bdi2_item_14 TINYINT(4) DEFAULT NULL AFTER bdi2_item_13,
  ADD COLUMN bdi2_item_15 TINYINT(4) DEFAULT NULL AFTER bdi2_item_14,
  ADD COLUMN bdi2_item_16 TINYINT(4) DEFAULT NULL AFTER bdi2_item_15,
  ADD COLUMN bdi2_item_17 TINYINT(4) DEFAULT NULL AFTER bdi2_item_16,
  ADD COLUMN bdi2_item_18 TINYINT(4) DEFAULT NULL AFTER bdi2_item_17,
  ADD COLUMN bdi2_item_19 TINYINT(4) DEFAULT NULL AFTER bdi2_item_18,
  ADD COLUMN bdi2_item_20 TINYINT(4) DEFAULT NULL AFTER bdi2_item_19,
  ADD COLUMN bdi2_item_21 TINYINT(4) DEFAULT NULL AFTER bdi2_item_20,
  ADD COLUMN bdi2_puntuacion_total TINYINT(4) DEFAULT NULL AFTER bdi2_item_21,
  ADD COLUMN bdi2_clasificacion VARCHAR(20) DEFAULT NULL AFTER bdi2_puntuacion_total;

-- Índice opcional si se usa para reportes
-- CREATE INDEX idx_bdi2_clasificacion ON educacion_diabetes (bdi2_clasificacion);

