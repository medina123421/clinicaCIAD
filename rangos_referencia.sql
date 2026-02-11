-- ============================================================================
-- DATOS INICIALES: RANGOS DE REFERENCIA Y CONFIGURACIÓN
-- Sistema de interpretación automática para análisis clínicos
-- ============================================================================

USE clinica_diabetes;

-- ============================================================================
-- INSERTAR ROLES
-- ============================================================================

INSERT INTO roles (nombre_rol, descripcion) VALUES
('Administrador', 'Acceso completo al sistema'),
('Doctor', 'Médico con acceso a pacientes y análisis');

-- ============================================================================
-- RANGOS DE REFERENCIA PARA PARÁMETROS CLÍNICOS
-- ============================================================================

-- Glucosa y Control Glucémico
INSERT INTO rangos_referencia (parametro, unidad, valor_minimo_normal, valor_maximo_normal, valor_minimo_precaucion, valor_maximo_precaucion, descripcion) VALUES
('glucosa_ayunas', 'mg/dL', 70, 100, 100, 125, 'Glucosa en ayunas: Normal <100, Prediabetes 100-125, Diabetes ≥126'),
('glucosa_postprandial', 'mg/dL', 70, 140, 140, 199, 'Glucosa 2h postprandial: Normal <140, Prediabetes 140-199, Diabetes ≥200'),
('hba1c', '%', 4.0, 5.6, 5.7, 6.4, 'HbA1c: Normal <5.7%, Prediabetes 5.7-6.4%, Diabetes ≥6.5%'),

-- Perfil Renal
('creatinina_hombre', 'mg/dL', 0.7, 1.3, 1.3, 2.0, 'Creatinina sérica en hombres'),
('creatinina_mujer', 'mg/dL', 0.6, 1.1, 1.1, 1.8, 'Creatinina sérica en mujeres'),
('tfg', 'mL/min/1.73m²', 90, 120, 60, 89, 'Tasa de Filtración Glomerular: Normal ≥90, Precaución 60-89, Alerta <60'),
('urea', 'mg/dL', 15, 40, 40, 60, 'Urea en sangre'),
('bun', 'mg/dL', 7, 20, 20, 30, 'Nitrógeno Ureico en Sangre'),
('microalbuminuria', 'mg/24h', 0, 30, 30, 300, 'Microalbuminuria: Normal <30, Precaución 30-300, Alerta >300'),
('acr', 'mg/g', 0, 30, 30, 300, 'Relación Albúmina/Creatinina en orina'),

-- Perfil Lipídico
('colesterol_total', 'mg/dL', 0, 200, 200, 239, 'Colesterol total: Deseable <200, Límite alto 200-239, Alto ≥240'),
('ldl', 'mg/dL', 0, 100, 100, 159, 'LDL: Óptimo <100, Límite alto 100-159, Alto ≥160'),
('hdl_hombre', 'mg/dL', 40, 200, 35, 39, 'HDL en hombres: Bajo <40'),
('hdl_mujer', 'mg/dL', 50, 200, 40, 49, 'HDL en mujeres: Bajo <50'),
('trigliceridos', 'mg/dL', 0, 150, 150, 199, 'Triglicéridos: Normal <150, Límite alto 150-199, Alto ≥200'),

-- Electrolitos
('sodio', 'mEq/L', 136, 145, 133, 148, 'Sodio sérico'),
('potasio', 'mEq/L', 3.5, 5.0, 3.0, 5.5, 'Potasio sérico'),
('cloro', 'mEq/L', 96, 106, 90, 110, 'Cloro sérico'),
('bicarbonato', 'mEq/L', 22, 28, 18, 32, 'Bicarbonato sérico'),
('calcio', 'mg/dL', 8.5, 10.5, 8.0, 11.0, 'Calcio sérico'),
('fosforo', 'mg/dL', 2.5, 4.5, 2.0, 5.5, 'Fósforo sérico'),
('magnesio', 'mg/dL', 1.7, 2.2, 1.5, 2.5, 'Magnesio sérico'),

-- Marcadores Hepáticos
('alt', 'U/L', 7, 56, 56, 100, 'ALT (Alanina aminotransferasa)'),
('ast', 'U/L', 10, 40, 40, 80, 'AST (Aspartato aminotransferasa)'),
('fosfatasa_alcalina', 'U/L', 44, 147, 147, 200, 'Fosfatasa alcalina'),
('bilirrubina_total', 'mg/dL', 0.1, 1.2, 1.2, 2.5, 'Bilirrubina total'),
('albumina', 'g/dL', 3.5, 5.5, 3.0, 3.4, 'Albúmina sérica'),

-- Marcadores Cardiovasculares
('troponina', 'ng/mL', 0, 0.04, 0.04, 0.1, 'Troponina: Normal <0.04, Precaución 0.04-0.1, Alerta >0.1'),
('bnp', 'pg/mL', 0, 100, 100, 400, 'BNP: Normal <100, Precaución 100-400, Alerta >400'),
('nt_probnp', 'pg/mL', 0, 125, 125, 450, 'NT-proBNP'),
('homocisteina', 'µmol/L', 5, 15, 15, 30, 'Homocisteína'),

-- Otros Estudios
('vitamina_d', 'ng/mL', 30, 100, 20, 29, 'Vitamina D: Suficiente ≥30, Insuficiente 20-29, Deficiente <20'),
('tsh', 'µIU/mL', 0.4, 4.0, 4.0, 10.0, 'TSH (Hormona estimulante de tiroides)'),
('t4_libre', 'ng/dL', 0.8, 1.8, 0.5, 2.5, 'T4 libre'),
('hemoglobina_hombre', 'g/dL', 13.5, 17.5, 12.0, 13.4, 'Hemoglobina en hombres'),
('hemoglobina_mujer', 'g/dL', 12.0, 16.0, 10.5, 11.9, 'Hemoglobina en mujeres'),
('hematocrito_hombre', '%', 38.8, 50.0, 35.0, 38.7, 'Hematocrito en hombres'),
('hematocrito_mujer', '%', 34.9, 44.5, 30.0, 34.8, 'Hematocrito en mujeres'),
('leucocitos', 'células/µL', 4000, 11000, 3500, 12000, 'Leucocitos'),
('plaquetas', 'células/µL', 150000, 400000, 100000, 450000, 'Plaquetas'),
('pcr', 'mg/L', 0, 3.0, 3.0, 10.0, 'Proteína C Reactiva'),
('vsg', 'mm/h', 0, 20, 20, 50, 'Velocidad de Sedimentación Globular'),

-- Signos Vitales
('peso_normal_imc', 'kg/m²', 18.5, 24.9, 25.0, 29.9, 'IMC: Normal 18.5-24.9, Sobrepeso 25-29.9, Obesidad ≥30'),
('presion_sistolica', 'mmHg', 90, 119, 120, 139, 'PA Sistólica: Normal <120, Prehipertensión 120-139, Hipertensión ≥140'),
('presion_diastolica', 'mmHg', 60, 79, 80, 89, 'PA Diastólica: Normal <80, Prehipertensión 80-89, Hipertensión ≥90'),
('frecuencia_cardiaca', 'lpm', 60, 100, 50, 110, 'Frecuencia cardíaca en reposo'),
('frecuencia_respiratoria', 'rpm', 12, 20, 10, 24, 'Frecuencia respiratoria'),
('temperatura', '°C', 36.1, 37.2, 37.3, 38.0, 'Temperatura corporal'),
('saturacion_oxigeno', '%', 95, 100, 90, 94, 'Saturación de oxígeno');

-- ============================================================================
-- INTERPRETACIONES AUTOMÁTICAS
-- ============================================================================

-- Glucosa en Ayunas
INSERT INTO interpretaciones (parametro, condicion, valor_referencia, valor_referencia_max, nivel_alerta, mensaje, recomendacion) VALUES
('glucosa_ayunas', 'menor_que', 70, NULL, 'Alerta', 'Hipoglucemia detectada', 'Atención inmediata. Administrar carbohidratos de acción rápida.'),
('glucosa_ayunas', 'entre', 70, 100, 'Normal', 'Glucosa en ayunas normal', 'Mantener control y estilo de vida saludable.'),
('glucosa_ayunas', 'entre', 100, 126, 'Precaución', 'Prediabetes - Glucosa alterada en ayunas', 'Modificaciones en estilo de vida. Considerar intervención preventiva.'),
('glucosa_ayunas', 'mayor_igual', 126, NULL, 'Alerta', 'Diabetes - Glucosa elevada', 'Requiere tratamiento farmacológico. Ajustar medicación si ya está en tratamiento.'),

-- HbA1c
('hba1c', 'menor_que', 5.7, NULL, 'Normal', 'Control glucémico excelente', 'Mantener adherencia al tratamiento y estilo de vida.'),
('hba1c', 'entre', 5.7, 6.5, 'Precaución', 'Prediabetes', 'Alto riesgo de desarrollar diabetes. Intervención intensiva en estilo de vida.'),
('hba1c', 'entre', 6.5, 7.0, 'Precaución', 'Diabetes con control aceptable', 'Optimizar tratamiento para alcanzar meta <7%.'),
('hba1c', 'entre', 7.0, 9.0, 'Alerta', 'Diabetes con control inadecuado', 'Ajuste de tratamiento necesario. Revisar adherencia.'),
('hba1c', 'mayor_igual', 9.0, NULL, 'Alerta', 'Diabetes con control muy deficiente', 'Ajuste urgente de tratamiento. Riesgo alto de complicaciones.'),

-- Presión Arterial Sistólica
('presion_sistolica', 'menor_que', 90, NULL, 'Alerta', 'Hipotensión', 'Evaluar causas. Puede requerir ajuste de medicación antihipertensiva.'),
('presion_sistolica', 'entre', 90, 120, 'Normal', 'Presión arterial normal', 'Mantener control y estilo de vida saludable.'),
('presion_sistolica', 'entre', 120, 140, 'Precaución', 'Prehipertensión', 'Modificaciones en estilo de vida. Monitoreo cercano.'),
('presion_sistolica', 'entre', 140, 160, 'Alerta', 'Hipertensión Estadio 1', 'Iniciar o ajustar tratamiento antihipertensivo.'),
('presion_sistolica', 'mayor_igual', 160, NULL, 'Alerta', 'Hipertensión Estadio 2', 'Tratamiento farmacológico urgente. Riesgo cardiovascular elevado.'),

-- IMC
('imc', 'menor_que', 18.5, NULL, 'Precaución', 'Bajo peso', 'Evaluación nutricional. Descartar causas subyacentes.'),
('imc', 'entre', 18.5, 25, 'Normal', 'Peso normal', 'Mantener peso saludable con dieta balanceada y ejercicio.'),
('imc', 'entre', 25, 30, 'Precaución', 'Sobrepeso', 'Programa de pérdida de peso. Dieta y ejercicio.'),
('imc', 'entre', 30, 35, 'Alerta', 'Obesidad Grado I', 'Intervención intensiva. Considerar tratamiento farmacológico.'),
('imc', 'entre', 35, 40, 'Alerta', 'Obesidad Grado II', 'Tratamiento multidisciplinario. Evaluar cirugía bariátrica.'),
('imc', 'mayor_igual', 40, NULL, 'Alerta', 'Obesidad Grado III (Mórbida)', 'Riesgo muy alto. Considerar cirugía bariátrica.'),

-- Colesterol Total
('colesterol_total', 'menor_que', 200, NULL, 'Normal', 'Colesterol total deseable', 'Mantener dieta saludable baja en grasas saturadas.'),
('colesterol_total', 'entre', 200, 240, 'Precaución', 'Colesterol límite alto', 'Modificaciones en dieta. Considerar estatinas si hay otros factores de riesgo.'),
('colesterol_total', 'mayor_igual', 240, NULL, 'Alerta', 'Colesterol alto', 'Tratamiento con estatinas. Dieta estricta baja en grasas.'),

-- LDL
('ldl', 'menor_que', 100, NULL, 'Normal', 'LDL óptimo', 'Mantener niveles con dieta y ejercicio.'),
('ldl', 'entre', 100, 130, 'Normal', 'LDL cercano al óptimo', 'Mantener control. Dieta saludable.'),
('ldl', 'entre', 130, 160, 'Precaución', 'LDL límite alto', 'Modificaciones en estilo de vida. Considerar estatinas.'),
('ldl', 'entre', 160, 190, 'Alerta', 'LDL alto', 'Tratamiento con estatinas recomendado.'),
('ldl', 'mayor_igual', 190, NULL, 'Alerta', 'LDL muy alto', 'Tratamiento farmacológico urgente. Riesgo cardiovascular muy elevado.'),

-- HDL (Hombres)
('hdl_hombre', 'menor_que', 40, NULL, 'Alerta', 'HDL bajo - Factor de riesgo cardiovascular', 'Aumentar actividad física. Considerar niacina o fibratos.'),
('hdl_hombre', 'mayor_igual', 40, NULL, 'Normal', 'HDL adecuado', 'Mantener niveles con ejercicio regular.'),

-- HDL (Mujeres)
('hdl_mujer', 'menor_que', 50, NULL, 'Alerta', 'HDL bajo - Factor de riesgo cardiovascular', 'Aumentar actividad física. Considerar niacina o fibratos.'),
('hdl_mujer', 'mayor_igual', 50, NULL, 'Normal', 'HDL adecuado', 'Mantener niveles con ejercicio regular.'),

-- Triglicéridos
('trigliceridos', 'menor_que', 150, NULL, 'Normal', 'Triglicéridos normales', 'Mantener dieta baja en azúcares y carbohidratos refinados.'),
('trigliceridos', 'entre', 150, 200, 'Precaución', 'Triglicéridos límite alto', 'Reducir azúcares y carbohidratos. Aumentar ejercicio.'),
('trigliceridos', 'entre', 200, 500, 'Alerta', 'Triglicéridos altos', 'Tratamiento con fibratos. Dieta estricta.'),
('trigliceridos', 'mayor_igual', 500, NULL, 'Alerta', 'Triglicéridos muy altos - Riesgo de pancreatitis', 'Tratamiento urgente. Hospitalización si hay síntomas.'),

-- TFG (Tasa de Filtración Glomerular)
('tfg', 'mayor_igual', 90, NULL, 'Normal', 'Función renal normal', 'Mantener control de glucosa y presión arterial.'),
('tfg', 'entre', 60, 89, 'Precaución', 'Enfermedad Renal Crónica Estadio 2', 'Monitoreo cercano. Control estricto de glucosa y PA.'),
('tfg', 'entre', 45, 59, 'Alerta', 'Enfermedad Renal Crónica Estadio 3a', 'Referir a nefrología. Ajustar medicamentos.'),
('tfg', 'entre', 30, 44, 'Alerta', 'Enfermedad Renal Crónica Estadio 3b', 'Manejo por nefrología. Preparar para terapia de reemplazo.'),
('tfg', 'entre', 15, 29, 'Alerta', 'Enfermedad Renal Crónica Estadio 4', 'Preparación para diálisis o trasplante.'),
('tfg', 'menor_que', 15, NULL, 'Alerta', 'Enfermedad Renal Crónica Estadio 5 - Falla renal', 'Diálisis o trasplante requerido.'),

-- Creatinina (Hombres)
('creatinina_hombre', 'entre', 0.7, 1.3, 'Normal', 'Creatinina normal', 'Función renal adecuada.'),
('creatinina_hombre', 'mayor_que', 1.3, NULL, 'Alerta', 'Creatinina elevada', 'Evaluar función renal. Calcular TFG.'),

-- Creatinina (Mujeres)
('creatinina_mujer', 'entre', 0.6, 1.1, 'Normal', 'Creatinina normal', 'Función renal adecuada.'),
('creatinina_mujer', 'mayor_que', 1.1, NULL, 'Alerta', 'Creatinina elevada', 'Evaluar función renal. Calcular TFG.'),

-- Vitamina D
('vitamina_d', 'menor_que', 20, NULL, 'Alerta', 'Deficiencia de Vitamina D', 'Suplementación con dosis altas. 50,000 UI semanales.'),
('vitamina_d', 'entre', 20, 30, 'Precaución', 'Insuficiencia de Vitamina D', 'Suplementación con 1000-2000 UI diarias.'),
('vitamina_d', 'mayor_igual', 30, NULL, 'Normal', 'Vitamina D suficiente', 'Mantener exposición solar y dieta adecuada.');

-- ============================================================================
-- CATÁLOGO DE MEDICAMENTOS COMUNES
-- ============================================================================

INSERT INTO medicamentos_catalogo (nombre_generico, nombre_comercial, categoria, presentacion, via_administracion) VALUES
-- Antidiabéticos Orales
('Metformina', 'Glucophage', 'Antidiabético Oral', '500mg, 850mg, 1000mg tabletas', 'Oral'),
('Glibenclamida', 'Daonil', 'Antidiabético Oral', '5mg tabletas', 'Oral'),
('Glimepirida', 'Amaryl', 'Antidiabético Oral', '1mg, 2mg, 4mg tabletas', 'Oral'),
('Sitagliptina', 'Januvia', 'Antidiabético Oral', '50mg, 100mg tabletas', 'Oral'),
('Empagliflozina', 'Jardiance', 'Antidiabético Oral', '10mg, 25mg tabletas', 'Oral'),
('Dapagliflozina', 'Forxiga', 'Antidiabético Oral', '5mg, 10mg tabletas', 'Oral'),
('Pioglitazona', 'Actos', 'Antidiabético Oral', '15mg, 30mg, 45mg tabletas', 'Oral'),

-- Insulinas
('Insulina Glargina', 'Lantus', 'Insulina', '100 UI/mL', 'Subcutánea'),
('Insulina Detemir', 'Levemir', 'Insulina', '100 UI/mL', 'Subcutánea'),
('Insulina NPH', 'Humulin N', 'Insulina', '100 UI/mL', 'Subcutánea'),
('Insulina Regular', 'Humulin R', 'Insulina', '100 UI/mL', 'Subcutánea'),
('Insulina Lispro', 'Humalog', 'Insulina', '100 UI/mL', 'Subcutánea'),
('Insulina Aspart', 'NovoRapid', 'Insulina', '100 UI/mL', 'Subcutánea'),

-- Antihipertensivos
('Enalapril', 'Renitec', 'Antihipertensivo', '5mg, 10mg, 20mg tabletas', 'Oral'),
('Losartán', 'Cozaar', 'Antihipertensivo', '50mg, 100mg tabletas', 'Oral'),
('Amlodipino', 'Norvasc', 'Antihipertensivo', '5mg, 10mg tabletas', 'Oral'),
('Hidroclorotiazida', 'Microzide', 'Antihipertensivo', '25mg, 50mg tabletas', 'Oral'),

-- Estatinas
('Atorvastatina', 'Lipitor', 'Estatina', '10mg, 20mg, 40mg, 80mg tabletas', 'Oral'),
('Rosuvastatina', 'Crestor', 'Estatina', '5mg, 10mg, 20mg, 40mg tabletas', 'Oral'),
('Simvastatina', 'Zocor', 'Estatina', '10mg, 20mg, 40mg tabletas', 'Oral'),

-- Antiagregantes
('Ácido Acetilsalicílico', 'Aspirina', 'Antiagregante', '100mg tabletas', 'Oral'),
('Clopidogrel', 'Plavix', 'Antiagregante', '75mg tabletas', 'Oral');

-- ============================================================================
-- USUARIO ADMINISTRADOR POR DEFECTO
-- ============================================================================
-- NOTA: La contraseña debe ser hasheada en la aplicación antes de insertar
-- Este es solo un ejemplo. La contraseña "admin123" debe ser hasheada con bcrypt

-- INSERT INTO usuarios (id_rol, nombre, apellido_paterno, email, password_hash, activo) VALUES
-- (1, 'Administrador', 'Sistema', 'admin@clinica.com', '$2y$10$ejemplo_hash_bcrypt', TRUE);

-- ============================================================================
-- FIN DEL SCRIPT DE DATOS INICIALES
-- ============================================================================
