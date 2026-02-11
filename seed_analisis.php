<?php
/**
 * Seeder de Análisis Clínicos
 * Inserta 10 reportes de prueba con datos aleatorios
 */

require_once 'app/config/database.php';
require_once 'app/models/Analisis.php';
require_once 'app/models/Paciente.php';

// Iniciamos sesión simulada para que no falle si algo requiere $_SESSION (aunque el modelo usa params)
session_start();
$_SESSION['usuario_id'] = 1;

try {
    $database = new Database();
    $db = $database->getConnection();
    $analisis_model = new Analisis($db);
    $paciente_model = new Paciente($db);

    echo "Iniciando generación de 10 reportes de análisis...\n";

    // Obtener algunos pacientes
    $pacientes = $paciente_model->obtenerTodos('', 50);
    if (empty($pacientes)) {
        die("Error: No hay pacientes en la base de datos. Ejecuta seed_pacientes.php primero.\n");
    }

    $count = 0;

    for ($i = 0; $i < 10; $i++) {
        $paciente = $pacientes[array_rand($pacientes)];
        $id_paciente = $paciente['id_paciente'];

        // Fecha aleatoria en los últimos 30 días
        $dias_atras = rand(0, 30);
        $fecha_analisis = date('Y-m-d', strtotime("-$dias_atras days"));

        // 1. Obtener/Crear Visita
        // Simulamos que el usuario 1 (Admin) lo crea
        $id_visita = $analisis_model->obtenerOCrearVisita($id_paciente, $fecha_analisis, 1);

        if (!$id_visita) {
            echo "Error al crear visita para Paciente ID $id_paciente en $fecha_analisis\n";
            continue;
        }

        $datos_base = [
            'id_visita' => $id_visita,
            'fecha_analisis' => $fecha_analisis,
            'observaciones' => 'Generado automáticamente por seeder',
            'created_by' => 1
        ];

        // Decidir qué estudios crear para esta visita (puede ser 1, 2 o los 3)
        // 1 = Bio, 2 = Quim, 3 = Orina
        $tipos = [];
        $num_estudios = rand(1, 3);
        $mis_estudios = array_rand([1 => 'BH', 2 => 'QS', 3 => 'EGO'], $num_estudios);
        if (!is_array($mis_estudios))
            $mis_estudios = [$mis_estudios];

        $registrado = [];

        // BIOMETRÍA HEMÁTICA
        if (in_array(1, $mis_estudios)) {
            $datos_bh = array_merge($datos_base, [
                'eritrocitos' => rand(400, 600) / 100, // 4.00 - 6.00
                'hemoglobina' => rand(120, 170) / 10,  // 12.0 - 17.0
                'hematocrito' => rand(360, 500) / 10,  // 36.0 - 50.0
                'vgm' => rand(800, 1000) / 10,
                'hgm' => rand(270, 320) / 10,
                'cmhg' => rand(320, 360) / 10,
                'ide' => rand(110, 150) / 10,
                'leucocitos' => rand(4000, 11000) / 1000, // 4.0 - 11.0
                'neutrofilos_perc' => rand(40, 70),
                'linfocitos_perc' => rand(20, 40),
                'mid_perc' => rand(1, 10),
                'neutrofilos_abs' => rand(2000, 7000) / 1000,
                'linfocitos_abs' => rand(1000, 4000) / 1000,
                'mid_abs' => rand(100, 800) / 1000,
                'plaquetas' => rand(150, 450)
            ]);
            if ($analisis_model->registrarBiometriaHematica($datos_bh))
                $registrado[] = "Biometría";
        }

        // QUÍMICA SANGUÍNEA
        if (in_array(2, $mis_estudios)) {
            $datos_qs = array_merge($datos_base, [
                'glucosa' => rand(70, 140),
                'urea' => rand(15, 45),
                'bun' => rand(7, 20),
                'creatinina' => rand(6, 13) / 10,
                'acido_urico' => rand(30, 70) / 10,
                'colesterol' => rand(150, 250),
                'trigliceridos' => rand(100, 300)
            ]);
            if ($analisis_model->registrarQuimicaSanguinea($datos_qs))
                $registrado[] = "Química";
        }

        // EXAMEN ORINA
        if (in_array(3, $mis_estudios)) {
            $colores = ['Amarillo', 'Ámbar', 'Transparente'];
            $aspectos = ['Limpio', 'Ligero Turbio', 'Turbio'];
            $datos_ego = array_merge($datos_base, [
                'color' => $colores[array_rand($colores)],
                'aspecto' => $aspectos[array_rand($aspectos)],
                'densidad' => '1.0' . rand(10, 30),
                'ph' => rand(50, 80) / 10,
                'leucocitos_quimico' => 'Negativo',
                'nitritos' => 'Negativo',
                'proteinas' => 'Negativo',
                'glucosa_quimico' => 'Normal',
                'sangre_quimico' => 'Negativo',
                'cetonas' => 'Negativo',
                'urobilinogeno' => 'Normal',
                'bilirrubina' => 'Negativo',
                'celulas_escamosas' => 'Escasas',
                'celulas_cilindricas' => 'No se observan',
                'celulas_urotelio' => 'No se observan',
                'celulas_renales' => 'No se observan',
                'leucocitos_micro' => rand(0, 5) . ' x campo',
                'cilindros' => 'No se observan',
                'eritrocitos_micro' => rand(0, 2) . ' x campo',
                'dismorficos' => 'No',
                'bacterias' => 'Escasas',
                'hongos' => 'No',
                'parasitos' => 'No'
            ]);
            if ($analisis_model->registrarExamenOrina($datos_ego))
                $registrado[] = "Orina";
        }

        echo "[$i] Reporte generado para " . $paciente['nombre'] . ": " . implode(', ', $registrado) . "\n";
        $count++;
    }

    echo "\nProceso finalizado. Se generaron $count reportes.\n";

} catch (Exception $e) {
    echo "Error fatal: " . $e->getMessage();
}
?>