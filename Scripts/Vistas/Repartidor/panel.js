(() => {
    document.querySelectorAll('[data-card-action]').forEach((action) => action.addEventListener('click', (event) => event.stopPropagation()));
    document.querySelectorAll('[data-detail-url] a, [data-detail-url] button, [data-detail-url] input, [data-detail-url] textarea, [data-detail-url] select').forEach((control) => control.addEventListener('click', (event) => event.stopPropagation()));
    document.querySelectorAll('[data-detail-url]').forEach((card) => {
        const openPage = () => { window.location.href = card.dataset.detailUrl; };
        card.addEventListener('click', openPage);
        card.addEventListener('keydown', (event) => {
            if (event.target !== card) return;
            if (event.key === 'Enter' || event.key === ' ') {
                event.preventDefault();
                openPage();
            }
        });
    });

    document.querySelectorAll('[data-filter-toggle]').forEach((button) => {
        button.addEventListener('click', () => {
            const container = button.closest('.delivery-card-filters, .delivery-history-filters');
            const panel = container?.querySelector('[data-filter-panel]');
            if (!panel) return;
            panel.hidden = !panel.hidden;
            button.setAttribute('aria-expanded', String(!panel.hidden));
            button.classList.toggle('is-active', !panel.hidden);
        });
    });

    document.querySelectorAll('[data-filter-scope]').forEach((filters) => {
        const section = filters.nextElementSibling;
        if (!section) return;
        const cards = Array.from(section.querySelectorAll('.delivery-card-filter-item'));
        const search = filters.querySelector('[data-filter-search]');
        const payment = filters.querySelector('[data-filter-payment]');
        const status = filters.querySelector('[data-filter-status]');
        const count = section.querySelector('[data-filter-count]');
        const apply = () => {
            const query = (search?.value || '').trim().toLocaleLowerCase();
            let visible = 0;
            cards.forEach((card) => {
                const show = (!query || (card.dataset.search || '').includes(query))
                    && (!payment?.value || card.dataset.payment === payment.value)
                    && (!status?.value || card.dataset.status === status.value);
                card.hidden = !show;
                if (show) visible += 1;
            });
            if (count) count.textContent = String(visible);
        };
        [search, payment, status].filter(Boolean).forEach((control) => {
            control.addEventListener('input', apply);
            control.addEventListener('change', apply);
        });
        filters.querySelector('[data-filter-clear]')?.addEventListener('click', () => {
            if (search) search.value = '';
            if (payment) payment.value = '';
            if (status) status.value = '';
            apply();
            search?.focus();
        });
    });

    const confirmModal = document.getElementById('deliveryConfirmModal');
    const confirmTitle = document.getElementById('deliveryConfirmTitle');
    const confirmMessage = document.getElementById('deliveryConfirmMessage');
    const confirmSubmit = document.getElementById('deliveryConfirmSubmit');
    let pendingForm = null;
    let pendingSubmitter = null;
    const closeConfirm = () => {
        if (confirmModal) confirmModal.hidden = true;
        pendingForm = null;
        pendingSubmitter = null;
    };
    document.querySelectorAll('[data-confirm-action]').forEach((formElement) => {
        formElement.addEventListener('submit', (event) => {
            if (formElement.dataset.confirmed === '1') {
                delete formElement.dataset.confirmed;
                return;
            }
            event.preventDefault();
            pendingForm = formElement;
            pendingSubmitter = event.submitter || formElement.querySelector('[type="submit"]');
            if (confirmTitle) confirmTitle.textContent = formElement.dataset.confirmTitle || 'Confirmar acción';
            if (confirmMessage) confirmMessage.textContent = formElement.dataset.confirmMessage || '¿Deseas continuar?';
            if (confirmModal) confirmModal.hidden = false;
        });
    });
    confirmSubmit?.addEventListener('click', () => {
        if (!pendingForm) return;
        const formElement = pendingForm;
        const submitter = pendingSubmitter;
        formElement.dataset.confirmed = '1';
        closeConfirm();
        formElement.requestSubmit(submitter || undefined);
    });
    document.querySelectorAll('[data-close-confirm]').forEach((button) => button.addEventListener('click', closeConfirm));
    confirmModal?.addEventListener('click', (event) => { if (event.target === confirmModal) closeConfirm(); });

    const modal = document.getElementById('deliveryFailedModal');
    const form = document.getElementById('deliveryFailedForm');
    const idInput = document.getElementById('deliveryFailedId');
    const orderText = document.getElementById('deliveryFailedOrder');
    const notes = document.getElementById('deliveryFailedNotes');
    if (!modal || !form || !idInput || !notes) return;

    const close = () => { modal.hidden = true; form.reset(); };
    document.querySelectorAll('.js-delivery-failed').forEach((button) => {
        button.addEventListener('click', () => {
            idInput.value = button.dataset.idEnvio || '';
            orderText.textContent = `Pedido ${button.dataset.pedido || ''}`;
            modal.hidden = false;
            window.setTimeout(() => notes.focus(), 0);
        });
    });
    document.querySelectorAll('[data-close-failed]').forEach((button) => button.addEventListener('click', close));
    modal.addEventListener('click', (event) => { if (event.target === modal) close(); });

    const incidentModal = document.getElementById('deliveryIncidentModal');
    const incidentForm = document.getElementById('deliveryIncidentForm');
    const incidentId = document.getElementById('deliveryIncidentId');
    const incidentOrder = document.getElementById('deliveryIncidentOrder');
    const incidentNotes = document.getElementById('deliveryIncidentNotes');
    const closeIncident = () => {
        if (incidentModal) incidentModal.hidden = true;
        incidentForm?.reset();
    };
    if (incidentModal && incidentForm && incidentId && incidentNotes) {
        document.querySelectorAll('.js-edit-incident').forEach((button) => {
            button.addEventListener('click', (event) => {
                event.stopPropagation();
                incidentId.value = button.dataset.idEnvio || '';
                if (incidentOrder) incidentOrder.textContent = `Pedido ${button.dataset.pedido || ''}`;
                incidentNotes.value = button.dataset.notas || '';
                incidentModal.hidden = false;
                window.setTimeout(() => incidentNotes.focus(), 0);
            });
        });
        document.querySelectorAll('[data-close-incident]').forEach((button) => button.addEventListener('click', closeIncident));
        incidentModal.addEventListener('click', (event) => { if (event.target === incidentModal) closeIncident(); });
    }

    document.addEventListener('keydown', (event) => {
        if (event.key !== 'Escape') return;
        closeConfirm();
        if (!modal.hidden) close();
        closeIncident();
    });
})();
