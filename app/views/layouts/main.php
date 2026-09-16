<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="<?php echo Controller::generateCsrfToken(); ?>">
    <title><?php echo isset($data['title']) ? $data['title'] . ' | Mi Botica' : 'Mi Botica'; ?></title>
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Bootstrap Icons -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">
    <!-- ChartJS -->
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <!-- Custom CSS -->
    <link rel="stylesheet" href="<?php echo BASE_URL; ?>css/style.css">
    <!-- Suite Nativa de Accesibilidad Web (WCAG 2.1 AA) -->
    <link rel="stylesheet" href="<?php echo BASE_URL; ?>css/accessibility.css">
</head>
<body>

<div id="sidebarOverlay"></div>
<div id="wrapper">
    <!-- Sidebar -->
    <aside id="sidebar">
        <?php
        require_once '../app/models/Configuracion.php';
        $_globalConfigModel = new Configuracion();
        $_globalLogo = $_globalConfigModel->get('logo');
        $configs     = $_globalConfigModel->getAll(); // disponible en todo el layout

        require_once '../app/models/Inventario.php';
        $_globalInvModel = new Inventario();
        $_lotesVencer = $_globalInvModel->getLotesProximosVencer(90);
        $_stockBajo = $_globalInvModel->getProductosBajoStock(20);
        $_totalNotifs = count($_lotesVencer) + count($_stockBajo);
        $boticaName = $configs['nombre_botica']['valor'] ?? 'CENGFARMA';
        ?>
        <a href="<?php echo BASE_URL; ?>auth/index" class="sidebar-logo text-center d-block">
            <?php if (!empty($_globalLogo)): 
                $logoSrc = (strpos($_globalLogo, 'http') === 0) ? $_globalLogo : BASE_URL . $_globalLogo;
            ?>
                <img src="<?php echo htmlspecialchars($logoSrc); ?>" alt="<?php echo htmlspecialchars($boticaName); ?>" style="max-height: 62px; max-width: 95%; object-fit: contain; border-radius: 8px; box-shadow: 0 4px 15px rgba(0,0,0,0.2);">
            <?php else: ?>
                <i class="bi bi-heart-pulse-fill"></i> <?php echo htmlspecialchars($boticaName); ?>
            <?php endif; ?>
        </a>
        <ul class="sidebar-nav">
            <?php if($_SESSION['rol_id'] == 1): ?>
            <li class="nav-section-title" style="color: #38bdf8;">Visión General</li>
            <li class="nav-item">
                <a href="<?php echo BASE_URL; ?>dashboard/index" class="nav-link">
                    <i class="bi bi-grid-1x2-fill"></i> Dashboard
                </a>
            </li>
            <?php endif; ?>

            <li class="nav-section-title" style="color: #fbbf24;">Comercio & Ventas</li>
            <li class="nav-item">
                <a class="nav-link" data-bs-toggle="collapse" href="#menuCaja" role="button" aria-expanded="false" aria-controls="menuCaja">
                    <i class="bi bi-box-arrow-in-right"></i> Gestión de Caja
                    <i class="bi bi-chevron-down ms-auto" style="font-size:12px"></i>
                </a>
                <div class="collapse" id="menuCaja">
                    <ul class="nav flex-column ms-3 mt-1" style="font-size: 13px;">
                        <li class="nav-item">
                            <a href="<?php echo BASE_URL; ?>caja/apertura" class="nav-link"><i class="bi bi-circle"></i> Abrir Turno (Caja)</a>
                        </li>
                        <li class="nav-item">
                            <a href="<?php echo BASE_URL; ?>caja/cierre" class="nav-link"><i class="bi bi-circle"></i> Cerrar / Arqueo</a>
                        </li>
                        <?php if($_SESSION['rol_id'] == 1): ?>
                        <li class="nav-item">
                            <a href="<?php echo BASE_URL; ?>caja/index" class="nav-link"><i class="bi bi-circle"></i> Historial Arqueos</a>
                        </li>
                        <?php endif; ?>
                    </ul>
                </div>
            </li>
            <li class="nav-item">
                <a href="<?php echo BASE_URL; ?>venta/pos" class="nav-link" id="nav-pos">
                    <i class="bi bi-cart-fill"></i> PUNTO DE VENTA (POS)
                </a>
            </li>
            <li class="nav-item">
                <a href="<?php echo BASE_URL; ?>venta/index" class="nav-link">
                    <i class="bi bi-receipt"></i> Historial Ventas
                </a>
            </li>
            <li class="nav-item">
                <a href="<?php echo BASE_URL; ?>cliente/index" class="nav-link">
                    <i class="bi bi-people"></i> Clientes
                </a>
            </li>
            
            <?php if($_SESSION['rol_id'] == 1): ?>
            <li class="nav-section-title" style="color: #34d399;">Logística & Inventario</li>
            <li class="nav-item">
                <a class="nav-link" data-bs-toggle="collapse" href="#menuProductos" role="button" aria-expanded="false" aria-controls="menuProductos">
                    <i class="bi bi-box-seam"></i> Catálogo Maestro
                    <i class="bi bi-chevron-down ms-auto" style="font-size:12px"></i>
                </a>
                <div class="collapse" id="menuProductos">
                    <ul class="nav flex-column ms-3 mt-1" style="font-size: 13px;">
                        <li class="nav-item">
                            <a href="<?php echo BASE_URL; ?>producto/index" class="nav-link"><i class="bi bi-circle"></i> Productos</a>
                        </li>
                        <li class="nav-item">
                            <a href="<?php echo BASE_URL; ?>categoria/index" class="nav-link"><i class="bi bi-circle"></i> Categorías</a>
                        </li>
                        <li class="nav-item">
                            <a href="<?php echo BASE_URL; ?>laboratorio/index" class="nav-link"><i class="bi bi-circle"></i> Laboratorios</a>
                        </li>
                    </ul>
                </div>
            </li>
            <li class="nav-item">
                <a href="<?php echo BASE_URL; ?>compra/index" class="nav-link">
                    <i class="bi bi-cart"></i> Compras
                </a>
            </li>
            <li class="nav-item">
                <a href="<?php echo BASE_URL; ?>proveedor/index" class="nav-link">
                    <i class="bi bi-truck"></i> Proveedores
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link" data-bs-toggle="collapse" href="#menuInventario" role="button" aria-expanded="false" aria-controls="menuInventario">
                    <i class="bi bi-clipboard-data"></i> Inventario
                    <i class="bi bi-chevron-down ms-auto" style="font-size:12px"></i>
                </a>
                <div class="collapse" id="menuInventario">
                    <ul class="nav flex-column ms-3 mt-1" style="font-size: 13px;">
                        <li class="nav-item">
                            <a href="<?php echo BASE_URL; ?>inventario/lotes" class="nav-link"><i class="bi bi-circle"></i> Fechas Vencimiento</a>
                        </li>
                        <li class="nav-item">
                            <a href="<?php echo BASE_URL; ?>inventario/kardex" class="nav-link"><i class="bi bi-circle"></i> Kardex General</a>
                        </li>
                        <li class="nav-item">
                            <a href="<?php echo BASE_URL; ?>inventariofisico/index" class="nav-link"><i class="bi bi-circle"></i> Inventario Físico</a>
                        </li>
                    </ul>
                </div>
            </li>
            
            <li class="nav-section-title" style="color: #fb7185;">Gerencia & Control</li>
            <li class="nav-item">
                <a href="<?php echo BASE_URL; ?>notificacion/index" class="nav-link">
                    <i class="bi bi-bell"></i> Alertas Sanitarias
                    <?php if($_totalNotifs > 0): ?>
                    <span class="badge-sidebar" style="background-color: var(--danger);"><?php echo $_totalNotifs; ?></span>
                    <?php endif; ?>
                </a>
            </li>
            <li class="nav-item">
                <a href="<?php echo BASE_URL; ?>reporte/index" class="nav-link">
                    <i class="bi bi-bar-chart-fill"></i> Reportes PDF/Excel
                </a>
            </li>
            
            <li class="nav-section-title" style="color: #a78bfa;">Ajustes & Sistema</li>
            <li class="nav-item">
                <a href="<?php echo BASE_URL; ?>usuario/index" class="nav-link">
                    <i class="bi bi-person-badge"></i> Gestión de Personal
                </a>
            </li>
            <li class="nav-item">
                <a href="<?php echo BASE_URL; ?>configuracion/index" class="nav-link">
                    <i class="bi bi-gear-fill"></i> Configuración General
                </a>
            </li>
            <li class="nav-item">
                <a href="<?php echo BASE_URL; ?>auditoria/index" class="nav-link">
                    <i class="bi bi-shield-check"></i> Logs de Auditoría
                </a>
            </li>
            <li class="nav-item">
                <a href="<?php echo BASE_URL; ?>sistema/index" class="nav-link">
                    <i class="bi bi-database-fill-gear"></i> Base de Datos & Respaldos
                </a>
            </li>
            <li class="nav-item">
                <a href="<?php echo BASE_URL; ?>configuracion/sunat" class="nav-link" style="position:relative;">
                    <i class="bi bi-shield-lock-fill"></i> Facturación Electrónica
                    <?php
                    $sunatActivo = ($configs['sunat_habilitado']['valor'] ?? '0') === '1';
                    $sunatModo   = $configs['sunat_modo']['valor'] ?? 'beta';
                    ?>
                    <span style="font-size:9px; font-weight:800; padding:2px 6px; border-radius:10px; margin-left:4px;
                        background:<?php echo $sunatActivo ? ($sunatModo==='produccion' ? 'rgba(16,185,129,0.2)' : 'rgba(245,158,11,0.2)') : 'rgba(239,68,68,0.15)'; ?>;
                        color:<?php echo $sunatActivo ? ($sunatModo==='produccion' ? '#10B981' : '#F59E0B') : '#ef4444'; ?>;">
                        <?php echo $sunatActivo ? strtoupper($sunatModo) : 'OFF'; ?>
                    </span>
                </a>
            </li>
            <?php endif; ?>
            <li class="nav-item">
                <a href="<?php echo BASE_URL; ?>auth/logout" class="nav-link">
                    <i class="bi bi-box-arrow-right"></i> Salir
                </a>
            </li>
        </ul>
    </aside>

    <!-- Content Wrapper -->
    <div id="content-wrapper">
        <!-- Topbar -->
        <header id="topbar">
            <div class="d-flex align-items-center">
                <button id="sidebarToggle" class="btn btn-link d-lg-none p-0 me-3" style="color: var(--text-primary); font-size: 26px; text-decoration: none;">
                    <i class="bi bi-list"></i>
                </button>
                <div class="search-box">
                    <i class="bi bi-search"></i>
                    <input type="text" placeholder="Buscar producto o código de barras...">
                </div>
            </div>

            <div class="topbar-actions">
                <a href="<?php echo BASE_URL; ?>notificacion/index" class="topbar-icon" style="text-decoration: none;">
                    <i class="bi bi-bell-fill"></i>
                    <?php if($_totalNotifs > 0): ?>
                    <span class="badge rounded-pill bg-danger"><?php echo $_totalNotifs; ?></span>
                    <?php endif; ?>
                </a>
                <div class="dropdown">
                    <div class="user-profile dropdown-toggle" data-bs-toggle="dropdown" aria-expanded="false" style="cursor: pointer;">
                        <img src="https://ui-avatars.com/api/?name=<?php echo urlencode($_SESSION['nombre'] ?? 'U'); ?>&background=00A896&color=fff&bold=true" alt="User Avatar">
                        <div class="user-info">
                            <span class="user-name"><?php echo htmlspecialchars($_SESSION['nombre'] ?? 'Administrador', ENT_QUOTES, 'UTF-8'); ?></span>
                            <span class="user-role">
                                <?php 
                                $roles = [1 => 'Administrador', 2 => 'Farmacéutico', 3 => 'Cajero', 4 => 'Almacenero'];
                                echo $roles[$_SESSION['rol_id'] ?? 1];
                                ?>
                            </span>
                        </div>
                    </div>
                    <ul class="dropdown-menu dropdown-menu-end shadow border-0 mt-2">
                        <li><a class="dropdown-item" href="<?php echo BASE_URL; ?>perfil/index"><i class="bi bi-person text-secondary me-2"></i> Mi Perfil</a></li>
                        <li><hr class="dropdown-divider"></li>
                        <li><a class="dropdown-item text-danger" href="<?php echo BASE_URL; ?>auth/logout"><i class="bi bi-box-arrow-right me-2"></i> Salir</a></li>
                    </ul>
                </div>
            </div>
        </header>

        <!-- Main Content ( injected by views ) -->
        <?php require_once '../app/views/' . $view . '.php'; ?>

    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script>
document.addEventListener("DOMContentLoaded", function() {
    let currentUrl = window.location.href.split('?')[0]; // Ignorar params
    let links = document.querySelectorAll('#sidebar .nav-link');
    
    links.forEach(link => {
        let href = link.getAttribute('href');
        if (href && href !== '#' && currentUrl.includes(href)) {
            // Si es POS, usa clase especial
            if(href.includes('venta/pos')) {
                link.classList.add('active-pos');
            } else {
                link.classList.add('active');
            }
            
            // Expandir menú padre si está colapsado
            let collapseParent = link.closest('.collapse');
            if (collapseParent) {
                new bootstrap.Collapse(collapseParent, {toggle: false}).show();
                let toggleBtn = document.querySelector('[aria-controls="' + collapseParent.id + '"]');
                if (toggleBtn) {
                    toggleBtn.setAttribute('aria-expanded', 'true');
                    toggleBtn.classList.add('active');
                }
            }
        }
    });

    // Lógica para Sidebar Responsivo
    const sidebarToggle = document.getElementById('sidebarToggle');
    const sidebar = document.getElementById('sidebar');
    const sidebarOverlay = document.getElementById('sidebarOverlay');

    if(sidebarToggle && sidebar && sidebarOverlay) {
        sidebarToggle.addEventListener('click', function() {
            sidebar.classList.toggle('show');
            sidebarOverlay.classList.toggle('show');
        });

        sidebarOverlay.addEventListener('click', function() {
            sidebar.classList.remove('show');
            sidebarOverlay.classList.remove('show');
        });
    }
});
</script>

<!-- Suite Nativa de Accesibilidad Web (Widget y Lógica) -->
<?php require_once '../app/views/partials/accessibility_widget.php'; ?>
<script src="<?php echo BASE_URL; ?>js/accessibility.js"></script>
</body>
</html>
