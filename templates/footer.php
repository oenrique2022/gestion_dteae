</main> <footer class="bg-light text-center py-3 mt-auto border-top">
    <div class="container">
        <span>© <?php echo date('Y'); ?> - <?php echo APP_NAME; ?></span>
    </div>
</footer>

<script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>

<script src="<?php echo APP_URL; ?>/assets/js/app.js"></script>
<script>
window.APP_PERMISOS = {
    esAdmin: <?php echo usuarioEsAdmin() ? 'true' : 'false'; ?>,
    puedeEscribir: <?php echo usuarioPuedeEscribir() ? 'true' : 'false'; ?>,
    puedeEliminar: <?php echo usuarioPuedeEliminar() ? 'true' : 'false'; ?>
};
</script>

<?php if (!empty($extra_scripts) && is_array($extra_scripts)): ?>
<?php foreach ($extra_scripts as $url): ?>
<script src="<?php echo htmlspecialchars((string) $url, ENT_QUOTES, 'UTF-8'); ?>"></script>
<?php endforeach; ?>
<?php endif; ?>

<?php if (isset($pagina_js)): ?>
    <script src="<?php echo APP_URL; ?>/assets/js/modules/<?php echo $pagina_js; ?>"></script>
<?php endif; ?>

</body>
</html>