<?php
session_start();
session_destroy();
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Iniciar Sesión</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css">
    <style>
        body {
           background-color: #e3f2fd;
            display: flex;
            justify-content: center;
            align-items: center;
            height: 100vh;
        }
        .login-container {
            width: 100%;
            max-width: 400px;
        }
        .login-card {
            padding: 2rem;
            border-radius: 12px;
            box-shadow: 0px 4px 10px rgba(0, 0, 0, 0.1);
            background-color: #ffffff;
        }
        .form-control {
            padding-left: 40px;
        }
        .input-group-text {
            width: 40px;
            justify-content: center;
        }
        .btn-primary {
            width: 100%;
            border-radius: 50px;
            font-weight: bold;
        }
        .btn-primary:hover {
            background-color: #0056b3;
        }
    </style>
</head>
<body>
    <div class="login-container">
        <div class="login-card">
            <h2 class="text-center mb-4">Iniciar Sesión </h2>
            <div style="font-size: small; color: #00008b; text-align: center;">Departamento de Tecnologías Emergentes Aplicadas a la Educación</div>
                <?php
                    if (isset($_GET['error'])) {
                    echo '<div class="alert alert-danger text-center" role="alert">Usuario o clave incorrecta</div>';
                    }
                ?>
            <form action="procesar_login" method="POST">
                <div class="mb-3">
                    <label for="email" class="form-label">Usuario</label>
                    <div class="input-group">
                        <span class="input-group-text"><i class="fas fa-envelope"></i></span>
                        <input type="text" class="form-control" id="email" name="email" required>
                    </div>
                </div>
                <div class="mb-3">
                    <label for="password" class="form-label">Contraseña</label>
                    <div class="input-group">
                        <span class="input-group-text"><i class="fas fa-lock"></i></span>
                        <input type="password" class="form-control" id="password" name="password" required>
                    </div>
                </div>
                <button type="submit" class="btn btn-primary">Iniciar Sesión</button>
            </form>
        </div>
    </div>

</body>
</html>
