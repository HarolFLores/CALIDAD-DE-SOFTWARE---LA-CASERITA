(function () {
    const campoBusqueda = document.getElementById('cashierProductSearch');
    const filas = Array.from(document.querySelectorAll('[data-catalog-row]'));
    const filtroSubcategoria = document.getElementById('catalogCategoryFilter');
    const filtroOrden = document.getElementById('catalogSortFilter');
    const contador = document.getElementById('catalogCount');
    const contadorPanel = document.getElementById('catalogPanelCount');
    const vacioTabla = document.getElementById('catalogEmptyTable');
    const contenedorPaginacion = document.getElementById('catalogPagination');
    const botonesResumen = Array.from(document.querySelectorAll('[data-catalog-filter], [data-catalog-focus]'));
    let filtroStock = 'all';
    let filasFiltradas = filas.slice();

    const paginador = window.TblPagination ? window.TblPagination.create(contenedorPaginacion, {
        rows: function () { return filasFiltradas; },
        size: 10,
        sizes: [10, 25, 50],
        label: 'Productos por página',
        hide: function (fila) { fila.hidden = true; },
        show: function (fila) { fila.hidden = false; }
    }) : null;

    function coincide(fila) {
        const consulta = campoBusqueda.value.trim().toLowerCase();
        if (consulta && !(fila.dataset.search || '').includes(consulta)) return false;

        if (filtroSubcategoria.value && fila.dataset.subcategory !== filtroSubcategoria.value) return false;

        const estado = fila.dataset.status;
        if (filtroStock === 'available' && estado !== 'ok') return false;
        if (filtroStock === 'low' && estado !== 'low' && estado !== 'out') return false;
        if (filtroStock === 'out' && estado !== 'out') return false;
        if (filtroStock === 'discount' && Number(fila.dataset.discount || 0) <= 0) return false;

        return true;
    }

    function compararNombre(a, b) {
        return (a.dataset.name || '').localeCompare(b.dataset.name || '', 'es', { sensitivity: 'base' });
    }

    function ordenar(lista) {
        const orden = filtroOrden ? filtroOrden.value : 'recomendados';
        return lista.slice().sort(function (a, b) {
            if (orden === 'nombre_asc') {
                return compararNombre(a, b);
            }
            if (orden === 'stock_asc') {
                return Number(a.dataset.stock || 0) - Number(b.dataset.stock || 0);
            }
            if (orden === 'stock_desc') {
                return Number(b.dataset.stock || 0) - Number(a.dataset.stock || 0);
            }

            const aBajo = a.dataset.status === 'ok' ? 1 : 0;
            const bBajo = b.dataset.status === 'ok' ? 1 : 0;
            if (aBajo !== bBajo) return aBajo - bBajo;
            return compararNombre(a, b);
        });
    }

    function actualizar() {
        filasFiltradas = ordenar(filas.filter(coincide));
        filas.forEach(function (fila) { fila.hidden = true; });

        if (paginador) {
            paginador.update(true);
        } else {
            filasFiltradas.forEach(function (fila) { fila.hidden = false; });
        }

        const total = filasFiltradas.length;
        contador.textContent = `Mostrando ${total} de ${filas.length} productos`;
        if (contadorPanel) {
            contadorPanel.textContent = `${total} ${total === 1 ? 'producto' : 'productos'}`;
        }
        vacioTabla.hidden = total !== 0;
    }

    function marcarResumen(activo) {
        botonesResumen.forEach(function (boton) {
            const esActivo = boton === activo;
            boton.classList.toggle('is-active', esActivo);
            boton.setAttribute('aria-pressed', esActivo ? 'true' : 'false');
        });
    }

    botonesResumen.forEach(function (boton) {
        boton.addEventListener('click', function () {
            if (boton.dataset.catalogFocus === 'category') {
                filtroSubcategoria.focus();
                filtroSubcategoria.scrollIntoView({ behavior: 'smooth', block: 'center' });
                return;
            }

            filtroStock = boton.dataset.catalogFilter || 'all';
            marcarResumen(boton);
            actualizar();
        });
    });

    campoBusqueda.addEventListener('input', actualizar);
    filtroSubcategoria.addEventListener('change', actualizar);
    if (filtroOrden) filtroOrden.addEventListener('change', actualizar);

    actualizar();
})();
