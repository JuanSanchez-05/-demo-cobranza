<?php
$titulo = 'Carteras - Administrador';
include __DIR__ . '/../layouts/header.php';
?>

<div class="dashboard-container">
    <div class="page-header">
        <h1>Gestión de Carteras</h1>
        <div class="page-actions">
            <a href="<?php echo BASE_URL; ?>controllers/AdminController.php?action=nuevo_cartera" class="btn btn-primary">+ Crear Cartera</a>
            <a href="<?php echo BASE_URL; ?>controllers/AdminController.php?action=dashboard" class="btn btn-secondary">← Volver al Panel de Control</a>
        </div>
    </div>

    <?php if (isset($_GET['mensaje'])): 
        if ($_GET['mensaje'] === 'guardado'): ?>
            <div class="alert alert-success">Cartera guardada exitosamente</div>
        <?php elseif ($_GET['mensaje'] === 'tarjeta_creada'): ?>
            <div class="alert alert-success">Tarjeta agregada a la cartera</div>
        <?php elseif ($_GET['mensaje'] === 'eliminado'): ?>
            <div class="alert alert-success">Cartera eliminada</div>
        <?php endif;
    endif; ?>
    <?php if (isset($_GET['error']) && $_GET['error'] === 'sin_cartera'): ?>
        <div class="alert alert-danger">Debe seleccionar una cartera para registrar la tarjeta</div>
    <?php endif; ?>

    <div class="table-container">
        <table class="data-table">
            <thead>
                <tr>
                    <th>ID Cartera</th>
                    <th>Trabajador</th>
                    <th>Total Tarjetas</th>
                    <th>Fecha Creación</th>
                    <th>Acciones</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($todas_carteras as $cartera):
                    $nombre_trab = $cartera['trabajador_nombre'] ?? ('ID ' . $cartera['trabajador_id']);
                    $total_tarjetas = isset($cartera['tarjetas']) ? count($cartera['tarjetas']) : 0;
                ?>
                <tr>
                    <td><?php echo $cartera['id']; ?></td>
                    <td><?php echo htmlspecialchars($nombre_trab); ?></td>
                    <td><strong><?php echo $total_tarjetas; ?></strong> tarjetas</td>
                    <td><?php echo htmlspecialchars($cartera['fecha_creacion'] ?? 'N/A'); ?></td>
                    <td>
                        <a href="<?php echo BASE_URL; ?>controllers/AdminController.php?action=detalle_cartera&id=<?php echo $cartera['id']; ?>" class="btn btn-sm btn-primary">Ver</a>
                        <a href="<?php echo BASE_URL; ?>controllers/AdminController.php?action=editar_cartera&id=<?php echo $cartera['id']; ?>" class="btn btn-sm btn-info">Editar</a>
                        <a href="<?php echo BASE_URL; ?>controllers/AdminController.php?action=asignar_promotor&id=<?php echo $cartera['id']; ?>" class="btn btn-sm btn-warning">Asignar Promotor</a>
                        <a href="<?php echo BASE_URL; ?>controllers/AdminController.php?action=eliminar_cartera&id=<?php echo $cartera['id']; ?>" class="btn btn-sm btn-danger" onclick="return confirm('¿Eliminar cartera? Esta acción moverá o perderá tarjetas asignadas.');">Eliminar</a>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>

<?php include __DIR__ . '/../layouts/footer.php'; ?>
