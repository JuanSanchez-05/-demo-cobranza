# ⚠️ Shell No Disponible en Plan Gratuito

## El Problema

El plan gratuito de Render **NO incluye acceso al Shell/Terminal**. Si intentas acceder al Shell, verás un mensaje pidiendo actualizar al plan Starter ($7/mes).

---

## ✅ Soluciones (Sin Pagar)

### 1️⃣ Setup Web - LA MÁS FÁCIL 🎯

He creado una página web que puedes acceder desde tu navegador para inicializar la base de datos:

**Pasos:**
1. Espera a que tu app termine de desplegarse en Render
2. Abre en tu navegador:
   ```
   https://tu-app.onrender.com/setup_web.php?key=demo2026
   ```
3. Haz clic en **"Ejecutar Setup Ahora"**
4. Espera 30-60 segundos mientras se crean las tablas
5. ¡Listo! Verás un mensaje de éxito

**¿Por qué funciona?**
- Es una página PHP que se ejecuta en el servidor de Render
- No necesita Shell/Terminal
- Todo sucede desde tu navegador

**Seguridad:**
- Requiere una clave para acceder (`?key=demo2026`)
- Después de usarlo, elimina el archivo `setup_web.php` del repositorio

---

### 2️⃣ Desde Tu Computadora Local

Si prefieres más control:

**A. Instalar Cliente PostgreSQL:**

**Opción 1: pgAdmin (Interfaz Gráfica)**
- Descargar: https://www.pgadmin.org/download/
- Instalar y abrir pgAdmin
- Crear nueva conexión con las credenciales External de Render
- Ejecutar el SQL manualmente

**Opción 2: DBeaver (Interfaz Gráfica)**
- Descargar: https://dbeaver.io/download/
- Soporta múltiples bases de datos
- Más liviano que pgAdmin

**Opción 3: psql (Terminal)**
```bash
# Instalar PostgreSQL localmente
# Windows: https://www.postgresql.org/download/windows/
# Mac: brew install postgresql
# Linux: sudo apt-get install postgresql-client

# Conectar
psql "postgresql://usuario:password@host:5432/database"

# O usar la URL completa de Render
psql "tu-external-database-url"
```

**B. Ejecutar el SQL:**
1. Copia el contenido de `database/schema.sql`
2. Ejecútalo en tu cliente PostgreSQL
3. Verifica que se crearon las tablas

---

### 3️⃣ Upgrade a Plan Starter

Si necesitas el Shell frecuentemente:

**Plan Starter - $7/mes incluye:**
- ✅ Shell y acceso SSH
- ✅ Sin suspensión (siempre activo)
- ✅ Persistent Disks
- ✅ Más recursos
- ✅ One-off jobs

**Para actualizar:**
1. Ve a tu Web Service en Render
2. Settings → Plan
3. Selecciona "Starter"

---

## 🎯 Recomendación

**Para esta aplicación (demo):**
- ✅ Usa el **Setup Web** - es gratis y funciona perfectamente
- ✅ Toma solo 1 minuto
- ✅ No requiere instalar nada

**Para producción:**
- Si tu app va a crecer, considera el plan Starter
- El Shell es útil para debugging y mantenimiento
- Sin suspensión = mejor experiencia de usuario

---

## 📝 Paso a Paso Completo

### 1. Asegurar que el código está actualizado
El archivo `setup_web.php` ya está en tu repositorio.

### 2. Acceder después del despliegue
Espera a que Render termine de desplegar (status: "Live")

### 3. Ejecutar setup web
```
https://tu-nombre-app.onrender.com/setup_web.php?key=demo2026
```

### 4. Verificar
Intenta hacer login con:
- Usuario: `superadmin`
- Password: `admin123`

### 5. Limpiar (Opcional pero recomendado)
Después de confirmar que todo funciona:
```bash
# En tu computadora
git rm setup_web.php
git commit -m "Remover setup web después de inicialización"
git push origin main
```

---

## 🐛 Problemas Comunes

### "Acceso Denegado"
- Asegúrate de incluir `?key=demo2026` en la URL
- Verifica que el archivo `setup_web.php` existe en el servidor

### "Database connection failed"
- Verifica las variables de entorno en Render
- Asegúrate de que la base de datos esté "Available"
- Usa las credenciales **Internal** (no External)

### "Tabla ya existe"
- Es normal si ejecutas el setup múltiples veces
- Los errores de "already exists" son ignorados automáticamente

### La página no se ve bonita / no carga estilos
- El setup funcionará igual
- Los estilos CSS son opcionales
- Lo importante es que veas los mensajes de éxito

---

## ❓ FAQ

**¿Puedo ejecutar el setup múltiples veces?**
Sí, es seguro. Las tablas existentes no se borran.

**¿Necesito PostgreSQL en mi computadora?**
No si usas el Setup Web. Solo necesitas un navegador.

**¿El setup web es seguro?**
Sí, pero elimínalo después de usarlo para que nadie más pueda acceder.

**¿Puedo cambiar la clave de setup?**
Sí, edita la variable `$SETUP_KEY` en `setup_web.php` antes de subir a Git.

**¿Funciona con MySQL?**
Este setup es para PostgreSQL (plan gratuito). Para MySQL necesitas el plan de pago.

---

## 📞 Ayuda Adicional

Si ninguna opción funciona:
1. Revisa los logs de Render (Dashboard → tu servicio → Logs)
2. Verifica que todas las variables de entorno están configuradas
3. Asegúrate de que la base de datos está en estado "Available"
4. Contacta al soporte de Render: https://render.com/support

---

**Última actualización:** Marzo 2026  
**Compatible con:** Render (plan gratuito)
