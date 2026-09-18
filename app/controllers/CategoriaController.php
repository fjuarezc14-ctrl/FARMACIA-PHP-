<?php
class CategoriaController extends Controller {

    public function __construct() {
        $this->requireRole([1, 2, 4], 'venta/pos');
    }

    public function index() {
        $modelo = $this->model('Categoria');
        
        $search = trim($_GET['search'] ?? '');
        $page = isset($_GET['page']) && is_numeric($_GET['page']) ? max(1, (int)$_GET['page']) : 1;
        $limit = isset($_GET['limit']) && in_array((int)$_GET['limit'], [15, 25, 50, 100]) ? (int)$_GET['limit'] : 15;
        $offset = ($page - 1) * $limit;

        $totalRegistros = $modelo->contarCategorias($search);
        $totalPaginas = max(1, ceil($totalRegistros / $limit));
        if ($page > $totalPaginas) {
            $page = $totalPaginas;
            $offset = ($page - 1) * $limit;
        }

        $categorias = $modelo->getPaginadas($search, $limit, $offset);
        $todasCategorias = $modelo->getAll();

        $this->view('categorias/index', [
            'title'            => 'Categorías',
            'categorias'       => $categorias,
            'todasCategorias'  => $todasCategorias,
            'search'           => $search,
            'pagina_actual'    => $page,
            'total_paginas'    => $totalPaginas,
            'total_registros'  => $totalRegistros,
            'limit'            => $limit
        ]);
    }

    public function save() {
        if ($_SERVER['REQUEST_METHOD'] == 'POST') {
            $this->validateCsrf();
            $modelo = $this->model('Categoria');
            
            $nombre = trim($_POST['nombre'] ?? '');
            $descripcion = trim($_POST['descripcion'] ?? '');
            $id = !empty($_POST['id']) ? (int)$_POST['id'] : null;

            if (empty($nombre)) {
                $_SESSION['error'] = 'El nombre de la categoría es obligatorio.';
                header('Location: ' . BASE_URL . 'categoria/index');
                exit;
            }

            if ($modelo->existeNombre($nombre, $id)) {
                $_SESSION['error'] = "Ya existe una categoría registrada con el nombre '{$nombre}'.";
                header('Location: ' . BASE_URL . 'categoria/index');
                exit;
            }

            $data = [
                'nombre' => $nombre,
                'descripcion' => $descripcion,
                'id' => $id
            ];
            
            if (empty($id)) {
                if ($modelo->create($data)) {
                    $_SESSION['mensaje'] = 'Categoría creada con éxito.';
                } else {
                    $_SESSION['error'] = 'Error al registrar la categoría.';
                }
            } else {
                if ($modelo->update($data)) {
                    $_SESSION['mensaje'] = 'Categoría actualizada con éxito.';
                } else {
                    $_SESSION['error'] = 'Error al actualizar la categoría.';
                }
            }
        }
        header('Location: ' . BASE_URL . 'categoria/index');
        exit;
    }

    public function delete($id = null) {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $_SESSION['error'] = 'Método no permitido.';
            header('Location: ' . BASE_URL . 'categoria/index');
            exit;
        }

        $this->validateCsrf();

        $categoryId = (int)($_POST['id'] ?? $id ?? 0);
        if ($categoryId <= 0) {
            $_SESSION['error'] = 'ID de categoría inválido.';
            header('Location: ' . BASE_URL . 'categoria/index');
            exit;
        }

        $modelo = $this->model('Categoria');
        $prods = $modelo->contarProductosAsociados($categoryId);
        if ($prods > 0) {
            $_SESSION['error'] = "Eliminación bloqueada: Esta categoría tiene {$prods} producto(s) asociado(s). Para darla de baja sin perder los productos, use la opción de 'Reasignar y Eliminar'.";
            header('Location: ' . BASE_URL . 'categoria/index');
            exit;
        }

        if ($modelo->delete($categoryId)) {
            $_SESSION['mensaje'] = 'Categoría eliminada correctamente.';
        } else {
            $_SESSION['error'] = 'No se pudo eliminar la categoría.';
        }
        
        header('Location: ' . BASE_URL . 'categoria/index');
        exit;
    }

    public function reasignar() {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $_SESSION['error'] = 'Método no permitido.';
            header('Location: ' . BASE_URL . 'categoria/index');
            exit;
        }

        $this->validateCsrf();

        $id_origen = (int)($_POST['id_origen'] ?? 0);
        $id_destino = (int)($_POST['id_destino'] ?? 0);

        if ($id_origen <= 0 || $id_destino <= 0 || $id_origen === $id_destino) {
            $_SESSION['error'] = 'Debe seleccionar una categoría de destino válida distinta a la original.';
            header('Location: ' . BASE_URL . 'categoria/index');
            exit;
        }

        $modelo = $this->model('Categoria');
        $prods = $modelo->contarProductosAsociados($id_origen);

        if ($modelo->reasignarYEliminar($id_origen, $id_destino)) {
            $_SESSION['mensaje'] = "Se transfirieron exitosamente {$prods} producto(s) y la categoría de origen fue eliminada.";
        } else {
            $_SESSION['error'] = 'Ocurrió un error al intentar reasignar los productos y eliminar la categoría.';
        }

        header('Location: ' . BASE_URL . 'categoria/index');
        exit;
    }
}

