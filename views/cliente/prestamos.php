<?php
$titulo = 'Mis Préstamos - Cliente';
include __DIR__ . '/../layouts/header.php';
?>

<div class="dashboard-container cliente-dashboard">
    <div class="page-header">
        <h1>Mis Préstamos</h1>
        <a href="<?php echo BASE_URL; ?>controllers/ClienteController.php?action=dashboard" class="btn btn-secondary">
            ← Volver
        </a>
    </div>

    <div class="prestamos-list">
        <?php if (count($prestamos) > 0): ?>
            <?php foreach ($prestamos as $prestamo): 
                $total = $prestamo['total_prestamo'] ?? ($prestamo['valor'] ?? 0);
                $cobrado = 0;
                if (isset($prestamo['pagos'])) {
                    foreach ($prestamo['pagos'] as $pago) {
                        $cobrado += $pago['pago'];
                    }
                }
                $pendiente = $total - $cobrado;
                $porcentaje = $total > 0 ? ($cobrado / $total) * 100 : 0;
            ?>
            <div class="prestamo-item">
                <div class="prestamo-item-header">
                    <h3>Préstamo #<?php echo $prestamo['id']; ?></h3>
                    <span class="prestamo-badge">
                        <?php 
                        echo $prestamo['tipo'] === 'antigua_semanal' ? 'Semanal' : 
                            ($prestamo['tipo'] === 'antigua_diaria' ? 'Diaria' : 'Nuevo'); 
                        ?>
                    </span>
                </div>
                <div class="prestamo-item-body">
                    <div class="prestamo-item-stats">
                        <div>
                            <p class="stat-label">Total</p>
                            <p class="stat-value">$<?php echo number_format($total, 2); ?></p>
                        </div>
                        <div>
                            <p class="stat-label">Pagado</p>
                            <p class="stat-value success">$<?php echo number_format($cobrado, 2); ?></p>
                        </div>
                        <div>
                            <p class="stat-label">Pendiente</p>
                            <p class="stat-value warning">$<?php echo number_format($pendiente, 2); ?></p>
                        </div>
                    </div>
                    <div class="prestamo-progress">
                        <div class="prestamo-progress-bar">
                            <div class="prestamo-progress-fill" style="width: <?php echo $porcentaje; ?>%"></div>
                        </div>
                        <p class="prestamo-progress-text"><?php echo number_format($porcentaje, 1); ?>% completado</p>
                    </div>
                </div>
                <div class="prestamo-item-footer">
                    <a href="<?php echo BASE_URL; ?>controllers/ClienteController.php?action=detalle_prestamo&id=<?php echo $prestamo['id']; ?>" 
                       class="btn btn-primary">
                        Ver Detalle Completo
                    </a>
                </div>
            </div>
            <?php endforeach; ?>
        <?php else: ?>
            <div class="empty-state">
                <p>No tienes préstamos registrados</p>
            </div>
        <?php endif; ?>
    </div>
</div>

<?php include __DIR__ . '/../layouts/footer.php'; ?>

