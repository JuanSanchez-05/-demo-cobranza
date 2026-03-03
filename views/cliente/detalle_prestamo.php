<?php
$titulo = 'Detalle de Préstamo - Cliente';
include __DIR__ . '/../layouts/header.php';

$total = $cartera['total_prestamo'] ?? ($cartera['valor'] ?? 0);
$cobrado = 0;
if (isset($cartera['pagos'])) {
    foreach ($cartera['pagos'] as $pago) {
        $cobrado += $pago['pago'];
    }
}
$pendiente = $total - $cobrado;
$porcentaje = $total > 0 ? ($cobrado / $total) * 100 : 0;
?>

<div class="dashboard-container cliente-dashboard">
    <div class="page-header">
        <h1>Detalle de Préstamo #<?php echo $cartera['id']; ?></h1>
        <a href="<?php echo BASE_URL; ?>controllers/ClienteController.php?action=dashboard" class="btn btn-secondary">
            ← Volver
        </a>
    </div>

    <!-- Resumen Visual -->
    <div class="cliente-summary">
        <div class="summary-card">
            <div class="summary-icon">
                <svg width="32" height="32" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <path d="M12 2v20M17 5H9.5a3.5 3.5 0 0 0 0 7h5a3.5 3.5 0 0 1 0 7H6"/>
                </svg>
            </div>
            <div class="summary-content">
                <h2>Total del Préstamo</h2>
                <p class="summary-value">$<?php echo number_format($total, 2); ?></p>
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
                <p class="summary-value success">$<?php echo number_format($cobrado, 2); ?></p>
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
                <p class="summary-value warning">$<?php echo number_format($pendiente, 2); ?></p>
            </div>
        </div>
    </div>

    <!-- Barra de Progreso -->
    <div class="cliente-progress-section">
        <h2>Progreso del Préstamo</h2>
        <div class="cliente-progress-bar">
            <div class="cliente-progress-fill" style="width: <?php echo $porcentaje; ?>%">
                <span class="cliente-progress-text"><?php echo number_format($porcentaje, 1); ?>%</span>
            </div>
        </div>
        <p class="progress-info">
            Has pagado $<?php echo number_format($cobrado, 2); ?> de $<?php echo number_format($total, 2); ?>
        </p>
    </div>

    <!-- Historial de Pagos -->
    <div class="cliente-pagos-section">
        <h2>Historial de Pagos</h2>
        <div class="pagos-table-container">
            <table class="pagos-table">
                <thead>
                    <tr>
                        <th>Número</th>
                        <th>Fecha</th>
                        <th>Pago Realizado</th>
                        <th>Saldo Pendiente</th>
                        <th>Estado</th>
                    </tr>
                </thead>
                <tbody>
                    <?php 
                    $semanas = $cartera['semanas_pagar'] ?? ($cartera['dias_pagar'] ?? 12);
                    $pago_unitario = $cartera['pago_semanal'] ?? ($cartera['cuota_diaria'] ?? ($cartera['pago'] ?? 0));
                    
                    for ($i = 1; $i <= $semanas; $i++): 
                        $pago_existente = null;
                        if (isset($cartera['pagos'])) {
                            foreach ($cartera['pagos'] as $p) {
                                if ($p['dia'] == $i) {
                                    $pago_existente = $p;
                                    break;
                                }
                            }
                        }
                        
                        $fecha_pago = $pago_existente ? $pago_existente['fecha'] : 'Pendiente';
                        $monto_pago = $pago_existente ? $pago_existente['pago'] : 0;
                        $saldo = $pago_existente ? $pago_existente['saldo'] : ($total - ($i - 1) * $pago_unitario);
                        $pagado = $monto_pago > 0;
                    ?>
                    <tr class="<?php echo $pagado ? 'pago-realizado' : ''; ?>">
                        <td><?php echo $i; ?></td>
                        <td><?php echo htmlspecialchars($fecha_pago); ?></td>
                        <td class="<?php echo $pagado ? 'text-success' : ''; ?>">
                            $<?php echo number_format($monto_pago, 2); ?>
                        </td>
                        <td>$<?php echo number_format($saldo, 2); ?></td>
                        <td>
                            <?php if ($pagado): ?>
                                <span class="badge badge-success">✓ Pagado</span>
                            <?php else: ?>
                                <span class="badge badge-secondary">Pendiente</span>
                            <?php endif; ?>
                        </td>
                    </tr>
                    <?php endfor; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<?php include __DIR__ . '/../layouts/footer.php'; ?>

