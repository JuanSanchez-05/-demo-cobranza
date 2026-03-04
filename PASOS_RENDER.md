# 🚀 PASOS RÁPIDOS PARA DESPLEGAR EN RENDER

## ✅ Código ya subido a GitHub ✓

Tu código ya está en GitHub y listo para desplegar.

---

## 📝 PASOS SIMPLES

### 1️⃣ Crear cuenta en Render
- Ve a: https://render.com
- Regístrate con tu cuenta de GitHub
- Autoriza el acceso a tus repositorios

### 2️⃣ Crear Base de Datos
1. Click en **"New +"** → **"PostgreSQL"**
2. Configurar:
   - Name: `cobranza-db`
   - Database: `cobranza_db`
   - Region: Oregon (US West)
   - Plan: **Free**
3. Click **"Create Database"**
4. **GUARDAR** las credenciales (username, password, host)

### 3️⃣ Crear Web Service
1. Click en **"New +"** → **"Web Service"**
2. Conectar repositorio: `demo-cobranza`
3. Configurar:
   - Name: `demo-cobranza`
   - Region: Oregon (US West)
   - Branch: `main`
   - Runtime: **Docker**
   - Plan: **Free**

### 4️⃣ Configurar Variables de Entorno
En **"Advanced"**, agregar:
```
DB_HOST = [copiar del paso 2]
DB_NAME = cobranza_db
DB_USER = [copiar del paso 2]
DB_PASSWORD = [copiar del paso 2]
DB_PORT = 5432
TIMEZONE = America/Mexico_City
```

### 5️⃣ Desplegar
- Click **"Create Web Service"**
- Esperar 5-10 minutos mientras se despliega

### 6️⃣ Inicializar Base de Datos

**⚠️ IMPORTANTE**: El Shell NO está disponible en el plan gratuito de Render.

**Prueba estas URLs en orden** (después de que el deploy esté "Live"):

**Opción 1: init.php** (más simple):
```
https://tu-app.onrender.com/init.php?setup=yes
```

**Opción 2: setup_web.php** (más detallado):
```
https://tu-app.onrender.com/setup_web.php?key=demo2026
```

**Opción 3: test.php** (verificar que PHP funciona):
```
https://tu-app.onrender.com/test.php
```
Si test.php no funciona, hay un problema con PHP en Render.

**Si ninguna funciona:**
1. Revisa los logs en Render (Dashboard → tu servicio → Logs)
2. Verifica que el deploy terminó exitosamente (estado "Live")
3. Espera 1-2 minutos después del deploy
4. Intenta conectarte desde tu PC con pgAdmin (ver SHELL_ALTERNATIVAS.md)

### 7️⃣ ¡Listo!
Tu app estará en: `https://demo-cobranza.onrender.com`

**Credenciales de acceso:**
- Usuario: `superadmin`
- Contraseña: `admin123`

🔒 **Por seguridad**: Cambia la contraseña después del primer login

---

## 📚 Documentación Completa
Para más detalles, ver: **RENDER_DEPLOY.md**

---

## ⚠️ IMPORTANTE

### Render usa PostgreSQL (no MySQL)
El plan gratuito de Render incluye PostgreSQL, no MySQL. Tu código ya está adaptado para soportar ambos.

Si necesitas MySQL:
- Cuesta ~$7/mes
- En el paso 2, selecciona "MySQL" en lugar de "PostgreSQL"

### Plan Gratuito - Limitaciones
- ⏸️ Se "duerme" después de 15 minutos sin uso
- 🐌 Primera carga después de dormir: 30-60 segundos
- 💾 1GB de almacenamiento en BD
- ✅ Perfecto para demos y desarrollo

---

## 🆘 ¿Problemas?

### No conecta a la base de datos
- Verifica las variables de entorno
- Usa el **Internal Database URL** (no el External)
- Verifica que la BD está en estado "Available"

### Error al desplegar
- Revisa los logs en: Web Service → Logs
- Asegúrate de que todos los archivos están en GitHub

### Login no funciona
- Asegúrate de haber ejecutado el setup web: `https://tu-app.onrender.com/setup_web.php?key=demo2026`
- Verifica que las tablas se crearon correctamente
- Revisa que el usuario super admin existe en la base de datos

---

## 🎯 Checklist Rápido

- [ ] Cuenta de Render creada
- [ ] Base de datos PostgreSQL creada
- [ ] Credenciales guardadas
- [ ] Web Service conectado a GitHub
- [ ] Variables de entorno configuradas
- [ ] Despliegue completado (verde)
- [ ] Base de datos inicializada con setup_web.php
- [ ] Puedes hacer login con superadmin/admin123
- [ ] Contraseña cambiada por seguridad
- [ ] Archivo setup_web.php eliminado (opcional)

---

**Tiempo estimado total**: 15-20 minutos
