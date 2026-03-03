<?php
// Script para configurar la base de datos
$passwords = ['', 'root', 'password', 'admin']; // Probar contraseñas comunes de XAMPP

foreach ($passwords as $password) {
    try {
        // Conectar a MySQL sin especificar base de datos
        $pdo = new PDO('mysql:host=localhost;charset=utf8mb4', 'root', $password);
        $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        
        echo "✓ Conectado a MySQL con contraseña: " . ($password ?: '(vacía)') . "\n";
        break;
    } catch (PDOException $e) {
        if (end($passwords) === $password) {
            echo "❌ No se pudo conectar con ninguna contraseña común\n";
            echo "Intente configurar manualmente la contraseña en config/config.php\n";
            exit(1);
        }
        continue;
    }
}

try {
    // Crear base de datos si no existe
    $pdo->exec("CREATE DATABASE IF NOT EXISTS cobranza CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci");
    echo "✓ Base de datos 'cobranza' creada o ya existe\n";
    
    // Seleccionar la base de datos
    $pdo->exec("USE cobranza");
    echo "✓ Usando base de datos 'cobranza'\n";
    
    // Leer y ejecutar el archivo schema.sql
    $sql_file = __DIR__ . '/database/schema.sql';
    if (!file_exists($sql_file)) {
        throw new Exception("Archivo schema.sql no encontrado");
    }
    
    $sql_content = file_get_contents($sql_file);
    
    // Dividir en statements individuales
    $statements = explode(';', $sql_content);
    
    foreach ($statements as $statement) {
        $statement = trim($statement);
        if (!empty($statement)) {
            try {
                $pdo->exec($statement);
                echo "✓ Ejecutado: " . substr($statement, 0, 50) . "...\n";
            } catch (PDOException $e) {
                // Algunos statements pueden fallar si la tabla ya existe, eso está bien
                if (!strpos($e->getMessage(), 'already exists')) {
                    echo "⚠ Warning: " . $e->getMessage() . "\n";
                }
            }
        }
    }
    
    echo "\n✅ Base de datos configurada exitosamente!\n";
    
    // Verificar datos
    $stmt = $pdo->query("SELECT COUNT(*) as total FROM usuarios");
    $users = $stmt->fetch()['total'];
    echo "📊 Usuarios creados: $users\n";
    
    $stmt = $pdo->query("SELECT COUNT(*) as total FROM carteras");
    $carteras = $stmt->fetch()['total'];
    echo "📊 Carteras creadas: $carteras\n";
    
    $stmt = $pdo->query("SELECT COUNT(*) as total FROM pagos");
    $pagos = $stmt->fetch()['total'];
    echo "📊 Pagos creados: $pagos\n";
    
    // Actualizar config.php con la contraseña correcta si es necesaria
    if (!empty($password)) {
        echo "\n⚠ IMPORTANTE: Actualize config/config.php para usar contraseña MySQL: '$password'\n";
    }
    
} catch (Exception $e) {
    echo "❌ Error: " . $e->getMessage() . "\n";
    exit(1);
}
?>