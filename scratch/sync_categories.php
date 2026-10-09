<?php
require_once __DIR__ . '/../Core/dataBase.php';
$pdo = (new DataBase())->getConnection();

// 1. Asegurar las 12 categorías de la Imagen 3
$categorias = [
    ['nombre' => 'Verduras', 'imagen' => 'https://images.unsplash.com/photo-1540420773420-3366772f4999?w=150&auto=format&fit=crop&q=80'],
    ['nombre' => 'Frutas', 'imagen' => 'https://images.unsplash.com/photo-1619566636858-adf3ef46400b?w=150&auto=format&fit=crop&q=80'],
    ['nombre' => 'Carnes y Aves', 'imagen' => 'https://images.unsplash.com/photo-1607623814075-e51df1bdc82f?w=150&auto=format&fit=crop&q=80'],
    ['nombre' => 'Pescados y Mariscos', 'imagen' => 'https://images.unsplash.com/photo-1534422298391-e4f8c172dddb?w=150&auto=format&fit=crop&q=80'],
    ['nombre' => 'Lácteos', 'imagen' => 'https://images.unsplash.com/photo-1550583724-b2692b85b150?w=150&auto=format&fit=crop&q=80'],
    ['nombre' => 'Panadería', 'imagen' => 'https://images.unsplash.com/photo-1509440159596-0249088772ff?w=150&auto=format&fit=crop&q=80'],
    ['nombre' => 'Abarrotes', 'imagen' => 'https://images.unsplash.com/photo-1586201375761-83865001e31c?w=150&auto=format&fit=crop&q=80'],
    ['nombre' => 'Bebidas', 'imagen' => 'https://images.unsplash.com/photo-1622483767028-3f66f32aef97?w=150&auto=format&fit=crop&q=80'],
    ['nombre' => 'Dulces y Snacks', 'imagen' => 'https://images.unsplash.com/photo-1621996346565-e3d5d6281699?w=150&auto=format&fit=crop&q=80'],
    ['nombre' => 'Limpieza', 'imagen' => 'https://images.unsplash.com/photo-1583947215259-38e31be8751f?w=150&auto=format&fit=crop&q=80'],
    ['nombre' => 'Cuidado Personal', 'imagen' => 'https://images.unsplash.com/photo-1556228720-195a672e8a03?w=150&auto=format&fit=crop&q=80'],
    ['nombre' => 'Mascotas', 'imagen' => 'https://images.unsplash.com/photo-1589924691995-400dc9ecc119?w=150&auto=format&fit=crop&q=80'],
];

$catMap = [];
foreach ($categorias as $cat) {
    $stmt = $pdo->prepare("SELECT id_categoria FROM categoria WHERE LOWER(nombre) = LOWER(?) LIMIT 1");
    $stmt->execute([$cat['nombre']]);
    $id = $stmt->fetchColumn();
    if ($id) {
        $up = $pdo->prepare("UPDATE categoria SET imagen = ? WHERE id_categoria = ?");
        $up->execute([$cat['imagen'], $id]);
        $catMap[$cat['nombre']] = $id;
    } else {
        $ins = $pdo->prepare("INSERT INTO categoria (nombre, imagen) VALUES (?, ?)");
        $ins->execute([$cat['nombre'], $cat['imagen']]);
        $catMap[$cat['nombre']] = $pdo->lastInsertId();
    }
}

// 2. Insertar productos representativos para que cada categoría tenga stock y ofertas
$productos = [
    // Verduras
    ['titulo' => 'Tomate Italiano Seleccionado 1kg', 'cat' => 'Verduras', 'precio' => 3.80, 'precio_anterior' => 4.50, 'marca' => 'Del Huerto', 'stock' => 50, 'img' => 'https://images.unsplash.com/photo-1592924357228-91a4daadcfea?w=400&auto=format&fit=crop&q=80'],
    ['titulo' => 'Limón Sutil Premium 1kg', 'cat' => 'Verduras', 'precio' => 4.80, 'precio_anterior' => null, 'marca' => 'Del Huerto', 'stock' => 80, 'img' => 'https://images.unsplash.com/photo-1533083441298-0236a6f60ee2?w=400&auto=format&fit=crop&q=80'],
    ['titulo' => 'Cebolla Roja Fresca 1kg', 'cat' => 'Verduras', 'precio' => 3.20, 'precio_anterior' => 4.00, 'marca' => 'Del Huerto', 'stock' => 60, 'img' => 'https://images.unsplash.com/photo-1618512496248-a07fe83aa8cb?w=400&auto=format&fit=crop&q=80'],

    // Frutas
    ['titulo' => 'Manzana Delicia Seleccionada 1kg', 'cat' => 'Frutas', 'precio' => 5.40, 'precio_anterior' => null, 'marca' => 'La Caserita', 'stock' => 70, 'img' => 'https://images.unsplash.com/photo-1560806887-1e4cd0b6cbd6?w=400&auto=format&fit=crop&q=80'],
    ['titulo' => 'Plátano de Seda de Chanchamayo (Mano)', 'cat' => 'Frutas', 'precio' => 4.50, 'precio_anterior' => 5.50, 'marca' => 'Selva Alta', 'stock' => 40, 'img' => 'https://images.unsplash.com/photo-1571771894821-ce9b6c11b08e?w=400&auto=format&fit=crop&q=80'],
    ['titulo' => 'Mandarina Sin Pepa Dulce 1kg', 'cat' => 'Frutas', 'precio' => 4.20, 'precio_anterior' => 5.20, 'marca' => 'La Caserita', 'stock' => 65, 'img' => 'https://images.unsplash.com/photo-1611080626919-7cf5a9dbab5b?w=400&auto=format&fit=crop&q=80'],

    // Carnes y Aves
    ['titulo' => 'Pollo Entero Fresco con Menudencia 2.2kg', 'cat' => 'Carnes y Aves', 'precio' => 21.50, 'precio_anterior' => 24.90, 'marca' => 'San Fernando', 'stock' => 30, 'img' => 'https://images.unsplash.com/photo-1587593810167-a84920ea0781?w=400&auto=format&fit=crop&q=80'],
    ['titulo' => 'Pechuga de Pollo Especial 1kg', 'cat' => 'Carnes y Aves', 'precio' => 15.90, 'precio_anterior' => null, 'marca' => 'San Fernando', 'stock' => 25, 'img' => 'https://images.unsplash.com/photo-1604503468506-a8da13d82791?w=400&auto=format&fit=crop&q=80'],

    // Pescados y Mariscos
    ['titulo' => 'Filete de Bonito Fresco del Día 1kg', 'cat' => 'Pescados y Mariscos', 'precio' => 18.00, 'precio_anterior' => 22.00, 'marca' => 'Pesca Fresca', 'stock' => 20, 'img' => 'https://images.unsplash.com/photo-1534422298391-e4f8c172dddb?w=400&auto=format&fit=crop&q=80'],

    // Lácteos
    ['titulo' => 'Leche Entera Gloria UHT Pack 6 und', 'cat' => 'Lácteos', 'precio' => 23.90, 'precio_anterior' => 26.50, 'marca' => 'Gloria', 'stock' => 45, 'img' => 'https://images.unsplash.com/photo-1550583724-b2692b85b150?w=400&auto=format&fit=crop&q=80'],
    ['titulo' => 'Yogurt Fresa Gloria 1kg', 'cat' => 'Lácteos', 'precio' => 6.80, 'precio_anterior' => null, 'marca' => 'Gloria', 'stock' => 35, 'img' => 'https://images.unsplash.com/photo-1488477181946-6428a0291777?w=400&auto=format&fit=crop&q=80'],

    // Panadería
    ['titulo' => 'Pan de Molde Blanco Bimbo Grande 650g', 'cat' => 'Panadería', 'precio' => 8.90, 'precio_anterior' => 10.50, 'marca' => 'Bimbo', 'stock' => 40, 'img' => 'https://images.unsplash.com/photo-1509440159596-0249088772ff?w=400&auto=format&fit=crop&q=80'],
    ['titulo' => 'Bolsa de Pan Francés Calientito 10 und', 'cat' => 'Panadería', 'precio' => 4.00, 'precio_anterior' => null, 'marca' => 'La Caserita', 'stock' => 50, 'img' => 'https://images.unsplash.com/photo-1586444248902-2f64eddc13df?w=400&auto=format&fit=crop&q=80'],

    // Abarrotes
    ['titulo' => 'Arroz Extra Premium Costeño Saco 5kg', 'cat' => 'Abarrotes', 'precio' => 18.50, 'precio_anterior' => 22.90, 'marca' => 'Costeño', 'stock' => 55, 'img' => 'https://images.unsplash.com/photo-1586201375761-83865001e31c?w=400&auto=format&fit=crop&q=80'],
    ['titulo' => 'Aceite de Girasol Primor Botella 1L', 'cat' => 'Abarrotes', 'precio' => 9.20, 'precio_anterior' => 10.50, 'marca' => 'Primor', 'stock' => 60, 'img' => 'https://images.unsplash.com/photo-1474979266404-7eaacbcd87c5?w=400&auto=format&fit=crop&q=80'],
    ['titulo' => 'Fideos Spaghetti Don Vittorio 500g', 'cat' => 'Abarrotes', 'precio' => 3.40, 'precio_anterior' => null, 'marca' => 'Don Vittorio', 'stock' => 90, 'img' => 'https://images.unsplash.com/photo-1551462147-ff29053bfc14?w=400&auto=format&fit=crop&q=80'],

    // Bebidas
    ['titulo' => 'Gaseosa Coca Cola Sabor Original 1.5L', 'cat' => 'Bebidas', 'precio' => 6.50, 'precio_anterior' => 7.80, 'marca' => 'Coca Cola', 'stock' => 50, 'img' => 'https://images.unsplash.com/photo-1622483767028-3f66f32aef97?w=400&auto=format&fit=crop&q=80'],
    ['titulo' => 'Agua San Luis Sin Gas 2.5L', 'cat' => 'Bebidas', 'precio' => 3.50, 'precio_anterior' => null, 'marca' => 'San Luis', 'stock' => 70, 'img' => 'https://images.unsplash.com/photo-1548839140-29a749e1bc4e?w=400&auto=format&fit=crop&q=80'],

    // Dulces y Snacks
    ['titulo' => 'Galletas de Chocolate Casino Pack 8 und', 'cat' => 'Dulces y Snacks', 'precio' => 6.50, 'precio_anterior' => 7.50, 'marca' => 'Casino', 'stock' => 60, 'img' => 'https://images.unsplash.com/photo-1558961363-fa8fdf82db35?w=400&auto=format&fit=crop&q=80'],
    ['titulo' => 'Papas Lays Clásicas Bolsa Grande 160g', 'cat' => 'Dulces y Snacks', 'precio' => 5.80, 'precio_anterior' => null, 'marca' => 'Lays', 'stock' => 45, 'img' => 'https://images.unsplash.com/photo-1566478989037-eec170784d0b?w=400&auto=format&fit=crop&q=80'],

    // Limpieza
    ['titulo' => 'Detergente en Polvo Bolívar Flores 2.6kg', 'cat' => 'Limpieza', 'precio' => 24.90, 'precio_anterior' => 29.90, 'marca' => 'Bolívar', 'stock' => 30, 'img' => 'https://images.unsplash.com/photo-1583947215259-38e31be8751f?w=400&auto=format&fit=crop&q=80'],
    ['titulo' => 'Lejía Clorox Tradicional Botella 1L', 'cat' => 'Limpieza', 'precio' => 4.20, 'precio_anterior' => null, 'marca' => 'Clorox', 'stock' => 50, 'img' => 'https://images.unsplash.com/photo-1585421514738-01798e348b17?w=400&auto=format&fit=crop&q=80'],

    // Cuidado Personal
    ['titulo' => 'Pañales Pampers Confort Sec Talla M 44 und', 'cat' => 'Cuidado Personal', 'precio' => 38.50, 'precio_anterior' => 43.80, 'marca' => 'Pampers', 'stock' => 35, 'img' => 'https://images.unsplash.com/photo-1515488042361-ee00e0ddd4e4?w=400&auto=format&fit=crop&q=80'],
    ['titulo' => 'Shampoo Head & Shoulders Limpieza Renovadora 375ml', 'cat' => 'Cuidado Personal', 'precio' => 16.90, 'precio_anterior' => null, 'marca' => 'Head & Shoulders', 'stock' => 40, 'img' => 'https://images.unsplash.com/photo-1535585209827-a15fcdbc4c2d?w=400&auto=format&fit=crop&q=80'],

    // Mascotas
    ['titulo' => 'Comida para Perro Adulto Ricocan Carne 3kg', 'cat' => 'Mascotas', 'precio' => 27.90, 'precio_anterior' => 32.50, 'marca' => 'Ricocan', 'stock' => 25, 'img' => 'https://images.unsplash.com/photo-1589924691995-400dc9ecc119?w=400&auto=format&fit=crop&q=80'],
    ['titulo' => 'Comida para Gato Whiskas Pescado 1kg', 'cat' => 'Mascotas', 'precio' => 14.50, 'precio_anterior' => null, 'marca' => 'Whiskas', 'stock' => 30, 'img' => 'https://images.unsplash.com/photo-1514888286974-6c03e2ca1dba?w=400&auto=format&fit=crop&q=80']
];

foreach ($productos as $p) {
    $catId = $catMap[$p['cat']] ?? 1;
    $stmt = $pdo->prepare("SELECT id_producto FROM producto WHERE LOWER(titulo) = LOWER(?) LIMIT 1");
    $stmt->execute([$p['titulo']]);
    $id = $stmt->fetchColumn();
    if ($id) {
        $up = $pdo->prepare("UPDATE producto SET id_categoria = ?, precio = ?, precio_anterior = ?, marca = ?, stock = ?, imagen = ? WHERE id_producto = ?");
        $up->execute([$catId, $p['precio'], $p['precio_anterior'], $p['marca'], $p['stock'], $p['img'], $id]);
    } else {
        $ins = $pdo->prepare("INSERT INTO producto (titulo, id_categoria, precio, precio_anterior, marca, stock, imagen, es_sugerencia, unidad_medida) VALUES (?, ?, ?, ?, ?, ?, ?, 1, 'UND')");
        $ins->execute([$p['titulo'], $catId, $p['precio'], $p['precio_anterior'], $p['marca'], $p['stock'], $p['img']]);
    }
}

echo "Categorias y productos sincronizados correctamente.\n";
echo "Total categorías: " . count($categorias) . "\n";
echo "Total productos cargados: " . count($productos) . "\n";
