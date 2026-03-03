<?php
require_once __DIR__ . '/../config/config.php';
verificarRol('administrador');

$action = $_GET['action'] ?? 'dashboard';

switch ($action) {
    case 'dashboard':
        $stats = calcularEstadisticas();
        $todas_carteras = obtenerTodasLasCarteras();
        $todas_tarjetas = obtenerTodasLasTarjetas();
        include __DIR__ . '/../views/admin/dashboard.php';
        break;
        
    case 'carteras':
        $todas_carteras = obtenerTodasLasCarteras();
        include __DIR__ . '/../views/admin/carteras.php';
        break;
        
    case 'tarjetas':
        $todas_tarjetas = obtenerTodasLasTarjetas();
        include __DIR__ . '/../views/admin/tarjetas.php';
        break;
        
    case 'alta_cartera':
        $tipo = $_GET['tipo'] ?? '';
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            // Simular guardado (en producción se guardaría en BD)
            header('Location: ' . BASE_URL . 'controllers/AdminController.php?action=carteras&mensaje=guardado');
            exit;
        }
        include __DIR__ . '/../views/admin/alta_cartera.php';
        break;
        
    case 'detalle_cartera':
        $id = $_GET['id'] ?? 0;
        $cartera = obtenerCarteraPorId($id);
        if (!$cartera) {
            header('Location: ' . BASE_URL . 'controllers/AdminController.php?action=carteras&error=no_encontrada');
            exit;
        }
        include __DIR__ . '/../views/admin/detalle_cartera.php';
        break;
        
    case 'detalle_tarjeta':
        $id = $_GET['id'] ?? 0;
        $tarjeta = obtenerTarjetaPorId($id);
        if (!$tarjeta) {
            header('Location: ' . BASE_URL . 'controllers/AdminController.php?action=tarjetas&error=no_encontrada');
            exit;
        }
        include __DIR__ . '/../views/admin/detalle_tarjeta.php';
        break;
        
    case 'trabajadores':
        global $usuarios_simulados;
        $trabajadores = array_filter($usuarios_simulados, function($u) {
            return $u['rol'] === 'trabajador';
        });
        include __DIR__ . '/../views/admin/trabajadores.php';
        break;
        
    case 'logout':
        session_destroy();
        header('Location: ' . BASE_URL . 'index.php');
        exit;
        break;
        
    default:
        header('Location: ' . BASE_URL . 'controllers/AdminController.php?action=dashboard');
        break;
}

