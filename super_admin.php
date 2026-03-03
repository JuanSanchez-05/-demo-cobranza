<?php
/**
 * PANEL DE SUPER ADMINISTRADOR
 * Gestión completa de usuarios del sistema
 */

require_once 'config/config.php';

$mensaje = '';
$error = '';

// Procesar acciones
if ($_POST) {
    $accion = $_POST['accion'] ?? '';
    
    if ($accion === 'crear_usuario') {
        $telefono = trim($_POST['telefono']);
        $password = trim($_POST['password']);
        $rol = $_POST['rol'];
        $nombre = trim($_POST['nombre']);
        
        if (empty($telefono) || empty($password) || empty($nombre)) {
            $error = "Todos los campos son requeridos";
        } else {
            try {
                $db = getDB();
                
                // Verificar si el teléfono ya existe
                $stmt = $db->prepare("SELECT COUNT(*) as count FROM usuarios WHERE telefono = ?");
                $stmt->execute([$telefono]);
                
                if ($stmt->fetch()['count'] > 0) {
                    $error = "❌ El teléfono ya está registrado";
                } else {
                    $hash = password_hash($password, PASSWORD_DEFAULT);
                    $stmt = $db->prepare("INSERT INTO usuarios (telefono, password, rol, nombre) VALUES (?, ?, ?, ?)");
                    $stmt->execute([$telefono, $hash, $rol, $nombre]);
                    $mensaje = "✅ Usuario creado exitosamente";
                }
                
            } catch (Exception $e) {
                $error = "❌ Error: " . $e->getMessage();
            }
        }
    }
    
    elseif ($accion === 'actualizar_usuario') {
        $id = $_POST['usuario_id'];
        $telefono = trim($_POST['telefono']);
        $nombre = trim($_POST['nombre']);
        $rol = $_POST['rol'];
        $activo = isset($_POST['activo']) ? 1 : 0;
        
        try {
            $db = getDB();
            
            if (!empty($_POST['password'])) {
                // Actualizar con nueva contraseña
                $hash = password_hash($_POST['password'], PASSWORD_DEFAULT);
                $stmt = $db->prepare("UPDATE usuarios SET telefono = ?, password = ?, rol = ?, nombre = ?, activo = ? WHERE id = ?");
                $stmt->execute([$telefono, $hash, $rol, $nombre, $activo, $id]);
            } else {
                // Actualizar sin cambiar contraseña
                $stmt = $db->prepare("UPDATE usuarios SET telefono = ?, rol = ?, nombre = ?, activo = ? WHERE id = ?");
                $stmt->execute([$telefono, $rol, $nombre, $activo, $id]);
            }
            
            $mensaje = "✅ Usuario actualizado exitosamente";
            
        } catch (Exception $e) {
            $error = "❌ Error: " . $e->getMessage();
        }
    }
    
    elseif ($accion === 'eliminar_usuario') {
        $id = $_POST['usuario_id'];
        
        try {
            $db = getDB();
            $stmt = $db->prepare("DELETE FROM usuarios WHERE id = ? AND rol != 'administrador'");
            $result = $stmt->execute([$id]);
            
            if ($stmt->rowCount() > 0) {
                $mensaje = "✅ Usuario eliminado exitosamente";
            } else {
                $error = "❌ No se puede eliminar (usuario administrador o no encontrado)";
            }
            
        } catch (Exception $e) {
            $error = "❌ Error: " . $e->getMessage();
        }
    }
}

// Obtener todos los usuarios
$usuarios = [];
try {
    $db = getDB();
    $stmt = $db->query("SELECT * FROM usuarios ORDER BY rol, nombre");
    $usuarios = $stmt->fetchAll();
} catch (Exception $e) {
    $error = "❌ Error cargando usuarios: " . $e->getMessage();
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Super Admin - Sistema de Cobranza</title>
    <style>
        body { font-family: Arial, sans-serif; margin: 0; padding: 20px; background: #f8f9fa; }
        .container { max-width: 1200px; margin: 0 auto; }
        .card { background: white; padding: 20px; margin: 20px 0; border-radius: 8px; box-shadow: 0 2px 4px rgba(0,0,0,0.1); }
        .form-group { margin: 15px 0; }
        .form-row { display: grid; grid-template-columns: 1fr 1fr; gap: 15px; }
        label { display: block; font-weight: bold; margin-bottom: 5px; }
        input, select { width: 100%; padding: 10px; border: 1px solid #ddd; border-radius: 4px; box-sizing: border-box; }
        .btn { padding: 10px 15px; border: none; border-radius: 4px; cursor: pointer; text-decoration: none; display: inline-block; margin: 2px; }
        .btn-primary { background: #007bff; color: white; }
        .btn-success { background: #28a745; color: white; }
        .btn-warning { background: #ffc107; color: black; }
        .btn-danger { background: #dc3545; color: white; }
        .btn-sm { padding: 5px 10px; font-size: 0.875em; }
        .btn:hover { opacity: 0.9; }
        .alert { padding: 15px; margin: 20px 0; border-radius: 4px; }
        .alert-success { background: #d4edda; color: #155724; border: 1px solid #c3e6cb; }
        .alert-danger { background: #f8d7da; color: #721c24; border: 1px solid #f5c6cb; }
        table { width: 100%; border-collapse: collapse; margin-top: 20px; }
        th, td { padding: 12px; text-align: left; border-bottom: 1px solid #ddd; }
        th { background: #f8f9fa; font-weight: bold; }
        .badge { padding: 4px 8px; border-radius: 4px; font-size: 0.75em; font-weight: bold; }
        .badge-admin { background: #6f42c1; color: white; }
        .badge-trabajador { background: #17a2b8; color: white; }
        .badge-cliente { background: #28a745; color: white; }
        .badge-activo { background: #28a745; color: white; }
        .badge-inactivo { background: #6c757d; color: white; }
        .header { background: #343a40; color: white; padding: 20px; border-radius: 8px; margin-bottom: 20px; text-align: center; }
        .stats { display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 15px; margin: 20px 0; }
        .stat-card { background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); color: white; padding: 20px; border-radius: 8px; text-align: center; }
        .modal { display: none; position: fixed; z-index: 1000; left: 0; top: 0; width: 100%; height: 100%; background: rgba(0,0,0,0.5); }
        .modal-content { background: white; margin: 5% auto; padding: 20px; border-radius: 8px; width: 90%; max-width: 500px; }
        .close { color: #aaa; float: right; font-size: 28px; font-weight: bold; cursor: pointer; }
        .close:hover { color: black; }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>👑 Panel de Super Administrador</h1>
            <p>Gestión completa de usuarios del sistema</p>
            <a href="index.php" class="btn btn-primary">🏠 Volver al Sistema</a>
            <a href="setup.php" class="btn btn-warning">🔧 Configuración</a>
        </div>

        <?php if ($mensaje): ?>
            <div class="alert alert-success"><?php echo $mensaje; ?></div>
        <?php endif; ?>

        <?php if ($error): ?>
            <div class="alert alert-danger"><?php echo $error; ?></div>
        <?php endif; ?>

        <!-- Estadísticas -->
        <div class="stats">
            <?php
            $stats = ['administrador' => 0, 'trabajador' => 0, 'cliente' => 0, 'activos' => 0];
            foreach ($usuarios as $user) {
                $stats[$user['rol']]++;
                if ($user['activo']) $stats['activos']++;
            }
            ?>
            <div class="stat-card">
                <h3><?php echo $stats['administrador']; ?></h3>
                <p>Administradores</p>
            </div>
            <div class="stat-card">
                <h3><?php echo $stats['trabajador']; ?></h3>
                <p>Trabajadores</p>
            </div>
            <div class="stat-card">
                <h3><?php echo $stats['cliente']; ?></h3>
                <p>Clientes</p>
            </div>
            <div class="stat-card">
                <h3><?php echo $stats['activos']; ?></h3>
                <p>Usuarios Activos</p>
            </div>
        </div>

        <!-- Crear Usuario -->
        <div class="card">
            <h2>➕ Crear Nuevo Usuario</h2>
            <form method="POST">
                <input type="hidden" name="accion" value="crear_usuario">
                
                <div class="form-row">
                    <div class="form-group">
                        <label>Teléfono (Usuario):</label>
                        <input type="text" name="telefono" required>
                    </div>
                    <div class="form-group">
                        <label>Contraseña:</label>
                        <input type="password" name="password" required>
                    </div>
                </div>

                <div class="form-row">
                    <div class="form-group">
                        <label>Nombre Completo:</label>
                        <input type="text" name="nombre" required>
                    </div>
                    <div class="form-group">
                        <label>Rol:</label>
                        <select name="rol" required>
                            <option value="trabajador">Trabajador</option>
                            <option value="cliente">Cliente</option>
                            <option value="administrador">Administrador</option>
                        </select>
                    </div>
                </div>

                <button type="submit" class="btn btn-success">👤 Crear Usuario</button>
            </form>
        </div>

        <!-- Lista de Usuarios -->
        <div class="card">
            <h2>👥 Usuarios del Sistema</h2>
            <table>
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Teléfono</th>
                        <th>Nombre</th>
                        <th>Rol</th>
                        <th>Estado</th>
                        <th>Fecha Creación</th>
                        <th>Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($usuarios as $usuario): ?>
                    <tr>
                        <td><?php echo $usuario['id']; ?></td>
                        <td><?php echo htmlspecialchars($usuario['telefono']); ?></td>
                        <td><?php echo htmlspecialchars($usuario['nombre']); ?></td>
                        <td>
                            <span class="badge badge-<?php echo $usuario['rol'] === 'administrador' ? 'admin' : ($usuario['rol'] === 'trabajador' ? 'trabajador' : 'cliente'); ?>">
                                <?php echo ucfirst($usuario['rol']); ?>
                            </span>
                        </td>
                        <td>
                            <span class="badge badge-<?php echo $usuario['activo'] ? 'activo' : 'inactivo'; ?>">
                                <?php echo $usuario['activo'] ? 'Activo' : 'Inactivo'; ?>
                            </span>
                        </td>
                        <td><?php echo date('d/m/Y', strtotime($usuario['fecha_creacion'])); ?></td>
                        <td>
                            <button onclick="editarUsuario(<?php echo htmlspecialchars(json_encode($usuario)); ?>)" class="btn btn-warning btn-sm">✏️ Editar</button>
                            <?php if ($usuario['rol'] !== 'administrador'): ?>
                                <button onclick="eliminarUsuario(<?php echo $usuario['id']; ?>, '<?php echo htmlspecialchars($usuario['nombre']); ?>')" class="btn btn-danger btn-sm">🗑️ Eliminar</button>
                            <?php endif; ?>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>

    <!-- Modal Editar Usuario -->
    <div id="modalEditar" class="modal">
        <div class="modal-content">
            <span class="close" onclick="cerrarModal()">&times;</span>
            <h2>✏️ Editar Usuario</h2>
            <form method="POST" id="formEditar">
                <input type="hidden" name="accion" value="actualizar_usuario">
                <input type="hidden" name="usuario_id" id="edit_id">
                
                <div class="form-group">
                    <label>Teléfono (Usuario):</label>
                    <input type="text" name="telefono" id="edit_telefono" required>
                </div>

                <div class="form-group">
                    <label>Nueva Contraseña (dejar vacío para no cambiar):</label>
                    <input type="password" name="password" id="edit_password">
                </div>

                <div class="form-group">
                    <label>Nombre Completo:</label>
                    <input type="text" name="nombre" id="edit_nombre" required>
                </div>

                <div class="form-group">
                    <label>Rol:</label>
                    <select name="rol" id="edit_rol" required>
                        <option value="trabajador">Trabajador</option>
                        <option value="cliente">Cliente</option>
                        <option value="administrador">Administrador</option>
                    </select>
                </div>

                <div class="form-group">
                    <label>
                        <input type="checkbox" name="activo" id="edit_activo"> Usuario Activo
                    </label>
                </div>

                <button type="submit" class="btn btn-success">💾 Guardar Cambios</button>
                <button type="button" onclick="cerrarModal()" class="btn btn-warning">❌ Cancelar</button>
            </form>
        </div>
    </div>

    <script>
        function editarUsuario(usuario) {
            document.getElementById('edit_id').value = usuario.id;
            document.getElementById('edit_telefono').value = usuario.telefono;
            document.getElementById('edit_nombre').value = usuario.nombre;
            document.getElementById('edit_rol').value = usuario.rol;
            document.getElementById('edit_activo').checked = usuario.activo == 1;
            document.getElementById('modalEditar').style.display = 'block';
        }

        function cerrarModal() {
            document.getElementById('modalEditar').style.display = 'none';
        }

        function eliminarUsuario(id, nombre) {
            if (confirm('¿Está seguro de eliminar al usuario "' + nombre + '"?')) {
                var form = document.createElement('form');
                form.method = 'POST';
                form.innerHTML = '<input type="hidden" name="accion" value="eliminar_usuario">' +
                                '<input type="hidden" name="usuario_id" value="' + id + '">';
                document.body.appendChild(form);
                form.submit();
            }
        }

        // Cerrar modal al hacer clic fuera
        window.onclick = function(event) {
            var modal = document.getElementById('modalEditar');
            if (event.target == modal) {
                cerrarModal();
            }
        }
    </script>
</body>
</html>