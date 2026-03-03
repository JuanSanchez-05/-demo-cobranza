<?php
$titulo = 'Detalle de Cartera - Trabajador';
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

<div class="dashboard-container">
    <div class="page-header">
        <h1>Detalle de Cartera #<?php echo $cartera['id']; ?></h1>
        <a href="<?php echo BASE_URL; ?>controllers/TrabajadorController.php?action=carteras" class="btn btn-secondary">
            ← Volver
        </a>
    </div>

    <?php if (isset($_GET['mensaje']) && $_GET['mensaje'] === 'pago_registrado'): ?>
        <div class="alert alert-success">Pago registrado exitosamente</div>
    <?php endif; ?>

    <!-- Resumen -->
    <div class="card">
        <div class="card-header">
            <h2>Resumen del Préstamo</h2>
        </div>
        <div class="card-body">
            <div class="stats-grid">
                <div class="stat-card">
                    <h3>Total Préstamo</h3>
                    <p class="stat-value">$<?php echo number_format($total, 2); ?></p>
                </div>
                <div class="stat-card">
                    <h3>Cobrado</h3>
                    <p class="stat-value text-success">$<?php echo number_format($cobrado, 2); ?></p>
                </div>
                <div class="stat-card">
                    <h3>Pendiente</h3>
                    <p class="stat-value text-warning">$<?php echo number_format($pendiente, 2); ?></p>
                </div>
                <div class="stat-card">
                    <h3>Progreso</h3>
                    <div class="progress-bar">
                        <div class="progress-fill" style="width: <?php echo $porcentaje; ?>%"></div>
                        <span class="progress-text"><?php echo number_format($porcentaje, 1); ?>%</span>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Información del Cliente -->
    <div class="card">
        <div class="card-header">
            <h2>Información del Cliente</h2>
        </div>
        <div class="card-body">
            <?php if ($cartera['tipo'] === 'antigua_semanal'): ?>
                <div class="info-grid">
                    <div><strong>Nombre:</strong> <?php echo htmlspecialchars($cartera['nombre']); ?></div>
                    <div><strong>Teléfono:</strong> <?php echo htmlspecialchars($cartera['telefono']); ?></div>
                    <div><strong>Dirección:</strong> <?php echo htmlspecialchars($cartera['direccion']); ?></div>
                    <div><strong>Colonia:</strong> <?php echo htmlspecialchars($cartera['colonia']); ?></div>
                    <div><strong>Día de Cobro:</strong> <?php echo htmlspecialchars($cartera['dia_cobro']); ?></div>
                    <div><strong>Pago Semanal:</strong> $<?php echo number_format($cartera['pago_semanal'], 2); ?></div>
                </div>
            <?php elseif ($cartera['tipo'] === 'antigua_diaria'): ?>
                <div class="info-grid">
                    <div><strong>Teléfono:</strong> <?php echo htmlspecialchars($cartera['telefono']); ?></div>
                    <div><strong>Cuota Diaria:</strong> $<?php echo number_format($cartera['cuota_diaria'], 2); ?></div>
                </div>
            <?php else: ?>
                <div class="info-grid">
                    <div><strong>Nombre:</strong> <?php echo htmlspecialchars($cartera['nombre']); ?></div>
                    <div><strong>Teléfono:</strong> <?php echo htmlspecialchars($cartera['telefono']); ?></div>
                    <div><strong>Dirección:</strong> <?php echo htmlspecialchars($cartera['direccion']); ?></div>
                    <div><strong>Dirección de Cobranza:</strong> <?php echo htmlspecialchars($cartera['direccion_cobranza']); ?></div>
                    <div><strong>Día de Cobro:</strong> <?php echo htmlspecialchars($cartera['dia_cobro']); ?></div>
                    <div><strong>Hora de Cobro:</strong> <?php echo htmlspecialchars($cartera['hora_cobro']); ?></div>
                </div>
            <?php endif; ?>
        </div>
    </div>

    <!-- Tabla de Pagos -->
    <div class="card">
        <div class="card-header">
            <h2>Historial de Pagos</h2>
        </div>
        <div class="card-body">
            <div class="table-container">
                <table class="data-table">
                    <thead>
                        <tr>
                            <th>Número de Día</th>
                            <th>Fecha</th>
                            <th>Pago Realizado</th>
                            <th>Saldo Pendiente</th>
                            <th>Firma del Empleado</th>
                            <th>Acción</th>
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
                            $firmado = $pago_existente ? $pago_existente['firma'] : false;
                        ?>
                        <tr class="<?php echo $monto_pago > 0 ? 'row-paid' : ''; ?>">
                            <td><?php echo $i; ?></td>
                            <td><?php echo htmlspecialchars($fecha_pago); ?></td>
                            <td class="<?php echo $monto_pago > 0 ? 'text-success' : ''; ?>">
                                $<?php echo number_format($monto_pago, 2); ?>
                            </td>
                            <td>$<?php echo number_format($saldo, 2); ?></td>
                            <td>
                                <?php if ($firmado): ?>
                                    <span class="badge badge-success">✓ Firmado</span>
                                <?php else: ?>
                                    <span class="badge badge-secondary">Pendiente</span>
                                <?php endif; ?>
                            </td>
                            <td>
                                <?php if (!$firmado): ?>
                                    <form method="POST" style="display: inline;">
                                        <input type="hidden" name="dia" value="<?php echo $i; ?>">
                                        <button type="submit" name="registrar_pago" class="btn btn-sm btn-success">
                                            Registrar Pago
                                        </button>
                                    </form>
                                <?php else: ?>
                                    <span class="text-muted">Ya registrado</span>
                                <?php endif; ?>
                            </td>
                        </tr>
                        <?php endfor; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<?php include __DIR__ . '/../layouts/footer.php'; ?>

