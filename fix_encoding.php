<?php
require_once 'app/config/database.php';

try {
    $database = new Database();
    $db = $database->getConnection();

    // Fix Usuarios
    $usuarios = [
        ['id' => 2, 'nombre' => 'Dr. Juan', 'apellido' => 'Pérez'],
        ['id' => 3, 'nombre' => 'Lic. María', 'apellido' => 'López'],
        ['id' => 4, 'nombre' => 'Psic. Ana', 'apellido' => 'Martínez']
    ];

    foreach ($usuarios as $u) {
        $stmt = $db->prepare("UPDATE usuarios SET nombre = ?, apellido_paterno = ? WHERE id_usuario = ?");
        $stmt->execute([$u['nombre'], $u['apellido'], $u['id']]);
    }

    // Fix Pacientes (Common names with accents)
    $pacientes = [
        ['id' => 4, 'nombre' => 'Lucía', 'apellido' => 'Fernández'],
        ['id' => 5, 'nombre' => 'Javier', 'apellido' => 'Ramírez'],
        ['id' => 6, 'nombre' => 'Marta', 'apellido' => 'Vázquez'],
        ['id' => 10, 'nombre' => 'Carmen', 'apellido' => 'Maldonado'], // No accents here but good to verify
        ['id' => 17, 'nombre' => 'Oscar', 'apellido' => 'Salazar'],
        ['id' => 18, 'nombre' => 'Isabel', 'apellido' => 'Nava']
    ];

    foreach ($pacientes as $p) {
        $stmt = $db->prepare("UPDATE pacientes SET nombre = ?, apellido_paterno = ? WHERE id_paciente = ?");
        $stmt->execute([$p['nombre'], $p['apellido'], $p['id']]);
    }

    echo "Encoding fixed successfully.";
} catch (Exception $e) {
    echo "Error: " . $e->getMessage();
}
