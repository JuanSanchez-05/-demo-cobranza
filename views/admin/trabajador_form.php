<?php
$titulo = ($trabajador ? 'Editar Trabajador' : 'Nuevo Trabajador');
include __DIR__ . '/../layouts/header.php';
?>

<div class="dashboard-container">
    <div class="page-header">
        <h1><?php echo $titulo; ?></h1>
        <a href="<?php echo BASE_URL; ?>controllers/AdminController.php?action=trabajadores" class="btn btn-secondary">
            ← Volver
        </a>
    </div>

    <div class="form-container">
        <form method="post" action="<?php echo BASE_URL; ?>controllers/AdminController.php?action=guardar_trabajador">
            <?php if ($trabajador): ?>
                <input type="hidden" name="id" value="<?php echo $trabajador['id']; ?>">
            <?php endif; ?>

            <div class="form-group">
                <label>Nombre</label>
                <input type="text" name="nombre" value="<?php echo $trabajador['nombre'] ?? ''; ?>" required>
            </div>

            <div class="form-group">
                <label>Teléfono</label>
                <input type="text" name="telefono" value="<?php echo $trabajador['telefono'] ?? ''; ?>" required>
            </div>

            <div class="form-group">
                <label>Dirección</label>
                <input type="text" name="direccion" value="<?php echo $trabajador['direccion'] ?? ''; ?>">
            </div>

            <div class="form-group">
                <label>Contraseña (dejar vacío para no cambiar)</label>
                <input type="password" name="password" value="">
            </div>

            <div class="form-actions">
                <button type="submit" class="btn btn-primary">Guardar</button>
                <a href="<?php echo BASE_URL; ?>controllers/AdminController.php?action=trabajadores" class="btn btn-secondary">Cancelar</a>
            </div>
        </form>
    </div>
</div>

<?php include __DIR__ . '/../layouts/footer.php';
