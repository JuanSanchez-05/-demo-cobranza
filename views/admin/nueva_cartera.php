<?php
$titulo = 'Crear Cartera - Administrador';
include __DIR__ . '/../layouts/header.php';

$trabajadores = obtenerTodosTrabajadores();
?>

<div class="dashboard-container">
    <div class="page-header">
        <h1>Nueva Cartera</h1>
        <a href="<?php echo BASE_URL; ?>controllers/AdminController.php?action=carteras" class="btn btn-secondary">
            ← Volver a Carteras
        </a>
    </div>

    <?php if (isset($_GET['error'])): ?>
        <div class="alert alert-danger">
            <?php 
            switch($_GET['error']) {
                case 'datos_invalidos': echo "❌ Datos inválidos"; break;
                default: echo "❌ Error desconocido"; break;
            }
            ?>
        </div>
    <?php endif; ?>

    <div class="form-container">
        <form method="POST" action="<?php echo BASE_URL; ?>controllers/AdminController.php?action=guardar_cartera">
            <div class="form-group">
                <label>Nombre de la Cartera</label>
                <input type="text" name="nombre" required placeholder="Ej: Cartera Centro">
            </div>

            <div class="form-group">
                <label>Descripción</label>
                <textarea name="descripcion" rows="3" placeholder="Descripción opcional de la cartera"></textarea>
            </div>

            <div class="form-group">
                <label>Asignar Trabajador</label>
                <select name="trabajador_id" required>
                    <option value="">-- Seleccionar Trabajador --</option>
                    <?php foreach ($trabajadores as $trabajador): ?>
                        <option value="<?php echo $trabajador['id']; ?>">
                            <?php echo htmlspecialchars($trabajador['nombre']); ?> (<?php echo htmlspecialchars($trabajador['telefono']); ?>)
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div class="form-actions">
                <button type="submit" class="btn btn-success">💾 Crear Cartera</button>
                <a href="<?php echo BASE_URL; ?>controllers/AdminController.php?action=carteras" class="btn btn-secondary">
                    ❌ Cancelar
                </a>
            </div>
        </form>
    </div>
</div>

<?php include __DIR__ . '/../layouts/footer.php'; ?>