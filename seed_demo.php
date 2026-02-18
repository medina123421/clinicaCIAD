<?php
/**
 * Seed de datos demo para Clinica InvestLab
 */
require_once 'app/config/database.php';
$db = (new Database())->getConnection();
$db->exec('SET FOREIGN_KEY_CHECKS = 0');

$pacientes = $db->query("SELECT id_paciente FROM pacientes WHERE activo=1 ORDER BY id_paciente")->fetchAll(PDO::FETCH_COLUMN);
$doctor_id = 1;

$numeros_visita = ['V1', 'V2'];
$tipos_visita = ['Primera Vez', 'Seguimiento'];

$fechas = ['2025-10-15 09:00:00', '2026-01-14 10:00:00'];

$rand = fn($a, $b) => round(rand($a * 10, $b * 10) / 10, 1);
$interp = fn($v, $lo, $hi) => $v < $lo ? 'Precaución' : ($v > $hi ? 'Alerta' : 'Normal');
$escalas_beck = ['Leve', 'Moderada', 'Severa', 'N/A'];
$escalas_freq = ['Siempre', 'Casi Siempre', 'Nunca', 'Algunas Veces', 'N/A'];
$rb = fn() => $escalas_beck[rand(0, 3)];
$rf = fn() => $escalas_freq[rand(0, 4)];

echo "Iniciando seed...\n\n";

foreach ($pacientes as $pid) {
    echo "Paciente $pid:\n";

    for ($v = 0; $v < 2; $v++) {
        $fecha = $fechas[$v];
        $fecha_dt = date('Y-m-d', strtotime($fecha));
        $num_vis = $numeros_visita[$v];
        $tipo = $tipos_visita[$v];

        // ── Visita ────────────────────────────────────────────────────────
        $stmt = $db->prepare("INSERT INTO visitas (id_paciente, id_doctor, fecha_visita, tipo_visita, numero_visita, diagnostico, observaciones, estatus, created_by) VALUES (?,?,?,?,?,?,?,?,?)");
        $stmt->execute([$pid, $doctor_id, $fecha, $tipo, $num_vis, 'Diabetes Mellitus tipo 2', 'Seguimiento de control glucémico.', 'Completada', $doctor_id]);
        $id_visita = $db->lastInsertId();
        echo "  Visita $id_visita ($num_vis)\n";

        // ── Glucosa ───────────────────────────────────────────────────────
        $ga = $rand(90, 280);
        $hba1 = $rand(5.5, 11.0);
        $db->prepare("INSERT INTO analisis_glucosa (id_visita, fecha_analisis, glucosa_ayunas, glucosa_postprandial_2h, hemoglobina_glicosilada, interpretacion_glucosa_ayunas, interpretacion_hba1c, observaciones, created_by) VALUES (?,?,?,?,?,?,?,?,?)")
            ->execute([$id_visita, $fecha_dt, $ga, $ga + rand(20, 60), $hba1, $interp($ga, 70, 100), $interp($hba1, 4, 7), 'Análisis registrado.', $doctor_id]);

        // ── Perfil Lipídico ───────────────────────────────────────────────
        $col = $rand(150, 280);
        $ldl = $rand(70, 180);
        $hdl = $rand(30, 70);
        $trig = $rand(100, 400);
        $db->prepare("INSERT INTO analisis_perfil_lipidico (id_visita, fecha_analisis, colesterol_total, ldl, hdl, trigliceridos, interpretacion_colesterol, interpretacion_ldl, interpretacion_hdl, interpretacion_trigliceridos, observaciones, created_by) VALUES (?,?,?,?,?,?,?,?,?,?,?,?)")
            ->execute([$id_visita, $fecha_dt, $col, $ldl, $hdl, $trig, $interp($col, 0, 200), $interp($ldl, 0, 100), $interp($hdl, 40, 999), $interp($trig, 0, 150), 'Perfil lipídico registrado.', $doctor_id]);

        // ── Perfil Renal ──────────────────────────────────────────────────
        $cr = $rand(0.6, 1.3);
        $tfg = $rand(45, 110);
        $urea = $rand(15, 50);
        $bun = $rand(7, 25);
        $micro = $rand(5, 300);
        $db->prepare("INSERT INTO analisis_perfil_renal (id_visita, fecha_analisis, creatinina_serica, tasa_filtracion_glomerular, urea, bun, microalbuminuria, relacion_albumina_creatinina, interpretacion_tfg, interpretacion_microalbuminuria, observaciones, created_by) VALUES (?,?,?,?,?,?,?,?,?,?,?,?)")
            ->execute([$id_visita, $fecha_dt, $cr, $tfg, $urea, $bun, $micro, round($micro / $cr, 1), $interp($tfg, 60, 999), $interp($micro, 0, 30), 'Perfil renal registrado.', $doctor_id]);

        // ── Biometría Hemática ────────────────────────────────────────────
        $db->prepare("INSERT INTO lab_biometria_hematica (id_visita, fecha_analisis, eritrocitos, hemoglobina, hematocrito, vgm, hgm, cmhg, ide, leucocitos, neutrofilos_perc, linfocitos_perc, mid_perc, neutrofilos_abs, linfocitos_abs, mid_abs, plaquetas, observaciones, created_by) VALUES (?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?)")
            ->execute([$id_visita, $fecha_dt, $rand(3.8, 5.5), $rand(11, 16), $rand(33, 50), $rand(80, 100), $rand(26, 34), $rand(31, 37), $rand(11, 15), $rand(4.5, 11), $rand(50, 75), $rand(20, 40), $rand(3, 10), $rand(2.5, 7.5), $rand(1.0, 4.0), $rand(0.2, 0.8), rand(150, 400), 'Biometría registrada.', $doctor_id]);

        // ── Química Sanguínea ─────────────────────────────────────────────
        $db->prepare("INSERT INTO lab_quimica_sanguinea (id_visita, fecha_analisis, glucosa, urea, bun, creatinina, acido_urico, colesterol, trigliceridos, observaciones, created_by) VALUES (?,?,?,?,?,?,?,?,?,?,?)")
            ->execute([$id_visita, $fecha_dt, $ga, $urea, $bun, $cr, $rand(3.0, 7.0), $col, $trig, 'Química sanguínea registrada.', $doctor_id]);

        // ── Examen de Orina ───────────────────────────────────────────────
        $colores = ['Amarillo claro', 'Amarillo', 'Amarillo oscuro'];
        $db->prepare("INSERT INTO lab_examen_orina (id_visita, fecha_analisis, color, aspecto, densidad, ph, leucocitos_quimico, nitritos, proteinas, glucosa_quimico, sangre_quimico, cetonas, urobilinogeno, bilirrubina, leucocitos_micro, eritrocitos_micro, bacterias, observaciones, created_by) VALUES (?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?)")
            ->execute([$id_visita, $fecha_dt, $colores[rand(0, 2)], 'Transparente', $rand(1010, 1025), $rand(5.5, 7.5), rand(0, 1) ? 'Negativo' : 'Positivo', 'Negativo', rand(0, 1) ? 'Negativo' : 'Trazas', rand(0, 1) ? 'Negativo' : 'Positivo', 'Negativo', 'Negativo', 'Normal', 'Negativo', rand(0, 5), rand(0, 3), rand(0, 1) ? 'Escasas' : 'Ausentes', 'Examen de orina registrado.', $doctor_id]);

        // ── Perfil Hepático ───────────────────────────────────────────────
        $db->prepare("INSERT INTO lab_perfil_hepatico (id_visita, fecha_analisis, bilirrubina_total, bilirrubina_directa, bilirrubina_indirecta, alt_gpt, ast_got, fosfatasa_alcalina, ggt, proteinas_totales, albumina, globulina, observaciones, created_by) VALUES (?,?,?,?,?,?,?,?,?,?,?,?,?,?)")
            ->execute([$id_visita, $fecha_dt, $rand(0.3, 1.5), $rand(0.1, 0.5), $rand(0.2, 1.0), rand(10, 50), rand(10, 45), rand(40, 130), rand(10, 60), $rand(6.0, 8.5), $rand(3.5, 5.0), $rand(2.0, 3.5), 'Perfil hepático registrado.', $doctor_id]);

        // ── Perfil Tiroideo ───────────────────────────────────────────────
        $db->prepare("INSERT INTO lab_perfil_tiroideo (id_visita, fecha_analisis, t3_total, t3_libre, t4_total, t4_libre, tsh, observaciones, created_by) VALUES (?,?,?,?,?,?,?,?,?)")
            ->execute([$id_visita, $fecha_dt, $rand(0.8, 2.0), $rand(2.3, 4.2), $rand(5.0, 12.0), $rand(0.8, 1.8), $rand(0.4, 4.5), 'Perfil tiroideo registrado.', $doctor_id]);

        // ── Insulina ──────────────────────────────────────────────────────
        $db->prepare("INSERT INTO lab_insulina (id_visita, fecha_analisis, insulina_basal, observaciones, created_by) VALUES (?,?,?,?,?)")
            ->execute([$id_visita, $fecha_dt, $rand(5, 30), 'Insulina basal registrada.', $doctor_id]);

        // ── Consulta Medicina Interna ─────────────────────────────────────
        $peso = $rand(55, 100);
        $talla = $rand(1.55, 1.85);
        $imc = round($peso / ($talla * $talla), 1);
        $db->prepare("INSERT INTO consulta_medicina_interna (id_visita, id_paciente, tipo_diabetes, anio_diagnostico, ultima_hba1c, control_actual, peso, talla, imc, presion_arterial, frecuencia_cardiaca, temperatura, glucosa_capilar, lab_glucosa_ayunas, lab_hba1c, lab_colesterol_total, lab_ldl, lab_hdl, lab_trigliceridos, lab_creatinina_serica, lab_tfg, lab_microalbuminuria_orina, observaciones_adicionales, created_by) VALUES (?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?)")
            ->execute([$id_visita, $pid, 'Tipo 2', rand(2010, 2023), $hba1, rand(0, 1) ? 'Adecuado' : 'Inadecuado', $peso, $talla, $imc, rand(110, 145) . '/' . rand(70, 95), rand(60, 90), $rand(36.5, 37.5), $ga, $ga, $hba1, $col, $ldl, $hdl, $trig, $cr, $tfg, $micro, 'Paciente con DM2 en seguimiento. Control glucémico en proceso de optimización.', $doctor_id]);

        // ── Consulta Nutrición ────────────────────────────────────────────
        $db->prepare("INSERT INTO consulta_nutricion (id_visita, id_paciente, peso, talla, imc, circunferencia_cintura, diagnostico_nutricional, tipo_dieta, objetivos_tratamiento, recomendaciones_generales, realiza_ejercicio, ejercicio_frecuencia, ejercicio_tipo, ejercicio_duracion, tabaquismo, alcoholismo) VALUES (?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?)")
            ->execute([$id_visita, $pid, $peso, $talla, $imc, rand(80, 110), rand(0, 1) ? 'Obesidad grado I' : 'Sobrepeso', 'Hipocalórica', 'Reducción de peso, control glucémico', 'Dieta fraccionada en 5 tiempos. Restricción de azúcares simples y grasas saturadas.', rand(0, 1) ? 'Sí' : 'No', rand(0, 1) ? '3 veces/semana' : 'Diario', rand(0, 1) ? 'Caminata' : 'Natación', rand(0, 1) ? '30 min' : '45 min', rand(0, 1) ? 'Sí' : 'No', rand(0, 1) ? 'Sí' : 'No']);

        // ── Consulta Psicología ───────────────────────────────────────────
        $db->prepare("INSERT INTO consulta_psicologia (id_visita, id_paciente, numero_visita, descripcion_paciente, v1_ansiedad_beck, v1_depresion_beck, v1_desesperanza_beck, v1_observaciones, v2_nivel_personal, v2_nivel_economico, v2_nivel_social, v2_nivel_sanitario, v2_observaciones, v3_pre_contemplacion, v3_contemplacion, v3_decision, v3_accion, v3_mantenimiento, v3_recaida, v3_observaciones, v4_logro_relajacion, v4_descripcion_paciente, v4_observaciones, v5_tristeza, v5_depresion, v5_observaciones) VALUES (?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?)")
            ->execute([$id_visita, $pid, $v + 1, 'Paciente en proceso de adaptación emocional al diagnóstico de DM2.', $rb(), $rb(), $rb(), 'Proceso de duelo ante el diagnóstico.', $rf(), $rf(), $rf(), $rf(), 'Limitantes identificadas.', $rf(), $rf(), $rf(), $rf(), $rf(), $rf(), 'Paciente en etapa de contemplación.', $rf(), 'Refiere dificultad para practicar técnicas en casa.', 'Se practicaron técnicas de respiración diafragmática.', $rf(), $rf(), 'Episodios ocasionales de tristeza relacionados al diagnóstico.']);

        // ── Estudio Socioeconómico (solo primera visita) ──────────────────
        if ($v === 0) {
            $escolaridades = ['Primaria', 'Secundaria', 'Preparatoria', 'Universidad', 'Posgrado'];
            $ocupaciones = ['Empleado', 'Comerciante', 'Ama de casa', 'Jubilado', 'Estudiante'];
            $estados_civil = ['Soltero', 'Casado', 'Divorciado', 'Viudo', 'Unión libre'];
            $db->prepare("INSERT INTO estudio_socioeconomico (id_paciente, escolaridad, estado_civil, ocupacion, ingreso_mensual_familiar, num_habitaciones, tipo_vivienda, material_vivienda, servicio_agua, servicio_drenaje, servicio_electricidad, servicio_gas, servicio_internet, apoyo_familiar, relaciones_familiares, diagnostico_desc, nivel_socioeconomico, observaciones_trabajo_social) VALUES (?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?)")
                ->execute([$pid, $escolaridades[rand(0, 4)], $estados_civil[rand(0, 4)], $ocupaciones[rand(0, 4)], rand(3000, 15000), rand(2, 5), rand(0, 1) ? 'Propia' : 'Rentada', rand(0, 1) ? 'Tabique' : 'Block', 'Sí', 'Sí', 'Sí', rand(0, 1) ? 'Sí' : 'No', rand(0, 1) ? 'Sí' : 'No', rand(0, 1) ? 'Bueno' : 'Regular', rand(0, 1) ? 'Funcional' : 'Disfuncional', 'Diabetes Mellitus tipo 2', rand(0, 1) ? 'Medio' : 'Bajo', 'Estudio socioeconómico realizado en primera visita.']);
        }
    }
    echo "  ✓ Completado\n";
}

$db->exec('SET FOREIGN_KEY_CHECKS = 1');
echo "\n¡Seed completado! " . count($pacientes) . " pacientes, " . (count($pacientes) * 2) . " visitas.\n";
?>