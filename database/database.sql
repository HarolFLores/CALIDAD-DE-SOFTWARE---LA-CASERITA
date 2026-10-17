-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Servidor: 127.0.0.1
-- Tiempo de generación: 17-10-2026 a las 01:19:26
-- Versión del servidor: 10.4.32-MariaDB
-- Versión de PHP: 8.0.30

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Base de datos: `lacaserita_db`
--

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `categoria`
--

CREATE TABLE `categoria` (
  `id_categoria` int(11) NOT NULL,
  `id_momento` int(11) DEFAULT NULL,
  `id_grupo` int(11) DEFAULT NULL,
  `nombre` varchar(100) NOT NULL,
  `descripcion` text DEFAULT NULL,
  `imagen` varchar(255) DEFAULT NULL,
  `activo` tinyint(1) NOT NULL DEFAULT 1,
  `fecha_creacion` datetime NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Volcado de datos para la tabla `categoria`
--

INSERT INTO `categoria` (`id_categoria`, `id_momento`, `id_grupo`, `nombre`, `descripcion`, `imagen`, `activo`, `fecha_creacion`) VALUES
(1, 1, 1, 'Frutas', 'Frutas frescas de temporada', 'https://images.unsplash.com/photo-1619566636858-adf3ef46400b?w=400&auto=format&fit=crop&q=80', 1, '2026-09-29 13:56:03'),
(2, 1, 1, 'Verduras', 'Verduras y hortalizas frescas', 'https://images.unsplash.com/photo-1566385101042-1a0aa0c1268c?w=400&auto=format&fit=crop&q=80', 1, '2026-09-29 13:52:37'),
(3, 1, 2, 'Pollo', 'Pollo entero y en piezas frescas', 'https://images.unsplash.com/photo-1604503468506-a8da13d82791?w=400&auto=format&fit=crop&q=80', 1, '2026-09-29 13:52:37'),
(4, 1, 2, 'Res', 'Cortes de res y carne de vacuno', 'https://images.unsplash.com/photo-1588168333986-5078d3ae3976?w=400&auto=format&fit=crop&q=80', 1, '2026-09-29 13:52:37'),
(5, 1, 2, 'Cerdo', 'Cerdo y cortes frescos', 'https://images.unsplash.com/photo-1607623814075-e51df1bdc82f?w=400&auto=format&fit=crop&q=80', 1, '2026-09-29 13:52:37'),
(6, 1, 3, 'Pescados', 'Pescados frescos del mar y río', 'https://images.unsplash.com/photo-1534422298391-e4f8c172dddb?w=400&auto=format&fit=crop&q=80', 1, '2026-09-29 13:52:37'),
(7, 1, 3, 'Mariscos', 'Camarones, conchas y mariscos frescos', 'https://images.unsplash.com/photo-1565680018434-b513d5e5fd47?w=400&auto=format&fit=crop&q=80', 1, '2026-09-29 13:52:37'),
(8, 2, 4, 'Leches', 'Leches pasteurizadas, UHT y evaporadas', 'https://images.unsplash.com/photo-1550583724-b2692b85b150?w=400&auto=format&fit=crop&q=80', 1, '2026-09-29 13:52:37'),
(9, 2, 4, 'Yogures', 'Yogures naturales y con sabor', 'https://images.unsplash.com/photo-1488477181946-6428a0291777?w=400&auto=format&fit=crop&q=80', 1, '2026-09-29 13:52:37'),
(10, 2, 4, 'Mantequillas', 'Mantequillas y margarinas', 'https://images.unsplash.com/photo-1589985270826-4b7bb135bc9d?w=400&auto=format&fit=crop&q=80', 1, '2026-09-29 13:52:37'),
(11, 2, 5, 'Quesos', 'Quesos frescos, madurados y procesados', 'https://images.unsplash.com/photo-1486297678162-eb2a19b0a32d?w=400&auto=format&fit=crop&q=80', 1, '2026-09-29 13:52:37'),
(12, 2, 5, 'Jamones y Embutidos', 'Jamones, salchichas y chorizos', 'https://images.unsplash.com/photo-1544025162-d76694265947?w=400&auto=format&fit=crop&q=80', 1, '2026-09-29 13:52:37'),
(13, 2, 6, 'Huevos', 'Huevos de gallina y codorniz', 'https://images.unsplash.com/photo-1516448620398-c5f44bf9f441?w=400&auto=format&fit=crop&q=80', 1, '2026-09-29 13:52:37'),
(14, 2, 6, 'Panes y Tostadas', 'Panes de molde, tostadas y baguetes', 'https://images.unsplash.com/photo-1509440159596-0249088772ff?w=400&auto=format&fit=crop&q=80', 1, '2026-09-29 13:52:37'),
(15, 3, 7, 'Arroz', 'Arroz blanco, integral y extra', 'https://images.unsplash.com/photo-1586201375761-83865001e31c?w=400&auto=format&fit=crop&q=80', 1, '2026-09-29 13:52:37'),
(16, 3, 7, 'Menestras y Legumbres', 'Lentejas, frijoles, garbanzos y más', 'https://images.unsplash.com/photo-1515543237350-b3eea1ec8082?w=400&auto=format&fit=crop&q=80', 1, '2026-09-29 13:52:37'),
(17, 3, 8, 'Aceites', 'Aceites vegetales, de oliva y de maíz', 'https://images.unsplash.com/photo-1474979266404-7eaacbcd87c5?w=400&auto=format&fit=crop&q=80', 1, '2026-09-29 13:52:37'),
(18, 3, 8, 'Salsas y Especias', 'Salsas, aderezos y sazonadores', 'https://images.unsplash.com/photo-1596040033229-a9821ebd058d?w=400&auto=format&fit=crop&q=80', 1, '2026-09-29 13:52:37'),
(19, 3, 9, 'Fideos', 'Fideos y pastas de trigo', 'https://images.unsplash.com/photo-1551462147-ff29053bfc14?w=400&auto=format&fit=crop&q=80', 1, '2026-09-29 13:52:37'),
(20, 3, 9, 'Conservas de Pescado', 'Atún, sardinas y conservas en lata', 'https://images.unsplash.com/photo-1628268909376-e8c44bb3067f?w=400&auto=format&fit=crop&q=80', 1, '2026-09-29 13:52:37'),
(21, 4, 10, 'Gaseosas', 'Gaseosas y refrescos carbonatados', 'https://images.unsplash.com/photo-1622483767028-3f66f32aef97?w=400&auto=format&fit=crop&q=80', 1, '2026-09-29 13:52:37'),
(22, 4, 10, 'Aguas', 'Aguas de mesa y minerales', 'https://images.unsplash.com/photo-1548839140-29a749e1bc4e?w=400&auto=format&fit=crop&q=80', 1, '2026-09-29 13:52:37'),
(23, 4, 10, 'Frugos y Jugos', 'Jugos naturales, néctares y frugos', 'https://images.unsplash.com/photo-1600271886742-f049cd451bba?w=400&auto=format&fit=crop&q=80', 1, '2026-09-29 13:52:37'),
(24, 4, 10, 'Energizantes', 'Bebidas energéticas e isotónicas', 'https://images.unsplash.com/photo-1622543925917-763c34d1a86e?w=400&auto=format&fit=crop&q=80', 1, '2026-09-29 13:52:37'),
(25, 4, 11, 'Papas y Piqueos', 'Papas fritas, chifles y snacks salados', 'https://images.unsplash.com/photo-1566478989037-eec170784d0b?w=400&auto=format&fit=crop&q=80', 1, '2026-09-29 13:52:37'),
(26, 4, 11, 'Galletas', 'Galletas dulces, saladas y crackers', 'https://images.unsplash.com/photo-1558961363-fa8fdf82db35?w=400&auto=format&fit=crop&q=80', 1, '2026-09-29 13:52:37'),
(27, 4, 11, 'Chocolates', 'Chocolates, bombones y golosinas', 'https://images.unsplash.com/photo-1549007994-cb92caebd54b?w=400&auto=format&fit=crop&q=80', 1, '2026-09-29 13:52:37'),
(28, 5, 12, 'Detergentes', 'Detergentes en polvo y líquido para ropa', 'https://images.unsplash.com/photo-1583947215259-38e31be8751f?w=400&auto=format&fit=crop&q=80', 1, '2026-09-29 13:52:37'),
(29, 5, 12, 'Lavavajillas', 'Lavavajillas y desengrasantes', 'https://images.unsplash.com/photo-1585421514284-efb74c2b69ba?w=400&auto=format&fit=crop&q=80', 1, '2026-09-29 13:52:37'),
(30, 5, 12, 'Desinfectantes', 'Lejías, desinfectantes y limpiadores', 'https://images.unsplash.com/photo-1585421514738-01798e348b17?w=400&auto=format&fit=crop&q=80', 1, '2026-09-29 13:52:37'),
(31, 5, 13, 'Jabones', 'Jabones de tocador y antibacteriales', 'https://images.unsplash.com/photo-1600857544200-b2f666a9a2ec?w=400&auto=format&fit=crop&q=80', 1, '2026-09-29 13:52:37'),
(32, 5, 13, 'Shampoo y Acondicionador', 'Shampoos, acondicionadores y tratamientos', 'https://images.unsplash.com/photo-1535585209827-a15fcdbc4c2d?w=400&auto=format&fit=crop&q=80', 1, '2026-09-29 13:52:37'),
(33, 5, 13, 'Higiene Oral', 'Cepillos, pastas dentales y enjuagues', 'https://images.unsplash.com/photo-1559599101-f09722fb4948?w=400&auto=format&fit=crop&q=80', 1, '2026-09-29 13:52:37'),
(35, 5, 15, 'Alimento para Perros', 'Comida seca, húmeda y snacks para perros', 'https://images.unsplash.com/photo-1589924691995-400dc9ecc119?w=400&auto=format&fit=crop&q=80', 1, '2026-09-29 15:12:51'),
(36, 5, 15, 'Alimento para Gatos', 'Comida seca, húmeda y snacks para gatos', 'https://images.unsplash.com/photo-1514888286974-6c03e2ca1dba?w=400&auto=format&fit=crop&q=80', 1, '2026-09-29 15:12:51');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `configuracion_correo`
--

CREATE TABLE `configuracion_correo` (
  `id_config` int(11) NOT NULL,
  `nombre_empresa` varchar(150) NOT NULL DEFAULT 'La Caserita',
  `logo_url` varchar(255) DEFAULT 'Images/logo.png',
  `cabecera_correo_url` varchar(255) DEFAULT NULL,
  `telefono_whatsapp` varchar(30) DEFAULT '+51 987654321',
  `correo_soporte` varchar(150) DEFAULT 'contacto@lacaserita.com',
  `enlace_seguimiento` varchar(255) DEFAULT 'index.php?action=mi_cuenta',
  `direccion_fisica` varchar(255) DEFAULT 'Av. Principal 123, Lima - Perú',
  `color_primario` varchar(20) NOT NULL DEFAULT '#0d5f6e',
  `color_secundario` varchar(20) NOT NULL DEFAULT '#ff7f00',
  `smtp_host` varchar(150) DEFAULT NULL,
  `smtp_port` int(11) DEFAULT 587,
  `smtp_user` varchar(150) DEFAULT NULL,
  `smtp_pass` varchar(255) DEFAULT NULL,
  `smtp_seguridad` varchar(10) NOT NULL DEFAULT 'tls',
  `activo` tinyint(1) NOT NULL DEFAULT 1,
  `fecha_actualizacion` datetime NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Volcado de datos para la tabla `configuracion_correo`
--

INSERT INTO `configuracion_correo` (`id_config`, `nombre_empresa`, `logo_url`, `cabecera_correo_url`, `telefono_whatsapp`, `correo_soporte`, `enlace_seguimiento`, `direccion_fisica`, `color_primario`, `color_secundario`, `smtp_host`, `smtp_port`, `smtp_user`, `smtp_pass`, `smtp_seguridad`, `activo`, `fecha_actualizacion`) VALUES
(1, 'Tienda La Caserita', 'Images/logo.png', 'Images/abarrotes-banner.png', '+51 987654321', 'erikcarrasco455@gmail.com', 'index.php?action=mi_cuenta', 'Av. Central 450, Lima', '#0d5f6e', '#ff7f00', 'smtp.gmail.com', 587, 'erikcarrasco455@gmail.com', 'dkgkbxgmenizalsi', 'tls', 1, '2026-10-16 14:45:25');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `detalle_pedido`
--

CREATE TABLE `detalle_pedido` (
  `id_detalle` int(11) NOT NULL,
  `id_pedido` int(11) NOT NULL,
  `id_producto` int(11) NOT NULL,
  `titulo_producto` varchar(150) DEFAULT NULL,
  `unidad_medida` varchar(30) DEFAULT NULL,
  `cantidad` int(11) NOT NULL,
  `precio_unitario` decimal(10,2) NOT NULL,
  `costo_unitario` decimal(10,2) NOT NULL DEFAULT 0.00,
  `subtotal` decimal(10,2) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Volcado de datos para la tabla `detalle_pedido`
--

INSERT INTO `detalle_pedido` (`id_detalle`, `id_pedido`, `id_producto`, `titulo_producto`, `unidad_medida`, `cantidad`, `precio_unitario`, `costo_unitario`, `subtotal`) VALUES
(1, 1, 1, NULL, NULL, 1, 4.80, 3.80, 4.80),
(7, 6, 1, 'Arroz Superior Costeño 1kg', 'BOLSA 1KG', 1, 4.80, 3.80, 4.80),
(8, 7, 7, 'Cebolla Roja Fresca 1kg', 'UND', 1, 3.20, 0.00, 3.20),
(9, 7, 10, 'Mandarina Sin Pepa Dulce 1kg', 'UND', 1, 4.20, 0.00, 4.20),
(10, 8, 7, 'Cebolla Roja Fresca 1kg', 'UND', 2, 3.20, 0.00, 6.40),
(11, 8, 8, 'Manzana Delicia Seleccionada 1kg', 'UND', 1, 5.40, 0.00, 5.40),
(12, 9, 7, 'Cebolla Roja Fresca 1kg', 'UND', 2, 3.20, 0.00, 6.40),
(13, 10, 9, 'Plátano de Seda de Chanchamayo (Mano)', 'UND', 1, 4.50, 0.00, 4.50),
(14, 11, 7, 'Cebolla Roja Fresca 1kg', 'UND', 1, 3.20, 0.00, 3.20),
(15, 12, 7, 'Cebolla Roja Fresca 1kg', 'UND', 1, 3.20, 0.00, 3.20),
(16, 13, 2, NULL, NULL, 5, 9.50, 7.50, 47.50),
(17, 14, 2, NULL, NULL, 1, 9.50, 7.50, 9.50),
(20, 17, 31, NULL, NULL, 1, 15000.00, 0.00, 15000.00),
(21, 18, 7, 'Cebolla Roja Fresca 1kg', 'UND', 1, 3.20, 0.00, 3.20),
(22, 19, 1, 'Arroz Superior Costeño 1kg', 'BOLSA 1KG', 1, 4.80, 3.80, 4.80),
(23, 20, 7, 'Cebolla Roja Fresca 1kg', 'UND', 3, 3.20, 0.00, 9.60),
(24, 21, 9, 'Plátano de Seda de Chanchamayo (Mano)', 'UND', 1, 4.50, 0.00, 4.50),
(25, 22, 8, 'Manzana Delicia Seleccionada 1kg', 'UND', 1, 5.40, 0.00, 5.40),
(26, 22, 9, 'Plátano de Seda de Chanchamayo (Mano)', 'UND', 1, 4.50, 0.00, 4.50),
(27, 23, 8, 'Manzana Delicia Seleccionada 1kg', 'UND', 1, 5.40, 0.00, 5.40),
(28, 24, 1, 'Arroz Superior Costeño 1kg', 'BOLSA 1KG', 2, 4.80, 3.80, 9.60),
(30, 26, 20, 'Fideos Spaghetti Don Vittorio 500g', 'UND', 1, 3.40, 0.00, 3.40),
(31, 26, 8, 'Manzana Delicia Seleccionada 1kg', 'UND', 2, 5.40, 0.00, 10.80),
(32, 27, 9, 'Plátano de Seda de Chanchamayo (Mano)', 'UND', 1, 4.50, 0.00, 4.50),
(33, 28, 7, 'Cebolla Roja Fresca 1kg', 'UND', 1, 3.20, 0.00, 3.20),
(34, 29, 18, 'Arroz Extra Premium Costeño Saco 5kg', 'UND', 1, 18.50, 0.00, 18.50),
(35, 30, 9, 'Plátano de Seda de Chanchamayo (Mano)', 'UND', 1, 4.50, 0.00, 4.50);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `direccion`
--

CREATE TABLE `direccion` (
  `id_direccion` int(11) NOT NULL,
  `id_usuario` int(11) NOT NULL,
  `departamento` varchar(50) NOT NULL DEFAULT 'Lambayeque',
  `provincia` varchar(50) NOT NULL DEFAULT 'Chiclayo',
  `distrito` varchar(50) NOT NULL,
  `calle` varchar(150) NOT NULL,
  `numero` varchar(20) DEFAULT NULL,
  `lote_manzana` varchar(30) DEFAULT NULL,
  `id_distrito` int(11) DEFAULT NULL,
  `referencia` text DEFAULT NULL,
  `telefono_contacto` varchar(20) DEFAULT NULL,
  `es_principal` tinyint(1) NOT NULL DEFAULT 0,
  `activo` tinyint(1) NOT NULL DEFAULT 1,
  `fecha_registro` datetime NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Volcado de datos para la tabla `direccion`
--

INSERT INTO `direccion` (`id_direccion`, `id_usuario`, `departamento`, `provincia`, `distrito`, `calle`, `numero`, `lote_manzana`, `id_distrito`, `referencia`, `telefono_contacto`, `es_principal`, `activo`, `fecha_registro`) VALUES
(1, 2, 'SAD', 'AASDA', 'ASDSA', 'ASDAS', NULL, NULL, NULL, 'ASDSA', NULL, 0, 1, '2026-09-28 19:23:52'),
(2, 2, 'adsad', 'asdasd', 'asdas', 'afassafasaas', NULL, NULL, NULL, 'asdsadas', NULL, 0, 1, '2026-09-28 21:21:01'),
(3, 2, 'hola', 'hola', 'hola', 'hola', NULL, NULL, NULL, 'hola', NULL, 1, 1, '2026-09-28 21:21:23'),
(4, 2, 'Lambayeque', 'Chiclayo', 'La Victoria', 'Av. José Balta', '456', 'Lote 12', 2, 'Casa color amarillo, frente al parque...', NULL, 0, 1, '2026-09-28 23:51:06'),
(5, 2, 'Lambayeque', 'Chiclayo', 'La Victoria', 'Av. José Balta', '456', 'Lote 12', 2, 'Casa color amarillo, frente al parque...', NULL, 0, 1, '2026-09-28 23:52:34'),
(6, 2, 'Lambayeque', 'Chiclayo', 'La Victoria', 'Av. José Balta', '456', 'Lote 12', 2, 'Casa color amarillo, frente al parque...', NULL, 0, 1, '2026-09-28 23:54:28'),
(7, 2, 'Lambayeque', 'Chiclayo', 'La Victoria', 'Av. José Balta', '456', 'Lote 12', 2, 'Casa color amarillo, frente al parque...', NULL, 0, 1, '2026-09-28 23:54:44'),
(8, 2, 'Lambayeque', 'Chiclayo', 'La Victoria', 'Av. José Balta', '456', 'Lote 12', 2, 'Casa color amarillo, frente al parque...', NULL, 0, 1, '2026-09-29 00:06:19');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `distrito_delivery`
--

CREATE TABLE `distrito_delivery` (
  `id_distrito` int(11) NOT NULL,
  `nombre` varchar(80) NOT NULL,
  `costo_delivery` decimal(10,2) NOT NULL DEFAULT 5.00,
  `activo` tinyint(1) NOT NULL DEFAULT 1,
  `orden` int(11) NOT NULL DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Volcado de datos para la tabla `distrito_delivery`
--

INSERT INTO `distrito_delivery` (`id_distrito`, `nombre`, `costo_delivery`, `activo`, `orden`) VALUES
(1, 'Chiclayo Centro', 5.00, 1, 1),
(2, 'La Victoria', 7.00, 1, 2),
(3, 'José Leonardo Ortiz', 8.00, 1, 3),
(4, 'Pimentel', 12.00, 1, 4),
(5, 'Santa Victoria', 6.00, 1, 5),
(6, 'Reque', 10.00, 0, 6),
(7, 'Monsefu', 10.00, 0, 7),
(8, 'Eten', 12.00, 0, 8),
(9, 'Pomalca', 9.00, 0, 9),
(10, 'Tuman', 10.00, 0, 10),
(11, 'Lambayeque', 12.00, 0, 11),
(12, 'San Jose', 11.00, 0, 12),
(13, 'Ferrenafe', 15.00, 0, 13),
(14, 'Otros distritos', 15.00, 0, 99);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `envio_delivery`
--

CREATE TABLE `envio_delivery` (
  `id_envio` int(11) NOT NULL,
  `id_pedido` int(11) NOT NULL,
  `id_repartidor` int(11) DEFAULT NULL,
  `nombre_destinatario` varchar(100) NOT NULL,
  `celular_contacto` varchar(20) NOT NULL,
  `direccion_destino` text NOT NULL,
  `referencia` text DEFAULT NULL,
  `costo_delivery` decimal(10,2) NOT NULL DEFAULT 5.00,
  `estado_delivery` enum('Pendiente','Asignado','En Ruta','Entregado','Fallido') NOT NULL DEFAULT 'Pendiente',
  `cobrado_por_repartidor` tinyint(1) NOT NULL DEFAULT 0,
  `monto_cobrado` decimal(10,2) NOT NULL DEFAULT 0.00,
  `liquidado_en_caja` tinyint(1) NOT NULL DEFAULT 0,
  `fecha_asignacion` datetime DEFAULT NULL,
  `fecha_entrega` datetime DEFAULT NULL,
  `notas_entrega` text DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Volcado de datos para la tabla `envio_delivery`
--

INSERT INTO `envio_delivery` (`id_envio`, `id_pedido`, `id_repartidor`, `nombre_destinatario`, `celular_contacto`, `direccion_destino`, `referencia`, `costo_delivery`, `estado_delivery`, `cobrado_por_repartidor`, `monto_cobrado`, `liquidado_en_caja`, `fecha_asignacion`, `fecha_entrega`, `notas_entrega`) VALUES
(1, 6, 4, 'Erik Carrasco', '999999999', 'Av. José Balta, #456, Lote Lote 12, La Victoria, Chiclayo', 'Casa color amarillo, frente al parque...', 7.00, 'Fallido', 0, 0.00, 0, '2026-10-01 01:05:26', NULL, 'Horario: Programado para 29/09/2026 09:06 | Programado: 2026-09-29 09:06:19\nHorario: Programado para 29/09/2026 09:06 | Programado: 2026-09-29 09:06:19\n'),
(2, 8, 4, 'Erik Carrasco', '999999999', 'Av. José Balta, #456, Lote Lote 12, La Victoria, Chiclayo', 'Casa color amarillo, frente al parque...', 7.00, 'Pendiente', 0, 0.00, 0, '2026-09-30 23:05:55', '2026-09-30 23:06:14', 'Horario: Lo antes posible (30-45 min)\nTest nota\nPrueba entrega\n\nHorario: Lo antes posible (30-45 min)\r\nTest nota\r\nPrueba entrega'),
(3, 18, NULL, 'Erik Carrasco', '942337947', 'Av. José Balta, #456, Lote Lote 12, La Victoria, Chiclayo', 'Casa color amarillo, frente al parque...', 7.00, 'Pendiente', 0, 0.00, 0, NULL, NULL, 'Horario: Lo antes posible (30-45 min)'),
(4, 20, NULL, 'Erik Carrasco', '942337947', 'Av. José Balta, #456, Lote Lote 12, La Victoria, Chiclayo', 'Casa color amarillo, frente al parque...', 7.00, 'Asignado', 0, 0.00, 0, NULL, NULL, 'Horario: Lo antes posible (30-45 min)\nHorario: Lo antes posible (30-45 min)'),
(5, 21, NULL, 'Erik Carrasco', '942337947', 'Av. José Balta, #456, Lote Lote 12, La Victoria, Chiclayo', 'Casa color amarillo, frente al parque...', 7.00, 'Pendiente', 0, 0.00, 0, NULL, NULL, 'Horario: Lo antes posible (30-45 min)'),
(6, 23, NULL, 'Erik Carrasco', '942337947', 'Av. José Balta, #456, Lote Lote 12, La Victoria, Chiclayo', 'Casa color amarillo, frente al parque...', 7.00, 'Pendiente', 0, 0.00, 0, NULL, NULL, 'Horario: Lo antes posible (30-45 min)'),
(7, 26, NULL, 'Erik Carrasco', '942337947', 'Av. José Balta, #456, Lote Lote 12, La Victoria, Chiclayo', 'Casa color amarillo, frente al parque...', 7.00, 'Pendiente', 0, 0.00, 0, NULL, NULL, 'Horario: Lo antes posible (30-45 min)'),
(8, 28, NULL, 'Erik Carrasco', '942337947', 'Av. José Balta, #456, Lote Lote 12, La Victoria, Chiclayo', 'Casa color amarillo, frente al parque...', 7.00, 'Pendiente', 0, 0.00, 0, NULL, NULL, 'Horario: Lo antes posible (30-45 min)'),
(9, 29, NULL, 'Erik Carrasco', '942337947', 'Av. José Balta, #456, Lote Lote 12, La Victoria, Chiclayo', 'Casa color amarillo, frente al parque...', 7.00, 'Pendiente', 0, 0.00, 0, NULL, NULL, 'Horario: Lo antes posible (30-45 min)'),
(10, 30, NULL, 'Erik Carrasco', '942337947', 'Av. José Balta, #456, Lote Lote 12, La Victoria, Chiclayo', 'Casa color amarillo, frente al parque...', 7.00, 'Pendiente', 0, 0.00, 0, NULL, NULL, 'Horario: Lo antes posible (30-45 min)');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `grupo_momento`
--

CREATE TABLE `grupo_momento` (
  `id_grupo` int(11) NOT NULL,
  `id_momento` int(11) NOT NULL,
  `nombre` varchar(100) NOT NULL,
  `descripcion` text DEFAULT NULL,
  `imagen` varchar(255) DEFAULT NULL,
  `color` varchar(30) DEFAULT '#ff7f00',
  `activo` tinyint(1) NOT NULL DEFAULT 1
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Volcado de datos para la tabla `grupo_momento`
--

INSERT INTO `grupo_momento` (`id_grupo`, `id_momento`, `nombre`, `descripcion`, `imagen`, `color`, `activo`) VALUES
(1, 1, 'Frutas y Verduras', 'Frutas frescas y verduras de temporada', 'https://images.unsplash.com/photo-1610832958506-aa56368176cf?w=500&auto=format&fit=crop&q=80', '#ff7f00', 1),
(2, 1, 'Carnes y Aves', 'Pollo, res, cerdo y cortes frescos', 'https://images.unsplash.com/photo-1604503468506-a8da13d82791?w=500&auto=format&fit=crop&q=80', '#15803d', 1),
(3, 1, 'Pescados y Mariscos', 'Pescados y mariscos frescos del día', 'https://images.unsplash.com/photo-1534422298391-e4f8c172dddb?w=500&auto=format&fit=crop&q=80', '#ff7f00', 1),
(4, 2, 'Lácteos', 'Leches, yogures, mantequillas y derivados', 'https://images.unsplash.com/photo-1550583724-b2692b85b150?w=500&auto=format&fit=crop&q=80', '#ff7f00', 1),
(5, 2, 'Quesos y Embutidos', 'Quesos frescos, madurados, jamones y embutidos', 'https://images.unsplash.com/photo-1486297678162-eb2a19b0a32d?w=500&auto=format&fit=crop&q=80', '#ff7f00', 1),
(6, 2, 'Huevos y Panadería', 'Huevos de corral, panes y tostadas', 'https://images.unsplash.com/photo-1509440159596-0249088772ff?w=500&auto=format&fit=crop&q=80', '#ff7f00', 1),
(7, 3, 'Granos y Menestras', 'Arroz, lentejas, frijoles y legumbres', 'https://images.unsplash.com/photo-1586201375761-83865001e31c?w=500&auto=format&fit=crop&q=80', '#ff7f00', 1),
(8, 3, 'Aceites y Condimentos', 'Aceites vegetales, de oliva, salsas y especias', 'https://images.unsplash.com/photo-1474979266404-7eaacbcd87c5?w=500&auto=format&fit=crop&q=80', '#ff7f00', 1),
(9, 3, 'Pastas y Conservas', 'Fideos, salsas rojas y conservas', 'https://images.unsplash.com/photo-1551462147-ff29053bfc14?w=500&auto=format&fit=crop&q=80', '#ff7f00', 1),
(10, 4, 'Bebidas', 'Gaseosas, aguas, jugos e hidratantes', 'https://images.unsplash.com/photo-1622483767028-3f66f32aef97?w=500&auto=format&fit=crop&q=80', '#ff7f00', 1),
(11, 4, 'Snacks y Golosinas', 'Papas, piqueos, galletas, chocolates y dulces', 'https://images.unsplash.com/photo-1621996346565-e3d5d6281699?w=500&auto=format&fit=crop&q=80', '#15803d', 1),
(12, 5, 'Limpieza del Hogar', 'Detergentes, desinfectantes y lavavajillas', 'https://images.unsplash.com/photo-1583947215259-38e31be8751f?w=500&auto=format&fit=crop&q=80', '#ff7f00', 1),
(13, 5, 'Cuidado Personal', 'Jabones, shampoo e higiene oral', 'https://images.unsplash.com/photo-1556228720-195a672e8a03?w=500&auto=format&fit=crop&q=80', '#ff7f00', 1),
(15, 5, 'Mascotas', 'Alimentos y accesorios para mascotas', 'https://images.unsplash.com/photo-1583511655857-d19b40a7a54e?w=500&auto=format&fit=crop&q=80', '#ff7f00', 1);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `inventario`
--

CREATE TABLE `inventario` (
  `id_inventario` int(11) NOT NULL,
  `id_producto` int(11) NOT NULL,
  `stock_actual` int(11) NOT NULL DEFAULT 0,
  `stock_minimo` int(11) NOT NULL DEFAULT 5,
  `stock_maximo` int(11) NOT NULL DEFAULT 100,
  `ubicacion` varchar(100) NOT NULL DEFAULT 'Almacén principal',
  `ultima_act` datetime NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Volcado de datos para la tabla `inventario`
--

INSERT INTO `inventario` (`id_inventario`, `id_producto`, `stock_actual`, `stock_minimo`, `stock_maximo`, `ubicacion`, `ultima_act`) VALUES
(1, 1, 75, 10, 200, 'Pasillo 1 - Estante A', '2026-10-15 22:31:48'),
(2, 2, 7, 10, 150, 'Pasillo 1 - Estante B', '2026-10-16 17:02:22'),
(3, 3, 126, 20, 300, 'Pasillo 2 - Estante A', '2026-10-10 11:38:12'),
(4, 4, 60, 15, 120, 'Refrigerador 1', '2026-09-24 11:37:25'),
(5, 19, 20, 5, 100, 'Almacén principal', '2026-09-30 13:26:50'),
(6, 31, 9, 5, 100, '', '2026-10-03 10:12:59'),
(7, 32, 10, 5, 100, '', '2026-10-02 19:41:43'),
(8, 33, 15, 5, 100, '', '2026-10-02 19:48:46'),
(9, 34, 9, 5, 100, '', '2026-10-02 19:50:46'),
(10, 5, 50, 5, 100, 'Almacén principal', '2026-10-16 16:53:15'),
(11, 6, 80, 5, 100, 'Almacén principal', '2026-10-16 16:53:15'),
(12, 7, 48, 5, 100, 'Almacén principal', '2026-10-16 16:53:15'),
(13, 8, 9, 10, 100, 'Almacén principal', '2026-10-16 17:26:24'),
(14, 9, 35, 5, 100, 'Almacén principal', '2026-10-16 16:53:15'),
(15, 10, 64, 5, 100, 'Almacén principal', '2026-10-16 16:53:15'),
(16, 11, 30, 5, 100, 'Almacén principal', '2026-10-16 16:53:15'),
(17, 12, 25, 5, 100, 'Almacén principal', '2026-10-16 16:53:15'),
(18, 13, 20, 5, 100, 'Almacén principal', '2026-10-16 16:53:15'),
(19, 14, 45, 5, 100, 'Almacén principal', '2026-10-16 16:53:15'),
(20, 15, 35, 5, 100, 'Almacén principal', '2026-10-16 16:53:15'),
(21, 16, 40, 5, 100, 'Almacén principal', '2026-10-16 16:53:15'),
(22, 17, 50, 5, 100, 'Almacén principal', '2026-10-16 16:53:15'),
(23, 18, 54, 5, 100, 'Almacén principal', '2026-10-16 16:53:15'),
(24, 20, 89, 5, 100, 'Almacén principal', '2026-10-16 16:53:15'),
(25, 21, 50, 5, 100, 'Almacén principal', '2026-10-16 16:53:15'),
(26, 22, 70, 5, 100, 'Almacén principal', '2026-10-16 16:53:15'),
(27, 23, 60, 5, 100, 'Almacén principal', '2026-10-16 16:53:15'),
(28, 24, 45, 5, 100, 'Almacén principal', '2026-10-16 16:53:15'),
(29, 25, 30, 5, 100, 'Almacén principal', '2026-10-16 16:53:15'),
(30, 26, 50, 5, 100, 'Almacén principal', '2026-10-16 16:53:15'),
(31, 27, 35, 5, 100, 'Almacén principal', '2026-10-16 16:53:15'),
(32, 28, 40, 5, 100, 'Almacén principal', '2026-10-16 16:53:15'),
(33, 29, 25, 5, 100, 'Almacén principal', '2026-10-16 16:53:15'),
(34, 30, 30, 5, 100, 'Almacén principal', '2026-10-16 16:53:15');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `kardex`
--

CREATE TABLE `kardex` (
  `id_kardex` int(11) NOT NULL,
  `id_producto` int(11) NOT NULL,
  `id_usuario` int(11) NOT NULL,
  `tipo_movimiento` enum('ENTRADA','SALIDA','AJUSTE') NOT NULL,
  `motivo` varchar(100) NOT NULL,
  `cantidad` int(11) NOT NULL,
  `costo_unitario` decimal(10,2) NOT NULL DEFAULT 0.00,
  `precio_unitario` decimal(10,2) NOT NULL DEFAULT 0.00,
  `stock_anterior` int(11) NOT NULL,
  `stock_actual` int(11) NOT NULL,
  `referencia_id` int(11) DEFAULT NULL,
  `referencia_tipo` varchar(50) DEFAULT NULL,
  `fecha` datetime NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Volcado de datos para la tabla `kardex`
--

INSERT INTO `kardex` (`id_kardex`, `id_producto`, `id_usuario`, `tipo_movimiento`, `motivo`, `cantidad`, `costo_unitario`, `precio_unitario`, `stock_anterior`, `stock_actual`, `referencia_id`, `referencia_tipo`, `fecha`) VALUES
(5, 1, 1, 'SALIDA', 'Venta POS POS-20260927-061543-24', 1, 3.80, 4.80, 80, 79, 1, 'pedido', '2026-09-26 23:15:43'),
(6, 1, 2, 'SALIDA', 'Venta Web Directa #PED-20260929-123F1', 1, 3.80, 4.80, 79, 78, 6, 'pedido', '2026-09-29 00:06:19'),
(7, 7, 2, 'SALIDA', 'Venta Web Directa #PED-20260929-65E3C', 1, 0.00, 3.20, 60, 59, 7, 'pedido', '2026-09-29 01:49:39'),
(8, 10, 2, 'SALIDA', 'Venta Web Directa #PED-20260929-65E3C', 1, 0.00, 4.20, 65, 64, 7, 'pedido', '2026-09-29 01:49:39'),
(9, 7, 2, 'SALIDA', 'Venta Web Directa #PED-20260929-E3578', 2, 0.00, 3.20, 59, 57, 8, 'pedido', '2026-09-29 02:04:06'),
(10, 8, 2, 'SALIDA', 'Venta Web Directa #PED-20260929-E3578', 1, 0.00, 5.40, 70, 69, 8, 'pedido', '2026-09-29 02:04:06'),
(11, 7, 2, 'SALIDA', 'Venta Web Directa #PED-20260929-26F5B', 2, 0.00, 3.20, 57, 55, 9, 'pedido', '2026-09-29 03:06:37'),
(12, 9, 2, 'SALIDA', 'Venta Web Directa #PED-20260929-CA5A8', 1, 0.00, 4.50, 40, 39, 10, 'pedido', '2026-09-29 03:33:40'),
(13, 7, 2, 'SALIDA', 'Venta Web Directa #PED-20260929-E96CC', 1, 0.00, 3.20, 55, 54, 11, 'pedido', '2026-09-29 03:40:57'),
(14, 7, 2, 'SALIDA', 'Venta Web Directa #PED-20260929-AD32D', 1, 0.00, 3.20, 54, 53, 12, 'pedido', '2026-09-29 19:49:47'),
(15, 2, 1, 'SALIDA', 'Venta POS POS-20261001-024916-22', 5, 7.50, 9.50, 50, 45, 13, 'pedido', '2026-10-01 02:49:16'),
(16, 2, 3, 'SALIDA', 'Venta POS POS-20261001-034929-80', 1, 7.50, 9.50, 45, 44, 14, 'pedido', '2026-10-01 03:49:29'),
(17, 31, 3, 'SALIDA', 'Venta POS POS-20261003-101259-42', 1, 0.00, 15000.00, 10, 9, 17, 'pedido', '2026-10-03 10:12:59'),
(18, 7, 2, 'SALIDA', 'Venta Web Directa #PED-20261010-0B47C', 1, 0.00, 3.20, 53, 52, 18, 'pedido', '2026-10-10 11:11:04'),
(19, 3, 1, 'ENTRADA', 'Compra Proveedor: Bebidas y Lácteos del Perú E.I.R.L. (F000-01)', 6, 3.20, 4.20, 120, 126, 1, 'reposicion', '2026-10-10 11:38:12'),
(20, 1, 1, 'SALIDA', 'Venta Web Directa #PED-20261016-F099C', 1, 3.80, 4.80, 78, 77, 19, 'pedido', '2026-10-15 19:53:16'),
(21, 7, 2, 'SALIDA', 'Venta Web Directa #PED-20261015-AB594', 3, 0.00, 3.20, 52, 49, 20, 'pedido', '2026-10-15 21:32:24'),
(22, 9, 2, 'SALIDA', 'Venta Web Directa #PED-20261015-2139E', 1, 0.00, 4.50, 39, 38, 21, 'pedido', '2026-10-15 21:32:52'),
(23, 8, 2, 'SALIDA', 'Venta Web Directa #PED-20261015-D84BD', 1, 0.00, 5.40, 69, 68, 22, 'pedido', '2026-10-15 21:34:52'),
(24, 9, 2, 'SALIDA', 'Venta Web Directa #PED-20261015-D84BD', 1, 0.00, 4.50, 38, 37, 22, 'pedido', '2026-10-15 21:34:52'),
(25, 8, 2, 'SALIDA', 'Venta Web Directa #PED-20261015-2ADB4', 1, 0.00, 5.40, 68, 67, 23, 'pedido', '2026-10-15 21:37:06'),
(26, 1, 2, 'SALIDA', 'Venta Web Directa #PED-20261016-00A52', 2, 3.80, 4.80, 78, 76, 24, 'pedido', '2026-10-15 22:30:39'),
(27, 1, 2, 'SALIDA', 'Venta Web Directa #PED-20261016-E9FA1', 2, 3.80, 4.80, 76, 74, 25, 'pedido', '2026-10-15 22:31:23'),
(28, 20, 2, 'SALIDA', 'Venta Web Directa #PED-20261016-9BE67', 1, 0.00, 3.40, 90, 89, 26, 'pedido', '2026-10-16 01:38:28'),
(29, 8, 2, 'SALIDA', 'Venta Web Directa #PED-20261016-9BE67', 2, 0.00, 5.40, 67, 65, 26, 'pedido', '2026-10-16 01:38:28'),
(30, 9, 2, 'SALIDA', 'Venta Web Directa #PED-20261016-2F805', 1, 0.00, 4.50, 37, 36, 27, 'pedido', '2026-10-16 01:49:29'),
(31, 7, 2, 'SALIDA', 'Venta Web Directa #PED-20261016-D34BF', 1, 0.00, 3.20, 49, 48, 28, 'pedido', '2026-10-16 13:54:23'),
(32, 18, 2, 'SALIDA', 'Venta Web Directa #PED-20261016-5B53A', 1, 0.00, 18.50, 55, 54, 29, 'pedido', '2026-10-16 14:15:07'),
(33, 9, 2, 'SALIDA', 'Venta Web Directa #PED-20261016-0F290', 1, 0.00, 4.50, 36, 35, 30, 'pedido', '2026-10-16 14:48:19');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `metodo_pago`
--

CREATE TABLE `metodo_pago` (
  `id_metodo` int(11) NOT NULL,
  `nombre` varchar(50) NOT NULL,
  `descripcion` varchar(150) DEFAULT NULL,
  `codigo_qr` varchar(255) DEFAULT NULL,
  `numero_cuenta` varchar(50) DEFAULT NULL,
  `titular` varchar(100) DEFAULT NULL,
  `requiere_comprobante` tinyint(1) NOT NULL DEFAULT 0,
  `activo` tinyint(1) NOT NULL DEFAULT 1
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Volcado de datos para la tabla `metodo_pago`
--

INSERT INTO `metodo_pago` (`id_metodo`, `nombre`, `descripcion`, `codigo_qr`, `numero_cuenta`, `titular`, `requiere_comprobante`, `activo`) VALUES
(1, 'Pago en efectivo al recibir', '', NULL, NULL, NULL, 0, 1),
(2, 'Yape', '', 'uploads/qr/qr_yape.svg', '999-111-222', 'La Caserita S.A.C.', 0, 1),
(3, 'Plin', 'Transferencia interbancaria móvil Plin (BBVA/Interbank/Scotiabank)', 'uploads/qr/qr_plin.svg', '999-111-222', 'La Caserita S.A.C.', 0, 0),
(4, 'Tarjeta de crédito / débito', '', NULL, NULL, NULL, 0, 1),
(5, 'Transferencia Bancaria', 'Depósito directo a cuenta corriente', NULL, '191-98765432-0-99', 'La Caserita S.A.C.', 1, 0);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `momento_consumo`
--

CREATE TABLE `momento_consumo` (
  `id_momento` int(11) NOT NULL,
  `nombre` varchar(100) NOT NULL,
  `descripcion` text DEFAULT NULL,
  `imagen` varchar(255) DEFAULT NULL,
  `activo` tinyint(1) NOT NULL DEFAULT 1
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Volcado de datos para la tabla `momento_consumo`
--

INSERT INTO `momento_consumo` (`id_momento`, `nombre`, `descripcion`, `imagen`, `activo`) VALUES
(1, 'Frescos y Proteínas', 'Frutas, verduras, carnes, aves, pescados y mariscos', 'https://images.unsplash.com/photo-1610832958506-aa56368176cf?w=600&auto=format&fit=crop&q=80', 1),
(2, 'Lácteos y Huevos', 'Leches, yogures, quesos, embutidos, huevos y panadería', 'https://images.unsplash.com/photo-1528750997573-59b89d56f4f7?w=600&auto=format&fit=crop&q=80', 1),
(3, 'Abarrotes y Despensa', 'Arroz, menestras, aceites, condimentos, pastas y conservas', 'https://images.unsplash.com/photo-1588964895597-cfccd6e2dbf9?w=600&auto=format&fit=crop&q=80', 1),
(4, 'Bebidas y Snacks', 'Gaseosas, aguas, jugos, galletas, chocolates y piqueos', 'https://images.unsplash.com/photo-1527661591475-527312dd65f5?w=600&auto=format&fit=crop&q=80', 1),
(5, 'Otros', 'Limpieza del hogar, cuidado personal y mascotas', 'https://images.unsplash.com/photo-1584308666744-24d5c474f2ae?w=600&auto=format&fit=crop&q=80', 1);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `movimiento_caja`
--

CREATE TABLE `movimiento_caja` (
  `id_movimiento` int(11) NOT NULL,
  `id_sesion` int(11) NOT NULL,
  `id_usuario` int(11) NOT NULL,
  `tipo` enum('INGRESO','EGRESO') NOT NULL,
  `categoria` enum('insumos','servicios','proveedor','adelanto','fondo_extra','otro') NOT NULL DEFAULT 'otro',
  `monto` decimal(10,2) NOT NULL,
  `motivo` varchar(255) NOT NULL,
  `comprobante_ref` varchar(100) DEFAULT NULL,
  `fecha` datetime NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Volcado de datos para la tabla `movimiento_caja`
--

INSERT INTO `movimiento_caja` (`id_movimiento`, `id_sesion`, `id_usuario`, `tipo`, `categoria`, `monto`, `motivo`, `comprobante_ref`, `fecha`) VALUES
(1, 7, 1, 'INGRESO', 'otro', 10.00, 'Propina', '', '2026-09-30 22:59:06');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `pago_verificacion`
--

CREATE TABLE `pago_verificacion` (
  `id_verificacion` int(11) NOT NULL,
  `id_pedido` int(11) NOT NULL,
  `id_metodo` int(11) NOT NULL,
  `codigo_operacion` varchar(50) NOT NULL,
  `celular_remitente` varchar(20) NOT NULL,
  `titular_origen` varchar(100) DEFAULT NULL,
  `captura_voucher` varchar(255) NOT NULL,
  `monto_reportado` decimal(10,2) NOT NULL,
  `estado_verificacion` enum('Pendiente','Aprobado','Rechazado') NOT NULL DEFAULT 'Pendiente',
  `id_usuario_verificador` int(11) DEFAULT NULL,
  `motivo_rechazo` varchar(255) DEFAULT NULL,
  `fecha_envio` datetime NOT NULL DEFAULT current_timestamp(),
  `fecha_verificacion` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Volcado de datos para la tabla `pago_verificacion`
--

INSERT INTO `pago_verificacion` (`id_verificacion`, `id_pedido`, `id_metodo`, `codigo_operacion`, `celular_remitente`, `titular_origen`, `captura_voucher`, `monto_reportado`, `estado_verificacion`, `id_usuario_verificador`, `motivo_rechazo`, `fecha_envio`, `fecha_verificacion`) VALUES
(1, 19, 2, '888999', '987654321', 'Cliente Yape', 'PAGO_DIRECTO_YAPE', 4.80, 'Aprobado', NULL, NULL, '2026-10-15 19:53:16', '2026-10-15 19:53:16'),
(2, 20, 2, '123544', '942337947', 'Cliente Yape', 'PAGO_DIRECTO_YAPE', 16.60, 'Aprobado', NULL, NULL, '2026-10-15 21:32:24', '2026-10-15 21:32:24'),
(3, 22, 4, 'TC-6176', '', 'APRO', 'PAGO_DIRECTO_TARJETA', 9.90, 'Aprobado', NULL, NULL, '2026-10-15 21:34:52', '2026-10-15 21:34:52'),
(4, 23, 4, 'TC-6176', '', 'FUND', 'PAGO_DIRECTO_TARJETA', 12.40, 'Aprobado', NULL, NULL, '2026-10-15 21:37:06', '2026-10-15 21:37:06'),
(5, 24, 4, 'TC-4242', '', 'APRO', 'PAGO_DIRECTO_TARJETA', 9.60, 'Aprobado', NULL, NULL, '2026-10-15 22:30:39', '2026-10-15 22:30:39'),
(7, 26, 4, 'TC-5306', '', 'APRO', 'PAGO_DIRECTO_TARJETA', 21.20, 'Aprobado', NULL, NULL, '2026-10-16 01:38:28', '2026-10-16 01:38:28'),
(8, 28, 2, '123544', '942337947', 'Cliente Yape', 'PAGO_DIRECTO_YAPE', 10.20, 'Aprobado', NULL, NULL, '2026-10-16 13:54:23', '2026-10-16 13:54:23'),
(9, 29, 2, '123231', '945666665', 'Cliente Yape', 'PAGO_DIRECTO_YAPE', 25.50, 'Aprobado', NULL, NULL, '2026-10-16 14:15:07', '2026-10-16 14:15:07'),
(10, 30, 2, '224563', '978985522', 'Cliente Yape', 'PAGO_DIRECTO_YAPE', 11.50, 'Aprobado', NULL, NULL, '2026-10-16 14:48:19', '2026-10-16 14:48:19');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `pedido`
--

CREATE TABLE `pedido` (
  `id_pedido` int(11) NOT NULL,
  `codigo_pedido` varchar(30) NOT NULL,
  `id_usuario` int(11) NOT NULL,
  `cliente_dni` varchar(15) DEFAULT '00000000',
  `cliente_nombre` varchar(150) DEFAULT 'Público General',
  `id_cajero` int(11) DEFAULT NULL,
  `id_sesion_caja` int(11) DEFAULT NULL,
  `id_metodo` int(11) DEFAULT NULL,
  `id_direccion` int(11) DEFAULT NULL,
  `canal` enum('POS','WEB') NOT NULL DEFAULT 'POS',
  `tipo_entrega` enum('tienda','delivery') NOT NULL DEFAULT 'tienda',
  `horario_entrega` varchar(60) DEFAULT 'Lo antes posible (30-45 min)',
  `fecha_programada` datetime DEFAULT NULL,
  `subtotal` decimal(10,2) NOT NULL,
  `costo_envio` decimal(10,2) NOT NULL DEFAULT 0.00,
  `descuento` decimal(10,2) NOT NULL DEFAULT 0.00,
  `total` decimal(10,2) NOT NULL,
  `monto_recibido` decimal(10,2) DEFAULT NULL,
  `vuelto` decimal(10,2) DEFAULT NULL,
  `estado` varchar(50) NOT NULL DEFAULT 'Pendiente',
  `leido_cliente` tinyint(1) NOT NULL DEFAULT 0,
  `metodo_pago` varchar(50) DEFAULT NULL,
  `pasarela_id` varchar(50) DEFAULT NULL,
  `pasarela_preference_id` varchar(150) DEFAULT NULL,
  `pasarela_payment_id` varchar(100) DEFAULT NULL,
  `pasarela_status` varchar(50) DEFAULT NULL,
  `pasarela_payment_type` varchar(50) DEFAULT NULL,
  `direccion_envio` text DEFAULT NULL,
  `observacion` text DEFAULT NULL,
  `fecha` datetime NOT NULL DEFAULT current_timestamp(),
  `fecha_pedido` datetime NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Volcado de datos para la tabla `pedido`
--

INSERT INTO `pedido` (`id_pedido`, `codigo_pedido`, `id_usuario`, `cliente_dni`, `cliente_nombre`, `id_cajero`, `id_sesion_caja`, `id_metodo`, `id_direccion`, `canal`, `tipo_entrega`, `horario_entrega`, `fecha_programada`, `subtotal`, `costo_envio`, `descuento`, `total`, `monto_recibido`, `vuelto`, `estado`, `leido_cliente`, `metodo_pago`, `pasarela_id`, `pasarela_preference_id`, `pasarela_payment_id`, `pasarela_status`, `pasarela_payment_type`, `direccion_envio`, `observacion`, `fecha`, `fecha_pedido`) VALUES
(1, 'POS-20260927-061543-24', 1, '46892314', 'Juan Pérez', 1, 2, NULL, NULL, 'POS', 'tienda', 'Lo antes posible (30-45 min)', NULL, 4.80, 0.00, 0.00, 4.80, 50.00, 45.20, 'Entregado', 0, 'efectivo', NULL, NULL, NULL, NULL, NULL, 'Venta Presencial - Mostrador POS', NULL, '2026-09-26 23:15:43', '2026-09-26 23:15:43'),
(6, 'PED-20260929-123F1', 2, '00000000', 'Público General', NULL, NULL, 1, 8, 'WEB', 'delivery', 'Programado para 29/09/2026 09:06', '2026-09-29 09:06:19', 4.80, 7.00, 0.00, 11.80, NULL, NULL, 'Cancelado', 0, 'Efectivo', NULL, NULL, NULL, NULL, NULL, 'Av. José Balta, #456, Lote Lote 12, La Victoria, Chiclayo', 'Timbre malogrado, llamar | Horario: Programado para 29/09/2026 09:06 | Programado: 2026-09-29 09:06:19', '2026-09-29 00:06:19', '2026-09-29 00:06:19'),
(7, 'PED-20260929-65E3C', 2, '00000000', 'Público General', NULL, NULL, 1, NULL, 'WEB', 'tienda', 'Lo antes posible (30-45 min)', NULL, 7.40, 0.00, 0.00, 7.40, NULL, NULL, 'Entregado', 0, 'Pago en efectivo al recibir', NULL, NULL, NULL, NULL, NULL, 'Recojo en tienda', '', '2026-09-29 01:49:39', '2026-09-29 01:49:39'),
(8, 'PED-20260929-E3578', 2, '00000000', 'Público General', NULL, NULL, 1, 8, 'WEB', 'delivery', 'Lo antes posible (30-45 min)', NULL, 11.80, 7.00, 0.00, 18.80, NULL, NULL, 'Pendiente', 0, 'Pago en efectivo al recibir', NULL, NULL, NULL, NULL, NULL, 'Av. José Balta, #456, Lote Lote 12, La Victoria, Chiclayo', ' | Horario: Lo antes posible (30-45 min)\r\nTest nota\r\nPrueba entrega', '2026-09-29 02:04:06', '2026-09-29 02:04:06'),
(9, 'PED-20260929-26F5B', 2, '00000000', 'Público General', NULL, NULL, 1, NULL, 'WEB', 'tienda', 'Lo antes posible (30-45 min)', NULL, 6.40, 0.00, 0.00, 6.40, NULL, NULL, 'Entregado', 0, 'Pago en efectivo al recibir', NULL, NULL, NULL, NULL, NULL, 'Recojo en tienda', '', '2026-09-29 03:06:37', '2026-09-29 03:06:37'),
(10, 'PED-20260929-CA5A8', 2, '00000000', 'Público General', NULL, NULL, 1, NULL, 'WEB', 'tienda', 'Lo antes posible (30-45 min)', NULL, 4.50, 0.00, 0.00, 4.50, NULL, NULL, 'Pendiente', 0, 'Pago en efectivo al recibir', NULL, NULL, NULL, NULL, NULL, 'Recojo en tienda', '', '2026-09-29 03:33:40', '2026-09-29 03:33:40'),
(11, 'PED-20260929-E96CC', 2, '00000000', 'Público General', NULL, NULL, 1, NULL, 'WEB', 'tienda', 'Lo antes posible (30-45 min)', NULL, 3.20, 0.00, 0.00, 3.20, NULL, NULL, 'Pendiente', 0, 'Pago en efectivo al recibir', NULL, NULL, NULL, NULL, NULL, 'Recojo en tienda', '', '2026-09-29 03:40:57', '2026-09-29 03:40:57'),
(12, 'PED-20260929-AD32D', 2, '00000000', 'Público General', NULL, NULL, 1, NULL, 'WEB', 'tienda', 'Lo antes posible (30-45 min)', NULL, 3.20, 0.00, 0.00, 3.20, NULL, NULL, 'Pendiente', 0, 'Pago en efectivo al recibir', NULL, NULL, NULL, NULL, NULL, 'Recojo en tienda', '', '2026-09-29 19:49:47', '2026-09-29 19:49:47'),
(13, 'POS-20261001-024916-22', 1, '00000000', 'Público General', 1, 10, NULL, NULL, 'POS', 'tienda', 'Lo antes posible (30-45 min)', NULL, 47.50, 0.00, 0.00, 47.50, 50.00, 2.50, 'Entregado', 0, 'efectivo', NULL, NULL, NULL, NULL, NULL, 'Venta Presencial - Mostrador POS', NULL, '2026-10-01 02:49:16', '2026-10-01 02:49:16'),
(14, 'POS-20261001-034929-80', 3, '00000000', 'Público General', 3, 11, NULL, NULL, 'POS', 'tienda', 'Lo antes posible (30-45 min)', NULL, 9.50, 0.00, 0.00, 9.50, 9.50, 0.00, 'Entregado', 0, 'efectivo', NULL, NULL, NULL, NULL, NULL, 'Venta Presencial - Mostrador POS', NULL, '2026-10-01 03:49:29', '2026-10-01 03:49:29'),
(17, 'POS-20261003-101259-42', 3, '00000000', 'Público General', 3, 11, NULL, NULL, 'POS', 'tienda', 'Lo antes posible (30-45 min)', NULL, 15000.00, 0.00, 0.00, 15000.00, 15000.00, 0.00, 'Entregado', 0, 'tarjeta', NULL, NULL, NULL, NULL, NULL, 'Venta Presencial - Mostrador POS', NULL, '2026-10-03 10:12:59', '2026-10-03 10:12:59'),
(18, 'PED-20261010-0B47C', 2, '00000000', 'Público General', NULL, NULL, 1, 8, 'WEB', 'delivery', 'Lo antes posible (30-45 min)', NULL, 3.20, 7.00, 0.00, 10.20, NULL, NULL, 'En Preparacion', 1, 'Pago en efectivo al recibir', NULL, NULL, NULL, NULL, NULL, 'Av. José Balta, #456, Lote Lote 12, La Victoria, Chiclayo', '', '2026-10-10 11:11:04', '2026-10-10 11:11:04'),
(19, 'PED-20261016-F099C', 1, '00000000', 'Público General', NULL, NULL, 2, NULL, 'WEB', 'tienda', 'Inmediato', NULL, 4.80, 0.00, 0.00, 4.80, NULL, NULL, 'En Preparacion', 0, 'Yape / Plin', 'mercadopago_yape', NULL, '888999', 'approved', 'Yape', 'Recojo en tienda', 'Prueba orden directa sin confirmacion Yape', '2026-10-15 19:53:16', '2026-10-15 19:53:16'),
(20, 'PED-20261015-AB594', 2, '00000000', 'Público General', NULL, NULL, 2, 8, 'WEB', 'delivery', 'Lo antes posible (30-45 min)', NULL, 9.60, 7.00, 0.00, 16.60, NULL, NULL, 'En Camino', 1, 'Yape', 'mercadopago_yape', NULL, '123544', 'approved', 'Yape', 'Av. José Balta, #456, Lote Lote 12, La Victoria, Chiclayo', '[Pago Yape: Cel. 942337947 - Cod. 123544] | Horario: Lo antes posible (30-45 min)', '2026-10-15 21:32:24', '2026-10-15 21:32:24'),
(21, 'PED-20261015-2139E', 2, '00000000', 'Público General', NULL, NULL, 1, 8, 'WEB', 'delivery', 'Lo antes posible (30-45 min)', NULL, 4.50, 7.00, 0.00, 11.50, NULL, NULL, 'En Preparacion', 1, 'Pago en efectivo al recibir', NULL, NULL, NULL, NULL, NULL, 'Av. José Balta, #456, Lote Lote 12, La Victoria, Chiclayo', '', '2026-10-15 21:32:52', '2026-10-15 21:32:52'),
(22, 'PED-20261015-D84BD', 2, '00000000', 'Público General', NULL, NULL, 4, NULL, 'WEB', 'tienda', 'Lo antes posible (30-45 min)', NULL, 9.90, 0.00, 0.00, 9.90, NULL, NULL, 'En Preparacion', 1, 'Tarjeta de crédito / débito', 'mercadopago_tarjeta', NULL, 'TC-6176', 'approved', 'Tarjeta', 'Recojo en tienda', '[Tarjeta •••• 6176 - Titular: APRO]', '2026-10-15 21:34:52', '2026-10-15 21:34:52'),
(23, 'PED-20261015-2ADB4', 2, '00000000', 'Público General', NULL, NULL, 4, 8, 'WEB', 'delivery', 'Lo antes posible (30-45 min)', NULL, 5.40, 7.00, 0.00, 12.40, NULL, NULL, 'En Preparacion', 1, 'Tarjeta de crédito / débito', 'mercadopago_tarjeta', NULL, 'TC-6176', 'approved', 'Tarjeta', 'Av. José Balta, #456, Lote Lote 12, La Victoria, Chiclayo', '[Tarjeta •••• 6176 - Titular: FUND]', '2026-10-15 21:37:06', '2026-10-15 21:37:06'),
(24, 'PED-20261016-00A52', 2, '00000000', 'Público General', NULL, NULL, 4, NULL, 'WEB', 'tienda', 'Lo antes posible (30-45 min)', NULL, 9.60, 0.00, 0.00, 9.60, NULL, NULL, 'En Preparacion', 1, 'Tarjeta de crédito / débito', 'mercadopago_tarjeta', NULL, 'TC-4242', 'approved', 'Tarjeta', 'Recojo en tienda', '[Tarjeta •••• 4242 - Titular: APRO]', '2026-10-15 22:30:39', '2026-10-15 22:30:39'),
(26, 'PED-20261016-9BE67', 2, '00000000', 'Público General', NULL, NULL, 4, 5, 'WEB', 'delivery', 'Lo antes posible (30-45 min)', NULL, 14.20, 7.00, 0.00, 21.20, NULL, NULL, 'En Preparacion', 1, 'Tarjeta de crédito / débito', 'mercadopago_tarjeta', NULL, 'TC-5306', 'approved', 'Tarjeta', 'Av. José Balta, #456, Lote Lote 12, La Victoria, Chiclayo', '[Tarjeta •••• 5306 - Titular: APRO]', '2026-10-16 01:38:28', '2026-10-16 01:38:28'),
(27, 'PED-20261016-2F805', 2, '00000000', 'Público General', NULL, NULL, 1, NULL, 'WEB', 'tienda', 'Lo antes posible (30-45 min)', NULL, 4.50, 0.00, 0.00, 4.50, NULL, NULL, 'En Preparacion', 1, 'Pago en efectivo al recibir', NULL, NULL, NULL, NULL, NULL, 'Recojo en tienda', '', '2026-10-16 01:49:29', '2026-10-16 01:49:29'),
(28, 'PED-20261016-D34BF', 2, '00000000', 'Público General', NULL, NULL, 2, 8, 'WEB', 'delivery', 'Lo antes posible (30-45 min)', NULL, 3.20, 7.00, 0.00, 10.20, NULL, NULL, 'En Preparacion', 1, 'Yape', 'mercadopago_yape', NULL, '123544', 'approved', 'Yape', 'Av. José Balta, #456, Lote Lote 12, La Victoria, Chiclayo', '[Pago Yape: Cel. 942337947 - Cod. 123544]', '2026-10-16 13:54:23', '2026-10-16 13:54:23'),
(29, 'PED-20261016-5B53A', 2, '00000000', 'Público General', NULL, NULL, 2, 6, 'WEB', 'delivery', 'Lo antes posible (30-45 min)', NULL, 18.50, 7.00, 0.00, 25.50, NULL, NULL, 'En Preparacion', 1, 'Yape', 'mercadopago_yape', NULL, '123231', 'approved', 'Yape', 'Av. José Balta, #456, Lote Lote 12, La Victoria, Chiclayo', '', '2026-10-16 14:15:07', '2026-10-16 14:15:07'),
(30, 'PED-20261016-0F290', 2, '00000000', 'Público General', NULL, NULL, 2, 6, 'WEB', 'delivery', 'Lo antes posible (30-45 min)', NULL, 4.50, 7.00, 0.00, 11.50, NULL, NULL, 'En Preparacion', 1, 'Yape', 'mercadopago_yape', NULL, '224563', 'approved', 'Yape', 'Av. José Balta, #456, Lote Lote 12, La Victoria, Chiclayo', '', '2026-10-16 14:48:19', '2026-10-16 14:48:19');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `pos_escaner_cola`
--

CREATE TABLE `pos_escaner_cola` (
  `id_escaneo` int(11) NOT NULL,
  `token_sesion` varchar(64) NOT NULL,
  `codigo_escaneado` varchar(150) NOT NULL,
  `nombre_producto` varchar(150) DEFAULT NULL,
  `id_producto` int(11) DEFAULT NULL,
  `cantidad` int(11) NOT NULL DEFAULT 1,
  `procesado` tinyint(1) NOT NULL DEFAULT 0,
  `dispositivo` varchar(100) DEFAULT 'Móvil Escáner',
  `fecha` datetime NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Volcado de datos para la tabla `pos_escaner_cola`
--

INSERT INTO `pos_escaner_cola` (`id_escaneo`, `token_sesion`, `codigo_escaneado`, `nombre_producto`, `id_producto`, `cantidad`, `procesado`, `dispositivo`, `fecha`) VALUES
(1, '818514dad38b630250fc941ea84ef32f', 'PROD-0001', 'Arroz Superior Costeño 1kg', 1, 1, 1, 'Celular Lector (Cable USB)', '2026-09-30 11:00:02'),
(2, '818514dad38b630250fc941ea84ef32f', '7750123000018', 'Arroz Superior Costeño 1kg', 1, 1, 1, 'Celular Lector (Cable USB)', '2026-09-30 11:43:43'),
(3, '818514dad38b630250fc941ea84ef32f', '7750123000018', 'Arroz Superior Costeño 1kg', 1, 1, 1, 'Celular Lector (Cable USB)', '2026-09-30 12:16:23'),
(4, '818514dad38b630250fc941ea84ef32f', '7750106002486', 'Producto no registrado', NULL, 1, 1, 'Celular Lector (Cable USB)', '2026-09-30 13:09:04'),
(5, '818514dad38b630250fc941ea84ef32f', '7750106002486', 'Producto no registrado', NULL, 1, 1, 'Celular Lector (Cable USB)', '2026-09-30 13:09:06'),
(6, '818514dad38b630250fc941ea84ef32f', '7750106002486', 'Producto no registrado', NULL, 1, 1, 'Celular Lector (Cable USB)', '2026-09-30 13:09:07'),
(7, '818514dad38b630250fc941ea84ef32f', '7750106002486', 'Producto no registrado', NULL, 1, 1, 'Celular Lector (Cable USB)', '2026-09-30 13:09:08'),
(8, '818514dad38b630250fc941ea84ef32f', '7750106002486', 'Producto no registrado', NULL, 1, 1, 'Celular Lector (Cable USB)', '2026-09-30 13:09:10'),
(9, '818514dad38b630250fc941ea84ef32f', '7750885022309', 'Producto no registrado', NULL, 1, 1, 'Celular Lector (Cable USB)', '2026-09-30 13:09:16'),
(10, '818514dad38b630250fc941ea84ef32f', '7750885022309', 'Producto no registrado', NULL, 1, 1, 'Celular Lector (Cable USB)', '2026-09-30 13:09:17'),
(11, '818514dad38b630250fc941ea84ef32f', '7750885022309', 'Producto no registrado', NULL, 1, 1, 'Celular Lector (Cable USB)', '2026-09-30 13:09:41'),
(12, '818514dad38b630250fc941ea84ef32f', '7750885022309', 'Producto no registrado', NULL, 1, 1, 'Celular Lector (Cable USB)', '2026-09-30 13:09:42'),
(13, '818514dad38b630250fc941ea84ef32f', '7750885022309', 'Producto no registrado', NULL, 1, 1, 'Celular Lector (Cable USB)', '2026-09-30 13:09:43'),
(14, '818514dad38b630250fc941ea84ef32f', '7750885022309', 'Producto no registrado', NULL, 1, 1, 'Celular Lector (Cable USB)', '2026-09-30 13:09:44'),
(15, '818514dad38b630250fc941ea84ef32f', '7750885022309', 'Producto no registrado', NULL, 1, 1, 'Celular Lector (Cable USB)', '2026-09-30 13:10:01'),
(16, '818514dad38b630250fc941ea84ef32f', '7750885022309', 'Producto no registrado', NULL, 1, 1, 'Celular Lector (Cable USB)', '2026-09-30 13:10:02'),
(17, '818514dad38b630250fc941ea84ef32f', '7750885022309', 'Producto no registrado', NULL, 1, 1, 'Celular Lector (Cable USB)', '2026-09-30 13:10:04'),
(18, '818514dad38b630250fc941ea84ef32f', '7750885022309', 'Producto no registrado', NULL, 1, 1, 'Celular Lector (Cable USB)', '2026-09-30 13:10:05'),
(19, '818514dad38b630250fc941ea84ef32f', '7750106002486', 'Producto no registrado', NULL, 1, 1, 'Celular Lector (Cable USB)', '2026-09-30 13:10:47'),
(20, '818514dad38b630250fc941ea84ef32f', '7750885022309', 'Producto no registrado', NULL, 1, 1, 'Celular Lector (Cable USB)', '2026-09-30 13:10:57'),
(21, '818514dad38b630250fc941ea84ef32f', '7750885022309', 'Producto no registrado', NULL, 1, 1, 'Celular Lector (Cable USB)', '2026-09-30 13:10:58'),
(22, '818514dad38b630250fc941ea84ef32f', '7750885022309', 'Producto no registrado', NULL, 1, 1, 'Celular Lector (Cable USB)', '2026-09-30 13:11:10'),
(23, '818514dad38b630250fc941ea84ef32f', '7750885022309', 'Producto no registrado', NULL, 1, 1, 'Celular Lector (Cable USB)', '2026-09-30 13:11:11'),
(24, '818514dad38b630250fc941ea84ef32f', '7750885022309', 'Producto no registrado', NULL, 1, 1, 'Celular Lector (Cable USB)', '2026-09-30 13:11:12'),
(25, '818514dad38b630250fc941ea84ef32f', '7750885022309', 'Producto no registrado', NULL, 1, 1, 'Celular Lector (Cable USB)', '2026-09-30 13:11:15'),
(26, '818514dad38b630250fc941ea84ef32f', '7750885022309', 'Producto no registrado', NULL, 1, 1, 'Celular Lector (Cable USB)', '2026-09-30 13:11:17'),
(27, '818514dad38b630250fc941ea84ef32f', '7750106002486', 'Producto no registrado', NULL, 1, 1, 'Celular Lector (Cable USB)', '2026-09-30 13:11:39'),
(28, '818514dad38b630250fc941ea84ef32f', '7750106002486', 'Producto no registrado', NULL, 1, 1, 'Celular Lector (Cable USB)', '2026-09-30 13:11:40'),
(29, '818514dad38b630250fc941ea84ef32f', '7750106002486', 'Producto no registrado', NULL, 1, 1, 'Celular Lector (Cable USB)', '2026-09-30 13:11:47'),
(30, '818514dad38b630250fc941ea84ef32f', '4830566002486', 'Producto no registrado', NULL, 1, 1, 'Celular Lector (Cable USB)', '2026-09-30 13:18:24'),
(31, '818514dad38b630250fc941ea84ef32f', '7750106002486', 'Producto no registrado', NULL, 1, 1, 'Celular Lector (Cable USB)', '2026-09-30 13:18:25'),
(32, '818514dad38b630250fc941ea84ef32f', '7750106002486', 'Aceite de Girasol Primor Botella 1L', 19, 1, 1, 'Celular Lector (Cable USB)', '2026-09-30 13:19:36'),
(33, '818514dad38b630250fc941ea84ef32f', '7750106002486', 'Aceite de Girasol Primor Botella 1L', 19, 1, 1, 'Celular Lector (Cable USB)', '2026-09-30 13:19:38'),
(34, '818514dad38b630250fc941ea84ef32f', '7750106002486', 'Aceite de Girasol Primor Botella 1L', 19, 1, 1, 'Celular Lector (Cable USB)', '2026-09-30 13:19:40'),
(35, '818514dad38b630250fc941ea84ef32f', '7750106002486', 'Aceite de Girasol Primor Botella 1L', 19, 1, 1, 'Celular Lector (Cable USB)', '2026-09-30 13:19:41'),
(36, '818514dad38b630250fc941ea84ef32f', '7750106002486', 'Aceite de Girasol Primor Botella 1L', 19, 1, 1, 'Celular Lector (Cable USB)', '2026-09-30 13:20:15'),
(37, '818514dad38b630250fc941ea84ef32f', '7750106002486', 'Aceite de Girasol Primor Botella 1L', 19, 1, 1, 'Celular Lector (Cable USB)', '2026-09-30 13:20:16'),
(38, '818514dad38b630250fc941ea84ef32f', '7750106002486', 'Aceite de Girasol Primor Botella 1L', 19, 1, 1, 'Celular Lector (Cable USB)', '2026-09-30 13:20:17'),
(39, '818514dad38b630250fc941ea84ef32f', '7750106002486', 'Aceite de Girasol Primor Botella 1L', 19, 1, 1, 'Celular Lector (Cable USB)', '2026-09-30 13:20:19'),
(40, '818514dad38b630250fc941ea84ef32f', '7750106002486', 'Aceite de Girasol Primor Botella 1L', 19, 1, 1, 'Celular Lector (Cable USB)', '2026-09-30 13:20:20'),
(41, '818514dad38b630250fc941ea84ef32f', '7750106002486', 'Aceite de Girasol Primor Botella 1L', 19, 1, 1, 'Celular Lector (Cable USB)', '2026-09-30 13:20:21'),
(42, '818514dad38b630250fc941ea84ef32f', '7750106002486', 'Aceite de Girasol Primor Botella 1L', 19, 1, 1, 'Celular Lector (Cable USB)', '2026-09-30 13:20:22'),
(43, '818514dad38b630250fc941ea84ef32f', '7750106002486', 'Aceite de Girasol Primor Botella 1L', 19, 1, 1, 'Celular Lector (Cable USB)', '2026-09-30 13:22:21'),
(44, '818514dad38b630250fc941ea84ef32f', '7750106002486', 'Aceite de Girasol Primor Botella 1L', 19, 1, 1, 'Celular Lector (Cable USB)', '2026-09-30 13:22:22'),
(45, '818514dad38b630250fc941ea84ef32f', '7750106002486', 'Aceite de Girasol Primor Botella 1L', 19, 1, 1, 'Celular Lector (Cable USB)', '2026-09-30 13:22:23'),
(46, '818514dad38b630250fc941ea84ef32f', '7750106002486', 'Aceite de Girasol Primor Botella 1L', 19, 1, 1, 'Celular Lector (Cable USB)', '2026-09-30 13:22:40'),
(47, '818514dad38b630250fc941ea84ef32f', '7750106002486', 'Aceite de Girasol Primor Botella 1L', 19, 1, 1, 'Celular Lector (Cable USB)', '2026-09-30 13:22:41'),
(48, '818514dad38b630250fc941ea84ef32f', '7750106002486', 'Aceite de Girasol Primor Botella 1L', 19, 1, 1, 'Celular Lector (Cable USB)', '2026-09-30 13:22:43'),
(49, '818514dad38b630250fc941ea84ef32f', '7750106002486', 'Aceite de Girasol Primor Botella 1L', 19, 1, 1, 'Celular Lector (Cable USB)', '2026-09-30 13:22:44'),
(50, '818514dad38b630250fc941ea84ef32f', '7750106002486', 'Aceite de Girasol Primor Botella 1L', 19, 1, 1, 'Celular Lector (Cable USB)', '2026-09-30 13:22:46'),
(51, '818514dad38b630250fc941ea84ef32f', '6756100002966', 'Producto no registrado', NULL, 1, 1, 'Celular Lector (Cable USB)', '2026-09-30 13:23:52'),
(52, '818514dad38b630250fc941ea84ef32f', '7750106002486', 'Aceite de Girasol Primor Botella 1L', 19, 1, 1, 'Celular Lector (Cable USB)', '2026-09-30 13:23:57'),
(53, '818514dad38b630250fc941ea84ef32f', '7850103002486', 'Producto no registrado', NULL, 1, 1, 'Celular Lector (Cable USB)', '2026-09-30 13:24:00'),
(54, '818514dad38b630250fc941ea84ef32f', '4750566000063', 'Producto no registrado', NULL, 1, 1, 'Celular Lector (Cable USB)', '2026-09-30 13:24:05'),
(55, '818514dad38b630250fc941ea84ef32f', '4750266000486', 'Producto no registrado', NULL, 1, 1, 'Celular Lector (Cable USB)', '2026-09-30 13:24:06'),
(56, '818514dad38b630250fc941ea84ef32f', '7750106002448', 'Producto no registrado', NULL, 1, 1, 'Celular Lector (Cable USB)', '2026-09-30 13:24:20'),
(57, '818514dad38b630250fc941ea84ef32f', '9754560002486', 'Producto no registrado', NULL, 1, 1, 'Celular Lector (Cable USB)', '2026-09-30 13:24:23'),
(58, '818514dad38b630250fc941ea84ef32f', '7751271015383', 'Producto no registrado', NULL, 1, 1, 'Celular Lector (Cable USB)', '2026-09-30 14:15:39'),
(59, '818514dad38b630250fc941ea84ef32f', '7750106002486', 'Producto no registrado', NULL, 1, 1, 'Celular Lector (Láser USB)', '2026-09-30 14:27:05'),
(60, '818514dad38b630250fc941ea84ef32f', '7750106002486', 'Producto no registrado', NULL, 1, 1, 'Celular Lector (Láser USB)', '2026-09-30 14:27:07'),
(61, '818514dad38b630250fc941ea84ef32f', '7750106002486', 'Producto no registrado', NULL, 1, 1, 'Celular Lector (Láser USB)', '2026-09-30 14:27:16'),
(62, '818514dad38b630250fc941ea84ef32f', '710400024833', 'Producto no registrado', NULL, 1, 1, 'Celular Lector (Láser USB)', '2026-09-30 14:27:17'),
(63, '818514dad38b630250fc941ea84ef32f', '7750106002486', 'Producto no registrado', NULL, 1, 1, 'Celular Lector (Láser USB)', '2026-09-30 14:27:18'),
(64, '818514dad38b630250fc941ea84ef32f', '7750106002486', 'Producto no registrado', NULL, 1, 1, 'Celular Lector (Láser USB)', '2026-09-30 14:27:20'),
(65, '818514dad38b630250fc941ea84ef32f', '7750106002486', 'Producto no registrado', NULL, 1, 1, 'Celular Lector (Láser USB)', '2026-09-30 14:27:21'),
(66, '818514dad38b630250fc941ea84ef32f', '7750106002486', 'Producto no registrado', NULL, 1, 1, 'Celular Lector (Láser USB)', '2026-09-30 14:27:34'),
(67, '818514dad38b630250fc941ea84ef32f', '7750106002486', 'Producto no registrado', NULL, 1, 1, 'Celular Lector (Láser USB)', '2026-09-30 14:27:35'),
(68, '818514dad38b630250fc941ea84ef32f', '7750106002486', 'Producto no registrado', NULL, 1, 1, 'Celular Lector (Láser USB)', '2026-09-30 14:27:37'),
(69, '818514dad38b630250fc941ea84ef32f', '7750106002486', 'Producto no registrado', NULL, 1, 1, 'Celular Lector (Láser USB)', '2026-09-30 14:27:38'),
(70, '818514dad38b630250fc941ea84ef32f', '7750106002486', 'Producto no registrado', NULL, 1, 1, 'Celular Lector (Láser USB)', '2026-09-30 14:27:40'),
(71, '818514dad38b630250fc941ea84ef32f', '4760566002486', 'Producto no registrado', NULL, 1, 1, 'Celular Lector (Láser USB)', '2026-09-30 14:28:35'),
(72, '818514dad38b630250fc941ea84ef32f', '7750106002486', 'Producto no registrado', NULL, 1, 1, 'Celular Lector (Láser USB)', '2026-09-30 14:28:37'),
(73, 'ab733b037e15a46ec3d0cfe9889c50f4', '7750885022309', 'Producto no registrado', NULL, 1, 1, 'Celular Lector (Láser USB)', '2026-10-01 02:10:40'),
(74, 'ab733b037e15a46ec3d0cfe9889c50f4', '7750885022309', 'Producto no registrado', NULL, 1, 1, 'Celular Lector (Láser USB)', '2026-10-01 02:10:41'),
(75, 'ab733b037e15a46ec3d0cfe9889c50f4', '7750885022309', 'Producto no registrado', NULL, 1, 1, 'Celular Lector (Láser USB)', '2026-10-01 02:10:42'),
(76, 'ab733b037e15a46ec3d0cfe9889c50f4', '7750885022309', 'Producto no registrado', NULL, 1, 1, 'Celular Lector (Láser USB)', '2026-10-01 02:12:16'),
(77, 'ab733b037e15a46ec3d0cfe9889c50f4', '7750885022309', 'Producto no registrado', NULL, 1, 1, 'Celular Lector (Láser USB)', '2026-10-01 02:12:28'),
(78, 'ab733b037e15a46ec3d0cfe9889c50f4', '7750885022309', 'Producto no registrado', NULL, 1, 1, 'Celular Lector (Láser USB)', '2026-10-01 02:12:29'),
(79, 'ab733b037e15a46ec3d0cfe9889c50f4', '750285013003', 'Producto no registrado', NULL, 1, 1, 'Celular Lector (Láser USB)', '2026-10-01 02:12:34'),
(80, 'ab733b037e15a46ec3d0cfe9889c50f4', '7750885022309', 'Producto no registrado', NULL, 1, 1, 'Celular Lector (Láser USB)', '2026-10-01 02:12:52'),
(81, 'ab733b037e15a46ec3d0cfe9889c50f4', '7750885024822', 'Producto no registrado', NULL, 1, 1, 'Celular Lector (Láser USB)', '2026-10-01 02:13:28'),
(82, 'ab733b037e15a46ec3d0cfe9889c50f4', '7750885022309', 'Escaneo remoto', NULL, 1, 1, 'Celular Lector (Láser USB)', '2026-10-01 02:44:33'),
(83, 'ab733b037e15a46ec3d0cfe9889c50f4', '7750885022309', 'Escaneo remoto', NULL, 1, 1, 'Celular Lector (Láser USB)', '2026-10-01 02:44:44'),
(84, 'ab733b037e15a46ec3d0cfe9889c50f4', '7750885022309', 'Escaneo remoto', NULL, 1, 1, 'Celular Lector (Láser USB)', '2026-10-01 02:44:46'),
(85, 'ab733b037e15a46ec3d0cfe9889c50f4', '7750885022309', 'Escaneo remoto', NULL, 1, 1, 'Celular Lector (Láser USB)', '2026-10-01 02:44:49'),
(86, 'ab733b037e15a46ec3d0cfe9889c50f4', '7750885022309', 'Escaneo remoto', NULL, 1, 1, 'Celular Lector (Láser USB)', '2026-10-01 02:44:54'),
(87, 'ab733b037e15a46ec3d0cfe9889c50f4', '310085123090', 'Escaneo remoto', NULL, 1, 1, 'Celular Lector (Láser USB)', '2026-10-01 02:44:58'),
(88, 'ab733b037e15a46ec3d0cfe9889c50f4', '7750885022309', 'Escaneo remoto', NULL, 1, 1, 'Celular Lector (Láser USB)', '2026-10-01 02:44:58'),
(89, 'ab733b037e15a46ec3d0cfe9889c50f4', '730983322003', 'Escaneo remoto', NULL, 1, 1, 'Celular Lector (Láser USB)', '2026-10-01 02:45:00'),
(90, 'ab733b037e15a46ec3d0cfe9889c50f4', '7750885022309', 'Escaneo remoto', NULL, 1, 1, 'Celular Lector (Láser USB)', '2026-10-01 02:45:05'),
(91, 'ab733b037e15a46ec3d0cfe9889c50f4', '710985223003', 'Escaneo remoto', NULL, 1, 1, 'Celular Lector (Láser USB)', '2026-10-01 02:45:08'),
(92, 'ab733b037e15a46ec3d0cfe9889c50f4', '7750885022309', 'Escaneo remoto', NULL, 1, 1, 'Celular Lector (Láser USB)', '2026-10-01 02:45:08'),
(93, 'ab733b037e15a46ec3d0cfe9889c50f4', '7750885024822', 'Escaneo remoto', NULL, 1, 1, 'Celular Lector (Láser USB)', '2026-10-01 02:45:35'),
(94, 'ab733b037e15a46ec3d0cfe9889c50f4', '7750885022309', 'Escaneo remoto', NULL, 1, 1, 'Celular Lector (Láser USB)', '2026-10-01 02:45:55'),
(95, 'ab733b037e15a46ec3d0cfe9889c50f4', '7750885022309', 'Escaneo remoto', NULL, 1, 1, 'Celular Lector (Láser USB)', '2026-10-01 02:45:57'),
(96, 'ab733b037e15a46ec3d0cfe9889c50f4', '7750885022613', 'Escaneo remoto', NULL, 1, 1, 'Celular Lector (Láser USB)', '2026-10-01 02:45:58'),
(97, 'ab733b037e15a46ec3d0cfe9889c50f4', '7750885022309', 'Escaneo remoto', NULL, 1, 1, 'Celular Lector (Láser USB)', '2026-10-01 02:45:59'),
(98, 'ab733b037e15a46ec3d0cfe9889c50f4', '7750885022309', 'Escaneo remoto', NULL, 1, 1, 'Celular Lector (Láser USB)', '2026-10-01 02:46:03'),
(99, 'ab733b037e15a46ec3d0cfe9889c50f4', '7750885022309', 'Escaneo remoto', NULL, 1, 1, 'Celular Lector (Láser USB)', '2026-10-01 02:46:20'),
(100, 'ab733b037e15a46ec3d0cfe9889c50f4', '7750885022309', 'Escaneo remoto', NULL, 1, 1, 'Celular Lector (Láser USB)', '2026-10-01 02:46:45'),
(101, 'ab733b037e15a46ec3d0cfe9889c50f4', '7750885022309', 'Escaneo remoto', NULL, 1, 1, 'Celular Lector (Láser USB)', '2026-10-01 02:46:46'),
(102, 'ab733b037e15a46ec3d0cfe9889c50f4', '7750885022309', 'Escaneo remoto', NULL, 1, 1, 'Celular Lector (Láser USB)', '2026-10-01 02:46:49'),
(103, 'ab733b037e15a46ec3d0cfe9889c50f4', '7750885022309', 'Escaneo remoto', NULL, 1, 1, 'Celular Lector (Láser USB)', '2026-10-01 02:48:42'),
(104, 'ab733b037e15a46ec3d0cfe9889c50f4', '7750885012300', 'Escaneo remoto', NULL, 1, 1, 'Celular Lector (Láser USB)', '2026-10-01 02:48:45'),
(105, 'ab733b037e15a46ec3d0cfe9889c50f4', '7750885022309', 'Escaneo remoto', NULL, 1, 1, 'Celular Lector (Láser USB)', '2026-10-01 02:48:46'),
(106, 'ab733b037e15a46ec3d0cfe9889c50f4', '7750885022309', 'Escaneo remoto', NULL, 1, 1, 'Celular Lector (Láser USB)', '2026-10-01 02:48:51'),
(107, 'ab733b037e15a46ec3d0cfe9889c50f4', '7750885022309', 'Escaneo remoto', NULL, 1, 1, 'Celular Lector (Láser USB)', '2026-10-01 02:48:59'),
(108, 'ab733b037e15a46ec3d0cfe9889c50f4', '7750885022309', 'Escaneo remoto', NULL, 1, 1, 'Celular Lector (Láser USB)', '2026-10-01 02:49:02'),
(109, 'ab733b037e15a46ec3d0cfe9889c50f4', '7750885022309', 'Escaneo remoto', NULL, 1, 1, 'Celular Lector (Láser USB)', '2026-10-01 02:54:18'),
(110, 'ab733b037e15a46ec3d0cfe9889c50f4', '7750885023443', 'Escaneo remoto', NULL, 1, 1, 'Celular Lector (Láser USB)', '2026-10-01 02:54:19'),
(111, 'ab733b037e15a46ec3d0cfe9889c50f4', '7750885022309', 'Escaneo remoto', NULL, 1, 1, 'Celular Lector (Láser USB)', '2026-10-01 02:54:19'),
(112, 'ab733b037e15a46ec3d0cfe9889c50f4', '7750885022309', 'Escaneo remoto', NULL, 1, 1, 'Celular Lector (Láser USB)', '2026-10-01 02:54:30'),
(113, 'ab733b037e15a46ec3d0cfe9889c50f4', '7750885024822', 'Escaneo remoto', NULL, 1, 1, 'Celular Lector (Láser USB)', '2026-10-01 02:55:23'),
(114, 'ab733b037e15a46ec3d0cfe9889c50f4', '7750885022309', 'Escaneo remoto', NULL, 1, 1, 'Celular Lector (Láser USB)', '2026-10-01 03:04:58'),
(115, 'ab733b037e15a46ec3d0cfe9889c50f4', '7750885022309', 'Escaneo remoto', NULL, 1, 1, 'Celular Lector (Láser USB)', '2026-10-01 03:04:59'),
(116, 'ab733b037e15a46ec3d0cfe9889c50f4', '7750885022309', 'Escaneo remoto', NULL, 1, 1, 'Celular Lector (Láser USB)', '2026-10-01 03:05:10'),
(117, '6944d65b62c9d3673f26207cb2610b55', '7750885022309', 'Escaneo remoto', NULL, 1, 1, 'Celular Lector (Láser USB)', '2026-10-01 03:48:39'),
(118, '6944d65b62c9d3673f26207cb2610b55', '7750885022309', 'Escaneo remoto', NULL, 1, 1, 'Celular Lector (Láser USB)', '2026-10-01 13:21:50'),
(119, '6944d65b62c9d3673f26207cb2610b55', '7750885024822', 'Escaneo remoto', NULL, 1, 1, 'Celular Lector (Láser USB)', '2026-10-01 13:22:07'),
(120, '6944d65b62c9d3673f26207cb2610b55', '7750885024822', 'Escaneo remoto', NULL, 1, 1, 'Celular Lector (Láser USB)', '2026-10-01 13:22:10'),
(121, '6944d65b62c9d3673f26207cb2610b55', '7750885024822', 'Escaneo remoto', NULL, 1, 1, 'Celular Lector (Láser USB)', '2026-10-01 13:22:11'),
(122, '6944d65b62c9d3673f26207cb2610b55', '7750885024822', 'Escaneo remoto', NULL, 1, 1, 'Celular Lector (Láser USB)', '2026-10-01 13:22:23'),
(123, '6944d65b62c9d3673f26207cb2610b55', '710985024822', 'Escaneo remoto', NULL, 1, 1, 'Celular Lector (Láser USB)', '2026-10-01 13:22:24'),
(124, '6944d65b62c9d3673f26207cb2610b55', '7750885024822', 'Escaneo remoto', NULL, 1, 1, 'Celular Lector (Láser USB)', '2026-10-01 13:22:24'),
(125, '6944d65b62c9d3673f26207cb2610b55', '7750885022309', 'Escaneo remoto', NULL, 1, 1, 'Celular Lector (Láser USB)', '2026-10-01 13:23:47'),
(126, '6944d65b62c9d3673f26207cb2610b55', '7750885022309', 'Escaneo remoto', NULL, 1, 1, 'Celular Lector (Láser USB)', '2026-10-01 13:23:55'),
(127, '6944d65b62c9d3673f26207cb2610b55', '7750885022309', 'Escaneo remoto', NULL, 1, 1, 'Celular Lector (Láser USB)', '2026-10-01 13:24:05'),
(128, '6944d65b62c9d3673f26207cb2610b55', '7750885021630', 'Escaneo remoto', NULL, 1, 1, 'Celular Lector (Láser USB)', '2026-10-01 13:24:07'),
(129, '6944d65b62c9d3673f26207cb2610b55', '7750885024822', 'Escaneo remoto', NULL, 1, 1, 'Celular Lector (Láser USB)', '2026-10-01 13:24:09'),
(130, '6944d65b62c9d3673f26207cb2610b55', '854085024822', 'Escaneo remoto', NULL, 1, 1, 'Celular Lector (Láser USB)', '2026-10-01 13:24:11'),
(131, '6944d65b62c9d3673f26207cb2610b55', '7750885022309', 'Escaneo remoto', NULL, 1, 1, 'Celular Lector (Láser USB)', '2026-10-01 13:25:04'),
(132, '6944d65b62c9d3673f26207cb2610b55', '7750885022309', 'Escaneo remoto', NULL, 1, 1, 'Celular Lector (Láser USB)', '2026-10-01 13:25:08'),
(133, '6944d65b62c9d3673f26207cb2610b55', '7750885024822', 'Escaneo remoto', NULL, 1, 1, 'Celular Lector (Láser USB)', '2026-10-01 13:25:11'),
(134, '6944d65b62c9d3673f26207cb2610b55', '7750885024822', 'Escaneo remoto', NULL, 1, 1, 'Celular Lector (Láser USB)', '2026-10-01 13:25:16'),
(135, '6944d65b62c9d3673f26207cb2610b55', '7750885024822', 'Escaneo remoto', NULL, 1, 1, 'Celular Lector (Láser USB)', '2026-10-01 13:25:33'),
(136, '6944d65b62c9d3673f26207cb2610b55', '7750885024822', 'Escaneo remoto', NULL, 1, 1, 'Celular Lector (Láser USB)', '2026-10-01 13:25:34'),
(137, '6944d65b62c9d3673f26207cb2610b55', '710985024822', 'Escaneo remoto', NULL, 1, 1, 'Celular Lector (Láser USB)', '2026-10-01 13:25:37'),
(138, '6944d65b62c9d3673f26207cb2610b55', '7750885022309', 'Escaneo remoto', NULL, 1, 1, 'Celular Lector (Láser USB)', '2026-10-01 13:25:42'),
(139, '6944d65b62c9d3673f26207cb2610b55', '7750885022309', 'Escaneo remoto', NULL, 1, 1, 'Celular Lector (Láser USB)', '2026-10-01 13:25:52'),
(140, '6944d65b62c9d3673f26207cb2610b55', '7750885024822', 'Escaneo remoto', NULL, 1, 1, 'Celular Lector (Láser USB)', '2026-10-01 13:25:55'),
(141, '6944d65b62c9d3673f26207cb2610b55', '7750885024822', 'Escaneo remoto', NULL, 1, 1, 'Celular Lector (Láser USB)', '2026-10-01 13:27:25'),
(142, '6944d65b62c9d3673f26207cb2610b55', '7750885024822', 'Escaneo remoto', NULL, 1, 1, 'Celular Lector (Láser USB)', '2026-10-01 13:27:35'),
(143, 'ab733b037e15a46ec3d0cfe9889c50f4', '7750885024822', 'Escaneo remoto', NULL, 1, 1, 'Celular Lector (Láser USB)', '2026-10-01 13:30:33'),
(144, 'ab733b037e15a46ec3d0cfe9889c50f4', '8750815022309', 'Escaneo remoto', NULL, 1, 1, 'Celular Lector (Láser USB)', '2026-10-01 13:30:39'),
(145, 'ab733b037e15a46ec3d0cfe9889c50f4', '753855922309', 'Escaneo remoto', NULL, 1, 1, 'Celular Lector (Láser USB)', '2026-10-01 13:31:02'),
(146, 'ab733b037e15a46ec3d0cfe9889c50f4', '7750885022309', 'Escaneo remoto', NULL, 1, 1, 'Celular Lector (Láser USB)', '2026-10-01 13:31:30'),
(147, 'ab733b037e15a46ec3d0cfe9889c50f4', '7622201720438', 'Escaneo remoto', NULL, 1, 1, 'Celular Lector (Láser USB)', '2026-10-01 13:33:05'),
(148, 'ab733b037e15a46ec3d0cfe9889c50f4', '7722201720435', 'Escaneo remoto', NULL, 1, 1, 'Celular Lector (Láser USB)', '2026-10-01 13:33:07'),
(149, 'ab733b037e15a46ec3d0cfe9889c50f4', '7622201720438', 'Escaneo remoto', NULL, 1, 1, 'Celular Lector (Láser USB)', '2026-10-01 13:33:29'),
(150, '6944d65b62c9d3673f26207cb2610b55', '7750885022309', 'Escaneo remoto', NULL, 1, 1, 'Celular Lector (Láser USB)', '2026-10-01 13:58:19'),
(151, '6944d65b62c9d3673f26207cb2610b55', '7750885022309', 'Escaneo remoto', NULL, 1, 1, 'Celular Lector (Láser USB)', '2026-10-01 13:58:27'),
(152, '6944d65b62c9d3673f26207cb2610b55', '7750885022309', 'Escaneo remoto', NULL, 1, 1, 'Celular Lector (Láser USB)', '2026-10-01 13:59:12'),
(153, '6944d65b62c9d3673f26207cb2610b55', '7750885022309', 'Escaneo remoto', NULL, 1, 1, 'Celular Lector (Láser USB)', '2026-10-01 13:59:22'),
(154, '6944d65b62c9d3673f26207cb2610b55', '7750885022309', 'Escaneo remoto', NULL, 1, 1, 'Celular Lector (Láser USB)', '2026-10-01 13:59:58'),
(155, '6944d65b62c9d3673f26207cb2610b55', '7750885022309', 'Escaneo remoto', NULL, 1, 1, 'Celular Lector (Láser USB)', '2026-10-01 14:00:00'),
(156, '6944d65b62c9d3673f26207cb2610b55', '17506298', 'Escaneo remoto', NULL, 1, 1, 'Celular Lector (Láser USB)', '2026-10-01 14:00:01'),
(157, '6944d65b62c9d3673f26207cb2610b55', '7750885022309', 'Escaneo remoto', NULL, 1, 1, 'Celular Lector (Láser USB)', '2026-10-01 14:00:02'),
(158, '6944d65b62c9d3673f26207cb2610b55', '7750885022309', 'Escaneo remoto', NULL, 1, 1, 'Celular Lector (Láser USB)', '2026-10-01 14:00:12'),
(159, '6944d65b62c9d3673f26207cb2610b55', '7750885022309', 'Escaneo remoto', NULL, 1, 1, 'Celular Lector (Láser USB)', '2026-10-01 14:00:14'),
(160, '6944d65b62c9d3673f26207cb2610b55', '8750625022605', 'Escaneo remoto', NULL, 1, 1, 'Celular Lector (Láser USB)', '2026-10-01 14:00:16'),
(161, '6944d65b62c9d3673f26207cb2610b55', '7750885022309', 'Escaneo remoto', NULL, 1, 1, 'Celular Lector (Láser USB)', '2026-10-01 14:01:19'),
(162, '6944d65b62c9d3673f26207cb2610b55', '7750885022309', 'Escaneo remoto', NULL, 1, 1, 'Celular Lector (Láser USB)', '2026-10-01 14:01:22'),
(163, '6944d65b62c9d3673f26207cb2610b55', '7750885022309', 'Escaneo remoto', NULL, 1, 1, 'Celular Lector (Láser USB)', '2026-10-01 14:01:52'),
(164, '6944d65b62c9d3673f26207cb2610b55', '7750885022309', 'Escaneo remoto', NULL, 1, 1, 'Celular Lector (Láser USB)', '2026-10-01 14:02:24'),
(165, '6944d65b62c9d3673f26207cb2610b55', '7622201720438', 'Escaneo remoto', NULL, 1, 1, 'Celular Lector (Láser USB)', '2026-10-01 14:05:36'),
(166, '6944d65b62c9d3673f26207cb2610b55', '7750885022309', 'Escaneo remoto', NULL, 1, 1, 'Celular Lector (Láser USB)', '2026-10-01 14:17:29'),
(167, '6944d65b62c9d3673f26207cb2610b55', '7622201720438', 'Escaneo remoto', NULL, 1, 1, 'Celular Lector (Láser USB)', '2026-10-01 14:17:33'),
(168, '6944d65b62c9d3673f26207cb2610b55', '7750885022309', 'Escaneo remoto', NULL, 1, 1, 'Celular Lector (Láser USB)', '2026-10-01 14:18:47'),
(169, '6944d65b62c9d3673f26207cb2610b55', '7750885022309', 'Escaneo remoto', NULL, 1, 1, 'Celular Lector (Láser USB)', '2026-10-01 14:19:26'),
(170, '6944d65b62c9d3673f26207cb2610b55', '7750885022309', 'Escaneo remoto', NULL, 1, 1, 'Celular Lector (Láser USB)', '2026-10-01 14:21:12'),
(171, '6944d65b62c9d3673f26207cb2610b55', '7750885022309', 'Escaneo remoto', NULL, 1, 1, 'Celular Lector (Láser USB)', '2026-10-01 14:28:15'),
(172, '6944d65b62c9d3673f26207cb2610b55', '7750885022309', 'Escaneo remoto', NULL, 1, 1, 'Celular Lector (Láser USB)', '2026-10-01 14:29:20'),
(173, '6944d65b62c9d3673f26207cb2610b55', '7750885022309', 'Escaneo remoto', NULL, 1, 1, 'Celular Lector (Láser USB)', '2026-10-01 14:33:05'),
(174, 'ab733b037e15a46ec3d0cfe9889c50f4', '7750885022309', 'Escaneo remoto', NULL, 1, 1, 'Celular Lector (Láser USB)', '2026-10-01 14:34:00'),
(175, '6944d65b62c9d3673f26207cb2610b55', '7750885022309', 'Escaneo remoto', NULL, 1, 1, 'Celular Lector (Láser USB)', '2026-10-01 14:34:51'),
(176, '6944d65b62c9d3673f26207cb2610b55', '7750885022309', 'Escaneo remoto', NULL, 1, 1, 'Celular Lector (Láser USB)', '2026-10-01 14:34:53'),
(177, '6944d65b62c9d3673f26207cb2610b55', '7750885022309', 'Escaneo remoto', NULL, 1, 1, 'Celular Lector (Láser USB)', '2026-10-01 14:34:58'),
(178, 'ab733b037e15a46ec3d0cfe9889c50f4', '9900000000011', 'Escaneo remoto', NULL, 1, 1, 'Celular Lector (Láser USB)', '2026-10-01 14:35:51'),
(179, 'ab733b037e15a46ec3d0cfe9889c50f4', '9900000000028', 'Escaneo remoto', NULL, 1, 1, 'Celular Lector (Láser USB)', '2026-10-01 14:35:53'),
(180, 'ab733b037e15a46ec3d0cfe9889c50f4', '7750123000018', 'Escaneo remoto', NULL, 1, 1, 'Celular Lector (Láser USB)', '2026-10-01 14:35:54'),
(181, 'ab733b037e15a46ec3d0cfe9889c50f4', '9900000000011', 'Escaneo remoto', NULL, 1, 1, 'Celular Lector (Láser USB)', '2026-10-01 14:36:49'),
(182, 'ab733b037e15a46ec3d0cfe9889c50f4', '9900000000028', 'Escaneo remoto', NULL, 1, 1, 'Celular Lector (Láser USB)', '2026-10-01 14:36:50'),
(183, 'ab733b037e15a46ec3d0cfe9889c50f4', '7750123000018', 'Escaneo remoto', NULL, 1, 1, 'Celular Lector (Láser USB)', '2026-10-01 14:36:51'),
(184, '6944d65b62c9d3673f26207cb2610b55', '9900000000097', 'Escaneo remoto', NULL, 1, 1, 'Celular Lector (Láser USB)', '2026-10-01 14:37:31'),
(185, '6944d65b62c9d3673f26207cb2610b55', '9900000000103', 'Escaneo remoto', NULL, 1, 1, 'Celular Lector (Láser USB)', '2026-10-01 14:39:06'),
(186, '6944d65b62c9d3673f26207cb2610b55', '7750885022309', 'Escaneo remoto', NULL, 1, 1, 'Celular Lector (Láser USB)', '2026-10-01 14:40:20'),
(187, '6944d65b62c9d3673f26207cb2610b55', '7750885022309', 'Escaneo remoto', NULL, 1, 1, 'Celular Lector (Láser USB)', '2026-10-01 14:40:31'),
(188, '6944d65b62c9d3673f26207cb2610b55', '7750885022309', 'Escaneo remoto', NULL, 1, 1, 'Celular Lector (Láser USB)', '2026-10-01 15:43:29'),
(189, '6944d65b62c9d3673f26207cb2610b55', '7750885022309', 'Escaneo remoto', NULL, 1, 1, 'Celular Lector (Láser USB)', '2026-10-01 15:43:35'),
(190, '6944d65b62c9d3673f26207cb2610b55', '7750885022309', 'Escaneo remoto', NULL, 1, 1, 'Celular Lector (Láser USB)', '2026-10-01 15:43:42'),
(191, '6944d65b62c9d3673f26207cb2610b55', '7750885022309', 'Escaneo remoto', NULL, 1, 1, 'Celular Lector (Láser USB)', '2026-10-01 15:44:15'),
(192, '6944d65b62c9d3673f26207cb2610b55', '7750885022309', 'Escaneo remoto', NULL, 1, 1, 'Celular Lector (Láser USB)', '2026-10-01 15:47:19'),
(193, '6944d65b62c9d3673f26207cb2610b55', '7750885022309', 'Escaneo remoto', NULL, 1, 1, 'Celular Lector (Láser USB)', '2026-10-01 15:47:28'),
(194, '6944d65b62c9d3673f26207cb2610b55', '7750885022309', 'Escaneo remoto', NULL, 1, 1, 'Celular Lector (Láser USB)', '2026-10-01 15:48:02'),
(195, '6944d65b62c9d3673f26207cb2610b55', '7750885022309', 'Escaneo remoto', NULL, 1, 1, 'Celular Lector (Láser USB)', '2026-10-01 15:48:08'),
(196, '6944d65b62c9d3673f26207cb2610b55', '7750885022309', 'Escaneo remoto', NULL, 1, 1, 'Celular Lector (Láser USB)', '2026-10-01 15:48:20'),
(197, '6944d65b62c9d3673f26207cb2610b55', '7750885022309', 'Escaneo remoto', NULL, 1, 1, 'Celular Lector (Láser USB)', '2026-10-01 15:50:10'),
(198, '6944d65b62c9d3673f26207cb2610b55', '7750885022309', 'Escaneo remoto', NULL, 1, 1, 'Celular Lector (Láser USB)', '2026-10-01 15:50:31'),
(199, '6944d65b62c9d3673f26207cb2610b55', '7750885022309', 'Escaneo remoto', NULL, 1, 1, 'Celular Lector (Láser USB)', '2026-10-01 15:53:39'),
(200, 'ab733b037e15a46ec3d0cfe9889c50f4', '7750885022309', 'Escaneo remoto', NULL, 1, 1, 'Prueba local', '2026-10-01 15:54:34'),
(201, '6944d65b62c9d3673f26207cb2610b55', 'ARR2026 238', 'Escaneo remoto', NULL, 1, 1, 'Celular Lector (Láser USB)', '2026-10-02 19:36:55'),
(202, '6944d65b62c9d3673f26207cb2610b55', '211122211131', 'Escaneo remoto', NULL, 1, 1, 'Celular Lector (Láser USB)', '2026-10-02 19:38:11'),
(203, '6944d65b62c9d3673f26207cb2610b55', 'ARR2026 231', 'Escaneo remoto', NULL, 1, 1, 'Celular Lector (Láser USB)', '2026-10-02 19:38:46'),
(204, '6944d65b62c9d3673f26207cb2610b55', 'ARR2026 231', 'Escaneo remoto', NULL, 1, 1, 'Celular Lector (Láser USB)', '2026-10-02 19:38:51'),
(205, '6944d65b62c9d3673f26207cb2610b55', 'ARR2026 231', 'Escaneo remoto', NULL, 1, 1, 'Celular Lector (Láser USB)', '2026-10-02 19:38:57'),
(206, '6944d65b62c9d3673f26207cb2610b55', '112211777777', 'Escaneo remoto', NULL, 1, 1, 'Celular Lector (Láser USB)', '2026-10-02 19:38:58'),
(207, '6944d65b62c9d3673f26207cb2610b55', 'ARR2026 238', 'Escaneo remoto', NULL, 1, 1, 'Celular Lector (Láser USB)', '2026-10-02 19:39:12'),
(208, '6944d65b62c9d3673f26207cb2610b55', 'ARR2026 238', 'Escaneo remoto', NULL, 1, 1, 'Celular Lector (Láser USB)', '2026-10-02 19:40:25'),
(209, '6944d65b62c9d3673f26207cb2610b55', '7750106002486', 'Escaneo remoto', NULL, 1, 1, 'Celular Lector (Láser USB)', '2026-10-02 19:40:56'),
(210, '6944d65b62c9d3673f26207cb2610b55', '7750106002486', 'Escaneo remoto', NULL, 1, 1, 'Celular Lector (Láser USB)', '2026-10-02 19:41:55'),
(211, '6944d65b62c9d3673f26207cb2610b55', '7750106002486', 'Escaneo remoto', NULL, 1, 1, 'Celular Lector (Láser USB)', '2026-10-02 19:42:22'),
(212, '6944d65b62c9d3673f26207cb2610b55', '7750106000086', 'Escaneo remoto', NULL, 1, 1, 'Celular Lector (Láser USB)', '2026-10-02 19:42:22'),
(213, '6944d65b62c9d3673f26207cb2610b55', '7750106002486', 'Escaneo remoto', NULL, 1, 1, 'Celular Lector (Láser USB)', '2026-10-02 19:42:22'),
(214, '6944d65b62c9d3673f26207cb2610b55', '7750106002486', 'Escaneo remoto', NULL, 1, 1, 'Celular Lector (Láser USB)', '2026-10-02 19:47:51'),
(215, '6944d65b62c9d3673f26207cb2610b55', '7750106002486', 'Escaneo remoto', NULL, 1, 1, 'Celular Lector (Láser USB)', '2026-10-02 19:47:57'),
(216, '6944d65b62c9d3673f26207cb2610b55', '4310716002486', 'Escaneo remoto', NULL, 1, 1, 'Celular Lector (Láser USB)', '2026-10-02 19:47:59'),
(217, '6944d65b62c9d3673f26207cb2610b55', '7750106002486', 'Escaneo remoto', NULL, 1, 1, 'Celular Lector (Láser USB)', '2026-10-02 19:48:00'),
(218, '6944d65b62c9d3673f26207cb2610b55', '908052797452', 'Escaneo remoto', NULL, 1, 1, 'Celular Lector (Láser USB)', '2026-10-02 19:48:07'),
(219, '6944d65b62c9d3673f26207cb2610b55', '7750727014240', 'Escaneo remoto', NULL, 1, 1, 'Celular Lector (Láser USB)', '2026-10-02 19:48:07'),
(220, '6944d65b62c9d3673f26207cb2610b55', '7750106002486', 'Escaneo remoto', NULL, 1, 1, 'Celular Lector (Láser USB)', '2026-10-02 19:49:12'),
(221, '6944d65b62c9d3673f26207cb2610b55', '7750182005982', 'Escaneo remoto', NULL, 1, 1, 'Celular Lector (Láser USB)', '2026-10-02 19:49:58'),
(222, '6944d65b62c9d3673f26207cb2610b55', '7750182005982', 'Escaneo remoto', NULL, 1, 1, 'Celular Lector (Láser USB)', '2026-10-02 19:50:02'),
(223, '6944d65b62c9d3673f26207cb2610b55', '7750182005982', 'Escaneo remoto', NULL, 1, 1, 'Celular Lector (Láser USB)', '2026-10-02 19:50:03'),
(224, '6944d65b62c9d3673f26207cb2610b55', '7750182005982', 'Escaneo remoto', NULL, 1, 1, 'Celular Lector (Láser USB)', '2026-10-02 19:50:11'),
(225, '6944d65b62c9d3673f26207cb2610b55', '4750512005982', 'Escaneo remoto', NULL, 1, 1, 'Celular Lector (Láser USB)', '2026-10-02 19:51:00'),
(226, '6944d65b62c9d3673f26207cb2610b55', '7750182005982', 'Escaneo remoto', NULL, 1, 1, 'Celular Lector (Láser USB)', '2026-10-02 19:51:01'),
(227, '6944d65b62c9d3673f26207cb2610b55', '750059005982', 'Escaneo remoto', NULL, 1, 1, 'Celular Lector (Láser USB)', '2026-10-02 19:51:02'),
(228, '6944d65b62c9d3673f26207cb2610b55', '7750182005982', 'Escaneo remoto', NULL, 1, 1, 'Celular Lector (Láser USB)', '2026-10-02 19:51:06'),
(229, '6944d65b62c9d3673f26207cb2610b55', '7750182005982', 'Escaneo remoto', NULL, 1, 1, 'Celular Lector (Láser USB)', '2026-10-02 19:51:12'),
(230, '6944d65b62c9d3673f26207cb2610b55', '7750182005982', 'Escaneo remoto', NULL, 1, 1, 'Celular Lector (Láser USB)', '2026-10-02 19:51:28'),
(231, '6944d65b62c9d3673f26207cb2610b55', '7750182005982', 'Escaneo remoto', NULL, 1, 1, 'Celular Lector (Láser USB)', '2026-10-02 19:51:38'),
(232, '6944d65b62c9d3673f26207cb2610b55', '7750182005982', 'Escaneo remoto', NULL, 1, 1, 'Celular Lector (Láser USB)', '2026-10-02 19:51:43'),
(233, '6944d65b62c9d3673f26207cb2610b55', '7750182005982', 'Escaneo remoto', NULL, 1, 1, 'Celular Lector (Láser USB)', '2026-10-02 19:51:47'),
(234, '6944d65b62c9d3673f26207cb2610b55', '7750106000086', 'Escaneo remoto', NULL, 1, 1, 'Celular Lector (Láser USB)', '2026-10-02 19:51:51'),
(235, '6944d65b62c9d3673f26207cb2610b55', '7750106002486', 'Escaneo remoto', NULL, 1, 1, 'Celular Lector (Láser USB)', '2026-10-02 19:51:51'),
(236, '6944d65b62c9d3673f26207cb2610b55', '7750106002486', 'Escaneo remoto', NULL, 1, 1, 'Celular Lector (Láser USB)', '2026-10-02 19:51:56'),
(237, '6944d65b62c9d3673f26207cb2610b55', '7750727014240', 'Escaneo remoto', NULL, 1, 1, 'Celular Lector (Láser USB)', '2026-10-02 19:52:00'),
(238, '6944d65b62c9d3673f26207cb2610b55', '4750826002486', 'Escaneo remoto', NULL, 1, 1, 'Celular Lector (Láser USB)', '2026-10-02 19:52:07'),
(239, '6944d65b62c9d3673f26207cb2610b55', '7750106002486', 'Escaneo remoto', NULL, 1, 1, 'Celular Lector (Láser USB)', '2026-10-02 19:52:07'),
(240, '6944d65b62c9d3673f26207cb2610b55', '06211776', 'Escaneo remoto', NULL, 1, 1, 'Celular Lector (Láser USB)', '2026-10-02 19:52:24'),
(241, '6944d65b62c9d3673f26207cb2610b55', '7750106009966', 'Escaneo remoto', NULL, 1, 1, 'Celular Lector (Láser USB)', '2026-10-02 19:53:17'),
(242, '6944d65b62c9d3673f26207cb2610b55', '7750106002486', 'Escaneo remoto', NULL, 1, 1, 'Celular Lector (Láser USB)', '2026-10-02 19:53:17'),
(243, '6944d65b62c9d3673f26207cb2610b55', 'ARR2026 238', 'Escaneo remoto', NULL, 1, 1, 'Celular Lector (Láser USB)', '2026-10-02 19:53:34'),
(244, '6944d65b62c9d3673f26207cb2610b55', '7750182007269', 'Escaneo remoto', NULL, 1, 1, 'Celular Lector (Láser USB)', '2026-10-02 19:54:39'),
(245, '6944d65b62c9d3673f26207cb2610b55', '7750182007269', 'Escaneo remoto', NULL, 1, 1, 'Celular Lector (Láser USB)', '2026-10-02 19:54:40'),
(246, '6944d65b62c9d3673f26207cb2610b55', '7750182007269', 'Escaneo remoto', NULL, 1, 1, 'Celular Lector (Láser USB)', '2026-10-02 19:54:43'),
(247, '6944d65b62c9d3673f26207cb2610b55', '752589007269', 'Escaneo remoto', NULL, 1, 1, 'Celular Lector (Láser USB)', '2026-10-02 19:54:46'),
(248, '6944d65b62c9d3673f26207cb2610b55', '7750182007269', 'Escaneo remoto', NULL, 1, 1, 'Celular Lector (Láser USB)', '2026-10-02 19:54:46'),
(249, '6944d65b62c9d3673f26207cb2610b55', '781077007269', 'Escaneo remoto', NULL, 1, 1, 'Celular Lector (Láser USB)', '2026-10-02 19:55:32'),
(250, '6944d65b62c9d3673f26207cb2610b55', '7750182007269', 'Escaneo remoto', NULL, 1, 1, 'Celular Lector (Láser USB)', '2026-10-02 19:55:32');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `producto`
--

CREATE TABLE `producto` (
  `id_producto` int(11) NOT NULL,
  `id_categoria` int(11) DEFAULT NULL,
  `id_grupo` int(11) DEFAULT NULL,
  `codigo` varchar(50) DEFAULT NULL,
  `codigo_barras` varchar(100) DEFAULT NULL,
  `nombre` varchar(150) NOT NULL,
  `titulo` varchar(150) NOT NULL,
  `marca` varchar(100) DEFAULT NULL,
  `descripcion` text DEFAULT NULL,
  `precio_costo` decimal(10,2) NOT NULL DEFAULT 0.00,
  `precio` decimal(10,2) NOT NULL,
  `precio_anterior` decimal(10,2) DEFAULT NULL,
  `unidad_medida` varchar(30) NOT NULL DEFAULT 'Unidad',
  `stock` int(11) NOT NULL DEFAULT 0,
  `imagen` varchar(255) DEFAULT NULL,
  `imagen_url` varchar(255) DEFAULT NULL,
  `imagen_2` varchar(255) DEFAULT NULL,
  `imagen_3` varchar(255) DEFAULT NULL,
  `imagen_4` varchar(255) DEFAULT NULL,
  `es_sugerencia` tinyint(1) NOT NULL DEFAULT 0,
  `activo` tinyint(1) NOT NULL DEFAULT 1,
  `fecha_creacion` datetime NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Volcado de datos para la tabla `producto`
--

INSERT INTO `producto` (`id_producto`, `id_categoria`, `id_grupo`, `codigo`, `codigo_barras`, `nombre`, `titulo`, `marca`, `descripcion`, `precio_costo`, `precio`, `precio_anterior`, `unidad_medida`, `stock`, `imagen`, `imagen_url`, `imagen_2`, `imagen_3`, `imagen_4`, `es_sugerencia`, `activo`, `fecha_creacion`) VALUES
(1, 15, 7, 'PROD-0001', '7750123000018', 'Arroz Superior Costeño 1kg', 'Arroz Superior Costeño 1kg', 'Costeño', 'Arroz seleccionado extra grano largo', 3.80, 4.80, 6.00, 'Bolsa 1kg', 75, 'https://images.unsplash.com/photo-1586201375761-83865001e31c?w=600&auto=format&fit=crop&q=80', NULL, NULL, NULL, NULL, 1, 1, '2026-09-24 11:37:25'),
(2, 17, 8, NULL, NULL, 'Aceite Vegetal Primor 900ml', 'Aceite Vegetal Primor 900ml', 'Primor', 'Aceite vegetal refinado sin colesterol', 7.50, 9.50, NULL, 'UND', 7, 'https://images.unsplash.com/photo-1474979266404-7eaacbcd87c5?w=600&auto=format&fit=crop&q=80', NULL, NULL, NULL, NULL, 0, 1, '2026-09-24 11:37:25'),
(3, 8, 4, 'PROD-0003', '7750123000032', 'Leche Evaporada Gloria Azul 400g', 'Leche Evaporada Gloria Azul 400g', 'Gloria', 'Leche entera con vitaminas A y D', 3.20, 4.20, 5.25, 'Lata', 126, 'https://images.unsplash.com/photo-1550583724-b2692b85b150?w=600&auto=format&fit=crop&q=80', NULL, NULL, NULL, NULL, 1, 1, '2026-09-24 11:37:25'),
(4, 21, 10, 'PROD-0004', '7750123000049', 'Gaseosa Inca Kola 500ml', 'Gaseosa Inca Kola 500ml', 'Caserita Selección', 'Bebida gaseosa sabor nacional personal', 2.00, 3.00, NULL, 'Botella', 60, 'https://images.unsplash.com/photo-1622483767028-3f66f32aef97?w=600&auto=format&fit=crop&q=80', NULL, NULL, NULL, NULL, 0, 1, '2026-09-24 11:37:25'),
(5, 2, 1, 'PROD-0005', NULL, 'Tomate Italiano Seleccionado 1kg', 'Tomate Italiano Seleccionado 1kg', 'Del Huerto', NULL, 0.00, 3.80, 4.50, 'UND', 50, 'https://images.unsplash.com/photo-1592924357228-91a4daadcfea?w=400&auto=format&fit=crop&q=80', NULL, NULL, NULL, NULL, 1, 1, '2026-09-27 19:10:30'),
(6, 2, 1, 'PROD-0006', NULL, 'Limón Sutil Premium 1kg', 'Limón Sutil Premium 1kg', 'Del Huerto', NULL, 0.00, 4.80, NULL, 'UND', 80, 'https://images.unsplash.com/photo-1533083441298-0236a6f60ee2?w=400&auto=format&fit=crop&q=80', NULL, NULL, NULL, NULL, 1, 1, '2026-09-27 19:10:30'),
(7, 2, 1, 'PROD-0007', NULL, 'Cebolla Roja Fresca 1kg', 'Cebolla Roja Fresca 1kg', 'Del Huerto', NULL, 0.00, 3.20, 4.00, 'UND', 48, 'https://images.unsplash.com/photo-1618512496248-a07fe83aa8cb?w=400&auto=format&fit=crop&q=80', NULL, NULL, NULL, NULL, 1, 1, '2026-09-27 19:10:30'),
(8, 1, 1, NULL, NULL, 'Manzana Delicia Seleccionada 1kg', 'Manzana Delicia Seleccionada 1kg', 'La Caserita', '', 0.00, 5.40, NULL, 'UND', 9, 'https://images.unsplash.com/photo-1560806887-1e4cd0b6cbd6?w=400&auto=format&fit=crop&q=80', NULL, NULL, NULL, NULL, 0, 1, '2026-09-27 19:10:30'),
(9, 1, 1, 'PROD-0009', NULL, 'Plátano de Seda de Chanchamayo (Mano)', 'Plátano de Seda de Chanchamayo (Mano)', 'Selva Alta', NULL, 0.00, 4.50, 5.50, 'UND', 35, 'https://images.unsplash.com/photo-1571771894821-ce9b6c11b08e?w=400&auto=format&fit=crop&q=80', NULL, NULL, NULL, NULL, 1, 1, '2026-09-27 19:10:30'),
(10, 1, 1, 'PROD-0010', NULL, 'Mandarina Sin Pepa Dulce 1kg', 'Mandarina Sin Pepa Dulce 1kg', 'La Caserita', NULL, 0.00, 4.20, 5.20, 'UND', 64, 'https://images.unsplash.com/photo-1611080626919-7cf5a9dbab5b?w=400&auto=format&fit=crop&q=80', NULL, NULL, NULL, NULL, 1, 1, '2026-09-27 19:10:30'),
(11, 3, 2, 'PROD-0011', NULL, 'Pollo Entero Fresco con Menudencia 2.2kg', 'Pollo Entero Fresco con Menudencia 2.2kg', 'San Fernando', NULL, 0.00, 21.50, 24.90, 'UND', 30, 'https://images.unsplash.com/photo-1587593810167-a84920ea0781?w=400&auto=format&fit=crop&q=80', NULL, NULL, NULL, NULL, 1, 1, '2026-09-27 19:10:30'),
(12, 3, 2, 'PROD-0012', NULL, 'Pechuga de Pollo Especial 1kg', 'Pechuga de Pollo Especial 1kg', 'San Fernando', NULL, 0.00, 15.90, NULL, 'UND', 25, 'https://images.unsplash.com/photo-1604503468506-a8da13d82791?w=400&auto=format&fit=crop&q=80', NULL, NULL, NULL, NULL, 1, 1, '2026-09-27 19:10:30'),
(13, 6, 3, 'PROD-0013', NULL, 'Filete de Bonito Fresco del Día 1kg', 'Filete de Bonito Fresco del Día 1kg', 'Pesca Fresca', NULL, 0.00, 18.00, 22.00, 'UND', 20, 'https://images.unsplash.com/photo-1534422298391-e4f8c172dddb?w=400&auto=format&fit=crop&q=80', NULL, NULL, NULL, NULL, 1, 1, '2026-09-27 19:10:30'),
(14, 8, 4, 'PROD-0014', NULL, 'Leche Entera Gloria UHT Pack 6 und', 'Leche Entera Gloria UHT Pack 6 und', 'Gloria', NULL, 0.00, 23.90, 26.50, 'UND', 45, 'https://images.unsplash.com/photo-1550583724-b2692b85b150?w=400&auto=format&fit=crop&q=80', NULL, NULL, NULL, NULL, 1, 1, '2026-09-27 19:10:30'),
(15, 9, 4, 'PROD-0015', NULL, 'Yogurt Fresa Gloria 1kg', 'Yogurt Fresa Gloria 1kg', 'Gloria', NULL, 0.00, 6.80, NULL, 'UND', 35, 'https://images.unsplash.com/photo-1488477181946-6428a0291777?w=400&auto=format&fit=crop&q=80', NULL, NULL, NULL, NULL, 1, 1, '2026-09-27 19:10:30'),
(16, 14, 6, 'PROD-0016', NULL, 'Pan de Molde Blanco Bimbo Grande 650g', 'Pan de Molde Blanco Bimbo Grande 650g', 'Bimbo', NULL, 0.00, 8.90, 10.50, 'UND', 40, 'https://images.unsplash.com/photo-1509440159596-0249088772ff?w=400&auto=format&fit=crop&q=80', NULL, NULL, NULL, NULL, 1, 1, '2026-09-27 19:10:30'),
(17, 14, 6, 'PROD-0017', NULL, 'Bolsa de Pan Francés Calientito 10 und', 'Bolsa de Pan Francés Calientito 10 und', 'La Caserita', NULL, 0.00, 4.00, NULL, 'UND', 50, 'https://images.unsplash.com/photo-1586444248902-2f64eddc13df?w=400&auto=format&fit=crop&q=80', NULL, NULL, NULL, NULL, 1, 1, '2026-09-27 19:10:30'),
(18, 15, 7, 'PROD-0018', NULL, 'Arroz Extra Premium Costeño Saco 5kg', 'Arroz Extra Premium Costeño Saco 5kg', 'Costeño', NULL, 0.00, 18.50, 22.90, 'UND', 54, 'https://images.unsplash.com/photo-1586201375761-83865001e31c?w=400&auto=format&fit=crop&q=80', NULL, NULL, NULL, NULL, 1, 1, '2026-09-27 19:10:30'),
(19, 26, 11, NULL, NULL, 'Aceite de Girasol Primor Botella 1L', 'Black Out', 'Primor', '', 0.00, 2.50, NULL, 'UND', 20, 'uploads/productos/prod_1790792360_153_imagen.png', NULL, NULL, NULL, NULL, 0, 1, '2026-09-27 19:10:30'),
(20, 19, 9, 'PROD-0020', NULL, 'Fideos Spaghetti Don Vittorio 500g', 'Fideos Spaghetti Don Vittorio 500g', 'Don Vittorio', NULL, 0.00, 3.40, NULL, 'UND', 89, 'https://images.unsplash.com/photo-1551462147-ff29053bfc14?w=400&auto=format&fit=crop&q=80', NULL, NULL, NULL, NULL, 1, 1, '2026-09-27 19:10:30'),
(21, 21, 10, 'PROD-0021', NULL, 'Gaseosa Coca Cola Sabor Original 1.5L', 'Gaseosa Coca Cola Sabor Original 1.5L', 'Coca Cola', NULL, 0.00, 6.50, 7.80, 'UND', 50, 'https://images.unsplash.com/photo-1622483767028-3f66f32aef97?w=400&auto=format&fit=crop&q=80', NULL, NULL, NULL, NULL, 1, 1, '2026-09-27 19:10:30'),
(22, 22, 10, 'PROD-0022', NULL, 'Agua San Luis Sin Gas 2.5L', 'Agua San Luis Sin Gas 2.5L', 'San Luis', NULL, 0.00, 3.50, NULL, 'UND', 70, 'https://images.unsplash.com/photo-1548839140-29a749e1bc4e?w=400&auto=format&fit=crop&q=80', NULL, NULL, NULL, NULL, 1, 1, '2026-09-27 19:10:30'),
(23, 26, 11, 'PROD-0023', NULL, 'Galletas de Chocolate Casino Pack 8 und', 'Galletas de Chocolate Casino Pack 8 und', 'Casino', NULL, 0.00, 6.50, 7.50, 'UND', 60, 'https://images.unsplash.com/photo-1558961363-fa8fdf82db35?w=400&auto=format&fit=crop&q=80', NULL, NULL, NULL, NULL, 1, 1, '2026-09-27 19:10:30'),
(24, 25, 11, 'PROD-0024', NULL, 'Papas Lays Clásicas Bolsa Grande 160g', 'Papas Lays Clásicas Bolsa Grande 160g', 'Lays', NULL, 0.00, 5.80, NULL, 'UND', 45, 'https://images.unsplash.com/photo-1566478989037-eec170784d0b?w=400&auto=format&fit=crop&q=80', NULL, NULL, NULL, NULL, 1, 1, '2026-09-27 19:10:30'),
(25, 28, 12, 'PROD-0025', NULL, 'Detergente en Polvo Bolívar Flores 2.6kg', 'Detergente en Polvo Bolívar Flores 2.6kg', 'Bolívar', NULL, 0.00, 24.90, 29.90, 'UND', 30, 'https://images.unsplash.com/photo-1583947215259-38e31be8751f?w=400&auto=format&fit=crop&q=80', NULL, NULL, NULL, NULL, 1, 1, '2026-09-27 19:10:30'),
(26, 30, 12, 'PROD-0026', NULL, 'Lejía Clorox Tradicional Botella 1L', 'Lejía Clorox Tradicional Botella 1L', 'Clorox', NULL, 0.00, 4.20, NULL, 'UND', 50, 'https://images.unsplash.com/photo-1585421514738-01798e348b17?w=400&auto=format&fit=crop&q=80', NULL, NULL, NULL, NULL, 1, 1, '2026-09-27 19:10:30'),
(27, 31, 13, 'PROD-0027', NULL, 'Pañales Pampers Confort Sec Talla M 44 und', 'Pañales Pampers Confort Sec Talla M 44 und', 'Pampers', NULL, 0.00, 38.50, 43.80, 'UND', 35, 'https://images.unsplash.com/photo-1515488042361-ee00e0ddd4e4?w=400&auto=format&fit=crop&q=80', NULL, NULL, NULL, NULL, 1, 1, '2026-09-27 19:10:30'),
(28, 32, 13, 'PROD-0028', NULL, 'Shampoo Head & Shoulders Limpieza Renovadora 375ml', 'Shampoo Head & Shoulders Limpieza Renovadora 375ml', 'Head & Shoulders', NULL, 0.00, 16.90, NULL, 'UND', 40, 'https://images.unsplash.com/photo-1535585209827-a15fcdbc4c2d?w=400&auto=format&fit=crop&q=80', NULL, NULL, NULL, NULL, 1, 1, '2026-09-27 19:10:30'),
(29, 35, 15, 'PROD-0029', NULL, 'Comida para Perro Adulto Ricocan Carne 3kg', 'Comida para Perro Adulto Ricocan Carne 3kg', 'Ricocan', NULL, 0.00, 27.90, 32.50, 'UND', 25, 'https://images.unsplash.com/photo-1589924691995-400dc9ecc119?w=400&auto=format&fit=crop&q=80', NULL, NULL, NULL, NULL, 1, 1, '2026-09-27 19:10:30'),
(30, 36, 15, 'PROD-0030', NULL, 'Comida para Gato Whiskas Pescado 1kg', 'Comida para Gato Whiskas Pescado 1kg', 'Whiskas', NULL, 0.00, 14.50, NULL, 'UND', 30, 'https://images.unsplash.com/photo-1514888286974-6c03e2ca1dba?w=400&auto=format&fit=crop&q=80', NULL, NULL, NULL, NULL, 1, 1, '2026-09-27 19:10:30'),
(31, 35, 15, 'PROD-0031', 'ARR2026 238', '', 'laptop', NULL, '', 0.00, 15000.00, NULL, 'UND', 9, 'uploads/productos/prod_1790987861_624_imagen.png', NULL, NULL, NULL, NULL, 0, 1, '2026-10-02 19:37:41'),
(32, 26, 11, 'PROD-0032', '7750106002486', '', 'Galleta Yefri', NULL, '', 0.00, 2.00, 3.00, 'UND', 10, 'uploads/productos/prod_1790988103_910_imagen.png', NULL, NULL, NULL, NULL, 0, 1, '2026-10-02 19:41:43'),
(33, 26, 11, 'PROD-0033', '7750727014240', '', 'Binboletas', NULL, '', 0.00, 2.50, NULL, 'UND', 15, 'uploads/productos/prod_1790988526_804_imagen.png', NULL, NULL, NULL, NULL, 0, 1, '2026-10-02 19:48:46'),
(34, 23, 10, 'PROD-0034', '7750182005982', '', 'Valle', NULL, '', 0.00, 3.50, NULL, 'UND', 9, 'uploads/productos/prod_1790988646_212_imagen.png', NULL, NULL, NULL, NULL, 0, 1, '2026-10-02 19:50:46');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `proveedor`
--

CREATE TABLE `proveedor` (
  `id_proveedor` int(11) NOT NULL,
  `ruc` varchar(20) DEFAULT NULL,
  `razon_social` varchar(150) NOT NULL,
  `contacto_nombre` varchar(100) DEFAULT NULL,
  `telefono` varchar(20) DEFAULT NULL,
  `correo` varchar(150) DEFAULT NULL,
  `direccion` varchar(255) DEFAULT NULL,
  `activo` tinyint(1) NOT NULL DEFAULT 1,
  `fecha_registro` datetime NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Volcado de datos para la tabla `proveedor`
--

INSERT INTO `proveedor` (`id_proveedor`, `ruc`, `razon_social`, `contacto_nombre`, `telefono`, `correo`, `direccion`, `activo`, `fecha_registro`) VALUES
(1, '20512345678', 'Distribuidora Alimentos del Norte S.A.C.', 'Sr. Roberto Gómez', '944555666', 'ventas@alimentosnorte.com', 'Av. Central 1234, Lima', 1, '2026-09-24 11:37:25'),
(2, '20609876543', 'Bebidas y Lácteos del Perú E.I.R.L.', 'Sra. Patricia Vega', '933222111', 'pedidos@lacteosperu.com', 'Jr. Los Cedros 450, Lima', 1, '2026-09-24 11:37:25');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `reposicion`
--

CREATE TABLE `reposicion` (
  `id_reposicion` int(11) NOT NULL,
  `id_producto` int(11) NOT NULL,
  `id_proveedor` int(11) DEFAULT NULL,
  `id_usuario` int(11) NOT NULL,
  `cantidad` int(11) NOT NULL,
  `costo_unitario` decimal(10,2) NOT NULL,
  `precio_venta_sugerido` decimal(10,2) DEFAULT NULL,
  `proveedor` varchar(150) DEFAULT NULL,
  `nro_comprobante` varchar(50) DEFAULT NULL,
  `lote` varchar(50) DEFAULT NULL,
  `fecha_vencimiento` date DEFAULT NULL,
  `observacion` text DEFAULT NULL,
  `fecha` datetime NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Volcado de datos para la tabla `reposicion`
--

INSERT INTO `reposicion` (`id_reposicion`, `id_producto`, `id_proveedor`, `id_usuario`, `cantidad`, `costo_unitario`, `precio_venta_sugerido`, `proveedor`, `nro_comprobante`, `lote`, `fecha_vencimiento`, `observacion`, `fecha`) VALUES
(1, 3, 2, 1, 6, 3.20, 4.20, 'Bebidas y Lácteos del Perú E.I.R.L.', 'F000-01', '', NULL, '', '2026-10-10 11:38:12');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `sesion_caja`
--

CREATE TABLE `sesion_caja` (
  `id_sesion` int(11) NOT NULL,
  `caja_id` varchar(50) NOT NULL DEFAULT 'CAJA-01',
  `id_usuario` int(11) NOT NULL,
  `token_emparejamiento` varchar(64) NOT NULL,
  `estado_conexion` enum('esperando','conectado','cerrado') NOT NULL DEFAULT 'esperando',
  `dispositivo_info` varchar(150) DEFAULT 'Escáner Móvil Android',
  `items_json` longtext DEFAULT NULL,
  `total_venta` decimal(10,2) NOT NULL DEFAULT 0.00,
  `monto_apertura` decimal(10,2) NOT NULL DEFAULT 0.00,
  `monto_cierre_esperado` decimal(10,2) DEFAULT NULL,
  `monto_cierre_real` decimal(10,2) DEFAULT NULL,
  `diferencia` decimal(10,2) DEFAULT NULL,
  `total_ventas_efectivo` decimal(10,2) NOT NULL DEFAULT 0.00,
  `total_ventas_yape` decimal(10,2) NOT NULL DEFAULT 0.00,
  `total_ventas_plin` decimal(10,2) NOT NULL DEFAULT 0.00,
  `total_ventas_tarjeta` decimal(10,2) NOT NULL DEFAULT 0.00,
  `total_ingresos` decimal(10,2) NOT NULL DEFAULT 0.00,
  `total_egresos` decimal(10,2) NOT NULL DEFAULT 0.00,
  `estado` enum('abierta','cerrada') NOT NULL DEFAULT 'abierta',
  `fecha_apertura` datetime NOT NULL DEFAULT current_timestamp(),
  `fecha_cierre` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Volcado de datos para la tabla `sesion_caja`
--

INSERT INTO `sesion_caja` (`id_sesion`, `caja_id`, `id_usuario`, `token_emparejamiento`, `estado_conexion`, `dispositivo_info`, `items_json`, `total_venta`, `monto_apertura`, `monto_cierre_esperado`, `monto_cierre_real`, `diferencia`, `total_ventas_efectivo`, `total_ventas_yape`, `total_ventas_plin`, `total_ventas_tarjeta`, `total_ingresos`, `total_egresos`, `estado`, `fecha_apertura`, `fecha_cierre`) VALUES
(2, 'CAJA-01', 1, 'dc14cabd26f4a4a64462acfa1b154bb6', 'cerrado', 'Simulador Web Android (Prueba)', '[]', 0.00, 0.00, 4.80, 4.80, 0.00, 4.80, 0.00, 0.00, 0.00, 0.00, 0.00, 'cerrada', '2026-09-24 14:49:23', '2026-09-30 00:55:23'),
(3, 'CAJA-01', 3, '9814dc3ea9b49032b19df3a4cbddcccb', 'cerrado', 'Simulador Web Android (Prueba)', '[]', 0.00, 0.00, 0.00, 0.00, 0.00, 0.00, 0.00, 0.00, 0.00, 0.00, 0.00, 'cerrada', '2026-09-28 01:11:16', '2026-09-30 00:54:40'),
(4, 'CAJA-01', 1, '818514dad38b630250fc941ea84ef32f', 'cerrado', 'Celular Moto G53 (Lector USB)', '[]', 0.00, 0.00, 0.00, 0.00, 0.00, 0.00, 0.00, 0.00, 0.00, 0.00, 0.00, 'cerrada', '2026-09-30 01:07:06', '2026-09-30 22:27:05'),
(5, 'CAJA-01', 1, '433494021d163305c873999553d478cd', 'cerrado', 'Escáner Móvil Android', NULL, 0.00, 0.00, 0.00, 0.00, 0.00, 0.00, 0.00, 0.00, 0.00, 0.00, 0.00, 'cerrada', '2026-09-30 22:28:01', '2026-09-30 22:34:09'),
(6, 'CAJA-01', 1, '632327e65db2299fb86c82a3b4591c16', 'cerrado', 'Escáner Móvil Android', NULL, 0.00, 0.00, 0.00, 0.00, 0.00, 0.00, 0.00, 0.00, 0.00, 0.00, 0.00, 'cerrada', '2026-09-30 22:35:51', '2026-09-30 22:36:50'),
(7, 'CAJA-01', 1, '3faa7612261bacb8cb056667a7a20894', 'cerrado', 'Escáner Móvil Android', NULL, 0.00, 0.00, 10.00, 10.00, 0.00, 0.00, 0.00, 0.00, 0.00, 10.00, 0.00, 'cerrada', '2026-09-30 22:37:12', '2026-09-30 23:03:56'),
(8, 'CAJA-01', 1, '2d11c2231eb524219e8213962172187a', 'cerrado', 'Escáner Móvil Android', NULL, 0.00, 0.00, 0.00, 0.00, 0.00, 0.00, 0.00, 0.00, 0.00, 0.00, 0.00, 'cerrada', '2026-09-30 23:05:08', '2026-09-30 23:17:09'),
(9, 'CAJA-01', 1, '607ed9939ed28019c286285c72a629c5', 'cerrado', 'Escáner Móvil Android', NULL, 0.00, 0.00, 0.00, 0.00, 0.00, 0.00, 0.00, 0.00, 0.00, 0.00, 0.00, 'cerrada', '2026-09-30 23:17:27', '2026-09-30 23:17:39'),
(10, 'CAJA-01', 1, 'ab733b037e15a46ec3d0cfe9889c50f4', 'esperando', 'Celular Moto G53 (Lector USB)', '[{\"id_producto\":31,\"codigo\":\"ARR2026 238\",\"nombre\":\"\",\"precio\":15000,\"cantidad\":1,\"subtotal\":15000,\"imagen_url\":\"\"}]', 15000.00, 0.00, NULL, NULL, NULL, 47.50, 0.00, 0.00, 0.00, 0.00, 0.00, 'abierta', '2026-10-01 01:23:22', NULL),
(11, 'CAJA-01', 3, '6944d65b62c9d3673f26207cb2610b55', 'cerrado', 'Celular (Cable USB)', '[]', 0.00, 0.00, 9.50, 9.50, 0.00, 9.50, 0.00, 0.00, 15000.00, 0.00, 0.00, 'cerrada', '2026-10-01 03:13:56', '2026-10-10 07:18:16'),
(12, 'CAJA-CJ-3', 3, '7e3c702674b45e309c2277dabd4cac46', 'conectado', 'Celular (Bluetooth)', '[{\"id_producto\":5,\"codigo\":\"PROD-0005\",\"nombre\":\"Tomate Italiano Seleccionado 1kg\",\"precio\":3.8,\"cantidad\":3,\"subtotal\":11.4,\"imagen_url\":\"\"}]', 11.40, 0.00, NULL, NULL, NULL, 0.00, 0.00, 0.00, 0.00, 0.00, 0.00, 'abierta', '2026-10-10 07:21:24', NULL);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `sys_sessions`
--

CREATE TABLE `sys_sessions` (
  `id` varchar(255) NOT NULL,
  `data` longtext NOT NULL,
  `last_accessed` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Volcado de datos para la tabla `sys_sessions`
--

INSERT INTO `sys_sessions` (`id`, `data`, `last_accessed`) VALUES
('0clmousubsvl20o4kk286mvlvt', 'csrf_token|s:64:\"dcb71e6f45a7772af08a124464b8c5114affe1fd63e8234dd807306ff8a6e93d\";carritos|a:0:{}', '2026-10-16 20:48:01'),
('0u2rsk3k3eit1oq918f17vdoj4', 'csrf_token|s:64:\"6d78114ead6b411d712ccf908e7e6949008b17eeeb3bee1de2961bca5ddaaadf\";carritos|a:0:{}', '2026-10-16 20:53:34'),
('2ajm9fqa3e4uigngdjbebptk9q', 'csrf_token|s:64:\"67e34de61e9e2abe79ed584a65a064f23833e1f9d5f328666dbebd4675300851\";carritos|a:0:{}', '2026-10-16 22:27:46'),
('31odq4tve1lj0t9ri0qfs7fqfq', 'csrf_token|s:64:\"d8ecb04ca82d346c8079e81ea6e80ca0bcae3892adffe7d5dff5a0cc74241d09\";carritos|a:0:{}', '2026-10-16 20:46:01'),
('3hcd646hccsucjsnkp870i00t5', 'csrf_token|s:64:\"a853a2cfa62735405b112e7035196ce9d632d8019fa1fcce87e33f93fdb993e7\";carritos|a:0:{}', '2026-10-16 22:21:36'),
('5cquvr7qct5ogvgoe78e44ljte', 'csrf_token|s:64:\"33de8677d5962cf06f198d160e16ef095a181c263f4f8bdc294971466f80edd4\";carritos|a:0:{}', '2026-10-16 22:09:19'),
('686nl3qc6r8fj1lf1l93oo19n6', 'csrf_token|s:64:\"0bfbfca1bbfc75bb361056a906c967c75b155e6a024554917cd9b6eb8056025d\";carritos|a:0:{}', '2026-10-16 22:09:42'),
('cu4msjnj7eudvgsv5k6duo9upq', 'csrf_token|s:64:\"529d80e7fafa1e65cea3436f645ee7ba07aafe97a7730c4f7d77eea074b275b2\";carritos|a:0:{}', '2026-10-16 22:09:30'),
('ea9ddanh9qev6getecllfa8lgr', 'csrf_token|s:64:\"0f4660d781529ee486840a60a9382892289d24149845acf66f22642c6e028d67\";carritos|a:0:{}', '2026-10-16 22:22:07'),
('gnirs07hulc3pp2i836lg78r5t', 'csrf_token|s:64:\"7f32b1b8c05840b50ae481eca0ffe96afddeb88feb480b5b47d01d9baf737fd1\";carritos|a:0:{}', '2026-10-16 20:40:42'),
('ijpoings6sgr1gd3giupjhbc1d', 'csrf_token|s:64:\"4b70419a769ad063670d8913ef1dbf02e245d28b64455b78c88c527531102905\";carritos|a:0:{}', '2026-10-16 22:08:53'),
('ksk9vv7ndufucop5lsmppoe018', 'csrf_token|s:64:\"a995cf31457623e64dc5599897b6cdd579f5c4a433603653b78ddd3d54a26c5b\";carritos|a:0:{}', '2026-10-16 20:51:17'),
('n88fp3n4n15v0mu0j7qko3ansf', 'carritos|a:3:{i:2;a:1:{i:5;a:2:{s:11:\"id_producto\";i:5;s:8:\"cantidad\";i:2;}}s:8:\"invitado\";a:0:{}i:1;a:0:{}}csrf_token|s:64:\"6f3e371eb2f2da88ebad41184115073adc994b776169c4563f51c1470a5ff081\";', '2026-10-16 23:17:20'),
('n8d7e11l4smr124tuvjao9cam7', 'csrf_token|s:64:\"a1ac45d6e3913e809c76083b99257e3459b7730cd9d7366dc5326104c03e1548\";carritos|a:0:{}', '2026-10-16 20:46:56'),
('qrn49n1q2er2avjjmoo0g32hne', 'csrf_token|s:64:\"1795743351c5fc3bd90530a19577901005093bdfd1605a13584e6ab46ca9c861\";carritos|a:0:{}', '2026-10-16 20:58:27'),
('s8n9p4p5osu30b25paarsu2g6v', 'csrf_token|s:64:\"64856e37ec5dd370f1e99bfa0a6b17161d683e2ed9e67550352e9495b4bed770\";carritos|a:0:{}', '2026-10-16 22:27:31'),
('t0e2bnhoj8fc3555j2o3vaplhb', 'csrf_token|s:64:\"2efdf26bb679fab06ed328616bc6dadb0723d65f952f48bec9a894953a306c2f\";carritos|a:0:{}', '2026-10-16 22:27:02');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `usuario`
--

CREATE TABLE `usuario` (
  `id_usuario` int(11) NOT NULL,
  `nombre` varchar(100) NOT NULL,
  `apellido` varchar(100) DEFAULT NULL,
  `correo` varchar(150) NOT NULL,
  `password` varchar(255) DEFAULT NULL,
  `telefono` varchar(20) DEFAULT NULL,
  `dni` varchar(15) DEFAULT NULL,
  `rol` enum('admin','cajero','cliente','repartidor') NOT NULL DEFAULT 'cliente',
  `auth_provider` varchar(20) NOT NULL DEFAULT 'local',
  `google_id` varchar(255) DEFAULT NULL,
  `avatar_url` varchar(255) DEFAULT NULL,
  `activo` tinyint(1) NOT NULL DEFAULT 1,
  `intentos_fallidos` int(11) NOT NULL DEFAULT 0,
  `bloqueo_until` datetime DEFAULT NULL,
  `fecha_registro` datetime NOT NULL DEFAULT current_timestamp(),
  `ultimo_login` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Volcado de datos para la tabla `usuario`
--

INSERT INTO `usuario` (`id_usuario`, `nombre`, `apellido`, `correo`, `password`, `telefono`, `dni`, `rol`, `auth_provider`, `google_id`, `avatar_url`, `activo`, `intentos_fallidos`, `bloqueo_until`, `fecha_registro`, `ultimo_login`) VALUES
(1, 'Admin', NULL, 'admin@lacaserita.com', '$2y$10$.8Km22Tlc/qS/BnYHlc8/etaNMeBXuxc8PAa76EAB3.TvPT0hj3ES', NULL, NULL, 'admin', 'local', NULL, NULL, 1, 0, NULL, '2026-09-24 12:39:43', NULL),
(2, 'Erik', 'Carrasco', 'erikcarrasco455@gmail.com', NULL, '942337947', NULL, 'cliente', 'google', '117023226088641828483', 'https://lh3.googleusercontent.com/a/ACg8ocK-a0aRUxlf3EsWHWHwejni1AAN_eaX0P29mQq_L3mSeh8o=s96-c', 1, 0, NULL, '2026-09-24 15:32:30', NULL),
(3, 'Cajero', '1', 'cajero@lacaserita.com', '$2y$10$fuDbTyaNucK9iZnFVg886OXdi4BvptATnYjzbl6RxV2.Eydu1ShN2', '', NULL, 'cajero', 'local', NULL, NULL, 1, 0, NULL, '2026-09-28 01:10:04', NULL),
(4, 'Repartidor', '1', 'repartidor@lacaserita.com', '$2y$10$NNUrXxrPQzCN428lTzyYfeS.hEKK.fS7kQIwsWucsvhtZfM6bqB.S', '', NULL, 'repartidor', 'local', NULL, NULL, 1, 0, NULL, '2026-09-28 01:10:46', NULL),
(5, 'cristian manuel', 'castro barrios', 'smt2dob03@gmail.com', NULL, NULL, NULL, 'cliente', 'google', '105578320743969996621', 'https://lh3.googleusercontent.com/a/ACg8ocJ-ShlnfZ7xwPcFIej09qXt24Kvw_TEDP73Q3rjshlotoUUrw=s96-c', 1, 0, NULL, '2026-09-28 16:57:21', NULL),
(6, 'cliente', NULL, 'cliente@lacaserita.com', '$2y$10$ICqMnUfspB3YQiuLSZAoXOAV4s46aVmfofoghklNGc3jaChHu0RAK', NULL, NULL, 'cliente', 'local', NULL, NULL, 1, 0, NULL, '2026-10-01 14:12:51', NULL);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `venta`
--

CREATE TABLE `venta` (
  `id_venta` int(11) NOT NULL,
  `id_pedido` int(11) NOT NULL,
  `id_usuario` int(11) NOT NULL,
  `subtotal` decimal(10,2) NOT NULL,
  `costo_envio` decimal(10,2) NOT NULL DEFAULT 0.00,
  `igv` decimal(10,2) NOT NULL DEFAULT 0.00,
  `descuento` decimal(10,2) NOT NULL DEFAULT 0.00,
  `total` decimal(10,2) NOT NULL,
  `tipo_comprobante` varchar(30) NOT NULL DEFAULT 'boleta',
  `numero_comprobante` varchar(50) NOT NULL,
  `estado_pago` varchar(50) NOT NULL DEFAULT 'pendiente',
  `fecha` datetime NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Volcado de datos para la tabla `venta`
--

INSERT INTO `venta` (`id_venta`, `id_pedido`, `id_usuario`, `subtotal`, `costo_envio`, `igv`, `descuento`, `total`, `tipo_comprobante`, `numero_comprobante`, `estado_pago`, `fecha`) VALUES
(1, 6, 2, 4.80, 7.00, 0.00, 0.00, 11.80, 'boleta', 'B001-00000006', 'pendiente', '2026-09-29 00:06:19'),
(2, 7, 2, 7.40, 0.00, 0.00, 0.00, 7.40, 'boleta', 'B001-00000007', 'pagado', '2026-09-29 01:49:39'),
(3, 8, 2, 11.80, 7.00, 0.00, 0.00, 18.80, 'boleta', 'B001-00000008', 'pendiente', '2026-09-29 02:04:06'),
(4, 9, 2, 6.40, 0.00, 0.00, 0.00, 6.40, 'boleta', 'B001-00000009', 'pagado', '2026-09-29 03:06:37'),
(5, 10, 2, 4.50, 0.00, 0.00, 0.00, 4.50, 'boleta', 'B001-00000010', 'pagado', '2026-09-29 03:33:40'),
(6, 11, 2, 3.20, 0.00, 0.00, 0.00, 3.20, 'boleta', 'B001-00000011', 'pagado', '2026-09-29 03:40:57'),
(7, 12, 2, 3.20, 0.00, 0.00, 0.00, 3.20, 'boleta', 'B001-00000012', 'pagado', '2026-09-29 19:49:47'),
(8, 18, 2, 3.20, 7.00, 0.00, 0.00, 10.20, 'boleta', 'B001-00000018', 'pendiente', '2026-10-10 11:11:04'),
(9, 19, 1, 4.80, 0.00, 0.00, 0.00, 4.80, 'boleta', 'B001-00000019', 'pagado', '2026-10-15 19:53:17'),
(10, 20, 2, 9.60, 7.00, 0.00, 0.00, 16.60, 'boleta', 'B001-00000020', 'pagado', '2026-10-15 21:32:24'),
(11, 21, 2, 4.50, 7.00, 0.00, 0.00, 11.50, 'boleta', 'B001-00000021', 'pendiente', '2026-10-15 21:32:52'),
(12, 22, 2, 9.90, 0.00, 0.00, 0.00, 9.90, 'boleta', 'B001-00000022', 'pagado', '2026-10-15 21:34:52'),
(13, 23, 2, 5.40, 7.00, 0.00, 0.00, 12.40, 'boleta', 'B001-00000023', 'pagado', '2026-10-15 21:37:06'),
(14, 24, 2, 9.60, 0.00, 0.00, 0.00, 9.60, 'boleta', 'B001-00000024', 'pagado', '2026-10-15 22:30:39'),
(15, 25, 2, 9.60, 0.00, 0.00, 0.00, 9.60, 'boleta', 'B001-00000025', 'pagado', '2026-10-15 22:31:23'),
(16, 26, 2, 14.20, 7.00, 0.00, 0.00, 21.20, 'boleta', 'B001-00000026', 'pagado', '2026-10-16 01:38:28'),
(17, 27, 2, 4.50, 0.00, 0.00, 0.00, 4.50, 'boleta', 'B001-00000027', 'pagado', '2026-10-16 01:49:29'),
(18, 28, 2, 3.20, 7.00, 0.00, 0.00, 10.20, 'boleta', 'B001-00000028', 'pagado', '2026-10-16 13:54:23'),
(19, 29, 2, 18.50, 7.00, 0.00, 0.00, 25.50, 'boleta', 'B001-00000029', 'pagado', '2026-10-16 14:15:07'),
(20, 30, 2, 4.50, 7.00, 0.00, 0.00, 11.50, 'boleta', 'B001-00000030', 'pagado', '2026-10-16 14:48:19');

--
-- Índices para tablas volcadas
--

--
-- Indices de la tabla `categoria`
--
ALTER TABLE `categoria`
  ADD PRIMARY KEY (`id_categoria`),
  ADD KEY `fk_cat_momento` (`id_momento`),
  ADD KEY `fk_cat_grupo` (`id_grupo`);

--
-- Indices de la tabla `configuracion_correo`
--
ALTER TABLE `configuracion_correo`
  ADD PRIMARY KEY (`id_config`);

--
-- Indices de la tabla `detalle_pedido`
--
ALTER TABLE `detalle_pedido`
  ADD PRIMARY KEY (`id_detalle`),
  ADD KEY `fk_det_pedido` (`id_pedido`),
  ADD KEY `fk_det_producto` (`id_producto`);

--
-- Indices de la tabla `direccion`
--
ALTER TABLE `direccion`
  ADD PRIMARY KEY (`id_direccion`),
  ADD KEY `fk_dir_usuario` (`id_usuario`);

--
-- Indices de la tabla `distrito_delivery`
--
ALTER TABLE `distrito_delivery`
  ADD PRIMARY KEY (`id_distrito`);

--
-- Indices de la tabla `envio_delivery`
--
ALTER TABLE `envio_delivery`
  ADD PRIMARY KEY (`id_envio`),
  ADD UNIQUE KEY `id_pedido` (`id_pedido`),
  ADD KEY `fk_envio_repartidor` (`id_repartidor`);

--
-- Indices de la tabla `grupo_momento`
--
ALTER TABLE `grupo_momento`
  ADD PRIMARY KEY (`id_grupo`),
  ADD KEY `fk_gm_momento` (`id_momento`);

--
-- Indices de la tabla `inventario`
--
ALTER TABLE `inventario`
  ADD PRIMARY KEY (`id_inventario`),
  ADD UNIQUE KEY `id_producto` (`id_producto`);

--
-- Indices de la tabla `kardex`
--
ALTER TABLE `kardex`
  ADD PRIMARY KEY (`id_kardex`),
  ADD KEY `fk_kardex_usuario` (`id_usuario`),
  ADD KEY `idx_kardex_producto_fecha` (`id_producto`,`fecha`);

--
-- Indices de la tabla `metodo_pago`
--
ALTER TABLE `metodo_pago`
  ADD PRIMARY KEY (`id_metodo`);

--
-- Indices de la tabla `momento_consumo`
--
ALTER TABLE `momento_consumo`
  ADD PRIMARY KEY (`id_momento`);

--
-- Indices de la tabla `movimiento_caja`
--
ALTER TABLE `movimiento_caja`
  ADD PRIMARY KEY (`id_movimiento`),
  ADD KEY `fk_mov_sesion` (`id_sesion`),
  ADD KEY `fk_mov_usuario` (`id_usuario`);

--
-- Indices de la tabla `pago_verificacion`
--
ALTER TABLE `pago_verificacion`
  ADD PRIMARY KEY (`id_verificacion`),
  ADD KEY `fk_verif_pedido` (`id_pedido`),
  ADD KEY `fk_verif_metodo` (`id_metodo`),
  ADD KEY `fk_verif_usuario` (`id_usuario_verificador`),
  ADD KEY `idx_verif_codigo` (`codigo_operacion`);

--
-- Indices de la tabla `pedido`
--
ALTER TABLE `pedido`
  ADD PRIMARY KEY (`id_pedido`),
  ADD UNIQUE KEY `codigo_pedido` (`codigo_pedido`),
  ADD KEY `fk_ped_usuario` (`id_usuario`),
  ADD KEY `fk_ped_cajero` (`id_cajero`),
  ADD KEY `fk_ped_caja` (`id_sesion_caja`),
  ADD KEY `fk_ped_metodo` (`id_metodo`),
  ADD KEY `fk_ped_direccion` (`id_direccion`),
  ADD KEY `idx_pedido_canal_fecha` (`canal`,`fecha`),
  ADD KEY `idx_pedido_estado` (`estado`);

--
-- Indices de la tabla `pos_escaner_cola`
--
ALTER TABLE `pos_escaner_cola`
  ADD PRIMARY KEY (`id_escaneo`),
  ADD KEY `idx_token_procesado` (`token_sesion`,`procesado`);

--
-- Indices de la tabla `producto`
--
ALTER TABLE `producto`
  ADD PRIMARY KEY (`id_producto`),
  ADD UNIQUE KEY `codigo` (`codigo`),
  ADD UNIQUE KEY `codigo_barras` (`codigo_barras`),
  ADD KEY `fk_prod_categoria` (`id_categoria`),
  ADD KEY `fk_prod_grupo` (`id_grupo`),
  ADD KEY `idx_producto_codigo_barras` (`codigo_barras`),
  ADD KEY `idx_producto_titulo` (`titulo`);

--
-- Indices de la tabla `proveedor`
--
ALTER TABLE `proveedor`
  ADD PRIMARY KEY (`id_proveedor`),
  ADD UNIQUE KEY `ruc` (`ruc`);

--
-- Indices de la tabla `reposicion`
--
ALTER TABLE `reposicion`
  ADD PRIMARY KEY (`id_reposicion`),
  ADD KEY `fk_repo_producto` (`id_producto`),
  ADD KEY `fk_repo_proveedor` (`id_proveedor`),
  ADD KEY `fk_repo_usuario` (`id_usuario`);

--
-- Indices de la tabla `sesion_caja`
--
ALTER TABLE `sesion_caja`
  ADD PRIMARY KEY (`id_sesion`),
  ADD UNIQUE KEY `token_emparejamiento` (`token_emparejamiento`),
  ADD KEY `fk_caja_usuario` (`id_usuario`);

--
-- Indices de la tabla `sys_sessions`
--
ALTER TABLE `sys_sessions`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_session_access` (`last_accessed`);

--
-- Indices de la tabla `usuario`
--
ALTER TABLE `usuario`
  ADD PRIMARY KEY (`id_usuario`),
  ADD UNIQUE KEY `correo` (`correo`),
  ADD UNIQUE KEY `dni` (`dni`),
  ADD UNIQUE KEY `google_id` (`google_id`),
  ADD KEY `idx_usuario_correo` (`correo`),
  ADD KEY `idx_usuario_rol` (`rol`);

--
-- Indices de la tabla `venta`
--
ALTER TABLE `venta`
  ADD PRIMARY KEY (`id_venta`),
  ADD KEY `idx_venta_pedido` (`id_pedido`);

--
-- AUTO_INCREMENT de las tablas volcadas
--

--
-- AUTO_INCREMENT de la tabla `categoria`
--
ALTER TABLE `categoria`
  MODIFY `id_categoria` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=37;

--
-- AUTO_INCREMENT de la tabla `configuracion_correo`
--
ALTER TABLE `configuracion_correo`
  MODIFY `id_config` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT de la tabla `detalle_pedido`
--
ALTER TABLE `detalle_pedido`
  MODIFY `id_detalle` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=36;

--
-- AUTO_INCREMENT de la tabla `direccion`
--
ALTER TABLE `direccion`
  MODIFY `id_direccion` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=9;

--
-- AUTO_INCREMENT de la tabla `distrito_delivery`
--
ALTER TABLE `distrito_delivery`
  MODIFY `id_distrito` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=15;

--
-- AUTO_INCREMENT de la tabla `envio_delivery`
--
ALTER TABLE `envio_delivery`
  MODIFY `id_envio` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=11;

--
-- AUTO_INCREMENT de la tabla `grupo_momento`
--
ALTER TABLE `grupo_momento`
  MODIFY `id_grupo` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=16;

--
-- AUTO_INCREMENT de la tabla `inventario`
--
ALTER TABLE `inventario`
  MODIFY `id_inventario` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=41;

--
-- AUTO_INCREMENT de la tabla `kardex`
--
ALTER TABLE `kardex`
  MODIFY `id_kardex` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=34;

--
-- AUTO_INCREMENT de la tabla `metodo_pago`
--
ALTER TABLE `metodo_pago`
  MODIFY `id_metodo` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT de la tabla `momento_consumo`
--
ALTER TABLE `momento_consumo`
  MODIFY `id_momento` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT de la tabla `movimiento_caja`
--
ALTER TABLE `movimiento_caja`
  MODIFY `id_movimiento` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT de la tabla `pago_verificacion`
--
ALTER TABLE `pago_verificacion`
  MODIFY `id_verificacion` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=11;

--
-- AUTO_INCREMENT de la tabla `pedido`
--
ALTER TABLE `pedido`
  MODIFY `id_pedido` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=31;

--
-- AUTO_INCREMENT de la tabla `pos_escaner_cola`
--
ALTER TABLE `pos_escaner_cola`
  MODIFY `id_escaneo` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=251;

--
-- AUTO_INCREMENT de la tabla `producto`
--
ALTER TABLE `producto`
  MODIFY `id_producto` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=35;

--
-- AUTO_INCREMENT de la tabla `proveedor`
--
ALTER TABLE `proveedor`
  MODIFY `id_proveedor` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT de la tabla `reposicion`
--
ALTER TABLE `reposicion`
  MODIFY `id_reposicion` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT de la tabla `sesion_caja`
--
ALTER TABLE `sesion_caja`
  MODIFY `id_sesion` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=13;

--
-- AUTO_INCREMENT de la tabla `usuario`
--
ALTER TABLE `usuario`
  MODIFY `id_usuario` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT de la tabla `venta`
--
ALTER TABLE `venta`
  MODIFY `id_venta` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=21;

--
-- Restricciones para tablas volcadas
--

--
-- Filtros para la tabla `categoria`
--
ALTER TABLE `categoria`
  ADD CONSTRAINT `fk_cat_grupo` FOREIGN KEY (`id_grupo`) REFERENCES `grupo_momento` (`id_grupo`) ON DELETE SET NULL,
  ADD CONSTRAINT `fk_cat_momento` FOREIGN KEY (`id_momento`) REFERENCES `momento_consumo` (`id_momento`) ON DELETE SET NULL;

--
-- Filtros para la tabla `detalle_pedido`
--
ALTER TABLE `detalle_pedido`
  ADD CONSTRAINT `fk_det_pedido` FOREIGN KEY (`id_pedido`) REFERENCES `pedido` (`id_pedido`) ON DELETE CASCADE,
  ADD CONSTRAINT `fk_det_producto` FOREIGN KEY (`id_producto`) REFERENCES `producto` (`id_producto`);

--
-- Filtros para la tabla `direccion`
--
ALTER TABLE `direccion`
  ADD CONSTRAINT `fk_dir_usuario` FOREIGN KEY (`id_usuario`) REFERENCES `usuario` (`id_usuario`) ON DELETE CASCADE;

--
-- Filtros para la tabla `envio_delivery`
--
ALTER TABLE `envio_delivery`
  ADD CONSTRAINT `fk_envio_pedido` FOREIGN KEY (`id_pedido`) REFERENCES `pedido` (`id_pedido`) ON DELETE CASCADE,
  ADD CONSTRAINT `fk_envio_repartidor` FOREIGN KEY (`id_repartidor`) REFERENCES `usuario` (`id_usuario`) ON DELETE SET NULL;

--
-- Filtros para la tabla `grupo_momento`
--
ALTER TABLE `grupo_momento`
  ADD CONSTRAINT `fk_gm_momento` FOREIGN KEY (`id_momento`) REFERENCES `momento_consumo` (`id_momento`) ON DELETE CASCADE;

--
-- Filtros para la tabla `inventario`
--
ALTER TABLE `inventario`
  ADD CONSTRAINT `fk_inv_producto` FOREIGN KEY (`id_producto`) REFERENCES `producto` (`id_producto`) ON DELETE CASCADE;

--
-- Filtros para la tabla `kardex`
--
ALTER TABLE `kardex`
  ADD CONSTRAINT `fk_kardex_producto` FOREIGN KEY (`id_producto`) REFERENCES `producto` (`id_producto`),
  ADD CONSTRAINT `fk_kardex_usuario` FOREIGN KEY (`id_usuario`) REFERENCES `usuario` (`id_usuario`);

--
-- Filtros para la tabla `movimiento_caja`
--
ALTER TABLE `movimiento_caja`
  ADD CONSTRAINT `fk_mov_sesion` FOREIGN KEY (`id_sesion`) REFERENCES `sesion_caja` (`id_sesion`) ON DELETE CASCADE,
  ADD CONSTRAINT `fk_mov_usuario` FOREIGN KEY (`id_usuario`) REFERENCES `usuario` (`id_usuario`);

--
-- Filtros para la tabla `pago_verificacion`
--
ALTER TABLE `pago_verificacion`
  ADD CONSTRAINT `fk_verif_metodo` FOREIGN KEY (`id_metodo`) REFERENCES `metodo_pago` (`id_metodo`),
  ADD CONSTRAINT `fk_verif_pedido` FOREIGN KEY (`id_pedido`) REFERENCES `pedido` (`id_pedido`) ON DELETE CASCADE,
  ADD CONSTRAINT `fk_verif_usuario` FOREIGN KEY (`id_usuario_verificador`) REFERENCES `usuario` (`id_usuario`) ON DELETE SET NULL;

--
-- Filtros para la tabla `pedido`
--
ALTER TABLE `pedido`
  ADD CONSTRAINT `fk_ped_caja` FOREIGN KEY (`id_sesion_caja`) REFERENCES `sesion_caja` (`id_sesion`) ON DELETE SET NULL,
  ADD CONSTRAINT `fk_ped_cajero` FOREIGN KEY (`id_cajero`) REFERENCES `usuario` (`id_usuario`) ON DELETE SET NULL,
  ADD CONSTRAINT `fk_ped_direccion` FOREIGN KEY (`id_direccion`) REFERENCES `direccion` (`id_direccion`) ON DELETE SET NULL,
  ADD CONSTRAINT `fk_ped_metodo` FOREIGN KEY (`id_metodo`) REFERENCES `metodo_pago` (`id_metodo`) ON DELETE SET NULL,
  ADD CONSTRAINT `fk_ped_usuario` FOREIGN KEY (`id_usuario`) REFERENCES `usuario` (`id_usuario`);

--
-- Filtros para la tabla `producto`
--
ALTER TABLE `producto`
  ADD CONSTRAINT `fk_prod_categoria` FOREIGN KEY (`id_categoria`) REFERENCES `categoria` (`id_categoria`) ON DELETE SET NULL,
  ADD CONSTRAINT `fk_prod_grupo` FOREIGN KEY (`id_grupo`) REFERENCES `grupo_momento` (`id_grupo`) ON DELETE SET NULL;

--
-- Filtros para la tabla `reposicion`
--
ALTER TABLE `reposicion`
  ADD CONSTRAINT `fk_repo_producto` FOREIGN KEY (`id_producto`) REFERENCES `producto` (`id_producto`),
  ADD CONSTRAINT `fk_repo_proveedor` FOREIGN KEY (`id_proveedor`) REFERENCES `proveedor` (`id_proveedor`) ON DELETE SET NULL,
  ADD CONSTRAINT `fk_repo_usuario` FOREIGN KEY (`id_usuario`) REFERENCES `usuario` (`id_usuario`);

--
-- Filtros para la tabla `sesion_caja`
--
ALTER TABLE `sesion_caja`
  ADD CONSTRAINT `fk_caja_usuario` FOREIGN KEY (`id_usuario`) REFERENCES `usuario` (`id_usuario`);
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
