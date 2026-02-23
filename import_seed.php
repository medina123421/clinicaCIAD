<?php
require_once 'app/config/database.php';

try {
    $database = new Database();
    $db = $database->getConnection();

    // Leer el archivo SQL
    $sql_file = 'database/seed_demo_20_patients.sql';
    if (!file_exists($sql_file)) {
        die("Error: No se encuentra el archivo $sql_file\n");
    }

    $sql = file_get_contents($sql_file);

    // Dividir por punto y coma, pero teniendo cuidado con los procedimientos (si los hubiera)
    // Para este script simple, podemos usar exec directamente si el archivo no es Gigante, 
    // o procesar por bloques. Dado que es un seed manejable:

    echo "Iniciando importación del seed...\n";

    // Desactivar FK checks
    $db->exec("SET FOREIGN_KEY_CHECKS = 0");

    // Ejecutar el SQL completo (PDO::exec puede ejecutar múltiples sentencias si el driver lo permite)
    // Sin embargo, es más seguro separar por bloques o usar el comando de mysql con el flag de encoding.
    // Vamos a usar una técnica más robusta:

    $queries = explode(";\n", $sql);
    foreach ($queries as $query) {
        $query = trim($query);
        if (!empty($query)) {
            $db->exec($query);
        }
    }

    $db->exec("SET FOREIGN_KEY_CHECKS = 1");

    echo "¡Importación completada exitosamente en UTF-8!\n";
} catch (Exception $e) {
    echo "Error durante la importación: " . $e->getMessage() . "\n";
}
