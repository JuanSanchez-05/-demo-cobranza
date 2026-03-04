-- ============================================
-- SISTEMA DE COBRANZA - PostgreSQL
-- Setup manual para Render
-- ============================================

-- TABLA: usuarios
CREATE TABLE IF NOT EXISTS usuarios (
    id SERIAL PRIMARY KEY,
    telefono VARCHAR(20) UNIQUE NOT NULL,
    password VARCHAR(255) NOT NULL,
    nombre VARCHAR(100) NOT NULL,
    rol VARCHAR(20) NOT NULL CHECK (rol IN ('administrador', 'trabajador', 'cliente')),
    activo BOOLEAN DEFAULT TRUE,
    fecha_creacion TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    fecha_actualizacion TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- TABLA: carteras
CREATE TABLE IF NOT EXISTS carteras (
    id SERIAL PRIMARY KEY,
    nombre VARCHAR(100) NOT NULL,
    descripcion TEXT,
    trabajador_id INTEGER REFERENCES usuarios(id) ON DELETE SET NULL,
    total_tarjetas INTEGER DEFAULT 0,
    monto_total DECIMAL(10,2) DEFAULT 0,
    fecha_creacion TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    activa BOOLEAN DEFAULT TRUE
);

-- TABLA: tarjetas
CREATE TABLE IF NOT EXISTS tarjetas (
    id SERIAL PRIMARY KEY,
    cartera_id INTEGER NOT NULL REFERENCES carteras(id) ON DELETE CASCADE,
    numero_tarjeta VARCHAR(50) UNIQUE NOT NULL,
    nombre_cliente VARCHAR(100) NOT NULL,
    telefono_cliente VARCHAR(20),
    direccion_cliente TEXT,
    monto_prestado DECIMAL(10,2) NOT NULL,
    tasa_interes DECIMAL(5,2) DEFAULT 20.00,
    monto_total DECIMAL(10,2) NOT NULL,
    fecha_prestamo DATE NOT NULL,
    fecha_vencimiento DATE,
    estado VARCHAR(20) DEFAULT 'activa' CHECK (estado IN ('activa', 'pagada', 'vencida', 'cancelada')),
    saldo_pendiente DECIMAL(10,2) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- TABLA: clientes
CREATE TABLE IF NOT EXISTS clientes (
    id SERIAL PRIMARY KEY,
    usuario_id INTEGER UNIQUE REFERENCES usuarios(id) ON DELETE CASCADE,
    nombre_completo VARCHAR(100) NOT NULL,
    telefono VARCHAR(20),
    direccion TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- TABLA: prestamos
CREATE TABLE IF NOT EXISTS prestamos (
    id SERIAL PRIMARY KEY,
    tarjeta_id INTEGER NOT NULL REFERENCES tarjetas(id) ON DELETE CASCADE,
    cliente_id INTEGER REFERENCES clientes(id) ON DELETE SET NULL,
    monto_prestado DECIMAL(10,2) NOT NULL,
    tasa_interes DECIMAL(5,2) DEFAULT 20.00,
    monto_total DECIMAL(10,2) NOT NULL,
    fecha_prestamo DATE NOT NULL,
    fecha_vencimiento DATE,
    estado VARCHAR(20) DEFAULT 'activo',
    saldo_pendiente DECIMAL(10,2) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- TABLA: pagos
CREATE TABLE IF NOT EXISTS pagos (
    id SERIAL PRIMARY KEY,
    prestamo_id INTEGER NOT NULL REFERENCES prestamos(id) ON DELETE CASCADE,
    tarjeta_id INTEGER REFERENCES tarjetas(id) ON DELETE CASCADE,
    monto DECIMAL(10,2) NOT NULL,
    fecha_pago TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    metodo_pago VARCHAR(50) DEFAULT 'efectivo',
    notas TEXT,
    registrado_por INTEGER REFERENCES usuarios(id) ON DELETE SET NULL
);

-- TABLA: cobros (para trabajadores)
CREATE TABLE IF NOT EXISTS cobros (
    id SERIAL PRIMARY KEY,
    tarjeta_id INTEGER NOT NULL REFERENCES tarjetas(id) ON DELETE CASCADE,
    trabajador_id INTEGER NOT NULL REFERENCES usuarios(id) ON DELETE CASCADE,
    monto_cobrado DECIMAL(10,2) NOT NULL,
    fecha_cobro TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    notas TEXT
);

-- TABLA: actividad (log de acciones)
CREATE TABLE IF NOT EXISTS actividad (
    id SERIAL PRIMARY KEY,
    usuario_id INTEGER REFERENCES usuarios(id) ON DELETE SET NULL,
    tipo_accion VARCHAR(50) NOT NULL,
    descripcion TEXT,
    fecha TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- ============================================
-- CREAR USUARIO SUPER ADMIN
-- ============================================
INSERT INTO usuarios (telefono, password, nombre, rol, activo, fecha_creacion)
VALUES (
    'superadmin',
    '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', -- password: admin123
    'Super Administrador',
    'administrador',
    TRUE,
    CURRENT_TIMESTAMP
)
ON CONFLICT (telefono) DO NOTHING;

-- ============================================
-- ÍNDICES PARA MEJOR RENDIMIENTO
-- ============================================
CREATE INDEX IF NOT EXISTS idx_carteras_trabajador ON carteras(trabajador_id);
CREATE INDEX IF NOT EXISTS idx_tarjetas_cartera ON tarjetas(cartera_id);
CREATE INDEX IF NOT EXISTS idx_tarjetas_estado ON tarjetas(estado);
CREATE INDEX IF NOT EXISTS idx_prestamos_tarjeta ON prestamos(tarjeta_id);
CREATE INDEX IF NOT EXISTS idx_prestamos_cliente ON prestamos(cliente_id);
CREATE INDEX IF NOT EXISTS idx_pagos_prestamo ON pagos(prestamo_id);
CREATE INDEX IF NOT EXISTS idx_cobros_tarjeta ON cobros(tarjeta_id);
CREATE INDEX IF NOT EXISTS idx_cobros_trabajador ON cobros(trabajador_id);

-- ============================================
-- FIN DEL SCRIPT
-- ============================================
