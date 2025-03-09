<?php

/**
 *
 * @author diego/@/envigo.net
 * @package
 * @subpackage
 * @copyright Copyright CC BY-NC-ND 4.0 @ 2020 - 2025 Diego Garcia (diego/@/envigo.net)
 */

namespace App\Services;

class HostFormatter
{
    private \AppContext $ctx;

    public function __construct(\AppContext $ctx)
    {
        $this->ctx = $ctx;
    }

    public function format(array $host): array
    {
        $lng = $this->ctx->get('lng');
        $cfg = $this->ctx->get('cfg');
        $user = $this->ctx->get('User');
        $networks = $this->ctx->get('Networks');
        $categories = $this->ctx->get('Categories');

        $id = (int) $host['id'];
        $net_id = $host['network'];
        $theme = $user->getTheme();

        $host['theme'] = $theme;
        $host['display_name'] = $this->getDisplayName($host);
        $host['hosts_categories'] = $categories->getByType(1);

        $network = $networks->getNetworkByID($net_id);
        if ($network !== false) {
            $host['net_cidr'] = $network['network'];
            $host['network_name'] = $network['name'];
            $host['network_vlan'] = $network['vlan'];
        } else {
            Log::warning('Host network seems not exists: ' . "[H: $id][N: $net_id]");
        }

        if ($host['online']) :
            $host['title_online'] = $lng['L_S_ONLINE'];
            $host['online_image'] = 'tpl/' . $theme . '/img/green2.png';
        else :
            $host['title_online'] = $lng['L_S_OFFLINE'];
            $host['online_image'] = 'tpl/' . $theme . '/img/red2.png';
        endif;

        if (!empty($host['last_seen'])) :
            $host['f_last_seen'] = utc_to_tz($host['last_seen'], $cfg['timezone'], $cfg['datetime_format']);
        endif;

        if (!empty($host['last_check'])) :
            $host['f_last_check'] = utc_to_tz($host['last_check'], $cfg['timezone'], $cfg['datetime_format']);
        endif;

        $host['formated_creation_date'] = utc_to_tz($host['created'], $cfg['timezone'], $cfg['datetime_format']);

        if ($host['online'] && !empty($host['latency'])) :
            $host['latency_ms'] = micro_to_ms($host['latency']) . 'ms';
        endif;

        $this->formatMisc($host);

        return $host;
    }

    /**
     *
     * @param array<string, mixed> $host
     *
     * @return string
     */
    public function getDisplayName(array $host): string
    {
        if (!empty($host['title'])) {
            return $host['title'];
        } elseif (!empty($host['hostname'])) {
            return ucfirst(explode('.', $host['hostname'])[0]);
        }

        return $host['ip'];
    }

    /**
     *
     * @param string $misc
     * @return array<string, string|int>
     */
    public function decodeMisc(string $misc): array
    {
        if (empty($misc)) {
            return [];
        }
        $misc = json_decode($misc, true);
        if (json_last_error() !== JSON_ERROR_NONE) {
           \Log::warning('Error decodeMisc');

            return ['status' => 'error'];
        }

        return $misc;
    }

    /**
     *
     * @param array<string, string|int> $logs_items
     * @return array<string, string|int>
     */
    public function fHostLogsMsgs(array $logs_items): array
    {
        $log_msg = [];
        $flogs_msgs = [];
        $flogs_items = [];
        $ncfg = $this->ctx->get('Config');

        foreach ($logs_items as $item) :
            if (!in_array($item['msg'], $flogs_msgs)) :
                $flogs_msgs[] = $item['msg'];
                $flogs_items[] = $item;
            endif;
        endforeach;
        $timezone = $ncfg->get('timezone');
        $timeformat = $ncfg->get('datetime_format_min');
        foreach ($flogs_items as $item) :
            $date = utc_to_tz($item['date'], $timezone, $timeformat);
            $log_msg[] = [
                'log_id' => $item['id'],
                'log_type' => \LogType::getName($item['log_type']),
                'event_type' => \EventType::getName($item['event_type']),
                'msg' => "{$item['msg']} - $date",
                'ack_state' => $item['ack']
            ];
        endforeach;

        return $log_msg;
    }

    /**
     *
     * @param array $host<string, string|int>
     */
    public function formatMisc(array &$host): void
    {
        $lng = $this->ctx->get('lng');

        if (!empty($host['misc'])) {
            $host['misc'] = $this->decodeMisc($host['misc']);
            /* TODO: Migrate: keep misc values in misc then delete this */
            $host = array_merge($host, $host['misc']);
        }


        if (!empty($host['misc']['load_avg'])) :
            $loadavg = unserialize($host['misc']['load_avg']);

            (!empty($host['ncpu'])) ? $ncpu = (float) $host['ncpu'] : (float) $ncpu = 1;
            $m1 = floatToPercentage((float) $loadavg['1min'], 0.0, $ncpu);
            $m5 = floatToPercentage((float) $loadavg['5min'], 0.0, $ncpu);
            $m15 = floatToPercentage((float) $loadavg['15min'], 0.0, $ncpu);

            $host['load_avg'] = [
                ['value' => round($m1, 1), 'legend' => $lng['L_LOAD'] . ' 1m', 'min' => 0, 'max' => 100],
                ['value' => round($m5, 1), 'legend' => $lng['L_LOAD'] . ' 5m', 'min' => 0, 'max' => 100],
                ['value' => round($m15, 1), 'legend' => $lng['L_LOAD'] . ' 15m', 'min' => 0, 'max' => 100],
            ];
        endif;
        if (!empty($host['misc']['mem_info'])) {
            $mem_info = unserialize($host['misc']['mem_info']);
            $total = $mem_info['total'];
            $used = $mem_info['used'];
            $gtotal = mbToGb($total, 0);
            $gused = mbToGb($used, 0);
            $gfree = mbToGb($mem_info['free'], 0);
            $legend = "{$lng['L_MEMORY']}: ({$mem_info['percent']}%) {$lng['L_TOTAL']}:{$gtotal}GB";
            $tooltip = "{$lng['L_USED']} {$gused}GB/{$lng['L_FREE']} {$gfree}GB";
            $host['mem_info'] = [
                    'value' => $used, 'legend' => $legend, 'tooltip' => $tooltip, 'min' => 0, 'max' => $total
            ];
        }

        if (!empty($host['misc']['disks_info'])) :
            $disksinfo = unserialize($host['misc']['disks_info']);
            $host['disks_info'] = [];

            foreach ($disksinfo as $disk) :
                $disk_percent = round($disk['percent']);
                $name = substr($disk['mountpoint'], strrpos($disk['mountpoint'], '/'));
                $legend = "($disk_percent%): $name";
                $gused = mbToGb($disk['used'], 0);
                $gfree = mbToGb($disk['free'], 0);
                $tooltip = "{$lng['L_USED']} {$gused}GB/{$lng['L_FREE']} {$gfree}GB\n{$disk['device']} {$disk['fstype']}";

                $host['disks_info'][] = [
                    'value' => $disk['used'],
                    'legend' => $legend,
                    'tooltip' => $tooltip,
                    'min' => 0,
                    'max' => $disk['total']
                ];
            endforeach;
        endif;
    }
}
