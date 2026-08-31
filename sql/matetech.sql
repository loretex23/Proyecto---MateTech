-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Servidor: 127.0.0.1
-- Tiempo de generación: 31-08-2026 a las 12:41:40
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
(2, 'club1@gmail.com', '$2y$10$wYi6jA2Pma/PwMTKGmKUeuTKGuwMMV5m4ku25tSoDX59V5z5HoJSe', 'MártirFC', NULL, 'Club', NULL, NULL, NULL, NULL),
(3, 'club2@gmail.com', '$2y$10$IgO2Gv8Lc/mJo/qO.8RqhuwIXbXHDiQZE.tDACdzyWhwxHhCOPHf2', 'SinCamisitaFC', NULL, 'Club', NULL, NULL, NULL, NULL),
(4, 'prueba@prueba.com', '$2y$10$WIncicxc3WrJah3Mzr3rUejuiRoOfWrsBiP3.c1N/1hrv/LkVOYGa', 'Prueba', NULL, 'Club', 'Chris', 'Lore', 'Joaco', 'Lucas'),
(5, 'pipefc@gmail.com', '$2y$10$miHmr1omV9JffwjMRXPlDOImkiLYne/sPkENgnh9zSH5TBs4sNGie', 'PipeFC', NULL, 'Club', NULL, NULL, NULL, NULL);

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
(4, 'iugiugiu', 'ouhouhoug', NULL, '2026-08-25 02:17:28', 'General');

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
(7, 5, 42, 10, 'penal'),
(8, 5, 41, 15, 'normal'),
(14, 6, 67, 1, 'normal'),
(15, 6, 67, 15, 'normal'),
(16, 6, 69, 30, 'normal'),
(17, 6, 62, 89, 'autogol'),
(18, 6, 65, 10, 'penal'),
(19, 6, 64, 90, 'normal');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `jugadores`
--

CREATE TABLE `jugadores` (
  `id` int(11) NOT NULL,
  `ci` varchar(20) NOT NULL,
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
(25, '10000001', 'Mateo', 'Rodríguez', '2013-03-12', 2, 1, NULL, NULL, NULL, 38.50, 1.42),
(26, '10000002', 'Luca', 'Fernández', '2013-07-22', 2, 1, NULL, NULL, NULL, 40.00, 1.45),
(27, '10000003', 'Agustín', 'López', '2013-01-08', 2, 1, NULL, NULL, NULL, 37.00, 1.40),
(28, '10000004', 'Tomás', 'García', '2013-11-30', 3, 1, NULL, NULL, NULL, 39.50, 1.43),
(29, '10000005', 'Nicolás', 'Pérez', '2013-05-17', 3, 1, NULL, NULL, NULL, 41.00, 1.46),
(30, '10000006', 'Santiago', 'Martínez', '2013-09-04', 3, 1, NULL, NULL, NULL, 38.00, 1.41),
(31, '10000007', 'Emiliano', 'Silva', '2013-02-25', 2, 1, NULL, NULL, NULL, 40.50, 1.44),
(32, '10000008', 'Facundo', 'González', '2013-06-14', 3, 1, NULL, NULL, NULL, 37.50, 1.39),
(33, '10000009', 'Benjamín', 'Hernández', '2013-10-01', 2, 1, NULL, NULL, NULL, 39.00, 1.42),
(34, '10000010', 'Axel', 'Suárez', '2014-04-18', 2, 2, NULL, NULL, NULL, 36.00, 1.38),
(35, '10000011', 'Bruno', 'Álvarez', '2014-08-09', 2, 2, NULL, NULL, NULL, 37.50, 1.40),
(36, '10000012', 'Damián', 'Torres', '2014-12-20', 2, 2, NULL, NULL, NULL, 35.50, 1.37),
(37, '10000013', 'Ezequiel', 'Ramírez', '2014-02-11', 3, 2, NULL, NULL, NULL, 38.00, 1.41),
(38, '10000014', 'Felipe', 'Sánchez', '2014-06-30', 3, 2, NULL, NULL, NULL, 36.50, 1.39),
(39, '10000015', 'Gabriel', 'Castro', '2014-10-07', 3, 2, NULL, NULL, NULL, 37.00, 1.38),
(40, '10000016', 'Héctor', 'Vargas', '2014-03-24', 2, 2, NULL, NULL, NULL, 35.00, 1.36),
(41, '10000017', 'Iván', 'Morales', '2014-07-15', 3, 2, NULL, NULL, NULL, 38.50, 1.42),
(42, '10000018', 'Joaquín', 'Jiménez', '2014-11-02', 2, 2, NULL, NULL, NULL, 36.00, 1.37),
(43, '10000019', 'Kevin', 'Reyes', '2015-01-27', 2, 3, NULL, NULL, NULL, 33.50, 1.33),
(44, '10000020', 'Leandro', 'Cruz', '2015-05-16', 2, 3, NULL, NULL, NULL, 34.00, 1.34),
(45, '10000021', 'Marcos', 'Flores', '2015-09-03', 2, 3, NULL, NULL, NULL, 32.50, 1.32),
(46, '10000022', 'Nahuel', 'Rivera', '2015-02-22', 3, 3, NULL, NULL, NULL, 35.00, 1.35),
(47, '10000023', 'Oscar', 'Herrera', '2015-06-11', 3, 3, NULL, NULL, NULL, 33.00, 1.33),
(48, '10000024', 'Pablo', 'Romero', '2015-10-28', 3, 3, NULL, NULL, NULL, 34.50, 1.34),
(49, '10000025', 'Rodrigo', 'Díaz', '2015-03-09', 2, 3, NULL, NULL, NULL, 32.00, 1.31),
(50, '10000026', 'Sebastián', 'Medina', '2015-07-19', 3, 3, NULL, NULL, NULL, 33.50, 1.32),
(51, '10000027', 'Thiago', 'Torres', '2015-11-14', 2, 3, NULL, NULL, NULL, 34.00, 1.35),
(52, '10000028', 'Ulises', 'Ortega', '2016-01-05', 2, 4, NULL, NULL, NULL, 30.00, 1.28),
(53, '10000029', 'Valentín', 'Espinoza', '2016-04-23', 2, 4, NULL, NULL, NULL, 31.50, 1.30),
(54, '10000030', 'Walter', 'Mendoza', '2016-08-12', 2, 4, NULL, NULL, NULL, 29.50, 1.27),
(55, '10000031', 'Xavier', 'Ríos', '2016-12-01', 3, 4, NULL, NULL, NULL, 32.00, 1.31),
(56, '10000032', 'Yael', 'Gutiérrez', '2016-03-17', 3, 4, NULL, NULL, NULL, 30.50, 1.29),
(57, '10000033', 'Zacarías', 'Núñez', '2016-07-06', 3, 4, NULL, NULL, NULL, 31.00, 1.28),
(58, '10000034', 'Alan', 'Campos', '2016-11-25', 2, 4, NULL, NULL, NULL, 29.00, 1.26),
(59, '10000035', 'Bryan', 'Ramos', '2016-02-14', 3, 4, NULL, NULL, NULL, 32.50, 1.32),
(60, '10000036', 'Carlos', 'Varela', '2016-06-03', 2, 4, NULL, NULL, NULL, 30.00, 1.28),
(61, '10000037', 'Diego', 'Ibáñez', '2017-01-18', 2, 5, NULL, NULL, NULL, 26.50, 1.22),
(62, '10000038', 'Eduardo', 'Paredes', '2017-05-07', 2, 5, NULL, NULL, NULL, 27.00, 1.23),
(63, '10000039', 'Fernando', 'Aguilar', '2017-09-26', 2, 5, NULL, NULL, NULL, 25.50, 1.21),
(64, '10000040', 'Gonzalo', 'Mora', '2017-02-13', 3, 5, NULL, NULL, NULL, 28.00, 1.24),
(65, '10000041', 'Horacio', 'Lara', '2017-06-02', 3, 5, NULL, NULL, NULL, 26.00, 1.22),
(66, '10000042', 'Ignacio', 'Vega', '2017-10-21', 3, 5, NULL, NULL, NULL, 27.50, 1.23),
(67, '10000043', 'Javier', 'Ponce', '2017-03-08', 2, 5, NULL, NULL, NULL, 25.00, 1.20),
(68, '10000044', 'Kristian', 'Salinas', '2017-07-17', 3, 5, NULL, NULL, NULL, 28.50, 1.25),
(69, '10000045', 'Luis', 'Palomino', '2017-11-04', 2, 5, NULL, NULL, NULL, 26.00, 1.21),
(70, '10000046', 'Miguel', 'Espejo', '2018-01-30', 2, 6, NULL, NULL, NULL, 22.00, 1.15),
(71, '10000047', 'Nicolás', 'Huerta', '2018-05-19', 2, 6, NULL, NULL, NULL, 23.50, 1.17),
(72, '10000048', 'Omar', 'Delgado', '2018-09-08', 2, 6, NULL, NULL, NULL, 21.50, 1.14),
(73, '10000049', 'Pedro', 'Cáceres', '2018-02-24', 3, 6, NULL, NULL, NULL, 24.00, 1.18),
(74, '10000050', 'Quintin', 'Barrios', '2018-06-13', 3, 6, NULL, NULL, NULL, 22.50, 1.15),
(75, '10000051', 'Rafael', 'Andrade', '2018-10-30', 3, 6, NULL, NULL, NULL, 23.00, 1.16),
(76, '10000052', 'Samuel', 'Carrillo', '2018-03-06', 2, 6, NULL, NULL, NULL, 21.00, 1.13),
(77, '10000053', 'Tobías', 'Peña', '2018-07-25', 3, 6, NULL, NULL, NULL, 24.50, 1.19),
(78, '10000054', 'Uriel', 'Sandoval', '2018-11-14', 2, 6, NULL, NULL, NULL, 22.00, 1.14),
(79, '10000055', 'Víctor', 'Montes', '2019-01-09', 2, 7, NULL, NULL, NULL, 18.00, 1.08),
(80, '10000056', 'William', 'Fuentes', '2019-04-28', 2, 7, NULL, NULL, NULL, 18.50, 1.09),
(81, '10000057', 'Alexis', 'Pedraza', '2019-08-17', 2, 7, NULL, NULL, NULL, 17.50, 1.07),
(82, '10000058', 'Boris', 'Contreras', '2019-12-06', 3, 7, NULL, NULL, NULL, 19.00, 1.10),
(83, '10000059', 'Cristian', 'Zamora', '2019-03-23', 3, 7, NULL, NULL, NULL, 18.00, 1.08),
(84, '10000060', 'Dante', 'Quispe', '2019-07-12', 3, 7, NULL, NULL, NULL, 17.00, 1.06),
(85, '10000061', 'Emilio', 'Centeno', '2019-11-01', 2, 7, NULL, NULL, NULL, 19.50, 1.11),
(86, '10000062', 'Franco', 'Bernal', '2019-02-17', 3, 7, NULL, NULL, NULL, 18.00, 1.08),
(87, '10000063', 'Gaspar', 'Villalba', '2019-06-04', 2, 7, NULL, NULL, NULL, 17.50, 1.07),
(88, '10000064', 'Valentina', 'Acosta', '2008-02-10', 2, 8, NULL, NULL, NULL, 52.00, 1.60),
(89, '10000065', 'Florencia', 'Benítez', '2009-06-25', 2, 8, NULL, NULL, NULL, 54.50, 1.62),
(90, '10000066', 'Camila', 'Cardozo', '2010-11-14', 2, 8, NULL, NULL, NULL, 50.00, 1.58),
(91, '10000067', 'Luciana', 'Domínguez', '2008-04-03', 3, 8, NULL, NULL, NULL, 55.00, 1.63),
(92, '10000068', 'Martina', 'Estévez', '2009-08-22', 3, 8, NULL, NULL, NULL, 51.50, 1.59),
(93, '10000069', 'Natalia', 'Figueroa', '2010-12-11', 3, 8, NULL, NULL, NULL, 53.00, 1.61),
(94, '10000070', 'Paola', 'Godoy', '2008-01-30', 2, 8, NULL, NULL, NULL, 56.00, 1.64),
(95, '10000071', 'Renata', 'Ibarra', '2009-05-19', 3, 8, NULL, NULL, NULL, 50.50, 1.58),
(96, '10000072', 'Sofía', 'Jiménez', '2010-09-08', 2, 8, NULL, NULL, NULL, 52.50, 1.60);

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
(1, 5, 41, 'Desgarro', 89),
(2, 6, 65, 'Se quebró el tobillo', 78);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `partidos`
--

CREATE TABLE `partidos` (
  `id` int(11) NOT NULL,
  `categoria_id` int(11) NOT NULL,
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

INSERT INTO `partidos` (`id`, `categoria_id`, `club_local_id`, `club_visitante_id`, `fecha_partido`, `estado`, `goles_local`, `goles_visitante`) VALUES
(5, 2, 2, 3, '2026-08-28 08:23:00', 'jugado', 1, 1),
(6, 5, 2, 3, '2026-08-29 10:30:00', 'jugado', 4, 2);

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
(6, 5, 42, 'roja', 40),
(7, 6, 69, 'roja', 90);

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
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=9;

--
-- AUTO_INCREMENT de la tabla `club`
--
ALTER TABLE `club`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT de la tabla `comunicados`
--
ALTER TABLE `comunicados`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT de la tabla `goles`
--
ALTER TABLE `goles`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=20;

--
-- AUTO_INCREMENT de la tabla `jugadores`
--
ALTER TABLE `jugadores`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=97;

--
-- AUTO_INCREMENT de la tabla `lesiones`
--
ALTER TABLE `lesiones`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT de la tabla `partidos`
--
ALTER TABLE `partidos`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT de la tabla `sanciones`
--
ALTER TABLE `sanciones`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8;

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
