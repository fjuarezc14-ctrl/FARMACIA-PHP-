<?php
class PerfilController extends Controller {

    public function __construct() {
        $this->requireAuth();
    }

    public function index() {
        $userModel = $this->model('User');
        $usuario = $userModel->getById($_SESSION['user_id']);
        
        $data = [
            'title' => 'Mi Perfil',
            'usuario' => $usuario
        ];
        
        $this->view('usuarios/perfil', $data);
    }

    public function update() {
        if ($_SERVER['REQUEST_METHOD'] == 'POST') {
            $this->validateCsrf();
            $userModel = $this->model('User');
            $id = $_SESSION['user_id'];
            
            $data = [
                'nombres' => trim($_POST['nombres']),
                'apellidos' => trim($_POST['apellidos']),
                'email' => trim($_POST['email'])
            ];
            
            if ($userModel->updateProfile($id, $data)) {
                // Actualizar variables de sesión
                $_SESSION['nombre'] = $data['nombres'] . ' ' . $data['apellidos'];
                $_SESSION['mensaje_perfil'] = "Perfil actualizado exitosamente.";
            } else {
                $_SESSION['error_perfil'] = "Error al actualizar el perfil.";
            }
        }
        header('Location: ' . BASE_URL . 'perfil/index');
    }

    public function updatePassword() {
        if ($_SERVER['REQUEST_METHOD'] == 'POST') {
            $this->validateCsrf();
            $userModel = $this->model('User');
            $id = $_SESSION['user_id'];
            
            $current_password = $_POST['current_password'];
            $new_password = $_POST['new_password'];
            $confirm_password = $_POST['confirm_password'];
            
            if ($new_password !== $confirm_password) {
                $_SESSION['error_perfil'] = "Las contraseñas nuevas no coinciden.";
            } else {
                if ($userModel->verifyPassword($id, $current_password)) {
                    if ($userModel->updatePassword($id, $new_password)) {
                        $_SESSION['mensaje_perfil'] = "Contraseña actualizada exitosamente.";
                    } else {
                        $_SESSION['error_perfil'] = "Error al actualizar la contraseña.";
                    }
                } else {
                    $_SESSION['error_perfil'] = "La contraseña actual es incorrecta.";
                }
            }
        }
        header('Location: ' . BASE_URL . 'perfil/index');
    }
}
