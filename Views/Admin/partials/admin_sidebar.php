<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
if (empty($_SESSION['usuario_id']) || ($_SESSION['usuario_rol'] ?? '') !== 'admin') {
    return;
}
$currentPage = $_GET['action'] ?? 'home_admin';
$adminNombre = htmlspecialchars($_SESSION['usuario_nombre'] ?? 'Administrador');
$adminInicial = strtoupper(substr($adminNombre, 0, 1));
?>

<script src="Scripts/normalizador-modales.js?v=<?= time(); ?>" defer></script>
<script src="Scripts/table-kebab-menu.js?v=<?= time(); ?>" defer></script>

<div class="admin-sidebar-overlay" id="adminSidebarOverlay"></div>

<aside class="admin-sidebar" id="adminSidebar">
    <!-- Header Móvil -->
    <div class="sidebar-header-mobile hide-on-desktop">
        <div class="sidebar-mobile-heading">
            <svg xmlns="http://www.w3.org/2000/svg" width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"></path><circle cx="12" cy="7" r="4"></circle></svg>
            <span>Panel ERP (Admin)</span>
        </div>
        <button type="button" class="sidebar-close-btn" id="closeAdminSidebar" aria-label="Cerrar Menú">
            <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><line x1="18" y1="6" x2="6" y2="18"></line><line x1="6" y1="6" x2="18" y2="18"></line></svg>
        </button>
    </div>
    <!-- Brand Desktop -->
    <a href="index.php?action=dashboard_ganancias" class="sidebar-brand hide-on-mobile">
        <img src="Images/logo_backup.png" alt="La Caserita" class="sidebar-brand-img">
        <div class="sidebar-brand-text">
            <span>La Caserita</span>
        </div>
    </a>

    <!-- Menú de Navegación ERP -->
    <nav class="sidebar-nav">
        <div class="sidebar-nav-title sidebar-mobile-menu-title">MENÚ PRINCIPAL</div>
        <div class="sidebar-nav-title">OPERACIONES</div>
        <ul class="sidebar-menu">
            <li>
                <a href="index.php?action=dashboard_ganancias" class="sidebar-link <?= $currentPage === 'dashboard_ganancias' ? 'active' : ''; ?>">
                    <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><line x1="18" y1="20" x2="18" y2="10"></line><line x1="12" y1="20" x2="12" y2="4"></line><line x1="6" y1="20" x2="6" y2="14"></line></svg>
                    Dashboard Financiero
                </a>
            </li>
            <li>
                <a href="index.php?action=ventas_admin" class="sidebar-link <?= $currentPage === 'ventas_admin' ? 'active' : ''; ?>">
                    <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M6 2 3 6v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2V6l-3-4z"></path><line x1="3" y1="6" x2="21" y2="6"></line><path d="M16 10a4 4 0 0 1-8 0"></path></svg>
                    Ventas
                </a>
            </li>
            <li>
                <a href="index.php?action=compras_admin" class="sidebar-link <?= $currentPage === 'compras_admin' ? 'active' : ''; ?>">
                    <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M11 7V5a2 2 0 0 1 2-2h6a2 2 0 0 1 2 2v14a2 2 0 0 1-2 2h-4"></path><path d="M17 13V7"></path><path d="m14 10 3-3 3 3"></path><rect x="2" y="10" width="12" height="4" rx="1"></rect><path d="M3 14v6a2 2 0 0 0 2 2h6a2 2 0 0 0 2-2v-6"></path><line x1="6.5" y1="17" x2="9.5" y2="17"></line></svg>
                    Compras y Reposición
                </a>
            </li>
            <li>
                <a href="index.php?action=verificacion_pagos" class="sidebar-link <?= $currentPage === 'verificacion_pagos' ? 'active' : ''; ?>">
                    <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="1" y="4" width="22" height="16" rx="2"></rect><path d="M10 10l2 2 4-4"></path></svg>
                    Auditoría Yape y Plin
                </a>
            </li>
            <li>
                <a href="index.php?action=delivery_admin" class="sidebar-link <?= $currentPage === 'delivery_admin' ? 'active' : ''; ?>">
                    <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="7" cy="17" r="2"></circle><circle cx="17" cy="17" r="2"></circle><path d="M5 17h-2v-4l2-5h9l4 5h1v4h-2"></path></svg>
                    Logística y Despacho
                </a>
            </li>
        </ul>
        <div class="sidebar-nav-title">INVENTARIO</div>
        <ul class="sidebar-menu">
            <li>
                <a href="index.php?action=inventario" class="sidebar-link sidebar-link-subitem <?= ($currentPage === 'inventario' || $currentPage === 'productos_admin') ? 'active' : ''; ?>">
                    <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M21 16V8a2 2 0 0 0-1-1.73l-7-4a2 2 0 0 0-2 0l-7 4A2 2 0 0 0 3 8v8a2 2 0 0 0 1 1.73l7 4a2 2 0 0 0 2 0l7-4A2 2 0 0 0 21 16z"></path><polyline points="3.27 6.96 12 12.01 20.73 6.96"></polyline><line x1="12" y1="22.08" x2="12" y2="12"></line></svg>
                    Productos
                </a>
            </li>
            <li>
                <a href="index.php?action=categorias_admin" class="sidebar-link sidebar-link-subitem <?= $currentPage === 'categorias_admin' ? 'active' : ''; ?>">
                    <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="3" y="3" width="7" height="7"></rect><rect x="14" y="3" width="7" height="7"></rect><rect x="14" y="14" width="7" height="7"></rect><rect x="3" y="14" width="7" height="7"></rect></svg>
                    Categorías
                </a>
            </li>
            <li>
                <a href="index.php?action=kardex_admin" class="sidebar-link sidebar-link-subitem <?= $currentPage === 'kardex_admin' ? 'active' : ''; ?>">
                    <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><line x1="8" y1="6" x2="21" y2="6"></line><line x1="8" y1="12" x2="21" y2="12"></line><line x1="8" y1="18" x2="21" y2="18"></line><line x1="3" y1="6" x2="3.01" y2="6"></line><line x1="3" y1="12" x2="3.01" y2="12"></line><line x1="3" y1="18" x2="3.01" y2="18"></line></svg>
                    Kardex Valorizado
                </a>
            </li>
            <li>
                <a href="index.php?action=proveedores_admin" class="sidebar-link sidebar-link-subitem <?= $currentPage === 'proveedores_admin' ? 'active' : ''; ?>">
                    <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M16 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"></path><circle cx="9" cy="7" r="4"></circle><path d="M22 17h-6"></path><path d="M19 14v6"></path></svg>
                    Proveedores
                </a>
            </li>
        </ul>
        <div class="sidebar-nav-title">SISTEMA</div>
        <ul class="sidebar-menu">
            <li>
                <a href="index.php?action=carrusel_admin" class="sidebar-link <?= $currentPage === 'carrusel_admin' ? 'active' : ''; ?>">
                    <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="3" y="3" width="18" height="18" rx="2" ry="2"></rect><circle cx="8.5" cy="8.5" r="1.5"></circle><polyline points="21 15 16 10 5 21"></polyline></svg>
                    Banners & Carrusel
                </a>
            </li>
            <li>
                <a href="index.php?action=usuarios_admin" class="sidebar-link <?= $currentPage === 'usuarios_admin' ? 'active' : ''; ?>">
                    <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"></path><circle cx="9" cy="7" r="4"></circle><path d="M23 21v-2a4 4 0 0 0-3-3.87"></path><path d="M16 3.13a4 4 0 0 1 0 7.75"></path></svg>
                    Gestión de Usuarios
                </a>
            </li>
        </ul>
</nav>

    <!-- Footer Sidebar (Estilo Referencia: Avatar, Nombre, Rol y Salir) -->
    <div class="sidebar-footer-user">
        <a href="index.php?action=mi_perfil_admin" class="sidebar-user-info" title="Mi Perfil">
            <div class="sidebar-user-avatar">
                <?= $adminInicial; ?>
            </div>
            <div class="sidebar-user-meta">
                <span class="sidebar-user-name"><?= htmlspecialchars($adminNombre ?: 'Admin'); ?></span>
                <span class="sidebar-user-role"><?= htmlspecialchars(strtolower($_SESSION['usuario_rol'] ?? 'administrador')); ?></span>
            </div>
        </a>
        <a href="index.php?action=logout" onclick="window.location.href='index.php?action=logout'; return false;" class="sidebar-user-exit" title="Cerrar sesión">
            <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round">
                <path d="M14 4h4a2 2 0 0 1 2 2v12a2 2 0 0 1-2 2h-4"></path>
                <polyline points="10 17 15 12 10 7"></polyline>
                <line x1="15" y1="12" x2="3" y2="12"></line>
            </svg>
            <span>Salir</span>
        </a>
    </div>
</aside>
