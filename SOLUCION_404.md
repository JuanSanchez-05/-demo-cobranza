# 🔧 Solución: "Not Found" en Render

## ¿Qué hacer si aparece "404 Not Found"?

He creado **3 archivos diferentes** para que pruebes:

### 1️⃣ test.php (Verificar que PHP funciona)
```
https://tu-app.onrender.com/test.php
```
**¿Qué hace?** Solo muestra la información de PHP (phpinfo)

**Si funciona:** PHP está bien configurado ✅  
**Si no funciona:** Hay un problema con Apache/PHP en Render ❌

---

### 2️⃣ init.php (Setup simple)
```
https://tu-app.onrender.com/init.php?setup=yes
```
**¿Qué hace?** Inicializa la base de datos (versión simple)

**Ventajas:**
- ✅ URL más corta
- ✅ Código más simple
- ✅ Menos dependencias

---

### 3️⃣ setup_web.php (Setup completo)
```
https://tu-app.onrender.com/setup_web.php?key=demo2026
```
**¿Qué hace?** Inicializa la base de datos (versión completa con interfaz bonita)

**Ventajas:**
- ✅ Interfaz visual bonita
- ✅ Más información durante el proceso
- ✅ Mensajes de error detallados

---

## 📋 Checklist de Problemas Comunes

### ✅ Paso 1: Verificar que el deploy terminó
1. Ve a tu dashboard de Render
2. Tu web service debe estar en estado **"Live"** (verde)
3. Si está en "Building" o "Deploying", espera a que termine

### ✅ Paso 2: Verificar la URL base
1. En tu dashboard de Render, copia la URL de tu app
2. Ejemplo: `https://demo-cobranza-abcd.onrender.com`
3. Asegúrate de usar ESA URL (no inventes una)

### ✅ Paso 3: Probar test.php primero
```
https://TU-URL-REAL.onrender.com/test.php
```
Si test.php funciona, los otros archivos también deberían funcionar.

### ✅ Paso 4: Revisar los logs
1. Dashboard → Tu servicio → **"Logs"**
2. Busca errores como:
   - "File not found"
   - "Permission denied"
   - "Apache error"

---

## 🐛 Causas Comunes del Error 404

### 1. URL Incorrecta
❌ `https://demo-cobranza.onrender.com/init.php`
✅ `https://demo-cobranza-xyz123.onrender.com/init.php`

Render agrega un sufijo random a tu URL. Usa la URL exacta del dashboard.

### 2. Deploy No Terminó
El deploy puede tomar 5-10 minutos. Si intentas acceder antes, obtendrás 404.

**Solución:** Espera a que el estado sea "Live" (verde)

### 3. Archivos No Se Copiaron
A veces git no sube los archivos nuevos.

**Solución:**
```bash
git add .
git status  # Verificar que los archivos estén agregados
git commit -m "Fix: agregar archivos de setup"
git push origin main
```

### 4. .htaccess Bloqueando
El .htaccess puede estar bloqueando el acceso.

**Solución:** Ya actualicé el .htaccess para permitir estos archivos. Solo necesitas hacer push.

### 5. Render Usa Diferentes DocumentRoot
A veces Render configura Apache diferente.

**Solución:** El Dockerfile ya está configurado para usar `/var/www/html/`

---

## 🔍 Debug Avanzado

### Ver qué archivos hay en el servidor
Revisa los logs de build en Render. Deberías ver:
```
=== Archivos en /var/www/html ===
-rw-r--r-- 1 root root  1234 Mar  4 12:00 init.php
-rw-r--r-- 1 root root  5678 Mar  4 12:00 setup_web.php
-rw-r--r-- 1 root root   123 Mar  4 12:00 test.php
```

Si NO ves estos archivos en los logs, significa que no se están copiando al contenedor.

### Verificar variables de entorno
1. Dashboard → Tu servicio → Environment
2. Debe haber:
   - DB_HOST
   - DB_NAME
   - DB_USER
   - DB_PASSWORD
   - DB_PORT

---

## 💡 Alternativa: Setup Manual

Si **NINGUNO** de los archivos funciona, haz el setup manualmente:

### Método 1: pgAdmin (GUI)
1. Descargar: https://www.pgadmin.org/download/
2. Instalar y abrir
3. Create → Server
4. En "Connection" pegar las credenciales External de Render:
   - Host
   - Port
   - Database
   - Username
   - Password
5. Abrir Query Tool
6. Copiar y pegar el contenido de `database/schema.sql`
7. Ejecutar (F5)

### Método 2: DBeaver (GUI más simple)
1. Descargar: https://dbeaver.io/download/
2. New Connection → PostgreSQL
3. Pegar credenciales External de Render
4. Test Connection
5. SQL Editor → pegar schema.sql
6. Execute (Ctrl+Enter)

### Método 3: Railway (Alternativa a Render)
Si Render está dando muchos problemas, Railway también tiene plan gratuito:
- https://railway.app
- Mejor experiencia de desarrollo
- Incluye Shell en plan gratuito
- Similar a Render pero más estable

---

## 📞 Última Opción

Si todo falla:

1. **Comparte los logs de Render**
   - Dashboard → Logs → copiar todo
   - Envíame los últimos 50 líneas

2. **Comparte la URL exacta**
   - La URL completa que Render te dio

3. **Comparte un screenshot**
   - Del error 404 que ves
   - Del estado del servicio en Render

Con esa información puedo ayudarte mejor.

---

## ✅ Una vez que funcione

Después de ejecutar cualquiera de los setup:

1. **Login:**
   ```
   Usuario: superadmin
   Password: admin123
   ```

2. **Eliminar archivos de setup** (por seguridad):
   ```bash
   git rm init.php setup_web.php test.php
   git commit -m "Remove setup files"
   git push origin main
   ```

3. **Cambiar contraseña** desde el panel de admin

---

**Fecha:** Marzo 2026  
**Problema:** 404 Not Found en archivos PHP de setup  
**Soluciones:** 3 archivos diferentes + setup manual
