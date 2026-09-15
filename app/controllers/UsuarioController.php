<?php
class UsuarioController extends Controller {

    public function __construct() {
        // Solo administradores pueden gestionar usuarios
        if (!isset($_SESSION['user_id']) || $_SESSION['rol_id'] != 1) {
            header('Location: ' . BASE_URL . 'auth/login');
            exit;
        }
    }

    public function index() {
        $userModel = $this->model('User');
        $roleModel = $this->model('Role');
        
        $data = [
            'title' => 'Gestión de Personal',
            'usuarios' => $userModel->getAll(),
            'roles' => $roleModel->getAll()
        ];
        
        $this->view('usuarios/index', $data);
    }

    public function save() {
        if ($_SERVER['REQUEST_METHOD'] == 'POST') {
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

    public function toggle($id) {
        $userModel = $this->model('User');
        if($id == 1) {
            $_SESSION['error'] = "No se puede desactivar al Administrador principal.";
        } else {
            if ($userModel->toggleEstado($id)) {
                $_SESSION['mensaje'] = "Estado de usuario cambiado.";
            } else {
                $_SESSION['error'] = "Error al cambiar estado.";
            }
        }
        header('Location: ' . BASE_URL . 'usuario/index');
    }
}
