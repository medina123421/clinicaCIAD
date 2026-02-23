-- Tabla para Consulta de Nutrición
CREATE TABLE IF NOT EXISTS consulta_nutricion (
    id_nutricion INT AUTO_INCREMENT PRIMARY KEY,
    id_paciente INT NOT NULL,
    fecha_consulta DATETIME DEFAULT CURRENT_TIMESTAMP,
    
    -- I. Diagnostico
    diag_dislipidemia TINYINT(1) DEFAULT 0,
    diag_renal TINYINT(1) DEFAULT 0,
    diag_hepatica TINYINT(1) DEFAULT 0,
    diag_cardiovascular TINYINT(1) DEFAULT 0,
    diag_hipotiroidismo_hiper TINYINT(1) DEFAULT 0,
    diag_cancer TINYINT(1) DEFAULT 0,
    diag_especificar TEXT,

    -- II. Sintomas
    sint_nauseas TINYINT(1) DEFAULT 0,
    sint_vomito TINYINT(1) DEFAULT 0,
    sint_diarrea TINYINT(1) DEFAULT 0,
    sint_estrenimiento TINYINT(1) DEFAULT 0,
    sint_distension TINYINT(1) DEFAULT 0,
    sint_acidez_reflujo TINYINT(1) DEFAULT 0,
    sint_fatiga TINYINT(1) DEFAULT 0,

    -- III. Signos
    signos_temperatura VARCHAR(20),
    signos_presion_arterial VARCHAR(20),

    -- D. Evaluación Dietética (Frecuencia)
    frec_verduras VARCHAR(50),
    frec_frutas VARCHAR(50),
    frec_cereales VARCHAR(50),
    frec_leguminosas VARCHAR(50),
    frec_carnes_rojas VARCHAR(50),
    frec_pollo_pescado VARCHAR(50),
    frec_lacteos VARCHAR(50),
    frec_huevos VARCHAR(50),
    frec_procesados VARCHAR(50),
    frec_te_azucar VARCHAR(50),
    frec_cafe VARCHAR(50),
    frec_cafe_azucar VARCHAR(50),
    frec_refresco VARCHAR(50),
    frec_jugos VARCHAR(50),
    frec_agua VARCHAR(50),
    alergias_alimentarias TEXT,

    -- Recordatorio 24h
    rec_desayuno_hora TIME,
    rec_desayuno_desc TEXT,
    rec_desayuno_hc INT,
    rec_almuerzo_hora TIME,
    rec_almuerzo_desc TEXT,
    rec_almuerzo_hc INT,
    rec_comida_hora TIME,
    rec_comida_desc TEXT,
    rec_comida_hc INT,
    rec_colacion_hora TIME,
    rec_colacion_desc TEXT,
    rec_colacion_hc INT,
    rec_cena_hora TIME,
    rec_cena_desc TEXT,
    rec_cena_hc INT,

    -- I. Medicamentos y Suplementos
    med_toma_actualmente TINYINT(1) DEFAULT 0,
    med_suplementos_check TINYINT(1) DEFAULT 0,
    med_suplem_multivitaminico TINYINT(1) DEFAULT 0,
    med_suplem_suero_leche TINYINT(1) DEFAULT 0,
    med_suplem_proteina_vegana TINYINT(1) DEFAULT 0,
    med_suplem_creatina TINYINT(1) DEFAULT 0,
    med_suplem_vit_d TINYINT(1) DEFAULT 0,
    med_suplem_omega_3 TINYINT(1) DEFAULT 0,
    med_suplem_otros VARCHAR(255),

    -- E. Estilo de vida
    af_realiza TINYINT(1) DEFAULT 0,
    af_frecuencia VARCHAR(50),
    af_tipo VARCHAR(100),
    af_duracion VARCHAR(50),
    af_dias_descanso VARCHAR(50),
    
    hab_tabaquismo TINYINT(1) DEFAULT 0,
    hab_alcoholismo TINYINT(1) DEFAULT 0,
    hab_duerme_bien TINYINT(1) DEFAULT 0,
    hab_horas_sueno VARCHAR(50),
    hab_estres TINYINT(1) DEFAULT 0,
    comentarios_objetivos TEXT,

    -- I. Diagnostico Nutricional (Checklist)
    dn_ingesta_carbohidratos TINYINT(1) DEFAULT 0,
    dn_ingesta_sodio TINYINT(1) DEFAULT 0,
    dn_ingesta_proteina TINYINT(1) DEFAULT 0,
    dn_conocimientos_deficientes TINYINT(1) DEFAULT 0,
    dn_no_cumplimiento TINYINT(1) DEFAULT 0,
    dn_mejorar_digestion TINYINT(1) DEFAULT 0,
    dn_otro VARCHAR(255),

    -- II. Tratamiento y Objetivos
    trat_tipo_dieta VARCHAR(255),
    
    obj_reduccion_masa TINYINT(1) DEFAULT 0,
    obj_aumentar_masa TINYINT(1) DEFAULT 0,
    obj_mejorar_estado TINYINT(1) DEFAULT 0,
    obj_aumentar_energia TINYINT(1) DEFAULT 0,
    obj_mejorar_digestion TINYINT(1) DEFAULT 0,
    obj_otro VARCHAR(255),

    suplem_recomendada_multi TINYINT(1) DEFAULT 0,
    suplem_recomendada_suero TINYINT(1) DEFAULT 0,
    suplem_recomendada_vegana TINYINT(1) DEFAULT 0,
    suplem_recomendada_creatina TINYINT(1) DEFAULT 0,
    suplem_recomendada_vit_d TINYINT(1) DEFAULT 0,
    suplem_recomendada_omega_3 TINYINT(1) DEFAULT 0,
    suplem_recomendada_otros VARCHAR(255),

    rec_evitar_azucar VARCHAR(255), -- Storing checks as comma separated or handle in code? Let's use individual checks or text for simplicity if many. The image shows predefined categories. Let's use text to store selected values (e.g. "Refresco,Pan Dulce")
    rec_evitar_ultraprocesados VARCHAR(255),
    rec_evitar_preparaciones VARCHAR(255),
    rec_otros TEXT,

    created_by INT,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,

    FOREIGN KEY (id_paciente) REFERENCES pacientes(id_paciente)
);
