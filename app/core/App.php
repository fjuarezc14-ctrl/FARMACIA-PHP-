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

        call_user_func_array([$this->controller, $this->method], $this->params);
    }

    public function parseUrl() {
        if (isset($_GET['url'])) {
            return explode('/', filter_var(rtrim($_GET['url'], '/'), FILTER_SANITIZE_URL));
        }
        return [];
    }
}
