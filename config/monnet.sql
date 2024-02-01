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
  `cat_type` tinyint NOT NULL COMMENT '1 Hosts 2 Links',
  `cat_name` varchar(32) NOT NULL,
  `on` tinyint(1) NOT NULL DEFAULT '0'
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
  `title` char(255) CHARACTER SET utf8mb3 COLLATE utf8mb3_general_ci DEFAULT NULL,
  `hostname` varchar(1024) CHARACTER SET utf8mb3 COLLATE utf8mb3_general_ci DEFAULT NULL,
  `ip` char(255) NOT NULL,
  `category` int NOT NULL,
  `mac` char(255) DEFAULT NULL,
  `mac_vendor` char(255) CHARACTER SET utf8mb3 COLLATE utf8mb3_general_ci DEFAULT NULL,
  `highlight` tinyint(1) NOT NULL DEFAULT '0',
  `check_method` tinyint NOT NULL DEFAULT '1' COMMENT '1:ping 2:tcp ports',
  `system` int DEFAULT NULL,
  `os` int NOT NULL DEFAULT '0',
  `os_distribution` int DEFAULT NULL,
  `codename` char(255) DEFAULT NULL,
  `version` char(255) CHARACTER SET utf8mb3 COLLATE utf8mb3_general_ci DEFAULT NULL,
  `weight` tinyint NOT NULL DEFAULT '60',
  `status` tinyint NOT NULL DEFAULT '0' COMMENT '0:ok;1:warn;3:danger',
  `online` tinyint NOT NULL DEFAULT '0',
  `access_method` tinyint NOT NULL DEFAULT '0' COMMENT '0:no;1:ssh..',
  `access_results` json DEFAULT NULL,
  `timeout` tinyint DEFAULT NULL,
  `latency` float DEFAULT NULL,
  `disable` tinyint NOT NULL DEFAULT '0',
  `warn` tinyint NOT NULL DEFAULT '0',
  `warn_port` tinyint NOT NULL DEFAULT '0',
  `fingerprint` char(255) CHARACTER SET utf8mb3 COLLATE utf8mb3_general_ci DEFAULT NULL,
  `updated` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `created` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `ports` varchar(15000) CHARACTER SET utf8mb3 COLLATE utf8mb3_bin DEFAULT NULL,
  `notes_id` int DEFAULT NULL,
  `last_check` datetime DEFAULT NULL,
  `last_seen` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3;

-- --------------------------------------------------------

--
-- Table structure for table `hosts_logs`
--

CREATE TABLE `hosts_logs` (
  `id` int NOT NULL,
  `host_id` int NOT NULL,
  `msg` varchar(255) NOT NULL,
  `date` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3;

-- --------------------------------------------------------

--
-- Table structure for table `items`
--

CREATE TABLE `items` (
  `id` int NOT NULL,
  `cat_id` int NOT NULL,
  `type` char(255) CHARACTER SET utf8mb3 COLLATE utf8mb3_general_ci DEFAULT NULL,
  `title` char(255) NOT NULL,
  `conf` varchar(4096) NOT NULL,
  `weight` tinyint NOT NULL DEFAULT '60'
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
-- Table structure for table `notes`
--

CREATE TABLE `notes` (
  `id` int NOT NULL,
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
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `id` int NOT NULL,
  `username` char(255) CHARACTER SET utf8mb3 COLLATE utf8mb3_general_ci NOT NULL,
  `email` char(255) CHARACTER SET utf8mb3 COLLATE utf8mb3_general_ci DEFAULT NULL,
  `password` char(255) CHARACTER SET utf8mb3 COLLATE utf8mb3_bin NOT NULL,
  `sid` char(255) CHARACTER SET utf8mb3 COLLATE utf8mb3_general_ci DEFAULT NULL,
  `isAdmin` tinyint NOT NULL DEFAULT '0',
  `created` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3;
