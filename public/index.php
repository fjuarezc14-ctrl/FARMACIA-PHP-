<?php
session_start();

// Definir BASE_URL globalmente
$isHttps = (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on') 
    || (isset($_SERVER['HTTP_X_FORWARDED_PROTO']) && $_SERVER['HTTP_X_FORWARDED_PROTO'] === 'https');
$protocol = $isHttps ? "https" : "http";
$host = $_SERVER['HTTP_HOST'];
$script = dirname($_SERVER['SCRIPT_NAME']);
$script = str_replace('\\', '/', $script); // fix para windows
define('BASE_URL', $protocol . '://' . $host . $script . '/');
define('BASE_PATH', dirname(__DIR__) . DIRECTORY_SEPARATOR);

require_once '../app/config/database.php';
require_once '../app/core/App.php';
require_once '../app/core/Controller.php';

// Inicializar la aplicación (Router)
$app = new App();
