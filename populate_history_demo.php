<?php
/**
 * Populate History Demo
 * Genera un historial completo (Visitas, Análisis, Medicina Interna) para 5 pacientes aleatorios.
 */

require_once 'app/config/database.php';
require_once 'app/models/Visita.php';
require_once 'app/models/Analisis.php';
require_once 'app/models/MedicinaInterna.php';
require_once 'app/models/Paciente.php';

// Simular sesión para que los modelos que usan $_SESSION no fallen (aunque usamos params en métodos)
session_start();
$_SESSION['usuario_id'] = 1;

try {
    $database = new Database();
    $db = $database->getConnection();

    $visita_model = new Visita($db);
    $analisis_model = new Analisis($db);
    $mi_model = new MedicinaInterna($db);
    $paciente_model = new Paciente($db);

    echo "Iniciando generación de historial demostrativo...\n";

    // 1. Obtener 5 pacientes aleatorios
    $pacientes = $paciente_model->obtenerTodos('', 100);
    if (count($pacientes) < 5) {
        die("Error: Se necesitan al menos 5 pacientes. Ejecuta seed_pacientes.php primero.\n");
    }

    $seleccionados_keys = array_rand($pacientes, min(5, count($pacientes)));
    $seleccionados = [];
    if (is_array($seleccionados_keys)) {
        foreach ($seleccionados_keys as $key)
            $seleccionados[] = $pacientes[$key];
    } else {
        $seleccionados[] = $pacientes[$seleccionados_keys];
    }

    $total_visitas = 0;
    $total_analisis = 0;
    $total_mi = 0;

    foreach ($seleccionados as $paciente) {
        $id_paciente = $paciente['id_paciente'];
        echo "Generando datos para: " . $paciente['nombre'] . " " . $paciente['apellido_paterno'] . " (ID: $id_paciente)...\n";

        // 2. Generar Visitas Pasadas (3-6)
        $num_visitas = rand(3, 6);
        for ($i = 0; $i < $num_visitas; $i++) {
            $dias_atras = rand(1, 180);
            $fecha = date('Y-m-d H:i:s', strtotime("-{$dias_atras} days + " . rand(8, 18) . " hours"));

            // Crear Visita
            $datos_visita = [
                'id_paciente' => $id_paciente,
                'id_doctor' => 1,
                'fecha_visita' => $fecha,
                'tipo_visita' => (rand(0, 10) > 7 ? 'Primera Vez' : 'Seguimiento'),
                'motivo_consulta' => 'Consulta de control historial demo',
                'diagnostico' => 'Diabetes Tipo 2',
                'plan_tratamiento' => 'Metformina, Dieta',
                'observaciones' => 'Generado por script demo',
                'estatus' => 'Completada'
            ];

            if ($visita_model->crear($datos_visita, 1)) {
                $total_visitas++;

                // Necesitamos el ID de la visita recién creada. 
                // Como el método crear() retorna bool, buscaremos la visita por fecha y paciente para obtener su ID.
                // Esto es un workaround porque crear() no devuelve el ID.
                $query = "SELECT id_visita FROM visitas WHERE id_paciente = :p AND fecha_visita = :f LIMIT 1";
                $stmt = $db->prepare($query);
                $stmt->execute([':p' => $id_paciente, ':f' => $fecha]);
                $visita_creada = $stmt->fetch(PDO::FETCH_ASSOC);
                $id_visita = $visita_creada['id_visita'] ?? null;

                if ($id_visita) {
                    $fecha_dia = date('Y-m-d', strtotime($fecha));

                    // 3. Generar Análisis (70% prob)
                    if (rand(1, 100) <= 70) {
                        $tipos = ['BH', 'QS', 'EGO'];
                        $num_estudios = rand(1, 3);
                        $mis_estudios = array_rand(array_flip($tipos), $num_estudios);
                        if (!is_array($mis_estudios))
                            $mis_estudios = [$mis_estudios];

                        $datos_base_analisis = [
                            'id_visita' => $id_visita,
                            'fecha_analisis' => $fecha_dia,
                            'observaciones' => 'Demo Historial',
                            'created_by' => 1
                        ];

                        if (in_array('BH', $mis_estudios)) {
                            $datos_bh = array_merge($datos_base_analisis, ['hemoglobina' => rand(120, 160) / 10, 'leucocitos' => rand(5000, 10000) / 1000]);
                            $analisis_model->registrarBiometriaHematica($datos_bh);
                            $total_analisis++;
                        }
                        if (in_array('QS', $mis_estudios)) {
                            $datos_qs = array_merge($datos_base_analisis, ['glucosa' => rand(80, 150), 'colesterol' => rand(150, 240)]);
                            $analisis_model->registrarQuimicaSanguinea($datos_qs);
                            $total_analisis++;
                        }
                    }

                    // 4. Generar Reporte Medicina Interna (40% prob)
                    if (rand(1, 100) <= 40) {
                        $datos_mi = [
                            'id_paciente' => $id_paciente,
                            'id_visita' => $id_visita,
                            'fecha_registro' => $fecha_dia,
                            'tipo_diabetes' => 'Tipo 2',
                            'control_actual' => (rand(0, 1) ? 'Bueno' : 'Regular'),
                            'hta' => (rand(0, 1) ? 1 : 0),
                            'peso' => rand(60, 90),
                            'talla' => rand(150, 180),
                            'observaciones_adicionales' => 'Paciente en seguimiento demo'
                        ];
                        // Llenar booleanos requeridos con 0
                        $booleans = ['enfermedad_coronaria', 'infarto_miocardio', 'insuficiencia_cardiaca', 'dislipidemia']; // Agregamos algunos
                        foreach ($booleans as $b)
                            $datos_mi[$b] = 0;

                        $mi_model->guardar($datos_mi);
                        $total_mi++;
                    }
                }
            }
        }

        // 5. Generar Visitas Futuras (1-2)
        $num_futuras = rand(1, 2);
        for ($i = 0; $i < $num_futuras; $i++) {
            $dias_futuro = rand(1, 30);
            $fecha = date('Y-m-d H:i:s', strtotime("+{$dias_futuro} days + " . rand(9, 17) . " hours"));

            $datos_visita = [
                'id_paciente' => $id_paciente,
                'id_doctor' => 1,
                'fecha_visita' => $fecha,
                'tipo_visita' => 'Seguimiento',
                'estatus' => 'Programada',
                'motivo_consulta' => '', // Nuevo formulario simplificado permite vacíos
                'diagnostico' => '',
                'plan_tratamiento' => '',
                'observaciones' => ''
            ];

            if ($visita_model->crear($datos_visita, 1)) {
                $total_visitas++;
            }
        }
    }

    echo "\n------------------------------------------------\n";
    echo "RESUMEN DE POBLACIÓN DE DATOS\n";
    echo "------------------------------------------------\n";
    echo "Pacientes procesados: " . count($seleccionados) . "\n";
    echo "Visitas creadas: $total_visitas\n";
    echo "Registros de análisis: $total_analisis\n";
    echo "Reportes Medicina Interna: $total_mi\n";
    echo "------------------------------------------------\n";
    echo "Nombres de pacientes actualizados:\n";
    foreach ($seleccionados as $p) {
        echo "- " . $p['nombre'] . " " . $p['apellido_paterno'] . "\n";
    }

} catch (Exception $e) {
    echo "Error fatal: " . $e->getMessage();
}
