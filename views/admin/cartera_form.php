<?php
$titulo = 'Crear Cartera';
include __DIR__ . '/../layouts/header.php';
?>

<div class="dashboard-container">
    <div class="page-header">
        <h1><?php echo $titulo; ?></h1>
        <a href="<?php echo BASE_URL; ?>controllers/AdminController.php?action=carteras" class="btn btn-secondary">← Volver</a>
    </div>

    <div class="form-container">
            <form method="post" action="<?php echo BASE_URL; ?>controllers/AdminController.php?action=guardar_cartera">
                <?php if (!empty($cartera)): ?>
                    <input type="hidden" name="id" value="<?php echo $cartera['id']; ?>">
                <?php endif; ?>
            <div class="form-group">
                <label>Nombre de la cartera</label>
                <input type="text" name="nombre" value="" required>
            </div>

            <!-- Sólo se pide el nombre de la cartera al crear/editar -->
            <!-- El trabajador asignado se determina automáticamente al vincular tarjetas -->




            <div class="form-actions">
                <button type="submit" class="btn btn-primary">Crear Cartera</button>
                <a href="<?php echo BASE_URL; ?>controllers/AdminController.php?action=carteras" class="btn btn-secondary">Cancelar</a>
            </div>
        </form>
    </div>
</div>

<?php include __DIR__ . '/../layouts/footer.php';
