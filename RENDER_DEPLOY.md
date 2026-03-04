# 🚀 Guía de Despliegue en Render

## 📋 Requisitos Previos

- ✅ Cuenta de GitHub con tu repositorio del proyecto
- ✅ Cuenta de Render (gratuita): https://render.com
- ✅ Archivos de configuración incluidos (render.yaml, Dockerfile)

---

## 🔄 Paso 1: Asegurar que tu código está en GitHub

Si aún no has subido tu código, ejecuta:

```bash
git add .
git commit -m "Configuración para Render"
git push origin main
```

Ya hiciste esto, así que puedes continuar al siguiente paso.

---

## 🌐 Paso 2: Crear Cuenta en Render

1. Ve a https://render.com
2. Haz clic en **"Get Started"**
3. Regístrate con tu cuenta de GitHub
4. Autoriza a Render para acceder a tus repositorios

---

## 🗄️ Paso 3: Crear Base de Datos PostgreSQL

1. En el dashboard de Render, haz clic en **"New +"**
2. Selecciona **"PostgreSQL"**
3. Configura:
   - **Name**: `cobranza-db`
   - **Database**: `cobranza_db`
   - **User**: (se genera automáticamente)
   - **Region**: Oregón (US West) - es gratis
   - **Plan**: Free
4. Haz clic en **"Create Database"**
5. ⏳ Espera unos minutos mientras se crea

**IMPORTANTE**: Guarda las credenciales que aparecen:
- Internal Database URL
- External Database URL
- Username
- Password

---

## 🔧 Paso 4: Adaptar Base de Datos de MySQL a PostgreSQL

Render ofrece PostgreSQL gratis, no MySQL. Tienes 2 opciones:

### Opción A: Usar PostgreSQL (Recomendado - GRATIS)
Necesitarás adaptar tu código para que funcione con PostgreSQL.

### Opción B: Usar MySQL (COSTO MENSUAL)
Render cobra por MySQL. Si prefieres MySQL:
- Plan Starter: ~$7/mes
- En el Paso 3, selecciona "MySQL" en lugar de PostgreSQL

---

## 🚀 Paso 5: Desplegar la Aplicación Web

1. En el dashboard de Render, haz clic en **"New +"**
2. Selecciona **"Web Service"**
3. Conecta tu repositorio de GitHub:
   - Busca: `demo-cobranza`
   - Haz clic en **"Connect"**

4. Configura el servicio:
   - **Name**: `demo-cobranza`
   - **Region**: Oregón (US West)
   - **Branch**: `main`
   - **Runtime**: Docker
   - **Plan**: Free

5. **Variables de Entorno**:
   Haz clic en **"Advanced"** y agrega estas variables:

   ```
   DB_HOST = [copiar de la base de datos: Internal Host]
   DB_NAME = cobranza_db
   DB_USER = [copiar de la base de datos]
   DB_PASSWORD = [copiar de la base de datos]
   DB_PORT = 5432
   TIMEZONE = America/Mexico_City
   ```

   💡 **Tip**: En el panel de tu base de datos PostgreSQL puedes copiar estas credenciales.

6. Haz clic en **"Create Web Service"**

7. ⏳ Espera mientras Render:
   - Clona tu repositorio
   - Construye la imagen Docker
   - Despliega la aplicación (5-10 minutos)

---

## 📊 Paso 6: Inicializar la Base de Datos

Una vez desplegada la app:

### Opción 1: Desde el Shell de Render
1. Ve a tu Web Service en Render
2. Haz clic en **"Shell"** (terminal)
3. Ejecuta:
```bash
php setup_database.php
```

### Opción 2: Desde tu computadora local
1. Instala PostgreSQL localmente
2. Conéctate a tu base de datos de Render:
```bash
psql [External Database URL]
```
3. Ejecuta el SQL adaptado para PostgreSQL

---

## ✅ Paso 7: Verificar el Despliegue

1. Tu aplicación estará disponible en:
   ```
   https://demo-cobranza.onrender.com
   ```
   (El nombre dependerá de cómo lo hayas configurado)

2. Accede y verifica:
   - ✅ Página de login funciona
   - ✅ Puedes iniciar sesión con super admin
   - ✅ Las carteras y tarjetas se muestran correctamente

---

## 🔄 Paso 8: Despliegues Automáticos

Render está configurado para desplegar automáticamente cuando hagas push a main:

```bash
git add .
git commit -m "Nuevos cambios"
git push origin main
```

Render detectará el cambio y redesplegarár automáticamente (toma ~5 minutos).

---

## 🐛 Solución de Problemas

### Error: "Database connection failed"
- Verifica que las variables de entorno estén correctas
- Asegúrate de usar el **Internal Database URL** (no el External)
- Verifica que la base de datos está en estado "Available"

### Error: "Application failed to start"
- Revisa los logs en: Web Service → Logs
- Verifica que el Dockerfile está en la raíz del proyecto
- Asegúrate de que todas las dependencias de PHP estén instaladas

### La página no carga / Error 404
- Verifica que `index.php` esté en la raíz del proyecto
- Revisa la configuración de Apache en el Dockerfile
- Asegúrate de que el `.htaccess` existe

### Base de datos vacía / No hay tablas
- Conéctate al shell de Render y ejecuta `setup_database.php`
- O ejecuta el SQL manualmente desde tu computadora

---

## 🆓 Limitaciones del Plan Gratuito

- ⏸️ El servicio se "duerme" después de 15 minutos de inactividad
- 🐌 Primera carga después de dormir: ~30-60 segundos
- 💾 Base de datos: 1GB de almacenamiento
- ⏱️ 750 horas/mes de servicio web (aproximadamente 31 días)
- 🔄 Después de 90 días de inactividad, se elimina automáticamente

---

## 💰 Upgrade a Plan Pago (Opcional)

Si necesitas que tu app esté siempre activa y más rápida:

- **Starter Plan**: $7/mes
  - Sin suspensión automática
  - Más recursos
  - SSL automático

---

## 📝 Checklist de Despliegue

- [ ] Código subido a GitHub
- [ ] Cuenta de Render creada y vinculada a GitHub
- [ ] Base de datos PostgreSQL creada
- [ ] Credenciales de BD guardadas
- [ ] Web Service creado y conectado al repo
- [ ] Variables de entorno configuradas
- [ ] Aplicación desplegada exitosamente
- [ ] Base de datos inicializada con `setup_database.php`
- [ ] Login y funcionalidades básicas verificadas
- [ ] Auto-deploy configurado

---

## 🎉 ¡Listo!

Tu aplicación ya está en producción y accesible desde cualquier lugar del mundo.

**URL de tu aplicación**: https://[tu-nombre].onrender.com

---

## 📞 Soporte

Si encuentras problemas:
1. Revisa los logs en Render → Tu servicio → Logs
2. Consulta la documentación: https://render.com/docs
3. Revisa este archivo de nuevo para verificar que seguiste todos los pasos

---

**Última actualización**: Marzo 2026
