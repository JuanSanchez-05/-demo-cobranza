<?php
require_once __DIR__ . '/config/config.php';

// Si ya está autenticado, redirigir según su rol
if (isset($_SESSION['usuario_id'])) {
    $rol = $_SESSION['rol'];
    switch ($rol) {
        case 'administrador':
            header('Location: ' . BASE_URL . 'controllers/AdminController.php?action=dashboard');
            break;
        case 'trabajador':
            header('Location: ' . BASE_URL . 'controllers/TrabajadorController.php?action=dashboard');
            break;
        case 'cliente':
            header('Location: ' . BASE_URL . 'controllers/ClienteController.php?action=dashboard');
            break;
    }
    exit;
}

// Procesar login
$error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['login'])) {
    $telefono = $_POST['telefono'] ?? '';
    $password = $_POST['password'] ?? '';
    
    if (empty($telefono) || empty($password)) {
        $error = 'Por favor completa todos los campos';
    } else {
        $usuario = obtenerUsuarioPorTelefono($telefono);
        
        if ($usuario && password_verify($password, $usuario['password'])) {
            $_SESSION['usuario_id'] = $usuario['id'];
            $_SESSION['rol'] = $usuario['rol'];
            $_SESSION['nombre'] = $usuario['nombre'];
            $_SESSION['telefono'] = $usuario['telefono'];
            
            // Redirigir según rol
            switch ($usuario['rol']) {
                case 'administrador':
                    header('Location: ' . BASE_URL . 'controllers/AdminController.php?action=dashboard');
                    break;
                case 'trabajador':
                    header('Location: ' . BASE_URL . 'controllers/TrabajadorController.php?action=dashboard');
                    break;
                case 'cliente':
                    header('Location: ' . BASE_URL . 'controllers/ClienteController.php?action=dashboard');
                    break;
            }
            exit;
        } else {
            $error = 'Teléfono o contraseña incorrectos';
        }
    }
}

// Mostrar vista de login
include __DIR__ . '/views/auth/login.php';

