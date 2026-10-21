(function () {
    'use strict';

    function iniciar() {
        var form = document.getElementById('cfgForm');
        var fields = document.getElementById('cfgFields');
        var editBtn = document.getElementById('cfgEditBtn');
        var saveBtn = document.getElementById('cfgSaveBtn');
        var toggleLabel = document.getElementById('cfgToggleLabel');
        if (!form || !fields || !editBtn || !saveBtn || !toggleLabel) return;
        if (form.dataset.cfgReady === '1') return;
        form.dataset.cfgReady = '1';

        function setEditMode(editing) {
            fields.disabled = !editing;
            saveBtn.hidden = !editing;
            editBtn.classList.toggle('is-active', editing);
            editBtn.setAttribute('aria-checked', editing ? 'true' : 'false');
            toggleLabel.textContent = editing ? 'Datos editables' : 'Modificar datos';
        }

        editBtn.addEventListener('click', function () {
            if (fields.disabled) {
                setEditMode(true);
                var first = fields.querySelector('input:not([type="hidden"]), select');
                if (first) first.focus();
            } else {
                form.reset();
                setEditMode(false);
            }
        });

        setEditMode(false);
    }

    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', iniciar, { once: true });
    } else {
        iniciar();
    }
})();
