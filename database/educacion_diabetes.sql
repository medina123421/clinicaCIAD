-- ============================================================================
-- TABLA: educacion_diabetes
-- Especialidad de Educación en Diabetes (CIADI)
-- Versión Final Integrada: Autocuidado + Medicación + Resolución de Problemas
-- ============================================================================

CREATE TABLE IF NOT EXISTS educacion_diabetes (
    id_educacion_diabetes INT(11) AUTO_INCREMENT PRIMARY KEY,
    id_visita INT(11) NOT NULL,              -- Enlace con la tabla visitas

    -- 1) EVALUACIÓN DE CONOCIMIENTOS Y DIAGNÓSTICO EDUCATIVO
    conocimientos_deficientes_nutricion TINYINT(1) DEFAULT 0 COMMENT 'Conocimientos deficientes sobre alimentación',
    no_cumple_recomendaciones TINYINT(1) DEFAULT 0 COMMENT 'No cumplimiento de recomendaciones',
    ingesta_excesiva_carbohidratos TINYINT(1) DEFAULT 0 COMMENT 'Ingesta excesiva de carbohidratos simples',
    manejo_inadecuado_hipoglucemia TINYINT(1) DEFAULT 0 COMMENT 'Manejo inadecuado de hipoglucemia',

    -- Barreras de Aprendizaje (Sincronización con Socioeconómico/Psicología)
    barrera_nivel_educativo TINYINT(1) DEFAULT 0,
    barrera_economica TINYINT(1) DEFAULT 0 COMMENT 'Vincular con dificultad_dieta_economica en tabla estudio_socioeconomico',
    barrera_apoyo_familiar TINYINT(1) DEFAULT 0,
    barrera_psicologica TINYINT(1) DEFAULT 0 COMMENT 'Duelo, negación o depresión',
    otras_barreras TEXT DEFAULT NULL,

    -- 2) ENTRENAMIENTO EN HABILIDADES TÉCNICAS (Insulina y Monitoreo)
    tecnica_seleccion_jeringa ENUM('No', 'En Proceso', 'Sí') DEFAULT 'No',
    tecnica_angulacion_pliegue ENUM('No', 'En Proceso', 'Sí') DEFAULT 'No',
    tecnica_almacenamiento_insulina ENUM('No', 'En Proceso', 'Sí') DEFAULT 'No',
    
    rotacion_sitios_abdomen ENUM('No', 'En Proceso', 'Sí') DEFAULT 'No',
    rotacion_sitios_muslos ENUM('No', 'En Proceso', 'Sí') DEFAULT 'No',
    rotacion_sitios_brazos ENUM('No', 'En Proceso', 'Sí') DEFAULT 'No',
    deteccion_lipodistrofias ENUM('No', 'En Proceso', 'Sí') DEFAULT 'No',
    
    uso_glucometro ENUM('No', 'En Proceso', 'Sí') DEFAULT 'No',
    uso_lancetero ENUM('No', 'En Proceso', 'Sí') DEFAULT 'No',
    registro_bitacora ENUM('No', 'En Proceso', 'Sí') DEFAULT 'No',
    frecuencia_medicion_adecuada ENUM('No', 'En Proceso', 'Sí') DEFAULT 'No',
    interpretacion_resultados ENUM('No', 'En Proceso', 'Sí') DEFAULT 'No',

    -- 3) HABILIDADES DE MEDICACIÓN ORAL (Mejora 1)
    conoce_mecanismo_accion TINYINT(1) DEFAULT 0 COMMENT '¿Sabe para qué sirve su medicación oral?',
    identifica_efectos_secundarios TINYINT(1) DEFAULT 0 COMMENT '¿Reconoce efectos adversos?',
    olvido_dosis_frecuencia ENUM('Nunca', '1 vez/semana', 'Más de 3 veces/semana') DEFAULT 'Nunca',
    adherencia_oral_metformina TINYINT(1) DEFAULT 1 COMMENT 'Seguimiento específico de tratamiento base',

    -- 4) PREVENCIÓN Y RESOLUCIÓN DE PROBLEMAS (Mejora 3)
    identificacion_sintomas_hipo ENUM('No', 'En Proceso', 'Sí') DEFAULT 'No',
    aplicacion_regla_15 ENUM('No', 'En Proceso', 'Sí') DEFAULT 'No' COMMENT '15g HC y esperar 15 min',
    identificacion_sintomas_hiper ENUM('No', 'En Proceso', 'Sí') DEFAULT 'No',
    cuando_medir_cetonas ENUM('No', 'En Proceso', 'Sí') DEFAULT 'No' COMMENT '¿Sabe cuándo medir cetonas?',
    sabe_manejar_dias_enfermedad TINYINT(1) DEFAULT 0 COMMENT 'Sick-day rules: Qué hacer con vómito/fiebre',
    plan_accion_crisis TINYINT(1) DEFAULT 0 COMMENT '¿Sabe a quién llamar en emergencia?',

    -- 5) EDUCACIÓN NUTRICIONAL Y SUPLEMENTACIÓN (Mejora 5)
    conteo_carbohidratos_nivel ENUM('Nulo', 'Básico', 'Avanzado') DEFAULT 'Nulo',
    lectura_etiquetas ENUM('No', 'En Proceso', 'Sí') DEFAULT 'No',
    calculo_porciones ENUM('No', 'En Proceso', 'Sí') DEFAULT 'No',
    
    -- Checklist de Suplementación (Referencia Pág. 6 PDF)
    conoce_uso_suplementos TINYINT(1) DEFAULT 0,
    suplemento_vit_d TINYINT(1) DEFAULT 0,
    suplemento_omega_3 TINYINT(1) DEFAULT 0,
    suplemento_creatina TINYINT(1) DEFAULT 0,
    suplemento_proteina_suero TINYINT(1) DEFAULT 0,

    -- Recomendaciones Generales (Alimentos a evitar)
    evita_refrescos TINYINT(1) DEFAULT 0,
    evita_pan_dulce TINYINT(1) DEFAULT 0,
    evita_jugos TINYINT(1) DEFAULT 0,
    evita_mermeladas TINYINT(1) DEFAULT 0,
    evita_ultraprocesados TINYINT(1) DEFAULT 0,

    -- 6) SEGUIMIENTO DE METAS (Lógica SMART - Mejora 4)
    meta_hba1c_objetivo DECIMAL(4,2) DEFAULT 7.0,
    meta_glucosa_ayunas_max INT(11) DEFAULT 130,
    meta_reduccion_peso TINYINT(1) DEFAULT 0,
    meta_ejercicio_regular TINYINT(1) DEFAULT 0,
    meta_adherencia_alimentacion TINYINT(1) DEFAULT 0,
    
    metas_cumplidas_anteriores TEXT DEFAULT NULL,
    nuevas_metas_establecidas TEXT DEFAULT NULL,

    -- 7) EVALUACIÓN ANTROPOMÉTRICA (Snapshot de la sesión)
    peso_actual DECIMAL(5,2) DEFAULT NULL,
    talla_actual DECIMAL(5,2) DEFAULT NULL,
    imc_actual DECIMAL(5,2) DEFAULT NULL,
    circunferencia_cintura DECIMAL(5,2) DEFAULT NULL,
    porcentaje_grasa DECIMAL(5,2) DEFAULT NULL,
    masa_muscular_kg DECIMAL(5,2) DEFAULT NULL,

    -- Recordatorio de 24h y Frecuencia Semanal
    recordatorio_24h_resumen TEXT DEFAULT NULL,
    freq_agua_litros ENUM('< 1 litro', '1-2 litros', '2-3 litros', '3+ litros') DEFAULT '< 1 litro',
    freq_frutas_verduras ENUM('0-2 porciones', '3-5 porciones', '5+ porciones') DEFAULT '0-2 porciones' COMMENT 'Resumen de consumo diario',

    -- 8) SEMÁFORO EDUCATIVO (Resultado Automático)
    semaforo_educativo ENUM('Rojo', 'Amarillo', 'Verde') DEFAULT 'Rojo',
    nivel_autonomia ENUM('Dependiente', 'Semi-autónomo', 'Autónomo') DEFAULT 'Dependiente',

    -- METADATOS
    observaciones_educador TEXT DEFAULT NULL,
    material_educativo_entregado TEXT DEFAULT NULL,
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    created_by INT(11) DEFAULT NULL,

    FOREIGN KEY (id_visita) REFERENCES visitas(id_visita) ON DELETE CASCADE,
    INDEX idx_educacion_visita (id_visita),
    INDEX idx_semaforo (semaforo_educativo),
    INDEX idx_autonomia (nivel_autonomia)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;