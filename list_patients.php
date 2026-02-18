<?php
require_once 'app/config/database.php';
$db = (new Database())->getConnection();
$s = $db->query("SELECT id_paciente, numero_expediente, nombre, apellido_paterno, sexo, fecha_nacimiento FROM pacientes WHERE activo=1 ORDER BY id_paciente");
foreach ($s->fetchAll(PDO::FETCH_ASSOC) as $p) {
    echo $p['id_paciente'] . ' | ' . $p['numero_expediente'] . ' | ' . $p['nombre'] . ' ' . $p['apellido_paterno'] . ' | ' . $p['sexo'] . ' | ' . $p['fecha_nacimiento'] . PHP_EOL;
}
// Also get doctor id
$u = $db->query("SELECT id_usuario FROM usuarios LIMIT 1")->fetch(PDO::FETCH_ASSOC);
echo "Doctor ID: " . $u['id_usuario'] . PHP_EOL;
