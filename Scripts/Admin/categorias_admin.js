(function() {
const itemsPerPageCat = 5;

let sizeStateCat = {
    tablaMom: itemsPerPageCat,
    tablaGrupos: itemsPerPageCat,
    tablaCategorias: itemsPerPageCat
};

let pagesStateCat = {
    tablaMom: 1,
    tablaGrupos: 1,
    tablaCategorias: 1
};

let filterStateCat = {
    tablaGrupos: 'all', // 'all', 'activas', 'inactivas'
    tablaCategorias: 'all' // 'all', 'activas', 'inactivas'
};

function paginateTableCat(tableId, listContainerId, pageKey, filterKey) {
    const table = document.getElementById(tableId);
    if (!table) return;
    const tbody = table.querySelector('tbody');
    if (!tbody) return;

    const allRows = Array.from(tbody.querySelectorAll('tr'));
    const searchInputId = tableId === 'tablaMom' ? 'filtroMom' : (tableId === 'tablaGrupos' ? 'filtroGrupos' : 'filtroCategorias');
    const query = (document.getElementById(searchInputId)?.value || '').toLowerCase().trim();
    const activeFilter = filterKey ? filterStateCat[filterKey] : 'all';

    let filtered = allRows.filter(row => {
        if (query && !row.textContent.toLowerCase().includes(query)) {
            return false;
        }
        if (activeFilter === 'activas') {
            return row.dataset.activo === '1';
        } else if (activeFilter === 'inactivas') {
            return row.dataset.activo === '0';
        }
        return true;
    });

    const totalItems = filtered.length;
    const perPage = sizeStateCat[pageKey] || itemsPerPageCat;
    const totalPages = Math.ceil(totalItems / perPage) || 1;

    if (pagesStateCat[pageKey] > totalPages) pagesStateCat[pageKey] = totalPages;
    if (pagesStateCat[pageKey] < 1) pagesStateCat[pageKey] = 1;

    const curPage = pagesStateCat[pageKey];
    const start = (curPage - 1) * perPage;
    const end = start + perPage;

    allRows.forEach(r => r.style.display = 'none');
    filtered.slice(start, end).forEach(r => r.style.display = '');

    const container = document.getElementById(listContainerId);
    if (!container || !window.TblPagination) return;

    window.TblPagination.render(container, {
        pages: totalPages,
        page: curPage,
        size: perPage,
        onPage: function (p) {
            pagesStateCat[pageKey] = p;
            paginateTableCat(tableId, listContainerId, pageKey, filterKey);
            table.scrollIntoView({ behavior: 'smooth', block: 'start' });
        },
        onSize: function (size) {
            sizeStateCat[pageKey] = size;
            pagesStateCat[pageKey] = 1;
            paginateTableCat(tableId, listContainerId, pageKey, filterKey);
        }
    });
}

window.confirmarCambioEstadoCategoria = function(tipo, id, estado, nombre) {
    const activar = Number(estado) === 1;
    const esGrupo = tipo === 'grupo';
    const entidad = esGrupo ? 'categoría' : 'subcategoría';
    const accion = activar ? 'Activar' : 'Inactivar';
    const modal = document.getElementById('modalConfirmarEstadoCategoria');
    const icono = document.getElementById('iconoEstadoCategoria');
    const titulo = document.getElementById('tituloEstadoCategoria');
    const texto = document.getElementById('textoEstadoCategoria');
    const boton = document.getElementById('btnConfirmarEstadoCategoria');
    if (!modal || !icono || !titulo || !texto || !boton) return;

    titulo.textContent = accion + ' ' + entidad;
    texto.textContent = '¿Confirmas ' + accion.toLowerCase() + ' "' + nombre + '"?';
    icono.style.background = activar ? '#dcfce7' : '#fee2e2';
    icono.style.color = activar ? '#15803d' : '#dc2626';
    icono.innerHTML = activar
        ? '<svg xmlns="http://www.w3.org/2000/svg" width="28" height="28" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"></circle><path d="m8 12 2.5 2.5L16 9"></path></svg>'
        : '<svg xmlns="http://www.w3.org/2000/svg" width="28" height="28" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"></circle><line x1="4.93" y1="4.93" x2="19.07" y2="19.07"></line></svg>';
    boton.textContent = accion;
    boton.style.background = activar ? '#15803d' : '#dc2626';
    const action = esGrupo ? 'toggle_grupo' : 'toggle_categoria';
    boton.href = 'index.php?action=' + action + '&id=' + encodeURIComponent(id) + '&estado=' + (activar ? 1 : 0) + '&redirect_to=categorias_admin';
    modal.style.display = 'flex';
    document.body.style.overflow = 'hidden';
};

window.switchTab = function(tab, subFilter) {
    document.querySelectorAll('.stat-card-square').forEach(function(card){ card.classList.remove('active-tab'); });
    var view = document.getElementById('tabViewSubcategorias');
    if (!view) return;
    view.style.display = 'block';

    var titleEl = document.getElementById('mainTitleAdmin');
    if (subFilter === 'activas') {
        document.getElementById('tabCardSubActivas')?.classList.add('active-tab');
        filterStateCat.tablaCategorias = 'activas';
    } else if (subFilter === 'inactivas') {
        document.getElementById('tabCardSubInactivas')?.classList.add('active-tab');
        filterStateCat.tablaCategorias = 'inactivas';
    } else {
        document.getElementById('tabCardSubcategorias')?.classList.add('active-tab');
        filterStateCat.tablaCategorias = 'all';
    }
    if (titleEl) titleEl.textContent = 'Gestión de Subcategorías';
    pagesStateCat.tablaCategorias = 1;
    paginateTableCat('tablaCategorias', 'paginationListCat', 'tablaCategorias', 'tablaCategorias');
};

window.filtrarTabla = function(tableId, inputId, colIdx) {
    if (tableId === 'tablaMom') {
        pagesStateCat.tablaMom = 1;
        paginateTableCat('tablaMom', 'paginationListMom', 'tablaMom', null);
    } else if (tableId === 'tablaGrupos') {
        pagesStateCat.tablaGrupos = 1;
        paginateTableCat('tablaGrupos', 'paginationListGrupos', 'tablaGrupos', 'tablaGrupos');
    } else if (tableId === 'tablaCategorias') {
        pagesStateCat.tablaCategorias = 1;
        paginateTableCat('tablaCategorias', 'paginationListCat', 'tablaCategorias', 'tablaCategorias');
    }
};

function cerrarMenusFiltroCat() {
    document.querySelectorAll('.cat-filter-menu.open').forEach(function(m) { m.classList.remove('open'); });
    document.querySelectorAll('.cat-filter-action[aria-expanded="true"]').forEach(function(b) { b.setAttribute('aria-expanded', 'false'); });
}

window.toggleFiltroCategorias = function(tableId) {
    const menuId = tableId === 'tablaGrupos' ? 'filtroMenuGrupos' : 'filtroMenuCategorias';
    const menu = document.getElementById(menuId);
    const btn = document.getElementById(tableId === 'tablaGrupos' ? 'btnFiltroGrupos' : 'btnFiltroCategorias');
    if (!menu) return;
    const abrir = !menu.classList.contains('open');
    cerrarMenusFiltroCat();
    if (abrir) {
        menu.classList.add('open');
        if (btn) btn.setAttribute('aria-expanded', 'true');
    }
};

window.setFiltroEstadoCategoria = function(tableId, valor) {
    const key = tableId === 'tablaGrupos' ? 'tablaGrupos' : 'tablaCategorias';
    filterStateCat[key] = valor;
    pagesStateCat[key] = 1;
    const menuId = tableId === 'tablaGrupos' ? 'filtroMenuGrupos' : 'filtroMenuCategorias';
    document.querySelectorAll('#' + menuId + ' .cat-filter-option').forEach(function(o) {
        o.classList.toggle('is-active', o.dataset.filtro === valor);
    });
    cerrarMenusFiltroCat();
    if (tableId === 'tablaGrupos') {
        paginateTableCat('tablaGrupos', 'paginationListGrupos', 'tablaGrupos', 'tablaGrupos');
    } else {
        paginateTableCat('tablaCategorias', 'paginationListCat', 'tablaCategorias', 'tablaCategorias');
    }
};

document.addEventListener('click', function(e) {
    if (!e.target.closest('.cat-filter-wrap')) cerrarMenusFiltroCat();
});

window.previewGrupoFileImage = function(input) {
    if (input.files && input.files[0]) {
        var reader = new FileReader();
        reader.onload = function(e) { document.getElementById('grpImgPreview').src = e.target.result; };
        reader.readAsDataURL(input.files[0]);
        document.getElementById('grpFileName').textContent = input.files[0].name;
    }
};

window.previewCatFileImage = function(input) {
    if (input.files && input.files[0]) {
        var reader = new FileReader();
        reader.onload = function(e) { document.getElementById('catImgPreview').src = e.target.result; };
        reader.readAsDataURL(input.files[0]);
        document.getElementById('catFileName').textContent = input.files[0].name;
    }
};

function catGrupoSelect() {
    return document.getElementById('catGrupoSelect');
}

function syncCategoriaMomento() {
    var select = catGrupoSelect();
    var hiddenMomento = document.getElementById('catMomentoId');
    if (!select || !hiddenMomento) return;
    var option = select.options[select.selectedIndex];
    hiddenMomento.value = option ? (option.getAttribute('data-momento') || '') : '';
}

function initCategoriaSelect() {
    var select = catGrupoSelect();
    if (!select || select.dataset.selectReady === '1') return;
    select.dataset.selectReady = '1';
    select.addEventListener('change', syncCategoriaMomento);
    syncCategoriaMomento();
}

window.initCategoriaSelect = initCategoriaSelect;

if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', initCategoriaSelect);
} else {
    initCategoriaSelect();
}

window.abrirModalCategoria = function() {
    document.getElementById('tituloModalCat').textContent = 'Nueva Subcategoría';
    document.getElementById('formCategoriaAdmin').action = 'index.php?action=crear_categoria';
    document.getElementById('catId').value = '';
    document.getElementById('catNombre').value = '';
    document.getElementById('catDescripcion').value = '';
    document.getElementById('catImagenUrl').value = '';
    document.getElementById('catFileInput').value = '';
    document.getElementById('catFileName').textContent = 'Seleccionar archivo PC...';
    document.getElementById('catImgPreview').src = 'Images/icono.png';
    var select = catGrupoSelect();
    if (select) select.value = '';
    syncCategoriaMomento();
    document.getElementById('btnGuardarCat').textContent = 'Guardar Subcategoría';
    document.getElementById('modalCategoriaAdmin').style.display = 'flex';
};

window.editarCategoria = function(cat) {
    document.getElementById('tituloModalCat').textContent = 'Editar Subcategoría';
    document.getElementById('formCategoriaAdmin').action = 'index.php?action=actualizar_categoria';
    document.getElementById('catId').value = cat.id_categoria || '';
    document.getElementById('catNombre').value = cat.nombre || '';
    document.getElementById('catDescripcion').value = cat.descripcion || '';
    document.getElementById('catImagenUrl').value = cat.imagen || '';
    document.getElementById('catFileInput').value = '';
    document.getElementById('catFileName').textContent = 'Seleccionar archivo PC...';
    document.getElementById('catImgPreview').src = cat.imagen || 'Images/icono.png';
    var select = catGrupoSelect();
    if (select) select.value = String(cat.id_grupo || '');
    syncCategoriaMomento();
    document.getElementById('btnGuardarCat').textContent = 'Actualizar Cambios';
    document.getElementById('modalCategoriaAdmin').style.display = 'flex';
};

window.exportarSubcategoriasPDF = function() {
    const data = window.__subcategoriasData || [];
    if (!data.length) {
        alert('No hay subcategorías para exportar.');
        return;
    }

    function escaparHtml(v) {
        return String(v == null ? '' : v)
            .replace(/&/g, '&amp;').replace(/</g, '&lt;').replace(/>/g, '&gt;')
            .replace(/"/g, '&quot;').replace(/'/g, '&#39;');
    }

    function cargarHtml2Pdf(cb) {
        if (typeof window.html2pdf !== 'undefined') { cb(); return; }
        const exist = document.querySelector('script[src*="html2pdf"]');
        if (exist) { exist.addEventListener('load', function() { cb(); }); return; }
        const s = document.createElement('script');
        s.src = 'https://cdnjs.cloudflare.com/ajax/libs/html2pdf.js/0.10.1/html2pdf.bundle.min.js';
        s.onload = function() { cb(); };
        s.onerror = function() { alert('No se pudo cargar la librería para exportar PDF.'); };
        document.head.appendChild(s);
    }

    const fecha = new Date().toLocaleDateString('es-PE', { day: '2-digit', month: '2-digit', year: 'numeric' });

    let filas = '';
    data.forEach(function(c, i) {
        filas += '<tr>'
            + '<td style="padding:5px 8px;border:1px solid #cbd5e1;">' + (i + 1) + '</td>'
            + '<td style="padding:5px 8px;border:1px solid #cbd5e1;">#' + c.id + '</td>'
            + '<td style="padding:5px 8px;border:1px solid #cbd5e1;"><strong>' + escaparHtml(c.nombre) + '</strong></td>'
            + '<td style="padding:5px 8px;border:1px solid #cbd5e1;">' + escaparHtml(c.categoria || '—') + '</td>'
            + '<td style="padding:5px 8px;border:1px solid #cbd5e1;">' + escaparHtml(c.descripcion || '—') + '</td>'
            + '<td style="padding:5px 8px;border:1px solid #cbd5e1;">' + (c.activo ? 'Activo' : 'Inactivo') + '</td>'
            + '</tr>';
    });

    const cont = document.createElement('div');
    cont.style.cssText = 'width:190mm;padding:4mm;background:#fff;font-family:Arial,Helvetica,sans-serif;color:#0f172a;';
    cont.innerHTML =
        '<div style="display:flex;justify-content:space-between;align-items:flex-end;border-bottom:2px solid #15803d;padding-bottom:8px;margin-bottom:12px;">'
        + '<div><div style="font-size:16px;font-weight:800;">La Caserita — ERP</div>'
        + '<div style="font-size:12px;color:#64748b;">Listado de Subcategorías</div></div>'
        + '<div style="font-size:11px;color:#64748b;text-align:right;">Generado: ' + fecha + '<br>Total: ' + data.length + '</div>'
        + '</div>'
        + '<table style="width:100%;border-collapse:collapse;font-size:11px;">'
        + '<thead><tr style="background:#f1f5f9;text-align:left;">'
        + '<th style="padding:6px 8px;border:1px solid #cbd5e1;">#</th>'
        + '<th style="padding:6px 8px;border:1px solid #cbd5e1;">ID</th>'
        + '<th style="padding:6px 8px;border:1px solid #cbd5e1;">Subcategoría</th>'
        + '<th style="padding:6px 8px;border:1px solid #cbd5e1;">Categoría</th>'
        + '<th style="padding:6px 8px;border:1px solid #cbd5e1;">Descripción</th>'
        + '<th style="padding:6px 8px;border:1px solid #cbd5e1;">Estado</th>'
        + '</tr></thead><tbody>' + filas + '</tbody></table>';

    cargarHtml2Pdf(function() {
        window.html2pdf().set({
            margin: [8, 8, 8, 8],
            filename: 'subcategorias_' + new Date().toISOString().slice(0, 10) + '.pdf',
            image: { type: 'jpeg', quality: 0.98 },
            html2canvas: { scale: 2, useCORS: true, logging: false },
            jsPDF: { unit: 'mm', format: 'a4', orientation: 'portrait' }
        }).from(cont).save();
    });
};

window.cerrarModalId = function(id) {
    var m = document.getElementById(id);
    if (m) m.style.display = 'none';
    if (!document.querySelector('.cat-modal-overlay[style*="display: flex"]')) {
        document.body.style.overflow = '';
    }
};

function initializeCategoriesView() {
    window.switchTab('subcategorias', 'all');
}
if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', initializeCategoriesView, { once: true });
} else {
    initializeCategoriesView();
}
})();
