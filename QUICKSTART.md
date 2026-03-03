# 🚀 Guía Rápida de Despliegue - 5 Minutos

## Paso 1: Subir a Git (2 min)

```bash
git init
git add .
git commit -m "Sistema Cobranza v1.0 - Producción"
git remote add origin <TU_REPOSITORIO>
git push -u origin main
```

## Paso 2: Configurar Amezmo (2 min)

1. **Crear App** → Conectar repositorio Git
2. **Variables de Entorno** → Añadir:
   - `DB_HOST`: localhost
   - `DB_NAME`: cobranza_db
   - `DB_USER`: [tu usuario]
   - `DB_PASSWORD`: [tu contraseña]
   - `TIMEZONE`: America/Mexico_City

## Paso 3: Base de Datos (1 min)

1. PhpMyAdmin → Importar `database.sql`
2. ✅ Verificar que se crearon 5 tablas

## Paso 4: Login ✅

- URL: `https://tu-dominio.amezmo.com`
- Usuario: `admin123`
- Contraseña: `admin123`

## ⚠️ IMPORTANTE

1. Cambiar contraseña en `super_admin.php`
2. Crear primer trabajador
3. Probar desde móvil

---

**¡Listo! Sistema en producción en 5 minutos** 🎉

Ver detalle completo en: **DEPLOY.md**
