<?php

/**
 *
 * @author diego/@/envigo.net
 * @package
 * @subpackage
 * @copyright Copyright CC BY-NC-ND 4.0 @ 2020 - 2024 Diego Garcia (diego/@/envigo.net)
 */
!defined('IN_CLI') ? exit : true;
/**
 *
 * @param AppContext $ctx
 * @return void
 */
function cron(AppContext $ctx): void
{
    $hosts = $ctx->get('Hosts');
    $ncfg = $ctx->get('Config');

    Log::debug("Starting cron...");
    //$results = $db->select('prefs', '*', ['uid' => 0]);
    //$system_prefs = $db->fetchAll($results);
    $cron_task_track = '';

    /*
    foreach ($system_prefs as $vpref) {
        if (strpos($vpref['pref_name'], 'cron_') === 0) {
            $cron_times[$vpref['pref_name']] = (int) $vpref['pref_value'];
        }
    }
    */

    $time_now = time();

    if (($ncfg->get('cron_five') + 300) < $time_now) {
        $cron_task_track .= '[5]';
        //$db->update('prefs', ['pref_value' => $time_now], ['pref_name' => ['value' => 'cron_five']], 'LIMIT 1');
        $ncfg->set('cron_five', $time_now);
        fill_hostnames($hosts);
    }

    if (($ncfg->get('cron_quarter') + 900) < $time_now) {
        $cron_task_track .= '[15]';
        //$db->update('prefs', ['pref_value' => $time_now], ['pref_name' => ['value' => 'cron_quarter']], 'LIMIT 1');
        $ncfg->set('cron_quarter', $time_now);
    }

    if (($ncfg->get('cron_hourly') + 3600) < $time_now) {
        $cron_task_track .= '[60]';
        //$db->update('prefs', ['pref_value' => $time_now], ['pref_name' => ['value' => 'cron_hourly']], 'LIMIT 1');
        $ncfg->set('cron_hourly', $time_now);
        fill_mac_vendors($hosts);
    }

    if (($ncfg->get('cron_halfday') + 21600) < $time_now) {
        $cron_task_track .= '[12]';
        check_macs($hosts);
        //$db->update('prefs', ['pref_value' => $time_now], ['pref_name' => ['value' => 'cron_halfday']], 'LIMIT 1');
        $ncfg->set('cron_halfday', $time_now);
    }
    if (($ncfg->get('cron_daily') + 8640) < $time_now) {
        $cron_task_track .= '[24]';
        clear_stats($ctx);
        clear_system_logs($ctx);
        clear_hosts_logs($ctx);
        clear_reports($ctx);
        //$db->update('prefs', ['pref_value' => $time_now], ['pref_name' => ['value' => 'cron_daily']], 'LIMIT 1');
        $ncfg->set('cron_daily', $time_now);
    }

    if (($ncfg->get('cron_weekly') + 604800) < $time_now) {
        $cron_task_track .= '[7d]';
        //$db->update('prefs', ['pref_value' => $time_now], ['pref_name' => ['value' => 'cron_weekly']], 'LIMIT 1');
        $ncfg->set('cron_weekly', $time_now);
        fill_hostnames($hosts, 1);
        fill_mac_vendors($hosts, 1);
    }
    if (($ncfg->get('cron_monthly') + 2592000) < $time_now) {
        $cron_task_track .= '[30d]';
        //$db->update('prefs', ['pref_value' => $time_now], ['pref_name' => ['value' => 'cron_monthly']], 'LIMIT 1');
        $ncfg->set('cron_monthly', $time_now);
    }
    if ($ncfg->get('cron_update') == 0) {
        //$db->update('prefs', ['pref_value' => $time_now], ['pref_name' => ['value' => 'cron_update']], 'LIMIT 1');
        $ncfg->set('cron_update', $time_now);
    }
    if (!empty($cron_task_track)) {
        Log::debug('Cron times :' . $cron_task_track);
    }
}

/**
 *
 * @param AppContext $ctx
 * @return void
 */
function clear_stats(AppContext $ctx): void
{
    $cfg = $ctx->get('cfg');
    $db = $ctx->get('Mysql');
    $intvl = $cfg['clear_stats_intvl'];

    $query = "DELETE FROM stats WHERE date < DATE_SUB(CURDATE(), INTERVAL $intvl DAY)";
    $db->query($query);
    $affected = $db->getAffected();
    Log::info('Clear stats, affected rows ' . $affected);
}

/**
 *
 * @param AppContext $ctx
 * @return void
 */
function clear_system_logs(AppContext $ctx): void
{
    $cfg = $ctx->get('cfg');
    $db = $ctx->get('Mysql');
    $intvl = $cfg['clear_logs_intvl'];

    $query = "DELETE FROM system_logs WHERE date < DATE_SUB(CURDATE(), INTERVAL $intvl DAY)";
    $db->query($query);
    $affected = $db->getAffected();
    Log::info('Clear system logs, affected rows ' . $affected);
}

/**
 *
 * @param AppContext $ctx
 * @return void
 */
function clear_hosts_logs(AppContext $ctx): void
{
    $cfg = $ctx->get('cfg');
    $db = $ctx->get('Mysql');
    $intvl = $cfg['clear_logs_intvl'];

    $query = "DELETE FROM hosts_logs WHERE date < DATE_SUB(CURDATE(), INTERVAL $intvl DAY)";
    $db->query($query);
    $affected = $db->getAffected();
    Log::info('Clear host logs, affected rows ' . $affected);
}

/**
 *
 * @param AppContext $ctx
 * @return void
 */
function clear_reports(AppContext $ctx): void
{
    $cfg = $ctx->get('cfg');
    $db = $ctx->get('Mysql');
    $intvl = $cfg['clear_reports_intvl'];

    $query = "DELETE FROM reports WHERE date < DATE_SUB(CURDATE(), INTERVAL $intvl DAY)";
    $db->query($query);
    $affected = $db->getAffected();
    Log::info('Clear host logs, affected rows ' . $affected);
}
