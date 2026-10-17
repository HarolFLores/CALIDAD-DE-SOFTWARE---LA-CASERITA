with open('Styles/styles-users-admin.css', 'r', encoding='utf-8') as f:
    c = f.read()

c = c.replace('font-size: 0.78rem;\n  font-weight: 700;\n  line-height: 1.25;\n  white-space: nowrap;\n  letter-spacing: 0;\n  box-shadow: none;\n}\n\n.status-pill::before {\n  content: "";\n  width: 6px;\n  height: 6px;',
              'font-size: 0.84rem;\n  font-weight: 700;\n  line-height: 1.25;\n  white-space: nowrap;\n  letter-spacing: 0;\n  box-shadow: none;\n}\n\n.status-pill::before {\n  content: "";\n  width: 7px;\n  height: 7px;')

# Also add .btn-limpiar-filtros if not present
if '.btn-limpiar-filtros' not in c:
    c += '''
/* Botón Limpiar Filtros estándar */
.btn-limpiar-filtros {
  min-height: 40px;
  padding: 8px 16px;
  border: 1px solid #cbd5e1;
  border-radius: 8px;
  background: #ffffff;
  color: #475569;
  font-size: 0.85rem;
  font-weight: 600;
  cursor: pointer;
  transition: all 0.2s ease;
  white-space: nowrap;
  display: inline-flex;
  align-items: center;
  justify-content: center;
}
.btn-limpiar-filtros:hover {
  background: #f8fafc;
  color: #0f172a;
  border-color: #94a3b8;
}
'''

# Handle CRLF if needed
if 'width: 6px;\r\n  height: 6px;' in c:
    c = c.replace('width: 6px;\r\n  height: 6px;', 'width: 7px;\r\n  height: 7px;')
if 'font-size: 0.78rem;\r\n  font-weight: 700;' in c:
    c = c.replace('font-size: 0.78rem;\r\n  font-weight: 700;', 'font-size: 0.84rem;\r\n  font-weight: 700;')

with open('Styles/styles-users-admin.css', 'w', encoding='utf-8') as f:
    f.write(c)

print('Updated styles-users-admin.css successfully')
