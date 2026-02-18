<?php
require_once 'app/config/database.php';
$db = (new Database())->getConnection();
$s = $db->query("SHOW CREATE TABLE consulta_nutricion");
$row = $s->fetch(PDO::FETCH_ASSOC);
echo $row['Create Table'];
