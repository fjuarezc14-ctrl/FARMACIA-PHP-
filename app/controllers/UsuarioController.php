<?php
class UsuarioController extends Controller {

    public function __construct() {
        $this->requireRole(1, 'venta/pos');
    }

    public function index() {
        $userModel = $this->model('User');
        $roleModel = $this->model('Role');
        
        $usuarios = $userModel->getAll();
        foreach ($usuarios as &$u) {
            unset($u['password']);
        }
        unset($u);

        $data = [
            'title' => 'Gestión de Personal',
            'usuarios' => $usuarios,
            'roles' => $roleModel->getAll()
        ];
        
        $this->view('usuarios/index', $data);
    }

    public function save() {
        if ($_SERVER['REQUEST_METHOD'] == 'POST') {
            $this->validateCsrf();
            $userModel = $this->model('User');
            
            $data = [
                'nombres' => trim($_POST['nombres']),
                'apellidos' => trim($_POST['apellidos']),
                'usuario' => trim($_POST['usuario']),
                'email' => trim($_POST['email']),
                'rol_id' => (int)$_POST['rol_id'],
                'estado' => 1
            ];
            
            if (empty($_POST['id'])) {
                // Nuevo usuario
                $data['password'] = $_POST['password'];
                if ($userModel->create($data)) {
                    $_SESSION['mensaje'] = "Usuario creado exitosamente.";
                } else {
                    $_SESSION['error'] = "Error al crear el usuario. Quizás el nombre de usuario ya existe.";
                }
            } else {
                // Actualizar usuario
                if ($userModel->update($_POST['id'], $data)) {
                    // Actualizar constraseña si se proporcionó
                    if (!empty($_POST['password'])) {
                        $userModel->updatePassword($_POST['id'], $_POST['password']);
                    }
                    $_SESSION['mensaje'] = "Usuario actualizado exitosamente.";
                } else {
                    $_SESSION['error'] = "Error al actualizar el usuario.";
                }
            }
        }
        header('Location: ' . BASE_URL . 'usuario/index');
    }

    public function toggle($id = null) {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $_SESSION['error'] = 'Método no permitido.';
            header('Location: ' . BASE_URL . 'usuario/index');
            exit;
        }

        $this->validateCsrf();

        $userId = (int)($_POST['id'] ?? $id ?? 0);
        if ($userId <= 0) {
            $_SESSION['error'] = 'ID de usuario inválido.';
            header('Location: ' . BASE_URL . 'usuario/index');
            exit;
        }

        $userModel = $this->model('User');
        if ($userId === 1) {
            $_SESSION['error'] = "No se puede desactivar al Administrador principal.";
        } else {
            if ($userModel->toggleEstado($userId)) {
                $_SESSION['mensaje'] = "Estado de usuario cambiado exitosamente.";
            } else {
                $_SESSION['error'] = "Error al cambiar estado de usuario.";
            }
        }
        header('Location: ' . BASE_URL . 'usuario/index');
        exit;
    }

    public function delete($id = null) {
        $this->requireRole(1, 'usuario/index');

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $_SESSION['error'] = 'Método no permitido.';
            header('Location: ' . BASE_URL . 'usuario/index');
            exit;
        }

        $this->validateCsrf();

        $userId = (int)($_POST['id'] ?? $id ?? 0);
        if ($userId <= 0) {
            $_SESSION['error'] = 'ID de usuario inválido.';
            header('Location: ' . BASE_URL . 'usuario/index');
            exit;
        }

        if ($userId === 1) {
            $_SESSION['error'] = 'No se puede eliminar al Administrador principal del sistema.';
            header('Location: ' . BASE_URL . 'usuario/index');
            exit;
        }

        if ($userId === (int)($_SESSION['user_id'] ?? 0)) {
            $_SESSION['error'] = 'No puedes eliminar tu propia cuenta en sesión.';
            header('Location: ' . BASE_URL . 'usuario/index');
            exit;
        }

        $userModel = $this->model('User');
        $usuarioExistente = $userModel->getById($userId);
        if (!$usuarioExistente) {
            $_SESSION['error'] = 'El usuario especificado no existe.';
            header('Location: ' . BASE_URL . 'usuario/index');
            exit;
        }

        // Si tiene movimientos vinculados, advertir al admin y recomendar desactivación
        if ($userModel->hasAssociatedRecords($userId)) {
            $_SESSION['error'] = 'No se puede eliminar el usuario "' . htmlspecialchars($usuarioExistente['usuario']) . '" porque tiene ventas, compras, movimientos de caja o auditorías registradas. Para revocar su acceso, utiliza la opción "Desactivar".';
            header('Location: ' . BASE_URL . 'usuario/index');
            exit;
        }

        if ($userModel->delete($userId)) {
            $this->logAccion('Personal', 'ELIMINAR', "Eliminación del usuario ID #$userId ({$usuarioExistente['usuario']}) por el administrador.");
            $_SESSION['mensaje'] = 'Usuario eliminado correctamente del sistema.';
        } else {
            $_SESSION['error'] = 'No se pudo eliminar el usuario.';
        }

        header('Location: ' . BASE_URL . 'usuario/index');
        exit;
    }
}
