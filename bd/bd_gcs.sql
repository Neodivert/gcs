-- phpMyAdmin SQL Dump
-- version 3.4.5
-- http://www.phpmyadmin.net
--
-- Servidor: localhost
-- Tiempo de generación: 20-06-2012 a las 03:10:11
-- Versión del servidor: 5.5.16
-- Versión de PHP: 5.3.8

SET SQL_MODE="NO_AUTO_VALUE_ON_ZERO";
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8 */;

--
-- Base de datos: `gcs`
--

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `compilers`
--

CREATE TABLE IF NOT EXISTS `compilers` (
  `id` bigint(20) NOT NULL AUTO_INCREMENT,
  `progLanguageId` bigint(20) NOT NULL,
  `operativeSystem` enum('GNU/Linux','Windows') NOT NULL,
  `architecture` enum('32 bits','64 bits') NOT NULL,
  `compiler` varchar(30) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `progLanguageId` (`progLanguageId`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 AUTO_INCREMENT=10 ;

--
-- Volcado de datos para la tabla `compilers`
--

INSERT INTO `compilers` (`id`, `progLanguageId`, `operativeSystem`, `architecture`, `compiler`) VALUES
(2, 2, 'GNU/Linux', '32 bits', 'gcc -m32'),
(3, 2, 'GNU/Linux', '64 bits', 'gcc -m64'),
(4, 3, 'GNU/Linux', '32 bits', 'g++ -m32'),
(5, 3, 'GNU/Linux', '64 bits', 'g++ -m64'),
(6, 2, 'Windows', '32 bits', 'i686-w64-mingw32-gcc'),
(7, 2, 'Windows', '64 bits', 'x86_64-w64-mingw32-gcc'),
(8, 3, 'Windows', '32 bits', 'i686-w64-mingw32-g++'),
(9, 3, 'Windows', '64 bits', 'x86_64-w64-mingw32-gcc');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `openFiles`
--

CREATE TABLE IF NOT EXISTS `openFiles` (
  `id` bigint(20) NOT NULL AUTO_INCREMENT,
  `ownerId` bigint(20) NOT NULL,
  `name` varchar(40) NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `name` (`id`,`name`),
  KEY `ownerId` (`ownerId`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 AUTO_INCREMENT=224 ;


-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `progLanguages`
--

CREATE TABLE IF NOT EXISTS `progLanguages` (
  `id` bigint(20) NOT NULL AUTO_INCREMENT,
  `name` varchar(10) NOT NULL,
  `header_extension` varchar(10) NOT NULL,
  `source_extension` varchar(10) NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `name` (`name`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 AUTO_INCREMENT=4 ;

--
-- Volcado de datos para la tabla `progLanguages`
--

INSERT INTO `progLanguages` (`id`, `name`, `header_extension`, `source_extension`) VALUES
(2, 'C', '.h', '.c'),
(3, 'C++', '.hpp', '.cpp');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `projects`
--

CREATE TABLE IF NOT EXISTS `projects` (
  `id` bigint(20) NOT NULL AUTO_INCREMENT,
  `ownerId` bigint(20) NOT NULL,
  `name` varchar(30) NOT NULL,
  `description` text,
  `progLanguageId` bigint(20) NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `project` (`ownerId`,`name`),
  KEY `ownerId` (`ownerId`),
  KEY `progLanguageId` (`progLanguageId`),
  KEY `name` (`name`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 AUTO_INCREMENT=88 ;


-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `users`
--

CREATE TABLE IF NOT EXISTS `users` (
  `id` bigint(20) NOT NULL AUTO_INCREMENT,
  `name` varchar(30) NOT NULL,
  `password` varchar(32) NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `name` (`name`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 AUTO_INCREMENT=15 ;


--
-- Restricciones para tablas volcadas
--

--
-- Filtros para la tabla `compilers`
--
ALTER TABLE `compilers`
  ADD CONSTRAINT `compilers_ibfk_1` FOREIGN KEY (`progLanguageId`) REFERENCES `progLanguages` (`id`) ON UPDATE CASCADE;

--
-- Filtros para la tabla `openFiles`
--
ALTER TABLE `openFiles`
  ADD CONSTRAINT `openFiles_ibfk_1` FOREIGN KEY (`ownerId`) REFERENCES `users` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Filtros para la tabla `projects`
--
ALTER TABLE `projects`
  ADD CONSTRAINT `projects_ibfk_1` FOREIGN KEY (`ownerId`) REFERENCES `users` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `projects_ibfk_2` FOREIGN KEY (`progLanguageId`) REFERENCES `progLanguages` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
