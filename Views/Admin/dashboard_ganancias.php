<?php
require_once 'Core/ajaxHelper.php';
if (session_status() === PHP_SESSION_NONE) { session_start(); }

$flash_success = $_SESSION['flash_success'] ?? null;
$flash_error = $_SESSION['flash_error'] ?? null;
unset($_SESSION['flash_success'], $_SESSION['flash_error']);

if (!isset($metricas) || !is_array($metricas)) {
    $metricas = [
        'total_ventas' => 0.0,
        'total_pedidos' => 0,
        'ventas_pos' => 0.0,
        'ventas_web' => 0.0,
        'ventas_hoy' => 0.0,
        'cogs' => 0.0,
        'total_egresos' => 0.0,
        'ganancia_neta' => 0.0,
        'margen_neto' => 0.0,
        'metodos_pago' => [],
        'top_productos' => []
    ];
}

$totalVentas = (float)($metricas['total_ventas'] ?? 0);
$totalPedidos = (int)($metricas['total_pedidos'] ?? 0);
$ventasPos = (float)($metricas['ventas_pos'] ?? 0);
$ventasWeb = (float)($metricas['ventas_web'] ?? 0);
$cogs = (float)($metricas['cogs'] ?? 0);
$egresos = (float)($metricas['total_egresos'] ?? 0);
$gananciaNeta = (float)($metricas['ganancia_neta'] ?? 0);
$margenNeto = (float)($metricas['margen_neto'] ?? 0);
$ticketPromedio = $totalPedidos > 0 ? $totalVentas / $totalPedidos : 0;

$porcentaje = static function (float $valor, float $total): float {
    if ($total <= 0) return 0;
    return min(100, max(0, round(($valor / $total) * 100, 1)));
};

$porcentajeCogs = $porcentaje($cogs, $totalVentas);
$porcentajeEgresos = $porcentaje($egresos, $totalVentas);
$porcentajeGanancia = $porcentaje(max(0, $gananciaNeta), $totalVentas);
$porcentajePos = $porcentaje($ventasPos, $totalVentas);
$porcentajeWeb = $porcentaje($ventasWeb, $totalVentas);
$metodosPago = [
    'Yape' => ['metodo' => 'Yape', 'total_recaudado' => 0.0, 'cantidad_transacciones' => 0],
    'Efectivo' => ['metodo' => 'Efectivo', 'total_recaudado' => 0.0, 'cantidad_transacciones' => 0],
    'Tarjeta' => ['metodo' => 'Tarjeta', 'total_recaudado' => 0.0, 'cantidad_transacciones' => 0]
];

foreach (($metricas['metodos_pago'] ?? []) as $metodoPago) {
    $nombreMetodo = strtolower(trim((string)($metodoPago['metodo'] ?? '')));

    if (str_contains($nombreMetodo, 'efectivo')) {
        $grupoMetodo = 'Efectivo';
    } elseif (str_contains($nombreMetodo, 'yape') || str_contains($nombreMetodo, 'plin')) {
        $grupoMetodo = 'Yape';
    } else {
        $grupoMetodo = 'Tarjeta';
    }

    $metodosPago[$grupoMetodo]['total_recaudado'] += (float)($metodoPago['total_recaudado'] ?? 0);
    $metodosPago[$grupoMetodo]['cantidad_transacciones'] += (int)($metodoPago['cantidad_transacciones'] ?? 0);
}

$metodosPago = array_values($metodosPago);
$topProductos = array_slice($metricas['top_productos'] ?? [], 0, 5);
$coloresMetodo = ['#7c3aed', '#16a34a', '#2563eb'];
?>
<?php if (!AjaxHelper::isAjax()): ?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard financiero — ERP La Caserita</title>
    <link rel="icon" type="image/png" href="Images/logo_backup.png">
    <link rel="stylesheet" href="Styles/global.css?v=<?= time(); ?>">
    <link rel="stylesheet" href="Styles/styles-home.css?v=<?= time(); ?>">
    <link rel="stylesheet" href="Styles/styles-users-admin.css?v=<?= time(); ?>">
    <link rel="stylesheet" href="Styles/admin-spa.css?v=<?= time(); ?>">
    <link rel="stylesheet" href="Styles/Vistas/Admin/dashboard_ganancias.css?v=<?= filemtime(__DIR__ . '/../../Styles/Vistas/Admin/dashboard_ganancias.css'); ?>">
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
                            <svg xmlns="http://www.w3.org/2000/svg" width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"><path d="M3 3v18h18"></path><path d="m7 15 4-4 3 3 5-7"></path></svg>
                        </span>
                        <span class="brand-title">Dashboard financiero</span>
                    </div>
                </div>
                <?php include __DIR__ . '/partials/admin_topbar_actions.php'; ?>
            </header>

            <div id="admin-container">
<?php endif; ?>

<main class="fin-dashboard-wrapper">
    <?php if ($flash_success || $flash_error): ?>
        <div class="fin-alert <?= $flash_error ? 'is-error' : ''; ?>">
            <?= htmlspecialchars($flash_error ?: $flash_success); ?>
        </div>
    <?php endif; ?>

    <section class="fin-summary-grid" aria-label="Indicadores principales">
        <article class="fin-summary-card is-primary">
            <div class="fin-summary-icon">
                <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M3 3v18h18"></path><path d="m7 15 4-4 3 3 5-7"></path></svg>
            </div>
            <div class="fin-summary-copy">
                <span>Ventas totales</span>
                <strong>S/ <?= number_format($totalVentas, 2); ?></strong>
                <small>Hoy: S/ <?= number_format((float)($metricas['ventas_hoy'] ?? 0), 2); ?></small>
            </div>
        </article>

        <article class="fin-summary-card is-profit">
            <div class="fin-summary-icon">
                <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="9"></circle><path d="M16 8h-6a2 2 0 0 0 0 4h4a2 2 0 0 1 0 4H8"></path><path d="M12 6v12"></path></svg>
            </div>
            <div class="fin-summary-copy">
                <span>Ganancia neta</span>
                <strong class="<?= $gananciaNeta < 0 ? 'is-negative' : ''; ?>">S/ <?= number_format($gananciaNeta, 2); ?></strong>
                <small>Margen neto: <?= number_format($margenNeto, 1); ?>%</small>
            </div>
        </article>

        <article class="fin-summary-card">
            <div class="fin-summary-icon">
                <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M6 2 3 6v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2V6l-3-4z"></path><path d="M3 6h18"></path><path d="M16 10a4 4 0 0 1-8 0"></path></svg>
            </div>
            <div class="fin-summary-copy">
                <span>Pedidos concluidos</span>
                <strong><?= number_format($totalPedidos); ?></strong>
                <small>Operaciones registradas</small>
            </div>
        </article>

        <article class="fin-summary-card">
            <div class="fin-summary-icon">
                <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M4 19h16"></path><path d="M6 16V8"></path><path d="M12 16V4"></path><path d="M18 16v-5"></path></svg>
            </div>
            <div class="fin-summary-copy">
                <span>Ticket promedio</span>
                <strong>S/ <?= number_format($ticketPromedio, 2); ?></strong>
                <small>Ingreso promedio por pedido</small>
            </div>
        </article>
    </section>

    <section class="fin-report-grid">
        <article class="fin-report-card">
            <div class="fin-card-heading">
                <div>
                    <span class="fin-card-kicker">Rentabilidad</span>
                    <h2>Composición del resultado</h2>
                </div>
                <span class="fin-card-total">Base: S/ <?= number_format($totalVentas, 2); ?></span>
            </div>

            <div class="fin-breakdown-list">
                <div class="fin-breakdown-row">
                    <div class="fin-breakdown-meta"><span>Costo de mercadería</span><strong>S/ <?= number_format($cogs, 2); ?></strong></div>
                    <div class="fin-meter"><span class="is-cost" style="width: <?= $porcentajeCogs; ?>%"></span></div>
                    <small><?= number_format($porcentajeCogs, 1); ?>% de las ventas</small>
                </div>
                <div class="fin-breakdown-row">
                    <div class="fin-breakdown-meta"><span>Gastos operativos</span><strong>S/ <?= number_format($egresos, 2); ?></strong></div>
                    <div class="fin-meter"><span class="is-expense" style="width: <?= $porcentajeEgresos; ?>%"></span></div>
                    <small><?= number_format($porcentajeEgresos, 1); ?>% de las ventas</small>
                </div>
                <div class="fin-breakdown-row">
                    <div class="fin-breakdown-meta"><span>Ganancia neta</span><strong>S/ <?= number_format($gananciaNeta, 2); ?></strong></div>
                    <div class="fin-meter"><span class="is-profit" style="width: <?= $porcentajeGanancia; ?>%"></span></div>
                    <small><?= number_format($margenNeto, 1); ?>% de margen neto</small>
                </div>
            </div>
        </article>

        <article class="fin-report-card">
            <div class="fin-card-heading">
                <div>
                    <span class="fin-card-kicker">Canales</span>
                    <h2>Origen de las ventas</h2>
                </div>
            </div>

            <div class="fin-channel-list">
                <div class="fin-channel-item is-pos">
                    <div><span>POS tienda</span><strong>S/ <?= number_format($ventasPos, 2); ?></strong></div>
                    <div class="fin-meter"><span style="width: <?= $porcentajePos; ?>%"></span></div>
                    <small><?= number_format($porcentajePos, 1); ?>% del total</small>
                </div>
                <div class="fin-channel-item is-web">
                    <div><span>Web e-commerce</span><strong>S/ <?= number_format($ventasWeb, 2); ?></strong></div>
                    <div class="fin-meter"><span style="width: <?= $porcentajeWeb; ?>%"></span></div>
                    <small><?= number_format($porcentajeWeb, 1); ?>% del total</small>
                </div>
            </div>
        </article>
    </section>

    <section class="fin-detail-grid">
        <article class="fin-report-card fin-products-card">
            <div class="fin-card-heading">
                <div>
                    <span class="fin-card-kicker">Productos</span>
                    <h2>Mayor aporte al negocio</h2>
                </div>
                <span class="fin-card-total">Top <?= count($topProductos); ?></span>
            </div>

            <div class="fin-table-wrap">
                <table class="fin-table">
                    <thead>
                        <tr>
                            <th>Producto</th>
                            <th class="is-center">Unidades</th>
                            <th class="is-right">Ingresos</th>
                            <th class="is-right">Ganancia</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if ($topProductos): ?>
                            <?php foreach ($topProductos as $idx => $producto): ?>
                                <tr>
                                    <td>
                                        <div class="fin-product-name">
                                            <span><?= $idx + 1; ?></span>
                                            <strong><?= htmlspecialchars($producto['nombre']); ?></strong>
                                        </div>
                                    </td>
                                    <td class="is-center"><?= number_format((float)$producto['total_unidades_vendidas'], 0); ?></td>
                                    <td class="is-right">S/ <?= number_format((float)$producto['total_ingresos'], 2); ?></td>
                                    <td class="is-right fin-positive">S/ <?= number_format((float)$producto['ganancia_aportada'], 2); ?></td>
                                </tr>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <tr><td colspan="4" class="fin-empty">Aún no hay ventas suficientes para generar este reporte.</td></tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </article>

        <article class="fin-report-card">
            <div class="fin-card-heading">
                <div>
                    <span class="fin-card-kicker">Cobros</span>
                    <h2>Métodos de pago</h2>
                </div>
            </div>

            <div class="fin-payment-list">
                <?php if ($metodosPago): ?>
                    <?php foreach ($metodosPago as $idx => $metodo):
                        $montoMetodo = (float)($metodo['total_recaudado'] ?? 0);
                        $porcentajeMetodo = $porcentaje($montoMetodo, $totalVentas);
                        $colorMetodo = $coloresMetodo[$idx % count($coloresMetodo)];
                    ?>
                        <div class="fin-payment-row" style="--payment-color: <?= $colorMetodo; ?>;">
                            <div class="fin-payment-main">
                                <span><i></i><?= htmlspecialchars($metodo['metodo']); ?></span>
                                <strong>S/ <?= number_format($montoMetodo, 2); ?></strong>
                            </div>
                            <div class="fin-meter"><span style="width: <?= $porcentajeMetodo; ?>%"></span></div>
                            <small><?= (int)($metodo['cantidad_transacciones'] ?? 0); ?> operaciones · <?= number_format($porcentajeMetodo, 1); ?>%</small>
                        </div>
                    <?php endforeach; ?>
                <?php else: ?>
                    <div class="fin-empty">No hay métodos de pago registrados.</div>
                <?php endif; ?>
            </div>
        </article>
    </section>
</main>

<?php if (!AjaxHelper::isAjax()): ?>
            </div>
        </div>
    </div>
</body>
</html>
<?php endif; ?>
