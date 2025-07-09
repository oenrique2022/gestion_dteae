<div class="container mt-4">
    <h1 class="mb-4">Gestión de Equipos Asociados a Contratos</h1>
    
    <div class="row">
        <!-- Columna Izquierda: Formulario -->
        <div class="col-md-4 mb-4">
            <div class="card">
                <div class="card-header">Agregar Equipo al Contrato</div>
                <div class="card-body">
                    <form id="formAsociarEquipo">
                        <input type="hidden" id="contrato_id_equipo" name="contrato_id_equipo">
                        <div class="mb-3">
                            <label for="equipo_id" class="form-label">Equipo</label>
                            <select id="equipo_id" name="equipo_id" class="form-select" required>
                                <option value="">Seleccione un equipo</option>
                                <!-- Opciones dinámicas -->
                                 <?php 
                                    $equipos=new Equipo;
                                    foreach($equipos->listarEquipos() as $datos){
                                        echo "<option value='".$datos['id_equipo']."'>".$datos['nombre_equipo']."</option>";  
                                    }
                                 ?>
                            </select>
                        </div>
                        <div class="mb-3">
                            <label for="cantidad" class="form-label">Cantidad</label>
                            <input type="number" id="cantidad" name="cantidad" class="form-control" required>
                        </div>
                        <div class="mb-3">
                            <label for="marca" class="form-label">Marca</label>
                            <input type="text" id="marca" name="marca" class="form-control">
                        </div>
                        <div class="mb-3">
                            <label for="precio" class="form-label">Precio</label>
                            <input type="number" step="0.01" id="precio" name="precio" class="form-control" required>
                        </div>
                        <div class="text-end">
                            <button type="submit" class="btn btn-primary">Agregar Equipo</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
        
        <!-- Columna Derecha: Tabla -->
        <div class="col-md-8">
            <div class="card">
                <div class="card-header">Equipos Asociados</div>
                <div class="card-body">
                    <table id="tablaEquiposAsociados" class="table table-striped table-hover datatable">
                        <thead>
                            <tr>
                                <th>Equipo</th>
                                <th>Cantidad</th>
                                <th>Marca</th>
                                <th>Precio</th>
                                <th>Total</th>
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
