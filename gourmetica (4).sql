SET FOREIGN_KEY_CHECKS=0;

-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Servidor: 127.0.0.1
-- Tiempo de generación: 19-06-2026 a las 01:41:48
-- Versión del servidor: 10.4.32-MariaDB
-- Versión de PHP: 8.2.12

SET FOREIGN_KEY_CHECKS=0;
SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Base de datos: `gourmetica`
--

--
-- Volcado de datos para la tabla `banners`
--

INSERT INTO `banners` (`id`, `title`, `subtitle`, `button_text`, `button_url`, `image_path`, `type`, `is_active`, `order_index`, `created_at`, `updated_at`) VALUES
(1, 'EL ARTE DE LA\nPASTELERÍA FINA', 'Sabores únicos creados con insumos de primera calidad y pasión artesanal', 'Ver Carta', '/shop', 'banners/hero_pastry.png', 'hero', 1, 0, '2026-05-27 08:55:23', '2026-05-27 08:55:23'),
(2, 'DULCES MOMENTOS\nPARA COMPARTIR', 'Descubre nuestra selección especial de tortas de diseño y bocaditos gourmet', 'Hacer Pedido', '/shop', 'banners/hero_celebration.png', 'hero', 1, 1, '2026-05-27 08:55:23', '2026-05-27 08:55:23'),
(3, '¡20% de descuento en tu primer pedido con el cupón DULCEBIENVENIDA!', NULL, 'Canjear Cupón', '/shop', 'banners/promo_discount.png', 'promo', 1, 0, '2026-05-27 08:55:23', '2026-05-27 08:55:23'),
(4, 'Servicio Catering', 'Haz de tus celebraciones momentos inolvidables con nuestra selección exclusiva de bocaditos y tortas.', 'Coordina tu evento', '/catering', 'banners/service_catering.png', 'service_catering', 1, 0, '2026-05-27 08:55:23', '2026-05-27 08:55:23'),
(5, 'Pedidos Corporativos', 'Sorprende a tu equipo y clientes con el detalle más dulce. Soluciones personalizadas para empresas.', 'Coordina tu pedido', '/corporate', 'banners/service_corporate.png', 'service_corporate', 1, 0, '2026-05-27 08:55:23', '2026-05-27 08:55:23'),
(6, 'Fondo Nuestras Casas', 'Tenemos más de 10 casas listas para recibirte y endulzar tu día.', 'VER NUESTRAS CASAS', '/locations', 'banners/section_locations.png', 'section_locations', 1, 0, '2026-05-27 08:55:23', '2026-05-27 08:55:23');

--
-- Volcado de datos para la tabla `categories`
--

INSERT INTO `categories` (`id`, `name`, `slug`, `image`, `created_at`, `updated_at`) VALUES
(1, 'Postres', 'postres', NULL, '2026-05-27 08:47:13', '2026-05-27 08:47:13'),
(2, 'Combos y Promociones', 'combos-y-promociones', NULL, '2026-05-27 08:47:13', '2026-05-27 08:47:13'),
(3, 'Boxes y Regalos', 'boxes-y-regalos', NULL, '2026-05-27 08:47:13', '2026-05-27 08:47:13'),
(4, 'Postres en Frasco y Parfaits', 'postres-en-frasco-y-parfaits', NULL, '2026-05-27 08:47:14', '2026-05-27 08:47:14'),
(5, 'Cheesecakes Especiales', 'cheesecakes-especiales', NULL, '2026-05-27 08:47:14', '2026-05-27 08:47:14'),
(6, 'Tortas y Postres', 'tortas-y-postres', NULL, '2026-05-27 08:47:14', '2026-05-27 08:47:14'),
(7, 'Bocaditos y Galletas', 'bocaditos-y-galletas', NULL, '2026-05-27 08:47:14', '2026-05-27 08:47:14');

--
-- Volcado de datos para la tabla `headquarters`
--

INSERT INTO `headquarters` (`id`, `name`, `address`, `city`, `phone`, `email`, `latitude`, `longitude`, `is_active`, `created_at`, `updated_at`) VALUES
(1, 'Sede Central Lima', 'Av. Arequipa 1234', 'Lima', '01 4445556', 'lima@gourmetica.com', NULL, NULL, 1, '2026-05-27 08:47:12', '2026-05-27 08:47:12'),
(2, 'Chiclayo', 'Torres Paz 574, Chiclayo 14001, Perú', 'Chiclayo', '943394206', 'admin@gourmetica.com', -6.77359756, -79.83957836, 1, '2026-06-03 04:07:13', '2026-06-10 06:55:02');

--
-- Volcado de datos para la tabla `headquarter_product`
--

INSERT INTO `headquarter_product` (`id`, `headquarter_id`, `product_id`, `stock`, `price`, `created_at`, `updated_at`) VALUES
(1, 1, 3, 50, NULL, '2026-05-27 08:47:13', '2026-05-27 08:47:13'),
(2, 1, 4, 50, NULL, '2026-05-27 08:47:13', '2026-05-27 08:47:13'),
(3, 1, 5, 50, NULL, '2026-05-27 08:47:13', '2026-05-27 08:47:13'),
(4, 1, 6, 50, NULL, '2026-05-27 08:47:13', '2026-05-27 08:47:13'),
(5, 1, 7, 50, NULL, '2026-05-27 08:47:13', '2026-05-27 08:47:13'),
(6, 1, 8, 50, NULL, '2026-05-27 08:47:13', '2026-05-27 08:47:13'),
(7, 1, 9, 50, NULL, '2026-05-27 08:47:13', '2026-05-27 08:47:13'),
(8, 1, 10, 50, NULL, '2026-05-27 08:47:13', '2026-05-27 08:47:13'),
(9, 1, 11, 50, NULL, '2026-05-27 08:47:13', '2026-05-27 08:47:13'),
(10, 1, 12, 50, NULL, '2026-05-27 08:47:13', '2026-05-27 08:47:13'),
(11, 1, 13, 50, NULL, '2026-05-27 08:47:13', '2026-05-27 08:47:13'),
(12, 1, 14, 50, NULL, '2026-05-27 08:47:13', '2026-05-27 08:47:13'),
(13, 1, 15, 50, NULL, '2026-05-27 08:47:13', '2026-05-27 08:47:13'),
(14, 1, 16, 50, NULL, '2026-05-27 08:47:13', '2026-05-27 08:47:13'),
(15, 1, 17, 50, NULL, '2026-05-27 08:47:13', '2026-05-27 08:47:13'),
(16, 1, 18, 50, NULL, '2026-05-27 08:47:13', '2026-05-27 08:47:13'),
(17, 1, 19, 50, NULL, '2026-05-27 08:47:13', '2026-05-27 08:47:13'),
(18, 1, 20, 50, NULL, '2026-05-27 08:47:14', '2026-05-27 08:47:14'),
(19, 1, 21, 50, NULL, '2026-05-27 08:47:14', '2026-05-27 08:47:14'),
(20, 1, 22, 50, NULL, '2026-05-27 08:47:14', '2026-05-27 08:47:14'),
(21, 1, 23, 50, NULL, '2026-05-27 08:47:14', '2026-05-27 08:47:14'),
(22, 1, 24, 50, NULL, '2026-05-27 08:47:14', '2026-05-27 08:47:14'),
(23, 1, 25, 50, NULL, '2026-05-27 08:47:14', '2026-05-27 08:47:14'),
(24, 1, 26, 50, NULL, '2026-05-27 08:47:14', '2026-05-27 08:47:14'),
(25, 1, 27, 50, NULL, '2026-05-27 08:47:14', '2026-05-27 08:47:14'),
(26, 1, 28, 50, NULL, '2026-05-27 08:47:14', '2026-05-27 08:47:14'),
(27, 1, 29, 50, NULL, '2026-05-27 08:47:14', '2026-05-27 08:47:14'),
(28, 1, 30, 50, NULL, '2026-05-27 08:47:14', '2026-05-27 08:47:14'),
(29, 1, 31, 50, NULL, '2026-05-27 08:47:14', '2026-05-27 08:47:14'),
(30, 1, 32, 50, NULL, '2026-05-27 08:47:14', '2026-05-27 08:47:14'),
(31, 1, 33, 50, NULL, '2026-05-27 08:47:14', '2026-05-27 08:47:14'),
(32, 1, 34, 50, NULL, '2026-05-27 08:47:14', '2026-05-27 08:47:14'),
(33, 1, 35, 50, NULL, '2026-05-27 08:47:14', '2026-05-27 08:47:14'),
(34, 1, 36, 50, NULL, '2026-05-27 08:47:14', '2026-05-27 08:47:14'),
(35, 1, 37, 50, NULL, '2026-05-27 08:47:14', '2026-05-27 08:47:14'),
(36, 1, 38, 50, NULL, '2026-05-27 08:47:14', '2026-05-27 08:47:14'),
(37, 1, 39, 50, NULL, '2026-05-27 08:47:14', '2026-05-27 08:47:14'),
(38, 1, 40, 50, NULL, '2026-05-27 08:47:14', '2026-05-27 08:47:14'),
(39, 1, 41, 50, NULL, '2026-05-27 08:47:14', '2026-05-27 08:47:14'),
(40, 1, 42, 50, NULL, '2026-05-27 08:47:14', '2026-05-27 08:47:14'),
(41, 1, 43, 50, NULL, '2026-05-27 08:47:14', '2026-05-27 08:47:14'),
(45, 1, 1, 0, 45.00, '2026-06-10 08:19:06', '2026-06-10 22:09:42'),
(46, 2, 1, 0, 45.00, '2026-06-10 08:19:06', '2026-06-10 23:37:34');

--
-- Volcado de datos para la tabla `headquarter_supply`
--

INSERT INTO `headquarter_supply` (`id`, `headquarter_id`, `supply_id`, `stock`, `created_at`, `updated_at`) VALUES
(4, 1, 4, 1.0000, '2026-06-10 08:12:46', '2026-06-10 08:12:46'),
(5, 1, 5, 5.0000, '2026-06-10 08:12:46', '2026-06-10 08:12:46'),
(6, 1, 6, 6.0000, '2026-06-10 08:12:46', '2026-06-10 08:12:46'),
(7, 1, 7, 1.0000, '2026-06-10 08:12:46', '2026-06-10 08:12:46'),
(8, 1, 8, 5000.0000, '2026-06-10 08:12:46', '2026-06-10 08:12:46'),
(9, 1, 9, 3000.0000, '2026-06-10 08:12:46', '2026-06-10 08:12:46'),
(10, 1, 10, 1000.0000, '2026-06-10 08:12:46', '2026-06-10 08:12:46'),
(11, 1, 11, 500.0000, '2026-06-10 08:12:46', '2026-06-10 08:12:46'),
(12, 1, 12, 1.0000, '2026-06-10 08:12:46', '2026-06-10 08:12:46'),
(13, 1, 13, 500.0000, '2026-06-10 08:12:46', '2026-06-10 08:12:46'),
(14, 1, 14, 250.0000, '2026-06-10 08:12:46', '2026-06-10 08:12:46'),
(15, 1, 15, 1000.0000, '2026-06-10 08:12:46', '2026-06-10 08:12:46'),
(16, 1, 16, 2000.0000, '2026-06-10 08:12:46', '2026-06-10 08:12:46'),
(17, 1, 17, 1000.0000, '2026-06-10 08:12:46', '2026-06-10 08:12:46');

--
-- Volcado de datos para la tabla `migrations`
--

INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES
(1, '0001_01_01_000000_create_users_table', 1),
(2, '0001_01_01_000001_create_cache_table', 1),
(3, '0001_01_01_000002_create_jobs_table', 1),
(4, '2026_05_13_054816_create_headquarters_table', 1),
(5, '2026_05_13_054816_create_roles_table', 1),
(6, '2026_05_13_054817_update_users_table_for_rbac_and_headquarters', 1),
(7, '2026_05_13_054818_create_categories_table', 1),
(8, '2026_05_13_054818_create_products_table', 1),
(9, '2026_05_13_054819_create_headquarter_product_table', 1),
(10, '2026_05_13_055252_create_purchases_table', 1),
(11, '2026_05_13_055252_create_sales_table', 1),
(12, '2026_05_13_055612_create_audits_table', 1),
(13, '2026_05_13_060220_create_orders_table', 1),
(14, '2026_05_13_060222_create_order_items_table', 1),
(15, '2026_05_13_233439_create_settings_table', 1),
(16, '2026_05_14_001436_create_product_options_tables', 1),
(17, '2026_05_14_002546_update_orders_and_items_for_checkout', 1),
(18, '2026_05_14_003338_add_google_id_to_users_table', 1),
(19, '2026_05_14_014145_create_favorites_table', 1),
(20, '2026_05_14_154219_add_slug_to_products_table', 1),
(21, '2026_05_14_154717_fix_double_encoded_options_in_order_items', 1),
(22, '2026_05_16_032103_add_image_to_categories_and_create_banners_table', 1),
(23, '2026_05_16_234157_create_catering_requests_table', 1),
(24, '2026_05_17_035802_add_order_id_to_sales_table', 1),
(25, '2026_05_17_214500_add_table_number_to_sales_table', 1),
(26, '2026_05_17_222500_create_delivery_zones_table', 1),
(27, '2026_05_17_222600_update_orders_table_for_delivery', 1),
(28, '2026_05_17_224500_add_coordinates_to_delivery_zones_table', 1),
(29, '2026_06_06_181410_add_coordinates_to_headquarters_table', 2),
(30, '2026_06_09_000000_add_nakama_fields_to_orders_table', 3),
(31, '2026_06_10_000000_create_supplies_and_recipes_tables', 4),
(32, '2026_06_10_000001_add_stock_decremented_to_orders_table', 5),
(33, '2026_06_10_173346_add_phone_to_users_table', 6),
(34, '2026_06_10_174454_add_nakama_status_to_orders_table', 7),
(35, '2026_06_10_181809_add_customer_name_to_orders_table', 8);

--
-- Volcado de datos para la tabla `products`
--

INSERT INTO `products` (`id`, `category_id`, `name`, `slug`, `description`, `base_price`, `image`, `is_active`, `created_at`, `updated_at`) VALUES
(1, 1, 'Torta de Chocolate', 'torta-de-chocolate', 'Nuestra clásica torta de chocolate con fudge artesanal.', 45.00, NULL, 1, '2026-05-27 08:47:13', '2026-06-10 08:19:55'),
(2, 1, 'Cupcake de Vainilla', 'cupcake-de-vainilla', 'Suave cupcake de vainilla con frosting de crema.', 8.50, NULL, 1, '2026-05-27 08:47:13', '2026-05-27 08:47:13'),
(3, 2, 'Promo Jars', 'promo-jars', '3 Cheesecake\'s jar a elección con un precio especial. Sabores disponibles: Fresa, Arándanos, Maracuyá, Chocomaní y Chocoavellanas', 39.00, NULL, 1, '2026-05-27 08:47:13', '2026-05-27 08:47:13'),
(4, 2, 'PROMO Porciones Saludables', 'promo-porciones-saludables', '3 porciones saludables, sabores a elección según stock disponible', 46.00, NULL, 1, '2026-05-27 08:47:13', '2026-05-27 08:47:13'),
(5, 2, 'Trío Perfecto', 'trio-perfecto', '1 Cheesecake\'s Jar sabor a elección + 1 FIT Snickers + 1 Súper alfajor', 35.00, NULL, 1, '2026-05-27 08:47:13', '2026-05-27 08:47:13'),
(6, 2, 'DeliPack (Chocotorta)', 'delipack-chocotorta', '1 Chocotorta de quinoa + 6 cookies cacaochips', 23.00, NULL, 1, '2026-05-27 08:47:13', '2026-05-27 08:47:13'),
(7, 2, 'DeliDuo (Carrot Cake)', 'deliduo-carrot-cake', '1 porción Fit Carrot Cake + 4 trufas de frutos secos', 19.90, NULL, 1, '2026-05-27 08:47:13', '2026-05-27 08:47:13'),
(8, 2, 'DeliDuo (Chocotorta)', 'deliduo-chocotorta', '1 Chocotorta de quinoa + 4 trufas de frutos secos', 19.90, NULL, 1, '2026-05-27 08:47:13', '2026-05-27 08:47:13'),
(9, 2, 'DeliPack (Carrot Cake)', 'delipack-carrot-cake', '1 Fit Carrot Cake + 6 cookies cacaochips', 23.00, NULL, 1, '2026-05-27 08:47:13', '2026-05-27 08:47:13'),
(10, 3, 'Box Bocaditos Saludables 25un. 🎀', 'box-bocaditos-saludables-25un', '25un bocaditos decorados según imagen:\n\n5 mini blondies de almendras\n5 trufas de frutos secos\n5 alfajorcitos de almendras\n5 mini brownies de cacao\n5 cookies cacaochips\n\nIncluye: Box, Lazo, Tarjeta y Dedicatoria 🎁', 60.00, NULL, 1, '2026-05-27 08:47:13', '2026-05-27 08:47:13'),
(11, 3, 'BOX Trufas de frutos secos 16un', 'box-trufas-de-frutos-secos-16un', '16un. de nuestras clásicas trufas de frutos secos\n\nIncluye: Box, Lazo, Tarjeta y Dedicatoria 🎁', 40.00, NULL, 1, '2026-05-27 08:47:13', '2026-05-27 08:47:13'),
(12, 3, 'BOX Alfajorcitos corazón de almendras 16un.', 'box-alfajorcitos-corazon-de-almendras-16un', '16un de nuestros clásicos alfajorcitos de almendras, relleno a elección: Manjar de panela o Chocomaní o mixto\n\nIncluye: Box, Lazo, Tarjeta y Dedicatoria 🎁', 38.00, NULL, 1, '2026-05-27 08:47:13', '2026-05-27 08:47:13'),
(13, 3, 'BOX Bocaditos Saludables clásicos 16un.', 'box-bocaditos-saludables-clasicos-16un', '4 mini blondies de almendras\n4 trufas de frutos secos\n4 alfajorcitos de almendras\n4 mini brownies de cacao\n\nIncluye: Box, Lazo, Tarjeta y Dedicatoria 🎁', 38.00, NULL, 1, '2026-05-27 08:47:13', '2026-05-27 08:47:13'),
(14, 3, 'Box Bocaditos Saludables 16un. 🌀', 'box-bocaditos-saludables-16un', '16un bocaditos decorados según imagen:\n\n4 mini blondies de almendras\n4 trufas de frutos secos\n4 alfajorcitos de almendras\n4 mini brownies de cacao\n\nIncluye: Box, Lazo, Tarjeta y Dedicatoria 🎁', 38.00, NULL, 1, '2026-05-27 08:47:13', '2026-05-27 08:47:13'),
(15, 3, 'Box Bocaditos saludable 🌼', 'box-bocaditos-saludable', '16un bocaditos decorados según imagen:\n\n4 mini blondies de almendras\n4 trufas de frutos secos\n4 alfajorcitos de almendras\n4 mini brownies de cacao\n\nIncluye: Box, Lazo, Tarjeta y Dedicatoria 🎁', 38.00, NULL, 1, '2026-05-27 08:47:13', '2026-05-27 08:47:13'),
(16, 3, 'Box Bocaditos Saludables 16un 🌸', 'box-bocaditos-saludables-16un-2', '16un bocaditos decorados según imagen:\n\n4 mini blondies de almendras\n4 trufas de frutos secos\n4 alfajorcitos de almendras\n4 mini brownies de cacao\n\nIncluye: Box, Lazo, Tarjeta y Dedicatoria 🎁', 38.00, NULL, 1, '2026-05-27 08:47:13', '2026-05-27 08:47:13'),
(17, 3, 'GIFT BOX I love You', 'gift-box-i-love-you', '1 fit snickers\n1 súper alfajor\n1 cheesecake\'s jar sabor a elección\n1 brownie o blondie a elección\n3 cookies cacaochips\n\nIncluye: Box, Lazo, Tarjeta y Dedicatoria 🎁.', 58.00, NULL, 1, '2026-05-27 08:47:13', '2026-05-27 08:47:13'),
(18, 3, 'GIFT BOX My Love', 'gift-box-my-love', '1 fit snickers\n1 brownie o blondie a elección\n1 cheesecake\'s jar sabor a elección\n3 cookies cacaochips\n\nIncluye: Box, Lazo, Tarjeta y Dedicatoria 🎁.', 48.00, NULL, 1, '2026-05-27 08:47:13', '2026-05-27 08:47:13'),
(19, 3, 'GIFT BOX 3', 'gift-box-3', '1 mantequilla de maní 350gr.\n1 parfait brownie o blondie a elección\n1 brownie o blondie a elección\n3 cookies cacaochips\n\nIncluye: Box, Lazo, Tarjeta y Dedicatoria 🎁.', 63.00, NULL, 1, '2026-05-27 08:47:13', '2026-05-27 08:47:13'),
(20, 3, 'GIFT BOX 1', 'gift-box-1', '1 cheesecake\'s jar sabor a elección\n1 brownie o blondie a elección\n1 súper alfajor bañado en chocolate sin azúcar\n4 cookies cacaochips\n\nIncluye: Box, Lazo, Tarjeta y Dedicatoria 🎁', 45.00, NULL, 1, '2026-05-27 08:47:14', '2026-05-27 08:47:14'),
(21, 3, 'GIFT BOX 2', 'gift-box-2', '1 parfait brownie o blondie a elección\n1 cheesecake jar sabor a elección\n1 brownie o blondie a elección\n3 cookies cacaochips\n\nIncluye: Box, Lazo, Tarjeta y Dedicatoria 🎁', 51.00, NULL, 1, '2026-05-27 08:47:14', '2026-05-27 08:47:14'),
(22, 4, 'Cheesecake\'s Jar (sabor a elegir)', 'cheesecakes-jar-sabor-a-elegir', 'Avena, aceite de coco, queso descremado, endulzados con panela. Sabores disponibles: Fresa, Arándanos, Maracuyá, Chocomani y Chocoavellanas.', 14.00, NULL, 1, '2026-05-27 08:47:14', '2026-05-27 08:47:14'),
(23, 4, 'Parfait Blondie o Brownie', 'parfait-blondie-o-brownie', 'Yogurt griego, granola artesanal de frutos secos, 2 frutas a elección, brownies o blondies en trozos, 1 topping de miel o mantequilla de maní.\n\n1 Frasco 400gr.', 22.00, NULL, 1, '2026-05-27 08:47:14', '2026-05-27 08:47:14'),
(24, 5, 'Cheesecake Oreo', 'cheesecake-oreo', 'Avena, cacao, aceite de coco, yogurt griego, queso descremado, endulzo con panela, decorado con fudge de cacao y trozos de nuestra galleta artesanal de cacao y avena.', 17.00, NULL, 1, '2026-05-27 08:47:14', '2026-05-27 08:47:14'),
(25, 5, 'Cheesecake de mango', 'cheesecake-de-mango', 'Avena, almendras, canela, aceite de coco, yogurt griego, queso descremado, endulzado con panela, decorado con salsa y trozos de mango.', 17.00, NULL, 1, '2026-05-27 08:47:14', '2026-05-27 08:47:14'),
(26, 5, 'Cheesecake Alfajor', 'cheesecake-alfajor', 'Avena, almendras, canela, aceite de coco, yogurt griego, queso descremado, endulzo con panela, bañado en manjar de panela y decorado con un alfajorcito de almendras.', 17.00, NULL, 1, '2026-05-27 08:47:14', '2026-05-27 08:47:14'),
(27, 5, 'Cheesecake de Pecanas', 'cheesecake-de-pecanas', 'Hojuela avena, aceite de coco, queso descremado, caramelo de pecanas, endulzado con panela.', 17.00, NULL, 1, '2026-05-27 08:47:14', '2026-05-27 08:47:14'),
(28, 5, 'Cheesecake Blondie', 'cheesecake-blondie', 'Harina de almendras, aceite de coco, queso descremado, caramelo de nueces, endulzado con panela.', 17.00, NULL, 1, '2026-05-27 08:47:14', '2026-05-27 08:47:14'),
(29, 6, 'Húracan de lúcuma', 'huracan-de-lucuma', 'Trozos de nuestros clásicos brownies de cacao, con crema de lúcuma (pulpa de lúcuma, manjar de panela) libre de glúten, contiene lácteos.', 16.00, NULL, 1, '2026-05-27 08:47:14', '2026-05-27 08:47:14'),
(30, 6, 'Tres leches de almendras', 'tres-leches-de-almendras', 'Harina pura de almendras, leche vegetal, endulzada con panela, decorada con frosting descremado y canela. (Libre de glúten/Keto)', 14.00, NULL, 1, '2026-05-27 08:47:14', '2026-05-27 08:47:14'),
(31, 6, 'Volcán de lúcuma', 'volcan-de-lucuma', 'Nuestra clásica Chocotorta de quinoa, con manjar de lucúma (pulpa de lúcuma, leche descremada, panela) libre de glúten, contiene lácteos.', 17.00, NULL, 1, '2026-05-27 08:47:14', '2026-05-27 08:47:14'),
(32, 6, 'Crema Volteada', 'crema-volteada', 'Leche vegetal de coco, huevo, panela. (Sin lácteos, Sin glúten)', 18.00, NULL, 1, '2026-05-27 08:47:14', '2026-05-27 08:47:14'),
(33, 6, 'Red Velvet', 'red-velvet', 'Harina de almendras, quinoa, tinte vegetal, aceite de oliva extra virgen, leche vegetal, endulzada con panela, rellena de manjar de panela y bañada en frosting descremado. Decorada con arándanos/fresa o almendras.', 17.00, NULL, 1, '2026-05-27 08:47:14', '2026-05-27 08:47:14'),
(34, 6, 'FIT Carrot Cake', 'fit-carrot-cake', 'Harina integral, zanahoria, pecanas, aceite oliva extra virgen, endulzada con panela, rellena y bañada en frosting descremado.', 16.00, NULL, 1, '2026-05-27 08:47:14', '2026-05-27 08:47:14'),
(35, 6, 'Chocotorta de Quinoa', 'chocotorta-de-quinoa', 'Harina de quinoa, cacao, leche vegetal, chocolate 55% cacao, endulzada con panela. Libre de glúten y lácteos.', 16.00, NULL, 1, '2026-05-27 08:47:14', '2026-05-27 08:47:14'),
(36, 7, 'Blondie de almendras', 'blondie-de-almendras', 'Harina de almendras, integral, aceite de coco, trozos de nueces, endulzado con panela.', 8.00, NULL, 1, '2026-05-27 08:47:14', '2026-05-27 08:47:14'),
(37, 7, 'Brownie de cacao', 'brownie-de-cacao', 'Cacao, aceite de coco, harina de avena, endulzados con panela, decorado con almendras. Libre de gluten y lácteos.', 8.00, NULL, 1, '2026-05-27 08:47:14', '2026-05-27 08:47:14'),
(38, 7, 'Súper Alfajor', 'super-alfajor', 'Almendras, avena, aceite de coco, relleno de nuestro manjar de panela, bañado en chocolate sin azúcar 55% cacao', 12.00, NULL, 1, '2026-05-27 08:47:14', '2026-05-27 08:47:14'),
(39, 7, 'FIT Snickers', 'fit-snickers', 'Avena, caramel vegano, maní, bañado en chocolate sin azúcar 55% cacao. (Libre de glúten, lácteos y vegano)', 13.00, NULL, 1, '2026-05-27 08:47:14', '2026-05-27 08:47:14'),
(40, 7, 'Súper Cookie rellena doble chocolate', 'super-cookie-rellena-doble-chocolate', 'Avena, cacao, aceite de coco, endulzadas con panela, rellenas de fudge vegetal. (Sin glúten. Sin lácteos, vegana)', 12.00, NULL, 1, '2026-05-27 08:47:14', '2026-05-27 08:47:14'),
(41, 7, 'Cookies cacaochips 6un.', 'cookies-cacaochips-6un', 'Harina integral, aceite de coco, chispas de cacao, endulzadas con panela.', 12.00, NULL, 1, '2026-05-27 08:47:14', '2026-05-27 08:47:14'),
(42, 7, 'Trufas de frutos secos 6un.', 'trufas-de-frutos-secos-6un', 'Mantequilla de maní pura, hojuelas de avena, pecanas, semillas de girasol, miel, bañadas en chocolate sin azúcar 55% cacao.', 13.00, NULL, 1, '2026-05-27 08:47:14', '2026-05-27 08:47:14'),
(43, 7, 'Alfajorcitos de almendras 6un.', 'alfajorcitos-de-almendras-6un', 'Almendras, aceite de coco, endulzados con panela, relleno: Manjar de panela o Chocomaní', 12.00, NULL, 1, '2026-05-27 08:47:14', '2026-05-27 08:47:14');

--
-- Volcado de datos para la tabla `product_options`
--

INSERT INTO `product_options` (`id`, `product_id`, `name`, `created_at`, `updated_at`) VALUES
(2, 2, 'Toppings', '2026-05-27 08:47:13', '2026-05-27 08:47:13'),
(3, 3, 'Sabor de Jar 1', '2026-05-27 08:47:13', '2026-05-27 08:47:13'),
(4, 3, 'Sabor de Jar 2', '2026-05-27 08:47:13', '2026-05-27 08:47:13'),
(5, 3, 'Sabor de Jar 3', '2026-05-27 08:47:13', '2026-05-27 08:47:13'),
(6, 12, 'Relleno a elección', '2026-05-27 08:47:13', '2026-05-27 08:47:13'),
(7, 17, 'Sabor de Cheesecake Jar', '2026-05-27 08:47:13', '2026-05-27 08:47:13'),
(8, 17, 'Bizcocho a elección', '2026-05-27 08:47:13', '2026-05-27 08:47:13'),
(9, 18, 'Sabor de Cheesecake Jar', '2026-05-27 08:47:13', '2026-05-27 08:47:13'),
(10, 18, 'Bizcocho a elección', '2026-05-27 08:47:13', '2026-05-27 08:47:13'),
(11, 19, 'Parfait a elección', '2026-05-27 08:47:13', '2026-05-27 08:47:13'),
(12, 19, 'Bizcocho a elección', '2026-05-27 08:47:13', '2026-05-27 08:47:13'),
(13, 20, 'Sabor de Cheesecake Jar', '2026-05-27 08:47:14', '2026-05-27 08:47:14'),
(14, 20, 'Bizcocho a elección', '2026-05-27 08:47:14', '2026-05-27 08:47:14'),
(15, 21, 'Parfait a elección', '2026-05-27 08:47:14', '2026-05-27 08:47:14'),
(16, 21, 'Sabor de Cheesecake Jar', '2026-05-27 08:47:14', '2026-05-27 08:47:14'),
(17, 21, 'Bizcocho a elección', '2026-05-27 08:47:14', '2026-05-27 08:47:14'),
(18, 22, 'Sabor de Cheesecake', '2026-05-27 08:47:14', '2026-05-27 08:47:14'),
(19, 23, 'Tipo de Parfait', '2026-05-27 08:47:14', '2026-05-27 08:47:14'),
(20, 23, 'Toppings / Adicional', '2026-05-27 08:47:14', '2026-05-27 08:47:14'),
(21, 33, 'Decoración', '2026-05-27 08:47:14', '2026-05-27 08:47:14'),
(22, 37, 'Presentación', '2026-05-27 08:47:14', '2026-05-27 08:47:14'),
(23, 41, 'Presentación / Cantidad', '2026-05-27 08:47:14', '2026-05-27 08:47:14'),
(24, 42, 'Presentación / Cantidad', '2026-05-27 08:47:14', '2026-05-27 08:47:14'),
(25, 43, 'Relleno', '2026-05-27 08:47:14', '2026-05-27 08:47:14'),
(27, 1, 'Tamaño', '2026-06-10 08:19:55', '2026-06-10 08:19:55');

--
-- Volcado de datos para la tabla `product_option_values`
--

INSERT INTO `product_option_values` (`id`, `product_option_id`, `value`, `price_modifier`, `created_at`, `updated_at`) VALUES
(3, 2, 'Chispas de Chocolate', 1.50, '2026-05-27 08:47:13', '2026-05-27 08:47:13'),
(4, 2, 'Fresas', 2.50, '2026-05-27 08:47:13', '2026-05-27 08:47:13'),
(5, 2, 'Sin Toppings', 0.00, '2026-05-27 08:47:13', '2026-05-27 08:47:13'),
(6, 3, 'Fresa', 0.00, '2026-05-27 08:47:13', '2026-05-27 08:47:13'),
(7, 3, 'Arándanos', 0.00, '2026-05-27 08:47:13', '2026-05-27 08:47:13'),
(8, 3, 'Maracuyá', 0.00, '2026-05-27 08:47:13', '2026-05-27 08:47:13'),
(9, 3, 'Chocomaní', 0.00, '2026-05-27 08:47:13', '2026-05-27 08:47:13'),
(10, 3, 'Chocoavellanas', 0.00, '2026-05-27 08:47:13', '2026-05-27 08:47:13'),
(11, 4, 'Fresa', 0.00, '2026-05-27 08:47:13', '2026-05-27 08:47:13'),
(12, 4, 'Arándanos', 0.00, '2026-05-27 08:47:13', '2026-05-27 08:47:13'),
(13, 4, 'Maracuyá', 0.00, '2026-05-27 08:47:13', '2026-05-27 08:47:13'),
(14, 4, 'Chocomaní', 0.00, '2026-05-27 08:47:13', '2026-05-27 08:47:13'),
(15, 4, 'Chocoavellanas', 0.00, '2026-05-27 08:47:13', '2026-05-27 08:47:13'),
(16, 5, 'Fresa', 0.00, '2026-05-27 08:47:13', '2026-05-27 08:47:13'),
(17, 5, 'Arándanos', 0.00, '2026-05-27 08:47:13', '2026-05-27 08:47:13'),
(18, 5, 'Maracuyá', 0.00, '2026-05-27 08:47:13', '2026-05-27 08:47:13'),
(19, 5, 'Chocomaní', 0.00, '2026-05-27 08:47:13', '2026-05-27 08:47:13'),
(20, 5, 'Chocoavellanas', 0.00, '2026-05-27 08:47:13', '2026-05-27 08:47:13'),
(21, 6, 'Manjar de panela', 0.00, '2026-05-27 08:47:13', '2026-05-27 08:47:13'),
(22, 6, 'Chocomaní', 0.00, '2026-05-27 08:47:13', '2026-05-27 08:47:13'),
(23, 6, 'Mixto', 0.00, '2026-05-27 08:47:13', '2026-05-27 08:47:13'),
(24, 7, 'Fresa', 0.00, '2026-05-27 08:47:13', '2026-05-27 08:47:13'),
(25, 7, 'Arándanos', 0.00, '2026-05-27 08:47:13', '2026-05-27 08:47:13'),
(26, 7, 'Maracuyá', 0.00, '2026-05-27 08:47:13', '2026-05-27 08:47:13'),
(27, 7, 'Chocomaní', 0.00, '2026-05-27 08:47:13', '2026-05-27 08:47:13'),
(28, 7, 'Chocoavellanas', 0.00, '2026-05-27 08:47:13', '2026-05-27 08:47:13'),
(29, 8, 'Brownie de cacao', 0.00, '2026-05-27 08:47:13', '2026-05-27 08:47:13'),
(30, 8, 'Blondie de almendras', 0.00, '2026-05-27 08:47:13', '2026-05-27 08:47:13'),
(31, 9, 'Fresa', 0.00, '2026-05-27 08:47:13', '2026-05-27 08:47:13'),
(32, 9, 'Arándanos', 0.00, '2026-05-27 08:47:13', '2026-05-27 08:47:13'),
(33, 9, 'Maracuyá', 0.00, '2026-05-27 08:47:13', '2026-05-27 08:47:13'),
(34, 9, 'Chocomaní', 0.00, '2026-05-27 08:47:13', '2026-05-27 08:47:13'),
(35, 9, 'Chocoavellanas', 0.00, '2026-05-27 08:47:13', '2026-05-27 08:47:13'),
(36, 10, 'Brownie de cacao', 0.00, '2026-05-27 08:47:13', '2026-05-27 08:47:13'),
(37, 10, 'Blondie de almendras', 0.00, '2026-05-27 08:47:13', '2026-05-27 08:47:13'),
(38, 11, 'Parfait Brownie', 0.00, '2026-05-27 08:47:13', '2026-05-27 08:47:13'),
(39, 11, 'Parfait Blondie', 0.00, '2026-05-27 08:47:13', '2026-05-27 08:47:13'),
(40, 12, 'Brownie de cacao', 0.00, '2026-05-27 08:47:14', '2026-05-27 08:47:14'),
(41, 12, 'Blondie de almendras', 0.00, '2026-05-27 08:47:14', '2026-05-27 08:47:14'),
(42, 13, 'Fresa', 0.00, '2026-05-27 08:47:14', '2026-05-27 08:47:14'),
(43, 13, 'Arándanos', 0.00, '2026-05-27 08:47:14', '2026-05-27 08:47:14'),
(44, 13, 'Maracuyá', 0.00, '2026-05-27 08:47:14', '2026-05-27 08:47:14'),
(45, 13, 'Chocomaní', 0.00, '2026-05-27 08:47:14', '2026-05-27 08:47:14'),
(46, 13, 'Chocoavellanas', 0.00, '2026-05-27 08:47:14', '2026-05-27 08:47:14'),
(47, 14, 'Brownie de cacao', 0.00, '2026-05-27 08:47:14', '2026-05-27 08:47:14'),
(48, 14, 'Blondie de almendras', 0.00, '2026-05-27 08:47:14', '2026-05-27 08:47:14'),
(49, 15, 'Parfait Brownie', 0.00, '2026-05-27 08:47:14', '2026-05-27 08:47:14'),
(50, 15, 'Parfait Blondie', 0.00, '2026-05-27 08:47:14', '2026-05-27 08:47:14'),
(51, 16, 'Fresa', 0.00, '2026-05-27 08:47:14', '2026-05-27 08:47:14'),
(52, 16, 'Arándanos', 0.00, '2026-05-27 08:47:14', '2026-05-27 08:47:14'),
(53, 16, 'Maracuyá', 0.00, '2026-05-27 08:47:14', '2026-05-27 08:47:14'),
(54, 16, 'Chocomaní', 0.00, '2026-05-27 08:47:14', '2026-05-27 08:47:14'),
(55, 16, 'Chocoavellanas', 0.00, '2026-05-27 08:47:14', '2026-05-27 08:47:14'),
(56, 17, 'Brownie de cacao', 0.00, '2026-05-27 08:47:14', '2026-05-27 08:47:14'),
(57, 17, 'Blondie de almendras', 0.00, '2026-05-27 08:47:14', '2026-05-27 08:47:14'),
(58, 18, 'Fresa', 0.00, '2026-05-27 08:47:14', '2026-05-27 08:47:14'),
(59, 18, 'Arándanos', 0.00, '2026-05-27 08:47:14', '2026-05-27 08:47:14'),
(60, 18, 'Maracuyá', 0.00, '2026-05-27 08:47:14', '2026-05-27 08:47:14'),
(61, 18, 'Chocomaní', 0.00, '2026-05-27 08:47:14', '2026-05-27 08:47:14'),
(62, 18, 'Chocoavellanas', 0.00, '2026-05-27 08:47:14', '2026-05-27 08:47:14'),
(63, 19, 'Parfait Brownie', 0.00, '2026-05-27 08:47:14', '2026-05-27 08:47:14'),
(64, 19, 'Parfait Blondie', 0.00, '2026-05-27 08:47:14', '2026-05-27 08:47:14'),
(65, 20, 'Miel', 0.00, '2026-05-27 08:47:14', '2026-05-27 08:47:14'),
(66, 20, 'Mantequilla de maní', 0.00, '2026-05-27 08:47:14', '2026-05-27 08:47:14'),
(67, 21, 'Arándanos y Fresa', 0.00, '2026-05-27 08:47:14', '2026-05-27 08:47:14'),
(68, 21, 'Almendras', 0.00, '2026-05-27 08:47:14', '2026-05-27 08:47:14'),
(69, 22, 'Unidad', 0.00, '2026-05-27 08:47:14', '2026-05-27 08:47:14'),
(70, 22, 'Pack de 4 unidades', 22.00, '2026-05-27 08:47:14', '2026-05-27 08:47:14'),
(71, 22, 'Pack de 6 unidades', 37.00, '2026-05-27 08:47:14', '2026-05-27 08:47:14'),
(72, 23, '6 unidades', 0.00, '2026-05-27 08:47:14', '2026-05-27 08:47:14'),
(73, 23, '12 unidades', 11.00, '2026-05-27 08:47:14', '2026-05-27 08:47:14'),
(74, 24, '6 unidades', 0.00, '2026-05-27 08:47:14', '2026-05-27 08:47:14'),
(75, 24, '12 unidades', 12.00, '2026-05-27 08:47:14', '2026-05-27 08:47:14'),
(76, 25, 'Manjar de panela', 0.00, '2026-05-27 08:47:14', '2026-05-27 08:47:14'),
(77, 25, 'Chocomaní', 0.00, '2026-05-27 08:47:14', '2026-05-27 08:47:14'),
(80, 27, 'Porción', -35.00, '2026-06-10 08:19:55', '2026-06-10 08:19:55'),
(81, 27, 'Entera (12 porciones)', 0.00, '2026-06-10 08:19:55', '2026-06-10 08:19:55');

--
-- Volcado de datos para la tabla `roles`
--

INSERT INTO `roles` (`id`, `name`, `slug`, `created_at`, `updated_at`) VALUES
(1, 'Admin General', 'admin_general', '2026-05-27 08:47:12', '2026-05-27 08:47:12'),
(2, 'Admin de Sede', 'admin_sede', '2026-05-27 08:47:12', '2026-05-27 08:47:12'),
(3, 'Ingeniero', 'ingeniero', '2026-05-27 08:47:12', '2026-05-27 08:47:12'),
(4, 'Cliente', 'cliente', '2026-05-27 08:47:12', '2026-05-27 08:47:12');

--
-- Volcado de datos para la tabla `sessions`
--

INSERT INTO `sessions` (`id`, `user_id`, `ip_address`, `user_agent`, `payload`, `last_activity`) VALUES
('2Qf7YQj5ADZ3vTFt8Y9K3RpxR1zdh0mElkZnQMy3', NULL, '127.0.0.1', '', 'YTozOntzOjY6Il90b2tlbiI7czo0MDoiNTRWQUhwOWxFVmVzQm9TVFptdFpLTFl4blVXaGp0aThmMUpXeGZDRyI7czo1OiJlcnJvciI7czo0OToiRGViZXMgaW5pY2lhciBzZXNpw7NuIHBhcmEgYWNjZWRlciBhIGxhIGludHJhbmV0LiI7czo2OiJfZmxhc2giO2E6Mjp7czozOiJuZXciO2E6MDp7fXM6Mzoib2xkIjthOjE6e2k6MDtzOjU6ImVycm9yIjt9fX0=', 1781114962),
('8r6eSgrwAw2OpO8ZAr4mHnY9PnfOHBeyt4SbeL3n', NULL, '127.0.0.1', '', 'YToyOntzOjY6Il90b2tlbiI7czo0MDoiR3RQczV5REs0TzZNR281SUgwSEJWc1FabkhEYjE5SWlJUktFTDlwbCI7czo2OiJfZmxhc2giO2E6Mjp7czozOiJvbGQiO2E6MDp7fXM6MzoibmV3IjthOjA6e319fQ==', 1781114962),
('jGc97ZQkKWW02743ODb2x5VspzclXR2qI1lfTOSz', NULL, '127.0.0.1', '', 'YToyOntzOjY6Il90b2tlbiI7czo0MDoiRkFoSDlRaGVMQzRDZFl2YTA1VVYyMG5ETkx2WDlXbUFRaGRLZ05CTCI7czo2OiJfZmxhc2giO2E6Mjp7czozOiJvbGQiO2E6MDp7fXM6MzoibmV3IjthOjA6e319fQ==', 1781117690),
('lOOnCAiFSzdwFwjzFh59zEgwENyRDDMGS9C95JfE', NULL, '127.0.0.1', '', 'YToyOntzOjY6Il90b2tlbiI7czo0MDoiVXJZMkFlSUxKS01DSDZpVDV4ZEEyQjcwb0dadTJVWVdzUjJZVThCTyI7czo2OiJfZmxhc2giO2E6Mjp7czozOiJvbGQiO2E6MDp7fXM6MzoibmV3IjthOjA6e319fQ==', 1781117704),
('QvHHxFUviqbVL3iXONDS8WmFI3BMl3W1qJT0f7NO', 3, '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/149.0.0.0 Safari/537.36', 'YTo4OntzOjY6Il90b2tlbiI7czo0MDoiQ0lwRFRvamtJTXRLU1FZY242d1AwTGl1bUpDVDN4bXhhVlQ0b2R2VSI7czo5OiJfcHJldmlvdXMiO2E6Mjp7czozOiJ1cmwiO3M6Mzc6Imh0dHA6Ly8xMjcuMC4wLjE6ODAwMC9pbnRyYW5ldC9vcmRlcnMiO3M6NToicm91dGUiO3M6MTg6ImFkbWluLm9yZGVycy5pbmRleCI7fXM6NjoiX2ZsYXNoIjthOjI6e3M6Mzoib2xkIjthOjA6e31zOjM6Im5ldyI7YTowOnt9fXM6NTI6ImxvZ2luX2FkbWluXzU5YmEzNmFkZGMyYjJmOTQwMTU4MGYwMTRjN2Y1OGVhNGUzMDk4OWQiO2k6MTtzOjQ6ImNhcnQiO2E6MTp7czozNDoiMV8wODg4MWQzZmZkODA4YTVlZDgwMGFiODU3NzA3MTQ0MyI7YTo2OntzOjI6ImlkIjtpOjE7czo0OiJuYW1lIjtzOjE4OiJUb3J0YSBkZSBDaG9jb2xhdGUiO3M6NToiaW1hZ2UiO047czo1OiJwcmljZSI7ZDo0NTtzOjg6InF1YW50aXR5IjtzOjE6IjEiO3M6Nzoib3B0aW9ucyI7YToxOntpOjA7czoyMToiRW50ZXJhICgxMiBwb3JjaW9uZXMpIjt9fX1zOjM6InVybCI7YTowOnt9czo1MDoibG9naW5fd2ViXzU5YmEzNmFkZGMyYjJmOTQwMTU4MGYwMTRjN2Y1OGVhNGUzMDk4OWQiO2k6MztzOjIzOiJzZWxlY3RlZF9oZWFkcXVhcnRlcl9pZCI7aToyO30=', 1781117930),
('VqaPnrCPnmseVGgsmIZ3u7B7Zssx4RpSnooPvvXo', NULL, '127.0.0.1', '', 'YToyOntzOjY6Il90b2tlbiI7czo0MDoiUlczWEs3VmRybTZrSlE0SkE1bjdxUjZpcUJsYVNqb1Bqa0FHalF5SCI7czo2OiJfZmxhc2giO2E6Mjp7czozOiJvbGQiO2E6MDp7fXM6MzoibmV3IjthOjA6e319fQ==', 1781117677);

--
-- Volcado de datos para la tabla `settings`
--

INSERT INTO `settings` (`id`, `key`, `value`, `group`, `created_at`, `updated_at`) VALUES
(1, 'nakama_api_url', 'http://localhost/public_html/public', 'nakama', '2026-06-10 02:25:01', '2026-06-10 02:25:01'),
(2, 'nakama_api_key', 'nk_f892916383bd0861324fb55c02c2b581', 'nakama', '2026-06-10 02:25:01', '2026-06-10 02:46:53'),
(3, 'nakama_enabled', '1', 'nakama', '2026-06-10 02:25:01', '2026-06-10 02:25:01');

--
-- Volcado de datos para la tabla `supplies`
--

INSERT INTO `supplies` (`id`, `name`, `unit`, `created_at`, `updated_at`) VALUES
(4, 'Leche de coco', 'und', '2026-06-10 08:12:46', '2026-06-10 08:12:46'),
(5, 'Frasco', 'und', '2026-06-10 08:12:46', '2026-06-10 08:12:46'),
(6, 'Frasquitos', 'und', '2026-06-10 08:12:46', '2026-06-10 08:12:46'),
(7, 'Frasco angosto', 'und', '2026-06-10 08:12:46', '2026-06-10 08:12:46'),
(8, 'Panela', 'g', '2026-06-10 08:12:46', '2026-06-10 08:12:46'),
(9, 'Harina de trigo', 'g', '2026-06-10 08:12:46', '2026-06-10 08:12:46'),
(10, 'Harina de quinoa', 'g', '2026-06-10 08:12:46', '2026-06-10 08:12:46'),
(11, 'Hojuelas de avena', 'g', '2026-06-10 08:12:46', '2026-06-10 08:12:46'),
(12, 'Miel', 'und', '2026-06-10 08:12:46', '2026-06-10 08:12:46'),
(13, 'Fresa', 'g', '2026-06-10 08:12:46', '2026-06-10 08:12:46'),
(14, 'Arándanos', 'g', '2026-06-10 08:12:46', '2026-06-10 08:12:46'),
(15, 'Mango Kent', 'g', '2026-06-10 08:12:46', '2026-06-10 08:12:46'),
(16, 'Lúcuma', 'g', '2026-06-10 08:12:46', '2026-06-10 08:12:46'),
(17, 'Zanahoria', 'g', '2026-06-10 08:12:46', '2026-06-10 08:12:46');

--
-- Volcado de datos para la tabla `users`
--

INSERT INTO `users` (`id`, `name`, `email`, `phone`, `email_verified_at`, `password`, `remember_token`, `created_at`, `updated_at`, `role_id`, `headquarter_id`, `google_id`, `avatar`) VALUES
(1, 'Administrador General', 'admin@gourmetica.com', NULL, NULL, '$2y$12$SBLPLa5AxY2LvN1aPE2mPe/vTx5JAI.6FeMVP4WzDNExfayGfelr.', NULL, '2026-05-27 08:47:12', '2026-05-27 08:47:12', 1, NULL, NULL, NULL),
(2, 'Ingeniero', 'ingeniero@gourmetica.com', NULL, NULL, '$2y$12$H4Ywx.i9l/mKABwsn/ZkceZyyXHjAv8j1/OaGGuDNM99861h5.tP6', NULL, '2026-05-27 08:47:13', '2026-06-10 02:27:02', 3, 1, NULL, NULL),
(3, 'Cliente de Prueba', 'cliente@gourmetica.com', NULL, NULL, '$2y$12$J4gt6dh6NLnkj6mTcz97CuvWQEZ90PLu1s0YhZwGkxsEHDSolB03u', NULL, '2026-05-27 08:47:13', '2026-05-27 08:47:13', 4, NULL, NULL, NULL),
(4, 'NELSON', 'nelson@gourmetica.com', NULL, NULL, '$2y$12$4RQ.vBumec0Nx8TV3KWYPu79J8hbh1xg10mHYoQ1Iqw9BPj0sF8rO', NULL, '2026-06-03 04:59:24', '2026-06-03 04:59:24', 2, 2, NULL, NULL);
SET FOREIGN_KEY_CHECKS=1;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;

SET FOREIGN_KEY_CHECKS=1;
