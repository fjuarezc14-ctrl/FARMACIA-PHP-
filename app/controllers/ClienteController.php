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
            $tipoDoc = trim($_POST['tipo_documento'] ?? 'DNI');
            $numDoc = trim($_POST['num_documento'] ?? '');
            $nombres = trim($_POST['nombres'] ?? '');

            if (empty($numDoc) || empty($nombres)) {
                $_SESSION['error'] = "El número de documento y el nombre son obligatorios.";
                header('Location: ' . BASE_URL . 'cliente/index');
                exit;
            }

            $errDoc = $this->validarDocumento($tipoDoc, $numDoc);
            if ($errDoc) {
                $_SESSION['error'] = $errDoc;
                header('Location: ' . BASE_URL . 'cliente/index');
                exit;
            }

            $modelo = $this->model('Cliente');
            $data = [
                'tipo_documento' => $tipoDoc,
                'num_documento' => $numDoc,
                'nombres' => $nombres,
                'telefono' => trim($_POST['telefono'] ?? ''),
                'direccion' => trim($_POST['direccion'] ?? ''),
                'id' => !empty($_POST['id']) ? (int)$_POST['id'] : null
            ];
            
            try {
                if (empty($data['id'])) {
                    $modelo->create($data);
                    $this->logAccion('Clientes', 'CREAR', "Nuevo cliente registrado: " . $data['nombres'] . " (" . $data['num_documento'] . ")");
                    $_SESSION['mensaje'] = "Cliente registrado correctamente.";
                } else {
                    $modelo->update($data);
                    $this->logAccion('Clientes', 'EDITAR', "Cliente editado: " . $data['nombres']);
                    $_SESSION['mensaje'] = "Cliente actualizado correctamente.";
                }
            } catch (PDOException $e) {
                if ($e->getCode() == 23000 || strpos($e->getMessage(), 'Duplicate entry') !== false) {
                    $_SESSION['error'] = "Ya existe un cliente registrado con ese número de documento.";
                } else {
                    $_SESSION['error'] = "Error al guardar el cliente en la base de datos.";
                }
            }
        }
        header('Location: ' . BASE_URL . 'cliente/index');
        exit;
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
        if ($clientId <= 0 || $clientId === 1) {
            $_SESSION['error'] = 'ID de cliente inválido o protegido contra eliminación.';
            header('Location: ' . BASE_URL . 'cliente/index');
            exit;
        }

        $modelo = $this->model('Cliente');
        if ($modelo->delete($clientId)) {
            $this->logAccion('Clientes', 'ELIMINAR', "Cliente eliminado ID #$clientId");
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

        $errDoc = $this->validarDocumento($tipoDoc, $numDoc);
        if ($errDoc) {
            echo json_encode(['success' => false, 'error' => $errDoc]);
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

        try {
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
        } catch (PDOException $e) {
            if ($e->getCode() == 23000 || strpos($e->getMessage(), 'Duplicate entry') !== false) {
                echo json_encode(['success' => false, 'error' => 'Ya existe un cliente con ese número de documento.']);
            } else {
                echo json_encode(['success' => false, 'error' => 'Error de base de datos al guardar cliente.']);
            }
        }
        exit;
    }

    private function validarDocumento($tipoDoc, $numDoc) {
        $numDoc = trim($numDoc);
        if ($tipoDoc === 'DNI') {
            if (!preg_match('/^\d{8}$/', $numDoc)) {
                return "El DNI debe contener exactamente 8 dígitos numéricos.";
            }
        } elseif ($tipoDoc === 'RUC') {
            if (!preg_match('/^\d{11}$/', $numDoc)) {
                return "El RUC debe contener exactamente 11 dígitos numéricos.";
            }
        } elseif ($tipoDoc === 'CE' || $tipoDoc === 'Pasaporte') {
            if (strlen($numDoc) < 4 || strlen($numDoc) > 15) {
                return "El documento debe contener entre 4 y 15 caracteres.";
            }
        }
        return null;
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

