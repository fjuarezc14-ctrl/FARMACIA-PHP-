<?php
session_start();

// Definir BASE_URL globalmente
$protocol = isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? "https" : "http";
$host = $_SERVER['HTTP_HOST'];
$script = dirname($_SERVER['SCRIPT_NAME']);
$script = str_replace('\\', '/', $script); // fix para windows
define('BASE_URL', $protocol . '://' . $host . $script . '/');

require_once '../app/config/database.php';
require_once '../app/core/App.php';
require_once '../app/core/Controller.php';

// Inicializar la aplicación (Router)
$app = new App();
