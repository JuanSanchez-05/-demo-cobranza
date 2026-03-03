-- ============================================
-- SISTEMA DE COBRANZA - BASE DE DATOS
-- Estructura completa con datos iniciales
-- Fecha: 2026-03-03
-- ============================================

-- Crear base de datos
CREATE DATABASE IF NOT EXISTS demo_cobranza CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE demo_cobranza;

-- ============================================
-- TABLA: usuarios
-- ============================================
CREATE TABLE usuarios (
    id INT PRIMARY KEY AUTO_INCREMENT,
    telefono VARCHAR(20) UNIQUE NOT NULL,
    password VARCHAR(255) NOT NULL,
    rol ENUM('administrador', 'trabajador', 'cliente') NOT NULL,
    nombre VARCHAR(100) NOT NULL,
    activo BOOLEAN DEFAULT TRUE,
    fecha_creacion TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    fecha_actualizacion TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    
    INDEX idx_telefono (telefono),
    INDEX idx_rol (rol)
);

-- ============================================
-- TABLA: trabajadores
-- ============================================
CREATE TABLE trabajadores (
    id INT PRIMARY KEY AUTO_INCREMENT,
    usuario_id INT NOT NULL,
    codigo_empleado VARCHAR(20) UNIQUE,
    zona_asignada VARCHAR(100),
    fecha_ingreso DATE,
    
    FOREIGN KEY (usuario_id) REFERENCES usuarios(id) ON DELETE CASCADE,
    INDEX idx_usuario (usuario_id)
);

-- ============================================
-- TABLA: carteras (tarjetas de préstamo)
-- ============================================
CREATE TABLE carteras (
    id INT PRIMARY KEY AUTO_INCREMENT,
    tipo ENUM('antigua_semanal', 'antigua_diaria', 'nueva') NOT NULL,
    trabajador_id INT NOT NULL,
    
    -- Campos comunes
    fecha DATE NOT NULL,
    lugar VARCHAR(100),
    nombre VARCHAR(100) NOT NULL,
    direccion VARCHAR(255),
    colonia VARCHAR(100),
    telefono VARCHAR(20),
    giro VARCHAR(100),
    
    -- Datos del aval (solo para carteras nuevas)
    aval_nombre VARCHAR(100),
    aval_direccion VARCHAR(255),
    aval_colonia VARCHAR(100),
    aval_telefono VARCHAR(20),
    direccion_cobranza VARCHAR(255),
    
    -- Datos del préstamo
    cantidad_prestamo DECIMAL(10,2),
    cargo_prestamo DECIMAL(10,2), 
    cuota_prestamo DECIMAL(10,2),
    total_prestamo DECIMAL(10,2) NOT NULL,
    valor DECIMAL(10,2), -- Para carteras diarias (alias de total_prestamo)
    
    -- Plan de pago
    pago_semanal DECIMAL(10,2),
    cuota_diaria DECIMAL(10,2),
    pago DECIMAL(10,2), -- Para carteras nuevas (pago unitario)
    semanas_pagar INT,
    dias_pagar INT,
    
    -- Información de cobro
    dia_cobro VARCHAR(20),
    hora_cobro TIME,
    
    -- Relaciones
    promotor_id INT,
    
    -- Estado
    estado ENUM('activa', 'completada', 'cancelada') DEFAULT 'activa',
    fecha_creacion TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    fecha_actualizacion TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    
    FOREIGN KEY (trabajador_id) REFERENCES trabajadores(id),
    FOREIGN KEY (promotor_id) REFERENCES trabajadores(id),
    INDEX idx_trabajador (trabajador_id),
    INDEX idx_tipo (tipo),
    INDEX idx_estado (estado),
    INDEX idx_promotor (promotor_id)
);

-- ============================================
-- TABLA: pagos
-- ============================================
CREATE TABLE pagos (
    id INT PRIMARY KEY AUTO_INCREMENT,
    cartera_id INT NOT NULL,
    numero_dia INT NOT NULL,
    fecha DATE NOT NULL,
    monto DECIMAL(10,2) NOT NULL,
    saldo_pendiente DECIMAL(10,2) NOT NULL,
    trabajador_id INT NOT NULL,
    observaciones TEXT,
    fecha_registro TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    
    FOREIGN KEY (cartera_id) REFERENCES carteras(id) ON DELETE CASCADE,
    FOREIGN KEY (trabajador_id) REFERENCES trabajadores(id),
    INDEX idx_cartera (cartera_id),
    INDEX idx_fecha (fecha),
    INDEX idx_trabajador (trabajador_id),
    UNIQUE KEY unique_cartera_dia (cartera_id, numero_dia)
);

-- ============================================
-- DATOS INICIALES
-- ============================================

-- Insertar usuarios
INSERT INTO usuarios (telefono, password, rol, nombre) VALUES
('5551000', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'administrador', 'Admin Principal'),
('5551001', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'trabajador', 'Carlos Cobrador'), 
('5551002', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'trabajador', 'María Recaudadora'),
('5551003', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'cliente', 'Pedro Cliente'),
('5551004', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'cliente', 'Luis Cliente');

-- Insertar trabajadores
INSERT INTO trabajadores (usuario_id, codigo_empleado, zona_asignada, fecha_ingreso) VALUES
(2, 'COB001', 'Centro/Norte', '2024-01-01'),
(3, 'COB002', 'Sur/Oriente', '2024-01-01');

-- Insertar carteras (tarjetas) con datos para HOY
INSERT INTO carteras (tipo, trabajador_id, fecha, lugar, nombre, direccion, colonia, telefono, 
                     cantidad_prestamo, cargo_prestamo, total_prestamo, pago_semanal, semanas_pagar, 
                     dia_cobro, promotor_id, estado) VALUES

-- Cartera del trabajador Carlos (ID 1)
('antigua_semanal', 1, '2024-01-15', 'Ciudad de México', 'Pedro Cliente', 
 'Calle Principal 123', 'Centro', '5551003', 10000.00, 2000.00, 12000.00, 
 1000.00, 12, 'Lunes', 1, 'activa'),

('antigua_semanal', 1, '2024-01-20', 'Guadalajara', 'Luis Cliente',
 'Av. Reforma 456', 'Zona Norte', '5551004', 15000.00, 3000.00, 18000.00,
 1500.00, 12, 'Miércoles', 1, 'activa'),

-- Nuevas carteras con pagos para HOY (Lunes 2026-03-03)
('antigua_semanal', 1, '2024-03-01', 'Ciudad de México', 'María González',
 'Av. Insurgentes 456', 'Roma Norte', '5551008', 15000.00, 3000.00, 18000.00,
 1500.00, 12, 'Lunes', 1, 'activa'),

('antigua_diaria', 1, '2024-02-15', '', 'Carlos Diario',
 'Calle Diaria 789', '', '5551009', NULL, NULL, 6000.00, 
 NULL, 20, NULL, 1, 'activa'),

('antigua_semanal', 1, '2024-02-20', 'Ciudad de México', 'Ana López',
 'Calle Reforma 321', 'Polanco', '5551010', 8000.00, 1600.00, 9600.00,
 800.00, 12, 'Lunes', 1, 'activa');

-- Actualizar valor para carteras diarias
UPDATE carteras SET valor = total_prestamo WHERE tipo = 'antigua_diaria';
UPDATE carteras SET cuota_diaria = 300.00 WHERE id = 4; -- Carlos Diario

-- ============================================
-- INSERTAR PAGOS (incluyendo pagos para HOY)
-- ============================================

-- Pagos para Pedro Cliente (Cartera 1) - Ya tiene 3 pagos, el 3ro es HOY 
INSERT INTO pagos (cartera_id, numero_dia, fecha, monto, saldo_pendiente, trabajador_id) VALUES
(1, 1, '2024-01-22', 1000.00, 11000.00, 1),
(1, 2, '2024-01-29', 1000.00, 10000.00, 1),
(1, 3, '2026-03-03', 1000.00, 9000.00, 1);

-- Pagos para Luis Cliente (Cartera 2) 
INSERT INTO pagos (cartera_id, numero_dia, fecha, monto, saldo_pendiente, trabajador_id) VALUES
(2, 1, '2024-01-24', 1500.00, 16500.00, 1),
(2, 2, '2024-01-31', 0.00, 16500.00, 1);

-- Pagos para María González (Cartera 3) - Pago HOY pendiente
INSERT INTO pagos (cartera_id, numero_dia, fecha, monto, saldo_pendiente, trabajador_id) VALUES
(3, 1, '2024-03-04', 1500.00, 16500.00, 1),
(3, 2, '2024-03-11', 1500.00, 15000.00, 1),
(3, 3, '2026-03-03', 1500.00, 13500.00, 1);

-- Pagos para Carlos Diario (Cartera 4) - Pago HOY pendiente
INSERT INTO pagos (cartera_id, numero_dia, fecha, monto, saldo_pendiente, trabajador_id) VALUES
(4, 1, '2024-02-16', 300.00, 5700.00, 1),
(4, 2, '2024-02-17', 300.00, 5400.00, 1),
(4, 3, '2026-03-03', 300.00, 5100.00, 1);

-- Pagos para Ana López (Cartera 5) - Pago HOY pendiente  
INSERT INTO pagos (cartera_id, numero_dia, fecha, monto, saldo_pendiente, trabajador_id) VALUES
(5, 1, '2024-02-26', 800.00, 8800.00, 1),
(5, 2, '2026-03-03', 800.00, 8000.00, 1);

-- ============================================
-- VISTAS ÚTILES
-- ============================================

-- Vista para dashboard del trabajador
CREATE VIEW vista_dashboard_trabajador AS
SELECT 
    t.id AS trabajador_id,
    u.nombre AS trabajador_nombre,
    COUNT(c.id) AS total_carteras,
    COUNT(CASE WHEN c.estado = 'completada' THEN 1 END) AS carteras_completadas,
    COALESCE(SUM(CASE WHEN p.fecha = CURDATE() AND p.monto > 0 THEN p.monto END), 0) AS cobrado_hoy,
    COALESCE(SUM(CASE WHEN p.fecha = CURDATE() THEN p.monto END), 0) AS debe_cobrar_hoy,
    COALESCE(SUM(c.total_prestamo - COALESCE(pagado.total_pagado, 0)), 0) AS pendiente_total
FROM trabajadores t
JOIN usuarios u ON t.usuario_id = u.id
LEFT JOIN carteras c ON t.id = c.trabajador_id AND c.estado = 'activa'
LEFT JOIN pagos p ON c.id = p.cartera_id
LEFT JOIN (
    SELECT cartera_id, SUM(monto) AS total_pagado
    FROM pagos 
    WHERE monto > 0
    GROUP BY cartera_id
) pagado ON c.id = pagado.cartera_id
GROUP BY t.id, u.nombre;

-- ============================================
-- PROCEDIMIENTOS ALMACENADOS
-- ============================================

DELIMITER //

-- Registrar un pago
CREATE PROCEDURE RegistrarPago(
    IN p_cartera_id INT,
    IN p_numero_dia INT,
    IN p_trabajador_id INT
)
BEGIN
    DECLARE v_monto DECIMAL(10,2);
    DECLARE v_saldo_anterior DECIMAL(10,2);
    DECLARE v_nuevo_saldo DECIMAL(10,2);
    DECLARE v_total_prestamo DECIMAL(10,2);
    
    -- Obtener información de la cartera
    SELECT 
        CASE 
            WHEN tipo = 'antigua_semanal' THEN pago_semanal
            WHEN tipo = 'antigua_diaria' THEN cuota_diaria  
            WHEN tipo = 'nueva' THEN pago
        END,
        total_prestamo
    INTO v_monto, v_total_prestamo
    FROM carteras 
    WHERE id = p_cartera_id;
    
    -- Calcular saldo anterior
    SELECT COALESCE(saldo_pendiente, v_total_prestamo)
    INTO v_saldo_anterior
    FROM pagos 
    WHERE cartera_id = p_cartera_id 
      AND numero_dia = p_numero_dia - 1
    ORDER BY numero_dia DESC 
    LIMIT 1;
    
    IF v_saldo_anterior IS NULL THEN
        SET v_saldo_anterior = v_total_prestamo;
    END IF;
    
    -- Calcular nuevo saldo
    SET v_nuevo_saldo = v_saldo_anterior - v_monto;
    
    -- Actualizar el pago
    UPDATE pagos 
    SET monto = v_monto,
        saldo_pendiente = v_nuevo_saldo,
        fecha = CURDATE()
    WHERE cartera_id = p_cartera_id 
      AND numero_dia = p_numero_dia;
      
    -- Si el saldo llega a 0, marcar cartera como completada
    IF v_nuevo_saldo <= 0 THEN
        UPDATE carteras 
        SET estado = 'completada' 
        WHERE id = p_cartera_id;
    END IF;
    
END //

DELIMITER ;

-- ============================================
-- ÍNDICES ADICIONALES PARA PERFORMANCE
-- ============================================

-- Índice compuesto para búsquedas frecuentes
CREATE INDEX idx_trabajador_estado ON carteras(trabajador_id, estado);
CREATE INDEX idx_fecha_trabajador ON pagos(fecha, trabajador_id);
CREATE INDEX idx_cartera_dia ON pagos(cartera_id, numero_dia);

-- ============================================
-- COMENTARIOS FINALES
-- ============================================

/*
DATOS DE PRUEBA INCLUIDOS:
- 1 Administrador: Admin Principal (5551000)
- 2 Trabajadores: Carlos Cobrador (5551001), María Recaudadora (5551002) 
- 2 Clientes: Pedro Cliente (5551003), Luis Cliente (5551004)
- 5 Carteras/Tarjetas con diferentes tipos de préstamos
- Pagos programados para HOY (2026-03-03) = $4,600.00 total a cobrar

CREDENCIALES DE ACCESO:
- Todos los passwords son: "password" (hash: $2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi)

PRÓXIMOS PASOS:
1. Ejecutar este script en MySQL
2. Modificar config.php para conectar a MySQL
3. Actualizar funciones para usar consultas SQL
*/