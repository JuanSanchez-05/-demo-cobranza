<?php
/**
 * Inicializador Simple de Base de Datos
 * URL: https://tu-app.onrender.com/init.php
 */

header('Content-Type: text/html; charset=utf-8');

// Seguridad básica
$authorized = isset($_GET['setup']) && $_GET['setup'] === 'yes';

if (!$authorized) {
    echo "<!DOCTYPE html><html><head><meta charset='UTF-8'><title>Init DB</title></head><body>";
    echo "<h1>🔒 Inicializar Base de Datos</h1>";
    echo "<p>Para continuar, agrega <code>?setup=yes</code> a la URL</p>";
    echo "<p>Ejemplo: <code>init.php?setup=yes</code></p>";
    echo "</body></html>";
    exit;
}

?>
<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Inicializar DB - Sistema Cobranza</title>
    <style>
        body { font-family: Arial, sans-serif; margin: 40px; background: #f5f5f5; }
        .container { background: white; padding: 30px; border-radius: 8px; max-width: 800px; margin: 0 auto; }
        .success { background: #d4edda; color: #155724; padding: 15px; margin: 10px 0; border-radius: 4px; }
        .error { background: #f8d7da; color: #721c24; padding: 15px; margin: 10px 0; border-radius: 4px; }
        .info { background: #d1ecf1; color: #0c5460; padding: 15px; margin: 10px 0; border-radius: 4px; }
        h1 { color: #333; }
        pre { background: #f8f9fa; padding: 10px; border-radius: 4px; overflow-x: auto; }
    </style>
</head>
<body>
    <div class="container">
        <h1>🚀 Inicializando Base de Datos</h1>
        
        <?php
        try {
            // Incluir configuración
            require_once __DIR__ . '/config/config.php';
            
            echo '<div class="info">📡 Conectando a la base de datos...</div>';
            flush();
            
            $db = new Database();
            $conn = $db->getConnection();
            
            echo '<div class="success">✅ Conexión exitosa</div>';
            flush();
            
            // Leer SQL
            $sqlFile = __DIR__ . '/database/schema.sql';
            
            if (!file_exists($sqlFile)) {
                throw new Exception("Archivo schema.sql no encontrado");
            }
            
            echo '<div class="info">📄 Leyendo schema.sql...</div>';
            $sql = file_get_contents($sqlFile);
            
            // Ejecutar SQL
            $statements = array_filter(
                array_map('trim', explode(';', $sql)),
                function($s) { return !empty($s) && !preg_match('/^--/', $s); }
            );
            
            echo '<div class="info">⚙️ Ejecutando ' . count($statements) . ' sentencias SQL...</div>';
            flush();
            
            $success = 0;
            $errors = 0;
            
            foreach ($statements as $stmt) {
                if (trim($stmt)) {
                    try {
                        $conn->exec($stmt);
                        $success++;
                    } catch (PDOException $e) {
                        if (strpos($e->getMessage(), 'already exists') === false) {
                            $errors++;
                            echo '<div class="error">⚠️ ' . htmlspecialchars($e->getMessage()) . '</div>';
                        }
                    }
                }
            }
            
            echo '<div class="success">';
            echo '<strong>✅ Proceso completado</strong><br>';
            echo 'Sentencias ejecutadas: ' . $success . '<br>';
            if ($errors > 0) echo 'Errores: ' . $errors . '<br>';
            echo '</div>';
            
            // Verificar tablas
            echo '<div class="info">🔍 Verificando tablas...</div>';
            
            $tables = ['usuarios', 'carteras', 'tarjetas', 'clientes', 'prestamos', 'pagos', 'cobros'];
            foreach ($tables as $table) {
                try {
                    $stmt = $conn->query("SELECT COUNT(*) FROM $table");
                    $count = $stmt->fetchColumn();
                    echo '<div class="success">✅ ' . $table . ' (' . $count . ' registros)</div>';
                } catch (PDOException $e) {
                    echo '<div class="error">❌ ' . $table . ' (no encontrada)</div>';
                }
            }
            
            // Crear super admin
            echo '<div class="info">👤 Verificando super admin...</div>';
            
            $stmt = $conn->prepare("SELECT COUNT(*) FROM usuarios WHERE username = 'superadmin'");
            $stmt->execute();
            
            if ($stmt->fetchColumn() == 0) {
                $stmt = $conn->prepare("
                    INSERT INTO usuarios (username, password, nombre_completo, rol, activo, created_at) 
                    VALUES ('superadmin', :pass, 'Super Administrador', 'admin', 1, NOW())
                ");
                $stmt->execute([':pass' => password_hash('admin123', PASSWORD_DEFAULT)]);
                echo '<div class="success">✅ Super admin creado</div>';
            } else {
                echo '<div class="success">✅ Super admin existe</div>';
            }
            
            // Mensaje final
            echo '<div class="success" style="margin-top: 30px; padding: 20px;">';
            echo '<h2>🎉 ¡Setup Completado!</h2>';
            echo '<p><strong>Credenciales:</strong></p>';
            echo '<pre>Usuario: superadmin<br>Password: admin123</pre>';
            echo '<p><a href="index.php" style="color: #007bff; font-weight: bold;">→ Ir al Login</a></p>';
            echo '</div>';
            
            echo '<div class="error" style="margin-top: 20px;">';
            echo '<strong>⚠️ SEGURIDAD:</strong> Elimina este archivo (init.php) del servidor después de usarlo.';
            echo '</div>';
            
        } catch (Exception $e) {
            echo '<div class="error">';
            echo '<strong>❌ Error:</strong> ' . htmlspecialchars($e->getMessage());
            echo '</div>';
            
            echo '<div class="info" style="margin-top: 20px;">';
            echo '<strong>💡 Soluciones:</strong><br>';
            echo '1. Verifica las variables de entorno en Render<br>';
            echo '2. Asegúrate de que la BD esté activa<br>';
            echo '3. Revisa los logs de Render';
            echo '</div>';
        }
        ?>
    </div>
</body>
</html>
