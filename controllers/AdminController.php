<?php
require_once __DIR__ . '/../config/config.php';
verificarRol('administrador');

$action = $_GET['action'] ?? 'dashboard';

switch ($action) {
    case 'dashboard':
        $stats = calcularEstadisticas();
        $todas_carteras = obtenerTodasLasCarteras();
        $todas_tarjetas = obtenerTodasLasTarjetas();
        $cobradores_detalle = [];
        foreach (obtenerTodosTrabajadores() as $trabajador) {
            if (!(bool)($trabajador['activo'] ?? true)) {
                continue;
            }

            $stats_trabajador = obtenerEstadisticasTrabajador($trabajador['id']);
            $cobradores_detalle[] = [
                'id' => $trabajador['id'],
                'nombre' => $trabajador['nombre'] ?? ('Trabajador #' . $trabajador['id']),
                'cobrado_hoy' => floatval($stats_trabajador['cobrado_hoy'] ?? 0),
                'pendiente_hoy' => floatval($stats_trabajador['pendiente_hoy'] ?? 0),
                'tarjetas_activas' => intval($stats_trabajador['total_tarjetas'] ?? 0),
                'completadas' => intval($stats_trabajador['completadas'] ?? 0),
            ];
        }
        include __DIR__ . '/../views/admin/dashboard.php';
        break;
        
    case 'carteras':
        $todas_carteras = obtenerTodasLasCarteras();
        include __DIR__ . '/../views/admin/carteras.php';
        break;

    case 'nuevo_cartera':
        // Form para crear cartera y asignar tarjetas
        $trabajadores = obtenerTodosTrabajadores();
        include __DIR__ . '/../views/admin/nueva_cartera.php';
        break;

    case 'editar_cartera':
        $id = $_GET['id'] ?? 0;
        $cartera = obtenerCarteraPorId($id);
        if (!$cartera) {
            header('Location: ' . BASE_URL . 'controllers/AdminController.php?action=carteras&error=no_encontrada');
            exit;
        }
        $trabajadores = obtenerTodosTrabajadores();
        include __DIR__ . '/../views/admin/editar_cartera.php';
        break;

    case 'guardar_cartera':
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $data = [
                'trabajador_id' => $_POST['trabajador_id'] ?? 0,
                'nombre' => $_POST['nombre'] ?? 'Cartera nueva',
                'descripcion' => $_POST['descripcion'] ?? ''
            ];
            $id = $_POST['id'] ?? '';
            if (!empty($id)) {
                actualizarCartera($id, $data);
            } else {
                agregarCartera($data);
            }
        }
        header('Location: ' . BASE_URL . 'controllers/AdminController.php?action=carteras&mensaje=guardado');
        exit;
        break;

    case 'eliminar_cartera':
        $id = $_GET['id'] ?? 0;
        eliminarCartera($id);
        header('Location: ' . BASE_URL . 'controllers/AdminController.php?action=carteras&mensaje=eliminado');
        exit;
        break;

    case 'completar_tarjeta':
        $tarjeta_id = $_GET['id'] ?? 0;
        $result = marcarTarjetaCompletada($tarjeta_id);
        if ($result) {
            $tarjeta = obtenerTarjetaPorId($tarjeta_id);
            $cartera_id = $tarjeta['cartera_id'] ?? 0;
            header('Location: ' . BASE_URL . 'controllers/AdminController.php?action=detalle_cartera&id=' . $cartera_id . '&mensaje=completada');
        } else {
            header('Location: ' . BASE_URL . 'controllers/AdminController.php?action=carteras&error=no_encontrada');
        }
        exit;
        break;
        
    case 'tarjetas':
        $todas_tarjetas = obtenerTodasLasTarjetas();
        include __DIR__ . '/../views/admin/tarjetas.php';
        break;

    case 'asignar_promotor':
        $cartera_id = $_GET['id'] ?? 0;
        $cartera = obtenerCarteraPorId($cartera_id);
        if (!$cartera) {
            header('Location: ' . BASE_URL . 'controllers/AdminController.php?action=carteras&error=no_encontrada');
            exit;
        }
        
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $trabajador_id = $_POST['trabajador_id'] ?? 0;
            if ($trabajador_id) {
                actualizarCartera($cartera_id, ['trabajador_id' => $trabajador_id]);
                header('Location: ' . BASE_URL . 'controllers/AdminController.php?action=detalle_cartera&id=' . $cartera_id . '&mensaje=promotor_asignado');
            } else {
                header('Location: ' . BASE_URL . 'controllers/AdminController.php?action=detalle_cartera&id=' . $cartera_id . '&error=datos_invalidos');
            }
            exit;
        }
        
        $trabajadores = obtenerTodosTrabajadores();
        include __DIR__ . '/../views/admin/asignar_promotor.php';
        break;
        
    case 'alta_cartera':
        // legacy route; redirect to nueva acción
        $tipo = $_GET['tipo'] ?? '';
        header('Location: ' . BASE_URL . 'controllers/AdminController.php?action=alta_tarjeta&tipo=' . urlencode($tipo));
        exit;
        break;

    case 'alta_tarjeta':
        $tipo = $_GET['tipo'] ?? 'nueva';
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $cartera_id = $_POST['cartera_id'] ?? 0;
            if (!$cartera_id) {
                header('Location: ' . BASE_URL . 'controllers/AdminController.php?action=alta_tarjeta&tipo=' . $tipo . '&error=sin_cartera');
                exit;
            }
            
            $data = $_POST;
            $data['tipo'] = $tipo;
            $tarjeta_id = agregarTarjeta($data, $cartera_id);
            
            if ($tarjeta_id) {
                header('Location: ' . BASE_URL . 'controllers/AdminController.php?action=detalle_cartera&id=' . $cartera_id . '&mensaje=tarjeta_creada');
            } else {
                header('Location: ' . BASE_URL . 'controllers/AdminController.php?action=alta_tarjeta&tipo=' . $tipo . '&error=datos_invalidos');
            }
            exit;
        }
        include __DIR__ . '/../views/admin/alta_tarjeta.php';
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
        $trabajadores = obtenerTodosTrabajadores();
        include __DIR__ . '/../views/admin/trabajadores.php';
        break;

    case 'nuevo_trabajador':
        $trabajador = null;
        include __DIR__ . '/../views/admin/trabajador_form.php';
        break;

    case 'editar_trabajador':
        $id = $_GET['id'] ?? 0;
        $trabajador = obtenerUsuarioPorId($id);
        if (!$trabajador || $trabajador['rol'] !== 'trabajador') {
            header('Location: ' . BASE_URL . 'controllers/AdminController.php?action=trabajadores&error=no_encontrado');
            exit;
        }
        include __DIR__ . '/../views/admin/trabajador_form.php';
        break;

    case 'guardar_trabajador':
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $id = $_POST['id'] ?? '';
            $data = [
                'telefono' => $_POST['telefono'] ?? '',
                'nombre' => $_POST['nombre'] ?? '',
                'password' => $_POST['password'] ?? ''
            ];
            
            if (!empty($id)) {
                actualizarUsuario($id, $data);
            } else {
                $data['rol'] = 'trabajador';
                agregarUsuario($data);
            }
        }
        header('Location: ' . BASE_URL . 'controllers/AdminController.php?action=trabajadores&mensaje=guardado');
        exit;
        break;

    case 'baja_trabajador':
        $id = $_GET['id'] ?? 0;
        togglearEstadoUsuario($id);
        header('Location: ' . BASE_URL . 'controllers/AdminController.php?action=trabajadores');
        exit;
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

