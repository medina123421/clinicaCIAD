<?php
/**
 * Modelo de Paciente
 * Manejo de datos de pacientes
 */

class Paciente
{
    private $conn;
    private $table_name = "pacientes";

    public function __construct($db)
    {
        $this->conn = $db;
    }

    /**
     * Obtener todos los pacientes activos
     */
    public function obtenerTodos($search = '', $limit = 50, $offset = 0)
    {
        $query = "SELECT id_paciente, numero_expediente, 
                  CONCAT(nombre, ' ', apellido_paterno, ' ', IFNULL(apellido_materno, '')) as nombre_completo,
                  edad, sexo, telefono, email, protocolo, fecha_registro
                  FROM " . $this->table_name . "
                  WHERE activo = 1";

        if (!empty($search)) {
            $query .= " AND (numero_expediente LIKE :search 
                        OR nombre LIKE :search 
                        OR apellido_paterno LIKE :search 
                        OR apellido_materno LIKE :search
                        OR email LIKE :search)";
        }

        $query .= " ORDER BY fecha_registro DESC LIMIT :limit OFFSET :offset";

        $stmt = $this->conn->prepare($query);

        if (!empty($search)) {
            $search_param = "%{$search}%";
            $stmt->bindParam(':search', $search_param);
        }

        $stmt->bindParam(':limit', $limit, PDO::PARAM_INT);
        $stmt->bindParam(':offset', $offset, PDO::PARAM_INT);
        $stmt->execute();

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Obtener paciente por ID
     */
    public function obtenerPorId($id)
    {
        $query = "SELECT * FROM " . $this->table_name . " WHERE id_paciente = :id AND activo = 1";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':id', $id, PDO::PARAM_INT);
        $stmt->execute();

        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    /**
     * Crear nuevo paciente
     */
    public function crear($datos, $usuario_id)
    {
        $query = "INSERT INTO " . $this->table_name . "
                  (numero_expediente, nombre, apellido_paterno, apellido_materno, 
                   fecha_nacimiento, sexo, telefono, email, direccion, ciudad, estado, 
                   codigo_postal, tipo_sangre, alergias, protocolo, nombre_emergencia,
                   telefono_emergencia, parentesco_emergencia, created_by)
                  VALUES 
                  (:numero_expediente, :nombre, :apellido_paterno, :apellido_materno,
                   :fecha_nacimiento, :sexo, :telefono, :email, :direccion, :ciudad, :estado,
                   :codigo_postal, :tipo_sangre, :alergias, :protocolo, :nombre_emergencia,
                   :telefono_emergencia, :parentesco_emergencia, :created_by)";

        $stmt = $this->conn->prepare($query);

        // Bind parameters
        $stmt->bindParam(':numero_expediente', $datos['numero_expediente']);
        $stmt->bindParam(':nombre', $datos['nombre']);
        $stmt->bindParam(':apellido_paterno', $datos['apellido_paterno']);
        $stmt->bindParam(':apellido_materno', $datos['apellido_materno']);
        $stmt->bindParam(':fecha_nacimiento', $datos['fecha_nacimiento']);
        $stmt->bindParam(':sexo', $datos['sexo']);
        $stmt->bindParam(':telefono', $datos['telefono']);
        $stmt->bindParam(':email', $datos['email']);
        $stmt->bindParam(':direccion', $datos['direccion']);
        $stmt->bindParam(':ciudad', $datos['ciudad']);
        $stmt->bindParam(':estado', $datos['estado']);
        $stmt->bindParam(':codigo_postal', $datos['codigo_postal']);
        $stmt->bindParam(':tipo_sangre', $datos['tipo_sangre']);
        $stmt->bindParam(':alergias', $datos['alergias']);
        $stmt->bindParam(':protocolo', $datos['protocolo']);
        $stmt->bindParam(':nombre_emergencia', $datos['nombre_emergencia']);
        $stmt->bindParam(':telefono_emergencia', $datos['telefono_emergencia']);
        $stmt->bindParam(':parentesco_emergencia', $datos['parentesco_emergencia']);
        $stmt->bindParam(':created_by', $usuario_id, PDO::PARAM_INT);

        if ($stmt->execute()) {
            return $this->conn->lastInsertId();
        }

        return false;
    }

    /**
     * Actualizar paciente
     */
    public function actualizar($id, $datos)
    {
        $query = "UPDATE " . $this->table_name . "
                  SET nombre = :nombre,
                      apellido_paterno = :apellido_paterno,
                      apellido_materno = :apellido_materno,
                      fecha_nacimiento = :fecha_nacimiento,
                      sexo = :sexo,
                      telefono = :telefono,
                      email = :email,
                      direccion = :direccion,
                      ciudad = :ciudad,
                      estado = :estado,
                      codigo_postal = :codigo_postal,
                      tipo_sangre = :tipo_sangre,
                      alergias = :alergias,
                      protocolo = :protocolo,
                      nombre_emergencia = :nombre_emergencia,
                      telefono_emergencia = :telefono_emergencia,
                      parentesco_emergencia = :parentesco_emergencia
                  WHERE id_paciente = :id";

        $stmt = $this->conn->prepare($query);

        $stmt->bindParam(':id', $id, PDO::PARAM_INT);
        $stmt->bindParam(':nombre', $datos['nombre']);
        $stmt->bindParam(':apellido_paterno', $datos['apellido_paterno']);
        $stmt->bindParam(':apellido_materno', $datos['apellido_materno']);
        $stmt->bindParam(':fecha_nacimiento', $datos['fecha_nacimiento']);
        $stmt->bindParam(':sexo', $datos['sexo']);
        $stmt->bindParam(':telefono', $datos['telefono']);
        $stmt->bindParam(':email', $datos['email']);
        $stmt->bindParam(':direccion', $datos['direccion']);
        $stmt->bindParam(':ciudad', $datos['ciudad']);
        $stmt->bindParam(':estado', $datos['estado']);
        $stmt->bindParam(':codigo_postal', $datos['codigo_postal']);
        $stmt->bindParam(':tipo_sangre', $datos['tipo_sangre']);
        $stmt->bindParam(':alergias', $datos['alergias']);
        $stmt->bindParam(':protocolo', $datos['protocolo']);
        $stmt->bindParam(':nombre_emergencia', $datos['nombre_emergencia']);
        $stmt->bindParam(':telefono_emergencia', $datos['telefono_emergencia']);
        $stmt->bindParam(':parentesco_emergencia', $datos['parentesco_emergencia']);

        return $stmt->execute();
    }

    /**
     * Generar número de expediente único
     */
    public function generarNumeroExpediente()
    {
        $year = date('Y');
        $query = "SELECT MAX(CAST(SUBSTRING(numero_expediente, -4) AS UNSIGNED)) as ultimo
                  FROM " . $this->table_name . "
                  WHERE numero_expediente LIKE 'EXP-{$year}-%'";

        $stmt = $this->conn->query($query);
        $result = $stmt->fetch(PDO::FETCH_ASSOC);

        $siguiente = ($result['ultimo'] ?? 0) + 1;

        return sprintf('EXP-%s-%04d', $year, $siguiente);
    }

    /**
     * Borrado lógico de paciente
     */
    public function eliminar($id)
    {
        $query = "UPDATE " . $this->table_name . " SET activo = 0 WHERE id_paciente = :id";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':id', $id, PDO::PARAM_INT);
        return $stmt->execute();
    }
}
