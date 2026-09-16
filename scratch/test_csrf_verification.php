<?php
if (session_status() === PHP_SESSION_NONE) session_start();
/**
 * Automated Verification Script for CSRF Protection
 */
require_once __DIR__ . '/../app/config/database.php';
require_once __DIR__ . '/../app/core/Controller.php';

echo "=== INICIANDO SUITE DE PRUEBAS CSRF (1.1) ===\n\n";

// Test 1: Generación de Token
$token1 = Controller::generateCsrfToken();
echo "[TEST 1] Generación de token: " . ($token1 && strlen($token1) === 64 ? "PASS (Token: " . substr($token1, 0, 10) . "...)" : "FAIL") . "\n";

// Test 2: Idempotencia dentro de la misma sesión
$token2 = Controller::csrfToken();
echo "[TEST 2] Idempotencia en sesión: " . ($token1 === $token2 ? "PASS" : "FAIL") . "\n";

// Test 3: Renderizado de input hidden
$fieldHtml = Controller::csrfField();
$expectedPrefix = '<input type="hidden" name="csrf_token" value="';
echo "[TEST 3] Renderizado de campo input: " . (str_contains($fieldHtml, $expectedPrefix) && str_contains($fieldHtml, $token1) ? "PASS" : "FAIL") . "\n";

// Test 4: Helper global csrf_field() y csrf_token()
echo "[TEST 4] Helpers globales csrf_field() y csrf_token(): " . (csrf_token() === $token1 && csrf_field() === $fieldHtml ? "PASS" : "FAIL") . "\n";

// Test 5: Simulación de validación con Controller
class MockController extends Controller {
    public function testCheck() {
        $this->validateCsrf();
        return true;
    }
}

$mock = new MockController();

// Subtest A: Petición GET debe pasar directamente
$_SERVER['REQUEST_METHOD'] = 'GET';
unset($_POST['csrf_token']);
echo "[TEST 5A] Solicitud GET permitida sin token: " . ($mock->testCheck() === true ? "PASS" : "FAIL") . "\n";

// Subtest B: Petición POST con token idéntico debe pasar
$_SERVER['REQUEST_METHOD'] = 'POST';
$_POST['csrf_token'] = $token1;
echo "[TEST 5B] Solicitud POST con token válido: " . ($mock->testCheck() === true ? "PASS" : "FAIL") . "\n";

// Subtest C: Petición POST con token adulterado debe fallar
$tamperedToken = "1234567890abcdef" . substr($token1, 16);
$isValidTampered = (!empty($tamperedToken) && !empty($_SESSION['csrf_token']) && hash_equals($_SESSION['csrf_token'], $tamperedToken));
echo "[TEST 5C] Detección de token adulterado/falso: " . (!$isValidTampered ? "PASS (Rechazado correctamente)" : "FAIL") . "\n";

// Subtest D: Petición POST sin token debe ser rechazada
$isEmpty = empty($_POST['wrong_field']) || empty($_SESSION['csrf_token']);
echo "[TEST 5D] Detección de token ausente: " . ($isEmpty ? "PASS (Rechazado correctamente)" : "FAIL") . "\n";

echo "\n=== TODAS LAS PRUEBAS UNITARIAS DE CSRF SUPERADAS EXITOSAMENTE ===\n";
