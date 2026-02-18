<?php
require_once 'app/config/database.php';
$db = (new Database())->getConnection();

$tables = ['anexos'];

foreach ($tables as $t) {
    echo "TABLE: $t\n";
    try {
        $stmt = $db->query("DESCRIBE $t");
        $cols = $stmt->fetchAll(PDO::FETCH_ASSOC);
        foreach ($cols as $c) {
            echo " - " . $c['Field'] . " (" . $c['Type'] . ")\n";
        }
    } catch (Exception $e) {
        echo "Error: " . $e->getMessage() . "\n";
    }
    echo "\n";
}
