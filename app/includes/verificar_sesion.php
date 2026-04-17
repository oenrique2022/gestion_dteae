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

/**
 * Roles esperados en BD:
 * 1 = Administradores, 2 = Digitadores, 3 = Consulta.
 */
function usuarioRolId(): int {
    return isset($_SESSION['id_rol']) ? (int) $_SESSION['id_rol'] : 0;
}

function usuarioEsAdmin(): bool {
    return usuarioRolId() === 1;
}

function usuarioEsDigitador(): bool {
    return usuarioRolId() === 2;
}

function usuarioEsConsulta(): bool {
    return usuarioRolId() === 3;
}

function usuarioPuedeEscribir(): bool {
    $rol = usuarioRolId();
    return $rol === 1 || $rol === 2;
}

function usuarioPuedeEliminar(): bool {
    return usuarioEsAdmin();
}

function denegarAccesoPagina(): void {
    http_response_code(403);
    echo 'Acceso denegado.';
    exit();
}

function denegarAccesoApi(string $mensaje = 'No autorizado para esta acción.'): void {
    http_response_code(403);
    header('Content-Type: application/json; charset=utf-8');
    echo json_encode(['success' => false, 'message' => $mensaje], JSON_UNESCAPED_UNICODE);
    exit();
}