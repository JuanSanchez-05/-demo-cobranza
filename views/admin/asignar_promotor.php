<?php
$titulo = 'Asignar Promotor - Administrador';
include __DIR__ . '/../layouts/header.php';

// recibimos el id de la cartera directamente
$cartera_id = $_GET['id'] ?? 0;
$cartera = obtenerCarteraPorId($cartera_id);
if (!$cartera) {
    echo '<div class="alert alert-danger">Cartera no encontrada</div>';
    include __DIR__ . '/../layouts/footer.php';
    exit;
}

// obtener lista de trabajadores
$trabajadores = obtenerTrabajadoresSimulados();
$current = $cartera['trabajador_id'] ?? null;
?>


<div class="dashboard-container">
    <div class="page-header">
        <h1>Asignar Promotor a Cartera #<?php echo $cartera['id']; ?></h1>
        <a href="<?php echo BASE_URL; ?>controllers/AdminController.php?action=carteras" class="btn btn-secondary">← Volver</a>
    </div>

    <div class="form-container">
        <form method="post" action="<?php echo BASE_URL; ?>controllers/AdminController.php?action=asignar_promotor&id=<?php echo $cartera['id']; ?>">
            <div class="form-group">
                <label>Promotor actual</label>
                <input type="text" value="<?php
                    if ($current) {
                        foreach ($trabajadores as $t) {
                            if ($t['id'] == $current) echo htmlspecialchars($t['nombre']);
                        }
                    }
                ?>" readonly>
            </div>
            <div class="form-group">
                <label>Nuevo Promotor</label>
                <select name="trabajador_id" required>
                    <option value="">-- Seleccionar --</option>
                    <?php foreach ($trabajadores as $t): ?>
                        <option value="<?php echo $t['id']; ?>"><?php echo htmlspecialchars($t['nombre']); ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="form-actions">
                <button class="btn btn-primary">Guardar</button>
            </div>
        </form>
    </div>
</div>

<?php include __DIR__ . '/../layouts/footer.php';
