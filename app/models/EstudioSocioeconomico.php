<?php
/**
 * Modelo de Estudio Socioeconómico
 * Manejo de datos socioeconómicos de pacientes
 */

class EstudioSocioeconomico
{
    private $conn;
    private $table_name = "estudio_socioeconomico";
    private $table_familiares = "estudio_socioeconomico_familiares";

    public function __construct($db)
    {
        $this->conn = $db;
    }

    /**
     * Obtener estudio por ID de paciente
     */
    public function obtenerPorPaciente($id_paciente)
    {
        $query = "SELECT * FROM " . $this->table_name . " WHERE id_paciente = :id_paciente ORDER BY fecha_estudio DESC LIMIT 1";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':id_paciente', $id_paciente, PDO::PARAM_INT);
        $stmt->execute();

        $estudio = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($estudio) {
            // Función helper local para decodificar JSON con limpieza de UTF-8
            $decodeJson = function ($json) {
                if (empty($json))
                    return [];
                // Intentar decodificar
                $decoded = json_decode($json, true);
                // Si falla por caracteres inválidos, intentar limpiar y reintentar
                if ($decoded === null && json_last_error() !== JSON_ERROR_NONE) {
                    $json = mb_convert_encoding($json, 'UTF-8', 'UTF-8'); // Limpia caracteres rotos
                    $decoded = json_decode($json, true);
                }
                return is_array($decoded) ? $decoded : null;
            };

            // Decodificar JSONs
            $estudio['servicio_medico'] = $decodeJson($estudio['servicio_medico']) ?? [];
            $estudio['tratamiento_actual'] = $decodeJson($estudio['tratamiento_actual']) ?? [];
            $estudio['frecuencia_alimentos'] = $decodeJson($estudio['frecuencia_alimentos']) ?? [];

            // Decodificar diagnóstico con soporte para texto plano antiguo
            $diag = $decodeJson($estudio['diagnostico_desc']);
            $estudio['diagnostico_desc'] = $diag ?? ($estudio['diagnostico_desc'] ? [$estudio['diagnostico_desc']] : []);

            // Obtener familiares
            $estudio['familiares'] = $this->obtenerFamiliares($estudio['id_estudio']);
        }

        return $estudio;
    }

    /**
     * Obtener familiares de un estudio
     */
    private function obtenerFamiliares($id_estudio)
    {
        $query = "SELECT * FROM " . $this->table_familiares . " WHERE id_estudio = :id_estudio";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':id_estudio', $id_estudio, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Guardar (Crear o Actualizar) estudio
     */
    public function guardar($datos)
    {
        try {
            $this->conn->beginTransaction();

            $existing = $this->obtenerPorPaciente($datos['id_paciente']);

            // Preparar datos JSON
            $servicio_medico = json_encode($datos['servicio_medico'] ?? []);
            $tratamiento_actual = json_encode($datos['tratamiento_actual'] ?? []);
            $frecuencia_alimentos = json_encode($datos['frecuencia_alimentos'] ?? []);
            $diagnostico_desc = json_encode($datos['diagnostico_diabetes'] ?? []); // Usar el nombre del formulario

            $fields = [
                'id_paciente' => $datos['id_paciente'],
                'religion' => $datos['religion'] ?? null,
                'tiempo_residencia' => $datos['tiempo_residencia'] ?? null,
                'escolaridad' => $datos['escolaridad'] ?? null,
                'estado_civil' => $datos['estado_civil'] ?? null,
                'ocupacion' => $datos['ocupacion'] ?? null,
                'es_jefe_familia' => isset($datos['es_jefe_familia']) ? 1 : 0,
                'relaciones_familiares' => $datos['relaciones_familiares'] ?? null,
                'apoyo_familiar' => $datos['apoyo_familiar'] ?? null,
                'tipo_vivienda' => $datos['tipo_vivienda'] ?? null,
                'material_vivienda' => $datos['material_vivienda'] ?? null,
                'num_habitaciones' => $datos['num_habitaciones'] ?? 0,
                'servicio_agua' => isset($datos['servicio_agua']) ? 1 : 0,
                'servicio_drenaje' => isset($datos['servicio_drenaje']) ? 1 : 0,
                'servicio_electricidad' => isset($datos['servicio_electricidad']) ? 1 : 0,
                'servicio_gas' => isset($datos['servicio_gas']) ? 1 : 0,
                'servicio_internet' => isset($datos['servicio_internet']) ? 1 : 0,
                'ingreso_mensual_familiar' => $datos['ingreso_mensual_familiar'] ?? 0,
                'gasto_renta' => $datos['gasto_renta'] ?? 0,
                'gasto_alimentos' => $datos['gasto_alimentos'] ?? 0,
                'gasto_transporte' => $datos['gasto_transporte'] ?? 0,
                'gasto_servicios' => $datos['gasto_servicios'] ?? 0,
                'gasto_tratamientos' => $datos['gasto_tratamientos'] ?? 0,
                'gasto_total_estimado' => $datos['gasto_total_estimado'] ?? 0,
                'apoyo_social_check' => isset($datos['apoyo_social_check']) ? 1 : 0,
                'apoyo_social_nombre' => $datos['apoyo_social_nombre'] ?? null,
                'ingreso_cubre_necesidades' => isset($datos['ingreso_cubre_necesidades']) ? 1 : 0,
                'diagnostico_desc' => $diagnostico_desc,
                'diagnostico_desc_otro' => $datos['diagnostico_desc_otro'] ?? null,
                'servicio_medico' => $servicio_medico,
                'servicio_medico_otro' => $datos['servicio_medico_otro'] ?? null,
                'tratamiento_actual' => $tratamiento_actual,
                'tiene_tratamiento' => $datos['tiene_tratamiento'] ?? 0,
                'tratamiento_detalle' => $datos['tratamiento_detalle'] ?? null,
                'cubre_costos_medicamento' => isset($datos['cubre_costos_medicamento']) ? 1 : 0,
                'cuenta_con_glucometro' => isset($datos['cuenta_con_glucometro']) ? 1 : 0,
                'dificultad_dieta_economica' => isset($datos['dificultad_dieta_economica']) ? 1 : 0,
                'frecuencia_alimentos' => $frecuencia_alimentos,
                'observaciones_trabajo_social' => $datos['observaciones_trabajo_social'] ?? null,
                'nivel_socioeconomico' => $datos['nivel_socioeconomico'] ?? null,
                'plan_intervencion' => $datos['plan_intervencion'] ?? null,
                'nombre_entrevistado' => $datos['nombre_entrevistado'] ?? null,
                'nombre_trabajador_social' => $datos['nombre_trabajador_social'] ?? null
            ];

            if ($existing) {
                // UPDATE
                $set_clause = [];
                foreach ($fields as $key => $value) {
                    if ($key === 'id_paciente')
                        continue; // Don't update FK
                    $set_clause[] = "$key = :$key";
                }
                $query = "UPDATE " . $this->table_name . " SET " . implode(', ', $set_clause) . " WHERE id_estudio = :id_estudio";
                $stmt = $this->conn->prepare($query);
                $fields['id_estudio'] = $existing['id_estudio'];
                foreach ($fields as $key => $value) {
                    $stmt->bindValue(":$key", $value);
                }
                $stmt->execute();
                $id_estudio = $existing['id_estudio'];

                // Limpiar familiares anteriores para reinsertar
                $del_fam = "DELETE FROM " . $this->table_familiares . " WHERE id_estudio = :id_estudio";
                $del_stmt = $this->conn->prepare($del_fam);
                $del_stmt->bindParam(':id_estudio', $id_estudio);
                $del_stmt->execute();

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
                $id_estudio = $this->conn->lastInsertId();
            }

            // Insertar familiares
            if (isset($datos['familiares']) && is_array($datos['familiares'])) {
                $query_fam = "INSERT INTO " . $this->table_familiares . " (id_estudio, nombre, parentesco, edad, ocupacion, ingreso_mensual) VALUES (:id_estudio, :nombre, :parentesco, :edad, :ocupacion, :ingreso_mensual)";
                $stmt_fam = $this->conn->prepare($query_fam);

                foreach ($datos['familiares'] as $familiar) {
                    // Skip empty rows
                    if (empty($familiar['nombre']))
                        continue;

                    $stmt_fam->bindValue(':id_estudio', $id_estudio);
                    $stmt_fam->bindValue(':nombre', $familiar['nombre']);
                    $stmt_fam->bindValue(':parentesco', $familiar['parentesco']);
                    $stmt_fam->bindValue(':edad', $familiar['edad']);
                    $stmt_fam->bindValue(':ocupacion', $familiar['ocupacion']);
                    $stmt_fam->bindValue(':ingreso_mensual', $familiar['ingreso_mensual'] ?? 0);
                    $stmt_fam->execute();
                }
            }

            $this->conn->commit();
            return $id_estudio;

        } catch (Exception $e) {
            $this->conn->rollBack();
            error_log("Error saving socioeconomic study: " . $e->getMessage());
            throw $e;
        }
    }
}
?>