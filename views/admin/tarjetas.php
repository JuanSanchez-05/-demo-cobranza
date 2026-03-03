<?php
$titulo = 'Tarjetas - Administrador';
include __DIR__ . '/../layouts/header.php';
?>

<div class="dashboard-container">
    <div class="page-header">
        <h1>Gestión de Tarjetas</h1>
        <a href="<?php echo BASE_URL; ?>controllers/AdminController.php?action=dashboard" class="btn btn-secondary">
            ← Volver al Panel de Control
        </a>
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
                    <th>Trabajador</th>
                    <th>Acciones</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($todas_tarjetas as $tarjeta): 
                    $total = $tarjeta['total_prestamo'] ?? ($tarjeta['valor'] ?? 0);
                    $cobrado = 0;
                    if (isset($tarjeta['pagos'])) {
                        foreach ($tarjeta['pagos'] as $pago) {
                            $cobrado += $pago['pago'];
                        }
                    }
                    $pendiente = $total - $cobrado;
                    $porcentaje = $total > 0 ? ($cobrado / $total) * 100 : 0;
                    
                    global $usuarios_simulados;
                    $trabajador = array_filter($usuarios_simulados, function($u) use ($tarjeta) {
                        return $u['id'] == $tarjeta['trabajador_id'];
                    });
                    $trabajador = reset($trabajador);
                    $nombre_trab = $trabajador ? $trabajador['nombre'] : "ID " . $tarjeta['trabajador_id'];
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
                    <td><?php echo htmlspecialchars($nombre_trab); ?></td>
                    <td>
                        <a href="<?php echo BASE_URL; ?>controllers/AdminController.php?action=detalle_tarjeta&id=<?php echo $tarjeta['id']; ?>" 
                           class="btn btn-sm btn-primary">Ver Detalle</a>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>

<?php include __DIR__ . '/../layouts/footer.php'; ?>

