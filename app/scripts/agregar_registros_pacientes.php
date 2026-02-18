<?php
/**
 * Añade 1 visita extra + consulta Psicología + Nutrición a cada paciente
 * que ya tenga al menos una visita.
 * Ejecutar desde raíz: php app/scripts/agregar_registros_pacientes.php
 */
require_once __DIR__ . '/../config/database.php';

$db = (new Database())->getConnection();
$doctor_id = 1;

$pacientes_con_visitas = $db->query(
    "SELECT DISTINCT id_paciente FROM visitas ORDER BY id_paciente"
)->fetchAll(PDO::FETCH_COLUMN);

if (empty($pacientes_con_visitas)) {
    echo "No hay pacientes con visitas. Nada que hacer.\n";
    exit(0);
}

$escalas_beck = ['Leve', 'Moderada', 'Severa', 'N/A'];
$escalas_freq = ['Siempre', 'Casi Siempre', 'Nunca', 'Algunas Veces', 'N/A'];
$rb = fn() => $escalas_beck[array_rand($escalas_beck)];
$rf = fn() => $escalas_freq[array_rand($escalas_freq)];
$rand = fn($a, $b) => round(rand($a * 10, $b * 10) / 10, 1);

$fecha_base = date('Y-m-d', strtotime('-2 weeks')) . ' 10:00:00';
$numeros_visita = ['V3', 'V4', 'Seguimiento'];
$tipos_visita = ['Seguimiento', 'Seguimiento', 'Seguimiento'];

$insertados = 0;
foreach ($pacientes_con_visitas as $pid) {
    $fecha = date('Y-m-d H:i:s', strtotime($fecha_base . ' +' . rand(0, 14) . ' days'));
    $fecha_dt = date('Y-m-d', strtotime($fecha));
    $num_vis = $numeros_visita[array_rand($numeros_visita)];
    $tipo = $tipos_visita[array_rand($tipos_visita)];

    $stmt = $db->prepare("INSERT INTO visitas (id_paciente, id_doctor, fecha_visita, tipo_visita, numero_visita, diagnostico, observaciones, estatus, created_by) VALUES (?,?,?,?,?,?,?,?,?)");
    $stmt->execute([$pid, $doctor_id, $fecha, $tipo, $num_vis, 'Seguimiento DM2', 'Consulta Psicología y Nutrición.', 'Completada', $doctor_id]);
    $id_visita = $db->lastInsertId();

    $peso = $rand(55, 100);
    $talla = $rand(1.55, 1.85);
    $imc = round($peso / ($talla * $talla), 1);

    $db->prepare("INSERT INTO consulta_psicologia (id_visita, id_paciente, numero_visita, descripcion_paciente, v1_ansiedad_beck, v1_depresion_beck, v1_desesperanza_beck, v1_observaciones, v2_nivel_personal, v2_nivel_economico, v2_nivel_social, v2_nivel_sanitario, v2_observaciones, v3_pre_contemplacion, v3_contemplacion, v3_decision, v3_accion, v3_mantenimiento, v3_recaida, v3_observaciones, v4_logro_relajacion, v4_descripcion_paciente, v4_observaciones, v5_tristeza, v5_depresion, v5_observaciones) VALUES (?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?)")
        ->execute([$id_visita, $pid, 1, 'Seguimiento psicológico.', $rb(), $rb(), $rb(), 'Observaciones visita.', $rf(), $rf(), $rf(), $rf(), 'Limitantes valoradas.', $rf(), $rf(), $rf(), $rf(), $rf(), $rf(), 'Seguimiento motivación.', $rf(), 'Paciente en seguimiento.', 'Técnicas de relajación.', $rf(), $rf(), 'Seguimiento tristeza/depresión.']);

    $dx_nutri = json_encode(['Sobrepeso']);
    $objetivos = json_encode(['Control de peso']);
    $recom = json_encode(['Dieta fraccionada en 5 tiempos']);
    $db->prepare("INSERT INTO consulta_nutricion (id_visita, id_paciente, peso, talla, imc, circunferencia_cintura, diagnostico_nutricional, tipo_dieta, objetivos_tratamiento, recomendaciones_generales, realiza_ejercicio, ejercicio_frecuencia, ejercicio_tipo, ejercicio_duracion, tabaquismo, alcoholismo) VALUES (?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?)")
        ->execute([$id_visita, $pid, $peso, $talla, $imc, $rand(80, 110), $dx_nutri, 'Normocalórica', $objetivos, $recom, 'Si', '3 veces/semana', 'Caminata', '30 min', 'No', 'No']);

    $insertados++;
    echo "Paciente $pid: visita $id_visita ($num_vis) + psicología + nutrición\n";
}

echo "\nListo. $insertados visitas (con psicología y nutrición) añadidas.\n";
