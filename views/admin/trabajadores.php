<?php
$titulo = 'Trabajadores - Administrador';
include __DIR__ . '/../layouts/header.php';
?>

<div class="dashboard-container">
    <div class="page-header">
        <h1>Gestión de Trabajadores</h1>
        <a href="<?php echo BASE_URL; ?>controllers/AdminController.php?action=dashboard" class="btn btn-secondary">
            ← Volver al Dashboard
        </a>
    </div>

    <div class="table-container">
        <table class="data-table">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Nombre</th>
                    <th>Teléfono</th>
                    <th>Carteras Asignadas</th>
                    <th>Acciones</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($trabajadores as $trabajador): 
                    $carteras = obtenerCarterasPorTrabajador($trabajador['id']);
                ?>
                <tr>
                    <td><?php echo $trabajador['id']; ?></td>
                    <td><?php echo htmlspecialchars($trabajador['nombre']); ?></td>
                    <td><?php echo htmlspecialchars($trabajador['telefono']); ?></td>
                    <td><?php echo count($carteras); ?> carteras</td>
                    <td>
                        <button class="btn btn-sm btn-info" onclick="alert('Funcionalidad de edición pendiente')">
                            Editar
                        </button>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>

<?php include __DIR__ . '/../layouts/footer.php'; ?>

