<?php
require_once 'app/config/database.php';

try {
    $database = new Database();
    $db = $database->getConnection();

    // Hacer la migración idempotente sin depender de "IF NOT EXISTS" (compatibilidad MySQL/MariaDB)
    $check = $db->prepare("
        SELECT COUNT(*) AS c
        FROM INFORMATION_SCHEMA.COLUMNS
        WHERE TABLE_SCHEMA = DATABASE()
          AND TABLE_NAME = 'cuidado_pies'
          AND COLUMN_NAME = 'cambios_color'
    ");
    $check->execute();
    $exists = (int)($check->fetch(PDO::FETCH_ASSOC)['c'] ?? 0) > 0;

    if ($exists) {
        echo "La columna cambios_color ya existe. No se aplicó ALTER.\n";
    } else {
        $sql = "ALTER TABLE cuidado_pies
                ADD COLUMN cambios_color TINYINT(1) DEFAULT 0
                COMMENT 'Cambios de color en la piel de piernas o pies'
                AFTER perdida_sensacion";

        $db->exec($sql);
        echo "Columna cambios_color agregada.\n";
    }
    echo "Migración de cuidado_pies completada.\n";

} catch (PDOException $e) {
    echo "Error en migración: " . $e->getMessage() . "\n";
}

