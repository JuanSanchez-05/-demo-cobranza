<?php
require_once __DIR__ . '/../config/config.php';
verificarRol('cliente');

$action = $_GET['action'] ?? 'dashboard';
$telefono_cliente = $_SESSION['telefono'];

switch ($action) {
    case 'dashboard':
        $prestamos = obtenerPrestamosPorCliente($telefono_cliente);
        include __DIR__ . '/../views/cliente/dashboard.php';
        break;
        
    case 'prestamos':
        $prestamos = obtenerPrestamosPorCliente($telefono_cliente);
        include __DIR__ . '/../views/cliente/prestamos.php';
        break;
        
    case 'detalle_prestamo':
        $id = $_GET['id'] ?? 0;
        $cartera = obtenerCarteraPorId($id);
        
        // Verificar que el préstamo pertenece al cliente
        if (!$cartera || !isset($cartera['telefono']) || $cartera['telefono'] !== $telefono_cliente) {
            header('Location: ' . BASE_URL . 'controllers/ClienteController.php?action=prestamos&error=acceso_denegado');
            exit;
        }
        
        include __DIR__ . '/../views/cliente/detalle_prestamo.php';
        break;
        
    case 'logout':
        session_destroy();
        header('Location: ' . BASE_URL . 'index.php');
        exit;
        break;
        
    default:
        header('Location: ' . BASE_URL . 'controllers/ClienteController.php?action=dashboard');
        break;
}

