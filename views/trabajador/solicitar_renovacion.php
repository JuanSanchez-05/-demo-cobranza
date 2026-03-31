<?php
$titulo = 'Solicitar Renovación - Trabajador';
include __DIR__ . '/../layouts/header.php';

$deuda = floatval($deuda_actual ?? 0);
$dia = intval($dia_avance ?? 0);
$tipo = $tarjeta['tipo'] ?? '';

// Determines label and requirement based on card type
if ($tipo === 'nueva') {
    $titulo_renovacion = 'Renovación de Tarjeta Nueva (21 días)';
    $desde_dia = 'Día 15';
    $plazo_nuevo = '21 días (fijo)';
} elseif ($tipo === 'antigua_diaria') {
    $titulo_renovacion = 'Renovación de Tarjeta Diaria (30 días)';
    $desde_dia = 'Día 25';
    $plazo_nuevo = '30 días (fijo)';
} else { // antigua_semanal
    $titulo_renovacion = 'Renovación de Tarjeta Semanal';
    $desde_dia = 'Cualquier día';
    $plazo_nuevo = 'A especificar';
}
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
            <h2><?php echo $titulo_renovacion; ?></h2>
        </div>
        <div class="card-body">
            <div class="info-grid">
                <div><strong>Cliente:</strong> <?php echo htmlspecialchars($tarjeta['nombre'] ?? 'N/A'); ?></div>
                <div><strong>Día actual:</strong> <?php echo $dia; ?></div>
                <div><strong>Desde:</strong> <?php echo $desde_dia; ?></div>
                <div><strong>Nuevo plazo:</strong> <?php echo $plazo_nuevo; ?></div>
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
                    <label>Monto Nuevo</label>
                    <input type="number" name="monto_nuevo" id="monto_nuevo" min="0.01" step="0.01" value="" required>
                    <small class="text-muted">Monto base a prestar (debe ser ≥ deuda actual: $<?php echo number_format($deuda, 2); ?>).</small>
                </div>
                <div class="form-group">
                    <label>Interés</label>
                    <input type="number" name="interes" id="interes" min="0" step="0.01" value="0" required>
                    <small class="text-muted">Interés a cobrar sobre el préstamo.</small>
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
                    <small class="text-muted">Monto nuevo − deuda actual (efectivo que recibirá el cliente).</small>
                </div>
            </div>

            <div class="form-group">
                <label>Monto Total del Nuevo Préstamo</label>
                <input type="number" name="total_prestamo_nuevo" id="total_prestamo_nuevo" min="0.01" step="0.01" value="" required>
                <small class="text-muted" id="desc_total_prestamo">Total que pagará el cliente (predeterminado: monto nuevo + interés; editable).</small>
            </div>

            <?php if ($tipo === 'antigua_semanal'): ?>
            <div class="form-group">
                <label>Plazo de la Nueva Tarjeta (días)</label>
                <input type="number" name="dias_pagar_semanal" id="dias_pagar_semanal" min="1" step="1" value="" required>
                <small class="text-muted">Cantidad de días que tendrá la nueva tarjeta semanal (ej: 49 para 7 semanas).</small>
            </div>
            <?php endif; ?>

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
    const montoNuevoInput    = document.getElementById('monto_nuevo');
    const interesInput       = document.getElementById('interes');
    const netoInput          = document.getElementById('neto_entregar');
    const totalInput         = document.getElementById('total_prestamo_nuevo');

    // Marcar el campo total como editado manualmente cuando el usuario lo toca
    totalInput.addEventListener('input', function() {
        totalInput.dataset.editado = '1';
    });

    function recalcular() {
        const montoNuevo = parseFloat(montoNuevoInput.value) || 0;
        const interes    = parseFloat(interesInput.value) || 0;

        const neto = montoNuevo - deuda;
        netoInput.value = '$' + neto.toFixed(2);
        netoInput.style.color = neto < 0 ? '#dc3545' : '#28a745';

        // Actualizar el total sólo si el usuario no lo ha editado manualmente
        if (!totalInput.dataset.editado) {
            totalInput.value = (montoNuevo + interes).toFixed(2);
        }
    }

    montoNuevoInput.addEventListener('input', recalcular);
    interesInput.addEventListener('input', recalcular);
    recalcular();
});
</script>

<?php include __DIR__ . '/../layouts/footer.php'; ?>
