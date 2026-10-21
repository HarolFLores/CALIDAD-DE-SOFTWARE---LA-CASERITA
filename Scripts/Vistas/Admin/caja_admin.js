(function () {
    'use strict';

    function iniciarTabla(tableId, paginationId) {
        var table = document.getElementById(tableId);
        var container = document.getElementById(paginationId);
        if (!table || !container || !window.TblPagination) return;
        if (container.dataset.cajaPagina === '1') return;
        container.dataset.cajaPagina = '1';

        var rows = Array.prototype.slice.call(table.querySelectorAll('tbody tr')).filter(function (row) {
            return !row.querySelector('.caja-empty');
        });
        if (!rows.length) return;

        window.TblPagination.create(container, {
            rows: function () { return rows; },
            size: 5,
            sizes: [5, 10, 25],
            label: 'Filas por página'
        });
    }

    function iniciar() {
        iniciarTabla('tablaMovimientosCaja', 'paginacionMovimientosList');
        iniciarTabla('tablaHistorialCaja', 'paginacionHistorialList');
    }

    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', iniciar, { once: true });
    } else {
        iniciar();
    }
})();
