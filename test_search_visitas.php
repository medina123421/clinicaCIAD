<?php
// Mock $_GET
$_GET['search'] = 'samuel';

// Mock paths for command line
chdir(__DIR__ . '/app/ajax');

require_once 'buscar_visitas.php';
