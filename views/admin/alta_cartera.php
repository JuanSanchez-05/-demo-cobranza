<?php
$titulo = 'Alta de Cartera - Administrador';
$tipo = $_GET['tipo'] ?? 'semanal';
include __DIR__ . '/../layouts/header.php';
?>

<div class="dashboard-container">
    <div class="page-header">
        <h1>Registro de Nueva Tarjeta - <?php echo ucfirst($tipo); ?></h1>
        <a href="<?php echo BASE_URL; ?>controllers/AdminController.php?action=carteras" class="btn btn-secondary">
            ← Volver
        </a>
    </div>

    <div class="form-container">
        <form method="POST" action="">
            <div class="form-group">
                <label>Cartera</label>
                <select name="cartera_id" required>
                    <option value="">-- Seleccionar --</option>
                    <?php foreach (obtenerTodasLasCarteras() as $c): ?>
                        <option value="<?php echo $c['id']; ?>"><?php echo htmlspecialchars($c['nombre']); ?> (ID <?php echo $c['id']; ?>)</option>
                    <?php endforeach; ?>
                </select>
            </div>
            <?php if ($tipo === 'semanal'): ?>
                <!-- Formulario Cartera Semanal -->
                <h2>Datos del Préstamo</h2>
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
                        <label>Día de Cobro</label>
                        <select name="dia_cobro" required>
                            <option value="Lunes">Lunes</option>
                            <option value="Martes">Martes</option>
                            <option value="Miércoles">Miércoles</option>
                            <option value="Jueves">Jueves</option>
                            <option value="Viernes">Viernes</option>
                            <option value="Sábado">Sábado</option>
                            <option value="Domingo">Domingo</option>
                        </select>
                    </div>
                </div>
                
                <div class="form-row">
                    <div class="form-group">
                        <label>Cantidad de Préstamo</label>
                        <input type="number" id="cantidad_prestamo" name="cantidad_prestamo" step="0.01" required>
                    </div>
                    <div class="form-group">
                        <label>Cargo del Préstamo</label>
                        <input type="number" id="cargo_prestamo" name="cargo_prestamo" step="0.01" required>
                    </div>
                </div>
                
                <div class="form-row">
                    <div class="form-group">
                        <label>Total del Préstamo (Calculado automáticamente)</label>
                        <input type="number" id="total_prestamo" name="total_prestamo" step="0.01" readonly required>
                        <small class="text-muted">Se calcula sumando: Cantidad + Cargo</small>
                    </div>
                    <div class="form-group">
                        <label>Pago Semanal</label>
                        <input type="number" id="pago_semanal" name="pago_semanal" step="0.01" required>
                    </div>
                </div>
                
                <div class="form-row">
                    <div class="form-group">
                        <label>Semanas a Pagar (Calculado automáticamente)</label>
                        <input type="number" id="semanas_pagar" name="semanas_pagar" readonly required>
                        <small class="text-muted">Se calcula dividiendo: Total del Préstamo / Pago Semanal</small>
                    </div>
                    <div class="form-group">
                        <label>Promotor (Trabajador)</label>
                        <select name="promotor_id" required>
                            <?php foreach (obtenerTodosTrabajadores() as $trab): ?>
                                <option value="<?php echo $trab['id']; ?>">
                                    <?php echo htmlspecialchars($trab['nombre']); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                </div>

            <?php elseif ($tipo === 'diaria'): ?>
                <!-- Formulario Cartera Diaria -->
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
                    <div class="form-group">
                        <label>Valor Total</label>
                        <input type="number" name="valor" step="0.01" required>
                    </div>
                </div>
                
            <?php else: ?>
                <!-- Formulario Cartera Nueva -->
                <h2>Datos del Cliente</h2>
                <div class="form-row">
                    <div class="form-group">
                        <label>Fecha</label>
                        <input type="date" name="fecha" required>
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
                
                <div class="form-group">
                    <label>Colonia del Aval</label>
                    <input type="text" name="aval_colonia" required>
                </div>
                
                <h2>Datos del Préstamo</h2>
                <div class="form-row">
                    <div class="form-group">
                        <label>Préstamo</label>
                        <input type="number" name="prestamo" step="0.01" required>
                    </div>
                    <div class="form-group">
                        <label>Cuota del Préstamo</label>
                        <input type="number" name="cuota_prestamo" step="0.01" required>
                    </div>
                </div>
                
                <div class="form-row">
                    <div class="form-group">
                        <label>Total del Préstamo</label>
                        <input type="number" name="total_prestamo" step="0.01" required>
                    </div>
                    <div class="form-group">
                        <label>Pago</label>
                        <input type="number" name="pago" step="0.01" required>
                    </div>
                </div>
                
                <div class="form-row">
                    <div class="form-group">
                        <label>Días a Pagar</label>
                        <input type="number" name="dias_pagar" required>
                    </div>
                    <div class="form-group">
                        <label>Día de Cobro</label>
                        <select name="dia_cobro" required>
                            <option value="Lunes">Lunes</option>
                            <option value="Martes">Martes</option>
                            <option value="Miércoles">Miércoles</option>
                            <option value="Jueves">Jueves</option>
                            <option value="Viernes">Viernes</option>
                            <option value="Sábado">Sábado</option>
                            <option value="Domingo">Domingo</option>
                        </select>
                    </div>
                </div>
                
                <div class="form-row">
                    <div class="form-group">
                        <label>Hora de Cobro</label>
                        <input type="time" name="hora_cobro" required>
                    </div>
                    <div class="form-group">
                        <label>Promotor (Trabajador)</label>
                        <select name="promotor_id" required>
                            <?php foreach (obtenerTodosTrabajadores() as $trab): ?>
                                <option value="<?php echo $trab['id']; ?>">
                                    <?php echo htmlspecialchars($trab['nombre']); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                </div>
            <?php endif; ?>
            
            <div class="form-actions">
                <button type="submit" class="btn btn-success">Guardar Cartera</button>
                <a href="<?php echo BASE_URL; ?>controllers/AdminController.php?action=carteras" class="btn btn-secondary">
                    Cancelar
                </a>
            </div>
        </form>
    </div>
</div>

<script>
// Cálculos automáticos para cartera semanal
<?php if ($tipo === 'semanal'): ?>
document.addEventListener('DOMContentLoaded', function() {
    const cantidadInput = document.getElementById('cantidad_prestamo');
    const cargoInput = document.getElementById('cargo_prestamo');
    const totalInput = document.getElementById('total_prestamo');
    const pagoSemanalInput = document.getElementById('pago_semanal');
    const semanasInput = document.getElementById('semanas_pagar');
    
    function calcularTotal() {
        const cantidad = parseFloat(cantidadInput.value) || 0;
        const cargo = parseFloat(cargoInput.value) || 0;
        const total = cantidad + cargo;
        totalInput.value = total.toFixed(2);
        calcularSemanas();
    }
    
    function calcularSemanas() {
        const total = parseFloat(totalInput.value) || 0;
        const pagoSemanal = parseFloat(pagoSemanalInput.value) || 0;
        
        if (pagoSemanal > 0) {
            const semanas = Math.ceil(total / pagoSemanal);
            semanasInput.value = semanas;
        } else {
            semanasInput.value = '';
        }
    }
    
    cantidadInput.addEventListener('input', calcularTotal);
    cargoInput.addEventListener('input', calcularTotal);
    pagoSemanalInput.addEventListener('input', calcularSemanas);
    totalInput.addEventListener('input', calcularSemanas);
});
<?php endif; ?>
</script>

<?php include __DIR__ . '/../layouts/footer.php'; ?>

