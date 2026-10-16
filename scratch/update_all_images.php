<?php
require_once __DIR__ . '/../Core/dataBase.php';
$db = new DataBase();
$pdo = $db->getConnection();

echo "1. Actualizando Productos con imagenes faltantes...\n";
$prodsUpdate = [
    1 => 'https://images.unsplash.com/photo-1586201375761-83865001e31c?w=600&auto=format&fit=crop&q=80', // Arroz
    2 => 'https://images.unsplash.com/photo-1474979266404-7eaacbcd87c5?w=600&auto=format&fit=crop&q=80', // Aceite
    3 => 'https://images.unsplash.com/photo-1550583724-b2692b85b150?w=600&auto=format&fit=crop&q=80', // Leche
    4 => 'https://images.unsplash.com/photo-1622483767028-3f66f32aef97?w=600&auto=format&fit=crop&q=80', // Gaseosa
];

foreach ($prodsUpdate as $id => $url) {
    $stmt = $pdo->prepare("UPDATE producto SET imagen = :img WHERE id_producto = :id");
    $stmt->execute([':img' => $url, ':id' => $id]);
    echo "Producto $id actualizado con imagen real.\n";
}

echo "\n2. Actualizando Macrocategorias (momento_consumo)...\n";
$macroUpdate = [
    1 => 'https://images.unsplash.com/photo-1610832958506-aa56368176cf?w=600&auto=format&fit=crop&q=80', // Frescos y Proteinas
    2 => 'https://images.unsplash.com/photo-1528750997573-59b89d56f4f7?w=600&auto=format&fit=crop&q=80', // Lacteos y Huevos
    3 => 'https://images.unsplash.com/photo-1588964895597-cfccd6e2dbf9?w=600&auto=format&fit=crop&q=80', // Abarrotes y Despensa
    4 => 'https://images.unsplash.com/photo-1527661591475-527312dd65f5?w=600&auto=format&fit=crop&q=80', // Bebidas y Snacks
    5 => 'https://images.unsplash.com/photo-1584308666744-24d5c474f2ae?w=600&auto=format&fit=crop&q=80', // Otros
];

foreach ($macroUpdate as $id => $url) {
    $stmt = $pdo->prepare("UPDATE momento_consumo SET imagen = :img WHERE id_momento = :id");
    $stmt->execute([':img' => $url, ':id' => $id]);
    echo "Momento $id actualizado con imagen real.\n";
}

echo "\n3. Actualizando Grupos (grupo_momento)...\n";
$grupoUpdate = [
    1  => 'https://images.unsplash.com/photo-1610832958506-aa56368176cf?w=500&auto=format&fit=crop&q=80', // Frutas y Verduras
    2  => 'https://images.unsplash.com/photo-1604503468506-a8da13d82791?w=500&auto=format&fit=crop&q=80', // Carnes y Aves
    3  => 'https://images.unsplash.com/photo-1534422298391-e4f8c172dddb?w=500&auto=format&fit=crop&q=80', // Pescados y Mariscos
    4  => 'https://images.unsplash.com/photo-1550583724-b2692b85b150?w=500&auto=format&fit=crop&q=80', // Lácteos
    5  => 'https://images.unsplash.com/photo-1486297678162-eb2a19b0a32d?w=500&auto=format&fit=crop&q=80', // Quesos y Embutidos
    6  => 'https://images.unsplash.com/photo-1509440159596-0249088772ff?w=500&auto=format&fit=crop&q=80', // Huevos y Panadería
    7  => 'https://images.unsplash.com/photo-1586201375761-83865001e31c?w=500&auto=format&fit=crop&q=80', // Granos y Menestras
    8  => 'https://images.unsplash.com/photo-1474979266404-7eaacbcd87c5?w=500&auto=format&fit=crop&q=80', // Aceites y Condimentos
    9  => 'https://images.unsplash.com/photo-1551462147-ff29053bfc14?w=500&auto=format&fit=crop&q=80', // Pastas y Conservas
    10 => 'https://images.unsplash.com/photo-1622483767028-3f66f32aef97?w=500&auto=format&fit=crop&q=80', // Bebidas
    11 => 'https://images.unsplash.com/photo-1621996346565-e3d5d6281699?w=500&auto=format&fit=crop&q=80', // Snacks y Golosinas
    12 => 'https://images.unsplash.com/photo-1583947215259-38e31be8751f?w=500&auto=format&fit=crop&q=80', // Limpieza del Hogar
    13 => 'https://images.unsplash.com/photo-1556228720-195a672e8a03?w=500&auto=format&fit=crop&q=80', // Cuidado Personal
    15 => 'https://images.unsplash.com/photo-1583511655857-d19b40a7a54e?w=500&auto=format&fit=crop&q=80', // Mascotas
];

foreach ($grupoUpdate as $id => $url) {
    $stmt = $pdo->prepare("UPDATE grupo_momento SET imagen = :img WHERE id_grupo = :id");
    $stmt->execute([':img' => $url, ':id' => $id]);
    echo "Grupo $id actualizado con imagen real.\n";
}

echo "\n4. Actualizando Subcategorias (categoria)...\n";
$catUpdate = [
    1  => 'https://images.unsplash.com/photo-1619566636858-adf3ef46400b?w=400&auto=format&fit=crop&q=80', // Frutas
    2  => 'https://images.unsplash.com/photo-1566385101042-1a0aa0c1268c?w=400&auto=format&fit=crop&q=80', // Verduras
    3  => 'https://images.unsplash.com/photo-1604503468506-a8da13d82791?w=400&auto=format&fit=crop&q=80', // Pollo
    4  => 'https://images.unsplash.com/photo-1588168333986-5078d3ae3976?w=400&auto=format&fit=crop&q=80', // Res
    5  => 'https://images.unsplash.com/photo-1607623814075-e51df1bdc82f?w=400&auto=format&fit=crop&q=80', // Cerdo
    6  => 'https://images.unsplash.com/photo-1534422298391-e4f8c172dddb?w=400&auto=format&fit=crop&q=80', // Pescados
    7  => 'https://images.unsplash.com/photo-1565680018434-b513d5e5fd47?w=400&auto=format&fit=crop&q=80', // Mariscos
    8  => 'https://images.unsplash.com/photo-1550583724-b2692b85b150?w=400&auto=format&fit=crop&q=80', // Leches
    9  => 'https://images.unsplash.com/photo-1488477181946-6428a0291777?w=400&auto=format&fit=crop&q=80', // Yogures
    10 => 'https://images.unsplash.com/photo-1589985270826-4b7bb135bc9d?w=400&auto=format&fit=crop&q=80', // Mantequillas
    11 => 'https://images.unsplash.com/photo-1486297678162-eb2a19b0a32d?w=400&auto=format&fit=crop&q=80', // Quesos
    12 => 'https://images.unsplash.com/photo-1544025162-d76694265947?w=400&auto=format&fit=crop&q=80', // Jamones y Embutidos
    13 => 'https://images.unsplash.com/photo-1516448620398-c5f44bf9f441?w=400&auto=format&fit=crop&q=80', // Huevos
    14 => 'https://images.unsplash.com/photo-1509440159596-0249088772ff?w=400&auto=format&fit=crop&q=80', // Panes y Tostadas
    15 => 'https://images.unsplash.com/photo-1586201375761-83865001e31c?w=400&auto=format&fit=crop&q=80', // Arroz
    16 => 'https://images.unsplash.com/photo-1515543237350-b3eea1ec8082?w=400&auto=format&fit=crop&q=80', // Menestras y Legumbres
    17 => 'https://images.unsplash.com/photo-1474979266404-7eaacbcd87c5?w=400&auto=format&fit=crop&q=80', // Aceites
    18 => 'https://images.unsplash.com/photo-1596040033229-a9821ebd058d?w=400&auto=format&fit=crop&q=80', // Salsas y Especias
    19 => 'https://images.unsplash.com/photo-1551462147-ff29053bfc14?w=400&auto=format&fit=crop&q=80', // Fideos
    20 => 'https://images.unsplash.com/photo-1628268909376-e8c44bb3067f?w=400&auto=format&fit=crop&q=80', // Conservas de Pescado
    21 => 'https://images.unsplash.com/photo-1622483767028-3f66f32aef97?w=400&auto=format&fit=crop&q=80', // Gaseosas
    22 => 'https://images.unsplash.com/photo-1548839140-29a749e1bc4e?w=400&auto=format&fit=crop&q=80', // Aguas
    23 => 'https://images.unsplash.com/photo-1600271886742-f049cd451bba?w=400&auto=format&fit=crop&q=80', // Frugos y Jugos
    24 => 'https://images.unsplash.com/photo-1622543925917-763c34d1a86e?w=400&auto=format&fit=crop&q=80', // Energizantes
    25 => 'https://images.unsplash.com/photo-1566478989037-eec170784d0b?w=400&auto=format&fit=crop&q=80', // Papas y Piqueos
    26 => 'https://images.unsplash.com/photo-1558961363-fa8fdf82db35?w=400&auto=format&fit=crop&q=80', // Galletas
    27 => 'https://images.unsplash.com/photo-1549007994-cb92caebd54b?w=400&auto=format&fit=crop&q=80', // Chocolates
    28 => 'https://images.unsplash.com/photo-1583947215259-38e31be8751f?w=400&auto=format&fit=crop&q=80', // Detergentes
    29 => 'https://images.unsplash.com/photo-1585421514284-efb74c2b69ba?w=400&auto=format&fit=crop&q=80', // Lavavajillas
    30 => 'https://images.unsplash.com/photo-1585421514738-01798e348b17?w=400&auto=format&fit=crop&q=80', // Desinfectantes
    31 => 'https://images.unsplash.com/photo-1600857544200-b2f666a9a2ec?w=400&auto=format&fit=crop&q=80', // Jabones
    32 => 'https://images.unsplash.com/photo-1535585209827-a15fcdbc4c2d?w=400&auto=format&fit=crop&q=80', // Shampoo y Acondicionador
    33 => 'https://images.unsplash.com/photo-1559599101-f09722fb4948?w=400&auto=format&fit=crop&q=80', // Higiene Oral
    35 => 'https://images.unsplash.com/photo-1589924691995-400dc9ecc119?w=400&auto=format&fit=crop&q=80', // Alimento para Perros (eliminado Images/icono.png)
    36 => 'https://images.unsplash.com/photo-1514888286974-6c03e2ca1dba?w=400&auto=format&fit=crop&q=80', // Alimento para Gatos (eliminado Images/icono.png)
];

foreach ($catUpdate as $id => $url) {
    $stmt = $pdo->prepare("UPDATE categoria SET imagen = :img WHERE id_categoria = :id");
    $stmt->execute([':img' => $url, ':id' => $id]);
    echo "Categoria $id actualizada con imagen real.\n";
}

echo "\n¡TODAS LAS IMAGENES ACTUALIZADAS CON FOTOS REALES!\n";
