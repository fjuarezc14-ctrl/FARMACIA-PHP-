<?php
class ClienteController extends Controller {

    public function __construct() {
        $this->requireAuth();
    }

    public function index() {
        $modelo = $this->model('Cliente');
        $search = !empty($_GET['search']) ? trim($_GET['search']) : '';
        
        $page = isset($_GET['page']) && is_numeric($_GET['page']) ? max(1, (int)$_GET['page']) : 1;
        $limit = isset($_GET['limit']) && in_array((int)$_GET['limit'], [15, 25, 50, 100]) ? (int)$_GET['limit'] : 25;
        $offset = ($page - 1) * $limit;

        $totalRegistros = $modelo->contarClientes($search);
        $totalPaginas = max(1, ceil($totalRegistros / $limit));
        if ($page > $totalPaginas) {
            $page = $totalPaginas;
            $offset = ($page - 1) * $limit;
        }

        $clientes = $modelo->getPaginados($search, $limit, $offset);
        
        $this->view('clientes/index', [
            'title'           => 'Directorio de Clientes',
            'clientes'        => $clientes,
            'search'          => $search,
            'pagina_actual'   => $page,
            'total_paginas'   => $totalPaginas,
            'total_registros' => $totalRegistros,
            'limit'           => $limit
        ]);
    }

    public function save() {
        if ($_SERVER['REQUEST_METHOD'] == 'POST') {
            $this->validateCsrf();
            $modelo = $this->model('Cliente');
            $data = [
                'tipo_documento' => $_POST['tipo_documento'],
                'num_documento' => $_POST['num_documento'],
                'nombres' => $_POST['nombres'],
                'telefono' => $_POST['telefono'],
                'direccion' => $_POST['direccion'],
                'id' => $_POST['id'] ?? null
            ];
            
            if (empty($data['id'])) {
                $modelo->create($data);
            } else {
                $modelo->update($data);
            }
        }
        header('Location: ' . BASE_URL . 'cliente/index');
    }

    public function delete($id = null) {
        $this->requireRole(1, 'cliente/index');

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $_SESSION['error'] = 'Método no permitido.';
            header('Location: ' . BASE_URL . 'cliente/index');
            exit;
        }

        $this->validateCsrf();

        $clientId = (int)($_POST['id'] ?? $id ?? 0);
        if ($clientId <= 0) {
            $_SESSION['error'] = 'ID de cliente inválido.';
            header('Location: ' . BASE_URL . 'cliente/index');
            exit;
        }

        $modelo = $this->model('Cliente');
        if ($modelo->delete($clientId)) {
            $_SESSION['mensaje'] = 'Cliente eliminado correctamente.';
        } else {
            $_SESSION['error'] = 'No se pudo eliminar el cliente especificado.';
        }
        
        header('Location: ' . BASE_URL . 'cliente/index');
        exit;
    }

    /**
     * Registro rápido de cliente desde el modal del POS vía AJAX
     */
    public function saveAjax() {
        header('Content-Type: application/json; charset=utf-8');

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            echo json_encode(['success' => false, 'error' => 'Método no permitido']);
            exit;
        }

        $this->validateCsrf();

        $tipoDoc = trim($_POST['tipo_documento'] ?? 'DNI');
        $numDoc  = trim($_POST['num_documento'] ?? '');
        $nombres = trim($_POST['nombres'] ?? '');
        $tel     = trim($_POST['telefono'] ?? '');
        $dir     = trim($_POST['direccion'] ?? '');

        if (empty($numDoc) || empty($nombres)) {
            echo json_encode(['success' => false, 'error' => 'El número de documento y nombres son obligatorios.']);
            exit;
        }

        $modelo = $this->model('Cliente');
        $data = [
            'tipo_documento' => $tipoDoc,
            'num_documento'  => $numDoc,
            'nombres'        => $nombres,
            'telefono'       => $tel,
            'direccion'      => $dir
        ];

        $newId = $modelo->create($data);
        if ($newId) {
            echo json_encode([
                'success' => true,
                'cliente' => [
                    'id'             => $newId,
                    'tipo_documento' => $tipoDoc,
                    'num_documento'  => $numDoc,
                    'nombres'        => $nombres
                ]
            ]);
        } else {
            echo json_encode(['success' => false, 'error' => 'No se pudo guardar el cliente en la base de datos.']);
        }
        exit;
    }

    /**
     * Búsqueda predictiva de clientes vía AJAX para el selector del POS
     */
    public function searchAjax() {
        header('Content-Type: application/json; charset=utf-8');
        $q = trim($_GET['q'] ?? '');

        $db = new Database();
        $conn = $db->getConnection();

        if (empty($q)) {
            $stmt = $conn->query("SELECT id, tipo_documento, num_documento, nombres, puntos_acumulados FROM clientes WHERE estado = 1 ORDER BY id = 1 DESC, nombres ASC LIMIT 25");
            $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
        } else {
            $stmt = $conn->prepare("SELECT id, tipo_documento, num_documento, nombres, puntos_acumulados FROM clientes 
                                   WHERE estado = 1 AND (num_documento LIKE ? OR nombres LIKE ?) 
                                   ORDER BY nombres ASC LIMIT 25");
            $term = "%$q%";
            $stmt->execute([$term, $term]);
            $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
        }

        echo json_encode(['success' => true, 'clientes' => $rows]);
        exit;
    }
}

