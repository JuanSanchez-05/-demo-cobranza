<?php
/**
 * Setup Web - Inicialización de Base de Datos desde el Navegador
 * Acceder a: https://tu-app.onrender.com/setup_web.php
 * 
 * IMPORTANTE: Eliminar este archivo después de usarlo en producción
 */

// Seguridad básica: solo permitir en primera ejecución o con clave
$SETUP_KEY = getenv('SETUP_KEY') ?: 'demo2026'; // Cambiar en producción

if (!isset($_GET['key']) || $_GET['key'] !== $SETUP_KEY) {
    die('<h1>⛔ Acceso Denegado</h1><p>Necesitas la clave de setup. Agrega ?key=TU_CLAVE a la URL</p>');
}

?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Setup Base de Datos - Sistema de Cobranza</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body {
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            padding: 20px;
            display: flex;
            align-items: center;
            justify-content: center;
        }
        .container {
            background: white;
            border-radius: 15px;
            box-shadow: 0 20px 60px rgba(0,0,0,0.3);
            padding: 40px;
            max-width: 800px;
            width: 100%;
        }
        h1 {
            color: #333;
            margin-bottom: 10px;
            font-size: 28px;
        }
        .subtitle {
            color: #666;
            margin-bottom: 30px;
            font-size: 14px;
        }
        .status {
            padding: 15px;
            border-radius: 8px;
            margin: 10px 0;
            font-size: 14px;
            line-height: 1.6;
        }
        .success {
            background: #d4edda;
            border: 1px solid #c3e6cb;
            color: #155724;
        }
        .error {
            background: #f8d7da;
            border: 1px solid #f5c6cb;
            color: #721c24;
        }
        .warning {
            background: #fff3cd;
            border: 1px solid #ffeaa7;
            color: #856404;
        }
        .info {
            background: #d1ecf1;
            border: 1px solid #bee5eb;
            color: #0c5460;
        }
        button {
            background: #667eea;
            color: white;
            border: none;
            padding: 12px 30px;
            border-radius: 6px;
            font-size: 16px;
            cursor: pointer;
            margin-top: 20px;
            transition: all 0.3s;
        }
        button:hover {
            background: #5568d3;
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(102, 126, 234, 0.4);
        }
        button:disabled {
            background: #ccc;
            cursor: not-allowed;
            transform: none;
        }
        .step {
            margin: 15px 0;
            padding: 10px;
            border-left: 3px solid #667eea;
            background: #f8f9fa;
        }
        .code {
            background: #2d2d2d;
            color: #f8f8f2;
            padding: 15px;
            border-radius: 6px;
            font-family: 'Courier New', monospace;
            font-size: 13px;
            overflow-x: auto;
            margin: 10px 0;
        }
        pre {
            white-space: pre-wrap;
            word-wrap: break-word;
        }
    </style>
</head>
<body>
    <div class="container">
        <h1>🚀 Setup Base de Datos</h1>
        <p class="subtitle">Sistema de Cobranza - Inicialización Automática</p>

        <?php
        if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['run_setup'])) {
            echo '<div id="setup-log">';
            
            try {
                // Incluir configuración
                require_once __DIR__ . '/config/config.php';
                
                echo '<div class="status info"><strong>📡 Conectando a la base de datos...</strong></div>';
                flush();
                
                $db = new Database();
                $conn = $db->getConnection();
                
                echo '<div class="status success">✅ Conexión exitosa a la base de datos</div>';
                flush();
                
                // Leer el archivo SQL
                $sqlFile = __DIR__ . '/database/schema.sql';
                
                if (!file_exists($sqlFile)) {
                    throw new Exception("No se encontró el archivo schema.sql");
                }
                
                echo '<div class="status info"><strong>📄 Leyendo archivo SQL...</strong></div>';
                $sql = file_get_contents($sqlFile);
                
                // Dividir en sentencias individuales
                $statements = array_filter(
                    array_map('trim', explode(';', $sql)),
                    function($stmt) {
                        return !empty($stmt) && !preg_match('/^--/', $stmt);
                    }
                );
                
                echo '<div class="status info"><strong>⚙️ Ejecutando ' . count($statements) . ' sentencias SQL...</strong></div>';
                flush();
                
                $executed = 0;
                $errors = 0;
                
                foreach ($statements as $statement) {
                    if (trim($statement)) {
                        try {
                            $conn->exec($statement);
                            $executed++;
                        } catch (PDOException $e) {
                            // Ignorar errores de "tabla ya existe"
                            if (strpos($e->getMessage(), 'already exists') === false) {
                                echo '<div class="status warning">⚠️ ' . htmlspecialchars($e->getMessage()) . '</div>';
                                $errors++;
                            }
                        }
                    }
                }
                
                echo '<div class="status success">';
                echo '<strong>✅ Setup completado exitosamente</strong><br>';
                echo '📊 Sentencias ejecutadas: ' . $executed . '<br>';
                if ($errors > 0) {
                    echo '⚠️ Errores (algunos pueden ser normales): ' . $errors . '<br>';
                }
                echo '</div>';
                
                // Verificar que se crearon las tablas
                echo '<div class="status info"><strong>🔍 Verificando tablas creadas...</strong></div>';
                
                $tables = ['usuarios', 'carteras', 'tarjetas', 'clientes', 'prestamos', 'pagos', 'cobros'];
                $tablesFound = [];
                
                foreach ($tables as $table) {
                    try {
                        $stmt = $conn->query("SELECT COUNT(*) FROM $table");
                        $count = $stmt->fetchColumn();
                        $tablesFound[] = "✅ $table ($count registros)";
                    } catch (PDOException $e) {
                        $tablesFound[] = "❌ $table (no encontrada)";
                    }
                }
                
                echo '<div class="step">';
                echo '<strong>Tablas en la base de datos:</strong><br>';
                echo implode('<br>', $tablesFound);
                echo '</div>';
                
                // Crear usuario super admin si no existe
                echo '<div class="status info"><strong>👤 Verificando usuario super admin...</strong></div>';
                
                $stmt = $conn->prepare("SELECT COUNT(*) FROM usuarios WHERE telefono = 'superadmin'");
                $stmt->execute();
                $exists = $stmt->fetchColumn();
                
                if ($exists == 0) {
                    $stmt = $conn->prepare("
                        INSERT INTO usuarios (telefono, password, nombre, rol, activo, fecha_creacion) 
                        VALUES ('superadmin', :password, 'Super Administrador', 'administrador', 1, NOW())
                    ");
                    $stmt->execute([':password' => password_hash('admin123', PASSWORD_DEFAULT)]);
                    echo '<div class="status success">✅ Usuario super admin creado</div>';
                } else {
                    echo '<div class="status success">✅ Usuario super admin ya existe</div>';
                }
                
                echo '<div class="status success" style="margin-top: 20px; padding: 20px;">';
                echo '<h3 style="margin-bottom: 10px;">🎉 ¡Setup Completado Exitosamente!</h3>';
                echo '<p><strong>Ya puedes usar tu aplicación:</strong></p>';
                echo '<div style="margin: 15px 0;">';
                echo '<strong>Usuario:</strong> superadmin<br>';
                echo '<strong>Contraseña:</strong> admin123';
                echo '</div>';
                echo '<p><a href="index.php" style="color: #667eea; font-weight: bold;">→ Ir al Login</a></p>';
                echo '</div>';
                
                echo '<div class="status warning" style="margin-top: 20px;">';
                echo '<strong>⚠️ IMPORTANTE - SEGURIDAD:</strong><br>';
                echo '1. Elimina el archivo <code>setup_web.php</code> del servidor<br>';
                echo '2. Cambia la contraseña del super admin desde el panel<br>';
                echo '3. No compartas la clave de setup';
                echo '</div>';
                
            } catch (Exception $e) {
                echo '<div class="status error">';
                echo '<strong>❌ Error durante el setup:</strong><br>';
                echo htmlspecialchars($e->getMessage());
                echo '</div>';
                
                echo '<div class="status info" style="margin-top: 20px;">';
                echo '<strong>💡 Soluciones posibles:</strong><br>';
                echo '1. Verifica que las variables de entorno estén correctamente configuradas en Render<br>';
                echo '2. Asegúrate de que la base de datos esté activa y accesible<br>';
                echo '3. Revisa los logs de Render para más detalles';
                echo '</div>';
            }
            
            echo '</div>';
            
        } else {
            // Mostrar información y botón para ejecutar
            ?>
            <div class="status info">
                <strong>ℹ️ Información del Sistema</strong><br>
                <strong>Host:</strong> <?php echo htmlspecialchars(getenv('DB_HOST') ?: 'localhost:3307'); ?><br>
                <strong>Base de datos:</strong> <?php echo htmlspecialchars(getenv('DB_NAME') ?: 'cobranza_db'); ?><br>
                <strong>Usuario:</strong> <?php echo htmlspecialchars(getenv('DB_USER') ?: 'root'); ?><br>
                <strong>Entorno:</strong> <?php echo getenv('RENDER') ? 'Render (Producción)' : 'Local (Desarrollo)'; ?>
            </div>

            <div class="step">
                <strong>📋 Este script hará lo siguiente:</strong><br>
                1. Conectar a la base de datos<br>
                2. Crear todas las tablas necesarias<br>
                3. Configurar las relaciones entre tablas<br>
                4. Crear el usuario super administrador<br>
                5. Verificar que todo se instaló correctamente
            </div>

            <div class="status warning">
                <strong>⚠️ Advertencia:</strong> Este script creará o actualizará las tablas en tu base de datos. 
                Puedes ejecutarlo múltiples veces de forma segura.
            </div>

            <form method="POST">
                <button type="submit" name="run_setup">🚀 Ejecutar Setup Ahora</button>
            </form>

            <div class="status info" style="margin-top: 30px;">
                <strong>🔒 Después del setup:</strong><br>
                Por seguridad, elimina este archivo <code>setup_web.php</code> del servidor después de usarlo.
            </div>
            <?php
        }
        ?>
    </div>
</body>
</html>
