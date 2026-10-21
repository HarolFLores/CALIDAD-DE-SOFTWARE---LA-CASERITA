(function () {
    'use strict';

    var table = document.getElementById('clOrdersTable');
    if (!table) return;

    var rows = Array.prototype.slice.call(table.querySelectorAll('.cl-order-row'));
    var search = document.getElementById('clSearch');
    var typeFilter = document.getElementById('clTypeFilter');
    var clearFilters = document.getElementById('clClearFilters');
    var summary = document.getElementById('clSummary');
    var empty = document.getElementById('clEmpty');
    var stateModal = document.getElementById('clStateModal');
    var detailModal = document.getElementById('clDetailModal');
    var stateTitle = document.getElementById('clStateTitle');
    var stateSelect = document.getElementById('clStateSelect');
    var saveState = document.getElementById('clSaveState');
    var detailTitle = document.getElementById('clDetailTitle');
    var detailBody = document.getElementById('clDetailBody');
    var toast = document.getElementById('clToast');
    var pageContainer = document.getElementById('clPagination');
    var activeId = 0;
    var activeRow = null;
    var toastTimer = null;
    var quickFilter = 'total';
    var paginator = null;
    var matchedRows = [];

    var states = {
        pendiente: { value: 'Pendiente', label: 'Pendiente' },
        en_preparacion: { value: 'En Preparacion', label: 'En Preparación' },
        en_camino: { value: 'En Camino', label: 'En Ruta' },
        listo_recojo: { value: 'Listo para Recojo', label: 'Listo para Recojo' },
        entregado: { value: 'Entregado', label: 'Entregado' },
        cancelado: { value: 'Cancelado', label: 'Cancelado' }
    };

    function limpiarObservacionCliente(texto) {
        var limpio = String(texto == null ? '' : texto);
        limpio = limpio.replace(/\[(?:Tarjeta|Pago|Cancelaci[oó]n|Rechazo comprobante)[^\]]*\]/gi, ' ');
        var partes = limpio.split(/\||\r?\n/);
        var vistos = {};
        var resultado = [];
        partes.forEach(function (parte) {
            var valor = parte.trim();
            if (!valor) return;
            if (/^(Horario|Programado)\s*:/i.test(valor)) return;
            var clave = valor.toLowerCase();
            if (vistos[clave]) return;
            vistos[clave] = true;
            resultado.push(valor);
        });
        return resultado.join(' ').trim();
    }

    function esc(value) {
        return String(value == null ? '' : value)
            .replace(/&/g, '&amp;')
            .replace(/</g, '&lt;')
            .replace(/>/g, '&gt;')
            .replace(/"/g, '&quot;')
            .replace(/'/g, '&#039;');
    }

    function money(value) {
        return 'S/ ' + (parseFloat(value || 0) || 0).toFixed(2);
    }

    function showToast(message, error) {
        if (!toast) return;
        clearTimeout(toastTimer);
        toast.textContent = message;
        toast.classList.toggle('is-error', Boolean(error));
        toast.classList.add('is-visible');
        toastTimer = setTimeout(function () { toast.classList.remove('is-visible'); }, 3500);
    }

    function closeMenus(except) {
        document.querySelectorAll('.cl-kebab.is-open').forEach(function (menu) {
            if (menu !== except) {
                menu.classList.remove('is-open');
                var button = menu.querySelector('.cl-kebab-button');
                if (button) button.setAttribute('aria-expanded', 'false');
            }
        });
    }

    function filterRows() {
        var term = (search && search.value ? search.value : '').toLowerCase().trim();
        var type = typeFilter ? typeFilter.value : '';
        matchedRows = [];
        rows.forEach(function (row) {
            var matchesText = !term || (row.getAttribute('data-search') || '').indexOf(term) !== -1;
            var deliveryType = row.getAttribute('data-entrega') || '';
            var state = row.getAttribute('data-estado') || 'pendiente';
            var assigned = row.getAttribute('data-repartidor') === '1';
            var charged = row.getAttribute('data-cobrado') === '1';
            var cash = row.getAttribute('data-efectivo') === '1';
            var finalState = state === 'entregado' || state === 'cancelado';
            var matchesType = !type || deliveryType === type;
            var matchesQuick = quickFilter === 'total'
                || (quickFilter === 'delivery' && deliveryType === 'delivery')
                || (quickFilter === 'sin_repartidor' && deliveryType === 'delivery' && !assigned && !finalState)
                || (quickFilter === 'pendiente' && state === 'pendiente')
                || (quickFilter === 'en_preparacion' && state === 'en_preparacion')
                || (quickFilter === 'en_ruta' && (state === 'en_camino' || (assigned && !finalState)))
                || (quickFilter === 'recojo' && deliveryType === 'recojo')
                || (quickFilter === 'entregado' && state === 'entregado')
                || (quickFilter === 'por_cobrar' && deliveryType === 'delivery' && cash && !charged && state !== 'cancelado');
            var show = matchesText && matchesType && matchesQuick;
            if (show) matchedRows.push(row);
            else row.hidden = true;
        });

        if (paginator) paginator.update(true);

        if (empty) empty.hidden = matchedRows.length !== 0;
    }

    function updateSummary() {
        var counts = { total: rows.length, delivery: 0, sin_repartidor: 0, pendiente: 0, en_preparacion: 0, en_ruta: 0, recojo: 0, entregado: 0, por_cobrar: 0 };
        rows.forEach(function (row) {
            var code = row.getAttribute('data-estado') || 'pendiente';
            var deliveryType = row.getAttribute('data-entrega') || '';
            var assigned = row.getAttribute('data-repartidor') === '1';
            var charged = row.getAttribute('data-cobrado') === '1';
            var cash = row.getAttribute('data-efectivo') === '1';
            var finalState = code === 'entregado' || code === 'cancelado';
            if (deliveryType === 'delivery') counts.delivery++; else counts.recojo++;
            if (deliveryType === 'delivery' && !assigned && !finalState) counts.sin_repartidor++;
            if (code === 'pendiente') counts.pendiente++;
            if (code === 'en_preparacion') counts.en_preparacion++;
            if (code === 'en_camino' || (assigned && !finalState)) counts.en_ruta++;
            if (code === 'entregado') counts.entregado++;
            if (deliveryType === 'delivery' && cash && !charged && code !== 'cancelado') counts.por_cobrar += parseFloat(row.getAttribute('data-total') || 0) || 0;
        });
        document.querySelectorAll('[data-cl-count]').forEach(function (node) {
            var key = node.getAttribute('data-cl-count');
            node.textContent = key === 'por_cobrar' ? money(counts[key]) : String(counts[key] || 0);
        });
    }

    function setQuickFilter(filter) {
        quickFilter = filter || 'total';
        document.querySelectorAll('[data-quick-filter]').forEach(function (card) {
            card.classList.toggle('is-active', card.getAttribute('data-quick-filter') === quickFilter);
        });
        filterRows();
    }

    function resetFilters() {
        if (search) search.value = '';
        if (typeFilter) typeFilter.value = '';
        setQuickFilter('total');
    }

    function openModal(modal) {
        if (!modal) return;
        modal.classList.add('is-open');
        modal.setAttribute('aria-hidden', 'false');
        document.body.style.overflow = 'hidden';
    }

    function closeModal(modal) {
        if (!modal) return;
        modal.classList.remove('is-open');
        modal.setAttribute('aria-hidden', 'true');
        if (!document.querySelector('.cl-modal.is-open')) document.body.style.overflow = '';
    }

    function openState(row) {
        activeRow = row;
        activeId = parseInt(row.getAttribute('data-id'), 10) || 0;
        var current = row.getAttribute('data-estado') || 'pendiente';
        var delivery = row.getAttribute('data-entrega') === 'delivery';
        var assigned = row.getAttribute('data-repartidor') === '1';
        var order = delivery
            ? ['pendiente', 'en_preparacion', 'en_camino', 'cancelado']
            : ['pendiente', 'en_preparacion', 'listo_recojo', 'entregado', 'cancelado'];

        stateSelect.innerHTML = order.map(function (code) {
            var item = states[code];
            var disabled = delivery && code === 'en_camino' && !assigned;
            return '<option value="' + esc(item.value) + '" data-code="' + code + '"' + (code === current ? ' selected' : '') + (disabled ? ' disabled' : '') + '>' + esc(item.label) + (disabled ? ' (asigna repartidor)' : '') + '</option>';
        }).join('');
        stateTitle.textContent = 'Cambiar estado · Pedido #' + String(activeId).padStart(5, '0');
        openModal(stateModal);
        stateSelect.focus();
    }

    function saveOrderState() {
        if (!activeId || !activeRow || !stateSelect.value) return;
        var selected = stateSelect.options[stateSelect.selectedIndex];
        var code = selected.getAttribute('data-code');
        var body = new FormData();
        body.append('id_pedido', String(activeId));
        body.append('estado', stateSelect.value);
        body.append('csrf_token', (window.CAJERO_LOGISTICA && window.CAJERO_LOGISTICA.csrf) || '');
        saveState.disabled = true;

        fetch('index.php?action=logistica_estado_cajero', {
            method: 'POST',
            headers: { 'X-Requested-With': 'XMLHttpRequest' },
            body: body
        })
        .then(function (response) { return response.json(); })
        .then(function (result) {
            if (!result || !result.success) throw new Error((result && result.message) || 'No se pudo actualizar el estado.');
            activeRow.setAttribute('data-estado', code);
            var badge = activeRow.querySelector('.cl-status');
            if (badge) {
                badge.className = 'cl-status cl-status--' + code;
                badge.textContent = states[code].label;
            }
            if (code === 'entregado' || code === 'cancelado') {
                var time = activeRow.querySelector('.cl-time');
                if (time) {
                    time.textContent = '—';
                    time.classList.remove('is-alert');
                }
                activeRow.classList.remove('is-alert');
            }
            closeModal(stateModal);
            updateSummary();
            filterRows();
            showToast('Estado actualizado correctamente.', false);
        })
        .catch(function (error) { showToast(error.message || 'Error de conexión.', true); })
        .finally(function () { saveState.disabled = false; });
    }

    var ultimoTicketData = null;

    function setElText(id, val) {
        var el = document.getElementById(id);
        if (el) el.textContent = val;
    }

    function popularTicket(order, items) {
        var numPadded = String(order.id_pedido || activeId || 0).padStart(5, '0');
        var codigoDoc = order.codigo_pedido ? esc(order.codigo_pedido) : ('B001-' + numPadded);
        var customer = ((order.usuario_nombre || '') + ' ' + (order.usuario_apellido || '')).trim() || order.cliente_nombre || 'PÚBLICO GENERAL';
        var isDelivery = String(order.tipo_entrega || '').toLowerCase() === 'delivery';
        var total = parseFloat(order.total || 0) || 0;
        var subtotal = parseFloat((total / 1.18).toFixed(2));
        var igv = parseFloat((total - subtotal).toFixed(2));
        var montoRec = parseFloat(order.monto_recibido || 0);
        var vueltoPed = parseFloat(order.vuelto || 0);

        setElText('lblClTicketCodigo', codigoDoc);
        setElText('lblClTicketNumInt', 'N° Pedido: #' + numPadded);
        setElText('lblClTicketFecha', order.fecha || '--');
        setElText('lblClTicketCliente', customer.toUpperCase());

        var rowCorreo = document.getElementById('rowClTicketCorreo');
        if (rowCorreo) {
            if (order.usuario_correo) {
                rowCorreo.style.display = 'block';
                setElText('lblClTicketCorreo', order.usuario_correo);
            } else {
                rowCorreo.style.display = 'none';
            }
        }

        setElText('lblClTicketEntrega', isDelivery ? 'DELIVERY A DOMICILIO' : 'RECOJO EN TIENDA');

        var rowDirec = document.getElementById('rowClTicketDireccion');
        if (rowDirec) {
            if (isDelivery && order.direccion_envio) {
                rowDirec.style.display = 'block';
                setElText('lblClTicketDireccion', order.direccion_envio + (order.referencia ? (' (Ref: ' + order.referencia + ')') : ''));
            } else {
                rowDirec.style.display = 'none';
            }
        }

        setElText('lblClTicketMetodo', String(order.metodo_pago || order.pasarela_payment_type || 'EFECTIVO').toUpperCase());
        setElText('lblClTicketEstado', String(order.estado || 'PENDIENTE').toUpperCase());

        var tbody = document.getElementById('clTicketTbodyItems');
        if (tbody) {
            var rowsHtml = '';
            if (!items || items.length === 0) {
                rowsHtml = '<tr><td colspan="4" style="text-align:center; padding:8px 0; color:#64748b;">(Sin productos registrados)</td></tr>';
            } else {
                items.forEach(function (item) {
                    rowsHtml += '<tr>'
                        + '<td style="padding:3px 0; vertical-align:top;">' + (item.cantidad || 1) + '</td>'
                        + '<td style="padding:3px 4px; vertical-align:top;">' + esc(item.titulo || item.titulo_producto || 'Producto') + '</td>'
                        + '<td style="padding:3px 0; vertical-align:top; text-align:right;">' + parseFloat(item.precio_unitario || 0).toFixed(2) + '</td>'
                        + '<td style="padding:3px 0; vertical-align:top; text-align:right; font-weight:bold;">' + parseFloat(item.subtotal || 0).toFixed(2) + '</td>'
                        + '</tr>';
                });
            }
            tbody.innerHTML = rowsHtml;
        }

        setElText('lblClTicketSubtotal', money(subtotal));
        setElText('lblClTicketIgv', money(igv));
        setElText('lblClTicketTotal', money(total));

        var rowEfectivo = document.getElementById('rowClTicketEfectivo');
        if (rowEfectivo) {
            if (montoRec > 0) {
                rowEfectivo.style.display = 'block';
                setElText('lblClTicketRecibido', money(montoRec));
                setElText('lblClTicketVuelto', money(vueltoPed));
            } else {
                rowEfectivo.style.display = 'none';
            }
        }

        var rowObs = document.getElementById('rowClTicketObs');
        if (rowObs) {
            var notaCliente = limpiarObservacionCliente(order.observacion || '');
            if (notaCliente !== '') {
                rowObs.style.display = 'block';
                setElText('lblClTicketObs', notaCliente);
            } else {
                rowObs.style.display = 'none';
            }
        }
    }

    function openDetail(id) {
        activeId = id;
        popularTicket({ id_pedido: id }, []);
        openModal(detailModal);

        fetch('index.php?action=logistica_detalle_cajero&id=' + encodeURIComponent(id), {
            headers: { 'X-Requested-With': 'XMLHttpRequest' }
        })
        .then(function (response) { return response.json(); })
        .then(function (result) {
            if (!result || !result.success || !result.data) throw new Error((result && result.message) || 'No se pudo cargar el pedido.');
            ultimoTicketData = result.data;
            popularTicket(result.data.pedido || {}, result.data.detalle || []);
        })
        .catch(function (error) {
            showToast(error.message || 'Error al cargar el comprobante.', true);
        });
    }

    function asegurarHtml2Pdf(callback) {
        if (typeof window.html2pdf !== 'undefined') { callback(); return; }
        var exist = document.querySelector('script[src*="html2pdf"]');
        if (exist) { exist.addEventListener('load', function () { callback(); }); return; }
        var s = document.createElement('script');
        s.src = 'https://cdnjs.cloudflare.com/ajax/libs/html2pdf.js/0.10.1/html2pdf.bundle.min.js';
        s.onload = function () { callback(); };
        s.onerror = function () { showToast('No se pudo cargar la librería para exportar PDF.', true); };
        document.head.appendChild(s);
    }

    function descargarTicketPdf() {
        var ticketEl = document.getElementById('clTicketImprimible');
        if (!ticketEl) return;
        var idPed = (ultimoTicketData && ultimoTicketData.pedido && ultimoTicketData.pedido.id_pedido) || activeId || 0;
        var filename = 'Comprobante-Pedido-' + String(idPed).padStart(5, '0') + '.pdf';
        showToast('Generando PDF del comprobante...', false);
        asegurarHtml2Pdf(function () {
            var rect = ticketEl.getBoundingClientRect();
            var altoMm = Math.ceil((rect.height * 25.4) / 96) + 12;
            if (altoMm < 140) altoMm = 140;
            var opt = {
                margin: [4, 4, 4, 4],
                filename: filename,
                image: { type: 'jpeg', quality: 0.98 },
                html2canvas: { scale: 2, useCORS: true, logging: false, scrollX: 0, scrollY: 0 },
                jsPDF: { unit: 'mm', format: [80, altoMm], orientation: 'portrait' }
            };
            window.html2pdf().set(opt).from(ticketEl).save().then(function () {
                showToast('Comprobante descargado con éxito.', false);
            }).catch(function () {
                showToast('No se pudo generar el archivo PDF.', true);
            });
        });
    }

    function activeStateCode(value) {
        var normalized = String(value || '').toLowerCase().replace(/[_-]/g, ' ');
        if (normalized.indexOf('cancel') !== -1) return 'cancelado';
        if (normalized.indexOf('entreg') !== -1) return 'entregado';
        if (normalized.indexOf('camin') !== -1) return 'en_camino';
        if (normalized.indexOf('recoj') !== -1 || normalized.indexOf('listo') !== -1) return 'listo_recojo';
        if (normalized.indexOf('prep') !== -1) return 'en_preparacion';
        return 'pendiente';
    }

    if (search) search.addEventListener('input', filterRows);
    if (typeFilter) typeFilter.addEventListener('change', function () {
        setQuickFilter('total');
    });
    if (clearFilters) clearFilters.addEventListener('click', resetFilters);
    if (summary) summary.addEventListener('click', function (event) {
        var card = event.target.closest('[data-quick-filter]');
        if (card) setQuickFilter(card.getAttribute('data-quick-filter'));
    });
    if (saveState) saveState.addEventListener('click', saveOrderState);

    table.addEventListener('click', function (event) {
        var kebabButton = event.target.closest('.cl-kebab-button');
        if (kebabButton) {
            var kebab = kebabButton.closest('.cl-kebab');
            var willOpen = !kebab.classList.contains('is-open');
            closeMenus(kebab);
            kebab.classList.toggle('is-open', willOpen);
            kebabButton.setAttribute('aria-expanded', willOpen ? 'true' : 'false');
            return;
        }
        var action = event.target.closest('.cl-action');
        if (!action) return;
        var row = action.closest('.cl-order-row');
        closeMenus();
        if (action.classList.contains('cl-action-state')) openState(row);
        if (action.classList.contains('cl-action-detail')) openDetail(parseInt(action.getAttribute('data-id'), 10));
    });

    document.addEventListener('click', function (event) {
        if (!event.target.closest('.cl-kebab')) closeMenus();
    });

    document.querySelectorAll('[data-close-modal]').forEach(function (button) {
        button.addEventListener('click', function () { closeModal(button.closest('.cl-modal')); });
    });
    document.querySelectorAll('.cl-modal').forEach(function (modal) {
        modal.addEventListener('click', function (event) { if (event.target === modal) closeModal(modal); });
    });
    document.addEventListener('keydown', function (event) {
        if (event.key === 'Escape') {
            closeMenus();
            document.querySelectorAll('.cl-modal.is-open').forEach(closeModal);
        }
    });
    var btnPrint = document.getElementById('btnClTicketImprimir');
    if (btnPrint) btnPrint.addEventListener('click', function () { window.print(); });
    var btnPdf = document.getElementById('btnClTicketDescargarPdf');
    if (btnPdf) btnPdf.addEventListener('click', descargarTicketPdf);

    if (pageContainer && window.TblPagination) {
        paginator = window.TblPagination.create(pageContainer, {
            size: 5,
            rows: function () { return matchedRows; }
        });
    }
    filterRows();
})();
