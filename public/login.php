<?php
session_start();
// Si el usuario ya está logueado, lo enviamos al panel principal.
if (isset($_SESSION['id_usuario'])) {
    header('Location: index.php');
    exit();
}
// Incluimos la configuración para usar variables como APP_NAME
require_once __DIR__ . '/../app/includes/config.php'; 
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Iniciar Sesión - <?= APP_NAME ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <style>
        body { background-color: #f0f2f5; }
        .login-card { max-width: 420px; margin: 5rem auto; border: none; }
        .card-body { padding: 2.5rem; }
    </style>
</head>
<body>
    <div class="container">
        <div class="card login-card shadow-lg">
            <div class="card-body">
                <h3 class="card-title text-center mb-4">
                    <i class="fas fa-file-signature text-primary me-2"></i><?= APP_NAME ?> 
                </h3>
                <form id="loginForm" novalidate>
                    <div class="mb-3">
                        <label for="correo" class="form-label">Correo Electrónico</label>
                        <input type="email" class="form-control" id="correo" name="correo" required>
                    </div>
                    <div class="mb-3">
                        <label for="password" class="form-label">Contraseña</label>
                        <input type="password" class="form-control" id="password" name="password" required>
                    </div>
                    <div id="error-message" class="alert alert-danger d-none p-2 text-center" role="alert"></div>
                    <div class="d-grid mt-4">
                        <button type="submit" class="btn btn-primary fw-bold">Ingresar</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script src="assets/js/login.js"></script> 
</body>
</html>