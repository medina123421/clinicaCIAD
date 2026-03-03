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
                'q1' => $datos['q1'] ?? null,
                'q2' => $datos['q2'] ?? null,
                'q3' => $datos['q3'] ?? null,
                'q4' => $datos['q4'] ?? null,
                'q5' => $datos['q5'] ?? null,
                'q6' => $datos['q6'] ?? null,
                'q7' => $datos['q7'] ?? null,
                'q8' => $datos['q8'] ?? null,
                'q9' => $datos['q9'] ?? null,
                'q10' => $datos['q10'] ?? null,
                'q11' => $datos['q11'] ?? null,
                'q12' => $datos['q12'] ?? null,
                'q13' => $datos['q13'] ?? null,
                'q14' => $datos['q14'] ?? null,
                'q15' => $datos['q15'] ?? null,
                'q16' => $datos['q16'] ?? null,
                'q17' => $datos['q17'] ?? null,
                'q18' => $datos['q18'] ?? null,
                'q19' => $datos['q19'] ?? null,
                'q20' => $datos['q20'] ?? null,
                'puntuacion_total' => $datos['puntuacion_total'] ?? 0,
                // BDI-2
                'bdi_q1' => $datos['bdi_q1'] ?? null,
                'bdi_q2' => $datos['bdi_q2'] ?? null,
                'bdi_q3' => $datos['bdi_q3'] ?? null,
                'bdi_q4' => $datos['bdi_q4'] ?? null,
                'bdi_q5' => $datos['bdi_q5'] ?? null,
                'bdi_q6' => $datos['bdi_q6'] ?? null,
                'bdi_q7' => $datos['bdi_q7'] ?? null,
                'bdi_q8' => $datos['bdi_q8'] ?? null,
                'bdi_q9' => $datos['bdi_q9'] ?? null,
                'bdi_q10' => $datos['bdi_q10'] ?? null,
                'bdi_q11' => $datos['bdi_q11'] ?? null,
                'bdi_q12' => $datos['bdi_q12'] ?? null,
                'bdi_q13' => $datos['bdi_q13'] ?? null,
                'bdi_q14' => $datos['bdi_q14'] ?? null,
                'bdi_q15' => $datos['bdi_q15'] ?? null,
                'bdi_q16' => $datos['bdi_q16'] ?? null,
                'bdi_q17' => $datos['bdi_q17'] ?? null,
                'bdi_q18' => $datos['bdi_q18'] ?? null,
                'bdi_q19' => $datos['bdi_q19'] ?? null,
                'bdi_q20' => $datos['bdi_q20'] ?? null,
                'bdi_q21' => $datos['bdi_q21'] ?? null,
                'bdi_total' => $datos['bdi_total'] ?? 0,
                // BAI
                'bai_q1' => $datos['bai_q1'] ?? null,
                'bai_q2' => $datos['bai_q2'] ?? null,
                'bai_q3' => $datos['bai_q3'] ?? null,
                'bai_q4' => $datos['bai_q4'] ?? null,
                'bai_q5' => $datos['bai_q5'] ?? null,
                'bai_q6' => $datos['bai_q6'] ?? null,
                'bai_q7' => $datos['bai_q7'] ?? null,
                'bai_q8' => $datos['bai_q8'] ?? null,
                'bai_q9' => $datos['bai_q9'] ?? null,
                'bai_q10' => $datos['bai_q10'] ?? null,
                'bai_q11' => $datos['bai_q11'] ?? null,
                'bai_q12' => $datos['bai_q12'] ?? null,
                'bai_q13' => $datos['bai_q13'] ?? null,
                'bai_q14' => $datos['bai_q14'] ?? null,
                'bai_q15' => $datos['bai_q15'] ?? null,
                'bai_q16' => $datos['bai_q16'] ?? null,
                'bai_q17' => $datos['bai_q17'] ?? null,
                'bai_q18' => $datos['bai_q18'] ?? null,
                'bai_q19' => $datos['bai_q19'] ?? null,
                'bai_q20' => $datos['bai_q20'] ?? null,
                'bai_q21' => $datos['bai_q21'] ?? null,
                'bai_total' => $datos['bai_total'] ?? 0,
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