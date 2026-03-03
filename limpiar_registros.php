<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

require_once 'config/config.php';

echo "<h2>Limpieza de Registros Inválidos</h2>";

$db = getDB();

// Encontrar tarjetas sin tipo o sin total_prestamo válido
$invalidas = $db->query("
    SELECT id, nombre, tipo, total_prestamo, estado 
    FROM tarjetas 
    WHERE (tipo IS NULL OR tipo = '') 
    OR (total_prestamo = 0 AND estado = 'activo')
    ORDER BY id DESC
")->fetchAll();

echo "<h3>Registros a Limpiar:</h3>";
if (empty($invalidas)) {
    echo "<p>✅ No hay registros inválidos para limpiar</p>";
} else {
    echo "<table border='1'>";
    echo "<tr><th>ID</th><th>Nombre</th><th>Tipo</th><th>Total</th><th>Estado</th><th>Acción</th></tr>";
    
    foreach ($invalidas as $reg) {
        echo "<tr>";
        echo "<td>{$reg['id']}</td>";
        echo "<td>{$reg['nombre']}</td>";
        echo "<td>" . ($reg['tipo'] ?: 'VACÍO') . "</td>";
        echo "<td>" . $reg['total_prestamo'] . "</td>";
        echo "<td>" . $reg['estado'] . "</td>";
        echo "<td>";
        ?>
            <form method='POST' action='' style='display:inline;'>
                <input type='hidden' name='action' value='delete'>
                <input type='hidden' name='id' value='<?php echo $reg['id']; ?>'>
                <button type='submit' style='background:red; color:white; padding:5px 10px; cursor:pointer;'>
                    Eliminar
                </button>
            </form>
        </td>
        <?php
        echo "</tr>";
    }
    echo "</table>";
}

// Procesar eliminaciones
if ($_SERVER['REQUEST_METHOD'] === 'POST' && $_POST['action'] === 'delete') {
    $id = intval($_POST['id']);
    $stmt = $db->prepare("DELETE FROM tarjetas WHERE id = ?");
    if ($stmt->execute([$id])) {
        // También eliminar pagos asociados
        $db->prepare("DELETE FROM pagos WHERE tarjeta_id = ?")->execute([$id]);
        echo "<p style='color:green; background:#efe; padding:10px; margin:10px 0; border-radius:5px;'>✅ Tarjeta $id eliminada correctamente</p>";
        echo "<meta http-equiv='refresh' content='2'>";
    } else {
        echo "<p style='color:red;'>Error al eliminar</p>";
    }
}

echo "<hr>";
echo "<h3>Estado Actual de Tarjetas Válidas:</h3>";

$validas = $db->query("
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

if (empty($validas)) {
    echo "<p>No hay tarjetas válidas</p>";
} else {
    echo "<table border='1'>";
    echo "<tr><th>Tipo</th><th>Cantidad</th><th>Total Prestamado</th></tr>";
    foreach ($validas as $v) {
        $tipo_name = $v['tipo'] === 'antigua_semanal' ? 'Semanal' : 
                    ($v['tipo'] === 'antigua_diaria' ? 'Diaria' : 'Nueva');
        echo "<tr>";
        echo "<td>$tipo_name</td>";
        echo "<td>" . $v['cantidad'] . "</td>";
        echo "<td>$" . number_format($v['total_prestamado'], 2) . "</td>";
        echo "</tr>";
    }
    echo "</table>";
}

?>
