#!/bin/bash

# Script de setup para Render
# Se ejecuta después de construir la imagen Docker

echo "🚀 Iniciando setup para Render..."

# Verificar variables de entorno
if [ -z "$DB_HOST" ]; then
    echo "❌ ERROR: Variable DB_HOST no está configurada"
    exit 1
fi

if [ -z "$DB_NAME" ]; then
    echo "❌ ERROR: Variable DB_NAME no está configurada"
    exit 1
fi

echo "✅ Variables de entorno configuradas correctamente"

# Aquí podrías agregar la inicialización de la base de datos
# pero es mejor hacerlo manualmente desde el shell de Render
# para evitar reinicios accidentales

echo "✅ Setup completado"
echo "💡 No olvides ejecutar setup_database.php desde el shell de Render"
echo "   para inicializar las tablas de la base de datos"

# Iniciar Apache
echo "🌐 Iniciando servidor Apache..."
