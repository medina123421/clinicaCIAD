-- ============================================================================
-- SEED DATA: 20 PACIENTES + HISTORIAL CLÍNICO
-- ============================================================================

SET FOREIGN_KEY_CHECKS = 0;
TRUNCATE TABLE pacientes;
TRUNCATE TABLE visitas;
TRUNCATE TABLE consulta_medicina_interna;
TRUNCATE TABLE consulta_nutricion;
TRUNCATE TABLE consulta_psicologia;
TRUNCATE TABLE actividad_fisica;
TRUNCATE TABLE cuidado_pies;
TRUNCATE TABLE educacion_diabetes;
TRUNCATE TABLE analisis_glucosa;
TRUNCATE TABLE analisis_perfil_lipidico;
TRUNCATE TABLE analisis_perfil_renal;
TRUNCATE TABLE lab_biometria_hematica;
TRUNCATE TABLE lab_examen_orina;
TRUNCATE TABLE lab_insulina;
TRUNCATE TABLE lab_perfil_hepatico;
TRUNCATE TABLE lab_perfil_tiroideo;
TRUNCATE TABLE lab_quimica_sanguinea;
SET FOREIGN_KEY_CHECKS = 1;

-- 1. PACIENTES (20)
INSERT INTO pacientes (id_paciente, numero_expediente, nombre, apellido_paterno, apellido_materno, fecha_nacimiento, edad, sexo, telefono, email, direccion, ciudad, estado, codigo_postal, tipo_sangre, protocolo, activo) VALUES
(1, 'EXP-0001', 'Roberto', 'Carlos', 'García', '1965-05-12', 60, 'M', '555-0101', 'roberto@email.com', 'Av. Central 123', 'CDMX', 'CDMX', '06700', 'O+', 'Diabético', 1),
(2, 'EXP-0002', 'Elena', 'Torres', 'Luna', '1972-08-24', 53, 'F', '555-0102', 'elena@email.com', 'Calle Roble 45', 'Querétaro', 'Qro', '76000', 'A+', 'Prediabético', 1),
(3, 'EXP-0003', 'Miguel', 'Angel', 'Sánchez', '1958-11-30', 67, 'M', '555-0103', 'miguel@email.com', 'Hidalgo 789', 'Puebla', 'Pue', '72000', 'B+', 'Diabético', 1),
(4, 'EXP-0004', 'Lucía', 'Fernández', 'Mora', '1980-03-15', 45, 'F', '555-0104', 'lucia@email.com', 'Pinos 12', 'Monterrey', 'NL', '64000', 'O-', 'Prediabético', 1),
(5, 'EXP-0005', 'Javier', 'Ramírez', 'Ortiz', '1950-01-20', 76, 'M', '555-0105', 'javier@email.com', 'Reforma 100', 'Guadalajara', 'Jal', '44100', 'AB+', 'Diabético', 1),
(6, 'EXP-0006', 'Marta', 'Vázquez', 'Ruiz', '1975-06-10', 50, 'F', '555-0106', 'marta@email.com', 'Cedros 567', 'Toluca', 'Mex', '50000', 'O+', 'Prediabético', 1),
(7, 'EXP-0007', 'Andrés', 'Castro', 'Peña', '1962-09-05', 63, 'M', '555-0107', 'andres@email.com', 'Vallarta 23', 'Zapopan', 'Jal', '45100', 'A-', 'Diabético', 1),
(8, 'EXP-0008', 'Sofía', 'Gómez', 'Roldán', '1985-12-28', 40, 'F', '555-0108', 'sofia@email.com', 'Morelos 90', 'Morelia', 'Mich', '58000', 'B-', 'Prediabético', 1),
(9, 'EXP-0009', 'Ricardo', 'Díaz', 'Valle', '1955-04-14', 70, 'M', '555-0109', 'ricardo@email.com', 'Juárez 101', 'León', 'Gto', '37000', 'O+', 'Diabético', 1),
(10, 'EXP-0010', 'Carmen', 'Maldonado', 'Vega', '1970-10-31', 55, 'F', '555-0110', 'carmen@email.com', 'Independencia 2', 'Tepic', 'Nay', '63000', 'A+', 'Diabético', 1),
(11, 'EXP-0011', 'Hugo', 'Santos', 'Mejía', '1968-07-22', 57, 'M', '555-0111', 'hugo@email.com', 'Galeana 55', 'Colima', 'Col', '28000', 'O+', 'Prediabético', 1),
(12, 'EXP-0012', 'Paola', 'Reyes', 'Cano', '1990-02-14', 35, 'F', '555-0112', 'paola@email.com', 'Bolívar 34', 'Merida', 'Yuc', '97000', 'O+', 'Prediabético', 1),
(13, 'EXP-0013', 'Felipe', 'Navarro', 'Sosa', '1945-05-19', 80, 'M', '555-0113', 'felipe@email.com', 'Zapata 67', 'Oaxaca', 'Oax', '68000', 'B+', 'Diabético', 1),
(14, 'EXP-0014', 'Gabriela', 'Medina', 'Pons', '1978-01-01', 48, 'F', '555-0114', 'gabriela@email.com', 'Allende 9', 'Cancún', 'QR', '77500', 'A+', 'Diabético', 1),
(15, 'EXP-0015', 'Enrique', 'Bermúdez', 'Tello', '1960-03-30', 65, 'M', '555-0115', 'enrique@email.com', 'Palmas 202', 'Culiacán', 'Sin', '80000', 'O-', 'Diabético', 1),
(16, 'EXP-0016', 'Silvia', 'Carrillo', 'Lazo', '1982-11-11', 43, 'F', '555-0116', 'silvia@email.com', 'Naranjos 5', 'Hermosillo', 'Son', '83000', 'O+', 'Prediabético', 1),
(17, 'EXP-0017', 'Oscar', 'Salazar', 'Mier', '1952-06-25', 73, 'M', '555-0117', 'oscar@email.com', 'Mina 30', 'Pachuca', 'Hgo', '42000', 'AB-', 'Diabético', 1),
(18, 'EXP-0018', 'Isabel', 'Nava', 'Campos', '1976-04-03', 49, 'F', '555-0118', 'isabel@email.com', 'Lerdo 8', 'Veracruz', 'Ver', '91700', 'A+', 'Diabético', 1),
(19, 'EXP-0019', 'Fernando', 'Guerrero', 'Lara', '1964-08-17', 61, 'M', '555-0119', 'fernando@email.com', 'Viveros 11', 'Saltillo', 'Coah', '25000', 'O+', 'Prediabético', 1),
(20, 'EXP-0020', 'Beatriz', 'Espinoza', 'Ríos', '1988-09-09', 37, 'F', '555-0120', 'beatriz@email.com', 'Flores 4', 'Tijuana', 'BC', '22000', 'O+', 'Prediabético', 1);

-- 2. VISITAS (1 por paciente para empezar)
INSERT INTO visitas (id_visita, id_paciente, id_doctor, fecha_visita, tipo_visita, numero_visita, motivo_consulta, estatus) VALUES
(1, 1, 2, '2026-02-01 10:00:00', 'Consulta Inicial', 1, 'Control rutinario de diabetes', 'Completada'),
(2, 2, 2, '2026-02-01 11:00:00', 'Consulta Inicial', 1, 'Fatiga y sed excesiva', 'Completada'),
(3, 3, 2, '2026-02-02 09:00:00', 'Consulta Inicial', 1, 'Revision de niveles de glucosa altos', 'Completada'),
(4, 4, 3, '2026-02-02 10:30:00', 'Consulta Inicial', 1, 'Plan alimenticio para prediabetes', 'Completada'),
(5, 5, 2, '2026-02-03 08:30:00', 'Sucesiva', 2, 'Seguimiento de tratamiento', 'Completada'),
(6, 6, 4, '2026-02-03 12:00:00', 'Consulta Inicial', 1, 'Ansiedad relacionada al diagnostico', 'Completada'),
(7, 7, 5, '2026-02-04 10:00:00', 'Consulta Inicial', 1, 'Evaluacion para inicio de ejercicio', 'Completada'),
(8, 8, 2, '2026-02-04 11:30:00', 'Consulta Inicial', 1, 'Deteccion oportuna', 'Completada'),
(9, 9, 2, '2026-02-05 09:00:00', 'Sucesiva', 3, 'Ajuste de insulina', 'Completada'),
(10, 10, 3, '2026-02-05 10:00:00', 'Consulta Inicial', 1, 'Cambio de dieta', 'Completada'),
(11, 11, 2, '2026-02-06 10:00:00', 'Consulta Inicial', 1, 'Chequeo general', 'Completada'),
(12, 12, 2, '2026-02-06 11:00:00', 'Consulta Inicial', 1, 'Revision de laboratorios', 'Completada'),
(13, 13, 2, '2026-02-07 09:00:00', 'Consulta Inicial', 1, 'Dolor en extremidades', 'Completada'),
(14, 14, 5, '2026-02-07 10:00:00', 'Consulta Inicial', 1, 'Plan de actividad fisica', 'Completada'),
(15, 15, 2, '2026-02-10 08:30:00', 'Consulta Inicial', 1, 'Nueva paciente derivada', 'Completada'),
(16, 16, 3, '2026-02-10 12:00:00', 'Consulta Inicial', 1, 'Educacion nutricional', 'Completada'),
(17, 17, 2, '2026-02-11 10:00:00', 'Consulta Inicial', 1, 'Vision borrosa', 'Completada'),
(18, 18, 4, '2026-02-11 11:00:00', 'Consulta Inicial', 1, 'Problemas de sueño', 'Completada'),
(19, 19, 2, '2026-02-12 09:00:00', 'Consulta Inicial', 1, 'Control glucemico', 'Completada'),
(20, 20, 2, '2026-02-12 10:30:00', 'Consulta Inicial', 1, 'Seguimiento prediabetes', 'Completada');

-- 3. MEDICINA INTERNA (MUESTRAS)
INSERT INTO consulta_medicina_interna (id_paciente, id_visita, fecha_registro, tipo_diabetes, control_actual, peso, talla, imc, presion_arterial, frecuencia_cardiaca, temperatura, glucosa_capilar) VALUES
(1, 1, '2026-02-01', 'Tipo 2', 'Bueno', 85.5, 175, 27.9, '120/80', 72, 36.5, 110),
(2, 2, '2026-02-01', 'Gestacional', 'Regular', 70.0, 160, 27.3, '130/85', 80, 36.6, 145),
(3, 3, '2026-02-02', 'Tipo 2', 'Malo', 95.0, 170, 32.9, '150/90', 88, 36.8, 210),
(5, 5, '2026-02-03', 'Tipo 2', 'Excelente', 78.0, 168, 27.6, '115/75', 68, 36.4, 95);

-- 4. NUTRICION
INSERT INTO consulta_nutricion (id_visita, id_paciente, fecha_registro, peso, talla, imc, porcentaje_grasa, masa_muscular, diagnostico_nutricional) VALUES
(4, 4, '2026-02-02', 65.0, 158, 26.0, 32.5, 22.0, 'Sobrepeso inicial'),
(10, 10, '2026-02-05', 72.0, 162, 27.4, 35.0, 24.0, 'Obesidad grado I'),
(16, 16, '2026-02-10', 58.0, 155, 24.1, 28.0, 20.0, 'Peso saludable');

-- 5. PSICOLOGIA
INSERT INTO consulta_psicologia (id_paciente, id_visita, fecha_registro, v1_ansiedad_beck, v1_depresion_beck, estado_emocional) VALUES
(6, 6, '2026-02-03', 15, 10, 'Ansioso'),
(18, 18, '2026-02-11', 8, 5, 'Estable');

-- 6. ACTIVIDAD FISICA
INSERT INTO actividad_fisica (id_visita, sarc_puntuacion_total, sarc_riesgo, dina_mano_der, sts_30seg_reps, act_realiza_ejercicio) VALUES
(7, 4, 'Baja', 28.5, 15, 1),
(14, 2, 'Baja', 32.0, 20, 1);

-- 7. LABORATORIOS (GLUCOSA)
INSERT INTO analisis_glucosa (id_visita, fecha_analisis, glucosa_ayunas, hemoglobina_glicosilada, interpretacion_hba1c) VALUES
(1, '2026-01-25', 115.0, 6.8, 'Precaución'),
(3, '2026-01-28', 198.0, 8.5, 'Alerta'),
(12, '2026-02-05', 98.0, 5.7, 'Normal');

-- 8. CUIDADO DE PIES
INSERT INTO cuidado_pies (id_visita, ulcera_previa, ardor_hormigueo, riesgo_der, riesgo_izq) VALUES
(13, 1, 1, 'Moderado', 'Leve'),
(17, 0, 1, 'Leve', 'Leve');
