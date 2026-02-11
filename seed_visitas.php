<?php
/**
 * Seeder de Visitas
 * Inserta visitas aleatorias para los pacientes existentes
 */

require_once 'app/config/database.php';
require_once 'app/models/Visita.php';

try {
    $database = new Database();
    $db = $database->getConnection();
    $visita_model = new Visita($db);

    // Obtener IDs de pacientes existentes
    $stmt = $db->query("SELECT id_paciente FROM pacientes WHERE activo = 1");
    $pacientes_ids = $stmt->fetchAll(PDO::FETCH_COLUMN);

    if (empty($pacientes_ids)) {
        die("Error: No hay pacientes registrados. Ejecuta primero seed_pacientes.php\n");
    }

    // Obtener ID de doctor (usamos el 1 por defecto o buscamos uno)
    $id_doctor = 1;

    echo "Iniciando inserción de visitas para " . count($pacientes_ids) . " pacientes...\n";

    $motivos = ['Control mensual', 'Dolor de cabeza frecuente', 'Examen general', 'Seguimiento de glucosa', 'Fatiga y mareos', 'Chequeo rutinario', 'Entumecimiento en pies', 'Sed excesiva'];
    $diagnosticos = ['Diabetes Tipo 2 controlada', 'Prehipertensión', 'Sin hallazgos patológicos', 'Hiperglucemia leve', 'Neuropatía diabética incipiente', 'Observación', 'Requiere ajustes en dieta'];
    $tratamientos = ['Metformina 850mg c/12h', 'Dieta y ejercicio', 'Losartán 50mg c/24h', 'Insulina Glargina 10U', 'Continuar mismo tratamiento', 'Complejo B'];

    $count = 0;

    foreach ($pacientes_ids as $id_paciente) {
        // Generar entre 1 y 4 visitas por paciente
        $num_visitas = rand(1, 4);

        for ($i = 0; $i < $num_visitas; $i++) {
            // Fecha aleatoria en los últimos 6 meses
            $days_ago = rand(0, 180);
            $fecha = date('Y-m-d H:i:s', strtotime("-{$days_ago} days + " . rand(8, 18) . " hours"));

            $estatus = ($days_ago > 0) ? 'Completada' : 'Programada';

            $datos = [
                'id_paciente' => $id_paciente,
                'id_doctor' => $id_doctor,
                'fecha_visita' => $fecha,
                'tipo_visita' => (rand(0, 10) > 7 ? 'Primera Vez' : 'Seguimiento'),
                'motivo_consulta' => $motivos[array_rand($motivos)],
                'diagnostico' => $diagnosticos[array_rand($diagnosticos)],
                'plan_tratamiento' => $tratamientos[array_rand($tratamientos)],
                'observaciones' => 'Generado automáticamente',
                'proxima_cita' => date('Y-m-d', strtotime($fecha . ' + 30 days')),
                'estatus' => $estatus
            ];

            if ($visita_model->crear($datos, 1)) {
                $count++;
            }
        }
    }

    echo "Proceso finalizado. Total visitas insertadas: $count\n";

} catch (Exception $e) {
    echo "Error fatal: " . $e->getMessage();
}
?>