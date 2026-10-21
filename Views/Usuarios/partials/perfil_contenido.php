<?php
/**
 * Partial compartido del perfil (Admin / Cajero / Repartidor).
 * Variables esperadas del controlador:
 *   $usuario          → fila del usuario (nombre, apellido, correo, telefono, rol, activo, fecha_registro)
 *   $csrf_token       → token CSRF
 *   $remainingLock    → segundos restantes de bloqueo de contraseña
 *   $mpRolLabel       → 'Administrador' | 'Cajero' | 'Repartidor'
 *   $mpStats          → array de ['label','value','icon','tone']
 *   $mpPerfilAction   → action del form de datos
 *   $mpPasswordAction → action del form de contraseña
 *   $mpVerifyAction   → action AJAX de verificación de contraseña actual
 */
$usuario = $usuario ?? [];
$csrf_token = $csrf_token ?? ($_SESSION['csrf_token'] ?? '');
$remainingLock = isset($remainingLock) ? (int)$remainingLock : 0;
$mpRolLabel = $mpRolLabel ?? 'Personal';
$mpStats = is_array($mpStats ?? null) ? $mpStats : [];
$mpPerfilAction = $mpPerfilAction ?? 'actualizar_perfil_admin';
$mpPasswordAction = $mpPasswordAction ?? 'cambiar_password_admin';
$mpVerifyAction = $mpVerifyAction ?? 'verificar_password_actual_admin';
$flash_success = $flash_success ?? null;
$flash_error = $flash_error ?? null;

$mpNombreCompleto = trim(($usuario['nombre'] ?? '') . ' ' . ($usuario['apellido'] ?? ''));
$mpInicial = strtoupper(substr(($usuario['nombre'] ?? 'U'), 0, 1));
$mpActivo = (int)($usuario['activo'] ?? 1) === 1;
$mpFechaRegistro = $usuario['fecha_registro'] ?? '';
$mpFechaLabel = '';
if (!empty($mpFechaRegistro)) {
    $mpTs = strtotime($mpFechaRegistro);
    if ($mpTs !== false) {
        $mpFechaLabel = date('d/m/Y', $mpTs);
    }
}

$mpSvg = [
    'shield' => '<path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"></path>',
    'mail' => '<rect x="2" y="4" width="20" height="16" rx="2"></rect><polyline points="22,6 12,13 2,6"></polyline>',
    'phone' => '<path d="M22 16.92v3a2 2 0 0 1-2.18 2 19.79 19.79 0 0 1-8.63-3.07 19.5 19.5 0 0 1-6-6 19.79 19.79 0 0 1-3.07-8.67A2 2 0 0 1 4.11 2h3a2 2 0 0 1 2 1.72c.13.96.36 1.9.7 2.81a2 2 0 0 1-.45 2.11L8.09 9.91a16 16 0 0 0 6 6l1.27-1.27a2 2 0 0 1 2.11-.45c.91.34 1.85.57 2.81.7A2 2 0 0 1 22 16.92z"></path>',
    'calendar' => '<rect x="3" y="4" width="18" height="18" rx="2"></rect><line x1="16" y1="2" x2="16" y2="6"></line><line x1="8" y1="2" x2="8" y2="6"></line><line x1="3" y1="10" x2="21" y2="10"></line>',
    'check' => '<polyline points="20 6 9 17 4 12"></polyline>',
    'checkCircle' => '<circle cx="12" cy="12" r="10"></circle><polyline points="8 12 11 15 16 9"></polyline>',
    'alert' => '<path d="M10.29 3.86L1.82 18a2 2 0 0 0 1.71 3h16.94a2 2 0 0 0 1.71-3L13.71 3.86a2 2 0 0 0-3.42 0z"></path><line x1="12" y1="9" x2="12" y2="13"></line><line x1="12" y1="17" x2="12.01" y2="17"></line>',
    'cash' => '<rect x="2" y="6" width="20" height="12" rx="2"></rect><circle cx="12" cy="12" r="2"></circle><path d="M6 12h.01M18 12h.01"></path>',
    'truck' => '<path d="M5 18H3c-.6 0-1-.4-1-1V7c0-.6.4-1 1-1h10c.6 0 1 .4 1 1v11"></path><path d="M14 9h4l4 4v4c0 .6-.4 1-1 1h-2"></path><circle cx="7" cy="18" r="2"></circle><circle cx="17" cy="18" r="2"></circle>',
    'clock' => '<circle cx="12" cy="12" r="10"></circle><polyline points="12 6 12 12 15 14"></polyline>',
    'receipt' => '<path d="M4 2v20l2-1 2 1 2-1 2 1 2-1 2 1V2l-2 1-2-1-2 1-2-1-2 1Z"></path><path d="M8 7h8M8 11h8M8 15h5"></path>',
    'user' => '<path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"></path><circle cx="12" cy="7" r="4"></circle>',
    'users' => '<path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"></path><circle cx="9" cy="7" r="4"></circle><path d="M23 21v-2a4 4 0 0 0-3-3.87"></path><path d="M16 3.13a4 4 0 0 1 0 7.75"></path>',
    'store' => '<path d="M6 2L3 6v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2V6l-3-4z"></path><line x1="3" y1="6" x2="21" y2="6"></line><path d="M16 10a4 4 0 0 1-8 0"></path>',
    'key' => '<circle cx="7.5" cy="15.5" r="5.5"></circle><path d="m21 2-9.6 9.6"></path><path d="m15.5 7.5 3 3L22 7l-3-3"></path>',
    'lock' => '<rect x="3" y="11" width="18" height="11" rx="2"></rect><path d="M7 11V7a5 5 0 0 1 10 0v4"></path>',
    'unlock' => '<rect x="3" y="11" width="18" height="11" rx="2"></rect><path d="M7 11V7a5 5 0 0 1 9.9-1"></path>',
    'pencil' => '<path d="M17 3a2.85 2.83 0 1 1 4 4L7.5 20.5 2 22l1.5-5.5Z"></path><path d="m15 5 4 4"></path>',
    'save' => '<path d="M19 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h11l5 5v11a2 2 0 0 1-2 2z"></path><polyline points="17 21 17 13 7 13 7 21"></polyline><polyline points="7 3 7 8 15 8"></polyline>',
];
?>
<div class="mp-page">

    <?php if (!empty($flash_success)): ?>
        <div class="mp-flash success">
            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"><?php echo $mpSvg['checkCircle']; ?></svg>
            <?php echo htmlspecialchars($flash_success); ?>
        </div>
    <?php endif; ?>
    <?php if (!empty($flash_error)): ?>
        <div class="mp-flash error">
            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"><?php echo $mpSvg['alert']; ?></svg>
            <?php echo htmlspecialchars($flash_error); ?>
        </div>
    <?php endif; ?>

    <!-- Hero -->
    <section class="mp-hero">
        <div class="mp-hero-main">
            <div class="mp-avatar">
                <div class="mp-avatar-circle"><?php echo htmlspecialchars($mpInicial); ?></div>
                <?php if ($mpActivo): ?>
                    <span class="mp-avatar-status" title="Cuenta activa"></span>
                <?php endif; ?>
            </div>
            <div class="mp-hero-id">
                <span class="mp-hero-eyebrow">Mi cuenta</span>
                <h1 class="mp-hero-name" id="mpHeroName"><?php echo htmlspecialchars($mpNombreCompleto); ?></h1>
                <div class="mp-hero-chips">
                    <span class="mp-chip mp-chip-role">
                        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"><?php echo $mpSvg['shield']; ?></svg>
                        <span class="mp-chip-text"><?php echo htmlspecialchars($mpRolLabel); ?></span>
                    </span>
                    <span class="mp-chip" title="<?php echo htmlspecialchars($usuario['correo'] ?? ''); ?>">
                        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"><?php echo $mpSvg['mail']; ?></svg>
                        <span class="mp-chip-text"><?php echo htmlspecialchars($usuario['correo'] ?? ''); ?></span>
                    </span>
                    <?php if (!empty($usuario['telefono'])): ?>
                        <span class="mp-chip">
                            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"><?php echo $mpSvg['phone']; ?></svg>
                            <span class="mp-chip-text"><?php echo htmlspecialchars($usuario['telefono']); ?></span>
                        </span>
                    <?php endif; ?>
                </div>
            </div>
        </div>
        <div class="mp-hero-side">
            <label class="mp-edit-box" for="mpToggleEdit">
                <span class="mp-edit-label">
                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"><?php echo $mpSvg['pencil']; ?></svg>
                    Modificar datos
                </span>
                <span class="mp-switch">
                    <input type="checkbox" id="mpToggleEdit" aria-label="Modificar datos">
                    <span class="mp-switch-track"></span>
                </span>
            </label>
        </div>
    </section>

    <?php if (!empty($mpStats)): ?>
        <!-- Datos esenciales del rol -->
        <section class="mp-stats">
            <?php foreach ($mpStats as $mpStat): ?>
                <article class="mp-stat">
                    <span class="mp-stat-icon tone-<?php echo htmlspecialchars($mpStat['tone'] ?? 'slate'); ?>">
                        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"><?php echo $mpSvg[$mpStat['icon'] ?? 'info'] ?? $mpSvg['check']; ?></svg>
                    </span>
                    <span class="mp-stat-body">
                        <span class="mp-stat-label"><?php echo htmlspecialchars($mpStat['label'] ?? ''); ?></span>
                        <span class="mp-stat-value" title="<?php echo htmlspecialchars($mpStat['value'] ?? ''); ?>"><?php echo htmlspecialchars($mpStat['value'] ?? ''); ?></span>
                    </span>
                </article>
            <?php endforeach; ?>
        </section>
    <?php endif; ?>

    <div class="mp-grid">

        <!-- Información personal -->
        <section class="mp-card">
            <header class="mp-card-head">
                <span class="mp-card-icon tone-teal">
                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"><?php echo $mpSvg['user']; ?></svg>
                </span>
                <div>
                    <span class="mp-card-eyebrow">Datos personales</span>
                    <h2 class="mp-card-title">Información personal</h2>
                    <p class="mp-card-sub">Mantén actualizados tus datos institucionales de contacto.</p>
                </div>
            </header>
            <div class="mp-card-body">
                <form id="mpFormPerfil" class="mp-form" action="index.php?action=<?php echo htmlspecialchars($mpPerfilAction); ?>" method="POST" autocomplete="off">
                    <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($csrf_token); ?>">

                    <div class="mp-fields">
                        <div class="mp-field">
                            <label class="mp-label" for="mpNombre">Nombre</label>
                            <input type="text" class="mp-input" name="nombre" id="mpNombre" maxlength="80" value="<?php echo htmlspecialchars($usuario['nombre'] ?? ''); ?>" readonly required>
                            <small class="mp-error-text" data-error-for="nombre"></small>
                        </div>
                        <div class="mp-field">
                            <label class="mp-label" for="mpApellido">Apellido</label>
                            <input type="text" class="mp-input" name="apellido" id="mpApellido" maxlength="80" value="<?php echo htmlspecialchars($usuario['apellido'] ?? ''); ?>" readonly required>
                            <small class="mp-error-text" data-error-for="apellido"></small>
                        </div>
                        <div class="mp-field span-2">
                            <label class="mp-label" for="mpCorreo">Correo electrónico (solo lectura)</label>
                            <input type="email" class="mp-input" name="correo" id="mpCorreo" maxlength="120" value="<?php echo htmlspecialchars($usuario['correo'] ?? ''); ?>" readonly required>
                            <small class="mp-error-text" data-error-for="correo"></small>
                        </div>
                        <div class="mp-field span-2">
                            <label class="mp-label" for="mpTelefono">Teléfono</label>
                            <input type="tel" class="mp-input" name="telefono" id="mpTelefono" maxlength="15" value="<?php echo htmlspecialchars($usuario['telefono'] ?? ''); ?>" placeholder="Ej. 987654321" readonly>
                            <small class="mp-error-text" data-error-for="telefono"></small>
                        </div>
                    </div>

                    <div class="mp-actions" id="accionesPerfil">
                        <button type="submit" class="mp-btn mp-btn-primary">
                            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"><?php echo $mpSvg['save']; ?></svg>
                            Guardar cambios
                        </button>
                        <span class="mp-hint">El correo no se puede modificar.</span>
                    </div>
                </form>
            </div>
        </section>

        <!-- Seguridad -->
        <section class="mp-card">
            <header class="mp-card-head">
                <span class="mp-card-icon tone-amber">
                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"><?php echo $mpSvg['key']; ?></svg>
                </span>
                <div>
                    <span class="mp-card-eyebrow">Seguridad</span>
                    <h2 class="mp-card-title">Cambiar contraseña</h2>
                    <p class="mp-card-sub">Protege tu cuenta utilizando una contraseña segura.</p>
                </div>
            </header>
            <div class="mp-card-body">
                <form id="mpFormPassword" class="mp-form" action="index.php?action=<?php echo htmlspecialchars($mpPasswordAction); ?>" method="POST" autocomplete="off" data-verify-action="index.php?action=<?php echo htmlspecialchars($mpVerifyAction); ?>" data-lock-seconds="<?php echo (int)$remainingLock; ?>">
                    <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($csrf_token); ?>">

                    <div class="mp-fields">
                        <div class="mp-field span-2">
                            <label class="mp-label" for="mpPassActual">Contraseña actual</label>
                            <div class="mp-verify-row" id="mpVerifyRow">
                                <div class="mp-input-wrap">
                                    <input type="password" class="mp-input has-eye" name="password_actual" id="mpPassActual" placeholder="Ingresa tu contraseña actual" required>
                                    <button type="button" class="mp-eye" data-target="mpPassActual" aria-label="Mostrar contraseña">
                                        <svg class="eye-closed" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M17.94 17.94A10.07 10.07 0 0 1 12 20c-7 0-11-8-11-8a18.45 18.45 0 0 1 5.06-5.94M9.9 4.24A9.12 9.12 0 0 1 12 4c7 0 11 8 11 8a18.5 18.5 0 0 1-2.16 3.19m-6.72-1.07a3 3 0 1 1-4.24-4.24"></path><line x1="1" y1="1" x2="23" y2="23"></line></svg>
                                        <svg class="eye-open" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"></path><circle cx="12" cy="12" r="3"></circle></svg>
                                    </button>
                                </div>
                                <button type="button" class="mp-btn-verify" id="btnVerificarPassword">Verificar</button>
                            </div>
                            <div class="mp-lock-msg" id="mpLockMsg"></div>
                            <small class="mp-error-text" data-error-for="password_actual"></small>
                        </div>

                        <div class="mp-field span-2">
                            <label class="mp-label" for="mpPassNueva">Nueva contraseña</label>
                            <div class="mp-input-wrap">
                                <input type="password" class="mp-input has-eye" name="password_nueva" id="mpPassNueva" minlength="8" placeholder="Mínimo 8 caracteres" readonly required>
                                <button type="button" class="mp-eye" data-target="mpPassNueva" aria-label="Mostrar contraseña">
                                    <svg class="eye-closed" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M17.94 17.94A10.07 10.07 0 0 1 12 20c-7 0-11-8-11-8a18.45 18.45 0 0 1 5.06-5.94M9.9 4.24A9.12 9.12 0 0 1 12 4c7 0 11 8 11 8a18.5 18.5 0 0 1-2.16 3.19m-6.72-1.07a3 3 0 1 1-4.24-4.24"></path><line x1="1" y1="1" x2="23" y2="23"></line></svg>
                                    <svg class="eye-open" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"></path><circle cx="12" cy="12" r="3"></circle></svg>
                                </button>
                            </div>
                            <small class="mp-error-text" data-error-for="password_nueva"></small>
                        </div>

                        <div class="mp-checklist mp-checklist-inline" id="mpTipsPassword">
                            <div class="mp-checklist-title">La contraseña debe tener:</div>
                            <div class="mp-checklist-item" data-mp-rule="length">
                                <span class="mp-chk-icon">
                                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3" stroke-linecap="round" stroke-linejoin="round"><polyline points="20 6 9 17 4 12"></polyline></svg>
                                </span>
                                <span>Mínimo 8 caracteres</span>
                            </div>
                            <div class="mp-checklist-item" data-mp-rule="upper">
                                <span class="mp-chk-icon">
                                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3" stroke-linecap="round" stroke-linejoin="round"><polyline points="20 6 9 17 4 12"></polyline></svg>
                                </span>
                                <span>Al menos 1 letra mayúscula</span>
                            </div>
                            <div class="mp-checklist-item" data-mp-rule="num">
                                <span class="mp-chk-icon">
                                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3" stroke-linecap="round" stroke-linejoin="round"><polyline points="20 6 9 17 4 12"></polyline></svg>
                                </span>
                                <span>Al menos 1 número</span>
                            </div>
                            <div class="mp-checklist-item" data-mp-rule="special">
                                <span class="mp-chk-icon">
                                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3" stroke-linecap="round" stroke-linejoin="round"><polyline points="20 6 9 17 4 12"></polyline></svg>
                                </span>
                                <span>Al menos 1 carácter especial (@, #, $, %, etc.)</span>
                            </div>
                        </div>

                        <div class="mp-field span-2">
                            <label class="mp-label" for="mpPassConfirm">Confirmar contraseña</label>
                            <div class="mp-input-wrap">
                                <input type="password" class="mp-input has-eye" name="password_confirmacion" id="mpPassConfirm" minlength="8" placeholder="Repite la nueva contraseña" readonly required>
                                <button type="button" class="mp-eye" data-target="mpPassConfirm" aria-label="Mostrar contraseña">
                                    <svg class="eye-closed" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M17.94 17.94A10.07 10.07 0 0 1 12 20c-7 0-11-8-11-8a18.45 18.45 0 0 1 5.06-5.94M9.9 4.24A9.12 9.12 0 0 1 12 4c7 0 11 8 11 8a18.5 18.5 0 0 1-2.16 3.19m-6.72-1.07a3 3 0 1 1-4.24-4.24"></path><line x1="1" y1="1" x2="23" y2="23"></line></svg>
                                    <svg class="eye-open" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"></path><circle cx="12" cy="12" r="3"></circle></svg>
                                </button>
                            </div>
                            <small class="mp-match-hint" id="mpMatchHint">Asegúrate de que coincida con la nueva contraseña.</small>
                            <small class="mp-error-text" data-error-for="password_confirmacion"></small>
                        </div>
                    </div>

                    <div class="mp-actions" id="accionesPassword">
                        <button type="submit" class="mp-btn mp-btn-primary">
                            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"><?php echo $mpSvg['lock']; ?></svg>
                            Guardar contraseña
                        </button>
                        <button type="button" class="mp-btn mp-btn-ghost" id="btnCancelarPassword">Cancelar</button>
                    </div>
                </form>
            </div>
        </section>

    </div>
</div>
