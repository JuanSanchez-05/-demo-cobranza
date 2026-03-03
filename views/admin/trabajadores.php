<?php
$titulo = 'Trabajadores - Administrador';
include __DIR__ . '/../layouts/header.php';
?>

<div class="dashboard-container">
    <div class="page-header">
        <h1>Gestión de Trabajadores</h1>
        <div class="page-actions">
            <a href="<?php echo BASE_URL; ?>controllers/AdminController.php?action=nuevo_trabajador" class="btn btn-primary">+ Registrar trabajador</a>
            <a href="<?php echo BASE_URL; ?>controllers/AdminController.php?action=dashboard" class="btn btn-secondary">← Volver al Dashboard</a>
        </div>
    </div>

    <div class="table-container">
        <table class="data-table">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Nombre</th>
                    <th>Teléfono</th>
                    <th>Tarjetas Asignadas</th>
                    <th>Acciones</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($trabajadores as $trabajador): 
                    $cartera = obtenerCarteraPorTrabajador($trabajador['id']);
                ?>
                <tr>
                    <td><?php echo $trabajador['id']; ?></td>
                    <td><?php echo htmlspecialchars($trabajador['nombre']); ?></td>
                    <td><?php echo htmlspecialchars($trabajador['telefono']); ?></td>
                    <td><?php echo ($cartera ? 1 : 0); ?> carteras</td>
                    <td>
                        <a href="<?php echo BASE_URL; ?>controllers/AdminController.php?action=editar_trabajador&id=<?php echo $trabajador['id']; ?>" class="btn btn-sm btn-info">Editar</a>
                        <?php if (empty($trabajador['activo'])): ?>
                            <span class="badge muted">Deshabilitado</span>
                            <a href="<?php echo BASE_URL; ?>controllers/AdminController.php?action=baja_trabajador&id=<?php echo $trabajador['id']; ?>" class="btn btn-sm">Activar</a>
                        <?php else: ?>
                            <a href="<?php echo BASE_URL; ?>controllers/AdminController.php?action=baja_trabajador&id=<?php echo $trabajador['id']; ?>" class="btn btn-sm btn-danger">Dar de baja</a>
                        <?php endif; ?>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>

<?php include __DIR__ . '/../layouts/footer.php'; ?>

