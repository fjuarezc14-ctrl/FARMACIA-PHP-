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
}
