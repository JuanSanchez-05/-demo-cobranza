<?php
$titulo = 'Solicitar Renovación - Trabajador';
include __DIR__ . '/../layouts/header.php';

$prestamo_sugerido = max(0, floatval($deuda_actual ?? 0));
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
                <div><strong>Neto a entregar:</strong> Nuevo total - deuda actual</div>
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
                    <label>Nuevo Total del Préstamo</label>
                    <input type="number" name="prestamo" id="prestamo" min="0.01" step="0.01" value="<?php echo number_format($prestamo_sugerido, 2, '.', ''); ?>" required>
                    <small class="text-muted">Debe ser igual o mayor a la deuda actual.</small>
                </div>
                <div class="form-group">
                    <label>Deuda Actual</label>
                    <input type="text" value="$<?php echo number_format($deuda, 2); ?>" readonly>
                </div>
            </div>

            <div class="form-group">
                <label>Neto a Entregar (estimado)</label>
                <input type="text" id="neto_entregar" readonly>
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
    const prestamoInput = document.getElementById('prestamo');
    const netoInput = document.getElementById('neto_entregar');

    function recalcularNeto() {
        const prestamo = parseFloat(prestamoInput.value) || 0;
        const neto = prestamo - deuda;
        netoInput.value = '$' + neto.toFixed(2);
        if (neto < 0) {
            netoInput.style.color = '#dc3545';
        } else {
            netoInput.style.color = '#28a745';
        }
    }

    prestamoInput.addEventListener('input', recalcularNeto);
    recalcularNeto();
});
</script>

<?php include __DIR__ . '/../layouts/footer.php'; ?>
