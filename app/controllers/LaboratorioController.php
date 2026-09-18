<?php
class LaboratorioController extends Controller {

    public function __construct() {
        $this->requireRole([1, 2, 4], 'venta/pos');
    }

    public function index() {
        $modelo = $this->model('Laboratorio');
        
        $search = trim($_GET['search'] ?? '');
        $page = isset($_GET['page']) && is_numeric($_GET['page']) ? max(1, (int)$_GET['page']) : 1;
        $limit = isset($_GET['limit']) && in_array((int)$_GET['limit'], [15, 25, 50, 100]) ? (int)$_GET['limit'] : 15;
        $offset = ($page - 1) * $limit;

        $totalRegistros = $modelo->contarLaboratorios($search);
        $totalPaginas = max(1, ceil($totalRegistros / $limit));
        if ($page > $totalPaginas) {
            $page = $totalPaginas;
            $offset = ($page - 1) * $limit;
        }

        $laboratorios = $modelo->getPaginados($search, $limit, $offset);
        $todosLaboratorios = $modelo->getAll();

        $this->view('laboratorios/index', [
            'title'             => 'Laboratorios',
            'laboratorios'      => $laboratorios,
            'todosLaboratorios' => $todosLaboratorios,
            'search'            => $search,
            'pagina_actual'     => $page,
            'total_paginas'     => $totalPaginas,
            'total_registros'   => $totalRegistros,
            'limit'             => $limit
        ]);
    }

    public function save() {
        if ($_SERVER['REQUEST_METHOD'] == 'POST') {
            $this->validateCsrf();
            $modelo = $this->model('Laboratorio');
            
            $nombre = trim($_POST['nombre'] ?? '');
            $descripcion = trim($_POST['descripcion'] ?? '');
            $id = !empty($_POST['id']) ? (int)$_POST['id'] : null;

            if (empty($nombre)) {
                $_SESSION['error'] = 'El nombre del laboratorio es obligatorio.';
                header('Location: ' . BASE_URL . 'laboratorio/index');
                exit;
            }

            if ($modelo->existeNombre($nombre, $id)) {
                $_SESSION['error'] = "Ya existe un laboratorio registrado con el nombre '{$nombre}'.";
                header('Location: ' . BASE_URL . 'laboratorio/index');
                exit;
            }

            $data = [
                'nombre' => $nombre,
                'descripcion' => $descripcion,
                'id' => $id
            ];
            
            if (empty($id)) {
                if ($modelo->create($data)) {
                    $_SESSION['mensaje'] = 'Laboratorio creado con éxito.';
                } else {
                    $_SESSION['error'] = 'Error al registrar el laboratorio.';
                }
            } else {
                if ($modelo->update($data)) {
                    $_SESSION['mensaje'] = 'Laboratorio actualizado con éxito.';
                } else {
                    $_SESSION['error'] = 'Error al actualizar el laboratorio.';
                }
            }
        }
        header('Location: ' . BASE_URL . 'laboratorio/index');
        exit;
    }

    public function delete($id = null) {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $_SESSION['error'] = 'Método no permitido.';
            header('Location: ' . BASE_URL . 'laboratorio/index');
            exit;
        }

        $this->validateCsrf();

        $labId = (int)($_POST['id'] ?? $id ?? 0);
        if ($labId <= 0) {
            $_SESSION['error'] = 'ID de laboratorio inválido.';
            header('Location: ' . BASE_URL . 'laboratorio/index');
            exit;
        }

        $modelo = $this->model('Laboratorio');
        $prods = $modelo->contarProductosAsociados($labId);
        if ($prods > 0) {
            $_SESSION['error'] = "Eliminación bloqueada: Este laboratorio tiene {$prods} producto(s) asociado(s). Para darlo de baja sin perder los productos, use la opción de 'Reasignar y Eliminar'.";
            header('Location: ' . BASE_URL . 'laboratorio/index');
            exit;
        }

        if ($modelo->delete($labId)) {
            $_SESSION['mensaje'] = 'Laboratorio eliminado correctamente.';
        } else {
            $_SESSION['error'] = 'No se pudo eliminar el laboratorio.';
        }
        
        header('Location: ' . BASE_URL . 'laboratorio/index');
        exit;
    }

    public function reasignar() {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $_SESSION['error'] = 'Método no permitido.';
            header('Location: ' . BASE_URL . 'laboratorio/index');
            exit;
        }

        $this->validateCsrf();

        $id_origen = (int)($_POST['id_origen'] ?? 0);
        $id_destino = (int)($_POST['id_destino'] ?? 0);

        if ($id_origen <= 0 || $id_destino <= 0 || $id_origen === $id_destino) {
            $_SESSION['error'] = 'Debe seleccionar un laboratorio de destino válido distinto al original.';
            header('Location: ' . BASE_URL . 'laboratorio/index');
            exit;
        }

        $modelo = $this->model('Laboratorio');
        $prods = $modelo->contarProductosAsociados($id_origen);

        if ($modelo->reasignarYEliminar($id_origen, $id_destino)) {
            $_SESSION['mensaje'] = "Se transfirieron exitosamente {$prods} producto(s) y el laboratorio de origen fue eliminado.";
        } else {
            $_SESSION['error'] = 'Ocurrió un error al intentar reasignar los productos y eliminar el laboratorio.';
        }

        header('Location: ' . BASE_URL . 'laboratorio/index');
        exit;
    }
}

