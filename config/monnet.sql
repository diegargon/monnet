SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";

--
-- Database: `monnet`
--

-- --------------------------------------------------------

--
-- Table structure for table `categories`
--

CREATE TABLE `categories` (
  `id` int NOT NULL,
  `cat_type` tinyint NOT NULL COMMENT '1 Hosts 2 Items',
  `cat_name` char(32) CHARACTER SET utf8mb3 COLLATE utf8mb3_general_ci NOT NULL,
  `disable` tinyint NOT NULL DEFAULT '0',
  `weight` tinyint NOT NULL DEFAULT '0'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3;

-- --------------------------------------------------------

--
-- Table structure for table `cmd`
--

CREATE TABLE `cmd` (
  `cmd_id` int NOT NULL,
  `hid` char(255) CHARACTER SET utf8mb3 COLLATE utf8mb3_general_ci NOT NULL,
  `cmd_type` smallint NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3;

-- --------------------------------------------------------

--
-- Table structure for table `hosts`
--

CREATE TABLE `hosts` (
  `id` int NOT NULL,
  `title` char(32) CHARACTER SET utf8mb3 COLLATE utf8mb3_general_ci DEFAULT NULL,
  `hostname` varchar(1024) CHARACTER SET utf8mb3 COLLATE utf8mb3_general_ci DEFAULT NULL,
  `ip` char(18) CHARACTER SET utf8mb3 COLLATE utf8mb3_general_ci NOT NULL,
  `category` int NOT NULL DEFAULT '1',
  `mac` char(255) DEFAULT NULL,
  `highlight` tinyint(1) NOT NULL DEFAULT '0',
  `check_method` tinyint NOT NULL DEFAULT '1' COMMENT '1:ping 2:tcp ports',
  `version` char(255) CHARACTER SET utf8mb3 COLLATE utf8mb3_general_ci DEFAULT NULL,
  `weight` tinyint NOT NULL DEFAULT '60',
  `status` tinyint NOT NULL DEFAULT '0' COMMENT '0:ok;1:warn;3:danger',
  `online` tinyint NOT NULL DEFAULT '0',
  `online_change` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `access_method` tinyint NOT NULL DEFAULT '0' COMMENT '0:no;1:ssh..',
  `access_results` json DEFAULT NULL,
  `latency` float DEFAULT NULL,
  `disable` tinyint NOT NULL DEFAULT '0',
  `warn` tinyint NOT NULL DEFAULT '0',
  `warn_port` tinyint NOT NULL DEFAULT '0',
  `warn_msg` char(255) DEFAULT NULL,
  `warn_mail` tinyint(1) NOT NULL DEFAULT '0',
  `alert_msg` varchar(255) DEFAULT NULL,
  `scan` tinyint NOT NULL DEFAULT '0',
  `alert` tinyint NOT NULL DEFAULT '0',
  `fingerprint` char(255) CHARACTER SET utf8mb3 COLLATE utf8mb3_general_ci DEFAULT NULL,
  `network` tinyint NOT NULL DEFAULT '1',
  `updated` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `created` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `ports` json DEFAULT NULL,
  `token` char(255) DEFAULT NULL,
  `notes_id` int DEFAULT NULL,
  `encrypted` text,
  `last_check` datetime DEFAULT NULL,
  `last_seen` datetime DEFAULT NULL,
  `misc` json DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3;

-- --------------------------------------------------------

--
-- Table structure for table `hosts_logs`
--

CREATE TABLE `hosts_logs` (
  `id` int NOT NULL,
  `host_id` int NOT NULL,
  `level` tinyint NOT NULL DEFAULT '7',
  `msg` char(255) CHARACTER SET utf8mb3 COLLATE utf8mb3_general_ci NOT NULL,
  `date` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3;

-- --------------------------------------------------------

--
-- Table structure for table `items`
--

CREATE TABLE `items` (
  `id` int NOT NULL,
  `uid` int NOT NULL DEFAULT '0',
  `cat_id` int NOT NULL DEFAULT '50',
  `type` char(255) CHARACTER SET utf8mb3 COLLATE utf8mb3_general_ci DEFAULT NULL,
  `title` char(255) NOT NULL,
  `conf` varchar(4096) NOT NULL,
  `weight` tinyint NOT NULL DEFAULT '60',
  `highlight` tinyint NOT NULL DEFAULT '0',
  `online` int NOT NULL DEFAULT '0'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3;

-- --------------------------------------------------------

--
-- Table structure for table `load_stats`
--

CREATE TABLE `load_stats` (
  `timestamp` timestamp NOT NULL,
  `host` int NOT NULL,
  `value` tinyint NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3;

-- --------------------------------------------------------

--
-- Table structure for table `networks`
--

CREATE TABLE `networks` (
  `id` int NOT NULL,
  `network` char(18) CHARACTER SET utf8mb3 COLLATE utf8mb3_general_ci NOT NULL,
  `name` char(255) CHARACTER SET utf8mb3 COLLATE utf8mb3_general_ci DEFAULT NULL,
  `vlan` smallint DEFAULT '1',
  `scan` tinyint(1) NOT NULL DEFAULT '1',
  `weight` tinyint NOT NULL DEFAULT '50',
  `disable` tinyint NOT NULL DEFAULT '0'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3;

-- --------------------------------------------------------

--
-- Table structure for table `notes`
--

CREATE TABLE `notes` (
  `id` int NOT NULL,
  `uid` int NOT NULL DEFAULT '0',
  `host_id` int NOT NULL,
  `update` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `content` text CHARACTER SET utf8mb3 COLLATE utf8mb3_general_ci
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3;

-- --------------------------------------------------------

--
-- Table structure for table `prefs`
--

CREATE TABLE `prefs` (
  `id` int NOT NULL,
  `uid` int NOT NULL,
  `pref_name` char(255) NOT NULL,
  `pref_value` char(255) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3;

-- --------------------------------------------------------

--
-- Table structure for table `stats`
--

CREATE TABLE `stats` (
  `date` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `type` tinyint UNSIGNED NOT NULL COMMENT '1 ping',
  `host_id` int NOT NULL,
  `value` float NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3;

-- --------------------------------------------------------

--
-- Table structure for table `system_logs`
--

CREATE TABLE `system_logs` (
  `id` int NOT NULL,
  `level` tinyint UNSIGNED NOT NULL,
  `msg` char(255) CHARACTER SET utf8mb3 COLLATE utf8mb3_general_ci NOT NULL,
  `date` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3;

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `id` int NOT NULL,
  `username` char(255) CHARACTER SET utf8mb3 COLLATE utf8mb3_general_ci NOT NULL,
  `email` char(255) CHARACTER SET utf8mb3 COLLATE utf8mb3_general_ci DEFAULT NULL,
  `password` char(255) CHARACTER SET utf8mb3 COLLATE utf8mb3_bin NOT NULL,
  `sid` char(255) CHARACTER SET utf8mb3 COLLATE utf8mb3_general_ci DEFAULT NULL,
  `timezone` char(32) CHARACTER SET utf8mb3 COLLATE utf8mb3_general_ci DEFAULT NULL,
  `theme` char(12) DEFAULT NULL,
  `lang` char(12) DEFAULT NULL,
  `isAdmin` tinyint NOT NULL DEFAULT '0',
  `created` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3;


INSERT INTO `prefs` (`id`, `uid`, `pref_name`, `pref_value`) VALUES
(1, 0, 'cli_last_run', '0'),
(2, 0, 'monnet_version', '0.36'),
(3, 0, 'cron_quarter', '0'),
(4, 0, 'cron_hourly', '0'),
(5, 0, 'cron_halfday', '0'),
(6, 0, 'cron_weekly', '0'),
(7, 0, 'cron_monthly', '0'),
(8, 0, 'cron_update', '0'),
(9, 0, 'cron_five', '0'),
(10, 0, 'cron_daily', '0');

INSERT INTO `categories` (`id`, `cat_type`, `cat_name`, `disable`, `weight`) VALUES
(1, 1, 'L_UNCATEGORIZED', 0, 0),
(2, 1, 'L_NETWORK', 0, 0),
(3, 1, 'L_SERVERS', 0, 0),
(4, 1, 'L_VM', 0, 0),
(5, 1, 'L_DESKTOP', 0, 0),
(6, 1, 'L_IOT', 0, 0),
(7, 1, 'L_CAMERAS', 0, 0),
(8, 1, 'L_TV', 0, 0),
(9, 1, 'L_PHONE', 0, 0),
(10, 1, 'L_PRINTERS', 0, 0),
(50, 2, 'L_OTHERS', 0, 0),
(51, 2, 'L_WEBS', 0, 0),
(52, 2, 'L_INTERNAL', 0, 0),
(100, 3, 'L_SEARCH_ENGINE', 0, 0),
(108, 1, 'test', 0, 0);

INSERT INTO `items` (`id`, `cat_id`, `type`, `title`, `conf`, `weight`, `highlight`) VALUES
(1, 20, 'search_engine', 'Google', '{\"url\":\"https:\\/\\/google.com\\/search\",\"name\":\"q\"}', 60, 0),
(2, 20, 'search_engine', 'Duck', '{\"url\":\"https:\\/\\/duckdockgo.com\\/search\",\"name\":\"q\"}', 60, 0);

INSERT INTO `users` (`id`, `username`, `email`, `password`, `sid`, `isAdmin`, `created`) VALUES
(1, 'monnet', NULL, '50fbd2ffa0f3e68cb2d7bc818d63f29cf3a4df10', '01s57t8jqms7f4etc9p5k492mj', 1, '2021-10-30 12:06:20');

--
-- Indexes for table `categories`
--
ALTER TABLE `categories`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `cmd`
--
ALTER TABLE `cmd`
  ADD PRIMARY KEY (`cmd_id`);

--
-- Indexes for table `hosts`
--
ALTER TABLE `hosts`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `ip` (`ip`);

--
-- Indexes for table `hosts_logs`
--
ALTER TABLE `hosts_logs`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_level_host_date` (`level`,`host_id`,`date`);

--
-- Indexes for table `items`
--
ALTER TABLE `items`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `load_stats`
--
ALTER TABLE `load_stats`
  ADD PRIMARY KEY (`timestamp`),
  ADD KEY `host` (`host`);

--
-- Indexes for table `networks`
--
ALTER TABLE `networks`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `network` (`network`),
  ADD UNIQUE KEY `name` (`name`);

--
-- Indexes for table `notes`
--
ALTER TABLE `notes`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `prefs`
--
ALTER TABLE `prefs`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `uid` (`uid`,`pref_name`);

--
-- Indexes for table `stats`
--
ALTER TABLE `stats`
  ADD UNIQUE KEY `date` (`date`,`host_id`);

--
-- Indexes for table `system_logs`
--
ALTER TABLE `system_logs`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_level_host_date` (`level`,`date`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `username` (`username`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `categories`
--
ALTER TABLE `categories`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=109;

--
-- AUTO_INCREMENT for table `cmd`
--
ALTER TABLE `cmd`
  MODIFY `cmd_id` int NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `hosts`
--
ALTER TABLE `hosts`
  MODIFY `id` int NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `hosts_logs`
--
ALTER TABLE `hosts_logs`
  MODIFY `id` int NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `items`
--
ALTER TABLE `items`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=60;

--
-- AUTO_INCREMENT for table `networks`
--
ALTER TABLE `networks`
  MODIFY `id` int NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `notes`
--
ALTER TABLE `notes`
  MODIFY `id` int NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `prefs`
--
ALTER TABLE `prefs`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=47;

--
-- AUTO_INCREMENT for table `system_logs`
--
ALTER TABLE `system_logs`
  MODIFY `id` int NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;
COMMIT;
