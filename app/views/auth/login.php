<?php
// Incluir el DOCTYPE e imports de Bootstrap / Iconos en una página limpia (sin el layout general)
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="<?php echo Controller::generateCsrfToken(); ?>">
    <title>Acceso - Sistema de Botica</title>
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Bootstrap Icons -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">
    <!-- Custom CSS -->
    <link rel="stylesheet" href="<?php echo BASE_URL; ?>css/style.css">
    <!-- Suite Nativa de Accesibilidad Web (WCAG 2.1 AA) -->
    <link rel="stylesheet" href="<?php echo BASE_URL; ?>css/accessibility.css">
</head>
<body class="login-body">

<div class="login-card">
    <div class="login-logo">
        <img src="<?php echo BASE_URL; ?>img/logo_cengfarma.jpg" alt="CENGFARMA" style="max-height: 88px; max-width: 95%; object-fit: contain; border-radius: 10px; margin-bottom: 16px; box-shadow: 0 6px 22px rgba(4, 123, 7, 0.2);">
    </div>
    <div class="login-title">Bienvenido a CENGFARMA 👋</div>
    <div class="login-subtitle">Soluciones para tu bienestar y salud</div>

    <?php 
    $displayError = !empty($data['error']) ? $data['error'] : ($_SESSION['error'] ?? '');
    unset($_SESSION['error']);
    if (!empty($displayError)): 
    ?>
        <div class="alert-danger">
            <i class="bi bi-exclamation-triangle"></i> <?php echo htmlspecialchars($displayError, ENT_QUOTES, 'UTF-8'); ?>
        </div>
    <?php endif; ?>

    <form action="<?php echo BASE_URL; ?>auth/login" method="POST">
        <?php echo Controller::csrfField(); ?>
        <div class="form-group">
            <label class="form-label">Usuario</label>
            <input type="text" name="username" class="form-control-custom" placeholder="Ej: admin" value="admin" required autofocus>
        </div>
        <div class="form-group">
            <div class="d-flex justify-content-between">
                <label class="form-label">Contraseña</label>
                <a href="#" style="font-size: 13px; color: var(--accent-primary); text-decoration: none;">¿Olvidaste la clave?</a>
            </div>
            <input type="password" name="password" class="form-control-custom" placeholder="••••••••" value="admin" required>
        </div>
        <div class="form-group form-check mb-4">
            <input type="checkbox" class="form-check-input" id="remember">
            <label class="form-check-label" for="remember" style="color: var(--text-secondary); font-size: 13px;">Recordarme en este equipo</label>
        </div>
        
        <button type="submit" class="btn-primary-custom">Iniciar Sesión</button>
    </form>
</div>

<!-- Suite Nativa de Accesibilidad Web (Widget y Lógica) -->
<?php require_once '../app/views/partials/accessibility_widget.php'; ?>
<script src="<?php echo BASE_URL; ?>js/accessibility.js"></script>
</body>
</html>
