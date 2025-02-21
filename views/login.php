<?php
$titulo = "Iniciar Sesión";
include '../includes/header.php';
?>

<div class="container mt-4">
    <h1>Iniciar Sesión</h1>
    <form action="procesar_login.php" method="POST">
        <div class="form-group">
            <label for="email">Email</label>
            <input type="email" class="form-control" id="email" name="email" required>
        </div>
        <div class="form-group">
            <label for="password">Contraseña</label>
            <input type="password" class="form-control" id="password" name="password" required>
        </div>
        <button type="submit" class="btn btn-primary">Iniciar Sesión</button>
    </form>
</div>

<?php include '../includes/footer.php'; ?>
