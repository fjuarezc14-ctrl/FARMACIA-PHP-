<?php
/**
 * End-to-End HTTP test for Role-Based Access Control (RBAC)
 */
require_once __DIR__ . '/../app/config/database.php';
require_once __DIR__ . '/../app/models/User.php';

$baseUrl = "http://localhost/";

echo "======================================================\n";
echo "   SUITE E2E HTTP: CONTROL DE ROLES (RBAC)           \n";
echo "======================================================\n\n";

$testsPassed = 0;
$testsFailed = 0;

function assertE2E($condition, $testName) {
    global $testsPassed, $testsFailed;
    if ($condition) {
        echo "[PASS] $testName\n";
        $testsPassed++;
    } else {
        echo "[FAIL] $testName\n";
        $testsFailed++;
    }
}

// 1. Tests SIN SESION (No autenticado)
echo "--- 1. Pruebas de Acceso No Autenticado (Anónimo) ---\n";

function requestHttp($url, $cookieFile = null, $ajax = false, $postFields = null) {
    $ch = curl_init($url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_FOLLOWLOCATION, false);
    
    $headers = [];
    if ($ajax) {
        $headers[] = 'X-Requested-With: XMLHttpRequest';
    }
    if (!empty($headers)) {
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
    }
    
    if ($cookieFile) {
        curl_setopt($ch, CURLOPT_COOKIEJAR, $cookieFile);
        curl_setopt($ch, CURLOPT_COOKIEFILE, $cookieFile);
    }
    
    if ($postFields !== null) {
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $postFields);
    }
    
    $response = curl_exec($ch);
    $code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    $redirect = curl_getinfo($ch, CURLINFO_REDIRECT_URL);
    curl_close($ch);
    
    return ['code' => $code, 'redirect' => $redirect, 'body' => $response];
}

// 1.1 Solicitud ordinaria sin sesión a /producto/index
$resAnon = requestHttp($baseUrl . 'producto/index');
assertE2E($resAnon['code'] === 302 && str_contains($resAnon['redirect'], 'auth/login'),
    "Acceso anónimo a /producto/index es redirigido con 302 a auth/login");

// 1.2 Solicitud AJAX sin sesión a /categoria/index
$resAnonAjax = requestHttp($baseUrl . 'categoria/index', null, true);
assertE2E($resAnonAjax['code'] === 401 && str_contains($resAnonAjax['body'], 'No autenticado'),
    "Acceso AJAX anónimo devuelve HTTP 401 No Autenticado");

// 2. Preparar sesión autenticada como CAJERO
echo "\n--- 2. Pruebas de Acceso con Rol Cajero (rol_id = 3) ---\n";
$userModel = new User();
// Buscar o actualizar la clave de cajero1 a algo conocido si es necesario
$db = new Database();
$conn = $db->getConnection();
$testHash = password_hash('cajero123', PASSWORD_BCRYPT);
$conn->prepare("UPDATE usuarios SET password = ? WHERE usuario = 'cajero1'")->execute([$testHash]);

$cajeroCookies = __DIR__ . '/cajero_cookies.txt';
if (file_exists($cajeroCookies)) unlink($cajeroCookies);

// 2.1 Obtener CSRF token para login
$loginPage = requestHttp($baseUrl . 'auth/login', $cajeroCookies);
preg_match('/name="csrf_token" value="([a-f0-9]{64})"/', $loginPage['body'], $tokenMatch);
$csrfToken = $tokenMatch[1] ?? '';

// 2.2 Iniciar sesión como cajero1
$loginRes = requestHttp($baseUrl . 'auth/login', $cajeroCookies, false, http_build_query([
    'username' => 'cajero1',
    'password' => 'cajero123',
    'csrf_token' => $csrfToken
]));

// AuthController redirige a auth/index, y auth/index redirige a venta/pos para cajero
assertE2E($loginRes['code'] === 302, "Login de cajero1 exitoso (redirección 302)");

// 2.3 Probar acceso de cajero a módulos exclusivamente de ADMIN
$adminModules = [
    'dashboard/index'        => 'venta/pos',
    'producto/index'         => 'venta/pos',
    'categoria/index'        => 'venta/pos',
    'laboratorio/index'      => 'venta/pos',
    'proveedor/index'        => 'venta/pos',
    'compra/index'           => 'venta/pos',
    'inventario/kardex'      => 'venta/pos',
    'inventariofisico/index' => 'venta/pos',
    'notificacion/index'     => 'venta/pos',
    'reporte/index'          => 'venta/pos',
    'usuario/index'          => 'venta/pos',
    'configuracion/index'    => 'venta/pos',
    'sistema/index'          => 'venta/pos',
    'auditoria/index'        => 'venta/pos',
    'caja/index'             => 'venta/pos',
    'cliente/delete/1'       => 'cliente/index',
    'venta/anular/1'         => 'venta/index'
];

foreach ($adminModules as $route => $expectedRedirect) {
    $res = requestHttp($baseUrl . $route, $cajeroCookies);
    $passed = ($res['code'] === 302 && str_contains($res['redirect'], $expectedRedirect));
    assertE2E($passed, "Cajero bloqueado en /$route -> redirigido a $expectedRedirect");
}

// 2.4 Probar petición AJAX de Cajero a módulo Admin
$resCajeroAjax = requestHttp($baseUrl . 'categoria/index', $cajeroCookies, true);
assertE2E($resCajeroAjax['code'] === 403 && str_contains($resCajeroAjax['body'], 'Privilegios insuficientes'),
    "Petición AJAX de Cajero a /categoria/index responde HTTP 403 Forbidden");

// 2.5 Probar endpoints que el cajero SI puede acceder
$resCajeroPos = requestHttp($baseUrl . 'venta/index', $cajeroCookies);
assertE2E($resCajeroPos['code'] === 200, "Cajero tiene acceso permitido a /venta/index (HTTP 200)");

$resCajeroClientes = requestHttp($baseUrl . 'cliente/index', $cajeroCookies);
assertE2E($resCajeroClientes['code'] === 200, "Cajero tiene acceso permitido a /cliente/index (HTTP 200)");

// 3. Preparar sesión autenticada como ADMINISTRADOR
echo "\n--- 3. Pruebas de Acceso con Rol Administrador (rol_id = 1) ---\n";
$adminHash = password_hash('admin123', PASSWORD_BCRYPT);
$conn->prepare("UPDATE usuarios SET password = ? WHERE usuario = 'admin'")->execute([$adminHash]);

$adminCookies = __DIR__ . '/admin_cookies.txt';
if (file_exists($adminCookies)) unlink($adminCookies);

$loginPageAdmin = requestHttp($baseUrl . 'auth/login', $adminCookies);
preg_match('/name="csrf_token" value="([a-f0-9]{64})"/', $loginPageAdmin['body'], $adminTokenMatch);
$csrfTokenAdmin = $adminTokenMatch[1] ?? '';

$loginResAdmin = requestHttp($baseUrl . 'auth/login', $adminCookies, false, http_build_query([
    'username' => 'admin',
    'password' => 'admin123',
    'csrf_token' => $csrfTokenAdmin
]));

assertE2E($loginResAdmin['code'] === 302, "Login de admin exitoso (redirección 302)");

// 3.1 Probar acceso de Admin a los módulos protegidos
$adminAccessible = [
    'dashboard/index',
    'producto/index',
    'categoria/index',
    'laboratorio/index',
    'proveedor/index',
    'compra/index',
    'inventario/kardex',
    'inventariofisico/index',
    'notificacion/index',
    'reporte/index',
    'usuario/index',
    'configuracion/index',
    'sistema/index',
    'auditoria/index',
    'caja/index'
];

foreach ($adminAccessible as $route) {
    $res = requestHttp($baseUrl . $route, $adminCookies);
    assertE2E($res['code'] === 200, "Admin accede con HTTP 200 a /$route");
}

// 4. Verificar Registro de Auditoría de Intentos Bloqueados
echo "\n--- 4. Verificación en Tabla de Auditoría (audit_acciones) ---\n";
$stmtAudit = $conn->query("SELECT count(*) as total FROM audit_acciones WHERE modulo = 'SEGURIDAD' AND accion = 'ACCESO_DENEGADO'");
$auditCount = $stmtAudit->fetch(PDO::FETCH_ASSOC)['total'] ?? 0;
assertE2E($auditCount > 0, "Se registraron $auditCount intentos de acceso denegado en la tabla audit_acciones");

// Limpiar archivos de cookies
if (file_exists($cajeroCookies)) unlink($cajeroCookies);
if (file_exists($adminCookies)) unlink($adminCookies);

echo "\n======================================================\n";
echo "RESULTADOS E2E: $testsPassed PASSED, $testsFailed FAILED\n";
echo "======================================================\n";

if ($testsFailed > 0) {
    exit(1);
}
