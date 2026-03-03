<?php
require_once 'config/config.php';

echo "=== PREPARANDO USUARIOS PARA EL SISTEMA ===\n";

try {
    $db = new Database();
    $conn = $db->getConnection();
    
    // 1. Crear usuario administrador
    $stmt = $conn->prepare("SELECT * FROM usuarios WHERE telefono = '5550001'");
    $stmt->execute();
    $admin_existente = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$admin_existente) {
        echo "Creando usuario administrador...\n";
        $data_admin = [
            'telefono' => '5550001', 
            'nombre' => 'Administrador Sistema',
            'password' => 'admin123',
            'rol' => 'administrador'
        ];
        agregarUsuario($data_admin);
        echo "✓ Admin creado: 5550001 / admin123\n";
    } else {
        echo "✓ Admin ya existe: {$admin_existente['nombre']}\n";
    }
    
    // 2. Crear usuario trabajador
    $stmt = $conn->prepare("SELECT * FROM usuarios WHERE telefono = '5550002'");
    $stmt->execute();
    $trabajador_existente = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$trabajador_existente) {
        echo "Creando usuario trabajador...\n";
        $data_trabajador = [
            'telefono' => '5550002',
            'nombre' => 'Juan Pérez - Cobrador',
            'password' => 'trab123', 
            'rol' => 'trabajador'
        ];
        agregarUsuario($data_trabajador);
        echo "✓ Trabajador creado: 5550002 / trab123\n";
    } else {
        echo "✓ Trabajador ya existe: {$trabajador_existente['nombre']}\n";
    }
    
    echo "\n=== USUARIOS DISPONIBLES ===\n";
    $stmt = $conn->query("SELECT id, telefono, nombre, rol, activo FROM usuarios ORDER BY rol, id");
    while ($usuario = $stmt->fetch(PDO::FETCH_ASSOC)) {
        $estado = $usuario['activo'] ? '✅' : '❌';
        echo "$estado ID:{$usuario['id']} | {$usuario['telefono']} | {$usuario['nombre']} | ROL: {$usuario['rol']}\n";
    }
    
    echo "\n✅ Sistema listo para crear tarjetas!\n";
    
} catch (Exception $e) {
    echo "❌ Error: " . $e->getMessage() . "\n";
}
?>