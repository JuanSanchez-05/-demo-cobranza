<?php
$titulo = 'Detalle Diario de Cobrador - Administrador';
include __DIR__ . '/../layouts/header.php';
?>

<div class="dashboard-container">
    <div class="page-header">
        <h1>Detalle diario: <?php echo htmlspecialchars($trabajador['nombre'] ?? 'Cobrador'); ?></h1>
        <a href="<?php echo BASE_URL; ?>controllers/AdminController.php?action=dashboard" class="btn btn-secondary">
            ← Volver al Dashboard
        </a>
    </div>

    <div class="stats-grid">
        <div class="stat-card">
            <div class="stat-content">
                <h3>Cobrado Hoy</h3>
                <p class="stat-value">$<?php echo number_format($stats_cobrador['cobrado_hoy'] ?? 0, 2); ?></p>
            </div>
        </div>
        <div class="stat-card">
            <div class="stat-content">
                <h3>Pendiente Hoy</h3>
                <p class="stat-value">$<?php echo number_format($stats_cobrador['pendiente_hoy'] ?? 0, 2); ?></p>
            </div>
        </div>
        <div class="stat-card">
            <div class="stat-content">
                <h3>Tarjetas Activas</h3>
                <p class="stat-value"><?php echo intval($stats_cobrador['total_tarjetas'] ?? 0); ?></p>
            </div>
        </div>
        <div class="stat-card">
            <div class="stat-content">
                <h3>Completadas</h3>
                <p class="stat-value"><?php echo intval($stats_cobrador['completadas'] ?? 0); ?></p>
            </div>
        </div>
    </div>

    <div class="section">
        <h2>Cobros registrados hoy</h2>
        <div class="table-container">
            <table class="data-table">
                <thead>
                    <tr>
                        <th>Cliente</th>
                        <th>Dirección</th>
                        <th>Día</th>
                        <th>Monto Cobrado</th>
                        <th>Acción</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($cobros_registrados_hoy as $cobro): ?>
                        <tr>
                            <td><?php echo htmlspecialchars($cobro['nombre'] ?? ''); ?></td>
                            <td><?php echo htmlspecialchars($cobro['direccion'] ?? ''); ?></td>
                            <td><?php echo intval($cobro['dia'] ?? 0); ?></td>
                            <td class="text-success">$<?php echo number_format($cobro['ya_cobrado_monto'] ?? 0, 2); ?></td>
                            <td>
                                <a href="<?php echo BASE_URL; ?>controllers/AdminController.php?action=detalle_tarjeta&id=<?php echo $cobro['id']; ?>" class="btn btn-sm btn-primary">Ver tarjeta</a>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                    <?php if (empty($cobros_registrados_hoy)): ?>
                        <tr>
                            <td colspan="5" style="text-align:center;color:#888;">No hay cobros registrados hoy para este cobrador.</td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<?php include __DIR__ . '/../layouts/footer.php'; ?>
