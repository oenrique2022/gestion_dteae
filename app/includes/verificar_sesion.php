<?php
// Usamos esta directiva para ser más estrictos con los tipos de datos
declare(strict_types=1);

// Si no hay una sesión activa, la iniciamos.
// Esto es necesario en cada página que vaya a usar sesiones.
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

/**
 * Función para redirigir al usuario a la página de login.
 * Construye la URL de forma dinámica para funcionar en cualquier servidor.
 */
function redirigirALogin(): void {
    // Destruir cualquier dato de sesión por seguridad
    session_destroy();
    
    // Construir la URL de forma dinámica
    $protocol = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? "https" : "http";
    $host = $_SERVER['HTTP_HOST'];
    
    // Obtenemos la ruta de la carpeta 'public'
    $path = rtrim(str_replace('\\', '/', dirname($_SERVER['PHP_SELF'])), '/');
    $publicPath = substr($path, 0, strrpos($path, '/')); // Asume que 'includes' está un nivel dentro de 'app'

    // Redirigir y detener el script
    header("Location: {$protocol}://{$host}{$publicPath}/login.php");
    exit();
}

// La regla de seguridad principal:
// Si la variable de sesión 'id_usuario' NO existe, significa que el usuario no está logueado.
if (!isset($_SESSION['id_usuario'])) {
    redirigirALogin();
}