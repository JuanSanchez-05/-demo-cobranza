    <footer class="site-footer">
        <div class="footer-inner">
            <?php
            $controller = '';
            switch ($_SESSION['rol'] ?? '') {
                case 'administrador': $controller = 'AdminController'; break;
                case 'trabajador': $controller = 'TrabajadorController'; break;
                case 'cliente': $controller = 'ClienteController'; break;
            }
            if ($controller): ?>
                <a href="<?php echo BASE_URL; ?>controllers/<?php echo $controller; ?>.php?action=logout" class="btn btn-sm btn-danger footer-logout">Cerrar Sesión</a>
            <?php endif; ?>
        </div>
    </footer>
    <script src="<?php echo BASE_URL; ?>assets/js/main.js"></script>
</body>
</html>

