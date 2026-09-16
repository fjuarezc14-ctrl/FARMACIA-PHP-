<?php
/**
 * VT VALETEC - Widget de Accesibilidad y Lectura Fácil para CENGFARMA
 * Diseñado especialmente para adultos mayores y personas con visión reducida
 * WCAG 2.1 Nivel AA / ADA - 100% Nativo
 */

if (!isset($configs)) {
    if (!class_exists('Configuracion')) {
        require_once dirname(__DIR__) . '/../models/Configuracion.php';
    }
    $_a11yConfigModel = new Configuracion();
    $configs = $_a11yConfigModel->getAll();
}

$a11yHabilitado = $configs['a11y_habilitado']['valor'] ?? '1';

if ($a11yHabilitado !== '1') {
    return;
}

$a11yPosicion = $configs['a11y_posicion']['valor'] ?? 'bottom-right';

$posMap = [
    'bottom-right' => ['btn' => 'a11y-pos-bottom-right', 'panel' => 'a11y-panel-bottom-right'],
    'bottom-left'  => ['btn' => 'a11y-pos-bottom-left',  'panel' => 'a11y-panel-bottom-left'],
    'top-right'    => ['btn' => 'a11y-pos-top-right',    'panel' => 'a11y-panel-top-right'],
    'top-left'     => ['btn' => 'a11y-pos-top-left',     'panel' => 'a11y-panel-top-left'],
];
$posClasses = $posMap[$a11yPosicion] ?? $posMap['bottom-right'];
?>

<!-- 1. Región ARIA Live para Anuncios Auditivos en Lectores de Pantalla -->
<div id="a11yAnnouncer" class="a11y-sr-only" role="status" aria-live="polite"></div>

<!-- 2. Fondo Oscuro al Abrir Modal -->
<div id="a11yBackdrop" class="a11y-modal-backdrop" aria-hidden="true"></div>

<!-- 3. Botón Flotante Disparador (Verde CENGFARMA) -->
<button type="button" 
        id="a11yTriggerBtn" 
        class="a11y-trigger-btn <?php echo $posClasses['btn']; ?>" 
        aria-label="Menú de Lectura Fácil y Accesibilidad" 
        aria-expanded="false" 
        aria-controls="a11yPanel" 
        title="Opciones de Lectura Fácil">
    <svg viewBox="0 0 24 24" aria-hidden="true" focusable="false">
        <path d="M12 2c1.1 0 2 .9 2 2s-.9 2-2 2-2-.9-2-2 .9-2 2-2zm9 7h-6v13h-2v-6h-2v6H9V9H3V7h18v2z"/>
    </svg>
</button>

<!-- 4. Panel Modal de Lectura Fácil (Senior-Friendly) -->
<div id="a11yPanel" 
     class="a11y-panel <?php echo $posClasses['panel']; ?>" 
     role="dialog" 
     aria-modal="true" 
     aria-labelledby="a11yTitle">
    
    <!-- Cabecera -->
    <div class="a11y-header">
        <div class="a11y-header-title-box">
            <h2 id="a11yTitle" class="a11y-header-title">
                <svg viewBox="0 0 24 24" aria-hidden="true" focusable="false">
                    <path d="M12 2c1.1 0 2 .9 2 2s-.9 2-2 2-2-.9-2-2 .9-2 2-2zm9 7h-6v13h-2v-6h-2v6H9V9H3V7h18v2z"/>
                </svg>
                Lectura Fácil
            </h2>
            <div class="a11y-header-subtitle">Herramientas visuales para ver y leer mejor</div>
        </div>
        <button type="button" id="a11yCloseBtn" class="a11y-btn-close" aria-label="Cerrar opciones de lectura" title="Cerrar">
            <svg viewBox="0 0 24 24" width="20" height="20" fill="currentColor" aria-hidden="true">
                <path d="M19 6.41L17.59 5 12 10.59 6.41 5 5 6.41 10.59 12 5 17.59 6.41 19 12 13.41 17.59 19 19 17.59 13.41 12z"/>
            </svg>
        </button>
    </div>

    <!-- Cuerpo con las 4 herramientas esenciales -->
    <div class="a11y-body">
        
        <!-- Tarjeta 1: Agrandar Letra -->
        <div class="a11y-senior-card">
            <div class="a11y-card-info">
                <div class="a11y-card-icon-bubble">
                    <svg viewBox="0 0 24 24"><path d="M2.5 4v3h5v12h3V7h5V4h-13zm19 5h-9v3h3v7h3v-7h3V9z"/></svg>
                </div>
                <div class="a11y-card-texts">
                    <div class="a11y-card-name">Tamaño de Letra</div>
                    <div class="a11y-card-desc">Aumenta el tamaño del texto</div>
                </div>
            </div>
            <div class="a11y-font-stepper">
                <button type="button" id="a11yFontDecrease" class="a11y-step-btn" title="Disminuir tamaño de letra" aria-label="Letra más pequeña">A-</button>
                <span id="a11yFontLabel" class="a11y-step-label">100%</span>
                <button type="button" id="a11yFontIncrease" class="a11y-step-btn" title="Aumentar tamaño de letra" aria-label="Letra más grande">A+</button>
            </div>
        </div>

        <!-- Tarjeta 2: Contraste Nítido CENGFARMA -->
        <div class="a11y-senior-card" id="a11yCardContrast">
            <div class="a11y-card-info">
                <div class="a11y-card-icon-bubble">
                    <svg viewBox="0 0 24 24"><path d="M12 2a10 10 0 1 0 10 10A10 10 0 0 0 12 2zm0 18a8 8 0 0 1 0-16v16z"/></svg>
                </div>
                <div class="a11y-card-texts">
                    <div class="a11y-card-name">Contraste Nítido</div>
                    <div class="a11y-card-desc">Azul noche con letras blancas</div>
                </div>
            </div>
            <button type="button" id="a11yToggleContrast" class="a11y-toggle-action-btn" aria-pressed="false">Activar</button>
        </div>

        <!-- Tarjeta 3: Léeme en Voz Alta -->
        <div class="a11y-senior-card" id="a11yCardSpeech">
            <div class="a11y-card-info">
                <div class="a11y-card-icon-bubble">
                    <svg viewBox="0 0 24 24"><path d="M3 9v6h4l5 5V4L7 9H3zm13.5 3c0-1.77-1.02-3.29-2.5-4.03v8.05c1.48-.73 2.5-2.25 2.5-4.02zM14 3.23v2.06c2.89.86 5 3.54 5 6.71s-2.11 5.85-5 6.71v2.06c4.01-.91 7-4.49 7-8.77s-2.99-7.86-7-8.77z"/></svg>
                </div>
                <div class="a11y-card-texts">
                    <div class="a11y-card-name">Léeme en Voz Alta</div>
                    <div class="a11y-card-desc">Toca palabras para escucharlas</div>
                </div>
            </div>
            <button type="button" id="a11yToggleSpeech" class="a11y-toggle-action-btn" aria-pressed="false">Activar</button>
        </div>

        <!-- Tarjeta 4: Resaltar Botones y Clics -->
        <div class="a11y-senior-card" id="a11yCardHighlight">
            <div class="a11y-card-info">
                <div class="a11y-card-icon-bubble">
                    <svg viewBox="0 0 24 24"><path d="M13.64 21.97l-3.32-7.39-4.32 4.32V2.05l13.56 13.56h-5.92z"/></svg>
                </div>
                <div class="a11y-card-texts">
                    <div class="a11y-card-name">Resaltar Botones</div>
                    <div class="a11y-card-desc">Cursor grande y marcos verdes</div>
                </div>
            </div>
            <button type="button" id="a11yToggleHighlight" class="a11y-toggle-action-btn" aria-pressed="false">Activar</button>
        </div>

        <!-- Botón Restablecer Todo -->
        <div class="a11y-reset-all-container">
            <button type="button" id="a11yResetBtn" class="a11y-btn-reset-large" title="Volver a la configuración inicial">
                <svg viewBox="0 0 24 24" width="16" height="16" fill="currentColor" aria-hidden="true">
                    <path d="M17.65 6.35A7.958 7.958 0 0 0 12 4c-4.42 0-7.99 3.58-7.99 8s3.57 8 7.99 8c3.73 0 6.84-2.55 7.73-6h-2.08A5.99 5.99 0 0 1 12 18c-3.31 0-6-2.69-6-6s2.69-6 6-6c1.66 0 3.14.69 4.22 1.78L13 11h7V4l-2.35 2.35z"/>
                </svg>
                Volver a la normalidad
            </button>
        </div>

    </div>

</div>
