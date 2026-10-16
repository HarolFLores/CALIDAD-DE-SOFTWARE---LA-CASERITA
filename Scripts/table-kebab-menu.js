/**
 * table-kebab-menu.js
 * Manejador global para el menú desplegable (kebab 3 puntos) en todas las tablas
 */
(function() {
    'use strict';

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

            // Posicionamiento inteligente: si choca con el borde inferior de la pantalla, abrir hacia arriba
            const rect = menu.getBoundingClientRect();
            const viewportHeight = window.innerHeight || document.documentElement.clientHeight;
            if (rect.bottom > viewportHeight - 15) {
                menu.classList.add('drop-up');
            } else {
                menu.classList.remove('drop-up');
            }
        }
    }

    function closeAllKebabs() {
        document.querySelectorAll('.table-kebab-menu.open').forEach(menu => {
            menu.classList.remove('open', 'drop-up');
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

    // Cerrar al hacer scroll en contenedor con scroll
    window.addEventListener('scroll', function() {
        closeAllKebabs();
    }, true);

    window.closeAllKebabs = closeAllKebabs;
})();
