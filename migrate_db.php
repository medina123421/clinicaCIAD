<?php
require_once 'app/config/database.php';

try {
    $database = new Database();
    $db = $database->getConnection();

    $sql = "ALTER TABLE pacientes 
            ADD COLUMN IF NOT EXISTS protocolo ENUM('Diabético', 'Prediabético') DEFAULT 'Diabético' AFTER alergias";

    if ($db->exec($sql) !== false) {
        echo "Protocolo column added successfully.\n";
    }
    echo "Database migrated successfully.\n";

} catch (PDOException $e) {
    echo "Error: " . $e->getMessage() . "\n";
}
?>