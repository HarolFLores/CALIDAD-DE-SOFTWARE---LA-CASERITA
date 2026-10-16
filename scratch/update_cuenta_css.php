<?php
$file = __DIR__ . '/../Styles/styles-cuenta.css';
$content = file_get_contents($file);
$content = str_replace(["\r\n", "\r"], "\n", $content);

$oldCSS = '.pedidos-toolbar {
    display: flex;
    justify-content: space-between;
    align-items: center;
    gap: 15px;
    margin-bottom: 20px;
    flex-wrap: wrap;
    background: #ffffff;
    padding: 15px 20px;
    border-radius: 14px;
    border: 1px solid #e2e8f0;
    box-shadow: 0 1px 3px rgba(0, 0, 0, 0.03);
}

.pedidos-search-box {
    position: relative;
    flex: 1;
    min-width: 250px;
}

.pedidos-search-box i {
    position: absolute;
    left: 14px;
    top: 50%;
    transform: translateY(-50%);
    color: #94a3b8;
}

.pedidos-search-box input {
    width: 100%;
    padding: 10px 15px 10px 40px;
    border: 1px solid #cbd5e1;
    border-radius: 8px;
    font-size: 0.9rem;
    color: #334155;
    transition: all 0.2s;
    outline: none;
}

.pedidos-search-box input:focus {
    border-color: #0d9488;
    box-shadow: 0 0 0 3px rgba(13, 148, 136, 0.1);
}

.pedidos-filter-box select {
    padding: 10px 35px 10px 15px;
    border: 1px solid #cbd5e1;
    border-radius: 8px;
    font-size: 0.9rem;
    color: #334155;
    background-color: #ffffff;
    cursor: pointer;
    outline: none;
    transition: all 0.2s;
    appearance: none;
    background-image: url("data:image/svg+xml;charset=US-ASCII,%3Csvg%20xmlns%3D%22http%3A%2F%2Fwww.w3.org%2F2000%2Fsvg%22%20width%3D%22292.4%22%20height%3D%22292.4%22%3E%3Cpath%20fill%3D%22%2394a3b8%22%20d%3D%22M287%2069.4a17.6%2017.6%200%200%200-13-5.4H18.4c-5%200-9.3%201.8-12.9%205.4A17.6%2017.6%200%200%200%200%2082.2c0%205%201.8%209.3%205.4%2012.9l128%20127.9c3.6%203.6%207.8%205.4%2012.8%205.4s9.2-1.8%2012.8-5.4L287%2095c3.5-3.5%205.4-7.8%205.4-12.8%200-5-1.9-9.2-5.5-12.8z%22%2F%3E%3C%2Fsvg%3E");
    background-repeat: no-repeat;
    background-position: right 12px top 50%;
    background-size: 10px auto;
}

.pedidos-filter-box select:focus {
    border-color: #0d9488;
}';

$newCSS = '.pedidos-toolbar {
    display: flex;
    justify-content: space-between;
    align-items: center;
    gap: 12px;
    margin-bottom: 20px;
    flex-wrap: wrap;
    background: #ffffff;
    padding: 14px 18px;
    border-radius: 14px;
    border: 1px solid #e2e8f0;
    box-shadow: 0 1px 3px rgba(0, 0, 0, 0.03);
}

.pedidos-search-box {
    position: relative;
    flex: 1;
    min-width: 260px;
}

.pedidos-search-box input {
    width: 100%;
    padding: 10px 14px;
    border: 1.5px solid #cbd5e1;
    border-radius: 10px;
    font-size: 0.9rem;
    color: #1e293b;
    background: #ffffff;
    transition: all 0.2s;
    outline: none;
    box-sizing: border-box;
}

.pedidos-search-box input:focus {
    border-color: #15803d;
    box-shadow: 0 0 0 3px rgba(21, 128, 61, 0.12);
}

.pedidos-filters-container {
    display: flex;
    align-items: center;
    gap: 10px;
    flex-wrap: wrap;
}

.pedidos-filter-box input[type="date"] {
    padding: 9px 12px;
    border: 1.5px solid #cbd5e1;
    border-radius: 10px;
    font-size: 0.88rem;
    color: #334155;
    background: #ffffff;
    outline: none;
    transition: all 0.2s;
}

.pedidos-filter-box input[type="date"]:focus {
    border-color: #15803d;
    box-shadow: 0 0 0 3px rgba(21, 128, 61, 0.12);
}

.pedidos-filter-box select {
    padding: 9px 34px 9px 14px;
    border: 1.5px solid #cbd5e1;
    border-radius: 10px;
    font-size: 0.88rem;
    color: #334155;
    background-color: #ffffff;
    cursor: pointer;
    outline: none;
    transition: all 0.2s;
    appearance: none;
    background-image: url("data:image/svg+xml;charset=US-ASCII,%3Csvg%20xmlns%3D%22http%3A%2F%2Fwww.w3.org%2F2000%2Fsvg%22%20width%3D%2212%22%20height%3D%2212%22%20viewBox%3D%220%200%2024%2024%22%20fill%3D%22none%22%20stroke%3D%22%2364748b%22%20stroke-width%3D%222.5%22%20stroke-linecap%3D%22round%22%20stroke-linejoin%3D%22round%22%3E%3Cpath%20d%3D%22m6%209%206%206%206-6%22%2F%3E%3C%2Fsvg%3E");
    background-repeat: no-repeat;
    background-position: right 12px center;
}

.pedidos-filter-box select:focus {
    border-color: #15803d;
    box-shadow: 0 0 0 3px rgba(21, 128, 61, 0.12);
}

.btn-limpiar-filtros {
    padding: 9px 16px;
    border: 1.5px solid #e2e8f0;
    border-radius: 10px;
    background: #f8fafc;
    color: #475569;
    font-size: 0.88rem;
    font-weight: 600;
    cursor: pointer;
    transition: all 0.2s;
    white-space: nowrap;
}

.btn-limpiar-filtros:hover {
    background: #f1f5f9;
    color: #0f172a;
    border-color: #cbd5e1;
}

.badge-tipo-entrega,
.badge-metodo-pago {
    display: inline-block;
    padding: 4px 10px;
    border-radius: 8px;
    font-size: 0.8rem;
    font-weight: 600;
    background: #f1f5f9;
    color: #334155;
    border: 1px solid #e2e8f0;
}';

$oldCSS = str_replace(["\r\n", "\r"], "\n", $oldCSS);
$newCSS = str_replace(["\r\n", "\r"], "\n", $newCSS);

if (strpos($content, $oldCSS) !== false) {
    $content = str_replace($oldCSS, $newCSS, $content);
    file_put_contents($file, $content);
    echo "styles-cuenta.css updated successfully.\n";
} else {
    echo "Warning: oldCSS not matched in styles-cuenta.css.\n";
}
