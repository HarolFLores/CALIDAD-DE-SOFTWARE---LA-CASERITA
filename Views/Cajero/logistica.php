<?php
$nombre = htmlspecialchars($_SESSION['usuario_nombre'] ?? 'Cajero', ENT_QUOTES, 'UTF-8');
$flashSuccess = $flashSuccess ?? null;
$flashError = $flashError ?? null;
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="icon" type="image/png" href="Images/logo_backup.png">
    <title>Logística y Despacho - La Caserita</title>
    <link rel="stylesheet" href="Styles/global.css">
    <link rel="stylesheet" href="Styles/staff-panel.css?v=<?= time() ?>">
    <link rel="stylesheet" href="Styles/Vistas/Cajero/logistica.css?v=<?= time() ?>">
</head>
<body class="staff-app cashier-app">
    <?php $paginaPanelStaff = 'logistica'; include __DIR__ . '/../Staff/partials/staff_sidebar.php'; ?>
    <div class="staff-main">
        <header class="staff-topbar">
            <div class="staff-topbar-left">
                <button type="button" class="staff-menu-toggle hide-on-desktop" id="staffMenuToggle" aria-label="Abrir menú">
                    <svg xmlns="http://www.w3.org/2000/svg" width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><line x1="3" y1="6" x2="21" y2="6"></line><line x1="3" y1="12" x2="21" y2="12"></line><line x1="3" y1="18" x2="21" y2="18"></line></svg>
                </button>
                <span class="staff-topbar-title">Logística y Despacho</span>
            </div>
            <div class="staff-topbar-actions">
            </div>
        </header>

        <main class="staff-content">
            <?php if ($flashSuccess): ?><div class="staff-flash success"><?= htmlspecialchars($flashSuccess) ?></div><?php endif; ?>
            <?php if ($flashError): ?><div class="staff-flash error"><?= htmlspecialchars($flashError) ?></div><?php endif; ?>

            <section class="logistica-heading">
                <div>
                    <span class="logistica-kicker">Bandeja de pedidos</span>
                    <p>Controla el estado, el despacho y la entrega desde una sola bandeja.</p>
                </div>
            </section>

            <?php include __DIR__ . '/partials/bandeja_logistica.php'; ?>
        </main>
    </div>
</body>
</html>
