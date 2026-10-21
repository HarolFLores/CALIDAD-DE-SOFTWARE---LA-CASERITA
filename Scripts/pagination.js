/* ==========================================================================
   Paginación unificada — La Caserita
   Genera el pie de tabla (selector de filas + navegación) y, además, una
   copia sincronizada en la parte superior de la tabla.
   ========================================================================== */
(function () {
    'use strict';

    if (window.TblPagination) return;

    var DEFAULT_SIZES = [5, 10, 25, 50];
    var DEFAULT_SIZE = 5;

    function buildPages(totalPages, current, maxButtons) {
        var total = Math.max(1, parseInt(totalPages, 10) || 1);
        var page = Math.min(Math.max(1, parseInt(current, 10) || 1), total);
        var max = Math.max(5, parseInt(maxButtons, 10) || 5);
        var pages = [];
        var i;

        if (total <= max) {
            for (i = 1; i <= total; i++) pages.push(i);
            return pages;
        }

        pages.push(1);
        var start = Math.max(2, page - 1);
        var end = Math.min(total - 1, page + 1);

        if (start > 2) pages.push('...');
        for (i = start; i <= end; i++) pages.push(i);
        if (end < total - 1) pages.push('...');
        pages.push(total);

        return pages;
    }

    function fill(el, options) {
        var totalPages = Math.max(1, parseInt(options.pages, 10) || 1);
        var current = Math.min(Math.max(1, parseInt(options.page, 10) || 1), totalPages);
        var sizes = options.sizes || DEFAULT_SIZES;
        var size = parseInt(options.size, 10) || DEFAULT_SIZE;
        var pages = buildPages(totalPages, current, options.maxButtons || 5);

        el.classList.add('pg-footer');
        el.innerHTML = '';

        var sizeWrap = document.createElement('label');
        sizeWrap.className = 'pg-size';

        var sizeText = document.createElement('span');
        sizeText.textContent = options.label || 'Mostrar por página';

        var select = document.createElement('select');
        select.className = 'pg-size-select';
        sizes.forEach(function (value) {
            var option = document.createElement('option');
            option.value = value;
            option.textContent = value;
            if (parseInt(value, 10) === size) option.selected = true;
            select.appendChild(option);
        });
        select.addEventListener('change', function () {
            var next = parseInt(this.value, 10);
            if (next && typeof options.onSize === 'function') options.onSize(next);
        });

        sizeWrap.appendChild(sizeText);
        sizeWrap.appendChild(select);

        var nav = document.createElement('nav');
        nav.className = 'pg-nav';
        nav.setAttribute('aria-label', 'Paginación');

        var prev = document.createElement('button');
        prev.type = 'button';
        prev.className = 'pg-btn pg-nav-btn';
        prev.setAttribute('aria-label', 'Anterior');
        prev.innerHTML = '&#8249;';
        prev.disabled = current <= 1;
        prev.addEventListener('click', function () {
            if (current > 1 && typeof options.onPage === 'function') options.onPage(current - 1);
        });
        nav.appendChild(prev);

        pages.forEach(function (value) {
            if (value === '...') {
                var dots = document.createElement('span');
                dots.className = 'pg-ellipsis';
                dots.textContent = '…';
                nav.appendChild(dots);
                return;
            }

            var button = document.createElement('button');
            button.type = 'button';
            button.className = 'pg-btn' + (value === current ? ' is-active' : '');
            button.textContent = value;
            button.setAttribute('aria-label', 'Página ' + value);
            if (value === current) {
                button.setAttribute('aria-current', 'page');
            } else {
                button.addEventListener('click', function () {
                    if (typeof options.onPage === 'function') options.onPage(value);
                });
            }
            nav.appendChild(button);
        });

        var next = document.createElement('button');
        next.type = 'button';
        next.className = 'pg-btn pg-nav-btn';
        next.setAttribute('aria-label', 'Siguiente');
        next.innerHTML = '&#8250;';
        next.disabled = current >= totalPages;
        next.addEventListener('click', function () {
            if (current < totalPages && typeof options.onPage === 'function') options.onPage(current + 1);
        });
        nav.appendChild(next);

        el.appendChild(sizeWrap);
        el.appendChild(nav);
    }

    function render(container, options) {
        if (!container) return;

        options = options || {};

        fill(container, options);
    }

    function create(container, options) {
        options = options || {};
        var size = options.size || DEFAULT_SIZE;
        var sizes = options.sizes || DEFAULT_SIZES;
        var page = 1;
        var hide = options.hide || function (row) { row.hidden = true; };
        var show = options.show || function (row) { row.hidden = false; };

        function update(resetPage) {
            if (resetPage) page = 1;

            var rows = options.rows() || [];
            var totalPages = Math.ceil(rows.length / size) || 1;
            if (page > totalPages) page = totalPages;
            if (page < 1) page = 1;

            var start = (page - 1) * size;
            var end = start + size;

            rows.forEach(function (row, index) {
                if (index >= start && index < end) show(row);
                else hide(row);
            });

            if (container) {
                render(container, {
                    pages: totalPages,
                    page: page,
                    size: size,
                    sizes: sizes,
                    label: options.label,
                    top: options.top,
                    onPage: function (p) {
                        page = p;
                        update(false);
                        if (options.onChange) options.onChange();
                    },
                    onSize: function (s) {
                        size = s;
                        page = 1;
                        update(false);
                        if (options.onChange) options.onChange();
                    }
                });
            }
        }

        update(true);

        return {
            update: update,
            getPage: function () { return page; }
        };
    }

    window.TblPagination = {
        render: render,
        buildPages: buildPages,
        create: create
    };
})();
