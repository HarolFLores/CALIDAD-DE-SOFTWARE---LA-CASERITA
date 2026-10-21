<?php
    require_once 'Core/ajaxHelper.php';
    if(session_status() == PHP_SESSION_NONE){ session_start(); }
    if(!isset($_SESSION['usuario_id'])){ header('Location: index.php?action=login'); exit(); }
    if($_SESSION['usuario_rol'] !== 'admin'){ header('Location: index.php?action=home_usuario'); exit(); }
    $usuario = isset($usuario) && is_array($usuario) ? $usuario : [];
    $csrf_token = isset($csrf_token) ? $csrf_token : ($_SESSION['csrf_token'] ?? '');
    $remainingLock = isset($remainingLock) ? (int)$remainingLock : 0;
    $mpRolLabel = 'Administrador';
    $mpPerfilAction = 'actualizar_perfil_admin';
    $mpPasswordAction = 'cambiar_password_admin';
    $mpVerifyAction = 'verificar_password_actual_admin';
?>
<?php if (!AjaxHelper::isAjax()): ?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Mi Perfil — Admin La Caserita</title>
    <link rel="icon" type="image/png" href="Images/logo_backup.png">
    <link rel="stylesheet" href="Styles/global.css?v=<?php echo time(); ?>">
    <link rel="stylesheet" href="Styles/styles-home.css?v=<?php echo time(); ?>">
    <link rel="stylesheet" href="Styles/styles-users-admin.css?v=<?php echo time(); ?>">
    <link rel="stylesheet" href="Styles/admin-spa.css?v=<?php echo time(); ?>">
    <link rel="stylesheet" href="Styles/Vistas/Perfil/mi_perfil.css?v=<?php echo time(); ?>">
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="Scripts/admin-spa.js?v=<?php echo time(); ?>" defer></script>
    <script src="Scripts/perfil-usuario.js?v=<?php echo time(); ?>" defer></script>
</head>
<body>
    <div class="admin-layout">
        <?php include __DIR__ . '/../Admin/partials/admin_sidebar.php'; ?>

        <div class="admin-main">
            <header class="admin-topbar">
                <div class="admin-topbar-left">
                    <button type="button" class="admin-hamburger-btn hide-on-desktop" id="openAdminSidebar" aria-label="Abrir Menú">
                        <svg xmlns="http://www.w3.org/2000/svg" width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                            <line x1="3" y1="6" x2="21" y2="6"></line>
                            <line x1="3" y1="12" x2="21" y2="12"></line>
                            <line x1="3" y1="18" x2="21" y2="18"></line>
                        </svg>
                    </button>
                    <div class="admin-topbar-brand">
                        <span class="brand-icon">
                            <svg xmlns="http://www.w3.org/2000/svg" width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"></path><circle cx="12" cy="7" r="4"></circle></svg>
                        </span>
                        <span class="brand-title">Mi perfil</span>
                    </div>
                </div>
                <div class="admin-topbar-actions">
                    <?php include __DIR__ . '/../Admin/partials/admin_topbar_actions.php'; ?>
                </div>
            </header>
            <div id="admin-container">
<?php endif; ?>

<div class="main-content">
<?php include __DIR__ . '/partials/perfil_contenido.php'; ?>
</div>

<?php if (!AjaxHelper::isAjax()): ?>
            </div>
        </div>
    </div>
</body>
</html>
<?php endif; ?>
