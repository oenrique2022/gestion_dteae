</main> <footer class="bg-light text-center py-3 mt-auto border-top">
    <div class="container">
        <span>© <?php echo date('Y'); ?> - <?php echo APP_NAME; ?></span>
    </div>
</footer>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>

<script src="<?php echo APP_URL; ?>/assets/js/app.js"></script>

<?php if (isset($pagina_js)): ?>
    <script src="<?php echo APP_URL; ?>/assets/js/modules/<?php echo $pagina_js; ?>"></script>
<?php endif; ?>

</body>
</html>