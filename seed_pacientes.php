<?php
/**
 * Seeder de Pacientes
 * Inserta 20 pacientes de prueba
 */

require_once 'app/config/database.php';
require_once 'app/models/Paciente.php';

// Arrays de datos para generar combinaciones aleatorias
$nombres = ['Juan', 'María', 'Pedro', 'Ana', 'Luis', 'Carmen', 'José', 'Laura', 'Miguel', 'Sofía', 'Carlos', 'Isabel', 'Jorge', 'Patricia', 'Roberto', 'Lucía', 'David', 'Elena', 'Fernando', 'Teresa'];
$apellidos = ['García', 'Martínez', 'López', 'González', 'Rodríguez', 'Fernández', 'Pérez', 'Ramírez', 'Sánchez', 'Flores', 'Rivera', 'Gómez', 'Díaz', 'Reyes', 'Morales', 'Ortiz', 'Castillo', 'Moreno', 'Romero', 'Álvarez'];
$ciudades = ['Ciudad de México', 'Monterrey', 'Guadalajara', 'Puebla', 'León', 'Tijuana', 'Mérida', 'San Luis Potosí', 'Querétaro', 'Toluca'];
$tipos_sangre = ['A+', 'A-', 'B+', 'B-', 'AB+', 'AB-', 'O+', 'O-'];
$sexos = ['M', 'F'];

try {
    $database = new Database();
    $db = $database->getConnection();
    $paciente_model = new Paciente($db);

    echo "Iniciando inserción de 20 pacientes...\n";

    $count = 0;

    for ($i = 0; $i < 20; $i++) {
        // Generar datos aleatorios
        $nombre = $nombres[array_rand($nombres)];
        $paterno = $apellidos[array_rand($apellidos)];
        $materno = $apellidos[array_rand($apellidos)];
        $sexo = $sexos[array_rand($sexos)];

        // Fecha nacimiento entre 1950 y 2000
        $year = rand(1950, 2000);
        $month = rand(1, 12);
        $day = rand(1, 28);
        $fecha_nacimiento = "$year-" . str_pad($month, 2, '0', STR_PAD_LEFT) . "-" . str_pad($day, 2, '0', STR_PAD_LEFT);

        $expediente = $paciente_model->generarNumeroExpediente();

        // Datos para el modelo
        $datos = [
            'numero_expediente' => $expediente,
            'nombre' => $nombre,
            'apellido_paterno' => $paterno,
            'apellido_materno' => $materno,
            'fecha_nacimiento' => $fecha_nacimiento,
            'sexo' => $sexo,
            'telefono' => '55' . rand(10000000, 99999999),
            'email' => strtolower($nombre . '.' . $paterno . rand(1, 99) . '@email.com'),
            'direccion' => 'Calle ' . rand(1, 100) . ' # ' . rand(1, 500),
            'ciudad' => $ciudades[array_rand($ciudades)],
            'estado' => 'México',
            'codigo_postal' => rand(10000, 99999),
            'tipo_sangre' => $tipos_sangre[array_rand($tipos_sangre)],
            'alergias' => (rand(0, 1) ? 'Ninguna' : 'Polen, Polvo')
        ];

        // Insertar (Usuario ID 1 como creador)
        if ($paciente_model->crear($datos, 1)) {
            echo "[$i] Paciente creado: $nombre $paterno ($expediente)\n";
            $count++;
        } else {
            echo "[$i] Error al crear paciente $nombre $paterno\n";
        }

        // Pequeña pausa para no duplicar timestamps o similar si fuera el caso, aunque no es necesario
        usleep(100000);
    }

    echo "\nProceso finalizado. Total insertados: $count\n";

} catch (Exception $e) {
    echo "Error fatal: " . $e->getMessage();
}
?>