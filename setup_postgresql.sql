-- ============================================
-- SISTEMA DE COBRANZA - BASE DE DATOS COMPLETA (PostgreSQL)
-- Conversión 1:1 del esquema MySQL de producción (mismos campos)
-- Uso recomendado: reinstalación completa en Render
-- ============================================

BEGIN;

-- ============================================
-- REINSTALACIÓN LIMPIA (BORRAR TODO Y RECREAR)
-- ============================================
DROP SCHEMA IF EXISTS public CASCADE;
CREATE SCHEMA public;
SET search_path TO public;

-- ============================================
-- FUNCIÓN PARA updated_at / fecha_actualizacion
-- ============================================
CREATE OR REPLACE FUNCTION set_timestamp()
RETURNS TRIGGER AS $$
BEGIN
  NEW.updated_at = CURRENT_TIMESTAMP;
  RETURN NEW;
END;
$$ LANGUAGE plpgsql;

CREATE OR REPLACE FUNCTION set_fecha_actualizacion()
RETURNS TRIGGER AS $$
BEGIN
  NEW.fecha_actualizacion = CURRENT_TIMESTAMP;
  RETURN NEW;
END;
$$ LANGUAGE plpgsql;

-- ============================================
-- TABLA: usuarios
-- ============================================
CREATE TABLE usuarios (
    id BIGSERIAL PRIMARY KEY,
    telefono VARCHAR(20) UNIQUE NOT NULL,
    password VARCHAR(255) NOT NULL,
    rol VARCHAR(20) NOT NULL CHECK (rol IN ('administrador', 'trabajador', 'cliente')),
    nombre VARCHAR(100) NOT NULL,
    activo BOOLEAN DEFAULT TRUE,
    fecha_creacion TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    fecha_actualizacion TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE INDEX idx_telefono ON usuarios(telefono);
CREATE INDEX idx_rol ON usuarios(rol);
CREATE INDEX idx_activo ON usuarios(activo);

CREATE TRIGGER trg_usuarios_fecha_actualizacion
BEFORE UPDATE ON usuarios
FOR EACH ROW EXECUTE FUNCTION set_fecha_actualizacion();

-- ============================================
-- TABLA: trabajadores
-- ============================================
CREATE TABLE trabajadores (
    id BIGSERIAL PRIMARY KEY,
    usuario_id BIGINT NOT NULL,
    codigo_empleado VARCHAR(20),
    fecha_ingreso DATE,
    activo BOOLEAN DEFAULT TRUE,
    CONSTRAINT fk_trabajadores_usuario FOREIGN KEY (usuario_id) REFERENCES usuarios(id) ON DELETE CASCADE,
    CONSTRAINT unique_usuario UNIQUE (usuario_id)
);

CREATE INDEX idx_codigo ON trabajadores(codigo_empleado);
CREATE INDEX idx_trabajadores_activo ON trabajadores(activo);

-- ============================================
-- TABLA: carteras
-- ============================================
CREATE TABLE carteras (
    id BIGSERIAL PRIMARY KEY,
    trabajador_id BIGINT NOT NULL,
    nombre VARCHAR(150) NOT NULL,
    descripcion TEXT,
    fecha_creacion TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    activo BOOLEAN DEFAULT TRUE,
    CONSTRAINT fk_carteras_trabajador FOREIGN KEY (trabajador_id) REFERENCES usuarios(id) ON DELETE CASCADE
);

CREATE INDEX idx_trabajador ON carteras(trabajador_id);
CREATE INDEX idx_carteras_activo ON carteras(activo);

-- ============================================
-- TABLA: tarjetas
-- ============================================
CREATE TABLE tarjetas (
    id BIGSERIAL PRIMARY KEY,
    cartera_id BIGINT NOT NULL,
    tipo VARCHAR(20) NOT NULL CHECK (tipo IN ('antigua_semanal', 'antigua_diaria', 'nueva')),

    nombre VARCHAR(100) NOT NULL,
    direccion TEXT,
    colonia VARCHAR(100),
    telefono VARCHAR(20),
    lugar VARCHAR(100),

    cantidad_prestamo NUMERIC(10,2) DEFAULT 0,
    cargo_prestamo NUMERIC(10,2) DEFAULT 0,
    total_prestamo NUMERIC(10,2) NOT NULL,

    pago_semanal NUMERIC(10,2) DEFAULT 0,
    semanas_pagar INT DEFAULT 0,
    dia_cobro VARCHAR(20) CHECK (dia_cobro IN ('Lunes', 'Martes', 'Miércoles', 'Jueves', 'Viernes', 'Sábado', 'Domingo')),

    cuota_diaria NUMERIC(10,2) DEFAULT 0,
    dias_pagar INT DEFAULT 0,

    giro VARCHAR(100),
    direccion_cobranza TEXT,
    aval_nombre VARCHAR(100),
    aval_direccion TEXT,
    aval_colonia VARCHAR(100),
    aval_telefono VARCHAR(20),

    prestamo NUMERIC(10,2) DEFAULT 0,
    cuota_prestamo NUMERIC(10,2) DEFAULT 0,
    pago NUMERIC(10,2) DEFAULT 0,
    hora_cobro TIME,

    promotor_id BIGINT,

    estado VARCHAR(20) DEFAULT 'activo' CHECK (estado IN ('activo', 'completado', 'cancelado')),
    fecha DATE NOT NULL,
    fecha_completada DATE NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,

    CONSTRAINT fk_tarjetas_cartera FOREIGN KEY (cartera_id) REFERENCES carteras(id) ON DELETE CASCADE,
    CONSTRAINT fk_tarjetas_promotor FOREIGN KEY (promotor_id) REFERENCES usuarios(id) ON DELETE SET NULL
);

CREATE INDEX idx_cartera ON tarjetas(cartera_id);
CREATE INDEX idx_tarjetas_estado ON tarjetas(estado);
CREATE INDEX idx_fecha ON tarjetas(fecha);
CREATE INDEX idx_promotor ON tarjetas(promotor_id);
CREATE INDEX idx_nombre ON tarjetas(nombre);
CREATE INDEX idx_tipo ON tarjetas(tipo);

CREATE TRIGGER trg_tarjetas_updated_at
BEFORE UPDATE ON tarjetas
FOR EACH ROW EXECUTE FUNCTION set_timestamp();

-- ============================================
-- TABLA: pagos
-- ============================================
CREATE TABLE pagos (
    id BIGSERIAL PRIMARY KEY,
    tarjeta_id BIGINT NOT NULL,
    dia INT NOT NULL,
    fecha DATE NOT NULL,
    pago NUMERIC(10,2) DEFAULT 0,
    saldo NUMERIC(10,2) NOT NULL,
    observacion TEXT NULL,

    cobrador_id BIGINT NULL,
    fecha_registro TIMESTAMP NULL,

    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,

    CONSTRAINT fk_pagos_tarjeta FOREIGN KEY (tarjeta_id) REFERENCES tarjetas(id) ON DELETE CASCADE,
    CONSTRAINT fk_pagos_cobrador FOREIGN KEY (cobrador_id) REFERENCES usuarios(id) ON DELETE SET NULL,
    CONSTRAINT unique_tarjeta_dia UNIQUE (tarjeta_id, dia)
);

CREATE INDEX idx_tarjeta ON pagos(tarjeta_id);
CREATE INDEX idx_pagos_fecha ON pagos(fecha);
CREATE INDEX idx_cobrador ON pagos(cobrador_id);
CREATE INDEX idx_fecha_pago ON pagos(fecha, pago);

CREATE TRIGGER trg_pagos_updated_at
BEFORE UPDATE ON pagos
FOR EACH ROW EXECUTE FUNCTION set_timestamp();

-- ============================================
-- DATOS INICIALES
-- ============================================
INSERT INTO usuarios (telefono, password, rol, nombre, activo) VALUES
('admin123', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'administrador', 'Administrador Principal', TRUE)
ON CONFLICT (telefono) DO NOTHING;

-- Usuario alterno compatible con guía anterior
INSERT INTO usuarios (telefono, password, rol, nombre, activo) VALUES
('superadmin', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'administrador', 'Super Administrador', TRUE)
ON CONFLICT (telefono) DO NOTHING;

-- ============================================
-- VISTAS (equivalentes a MySQL)
-- ============================================
CREATE OR REPLACE VIEW vista_tarjetas_completa AS
SELECT 
    t.*,
    c.nombre AS cartera_nombre,
    c.trabajador_id,
    u.nombre AS trabajador_nombre,
    p.nombre AS promotor_nombre,
    (SELECT COUNT(*) FROM pagos WHERE tarjeta_id = t.id AND pago > 0) AS pagos_realizados,
    (SELECT COUNT(*) FROM pagos WHERE tarjeta_id = t.id) AS total_pagos,
    (SELECT COALESCE(SUM(pago), 0) FROM pagos WHERE tarjeta_id = t.id) AS total_cobrado,
    (SELECT saldo FROM pagos WHERE tarjeta_id = t.id ORDER BY dia DESC LIMIT 1) AS saldo_actual
FROM tarjetas t
JOIN carteras c ON t.cartera_id = c.id
JOIN usuarios u ON c.trabajador_id = u.id
LEFT JOIN usuarios p ON t.promotor_id = p.id
WHERE t.estado = 'activo' AND c.activo = TRUE;

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
WHERE p.fecha = CURRENT_DATE
ORDER BY p.pago ASC, t.nombre;

-- ============================================
-- FUNCIÓN: RegistrarPago (equivalente al SP MySQL)
-- ============================================
CREATE OR REPLACE FUNCTION registrar_pago(
    p_tarjeta_id BIGINT,
    p_dia INT,
    p_monto NUMERIC(10,2),
    p_cobrador_id BIGINT
)
RETURNS VOID AS $$
DECLARE
    v_saldo_anterior NUMERIC(10,2);
    v_nuevo_saldo NUMERIC(10,2);
    v_dia_anterior INT;
BEGIN
    v_dia_anterior := p_dia - 1;

    IF v_dia_anterior > 0 THEN
        SELECT saldo INTO v_saldo_anterior
        FROM pagos
        WHERE tarjeta_id = p_tarjeta_id AND dia = v_dia_anterior
        LIMIT 1;
    END IF;

    IF v_saldo_anterior IS NULL THEN
        SELECT total_prestamo INTO v_saldo_anterior
        FROM tarjetas
        WHERE id = p_tarjeta_id;
    END IF;

    v_nuevo_saldo := v_saldo_anterior - p_monto;
    IF v_nuevo_saldo < 0 THEN
        v_nuevo_saldo := 0;
    END IF;

    UPDATE pagos
    SET pago = p_monto,
        saldo = v_nuevo_saldo,
        cobrador_id = p_cobrador_id,
        fecha_registro = CURRENT_TIMESTAMP,
        updated_at = CURRENT_TIMESTAMP
    WHERE tarjeta_id = p_tarjeta_id AND dia = p_dia;

    IF v_nuevo_saldo = 0 THEN
        UPDATE tarjetas
        SET estado = 'completado',
            fecha_completada = CURRENT_DATE,
            updated_at = CURRENT_TIMESTAMP
        WHERE id = p_tarjeta_id;
    END IF;
END;
$$ LANGUAGE plpgsql;

COMMIT;

-- ============================================
-- CREDENCIALES INICIALES
-- ============================================
-- Telefono/usuario: admin123    Password: password
-- Telefono/usuario: superadmin  Password: password
-- (el hash cargado corresponde a "password")
