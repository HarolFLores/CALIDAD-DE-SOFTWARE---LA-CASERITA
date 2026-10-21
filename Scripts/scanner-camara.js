/**
 * UniversalCameraScanner - Sistema Integral de Lectura de Códigos de Barras por Cámara / Celular
 * El celular vinculado transmite códigos al campo activo del ERP.
 * - Lectura de códigos 1D (EAN-13, EAN-8, UPC-A, UPC-E, CODE 128, CODE 39) y 2D (QR Code)
 * Tienda La Caserita
 */

(function (window) {
    'use strict';

    // Contexto de audio Web Audio API para emitir pitido de escaneo sin requerir archivos mp3
    let audioCtx = null;
    function playBarcodeBeep(isSuccess = true) {
        try {
            if (!audioCtx) {
                audioCtx = new (window.AudioContext || window.webkitAudioContext)();
            }
            if (audioCtx.state === 'suspended') {
                audioCtx.resume();
            }
            const osc = audioCtx.createOscillator();
            const gain = audioCtx.createGain();
            osc.connect(gain);
            gain.connect(audioCtx.destination);

            osc.type = 'sine';
            osc.frequency.setValueAtTime(isSuccess ? 1900 : 380, audioCtx.currentTime);
            gain.gain.setValueAtTime(0.18, audioCtx.currentTime);
            gain.gain.exponentialRampToValueAtTime(0.001, audioCtx.currentTime + (isSuccess ? 0.085 : 0.22));

            osc.start();
            osc.stop(audioCtx.currentTime + (isSuccess ? 0.095 : 0.23));

            if (navigator.vibrate) {
                navigator.vibrate(isSuccess ? [60] : [100, 50, 100]);
            }
        } catch (e) {
            console.warn('Audio/Haptic feedback no disponible:', e);
        }
    }

    function isEditableScanTarget(element) {
        if (!element || element.disabled || element.readOnly) return false;
        if (element.tagName === 'TEXTAREA') return true;
        if (element.tagName !== 'INPUT') return false;
        return !['button', 'submit', 'reset', 'checkbox', 'radio', 'file', 'range', 'color', 'hidden', 'image'].includes(element.type);
    }

    const UniversalCameraScanner = {
        isReal: true,
        currentConfig: null,
        isOpen: false,
        lastScannedCode: null,
        lastScannedTime: 0,
        domElements: null,

        // Inicializa o asegura que el modal exista en el DOM
        ensureModalDOM: function () {
            if (document.getElementById('universalCameraScannerModal')) {
                return;
            }

            // Inyectar estilos para el modal y la animación láser
            const style = document.createElement('style');
            style.id = 'universalCameraScannerStyles';
            style.textContent = `
                .ucs-overlay {
                    position: fixed;
                    top: 0;
                    left: 0;
                    right: 0;
                    bottom: 0;
                    background: rgba(15, 23, 42, 0.75);
                    backdrop-filter: blur(6px);
                    z-index: 999999;
                    display: flex;
                    align-items: center;
                    justify-content: center;
                    padding: 16px;
                    opacity: 0;
                    visibility: hidden;
                    transition: opacity 0.25s ease, visibility 0.25s ease;
                }
                .ucs-overlay.active {
                    opacity: 1;
                    visibility: visible;
                }
                .ucs-card {
                    background: #ffffff;
                    border-radius: 20px;
                    width: 100%;
                    max-width: 520px;
                    box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.45);
                    overflow: hidden;
                    display: flex;
                    flex-direction: column;
                    transform: translateY(20px) scale(0.97);
                    transition: transform 0.25s cubic-bezier(0.16, 1, 0.3, 1);
                }
                .ucs-overlay.active .ucs-card {
                    transform: translateY(0) scale(1);
                }
                .ucs-header {
                    padding: 16px 20px;
                    background: #15803D;
                    color: #ffffff;
                    display: flex;
                    align-items: center;
                    justify-content: space-between;
                }
                .ucs-header-title {
                    display: flex;
                    align-items: center;
                    gap: 12px;
                }
                .ucs-header-icon {
                    width: 40px;
                    height: 40px;
                    background: rgba(255, 255, 255, 0.18);
                    border-radius: 10px;
                    display: flex;
                    align-items: center;
                    justify-content: center;
                    font-size: 1.25rem;
                }
                .ucs-btn-close {
                    background: rgba(255, 255, 255, 0.15);
                    border: none;
                    color: #ffffff;
                    width: 34px;
                    height: 34px;
                    border-radius: 50%;
                    cursor: pointer;
                    display: flex;
                    align-items: center;
                    justify-content: center;
                    font-size: 1.3rem;
                    transition: background 0.2s;
                }
                .ucs-btn-close:hover {
                    background: rgba(255, 255, 255, 0.3);
                }
                .ucs-body {
                    padding: 18px 20px;
                    display: flex;
                    flex-direction: column;
                    gap: 14px;
                }
                .ucs-pairing-card {
                    display: grid;
                    justify-items: center;
                    gap: 10px;
                    padding: 16px;
                    border: 1px solid #cbd5e1;
                    border-radius: 14px;
                    background: #f8fafc;
                    text-align: center;
                    color: #475569;
                }
                .ucs-transport-switch {
                    display: flex;
                    gap: 8px;
                }
                .ucs-transport-switch button {
                    padding: 8px 12px;
                    border: 1px solid #cbd5e1;
                    border-radius: 9px;
                    background: #fff;
                    color: #334155;
                    font-weight: 800;
                    cursor: pointer;
                }
                .ucs-transport-switch button.active {
                    border-color: #15803d;
                    background: #f0fdf4;
                    color: #166534;
                }
                .ucs-open-usb {
                    display: none;
                    width: 100%;
                    padding: 12px 16px;
                    border: 0;
                    border-radius: 10px;
                    background: #15803d;
                    color: #fff;
                    font-size: 0.92rem;
                    font-weight: 800;
                    cursor: pointer;
                }
                .ucs-open-usb.visible {
                    display: block;
                }
                .ucs-open-usb:disabled {
                    opacity: 0.65;
                    cursor: wait;
                }
                .ucs-pairing-card[hidden],
                .ucs-connected-message[hidden] {
                    display: none;
                }
                .ucs-qr-box {
                    width: 176px;
                    min-height: 176px;
                    display: grid;
                    place-items: center;
                    padding: 8px;
                    background: #fff;
                    border: 1px solid #cbd5e1;
                    border-radius: 12px;
                }
                .ucs-qr-box img,
                .ucs-qr-box canvas {
                    display: block;
                    max-width: 100%;
                    height: auto;
                }
                .ucs-pairing-link {
                    color: #15803d;
                    font-weight: 800;
                    text-decoration: underline;
                }
                .ucs-pairing-status {
                    width: 100%;
                    padding: 8px 10px;
                    border-radius: 9px;
                    background: #fff7ed;
                    color: #9a3412;
                    font-size: 0.82rem;
                    font-weight: 700;
                }
                .ucs-pairing-status.connected {
                    background: #f0fdf4;
                    color: #166534;
                }
                .ucs-connected-message {
                    padding: 20px 14px;
                    border-radius: 12px;
                    background: #f0fdf4;
                    color: #15803d;
                    font-size: 1.05rem;
                    font-weight: 800;
                    text-align: center;
                }
                .ucs-connected-message button {
                    display: block;
                    margin: 14px auto 0;
                    padding: 8px 14px;
                    border: 1px solid #15803d;
                    border-radius: 8px;
                    background: #fff;
                    color: #166534;
                    font-weight: 800;
                    cursor: pointer;
                }
                .ucs-pairing-error {
                    color: #b91c1c;
                    font-size: 0.82rem;
                    font-weight: 700;
                }
                .ucs-select-group {
                    display: flex;
                    gap: 8px;
                    align-items: center;
                }
                .ucs-select {
                    flex: 1;
                    padding: 10px 14px;
                    border-radius: 10px;
                    border: 1.5px solid #cbd5e1;
                    font-size: 0.88rem;
                    font-weight: 600;
                    color: #0f172a;
                    background: #f8fafc;
                    outline: none;
                    cursor: pointer;
                }
                .ucs-select:focus {
                    border-color: #15803D;
                    background: #ffffff;
                }
                .ucs-btn-refresh {
                    padding: 0 12px;
                    height: 42px;
                    background: #f1f5f9;
                    border: 1.5px solid #cbd5e1;
                    border-radius: 10px;
                    color: #475569;
                    cursor: pointer;
                    display: flex;
                    align-items: center;
                    justify-content: center;
                    transition: all 0.2s;
                }
                .ucs-btn-refresh:hover {
                    background: #e2e8f0;
                    color: #0f172a;
                }
                .ucs-viewport-wrapper {
                    position: relative;
                    width: 100%;
                    border-radius: 16px;
                    background: #000000;
                    overflow: hidden;
                    min-height: 280px;
                    display: flex;
                    align-items: center;
                    justify-content: center;
                    box-shadow: inset 0 0 25px rgba(0, 0, 0, 0.8);
                }
                #ucsCameraReader {
                    width: 100% !important;
                    height: 100% !important;
                }
                #ucsCameraReader video {
                    width: 100% !important;
                    height: auto !important;
                    border-radius: 16px;
                    object-fit: cover;
                }
                .ucs-laser-overlay {
                    position: absolute;
                    inset: 18px;
                    border: 2px dashed rgba(34, 197, 94, 0.75);
                    border-radius: 14px;
                    pointer-events: none;
                    box-shadow: 0 0 0 9999px rgba(0, 0, 0, 0.4);
                }
                .ucs-laser-line {
                    position: absolute;
                    left: 4%;
                    right: 4%;
                    height: 2.5px;
                    background: #ef4444;
                    box-shadow: 0 0 12px #ef4444, 0 0 4px #ffffff;
                    animation: ucsLaserScan 2s infinite alternate ease-in-out;
                }
                @keyframes ucsLaserScan {
                    0% { top: 12%; opacity: 0.85; }
                    50% { opacity: 1; }
                    100% { top: 88%; opacity: 0.85; }
                }
                .ucs-target-hint {
                    position: absolute;
                    bottom: 10px;
                    left: 0;
                    right: 0;
                    text-align: center;
                    color: #ffffff;
                    font-size: 0.78rem;
                    font-weight: 700;
                    text-shadow: 0 1px 4px rgba(0,0,0,0.9);
                    background: rgba(0, 0, 0, 0.6);
                    padding: 4px 10px;
                    border-radius: 8px;
                    margin: 0 20px;
                    backdrop-filter: blur(2px);
                }
                .ucs-feedback-card {
                    padding: 12px 14px;
                    border-radius: 12px;
                    background: #f0fdf4;
                    border: 1px solid #bbf7d0;
                    display: none;
                    align-items: center;
                    gap: 10px;
                }
                .ucs-feedback-card.active {
                    display: flex;
                }
                .ucs-feedback-icon {
                    color: #16a34a;
                    font-size: 1.3rem;
                }
                .ucs-feedback-title {
                    font-size: 0.86rem;
                    font-weight: 800;
                    color: #166534;
                }
                .ucs-feedback-sub {
                    font-size: 0.78rem;
                    color: #15803d;
                }
            `;
            document.head.appendChild(style);

            // Tema limpio compartido por todos los botones de escáner
            if (!document.getElementById('universalCameraScannerPosTheme')) {
                const themeStyle = document.createElement('style');
                themeStyle.id = 'universalCameraScannerPosTheme';
                themeStyle.textContent = `
                    html body .ucs-theme-pos .ucs-card {
                        max-width: 440px;
                        border: 0;
                        border-radius: 24px;
                        background: #fff;
                        box-shadow: 0 20px 50px rgba(0, 0, 0, 0.25);
                    }
                    html body .ucs-theme-pos .ucs-header {
                        align-items: flex-start;
                        padding: 22px 24px 0;
                        background: #fff;
                        color: #0f172a;
                    }
                    html body .ucs-theme-pos .ucs-header-icon {
                        display: none;
                    }
                    html body .ucs-theme-pos .ucs-header-title {
                        flex: 1;
                        justify-content: center;
                    }
                    html body .ucs-theme-pos .ucs-header-title h3 {
                        color: #0d5f6e;
                        text-align: center;
                    }
                    html body .ucs-theme-pos #ucsModalSubtitle {
                        display: none;
                    }
                    html body .ucs-theme-pos .ucs-btn-close {
                        width: 34px;
                        height: 34px;
                        border: 2px solid #0d5f6e;
                        border-radius: 50%;
                        background: #fff;
                        color: #0d5f6e;
                        font-size: 1.3rem;
                        line-height: 1;
                    }
                    html body .ucs-theme-pos .ucs-btn-close:hover {
                        background: #ecfdf5;
                    }
                    html body .ucs-theme-pos .ucs-body {
                        gap: 12px;
                        padding: 10px 24px 20px;
                    }
                    html body .ucs-theme-pos .ucs-pairing-card {
                        display: flex;
                        flex-direction: column;
                        align-items: stretch;
                        gap: 8px;
                        padding: 0;
                        border: 0;
                        border-radius: 0;
                        background: transparent;
                        text-align: center;
                    }
                    html body .ucs-theme-pos .ucs-pairing-card > strong {
                        order: 1;
                        color: #475569;
                        font-size: 0.88rem;
                        font-weight: 400;
                        line-height: 1.35;
                    }
                    html body .ucs-theme-pos .ucs-pairing-status {
                        order: 2;
                        padding: 6px 9px;
                        border-radius: 8px;
                        background: #fff7ed;
                        color: #9a3412;
                        font-size: 0.75rem;
                        font-weight: 600;
                    }
                    html body .ucs-theme-pos .ucs-pairing-status.connected {
                        background: #f0fdf4;
                        color: #166534;
                    }
                    html body .ucs-theme-pos .ucs-transport-switch {
                        order: 3;
                        display: grid;
                        grid-template-columns: 1fr 1fr;
                        gap: 7px;
                        width: 100%;
                    }
                    html body .ucs-theme-pos .ucs-transport-switch button {
                        display: flex;
                        align-items: center;
                        justify-content: center;
                        gap: 7px;
                        min-height: 38px;
                        padding: 6px 9px;
                        border: 1px solid #cbd5e1;
                        border-radius: 9px;
                        background: #f8fafc;
                        color: #334155;
                        font-size: 0.82rem;
                        font-weight: 700;
                    }
                    html body .ucs-theme-pos .ucs-transport-switch button.active {
                        border-color: #0d5f6e;
                        background: #ecfdf5;
                        color: #166534;
                    }
                    html body .ucs-theme-pos .ucs-qr-box {
                        order: 4;
                        align-self: center;
                        width: 184px;
                        min-height: 184px;
                        padding: 0;
                        border: 0;
                        border-radius: 12px;
                        background: #fff;
                    }
                    html body .ucs-theme-pos #ucsPairingToken {
                        order: 5;
                        color: #475569;
                        font-size: 0.82rem;
                    }
                    html body .ucs-theme-pos .ucs-open-usb {
                        order: 6;
                    }
                    html body .ucs-theme-pos .ucs-pairing-link {
                        order: 7;
                        display: flex;
                        align-items: center;
                        justify-content: center;
                        min-height: 38px;
                        padding: 8px 10px;
                        border: 1px solid #cbd5e1;
                        border-radius: 9px;
                        background: #f8fafc;
                        color: #0d5f6e;
                        font-size: 0.79rem;
                        font-weight: 700;
                        text-decoration: none;
                    }
                    html body .ucs-theme-pos .ucs-pairing-error {
                        order: 8;
                    }
                    html body .ucs-theme-pos .ucs-connected-message {
                        border-radius: 12px;
                        background: #f0fdf4;
                        color: #15803d;
                    }
                `;
                document.head.appendChild(themeStyle);
            }

            // Inyectar HTML del modal
            const modal = document.createElement('div');
            modal.id = 'universalCameraScannerModal';
            modal.className = 'ucs-overlay';
            modal.setAttribute('role', 'dialog');
            modal.setAttribute('aria-modal', 'true');
            modal.innerHTML = `
                <div class="ucs-card" onclick="event.stopPropagation()">
                    <div class="ucs-header">
                        <div class="ucs-header-title">
                            <div class="ucs-header-icon">
                                <i class="fa-solid fa-camera"></i>
                            </div>
                            <div>
                                <h3 id="ucsModalTitle" style="margin:0; font-size:1.15rem; font-weight:800; line-height:1.2;">Lector de Código de Barras</h3>
                                <small id="ucsModalSubtitle" style="opacity:0.85; font-size:0.75rem;">Celular vinculado por cable o Bluetooth</small>
                            </div>
                        </div>
                        <button type="button" class="ucs-btn-close btn-close-universal" id="ucsBtnClose" title="Cerrar (Esc)" aria-label="Cerrar escáner">&times;</button>
                    </div>

                    <div class="ucs-body">
                        <div class="ucs-pairing-card" id="ucsPairingCard">
                            <strong>Vincula el celular escaneando este QR</strong>
                            <div class="ucs-transport-switch" role="group" aria-label="Tipo de conexión">
                                <button type="button" id="ucsTransportBluetooth" class="active" aria-pressed="true"><i class="fa-brands fa-bluetooth-b"></i> Bluetooth</button>
                                <button type="button" id="ucsTransportUsb" aria-pressed="false"><i class="fa-solid fa-plug"></i> Cable USB</button>
                            </div>
                            <div class="ucs-pairing-status" id="ucsPairingStatus" role="status">Esperando lector m\u00f3vil \u00b7 Bluetooth/Cable USB</div>
                            <div class="ucs-qr-box" id="ucsQrCode" aria-label="Código QR para conectar el celular"></div>
                            <span id="ucsPairingToken"></span>
                            <button type="button" class="ucs-open-usb" id="ucsOpenUsb">Abrir automáticamente en celular (Cable USB)</button>
                            <a class="ucs-pairing-link" id="ucsPairingLink" href="#" target="_blank" rel="noopener">Abrir enlace del escáner móvil</a>
                            <span class="ucs-pairing-error" id="ucsPairingError" role="alert" hidden></span>
                        </div>
                        <div class="ucs-connected-message" id="ucsConnectedMessage" hidden>
                            <span><i class="fa-solid fa-circle-check" aria-hidden="true"></i> Lector conectado</span>
                            <button type="button" id="ucsBtnToggleScanner">Desconectar lector</button>
                        </div>

                        <!-- Feedback de lectura -->
                        <div class="ucs-feedback-card" id="ucsFeedbackBox">
                            <div class="ucs-feedback-icon">
                                <i class="fa-solid fa-circle-check"></i>
                            </div>
                            <div style="flex:1;">
                                <div class="ucs-feedback-title" id="ucsFeedbackTitle">¡Código leído con éxito!</div>
                                <div class="ucs-feedback-sub" id="ucsFeedbackSub">Código: <strong id="ucsFeedbackCode"></strong></div>
                            </div>
                        </div>
                    </div>

                </div>
            `;

            document.body.appendChild(modal);

            // Listeners de cierre
            modal.addEventListener('click', () => {
                UniversalCameraScanner.close();
            });
            document.getElementById('ucsTransportBluetooth').addEventListener('click', () => {
                UniversalCameraScanner.selectRemoteTransport('bluetooth');
            });
            document.getElementById('ucsTransportUsb').addEventListener('click', () => {
                UniversalCameraScanner.selectRemoteTransport('usb');
            });
            document.getElementById('ucsBtnClose').addEventListener('click', () => {
                UniversalCameraScanner.close();
            });
            document.getElementById('ucsBtnToggleScanner').addEventListener('click', () => {
                UniversalCameraScanner.toggleRemoteScanner();
            });
            document.getElementById('ucsOpenUsb').addEventListener('click', () => {
                UniversalCameraScanner.openRemoteUsb();
            });
            // Teclado Esc
            document.addEventListener('keydown', (e) => {
                if (e.key === 'Escape' && UniversalCameraScanner.isOpen) {
                    UniversalCameraScanner.close();
                }
            });
        },

        // Manejador central cuando se detecta un código válido
        handleScanSuccess: function (decodedText, decodedResult) {
            const now = Date.now();
            UniversalCameraScanner.lastScannedCode = decodedText;
            UniversalCameraScanner.lastScannedTime = now;

            // Emitir sonido tipo lector de supermercado
            playBarcodeBeep(true);

            // Mostrar feedback visual en modal
            const fb = document.getElementById('ucsFeedbackBox');
            const fbCode = document.getElementById('ucsFeedbackCode');
            const fbTitle = document.getElementById('ucsFeedbackTitle');
            const fbSub = document.getElementById('ucsFeedbackSub');
            if (fb && fbCode) {
                fb.className = 'ucs-feedback-card active';
                fb.style.borderColor = '#bbf7d0';
                fb.style.background = '#f0fdf4';
                fbTitle.textContent = '¡Código de barras leído!';
                fbCode.textContent = decodedText;
            }

            const targetInputId = UniversalCameraScanner.currentConfig && UniversalCameraScanner.currentConfig.targetInputId;
            let targetInput = targetInputId ? document.getElementById(targetInputId) : null;
            if (!targetInput && !(UniversalCameraScanner.isOpen && UniversalCameraScanner.currentConfig && UniversalCameraScanner.currentConfig.onScan)) {
                const focusedElement = document.activeElement;
                if (isEditableScanTarget(focusedElement)) targetInput = focusedElement;
            }
            if (targetInput) {
                    targetInput.value = decodedText;
                    targetInput.dispatchEvent(new Event('input', { bubbles: true }));
                    targetInput.dispatchEvent(new Event('change', { bubbles: true }));
                    
                    // Efecto de pulso verde en el input
                    targetInput.style.transition = 'box-shadow 0.3s ease, border-color 0.3s ease';
                    targetInput.style.boxShadow = '0 0 0 3px rgba(34, 197, 94, 0.4)';
                    targetInput.style.borderColor = '#16a34a';
                    setTimeout(() => {
                        targetInput.style.boxShadow = '';
                        targetInput.style.borderColor = '';
                    }, 1500);
            }

            // Invocar callback personalizado si existe
            if (UniversalCameraScanner.isOpen && UniversalCameraScanner.currentConfig && typeof UniversalCameraScanner.currentConfig.onScan === 'function') {
                try {
                    UniversalCameraScanner.currentConfig.onScan(decodedText, decodedResult, UniversalCameraScanner);
                } catch (e) {
                    console.error('Error en callback onScan:', e);
                }
            }

        },

        remotePhonePollingTimer: null,
        remoteScannerEnabled: false,
        remoteScannerToken: '',
        remoteStatusPollAt: 0,
        remoteTransport: 'bluetooth',
        selectRemoteTransport: function (transport) {
            if (transport !== 'bluetooth' && transport !== 'usb') return;
            UniversalCameraScanner.remoteTransport = transport;
            const bluetoothButton = document.getElementById('ucsTransportBluetooth');
            const usbButton = document.getElementById('ucsTransportUsb');
            bluetoothButton?.classList.toggle('active', transport === 'bluetooth');
            bluetoothButton?.setAttribute('aria-pressed', String(transport === 'bluetooth'));
            usbButton?.classList.toggle('active', transport === 'usb');
            usbButton?.setAttribute('aria-pressed', String(transport === 'usb'));
            UniversalCameraScanner.renderRemotePairing();
        },
        renderRemotePairing: function () {
            const token = UniversalCameraScanner.remoteScannerToken;
            const container = document.getElementById('ucsQrCode');
            const label = document.getElementById('ucsPairingToken');
            const link = document.getElementById('ucsPairingLink');
            if (!container || !token) return;

            const url = new URL(window.location.href);
            url.search = '';
            url.searchParams.set('action', 'pos_escaner_movil');
            url.searchParams.set('token', token);
            url.searchParams.set('transporte', UniversalCameraScanner.remoteTransport);
            url.searchParams.set('scanner_destino', 'campo');
            container.replaceChildren();
            if (label) label.textContent = `Token de sesión: ${token.slice(0, 12)}...`;
            if (link) link.href = url.toString();
            const usbButton = document.getElementById('ucsOpenUsb');
            if (usbButton) usbButton.classList.toggle('visible', UniversalCameraScanner.remoteTransport === 'usb');

            const drawQr = () => {
                if (typeof window.QRCode === 'undefined') return;
                new window.QRCode(container, {
                    text: url.toString(),
                    width: 160,
                    height: 160,
                    colorDark: '#0f172a',
                    colorLight: '#ffffff',
                    correctLevel: window.QRCode.CorrectLevel.M
                });
            };

            if (typeof window.QRCode === 'function') {
                drawQr();
            } else if (!window.ucsQrLoading) {
                window.ucsQrLoading = true;
                const script = document.createElement('script');
                script.src = 'https://cdnjs.cloudflare.com/ajax/libs/qrcodejs/1.0.0/qrcode.min.js';
                script.onload = () => {
                    window.ucsQrLoading = false;
                    drawQr();
                };
                script.onerror = () => {
                    window.ucsQrLoading = false;
                    container.textContent = 'Abre el enlace del escáner móvil.';
                };
                document.head.appendChild(script);
            }
        },
        updateRemotePhoneStatus: function () {
            const token = UniversalCameraScanner.remoteScannerToken;
            if (!token) return;
            fetch(`index.php?action=pos_estado_sesion&token=${encodeURIComponent(token)}`)
                .then(res => res.json())
                .then(data => {
                    if (data.status !== 'success') {
                        throw new Error(data.message || 'No se pudo consultar la conexión del celular.');
                    }

                    const conectado = data.scanner_activo === true && data.estado_conexion === 'conectado';
                    const pairingCard = document.getElementById('ucsPairingCard');
                    const connectedMessage = document.getElementById('ucsConnectedMessage');
                    const pairingStatus = document.getElementById('ucsPairingStatus');
                    if (pairingCard) pairingCard.hidden = conectado;
                    if (connectedMessage) connectedMessage.hidden = !conectado;
                    if (pairingStatus) {
                                                const modoConexion = UniversalCameraScanner.remoteTransport === 'usb' ? 'Cable USB' : 'Bluetooth';
                        pairingStatus.textContent = conectado
                            ? 'Lector conectado'
                            : data.scanner_activo
                                ? `Esperando lector m\u00f3vil \u00b7 ${modoConexion}`
                                : 'Lector desconectado';
                        pairingStatus.classList.toggle('connected', conectado);
                    }
                    UniversalCameraScanner.remoteScannerEnabled = data.scanner_activo === true;
                    if (data.scanner_activo === false) {
                        const error = document.getElementById('ucsPairingError');
                        if (error) error.hidden = true;
                    }
                })
                .catch(error => {
                    console.error('Error consultando la cámara conectada:', error);
                    const errorMessage = document.getElementById('ucsPairingError');
                    if (errorMessage) {
                        errorMessage.textContent = error.message || 'No se pudo consultar la conexión del celular.';
                        errorMessage.hidden = false;
                    }
                });
        },
        openRemoteUsb: function () {
            const button = document.getElementById('ucsOpenUsb');
            const errorMessage = document.getElementById('ucsPairingError');
            if (!button) return;
            button.disabled = true;
            if (errorMessage) {
                errorMessage.textContent = '';
                errorMessage.hidden = true;
            }

            const formData = new FormData();
            formData.append('token', UniversalCameraScanner.remoteScannerToken);
            formData.append('transporte', 'usb');
            fetch('index.php?action=pos_abrir_lector_celular', { method: 'POST', body: formData })
                .then(async response => {
                    const data = await response.json();
                    if (!response.ok || data.ok !== true) {
                        throw new Error(data.mensaje || 'No se pudo abrir el escáner en el celular.');
                    }
                    return data;
                })
                .then(data => {
                    if (data.token) {
                        UniversalCameraScanner.remoteScannerToken = data.token;
                        UniversalCameraScanner.renderRemotePairing();
                    }
                    if (errorMessage) {
                        errorMessage.textContent = data.mensaje || 'Se solicitó abrir el escáner en el celular.';
                        errorMessage.hidden = false;
                        errorMessage.style.color = '#15803d';
                    }
                })
                .catch(error => {
                    console.error('Error al abrir el lector USB:', error);
                    if (errorMessage) {
                        errorMessage.textContent = error.message || 'No se pudo abrir el escáner por USB.';
                        errorMessage.hidden = false;
                        errorMessage.style.color = '#b91c1c';
                    }
                })
                .finally(() => {
                    button.disabled = false;
                });
        },
        setRemoteScannerState: function (enabled) {
            const previousState = UniversalCameraScanner.remoteScannerEnabled;
            UniversalCameraScanner.remoteScannerEnabled = enabled;
            const pairingCard = document.getElementById('ucsPairingCard');
            const connectedMessage = document.getElementById('ucsConnectedMessage');
            if (!enabled && pairingCard) pairingCard.hidden = false;
            if (!enabled && connectedMessage) connectedMessage.hidden = true;

            const formData = new FormData();
            const token = UniversalCameraScanner.remoteScannerToken || window.TOKEN_SESION || '';
            if (token) formData.append('token', token);
            formData.append('scanner_activo', enabled ? '1' : '0');
            formData.append('scanner_destino', enabled ? 'campo' : 'pos');
            formData.append('transporte', UniversalCameraScanner.remoteTransport);
            fetch('index.php?action=pos_estado_sesion', { method: 'POST', body: formData })
                .then(res => res.json())
                .then(data => {
                    if (data.status !== 'success' || data.scanner_activo !== enabled) {
                        throw new Error(data.message || 'No se pudo cambiar el estado del escáner.');
                    }
                    if (data.token) UniversalCameraScanner.remoteScannerToken = data.token;
                    UniversalCameraScanner.renderRemotePairing();
                    UniversalCameraScanner.updateRemotePhoneStatus();
                })
                .catch(error => {
                    console.error('Error actualizando el estado del escáner:', error);
                    UniversalCameraScanner.remoteScannerEnabled = previousState;
                    const errorMessage = document.getElementById('ucsPairingError');
                    if (errorMessage) {
                        errorMessage.textContent = error.message || 'No se pudo actualizar la conexión del escáner.';
                        errorMessage.hidden = false;
                        errorMessage.style.color = '#b91c1c';
                    }
                });
        },
        toggleRemoteScanner: function () {
            UniversalCameraScanner.setRemoteScannerState(!UniversalCameraScanner.remoteScannerEnabled);
        },
        startRemotePhonePolling: function () {
            if (UniversalCameraScanner.remotePhonePollingTimer) {
                clearInterval(UniversalCameraScanner.remotePhonePollingTimer);
            }
            UniversalCameraScanner.remotePhonePollingTimer = setInterval(() => {
                if (UniversalCameraScanner.isOpen && Date.now() - UniversalCameraScanner.remoteStatusPollAt >= 1000) {
                    UniversalCameraScanner.remoteStatusPollAt = Date.now();
                    UniversalCameraScanner.updateRemotePhoneStatus();
                }
                if (!UniversalCameraScanner.remoteScannerEnabled) return;
                if (document.getElementById('posTableBody')) {
                    return;
                }
                const config = UniversalCameraScanner.currentConfig;
                const configuredTarget = config && config.targetInputId && document.getElementById(config.targetInputId);
                const focusedElement = document.activeElement;
                const focusedTarget = isEditableScanTarget(focusedElement);
                const callbackTarget = UniversalCameraScanner.isOpen && config && typeof config.onScan === 'function';
                if (!configuredTarget && !focusedTarget && !callbackTarget) return;
                const tokenQuery = UniversalCameraScanner.remoteScannerToken
                    ? `&token=${encodeURIComponent(UniversalCameraScanner.remoteScannerToken)}`
                    : '';
                fetch(`index.php?action=pos_escaneos_pendientes${tokenQuery}&scanner_destino=campo`)
                    .then(res => res.json())
                    .then(res => {
                        if (res.status === 'success' && Array.isArray(res.data) && res.data.length > 0) {
                            res.data.forEach(item => {
                                const codigo = item.codigo_escaneado;
                                if (codigo) {
                                    UniversalCameraScanner.handleScanSuccess(codigo, { text: codigo, source: 'celular_remoto' });
                                }
                            });
                        }
                    })
                    .catch(error => console.error('Error consultando la cola temporal del escáner:', error));
            }, 500);
        },

        stopRemotePhonePolling: function () {
            if (UniversalCameraScanner.remotePhonePollingTimer) {
                clearInterval(UniversalCameraScanner.remotePhonePollingTimer);
                UniversalCameraScanner.remotePhonePollingTimer = null;
            }
        },

        /**
         * Abre el lector de cámara
         * Opciones soportadas:
         * - title: Título del modal
         * - subtitle: Subtítulo descriptivo
         * - targetInputId: ID del <input> donde se inyectará el código leído
         * - onScan: function(code, result, scanner)
         */
        open: function (options = {}) {
            UniversalCameraScanner.ensureModalDOM();
            UniversalCameraScanner.currentConfig = options;
            UniversalCameraScanner.isOpen = true;
            UniversalCameraScanner.remoteScannerToken = options.token || window.TOKEN_SESION || '';
            UniversalCameraScanner.remoteTransport = options.transport === 'usb' ? 'usb' : 'bluetooth';
            UniversalCameraScanner.setRemoteScannerState(true);

            const modal = document.getElementById('universalCameraScannerModal');
            const title = document.getElementById('ucsModalTitle');
            const sub = document.getElementById('ucsModalSubtitle');
            const fb = document.getElementById('ucsFeedbackBox');

            if (modal) modal.classList.toggle('ucs-theme-pos', options.theme !== 'classic');
            UniversalCameraScanner.selectRemoteTransport(UniversalCameraScanner.remoteTransport);
            if (title && options.title) title.textContent = options.title;
            if (sub && options.subtitle) sub.textContent = options.subtitle;
            UniversalCameraScanner.renderRemotePairing();
            if (fb) fb.className = 'ucs-feedback-card'; // ocultar

            modal.classList.add('active');

            UniversalCameraScanner.startRemotePhonePolling();
            UniversalCameraScanner.updateRemotePhoneStatus();
        },

        // Cierra el lector y apaga la cámara
        close: function () {
            UniversalCameraScanner.isOpen = false;
            UniversalCameraScanner.currentConfig = null;
            if (UniversalCameraScanner.remoteScannerEnabled) {
                UniversalCameraScanner.setRemoteScannerState(false);
            }
            const modal = document.getElementById('universalCameraScannerModal');
            if (modal) {
                modal.classList.remove('active');
            }
        }
    };

    // Exportar al objeto global window
    window.UniversalCameraScanner = UniversalCameraScanner;
    window.playBarcodeBeep = playBarcodeBeep;
    UniversalCameraScanner.startRemotePhonePolling();

})(window);
