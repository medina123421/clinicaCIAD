<?php
require_once 'app/config/database.php';
$db = (new Database())->getConnection();
$s = $db->query('SHOW TABLES');
foreach ($s->fetchAll(PDO::FETCH_NUM) as $r) {
    echo $r[0] . PHP_EOL;
}
