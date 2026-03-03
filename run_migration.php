<?php
try {
    $db = new PDO('mysql:host=localhost;dbname=clinica_diabetes', 'root', '');
    $db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $sql = file_get_contents('database/migrations/add_full_proximos_estudios.sql');
    $db->exec($sql);
    echo "Migration successful\n";
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
}
?>