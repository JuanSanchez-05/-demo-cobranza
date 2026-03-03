<?php
/**
 * ============================================
 * CONFIGURACIÓN DE BASE DE DATOS - PRODUCCIÓN
 * ============================================
 * 
 * INSTRUCCIONES:
 * 1. Renombrar este archivo a: database.php
 * 2. Completar con las credenciales de Amezmo
 * 3. NO subir database.php a Git (está en .gitignore)
 * 
 * Para usar variables de entorno de Amezmo:
 * - Configurar en: Dashboard > Settings > Environment
 * - Este script las leerá automáticamente
 * ============================================
 */

return [
    // Host de la base de datos
    'host' => getenv('DB_HOST') ?: 'localhost',
    
    // Nombre de la base de datos
    'dbname' => getenv('DB_NAME') ?: 'cobranza_db',
    
    // Usuario de base de datos (proporcionado por Amezmo)
    'username' => getenv('DB_USER') ?: 'root',
    
    // Contraseña (proporcionado por Amezmo)
    'password' => getenv('DB_PASSWORD') ?: '',
    
    // Charset
    'charset' => getenv('DB_CHARSET') ?: 'utf8mb4',
    
    // Opciones PDO
    'options' => [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        PDO::ATTR_EMULATE_PREPARES => false,
    ]
];
