-- Tabla para la Historia Clínica Nutricional
CREATE TABLE IF NOT EXISTS historia_nutricional (
    id_historia INT AUTO_INCREMENT PRIMARY KEY,
    id_paciente INT NOT NULL,
    fecha_historia DATETIME DEFAULT CURRENT_TIMESTAMP,
    
    -- A. Evaluación Antropométrica
    peso DECIMAL(5,2),
    talla DECIMAL(5,2),
    circunferencia_cintura DECIMAL(5,2),
    porcentaje_grasa DECIMAL(5,2),
    kilos_grasa DECIMAL(5,2),
    indice_masa_muscular DECIMAL(5,2),
    kilos_masa_muscular DECIMAL(5,2),
    imc DECIMAL(5,2),
    
    -- C. Evaluación Clínica
    -- I. Diagnóstico Médico Actual (JSON: diabetes_t1, diabetes_t2, pre_diabetes, hta, obesidad, dislipidemia, renal, hepatica, cardiovascular, hipo_hipertiroidismo, cancer)
    diagnosticos_medicos JSON,
    diagnostico_especificar TEXT, -- "Especifique el diagnóstico y las alergias"
    
    -- II. Síntomas (JSON: nauseas, vomito, diarrea, estrenimiento, distension, acidez, fatiga)
    sintomas JSON,
    
    -- III. Signos
    temperatura DECIMAL(4,1),
    presion_arterial VARCHAR(20),
    
    -- D. Evaluación Dietética
    -- Frecuencia de Consumo (JSON: verduras, frutas, cereales, leguminosas, carnes_rojas, pollo_pescado, lacteos, huevos, procesados, te, cafe, cafe_azucar, refresco, jugos, agua)
    frecuencia_consumo JSON,
    
    -- Alergias/Intolerancias
    alergias_alimentarias VARCHAR(10), -- Si/No
    alergias_alimentarias_cual TEXT,
    
    -- Recordatorio 24h (JSON: desayuno, almuerzo, comida, colacion, cena - cada uno con hora, especificar, carbohidratos)
    recordatorio_24h JSON,
    
    -- I. Medicamentos y Suplementos
    toma_medicamentos VARCHAR(10), -- Si/No
    toma_suplementos VARCHAR(10), -- Si/No
    suplementos_detalle JSON, -- Checkboxes: multivitaminico, proteina, etc.
    suplementos_otro TEXT,
    
    -- E. Evaluación de Estilo de Vida
    -- I. Actividad Física
    realiza_ejercicio VARCHAR(10), -- Si/No
    ejercicio_frecuencia VARCHAR(50),
    ejercicio_tipo VARCHAR(100), -- Aerobico, Pesas, etc.
    ejercicio_duracion VARCHAR(50),
    dias_descanso VARCHAR(50),
    
    -- II. Hábitos
    tabaquismo VARCHAR(10),
    alcoholismo VARCHAR(10),
    duerme_bien VARCHAR(10),
    horas_sueno VARCHAR(50),
    maneja_estres VARCHAR(10),
    comentarios_objetivos TEXT,
    
    -- I. Diagnóstico Nutricional (JSON: checkboxes de ingesta excesiva, etc.)
    diagnostico_nutricional JSON,
    diagnostico_nutricional_otro TEXT,
    
    -- II. Tratamiento y Objetivos Nutricionales
    tipo_dieta VARCHAR(100), -- Normoproteica, etc.
    objetivos_tratamiento JSON, -- Checkboxes: Reducción masa, Aumentar masa, etc.
    objetivos_otro TEXT,
    
    -- Recomendaciones Generales (JSON o Texto)
    recomendaciones_generales JSON, -- Evitar altos en azucar, ultraprocesados, etc.
    recomendaciones_otros TEXT,
    
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    
    FOREIGN KEY (id_paciente) REFERENCES pacientes(id_paciente) ON DELETE CASCADE
);
