<?php
include_once '../../includes/header.php';
?>
<div class="container mt-4">
    <h1 class="mb-4">Gestión de Proveedores</h1>

    <!-- Formulario para registrar proveedor -->
    <div class="card mb-4">
        <div class="card-header">Registrar Proveedor</div>
        <div class="card-body">
            
            <div id="spinner" style="display: none; align-items: center; justify-content: center; position: fixed; top: 0; left: 0; width: 100%; height: 100%; background-color: rgba(0, 0, 0, 0.5); z-index: 1050;">
                <div class="spinner-border text-light" role="status">
                    <span class="visually-hidden">Guardando...</span>
                </div>
            </div>

            <form id="formProveedor" class="mb-4">
                <input type="hidden" id="idProveedor" name="idProveedor">
                <div class="row">
                    <div class="col-md-6">
                        <div class="mb-3">
                            <label for="nombreProveedor" class="form-label">Nombre del Proveedor</label>
                            <input type="text" id="nombre_proveedor" name="nombre_proveedor" class="form-control" required>
                        </div>
                        <div class="mb-3">
                            <label for="telefonoContacto" class="form-label">Teléfono</label>
                            <input type="text" id="telefono_contacto" name="telefono_contacto" class="form-control">
                        </div>
                        <div class="mb-3">
                            <label for="emailContacto" class="form-label">Email</label>
                            <input type="email" id="email_contacto" name="email_contacto" class="form-control">
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="mb-3">
                            <label for="contacto" class="form-label">Contacto</label>
                            <input type="text" id="contacto" name="contacto" class="form-control">
                        </div>
                        <div class="mb-3">
                            <label for="descripcion" class="form-label">Descripción</label>
                            <textarea id="descripcion" name="descripcion" class="form-control" rows="4"></textarea>
                        </div>
                    </div>
                </div>
                <div class="text-end">
                    <button type="submit" class="btn btn-primary">Guardar</button>
                    <button type="reset" class="btn btn-secondary">Cancelar</button>
                </div>
            </form>
        </div>
    </div>

    <!-- Tabla para listar proveedores -->
    <div class="card">
        <div class="card-header">Listado de Proveedores</div>
        <div class="card-body">
            <table id="tablaProveedores" class="table table-striped datatable">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Nombre</th>
                        <th>Teléfono</th>
                        <th>Email</th>
                        <th>Contacto</th>
                        <th>Descripción</th>
                        <th>Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    <!-- Datos generados dinámicamente -->
                </tbody>
            </table>
        </div>
    </div>
</div>

<script src="<?php echo BASE_URL ?>assets/js/proveedores.js"></script>
<?php
include_once '../../includes/footer.php';
?>
