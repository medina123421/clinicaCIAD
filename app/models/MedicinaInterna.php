<?php
/**
 * Modelo de Medicina Interna
 * Manejo de datos de la consulta de Medicina Interna
 */

class MedicinaInterna
{
    private $conn;
    private $table_name = "consulta_medicina_interna";

    public function __construct($db)
    {
        $this->conn = $db;
    }

    public function obtenerPorVisita($id_visita)
    {
        $query = "SELECT * FROM " . $this->table_name . " WHERE id_visita = :id_visita LIMIT 1";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':id_visita', $id_visita, PDO::PARAM_INT);
        $stmt->execute();

        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    /**
     * Obtener última consulta por ID de paciente
     * @param int $id_paciente
     * @return array|false
     */
    public function obtenerPorPaciente($id_paciente)
    {
        $query = "SELECT * FROM " . $this->table_name . " WHERE id_paciente = :id_paciente ORDER BY fecha_registro DESC LIMIT 1";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':id_paciente', $id_paciente, PDO::PARAM_INT);
        $stmt->execute();

        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    /**
     * Obtener consulta específica por ID
     * @param int $id_medicina_interna
     * @return array|false
     */
    public function obtenerPorId($id_medicina_interna)
    {
        $query = "SELECT * FROM " . $this->table_name . " WHERE id_medicina_interna = :id LIMIT 1";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':id', $id_medicina_interna, PDO::PARAM_INT);
        $stmt->execute();

        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    /**
     * Guardar (Crear o Actualizar) consulta
     * @param array $datos
     * @return bool
     */
    public function guardar($datos)
    {
        // Si hay un ID de medicina interna específico, es una actualización
        $id_medicina_interna = $datos['id_medicina_interna'] ?? null;
        $existing = null;

        if ($id_medicina_interna) {
            $existing = $this->obtenerPorId($id_medicina_interna);
        } elseif (!empty($datos['id_visita'])) {
            $existing = $this->obtenerPorVisita($datos['id_visita']);
        }

        // Asegurar que fecha_registro esté presente en nuevos registros
        if (!$existing && empty($datos['fecha_registro'])) {
            $datos['fecha_registro'] = date('Y-m-d H:i:s');
        }

        // Lista completa de campos
        $fields = [
            'id_paciente',
            'id_visita',
            'fecha_registro',
            'tipo_diabetes',
            'anio_diagnostico',
            'ultima_hba1c',
            'control_actual',
            'hta',
            'enfermedad_coronaria',
            'infarto_miocardio',
            'insuficiencia_cardiaca',
            'dislipidemia',
            'enf_vascular_periferica',
            'retinopatia_diabetica',
            'nefropatia_diabetica',
            'neuropatia_periferica',
            'neuropatia_autonomica',
            'infecciones_urinarias',
            'pie_diabetico',
            'infecciones_piel',
            'tuberculosis',
            'hepatitis_b_c',
            'obesidad',
            'enfermedad_tiroidea',
            'sindrome_metabolico',
            'insuficiencia_renal_cronica',
            'proteinuria',
            'nefrolitiasis',
            'higado_graso',
            'pancreatitis',
            'gastroparesia',
            'evc',
            'neuropatia_periferica_previa',
            'amputaciones',
            'depresion',
            'ansiedad',
            'trastornos_sueno',
            'alergias_check',
            'detalle_alergias',
            'enfermedades_cronicas_check',
            'detalle_enfermedades_cronicas',
            'cirugias_previas_check',
            'detalle_cirugias_previas',
            'hospitalizaciones_previas_check',
            'detalle_hospitalizaciones_previas',

            // Medicación Detallada
            'med_metformina',
            'med_sulfonilureas_glibenclamida',
            'med_sulfonilureas_glimepirida',
            'med_sulfonilureas_gliclazida',
            'med_meglitinidas_repaglinida',
            'med_meglitinidas_nateglinida',
            'med_inhibidores_alfaglucosidasa_acarbosa',
            'med_inhibidores_alfaglucosidasa_miglitol',
            'med_tiazolidinedionas_pioglitazona',
            'med_tiazolidinedionas_rosiglitazona',
            'med_inhibidores_dpp4_sitagliptina',
            'med_inhibidores_dpp4_saxaglipina',
            'med_inhibidores_dpp4_linagliptina',
            'med_inhibidores_dpp4_alogliptina',
            'med_agonistas_glp1_exenatida',
            'med_agonistas_glp1_liraglutida',
            'med_agonistas_glp1_dulaglutida',
            'med_agonistas_glp1_lixisenatida',
            'med_agonistas_glp1_semaglutida',
            'med_inhibidores_sglt2_empagliflozina',
            'med_inhibidores_sglt2_dapagliflozina',
            'med_inhibidores_sglt2_canagliflozina',
            'med_inhibidores_sglt2_ertugliflozina',

            // Insulinas
            'ins_rapida_regular',
            'ins_ultrarrapida_lispro',
            'ins_ultrarrapida_aspart',
            'ins_ultrarrapida_glulisina',
            'ins_intermedia_nph',
            'ins_prolongada_glargina',
            'ins_prolongada_detemir',
            'ins_prolongada_degludec',
            'ins_ultralarga_degludec',
            'ins_ultralarga_glargina_u300',
            'ins_mezcla_nph_regular',
            'ins_mezcla_lispro',
            'ins_mezcla_aspart',

            'med_estatinas',
            'med_antihipertensivos',
            'med_antiagregantes',
            'detalles_medicacion',

            // Laboratorios
            'lab_glucosa_ayunas',
            'lab_glucosa_postprandial',
            'lab_hba1c',
            'lab_curva_tolerancia',
            'lab_creatinina_serica',
            'lab_tfg',
            'lab_urea_bun',
            'lab_microalbuminuria_orina',
            'lab_relacion_acr',
            'lab_ego',
            'lab_colesterol_total',
            'lab_ldl',
            'lab_hdl',
            'lab_trigliceridos',
            'lab_sodio',
            'lab_potasio',
            'lab_cloro',
            'lab_bicarbonato',
            'lab_calcio',
            'lab_fosforo',
            'lab_magnesio',
            'lab_gasometria',
            'lab_alt',
            'lab_ast',
            'lab_fosfatasa_alcalina',
            'lab_bilirrubinas',
            'lab_albumina_serica',
            'lab_cetonas',
            'lab_peptido_c',
            'lab_insulinemia',
            'lab_pcr',
            'lab_vsg',
            'lab_troponina',
            'lab_bnp',
            'lab_homocisteina',
            'lab_vitamina_d',
            'lab_hormonas_tiroideas',
            'lab_hemograma',

            // Signos y Control
            'peso',
            'talla',
            'imc',
            'circunferencia_abdominal',
            'presion_arterial',
            'frecuencia_cardiaca',
            'temperatura',
            'frecuencia_respiratoria',
            'glucosa_capilar',
            'control_hba1c_reciente_valor',
            'control_bitacora',
            'control_hipoglucemias',
            'control_hipoglucemias_detalles',
            'control_hiperglucemias_sintomaticas',
            'control_adherencia',
            'control_problemas_medicamentos',
            'control_tecnica_insulina',
            'control_glucemia_hb1ac_reciente',
            'control_glucemia_glucometrias_diarias',
            'control_glucemia_hipoglucemias_recientes',
            'control_glucemia_hiperglucemias_sintomaticas',
            'control_glucemia_cambios_medicamentos',
            'control_glucemia_aplicacion_insulina_adecuada',

            // Revisiones
            'rev_pies_detalles',
            'rev_neuropatia_monofilamento',
            'rev_renal_laboratorios',
            'rev_vision_borrosa',
            'rev_macro_coronaria',
            'rev_macro_claudicacion',
            'rev_riesgo_cv',
            'med_revision_completa',
            'med_ajuste_orales',
            'med_ajuste_insulina',
            'med_ajuste_estatina_hta',
            'med_evaluar_cambio',
            'prog_estudios_pendientes',

            'alimentacion_adecuada',
            'actividad_fisica',
            'consumo_alcohol',
            'tabaquismo',
            'horarios_comida_regulares',
            'educacion_diabetologica',
            'tecnica_insulina',
            'revision_sitio_inyeccion',
            'prevencion_hipoglucemia',
            'cuidado_pies',
            'revision_metas',
            'sintomas_ansiedad_depresion',
            'estres_enfermedad',
            'apoyo_familiar_social',
            'observaciones_adicionales',

            // Próximos Estudios (Laboratorio)
            'prox_bh',
            'prox_osc',
            'prox_ego',
            'prox_hba1c',
            'prox_perfil_tiroideo',
            'prox_perfil_hepatico',
            'prox_insulina_basal',
            'prox_qs3_i',
            'prox_qs3_ii',
            'prox_glucosa_ayunas',
            'prox_glucosa_postprandial',
            'prox_curva_tolerancia',
            'prox_creatinina',
            'prox_tfg',
            'prox_urea_bun',
            'prox_microalbuminuria',
            'prox_acr',
            'prox_colesterol_total',
            'prox_colesterol_ldl',
            'prox_colesterol_hdl',
            'prox_trigliceridos',
            'prox_sodio',
            'prox_potasio',
            'prox_cloro',
            'prox_bicarbonato',
            'prox_calcio',
            'prox_fosforo',
            'prox_magnesio',
            'prox_gasometria',
            'prox_alt_gpt',
            'prox_ast_got',
            'prox_fosfatasa_alcalina',
            'prox_bilirrubinas',
            'prox_albumina_serica',
            'prox_cetonas',
            'prox_pcr',
            'prox_vsg',
            'prox_peptido_c',
            'prox_insulinemia',
            'prox_vitamina_d',
            'prox_troponina',
            'prox_bnp',
            'prox_homocisteina',

            // Próximos Estudios (Radiología)
            'prox_rad_craneo',
            'prox_rad_senos',
            'prox_rad_perfilograma',
            'prox_rad_cuello',
            'prox_rad_col_cervical',
            'prox_rad_col_dorsal',
            'prox_rad_col_lumbar',
            'prox_rad_col_oblicuas',
            'prox_rad_col_oblicuas_desc',
            'prox_rad_col_dinamicas',
            'prox_rad_col_dinamicas_desc',
            'prox_rad_torax_pa',
            'prox_rad_torax_lat',
            'prox_rad_torax_oseo_ap',
            'prox_rad_torax_oseo_obl',
            'prox_rad_abd_supino',
            'prox_rad_abd_bipe',
            'prox_rad_escanometria',
            'prox_rad_huesos',
            'prox_rad_huesos_desc',
            'prox_rad_cefalopelvi',

            // Próximos Estudios (Contrastados)
            'prox_con_esofagograma',
            'prox_con_serie_egd',
            'prox_con_transito_int',
            'prox_con_colon_enema',
            'prox_con_colangio_t',
            'prox_con_urograma',
            'prox_con_cistograma',
            'prox_con_uretrograma',

            // Próximos Estudios (Ultrasonido)
            'prox_us_abd_completo',
            'prox_us_higado_vias',
            'prox_us_renal_vias',
            'prox_us_pelvico_gin',
            'prox_us_gin_endovag',
            'prox_us_prostatico',
            'prox_us_pros_transrectal',
            'prox_us_testicular',
            'prox_us_inguinal',
            'prox_us_cuello_tiroides',
            'prox_us_transfontanelar',
            'prox_us_obstetrico',
            'prox_us_obstetrico_4d',
            'prox_us_obstetrico_doppler',
            'prox_us_perfil_biofisico',
            'prox_us_doppler_obs',
            'prox_us_doppler_carotida',
            'prox_us_doppler_venoso_art',
            'prox_us_doppler_ext_venoso_art',
            'prox_us_doppler_abd_renal',
            'prox_us_doppler_testicular',
            'prox_us_mamario',
            'prox_us_tejidos_blandos',
            'prox_us_musculoesqueletico',
            'prox_us_prueba_boyden',
            'prox_us_seguimientos_foli',

            // Próximos Estudios (Tomografía)
            'prox_tac_craneo',
            'prox_tac_silla_turca',
            'prox_tac_senos',
            'prox_tac_orbitas_oidos',
            'prox_tac_macizo_3d',
            'prox_tac_cuello',
            'prox_tac_col_cervical',
            'prox_tac_col_dorsal',
            'prox_tac_col_lumbar',
            'prox_tac_torax_ar',
            'prox_tac_abdominopelvica',
            'prox_tac_toracoabdominopelvica',
            'prox_tac_urotac',
            'prox_tac_cadera_3d',
            'prox_tac_hombro',
            'prox_tac_codo',
            'prox_tac_muneca',
            'prox_tac_rodilla',
            'prox_tac_tobillo',
            'prox_tac_otra',
            'prox_tac_angio_carotidas',
            'prox_tac_angio_aorta',
            'prox_tac_angio_pulmonar',
            'prox_tac_angio_abdominal',
            'prox_tac_angio_renal',
            'prox_tac_angio_ext_sup',
            'prox_tac_angio_ext_inf',
            'prox_tac_simple',
            'prox_tac_contraste'
        ];

        $data_to_bind = [];
        $booleans = $this->getBooleanFieldList();

        foreach ($fields as $field) {
            if (in_array($field, $booleans)) {
                $data_to_bind[$field] = (isset($datos[$field]) && ($datos[$field] == '1' || $datos[$field] == 'on')) ? 1 : 0;
            } else {
                $data_to_bind[$field] = $datos[$field] ?? null;
            }
        }

        // Limpieza de datos numéricos (convertir strings vacíos a null)
        $numeric_fields = ['id_visita', 'anio_diagnostico', 'ultima_hba1c', 'peso', 'talla', 'imc', 'circunferencia_abdominal', 'frecuencia_cardiaca', 'temperatura', 'frecuencia_respiratoria', 'glucosa_capilar'];
        foreach ($numeric_fields as $num_field) {
            if (isset($data_to_bind[$num_field]) && $data_to_bind[$num_field] === '') {
                $data_to_bind[$num_field] = null;
            }
        }

        if ($existing) {
            $set_parts = [];
            foreach ($data_to_bind as $key => $value) {
                if ($key === 'id_visita')
                    continue;
                $set_parts[] = "$key = :$key";
            }
            $query = "UPDATE " . $this->table_name . " SET " . implode(', ', $set_parts) . " WHERE id_medicina_interna = :id_medicina_interna";
            $data_to_bind['id_medicina_interna'] = $existing['id_medicina_interna'];
        } else {
            $cols = implode(', ', array_keys($data_to_bind));
            $params = ':' . implode(', :', array_keys($data_to_bind));
            $query = "INSERT INTO " . $this->table_name . " ($cols) VALUES ($params)";
        }

        $stmt = $this->conn->prepare($query);
        foreach ($data_to_bind as $key => $value) {
            // Solo bindear si el placeholder existe en la query
            if (strpos($query, ":$key") !== false) {
                $stmt->bindValue(":$key", $value);
            }
        }

        return $stmt->execute();
    }

    /**
     * Lista de campos tipo checkbox/booleano
     */
    private function getBooleanFieldList()
    {
        return [
            'hta',
            'enfermedad_coronaria',
            'infarto_miocardio',
            'insuficiencia_cardiaca',
            'dislipidemia',
            'enf_vascular_periferica',
            'retinopatia_diabetica',
            'nefropatia_diabetica',
            'neuropatia_periferica',
            'neuropatia_autonomica',
            'infecciones_urinarias',
            'pie_diabetico',
            'infecciones_piel',
            'tuberculosis',
            'hepatitis_b_c',
            'obesidad',
            'enfermedad_tiroidea',
            'sindrome_metabolico',
            'insuficiencia_renal_cronica',
            'proteinuria',
            'nefrolitiasis',
            'higado_graso',
            'pancreatitis',
            'gastroparesia',
            'evc',
            'neuropatia_periferica_previa',
            'amputaciones',
            'depresion',
            'ansiedad',
            'trastornos_sueno',
            'alergias_check',
            'enfermedades_cronicas_check',
            'cirugias_previas_check',
            'hospitalizaciones_previas_check',

            // Meds
            'med_metformina',
            'med_sulfonilureas_glibenclamida',
            'med_sulfonilureas_glimepirida',
            'med_sulfonilureas_gliclazida',
            'med_meglitinidas_repaglinida',
            'med_meglitinidas_nateglinida',
            'med_inhibidores_alfaglucosidasa_acarbosa',
            'med_inhibidores_alfaglucosidasa_miglitol',
            'med_tiazolidinedionas_pioglitazona',
            'med_tiazolidinedionas_rosiglitazona',
            'med_inhibidores_dpp4_sitagliptina',
            'med_inhibidores_dpp4_saxaglipina',
            'med_inhibidores_dpp4_linagliptina',
            'med_inhibidores_dpp4_alogliptina',
            'med_agonistas_glp1_exenatida',
            'med_agonistas_glp1_liraglutida',
            'med_agonistas_glp1_dulaglutida',
            'med_agonistas_glp1_lixisenatida',
            'med_agonistas_glp1_semaglutida',
            'med_inhibidores_sglt2_empagliflozina',
            'med_inhibidores_sglt2_dapagliflozina',
            'med_inhibidores_sglt2_canagliflozina',
            'med_inhibidores_sglt2_ertugliflozina',

            // Insulinas
            'ins_rapida_regular',
            'ins_ultrarrapida_lispro',
            'ins_ultrarrapida_aspart',
            'ins_ultrarrapida_glulisina',
            'ins_intermedia_nph',
            'ins_prolongada_glargina',
            'ins_prolongada_detemir',
            'ins_prolongada_degludec',
            'ins_ultralarga_degludec',
            'ins_ultralarga_glargina_u300',
            'ins_mezcla_nph_regular',
            'ins_mezcla_lispro',
            'ins_mezcla_aspart',

            'med_estatinas',
            'med_antihipertensivos',
            'med_antiagregantes',

            // Labs
            'lab_glucosa_ayunas',
            'lab_glucosa_postprandial',
            'lab_hba1c',
            'lab_curva_tolerancia',
            'lab_creatinina_serica',
            'lab_tfg',
            'lab_urea_bun',
            'lab_microalbuminuria_orina',
            'lab_relacion_acr',
            'lab_ego',
            'lab_colesterol_total',
            'lab_ldl',
            'lab_hdl',
            'lab_trigliceridos',
            'lab_sodio',
            'lab_potasio',
            'lab_cloro',
            'lab_bicarbonato',
            'lab_calcio',
            'lab_fosforo',
            'lab_magnesio',
            'lab_gasometria',
            'lab_alt',
            'lab_ast',
            'lab_fosfatasa_alcalina',
            'lab_bilirrubinas',
            'lab_albumina_serica',
            'lab_cetonas',
            'lab_peptido_c',
            'lab_insulinemia',
            'lab_pcr',
            'lab_vsg',
            'lab_troponina',
            'lab_bnp',
            'lab_homocisteina',
            'lab_vitamina_d',
            'lab_hormonas_tiroideas',
            'lab_hemograma',

            'control_bitacora',
            'control_hipoglucemias',
            'control_hiperglucemias_sintomaticas',
            'control_adherencia',
            'control_tecnica_insulina',
            'control_glucemia_hb1ac_reciente',
            'control_glucemia_glucometrias_diarias',
            'control_glucemia_hipoglucemias_recientes',
            'control_glucemia_hiperglucemias_sintomaticas',
            'control_glucemia_cambios_medicamentos',
            'control_glucemia_aplicacion_insulina_adecuada',

            'rev_neuropatia_monofilamento',
            'rev_renal_laboratorios',
            'rev_vision_borrosa',
            'rev_macro_coronaria',
            'rev_macro_claudicacion',
            'rev_riesgo_cv',

            'alimentacion_adecuada',
            'actividad_fisica',
            'consumo_alcohol',
            'tabaquismo',
            'horarios_comida_regulares',
            'educacion_diabetologica',
            'tecnica_insulina',
            'revision_sitio_inyeccion',
            'prevencion_hipoglucemia',
            'cuidado_pies',
            'revision_metas',
            'sintomas_ansiedad_depresion',
            'estres_enfermedad',
            'apoyo_familiar_social',

            // Próximos Estudios (Laboratorio)
            'prox_bh',
            'prox_osc',
            'prox_ego',
            'prox_hba1c',
            'prox_perfil_tiroideo',
            'prox_perfil_hepatico',
            'prox_insulina_basal',
            'prox_qs3_i',
            'prox_qs3_ii',
            'prox_glucosa_ayunas',
            'prox_glucosa_postprandial',
            'prox_curva_tolerancia',
            'prox_creatinina',
            'prox_tfg',
            'prox_urea_bun',
            'prox_microalbuminuria',
            'prox_acr',
            'prox_colesterol_total',
            'prox_colesterol_ldl',
            'prox_colesterol_hdl',
            'prox_trigliceridos',
            'prox_sodio',
            'prox_potasio',
            'prox_cloro',
            'prox_bicarbonato',
            'prox_calcio',
            'prox_fosforo',
            'prox_magnesio',
            'prox_gasometria',
            'prox_alt_gpt',
            'prox_ast_got',
            'prox_fosfatasa_alcalina',
            'prox_bilirrubinas',
            'prox_albumina_serica',
            'prox_cetonas',
            'prox_pcr',
            'prox_vsg',
            'prox_peptido_c',
            'prox_insulinemia',
            'prox_vitamina_d',
            'prox_troponina',
            'prox_bnp',
            'prox_homocisteina',

            // Próximos Estudios (Radiología)
            'prox_rad_craneo',
            'prox_rad_senos',
            'prox_rad_perfilograma',
            'prox_rad_cuello',
            'prox_rad_col_cervical',
            'prox_rad_col_dorsal',
            'prox_rad_col_lumbar',
            'prox_rad_col_oblicuas',
            'prox_rad_col_dinamicas',
            'prox_rad_torax_pa',
            'prox_rad_torax_lat',
            'prox_rad_torax_oseo_ap',
            'prox_rad_torax_oseo_obl',
            'prox_rad_abd_supino',
            'prox_rad_abd_bipe',
            'prox_rad_escanometria',
            'prox_rad_huesos',
            'prox_rad_cefalopelvi',

            // Próximos Estudios (Contrastados)
            'prox_con_esofagograma',
            'prox_con_serie_egd',
            'prox_con_transito_int',
            'prox_con_colon_enema',
            'prox_con_colangio_t',
            'prox_con_urograma',
            'prox_con_cistograma',
            'prox_con_uretrograma',

            // Próximos Estudios (Ultrasonido)
            'prox_us_abd_completo',
            'prox_us_higado_vias',
            'prox_us_renal_vias',
            'prox_us_pelvico_gin',
            'prox_us_gin_endovag',
            'prox_us_prostatico',
            'prox_us_pros_transrectal',
            'prox_us_testicular',
            'prox_us_inguinal',
            'prox_us_cuello_tiroides',
            'prox_us_transfontanelar',
            'prox_us_obstetrico',
            'prox_us_obstetrico_4d',
            'prox_us_obstetrico_doppler',
            'prox_us_perfil_biofisico',
            'prox_us_doppler_obs',
            'prox_us_doppler_carotida',
            'prox_us_doppler_venoso_art',
            'prox_us_doppler_ext_venoso_art',
            'prox_us_doppler_abd_renal',
            'prox_us_doppler_testicular',
            'prox_us_mamario',
            'prox_us_tejidos_blandos',
            'prox_us_musculoesqueletico',
            'prox_us_prueba_boyden',
            'prox_us_seguimientos_foli',

            // Próximos Estudios (Tomografía)
            'prox_tac_craneo',
            'prox_tac_silla_turca',
            'prox_tac_senos',
            'prox_tac_orbitas_oidos',
            'prox_tac_macizo_3d',
            'prox_tac_cuello',
            'prox_tac_col_cervical',
            'prox_tac_col_dorsal',
            'prox_tac_col_lumbar',
            'prox_tac_torax_ar',
            'prox_tac_abdominopelvica',
            'prox_tac_toracoabdominopelvica',
            'prox_tac_urotac',
            'prox_tac_cadera_3d',
            'prox_tac_hombro',
            'prox_tac_codo',
            'prox_tac_muneca',
            'prox_tac_rodilla',
            'prox_tac_tobillo',
            'prox_tac_angio_carotidas',
            'prox_tac_angio_aorta',
            'prox_tac_angio_pulmonar',
            'prox_tac_angio_abdominal',
            'prox_tac_angio_renal',
            'prox_tac_angio_ext_sup',
            'prox_tac_angio_ext_inf',
            'prox_tac_simple',
            'prox_tac_contraste'
        ];
    }

    /**
     * Obtiene los estudios marcados como "próximos" en la última consulta de medicina interna del paciente
     * @param int $id_paciente
     * @return array Lista de nombres de estudios
     */
    public function obtenerEstudiosPendientes($id_paciente)
    {
        $fields = $this->getProxEstudiosFields();
        $fields_sql = implode(", ", $fields);

        $query = "SELECT $fields_sql 
                  FROM consulta_medicina_interna 
                  WHERE id_paciente = :id_paciente 
                  ORDER BY created_at DESC LIMIT 1";

        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':id_paciente', $id_paciente);
        $stmt->execute();

        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        if (!$row)
            return [];

        $estudios = [];
        $labels = $this->getProxEstudiosLabels();

        foreach ($row as $field => $value) {
            if ($value == 1 || ($value !== null && $value !== '0' && $field == 'prox_rad_col_oblicuas_desc')) {
                // Si es un checkbox (1) o un campo de texto con contenido
                $label = $labels[$field] ?? $field;
                $estudios[] = $label;
            }
        }

        return $estudios;
    }

    /**
     * Retorna un mapeo de campos prox_ a etiquetas legibles
     */
    private function getProxEstudiosLabels()
    {
        return [
            'prox_bh' => 'BH',
            'prox_osc' => 'OSC',
            'prox_ego' => 'EGO',
            'prox_hba1c' => 'HbA1c',
            'prox_perfil_tiroideo' => 'Perfil Tiroideo',
            'prox_perfil_hepatico' => 'Perfil Hepático',
            'prox_insulina_basal' => 'Insulina Basal',
            'prox_qs3_i' => 'QS3 I',
            'prox_qs3_ii' => 'QS3 II',
            'prox_glucosa_ayunas' => 'Glucosa Ayunas',
            'prox_glucosa_postprandial' => 'Glucosa Postprandial',
            'prox_curva_tolerancia' => 'Curva Tolerancia',
            'prox_creatinina' => 'Creatinina',
            'prox_tfg' => 'TFG',
            'prox_urea_bun' => 'Urea/BUN',
            'prox_microalbuminuria' => 'Microalbuminuria',
            'prox_acr' => 'ACR',
            'prox_colesterol_total' => 'Colesterol Total',
            'prox_colesterol_ldl' => 'LDL',
            'prox_colesterol_hdl' => 'HDL',
            'prox_trigliceridos' => 'Triglicéridos',
            'prox_sodio' => 'Sodio',
            'prox_potasio' => 'Potasio',
            'prox_cloro' => 'Cloro',
            'prox_bicarbonato' => 'Bicarbonato',
            'prox_calcio' => 'Calcio',
            'prox_fosforo' => 'Fósforo',
            'prox_magnesio' => 'Magnesio',
            'prox_gasometria' => 'Gasometría',
            'prox_alt_gpt' => 'ALT/GPT',
            'prox_ast_got' => 'AST/GOT',
            'prox_fosfatasa_alcalina' => 'Fosfatasa Alcalina',
            'prox_bilirrubinas' => 'Bilirrubinas',
            'prox_albumina_serica' => 'Albúmina Sérica',
            'prox_cetonas' => 'Cetonas',
            'prox_pcr' => 'PCR',
            'prox_vsg' => 'VSG',
            'prox_peptido_c' => 'Péptido C',
            'prox_insulinemia' => 'Insulinemia',
            'prox_vitamina_d' => 'Vitamina D',
            'prox_troponina' => 'Troponina',
            'prox_bnp' => 'BNP',
            'prox_homocisteina' => 'Homocisteína',
            // Radiología
            'prox_rad_craneo' => 'Rad. Cráneo',
            'prox_rad_senos' => 'Rad. Senos Paranasales',
            'prox_rad_perfilograma' => 'Perfilograma',
            'prox_rad_cuello' => 'Rad. Cuello',
            'prox_rad_col_cervical' => 'Rad. Columna Cervical',
            'prox_rad_col_dorsal' => 'Rad. Columna Dorsal',
            'prox_rad_col_lumbar' => 'Rad. Columna Lumbar',
            'prox_rad_col_oblicuas' => 'Rad. Columna Oblicuas',
            'prox_rad_col_dinamicas' => 'Rad. Columna Dinámicas',
            'prox_rad_torax_pa' => 'Rad. Tórax PA',
            'prox_rad_torax_lat' => 'Rad. Tórax LAT',
            'prox_rad_torax_oseo_ap' => 'Rad. Tórax Óseo AP',
            'prox_rad_torax_oseo_obl' => 'Rad. Tórax Óseo OBL',
            'prox_rad_abd_supino' => 'Rad. Abdomen Supino',
            'prox_rad_abd_bipe' => 'Rad. Abdomen Bipedestación',
            'prox_rad_escanometria' => 'Escanometría',
            'prox_rad_huesos' => 'Rad. Huesos',
            'prox_rad_cefalopelvi' => 'Cefalopelvimetría',
            // Estudios Contrastados
            'prox_con_esofagograma' => 'Esofagograma',
            'prox_con_serie_egd' => 'Serie EGD',
            'prox_con_transito_int' => 'Tránsito Intestinal',
            'prox_con_colon_enema' => 'Colon por Enema',
            'prox_con_colangio_t' => 'Colangiografía sonda T',
            'prox_con_urograma' => 'Urograma Excretor',
            'prox_con_cistograma' => 'Cistograma',
            'prox_con_uretrograma' => 'Uretrograma retrógrado',
            // US
            'prox_us_abd_completo' => 'US Abdomen Completo',
            'prox_us_higado_vias' => 'US Hígado/Vías',
            'prox_us_renal_vias' => 'US Renal',
            'prox_us_pelvico_gin' => 'US Pélvico',
            'prox_us_gin_endovag' => 'US Ginec. Endovaginal',
            'prox_us_prostatico' => 'US Prostático',
            'prox_us_pros_transrectal' => 'US Prost. Transrectal',
            'prox_us_testicular' => 'US Testicular',
            'prox_us_inguinal' => 'US Inguinal',
            'prox_us_cuello_tiroides' => 'US Tiroides',
            'prox_us_transfontanelar' => 'US Transfontanelar',
            'prox_us_obstetrico' => 'US Obstétrico',
            'prox_us_obstetrico_4d' => 'US Obstétrico 4D',
            'prox_us_obstetrico_doppler' => 'US Obstétrico Doppler',
            'prox_us_perfil_biofisico' => 'US Perfil Biofísico',
            'prox_us_doppler_obs' => 'US Doppler Obstétrico',
            'prox_us_doppler_carotida' => 'US Doppler Carotídeo',
            'prox_us_doppler_venoso_art' => 'US Doppler Venoso/Art.',
            'prox_us_doppler_ext_venoso_art' => 'US Doppler Ext. Ven/Art.',
            'prox_us_doppler_abd_renal' => 'US Doppler Abd. o Renal',
            'prox_us_doppler_testicular' => 'US Doppler Testicular',
            'prox_us_mamario' => 'US Mamario',
            'prox_us_tejidos_blandos' => 'US Tejidos Blandos',
            'prox_us_musculoesqueletico' => 'US Musculoesquelético',
            'prox_us_prueba_boyden' => 'US Prueba de Boyden',
            'prox_us_seguimientos_foli' => 'US Seguimientos Foliculares',
            // TAC
            'prox_tac_craneo' => 'TAC Cráneo',
            'prox_tac_silla_turca' => 'TAC Silla Turca',
            'prox_tac_senos' => 'TAC Senos Paranasales',
            'prox_tac_orbitas_oidos' => 'TAC Órbitas y Oídos',
            'prox_tac_macizo_3d' => 'TAC Macizo Facial 3D',
            'prox_tac_cuello' => 'TAC Cuello',
            'prox_tac_col_cervical' => 'TAC Columna Cervical',
            'prox_tac_col_dorsal' => 'TAC Columna Dorsal',
            'prox_tac_col_lumbar' => 'TAC Columna Lumbar',
            'prox_tac_torax_ar' => 'TAC Tórax AR',
            'prox_tac_abdominopelvica' => 'TAC Abdomino-Pélvica',
            'prox_tac_toracoabdominopelvica' => 'TAC Toracoabdominopélvica',
            'prox_tac_urotac' => 'TAC UROTAC',
            'prox_tac_cadera_3d' => 'TAC Cadera 3D',
            'prox_tac_hombro' => 'TAC Hombro',
            'prox_tac_codo' => 'TAC Codo',
            'prox_tac_muneca' => 'TAC Muñeca',
            'prox_tac_rodilla' => 'TAC Rodilla',
            'prox_tac_tobillo' => 'TAC Tobillo',
            'prox_tac_angio_carotidas' => 'AngioTAC Carótidas',
            'prox_tac_angio_aorta' => 'AngioTAC Aorta',
            'prox_tac_angio_pulmonar' => 'AngioTAC Pulmonar',
            'prox_tac_angio_abdominal' => 'AngioTAC Abdominal',
            'prox_tac_angio_renal' => 'AngioTAC Renal',
            'prox_tac_angio_ext_sup' => 'AngioTAC Ext. Sup.',
            'prox_tac_angio_ext_inf' => 'AngioTAC Ext. Inf.',
            'prox_tac_simple' => 'TAC Simple',
            'prox_tac_contraste' => 'TAC Con Contraste'
        ];
    }

    private function getProxEstudiosFields()
    {
        return [
            'prox_bh',
            'prox_osc',
            'prox_ego',
            'prox_hba1c',
            'prox_perfil_tiroideo',
            'prox_perfil_hepatico',
            'prox_insulina_basal',
            'prox_qs3_i',
            'prox_qs3_ii',
            'prox_glucosa_ayunas',
            'prox_glucosa_postprandial',
            'prox_curva_tolerancia',
            'prox_creatinina',
            'prox_tfg',
            'prox_urea_bun',
            'prox_microalbuminuria',
            'prox_acr',
            'prox_colesterol_total',
            'prox_colesterol_ldl',
            'prox_colesterol_hdl',
            'prox_trigliceridos',
            'prox_sodio',
            'prox_potasio',
            'prox_cloro',
            'prox_bicarbonato',
            'prox_calcio',
            'prox_fosforo',
            'prox_magnesio',
            'prox_gasometria',
            'prox_alt_gpt',
            'prox_ast_got',
            'prox_fosfatasa_alcalina',
            'prox_bilirrubinas',
            'prox_albumina_serica',
            'prox_cetonas',
            'prox_pcr',
            'prox_vsg',
            'prox_peptido_c',
            'prox_insulinemia',
            'prox_vitamina_d',
            'prox_troponina',
            'prox_bnp',
            'prox_homocisteina',
            'prox_rad_craneo',
            'prox_rad_senos',
            'prox_rad_perfilograma',
            'prox_rad_cuello',
            'prox_rad_col_cervical',
            'prox_rad_col_dorsal',
            'prox_rad_col_lumbar',
            'prox_rad_col_oblicuas',
            'prox_rad_col_dinamicas',
            'prox_rad_torax_pa',
            'prox_rad_torax_lat',
            'prox_rad_torax_oseo_ap',
            'prox_rad_torax_oseo_obl',
            'prox_rad_abd_supino',
            'prox_rad_abd_bipe',
            'prox_rad_escanometria',
            'prox_rad_huesos',
            'prox_rad_cefalopelvi',
            'prox_con_esofagograma',
            'prox_con_serie_egd',
            'prox_con_transito_int',
            'prox_con_colon_enema',
            'prox_con_colangio_t',
            'prox_con_urograma',
            'prox_con_cistograma',
            'prox_con_uretrograma',
            'prox_us_abd_completo',
            'prox_us_higado_vias',
            'prox_us_renal_vias',
            'prox_us_pelvico_gin',
            'prox_us_gin_endovag',
            'prox_us_prostatico',
            'prox_us_pros_transrectal',
            'prox_us_testicular',
            'prox_us_inguinal',
            'prox_us_cuello_tiroides',
            'prox_us_transfontanelar',
            'prox_us_obstetrico',
            'prox_us_obstetrico_4d',
            'prox_us_obstetrico_doppler',
            'prox_us_perfil_biofisico',
            'prox_us_doppler_obs',
            'prox_us_doppler_carotida',
            'prox_us_doppler_venoso_art',
            'prox_us_doppler_ext_venoso_art',
            'prox_us_doppler_abd_renal',
            'prox_us_doppler_testicular',
            'prox_us_mamario',
            'prox_us_tejidos_blandos',
            'prox_us_musculoesqueletico',
            'prox_us_prueba_boyden',
            'prox_us_seguimientos_foli',
            'prox_tac_craneo',
            'prox_tac_silla_turca',
            'prox_tac_senos',
            'prox_tac_orbitas_oidos',
            'prox_tac_macizo_3d',
            'prox_tac_cuello',
            'prox_tac_col_cervical',
            'prox_tac_col_dorsal',
            'prox_tac_col_lumbar',
            'prox_tac_torax_ar',
            'prox_tac_abdominopelvica',
            'prox_tac_toracoabdominopelvica',
            'prox_tac_urotac',
            'prox_tac_cadera_3d',
            'prox_tac_hombro',
            'prox_tac_codo',
            'prox_tac_muneca',
            'prox_tac_rodilla',
            'prox_tac_tobillo',
            'prox_tac_angio_carotidas',
            'prox_tac_angio_aorta',
            'prox_tac_angio_pulmonar',
            'prox_tac_angio_abdominal',
            'prox_tac_angio_renal',
            'prox_tac_angio_ext_sup',
            'prox_tac_angio_ext_inf',
            'prox_tac_simple',
            'prox_tac_contraste'
        ];
    }
}
