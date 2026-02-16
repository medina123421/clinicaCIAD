CREATE TABLE IF NOT EXISTS consulta_medicina_interna (
    id_medicina_interna INT AUTO_INCREMENT PRIMARY KEY,
    id_visita INT NOT NULL,
    
    -- 0. Datos Contacto Emergencia
    nombre_emergencia VARCHAR(255),
    telefono_emergencia VARCHAR(50),
    parentesco_emergencia VARCHAR(100),

    -- 1. Diagnóstico de Diabetes
    tipo_diabetes ENUM('Tipo 1', 'Tipo 2', 'Gestacional', 'Otra'),
    anio_diagnostico INT,
    ultima_hba1c DECIMAL(5,2),
    control_actual ENUM('Bueno', 'Regular', 'Malo'),

    -- 2. Enfermedades Cardiovasculares (Checklist)
    hta TINYINT(1) DEFAULT 0,
    enfermedad_coronaria TINYINT(1) DEFAULT 0,
    infarto_miocardio TINYINT(1) DEFAULT 0,
    insuficiencia_cardiaca TINYINT(1) DEFAULT 0,
    dislipidemia TINYINT(1) DEFAULT 0,
    enf_vascular_periferica TINYINT(1) DEFAULT 0,

    -- 3. Complicaciones Microvasculares (Checklist)
    retinopatia_diabetica TINYINT(1) DEFAULT 0,
    nefropatia_diabetica TINYINT(1) DEFAULT 0,
    neuropatia_periferica TINYINT(1) DEFAULT 0,
    neuropatia_autonomica TINYINT(1) DEFAULT 0,

    -- 4. Enfermedades Infecciosas (Checklist)
    infecciones_urinarias TINYINT(1) DEFAULT 0,
    pie_diabetico TINYINT(1) DEFAULT 0,
    infecciones_piel TINYINT(1) DEFAULT 0,
    tuberculosis TINYINT(1) DEFAULT 0,
    hepatitis_b_c TINYINT(1) DEFAULT 0,

    -- 5. Endocrino–Metabólicas Asociadas
    obesidad TINYINT(1) DEFAULT 0,
    enfermedad_tiroidea TINYINT(1) DEFAULT 0,
    sindrome_metabolico TINYINT(1) DEFAULT 0,

    -- 6. Renales y Genitourinarias
    insuficiencia_renal_cronica TINYINT(1) DEFAULT 0,
    proteinuria TINYINT(1) DEFAULT 0,
    nefrolitiasis TINYINT(1) DEFAULT 0,

    -- 7. Gastrointestinales
    higado_graso TINYINT(1) DEFAULT 0,
    pancreatitis TINYINT(1) DEFAULT 0,
    gastroparesia TINYINT(1) DEFAULT 0,

    -- 8. Neurológicas
    evc TINYINT(1) DEFAULT 0,
    neuropatia_periferica_previa TINYINT(1) DEFAULT 0,
    amputaciones TINYINT(1) DEFAULT 0,

    -- 9. Salud Mental
    depresion TINYINT(1) DEFAULT 0,
    ansiedad TINYINT(1) DEFAULT 0,
    trastornos_sueno TINYINT(1) DEFAULT 0,

    -- Antecedentes Relevantes (Checklist)
    alergias_check TINYINT(1) DEFAULT 0,
    enfermedades_cronicas_check TINYINT(1) DEFAULT 0,
    cirugias_previas_check TINYINT(1) DEFAULT 0,
    hospitalizaciones_previas_check TINYINT(1) DEFAULT 0,

    -- 10. Medicación y Tratamiento (Granularidad basada en imágenes)
    -- Antidiabéticos Orales
    med_metformina TINYINT(1) DEFAULT 0,
    med_sulfonilureas TINYINT(1) DEFAULT 0,
    med_meglitinidas TINYINT(1) DEFAULT 0,
    med_inhibidores_alfa_glucosidasa TINYINT(1) DEFAULT 0,
    med_tzd TINYINT(1) DEFAULT 0,
    med_inhibidores_dpp4 TINYINT(1) DEFAULT 0,
    med_inhibidores_sglt2 TINYINT(1) DEFAULT 0,
    
    -- Inyectables no insulinas
    med_agonistas_glp1 TINYINT(1) DEFAULT 0,
    
    -- Insulinas
    med_insulina_basal TINYINT(1) DEFAULT 0,
    med_insulina_rapida_ultrarrapida TINYINT(1) DEFAULT 0,
    med_insulina_mezclas TINYINT(1) DEFAULT 0,
    
    -- Otros Tratamientos
    med_estatinas TINYINT(1) DEFAULT 0,
    med_antihipertensivos TINYINT(1) DEFAULT 0,
    med_antiagregantes TINYINT(1) DEFAULT 0,
    
    detalles_medicacion TEXT,

    -- 11. Datos Clínicos y Signos Vitales
    peso DECIMAL(5,2),
    talla INT,
    imc DECIMAL(5,2),
    circunferencia_abdominal DECIMAL(5,2),
    presion_arterial VARCHAR(20),
    frecuencia_cardiaca INT,
    temperatura DECIMAL(4,1),
    frecuencia_respiratoria INT,
    glucosa_capilar INT,

    -- 12. Estilo de Vida
    alimentacion_adecuada TINYINT(1) DEFAULT 0,
    actividad_fisica TINYINT(1) DEFAULT 0,
    consumo_alcohol TINYINT(1) DEFAULT 0,
    tabaquismo TINYINT(1) DEFAULT 0,
    horarios_comida_regulares TINYINT(1) DEFAULT 0,
    
    -- 13. Educación y Prevención
    educacion_diabetologica TINYINT(1) DEFAULT 0,
    tecnica_insulina TINYINT(1) DEFAULT 0,
    revision_sitio_inyeccion TINYINT(1) DEFAULT 0,
    prevencion_hipoglucemia TINYINT(1) DEFAULT 0,
    cuidado_pies TINYINT(1) DEFAULT 0,
    revision_metas TINYINT(1) DEFAULT 0,

    -- 14. Salud Mental y Bienestar
    estado_animo ENUM('Bueno', 'Regular', 'Malo'),
    sintomas_ansiedad_depresion TINYINT(1) DEFAULT 0,
    estres_enfermedad TINYINT(1) DEFAULT 0,
    apoyo_familiar_social TINYINT(1) DEFAULT 0,

    observaciones_adicionales TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    created_by INT,
    FOREIGN KEY (id_visita) REFERENCES visitas(id_visita) ON DELETE CASCADE
);
