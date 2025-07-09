<?php 
@session_start();
date_default_timezone_set('America/El_Salvador');
setlocale(LC_TIME, 'es_ES');//Para la fecha en español 
setlocale(LC_MONETARY, 'en_US.UTF-8');
ini_set('memory_limit', '-1');
set_time_limit(900);
error_reporting(E_ALL);
ini_set('display_errors', '1');

?>