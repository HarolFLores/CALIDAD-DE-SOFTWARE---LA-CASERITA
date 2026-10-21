<?php
$flashSuccess = $flashSuccess ?? null;
$flashError = $flashError ?? null;
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="icon" type="image/png" href="Images/logo_backup.png">
    <title>Panel de caja - La Caserita</title>
    <link rel="stylesheet" href="Styles/global.css">
    <link rel="stylesheet" href="Styles/staff-panel.css?v=<?= time() ?>">
    <link rel="stylesheet" href="Styles/Vistas/Cajero/panel_caja.css?v=<?= filemtime(__DIR__ . '/../../Styles/Vistas/Cajero/panel_caja.css') ?>">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body class="staff-app cashier-app">
    <?php $paginaPanelStaff = 'arqueo'; include __DIR__ . '/../Staff/partials/staff_sidebar.php'; ?>
    <div class="staff-main">
        <header class="staff-topbar">
            <div class="staff-topbar-left">
                <button type="button" class="staff-menu-toggle hide-on-desktop" id="staffMenuToggle" aria-label="Abrir menú">
                    <svg xmlns="http://www.w3.org/2000/svg" width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><line x1="3" y1="6" x2="21" y2="6"></line><line x1="3" y1="12" x2="21" y2="12"></line><line x1="3" y1="18" x2="21" y2="18"></line></svg>
                </button>
                <span class="staff-topbar-title">Arqueo de caja</span>
            </div>
        </header>
        <main class="staff-content">
            <?php if ($flashSuccess): ?><div class="staff-flash success"><?= htmlspecialchars($flashSuccess) ?></div><?php endif; ?>
            <?php if ($flashError): ?><div class="staff-flash error"><?= htmlspecialchars($flashError) ?></div><?php endif; ?>

            <section class="cashier-dashboard-heading">
                <div>
                    <span class="staff-section-kicker">CONTROL Y CIERRE DE EFECTIVO</span>
                    <p><?= htmlspecialchars($nombre) ?> · Control de gaveta y arqueo de turno</p>
                </div>
                <div>
                    <?php if ($sesionActiva): ?>
                        <a class="cashier-open-pos" href="index.php?action=pos"><i class="fa-solid fa-cash-register"></i> Abrir Caja POS (Vender)</a>
                    <?php else: ?>
                        <span class="cashier-pos-blocked" style="background:#fee2e2; color:#dc2626; padding:8px 14px; border-radius:8px; font-weight:700; font-size:0.85rem; display:inline-flex; align-items:center; gap:8px;">
                            <i class="fa-solid fa-lock"></i> Caja POS Bloqueada (Apertura requerida)
                        </span>
                    <?php endif; ?>
                </div>
            </section>

            <?php if (!$sesionActiva): ?>
                <section class="cashier-open-card">
                    <div class="cashier-open-icon"><i class="fa-solid fa-unlock-keyhole"></i></div>
                    <div>
                        <span class="staff-section-kicker" style="color:#dc2626;">TURNO CERRADO</span>
                        <h2>Apertura de caja</h2>
                        <p>No se pueden procesar ventas en Caja POS hasta que abras tu turno indicando el fondo inicial en gaveta.</p>
                    </div>
                    <form id="formAperturaCaja" method="post" action="index.php?action=abrir_caja_cajero" class="cashier-open-form" data-cash-confirm data-cash-kind="open">
                        <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($csrfToken) ?>">
                        <label for="cashOpeningAmount">Fondo inicial en efectivo (S/)</label>
                        <div class="cashier-input-action">
                            <input id="cashOpeningAmount" type="number" name="monto_apertura" min="0" step="0.01" value="0.00" required placeholder="0.00">
                            <button type="submit"><i class="fa-solid fa-lock-open"></i> Aperturar Turno</button>
                        </div>
                    </form>
                </section>
            <?php else: ?>
                <section class="cashier-session-banner">
                    <div>
                        <span class="cashier-live"><i></i> CAJA ABIERTA</span>
                        <h2><?= htmlspecialchars($sesionActiva['caja_id']) ?></h2>
                        <p>Inicio <?= date('d/m/Y H:i', strtotime($sesionActiva['fecha_apertura'])) ?> · Fondo inicial S/ <?= number_format((float)$sesionActiva['monto_apertura'], 2) ?></p>
                    </div>
                    <div class="cashier-expected">
                        <span>EFECTIVO ESPERADO EN GAVETA</span>
                        <strong>S/ <?= number_format((float)($resumen['efectivo_esperado'] ?? 0), 2) ?></strong>
                    </div>
                </section>

                <section class="cashier-metrics" aria-label="Resumen de turno">
                    <article>
                        <span>FONDO INICIAL</span>
                        <strong>S/ <?= number_format((float)$resumen['apertura'], 2) ?></strong>
                    </article>
                    <article>
                        <span>VENTAS EN EFECTIVO (<?= $resumen['total_tickets'] ?>)</span>
                        <strong>S/ <?= number_format((float)$resumen['ventas_efectivo'], 2) ?></strong>
                    </article>
                    <article>
                        <span>YAPE / PLIN / DIGITAL</span>
                        <strong>S/ <?= number_format((float)$resumen['ventas_yape'] + (float)$resumen['ventas_plin'], 2) ?></strong>
                    </article>
                    <article>
                        <span>TARJETA</span>
                        <strong>S/ <?= number_format((float)$resumen['ventas_tarjeta'], 2) ?></strong>
                    </article>
                    <article>
                        <span>INGRESOS EXTRA</span>
                        <strong>S/ <?= number_format((float)$resumen['ingresos_extra'], 2) ?></strong>
                    </article>
                    <article>
                        <span>GASTOS / EGRESOS</span>
                        <strong>S/ <?= number_format((float)$resumen['egresos'], 2) ?></strong>
                    </article>
                </section>

                <section class="cashier-actions-grid">
                    <article class="cashier-action-panel">
                        <h2><i class="fa-solid fa-arrow-right-arrow-left"></i> Movimiento de caja</h2>
                        <form id="formMovimientoCaja" method="post" action="index.php?action=movimiento_caja_cajero" class="cashier-form-grid" data-cash-confirm data-cash-kind="movement">
                            <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($csrfToken) ?>">
                            <label>Tipo<select name="tipo" required><option value="INGRESO">Ingreso</option><option value="EGRESO">Gasto</option></select></label>
                            <label>Categoría<select name="categoria"><option value="otro">Otro</option><option value="compra_menor">Compra menor</option><option value="servicios">Servicios</option><option value="ajuste">Ajuste</option></select></label>
                            <label>Monto (S/)<input type="number" name="monto" min="0.01" step="0.01" required></label>
                            <label class="cashier-form-wide">Motivo<input type="text" name="motivo" maxlength="180" required></label>
                            <label class="cashier-form-wide">Comprobante (opcional)<input type="text" name="comprobante" maxlength="80"></label>
                            <button type="submit"><i class="fa-solid fa-plus"></i> Registrar movimiento</button>
                        </form>
                    </article>

                    <article class="cashier-action-panel cashier-close-panel">
                        <h2><i class="fa-solid fa-calculator"></i> Arqueo y Cierre de caja</h2>
                        <div style="background:#f8fafc; border:1px solid #e2e8f0; padding:12px 14px; border-radius:8px; margin-bottom:12px;">
                            <div style="display:flex; justify-content:space-between; margin-bottom:4px; font-size:0.85rem;">
                                <span style="color:#64748b;">Efectivo esperado:</span>
                                <strong style="color:#15803d; font-size:1.05rem;">S/ <?= number_format((float)$resumen['efectivo_esperado'], 2) ?></strong>
                            </div>
                            <div id="boxDiferenciaArqueo" style="font-size:0.82rem; font-weight:700; color:#64748b; margin-top:4px;">
                                Ingresa el efectivo contado para verificar el arqueo.
                            </div>
                        </div>
                        <form method="post" action="index.php?action=cerrar_caja_cajero" class="cashier-close-form" id="formCierreCaja" data-cash-confirm data-cash-kind="close">
                            <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($csrfToken) ?>">
                            <label for="cashCountedAmount">Efectivo físico contado en gaveta (S/)</label>
                            <input id="cashCountedAmount" type="number" name="monto_cierre_real" min="0" step="0.01" placeholder="0.00" required>
                            <label for="cashClosingObservation">Observación del cierre</label>
                            <textarea id="cashClosingObservation" name="observacion_cierre" maxlength="500" rows="3" placeholder="Describe el resultado del arqueo" required></textarea>
                            <button type="submit" id="btnConfirmarCierre" style="margin-top:10px;"><i class="fa-solid fa-lock"></i> Realizar Arqueo y Cerrar Caja</button>
                        </form>
                    </article>
                </section>

                <section class="cashier-movements-panel">
                    <div class="cashier-section-title"><h2>Movimientos de este turno</h2><span><?= count($movimientos) ?> registros</span></div>
                    <div class="cashier-table-scroll"><table><thead><tr><th>FECHA</th><th>TIPO</th><th>MOTIVO</th><th>COMPROBANTE</th><th class="cashier-align-right">MONTO</th></tr></thead><tbody id="tbodyMovCaja">
                        <?php if (!$movimientos): ?><tr><td colspan="5" class="cashier-no-data">No hay movimientos para este turno.</td></tr><?php else: foreach ($movimientos as $movimiento): ?>
                            <tr class="fila-mov-caja"><td><?= date('d/m/Y H:i', strtotime($movimiento['fecha'])) ?></td><td><?= htmlspecialchars($movimiento['tipo']) ?></td><td><?= htmlspecialchars($movimiento['motivo']) ?></td><td><?= htmlspecialchars($movimiento['comprobante_ref'] ?: '—') ?></td><td class="cashier-align-right">S/ <?= number_format((float)$movimiento['monto'], 2) ?></td></tr>
                        <?php endforeach; endif; ?>
                    </tbody></table></div>
                    <div class="pg-footer" id="pagMovCaja"></div>
                </section>
            <?php endif; ?>

            <section class="cashier-movements-panel cashier-history-panel">
                <div class="cashier-section-title"><h2>Mis turnos anteriores</h2><span><?= count($historialSesiones) ?> registros</span></div>
                <div class="cashier-table-scroll"><table><thead><tr><th>CAJA</th><th>APERTURA</th><th>CIERRE</th><th>FONDO</th><th>ESPERADO</th><th>CONTADO</th><th>DIFERENCIA</th><th>OBSERVACIÓN</th><th>ESTADO</th></tr></thead><tbody id="tbodyTurnosCaja">
                    <?php if (!$historialSesiones): ?><tr><td colspan="9" class="cashier-no-data">Aún no hay turnos registrados.</td></tr><?php else: foreach ($historialSesiones as $turno): ?>
                        <?php $diferenciaTurno = (float)($turno['diferencia'] ?? 0); ?>
                        <tr class="fila-turno-caja"><td><?= htmlspecialchars($turno['caja_id']) ?></td><td><?= date('d/m/Y H:i', strtotime($turno['fecha_apertura'])) ?></td><td><?= !empty($turno['fecha_cierre']) ? date('d/m/Y H:i', strtotime($turno['fecha_cierre'])) : '—' ?></td><td>S/ <?= number_format((float)$turno['monto_apertura'], 2) ?></td><td>S/ <?= number_format((float)($turno['monto_cierre_esperado'] ?? 0), 2) ?></td><td>S/ <?= number_format((float)($turno['monto_cierre_real'] ?? 0), 2) ?></td><td class="cashier-difference <?= abs($diferenciaTurno) < 0.01 ? 'is-even' : 'has-difference' ?>">S/ <?= number_format($diferenciaTurno, 2) ?></td><td class="cashier-observation"><?= htmlspecialchars($turno['observacion_cierre'] ?? '—') ?></td><td><span class="status-pill <?= strtolower($turno['estado']) === 'abierta' ? 'activo' : 'inactivo' ?>"><?= ucfirst(htmlspecialchars($turno['estado'])) ?></span></td></tr>
                    <?php endforeach; endif; ?>
                </tbody></table></div>
                <div class="pg-footer" id="pagTurnosCaja"></div>
            </section>

            <div class="cash-modal" id="cashConfirmModal" hidden role="dialog" aria-modal="true" aria-labelledby="cashConfirmTitle">
                <div class="cash-modal-card">
                    <button type="button" class="modal-close" data-cash-close aria-label="Cerrar">&times;</button>
                    <span class="cash-modal-icon" id="cashConfirmIcon"><i class="fa-solid fa-cash-register"></i></span>
                    <h2 id="cashConfirmTitle">Confirmar operación</h2>
                    <p id="cashConfirmMessage"></p>
                    <dl class="cash-modal-summary" id="cashConfirmSummary" hidden></dl>
                    <div class="cash-modal-actions">
                        <button type="button" class="cash-modal-cancel" data-cash-close>Cancelar</button>
                        <button type="button" class="cash-modal-confirm" id="cashConfirmSubmit">Confirmar</button>
                    </div>
                </div>
            </div>
        </main>
    </div>
    <script>
        const EFECTIVO_ESPERADO = <?= (float)($resumen['efectivo_esperado'] ?? 0) ?>;
        function paginarTablaCajero(containerId, rowSelector, size) {
            const container = document.getElementById(containerId);
            const rows = Array.prototype.slice.call(document.querySelectorAll(rowSelector));
            if (!container || !rows.length || !window.TblPagination) return;
            window.TblPagination.create(container, {
                size: size || 5,
                rows: function () { return rows; },
                show: function (row) { row.style.display = ''; },
                hide: function (row) { row.style.display = 'none'; }
            });
        }
        function calcularDiferenciaArqueo(val) {
            const box = document.getElementById('boxDiferenciaArqueo');
            if (!box) return;
            const contado = parseFloat(val);
            if (isNaN(contado) || val === '') {
                box.style.color = '#64748b';
                box.innerHTML = 'Ingresa el efectivo contado para verificar el arqueo.';
                return;
            }
            const dif = contado - EFECTIVO_ESPERADO;
            if (Math.abs(dif) < 0.01) {
                box.style.color = '#15803d';
                box.innerHTML = '✓ Cuadre exacto (Diferencia: S/ 0.00)';
            } else if (dif < 0) {
                box.style.color = '#dc2626';
                box.innerHTML = `⚠️ Faltante en caja: S/ ${Math.abs(dif).toFixed(2)}`;
            } else {
                box.style.color = '#0284c7';
                box.innerHTML = `ℹ️ Sobrante en caja: S/ ${dif.toFixed(2)}`;
            }
        }
        const cashModal = document.getElementById('cashConfirmModal');
        const cashTitle = document.getElementById('cashConfirmTitle');
        const cashMessage = document.getElementById('cashConfirmMessage');
        const cashSummary = document.getElementById('cashConfirmSummary');
        const cashIcon = document.getElementById('cashConfirmIcon');
        const cashConfirmSubmit = document.getElementById('cashConfirmSubmit');
        let cashPendingForm = null;
        let cashPendingSubmitter = null;
        const formatoMoneda = (valor) => `S/ ${(Number(valor) || 0).toFixed(2)}`;
        const cerrarModalCaja = () => {
            if (cashModal) cashModal.hidden = true;
            cashPendingForm = null;
            cashPendingSubmitter = null;
        };
        const resumirModalCaja = (filas) => {
            if (!cashSummary) return;
            cashSummary.replaceChildren();
            filas.forEach(([etiqueta, valor]) => {
                const termino = document.createElement('dt');
                termino.textContent = etiqueta;
                const detalle = document.createElement('dd');
                detalle.textContent = valor;
                cashSummary.append(termino, detalle);
            });
            cashSummary.hidden = filas.length === 0;
        };
        const contenidoModalCaja = (formElement) => {
            const datos = new FormData(formElement);
            const tipoOperacion = formElement.dataset.cashKind;
            if (tipoOperacion === 'open') {
                const monto = parseFloat(datos.get('monto_apertura')) || 0;
                return {
                    icono: 'fa-lock-open',
                    titulo: 'Aperturar turno de caja',
                    mensaje: 'Verifica el fondo inicial antes de abrir la caja. Este monto quedará registrado al iniciar el turno.',
                    filas: [['Fondo inicial', formatoMoneda(monto)]],
                    boton: 'Aperturar turno'
                };
            }
            if (tipoOperacion === 'movement') {
                const esIngreso = String(datos.get('tipo') || '').toUpperCase() === 'INGRESO';
                const categorias = { otro: 'Otro', compra_menor: 'Compra menor', servicios: 'Servicios', ajuste: 'Ajuste' };
                const filas = [
                    ['Tipo', esIngreso ? 'Ingreso' : 'Gasto'],
                    ['Categoría', categorias[datos.get('categoria')] || 'Otro'],
                    ['Monto', formatoMoneda(parseFloat(datos.get('monto')) || 0)],
                    ['Motivo', String(datos.get('motivo') || 'Sin motivo')]
                ];
                if (datos.get('comprobante')) filas.push(['Comprobante', String(datos.get('comprobante'))]);
                return {
                    icono: 'fa-arrow-right-arrow-left',
                    titulo: esIngreso ? 'Registrar ingreso en caja' : 'Registrar gasto en caja',
                    mensaje: 'Revisa los datos del movimiento antes de registrarlo. Esta operación afectará el efectivo esperado.',
                    filas,
                    boton: 'Registrar movimiento'
                };
            }
            const contado = parseFloat(datos.get('monto_cierre_real')) || 0;
            const diferencia = contado - EFECTIVO_ESPERADO;
            const mensaje = Math.abs(diferencia) < 0.01
                ? 'El efectivo contado coincide con el esperado. La caja quedará cerrada sin diferencias.'
                : (diferencia < 0
                    ? `El arqueo presenta un faltante de ${formatoMoneda(Math.abs(diferencia))}. La caja quedará cerrada con esta diferencia.`
                    : `El arqueo presenta un sobrante de ${formatoMoneda(diferencia)}. La caja quedará cerrada con esta diferencia.`);
            return {
                icono: 'fa-calculator',
                titulo: 'Cerrar caja y registrar arqueo',
                mensaje,
                filas: [
                    ['Efectivo esperado', formatoMoneda(EFECTIVO_ESPERADO)],
                    ['Efectivo contado', formatoMoneda(contado)],
                    ['Diferencia', formatoMoneda(diferencia)],
                    ['Observación', String(datos.get('observacion_cierre') || 'Sin observación')]
                ],
                boton: 'Cerrar caja'
            };
        };
        document.querySelectorAll('[data-cash-confirm]').forEach((formElement) => {
            formElement.addEventListener('submit', (event) => {
                if (formElement.dataset.cashConfirmed === '1') {
                    delete formElement.dataset.cashConfirmed;
                    return;
                }
                event.preventDefault();
                cashPendingForm = formElement;
                cashPendingSubmitter = event.submitter || formElement.querySelector('[type="submit"]');
                const contenido = contenidoModalCaja(formElement);
                if (cashTitle) cashTitle.textContent = contenido.titulo;
                if (cashMessage) cashMessage.textContent = contenido.mensaje;
                if (cashIcon) cashIcon.innerHTML = `<i class="fa-solid ${contenido.icono}"></i>`;
                if (cashConfirmSubmit) cashConfirmSubmit.textContent = contenido.boton;
                resumirModalCaja(contenido.filas);
                if (cashModal) cashModal.hidden = false;
                window.setTimeout(() => cashConfirmSubmit?.focus(), 0);
            });
        });
        cashConfirmSubmit?.addEventListener('click', () => {
            if (!cashPendingForm) return;
            const formElement = cashPendingForm;
            const submitter = cashPendingSubmitter;
            formElement.dataset.cashConfirmed = '1';
            cerrarModalCaja();
            formElement.requestSubmit(submitter || undefined);
        });
        document.querySelectorAll('[data-cash-close]').forEach((boton) => boton.addEventListener('click', cerrarModalCaja));
        cashModal?.addEventListener('click', (event) => { if (event.target === cashModal) cerrarModalCaja(); });
        document.addEventListener('keydown', (event) => {
            if (event.key === 'Escape' && cashModal && !cashModal.hidden) cerrarModalCaja();
        });
        document.getElementById('cashCountedAmount')?.addEventListener('input', function () {
            calcularDiferenciaArqueo(this.value);
        });
        paginarTablaCajero('pagMovCaja', '#tbodyMovCaja .fila-mov-caja', 5);
        paginarTablaCajero('pagTurnosCaja', '#tbodyTurnosCaja .fila-turno-caja', 5);
    </script>
</body>
</html>
