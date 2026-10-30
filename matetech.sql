-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Servidor: 127.0.0.1
-- Tiempo de generación: 30-10-2026 a las 14:03:52
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
(6, 'penarol@club.com', '$2y$10$IghjIaHc9sGxUsDk267CbuJkRgTqMvHSrcjnvPO2eIjFpWUs0oGLy', 'Peñarol', NULL, 'Club', 'A. Chagas', 'W. Llaguna', 'E. Nizarala', 'Pamela Ferrao'),
(7, 'nacional@club.com', '$2y$10$VXVJWAghoB2XVC8NywX18eBCxOCHQULejSfdH2HRVb/K90uQNgIHC', 'NACIONAL DECANO', NULL, 'Club', 'Pep Guardiola', 'Rodri', 'Cuti Romero', 'Gallardo'),
(8, 'silva@gmail.com', '$2y$10$iuDchQTVbCki5Lb6m5q5k.g.D/Vrwb2hn8d3igBLAG0mVNp6xviu2', 'SilvaFC', NULL, 'Club', NULL, NULL, NULL, NULL),
(9, 'flancito@gmail.com', '$2y$10$5RBkKLkSjm7.PHnAiXPC6eB83mSHt1UmE4SfBaaigdrL.o5OsPAUi', 'FlanFC', NULL, 'Club', NULL, NULL, NULL, NULL);

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
(6, 'Prueba', 'Este es un comentario de prueba.', NULL, '2026-10-14 12:55:28', 'Importante'),
(7, 'Partido suspendido', 'El partido del MártirFc contra Peñarol ha sido suspendido a causa de lluvias', NULL, '2026-09-25 14:11:50', 'General'),
(8, 'Roja en el partido MártirFc vs SilviaFC', 'El jugador Emiliano Silva recibió una tarjeta roja tras un mal comportamiento continuo contra el jugador Héctor Vargas. Silva no podrá jugar por el total de un solo partido.', NULL, '2026-09-08 18:46:02', 'Suspensión');

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
(53, 9, 102, 48, 'normal'),
(54, 14, 45, 25, 'normal'),
(55, 14, 48, 67, 'normal'),
(56, 14, 65, 54, 'normal'),
(57, 14, 64, 90, 'normal'),
(62, 12, 5, 34, 'normal'),
(63, 12, 3, 56, 'normal'),
(64, 12, 5, 79, 'penal'),
(65, 12, 75, 62, 'normal'),
(66, 15, 49, 12, 'normal'),
(67, 15, 53, 28, 'normal'),
(68, 15, 40, 31, 'normal'),
(69, 15, 38, 48, 'normal'),
(70, 15, 41, 72, 'penal'),
(71, 15, 41, 86, 'normal'),
(72, 16, 30, 28, 'normal'),
(73, 16, 28, 63, 'normal'),
(74, 18, 12, 75, 'normal'),
(75, 18, 21, 42, 'normal'),
(76, 18, 21, 62, 'normal'),
(77, 18, 21, 88, 'normal'),
(90, 17, 73, 12, 'normal'),
(91, 17, 73, 34, 'normal'),
(92, 17, 78, 52, 'normal'),
(93, 17, 75, 67, 'normal'),
(94, 17, 76, 72, 'normal'),
(95, 17, 77, 90, 'normal');

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
(1, 65543790, 'Ricardo', 'Vargas', '2013-09-17', 2, 1, NULL, NULL, NULL, NULL, NULL),
(2, 52250001, 'Pamela', 'Profe', '2013-01-07', 2, 1, NULL, NULL, NULL, NULL, NULL),
(3, 56526090, 'Rodrigo', 'Peña', '2013-08-04', 2, 1, NULL, NULL, NULL, NULL, NULL),
(4, 5456814, 'Mateo', 'Rodríguez', '2013-03-12', 2, 1, NULL, NULL, NULL, NULL, NULL),
(5, 5260900, 'Lautaro', 'Gómez', '2013-01-28', 2, 1, NULL, NULL, NULL, NULL, NULL),
(6, 46735722, 'Agustín', 'López', '2013-01-08', 2, 1, NULL, '2027-07-08', 0x696d672f666f746f732f466f746f5f35303132333435362e6a7067, 51.00, 1.57),
(7, 43021605, 'Sergio', 'Rochet', '2014-07-19', 2, 2, NULL, NULL, NULL, NULL, NULL),
(8, 54182736, 'Ignacio', 'Laquintana', '2014-07-12', 2, 2, NULL, NULL, NULL, NULL, NULL),
(9, 45241327, 'Sebastian', 'Sosa', '2014-09-10', 2, 2, NULL, NULL, NULL, NULL, NULL),
(10, 55273849, 'Diego', 'Polenta', '2014-01-15', 2, 2, NULL, NULL, NULL, NULL, NULL),
(11, 44132716, 'Gonzalo', 'Bergessio', '2014-03-20', 2, 2, NULL, NULL, NULL, NULL, NULL),
(12, 56384920, 'Franco', 'Fagúndez', '2014-05-12', 2, 2, NULL, NULL, NULL, NULL, NULL),
(13, 48899001, 'Micaela', 'González', '2008-04-08', 2, 8, NULL, NULL, NULL, NULL, NULL),
(14, 59900112, 'Agustina', 'Silva', '2009-06-12', 2, 8, NULL, NULL, NULL, NULL, NULL),
(15, 51122330, 'Catalina', 'Romero', '2010-10-20', 2, 8, NULL, NULL, NULL, NULL, NULL),
(16, 53344552, 'Victoria', 'Acosta', '2008-01-28', 2, 8, NULL, NULL, NULL, NULL, NULL),
(17, 55566774, 'Delfina', 'Castro', '2009-05-07', 2, 8, NULL, NULL, NULL, NULL, NULL),
(18, 46677885, 'Josefina', 'Gómez', '2009-07-11', 2, 8, NULL, '2027-02-17', 0x696d672f666f746f732f466f746f5f34363637373838352e706e67, 45.00, 1.30),
(19, 62162280, 'Alvaro', 'Chagas', '2014-08-22', 7, 2, NULL, NULL, NULL, NULL, NULL),
(20, 50238491, 'Axel', 'Suárez', '2014-04-18', 7, 2, NULL, NULL, NULL, NULL, NULL),
(21, 49327580, 'Bruno', 'Álvarez', '2014-08-09', 7, 2, NULL, NULL, NULL, NULL, NULL),
(22, 51416679, 'Damián', 'Torres', '2014-12-20', 7, 2, NULL, NULL, NULL, NULL, NULL),
(23, 48505768, 'Héctor', 'Vargas', '2014-03-24', 7, 2, NULL, NULL, NULL, NULL, NULL),
(24, 52694857, 'Joaquín', 'Jiménez', '2014-11-02', 7, 2, NULL, NULL, NULL, NULL, NULL),
(25, 57492031, 'Camilo', 'Cándido', '2015-09-23', 7, 3, NULL, NULL, NULL, NULL, NULL),
(26, 42910594, 'Felipe', 'Carballo', '2015-11-02', 7, 3, NULL, NULL, NULL, NULL, NULL),
(27, 58503142, 'Alfonso', 'Trezza', '2015-02-14', 7, 3, NULL, NULL, NULL, NULL, NULL),
(28, 41809483, 'Brian', 'Ocampo', '2015-04-18', 7, 3, NULL, NULL, NULL, NULL, NULL),
(29, 59614253, 'Mathías', 'Laborda', '2015-06-22', 7, 3, NULL, NULL, NULL, NULL, NULL),
(30, 40798372, 'Leandro', 'Fernández', '2015-08-28', 7, 3, NULL, '2027-10-02', 0x696d672f666f746f732f466f746f5f34303739383337322e6a7067, 45.00, 1.40),
(31, 70563356, 'Juan', 'Pitoco', '2015-07-18', 6, 3, NULL, NULL, NULL, NULL, NULL),
(32, 42122312, 'Ignacio', 'Suárez', '2015-10-14', 6, 3, NULL, NULL, NULL, NULL, NULL),
(33, 51033223, 'Gabriel', 'Báez', '2015-09-10', 6, 3, NULL, NULL, NULL, NULL, NULL),
(34, 49944134, 'Mateo', 'Antoni', '2015-08-06', 6, 3, NULL, NULL, NULL, NULL, NULL),
(35, 58855045, 'Federico', 'Santander', '2015-07-02', 6, 3, NULL, NULL, NULL, NULL, NULL),
(36, 47765956, 'Ruben', 'Bentancourt', '2015-06-28', 6, 3, NULL, NULL, NULL, NULL, NULL),
(37, 50032223, 'Damian', 'García', '2016-09-12', 6, 4, NULL, NULL, NULL, NULL, NULL),
(38, 41121312, 'Nahuel', 'Acosta', '2016-10-16', 6, 4, NULL, NULL, NULL, NULL, NULL),
(39, 52210401, 'Diego', 'Zabala', '2016-01-08', 6, 4, NULL, NULL, NULL, NULL, NULL),
(40, 43309590, 'Christian', 'Oliva', '2016-02-12', 6, 4, NULL, NULL, NULL, NULL, NULL),
(41, 54498689, 'Alexis', 'Castro', '2016-03-16', 6, 4, NULL, NULL, NULL, NULL, NULL),
(42, 45587778, 'Jeremía', 'Recoba', '2016-04-20', 6, 4, NULL, NULL, NULL, NULL, NULL),
(43, 43344552, 'Gimena', 'Vega', '2010-12-02', 6, 8, NULL, NULL, NULL, NULL, NULL),
(44, 54455663, 'Isabella', 'Lara', '2008-02-06', 6, 8, NULL, NULL, NULL, NULL, NULL),
(45, 45566774, 'Lola', 'Cabrera', '2008-04-10', 6, 8, NULL, NULL, NULL, NULL, NULL),
(46, 56677885, 'Magdalena', 'Pinto', '2009-06-14', 6, 8, NULL, NULL, NULL, NULL, NULL),
(47, 47788996, 'Nuria', 'Núñez', '2009-08-18', 6, 8, NULL, NULL, NULL, NULL, NULL),
(48, 58899000, 'Olivia', 'Cáceres', '2010-10-22', 6, 8, NULL, NULL, NULL, NULL, NULL),
(49, 42308590, 'Diego', 'Aguirre', '2016-02-14', 3, 4, NULL, NULL, NULL, NULL, NULL),
(50, 53497689, 'Léo', 'Coelho', '2016-03-19', 3, 4, NULL, NULL, NULL, NULL, NULL),
(51, 44586778, 'Maximiliano', 'Olivera', '2016-04-23', 3, 4, NULL, NULL, NULL, NULL, NULL),
(52, 55675867, 'Javier', 'Cabrera', '2016-05-27', 3, 4, NULL, NULL, NULL, NULL, NULL),
(53, 46764956, 'Eduardo', 'Darias', '2016-06-30', 3, 4, NULL, NULL, NULL, NULL, NULL),
(54, 57854045, 'Lucas', 'Hernández', '2016-07-04', 3, 4, NULL, NULL, NULL, NULL, NULL),
(55, 50817263, 'Alan', 'Mendoza', '2017-01-05', 3, 5, NULL, NULL, NULL, NULL, NULL),
(56, 49726150, 'Bruno', 'Sanguinetti', '2017-02-09', 3, 5, NULL, NULL, NULL, NULL, NULL),
(57, 51635249, 'Cesar', 'Falco', '2017-03-13', 3, 5, NULL, NULL, NULL, NULL, NULL),
(58, 48544338, 'Daniel', 'Garat', '2017-04-17', 3, 5, NULL, NULL, NULL, NULL, NULL),
(59, 52553427, 'Edgardo', 'Viana', '2017-05-21', 3, 5, NULL, '2027-09-10', 0x696d672f666f746f732f466f746f5f35323535333432372e6a7067, 46.00, 1.50),
(60, 47462516, 'Fabian', 'López', '2017-06-25', 3, 5, NULL, NULL, NULL, NULL, NULL),
(61, 51122334, 'Sofía', 'Olivera', '2008-01-10', 3, 8, NULL, NULL, NULL, NULL, NULL),
(62, 42233445, 'Lucía', 'Rodríguez', '2008-03-14', 3, 8, NULL, NULL, NULL, NULL, NULL),
(63, 53344556, 'Martina', 'Fernández', '2009-05-18', 3, 8, NULL, NULL, NULL, NULL, NULL),
(64, 44455667, 'Camila', 'García', '2009-07-22', 3, 8, NULL, NULL, NULL, NULL, NULL),
(65, 55566778, 'Valentina', 'López', '2010-09-26', 3, 8, NULL, NULL, NULL, NULL, NULL),
(66, 57788990, 'Juliet', 'Pérez', '2008-02-04', 3, 8, NULL, NULL, NULL, NULL, NULL),
(67, 49199883, 'Leo', 'Ortega', '2017-12-24', 8, 7, NULL, NULL, NULL, NULL, NULL),
(68, 51008972, 'Marcelo', 'Paredes', '2017-01-28', 8, 7, NULL, NULL, NULL, NULL, NULL),
(69, 55644338, 'Rodrigo', 'Valdez', '2017-05-14', 8, 7, NULL, NULL, NULL, NULL, NULL),
(70, 58280794, 'Kevin', 'Núñez', '2017-11-20', 8, 7, NULL, NULL, NULL, NULL, NULL),
(71, 56462516, 'Ivan', 'Lara', '2017-09-12', 8, 7, NULL, NULL, NULL, NULL, NULL),
(72, 54644338, 'Gabriel', 'Ibarra', '2017-07-04', 8, 7, NULL, NULL, NULL, NULL, NULL),
(73, 84221535, 'Tomás', 'García', '2013-11-30', 8, 1, NULL, NULL, NULL, NULL, NULL),
(74, 7273866, 'Nicolás', 'Pérez', '2013-05-17', 8, 1, NULL, NULL, NULL, NULL, NULL),
(75, 72761242, 'Santiago', 'Martínez', '1764-06-19', 8, 1, NULL, '2027-06-18', NULL, 155.00, 2.15),
(76, 38825797, 'Emiliano', 'Silva', '2013-02-25', 8, 1, NULL, NULL, NULL, NULL, NULL),
(77, 16105573, 'Facundo', 'González', '2013-06-14', 8, 1, NULL, NULL, NULL, NULL, NULL),
(78, 54567890, 'Benjamín', 'Hernández', '2013-10-01', 8, 1, NULL, NULL, NULL, NULL, NULL),
(79, 57684867, 'Bautista', 'Iglesias', '2016-01-20', 8, 4, NULL, NULL, NULL, NULL, NULL),
(80, 42773956, 'Camilo', 'Fontana', '2016-03-18', 8, 4, NULL, NULL, NULL, NULL, NULL),
(81, 58863045, 'Dante', 'Varela', '2016-05-11', 8, 4, NULL, NULL, NULL, NULL, NULL),
(82, 41952134, 'Emiliano', 'Barrios', '2016-08-25', 8, 4, NULL, NULL, NULL, NULL, NULL),
(83, 59041223, 'Franco', 'Cabrera', '2016-09-09', 8, 4, NULL, '2028-02-03', NULL, 38.00, 1.34),
(84, 40130312, 'Gael', 'Espinosa', '2016-11-28', 8, 4, NULL, '2028-07-04', 0x696d672f666f746f732f466f746f5f34303133303331322e6a7067, 52.00, 1.30),
(85, 57554484, 'Pipe', 'Robertson', '2015-10-14', 6, 3, NULL, '2027-09-12', 0x696d672f666f746f732f466f746f5f35373535343438342e6a7067, 2.00, 3.00),
(87, 57011963, 'Pollo', 'Gallego', '2019-12-17', 7, 7, NULL, '2027-10-09', NULL, 15.00, 2.00),
(88, 57228269, 'Morena', 'Carrasco', '2015-04-01', 7, 8, NULL, '2028-10-26', NULL, 40.00, 1.50),
(89, 71629879, 'Julieta', 'Antunez', '2014-06-29', 7, 8, NULL, '2031-12-07', NULL, 40.00, 1.40),
(90, 55478711, 'Mayra', 'Nuñez', '2014-03-02', 7, 8, NULL, '2029-05-04', NULL, 39.00, 1.83),
(91, 57035363, 'Josepapu', 'Batnut segundo', '2013-06-12', 9, 1, NULL, '2028-12-15', NULL, 15.00, 2.78);

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

--
-- Volcado de datos para la tabla `lesiones`
--

INSERT INTO `lesiones` (`id`, `partido_id`, `jugador_id`, `descripcion`, `minuto`) VALUES
(6, 15, 40, 'Esguince de tobillo', 69);

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
(12, 1, 'Copa de Oro', 2, 8, '2026-09-08 12:30:00', 'jugado', 3, 1),
(14, 8, 'Liga', 6, 3, '2026-07-24 15:30:00', 'jugado', 2, 3),
(15, 4, 'Liga', 3, 6, '2026-09-26 16:00:00', 'jugado', 2, 4),
(16, 3, 'Copa de Plata', 2, 7, '2026-07-16 15:45:00', 'jugado', 0, 2),
(17, 1, 'Liga', 2, 8, '2026-11-14 14:00:00', 'programado', 0, 6),
(18, 2, 'Copa de Oro', 2, 7, '2026-10-04 16:00:00', 'jugado', 1, 3),
(19, 3, 'Copa de Plata', 3, 6, '2026-09-26 13:45:00', 'suspendido', 0, 0),
(20, 1, 'Liga', 2, 8, '2026-09-10 14:45:00', 'jugado', 0, 0);

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
(13, 9, 102, 'amarilla', 72),
(14, 14, 46, 'amarilla', 47),
(15, 14, 46, 'amarilla', 65),
(16, 12, 76, 'roja', 78),
(17, 15, 50, 'roja', 70),
(18, 18, 7, 'amarilla', 58);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `solicitudes_transferencia`
--

CREATE TABLE `solicitudes_transferencia` (
  `id` int(11) NOT NULL,
  `jugador_id` int(11) NOT NULL,
  `club_solicitante_id` int(11) NOT NULL,
  `club_origen_id` int(11) NOT NULL,
  `estado` enum('pendiente','aceptada','rechazada') NOT NULL DEFAULT 'pendiente',
  `fecha` datetime NOT NULL DEFAULT current_timestamp(),
  `fecha_respuesta` datetime DEFAULT NULL,
  `respondida_por` varchar(100) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `solicitudes_transferencia`
--

INSERT INTO `solicitudes_transferencia` (`id`, `jugador_id`, `club_solicitante_id`, `club_origen_id`, `estado`, `fecha`, `fecha_respuesta`, `respondida_por`) VALUES
(1, 189, 2, 6, 'pendiente', '2026-10-21 10:52:07', NULL, NULL),
(2, 173, 6, 7, 'aceptada', '2026-10-21 10:56:46', '2026-10-21 10:57:10', 'nacional@club.com');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `transferencias`
--

CREATE TABLE `transferencias` (
  `id` int(11) NOT NULL,
  `jugador_id` int(11) NOT NULL,
  `club_origen_id` int(11) NOT NULL,
  `club_destino_id` int(11) NOT NULL,
  `fecha` datetime NOT NULL DEFAULT current_timestamp(),
  `realizada_por` varchar(100) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `transferencias`
--

INSERT INTO `transferencias` (`id`, `jugador_id`, `club_origen_id`, `club_destino_id`, `fecha`, `realizada_por`) VALUES
(1, 240, 6, 7, '2026-10-21 10:28:16', 'admin@admin.com'),
(2, 191, 6, 2, '2026-10-21 10:35:11', 'admin@admin.com'),
(3, 191, 2, 6, '2026-10-21 10:36:08', 'admin@admin.com'),
(4, 251, 2, 3, '2026-10-21 10:36:27', 'admin@admin.com'),
(5, 251, 3, 2, '2026-10-21 10:36:37', 'admin@admin.com'),
(6, 173, 7, 6, '2026-10-21 10:57:10', 'nacional@club.com'),
(7, 251, 2, 7, '2026-10-21 10:58:46', 'admin@admin.com');

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
-- Indices de la tabla `solicitudes_transferencia`
--
ALTER TABLE `solicitudes_transferencia`
  ADD PRIMARY KEY (`id`),
  ADD KEY `jugador_id` (`jugador_id`),
  ADD KEY `club_solicitante_id` (`club_solicitante_id`),
  ADD KEY `club_origen_id` (`club_origen_id`);

--
-- Indices de la tabla `transferencias`
--
ALTER TABLE `transferencias`
  ADD PRIMARY KEY (`id`),
  ADD KEY `jugador_id` (`jugador_id`),
  ADD KEY `club_origen_id` (`club_origen_id`),
  ADD KEY `club_destino_id` (`club_destino_id`);

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
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=10;

--
-- AUTO_INCREMENT de la tabla `comunicados`
--
ALTER TABLE `comunicados`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=9;

--
-- AUTO_INCREMENT de la tabla `goles`
--
ALTER TABLE `goles`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=96;

--
-- AUTO_INCREMENT de la tabla `jugadores`
--
ALTER TABLE `jugadores`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=92;

--
-- AUTO_INCREMENT de la tabla `lesiones`
--
ALTER TABLE `lesiones`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT de la tabla `partidos`
--
ALTER TABLE `partidos`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=22;

--
-- AUTO_INCREMENT de la tabla `sanciones`
--
ALTER TABLE `sanciones`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=19;

--
-- AUTO_INCREMENT de la tabla `solicitudes_transferencia`
--
ALTER TABLE `solicitudes_transferencia`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT de la tabla `transferencias`
--
ALTER TABLE `transferencias`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=10;

--
-- Restricciones para tablas volcadas
--

--
-- Filtros para la tabla `jugadores`
--
ALTER TABLE `jugadores`
  ADD CONSTRAINT `jugadores_ibfk_1` FOREIGN KEY (`club_id`) REFERENCES `club` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `jugadores_ibfk_2` FOREIGN KEY (`categoria_id`) REFERENCES `categorias` (`id`);

--
-- Filtros para la tabla `partidos`
--
ALTER TABLE `partidos`
  ADD CONSTRAINT `partidos_ibfk_1` FOREIGN KEY (`categoria_id`) REFERENCES `categorias` (`id`),
  ADD CONSTRAINT `partidos_ibfk_2` FOREIGN KEY (`club_local_id`) REFERENCES `club` (`id`),
  ADD CONSTRAINT `partidos_ibfk_3` FOREIGN KEY (`club_visitante_id`) REFERENCES `club` (`id`);
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
