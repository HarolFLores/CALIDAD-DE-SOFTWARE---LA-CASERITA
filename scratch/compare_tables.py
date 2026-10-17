import re

live_tables = [
    'categoria', 'configuracion_correo', 'detalle_pedido', 'direccion', 
    'distrito_delivery', 'envio_delivery', 'grupo_momento', 'inventario', 
    'kardex', 'metodo_pago', 'momento_consumo', 'movimiento_caja', 
    'pago_verificacion', 'pedido', 'pos_escaner_cola', 'producto', 
    'proveedor', 'reposicion', 'sesion_caja', 'sys_sessions', 
    'usuario', 'venta'
]

files = [
    '../lacaserita_db.sql',
    'database/database.sql'
]

for fp in files:
    try:
        with open(fp, 'r', encoding='utf-8', errors='ignore') as f:
            content = f.read()
        found = set(re.findall(r'CREATE TABLE\s+[`"]?([a-zA-Z0-9_]+)[`"]?', content, re.IGNORECASE))
        print(f"=== {fp} ===")
        print(f"Total tablas encontradas: {len(found)}")
        
        nuevas = set(live_tables) - found
        faltan_en_vivo = found - set(live_tables)
        
        print("Tablas en tu MySQL actual que NO están en este archivo (NUEVAS):")
        print(sorted(list(nuevas)) if nuevas else "Ninguna (están todas)")
        
        print("\nTablas en este archivo que NO están en tu MySQL:")
        print(sorted(list(faltan_en_vivo)) if faltan_en_vivo else "Ninguna")
        print("="*40 + "\n")
    except Exception as e:
        print(f"Error con {fp}: {e}")
