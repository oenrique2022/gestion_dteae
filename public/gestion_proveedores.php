<?php require_once __DIR__ . '/../templates/header.php'; ?>

<div class="card shadow-sm">
    <div class="card-header d-flex justify-content-between align-items-center">
        <h2 class="h5 mb-0">
            <i class="fas fa-truck-field me-2"></i>Gestión de Proveedores
        </h2>
        <button id="btnNuevoProveedor" class="btn btn-primary btn-sm">
            <i class="fas fa-plus me-1"></i> Nuevo Proveedor
        </button>
    </div>
    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-striped table-hover">
                <thead class="table-dark">
                    <tr>
                        <th>Nombre</th>
                        <th>Contacto</th>
                        <th>Teléfono</th>
                        <th>Email</th>
                        <th class="text-end">Acciones</th>
                    </tr>
                </thead>
                <tbody id="tablaProveedoresBody">
                    </tbody>
            </table>
        </div>
    </div>
</div>

<div class="modal fade" id="proveedorModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header bg-primary text-white">
                <h5 class="modal-title" id="modalTitle"></h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <form id="proveedorForm">
                    <input type="hidden" name="id_proveedor" id="id_proveedor">
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="nombre_proveedor" class="form-label">Nombre del Proveedor</label>
                            <input type="text" class="form-control" id="nombre_proveedor" name="nombre_proveedor" required>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label for="contacto" class="form-label">Nombre del Contacto</label>
                            <input type="text" class="form-control" id="contacto" name="contacto">
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="telefono_contacto" class="form-label">Teléfono</label>
                            <input type="tel" class="form-control" id="telefono_contacto" name="telefono_contacto">
                        </div>
                        <div class="col-md-6 mb-3">
                            <label for="email_contacto" class="form-label">Email</label>
                            <input type="email" class="form-control" id="email_contacto" name="email_contacto">
                        </div>
                    </div>
                    <div class="mb-3">
                        <label for="descripcion" class="form-label">Descripción</label>
                        <textarea class="form-control" id="descripcion" name="descripcion" rows="3"></textarea>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                        <button type="submit" class="btn btn-primary">Guardar Cambios</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<?php 
 $pagina_js = 'proveedores.js'; // <-- JS a cargar
require_once __DIR__ . '/../templates/footer.php'; ?>