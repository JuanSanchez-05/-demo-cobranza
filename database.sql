-- ============================================
-- SISTEMA DE COBRANZA - BASE DE DATOS PRODUCCIÓN
-- Versión: 1.0
-- Fecha: 2026-03-03
-- Compatible con: MySQL 5.7+, MariaDB 10.3+
-- ============================================

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
SET time_zone = "+00:00";

-- ============================================
-- CREAR BASE DE DATOS
-- ============================================
CREATE DATABASE IF NOT EXISTS cobranza_db 
CHARACTER SET utf8mb4 
COLLATE utf8mb4_unicode_ci;

USE cobranza_db;

-- ============================================
-- TABLA: usuarios
-- Almacena todos los usuarios del sistema (admin, trabajadores, clientes)
-- ============================================
CREATE TABLE IF NOT EXISTS usuarios (
    id INT PRIMARY KEY AUTO_INCREMENT,
    telefono VARCHAR(20) UNIQUE NOT NULL,
    password VARCHAR(255) NOT NULL,
    rol ENUM('administrador', 'trabajador', 'cliente') NOT NULL,
    nombre VARCHAR(100) NOT NULL,
    activo BOOLEAN DEFAULT TRUE,
    fecha_creacion TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    fecha_actualizacion TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_telefono (telefono),
    INDEX idx_rol (rol),
    INDEX idx_activo (activo)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================
-- TABLA: trabajadores
-- Información adicional de empleados/trabajadores
-- ============================================
CREATE TABLE IF NOT EXISTS trabajadores (
    id INT PRIMARY KEY AUTO_INCREMENT,
    usuario_id INT NOT NULL,
    codigo_empleado VARCHAR(20),
    fecha_ingreso DATE,
    activo BOOLEAN DEFAULT TRUE,
    FOREIGN KEY (usuario_id) REFERENCES usuarios(id) ON DELETE CASCADE,
    UNIQUE KEY unique_usuario (usuario_id),
    INDEX idx_codigo (codigo_empleado),
    INDEX idx_activo (activo)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================
-- TABLA: carteras
-- Contenedores de tarjetas asignadas a trabajadores
-- ============================================
CREATE TABLE IF NOT EXISTS carteras (
    id INT PRIMARY KEY AUTO_INCREMENT,
    trabajador_id INT NOT NULL,
    nombre VARCHAR(150) NOT NULL,
    descripcion TEXT,
    fecha_creacion TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    activo BOOLEAN DEFAULT TRUE,
    FOREIGN KEY (trabajador_id) REFERENCES usuarios(id) ON DELETE CASCADE,
    INDEX idx_trabajador (trabajador_id),
    INDEX idx_activo (activo)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================
-- TABLA: tarjetas
-- Préstamos individuales (clientes)
-- ============================================
CREATE TABLE IF NOT EXISTS tarjetas (
    id INT PRIMARY KEY AUTO_INCREMENT,
    cartera_id INT NOT NULL,
    tipo ENUM('antigua_semanal', 'antigua_diaria', 'nueva') NOT NULL,
    
    -- Información del cliente
    nombre VARCHAR(100) NOT NULL,
    direccion TEXT,
    colonia VARCHAR(100),
    telefono VARCHAR(20),
    lugar VARCHAR(100),
    
    -- Datos del préstamo (tipos: antigua_semanal, antigua_diaria)
    cantidad_prestamo DECIMAL(10,2) DEFAULT 0,
    cargo_prestamo DECIMAL(10,2) DEFAULT 0,
    total_prestamo DECIMAL(10,2) NOT NULL,
    
    -- Modalidad pago semanal
    pago_semanal DECIMAL(10,2) DEFAULT 0,
    semanas_pagar INT DEFAULT 0,
    dia_cobro ENUM('Lunes', 'Martes', 'Miércoles', 'Jueves', 'Viernes', 'Sábado', 'Domingo'),
    
    -- Modalidad pago diario
    cuota_diaria DECIMAL(10,2) DEFAULT 0,
    dias_pagar INT DEFAULT 0,
    
    -- Información adicional (tipo: nueva)
    giro VARCHAR(100),
    direccion_cobranza TEXT,
    aval_nombre VARCHAR(100),
    aval_direccion TEXT,
    aval_colonia VARCHAR(100),
    aval_telefono VARCHAR(20),
    
    -- Datos tipo nueva
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
    INDEX idx_promotor (promotor_id),
    INDEX idx_nombre (nombre),
    INDEX idx_tipo (tipo)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================
-- TABLA: pagos
-- Registro de pagos programados y realizados
-- ============================================
CREATE TABLE IF NOT EXISTS pagos (
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
    INDEX idx_fecha_pago (fecha, pago),
    UNIQUE KEY unique_tarjeta_dia (tarjeta_id, dia)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================
-- DATOS INICIALES
-- Usuario administrador por defecto
-- ============================================
INSERT INTO usuarios (telefono, password, rol, nombre, activo) VALUES
('admin123', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'administrador', 'Administrador Principal', TRUE)
ON DUPLICATE KEY UPDATE telefono = telefono;

-- Contraseña por defecto: admin123
-- NOTA: Cambiar contraseña después de la primera instalación usando super_admin.php

-- ============================================
-- VISTAS PARA CONSULTAS OPTIMIZADAS
-- ============================================

-- Vista: Información completa de tarjetas activas
CREATE OR REPLACE VIEW vista_tarjetas_completa AS
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

-- Vista: Pagos programados para hoy
CREATE OR REPLACE VIEW vista_pagos_hoy AS
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
-- PROCEDIMIENTOS ALMACENADOS
-- ============================================

DELIMITER $$

-- Procedimiento: Registrar un pago y actualizar saldos
DROP PROCEDURE IF EXISTS RegistrarPago$$
CREATE PROCEDURE RegistrarPago(
    IN p_tarjeta_id INT,
    IN p_dia INT,
    IN p_monto DECIMAL(10,2),
    IN p_cobrador_id INT
)
BEGIN
    DECLARE v_saldo_anterior DECIMAL(10,2);
    DECLARE v_nuevo_saldo DECIMAL(10,2);
    DECLARE v_dia_anterior INT;
    
    -- Calcular día anterior
    SET v_dia_anterior = p_dia - 1;
    
    -- Obtener saldo anterior
    IF v_dia_anterior > 0 THEN
        SELECT saldo INTO v_saldo_anterior 
        FROM pagos 
        WHERE tarjeta_id = p_tarjeta_id AND dia = v_dia_anterior
        LIMIT 1;
    END IF;
    
    -- Si no hay pago anterior, obtener total del préstamo
    IF v_saldo_anterior IS NULL THEN
        SELECT total_prestamo INTO v_saldo_anterior 
        FROM tarjetas 
        WHERE id = p_tarjeta_id;
    END IF;
    
    -- Calcular nuevo saldo
    SET v_nuevo_saldo = v_saldo_anterior - p_monto;
    IF v_nuevo_saldo < 0 THEN
        SET v_nuevo_saldo = 0;
    END IF;
    
    -- Actualizar el pago
    UPDATE pagos 
    SET pago = p_monto,
        saldo = v_nuevo_saldo,
        cobrador_id = p_cobrador_id,
        fecha_registro = NOW()
    WHERE tarjeta_id = p_tarjeta_id AND dia = p_dia;
    
    -- Actualizar saldos de días posteriores
    UPDATE pagos p1
    INNER JOIN pagos p2 ON p1.tarjeta_id = p2.tarjeta_id AND p2.dia = p1.dia - 1
    SET p1.saldo = p2.saldo - p1.pago
    WHERE p1.tarjeta_id = p_tarjeta_id AND p1.dia > p_dia;
    
    -- Si el saldo llega a 0, marcar tarjeta como completada
    IF v_nuevo_saldo <= 0 THEN
        UPDATE tarjetas 
        SET estado = 'completado', 
            fecha_completada = CURDATE()
        WHERE id = p_tarjeta_id;
    END IF;
    
END$$

DELIMITER ;

-- ============================================
-- TRIGGERS PARA INTEGRIDAD DE DATOS
-- ============================================

DELIMITER $$

-- Trigger: Validar que el monto de pago no exceda el saldo
DROP TRIGGER IF EXISTS before_pago_update$$
CREATE TRIGGER before_pago_update
BEFORE UPDATE ON pagos
FOR EACH ROW
BEGIN
    DECLARE v_saldo_anterior DECIMAL(10,2);
    DECLARE v_dia_anterior INT;
    
    -- Solo validar si se está registrando un pago nuevo
    IF NEW.pago > 0 AND OLD.pago = 0 THEN
        SET v_dia_anterior = NEW.dia - 1;
        
        -- Obtener saldo anterior
        IF v_dia_anterior > 0 THEN
            SELECT saldo INTO v_saldo_anterior 
            FROM pagos 
            WHERE tarjeta_id = NEW.tarjeta_id AND dia = v_dia_anterior
            LIMIT 1;
        ELSE
            SELECT total_prestamo INTO v_saldo_anterior 
            FROM tarjetas 
            WHERE id = NEW.tarjeta_id;
        END IF;
        
        -- Validar que el pago no exceda el saldo
        IF NEW.pago > v_saldo_anterior THEN
            SIGNAL SQLSTATE '45000'
            SET MESSAGE_TEXT = 'El monto del pago no puede exceder el saldo pendiente';
        END IF;
    END IF;
END$$

DELIMITER ;

-- ============================================
-- PERMISOS Y SEGURIDAD
-- ============================================
-- NOTA: Configurar permisos de usuario según las políticas de Amezmo

-- ============================================
-- INFORMACIÓN DE VERSIÓN
-- ============================================
CREATE TABLE IF NOT EXISTS schema_version (
    version VARCHAR(20) PRIMARY KEY,
    installed_on TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    description TEXT
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT INTO schema_version (version, description) VALUES
('1.0.0', 'Versión inicial de producción - Sistema de Cobranza')
ON DUPLICATE KEY UPDATE version = version;

-- ============================================
-- FIN DEL SCRIPT
-- ============================================
-- Este script está listo para ser ejecutado en producción
-- Asegúrate de tener backups antes de ejecutar en producción
-- ============================================
