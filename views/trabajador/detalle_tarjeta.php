<?php
// Version 2.5 - CSS simplificado sin ::before, tabla normal
$titulo = 'Detalle de Tarjeta - Trabajador';
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
?>

<div class="dashboard-container">
    <div class="page-header">
        <h1>Detalle de Tarjeta #<?php echo $tarjeta['id']; ?></h1>
        <a href="<?php echo BASE_URL; ?>controllers/TrabajadorController.php?action=carteras" class="btn btn-secondary">
            ← Volver a Mi Cartera
        </a>
    </div>

    <?php if (isset($_GET['mensaje'])): ?>
        <?php if ($_GET['mensaje'] === 'pago_registrado'): ?>
            <div class="alert alert-success">✓ Pago registrado exitosamente</div>
        <?php elseif ($_GET['mensaje'] === 'pendiente_marcado'): ?>
            <div class="alert alert-info">⭕ Visita registrada como pendiente. Se completará cuando se realice el pago.</div>
        <?php elseif ($_GET['mensaje'] === 'dia_extra_agregado'): ?>
            <div class="alert alert-success">✓ Se agregó un día extra para seguir cobrando el saldo pendiente.</div>
        <?php elseif ($_GET['mensaje'] === 'error_pago'): ?>
            <div class="alert alert-warning">⚠ Este pago ya fue registrado anteriormente</div>
        <?php elseif ($_GET['mensaje'] === 'error_dia'): ?>
            <div class="alert alert-danger">✗ Error al procesar el pago</div>
        <?php endif; ?>
    <?php endif; ?>

    <?php if (isset($_GET['error'])): ?>
        <?php if ($_GET['error'] === 'datos_invalidos'): ?>
            <div class="alert alert-danger">✗ Datos inválidos. Verifica el día y el monto del pago.</div>
        <?php elseif ($_GET['error'] === 'error_pago'): ?>
            <div class="alert alert-danger">✗ Error al registrar el pago. Intenta nuevamente.</div>
        <?php elseif ($_GET['error'] === 'error_pendiente'): ?>
            <div class="alert alert-danger">✗ Error al marcar como pendiente. Intenta nuevamente.</div>
        <?php elseif ($_GET['error'] === 'orden_pago_invalido'): ?>
            <div class="alert alert-warning">⚠ Debes registrar los pagos en orden. Primero completa el siguiente período pendiente.</div>
        <?php elseif ($_GET['error'] === 'monto_excede_saldo'): ?>
            <div class="alert alert-danger">✗ El monto ingresado excede lo permitido para este día. Solo puedes cobrar el monto del día más atrasos anteriores, sin pasar la deuda total.</div>
        <?php elseif ($_GET['error'] === 'error_dia_extra'): ?>
            <div class="alert alert-danger">✗ No fue posible agregar un día extra. Verifica que aún exista saldo pendiente.</div>
        <?php endif; ?>
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
            <?php if ($tarjeta['tipo'] === 'antigua_semanal'): ?>
                <div class="info-grid">
                    <div><strong>Lugar:</strong> <?php echo htmlspecialchars($tarjeta['lugar'] ?? 'N/A'); ?></div>
                    <div><strong>Fecha:</strong> <?php echo htmlspecialchars($tarjeta['fecha'] ?? 'N/A'); ?></div>
                    <div><strong>Nombre:</strong> <?php echo htmlspecialchars($tarjeta['nombre']); ?></div>
                    <div><strong>Teléfono:</strong> <?php echo htmlspecialchars($tarjeta['telefono']); ?></div>
                    <div><strong>Dirección:</strong> <?php echo htmlspecialchars($tarjeta['direccion']); ?></div>
                    <div><strong>Colonia:</strong> <?php echo htmlspecialchars($tarjeta['colonia']); ?></div>
                    <div><strong>Cantidad Préstamo:</strong> $<?php echo number_format($tarjeta['cantidad_prestamo'] ?? 0, 2); ?></div>
                    <div><strong>Cargo del Préstamo:</strong> $<?php echo number_format($tarjeta['cargo_prestamo'] ?? 0, 2); ?></div>
                    <div><strong>Total del Préstamo:</strong> $<?php echo number_format($tarjeta['total_prestamo'], 2); ?></div>
                    <div><strong>Pago Semanal:</strong> $<?php echo number_format($tarjeta['pago_semanal'], 2); ?></div>
                    <div><strong>Semanas a Pagar:</strong> <?php echo $tarjeta['semanas_pagar']; ?></div>
                    <div><strong>Día de Cobro:</strong> <?php echo htmlspecialchars($tarjeta['dia_cobro']); ?></div>
                    <?php $nombre_promotor = obtenerNombreUsuarioPorId($tarjeta['promotor_id'] ?? 0); ?>
                    <div><strong>Promotor:</strong> <?php echo htmlspecialchars($nombre_promotor); ?></div>
                </div>
            <?php elseif ($tarjeta['tipo'] === 'antigua_diaria'): ?>
                <div class="info-grid">
                    <div><strong>Nombre:</strong> <?php echo htmlspecialchars($tarjeta['nombre'] ?? 'N/A'); ?></div>
                    <div><strong>Cuota Diaria:</strong> $<?php echo number_format($tarjeta['cuota_diaria'], 2); ?></div>
                    <div><strong>Fecha:</strong> <?php echo htmlspecialchars($tarjeta['fecha'] ?? 'N/A'); ?></div>
                    <div><strong>Dirección:</strong> <?php echo htmlspecialchars($tarjeta['direccion'] ?? 'N/A'); ?></div>
                    <div><strong>Teléfono:</strong> <?php echo htmlspecialchars($tarjeta['telefono'] ?? 'N/A'); ?></div>
                    <div><strong>Valor Total:</strong> $<?php echo number_format($tarjeta['valor'] ?? 0, 2); ?></div>
                </div>
            <?php else: ?>
                <div class="info-grid">
                    <div><strong>Fecha:</strong> <?php echo htmlspecialchars($tarjeta['fecha'] ?? 'N/A'); ?></div>
                    <div><strong>Lugar:</strong> <?php echo htmlspecialchars($tarjeta['lugar'] ?? 'N/A'); ?></div>
                    <div><strong>Nombre:</strong> <?php echo htmlspecialchars($tarjeta['nombre']); ?></div>
                    <div><strong>Teléfono:</strong> <?php echo htmlspecialchars($tarjeta['telefono']); ?></div>
                    <div><strong>Dirección:</strong> <?php echo htmlspecialchars($tarjeta['direccion']); ?></div>
                    <div><strong>Colonia:</strong> <?php echo htmlspecialchars($tarjeta['colonia'] ?? 'N/A'); ?></div>
                    <div><strong>Giro:</strong> <?php echo htmlspecialchars($tarjeta['giro'] ?? 'N/A'); ?></div>
                    <div><strong>Dirección de Cobranza:</strong> <?php echo htmlspecialchars($tarjeta['direccion_cobranza']); ?></div>
                    <div><strong>Aval Nombre:</strong> <?php echo htmlspecialchars($tarjeta['aval_nombre'] ?? 'N/A'); ?></div>
                    <div><strong>Aval Dirección:</strong> <?php echo htmlspecialchars($tarjeta['aval_direccion'] ?? 'N/A'); ?></div>
                    <div><strong>Aval Colonia:</strong> <?php echo htmlspecialchars($tarjeta['aval_colonia'] ?? 'N/A'); ?></div>
                    <div><strong>Aval Teléfono:</strong> <?php echo htmlspecialchars($tarjeta['aval_telefono'] ?? 'N/A'); ?></div>
                    <div><strong>Préstamo:</strong> $<?php echo number_format($tarjeta['prestamo'] ?? 0, 2); ?></div>
                    <div><strong>Total del Préstamo:</strong> $<?php echo number_format($tarjeta['total_prestamo'], 2); ?></div>
                    <div><strong>Pago:</strong> $<?php echo number_format($tarjeta['pago'] ?? 0, 2); ?></div>
                    <div><strong>Días a Pagar:</strong> <?php echo $tarjeta['dias_pagar'] ?? 'N/A'; ?></div>
                    <div><strong>Día de Cobro:</strong> <?php echo htmlspecialchars($tarjeta['dia_cobro']); ?></div>
                    <div><strong>Hora de Cobro:</strong> <?php echo htmlspecialchars($tarjeta['hora_cobro']); ?></div>
                    <?php $nombre_promotor = obtenerNombreUsuarioPorId($tarjeta['promotor_id'] ?? 0); ?>
                    <div><strong>Promotor:</strong> <?php echo htmlspecialchars($nombre_promotor); ?></div>
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
                            <th><?php echo ($tarjeta['tipo'] === 'antigua_semanal') ? 'Período' : 'Número de Día'; ?></th>
                            <th>Fecha</th>
                            <th>Pago Realizado</th>
                            <th>Saldo Pendiente</th>
                            <th>Nota</th>
                            <th>Acción</th>
                        </tr>
                    </thead>
                    <tbody>
                        <!-- DEBUG: Tabla con 5 columnas - Version 2.2 -->
                        <?php 
                        $is_semanal = ($tarjeta['tipo'] === 'antigua_semanal');
                        $semanas = obtenerTotalPeriodosTarjeta($tarjeta);
                        
                        // Calcular el pago unitario según el tipo de tarjeta
                        if ($tarjeta['tipo'] === 'antigua_semanal') {
                            $pago_unitario = floatval($tarjeta['pago_semanal'] ?? 0);
                        } elseif ($tarjeta['tipo'] === 'antigua_diaria') {
                            $pago_unitario = floatval($tarjeta['cuota_diaria'] ?? 0);
                        } else { // nueva
                            $pago_unitario = floatval($tarjeta['pago'] ?? 0);
                        }
                        
                        $primer_pendiente_habilitado = false;
                        $hay_dia_disponible = false;
                        for ($i = 1; $i <= $semanas; $i++): 
                            $dia_buscar = $is_semanal ? ($i * 7) : $i;
                            $etiqueta_periodo = $is_semanal ? "Semana $i" : "Día $i";
                            $pago_existente = null;
                            if (isset($tarjeta['pagos'])) {
                                foreach ($tarjeta['pagos'] as $p) {
                                    if ($p['dia'] == $dia_buscar) {
                                        $pago_existente = $p;
                                        break;
                                    }
                                }
                            }
                            
                            $fecha_pago = $pago_existente ? $pago_existente['fecha'] : 'Pendiente';
                            $fecha_cobro_real = $pago_existente && !empty($pago_existente['fecha_registro']) ? date('Y-m-d', strtotime($pago_existente['fecha_registro'])) : null;
                            $monto_pago = $pago_existente ? floatval($pago_existente['pago']) : 0;
                            $nota_pago = $pago_existente ? trim((string)($pago_existente['observacion'] ?? '')) : '';
                            $pago_registrado = ($pago_existente && $monto_pago > 0);
                            $es_pendiente_marcado = ($pago_existente && $monto_pago == 0 && !empty($pago_existente['fecha_registro']));

                            $puede_registrar = false;
                            if (!$pago_registrado && !$es_pendiente_marcado && !$primer_pendiente_habilitado) {
                                $puede_registrar = true;
                                $primer_pendiente_habilitado = true;
                            }
                            
                            // El saldo en BD representa lo que se debe ANTES de pagar ese día
                            $saldo_antes = $pago_existente ? floatval($pago_existente['saldo']) : max(0, $total - ($i - 1) * $pago_unitario);
                            $monto_esperado_dia = min($pago_unitario, $saldo_antes);
                            $monto_maximo_dia = obtenerMontoMaximoPermitidoDiaTarjeta($tarjeta, $dia_buscar, $saldo_antes);
                            $faltante_dia = max(0, $monto_esperado_dia - $monto_pago);
                            $es_pago_parcial = ($pago_registrado && $faltante_dia > 0.009);
                            if ($pago_registrado) {
                                $saldo_despues = floatval($pago_existente['saldo']);
                            } elseif ($es_pendiente_marcado) {
                                $saldo_despues = $saldo_antes;
                            } else {
                                $saldo_despues = max(0, $saldo_antes - $monto_esperado_dia);
                            }
                            if ($puede_registrar || $es_pendiente_marcado) {
                                $hay_dia_disponible = true;
                            }
                        ?>
                        <tr class="<?php echo $pago_registrado ? 'row-paid' : ''; ?>">
                            <!-- Columna 1: Número de Día -->
                            <td><?php echo htmlspecialchars($etiqueta_periodo); ?></td>
                            <!-- Columna 2: Fecha -->
                            <td>
                                <strong style="color: #28a745;"><?php echo htmlspecialchars($fecha_pago); ?></strong>
                                <?php if ($pago_registrado && $fecha_cobro_real): ?>
                                    <br><small class="text-muted">Cobrado: <?php echo htmlspecialchars($fecha_cobro_real); ?></small>
                                <?php endif; ?>
                            </td>
                            <!-- Columna 3: Pago Realizado -->
                            <td class="<?php echo $pago_registrado ? 'text-success' : ''; ?>">
                                $<?php echo number_format($monto_pago, 2); ?>
                            </td>
                            <!-- Columna 4: Saldo Pendiente -->
                            <td>$<?php echo number_format($saldo_despues, 2); ?></td>
                            <!-- Columna 5: Nota -->
                            <td>
                                <?php if ($nota_pago !== ''): ?>
                                    <span class="text-info"><?php echo htmlspecialchars($nota_pago); ?></span>
                                <?php else: ?>
                                    <span class="text-muted">-</span>
                                <?php endif; ?>
                            </td>
                            <!-- Columna 6: Acción -->
                            <td style="white-space: nowrap;">
                                <?php if ($pago_registrado): ?>
                                    <?php if ($es_pago_parcial): ?>
                                        <span class="badge" style="background-color: #17a2b8; color: #fff;">△ Parcial</span>
                                    <?php else: ?>
                                        <span class="text-muted">✓ Pagado</span>
                                    <?php endif; ?>
                                <?php elseif ($es_pendiente_marcado): ?>
                                    <div style="margin-bottom: 8px;">
                                        <span class="badge" style="background-color: #ffc107; color: #000;">⭕ Pendiente</span>
                                    </div>
                                    <form method="POST" style="display: flex; gap: 5px; align-items: center;">
                                        <input type="hidden" name="dia" value="<?php echo $dia_buscar; ?>">
                                        <div style="display: flex; flex-direction: column; gap: 3px;">
                                            <input 
                                                type="number" 
                                                name="monto" 
                                                step="0.01" 
                                                min="0.01" 
                                                max="<?php echo $monto_maximo_dia; ?>" 
                                                value="<?php echo min($monto_maximo_dia, max($monto_esperado_dia, 0.01)); ?>" 
                                                placeholder="Monto" 
                                                required
                                                style="width: 90px; padding: 4px; border: 1px solid #ddd; border-radius: 4px;"
                                                title="Máximo: $<?php echo number_format($monto_maximo_dia, 2); ?>"
                                            >
                                            <small style="color: #666; font-size: 10px;">Max: $<?php echo number_format($monto_maximo_dia, 2); ?></small>
                                        </div>
                                        <button type="submit" name="registrar_pago" class="btn btn-sm btn-info">
                                            💵 Cobrar
                                        </button>
                                    </form>
                                <?php elseif ($puede_registrar): ?>
                                    <form method="POST" style="display: flex; gap: 5px; align-items: center;">
                                        <input type="hidden" name="dia" value="<?php echo $dia_buscar; ?>">
                                        <div style="display: flex; flex-direction: column; gap: 3px;">
                                            <input 
                                                type="number" 
                                                name="monto" 
                                                step="0.01" 
                                                min="0.01" 
                                                max="<?php echo $monto_maximo_dia; ?>" 
                                                value="<?php echo min($monto_maximo_dia, max($monto_esperado_dia, 0.01)); ?>" 
                                                placeholder="Monto" 
                                                required
                                                style="width: 90px; padding: 4px; border: 1px solid #ddd; border-radius: 4px;"
                                                title="Máximo: $<?php echo number_format($monto_maximo_dia, 2); ?>"
                                            >
                                            <small style="color: #666; font-size: 10px;">Max: $<?php echo number_format($monto_maximo_dia, 2); ?></small>
                                        </div>
                                        <button type="submit" name="registrar_pago" class="btn btn-sm btn-success">
                                            💵 Ingresar Monto
                                        </button>
                                    </form>
                                    <form method="POST" style="display: inline; margin-left: 5px;">
                                        <input type="hidden" name="dia" value="<?php echo $dia_buscar; ?>">
                                        <button type="submit" name="marcar_pendiente" class="btn btn-sm btn-warning">
                                            ⭕ Marcar Pendiente
                                        </button>
                                    </form>
                                <?php else: ?>
                                    <button type="button" class="btn btn-sm btn-secondary" disabled>
                                        Bloqueado
                                    </button>
                                <?php endif; ?>
                            </td>
                        </tr>
                        <?php endfor; ?>
                    </tbody>
                </table>
            </div>

            <?php if (!$hay_dia_disponible && $pendiente > 0.009): ?>
                <div style="margin-top: 16px; display: flex; justify-content: flex-end;">
                    <form method="POST">
                        <button type="submit" name="agregar_dia_extra" class="btn btn-primary">
                            ➕ Agregar un día más
                        </button>
                    </form>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<?php include __DIR__ . '/../layouts/footer.php'; ?>

