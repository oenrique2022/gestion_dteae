<?php include_once '../../includes/header.php'; ?>
<div class="container mt-4">
    <h1>Listado de Entregas</h1>
    <div class="table-responsive">
        <table class="table table-striped datatable" id="tablaEntregas">
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
<script src="<?php echo BASE_URL ?>assets/js/entregas.js"></script>
<?php include_once '../../includes/footer.php'; ?>
