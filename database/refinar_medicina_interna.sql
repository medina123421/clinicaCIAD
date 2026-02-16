-- Refinamiento de Base de Datos para Medicina Interna y Registro de Pacientes

-- 1. Actualizar tabla de pacientes para incluir contacto de emergencia
ALTER TABLE pacientes
ADD COLUMN IF NOT EXISTS nombre_emergencia VARCHAR(255) AFTER ocupacion,
ADD COLUMN IF NOT EXISTS telefono_emergencia VARCHAR(50) AFTER nombre_emergencia,
ADD COLUMN IF NOT EXISTS parentesco_emergencia VARCHAR(100) AFTER telefono_emergencia;

-- 2. Modificar tabla de consulta_medicina_interna
-- Primero, añadir los campos de detalle para antecedentes
ALTER TABLE consulta_medicina_interna
ADD COLUMN IF NOT EXISTS detalle_alergias TEXT AFTER alergias_check,
ADD COLUMN IF NOT EXISTS detalle_enfermedades_cronicas TEXT AFTER enfermedades_cronicas_check,
ADD COLUMN IF NOT EXISTS detalle_cirugias_previas TEXT AFTER cirugias_previas_check,
ADD COLUMN IF NOT EXISTS detalle_hospitalizaciones_previas TEXT AFTER hospitalizaciones_previas_check;

-- Asegurar que existan todos los campos de laboratorios relevantes (7 en total según imagen)
ALTER TABLE consulta_medicina_interna
ADD COLUMN IF NOT EXISTS lab_glucosa TINYINT(1) DEFAULT 0,
ADD COLUMN IF NOT EXISTS lab_hba1c TINYINT(1) DEFAULT 0,
ADD COLUMN IF NOT EXISTS lab_perfil_lipidico TINYINT(1) DEFAULT 0,
ADD COLUMN IF NOT EXISTS lab_creatinina_tfg TINYINT(1) DEFAULT 0,
ADD COLUMN IF NOT EXISTS lab_microalbuminuria TINYINT(1) DEFAULT 0,
ADD COLUMN IF NOT EXISTS lab_ego TINYINT(1) DEFAULT 0,
ADD COLUMN IF NOT EXISTS lab_funcion_hepatica TINYINT(1) DEFAULT 0;

-- Eliminar los campos de emergencia de consulta_medicina_interna para evitar duplicidad
-- Nota: En un entorno real se migrarían los datos antes de borrar. 
-- Como estamos en desarrollo inicial, los borramos directamente.
ALTER TABLE consulta_medicina_interna
DROP COLUMN IF EXISTS nombre_emergencia,
DROP COLUMN IF EXISTS telefono_emergencia,
DROP COLUMN IF EXISTS parentesco_emergencia;
