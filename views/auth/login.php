<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - Sistema de Cobranza</title>
    <link rel="stylesheet" href="<?php echo BASE_URL; ?>assets/css/style.css">
</head>
<body class="login-page">
    <div class="login-container">
        <div class="login-box">
            <h1>Sistema de Gestión de Cobranza</h1>
            <p class="subtitle">Inicie sesión con su teléfono y contraseña</p>
            
            <?php if (isset($_GET['error']) && $_GET['error'] === 'acceso_denegado'): ?>
                <div class="alert alert-error">Acceso denegado</div>
            <?php endif; ?>
            
            <?php if ($error): ?>
                <div class="alert alert-error"><?php echo htmlspecialchars($error); ?></div>
            <?php endif; ?>
            
            <form method="POST" action="">
                <div class="form-group">
                    <label for="telefono">📱 Teléfono</label>
                    <input type="text" id="telefono" name="telefono" required 
                           placeholder="Ej: 5550001" autocomplete="off">
                </div>
                
                <div class="form-group">
                    <label for="password">🔒 Contraseña</label>
                    <input type="password" id="password" name="password" required 
                           placeholder="Ingresa tu contraseña">
                </div>
                
                <button type="submit" name="login" class="btn btn-primary btn-block">
                    Iniciar Sesión
                </button>
            </form>
        </div>
    </div>
</body>
</html>

