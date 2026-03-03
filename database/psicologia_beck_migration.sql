-- Eliminar la tabla antigua para borrar los datos anteriores
DROP TABLE IF EXISTS `consulta_psicologia`;

-- Crear la nueva tabla para la Escala de Beck (Desesperanza)
CREATE TABLE `consulta_psicologia` (
    `id_psicologia` INT AUTO_INCREMENT PRIMARY KEY,
    `id_visita` INT NOT NULL,
    `id_paciente` INT NOT NULL,
    `descripcion_paciente` TEXT,
    
    -- Preguntas de la Escala de Beck (20 ítems)
    `q1` ENUM('V', 'F') DEFAULT NULL,
    `q2` ENUM('V', 'F') DEFAULT NULL,
    `q3` ENUM('V', 'F') DEFAULT NULL,
    `q4` ENUM('V', 'F') DEFAULT NULL,
    `q5` ENUM('V', 'F') DEFAULT NULL,
    `q6` ENUM('V', 'F') DEFAULT NULL,
    `q7` ENUM('V', 'F') DEFAULT NULL,
    `q8` ENUM('V', 'F') DEFAULT NULL,
    `q9` ENUM('V', 'F') DEFAULT NULL,
    `q10` ENUM('V', 'F') DEFAULT NULL,
    `q11` ENUM('V', 'F') DEFAULT NULL,
    `q12` ENUM('V', 'F') DEFAULT NULL,
    `q13` ENUM('V', 'F') DEFAULT NULL,
    `q14` ENUM('V', 'F') DEFAULT NULL,
    `q15` ENUM('V', 'F') DEFAULT NULL,
    `q16` ENUM('V', 'F') DEFAULT NULL,
    `q17` ENUM('V', 'F') DEFAULT NULL,
    `q18` ENUM('V', 'F') DEFAULT NULL,
    `q19` ENUM('V', 'F') DEFAULT NULL,
    `q20` ENUM('V', 'F') DEFAULT NULL,
    
    -- Puntuación total (calculada)
    `puntuacion_total` INT DEFAULT 0,
    
    `fecha_registro` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,

    FOREIGN KEY (`id_visita`) REFERENCES `visitas`(`id_visita`) ON DELETE CASCADE,
    FOREIGN KEY (`id_paciente`) REFERENCES `pacientes`(`id_paciente`) ON DELETE CASCADE
);
