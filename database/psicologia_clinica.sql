CREATE TABLE IF NOT EXISTS consulta_psicologia (
    id_psicologia INT AUTO_INCREMENT PRIMARY KEY,
    id_visita INT NOT NULL,
    id_paciente INT NOT NULL,
    numero_visita INT NOT NULL DEFAULT 1, -- 1 al 5

    -- Descripción del paciente (texto libre por visita)
    descripcion_paciente TEXT,

    -- VISITA 1: Proceso del Duelo en la Enfermedad (Inventarios de Beck)
    -- Escala: Leve, Moderada, Severa, N/A
    v1_ansiedad_beck ENUM('Leve','Moderada','Severa','N/A') DEFAULT NULL,
    v1_depresion_beck ENUM('Leve','Moderada','Severa','N/A') DEFAULT NULL,
    v1_desesperanza_beck ENUM('Leve','Moderada','Severa','N/A') DEFAULT NULL,
    v1_observaciones TEXT,

    -- VISITA 2: Limitantes para la Adherencia al Tratamiento
    -- Escala: Siempre, Casi Siempre, Nunca, Algunas Veces, N/A
    v2_nivel_personal ENUM('Siempre','Casi Siempre','Nunca','Algunas Veces','N/A') DEFAULT NULL,
    v2_nivel_economico ENUM('Siempre','Casi Siempre','Nunca','Algunas Veces','N/A') DEFAULT NULL,
    v2_nivel_social ENUM('Siempre','Casi Siempre','Nunca','Algunas Veces','N/A') DEFAULT NULL,
    v2_nivel_sanitario ENUM('Siempre','Casi Siempre','Nunca','Algunas Veces','N/A') DEFAULT NULL,
    v2_observaciones TEXT,

    -- VISITA 3: Estados de Cambio en la Motivación
    -- Escala: Siempre, Casi Siempre, Nunca, Algunas Veces, N/A
    v3_pre_contemplacion ENUM('Siempre','Casi Siempre','Nunca','Algunas Veces','N/A') DEFAULT NULL,
    v3_contemplacion ENUM('Siempre','Casi Siempre','Nunca','Algunas Veces','N/A') DEFAULT NULL,
    v3_decision ENUM('Siempre','Casi Siempre','Nunca','Algunas Veces','N/A') DEFAULT NULL,
    v3_accion ENUM('Siempre','Casi Siempre','Nunca','Algunas Veces','N/A') DEFAULT NULL,
    v3_mantenimiento ENUM('Siempre','Casi Siempre','Nunca','Algunas Veces','N/A') DEFAULT NULL,
    v3_recaida ENUM('Siempre','Casi Siempre','Nunca','Algunas Veces','N/A') DEFAULT NULL,
    v3_observaciones TEXT,

    -- VISITA 4: Técnicas de Relajación por Respiración Profunda
    -- Escala: Siempre, Casi Siempre, Nunca, Algunas Veces, N/A
    v4_logro_relajacion ENUM('Siempre','Casi Siempre','Nunca','Algunas Veces','N/A') DEFAULT NULL,
    v4_descripcion_paciente TEXT,
    v4_observaciones TEXT,

    -- VISITA 5: Tristeza y Depresión
    -- Escala: Siempre, Casi Siempre, Nunca, Algunas Veces, N/A
    v5_tristeza ENUM('Siempre','Casi Siempre','Nunca','Algunas Veces','N/A') DEFAULT NULL,
    v5_depresion ENUM('Siempre','Casi Siempre','Nunca','Algunas Veces','N/A') DEFAULT NULL,
    v5_observaciones TEXT,

    fecha_registro TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,

    FOREIGN KEY (id_visita) REFERENCES visitas(id_visita) ON DELETE CASCADE,
    FOREIGN KEY (id_paciente) REFERENCES pacientes(id_paciente) ON DELETE CASCADE
);
