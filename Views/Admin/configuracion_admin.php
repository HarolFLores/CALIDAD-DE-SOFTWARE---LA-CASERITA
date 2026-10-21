<?php
require_once 'Core/ajaxHelper.php';
if (session_status() === PHP_SESSION_NONE) { session_start(); }

$configTienda = $configTienda ?? [];
$configOperativa = $configOperativa ?? [];
$csrfToken = $csrfToken ?? '';
$flashSuccess = $flashSuccess ?? null;
$flashError = $flashError ?? null;

$valor = function ($clave, $default = '') use ($configTienda) {
    return htmlspecialchars((string)($configTienda[$clave] ?? $default), ENT_QUOTES, 'UTF-8');
};
$hora = function ($clave, $default = '') use ($configOperativa) {
    $v = (string)($configOperativa[$clave] ?? $default);
    return htmlspecialchars(substr($v, 0, 5), ENT_QUOTES, 'UTF-8');
};
?>
<?php if (!AjaxHelper::isAjax()): ?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Configuración - ERP La Caserita</title>
    <link rel="icon" type="image/png" href="Images/logo_backup.png">
    <link rel="stylesheet" href="Styles/global.css?v=<?= time(); ?>">
    <link rel="stylesheet" href="Styles/styles-home.css?v=<?= time(); ?>">
    <link rel="stylesheet" href="Styles/styles-users-admin.css?v=<?= time(); ?>">
    <link rel="stylesheet" href="Styles/admin-spa.css?v=<?= time(); ?>">
    <link rel="stylesheet" href="Styles/Vistas/Admin/configuracion_admin.css?v=<?= filemtime(__DIR__ . '/../../Styles/Vistas/Admin/configuracion_admin.css'); ?>">
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="Scripts/admin-spa.js?v=<?= time(); ?>" defer></script>
</head>
<body>
    <div class="admin-layout">
        <?php include __DIR__ . '/partials/admin_sidebar.php'; ?>

        <div class="admin-main">
            <header class="admin-topbar">
                <div class="admin-topbar-left">
                    <button type="button" class="admin-hamburger-btn hide-on-desktop" id="openAdminSidebar" aria-label="Abrir menú">
                        <svg xmlns="http://www.w3.org/2000/svg" width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><line x1="3" y1="6" x2="21" y2="6"></line><line x1="3" y1="12" x2="21" y2="12"></line><line x1="3" y1="18" x2="21" y2="18"></line></svg>
                    </button>
                    <div class="admin-topbar-brand">
                        <span class="brand-icon">
                            <svg xmlns="http://www.w3.org/2000/svg" width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="3"></circle><path d="M19.4 15a1.65 1.65 0 0 0 .33 1.82l.06.06a2 2 0 0 1 0 2.83 2 2 0 0 1-2.83 0l-.06-.06a1.65 1.65 0 0 0-1.82-.33 1.65 1.65 0 0 0-1 1.51V21a2 2 0 0 1-4 0v-.09A1.65 1.65 0 0 0 9 19.4a1.65 1.65 0 0 0-1.82.33l-.06.06a2 2 0 0 1-2.83 0 2 2 0 0 1 0-2.83l.06-.06a1.65 1.65 0 0 0 .33-1.82 1.65 1.65 0 0 0-1.51-1H3a2 2 0 0 1 0-4h.09A1.65 1.65 0 0 0 4.6 9a1.65 1.65 0 0 0-.33-1.82l-.06-.06a2 2 0 0 1 0-2.83 2 2 0 0 1 2.83 0l.06.06a1.65 1.65 0 0 0 1.82.33H9a1.65 1.65 0 0 0 1-1.51V3a2 2 0 0 1 4 0v.09a1.65 1.65 0 0 0 1 1.51 1.65 1.65 0 0 0 1.82-.33l.06-.06a2 2 0 0 1 2.83 0 2 2 0 0 1 0 2.83l-.06.06a1.65 1.65 0 0 0-.33 1.82V9a1.65 1.65 0 0 0 1.51 1H21a2 2 0 0 1 0 4h-.09a1.65 1.65 0 0 0-1.51 1z"></path></svg>
                        </span>
                        <span class="brand-title">Configuración</span>
                    </div>
                </div>
                <?php include __DIR__ . '/partials/admin_topbar_actions.php'; ?>
            </header>

            <div id="admin-container">
<?php endif; ?>

<main class="cfg-wrapper">
    <div class="cfg-head">
        <div>
            <p class="cfg-eyebrow">Sistema</p>
            <h1>Configuración</h1>
            <p>Administra los datos de contacto, ubicación y horarios de atención.</p>
        </div>
        <div class="cfg-head-actions">
            <button type="button" class="cfg-toggle" id="cfgEditBtn" role="switch" aria-checked="false">
                <span class="cfg-toggle-track" aria-hidden="true"><span class="cfg-toggle-thumb"></span></span>
                <span class="cfg-toggle-label" id="cfgToggleLabel">Modificar datos</span>
            </button>
            <button type="submit" form="cfgForm" class="cfg-btn cfg-btn-save" id="cfgSaveBtn" hidden>Guardar cambios</button>
        </div>
    </div>

    <?php if ($flashSuccess): ?>
        <div class="cfg-flash is-success"><?= htmlspecialchars($flashSuccess, ENT_QUOTES, 'UTF-8') ?></div>
    <?php endif; ?>
    <?php if ($flashError): ?>
        <div class="cfg-flash is-error"><?= htmlspecialchars($flashError, ENT_QUOTES, 'UTF-8') ?></div>
    <?php endif; ?>

    <form class="cfg-form" id="cfgForm" method="POST" action="index.php?action=guardar_configuracion">
        <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($csrfToken, ENT_QUOTES, 'UTF-8') ?>">

        <fieldset class="cfg-fields" id="cfgFields" disabled>
        <section class="cfg-card">
            <div class="cfg-card-head">
                <div>
                    <h2>Datos de la tienda</h2>
                    <p>Información de contacto, ubicación y horarios de atención al cliente.</p>
                </div>
            </div>
            <div class="cfg-grid">
                <label class="cfg-field">
                    <span>Nombre de la tienda</span>
                    <input type="text" name="nombre_empresa" value="<?= $valor('nombre_empresa', 'La Caserita') ?>" required>
                    <small class="cfg-hint">Se usa en boletas, correos y en la tienda web.</small>
                </label>
                <label class="cfg-field">
                    <span>Teléfono / WhatsApp</span>
                    <input type="text" name="telefono_whatsapp" value="<?= $valor('telefono_whatsapp') ?>" placeholder="+51 999 999 999">
                    <small class="cfg-hint">Incluye el código de país, por ejemplo +51.</small>
                </label>
                <label class="cfg-field">
                    <span>Correo de soporte</span>
                    <input type="email" name="correo_soporte" value="<?= $valor('correo_soporte') ?>" placeholder="soporte@lacaserita.com">
                    <small class="cfg-hint">Recibirá avisos de pedidos y pagos.</small>
                </label>
                <label class="cfg-field">
                    <span>Dirección física</span>
                    <input type="text" name="direccion_fisica" value="<?= $valor('direccion_fisica') ?>" placeholder="Av. Principal 123">
                    <small class="cfg-hint">Se muestra en la web y en los comprobantes.</small>
                </label>
                <label class="cfg-field">
                    <span>Hora de apertura</span>
                    <input type="time" name="horario_apertura" value="<?= $hora('horario_apertura', '08:00') ?>">
                    <small class="cfg-hint">Sugerido: 08:00 para bodegas y minimarkets.</small>
                </label>
                <label class="cfg-field">
                    <span>Hora de cierre</span>
                    <input type="time" name="horario_cierre" value="<?= $hora('horario_cierre', '21:00') ?>">
                    <small class="cfg-hint">Sugerido: 21:00 o 22:00 según tu zona.</small>
                </label>
            </div>
        </section>
        </fieldset>
    </form>
</main>

<script src="Scripts/Vistas/Admin/configuracion_admin.js?v=<?= filemtime(__DIR__ . '/../../Scripts/Vistas/Admin/configuracion_admin.js'); ?>"></script>

<?php if (!AjaxHelper::isAjax()): ?>
            </div>
        </div>
    </div>
</body>
</html>
<?php endif; ?>
