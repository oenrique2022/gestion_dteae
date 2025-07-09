<?php include_once '../../includes/header.php'; ?>

<div class="container mt-4">
    <h1 class="mb-4">Gestión de Entregas</h1>

    <!-- Card de Tabs -->
    <div class="card">
        <div class="card-header">
            <ul class="nav nav-tabs card-header-tabs" id="entregasTab" role="tablist">
                <li class="nav-item">
                    <button class="nav-link active" id="registrar-tab" data-bs-toggle="tab" data-bs-target="#registrar" type="button" role="tab" aria-controls="registrar" aria-selected="true">Registrar/Editar Entrega</button>
                </li>
                <li class="nav-item">
                    <button class="nav-link" id="listado-tab" data-bs-toggle="tab" data-bs-target="#listado" type="button" role="tab" aria-controls="listado" aria-selected="false">Listado de Entregas</button>
                </li>
            </ul>
        </div>
        <div class="card-body">
            <div class="tab-content" id="entregasTabContent">

                <!-- Tab: Registrar/Editar Entrega -->
                <div class="tab-pane fade show active" id="registrar" role="tabpanel" aria-labelledby="registrar-tab">
                    <div class="card mb-4">
                        <div class="card-header">Registrar/Editar Entrega</div>
                        <div class="card-body">
                            <form id="formEntrega">
                                <input type="hidden" id="idEntrega" name="idEntrega">
                                <div class="row">
                                    <div class="col-md-6">
                                        <label for="contrato_id" class="form-label">Contrato</label>
                                        <select id="contrato_id" name="contrato_id" class="form-select" required>
                                            <option value="">Seleccione un contrato</option>
                                            <!-- Opciones dinámicas -->
                                        </select>
                                    </div>
                                    <div class="col-md-6">
                                        <label for="institucion_id" class="form-label">Institución</label>
                                        <select id="institucion_id" name="institucion_id" class="form-select" required>
                                            <option value="">Seleccione una institución</option>
                                            <!-- Opciones dinámicas -->
                                        </select>
                                    </div>
                                </div>
                                <div class="row mt-3">
                                    <div class="col-md-6">
                                        <label for="fecha_entrega" class="form-label">Fecha de Entrega</label>
                                        <input type="date" id="fecha_entrega" name="fecha_entrega" class="form-control" required>
                                    </div>
                                    <div class="col-md-6">
                                        <label for="estado" class="form-label">Estado</label>
                                        <select id="estado" name="estado" class="form-select" required>
                                            <option value="En proceso">En proceso</option>
                                            <option value="Entregado">Entregado</option>
                                        </select>
                                    </div>
                                </div>
                                <div class="row mt-3">
                                    <div class="col-md-6">
                                        <label for="firma_responsable" class="form-label">Persona que Firma</label>
                                        <input type="text" id="firma_responsable" name="firma_responsable" class="form-control">
                                    </div>
                                    <div class="col-md-6">
                                        <label for="comentarios" class="form-label">Comentarios Internos</label>
                                        <textarea id="comentarios" name="comentarios" class="form-control" rows="2"></textarea>
                                    </div>
                                </div>
                                <div class="text-end mt-4">
                                    <button type="submit" class="btn btn-primary">Guardar Entrega</button>
                                </div>
                            </form>
                        </div>
                    </div>

                    <!-- Card para Detalle -->
                    <div class="card mb-4">
                        <div class="card-header">Agregar Equipos a Entrega</div>
                        <div class="card-body">
                            <form id="formDetalleEntrega" class="mb-3">
                                <input type="hidden" id="id_entrega" name="id_entrega">
                                <div class="row">
                                    <div class="col-md-4">
                                        <label for="equipo_id" class="form-label">Equipo</label>
                                        <select id="equipo_id" name="equipo_id" class="form-select" required>
                                            <option value="">Seleccione un equipo</option>
                                            <!-- Opciones dinámicas -->
                                        </select>
                                    </div>
                                    <div class="col-md-2">
                                        <label for="cantidad" class="form-label">Cantidad</label>
                                        <input type="number" id="cantidad" name="cantidad" class="form-control" required>
                                    </div>
                                    <div class="col-md-3">
                                        <label for="precio" class="form-label">Precio</label>
                                        <input type="number" step="0.01" id="precio" name="precio" class="form-control" required>
                                    </div>
                                    <div class="col-md-3">
                                        <label for="comentario" class="form-label">Comentario</label>
                                        <input type="text" id="comentario" name="comentario" class="form-control">
                                    </div>
                                </div>
                                <div class="text-end mt-4">
                                    <button type="submit" class="btn btn-primary">Agregar Equipo</button>
                                </div>
                            </form>
                            <table id="tablaDetalles" class="table table-striped mt-3">
                                <thead>
                                    <tr>
                                        <th>Equipo</th>
                                        <th>Cantidad</th>
                                        <th>Precio</th>
                                        <th>Comentario</th>
                                        <th>Acciones</th>
                                    </tr>
                                </thead>
                                <tbody id="detalleEntregaBody">
                                    <!-- Contenido dinámico -->
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>

                <!-- Tab: Listado de Entregas -->
                <div class="tab-pane fade" id="listado" role="tabpanel" aria-labelledby="listado-tab">
                    <div class="table-responsive">
                        <table id="tablaEntregas" class="table table-striped datatable">
                            <thead>
                                <tr>
                                    <th>Código Infraestructura</th>
                                    <th>Nombre Institución</th>
                                    <th>Contrato</th>
                                    <th>Fecha de Entrega</th>
                                    <th>Comentarios</th>
                                    <th>Estado</th>
                                    <th>Acciones</th>
                                </tr>
                            </thead>
                            <tbody>
                                <!-- Filas dinámicas -->
                            </tbody>
                        </table>
                    </div>
                </div>

            </div>
        </div>
    </div>
</div>

<script src="<?php echo BASE_URL ?>assets/js/entregas.js"></script>
<?php include_once '../../includes/footer.php'; ?>