<?php
/**
 * Modelo de Cuidado de los Pies
 * Manejo de datos de la especialidad Cuidado de los Pies/Podología
 * Incluye lógica de cálculo de riesgo y alertas automáticas
 */

class CuidadoPies
{
    private $conn;
    private $table_name = "cuidado_pies";

    public function __construct($db)
    {
        $this->conn = $db;
    }

    /**
     * Obtener registro por ID de visita
     * @param int $id_visita
     * @return array|false
     */
    public function obtenerPorVisita($id_visita)
    {
        $query = "SELECT * FROM " . $this->table_name . " WHERE id_visita = :id_visita LIMIT 1";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':id_visita', $id_visita, PDO::PARAM_INT);
        $stmt->execute();

        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    /**
     * Guardar (Crear o Actualizar) evaluación de Cuidado de Pies
     * Calcula automáticamente: puntuaciones totales, niveles de riesgo y alertas rojas
     * @param array $datos
     * @return bool
     */
    public function guardar($datos)
    {
        $existing = $this->obtenerPorVisita($datos['id_visita'] ?? 0);

        // Campos para cálculo de puntuación (todos los que se califican 0-2)
        $campos_dermatologicos = [
            'hiper_plantar_der', 'hiper_plantar_izq', 'hiper_dorsal_der', 'hiper_dorsal_izq', 'hiper_talar_der', 'hiper_talar_izq',
            'onicocriptosis_der', 'onicocriptosis_izq', 'onicomicosis_der', 'onicomicosis_izq', 'onicogrifosis_der', 'onicogrifosis_izq',
            'bullosis_der', 'bullosis_izq', 'necrosis_der', 'necrosis_izq', 'grietas_fisuras_der', 'grietas_fisuras_izq',
            'lesion_superficial_der', 'lesion_superficial_izq', 'anhidrosis_der', 'anhidrosis_izq', 'tina_der', 'tina_izq',
            'proceso_infeccioso_der', 'proceso_infeccioso_izq',
            'ulcera_venosa_der', 'ulcera_venosa_izq', 'ulcera_arterial_der', 'ulcera_arterial_izq',
            'ulcera_mixta_der', 'ulcera_mixta_izq', 'ulcera_otra_der', 'ulcera_otra_izq'
        ];

        $campos_estructura = [
            'hallux_valgus_der', 'hallux_valgus_izq', 'dedos_garra_der', 'dedos_garra_izq',
            'dedos_martillo_der', 'dedos_martillo_izq', 'infraducto_der', 'infraducto_izq',
            'supraducto_der', 'supraducto_izq', 'pie_cavo_der', 'pie_cavo_izq',
            'arco_caido_der', 'arco_caido_izq', 'talo_varo_der', 'talo_varo_izq',
            'espolon_calcaneo_der', 'espolon_calcaneo_izq', 'hipercargas_metatarsianos_der', 'hipercargas_metatarsianos_izq',
            'pie_charcot_der', 'pie_charcot_izq'
        ];

        $campos_neurologicos = ['reflejo_rotuliano', 'dorsiflexion_pie', 'apertura_ortejos'];

        // Calcular puntuaciones por pie
        $puntuacion_der = 0;
        $puntuacion_izq = 0;
        $alerta_roja = 0;

        // Sumar campos derechos
        foreach ($campos_dermatologicos as $campo) {
            if (strpos($campo, '_der') !== false) {
                $val = (int)($datos[$campo] ?? 0);
                $puntuacion_der += $val;
                if ($val == 2) $alerta_roja = 1; // Cualquier valor 2 = alerta roja
            }
        }
        foreach ($campos_estructura as $campo) {
            if (strpos($campo, '_der') !== false) {
                $val = (int)($datos[$campo] ?? 0);
                $puntuacion_der += $val;
                if ($val == 2) $alerta_roja = 1;
                // Pie de Charcot siempre es alerta roja
                if ($campo == 'pie_charcot_der' && $val > 0) $alerta_roja = 1;
            }
        }

        // Sumar campos izquierdos
        foreach ($campos_dermatologicos as $campo) {
            if (strpos($campo, '_izq') !== false) {
                $val = (int)($datos[$campo] ?? 0);
                $puntuacion_izq += $val;
                if ($val == 2) $alerta_roja = 1;
            }
        }
        foreach ($campos_estructura as $campo) {
            if (strpos($campo, '_izq') !== false) {
                $val = (int)($datos[$campo] ?? 0);
                $puntuacion_izq += $val;
                if ($val == 2) $alerta_roja = 1;
                if ($campo == 'pie_charcot_izq' && $val > 0) $alerta_roja = 1;
            }
        }

        // Sumar campos neurológicos (no bilaterales)
        foreach ($campos_neurologicos as $campo) {
            $val = (int)($datos[$campo] ?? 0);
            $puntuacion_der += $val;
            $puntuacion_izq += $val;
            if ($val == 2) $alerta_roja = 1;
        }

        // Alertas rojas adicionales
        if (!empty($datos['amputacion_previa'])) $alerta_roja = 1;
        if (!empty($datos['necrosis_der']) || !empty($datos['necrosis_izq'])) $alerta_roja = 1;

        // Determinar niveles de riesgo
        $riesgo_der = $this->calcularRiesgo($puntuacion_der);
        $riesgo_izq = $this->calcularRiesgo($puntuacion_izq);

        // Lista completa de campos
        $fields = [
            'id_visita',
            // Interrogatorio
            'ulcera_previa', 'amputacion_previa', 'cirugia_previa', 'herida_lenta',
            'ardor_hormigueo', 'dolor_actividad', 'dolor_reposo', 'perdida_sensacion',
            'fuma', 'cigarrillos_dia', 'revision_pies_previa',
            // Dermatológico
            'hiper_plantar_der', 'hiper_plantar_izq', 'hiper_dorsal_der', 'hiper_dorsal_izq', 'hiper_talar_der', 'hiper_talar_izq',
            'onicocriptosis_der', 'onicocriptosis_izq', 'onicomicosis_der', 'onicomicosis_izq', 'onicogrifosis_der', 'onicogrifosis_izq',
            'bullosis_der', 'bullosis_izq', 'necrosis_der', 'necrosis_izq', 'grietas_fisuras_der', 'grietas_fisuras_izq',
            'lesion_superficial_der', 'lesion_superficial_izq', 'anhidrosis_der', 'anhidrosis_izq', 'tina_der', 'tina_izq',
            'proceso_infeccioso_der', 'proceso_infeccioso_izq', 'der_izq_otra_lesion',
            'ulcera_venosa_der', 'ulcera_venosa_izq', 'ulcera_arterial_der', 'ulcera_arterial_izq',
            'ulcera_mixta_der', 'ulcera_mixta_izq', 'ulcera_otra_der', 'ulcera_otra_izq',
            // Estructura ósea
            'hallux_valgus_der', 'hallux_valgus_izq', 'dedos_garra_der', 'dedos_garra_izq',
            'dedos_martillo_der', 'dedos_martillo_izq', 'infraducto_der', 'infraducto_izq',
            'supraducto_der', 'supraducto_izq', 'pie_cavo_der', 'pie_cavo_izq',
            'arco_caido_der', 'arco_caido_izq', 'talo_varo_der', 'talo_varo_izq',
            'espolon_calcaneo_der', 'espolon_calcaneo_izq', 'hipercargas_metatarsianos_der', 'hipercargas_metatarsianos_izq',
            'pie_charcot_der', 'pie_charcot_izq',
            // Vascular y neurológico
            'pulso_pedio_der', 'pulso_pedio_izq', 'pulso_tibial_der', 'pulso_tibial_izq',
            'llenado_capilar_der', 'llenado_capilar_izq', 'varices', 'edema_godet',
            'monofilamento_puntos', 'sensibilidad_vibratoria_seg', 'reflejo_rotuliano', 'dorsiflexion_pie', 'apertura_ortejos',
            // Integración y educación
            'educacion_cuidado_pies',
            // Campos calculados
            'puntuacion_total_der', 'puntuacion_total_izq', 'riesgo_der', 'riesgo_izq', 'alerta_roja',
            // Metadatos
            'observaciones_especialista', 'created_by'
        ];

        $data_to_bind = [];
        $booleans = $this->getBooleanFieldList();

        foreach ($fields as $field) {
            if ($field === 'puntuacion_total_der') {
                $data_to_bind[$field] = $puntuacion_der;
            } elseif ($field === 'puntuacion_total_izq') {
                $data_to_bind[$field] = $puntuacion_izq;
            } elseif ($field === 'riesgo_der') {
                $data_to_bind[$field] = $riesgo_der;
            } elseif ($field === 'riesgo_izq') {
                $data_to_bind[$field] = $riesgo_izq;
            } elseif ($field === 'alerta_roja') {
                $data_to_bind[$field] = $alerta_roja;
            } elseif (in_array($field, $booleans)) {
                $data_to_bind[$field] = (isset($datos[$field]) && ($datos[$field] == '1' || $datos[$field] == 'on')) ? 1 : 0;
            } else {
                $val = $datos[$field] ?? null;
                if ($val === '') $val = null;
                $data_to_bind[$field] = $val;
            }
        }

        // Limpiar campos numéricos vacíos
        $numeric_fields = ['cigarrillos_dia', 'llenado_capilar_der', 'llenado_capilar_izq', 'monofilamento_puntos', 'sensibilidad_vibratoria_seg'];
        foreach ($numeric_fields as $nf) {
            if (isset($data_to_bind[$nf]) && $data_to_bind[$nf] === '') {
                $data_to_bind[$nf] = null;
            }
        }

        if ($existing) {
            $set_parts = [];
            foreach ($data_to_bind as $key => $value) {
                if ($key === 'id_visita') continue;
                $set_parts[] = "`$key` = :$key";
            }
            $query = "UPDATE " . $this->table_name . " SET " . implode(', ', $set_parts) . " WHERE id_visita = :id_visita";
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
     * Calcular nivel de riesgo según puntuación
     * @param int $puntuacion
     * @return string
     */
    private function calcularRiesgo($puntuacion)
    {
        if ($puntuacion <= 10) return 'Leve';
        if ($puntuacion <= 25) return 'Moderado';
        return 'Alto';
    }

    /**
     * Lista de campos booleanos (checkbox)
     */
    private function getBooleanFieldList()
    {
        return [
            'ulcera_previa', 'amputacion_previa', 'cirugia_previa', 'herida_lenta',
            'ardor_hormigueo', 'dolor_actividad', 'dolor_reposo', 'perdida_sensacion',
            'fuma', 'revision_pies_previa', 'varices', 'educacion_cuidado_pies'
        ];
    }

    /**
     * Obtener estadísticas de riesgo para dashboard
     * @return array
     */
    public function obtenerEstadisticasRiesgo()
    {
        $query = "SELECT 
                    COUNT(*) as total,
                    SUM(CASE WHEN riesgo_der = 'Alto' OR riesgo_izq = 'Alto' THEN 1 ELSE 0 END) as alto_riesgo,
                    SUM(CASE WHEN riesgo_der = 'Moderado' OR riesgo_izq = 'Moderado' THEN 1 ELSE 0 END) as moderado_riesgo,
                    SUM(alerta_roja) as alertas_rojas
                  FROM " . $this->table_name . "
                  WHERE created_at >= DATE_SUB(NOW(), INTERVAL 6 MONTH)";
        
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }
}