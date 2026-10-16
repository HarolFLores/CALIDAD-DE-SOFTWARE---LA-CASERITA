document.addEventListener('DOMContentLoaded', () => {
    const searchInput = document.getElementById('busqueda');
    const searchClear = document.getElementById('searchClear');
    const searchDropdown = document.getElementById('searchDropdown');
    const searchSuggestionsList = document.getElementById('searchSuggestionsList');
    const searchQuickTerm = document.getElementById('searchQuickTerm');
    const searchQuickProducts = document.getElementById('searchQuickProducts');
    const searchViewAllBtn = document.getElementById('searchViewAllBtn');
    
    const searchResultsSection = document.getElementById('searchResultsSection');
    const mainContent = document.getElementById('mainContent');
    const searchResultsTerm = document.getElementById('searchResultsTerm');
    const searchResultsCount = document.getElementById('searchResultsCount');
    const searchResultsGrid = document.getElementById('searchResultsGrid');

    let currentTerm = '';

    if (searchInput) {
        searchInput.addEventListener('input', (e) => {
            const term = e.target.value.trim().toLowerCase();
            currentTerm = term;

            if (term.length > 0) {
                if (searchClear) searchClear.style.display = 'block';
                if (searchDropdown) {
                    searchDropdown.classList.add('active');
                    searchDropdown.style.display = 'block';
                }
                realizarBusqueda(term);
            } else {
                if (searchClear) searchClear.style.display = 'none';
                if (searchDropdown) {
                    searchDropdown.classList.remove('active');
                    searchDropdown.style.display = 'none';
                }
            }
        });

        if (searchClear) {
            searchClear.addEventListener('click', () => {
                searchInput.value = '';
                searchInput.focus();
                searchClear.style.display = 'none';
                if (searchDropdown) {
                    searchDropdown.classList.remove('active');
                    searchDropdown.style.display = 'none';
                }
            });
        }

        searchInput.addEventListener('keypress', (e) => {
            if (e.key === 'Enter') {
                e.preventDefault();
                const term = searchInput.value.trim();
                if (term.length > 0) {
                    window.location.href = 'index.php?action=ver_categoria&buscar=' + encodeURIComponent(term);
                } else {
                    window.location.href = 'index.php?action=ver_categoria&ofertas=1&nombre=Ofertas';
                }
            }
        });
    }

    document.addEventListener('click', (e) => {
        if (!e.target.closest('.search-container') && !e.target.closest('.header-search-wrap')) {
            if (searchDropdown) {
                searchDropdown.classList.remove('active');
                searchDropdown.style.display = 'none';
            }
        }
    });

    if (searchViewAllBtn) {
        searchViewAllBtn.addEventListener('click', (e) => {
            e.preventDefault();
            const term = (searchInput ? searchInput.value : currentTerm || '').trim();
            if (term.length > 0) {
                window.location.href = 'index.php?action=ver_categoria&buscar=' + encodeURIComponent(term);
            } else {
                window.location.href = 'index.php?action=ver_categoria&ofertas=1&nombre=Ofertas';
            }
        });
    }

    function realizarBusqueda(term) {
        if (!window.productosBD) return;

        const resultados = window.productosBD.filter(p => p.titulo.toLowerCase().includes(term));
        const suggestions = new Set();
        if (resultados.length > 0) {
            resultados.forEach(p => {
                const words = p.titulo.toLowerCase().split(/\s+/);
                words.forEach(w => {
                    if (w.includes(term)) {
                        suggestions.add(w);
                    }
                });
            });
        }

        if (searchSuggestionsList) {
            searchSuggestionsList.innerHTML = '';
            Array.from(suggestions).slice(0, 8).forEach(sug => {
                const li = document.createElement('li');
                li.innerHTML = sug.replace(new RegExp(term, 'gi'), match => `<strong>${match}</strong>`);
                li.addEventListener('click', () => {
                    if (searchInput) {
                        searchInput.value = sug;
                        currentTerm = sug;
                        realizarBusqueda(sug);
                        searchInput.focus();
                    }
                });
                searchSuggestionsList.appendChild(li);
            });

            if (suggestions.size === 0) {
                searchSuggestionsList.innerHTML = '<li>No se encontraron sugerencias</li>';
            }
        }

        if (searchQuickTerm) searchQuickTerm.textContent = term;
        if (searchQuickProducts) {
            searchQuickProducts.innerHTML = '';
            
            // Mostrar imagen, título resaltado y precio del producto
            resultados.slice(0, 6).forEach(p => {
                const itemRow = document.createElement('div');
                itemRow.className = 'search-quick-item';
                itemRow.style.display = 'flex';
                itemRow.style.alignItems = 'center';
                itemRow.style.gap = '12px';
                itemRow.style.padding = '8px 12px';
                itemRow.style.cursor = 'pointer';
                itemRow.style.borderBottom = '1px solid #f1f5f9';
                itemRow.style.borderRadius = '8px';
                itemRow.style.transition = 'background 0.15s ease';

                itemRow.addEventListener('mouseenter', () => itemRow.style.background = '#f8fafc');
                itemRow.addEventListener('mouseleave', () => itemRow.style.background = 'transparent');

                let imgSrc = p.imagen || p.imagen_url || 'Images/logo.png';
                if (!imgSrc || imgSrc.trim() === '') {
                    imgSrc = 'Images/logo.png';
                }
                const highlightedTitle = p.titulo.replace(new RegExp(term, 'gi'), match => `<strong>${match}</strong>`);
                const precio = parseFloat(p.precio || 0).toFixed(2);
                const und = p.unidad_medida ? ` / ${p.unidad_medida.toLowerCase()}` : '';

                itemRow.innerHTML = `
                    <img src="${imgSrc}" alt="${p.titulo}" onerror="this.onerror=null; this.src='Images/logo.png';" style="width: 44px; height: 44px; object-fit: contain; border-radius: 8px; border: 1px solid #e2e8f0; flex-shrink: 0; background: #fff; padding: 2px;">
                    <div style="flex: 1; min-width: 0;">
                        <div style="font-size: 0.88rem; color: #1e293b; line-height: 1.35; white-space: nowrap; overflow: hidden; text-overflow: ellipsis;">
                            ${highlightedTitle}
                        </div>
                        <div style="font-size: 0.80rem; font-weight: 700; color: #15803d; margin-top: 2px;">
                            S/ ${precio}<span style="font-size: 0.72rem; color: #64748b; font-weight: 500;">${und}</span>
                        </div>
                    </div>
                `;
                
                itemRow.addEventListener('click', () => { 
                    window.location.href = `index.php?action=detalle_producto&id=${p.id_producto}`; 
                });
                
                searchQuickProducts.appendChild(itemRow);
            });

            if (resultados.length === 0) {
                searchQuickProducts.innerHTML = '<p style="padding:15px; color:#666; font-size:13px;">No hay productos que coincidan.</p>';
                if (searchViewAllBtn) {
                    searchViewAllBtn.style.display = 'block';
                    searchViewAllBtn.textContent = 'VER PRODUCTOS CON DESCUENTO';
                }
            } else if (searchViewAllBtn) {
                searchViewAllBtn.style.display = 'block';
                searchViewAllBtn.textContent = `VER LOS ${resultados.length} PRODUCTOS`;
            }
        }
    }

    window.mostrarResultadosCompletos = function(term) {
        const cleanTerm = (term || '').trim();
        if (cleanTerm === '') {
            window.location.href = 'index.php?action=ver_categoria&ofertas=1&nombre=Ofertas';
            return;
        }

        if (!searchResultsSection || !mainContent) {
            window.location.href = 'index.php?action=ver_categoria&buscar=' + encodeURIComponent(cleanTerm);
            return;
        }

        if (searchDropdown) {
            searchDropdown.classList.remove('active');
            searchDropdown.style.display = 'none';
        }

        const resultados = window.productosBD ? window.productosBD.filter(p => p.titulo.toLowerCase().includes(cleanTerm.toLowerCase())) : [];

        if (searchResultsTerm) searchResultsTerm.textContent = term;
        if (searchResultsCount) searchResultsCount.textContent = resultados.length;
        
        if (mainContent) mainContent.style.display = 'none';
        if (searchResultsSection) searchResultsSection.style.display = 'block';
        
        if (searchResultsGrid) {
            searchResultsGrid.innerHTML = '';

            if (resultados.length === 0) {
                searchResultsGrid.innerHTML = '<div style="grid-column: 1/-1; text-align: center; padding: 50px;"><h3>No se encontraron productos</h3><p>Intenta con otros términos de búsqueda.</p></div>';
                const footer = document.getElementById('searchResultsFooter');
                if (footer) footer.style.display = 'none';
                return;
            }

            resultados.sort((a, b) => parseInt(b.es_sugerencia) - parseInt(a.es_sugerencia));

            resultados.forEach(producto => {
                const card = document.createElement('div');
                const isSugerencia = parseInt(producto.es_sugerencia) === 1;
                
                card.className = isSugerencia ? 'producto-card sugerencia-card' : 'producto-card';
                
                const precioFormatted = parseFloat(producto.precio).toFixed(2);
                const tieneDesc = producto.precio_anterior && parseFloat(producto.precio_anterior) > parseFloat(producto.precio);
                const pctDesc = tieneDesc ? Math.round(((parseFloat(producto.precio_anterior) - parseFloat(producto.precio)) / parseFloat(producto.precio_anterior)) * 100) : 0;
                const badgeHtml = (tieneDesc && pctDesc > 0) ? `<span class="badge-descuento-porcentual">-${pctDesc}%</span>` : '';
                const oldPriceHtml = tieneDesc ? `<span class="product-card-old-price">S/ ${parseFloat(producto.precio_anterior).toFixed(2)}</span>` : '';

                const sku = (producto.sku || producto.codigo_sku || ('00' + String(producto.id_producto || 1000).padStart(4, '0'))).trim();

                const tit = (producto.titulo || '').toLowerCase();
                let unit = '1 und';
                if (producto.unidad_medida === 'KG') {
                    if (tit.includes('saco') || tit.includes('arroz')) unit = 'Saco 5 kg';
                    else unit = '1 kg';
                } else if (tit.includes('aceite') || tit.includes('botella')) {
                    unit = 'Botella 1 L';
                } else if (tit.includes('pack') || tit.includes('leche') || tit.includes('galleta')) {
                    unit = 'Pack 6 und';
                } else if (tit.includes('jaba') || tit.includes('huevo')) {
                    unit = 'Jaba 30 und';
                } else if (tit.includes('papel')) {
                    unit = 'Pack 4 und';
                }

                card.innerHTML = `
                    <a href="index.php?action=detalle_producto&id=${producto.id_producto}" class="product-card-link">
                        <div class="producto-img">
                            ${badgeHtml}
                            <img src="${producto.imagen || 'Imagenes/producto-placeholder.png'}" alt="${producto.titulo}">
                        </div>

                        <span class="product-card-sku">SKU: ${sku}</span>
                        <h3 class="producto-titulo">${producto.titulo}</h3>
                        <span class="product-card-unit">${unit}</span>
                    </a>

                    <div class="product-card-price-row">
                        <span class="product-card-price">S/ ${precioFormatted}</span>
                        ${oldPriceHtml}
                    </div>

                    <div class="product-card-actions-row">
                        <div class="product-qty-pill" onclick="event.stopPropagation();">
                            <button type="button" class="btn-qty-minus" onclick="if(typeof cambiarCardQty==='function') cambiarCardQty(this, -1)">-</button>
                            <span class="card-qty-val">1</span>
                            <button type="button" class="btn-qty-plus" onclick="if(typeof cambiarCardQty==='function') cambiarCardQty(this, 1)">+</button>
                        </div>
                        <button type="button" class="btn-add-cart-pill btn-agregar-carrito" 
                                data-id-producto="${producto.id_producto}"
                                onclick="event.stopPropagation(); if(typeof agregarConQty==='function') agregarConQty(this, ${producto.id_producto});">
                            <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                                <circle cx="9" cy="21" r="1"></circle>
                                <circle cx="20" cy="21" r="1"></circle>
                                <path d="M1 1h4l2.68 13.39a2 2 0 0 0 2 1.61h9.72a2 2 0 0 0 2-1.61L23 6H6"></path>
                            </svg>
                            <span>Agregar</span>
                        </button>
                    </div>
                `;
                searchResultsGrid.appendChild(card);
            });
        }

        const footer = document.getElementById('searchResultsFooter');
        if (footer) {
            footer.style.display = resultados.length > 20 ? 'flex' : 'none';
            if (resultados.length > 20) {
                const showingText = document.getElementById('searchShowingText');
                const progressFill = document.getElementById('searchProgressFill');
                if (showingText) showingText.textContent = `Mostrando ${Math.min(20, resultados.length)} de ${resultados.length}`;
                if (progressFill) progressFill.style.width = `${(Math.min(20, resultados.length) / resultados.length) * 100}%`;
            }
        }
        
        window.scrollTo({ top: 0, behavior: 'smooth' });
    };

    window.cerrarBusqueda = function() {
        if (searchResultsSection) searchResultsSection.style.display = 'none';
        if (mainContent) mainContent.style.display = '';
        if (searchInput) searchInput.value = '';
        if (searchClear) searchClear.style.display = 'none';
    };

    const notifDropdown = document.getElementById('notifDropdown');
    const notifOverlay = document.getElementById('notifOverlay');
    const notifItems = document.getElementById('notifItems');
    const notifEmpty = document.getElementById('notifEmpty');
    const notifBadge = document.getElementById('notifBadge');

    window.toggleNotificaciones = function(e) {
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
                if(cartOverlay) cartOverlay.classList.remove('active');
                document.body.classList.remove('cart-open');
            }
            
            dropdown.classList.add('active');
            if (overlay) overlay.classList.add('active');
            cargarNotificaciones();
        }
    };

    window.cerrarNotificaciones = function(e) {
        if (e) {
            e.preventDefault();
            e.stopPropagation();
        }
        const dropdown = document.getElementById('notifDropdown');
        const overlay = document.getElementById('notifOverlay');
        if (dropdown) dropdown.classList.remove('active');
        if (overlay) overlay.classList.remove('active');
    };

    function cargarNotificaciones() {
        if (!notifItems) return;
        // Si el drawer ya tiene items renderizados por PHP, conservarlos para carga instantánea
        if (notifItems.querySelector('.notif-item-row')) {
            return;
        }

        notifItems.innerHTML = `
            <div style="padding:40px 20px;text-align:center;color:#64748b;">
                <div style="display:inline-flex;margin-bottom:10px;animation:spin 1s linear infinite;color:#15803d;">
                    <svg xmlns="http://www.w3.org/2000/svg" width="28" height="28" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"><path d="M21 12a9 9 0 1 1-6.219-8.56"/></svg>
                </div>
                <div style="font-size:0.9rem;font-weight:600;">Cargando notificaciones...</div>
            </div>
        `;
        if (notifEmpty) notifEmpty.style.display = 'none';

        fetch('index.php?action=listar_pedidos_usuario', {
            headers: { 'X-Requested-With': 'XMLHttpRequest' }
        })
        .then(res => {
            if (!res.ok) throw new Error('No autenticado o error del servidor');
            const ct = res.headers.get('content-type') || '';
            if (!ct.includes('application/json')) throw new Error('Respuesta no es JSON');
            return res.json();
        })
        .then(response => {
            if (response.success && response.data && Array.isArray(response.data.pedidos)) {
                const pendientes = response.data.pedidos.filter(pedido =>
                    parseInt(pedido.leido_cliente !== undefined ? pedido.leido_cliente : 0, 10) === 0
                );
                renderNotificaciones(pendientes);
            } else {
                renderNotificacionesVacias();
            }
        })
        .catch(() => {
            renderNotificacionesVacias('No se pudieron cargar las notificaciones.');
        });
    }

    function renderNotificacionesVacias(mensaje = 'Los cambios de estado de tus pedidos aparecerán aquí.') {
        if (!notifItems) return;
        notifItems.innerHTML = `
            <div style="padding:50px 20px;text-align:center;color:#64748b;">
                <div style="margin-bottom:12px;display:inline-flex;align-items:center;justify-content:center;width:64px;height:64px;border-radius:50%;background:#f0fdf4;color:#15803d;border:1px solid #bbf7d0;">
                    <svg xmlns="http://www.w3.org/2000/svg" width="30" height="30" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M18 8a6 6 0 0 0-12 0c0 7-3 9-3 9h18s-3-2-3-9"></path><path d="M13.73 21a2 2 0 0 1-3.46 0"></path></svg>
                </div>
                <strong style="font-size:1rem;color:#0f172a;display:block;">No tienes notificaciones nuevas</strong>
                <p style="font-size:0.85rem;margin-top:4px;">${mensaje}</p>
            </div>
        `;
    }

    function renderNotificaciones(pedidos) {
        if (!notifItems) return;
        notifItems.innerHTML = '';
        
        if (pedidos.length === 0) {
            renderNotificacionesVacias();
            return;
        }

        pedidos.forEach(pedido => {
            const rawEst = pedido.estado ? pedido.estado.trim().toLowerCase() : 'pendiente';
            const isUnread = parseInt(pedido.leido_cliente !== undefined ? pedido.leido_cliente : 0, 10) === 0;
            
            let colorEstado = '#64748b';
            let badgeText = pedido.estado || 'Pendiente';
            switch (rawEst) {
                case 'en camino':
                    colorEstado = '#0284c7';
                    badgeText = 'En camino';
                    break;
                case 'en preparacion':
                case 'en preparación':
                    colorEstado = '#4f46e5';
                    badgeText = 'En preparación';
                    break;
                case 'pendiente':
                    colorEstado = '#d97706';
                    badgeText = 'Pendiente';
                    break;
                case 'entregado':
                    colorEstado = '#15803d';
                    badgeText = 'Entregado';
                    break;
                case 'cancelado':
                case 'rechazado':
                    colorEstado = '#dc2626';
                    badgeText = 'Cancelado';
                    break;
            }

            const fechaFormat = new Date(pedido.fecha).toLocaleString('es-PE', {
                year: 'numeric', month: 'short', day: 'numeric',
                hour: '2-digit', minute: '2-digit'
            });

            const div = document.createElement('div');
            div.className = `stock-dropdown-item notif-item-row ${isUnread ? 'notif-unread' : 'notif-read'}`;
            div.id = `notifItemRow_${pedido.id_pedido}`;
            div.setAttribute('data-leido', isUnread ? '0' : '1');
            div.style.cssText = `cursor:pointer; padding: 16px 20px; border-bottom: 1px solid #f1f5f9; display: flex; gap: 14px; align-items: flex-start; transition: all 0.2s ease; background-color: ${isUnread ? '#f0fdf4' : '#ffffff'}; border-left: ${isUnread ? '3.5px solid #15803d' : '3.5px solid transparent'};`;
            
            div.addEventListener('mouseenter', () => div.style.backgroundColor = '#f1f5f9');
            div.addEventListener('mouseleave', () => {
                div.style.backgroundColor = (div.getAttribute('data-leido') === '0' ? '#f0fdf4' : '#ffffff');
            });
            div.addEventListener('click', () => {
                if (typeof window.leerNotificacionPedido === 'function') {
                    window.leerNotificacionPedido(pedido.id_pedido, `index.php?action=detalle_pedido_usuario&id=${pedido.id_pedido}`);
                } else {
                    window.location.href = `index.php?action=detalle_pedido_usuario&id=${pedido.id_pedido}`;
                }
            });

            const iconBoxBg = isUnread ? '#dcfce7' : '#f8fafc';
            const iconBoxBorder = isUnread ? '#86efac' : '#e2e8f0';
            const iconBoxColor = isUnread ? '#15803d' : '#94a3b8';

            const iconSvg = `
                <div class="notif-icon-box" style="width: 42px; height: 42px; background: ${iconBoxBg}; border: 1px solid ${iconBoxBorder}; border-radius: 12px; display: flex; align-items: center; justify-content: center; color: ${iconBoxColor}; flex-shrink: 0; transition: all 0.2s ease;">
                    <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <path d="M6 2 3 6v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2V6l-3-4z"></path>
                        <line x1="3" y1="6" x2="21" y2="6"></line>
                        <path d="M16 10a4 4 0 0 1-8 0"></path>
                    </svg>
                </div>
            `;

            const pillNuevo = isUnread ? `<span class="notif-pill-nuevo" style="background:#dcfce7;color:#15803d;font-size:0.68rem;font-weight:700;padding:2px 7px;border-radius:10px;border:1px solid #86efac;letter-spacing:0.3px;">Nuevo</span>` : '';

            div.innerHTML = `
                ${iconSvg}
                <div style="flex: 1; min-width: 0;">
                    <div style="display: flex; justify-content: space-between; align-items: center; gap: 8px;">
                        <div style="display:flex; align-items:center; gap:6px;">
                            <strong class="notif-pedido-titulo" style="font-size: 0.95rem; color: ${isUnread ? '#0f172a' : '#334155'}; font-weight: ${isUnread ? '700' : '600'};">Pedido #${String(pedido.id_pedido).padStart(5, '0')}</strong>
                            ${pillNuevo}
                        </div>
                        <small style="font-size: 0.74rem; color: #94a3b8; flex-shrink:0;">${fechaFormat}</small>
                    </div>
                    <p style="margin: 4px 0; color: #334155; font-size: 0.85rem; font-weight: 600;">Total: S/ ${parseFloat(pedido.total || 0).toFixed(2)} - ${pedido.tipo_entrega || 'Delivery'}</p>
                    <div style="display: flex; justify-content: space-between; align-items: center; margin-top: 6px;">
                        <span class="notif-estado-texto" style="color:${colorEstado};font-size:0.8rem;font-weight:700;letter-spacing:0.2px;">${badgeText}</span>
                        <span style="font-size: 0.82rem; color: #15803d; font-weight: 700;">Ver detalle →</span>
                    </div>
                </div>
            `;
            notifItems.appendChild(div);
        });
    }

    fetch('index.php?action=listar_pedidos_usuario', {
        headers: { 'X-Requested-With': 'XMLHttpRequest' }
    })
    .then(res => {
        if (!res.ok) throw new Error('No autenticado');
        const ct = res.headers.get('content-type') || '';
        if (!ct.includes('application/json')) throw new Error('Respuesta no es JSON');
        return res.json();
    })
    .then(response => {
        if (response.success && response.data && response.data.pedidos) {
            const pedidos = response.data.pedidos;
            let unreadCount = 0;
            pedidos.forEach(p => {
                const est = p.estado ? p.estado.toLowerCase() : '';
                const leido = parseInt(p.leido_cliente !== undefined ? p.leido_cliente : 0, 10);
                if (leido === 0) {
                    unreadCount++;
                }
            });
            if (notifBadge) {
                notifBadge.textContent = unreadCount > 0 ? unreadCount : '0';
                notifBadge.style.display = unreadCount > 0 ? 'flex' : 'none';
            }
        }
    }).catch(() => { /* Sin sesión activa, no mostrar badge */ });
});

// ==========================================
// CONTROL GLOBAL DE MENÚ HAMBURGUESA Y CARRITO (MÓVIL Y DESKTOP)
// ==========================================
document.addEventListener('click', (e) => {
    // 1. Abrir Menú Hamburguesa Móvil
    const btnOpenSidebar = e.target.closest('#openSidebar, .btn-menu-mobile');
    if (btnOpenSidebar) {
        e.preventDefault();
        const sidebar = document.getElementById('mobileSidebar');
        const overlay = document.getElementById('sidebarOverlay');
        if (sidebar) sidebar.classList.add('active', 'open');
        if (overlay) overlay.classList.add('active', 'open');
        document.body.style.overflow = 'hidden';
        return;
    }

    // 2. Cerrar Menú Hamburguesa Móvil
    const btnCloseSidebar = e.target.closest('#closeSidebar, .sidebar-close, #sidebarOverlay');
    if (btnCloseSidebar) {
        e.preventDefault();
        const sidebar = document.getElementById('mobileSidebar');
        const overlay = document.getElementById('sidebarOverlay');
        if (sidebar) sidebar.classList.remove('active', 'open');
        if (overlay) overlay.classList.remove('active', 'open');
        document.body.style.overflow = '';
        return;
    }

    // 3. Abrir Carrito de Compras (Delegado para Desktop y Móvil, Autenticado o Invitado)
    const btnCart = e.target.closest('#btnAbrirCarrito, #btnAbrirCarritoMobile, .btn-cart');
    if (btnCart) {
        e.preventDefault();
        if (typeof window.abrirCarrito === 'function') {
            window.abrirCarrito();
        } else {
            const cartSidebar = document.getElementById('cartSidebar');
            const cartOverlay = document.getElementById('cartOverlay');
            if (cartSidebar) cartSidebar.classList.add('active');
            if (cartOverlay) cartOverlay.classList.add('active');
            document.body.classList.add('cart-open');
        }
        return;
    }
});

window.cerrarSidebar = function() {
    const sidebar = document.getElementById('mobileSidebar');
    const overlay = document.getElementById('sidebarOverlay');
    if (sidebar) sidebar.classList.remove('active', 'open');
    if (overlay) overlay.classList.remove('active', 'open');
    document.body.style.overflow = '';
};

window.toggleSidebarAccordion = function(btn) {
    const parent = btn.closest('.sidebar-acc-item, .sidebar-subacc-item');
    if (!parent) return;
    parent.classList.toggle('open');
};

