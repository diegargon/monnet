SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";

--
-- Database: `monnet`
--

-- --------------------------------------------------------

--
-- Table structure for table `ansible_msg`
--

CREATE TABLE `ansible_msg` (
  `id` int NOT NULL,
  `host_id` int NOT NULL,
  `msg` text CHARACTER SET utf8mb3 COLLATE utf8mb3_general_ci NOT NULL,
  `type` tinyint NOT NULL DEFAULT '0' COMMENT '0 success 1 error',
  `timestamp` timestamp NOT NULL ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3;

-- --------------------------------------------------------

--
-- Table structure for table `ansible_vars`
--

CREATE TABLE `ansible_vars` (
  `id` int NOT NULL,
  `hid` int NOT NULL,
  `vtype` tinyint NOT NULL,
  `vkey` varchar(255) NOT NULL,
  `vvalue` varchar(700) CHARACTER SET utf8mb3 COLLATE utf8mb3_general_ci NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3;

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

--
-- Dumping data for table `categories`
--

INSERT INTO `categories` (`id`, `cat_type`, `cat_name`, `disable`, `weight`) VALUES
(1, 1, 'L_UNCATEGORIZED', 0, 0),
(2, 1, 'L_NETWORK', 0, 0),
(3, 1, 'L_SERVERS', 0, 0),
(5, 1, 'L_DESKTOP', 0, 0),
(6, 1, 'L_IOT', 0, 0),
(7, 1, 'L_CAMERAS', 0, 0),
(8, 1, 'L_TV', 0, 0),
(9, 1, 'L_PHONE', 0, 0),
(10, 1, 'L_PRINTERS', 0, 0),
(50, 2, 'L_OTHERS', 0, 0),
(51, 2, 'L_WEBS', 0, 0),
(52, 2, 'L_INTERNAL', 0, 0),
(100, 3, 'L_SEARCH_ENGINE', 0, 0);

-- --------------------------------------------------------

--
-- Table structure for table `config`
--

CREATE TABLE `config` (
  `id` int NOT NULL,
  `ckey` varchar(128) NOT NULL,
  `cvalue` json DEFAULT NULL,
  `ctype` tinyint NOT NULL DEFAULT '0',
  `ccat` tinyint NOT NULL DEFAULT '0',
  `cdesc` varchar(128) CHARACTER SET utf8mb3 COLLATE utf8mb3_general_ci DEFAULT NULL,
  `uid` int NOT NULL DEFAULT '0'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3;

--
-- Dumping data for table `config`
--

INSERT INTO `config` (`id`, `ckey`, `cvalue`, `ctype`, `ccat`, `cdesc`, `uid`) VALUES
(100, 'ansible', '0', 2, 1, NULL, 0),
(101, 'ansible_server_ip', '\"127.0.0.1\"', 0, 102, NULL, 0),
(102, 'ansible_server_port', '65432', 1, 102, NULL, 0),
(103, 'mail', '0', 2, 1, NULL, 0),
(104, 'mail_auth', '1', 2, 101, NULL, 0),
(105, 'mail_host', '\"localhost\"', 0, 101, NULL, 0),
(106, 'mail_username', '\"monnet\"', 0, 101, NULL, 0),
(107, 'mail_password', '\"password\"', 0, 101, NULL, 0),
(108, 'mail_port', '587', 1, 101, NULL, 0),
(109, 'mail_auth_type', '{\"LOGIN\": 0, \"PLAIN\": 1, \"XOAUTH2\": 0, \"CRAM-MD5\": 0}', 6, 101, NULL, 0),
(110, 'mail_from', '\"monnet@localhost\"', 0, 101, NULL, 0),
(111, 'smtp_security', '{\"SMTPS\": 0, \"STARTTLS\": 1}', 6, 101, NULL, 0),
(112, 'ansible_user', '\"ansible\"', 0, 102, NULL, 0),
(113, 'allow_save_password', '0', 2, 1, NULL, 0),
(114, 'cli_last_run', '0', 1, 0, NULL, 0),
(115, 'cron_quarter', '0', 1, 0, NULL, 0),
(116, 'cron_hourly', '0', 1, 0, NULL, 0),
(117, 'cron_halfday', '0', 1, 0, NULL, 0),
(118, 'cron_weekly', '0', 1, 0, NULL, 0),
(119, 'cron_monthly', '0', 1, 0, NULL, 0),
(120, 'cron_update', '0', 1, 0, NULL, 0),
(121, 'cron_five', '0', 1, 0, NULL, 0),
(122, 'cron_daily', '0', 1, 0, NULL, 0),
(123, 'refreshing', '0', 1, 0, NULL, 0),
(124, 'db_monnet_version', '0.61', 0, 0, NULL, 0),
(125, 'discovery_last_run', '0', 1, 0, NULL, 0),
(126, 'agent_external_host', '\"\"', 0, 103, NULL, 0),
(127, 'agent_default_interval', '60', 1, 103, NULL, 0),
(129, 'public_key', '\"\"', 10, 10, NULL, 0),
(130, 'log_level', '5', 1, 105, NULL, 0),
(131, 'log_file', '\"logs/monnet.log\"', 0, 105, NULL, 0),
(132, 'system_log_to_syslog', '1', 2, 105, NULL, 0),
(133, 'system_log_to_db', '1', 2, 105, NULL, 0),
(134, 'system_log_to_db_debug', '0', 2, 105, NULL, 0),
(135, 'log_to_file', '1', 2, 105, NULL, 0),
(136, 'log_file_owner', '\"www-data\"', 0, 105, NULL, 0),
(137, 'log_file_owner_group', '\"www-data\"', 0, 105, NULL, 0),
(138, 'term_hosts_log_level', '5', 1, 105, NULL, 0),
(139, 'term_system_log_level', '5', 1, 105, NULL, 0),
(140, 'term_max_lines', '100', 1, 105, NULL, 0),
(141, 'term_show_system_logs', '1', 2, 105, NULL, 0),
(142, 'theme_css', '\"default\"', 0, 2, NULL, 0),
(143, 'theme', '\"default\"', 0, 2, NULL, 0),
(144, 'refresher_time', '2', 1, 2, NULL, 0),
(145, 'glow_time', '10', 1, 2, NULL, 0),
(146, 'port_timeout_local', '0.5', 3, 106, NULL, 0),
(147, 'port_timeout', '0.8', 3, 106, NULL, 0),
(148, 'ping_nets_timeout', '200000', 1, 106, NULL, 0),
(149, 'ping_hosts_timeout', '400000', 1, 106, NULL, 0),
(150, 'ping_local_hosts_timeout', '300000', 1, 106, NULL, 0),
(151, 'clear_logs_intvl', '30', 1, 104, NULL, 0),
(152, 'clear_stats_intvl', '15', 1, 104, NULL, 0),
(153, 'clear_reports_intvl', '30', 1, 104, NULL, 0),
(154, 'agent_allow_selfcerts', '1', 2, 103, NULL, 0),
(155, 'default_mem_alert_threshold', '90', 1, 103, NULL, 0),
(156, 'default_mem_warn_threshold', '80', 1, 103, NULL, 0),
(157, 'default_disks_alert_threshold', '90', 1, 103, NULL, 0),
(158, 'default_disks_warn_threshold', '80', 1, 103, NULL, 0),
(159, 'term_date_format', '\"[d][H:i]\"', 0, 5, NULL, 0),
(160, 'date_format', '\"d-m-Y\"', 0, 5, NULL, 0),
(161, 'time_format', '\"H:i:s\"', 0, 5, NULL, 0),
(162, 'datetime_format', '\"d-m-Y H:i:s\"', 0, 5, NULL, 0),
(163, 'datetime_format_min', '\"d/H:i\"', 0, 5, NULL, 0),
(164, 'datatime_graph_format', '\"H:i\"', 0, 5, NULL, 0),
(165, 'datetime_log_format', '\"d-m-y H:i:s\"', 0, 5, NULL, 0),
(166, 'default_charset', '\"utf-8\"', 0, 1, NULL, 0),
(167, 'default_timezone', '\"UTC\"', 0, 1, NULL, 0),
(168, 'graph_charset', '\"es-ES\"', 0, 1, NULL, 0),
(169, 'web_title', '\"MonNet\"', 0, 2, NULL, 0),
(170, 'check_retries_usleep', '500000', 1, 106, NULL, 0),
(171, 'check_retries', '4', 1, 106, NULL, 0);

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
  `mac` char(17) DEFAULT NULL,
  `highlight` tinyint(1) NOT NULL DEFAULT '0',
  `check_method` tinyint NOT NULL DEFAULT '1' COMMENT '1:ping 2:tcp ports 3 https',
  `weight` tinyint NOT NULL DEFAULT '60',
  `status` tinyint NOT NULL DEFAULT '0' COMMENT '0:ok;1:warn;3:danger',
  `online` tinyint NOT NULL DEFAULT '0',
  `glow` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `online_change` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `access_method` tinyint NOT NULL DEFAULT '0' COMMENT '0:no;1:ssh..',
  `disable` tinyint NOT NULL DEFAULT '0',
  `warn` tinyint NOT NULL DEFAULT '0',
  `warn_mail` tinyint(1) NOT NULL DEFAULT '0',
  `scan` tinyint NOT NULL DEFAULT '0',
  `alert` tinyint NOT NULL DEFAULT '0',
  `network` tinyint NOT NULL DEFAULT '1',
  `updated` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `created` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `token` char(255) DEFAULT NULL,
  `notes_id` int DEFAULT NULL,
  `encrypted` text,
  `last_check` datetime DEFAULT NULL,
  `last_seen` datetime DEFAULT NULL,
  `misc` json DEFAULT NULL,
  `ansible_enabled` tinyint NOT NULL DEFAULT '0',
  `ansible_fail` tinyint NOT NULL DEFAULT '0',
  `agent_installed` tinyint(1) NOT NULL DEFAULT '0'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3;

-- --------------------------------------------------------

--
-- Table structure for table `hosts_logs`
--

CREATE TABLE `hosts_logs` (
  `id` int NOT NULL,
  `host_id` int NOT NULL,
  `level` tinyint NOT NULL DEFAULT '7',
  `log_type` varchar(255) CHARACTER SET utf8mb3 COLLATE utf8mb3_general_ci NOT NULL DEFAULT '0' COMMENT 'DEFAULT = 0;\r\nEVENT = 1;\r\nEVENT_WARN = 2;\r\nEVENT_ALERT = 3;',
  `event_type` smallint DEFAULT '0',
  `msg` char(255) CHARACTER SET utf8mb3 COLLATE utf8mb3_general_ci NOT NULL,
  `ack` tinyint(1) NOT NULL DEFAULT '0',
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

--
-- Dumping data for table `items`
--

INSERT INTO `items` (`id`, `uid`, `cat_id`, `type`, `title`, `conf`, `weight`, `highlight`, `online`) VALUES
(1, 1, 20, 'search_engine', 'Google', '{\"url\":\"https:\\/\\/google.com\\/search\",\"name\":\"q\"}', 60, 0, 0),
(2, 1, 20, 'search_engine', 'Duck', '{\"url\":\"https:\\/\\/duckdockgo.com\\/search\",\"name\":\"q\"}', 60, 0, 0),
(4, 1, 121, 'bookmarks', 'Reddit', '{\"url\":\"https:\\/\\/reddit.com\",\"image_type\":\"local_img\",\"image_resource\":\"reddit.png\"}', 50, 0, 0);


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
  `pool` tinyint NOT NULL DEFAULT '0',
  `weight` tinyint NOT NULL DEFAULT '50',
  `disable` tinyint NOT NULL DEFAULT '0',
  `only_online` tinyint(1) NOT NULL DEFAULT '0'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3;

--
-- Dumping data for table `networks`
--

INSERT INTO `networks` (`id`, `network`, `name`, `vlan`, `scan`, `pool`, `weight`, `disable`, `only_online`) VALUES
(1, '255.255.255.0/24', 'default', 1, 0, 0, 50, 0, 0),
(2, '0.0.0.0/0', 'INTERNET', 0, 0, 0, 50, 0, 0),
(3, '192.168.1.0/24', 'Main Network', 1, 1, 1, 50, 0, 0);

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
-- Table structure for table `ports`
--

CREATE TABLE `ports` (
  `id` int NOT NULL,
  `hid` int NOT NULL,
  `scan_type` tinyint DEFAULT NULL,
  `protocol` tinyint NOT NULL COMMENT '1 tcp 2 udp 3 https',
  `pnumber` smallint UNSIGNED NOT NULL,
  `online` tinyint(1) NOT NULL DEFAULT '0',
  `interface` varchar(45) DEFAULT NULL,
  `ip_version` varchar(5) DEFAULT NULL,
  `custom_service` varchar(255) DEFAULT NULL,
  `service` varchar(255) DEFAULT NULL,
  `latency` float DEFAULT NULL,
  `last_check` datetime DEFAULT NULL
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

--
-- Dumping data for table `prefs`
--

INSERT INTO `prefs` (`id`, `uid`, `pref_name`, `pref_value`) VALUES
(1, 0, 'cli_last_run', '0'),
(2, 0, 'monnet_version', '0.42'),
(3, 0, 'cron_quarter', '0'),
(4, 0, 'cron_hourly', '0'),
(5, 0, 'cron_halfday', '0'),
(6, 0, 'cron_weekly', '0'),
(7, 0, 'cron_monthly', '0'),
(8, 0, 'cron_update', '0'),
(9, 0, 'cron_five', '0'),
(10, 0, 'cron_daily', '0');

-- --------------------------------------------------------

--
-- Table structure for table `reports`
--

CREATE TABLE `reports` (
  `id` int NOT NULL,
  `host_id` int NOT NULL,
  `pb_id` int NOT NULL,
  `source_id` int DEFAULT '0' COMMENT 'source_id = \r\ntask id if task\r\nuser id if manual',
  `rtype` tinyint NOT NULL COMMENT '1 manual 2 Task',
  `date` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `report` json NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3;

-- --------------------------------------------------------

--
-- Table structure for table `stats`
--

CREATE TABLE `stats` (
  `date` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `type` tinyint UNSIGNED NOT NULL COMMENT '1 ping 2 load avg 3 iowait',
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
-- Table structure for table `tasks`
--

CREATE TABLE `tasks` (
  `id` int NOT NULL,
  `hid` int NOT NULL,
  `pb_id` smallint NOT NULL,
  `trigger_type` smallint NOT NULL,
  `last_triggered` datetime DEFAULT NULL,
  `next_trigger` datetime DEFAULT NULL,
  `task_name` varchar(100) NOT NULL,
  `next_task` int DEFAULT '0',
  `disable` tinyint(1) DEFAULT '0',
  `extra` json DEFAULT NULL,
  `task_interval` varchar(10) DEFAULT NULL,
  `interval_seconds` int DEFAULT NULL,
  `created` datetime DEFAULT CURRENT_TIMESTAMP,
  `event_id` int DEFAULT '0',
  `crontime` varchar(255) DEFAULT NULL,
  `groups` varchar(255) DEFAULT NULL
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

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`id`, `username`, `email`, `password`, `sid`, `timezone`, `theme`, `lang`, `isAdmin`, `created`) VALUES
(1, 'monnet', NULL, '50fbd2ffa0f3e68cb2d7bc818d63f29cf3a4df10', '4n3bcgu6i4fm7ufl8egenv6d30', NULL, NULL, NULL, 1, '2021-10-30 12:06:20');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `ansible_msg`
--
ALTER TABLE `ansible_msg`
  ADD PRIMARY KEY (`id`),
  ADD KEY `host_id` (`host_id`);

--
-- Indexes for table `ansible_vars`
--
ALTER TABLE `ansible_vars`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `hid` (`hid`,`vkey`);

--
-- Indexes for table `categories`
--
ALTER TABLE `categories`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `config`
--
ALTER TABLE `config`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `ckey` (`ckey`);

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
-- Indexes for table `ports`
--
ALTER TABLE `ports`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_hid` (`hid`);

--
-- Indexes for table `prefs`
--
ALTER TABLE `prefs`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `uid` (`uid`,`pref_name`);

--
-- Indexes for table `reports`
--
ALTER TABLE `reports`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_host_id_id` (`host_id`,`id`);

--
-- Indexes for table `stats`
--
ALTER TABLE `stats`
  ADD KEY `idx_host_date` (`host_id`,`date`);

--
-- Indexes for table `system_logs`
--
ALTER TABLE `system_logs`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_level_host_date` (`level`,`date`);

--
-- Indexes for table `tasks`
--
ALTER TABLE `tasks`
  ADD PRIMARY KEY (`id`);

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
-- AUTO_INCREMENT for table `ansible_msg`
--
ALTER TABLE `ansible_msg`
  MODIFY `id` int NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `ansible_vars`
--
ALTER TABLE `ansible_vars`
  MODIFY `id` int NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `categories`
--
ALTER TABLE `categories`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=200;

--
-- AUTO_INCREMENT for table `config`
--
ALTER TABLE `config`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=500;

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
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=15;

--
-- AUTO_INCREMENT for table `notes`
--
ALTER TABLE `notes`
  MODIFY `id` int NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `ports`
--
ALTER TABLE `ports`
  MODIFY `id` int NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `prefs`
--
ALTER TABLE `prefs`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=50;

--
-- AUTO_INCREMENT for table `reports`
--
ALTER TABLE `reports`
  MODIFY `id` int NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `system_logs`
--
ALTER TABLE `system_logs`
  MODIFY `id` int NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `tasks`
--
ALTER TABLE `tasks`
  MODIFY `id` int NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;
COMMIT;
