-- ============================================================================
-- TABLA: actividad_fisica
-- Especialidad de Actividad Física / Rehabilitación
-- ============================================================================

CREATE TABLE IF NOT EXISTS actividad_fisica (
    id_actividad_fisica INT(11) AUTO_INCREMENT PRIMARY KEY,
    id_visita INT(11) NOT NULL,              -- Enlace con la tabla visitas
    
    -- 1) TAMIZAJE SARC-F5 (Cuestionario de Sarcopenia)
    sarc_fuerza TINYINT(4) COMMENT '0: Ninguno, 1: Alguna, 2: Mucha',
    sarc_asistencia_caminar TINYINT(4),
    sarc_levantarse_silla TINYINT(4),
    sarc_subir_escaleras TINYINT(4),
    sarc_caidas TINYINT(4) COMMENT '0: Ninguna, 1: 1-3 caídas, 2: 4 o más',
    sarc_puntuacion_total TINYINT(4),       -- Suma total para estadísticas
    sarc_riesgo ENUM('Baja', 'Alta') DEFAULT 'Baja',
    
    -- 2) DINAMOMETRÍA (Fuerza de agarre)
    dina_mano_der DECIMAL(5,2) DEFAULT NULL,
    dina_mano_izq DECIMAL(5,2) DEFAULT NULL,
    dina_percentil_resultado VARCHAR(20) DEFAULT NULL, 
    
    -- 3) ESCALA DE DANIELS (Fuerza Muscular 0–5)
    daniels_ms_der TINYINT(4) DEFAULT NULL,
    daniels_ms_izq TINYINT(4) DEFAULT NULL,
    daniels_mi_der TINYINT(4) DEFAULT NULL,
    daniels_mi_izq TINYINT(4) DEFAULT NULL,
    
    -- 4) SIT-TO-STAND (Pruebas de Resistencia)
    sts_30seg_reps TINYINT(4) DEFAULT NULL COMMENT 'Repeticiones en 30s',
    sts_5rep_seg DECIMAL(5,2) DEFAULT NULL COMMENT 'Tiempo para 5 reps',
    sts_5rep_alerta TINYINT(1) DEFAULT 0 COMMENT '1 si tiempo > 15 seg',
    
    -- 5) DOLOR (Escala EVA)
    eva_zona VARCHAR(150) DEFAULT NULL,
    eva_puntaje TINYINT(4) DEFAULT NULL COMMENT 'Escala 0-10',
    
    -- 6) MOVILIDAD ARTICULAR (Oxford / Daniels modificada 0-5)
    mov_ms_der TINYINT(4) DEFAULT NULL,
    mov_ms_izq TINYINT(4) DEFAULT NULL,
    mov_mi_der TINYINT(4) DEFAULT NULL,
    mov_mi_izq TINYINT(4) DEFAULT NULL,
    
    -- 7) ACTIVIDAD ACTUAL (Detalle de la visita)
    act_realiza_ejercicio TINYINT(1) DEFAULT 0,
    act_frecuencia ENUM('0', '1-2', '3-4', '5+') DEFAULT '0',
    act_tipo ENUM('Aerobico', 'Fuerza', 'Combinado', 'Otro') DEFAULT NULL,
    act_tipo_otro VARCHAR(150) DEFAULT NULL,
    act_duracion ENUM('0-30', '30-60', '60+') DEFAULT NULL,
    act_dias_descanso ENUM('1-2', '2-3', '3+') DEFAULT NULL,
    
    -- NOTAS Y METADATOS
    observaciones_especialista TEXT DEFAULT NULL,
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    created_by INT(11) DEFAULT NULL,
    
    FOREIGN KEY (id_visita) REFERENCES visitas(id_visita) ON DELETE CASCADE,
    INDEX idx_actividad_visita (id_visita)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

