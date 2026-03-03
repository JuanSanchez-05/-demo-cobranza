<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

require_once 'config/config.php';

$db = getDB();

// Eliminar la tarjeta 17
$id = 17;
$stmt = $db->prepare("DELETE FROM tarjetas WHERE id = ?");
$result = $stmt->execute([$id]);

if ($result) {
    // También eliminar pagos asociados
    $db->prepare("DELETE FROM pagos WHERE tarjeta_id = ?")->execute([$id]);
    echo "<h2>✅ Registro ID $id Eliminado</h2>";
    
    // Mostrar estado final
    $final = $db->query("
        SELECT 
            tipo,
            COUNT(*) as cantidad,
            SUM(total_prestamo) as total_prestamado
        FROM tarjetas 
        WHERE estado = 'activo' 
        AND (tipo IS NOT NULL AND tipo != '')
        AND total_prestamo > 0
        GROUP BY tipo
    ")->fetchAll();
    
    echo "<h3>Estado Final de Tarjetas Válidas:</h3>";
    echo "<table border='1'>";
    echo "<tr><th>Tipo</th><th>Cantidad</th><th>Total Prestamado</th></tr>";
    $total_general = 0;
    foreach ($final as $v) {
        $tipo_name = $v['tipo'] === 'antigua_semanal' ? 'Semanal' : 
                    ($v['tipo'] === 'antigua_diaria' ? 'Diaria' : 'Nueva');
        $total = floatval($v['total_prestamado']);
        $total_general += $total;
        echo "<tr>";
        echo "<td>$tipo_name</td>";
        echo "<td>" . $v['cantidad'] . "</td>";
        echo "<td>$" . number_format($total, 2) . "</td>";
        echo "</tr>";
    }
    echo "<tr style='background:#eee; font-weight:bold;'>";
    echo "<td colspan='2'>TOTAL GENERAL</td>";
    echo "<td>$" . number_format($total_general, 2) . "</td>";
    echo "</tr>";
    echo "</table>";
    
} else {
    echo "<h2>❌ Error al eliminar</h2>";
}

?>
