-- ============================================================================
-- CIADI - BASE DE DATOS COMBINADA COMPLETA
-- Versión Final Consolidada: Todas las Especialidades Integradas
-- Incluye: Medicina Interna + Nutrición + Psicología + 
--          Actividad Física + Cuidado Pies + Educación Diabetes
-- ============================================================================

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";

-- Configuración para evitar errores de duplicados y llaves foráneas durante la carga
SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0;
SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE='NO_AUTO_VALUE_ON_ZERO';

/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

-- ============================================================================
-- 1. ESTRUCTURA DE ACCESO Y SEGURIDAD
-- ============================================================================

CREATE TABLE IF NOT EXISTS roles (
  id_rol int(11) NOT NULL AUTO_INCREMENT,
  nombre_rol varchar(50) NOT NULL,
  descripcion text DEFAULT NULL,
  created_at timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (id_rol)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT IGNORE INTO roles (id_rol, nombre_rol, descripcion) VALUES
(1, 'Administrador', 'Acceso total al sistema'),
(2, 'Doctor', 'Personal médico con acceso a expedientes'),
(3, 'Nutriólogo', 'Especialista en nutrición clínica'),
(4, 'Psicólogo', 'Especialista en salud mental'),
(5, 'Fisioterapeuta', 'Especialista en actividad física'),
(6, 'Podólogo', 'Especialista en cuidado de pies'),
(7, 'Educador en Diabetes', 'Especialista en educación diabetológica');

CREATE TABLE IF NOT EXISTS usuarios (
  id_usuario int(11) NOT NULL AUTO_INCREMENT,
  id_rol int(11) NOT NULL,
  nombre varchar(100) NOT NULL,
  apellido_paterno varchar(100) NOT NULL,
  apellido_materno varchar(100) DEFAULT NULL,
  email varchar(150) NOT NULL UNIQUE,
  password_hash varchar(255) NOT NULL,
  cedula_profesional varchar(50) DEFAULT NULL,
  especialidad varchar(100) DEFAULT NULL,
  activo tinyint(1) DEFAULT 1,
  created_at timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (id_usuario),
  FOREIGN KEY (id_rol) REFERENCES roles (id_rol)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT IGNORE INTO usuarios (id_usuario, id_rol, nombre, apellido_paterno, apellido_materno, email, password_hash, cedula_profesional, especialidad, activo) VALUES
(1, 1, 'Admin', 'Sistema', NULL, 'admin@clinica.com', '$2y$10$.RkahSTVBwlM8FfqLq/RuOEuIKR/K.5jQz2.bjclpnnqeiT6v/i2S', NULL, 'Administración', 1),
(2, 2, 'Dr. Juan', 'Pérez', 'González', 'doctor@clinica.com', '$2y$10$hWybj/H43l8sBgPRAIfcR.loA/HPIEFHVoYTTh3/kVGVwj8K3GrKW', '12345678', 'Medicina Interna', 1),
(3, 3, 'Lic. María', 'López', 'Hernández', 'nutricion@clinica.com', '$2y$10$hWybj/H43l8sBgPRAIfcR.loA/HPIEFHVoYTTh3/kVGVwj8K3GrKW', '87654321', 'Nutrición Clínica', 1),
(4, 4, 'Psic. Ana', 'Martínez', 'Ruiz', 'psicologia@clinica.com', '$2y$10$hWybj/H43l8sBgPRAIfcR.loA/HPIEFHVoYTTh3/kVGVwj8K3GrKW', '11223344', 'Psicología Clínica', 1),
(5, 5, 'Axel', 'Usuario', 'Ramses', 'axel@clinica.com', '$2y$10$hWybj/H43l8sBgPRAIfcR.loA/HPIEFHVoYTTh3/kVGVwj8K3GrKW', '55667788', 'Actividad Física', 1);

CREATE TABLE IF NOT EXISTS sesiones (
  id_sesion varchar(128) NOT NULL,
  id_usuario int(11) NOT NULL,
  fecha_inicio timestamp NOT NULL DEFAULT current_timestamp(),
  fecha_expiracion timestamp NOT NULL,
  activa tinyint(1) DEFAULT 1,
  PRIMARY KEY (id_sesion),
  FOREIGN KEY (id_usuario) REFERENCES usuarios (id_usuario)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================================================
-- 2. PACIENTES Y VISITAS (CORE RELACIONAL)
-- ============================================================================

CREATE TABLE IF NOT EXISTS pacientes (
  id_paciente int(11) NOT NULL AUTO_INCREMENT,
  numero_expediente varchar(50) NOT NULL UNIQUE,
  nombre varchar(100) NOT NULL,
  apellido_paterno varchar(100) NOT NULL,
  apellido_materno varchar(100) DEFAULT NULL,
  fecha_nacimiento date NOT NULL,
  edad int(11) DEFAULT NULL,
  sexo enum('M','F') NOT NULL,
  telefono varchar(20) DEFAULT NULL,
  email varchar(150) DEFAULT NULL,
  direccion text DEFAULT NULL,
  ciudad varchar(100) DEFAULT NULL,
  estado varchar(100) DEFAULT NULL,
  codigo_postal varchar(10) DEFAULT NULL,
  tipo_sangre varchar(20) DEFAULT NULL,
  alergias text DEFAULT NULL,
  protocolo enum('Diabético','Prediabético') DEFAULT 'Diabético',
  nombre_emergencia varchar(255) DEFAULT NULL,
  telefono_emergencia varchar(50) DEFAULT NULL,
  parentesco_emergencia varchar(100) DEFAULT NULL,
  activo tinyint(1) DEFAULT 1,
  created_at timestamp NOT NULL DEFAULT current_timestamp(),
  updated_at timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  created_by int(11) DEFAULT NULL,
  PRIMARY KEY (id_paciente),
  INDEX idx_expediente (numero_expediente),
  INDEX idx_protocolo (protocolo)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS contactos_emergencia (
  id_contacto int(11) NOT NULL AUTO_INCREMENT,
  id_paciente int(11) NOT NULL,
  nombre varchar(150) NOT NULL,
  parentesco varchar(50) DEFAULT NULL,
  telefono varchar(20) NOT NULL,
  es_principal tinyint(1) DEFAULT 0,
  PRIMARY KEY (id_contacto),
  FOREIGN KEY (id_paciente) REFERENCES pacientes (id_paciente) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS visitas (
  id_visita int(11) NOT NULL AUTO_INCREMENT,
  id_paciente int(11) NOT NULL,
  id_doctor int(11) NOT NULL,
  fecha_visita datetime NOT NULL,
  tipo_visita varchar(50) DEFAULT 'Consulta',
  numero_visita int(11) DEFAULT NULL,
  motivo_consulta text DEFAULT NULL,
  diagnostico text DEFAULT NULL,
  plan_tratamiento text DEFAULT NULL,
  observaciones text DEFAULT NULL,
  proxima_cita date DEFAULT NULL,
  estatus enum('Programada','En Curso','Completada','Cancelada') DEFAULT 'Programada',
  created_at timestamp NOT NULL DEFAULT current_timestamp(),
  updated_at timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  created_by int(11) DEFAULT NULL,
  PRIMARY KEY (id_visita),
  FOREIGN KEY (id_paciente) REFERENCES pacientes (id_paciente),
  FOREIGN KEY (id_doctor) REFERENCES usuarios (id_usuario),
  INDEX idx_visita_paciente (id_paciente),
  INDEX idx_visita_fecha (fecha_visita)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================================================
-- 3. ESPECIALIDADES (ACTIVIDAD FÍSICA, CUIDADO PIES, EDUCACIÓN DIABETES)
-- ============================================================================

-- 3.1 ACTIVIDAD FÍSICA
CREATE TABLE IF NOT EXISTS actividad_fisica (
    id_actividad_fisica int(11) NOT NULL AUTO_INCREMENT,
    id_visita int(11) NOT NULL,
    sarc_fuerza tinyint(4) DEFAULT NULL,
    sarc_asistencia_caminar tinyint(4) DEFAULT NULL,
    sarc_levantarse_silla tinyint(4) DEFAULT NULL,
    sarc_subir_escaleras tinyint(4) DEFAULT NULL,
    sarc_caidas tinyint(4) DEFAULT NULL,
    sarc_puntuacion_total tinyint(4) DEFAULT NULL,
    sarc_riesgo enum('Baja','Alta') DEFAULT 'Baja',
    dina_mano_der decimal(5,2) DEFAULT NULL,
    dina_mano_izq decimal(5,2) DEFAULT NULL,
    dina_percentil_resultado varchar(20) DEFAULT NULL,
    daniels_ms_der tinyint(4) DEFAULT NULL,
    daniels_ms_izq tinyint(4) DEFAULT NULL,
    daniels_mi_der tinyint(4) DEFAULT NULL,
    daniels_mi_izq tinyint(4) DEFAULT NULL,
    sts_30seg_reps tinyint(4) DEFAULT NULL,
    sts_5rep_seg decimal(5,2) DEFAULT NULL,
    sts_5rep_alerta tinyint(1) DEFAULT 0,
    eva_zona varchar(150) DEFAULT NULL,
    eva_puntaje tinyint(4) DEFAULT NULL,
    mov_ms_der tinyint(4) DEFAULT NULL,
    mov_ms_izq tinyint(4) DEFAULT NULL,
    mov_mi_der tinyint(4) DEFAULT NULL,
    mov_mi_izq tinyint(4) DEFAULT NULL,
    act_realiza_ejercicio tinyint(1) DEFAULT 0,
    act_frecuencia enum('0','1-2','3-4','5+') DEFAULT '0',
    act_tipo enum('Aerobico','Fuerza','Combinado','Otro') DEFAULT NULL,
    act_tipo_otro varchar(150) DEFAULT NULL,
    act_duracion enum('0-30','30-60','60+') DEFAULT NULL,
    act_dias_descanso enum('1-2','2-3','3+') DEFAULT NULL,
    observaciones_especialista text DEFAULT NULL,
    created_at timestamp NOT NULL DEFAULT current_timestamp(),
    updated_at timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
    created_by int(11) DEFAULT NULL,
    PRIMARY KEY (id_actividad_fisica),
    FOREIGN KEY (id_visita) REFERENCES visitas (id_visita) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 3.2 CUIDADO DE PIES
CREATE TABLE IF NOT EXISTS cuidado_pies (
    id_cuidado_pies int(11) NOT NULL AUTO_INCREMENT,
    id_visita int(11) NOT NULL,
    ulcera_previa tinyint(1) DEFAULT 0,
    amputacion_previa tinyint(1) DEFAULT 0,
    cirugia_previa tinyint(1) DEFAULT 0,
    herida_lenta tinyint(1) DEFAULT 0,
    ardor_hormigueo tinyint(1) DEFAULT 0,
    dolor_actividad tinyint(1) DEFAULT 0,
    dolor_reposo tinyint(1) DEFAULT 0,
    perdida_sensacion tinyint(1) DEFAULT 0,
    fuma tinyint(1) DEFAULT 0,
    cigarrillos_dia tinyint(4) DEFAULT NULL,
    revision_pies_previa tinyint(1) DEFAULT 0,
    hiper_plantar_der tinyint(4) DEFAULT 0,
    hiper_plantar_izq tinyint(4) DEFAULT 0,
    hiper_dorsal_der tinyint(4) DEFAULT 0,
    hiper_dorsal_izq tinyint(4) DEFAULT 0,
    hiper_talar_der tinyint(4) DEFAULT 0,
    hiper_talar_izq tinyint(4) DEFAULT 0,
    onicocriptosis_der tinyint(4) DEFAULT 0,
    onicocriptosis_izq tinyint(4) DEFAULT 0,
    onicomicosis_der tinyint(4) DEFAULT 0,
    onicomicosis_izq tinyint(4) DEFAULT 0,
    onicogrifosis_der tinyint(4) DEFAULT 0,
    onicogrifosis_izq tinyint(4) DEFAULT 0,
    bullosis_der tinyint(4) DEFAULT 0,
    bullosis_izq tinyint(4) DEFAULT 0,
    necrosis_der tinyint(4) DEFAULT 0,
    necrosis_izq tinyint(4) DEFAULT 0,
    grietas_fisuras_der tinyint(4) DEFAULT 0,
    grietas_fisuras_izq tinyint(4) DEFAULT 0,
    lesion_superficial_der tinyint(4) DEFAULT 0,
    lesion_superficial_izq tinyint(4) DEFAULT 0,
    anhidrosis_der tinyint(4) DEFAULT 0,
    anhidrosis_izq tinyint(4) DEFAULT 0,
    tina_der tinyint(4) DEFAULT 0,
    tina_izq tinyint(4) DEFAULT 0,
    proceso_infeccioso_der tinyint(4) DEFAULT 0,
    proceso_infeccioso_izq tinyint(4) DEFAULT 0,
    ulcera_venosa_der tinyint(4) DEFAULT 0,
    ulcera_venosa_izq tinyint(4) DEFAULT 0,
    ulcera_arterial_der tinyint(4) DEFAULT 0,
    ulcera_arterial_izq tinyint(4) DEFAULT 0,
    ulcera_mixta_der tinyint(4) DEFAULT 0,
    ulcera_mixta_izq tinyint(4) DEFAULT 0,
    ulcera_otra_der tinyint(4) DEFAULT 0,
    ulcera_otra_izq tinyint(4) DEFAULT 0,
    hallux_valgus_der tinyint(4) DEFAULT 0,
    hallux_valgus_izq tinyint(4) DEFAULT 0,
    dedos_garra_der tinyint(4) DEFAULT 0,
    dedos_garra_izq tinyint(4) DEFAULT 0,
    dedos_martillo_der tinyint(4) DEFAULT 0,
    dedos_martillo_izq tinyint(4) DEFAULT 0,
    infraducto_der tinyint(4) DEFAULT 0,
    infraducto_izq tinyint(4) DEFAULT 0,
    supraducto_der tinyint(4) DEFAULT 0,
    supraducto_izq tinyint(4) DEFAULT 0,
    pie_cavo_der tinyint(4) DEFAULT 0,
    pie_cavo_izq tinyint(4) DEFAULT 0,
    arco_caido_der tinyint(4) DEFAULT 0,
    arco_caido_izq tinyint(4) DEFAULT 0,
    talo_varo_der tinyint(4) DEFAULT 0,
    talo_varo_izq tinyint(4) DEFAULT 0,
    espolon_calcaneo_der tinyint(4) DEFAULT 0,
    espolon_calcaneo_izq tinyint(4) DEFAULT 0,
    hipercargas_metatarsianos_der tinyint(4) DEFAULT 0,
    hipercargas_metatarsianos_izq tinyint(4) DEFAULT 0,
    pie_charcot_der tinyint(4) DEFAULT 0,
    pie_charcot_izq tinyint(4) DEFAULT 0,
    pulso_pedio_der enum('Presente','Disminuido','Ausente') DEFAULT 'Presente',
    pulso_pedio_izq enum('Presente','Disminuido','Ausente') DEFAULT 'Presente',
    pulso_tibial_der enum('Presente','Disminuido','Ausente') DEFAULT 'Presente',
    pulso_tibial_izq enum('Presente','Disminuido','Ausente') DEFAULT 'Presente',
    llenado_capilar_der decimal(4,1) DEFAULT NULL,
    llenado_capilar_izq decimal(4,1) DEFAULT NULL,
    varices tinyint(1) DEFAULT 0,
    edema_godet enum('Sin edema','Grado I','Grado II','Grado III','Grado IV') DEFAULT 'Sin edema',
    monofilamento_puntos tinyint(4) DEFAULT NULL,
    sensibilidad_vibratoria_seg decimal(4,1) DEFAULT NULL,
    reflejo_rotuliano tinyint(4) DEFAULT 0,
    dorsiflexion_pie tinyint(4) DEFAULT 0,
    apertura_ortejos tinyint(4) DEFAULT 0,
    educacion_cuidado_pies tinyint(1) DEFAULT 0,
    puntuacion_total_der int(11) DEFAULT 0,
    puntuacion_total_izq int(11) DEFAULT 0,
    riesgo_der enum('Leve','Moderado','Alto') DEFAULT 'Leve',
    riesgo_izq enum('Leve','Moderado','Alto') DEFAULT 'Leve',
    alerta_roja tinyint(1) DEFAULT 0,
    observaciones_especialista text DEFAULT NULL,
    created_at timestamp NOT NULL DEFAULT current_timestamp(),
    updated_at timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
    created_by int(11) DEFAULT NULL,
    PRIMARY KEY (id_cuidado_pies),
    FOREIGN KEY (id_visita) REFERENCES visitas (id_visita) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 3.3 EDUCACIÓN EN DIABETES
CREATE TABLE IF NOT EXISTS educacion_diabetes (
    id_educacion_diabetes int(11) NOT NULL AUTO_INCREMENT,
    id_visita int(11) NOT NULL,
    conocimientos_deficientes_nutricion tinyint(1) DEFAULT 0,
    no_cumple_recomendaciones tinyint(1) DEFAULT 0,
    ingesta_excesiva_carbohidratos tinyint(1) DEFAULT 0,
    manejo_inadecuado_hipoglucemia tinyint(1) DEFAULT 0,
    barrera_nivel_educativo tinyint(1) DEFAULT 0,
    barrera_economica tinyint(1) DEFAULT 0,
    barrera_apoyo_familiar tinyint(1) DEFAULT 0,
    barrera_psicologica tinyint(1) DEFAULT 0,
    otras_barreras text DEFAULT NULL,
    tecnica_seleccion_jeringa enum('No','En Proceso','Sí') DEFAULT 'No',
    tecnica_angulacion_pliegue enum('No','En Proceso','Sí') DEFAULT 'No',
    tecnica_almacenamiento_insulina enum('No','En Proceso','Sí') DEFAULT 'No',
    rotacion_sitios_abdomen enum('No','En Proceso','Sí') DEFAULT 'No',
    rotacion_sitios_muslos enum('No','En Proceso','Sí') DEFAULT 'No',
    rotacion_sitios_brazos enum('No','En Proceso','Sí') DEFAULT 'No',
    deteccion_lipodistrofias enum('No','En Proceso','Sí') DEFAULT 'No',
    uso_glucometro enum('No','En Proceso','Sí') DEFAULT 'No',
    uso_lancetero enum('No','En Proceso','Sí') DEFAULT 'No',
    registro_bitacora enum('No','En Proceso','Sí') DEFAULT 'No',
    frecuencia_medicion_adecuada enum('No','En Proceso','Sí') DEFAULT 'No',
    interpretacion_resultados enum('No','En Proceso','Sí') DEFAULT 'No',
    conoce_mecanismo_accion tinyint(1) DEFAULT 0,
    identifica_efectos_secundarios tinyint(1) DEFAULT 0,
    olvido_dosis_frec_oral enum('Nunca','1 vez/semana','Más de 3 veces/semana') DEFAULT 'Nunca',
    adherencia_oral_metformina tinyint(1) DEFAULT 1,
    identificacion_sintomas_hipo enum('No','En Proceso','Sí') DEFAULT 'No',
    aplicacion_regla_15 enum('No','En Proceso','Sí') DEFAULT 'No',
    identificacion_sintomas_hiper enum('No','En Proceso','Sí') DEFAULT 'No',
    cuando_medir_cetonas enum('No','En Proceso','Sí') DEFAULT 'No',
    sabe_manejar_dias_enfermedad tinyint(1) DEFAULT 0,
    plan_accion_crisis tinyint(1) DEFAULT 0,
    conteo_carbohidratos_nivel enum('Nulo','Básico','Avanzado') DEFAULT 'Nulo',
    lectura_etiquetas enum('No','En Proceso','Sí') DEFAULT 'No',
    calculo_porciones enum('No','En Proceso','Sí') DEFAULT 'No',
    conoce_uso_suplementos tinyint(1) DEFAULT 0,
    suplemento_vit_d tinyint(1) DEFAULT 0,
    suplemento_omega_3 tinyint(1) DEFAULT 0,
    suplemento_creatina tinyint(1) DEFAULT 0,
    suplemento_proteina_suero tinyint(1) DEFAULT 0,
    evita_refrescos tinyint(1) DEFAULT 0,
    evita_pan_dulce tinyint(1) DEFAULT 0,
    evita_jugos tinyint(1) DEFAULT 0,
    evita_mermeladas tinyint(1) DEFAULT 0,
    evita_ultraprocesados tinyint(1) DEFAULT 0,
    meta_hba1c_objetivo decimal(4,2) DEFAULT 7.0,
    meta_glucosa_ayunas_max int(11) DEFAULT 130,
    meta_reduccion_peso tinyint(1) DEFAULT 0,
    meta_ejercicio_regular tinyint(1) DEFAULT 0,
    meta_adherencia_alimentacion tinyint(1) DEFAULT 0,
    metas_cumplidas_anteriores text DEFAULT NULL,
    nuevas_metas_establecidas text DEFAULT NULL,
    peso_actual decimal(5,2) DEFAULT NULL,
    talla_actual decimal(5,2) DEFAULT NULL,
    imc_actual decimal(5,2) DEFAULT NULL,
    circunferencia_cintura decimal(5,2) DEFAULT NULL,
    porcentaje_grasa decimal(5,2) DEFAULT NULL,
    masa_muscular_kg decimal(5,2) DEFAULT NULL,
    recordatorio_24h_resumen text DEFAULT NULL,
    freq_agua_litros enum('< 1 litro','1-2 litros','2-3 litros','3+ litros') DEFAULT '< 1 litro',
    freq_frutas_verduras enum('0-2 porciones','3-5 porciones','5+ porciones') DEFAULT '0-2 porciones',
    semaforo_educativo enum('Rojo','Amarillo','Verde') DEFAULT 'Rojo',
    nivel_autonomia enum('Dependiente','Semi-autónomo','Autónomo') DEFAULT 'Dependiente',
    observaciones_educador text DEFAULT NULL,
    material_educativo_entregado text DEFAULT NULL,
    created_at timestamp NOT NULL DEFAULT current_timestamp(),
    updated_at timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
    created_by int(11) DEFAULT NULL,
    PRIMARY KEY (id_educacion_diabetes),
    FOREIGN KEY (id_visita) REFERENCES visitas (id_visita) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================================================
-- 4. MEDICINA INTERNA, NUTRICIÓN Y PSICOLOGÍA
-- ============================================================================

CREATE TABLE IF NOT EXISTS consulta_medicina_interna (
  id_medicina_interna int(11) NOT NULL AUTO_INCREMENT,
  id_paciente int(11) NOT NULL,
  id_visita int(11) DEFAULT NULL,
  fecha_registro date NOT NULL,
  
  -- 1. Diagnóstico de Diabetes
  tipo_diabetes enum('Tipo 1','Tipo 2','MODY','Gestacional','Secundaria') DEFAULT 'Tipo 2',
  anio_diagnostico int(4) DEFAULT NULL,
  ultima_hba1c decimal(4,2) DEFAULT NULL,
  control_actual enum('Excelente','Bueno','Regular','Malo') DEFAULT 'Regular',
  
  -- 2. Enfermedades Cardiovasculares (Checklist)
  hta tinyint(1) DEFAULT 0,
  enfermedad_coronaria tinyint(1) DEFAULT 0,
  infarto_miocardio tinyint(1) DEFAULT 0,
  insuficiencia_cardiaca tinyint(1) DEFAULT 0,
  dislipidemia tinyint(1) DEFAULT 0,
  enf_vascular_periferica tinyint(1) DEFAULT 0,
  
  -- 3. Complicaciones Microvasculares (Checklist)
  retinopatia_diabetica tinyint(1) DEFAULT 0,
  nefropatia_diabetica tinyint(1) DEFAULT 0,
  neuropatia_periferica tinyint(1) DEFAULT 0,
  neuropatia_autonomica tinyint(1) DEFAULT 0,
  
  -- 4. Enfermedades Infecciosas (Checklist)
  infecciones_urinarias tinyint(1) DEFAULT 0,
  pie_diabetico tinyint(1) DEFAULT 0,
  infecciones_piel tinyint(1) DEFAULT 0,
  tuberculosis tinyint(1) DEFAULT 0,
  hepatitis_b_c tinyint(1) DEFAULT 0,
  
  -- 5. Endocrino–Metabólicas Asociadas
  obesidad tinyint(1) DEFAULT 0,
  enfermedad_tiroidea tinyint(1) DEFAULT 0,
  sindrome_metabolico tinyint(1) DEFAULT 0,
  
  -- 6. Renales y Genitourinarias
  insuficiencia_renal_cronica tinyint(1) DEFAULT 0,
  proteinuria tinyint(1) DEFAULT 0,
  nefrolitiasis tinyint(1) DEFAULT 0,
  
  -- 7. Gastrointestinales
  higado_graso tinyint(1) DEFAULT 0,
  pancreatitis tinyint(1) DEFAULT 0,
  gastroparesia tinyint(1) DEFAULT 0,
  
  -- 8. Neurológicas
  evc tinyint(1) DEFAULT 0,
  neuropatia_periferica_previa tinyint(1) DEFAULT 0,
  amputaciones tinyint(1) DEFAULT 0,
  
  -- 9. Salud Mental
  depresion tinyint(1) DEFAULT 0,
  ansiedad tinyint(1) DEFAULT 0,
  trastornos_sueno tinyint(1) DEFAULT 0,
  
  -- Antecedentes Relevantes
  alergias_check tinyint(1) DEFAULT 0,
  detalle_alergias text DEFAULT NULL,
  enfermedades_cronicas_check tinyint(1) DEFAULT 0,
  detalle_enfermedades_cronicas text DEFAULT NULL,
  cirugias_previas_check tinyint(1) DEFAULT 0,
  detalle_cirugias_previas text DEFAULT NULL,
  hospitalizaciones_previas_check tinyint(1) DEFAULT 0,
  detalle_hospitalizaciones_previas text DEFAULT NULL,
  
  -- 10. Medicación Detallada (Antidiabéticos Orales)
  med_metformina tinyint(1) DEFAULT 0,
  med_sulfonilureas_glibenclamida tinyint(1) DEFAULT 0,
  med_sulfonilureas_glimepirida tinyint(1) DEFAULT 0,
  med_sulfonilureas_gliclazida tinyint(1) DEFAULT 0,
  med_meglitinidas_repaglinida tinyint(1) DEFAULT 0,
  med_meglitinidas_nateglinida tinyint(1) DEFAULT 0,
  med_inhibidores_alfaglucosidasa_acarbosa tinyint(1) DEFAULT 0,
  med_inhibidores_alfaglucosidasa_miglitol tinyint(1) DEFAULT 0,
  med_tiazolidinedionas_pioglitazona tinyint(1) DEFAULT 0,
  med_tiazolidinedionas_rosiglitazona tinyint(1) DEFAULT 0,
  med_inhibidores_dpp4_sitagliptina tinyint(1) DEFAULT 0,
  med_inhibidores_dpp4_saxaglipina tinyint(1) DEFAULT 0,
  med_inhibidores_dpp4_linagliptina tinyint(1) DEFAULT 0,
  med_inhibidores_dpp4_alogliptina tinyint(1) DEFAULT 0,
  med_agonistas_glp1_exenatida tinyint(1) DEFAULT 0,
  med_agonistas_glp1_liraglutida tinyint(1) DEFAULT 0,
  med_agonistas_glp1_dulaglutida tinyint(1) DEFAULT 0,
  med_agonistas_glp1_lixisenatida tinyint(1) DEFAULT 0,
  med_agonistas_glp1_semaglutida tinyint(1) DEFAULT 0,
  med_inhibidores_sglt2_empagliflozina tinyint(1) DEFAULT 0,
  med_inhibidores_sglt2_dapagliflozina tinyint(1) DEFAULT 0,
  med_inhibidores_sglt2_canagliflozina tinyint(1) DEFAULT 0,
  med_inhibidores_sglt2_ertugliflozina tinyint(1) DEFAULT 0,
  
  -- Insulinas
  ins_rapida_regular tinyint(1) DEFAULT 0,
  ins_ultrarrapida_lispro tinyint(1) DEFAULT 0,
  ins_ultrarrapida_aspart tinyint(1) DEFAULT 0,
  ins_ultrarrapida_glulisina tinyint(1) DEFAULT 0,
  ins_intermedia_nph tinyint(1) DEFAULT 0,
  ins_prolongada_glargina tinyint(1) DEFAULT 0,
  ins_prolongada_detemir tinyint(1) DEFAULT 0,
  ins_prolongada_degludec tinyint(1) DEFAULT 0,
  ins_ultralarga_degludec tinyint(1) DEFAULT 0,
  ins_ultralarga_glargina_u300 tinyint(1) DEFAULT 0,
  ins_mezcla_nph_regular tinyint(1) DEFAULT 0,
  ins_mezcla_lispro tinyint(1) DEFAULT 0,
  ins_mezcla_aspart tinyint(1) DEFAULT 0,
  
  -- Otros Tratamientos
  med_estatinas tinyint(1) DEFAULT 0,
  med_antihipertensivos tinyint(1) DEFAULT 0,
  med_antiagregantes tinyint(1) DEFAULT 0,
  detalles_medicacion text DEFAULT NULL,
  
  -- 11. Laboratorios (Checklist/Valores en Medicina Interna)
  lab_glucosa_ayunas tinyint(1) DEFAULT 0,
  lab_glucosa_postprandial tinyint(1) DEFAULT 0,
  lab_hba1c tinyint(1) DEFAULT 0,
  lab_curva_tolerancia tinyint(1) DEFAULT 0,
  lab_creatinina_serica tinyint(1) DEFAULT 0,
  lab_tfg tinyint(1) DEFAULT 0,
  lab_urea_bun tinyint(1) DEFAULT 0,
  lab_microalbuminuria_orina tinyint(1) DEFAULT 0,
  lab_relacion_acr tinyint(1) DEFAULT 0,
  lab_ego tinyint(1) DEFAULT 0,
  lab_colesterol_total tinyint(1) DEFAULT 0,
  lab_ldl tinyint(1) DEFAULT 0,
  lab_hdl tinyint(1) DEFAULT 0,
  lab_trigliceridos tinyint(1) DEFAULT 0,
  lab_sodio tinyint(1) DEFAULT 0,
  lab_potasio tinyint(1) DEFAULT 0,
  lab_cloro tinyint(1) DEFAULT 0,
  lab_bicarbonato tinyint(1) DEFAULT 0,
  lab_calcio tinyint(1) DEFAULT 0,
  lab_fosforo tinyint(1) DEFAULT 0,
  lab_magnesio tinyint(1) DEFAULT 0,
  lab_gasometria tinyint(1) DEFAULT 0,
  lab_alt tinyint(1) DEFAULT 0,
  lab_ast tinyint(1) DEFAULT 0,
  lab_fosfatasa_alcalina tinyint(1) DEFAULT 0,
  lab_bilirrubinas tinyint(1) DEFAULT 0,
  lab_albumina_serica tinyint(1) DEFAULT 0,
  lab_cetonas tinyint(1) DEFAULT 0,
  lab_peptido_c tinyint(1) DEFAULT 0,
  lab_insulinemia tinyint(1) DEFAULT 0,
  lab_pcr tinyint(1) DEFAULT 0,
  lab_vsg tinyint(1) DEFAULT 0,
  lab_troponina tinyint(1) DEFAULT 0,
  lab_bnp tinyint(1) DEFAULT 0,
  lab_homocisteina tinyint(1) DEFAULT 0,
  lab_vitamina_d tinyint(1) DEFAULT 0,
  lab_hormonas_tiroideas tinyint(1) DEFAULT 0,
  lab_hemograma tinyint(1) DEFAULT 0,
  
  -- 12. Signos Vitales y Antropometría
  peso decimal(5,2) DEFAULT NULL,
  talla int(11) DEFAULT NULL,
  imc decimal(5,2) DEFAULT NULL,
  circunferencia_abdominal decimal(5,2) DEFAULT NULL,
  presion_arterial varchar(20) DEFAULT NULL,
  frecuencia_cardiaca int(11) DEFAULT NULL,
  temperatura decimal(4,1) DEFAULT NULL,
  frecuencia_respiratoria int(11) DEFAULT NULL,
  glucosa_capilar int(11) DEFAULT NULL,
  
  -- 13. Control y Seguimiento
  control_hba1c_reciente_valor decimal(4,2) DEFAULT NULL,
  control_bitacora tinyint(1) DEFAULT 0,
  control_hipoglucemias tinyint(1) DEFAULT 0,
  control_hipoglucemias_detalles text DEFAULT NULL,
  control_hiperglucemias_sintomaticas tinyint(1) DEFAULT 0,
  control_adherencia tinyint(1) DEFAULT 0,
  control_problemas_medicamentos text DEFAULT NULL,
  control_tecnica_insulina tinyint(1) DEFAULT 0,
  control_glucemia_hb1ac_reciente tinyint(1) DEFAULT 0,
  control_glucemia_glucometrias_diarias tinyint(1) DEFAULT 0,
  control_glucemia_hipoglucemias_recientes tinyint(1) DEFAULT 0,
  control_glucemia_hiperglucemias_sintomaticas tinyint(1) DEFAULT 0,
  control_glucemia_cambios_medicamentos tinyint(1) DEFAULT 0,
  control_glucemia_aplicacion_insulina_adecuada tinyint(1) DEFAULT 0,
  
  -- 14. Revisiones y Plan
  rev_pies_detalles text DEFAULT NULL,
  rev_neuropatia_monofilamento tinyint(1) DEFAULT 0,
  rev_renal_laboratorios tinyint(1) DEFAULT 0,
  rev_vision_borrosa tinyint(1) DEFAULT 0,
  rev_macro_coronaria tinyint(1) DEFAULT 0,
  rev_macro_claudicacion tinyint(1) DEFAULT 0,
  rev_riesgo_cv tinyint(1) DEFAULT 0,
  med_revision_completa tinyint(1) DEFAULT 0,
  med_ajuste_orales tinyint(1) DEFAULT 0,
  med_ajuste_insulina tinyint(1) DEFAULT 0,
  med_ajuste_estatina_hta tinyint(1) DEFAULT 0,
  med_evaluar_cambio tinyint(1) DEFAULT 0,
  prog_estudios_pendientes text DEFAULT NULL,
  
  -- 15. Estilo de Vida y Educación
  alimentacion_adecuada tinyint(1) DEFAULT 0,
  actividad_fisica tinyint(1) DEFAULT 0,
  consumo_alcohol tinyint(1) DEFAULT 0,
  tabaquismo tinyint(1) DEFAULT 0,
  horarios_comida_regulares tinyint(1) DEFAULT 0,
  educacion_diabetologica tinyint(1) DEFAULT 0,
  tecnica_insulina tinyint(1) DEFAULT 0,
  revision_sitio_inyeccion tinyint(1) DEFAULT 0,
  prevencion_hipoglucemia tinyint(1) DEFAULT 0,
  cuidado_pies tinyint(1) DEFAULT 0,
  revision_metas tinyint(1) DEFAULT 0,
  
  -- 16. Salud Mental
  sintomas_ansiedad_depresion tinyint(1) DEFAULT 0,
  estres_enfermedad tinyint(1) DEFAULT 0,
  apoyo_familiar_social tinyint(1) DEFAULT 0,
  estado_animo enum('Bueno','Regular','Malo') DEFAULT 'Regular',
  
  observaciones_adicionales text DEFAULT NULL,
  created_at timestamp NOT NULL DEFAULT current_timestamp(),
  created_by int(11) DEFAULT NULL,
  PRIMARY KEY (id_medicina_interna),
  FOREIGN KEY (id_paciente) REFERENCES pacientes (id_paciente),
  FOREIGN KEY (id_visita) REFERENCES visitas (id_visita)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS consulta_nutricion (
  id_nutricion int(11) NOT NULL AUTO_INCREMENT,
  id_visita int(11) NOT NULL,
  id_paciente int(11) NOT NULL,
  fecha_registro date DEFAULT NULL,
  
  -- Antropometría
  peso decimal(5,2) DEFAULT NULL,
  talla decimal(5,2) DEFAULT NULL,
  imc decimal(5,2) DEFAULT NULL,
  circunferencia_cintura decimal(5,2) DEFAULT NULL,
  circunferencia_cadera decimal(5,2) DEFAULT NULL,
  porcentaje_grasa decimal(5,2) DEFAULT NULL,
  kilos_grasa decimal(5,2) DEFAULT NULL,
  masa_muscular decimal(5,2) DEFAULT NULL,
  indice_masa_muscular decimal(5,2) DEFAULT NULL,
  kilos_masa_muscular decimal(5,2) DEFAULT NULL,
  
  -- Clínica
  diagnosticos_medicos text DEFAULT NULL, -- JSON
  diagnostico_especificar text DEFAULT NULL,
  sintomas text DEFAULT NULL, -- JSON
  temperatura decimal(4,2) DEFAULT NULL,
  presion_arterial varchar(50) DEFAULT NULL,
  
  -- Dietética
  frecuencia_consumo text DEFAULT NULL, -- JSON
  alergias_alimentarias enum('Si','No') DEFAULT 'No',
  alergias_alimentarias_cual text DEFAULT NULL,
  recordatorio_24h text DEFAULT NULL, -- JSON
  
  -- Medicamentos
  toma_medicamentos enum('Si','No') DEFAULT 'No',
  toma_suplementos enum('Si','No') DEFAULT 'No',
  suplementos_detalle text DEFAULT NULL, -- JSON
  suplementos_otro text DEFAULT NULL,
  
  -- Estilo de Vida
  realiza_ejercicio enum('Si','No') DEFAULT 'No',
  ejercicio_frecuencia varchar(100) DEFAULT NULL,
  ejercicio_tipo varchar(100) DEFAULT NULL,
  ejercicio_duracion varchar(100) DEFAULT NULL,
  dias_descanso varchar(100) DEFAULT NULL,
  tabaquismo enum('Si','No') DEFAULT 'No',
  alcoholismo enum('Si','No') DEFAULT 'No',
  duerme_bien enum('Si','No') DEFAULT 'No',
  maneja_estres enum('Si','No') DEFAULT 'No',
  horas_sueno decimal(4,2) DEFAULT NULL,
  
  -- Resultados y Recomendaciones
  diagnostico_nutricional text DEFAULT NULL, -- JSON
  diagnostico_nutricional_otro text DEFAULT NULL,
  tipo_dieta varchar(100) DEFAULT NULL,
  calorias_recomendadas int(11) DEFAULT NULL,
  objetivos_tratamiento text DEFAULT NULL, -- JSON
  objetivos_otro text DEFAULT NULL,
  recomendaciones_generales text DEFAULT NULL, -- JSON
  recomendaciones_otros text DEFAULT NULL,
  
  observaciones text DEFAULT NULL,
  created_at timestamp NOT NULL DEFAULT current_timestamp(),
  created_by int(11) DEFAULT NULL,
  PRIMARY KEY (id_nutricion),
  FOREIGN KEY (id_visita) REFERENCES visitas (id_visita) ON DELETE CASCADE,
  FOREIGN KEY (id_paciente) REFERENCES pacientes (id_paciente)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS historia_nutricional (
  id_historia int(11) NOT NULL AUTO_INCREMENT,
  id_paciente int(11) NOT NULL,
  id_visita int(11) DEFAULT NULL,
  habitos_alimentarios text DEFAULT NULL,
  intolerancias_alergias text DEFAULT NULL,
  suplementos text DEFAULT NULL,
  actividad_fisica_nivel enum('Sedentario','Ligero','Moderado','Intenso') DEFAULT 'Sedentario',
  objetivos_nutricionales text DEFAULT NULL,
  created_at timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (id_historia),
  FOREIGN KEY (id_paciente) REFERENCES pacientes (id_paciente),
  FOREIGN KEY (id_visita) REFERENCES visitas (id_visita)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS consulta_psicologia (
  id_psicologia int(11) NOT NULL AUTO_INCREMENT,
  id_paciente int(11) NOT NULL,
  id_visita int(11) DEFAULT NULL,
  fecha_registro date DEFAULT NULL,
  numero_visita int(11) DEFAULT 1,
  descripcion_paciente text DEFAULT NULL,

  -- Visita 1 (Evaluación Inicial)
  v1_ansiedad_beck int(11) DEFAULT NULL,
  v1_depresion_beck int(11) DEFAULT NULL,
  v1_desesperanza_beck int(11) DEFAULT NULL,
  v1_observaciones text DEFAULT NULL,

  -- Visita 2 (Niveles de Adaptación)
  v2_nivel_personal varchar(100) DEFAULT NULL,
  v2_nivel_economico varchar(100) DEFAULT NULL,
  v2_nivel_social varchar(100) DEFAULT NULL,
  v2_nivel_sanitario varchar(100) DEFAULT NULL,
  v2_observaciones text DEFAULT NULL,

  -- Visita 3 (Etapas de Cambio)
  v3_pre_contemplacion varchar(100) DEFAULT NULL,
  v3_contemplacion varchar(100) DEFAULT NULL,
  v3_decision varchar(100) DEFAULT NULL,
  v3_accion varchar(100) DEFAULT NULL,
  v3_mantenimiento varchar(100) DEFAULT NULL,
  v3_recaida varchar(100) DEFAULT NULL,
  v3_observaciones text DEFAULT NULL,

  -- Visita 4 (Relajación)
  v4_logro_relajacion varchar(100) DEFAULT NULL,
  v4_descripcion_paciente text DEFAULT NULL,
  v4_observaciones text DEFAULT NULL,

  -- Visita 5 (Estado de Ánimo)
  v5_tristeza varchar(100) DEFAULT NULL,
  v5_depresion varchar(100) DEFAULT NULL,
  v5_observaciones text DEFAULT NULL,

  -- Campos heredados del esquema anterior (compatibilidad)
  motivo_consulta_psicologica text DEFAULT NULL,
  estado_emocional enum('Estable','Ansioso','Deprimido','Irritable','Confundido') DEFAULT 'Estable',
  test_beck_depresion int(11) DEFAULT NULL,
  test_ansiedad_hamilton int(11) DEFAULT NULL,
  calidad_vida_escala int(11) DEFAULT NULL,
  adherencia_psicologica enum('Nula','Baja','Media','Alta') DEFAULT 'Media',
  recomendaciones_psicologicas text DEFAULT NULL,

  created_at timestamp NOT NULL DEFAULT current_timestamp(),
  created_by int(11) DEFAULT NULL,
  PRIMARY KEY (id_psicologia),
  FOREIGN KEY (id_paciente) REFERENCES pacientes (id_paciente),
  FOREIGN KEY (id_visita) REFERENCES visitas (id_visita)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================================================
-- 5. LABORATORIOS Y ANÁLISIS
-- ============================================================================

CREATE TABLE IF NOT EXISTS analisis_glucosa (
  id_analisis int(11) NOT NULL AUTO_INCREMENT,
  id_visita int(11) NOT NULL,
  fecha_analisis date NOT NULL,
  glucosa_ayunas decimal(5,2) DEFAULT NULL,
  glucosa_postprandial_2h decimal(5,2) DEFAULT NULL,
  hemoglobina_glicosilada decimal(4,2) DEFAULT NULL,
  fructosamina decimal(6,2) DEFAULT NULL,
  interpretacion_glucosa_ayunas enum('Normal','Precaución','Alerta') DEFAULT NULL,
  interpretacion_hba1c enum('Normal','Precaución','Alerta') DEFAULT NULL,
  observaciones text DEFAULT NULL,
  created_at timestamp NOT NULL DEFAULT current_timestamp(),
  created_by int(11) DEFAULT NULL,
  PRIMARY KEY (id_analisis),
  FOREIGN KEY (id_visita) REFERENCES visitas (id_visita) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS analisis_perfil_lipidico (
  id_analisis int(11) NOT NULL AUTO_INCREMENT,
  id_visita int(11) NOT NULL,
  fecha_analisis date NOT NULL,
  colesterol_total decimal(5,2) DEFAULT NULL,
  ldl decimal(5,2) DEFAULT NULL,
  hdl decimal(5,2) DEFAULT NULL,
  trigliceridos decimal(6,2) DEFAULT NULL,
  colesterol_no_hdl decimal(5,2) DEFAULT NULL,
  indice_aterogenico decimal(4,2) DEFAULT NULL,
  interpretacion_colesterol enum('Normal','Precaución','Alerta') DEFAULT NULL,
  interpretacion_ldl enum('Normal','Precaución','Alerta') DEFAULT NULL,
  interpretacion_trigliceridos enum('Normal','Precaución','Alerta') DEFAULT NULL,
  observaciones text DEFAULT NULL,
  created_at timestamp NOT NULL DEFAULT current_timestamp(),
  created_by int(11) DEFAULT NULL,
  PRIMARY KEY (id_analisis),
  FOREIGN KEY (id_visita) REFERENCES visitas (id_visita) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS analisis_perfil_renal (
  id_analisis int(11) NOT NULL AUTO_INCREMENT,
  id_visita int(11) NOT NULL,
  fecha_analisis date NOT NULL,
  creatinina_serica decimal(5,2) DEFAULT NULL,
  tasa_filtracion_glomerular decimal(5,2) DEFAULT NULL,
  urea decimal(5,2) DEFAULT NULL,
  bun decimal(5,2) DEFAULT NULL,
  acido_urico decimal(5,2) DEFAULT NULL,
  microalbuminuria decimal(6,2) DEFAULT NULL,
  relacion_albumina_creatinina decimal(6,2) DEFAULT NULL,
  proteinuria_24h decimal(6,2) DEFAULT NULL,
  interpretacion_tfg enum('Normal','Precaución','Alerta') DEFAULT NULL,
  interpretacion_microalbuminuria enum('Normal','Precaución','Alerta') DEFAULT NULL,
  observaciones text DEFAULT NULL,
  created_at timestamp NOT NULL DEFAULT current_timestamp(),
  created_by int(11) DEFAULT NULL,
  PRIMARY KEY (id_analisis),
  FOREIGN KEY (id_visita) REFERENCES visitas (id_visita) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Tablas de Laboratorio Adicionales
CREATE TABLE IF NOT EXISTS lab_biometria_hematica (
    id_biometria INT AUTO_INCREMENT PRIMARY KEY,
    id_visita INT NOT NULL,
    fecha_analisis DATE NOT NULL,
    eritrocitos DECIMAL(10,2),
    hemoglobina DECIMAL(10,2),
    hematocrito DECIMAL(10,2),
    vgm DECIMAL(10,2),
    hgm DECIMAL(10,2),
    cmhg DECIMAL(10,2),
    ide DECIMAL(10,2),
    leucocitos DECIMAL(10,2),
    neutrofilos_perc DECIMAL(10,2),
    linfocitos_perc DECIMAL(10,2),
    mid_perc DECIMAL(10,2),
    neutrofilos_abs DECIMAL(10,2),
    linfocitos_abs DECIMAL(10,2),
    mid_abs DECIMAL(10,2),
    plaquetas DECIMAL(10,2),
    observaciones TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    created_by INT,
    FOREIGN KEY (id_visita) REFERENCES visitas(id_visita) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS lab_quimica_sanguinea (
    id_quimica INT AUTO_INCREMENT PRIMARY KEY,
    id_visita INT NOT NULL,
    fecha_analisis DATE NOT NULL,
    glucosa DECIMAL(10,2),
    urea DECIMAL(10,2),
    bun DECIMAL(10,2),
    creatinina DECIMAL(10,2),
    acido_urico DECIMAL(10,2),
    colesterol DECIMAL(10,2),
    trigliceridos DECIMAL(10,2),
    observaciones TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    created_by INT,
    FOREIGN KEY (id_visita) REFERENCES visitas(id_visita) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS lab_examen_orina (
    id_orina INT AUTO_INCREMENT PRIMARY KEY,
    id_visita INT NOT NULL,
    fecha_analisis DATE NOT NULL,
    color VARCHAR(50),
    aspecto VARCHAR(50),
    densidad VARCHAR(20),
    ph DECIMAL(4,1),
    leucocitos_quimico VARCHAR(50),
    nitritos VARCHAR(50),
    proteinas VARCHAR(50),
    glucosa_quimico VARCHAR(50),
    sangre_quimico VARCHAR(50),
    cetonas VARCHAR(50),
    urobilinogeno VARCHAR(50),
    bilirrubina VARCHAR(50),
    celulas_escamosas VARCHAR(50),
    celulas_cilindricas VARCHAR(50),
    celulas_urotelio VARCHAR(50),
    cristales VARCHAR(50),
    celulas_renales VARCHAR(50),
    leucocitos_micro VARCHAR(50),
    cilindros VARCHAR(50),
    eritrocitos_micro VARCHAR(50),
    dismorficos VARCHAR(50),
    bacterias VARCHAR(50),
    hongos VARCHAR(50),
    levaduras VARCHAR(50),
    parasitos VARCHAR(50),
    observaciones TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    created_by INT,
    FOREIGN KEY (id_visita) REFERENCES visitas(id_visita) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS lab_perfil_hepatico (
    id_hepatico INT AUTO_INCREMENT PRIMARY KEY,
    id_visita INT NOT NULL,
    fecha_analisis DATE NOT NULL,
    bilirrubina_total DECIMAL(10,2),
    bilirrubina_directa DECIMAL(10,2),
    bilirrubina_indirecta DECIMAL(10,2),
    alt_gpt DECIMAL(10,2),
    ast_got DECIMAL(10,2),
    fosfatasa_alcalina DECIMAL(10,2),
    ggt DECIMAL(10,2),
    proteinas_totales DECIMAL(10,2),
    albumina DECIMAL(10,2),
    globulina DECIMAL(10,2),
    observaciones TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    created_by INT,
    FOREIGN KEY (id_visita) REFERENCES visitas(id_visita) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS lab_perfil_tiroideo (
    id_tiroideo INT AUTO_INCREMENT PRIMARY KEY,
    id_visita INT NOT NULL,
    fecha_analisis DATE NOT NULL,
    t3_total DECIMAL(10,2),
    t3_libre DECIMAL(10,2),
    t4_total DECIMAL(10,2),
    t4_libre DECIMAL(10,2),
    tsh DECIMAL(10,2),
    observaciones TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    created_by INT,
    FOREIGN KEY (id_visita) REFERENCES visitas(id_visita) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS lab_insulina (
    id_insulina INT AUTO_INCREMENT PRIMARY KEY,
    id_visita INT NOT NULL,
    fecha_analisis DATE NOT NULL,
    insulina_basal DECIMAL(10,2),
    observaciones TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    created_by INT,
    FOREIGN KEY (id_visita) REFERENCES visitas(id_visita) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================================================
-- 6. MEDICAMENTOS Y CATÁLOGOS
-- ============================================================================

CREATE TABLE IF NOT EXISTS medicamentos_catalogo (
  id_medicamento int(11) NOT NULL AUTO_INCREMENT,
  nombre_generico varchar(200) NOT NULL,
  nombre_comercial varchar(200) DEFAULT NULL,
  categoria varchar(100) DEFAULT NULL,
  presentacion varchar(100) DEFAULT NULL,
  via_administracion varchar(50) DEFAULT NULL,
  activo tinyint(1) DEFAULT 1,
  created_at timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (id_medicamento)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT IGNORE INTO medicamentos_catalogo (id_medicamento, nombre_generico, nombre_comercial, categoria, presentacion, via_administracion) VALUES
(1, 'Metformina', 'Glucophage', 'Antidiabético Oral', '500mg, 850mg, 1000mg', 'Oral'),
(2, 'Glibenclamida', 'Daonil', 'Antidiabético Oral', '5mg', 'Oral'),
(3, 'Insulina Glargina', 'Lantus', 'Insulina', '100 UI/mL', 'Subcutánea'),
(4, 'Insulina Lispro', 'Humalog', 'Insulina', '100 UI/mL', 'Subcutánea'),
(5, 'Enalapril', 'Renitec', 'Antihipertensivo', '5mg, 10mg, 20mg', 'Oral'),
(6, 'Losartán', 'Cozaar', 'Antihipertensivo', '50mg, 100mg', 'Oral'),
(7, 'Atorvastatina', 'Lipitor', 'Estatina', '10mg, 20mg, 40mg', 'Oral'),
(8, 'Aspirina', 'Aspirina', 'Antiagregante', '100mg', 'Oral');

CREATE TABLE IF NOT EXISTS tratamientos (
  id_tratamiento int(11) NOT NULL AUTO_INCREMENT,
  id_paciente int(11) NOT NULL,
  id_medicamento int(11) NOT NULL,
  id_visita int(11) DEFAULT NULL,
  dosis varchar(100) DEFAULT NULL,
  frecuencia varchar(100) DEFAULT NULL,
  duracion varchar(100) DEFAULT NULL,
  indicaciones text DEFAULT NULL,
  fecha_inicio date DEFAULT NULL,
  fecha_fin date DEFAULT NULL,
  activo tinyint(1) DEFAULT 1,
  created_at timestamp NOT NULL DEFAULT current_timestamp(),
  created_by int(11) DEFAULT NULL,
  PRIMARY KEY (id_tratamiento),
  FOREIGN KEY (id_paciente) REFERENCES pacientes (id_paciente),
  FOREIGN KEY (id_medicamento) REFERENCES medicamentos_catalogo (id_medicamento),
  FOREIGN KEY (id_visita) REFERENCES visitas (id_visita)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================================================
-- 7. TABLAS COMPLEMENTARIAS
-- ============================================================================

CREATE TABLE IF NOT EXISTS antecedentes_familiares (
  id_antecedente int(11) NOT NULL AUTO_INCREMENT,
  id_paciente int(11) NOT NULL,
  parentesco enum('Padre','Madre','Hermano','Hermana','Abuelo','Abuela','Tío','Tía','Otro') NOT NULL,
  enfermedad varchar(150) NOT NULL,
  edad_diagnostico int(11) DEFAULT NULL,
  observaciones text DEFAULT NULL,
  PRIMARY KEY (id_antecedente),
  FOREIGN KEY (id_paciente) REFERENCES pacientes (id_paciente) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS datos_clinicos (
  id_datos_clinicos int(11) NOT NULL AUTO_INCREMENT,
  id_visita int(11) NOT NULL,
  peso decimal(5,2) DEFAULT NULL,
  talla decimal(5,2) DEFAULT NULL,
  imc decimal(5,2) DEFAULT NULL,
  presion_sistolica int(11) DEFAULT NULL,
  presion_diastolica int(11) DEFAULT NULL,
  frecuencia_cardiaca int(11) DEFAULT NULL,
  frecuencia_respiratoria int(11) DEFAULT NULL,
  temperatura decimal(4,2) DEFAULT NULL,
  saturacion_oxigeno decimal(4,2) DEFAULT NULL,
  circunferencia_cintura decimal(5,2) DEFAULT NULL,
  circunferencia_cadera decimal(5,2) DEFAULT NULL,
  PRIMARY KEY (id_datos_clinicos),
  FOREIGN KEY (id_visita) REFERENCES visitas (id_visita) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS estudio_socioeconomico (
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
    diagnostico_desc_otro TEXT,
    servicio_medico JSON,
    servicio_medico_otro TEXT,
    tratamiento_actual JSON,
    tiene_tratamiento TINYINT(1) DEFAULT 0,
    tratamiento_detalle TEXT,
    cubre_costos_medicamento BOOLEAN,
    cuenta_con_glucometro BOOLEAN,
    dificultad_dieta_economica BOOLEAN,
    
    -- VI. Alimentación
    frecuencia_alimentos JSON,
    
    -- VII. Conclusiones
    observaciones_trabajo_social TEXT,
    nivel_socioeconomico ENUM('Alto', 'Medio', 'Bajo', 'Vulnerabilidad Extrema'),
    plan_intervencion TEXT,
    nombre_entrevistado VARCHAR(200),
    nombre_trabajador_social VARCHAR(200),
    
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    
    FOREIGN KEY (id_paciente) REFERENCES pacientes(id_paciente) ON DELETE CASCADE,
    INDEX idx_socio_paciente (id_paciente)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS estudio_socioeconomico_familiares (
    id_familiar_estudio INT AUTO_INCREMENT PRIMARY KEY,
    id_estudio INT NOT NULL,
    nombre VARCHAR(200) NOT NULL,
    parentesco VARCHAR(100),
    edad INT,
    ocupacion VARCHAR(150),
    ingreso_mensual DECIMAL(10,2),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (id_estudio) REFERENCES estudio_socioeconomico(id_estudio) ON DELETE CASCADE,
    INDEX idx_socio_estudio (id_estudio)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================================================
-- 8. VISTAS
-- ============================================================================

CREATE OR REPLACE VIEW vista_pacientes_activos AS
SELECT 
    p.id_paciente,
    p.numero_expediente,
    CONCAT(p.nombre, ' ', p.apellido_paterno, ' ', COALESCE(p.apellido_materno, '')) as nombre_completo,
    p.edad,
    p.sexo,
    p.protocolo,
    MAX(v.fecha_visita) as ultima_visita,
    COUNT(v.id_visita) as total_visitas
FROM pacientes p
LEFT JOIN visitas v ON p.id_paciente = v.id_paciente
WHERE p.activo = 1
GROUP BY p.id_paciente;

-- ============================================================================
-- 10. DATOS DE EJEMPLO
-- ============================================================================

INSERT IGNORE INTO pacientes (numero_expediente, nombre, apellido_paterno, apellido_materno, fecha_nacimiento, edad, sexo, protocolo) VALUES
('EXP-2026-001', 'Juan', 'Pérez', 'García', '1975-05-15', 49, 'M', 'Diabético'),
('EXP-2026-002', 'María', 'López', 'Hernández', '1980-08-22', 44, 'F', 'Diabético'),
('EXP-2026-003', 'Carlos', 'Martínez', 'Ruiz', '1965-12-10', 59, 'M', 'Diabético');

-- Restaurar configuración original
SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS;
SET SQL_MODE=@OLD_SQL_MODE;

COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
