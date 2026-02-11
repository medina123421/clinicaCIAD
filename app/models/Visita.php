<?php
/**
 * Modelo de Visita
 * Manejo de consultas y datos de visitas médicas
 */

class Visita
{
    private $conn;
    private $table_name = "visitas";

    public function __construct($db)
    {
        $this->conn = $db;
    }

    /**
     * Crear nueva visita
     */
    public function crear($datos, $usuario_id)
    {
        $query = "INSERT INTO " . $this->table_name . "
                  (id_paciente, id_doctor, fecha_visita, tipo_visita, motivo_consulta, 
                   diagnostico, plan_tratamiento, observaciones, proxima_cita, estatus, created_by)
                  VALUES 
                  (:id_paciente, :id_doctor, :fecha_visita, :tipo_visita, :motivo_consulta,
                   :diagnostico, :plan_tratamiento, :observaciones, :proxima_cita, :estatus, :created_by)";

        $stmt = $this->conn->prepare($query);

        // Bind parameters
        $stmt->bindParam(':id_paciente', $datos['id_paciente'], PDO::PARAM_INT);
        $stmt->bindParam(':id_doctor', $datos['id_doctor'], PDO::PARAM_INT); // Puede ser el mismo usuario_id si es doctor
        $stmt->bindParam(':fecha_visita', $datos['fecha_visita']);
        $stmt->bindParam(':tipo_visita', $datos['tipo_visita']);
        $stmt->bindParam(':motivo_consulta', $datos['motivo_consulta']);
        $stmt->bindParam(':diagnostico', $datos['diagnostico']);
        $stmt->bindParam(':plan_tratamiento', $datos['plan_tratamiento']);
        $stmt->bindParam(':observaciones', $datos['observaciones']);

        // Manejo de valores nulos para fechas/opcionales
        $proxima_cita = !empty($datos['proxima_cita']) ? $datos['proxima_cita'] : null;
        $stmt->bindParam(':proxima_cita', $proxima_cita);

        $estatus = !empty($datos['estatus']) ? $datos['estatus'] : 'Programada';
        $stmt->bindParam(':estatus', $estatus);

        $stmt->bindParam(':created_by', $usuario_id, PDO::PARAM_INT);

        if ($stmt->execute()) {
            return $this->conn->lastInsertId();
        }

        return false;
    }

    /**
     * Obtener visita por ID
     */
    public function obtenerPorId($id)
    {
        $query = "SELECT v.*, 
                  CONCAT(p.nombre, ' ', p.apellido_paterno, ' ', IFNULL(p.apellido_materno, '')) as paciente_nombre,
                  p.numero_expediente,
                  CONCAT(u.nombre, ' ', u.apellido_paterno) as doctor_nombre
                  FROM " . $this->table_name . " v
                  JOIN pacientes p ON v.id_paciente = p.id_paciente
                  JOIN usuarios u ON v.id_doctor = u.id_usuario
                  WHERE v.id_visita = :id";

        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':id', $id, PDO::PARAM_INT);
        $stmt->execute();

        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    /**
     * Obtener historial de visitas de un paciente
     */
    public function obtenerPorPaciente($id_paciente)
    {
        $query = "SELECT v.*, CONCAT(u.nombre, ' ', u.apellido_paterno) as doctor_nombre
                  FROM " . $this->table_name . " v
                  JOIN usuarios u ON v.id_doctor = u.id_usuario
                  WHERE v.id_paciente = :id_paciente
                  ORDER BY v.fecha_visita DESC";

        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':id_paciente', $id_paciente, PDO::PARAM_INT);
        $stmt->execute();

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
}
