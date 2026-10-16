'use strict';

document.addEventListener('DOMContentLoaded', function () {

    /* ── References ── */
    const direccionIdInput   = document.getElementById('checkoutDireccionId');
    const tipoEntregaInput   = document.getElementById('checkoutTipoEntrega');
    const metodoIdInput      = document.getElementById('checkoutMetodoId');
    const subtotalElement    = document.getElementById('checkoutSubtotal');
    const deliveryCostEl     = document.getElementById('checkoutDeliveryCost');
    const totalEl            = document.getElementById('checkoutTotal');
    const observacionInput   = document.getElementById('checkoutObservacion');
    const observacionCount   = document.getElementById('observacionCount');
    const formNuevaDireccion = document.getElementById('formNuevaDireccionCheckout');
    const btnGuardarDir      = document.getElementById('btnGuardarDireccionCheckout');

    let costoDeliveryActual = 5.00; // Se actualiza al seleccionar distrito

    const subtotalBase = obtenerNumeroMoneda(
        subtotalElement ? subtotalElement.textContent : '0'
    );

    /* ════════════════════════════════════
       MODALS
    ════════════════════════════════════ */
    function abrirModal(id) {
        const modal = document.getElementById(id);
        if (!modal) return;
        modal.classList.add('active');
        modal.setAttribute('aria-hidden', 'false');
        document.body.classList.add('co-modal-open');
    }

    function cerrarModal(id) {
        const modal = document.getElementById(id);
        if (!modal) return;
        modal.classList.remove('active');
        modal.setAttribute('aria-hidden', 'true');
        if (!document.querySelector('.co-modal-overlay.active')) {
            document.body.classList.remove('co-modal-open');
        }
    }

    window.abrirModal = abrirModal;
    window.cerrarModal = cerrarModal;

    /* Open via [data-open-modal] buttons */
    document.addEventListener('click', function (e) {
        const openBtn = e.target.closest('[data-open-modal]');
        if (openBtn) {
            e.preventDefault();
            e.stopPropagation();
            abrirModal(openBtn.dataset.openModal);
            return;
        }

        /* Close via [data-close-modal] buttons */
        const btn = e.target.closest('[data-close-modal]');
        if (btn) { cerrarModal(btn.dataset.closeModal); return; }

        /* Click outside modal */
        if (e.target.classList.contains('co-modal-overlay')) {
            cerrarModal(e.target.id);
        }
    });

    document.addEventListener('keydown', function (e) {
        if (e.key !== 'Escape') return;
        document.querySelectorAll('.co-modal-overlay.active').forEach(function (m) {
            cerrarModal(m.id);
        });
    });

    /* Cambiar / agregar dirección */
    const btnCambiar = document.getElementById('btnCambiarDireccion');
    if (btnCambiar) btnCambiar.addEventListener('click', function () {
        abrirModal('modalDireccionesCheckout');
    });

    const btnAgregarPrimera = document.getElementById('btnAgregarPrimeraDireccion');
    if (btnAgregarPrimera) btnAgregarPrimera.addEventListener('click', function () {
        prepararFormNuevaDireccion();
        abrirModal('modalNuevaDireccionCheckout');
    });

    const btnNuevaDireccion = document.getElementById('btnAbrirNuevaDireccion');
    if (btnNuevaDireccion) btnNuevaDireccion.addEventListener('click', function () {
        cerrarModal('modalDireccionesCheckout');
        prepararFormNuevaDireccion();
        abrirModal('modalNuevaDireccionCheckout');
    });

    /* ════════════════════════════════════
       ADDRESS SELECTION (modal list)
    ════════════════════════════════════ */
    document.addEventListener('click', function (e) {
        const opt = e.target.closest('.co-address-opt');
        if (!opt) return;

        document.querySelectorAll('.co-address-opt').forEach(function (item) {
            item.classList.remove('active');
            const radio = item.querySelector('input[type="radio"]');
            if (radio) radio.checked = false;
        });

        opt.classList.add('active');
        const radio = opt.querySelector('input[type="radio"]');
        if (radio) radio.checked = true;

        const id       = opt.dataset.idDireccion || '';
        const calle    = opt.dataset.calle    || '';
        const numero   = opt.dataset.numero   || '';
        const lote     = opt.dataset.lote     || '';
        const distrito = opt.dataset.distrito || '';
        const referencia = opt.dataset.referencia || '';
        const costo    = parseFloat(opt.dataset.costo || '5') || 5;

        if (direccionIdInput) direccionIdInput.value = id;

        const parts = [calle];
        if (numero) parts.push('#' + numero);
        if (lote)   parts.push('Lote ' + lote);
        if (distrito) parts.push(distrito);
        const full = parts.filter(Boolean).join(', ');

        actualizarDireccionVisual(full, referencia, costo, calle, numero, lote, distrito);

        cerrarModal('modalDireccionesCheckout');
        mostrarToast('Dirección seleccionada correctamente.', 'success');
    });

    function actualizarDireccionVisual(full, referencia, costo, calle, numero, lote, distrito) {
        const addrText = document.getElementById('checkoutAddressText');
        const addrRef  = document.getElementById('checkoutAddressReference');
        const addrCard = document.getElementById('checkoutSelectedAddress');
        const noAddr   = document.getElementById('checkoutNoAddress');
        const addrReq  = document.getElementById('checkoutAddressRequirement');

        if (addrText) addrText.textContent = full;
        if (addrRef) addrRef.textContent = referencia ? 'Ref: ' + referencia : '';

        if (addrCard) addrCard.style.display = '';
        if (noAddr) noAddr.style.display = 'none';
        if (addrReq) addrReq.setAttribute('hidden', '');

        // Rellenar campos del formulario en pantalla (estilo Imagen 2 y 3)
        const calleIn = document.getElementById('checkoutCalle');
        const numIn   = document.getElementById('checkoutNumero');
        const loteIn  = document.getElementById('checkoutLote');
        const distIn  = document.getElementById('checkoutDistrito');
        const refIn   = document.getElementById('checkoutReferencia');

        if (calleIn && calle !== undefined) calleIn.value = calle;
        if (numIn && numero !== undefined) numIn.value = numero;
        if (loteIn && lote !== undefined) loteIn.value = lote;
        if (refIn && referencia !== undefined) refIn.value = referencia;

        if (distIn && distrito) {
            for (let i = 0; i < distIn.options.length; i++) {
                if (distIn.options[i].value.toLowerCase() === distrito.toLowerCase()) {
                    distIn.selectedIndex = i;
                    distIn.dispatchEvent(new Event('change'));
                    break;
                }
            }
        }

        // Update delivery cost if provided
        if (typeof costo === 'number') {
            actualizarCostoDelivery(costo);
        }
    }

    // Escuchar cambios en el selector de distrito para actualizar el costo dinámicamente
    const distSelectEl = document.getElementById('checkoutDistrito');
    if (distSelectEl) {
        distSelectEl.addEventListener('change', function () {
            const opt = this.options[this.selectedIndex];
            if (!opt) return;
            const costo = parseFloat(opt.dataset.costo || '5') || 5;
            actualizarCostoDelivery(costo);
        });
    }

    function actualizarCostoDelivery(costo) {
        costoDeliveryActual = costo;
        actualizarTotales();
        const displayEl = document.getElementById('costoDeliveryDisplay');
        if (displayEl) displayEl.textContent = 'S/ ' + costo.toFixed(2);
    }

    /* ════════════════════════════════════
       DELIVERY OPTION SELECTION
    ════════════════════════════════════ */
    document.addEventListener('click', function (e) {
        const opt = e.target.closest('[data-delivery-option]');
        if (!opt) return;

        document.querySelectorAll('[data-delivery-option]').forEach(function (item) {
            item.classList.remove('active');
            const r = item.querySelector('input[type="radio"]');
            if (r) r.checked = false;
        });

        opt.classList.add('active');
        const r = opt.querySelector('input[type="radio"]');
        if (r) r.checked = true;

        if (tipoEntregaInput) tipoEntregaInput.value = opt.dataset.deliveryOption;

        const secDir = document.getElementById('seccionDireccion');
        const addrReq = document.getElementById('checkoutAddressRequirement');

        if (opt.dataset.deliveryOption === 'recojo') {
            if (secDir) secDir.style.display = 'none';
        } else {
            if (secDir) secDir.style.display = '';
        }

        actualizarTotales();
    });

    /* ════════════════════════════════════
       PAYMENT OPTION SELECTION
    ════════════════════════════════════ */
    document.addEventListener('click', function (e) {
        if (e.target.closest('[data-open-modal]') || e.target.closest('[data-close-modal]')) return;

        const opt = e.target.closest('[data-payment-option]');
        if (!opt) return;

        document.querySelectorAll('[data-payment-option]').forEach(function (item) {
            item.classList.remove('active');
            const r = item.querySelector('input[type="radio"]');
            if (r) r.checked = false;
        });

        opt.classList.add('active');
        const r = opt.querySelector('input[type="radio"]');
        if (r) r.checked = true;

        if (metodoIdInput) metodoIdInput.value = opt.dataset.paymentOption;

        actualizarPanelVoucher(opt);
    });

    document.addEventListener('change', function (e) {
        if (e.target && e.target.name === 'metodo_pago_visual') {
            const opt = e.target.closest('[data-payment-option]');
            if (opt) {
                document.querySelectorAll('[data-payment-option]').forEach(function (item) {
                    item.classList.remove('active');
                });
                opt.classList.add('active');
                if (metodoIdInput) metodoIdInput.value = opt.dataset.paymentOption;
                actualizarPanelVoucher(opt);
            }
        }
    });

    function actualizarPanelVoucher(option) {
        if (!option) return;

        const secEfectivo = document.getElementById('seccionEfectivo');
        const secYapePlin = document.getElementById('seccionYapePlin');
        const secTarjeta  = document.getElementById('seccionTarjeta');

        // Ocultar todos los paneles dinámicos inline
        if (secEfectivo) secEfectivo.style.display = 'none';
        if (secYapePlin) secYapePlin.style.display = 'none';
        if (secTarjeta)  secTarjeta.style.display  = 'none';

        document.querySelectorAll('.co-dynamic-panel').forEach(function(p) {
            p.style.display = 'none';
        });

        const nombre = (option.dataset.nombreMetodo || '').toLowerCase();

        if (nombre.includes('efectivo') || nombre.includes('recibir')) {
            if (secEfectivo) {
                secEfectivo.style.display = 'block';
                const montoEfectivo = document.getElementById('montoEfectivoTexto');
                if (montoEfectivo && totalEl) montoEfectivo.textContent = totalEl.textContent;
            }
        } else if (nombre.includes('yape') || nombre.includes('plin') || nombre.includes('billetera')) {
            if (secYapePlin) {
                secYapePlin.style.display = 'block';
            }
        } else if (nombre.includes('tarjeta') || nombre.includes('débito') || nombre.includes('debito') || nombre.includes('crédito') || nombre.includes('credito') || nombre.includes('card')) {
            if (secTarjeta) {
                secTarjeta.style.display = 'block';
            }
        }
    }

    // Tabs Yape/Plin
    document.addEventListener('click', function(e) {
        const tab = e.target.closest('.co-yp-tab');
        if (!tab) return;
        
        const parent = tab.closest('.co-yape-plin-tabs');
        if (parent) {
            parent.querySelectorAll('.co-yp-tab').forEach(t => {
                t.classList.remove('active');
                t.style.background = 'transparent';
                t.style.borderColor = '#e2e8f0';
            });
            tab.classList.add('active');
            tab.style.background = '#fff0f0';
            tab.style.borderColor = '#d32f2f';
            
            const target = tab.dataset.target;
            const lbl = document.getElementById('lblCelularYP');
            const hidden = document.getElementById('billetera_seleccionada');
            if (lbl) lbl.textContent = target === 'yape' ? 'Ingresa tu celular Yape' : 'Ingresa tu celular Plin';
            if (hidden) hidden.value = target === 'yape' ? 'Yape' : 'Plin';
        }
    });

    /* ════════════════════════════════════
       TOTALS
    ════════════════════════════════════ */
    function actualizarTotales() {
        const tipo      = tipoEntregaInput ? tipoEntregaInput.value : 'delivery';
        const envio     = tipo === 'delivery' ? costoDeliveryActual : 0;
        const total     = subtotalBase + envio;

        const fmtEnvio  = formatearMoneda(envio);
        const fmtTotal  = formatearMoneda(total);

        /* Update all delivery cost / total elements across panels */
        ['', '2', '3', '4'].forEach(function (suffix) {
            const dc = document.getElementById('checkoutDeliveryCost' + suffix);
            const t  = document.getElementById('checkoutTotal' + suffix);
            if (dc) dc.textContent = fmtEnvio;
            if (t)  t.textContent  = fmtTotal;
        });

        const montoEfectivo = document.getElementById('montoEfectivoTexto');
        if (montoEfectivo) montoEfectivo.textContent = fmtTotal;
    }

    function formatearMoneda(valor) {
        return 'S/ ' + Number(valor).toFixed(2);
    }

    function obtenerNumeroMoneda(texto) {
        return parseFloat(
            String(texto).replace('S/', '').replace(',', '').trim()
        ) || 0;
    }

    /* ════════════════════════════════════
       OBSERVACION COUNTER
    ════════════════════════════════════ */
    if (observacionInput && observacionCount) {
        observacionInput.addEventListener('input', function () {
            observacionCount.textContent = observacionInput.value.length;
        });
    }

    /* ════════════════════════════════════
       NUEVA DIRECCIÓN — FORM SUBMIT
    ════════════════════════════════════ */
    function prepararFormNuevaDireccion() {
        if (!formNuevaDireccion) return;
        formNuevaDireccion.reset();
        formNuevaDireccion.querySelectorAll('[data-error-for]').forEach(function (el) {
            el.textContent = '';
        });
    }

    if (formNuevaDireccion && btnGuardarDir) {
        formNuevaDireccion.addEventListener('submit', function (e) {
            e.preventDefault();

            const fields = ['departamento', 'provincia', 'distrito', 'calle'];
            let valid = true;

            fields.forEach(function (field) {
                const input = formNuevaDireccion.querySelector('[name="' + field + '"]');
                const errorEl = formNuevaDireccion.querySelector('[data-error-for="' + field + '"]');
                if (input && !input.value.trim()) {
                    valid = false;
                    if (errorEl) errorEl.textContent = 'Este campo es requerido.';
                } else {
                    if (errorEl) errorEl.textContent = '';
                }
            });

            if (!valid) return;

            btnGuardarDir.disabled = true;
            btnGuardarDir.textContent = 'Guardando...';

            const formData = new FormData(formNuevaDireccion);

            fetch(formNuevaDireccion.action, {
                method: 'POST',
                body: formData,
                headers: { 'X-Requested-With': 'XMLHttpRequest' }
            })
            .then(function (res) { return res.json(); })
            .then(function (data) {
                if (data.success) {
                    const idNueva = data.id_direccion || data.data?.id_direccion;

                    const calle  = formNuevaDireccion.querySelector('[name="calle"]').value.trim();
                    const dist   = formNuevaDireccion.querySelector('[name="distrito"]').value.trim();
                    const prov   = formNuevaDireccion.querySelector('[name="provincia"]').value.trim();
                    const depto  = formNuevaDireccion.querySelector('[name="departamento"]').value.trim();
                    const ref    = formNuevaDireccion.querySelector('[name="referencia"]').value.trim();

                    if (idNueva && direccionIdInput) direccionIdInput.value = idNueva;

                    const full = [calle, dist, prov, depto].filter(Boolean).join(', ');
                    actualizarDireccionVisual(full, ref);

                    cerrarModal('modalNuevaDireccionCheckout');
                    mostrarToast('Dirección guardada correctamente.', 'success');

                    formNuevaDireccion.reset();
                } else {
                    mostrarToast(data.message || 'Error al guardar la dirección.', 'error');
                }
            })
            .catch(function () {
                mostrarToast('Error de conexión. Intenta de nuevo.', 'error');
            })
            .finally(function () {
                btnGuardarDir.disabled = false;
                btnGuardarDir.textContent = 'Guardar dirección';
            });
        });
    }

    /* ════════════════════════════════════
       FORM SUBMIT
    ════════════════════════════════════ */
    const formCheckout = document.getElementById('formCheckout');
    const btnConfirmarPedidoFinal = document.getElementById('btnConfirmarPedidoFinal');
    const totalConfirmacionPedido = document.getElementById('checkoutModalConfirmTotal');
    let pedidoConfirmado = false;
    // Credit Card Visual Updates & Real-time Brand Detection
    const ccNum = document.getElementById('cc_numero');
    const ccName = document.getElementById('cc_nombre');
    const ccExp = document.getElementById('cc_vencimiento');
    const ccVisNum = document.getElementById('ccNumVisual');
    const ccVisName = document.getElementById('ccNameVisual');
    const ccVisExp = document.getElementById('ccExpVisual');
    const ccCvv = document.getElementById('cc_cvv');
    const ccCvvVis = document.getElementById('ccCvvVisual');
    const ccFlipper = document.getElementById('ccFlipper');
    const ccFrontCard = document.getElementById('ccFrontCard');
    const ccBackCard = document.getElementById('ccBackCard');
    const ccBrandVis = document.getElementById('ccBrandVisual');
    const ccBadgeMarca = document.getElementById('ccBadgeMarca');

    const brandBadges = {
        visa: document.getElementById('badgeCardVisa'),
        amex: document.getElementById('badgeCardAmex'),
        mastercard: document.getElementById('badgeCardMastercard')
    };

    function actualizarBadgesMarcas(activeBrandKey) {
        Object.entries(brandBadges).forEach(([key, el]) => {
            if (!el) return;
            if (!activeBrandKey) {
                // Estado neutral si no hay tarjeta ingresada
                el.style.opacity = '0.85';
                el.style.border = '1.5px solid #cbd5e1';
                el.style.transform = 'scale(1)';
                el.style.boxShadow = 'none';
            } else if (key === activeBrandKey) {
                // Marca detectada: resaltar con borde azul y sombra
                el.style.opacity = '1';
                el.style.border = '2px solid #2563eb';
                el.style.transform = 'scale(1.08)';
                el.style.boxShadow = '0 4px 12px rgba(37,99,235,0.25)';
            } else {
                // Otras marcas: atenuar
                el.style.opacity = '0.25';
                el.style.border = '1.5px solid #e2e8f0';
                el.style.transform = 'scale(0.95)';
                el.style.boxShadow = 'none';
            }
        });
    }

    function detectarMarcaTarjeta(numero) {
        const num = numero.replace(/\D/g, '');
        if (!num) {
            return {
                brandKey: null,
                marca: 'TARJETA',
                nombre: 'Tarjeta',
                badgeText: '💳 Tarjeta',
                bg: '#1e293b', // Solid dark slate (Sin degradado)
                badgeBg: '#e2e8f0',
                badgeColor: '#475569',
                brandHtml: '<span style="font-weight:900; font-size:1.15rem; letter-spacing:1px; color:#cbd5e1;">TARJETA</span>',
                cvvLen: 3,
                isAmex: false
            };
        }

        // 1. Visa: empieza con 4
        if (/^4/.test(num)) {
            return {
                brandKey: 'visa',
                marca: 'VISA',
                nombre: 'Visa',
                badgeText: '💳 Visa',
                bg: '#0f2042', // Azul Marino Clásico Oficial Visa (Sin degradado)
                badgeBg: '#dbeafe',
                badgeColor: '#1d4ed8',
                brandHtml: '<span style="font-family:\'Arial Black\', sans-serif; font-weight:900; font-size:1.4rem; font-style:italic; letter-spacing:1px; color:#ffffff;">VISA</span>',
                cvvLen: 3,
                isAmex: false
            };
        }

        // 2. American Express: 34 o 37
        if (/^3[47]/.test(num)) {
            return {
                brandKey: 'amex',
                marca: 'AMEX',
                nombre: 'American Express',
                badgeText: '🟦 Amex',
                bg: '#006fcf', // Azul Oficial American Express (Sin degradado)
                badgeBg: '#e0f2fe',
                badgeColor: '#0369a1',
                brandHtml: '<span style="font-family:\'Arial Black\', sans-serif; font-size:0.75rem; font-weight:900; color:#ffffff; line-height:1; text-align:center; display:block; border:1px solid #ffffff; padding:2px 4px; border-radius:3px;">AMERICAN<br>EXPRESS</span>',
                cvvLen: 4,
                isAmex: true
            };
        }

        // 3. Mastercard / Maestro: 50-59, 22-27, 67 (detecta 5031 de Mercado Pago)
        if (/^(5[0-9]|2[2-7]|67)/.test(num)) {
            return {
                brandKey: 'mastercard',
                marca: 'MASTERCARD',
                nombre: 'Mastercard',
                badgeText: '🔴🟠 Mastercard',
                bg: '#17171a', // Negro Mate Carbón Oficial Mastercard (Sin degradado)
                badgeBg: '#ffedd5',
                badgeColor: '#c2410c',
                brandHtml: '<div style="display:flex; align-items:center; gap:6px;"><svg viewBox="0 0 36 22" width="34" height="20"><circle cx="12" cy="11" r="9" fill="#eb001b"/><circle cx="24" cy="11" r="9" fill="#f79e1b" fill-opacity="0.9"/></svg><span style="font-size:0.95rem; font-weight:800; text-transform:lowercase; letter-spacing:-0.5px; color:#ffffff;">mastercard</span></div>',
                cvvLen: 3,
                isAmex: false
            };
        }

        return {
            brandKey: null,
            marca: 'TARJETA',
            nombre: 'Tarjeta',
            badgeText: '💳 Tarjeta',
            bg: '#1e293b', // Solid dark slate (Sin degradado)
            badgeBg: '#e2e8f0',
            badgeColor: '#475569',
            brandHtml: '<span style="font-weight:900; font-size:1.15rem; letter-spacing:1px; color:#cbd5e1;">TARJETA</span>',
            cvvLen: 3,
            isAmex: false
        };
    }

    if (ccNum) ccNum.addEventListener('input', e => {
        let val = e.target.value.replace(/\D/g, '');
        const brand = detectarMarcaTarjeta(val);

        // Actualizar estados de los badges de marca
        actualizarBadgesMarcas(brand.brandKey);

        // Formato para Amex (4-6-5) o general (4-4-4-4)
        let formatted = '';
        if (brand.isAmex) {
            ccNum.maxLength = 17; // 15 dígitos + 2 espacios
            if (val.length > 10) {
                formatted = val.substring(0, 4) + ' ' + val.substring(4, 10) + ' ' + val.substring(10, 15);
            } else if (val.length > 4) {
                formatted = val.substring(0, 4) + ' ' + val.substring(4, 10);
            } else {
                formatted = val;
            }
        } else {
            ccNum.maxLength = 19; // 16 dígitos + 3 espacios
            formatted = val.match(/.{1,4}/g)?.join(' ') || '';
        }

        e.target.value = formatted;
        if (ccVisNum) ccVisNum.textContent = formatted || '•••• •••• •••• ••••';

        // Actualizar visualización de marca en tiempo real
        if (ccBrandVis) ccBrandVis.innerHTML = brand.brandHtml;
        if (ccBadgeMarca) {
            ccBadgeMarca.textContent = brand.badgeText;
            ccBadgeMarca.style.background = brand.badgeBg;
            ccBadgeMarca.style.color = brand.badgeColor;
        }
        if (ccFrontCard) ccFrontCard.style.background = brand.bg;
        if (ccBackCard) ccBackCard.style.background = brand.bg;

        // Ajustar CVV según marca
        if (ccCvv) {
            ccCvv.maxLength = brand.cvvLen;
            ccCvv.placeholder = brand.cvvLen === 4 ? '••••' : '•••';
            if (ccCvvVis && !ccCvv.value) ccCvvVis.textContent = brand.cvvLen === 4 ? '••••' : '•••';
        }
    });

    if (ccName) ccName.addEventListener('input', e => {
        if (ccVisName) ccVisName.textContent = e.target.value.toUpperCase() || 'NOMBRE APELLIDO';
    });

    if (ccExp) ccExp.addEventListener('input', e => {
        let val = e.target.value.replace(/\D/g, '');
        if (val.length > 2) val = val.substring(0,2) + '/' + val.substring(2,4);
        e.target.value = val;
        if (ccVisExp) ccVisExp.textContent = val || 'MM/AA';
    });

    if (ccCvv && ccFlipper) {
        ccCvv.addEventListener('focus', () => ccFlipper.style.transform = 'rotateY(180deg)');
        ccCvv.addEventListener('blur', () => ccFlipper.style.transform = 'rotateY(0deg)');
        ccCvv.addEventListener('input', e => {
            if (ccCvvVis) ccCvvVis.textContent = e.target.value || '•••';
        });
    }

    // Validar monto efectivo en tiempo real y calcular vuelto
    const montoEfectivo = document.getElementById('monto_efectivo');
    const efectivoError = document.getElementById('efectivoError');
    const boxVuelto = document.getElementById('boxVueltoEfectivo');
    const textoVuelto = document.getElementById('textoVueltoEfectivo');
    const btnsSugerencia = document.querySelectorAll('.btn-sugerencia-efectivo');

    function calcularVueltoEfectivo() {
        if (!montoEfectivo) return;
        const val = parseFloat(montoEfectivo.value || 0);
        const total = subtotalBase + (tipoEntregaInput && tipoEntregaInput.value === 'delivery' ? costoDeliveryActual : 0);

        if (val >= total && total > 0) {
            if (efectivoError) efectivoError.style.display = 'none';
            const vuelto = val - total;
            if (boxVuelto && textoVuelto) {
                textoVuelto.textContent = 'S/ ' + vuelto.toFixed(2);
                boxVuelto.style.display = 'flex';
            }
        } else if (val > 0 && val < total) {
            if (efectivoError) efectivoError.style.display = 'block';
            if (boxVuelto) boxVuelto.style.display = 'none';
        } else {
            if (efectivoError) efectivoError.style.display = 'none';
            if (boxVuelto) boxVuelto.style.display = 'none';
        }
    }

    if (montoEfectivo) {
        montoEfectivo.addEventListener('input', calcularVueltoEfectivo);
    }

    btnsSugerencia.forEach(btn => {
        btn.addEventListener('click', () => {
            const total = subtotalBase + (tipoEntregaInput && tipoEntregaInput.value === 'delivery' ? costoDeliveryActual : 0);
            const tipo = btn.dataset.monto;
            if (tipo === 'exacto') {
                montoEfectivo.value = total.toFixed(2);
            } else {
                montoEfectivo.value = parseFloat(tipo).toFixed(2);
            }
            calcularVueltoEfectivo();
            montoEfectivo.focus();
        });
    });

    // Phone Formatting for Yape
    const celYP = document.getElementById('celular_yape_plin');
    const boxCelYape = document.getElementById('boxCelularYape');
    if (celYP) {
        celYP.addEventListener('focus', () => {
            if (boxCelYape) boxCelYape.style.borderColor = '#73228B';
        });
        celYP.addEventListener('blur', () => {
            if (boxCelYape) boxCelYape.style.borderColor = '#cbd5e1';
        });
        celYP.addEventListener('input', e => {
            let val = e.target.value.replace(/\D/g, '');
            let formatted = val.match(/.{1,3}/g)?.join(' ') || '';
            e.target.value = formatted;
        });
    }

    // Yape OTP 6-Box individual input management
    const yapeOtpBoxes = document.querySelectorAll('.yape-otp-box');
    const hiddenCodigoYP = document.getElementById('codigo_yape_plin');

    function sincronizarCodigoYape() {
        let code = '';
        yapeOtpBoxes.forEach(b => code += b.value.trim());
        if (hiddenCodigoYP) hiddenCodigoYP.value = code;
    }

    yapeOtpBoxes.forEach((box, idx) => {
        box.addEventListener('focus', () => {
            box.style.borderColor = '#73228B';
            box.style.boxShadow = '0 0 0 3px rgba(115, 34, 139, 0.15)';
        });
        box.addEventListener('blur', () => {
            box.style.borderColor = '#cbd5e1';
            box.style.boxShadow = 'none';
        });

        box.addEventListener('input', e => {
            const val = e.target.value.replace(/\D/g, '');
            e.target.value = val ? val[val.length - 1] : '';
            sincronizarCodigoYape();

            // Avanzar automáticamente a la siguiente casilla
            if (e.target.value && idx < yapeOtpBoxes.length - 1) {
                yapeOtpBoxes[idx + 1].focus();
                yapeOtpBoxes[idx + 1].select();
            }
        });

        box.addEventListener('keydown', e => {
            if (e.key === 'Backspace' && !box.value && idx > 0) {
                yapeOtpBoxes[idx - 1].focus();
            }
        });

        // Soporte para pegar código completo (ej: 123456)
        box.addEventListener('paste', e => {
            e.preventDefault();
            const pasteData = (e.clipboardData || window.clipboardData).getData('text').replace(/\D/g, '');
            if (pasteData) {
                const digits = pasteData.split('');
                yapeOtpBoxes.forEach((b, i) => {
                    b.value = digits[i] || '';
                });
                sincronizarCodigoYape();
                const nextIdx = Math.min(digits.length, yapeOtpBoxes.length - 1);
                yapeOtpBoxes[nextIdx].focus();
            }
        });
    });

    // Botón oficial "Yapear S/ ..."
    const btnYapearDirecto = document.getElementById('btnYapearDirecto');
    if (btnYapearDirecto) {
        btnYapearDirecto.addEventListener('click', function() {
            if (typeof window.procesarEnvioPedidoDirecto === 'function') {
                window.procesarEnvioPedidoDirecto();
            }
        });
    }

    /* ════════════════════════════════════
       MODALES DE PAGO: GUARDAR / CONFIRMAR
    ════════════════════════════════════ */
    const btnConfTarjeta = document.getElementById('btnConfirmarTarjeta');
    if (btnConfTarjeta) {
        btnConfTarjeta.addEventListener('click', function() {
            const num = ccNum ? ccNum.value.replace(/\D/g, '') : '';
            const exp = ccExp ? ccExp.value.replace(/\D/g, '') : '';
            const cvv = ccCvv ? ccCvv.value.replace(/\D/g, '') : '';
            const nom = ccName ? ccName.value.trim() : '';
            const brand = detectarMarcaTarjeta(num);
            const minDigits = brand.isAmex ? 15 : 14;
            const minCvv = brand.isAmex ? 4 : 3;

            if (num.length < minDigits) {
                mostrarToast('Por favor ingresa un número de tarjeta válido.', 'error');
                if (ccNum) ccNum.focus();
                return;
            }
            if (exp.length < 4) {
                mostrarToast('Ingresa una fecha de vencimiento válida (MM/AA).', 'error');
                if (ccExp) ccExp.focus();
                return;
            }
            if (cvv.length < minCvv) {
                mostrarToast('Ingresa el código de seguridad (CVV) de ' + minCvv + ' dígitos.', 'error');
                if (ccCvv) ccCvv.focus();
                return;
            }
            if (nom.length < 3) {
                mostrarToast('Ingresa el nombre del titular de la tarjeta.', 'error');
                if (ccName) ccName.focus();
                return;
            }

            const last4 = num.slice(-4);
            const resEl = document.getElementById('resumenMetodoTarjeta');
            if (resEl) {
                resEl.innerHTML = '<span class="co-badge-status-ok">&#10003; ' + brand.nombre + ' •••• ' + last4 + '</span> <button type="button" class="co-btn-open-modal" data-open-modal="modalPagoTarjeta">Editar &rarr;</button>';
            }
            cerrarModal('modalPagoTarjeta');
            mostrarToast('Datos de tu tarjeta guardados correctamente.', 'success');
        });
    }

    const btnConfYape = document.getElementById('btnConfirmarYape');
    if (btnConfYape) {
        btnConfYape.addEventListener('click', function() {
            const cel = celYP ? celYP.value.replace(/\D/g, '') : '';
            const cod = hiddenCodigoYP ? hiddenCodigoYP.value.replace(/\D/g, '') : '';

            if (cel.length !== 9 || !cel.startsWith('9')) {
                mostrarToast('Ingresa un número de celular Yape válido de 9 dígitos (debe empezar con 9).', 'error');
                if (celYP) celYP.focus();
                return;
            }
            if (cod.length !== 6) {
                mostrarToast('Ingresa los 6 dígitos del código de aprobación de Yape.', 'error');
                const emptyOtp = Array.from(document.querySelectorAll('.yape-otp-box')).find(b => !b.value);
                if (emptyOtp) emptyOtp.focus();
                return;
            }

            const resEl = document.getElementById('resumenMetodoYape');
            if (resEl) {
                resEl.innerHTML = '<span class="co-badge-status-ok">&#10003; Yape Cel: ' + cel + '</span> <button type="button" class="co-btn-open-modal" data-open-modal="modalPagoYape">Editar &rarr;</button>';
            }
            cerrarModal('modalPagoYape');
            mostrarToast('Datos de Yape listos para procesar la compra.', 'success');
        });
    }

    const btnConfEfectivo = document.getElementById('btnConfirmarEfectivo');
    if (btnConfEfectivo) {
        btnConfEfectivo.addEventListener('click', function() {
            const montoInput = document.getElementById('monto_efectivo');
            const totalNum = obtenerNumeroMoneda(totalEl ? totalEl.textContent : '0');
            const montoVal = parseFloat(montoInput ? montoInput.value : '') || 0;

            if (montoVal > 0 && montoVal < totalNum) {
                mostrarToast('El monto con el que vas a pagar debe ser mayor o igual al total (' + formatearMoneda(totalNum) + ').', 'error');
                if (montoInput) montoInput.focus();
                return;
            }

            const vuelto = montoVal > totalNum ? (montoVal - totalNum) : 0;
            const resEl = document.getElementById('resumenMetodoEfectivo');
            if (resEl) {
                if (montoVal >= totalNum) {
                    resEl.innerHTML = '<span class="co-badge-status-ok" id="textoResumenEfectivo">&#10003; Paga con S/ ' + montoVal.toFixed(2) + ' ' + (vuelto > 0 ? '(Vuelto: S/ ' + vuelto.toFixed(2) + ')' : '(Monto exacto)') + '</span> <button type="button" class="co-btn-open-modal" data-open-modal="modalPagoEfectivo">Cambiar monto &rarr;</button>';
                } else {
                    resEl.innerHTML = '<span class="co-badge-status-ok" id="textoResumenEfectivo">&#10003; Pago con monto exacto</span> <button type="button" class="co-btn-open-modal" data-open-modal="modalPagoEfectivo">Cambiar monto &rarr;</button>';
                }
            }
            cerrarModal('modalPagoEfectivo');
            mostrarToast('Monto en efectivo guardado.', 'success');
        });
    }

    if (formCheckout) {
        formCheckout.addEventListener('submit', function (e) {
            const metodoOption = document.querySelector('[data-payment-option].active');
            const nombreMetodo = (metodoOption && metodoOption.dataset.nombreMetodo) ? metodoOption.dataset.nombreMetodo.toLowerCase() : '';
            
            // Validación Efectivo
            if (nombreMetodo.includes('efectivo') || nombreMetodo.includes('recibir')) {
                const total = subtotalBase + (tipoEntregaInput && tipoEntregaInput.value === 'delivery' ? costoDeliveryActual : 0);
                const val = parseFloat(montoEfectivo ? montoEfectivo.value : 0);
                if (isNaN(val) || val < total) {
                    e.preventDefault();
                    mostrarToast('El monto ingresado es menor al total a pagar.', 'error');
                    if (efectivoError) efectivoError.style.display = 'block';
                    return;
                }
            }

            // Si es directo sin modal o desde el paso 2, procesar sin abrir modal
            if (window.pedidoDirectoSinModal) {
                e.preventDefault();
                procesarEnvioPedido();
                return;
            }

            if (!pedidoConfirmado) {
                e.preventDefault();
                const totalActual = document.getElementById('checkoutTotal3');
                if (totalConfirmacionPedido && totalActual) {
                    totalConfirmacionPedido.textContent = totalActual.textContent;
                }
                abrirModal('modalConfirmarPedido');
                return;
            }

            e.preventDefault();
            procesarEnvioPedido();
        });
    }

    function procesarEnvioPedido() {
        const submits = formCheckout.querySelectorAll('[type="submit"]');
        submits.forEach(function (btn) {
            const def = btn.querySelector('.co-submit-default');
            const loading = btn.querySelector('.co-submit-loading');
            if (def) def.hidden = true;
            if (loading) loading.hidden = false;
            btn.disabled = true;
        });

        const btnStep2 = document.getElementById('btnNextStep2Sidebar');
        if (btnStep2) {
            btnStep2.disabled = true;
            btnStep2.innerHTML = '<span style="display:inline-flex; align-items:center; gap:8px;">Procesando pago...</span>';
        }

        const formData = new FormData(formCheckout);

        const celYP = document.getElementById('celular_yape_plin');
        const codYP = document.getElementById('codigo_yape_plin');
        if (celYP && celYP.value) {
            formData.set('celular_yape_plin', celYP.value.replace(/\D/g, ''));
            formData.set('celular_remitente', celYP.value.replace(/\D/g, ''));
        }
        if (codYP && codYP.value) {
            formData.set('codigo_yape_plin', codYP.value.replace(/\D/g, ''));
            formData.set('codigo_operacion', codYP.value.replace(/\D/g, ''));
        }

        const ccNumEl = document.getElementById('cc_numero');
        const ccNomEl = document.getElementById('cc_nombre');
        const ccExpEl = document.getElementById('cc_vencimiento');
        const ccCvvEl = document.getElementById('cc_cvv');
        if (ccNumEl && ccNumEl.value) formData.set('cc_numero', ccNumEl.value);
        if (ccNomEl && ccNomEl.value) formData.set('cc_nombre', ccNomEl.value);
        if (ccExpEl && ccExpEl.value) formData.set('cc_vencimiento', ccExpEl.value);
        if (ccCvvEl && ccCvvEl.value) formData.set('cc_cvv', ccCvvEl.value);

        fetch('index.php?action=crear_pedido', {
            method: 'POST',
            body: formData,
            headers: {
                'X-Requested-With': 'XMLHttpRequest'
            }
        })
        .then(res => res.json())
        .then(data => {
            if (!data.success) {
                mostrarToast(data.message || 'Error al procesar el pedido.', 'error');
                pedidoConfirmado = false;
                window.pedidoDirectoSinModal = false;
                submits.forEach(function (btn) {
                    const def = btn.querySelector('.co-submit-default');
                    const loading = btn.querySelector('.co-submit-loading');
                    if (def) def.hidden = false;
                    if (loading) loading.hidden = true;
                    btn.disabled = false;
                });
                if (btnStep2) {
                    btnStep2.disabled = false;
                    btnStep2.innerHTML = 'Pagar y completar pedido <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><polyline points="20 6 9 17 4 12"></polyline></svg>';
                }
                return;
            }

            // Si es pasarela digital (Mercado Pago: Tarjetas, Yape, Plin)
            if (data.data && data.data.es_pasarela) {
                mostrarToast('Conectando con la pasarela segura...', 'info');

                if (typeof MercadoPago !== 'undefined' && data.data.preference_id) {
                    try {
                        const mp = new MercadoPago(data.data.public_key, { locale: 'es-PE' });
                        mp.checkout({
                            preference: {
                                id: data.data.preference_id
                            },
                            autoOpen: true
                        });
                        setTimeout(() => {
                            submits.forEach(function (btn) {
                                const def = btn.querySelector('.co-submit-default');
                                const loading = btn.querySelector('.co-submit-loading');
                                if (def) def.hidden = false;
                                if (loading) loading.hidden = true;
                                btn.disabled = false;
                            });
                        }, 2000);
                        return;
                    } catch (err) {
                        console.warn('Error abriendo modal Mercado Pago, redirigiendo...', err);
                    }
                }

                // Redirección directa como respaldo
                window.location.href = data.data.checkout_url;
                return;
            }

            // Si es pago en efectivo u otro método directo
            if (data.data && data.data.redirect) {
                window.location.href = data.data.redirect;
            } else {
                window.location.href = 'index.php?action=pedido_confirmado&id=' + ((data.data && data.data.pedido) ? data.data.pedido.id_pedido : '');
            }
        })
        .catch(err => {
            console.error('Error enviando pedido:', err);
            mostrarToast('Error de conexión con el servidor. Intenta de nuevo.', 'error');
            pedidoConfirmado = false;
            window.pedidoDirectoSinModal = false;
            submits.forEach(function (btn) {
                const def = btn.querySelector('.co-submit-default');
                const loading = btn.querySelector('.co-submit-loading');
                if (def) def.hidden = false;
                if (loading) loading.hidden = true;
                btn.disabled = false;
            });
            const btnStep2 = document.getElementById('btnNextStep2Sidebar');
            if (btnStep2) {
                btnStep2.disabled = false;
                btnStep2.innerHTML = 'Pagar y completar pedido <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><polyline points="20 6 9 17 4 12"></polyline></svg>';
            }
        });
    }

    window.procesarEnvioPedidoDirecto = procesarEnvioPedido;

    if (btnConfirmarPedidoFinal && formCheckout) {
        btnConfirmarPedidoFinal.addEventListener('click', function() {
            pedidoConfirmado = true;
            cerrarModal('modalConfirmarPedido');
            procesarEnvioPedido();
        });
    }

    /* ════════════════════════════════════
       TOAST (Notificación Moderna y Amigable)
    ════════════════════════════════════ */
    function traducirMensajeAmigable(msg) {
        if (!msg) return 'Ocurrió un inconveniente al procesar tu pago. Por favor intenta de nuevo.';

        // Eliminar códigos técnicos entre paréntesis como (cc_rejected_...)
        let limpio = String(msg).replace(/\s*\([a-zA-Z0-9_-]+\)\.?/g, '').trim();

        if (/security_code|cvv|código de seguridad/i.test(msg)) {
            return 'El código de seguridad (CVV) es incorrecto. Por favor verifica los 3 o 4 dígitos al reverso de tu tarjeta.';
        }
        if (/insufficient_amount|fondos/i.test(msg)) {
            return 'Tu tarjeta no cuenta con fondos suficientes para completar esta compra.';
        }
        if (/expiry_date|vencimiento|vencida/i.test(msg)) {
            return 'La fecha de vencimiento es incorrecta o la tarjeta ya venció. Por favor revísala.';
        }
        if (/card_number|número de tarjeta/i.test(msg)) {
            return 'El número de tarjeta no es válido. Por favor verifica los dígitos ingresados.';
        }
        if (/call_for_authorize|banco|autoriz/i.test(msg)) {
            return 'Tu banco emisor requiere autorización para este pago. Por favor comunícate con tu banco o usa otra tarjeta.';
        }
        if (/other_reason|rechazado/i.test(msg)) {
            return 'El pago no pudo ser procesado por el emisor. Por favor intenta con otra tarjeta o medio de pago.';
        }
        return limpio;
    }

    function mostrarToast(mensaje, tipo = 'info') {
        let container = document.getElementById('checkoutToastContainer');
        if (!container) {
            container = document.createElement('div');
            container.id = 'checkoutToastContainer';
            container.className = 'co-toast-container';
            document.body.appendChild(container);
        }

        const mensajeAmigable = traducirMensajeAmigable(mensaje);
        const card = document.createElement('div');
        card.className = 'co-toast-card ' + tipo;

        let iconSvg = '';
        let titulo = '';

        if (tipo === 'error') {
            titulo = 'No pudimos procesar tu pago';
            iconSvg = '<svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><circle cx="12" cy="12" r="10"></circle><line x1="12" y1="8" x2="12" y2="12"></line><line x1="12" y1="16" x2="12.01" y2="16"></line></svg>';
        } else if (tipo === 'success') {
            titulo = '¡Listo!';
            iconSvg = '<svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"></path><polyline points="22 4 12 14.01 9 11.01"></polyline></svg>';
        } else {
            titulo = 'Información';
            iconSvg = '<svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><circle cx="12" cy="12" r="10"></circle><line x1="12" y1="16" x2="12" y2="12"></line><line x1="12" y1="8" x2="12.01" y2="8"></line></svg>';
        }

        card.innerHTML = 
            '<div class="co-toast-icon-wrap">' + iconSvg + '</div>' +
            '<div class="co-toast-body">' +
                '<div class="co-toast-title">' + titulo + '</div>' +
                '<div class="co-toast-message">' + mensajeAmigable + '</div>' +
            '</div>' +
            '<button type="button" class="co-toast-close" aria-label="Cerrar">&times;</button>';

        const closeBtn = card.querySelector('.co-toast-close');
        if (closeBtn) {
            closeBtn.addEventListener('click', function() {
                card.style.opacity = '0';
                card.style.transform = 'translateY(-10px)';
                card.style.transition = 'all 0.25s ease';
                setTimeout(function() { card.remove(); }, 250);
            });
        }

        container.appendChild(card);

        setTimeout(function () {
            if (!card.parentNode) return;
            card.style.opacity = '0';
            card.style.transform = 'translateY(-10px)';
            card.style.transition = 'all 0.35s ease';
            setTimeout(function () { card.remove(); }, 350);
        }, 5000);
    }

    window.mostrarToast = mostrarToast;

    /* ════════════════════════════════════
       INIT
    ════════════════════════════════════ */
    // Init totals
    actualizarTotales();

    // Init voucher panel for initially selected payment
    const initPayment = document.querySelector('[data-payment-option].active');
    if (initPayment) actualizarPanelVoucher(initPayment);

});
