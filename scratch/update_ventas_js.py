with open('Scripts/Admin/ventas_admin.js', 'r', encoding='utf-8') as f:
    content = f.read()

# 1. Add filtCanal and btnLimpiar
target_vars = "var filtEstado     = document.getElementById('vaFiltroEstado');"
replace_vars = "var filtCanal      = document.getElementById('vaFiltroCanal');\n    var filtEstado     = document.getElementById('vaFiltroEstado');\n    var btnLimpiar     = document.getElementById('vaBtnLimpiar');"
assert target_vars in content, 'target_vars not found'
content = content.replace(target_vars, replace_vars, 1)

# 2. Add can in aplicarFiltros
target_filt = "var est = (filtEstado ? filtEstado.value : '').toLowerCase();"
replace_filt = "var can = (filtCanal ? filtCanal.value : '').toUpperCase();\n        var est = (filtEstado ? filtEstado.value : '').toLowerCase();"
assert target_filt in content, 'target_filt not found'
content = content.replace(target_filt, replace_filt, 1)

# 3. Add rowCanal in rows.filter
target_row_props = "var rowEstado  = normEstado(row.getAttribute('data-estado'));"
replace_row_props = "var rowCanal   = (row.getAttribute('data-canal') || 'WEB').toUpperCase();\n            var rowEstado  = normEstado(row.getAttribute('data-estado'));"
assert target_row_props in content, 'target_row_props not found'
content = content.replace(target_row_props, replace_row_props, 1)

# 4. Add matchCanal in return
target_ret = "return matchQ && matchEstado && matchEntrega && matchFecha;"
replace_ret = "var matchCanal = !can || rowCanal === can;\n            return matchQ && matchEstado && matchEntrega && matchFecha && matchCanal;"
assert target_ret in content, 'target_ret not found'
content = content.replace(target_ret, replace_ret, 1)

# 5. Add event listeners for filtCanal and btnLimpiar
target_evts = "if (filtEntrega) filtEntrega.addEventListener('change', function() { aplicarFiltros(true); });"
replace_evts = """if (filtCanal) filtCanal.addEventListener('change', function() { aplicarFiltros(true); });
    if (filtEntrega) filtEntrega.addEventListener('change', function() { aplicarFiltros(true); });
    if (btnLimpiar) {
        btnLimpiar.addEventListener('click', function() {
            if (searchInput) searchInput.value = '';
            if (filtCanal) filtCanal.value = '';
            if (filtEstado) filtEstado.value = '';
            if (filtEntrega) filtEntrega.value = '';
            if (filtFecha) filtFecha.value = '';
            statCards.forEach(function(c) { c.classList.remove('active-card'); });
            var firstCard = document.querySelector('.stat-card-square[data-filtro-estado=""]');
            if (firstCard) firstCard.classList.add('active-card');
            aplicarFiltros(true);
        });
    }"""
assert target_evts in content, 'target_evts not found'
content = content.replace(target_evts, replace_evts, 1)

# 6. Update badge selector in status update
target_badge = "var badge = row.querySelector('.va-badge-pill');"
replace_badge = """var badge = row.querySelector('.status-pill, .va-badge-pill');
                        if (badge) {
                            badge.className = 'status-pill ' + estCod;
                            badge.textContent = estData.label;
                        }"""
assert target_badge in content, 'target_badge not found'
# replace lines 404-409
content = content.replace(
"""                        var badge = row.querySelector('.va-badge-pill');
                        if (badge) {
                            //badge.style.background = estData.bg;
                            //badge.style.color = estData.color;
                            badge.innerHTML = '<span style="width:6px;height:6px;border-radius:50%;background:' + estData.dot + ';flex-shrink:0;"></span><span>' + estData.label + '</span>';
                        }""",
"""                        var badge = row.querySelector('.status-pill, .va-badge-pill');
                        if (badge) {
                            badge.className = 'status-pill ' + estCod;
                            badge.textContent = estData.label;
                        }"""
)

with open('Scripts/Admin/ventas_admin.js', 'w', encoding='utf-8') as f:
    f.write(content)
print('Successfully updated ventas_admin.js')
