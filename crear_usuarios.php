<?php
require_once 'config/config.php';

echo "Creando usuario administrador...\n";

try {
    $db = new Database();
    $conn = $db->getConnection();
    
    // Verificar si existe el usuario admin
    $stmt = $conn->prepare("SELECT * FROM usuarios WHERE telefono = '5550001'");
    $stmt->execute();
    $admin_existente = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if ($admin_existente) {
        echo "✓ Usuario admin ya existe: {$admin_existente['nombre']} ({$admin_existente['rol']})\n";
    } else {
        // Crear usuario admin
        $data = [
            'telefono' => '5550001',
            'nombre' => 'Administrador',
            'password' => 'admin123',
            'rol' => 'administrador'
        ];
        
        $result = agregarUsuario($data);
        if ($result) {
            echo "✅ Usuario admin creado exitosamente!\n";
            echo "   Teléfono: 5550001\n";
            echo "   Contraseña: admin123\n";
        } else {
            echo "❌ Error al crear usuario admin\n";
        }
    }
    
    // También crear un trabajador de ejemplo
    $stmt = $conn->prepare("SELECT * FROM usuarios WHERE telefono = '5550002'");
    $stmt->execute();
    $trabajador_existente = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if ($trabajador_existente) {
        echo "✓ Usuario trabajador ya existe: {$trabajador_existente['nombre']} ({$trabajador_existente['rol']})\n";
    } else {
        $data_trabajador = [
            'telefono' => '5550002',
            'nombre' => 'Juan Pérez',
            'password' => 'trab123',
            'rol' => 'trabajador'
        ];
        
        $result = agregarUsuario($data_trabajador);
        if ($result) {
            echo "✅ Usuario trabajador creado exitosamente!\n";
            echo "   Teléfono: 5550002\n";
            echo "   Contraseña: trab123\n";
        }
    }
    
    echo "\n=== Usuarios en el sistema ===\n";
    $stmt = $conn->query("SELECT id, telefono, nombre, rol, activo FROM usuarios");
    $usuarios = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    foreach ($usuarios as $usuario) {
        $estado = $usuario['activo'] ? 'Activo' : 'Inactivo';
        echo "ID: {$usuario['id']}, Teléfono: {$usuario['telefono']}, Nombre: {$usuario['nombre']}, Rol: {$usuario['rol']}, Estado: $estado\n";
    }
    
} catch (Exception $e) {
    echo "❌ Error: " . $e->getMessage() . "\n";
}
?>