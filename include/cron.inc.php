<?php

/**
 *
 *  @author diego/@/envigo.net
 *  @package
 *  @subpackage
 *  @copyright Copyright CC BY-NC-ND 4.0 @ 2020 - 2024 Diego Garcia (diego/@/envigo.net)
 */
!defined('IN_CLI') ? exit : true;

function cron(array $cfg, Database $db, Hosts $hosts) {
    $results = $db->select('prefs', '*', ['uid' => 0]);
    $admin_prefs = $db->fetchAll($results);

    foreach ($admin_prefs as $vpref) {
        $cron_times[$vpref['pref_name']] = $vpref['pref_value'];
    }

    $time_now = time();

    if (($cron_times['cron_five'] + 0) < $time_now) {
        $db->update('prefs', ['pref_value' => $time_now], ['pref_name' => ['value' => 'cron_five']], 'LIMIT 1');
        $only_missing = 1;
        fill_hostnames($hosts, $only_missing);
        fill_mac_vendors($hosts, $only_missing);
#        host_access($cfg, $hosts);
    }

    if (($cron_times['cron_quarter'] + 900) < $time_now) {
        $db->update('prefs', ['pref_value' => $time_now], ['pref_name' => ['value' => 'cron_quarter']], 'LIMIT 1');
        ping_net($cfg, $hosts);
    }

    if (($cron_times['cron_hourly'] + 3600) < $time_now) {
        $db->update('prefs', ['pref_value' => $time_now], ['pref_name' => ['value' => 'cron_hourly']], 'LIMIT 1');
    }

    if (($cron_times['cron_halfday'] + 21600) < $time_now) {
        $db->update('prefs', ['pref_value' => $time_now], ['pref_name' => ['value' => 'cron_halfday']], 'LIMIT 1');
    }
    if (($cron_times['cron_daily'] + 8640) < $time_now) {
        $db->update('prefs', ['pref_value' => $time_now], ['pref_name' => ['value' => 'cron_daily']], 'LIMIT 1');
    }

    if (($cron_times['cron_weekly'] + 604800) < $time_now) {
        $db->update('prefs', ['pref_value' => $time_now], ['pref_name' => ['value' => 'cron_weekly']], 'LIMIT 1');
    }
    if (($cron_times['cron_monthly'] + 2592000) < $time_now) {
        $db->update('prefs', ['pref_value' => $time_now], ['pref_name' => ['value' => 'cron_monthly']], 'LIMIT 1');
        $only_missing = 0;
        fill_hostnames($hosts, $only_missing);
        fill_mac_vendors($hosts, $only_missing);
    }
    if ($cron_times['cron_update'] == 0) {
        $db->update('prefs', ['pref_value' => $time_now], ['pref_name' => ['value' => 'cron_update']], 'LIMIT 1');
    }
}
