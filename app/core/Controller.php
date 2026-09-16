<?php
class Controller {
    public function model($model) {
        require_once '../app/models/' . $model . '.php';
        return new $model();
    }

    public function view($view, $data = []) {
        if (file_exists('../app/views/' . $view . '.php')) {
            if (strpos($view, 'auth/') !== false) {
                require_once '../app/views/' . $view . '.php';
            } else {
                require_once '../app/views/layouts/main.php';
            }
        } else {
            die("La vista $view no existe.");
        }
    }

    protected function logAccion($modulo, $accion, $descripcion, $monto = 0) {
        if (isset($_SESSION['user_id'])) {
            $audit = $this->model('Auditoria');
            $audit->registrarAccion($_SESSION['user_id'], $modulo, $accion, $descripcion, $monto);
        }
    }

    /**
     * Verifica que el usuario haya iniciado sesión.
     * Si no está autenticado, redirige al login (o responde 401 en peticiones AJAX).
     */
    protected function requireAuth(): void {
        if (session_status() === PHP_SESSION_NONE && !headers_sent()) {
            session_start();
        }
        if (!isset($_SESSION['user_id'])) {
            if (!empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest') {
                http_response_code(401);
                header('Content-Type: application/json; charset=utf-8');
                echo json_encode([
                    'status' => 'error',
                    'message' => 'No autenticado. Por favor inicie sesión.'
                ]);
                exit;
            }
            $loginUrl = (defined('BASE_URL') ? BASE_URL : '/') . 'auth/login';
            header('Location: ' . $loginUrl);
            exit;
        }
    }

    /**
     * Comprueba si el usuario autenticado posee alguno de los roles autorizados.
     *
     * @param int|array $roles Rol o lista de roles permitidos.
     * @return bool
     */
    protected function hasRole($roles): bool {
        if (!isset($_SESSION['rol_id'])) {
            return false;
        }
        $allowed = is_array($roles) ? $roles : [$roles];
        return in_array((int)$_SESSION['rol_id'], $allowed, true);
    }

    /**
     * Exige que el usuario autenticado posea un rol autorizado.
     * Si no cumple los permisos:
     * 1. Registra el evento en logs del servidor y tabla de auditoría.
     * 2. Asigna un mensaje de error flash en $_SESSION['error'].
     * 3. Responde HTTP 403 para llamadas AJAX o redirige al usuario a una ruta autorizada.
     *
     * @param int|array $allowedRoles Roles autorizados (1 = Admin, 2 = Farmacéutico, 3 = Cajero, 4 = Almacenero).
     * @param string $redirectUrl Ruta de redirección relativa a BASE_URL en caso de denegación.
     */
    protected function requireRole($allowedRoles = 1, string $redirectUrl = 'venta/pos'): void {
        $this->requireAuth();

        if (!$this->hasRole($allowedRoles)) {
            $userRole = (int)($_SESSION['rol_id'] ?? 0);
            $userId = $_SESSION['user_id'] ?? 'desconocido';
            $requestUri = $_SERVER['REQUEST_URI'] ?? '';

            error_log("[Acceso Denegado] Usuario ID: {$userId} (Rol: {$userRole}) intentó acceder sin permisos a: {$requestUri}");
            $this->logAccion('SEGURIDAD', 'ACCESO_DENEGADO', "Acceso denegado a {$requestUri} por falta de privilegios (Rol: {$userRole})");

            $_SESSION['error'] = 'Acceso denegado: No cuenta con permisos suficientes para acceder a este módulo.';

            if (!empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest') {
                http_response_code(403);
                header('Content-Type: application/json; charset=utf-8');
                echo json_encode([
                    'status' => 'error',
                    'message' => 'Acceso denegado: Privilegios insuficientes.'
                ]);
                exit;
            }

            $targetUrl = (defined('BASE_URL') ? BASE_URL : '/') . $redirectUrl;
            header('Location: ' . $targetUrl);
            exit;
        }
    }

    /**
     * Genera o recupera el token CSRF activo de la sesión actual.
     * Utiliza un generador criptográficamente seguro (random_bytes).
     *
     * @return string Token CSRF hexadecimal de 64 caracteres.
     */
    public static function generateCsrfToken(): string {
        if (session_status() === PHP_SESSION_NONE && !headers_sent()) {
            session_start();
        }
        if (empty($_SESSION['csrf_token'])) {
            $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
        }
        return $_SESSION['csrf_token'];
    }

    /**
     * Alias para obtener el token CSRF actual.
     */
    public static function csrfToken(): string {
        return self::generateCsrfToken();
    }

    /**
     * Renderiza un campo HTML input oculto con el token CSRF sanitizado.
     *
     * @return string Etiqueta <input type="hidden"> lista para incrustar en formularios.
     */
    public static function csrfField(): string {
        $token = self::generateCsrfToken();
        return '<input type="hidden" name="csrf_token" value="' . htmlspecialchars($token, ENT_QUOTES, 'UTF-8') . '">';
    }

    /**
     * Valida de manera estricta y segura contra ataques de temporización (timing-safe)
     * el token CSRF recibido en solicitudes mutativas (POST).
     * Si la validación falla:
     * 1. Registra la alerta en el log del servidor y en la auditoría.
     * 2. Establece un mensaje flash de error para el usuario.
     * 3. Termina la ejecución y redirige con seguridad (o responde 403 en solicitudes AJAX).
     */
    protected function validateCsrf(): void {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $token = $_POST['csrf_token'] ?? $_SERVER['HTTP_X_CSRF_TOKEN'] ?? '';
            $sessionToken = $_SESSION['csrf_token'] ?? '';

            if (empty($token) || empty($sessionToken) || !hash_equals($sessionToken, $token)) {
                $clientIp = $_SERVER['REMOTE_ADDR'] ?? '127.0.0.1';
                $requestUri = $_SERVER['REQUEST_URI'] ?? '';
                error_log("[CSRF Security Alert] Validación fallida. IP: {$clientIp}, URI: {$requestUri}");

                if (isset($_SESSION['user_id'])) {
                    $this->logAccion('SEGURIDAD', 'CSRF_BLOCKED', "Intento de solicitud POST bloqueado por token CSRF inválido en URI: {$requestUri}");
                }

                $_SESSION['error'] = 'Error de seguridad (CSRF): La solicitud no es válida o su sesión ha expirado. Por favor, recargue la página e inténtelo nuevamente.';
                $_SESSION['error_pos'] = 'Error de seguridad (CSRF): Token no válido o sesión expirada.';

                // Si es petición asíncrona o AJAX
                if (!empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest') {
                    http_response_code(403);
                    header('Content-Type: application/json; charset=utf-8');
                    echo json_encode([
                        'status' => 'error',
                        'message' => 'Token CSRF inválido o sesión expirada.'
                    ]);
                    exit;
                }

                // Redireccionar a la página anterior o a la raíz
                $referer = $_SERVER['HTTP_REFERER'] ?? (defined('BASE_URL') ? BASE_URL : '/');
                header("Location: " . $referer);
                exit;
            }
        }
    }
}

// Helpers globales para mayor conveniencia en vistas
if (!function_exists('csrf_token')) {
    function csrf_token(): string {
        return Controller::generateCsrfToken();
    }
}

if (!function_exists('csrf_field')) {
    function csrf_field(): string {
        return Controller::csrfField();
    }
}
