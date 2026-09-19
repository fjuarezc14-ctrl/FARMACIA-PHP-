<?php
// Configuración de reporte de errores para producción
$isDev = (getenv('APP_ENV') === 'development' || getenv('APP_DEBUG') === 'true');
if ($isDev) {
    error_reporting(E_ALL);
    ini_set('display_errors', '1');
} else {
    error_reporting(0);
    ini_set('display_errors', '0');
}
ini_set('log_errors', '1');

// Cabeceras HTTP de Seguridad (OWASP Hardening)
if (!headers_sent()) {
    header('X-Frame-Options: SAMEORIGIN');
    header('X-Content-Type-Options: nosniff');
    header('Referrer-Policy: strict-origin-when-cross-origin');
    header('X-XSS-Protection: 1; mode=block');
}

// Configuración segura de sesión
if (session_status() === PHP_SESSION_NONE && !headers_sent()) {
    $isSecureCookie = (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on') 
        || (isset($_SERVER['HTTP_X_FORWARDED_PROTO']) && $_SERVER['HTTP_X_FORWARDED_PROTO'] === 'https');
    session_set_cookie_params([
        'lifetime' => 0,
        'path'     => '/',
        'secure'   => $isSecureCookie,
        'httponly' => true,
        'samesite' => 'Lax'
    ]);
    session_start();
}

// Definir BASE_URL globalmente con normalización para DocumentRoot
$isHttps = (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on') 
    || (isset($_SERVER['HTTP_X_FORWARDED_PROTO']) && $_SERVER['HTTP_X_FORWARDED_PROTO'] === 'https');
$protocol = $isHttps ? "https" : "http";
$host = $_SERVER['HTTP_HOST'];
$script = dirname($_SERVER['SCRIPT_NAME']);
$script = str_replace('\\', '/', $script); // fix para windows
$basePrefix = ($script === '/' || $script === '.') ? '' : rtrim($script, '/');
define('BASE_URL', $protocol . '://' . $host . $basePrefix . '/');
define('BASE_PATH', dirname(__DIR__) . DIRECTORY_SEPARATOR);

require_once '../app/config/database.php';
require_once '../app/core/App.php';
require_once '../app/core/Controller.php';

// Inicializar la aplicación (Router)
$app = new App();
