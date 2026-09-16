<?php
class AuthController extends Controller {

    public function index() {
        $this->requireAuth();
        if ((int)($_SESSION['rol_id'] ?? 0) === 1) {
            header('Location: ' . BASE_URL . 'dashboard/index');
        } else {
            header('Location: ' . BASE_URL . 'venta/pos');
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
            $this->validateCsrf();

            $userModel = $this->model('User');
            $username = $_POST['username'] ?? '';
            $password = $_POST['password'] ?? '';

            $user = $userModel->login($username, $password);

            if ($user) {
                // Regenerar id de sesión para mitigar Session Fixation
                session_regenerate_id(true);

                $_SESSION['user_id'] = $user['id'];
                $_SESSION['username'] = $user['usuario'];
                $_SESSION['nombre'] = $user['nombres'] . ' ' . $user['apellidos'];
                $_SESSION['rol_id'] = $user['rol_id'];
                // Regenerar token CSRF para el nuevo estado autenticado
                $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
                
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
        $_SESSION = [];
        if (ini_get("session.use_cookies")) {
            $params = session_get_cookie_params();
            setcookie(session_name(), '', time() - 42000,
                $params["path"], $params["domain"],
                $params["secure"], $params["httponly"]
            );
        }
        session_destroy();
        header('Location: ' . BASE_URL . 'auth/login');
        exit;
    }
}
