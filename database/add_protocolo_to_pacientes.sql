-- Migración: Añadir campo Protocolo a tabla pacientes
ALTER TABLE pacientes ADD COLUMN protocolo ENUM('Diabético', 'Prediabético') DEFAULT 'Diabético' AFTER alergias;
