<?php
/**
 * Modelo de Psicología Clínica
 * Manejo de consulta psicológica ligada a visitas
 */

class Psicologia
{
    private $conn;
    private $table_name = "consulta_psicologia";

    public function __construct($db)
    {
        $this->conn = $db;
    }

    /**
     * Obtener consulta psicológica por ID de Visita
     */
    public function obtenerPorVisita($id_visita)
    {
        $query = "SELECT * FROM " . $this->table_name . " WHERE id_visita = :id_visita LIMIT 1";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':id_visita', $id_visita, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetch(PDO::FETCH_ASSOC) ?: [];
    }

    /**
     * Obtener última consulta por ID de Paciente
     */
    public function obtenerPorPaciente($id_paciente)
    {
        $query = "SELECT * FROM " . $this->table_name . " WHERE id_paciente = :id_paciente ORDER BY fecha_registro DESC LIMIT 1";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':id_paciente', $id_paciente, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetch(PDO::FETCH_ASSOC) ?: [];
    }

    /**
     * Guardar (Crear o Actualizar) consulta psicológica
     */
    public function guardar($datos)
    {
        try {
            $this->conn->beginTransaction();

            $id_visita = $datos['id_visita'];
            $existing = $this->obtenerPorVisita($id_visita);

            $fields = [
                'id_visita' => $datos['id_visita'],
                'id_paciente' => $datos['id_paciente'],
                'numero_visita' => $datos['numero_visita'] ?? 1,
                'descripcion_paciente' => $datos['descripcion_paciente'] ?? null,

                // Visita 1
                'v1_ansiedad_beck' => $datos['v1_ansiedad_beck'] ?? null,
                'v1_depresion_beck' => $datos['v1_depresion_beck'] ?? null,
                'v1_desesperanza_beck' => $datos['v1_desesperanza_beck'] ?? null,
                'v1_observaciones' => $datos['v1_observaciones'] ?? null,

                // Visita 2
                'v2_nivel_personal' => $datos['v2_nivel_personal'] ?? null,
                'v2_nivel_economico' => $datos['v2_nivel_economico'] ?? null,
                'v2_nivel_social' => $datos['v2_nivel_social'] ?? null,
                'v2_nivel_sanitario' => $datos['v2_nivel_sanitario'] ?? null,
                'v2_observaciones' => $datos['v2_observaciones'] ?? null,

                // Visita 3
                'v3_pre_contemplacion' => $datos['v3_pre_contemplacion'] ?? null,
                'v3_contemplacion' => $datos['v3_contemplacion'] ?? null,
                'v3_decision' => $datos['v3_decision'] ?? null,
                'v3_accion' => $datos['v3_accion'] ?? null,
                'v3_mantenimiento' => $datos['v3_mantenimiento'] ?? null,
                'v3_recaida' => $datos['v3_recaida'] ?? null,
                'v3_observaciones' => $datos['v3_observaciones'] ?? null,

                // Visita 4
                'v4_logro_relajacion' => $datos['v4_logro_relajacion'] ?? null,
                'v4_descripcion_paciente' => $datos['v4_descripcion_paciente'] ?? null,
                'v4_observaciones' => $datos['v4_observaciones'] ?? null,

                // Visita 5
                'v5_tristeza' => $datos['v5_tristeza'] ?? null,
                'v5_depresion' => $datos['v5_depresion'] ?? null,
                'v5_observaciones' => $datos['v5_observaciones'] ?? null,
            ];

            if (!empty($existing)) {
                // UPDATE
                $set_clause = [];
                foreach ($fields as $key => $value) {
                    if ($key === 'id_visita' || $key === 'id_paciente')
                        continue;
                    $set_clause[] = "$key = :$key";
                }
                $query = "UPDATE " . $this->table_name . " SET " . implode(', ', $set_clause) . " WHERE id_psicologia = :id_psicologia";
                $stmt = $this->conn->prepare($query);
                $fields['id_psicologia'] = $existing['id_psicologia'];
                foreach ($fields as $key => $value) {
                    $stmt->bindValue(":$key", $value);
                }
                $stmt->execute();
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
            }

            $this->conn->commit();
            return true;

        } catch (Exception $e) {
            $this->conn->rollBack();
            error_log("Error saving psychology consultation: " . $e->getMessage());
            return false;
        }
    }
}
?>