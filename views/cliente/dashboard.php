<?php
$titulo = 'Mi Dashboard - Cliente';
include __DIR__ . '/../layouts/header.php';

$total_prestamos = 0;
$total_cobrado = 0;
$total_pendiente = 0;

foreach ($prestamos as $prestamo) {
    $total = $prestamo['total_prestamo'] ?? ($prestamo['valor'] ?? 0);
    $total_prestamos += $total;
    
    $cobrado = 0;
    if (isset($prestamo['pagos'])) {
        foreach ($prestamo['pagos'] as $pago) {
            $cobrado += $pago['pago'];
        }
    }
    
    $total_cobrado += $cobrado;
    $total_pendiente += ($total - $cobrado);
}

$porcentaje_general = $total_prestamos > 0 ? ($total_cobrado / $total_prestamos) * 100 : 0;
?>

<div class="dashboard-container cliente-dashboard">
    <div class="page-header">
        <h1>Bienvenido, <?php echo htmlspecialchars($_SESSION['nombre']); ?></h1>
        <p>Consulte el estado de sus préstamos</p>
    </div>

    <!-- Resumen General -->
    <div class="cliente-summary">
        <div class="summary-card">
            <div class="summary-icon">
                <svg width="32" height="32" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <path d="M12 2v20M17 5H9.5a3.5 3.5 0 0 0 0 7h5a3.5 3.5 0 0 1 0 7H6"/>
                </svg>
            </div>
            <div class="summary-content">
                <h2>Total de Préstamos</h2>
                <p class="summary-value">$<?php echo number_format($total_prestamos, 2); ?></p>
            </div>
        </div>
        
        <div class="summary-card">
            <div class="summary-icon">
                <svg width="32" height="32" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <path d="M9 11l3 3L22 4M21 12v7a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h11"/>
                </svg>
            </div>
            <div class="summary-content">
                <h2>Total Pagado</h2>
                <p class="summary-value success">$<?php echo number_format($total_cobrado, 2); ?></p>
            </div>
        </div>
        
        <div class="summary-card">
            <div class="summary-icon">
                <svg width="32" height="32" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <path d="M9 12l2 2 4-4M7.835 4.697a3.42 3.42 0 0 0 1.946-.806 3.42 3.42 0 0 1 4.438 0 3.42 3.42 0 0 0 1.946.806 3.42 3.42 0 0 1 .926 5.895 3.42 3.42 0 0 0 .805 1.946 3.42 3.42 0 0 1 0 4.438 3.42 3.42 0 0 0-.806 1.946 3.42 3.42 0 0 1-5.895.926 3.42 3.42 0 0 0-1.946.805 3.42 3.42 0 0 1-4.438 0 3.42 3.42 0 0 0-1.946-.806 3.42 3.42 0 0 1-.926-5.895 3.42 3.42 0 0 0-.805-1.946 3.42 3.42 0 0 1 0-4.438 3.42 3.42 0 0 0 .806-1.946 3.42 3.42 0 0 1 5.895-.926z"/>
                </svg>
            </div>
            <div class="summary-content">
                <h2>Saldo Pendiente</h2>
                <p class="summary-value warning">$<?php echo number_format($total_pendiente, 2); ?></p>
            </div>
        </div>
    </div>

    <!-- Barra de Progreso General -->
    <div class="cliente-progress-section">
        <h2>Progreso General</h2>
        <div class="cliente-progress-bar">
            <div class="cliente-progress-fill" style="width: <?php echo $porcentaje_general; ?>%">
                <span class="cliente-progress-text"><?php echo number_format($porcentaje_general, 1); ?>%</span>
            </div>
        </div>
        <p class="progress-info">
            Has pagado $<?php echo number_format($total_cobrado, 2); ?> de $<?php echo number_format($total_prestamos, 2); ?>
        </p>
    </div>

    <!-- Mis Préstamos -->
    <div class="cliente-prestamos">
        <h2>Mis Préstamos</h2>
        
        <?php if (count($prestamos) > 0): ?>
            <div class="prestamos-grid">
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
                <div class="prestamo-card">
                    <div class="prestamo-header">
                        <h3>Préstamo #<?php echo $prestamo['id']; ?></h3>
                        <span class="prestamo-badge">
                            <?php 
                            echo $prestamo['tipo'] === 'antigua_semanal' ? 'Semanal' : 
                                ($prestamo['tipo'] === 'antigua_diaria' ? 'Diaria' : 'Nuevo'); 
                            ?>
                        </span>
                    </div>
                    
                    <div class="prestamo-body">
                        <div class="prestamo-amount">
                            <p class="prestamo-label">Total del Préstamo</p>
                            <p class="prestamo-value">$<?php echo number_format($total, 2); ?></p>
                        </div>
                        
                        <div class="prestamo-stats">
                            <div class="prestamo-stat">
                                <span class="stat-label">Pagado</span>
                                <span class="stat-value success">$<?php echo number_format($cobrado, 2); ?></span>
                            </div>
                            <div class="prestamo-stat">
                                <span class="stat-label">Pendiente</span>
                                <span class="stat-value warning">$<?php echo number_format($pendiente, 2); ?></span>
                            </div>
                        </div>
                        
                        <div class="prestamo-progress">
                            <div class="prestamo-progress-bar">
                                <div class="prestamo-progress-fill" style="width: <?php echo $porcentaje; ?>%"></div>
                            </div>
                            <p class="prestamo-progress-text"><?php echo number_format($porcentaje, 1); ?>% completado</p>
                        </div>
                    </div>
                    
                    <div class="prestamo-footer">
                        <a href="<?php echo BASE_URL; ?>controllers/ClienteController.php?action=detalle_prestamo&id=<?php echo $prestamo['id']; ?>" 
                           class="btn btn-primary btn-block">
                            Ver Detalle
                        </a>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>
        <?php else: ?>
            <div class="empty-state">
                <p>No tienes préstamos registrados</p>
            </div>
        <?php endif; ?>
    </div>
</div>

<?php include __DIR__ . '/../layouts/footer.php'; ?>

