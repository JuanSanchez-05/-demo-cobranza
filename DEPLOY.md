# 🚀 Despliegue en Amezmo - Sistema de Cobranza

## 📋 Requisitos Previos

- Cuenta de Amezmo activa
- Repositorio Git configurado
- PHP 8.0 o superior
- MySQL 5.7 o MariaDB 10.3+

## 🔧 Configuración Paso a Paso

### 1. Preparar el Repositorio Git

```bash
cd /ruta/a/demo-cobranza
git init
git add .
git commit -m "Initial commit - Sistema de Cobranza v1.0"
git branch -M main
git remote add origin <TU_REPOSITORIO_GIT>
git push -u origin main
```

### 2. Configurar Amezmo

#### En el Panel de Amezmo:

1. **Crear Nueva Aplicación**
   - Nombre: `sistema-cobranza`
   - Plataforma: PHP
   - Versión PHP: 8.0 o superior

2. **Conectar Repositorio Git**
   - Seleccionar tu repositorio
   - Rama: `main`
   - Auto-deploy: Activado

3. **Configurar Variables de Entorno**
   En Amezmo > Settings > Environment Variables, añadir:
   
   ```
   DB_HOST=localhost
   DB_NAME=cobranza_db
   DB_USER=[usuario_proporcionado_por_amezmo]
   DB_PASSWORD=[contraseña_proporcionada_por_amezmo]
   DB_CHARSET=utf8mb4
   TIMEZONE=America/Mexico_City
   ```

### 3. Configurar Base de Datos

#### Opción A: Desde PhpMyAdmin de Amezmo
1. Acceder a PhpMyAdmin
2. Importar el archivo `database.sql`
3. Verificar que todas las tablas se crearon correctamente

#### Opción B: Desde SSH (si está disponible)
```bash
mysql -u [usuario] -p [base_datos] < database.sql
```

### 4. Configurar Archivo de Conexión

Crear archivo `config/database.php` en producción:

```php
<?php
return [
    'host' => getenv('DB_HOST') ?: 'localhost',
    'dbname' => getenv('DB_NAME') ?: 'cobranza_db',
    'username' => getenv('DB_USER'),
    'password' => getenv('DB_PASSWORD'),
    'charset' => getenv('DB_CHARSET') ?: 'utf8mb4'
];
```

### 5. Configurar .htaccess

El archivo `.htaccess` ya está incluido en el proyecto con:
- Protección de archivos sensibles
- Redirecciones apropiadas
- Headers de seguridad

### 6. Post-Instalación

1. **Acceder a la aplicación**
   ```
   https://tu-dominio-amezmo.com
   ```

2. **Login inicial**
   - Usuario: `admin123`
   - Contraseña: `admin123`

3. **IMPORTANTE: Cambiar contraseña**
   - Acceder a `super_admin.php`
   - Cambiar la contraseña del administrador

4. **Crear primer trabajador**
   - Ir a Panel Admin > Trabajadores
   - Crear nuevo trabajador con teléfono y contraseña

## 📁 Estructura de Archivos para Producción

```
demo-cobranza/
├── .gitignore              # Archivos ignorados por Git
├── .htaccess               # Configuración Apache
├── database.sql            # Script de base de datos
├── index.php               # Punto de entrada
├── README.md               # Documentación
├── DEPLOY.md              # Este archivo
│
├── assets/
│   ├── css/
│   │   └── style.css      # Estilos (v2.7 - Móvil optimizado)
│   └── js/
│       └── main.js        # JavaScript
│
├── config/
│   ├── config.php         # Configuración principal
│   └── database.php       # (Crearlo en producción)
│
├── controllers/
│   ├── AdminController.php
│   ├── ClienteController.php
│   └── TrabajadorController.php
│
├── database/
│   └── schema.sql
│
└── views/
    ├── admin/             # Vistas administrador
    ├── auth/              # Login
    ├── cliente/           # Vistas cliente
    ├── layouts/           # Header/Footer
    └── trabajador/        # Vistas trabajador
```

## 🔐 Seguridad

### Variables Sensibles
NO subir a Git:
- ❌ Contraseñas de base de datos
- ❌ Archivos `config/database.php` con credenciales
- ❌ Archivos `.env` con datos sensibles

Usar variables de entorno de Amezmo para:
- ✅ Credenciales de base de datos
- ✅ Configuraciones específicas del servidor
- ✅ Timezone

### Permisos de Archivos
```bash
chmod 644 *.php
chmod 755 assets/ config/ controllers/ views/
chmod 600 config/database.php
```

## 🔍 Verificación Post-Despliegue

1. **Verificar conexión a base de datos**
   ```
   https://tu-dominio/setup.php
   ```

2. **Verificar login**
   - Acceder con admin123/admin123
   - Debe redirigir al dashboard

3. **Verificar timezone**
   - Dashboard debe mostrar fecha correcta (México)

4. **Verificar responsividad**
   - Probar en móvil
   - Botones deben ser grandes (48x48px mínimo)
   - Zoom debe funcionar

## 🐛 Solución de Problemas

### Error de Conexión a Base de Datos
```php
// Verificar en config/config.php que esté:
date_default_timezone_set('America/Mexico_City');

// Y que las credenciales sean correctas
$db_config = require 'config/database.php';
```

### Error 500
- Revisar logs de PHP en Amezmo
- Verificar permisos de archivos
- Verificar que todas las tablas existan

### Fechas Incorrectas
```php
// Asegurar en config/config.php línea 2:
date_default_timezone_set('America/Mexico_City');
```

### Tarjetas no se crean correctamente
- Verificar que `crearPagosProgramados()` esté en config.php
- Verificar que los tipos sean: 'antigua_semanal', 'antigua_diaria', 'nueva'

## 📱 Optimizaciones Móviles Incluidas

✅ Viewport con zoom habilitado (max-scale: 5.0)
✅ Botones mínimo 48x48px (táctil friendly)
✅ Formularios con font-size: 16px (previene zoom iOS)
✅ Responsive breakpoints: 768px, 576px, 380px
✅ Tablas con scroll horizontal suave
✅ Feedback visual en toques (transform scale)

## 📊 Estado del Sistema

- **Versión**: 1.0.0
- **Fecha**: 2026-03-03
- **Estado**: Producción Ready ✅
- **Base de Datos**: MySQL/MariaDB
- **Timezone**: America/Mexico_City
- **CSS Version**: 2.7 (Mobile Optimized)

## 🚦 Siguientes Pasos

1. Hacer push al repositorio Git
2. Configurar Amezmo para auto-deploy
3. Importar base de datos
4. Configurar variables de entorno
5. Acceder y cambiar contraseña admin
6. Crear usuarios trabajadores
7. Probar flujo completo desde móvil

## 📞 Soporte

Para problemas o preguntas:
- Revisar logs en Amezmo Dashboard
- Verificar configuración de timezone
- Comprobar permisos de base de datos

---

**¡Sistema listo para producción! 🎉**
