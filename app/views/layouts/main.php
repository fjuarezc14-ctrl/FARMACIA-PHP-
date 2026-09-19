<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="<?php echo Controller::generateCsrfToken(); ?>">
    <title><?php echo isset($data['title']) ? $data['title'] . ' | Farmacia Prueba' : 'Farmacia Prueba | Sistema de Gestión'; ?></title>
    <!-- Favicon -->
    <link rel="icon" type="image/png" href="<?php echo BASE_URL; ?>img/logo_icon.png">
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Bootstrap Icons -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">
    <!-- ChartJS -->
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <?php
    $baseFs = defined('BASE_PATH') ? BASE_PATH : dirname(dirname(__DIR__)) . DIRECTORY_SEPARATOR;
    $vStyle = file_exists($baseFs . 'public/css/style.css') ? filemtime($baseFs . 'public/css/style.css') : '1.0';
    $vA11y  = file_exists($baseFs . 'public/css/accessibility.css') ? filemtime($baseFs . 'public/css/accessibility.css') : '1.0';
    ?>
    <!-- Custom CSS -->
    <link rel="stylesheet" href="<?php echo BASE_URL; ?>css/style.css?v=<?php echo $vStyle; ?>">
    <!-- Suite Nativa de Accesibilidad Web (WCAG 2.1 AA) -->
    <link rel="stylesheet" href="<?php echo BASE_URL; ?>css/accessibility.css?v=<?php echo $vA11y; ?>">
</head>
<body class="marca-body">

<?php
require_once '../app/models/Configuracion.php';
$_globalConfigModel = new Configuracion();
$_globalLogo = $_globalConfigModel->get('logo');
$configs     = $_globalConfigModel->getAll();

require_once '../app/models/Inventario.php';
$_globalInvModel = new Inventario();
$_lotesVencer = $_globalInvModel->getLotesProximosVencer(90);
$_stockBajo = $_globalInvModel->getProductosBajoStock(20);
$_totalNotifs = count($_lotesVencer) + count($_stockBajo);
$boticaName = $configs['nombre_botica']['valor'] ?? 'Farmacia Prueba';

$sunatActivo = ($configs['sunat_habilitado']['valor'] ?? '0') === '1';
$sunatModo   = $configs['sunat_modo']['valor'] ?? 'beta';

$roles = [1 => 'Administrador', 2 => 'Farmacéutico', 3 => 'Cajero', 4 => 'Almacenero'];
$userRoleName = $roles[$_SESSION['rol_id'] ?? 1] ?? 'Usuario';

$effectiveLogo = !empty($_globalLogo) ? $_globalLogo : 'img/logo_banner.png';
$logoSrc = (strpos($effectiveLogo, 'http') === 0) ? $effectiveLogo : BASE_URL . $effectiveLogo;
?>

<!-- ======================================================= -->
<!-- NAVEGACIÓN PRINCIPAL HORIZONTAL SUPERIOR (TOP BAR)     -->
<!-- ======================================================= -->
<header id="marca-topbar" class="marca-topbar sticky-top">
    <div class="marca-topbar-inner">
        
        <!-- Bloque Izquierdo Unificado: Marca + Navegación (Continuo y sin huecos) -->
        <div class="marca-topbar-left d-flex align-items-center gap-3">
            <div class="marca-brand-area d-flex align-items-center gap-2">
                <!-- Botón Menú Móvil / Tablet (Offcanvas) -->
                <button class="marca-mobile-toggler d-lg-none" type="button" data-bs-toggle="offcanvas" data-bs-target="#marcaMobileDrawer" aria-controls="marcaMobileDrawer" aria-label="Abrir Menú">
                    <i class="bi bi-list"></i>
                </button>

                <!-- Logotipo & Acceso Inteligente a Dashboard -->
                <?php 
                $isDashboardActive = (strpos($_SERVER['REQUEST_URI'] ?? '', 'dashboard') !== false) || (trim($_SERVER['REQUEST_URI'] ?? '', '/') === 'public');
                ?>
                <a href="<?php echo BASE_URL; ?><?php echo ($_SESSION['rol_id'] == 1) ? 'dashboard/index' : 'venta/pos'; ?>" 
                   class="marca-brand-link <?php echo $isDashboardActive ? 'active-home' : ''; ?>" 
                   title="Ir al Dashboard / Inicio">
                    <img src="<?php echo htmlspecialchars($logoSrc); ?>" alt="<?php echo htmlspecialchars($boticaName); ?>" class="marca-brand-logo">
                    <?php if($isDashboardActive): ?>
                    <span class="marca-brand-active-indicator" title="Estás en el Dashboard"></span>
                    <?php endif; ?>
                </a>
            </div>

            <!-- Separador Vertical Sutil -->
            <div class="marca-topbar-sep d-none d-lg-block"></div>

            <!-- Menú de Navegación Principal (Continuo al logo, sin huecos vacíos) -->
            <nav class="marca-nav-menu d-none d-lg-flex align-items-center gap-2">

                <!-- Botón Estrella: Vender (POS) -->
                <a href="<?php echo BASE_URL; ?>venta/pos" class="marca-pos-btn" id="nav-pos" data-route="venta/pos" title="Ir a Punto de Venta (Atajo F1)">
                    <i class="bi bi-cart-fill"></i>
                    <span>Vender</span>
                    <span class="marca-kbd-tag">F1</span>
                </a>

                <!-- Módulo: Ventas & Caja -->
                <div class="dropdown marca-dropdown">
                    <button class="marca-nav-link dropdown-toggle" type="button" data-bs-toggle="dropdown" aria-expanded="false" data-route="caja,venta,cliente,puntos">
                        <i class="bi bi-cash-coin"></i>
                        <span>Ventas &amp; Caja</span>
                    </button>
                    <ul class="dropdown-menu marca-dropdown-menu shadow-lg">
                        <li class="marca-dropdown-title"><i class="bi bi-wallet2 text-success me-1"></i> Gestión de Caja</li>
                        <li><a class="dropdown-item" href="<?php echo BASE_URL; ?>caja/apertura"><i class="bi bi-box-arrow-in-right"></i> Apertura de Turno</a></li>
                        <li><a class="dropdown-item" href="<?php echo BASE_URL; ?>caja/cierre"><i class="bi bi-lock-fill"></i> Cerrar / Arqueo</a></li>
                        <?php if($_SESSION['rol_id'] == 1): ?>
                        <li><a class="dropdown-item" href="<?php echo BASE_URL; ?>caja/index"><i class="bi bi-clock-history"></i> Historial Arqueos</a></li>
                        <?php endif; ?>
                        <li><hr class="dropdown-divider"></li>
                        <li class="marca-dropdown-title"><i class="bi bi-receipt text-warning me-1"></i> Ventas &amp; Clientes</li>
                        <li><a class="dropdown-item" href="<?php echo BASE_URL; ?>venta/index"><i class="bi bi-receipt"></i> Historial de Ventas</a></li>
                        <li><a class="dropdown-item" href="<?php echo BASE_URL; ?>cliente/index"><i class="bi bi-people-fill"></i> Clientes</a></li>
                        <?php if(in_array($_SESSION['rol_id'], [1, 2, 4])): ?>
                        <li><a class="dropdown-item" href="<?php echo BASE_URL; ?>puntos/index"><i class="bi bi-star-fill text-warning"></i> Club de Puntos</a></li>
                        <?php endif; ?>
                    </ul>
                </div>

                <!-- Módulo: Almacén & FEFO -->
                <?php if(in_array($_SESSION['rol_id'], [1, 2, 4])): ?>
                <div class="dropdown marca-dropdown">
                    <button class="marca-nav-link dropdown-toggle" type="button" data-bs-toggle="dropdown" aria-expanded="false" data-route="producto,categoria,laboratorio,inventario,compra,proveedor">
                        <i class="bi bi-boxes"></i>
                        <span>Almacén &amp; FEFO</span>
                    </button>
                    <ul class="dropdown-menu marca-dropdown-menu shadow-lg">
                        <li class="marca-dropdown-title"><i class="bi bi-capsule text-primary me-1"></i> Catálogo Maestro</li>
                        <li><a class="dropdown-item" href="<?php echo BASE_URL; ?>producto/index"><i class="bi bi-box-seam"></i> Catálogo de Productos</a></li>
                        <li><a class="dropdown-item" href="<?php echo BASE_URL; ?>categoria/index"><i class="bi bi-tags-fill"></i> Categorías</a></li>
                        <li><a class="dropdown-item" href="<?php echo BASE_URL; ?>laboratorio/index"><i class="bi bi-building"></i> Laboratorios</a></li>
                        <li><hr class="dropdown-divider"></li>
                        <li class="marca-dropdown-title"><i class="bi bi-calendar-check text-danger me-1"></i> Control FEFO &amp; Stock</li>
                        <li><a class="dropdown-item" href="<?php echo BASE_URL; ?>inventario/lotes"><i class="bi bi-calendar-event"></i> Fechas Vencimiento (FEFO)</a></li>
                        <li><a class="dropdown-item" href="<?php echo BASE_URL; ?>inventario/kardex"><i class="bi bi-clipboard2-data"></i> Kardex General</a></li>
                        <li><a class="dropdown-item" href="<?php echo BASE_URL; ?>inventariofisico/index"><i class="bi bi-check2-square"></i> Inventario Físico</a></li>
                        <li><hr class="dropdown-divider"></li>
                        <li class="marca-dropdown-title"><i class="bi bi-truck text-info me-1"></i> Abastecimiento</li>
                        <li><a class="dropdown-item" href="<?php echo BASE_URL; ?>compra/index"><i class="bi bi-bag-plus"></i> Compras</a></li>
                        <li><a class="dropdown-item" href="<?php echo BASE_URL; ?>proveedor/index"><i class="bi bi-truck"></i> Proveedores</a></li>
                    </ul>
                </div>
                <?php endif; ?>

                <!-- Módulo: Administración & Sistema -->
                <?php if(in_array($_SESSION['rol_id'], [1, 2, 4])): ?>
                <div class="dropdown marca-dropdown">
                    <button class="marca-nav-link dropdown-toggle" type="button" data-bs-toggle="dropdown" aria-expanded="false" data-route="notificacion,reporte,usuario,configuracion,auditoria,sistema">
                        <i class="bi bi-sliders"></i>
                        <span>Administración</span>
                    </button>
                    <ul class="dropdown-menu marca-dropdown-menu shadow-lg">
                        <li class="marca-dropdown-title"><i class="bi bi-graph-up text-info me-1"></i> Control &amp; Alertas</li>
                        <li>
                            <a class="dropdown-item d-flex justify-content-between align-items-center" href="<?php echo BASE_URL; ?>notificacion/index">
                                <span><i class="bi bi-bell"></i> Alertas Sanitarias</span>
                                <?php if($_totalNotifs > 0): ?>
                                <span class="badge rounded-pill bg-danger"><?php echo $_totalNotifs; ?></span>
                                <?php endif; ?>
                            </a>
                        </li>
                        <li><a class="dropdown-item" href="<?php echo BASE_URL; ?>reporte/index"><i class="bi bi-bar-chart-fill"></i> Reportes PDF / Excel</a></li>
                        
                        <?php if($_SESSION['rol_id'] == 1): ?>
                        <li><hr class="dropdown-divider"></li>
                        <li class="marca-dropdown-title"><i class="bi bi-shield-lock-fill text-warning me-1"></i> Sistema &amp; SUNAT</li>
                        <li><a class="dropdown-item" href="<?php echo BASE_URL; ?>usuario/index"><i class="bi bi-person-badge"></i> Gestión de Personal</a></li>
                        <li><a class="dropdown-item" href="<?php echo BASE_URL; ?>configuracion/index"><i class="bi bi-gear-fill"></i> Configuración General</a></li>
                        <li>
                            <a class="dropdown-item d-flex justify-content-between align-items-center" href="<?php echo BASE_URL; ?>configuracion/sunat">
                                <span><i class="bi bi-shield-check"></i> Facturación SUNAT</span>
                                <span class="badge" style="font-size:9px; font-weight:800; padding:2px 6px; border-radius:10px;
                                    background:<?php echo $sunatActivo ? ($sunatModo==='produccion' ? 'rgba(16,185,129,0.2)' : 'rgba(245,158,11,0.2)') : 'rgba(239,68,68,0.15)'; ?>;
                                    color:<?php echo $sunatActivo ? ($sunatModo==='produccion' ? '#10B981' : '#F59E0B') : '#ef4444'; ?>;">
                                    <?php echo $sunatActivo ? strtoupper($sunatModo) : 'OFF'; ?>
                                </span>
                            </a>
                        </li>
                        <li><a class="dropdown-item" href="<?php echo BASE_URL; ?>auditoria/index"><i class="bi bi-journal-text"></i> Logs de Auditoría</a></li>
                        <li><a class="dropdown-item" href="<?php echo BASE_URL; ?>sistema/index"><i class="bi bi-database-fill-gear"></i> Base de Datos &amp; Respaldos</a></li>
                        <?php endif; ?>
                    </ul>
                </div>
                <?php endif; ?>

            </nav>
        </div>

        <!-- Zona Derecha: Buscador Rápido + Alertas + Perfil Usuario -->
        <div class="marca-user-area d-flex align-items-center gap-2 gap-md-3">
            
            <!-- Buscador Rápido de Productos en Topbar (Desktop Amplio) -->
            <form action="<?php echo BASE_URL; ?>producto/index" method="GET" class="marca-quick-search d-none d-xl-flex">
                <i class="bi bi-search"></i>
                <input type="text" name="search" placeholder="Buscar medicamento o código..." value="<?php echo htmlspecialchars($_GET['search'] ?? '', ENT_QUOTES, 'UTF-8'); ?>">
            </form>

            <!-- Campana de Alertas Sanitarias -->
            <?php if(in_array($_SESSION['rol_id'], [1, 2, 4])): ?>
            <a href="<?php echo BASE_URL; ?>notificacion/index" class="marca-alert-bell" title="Alertas Sanitarias">
                <i class="bi bi-bell-fill"></i>
                <?php if($_totalNotifs > 0): ?>
                <span class="marca-bell-pulse"></span>
                <span class="marca-bell-badge"><?php echo $_totalNotifs; ?></span>
                <?php endif; ?>
            </a>
            <?php endif; ?>

            <!-- Dropdown Usuario / Perfil -->
            <div class="dropdown">
                <div class="marca-user-card dropdown-toggle" data-bs-toggle="dropdown" aria-expanded="false" role="button" tabindex="0">
                    <img src="https://ui-avatars.com/api/?name=<?php echo urlencode($_SESSION['nombre'] ?? 'U'); ?>&background=047B07&color=fff&bold=true" alt="Avatar" class="marca-avatar">
                    <div class="marca-user-meta d-none d-sm-flex flex-column text-start">
                        <span class="marca-user-fullname"><?php echo htmlspecialchars($_SESSION['nombre'] ?? 'Administrador', ENT_QUOTES, 'UTF-8'); ?></span>
                        <span class="marca-user-role-badge"><?php echo $userRoleName; ?></span>
                    </div>
                </div>
                <ul class="dropdown-menu dropdown-menu-end marca-dropdown-menu shadow-lg mt-2">
                    <li class="px-3 py-2 border-bottom d-sm-none">
                        <div class="fw-bold text-white"><?php echo htmlspecialchars($_SESSION['nombre'] ?? 'Administrador', ENT_QUOTES, 'UTF-8'); ?></div>
                        <small class="text-secondary"><?php echo $userRoleName; ?></small>
                    </li>
                    <li><a class="dropdown-item" href="<?php echo BASE_URL; ?>perfil/index"><i class="bi bi-person-gear me-2 text-info"></i> Mi Perfil</a></li>
                    <li><hr class="dropdown-divider"></li>
                    <li><a class="dropdown-item text-danger fw-bold" href="<?php echo BASE_URL; ?>auth/logout"><i class="bi bi-box-arrow-right me-2"></i> Cerrar Sesión</a></li>
                </ul>
            </div>

        </div>

    </div>
</header>

<!-- ======================================================= -->
<!-- CAJÓN MÓVIL / TABLET (OFFCANVAS MODERNO)                -->
<!-- ======================================================= -->
<div class="offcanvas offcanvas-start marca-offcanvas" tabindex="-1" id="marcaMobileDrawer" aria-labelledby="marcaMobileDrawerLabel">
    <div class="offcanvas-header border-bottom border-secondary border-opacity-25">
        <div class="d-flex align-items-center gap-2">
            <img src="<?php echo htmlspecialchars($logoSrc); ?>" alt="Farmacia Prueba" class="marca-brand-logo" style="max-height: 38px;">
            <div>
                <h5 class="offcanvas-title text-white fw-bold mb-0" id="marcaMobileDrawerLabel"><?php echo htmlspecialchars($boticaName); ?></h5>
                <small class="text-muted" style="font-size: 11px;">Sistema de gestión</small>
            </div>
        </div>
        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="offcanvas" aria-label="Cerrar"></button>
    </div>
    
    <div class="offcanvas-body p-3">
        <!-- Buscador móvil -->
        <form action="<?php echo BASE_URL; ?>producto/index" method="GET" class="mb-3">
            <div class="input-group">
                <input type="text" name="search" class="form-control bg-dark text-white border-secondary" placeholder="Buscar medicamento...">
                <button class="btn btn-success" type="submit"><i class="bi bi-search"></i></button>
            </div>
        </form>

        <!-- Botón POS Móvil -->
        <a href="<?php echo BASE_URL; ?>venta/pos" class="marca-pos-btn w-100 justify-content-center py-2.5 mb-3">
            <i class="bi bi-cart-fill"></i>
            <span>Vender</span>
            <span class="marca-kbd-tag">F1</span>
        </a>

        <!-- Lista de navegación acordeón -->
        <div class="accordion accordion-flush" id="mobileNavAccordion">
            
            <?php if($_SESSION['rol_id'] == 1): ?>
            <div class="mb-2">
                <a href="<?php echo BASE_URL; ?>dashboard/index" class="marca-drawer-link">
                    <i class="bi bi-grid-1x2-fill text-info"></i> Dashboard
                </a>
            </div>
            <?php endif; ?>

            <!-- Grupo Ventas & Caja -->
            <div class="accordion-item bg-transparent border-0 mb-2">
                <h2 class="accordion-header">
                    <button class="accordion-button collapsed marca-drawer-acc-btn" type="button" data-bs-toggle="collapse" data-bs-target="#accCaja">
                        <i class="bi bi-cash-coin me-2 text-warning"></i> Ventas &amp; Caja
                    </button>
                </h2>
                <div id="accCaja" class="accordion-collapse collapse" data-bs-parent="#mobileNavAccordion">
                    <div class="accordion-body p-0 ps-3">
                        <a href="<?php echo BASE_URL; ?>caja/apertura" class="marca-drawer-sublink"><i class="bi bi-box-arrow-in-right"></i> Apertura de Turno</a>
                        <a href="<?php echo BASE_URL; ?>caja/cierre" class="marca-drawer-sublink"><i class="bi bi-lock-fill"></i> Cerrar / Arqueo</a>
                        <?php if($_SESSION['rol_id'] == 1): ?>
                        <a href="<?php echo BASE_URL; ?>caja/index" class="marca-drawer-sublink"><i class="bi bi-clock-history"></i> Historial Arqueos</a>
                        <?php endif; ?>
                        <a href="<?php echo BASE_URL; ?>venta/index" class="marca-drawer-sublink"><i class="bi bi-receipt"></i> Historial de Ventas</a>
                        <a href="<?php echo BASE_URL; ?>cliente/index" class="marca-drawer-sublink"><i class="bi bi-people-fill"></i> Clientes</a>
                        <?php if(in_array($_SESSION['rol_id'], [1, 2, 4])): ?>
                        <a href="<?php echo BASE_URL; ?>puntos/index" class="marca-drawer-sublink"><i class="bi bi-star-fill text-warning"></i> Club de Puntos</a>
                        <?php endif; ?>
                    </div>
                </div>
            </div>

            <!-- Grupo Almacén & FEFO -->
            <?php if(in_array($_SESSION['rol_id'], [1, 2, 4])): ?>
            <div class="accordion-item bg-transparent border-0 mb-2">
                <h2 class="accordion-header">
                    <button class="accordion-button collapsed marca-drawer-acc-btn" type="button" data-bs-toggle="collapse" data-bs-target="#accAlmacen">
                        <i class="bi bi-boxes me-2 text-success"></i> Almacén &amp; FEFO
                    </button>
                </h2>
                <div id="accAlmacen" class="accordion-collapse collapse" data-bs-parent="#mobileNavAccordion">
                    <div class="accordion-body p-0 ps-3">
                        <a href="<?php echo BASE_URL; ?>producto/index" class="marca-drawer-sublink"><i class="bi bi-box-seam"></i> Productos</a>
                        <a href="<?php echo BASE_URL; ?>categoria/index" class="marca-drawer-sublink"><i class="bi bi-tags-fill"></i> Categorías</a>
                        <a href="<?php echo BASE_URL; ?>laboratorio/index" class="marca-drawer-sublink"><i class="bi bi-building"></i> Laboratorios</a>
                        <a href="<?php echo BASE_URL; ?>inventario/lotes" class="marca-drawer-sublink"><i class="bi bi-calendar-event"></i> Fechas Vencimiento (FEFO)</a>
                        <a href="<?php echo BASE_URL; ?>inventario/kardex" class="marca-drawer-sublink"><i class="bi bi-clipboard2-data"></i> Kardex General</a>
                        <a href="<?php echo BASE_URL; ?>inventariofisico/index" class="marca-drawer-sublink"><i class="bi bi-check2-square"></i> Inventario Físico</a>
                        <a href="<?php echo BASE_URL; ?>compra/index" class="marca-drawer-sublink"><i class="bi bi-bag-plus"></i> Compras</a>
                        <a href="<?php echo BASE_URL; ?>proveedor/index" class="marca-drawer-sublink"><i class="bi bi-truck"></i> Proveedores</a>
                    </div>
                </div>
            </div>
            <?php endif; ?>

            <!-- Grupo Administración -->
            <?php if(in_array($_SESSION['rol_id'], [1, 2, 4])): ?>
            <div class="accordion-item bg-transparent border-0 mb-2">
                <h2 class="accordion-header">
                    <button class="accordion-button collapsed marca-drawer-acc-btn" type="button" data-bs-toggle="collapse" data-bs-target="#accAdmin">
                        <i class="bi bi-sliders me-2 text-info"></i> Administración &amp; Control
                    </button>
                </h2>
                <div id="accAdmin" class="accordion-collapse collapse" data-bs-parent="#mobileNavAccordion">
                    <div class="accordion-body p-0 ps-3">
                        <a href="<?php echo BASE_URL; ?>notificacion/index" class="marca-drawer-sublink d-flex justify-content-between align-items-center">
                            <span><i class="bi bi-bell"></i> Alertas Sanitarias</span>
                            <?php if($_totalNotifs > 0): ?>
                            <span class="badge bg-danger"><?php echo $_totalNotifs; ?></span>
                            <?php endif; ?>
                        </a>
                        <a href="<?php echo BASE_URL; ?>reporte/index" class="marca-drawer-sublink"><i class="bi bi-bar-chart-fill"></i> Reportes PDF / Excel</a>
                        
                        <?php if($_SESSION['rol_id'] == 1): ?>
                        <a href="<?php echo BASE_URL; ?>usuario/index" class="marca-drawer-sublink"><i class="bi bi-person-badge"></i> Gestión de Personal</a>
                        <a href="<?php echo BASE_URL; ?>configuracion/index" class="marca-drawer-sublink"><i class="bi bi-gear-fill"></i> Configuración General</a>
                        <a href="<?php echo BASE_URL; ?>configuracion/sunat" class="marca-drawer-sublink"><i class="bi bi-shield-check"></i> Facturación SUNAT</a>
                        <a href="<?php echo BASE_URL; ?>auditoria/index" class="marca-drawer-sublink"><i class="bi bi-journal-text"></i> Logs Auditoría</a>
                        <a href="<?php echo BASE_URL; ?>sistema/index" class="marca-drawer-sublink"><i class="bi bi-database-fill-gear"></i> Base de Datos &amp; Respaldos</a>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
            <?php endif; ?>

            <!-- Perfil & Salir Móvil -->
            <div class="pt-3 mt-3 border-top border-secondary">
                <a href="<?php echo BASE_URL; ?>perfil/index" class="marca-drawer-link mb-2">
                    <i class="bi bi-person-gear text-info"></i> Mi Perfil
                </a>
                <a href="<?php echo BASE_URL; ?>auth/logout" class="marca-drawer-link text-danger">
                    <i class="bi bi-box-arrow-right"></i> Cerrar Sesión
                </a>
            </div>

        </div>
    </div>
</div>

<!-- ======================================================= -->
<!-- CONTENIDO PRINCIPAL (100% ANCHO DE PANTALLA)            -->
<!-- ======================================================= -->
<div id="wrapper" class="marca-wrapper">
    <main id="content-wrapper" class="marca-main-viewport">
        <!-- Main Content (Inyectado dinámicamente por las vistas) -->
        <?php require_once '../app/views/' . $view . '.php'; ?>
    </main>
</div>

<!-- Bootstrap 5 JS Bundle -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>

<script>
document.addEventListener("DOMContentLoaded", function() {
    // 1. Detección Inteligente de Enlace / Módulo Activo
    const currentUrl = window.location.pathname.toLowerCase();
    
    // Marcar enlaces individuales o botones dropdown
    document.querySelectorAll('.marca-nav-link, .marca-pos-btn, .marca-dropdown-menu .dropdown-item').forEach(item => {
        const href = item.getAttribute('href');
        const routeAttr = item.getAttribute('data-route');

        if (href && currentUrl.includes(new URL(href, window.location.origin).pathname.toLowerCase())) {
            item.classList.add('active');
            
            // Si está dentro de un dropdown, marcar el botón padre
            const parentDropdown = item.closest('.marca-dropdown');
            if (parentDropdown) {
                const triggerBtn = parentDropdown.querySelector('.dropdown-toggle');
                if (triggerBtn) triggerBtn.classList.add('active');
            }
        } else if (routeAttr) {
            const routes = routeAttr.split(',');
            if (routes.some(r => currentUrl.includes(r.trim().toLowerCase()))) {
                item.classList.add('active');
            }
        }
    });

    // 2. Atajo Global de Teclado F1 -> PUNTO DE VENTA (POS)
    document.addEventListener("keydown", function(event) {
        if (event.key === "F1") {
            event.preventDefault();
            const posLink = document.getElementById("nav-pos");
            if (posLink) {
                window.location.href = posLink.getAttribute("href");
            }
        }
    });
});
</script>

<!-- Suite Nativa de Accesibilidad Web (Widget y Lógica) -->
<?php 
require_once '../app/views/partials/accessibility_widget.php'; 
$vJsA11y = file_exists($baseFs . 'public/js/accessibility.js') ? filemtime($baseFs . 'public/js/accessibility.js') : '1.0';
?>
<script src="<?php echo BASE_URL; ?>js/accessibility.js?v=<?php echo $vJsA11y; ?>"></script>
</body>
</html>
