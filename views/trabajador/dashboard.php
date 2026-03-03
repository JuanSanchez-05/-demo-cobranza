<?php
$titulo = 'Dashboard - Trabajador';
include __DIR__ . '/../layouts/header.php';

$total_tarjetas = count($tarjetas);
$cobrado_hoy = 0;
$pendiente_hoy = 0;

foreach ($tarjetas as $tarjeta) {
    $total = $tarjeta['total_prestamo'] ?? ($tarjeta['valor'] ?? 0);
    $cobrado = 0;
    if (isset($tarjeta['pagos'])) {
        foreach ($tarjeta['pagos'] as $pago) {
            $cobrado += $pago['pago'];
        }
    }
    $pendiente_hoy += ($total - $cobrado);
    
    // Verificar si hay pago hoy
    $hoy = date('Y-m-d');
    if (isset($tarjeta['pagos'])) {
        foreach ($tarjeta['pagos'] as $pago) {
            if ($pago['fecha'] === $hoy) {
                $cobrado_hoy += $pago['pago'];
            }
        }
    }
}
?>

<div class="dashboard-container">
    <div class="page-header">
        <h1>Panel de Control</h1>
        <p>Bienvenido, <?php echo htmlspecialchars($_SESSION['nombre']); ?></p>
    </div>

    <!-- Estadísticas -->
    <div class="stats-grid">
        <div class="stat-card">
            <div class="stat-icon">
                <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <path d="M3 3h18v18H3zM3 9h18M9 3v18"/>
                </svg>
            </div>
            <div class="stat-content">
                <h3>Mis Tarjetas</h3>
                <p class="stat-value"><?php echo $total_tarjetas; ?></p>
            </div>
        </div>
        
        <div class="stat-card">
            <div class="stat-icon">
                <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <path d="M12 2v20M17 5H9.5a3.5 3.5 0 0 0 0 7h5a3.5 3.5 0 0 1 0 7H6"/>
                </svg>
            </div>
            <div class="stat-content">
                <h3>Cobrado Hoy</h3>
                <p class="stat-value">$<?php echo number_format($cobrado_hoy, 2); ?></p>
            </div>
        </div>
        
        <div class="stat-card">
            <div class="stat-icon">
                <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <path d="M9 11l3 3L22 4M21 12v7a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h11"/>
                </svg>
            </div>
            <div class="stat-content">
                <h3>Pendiente Total</h3>
                <p class="stat-value">$<?php echo number_format($pendiente_hoy, 2); ?></p>
            </div>
        </div>
    </div>

    <!-- Acciones Rápidas -->
    <div class="section">
        <h2>Acciones Rápidas</h2>
        <div class="action-buttons">
            <a href="<?php echo BASE_URL; ?>controllers/TrabajadorController.php?action=carteras" class="btn btn-primary">
                Ver Mis Carteras
            </a>
            <a href="<?php echo BASE_URL; ?>controllers/TrabajadorController.php?action=carteras&filtro=cobradas_hoy" class="btn btn-success">
                Cobradas Hoy
            </a>
            <a href="<?php echo BASE_URL; ?>controllers/TrabajadorController.php?action=carteras&filtro=no_cobradas_hoy" class="btn btn-warning">
                Pendientes Hoy
            </a>
        </div>
    </div>

    <!-- Mi Cartera -->
    <?php if ($cartera): ?>
    <div class="section">
        <h2>Mi Cartera: <?php echo htmlspecialchars($cartera['nombre']); ?></h2>
        <p class="text-muted">Total de tarjetas: <?php echo count($tarjetas); ?></p>
    </div>
    <?php endif; ?>
    
    <!-- Mis Tarjetas -->
    <div class="section">
        <h2>Mis Tarjetas</h2>
        <div class="table-container">
            <table class="data-table">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Tipo</th>
                        <th>Cliente</th>
                        <th>Teléfono</th>
                        <th>Total</th>
                        <th>Progreso</th>
                        <th>Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (count($tarjetas) > 0): ?>
                        <?php foreach ($tarjetas as $tarjeta): 
                            $total = $tarjeta['total_prestamo'] ?? ($tarjeta['valor'] ?? 0);
                            $cobrado = 0;
                            if (isset($tarjeta['pagos'])) {
                                foreach ($tarjeta['pagos'] as $pago) {
                                    $cobrado += $pago['pago'];
                                }
                            }
                            $porcentaje = $total > 0 ? ($cobrado / $total) * 100 : 0;
                        ?>
                        <tr>
                            <td><?php echo $tarjeta['id']; ?></td>
                            <td>
                                <span class="badge badge-<?php 
                                    echo $tarjeta['tipo'] === 'antigua_semanal' ? 'info' : 
                                        ($tarjeta['tipo'] === 'antigua_diaria' ? 'warning' : 'success'); 
                                ?>">
                                    <?php 
                                    echo $tarjeta['tipo'] === 'antigua_semanal' ? 'Semanal' : 
                                        ($tarjeta['tipo'] === 'antigua_diaria' ? 'Diaria' : 'Nueva'); 
                                    ?>
                                </span>
                            </td>
                            <td><?php echo htmlspecialchars($tarjeta['nombre'] ?? 'N/A'); ?></td>
                            <td><?php echo htmlspecialchars($tarjeta['telefono'] ?? 'N/A'); ?></td>
                            <td>$<?php echo number_format($total, 2); ?></td>
                            <td>
                                <div class="progress-bar">
                                    <div class="progress-fill" style="width: <?php echo $porcentaje; ?>%"></div>
                                    <span class="progress-text"><?php echo number_format($porcentaje, 1); ?>%</span>
                                </div>
                            </td>
                            <td>
                                <a href="<?php echo BASE_URL; ?>controllers/TrabajadorController.php?action=detalle_tarjeta&id=<?php echo $tarjeta['id']; ?>" 
                                   class="btn btn-sm btn-primary">Ver Detalle</a>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="7" class="text-center">No tienes tarjetas en tu cartera</td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<?php include __DIR__ . '/../layouts/footer.php'; ?>

