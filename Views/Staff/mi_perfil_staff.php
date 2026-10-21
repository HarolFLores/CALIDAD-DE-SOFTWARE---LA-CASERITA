<?php
    require_once 'Core/ajaxHelper.php';
    if (session_status() === PHP_SESSION_NONE) { session_start(); }
    if (!isset($_SESSION['usuario_id'])) { header('Location: index.php?action=login'); exit(); }
    $rolStaffPerfil = $_SESSION['usuario_rol'] ?? '';
    if (!in_array($rolStaffPerfil, ['cajero', 'repartidor'], true)) {
        header('Location: index.php?action=' . ($rolStaffPerfil === 'admin' ? 'mi_perfil_admin' : 'home_usuario'));
        exit();
    }
    $usuario = isset($usuario) && is_array($usuario) ? $usuario : [];
    $csrf_token = isset($csrf_token) ? $csrf_token : ($_SESSION['csrf_token'] ?? '');
    $remainingLock = isset($remainingLock) ? (int)$remainingLock : 0;
    $mpRolLabel = $rolStaffPerfil === 'cajero' ? 'Cajero' : 'Repartidor';
    $mpPerfilAction = 'actualizar_perfil_staff';
    $mpPasswordAction = 'cambiar_password_staff';
    $mpVerifyAction = 'verificar_password_actual_staff';
    $rutaPanelStaffPerfil = $rolStaffPerfil === 'cajero' ? 'index.php?action=panel_cajero' : 'index.php?action=panel_repartidor';
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="icon" type="image/png" href="Images/logo_backup.png">
    <title>Mi Perfil - La Caserita</title>
    <link rel="stylesheet" href="Styles/global.css">
    <link rel="stylesheet" href="Styles/staff-panel.css?v=<?= time() ?>">
    <link rel="stylesheet" href="Styles/Vistas/Perfil/mi_perfil.css?v=<?= time() ?>">
    <script src="Scripts/perfil-usuario.js?v=<?= time() ?>" defer></script>
</head>
<body class="staff-app <?= $rolStaffPerfil === 'cajero' ? 'cashier-app' : 'repartidor-panel' ?>">
    <?php $paginaPanelStaff = ''; include __DIR__ . '/partials/staff_sidebar.php'; ?>
    <div class="staff-main">
        <header class="staff-topbar">
            <div class="staff-topbar-left">
                <button type="button" class="staff-menu-toggle hide-on-desktop" id="staffMenuToggle" aria-label="Abrir menú">
                    <svg xmlns="http://www.w3.org/2000/svg" width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><line x1="3" y1="6" x2="21" y2="6"></line><line x1="3" y1="12" x2="21" y2="12"></line><line x1="3" y1="18" x2="21" y2="18"></line></svg>
                </button>
                <span class="staff-topbar-title">Mi perfil</span>
            </div>
            <div class="staff-topbar-actions">
                <a href="<?= htmlspecialchars($rutaPanelStaffPerfil) ?>" class="topbar-store-btn">
                    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"><path d="M3 9l9-7 9 7v11a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2z"></path><polyline points="9 22 9 12 15 12 15 22"></polyline></svg>
                    Mi panel
                </a>
            </div>
        </header>

        <main class="staff-content">
            <?php include __DIR__ . '/../Usuarios/partials/perfil_contenido.php'; ?>
        </main>
    </div>
</body>
</html>
