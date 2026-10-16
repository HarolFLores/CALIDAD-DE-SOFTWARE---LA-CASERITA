<?php
$file = __DIR__ . '/../Views/Cuenta/mi_cuenta.php';
$content = file_get_contents($file);

// 1. Update the filter options and add Limpiar button
$oldToolbar = '<div class="pedidos-filter-box">
                                    <select id="filtroEstadoPedidos">
                                        <option value="todos">Todos los estados</option>
                                        <option value="pendiente">Pendiente</option>
                                        <option value="procesando">Procesando</option>
                                        <option value="enviado">Enviado</option>
                                        <option value="entregado">Entregado</option>
                                        <option value="cancelado">Cancelado</option>
                                    </select>
                                </div>';

$newToolbar = '<div class="pedidos-filter-box">
                                    <select id="filtroEstadoPedidos" title="Filtrar por estado">
                                        <option value="todos">Todos los estados</option>
                                        <option value="pendiente">Pendiente</option>
                                        <option value="en_preparacion">En preparación</option>
                                        <option value="en_camino">En camino</option>
                                        <option value="entregado">Entregado</option>
                                        <option value="cancelado">Cancelado</option>
                                    </select>
                                </div>
                                <button type="button" id="btnLimpiarFiltrosPedidos" class="btn-limpiar-filtros" title="Restablecer filtros">
                                    Limpiar
                                </button>';

// Normalize line endings for replacement
$content = str_replace(["\r\n", "\r"], "\n", $content);
$oldToolbar = str_replace(["\r\n", "\r"], "\n", $oldToolbar);
$newToolbar = str_replace(["\r\n", "\r"], "\n", $newToolbar);

if (strpos($content, $oldToolbar) !== false) {
    $content = str_replace($oldToolbar, $newToolbar, $content);
    echo "Toolbar updated successfully.\n";
} else {
    echo "Warning: oldToolbar not matched.\n";
}

// 2. Update table row attributes: data-id, data-fecha, and remove SVG icons from badges
$oldRow = '<tr class="pedido-row" data-estado="<?= htmlspecialchars($pedido[\'estado\'] ?? \'pendiente\', ENT_QUOTES, \'UTF-8\'); ?>">';
$newRow = '<?php 
    $rawEst = strtolower(trim((string)($pedido[\'estado\'] ?? \'pendiente\')));
    $rawDt = !empty($pedido[\'fecha\']) ? date(\'Y-m-d\', strtotime($pedido[\'fecha\'])) : \'\';
?>
<tr class="pedido-row" 
    data-id="<?= (int)$pedido[\'id_pedido\']; ?>" 
    data-estado="<?= htmlspecialchars($rawEst, ENT_QUOTES, \'UTF-8\'); ?>"
    data-fecha="<?= $rawDt; ?>">';

$oldRow = str_replace(["\r\n", "\r"], "\n", $oldRow);
$newRow = str_replace(["\r\n", "\r"], "\n", $newRow);

if (strpos($content, $oldRow) !== false) {
    $content = str_replace($oldRow, $newRow, $content);
    echo "Row attributes updated successfully.\n";
} else {
    echo "Warning: oldRow not matched.\n";
}

// 3. Remove SVG icons from badges and clean up
$oldEntrega = '<span class="badge-tipo-entrega" style="display:inline-flex; align-items:center; gap:4px;">
                                                    <?php if (($pedido[\'tipo_entrega\'] ?? \'delivery\') === \'delivery\'): ?>
                                                        <svg xmlns="http://www.w3.org/2000/svg" width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="1" y="3" width="15" height="13"></rect><polygon points="16 8 20 8 23 11 23 16 16 16 16 8"></polygon><circle cx="5.5" cy="18.5" r="2.5"></circle><circle cx="18.5" cy="18.5" r="2.5"></circle></svg>Delivery
                                                    <?php else: ?>
                                                        <svg xmlns="http://www.w3.org/2000/svg" width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M3 9l9-7 9 7v11a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2z"></path><polyline points="9 22 9 12 15 12 15 22"></polyline></svg>Recojo
                                                    <?php endif; ?>
                                                </span>';

$newEntrega = '<span class="badge-tipo-entrega">
                                                    <?= ucfirst(escapar($pedido[\'tipo_entrega\'] ?? \'Delivery\')); ?>
                                                </span>';

$oldEntrega = str_replace(["\r\n", "\r"], "\n", $oldEntrega);
$newEntrega = str_replace(["\r\n", "\r"], "\n", $newEntrega);

if (strpos($content, $oldEntrega) !== false) {
    $content = str_replace($oldEntrega, $newEntrega, $content);
    echo "Badge entrega updated.\n";
} else {
    echo "Warning: oldEntrega not matched.\n";
}

$oldPago = '<span class="badge-metodo-pago" style="display:inline-flex; align-items:center; gap:4px;">
                                                    <svg xmlns="http://www.w3.org/2000/svg" width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="1" y="4" width="22" height="16" rx="2" ry="2"></rect><line x1="1" y1="10" x2="23" y2="10"></line></svg>
                                                    <?= escapar($pedido[\'metodo_pago\'] ?? \'No especificado\'); ?>
                                                </span>';

$newPago = '<span class="badge-metodo-pago">
                                                    <?= escapar($pedido[\'metodo_pago\'] ?? \'No especificado\'); ?>
                                                </span>';

$oldPago = str_replace(["\r\n", "\r"], "\n", $oldPago);
$newPago = str_replace(["\r\n", "\r"], "\n", $newPago);

if (strpos($content, $oldPago) !== false) {
    $content = str_replace($oldPago, $newPago, $content);
    echo "Badge pago updated.\n";
} else {
    echo "Warning: oldPago not matched.\n";
}

// 4. Add noResultsPedidosRow
$oldEndTable = '</tbody>
                            </table>';
$newEndTable = '<tr id="noResultsPedidosRow" style="display: none;">
                                        <td colspan="7" class="text-center" style="padding: 38px 20px; color: #64748b; font-size: 0.95rem; background: #f8fafc;">
                                            No se encontraron pedidos que coincidan con los filtros aplicados.
                                        </td>
                                    </tr>
                                </tbody>
                            </table>';

$oldEndTable = str_replace(["\r\n", "\r"], "\n", $oldEndTable);
$newEndTable = str_replace(["\r\n", "\r"], "\n", $newEndTable);

if (strpos($content, $oldEndTable) !== false) {
    $content = str_replace($oldEndTable, $newEndTable, $content);
    echo "noResultsPedidosRow added.\n";
} else {
    echo "Warning: oldEndTable not matched.\n";
}

file_put_contents($file, $content);
echo "File updated successfully.\n";
