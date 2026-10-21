<?php
require_once 'Core/ajaxHelper.php';
if (session_status() === PHP_SESSION_NONE) { session_start(); }

$reporte = $reporte ?? [];
$desde = $reporte['desde'] ?? date('Y-m-01');
$hasta = $reporte['hasta'] ?? date('Y-m-d');
$canal = $reporte['canal'] ?? 'all';
$totalVentas = (float)($reporte['total_ventas'] ?? 0);
$totalPedidos = (int)($reporte['total_pedidos'] ?? 0);
$ticketPromedio = (float)($reporte['ticket_promedio'] ?? 0);
$cogs = (float)($reporte['cogs'] ?? 0);
$gananciaBruta = (float)($reporte['ganancia_bruta'] ?? 0);
$margenBruto = (float)($reporte['margen_bruto'] ?? 0);
$porDia = $reporte['por_dia'] ?? [];
$porProducto = $reporte['por_producto'] ?? [];
$porCategoria = $reporte['por_categoria'] ?? [];
$porMetodo = $reporte['por_metodo'] ?? [];
$porCanal = $reporte['por_canal'] ?? [];
$canalTexto = $canal === 'all' ? 'Todos los canales' : $canal;
$exportUrl = 'index.php?action=exportar_reporte_ventas&desde=' . urlencode($desde) . '&hasta=' . urlencode($hasta) . '&canal=' . urlencode($canal);
$flashSuccess = $_SESSION['flash_success'] ?? null;
$flashError = $_SESSION['flash_error'] ?? null;
unset($_SESSION['flash_success'], $_SESSION['flash_error']);
?>
<?php if (!AjaxHelper::isAjax()): ?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reportes - ERP La Caserita</title>
    <link rel="icon" type="image/png" href="Images/logo_backup.png">
    <link rel="stylesheet" href="Styles/global.css?v=<?= time(); ?>">
    <link rel="stylesheet" href="Styles/styles-home.css?v=<?= time(); ?>">
    <link rel="stylesheet" href="Styles/styles-users-admin.css?v=<?= time(); ?>">
    <link rel="stylesheet" href="Styles/admin-spa.css?v=<?= time(); ?>">
    <link rel="stylesheet" href="Styles/Vistas/Admin/reportes_admin.css?v=<?= filemtime(__DIR__ . '/../../Styles/Vistas/Admin/reportes_admin.css'); ?>">
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
                            <svg xmlns="http://www.w3.org/2000/svg" width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"></path><polyline points="14 2 14 8 20 8"></polyline><line x1="8" y1="18" x2="8" y2="12"></line><line x1="12" y1="18" x2="12" y2="9"></line><line x1="16" y1="18" x2="16" y2="15"></line></svg>
                        </span>
                        <span class="brand-title">Reportes</span>
                    </div>
                </div>
                <div class="admin-topbar-actions">
                    <?php include __DIR__ . '/partials/admin_topbar_actions.php'; ?>
                </div>
            </header>

            <div id="admin-container">
<?php endif; ?>

<main class="rep-wrapper">
    <div class="rep-head">
        <div>
            <p class="rep-eyebrow">Análisis comercial</p>
            <h1>Reportes de ventas</h1>
            <p>Consulta ventas, rentabilidad y rendimiento por fecha, producto, categoría y método de pago.</p>
        </div>
        <a class="rep-export" href="<?= htmlspecialchars($exportUrl, ENT_QUOTES, 'UTF-8') ?>">
            <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"></path><polyline points="7 10 12 15 17 10"></polyline><line x1="12" y1="15" x2="12" y2="3"></line></svg>
            Exportar CSV
        </a>
    </div>

    <?php if ($flashSuccess): ?>
        <div class="rep-flash is-success"><?= htmlspecialchars($flashSuccess, ENT_QUOTES, 'UTF-8') ?></div>
    <?php endif; ?>
    <?php if ($flashError): ?>
        <div class="rep-flash is-error"><?= htmlspecialchars($flashError, ENT_QUOTES, 'UTF-8') ?></div>
    <?php endif; ?>

    <section class="rep-kpis" aria-label="Resumen del reporte">
        <article class="rep-kpi">
            <span>Ventas totales</span>
            <strong>S/ <?= number_format($totalVentas, 2) ?></strong>
            <small><?= htmlspecialchars($canalTexto, ENT_QUOTES, 'UTF-8') ?></small>
        </article>
        <article class="rep-kpi">
            <span>Pedidos</span>
            <strong><?= number_format($totalPedidos) ?></strong>
            <small>Ventas concluidas</small>
        </article>
        <article class="rep-kpi">
            <span>Ticket promedio</span>
            <strong>S/ <?= number_format($ticketPromedio, 2) ?></strong>
            <small>Por pedido</small>
        </article>
        <article class="rep-kpi">
            <span>Costo de ventas</span>
            <strong>S/ <?= number_format($cogs, 2) ?></strong>
            <small>Costo de productos vendidos</small>
        </article>
        <article class="rep-kpi is-profit">
            <span>Ganancia bruta</span>
            <strong>S/ <?= number_format($gananciaBruta, 2) ?></strong>
            <small>Ventas menos costo</small>
        </article>
        <article class="rep-kpi is-profit">
            <span>Margen bruto</span>
            <strong><?= number_format($margenBruto, 1) ?>%</strong>
            <small>Rentabilidad sobre ventas</small>
        </article>
    </section>

    <form class="rep-filters" method="GET" action="index.php">
        <input type="hidden" name="action" value="reportes_admin">
        <label>
            <span>Desde</span>
            <input type="date" name="desde" value="<?= htmlspecialchars($desde, ENT_QUOTES, 'UTF-8') ?>">
        </label>
        <label>
            <span>Hasta</span>
            <input type="date" name="hasta" value="<?= htmlspecialchars($hasta, ENT_QUOTES, 'UTF-8') ?>">
        </label>
        <label>
            <span>Canal</span>
            <select name="canal">
                <option value="all" <?= $canal === 'all' ? 'selected' : '' ?>>Todos</option>
                <option value="POS" <?= $canal === 'POS' ? 'selected' : '' ?>>POS</option>
                <option value="WEB" <?= $canal === 'WEB' ? 'selected' : '' ?>>Web</option>
            </select>
        </label>
        <button type="submit" class="rep-apply">Aplicar filtros</button>
        <a class="rep-reset" href="index.php?action=reportes_admin">Limpiar</a>
    </form>

    <section class="rep-panel">
        <div class="rep-panel-head">
            <div>
                <h2>Ventas por día</h2>
                <p>Evolución diaria del periodo seleccionado.</p>
            </div>
            <span class="rep-badge"><?= htmlspecialchars($desde, ENT_QUOTES, 'UTF-8') ?> al <?= htmlspecialchars($hasta, ENT_QUOTES, 'UTF-8') ?></span>
        </div>
        <div class="rep-table-wrap">
            <table class="rep-table">
                <thead>
                    <tr>
                        <th>Fecha</th>
                        <th class="is-right">Pedidos</th>
                        <th class="is-right">Ventas</th>
                        <th class="is-right">Costo</th>
                        <th class="is-right">Ganancia</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (!empty($porDia)): ?>
                        <?php foreach ($porDia as $fila): ?>
                            <tr>
                                <td><?= date('d/m/Y', strtotime($fila['fecha'])) ?></td>
                                <td class="is-right"><?= (int)$fila['pedidos'] ?></td>
                                <td class="is-right">S/ <?= number_format((float)$fila['total'], 2) ?></td>
                                <td class="is-right">S/ <?= number_format((float)$fila['costo'], 2) ?></td>
                                <td class="is-right is-profit">S/ <?= number_format((float)$fila['ganancia'], 2) ?></td>
                            </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr><td colspan="5" class="rep-empty">No hay ventas en el periodo seleccionado.</td></tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </section>

    <section class="rep-panel">
        <div class="rep-panel-head">
            <div>
                <h2>Rentabilidad por producto</h2>
                <p>Productos ordenados por ingresos generados.</p>
            </div>
            <span class="rep-badge"><?= count($porProducto) ?> productos</span>
        </div>
        <div class="rep-table-wrap">
            <table class="rep-table">
                <thead>
                    <tr>
                        <th>Producto</th>
                        <th>Subcategoría</th>
                        <th class="is-right">Unidades</th>
                        <th class="is-right">Ingresos</th>
                        <th class="is-right">Ganancia</th>
                        <th class="is-right">Margen</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (!empty($porProducto)): ?>
                        <?php foreach ($porProducto as $fila): ?>
                            <tr>
                                <td><?= htmlspecialchars($fila['producto'], ENT_QUOTES, 'UTF-8') ?></td>
                                <td><?= htmlspecialchars($fila['categoria'], ENT_QUOTES, 'UTF-8') ?></td>
                                <td class="is-right"><?= number_format((float)$fila['unidades'], 2) ?></td>
                                <td class="is-right">S/ <?= number_format((float)$fila['ingresos'], 2) ?></td>
                                <td class="is-right is-profit">S/ <?= number_format((float)$fila['ganancia'], 2) ?></td>
                                <td class="is-right"><?= number_format((float)$fila['margen'], 1) ?>%</td>
                            </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr><td colspan="6" class="rep-empty">No hay productos vendidos en el periodo.</td></tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </section>

    <div class="rep-grid">
        <section class="rep-panel">
            <div class="rep-panel-head">
                <div>
                    <h2>Ventas por subcategoría</h2>
                    <p>Ingresos y ganancia por subcategoría.</p>
                </div>
            </div>
            <div class="rep-table-wrap">
                <table class="rep-table">
                    <thead>
                        <tr>
                            <th>Subcategoría</th>
                            <th class="is-right">Ingresos</th>
                            <th class="is-right">Ganancia</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (!empty($porCategoria)): ?>
                            <?php foreach ($porCategoria as $fila): ?>
                                <tr>
                                    <td><?= htmlspecialchars($fila['categoria'], ENT_QUOTES, 'UTF-8') ?></td>
                                    <td class="is-right">S/ <?= number_format((float)$fila['ingresos'], 2) ?></td>
                                    <td class="is-right is-profit">S/ <?= number_format((float)$fila['ganancia'], 2) ?></td>
                                </tr>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <tr><td colspan="3" class="rep-empty">Sin subcategorías vendidas.</td></tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </section>

        <section class="rep-panel">
            <div class="rep-panel-head">
                <div>
                    <h2>Métodos de pago</h2>
                    <p>Recaudación por método.</p>
                </div>
            </div>
            <div class="rep-table-wrap">
                <table class="rep-table rep-table-pagos">
                    <thead>
                        <tr>
                            <th>Método</th>
                            <th class="is-right">Pedidos</th>
                            <th class="is-right">Total</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (!empty($porMetodo)): ?>
                            <?php foreach ($porMetodo as $fila): ?>
                                <tr>
                                    <td><?= htmlspecialchars($fila['metodo'], ENT_QUOTES, 'UTF-8') ?></td>
                                    <td class="is-right"><?= (int)$fila['pedidos'] ?></td>
                                    <td class="is-right">S/ <?= number_format((float)$fila['total'], 2) ?></td>
                                </tr>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <tr><td colspan="3" class="rep-empty">Sin pagos registrados.</td></tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </section>
    </div>

    <section class="rep-panel">
        <div class="rep-panel-head">
            <div>
                <h2>Ventas por canal</h2>
                <p>Comparativo entre POS y tienda web.</p>
            </div>
        </div>
        <div class="rep-table-wrap">
            <table class="rep-table">
                <thead>
                    <tr>
                        <th>Canal</th>
                        <th class="is-right">Pedidos</th>
                        <th class="is-right">Total</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (!empty($porCanal)): ?>
                        <?php foreach ($porCanal as $fila): ?>
                            <tr>
                                <td><?= htmlspecialchars($fila['canal'], ENT_QUOTES, 'UTF-8') ?></td>
                                <td class="is-right"><?= (int)$fila['pedidos'] ?></td>
                                <td class="is-right">S/ <?= number_format((float)$fila['total'], 2) ?></td>
                            </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr><td colspan="3" class="rep-empty">Sin ventas registradas.</td></tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </section>
</main>

<?php if (!AjaxHelper::isAjax()): ?>
            </div>
        </div>
    </div>
</body>
</html>
<?php endif; ?>
