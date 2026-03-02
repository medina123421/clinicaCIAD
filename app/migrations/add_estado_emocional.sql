-- Migration: Add estado_emocional to estudio_socioeconomico
ALTER TABLE estudio_socioeconomico ADD COLUMN estado_emocional VARCHAR(50) DEFAULT NULL AFTER observations_trabajo_social;
