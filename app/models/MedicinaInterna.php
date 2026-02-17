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
            'observaciones_adicionales'
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
        $numeric_fields = ['anio_diagnostico', 'ultima_hba1c', 'peso', 'talla', 'imc', 'circunferencia_abdominal', 'frecuencia_cardiaca', 'temperatura', 'frecuencia_respiratoria', 'glucosa_capilar'];
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
            $stmt->bindValue(":$key", $value);
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
            'apoyo_familiar_social'
        ];
    }
}
