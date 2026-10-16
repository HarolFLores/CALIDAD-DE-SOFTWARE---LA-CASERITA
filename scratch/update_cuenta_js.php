<?php
$file = __DIR__ . '/../Scripts/cuenta.js';
$content = file_get_contents($file);

$oldCode = '    const searchInput = document.getElementById(\'busquedaPedidos\');
    const filterSelect = document.getElementById(\'filtroEstadoPedidos\');
    const dateInput = document.getElementById(\'filtroFechaPedidos\');
    const orderRows = document.querySelectorAll(\'.pedido-row\');

    function filterOrders() {
        if (!searchInput || !filterSelect || !dateInput) return;
        
        const searchTerm = searchInput.value.toLowerCase();
        const selectedStatus = filterSelect.value.toLowerCase();
        const selectedDate = dateInput.value; 

        orderRows.forEach(row => {
            const orderId = row.querySelector(\'.pedido-id\').textContent.toLowerCase();
            const orderStatus = row.getAttribute(\'data-estado\').toLowerCase();
            const rawDate = row.querySelector(\'.pedido-fecha-raw\').getAttribute(\'data-raw-date\');

            const matchesSearch = orderId.includes(searchTerm);
            const matchesStatus = selectedStatus === \'todos\' || orderStatus === selectedStatus;
            const matchesDate = !selectedDate || rawDate === selectedDate;

            if (matchesSearch && matchesStatus && matchesDate) {
                row.style.display = \'\';
            } else {
                row.style.display = \'none\';
            }
        });
    }

    if (searchInput) {
        searchInput.addEventListener(\'input\', filterOrders);
    }
    
    if (filterSelect) {
        filterSelect.addEventListener(\'change\', filterOrders);
    }

    if (dateInput) {
        dateInput.addEventListener(\'change\', filterOrders);
    }';

$newCode = '    const searchInput = document.getElementById(\'busquedaPedidos\');
    const filterSelect = document.getElementById(\'filtroEstadoPedidos\');
    const dateInput = document.getElementById(\'filtroFechaPedidos\');
    const btnLimpiar = document.getElementById(\'btnLimpiarFiltrosPedidos\');
    const orderRows = document.querySelectorAll(\'.pedido-row\');
    const noResultsRow = document.getElementById(\'noResultsPedidosRow\');
    const countBadge = document.querySelector(\'.cuenta-count-badge\');
    const totalOrdersCount = orderRows.length;

    function filterOrders() {
        const rawSearch = searchInput ? searchInput.value.trim().toLowerCase() : \'\';
        // Quitar "#" inicial si el usuario busca "#30" o "#00030"
        const searchTerm = rawSearch.startsWith(\'#\') ? rawSearch.substring(1).trim() : rawSearch;
        const selectedStatus = filterSelect ? filterSelect.value.toLowerCase().replace(/[\\s_-]+/g, \'\') : \'todos\';
        const selectedDate = dateInput ? dateInput.value : \'\'; 

        let visibleCount = 0;

        orderRows.forEach(row => {
            const rawId = (row.getAttribute(\'data-id\') || \'\').toLowerCase();
            const idCell = row.querySelector(\'.pedido-id-text, .pedido-id-cell, td:first-child\');
            const idText = idCell ? idCell.textContent.toLowerCase().replace(\'#\', \'\').trim() : \'\';
            const rowText = row.textContent.toLowerCase();
            
            const rawStatus = (row.getAttribute(\'data-estado\') || \'\').toLowerCase().replace(/[\\s_-]+/g, \'\');
            const rawDate = row.getAttribute(\'data-fecha\') || (row.querySelector(\'.pedido-fecha-raw\') ? row.querySelector(\'.pedido-fecha-raw\').getAttribute(\'data-raw-date\') : \'\');

            // 1. Búsqueda por N° pedido (ID exacto o parcial), o texto general
            let matchesSearch = true;
            if (searchTerm !== \'\') {
                matchesSearch = rawId.includes(searchTerm) || 
                                idText.includes(searchTerm) || 
                                rowText.includes(searchTerm) || 
                                (rawSearch.startsWith(\'#\') && rowText.includes(rawSearch));
            }

            // 2. Coincidencia de estado mapeando sinónimos
            let matchesStatus = true;
            if (selectedStatus !== \'todos\' && selectedStatus !== \'\') {
                if (selectedStatus === \'enpreparacion\' || selectedStatus === \'procesando\') {
                    matchesStatus = [\'enpreparacion\', \'preparacion\', \'aceptado\', \'confirmado\', \'procesando\'].includes(rawStatus);
                } else if (selectedStatus === \'encamino\' || selectedStatus === \'enviado\') {
                    matchesStatus = [\'encamino\', \'despachado\', \'asignado\', \'enruta\', \'enviado\'].includes(rawStatus);
                } else if (selectedStatus === \'cancelado\') {
                    matchesStatus = [\'cancelado\', \'rechazado\', \'fallido\'].includes(rawStatus);
                } else {
                    matchesStatus = rawStatus.includes(selectedStatus);
                }
            }

            // 3. Coincidencia de fecha YYYY-MM-DD
            const matchesDate = !selectedDate || (rawDate === selectedDate);

            if (matchesSearch && matchesStatus && matchesDate) {
                row.style.display = \'\';
                visibleCount++;
            } else {
                row.style.display = \'none\';
            }
        });

        if (noResultsRow) {
            noResultsRow.style.display = (visibleCount === 0) ? \'\' : \'none\';
        }

        if (countBadge) {
            if (searchTerm !== \'\' || (selectedStatus !== \'todos\' && selectedStatus !== \'\') || selectedDate) {
                countBadge.textContent = `${visibleCount} de ${totalOrdersCount} ${totalOrdersCount === 1 ? \'pedido\' : \'pedidos\'}`;
            } else {
                countBadge.textContent = `${totalOrdersCount} ${totalOrdersCount === 1 ? \'pedido\' : \'pedidos\'}`;
            }
        }
    }

    if (searchInput) {
        searchInput.addEventListener(\'input\', filterOrders);
        searchInput.addEventListener(\'keyup\', filterOrders);
    }
    
    if (filterSelect) {
        filterSelect.addEventListener(\'change\', filterOrders);
    }

    if (dateInput) {
        dateInput.addEventListener(\'change\', filterOrders);
        dateInput.addEventListener(\'input\', filterOrders);
    }

    if (btnLimpiar) {
        btnLimpiar.addEventListener(\'click\', function() {
            if (searchInput) searchInput.value = \'\';
            if (filterSelect) filterSelect.value = \'todos\';
            if (dateInput) dateInput.value = \'\';
            filterOrders();
        });
    }';

$content = str_replace(["\r\n", "\r"], "\n", $content);
$oldCode = str_replace(["\r\n", "\r"], "\n", $oldCode);
$newCode = str_replace(["\r\n", "\r"], "\n", $newCode);

if (strpos($content, $oldCode) !== false) {
    $content = str_replace($oldCode, $newCode, $content);
    file_put_contents($file, $content);
    echo "cuenta.js updated successfully.\n";
} else {
    echo "Warning: oldCode not matched in cuenta.js.\n";
}
