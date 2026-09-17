<?php
class PuntosController extends Controller {

    public function __construct() {
        $this->requireRole([1, 2, 4], 'venta/pos');
    }

    public function index() {
        $puntoModel = $this->model('Punto');

        $search = !empty($_GET['search']) ? trim($_GET['search']) : '';
        $page = isset($_GET['page']) && is_numeric($_GET['page']) ? max(1, (int)$_GET['page']) : 1;
        $limit = isset($_GET['limit']) && in_array((int)$_GET['limit'], [15, 25, 50, 100]) ? (int)$_GET['limit'] : 25;
        $offset = ($page - 1) * $limit;

        $totalRegistros = $puntoModel->contarClientesPuntos($search);
        $totalPaginas = max(1, ceil($totalRegistros / $limit));
        if ($page > $totalPaginas) {
            $page = $totalPaginas;
            $offset = ($page - 1) * $limit;
        }

        $clientes = $puntoModel->getClientesPuntos($search, $limit, $offset);
        $configPuntos = $puntoModel->getConfig();
        $metricas = $puntoModel->getMetricasPuntos();

        $this->view('puntos/index', [
            'title'           => 'Gestión de Puntos de Clientes',
            'clientes'        => $clientes,
            'configPuntos'    => $configPuntos,
            'metricas'        => $metricas,
            'search'          => $search,
            'pagina_actual'   => $page,
            'total_paginas'   => $totalPaginas,
            'total_registros' => $totalRegistros,
            'limit'           => $limit
        ]);
    }

    public function guardarConfig() {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Location: ' . BASE_URL . 'puntos/index');
            exit;
        }

        $this->validateCsrf();
        if ($_SESSION['rol_id'] != 1) {
            $_SESSION['error'] = "Solo los administradores pueden modificar las reglas del sistema de puntos.";
            header('Location: ' . BASE_URL . 'puntos/index');
            exit;
        }

        $consumoBase = isset($_POST['consumo_base']) ? (float)$_POST['consumo_base'] : 10.00;
        $valorCanje  = isset($_POST['valor_canje']) ? (float)$_POST['valor_canje'] : 0.10;
        $habilitado  = isset($_POST['habilitado']) ? (int)$_POST['habilitado'] : 1;

        if ($consumoBase <= 0 || $valorCanje <= 0) {
            $_SESSION['error'] = "Los valores de consumo y canje deben ser mayores a 0.";
            header('Location: ' . BASE_URL . 'puntos/index');
            exit;
        }

        $puntoModel = $this->model('Punto');
        if ($puntoModel->updateConfig($consumoBase, $valorCanje, $habilitado)) {
            $_SESSION['mensaje'] = "Configuración de puntos actualizada correctamente. Las compras futuras aplicarán esta nueva regla sin alterar los históricos.";
        } else {
            $_SESSION['error'] = "No se pudo guardar la configuración de puntos.";
        }

        header('Location: ' . BASE_URL . 'puntos/index');
        exit;
    }

    public function ajustar() {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Location: ' . BASE_URL . 'puntos/index');
            exit;
        }

        $this->validateCsrf();
        $idCliente = isset($_POST['id_cliente']) ? (int)$_POST['id_cliente'] : 0;
        $operacion = trim($_POST['operacion'] ?? 'sumar');
        $puntos    = isset($_POST['puntos']) ? abs((int)$_POST['puntos']) : 0;
        $motivo    = trim($_POST['motivo'] ?? '');

        if ($idCliente <= 1 || $puntos <= 0 || empty($motivo)) {
            $_SESSION['error'] = "Datos inválidos para el ajuste de puntos. Debe ingresar un cliente válido, cantidad mayor a 0 y un motivo explicativo.";
            header('Location: ' . BASE_URL . 'puntos/index');
            exit;
        }

        $puntosDelta = ($operacion === 'restar') ? -$puntos : $puntos;
        $motivoCompleto = ($operacion === 'restar' ? "Deducción manual: " : "Abono manual: ") . $motivo;

        $puntoModel = $this->model('Punto');
        if ($puntoModel->registrarMovimiento($idCliente, $_SESSION['user_id'], 'AJUSTE_MANUAL', $puntosDelta, $motivoCompleto)) {
            $_SESSION['mensaje'] = "Ajuste de puntos aplicado exitosamente al cliente.";
        } else {
            $_SESSION['error'] = "Error al procesar el ajuste de puntos. Verifique el saldo del cliente.";
        }

        header('Location: ' . BASE_URL . 'puntos/index');
        exit;
    }

    public function historialAjax($idCliente = 0) {
        header('Content-Type: application/json');
        $idCliente = (int)$idCliente;
        if ($idCliente <= 1) {
            echo json_encode(['status' => 'error', 'data' => []]);
            exit;
        }

        $puntoModel = $this->model('Punto');
        $historial = $puntoModel->getHistorialCliente($idCliente, 50);

        echo json_encode([
            'status' => 'success',
            'data'   => $historial
        ]);
        exit;
    }
}
