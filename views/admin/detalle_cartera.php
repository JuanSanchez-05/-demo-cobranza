<?php
$titulo = 'Detalle de Cartera - Administrador';
include __DIR__ . '/../layouts/header.php';

$nombre_trab = $cartera['trabajador_nombre'] ?? ('ID ' . $cartera['trabajador_id']);
$tarjetas = isset($cartera['tarjetas']) ? $cartera['tarjetas'] : [];
$completadas = isset($cartera['completadas']) ? $cartera['completadas'] : [];

// Calcular estadísticas de la cartera
$total_cartera = 0;
$cobrado_cartera = 0;
$pendiente_cartera = 0;

foreach ($tarjetas as $tarjeta) {
    $total_tarjeta = $tarjeta['total_prestamo'] ?? ($tarjeta['valor'] ?? 0);
    $cobrado_tarjeta = 0;
    
    if (isset($tarjeta['pagos'])) {
        foreach ($tarjeta['pagos'] as $pago) {
            $cobrado_tarjeta += $pago['pago'];
        }
    }
    
    $total_cartera += $total_tarjeta;
    $cobrado_cartera += $cobrado_tarjeta;
    $pendiente_cartera += ($total_tarjeta - $cobrado_tarjeta);
}

$porcentaje_cartera = $total_cartera > 0 ? ($cobrado_cartera / $total_cartera) * 100 : 0;
?>

<div class="dashboard-container">
    <div class="page-header">
        <h1>Cartera #<?php echo $cartera['id']; ?>: <?php echo htmlspecialchars($cartera['nombre']); ?></h1>
        <a href="<?php echo BASE_URL; ?>controllers/AdminController.php?action=carteras" class="btn btn-secondary">
            ← Volver
        </a>
    </div>
    
    <!-- Estadísticas de la Cartera -->
    <div class="stats-grid">
        <div class="stat-card">
            <div class="stat-icon">
                <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <path d="M3 3h18v18H3zM3 9h18M9 3v18"/>
                </svg>
            </div>
            <div class="stat-content">
                <h3>Total de Tarjetas</h3>
                <p class="stat-value"><?php echo count($tarjetas); ?></p>
            </div>
        </div>
        
        <div class="stat-card">
            <div class="stat-icon">
                <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <path d="M12 2v20M17 5H9.5a3.5 3.5 0 0 0 0 7h5a3.5 3.5 0 0 1 0 7H6"/>
                </svg>
            </div>
            <div class="stat-content">
                <h3>Monto Total Cartera</h3>
                <p class="stat-value">$<?php echo number_format($total_cartera, 2); ?></p>
            </div>
        </div>
        
        <div class="stat-card">
            <div class="stat-icon">
                <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <path d="M9 11l3 3L22 4M21 12v7a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h11"/>
                </svg>
            </div>
            <div class="stat-content">
                <h3>Cobrado</h3>
                <p class="stat-value text-success">$<?php echo number_format($cobrado_cartera, 2); ?></p>
            </div>
        </div>
        
        <div class="stat-card">
            <div class="stat-icon">
                <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <path d="M12 2v20M17 5H9.5a3.5 3.5 0 0 0 0 7h5a3.5 3.5 0 0 1 0 7H6"/>
                </svg>
            </div>
            <div class="stat-content">
                <h3>Pendiente por Cobrar</h3>
                <p class="stat-value text-warning">$<?php echo number_format($pendiente_cartera, 2); ?></p>
            </div>
        </div>
        
        <div class="stat-card">
            <div class="stat-icon">
                <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <path d="M9 11l3 3L22 4M21 12v7a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h11"/>
                </svg>
            </div>
            <div class="stat-content">
                <h3>Progreso General</h3>
                <div class="progress-bar" style="margin-top: 12px;">
                    <div class="progress-fill" style="width: <?php echo $porcentaje_cartera; ?>%"></div>
                    <span class="progress-text"><?php echo number_format($porcentaje_cartera, 1); ?>%</span>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Información de la Cartera -->
    <div class="card">
        <div class="card-header">
            <h2>Información de la Cartera</h2>
        </div>
        <div class="card-body">
            <div class="info-grid">
                <div><strong>Trabajador:</strong> <?php echo htmlspecialchars($nombre_trab); ?></div>
                <div><strong>Total de Tarjetas:</strong> <?php echo count($tarjetas); ?></div>
                <div><strong>Fecha de Creación:</strong> <?php echo htmlspecialchars($cartera['fecha_creacion'] ?? 'N/A'); ?></div>
            </div>
        </div>
    </div>
    
    <!-- Tarjetas en esta Cartera -->
    <div class="section">
        <h2>Tarjetas en esta Cartera</h2>
        <div class="table-container">
            <table class="data-table">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Tipo</th>
                        <th>Cliente</th>
                        <th>Teléfono</th>
                        <th>Total Préstamo</th>
                        <th>Cobrado</th>
                        <th>Pendiente</th>
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
                            $pendiente = $total - $cobrado;
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
                            <td class="text-success">$<?php echo number_format($cobrado, 2); ?></td>
                            <td class="text-warning">$<?php echo number_format($pendiente, 2); ?></td>
                            <td>
                                <div class="progress-bar">
                                    <div class="progress-fill" style="width: <?php echo $porcentaje; ?>%"></div>
                                    <span class="progress-text"><?php echo number_format($porcentaje, 1); ?>%</span>
                                </div>
                            </td>
                            <td>
                                <a href="<?php echo BASE_URL; ?>controllers/AdminController.php?action=detalle_tarjeta&id=<?php echo $tarjeta['id']; ?>" 
                                   class="btn btn-sm btn-primary">Ver Detalle</a>
                                <a href="<?php echo BASE_URL; ?>controllers/AdminController.php?action=completar_tarjeta&id=<?php echo $tarjeta['id']; ?>" class="btn btn-sm btn-success" onclick="return confirm('Marcar como completada?')">Marcar completada</a>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="9" class="text-center">Esta cartera no tiene tarjetas asignadas</td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>

</div>

    <!-- Tarjetas completadas -->
    <div class="section">
        <h2>Tarjetas Completadas</h2>
        <div class="table-container">
            <table class="data-table">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Cliente</th>
                        <th>Teléfono</th>
                        <th>Total Préstamo</th>
                        <th>Fecha Completada</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (count($completadas) > 0): ?>
                        <?php foreach ($completadas as $ct): ?>
                            <tr>
                                <td><?php echo $ct['id']; ?></td>
                                <td><?php echo htmlspecialchars($ct['nombre'] ?? 'N/A'); ?></td>
                                <td><?php echo htmlspecialchars($ct['telefono'] ?? 'N/A'); ?></td>
                                <td>$<?php echo number_format($ct['total_prestamo'] ?? ($ct['valor'] ?? 0), 2); ?></td>
                                <td><?php echo htmlspecialchars($ct['fecha_completada'] ?? ''); ?></td>
                            </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr><td colspan="5" class="text-center">No hay tarjetas completadas</td></tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>

<?php include __DIR__ . '/../layouts/footer.php'; ?>

