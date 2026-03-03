<?php
require_once __DIR__ . '/../config/config.php';
verificarRol('trabajador');

$action = $_GET['action'] ?? 'dashboard';
$trabajador_id = $_SESSION['usuario_id'];

switch ($action) {
    case 'dashboard':
        $cartera = obtenerCarteraPorTrabajador($trabajador_id);
        $tarjetas = obtenerTarjetasPorTrabajador($trabajador_id);
        include __DIR__ . '/../views/trabajador/dashboard.php';
        break;
        
    case 'carteras':
        $filtro = $_GET['filtro'] ?? 'todas';
        $cartera = obtenerCarteraPorTrabajador($trabajador_id);
        $tarjetas = obtenerTarjetasPorTrabajador($trabajador_id);
        
        // Aplicar filtro a las tarjetas
        if ($filtro === 'cobradas_hoy') {
            $hoy = date('Y-m-d');
            $tarjetas = array_filter($tarjetas, function($t) use ($hoy) {
                if (!isset($t['pagos'])) return false;
                foreach ($t['pagos'] as $pago) {
                    if ($pago['fecha'] === $hoy && $pago['pago'] > 0) {
                        return true;
                    }
                }
                return false;
            });
        } elseif ($filtro === 'no_cobradas_hoy') {
            $hoy = date('Y-m-d');
            $tarjetas = array_filter($tarjetas, function($t) use ($hoy) {
                if (!isset($t['pagos'])) return true;
                foreach ($t['pagos'] as $pago) {
                    if ($pago['fecha'] === $hoy && $pago['pago'] > 0) {
                        return false;
                    }
                }
                return true;
            });
        }
        
        include __DIR__ . '/../views/trabajador/carteras.php';
        break;
        
    case 'detalle_tarjeta':
        $id = $_GET['id'] ?? 0;
        $tarjeta = obtenerTarjetaPorId($id);
        
        // Verificar que la tarjeta pertenece a la cartera del trabajador
        if (!$tarjeta) {
            header('Location: ' . BASE_URL . 'controllers/TrabajadorController.php?action=carteras&error=no_encontrada');
            exit;
        }
        
        // Verificar que la tarjeta está en la cartera del trabajador
        $cartera_trabajador = obtenerCarteraPorTrabajador($trabajador_id);
        $tarjeta_encontrada = false;
        if ($cartera_trabajador && isset($cartera_trabajador['tarjetas'])) {
            foreach ($cartera_trabajador['tarjetas'] as $t) {
                if ($t['id'] == $id) {
                    $tarjeta_encontrada = true;
                    break;
                }
            }
        }
        
        if (!$tarjeta_encontrada) {
            header('Location: ' . BASE_URL . 'controllers/TrabajadorController.php?action=carteras&error=acceso_denegado');
            exit;
        }
        
        // Procesar registro de pago (simulado)
        if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['registrar_pago'])) {
            // En producción se actualizaría en BD
            header('Location: ' . BASE_URL . 'controllers/TrabajadorController.php?action=detalle_tarjeta&id=' . $id . '&mensaje=pago_registrado');
            exit;
        }
        
        include __DIR__ . '/../views/trabajador/detalle_tarjeta.php';
        break;
        
    case 'logout':
        session_destroy();
        header('Location: ' . BASE_URL . 'index.php');
        exit;
        break;
        
    default:
        header('Location: ' . BASE_URL . 'controllers/TrabajadorController.php?action=dashboard');
        break;
}

