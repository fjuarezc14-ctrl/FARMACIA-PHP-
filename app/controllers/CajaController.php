<?php
class CajaController extends Controller {

    public function __construct() {
        $this->requireAuth();
    }

    public function index() {
        $this->requireRole(1, 'venta/pos');
        $cajaModel = $this->model('Caja');
        
        $fecha_inicio = $_GET['fecha_inicio'] ?? date('Y-m-d');
        $fecha_fin = $_GET['fecha_fin'] ?? date('Y-m-d');
        
        $historial = $cajaModel->getHistorial($fecha_inicio, $fecha_fin);
        
        $data = [
            'title' => 'Historial de Cajas',
            'historial' => $historial,
            'fecha_inicio' => $fecha_inicio,
            'fecha_fin' => $fecha_fin
        ];
        
        $this->view('cajas/index', $data);
    }

    public function apertura() {
        $cajaModel = $this->model('Caja');
        
        // Si ya tiene caja abierta, no puede abrir otra.
        $cajaAbierta = $cajaModel->getCajaAbiertaPorUsuario($_SESSION['user_id']);
        if ($cajaAbierta) {
            header('Location: ' . BASE_URL . 'caja/cierre');
            exit;
        }

        if ($_SERVER['REQUEST_METHOD'] == 'POST') {
            $this->validateCsrf();
            
            $raw_monto = str_replace(',', '.', trim($_POST['monto_inicial'] ?? '0'));
            if (!is_numeric($raw_monto)) {
                $_SESSION['error'] = "El monto inicial debe ser un valor numérico válido.";
                header('Location: ' . BASE_URL . 'caja/apertura');
                exit;
            }

            $monto_inicial = (float)$raw_monto;

            if ($monto_inicial < 0) {
                $_SESSION['error'] = "El monto inicial de apertura no puede ser negativo.";
                header('Location: ' . BASE_URL . 'caja/apertura');
                exit;
            }

            if ($monto_inicial > 10000) {
                $_SESSION['error'] = "El monto inicial (S/ " . number_format($monto_inicial, 2) . ") excede el límite máximo permitido para apertura de turno (S/ 10,000.00).";
                header('Location: ' . BASE_URL . 'caja/apertura');
                exit;
            }
            
            if ($cajaModel->abrirCaja($_SESSION['user_id'], $monto_inicial)) {
                $_SESSION['mensaje'] = "Caja aperturada exitosamente con S/ " . number_format($monto_inicial, 2) . ". Puede iniciar la venta.";
                header('Location: ' . BASE_URL . 'venta/pos');
                exit;
            } else {
                $_SESSION['error'] = "Ocurrió un error al abrir la caja.";
            }
        }
        
        $this->view('cajas/apertura', ['title' => 'Apertura de Caja']);
    }

    public function cierre() {
        $cajaModel = $this->model('Caja');
        
        // Verificar si tiene caja abierta
        $cajaAbierta = $cajaModel->getCajaAbiertaPorUsuario($_SESSION['user_id']);
        
        if (!$cajaAbierta) {
            $_SESSION['error'] = "No tienes ninguna caja abierta para cerrar.";
            header('Location: ' . BASE_URL . 'caja/apertura');
            exit;
        }

        if ($_SERVER['REQUEST_METHOD'] == 'POST') {
            $this->validateCsrf();
            
            $raw_final = str_replace(',', '.', trim($_POST['monto_final_real'] ?? '0'));
            if (!is_numeric($raw_final)) {
                $_SESSION['error'] = "El monto final real debe ser un valor numérico válido.";
                header('Location: ' . BASE_URL . 'caja/cierre');
                exit;
            }

            $monto_final_real = (float)$raw_final;

            if ($monto_final_real < 0) {
                $_SESSION['error'] = "El monto de efectivo contado no puede ser negativo.";
                header('Location: ' . BASE_URL . 'caja/cierre');
                exit;
            }

            if ($monto_final_real > 500000) {
                $_SESSION['error'] = "El monto declarado (S/ " . number_format($monto_final_real, 2) . ") excede el límite permitido para un arqueo de turno.";
                header('Location: ' . BASE_URL . 'caja/cierre');
                exit;
            }

            $observacion = trim($_POST['observacion'] ?? '');
            
            if ($cajaModel->cerrarCaja($cajaAbierta['id'], $monto_final_real, $observacion)) {
                $_SESSION['mensaje'] = "Caja cerrada correctamente. Su turno ha finalizado.";
                $_SESSION['ultimo_arqueo_cerrado'] = $cajaAbierta['id'];
                header('Location: ' . BASE_URL . 'caja/apertura');
                exit;
            } else {
                $_SESSION['error'] = "Ocurrió un error al cerrar la caja.";
            }
        }
        
        $resumen = $cajaModel->getResumenActual($cajaAbierta['id']);
        $movimientos = $cajaModel->getMovimientos($cajaAbierta['id']);
        
        $data = [
            'title' => 'Cierre de Caja',
            'caja' => $cajaAbierta,
            'resumen' => $resumen,
            'movimientos' => $movimientos
        ];
        
        $this->view('cajas/cierre', $data);
    }
    
    public function movimiento() {
        $cajaModel = $this->model('Caja');
        $cajaAbierta = $cajaModel->getCajaAbiertaPorUsuario($_SESSION['user_id']);
        
        if (!$cajaAbierta) {
            $_SESSION['error'] = "No tienes caja abierta para registrar movimientos.";
        } else {
            if ($_SERVER['REQUEST_METHOD'] == 'POST') {
                $this->validateCsrf();
                $tipo = trim($_POST['tipo'] ?? 'INGRESO');
                $rawMonto = str_replace(',', '.', trim($_POST['monto'] ?? '0'));
                $monto = (float)preg_replace('/[^\d.,\-]/', '', $rawMonto);
                $motivo = trim($_POST['motivo'] ?? '');
                
                if ($monto <= 0) {
                    $_SESSION['error'] = "El monto del movimiento debe ser mayor a S/ 0.00.";
                    header('Location: ' . BASE_URL . 'caja/cierre');
                    exit;
                }

                $resumen = $cajaModel->getResumenActual($cajaAbierta['id']);
                $saldoEfectivoActual = (float)$cajaAbierta['monto_inicial'] 
                                     + (float)($resumen['ingresos_efectivo'] ?? 0) 
                                     + (float)($resumen['ingresos_extras'] ?? 0) 
                                     - (float)($resumen['egresos'] ?? 0);

                if ($tipo === 'EGRESO' && $monto > $saldoEfectivoActual) {
                    $_SESSION['error'] = "Operación denegada: El egreso solicitado (S/ " . number_format($monto, 2) . ") supera el efectivo disponible en gaveta (S/ " . number_format($saldoEfectivoActual, 2) . "). No se permiten saldos negativos.";
                    header('Location: ' . BASE_URL . 'caja/cierre');
                    exit;
                }
                
                if ($cajaModel->registrarMovimiento($cajaAbierta['id'], $tipo, $monto, $motivo)) {
                    $this->logAccion('Caja', $tipo, "Movimiento de $tipo por S/ " . number_format($monto, 2) . " ($motivo)");
                    $_SESSION['mensaje'] = "Movimiento de $tipo registrado exitosamente.";
                } else {
                    $_SESSION['error'] = "Error al registrar el movimiento en la base de datos.";
                }
            }
        }
        header('Location: ' . BASE_URL . 'caja/cierre');
        exit;
    }
    
    public function ticket_arqueo($id) {
        $cajaModel = $this->model('Caja');
        $caja = $cajaModel->getById($id);
        
        if(!$caja) {
            $_SESSION['error'] = "El arqueo de caja solicitado no fue encontrado.";
            header('Location: ' . BASE_URL . 'dashboard/index');
            exit;
        }

        // Solo el administrador o el usuario dueño de la caja puede ver el ticket de arqueo
        $cajaUserId = (int)($caja['usuario_id'] ?? $caja['id_usuario'] ?? 0);
        if ((int)($_SESSION['rol_id'] ?? 0) !== 1 && $cajaUserId !== (int)$_SESSION['user_id']) {
            $_SESSION['error'] = "No tiene permiso para visualizar arqueos de otros usuarios.";
            header('Location: ' . BASE_URL . 'caja/cierre');
            exit;
        }
        
        require_once '../app/views/cajas/ticket_arqueo.php';
    }
}
