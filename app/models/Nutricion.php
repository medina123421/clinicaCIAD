<?php
/**
 * Modelo de Nutrición Clínica
 * Manejo de consulta nutricional ligada a visitas
 */

class Nutricion
{
    private $conn;
    private $table_name = "consulta_nutricion";

    public function __construct($db)
    {
        $this->conn = $db;
    }

    /**
     * Obtener consulta nutricional por ID de Visita
     */
    public function obtenerPorVisita($id_visita)
    {
        $query = "SELECT * FROM " . $this->table_name . " WHERE id_visita = :id_visita LIMIT 1";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':id_visita', $id_visita, PDO::PARAM_INT);
        $stmt->execute();

        $consulta = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($consulta) {
            $this->decodeJsonFields($consulta);
        }

        return $consulta;
    }

    /**
     * Obtener última consulta por ID de Paciente (para pre-llenado)
     */
    public function obtenerPorPaciente($id_paciente)
    {
        $query = "SELECT * FROM " . $this->table_name . " WHERE id_paciente = :id_paciente ORDER BY fecha_registro DESC LIMIT 1";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':id_paciente', $id_paciente, PDO::PARAM_INT);
        $stmt->execute();

        $consulta = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($consulta) {
            $this->decodeJsonFields($consulta);
        }

        return $consulta;
    }

    private function decodeJsonFields(&$data)
    {
        $json_fields = [
            'diagnosticos_medicos',
            'sintomas',
            'frecuencia_consumo',
            'recordatorio_24h',
            'suplementos_detalle',
            'diagnostico_nutricional',
            'objetivos_tratamiento',
            'recomendaciones_generales'
        ];

        foreach ($json_fields as $field) {
            $data[$field] = json_decode($data[$field], true) ?? [];
            if (json_last_error() !== JSON_ERROR_NONE) {
                $data[$field] = [];
            }
        }
    }

    /**
     * Guardar (Crear o Actualizar) consulta nutricional
     */
    public function guardar($datos)
    {
        try {
            $this->conn->beginTransaction();

            $id_visita = $datos['id_visita'];
            $existing = $this->obtenerPorVisita($id_visita);

            // Preparar campos JSON
            $json_fields = [
                'diagnosticos_medicos',
                'sintomas',
                'frecuencia_consumo',
                'recordatorio_24h',
                'suplementos_detalle',
                'diagnostico_nutricional',
                'objetivos_tratamiento',
                'recomendaciones_generales'
            ];

            $processed_data = [];
            foreach ($json_fields as $field) {
                $processed_data[$field] = isset($datos[$field]) ? json_encode($datos[$field]) : json_encode([]);
            }

            $fields = [
                'id_visita' => $datos['id_visita'],
                'id_paciente' => $datos['id_paciente'],

                // Antropometría
                'peso' => $datos['peso'] ?? null,
                'talla' => $datos['talla'] ?? null,
                'circunferencia_cintura' => $datos['circunferencia_cintura'] ?? null,
                'porcentaje_grasa' => $datos['porcentaje_grasa'] ?? null,
                'kilos_grasa' => $datos['kilos_grasa'] ?? null,
                'indice_masa_muscular' => $datos['indice_masa_muscular'] ?? null,
                'kilos_masa_muscular' => $datos['kilos_masa_muscular'] ?? null,
                'imc' => $datos['imc'] ?? null,

                // Clínica
                'diagnosticos_medicos' => $processed_data['diagnosticos_medicos'],
                'diagnostico_especificar' => $datos['diagnostico_especificar'] ?? null,
                'sintomas' => $processed_data['sintomas'],
                'temperatura' => $datos['temperatura'] ?? null,
                'presion_arterial' => $datos['presion_arterial'] ?? null,

                // Dietética
                'frecuencia_consumo' => $processed_data['frecuencia_consumo'],
                'alergias_alimentarias' => isset($datos['alergias_alimentarias']) ? ($datos['alergias_alimentarias'] == 'Si' ? 'Si' : 'No') : 'No',
                'alergias_alimentarias_cual' => $datos['alergias_alimentarias_cual'] ?? null,
                'recordatorio_24h' => $processed_data['recordatorio_24h'],

                // Medicamentos
                'toma_medicamentos' => isset($datos['toma_medicamentos']) ? ($datos['toma_medicamentos'] == 'Si' ? 'Si' : 'No') : 'No',
                'toma_suplementos' => isset($datos['toma_suplementos']) ? ($datos['toma_suplementos'] == 'Si' ? 'Si' : 'No') : 'No',
                'suplementos_detalle' => $processed_data['suplementos_detalle'],
                'suplementos_otro' => $datos['suplementos_otro'] ?? null,

                // Estilo de Vida
                'realiza_ejercicio' => isset($datos['realiza_ejercicio']) ? ($datos['realiza_ejercicio'] == 'Si' ? 'Si' : 'No') : 'No',
                'ejercicio_frecuencia' => $datos['ejercicio_frecuencia'] ?? null,
                'ejercicio_tipo' => $datos['ejercicio_tipo'] ?? null,
                'ejercicio_duracion' => $datos['ejercicio_duracion'] ?? null,
                'dias_descanso' => $datos['dias_descanso'] ?? null,

                'tabaquismo' => isset($datos['tabaquismo']) ? ($datos['tabaquismo'] == 'Si' ? 'Si' : 'No') : 'No',
                'alcoholismo' => isset($datos['alcoholismo']) ? ($datos['alcoholismo'] == 'Si' ? 'Si' : 'No') : 'No',
                'duerme_bien' => isset($datos['duerme_bien']) ? ($datos['duerme_bien'] == 'Si' ? 'Si' : 'No') : 'No',
                'maneja_estres' => isset($datos['maneja_estres']) ? ($datos['maneja_estres'] == 'Si' ? 'Si' : 'No') : 'No',

                'horas_sueno' => $datos['horas_sueno'] ?? null,
                'comentarios_objetivos' => $datos['comentarios_objetivos'] ?? null,

                // Diagnóstico Nutricional
                'diagnostico_nutricional' => $processed_data['diagnostico_nutricional'],
                'diagnostico_nutricional_otro' => $datos['diagnostico_nutricional_otro'] ?? null,

                // Tratamiento
                'tipo_dieta' => $datos['tipo_dieta'] ?? null,
                'objetivos_tratamiento' => $processed_data['objetivos_tratamiento'],
                'objetivos_otro' => $datos['objetivos_otro'] ?? null,

                // Recomendaciones
                'recomendaciones_generales' => $processed_data['recomendaciones_generales'],
                'recomendaciones_otros' => $datos['recomendaciones_otros'] ?? null
            ];

            if ($existing) {
                // UPDATE
                $set_clause = [];
                foreach ($fields as $key => $value) {
                    if ($key === 'id_visita' || $key === 'id_paciente')
                        continue;
                    $set_clause[] = "$key = :$key";
                }

                $query = "UPDATE " . $this->table_name . " SET " . implode(', ', $set_clause) . " WHERE id_nutricion = :id_nutricion";
                $stmt = $this->conn->prepare($query);

                $fields['id_nutricion'] = $existing['id_nutricion'];
                foreach ($fields as $key => $value) {
                    $stmt->bindValue(":$key", $value);
                }
                $stmt->execute();
                $id = $existing['id_nutricion'];

            } else {
                // INSERT
                $columns = implode(', ', array_keys($fields));
                $placeholders = ':' . implode(', :', array_keys($fields));

                $query = "INSERT INTO " . $this->table_name . " ($columns) VALUES ($placeholders)";
                $stmt = $this->conn->prepare($query);

                foreach ($fields as $key => $value) {
                    $stmt->bindValue(":$key", $value);
                }
                $stmt->execute();
                $id = $this->conn->lastInsertId();
            }

            $this->conn->commit();
            return true;

        } catch (Exception $e) {
            $this->conn->rollBack();
            error_log("Error saving nutrition consultation: " . $e->getMessage());
            return false;
        }
    }
}
?>