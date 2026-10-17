with open('Views/Admin/ventas_admin.php', 'r', encoding='utf-8') as f:
    content = f.read()

# 1. Add th CANAL
target_th = '<th>N° PEDIDO</th>'
replace_th = '<th>N° PEDIDO</th>\n                            <th style="text-align: center;">CANAL</th>'
assert target_th in content, 'target_th not found'
content = content.replace(target_th, replace_th, 1)

# 2. Add canal variable
target_var = "$padId         = str_pad($idPed, 5, '0', STR_PAD_LEFT);"
replace_var = target_var + "\n                                $canalPed      = strtoupper(trim((string)($v['canal'] ?? 'WEB')));\n                                if ($canalPed !== 'POS') $canalPed = 'WEB';"
assert target_var in content, 'target_var not found'
content = content.replace(target_var, replace_var, 1)

# 3. Add canal to busqTexto
target_busq = '$busqTexto     = strtolower("#{$padId} {$idPed} " . ($v[\'codigo_pedido\'] ?? \'\')'
replace_busq = '$busqTexto     = strtolower("#{$padId} {$idPed} {$canalPed} " . ($v[\'codigo_pedido\'] ?? \'\')'
assert target_busq in content, 'target_busq not found'
content = content.replace(target_busq, replace_busq, 1)

# 4. Add data-canal to tr
target_tr = 'data-id="<?= $idPed; ?>"'
replace_tr = 'data-id="<?= $idPed; ?>"\n                                data-canal="<?= $canalPed; ?>"'
assert target_tr in content, 'target_tr not found'
content = content.replace(target_tr, replace_tr, 1)

# 5. Add td CANAL
target_td = '''                                <td style="font-weight: 800; font-size: 0.80rem; white-space: nowrap;">
                                    #<?= $padId; ?>
                                </td>'''
replace_td = '''                                <td style="font-weight: 800; font-size: 0.80rem; white-space: nowrap;">
                                    #<?= $padId; ?>
                                </td>
                                <td style="text-align: center; white-space: nowrap;">
                                    <?php if ($canalPed === 'POS'): ?>
                                        <span class="badge-canal canal-pos" title="Venta Mostrador POS">POS</span>
                                    <?php else: ?>
                                        <span class="badge-canal canal-web" title="Venta Tienda Web">WEB</span>
                                    <?php endif; ?>
                                </td>'''
if target_td not in content:
    target_td = target_td.replace('\n', '\r\n')
    replace_td = replace_td.replace('\n', '\r\n')
assert target_td in content, 'target_td not found'
content = content.replace(target_td, replace_td, 1)

with open('Views/Admin/ventas_admin.php', 'w', encoding='utf-8') as f:
    f.write(content)
print('Successfully updated ventas_admin.php')
