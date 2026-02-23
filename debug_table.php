<?php
require_once 'app/config/database.php';
try {
    $database = new Database();
    $db = $database->getConnection();
    $stmt = $db->query("DESCRIBE pacientes");
    while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
        echo "Field: " . $row['Field'] . " | Type: " . $row['Type'] . " | Null: " . $row['Null'] . " | Key: " . $row['Key'] . " | Default: " . $row['Default'] . " | Extra: " . $row['Extra'] . "\n";
    }
} catch (Exception $e) {
    echo "Error: " . $e->getMessage();
}
