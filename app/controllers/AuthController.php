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
        $bloqueo = 0;
        if ($_SERVER['REQUEST_METHOD'] == 'POST') {
            $this->validateCsrf();

            $userModel = $this->model('User');
            $username = $_POST['username'] ?? '';
            $password = $_POST['password'] ?? '';

            // Límite de intentos fallidos (fuerza bruta)
            $intentos = $this->model('LoginIntento');
            $usuarioKey = LoginIntento::normalizarUsuario($username);
            $ip = LoginIntento::ipCliente();

            $bloqueo = $intentos->segundosBloqueo($usuarioKey, $ip);
            if ($bloqueo > 0) {
                // Bloqueado: ni siquiera se verifica la contraseña
                $error = 'Demasiados intentos fallidos. Intente nuevamente en ' . LoginIntento::formatoEspera($bloqueo) . '.';
                $this->view('auth/login', ['error' => $error, 'bloqueo' => $bloqueo]);
                return;
            }

            $user = $userModel->login($username, $password);

            if ($user) {
                $intentos->limpiar($usuarioKey, $ip);

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
                $intentos->registrarFallo($usuarioKey, $ip);
                $bloqueo = $intentos->segundosBloqueo($usuarioKey, $ip);
                if ($bloqueo > 0) {
                    $error = 'Demasiados intentos fallidos. Acceso bloqueado por ' . LoginIntento::formatoEspera($bloqueo) . '.';
                } else {
                    $restantes = $intentos->intentosRestantes($usuarioKey, $ip);
                    $error = 'Usuario o contraseña incorrectos';
                    if ($restantes <= 3) {
                        $error .= $restantes === 1
                            ? '. Le queda 1 intento antes del bloqueo temporal.'
                            : ". Le quedan $restantes intentos antes del bloqueo temporal.";
                    }
                }
            }
        }

        $this->view('auth/login', ['error' => $error, 'bloqueo' => $bloqueo]);
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
