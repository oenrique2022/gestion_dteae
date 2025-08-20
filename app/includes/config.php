<?php
// Configuración de la Base de Datos
define('DB_HOST', '203.161.62.79');
define('DB_USER', 'dtae');
define('DB_PASS', 'dtae2025@');
define('DB_NAME', 'desarrollo_dteae_administracion');
define('DB_CHARSET', 'utf8mb4');

// --- INICIO DEL CÓDIGO DINÁMICO PARA LA URL ---

// 1. Detectar si es HTTP o HTTPS
$protocol = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? "https" : "http";

// 2. Obtener el host (localhost, la IP, o el dominio)
$host = $_SERVER['HTTP_HOST'];

// 3. Obtener la ruta base donde está el proyecto
$script_name = str_replace('\\', '/', dirname($_SERVER['SCRIPT_NAME']));
// Si el proyecto está en la raíz, la ruta es vacía, si no, es /nombre_carpeta
$base_path = (substr($script_name, -7) === '/public') ? dirname($script_name) : $script_name;
$base_path = rtrim($base_path, '/'); // Limpiar la barra final si existe

// 4. Construir y definir la URL completa de la carpeta 'public'
define('APP_URL', $protocol . "://" . $host . $base_path . '/public');

// --- FIN DEL CÓDIGO DINÁMICO ---


// Configuración de la aplicación
define('APP_NAME', 'Gestión de Contratos');
// --- RUTA RAÍZ DEL PROYECTO EN EL SERVIDOR ---
// dirname(__DIR__, 2) sube dos niveles de directorio,
define('PROJECT_ROOT', dirname(__DIR__, 2));  