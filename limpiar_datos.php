<?php
require_once 'app/config/database.php';
$db = (new Database())->getConnection();

$tablas = [
    // Análisis clínicos
    'analisis_glucosa',
    'analisis_perfil_lipidico',
    'analisis_perfil_renal',
    'analisis_cardiovascular',
    'analisis_electrolitos',
    'analisis_hepaticos',
    'analisis_otros',
    'lab_biometria_hematica',
    'lab_quimica_sanguinea',
    'lab_examen_orina',
    'lab_perfil_hepatico',
    'lab_perfil_tiroideo',
    'lab_insulina',
    // Especialidades
    'consulta_medicina_interna',
    'consulta_nutricion',
    'consulta_psicologia',
    // Datos clínicos del paciente
    'datos_clinicos',
    'antecedentes_familiares',
    'complicaciones_macrovasculares',
    'complicaciones_microvasculares',
    'ajustes_tratamiento',
    'tratamientos',
    'glucometrias',
    'hiperglucemias',
    'hipoglucemias',
    'historia_nutricional',
    'estilo_vida',
    'salud_mental',
    'educacion_diabetes',
    'notas_consulta',
    'interpretaciones',
    'anexos',
    // Estudio socioeconómico
    'estudio_socioeconomico_familiares',
    'estudio_socioeconomico',
    // Visitas (al final por FK)
    'visitas',
];

$db->exec('SET FOREIGN_KEY_CHECKS = 0');
foreach ($tablas as $tabla) {
    $db->exec("TRUNCATE TABLE `$tabla`");
    echo "✓ $tabla limpiada\n";
}
$db->exec('SET FOREIGN_KEY_CHECKS = 1');
echo "\n¡Listo! Pacientes y usuarios conservados.\n";
?>