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
        // 1. Validar capacidad máxima (4 pacientes por día - Solo visitas activas)
        $fecha_solo = date('Y-m-d', strtotime($datos['fecha_visita']));

        $query_check = "SELECT COUNT(*) as total FROM " . $this->table_name . " 
                        WHERE DATE(fecha_visita) = :fecha AND estatus != 'Cancelada'";
        $stmt_check = $this->conn->prepare($query_check);
        $stmt_check->bindParam(':fecha', $fecha_solo);
        $stmt_check->execute();
        $row = $stmt_check->fetch(PDO::FETCH_ASSOC);

        if ($row['total'] >= 4) {
            throw new Exception("Capacidad máxima alcanzada para este día (4 pacientes).");
        }

        // 2. Validar que el paciente no tenga ya una cita este día
        $query_dup = "SELECT COUNT(*) as total FROM " . $this->table_name . " 
                      WHERE id_paciente = :id_paciente AND DATE(fecha_visita) = :fecha";
        $stmt_dup = $this->conn->prepare($query_dup);
        $stmt_dup->bindParam(':id_paciente', $datos['id_paciente'], PDO::PARAM_INT);
        $stmt_dup->bindParam(':fecha', $fecha_solo);
        $stmt_dup->execute();
        $row_dup = $stmt_dup->fetch(PDO::FETCH_ASSOC);

        if ($row_dup['total'] > 0) {
            throw new Exception("Este paciente ya tiene una cita agendada para este día.");
        }

        // 3. Proceder con la inserción
        $query = "INSERT INTO " . $this->table_name . "
              (id_paciente, id_doctor, fecha_visita, tipo_visita, numero_visita,
               diagnostico, plan_tratamiento, observaciones, proxima_cita, estatus, created_by)
              VALUES 
              (:id_paciente, :id_doctor, :fecha_visita, :tipo_visita, :numero_visita,
               :diagnostico, :plan_tratamiento, :observaciones, :proxima_cita, :estatus, :created_by)";

        $stmt = $this->conn->prepare($query);

        // Bind parameters
        $stmt->bindParam(':id_paciente', $datos['id_paciente'], PDO::PARAM_INT);
        $stmt->bindParam(':id_doctor', $datos['id_doctor'], PDO::PARAM_INT);
        $stmt->bindParam(':fecha_visita', $datos['fecha_visita']);
        $stmt->bindParam(':tipo_visita', $datos['tipo_visita']);
        $numero_visita = $datos['numero_visita'] ?? null;
        $stmt->bindParam(':numero_visita', $numero_visita);
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

    /**
     * Obtener todas las visitas con datos de paciente y doctor
     */
    public function obtenerTodas($search = '', $limit = 7)
    {
        $query = "SELECT v.*, 
                  CONCAT(p.nombre, ' ', p.apellido_paterno, ' ', IFNULL(p.apellido_materno, '')) as paciente_nombre,
                  p.numero_expediente,
                  CONCAT(u.nombre, ' ', u.apellido_paterno) as doctor_nombre
                  FROM " . $this->table_name . " v
                  JOIN pacientes p ON v.id_paciente = p.id_paciente
                  JOIN usuarios u ON v.id_doctor = u.id_usuario";

        if (!empty($search)) {
            $query .= " WHERE p.nombre LIKE :search 
                        OR p.apellido_paterno LIKE :search 
                        OR p.numero_expediente LIKE :search
                        OR v.motivo_consulta LIKE :search";
        }

        $query .= " ORDER BY v.fecha_visita DESC LIMIT :limit";

        $stmt = $this->conn->prepare($query);

        if (!empty($search)) {
            $searchTerm = "%$search%";
            $stmt->bindParam(':search', $searchTerm);
        }

        $stmt->bindParam(':limit', $limit, PDO::PARAM_INT);
        $stmt->execute();

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Obtener conteos de visitas por día para un rango de fechas
     */
    public function obtenerConteosRango($inicio, $fin)
    {
        $query = "SELECT DATE(fecha_visita) as fecha, COUNT(*) as total 
                  FROM " . $this->table_name . " 
                  WHERE DATE(fecha_visita) >= :inicio AND DATE(fecha_visita) <= :fin
                  AND estatus != 'Cancelada'
                  GROUP BY DATE(fecha_visita)";

        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':inicio', $inicio);
        $stmt->bindParam(':fin', $fin);
        $stmt->execute();

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Obtener lista de pacientes programados para un día
     */
    public function obtenerPacientesPorDia($fecha)
    {
        $query = "SELECT p.nombre, p.apellido_paterno, p.numero_expediente, v.tipo_visita, v.estatus
                  FROM " . $this->table_name . " v
                  JOIN pacientes p ON v.id_paciente = p.id_paciente
                  WHERE DATE(v.fecha_visita) = :fecha AND v.estatus != 'Cancelada'
                  ORDER BY v.fecha_visita ASC";

        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':fecha', $fecha);
        $stmt->execute();

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Actualizar estatus de una visita
     */
    public function actualizarEstatus($id, $estatus)
    {
        $query = "UPDATE " . $this->table_name . " SET estatus = :estatus WHERE id_visita = :id";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':id', $id, PDO::PARAM_INT);
        $stmt->bindParam(':estatus', $estatus);
        return $stmt->execute();
    }

    /**
     * Reagendar una visita
     */
    public function reagendar($id, $nueva_fecha)
    {
        $query = "UPDATE " . $this->table_name . " 
                  SET fecha_visita = :fecha, estatus = 'Programada' 
                  WHERE id_visita = :id";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':id', $id, PDO::PARAM_INT);
        $stmt->bindParam(':fecha', $nueva_fecha);
        return $stmt->execute();
    }
}
