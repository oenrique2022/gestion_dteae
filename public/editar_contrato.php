<?php
require_once __DIR__ . '/../app/clases/Contrato.php';

$id_contrato = $_GET['id'] ?? null;
if (!$id_contrato) {
    die("Error: No se proporcionó un ID de contrato.");
}

$contratoModel = new Contrato();
$contrato = $contratoModel->leerUnoCompleto($id_contrato);

if (!$contrato) {
    die("Error: Contrato no encontrado.");
}

$modo = 'editar';
$titulo_pagina = "Editar Contrato: " . htmlspecialchars($contrato['generales']['nombre_contrato']);

// Incluye el formulario maestro
require_once __DIR__ . '/_formulario_contrato.php';
?>