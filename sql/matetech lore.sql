-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Servidor: 127.0.0.1
-- Tiempo de generación: 21-10-2026 a las 16:06:02
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
-- Indices de la tabla `goles`
--
ALTER TABLE `goles`
  ADD PRIMARY KEY (`id`),
  ADD KEY `partido_id` (`partido_id`),
  ADD KEY `jugador_id` (`jugador_id`);

--
-- Indices de la tabla `lesiones`
--
ALTER TABLE `lesiones`
  ADD PRIMARY KEY (`id`),
  ADD KEY `partido_id` (`partido_id`),
  ADD KEY `jugador_id` (`jugador_id`);

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
-- AUTO_INCREMENT de la tabla `goles`
--
ALTER TABLE `goles`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=54;

--
-- AUTO_INCREMENT de la tabla `lesiones`
--
ALTER TABLE `lesiones`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT de la tabla `sanciones`
--
ALTER TABLE `sanciones`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=14;

--
-- AUTO_INCREMENT de la tabla `solicitudes_transferencia`
--
ALTER TABLE `solicitudes_transferencia`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT de la tabla `transferencias`
--
ALTER TABLE `transferencias`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=9;

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
-- Filtros para la tabla `lesiones`
--
ALTER TABLE `lesiones`
  ADD CONSTRAINT `lesiones_ibfk_1` FOREIGN KEY (`partido_id`) REFERENCES `partidos` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `lesiones_ibfk_2` FOREIGN KEY (`jugador_id`) REFERENCES `jugadores` (`id`) ON DELETE CASCADE;

--
-- Filtros para la tabla `sanciones`
--
ALTER TABLE `sanciones`
  ADD CONSTRAINT `sanciones_ibfk_1` FOREIGN KEY (`partido_id`) REFERENCES `partidos` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `sanciones_ibfk_2` FOREIGN KEY (`jugador_id`) REFERENCES `jugadores` (`id`) ON DELETE CASCADE;

--
-- Filtros para la tabla `solicitudes_transferencia`
--
ALTER TABLE `solicitudes_transferencia`
  ADD CONSTRAINT `sol_ibfk_1` FOREIGN KEY (`jugador_id`) REFERENCES `jugadores` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `sol_ibfk_2` FOREIGN KEY (`club_solicitante_id`) REFERENCES `club` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `sol_ibfk_3` FOREIGN KEY (`club_origen_id`) REFERENCES `club` (`id`) ON DELETE CASCADE;

--
-- Filtros para la tabla `transferencias`
--
ALTER TABLE `transferencias`
  ADD CONSTRAINT `transferencias_ibfk_1` FOREIGN KEY (`jugador_id`) REFERENCES `jugadores` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `transferencias_ibfk_2` FOREIGN KEY (`club_origen_id`) REFERENCES `club` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `transferencias_ibfk_3` FOREIGN KEY (`club_destino_id`) REFERENCES `club` (`id`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
