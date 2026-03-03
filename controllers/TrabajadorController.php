<?php
require_once __DIR__ . '/../config/config.php';
verificarRol('trabajador');

$action = $_GET['action'] ?? 'dashboard';
$trabajador_id = $_SESSION['usuario_id'];

switch ($action) {
    case 'dashboard':
        // Obtener estadísticas del trabajador desde la base de datos
        $estadisticas = obtenerEstadisticasTrabajador($trabajador_id);
        $cobros_hoy = obtenerCobrosHoy($trabajador_id);
        
        // Para compatibilidad con el código existente
        $cartera = obtenerCarteraPorTrabajador($trabajador_id);
        $tarjetas = obtenerTarjetasPorTrabajador($trabajador_id);
        $completadas = isset($cartera['completadas']) ? $cartera['completadas'] : [];
        
        include __DIR__ . '/../views/trabajador/dashboard.php';
        break;
        
    case 'carteras':
        $filtro = $_GET['filtro'] ?? 'todas';
        
        // Obtener datos básicos
        $cartera = obtenerCarteraPorTrabajador($trabajador_id);
        $tarjetas = obtenerTarjetasPorTrabajador($trabajador_id);
        $completadas = isset($cartera['completadas']) ? $cartera['completadas'] : [];
        
        // Aplicar filtro usando nueva función
        $tarjetas = aplicarFiltroTarjetas($tarjetas, $filtro);
        
        include __DIR__ . '/../views/trabajador/carteras.php';
        break;
        
    case 'detalle_tarjeta':
        $id = $_GET['id'] ?? 0;
        $tarjeta = obtenerTarjetaPorId($id);
        
        // Verificar que la tarjeta pertenece a la cartera del trabajador
        if (!$tarjeta) {
            header('Location: ' . BASE_URL . 'index.php');
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
            header('Location: ' . BASE_URL . 'index.php');
            exit;
        }
        
        // Procesar registro de pago
        if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['registrar_pago'])) {
            $dia = intval($_POST['dia'] ?? 0);
            $monto = floatval($_POST['monto'] ?? 0);
            
            if ($dia > 0 && $monto > 0) {
                $is_semanal = ($tarjeta['tipo'] === 'antigua_semanal');
                $total_periodos = $tarjeta['semanas_pagar'] ?: ($tarjeta['dias_pagar'] ?: 12);
                $pagos_por_dia = [];

                if (isset($tarjeta['pagos'])) {
                    foreach ($tarjeta['pagos'] as $p) {
                        $pagos_por_dia[intval($p['dia'])] = floatval($p['pago'] ?? 0);
                    }
                }

                $primer_dia_pendiente = null;
                for ($periodo = 1; $periodo <= $total_periodos; $periodo++) {
                    $dia_esperado = $is_semanal ? ($periodo * 7) : $periodo;
                    $monto_registrado = floatval($pagos_por_dia[$dia_esperado] ?? 0);
                    if ($monto_registrado <= 0) {
                        $primer_dia_pendiente = $dia_esperado;
                        break;
                    }
                }

                if ($primer_dia_pendiente === null || $dia !== $primer_dia_pendiente) {
                    header('Location: ' . BASE_URL . 'controllers/TrabajadorController.php?action=detalle_tarjeta&id=' . $id . '&error=orden_pago_invalido');
                    exit;
                }

                $resultado = registrarPago($id, $dia, $monto, $trabajador_id);
                if ($resultado) {
                    header('Location: ' . BASE_URL . 'controllers/TrabajadorController.php?action=detalle_tarjeta&id=' . $id . '&mensaje=pago_registrado');
                } else {
                    header('Location: ' . BASE_URL . 'controllers/TrabajadorController.php?action=detalle_tarjeta&id=' . $id . '&error=error_pago');
                }
            } else {
                header('Location: ' . BASE_URL . 'controllers/TrabajadorController.php?action=detalle_tarjeta&id=' . $id . '&error=datos_invalidos');
            }
            exit;
        }
        
        // Recargar tarjeta después de cambios
        $tarjeta = obtenerTarjetaPorId($id);
        
        include __DIR__ . '/../views/trabajador/detalle_tarjeta.php';
        break;
    
    case 'registrar_cobros':
        // Vista para registrar cobros del día
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $pagos = $_POST['pagos'] ?? [];
            $exitosos = 0;
            $errores = 0;
            
            foreach ($pagos as $pago_data) {
                $tarjeta_id = intval($pago_data['tarjeta_id'] ?? 0);
                $dia = intval($pago_data['dia'] ?? 0);
                $monto = floatval($pago_data['monto'] ?? 0);
                
                if ($tarjeta_id > 0 && $dia > 0 && $monto > 0) {
                    $resultado = registrarPago($tarjeta_id, $dia, $monto, $trabajador_id);
                    if ($resultado) {
                        $exitosos++;
                    } else {
                        $errores++;
                    }
                }
            }
            
            $mensaje = "✅ {$exitosos} pagos registrados";
            if ($errores > 0) {
                $mensaje .= ", ❌ {$errores} errores";
            }
            
            header('Location: ' . BASE_URL . 'controllers/TrabajadorController.php?action=dashboard&mensaje=' . urlencode($mensaje));
            exit;
        }
        
        // Obtener cobros programados para hoy
        $cobros_hoy = obtenerCobrosHoy($trabajador_id);
        include __DIR__ . '/../views/trabajador/registrar_cobros.php';
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

