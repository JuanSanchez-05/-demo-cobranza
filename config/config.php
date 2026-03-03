  <?php
/**
 * Configuración del sistema y datos simulados
 */

// Iniciar sesión si no está iniciada
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Constantes
define('BASE_URL', '/demo-cobranza/');
define('ROOT_PATH', __DIR__ . '/../');

// Datos simulados de usuarios
$usuarios_simulados = [
    // Administrador
    [
        'id' => 1,
        'telefono' => '5550001',
        'password' => password_hash('admin123', PASSWORD_DEFAULT),
        'rol' => 'administrador',
        'nombre' => 'Juan Administrador'
    ],
    // Trabajadores
    [
        'id' => 2,
        'telefono' => '5550002',
        'password' => password_hash('trab123', PASSWORD_DEFAULT),
        'rol' => 'trabajador',
        'nombre' => 'Carlos Cobrador',
        'cartera_id' => 1  // Cada trabajador tiene UNA cartera
    ],
    [
        'id' => 3,
        'telefono' => '5550003',
        'password' => password_hash('trab123', PASSWORD_DEFAULT),
        'rol' => 'trabajador',
        'nombre' => 'María Recaudadora',
        'cartera_id' => 2  // Cada trabajador tiene UNA cartera
    ],
    // Clientes
    [
        'id' => 4,
        'telefono' => '5551001',
        'password' => password_hash('cliente123', PASSWORD_DEFAULT),
        'rol' => 'cliente',
        'nombre' => 'Pedro Cliente',
        'prestamos' => [1, 2]
    ],
    [
        'id' => 5,
        'telefono' => '5551002',
        'password' => password_hash('cliente123', PASSWORD_DEFAULT),
        'rol' => 'cliente',
        'nombre' => 'Ana Cliente',
        'prestamos' => [3]
    ]
];

// Estructura de CARTERAS (cada trabajador tiene UNA cartera con múltiples tarjetas)
// Las carteras son los contenedores físicos que llevan los trabajadores
$carteras = [
    // Cartera 1 - Trabajador Carlos Cobrador (ID 2)
    [
        'id' => 1,
        'trabajador_id' => 2,
        'nombre' => 'Cartera de Carlos Cobrador',
        'fecha_creacion' => '2024-01-01',
        'tarjetas' => [
            // Tarjeta 1 - Cliente Antiguo Semanal
            [
                'id' => 1,
                'tipo' => 'antigua_semanal',
                'lugar' => 'Ciudad de México',
                'fecha' => '2024-01-15',
                'nombre' => 'Pedro Cliente',
                'direccion' => 'Calle Principal 123',
                'colonia' => 'Centro',
                'telefono' => '5551001',
                'cantidad_prestamo' => 10000,
                'cargo_prestamo' => 2000,
                'total_prestamo' => 12000, // Calculado: cantidad_prestamo + cargo_prestamo
                'pago_semanal' => 1000,
                'semanas_pagar' => 12, // Calculado: total_prestamo / pago_semanal
                'dia_cobro' => 'Lunes',
                'promotor_id' => 2,
                'pagos' => [
                    ['dia' => 1, 'fecha' => '2024-01-22', 'pago' => 1000, 'saldo' => 11000, 'firma' => true],
                    ['dia' => 2, 'fecha' => '2024-01-29', 'pago' => 1000, 'saldo' => 10000, 'firma' => true],
                    ['dia' => 3, 'fecha' => '2024-02-05', 'pago' => 0, 'saldo' => 10000, 'firma' => false],
                ]
            ],
            // Tarjeta 2 - Cliente Antiguo Semanal
            [
                'id' => 2,
                'tipo' => 'antigua_semanal',
                'lugar' => 'Guadalajara',
                'fecha' => '2024-01-20',
                'nombre' => 'Luis Cliente',
                'direccion' => 'Av. Reforma 456',
                'colonia' => 'Zona Norte',
                'telefono' => '5551003',
                'cantidad_prestamo' => 15000,
                'cargo_prestamo' => 3000,
                'total_prestamo' => 18000,
                'pago_semanal' => 1500,
                'semanas_pagar' => 12,
                'dia_cobro' => 'Miércoles',
                'promotor_id' => 2,
                'pagos' => [
                    ['dia' => 1, 'fecha' => '2024-01-24', 'pago' => 1500, 'saldo' => 16500, 'firma' => true],
                    ['dia' => 2, 'fecha' => '2024-01-31', 'pago' => 0, 'saldo' => 16500, 'firma' => false],
                ]
            ],
            // Tarjeta 3 - Cliente Nuevo
            [
                'id' => 3,
                'tipo' => 'nueva',
                'fecha' => '2024-02-10',
                'lugar' => 'Ciudad de México',
                'nombre' => 'Roberto Nuevo',
                'direccion' => 'Calle Nueva 321',
                'colonia' => 'Sur',
                'giro' => 'Comercio',
                'telefono' => '5551006',
                'direccion_cobranza' => 'Calle Nueva 321',
                'aval_nombre' => 'José Aval',
                'aval_direccion' => 'Calle Aval 654',
                'aval_colonia' => 'Norte',
                'aval_telefono' => '5551007',
                'prestamo' => 20000,
                'cuota_prestamo' => 2000,
                'total_prestamo' => 24000,
                'pago' => 2000,
                'dias_pagar' => 12,
                'dia_cobro' => 'Martes',
                'hora_cobro' => '14:00',
                'promotor_id' => 2,
                'pagos' => []
            ]
        ]
    ],
    // Cartera 2 - Trabajador María Recaudadora (ID 3)
    [
        'id' => 2,
        'trabajador_id' => 3,
        'nombre' => 'Cartera de María Recaudadora',
        'fecha_creacion' => '2024-01-01',
        'tarjetas' => [
            // Tarjeta 1 - Cliente Antiguo Diaria
            [
                'id' => 4,
                'tipo' => 'antigua_diaria',
                'nombre' => 'Juan Diario',
                'cuota_diaria' => 200,
                'fecha' => '2024-01-10',
                'direccion' => 'Calle Diaria 100',
                'telefono' => '5551004',
                'valor' => 5000, // Valor total del préstamo
                'pagos' => [
                    ['dia' => 1, 'fecha' => '2024-01-11', 'pago' => 200, 'saldo' => 4800, 'firma' => true],
                    ['dia' => 2, 'fecha' => '2024-01-12', 'pago' => 200, 'saldo' => 4600, 'firma' => true],
                    ['dia' => 3, 'fecha' => '2024-01-13', 'pago' => 0, 'saldo' => 4600, 'firma' => false],
                ]
            ],
            // Tarjeta 2 - Cliente Antiguo Diaria
            [
                'id' => 5,
                'tipo' => 'antigua_diaria',
                'nombre' => 'María Diaria',
                'cuota_diaria' => 150,
                'fecha' => '2024-01-15',
                'direccion' => 'Av. Diaria 200',
                'telefono' => '5551005',
                'valor' => 3000, // Valor total del préstamo
                'pagos' => [
                    ['dia' => 1, 'fecha' => '2024-01-16', 'pago' => 150, 'saldo' => 2850, 'firma' => true],
                ]
            ],
            // Tarjeta 3 - Cliente Antiguo Semanal
            [
                'id' => 6,
                'tipo' => 'antigua_semanal',
                'lugar' => 'Monterrey',
                'fecha' => '2024-02-01',
                'nombre' => 'Ana Cliente',
                'direccion' => 'Blvd. Industrial 789',
                'colonia' => 'Industrial',
                'telefono' => '5551002',
                'cantidad_prestamo' => 8000,
                'cargo_prestamo' => 1600,
                'total_prestamo' => 9600,
                'pago_semanal' => 800,
                'semanas_pagar' => 12,
                'dia_cobro' => 'Viernes',
                'promotor_id' => 3,
                'pagos' => [
                    ['dia' => 1, 'fecha' => '2024-02-09', 'pago' => 800, 'saldo' => 8800, 'firma' => true],
                ]
            ]
        ]
    ]
];

// Función para obtener usuario por teléfono
function obtenerUsuarioPorTelefono($telefono) {
    global $usuarios_simulados;
    foreach ($usuarios_simulados as $usuario) {
        if ($usuario['telefono'] === $telefono) {
            return $usuario;
        }
    }
    return null;
}

// Función para obtener todas las carteras (contenedores)
function obtenerTodasLasCarteras() {
    global $carteras;
    return $carteras;
}

// Función para obtener cartera por trabajador (cada trabajador tiene UNA cartera)
function obtenerCarteraPorTrabajador($trabajador_id) {
    global $carteras;
    foreach ($carteras as $cartera) {
        if ($cartera['trabajador_id'] == $trabajador_id) {
            return $cartera;
        }
    }
    return null;
}

// Función para obtener todas las tarjetas de un trabajador (tarjetas dentro de su cartera)
function obtenerTarjetasPorTrabajador($trabajador_id) {
    $cartera = obtenerCarteraPorTrabajador($trabajador_id);
    if ($cartera && isset($cartera['tarjetas'])) {
        return $cartera['tarjetas'];
    }
    return [];
}

// Función para obtener cartera por ID (contenedor)
function obtenerCarteraPorId($id) {
    global $carteras;
    foreach ($carteras as $cartera) {
        if ($cartera['id'] == $id) {
            return $cartera;
        }
    }
    return null;
}

// Función para obtener tarjeta por ID (dentro de cualquier cartera)
function obtenerTarjetaPorId($tarjeta_id) {
    global $carteras;
    foreach ($carteras as $cartera) {
        if (isset($cartera['tarjetas'])) {
            foreach ($cartera['tarjetas'] as $tarjeta) {
                if ($tarjeta['id'] == $tarjeta_id) {
                    return $tarjeta;
                }
            }
        }
    }
    return null;
}

// Función para obtener todas las tarjetas de todas las carteras (para administrador)
function obtenerTodasLasTarjetas() {
    global $carteras;
    $todas_tarjetas = [];
    foreach ($carteras as $cartera) {
        if (isset($cartera['tarjetas'])) {
            foreach ($cartera['tarjetas'] as $tarjeta) {
                $tarjeta['cartera_id'] = $cartera['id'];
                $tarjeta['trabajador_id'] = $cartera['trabajador_id'];
                $todas_tarjetas[] = $tarjeta;
            }
        }
    }
    return $todas_tarjetas;
}

// Función para obtener préstamos por cliente (por teléfono) - busca en todas las tarjetas
function obtenerPrestamosPorCliente($telefono) {
    $todas_tarjetas = obtenerTodasLasTarjetas();
    $prestamos = [];
    foreach ($todas_tarjetas as $tarjeta) {
        if (isset($tarjeta['telefono']) && $tarjeta['telefono'] === $telefono) {
            $prestamos[] = $tarjeta;
        }
    }
    return $prestamos;
}

// Función para calcular estadísticas (basada en tarjetas)
function calcularEstadisticas() {
    $todas_tarjetas = obtenerTodasLasTarjetas();
    $stats = [
        'total_cobrado' => 0,
        'total_pendiente' => 0,
        'por_trabajador' => []
    ];
    
    foreach ($todas_tarjetas as $tarjeta) {
        $total_prestamo = $tarjeta['total_prestamo'] ?? ($tarjeta['valor'] ?? 0);
        $cobrado = 0;
        
        if (isset($tarjeta['pagos'])) {
            foreach ($tarjeta['pagos'] as $pago) {
                $cobrado += $pago['pago'];
            }
        }
        
        $pendiente = $total_prestamo - $cobrado;
        
        $stats['total_cobrado'] += $cobrado;
        $stats['total_pendiente'] += $pendiente;
        
        $trab_id = $tarjeta['trabajador_id'];
        if (!isset($stats['por_trabajador'][$trab_id])) {
            $stats['por_trabajador'][$trab_id] = ['cobrado' => 0, 'pendiente' => 0];
        }
        $stats['por_trabajador'][$trab_id]['cobrado'] += $cobrado;
        $stats['por_trabajador'][$trab_id]['pendiente'] += $pendiente;
    }
    
    return $stats;
}

// Función para verificar autenticación
function verificarAutenticacion() {
    if (!isset($_SESSION['usuario_id'])) {
        header('Location: ' . BASE_URL . 'index.php');
        exit;
    }
}

// Función para verificar rol
function verificarRol($rol_requerido) {
    verificarAutenticacion();
    if ($_SESSION['rol'] !== $rol_requerido) {
        header('Location: ' . BASE_URL . 'index.php?error=acceso_denegado');
        exit;
    }
}

