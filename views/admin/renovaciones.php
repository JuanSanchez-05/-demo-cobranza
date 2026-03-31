<?php
$titulo = 'Solicitudes de Renovación - Administrador';
include __DIR__ . '/../layouts/header.php';

$estado_actual = $estado ?? 'pendiente';
?>

<div class="dashboard-container">
    <div class="page-header">
        <h1>Solicitudes de Renovación</h1>
        <div class="page-actions">
            <a href="<?php echo BASE_URL; ?>controllers/AdminController.php?action=dashboard" class="btn btn-secondary">← Volver al Panel</a>
        </div>
    </div>

    <?php if (isset($_GET['mensaje'])): ?>
        <?php if ($_GET['mensaje'] === 'renovacion_aprobada'): ?>
            <div class="alert alert-success">✓ Renovación aprobada: se creó la nueva tarjeta y la anterior quedó completada.</div>
        <?php elseif ($_GET['mensaje'] === 'renovacion_rechazada'): ?>
            <div class="alert alert-warning">⚠ Solicitud de renovación rechazada.</div>
        <?php endif; ?>
    <?php endif; ?>

    <?php if (isset($_GET['error'])): ?>
        <div class="alert alert-danger">✗ No se pudo procesar la solicitud. Código: <?php echo htmlspecialchars($_GET['error']); ?></div>
    <?php endif; ?>

    <div class="card" style="margin-bottom: 16px;">
        <div class="card-body">
            <div class="action-buttons">
                <a class="btn <?php echo $estado_actual === 'pendiente' ? 'btn-primary' : 'btn-secondary'; ?>" href="<?php echo BASE_URL; ?>controllers/AdminController.php?action=renovaciones&estado=pendiente">Pendientes</a>
                <a class="btn <?php echo $estado_actual === 'aprobada' ? 'btn-primary' : 'btn-secondary'; ?>" href="<?php echo BASE_URL; ?>controllers/AdminController.php?action=renovaciones&estado=aprobada">Aprobadas</a>
                <a class="btn <?php echo $estado_actual === 'rechazada' ? 'btn-primary' : 'btn-secondary'; ?>" href="<?php echo BASE_URL; ?>controllers/AdminController.php?action=renovaciones&estado=rechazada">Rechazadas</a>
                <a class="btn <?php echo $estado_actual === 'todas' ? 'btn-primary' : 'btn-secondary'; ?>" href="<?php echo BASE_URL; ?>controllers/AdminController.php?action=renovaciones&estado=todas">Todas</a>
            </div>
        </div>
    </div>

    <div class="table-container">
        <table class="data-table">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Tarjeta Origen</th>
                    <th>Cliente</th>
                    <th>Solicitante</th>
                    <th>Deuda</th>
                    <th>Nuevo Total</th>
                    <th>Neto a Entregar</th>
                    <th>Estado</th>
                    <th>Fecha</th>
                    <th>Acción</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach (($solicitudes_renovacion ?? []) as $solicitud):
                    $tarjeta = obtenerTarjetaPorId($solicitud['tarjeta_origen_id'] ?? 0);
                    $cliente = $tarjeta['nombre'] ?? (($solicitud['datos_nueva']['nombre'] ?? '') ?: 'N/A');
                    $solicitante = obtenerNombreUsuarioPorId($solicitud['solicitante_id'] ?? 0);
                    $estado_item = $solicitud['estado'] ?? 'pendiente';
                    $deuda = floatval($solicitud['deuda_al_solicitar'] ?? 0);
                    $nuevo_total = floatval($solicitud['prestamo_nuevo'] ?? 0);
                    $neto = floatval($solicitud['neto_al_solicitar'] ?? 0);
                ?>
                <tr>
                    <td>#<?php echo intval($solicitud['id'] ?? 0); ?></td>
                    <td>
                        #<?php echo intval($solicitud['tarjeta_origen_id'] ?? 0); ?>
                        <?php if ($tarjeta): ?>
                            <br><a class="btn btn-sm btn-info" href="<?php echo BASE_URL; ?>controllers/AdminController.php?action=detalle_tarjeta&id=<?php echo intval($tarjeta['id']); ?>">Ver origen</a>
                        <?php endif; ?>
                    </td>
                    <td><?php echo htmlspecialchars($cliente); ?></td>
                    <td><?php echo htmlspecialchars($solicitante); ?></td>
                    <td>$<?php echo number_format($deuda, 2); ?></td>
                    <td>$<?php echo number_format($nuevo_total, 2); ?></td>
                    <td>$<?php echo number_format($neto, 2); ?></td>
                    <td>
                        <?php if ($estado_item === 'pendiente'): ?>
                            <span class="badge badge-warning">Pendiente</span>
                        <?php elseif ($estado_item === 'aprobada'): ?>
                            <span class="badge badge-success">Aprobada</span>
                        <?php else: ?>
                            <span class="badge badge-danger">Rechazada</span>
                        <?php endif; ?>
                    </td>
                    <td><?php echo htmlspecialchars($solicitud['fecha_solicitud'] ?? ''); ?></td>
                    <td>
                        <?php if ($estado_item === 'pendiente'): ?>
                            <form method="POST" action="<?php echo BASE_URL; ?>controllers/AdminController.php?action=aprobar_renovacion" style="display:inline-block; margin-bottom: 6px;">
                                <input type="hidden" name="solicitud_id" value="<?php echo intval($solicitud['id']); ?>">
                                <button type="submit" class="btn btn-sm btn-success" onclick="return confirm('¿Aprobar renovación? Esto creará una tarjeta nueva a 21 días y completará la anterior.');">
                                    Aprobar
                                </button>
                            </form>
                            <form method="POST" action="<?php echo BASE_URL; ?>controllers/AdminController.php?action=rechazar_renovacion" style="display:inline-block;">
                                <input type="hidden" name="solicitud_id" value="<?php echo intval($solicitud['id']); ?>">
                                <input type="text" name="motivo_rechazo" placeholder="Motivo (opcional)" style="width: 140px; margin-bottom: 6px;">
                                <button type="submit" class="btn btn-sm btn-danger" onclick="return confirm('¿Rechazar solicitud?');">
                                    Rechazar
                                </button>
                            </form>
                        <?php else: ?>
                            <span class="text-muted">Sin acciones</span>
                        <?php endif; ?>
                    </td>
                </tr>
                <?php endforeach; ?>
                <?php if (empty($solicitudes_renovacion)): ?>
                <tr>
                    <td colspan="10" style="text-align:center;color:#888;">No hay solicitudes en este filtro.</td>
                </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<?php include __DIR__ . '/../layouts/footer.php'; ?>
