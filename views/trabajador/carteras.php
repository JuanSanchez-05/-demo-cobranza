<?php
$titulo = 'Mi Cartera - Trabajador';
include __DIR__ . '/../layouts/header.php';

// La variable $filtro viene del controlador
// Si no viene, obtenerla del GET (para que funcione si se llama directamente)
if (!isset($filtro)) {
    $filtro = $_GET['filtro'] ?? 'todas';
}

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
        <h1>Mi Cartera<?php if ($cartera): ?>: <?php echo htmlspecialchars($cartera['nombre']); ?><?php endif; ?></h1>
        <a href="<?php echo BASE_URL; ?>controllers/TrabajadorController.php?action=dashboard" class="btn btn-secondary">
            ← Volver al Dashboard
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

    <!-- Filtros -->
    <div class="filters" style="margin-bottom: 20px;">
        <a href="<?php echo BASE_URL; ?>controllers/TrabajadorController.php?action=carteras&filtro=todas" 
           class="btn <?php echo ($filtro === 'todas' || empty($_GET['filtro'])) ? 'btn-primary' : 'btn-outline-secondary'; ?>"
           style="<?php echo ($filtro === 'todas' || empty($_GET['filtro'])) ? 'background-color: #007bff; color: white; border-color: #007bff;' : ''; ?>">
            📋 TODAS
        </a>
        <a href="<?php echo BASE_URL; ?>controllers/TrabajadorController.php?action=carteras&filtro=cobradas_hoy" 
           class="btn <?php echo $filtro === 'cobradas_hoy' ? 'btn-success' : 'btn-outline-secondary'; ?>"
           style="<?php echo $filtro === 'cobradas_hoy' ? 'background-color: #28a745; color: white; border-color: #28a745;' : ''; ?>">
            ✓ COBRADAS HOY
        </a>
        <a href="<?php echo BASE_URL; ?>controllers/TrabajadorController.php?action=carteras&filtro=no_cobradas_hoy" 
           class="btn <?php echo $filtro === 'no_cobradas_hoy' ? 'btn-warning' : 'btn-outline-secondary'; ?>"
           style="<?php echo $filtro === 'no_cobradas_hoy' ? 'background-color: #ffc107; color: black; border-color: #ffc107;' : ''; ?>">
            ⏳ NO COBRADAS HOY
        </a>
    </div>
    </div>

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
                            <a href="<?php echo BASE_URL; ?>controllers/TrabajadorController.php?action=detalle_tarjeta&id=<?php echo $tarjeta['id']; ?>" 
                               class="btn btn-sm btn-primary">Ver Detalle</a>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                <?php else: ?>
                    <tr>
                        <td colspan="9" class="text-center">No hay tarjetas que mostrar con este filtro</td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>

    <!-- Tarjetas Completadas -->
    <div class="section">
        <h2>Tarjetas Completadas</h2>
        <div class="table-container">
            <table class="data-table">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Tipo</th>
                        <th>Cliente</th>
                        <th>Teléfono</th>
                        <th>Total Préstamo</th>
                        <th>Fecha Completada</th>
                        <th>Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (isset($completadas) && count($completadas) > 0): ?>
                        <?php foreach ($completadas as $tarjeta): 
                            $total = $tarjeta['total_prestamo'] ?? ($tarjeta['valor'] ?? 0);
                        ?>
                        <tr class="row-completed">
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
                            <td><strong><?php echo htmlspecialchars($tarjeta['fecha_completada'] ?? 'N/A'); ?></strong></td>
                            <td>
                                <span class="badge badge-success">✓ Completada</span>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="7" class="text-center">No hay tarjetas completadas</td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<?php include __DIR__ . '/../layouts/footer.php'; ?>

