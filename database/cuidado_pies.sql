-- ============================================================================
-- TABLA: cuidado_pies
-- Especialidad de Cuidado de los Pies / Podología
-- ============================================================================

CREATE TABLE IF NOT EXISTS cuidado_pies (
    id_cuidado_pies INT(11) AUTO_INCREMENT PRIMARY KEY,
    id_visita INT(11) NOT NULL,              -- Enlace con la tabla visitas
    
    -- 1) INTERROGATORIO CLÍNICO (Antecedentes Específicos)
    -- Antecedentes de Lesiones (Sí/No)
    ulcera_previa TINYINT(1) DEFAULT 0 COMMENT 'Úlcera previa en pierna o pie',
    amputacion_previa TINYINT(1) DEFAULT 0 COMMENT 'Amputación de extremidades inferiores',
    cirugia_previa TINYINT(1) DEFAULT 0 COMMENT 'Cirugía previa en pierna o pie',
    herida_lenta TINYINT(1) DEFAULT 0 COMMENT 'Herida que tardó más de 3 meses en sanar',
    
    -- Sintomatología Actual (Sí/No)
    ardor_hormigueo TINYINT(1) DEFAULT 0 COMMENT 'Ardor u hormigueo en piernas o pies',
    dolor_actividad TINYINT(1) DEFAULT 0 COMMENT 'Dolor en pierna/pie con actividad',
    dolor_reposo TINYINT(1) DEFAULT 0 COMMENT 'Dolor en pierna/pie en reposo',
    perdida_sensacion TINYINT(1) DEFAULT 0 COMMENT 'Pérdida de sensación en extremidades inferiores',
    
    -- Hábitos y Seguimiento
    fuma TINYINT(1) DEFAULT 0 COMMENT 'Fuma actualmente',
    cigarrillos_dia TINYINT(4) DEFAULT NULL COMMENT 'Cigarrillos al día si fuma',
    revision_pies_previa TINYINT(1) DEFAULT 0 COMMENT 'Han revisado sus pies previamente',
    
    -- 2) EXAMEN DERMATOLÓGICO (Matriz Derecha/Izquierda) - Calificación 0, 1, 2
    -- Hiperqueratosis
    hiper_plantar_der TINYINT(4) DEFAULT 0,
    hiper_plantar_izq TINYINT(4) DEFAULT 0,
    hiper_dorsal_der TINYINT(4) DEFAULT 0,
    hiper_dorsal_izq TINYINT(4) DEFAULT 0,
    hiper_talar_der TINYINT(4) DEFAULT 0,
    hiper_talar_izq TINYINT(4) DEFAULT 0,
    
    -- Alteraciones Ungueales
    onicocriptosis_der TINYINT(4) DEFAULT 0 COMMENT 'Uña enterrada derecha',
    onicocriptosis_izq TINYINT(4) DEFAULT 0,
    onicomicosis_der TINYINT(4) DEFAULT 0 COMMENT 'Hongos uñas derecha',
    onicomicosis_izq TINYINT(4) DEFAULT 0,
    onicogrifosis_der TINYINT(4) DEFAULT 0 COMMENT 'Uña engrosada derecha',
    onicogrifosis_izq TINYINT(4) DEFAULT 0,
    
    -- Otras Lesiones
    bullosis_der TINYINT(4) DEFAULT 0 COMMENT 'Ampollas derecha',
    bullosis_izq TINYINT(4) DEFAULT 0,
    necrosis_der TINYINT(4) DEFAULT 0,
    necrosis_izq TINYINT(4) DEFAULT 0,
    grietas_fisuras_der TINYINT(4) DEFAULT 0,
    grietas_fisuras_izq TINYINT(4) DEFAULT 0,
    lesion_superficial_der TINYINT(4) DEFAULT 0,
    lesion_superficial_izq TINYINT(4) DEFAULT 0,
    anhidrosis_der TINYINT(4) DEFAULT 0,
    anhidrosis_izq TINYINT(4) DEFAULT 0,
    tina_der TINYINT(4) DEFAULT 0,
    tina_izq TINYINT(4) DEFAULT 0,
    proceso_infeccioso_der TINYINT(4) DEFAULT 0,
    proceso_infeccioso_izq TINYINT(4) DEFAULT 0,
    
    -- Úlcera (tipo específico)
    ulcera_venosa_der TINYINT(4) DEFAULT 0,
    ulcera_venosa_izq TINYINT(4) DEFAULT 0,
    ulcera_arterial_der TINYINT(4) DEFAULT 0,
    ulcera_arterial_izq TINYINT(4) DEFAULT 0,
    ulcera_mixta_der TINYINT(4) DEFAULT 0,
    ulcera_mixta_izq TINYINT(4) DEFAULT 0,
    ulcera_otra_der TINYINT(4) DEFAULT 0,
    ulcera_otra_izq TINYINT(4) DEFAULT 0,
    
    -- 3) EXAMEN DE ESTRUCTURA ÓSEA (Matriz Derecha/Izquierda) - Calificación 0, 1, 2
    hallux_valgus_der TINYINT(4) DEFAULT 0,
    hallux_valgus_izq TINYINT(4) DEFAULT 0,
    dedos_garra_der TINYINT(4) DEFAULT 0,
    dedos_garra_izq TINYINT(4) DEFAULT 0,
    dedos_martillo_der TINYINT(4) DEFAULT 0,
    dedos_martillo_izq TINYINT(4) DEFAULT 0,
    infraducto_der TINYINT(4) DEFAULT 0,
    infraducto_izq TINYINT(4) DEFAULT 0,
    supraducto_der TINYINT(4) DEFAULT 0,
    supraducto_izq TINYINT(4) DEFAULT 0,
    pie_cavo_der TINYINT(4) DEFAULT 0,
    pie_cavo_izq TINYINT(4) DEFAULT 0,
    arco_caido_der TINYINT(4) DEFAULT 0,
    arco_caido_izq TINYINT(4) DEFAULT 0,
    talo_varo_der TINYINT(4) DEFAULT 0,
    talo_varo_izq TINYINT(4) DEFAULT 0,
    espolon_calcaneo_der TINYINT(4) DEFAULT 0,
    espolon_calcaneo_izq TINYINT(4) DEFAULT 0,
    hipercargas_metatarsianos_der TINYINT(4) DEFAULT 0,
    hipercargas_metatarsianos_izq TINYINT(4) DEFAULT 0,
    pie_charcot_der TINYINT(4) DEFAULT 0,
    pie_charcot_izq TINYINT(4) DEFAULT 0,
    
    -- 4) EXAMEN VASCULAR Y NEUROLÓGICO
    -- A. Valoración Vascular
    pulso_pedio_der ENUM('Presente', 'Disminuido', 'Ausente') DEFAULT 'Presente',
    pulso_pedio_izq ENUM('Presente', 'Disminuido', 'Ausente') DEFAULT 'Presente',
    pulso_tibial_der ENUM('Presente', 'Disminuido', 'Ausente') DEFAULT 'Presente',
    pulso_tibial_izq ENUM('Presente', 'Disminuido', 'Ausente') DEFAULT 'Presente',
    llenado_capilar_der DECIMAL(4,1) DEFAULT NULL COMMENT 'Segundos',
    llenado_capilar_izq DECIMAL(4,1) DEFAULT NULL COMMENT 'Segundos',
    varices TINYINT(1) DEFAULT 0,
    edema_godet ENUM('Sin edema', 'Grado I', 'Grado II', 'Grado III', 'Grado IV') DEFAULT 'Sin edema',
    
    -- B. Valoración Neurológica
    monofilamento_puntos TINYINT(4) DEFAULT NULL COMMENT 'Puntos positivos 0-10',
    sensibilidad_vibratoria_seg DECIMAL(4,1) DEFAULT NULL COMMENT 'Segundos con diapasón',
    reflejo_rotuliano TINYINT(4) DEFAULT 0 COMMENT '0, 1, 2',
    dorsiflexion_pie TINYINT(4) DEFAULT 0 COMMENT '0, 1, 2',
    apertura_ortejos TINYINT(4) DEFAULT 0 COMMENT '0, 1, 2',
    
    -- 5) INTEGRACIÓN CON SISTEMA GENERAL
    educacion_cuidado_pies TINYINT(1) DEFAULT 0 COMMENT 'Se impartió técnica de cuidado de pies',
    
    -- 6) LÓGICA DE RIESGO Y ALERTAS (Calculados automáticamente)
    puntuacion_total_der INT(11) DEFAULT 0 COMMENT 'Suma total pie derecho',
    puntuacion_total_izq INT(11) DEFAULT 0 COMMENT 'Suma total pie izquierdo',
    riesgo_der ENUM('Leve', 'Moderado', 'Alto') DEFAULT 'Leve',
    riesgo_izq ENUM('Leve', 'Moderado', 'Alto') DEFAULT 'Leve',
    alerta_roja TINYINT(1) DEFAULT 0 COMMENT 'Requiere envío urgente',
    
    -- NOTAS Y METADATOS
    observaciones_especialista TEXT DEFAULT NULL,
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    created_by INT(11) DEFAULT NULL,
    
    FOREIGN KEY (id_visita) REFERENCES visitas(id_visita) ON DELETE CASCADE,
    INDEX idx_cuidado_visita (id_visita),
    INDEX idx_riesgo_alto (riesgo_der, riesgo_izq),
    INDEX idx_alerta_roja (alerta_roja)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;