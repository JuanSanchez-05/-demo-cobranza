-- ============================================
-- SISTEMA DE COBRANZA - SCRIPT COMPLETO
-- Creado según las necesidades exactas del sistema
-- ============================================

CREATE DATABASE IF NOT EXISTS cobranza_db CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE cobranza_db;

-- ============================================
-- TABLA: usuarios (con password incluido)
-- ============================================
CREATE TABLE usuarios (
    id INT PRIMARY KEY AUTO_INCREMENT,
    telefono VARCHAR(20) UNIQUE NOT NULL,
    password VARCHAR(255) NOT NULL,
    rol ENUM('administrador', 'trabajador', 'cliente') NOT NULL,
    nombre VARCHAR(100) NOT NULL,
    activo BOOLEAN DEFAULT TRUE,
    fecha_creacion TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    fecha_actualizacion TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

-- ============================================
-- TABLA: trabajadores (información adicional)
-- ============================================
CREATE TABLE trabajadores (
    id INT PRIMARY KEY AUTO_INCREMENT,
    usuario_id INT NOT NULL,
    codigo_empleado VARCHAR(20),
    fecha_ingreso DATE,
    activo BOOLEAN DEFAULT TRUE,
    FOREIGN KEY (usuario_id) REFERENCES usuarios(id) ON DELETE CASCADE,
    UNIQUE KEY (usuario_id)
);

-- ============================================
-- TABLA: carteras (contenedores de tarjetas)
-- ============================================
CREATE TABLE carteras (
    id INT PRIMARY KEY AUTO_INCREMENT,
    trabajador_id INT NOT NULL,
    nombre VARCHAR(150) NOT NULL,
    descripcion TEXT,
    fecha_creacion TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    activo BOOLEAN DEFAULT TRUE,
    FOREIGN KEY (trabajador_id) REFERENCES usuarios(id) ON DELETE CASCADE,
    INDEX idx_trabajador (trabajador_id)
);

-- ============================================
-- TABLA: tarjetas (préstamos/clientes)
-- ============================================
CREATE TABLE tarjetas (
    id INT PRIMARY KEY AUTO_INCREMENT,
    cartera_id INT NOT NULL,
    tipo ENUM('antigua_semanal', 'antigua_diaria', 'nueva') NOT NULL,
    
    -- Información del cliente
    nombre VARCHAR(100) NOT NULL,
    direccion TEXT,
    colonia VARCHAR(100),
    telefono VARCHAR(20),
    lugar VARCHAR(100),
    
    -- Datos del préstamo (antiguos semanales/diarios)
    cantidad_prestamo DECIMAL(10,2) DEFAULT 0,
    cargo_prestamo DECIMAL(10,2) DEFAULT 0,
    total_prestamo DECIMAL(10,2) NOT NULL,
    
    -- Modalidad de pago antiguo semanal
    pago_semanal DECIMAL(10,2) DEFAULT 0,
    semanas_pagar INT DEFAULT 0,
    dia_cobro ENUM('Lunes', 'Martes', 'Miércoles', 'Jueves', 'Viernes', 'Sábado', 'Domingo'),
    
    -- Modalidad de pago antiguo diario
    cuota_diaria DECIMAL(10,2) DEFAULT 0,
    dias_pagar INT DEFAULT 0,
    
    -- Información adicional (para préstamos nuevos)
    giro VARCHAR(100),
    direccion_cobranza TEXT,
    aval_nombre VARCHAR(100),
    aval_direccion TEXT,
    aval_colonia VARCHAR(100),
    aval_telefono VARCHAR(20),
    
    -- Para tipo 'nueva'
    prestamo DECIMAL(10,2) DEFAULT 0,
    cuota_prestamo DECIMAL(10,2) DEFAULT 0,
    pago DECIMAL(10,2) DEFAULT 0,
    hora_cobro TIME,
    
    -- Promotor asignado
    promotor_id INT,
    
    -- Estado y fechas
    estado ENUM('activo', 'completado', 'cancelado') DEFAULT 'activo',
    fecha DATE NOT NULL,
    fecha_completada DATE NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    
    FOREIGN KEY (cartera_id) REFERENCES carteras(id) ON DELETE CASCADE,
    FOREIGN KEY (promotor_id) REFERENCES usuarios(id) ON DELETE SET NULL,
    INDEX idx_cartera (cartera_id),
    INDEX idx_estado (estado),
    INDEX idx_fecha (fecha),
    INDEX idx_promotor (promotor_id)
);

-- ============================================
-- TABLA: pagos (registro de pagos por día)
-- ============================================
CREATE TABLE pagos (
    id INT PRIMARY KEY AUTO_INCREMENT,
    tarjeta_id INT NOT NULL,
    dia INT NOT NULL,
    fecha DATE NOT NULL,
    pago DECIMAL(10,2) DEFAULT 0,
    saldo DECIMAL(10,2) NOT NULL,
    
    -- Registro de cobro
    cobrador_id INT NULL,
    fecha_registro TIMESTAMP NULL,
    
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    
    FOREIGN KEY (tarjeta_id) REFERENCES tarjetas(id) ON DELETE CASCADE,
    FOREIGN KEY (cobrador_id) REFERENCES usuarios(id) ON DELETE SET NULL,
    INDEX idx_tarjeta (tarjeta_id),
    INDEX idx_fecha (fecha),
    INDEX idx_cobrador (cobrador_id),
    UNIQUE KEY unique_tarjeta_dia (tarjeta_id, dia)
);

-- ============================================
-- DATOS INICIALES: Solo administrador
-- ============================================
INSERT INTO usuarios (telefono, password, rol, nombre) VALUES
('admin123', '$2y$10$k5F7bW3zB4G.x1dV8KjQmOeH2cH2hF3zK9x4M7tB5qJ8r0A3sL1nS', 'administrador', 'Administrador Principal');
-- Contraseña por defecto: admin123
-- IMPORTANTE: Si no funciona, usar setup.php para establecer una nueva contraseña

-- ============================================
-- VISTAS ÚTILES PARA EL SISTEMA
-- ============================================

-- Vista para información completa de tarjetas
CREATE VIEW vista_tarjetas_completa AS
SELECT 
    t.*,
    c.nombre AS cartera_nombre,
    c.trabajador_id,
    u.nombre AS trabajador_nombre,
    p.nombre AS promotor_nombre,
    (SELECT COUNT(*) FROM pagos WHERE tarjeta_id = t.id AND pago > 0) AS pagos_realizados,
    (SELECT COUNT(*) FROM pagos WHERE tarjeta_id = t.id) AS total_pagos,
    (SELECT SUM(pago) FROM pagos WHERE tarjeta_id = t.id) AS total_cobrado,
    (SELECT saldo FROM pagos WHERE tarjeta_id = t.id ORDER BY dia DESC LIMIT 1) AS saldo_actual
FROM tarjetas t
JOIN carteras c ON t.cartera_id = c.id
JOIN usuarios u ON c.trabajador_id = u.id
LEFT JOIN usuarios p ON t.promotor_id = p.id
WHERE t.estado = 'activo' AND c.activo = TRUE;

-- Vista para pagos programados para hoy
CREATE VIEW vista_pagos_hoy AS
SELECT 
    p.*,
    t.nombre AS cliente_nombre,
    t.direccion AS cliente_direccion,
    t.tipo AS tipo_prestamo,
    t.pago_semanal,
    t.cuota_diaria,
    t.pago AS pago_nuevo,
    c.trabajador_id,
    u.nombre AS trabajador_nombre
FROM pagos p
JOIN tarjetas t ON p.tarjeta_id = t.id
JOIN carteras c ON t.cartera_id = c.id
JOIN usuarios u ON c.trabajador_id = u.id
WHERE p.fecha = CURDATE()
ORDER BY p.pago ASC, t.nombre;

-- ============================================
-- PROCEDIMIENTOS ALMACENADOS ÚTILES
-- ============================================

DELIMITER $$

-- Procedimiento para registrar un pago
CREATE PROCEDURE RegistrarPago(
    IN p_tarjeta_id INT,
    IN p_dia INT,
    IN p_monto DECIMAL(10,2),
    IN p_cobrador_id INT
)
BEGIN
    DECLARE v_saldo_anterior DECIMAL(10,2);
    DECLARE v_nuevo_saldo DECIMAL(10,2);
    
    -- Obtener saldo anterior
    SELECT saldo INTO v_saldo_anterior 
    FROM pagos 
    WHERE tarjeta_id = p_tarjeta_id AND dia = (p_dia - 1);
    
    -- Si no hay pago anterior, obtener total del préstamo
    IF v_saldo_anterior IS NULL THEN
        SELECT total_prestamo INTO v_saldo_anterior 
        FROM tarjetas 
        WHERE id = p_tarjeta_id;
    END IF;
    
    -- Calcular nuevo saldo
    SET v_nuevo_saldo = v_saldo_anterior - p_monto;
    
    -- Actualizar el pago
    UPDATE pagos 
    SET pago = p_monto,
        saldo = v_nuevo_saldo,
        cobrador_id = p_cobrador_id,
        fecha_registro = NOW()
    WHERE tarjeta_id = p_tarjeta_id AND dia = p_dia;
    
    -- Si el saldo llega a 0, marcar tarjeta como completada
    IF v_nuevo_saldo <= 0 THEN
        UPDATE tarjetas 
        SET estado = 'completado', fecha_completada = CURDATE()
        WHERE id = p_tarjeta_id;
    END IF;
    
END$$

DELIMITER ;

-- ============================================
-- ÍNDICES ADICIONALES PARA RENDIMIENTO
-- ============================================
CREATE INDEX idx_usuarios_telefono ON usuarios(telefono);
CREATE INDEX idx_usuarios_rol ON usuarios(rol);
CREATE INDEX idx_tarjetas_nombre ON tarjetas(nombre);
CREATE INDEX idx_pagos_fecha_pago ON pagos(fecha, pago);

-- ============================================
-- FIN DEL SCRIPT
-- ============================================