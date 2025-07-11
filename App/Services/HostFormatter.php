<?php

/**
 *
 * @author diego/@/envigo.net
 * @copyright Copyright CC BY-NC-ND 4.0 @ 2020 - 2025 Diego Garcia (diego/@/envigo.net)
 */

namespace App\Services;

use App\Core\AppContext;
use App\Core\Config;

use App\Services\LogSystemService;
use App\Services\NetworksService;
use App\Services\DateTimeService;
use App\Services\CategoriesService;
use App\Services\UserService;
use App\Utils\MiscUtils;

use App\Constants\LogLevel;
use App\Constants\LogType;
use App\Constants\EventType;

class HostFormatter
{
    private AppContext $ctx;
    private LogSystemService $logSys;
    private NetworksService $networksService;
    private Config $ncfg;

    public function __construct(AppContext $ctx)
    {
        $this->ctx = $ctx;
        $this->logSys = new LogSystemService($ctx);
        $this->ncfg = $ctx->get(Config::class);
    }

    /**
     *
     * @param array<string, mixed> $host
     * @return array<string, mixed>
     */
    public function format(array $host): array
    {
        $lng = $this->ctx->get('lng');
        $user = $this->ctx->get(UserService::class);
        $timezone = $user->getTimezone();

        if (!isset($this->networksService)) {
            $this->networksService = new NetworksService($this->ctx);
        }
        $categories = $this->ctx->get(CategoriesService::class);

        $id = (int) $host['id'];
        $net_id = $host['network'];
        $theme = $user->getTheme();

        $host['theme'] = $theme;
        $host['display_name'] = $this->getDisplayName($host);
        $host['hosts_categories'] = $categories->getByType(1);

        $network = $this->networksService->getNetworkByID($net_id);
        if ($network !== false) {
            $host['net_cidr'] = $network['network'];
            $host['network_name'] = $network['name'];
            $host['network_vlan'] = $network['vlan'];
        } else {
            $this->logSys->warning('Host network seems not exists: ' . "[H: $id][N: $net_id]");
        }

        if ($host['online']) :
            $host['title_online'] = $lng['L_S_ONLINE'];
            $host['host-status'] = 'led-green-on';
        else :
            $host['title_online'] = $lng['L_S_OFFLINE'];
            $host['host-status'] = 'led-red-on';
        endif;

        if (!empty($host['last_check'])) :
            $host['f_last_check'] = DateTimeService::utcToTz(
                $host['last_check'],
                $timezone,
                $this->ncfg->get('datetime_format')
            );
        endif;

        if (!empty($host['last_seen'])) :
            $host['f_last_seen'] = DateTimeService::utcToTz(
                $host['last_seen'],
                $timezone,
                $this->ncfg->get('datetime_format')
            );
        endif;

        $host['formated_creation_date'] = DateTimeService::utcToTz(
            $host['created'],
            $timezone,
            $this->ncfg->get('datetime_format')
        );

        if ($host['online'] && !empty($host['misc']['latency'])) :
            $host['latency_ms'] = MiscUtils::microToMs($host['misc']['latency']) . 'ms';
        endif;

        $this->formatMisc($host); // &REF

        if ($host['agent_installed']) {
            if (empty($host['misc']['agent_log_level'])) {
                $host['misc']['agent_log_level'] = $this->ncfg->get('agent_log_level', 'INFO');
            }
            if (empty($host['misc']['mem_alert_threshold'])) {
                $host['misc']['mem_alert_threshold'] = $this->ncfg->get('default_mem_alert_threshold', 90);
            }
            if (empty($host['misc']['mem_warn_threshold'])) {
                $host['misc']['mem_warn_threshold'] = $this->ncfg->get('default_mem_warn_threshold', 80);
            }
            if (empty($host['misc']['disks_alert_threshold'])) {
                $host['misc']['disks_alert_threshold'] = $this->ncfg->get('default_disks_alert_threshold', 90);
            }
            if (empty($host['misc']['disks_warn_threshold'])) {
                $host['misc']['disks_warn_threshold'] = $this->ncfg->get('default_disks_warn_threshold', 80);
            }
            /*
            if (!isset($hostDetails['misc']['cpu_alert_threshold'])) {
                $hostDetails['misc']['cpu_alert_threshold'] = $this->ncfg->get('default_cpu_alert_threshold');

            }
            if (!isset($hostDetails['misc']['cpu_warn_threshold'])) {
                $hostDetails['misc']['cpu_warn_threshold'] = $this->ncfg->get('default_cpu_warn_threshold');
            }
            */
        }

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
     * @param array<string, mixed> $logs_items
     * @return array<string, string|int>
     */
    public function fHostLogsMsgs(array $logs_items): array
    {
        $log_msg = [];

        # Usado para evitar repeticion de mensajes deshabilitado por que no tienen en cuenta
        # la hora
        /*
        $flogs_msgs = [];
        $flogs_items = [];
        foreach ($logs_items as $item) :
            if (!empty($item['msg']) && !in_array($item['msg'], $flogs_msgs)) :
                $flogs_msgs[] = $item['msg'];
                $flogs_items[] = $item;
            endif;
        endforeach;
        */
        $user = $this->ctx->get(UserService::class);
        $timezone = $user->getTimezone();
        $timeformat = $this->ncfg->get('datetime_format_min');
        foreach ($logs_items as $item) :
            $date = DateTimeService::utcToTz($item['date'], $timezone, $timeformat);
            $msg = '';
            $msg .= '[' . $date . '] ';
            $msg .= !empty($item['msg']) ? $item['msg'] : '?';
            $msg .= '[' . LogType::getName($item['log_type']) . '] ';
            $msg .= '[' . EventType::getName($item['event_type']) . ']';

            $log_msg[] = [
                'log_id' => $item['id'],
                'msg' => $msg,
                'ack_state' => $item['ack']
            ];
        endforeach;

        return $log_msg;
    }

    /**
     *
     * @param array $host<string, mixed>
     */
    public function formatMisc(array &$host): void
    {
        $lng = $this->ctx->get('lng');

        if (!empty($host['misc']['load_avg'])) :
            $loadavg = unserialize($host['misc']['load_avg']);

            (!empty($host['misc']['ncpu'])) ? $ncpu = (int) $host['misc']['ncpu'] : $ncpu = 1;
            $m1 = MiscUtils::floatToPercentage((float) $loadavg['1min'], 0.0, $ncpu);
            $m5 = MiscUtils::floatToPercentage((float) $loadavg['5min'], 0.0, $ncpu);
            $m15 = MiscUtils::floatToPercentage((float) $loadavg['15min'], 0.0, $ncpu);

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
            $gtotal = MiscUtils::mbToGb($total, 0);
            $gused = MiscUtils::mbToGb($used, 0);
            $gfree = MiscUtils::mbToGb($mem_info['free'], 0);
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
                $gused = MiscUtils::mbToGb($disk['used'], 0);
                $gfree = MiscUtils::mbToGb($disk['free'], 0);
                $tooltip = "{$lng['L_USED']} {$gused}GB/{$lng['L_FREE']} {$gfree}GB\n";
                $tooltip .= "{$disk['device']} {$disk['fstype']}";

                $host['disks_info'][] = [
                    'value' => $disk['used'],
                    'legend' => $legend,
                    'tooltip' => $tooltip,
                    'min' => 0,
                    'max' => $disk['total']
                ];
            endforeach;
        endif;

        if (!empty($host['misc']['uptime'])) {
            $host['misc']['uptime'] = $this->formatUptime($host['misc']['uptime']);
        }

        if (!empty($host['misc']['agent_last_contact'])) {
            $user = $this->ctx->get(UserService::class);
            $host['misc']['f_agent_contact'] = DateTimeService::formatTimestamp(
                $host['misc']['agent_last_contact'],
                $user->getTimezone(),
                $this->ncfg->get('datetime_format')
            );
        }
    }

    /**
     * Converts system uptime in seconds to a human-readable format.
     *
     * @param float $seconds The uptime in seconds.
     * @return string Human-readable uptime format.
     */
    public function formatUptime(float $seconds): string
    {
        $days = floor($seconds / 86400);
        $seconds %= 86400;

        $hours = floor($seconds / 3600);
        $seconds %= 3600;

        $minutes = floor($seconds / 60);
        $seconds %= 60;

        return sprintf("%d days, %d:%d:%d", $days, $hours, $minutes, $seconds);
    }
}
