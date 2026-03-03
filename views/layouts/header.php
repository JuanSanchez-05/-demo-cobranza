<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <!-- viewport fijo para evitar que el navegador haga zoom automático en móviles -->
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
    <title><?php echo isset($titulo) ? $titulo : 'Sistema de Cobranza'; ?></title>
    <link rel="stylesheet" href="<?php echo BASE_URL; ?>assets/css/style.css">
</head>
<body class="<?php echo $_SESSION['rol'] ?? 'default'; ?>-page">
    <nav class="navbar">
        <!-- botón hamburguesa para dispositivos móviles -->
        <button class="navbar-toggle" aria-label="Abrir menú">&#9776;</button>
        <div class="navbar-brand">
            <h2>Sistema de Gestión de Cobranza</h2>
        </div>
        <div class="navbar-menu">
            <span class="user-info"><?php echo htmlspecialchars($_SESSION['nombre'] ?? ''); ?></span>
            <span class="user-role"><?php echo ucfirst($_SESSION['rol'] ?? ''); ?></span>
            <?php 
            $controller = '';
            switch ($_SESSION['rol']) {
                case 'administrador':
                    $controller = 'AdminController';
                    break;
                case 'trabajador':
                    $controller = 'TrabajadorController';
                    break;
                case 'cliente':
                    $controller = 'ClienteController';
                    break;
            }
            ?>
            <a href="<?php echo BASE_URL; ?>controllers/<?php echo $controller; ?>.php?action=logout" class="btn btn-sm btn-danger">
                Cerrar Sesión
            </a>
        </div>
    </nav>

