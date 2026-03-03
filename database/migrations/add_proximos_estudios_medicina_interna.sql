-- Migration: Add "Próximos Estudios" to consulta_medicina_interna
-- Based on the user's specific request for upcoming internal medicine studies.

ALTER TABLE consulta_medicina_interna
ADD COLUMN prox_bh TINYINT(1) DEFAULT 0 AFTER observaciones_adicionales,
ADD COLUMN prox_osc TINYINT(1) DEFAULT 0 AFTER prox_bh,
ADD COLUMN prox_ego TINYINT(1) DEFAULT 0 AFTER prox_osc,
ADD COLUMN prox_hba1c TINYINT(1) DEFAULT 0 AFTER prox_ego,
ADD COLUMN prox_perfil_tiroideo TINYINT(1) DEFAULT 0 AFTER prox_hba1c,
ADD COLUMN prox_perfil_hepatico TINYINT(1) DEFAULT 0 AFTER prox_perfil_tiroideo,
ADD COLUMN prox_insulina_basal TINYINT(1) DEFAULT 0 AFTER prox_perfil_hepatico,
ADD COLUMN prox_qs3_i TINYINT(1) DEFAULT 0 AFTER prox_insulina_basal,
ADD COLUMN prox_qs3_ii TINYINT(1) DEFAULT 0 AFTER prox_qs3_i;

-- Note: QS3 I includes (Glucosa, Urea, Creatinina y Bun)
-- Note: QS3 II includes (Glucosa, Trigliseridos, Colesterol)
