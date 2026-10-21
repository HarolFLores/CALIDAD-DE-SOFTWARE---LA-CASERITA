/* ==========================================================================
   PERFIL — La Caserita (Admin / Cajero / Repartidor)
   Lógica compartida de la vista de perfil. Vanilla JS, sin jQuery.
   ========================================================================== */
(function () {
    'use strict';

    function onReady(fn) {
        if (document.readyState === 'loading') {
            document.addEventListener('DOMContentLoaded', fn);
        } else {
            fn();
        }
    }

    function mpToast(type, title, message) {
        if (typeof SPA !== 'undefined' && SPA && typeof SPA.toast === 'function') {
            SPA.toast(type, title, message);
            return;
        }
        var host = document.querySelector('.mp-toast-host');
        if (!host) {
            host = document.createElement('div');
            host.className = 'mp-toast-host';
            document.body.appendChild(host);
        }
        var toast = document.createElement('div');
        toast.className = 'mp-toast' + (type === 'error' ? ' is-error' : '');
        var head = document.createElement('strong');
        head.textContent = title || '';
        var body = document.createElement('span');
        body.textContent = message || '';
        toast.appendChild(head);
        toast.appendChild(body);
        host.appendChild(toast);
        window.setTimeout(function () {
            toast.classList.add('is-out');
            window.setTimeout(function () { toast.remove(); }, 260);
        }, 3800);
    }

    function mpPost(url, data) {
        return fetch(url, {
            method: 'POST',
            headers: {
                'X-Requested-With': 'XMLHttpRequest',
                'Content-Type': 'application/x-www-form-urlencoded; charset=UTF-8'
            },
            body: new URLSearchParams(data).toString()
        }).then(function (res) {
            return res.json().catch(function () { return null; }).then(function (json) {
                return { ok: res.ok, status: res.status, json: json };
            });
        });
    }

    var mpFieldInputs = {
        nombre: 'mpNombre',
        apellido: 'mpApellido',
        correo: 'mpCorreo',
        telefono: 'mpTelefono',
        password_actual: 'mpPassActual',
        password_nueva: 'mpPassNueva',
        password_confirmacion: 'mpPassConfirm'
    };

    function clearFieldErrors(scope) {
        scope.querySelectorAll('[data-error-for]').forEach(function (el) {
            el.textContent = '';
        });
        scope.querySelectorAll('.mp-error').forEach(function (el) {
            el.classList.remove('mp-error');
        });
    }

    function applyFieldErrors(scope, errors) {
        Object.keys(errors || {}).forEach(function (field) {
            var msgEl = scope.querySelector('[data-error-for="' + field + '"]');
            if (msgEl) msgEl.textContent = errors[field];
            var inputId = mpFieldInputs[field];
            var input = inputId ? document.getElementById(inputId) : null;
            if (input) input.classList.add('mp-error');
        });
    }

    onReady(function () {
        var toggleEdit = document.getElementById('mpToggleEdit');
        var accionesPerfil = document.getElementById('accionesPerfil');
        var formPerfil = document.getElementById('mpFormPerfil');
        var camposEditar = ['mpNombre', 'mpApellido', 'mpTelefono']
            .map(function (id) { return document.getElementById(id); })
            .filter(Boolean);

        if (toggleEdit && accionesPerfil) {
            toggleEdit.addEventListener('change', function () {
                var editando = this.checked;
                camposEditar.forEach(function (input) {
                    input.readOnly = !editando;
                });
                accionesPerfil.classList.toggle('is-visible', editando);
                if (!editando) clearFieldErrors(formPerfil);
            });
        }

        if (formPerfil) {
            formPerfil.addEventListener('submit', function (e) {
                e.preventDefault();
                clearFieldErrors(formPerfil);

                mpPost(formPerfil.action, new FormData(formPerfil)).then(function (r) {
                    var json = r.json || {};
                    if (r.ok && json.success) {
                        mpToast('success', 'Éxito', json.message || 'Datos actualizados.');
                        var nuevoNombre = (document.getElementById('mpNombre') || {}).value || '';
                        var apellido = (document.getElementById('mpApellido') || {}).value || '';
                        var hero = document.getElementById('mpHeroName');
                        if (hero) hero.textContent = (nuevoNombre + ' ' + apellido).trim();
                        document.querySelectorAll('.sidebar-user-name').forEach(function (el) {
                            el.textContent = (nuevoNombre + ' ' + apellido).trim();
                        });
                        if (toggleEdit && toggleEdit.checked) toggleEdit.checked = false;
                        camposEditar.forEach(function (input) { input.readOnly = true; });
                        if (accionesPerfil) accionesPerfil.classList.remove('is-visible');
                    } else if (r.status === 422) {
                        applyFieldErrors(formPerfil, json.errors || {});
                        mpToast('error', 'Datos inválidos', 'Corrige los errores en el formulario.');
                    } else {
                        mpToast('error', 'Error', json.message || 'No se pudieron actualizar tus datos.');
                    }
                }).catch(function () {
                    mpToast('error', 'Error', 'Ocurrió un error en la comunicación con el servidor.');
                });
            });
        }

        /* ---------------- Contraseña ---------------- */
        var formPassword = document.getElementById('mpFormPassword');
        var btnVerificar = document.getElementById('btnVerificarPassword');
        var passActual = document.getElementById('mpPassActual');
        var passNueva = document.getElementById('mpPassNueva');
        var passConfirm = document.getElementById('mpPassConfirm');
        var accionesPassword = document.getElementById('accionesPassword');
        var btnCancelar = document.getElementById('btnCancelarPassword');
        var lockMsg = document.getElementById('mpLockMsg');
        var verifyRow = document.getElementById('mpVerifyRow');
        var matchHint = document.getElementById('mpMatchHint');
        var lockIntervalo = null;

        document.querySelectorAll('.mp-eye').forEach(function (btn) {
            btn.addEventListener('click', function () {
                var input = document.getElementById(this.getAttribute('data-target'));
                if (!input) return;
                var revelar = input.type === 'password';
                input.type = revelar ? 'text' : 'password';
                this.classList.toggle('is-revealed', revelar);
                this.setAttribute('aria-label', revelar ? 'Ocultar contraseña' : 'Mostrar contraseña');
            });
        });

        var tipsPassword = document.getElementById('mpTipsPassword');
        var tipsRules = tipsPassword ? tipsPassword.querySelectorAll('[data-mp-rule]') : [];

        function actualizarTipsPassword() {
            if (!tipsPassword || !passNueva) return;
            var nueva = passNueva.value || '';
            var reglas = {
                length: nueva.length >= 8,
                upper: /[A-Z]/.test(nueva),
                num: /[0-9]/.test(nueva),
                special: /[^A-Za-z0-9]/.test(nueva)
            };
            tipsRules.forEach(function (item) {
                item.classList.toggle('valid', reglas[item.getAttribute('data-mp-rule')] === true);
            });
            var todoCompleto = reglas.length && reglas.upper && reglas.num && reglas.special;
            tipsPassword.classList.toggle('is-visible', !passNueva.readOnly && !todoCompleto);
        }

        function actualizarVisibilidadTips() {
            actualizarTipsPassword();
        }

        if (passNueva) passNueva.addEventListener('input', actualizarTipsPassword);

        function iniciarBloqueo(segundos) {
            if (!btnVerificar || !passActual || !lockMsg) return;
            if (lockIntervalo) window.clearInterval(lockIntervalo);
            passActual.disabled = true;
            btnVerificar.disabled = true;
            lockMsg.classList.add('is-visible');

            function pintar() {
                lockMsg.textContent = 'Por el momento no puede cambiar la contraseña, bloqueado por ' + segundos + ' segundos.';
            }
            pintar();
            lockIntervalo = window.setInterval(function () {
                segundos--;
                if (segundos <= 0) {
                    window.clearInterval(lockIntervalo);
                    lockIntervalo = null;
                    passActual.disabled = false;
                    passActual.value = '';
                    btnVerificar.disabled = false;
                    lockMsg.classList.remove('is-visible');
                    lockMsg.textContent = '';
                } else {
                    pintar();
                }
            }, 1000);
        }

        if (formPassword) {
            var lockInicial = parseInt(formPassword.getAttribute('data-lock-seconds') || '0', 10);
            if (lockInicial > 0) iniciarBloqueo(lockInicial);
        }

        if (btnVerificar && formPassword && passActual) {
            btnVerificar.addEventListener('click', function () {
                if (btnVerificar.disabled) return;
                var actual = passActual.value.trim();
                if (!actual) {
                    mpToast('error', 'Error', 'Ingresa tu contraseña actual primero.');
                    return;
                }
                var csrf = (formPassword.querySelector('input[name="csrf_token"]') || {}).value || '';
                btnVerificar.disabled = true;
                btnVerificar.textContent = 'Verificando...';

                mpPost(formPassword.getAttribute('data-verify-action'), {
                    password_actual: actual,
                    csrf_token: csrf
                }).then(function (r) {
                    var json = r.json || {};
                    if (r.ok && json.success) {
                        mpToast('success', 'Éxito', json.message || 'Contraseña verificada.');
                        passActual.readOnly = true;
                        if (verifyRow) verifyRow.classList.add('is-verified');
                        if (passNueva) passNueva.readOnly = false;
                        if (passConfirm) passConfirm.readOnly = false;
                        if (accionesPassword) accionesPassword.classList.add('is-visible');
                        actualizarVisibilidadTips();
                    } else {
                        mpToast('error', 'Error', json.message || 'La contraseña actual es incorrecta.');
                        var match = (json.message || '').match(/bloqueado por (\d+) segundos/);
                        if (match) iniciarBloqueo(parseInt(match[1], 10));
                    }
                }).catch(function () {
                    mpToast('error', 'Error', 'Ocurrió un error en la comunicación con el servidor.');
                }).finally(function () {
                    btnVerificar.disabled = false;
                    btnVerificar.textContent = 'Verificar';
                });
            });
        }

        function reiniciarPasswordForm() {
            if (!formPassword) return;
            clearFieldErrors(formPassword);
            formPassword.reset();
            if (passActual) passActual.readOnly = false;
            if (verifyRow) verifyRow.classList.remove('is-verified');
            if (btnVerificar) {
                btnVerificar.disabled = false;
                btnVerificar.textContent = 'Verificar';
            }
            if (passNueva) passNueva.readOnly = true;
            if (passConfirm) passConfirm.readOnly = true;
            if (accionesPassword) accionesPassword.classList.remove('is-visible');
            actualizarVisibilidadTips();
            if (passConfirm) passConfirm.classList.remove('mp-match-ok', 'mp-match-bad');
            if (matchHint) {
                matchHint.classList.remove('is-ok', 'is-bad');
                matchHint.textContent = 'Asegúrate de que coincida con la nueva contraseña.';
            }
        }

        if (btnCancelar) {
            btnCancelar.addEventListener('click', reiniciarPasswordForm);
        }

        function validarCoincidencia() {
            if (!passNueva || !passConfirm || !matchHint) return;
            if (passConfirm.readOnly) return;
            var nueva = passNueva.value;
            var conf = passConfirm.value;
            passConfirm.classList.remove('mp-match-ok', 'mp-match-bad');
            matchHint.classList.remove('is-ok', 'is-bad');
            if (conf.length === 0) {
                matchHint.textContent = 'Asegúrate de que coincida con la nueva contraseña.';
            } else if (nueva !== conf) {
                passConfirm.classList.add('mp-match-bad');
                matchHint.classList.add('is-bad');
                matchHint.textContent = 'Las contraseñas no coinciden.';
            } else {
                passConfirm.classList.add('mp-match-ok');
                matchHint.classList.add('is-ok');
                matchHint.textContent = 'Las contraseñas coinciden.';
            }
        }

        if (passNueva && passConfirm) {
            passNueva.addEventListener('input', validarCoincidencia);
            passConfirm.addEventListener('input', validarCoincidencia);
        }

        if (formPassword) {
            formPassword.addEventListener('submit', function (e) {
                e.preventDefault();
                clearFieldErrors(formPassword);

                var nueva = passNueva ? passNueva.value : '';
                var requisitosOk = nueva.length >= 8 &&
                    /[A-Z]/.test(nueva) &&
                    /[0-9]/.test(nueva) &&
                    /[^A-Za-z0-9]/.test(nueva);
                if (!requisitosOk) {
                    mpToast('error', 'Contraseña débil', 'La contraseña debe tener mínimo 8 caracteres, 1 mayúscula, 1 número y 1 carácter especial.');
                    return;
                }

                mpPost(formPassword.action, new FormData(formPassword)).then(function (r) {
                    var json = r.json || {};
                    if (r.ok && json.success) {
                        mpToast('success', 'Éxito', json.message || 'Contraseña actualizada.');
                        reiniciarPasswordForm();
                    } else if (r.status === 422) {
                        applyFieldErrors(formPassword, json.errors || {});
                        mpToast('error', 'Datos inválidos', 'Corrige los errores en el formulario.');
                    } else {
                        mpToast('error', 'Error', json.message || 'No se pudo actualizar tu contraseña.');
                    }
                }).catch(function () {
                    mpToast('error', 'Error', 'Ocurrió un error en la comunicación con el servidor.');
                });
            });
        }
    });
})();
