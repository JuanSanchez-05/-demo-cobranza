# Diseño Conceptual de Base de Datos

## 📊 Esquema de Base de Datos (MySQL)

Este documento describe el diseño conceptual de la base de datos para el Sistema de Cobranza. **NO está implementado aún**, solo es una referencia para la migración futura.

---

## 🗄️ Tablas Principales

### 1. `usuarios`
Almacena información de todos los usuarios del sistema (administradores, trabajadores, clientes).

```sql
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
```

**Índices:**
- `idx_telefono` en `telefono`
- `idx_rol` en `rol`

---

### 2. `trabajadores`
Información adicional de trabajadores (cobradores).

```sql
CREATE TABLE trabajadores (
    id INT PRIMARY KEY AUTO_INCREMENT,
    usuario_id INT NOT NULL,
    codigo_empleado VARCHAR(20) UNIQUE,
    zona_asignada VARCHAR(100),
    fecha_ingreso DATE,
    FOREIGN KEY (usuario_id) REFERENCES usuarios(id) ON DELETE CASCADE
);
```

---

### 3. `carteras`
Tabla principal que almacena todas las carteras (préstamos).

```sql
CREATE TABLE carteras (
    id INT PRIMARY KEY AUTO_INCREMENT,
    tipo ENUM('antigua_semanal', 'antigua_diaria', 'nueva') NOT NULL,
    trabajador_id INT NOT NULL,
    cliente_telefono VARCHAR(20) NOT NULL,
    
    -- Campos comunes
    fecha DATE NOT NULL,
    lugar VARCHAR(100),
    
    -- Campos específicos según tipo
    nombre VARCHAR(100),
    direccion VARCHAR(255),
    colonia VARCHAR(100),
    telefono VARCHAR(20),
    giro VARCHAR(100),
    direccion_cobranza VARCHAR(255),
    
    -- Datos del aval (solo para carteras nuevas)
    aval_nombre VARCHAR(100),
    aval_direccion VARCHAR(255),
    aval_colonia VARCHAR(100),
    aval_telefono VARCHAR(20),
    
    -- Datos del préstamo
    cantidad_prestamo DECIMAL(10,2),
    cargo_prestamo DECIMAL(10,2),
    total_prestamo DECIMAL(10,2) NOT NULL,
    valor DECIMAL(10,2), -- Para carteras diarias
    
    -- Plan de pago
    pago_semanal DECIMAL(10,2),
    cuota_diaria DECIMAL(10,2),
    pago DECIMAL(10,2), -- Para carteras nuevas
    semanas_pagar INT,
    dias_pagar INT,
    
    -- Información de cobro
    dia_cobro VARCHAR(20),
    hora_cobro TIME,
    
    -- Relaciones
    promotor_id INT,
    
    -- Metadatos
    estado ENUM('activa', 'completada', 'cancelada') DEFAULT 'activa',
    fecha_creacion TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    fecha_actualizacion TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    
    FOREIGN KEY (trabajador_id) REFERENCES trabajadores(id),
    FOREIGN KEY (promotor_id) REFERENCES trabajadores(id),
    INDEX idx_trabajador (trabajador_id),
    INDEX idx_cliente (cliente_telefono),
    INDEX idx_tipo (tipo),
    INDEX idx_estado (estado)
);
```

---

### 4. `pagos`
Registro de todos los pagos realizados.

```sql
CREATE TABLE pagos (
    id INT PRIMARY KEY AUTO_INCREMENT,
    cartera_id INT NOT NULL,
    numero_dia INT NOT NULL,
    fecha DATE NOT NULL,
    monto DECIMAL(10,2) NOT NULL,
    saldo_pendiente DECIMAL(10,2) NOT NULL,
    trabajador_id INT NOT NULL,
    firma_empleado BOOLEAN DEFAULT FALSE,
    observaciones TEXT,
    fecha_registro TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    
    FOREIGN KEY (cartera_id) REFERENCES carteras(id) ON DELETE CASCADE,
    FOREIGN KEY (trabajador_id) REFERENCES trabajadores(id),
    INDEX idx_cartera (cartera_id),
    INDEX idx_fecha (fecha),
    UNIQUE KEY unique_cartera_dia (cartera_id, numero_dia)
);
```

---

### 5. `asignaciones_carteras`
Relación muchos a muchos entre trabajadores y carteras (si un trabajador puede tener múltiples carteras).

```sql
CREATE TABLE asignaciones_carteras (
    id INT PRIMARY KEY AUTO_INCREMENT,
    trabajador_id INT NOT NULL,
    cartera_id INT NOT NULL,
    fecha_asignacion DATE DEFAULT (CURRENT_DATE),
    activa BOOLEAN DEFAULT TRUE,
    
    FOREIGN KEY (trabajador_id) REFERENCES trabajadores(id) ON DELETE CASCADE,
    FOREIGN KEY (cartera_id) REFERENCES carteras(id) ON DELETE CASCADE,
    UNIQUE KEY unique_asignacion (trabajador_id, cartera_id)
);
```

---

## 📈 Vistas Útiles

### Vista: `vista_carteras_completas`
Vista que combina información de carteras con estadísticas de pagos.

```sql
CREATE VIEW vista_carteras_completas AS
SELECT 
    c.*,
    COALESCE(SUM(p.monto), 0) AS total_cobrado,
    (c.total_prestamo - COALESCE(SUM(p.monto), 0)) AS saldo_pendiente,
    COUNT(p.id) AS pagos_realizados,
    CASE 
        WHEN COALESCE(SUM(p.monto), 0) = 0 THEN 0
        ELSE (COALESCE(SUM(p.monto), 0) / c.total_prestamo) * 100
    END AS porcentaje_pagado
FROM carteras c
LEFT JOIN pagos p ON c.id = p.cartera_id
GROUP BY c.id;
```

---

### Vista: `vista_estadisticas_trabajador`
Estadísticas por trabajador.

```sql
CREATE VIEW vista_estadisticas_trabajador AS
SELECT 
    t.id AS trabajador_id,
    u.nombre AS trabajador_nombre,
    COUNT(DISTINCT c.id) AS total_carteras,
    COALESCE(SUM(p.monto), 0) AS total_cobrado,
    COALESCE(SUM(c.total_prestamo), 0) AS total_prestamos,
    (COALESCE(SUM(c.total_prestamo), 0) - COALESCE(SUM(p.monto), 0)) AS saldo_pendiente
FROM trabajadores t
INNER JOIN usuarios u ON t.usuario_id = u.id
LEFT JOIN carteras c ON t.id = c.trabajador_id
LEFT JOIN pagos p ON c.id = p.cartera_id
GROUP BY t.id, u.nombre;
```

---

## 🔍 Consultas Útiles

### Obtener carteras de un trabajador
```sql
SELECT * FROM vista_carteras_completas 
WHERE trabajador_id = ? 
ORDER BY fecha DESC;
```

### Obtener pagos de una cartera
```sql
SELECT * FROM pagos 
WHERE cartera_id = ? 
ORDER BY numero_dia ASC;
```

### Obtener préstamos de un cliente
```sql
SELECT * FROM vista_carteras_completas 
WHERE cliente_telefono = ? 
AND estado = 'activa';
```

### Estadísticas generales
```sql
SELECT 
    COUNT(*) AS total_carteras,
    SUM(total_prestamo) AS total_prestamos,
    SUM(total_cobrado) AS total_cobrado,
    SUM(saldo_pendiente) AS saldo_pendiente
FROM vista_carteras_completas
WHERE estado = 'activa';
```

### Pagos del día
```sql
SELECT 
    c.*,
    p.monto,
    p.fecha
FROM carteras c
INNER JOIN pagos p ON c.id = p.cartera_id
WHERE p.fecha = CURDATE();
```

---

## 🔐 Consideraciones de Seguridad

1. **Contraseñas**: Usar `password_hash()` y `password_verify()` de PHP
2. **Prepared Statements**: Siempre usar prepared statements para prevenir SQL injection
3. **Índices**: Agregar índices en campos de búsqueda frecuente
4. **Backups**: Implementar sistema de backups regulares
5. **Logs**: Registrar acciones importantes (creación, modificación, eliminación)

---

## 📝 Notas de Migración

### Datos Iniciales
Al migrar, se deben insertar los datos de prueba:

```sql
-- Insertar usuarios de prueba
INSERT INTO usuarios (telefono, password, rol, nombre) VALUES
('5550001', '$2y$10$...', 'administrador', 'Juan Administrador'),
('5550002', '$2y$10$...', 'trabajador', 'Carlos Cobrador'),
('5551001', '$2y$10$...', 'cliente', 'Pedro Cliente');
```

### Migración de Datos Simulados
1. Convertir arrays PHP a INSERT statements
2. Mantener IDs originales si es necesario
3. Ajustar fechas y montos según necesidad

---

**Estado**: Diseño conceptual - No implementado  
**Última actualización**: 2024

