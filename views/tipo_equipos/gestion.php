<?php
include_once '../../includes/header.php';
?>

<div class="container mt-5">
    <h2>Tipos de Equipos</h2>

    <!-- Formulario de Creación/Edición de Tipo de Equipo -->
    <div id="formularioTipoEquipo">
        <h3 id="tituloFormulario">Nuevo Tipo de Equipo</h3>
        <form id="formTipoEquipo">
            <input type="hidden" id="id_tipo_equipo" name="id_tipo_equipo"> <!-- Para edición -->
            <div class="mb-3">
                <label for="nombre_tipo_equipo" class="form-label">Nombre</label>
                <input type="text" class="form-control" id="nombre_tipo_equipo" name="nombre_tipo_equipo" required>
            </div>
            <div class="mb-3">
                <label for="descripcion" class="form-label">Descripción</label>
                <textarea class="form-control" id="descripcion" name="descripcion"></textarea>
            </div>
            <button type="submit" class="btn btn-primary">Guardar</button>
            <button type="button" class="btn btn-secondary" id="cancelarFormulario">Cancelar</button>
        </form>
    </div>

    <!-- Tabla para mostrar los tipos de equipos -->
    <table id="tablaTiposEquipos" class="table table-striped mt-4">
        <thead>
            <tr>
                <th>Nombre</th>
                <th>Descripción</th>
                <th>Acciones</th>
            </tr>
        </thead>
        <tbody>
            <!-- Los datos se llenan con JavaScript -->
        </tbody>
    </table>
</div>
<script src="<?php echo BASE_URL ?>assets/js/tipoEquipo.js"></script>
<?php
include_once '../../includes/footer.php';
?>
