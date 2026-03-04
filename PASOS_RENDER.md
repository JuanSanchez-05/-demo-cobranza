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
Una vez desplegado:
1. Ir a tu Web Service → **"Shell"**
2. Ejecutar: `php setup_database.php`

### 7️⃣ ¡Listo!
Tu app estará en: `https://demo-cobranza.onrender.com`

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
- Ejecuta `php setup_database.php` desde el Shell de Render
- Verifica que las tablas se crearon correctamente

---

## 🎯 Checklist Rápido

- [ ] Cuenta de Render creada
- [ ] Base de datos PostgreSQL creada
- [ ] Credenciales guardadas
- [ ] Web Service conectado a GitHub
- [ ] Variables de entorno configuradas
- [ ] Despliegue completado (verde)
- [ ] Base de datos inicializada
- [ ] Puedes hacer login

---

**Tiempo estimado total**: 15-20 minutos
