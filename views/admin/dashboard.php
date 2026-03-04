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
                <h3>Cobrado Hoy</h3>
                <p class="stat-value">$<?php echo number_format($stats['cobrado_hoy'], 2); ?></p>
            </div>
        </div>

        <div class="stat-card">
            <div class="stat-icon">
                <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <path d="M9 11l3 3L22 4M21 12v7a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h11"/>
                </svg>
            </div>
            <div class="stat-content">
                <h3>Saldo Pendiente Total</h3>
                <p class="stat-value">$<?php echo number_format($stats['pendiente_total'], 2); ?></p>
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
        </div>

        <div class="stat-card">
            <div class="stat-icon">
                <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <path d="M9 12l2 2 4-4M7.835 4.697a3.42 3.42 0 001.946-.806 3.42 3.42 0 014.438 0 3.42 3.42 0 001.946.806 3.42 3.42 0 013.138 3.138 3.42 3.42 0 00.806 1.946 3.42 3.42 0 010 4.438 3.42 3.42 0 00-.806 1.946 3.42 3.42 0 01-3.138 3.138 3.42 3.42 0 00-1.946.806 3.42 3.42 0 01-4.438 0 3.42 3.42 0 00-1.946-.806 3.42 3.42 0 01-3.138-3.138 3.42 3.42 0 00-.806-1.946 3.42 3.42 0 010-4.438 3.42 3.42 0 00.806-1.946 3.42 3.42 0 013.138-3.138z"/>
                </svg>
            </div>
            <div class="stat-content">
                <h3>Total Tarjetas</h3>
                <p class="stat-value"><?php echo $stats['total_tarjetas']; ?></p>
            </div>
        </div>

        <div class="stat-card">
            <div class="stat-icon">
                <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2M9 7a4 4 0 1 0 8 0 4 4 0 0 0-8 0M23 21v-2a4 4 0 0 0-3-3.87M16 3.13a4 4 0 0 1 0 7.75"/>
                </svg>
            </div>
            <div class="stat-content">
                <h3>Trabajadores Activos</h3>
                <p class="stat-value"><?php echo $stats['total_trabajadores']; ?></p>
            </div>
        </div>
    </div>

    <!-- Acciones Rápidas -->
    <div class="section">
        <h2>Acciones Rápidas</h2>
        <div class="action-buttons">
            <a href="<?php echo BASE_URL; ?>controllers/AdminController.php?action=carteras" class="btn btn-primary">Ver Todas las Carteras</a>
            <a href="<?php echo BASE_URL; ?>controllers/AdminController.php?action=tarjetas" class="btn btn-info">Ver Todas las Tarjetas</a>
            <a href="<?php echo BASE_URL; ?>controllers/AdminController.php?action=alta_tarjeta&tipo=semanal" class="btn btn-success">+ Tarjeta Semanal</a>
            <a href="<?php echo BASE_URL; ?>controllers/AdminController.php?action=alta_tarjeta&tipo=diaria" class="btn btn-success">+ Tarjeta Diaria</a>
            <a href="<?php echo BASE_URL; ?>controllers/AdminController.php?action=alta_tarjeta&tipo=nueva" class="btn btn-success">+ Tarjeta Nueva</a>
            <a href="<?php echo BASE_URL; ?>controllers/AdminController.php?action=trabajadores" class="btn btn-info">Gestión de Trabajadores</a>
        </div>
    </div>

    <div class="section">
        <h2>Detalle por Cobrador</h2>
        <div class="table-container">
            <table class="data-table">
                <thead>
                    <tr>
                        <th>Cobrador</th>
                        <th>Cobrado Hoy</th>
                        <th>Pendiente Hoy</th>
                        <th>Tarjetas Activas</th>
                        <th>Completadas</th>
                        <th>Acción</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($cobradores_detalle as $cobrador): ?>
                    <tr>
                        <td><?php echo htmlspecialchars($cobrador['nombre']); ?></td>
                        <td class="text-success">$<?php echo number_format($cobrador['cobrado_hoy'], 2); ?></td>
                        <td>$<?php echo number_format($cobrador['pendiente_hoy'], 2); ?></td>
                        <td><?php echo intval($cobrador['tarjetas_activas']); ?></td>
                        <td><?php echo intval($cobrador['completadas']); ?></td>
                        <td>
                            <a href="<?php echo BASE_URL; ?>controllers/AdminController.php?action=detalle_cobrador&id=<?php echo $cobrador['id']; ?>"
                               class="btn btn-sm btn-primary">Ver detalle hoy</a>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                    <?php if (empty($cobradores_detalle)): ?>
                    <tr><td colspan="6" style="text-align:center;color:#888;">No hay cobradores activos</td></tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>

    <!-- Carteras por Trabajador -->
    <div class="section">
        <h2>Carteras por Trabajador</h2>
        <div class="table-container">
            <table class="data-table">
                <thead>
                    <tr>
                        <th>Cartera</th>
                        <th>Trabajador</th>
                        <th>Tarjetas Activas</th>
                        <th>Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($todas_carteras as $cartera): ?>
                    <tr>
                        <td><?php echo htmlspecialchars($cartera['nombre']); ?></td>
                        <td><?php echo htmlspecialchars($cartera['trabajador_nombre']); ?></td>
                        <td><?php echo count($cartera['tarjetas']); ?> tarjetas</td>
                        <td>
                            <a href="<?php echo BASE_URL; ?>controllers/AdminController.php?action=detalle_cartera&id=<?php echo $cartera['id']; ?>"
                               class="btn btn-sm btn-primary">Ver Cartera</a>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                    <?php if (empty($todas_carteras)): ?>
                    <tr><td colspan="4" style="text-align:center;color:#888;">No hay carteras registradas</td></tr>
                    <?php endif; ?>
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
                        <th>Cliente</th>
                        <th>Tipo</th>
                        <th>Trabajador</th>
                        <th>Total Préstamo</th>
                        <th>Avance</th>
                        <th>Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach (array_slice($todas_tarjetas, 0, 10) as $tarjeta):
                        $total   = $tarjeta['total_prestamo'] ?? 0;
                        $cobrado = !empty($tarjeta['pagos']) ? array_sum(array_column($tarjeta['pagos'], 'pago')) : 0;
                        $pct     = $total > 0 ? min(100, ($cobrado / $total) * 100) : 0;
                    ?>
                    <tr>
                        <td><?php echo htmlspecialchars($tarjeta['nombre']); ?></td>
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
                        <td><?php echo htmlspecialchars($tarjeta['trabajador_nombre'] ?? '-'); ?></td>
                        <td>$<?php echo number_format($total, 2); ?></td>
                        <td>
                            <div class="progress-bar">
                                <div class="progress-fill" style="width:<?php echo $pct; ?>%"></div>
                                <span class="progress-text"><?php echo number_format($pct, 1); ?>%</span>
                            </div>
                        </td>
                        <td>
                            <a href="<?php echo BASE_URL; ?>controllers/AdminController.php?action=detalle_tarjeta&id=<?php echo $tarjeta['id']; ?>"
                               class="btn btn-sm btn-primary">Ver</a>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                    <?php if (empty($todas_tarjetas)): ?>
                    <tr><td colspan="6" style="text-align:center;color:#888;">No hay tarjetas registradas</td></tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<?php include __DIR__ . '/../layouts/footer.php'; ?>

