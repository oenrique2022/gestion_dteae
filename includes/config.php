<?php
// Detectar la URL base dinámicamente
//$base_url = (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? "https" : "http") . "://" . $_SERVER['HTTP_HOST'] . dirname($_SERVER['SCRIPT_NAME'], 2) . "/";
// Definir constante para la base URL
//define('BASE_URL', $base_url);
define('BASE_URL', '/gestion_dteae/');
ini_set('display_errors', '1');
$_SESSION['usuario']="System";
?>
