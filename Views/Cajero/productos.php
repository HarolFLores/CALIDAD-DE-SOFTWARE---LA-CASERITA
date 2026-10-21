<?php
$productos = $productos ?? [];
$productosStockBajo = $productosStockBajo ?? [];
$nombre = htmlspecialchars($_SESSION['usuario_nombre'] ?? 'Cajero', ENT_QUOTES, 'UTF-8');

$productosVista = [];
foreach ($productos as $producto) {
    $productoNombre = (string)(($producto['nombre'] ?? '') ?: ($producto['titulo'] ?? 'Producto'));
    $sku = (string)($producto['codigo'] ?? '');
    $barcode = (string)($producto['codigo_barras'] ?? '');
    $imagen = (string)($producto['imagen'] ?? 'Images/logo_backup.png');
    $stock = (float)($producto['stock'] ?? 0);
    $stockMinimo = (float)($producto['stock_minimo'] ?? 5);
    $unidad = strtolower((string)($producto['unidad_medida'] ?? 'und.')) ?: 'und.';
    $estado = $stock <= 0 ? 'out' : ($stock <= $stockMinimo ? 'low' : 'ok');
    $estadoTexto = $estado === 'out' ? 'Agotado' : ($estado === 'low' ? 'Poco stock' : 'Disponible');
    $stockCantidad = number_format($stock, 0) . ' ' . $unidad;
    $stockMinimoCantidad = number_format($stockMinimo, 0) . ' ' . $unidad;
    $precio = (float)($producto['precio'] ?? 0);
    $precioAnterior = (float)($producto['precio_anterior'] ?? 0);
    $tieneDescuento = $precioAnterior > $precio && $precio > 0;
    $pctDescuento = $tieneDescuento ? (int)round((($precioAnterior - $precio) / $precioAnterior) * 100) : 0;
    $subcategoria = trim((string)($producto['categoria_nombre'] ?? '')) ?: 'Sin subcategoría';
    $categoriaPadre = trim((string)($producto['grupo_nombre'] ?? '')) ?: 'Sin categoría';
    $busqueda = strtolower($productoNombre . ' ' . $sku . ' ' . $barcode . ' ' . ($producto['marca'] ?? '') . ' ' . $subcategoria . ' ' . $categoriaPadre);

    $productosVista[] = [
        'nombre' => $productoNombre,
        'sku' => $sku,
        'barcode' => $barcode,
        'imagen' => $imagen,
        'stock' => $stock,
        'stock_minimo' => $stockMinimo,
        'unidad' => $unidad,
        'estado' => $estado,
        'estado_texto' => $estadoTexto,
        'stock_cantidad' => $stockCantidad,
        'stock_minimo_cantidad' => $stockMinimoCantidad,
        'precio' => $precio,
        'precio_anterior' => $precioAnterior,
        'tiene_descuento' => $tieneDescuento,
        'pct_descuento' => $pctDescuento,
        'subcategoria' => $subcategoria,
        'categoria_padre' => $categoriaPadre,
        'busqueda' => $busqueda,
    ];
}
$totalProductosVista = count($productosVista);
$totalStockBajo = count($productosStockBajo);
$totalAgotados = 0;
$totalDisponibles = 0;
$totalConDescuento = 0;
$subcategoriasCatalogo = [];
foreach ($productosVista as $item) {
    if ($item['estado'] === 'out') {
        $totalAgotados++;
    }
    if ($item['estado'] === 'ok') {
        $totalDisponibles++;
    }
    if ($item['tiene_descuento']) {
        $totalConDescuento++;
    }
    $subcategoriasCatalogo[$item['subcategoria']] = true;
}
$subcategoriasCatalogo = array_keys($subcategoriasCatalogo);
sort($subcategoriasCatalogo, SORT_NATURAL | SORT_FLAG_CASE);
$totalSubcategorias = count($subcategoriasCatalogo);
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="icon" type="image/png" href="Images/logo_backup.png">
    <title>Productos - La Caserita</title>
    <link rel="stylesheet" href="Styles/global.css">
    <link rel="stylesheet" href="Styles/staff-panel.css">
    <link rel="stylesheet" href="Styles/Vistas/Cajero/catalogo_productos.css?v=<?= filemtime(__DIR__ . '/../../Styles/Vistas/Cajero/catalogo_productos.css') ?>">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <script src="Scripts/scanner-camara.js?v=<?= filemtime(__DIR__ . '/../../Scripts/scanner-camara.js') ?>" defer></script>
</head>
<body class="staff-app cashier-app">
    <?php $paginaPanelStaff = 'productos'; include __DIR__ . '/../Staff/partials/staff_sidebar.php'; ?>
    <div class="staff-main">
        <header class="staff-topbar">
            <div class="staff-topbar-left">
                <button type="button" class="staff-menu-toggle hide-on-desktop" id="staffMenuToggle" aria-label="Abrir menú">
                    <svg xmlns="http://www.w3.org/2000/svg" width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round"><line x1="3" y1="6" x2="21" y2="6"></line><line x1="3" y1="12" x2="21" y2="12"></line><line x1="3" y1="18" x2="21" y2="18"></line></svg>
                </button>
                <span class="staff-topbar-title">Productos</span>
            </div>
            <div class="staff-topbar-actions">
            </div>
        </header>
        <main class="staff-content catalog-content">
            <div class="catalog-heading">
                <div>
                    <h1>Productos</h1>
                    <p>Consulta precios, existencias y subcategorías del catálogo.</p>
                </div>
            </div>

            <section class="catalog-quick-stats" aria-label="Resumen del catálogo">
                <button type="button" class="catalog-stat-card is-active" data-catalog-filter="all" aria-pressed="true">
                    <span>Total</span>
                    <strong><?= $totalProductosVista ?></strong>
                </button>
                <button type="button" class="catalog-stat-card is-ok" data-catalog-filter="available" aria-pressed="false">
                    <span>Disponible</span>
                    <strong><?= $totalDisponibles ?></strong>
                </button>
                <button type="button" class="catalog-stat-card is-danger" data-catalog-filter="low" aria-pressed="false">
                    <span>Stock bajo</span>
                    <strong><?= $totalStockBajo ?></strong>
                </button>
                <button type="button" class="catalog-stat-card is-danger" data-catalog-filter="out" aria-pressed="false">
                    <span>Agotados</span>
                    <strong><?= $totalAgotados ?></strong>
                </button>
                <button type="button" class="catalog-stat-card" data-catalog-filter="discount" aria-pressed="false">
                    <span>Con descuento</span>
                    <strong><?= $totalConDescuento ?></strong>
                </button>
                <button type="button" class="catalog-stat-card" data-catalog-focus="category">
                    <span>Subcategorías</span>
                    <strong><?= $totalSubcategorias ?></strong>
                </button>
            </section>

            <section class="catalog-panel">
                <div class="catalog-panel-head">
                    <h2>Productos en catálogo</h2>
                    <span class="catalog-panel-count" id="catalogPanelCount"><?= $totalProductosVista ?> productos</span>
                </div>

                <div class="catalog-toolbar">
                    <label class="catalog-search">
                        <i class="fa-solid fa-magnifying-glass" aria-hidden="true"></i>
                        <input type="search" id="cashierProductSearch" placeholder="Buscar producto, SKU, código o subcategoría..." autocomplete="off" aria-label="Buscar productos">
                        <button type="button" aria-label="Escanear código de barras" title="Escanear con el celular" onclick="UniversalCameraScanner.open({ targetInputId: 'cashierProductSearch', title: 'Emparejar Escáner Móvil', subtitle: 'Vincula tu celular con el QR; escanea con el teléfono y los productos aparecerán en esta caja.', theme: 'pos' })">
                            <i class="fa-solid fa-barcode" aria-hidden="true"></i>
                        </button>
                    </label>
                    <label class="catalog-toolbar-field" for="catalogCategoryFilter">
                        <span>Subcategoría</span>
                        <select id="catalogCategoryFilter">
                            <option value="">Todas las subcategorías</option>
                            <?php foreach ($subcategoriasCatalogo as $subcategoria): ?>
                                <option value="<?= htmlspecialchars($subcategoria, ENT_QUOTES, 'UTF-8') ?>"><?= htmlspecialchars($subcategoria, ENT_QUOTES, 'UTF-8') ?></option>
                            <?php endforeach; ?>
                        </select>
                    </label>
                    <label class="catalog-toolbar-field" for="catalogSortFilter">
                        <span>Ordenar por</span>
                        <select id="catalogSortFilter">
                            <option value="recomendados">Recomendados</option>
                            <option value="nombre_asc">Nombre (A - Z)</option>
                            <option value="stock_asc">Stock (menor a mayor)</option>
                            <option value="stock_desc">Stock (mayor a menor)</option>
                        </select>
                    </label>
                </div>

                <p class="catalog-count" id="catalogCount" aria-live="polite"></p>

                <div class="catalog-table-wrap" id="catalogTableWrap" aria-label="Catálogo de productos en tabla">
                    <table class="catalog-table">
                        <thead>
                            <tr>
                                <th>Producto</th>
                                <th>Subcategoría</th>
                                <th>Precio venta</th>
                                <th>Stock actual</th>
                                <th>Stock mínimo</th>
                                <th>Estado</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($productosVista as $item): ?>
                                <tr class="catalog-row is-<?= $item['estado'] ?><?= $item['tiene_descuento'] ? ' has-discount' : '' ?>"
                                    data-catalog-row
                                    data-name="<?= htmlspecialchars($item['nombre'], ENT_QUOTES, 'UTF-8') ?>"
                                    data-search="<?= htmlspecialchars($item['busqueda'], ENT_QUOTES, 'UTF-8') ?>"
                                    data-stock="<?= htmlspecialchars((string)$item['stock'], ENT_QUOTES, 'UTF-8') ?>"
                                    data-stockmin="<?= htmlspecialchars((string)$item['stock_minimo'], ENT_QUOTES, 'UTF-8') ?>"
                                    data-status="<?= $item['estado'] ?>"
                                    data-discount="<?= (int)$item['pct_descuento'] ?>"
                                    data-subcategory="<?= htmlspecialchars($item['subcategoria'], ENT_QUOTES, 'UTF-8') ?>"
                                    data-category="<?= htmlspecialchars($item['categoria_padre'], ENT_QUOTES, 'UTF-8') ?>">
                                    <td>
                                        <div class="catalog-table-product">
                                            <img src="<?= htmlspecialchars($item['imagen'], ENT_QUOTES, 'UTF-8') ?>" alt="" loading="lazy" onerror="this.onerror=null;this.src='Images/logo_backup.png';">
                                            <div>
                                                <strong><?= htmlspecialchars($item['nombre'], ENT_QUOTES, 'UTF-8') ?></strong>
                                                <span>SKU: <?= htmlspecialchars($item['sku'] ?: 'Sin SKU', ENT_QUOTES, 'UTF-8') ?> · <?= htmlspecialchars($item['categoria_padre'], ENT_QUOTES, 'UTF-8') ?></span>
                                                <?php if ($item['barcode']): ?>
                                                    <span>Cód: <?= htmlspecialchars($item['barcode'], ENT_QUOTES, 'UTF-8') ?></span>
                                                <?php endif; ?>
                                            </div>
                                        </div>
                                    </td>
                                    <td><span class="catalog-subcategory"><?= htmlspecialchars($item['subcategoria'], ENT_QUOTES, 'UTF-8') ?></span></td>
                                    <td>
                                        <div class="catalog-price-block">
                                            <?php if ($item['tiene_descuento']): ?>
                                                <span class="catalog-old-price">S/ <?= number_format($item['precio_anterior'], 2) ?></span>
                                            <?php endif; ?>
                                            <span class="catalog-price">S/ <?= number_format($item['precio'], 2) ?></span>
                                            <?php if ($item['tiene_descuento']): ?>
                                                <span class="catalog-discount-badge">-<?= (int)$item['pct_descuento'] ?>%</span>
                                            <?php endif; ?>
                                        </div>
                                    </td>
                                    <td><span class="catalog-table-stock is-<?= $item['estado'] ?>"><?= htmlspecialchars($item['stock_cantidad'], ENT_QUOTES, 'UTF-8') ?></span></td>
                                    <td><span class="catalog-muted"><?= htmlspecialchars($item['stock_minimo_cantidad'], ENT_QUOTES, 'UTF-8') ?></span></td>
                                    <td><span class="catalog-status is-<?= $item['estado'] ?>"><?= htmlspecialchars($item['estado_texto'], ENT_QUOTES, 'UTF-8') ?></span></td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                    <p class="catalog-empty" id="catalogEmptyTable" hidden>No hay productos que coincidan con los filtros.</p>
                </div>
                <div class="pg-footer" id="catalogPagination"></div>
            </section>
        </main>

    </div>
    <script src="Scripts/pagination.js" defer></script>
    <script src="Scripts/catalogo_productos.js" defer></script>

</body>
</html>
