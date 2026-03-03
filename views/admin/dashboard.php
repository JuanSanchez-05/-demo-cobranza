<?php
$titulo = 'Dashboard - Administrador';
include __DIR__ . '/../layouts/header.php';
?>

<div class="dashboard-container">
    <div class="page-header">
        <h1>Panel de Control Administrativo</h1>
        <p>Bienvenido, <?php echo htmlspecialchars($_SESSION['nombre']); ?></p>
    </div>

    <!-- Estadísticas Generales -->
    <div class="stats-grid">
        <div class="stat-card">
            <div class="stat-icon">
                <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <path d="M12 2v20M17 5H9.5a3.5 3.5 0 0 0 0 7h5a3.5 3.5 0 0 1 0 7H6"/>
                </svg>
            </div>
            <div class="stat-content">
                <h3>Total Cobrado</h3>
                <p class="stat-value">$<?php echo number_format($stats['total_cobrado'], 2); ?></p>
            </div>
        </div>
        
        <div class="stat-card">
            <div class="stat-icon">
                <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <path d="M9 11l3 3L22 4M21 12v7a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h11"/>
                </svg>
            </div>
            <div class="stat-content">
                <h3>Saldo Pendiente</h3>
                <p class="stat-value">$<?php echo number_format($stats['total_pendiente'], 2); ?></p>
            </div>
        </div>
        
        <div class="stat-card">
            <div class="stat-icon">
                <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <path d="M3 3h18v18H3zM3 9h18M9 3v18"/>
                </svg>
            </div>
            <div class="stat-content">
                <h3>Total Carteras</h3>
                <p class="stat-value"><?php echo count($todas_carteras); ?></p>
            </div>
            
            <div class="stat-card">
                <div class="stat-icon">
                    <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <path d="M9 12l2 2 4-4M7.835 4.697a3.42 3.42 0 001.946-.806 3.42 3.42 0 014.438 0 3.42 3.42 0 001.946.806 3.42 3.42 0 013.138 3.138 3.42 3.42 0 00.806 1.946 3.42 3.42 0 010 4.438 3.42 3.42 0 00-.806 1.946 3.42 3.42 0 01-3.138 3.138 3.42 3.42 0 00-1.946.806 3.42 3.42 0 01-4.438 0 3.42 3.42 0 00-1.946-.806 3.42 3.42 0 01-3.138-3.138 3.42 3.42 0 00-.806-1.946 3.42 3.42 0 010-4.438 3.42 3.42 0 00.806-1.946 3.42 3.42 0 013.138-3.138z"/>
                    </svg>
                </div>
                <div class="stat-content">
                    <h3>Total Tarjetas</h3>
                    <p class="stat-value"><?php echo count($todas_tarjetas); ?></p>
                </div>
            </div>
        </div>
    </div>

    <!-- Estadísticas por Trabajador -->
    <div class="section">
        <h2>Análisis por Trabajador</h2>
        <div class="table-container">
            <table class="data-table">
                <thead>
                    <tr>
                        <th>Trabajador ID</th>
                        <th>Total Cobrado</th>
                        <th>Saldo Pendiente</th>
                        <th>Total</th>
                    </tr>
                </thead>
                <tbody>
                    <?php 
                    global $usuarios_simulados;
                    foreach ($stats['por_trabajador'] as $trab_id => $data): 
                        $trabajador = array_filter($usuarios_simulados, function($u) use ($trab_id) {
                            return $u['id'] == $trab_id;
                        });
                        $trabajador = reset($trabajador);
                        $nombre = $trabajador ? $trabajador['nombre'] : "ID $trab_id";
                    ?>
                    <tr>
                        <td><?php echo htmlspecialchars($nombre); ?></td>
                        <td>$<?php echo number_format($data['cobrado'], 2); ?></td>
                        <td>$<?php echo number_format($data['pendiente'], 2); ?></td>
                        <td><strong>$<?php echo number_format($data['cobrado'] + $data['pendiente'], 2); ?></strong></td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>

    <!-- Acciones Rápidas -->
    <div class="section">
        <h2>Acciones Rápidas</h2>
        <div class="action-buttons">
                    <a href="<?php echo BASE_URL; ?>controllers/AdminController.php?action=carteras" class="btn btn-primary">
                        Ver Todas las Carteras
                    </a>
                    <a href="<?php echo BASE_URL; ?>controllers/AdminController.php?action=tarjetas" class="btn btn-info">
                        Ver Todas las Tarjetas
                    </a>
            <a href="<?php echo BASE_URL; ?>controllers/AdminController.php?action=alta_cartera&tipo=semanal" class="btn btn-success">
                Nueva Cartera Semanal
            </a>
            <a href="<?php echo BASE_URL; ?>controllers/AdminController.php?action=alta_cartera&tipo=diaria" class="btn btn-success">
                Nueva Cartera Diaria
            </a>
            <a href="<?php echo BASE_URL; ?>controllers/AdminController.php?action=alta_cartera&tipo=nueva" class="btn btn-success">
                Nueva Cartera
            </a>
            <a href="<?php echo BASE_URL; ?>controllers/AdminController.php?action=trabajadores" class="btn btn-info">
                Gestión de Trabajadores
            </a>
        </div>
    </div>

            <!-- Carteras -->
            <div class="section">
                <h2>Carteras por Trabajador</h2>
                <div class="table-container">
                    <table class="data-table">
                        <thead>
                            <tr>
                                <th>ID Cartera</th>
                                <th>Trabajador</th>
                                <th>Total Tarjetas</th>
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
                                <td><?php echo $total_tarjetas; ?> tarjetas</td>
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
            
            <!-- Tarjetas Recientes -->
            <div class="section">
                <h2>Tarjetas Recientes</h2>
                <div class="table-container">
                    <table class="data-table">
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Tipo</th>
                                <th>Cliente</th>
                                <th>Total</th>
                                <th>Estado</th>
                                <th>Acciones</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach (array_slice($todas_tarjetas, 0, 5) as $tarjeta): 
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
                                <td>$<?php echo number_format($total, 2); ?></td>
                                <td>
                                    <div class="progress-bar">
                                        <div class="progress-fill" style="width: <?php echo $porcentaje; ?>%"></div>
                                        <span class="progress-text"><?php echo number_format($porcentaje, 1); ?>%</span>
                                    </div>
                                </td>
                                <td>
                                    <a href="<?php echo BASE_URL; ?>controllers/AdminController.php?action=detalle_tarjeta&id=<?php echo $tarjeta['id']; ?>" 
                                       class="btn btn-sm btn-primary">Ver</a>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
</div>

<?php include __DIR__ . '/../layouts/footer.php'; ?>

