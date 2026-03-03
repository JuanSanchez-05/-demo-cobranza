<?php
/**
 * CONFIGURACIÓN INICIAL DEL SISTEMA
 * Script para crear el usuario administrador y verificar la base de datos
 */

// Incluir configuración
require_once 'config/config.php';

$mensaje = '';
$error = '';

// Procesar formulario
if ($_POST) {
    $accion = $_POST['accion'] ?? '';
    
    if ($accion === 'crear_admin') {
        $telefono = trim($_POST['telefono']);
        $password = trim($_POST['password']);
        $nombre = trim($_POST['nombre']);
        
        if (empty($telefono) || empty($password) || empty($nombre)) {
            $error = "Todos los campos son requeridos";
        } else {
            try {
                $db = getDB();
                
                // Verificar si ya existe un administrador
                $stmt = $db->prepare("SELECT COUNT(*) as count FROM usuarios WHERE rol = 'administrador'");
                $stmt->execute();
                $count = $stmt->fetch()['count'];
                
                if ($count > 0) {
                    // Actualizar admin existente
                    $hash = password_hash($password, PASSWORD_DEFAULT);
                    $stmt = $db->prepare("UPDATE usuarios SET telefono = ?, password = ?, nombre = ? WHERE rol = 'administrador' LIMIT 1");
                    $stmt->execute([$telefono, $hash, $nombre]);
                    $mensaje = "✅ Administrador actualizado exitosamente";
                } else {
                    // Crear nuevo admin
                    $hash = password_hash($password, PASSWORD_DEFAULT);
                    $stmt = $db->prepare("INSERT INTO usuarios (telefono, password, rol, nombre) VALUES (?, ?, 'administrador', ?)");
                    $stmt->execute([$telefono, $hash, $nombre]);
                    $mensaje = "✅ Administrador creado exitosamente";
                }
                
            } catch (Exception $e) {
                $error = "❌ Error: " . $e->getMessage();
            }
        }
    }
    
    elseif ($accion === 'ejecutar_sql') {
        try {
            $db = getDB();
            
            // Leer y ejecutar el script SQL
            $sql = file_get_contents('script_bd_completo.sql');
            
            // Dividir en consultas individuales
            $queries = array_filter(array_map('trim', explode(';', $sql)));
            
            foreach ($queries as $query) {
                if (!empty($query) && !preg_match('/^(--|\/\*|CREATE DATABASE|USE)/', $query)) {
                    $db->exec($query);
                }
            }
            
            $mensaje = "✅ Base de datos configurada exitosamente";
            
        } catch (Exception $e) {
            $error = "❌ Error ejecutando SQL: " . $e->getMessage();
        }
    }
}

// Verificar estado de la base de datos
$db_status = [];
try {
    $db = getDB();
    $db_status['conexion'] = '✅ Conectado';
    
    // Verificar tablas
    $tables = $db->query("SHOW TABLES")->fetchAll();
    $db_status['tablas'] = count($tables) . ' tablas encontradas';
    
    // Verificar administradores
    $admins = $db->query("SELECT COUNT(*) as count FROM usuarios WHERE rol = 'administrador'")->fetch();
    $db_status['admin'] = $admins['count'] . ' administrador(es)';
    
} catch (Exception $e) {
    $db_status['error'] = '❌ ' . $e->getMessage();
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Configuración Inicial - Sistema de Cobranza</title>
    <style>
        body { font-family: Arial, sans-serif; max-width: 800px; margin: 0 auto; padding: 20px; background: #f5f5f5; }
        .card { background: white; padding: 20px; margin: 20px 0; border-radius: 8px; box-shadow: 0 2px 4px rgba(0,0,0,0.1); }
        .form-group { margin: 15px 0; }
        label { display: block; font-weight: bold; margin-bottom: 5px; }
        input[type="text"], input[type="password"] { width: 100%; padding: 10px; border: 1px solid #ddd; border-radius: 4px; box-sizing: border-box; }
        .btn { padding: 12px 20px; border: none; border-radius: 4px; cursor: pointer; text-decoration: none; display: inline-block; margin: 5px; }
        .btn-primary { background: #007bff; color: white; }
        .btn-success { background: #28a745; color: white; }
        .btn-warning { background: #ffc107; color: black; }
        .btn:hover { opacity: 0.9; }
        .alert { padding: 15px; margin: 20px 0; border-radius: 4px; }
        .alert-success { background: #d4edda; color: #155724; border: 1px solid #c3e6cb; }
        .alert-danger { background: #f8d7da; color: #721c24; border: 1px solid #f5c6cb; }
        .status-grid { display: grid; grid-template-columns: 1fr 1fr; gap: 10px; }
        .status-item { padding: 10px; background: #f8f9fa; border-left: 4px solid #28a745; }
        h1, h2 { color: #333; }
        .header { text-align: center; margin-bottom: 30px; }
    </style>
</head>
<body>
    <div class="header">
        <h1>🔧 Configuración Inicial del Sistema</h1>
        <p>Configure la base de datos y cree el usuario administrador</p>
    </div>

    <?php if ($mensaje): ?>
        <div class="alert alert-success"><?php echo $mensaje; ?></div>
    <?php endif; ?>

    <?php if ($error): ?>
        <div class="alert alert-danger"><?php echo $error; ?></div>
    <?php endif; ?>

    <!-- Estado de la Base de Datos -->
    <div class="card">
        <h2>📊 Estado de la Base de Datos</h2>
        <div class="status-grid">
            <?php foreach ($db_status as $key => $value): ?>
                <div class="status-item">
                    <strong><?php echo ucfirst($key); ?>:</strong> <?php echo $value; ?>
                </div>
            <?php endforeach; ?>
        </div>
    </div>

    <!-- Ejecutar Script SQL -->
    <div class="card">
        <h2>🗃️ Configurar Base de Datos</h2>
        <p>Si las tablas no existen, ejecute el script de base de datos:</p>
        <form method="POST">
            <input type="hidden" name="accion" value="ejecutar_sql">
            <button type="submit" class="btn btn-warning">📜 Ejecutar script_bd_completo.sql</button>
        </form>
    </div>

    <!-- Crear/Actualizar Administrador -->
    <div class="card">
        <h2>👤 Crear/Actualizar Usuario Administrador</h2>
        <form method="POST">
            <input type="hidden" name="accion" value="crear_admin">
            
            <div class="form-group">
                <label>Teléfono (Usuario):</label>
                <input type="text" name="telefono" value="admin123" required>
            </div>

            <div class="form-group">
                <label>Contraseña:</label>
                <input type="password" name="password" required>
            </div>

            <div class="form-group">
                <label>Nombre Completo:</label>
                <input type="text" name="nombre" value="Administrador Principal" required>
            </div>

            <button type="submit" class="btn btn-success">👑 Crear/Actualizar Administrador</button>
        </form>
    </div>

    <!-- Acceder al Sistema -->
    <div class="card">
        <h2>🚀 Acceder al Sistema</h2>
        <p>Una vez configurado el administrador, puede acceder al sistema:</p>
        <a href="index.php" class="btn btn-primary">🏠 Ir al Sistema de Cobranza</a>
    </div>

    <!-- Herramientas Adicionales -->
    <div class="card">
        <h2>🛠️ Herramientas</h2>
        <a href="super_admin.php" class="btn btn-primary">⚙️ Panel Super Admin</a>
        <a href="test_hash.php" class="btn btn-primary">🔑 Generar Hashes</a>
    </div>
</body>
</html>