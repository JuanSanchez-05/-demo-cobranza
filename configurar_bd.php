<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Configurar Base de Datos - Demo Cobranza</title>
    <style>
        body { font-family: Arial, sans-serif; max-width: 800px; margin: 20px auto; padding: 20px; }
        .form-group { margin-bottom: 15px; }
        label { display: block; margin-bottom: 5px; font-weight: bold; }
        input { padding: 8px; width: 200px; border: 1px solid #ddd; }
        button { padding: 10px 20px; background: #007bff; color: white; border: none; cursor: pointer; }
        button:hover { background: #0056b3; }
        .result { margin-top: 20px; padding: 15px; border-radius: 5px; }
        .success { background: #d4edda; border: 1px solid #c3e6cb; color: #155724; }
        .error { background: #f8d7da; border: 1px solid #f5c6cb; color: #721c24; }
        .warning { background: #fff3cd; border: 1px solid #ffeaa7; color: #856404; }
        pre { background: #f8f9fa; padding: 10px; border-radius: 3px; overflow-x: auto; }
    </style>
</head>
<body>
    <h1>🔧 Configurar Base de Datos</h1>
    <p>Este formulario configurará la base de datos MySQL para el sistema de cobranza.</p>

    <?php
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $host = $_POST['host'] ?? 'localhost';
        $usuario = $_POST['usuario'] ?? 'root';
        $password = $_POST['password'] ?? '';
        $puerto = $_POST['puerto'] ?? 3306;
        
        echo "<div class='result'>";
        
        try {
            // Conectar a MySQL
            $dsn = "mysql:host=$host;port=$puerto;charset=utf8mb4";
            $pdo = new PDO($dsn, $usuario, $password);
            $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            
            echo "<div class='success'>";
            echo "✅ <strong>Conexión exitosa a MySQL</strong><br>";
            echo "Host: $host:$puerto<br>";
            echo "Usuario: $usuario<br>";
            echo "</div>";
            
            // Crear base de datos
            $pdo->exec("CREATE DATABASE IF NOT EXISTS cobranza CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci");
            echo "<p>✅ Base de datos 'cobranza' creada o ya existe</p>";
            
            // Seleccionar la base de datos
            $pdo->exec("USE cobranza");
            echo "<p>✅ Usando base de datos 'cobranza'</p>";
            
            // Leer archivo SQL
            $sql_file = __DIR__ . '/database/schema.sql';
            if (!file_exists($sql_file)) {
                throw new Exception("❌ Archivo schema.sql no encontrado en: $sql_file");
            }
            
            $sql_content = file_get_contents($sql_file);
            echo "<p>✅ Archivo schema.sql leído correctamente</p>";
            
            // Ejecutar SQL
            $statements = array_filter(array_map('trim', explode(';', $sql_content)));
            $ejecutados = 0;
            
            echo "<p><strong>Ejecutando comandos SQL...</strong></p>";
            echo "<pre>";
            
            foreach ($statements as $statement) {
                if (!empty($statement)) {
                    try {
                        $pdo->exec($statement);
                        $ejecutados++;
                        $preview = substr(preg_replace('/\s+/', ' ', $statement), 0, 60);
                        echo "✅ $preview...\n";
                    } catch (PDOException $e) {
                        if (strpos($e->getMessage(), 'already exists') === false) {
                            echo "⚠️ " . $e->getMessage() . "\n";
                        } else {
                            echo "ℹ️ " . substr($statement, 0, 30) . "... (ya existe)\n";
                        }
                    }
                }
            }
            echo "</pre>";
            
            echo "<p>✅ <strong>$ejecutados comandos SQL ejecutados</strong></p>";
            
            // Verificar datos
            echo "<h3>📊 Verificación de datos:</h3>";
            
            $tables = ['usuarios', 'trabajadores', 'carteras', 'pagos'];
            foreach ($tables as $table) {
                try {
                    $stmt = $pdo->query("SELECT COUNT(*) as total FROM $table");
                    $count = $stmt->fetch()['total'];
                    echo "<p>✅ Tabla <code>$table</code>: $count registros</p>";
                } catch (PDOException $e) {
                    echo "<p>⚠️ Tabla <code>$table</code>: Error - " . $e->getMessage() . "</p>";
                }
            }
            
            // Actualizar config.php si es necesario
            if (!empty($password)) {
                echo "<div class='warning'>";
                echo "<h3>⚠️ Acción requerida</h3>";
                echo "<p>Su MySQL tiene contraseña. Debe actualizar el archivo <code>config/config.php</code>:</p>";
                echo "<pre>'password' => '$password'</pre>";
                echo "</div>";
            }
            
            echo "<div class='success'>";
            echo "<h3>🎉 ¡Configuración completada!</h3>";
            echo "<p>La base de datos ha sido configurada exitosamente.</p>";
            echo "<p><a href='index.php'>🏠 Ir al sistema de cobranza</a></p>";
            echo "</div>";
            
        } catch (Exception $e) {
            echo "<div class='error'>";
            echo "<h3>❌ Error de configuración</h3>";
            echo "<p><strong>Mensaje de error:</strong> " . htmlspecialchars($e->getMessage()) . "</p>";
            echo "</div>";
        }
        
        echo "</div>";
    }
    ?>

    <form method="POST">
        <h3>📋 Configuración de conexión</h3>
        
        <div class="form-group">
            <label for="host">Host de MySQL:</label>
            <input type="text" id="host" name="host" value="<?= $_POST['host'] ?? 'localhost' ?>" required>
        </div>
        
        <div class="form-group">
            <label for="puerto">Puerto:</label>
            <input type="number" id="puerto" name="puerto" value="<?= $_POST['puerto'] ?? '3306' ?>" required>
        </div>
        
        <div class="form-group">
            <label for="usuario">Usuario:</label>
            <input type="text" id="usuario" name="usuario" value="<?= $_POST['usuario'] ?? 'root' ?>" required>
        </div>
        
        <div class="form-group">
            <label for="password">Contraseña:</label>
            <input type="password" id="password" name="password" value="<?= $_POST['password'] ?? '' ?>" placeholder="Dejar vacío si no tiene contraseña">
        </div>
        
        <button type="submit">🔧 Configurar Base de Datos</button>
    </form>

    <div class="result">
        <h3>ℹ️ Información</h3>
        <ul>
            <li><strong>XAMPP por defecto:</strong> Usuario 'root', sin contraseña</li>
            <li><strong>Si olvida su contraseña:</strong> Puede resetearla desde XAMPP Control Panel</li>
            <li><strong>El archivo se eliminará automáticamente después de uso</strong></li>
        </ul>
    </div>
</body>
</html>