<?php
require_once 'Core/ajaxHelper.php';
if (session_status() === PHP_SESSION_NONE) { session_start(); }
$nombre = htmlspecialchars($_SESSION['usuario_nombre'] ?? 'Administrador');
$flash_success = $_SESSION['flash_success'] ?? null;
$flash_error   = $_SESSION['flash_error']   ?? null;
$avisoAccion = $_GET['aviso'] ?? '';
if (isset($_SESSION)) { unset($_SESSION['flash_success'], $_SESSION['flash_error']); }
if (!isset($categorias_bd)) {
    require_once 'Models/categoryModel.php';
    $catModel = new CategoryModel();
    $categorias_bd = $catModel->getCategoria();
}
if (!isset($moments_bd)) {
    require_once 'Models/momentConModel.php';
    $momModel = new MomentConModel();
    $moments_bd = $momModel->getMomento();
}
if (!isset($grupMomento_bd)) {
    require_once 'Models/grupoMomentModel.php';
    $grupModel = new GrupMomentModel();
    $grupMomento_bd = $grupModel->getGrupo();
}

$countMoments = count($moments_bd);
$countGrupos = count($grupMomento_bd);
$countSubcats = count($categorias_bd);

$countGruposActivos = 0;
$countGruposInactivos = 0;
if (!empty($grupMomento_bd)) {
    foreach ($grupMomento_bd as $g) {
        $act = !isset($g['activo']) || $g['activo'] == 1;
        if ($act) $countGruposActivos++;
        else $countGruposInactivos++;
    }
}

$countSubcatsActivas = 0;
$countSubcatsInactivas = 0;
if (!empty($categorias_bd)) {
    foreach ($categorias_bd as $c) {
        $act = !isset($c['activo']) || $c['activo'] == 1;
        if ($act) $countSubcatsActivas++;
        else $countSubcatsInactivas++;
    }
}
?>
<?php if (!AjaxHelper::isAjax()): ?>
<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Gestión de Subcategorías - ERP La Caserita</title>
<link rel="icon" type="image/png" href="Images/logo_backup.png">
<link rel="stylesheet" href="Styles/global.css?v=<?= time(); ?>">
<link rel="stylesheet" href="Styles/styles-home.css?v=<?= time(); ?>">
<link rel="stylesheet" href="Styles/styles-users-admin.css?v=<?= time(); ?>">
<link rel="stylesheet" href="Styles/admin-spa.css?v=<?= time(); ?>">
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="Scripts/admin-spa.js?v=<?= time(); ?>" defer></script>
    <script src="Scripts/pagination.js?v=<?= time(); ?>"></script>
<link rel="stylesheet" href="Styles/Vistas/Admin/categorias_admin.css?v=<?= filemtime(__DIR__ . '/../../Styles/Vistas/Admin/categorias_admin.css'); ?>">
</head>
<body>
<div class="admin-layout">
<?php include __DIR__ . '/partials/admin_sidebar.php'; ?>
<div class="admin-main">
<header class="admin-topbar">
<div class="admin-topbar-left">
<button type="button" class="admin-hamburger-btn hide-on-desktop" id="openAdminSidebar" aria-label="Abrir Menu">
<svg xmlns="http://www.w3.org/2000/svg" width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><line x1="3" y1="6" x2="21" y2="6"></line><line x1="3" y1="12" x2="21" y2="12"></line><line x1="3" y1="18" x2="21" y2="18"></line></svg>
</button>
<div class="admin-topbar-brand">
<span class="brand-icon"><svg xmlns="http://www.w3.org/2000/svg" width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"><rect x="3" y="3" width="7" height="7"></rect><rect x="14" y="3" width="7" height="7"></rect><rect x="14" y="14" width="7" height="7"></rect><rect x="3" y="14" width="7" height="7"></rect></svg></span>
<span class="brand-title">Subcategorías</span>
</div>
</div>
<div class="admin-topbar-actions"><?php include __DIR__ . '/partials/admin_topbar_actions.php'; ?></div>
</header>
<div id="admin-container">
<?php endif; ?>
<div class="categorias-admin-wrapper" style="padding:26px 24px;min-height:calc(100vh - 75px);">




<?php if ($flash_success || $flash_error || in_array($avisoAccion, ['estado_actualizado', 'estado_error'], true)): 
    $esError = ($flash_error || $avisoAccion === 'estado_error');
    $msgNotif = $flash_error ?: $flash_success ?: ($avisoAccion === 'estado_actualizado' ? 'Estado actualizado correctamente.' : 'No se pudo actualizar el estado.');
?>


<script>
(function() {
    const tipo = <?= json_encode($esError ? 'error' : 'success'); ?>;
    const mensaje = <?= json_encode($msgNotif, JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_HEX_AMP); ?>;
    function mostrarAvisoCategorias() {
        if (typeof SPA !== 'undefined' && typeof SPA.toast === 'function') {
            if (typeof SPA.createToastContainer === 'function') SPA.createToastContainer();
            SPA.toast(tipo, tipo === 'error' ? 'Error' : 'Éxito', mensaje, 4000);
            return;
        }
        let container = document.getElementById('toast-container');
        if (!container) {
            container = document.createElement('div');
            container.id = 'toast-container';
            document.body.appendChild(container);
        }
        const toast = document.createElement('div');
        toast.className = 'custom-toast toast-' + tipo + ' toast-show';
        toast.setAttribute('role', tipo === 'error' ? 'alert' : 'status');
        toast.style.cssText = 'position:fixed;right:20px;top:20px;z-index:99999;display:flex;align-items:center;gap:12px;width:min(420px,calc(100vw - 32px));padding:14px 18px;background:#fff;border-radius:14px;box-shadow:0 12px 32px rgba(15,23,42,.2);border:1px solid ' + (tipo === 'error' ? '#fecaca' : '#bbf7d0') + ';color:#0f172a;';
        const icon = document.createElement('span');
        icon.className = 'toast-icon';
        icon.innerHTML = tipo === 'error'
            ? '<svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="#dc2626" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"></circle><line x1="15" y1="9" x2="9" y2="15"></line><line x1="9" y1="9" x2="15" y2="15"></line></svg>'
            : '<svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="#15803d" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"></path><path d="m9 11 3 3L22 4"></path></svg>';
        const content = document.createElement('div');
        content.style.cssText = 'flex:1;';
        const title = document.createElement('div');
        title.style.cssText = 'font-weight:700;font-size:0.95rem;color:#0f172a;margin-bottom:2px;';
        title.textContent = tipo === 'error' ? 'Error' : 'Éxito';
        const description = document.createElement('div');
        description.style.cssText = 'font-size:0.86rem;color:#475569;';
        description.textContent = mensaje;
        content.append(title, description);
        const close = document.createElement('button');
        close.type = 'button';
        close.style.cssText = 'background:none;border:none;cursor:pointer;color:#94a3b8;padding:4px;display:flex;';
        close.innerHTML = '<svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><line x1="18" y1="6" x2="6" y2="18"></line><line x1="6" y1="6" x2="18" y2="18"></line></svg>';
        toast.append(icon, content, close);
        container.appendChild(toast);
        const removeFn = function() {
            toast.style.opacity = '0';
            toast.style.transform = 'translateY(-10px)';
            toast.style.transition = 'all 0.3s ease';
            setTimeout(function() { toast.remove(); }, 300);
        };
        close.addEventListener('click', removeFn);
        setTimeout(removeFn, 4500);
    }
    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', mostrarAvisoCategorias, { once: true });
    } else {
        mostrarAvisoCategorias();
    }
})();
</script>
<?php endif; ?>

<!-- TARJETAS CUADRADAS DE MÉTRICAS (SOLO SUBCATEGORÍAS N3) -->
<div class="stats-grid-square">
    <div class="stat-card-square active-tab" id="tabCardSubcategorias" onclick="window.switchTab('subcategorias', 'all')">
        <span class="stat-title">SUBCATEGORÍAS</span>
        <span class="stat-val"><?= $countSubcats; ?></span>
    </div>
    <div class="stat-card-square" id="tabCardSubActivas" onclick="window.switchTab('subcategorias', 'activas')">
        <span class="stat-title">SUBCATEGORÍAS ACTIVAS</span>
        <span class="stat-val"><?= $countSubcatsActivas; ?></span>
    </div>
    <div class="stat-card-square" id="tabCardSubInactivas" onclick="window.switchTab('subcategorias', 'inactivas')">
        <span class="stat-title">SUBCATEGORÍAS INACTIVAS</span>
        <span class="stat-val"><?= $countSubcatsInactivas; ?></span>
    </div>
</div>

<!-- TAB 3: SUBCATEGORÍAS (NIVEL 3) -->
<div id="tabViewSubcategorias" style="background:#fff;border-radius:14px;border:1px solid #e2e8f0;box-shadow:0 4px 12px rgba(0,0,0,.03);overflow:hidden;margin-bottom:30px;">
<div class="cat-panel-head">
<div class="cat-panel-heading"><div><h3 style="margin:0;font-size:1.15rem;font-weight:800;color:#0f172a;">Listado de Subcategorías</h3><p style="margin:3px 0 0;font-size:.82rem;color:#64748b;">Subcategorías de productos; cada una pertenece a una categoría.</p></div><div class="cat-heading-actions"><button type="button" id="btnNuevaCategoria" class="cat-primary-action" onclick="window.abrirModalCategoria()"><svg xmlns="http://www.w3.org/2000/svg" width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><line x1="12" y1="5" x2="12" y2="19"></line><line x1="5" y1="12" x2="19" y2="12"></line></svg><span>Nueva Subcategoría</span></button><button type="button" class="cat-export-action" onclick="window.exportarSubcategoriasPDF()" title="Exportar el listado completo de subcategorías a PDF"><svg xmlns="http://www.w3.org/2000/svg" width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"></path><polyline points="14 2 14 8 20 8"></polyline><line x1="12" y1="18" x2="12" y2="12"></line><polyline points="9 15 12 18 15 15"></polyline></svg><span>Exportar PDF</span></button></div></div>
<div class="cat-panel-actions">
    <input type="text" id="filtroCategorias" onkeyup="window.filtrarTabla('tablaCategorias','filtroCategorias',2)" placeholder="Buscar subcategoría..." style="padding:8px 14px;border:1px solid #cbd5e1;border-radius:8px;font-size:.88rem;min-width:220px;">
    <div class="cat-filter-wrap">
        <button type="button" class="cat-filter-action" id="btnFiltroCategorias" onclick="window.toggleFiltroCategorias('tablaCategorias')" title="Filtrar por estado" aria-haspopup="true" aria-expanded="false">
            <svg xmlns="http://www.w3.org/2000/svg" width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"><polygon points="22 3 2 3 10 12.46 10 19 14 21 14 12.46 22 3"></polygon></svg>
            <span>Filtrar</span>
        </button>
        <div class="cat-filter-menu" id="filtroMenuCategorias" role="menu">
            <button type="button" class="cat-filter-option is-active" data-filtro="all" onclick="window.setFiltroEstadoCategoria('tablaCategorias','all')">Todos</button>
            <button type="button" class="cat-filter-option" data-filtro="activas" onclick="window.setFiltroEstadoCategoria('tablaCategorias','activas')">Solo activas</button>
            <button type="button" class="cat-filter-option" data-filtro="inactivas" onclick="window.setFiltroEstadoCategoria('tablaCategorias','inactivas')">Solo inactivas</button>
        </div>
    </div>
</div>
</div>
<div style="overflow-x:auto;">
<table style="width:100%;border-collapse:collapse;text-align:left;" id="tablaCategorias">
<thead><tr style="background:#f8fafc;border-bottom:2px solid #e2e8f0;">
<th style="padding:12px 18px;font-size:.76rem;font-weight:800;color:#475569;text-transform:uppercase;">ID</th>
<th style="padding:12px 18px;font-size:.76rem;font-weight:800;color:#475569;text-transform:uppercase;">Imagen</th>
<th style="padding:12px 18px;font-size:.76rem;font-weight:800;color:#475569;text-transform:uppercase;">Subcategoría</th>
<th style="padding:12px 18px;font-size:.76rem;font-weight:800;color:#475569;text-transform:uppercase;">Categoría</th>
<th style="padding:12px 18px;font-size:.76rem;font-weight:800;color:#475569;text-transform:uppercase;">Descripcion</th>
<th style="padding:12px 18px;font-size:.76rem;font-weight:800;color:#475569;text-transform:uppercase;">Estado</th>
<th style="width: 48px; text-align: center;"></th>
</tr></thead>
<tbody>
<?php if (!empty($categorias_bd)): ?>
<?php foreach ($categorias_bd as $cat): 
    $catImg = !empty($cat['imagen']) ? $cat['imagen'] : 'Images/icono.png';
    $esActivo = !isset($cat['activo']) || $cat['activo'] == 1;
?>
<tr data-activo="<?= $esActivo ? 1 : 0; ?>" style="border-bottom:1px solid #f1f5f9;" onmouseover="this.style.background='#f8fafc'" onmouseout="this.style.background='transparent'">
<td style="padding:14px 18px;font-weight:700;color:#64748b;font-size:.85rem;">#<?= (int)$cat['id_categoria']; ?></td>
<td style="padding:14px 18px;"><img src="<?= htmlspecialchars($catImg); ?>" alt="" class="cat-thumb-img" onerror="this.onerror=null;this.src='Images/icono.png';"></td>
<td style="padding:14px 18px;"><strong style="font-size:.95rem;color:#0f172a;"><?= htmlspecialchars($cat['nombre']); ?></strong></td>
<td style="padding:14px 18px;">
    <div class="cat-hierarchy-list">
    <?php if (!empty($cat['grupo_nombre'])): ?>
        <span class="cat-hierarchy-pill cat-group-pill"><?= htmlspecialchars($cat['grupo_nombre']); ?></span>
    <?php else: ?>
        <span style="color:#94a3b8;font-size:.82rem;font-style:italic;">Sin asignar</span>
    <?php endif; ?>
    </div>
</td>
<td style="padding:14px 18px;color:#64748b;font-size:.88rem;max-width:240px;"><?= !empty($cat['descripcion']) ? htmlspecialchars($cat['descripcion']) : '<span style="color:#94a3b8;font-style:italic;">Sin descripcion</span>'; ?></td>
<td style="padding:14px 18px;">
    <span class="status-pill <?= $esActivo ? 'activo' : 'inactivo'; ?>">
        <?= $esActivo ? 'Activo' : 'Inactivo'; ?>
    </span>
</td>
<td style="padding:14px 18px;text-align:right;white-space:nowrap;">
<div class="table-kebab-container">
    <button type="button" class="table-kebab-btn" aria-label="Acciones de subcategoría" title="Opciones">
        <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="currentColor"><circle cx="12" cy="5" r="1.8"/><circle cx="12" cy="12" r="1.8"/><circle cx="12" cy="19" r="1.8"/></svg>
    </button>
    <div class="table-kebab-menu">
        <button type="button" class="table-kebab-item" onclick='window.editarCategoria(<?= json_encode($cat,JSON_HEX_TAG|JSON_HEX_APOS|JSON_HEX_QUOT|JSON_HEX_AMP); ?>)'>
            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M12 20h9"></path><path d="M16.5 3.5a2.121 2.121 0 0 1 3 3L7 19l-4 1 1-4L16.5 3.5z"></path></svg>
            Editar
        </button>
        <?php if ($esActivo): ?>
        <button type="button" class="table-kebab-item" onclick='window.confirmarCambioEstadoCategoria("categoria", <?= (int)$cat['id_categoria']; ?>, 0, <?= json_encode($cat['nombre'], JSON_HEX_TAG|JSON_HEX_APOS|JSON_HEX_QUOT|JSON_HEX_AMP); ?>)'>
            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"></circle><line x1="4.93" y1="4.93" x2="19.07" y2="19.07"></line></svg>
            Desactivar
        </button>
        <?php else: ?>
        <button type="button" class="table-kebab-item" onclick='window.confirmarCambioEstadoCategoria("categoria", <?= (int)$cat['id_categoria']; ?>, 1, <?= json_encode($cat['nombre'], JSON_HEX_TAG|JSON_HEX_APOS|JSON_HEX_QUOT|JSON_HEX_AMP); ?>)'>
            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"></circle><path d="m8 12 2.5 2.5L16 9"></path></svg>
            Activar
        </button>
        <?php endif; ?>
    </div>
</div>
</td>
</tr>
<?php endforeach; ?>
<?php else: ?>
<tr><td colspan="7" style="text-align:center;padding:40px;color:#64748b;">No hay subcategorías registradas.</td></tr>
<?php endif; ?>
</tbody>
</table>
</div>
            <div class="pg-footer" id="paginationListCat"></div>
</div>

<!-- MODAL SUBCATEGORÍA (NIVEL 3) -->
<div class="cat-modal-overlay" id="modalCategoriaAdmin" onclick="if(event.target===this)window.cerrarModalId('modalCategoriaAdmin')">
<div class="cat-modal-card">
<div style="padding-bottom:16px;border-bottom:1px solid #f1f5f9;display:flex;justify-content:space-between;align-items:center;margin-bottom:18px;">
<h3 id="tituloModalCat" style="margin:0;font-size:1.25rem;font-weight:800;color:#0f172a;">Nueva Subcategoría</h3>
<button type="button" onclick="window.cerrarModalId('modalCategoriaAdmin')" class="cat-modal-close-btn" aria-label="Cerrar modal" title="Cerrar">
<svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
<line x1="18" y1="6" x2="6" y2="18"></line>
<line x1="6" y1="6" x2="18" y2="18"></line>
</svg>
</button>
</div>
<form method="POST" action="index.php?action=crear_categoria" enctype="multipart/form-data" id="formCategoriaAdmin">
<input type="hidden" name="id_categoria" id="catId">
<input type="hidden" name="redirect_to" value="categorias_admin">
<div style="margin-bottom:16px;">
<label style="display:block;font-weight:700;font-size:.82rem;color:#334155;text-transform:uppercase;margin-bottom:6px;">Nombre de la Subcategoría *</label>
<input type="text" name="nombre" id="catNombre" required placeholder="Ej. Pollo, Res, Cerdo, Alimento para Perros..." style="width:100%;padding:10px 14px;border:1.5px solid #cbd5e1;border-radius:8px;font-size:.95rem;box-sizing:border-box;outline:none;">
</div>
<div class="cat-combo-field">
<label class="cat-combo-label" for="catGrupoSelect">Categoría *</label>
<input type="hidden" name="id_momento" id="catMomentoId" value="">
<select name="id_grupo" id="catGrupoSelect" class="cat-select" required>
<option value="">Selecciona una categoría</option>
<?php foreach ($grupMomento_bd as $g): ?>
<option value="<?= (int)$g['id_grupo']; ?>" data-momento="<?= (int)$g['id_momento']; ?>"><?= htmlspecialchars($g['nombre']); ?></option>
<?php endforeach; ?>
</select>
<p class="cat-combo-help">La subcategoría se guardará dentro de la categoría seleccionada.</p>
</div>
<div style="margin-bottom:16px;">
<label style="display:block;font-weight:700;font-size:.82rem;color:#334155;text-transform:uppercase;margin-bottom:6px;">Descripcion (Opcional)</label>
<textarea name="descripcion" id="catDescripcion" rows="2" placeholder="Breve detalle..." style="width:100%;padding:10px 14px;border:1.5px solid #cbd5e1;border-radius:8px;font-size:.95rem;box-sizing:border-box;resize:vertical;outline:none;"></textarea>
</div>
<div style="margin-bottom:20px;">
<label style="display:block;font-weight:700;font-size:.82rem;color:#334155;text-transform:uppercase;margin-bottom:8px;">Imagen de Subcategoría (Opcional)</label>
<div style="display:flex;gap:14px;align-items:center;background:#f8fafc;padding:12px;border:1.5px solid #e2e8f0;border-radius:12px;">
    <div style="width:52px;height:52px;border-radius:10px;border:1.5px dashed #cbd5e1;background:#fff;display:flex;align-items:center;justify-content:center;overflow:hidden;flex-shrink:0;">
        <img id="catImgPreview" src="Images/icono.png" style="width:100%;height:100%;object-fit:cover;" onerror="this.onerror=null;this.src='Images/icono.png';">
    </div>
    <div style="flex:1;">
        <label for="catFileInput" style="display:inline-flex;align-items:center;gap:6px;padding:7px 13px;background:#fff;border:1px solid #cbd5e1;border-radius:8px;font-size:.82rem;font-weight:700;color:#334155;cursor:pointer;transition:all .2s ease;box-shadow:0 1px 3px rgba(0,0,0,.04);" onmouseover="this.style.background='#f1f5f9';this.style.borderColor='#94a3b8';" onmouseout="this.style.background='#fff';this.style.borderColor='#cbd5e1';">
            <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"><path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"></path><polyline points="17 8 12 3 7 8"></polyline><line x1="12" y1="3" x2="12" y2="15"></line></svg>
            <span id="catFileName">Seleccionar archivo PC...</span>
        </label>
        <input type="file" name="imagen" id="catFileInput" accept="image/*" style="display:none;" onchange="window.previewCatFileImage(this)">
        <div style="margin-top:8px;">
            <input type="text" name="imagen_url" id="catImagenUrl" placeholder="O pega enlace / ruta: Images/..." style="width:100%;padding:7px 12px;border:1.5px solid #cbd5e1;border-radius:8px;font-size:.84rem;box-sizing:border-box;outline:none;background:#fff;" oninput="document.getElementById('catImgPreview').src = this.value || 'Images/icono.png'">
        </div>
    </div>
</div>
</div>
<div style="display:flex;justify-content:flex-end;gap:10px;">
<button type="submit" id="btnGuardarCat" style="padding:10px 22px;border-radius:8px;border:none;background:#15803d;color:#fff;font-weight:700;cursor:pointer;">Guardar Subcategoría</button>
</div>
</form>
</div>
</div>

<div class="cat-modal-overlay" id="modalConfirmarEstadoCategoria" onclick="if(event.target===this)window.cerrarModalId('modalConfirmarEstadoCategoria')">
    <div class="cat-modal-card" style="max-width:420px;text-align:center;position:relative;">
        <button type="button" class="btn-close-universal" onclick="window.cerrarModalId('modalConfirmarEstadoCategoria')" aria-label="Cerrar modal" style="position: absolute; top: 14px; right: 14px;">&times;</button>
        <div id="iconoEstadoCategoria" style="width:56px;height:56px;margin:0 auto 16px;border-radius:50%;display:flex;align-items:center;justify-content:center;"></div>
        <h3 id="tituloEstadoCategoria" style="margin:0 0 8px;font-size:1.2rem;font-weight:800;color:#0f172a;">Confirmar cambio</h3>
        <p id="textoEstadoCategoria" style="margin:0 0 22px;color:#64748b;font-size:.92rem;"></p>
        <div style="display:flex;justify-content:center;gap:10px;">
            <a id="btnConfirmarEstadoCategoria" href="#" style="padding:10px 20px;border-radius:8px;color:#fff;font-weight:700;text-decoration:none;display:inline-flex;align-items:center;">Confirmar</a>
        </div>
    </div>
</div>

<script>
    window.__subcategoriasData = <?php
        $subcatsExport = array_map(function($c) {
            return [
                'id' => (int)($c['id_categoria'] ?? 0),
                'nombre' => $c['nombre'] ?? '',
                'macro' => $c['momento_nombre'] ?? '',
                'categoria' => $c['grupo_nombre'] ?? '',
                'descripcion' => $c['descripcion'] ?? '',
                'activo' => (isset($c['activo']) ? ((int)$c['activo'] === 1) : true)
            ];
        }, $categorias_bd);
        echo json_encode($subcatsExport, JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_HEX_AMP | JSON_UNESCAPED_UNICODE);
    ?>;
</script>
<script src="Scripts/Admin/categorias_admin.js?v=<?= time(); ?>"></script>
<?php if (!AjaxHelper::isAjax()): ?>
            </div>
        </div>
    </div>
</body>
</html>
<?php endif; ?>
