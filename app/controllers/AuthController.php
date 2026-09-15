<?php
class AuthController extends Controller {

    public function index() {
        if (!isset($_SESSION['user_id'])) {
            header('Location: ' . BASE_URL . 'auth/login');
            exit;
        }
        if ($_SESSION['rol_id'] == 2) {
            header('Location: ' . BASE_URL . 'venta/pos');
        } else {
            header('Location: ' . BASE_URL . 'dashboard/index');
        }
        exit;
    }

    public function login() {
        if (isset($_SESSION['user_id'])) {
            header('Location: ' . BASE_URL . 'auth/index');
            exit;
        }

        $error = '';
        if ($_SERVER['REQUEST_METHOD'] == 'POST') {
            $userModel = $this->model('User');
            $username = $_POST['username'] ?? '';
            $password = $_POST['password'] ?? '';

            $user = $userModel->login($username, $password);

            if ($user) {
                $_SESSION['user_id'] = $user['id'];
                $_SESSION['username'] = $user['usuario'];
                $_SESSION['nombre'] = $user['nombres'] . ' ' . $user['apellidos'];
                $_SESSION['rol_id'] = $user['rol_id'];
                
                $userModel->updateLastLogin($user['id']);
                
                // Registro de Auditoría
                $auditModel = $this->model('Auditoria');
                $auditModel->registrarAcceso($user['id'], 'LOGIN');
                
                header('Location: ' . BASE_URL . 'auth/index');
                exit;
            } else {
                $error = 'Usuario o contraseña incorrectos';
            }
        }

        $this->view('auth/login', ['error' => $error]);
    }

    public function logout() {
        if (isset($_SESSION['user_id'])) {
            $auditModel = $this->model('Auditoria');
            $auditModel->registrarAcceso($_SESSION['user_id'], 'LOGOUT');
        }
        session_destroy();
        header('Location: ' . BASE_URL . 'auth/login');
        exit;
    }
}
