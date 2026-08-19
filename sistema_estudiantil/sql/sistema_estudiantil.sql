-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Servidor: 127.0.0.1
-- Tiempo de generación: 12-08-2026 a las 08:10:08
-- Versión del servidor: 10.4.32-MariaDB
-- Versión de PHP: 8.1.25

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Base de datos: `sistema_estudiantil`
--

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `alertas`
--

CREATE TABLE `alertas` (
  `id` int(11) NOT NULL,
  `estudiante_id` int(11) NOT NULL,
  `estudiante_nombre` varchar(100) NOT NULL,
  `telefono_padre` varchar(20) NOT NULL,
  `mensaje` text NOT NULL,
  `leido` tinyint(1) DEFAULT 0,
  `creado` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `asistencia`
--

CREATE TABLE `asistencia` (
  `id` int(11) NOT NULL,
  `estudiante_id` int(11) NOT NULL,
  `estudiante_nombre` varchar(100) NOT NULL,
  `estudiante_codigo` varchar(20) NOT NULL,
  `tipo` enum('entrada','salida') NOT NULL,
  `fecha_hora` timestamp NOT NULL DEFAULT current_timestamp(),
  `ubicacion` varchar(100) NOT NULL,
  `validado_por` int(11) NOT NULL,
  `fecha` date NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `estudiantes`
--

CREATE TABLE `estudiantes` (
  `id` int(11) NOT NULL,
  `nombre` varchar(50) NOT NULL,
  `apellido` varchar(50) NOT NULL,
  `dni` varchar(20) NOT NULL,
  `codigo_estudiante` varchar(20) NOT NULL,
  `curso` varchar(20) NOT NULL,
  `seccion` varchar(10) NOT NULL,
  `jornada` enum('Matutina','Vespertina') NOT NULL,
  `fecha_nac` date NOT NULL,
  `telefono_padre` varchar(20) NOT NULL,
  `email_padre` varchar(100) DEFAULT NULL,
  `codigo_qr` varchar(255) NOT NULL,
  `carnet_number` varchar(20) NOT NULL,
  `activo` tinyint(1) DEFAULT 1,
  `creado` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `estudiantes`
--

INSERT INTO `estudiantes` (`id`, `nombre`, `apellido`, `dni`, `codigo_estudiante`, `curso`, `seccion`, `jornada`, `fecha_nac`, `telefono_padre`, `email_padre`, `codigo_qr`, `carnet_number`, `activo`, `creado`) VALUES
(1, 'Juan', 'Pérez', '0801199900001', 'EST-2026-001', '7mo', 'A', 'Matutina', '2014-05-15', '98765432', 'padre@email.com', 'QR-001', 'CAR-0001', 1, '2026-08-12 05:50:27'),
(2, 'María', 'García', '0801199900002', 'EST-2026-002', '8vo', 'B', 'Vespertina', '2013-08-20', '87654321', 'padre2@email.com', 'QR-002', 'CAR-0002', 1, '2026-08-12 05:50:27'),
(3, 'Carlos', 'Rodríguez', '0801199900003', 'EST-2026-003', '9no', 'A', 'Matutina', '2012-03-10', '76543210', 'padre3@email.com', 'QR-003', 'CAR-0003', 1, '2026-08-12 05:50:27');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `usuarios`
--

CREATE TABLE `usuarios` (
  `id` int(11) NOT NULL,
  `email` varchar(100) DEFAULT NULL,
  `password` varchar(255) DEFAULT NULL,
  `nombre` varchar(100) NOT NULL,
  `rol` enum('admin','guardia','consejero','padre') NOT NULL,
  `telefono` varchar(20) DEFAULT NULL,
  `ubicacion` varchar(100) DEFAULT NULL,
  `activo` tinyint(1) DEFAULT 1,
  `creado` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `usuarios`
--

INSERT INTO `usuarios` (`id`, `email`, `password`, `nombre`, `rol`, `telefono`, `ubicacion`, `activo`, `creado`) VALUES
(1, 'admin@dhs.com', 'e10adc3949ba59abbe56e057f20f883e', 'Administrador', 'admin', NULL, NULL, 1, '2026-08-12 05:50:27'),
(2, 'guardia@dhs.com', '827ccb0eea8a706c4c34a16891f84e7b', 'Guardia de Seguridad', 'guardia', NULL, 'Puerta Principal', 1, '2026-08-12 05:50:27'),
(3, 'consejero@dhs.com', '827ccb0eea8a706c4c34a16891f84e7b', 'Consejero Escolar', 'consejero', NULL, NULL, 1, '2026-08-12 05:50:27');

--
-- Índices para tablas volcadas
--

--
-- Indices de la tabla `alertas`
--
ALTER TABLE `alertas`
  ADD PRIMARY KEY (`id`),
  ADD KEY `estudiante_id` (`estudiante_id`);

--
-- Indices de la tabla `asistencia`
--
ALTER TABLE `asistencia`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `uq_asistencia_estudiante_fecha_tipo` (`estudiante_id`,`fecha`,`tipo`),
  ADD KEY `estudiante_id` (`estudiante_id`),
  ADD KEY `validado_por` (`validado_por`);

--
-- Indices de la tabla `estudiantes`
--
ALTER TABLE `estudiantes`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `dni` (`dni`),
  ADD UNIQUE KEY `codigo_estudiante` (`codigo_estudiante`),
  ADD UNIQUE KEY `codigo_qr` (`codigo_qr`),
  ADD UNIQUE KEY `carnet_number` (`carnet_number`);

--
-- Indices de la tabla `usuarios`
--
ALTER TABLE `usuarios`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `email` (`email`),
  ADD UNIQUE KEY `telefono` (`telefono`);

--
-- AUTO_INCREMENT de las tablas volcadas
--

--
-- AUTO_INCREMENT de la tabla `alertas`
--
ALTER TABLE `alertas`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `asistencia`
--
ALTER TABLE `asistencia`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `estudiantes`
--
ALTER TABLE `estudiantes`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT de la tabla `usuarios`
--
ALTER TABLE `usuarios`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- Restricciones para tablas volcadas
--

--
-- Filtros para la tabla `alertas`
--
ALTER TABLE `alertas`
  ADD CONSTRAINT `alertas_ibfk_1` FOREIGN KEY (`estudiante_id`) REFERENCES `estudiantes` (`id`) ON DELETE CASCADE;

--
-- Filtros para la tabla `asistencia`
--
ALTER TABLE `asistencia`
  ADD CONSTRAINT `asistencia_ibfk_1` FOREIGN KEY (`estudiante_id`) REFERENCES `estudiantes` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `asistencia_ibfk_2` FOREIGN KEY (`validado_por`) REFERENCES `usuarios` (`id`);
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
