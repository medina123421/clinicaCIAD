<?php
/**
 * Modelo de Actividad Física
 * Manejo de datos de la consulta de la especialidad Actividad Física (SARC-F5, dinamometría, Daniels, etc.)
 */

class ActividadFisica
{
    private $conn;
    private $table_name = "actividad_fisica";

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
     * Guardar (Crear o Actualizar) consulta de Actividad Física
     * Calcula automáticamente: sarc_puntuacion_total, sarc_riesgo, sts_5rep_alerta
     * @param array $datos
     * @return bool
     */
    public function guardar($datos)
    {
        $existing = $this->obtenerPorVisita($datos['id_visita'] ?? 0);

        // Calcular SARC-F5: suma de los 5 ítems (0-2 cada uno)
        $sarc_fuerza = isset($datos['sarc_fuerza']) ? (int)$datos['sarc_fuerza'] : null;
        $sarc_asistencia = isset($datos['sarc_asistencia_caminar']) ? (int)$datos['sarc_asistencia_caminar'] : null;
        $sarc_levantarse = isset($datos['sarc_levantarse_silla']) ? (int)$datos['sarc_levantarse_silla'] : null;
        $sarc_escaleras = isset($datos['sarc_subir_escaleras']) ? (int)$datos['sarc_subir_escaleras'] : null;
        $sarc_caidas = isset($datos['sarc_caidas']) ? (int)$datos['sarc_caidas'] : null;

        $total = null;
        if ($sarc_fuerza !== null && $sarc_asistencia !== null && $sarc_levantarse !== null && $sarc_escaleras !== null && $sarc_caidas !== null) {
            $total = $sarc_fuerza + $sarc_asistencia + $sarc_levantarse + $sarc_escaleras + $sarc_caidas;
        }
        $sarc_riesgo = ($total !== null && $total >= 4) ? 'Alta' : 'Baja';

        // Alerta Sit-to-Stand: tiempo > 15 seg
        $sts_seg = isset($datos['sts_5rep_seg']) && $datos['sts_5rep_seg'] !== '' ? (float)$datos['sts_5rep_seg'] : null;
        $sts_5rep_alerta = ($sts_seg !== null && $sts_seg > 15) ? 1 : 0;

        $fields = [
            'id_visita',
            'sarc_fuerza',
            'sarc_asistencia_caminar',
            'sarc_levantarse_silla',
            'sarc_subir_escaleras',
            'sarc_caidas',
            'sarc_puntuacion_total',
            'sarc_riesgo',
            'dina_mano_der',
            'dina_mano_izq',
            'dina_percentil_resultado',
            'daniels_ms_der',
            'daniels_ms_izq',
            'daniels_mi_der',
            'daniels_mi_izq',
            'sts_30seg_reps',
            'sts_5rep_seg',
            'sts_5rep_alerta',
            'eva_zona',
            'eva_puntaje',
            'mov_ms_der',
            'mov_ms_izq',
            'mov_mi_der',
            'mov_mi_izq',
            'act_realiza_ejercicio',
            'act_frecuencia',
            'act_tipo',
            'act_tipo_otro',
            'act_duracion',
            'act_dias_descanso',
            'observaciones_especialista',
            'created_by',
        ];

        $data_to_bind = [];
        foreach ($fields as $field) {
            if ($field === 'sarc_puntuacion_total') {
                $data_to_bind[$field] = $total;
            } elseif ($field === 'sarc_riesgo') {
                $data_to_bind[$field] = $sarc_riesgo;
            } elseif ($field === 'sts_5rep_alerta') {
                $data_to_bind[$field] = $sts_5rep_alerta;
            } elseif ($field === 'act_realiza_ejercicio') {
                $data_to_bind[$field] = (isset($datos[$field]) && ($datos[$field] == '1' || $datos[$field] == 'on')) ? 1 : 0;
            } else {
                $val = $datos[$field] ?? null;
                if ($val === '') {
                    $val = null;
                }
                $data_to_bind[$field] = $val;
            }
        }

        // Limpiar numéricos vacíos
        $numeric_fields = ['sarc_fuerza', 'sarc_asistencia_caminar', 'sarc_levantarse_silla', 'sarc_subir_escaleras', 'sarc_caidas', 'dina_mano_der', 'dina_mano_izq', 'daniels_ms_der', 'daniels_ms_izq', 'daniels_mi_der', 'daniels_mi_izq', 'sts_30seg_reps', 'sts_5rep_seg', 'eva_puntaje', 'mov_ms_der', 'mov_ms_izq', 'mov_mi_der', 'mov_mi_izq'];
        foreach ($numeric_fields as $nf) {
            if (isset($data_to_bind[$nf]) && $data_to_bind[$nf] === '') {
                $data_to_bind[$nf] = null;
            }
        }

        if ($existing) {
            $set_parts = [];
            foreach ($data_to_bind as $key => $value) {
                if ($key === 'id_visita') {
                    continue;
                }
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
}
