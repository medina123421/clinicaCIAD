CREATE TABLE IF NOT EXISTS consulta_nutricion (
    id_nutricion INT AUTO_INCREMENT PRIMARY KEY,
    id_visita INT NOT NULL,
    id_paciente INT NOT NULL,
    
    -- 1. Evaluacion Antropometrica
    peso DECIMAL(5,2),
    talla DECIMAL(5,2),
    circunferencia_cintura DECIMAL(5,2),
    imc DECIMAL(5,2),
    porcentaje_grasa DECIMAL(5,2),
    kilos_grasa DECIMAL(5,2),
    indice_masa_muscular DECIMAL(5,2),
    kilos_masa_muscular DECIMAL(5,2),

    -- 2. Evaluacion Clinica
    diagnosticos_medicos JSON, -- Array de strings
    diagnostico_especificar TEXT,
    sintomas JSON, -- Array de strings
    temperatura DECIMAL(4,1),
    presion_arterial VARCHAR(20),
    
    toma_medicamentos ENUM('Si', 'No') DEFAULT 'No',
    toma_suplementos ENUM('Si', 'No') DEFAULT 'No',
    suplementos_detalle JSON, -- Array de strings
    suplementos_otro TEXT,

    -- 3. Evaluacion Dietetica
    frecuencia_consumo JSON, -- Objeto {verduras: 'opt', frutas: 'opt', ...}
    alergias_alimentarias ENUM('Si', 'No') DEFAULT 'No',
    alergias_alimentarias_cual TEXT,
    recordatorio_24h JSON, -- Objeto {desayuno: {hora, desc, hc}, ...}

    -- 4. Estilo de Vida
    realiza_ejercicio ENUM('Si', 'No') DEFAULT 'No',
    ejercicio_frecuencia VARCHAR(100),
    ejercicio_tipo VARCHAR(100),
    ejercicio_duracion VARCHAR(100),
    dias_descanso VARCHAR(100),
    
    tabaquismo ENUM('Si', 'No') DEFAULT 'No',
    alcoholismo ENUM('Si', 'No') DEFAULT 'No',
    maneja_estres ENUM('Si', 'No') DEFAULT 'No',
    duerme_bien ENUM('Si', 'No') DEFAULT 'No',
    horas_sueno VARCHAR(50),
    comentarios_objetivos TEXT,

    -- 5. Diagnostico y Tratamiento
    diagnostico_nutricional JSON, -- Array de strings
    diagnostico_nutricional_otro TEXT,
    
    tipo_dieta VARCHAR(100),
    objetivos_tratamiento JSON, -- Array de strings
    objetivos_otro TEXT,
    
    recomendaciones_generales JSON, -- Array de strings
    recomendaciones_otros TEXT,

    fecha_registro TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    
    FOREIGN KEY (id_visita) REFERENCES visitas(id_visita) ON DELETE CASCADE,
    FOREIGN KEY (id_paciente) REFERENCES pacientes(id_paciente) ON DELETE CASCADE
);
