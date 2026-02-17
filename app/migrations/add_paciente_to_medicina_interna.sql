-- Migración para relacionar Medicina Interna directamente con Pacientes
ALTER TABLE consulta_medicina_interna
ADD COLUMN id_paciente INT AFTER id_medicina_interna,
ADD COLUMN fecha_registro TIMESTAMP DEFAULT CURRENT_TIMESTAMP AFTER id_paciente,
MODIFY COLUMN id_visita INT NULL;

-- Actualizar registros existentes basándose en la tabla de visitas
UPDATE consulta_medicina_interna cmi
JOIN visitas v ON cmi.id_visita = v.id_visita
SET cmi.id_paciente = v.id_paciente
WHERE cmi.id_paciente IS NULL;

-- Añadir llave foránea
ALTER TABLE consulta_medicina_interna
ADD CONSTRAINT fk_medicina_interna_paciente
FOREIGN KEY (id_paciente) REFERENCES pacientes(id_paciente)
ON DELETE CASCADE;
