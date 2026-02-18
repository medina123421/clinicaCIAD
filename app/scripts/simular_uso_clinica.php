<?php
/**
 * Simulación de uso en clínica:
 * - Para cada paciente activo, crea visitas hasta llegar a un objetivo (target).
 * - Por cada visita crea: analisis_* + lab_* + consultas (Medicina Interna / Nutrición / Psicología).
 *
 * Uso (desde la raíz del proyecto):
 *   php app/scripts/simular_uso_clinica.php --target=24
 * Opcionales:
 *   --doctor=1
 *   --dry-run=1
 */

require_once __DIR__ . '/../config/database.php';

$opt = getopt('', ['target::', 'doctor::', 'dry-run::']);
$target = max(1, (int)($opt['target'] ?? 24));
$doctorId = max(1, (int)($opt['doctor'] ?? 1));
$dryRun = isset($opt['dry-run']);

$db = (new Database())->getConnection();
$db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

$rand1 = fn($a, $b) => round(rand((int)($a * 10), (int)($b * 10)) / 10, 1);
$interp = fn($v, $lo, $hi) => $v < $lo ? 'Precaución' : ($v > $hi ? 'Alerta' : 'Normal');

$escalasBeck = ['Leve', 'Moderada', 'Severa', 'N/A'];
$escalasFreq = ['Siempre', 'Casi Siempre', 'Nunca', 'Algunas Veces', 'N/A'];
$rb = fn() => $escalasBeck[array_rand($escalasBeck)];
$rf = fn() => $escalasFreq[array_rand($escalasFreq)];

$pacientes = $db->query("SELECT id_paciente FROM pacientes WHERE activo = 1 ORDER BY id_paciente")
    ->fetchAll(PDO::FETCH_COLUMN);

if (empty($pacientes)) {
    echo "No hay pacientes activos.\n";
    exit(0);
}

echo "Simulación iniciada. Pacientes: " . count($pacientes) . " | target visitas/paciente: $target" . ($dryRun ? " | DRY-RUN\n" : "\n");

$stmtCountVisitas = $db->prepare("SELECT COUNT(*) FROM visitas WHERE id_paciente = ?");
$stmtInsertVisita = $db->prepare("INSERT INTO visitas (id_paciente, id_doctor, fecha_visita, tipo_visita, numero_visita, diagnostico, observaciones, estatus, created_by) VALUES (?,?,?,?,?,?,?,?,?)");

$stmtGlucosa = $db->prepare("INSERT INTO analisis_glucosa (id_visita, fecha_analisis, glucosa_ayunas, glucosa_postprandial_2h, hemoglobina_glicosilada, interpretacion_glucosa_ayunas, interpretacion_hba1c, observaciones, created_by) VALUES (?,?,?,?,?,?,?,?,?)");
$stmtLipidico = $db->prepare("INSERT INTO analisis_perfil_lipidico (id_visita, fecha_analisis, colesterol_total, ldl, hdl, trigliceridos, interpretacion_colesterol, interpretacion_ldl, interpretacion_hdl, interpretacion_trigliceridos, observaciones, created_by) VALUES (?,?,?,?,?,?,?,?,?,?,?,?)");
$stmtRenal = $db->prepare("INSERT INTO analisis_perfil_renal (id_visita, fecha_analisis, creatinina_serica, tasa_filtracion_glomerular, urea, bun, microalbuminuria, relacion_albumina_creatinina, interpretacion_tfg, interpretacion_microalbuminuria, observaciones, created_by) VALUES (?,?,?,?,?,?,?,?,?,?,?,?)");

$stmtBH = $db->prepare("INSERT INTO lab_biometria_hematica (id_visita, fecha_analisis, eritrocitos, hemoglobina, hematocrito, vgm, hgm, cmhg, ide, leucocitos, neutrofilos_perc, linfocitos_perc, mid_perc, neutrofilos_abs, linfocitos_abs, mid_abs, plaquetas, observaciones, created_by) VALUES (?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?)");
$stmtQS = $db->prepare("INSERT INTO lab_quimica_sanguinea (id_visita, fecha_analisis, glucosa, urea, bun, creatinina, acido_urico, colesterol, trigliceridos, observaciones, created_by) VALUES (?,?,?,?,?,?,?,?,?,?,?)");
$stmtEGO = $db->prepare("INSERT INTO lab_examen_orina (id_visita, fecha_analisis, color, aspecto, densidad, ph, leucocitos_quimico, nitritos, proteinas, glucosa_quimico, sangre_quimico, cetonas, urobilinogeno, bilirrubina, leucocitos_micro, eritrocitos_micro, bacterias, observaciones, created_by) VALUES (?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?)");
$stmtHepatico = $db->prepare("INSERT INTO lab_perfil_hepatico (id_visita, fecha_analisis, bilirrubina_total, bilirrubina_directa, bilirrubina_indirecta, alt_gpt, ast_got, fosfatasa_alcalina, ggt, proteinas_totales, albumina, globulina, observaciones, created_by) VALUES (?,?,?,?,?,?,?,?,?,?,?,?,?,?)");
$stmtTiroideo = $db->prepare("INSERT INTO lab_perfil_tiroideo (id_visita, fecha_analisis, t3_total, t3_libre, t4_total, t4_libre, tsh, observaciones, created_by) VALUES (?,?,?,?,?,?,?,?,?)");
$stmtInsulina = $db->prepare("INSERT INTO lab_insulina (id_visita, fecha_analisis, insulina_basal, observaciones, created_by) VALUES (?,?,?,?,?)");

$stmtMedInt = $db->prepare("INSERT INTO consulta_medicina_interna (id_visita, id_paciente, tipo_diabetes, anio_diagnostico, ultima_hba1c, control_actual, peso, talla, imc, presion_arterial, frecuencia_cardiaca, temperatura, glucosa_capilar, lab_glucosa_ayunas, lab_hba1c, lab_colesterol_total, lab_ldl, lab_hdl, lab_trigliceridos, lab_creatinina_serica, lab_tfg, lab_microalbuminuria_orina, observaciones_adicionales, created_by) VALUES (?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?)");
$stmtNutri = $db->prepare("INSERT INTO consulta_nutricion (id_visita, id_paciente, peso, talla, imc, circunferencia_cintura, diagnostico_nutricional, tipo_dieta, objetivos_tratamiento, recomendaciones_generales, realiza_ejercicio, ejercicio_frecuencia, ejercicio_tipo, ejercicio_duracion, tabaquismo, alcoholismo) VALUES (?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?)");
$stmtPsi = $db->prepare("INSERT INTO consulta_psicologia (id_visita, id_paciente, numero_visita, descripcion_paciente, v1_ansiedad_beck, v1_depresion_beck, v1_desesperanza_beck, v1_observaciones, v2_nivel_personal, v2_nivel_economico, v2_nivel_social, v2_nivel_sanitario, v2_observaciones, v3_pre_contemplacion, v3_contemplacion, v3_decision, v3_accion, v3_mantenimiento, v3_recaida, v3_observaciones, v4_logro_relajacion, v4_descripcion_paciente, v4_observaciones, v5_tristeza, v5_depresion, v5_observaciones) VALUES (?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?)");

$totalVisitasAgregadas = 0;
foreach ($pacientes as $pid) {
    $stmtCountVisitas->execute([$pid]);
    $existentes = (int)$stmtCountVisitas->fetchColumn();
    $faltan = max(0, $target - $existentes);

    if ($faltan === 0) {
        continue;
    }

    if (!$dryRun) {
        $db->beginTransaction();
    }

    try {
        for ($i = 0; $i < $faltan; $i++) {
            $daysAgo = rand(0, 720);
            $fechaVisita = date('Y-m-d H:i:s', strtotime("-{$daysAgo} days +" . rand(8, 18) . " hours"));
            $fechaAnalisis = date('Y-m-d', strtotime($fechaVisita));
            $tipoVisita = (rand(0, 10) > 8) ? 'Primera Vez' : 'Seguimiento';
            $numeroVisita = 'V' . min(99, ($existentes + $i + 1));

            if ($dryRun) {
                continue;
            }

            $stmtInsertVisita->execute([$pid, $doctorId, $fechaVisita, $tipoVisita, $numeroVisita, 'Diabetes Mellitus tipo 2', 'Simulación de uso clínico', 'Completada', $doctorId]);
            $idVisita = (int)$db->lastInsertId();

            // Analisis principales
            $ga = $rand1(90, 280);
            $hba1 = $rand1(5.5, 11.0);
            $col = $rand1(150, 280);
            $ldl = $rand1(70, 180);
            $hdl = $rand1(30, 70);
            $trig = $rand1(100, 400);
            $cr = $rand1(0.6, 1.3);
            $tfg = $rand1(45, 110);
            $urea = $rand1(15, 50);
            $bun = $rand1(7, 25);
            $micro = $rand1(5, 300);

            $stmtGlucosa->execute([$idVisita, $fechaAnalisis, $ga, $ga + rand(20, 60), $hba1, $interp($ga, 70, 100), $interp($hba1, 4, 7), 'Análisis simulado.', $doctorId]);
            $stmtLipidico->execute([$idVisita, $fechaAnalisis, $col, $ldl, $hdl, $trig, $interp($col, 0, 200), $interp($ldl, 0, 100), $interp($hdl, 40, 999), $interp($trig, 0, 150), 'Perfil lipídico simulado.', $doctorId]);
            $stmtRenal->execute([$idVisita, $fechaAnalisis, $cr, $tfg, $urea, $bun, $micro, round($micro / max($cr, 0.1), 1), $interp($tfg, 60, 999), $interp($micro, 0, 30), 'Perfil renal simulado.', $doctorId]);

            // Labs
            $stmtBH->execute([$idVisita, $fechaAnalisis, $rand1(3.8, 5.5), $rand1(11, 16), $rand1(33, 50), $rand1(80, 100), $rand1(26, 34), $rand1(31, 37), $rand1(11, 15), $rand1(4.5, 11), $rand1(50, 75), $rand1(20, 40), $rand1(3, 10), $rand1(2.5, 7.5), $rand1(1.0, 4.0), $rand1(0.2, 0.8), rand(150, 400), 'BH simulada.', $doctorId]);
            $stmtQS->execute([$idVisita, $fechaAnalisis, $ga, $urea, $bun, $cr, $rand1(3.0, 7.0), $col, $trig, 'QS simulada.', $doctorId]);
            $colores = ['Amarillo claro', 'Amarillo', 'Amarillo oscuro'];
            $stmtEGO->execute([$idVisita, $fechaAnalisis, $colores[array_rand($colores)], 'Transparente', $rand1(1010, 1025), $rand1(5.5, 7.5), (rand(0, 1) ? 'Negativo' : 'Positivo'), 'Negativo', (rand(0, 1) ? 'Negativo' : 'Trazas'), (rand(0, 1) ? 'Negativo' : 'Positivo'), 'Negativo', 'Negativo', 'Normal', 'Negativo', rand(0, 5), rand(0, 3), (rand(0, 1) ? 'Escasas' : 'Ausentes'), 'EGO simulado.', $doctorId]);
            $stmtHepatico->execute([$idVisita, $fechaAnalisis, $rand1(0.3, 1.5), $rand1(0.1, 0.5), $rand1(0.2, 1.0), rand(10, 50), rand(10, 45), rand(40, 130), rand(10, 60), $rand1(6.0, 8.5), $rand1(3.5, 5.0), $rand1(2.0, 3.5), 'Perfil hepático simulado.', $doctorId]);
            $stmtTiroideo->execute([$idVisita, $fechaAnalisis, $rand1(0.8, 2.0), $rand1(2.3, 4.2), $rand1(5.0, 12.0), $rand1(0.8, 1.8), $rand1(0.4, 4.5), 'Perfil tiroideo simulado.', $doctorId]);
            $stmtInsulina->execute([$idVisita, $fechaAnalisis, $rand1(5, 30), 'Insulina simulada.', $doctorId]);

            // Consultas
            $peso = $rand1(55, 100);
            $talla = $rand1(1.55, 1.85);
            $imc = round($peso / max(($talla * $talla), 0.01), 1);
            $control = (rand(0, 1) ? 'Adecuado' : 'Inadecuado');
            $stmtMedInt->execute([$idVisita, $pid, 'Tipo 2', rand(2010, 2023), $hba1, $control, $peso, $talla, $imc, rand(110, 145) . '/' . rand(70, 95), rand(60, 90), $rand1(36.5, 37.5), $ga, $ga, $hba1, $col, $ldl, $hdl, $trig, $cr, $tfg, $micro, 'Seguimiento simulado en clínica.', $doctorId]);

            $dxNutri = json_encode([rand(0, 1) ? 'Obesidad grado I' : 'Sobrepeso'], JSON_UNESCAPED_UNICODE);
            $objNutri = json_encode(['Reducción de masa corporal', 'Mejorar estado Nutricional'], JSON_UNESCAPED_UNICODE);
            $recNutri = json_encode(['Evitar Ultraprocesados', 'Evitar altos en azúcar'], JSON_UNESCAPED_UNICODE);
            $stmtNutri->execute([$idVisita, $pid, $peso, $talla, $imc, $rand1(80, 110), $dxNutri, 'Normocalórica', $objNutri, $recNutri, (rand(0, 1) ? 'Si' : 'No'), (rand(0, 1) ? '3 veces/semana' : 'Diario'), (rand(0, 1) ? 'Caminata' : 'Natación'), (rand(0, 1) ? '30 min' : '45 min'), (rand(0, 1) ? 'Si' : 'No'), (rand(0, 1) ? 'Si' : 'No')]);

            $numPsi = (($existentes + $i) % 5) + 1; // 1..5
            $stmtPsi->execute([$idVisita, $pid, $numPsi, 'Consulta psicológica simulada.', $rb(), $rb(), $rb(), 'Observaciones simuladas.', $rf(), $rf(), $rf(), $rf(), 'Limitantes simuladas.', $rf(), $rf(), $rf(), $rf(), $rf(), $rf(), 'Motivación simulada.', $rf(), 'Descripción simulada.', 'Relajación simulada.', $rf(), $rf(), 'Seguimiento emocional simulado.']);

            $totalVisitasAgregadas++;
        }

        if (!$dryRun) {
            $db->commit();
        }
    } catch (Exception $e) {
        if (!$dryRun && $db->inTransaction()) {
            $db->rollBack();
        }
        echo "Error en paciente $pid: " . $e->getMessage() . "\n";
    }
}

echo "Listo. Visitas agregadas: $totalVisitasAgregadas\n";

