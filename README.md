# Sistema de Cobranza - Documentación

## 📋 Descripción General

Sistema web para gestión de cobranza de una financiera, desarrollado con PHP estructurado (estilo MVC ligero), HTML, CSS y JavaScript vanilla. El sistema utiliza **datos simulados** (arrays en PHP) y está diseñado para ser fácilmente migrable a MySQL en el futuro.

## 🏗️ Arquitectura del Sistema

### Estructura de Carpetas

```
demo-cobranza/
├── index.php                 # Punto de entrada principal (login)
├── config/
│   └── config.php           # Configuración y datos simulados
├── controllers/
│   ├── AdminController.php   # Controlador para Administrador
│   ├── TrabajadorController.php  # Controlador para Trabajador
│   └── ClienteController.php # Controlador para Cliente
├── views/
│   ├── auth/
│   │   └── login.php        # Vista de login
│   ├── layouts/
│   │   ├── header.php       # Header común
│   │   └── footer.php       # Footer común
│   ├── admin/               # Vistas del Administrador
│   ├── trabajador/          # Vistas del Trabajador
│   └── cliente/             # Vistas del Cliente
├── assets/
│   ├── css/
│   │   └── style.css        # Estilos principales
│   └── js/
│       └── main.js          # JavaScript principal
└── .htaccess                # Configuración Apache
```

## 👥 Roles del Sistema

### 1. Administrador
- **Acceso completo** al sistema
- Dashboard con estadísticas generales
- Gestión de todas las carteras
- Alta de nuevas carteras (semanal, diaria, nueva)
- Visualización de trabajadores
- Análisis y reportes

### 2. Trabajador (Cobrador)
- Acceso solo a **sus carteras asignadas**
- Dashboard personalizado
- Filtros: Cobradas hoy / No cobradas hoy
- Registro de pagos
- Visualización de progreso

### 3. Cliente
- Acceso solo a **sus propios préstamos**
- Vista simplificada y clara
- Barra de progreso visual
- Historial de pagos

## 🔐 Autenticación

### Usuarios de Prueba

| Rol | Teléfono | Contraseña |
|-----|----------|------------|
| Administrador | 5550001 | admin123 |
| Trabajador | 5550002 | trab123 |
| Cliente | 5551001 | cliente123 |

### Sistema de Login
- Autenticación por **teléfono** y **contraseña**
- Contraseñas simuladas como hasheadas (usando `password_hash`)
- Sesiones PHP para mantener estado
- Redirección automática según rol

## 📊 Tipos de Carteras

### 1. Clientes Antiguos - Semanal
**Campos:**
- Lugar, Fecha, Nombre, Dirección, Colonia, Teléfono
- Cantidad de préstamo, Cargo del préstamo, Total del préstamo
- Pago semanal, Semanas a pagar, Día de cobro
- Promotor (trabajador asignado)

### 2. Clientes Antiguos - Diaria
**Campos:**
- Valor, Cuota diaria, Fecha, Teléfono

### 3. Clientes Nuevos
**Datos del Cliente:**
- Fecha, Lugar, Nombre, Dirección, Colonia, Giro, Teléfono, Dirección de cobranza

**Datos del Aval:**
- Nombre, Dirección, Colonia, Teléfono

**Datos del Préstamo:**
- Préstamo, Cuota del préstamo, Total del préstamo, Pago
- Días a pagar, Día de cobro, Hora de cobro
- Promotor (trabajador asignado)

## 💰 Sistema de Pagos

### Vista de Detalle de Cartera
Todas las carteras muestran una **tabla de pagos** con:
- **Número de día**: Secuencia del pago
- **Fecha**: Fecha del pago
- **Pago realizado**: Monto pagado
- **Saldo pendiente**: Saldo restante después del pago
- **Firma del empleado**: Estado (Firmado/Pendiente)

### Registro de Pagos
- Los trabajadores pueden registrar pagos desde la vista de detalle
- El sistema simula el registro (en producción se guardaría en BD)
- Los pagos se reflejan inmediatamente en el progreso

## 🎨 Diseño UI/UX

### Cliente
- **Diseño extremadamente simple**
- Tipografía grande y legible
- Colores claros y contrastantes
- Barras de progreso visuales prominentes
- Indicadores claros de estado

### Administrador y Trabajador
- **Diseño moderno y profesional**
- Dashboard con cards informativos
- Tablas limpias y organizadas
- Iconos para mejor UX
- Colores profesionales y consistentes

## 🚀 Instalación y Uso

### Requisitos
- PHP 7.4 o superior
- Apache con mod_rewrite habilitado
- XAMPP (recomendado para desarrollo)

### Pasos de Instalación

1. **Copiar el proyecto** a `C:\xampp\htdocs\demo-cobranza\`

2. **Configurar BASE_URL** (si es necesario):
   - Editar `config/config.php`
   - Ajustar la constante `BASE_URL` según tu configuración

3. **Acceder al sistema**:
   - Abrir navegador en: `http://localhost/demo-cobranza/`
   - Usar uno de los usuarios de prueba

### Estructura de URLs

```
http://localhost/demo-cobranza/
├── index.php (login)
├── controllers/AdminController.php?action=dashboard
├── controllers/TrabajadorController.php?action=dashboard
└── controllers/ClienteController.php?action=dashboard
```

## 📝 Funcionalidades por Rol

### Administrador

#### Dashboard
- Estadísticas generales (Total cobrado, Saldo pendiente)
- Monto por trabajador
- Carteras recientes
- Acciones rápidas

#### Gestión de Carteras
- Ver todas las carteras
- Alta de carteras (semanal, diaria, nueva)
- Ver detalle completo de cualquier cartera
- Historial de pagos

#### Trabajadores
- Listado de trabajadores
- Carteras asignadas por trabajador

### Trabajador

#### Dashboard
- Mis carteras asignadas
- Cobrado hoy
- Pendiente total

#### Gestión de Carteras
- Ver solo mis carteras
- Filtros: Todas / Cobradas hoy / No cobradas hoy
- Ver detalle de cartera
- Registrar pagos

### Cliente

#### Dashboard
- Resumen general de préstamos
- Total de préstamos
- Total pagado
- Saldo pendiente
- Barra de progreso general

#### Préstamos
- Listado de mis préstamos
- Detalle de cada préstamo
- Historial de pagos
- Progreso visual

## 🔄 Flujo del Sistema

### Flujo de Login
1. Usuario ingresa teléfono y contraseña
2. Sistema valida credenciales
3. Crea sesión PHP
4. Redirige según rol:
   - Administrador → Dashboard Admin
   - Trabajador → Dashboard Trabajador
   - Cliente → Dashboard Cliente

### Flujo de Trabajador
1. Login → Dashboard personal
2. Ver carteras asignadas
3. Filtrar por estado (cobradas/no cobradas)
4. Seleccionar cartera → Ver detalle
5. Registrar pago → Actualizar progreso

### Flujo de Cliente
1. Login → Dashboard personal
2. Ver resumen de préstamos
3. Seleccionar préstamo → Ver detalle
4. Visualizar progreso y pagos

### Flujo de Administrador
1. Login → Dashboard general
2. Ver estadísticas
3. Gestionar carteras:
   - Ver todas
   - Crear nueva
   - Ver detalle
4. Ver trabajadores

## 📦 Datos Simulados

### Ubicación
Todos los datos simulados están en `config/config.php`:

- `$usuarios_simulados`: Array de usuarios
- `$carteras_semanales`: Carteras semanales
- `$carteras_diarias`: Carteras diarias
- `$carteras_nuevas`: Carteras nuevas

### Funciones de Acceso
- `obtenerUsuarioPorTelefono($telefono)`: Buscar usuario
- `obtenerTodasLasCarteras()`: Todas las carteras
- `obtenerCarterasPorTrabajador($id)`: Carteras de un trabajador
- `obtenerCarteraPorId($id)`: Cartera específica
- `obtenerPrestamosPorCliente($telefono)`: Préstamos de un cliente
- `calcularEstadisticas()`: Estadísticas generales

## 🔮 Migración Futura a MySQL

### Preparación
El sistema está diseñado para facilitar la migración:

1. **Estructura de datos clara**: Los arrays PHP reflejan la estructura de BD
2. **Funciones de acceso**: Fáciles de reemplazar por consultas SQL
3. **Separación MVC**: Lógica separada de presentación

### Pasos Sugeridos
1. Crear esquema de base de datos
2. Reemplazar arrays por consultas SQL
3. Implementar conexión PDO/MySQLi
4. Migrar funciones de `config.php` a modelos
5. Implementar CRUD real

## 🛠️ Tecnologías Utilizadas

- **Backend**: PHP 7.4+ (estructurado, estilo MVC ligero)
- **Frontend**: HTML5, CSS3, JavaScript (vanilla)
- **Servidor**: Apache (XAMPP)
- **Sesiones**: PHP Sessions
- **Seguridad**: password_hash/password_verify

## 📌 Notas Importantes

### ⚠️ Limitaciones Actuales
- **No hay base de datos real**: Todo funciona con arrays PHP
- **No hay persistencia**: Los cambios no se guardan entre sesiones
- **Seguridad básica**: Implementación básica de sesiones
- **Sin validación avanzada**: Validaciones básicas en frontend

### ✅ Características Implementadas
- Sistema de login funcional
- Dashboards por rol
- Visualización de carteras
- Registro de pagos (simulado)
- Filtros y búsquedas
- Diseño responsive
- UI/UX diferenciado por rol

## 🎯 Próximos Pasos Sugeridos

1. **Implementar MySQL**:
   - Crear esquema de base de datos
   - Migrar datos simulados
   - Implementar modelos

2. **Mejorar Seguridad**:
   - CSRF tokens
   - Validación de entrada más robusta
   - Sanitización de datos

3. **Funcionalidades Adicionales**:
   - Exportar reportes (PDF/Excel)
   - Notificaciones
   - Búsqueda avanzada
   - Paginación en tablas

4. **Optimizaciones**:
   - Caché de consultas
   - Optimización de consultas
   - Lazy loading

## 📞 Soporte

Para preguntas o mejoras, revisar la estructura del código y los comentarios en los archivos principales.

---

**Versión**: 1.0.0  
**Fecha**: 2024  
**Estado**: Funcional con datos simulados

