-- Tiempo de generación: 13-11-2021 a las 16:11:57
-- Versión del servidor: 8.0.27-0ubuntu0.20.04.1
-- Versión de PHP: 7.4.3

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
SET AUTOCOMMIT = 0;
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Base de datos: `monnet`
--

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `hosts`
--

CREATE TABLE `hosts` (
  `id` int NOT NULL,
  `title` char(255) CHARACTER SET utf8 COLLATE utf8_general_ci DEFAULT NULL,
  `hostname` varchar(1024) CHARACTER SET utf8 COLLATE utf8_general_ci DEFAULT NULL,
  `ip` char(255) NOT NULL,
  `mac` char(255) DEFAULT NULL,
  `mac_vendor` char(255) CHARACTER SET utf8 COLLATE utf8_general_ci DEFAULT NULL,
  `highlight` tinyint(1) NOT NULL DEFAULT '0',
  `check_method` tinyint NOT NULL DEFAULT '1',
  `system` char(255) CHARACTER SET utf8 COLLATE utf8_general_ci DEFAULT NULL,
  `distributor` char(255) CHARACTER SET utf8 COLLATE utf8_general_ci DEFAULT NULL,
  `codename` char(255) DEFAULT NULL,
  `version` char(255) CHARACTER SET utf8 COLLATE utf8_general_ci DEFAULT NULL,
  `img_ico` char(255) CHARACTER SET utf8 COLLATE utf8_general_ci DEFAULT NULL,
  `weight` tinyint NOT NULL DEFAULT '60',
  `status` tinyint NOT NULL DEFAULT '0' COMMENT '0:ok;1:warn;3:danger',
  `online` tinyint NOT NULL DEFAULT '0',
  `access_method` tinyint NOT NULL DEFAULT '0' COMMENT '0:no;1:ssh..',
  `wol` tinyint NOT NULL DEFAULT '0',
  `timeout` tinyint DEFAULT NULL,
  `disable` tinyint NOT NULL DEFAULT '0',
  `clilog` varchar(2048) CHARACTER SET utf8 COLLATE utf8_general_ci DEFAULT NULL COMMENT 'clilog keep extra cli logs',
  `warn` tinyint NOT NULL DEFAULT '0',
  `warn_port` tinyint NOT NULL DEFAULT '0',
  `updated` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `comment` varchar(2048) CHARACTER SET utf8 COLLATE utf8_general_ci DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3;


--
-- Estructura de tabla para la tabla `items`
--

CREATE TABLE `items` (
  `id` int NOT NULL,
  `type` char(255) NOT NULL,
  `title` char(255) NOT NULL,
  `conf` varchar(4096) NOT NULL,
  `weight` tinyint NOT NULL DEFAULT '60'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3;

--
-- Estructura de tabla para la tabla `load_stats`
--

CREATE TABLE `load_stats` (
  `timestamp` timestamp NOT NULL,
  `host` int NOT NULL,
  `value` tinyint NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `ping_stats`
--

CREATE TABLE `ping_stats` (
  `timestamp` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `host` int DEFAULT NULL,
  `value` tinyint DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `ports`
--

CREATE TABLE `ports` (
  `id` int NOT NULL,
  `hid` int NOT NULL,
  `port` smallint NOT NULL,
  `port_type` tinyint NOT NULL DEFAULT '1' COMMENT '1:tcp;2:udp',
  `title` char(255) NOT NULL,
  `icon` char(255) DEFAULT NULL,
  `online` tinyint NOT NULL DEFAULT '0',
  `clilog` varchar(2048) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `prefs`
--

CREATE TABLE `prefs` (
  `id` int NOT NULL,
  `uid` int NOT NULL,
  `pref_name` char(255) NOT NULL,
  `pref_value` char(255) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3;

--
-- Estructura de tabla para la tabla `users`
--

CREATE TABLE `users` (
  `id` int NOT NULL,
  `username` char(255) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL,
  `email` char(255) CHARACTER SET utf8 COLLATE utf8_general_ci DEFAULT NULL,
  `password` char(255) CHARACTER SET utf8 COLLATE utf8_bin NOT NULL,
  `sid` char(255) CHARACTER SET utf8 COLLATE utf8_general_ci DEFAULT NULL,
  `isAdmin` tinyint NOT NULL DEFAULT '0',
  `created` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3;

--
-- Volcado de datos para la tabla `users`
--

INSERT INTO `users` (`id`, `username`, `email`, `password`, `sid`, `isAdmin`, `created`) VALUES
(1, 'monnet', NULL, '50fbd2ffa0f3e68cb2d7bc818d63f29cf3a4df10', '6969ongm6grfet4s6u9uinmssa', 1, '2021-10-30 12:06:20');

--
-- Índices para tablas volcadas
--

--
-- Indices de la tabla `hosts`
--
ALTER TABLE `hosts`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `ip` (`ip`);

--
-- Indices de la tabla `items`
--
ALTER TABLE `items`
  ADD PRIMARY KEY (`id`);

--
-- Indices de la tabla `load_stats`
--
ALTER TABLE `load_stats`
  ADD PRIMARY KEY (`timestamp`);

--
-- Indices de la tabla `ping_stats`
--
ALTER TABLE `ping_stats`
  ADD PRIMARY KEY (`timestamp`);

--
-- Indices de la tabla `ports`
--
ALTER TABLE `ports`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `hid` (`hid`,`port`);

--
-- Indices de la tabla `prefs`
--
ALTER TABLE `prefs`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `uid` (`uid`,`pref_name`);

--
-- Indices de la tabla `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `username` (`username`),
  ADD KEY `username_2` (`username`);

--
-- AUTO_INCREMENT de las tablas volcadas
--

--
-- AUTO_INCREMENT de la tabla `hosts`
--
ALTER TABLE `hosts`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=1;

--
-- AUTO_INCREMENT de la tabla `items`
--
ALTER TABLE `items`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=1;

--
-- AUTO_INCREMENT de la tabla `ports`
--
ALTER TABLE `ports`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=1;

--
-- AUTO_INCREMENT de la tabla `prefs`
--
ALTER TABLE `prefs`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=1;

--
-- AUTO_INCREMENT de la tabla `users`
--
ALTER TABLE `users`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
