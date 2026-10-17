/**
 * table-kebab-menu.js
 * Manejador global para el menú desplegable (kebab 3 puntos) en todas las tablas.
 * Usa posicionamiento fijo para evitar que el menú sea recortado por contenedores
 * con overflow (tablas con scroll horizontal) en las últimas filas.
 */
(function() {
    'use strict';

    function positionKebabMenu(menu, btn) {
        if (!menu || !btn) return;

        // Medir primero sin transformaciones fijas para obtener el tamaño real
        menu.style.position = 'fixed';
        menu.style.right = 'auto';
        menu.style.bottom = 'auto';
        menu.style.left = '0px';
        menu.style.top = '0px';

        const btnRect = btn.getBoundingClientRect();
        const menuRect = menu.getBoundingClientRect();
        const menuW = menuRect.width;
        const menuH = menuRect.height;
        const vw = window.innerWidth || document.documentElement.clientWidth;
        const vh = window.innerHeight || document.documentElement.clientHeight;
        const gap = 6;

        // Alinear el borde derecho del menú con el borde derecho del botón
        let left = btnRect.right - menuW;
        if (left < 8) left = 8;
        if (left + menuW > vw - 8) left = vw - menuW - 8;
        if (left < 8) left = 8;

        // Abrir hacia abajo; si no cabe, abrir hacia arriba
        let top = btnRect.bottom + gap;
        if (top + menuH > vh - 8 && (btnRect.top - menuH - gap) >= 8) {
            top = btnRect.top - menuH - gap;
        }
        if (top < 8) top = 8;

        menu.style.top = Math.round(top) + 'px';
        menu.style.left = Math.round(left) + 'px';
    }

    function toggleKebab(btn, event) {
        if (event) {
            event.preventDefault();
            event.stopPropagation();
        }

        const container = btn.closest('.table-kebab-container') || btn.closest('.table-kebab-wrapper');
        if (!container) return;

        const menu = container.querySelector('.table-kebab-menu');
        if (!menu) return;

        const isCurrentlyOpen = menu.classList.contains('open');

        // Cerrar todos los demás menús primero
        closeAllKebabs();

        if (!isCurrentlyOpen) {
            btn.classList.add('active');
            menu.classList.add('open');
            positionKebabMenu(menu, btn);
        }
    }

    function closeAllKebabs() {
        document.querySelectorAll('.table-kebab-menu.open').forEach(menu => {
            menu.classList.remove('open', 'drop-up');
            menu.style.position = '';
            menu.style.left = '';
            menu.style.top = '';
            menu.style.right = '';
            menu.style.bottom = '';
        });
        document.querySelectorAll('.table-kebab-btn.active').forEach(btn => {
            btn.classList.remove('active');
        });
    }

    // Event delegation para que funcione con filas dinámicas, paginadas o cargadas por AJAX
    document.addEventListener('click', function(e) {
        const btn = e.target.closest('.table-kebab-btn');
        if (btn) {
            toggleKebab(btn, e);
            return;
        }

        // Si se hace clic dentro del menú en un item ejecutable, cerramos el menú
        const item = e.target.closest('.table-kebab-item') || e.target.closest('.kebab-item');
        if (item) {
            // Permitir que el evento onclick del botón/enlace se ejecute normalmente
            setTimeout(closeAllKebabs, 50);
            return;
        }

        // Clic fuera -> cerrar todo
        if (!e.target.closest('.table-kebab-menu')) {
            closeAllKebabs();
        }
    });

    document.addEventListener('keydown', function(e) {
        if (e.key === 'Escape') {
            closeAllKebabs();
        }
    });

    // Cerrar al hacer scroll en cualquier contenedor con scroll
    window.addEventListener('scroll', function() {
        closeAllKebabs();
    }, true);

    // Reposicionar/cerrar al redimensionar la ventana
    window.addEventListener('resize', function() {
        closeAllKebabs();
    });

    window.closeAllKebabs = closeAllKebabs;
})();
