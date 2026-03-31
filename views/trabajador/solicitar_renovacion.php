<?php
$titulo = 'Solicitar Renovación - Trabajador';
include __DIR__ . '/../layouts/header.php';

$monto_nuevo_sugerido = max(0, floatval($deuda_actual ?? 0));
$deuda = floatval($deuda_actual ?? 0);
$dia = intval($dia_avance ?? 0);
?>

<div class="dashboard-container">
    <div class="page-header">
        <h1>Solicitar Renovación de Tarjeta #<?php echo intval($tarjeta['id']); ?></h1>
        <a href="<?php echo BASE_URL; ?>controllers/TrabajadorController.php?action=detalle_tarjeta&id=<?php echo intval($tarjeta['id']); ?>" class="btn btn-secondary">
            ← Volver al Detalle
        </a>
    </div>

    <?php if (isset($_GET['error'])): ?>
        <div class="alert alert-danger">✗ No se pudo registrar la solicitud. Código: <?php echo htmlspecialchars($_GET['error']); ?></div>
    <?php endif; ?>

    <div class="card" style="margin-bottom: 20px;">
        <div class="card-header">
            <h2>Resumen de Renovación</h2>
        </div>
        <div class="card-body">
            <div class="info-grid">
                <div><strong>Cliente:</strong> <?php echo htmlspecialchars($tarjeta['nombre'] ?? 'N/A'); ?></div>
                <div><strong>Día actual:</strong> <?php echo $dia; ?></div>
                <div><strong>Regla:</strong> Solo se puede renovar desde el día 15</div>
                <div><strong>Nuevo plazo:</strong> 21 días (fijo)</div>
                <div><strong>Deuda actual:</strong> $<?php echo number_format($deuda, 2); ?></div>
                <div><strong>Cálculo final:</strong> (Monto nuevo + interés) - deuda actual</div>
            </div>
        </div>
    </div>

    <div class="form-container">
        <form method="POST" action="">
            <h2>Datos de la Nueva Tarjeta</h2>
            <div class="form-row">
                <div class="form-group">
                    <label>Nombre del Cliente</label>
                    <input type="text" name="nombre" value="<?php echo htmlspecialchars($tarjeta['nombre'] ?? ''); ?>" required>
                </div>
                <div class="form-group">
                    <label>Fecha de Inicio</label>
                    <input type="date" name="fecha" value="<?php echo date('Y-m-d'); ?>" required>
                </div>
            </div>

            <div class="form-row">
                <div class="form-group">
                    <label>Hora de Cobro</label>
                    <input type="time" name="hora_cobro" value="<?php echo htmlspecialchars($tarjeta['hora_cobro'] ?? ''); ?>" required>
                </div>
                <div class="form-group">
                    <label>Teléfono</label>
                    <input type="text" name="telefono" value="<?php echo htmlspecialchars($tarjeta['telefono'] ?? ''); ?>" required>
                </div>
            </div>

            <div class="form-row">
                <div class="form-group">
                    <label>Dirección</label>
                    <input type="text" name="direccion" value="<?php echo htmlspecialchars($tarjeta['direccion'] ?? ''); ?>" required>
                </div>
                <div class="form-group">
                    <label>Colonia</label>
                    <input type="text" name="colonia" value="<?php echo htmlspecialchars($tarjeta['colonia'] ?? ''); ?>" required>
                </div>
            </div>

            <div class="form-row">
                <div class="form-group">
                    <label>Lugar de Trabajo</label>
                    <input type="text" name="lugar" value="<?php echo htmlspecialchars($tarjeta['lugar'] ?? ''); ?>" required>
                </div>
                <div class="form-group">
                    <label>Giro del Negocio</label>
                    <input type="text" name="giro" value="<?php echo htmlspecialchars($tarjeta['giro'] ?? ''); ?>" required>
                </div>
            </div>

            <div class="form-group">
                <label>Dirección de Cobranza</label>
                <input type="text" name="direccion_cobranza" value="<?php echo htmlspecialchars($tarjeta['direccion_cobranza'] ?? ''); ?>" required>
            </div>

            <h2>Datos del Aval</h2>
            <div class="form-row">
                <div class="form-group">
                    <label>Nombre del Aval</label>
                    <input type="text" name="aval_nombre" value="<?php echo htmlspecialchars($tarjeta['aval_nombre'] ?? ''); ?>" required>
                </div>
                <div class="form-group">
                    <label>Teléfono del Aval</label>
                    <input type="text" name="aval_telefono" value="<?php echo htmlspecialchars($tarjeta['aval_telefono'] ?? ''); ?>" required>
                </div>
            </div>

            <div class="form-row">
                <div class="form-group">
                    <label>Dirección del Aval</label>
                    <input type="text" name="aval_direccion" value="<?php echo htmlspecialchars($tarjeta['aval_direccion'] ?? ''); ?>" required>
                </div>
                <div class="form-group">
                    <label>Colonia del Aval</label>
                    <input type="text" name="aval_colonia" value="<?php echo htmlspecialchars($tarjeta['aval_colonia'] ?? ''); ?>" required>
                </div>
            </div>

            <h2>Monto de Renovación</h2>
            <div class="form-row">
                <div class="form-group">
                    <label>Monto Nuevo</label>
                    <input type="number" name="monto_nuevo" id="monto_nuevo" min="0.01" step="0.01" value="<?php echo number_format($monto_nuevo_sugerido, 2, '.', ''); ?>" required>
                    <small class="text-muted">Ejemplo: 10000</small>
                </div>
                <div class="form-group">
                    <label>Interés</label>
                    <input type="number" name="interes" id="interes" min="0" step="0.01" value="0.00" required>
                    <small class="text-muted">Ejemplo: 1200</small>
                </div>
            </div>

            <div class="form-row">
                <div class="form-group">
                    <label>Deuda Actual</label>
                    <input type="text" value="$<?php echo number_format($deuda, 2); ?>" readonly>
                </div>
                <div class="form-group">
                    <label>Monto Neto a Entregar</label>
                    <input type="text" id="neto_entregar" readonly>
                    <small class="text-muted">Monto nuevo - deuda actual</small>
                </div>
            </div>

            <div class="form-group">
                <label>Monto Total del Nuevo Préstamo</label>
                <input type="text" id="total_nuevo_prestamo" readonly>
                <small class="text-muted">(Monto nuevo + interés) - deuda actual. Este monto se usará en la nueva tarjeta.</small>
            </div>

            <div class="form-actions">
                <button type="submit" class="btn btn-success">Enviar Solicitud a Admin</button>
                <a href="<?php echo BASE_URL; ?>controllers/TrabajadorController.php?action=detalle_tarjeta&id=<?php echo intval($tarjeta['id']); ?>" class="btn btn-secondary">Cancelar</a>
            </div>
        </form>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const deuda = <?php echo json_encode(round($deuda, 2)); ?>;
    const montoNuevoInput = document.getElementById('monto_nuevo');
    const interesInput = document.getElementById('interes');
    const netoInput = document.getElementById('neto_entregar');
    const totalInput = document.getElementById('total_nuevo_prestamo');

    function recalcularMontos() {
        const montoNuevo = parseFloat(montoNuevoInput.value) || 0;
        const interes = parseFloat(interesInput.value) || 0;
        const neto = montoNuevo - deuda;
        const totalNuevoPrestamo = (montoNuevo + interes) - deuda;

        netoInput.value = '$' + neto.toFixed(2);
        totalInput.value = '$' + totalNuevoPrestamo.toFixed(2);

        if (neto < 0) {
            netoInput.style.color = '#dc3545';
        } else {
            netoInput.style.color = '#28a745';
        }

        if (totalNuevoPrestamo <= 0) {
            totalInput.style.color = '#dc3545';
        } else {
            totalInput.style.color = '#28a745';
        }
    }

    montoNuevoInput.addEventListener('input', recalcularMontos);
    interesInput.addEventListener('input', recalcularMontos);
    recalcularMontos();
});
</script>

<?php include __DIR__ . '/../layouts/footer.php'; ?>
