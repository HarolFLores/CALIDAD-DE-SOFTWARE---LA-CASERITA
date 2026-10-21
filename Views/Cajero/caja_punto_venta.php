<?php
require_once 'Core/ajaxHelper.php';
$isAjax = AjaxHelper::isAjax();
$isAdminPOS = ($_SESSION['usuario_rol'] ?? '') === 'admin';
$isCashierPOS = ($_SESSION['usuario_rol'] ?? '') === 'cajero';
?>
<?php if (!$isAjax): ?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Punto de Venta POS - La Caserita</title>
    
    <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800;900&display=swap">
    
    <script src="https://cdnjs.cloudflare.com/ajax/libs/qrcodejs/1.0.0/qrcode.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/html2pdf.js/0.10.1/html2pdf.bundle.min.js"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="Styles/styles-pos.css?v=<?= filemtime(__DIR__ . '/../../Styles/styles-pos.css') ?>">
    <link rel="icon" type="image/png" href="Images/logo_backup.png">
    <?php if ($isAdminPOS || $isCashierPOS): ?>
    <link rel="stylesheet" href="Styles/global.css?v=<?= filemtime(__DIR__ . '/../../Styles/global.css') ?>">
    <link rel="stylesheet" href="Styles/styles-home.css?v=<?= filemtime(__DIR__ . '/../../Styles/styles-home.css') ?>">
    <?php endif; ?>
    <link rel="stylesheet" href="Styles/Vistas/Cajero/caja_punto_venta.css?v=<?= filemtime(__DIR__ . '/../../Styles/Vistas/Cajero/caja_punto_venta.css') ?>">
    <?php if ($isAdminPOS): ?>
    <link rel="stylesheet" href="Styles/styles-users-admin.css?v=<?= filemtime(__DIR__ . '/../../Styles/styles-users-admin.css') ?>">
    <link rel="stylesheet" href="Styles/admin-spa.css">
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="Scripts/admin-spa.js" defer></script>
    <?php endif; ?>
</head>
<body class="<?= $isAdminPOS ? '' : 'pos-body' . ($isCashierPOS ? ' role-staff' : '') ?>">
<?php if ($isAdminPOS): ?>
    <div class="admin-layout">
        <?php include __DIR__ . '/../Admin/partials/admin_sidebar.php'; ?>
        <div class="admin-main">
            <header class="admin-topbar">
                <div class="admin-topbar-left">
                    <button type="button" class="admin-hamburger-btn hide-on-desktop" id="openAdminSidebar" aria-label="Abrir menú">
                        <i class="fa-solid fa-bars"></i>
                    </button>
                    <div class="admin-topbar-brand">
                        <span class="brand-title">Punto de Venta POS</span>
                    </div>
                </div>
                <div class="admin-topbar-actions">
                    <?php include __DIR__ . '/../Admin/partials/admin_topbar_actions.php'; ?>
                </div>
            </header>
            <div id="admin-container">
<?php endif; ?>
<?php if (!$isAjax && $isCashierPOS): ?>
    <?php $paginaPanelStaff = 'pos'; include __DIR__ . '/../Staff/partials/staff_sidebar.php'; ?>
<?php endif; ?>
<?php endif; ?>

<?php if (!$isAjax && !$isAdminPOS): ?>
    <header class="pos-header">
        <?php if ($isCashierPOS): ?>
        <button type="button" class="btn-menu-mobile pos-menu-toggle" id="staffMenuToggle" aria-label="Abrir menú">
            <svg xmlns="http://www.w3.org/2000/svg" width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><line x1="3" y1="6" x2="21" y2="6"></line><line x1="3" y1="12" x2="21" y2="12"></line><line x1="3" y1="18" x2="21" y2="18"></line></svg>
        </button>
        <?php endif; ?>
        <div class="pos-brand">
            <i class="fa-solid fa-cash-register"></i>
            <span>La Caserita POS <small style="font-size:0.75rem; opacity:0.8;">[Caja 01]</small></span>
        </div>
    </header>
<?php endif; ?>

    <main class="pos-container">
        
        
        <section class="pos-left-panel">
            <div style="display: flex; gap: 10px; align-items: stretch; margin-bottom: 1.5rem; flex-wrap: wrap;">
                <div class="pos-search-box" style="flex: 1; min-width: 280px; margin-bottom: 0; position: relative;">
                    <i class="fa-solid fa-magnifying-glass pos-search-icon"></i>
                    <input type="text" 
                           id="inputBuscarManual" 
                           class="pos-search-input" 
                           placeholder="Buscar por letras, nombre, marca o código de barras... (F2)" 
                           autocomplete="off">
                    <button type="button" id="posSearchClear" style="display: none; position: absolute; right: 14px; top: 50%; transform: translateY(-50%); background: none; border: none; color: #94a3b8; cursor: pointer; font-size: 1rem; padding: 4px;" title="Limpiar búsqueda">
                        <i class="fa-solid fa-xmark"></i>
                    </button>
                    <div id="posSearchDropdown" class="pos-search-dropdown"></div>
                </div>
                <button type="button" id="statusBadge" class="pos-status-badge scanner-disabled" onclick="alternarEscanerPOS()" aria-pressed="false" title="Habilitar escáner">
                    <span class="status-dot"></span>
                    <span id="statusText">Habilitar escáner</span>
                </button>
            </div>

            <div class="pos-table-wrapper">
                <table class="pos-table">
                    <thead>
                        <tr>
                            <th>Código</th>
                            <th>Producto</th>
                            <th>P. Unit.</th>
                            <th>Cant.</th>
                            <th>Subtotal</th>
                            <th style="text-align: center;">Acción</th>
                        </tr>
                    </thead>
                    <tbody id="posTableBody">
                        
                    </tbody>
                </table>
            </div>
        </section>

        
        <section class="pos-right-panel">
            <h3 class="pos-summary-title">
                <i class="fa-solid fa-receipt"></i> Resumen de la Venta
            </h3>

            <div class="pos-summary-card">
                <div class="summary-line">
                    <span>Subtotal:</span>
                    <strong id="lblSubtotal">S/ 0.00</strong>
                </div>
                <div class="summary-line">
                    <span>IGV (18% incluido):</span>
                    <strong id="lblIgv">S/ 0.00</strong>
                </div>
                <div class="summary-line total">
                    <span>TOTAL A PAGAR:</span>
                    <span id="lblTotal">S/ 0.00</span>
                </div>
            </div>

            
            <!-- CLIENTE / IDENTIFICACIÓN (DNI) -->
            <div class="pos-side-client">
                <div class="pos-side-client-head">
                    <label class="pos-side-client-label">
                        <i class="fa-solid fa-id-card"></i> Documento del cliente:
                    </label>
                    <button type="button" class="pos-side-client-generic" onclick="setClienteGenerico()" title="Asignar Público General">
                        <i class="fa-solid fa-user"></i> Público General
                    </button>
                </div>
                <div class="pos-side-client-dni-row">
                    <div class="pos-side-client-dni-wrap">
                        <input type="text" id="inputClienteDni" class="pos-input-field" maxlength="8" inputmode="numeric" placeholder="DNI (8 dígitos)" value="00000000" style="padding-right:32px; font-weight:700;" onkeypress="if(event.key==='Enter'){event.preventDefault(); consultarDniCajero();}">
                        <button type="button" class="pos-side-client-search" onclick="consultarDniCajero()" title="Consultar RENIEC">
                            <i class="fa-solid fa-magnifying-glass" id="iconBuscarDni"></i>
                        </button>
                    </div>
                </div>
                <div class="pos-side-client-name-row">
                    <span class="pos-side-client-name-label">Nombre:</span>
                    <strong id="lblClienteNombre" class="pos-side-client-name">Público General</strong>
                    <button type="button" class="pos-side-client-edit" onclick="editarNombreClienteManual()" title="Editar nombre manualmente">
                        <i class="fa-solid fa-pen-to-square"></i>
                    </button>
                </div>
            </div>

            <label class="pos-pay-heading">
                MÉTODO DE PAGO <span>(puedes combinar varios)</span>:
            </label>
            <div class="pos-pay-rows" id="posPayRows">
                <div class="pos-pay-row">
                    <span class="pos-pay-label"><i class="fa-solid fa-money-bill-wave"></i> Efectivo</span>
                    <input type="number" min="0" step="0.10" class="pos-input-field pos-pay-input" id="payMontoEfectivo" data-method="efectivo" placeholder="0.00" oninput="onPagoInput('efectivo')" onfocus="onPagoFocus('efectivo')">
                    <div class="pos-pay-actions">
                        <button type="button" class="pos-pay-fill" onclick="autocompletarPago('efectivo')" title="Completar con el saldo pendiente">AUTO</button>
                        <button type="button" class="pos-pay-clear" data-method="efectivo" onclick="limpiarPago('efectivo')" title="Limpiar monto de efectivo" aria-label="Limpiar monto de efectivo" hidden><i class="fa-solid fa-xmark"></i></button>
                    </div>
                </div>
                <div class="pos-pay-row">
                    <span class="pos-pay-label"><i class="fa-solid fa-credit-card"></i> Tarjeta</span>
                    <input type="number" min="0" step="0.10" class="pos-input-field pos-pay-input" id="payMontoTarjeta" data-method="tarjeta" placeholder="0.00" oninput="onPagoInput('tarjeta')" onfocus="onPagoFocus('tarjeta')">
                    <div class="pos-pay-actions">
                        <button type="button" class="pos-pay-fill" onclick="autocompletarPago('tarjeta')" title="Completar con el saldo pendiente">AUTO</button>
                        <button type="button" class="pos-pay-clear" data-method="tarjeta" onclick="limpiarPago('tarjeta')" title="Limpiar monto de tarjeta" aria-label="Limpiar monto de tarjeta" hidden><i class="fa-solid fa-xmark"></i></button>
                    </div>
                </div>
                <div class="pos-pay-row">
                    <span class="pos-pay-label"><i class="fa-solid fa-mobile-screen-button"></i> Yape</span>
                    <input type="number" min="0" step="0.10" class="pos-input-field pos-pay-input" id="payMontoYape" data-method="yape" placeholder="0.00" oninput="onPagoInput('yape')" onfocus="onPagoFocus('yape')">
                    <div class="pos-pay-actions">
                        <button type="button" class="pos-pay-fill" onclick="autocompletarPago('yape')" title="Completar con el saldo pendiente">AUTO</button>
                        <button type="button" class="pos-pay-clear" data-method="yape" onclick="limpiarPago('yape')" title="Limpiar monto de Yape" aria-label="Limpiar monto de Yape" hidden><i class="fa-solid fa-xmark"></i></button>
                    </div>
                </div>
            </div>

            <div class="pos-pay-summary" id="posPaySummary">
                <div class="pos-pay-summary-stats">
                    <span>Total <strong id="payTotal">S/ 0.00</strong></span>
                    <span>Asignado <strong id="payAsignado">S/ 0.00</strong></span>
                    <span id="payResultadoWrap"><span id="lblResultadoPago">Falta</span> <strong id="payResultado">S/ 0.00</strong></span>
                </div>
                <div id="boxMontoRecibido" class="pos-pay-cash is-off">
                    <label class="pos-pay-cash-label">Efectivo recibido (S/):</label>
                    <input type="number" step="0.50" id="inputMontoRecibido" class="pos-input-field" placeholder="0.00" oninput="calcularVueltas()">
                </div>
            </div>

            
            <button type="button" class="btn-cobrar-pos" id="btnCobrarVenta" onclick="procesarVentaCaja()">
                <i class="fa-solid fa-circle-check"></i> COBRAR VENTA (F9)
            </button>
            <button type="button" id="btnImprimirVentaAnterior" class="pos-print-btn" onclick="imprimirVentaAnterior()" disabled>
                <i class="fa-solid fa-print"></i> Imprimir venta anterior
            </button>
        </section>
    </main>

    <div id="posValidationModal" class="pos-validation-overlay" aria-hidden="true">
        <section class="pos-validation-card" role="alertdialog" aria-modal="true" aria-labelledby="posValidationTitle" aria-describedby="posValidationMessage">
            <button type="button" class="btn-close-universal pos-validation-close is-danger" onclick="cerrarAvisoValidacionCaja()" aria-label="Cerrar aviso"><span class="modal-close-glyph" aria-hidden="true">&times;</span></button>
            <div class="pos-validation-icon"><i class="fa-solid fa-circle-exclamation" aria-hidden="true"></i></div>
            <h2 id="posValidationTitle">Revisa el pago</h2>
            <p id="posValidationMessage"></p>
        </section>
    </div>

    <div id="posClientModal" class="pos-validation-overlay" aria-hidden="true">
        <section class="pos-validation-card pos-client-card" role="dialog" aria-modal="true" aria-labelledby="posClientTitle">
            <button type="button" class="btn-close-universal pos-validation-close" onclick="cerrarModalCliente()" aria-label="Cerrar modal"><span class="modal-close-glyph" aria-hidden="true">&times;</span></button>
            <div class="pos-client-heading">
                <span class="pos-client-icon"><i class="fa-solid fa-user-pen"></i></span>
                <div>
                    <h2 id="posClientTitle">Datos del cliente</h2>
                    <p>Busca por DNI o escribe el nombre manualmente.</p>
                </div>
            </div>
            <div class="pos-client-field">
                <label for="inputModalClienteDni">DNI</label>
                <div class="pos-client-search">
                    <input type="text" id="inputModalClienteDni" class="pos-input-field" maxlength="8" inputmode="numeric" placeholder="8 dígitos">
                    <button type="button" class="pos-client-search-btn" onclick="consultarDniModal()"><i class="fa-solid fa-magnifying-glass"></i> Buscar</button>
                </div>
            </div>
            <p id="lblModalClienteEstado" class="pos-client-status" aria-live="polite"></p>
            <div class="pos-client-field">
                <label for="inputModalClienteNombre">Nombre completo</label>
                <input type="text" id="inputModalClienteNombre" class="pos-input-field" maxlength="120" placeholder="Nombre y apellidos">
            </div>
            <div class="pos-client-actions">
                <button type="button" class="btn-confirm-cancel" onclick="cerrarModalCliente()">Cancelar</button>
                <button type="button" class="btn-confirm-accept" onclick="guardarClienteModal()">Guardar cliente</button>
            </div>
        </section>
    </div>

    <div id="posConfirmModal" class="pos-confirm-overlay" aria-hidden="true">
        <section class="pos-confirm-card" role="dialog" aria-modal="true" aria-labelledby="posConfirmTitle">
            <button type="button" class="btn-close-universal pos-confirm-close is-danger" onclick="cerrarConfirmacionCobro()" aria-label="Cerrar">&times;</button>
            <div class="pos-confirm-heading">
                <span class="pos-confirm-icon"><i class="fa-solid fa-cash-register"></i></span>
                <div><small>CONFIRMACIÓN DE VENTA</small><h2 id="posConfirmTitle">Revisa antes de cobrar</h2></div>
            </div>
            <div class="pos-confirm-client"><span>Cliente</span><strong id="confirmCliente"></strong><small id="confirmDocumento"></small></div>
            <div class="pos-confirm-products" id="confirmProductos"></div>
            <div class="pos-confirm-summary">
                <div><span>Métodos de pago</span><strong id="confirmPagos"></strong></div>
                <div class="pos-confirm-total"><span>Total</span><strong id="confirmTotal"></strong></div>
            </div>
            <div class="pos-confirm-actions">
                <button type="button" id="btnConfirmarCobro" class="btn-confirm-accept pos-confirm-submit" onclick="confirmarCobroVenta()">Confirmar</button>
            </div>
        </section>
    </div>

    
    <div id="modalLockQR" class="pos-lock-overlay">
        <div class="pos-qr-modal">
            <button type="button" id="btnCerrarConexionEscaner" class="btn-close-universal pos-qr-close" aria-label="Cerrar conexión del escáner">&times;</button>
            <h2 style="margin:0 0 4px; color:var(--pos-primary); font-size:1.15rem; font-weight:800;">
                <i class="fa-solid fa-qrcode" style="color:var(--pos-primary);"></i> Emparejar Escáner Móvil
            </h2>
            <p class="scanner-modal-intro">Vincula tu celular con el QR; escanea con el teléfono y los productos aparecerán en esta caja.</p>
            <p id="scannerConnectionStatus" class="scanner-connection-status">
                <span class="status-dot"></span><span id="scannerConnectionStatusText">Esperando conexión del celular</span>
            </p>

            <div class="scanner-connection-modes" role="group" aria-label="Tipo de conexión del lector">
                <button type="button" id="btnModeBluetooth" class="scanner-mode-button" aria-pressed="false" onclick="seleccionarTipoConexion('bluetooth')">
                    <i class="fa-brands fa-bluetooth-b"></i> Bluetooth
                </button>
                <button type="button" id="btnModeUsb" class="scanner-mode-button" aria-pressed="false" onclick="seleccionarTipoConexion('usb')">
                    <i class="fa-solid fa-plug"></i> Cable USB
                </button>
            </div>

            <div class="scanner-connection-content">
                <div class="qr-container-box">
                    <div id="qrcode"></div>
                </div>
            </div>
            <div class="scanner-token">Token de sesión: <code id="lblTokenSesion">Cargando...</code></div>

            <div class="scanner-modal-actions">
                <button type="button" id="btnOpenScannerUsb" class="btn-simulate-scanner scanner-open-usb" onclick="abrirLectorEnCelular()" hidden>
                    <i class="fa-solid fa-mobile-screen-button"></i> Abrir automáticamente en celular (Cable USB)
                </button>
                <a id="btnLinkEscanerMovil" href="#" target="_blank" rel="noopener" class="scanner-open-link">
                    <i class="fa-solid fa-arrow-up-right-from-square"></i> Abrir en otra pestaña / Enlace manual
                </a>
            </div>

            <button type="button" id="btnEntrarCaja" class="scanner-enter-pos">
                <i class="fa-solid fa-cash-register"></i> Entrar a Caja
            </button>
        </div>
    </div>
    <div id="scannerConnectionError" class="scanner-error-overlay" aria-hidden="true">
        <section class="scanner-error-dialog" role="alertdialog" aria-modal="true" aria-labelledby="scannerErrorTitle" aria-describedby="scannerErrorMessage">
            <h2 id="scannerErrorTitle">No se pudo conectar</h2>
            <p id="scannerErrorMessage"></p>
            <button type="button" onclick="cerrarErrorConexionEscaner()">Aceptar</button>
        </section>
    </div>

    <!-- MODAL QR YAPE EN MOSTRADOR -->
    <div id="modalYapePlinPOS" class="pos-lock-overlay" style="display:none;">
        <div class="pos-qr-modal" style="max-width:380px;">
            <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:12px;">
                <h3 style="margin:0; color:#7c3aed; font-size:1.2rem; font-weight:800;">
                    <i class="fa-solid fa-mobile-screen-button"></i> Cobro con Yape
                </h3>
                <button type="button" onclick="cerrarModalYapePlin()" class="btn-close-universal">&times;</button>
            </div>
            <p style="margin:0 0 14px; font-size:0.85rem; color:var(--pos-text-secondary);">
                Verifica el abono en la aplicación antes de registrar la venta. Esta pantalla no se conecta a una pasarela de pago.
            </p>
            <div style="background:#f8fafc; border:2px dashed #cbd5e1; border-radius:12px; padding:15px; text-align:center; margin-bottom:14px;">
                <div style="font-size:0.85rem; font-weight:700; color:#15803D;">La Caserita S.A.C.</div>
                <div style="margin:12px auto; width:160px; height:160px; background:#fff; border-radius:10px; display:flex; align-items:center; justify-content:center; box-shadow:0 4px 10px rgba(0,0,0,0.08);">
                    <img id="qrPOSBilletera" src="uploads/qr/qr_yape.svg" style="width:140px; height:140px; object-fit:contain;" alt="Imagen ilustrativa de Yape; no es un código QR de pago">
                </div>
                <p style="margin:0 0 10px; font-size:0.78rem; color:#64748b;">Imagen ilustrativa; no es un código QR escaneable.</p>
                <div style="display:flex; justify-content:center; gap:8px;">
                    <span style="background:#7c3aed; color:#fff; padding:3px 10px; border-radius:10px; font-size:0.75rem; font-weight:800;">YAPE</span>
                </div>
            </div>
            <button type="button" class="btn-cobrar-pos" onclick="cerrarModalYapePlin(); procesarVentaCaja()" style="margin:0; font-size:0.95rem;">
                Continuar con el cobro
            </button>
        </div>
    </div>

    <!-- MODAL TICKET TÉRMICO DE VENTA SIMULADO (80MM / PDF LOCAL) -->
    <div id="modalTicketVenta" class="pos-lock-overlay" style="display:none; z-index:99999;">
        <div style="background:#fff; border-radius:16px; width:100%; max-width:440px; padding:20px; box-shadow:0 25px 50px -12px rgba(0,0,0,0.35); max-height:92vh; display:flex; flex-direction:column;">
            <div class="ticket-no-print" style="display:flex; justify-content:space-between; align-items:center; margin-bottom:14px; border-bottom:1px solid #f1f5f9; padding-bottom:10px;">
                <h3 style="margin:0; font-size:1.1rem; color:#0f172a; font-weight:800;">
                    ?? Comprobante de Venta
                </h3>
                <button type="button" onclick="cerrarTicketVenta()" class="btn-close-universal">&times;</button>
            </div>

            <div style="overflow-y:auto; flex:1; background:#f8fafc; padding:12px; border-radius:10px;">
                <div id="ticketTermicoImprimible" style="background:#fff; padding:20px; font-family:'Courier New', Courier, monospace; font-size:12px; color:#000; line-height:1.35; border:1px solid #e2e8f0; border-radius:6px; max-width:320px; margin:0 auto; box-shadow:0 2px 8px rgba(0,0,0,0.06);">
                    <div style="text-align:center; margin-bottom:10px;">
                        <strong style="font-size:16px; display:block;">TIENDA "LA CASERITA"</strong>
                        <div style="font-size:11px;">RUC: 20512345678</div>
                        <div style="font-size:10px;">Av. Las Flores 123 - San Juan, Lima</div>
                        <div style="font-size:10px;">Teléfono: (01) 456-7890</div>
                        <div style="margin:6px 0; border-bottom:1px dashed #000;"></div>
                        <div style="font-weight:bold; font-size:13px;" id="lblTicketTipoDoc">BOLETA DE VENTA ELECTRÓNICA</div>
                        <div style="font-weight:bold; font-size:13px;" id="lblTicketCodigo">POS-2026-0000</div>
                    </div>

                    <div style="margin-bottom:8px; font-size:11px;">
                        <div><strong>Fecha:</strong> <span id="lblTicketFecha">--</span></div>
                        <div><strong>Cajero:</strong> <span id="lblTicketCajero">--</span></div>
                        <div><strong>Caja:</strong> CAJA-01 (Mostrador)</div>
                        <div><strong>Cliente:</strong> <span id="lblTicketCliente">CLIENTE GENERAL</span></div>
                        <div><strong>DNI:</strong> <span id="lblTicketDni">00000000</span></div>
                    </div>

                    <div style="border-bottom:1px dashed #000; margin:6px 0;"></div>

                    <table style="width:100%; border-collapse:collapse; font-size:11px;">
                        <thead>
                            <tr style="border-bottom:1px solid #000; text-align:left;">
                                <th>CANT</th>
                                <th>DESCRIPCIÓN</th>
                                <th style="text-align:right;">P.U</th>
                                <th style="text-align:right;">TOTAL</th>
                            </tr>
                        </thead>
                        <tbody id="ticketTbodyItems">
                        </tbody>
                    </table>

                    <div style="border-bottom:1px dashed #000; margin:8px 0;"></div>

                    <div style="font-size:11px; text-align:right;">
                        <div>SUBTOTAL: <strong id="lblTicketSubtotal">S/ 0.00</strong></div>
                        <div>I.G.V. (18%): <strong id="lblTicketIgv">S/ 0.00</strong></div>
                        <div style="font-size:14px; margin-top:4px; font-weight:bold;">TOTAL A PAGAR: <span id="lblTicketTotal">S/ 0.00</span></div>
                    </div>

                    <div style="border-bottom:1px dashed #000; margin:8px 0;"></div>

                    <div style="font-size:11px;">
                        <div><strong>FORMA DE PAGO:</strong> <span id="lblTicketMetodo">EFECTIVO</span></div>
                        <div id="rowTicketEfectivo">
                            <div>RECIBIDO: <span id="lblTicketRecibido">S/ 0.00</span></div>
                            <div>VUELTO: <span id="lblTicketVuelto">S/ 0.00</span></div>
                        </div>
                    </div>

                    <div style="border-bottom:1px dashed #000; margin:10px 0;"></div>

                    <div style="text-align:center; font-size:10px;">
                        <p style="margin:2px 0;">¡GRACIAS POR SU PREFERENCIA!</p>
                        <p style="margin:2px 0;">Conserve este comprobante para cambios o reclamos.</p>
                        <div style="margin-top:8px; letter-spacing:2px; font-size:18px;">||| | ||||| || |||| |||||</div>
                    </div>
                </div>
            </div>

            <div class="ticket-no-print" style="margin-top:14px; display:flex; gap:10px;">
                <button type="button" onclick="imprimirTicketTermico()" style="flex:1; padding:12px; background:#15803D; color:#fff; border:none; border-radius:10px; font-weight:800; cursor:pointer; font-size:0.95rem; display:flex; align-items:center; justify-content:center; gap:8px;">
                    <i class="fa-solid fa-print"></i> IMPRIMIR
                </button>
                <button type="button" onclick="descargarTicketPDF()" style="flex:1; padding:12px; background:#ff7f00; color:#fff; border:none; border-radius:10px; font-weight:800; cursor:pointer; font-size:0.95rem; display:flex; align-items:center; justify-content:center; gap:8px;">
                    <i class="fa-solid fa-file-pdf"></i> DESCARGAR PDF
                </button>
            </div>
            <button type="button" class="ticket-no-print" onclick="cerrarTicketVenta()" style="margin-top:8px; padding:10px; background:#f1f5f9; color:#475569; border:none; border-radius:10px; font-weight:700; cursor:pointer; font-size:0.9rem;">
                ? Nueva Venta
            </button>
        </div>
    </div>

    <!-- Estilos de Impresión Térmica Directa -->
    <script>
        var TOKEN_SESION = "<?= htmlspecialchars($sesionCaja['token_emparejamiento'] ?? '') ?>";
        var CAJA_ID = "<?= htmlspecialchars($sesionCaja['caja_id'] ?? 'CAJA-01') ?>";
        var estadoActualConexion = <?= json_encode($sesionCaja['estado_conexion'] ?? 'esperando') ?>;
        var metodoPagoSeleccionado = 'efectivo';
        var totalGeneralVenta = 0;

        
        var clienteDniActual = '00000000';
        var clienteNombreActual = 'Público General';
        var ultimoTicketData = null;
        var accesoCajaPermitido = false;
        var modalConexionSolicitado = false;
        var scannerPOSActivo = false;
        var scannerMovilConectado = false;
        var scannerLectorVerificado = false;
        var scannerDestinoActual = 'pos';
        var scannerTransport = 'bluetooth';
        var scannerFullUrl = '';

        function actualizarBotonEstadoEscaner(activo) {
            scannerPOSActivo = !!activo;
            const boton = document.getElementById('statusBadge');
            if (!boton) return;
            boton.setAttribute('aria-pressed', scannerPOSActivo ? 'true' : 'false');
            const conectado = scannerPOSActivo && scannerMovilConectado;
            boton.classList.toggle('connected', conectado);
            boton.classList.toggle('scanner-disabled', !scannerPOSActivo);
            boton.classList.toggle('scanner-waiting', scannerPOSActivo && !conectado);
            boton.title = scannerPOSActivo ? 'Desactivar escáner' : 'Habilitar escáner';
            const texto = document.getElementById('statusText');
            if (texto) {
                texto.textContent = !scannerPOSActivo
                    ? 'Habilitar escáner'
                    : conectado
                        ? 'Cámara conectada'
                        : 'Escáner pendiente de prueba';
            }
        }

        function alternarEscanerPOS() {
            const formData = new FormData();
            formData.append('token', TOKEN_SESION);
            formData.append('scanner_activo', scannerPOSActivo ? '0' : '1');
            const boton = document.getElementById('statusBadge');
            if (boton) boton.disabled = true;
            fetch('index.php?action=pos_estado_sesion', { method: 'POST', body: formData })
                .then(res => res.json())
                .then(data => {
                    if (data.status !== 'success') throw new Error(data.message || 'No se pudo cambiar el estado del escáner.');
                    actualizarBotonEstadoEscaner(data.scanner_activo);
                    if (data.scanner_activo) {
                        abrirModalConexionEscaner();
                    } else {
                        scannerMovilConectado = false;
                        scannerLectorVerificado = false;
                        actualizarBotonEstadoEscaner(false);
                        actualizarEstadoConexionEscaner({
                            estado_conexion: 'esperando',
                            dispositivo_info: ''
                        });
                        mostrarNotificacionCaja('Escáner desactivado');
                    }
                })
                .catch(error => {
                    console.error('Error al cambiar estado del escáner:', error);
                    alert(error.message || 'No se pudo actualizar el estado del escáner.');
                })
                .finally(() => { if (boton) boton.disabled = false; });
        }

        function consultarEstadoEscaner() {
            fetch(`index.php?action=pos_estado_sesion&token=${encodeURIComponent(TOKEN_SESION)}`)
                .then(res => res.json())
                .then(data => {
                    if (data.status === 'success' && typeof data.scanner_activo === 'boolean') {
                        actualizarBotonEstadoEscaner(data.scanner_activo);
                    }
                    if (data.status === 'success') actualizarEstadoConexionEscaner(data);
                })
                .catch(error => console.error('Error al consultar el estado del escáner:', error));
        }

        function actualizarEstadoConexionEscaner(data) {
            const texto = document.getElementById('scannerConnectionStatusText');
            const estado = document.getElementById('scannerConnectionStatus');
            if (!texto || !estado) return;
            const conectado = data.estado_conexion === 'conectado';
            const dispositivo = String(data.dispositivo_info || '');
            const modo = dispositivo
                ? (dispositivo.toLowerCase().includes('usb') ? 'Cable USB' : 'Bluetooth')
                : (scannerTransport === 'usb' ? 'Cable USB' : 'Bluetooth');
            scannerMovilConectado = conectado;
            scannerLectorVerificado = conectado && dispositivo.toLowerCase().includes('probado');
            actualizarBotonEstadoEscaner(scannerPOSActivo);
            estado.classList.toggle('is-connected', conectado);
            texto.textContent = conectado
                ? 'Cámara conectada'
                : `Esperando lector móvil · ${scannerTransport === 'usb' ? 'Cable USB' : 'Bluetooth'}`;
        }

        function seleccionarTipoConexion(tipo) {
            if (tipo !== 'bluetooth' && tipo !== 'usb') return;
            scannerTransport = tipo;
            const bluetooth = tipo === 'bluetooth';
            const botonBluetooth = document.getElementById('btnModeBluetooth');
            const botonUsb = document.getElementById('btnModeUsb');
            const botonUsbAuto = document.getElementById('btnOpenScannerUsb');
            const botonLink = document.getElementById('btnLinkEscanerMovil');

            botonBluetooth?.classList.toggle('active', bluetooth);
            botonBluetooth?.setAttribute('aria-pressed', String(bluetooth));
            botonUsb?.classList.toggle('active', !bluetooth);
            botonUsb?.setAttribute('aria-pressed', String(!bluetooth));
            if (botonUsbAuto) botonUsbAuto.hidden = bluetooth;
            if (scannerFullUrl) {
                const url = new URL(scannerFullUrl);
                url.searchParams.set('transporte', tipo);
                if (botonLink) botonLink.href = url.toString();
                actualizarQrEscaner(url.toString());
            }
            actualizarEstadoConexionEscaner({ estado_conexion: estadoActualConexion, dispositivo_info: '' });
        }

        function actualizarQrEscaner(url) {
            const qrContainer = document.getElementById('qrcode');
            if (!qrContainer) return;
            qrContainer.innerHTML = '';
            if (typeof QRCode === 'undefined') {
                qrContainer.textContent = 'Usa “Abrir lector móvil”.';
                return;
            }
            new QRCode(qrContainer, {
                text: url,
                width: 144,
                height: 144,
                colorDark: '#0f172a',
                colorLight: '#ffffff',
                correctLevel: QRCode.CorrectLevel.M
            });
        }

        function inicializarPOS() {
            scannerFullUrl = window.location.origin + window.location.pathname + `?action=pos_escaner_movil&token=${encodeURIComponent(TOKEN_SESION)}`;
            const linkEscaner = document.getElementById('btnLinkEscanerMovil');
            const tokenLabel = document.getElementById('lblTokenSesion');
            if (linkEscaner) linkEscaner.href = scannerFullUrl;
            if (tokenLabel) tokenLabel.textContent = TOKEN_SESION.substring(0, 12) + '...';

            const renderizarQR = () => {
                seleccionarTipoConexion(scannerTransport);
            };

            if (typeof QRCode === 'undefined') {
                const qrContainer = document.getElementById('qrcode');
                if (qrContainer) qrContainer.textContent = 'Usa “Abrir lector móvil”.';
                if (!window.posQrLoading) {
                    window.posQrLoading = true;
                    const qrScript = document.createElement('script');
                    qrScript.src = 'https://cdnjs.cloudflare.com/ajax/libs/qrcodejs/1.0.0/qrcode.min.js';
                    qrScript.onload = () => {
                        window.posQrLoading = false;
                        renderizarQR();
                    };
                    qrScript.onerror = () => { window.posQrLoading = false; };
                    document.head.appendChild(qrScript);
                }
            } else {
                renderizarQR();
            }

            document.getElementById('btnEntrarCaja')?.addEventListener('click', omitirLockQR);
            document.getElementById('btnCerrarConexionEscaner')?.addEventListener('click', cerrarModalConexionEscaner);
            const clienteDniInput = document.getElementById('inputClienteDni');
            if (clienteDniInput) {
                clienteDniInput.addEventListener('focus', function () {
                    if (this.value === '00000000' || this.value === '99999999') this.select();
                });
                clienteDniInput.addEventListener('input', function () {
                    this.value = this.value.replace(/\D/g, '').slice(0, 8);
                });
            }
            const modalDniInput = document.getElementById('inputModalClienteDni');
            if (modalDniInput) {
                modalDniInput.addEventListener('focus', function () { this.select(); });
                modalDniInput.addEventListener('input', function () {
                    this.value = this.value.replace(/\D/g, '').slice(0, 8);
                });
                modalDniInput.addEventListener('keydown', (e) => {
                    if (e.key === 'Enter') {
                        e.preventDefault();
                        consultarDniModal();
                    }
                });
            }
            document.getElementById('inputModalClienteNombre')?.addEventListener('keydown', (e) => {
                if (e.key === 'Enter') {
                    e.preventDefault();
                    guardarClienteModal();
                }
            });

            verificarEstadoLock();
            consultarEstadoEscaner();
            iniciarPollingCaja();
            iniciarPollingEscaneosMovil();
            cargarItemsPOS();
            try {
                ultimoTicketData = JSON.parse(sessionStorage.getItem('caserita_pos_ultimo_ticket') || 'null');
                document.getElementById('btnImprimirVentaAnterior').disabled = !ultimoTicketData;
                document.getElementById('btnImprimirVentaAnterior').style.opacity = ultimoTicketData ? '1' : '.55';
            } catch (error) {
                ultimoTicketData = null;
            }

            
            document.addEventListener('keydown', (e) => {
                if (e.key === 'F2') {
                    e.preventDefault();
                    document.getElementById('inputBuscarManual').focus();
                } else if (e.key === 'F9') {
                    e.preventDefault();
                    procesarVentaCaja();
                }
            });

            document.addEventListener('wheel', (e) => {
                const target = e.target;
                const input = target && target.closest ? target.closest('input[type="number"]') : null;
                if (input && document.activeElement === input) {
                    e.preventDefault();
                    input.blur();
                }
            }, { passive: false });

            // Buffer inteligente global para pistolas lectoras de código de barras USB
            let barcodeGunBuffer = '';
            let lastKeypressTime = 0;

            window.addEventListener('keydown', (e) => {
                const now = Date.now();
                const diff = now - lastKeypressTime;
                lastKeypressTime = now;

                const activeEl = document.activeElement;
                const tagName = activeEl ? activeEl.tagName : '';
                const isGeneralInput = (tagName === 'INPUT' || tagName === 'TEXTAREA') && activeEl.id !== 'inputBuscarManual';

                // Si se presiona Enter y hay caracteres acumulados en el buffer
                if (e.key === 'Enter') {
                    if (barcodeGunBuffer.length >= 3) {
                        const codigoLeido = barcodeGunBuffer.trim();
                        barcodeGunBuffer = '';
                        if (scannerPOSActivo && codigoLeido) {
                            e.preventDefault();
                            escanearCodigo(codigoLeido);
                            const searchBox = document.getElementById('inputBuscarManual');
                            if (searchBox) {
                                searchBox.value = '';
                                document.getElementById('posSearchDropdown')?.classList.remove('active');
                            }
                            return;
                        }
                    }
                    barcodeGunBuffer = '';
                    return;
                }

                // Si el tiempo entre teclas supera 90ms, fue tipeo humano -> limpiar buffer
                if (diff > 90) {
                    barcodeGunBuffer = '';
                }

                // Capturar caracteres imprimibles si no está en un input especial (o está en el buscador del POS)
                if (e.key.length === 1 && !e.ctrlKey && !e.altKey && !e.metaKey) {
                    if (scannerPOSActivo && !isGeneralInput) {
                        barcodeGunBuffer += e.key;
                    }
                }
            });

            setupSearchManual();
        }

        if (document.readyState === 'loading') {
            document.addEventListener('DOMContentLoaded', inicializarPOS, { once: true });
        } else {
            inicializarPOS();
        }

        var previoItemsCount = -1;
        var previoTotal = -1;
        var previaFirmaItems = '';
        var itemsVentaActual = [];
        var audioPosCtx = null;

        function playDingSound() {
            try {
                if (!audioPosCtx) {
                    audioPosCtx = new (window.AudioContext || window.webkitAudioContext)();
                }
                if (audioPosCtx.state === 'suspended') {
                    audioPosCtx.resume();
                }
                const osc = audioPosCtx.createOscillator();
                const gain = audioPosCtx.createGain();
                osc.connect(gain);
                gain.connect(audioPosCtx.destination);
                osc.type = 'sine';
                osc.frequency.setValueAtTime(1760, audioPosCtx.currentTime);
                osc.frequency.setValueAtTime(2637, audioPosCtx.currentTime + 0.08);
                gain.gain.setValueAtTime(0.25, audioPosCtx.currentTime);
                gain.gain.exponentialRampToValueAtTime(0.001, audioPosCtx.currentTime + 0.22);
                osc.start();
                osc.stop(audioPosCtx.currentTime + 0.23);
            } catch(e) {}
        }

        function mostrarNotificacionCaja(msg) {
            let toast = document.getElementById('posSyncToast');
            if (!toast) {
                toast = document.createElement('div');
                toast.id = 'posSyncToast';
                toast.style.cssText = 'position:fixed; top:20px; right:20px; background:#15803D; color:#fff; padding:12px 20px; border-radius:12px; font-weight:700; font-size:0.92rem; z-index:999999; box-shadow:0 8px 25px rgba(0,0,0,0.25); display:flex; align-items:center; gap:10px; transition:all 0.3s; transform:translateY(-50px); opacity:0; pointer-events:none;';
                document.body.appendChild(toast);
            }
            toast.innerHTML = `<i class="fa-solid fa-mobile-screen-button" style="font-size:1.2rem; color:#ffca3a;"></i> ${escapeHtml(msg)}`;
            toast.style.transform = 'translateY(0)';
            toast.style.opacity = '1';
            clearTimeout(window.toastSyncTimer);
            window.toastSyncTimer = setTimeout(() => {
                toast.style.transform = 'translateY(-50px)';
                toast.style.opacity = '0';
            }, 2500);
        }

        function omitirLockQR() {
            modalConexionSolicitado = false;
            accesoCajaPermitido = true;
            verificarEstadoLock();
        }

        function establecerModalConexionVisible(visible) {
            const modal = document.getElementById('modalLockQR');
            if (!modal) return;
            modal.classList.toggle('active', visible);
            modal.style.setProperty('transition', 'none', 'important');
            modal.style.setProperty('opacity', visible ? '1' : '0', 'important');
            modal.style.setProperty('visibility', visible ? 'visible' : 'hidden', 'important');
        }

        function abrirModalConexionEscaner() {
            modalConexionSolicitado = true;
            establecerModalConexionVisible(true);
        }

        function cerrarModalConexionEscaner() {
            modalConexionSolicitado = false;
            accesoCajaPermitido = true;
            establecerModalConexionVisible(false);
        }

        function abrirLectorEnCelular() {
            const boton = document.getElementById('btnOpenScannerUsb');
            if (boton) boton.disabled = true;
            const formData = new FormData();
            formData.append('token', TOKEN_SESION);
            formData.append('transporte', scannerTransport);
            fetch('index.php?action=pos_abrir_lector_celular', { method: 'POST', body: formData })
                .then(res => res.json())
                .then(data => {
                    if (data.ok) {
                        mostrarNotificacionCaja('Lector abierto en el celular; permite el acceso a la cámara si te lo solicita.');
                    } else {
                        mostrarErrorConexionEscaner(data.mensaje || 'ADB no pudo conectar el teléfono. Desbloquéalo y acepta la depuración USB.');
                    }
                })
                .catch(error => {
                    console.error('Error al abrir el lector por USB:', error);
                    mostrarErrorConexionEscaner('No se pudo comunicar con ADB. Verifica el cable USB, desbloquea el teléfono y acepta la solicitud de depuración USB.');
                })
                .finally(() => { if (boton) boton.disabled = false; });
        }

        function mostrarErrorConexionEscaner(mensaje) {
            const overlay = document.getElementById('scannerConnectionError');
            const texto = document.getElementById('scannerErrorMessage');
            if (!overlay || !texto) return;
            texto.textContent = mensaje;
            overlay.classList.add('active');
            overlay.setAttribute('aria-hidden', 'false');
            document.getElementById('scannerErrorTitle')?.focus();
        }

        function cerrarErrorConexionEscaner() {
            const overlay = document.getElementById('scannerConnectionError');
            if (!overlay) return;
            overlay.classList.remove('active');
            overlay.setAttribute('aria-hidden', 'true');
        }

        function verificarEstadoLock() {
            const modal = document.getElementById('modalLockQR');
            const badge = document.getElementById('statusBadge');

            if (estadoActualConexion === 'conectado') {
                accesoCajaPermitido = true;
                establecerModalConexionVisible(modalConexionSolicitado);
            } else {
                establecerModalConexionVisible(!accesoCajaPermitido || modalConexionSolicitado);
            }
            actualizarBotonEstadoEscaner(scannerPOSActivo);
        }

        // Polling constante a 400ms para actualización en tiempo real desde el celular
        function iniciarPollingCaja() {
            if (window.posPollingTimer) clearInterval(window.posPollingTimer);
            window.posPollingTimer = setInterval(() => {
                if (!document.getElementById('posTableBody')) {
                    clearInterval(window.posPollingTimer);
                    window.posPollingTimer = null;
                    return;
                }
                fetch(`index.php?action=pos_estado_sesion&token=${TOKEN_SESION}`)
                    .then(res => res.json())
                    .then(data => {
                        if (data.status === 'success') {
                        scannerDestinoActual = data.scanner_destino === 'campo' ? 'campo' : 'pos';
                        if (typeof data.scanner_activo === 'boolean' && data.scanner_activo !== scannerPOSActivo) {
                            actualizarBotonEstadoEscaner(data.scanner_activo);
                        }
                        if (data.estado_conexion !== estadoActualConexion) {
                                estadoActualConexion = data.estado_conexion;
                                verificarEstadoLock();
                            }

                            actualizarEstadoConexionEscaner(data);
                            const items = data.items || [];
                            const total = parseFloat(data.total_venta || 0);

                            // Detectar si el celular agregó un nuevo producto
                            if (previoTotal !== -1 && (items.length > previoItemsCount || total > previoTotal)) {
                                playDingSound();
                                const ultimo = items[items.length - 1];
                                if (ultimo) {
                                    mostrarNotificacionCaja(`📱 Agregado desde Celular: ${ultimo.nombre || ultimo.titulo || ('Producto #' + ultimo.id_producto)} (x${ultimo.cantidad})`);
                                }
                            }
                            previoItemsCount = items.length;
                            previoTotal = total;

                            const firmaItems = JSON.stringify(items) + '|' + total;
                            if (firmaItems !== previaFirmaItems) {
                                renderizarTablaItems(items, total);
                            }
                        }
                    })
                    .catch(err => console.error('Error polling POS:', err));
            }, 400);
        }

        function cargarItemsPOS() {
            fetch(`index.php?action=pos_estado_sesion&token=${TOKEN_SESION}`)
                .then(res => res.json())
                .then(data => {
                    if (data.status === 'success') {
                        const items = data.items || [];
                        const total = parseFloat(data.total_venta || 0);
                        previoItemsCount = items.length;
                        previoTotal = total;
                        renderizarTablaItems(items, total);
                    }
                });
        }

            function iniciarPollingEscaneosMovil() {
                if (window.posScanQueueTimer) clearInterval(window.posScanQueueTimer);
                window.posScanQueueTimer = setInterval(() => {
                    if (!document.getElementById('posTableBody')) {
                        clearInterval(window.posScanQueueTimer);
                        window.posScanQueueTimer = null;
                        return;
                    }
                    if (scannerDestinoActual === 'campo') return;
                    if (window.posScanQueueBusy) return;
                    window.posScanQueueBusy = true;
                    fetch(`index.php?action=pos_escaneos_pendientes&token=${encodeURIComponent(TOKEN_SESION)}`)
                        .then(res => res.json())
                        .then(async res => {
                            if (res.status === 'success' && Array.isArray(res.data)) {
                                for (const item of res.data) {
                                    if (item.codigo_escaneado) await procesarEscaneoMovil(item.codigo_escaneado);
                                }
                            }
                        })
                        .catch(error => console.error('Error leyendo la cola temporal del escáner:', error))
                        .finally(() => { window.posScanQueueBusy = false; });
                }, 500);
            }

            function procesarEscaneoMovil(codigo) {
                const campo = document.activeElement;
                const esCampoTexto = campo && /^(INPUT|TEXTAREA)$/.test(campo.tagName)
                    && !campo.disabled && !campo.readOnly
                    && (campo.tagName === 'TEXTAREA' || !['button', 'submit', 'reset', 'checkbox', 'radio', 'file', 'range', 'color', 'hidden', 'image'].includes(campo.type));

                if (campo?.id === 'inputBuscarManual') {
                    campo.value = codigo;
                    campo.dispatchEvent(new Event('input', { bubbles: true }));
                    return escanearCodigo(codigo).finally(() => {
                        campo.value = '';
                        campo.dispatchEvent(new Event('input', { bubbles: true }));
                        document.getElementById('posSearchDropdown')?.classList.remove('active');
                    });
                }

                if (esCampoTexto) {
                    campo.value = codigo;
                    campo.dispatchEvent(new Event('input', { bubbles: true }));
                    campo.dispatchEvent(new Event('change', { bubbles: true }));
                    return Promise.resolve();
                }

                return escanearCodigo(codigo);
            }

        function renderizarTablaItems(items, total) {
            totalGeneralVenta = parseFloat(total || 0);
            itemsVentaActual = Array.isArray(items) ? items : [];
            previaFirmaItems = JSON.stringify(itemsVentaActual) + '|' + totalGeneralVenta;
            const tbody = document.getElementById('posTableBody');
            if (!tbody) return;

            if (!items || items.length === 0) {
                tbody.innerHTML = `
                    <tr>
                        <td colspan="6" style="text-align:center; padding: 40px 10px; color:#64748b;">
                            <i class="fa-solid fa-barcode" style="font-size: 2.5rem; margin-bottom:10px; display:block;"></i>
                            Escanea un código de barras con la cámara o busca un producto manualmente.
                        </td>
                    </tr>`;
                actualizarTotalesUI(0);
                reiniciarPagos();
                return;
            }

            let html = '';
            items.forEach(item => {
                const codigoItem = item.codigo || item.codigo_barras || ('ID: ' + item.id_producto);
                const nombreItem = item.nombre || item.titulo || ('Producto #' + item.id_producto);
                const imagenItem = String(item.imagen || item.imagen_url || '').trim();
                const imagenSrc = imagenItem ? escapeHtml(imagenItem) : 'Images/logo.png';
                const precioUnit = parseFloat(item.precio) || 0;
                const precioAnterior = parseFloat(item.precio_anterior) || 0;
                const tieneDescuento = precioAnterior > precioUnit && precioUnit > 0;
                const pctDescuento = tieneDescuento ? Math.round(((precioAnterior - precioUnit) / precioAnterior) * 100) : 0;
                html += `
                    <tr>
                        <td><code style="color:var(--pos-primary); font-weight:700;">${escapeHtml(String(codigoItem))}</code></td>
                        <td>
                            <div class="pos-product-item-cell">
                                <div class="pos-product-img-box">
                                    <img src="${imagenSrc}" alt="${escapeHtml(nombreItem)}"${imagenItem ? '' : ' class="pos-img-fallback"'}>
                                </div>
                                <div class="pos-product-meta">
                                    <strong class="pos-product-name">${escapeHtml(nombreItem)}</strong>
                                </div>
                            </div>
                        </td>
                        <td class="pos-price-cell">
                            ${tieneDescuento ? `<span class="pos-price-old">S/ ${precioAnterior.toFixed(2)}</span>` : ''}
                            <span class="pos-price-current">S/ ${precioUnit.toFixed(2)}</span>
                            ${tieneDescuento ? `<span class="pos-discount-badge">-${pctDescuento}%</span>` : ''}
                        </td>
                        <td>
                            <div class="pos-qty-control">
                                <button type="button" class="btn-qty" onclick="cambiarCantidad(${item.id_producto}, ${item.cantidad - 1})">-</button>
                                <span style="padding:0 6px; font-weight:700;">${item.cantidad}</span>
                                <button type="button" class="btn-qty" onclick="cambiarCantidad(${item.id_producto}, ${item.cantidad + 1})">+</button>
                            </div>
                        </td>
                        <td style="font-weight:800; color:var(--pos-primary);">S/ ${parseFloat(item.subtotal).toFixed(2)}</td>
                        <td style="text-align:center;">
                            <button type="button" class="btn-remove-item" onclick="cambiarCantidad(${item.id_producto}, 0)" title="Eliminar ítem">
                                <i class="fa-solid fa-trash-can"></i>
                            </button>
                        </td>
                    </tr>`;
            });

            tbody.innerHTML = html;
            actualizarTotalesUI(totalGeneralVenta);
        }

        function actualizarTotalesUI(total) {
            const igv = total * 0.18;
            const subtotal = total - igv;

            document.getElementById('lblSubtotal').textContent = `S/ ${subtotal.toFixed(2)}`;
            document.getElementById('lblIgv').textContent = `S/ ${igv.toFixed(2)}`;
            document.getElementById('lblTotal').textContent = `S/ ${total.toFixed(2)}`;

            actualizarResumenPagos();
        }

        function cambiarCantidad(idProducto, nuevaCant) {
            const formData = new FormData();
            formData.append('token', TOKEN_SESION);
            formData.append('id_producto', idProducto);
            formData.append('cantidad', nuevaCant);

            fetch('index.php?action=pos_actualizar_cantidad', {
                method: 'POST',
                body: formData
            })
            .then(res => res.json())
            .then(data => {
                if (data.status === 'success' && data.data) {
                    renderizarTablaItems(data.data.items, data.data.total);
                } else {
                    mostrarAvisoProductoPOS(data.message || 'No se pudo actualizar la cantidad.');
                }
            })
            .catch(() => mostrarAvisoProductoPOS('Error de conexión al actualizar la cantidad.'));
        }

        function getPagoInput(metodo) {
            if (metodo === 'efectivo') return document.getElementById('payMontoEfectivo');
            if (metodo === 'tarjeta') return document.getElementById('payMontoTarjeta');
            if (metodo === 'yape') return document.getElementById('payMontoYape');
            return null;
        }

        function leerPagos() {
            const metodos = ['efectivo', 'tarjeta', 'yape'];
            const pagos = [];
            metodos.forEach(function (m) {
                const input = getPagoInput(m);
                const val = parseFloat(input ? input.value : 0) || 0;
                if (val > 0) {
                    pagos.push({ metodo: m, monto: Math.round(val * 100) / 100 });
                }
            });
            return pagos;
        }

        function sumaPagos(pagos) {
            const list = pagos || leerPagos();
            const sum = list.reduce(function (acc, p) { return acc + (parseFloat(p.monto) || 0); }, 0);
            return Math.round(sum * 100) / 100;
        }

        function actualizarResumenPagos() {
            const total = Math.round(totalGeneralVenta * 100) / 100;
            const asignado = sumaPagos();

            const elTotal = document.getElementById('payTotal');
            const elAsignado = document.getElementById('payAsignado');
            const wrap = document.getElementById('posPaySummary');

            if (elTotal) elTotal.textContent = `S/ ${total.toFixed(2)}`;
            if (elAsignado) elAsignado.textContent = `S/ ${asignado.toFixed(2)}`;

            if (wrap) {
                if (total > 0 && Math.abs(asignado - total) < 0.01) {
                    wrap.classList.add('is-ok');
                    wrap.classList.remove('is-warn');
                } else if (asignado > total) {
                    wrap.classList.add('is-warn');
                    wrap.classList.remove('is-ok');
                } else {
                    wrap.classList.remove('is-ok', 'is-warn');
                }
            }

            const ef = getPagoInput('efectivo');
            const efVal = parseFloat(ef ? ef.value : 0) || 0;
            const boxEfectivo = document.getElementById('boxMontoRecibido');
            if (boxEfectivo) {
                if (efVal > 0) {
                    boxEfectivo.classList.remove('is-off');
                } else {
                    boxEfectivo.classList.add('is-off');
                }
            }
            calcularVueltas();
            actualizarBotonesLimpiarPago();
        }

        function actualizarBotonesLimpiarPago() {
            document.querySelectorAll('.pos-pay-clear').forEach(function (btn) {
                const input = getPagoInput(btn.getAttribute('data-method'));
                const valor = parseFloat(input && input.value ? input.value : 0) || 0;
                btn.hidden = valor <= 0;
            });
        }

        function sincronizarEfectivoRecibido() {
            const ef = getPagoInput('efectivo');
            const aplicado = Math.round((parseFloat(ef && ef.value ? ef.value : 0) || 0) * 100) / 100;
            const rec = document.getElementById('inputMontoRecibido');
            if (!rec) return;

            if (aplicado <= 0) {
                rec.value = '';
                return;
            }

            const recibido = parseFloat(rec.value || 0) || 0;
            if (!rec.value || recibido < aplicado) {
                rec.value = aplicado.toFixed(2);
            }
        }

        function onPagoInput(metodo) {
            if (metodo === 'efectivo') {
                sincronizarEfectivoRecibido();
            }
            actualizarResumenPagos();
        }

        function onPagoFocus(metodo) {
            // Callback opcional de foco
        }

        function autocompletarPago(metodo) {
            const total = Math.round(totalGeneralVenta * 100) / 100;
            const input = getPagoInput(metodo);
            if (!input) return;

            const otros = ['efectivo', 'tarjeta', 'yape'].filter(function (m) { return m !== metodo; });
            let sumaOtros = 0;
            otros.forEach(function (m) {
                const inp = getPagoInput(m);
                sumaOtros += parseFloat(inp ? inp.value : 0) || 0;
            });

            const saldo = Math.max(0, Math.round((total - sumaOtros) * 100) / 100);
            input.value = saldo > 0 ? saldo.toFixed(2) : '';
            if (metodo === 'efectivo') {
                sincronizarEfectivoRecibido();
            }
            actualizarResumenPagos();
        }

        function limpiarPago(metodo) {
            const input = getPagoInput(metodo);
            if (!input) return;
            input.value = '';
            if (metodo === 'efectivo') {
                const rec = document.getElementById('inputMontoRecibido');
                if (rec) rec.value = '';
            }
            onPagoInput(metodo);
            input.focus();
        }

        function abrirModalYapePlin() {
            document.getElementById('modalYapePlinPOS').style.display = 'flex';
        }

        function cerrarModalYapePlin() {
            document.getElementById('modalYapePlinPOS').style.display = 'none';
        }

        function calcularVueltas() {
            const total = Math.round(totalGeneralVenta * 100) / 100;
            const asignado = sumaPagos();
            const falta = Math.max(0, Math.round((total - asignado) * 100) / 100);
            const exceso = Math.max(0, Math.round((asignado - total) * 100) / 100);

            const ef = getPagoInput('efectivo');
            const aplicado = Math.round((parseFloat(ef && ef.value ? ef.value : 0) || 0) * 100) / 100;
            const rec = document.getElementById('inputMontoRecibido');
            const recibido = parseFloat(rec && rec.value ? rec.value : 0) || 0;
            const cambio = Math.max(0, Math.round((recibido - aplicado) * 100) / 100);
            const faltaEfectivo = Math.max(0, Math.round((aplicado - recibido) * 100) / 100);

            const elResultado = document.getElementById('payResultado');
            const lblResultado = document.getElementById('lblResultadoPago');
            const wrapResultado = document.getElementById('payResultadoWrap');
            if (!elResultado || !wrapResultado) return;

            wrapResultado.classList.remove('is-falta', 'is-vuelto', 'is-exceso');

            if (falta > 0.009) {
                if (lblResultado) lblResultado.textContent = 'Falta';
                elResultado.textContent = `S/ ${falta.toFixed(2)}`;
                wrapResultado.classList.add('is-falta');
            } else if (exceso > 0.009) {
                if (lblResultado) lblResultado.textContent = 'Exceso';
                elResultado.textContent = `S/ ${exceso.toFixed(2)}`;
                wrapResultado.classList.add('is-exceso');
            } else if (faltaEfectivo > 0.009) {
                if (lblResultado) lblResultado.textContent = 'Falta';
                elResultado.textContent = `S/ ${faltaEfectivo.toFixed(2)}`;
                wrapResultado.classList.add('is-falta');
            } else {
                if (lblResultado) lblResultado.textContent = 'Vuelto';
                elResultado.textContent = `S/ ${cambio.toFixed(2)}`;
                wrapResultado.classList.add('is-vuelto');
            }
        }

        function reiniciarPagos() {
            ['efectivo', 'tarjeta', 'yape'].forEach(function (m) {
                const el = getPagoInput(m);
                if (el) el.value = '';
            });
            const rec = document.getElementById('inputMontoRecibido');
            if (rec) rec.value = '';
            const box = document.getElementById('boxMontoRecibido');
            if (box) box.classList.add('is-off');
            actualizarResumenPagos();
        }

        function setClienteGenerico() {
            clienteDniActual = '00000000';
            clienteNombreActual = 'Público General';
            document.getElementById('inputClienteDni').value = '00000000';
            document.getElementById('lblClienteNombre').textContent = 'Público General';
            document.getElementById('lblClienteNombre').style.color = '#0f172a';
        }

        function consultarDniCajero() {
            const input = document.getElementById('inputClienteDni');
            const dni = input.value.trim();
            const icon = document.getElementById('iconBuscarDni');
            const lbl = document.getElementById('lblClienteNombre');

            if (!dni || dni === '00000000' || dni === '99999999') {
                setClienteGenerico();
                return;
            }

            if (!/^\d{8}$/.test(dni)) {
                mostrarAvisoValidacionCaja('Ingresa un DNI de 8 dígitos.');
                return;
            }

            icon.className = 'fa-solid fa-spinner fa-spin';
            clienteDniActual = dni;
            clienteNombreActual = '';
            lbl.textContent = 'Consultando RENIEC...';
            lbl.style.color = 'var(--pos-primary)';

            fetch(`index.php?action=pos_consultar_dni&documento=${encodeURIComponent(dni)}`)
                .then(res => res.json())
                .then(data => {
                    icon.className = 'fa-solid fa-magnifying-glass';
                    if (data && data.ok) {
                        clienteDniActual = data.dni || dni;
                        const nombreEncontrado = data.nombre_completo || data.razon_social || data.nombre || '';
                        if (nombreEncontrado.trim().length > 0) {
                            clienteNombreActual = nombreEncontrado.trim();
                            lbl.textContent = clienteNombreActual;
                            lbl.style.color = '#15803d';
                        } else {
                            lbl.textContent = data.mensaje || 'No se encontraron datos. Ingresa el nombre manualmente.';
                            lbl.style.color = '#d97706';
                            setTimeout(() => { editarNombreClienteManual(); }, 300);
                        }
                    } else {
                        lbl.textContent = data.mensaje || 'No se encontró información en RENIEC.';
                        lbl.style.color = '#dc2626';
                        setTimeout(() => { editarNombreClienteManual(); }, 300);
                    }
                })
                .catch(err => {
                    console.error('Error al consultar DNI:', err);
                    icon.className = 'fa-solid fa-magnifying-glass';
                    lbl.textContent = 'Error de conexión. Ingrese nombre manual.';
                    lbl.style.color = '#dc2626';
                    setTimeout(() => { editarNombreClienteManual(); }, 300);
                });
        }

        function editarNombreClienteManual() {
            abrirModalCliente();
        }

        function abrirModalCliente() {
            const modal = document.getElementById('posClientModal');
            const dniInput = document.getElementById('inputModalClienteDni');
            const nombreInput = document.getElementById('inputModalClienteNombre');
            const estado = document.getElementById('lblModalClienteEstado');
            if (!modal || !dniInput || !nombreInput || !estado) return;

            dniInput.value = ['00000000', '99999999'].includes(clienteDniActual) ? '' : clienteDniActual;
            nombreInput.value = clienteNombreActual === 'Público General' ? '' : clienteNombreActual;
            estado.textContent = '';
            estado.className = 'pos-client-status';

            modal.classList.add('active');
            modal.setAttribute('aria-hidden', 'false');
            setTimeout(function () { dniInput.focus(); }, 60);
        }

        function cerrarModalCliente() {
            const modal = document.getElementById('posClientModal');
            if (!modal) return;
            modal.classList.remove('active');
            modal.setAttribute('aria-hidden', 'true');
        }

        function setEstadoModalCliente(mensaje, tipo) {
            const estado = document.getElementById('lblModalClienteEstado');
            if (!estado) return;
            estado.textContent = mensaje || '';
            estado.className = 'pos-client-status' + (tipo ? ' is-' + tipo : '');
        }

        function consultarDniModal() {
            const dniInput = document.getElementById('inputModalClienteDni');
            const nombreInput = document.getElementById('inputModalClienteNombre');
            const dni = dniInput.value.trim();
            const icon = document.querySelector('.pos-client-search-btn i');
            if (!/^\d{8}$/.test(dni) || dni === '00000000' || dni === '99999999') {
                setEstadoModalCliente('Ingresa un DNI de 8 dígitos.', 'error');
                dniInput.focus();
                return;
            }

            if (icon) icon.className = 'fa-solid fa-spinner fa-spin';
            setEstadoModalCliente('Consultando RENIEC...', '');

            fetch(`index.php?action=pos_consultar_dni&documento=${encodeURIComponent(dni)}`)
                .then(res => res.json())
                .then(data => {
                    if (icon) icon.className = 'fa-solid fa-magnifying-glass';
                    if (data && data.ok) {
                        const nombreEncontrado = String(data.nombre_completo || data.nombre || '').trim();
                        if (nombreEncontrado !== '') {
                            nombreInput.value = nombreEncontrado;
                            setEstadoModalCliente('DNI encontrado en RENIEC.', 'ok');
                        } else {
                            setEstadoModalCliente(data.mensaje || 'No se encontró el nombre. Escríbelo manualmente.', 'error');
                            nombreInput.focus();
                        }
                    } else {
                        setEstadoModalCliente(data.mensaje || 'No se encontró información en RENIEC.', 'error');
                        nombreInput.focus();
                    }
                })
                .catch(err => {
                    console.error('Error al consultar DNI:', err);
                    if (icon) icon.className = 'fa-solid fa-magnifying-glass';
                    setEstadoModalCliente('Error de conexión. Escribe el nombre manualmente.', 'error');
                    nombreInput.focus();
                });
        }

        function guardarClienteModal() {
            const dniInput = document.getElementById('inputModalClienteDni');
            const nombreInput = document.getElementById('inputModalClienteNombre');
            const dni = dniInput.value.trim();
            const nombre = nombreInput.value.trim();

            if (dni !== '' && !/^\d{8}$/.test(dni)) {
                setEstadoModalCliente('El DNI debe tener 8 dígitos.', 'error');
                dniInput.focus();
                return;
            }
            if (nombre === '') {
                setEstadoModalCliente('Ingresa o consulta el nombre del cliente.', 'error');
                nombreInput.focus();
                return;
            }

            clienteDniActual = dni || '00000000';
            clienteNombreActual = nombre;
            document.getElementById('inputClienteDni').value = clienteDniActual;
            document.getElementById('lblClienteNombre').textContent = clienteNombreActual;
            document.getElementById('lblClienteNombre').style.color = '#0f172a';
            cerrarModalCliente();
        }

        var productosEnDropdown = [];
        var dropdownSelectedIndex = -1;

        function setupSearchManual() {
            const input = document.getElementById('inputBuscarManual');
            const dropdown = document.getElementById('posSearchDropdown');
            let debounceTimer = null;

            input.addEventListener('input', () => {
                const query = input.value.trim();
                clearTimeout(debounceTimer);
                dropdownSelectedIndex = -1;

                if (query.length < 1) {
                    productosEnDropdown = [];
                    dropdown.innerHTML = '';
                    dropdown.classList.remove('active');
                    return;
                }

                debounceTimer = setTimeout(() => {
                    fetch(`index.php?action=pos_buscar_productos&q=${encodeURIComponent(query)}`)
                        .then(res => res.json())
                        .then(data => {
                            if (data.status === 'success' && data.data && data.data.length > 0) {
                                productosEnDropdown = data.data;
                                renderDropdownItems(data.data);
                            } else {
                                productosEnDropdown = [];
                                dropdown.innerHTML = `
                                    <div class="pos-search-no-results" style="padding: 12px 16px; color: #64748b; font-size: 0.85rem; display: flex; align-items: center; gap: 8px;">
                                        <i class="fa-solid fa-circle-exclamation"></i> No se encontraron productos coincidentes
                                    </div>`;
                                dropdown.classList.add('active');
                            }
                        })
                        .catch(err => {
                            console.error('Error buscando productos:', err);
                            productosEnDropdown = [];
                        });
                }, 180);
            });

            input.addEventListener('keydown', (e) => {
                if (dropdown.classList.contains('active') && productosEnDropdown.length > 0) {
                    if (e.key === 'ArrowDown') {
                        e.preventDefault();
                        dropdownSelectedIndex = (dropdownSelectedIndex + 1) % productosEnDropdown.length;
                        actualizarHighlightDropdown();
                        return;
                    }
                    if (e.key === 'ArrowUp') {
                        e.preventDefault();
                        dropdownSelectedIndex = (dropdownSelectedIndex - 1 + productosEnDropdown.length) % productosEnDropdown.length;
                        actualizarHighlightDropdown();
                        return;
                    }
                    if (e.key === 'Escape') {
                        dropdown.classList.remove('active');
                        return;
                    }
                }

                if (e.key === 'Enter') {
                    e.preventDefault();
                    if (dropdown.classList.contains('active') && productosEnDropdown.length > 0) {
                        const targetIdx = dropdownSelectedIndex >= 0 ? dropdownSelectedIndex : 0;
                        seleccionarProductoPorIndice(targetIdx);
                        return;
                    }

                    const val = input.value.trim();
                    if (val !== '') {
                        escanearCodigo(val);
                        input.value = '';
                        dropdown.classList.remove('active');
                    }
                }
            });

            document.addEventListener('click', (e) => {
                if (!input.contains(e.target) && !dropdown.contains(e.target)) {
                    dropdown.classList.remove('active');
                }
            });
        }

        function actualizarHighlightDropdown() {
            const items = document.querySelectorAll('.pos-search-item');
            items.forEach((it, idx) => {
                if (idx === dropdownSelectedIndex) {
                    it.classList.add('selected');
                    it.style.background = '#dcfce7';
                    it.scrollIntoView({ block: 'nearest' });
                } else {
                    it.classList.remove('selected');
                    it.style.background = '';
                }
            });
        }

        function renderDropdownItems(productos) {
            const dropdown = document.getElementById('posSearchDropdown');
            let html = '';

            productos.forEach((prod, idx) => {
                const nombreProducto = prod.nombre || prod.titulo || ('Producto #' + prod.id_producto);
                const sku = prod.codigo || '';
                const codigoBarras = prod.codigo_barras || '';
                const codigos = [sku ? `SKU: ${sku}` : '', codigoBarras ? `Barras: ${codigoBarras}` : '']
                    .filter(Boolean).join(' · ') || `ID: ${prod.id_producto}`;
                const stock = prod.stock !== undefined ? parseInt(prod.stock) : 0;
                const imagenProducto = String(prod.imagen || prod.imagen_url || '').trim();
                const precio = parseFloat(prod.precio) || 0;
                const precioAnterior = parseFloat(prod.precio_anterior) || 0;
                const tieneDescuento = precioAnterior > precio && precio > 0;
                const pctDescuento = tieneDescuento ? Math.round(((precioAnterior - precio) / precioAnterior) * 100) : 0;
                const stockBadge = stock > 0
                    ? `<span class="pos-stock-badge in-stock">${stock} disponibles</span>`
                    : `<span class="pos-stock-badge out-stock">Agotado</span>`;

                html += `
                    <div class="pos-search-item" data-index="${idx}" onclick="seleccionarProductoPorIndice(${idx})">
                        <div class="pos-search-item-left">
                            ${imagenProducto
                                ? `<img class="pos-search-item-thumb" src="${escapeHtml(imagenProducto)}" alt="${escapeHtml(nombreProducto)}">`
                                : `<span class="pos-search-item-icon-fallback"><i class="fa-solid fa-box"></i></span>`}
                            <div class="pos-search-item-info">
                                <strong class="pos-search-item-name">${escapeHtml(nombreProducto)}</strong>
                                <div class="pos-search-item-meta"><span>${escapeHtml(codigos)}</span></div>
                            </div>
                        </div>
                        <div class="pos-search-item-price-stock">
                            ${tieneDescuento ? `<span class="pos-search-item-old-price">S/ ${precioAnterior.toFixed(2)}</span>` : ''}
                            <span class="pos-search-item-price">S/ ${precio.toFixed(2)}</span>
                            ${tieneDescuento ? `<span class="pos-search-discount-badge">-${pctDescuento}%</span>` : ''}
                            ${stockBadge}
                        </div>
                    </div>`;
            });

            dropdown.innerHTML = html;
            dropdown.classList.add('active');
        }

        function seleccionarProductoDropdown(idOcodigo) {
            const input = document.getElementById('inputBuscarManual');
            const dropdown = document.getElementById('posSearchDropdown');
            escanearCodigo(idOcodigo, true);
            input.value = '';
            dropdown.classList.remove('active');
            dropdownSelectedIndex = -1;
            productosEnDropdown = [];
            setTimeout(() => input.focus(), 80);
        }

        function seleccionarProductoPorIndice(index) {
            const producto = productosEnDropdown[index];
            if (!producto) return;
            seleccionarProductoDropdown(String(producto.id_producto));
        }

        function escapeHtml(str) {
            if (!str) return '';
            return String(str)
                .replace(/&/g, "&amp;")
                .replace(/</g, "&lt;")
                .replace(/>/g, "&gt;")
                .replace(/"/g, "&quot;")
                .replace(/'/g, "&#039;");
        }

            function mostrarAvisoProductoPOS(mensaje) {
                document.getElementById('posProductNotice')?.remove();
                const overlay = document.createElement('div');
                overlay.id = 'posProductNotice';
                overlay.className = 'pos-product-notice';
                overlay.setAttribute('role', 'alertdialog');
                overlay.setAttribute('aria-live', 'assertive');
                const panel = document.createElement('div');
                panel.className = 'pos-product-notice-panel';
                panel.tabIndex = 0;
                panel.innerHTML = '<i class="fa-solid fa-circle-exclamation" aria-hidden="true"></i><strong>Producto no encontrado</strong>';
                const detail = document.createElement('span');
                detail.textContent = mensaje || 'Revisa el código o el nombre e inténtalo de nuevo.';
                panel.appendChild(detail);
                overlay.appendChild(panel);
                document.body.appendChild(overlay);
                let remaining = 1800;
                let startedAt = Date.now();
                let removalTimer = window.setTimeout(() => overlay.remove(), remaining);
                const pauseNotice = () => {
                    if (!removalTimer) return;
                    window.clearTimeout(removalTimer);
                    removalTimer = null;
                    remaining = Math.max(0, remaining - (Date.now() - startedAt));
                };
                const resumeNotice = () => {
                    if (removalTimer || remaining <= 0) return;
                    startedAt = Date.now();
                    removalTimer = window.setTimeout(() => overlay.remove(), remaining);
                };
                panel.addEventListener('pointerenter', pauseNotice);
                panel.addEventListener('pointerleave', resumeNotice);
                panel.addEventListener('focusin', pauseNotice);
                panel.addEventListener('focusout', event => {
                    if (!panel.contains(event.relatedTarget)) resumeNotice();
                });
            }

        function escanearCodigo(codigo, esIdProducto = false) {
            const formData = new FormData();
            formData.append('token', TOKEN_SESION);
            formData.append(esIdProducto ? 'id_producto' : 'codigo_barras', codigo);

            return fetch('index.php?action=pos_escanear_producto', {
                method: 'POST',
                body: formData
            })
            .then(res => res.json())
            .then(data => {
                if (data.status === 'success') {
                    renderizarTablaItems(data.items, data.total);
                } else {
                    mostrarAvisoProductoPOS(data.message);
                }
            })
            .catch(() => mostrarAvisoProductoPOS('No se pudo consultar el producto. Revisa la conexión.'));
        }

        function mostrarAvisoValidacionCaja(mensaje) {
            const modal = document.getElementById('posValidationModal');
            const message = document.getElementById('posValidationMessage');
            if (!modal || !message) return;
            message.textContent = mensaje;
            modal.classList.add('active');
            modal.setAttribute('aria-hidden', 'false');
        }

        function cerrarAvisoValidacionCaja() {
            const modal = document.getElementById('posValidationModal');
            if (!modal) return;
            modal.classList.remove('active');
            modal.setAttribute('aria-hidden', 'true');
        }

        var cobroPendiente = null;

        function cerrarConfirmacionCobro() {
            const modal = document.getElementById('posConfirmModal');
            if (!modal) return;
            modal.classList.remove('active');
            modal.setAttribute('aria-hidden', 'true');
            cobroPendiente = null;
        }

        function cerrarModalActivoPOS() {
            if (document.getElementById('posClientModal')?.classList.contains('active')) return cerrarModalCliente();
            if (document.getElementById('posConfirmModal')?.classList.contains('active')) return cerrarConfirmacionCobro();
            if (document.getElementById('posValidationModal')?.classList.contains('active')) return cerrarAvisoValidacionCaja();
            if (document.getElementById('modalTicketVenta')?.style.display === 'flex') return cerrarTicketVenta();
            if (document.getElementById('modalYapePlinPOS')?.style.display === 'flex') return cerrarModalYapePlin();
            cerrarModalConexionEscaner();
        }

        function procesarVentaCaja() {
            if (totalGeneralVenta <= 0) {
                mostrarAvisoValidacionCaja('No hay productos en la orden de cobro.');
                return;
            }

            if (!['00000000', '99999999'].includes(clienteDniActual) && !clienteNombreActual.trim()) {
                mostrarAvisoValidacionCaja('Consulta el DNI o ingresa manualmente el nombre del cliente antes de cobrar.');
                editarNombreClienteManual();
                if (!clienteNombreActual.trim()) return;
            }

            const pagos = leerPagos();
            const total = Math.round(totalGeneralVenta * 100) / 100;
            const asignado = sumaPagos(pagos);

            if (pagos.length === 0) {
                mostrarAvisoValidacionCaja('Asigna el pago indicando el monto en al menos un método (Efectivo, Tarjeta o Yape).');
                return;
            }

            if (Math.abs(asignado - total) > 0.05) {
                mostrarAvisoValidacionCaja(`El pago asignado es S/ ${asignado.toFixed(2)} y el total de la venta es S/ ${total.toFixed(2)}. Ajusta los montos para que coincidan.`);
                return;
            }

            const pagoEfectivo = pagos.find(function (p) { return p.metodo === 'efectivo'; });
            let recibido = parseFloat(document.getElementById('inputMontoRecibido').value || 0) || 0;
            if (pagoEfectivo) {
                if (recibido < pagoEfectivo.monto) {
                    mostrarAvisoValidacionCaja(`La parte en efectivo es S/ ${pagoEfectivo.monto.toFixed(2)} y recibiste S/ ${recibido.toFixed(2)}. Ingresa un monto igual o mayor para continuar.`);
                    document.getElementById('inputMontoRecibido').focus();
                    return;
                }
            } else {
                recibido = total;
            }

            cobroPendiente = { pagos, total, recibido };
            document.getElementById('confirmCliente').textContent = clienteNombreActual;
            document.getElementById('confirmDocumento').textContent = `Documento: ${clienteDniActual}`;
            document.getElementById('confirmProductos').innerHTML = itemsVentaActual.map(function (item) {
                return `<div><span><strong>${Number(item.cantidad)} ×</strong> ${escapeHtml(item.nombre || item.titulo || ('Producto #' + item.id_producto))}</span><strong>S/ ${parseFloat(item.subtotal).toFixed(2)}</strong></div>`;
            }).join('');
            document.getElementById('confirmPagos').textContent = pagos.map(function (p) {
                return `${p.metodo.toUpperCase()} S/ ${p.monto.toFixed(2)}`;
            }).join(' + ');
            document.getElementById('confirmTotal').textContent = `S/ ${total.toFixed(2)}`;
            const modal = document.getElementById('posConfirmModal');
            modal.classList.add('active');
            modal.setAttribute('aria-hidden', 'false');
            document.getElementById('btnConfirmarCobro').focus();
        }

        function confirmarCobroVenta() {
            if (!cobroPendiente) return;
            const { pagos, recibido } = cobroPendiente;
            const metodoPrincipal = pagos.length === 1 ? pagos[0].metodo : 'mixto';
            const formData = new FormData();
            formData.append('token', TOKEN_SESION);
            formData.append('metodo_pago', metodoPrincipal);
            formData.append('monto_recibido', recibido);
            formData.append('pagos', JSON.stringify(pagos));
            formData.append('cliente_dni', clienteDniActual);
            formData.append('cliente_nombre', clienteNombreActual);

            const boton = document.getElementById('btnConfirmarCobro');
            boton.disabled = true;
            fetch('index.php?action=pos_procesar_venta', {
                method: 'POST',
                body: formData
            })
            .then(res => res.json())
            .then(data => {
                if (data.status === 'success') {
                    cerrarConfirmacionCobro();
                    ultimoTicketData = data;
                    try { sessionStorage.setItem('caserita_pos_ultimo_ticket', JSON.stringify(data)); } catch (error) {}
                    document.getElementById('btnImprimirVentaAnterior').disabled = false;
                    document.getElementById('btnImprimirVentaAnterior').style.opacity = '1';
                    mostrarTicketTermico(data);
                    reiniciarPagos();
                    cargarItemsPOS();
                } else {
                    mostrarAvisoValidacionCaja(data.message || 'No se pudo completar la venta.');
                }
            })
            .catch(() => {
                mostrarAvisoValidacionCaja('Error de conexión al procesar la venta.');
            })
            .finally(() => {
                boton.disabled = false;
            });
        }

        function mostrarTicketTermico(data) {
            const numeroDocumento = data.cliente_dni || clienteDniActual;
            const metodoTicket = String(data.metodo_pago || '').toUpperCase();
            document.getElementById('lblTicketTipoDoc').textContent = 'BOLETA DE VENTA ELECTRÓNICA';
            document.getElementById('lblTicketCodigo').textContent = data.codigo_pedido || `POS-${data.id_pedido}`;
            document.getElementById('lblTicketFecha').textContent = data.fecha || new Date().toLocaleString();
            document.getElementById('lblTicketCajero').textContent = data.cajero || 'Cajero de Turno';
            document.getElementById('lblTicketCliente').textContent = data.cliente_nombre || clienteNombreActual;
            document.getElementById('lblTicketDni').textContent = numeroDocumento;
            document.getElementById('lblTicketTotal').textContent = `S/ ${parseFloat(data.total).toFixed(2)}`;
            document.getElementById('lblTicketSubtotal').textContent = `S/ ${parseFloat(data.subtotal || data.total / 1.18).toFixed(2)}`;
            document.getElementById('lblTicketIgv').textContent = `S/ ${parseFloat(data.igv || (data.total - (data.total / 1.18))).toFixed(2)}`;
            document.getElementById('lblTicketMetodo').textContent = metodoTicket || 'EFECTIVO';

            const pagosTicket = Array.isArray(data.pagos) ? data.pagos : [];
            const tieneEfectivo = pagosTicket.some(function (p) { return p.metodo === 'efectivo'; });
            const rowEfectivo = document.getElementById('rowTicketEfectivo');
            if (rowEfectivo) {
                if (tieneEfectivo || metodoTicket === 'EFECTIVO') {
                    rowEfectivo.style.display = 'block';
                    document.getElementById('lblTicketRecibido').textContent = `S/ ${parseFloat(data.monto_recibido || 0).toFixed(2)}`;
                    document.getElementById('lblTicketVuelto').textContent = `S/ ${parseFloat(data.vueltas || 0).toFixed(2)}`;
                } else {
                    rowEfectivo.style.display = 'none';
                }
            }

            const tbody = document.getElementById('ticketTbodyItems');
            let rows = '';
            if (data.items && Array.isArray(data.items)) {
                data.items.forEach(it => {
                    rows += `
                        <tr>
                            <td style="padding:3px 0;">${it.cantidad}</td>
                            <td style="padding:3px 0;">${escapeHtml(it.nombre || it.titulo || ('Producto #' + it.id_producto))}</td>
                            <td style="padding:3px 0; text-align:right;">${parseFloat(it.precio).toFixed(2)}</td>
                            <td style="padding:3px 0; text-align:right; font-weight:bold;">${parseFloat(it.subtotal).toFixed(2)}</td>
                        </tr>`;
                });
            }
            tbody.innerHTML = rows;

            document.getElementById('modalTicketVenta').style.display = 'flex';
        }

        function cerrarTicketVenta() {
            document.getElementById('modalTicketVenta').style.display = 'none';
        }

        function imprimirTicketTermico() {
            const ticketElement = document.getElementById('ticketTermicoImprimible');
            if (!ticketElement) {
                window.print();
                return;
            }

            let iframe = document.getElementById('posThermalPrintFrame');
            if (!iframe) {
                iframe = document.createElement('iframe');
                iframe.id = 'posThermalPrintFrame';
                iframe.style.position = 'fixed';
                iframe.style.right = '0';
                iframe.style.bottom = '0';
                iframe.style.width = '0';
                iframe.style.height = '0';
                iframe.style.border = '0';
                iframe.style.visibility = 'hidden';
                document.body.appendChild(iframe);
            }

            const doc = iframe.contentWindow.document;
            doc.open();
            doc.write(`<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Imprimir Comprobante</title>
    <link rel="stylesheet" href="${new URL('Styles/Vistas/Cajero/ticket_impresion.css', window.location.href).href}">
</head>
<body>
    ${ticketElement.innerHTML}
</body>
</html>`);
            doc.close();

            const imprimir = () => setTimeout(() => {
                try {
                    iframe.contentWindow.focus();
                    iframe.contentWindow.print();
                } catch (e) {
                    console.error('Error al imprimir ticket:', e);
                    window.print();
                }
            }, 200);
            const cssImpresion = doc.querySelector('link[rel="stylesheet"]');
            if (cssImpresion && !cssImpresion.sheet) {
                cssImpresion.addEventListener('load', imprimir, { once: true });
                cssImpresion.addEventListener('error', imprimir, { once: true });
            } else {
                imprimir();
            }
        }

        function imprimirVentaAnterior() {
            if (!ultimoTicketData) {
                alert('Todavía no hay una venta anterior guardada en esta PC.');
                return;
            }
            mostrarTicketTermico(ultimoTicketData);
            setTimeout(imprimirTicketTermico, 200);
        }

        function descargarTicketPDF() {
            const ticketElement = document.getElementById('ticketTermicoImprimible');
            const codigo = ultimoTicketData ? (ultimoTicketData.codigo_pedido || 'POS-Ticket') : 'Ticket';
            const opt = {
                margin:       [5, 5, 5, 5],
                filename:     `${codigo}.pdf`,
                image:        { type: 'jpeg', quality: 0.98 },
                html2canvas:  { scale: 2 },
                jsPDF:        { unit: 'mm', format: [80, 200], orientation: 'portrait' }
            };
            html2pdf().set(opt).from(ticketElement).save();
        }
    </script>
<?php if (!$isAjax && $isAdminPOS): ?>
            </div>
        </div>
    </div>
<?php endif; ?>
<?php if (!$isAjax): ?>
</body>
</html>
<?php endif; ?>
