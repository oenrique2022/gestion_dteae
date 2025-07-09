<?php 
include_once("conf.inc.php") ;
$search = isset($_GET['search']) ? $_GET['search'] : '';
$aeconomica=new ActividadEconomica ;
$aeconomica->listar($search);
$data = [];
foreach($aeconomica->getDatos() as $datosActividad){
 $data[] = [
        'id' => $datosActividad['codigo_actividad'],
        'text' => $datosActividad['codigo_actividad']." ".$datosActividad['nombre_actividad']
    ];
 }
 echo json_encode($data);
?>