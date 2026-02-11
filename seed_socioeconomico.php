<?php
require_once 'app/config/database.php';
require_once 'app/models/EstudioSocioeconomico.php';

$database = new Database();
$db = $database->getConnection();
$model = new EstudioSocioeconomico($db);

$query = "SELECT id_paciente, nombre, apellido_paterno FROM pacientes LIMIT 5";
$stmt = $db->query($query);
$pacientes = $stmt->fetchAll(PDO::FETCH_ASSOC);

if (count($pacientes) < 1) {
    die("No hay pacientes registrados para llenar estudios.");
}

$sample_data = [
    [
        'escolaridad' => 'Primaria completa',
        'estado_civil' => 'Casado',
        'ocupacion' => 'Obrero',
        'religion' => 'Católico',
        'tiempo_residencia' => '15 años',
        'es_jefe_familia' => 1,
        'relaciones_familiares' => 'Armónicas',
        'apoyo_familiar' => 'Muy Alto',
        'tipo_vivienda' => 'Propia',
        'num_habitaciones' => 3,
        'material_vivienda' => 'Block y concreto',
        'servicio_agua' => 1,
        'servicio_drenaje' => 1,
        'servicio_electricidad' => 1,
        'servicio_gas' => 1,
        'servicio_internet' => 0,
        'ingreso_mensual_familiar' => 8500,
        'gasto_renta' => 0,
        'gasto_alimentos' => 4500,
        'gasto_transporte' => 800,
        'gasto_servicios' => 1200,
        'gasto_tratamientos' => 500,
        'gasto_total_estimado' => 7000,
        'apoyo_social_check' => 0,
        'ingreso_cubre_necesidades' => 1,
        'diagnostico_desc' => 'Diabetes Tipo 2, diagnósticado hace 3 años',
        'servicio_medico' => ['IMSS'],
        'tratamiento_actual' => ['Metformina'],
        'cubre_costos_medicamento' => 0,
        'cuenta_con_glucometro' => 1,
        'dificultad_dieta_economica' => 0,
        'frecuencia_alimentos' => ['carne_res' => '1 vez sem', 'pollo' => 'Diario', 'verduras' => 'Diario'],
        'observaciones_trabajo_social' => 'Paciente colaborador, vivienda en buenas condiciones.',
        'nivel_socioeconomico' => 'Bajo-Medio',
        'nombre_entrevistado' => 'Juan Pérez',
        'nombre_trabajador_social' => 'Lic. Ana Morales',
        'familiares' => [
            ['nombre' => 'Rosa Maria', 'parentesco' => 'Esposa', 'edad' => 45, 'ocupacion' => 'Hogar', 'ingreso_mensual' => 0],
            ['nombre' => 'Pedro', 'parentesco' => 'Hijo', 'edad' => 18, 'ocupacion' => 'Estudiante', 'ingreso_mensual' => 1000]
        ]
    ],
    [
        'escolaridad' => 'Secundaria trunca',
        'estado_civil' => 'Union Libre',
        'ocupacion' => 'Comerciante informal',
        'religion' => 'Ninguna',
        'tiempo_residencia' => '5 años',
        'es_jefe_familia' => 1,
        'relaciones_familiares' => 'Conflictivas',
        'apoyo_familiar' => 'Bajo',
        'tipo_vivienda' => 'Rentada',
        'num_habitaciones' => 1,
        'material_vivienda' => 'Lámina y madera',
        'servicio_agua' => 1,
        'servicio_drenaje' => 0,
        'servicio_electricidad' => 1,
        'servicio_gas' => 1,
        'servicio_internet' => 0,
        'ingreso_mensual_familiar' => 4200,
        'gasto_renta' => 1800,
        'gasto_alimentos' => 2000,
        'gasto_transporte' => 400,
        'gasto_servicios' => 300,
        'gasto_tratamientos' => 200,
        'gasto_total_estimado' => 4700,
        'apoyo_social_check' => 1,
        'apoyo_social_nombre' => 'Beca Benito Juárez',
        'ingreso_cubre_necesidades' => 0,
        'diagnostico_desc' => 'Diabetes gestacional previa, ahora Tipo 2',
        'servicio_medico' => ['No cuenta con servicio'],
        'tratamiento_actual' => ['Otro'],
        'cubre_costos_medicamento' => 1,
        'cuenta_con_glucometro' => 0,
        'dificultad_dieta_economica' => 1,
        'frecuencia_alimentos' => ['verduras' => 'Ocasional', 'cereales' => 'Diario'],
        'observaciones_trabajo_social' => 'Vulnerabilidad económica alta, requiere canalización a programas de apoyo.',
        'nivel_socioeconomico' => 'Vulnerabilidad Extrema',
        'nombre_entrevistado' => 'Maria Lopez',
        'nombre_trabajador_social' => 'Lic. Ana Morales',
        'familiares' => [
            ['nombre' => 'Luis', 'parentesco' => 'Pareja', 'edad' => 30, 'ocupacion' => 'Ayudante', 'ingreso_mensual' => 3000]
        ]
    ]
];

// Fill 3 more with generic variations
for ($i = 2; $i < 5; $i++) {
    $sample_data[$i] = $sample_data[0];
    $sample_data[$i]['nombre_entrevistado'] = "Entrevistado " . ($i + 1);
    $sample_data[$i]['ingreso_mensual_familiar'] += ($i * 500);
}

foreach ($pacientes as $index => $paciente) {
    if (isset($sample_data[$index])) {
        $data = $sample_data[$index];
        $data['id_paciente'] = $paciente['id_paciente'];
        $data['nombre_entrevistado'] = $paciente['nombre'] . ' ' . $paciente['apellido_paterno'];

        try {
            $model->guardar($data);
            echo "Estudio creado para paciente ID: " . $paciente['id_paciente'] . " (" . $paciente['nombre'] . ")\n";
        } catch (Exception $e) {
            echo "Error en paciente " . $paciente['id_paciente'] . ": " . $e->getMessage() . "\n";
        }
    }
}
?>