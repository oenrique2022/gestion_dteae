<?php include_once '../../includes/header.php'; ?>

<div class="container mt-4">
    <h1 class="mb-4">Gestión de Capacitaciones</h1>

    <!-- Tabs para Formulario y Listado -->
    <div class="card">
        <div class="card-header">
            <ul class="nav nav-tabs card-header-tabs" id="capacitacionesTab" role="tablist">
                <li class="nav-item" role="presentation">
                    <button class="nav-link active" id="formulario-tab" data-bs-toggle="tab" data-bs-target="#formulario" type="button" role="tab" aria-controls="formulario" aria-selected="true">Formulario</button>
                </li>
                <li class="nav-item" role="presentation">
                    <button class="nav-link" id="listado-tab" data-bs-toggle="tab" data-bs-target="#listado" type="button" role="tab" aria-controls="listado" aria-selected="false">Listado</button>
                </li>
            </ul>
        </div>
        <div class="card-body">
            <div class="tab-content" id="capacitacionesTabContent">
                <!-- Tab: Formulario -->
                <div class="tab-pane fade show active" id="formulario" role="tabpanel" aria-labelledby="formulario-tab">
                    <form id="formCapacitacion">
                        <div class="col-md-6">
                        <label for="id_institucion" class="form-label">Institución</label>
                        <select id="id_institucion" name="id_institucion" class="form-select" required>
                        <option value="">Seleccione una institución</option>
                        <!-- Opciones dinámicas -->
                        </select>
                        </div>

                        <input type="hidden" id="idCapacitacion" name="idCapacitacion">
                        <div class="row">
                            <div class="col-md-6">
                                <label for="nombre_capacitacion" class="form-label">Nombre de la Capacitación</label>
                                <input type="text" id="nombre_capacitacion" name="nombre_capacitacion" class="form-control" required>
                            </div>
                            <div class="col-md-6">
                                <label for="id_equipo" class="form-label">Tecnología</label>
                                <select id="id_equipo" name="id_equipo" class="form-select" required>
                                    <option value="">Seleccione un equipo</option>
                                    <!-- Opciones dinámicas -->
                                </select>
                            </div>
                        </div>
                        <div class="row mt-3">
                            <div class="col-md-6">
                                <label for="fecha" class="form-label">Fecha</label>
                                <input type="date" id="fecha" name="fecha" class="form-control" required>
                            </div>
                            <div class="col-md-6">
                                <label for="duracion" class="form-label">Duración (en horas)</label>
                                <input type="number" id="duracion" name="duracion" class="form-control" required>
                            </div>
                        </div>
                        <div class="row mt-3">
                            <div class="col-md-6">
                                <label for="tipo_capacitacion" class="form-label">Tipo de Capacitación</label>
                                <select id="tipo_capacitacion" name="tipo_capacitacion" class="form-select" required>
                                    <option value="">Seleccione un tipo</option>
                                    <option value="Presencial">Presencial</option>
                                    <option value="Virtual Autogestionado">Virtual Autogestionado</option>
                                    <option value="Virtual Tutoriado">Virtual Tutoriado</option>
                                </select>
                            </div>
                            <div class="col-md-3">
                                <label for="docentes_capacitados" class="form-label">Docentes Capacitados</label>
                                <input type="number" id="docentes_capacitados" name="docentes_capacitados" class="form-control">
                            </div>
                            <div class="col-md-3">
                                <label for="estudiantes_capacitados" class="form-label">Estudiantes Capacitados</label>
                                <input type="number" id="estudiantes_capacitados" name="estudiantes_capacitados" class="form-control">
                            </div>
                        </div>
                        <div class="row mt-3">
                            <div class="col-md-12">
                                <label for="descripcion" class="form-label">Descripción</label>
                                <textarea id="descripcion" name="descripcion" class="form-control" rows="3"></textarea>
                            </div>
                        </div>
                        <div class="text-end mt-4">
                            <button type="submit" class="btn btn-primary">Guardar</button>
                        </div>
                    </form>
                </div>

                <!-- Tab: Listado -->
                <div class="tab-pane fade" id="listado" role="tabpanel" aria-labelledby="listado-tab">
                    <table id="tablaCapacitaciones" class="table table-striped datatable">
                        <thead>
                            <tr>
                                <th>Infraestructura</th>
                                <th>Centro Educativo</th>
                                <th>Nombre</th>
                                <th>Equipo</th>
                                <th>Fecha</th>
                                <th>Duración</th>
                                <th>Tipo</th>
                                <th>Docentes</th>
                                <th>Estudiantes</th>
                                <th>Acciones</th>
                            </tr>
                        </thead>
                        <tbody>
                            <!-- Contenido dinámico -->
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

<script src="<?php echo BASE_URL ?>assets/js/capacitaciones.js"></script>
<?php include_once '../../includes/footer.php'; ?>
