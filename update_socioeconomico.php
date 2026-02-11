<?php
require_once 'app/config/database.php';

try {
    $database = new Database();
    $db = $database->getConnection();

    // 1. Tabla estudio_socioeconomico
    $sql1 = "CREATE TABLE IF NOT EXISTS estudio_socioeconomico (
        id_estudio INT AUTO_INCREMENT PRIMARY KEY,
        id_paciente INT NOT NULL,
        fecha_estudio DATE DEFAULT (CURRENT_DATE),
        
        -- I. Datos Generales
        religion VARCHAR(100),
        tiempo_residencia VARCHAR(100),
        escolaridad VARCHAR(100),
        estado_civil ENUM('Soltero', 'Casado', 'Divorciado', 'Viudo', 'Union Libre'),
        ocupacion VARCHAR(150),
        
        -- II. Estructura Familiar
        es_jefe_familia BOOLEAN,
        relaciones_familiares ENUM('Armónicas', 'Conflictivas', 'Aisladas'),
        apoyo_familiar ENUM('Muy Alto', 'Medio', 'Bajo', 'Nulo'),
        
        -- III. Vivienda
        tipo_vivienda ENUM('Propia', 'Rentada', 'Prestada', 'Otra'),
        material_vivienda TEXT,
        num_habitaciones INT,
        servicio_agua BOOLEAN DEFAULT FALSE,
        servicio_drenaje BOOLEAN DEFAULT FALSE,
        servicio_electricidad BOOLEAN DEFAULT FALSE,
        servicio_gas BOOLEAN DEFAULT FALSE,
        servicio_internet BOOLEAN DEFAULT FALSE,
        
        -- IV. Economía
        ingreso_mensual_familiar DECIMAL(10,2),
        gasto_renta DECIMAL(10,2),
        gasto_alimentos DECIMAL(10,2),
        gasto_transporte DECIMAL(10,2),
        gasto_servicios DECIMAL(10,2),
        gasto_tratamientos DECIMAL(10,2),
        gasto_total_estimado DECIMAL(10,2),
        apoyo_social_check BOOLEAN DEFAULT FALSE,
        apoyo_social_nombre VARCHAR(200),
        ingreso_cubre_necesidades BOOLEAN DEFAULT FALSE,
        
        -- V. Salud (Diabetes Context)
        diagnostico_desc TEXT,
        servicio_medico JSON COMMENT 'IMSS, ISSSTE, INSABI, Privado, No cuenta',
        tratamiento_actual JSON COMMENT 'Insulina, Metformina, Otro',
        cubre_costos_medicamento BOOLEAN,
        cuenta_con_glucometro BOOLEAN,
        dificultad_dieta_economica BOOLEAN,
        
        -- VI. Alimentación
        frecuencia_alimentos JSON COMMENT 'Matriz de frecuencia de consumo',
        
        -- VII. Conclusiones
        observaciones_trabajo_social TEXT,
        nivel_socioeconomico ENUM('Alto', 'Medio', 'Bajo', 'Vulnerabilidad Extrema'),
        plan_intervencion TEXT,
        nombre_entrevistado VARCHAR(200),
        nombre_trabajador_social VARCHAR(200),
        
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
        
        FOREIGN KEY (id_paciente) REFERENCES pacientes(id_paciente) ON DELETE CASCADE,
        INDEX idx_paciente (id_paciente)
    ) ENGINE=InnoDB;";

    $db->exec($sql1);
    echo "Tabla 'estudio_socioeconomico' creada o verificada correctamente.\n";

    // 2. Tabla estudio_socioeconomico_familiares
    $sql2 = "CREATE TABLE IF NOT EXISTS estudio_socioeconomico_familiares (
        id_familiar_estudio INT AUTO_INCREMENT PRIMARY KEY,
        id_estudio INT NOT NULL,
        nombre VARCHAR(200) NOT NULL,
        parentesco VARCHAR(100),
        edad INT,
        ocupacion VARCHAR(150),
        ingreso_mensual DECIMAL(10,2),
        
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        
        FOREIGN KEY (id_estudio) REFERENCES estudio_socioeconomico(id_estudio) ON DELETE CASCADE,
        INDEX idx_estudio (id_estudio)
    ) ENGINE=InnoDB;";

    $db->exec($sql2);
    echo "Tabla 'estudio_socioeconomico_familiares' creada o verificada correctamente.\n";

} catch (PDOException $e) {
    echo "Error BD: " . $e->getMessage();
}
?>