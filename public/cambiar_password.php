<?php
// El guardián protege esta página, asegurando que solo usuarios logueados puedan acceder.
require_once __DIR__ . '/../templates/header.php';
?>

<div class="row justify-content-center">
    <div class="col-md-6">
        <div class="card shadow-sm">
            <div class="card-header">
                <h2 class="h5 mb-0"><i class="fas fa-key me-2"></i>Cambiar mi Contraseña</h2>
            </div>
            <div class="card-body">
                <form id="passwordChangeForm" novalidate>
                    <div class="mb-3">
                        <label for="password_actual" class="form-label">Contraseña Actual</label>
                        <input type="password" class="form-control" id="password_actual" name="password_actual" required>
                    </div>
                    <div class="mb-3">
                        <label for="password_nueva" class="form-label">Nueva Contraseña</label>
                        <input type="password" class="form-control" id="password_nueva" name="password_nueva" required>
                    </div>
                    <div class="mb-3">
                        <label for="password_confirmar" class="form-label">Confirmar Nueva Contraseña</label>
                        <input type="password" class="form-control" id="password_confirmar" name="password_confirmar" required>
                    </div>

                    <div id="password-error-message" class="alert alert-danger d-none p-2" role="alert"></div>
                    <div id="password-success-message" class="alert alert-success d-none p-2" role="alert"></div>

                    <div class="text-end">
                        <button type="submit" class="btn btn-primary">Actualizar Contraseña</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<?php 
// Asignamos un archivo JS dedicado para esta funcionalidad
$pagina_js = 'perfil.js';
require_once __DIR__ . '/../templates/footer.php'; 
?>