<?php
require_once '../../includes/header.php';
?>

<div class="container mt-4">
    <h1 class="mb-4">Gestión de Equipos</h1>

    <!-- Formulario para registrar equipos -->
    <div class="card mb-4">
        <div class="card-header">Registrar Equipo</div>
        <div class="card-body">
            
<div id="spinner" style="display: none; align-items: center; justify-content: center; position: fixed; top: 0; left: 0; width: 100%; height: 100%; background-color: rgba(0, 0, 0, 0.5); z-index: 1050;">
<div class="spinner-border text-light" role="status">
<span class="visually-hidden">Guardando...</span>
</div>
</div>
            <form id="formEquipo" method="POST">
                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label for="codigo" class="form-label">Código del Equipo</label>
                        <input type="text" class="form-control" id="codigo_equipo" name="codigo_equipo" required>
                    </div>
                    <div class="col-md-6 mb-3">
                        <label for="nombre" class="form-label">Nombre del Equipo</label>
                        <input type="text" class="form-control" id="nombre_equipo" name="nombre_equipo" required>
                    </div>
                </div>
                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label for="tipo" class="form-label">Tipo de Equipo</label>
                        <select class="form-select" id="id_tipo_equipo" name="id_tipo_equipo" required>
                            <option value="">Seleccione un tipo</option>
                            <option value="1">ROBOTIX</option>
                        </select>
                    </div>
                    <div class="col-md-6 mb-3">
                        <label for="estado" class="form-label">Estado</label>
                        <select class="form-select" id="estado" name="estado" required>
                            <option value="En inventario">En inventario</option>
                            <option value="Entregado">Entregado</option>
                        </select>
                    </div>
                </div>
                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label for="fecha_adquisicion" class="form-label">Fecha de Adquisición</label>
                        <input type="date" class="form-control" id="fecha_adquisicion" name="fecha_adquisicion">
                    </div>
                    <div class="col-md-6 mb-3">
                        <label for="descripcion" class="form-label">Descripción</label>
                        <textarea class="form-control" id="descripcion" name="descripcion"></textarea>
                    </div>
                </div>
                <div class="text-end">
                    <button type="submit" class="btn btn-primary">Guardar</button>
                </div>
            </form>
        </div>
    </div>

    <!-- Tabla para listar equipos -->
    <div class="card">
        <div class="card-header">Listado de Equipos</div>
        <div class="card-body">
            <table id="tablaEquipos" class="table table-striped datatable">
                <thead>
                    <tr>
                        <th>Código</th>
                        <th>Nombre</th>
                        <th>Tipo</th>
                        <th>Estado</th>
                        <th>Fecha Adquisición</th>
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

<script src="<?php echo BASE_URL ?>assets/js/equipos.js"></script>
<?php require_once '../../includes/footer.php'; ?>
