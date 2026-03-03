<?php
$titulo = 'Detalle de Tarjeta - Administrador';
include __DIR__ . '/../layouts/header.php';

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

<div class="dashboard-container">
    <div class="page-header">
        <h1>Detalle de Tarjeta #<?php echo $tarjeta['id']; ?></h1>
        <a href="<?php echo BASE_URL; ?>controllers/AdminController.php?action=tarjetas" class="btn btn-secondary">
            ← Volver a Tarjetas
        </a>
    </div>

    <!-- Información General -->
    <div class="card">
        <div class="card-header">
            <h2>Información General</h2>
            <span class="badge badge-<?php 
                echo $tarjeta['tipo'] === 'antigua_semanal' ? 'info' : 
                    ($tarjeta['tipo'] === 'antigua_diaria' ? 'warning' : 'success'); 
            ?>">
                <?php 
                echo $tarjeta['tipo'] === 'antigua_semanal' ? 'Semanal' : 
                    ($tarjeta['tipo'] === 'antigua_diaria' ? 'Diaria' : 'Nueva'); 
                ?>
            </span>
        </div>
        <div class="card-body">
            <div class="info-grid">
                <div><strong>Trabajador:</strong> <?php echo htmlspecialchars($nombre_trab); ?></div>
                <?php if ($tarjeta['tipo'] === 'antigua_semanal'): ?>
                    <div><strong>Lugar:</strong> <?php echo htmlspecialchars($tarjeta['lugar']); ?></div>
                    <div><strong>Fecha:</strong> <?php echo htmlspecialchars($tarjeta['fecha']); ?></div>
                    <div><strong>Nombre:</strong> <?php echo htmlspecialchars($tarjeta['nombre']); ?></div>
                    <div><strong>Teléfono:</strong> <?php echo htmlspecialchars($tarjeta['telefono']); ?></div>
                    <div><strong>Dirección:</strong> <?php echo htmlspecialchars($tarjeta['direccion']); ?></div>
                    <div><strong>Colonia:</strong> <?php echo htmlspecialchars($tarjeta['colonia']); ?></div>
                    <div><strong>Cantidad Préstamo:</strong> $<?php echo number_format($tarjeta['cantidad_prestamo'], 2); ?></div>
                    <div><strong>Cargo del Préstamo:</strong> $<?php echo number_format($tarjeta['cargo_prestamo'], 2); ?></div>
                    <div><strong>Total del Préstamo:</strong> $<?php echo number_format($tarjeta['total_prestamo'], 2); ?> 
                        <small>(Calculado: <?php echo number_format($tarjeta['cantidad_prestamo'], 2); ?> + <?php echo number_format($tarjeta['cargo_prestamo'], 2); ?>)</small>
                    </div>
                    <div><strong>Pago Semanal:</strong> $<?php echo number_format($tarjeta['pago_semanal'], 2); ?></div>
                    <div><strong>Semanas a Pagar:</strong> <?php echo $tarjeta['semanas_pagar']; ?> 
                        <small>(Calculado: <?php echo number_format($tarjeta['total_prestamo'], 2); ?> / <?php echo number_format($tarjeta['pago_semanal'], 2); ?>)</small>
                    </div>
                    <div><strong>Día de Cobro:</strong> <?php echo htmlspecialchars($tarjeta['dia_cobro']); ?></div>
                    <?php 
                    global $usuarios_simulados;
                    $promotor = array_filter($usuarios_simulados, function($u) use ($tarjeta) {
                        return $u['id'] == ($tarjeta['promotor_id'] ?? 0);
                    });
                    $promotor = reset($promotor);
                    $nombre_promotor = $promotor ? $promotor['nombre'] : 'N/A';
                    ?>
                    <div><strong>Promotor:</strong> <?php echo htmlspecialchars($nombre_promotor); ?></div>
                <?php elseif ($tarjeta['tipo'] === 'antigua_diaria'): ?>
                    <div><strong>Nombre:</strong> <?php echo htmlspecialchars($tarjeta['nombre'] ?? 'N/A'); ?></div>
                    <div><strong>Cuota Diaria:</strong> $<?php echo number_format($tarjeta['cuota_diaria'], 2); ?></div>
                    <div><strong>Fecha:</strong> <?php echo htmlspecialchars($tarjeta['fecha'] ?? 'N/A'); ?></div>
                    <div><strong>Dirección:</strong> <?php echo htmlspecialchars($tarjeta['direccion'] ?? 'N/A'); ?></div>
                    <div><strong>Teléfono:</strong> <?php echo htmlspecialchars($tarjeta['telefono'] ?? 'N/A'); ?></div>
                    <div><strong>Valor Total:</strong> $<?php echo number_format($tarjeta['valor'] ?? 0, 2); ?></div>
                <?php else: ?>
                    <div><strong>Fecha:</strong> <?php echo htmlspecialchars($tarjeta['fecha']); ?></div>
                    <div><strong>Lugar:</strong> <?php echo htmlspecialchars($tarjeta['lugar']); ?></div>
                    <div><strong>Nombre:</strong> <?php echo htmlspecialchars($tarjeta['nombre']); ?></div>
                    <div><strong>Teléfono:</strong> <?php echo htmlspecialchars($tarjeta['telefono']); ?></div>
                    <div><strong>Dirección:</strong> <?php echo htmlspecialchars($tarjeta['direccion']); ?></div>
                    <div><strong>Colonia:</strong> <?php echo htmlspecialchars($tarjeta['colonia']); ?></div>
                    <div><strong>Giro:</strong> <?php echo htmlspecialchars($tarjeta['giro']); ?></div>
                    <div><strong>Dirección Cobranza:</strong> <?php echo htmlspecialchars($tarjeta['direccion_cobranza']); ?></div>
                    <div><strong>Aval Nombre:</strong> <?php echo htmlspecialchars($tarjeta['aval_nombre']); ?></div>
                    <div><strong>Aval Dirección:</strong> <?php echo htmlspecialchars($tarjeta['aval_direccion']); ?></div>
                    <div><strong>Aval Colonia:</strong> <?php echo htmlspecialchars($tarjeta['aval_colonia']); ?></div>
                    <div><strong>Aval Teléfono:</strong> <?php echo htmlspecialchars($tarjeta['aval_telefono']); ?></div>
                    <div><strong>Préstamo:</strong> $<?php echo number_format($tarjeta['prestamo'], 2); ?></div>
                    <div><strong>Cuota del Préstamo:</strong> $<?php echo number_format($tarjeta['cuota_prestamo'], 2); ?></div>
                    <div><strong>Total del Préstamo:</strong> $<?php echo number_format($tarjeta['total_prestamo'], 2); ?></div>
                    <div><strong>Pago:</strong> $<?php echo number_format($tarjeta['pago'], 2); ?></div>
                    <div><strong>Días a Pagar:</strong> <?php echo $tarjeta['dias_pagar']; ?></div>
                    <div><strong>Día de Cobro:</strong> <?php echo htmlspecialchars($tarjeta['dia_cobro']); ?></div>
                    <div><strong>Hora de Cobro:</strong> <?php echo htmlspecialchars($tarjeta['hora_cobro']); ?></div>
                    <?php 
                    global $usuarios_simulados;
                    $promotor = array_filter($usuarios_simulados, function($u) use ($tarjeta) {
                        return $u['id'] == ($tarjeta['promotor_id'] ?? 0);
                    });
                    $promotor = reset($promotor);
                    $nombre_promotor = $promotor ? $promotor['nombre'] : 'N/A';
                    ?>
                    <div><strong>Promotor:</strong> <?php echo htmlspecialchars($nombre_promotor); ?></div>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <!-- Resumen de Pagos -->
    <div class="card">
        <div class="card-header">
            <h2>Resumen de Pagos</h2>
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
                        </tr>
                    </thead>
                    <tbody>
                        <?php 
                        $semanas = $tarjeta['semanas_pagar'] ?? ($tarjeta['dias_pagar'] ?? 12);
                        $pago_unitario = $tarjeta['pago_semanal'] ?? ($tarjeta['cuota_diaria'] ?? ($tarjeta['pago'] ?? 0));
                        
                        for ($i = 1; $i <= $semanas; $i++): 
                            $pago_existente = null;
                            if (isset($tarjeta['pagos'])) {
                                foreach ($tarjeta['pagos'] as $p) {
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
                        </tr>
                        <?php endfor; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<?php include __DIR__ . '/../layouts/footer.php'; ?>

