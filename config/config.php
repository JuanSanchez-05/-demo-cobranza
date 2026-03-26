<?php
/**
 * Configuración del sistema - Base de Datos MySQL
 */

// Configurar zona horaria para México
date_default_timezone_set('America/Mexico_City');

// NOTA: Si el servidor muestra fecha incorrecta, ajusta el reloj del sistema Windows
// Panel de Control → Fecha y hora

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Detectar si estamos en producción (Render) o en desarrollo local (XAMPP)
$isProduction = getenv('RENDER') !== false || getenv('RAILWAY_ENVIRONMENT') !== false;
define('BASE_URL', $isProduction ? '/' : '/demo-cobranza/');
define('ROOT_PATH', __DIR__ . '/../');

// ============================================
// CONFIGURACIÓN DE BASE DE DATOS
// ============================================

/**
 * Configuración de Base de Datos
 * Sistema de Cobranza
 * Soporta variables de entorno (Render, Railway, etc.) y XAMPP local
 */
class Database {
    private $host;
    private $db_name;
    private $username;
    private $password;
    private $port;
    private $driver;
    private $conn;
    
    public function __construct() {
        // Detectar si estamos en producción (PostgreSQL) o local (MySQL)
        $this->host = getenv('DB_HOST') ?: 'localhost';
        $this->db_name = getenv('DB_NAME') ?: 'cobranza_db';
        $this->username = getenv('DB_USER') ?: 'root';
        $this->password = getenv('DB_PASSWORD') ?: '';
        $this->port = getenv('DB_PORT') ?: '3307';
        
        // Detectar PostgreSQL: render.com, railway, rlwy.net o puerto 5432
        if (strpos($this->host, 'render.com') !== false || 
            strpos($this->host, 'railway') !== false ||
            strpos($this->host, '.rlwy.net') !== false ||
            $this->port == '5432') {
            $this->driver = 'pgsql';
        } else {
            $this->driver = 'mysql';
        }
    }
    
    public function getConnection() {
        // Si ya tenemos una conexión, verificar si sigue activa
        if ($this->conn) {
            try {
                $this->conn->query('SELECT 1');
                return $this->conn; // Conexión sigue activa
            } catch (PDOException $e) {
                // Conexión perdida, crear nueva
                error_log("Conexión perdida, creando nueva conexión...");
                $this->conn = null;
            }
        }
        
        try {
            // Conectar según el driver detectado
            if ($this->driver === 'pgsql') {
                // PostgreSQL (Render, Railway, etc.)
                $dsn = "pgsql:host={$this->host};port={$this->port};dbname={$this->db_name}";
                $options = [
                    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                    PDO::ATTR_EMULATE_PREPARES => false,
                ];
            } else {
                // MySQL (XAMPP local)
                $dsn = "mysql:host={$this->host};port={$this->port};dbname={$this->db_name};charset=utf8mb4";
                $options = [
                    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                    PDO::ATTR_EMULATE_PREPARES => false,
                    PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES utf8mb4 COLLATE utf8mb4_unicode_ci",
                    PDO::ATTR_PERSISTENT => false,
                    PDO::MYSQL_ATTR_USE_BUFFERED_QUERY => true,
                    PDO::MYSQL_ATTR_LOCAL_INFILE => false,
                ];
            }
            
            $this->conn = new PDO($dsn, $this->username, $this->password, $options);

            if ($this->driver === 'pgsql') {
                $this->ensurePostgresUsuariosCompatibility();
            }

            $this->ensurePagosCompatibility();
            
            // Configuraciones específicas de MySQL
            if ($this->driver === 'mysql') {
                $this->conn->exec("SET SESSION wait_timeout=28800");
                $this->conn->exec("SET SESSION interactive_timeout=28800");
                $this->conn->exec("SET SESSION net_read_timeout=300");
                $this->conn->exec("SET SESSION net_write_timeout=300");
                $this->conn->exec("SET SESSION lock_wait_timeout=300");
                $this->conn->exec("SET SESSION innodb_lock_wait_timeout=300");
                $this->conn->exec("SET SESSION sql_mode='NO_ZERO_DATE,NO_ZERO_IN_DATE,ERROR_FOR_DIVISION_BY_ZERO'");
                $this->conn->exec("SET SESSION autocommit=1");
            }
            
        } catch(PDOException $exception) {
            error_log("Error de conexión: " . $exception->getMessage());
            throw new Exception("Error de conexión a la base de datos: " . $exception->getMessage());
        }
        
        return $this->conn;
    }
    
    private function createDatabaseIfNotExists() {
        // Solo para MySQL local, no necesario en producción
        if ($this->driver !== 'mysql') {
            return;
        }
        
        try {
            // Conectar sin especificar base de datos
            $temp_conn = new PDO(
                "mysql:host={$this->host};port={$this->port};charset=utf8mb4",
                $this->username,
                $this->password,
                [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]
            );
            
            // Crear base de datos si no existe
            $temp_conn->exec("CREATE DATABASE IF NOT EXISTS `" . $this->db_name . "` 
                            CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci");
            
        } catch(PDOException $e) {
            error_log("Error creando base de datos: " . $e->getMessage());
            // No lanzar excepción aquí, intentaremos conectar de todas formas
        }
    }
    
    public function closeConnection() {
        $this->conn = null;
    }

    private function columnExists($table, $column) {
        if ($this->driver === 'pgsql') {
            $stmt = $this->conn->prepare("SELECT 1 FROM information_schema.columns WHERE table_schema = 'public' AND table_name = ? AND column_name = ? LIMIT 1");
            $stmt->execute([$table, $column]);
            return (bool)$stmt->fetchColumn();
        }

        $stmt = $this->conn->prepare("SELECT 1 FROM information_schema.columns WHERE table_schema = ? AND table_name = ? AND column_name = ? LIMIT 1");
        $stmt->execute([$this->db_name, $table, $column]);
        return (bool)$stmt->fetchColumn();
    }

    private function ensurePostgresUsuariosCompatibility() {
        if ($this->driver !== 'pgsql') {
            return;
        }

        try {
            $this->conn->exec("ALTER TABLE usuarios ADD COLUMN IF NOT EXISTS telefono VARCHAR(20)");
            $this->conn->exec("ALTER TABLE usuarios ADD COLUMN IF NOT EXISTS nombre VARCHAR(100)");
            $this->conn->exec("ALTER TABLE usuarios ADD COLUMN IF NOT EXISTS fecha_creacion TIMESTAMP DEFAULT CURRENT_TIMESTAMP");
            $this->conn->exec("ALTER TABLE usuarios ADD COLUMN IF NOT EXISTS fecha_actualizacion TIMESTAMP DEFAULT CURRENT_TIMESTAMP");

            if ($this->columnExists('usuarios', 'username') && $this->columnExists('usuarios', 'telefono')) {
                $this->conn->exec("UPDATE usuarios SET telefono = username WHERE telefono IS NULL AND username IS NOT NULL");
            }

            if ($this->columnExists('usuarios', 'nombre_completo') && $this->columnExists('usuarios', 'nombre')) {
                $this->conn->exec("UPDATE usuarios SET nombre = nombre_completo WHERE nombre IS NULL AND nombre_completo IS NOT NULL");
            }

            $this->conn->exec("UPDATE usuarios SET rol = 'administrador' WHERE rol = 'admin'");
            $this->conn->exec("CREATE UNIQUE INDEX IF NOT EXISTS idx_usuarios_telefono_unique ON usuarios(telefono)");
            $this->conn->exec("CREATE INDEX IF NOT EXISTS idx_usuarios_rol ON usuarios(rol)");
        } catch (PDOException $e) {
            error_log('Compatibilidad PostgreSQL (usuarios) falló: ' . $e->getMessage());
        }
    }

    private function ensurePagosCompatibility() {
        try {
            if (!$this->columnExists('pagos', 'observacion')) {
                $this->conn->exec("ALTER TABLE pagos ADD COLUMN observacion TEXT NULL");
            }
        } catch (PDOException $e) {
            error_log('Compatibilidad pagos (observacion) falló: ' . $e->getMessage());
        }
    }
}

function getDB() {
    static $database = null;
    if ($database === null) {
        $database = new Database();
    }
    return $database->getConnection();
}

// ============================================
// AUTENTICACIÓN
// ============================================

function obtenerUsuarioPorTelefono($telefono) {
    $db = getDB();
    $stmt = $db->prepare("SELECT * FROM usuarios WHERE telefono = ? AND activo = TRUE");
    $stmt->execute([$telefono]);
    return $stmt->fetch();
}

function verificarAutenticacion() {
    if (!isset($_SESSION['usuario_id'])) {
        header('Location: ' . BASE_URL . 'index.php');
        exit;
    }
}

function verificarRol($rol_requerido) {
    verificarAutenticacion();
    if ($_SESSION['rol'] !== $rol_requerido) {
        header('Location: ' . BASE_URL . 'index.php?error=acceso_denegado');
        exit;
    }
}

// ============================================
// TRABAJADORES
// ============================================

function obtenerTodosTrabajadores() {
    $db = getDB();
    $stmt = $db->query("SELECT * FROM usuarios WHERE rol = 'trabajador' ORDER BY nombre");
    return $stmt->fetchAll();
}

function obtenerTrabajadoresSimulados() {
    return obtenerTodosTrabajadores();
}

function obtenerTrabajadorPorId($id) {
    $db = getDB();
    $stmt = $db->prepare("SELECT * FROM usuarios WHERE id = ? AND rol = 'trabajador'");
    $stmt->execute([$id]);
    return $stmt->fetch();
}

function agregarTrabajadorSimulado($data) {
    $db = getDB();
    $pass = !empty($data['password'])
        ? password_hash($data['password'], PASSWORD_DEFAULT)
        : password_hash('trabajador123', PASSWORD_DEFAULT);
    $stmt = $db->prepare("INSERT INTO usuarios (telefono, password, rol, nombre) VALUES (?, ?, 'trabajador', ?)");
    $stmt->execute([$data['telefono'], $pass, $data['nombre']]);
    return $db->lastInsertId();
}

function actualizarTrabajadorSimulado($id, $data) {
    $db = getDB();
    if (!empty($data['password'])) {
        $stmt = $db->prepare("UPDATE usuarios SET telefono = ?, nombre = ?, password = ? WHERE id = ?");
        $stmt->execute([$data['telefono'], $data['nombre'], password_hash($data['password'], PASSWORD_DEFAULT), $id]);
    } else {
        $stmt = $db->prepare("UPDATE usuarios SET telefono = ?, nombre = ? WHERE id = ?");
        $stmt->execute([$data['telefono'], $data['nombre'], $id]);
    }
}

function togglearEstadoTrabajadorSimulado($id) {
    $db = getDB();
    $stmt = $db->prepare("UPDATE usuarios SET activo = NOT activo WHERE id = ?");
    $stmt->execute([$id]);
}

function obtenerNombreUsuarioPorId($id) {
    if (!$id) return 'N/A';
    $db = getDB();
    $stmt = $db->prepare("SELECT nombre FROM usuarios WHERE id = ?");
    $stmt->execute([$id]);
    $row = $stmt->fetch();
    return $row ? $row['nombre'] : 'N/A';
}

// ============================================
// CARTERAS
// ============================================

function obtenerTodasLasCarteras() {
    $db = getDB();
    $stmt = $db->query("
        SELECT c.*, u.nombre AS trabajador_nombre
        FROM carteras c
        JOIN usuarios u ON c.trabajador_id = u.id
        WHERE c.activo = TRUE
        ORDER BY c.nombre
    ");
    $carteras = $stmt->fetchAll();
    foreach ($carteras as &$cartera) {
        $cartera['tarjetas']   = obtenerTarjetasPorCartera($cartera['id']);
        $cartera['completadas'] = obtenerTarjetasCompletadasPorCartera($cartera['id']);
    }
    return $carteras;
}

function obtenerCarteraPorId($cartera_id) {
    $db = getDB();
    $stmt = $db->prepare("
        SELECT c.*, u.nombre AS trabajador_nombre
        FROM carteras c
        JOIN usuarios u ON c.trabajador_id = u.id
        WHERE c.id = ?
    ");
    $stmt->execute([$cartera_id]);
    $cartera = $stmt->fetch();
    if ($cartera) {
        $cartera['tarjetas']   = obtenerTarjetasPorCartera($cartera['id']);
        $cartera['completadas'] = obtenerTarjetasCompletadasPorCartera($cartera['id']);
    }
    return $cartera;
}

function obtenerCarteraPorTrabajador($trabajador_id) {
    $db = getDB();
    $stmt = $db->prepare("SELECT * FROM carteras WHERE trabajador_id = ? AND activo = TRUE LIMIT 1");
    $stmt->execute([$trabajador_id]);
    $cartera = $stmt->fetch();
    if ($cartera) {
        $cartera['tarjetas']   = obtenerTarjetasPorCartera($cartera['id']);
        $cartera['completadas'] = obtenerTarjetasCompletadasPorCartera($cartera['id']);
    }
    return $cartera;
}

function agregarCarteraSimulada($data) {
    $db = getDB();
    $stmt = $db->prepare("INSERT INTO carteras (trabajador_id, nombre) VALUES (?, ?)");
    $stmt->execute([$data['trabajador_id'], $data['nombre']]);
    return $db->lastInsertId();
}

function actualizarCarteraSimulada($id, $data) {
    $db = getDB();
    $stmt = $db->prepare("UPDATE carteras SET trabajador_id = ?, nombre = ? WHERE id = ?");
    $stmt->execute([$data['trabajador_id'], $data['nombre'], $id]);
}

function eliminarCarteraSimulada($id) {
    $db = getDB();
    $stmt = $db->prepare("UPDATE carteras SET activo = FALSE WHERE id = ?");
    $stmt->execute([$id]);
}

function guardarCarterasSimuladas($carteras_nuevas) {
    // Con BD real no se necesita; cada operación es individual
}

// ============================================
// TARJETAS
// ============================================

function obtenerTarjetasPorCartera($cartera_id) {
    $db = getDB();
    $stmt = $db->prepare("SELECT * FROM tarjetas WHERE cartera_id = ? AND estado = 'activo' ORDER BY nombre");
    $stmt->execute([$cartera_id]);
    $tarjetas = $stmt->fetchAll();
    foreach ($tarjetas as &$tarjeta) {
        $tarjeta['pagos'] = obtenerPagosPorTarjeta($tarjeta['id']);
    }
    return $tarjetas;
}

function obtenerTarjetasCompletadasPorCartera($cartera_id) {
    $db = getDB();
    $stmt = $db->prepare("SELECT * FROM tarjetas WHERE cartera_id = ? AND estado = 'completado' ORDER BY fecha_completada DESC");
    $stmt->execute([$cartera_id]);
    return $stmt->fetchAll();
}

function obtenerTarjetasPorTrabajador($trabajador_id) {
    $db = getDB();
    $stmt = $db->prepare("
        SELECT t.*
        FROM tarjetas t
        JOIN carteras c ON t.cartera_id = c.id
        WHERE c.trabajador_id = ? AND t.estado = 'activo' AND c.activo = TRUE
        ORDER BY t.nombre
    ");
    $stmt->execute([$trabajador_id]);
    $tarjetas = $stmt->fetchAll();
    foreach ($tarjetas as &$tarjeta) {
        $tarjeta['pagos'] = obtenerPagosPorTarjeta($tarjeta['id']);
    }
    return $tarjetas;
}

function obtenerTarjetaPorId($tarjeta_id) {
    $db = getDB();
    $stmt = $db->prepare("
        SELECT t.*, c.trabajador_id, c.nombre AS cartera_nombre
        FROM tarjetas t
        JOIN carteras c ON t.cartera_id = c.id
        WHERE t.id = ?
    ");
    $stmt->execute([$tarjeta_id]);
    $tarjeta = $stmt->fetch();
    if ($tarjeta) {
        $tarjeta['pagos'] = obtenerPagosPorTarjeta($tarjeta['id']);
    }
    return $tarjeta;
}

function obtenerTodasLasTarjetas() {
    $db = getDB();
    $stmt = $db->query("
        SELECT t.*, c.nombre AS cartera_nombre, c.trabajador_id, u.nombre AS trabajador_nombre
        FROM tarjetas t
        JOIN carteras c ON t.cartera_id = c.id
        JOIN usuarios u ON c.trabajador_id = u.id
        WHERE t.estado = 'activo'
        ORDER BY t.nombre
    ");
    $tarjetas = $stmt->fetchAll();
    foreach ($tarjetas as &$tarjeta) {
        $tarjeta['pagos'] = obtenerPagosPorTarjeta($tarjeta['id']);
    }
    return $tarjetas;
}

function obtenerCarteraPorTarjeta($tarjeta_id) {
    $tarjeta = obtenerTarjetaPorId($tarjeta_id);
    if ($tarjeta) {
        return obtenerCarteraPorId($tarjeta['cartera_id']);
    }
    return null;
}

function agregarTarjetaSimulada($data, $cartera_id) {
    $db = getDB();
    $stmt = $db->prepare("
        INSERT INTO tarjetas (
            cartera_id, tipo, nombre, direccion, colonia, telefono, lugar,
            cantidad_prestamo, cargo_prestamo, total_prestamo,
            pago_semanal, semanas_pagar, dia_cobro,
            cuota_diaria, dias_pagar,
            giro, direccion_cobranza, aval_nombre, aval_direccion, aval_colonia, aval_telefono,
            prestamo, cuota_prestamo, pago, hora_cobro,
            promotor_id, fecha
        ) VALUES (
            ?, ?, ?, ?, ?, ?, ?,
            ?, ?, ?,
            ?, ?, ?,
            ?, ?,
            ?, ?, ?, ?, ?, ?,
            ?, ?, ?, ?,
            ?, ?
        )
    ");
    $stmt->execute([
        $cartera_id,
        $data['tipo'] ?? 'antigua_semanal',
        $data['nombre'] ?? '',
        $data['direccion'] ?? null,
        $data['colonia'] ?? null,
        $data['telefono'] ?? null,
        $data['lugar'] ?? null,
        $data['cantidad_prestamo'] ?? 0,
        $data['cargo_prestamo'] ?? 0,
        $data['total_prestamo'] ?? 0,
        $data['pago_semanal'] ?? 0,
        $data['semanas_pagar'] ?? 0,
        $data['dia_cobro'] ?? null,
        $data['cuota_diaria'] ?? 0,
        $data['dias_pagar'] ?? 0,
        $data['giro'] ?? null,
        $data['direccion_cobranza'] ?? null,
        $data['aval_nombre'] ?? null,
        $data['aval_direccion'] ?? null,
        $data['aval_colonia'] ?? null,
        $data['aval_telefono'] ?? null,
        $data['prestamo'] ?? 0,
        $data['cuota_prestamo'] ?? 0,
        $data['pago'] ?? 0,
        $data['hora_cobro'] ?? null,
        $data['promotor_id'] ?? null,
        $data['fecha'] ?? date('Y-m-d'),
    ]);
    return $db->lastInsertId();
}

function marcarTarjetaCompletada($tarjeta_id) {
    $db = getDB();
    $tarjeta = obtenerTarjetaPorId($tarjeta_id);
    if (!$tarjeta) return false;
    
    $stmt = $db->prepare("UPDATE tarjetas SET estado = 'completado', fecha_completada = CURRENT_DATE WHERE id = ?");
    $result = $stmt->execute([$tarjeta_id]);
    
    return $result;
}

// ============================================
// PAGOS
// ============================================

function obtenerPagosPorTarjeta($tarjeta_id) {
    $db = getDB();
    $stmt = $db->prepare("SELECT * FROM pagos WHERE tarjeta_id = ? ORDER BY dia");
    $stmt->execute([$tarjeta_id]);
    return $stmt->fetchAll();
}

function registrarPagoTarjeta($tarjeta_id, $numero_dia) {
    $db = getDB();
    $tarjeta = obtenerTarjetaPorId($tarjeta_id);
    if (!$tarjeta) return false;

    // Calcular monto según tipo
    switch ($tarjeta['tipo']) {
        case 'antigua_semanal': $monto = $tarjeta['pago_semanal']; break;
        case 'antigua_diaria':  $monto = $tarjeta['cuota_diaria']; break;
        case 'nueva':           $monto = $tarjeta['pago'];          break;
        default:                $monto = 0;
    }

    try {
        // Obtener pago actual
        $stmt = $db->prepare("SELECT * FROM pagos WHERE tarjeta_id = ? AND dia = ?");
        $stmt->execute([$tarjeta_id, $numero_dia]);
        $pago_actual = $stmt->fetch();

        if ($pago_actual) {
            // Calcular nuevo saldo
            $saldo_anterior = $pago_actual['saldo'] + $pago_actual['pago']; // saldo antes de este pago
            $nuevo_saldo = $saldo_anterior - $monto;

            $fechaRegistro = date('Y-m-d H:i:s');
            $stmt = $db->prepare("
                UPDATE pagos SET pago = ?, saldo = ?, cobrador_id = ?, fecha_registro = ?
                WHERE tarjeta_id = ? AND dia = ?
            ");
            $stmt->execute([$monto, $nuevo_saldo, $_SESSION['usuario_id'], $fechaRegistro, $tarjeta_id, $numero_dia]);
        } else {
            // Obtener saldo del pago anterior
            $stmt = $db->prepare("SELECT saldo FROM pagos WHERE tarjeta_id = ? AND dia < ? ORDER BY dia DESC LIMIT 1");
            $stmt->execute([$tarjeta_id, $numero_dia]);
            $anterior = $stmt->fetch();
            $saldo_base = $anterior ? $anterior['saldo'] : $tarjeta['total_prestamo'];
            $nuevo_saldo = $saldo_base - $monto;

            $fechaRegistro = date('Y-m-d H:i:s');
            $stmt = $db->prepare("
                INSERT INTO pagos (tarjeta_id, dia, fecha, pago, saldo, cobrador_id, fecha_registro)
                VALUES (?, ?, CURRENT_DATE, ?, ?, ?, ?)
            ");
            $stmt->execute([$tarjeta_id, $numero_dia, $monto, $nuevo_saldo, $_SESSION['usuario_id'], $fechaRegistro]);
        }

        // Si saldo llega a 0, marcar como completada
        if ($nuevo_saldo <= 0) {
            $db->prepare("UPDATE tarjetas SET estado = 'completado', fecha_completada = CURRENT_DATE WHERE id = ?")
               ->execute([$tarjeta_id]);
        }

        return true;
    } catch (PDOException $e) {
        error_log('Error registrando pago: ' . $e->getMessage());
        return false;
    }
}

// ============================================
// DASHBOARD TRABAJADOR
// ============================================

function obtenerEstadisticasTrabajador($trabajador_id) {
    $db  = getDB();
    $hoy = date('Y-m-d');
    $stmt = $db->prepare("SELECT COUNT(*) FROM tarjetas t JOIN carteras c ON t.cartera_id = c.id WHERE c.trabajador_id = ? AND t.estado = 'activo'");
    $stmt->execute([$trabajador_id]);
    $total_tarjetas = $stmt->fetchColumn();

    $stmt = $db->prepare("SELECT COUNT(*) FROM tarjetas t JOIN carteras c ON t.cartera_id = c.id WHERE c.trabajador_id = ? AND t.estado = 'completado'");
    $stmt->execute([$trabajador_id]);
    $completadas = $stmt->fetchColumn();

    $stmt = $db->prepare(" 
        SELECT COALESCE(SUM(p.pago), 0)
        FROM pagos p
        JOIN tarjetas t ON p.tarjeta_id = t.id
        JOIN carteras c ON t.cartera_id = c.id
                WHERE c.trabajador_id = ? AND DATE(p.fecha_registro) = ? AND p.pago > 0
          AND (t.tipo <> 'antigua_semanal' OR MOD(p.dia, 7) = 0)
    ");
    $stmt->execute([$trabajador_id, $hoy]);
    $cobrado_hoy = $stmt->fetchColumn();

    $stmt = $db->prepare("
        SELECT COALESCE(SUM(
            CASE t.tipo
                WHEN 'antigua_semanal' THEN t.pago_semanal
                WHEN 'antigua_diaria'  THEN t.cuota_diaria
                WHEN 'nueva'           THEN t.pago
                ELSE 0
            END
        ), 0)
        FROM pagos p
        JOIN tarjetas t ON p.tarjeta_id = t.id
        JOIN carteras c ON t.cartera_id = c.id
        WHERE c.trabajador_id = ? AND p.fecha = ? AND p.pago = 0
          AND p.fecha_registro IS NULL
          AND (t.tipo <> 'antigua_semanal' OR MOD(p.dia, 7) = 0)
    ");
    $stmt->execute([$trabajador_id, $hoy]);
    $debe_cobrar_hoy = $stmt->fetchColumn();

    // Pendiente total general (suma de todos los saldos finales)
    $stmt = $db->prepare("
        SELECT COALESCE(SUM(p.saldo), 0)
        FROM pagos p
        JOIN (
            SELECT tarjeta_id, MAX(dia) AS ultimo_dia FROM pagos GROUP BY tarjeta_id
        ) ult ON p.tarjeta_id = ult.tarjeta_id AND p.dia = ult.ultimo_dia
        JOIN tarjetas t ON p.tarjeta_id = t.id
        JOIN carteras c ON t.cartera_id = c.id
        WHERE c.trabajador_id = ? AND t.estado = 'activo'
    ");
    $stmt->execute([$trabajador_id]);
    $pendiente_general = $stmt->fetchColumn();

    // Pendiente de HOY (saldo actual de los padrones que vencen hoy)
        $stmt = $db->prepare("
                SELECT COALESCE(SUM(p.saldo), 0)
                FROM pagos p
                JOIN tarjetas t ON p.tarjeta_id = t.id
                JOIN carteras c ON t.cartera_id = c.id
                WHERE c.trabajador_id = ? AND p.fecha = ? 
                    AND t.estado = 'activo'
                    AND p.pago = 0
                    AND p.fecha_registro IS NULL
                    AND (t.tipo <> 'antigua_semanal' OR MOD(p.dia, 7) = 0)
        ");
    $stmt->execute([$trabajador_id, $hoy]);
    $pendiente_hoy = $stmt->fetchColumn();

    return compact('total_tarjetas', 'completadas', 'cobrado_hoy', 'debe_cobrar_hoy', 'pendiente_hoy', 'pendiente_general');
}

function obtenerCobrosHoy($trabajador_id) {
    $db  = getDB();
    $hoy = date('Y-m-d');
    $stmt = $db->prepare("
        SELECT t.id, t.nombre, t.direccion, t.tipo, p.dia,
               t.pago_semanal, t.cuota_diaria, t.pago AS pago_nuevo,
               p.pago AS ya_cobrado_monto,
               p.fecha,
               p.fecha_registro,
               p.observacion,
               CASE
                   WHEN p.pago > 0 AND LOWER(COALESCE(p.observacion, '')) LIKE '%pagado con retraso%' THEN 'pagado_retraso'
                   WHEN p.pago > 0 THEN 'cobrado'
                   WHEN p.pago = 0 AND p.fecha < ? AND p.fecha_registro IS NOT NULL THEN 'pendiente'
                   WHEN p.pago = 0 AND p.fecha = ? AND p.fecha_registro IS NOT NULL THEN 'pendiente'
                   ELSE 'programado'
               END AS estado_visita
        FROM pagos p
        JOIN tarjetas t ON p.tarjeta_id = t.id
        JOIN carteras c ON t.cartera_id = c.id
        WHERE c.trabajador_id = ? AND t.estado = 'activo'
          AND (
              p.fecha = ?
              OR (p.pago = 0 AND p.fecha < ? AND p.fecha_registro IS NOT NULL)
          )
          AND (t.tipo <> 'antigua_semanal' OR MOD(p.dia, 7) = 0)
        ORDER BY
            CASE
                WHEN p.pago = 0 AND p.fecha = ? AND p.fecha_registro IS NULL THEN 1
                WHEN p.pago = 0 AND p.fecha_registro IS NOT NULL THEN 2
                WHEN p.pago > 0 THEN 3
                ELSE 4
            END,
            p.fecha,
            t.nombre
    ");
    $stmt->execute([$hoy, $hoy, $trabajador_id, $hoy, $hoy, $hoy]);
    $rows = $stmt->fetchAll();

    $cobros = [];
    foreach ($rows as $r) {
        switch ($r['tipo']) {
            case 'antigua_semanal': $monto = $r['pago_semanal']; break;
            case 'antigua_diaria':  $monto = $r['cuota_diaria']; break;
            case 'nueva':           $monto = $r['pago_nuevo'];   break;
            default:                $monto = 0;
        }
        $cobros[] = [
            'id'          => $r['id'],
            'nombre'      => $r['nombre'],
            'direccion'   => $r['direccion'] ?? '',
            'tipo'        => $r['tipo'],
            'dia'         => intval($r['dia'] ?? 0),
            'pago_semanal'=> floatval($r['pago_semanal'] ?? 0),
            'cuota_diaria'=> floatval($r['cuota_diaria'] ?? 0),
            'pago_nuevo'  => floatval($r['pago_nuevo'] ?? 0),
            'ya_cobrado_monto' => floatval($r['ya_cobrado_monto'] ?? 0),
            'fecha'       => $r['fecha'] ?? null,
            'observacion' => $r['observacion'] ?? null,
            'estado_visita' => $r['estado_visita'] ?? 'programado',
            'monto'       => $monto,
            'ya_cobrado'  => $r['ya_cobrado_monto'] > 0,
        ];
    }
    return $cobros;
}

function obtenerCobradoTrabajadorPorFecha($trabajador_id, $fecha) {
    $db = getDB();
    $stmt = $db->prepare(" 
        SELECT COALESCE(SUM(p.pago), 0)
        FROM pagos p
        JOIN tarjetas t ON p.tarjeta_id = t.id
        JOIN carteras c ON t.cartera_id = c.id
                WHERE c.trabajador_id = ? AND DATE(p.fecha_registro) = ? AND p.pago > 0
          AND (t.tipo <> 'antigua_semanal' OR MOD(p.dia, 7) = 0)
    ");
    $stmt->execute([$trabajador_id, $fecha]);
    return floatval($stmt->fetchColumn());
}

function obtenerPendienteTrabajadorPorFecha($trabajador_id, $fecha) {
    $db = getDB();
    $stmt = $db->prepare("
        SELECT COALESCE(SUM(
            CASE t.tipo
                WHEN 'antigua_semanal' THEN t.pago_semanal
                WHEN 'antigua_diaria'  THEN t.cuota_diaria
                WHEN 'nueva'           THEN t.pago
                ELSE 0
            END
        ), 0)
        FROM pagos p
        JOIN tarjetas t ON p.tarjeta_id = t.id
        JOIN carteras c ON t.cartera_id = c.id
        WHERE c.trabajador_id = ? AND p.fecha = ? AND p.pago = 0
          AND p.fecha_registro IS NULL
          AND t.estado = 'activo'
          AND (t.tipo <> 'antigua_semanal' OR MOD(p.dia, 7) = 0)
    ");
    $stmt->execute([$trabajador_id, $fecha]);
    return floatval($stmt->fetchColumn());
}

function obtenerCobrosRegistradosTrabajadorPorFecha($trabajador_id, $fecha) {
    $db = getDB();
    $stmt = $db->prepare("
        SELECT t.id, t.nombre, t.direccion, p.dia, p.pago AS ya_cobrado_monto, p.fecha
        FROM pagos p
        JOIN tarjetas t ON p.tarjeta_id = t.id
        JOIN carteras c ON t.cartera_id = c.id
        WHERE c.trabajador_id = ? AND p.fecha = ? AND p.pago > 0
          AND t.estado = 'activo'
          AND (t.tipo <> 'antigua_semanal' OR MOD(p.dia, 7) = 0)
        ORDER BY t.nombre
    ");
    $stmt->execute([$trabajador_id, $fecha]);
    return $stmt->fetchAll();
}

// ============================================
// DASHBOARD ADMIN
// ============================================

function calcularEstadisticas() {
    $db = getDB();
    $hoy = date('Y-m-d');

    // Total tarjetas activas
    $total_tarjetas = $db->query("SELECT COUNT(*) FROM tarjetas WHERE estado = 'activo'")->fetchColumn();
    
    // Total trabajadores activos
    $total_trabajadores = $db->query("SELECT COUNT(*) FROM usuarios WHERE rol = 'trabajador' AND activo = TRUE")->fetchColumn();

    // Cobrado hoy (suma de todos los pagos registrados hoy)
    $stmt = $db->prepare("SELECT COALESCE(SUM(pago), 0) FROM pagos WHERE fecha = ? AND pago > 0");
    $stmt->execute([$hoy]);
    $cobrado_hoy = $stmt->fetchColumn();

    // Calcular saldo pendiente total según tipo de tarjeta
    $stmt = $db->query("
        SELECT 
            t.id,
            t.tipo,
            t.total_prestamo,
            t.pago_semanal,
            t.cuota_diaria,
            t.pago,
            COALESCE(SUM(p.pago), 0) as total_pagado
        FROM tarjetas t
        LEFT JOIN pagos p ON t.id = p.tarjeta_id AND p.pago > 0
        WHERE t.estado = 'activo'
        GROUP BY t.id, t.tipo, t.total_prestamo, t.pago_semanal, t.cuota_diaria, t.pago
    ");
    
    $pendiente_total = 0;
    $total_prestado = 0;
    
    while ($tarjeta = $stmt->fetch(PDO::FETCH_ASSOC)) {
        $monto_total = floatval($tarjeta['total_prestamo']);
        $pagado = floatval($tarjeta['total_pagado']);
        $pendiente = $monto_total - $pagado;
        
        if ($pendiente > 0) {
            $pendiente_total += $pendiente;
        }
        $total_prestado += $monto_total;
    }

    return [
        'total_tarjetas' => $total_tarjetas,
        'total_trabajadores' => $total_trabajadores, 
        'cobrado_hoy' => $cobrado_hoy,
        'pendiente_total' => $pendiente_total,
        'total_prestado' => $total_prestado
    ];
}

// ============================================
// FILTROS
// ============================================

function aplicarFiltroTarjetas($tarjetas, $filtro) {
    $hoy = date('Y-m-d');
    switch ($filtro) {
        case 'cobradas_hoy':
            return array_filter($tarjetas, function($t) use ($hoy) {
                foreach ($t['pagos'] as $p) {
                    $es_dia_valido = ($t['tipo'] !== 'antigua_semanal') || ((intval($p['dia']) % 7) === 0);
                    if ($p['fecha'] === $hoy && $p['pago'] > 0 && $es_dia_valido) return true;
                }
                return false;
            });
        case 'no_cobradas_hoy':
            return array_filter($tarjetas, function($t) use ($hoy) {
                foreach ($t['pagos'] as $p) {
                    $es_dia_valido = ($t['tipo'] !== 'antigua_semanal') || ((intval($p['dia']) % 7) === 0);
                    if ($p['fecha'] === $hoy && $p['pago'] == 0 && $es_dia_valido) return true;
                }
                return false;
            });
        default:
            return $tarjetas;
    }
}

// ============================================ 
// FUNCIONES CRUD ADICIONALES
// ============================================

function agregarCartera($data) {
    $db = getDB();
    $stmt = $db->prepare("INSERT INTO carteras (trabajador_id, nombre, descripcion) VALUES (?, ?, ?)");
    $stmt->execute([
        $data['trabajador_id'],
        $data['nombre'],
        $data['descripcion'] ?? ''
    ]);
    return $db->lastInsertId();
}

function actualizarCartera($id, $data) {
    $db = getDB();
    $fields = [];
    $params = [];
    
    if (isset($data['nombre'])) {
        $fields[] = 'nombre = ?';
        $params[] = $data['nombre'];
    }
    if (isset($data['descripcion'])) {
        $fields[] = 'descripcion = ?';
        $params[] = $data['descripcion'];
    }
    if (isset($data['trabajador_id'])) {
        $fields[] = 'trabajador_id = ?';
        $params[] = $data['trabajador_id'];
    }
    
    if (empty($fields)) return false;
    
    $params[] = $id;
    $sql = "UPDATE carteras SET " . implode(', ', $fields) . " WHERE id = ?";
    $stmt = $db->prepare($sql);
    return $stmt->execute($params);
}

function eliminarCartera($id) {
    $db = getDB();
    $stmt = $db->prepare("UPDATE carteras SET activo = FALSE WHERE id = ?");
    return $stmt->execute([$id]);
}

function agregarTarjeta($data, $cartera_id) {
    $db = getDB();
    
    // Debug: Registrar datos recibidos
    error_log("agregarTarjeta - Datos recibidos: " . print_r($data, true));
    
    // Normalizar tipo: convertir 'semanal' → 'antigua_semanal', 'diaria' → 'antigua_diaria'
    $tipo_raw = trim($data['tipo'] ?? '');
    
    // Mapeo de tipos
    $tipo_map = [
        'semanal' => 'antigua_semanal',
        'diaria' => 'antigua_diaria',
        'nueva' => 'nueva',
        'antigua_semanal' => 'antigua_semanal',
        'antigua_diaria' => 'antigua_diaria',
    ];
    
    $tipo = $tipo_map[$tipo_raw] ?? '';
    
    // Si tipo está vacío o no reconocido, intentar deducir del contenido de los datos
    if (empty($tipo)) {
        // Si tiene prestamo, es tipo nueva
        if (!empty($data['prestamo'])) {
            $tipo = 'nueva';
        }
        // Si tiene valor y cuota_diaria, es tipo diaria
        elseif (!empty($data['valor']) && !empty($data['cuota_diaria'])) {
            $tipo = 'antigua_diaria';
        }
        // Si tiene cantidad_prestamo y cargo_prestamo, es tipo semanal
        elseif (!empty($data['cantidad_prestamo']) && !empty($data['cargo_prestamo'])) {
            $tipo = 'antigua_semanal';
        }
        // Si tiene pago_semanal sin prestamo nuevo, es tipo semanal
        elseif (!empty($data['pago_semanal'])) {
            $tipo = 'antigua_semanal';
        }
        // Si tiene cuota_diaria sin prestamo nuevo, es tipo diaria
        elseif (!empty($data['cuota_diaria'])) {
            $tipo = 'antigua_diaria';
        }
        // Default a nueva
        else {
            $tipo = 'nueva';
        }
        error_log("agregarTarjeta - Tipo deducido (estaba vacío): $tipo");
        // Actualizar $data con el tipo deducido para que esté disponible en crearPagosProgramados()
        $data['tipo'] = $tipo;
    }
    
    if ($tipo === 'antigua_semanal') {
        $cantidad = floatval($data['cantidad_prestamo'] ?? 0);
        $cargo = floatval($data['cargo_prestamo'] ?? 0);
        $total_prestamo = $cantidad + $cargo;
        $semanas = intval($data['semanas_pagar'] ?? 0);
        $pago_semanal = $semanas > 0 ? $total_prestamo / $semanas : 0;
        $data['semanas_pagar'] = $semanas;
        $data['pago_semanal'] = $pago_semanal;
        $data['total_prestamo'] = $total_prestamo;
        
    } elseif ($tipo === 'antigua_diaria') {
        $total_prestamo = floatval($data['valor'] ?? 0);
        $cuota_diaria = floatval($data['cuota_diaria'] ?? 0);
        $dias = $cuota_diaria > 0 ? ceil($total_prestamo / $cuota_diaria) : 0;
        $data['dias_pagar'] = $dias;
        $data['total_prestamo'] = $total_prestamo;
        
    } else { // nueva
        $prestamo = floatval($data['prestamo'] ?? 0);
        $pago_diario = floatval($data['pago'] ?? 0);
        $dias_pagar = intval($data['dias_pagar'] ?? 0);

        // Sin cuota adicional: el total es exactamente el préstamo capturado
        $total_prestamo = $prestamo;

        // Si no hay pago diario especificado pero sí días, calcularlo
        if ($pago_diario <= 0 && $dias_pagar > 0 && $total_prestamo > 0) {
            $pago_diario = $total_prestamo / $dias_pagar;
        }

        $data['prestamo'] = $prestamo;
        $data['cuota_prestamo'] = 0;
        $data['pago'] = $pago_diario;
        $data['dias_pagar'] = $dias_pagar;
        $data['total_prestamo'] = $total_prestamo;

        error_log("agregarTarjeta - Valores calculados: prestamo=$prestamo, total=$total_prestamo, pago_diario=$pago_diario, dias=$dias_pagar");
    }
    
    $stmt = $db->prepare("
        INSERT INTO tarjetas (
            cartera_id, tipo, nombre, direccion, colonia, telefono, lugar,
            cantidad_prestamo, cargo_prestamo, total_prestamo,
            pago_semanal, semanas_pagar, dia_cobro,
            cuota_diaria, dias_pagar,
            giro, direccion_cobranza, aval_nombre, aval_direccion, aval_colonia, aval_telefono,
            prestamo, cuota_prestamo, pago, hora_cobro,
            promotor_id, fecha
        ) VALUES (
            ?, ?, ?, ?, ?, ?, ?,
            ?, ?, ?,
            ?, ?, ?,
            ?, ?,
            ?, ?, ?, ?, ?, ?,
            ?, ?, ?, ?,
            ?, ?
        )
    ");
    
    $valores = [
        $cartera_id,
        $tipo,
        $data['nombre'] ?? '',
        $data['direccion'] ?? '',
        $data['colonia'] ?? '',
        $data['telefono'] ?? '',
        $data['lugar'] ?? '',
        $data['cantidad_prestamo'] ?? 0,
        $data['cargo_prestamo'] ?? 0,
        $data['total_prestamo'] ?? 0,
        $data['pago_semanal'] ?? 0,
        $data['semanas_pagar'] ?? 0,
        $data['dia_cobro'] ?? null,
        $data['cuota_diaria'] ?? 0,
        $data['dias_pagar'] ?? 0,
        $data['giro'] ?? '',
        $data['direccion_cobranza'] ?? '',
        $data['aval_nombre'] ?? '',
        $data['aval_direccion'] ?? '',
        $data['aval_colonia'] ?? '',
        $data['aval_telefono'] ?? '',
        $data['prestamo'] ?? 0,
        $data['cuota_prestamo'] ?? 0,
        $data['pago'] ?? 0,
        $data['hora_cobro'] ?? null,
        $data['promotor_id'] ?? null,
        $data['fecha'] ?? date('Y-m-d')
    ];
    
    // Debug: Registrar valores que se van a insertar
    error_log("agregarTarjeta - Valores para insertar: " . print_r($valores, true));
    
    $stmt->execute($valores);
    
    $tarjeta_id = $db->lastInsertId();
    
    // Crear registros de pagos programados solo si hay datos válidos
    // IMPORTANTE: Pasar el tipo normalizado a la función de pagos
    if ($tarjeta_id && ($data['total_prestamo'] ?? 0) > 0) {
        $data['tipo'] = $tipo;  // Asignar el tipo normalizado a $data
        crearPagosProgramados($tarjeta_id, $data);
    }
    
    return $tarjeta_id;
}

function crearPagosProgramados($tarjeta_id, $data) {
    $db = getDB();
    
    $tipo = $data['tipo'] ?? 'nueva';
    $total = floatval($data['total_prestamo'] ?? 0);
    $fecha_base = $data['fecha'] ?? date('Y-m-d');
    
    if ($tipo === 'antigua_semanal') {
        // SEMANAL: crear registros DIARIOS, pago cada 7 días
        $pago_semanal = floatval($data['pago_semanal'] ?? 0);
        $semanas = intval($data['semanas_pagar'] ?? 0);
        $total_dias = $semanas * 7;
        $saldo_pendiente = $total;
        
        for ($dia = 1; $dia <= $total_dias; $dia++) {
            $fecha_pago = date('Y-m-d', strtotime($fecha_base . ' +' . ($dia - 1) . ' days'));
            
            // Guardar el saldo ANTES de realizar el pago de este día
            $stmt = $db->prepare("
                INSERT INTO pagos (tarjeta_id, dia, fecha, pago, saldo) 
                VALUES (?, ?, ?, 0, ?)
            ");
            $stmt->execute([$tarjeta_id, $dia, $fecha_pago, max(0, $saldo_pendiente)]);
            
            // El pago se acredita cada 7 días (días: 7, 14, 21, 28...)
            if ($dia % 7 == 0 && $saldo_pendiente > 0) {
                $pago_hoy = min($pago_semanal, $saldo_pendiente);
                $saldo_pendiente -= $pago_hoy;
            }
        }
        
    } elseif ($tipo === 'antigua_diaria') {
        // DIARIA: crear registros DIARIOS, pago cada día (inicia al día siguiente)
        $cuota_diaria = floatval($data['cuota_diaria'] ?? 0);
        $dias = intval($data['dias_pagar'] ?? 0);
        $saldo_pendiente = $total;
        
        for ($dia = 1; $dia <= $dias; $dia++) {
            // Día 1 = fecha_base + 1 día (siguiente día)
            $fecha_pago = date('Y-m-d', strtotime($fecha_base . ' +' . $dia . ' days'));
            
            // Guardar el saldo ANTES de realizar el pago de este día
            $stmt = $db->prepare("
                INSERT INTO pagos (tarjeta_id, dia, fecha, pago, saldo) 
                VALUES (?, ?, ?, 0, ?)
            ");
            $stmt->execute([$tarjeta_id, $dia, $fecha_pago, max(0, $saldo_pendiente)]);
            
            // El pago se acredita cada día
            if ($saldo_pendiente > 0) {
                $pago_hoy = min($cuota_diaria, $saldo_pendiente);
                $saldo_pendiente -= $pago_hoy;
            }
        }
        
    } else { // nueva
        // NUEVA: crear registros de LUNES a SÁBADO (domingo no cuenta)
        $pago_diario = floatval($data['pago'] ?? 0);
        $dias = intval($data['dias_pagar'] ?? 0);
        $saldo_pendiente = $total;
        $dias_calendario = 0;
        
        for ($dia = 1; $dia <= $dias; $dia++) {
            do {
                $dias_calendario++;
                $fecha_pago = date('Y-m-d', strtotime($fecha_base . ' +' . $dias_calendario . ' days'));
                $dia_semana = date('w', strtotime($fecha_pago)); // 0=Domingo, 1=Lunes ... 6=Sábado
            } while ($dia_semana == 0);
            
            // Guardar el saldo ANTES de realizar el pago de este día
            $stmt = $db->prepare("
                INSERT INTO pagos (tarjeta_id, dia, fecha, pago, saldo) 
                VALUES (?, ?, ?, 0, ?)
            ");
            $stmt->execute([$tarjeta_id, $dia, $fecha_pago, max(0, $saldo_pendiente)]);
            
            // El pago se acredita cada día
            if ($saldo_pendiente > 0) {
                $pago_hoy = min($pago_diario, $saldo_pendiente);
                $saldo_pendiente -= $pago_hoy;
            }
        }
    }
}

function obtenerUsuarioPorId($id) {
    $db = getDB();
    $stmt = $db->prepare("SELECT * FROM usuarios WHERE id = ?");
    $stmt->execute([$id]);
    return $stmt->fetch();
}

function agregarUsuario($data) {
    $db = getDB();
    $password = !empty($data['password']) 
        ? password_hash($data['password'], PASSWORD_DEFAULT)
        : password_hash('123456', PASSWORD_DEFAULT);
        
    $stmt = $db->prepare("INSERT INTO usuarios (telefono, password, rol, nombre) VALUES (?, ?, ?, ?)");
    $stmt->execute([
        $data['telefono'],
        $password,
        $data['rol'] ?? 'cliente',
        $data['nombre']
    ]);
    return $db->lastInsertId();
}

function actualizarUsuario($id, $data) {
    $db = getDB();
    $fields = [];
    $params = [];
    
    if (isset($data['telefono'])) {
        $fields[] = 'telefono = ?';
        $params[] = $data['telefono'];
    }
    if (isset($data['nombre'])) {
        $fields[] = 'nombre = ?';
        $params[] = $data['nombre'];
    }
    if (isset($data['rol'])) {
        $fields[] = 'rol = ?';
        $params[] = $data['rol'];
    }
    if (!empty($data['password'])) {
        $fields[] = 'password = ?';
        $params[] = password_hash($data['password'], PASSWORD_DEFAULT);
    }
    
    if (empty($fields)) return false;
    
    $params[] = $id;
    $sql = "UPDATE usuarios SET " . implode(', ', $fields) . " WHERE id = ?";
    $stmt = $db->prepare($sql);
    return $stmt->execute($params);
}

function togglearEstadoUsuario($id) {
    $db = getDB();
    $stmt = $db->prepare("UPDATE usuarios SET activo = NOT activo WHERE id = ?");
    return $stmt->execute([$id]);
}

function obtenerMontoBaseTarjeta($tarjeta) {
    $tipo = $tarjeta['tipo'] ?? 'nueva';

    if ($tipo === 'antigua_semanal') {
        return floatval($tarjeta['pago_semanal'] ?? 0);
    }

    if ($tipo === 'antigua_diaria') {
        return floatval($tarjeta['cuota_diaria'] ?? 0);
    }

    return floatval($tarjeta['pago'] ?? 0);
}

function obtenerPeriodoDesdeDiaTarjeta($tarjeta, $dia) {
    if (($tarjeta['tipo'] ?? '') === 'antigua_semanal') {
        return max(1, (int) ceil(intval($dia) / 7));
    }

    return max(1, intval($dia));
}

function obtenerTotalPeriodosTarjeta($tarjeta) {
    $base = intval($tarjeta['semanas_pagar'] ?: ($tarjeta['dias_pagar'] ?: 0));
    $maxDia = 0;

    if (!empty($tarjeta['pagos'])) {
        foreach ($tarjeta['pagos'] as $pago) {
            $maxDia = max($maxDia, intval($pago['dia'] ?? 0));
        }
    }

    if (($tarjeta['tipo'] ?? '') === 'antigua_semanal') {
        return max($base, $maxDia > 0 ? (int) ceil($maxDia / 7) : 0);
    }

    return max($base, $maxDia);
}

function obtenerMontoProgramadoAcumuladoTarjeta($tarjeta, $periodos) {
    $totalPrestamo = floatval($tarjeta['total_prestamo'] ?? ($tarjeta['valor'] ?? 0));
    $montoBase = obtenerMontoBaseTarjeta($tarjeta);

    if ($periodos <= 0 || $montoBase <= 0) {
        return 0;
    }

    return min($totalPrestamo, $montoBase * $periodos);
}

function obtenerMontoPagadoAcumuladoHastaDiaTarjeta($tarjeta, $diaLimite) {
    $totalPagado = 0;

    if (empty($tarjeta['pagos'])) {
        return 0;
    }

    foreach ($tarjeta['pagos'] as $pago) {
        if (intval($pago['dia'] ?? 0) < intval($diaLimite)) {
            $totalPagado += floatval($pago['pago'] ?? 0);
        }
    }

    return $totalPagado;
}

function obtenerPendienteArrastradoHastaDiaTarjeta($tarjeta, $dia) {
    $periodoActual = obtenerPeriodoDesdeDiaTarjeta($tarjeta, $dia);
    $programadoPrevio = obtenerMontoProgramadoAcumuladoTarjeta($tarjeta, $periodoActual - 1);
    $pagadoPrevio = obtenerMontoPagadoAcumuladoHastaDiaTarjeta($tarjeta, $dia);

    return max(0, $programadoPrevio - $pagadoPrevio);
}

function obtenerMontoProgramadoPeriodoTarjeta($tarjeta, $dia, $saldoAntes = null) {
    $montoBase = obtenerMontoBaseTarjeta($tarjeta);
    $periodoActual = obtenerPeriodoDesdeDiaTarjeta($tarjeta, $dia);
    $programadoPrevio = obtenerMontoProgramadoAcumuladoTarjeta($tarjeta, $periodoActual - 1);
    $totalPrestamo = floatval($tarjeta['total_prestamo'] ?? ($tarjeta['valor'] ?? 0));
    $restantePlan = max(0, $totalPrestamo - $programadoPrevio);
    $programadoDia = $montoBase > 0 ? min($montoBase, $restantePlan) : $restantePlan;

    if ($saldoAntes !== null) {
        $programadoDia = min($programadoDia, max(0, floatval($saldoAntes)));
    }

    return max(0, $programadoDia);
}

function obtenerMontoMaximoPermitidoDiaTarjeta($tarjeta, $dia, $saldoAntes) {
    $db = getDB();
    $tarjeta_id = $tarjeta['id'];
    
    // Obtener monto programado para el día actual
    $montoDia = obtenerMontoProgramadoPeriodoTarjeta($tarjeta, $dia, $saldoAntes);
    
    // Obtener suma de faltantes reales de días previos ya procesados (programado - pagado)
    // En semanal solo cuentan días cobrables (múltiplos de 7)
    $hoy = date('Y-m-d');
    $stmt = $db->prepare("
        SELECT COALESCE(SUM(
            GREATEST(0,
                CASE
                    WHEN t.tipo = 'antigua_semanal' THEN t.pago_semanal
                    WHEN t.tipo = 'antigua_diaria' THEN t.cuota_diaria
                    WHEN t.tipo = 'nueva' THEN t.pago
                    ELSE 0
                END - COALESCE(p.pago, 0)
            )
        ), 0) as atraso_acumulado
        FROM pagos p
        JOIN tarjetas t ON p.tarjeta_id = t.id
                WHERE p.tarjeta_id = ?
                    AND p.dia < ?
                    AND p.fecha < ?
                    AND p.fecha_registro IS NOT NULL
                    AND (t.tipo <> 'antigua_semanal' OR MOD(p.dia, 7) = 0)
    ");
    $stmt->execute([$tarjeta_id, $dia, $hoy]);
    $atraso_acumulado = floatval($stmt->fetchColumn());
    
    // Máximo permitido = monto del día actual + atrasos acumulados
    $maximo = $montoDia + $atraso_acumulado;

    return min(max(0, floatval($saldoAntes)), max(0, $maximo));
}

function calcularSiguienteFechaPagoSegunTipo($tipo, $fechaBase) {
    $timestamp = strtotime($fechaBase);
    if ($timestamp === false) {
        $timestamp = time();
    }

    if ($tipo === 'antigua_semanal') {
        return date('Y-m-d', strtotime('+7 days', $timestamp));
    }

    do {
        $timestamp = strtotime('+1 day', $timestamp);
        $diaSemana = date('w', $timestamp);
    } while ($tipo === 'nueva' && $diaSemana == 0);

    return date('Y-m-d', $timestamp);
}

function agregarDiaExtraPago($tarjeta_id) {
    $db = getDB();
    $tarjeta = obtenerTarjetaPorId($tarjeta_id);

    if (!$tarjeta) {
        return false;
    }

    $stmt = $db->prepare("SELECT * FROM pagos WHERE tarjeta_id = ? ORDER BY dia DESC LIMIT 1");
    $stmt->execute([$tarjeta_id]);
    $ultimoPago = $stmt->fetch();

    if (!$ultimoPago) {
        return false;
    }

    $saldoRestante = max(0, floatval($ultimoPago['saldo'] ?? 0));
    if ($saldoRestante <= 0.009) {
        return false;
    }

    $siguienteDia = ($tarjeta['tipo'] ?? '') === 'antigua_semanal'
        ? intval($ultimoPago['dia']) + 7
        : intval($ultimoPago['dia']) + 1;

    $stmt = $db->prepare("SELECT id FROM pagos WHERE tarjeta_id = ? AND dia = ? LIMIT 1");
    $stmt->execute([$tarjeta_id, $siguienteDia]);
    if ($stmt->fetch()) {
        return false;
    }

    $siguienteFecha = calcularSiguienteFechaPagoSegunTipo($tarjeta['tipo'] ?? 'nueva', $ultimoPago['fecha'] ?? date('Y-m-d'));

    // Insertar día extra SOLO con el saldo restante de la deuda
    $stmt = $db->prepare("
        INSERT INTO pagos (tarjeta_id, dia, fecha, pago, saldo, observacion)
        VALUES (?, ?, ?, 0, ?, ?)
    ");
    $observacion_extra = 'Día extra por saldo pendiente: $' . number_format($saldoRestante, 2);
    $resultado = $stmt->execute([$tarjeta_id, $siguienteDia, $siguienteFecha, $saldoRestante, $observacion_extra]);

    if (!$resultado) {
        return false;
    }

    if (($tarjeta['tipo'] ?? '') === 'antigua_semanal') {
        $db->prepare("UPDATE tarjetas SET semanas_pagar = COALESCE(semanas_pagar, 0) + 1 WHERE id = ?")
            ->execute([$tarjeta_id]);
    } else {
        $db->prepare("UPDATE tarjetas SET dias_pagar = COALESCE(dias_pagar, 0) + 1 WHERE id = ?")
            ->execute([$tarjeta_id]);
    }

    return true;
}

function obtenerMontoProgramadoDia($tarjeta_id, $saldo_antes = 0) {
    $db = getDB();
    $stmt = $db->prepare("SELECT tipo, pago_semanal, cuota_diaria, pago FROM tarjetas WHERE id = ?");
    $stmt->execute([$tarjeta_id]);
    $tarjeta = $stmt->fetch();

    if (!$tarjeta) {
        return max(0, floatval($saldo_antes));
    }

    $tipo = $tarjeta['tipo'] ?? 'nueva';
    if ($tipo === 'antigua_semanal') {
        $programado = floatval($tarjeta['pago_semanal'] ?? 0);
    } elseif ($tipo === 'antigua_diaria') {
        $programado = floatval($tarjeta['cuota_diaria'] ?? 0);
    } else {
        $programado = floatval($tarjeta['pago'] ?? 0);
    }

    if ($programado <= 0) {
        return max(0, floatval($saldo_antes));
    }

    return min($programado, max(0, floatval($saldo_antes)));
}

function registrarPago($tarjeta_id, $dia, $monto, $cobrador_id = null) {
    $db = getDB();
    $fechaRegistro = date('Y-m-d H:i:s');
    
    // Obtener el pago actual
    $stmt = $db->prepare("SELECT * FROM pagos WHERE tarjeta_id = ? AND dia = ?");
    $stmt->execute([$tarjeta_id, $dia]);
    $pago = $stmt->fetch();
    
    if (!$pago) return false;

    $tarjeta = obtenerTarjetaPorId($tarjeta_id);
    if (!$tarjeta) return false;

    // Verificar si el día ya está completamente pagado
    $saldo_actual = floatval($pago['saldo']);
    $pago_actual = floatval($pago['pago']);
    $monto_programado_dia = obtenerMontoProgramadoDia($tarjeta_id, $saldo_actual);
    
    // Si ya tiene el pago completo del día, no permitir más entradas
    if ($pago_actual >= $monto_programado_dia - 0.009 && $pago['fecha_registro'] !== null) {
        return false; // Día ya está completamente pagado
    }

    $hoy = date('Y-m-d');
    $es_pagado_con_retraso = (
        floatval($pago['pago'] ?? 0) <= 0 &&
        !empty($pago['fecha_registro']) &&
        !empty($pago['fecha']) &&
        $pago['fecha'] < $hoy
    );

    // Separar monto del día vs excedente para atrasos
    $saldo_antes = floatval($pago['saldo']);
    $faltante_actual_antes = max(0, $monto_programado_dia - $pago_actual);
    $monto_para_dia = min($monto, $faltante_actual_antes);
    $monto_para_atrasos = max(0, $monto - $monto_para_dia);

    // Calcular el saldo DESPUÉS del pago total (día + atrasos)
    $saldo_despues = max(0, $saldo_antes - $monto);
    $pago_total_dia = floatval($pago['pago']) + $monto_para_dia;
    $faltante_dia = max(0, $monto_programado_dia - $pago_total_dia);

    // Construir observación
    $observaciones = [];
    if ($es_pagado_con_retraso) {
        $observaciones[] = 'pagado con retraso';
    }
    if ($monto <= 0 && $faltante_dia > 0) {
        $observaciones[] = 'Pendiente total del día: $' . number_format($faltante_dia, 2) . ' (no dio nada)';
    }
    if ($monto > 0 && $faltante_dia > 0) {
        $observaciones[] = 'Pendiente del día: $' . number_format($faltante_dia, 2);
    }
    $observacion_nueva = implode(' | ', $observaciones);
    
    // Actualizar el pago actual
    $stmt = $db->prepare("
        UPDATE pagos 
        SET pago = pago + ?, saldo = ?, cobrador_id = ?, fecha_registro = ?, observacion = ?
        WHERE tarjeta_id = ? AND dia = ?
    ");
    $result = $stmt->execute([$monto_para_dia, $saldo_despues, $cobrador_id, $fechaRegistro, $observacion_nueva, $tarjeta_id, $dia]);
    
    if (!$result) return false;
    
    // Si hubo excedente del día, aplicarlo a atrasos anteriores
    if ($monto_para_atrasos > 0.009) {
        distribuirPagoEnAtrasos($tarjeta_id, $dia, $monto_para_atrasos, $cobrador_id, $fechaRegistro);
    }
    
    // Recalcular saldos de días posteriores
    recalcularSaldosPosteriores($tarjeta_id, $dia);
    
    // Verificar si la tarjeta está completada
    verificarTarjetaCompletada($tarjeta_id);
    
    return $result;
}

function distribuirPagoEnAtrasos($tarjeta_id, $dia_actual, $monto_pagado, $cobrador_id = null, $fechaRegistro = null) {
    $db = getDB();
    $tarjeta = obtenerTarjetaPorId($tarjeta_id);
    if (!$tarjeta) return;

    if ($fechaRegistro === null) {
        $fechaRegistro = date('Y-m-d H:i:s');
    }
    
    // Obtener todos los días anteriores
    $stmt = $db->prepare("
        SELECT dia, saldo, pago, observacion, fecha_registro FROM pagos 
        WHERE tarjeta_id = ? AND dia < ? 
        ORDER BY dia ASC
    ");
    $stmt->execute([$tarjeta_id, $dia_actual]);
    $dias_anteriores = $stmt->fetchAll();
    
    if (empty($dias_anteriores)) return;
    
    $restante_distribuir = $monto_pagado;
    
    // Distribuir el pago en orden de días anteriores
    foreach ($dias_anteriores as $dia_ant) {
        if ($restante_distribuir <= 0.009) break;
        
        $dia_num = intval($dia_ant['dia']);
        $es_dia_cobrable_semanal = (($tarjeta['tipo'] ?? '') !== 'antigua_semanal') || (($dia_num % 7) === 0);
        if (!$es_dia_cobrable_semanal) {
            continue;
        }

        $dia_procesado = !empty($dia_ant['fecha_registro']) || floatval($dia_ant['pago'] ?? 0) > 0;
        if (!$dia_procesado) {
            continue;
        }

        $saldo_ant = floatval($dia_ant['saldo']);
        $pago_ant = floatval($dia_ant['pago']);
        
        // Obtener monto programado para ese día
        $monto_base = obtenerMontoProgramadoDia($tarjeta_id, $saldo_ant);
        $faltante_dia_ant = max(0, $monto_base - $pago_ant);
        
        // Si hay faltante en este día, cubrirlo con el pago distribuible
        if ($faltante_dia_ant > 0.009) {
            $a_pagar = min($faltante_dia_ant, $restante_distribuir);

            $observacion_actual = trim((string)($dia_ant['observacion'] ?? ''));
            $nota_abono = ($a_pagar >= ($faltante_dia_ant - 0.009))
                ? ('Completado con abono del día ' . intval($dia_actual))
                : ('Abono del día ' . intval($dia_actual) . ': $' . number_format($a_pagar, 2));
            $observacion_nueva = $observacion_actual === ''
                ? $nota_abono
                : ($observacion_actual . ' | ' . $nota_abono);
            
            $stmt = $db->prepare("
                UPDATE pagos 
                SET pago = pago + ?, observacion = ?, cobrador_id = COALESCE(cobrador_id, ?), fecha_registro = COALESCE(fecha_registro, ?)
                WHERE tarjeta_id = ? AND dia = ?
            ");
            $stmt->execute([$a_pagar, $observacion_nueva, $cobrador_id, $fechaRegistro, $tarjeta_id, $dia_num]);
            
            $restante_distribuir -= $a_pagar;
        }
    }
}


function recalcularSaldosPosteriores($tarjeta_id, $dia_desde) {
    $db = getDB();
    
    // Obtener todos los pagos desde el día siguiente en adelante
    $stmt = $db->prepare("
        SELECT dia, pago, saldo 
        FROM pagos 
        WHERE tarjeta_id = ? AND dia > ? 
        ORDER BY dia ASC
    ");
    $stmt->execute([$tarjeta_id, $dia_desde]);
    $pagos_posteriores = $stmt->fetchAll();
    
    if (empty($pagos_posteriores)) return;
    
    // Obtener el saldo actual del día desde el que recalculamos
    $stmt = $db->prepare("SELECT saldo, pago FROM pagos WHERE tarjeta_id = ? AND dia = ?");
    $stmt->execute([$tarjeta_id, $dia_desde]);
    $pago_base = $stmt->fetch();
    
    if (!$pago_base) return;
    
    // El saldo guardado del día base ya representa lo que quedó después de ese pago
    $saldo_acumulado = floatval($pago_base['saldo']);
    
    // Actualizar cada día posterior
    foreach ($pagos_posteriores as $pago_post) {
        $dia_actual = intval($pago_post['dia']);
        $pago_realizado = floatval($pago_post['pago']);
        
        // El saldo del día es lo que queda antes de pagar ese día
        $stmt = $db->prepare("UPDATE pagos SET saldo = ? WHERE tarjeta_id = ? AND dia = ?");
        $stmt->execute([max(0, $saldo_acumulado), $tarjeta_id, $dia_actual]);
        
        // Restar el pago para el siguiente día
        $saldo_acumulado = max(0, $saldo_acumulado - $pago_realizado);
    }
}

function registrarPagoPendiente($tarjeta_id, $dia, $cobrador_id = null) {
    $db = getDB();
    $fechaRegistro = date('Y-m-d H:i:s');

    $stmt = $db->prepare("SELECT pago, saldo FROM pagos WHERE tarjeta_id = ? AND dia = ?");
    $stmt->execute([$tarjeta_id, $dia]);
    $pago = $stmt->fetch();

    if (!$pago) return false;

    // Si ya fue cobrado, no permitir sobreescribir como pendiente
    if (floatval($pago['pago']) > 0) {
        return false;
    }

    $saldo_antes = floatval($pago['saldo'] ?? 0);
    $monto_programado_dia = obtenerMontoProgramadoDia($tarjeta_id, $saldo_antes);
    $faltante_dia = max(0, $monto_programado_dia - floatval($pago['pago'] ?? 0));
    $observacion = $faltante_dia > 0 ? ('Pendiente del día: $' . number_format($faltante_dia, 2)) : 'Pendiente';

    $stmt = $db->prepare("
        UPDATE pagos
        SET pago = 0, cobrador_id = ?, fecha_registro = ?, observacion = ?
        WHERE tarjeta_id = ? AND dia = ?
    ");

    return $stmt->execute([$cobrador_id, $fechaRegistro, $observacion, $tarjeta_id, $dia]);
}

function actualizarSaldosPosteriores($tarjeta_id, $dia_inicio, $cambio_saldo) {
    $db = getDB();
    $stmt = $db->prepare("
        UPDATE pagos 
        SET saldo = GREATEST(0, saldo + ?)
        WHERE tarjeta_id = ? AND dia > ?
    ");
    return $stmt->execute([$cambio_saldo, $tarjeta_id, $dia_inicio]);
}

function verificarTarjetaCompletada($tarjeta_id) {
    $db = getDB();
    
    // Verificar si el último pago tiene saldo = 0
    $stmt = $db->prepare("
        SELECT saldo, pago 
        FROM pagos 
        WHERE tarjeta_id = ? 
        ORDER BY dia DESC 
        LIMIT 1
    ");
    $stmt->execute([$tarjeta_id]);
    $ultimo_pago = $stmt->fetch();
    
    if ($ultimo_pago) {
        $saldo_final = floatval($ultimo_pago['saldo']);
        
        if ($saldo_final <= 0) {
            $stmt = $db->prepare("
                UPDATE tarjetas 
                SET estado = 'completado', fecha_completada = CURRENT_DATE
                WHERE id = ? AND estado != 'completado'
            ");
            return $stmt->execute([$tarjeta_id]);
        }
    }
    
    return false;
}

