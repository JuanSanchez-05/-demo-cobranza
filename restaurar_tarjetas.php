<?php
require_once 'config/config.php';

echo "=== RESTAURANDO TARJETAS DE DIFERENTES TIPOS ===\n";

try {
    $db = new Database();
    $conn = $db->getConnection();
    
    // Primero crear una cartera de ejemplo si no existe
    $stmt = $conn->prepare("SELECT id FROM carteras LIMIT 1");
    $stmt->execute();
    $cartera_existente = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$cartera_existente) {
        echo "Creando cartera de ejemplo...\n";
        $data_cartera = [
            'nombre' => 'Cartera Mixta - Tipos de Tarjetas',
            'trabajador_id' => 1, // Asumiendo que existe trabajador ID 1
            'fecha_inicio' => date('Y-m-d'),
            'activa' => true
        ];
        
        $cartera_id = agregarCartera($data_cartera);
        if (!$cartera_id) {
            die("Error: No se pudo crear la cartera\n");
        }
        echo "✓ Cartera creada con ID: $cartera_id\n";
    } else {
        $cartera_id = $cartera_existente['id'];
        echo "✓ Usando cartera existente ID: $cartera_id\n";
    }
    
    echo "\n--- CREANDO TARJETAS TIPO SEMANAL (ANTIGUA) ---\n";
    
    // 1. Tarjeta Semanal Antigua
    $tarjeta_semanal = [
        'tipo' => 'antigua_semanal',
        'nombre' => 'María González',
        'direccion' => 'Av. Juárez #123',
        'colonia' => 'Centro',
        'telefono' => '5551234567',
        'lugar' => 'Mercado Municipal',
        'cantidad_prestamo' => 5000,
        'cargo_prestamo' => 1500,
        'total_prestamo' => 6500,
        'pago_semanal' => 650,      // Pago cada semana
        'semanas_pagar' => 10,      // 10 semanas
        'dia_cobro' => 'Viernes',   // Día específico
        'giro' => 'Venta de frutas',
        'aval_nombre' => 'Pedro González',
        'aval_direccion' => 'Calle 5 de Mayo #45',
        'aval_colonia' => 'Centro',
        'aval_telefono' => '5559876543'
    ];
    
    $result = agregarTarjeta($tarjeta_semanal, $cartera_id);
    if ($result) {
        echo "✓ Tarjeta SEMANAL creada: {$tarjeta_semanal['nombre']} - \$650/semana x 10 semanas\n";
    }
    
    // Otra tarjeta semanal con datos diferentes
    $tarjeta_semanal_2 = [
        'tipo' => 'antigua_semanal',
        'nombre' => 'Carlos Ramírez',
        'direccion' => 'Calle Morelos #456',
        'colonia' => 'San Juan',
        'telefono' => '5554567890',
        'lugar' => 'Taller de carpintería',
        'cantidad_prestamo' => 8000,
        'cargo_prestamo' => 2400,
        'total_prestamo' => 10400,
        'pago_semanal' => 1040,     // Pago cada semana
        'semanas_pagar' => 10,      // 10 semanas
        'dia_cobro' => 'Lunes',     // Día específico
        'giro' => 'Carpintería',
        'aval_nombre' => 'Ana Ramírez',
        'aval_direccion' => 'Av. Insurgentes #78',
        'aval_colonia' => 'San Juan',
        'aval_telefono' => '5551112233'
    ];
    
    $result = agregarTarjeta($tarjeta_semanal_2, $cartera_id);
    if ($result) {
        echo "✓ Tarjeta SEMANAL creada: {$tarjeta_semanal_2['nombre']} - \$1,040/semana x 10 semanas\n";
    }
    
    echo "\n--- CREANDO TARJETAS TIPO DIARIO (ANTIGUA) ---\n";
    
    // 2. Tarjeta Diaria Antigua
    $tarjeta_diaria = [
        'tipo' => 'antigua_diaria',
        'nombre' => 'Rosa Martínez',
        'direccion' => 'Calle Hidalgo #789',
        'colonia' => 'La Esperanza',
        'telefono' => '5557890123',
        'lugar' => 'Puesto de tacos',
        'cantidad_prestamo' => 3000,
        'cargo_prestamo' => 900,
        'total_prestamo' => 3900,
        'cuota_diaria' => 195,      // Pago diario
        'dias_pagar' => 20,         // 20 días hábiles
        'giro' => 'Venta de comida',
        'aval_nombre' => 'Luis Martínez',
        'aval_direccion' => 'Calle Zaragoza #321',
        'aval_colonia' => 'La Esperanza',
        'aval_telefono' => '5556547890'
    ];
    
    $result = agregarTarjeta($tarjeta_diaria, $cartera_id);
    if ($result) {
        echo "✓ Tarjeta DIARIA (antigua) creada: {$tarjeta_diaria['nombre']} - \$195/día x 20 días\n";
    }
    
    // Otra tarjeta diaria antigua
    $tarjeta_diaria_2 = [
        'tipo' => 'antigua_diaria',
        'nombre' => 'Fernando López',
        'direccion' => 'Av. Revolución #159',
        'colonia' => 'El Progreso',
        'telefono' => '5552468135',
        'lugar' => 'Tienda de abarrotes',
        'cantidad_prestamo' => 2500,
        'cargo_prestamo' => 750,
        'total_prestamo' => 3250,
        'cuota_diaria' => 162.50,   // Pago diario
        'dias_pagar' => 20,         // 20 días hábiles
        'giro' => 'Abarrotes',
        'aval_nombre' => 'Carmen López',
        'aval_direccion' => 'Calle Allende #753',
        'aval_colonia' => 'El Progreso',
        'aval_telefono' => '5558642097'
    ];
    
    $result = agregarTarjeta($tarjeta_diaria_2, $cartera_id);
    if ($result) {
        echo "✓ Tarjeta DIARIA (antigua) creada: {$tarjeta_diaria_2['nombre']} - \$162.50/día x 20 días\n";
    }
    
    echo "\n--- CREANDO TARJETAS TIPO NUEVA (DIARIA MODERNA) ---\n";
    
    // 3. Tarjeta Nueva (sistema moderno)
    $tarjeta_nueva = [
        'tipo' => 'nueva',
        'nombre' => 'Patricia Vega',
        'direccion' => 'Calle Reforma #258',
        'colonia' => 'Moderna',
        'telefono' => '5553691470',
        'lugar' => 'Salón de belleza',
        'prestamo' => 4500,          // Monto del préstamo (nuevo sistema)
        'cuota_prestamo' => 1350,    // Cargo (30% del préstamo)
        'pago' => 195,               // Pago diario (nuevo sistema)
        'hora_cobro' => '14:00',     // Hora específica de cobro
        'giro' => 'Servicios de belleza',
        'aval_nombre' => 'Roberto Vega',
        'aval_direccion' => 'Av. Libertad #963',
        'aval_colonia' => 'Moderna',
        'aval_telefono' => '5557412569'
    ];
    
    $result = agregarTarjeta($tarjeta_nueva, $cartera_id);
    if ($result) {
        echo "✓ Tarjeta NUEVA (diaria moderna) creada: {$tarjeta_nueva['nombre']} - \$195/día (hora: 14:00)\n";
        
        // Crear pagos programados para la tarjeta nueva (30 días)
        crearPagosProgramados($conn->lastInsertId(), [
            'monto_pago' => 195,
            'total_dias' => 30,
            'fecha_inicio' => date('Y-m-d')
        ]);
        echo "  → Pagos programados creados (30 días)\n";
    }
    
    // Otra tarjeta nueva
    $tarjeta_nueva_2 = [
        'tipo' => 'nueva',
        'nombre' => 'Miguel Santos',
        'direccion' => 'Av. Tecnológico #741',
        'colonia' => 'Industrial',
        'telefono' => '5559513570',
        'lugar' => 'Taller mecánico',
        'prestamo' => 7000,          // Monto del préstamo
        'cuota_prestamo' => 2100,    // Cargo (30% del préstamo)  
        'pago' => 280,               // Pago diario
        'hora_cobro' => '16:30',     // Hora específica de cobro
        'giro' => 'Reparación de autos',
        'aval_nombre' => 'Teresa Santos',
        'aval_direccion' => 'Calle Industrial #852',
        'aval_colonia' => 'Industrial',
        'aval_telefono' => '5556429581'
    ];
    
    $result = agregarTarjeta($tarjeta_nueva_2, $cartera_id);
    if ($result) {
        echo "✓ Tarjeta NUEVA (diaria moderna) creada: {$tarjeta_nueva_2['nombre']} - \$280/día (hora: 16:30)\n";
        
        // Crear pagos programados para la tarjeta nueva (30 días)
        crearPagosProgramados($conn->lastInsertId(), [
            'monto_pago' => 280,
            'total_dias' => 30,
            'fecha_inicio' => date('Y-m-d')
        ]);
        echo "  → Pagos programados creados (30 días)\n";
    }
    
    echo "\n=== RESUMEN DE TARJETAS CREADAS ===\n";
    
    $stmt = $conn->query("
        SELECT tipo, COUNT(*) as cantidad,
               CASE 
                   WHEN tipo = 'antigua_semanal' THEN 'Semanales (Sistema Antiguo)'
                   WHEN tipo = 'antigua_diaria' THEN 'Diarias (Sistema Antiguo)' 
                   WHEN tipo = 'nueva' THEN 'Nuevas (Sistema Moderno)'
                   ELSE tipo
               END as descripcion
        FROM tarjetas 
        GROUP BY tipo, descripcion
        ORDER BY tipo
    ");
    
    while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
        echo "• {$row['descripcion']}: {$row['cantidad']} tarjeta(s)\n";
    }
    
    echo "\n=== DIFERENCIAS ENTRE TIPOS ===\n";
    echo "📅 SEMANALES (antigua_semanal):\n";
    echo "   - Cobro cada semana en día específico\n";
    echo "   - Campos: pago_semanal, semanas_pagar, dia_cobro\n";
    echo "   - Ejemplo: \$650 cada viernes por 10 semanas\n\n";
    
    echo "📆 DIARIAS ANTIGUAS (antigua_diaria):\n";
    echo "   - Cobro diario, solo monto\n";
    echo "   - Campos: cuota_diaria, dias_pagar\n";
    echo "   - Ejemplo: \$195 cada día por 20 días\n\n";
    
    echo "🆕 NUEVAS (nueva):\n";
    echo "   - Cobro diario con hora específica\n";
    echo "   - Campos: prestamo, cuota_prestamo, pago, hora_cobro\n";
    echo "   - Ejemplo: \$195 a las 14:00 por 30 días\n";
    echo "   - Incluye programación automática de pagos\n\n";
    
    echo "✅ ¡Tarjetas restauradas exitosamente!\n";
    echo "Ahora puedes ver los 3 tipos funcionando en el sistema.\n";
    
} catch (Exception $e) {
    echo "❌ Error: " . $e->getMessage() . "\n";
}
?>