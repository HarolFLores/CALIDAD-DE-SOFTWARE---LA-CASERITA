<?php
/**
 * Header Principal Verde (Tier 1 + Tier 2) - La Caserita
 * Canónico y unificado para todas las vistas (Home, Categorías, Detalle de Producto, etc.)
 */
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

$estaAutenticado = isset($_SESSION['usuario_id']);
$rol = $_SESSION['usuario_rol'] ?? '';
$esAdmin = ($rol === 'admin');
$esPersonal = in_array($rol, ['admin', 'cajero', 'repartidor'], true);
$rutaInicio = 'index.php?action=home_usuario';
$rutaPerfil = $esAdmin ? 'index.php?action=dashboard_ganancias' : (($rol === 'cajero') ? 'index.php?action=panel_cajero' : (($rol === 'repartidor') ? 'index.php?action=panel_repartidor' : 'index.php?action=mi_cuenta'));
$textoPerfil = $esAdmin ? 'Panel ERP' : (($rol === 'cajero') ? 'Panel de caja' : (($rol === 'repartidor') ? 'Mis entregas' : 'Ir a Mi Cuenta'));
$nombreCompleto = $_SESSION['usuario_nombre'] ?? 'Usuario';
$partesNombre = explode(' ', trim($nombreCompleto));
$nombre = $estaAutenticado ? htmlspecialchars($partesNombre[0]) : 'Invitado';

if (!isset($cantPedidosActivos)) {
    $cantPedidosActivos = 0;
    if ($estaAutenticado && !empty($_SESSION['usuario_id'])) {
        try {
            require_once __DIR__ . '/../../../Models/pedidoModel.php';
            $pModel = new PedidoModel();
            $cantPedidosActivos = $pModel->obtenerCantidadPedidosNoLeidos((int)$_SESSION['usuario_id']);
            if (!isset($pedidosUsuario)) {
                $pedidosUsuario = $pModel->getPedidosByUsuario((int)$_SESSION['usuario_id']);
            }
        } catch (\Throwable $th) {
            $cantPedidosActivos = 0;
            $pedidosUsuario = [];
        }
    }
}

// Total de productos en carrito para insignia roja
$cartUserIdHeader = $_SESSION['usuario_id'] ?? 'invitado';
$carritoSesionHeader = $_SESSION['carritos'][$cartUserIdHeader] ?? [];
$cantTotalCarritoHeader = 0;
foreach ($carritoSesionHeader as $itemCart) {
    $cantTotalCarritoHeader += (int)($itemCart['cantidad'] ?? 0);
}

if (!isset($productosParaBuscadorGlobal)) {
    if (isset($product_bd) && !empty($product_bd)) {
        $productosParaBuscadorGlobal = $product_bd;
    } else {
        try {
            require_once __DIR__ . '/../../../Models/productModel.php';
            $__pModelGlobal = new ProductoModel();
            $productosParaBuscadorGlobal = $__pModelGlobal->getProductofull();
        } catch (\Throwable $th) {
            $productosParaBuscadorGlobal = [];
        }
    }
}
?>
    <script>
        window.productosBD = <?= json_encode($productosParaBuscadorGlobal ?? []); ?>;
    </script>
    <script src="Scripts/app.js?v=<?= time(); ?>"></script>

    <header class="main-header" id="mainHeader">
        <!-- Tier 1: Logo Caserita + Buscador + Cuenta + Carrito -->
        <div class="header-tier1">
            <button class="btn-menu-mobile" id="openSidebar" type="button" aria-label="Abrir menú" style="margin-right: -4px;">
                <svg xmlns="http://www.w3.org/2000/svg" width="26" height="26" viewBox="0 0 24 24" fill="none"
                    stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <line x1="3" y1="12" x2="21" y2="12"></line>
                    <line x1="3" y1="6" x2="21" y2="6"></line>
                    <line x1="3" y1="18" x2="21" y2="18"></line>
                </svg>
            </button>

            <a href="<?= htmlspecialchars($rutaInicio); ?>" class="header-brand-wrap" onclick="if(typeof cerrarBusqueda==='function') cerrarBusqueda(); return true;">
                <div class="header-brand-icon">
                    <img src="Images/logo_backup.png" alt="La Caserita" onerror="this.onerror=null;this.src='Images/logo.png';">
                </div>
                <div class="header-brand-text">
                    <span class="header-brand-name">La Caserita</span>
                </div>
            </a>

            <div class="header-search-wrap">
                <span class="header-search-icon-inside">
                    <svg xmlns="http://www.w3.org/2000/svg" width="19" height="19" viewBox="0 0 24 24"
                        fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <circle cx="11" cy="11" r="8"></circle>
                        <line x1="21" y1="21" x2="16.65" y2="16.65"></line>
                    </svg>
                </span>
                <?php $valBusquedaHeader = isset($_GET['buscar']) ? trim($_GET['buscar']) : (isset($_GET['q']) ? trim($_GET['q']) : ''); ?>
                <input type="text" name="busqueda" id="busqueda" value="<?= htmlspecialchars($valBusquedaHeader); ?>" placeholder="Busca arroz, leche, bebidas, frutas y más..."
                    class="header-search-input-pill" autocomplete="off">
                <button type="button" class="search-clear" id="searchClear" style="display: none;">×</button>

                <div class="search-dropdown" id="searchDropdown">
                    <div class="search-dropdown-content">
                        <div class="search-suggestions">
                            <h4>Sugerencias</h4>
                            <ul id="searchSuggestionsList"></ul>
                        </div>
                        <div class="search-quick-results">
                            <div class="search-quick-header">
                                <span id="searchQuickLabel">Resultado para</span>
                                <strong id="searchQuickTerm"></strong>
                            </div>
                            <div class="search-quick-products" id="searchQuickProducts"></div>
                            <button type="button" class="search-view-all-btn" id="searchViewAllBtn">VER PRODUCTOS CON DESCUENTO</button>
                        </div>
                    </div>
                </div>
            </div>

            <div class="header-controls-group header-actions">
                <!-- Botón Usuario (Píldora con Menú Desplegable al pasar el cursor) -->
                <?php if ($estaAutenticado): ?>
                    <div class="user-menu-wrapper" id="userMenuWrapper">
                        <button type="button" class="btn-user-pill-img1 btn-user" title="<?= htmlspecialchars($textoPerfil) ?>" id="btnUserPill" onclick="toggleUserDropdownMenu(event)" aria-haspopup="true" aria-expanded="false">
                            <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none"
                                stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                <path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"></path>
                                <circle cx="12" cy="7" r="4"></circle>
                            </svg>
                            <span class="btn-user-label"><?= $esPersonal ? htmlspecialchars($textoPerfil) . ' (' . $nombre . ')' : $nombre; ?></span>
                            <?php if (!$esPersonal): ?>
                                <span class="user-notif-badge-img1 notif-badge" id="notifBadge" title="<?= $cantPedidosActivos; ?> notificaciones de pedidos sin leer" style="display: <?= $cantPedidosActivos > 0 ? 'flex' : 'none'; ?>; cursor: default;">
                                    <?= $cantPedidosActivos; ?>
                                </span>
                            <?php endif; ?>
                        </button>

                        <!-- Dropdown flotante al hacer hover sobre el usuario -->
                        <div class="user-dropdown-menu" id="userDropdownMenu" role="menu">
                            <div class="user-dropdown-header">
                                <div class="user-dropdown-avatar">
                                    <?= strtoupper(substr($nombre, 0, 1)); ?>
                                </div>
                                <div class="user-dropdown-info">
                                    <strong class="user-dropdown-name"><?= htmlspecialchars($nombreCompleto); ?></strong>
                                    <span class="user-dropdown-email"><?= htmlspecialchars($_SESSION['usuario_correo'] ?? ''); ?></span>
                                </div>
                            </div>
                            <div class="user-dropdown-divider"></div>

                            <a href="<?= htmlspecialchars($rutaPerfil); ?>" class="user-dropdown-item" role="menuitem">
                                <svg xmlns="http://www.w3.org/2000/svg" width="17" height="17" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"></path><circle cx="12" cy="7" r="4"></circle></svg>
                                <span>Ver mi perfil</span>
                            </a>

                            <?php if (!$esPersonal): ?>
                            <a href="javascript:void(0);" onclick="toggleNotificaciones(event)" class="user-dropdown-item" role="menuitem">
                                <svg xmlns="http://www.w3.org/2000/svg" width="17" height="17" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M18 8a6 6 0 0 0-12 0c0 7-3 9-3 9h18s-3-2-3-9"></path><path d="M13.73 21a2 2 0 0 1-3.46 0"></path></svg>
                                <span>Notificaciones</span>
                            </a>
                            <?php endif; ?>

                            <div class="user-dropdown-divider"></div>

                            <a href="index.php?action=logout" class="user-dropdown-item user-dropdown-logout" role="menuitem">
                                <svg xmlns="http://www.w3.org/2000/svg" width="17" height="17" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4"></path><polyline points="16 17 21 12 16 7"></polyline><line x1="21" y1="12" x2="9" y2="12"></line></svg>
                                <span>Salir</span>
                            </a>
                        </div>
                    </div>
                <?php else: ?>
                    <button type="button" class="btn-user-pill-img1 btn-user" onclick="if(typeof abrirModalAuth==='function') abrirModalAuth('login'); else window.location.href='index.php?action=login';" title="Entrar">
                        <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none"
                            stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"></path>
                            <circle cx="12" cy="7" r="4"></circle>
                        </svg>
                        <span class="btn-user-label">Entrar</span>
                    </button>
                <?php endif; ?>

                <!-- Botón Carrito Circular / Píldora con insignia roja -->
                <?php if (!$esPersonal): ?><button type="button" class="btn-cart-circle-img1 btn-cart" id="btnAbrirCarrito" title="Ver Carrito de Compras">
                    <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none"
                        stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <circle cx="9" cy="21" r="1"></circle>
                        <circle cx="20" cy="21" r="1"></circle>
                        <path d="M1 1h4l2.68 13.39a2 2 0 0 0 2 1.61h9.72a2 2 0 0 0 2-1.61L23 6H6"></path>
                    </svg>
                    <span class="btn-cart-label">Carrito</span>
                    <span class="cart-count-badge-img1" id="cartCountHeader"><?= $cantTotalCarritoHeader; ?></span>
                </button><?php endif; ?>
            </div>
        </div>

        <!-- Tier 2: Barra Verde Automatizada de Categorías -->
        <?php include __DIR__ . '/tier2_categories_nav.php'; ?>
    </header>

    <!-- Modal de Autenticación Global (Login / Registro) -->
    <?php include_once __DIR__ . '/../../Login/auth_modal.php'; ?>

    <!-- Drawer Lateral de Notificaciones ("Mis Pedidos") Unificado -->
    <?php include_once __DIR__ . '/notificaciones_drawer.php'; ?>

    <script>
        function toggleUserDropdownMenu(e) {
            if (e) {
                e.preventDefault();
                e.stopPropagation();
            }
            const wrapper = document.getElementById('userMenuWrapper');
            if (!wrapper) return;
            wrapper.classList.toggle('active');
        }

        document.addEventListener('click', function(e) {
            const wrapper = document.getElementById('userMenuWrapper');
            if (wrapper && !wrapper.contains(e.target)) {
                wrapper.classList.remove('active');
            }
        });
        window.toggleUserDropdownMenu = toggleUserDropdownMenu;
    </script>

