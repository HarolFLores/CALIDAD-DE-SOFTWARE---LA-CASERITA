/* ============================================================
   BANDEJA DE PEDIDOS PENDIENTES (HU33)
   ============================================================ */
(function () {
    'use strict';

    var tabla = document.getElementById('bpTabla');
    if (!tabla) { window.bpSetTab = function () {}; return; }

    var rows        = Array.prototype.slice.call(tabla.querySelectorAll('tbody tr.bp-row'));
    var tabs        = Array.prototype.slice.call(document.querySelectorAll('#bpTabs .bp-tab'));
    var stats       = Array.prototype.slice.call(document.querySelectorAll('#bpStats .bp-stat'));
    var search      = document.getElementById('bpSearch');
    var filterEmpty = document.getElementById('bpFilterEmpty');
    var checkAll    = document.getElementById('bpCheckAll');
    var bulkBar     = document.getElementById('bpBulkBar');
    var bulkCount   = document.getElementById('bpBulkCount');

    var overlay     = document.getElementById('bpModalOverlay');
    var modalTitulo = document.getElementById('bpModalTitulo');
    var modalLabel  = document.getElementById('bpModalLabel');
    var modalSelect = document.getElementById('bpModalSelect');
    var modalResumen= document.getElementById('bpModalResumen');
    var modalHint   = document.getElementById('bpModalHint');
    var modalCerrar = document.getElementById('bpModalCerrar');
    var modalOk     = document.getElementById('bpModalConfirmar');

    var toast       = document.getElementById('bpToast');
    var toastIcon   = document.getElementById('bpToastIcon');
    var toastTitle  = document.getElementById('bpToastTitle');
    var toastMsg    = document.getElementById('bpToastMsg');
    var toastTimer  = null;

    var currentTab = 'por_validar';
    var modalTipo  = 'empaque';
    var modalIds   = [];

    var ICON_OK = '<svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.6" stroke-linecap="round" stroke-linejoin="round"><path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"></path><polyline points="22 4 12 14.01 9 11.01"></polyline></svg>';
    var ICON_ERR = '<svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.6" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"></circle><line x1="15" y1="9" x2="9" y2="15"></line><line x1="9" y1="9" x2="15" y2="15"></line></svg>';

    function bpToast(titulo, msg, esExito) {
        if (!toast) return;
        clearTimeout(toastTimer);
        toast.classList.remove('error');
        if (!esExito) toast.classList.add('error');
        if (toastIcon) toastIcon.innerHTML = esExito ? ICON_OK : ICON_ERR;
        if (toastTitle) toastTitle.textContent = titulo;
        if (toastMsg) toastMsg.textContent = msg || '';
        toast.classList.add('show');
        toastTimer = setTimeout(function () { toast.classList.remove('show'); }, 3600);
    }

    function bpApply() {
        var q = (search && search.value ? search.value : '').toLowerCase().trim();
        var visibles = 0;
        rows.forEach(function (row) {
            var okTab = currentTab === 'alerta'
                ? row.getAttribute('data-alerta') === '1'
                : row.getAttribute('data-clasif') === currentTab;
            var okQ = !q || (row.getAttribute('data-search') || '').indexOf(q) !== -1;
            var show = okTab && okQ;
            row.style.display = show ? '' : 'none';
            if (show) visibles++;
        });
        if (filterEmpty) filterEmpty.style.display = (rows.length > 0 && visibles === 0) ? 'block' : 'none';
    }

    function bpSetActiveTab(tab) {
        currentTab = tab;
        tabs.forEach(function (t) {
            t.classList.toggle('active', t.getAttribute('data-tab') === tab);
        });
        stats.forEach(function (s) {
            s.classList.toggle('active', s.getAttribute('data-tab') === tab);
        });
        bpApply();
    }
    window.bpSetTab = bpSetActiveTab;

    function bpSyncBulk() {
        var checks = Array.prototype.slice.call(tabla.querySelectorAll('.bp-check:checked'));
        if (bulkCount) bulkCount.textContent = checks.length;
        if (bulkBar) bulkBar.classList.toggle('show', checks.length > 0);
        if (checkAll) {
            var all = Array.prototype.slice.call(tabla.querySelectorAll('.bp-check'));
            checkAll.checked = all.length > 0 && checks.length === all.length;
        }
    }

    function bpSelectedIds() {
        return Array.prototype.slice.call(tabla.querySelectorAll('.bp-check:checked'))
            .map(function (c) { return parseInt(c.value, 10); })
            .filter(function (n) { return !isNaN(n); });
    }

    function bpLimpiarSeleccion() {
        Array.prototype.slice.call(tabla.querySelectorAll('.bp-check:checked')).forEach(function (c) { c.checked = false; });
        if (checkAll) checkAll.checked = false;
        bpSyncBulk();
    }

    function bpAbrirModal(tipo, ids) {
        if (!overlay) return;
        modalTipo = tipo;
        modalIds = ids || [];
        var lista = tipo === 'empaque' ? (window.BP_PERSONAL_EMPAQUE || []) : (window.BP_REPARTIDORES || []);

        if (modalTitulo) modalTitulo.textContent = tipo === 'empaque' ? 'Asignar a Empaque' : 'Asignar a Reparto';
        if (modalLabel) modalLabel.textContent = tipo === 'empaque' ? 'Personal de empaque' : 'Motorizado';
        if (modalResumen) modalResumen.textContent = modalIds.length + ' pedido(s) seleccionado(s).';
        if (modalHint) {
            modalHint.textContent = tipo === 'reparto'
                ? 'Se creará el envío y quedará marcado como asignado al motorizado.'
                : 'El pedido quedará a cargo del responsable de empaque seleccionado.';
        }

        if (modalSelect) {
            modalSelect.innerHTML = '';
            if (!lista.length) {
                var opt0 = document.createElement('option');
                opt0.value = '';
                opt0.textContent = tipo === 'empaque' ? 'Sin personal de empaque disponible' : 'Sin motorizados disponibles';
                modalSelect.appendChild(opt0);
            } else {
                lista.forEach(function (u) {
                    var opt = document.createElement('option');
                    opt.value = u.id;
                    opt.textContent = u.nombre + (u.correo ? ' — ' + u.correo : (u.telefono ? ' — ' + u.telefono : ''));
                    modalSelect.appendChild(opt);
                });
            }
        }
        overlay.classList.add('active');
    }

    function bpCerrarModal() {
        if (overlay) overlay.classList.remove('active');
    }

    function bpConfirmar() {
        if (!modalIds.length) { bpToast('Atención', 'No hay pedidos seleccionados.', false); return; }
        var idUsuario = modalSelect ? modalSelect.value : '';
        if (!idUsuario) { bpToast('Atención', 'Seleccione un responsable.', false); return; }

        var nombreSel = modalSelect && modalSelect.selectedIndex >= 0
            ? modalSelect.options[modalSelect.selectedIndex].textContent.split(' — ')[0]
            : '';

        var fd = new FormData();
        modalIds.forEach(function (id) { fd.append('ids[]', id); });
        fd.append('tipo', modalTipo);
        fd.append('id_usuario', idUsuario);
        fd.append('is_ajax', '1');

        if (modalOk) { modalOk.disabled = true; }

        fetch('index.php?action=bandeja_asignar', {
            method: 'POST',
            headers: { 'X-Requested-With': 'XMLHttpRequest' },
            body: fd
        })
        .then(function (r) { return r.json(); })
        .then(function (res) {
            if (res && res.success) {
                modalIds.forEach(function (id) {
                    var row = tabla.querySelector('.bp-row[data-id="' + id + '"]');
                    if (!row) return;
                    var cell = row.querySelector(modalTipo === 'empaque' ? '.bp-cell-empaque' : '.bp-cell-reparto');
                    if (cell) cell.textContent = nombreSel || 'Asignado';
                });
                bpToast('Éxito', (res && res.message) || 'Asignación realizada.', true);
                bpCerrarModal();
                bpLimpiarSeleccion();
            } else {
                bpToast('Error', (res && res.message) || 'No se pudo completar la asignación.', false);
            }
        })
        .catch(function () {
            bpToast('Error', 'Error de conexión al asignar.', false);
        })
        .finally(function () {
            if (modalOk) { modalOk.disabled = false; }
        });
    }

    // ---- Eventos ----
    if (search) {
        ['input', 'keyup', 'search'].forEach(function (evt) {
            search.addEventListener(evt, bpApply);
        });
    }

    if (checkAll) {
        checkAll.addEventListener('change', function () {
            var visible = rows.filter(function (r) { return r.style.display !== 'none'; });
            visible.forEach(function (r) {
                var c = r.querySelector('.bp-check');
                if (c) c.checked = checkAll.checked;
            });
            bpSyncBulk();
        });
    }

    tabla.addEventListener('change', function (e) {
        if (e.target && e.target.classList && e.target.classList.contains('bp-check')) {
            bpSyncBulk();
        }
    });

    tabla.addEventListener('click', function (e) {
        var btn = e.target && e.target.closest ? e.target.closest('.bp-acc-empaque, .bp-acc-reparto') : null;
        if (!btn) return;
        var id = parseInt(btn.getAttribute('data-id'), 10);
        if (isNaN(id)) return;
        bpAbrirModal(btn.classList.contains('bp-acc-empaque') ? 'empaque' : 'reparto', [id]);
    });

    var bulkEmpaque = document.getElementById('bpBulkEmpaque');
    var bulkReparto = document.getElementById('bpBulkReparto');
    var bulkLimpiar = document.getElementById('bpBulkLimpiar');

    if (bulkEmpaque) bulkEmpaque.addEventListener('click', function () {
        var ids = bpSelectedIds();
        if (!ids.length) { bpToast('Atención', 'Seleccione al menos un pedido.', false); return; }
        bpAbrirModal('empaque', ids);
    });
    if (bulkReparto) bulkReparto.addEventListener('click', function () {
        var ids = bpSelectedIds();
        if (!ids.length) { bpToast('Atención', 'Seleccione al menos un pedido.', false); return; }
        bpAbrirModal('reparto', ids);
    });
    if (bulkLimpiar) bulkLimpiar.addEventListener('click', bpLimpiarSeleccion);

    if (modalCerrar) modalCerrar.addEventListener('click', bpCerrarModal);
    if (modalOk) modalOk.addEventListener('click', bpConfirmar);
    if (overlay) overlay.addEventListener('click', function (e) {
        if (e.target === overlay) bpCerrarModal();
    });

    // ---- Init ----
    bpSetActiveTab(currentTab);
    bpSyncBulk();

    var flash = window.BP_FLASH || {};
    if (flash.success) bpToast('Éxito', flash.success, true);
    else if (flash.error) bpToast('Atención', flash.error, false);
})();
