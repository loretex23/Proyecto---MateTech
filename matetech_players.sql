-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Servidor: 127.0.0.1
-- Tiempo de generación: 22-10-2026 a las 13:41:14
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
(2, 74836489, 'Pamela', 'Profe', '2013-01-07', 2, 1, NULL, NULL, NULL, NULL, NULL),
(3, 56526090, 'Rodrigo', 'Peña', '2013-08-04', 2, 1, NULL, NULL, NULL, NULL, NULL),
(4, 51234567, 'Mateo', 'Rodríguez', '2013-03-12', 2, 1, NULL, NULL, NULL, NULL, NULL),
(5, 58901234, 'Lautaro', 'Gómez', '2013-01-28', 2, 1, NULL, NULL, NULL, NULL, NULL),
(6, 50123456, 'Agustín', 'López', '2013-01-08', 2, 1, NULL, '2027-07-08', 0x696d672f666f746f732f466f746f5f35303132333435362e6a7067, 51.00, 1.57),
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
(73, 48765432, 'Tomás', 'García', '2013-11-30', 8, 1, NULL, NULL, NULL, NULL, NULL),
(74, 52345678, 'Nicolás', 'Pérez', '2013-05-17', 8, 1, NULL, NULL, NULL, NULL, NULL),
(75, 47654321, 'Santiago', 'Martínez', '2013-09-04', 8, 1, NULL, NULL, NULL, NULL, NULL),
(76, 53456789, 'Emiliano', 'Silva', '2013-02-25', 8, 1, NULL, NULL, NULL, NULL, NULL),
(77, 46543210, 'Facundo', 'González', '2013-06-14', 8, 1, NULL, NULL, NULL, NULL, NULL),
(78, 54567890, 'Benjamín', 'Hernández', '2013-10-01', 8, 1, NULL, NULL, NULL, NULL, NULL),
(79, 57684867, 'Bautista', 'Iglesias', '2016-01-20', 8, 4, NULL, NULL, NULL, NULL, NULL),
(80, 42773956, 'Camilo', 'Fontana', '2016-03-18', 8, 4, NULL, NULL, NULL, NULL, NULL),
(81, 58863045, 'Dante', 'Varela', '2016-05-11', 8, 4, NULL, NULL, NULL, NULL, NULL),
(82, 41952134, 'Emiliano', 'Barrios', '2016-08-25', 8, 4, NULL, NULL, NULL, NULL, NULL),
(83, 59041223, 'Franco', 'Cabrera', '2016-09-09', 8, 4, NULL, NULL, NULL, NULL, NULL),
(84, 40130312, 'Gael', 'Espinosa', '2016-11-28', 8, 4, NULL, '2028-07-04', 0x696d672f666f746f732f466f746f5f34303133303331322e6a7067, 52.00, 1.30);

--
-- Índices para tablas volcadas
--

--
-- Indices de la tabla `jugadores`
--
ALTER TABLE `jugadores`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `ci` (`ci`),
  ADD KEY `club_id` (`club_id`),
  ADD KEY `categoria_id` (`categoria_id`);

--
-- AUTO_INCREMENT de las tablas volcadas
--

--
-- AUTO_INCREMENT de la tabla `jugadores`
--
ALTER TABLE `jugadores`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=85;

--
-- Restricciones para tablas volcadas
--

--
-- Filtros para la tabla `jugadores`
--
ALTER TABLE `jugadores`
  ADD CONSTRAINT `jugadores_ibfk_1` FOREIGN KEY (`club_id`) REFERENCES `club` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `jugadores_ibfk_2` FOREIGN KEY (`categoria_id`) REFERENCES `categorias` (`id`);
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
