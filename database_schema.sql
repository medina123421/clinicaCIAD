-- ============================================================================
-- BASE DE DATOS: CLÍNICA DE DIABETES Y PREDIABETES
-- Sistema de gestión integral para pacientes diabéticos
-- Versión: 1.0
-- Motor: MySQL 8.0+
-- ============================================================================

-- Crear base de datos
CREATE DATABASE IF NOT EXISTS clinica_diabetes
CHARACTER SET utf8mb4
COLLATE utf8mb4_unicode_ci;

USE clinica_diabetes;

-- ============================================================================
-- MÓDULO 1: USUARIOS Y AUTENTICACIÓN
-- ============================================================================

-- Tabla de roles
CREATE TABLE roles (
    id_rol INT AUTO_INCREMENT PRIMARY KEY,
    nombre_rol VARCHAR(50) NOT NULL UNIQUE,
    descripcion TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB;

-- Tabla de usuarios (Doctores y Administradores)
CREATE TABLE usuarios (
    id_usuario INT AUTO_INCREMENT PRIMARY KEY,
    id_rol INT NOT NULL,
    nombre VARCHAR(100) NOT NULL,
    apellido_paterno VARCHAR(100) NOT NULL,
    apellido_materno VARCHAR(100),
    email VARCHAR(150) NOT NULL UNIQUE,
    password_hash VARCHAR(255) NOT NULL,
    cedula_profesional VARCHAR(50),
    especialidad VARCHAR(100),
    telefono VARCHAR(20),
    activo BOOLEAN DEFAULT TRUE,
    ultimo_acceso DATETIME,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (id_rol) REFERENCES roles(id_rol),
    INDEX idx_email (email),
    INDEX idx_activo (activo)
) ENGINE=InnoDB;

-- Tabla de sesiones
CREATE TABLE sesiones (
    id_sesion INT AUTO_INCREMENT PRIMARY KEY,
    id_usuario INT NOT NULL,
    token VARCHAR(255) NOT NULL UNIQUE,
    ip_address VARCHAR(45),
    user_agent TEXT,
    fecha_inicio DATETIME DEFAULT CURRENT_TIMESTAMP,
    fecha_expiracion DATETIME,
    activa BOOLEAN DEFAULT TRUE,
    FOREIGN KEY (id_usuario) REFERENCES usuarios(id_usuario),
    INDEX idx_token (token),
    INDEX idx_usuario (id_usuario)
) ENGINE=InnoDB;

-- ============================================================================
-- MÓDULO 2: PACIENTES
-- ============================================================================

-- Tabla de pacientes
CREATE TABLE pacientes (
    id_paciente INT AUTO_INCREMENT PRIMARY KEY,
    numero_expediente VARCHAR(50) NOT NULL UNIQUE,
    nombre VARCHAR(100) NOT NULL,
    apellido_paterno VARCHAR(100) NOT NULL,
    apellido_materno VARCHAR(100),
    fecha_nacimiento DATE NOT NULL,
    edad INT,
    sexo ENUM('M', 'F') NOT NULL,
    curp VARCHAR(18) UNIQUE,
    rfc VARCHAR(13),
    nss VARCHAR(11),
    telefono VARCHAR(20),
    celular VARCHAR(20),
    email VARCHAR(150),
    direccion TEXT,
    ciudad VARCHAR(100),
    estado VARCHAR(100),
    codigo_postal VARCHAR(10),
    ocupacion VARCHAR(100),
    estado_civil ENUM('Soltero', 'Casado', 'Divorciado', 'Viudo', 'Union Libre'),
    tipo_sangre ENUM('A+', 'A-', 'B+', 'B-', 'AB+', 'AB-', 'O+', 'O-'),
    alergias TEXT,
    fecha_registro DATE DEFAULT (CURRENT_DATE),
    activo BOOLEAN DEFAULT TRUE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    created_by INT,
    FOREIGN KEY (created_by) REFERENCES usuarios(id_usuario),
    INDEX idx_expediente (numero_expediente),
    INDEX idx_nombre (nombre, apellido_paterno),
    INDEX idx_curp (curp)
) ENGINE=InnoDB;

-- Tabla de contactos de emergencia
CREATE TABLE contactos_emergencia (
    id_contacto INT AUTO_INCREMENT PRIMARY KEY,
    id_paciente INT NOT NULL,
    nombre_completo VARCHAR(200) NOT NULL,
    parentesco VARCHAR(50),
    telefono VARCHAR(20) NOT NULL,
    celular VARCHAR(20),
    es_principal BOOLEAN DEFAULT FALSE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (id_paciente) REFERENCES pacientes(id_paciente) ON DELETE CASCADE,
    INDEX idx_paciente (id_paciente)
) ENGINE=InnoDB;

-- Tabla de antecedentes familiares
CREATE TABLE antecedentes_familiares (
    id_antecedente INT AUTO_INCREMENT PRIMARY KEY,
    id_paciente INT NOT NULL,
    parentesco VARCHAR(50),
    enfermedad VARCHAR(100),
    observaciones TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (id_paciente) REFERENCES pacientes(id_paciente) ON DELETE CASCADE,
    INDEX idx_paciente (id_paciente)
) ENGINE=InnoDB;

-- ============================================================================
-- MÓDULO 3: VISITAS Y CONSULTAS
-- ============================================================================

-- Tabla de visitas
CREATE TABLE visitas (
    id_visita INT AUTO_INCREMENT PRIMARY KEY,
    id_paciente INT NOT NULL,
    id_doctor INT NOT NULL,
    fecha_visita DATETIME NOT NULL,
    tipo_visita ENUM('Primera Vez', 'Seguimiento', 'Urgencia', 'Control') DEFAULT 'Seguimiento',
    motivo_consulta TEXT,
    diagnostico TEXT,
    plan_tratamiento TEXT,
    observaciones TEXT,
    proxima_cita DATE,
    estatus ENUM('Programada', 'En Curso', 'Completada', 'Cancelada') DEFAULT 'Programada',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    created_by INT,
    FOREIGN KEY (id_paciente) REFERENCES pacientes(id_paciente),
    FOREIGN KEY (id_doctor) REFERENCES usuarios(id_usuario),
    FOREIGN KEY (created_by) REFERENCES usuarios(id_usuario),
    INDEX idx_paciente (id_paciente),
    INDEX idx_doctor (id_doctor),
    INDEX idx_fecha (fecha_visita),
    INDEX idx_estatus (estatus)
) ENGINE=InnoDB;

-- Tabla de datos clínicos (signos vitales)
CREATE TABLE datos_clinicos (
    id_dato_clinico INT AUTO_INCREMENT PRIMARY KEY,
    id_visita INT NOT NULL,
    peso DECIMAL(5,2) COMMENT 'kg',
    talla DECIMAL(5,2) COMMENT 'cm',
    imc DECIMAL(5,2) COMMENT 'Calculado automáticamente',
    circunferencia_abdominal DECIMAL(5,2) COMMENT 'cm',
    presion_arterial_sistolica INT COMMENT 'mmHg',
    presion_arterial_diastolica INT COMMENT 'mmHg',
    frecuencia_cardiaca INT COMMENT 'lpm',
    frecuencia_respiratoria INT COMMENT 'rpm',
    temperatura DECIMAL(4,2) COMMENT '°C',
    saturacion_oxigeno INT COMMENT '%',
    glucosa_capilar DECIMAL(5,2) COMMENT 'mg/dL',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (id_visita) REFERENCES visitas(id_visita) ON DELETE CASCADE,
    INDEX idx_visita (id_visita)
) ENGINE=InnoDB;

-- Tabla de notas de consulta
CREATE TABLE notas_consulta (
    id_nota INT AUTO_INCREMENT PRIMARY KEY,
    id_visita INT NOT NULL,
    tipo_nota ENUM('Evolución', 'Interconsulta', 'Procedimiento', 'Otro') DEFAULT 'Evolución',
    contenido TEXT NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    created_by INT,
    FOREIGN KEY (id_visita) REFERENCES visitas(id_visita) ON DELETE CASCADE,
    FOREIGN KEY (created_by) REFERENCES usuarios(id_usuario),
    INDEX idx_visita (id_visita)
) ENGINE=InnoDB;

-- ============================================================================
-- MÓDULO 4: ANÁLISIS CLÍNICOS
-- ============================================================================

-- Tabla de análisis de glucosa y control glucémico
CREATE TABLE analisis_glucosa (
    id_analisis INT AUTO_INCREMENT PRIMARY KEY,
    id_visita INT NOT NULL,
    fecha_analisis DATE NOT NULL,
    glucosa_ayunas DECIMAL(5,2) COMMENT 'mg/dL',
    glucosa_postprandial_2h DECIMAL(5,2) COMMENT 'mg/dL',
    hemoglobina_glicosilada DECIMAL(4,2) COMMENT 'HbA1c %',
    curva_tolerancia_glucosa TEXT COMMENT 'Valores separados por comas',
    interpretacion_glucosa_ayunas ENUM('Normal', 'Precaución', 'Alerta'),
    interpretacion_hba1c ENUM('Normal', 'Precaución', 'Alerta'),
    observaciones TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    created_by INT,
    FOREIGN KEY (id_visita) REFERENCES visitas(id_visita) ON DELETE CASCADE,
    FOREIGN KEY (created_by) REFERENCES usuarios(id_usuario),
    INDEX idx_visita (id_visita),
    INDEX idx_fecha (fecha_analisis)
) ENGINE=InnoDB;

-- Tabla de perfil renal
CREATE TABLE analisis_perfil_renal (
    id_analisis INT AUTO_INCREMENT PRIMARY KEY,
    id_visita INT NOT NULL,
    fecha_analisis DATE NOT NULL,
    creatinina_serica DECIMAL(5,2) COMMENT 'mg/dL',
    tasa_filtracion_glomerular DECIMAL(5,2) COMMENT 'TFG mL/min/1.73m²',
    urea DECIMAL(5,2) COMMENT 'mg/dL',
    bun DECIMAL(5,2) COMMENT 'mg/dL',
    microalbuminuria DECIMAL(6,2) COMMENT 'mg/24h',
    relacion_albumina_creatinina DECIMAL(6,2) COMMENT 'ACR mg/g',
    ego_resultado TEXT COMMENT 'Examen General de Orina',
    interpretacion_tfg ENUM('Normal', 'Precaución', 'Alerta'),
    interpretacion_microalbuminuria ENUM('Normal', 'Precaución', 'Alerta'),
    observaciones TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    created_by INT,
    FOREIGN KEY (id_visita) REFERENCES visitas(id_visita) ON DELETE CASCADE,
    FOREIGN KEY (created_by) REFERENCES usuarios(id_usuario),
    INDEX idx_visita (id_visita),
    INDEX idx_fecha (fecha_analisis)
) ENGINE=InnoDB;

-- Tabla de perfil lipídico
CREATE TABLE analisis_perfil_lipidico (
    id_analisis INT AUTO_INCREMENT PRIMARY KEY,
    id_visita INT NOT NULL,
    fecha_analisis DATE NOT NULL,
    colesterol_total DECIMAL(5,2) COMMENT 'mg/dL',
    ldl DECIMAL(5,2) COMMENT 'mg/dL',
    hdl DECIMAL(5,2) COMMENT 'mg/dL',
    trigliceridos DECIMAL(6,2) COMMENT 'mg/dL',
    interpretacion_colesterol ENUM('Normal', 'Precaución', 'Alerta'),
    interpretacion_ldl ENUM('Normal', 'Precaución', 'Alerta'),
    interpretacion_hdl ENUM('Normal', 'Precaución', 'Alerta'),
    interpretacion_trigliceridos ENUM('Normal', 'Precaución', 'Alerta'),
    observaciones TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    created_by INT,
    FOREIGN KEY (id_visita) REFERENCES visitas(id_visita) ON DELETE CASCADE,
    FOREIGN KEY (created_by) REFERENCES usuarios(id_usuario),
    INDEX idx_visita (id_visita),
    INDEX idx_fecha (fecha_analisis)
) ENGINE=InnoDB;

-- Tabla de electrolitos y estado metabólico
CREATE TABLE analisis_electrolitos (
    id_analisis INT AUTO_INCREMENT PRIMARY KEY,
    id_visita INT NOT NULL,
    fecha_analisis DATE NOT NULL,
    sodio DECIMAL(5,2) COMMENT 'Na mEq/L',
    potasio DECIMAL(4,2) COMMENT 'K mEq/L',
    cloro DECIMAL(5,2) COMMENT 'Cl mEq/L',
    bicarbonato DECIMAL(4,2) COMMENT 'HCO3 mEq/L',
    calcio DECIMAL(4,2) COMMENT 'mg/dL',
    fosforo DECIMAL(4,2) COMMENT 'mg/dL',
    magnesio DECIMAL(4,2) COMMENT 'mg/dL',
    interpretacion_sodio ENUM('Normal', 'Precaución', 'Alerta'),
    interpretacion_potasio ENUM('Normal', 'Precaución', 'Alerta'),
    observaciones TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    created_by INT,
    FOREIGN KEY (id_visita) REFERENCES visitas(id_visita) ON DELETE CASCADE,
    FOREIGN KEY (created_by) REFERENCES usuarios(id_usuario),
    INDEX idx_visita (id_visita),
    INDEX idx_fecha (fecha_analisis)
) ENGINE=InnoDB;

-- Tabla de marcadores hepáticos
CREATE TABLE analisis_hepaticos (
    id_analisis INT AUTO_INCREMENT PRIMARY KEY,
    id_visita INT NOT NULL,
    fecha_analisis DATE NOT NULL,
    alt DECIMAL(6,2) COMMENT 'ALT U/L',
    ast DECIMAL(6,2) COMMENT 'AST U/L',
    fosfatasa_alcalina DECIMAL(6,2) COMMENT 'U/L',
    bilirrubina_total DECIMAL(4,2) COMMENT 'mg/dL',
    bilirrubina_directa DECIMAL(4,2) COMMENT 'mg/dL',
    albumina_serica DECIMAL(4,2) COMMENT 'g/dL',
    interpretacion_alt ENUM('Normal', 'Precaución', 'Alerta'),
    interpretacion_ast ENUM('Normal', 'Precaución', 'Alerta'),
    observaciones TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    created_by INT,
    FOREIGN KEY (id_visita) REFERENCES visitas(id_visita) ON DELETE CASCADE,
    FOREIGN KEY (created_by) REFERENCES usuarios(id_usuario),
    INDEX idx_visita (id_visita),
    INDEX idx_fecha (fecha_analisis)
) ENGINE=InnoDB;

-- Tabla de estudios cardiovasculares
CREATE TABLE analisis_cardiovascular (
    id_analisis INT AUTO_INCREMENT PRIMARY KEY,
    id_visita INT NOT NULL,
    fecha_analisis DATE NOT NULL,
    troponina DECIMAL(6,3) COMMENT 'ng/mL',
    bnp DECIMAL(8,2) COMMENT 'pg/mL',
    nt_probnp DECIMAL(8,2) COMMENT 'pg/mL',
    homocisteina DECIMAL(5,2) COMMENT 'µmol/L',
    interpretacion_troponina ENUM('Normal', 'Precaución', 'Alerta'),
    interpretacion_bnp ENUM('Normal', 'Precaución', 'Alerta'),
    observaciones TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    created_by INT,
    FOREIGN KEY (id_visita) REFERENCES visitas(id_visita) ON DELETE CASCADE,
    FOREIGN KEY (created_by) REFERENCES usuarios(id_usuario),
    INDEX idx_visita (id_visita),
    INDEX idx_fecha (fecha_analisis)
) ENGINE=InnoDB;

-- Tabla de otros estudios
CREATE TABLE analisis_otros (
    id_analisis INT AUTO_INCREMENT PRIMARY KEY,
    id_visita INT NOT NULL,
    fecha_analisis DATE NOT NULL,
    vitamina_d DECIMAL(5,2) COMMENT 'ng/mL',
    tsh DECIMAL(6,3) COMMENT 'µIU/mL',
    t4_libre DECIMAL(4,2) COMMENT 'ng/dL',
    hemoglobina DECIMAL(4,2) COMMENT 'g/dL',
    hematocrito DECIMAL(4,2) COMMENT '%',
    leucocitos DECIMAL(6,2) COMMENT 'células/µL',
    plaquetas DECIMAL(6,2) COMMENT 'células/µL',
    cetonas_sangre DECIMAL(4,2) COMMENT 'mmol/L',
    cetonas_orina BOOLEAN,
    peptido_c DECIMAL(5,2) COMMENT 'ng/mL',
    insulinemia DECIMAL(6,2) COMMENT 'µU/mL',
    proteina_c_reactiva DECIMAL(5,2) COMMENT 'PCR mg/L',
    velocidad_sedimentacion DECIMAL(5,2) COMMENT 'VSG mm/h',
    interpretacion_vitamina_d ENUM('Normal', 'Precaución', 'Alerta'),
    interpretacion_tsh ENUM('Normal', 'Precaución', 'Alerta'),
    observaciones TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    created_by INT,
    FOREIGN KEY (id_visita) REFERENCES visitas(id_visita) ON DELETE CASCADE,
    FOREIGN KEY (created_by) REFERENCES usuarios(id_usuario),
    INDEX idx_visita (id_visita),
    INDEX idx_fecha (fecha_analisis)
) ENGINE=InnoDB;

-- ============================================================================
-- MÓDULO 5: MEDICAMENTOS Y TRATAMIENTO
-- ============================================================================

-- Catálogo de medicamentos
CREATE TABLE medicamentos_catalogo (
    id_medicamento INT AUTO_INCREMENT PRIMARY KEY,
    nombre_generico VARCHAR(200) NOT NULL,
    nombre_comercial VARCHAR(200),
    categoria ENUM('Antidiabético Oral', 'Insulina', 'Antihipertensivo', 'Estatina', 'Antiagregante', 'Otro'),
    presentacion VARCHAR(100),
    via_administracion VARCHAR(50),
    activo BOOLEAN DEFAULT TRUE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_nombre (nombre_generico),
    INDEX idx_categoria (categoria)
) ENGINE=InnoDB;

-- Tratamientos del paciente
CREATE TABLE tratamientos (
    id_tratamiento INT AUTO_INCREMENT PRIMARY KEY,
    id_paciente INT NOT NULL,
    id_medicamento INT NOT NULL,
    id_visita INT COMMENT 'Visita en la que se prescribió',
    dosis VARCHAR(100) NOT NULL,
    frecuencia VARCHAR(100) NOT NULL,
    via_administracion VARCHAR(50),
    fecha_inicio DATE NOT NULL,
    fecha_fin DATE,
    activo BOOLEAN DEFAULT TRUE,
    indicaciones TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    created_by INT,
    FOREIGN KEY (id_paciente) REFERENCES pacientes(id_paciente),
    FOREIGN KEY (id_medicamento) REFERENCES medicamentos_catalogo(id_medicamento),
    FOREIGN KEY (id_visita) REFERENCES visitas(id_visita),
    FOREIGN KEY (created_by) REFERENCES usuarios(id_usuario),
    INDEX idx_paciente (id_paciente),
    INDEX idx_activo (activo)
) ENGINE=InnoDB;

-- Historial de ajustes de tratamiento
CREATE TABLE ajustes_tratamiento (
    id_ajuste INT AUTO_INCREMENT PRIMARY KEY,
    id_tratamiento INT NOT NULL,
    id_visita INT NOT NULL,
    dosis_anterior VARCHAR(100),
    dosis_nueva VARCHAR(100) NOT NULL,
    motivo_ajuste TEXT,
    fecha_ajuste DATE NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    created_by INT,
    FOREIGN KEY (id_tratamiento) REFERENCES tratamientos(id_tratamiento),
    FOREIGN KEY (id_visita) REFERENCES visitas(id_visita),
    FOREIGN KEY (created_by) REFERENCES usuarios(id_usuario),
    INDEX idx_tratamiento (id_tratamiento)
) ENGINE=InnoDB;

-- ============================================================================
-- MÓDULO 6: ESTILO DE VIDA
-- ============================================================================

-- Tabla de estilo de vida
CREATE TABLE estilo_vida (
    id_estilo_vida INT AUTO_INCREMENT PRIMARY KEY,
    id_visita INT NOT NULL,
    adherencia_plan_nutricional ENUM('Buena', 'Regular', 'Mala'),
    actividad_fisica ENUM('Sedentario', 'Ligera', 'Moderada', 'Intensa'),
    frecuencia_ejercicio VARCHAR(100),
    consumo_alcohol ENUM('No consume', 'Ocasional', 'Moderado', 'Frecuente'),
    tabaquismo ENUM('No fuma', 'Ex-fumador', 'Fumador Ocasional', 'Fumador Activo'),
    cigarrillos_dia INT,
    horarios_comida TEXT,
    educacion_diabetologica BOOLEAN DEFAULT FALSE,
    educacion_actualizada BOOLEAN DEFAULT FALSE,
    observaciones TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (id_visita) REFERENCES visitas(id_visita) ON DELETE CASCADE,
    INDEX idx_visita (id_visita)
) ENGINE=InnoDB;

-- Tabla de educación en diabetes
CREATE TABLE educacion_diabetes (
    id_educacion INT AUTO_INCREMENT PRIMARY KEY,
    id_paciente INT NOT NULL,
    id_visita INT,
    tema VARCHAR(200) NOT NULL,
    fecha_educacion DATE NOT NULL,
    tecnica_administracion_insulina BOOLEAN DEFAULT FALSE,
    revision_sitio_inyeccion BOOLEAN DEFAULT FALSE,
    prevencion_hipoglucemia BOOLEAN DEFAULT FALSE,
    cuidado_pies BOOLEAN DEFAULT FALSE,
    revision_metas_tratamiento BOOLEAN DEFAULT FALSE,
    estudios_pendientes TEXT,
    observaciones TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    created_by INT,
    FOREIGN KEY (id_paciente) REFERENCES pacientes(id_paciente),
    FOREIGN KEY (id_visita) REFERENCES visitas(id_visita),
    FOREIGN KEY (created_by) REFERENCES usuarios(id_usuario),
    INDEX idx_paciente (id_paciente)
) ENGINE=InnoDB;

-- ============================================================================
-- MÓDULO 7: COMPLICACIONES
-- ============================================================================

-- Tabla de complicaciones microvasculares
CREATE TABLE complicaciones_microvasculares (
    id_complicacion INT AUTO_INCREMENT PRIMARY KEY,
    id_visita INT NOT NULL,
    revision_pies BOOLEAN DEFAULT FALSE,
    ulcera_pies BOOLEAN DEFAULT FALSE,
    callosidades BOOLEAN DEFAULT FALSE,
    sensibilidad_alterada BOOLEAN DEFAULT FALSE,
    pulsos_pedios BOOLEAN DEFAULT TRUE,
    neuropatia BOOLEAN DEFAULT FALSE,
    monofilamento_positivo BOOLEAN DEFAULT FALSE,
    revision_funcion_renal BOOLEAN DEFAULT FALSE,
    nefropatia_diabetica BOOLEAN DEFAULT FALSE,
    revision_vision BOOLEAN DEFAULT FALSE,
    retinopatia_diabetica BOOLEAN DEFAULT FALSE,
    ultima_revision_oftalmologica DATE,
    observaciones TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (id_visita) REFERENCES visitas(id_visita) ON DELETE CASCADE,
    INDEX idx_visita (id_visita)
) ENGINE=InnoDB;

-- Tabla de complicaciones macrovasculares
CREATE TABLE complicaciones_macrovasculares (
    id_complicacion INT AUTO_INCREMENT PRIMARY KEY,
    id_visita INT NOT NULL,
    enfermedad_coronaria BOOLEAN DEFAULT FALSE,
    angina BOOLEAN DEFAULT FALSE,
    infarto_previo BOOLEAN DEFAULT FALSE,
    claudicacion_intermitente BOOLEAN DEFAULT FALSE,
    enfermedad_arterial_periferica BOOLEAN DEFAULT FALSE,
    evento_cerebrovascular BOOLEAN DEFAULT FALSE,
    ait BOOLEAN DEFAULT FALSE COMMENT 'Ataque Isquémico Transitorio',
    revision_riesgo_cardiovascular BOOLEAN DEFAULT FALSE,
    observaciones TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (id_visita) REFERENCES visitas(id_visita) ON DELETE CASCADE,
    INDEX idx_visita (id_visita)
) ENGINE=InnoDB;

-- ============================================================================
-- MÓDULO 8: CONTROL Y SEGUIMIENTO
-- ============================================================================

-- Tabla de glucometrías (bitácora diaria)
CREATE TABLE glucometrias (
    id_glucometria INT AUTO_INCREMENT PRIMARY KEY,
    id_paciente INT NOT NULL,
    fecha_hora DATETIME NOT NULL,
    glucosa DECIMAL(5,2) NOT NULL COMMENT 'mg/dL',
    momento ENUM('Ayunas', 'Preprandial', 'Postprandial', 'Antes de dormir', 'Madrugada', 'Otro'),
    observaciones TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (id_paciente) REFERENCES pacientes(id_paciente),
    INDEX idx_paciente (id_paciente),
    INDEX idx_fecha (fecha_hora)
) ENGINE=InnoDB;

-- Tabla de eventos de hipoglucemia
CREATE TABLE hipoglucemias (
    id_evento INT AUTO_INCREMENT PRIMARY KEY,
    id_paciente INT NOT NULL,
    id_visita INT,
    fecha_hora DATETIME NOT NULL,
    glucosa DECIMAL(5,2) COMMENT 'mg/dL',
    sintomas TEXT,
    severidad ENUM('Leve', 'Moderada', 'Severa'),
    tratamiento_aplicado TEXT,
    observaciones TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (id_paciente) REFERENCES pacientes(id_paciente),
    FOREIGN KEY (id_visita) REFERENCES visitas(id_visita),
    INDEX idx_paciente (id_paciente),
    INDEX idx_fecha (fecha_hora)
) ENGINE=InnoDB;

-- Tabla de eventos de hiperglucemia
CREATE TABLE hiperglucemias (
    id_evento INT AUTO_INCREMENT PRIMARY KEY,
    id_paciente INT NOT NULL,
    id_visita INT,
    fecha_hora DATETIME NOT NULL,
    glucosa DECIMAL(5,2) NOT NULL COMMENT 'mg/dL',
    sintomas TEXT,
    cetonas BOOLEAN,
    tratamiento_aplicado TEXT,
    observaciones TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (id_paciente) REFERENCES pacientes(id_paciente),
    FOREIGN KEY (id_visita) REFERENCES visitas(id_visita),
    INDEX idx_paciente (id_paciente),
    INDEX idx_fecha (fecha_hora)
) ENGINE=InnoDB;

-- ============================================================================
-- MÓDULO 9: SALUD MENTAL Y BIENESTAR
-- ============================================================================

-- Tabla de salud mental
CREATE TABLE salud_mental (
    id_evaluacion INT AUTO_INCREMENT PRIMARY KEY,
    id_visita INT NOT NULL,
    estado_animo ENUM('Bueno', 'Regular', 'Malo'),
    ansiedad BOOLEAN DEFAULT FALSE,
    depresion BOOLEAN DEFAULT FALSE,
    estres_relacionado_enfermedad BOOLEAN DEFAULT FALSE,
    apoyo_familiar BOOLEAN DEFAULT TRUE,
    apoyo_social BOOLEAN DEFAULT TRUE,
    requiere_atencion_psicologica BOOLEAN DEFAULT FALSE,
    observaciones TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (id_visita) REFERENCES visitas(id_visita) ON DELETE CASCADE,
    INDEX idx_visita (id_visita)
) ENGINE=InnoDB;

-- ============================================================================
-- MÓDULO 10: ANEXOS Y ARCHIVOS
-- ============================================================================

-- Tabla de anexos (archivos adjuntos)
CREATE TABLE anexos (
    id_anexo INT AUTO_INCREMENT PRIMARY KEY,
    id_paciente INT NOT NULL,
    id_visita INT,
    tipo_archivo ENUM('Laboratorio', 'Imagen', 'Estudio', 'Receta', 'Documento', 'Otro'),
    nombre_archivo VARCHAR(255) NOT NULL,
    ruta_archivo VARCHAR(500) NOT NULL,
    tamanio_kb INT,
    extension VARCHAR(10),
    descripcion TEXT,
    fecha_subida DATETIME DEFAULT CURRENT_TIMESTAMP,
    subido_por INT,
    FOREIGN KEY (id_paciente) REFERENCES pacientes(id_paciente),
    FOREIGN KEY (id_visita) REFERENCES visitas(id_visita),
    FOREIGN KEY (subido_por) REFERENCES usuarios(id_usuario),
    INDEX idx_paciente (id_paciente),
    INDEX idx_visita (id_visita),
    INDEX idx_tipo (tipo_archivo)
) ENGINE=InnoDB;

-- ============================================================================
-- MÓDULO 11: RANGOS DE REFERENCIA Y CONFIGURACIÓN
-- ============================================================================

-- Tabla de rangos de referencia
CREATE TABLE rangos_referencia (
    id_rango INT AUTO_INCREMENT PRIMARY KEY,
    parametro VARCHAR(100) NOT NULL UNIQUE,
    unidad VARCHAR(20),
    valor_minimo_normal DECIMAL(10,2),
    valor_maximo_normal DECIMAL(10,2),
    valor_minimo_precaucion DECIMAL(10,2),
    valor_maximo_precaucion DECIMAL(10,2),
    descripcion TEXT,
    activo BOOLEAN DEFAULT TRUE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_parametro (parametro)
) ENGINE=InnoDB;

-- Tabla de interpretaciones automáticas
CREATE TABLE interpretaciones (
    id_interpretacion INT AUTO_INCREMENT PRIMARY KEY,
    parametro VARCHAR(100) NOT NULL,
    condicion VARCHAR(50) COMMENT 'menor_que, mayor_que, entre, etc.',
    valor_referencia DECIMAL(10,2),
    valor_referencia_max DECIMAL(10,2),
    nivel_alerta ENUM('Normal', 'Precaución', 'Alerta'),
    mensaje TEXT,
    recomendacion TEXT,
    activo BOOLEAN DEFAULT TRUE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_parametro (parametro)
) ENGINE=InnoDB;

-- ============================================================================
-- TRIGGERS PARA CÁLCULOS AUTOMÁTICOS
-- ============================================================================

-- Trigger para calcular IMC automáticamente
DELIMITER $$
CREATE TRIGGER calcular_imc_before_insert
BEFORE INSERT ON datos_clinicos
FOR EACH ROW
BEGIN
    IF NEW.peso IS NOT NULL AND NEW.talla IS NOT NULL AND NEW.talla > 0 THEN
        SET NEW.imc = NEW.peso / POWER(NEW.talla / 100, 2);
    END IF;
END$$

CREATE TRIGGER calcular_imc_before_update
BEFORE UPDATE ON datos_clinicos
FOR EACH ROW
BEGIN
    IF NEW.peso IS NOT NULL AND NEW.talla IS NOT NULL AND NEW.talla > 0 THEN
        SET NEW.imc = NEW.peso / POWER(NEW.talla / 100, 2);
    END IF;
END$$
DELIMITER ;

-- Trigger para calcular edad del paciente
DELIMITER $$
CREATE TRIGGER calcular_edad_before_insert
BEFORE INSERT ON pacientes
FOR EACH ROW
BEGIN
    IF NEW.fecha_nacimiento IS NOT NULL THEN
        SET NEW.edad = TIMESTAMPDIFF(YEAR, NEW.fecha_nacimiento, CURDATE());
    END IF;
END$$

CREATE TRIGGER calcular_edad_before_update
BEFORE UPDATE ON pacientes
FOR EACH ROW
BEGIN
    IF NEW.fecha_nacimiento IS NOT NULL THEN
        SET NEW.edad = TIMESTAMPDIFF(YEAR, NEW.fecha_nacimiento, CURDATE());
    END IF;
END$$
DELIMITER ;

-- ============================================================================
-- VISTAS ÚTILES
-- ============================================================================

-- Vista de pacientes activos con última visita
CREATE VIEW vista_pacientes_activos AS
SELECT 
    p.id_paciente,
    p.numero_expediente,
    CONCAT(p.nombre, ' ', p.apellido_paterno, ' ', IFNULL(p.apellido_materno, '')) AS nombre_completo,
    p.edad,
    p.sexo,
    p.telefono,
    p.celular,
    MAX(v.fecha_visita) AS ultima_visita,
    COUNT(v.id_visita) AS total_visitas
FROM pacientes p
LEFT JOIN visitas v ON p.id_paciente = v.id_paciente
WHERE p.activo = TRUE
GROUP BY p.id_paciente
ORDER BY p.apellido_paterno, p.nombre;

-- Vista de análisis recientes con interpretación
CREATE VIEW vista_analisis_recientes AS
SELECT 
    p.id_paciente,
    p.numero_expediente,
    CONCAT(p.nombre, ' ', p.apellido_paterno) AS nombre_paciente,
    ag.fecha_analisis,
    ag.glucosa_ayunas,
    ag.hemoglobina_glicosilada,
    ag.interpretacion_glucosa_ayunas,
    ag.interpretacion_hba1c,
    apl.colesterol_total,
    apl.ldl,
    apl.hdl,
    apl.trigliceridos
FROM pacientes p
LEFT JOIN visitas v ON p.id_paciente = v.id_paciente
LEFT JOIN analisis_glucosa ag ON v.id_visita = ag.id_visita
LEFT JOIN analisis_perfil_lipidico apl ON v.id_visita = apl.id_visita
WHERE p.activo = TRUE
ORDER BY ag.fecha_analisis DESC;

-- Vista de tratamientos activos
CREATE VIEW vista_tratamientos_activos AS
SELECT 
    p.id_paciente,
    p.numero_expediente,
    CONCAT(p.nombre, ' ', p.apellido_paterno) AS nombre_paciente,
    mc.nombre_generico,
    mc.nombre_comercial,
    mc.categoria,
    t.dosis,
    t.frecuencia,
    t.fecha_inicio,
    DATEDIFF(CURDATE(), t.fecha_inicio) AS dias_tratamiento
FROM tratamientos t
JOIN pacientes p ON t.id_paciente = p.id_paciente
JOIN medicamentos_catalogo mc ON t.id_medicamento = mc.id_medicamento
WHERE t.activo = TRUE AND p.activo = TRUE
ORDER BY p.apellido_paterno, mc.categoria;

-- ============================================================================
-- FIN DEL SCRIPT
-- ============================================================================
< ? p h p  
 r e q u i r e _ o n c e   ' a p p / c o n f i g / d a t a b a s e . p h p ' ;  
  
 t r y   {  
         $ d a t a b a s e   =   n e w   D a t a b a s e ( ) ;  
         $ d b   =   $ d a t a b a s e - > g e t C o n n e c t i o n ( ) ;  
  
         / /   1 .   T a b l a   e s t u d i o _ s o c i o e c o n o m i c o  
         $ s q l 1   =   " C R E A T E   T A B L E   I F   N O T   E X I S T S   e s t u d i o _ s o c i o e c o n o m i c o   (  
                 i d _ e s t u d i o   I N T   A U T O _ I N C R E M E N T   P R I M A R Y   K E Y ,  
                 i d _ p a c i e n t e   I N T   N O T   N U L L ,  
                 f e c h a _ e s t u d i o   D A T E   D E F A U L T   ( C U R R E N T _ D A T E ) ,  
                  
                 - -   I .   D a t o s   G e n e r a l e s  
                 r e l i g i o n   V A R C H A R ( 1 0 0 ) ,  
                 t i e m p o _ r e s i d e n c i a   V A R C H A R ( 1 0 0 ) ,  
                 e s c o l a r i d a d   V A R C H A R ( 1 0 0 ) ,  
                 e s t a d o _ c i v i l   E N U M ( ' S o l t e r o ' ,   ' C a s a d o ' ,   ' D i v o r c i a d o ' ,   ' V i u d o ' ,   ' U n i o n   L i b r e ' ) ,  
                 o c u p a c i o n   V A R C H A R ( 1 5 0 ) ,  
                  
                 - -   I I .   E s t r u c t u r a   F a m i l i a r  
                 e s _ j e f e _ f a m i l i a   B O O L E A N ,  
                 r e l a c i o n e s _ f a m i l i a r e s   E N U M ( ' A r m � � n i c a s ' ,   ' C o n f l i c t i v a s ' ,   ' A i s l a d a s ' ) ,  
                 a p o y o _ f a m i l i a r   E N U M ( ' M u y   A l t o ' ,   ' M e d i o ' ,   ' B a j o ' ,   ' N u l o ' ) ,  
                  
                 - -   I I I .   V i v i e n d a  
                 t i p o _ v i v i e n d a   E N U M ( ' P r o p i a ' ,   ' R e n t a d a ' ,   ' P r e s t a d a ' ,   ' O t r a ' ) ,  
                 m a t e r i a l _ v i v i e n d a   T E X T ,  
                 n u m _ h a b i t a c i o n e s   I N T ,  
                 s e r v i c i o _ a g u a   B O O L E A N   D E F A U L T   F A L S E ,  
                 s e r v i c i o _ d r e n a j e   B O O L E A N   D E F A U L T   F A L S E ,  
                 s e r v i c i o _ e l e c t r i c i d a d   B O O L E A N   D E F A U L T   F A L S E ,  
                 s e r v i c i o _ g a s   B O O L E A N   D E F A U L T   F A L S E ,  
                 s e r v i c i o _ i n t e r n e t   B O O L E A N   D E F A U L T   F A L S E ,  
                  
                 - -   I V .   E c o n o m � � a  
                 i n g r e s o _ m e n s u a l _ f a m i l i a r   D E C I M A L ( 1 0 , 2 ) ,  
                 g a s t o _ r e n t a   D E C I M A L ( 1 0 , 2 ) ,  
                 g a s t o _ a l i m e n t o s   D E C I M A L ( 1 0 , 2 ) ,  
                 g a s t o _ t r a n s p o r t e   D E C I M A L ( 1 0 , 2 ) ,  
                 g a s t o _ s e r v i c i o s   D E C I M A L ( 1 0 , 2 ) ,  
                 g a s t o _ t r a t a m i e n t o s   D E C I M A L ( 1 0 , 2 ) ,  
                 g a s t o _ t o t a l _ e s t i m a d o   D E C I M A L ( 1 0 , 2 ) ,  
                 a p o y o _ s o c i a l _ c h e c k   B O O L E A N   D E F A U L T   F A L S E ,  
                 a p o y o _ s o c i a l _ n o m b r e   V A R C H A R ( 2 0 0 ) ,  
                 i n g r e s o _ c u b r e _ n e c e s i d a d e s   B O O L E A N   D E F A U L T   F A L S E ,  
                  
                 - -   V .   S a l u d   ( D i a b e t e s   C o n t e x t )  
                 d i a g n o s t i c o _ d e s c   T E X T ,  
                 s e r v i c i o _ m e d i c o   J S O N   C O M M E N T   ' I M S S ,   I S S S T E ,   I N S A B I ,   P r i v a d o ,   N o   c u e n t a ' ,  
                 t r a t a m i e n t o _ a c t u a l   J S O N   C O M M E N T   ' I n s u l i n a ,   M e t f o r m i n a ,   O t r o ' ,  
                 c u b r e _ c o s t o s _ m e d i c a m e n t o   B O O L E A N ,  
                 c u e n t a _ c o n _ g l u c o m e t r o   B O O L E A N ,  
                 d i f i c u l t a d _ d i e t a _ e c o n o m i c a   B O O L E A N ,  
                  
                 - -   V I .   A l i m e n t a c i � � n  
                 f r e c u e n c i a _ a l i m e n t o s   J S O N   C O M M E N T   ' M a t r i z   d e   f r e c u e n c i a   d e   c o n s u m o ' ,  
                  
                 - -   V I I .   C o n c l u s i o n e s  
                 o b s e r v a c i o n e s _ t r a b a j o _ s o c i a l   T E X T ,  
                 n i v e l _ s o c i o e c o n o m i c o   E N U M ( ' A l t o ' ,   ' M e d i o ' ,   ' B a j o ' ,   ' V u l n e r a b i l i d a d   E x t r e m a ' ) ,  
                 p l a n _ i n t e r v e n c i o n   T E X T ,  
                 n o m b r e _ e n t r e v i s t a d o   V A R C H A R ( 2 0 0 ) ,  
                 n o m b r e _ t r a b a j a d o r _ s o c i a l   V A R C H A R ( 2 0 0 ) ,  
                  
                 c r e a t e d _ a t   T I M E S T A M P   D E F A U L T   C U R R E N T _ T I M E S T A M P ,  
                 u p d a t e d _ a t   T I M E S T A M P   D E F A U L T   C U R R E N T _ T I M E S T A M P   O N   U P D A T E   C U R R E N T _ T I M E S T A M P ,  
                  
                 F O R E I G N   K E Y   ( i d _ p a c i e n t e )   R E F E R E N C E S   p a c i e n t e s ( i d _ p a c i e n t e )   O N   D E L E T E   C A S C A D E ,  
                 I N D E X   i d x _ p a c i e n t e   ( i d _ p a c i e n t e )  
         )   E N G I N E = I n n o D B ; " ;  
  
         $ d b - > e x e c ( $ s q l 1 ) ;  
         e c h o   " T a b l a   ' e s t u d i o _ s o c i o e c o n o m i c o '   c r e a d a   o   v e r i f i c a d a   c o r r e c t a m e n t e . \ n " ;  
  
         / /   2 .   T a b l a   e s t u d i o _ s o c i o e c o n o m i c o _ f a m i l i a r e s  
         $ s q l 2   =   " C R E A T E   T A B L E   I F   N O T   E X I S T S   e s t u d i o _ s o c i o e c o n o m i c o _ f a m i l i a r e s   (  
                 i d _ f a m i l i a r _ e s t u d i o   I N T   A U T O _ I N C R E M E N T   P R I M A R Y   K E Y ,  
                 i d _ e s t u d i o   I N T   N O T   N U L L ,  
                 n o m b r e   V A R C H A R ( 2 0 0 )   N O T   N U L L ,  
                 p a r e n t e s c o   V A R C H A R ( 1 0 0 ) ,  
                 e d a d   I N T ,  
                 o c u p a c i o n   V A R C H A R ( 1 5 0 ) ,  
                 i n g r e s o _ m e n s u a l   D E C I M A L ( 1 0 , 2 ) ,  
                  
                 c r e a t e d _ a t   T I M E S T A M P   D E F A U L T   C U R R E N T _ T I M E S T A M P ,  
                  
                 F O R E I G N   K E Y   ( i d _ e s t u d i o )   R E F E R E N C E S   e s t u d i o _ s o c i o e c o n o m i c o ( i d _ e s t u d i o )   O N   D E L E T E   C A S C A D E ,  
                 I N D E X   i d x _ e s t u d i o   ( i d _ e s t u d i o )  
         )   E N G I N E = I n n o D B ; " ;  
  
         $ d b - > e x e c ( $ s q l 2 ) ;  
         e c h o   " T a b l a   ' e s t u d i o _ s o c i o e c o n o m i c o _ f a m i l i a r e s '   c r e a d a   o   v e r i f i c a d a   c o r r e c t a m e n t e . \ n " ;  
  
 }   c a t c h   ( P D O E x c e p t i o n   $ e )   {  
         e c h o   " E r r o r   B D :   "   .   $ e - > g e t M e s s a g e ( ) ;  
 }  
 ? >  
 