<?php
require_once 'Core/ajaxHelper.php';
if (session_status() === PHP_SESSION_NONE) { session_start(); }
if (!isset($_SESSION['usuario_id']) || $_SESSION['usuario_rol'] !== 'admin') {
    if (AjaxHelper::isAjax()) { AjaxHelper::jsonError('No autorizado.', 403); }
    header('Location: index.php?action=login'); exit();
}

$nombre = htmlspecialchars($_SESSION['usuario_nombre'] ?? 'Administrador');
$flash_success = $_SESSION['flash_success'] ?? null;
$flash_error = $_SESSION['flash_error'] ?? null;
unset($_SESSION['flash_success'], $_SESSION['flash_error']);

$estaAbierta = !empty($sesionActiva);
$isAjax = AjaxHelper::isAjax();
$soloLectura = $soloLectura ?? false;
$resumen = $resumen ?? [];
$movimientos = $movimientos ?? [];
$historialSesiones = $historialSesiones ?? [];
$totalMovimientos = count($movimientos);
$totalHistorial = count($historialSesiones);
$cajeroActivo = $estaAbierta
    ? trim((string)($sesionActiva['cajero_nombre'] ?? '') . ' ' . (string)($sesionActiva['cajero_apellido'] ?? ''))
    : '';
?>
<?php if (!$isAjax): ?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Supervisi&oacute;n de caja - ERP La Caserita</title>
    <link rel="icon" type="image/png" href="Images/logo_backup.png">
    <link rel="stylesheet" href="Styles/global.css?v=<?= time(); ?>">
    <link rel="stylesheet" href="Styles/styles-home.css?v=<?= time(); ?>">
    <link rel="stylesheet" href="Styles/styles-users-admin.css?v=<?= time(); ?>">
    <link rel="stylesheet" href="Styles/admin-spa.css?v=<?= time(); ?>">
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="Scripts/admin-spa.js?v=<?= time(); ?>" defer></script>
    <script src="Scripts/pagination.js?v=<?= time(); ?>"></script>
    <link rel="stylesheet" href="Styles/Vistas/Admin/caja_admin.css?v=<?= filemtime(__DIR__ . '/../../Styles/Vistas/Admin/caja_admin.css'); ?>">
</head>
<body>
    <div class="admin-layout">
        <?php include __DIR__ . '/partials/admin_sidebar.php'; ?>

        <div class="admin-main">
            <header class="admin-topbar">
                <div class="admin-topbar-left">
                    <button type="button" class="admin-hamburger-btn hide-on-desktop" id="openAdminSidebar" aria-label="Abrir Menú">
                        <svg xmlns="http://www.w3.org/2000/svg" width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><line x1="3" y1="6" x2="21" y2="6"></line><line x1="3" y1="12" x2="21" y2="12"></line><line x1="3" y1="18" x2="21" y2="18"></line></svg>
                    </button>
                    <div class="admin-topbar-brand">
                        <span class="brand-icon">
                            <svg xmlns="http://www.w3.org/2000/svg" width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="2" y="4" width="20" height="16" rx="2"></rect><line x1="12" y1="8" x2="12" y2="16"></line><line x1="8" y1="12" x2="16" y2="12"></line></svg>
                        </span>
                        <span class="brand-title">Supervisi&oacute;n de caja</span>
                    </div>
                </div>
                <div class="admin-topbar-actions">
                    <?php include __DIR__ . '/partials/admin_topbar_actions.php'; ?>
                </div>
            </header>

        <div id="admin-container">
<?php endif; ?>

    <main class="main-content container-admin admin-fullwidth caja-admin-page">
        <?php if ($flash_success || $flash_error): ?>
            <div class="caja-flash <?= $flash_error ? 'is-error' : 'is-success'; ?>" role="status">
                <?= htmlspecialchars($flash_error ?: $flash_success); ?>
            </div>
        <?php endif; ?>

        <div class="stats-grid-square caja-stats-grid">
            <div class="stat-card-square">
                <span class="stat-title">MONTO APERTURA</span>
                <span class="stat-val">S/ <?= number_format((float)($resumen['apertura'] ?? 0), 2); ?></span>
            </div>
            <div class="stat-card-square">
                <span class="stat-title">VENTAS EFECTIVO</span>
                <span class="stat-val caja-value-positive">+ S/ <?= number_format((float)($resumen['ventas_efectivo'] ?? 0), 2); ?></span>
            </div>
            <div class="stat-card-square">
                <span class="stat-title">INGRESOS EXTRAS</span>
                <span class="stat-val caja-value-info">+ S/ <?= number_format((float)($resumen['ingresos_extra'] ?? 0), 2); ?></span>
            </div>
            <div class="stat-card-square">
                <span class="stat-title">EGRESOS / GASTOS</span>
                <span class="stat-val caja-value-negative">- S/ <?= number_format((float)($resumen['egresos'] ?? 0), 2); ?></span>
            </div>
            <div class="stat-card-square">
                <span class="stat-title">VENTAS YAPE</span>
                <span class="stat-val caja-value-violet">S/ <?= number_format((float)($resumen['ventas_yape'] ?? 0), 2); ?></span>
            </div>
            <div class="stat-card-square caja-stat-highlight">
                <span class="stat-title">EFECTIVO ESPERADO</span>
                <span class="stat-val caja-value-positive">S/ <?= number_format((float)($resumen['efectivo_esperado'] ?? 0), 2); ?></span>
            </div>
        </div>

        <section class="panel caja-status-panel <?= $estaAbierta ? 'is-open' : 'is-closed'; ?>">
            <div class="caja-status-main">
                <div class="caja-status-heading">
                    <span class="caja-status-badge <?= $estaAbierta ? 'is-open' : 'is-closed'; ?>">
                        <span class="caja-status-dot"></span>
                        <?= $estaAbierta ? 'Caja abierta' : 'Caja cerrada'; ?>
                    </span>
                    <?php if ($soloLectura): ?>
                        <span class="caja-readonly-pill">Supervisión de solo lectura</span>
                    <?php endif; ?>
                </div>

                <?php if ($estaAbierta): ?>
                    <h1><?= htmlspecialchars($sesionActiva['caja_id']); ?> <span class="caja-separator">·</span> <?= htmlspecialchars($cajeroActivo); ?></h1>
                    <p>Aperturada el <?= date('d/m/Y h:i A', strtotime($sesionActiva['fecha_apertura'])); ?> con un fondo inicial de <strong>S/ <?= number_format((float)$sesionActiva['monto_apertura'], 2); ?></strong>.</p>
                <?php else: ?>
                    <h1>No hay turno de caja abierto</h1>
                    <p>La caja la opera un cajero. Desde aquí puedes supervisar los turnos, movimientos y arqueos.</p>
                <?php endif; ?>
            </div>

            <div class="caja-status-side">
                <span class="caja-status-side-label">Efectivo físico esperado</span>
                <strong class="caja-status-side-value">S/ <?= number_format((float)($resumen['efectivo_esperado'] ?? 0), 2); ?></strong>
                <span class="caja-status-side-meta"><?= $estaAbierta ? 'Turno en curso' : 'Sin turno activo'; ?></span>
            </div>
        </section>

        <section class="panel caja-panel">
            <div class="panel-header">
                <div class="panel-heading-row">
                    <div>
                        <h2>Movimientos de caja chica</h2>
                        <p class="caja-panel-subtitle">Ingresos y egresos registrados en el turno actual.</p>
                    </div>
                    <span class="caja-count-pill"><?= $totalMovimientos; ?> movimiento<?= $totalMovimientos === 1 ? '' : 's'; ?></span>
                </div>
            </div>

            <div class="table-responsive">
                <table class="table caja-table" id="tablaMovimientosCaja">
                    <thead>
                        <tr>
                            <th>Hora</th>
                            <th>Tipo</th>
                            <th>Categoría</th>
                            <th>Motivo</th>
                            <th>Comprobante</th>
                            <th class="caja-cell-right">Monto</th>
                            <th>Registrado por</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (!empty($movimientos)): ?>
                            <?php foreach ($movimientos as $mv): ?>
                                <?php $esIngreso = ($mv['tipo'] ?? '') === 'INGRESO'; ?>
                                <tr class="caja-mov-row">
                                    <td class="caja-cell-muted"><?= date('h:i A', strtotime($mv['fecha'])); ?></td>
                                    <td>
                                        <span class="caja-type-badge <?= $esIngreso ? 'is-income' : 'is-expense'; ?>">
                                            <?= htmlspecialchars($mv['tipo']); ?>
                                        </span>
                                    </td>
                                    <td class="caja-cell-capitalize"><?= htmlspecialchars($mv['categoria']); ?></td>
                                    <td class="caja-cell-strong"><?= htmlspecialchars($mv['motivo']); ?></td>
                                    <td><span class="caja-code"><?= htmlspecialchars($mv['comprobante_ref'] ?: '—'); ?></span></td>
                                    <td class="caja-cell-right caja-cell-amount <?= $esIngreso ? 'is-income' : 'is-expense'; ?>">
                                        <?= $esIngreso ? '+' : '-'; ?> S/ <?= number_format((float)$mv['monto'], 2); ?>
                                    </td>
                                    <td class="caja-cell-muted"><?= htmlspecialchars($mv['usuario_nombre']); ?></td>
                                </tr>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="7" class="caja-empty">No hay movimientos de caja chica en este turno.</td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
            <?php if (!empty($movimientos)): ?>
                <div id="paginacionMovimientosList"></div>
            <?php endif; ?>
        </section>

        <section class="panel caja-panel">
            <div class="panel-header">
                <div class="panel-heading-row">
                    <div>
                        <h2>Historial de sesiones y arqueos</h2>
                        <p class="caja-panel-subtitle">Turnos anteriores, montos esperados y diferencias de cierre.</p>
                    </div>
                    <span class="caja-count-pill"><?= $totalHistorial; ?> sesión<?= $totalHistorial === 1 ? '' : 'es'; ?></span>
                </div>
            </div>

            <div class="table-responsive">
                <table class="table caja-table" id="tablaHistorialCaja">
                    <thead>
                        <tr>
                            <th>Caja ID</th>
                            <th>Cajero</th>
                            <th>Apertura</th>
                            <th>Cierre</th>
                            <th class="caja-cell-right">Fondo apertura</th>
                            <th class="caja-cell-right">Ventas efectivo</th>
                            <th class="caja-cell-right">Esperado</th>
                            <th class="caja-cell-right">Real contado</th>
                            <th class="caja-cell-center">Diferencia</th>
                            <th class="caja-cell-center">Estado</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (!empty($historialSesiones)): ?>
                            <?php foreach ($historialSesiones as $hs): ?>
                                <?php
                                    $dif = (float)($hs['diferencia'] ?? 0);
                                    $cerrada = ($hs['estado'] ?? '') === 'cerrada';
                                ?>
                                <tr class="caja-hist-row">
                                    <td class="caja-cell-strong"><?= htmlspecialchars($hs['caja_id']); ?></td>
                                    <td class="caja-cell-name"><?= htmlspecialchars(trim((string)$hs['cajero_nombre'] . ' ' . (string)$hs['cajero_apellido'])); ?></td>
                                    <td>
                                        <span class="caja-date-main"><?= date('d/m/y', strtotime($hs['fecha_apertura'])); ?></span>
                                        <span class="caja-date-sub"><?= date('H:i', strtotime($hs['fecha_apertura'])); ?></span>
                                    </td>
                                    <td>
                                        <?php if (!empty($hs['fecha_cierre'])): ?>
                                            <span class="caja-date-main"><?= date('d/m/y', strtotime($hs['fecha_cierre'])); ?></span>
                                            <span class="caja-date-sub"><?= date('H:i', strtotime($hs['fecha_cierre'])); ?></span>
                                        <?php else: ?>
                                            <span class="caja-cell-muted">—</span>
                                        <?php endif; ?>
                                    </td>
                                    <td class="caja-cell-right">S/ <?= number_format((float)$hs['monto_apertura'], 2); ?></td>
                                    <td class="caja-cell-right caja-cell-amount is-income">+ S/ <?= number_format((float)$hs['total_ventas_efectivo'], 2); ?></td>
                                    <td class="caja-cell-right">S/ <?= number_format((float)($hs['monto_cierre_esperado'] ?? 0), 2); ?></td>
                                    <td class="caja-cell-right caja-cell-amount">S/ <?= number_format((float)($hs['monto_cierre_real'] ?? 0), 2); ?></td>
                                    <td class="caja-cell-center">
                                        <?php if ($cerrada): ?>
                                            <?php if (abs($dif) < 0.01): ?>
                                                <span class="caja-diff is-exact">Exacto</span>
                                            <?php elseif ($dif > 0): ?>
                                                <span class="caja-diff is-over">+ S/ <?= number_format($dif, 2); ?></span>
                                            <?php else: ?>
                                                <span class="caja-diff is-under">- S/ <?= number_format(abs($dif), 2); ?></span>
                                            <?php endif; ?>
                                        <?php else: ?>
                                            <span class="caja-cell-muted">—</span>
                                        <?php endif; ?>
                                    </td>
                                    <td class="caja-cell-center">
                                        <span class="status-pill <?= $cerrada ? 'inactivo' : 'activo'; ?>">
                                            <?= $cerrada ? 'Cerrada' : 'Abierta'; ?>
                                        </span>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="10" class="caja-empty">No hay historial de sesiones todavía.</td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
            <?php if (!empty($historialSesiones)): ?>
                <div id="paginacionHistorialList"></div>
            <?php endif; ?>
        </section>
    </main>

    <script src="Scripts/Vistas/Admin/caja_admin.js?v=<?= filemtime(__DIR__ . '/../../Scripts/Vistas/Admin/caja_admin.js'); ?>"></script>

<?php if (!$isAjax): ?>
        </div>
    </div>
</div>
</body>
</html>
<?php endif; ?>
