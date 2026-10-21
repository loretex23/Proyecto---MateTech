-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Servidor: 127.0.0.1
-- Tiempo de generación: 16-10-2026 a las 14:14:25
-- Versión del servidor: 10.4.32-MariaDB
-- Versión de PHP: 8.2.12

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Base de datos: `matetech`
--

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `categorias`
--

CREATE TABLE `categorias` (
  `id` int(11) NOT NULL,
  `nombre` varchar(20) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `categorias`
--

INSERT INTO `categorias` (`id`, `nombre`) VALUES
(1, '2013'),
(2, '2014'),
(3, '2015'),
(4, '2016'),
(5, '2017'),
(6, '2018'),
(7, '2019'),
(9, '2020'),
(8, 'Femenino');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `club`
--

CREATE TABLE `club` (
  `id` int(11) NOT NULL,
  `Usuario` varchar(100) NOT NULL,
  `Contraseña_hash` varchar(255) NOT NULL,
  `nombre` varchar(100) NOT NULL,
  `logo_url` varchar(255) DEFAULT NULL,
  `rol` enum('Admin','Club') NOT NULL DEFAULT 'Club',
  `nombre_dt` varchar(100) DEFAULT NULL,
  `kinesiologo` varchar(100) DEFAULT NULL,
  `ayudante_tecnico` varchar(100) DEFAULT NULL,
  `delegado` varchar(100) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `club`
--

INSERT INTO `club` (`id`, `Usuario`, `Contraseña_hash`, `nombre`, `logo_url`, `rol`, `nombre_dt`, `kinesiologo`, `ayudante_tecnico`, `delegado`) VALUES
(1, 'admin@admin.com', '$2y$10$48dDKv14h4cxG5uaZVp6leHKlu17hB7jk5W.99JjbnpUXDfx4u0ZS', 'Administrador', NULL, 'Admin', NULL, NULL, NULL, NULL),
(2, 'club1@gmail.com', '$2y$10$wYi6jA2Pma/PwMTKGmKUeuTKGuwMMV5m4ku25tSoDX59V5z5HoJSe', 'MártirFC', NULL, 'Club', 'L. Duarte', 'C. de León', 'L. Cuello', 'J. Leites'),
(3, 'club2@gmail.com', '$2y$10$IgO2Gv8Lc/mJo/qO.8RqhuwIXbXHDiQZE.tDACdzyWhwxHhCOPHf2', 'Sarandí FC', NULL, 'Club', '', '', '', ''),
(6, 'peñarol@club.com', '$2y$10$IghjIaHc9sGxUsDk267CbuJkRgTqMvHSrcjnvPO2eIjFpWUs0oGLy', 'Peñarol', NULL, 'Club', 'A. Chagas', 'W. Llaguna', 'E. Nizarala', 'Pamela Ferrao'),
(7, 'nacional@club.com', '$2y$10$VXVJWAghoB2XVC8NywX18eBCxOCHQULejSfdH2HRVb/K90uQNgIHC', 'NACIONAL DECANO', NULL, 'Club', 'Pep Guardiola', 'Rodri', 'Cuti Romero', 'Gallardo'),
(8, 'silva@gmail.com', '$2y$10$iuDchQTVbCki5Lb6m5q5k.g.D/Vrwb2hn8d3igBLAG0mVNp6xviu2', 'SilvaFC', NULL, 'Club', NULL, NULL, NULL, NULL);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `comunicados`
--

CREATE TABLE `comunicados` (
  `id` int(11) NOT NULL,
  `titulo` varchar(150) NOT NULL,
  `contenido` text NOT NULL,
  `pdf_url` varchar(255) DEFAULT NULL,
  `fecha_publicacion` timestamp NOT NULL DEFAULT current_timestamp(),
  `tipo` varchar(50) DEFAULT 'General'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `comunicados`
--

INSERT INTO `comunicados` (`id`, `titulo`, `contenido`, `pdf_url`, `fecha_publicacion`, `tipo`) VALUES
(1, 'asdsad', 'wadawd', NULL, '2026-08-24 11:25:14', 'Suspensión'),
(4, 'iugiugiu', 'ouhouhoug', NULL, '2026-08-25 02:17:28', 'General'),
(6, 'Prueba', 'Este es un comentario de prueba.', NULL, '2026-10-14 12:55:28', 'Importante');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `goles`
--

CREATE TABLE `goles` (
  `id` int(11) NOT NULL,
  `partido_id` int(11) NOT NULL,
  `jugador_id` int(11) NOT NULL,
  `minuto` int(11) DEFAULT NULL,
  `tipo` enum('normal','penal','autogol') NOT NULL DEFAULT 'normal'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `goles`
--

INSERT INTO `goles` (`id`, `partido_id`, `jugador_id`, `minuto`, `tipo`) VALUES
(43, 8, 100, 80, 'normal'),
(48, 5, 216, 52, 'normal'),
(49, 5, 226, 71, 'normal'),
(50, 6, 251, 15, 'normal'),
(51, 6, 99, 26, 'normal'),
(52, 9, 101, 35, 'penal'),
(53, 9, 102, 48, 'normal');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `jugadores`
--

CREATE TABLE `jugadores` (
  `id` int(11) NOT NULL,
  `ci` int(20) NOT NULL,
  `nombre` varchar(50) NOT NULL,
  `apellido` varchar(50) NOT NULL,
  `fecha_nacimiento` date NOT NULL,
  `club_id` int(11) NOT NULL,
  `categoria_id` int(11) NOT NULL,
  `carnet_salud_url` varchar(255) DEFAULT NULL,
  `carnet_vencimiento` date DEFAULT NULL,
  `foto_url` longblob DEFAULT NULL,
  `masa` decimal(5,2) DEFAULT NULL COMMENT 'Peso en kg',
  `altura` decimal(4,2) DEFAULT NULL COMMENT 'Altura en metros (ej: 1.75)'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `jugadores`
--

INSERT INTO `jugadores` (`id`, `ci`, `nombre`, `apellido`, `fecha_nacimiento`, `club_id`, `categoria_id`, `carnet_salud_url`, `carnet_vencimiento`, `foto_url`, `masa`, `altura`) VALUES
(99, 65543790, 'Ricardo', 'Vargas', '2026-09-17', 3, 5, NULL, '2026-09-07', 0x696d672f666f746f732f466f746f5f373231363238343631382e706e67, 42.00, 1.38),
(100, 74836489, 'Pamela', 'Profe', '2001-01-07', 6, 8, NULL, '2027-01-07', 0x696d672f666f746f732f466f746f5f37343833363438392e6a7067, 60.00, 1.60),
(101, 56526090, 'Rodrigo', 'Peña', '2015-08-04', 6, 3, NULL, '2027-06-16', 0x696d672f666f746f732f466f746f5f35363532363039302e6a7067, 47.00, 1.50),
(102, 62162280, 'Alvaro', 'Chagas', '2015-08-22', 6, 3, NULL, '2028-02-12', 0x696d672f666f746f732f466f746f5f36323136323238302e6a7067, 42.40, 1.57),
(103, 51234567, 'Mateo', 'Rodríguez', '2013-03-12', 2, 1, NULL, '2027-05-10', NULL, 38.50, 1.42),
(104, 49876543, 'Luca', 'Fernández', '2013-07-22', 2, 1, NULL, '2027-06-15', NULL, 40.00, 1.45),
(105, 50123456, 'Agustín', 'López', '2013-01-08', 2, 1, NULL, '2027-04-20', NULL, 37.00, 1.40),
(106, 48765432, 'Tomás', 'García', '2013-11-30', 2, 1, NULL, '2027-08-11', NULL, 39.50, 1.43),
(107, 52345678, 'Nicolás', 'Pérez', '2013-05-17', 2, 1, NULL, '2027-09-01', NULL, 41.00, 1.46),
(108, 47654321, 'Santiago', 'Martínez', '2013-09-04', 2, 1, NULL, '2027-03-14', NULL, 38.00, 1.41),
(109, 53456789, 'Emiliano', 'Silva', '2013-02-25', 2, 1, NULL, '2027-07-19', NULL, 40.50, 1.44),
(110, 46543210, 'Facundo', 'González', '2013-06-14', 2, 1, NULL, '2027-10-05', NULL, 37.50, 1.39),
(111, 54567890, 'Benjamín', 'Hernández', '2013-10-01', 2, 1, NULL, '2027-11-22', NULL, 39.00, 1.42),
(112, 0, 'Gonzalo', 'Olivera', '2013-04-11', 2, 1, NULL, '2027-01-30', NULL, 38.00, 1.40),
(113, 55678901, 'Joaquín', 'Romero', '2013-03-15', 3, 1, NULL, '2027-05-12', NULL, 39.00, 1.41),
(114, 44321098, 'Mathías', 'Sosa', '2013-08-20', 3, 1, NULL, '2027-06-18', NULL, 41.00, 1.44),
(115, 56789012, 'Bautista', 'Torres', '2013-02-10', 3, 1, NULL, '2027-04-22', NULL, 37.50, 1.39),
(116, 43210987, 'Gabriel', 'Benítez', '2013-12-01', 3, 1, NULL, '2027-08-14', NULL, 40.00, 1.43),
(117, 57890123, 'Santino', 'Acosta', '2013-06-18', 3, 1, NULL, '2027-09-05', NULL, 40.50, 1.45),
(118, 42109876, 'Thiago', 'Castro', '2013-10-05', 3, 1, NULL, '2027-03-18', NULL, 38.50, 1.42),
(119, 58901234, 'Lautaro', 'Gómez', '2013-01-28', 3, 1, NULL, '2027-07-21', NULL, 39.00, 1.40),
(120, 41098765, 'Felipe', 'Morales', '2013-07-12', 3, 1, NULL, '2027-10-09', NULL, 38.00, 1.41),
(121, 59012345, 'Ian', 'Rua', '2013-11-04', 3, 1, NULL, '2027-11-25', NULL, 39.50, 1.43),
(122, 40987654, 'Enzo', 'Díaz', '2013-05-09', 3, 1, NULL, '2027-02-01', NULL, 37.00, 1.38),
(123, 50192834, 'Nahuel', 'Nández', '2013-02-11', 6, 1, NULL, '2027-04-10', NULL, 40.00, 1.43),
(124, 49283741, 'Gaston', 'Pereiro', '2013-04-14', 6, 1, NULL, '2027-05-11', NULL, 39.00, 1.41),
(125, 51827364, 'Federico', 'Valverde', '2013-06-18', 6, 1, NULL, '2027-06-12', NULL, 41.50, 1.46),
(126, 48372615, 'Darwin', 'Núñez', '2013-08-22', 6, 1, NULL, '2027-07-13', NULL, 42.00, 1.47),
(127, 52736418, 'Cristian', 'Rodriguez', '2013-10-26', 6, 1, NULL, '2027-08-14', NULL, 40.50, 1.44),
(128, 47261539, 'Brian', 'Lozano', '2013-01-30', 6, 1, NULL, '2027-09-15', NULL, 38.00, 1.40),
(129, 53641827, 'Facundo', 'Pellistri', '2013-03-04', 6, 1, NULL, '2027-10-16', NULL, 37.50, 1.39),
(130, 46152438, 'Agustin', 'Alvarez', '2013-05-08', 6, 1, NULL, '2027-11-17', NULL, 39.50, 1.42),
(131, 54182736, 'Ignacio', 'Laquintana', '2013-07-12', 6, 1, NULL, '2027-12-18', NULL, 38.50, 1.41),
(132, 45241327, 'Sebastian', 'Sosa', '2013-09-16', 6, 1, NULL, '2027-01-19', NULL, 40.00, 1.43),
(133, 55273849, 'Diego', 'Polenta', '2013-01-15', 7, 1, NULL, '2027-03-10', NULL, 42.00, 1.44),
(134, 44132716, 'Gonzalo', 'Bergessio', '2013-03-20', 7, 1, NULL, '2027-04-11', NULL, 41.00, 1.43),
(135, 56384920, 'Franco', 'Fagúndez', '2013-05-12', 7, 1, NULL, '2027-05-12', NULL, 39.50, 1.41),
(136, 43021605, 'Sergio', 'Rochet', '2013-07-19', 7, 1, NULL, '2027-06-13', NULL, 40.00, 1.45),
(137, 57492031, 'Camilo', 'Cándido', '2013-09-23', 7, 1, NULL, '2027-07-14', NULL, 38.00, 1.40),
(138, 42910594, 'Felipe', 'Carballo', '2013-11-02', 7, 1, NULL, '2027-08-15', NULL, 39.00, 1.42),
(139, 58503142, 'Alfonso', 'Trezza', '2013-02-14', 7, 1, NULL, '2027-09-16', NULL, 37.00, 1.38),
(140, 41809483, 'Brian', 'Ocampo', '2013-04-18', 7, 1, NULL, '2027-10-17', NULL, 38.50, 1.41),
(141, 59614253, 'Mathías', 'Laborda', '2013-06-22', 7, 1, NULL, '2027-11-18', NULL, 40.50, 1.44),
(142, 40798372, 'Leandro', 'Fernández', '2013-08-28', 7, 1, NULL, '2027-12-19', NULL, 39.00, 1.42),
(143, 50817263, 'Alan', 'Mendoza', '2017-01-05', 6, 5, NULL, '2027-05-10', NULL, 26.00, 1.20),
(144, 49726150, 'Bruno', 'Sanguinetti', '2017-02-09', 6, 5, NULL, '2027-05-10', NULL, 25.50, 1.21),
(145, 51635249, 'Cesar', 'Falco', '2017-03-13', 6, 5, NULL, '2027-05-10', NULL, 27.00, 1.22),
(146, 48544338, 'Daniel', 'Garat', '2017-04-17', 6, 5, NULL, '2027-05-10', NULL, 26.50, 1.21),
(147, 52553427, 'Edgardo', 'Viana', '2017-05-21', 6, 5, NULL, '2027-05-10', NULL, 28.00, 1.24),
(148, 47462516, 'Fabian', 'López', '2017-06-25', 6, 5, NULL, '2027-05-10', NULL, 25.00, 1.19),
(149, 53371605, 'Guillermo', 'Sosa', '2017-07-29', 6, 5, NULL, '2027-05-10', NULL, 27.50, 1.23),
(150, 46280794, 'Hugo', 'Pérez', '2017-08-02', 6, 5, NULL, '2027-05-10', NULL, 26.80, 1.22),
(151, 54199883, 'Ismael', 'Gómez', '2017-09-06', 6, 5, NULL, '2027-05-10', NULL, 27.20, 1.23),
(152, 45008972, 'Jorge', 'Silva', '2017-10-10', 6, 5, NULL, '2027-05-10', NULL, 26.00, 1.20),
(153, 55917061, 'Lucas', 'Ríos', '2017-11-14', 6, 5, NULL, '2027-05-10', NULL, 28.50, 1.25),
(154, 44826150, 'Mario', 'Torres', '2017-12-18', 6, 5, NULL, '2027-05-10', NULL, 25.90, 1.21),
(155, 56735249, 'Nestor', 'Díaz', '2017-01-22', 6, 5, NULL, '2027-05-10', NULL, 27.10, 1.22),
(156, 43644338, 'Omar', 'Acosta', '2017-02-26', 6, 5, NULL, '2027-05-10', NULL, 26.40, 1.21),
(157, 57553427, 'Pablo', 'Benítez', '2017-03-30', 6, 5, NULL, '2027-05-10', NULL, 28.00, 1.24),
(158, 42462516, 'Quintin', 'Morales', '2017-04-03', 6, 5, NULL, '2027-05-10', NULL, 25.70, 1.20),
(159, 58371605, 'Ramiro', 'Vargas', '2017-05-07', 6, 5, NULL, '2027-05-10', NULL, 27.30, 1.23),
(160, 59280794, 'Ariel', 'Campos', '2017-01-11', 7, 5, NULL, '2027-05-10', NULL, 26.10, 1.20),
(161, 48199883, 'Boris', 'Delgado', '2017-02-15', 7, 5, NULL, '2027-05-10', NULL, 25.60, 1.21),
(162, 50008972, 'Carlos', 'Espinoza', '2017-03-19', 7, 5, NULL, '2027-05-10', NULL, 27.20, 1.22),
(163, 41917061, 'Dante', 'Fuentes', '2017-04-23', 7, 5, NULL, '2027-05-10', NULL, 26.40, 1.21),
(164, 52826150, 'Enzo', 'Gutiérrez', '2017-05-27', 7, 5, NULL, '2027-05-10', NULL, 28.10, 1.24),
(165, 43735249, 'Fabian', 'Herrera', '2017-06-30', 7, 5, NULL, '2027-05-10', NULL, 25.20, 1.19),
(166, 54644338, 'Gabriel', 'Ibarra', '2017-07-04', 7, 5, NULL, '2027-05-10', NULL, 27.60, 1.23),
(167, 45553427, 'Hector', 'Jiménez', '2017-08-08', 7, 5, NULL, '2027-05-10', NULL, 26.90, 1.22),
(168, 56462516, 'Ivan', 'Lara', '2017-09-12', 7, 5, NULL, '2027-05-10', NULL, 27.30, 1.23),
(169, 47371605, 'Javier', 'Mendoza', '2017-10-16', 7, 5, NULL, '2027-05-10', NULL, 26.10, 1.20),
(170, 58280794, 'Kevin', 'Núñez', '2017-11-20', 7, 5, NULL, '2027-05-10', NULL, 28.40, 1.25),
(171, 49199883, 'Leo', 'Ortega', '2017-12-24', 7, 5, NULL, '2027-05-10', NULL, 25.80, 1.21),
(172, 51008972, 'Marcelo', 'Paredes', '2017-01-28', 7, 5, NULL, '2027-05-10', NULL, 27.00, 1.22),
(173, 42917061, 'Nicolas', 'Quinteros', '2017-02-02', 7, 5, NULL, '2027-05-10', NULL, 26.30, 1.21),
(174, 53826150, 'Oscar', 'Ramos', '2017-03-06', 7, 5, NULL, '2027-05-10', NULL, 28.10, 1.24),
(175, 44735249, 'Pablo', 'Salinas', '2017-04-10', 7, 5, NULL, '2027-05-10', NULL, 25.60, 1.20),
(176, 55644338, 'Rodrigo', 'Valdez', '2017-05-14', 7, 5, NULL, '2027-05-10', NULL, 27.40, 1.23),
(177, 51122334, 'Sofía', 'Olivera', '2008-01-10', 6, 8, NULL, '2027-08-10', NULL, 53.00, 1.61),
(178, 42233445, 'Lucía', 'Rodríguez', '2008-03-14', 6, 8, NULL, '2027-08-10', NULL, 54.50, 1.63),
(179, 53344556, 'Martina', 'Fernández', '2009-05-18', 6, 8, NULL, '2027-08-10', NULL, 51.00, 1.59),
(180, 44455667, 'Camila', 'García', '2009-07-22', 6, 8, NULL, '2027-08-10', NULL, 52.50, 1.60),
(181, 55566778, 'Valentina', 'López', '2010-09-26', 6, 8, NULL, '2027-08-10', NULL, 50.00, 1.58),
(182, 46677889, 'Florencia', 'Martínez', '2010-11-30', 6, 8, NULL, '2027-08-10', NULL, 55.00, 1.64),
(183, 57788990, 'Juliet', 'Pérez', '2008-02-04', 6, 8, NULL, '2027-08-10', NULL, 53.50, 1.62),
(184, 48899001, 'Micaela', 'González', '2008-04-08', 6, 8, NULL, '2027-08-10', NULL, 54.00, 1.62),
(185, 59900112, 'Agustina', 'Silva', '2009-06-12', 6, 8, NULL, '2027-08-10', NULL, 52.00, 1.61),
(186, 40011223, 'Paula', 'Sosa', '2009-08-16', 6, 8, NULL, '2027-08-10', NULL, 51.50, 1.59),
(187, 51122330, 'Catalina', 'Romero', '2010-10-20', 6, 8, NULL, '2027-08-10', NULL, 56.00, 1.65),
(188, 42233441, 'Renata', 'Benítez', '2010-12-24', 6, 8, NULL, '2027-08-10', NULL, 50.50, 1.58),
(189, 53344552, 'Victoria', 'Acosta', '2008-01-28', 6, 8, NULL, '2027-08-10', NULL, 53.00, 1.61),
(190, 44455663, 'Clara', 'Torres', '2008-03-03', 6, 8, NULL, '2027-08-10', NULL, 54.20, 1.63),
(191, 55566774, 'Delfina', 'Castro', '2009-05-07', 6, 8, NULL, '2027-08-10', NULL, 51.80, 1.60),
(192, 46677885, 'Josefina', 'Gómez', '2009-07-11', 6, 8, NULL, '2027-08-10', NULL, 52.90, 1.61),
(193, 57788996, 'Belén', 'Morales', '2010-09-15', 6, 8, NULL, '2027-08-10', NULL, 50.20, 1.58),
(194, 58899007, 'Antonella', 'Díaz', '2008-01-12', 7, 8, NULL, '2027-08-10', NULL, 53.10, 1.61),
(195, 49900118, 'Bianca', 'Vargas', '2008-03-16', 7, 8, NULL, '2027-08-10', NULL, 54.60, 1.63),
(196, 50011229, 'Constanza', 'Ríos', '2009-05-20', 7, 8, NULL, '2027-08-10', NULL, 51.20, 1.59),
(197, 41122330, 'Ema', 'Mendoza', '2009-07-24', 7, 8, NULL, '2027-08-10', NULL, 52.40, 1.60),
(198, 52233441, 'Fiorella', 'Paredes', '2010-09-28', 7, 8, NULL, '2027-08-10', NULL, 50.30, 1.58),
(199, 43344552, 'Gimena', 'Vega', '2010-12-02', 7, 8, NULL, '2027-08-10', NULL, 55.10, 1.64),
(200, 54455663, 'Isabella', 'Lara', '2008-02-06', 7, 8, NULL, '2027-08-10', NULL, 53.60, 1.62),
(201, 45566774, 'Lola', 'Cabrera', '2008-04-10', 7, 8, NULL, '2027-08-10', NULL, 54.10, 1.62),
(202, 56677885, 'Magdalena', 'Pinto', '2009-06-14', 7, 8, NULL, '2027-08-10', NULL, 52.20, 1.61),
(203, 47788996, 'Nuria', 'Núñez', '2009-08-18', 7, 8, NULL, '2027-08-10', NULL, 51.40, 1.59),
(204, 58899000, 'Olivia', 'Cáceres', '2010-10-22', 7, 8, NULL, '2027-08-10', NULL, 56.20, 1.65),
(205, 49900111, 'Pia', 'Mendez', '2010-12-26', 7, 8, NULL, '2027-08-10', NULL, 50.40, 1.58),
(206, 50011222, 'Rocío', 'Figueroa', '2008-01-30', 7, 8, NULL, '2027-08-10', NULL, 53.20, 1.61),
(207, 41122333, 'Sol', 'Aguirre', '2008-03-05', 7, 8, NULL, '2027-08-10', NULL, 54.30, 1.63),
(208, 52233444, 'Trinidad', 'Ibarra', '2009-05-09', 7, 8, NULL, '2027-08-10', NULL, 51.70, 1.60),
(209, 43344555, 'Ximena', 'Barrios', '2009-07-13', 7, 8, NULL, '2027-08-10', NULL, 52.80, 1.61),
(210, 54455666, 'Zoe', 'Campos', '2010-09-17', 7, 8, NULL, '2027-08-10', NULL, 50.10, 1.58),
(211, 50238491, 'Axel', 'Suárez', '2014-04-18', 2, 2, NULL, '2027-06-10', NULL, 36.00, 1.38),
(212, 49327580, 'Bruno', 'Álvarez', '2014-08-09', 2, 2, NULL, '2027-06-10', NULL, 37.50, 1.40),
(213, 51416679, 'Damián', 'Torres', '2014-12-20', 2, 2, NULL, '2027-06-10', NULL, 35.50, 1.37),
(214, 48505768, 'Héctor', 'Vargas', '2014-03-24', 2, 2, NULL, '2027-06-10', NULL, 35.00, 1.36),
(215, 52694857, 'Joaquín', 'Jiménez', '2014-11-02', 2, 2, NULL, '2027-06-10', NULL, 36.00, 1.37),
(216, 47783946, 'Luciano', 'Reyes', '2014-01-15', 2, 2, NULL, '2027-06-10', NULL, 37.00, 1.39),
(217, 53872035, 'Manuel', 'Acosta', '2014-05-22', 2, 2, NULL, '2027-06-10', NULL, 36.50, 1.38),
(218, 46961124, 'Santino', 'Méndez', '2014-07-30', 2, 2, NULL, '2027-06-10', NULL, 38.00, 1.41),
(219, 54050213, 'Tomas', 'Paredes', '2014-09-12', 2, 2, NULL, '2027-06-10', NULL, 35.80, 1.37),
(220, 45139302, 'Valentín', 'Lara', '2014-10-05', 2, 2, NULL, '2027-06-10', NULL, 37.20, 1.40),
(221, 55228401, 'Ezequiel', 'Ramírez', '2014-02-11', 3, 2, NULL, '2027-07-15', NULL, 38.00, 1.41),
(222, 44317590, 'Felipe', 'Sánchez', '2014-06-30', 3, 2, NULL, '2027-07-15', NULL, 36.50, 1.39),
(223, 56406689, 'Gabriel', 'Castro', '2014-10-07', 3, 2, NULL, '2027-07-15', NULL, 37.00, 1.38),
(224, 43595778, 'Iván', 'Morales', '2014-07-15', 3, 2, NULL, '2027-07-15', NULL, 38.50, 1.42),
(225, 57684867, 'Bautista', 'Iglesias', '2014-01-20', 3, 2, NULL, '2027-07-15', NULL, 36.20, 1.38),
(226, 42773956, 'Camilo', 'Fontana', '2014-03-18', 3, 2, NULL, '2027-07-15', NULL, 37.80, 1.40),
(227, 58863045, 'Dante', 'Varela', '2014-05-11', 3, 2, NULL, '2027-07-15', NULL, 35.90, 1.37),
(228, 41952134, 'Emiliano', 'Barrios', '2014-08-25', 3, 2, NULL, '2027-07-15', NULL, 38.10, 1.41),
(229, 59041223, 'Franco', 'Cabrera', '2014-09-09', 3, 2, NULL, '2027-07-15', NULL, 36.70, 1.39),
(230, 40130312, 'Gael', 'Espinosa', '2014-11-28', 3, 2, NULL, '2027-07-15', NULL, 37.40, 1.40),
(231, 51219401, 'Ignacio', 'Ruglio', '2014-01-10', 6, 2, NULL, '2027-08-20', NULL, 37.00, 1.39),
(232, 42308590, 'Diego', 'Aguirre', '2014-02-14', 6, 2, NULL, '2027-08-20', NULL, 38.50, 1.42),
(233, 53497689, 'Léo', 'Coelho', '2014-03-19', 6, 2, NULL, '2027-08-20', NULL, 39.00, 1.43),
(234, 44586778, 'Maximiliano', 'Olivera', '2014-04-23', 6, 2, NULL, '2027-08-20', NULL, 37.50, 1.40),
(235, 55675867, 'Javier', 'Cabrera', '2014-05-27', 6, 2, NULL, '2027-08-20', NULL, 36.00, 1.38),
(236, 46764956, 'Eduardo', 'Darias', '2014-06-30', 6, 2, NULL, '2027-08-20', NULL, 38.00, 1.41),
(237, 57854045, 'Lucas', 'Hernández', '2014-07-04', 6, 2, NULL, '2027-08-20', NULL, 37.20, 1.39),
(238, 48943134, 'Javier', 'Méndez', '2014-08-08', 6, 2, NULL, '2027-08-20', NULL, 38.80, 1.42),
(239, 50032223, 'Damian', 'García', '2014-09-12', 6, 2, NULL, '2027-08-20', NULL, 36.90, 1.38),
(240, 41121312, 'Nahuel', 'Acosta', '2014-10-16', 6, 2, NULL, '2027-08-20', NULL, 37.70, 1.40),
(241, 52210401, 'Diego', 'Zabala', '2014-01-08', 7, 2, NULL, '2027-09-05', NULL, 38.00, 1.41),
(242, 43309590, 'Christian', 'Oliva', '2014-02-12', 7, 2, NULL, '2027-09-05', NULL, 37.50, 1.40),
(243, 54498689, 'Alexis', 'Castro', '2014-03-16', 7, 2, NULL, '2027-09-05', NULL, 36.80, 1.38),
(244, 45587778, 'Jeremía', 'Recoba', '2014-04-20', 7, 2, NULL, '2027-09-05', NULL, 35.90, 1.37),
(245, 56676867, 'Gonzalo', 'Carneiro', '2014-05-24', 7, 2, NULL, '2027-09-05', NULL, 39.50, 1.44),
(246, 47765956, 'Ruben', 'Bentancourt', '2014-06-28', 7, 2, NULL, '2027-09-05', NULL, 38.20, 1.42),
(247, 58855045, 'Federico', 'Santander', '2014-07-02', 7, 2, NULL, '2027-09-05', NULL, 40.00, 1.45),
(248, 49944134, 'Mateo', 'Antoni', '2014-08-06', 7, 2, NULL, '2027-09-05', NULL, 37.10, 1.39),
(249, 51033223, 'Gabriel', 'Báez', '2014-09-10', 7, 2, NULL, '2027-09-05', NULL, 36.60, 1.38),
(250, 42122312, 'Ignacio', 'Suárez', '2014-10-14', 7, 2, NULL, '2027-09-05', NULL, 37.90, 1.41),
(251, 70563356, 'Juan', 'Pitoco', '2017-07-18', 2, 5, NULL, '2027-04-25', 0x696d672f666f746f732f466f746f5f37303536333335372e6a7067, 38.00, 1.42);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `lesiones`
--

CREATE TABLE `lesiones` (
  `id` int(11) NOT NULL,
  `partido_id` int(11) NOT NULL,
  `jugador_id` int(11) NOT NULL,
  `descripcion` varchar(200) NOT NULL,
  `minuto` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `partidos`
--

CREATE TABLE `partidos` (
  `id` int(11) NOT NULL,
  `categoria_id` int(11) NOT NULL,
  `competencia` enum('Liga','Copa de Oro','Copa de Plata') NOT NULL DEFAULT 'Liga',
  `club_local_id` int(11) NOT NULL,
  `club_visitante_id` int(11) NOT NULL,
  `fecha_partido` datetime DEFAULT NULL,
  `estado` enum('sin_fecha','programado','jugado','pendiente','suspendido') DEFAULT 'sin_fecha',
  `goles_local` int(11) DEFAULT 0,
  `goles_visitante` int(11) DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `partidos`
--

INSERT INTO `partidos` (`id`, `categoria_id`, `competencia`, `club_local_id`, `club_visitante_id`, `fecha_partido`, `estado`, `goles_local`, `goles_visitante`) VALUES
(5, 2, 'Liga', 2, 3, '2026-08-28 08:23:00', 'jugado', 1, 1),
(6, 5, 'Liga', 2, 3, '2026-08-29 10:30:00', 'jugado', 1, 1),
(8, 8, 'Liga', 7, 6, '2026-09-25 12:34:00', 'jugado', 0, 1),
(9, 3, 'Copa de Plata', 6, 7, '2026-09-29 12:30:00', 'jugado', 2, 0),
(10, 2, 'Copa de Oro', 3, 2, '2026-10-22 17:00:00', 'programado', 0, 0),
(11, 5, 'Copa de Oro', 2, 6, '2026-09-15 15:45:00', 'jugado', 3, 1);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `sanciones`
--

CREATE TABLE `sanciones` (
  `id` int(11) NOT NULL,
  `partido_id` int(11) NOT NULL,
  `jugador_id` int(11) NOT NULL,
  `tipo_tarjeta` enum('amarilla','roja') NOT NULL,
  `minuto` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `sanciones`
--

INSERT INTO `sanciones` (`id`, `partido_id`, `jugador_id`, `tipo_tarjeta`, `minuto`) VALUES
(11, 8, 100, 'roja', 90),
(12, 5, 212, 'amarilla', 22),
(13, 9, 102, 'amarilla', 72);

--
-- Índices para tablas volcadas
--

--
-- Indices de la tabla `categorias`
--
ALTER TABLE `categorias`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `nombre` (`nombre`);

--
-- Indices de la tabla `club`
--
ALTER TABLE `club`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `Usuario` (`Usuario`);

--
-- Indices de la tabla `comunicados`
--
ALTER TABLE `comunicados`
  ADD PRIMARY KEY (`id`);

--
-- Indices de la tabla `goles`
--
ALTER TABLE `goles`
  ADD PRIMARY KEY (`id`),
  ADD KEY `partido_id` (`partido_id`),
  ADD KEY `jugador_id` (`jugador_id`);

--
-- Indices de la tabla `jugadores`
--
ALTER TABLE `jugadores`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `ci` (`ci`),
  ADD KEY `club_id` (`club_id`),
  ADD KEY `categoria_id` (`categoria_id`);

--
-- Indices de la tabla `lesiones`
--
ALTER TABLE `lesiones`
  ADD PRIMARY KEY (`id`),
  ADD KEY `partido_id` (`partido_id`),
  ADD KEY `jugador_id` (`jugador_id`);

--
-- Indices de la tabla `partidos`
--
ALTER TABLE `partidos`
  ADD PRIMARY KEY (`id`),
  ADD KEY `categoria_id` (`categoria_id`),
  ADD KEY `club_local_id` (`club_local_id`),
  ADD KEY `club_visitante_id` (`club_visitante_id`);

--
-- Indices de la tabla `sanciones`
--
ALTER TABLE `sanciones`
  ADD PRIMARY KEY (`id`),
  ADD KEY `partido_id` (`partido_id`),
  ADD KEY `jugador_id` (`jugador_id`);

--
-- AUTO_INCREMENT de las tablas volcadas
--

--
-- AUTO_INCREMENT de la tabla `categorias`
--
ALTER TABLE `categorias`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=10;

--
-- AUTO_INCREMENT de la tabla `club`
--
ALTER TABLE `club`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=9;

--
-- AUTO_INCREMENT de la tabla `comunicados`
--
ALTER TABLE `comunicados`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT de la tabla `goles`
--
ALTER TABLE `goles`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=54;

--
-- AUTO_INCREMENT de la tabla `jugadores`
--
ALTER TABLE `jugadores`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=252;

--
-- AUTO_INCREMENT de la tabla `lesiones`
--
ALTER TABLE `lesiones`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT de la tabla `partidos`
--
ALTER TABLE `partidos`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=12;

--
-- AUTO_INCREMENT de la tabla `sanciones`
--
ALTER TABLE `sanciones`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=14;

--
-- Restricciones para tablas volcadas
--

--
-- Filtros para la tabla `goles`
--
ALTER TABLE `goles`
  ADD CONSTRAINT `goles_ibfk_1` FOREIGN KEY (`partido_id`) REFERENCES `partidos` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `goles_ibfk_2` FOREIGN KEY (`jugador_id`) REFERENCES `jugadores` (`id`) ON DELETE CASCADE;

--
-- Filtros para la tabla `jugadores`
--
ALTER TABLE `jugadores`
  ADD CONSTRAINT `jugadores_ibfk_1` FOREIGN KEY (`club_id`) REFERENCES `club` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `jugadores_ibfk_2` FOREIGN KEY (`categoria_id`) REFERENCES `categorias` (`id`);

--
-- Filtros para la tabla `lesiones`
--
ALTER TABLE `lesiones`
  ADD CONSTRAINT `lesiones_ibfk_1` FOREIGN KEY (`partido_id`) REFERENCES `partidos` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `lesiones_ibfk_2` FOREIGN KEY (`jugador_id`) REFERENCES `jugadores` (`id`) ON DELETE CASCADE;

--
-- Filtros para la tabla `partidos`
--
ALTER TABLE `partidos`
  ADD CONSTRAINT `partidos_ibfk_1` FOREIGN KEY (`categoria_id`) REFERENCES `categorias` (`id`),
  ADD CONSTRAINT `partidos_ibfk_2` FOREIGN KEY (`club_local_id`) REFERENCES `club` (`id`),
  ADD CONSTRAINT `partidos_ibfk_3` FOREIGN KEY (`club_visitante_id`) REFERENCES `club` (`id`);

--
-- Filtros para la tabla `sanciones`
--
ALTER TABLE `sanciones`
  ADD CONSTRAINT `sanciones_ibfk_1` FOREIGN KEY (`partido_id`) REFERENCES `partidos` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `sanciones_ibfk_2` FOREIGN KEY (`jugador_id`) REFERENCES `jugadores` (`id`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
