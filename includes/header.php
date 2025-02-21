<?php
include_once "config.php";
ini_set('display_errors', '1');


?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $titulo ?? "Sistema de Gestión Administrativa"; ?></title>
    <link rel="stylesheet" href="/gestion_dteae/assets/css/bootstrap.min.css">
    <link rel="stylesheet" href="/gestion_dteae/assets/css/estilos.css">
    <link rel="stylesheet" href="/gestion_dteae/assets/css/colores.css">
    <link rel="stylesheet" href="/gestion_dteae/assets/css/datatables.css">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons/font/bootstrap-icons.css" rel="stylesheet">
<script>
const BASE_URL = "<?= BASE_URL ?>";
</script>
</head>
<body>
    <!-- Contenedor para la notificación -->
<div id="notificacion" class="notificacion"></div>
<nav class="navbar navbar-expand-lg navbar-dark bg-primary">
    <div class="container-fluid">
        <a class="navbar-brand" href="/dashboard">
            <i class="bi bi-house-door-fill"></i> Gestión Administrativa.
        </a>
        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav" aria-controls="navbarNav" aria-expanded="false" aria-label="Toggle navigation">
            <span class="navbar-toggler-icon"></span>
        </button>
        <div class="collapse navbar-collapse" id="navbarNav">
            <ul class="navbar-nav">
                <li class="nav-item">
                    <a class="nav-link" href="../usuarios/listar_usuarios">
                        <i class="bi bi-people-fill"></i> Usuarios
                    </a>
                </li>
                <!--li class="nav-item">
                    <a class="nav-link" href="../instituciones/gestion.php">
                        <i class="bi bi-building"></i> Instituciones
                    </a>
                </li -->
                <li class="nav-item">
                    <a class="nav-link" href="../tipo_equipos/gestion">
                        <i class="bi bi-grid-3x3-gap-fill"></i> Categorías
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="../equipos/gestion">
                        <i class="bi bi-pc-display"></i> Tecnologías
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="../proveedores/gestion">
                        <i class="bi bi-truck"></i> Proveedores
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="../contratos/gestion">
                        <i class="bi bi-file-earmark-text-fill"></i> Contratos
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="../entregas/gestion">
                        <i class="bi bi-box2-fill"></i> Entregas
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="../capacitaciones/gestion">
                        <i class="bi bi-box2-fill"></i> Capacitaciones
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="../dashboard/dashboard">
                        <i class="bi bi-bar-chart-fill"></i> Informes
                    </a>
                </li>
                <!-- Otros enlaces -->
            </ul>
            <ul class="navbar-nav ms-auto">
                <li class="nav-item">
                    <a class="nav-link" href="../login">
                        <i class="bi bi-box-arrow-right"></i> Cerrar Sesión
                    </a>
                </li>
            </ul>
        </div>
    </div>
</nav>

    <main>