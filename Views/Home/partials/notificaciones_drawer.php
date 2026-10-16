<?php
/**
 * Drawer lateral de notificaciones de pedidos.
 * Canónico para todas las páginas que incluyen header_principal.php.
 */
$pedidosNotif = $pedidosUsuario ?? [];
?>
<div class="notif-overlay" id="notifOverlay" onclick="cerrarNotificaciones(event)"></div>
<aside class="notif-sidebar" id="notifDropdown" aria-label="Panel de Notificaciones de Pedidos">
    <div class="notif-header" style="color: #0f172a; padding: 18px 20px; display: flex; align-items: center; justify-content: space-between; border-bottom: 1px solid #f1f5f9;">
        <div>
            <h2 style="display:flex; align-items:center; gap:8px; margin: 0; font-size: 1.22rem; font-weight:800; color:#0f172a;">
                <svg xmlns="http://www.w3.org/2000/svg" width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="#15803d" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"><path d="M18 8a6 6 0 0 0-12 0c0 7-3 9-3 9h18s-3-2-3-9"></path><path d="M13.73 21a2 2 0 0 1-3.46 0"></path></svg>
                Notificaciones
            </h2>
            <p class="notif-header-subtitle" style="margin: 4px 0 0 0; font-size: 0.85rem; color:#64748b;">Estado de tus compras en tiempo real</p>
        </div>
        <div style="display: flex; align-items: center; gap: 8px;">
            <button type="button" class="btn-close-universal btn-close-notif" id="closeNotifDropdown" onclick="cerrarNotificaciones(event)" aria-label="Cerrar notificaciones">&times;</button>
        </div>
    </div>

    <div class="notif-content" id="notifItems">
        <?php if (!empty($pedidosNotif)): ?>
            <?php foreach (array_slice($pedidosNotif, 0, 15) as $ped):
                $estClean = strtolower(trim((string)($ped['estado'] ?? 'pendiente')));
                $esNoLeido = ((int)($ped['leido_cliente'] ?? 0) === 0);
                
                switch ($estClean) {
                    case 'en camino':
                        $colorEstado = '#0284c7'; // Celeste vivo
                        $badgeText = 'En camino';
                        break;
                    case 'en preparacion':
                    case 'en preparación':
                        $colorEstado = '#4f46e5'; // Índigo / Púrpura
                        $badgeText = 'En preparación';
                        break;
                    case 'pendiente':
                        $colorEstado = '#d97706'; // Ámbar
                        $badgeText = 'Pendiente';
                        break;
                    case 'entregado':
                        $colorEstado = '#15803d'; // Verde
                        $badgeText = 'Entregado';
                        break;
                    case 'cancelado':
                    case 'rechazado':
                        $colorEstado = '#dc2626'; // Rojo
                        $badgeText = 'Cancelado';
                        break;
                    default:
                        $colorEstado = '#64748b';
                        $badgeText = ucfirst($estClean);
                        break;
                }

                $fechaFormat = !empty($ped['fecha']) ? date('d M Y, h:i A', strtotime($ped['fecha'])) : 'Reciente';
                $bgInicial = $esNoLeido ? '#f0fdf4' : '#ffffff';
                $borderLeftInicial = $esNoLeido ? '3.5px solid #15803d' : '3.5px solid transparent';
            ?>
            <div class="stock-dropdown-item notif-item-row <?= $esNoLeido ? 'notif-unread' : 'notif-read'; ?>" 
                 id="notifItemRow_<?= (int)$ped['id_pedido']; ?>" 
                 data-leido="<?= $esNoLeido ? '0' : '1'; ?>"
                 style="cursor:pointer; padding: 16px 20px; border-bottom: 1px solid #f1f5f9; display: flex; gap: 14px; align-items: flex-start; transition: all 0.2s ease; background-color: <?= $bgInicial; ?>; border-left: <?= $borderLeftInicial; ?>;" 
                 onmouseover="this.style.backgroundColor='#f1f5f9'" 
                 onmouseout="this.style.backgroundColor=(this.getAttribute('data-leido')==='0' ? '#f0fdf4' : '#ffffff')" 
                 onclick="leerNotificacionPedido(<?= (int)$ped['id_pedido']; ?>, 'index.php?action=detalle_pedido_usuario&id=<?= (int)$ped['id_pedido']; ?>')">
                
                <div class="notif-icon-box" style="width:42px;height:42px;background:<?= $esNoLeido ? '#dcfce7' : '#f8fafc'; ?>;border:1px solid <?= $esNoLeido ? '#86efac' : '#e2e8f0'; ?>;border-radius:12px;display:flex;align-items:center;justify-content:center;color:<?= $esNoLeido ? '#15803d' : '#94a3b8'; ?>;flex-shrink:0;transition:all 0.2s ease;">
                    <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <path d="M6 2 3 6v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2V6l-3-4z"></path>
                        <line x1="3" y1="6" x2="21" y2="6"></line>
                        <path d="M16 10a4 4 0 0 1-8 0"></path>
                    </svg>
                </div>
                <div style="flex:1; min-width:0;">
                    <div style="display:flex;justify-content:space-between;align-items:center; gap:8px;">
                        <div style="display:flex;align-items:center;gap:6px;">
                            <strong class="notif-pedido-titulo" style="font-size:0.95rem;color:<?= $esNoLeido ? '#0f172a' : '#334155'; ?>;font-weight:<?= $esNoLeido ? '700' : '600'; ?>;">
                                Pedido #<?= str_pad((int)$ped['id_pedido'], 5, '0', STR_PAD_LEFT); ?>
                            </strong>
                            <?php if ($esNoLeido): ?>
                                <span class="notif-pill-nuevo" style="background:#dcfce7;color:#15803d;font-size:0.68rem;font-weight:700;padding:2px 7px;border-radius:10px;border:1px solid #86efac;letter-spacing:0.3px;">Nuevo</span>
                            <?php endif; ?>
                        </div>
                        <small style="font-size:0.74rem;color:#94a3b8;flex-shrink:0;"><?= $fechaFormat; ?></small>
                    </div>
                    <p style="margin:4px 0;color:#334155;font-size:0.85rem;font-weight:600;">
                        Total: S/ <?= number_format((float)($ped['total'] ?? 0), 2); ?> - <?= htmlspecialchars($ped['tipo_entrega'] ?? 'Delivery'); ?>
                    </p>
                    <div style="display:flex;justify-content:space-between;align-items:center;margin-top:6px;">
                        <span class="notif-estado-texto" style="color:<?= $colorEstado; ?>;font-size:0.8rem;font-weight:700;letter-spacing:0.2px;">
                            <?= $badgeText; ?>
                        </span>
                        <span style="font-size:0.82rem;color:#15803d;font-weight:700;">Ver detalle →</span>
                    </div>
                </div>
            </div>
            <?php endforeach; ?>
        <?php else: ?>
            <div style="padding: 50px 20px; text-align: center; color: #64748b;">
                <div style="margin-bottom: 12px; display: inline-flex; align-items: center; justify-content: center; width: 64px; height: 64px; border-radius: 50%; background: #f0fdf4; color: #15803d; border: 1px solid #bbf7d0;">
                    <svg xmlns="http://www.w3.org/2000/svg" width="30" height="30" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M18 8a6 6 0 0 0-12 0c0 7-3 9-3 9h18s-3-2-3-9"></path><path d="M13.73 21a2 2 0 0 1-3.46 0"></path></svg>
                </div>
                <strong style="font-size: 1rem; color: #0f172a; display: block;">No tienes notificaciones</strong>
                <p style="font-size: 0.85rem; margin-top: 4px;">Los cambios de estado de tus pedidos aparecerán aquí.</p>
            </div>
        <?php endif; ?>
    </div>

    <div class="notif-footer" style="padding: 16px 20px; border-top: 1px solid #f1f5f9; background: #ffffff;">
        <a href="index.php?action=mi_cuenta" class="btn-notif-action" style="display: flex; align-items: center; justify-content: center; width: 100%; height: 44px; border-radius: 12px; background: linear-gradient(135deg, #15803d 0%, #166534 100%); color: #ffffff; text-decoration: none; font-weight: 700; font-size: 0.95rem; box-shadow: 0 4px 12px rgba(21, 128, 61, 0.28); transition: all 0.2s;" onmouseover="this.style.boxShadow='0 6px 16px rgba(21, 128, 61, 0.38)'; this.style.transform='translateY(-1px)';" onmouseout="this.style.boxShadow='0 4px 12px rgba(21, 128, 61, 0.28)'; this.style.transform='translateY(0)';">
            Ver mis pedidos
        </a>
    </div>
</aside>

<script>
    function leerNotificacionPedido(idPedido, redirectUrl) {
        const row = document.getElementById('notifItemRow_' + idPedido);
        const wasUnread = !row || row.getAttribute('data-leido') === '0' || row.classList.contains('notif-unread');

        if (row) {
            // Cambio instantáneo a estado leído
            row.setAttribute('data-leido', '1');
            row.classList.remove('notif-unread');
            row.classList.add('notif-read');
            row.style.backgroundColor = '#ffffff';
            row.style.borderLeft = '3.5px solid transparent';

            const iconBox = row.querySelector('.notif-icon-box');
            if (iconBox) {
                iconBox.style.backgroundColor = '#f8fafc';
                iconBox.style.borderColor = '#e2e8f0';
                iconBox.style.color = '#94a3b8';
            }

            const pillNuevo = row.querySelector('.notif-pill-nuevo');
            if (pillNuevo) {
                pillNuevo.remove();
            }

            const titulo = row.querySelector('.notif-pedido-titulo');
            if (titulo) {
                titulo.style.color = '#334155';
                titulo.style.fontWeight = '600';
            }
        }

        if (wasUnread) {
            // Decrementar contadores en la interfaz en tiempo real
            document.querySelectorAll('#notifBadge, .user-dropdown-badge').forEach(badge => {
                let count = parseInt(badge.textContent || '0', 10);
                if (count > 0) {
                    count--;
                    badge.textContent = count;
                    if (count <= 0) {
                        badge.style.display = 'none';
                    }
                }
            });

            // Registrar en base de datos para persistir como leído
            const formData = new FormData();
            formData.append('id_pedido', idPedido);
            
            if (navigator.sendBeacon) {
                navigator.sendBeacon('index.php?action=marcar_pedido_leido', formData);
                if (redirectUrl) {
                    window.location.href = redirectUrl;
                }
            } else {
                fetch('index.php?action=marcar_pedido_leido', {
                    method: 'POST',
                    headers: { 'X-Requested-With': 'XMLHttpRequest' },
                    body: formData
                }).finally(() => {
                    if (redirectUrl) {
                        window.location.href = redirectUrl;
                    }
                });
            }
        } else if (redirectUrl) {
            window.location.href = redirectUrl;
        }
    }

    function toggleNotificaciones(e) {
        if (e) {
            e.preventDefault();
            e.stopPropagation();
        }
        const dropdown = document.getElementById('notifDropdown');
        const overlay = document.getElementById('notifOverlay');
        if (!dropdown) return;

        const isActive = dropdown.classList.contains('active');
        if (isActive) {
            dropdown.classList.remove('active');
            if (overlay) overlay.classList.remove('active');
        } else {
            const userMenuWrapper = document.getElementById('userMenuWrapper');
            if (userMenuWrapper) userMenuWrapper.classList.remove('active');

            const cartSidebar = document.getElementById('cartSidebar');
            if (cartSidebar && cartSidebar.classList.contains('active')) {
                cartSidebar.classList.remove('active');
                const cartOverlay = document.getElementById('cartOverlay');
                if (cartOverlay) cartOverlay.classList.remove('active');
                document.body.classList.remove('cart-open');
            }
            dropdown.classList.add('active');
            if (overlay) overlay.classList.add('active');
        }
    }

    function cerrarNotificaciones(e) {
        if (e) {
            e.preventDefault();
            e.stopPropagation();
        }
        const dropdown = document.getElementById('notifDropdown');
        const overlay = document.getElementById('notifOverlay');
        if (dropdown) dropdown.classList.remove('active');
        if (overlay) overlay.classList.remove('active');
    }

    window.toggleNotificaciones = toggleNotificaciones;
    window.cerrarNotificaciones = cerrarNotificaciones;
    window.leerNotificacionPedido = leerNotificacionPedido;
</script>
