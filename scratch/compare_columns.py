import subprocess
import re

# Get all columns from live MySQL
cmd = "C:\\xampp\\mysql\\bin\\mysql.exe -u root -e \"SELECT TABLE_NAME, COLUMN_NAME FROM information_schema.COLUMNS WHERE TABLE_SCHEMA = 'lacaserita_db' ORDER BY TABLE_NAME, ORDINAL_POSITION;\""
res = subprocess.check_output(cmd, shell=True, text=True)
live_cols = {}
for line in res.strip().split('\n')[1:]:
    parts = line.split('\t')
    if len(parts) == 2:
        t, c = parts[0].strip(), parts[1].strip()
        live_cols.setdefault(t, []).append(c)

print(f"Total tablas en MySQL en vivo: {len(live_cols)}")

# Inspect ../lacaserita_db.sql
with open('../lacaserita_db.sql', 'r', encoding='utf-8', errors='ignore') as f:
    sql_text = f.read()

# Check for specific columns we modified recently:
# e.g., canal in pedido?
print("¿'canal' en lacaserita_db.sql?:", "'canal'" in sql_text or "`canal`" in sql_text)

# Let's see if there are any columns in live MySQL that aren't in lacaserita_db.sql
missing_in_sql = []
for t, cols in live_cols.items():
    # find CREATE TABLE for t
    m = re.search(r'CREATE TABLE [`"]?' + t + r'[`"]?\s*\((.*?)\)\s*ENGINE', sql_text, re.DOTALL | re.IGNORECASE)
    if m:
        table_def = m.group(1)
        for col in cols:
            if not re.search(r'[`"]?' + col + r'[`"]?\s+', table_def, re.IGNORECASE):
                missing_in_sql.append((t, col))
    else:
        print(f"Tabla {t} no encontrada en lacaserita_db.sql")

print("Columnas en MySQL en vivo que NO están en ../lacaserita_db.sql:")
if missing_in_sql:
    for t, c in missing_in_sql:
        print(f" - {t}.{c}")
else:
    print("¡Ninguna! ../lacaserita_db.sql tiene exactamente todas las columnas.")

