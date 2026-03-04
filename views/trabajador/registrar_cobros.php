<?php
$titulo = 'Registrar Cobros - Trabajador';
include __DIR__ . '/../layouts/header.php';
?>

<div class="dashboard-container">
    <div class="page-header">
        <h1>📝 Registrar Cobros del Día</h1>
        <div class="page-actions">
            <a href="<?php echo BASE_URL; ?>controllers/TrabajadorController.php?action=dashboard" class="btn btn-secondary">
                ← Volver al Dashboard
            </a>
        </div>
    </div>

    <?php if (isset($_GET['mensaje'])): ?>
        <div class="alert alert-success"><?php echo htmlspecialchars($_GET['mensaje']); ?></div>
    <?php endif; ?>

    <?php if (isset($_GET['error'])): ?>
        <div class="alert alert-danger">❌ <?php echo htmlspecialchars($_GET['error']); ?></div>
    <?php endif; ?>

    <?php if (empty($cobros_hoy)): ?>
        <div class="alert alert-info">
            <strong>📅 Sin cobros programados para hoy</strong><br>
            No hay pagos programados para el día de hoy en su cartera.
        </div>
    <?php else: ?>
        <div class="card">
            <div class="card-header">
                <h2>💰 Cobros Programados - <?php echo date('d/m/Y'); ?></h2>
                <p>Registre los pagos recibidos hoy:</p>
            </div>
            
            <form method="POST" action="<?php echo BASE_URL; ?>controllers/TrabajadorController.php?action=registrar_cobros" id="formCobros">
                <div class="table-container">
                    <table class="data-table">
                        <thead>
                            <tr>
                                <th>Cliente</th>
                                <th>Dirección</th>
                                <th>Tipo</th>
                                <th>Monto Esperado</th>
                                <th>Cobrado</th>
                                <th>Estado de Visita</th>
                                <th>Monto a Registrar</th>
                                <th>Acción Rápida</th>
                                <th>Registrar</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($cobros_hoy as $index => $cobro): 
                                $monto_esperado = 0;
                                switch($cobro['tipo']) {
                                    case 'antigua_semanal':
                                        $monto_esperado = $cobro['pago_semanal'] ?? 0;
                                        break;
                                    case 'antigua_diaria':
                                        $monto_esperado = $cobro['cuota_diaria'] ?? 0;
                                        break;
                                    default:
                                        $monto_esperado = $cobro['pago_nuevo'] ?? 0;
                                        break;
                                }
                                $ya_cobrado = $cobro['ya_cobrado_monto'] ?? 0;
                                $estado_visita = $cobro['estado_visita'] ?? ($ya_cobrado > 0 ? 'cobrado' : 'programado');
                            ?>
                            <tr>
                                <td>
                                    <strong><?php echo htmlspecialchars($cobro['nombre']); ?></strong>
                                </td>
                                <td>
                                    <small><?php echo htmlspecialchars($cobro['direccion']); ?></small>
                                </td>
                                <td>
                                    <span class="badge badge-<?php 
                                        echo $cobro['tipo'] === 'antigua_semanal' ? 'info' : 
                                            ($cobro['tipo'] === 'antigua_diaria' ? 'warning' : 'success'); 
                                    ?>">
                                        <?php 
                                        echo $cobro['tipo'] === 'antigua_semanal' ? 'Semanal' : 
                                            ($cobro['tipo'] === 'antigua_diaria' ? 'Diaria' : 'Nueva'); 
                                        ?>
                                    </span>
                                </td>
                                <td>$<?php echo number_format($monto_esperado, 2); ?></td>
                                <td class="<?php echo $ya_cobrado > 0 ? 'text-success' : 'text-muted'; ?>">
                                    $<?php echo number_format($ya_cobrado, 2); ?>
                                </td>
                                <td>
                                    <select name="pagos[<?php echo $index; ?>][estado]"
                                            class="form-control form-control-sm estado-cobro"
                                            data-index="<?php echo $index; ?>"
                                            <?php echo $ya_cobrado > 0 ? 'disabled' : ''; ?>>
                                        <option value="cobrado" <?php echo $estado_visita === 'cobrado' ? 'selected' : ''; ?>>✅ Cobrado</option>
                                        <option value="cobrado" <?php echo $estado_visita === 'pagado_retraso' ? 'selected' : ''; ?>>✅ Cobrado</option>
                                        <option value="pendiente" <?php echo $estado_visita === 'pendiente' ? 'selected' : ''; ?>>⭕ Pendiente (no pagó / no estaba)</option>
                                    </select>
                                </td>
                                <td>
                                    <input type="number" 
                                           name="pagos[<?php echo $index; ?>][monto]" 
                                           step="0.01" 
                                           min="0" 
                                           max="<?php echo $monto_esperado; ?>"
                                         value="<?php echo $ya_cobrado > 0 ? '' : ($estado_visita === 'pendiente' ? '0' : $monto_esperado); ?>"
                                         <?php echo $estado_visita === 'pendiente' ? 'readonly' : ''; ?>
                                           class="form-control form-control-sm">
                                    <input type="hidden" name="pagos[<?php echo $index; ?>][tarjeta_id]" value="<?php echo $cobro['id']; ?>">
                                    <input type="hidden" name="pagos[<?php echo $index; ?>][dia]" value="<?php echo $cobro['dia']; ?>">
                                </td>
                                <td>
                                    <?php if ($ya_cobrado > 0): ?>
                                        <button type="button" class="btn btn-sm btn-secondary" disabled>Pagado</button>
                                    <?php else: ?>
                                        <button type="button" class="btn btn-sm btn-warning" onclick="marcarFilaPendiente(<?php echo $index; ?>)">
                                            Marcar pendiente
                                        </button>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <label>
                                        <input type="checkbox" 
                                               class="cobro-checkbox" 
                                               <?php echo $ya_cobrado > 0 ? 'checked disabled' : ''; ?>>
                                        <?php
                                        if ($estado_visita === 'cobrado') {
                                            echo '✅ Cobrado';
                                        } elseif ($estado_visita === 'pagado_retraso') {
                                            echo '✅ Pagado con retraso';
                                        } elseif ($estado_visita === 'pendiente') {
                                            echo '🕒 Pendiente';
                                        } else {
                                            echo '📌 Programado';
                                        }
                                        ?>
                                    </label>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>

                <div class="form-actions">
                    <button type="submit" class="btn btn-success" id="btnGuardar">
                        💾 Guardar Cobros Seleccionados
                    </button>
                    <button type="button" class="btn btn-info" onclick="seleccionarTodos()">
                        ☑️ Seleccionar Todos
                    </button>
                    <button type="button" class="btn btn-warning" onclick="limpiarSeleccion()">
                        🔄 Limpiar Selección
                    </button>
                </div>
            </form>
        </div>
    <?php endif; ?>

    <!-- Resumen del día -->
    <div class="card">
        <div class="card-header">
            <h2>📊 Resumen del Día</h2>
        </div>
        <div class="card-body">
            <?php 
            $total_esperado = 0;
            $total_cobrado = 0;
            $pendientes = 0;
            $pendientes_marcados = 0;
            
            foreach ($cobros_hoy as $cobro) {
                switch($cobro['tipo']) {
                    case 'antigua_semanal':
                        $esperado = $cobro['pago_semanal'] ?? 0;
                        break;
                    case 'antigua_diaria':
                        $esperado = $cobro['cuota_diaria'] ?? 0;
                        break;
                    default:
                        $esperado = $cobro['pago_nuevo'] ?? 0;
                        break;
                }
                
                $total_esperado += $esperado;
                $cobrado = $cobro['ya_cobrado_monto'] ?? 0;
                $total_cobrado += $cobrado;
                $estado_visita = $cobro['estado_visita'] ?? ($cobrado > 0 ? 'cobrado' : 'programado');
                
                if ($cobrado == 0) {
                    $pendientes++;
                }

                if ($estado_visita === 'pendiente') {
                    $pendientes_marcados++;
                }
            }
            
            $porcentaje = $total_esperado > 0 ? ($total_cobrado / $total_esperado) * 100 : 0;
            ?>
            
            <div class="stats-grid">
                <div class="stat-card">
                    <h3>Total Esperado</h3>
                    <p class="stat-value">$<?php echo number_format($total_esperado, 2); ?></p>
                </div>
                
                <div class="stat-card">
                    <h3>Total Cobrado</h3>
                    <p class="stat-value text-success">$<?php echo number_format($total_cobrado, 2); ?></p>
                </div>
                
                <div class="stat-card">
                    <h3>Clientes Pendientes</h3>
                    <p class="stat-value text-warning"><?php echo $pendientes; ?></p>
                </div>

                <div class="stat-card">
                    <h3>Pendientes Marcados</h3>
                    <p class="stat-value text-info"><?php echo $pendientes_marcados; ?></p>
                </div>
                
                <div class="stat-card">
                    <h3>Progreso del Día</h3>
                    <div class="progress-bar">
                        <div class="progress-fill" style="width: <?php echo $porcentaje; ?>%"></div>
                        <span class="progress-text"><?php echo number_format($porcentaje, 1); ?>%</span>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
function seleccionarTodos() {
    const checkboxes = document.querySelectorAll('.cobro-checkbox:not(:disabled)');
    checkboxes.forEach(cb => {
        cb.checked = true;
    });
}

function limpiarSeleccion() {
    const checkboxes = document.querySelectorAll('.cobro-checkbox:not(:disabled)');
    checkboxes.forEach(cb => {
        cb.checked = false;
    });
}

function actualizarMontoPorEstado(index, estado) {
    const montoInput = document.querySelector(`input[name="pagos[${index}][monto]"]`);
    if (!montoInput) return;

    if (estado === 'pendiente') {
        montoInput.value = '0';
        montoInput.setAttribute('readonly', 'readonly');
    } else {
        montoInput.removeAttribute('readonly');
    }
}

function marcarFilaPendiente(index) {
    const estadoSelect = document.querySelector(`select[name="pagos[${index}][estado]"]`);
    const checkbox = document.querySelectorAll('.cobro-checkbox')[index];

    if (estadoSelect && !estadoSelect.disabled) {
        estadoSelect.value = 'pendiente';
        actualizarMontoPorEstado(index, 'pendiente');
    }

    if (checkbox && !checkbox.disabled) {
        checkbox.checked = true;
    }
}

document.querySelectorAll('.estado-cobro').forEach(select => {
    select.addEventListener('change', function() {
        const index = this.dataset.index;
        actualizarMontoPorEstado(index, this.value);
    });
});

// Validar formulario antes de enviar
document.getElementById('formCobros').addEventListener('submit', function(e) {
    const checkboxes = document.querySelectorAll('.cobro-checkbox:checked:not(:disabled)');
    if (checkboxes.length === 0) {
        e.preventDefault();
        alert('Por favor seleccione al menos un cobro para registrar.');
        return false;
    }
    
    // Deshabilitar inputs no seleccionados
    const allCheckboxes = document.querySelectorAll('.cobro-checkbox');
    allCheckboxes.forEach((cb, index) => {
        if (!cb.checked) {
            const inputs = document.querySelectorAll(`input[name^="pagos[${index}]"]`);
            inputs.forEach(input => {
                input.disabled = true;
            });
        }
    });
});
</script>

<?php include __DIR__ . '/../layouts/footer.php'; ?>