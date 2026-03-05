<?php
$titulo = 'Alta de Tarjeta - Administrador';
$tipo = $_GET['tipo'] ?? 'semanal';
include __DIR__ . '/../layouts/header.php';

// lista de carteras para seleccionar
$carteras = obtenerTodasLasCarteras();

?>

<div class="dashboard-container">
    <div class="page-header">
        <h1>Registro de Nueva Tarjeta - <?php echo ucfirst($tipo); ?></h1>
        <a href="<?php echo BASE_URL; ?>controllers/AdminController.php?action=dashboard" class="btn btn-secondary">
            &larr; Volver
        </a>
    </div>

    <div class="form-container">
        <form method="POST" action="">
            <div class="form-group">
                <label>Cartera</label>
                <select name="cartera_id" id="cartera_select" required>
                    <option value="" data-promotor="">-- Seleccionar --</option>
                    <?php foreach ($carteras as $c):
                    $promName = $c['trabajador_nombre'] ?? '';
                    ?>
                        <option value="<?php echo $c['id']; ?>" data-promotor="<?php echo htmlspecialchars($promName); ?>">
                            <?php echo htmlspecialchars($c['nombre']); ?> (ID <?php echo $c['id']; ?>)<?php echo $promName ? ' - Promotor: '.htmlspecialchars($promName) : ''; ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div class="form-group">
                <label>Promotor actual</label>
                <input type="text" id="current_promotor" readonly>
            </div>

            <?php if ($tipo === 'semanal'): ?>
                <h2>Datos del Cliente</h2>
                <div class="form-row">
                    <div class="form-group">
                        <label>Lugar</label>
                        <input type="text" name="lugar" required>
                    </div>
                    <div class="form-group">
                        <label>Fecha</label>
                        <input type="date" name="fecha" required>
                    </div>
                </div>
                
                <div class="form-row">
                    <div class="form-group">
                        <label>Nombre del Cliente</label>
                        <input type="text" name="nombre" required>
                    </div>
                    <div class="form-group">
                        <label>Teléfono</label>
                        <input type="text" name="telefono" required>
                    </div>
                </div>
                
                <div class="form-row">
                    <div class="form-group">
                        <label>Dirección</label>
                        <input type="text" name="direccion" required>
                    </div>
                    <div class="form-group">
                        <label>Colonia</label>
                        <input type="text" name="colonia" required>
                    </div>
                </div>
                
                <div class="form-group">
                    <label>Día de Cobro</label>
                    <select name="dia_cobro" required>
                        <option value="">-- Seleccionar --</option>
                        <option value="Lunes">Lunes</option>
                        <option value="Martes">Martes</option>
                        <option value="Miércoles">Miércoles</option>
                        <option value="Jueves">Jueves</option>
                        <option value="Viernes">Viernes</option>
                        <option value="Sábado">Sábado</option>
                        <option value="Domingo">Domingo</option>
                    </select>
                </div>

                <h2>Información del Préstamo</h2>
                <div class="form-row">
                    <div class="form-group">
                        <label>Cantidad del Préstamo</label>
                        <input type="number" id="cantidad_prestamo" name="cantidad_prestamo" step="0.01" required>
                    </div>
                    <div class="form-group">
                        <label>Cargo del Préstamo</label>
                        <input type="number" id="cargo_prestamo" name="cargo_prestamo" step="0.01" required>
                    </div>
                </div>
                
                <div class="form-row">
                    <div class="form-group">
                        <label>Total del Préstamo</label>
                        <input type="number" id="total_prestamo" name="total_prestamo" step="0.01" readonly required>
                        <small class="text-muted">Se calcula automáticamente: Cantidad + Cargo</small>
                    </div>
                    <div class="form-group">
                        <label>Semanas a Pagar</label>
                        <input type="number" id="semanas_pagar" name="semanas_pagar" step="1" min="1" required>
                        <small class="text-muted">Ingresa el número de semanas</small>
                    </div>
                </div>
                
                <div class="form-group">
                    <label>Pago Semanal</label>
                    <input type="number" id="pago_semanal" name="pago_semanal" step="0.01" readonly required>
                    <small class="text-muted">Se calcula automáticamente: Total / Semanas a Pagar</small>
                </div>
            <?php elseif ($tipo === 'diaria'): ?>
                <h2>Datos del Préstamo</h2>
                <div class="form-row">
                    <div class="form-group">
                        <label>Nombre</label>
                        <input type="text" name="nombre" required>
                    </div>
                    <div class="form-group">
                        <label>Cuota Diaria</label>
                        <input type="number" name="cuota_diaria" step="0.01" required>
                    </div>
                </div>
                
                <div class="form-row">
                    <div class="form-group">
                        <label>Valor Total</label>
                        <input type="number" name="valor" step="0.01" required>
                    </div>
                    <div class="form-group">
                        <label>Días a Pagar</label>
                        <input type="number" name="dias_pagar" min="1" max="365" required>
                        <small class="text-muted">Número de días para completar el pago</small>
                    </div>
                </div>
                
                <div class="form-row">
                    <div class="form-group">
                        <label>Fecha</label>
                        <input type="date" name="fecha" required>
                    </div>
                    <div class="form-group">
                        <label>Dirección</label>
                        <input type="text" name="direccion" required>
                    </div>
                </div>
                
                <div class="form-row">
                    <div class="form-group">
                        <label>Teléfono</label>
                        <input type="text" name="telefono" required>
                    </div>
                </div>
            <?php elseif ($tipo === 'nueva'): ?>
                <h2>Datos del Cliente (Sistema Moderno)</h2>
                <div class="form-row">
                    <div class="form-group">
                        <label>Nombre del Cliente</label>
                        <input type="text" name="nombre" required>
                    </div>
                    <div class="form-group">
                        <label>Fecha de Inicio</label>
                        <input type="date" name="fecha" required>
                    </div>
                </div>
                
                <div class="form-row">
                    <div class="form-group">
                        <label>Hora de Cobro</label>
                        <input type="time" name="hora_cobro" required>
                        <small class="text-muted">Hora específica para el cobro diario</small>
                    </div>
                    <div class="form-group">
                        <label>Teléfono</label>
                        <input type="text" name="telefono" required>
                    </div>
                </div>
                
                <div class="form-row">
                    <div class="form-group">
                        <label>Dirección</label>
                        <input type="text" name="direccion" required>
                    </div>
                    <div class="form-group">
                        <label>Colonia</label>
                        <input type="text" name="colonia" required>
                    </div>
                </div>
                
                <div class="form-row">
                    <div class="form-group">
                        <label>Lugar de Trabajo</label>
                        <input type="text" name="lugar" required>
                    </div>
                    <div class="form-group">
                        <label>Giro del Negocio</label>
                        <input type="text" name="giro" required>
                    </div>
                </div>
                
                <h2>Datos del Aval</h2>
                <div class="form-row">
                    <div class="form-group">
                        <label>Nombre del Aval</label>
                        <input type="text" name="aval_nombre" required>
                    </div>
                    <div class="form-group">
                        <label>Teléfono del Aval</label>
                        <input type="text" name="aval_telefono" required>
                    </div>
                </div>
                
                <div class="form-row">
                    <div class="form-group">
                        <label>Dirección del Aval</label>
                        <input type="text" name="aval_direccion" required>
                    </div>
                    <div class="form-group">
                        <label>Colonia del Aval</label>
                        <input type="text" name="aval_colonia" required>
                    </div>
                </div>
                
                <h2>Información del Préstamo</h2>
                <div class="form-row">
                    <div class="form-group">
                        <label>Total del Préstamo</label>
                        <input type="number" name="prestamo" step="0.01" required id="prestamo_nuevo">
                        <small class="text-muted">Monto total del préstamo</small>
                    </div>
                    <div class="form-group">
                        <label>Pago</label>
                        <input type="number" name="pago" step="0.01" required id="pago_diario_nuevo">
                        <small class="text-muted">Monto del pago diario</small>
                    </div>
                </div>
                
                <div class="form-group">
                    <label>Días a Pagar</label>
                    <input type="number" name="dias_pagar" min="1" max="365" required id="dias_pagar_nuevo">
                    <small class="text-muted">Número de días para completar el pago (1-365)</small>
                </div>
            <?php else: ?>
                <h2>Datos del Cliente</h2>
                <div class="form-row">
                    <div class="form-group">
                        <label>Fecha de Inicio del Préstamo</label>
                        <input type="date" name="fecha" id="fecha_nueva" max="<?php echo date('Y-m-d'); ?>" required>
                        <small class="text-muted">No puede ser fecha futura</small>
                    </div>
                    <div class="form-group">
                        <label>Lugar</label>
                        <input type="text" name="lugar" required>
                    </div>
                </div>
                
                <div class="form-row">
                    <div class="form-group">
                        <label>Nombre</label>
                        <input type="text" name="nombre" required>
                    </div>
                    <div class="form-group">
                        <label>Teléfono</label>
                        <input type="text" name="telefono" required>
                    </div>
                </div>
                
                <div class="form-group">
                    <label>Dirección</label>
                    <input type="text" name="direccion" required>
                </div>
                
                <div class="form-row">
                    <div class="form-group">
                        <label>Colonia</label>
                        <input type="text" name="colonia" required>
                    </div>
                    <div class="form-group">
                        <label>Giro</label>
                        <input type="text" name="giro" required>
                    </div>
                </div>
                
                <div class="form-group">
                    <label>Dirección de Cobranza</label>
                    <input type="text" name="direccion_cobranza" required>
                </div>
                
                <h2>Datos del Aval</h2>
                <div class="form-row">
                    <div class="form-group">
                        <label>Nombre del Aval</label>
                        <input type="text" name="aval_nombre" required>
                    </div>
                    <div class="form-group">
                        <label>Teléfono del Aval</label>
                        <input type="text" name="aval_telefono" required>
                    </div>
                </div>
                
                <div class="form-group">
                    <label>Dirección del Aval</label>
                    <input type="text" name="aval_direccion" required>
                </div>
            <?php endif; ?>

            <div class="form-actions">
                <button type="submit" class="btn btn-success">Guardar Tarjeta</button>
                <a href="<?php echo BASE_URL; ?>controllers/AdminController.php?action=dashboard" class="btn btn-secondary">
                    Cancelar
                </a>
            </div>
        </form>
    </div>
</div>

<script>
// actualizar promotor cuando cambie la cartera
document.addEventListener('DOMContentLoaded', function() {
    const carteraSelect = document.getElementById('cartera_select');
    const promInput = document.getElementById('current_promotor');
    function updatePromotor() {
        const opt = carteraSelect.options[carteraSelect.selectedIndex];
        promInput.value = opt ? opt.getAttribute('data-promotor') : '';
    }
    carteraSelect.addEventListener('change', updatePromotor);
    updatePromotor();

    // cálculos automáticos para tipo semanal
    <?php if ($tipo === 'semanal'): ?>
    const cantidadInput = document.getElementById('cantidad_prestamo');
    const cargoInput = document.getElementById('cargo_prestamo');
    const totalInput = document.getElementById('total_prestamo');
    const semanasInput = document.getElementById('semanas_pagar');
    const pagoSemanalInput = document.getElementById('pago_semanal');
    
    function calcularTotal() {
        const cantidad = parseFloat(cantidadInput.value) || 0;
        const cargo = parseFloat(cargoInput.value) || 0;
        const total = cantidad + cargo;
        totalInput.value = total.toFixed(2);
        calcularPagoSemanal();
    }
    
    function calcularPagoSemanal() {
        const total = parseFloat(totalInput.value) || 0;
        const semanas = parseInt(semanasInput.value) || 0;
        if (semanas > 0 && total > 0) {
            const pagoSemanal = total / semanas;
            pagoSemanalInput.value = pagoSemanal.toFixed(2);
        } else {
            pagoSemanalInput.value = '';
        }
    }
    
    cantidadInput.addEventListener('input', calcularTotal);
    cargoInput.addEventListener('input', calcularTotal);
    semanasInput.addEventListener('input', calcularPagoSemanal);
    totalInput.addEventListener('input', calcularPagoSemanal);
    <?php elseif ($tipo === 'nueva'): ?>
    // cálculos automáticos para tipo nueva
    const prestamoInput = document.getElementById('prestamo_nuevo');
    const diasPagarInput = document.getElementById('dias_pagar_nuevo');
    const pagoDiarioInput = document.getElementById('pago_diario_nuevo');

    function calcularPagoDiario() {
        const prestamo = parseFloat(prestamoInput.value) || 0;
        const dias = parseInt(diasPagarInput.value) || 0;
        const total = prestamo;
        
        if (dias > 0 && (!pagoDiarioInput.value || parseFloat(pagoDiarioInput.value) <= 0)) {
            const pagoDiario = total / dias;
            pagoDiarioInput.value = pagoDiario.toFixed(2);
        }
    }

    prestamoInput.addEventListener('input', calcularPagoDiario);
    
    diasPagarInput.addEventListener('input', calcularPagoDiario);
    <?php endif; ?>
});
</script>

<?php include __DIR__ . '/../layouts/footer.php';
