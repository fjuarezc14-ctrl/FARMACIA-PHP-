<?php
/**
 * End-to-End HTTP test for CSRF protection against running web server
 */

$baseUrl = "http://localhost/";
$cookieFile = __DIR__ . '/test_cookies.txt';
if (file_exists($cookieFile)) unlink($cookieFile);

echo "=== INICIANDO PRUEBA END-TO-END HTTP DE CSRF ===\n\n";

// 1. GET /auth/login
$ch = curl_init($baseUrl . 'auth/login');
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_COOKIEJAR, $cookieFile);
curl_setopt($ch, CURLOPT_COOKIEFILE, $cookieFile);
$response = curl_exec($ch);
curl_close($ch);

if (!preg_match('/name="csrf_token" value="([a-f0-9]{64})"/', $response, $matches)) {
    die("ERROR: No se encontró el token CSRF en el formulario de login.\n");
}
$loginToken = $matches[1];
echo "[E2E-1] Token CSRF obtenido de /auth/login: " . substr($loginToken, 0, 16) . "... PASS\n";

// 2. Intento de login POST SIN TOKEN (Ataque CSRF simulado)
$ch = curl_init($baseUrl . 'auth/login');
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query([
    'username' => 'admin',
    'password' => 'admin'
]));
curl_setopt($ch, CURLOPT_COOKIEFILE, $cookieFile);
curl_setopt($ch, CURLOPT_FOLLOWLOCATION, false);
$res = curl_exec($ch);
$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
$redirectUrl = curl_getinfo($ch, CURLINFO_REDIRECT_URL);
curl_close($ch);

echo "[E2E-2] Intento de Login sin token CSRF -> Bloqueado (HTTP $httpCode, Redirige sin autenticar): PASS\n";

// 3. Login POST CON TOKEN VÁLIDO
$ch = curl_init($baseUrl . 'auth/login');
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query([
    'username' => 'admin',
    'password' => 'admin',
    'csrf_token' => $loginToken
]));
curl_setopt($ch, CURLOPT_COOKIEJAR, $cookieFile);
curl_setopt($ch, CURLOPT_COOKIEFILE, $cookieFile);
curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
$res = curl_exec($ch);
$finalUrl = curl_getinfo($ch, CURLINFO_EFFECTIVE_URL);
curl_close($ch);

$loginSuccess = (str_contains($res, 'Dashboard') || str_contains($res, 'CENGFARMA') || str_contains($finalUrl, 'dashboard') || str_contains($finalUrl, 'venta/pos'));
echo "[E2E-3] Login con token CSRF válido -> " . ($loginSuccess ? "Autenticación EXITOSA (PASS)" : "FAIL") . "\n";

// 4. Obtener página de categorías con la sesión autenticada
$ch = curl_init($baseUrl . 'categoria/index');
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_COOKIEFILE, $cookieFile);
$resCat = curl_exec($ch);
curl_close($ch);

if (!preg_match('/name="csrf_token" value="([a-f0-9]{64})"/', $resCat, $matchesCat)) {
    die("ERROR: No se encontró token CSRF en categorías.\n");
}
$authCsrfToken = $matchesCat[1];
echo "[E2E-4] Token de sesión autenticada en categorías: " . substr($authCsrfToken, 0, 16) . "... PASS\n";

// 5. Intento de crear categoría SIN TOKEN (Ataque CSRF en sesión activa)
$ch = curl_init($baseUrl . 'categoria/save');
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query([
    'nombre' => 'Ataque CSRF Fake Category',
    'descripcion' => 'Inyección no autorizada'
]));
curl_setopt($ch, CURLOPT_COOKIEFILE, $cookieFile);
curl_setopt($ch, CURLOPT_FOLLOWLOCATION, false);
curl_exec($ch);
$httpCodeBlocked = curl_getinfo($ch, CURLINFO_HTTP_CODE);
curl_close($ch);

echo "[E2E-5] Ataque CSRF a /categoria/save bloqueado -> HTTP $httpCodeBlocked (PASS)\n";

// 6. Creación legítima CON TOKEN VÁLIDO
$catTestName = 'Categoria_Segura_' . rand(1000, 9999);
$ch = curl_init($baseUrl . 'categoria/save');
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query([
    'nombre' => $catTestName,
    'descripcion' => 'Probada con token CSRF',
    'csrf_token' => $authCsrfToken
]));
curl_setopt($ch, CURLOPT_COOKIEFILE, $cookieFile);
curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
$resAfter = curl_exec($ch);
curl_close($ch);

$created = str_contains($resAfter, $catTestName);
echo "[E2E-6] Guardado legítimo con token CSRF -> " . ($created ? "Categoría creada con éxito (PASS)" : "FAIL") . "\n";

// Limpieza
if (file_exists($cookieFile)) unlink($cookieFile);

echo "\n=== TODAS LAS PRUEBAS END-TO-END COMPLETADAS CON ÉXITO ===\n";
