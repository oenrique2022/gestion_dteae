<?php
// Define una variable para indicar el modo y un array vacío para los datos
$modo = 'crear';
$contrato = [
    'generales' => [],
    'equipos' => [],
    'documentos' => [],
    'entregas' => []
];
$titulo_pagina = "Crear Nuevo Contrato";

// Incluye el formulario maestro
require_once __DIR__ . '/_formulario_contrato.php';
?>