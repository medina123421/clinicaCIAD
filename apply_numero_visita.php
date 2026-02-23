<?php
require_once 'app/config/database.php';
try {
    $database = new Database();
    $db = $database->getConnection();
    // Add numero_visita column if not exists
    $db->exec("ALTER TABLE visitas ADD COLUMN IF NOT EXISTS numero_visita VARCHAR(10) NULL AFTER tipo_visita");
    echo "OK: numero_visita column added (or already exists).";
} catch (PDOException $e) {
    echo "Error: " . $e->getMessage();
}
?>