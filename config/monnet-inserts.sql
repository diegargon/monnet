INSERT INTO `prefs` (`id`, `uid`, `pref_name`, `pref_value`) VALUES
(1, 0, 'monnet_version',0.1),
(2, 0, 'cron_quarter', '0'),
(3, 0, 'cron_hourly', '0'),
(4, 0, 'cron_halfday', '0'),
(5, 0, 'cron_weekly', '0'),
(6, 0, 'cron_monthly', '0'),
(7, 0, 'cron_update', '0'),
(8, 0, 'cron_five', '0'),
(9, 0, 'cron_daily', '0'),

INSERT INTO `categories` (`id`, `cat_type`, `cat_name`, `on`) VALUES
(1, 1, 'L_UNCATEGORIZED', 1),
(2, 1, 'L_NETWORK', 1),
(3, 1, 'L_SERVERS', 1),
(4, 1, 'L_VM', 1),
(5, 1, 'L_USERS', 1),
(6, 1, 'L_IOT', 1),
(7, 1, 'L_CAMERAS', 1),
(8, 1, 'L_TV', 1),
(9, 1, 'L_PHONE', 1),
(50, 2, 'L_WEBS', 0),
(51, 2, 'L_APPLINKS', 0),
(100, 3, 'L_SEARCH_ENGINE', 0);

INSERT INTO `prefs` (`id`, `uid`, `pref_name`, `pref_value`) VALUES (1, 0, 'cli_last_run', '0');

INSERT INTO `items` (`id`, `cat_id`, `type`, `title`, `conf`, `weight`) VALUES
(1, 10, NULL, 'Google', '{\"url\":\"https://google.es\",\"image_type\":\"favicon\",\"image_resource\":false}', 0),
(2, 10, NULL, 'Yahoo', '{\"url\":\"http://es.yahoo.com\",\"image_type\":\"favicon\",\"image_resource\":false}', 0),

INSERT INTO `users` (`id`, `username`, `email`, `password`, `sid`, `isAdmin`, `created`) VALUES
(1, 'monnet', NULL, '50fbd2ffa0f3e68cb2d7bc818d63f29cf3a4df10', '01s57t8jqms7f4etc9p5k492mj', 1, '2021-10-30 12:06:20');

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
-- AUTO_INCREMENT for table `categories`
--
ALTER TABLE `categories`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=101;

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
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=50;

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
