<?php
class App {
    protected $controller = 'AuthController';
    protected $method = 'login';
    protected $params = [];

    public function __construct() {
        $url = $this->parseUrl();

        if (isset($url[0])) {
            $directFile = '../app/controllers/' . ucfirst($url[0]) . 'Controller.php';
            if (file_exists($directFile)) {
                $this->controller = ucfirst($url[0]) . 'Controller';
                unset($url[0]);
            } else {
                // Búsqueda insensible a mayúsculas/minúsculas para compatibilidad Linux/Docker
                $normalized = strtolower(str_replace(['_', '-'], '', $url[0])) . 'controller.php';
                $ctrlFiles = glob('../app/controllers/*Controller.php');
                if ($ctrlFiles) {
                    foreach ($ctrlFiles as $f) {
                        if (strtolower(basename($f)) === $normalized) {
                            $this->controller = basename($f, '.php');
                            unset($url[0]);
                            break;
                        }
                    }
                }
            }
        }

        require_once '../app/controllers/' . $this->controller . '.php';
        $this->controller = new $this->controller;

        if (isset($url[1])) {
            if (method_exists($this->controller, $url[1])) {
                $this->method = $url[1];
                unset($url[1]);
            } else {
                // Verificación insensible a mayúsculas para métodos
                $methods = get_class_methods($this->controller);
                if ($methods) {
                    foreach ($methods as $m) {
                        if (strcasecmp($m, $url[1]) === 0) {
                            $this->method = $m;
                            unset($url[1]);
                            break;
                        }
                    }
                }
            }
        }

        $this->params = $url ? array_values($url) : [];

        try {
            call_user_func_array([$this->controller, $this->method], $this->params);
        } catch (\Throwable $e) {
            error_log("[Application Router Exception] " . $e->getMessage() . " in " . $e->getFile() . ":" . $e->getLine());

            if (!headers_sent()) {
                http_response_code(500);
            }

            // Si es petición AJAX, devolver JSON estructurado sin fugar trazas
            if (!empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest') {
                header('Content-Type: application/json; charset=utf-8');
                echo json_encode([
                    'status' => 'error',
                    'message' => 'Ocurrió un error interno al procesar la solicitud. El incidente ha sido registrado.'
                ]);
                exit;
            }

            // Página HTML amigable institucional de Error 500
            header('Content-Type: text/html; charset=utf-8');
            ?>
            <!DOCTYPE html>
            <html lang="es">
            <head>
                <meta charset="UTF-8">
                <title>Error del Sistema (500) | CENGFARMA</title>
                <meta name="viewport" content="width=device-width, initial-scale=1.0">
                <style>
                    body { font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif; background: #FFF3EC; color: #1A2238; display: flex; align-items: center; justify-content: center; min-height: 100vh; margin: 0; padding: 20px; box-sizing: border-box; }
                    .card { background: #FFFFFF; padding: 40px; border-radius: 16px; box-shadow: 0 4px 25px rgba(0,0,0,0.06); text-align: center; max-width: 500px; border: 1px solid #E8DFD8; }
                    .code { font-size: 48px; font-weight: 800; color: #DC2626; margin: 0 0 10px 0; }
                    h2 { color: #1A2238; margin-top: 0; margin-bottom: 12px; font-weight: 700; font-size: 20px; }
                    p { color: #5A6478; line-height: 1.5; font-size: 14px; margin: 8px 0; }
                    .btn { display: inline-block; background: #047B07; color: white; padding: 12px 26px; border-radius: 10px; text-decoration: none; font-weight: 700; margin-top: 20px; font-size: 14px; border: none; cursor: pointer; transition: background 0.2s; }
                    .btn:hover { background: #035E05; }
                </style>
            </head>
            <body>
                <div class="card">
                    <div class="code">500</div>
                    <h2>Error Inesperado en el Sistema</h2>
                    <p>Ha ocurrido un problema al procesar su solicitud. El equipo técnico ha sido notificado a través de los registros del servidor.</p>
                    <a href="<?php echo defined('BASE_URL') ? BASE_URL : '/'; ?>" class="btn">Volver al Inicio</a>
                </div>
            </body>
            </html>
            <?php
            exit;
        }
    }

    public function parseUrl() {
        if (isset($_GET['url'])) {
            return explode('/', filter_var(rtrim($_GET['url'], '/'), FILTER_SANITIZE_URL));
        }
        return [];
    }
}
