<?php
$titulo = 'Carteras - Administrador';
include __DIR__ . '/../layouts/header.php';
?>

<div class="dashboard-container">
    <div class="page-header">
        <h1>Gestión de Carteras</h1>
        <a href="<?php echo BASE_URL; ?>controllers/AdminController.php?action=dashboard" class="btn btn-secondary">
            ← Volver al Panel de Control
        </a>
    </div>

    <?php if (isset($_GET['mensaje']) && $_GET['mensaje'] === 'guardado'): ?>
        <div class="alert alert-success">Cartera guardada exitosamente</div>
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
                    global $usuarios_simulados;
                    $trabajador = array_filter($usuarios_simulados, function($u) use ($cartera) {
                        return $u['id'] == $cartera['trabajador_id'];
                    });
                    $trabajador = reset($trabajador);
                    $nombre_trab = $trabajador ? $trabajador['nombre'] : "ID " . $cartera['trabajador_id'];
                    $total_tarjetas = isset($cartera['tarjetas']) ? count($cartera['tarjetas']) : 0;
                ?>
                <tr>
                    <td><?php echo $cartera['id']; ?></td>
                    <td><?php echo htmlspecialchars($nombre_trab); ?></td>
                    <td><strong><?php echo $total_tarjetas; ?></strong> tarjetas</td>
                    <td><?php echo htmlspecialchars($cartera['fecha_creacion'] ?? 'N/A'); ?></td>
                    <td>
                        <a href="<?php echo BASE_URL; ?>controllers/AdminController.php?action=detalle_cartera&id=<?php echo $cartera['id']; ?>" 
                           class="btn btn-sm btn-primary">Ver Cartera</a>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>

<?php include __DIR__ . '/../layouts/footer.php'; ?>
