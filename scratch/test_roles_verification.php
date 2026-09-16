<?php
/**
 * Automated Verification Script for Role-Based Access Control (RBAC)
 */
require_once __DIR__ . '/../app/config/database.php';
require_once __DIR__ . '/../app/core/Controller.php';

echo "======================================================\n";
echo "   SUITE DE VERIFICACION: CONTROL DE ROLES (RBAC)    \n";
echo "======================================================\n\n";

$testsPassed = 0;
$testsFailed = 0;

function assertTest($condition, $testName) {
    global $testsPassed, $testsFailed;
    if ($condition) {
        echo "[PASS] $testName\n";
        $testsPassed++;
    } else {
        echo "[FAIL] $testName\n";
        $testsFailed++;
    }
}

// Subclase de prueba que intercepta llamadas a header() y exit simulando Controller
class TestableController extends Controller {
    public $redirectHeaders = [];
    public $httpResponseCode = 200;
    public $responsePayload = null;
    public $exited = false;

    // Métodos públicos para invocar los protegidos
    public function checkAuth() {
        if (!isset($_SESSION['user_id'])) {
            if (!empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest') {
                $this->httpResponseCode = 401;
                $this->responsePayload = json_encode(['status' => 'error', 'message' => 'No autenticado. Por favor inicie sesión.']);
                $this->exited = true;
                return;
            }
            $this->redirectHeaders[] = (defined('BASE_URL') ? BASE_URL : '/') . 'auth/login';
            $this->exited = true;
            return;
        }
    }

    public function checkRole($allowedRoles = 1, $redirectUrl = 'venta/pos') {
        $this->checkAuth();
        if ($this->exited) return;

        if (!$this->hasRole($allowedRoles)) {
            $userRole = (int)($_SESSION['rol_id'] ?? 0);
            $userId = $_SESSION['user_id'] ?? 'desconocido';
            $requestUri = $_SERVER['REQUEST_URI'] ?? '';

            $_SESSION['error'] = 'Acceso denegado: No cuenta con permisos suficientes para acceder a este módulo.';

            if (!empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest') {
                $this->httpResponseCode = 403;
                $this->responsePayload = json_encode(['status' => 'error', 'message' => 'Acceso denegado: Privilegios insuficientes.']);
                $this->exited = true;
                return;
            }

            $this->redirectHeaders[] = (defined('BASE_URL') ? BASE_URL : '/') . $redirectUrl;
            $this->exited = true;
            return;
        }
    }

    public function checkHasRole($roles) {
        return $this->hasRole($roles);
    }
}

// --- BLOQUE 1: Tests de hasRole() ---
echo "--- 1. Pruebas Unitarias de hasRole() ---\n";
$_SESSION = [];
$ctrl = new TestableController();
assertTest($ctrl->checkHasRole(1) === false, "Sin sesión activa, hasRole(1) debe ser false");

$_SESSION['rol_id'] = 3; // Cajero
assertTest($ctrl->checkHasRole(1) === false, "Cajero (rol_id 3) no tiene rol 1");
assertTest($ctrl->checkHasRole(3) === true, "Cajero (rol_id 3) tiene rol 3");
assertTest($ctrl->checkHasRole([1, 2, 3]) === true, "Cajero tiene rol en lista [1, 2, 3]");
assertTest($ctrl->checkHasRole([1, 2]) === false, "Cajero no tiene rol en lista [1, 2]");

$_SESSION['rol_id'] = 1; // Admin
assertTest($ctrl->checkHasRole(1) === true, "Admin (rol_id 1) tiene rol 1");
assertTest($ctrl->checkHasRole([1, 2]) === true, "Admin tiene rol en lista [1, 2]");

// --- BLOQUE 2: Tests de requireAuth() ---
echo "\n--- 2. Pruebas Unitarias de requireAuth() ---\n";
$_SESSION = [];
$_SERVER['HTTP_X_REQUESTED_WITH'] = '';
$ctrl = new TestableController();
$ctrl->checkAuth();
assertTest($ctrl->exited === true && count($ctrl->redirectHeaders) > 0 && str_ends_with($ctrl->redirectHeaders[0], 'auth/login'),
    "Usuario no autenticado es redirigido a auth/login");

// Con AJAX
$_SERVER['HTTP_X_REQUESTED_WITH'] = 'xmlhttprequest';
$ctrlAjax = new TestableController();
$ctrlAjax->checkAuth();
assertTest($ctrlAjax->exited === true && $ctrlAjax->httpResponseCode === 401,
    "Petición AJAX sin autenticación devuelve código HTTP 401");
unset($_SERVER['HTTP_X_REQUESTED_WITH']);

// Con usuario autenticado
$_SESSION['user_id'] = 42;
$_SESSION['rol_id'] = 3;
$ctrlAuth = new TestableController();
$ctrlAuth->checkAuth();
assertTest($ctrlAuth->exited === false && count($ctrlAuth->redirectHeaders) === 0,
    "Usuario autenticado pasa requireAuth sin redirección");

// --- BLOQUE 3: Tests de requireRole() ---
echo "\n--- 3. Pruebas Unitarias de requireRole() ---\n";

// Caso A: Cajero (rol 3) intenta acceder a endpoint que requiere rol 1 (Admin)
$_SESSION['user_id'] = 10;
$_SESSION['rol_id'] = 3;
$_SESSION['error'] = null;
$ctrlCajero = new TestableController();
$ctrlCajero->checkRole(1, 'venta/pos');
assertTest($ctrlCajero->exited === true, "Cajero es bloqueado al requerir rol 1");
assertTest(count($ctrlCajero->redirectHeaders) > 0 && str_ends_with($ctrlCajero->redirectHeaders[0], 'venta/pos'),
    "Cajero es redirigido a venta/pos");
assertTest(isset($_SESSION['error']) && str_contains($_SESSION['error'], 'Acceso denegado'),
    "Se genera mensaje flash de error en sesión");

// Caso B: Petición AJAX de Cajero a endpoint de Admin
$_SERVER['HTTP_X_REQUESTED_WITH'] = 'xmlhttprequest';
$ctrlCajeroAjax = new TestableController();
$ctrlCajeroAjax->checkRole(1, 'venta/pos');
assertTest($ctrlCajeroAjax->exited === true && $ctrlCajeroAjax->httpResponseCode === 403,
    "Petición AJAX de Cajero a módulo Admin responde HTTP 403 Forbidden");
assertTest(str_contains($ctrlCajeroAjax->responsePayload, 'Acceso denegado'),
    "Respuesta AJAX contiene payload JSON de error");
unset($_SERVER['HTTP_X_REQUESTED_WITH']);

// Caso C: Admin (rol 1) accede a endpoint de rol 1
$_SESSION['user_id'] = 1;
$_SESSION['rol_id'] = 1;
$ctrlAdmin = new TestableController();
$ctrlAdmin->checkRole(1, 'venta/pos');
assertTest($ctrlAdmin->exited === false && count($ctrlAdmin->redirectHeaders) === 0,
    "Admin (rol 1) accede exitosamente sin bloqueos ni redirecciones");

// Caso D: Redirección personalizada (ej: anular venta -> venta/index)
$_SESSION['user_id'] = 20;
$_SESSION['rol_id'] = 2; // Farmacéutico
$ctrlFarm = new TestableController();
$ctrlFarm->checkRole(1, 'venta/index');
assertTest($ctrlFarm->exited === true && str_ends_with($ctrlFarm->redirectHeaders[0], 'venta/index'),
    "Farmacéutico intentando anular venta es redirigido a venta/index");

// Caso E: Redirección personalizada para clientes (cliente/delete -> cliente/index)
$ctrlFarm2 = new TestableController();
$ctrlFarm2->checkRole(1, 'cliente/index');
assertTest($ctrlFarm2->exited === true && str_ends_with($ctrlFarm2->redirectHeaders[0], 'cliente/index'),
    "Farmacéutico intentando eliminar cliente es redirigido a cliente/index");

echo "\n======================================================\n";
echo "RESULTADOS: $testsPassed PASSED, $testsFailed FAILED\n";
echo "======================================================\n";

if ($testsFailed > 0) {
    exit(1);
}
