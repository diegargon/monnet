<?php
/**
 *
 * @author diego/@/envigo.net
 * @package
 * @subpackage
 * @copyright Copyright CC BY-NC-ND 4.0 @ 2020 - 2025 Diego Garcia (diego/@/envigo.net)
 */

namespace App\Services;

use App\Models\HostsModel;
use App\Services\LogHostsService;
use App\Services\HostService;

class RefresherService
{
    private \AppContext $ctx;
    private \Config $ncfg;
    private \DBManager $db;

    private LogHostsService $logHostService;
    private LogSystemService $logSystemService;
    private HostService $hostService;
    private HostsModel $hostsModel;


    public function __construct(\AppContext $ctx) {
        $this->ctx = $ctx;
        $this->db = $ctx->get('DBManager');
        $this->ncfg = $ctx->get('Config');

        $this->logHostService = new LogHostsService($ctx);
        $this->logSystemService = new LogSystemService($ctx);
        $this->hostService = new hostService($ctx);

        $this->hostsModel = new HostsModel($this->db);
    }

    /**
     * Obtiene la vista de hosts según el estado de "highlight".
     *
     * @param int $hosts_other
     * @param int $highlight
     * @return array<string, mixed>
     */
    public function getHostsView(int $hosts_other = 1, int $highlight = 0): array
    {
        $user = $this->ctx->get('User');
        $networks = $this->ctx->get('Networks');
        $ncfg = $this->ctx->get('Config');

        $hosts_filter = [];

        if ($highlight == 1 && $hosts_other == 0) {
            $hosts_filter['only_highlight'] = 1;
        }

        if ($highlight == 0 && $hosts_other == 1) {
            $hosts_filter['not_highlight'] = 1;
        }

/*
        $user_networks = $user->getSelectedNetworks();

        if (!empty($user_networks) && count($user_networks) > 0) {
            $hosts_filter['networks'] = $user_networks;
        }
  */


        $valid_cats = $user->getEnabledHostCatId();
        if (count($valid_cats) > 0) {
           $hosts_filter['cats'] = $valid_cats;
        }

        $hosts_view = $this->hostService->getFiltered($hosts_filter);


        if (!$hosts_view) {
            return [];
        }

        $hosts_view = $this->filter_hosts($hosts_view, $networks);
        $hosts_view = $this->format_hosts($hosts_view, $user, $ncfg);

        order($hosts_view, 'display_name');

        return $hosts_view;
    }

    public function getTermHostsLogs(): array
    {
        // Get Host Relate Logs for termlog
        $logs_opt = [
            'limit' => $this->ncfg->get('term_max_lines'),
            'level' => $this->ncfg->get('term_hosts_log_level'),
            'ack' => 1,
        ];
        $host_logs = $this->logHostService->getLogsHosts($logs_opt);

        return $host_logs;
    }

    public function getTermSystemLogs(): array
    {
        $logs_limit = $this->ncfg->get('term_max_lines');

        return $this->logSystemService->get($logs_limit);
    }

    /**
     *
     * @return array<string, int>
     */
    public function getHostsStats(): array
    {
        $ncfg = $this->ctx->get('Config');
        $total = $this->hostsModel->getTotalsStats();
        $online = $total['total_online'];
        $total['total_offline'] = $total['total_hosts'] - $online;
        $total['agent_online'] = $total['agent_installed'] - $total['agent_offline'];
        if ($ncfg->get('ansible')) {
            $total['ansible_hosts_off'] = $total['ansible_enabled'] - $total['ansible_online'];
        }

        return $total;
    }
    /**
     * Filtra los hosts donde la configuracion de red esta configurada
     * para que se muestren solo los onlines
     *
     * @param array<string, mixed> $hosts_view
     * @param array $user
     * @param \Networks $networks
     * @return array<string, mixed>
     */
    private function filter_hosts(array $hosts_view,  $networks): array
    {
        foreach ($hosts_view as $key => $host) {
            if (!empty($host['network'])) {
                $network = $networks->getNetworkById($host['network']);
                if ((int)$host['online'] === 0 && (int)$network['only_online'] === 1) {
                    unset($hosts_view[$key]);
                }
            }
        }

        return $hosts_view;
    }

    /**
     * Formatea los datos de los hosts para la vista.
     *
     * @param array<string, mixed> $hosts_view
     * @param object $user
     * @param object $ncfg
     * @return array<string, mixed>
     */
    private function format_hosts(array $hosts_view, $user, $ncfg): array
    {
        $theme = $user->getTheme();
        $lng = $this->ctx->get('lng');
        $date_now = new \DateTime('now', new \DateTimeZone('UTC'));

        foreach ($hosts_view as $key => $vhost) {
            $hosts_view[$key]['theme'] = $theme;
            $hosts_view[$key]['details'] = $lng['L_IP'] . ': ' . $vhost['ip'] . "\n";

            // Título del host
            if (empty($vhost['title'])) {
                $hosts_view[$key]['title'] = !empty($vhost['hostname']) ? explode('.', $vhost['hostname'])[0] : $vhost['ip'];
            } else {
                if (!empty($vhost['hostname'])) {
                    $hosts_view[$key]['details'] .= $lng['L_HOSTNAME'] . ': ' . $vhost['hostname'] . "\n";
                }
            }

            // Estado online/offline
            $hosts_view[$key]['title_online'] = $vhost['online'] ? $lng['L_S_ONLINE'] : $lng['L_S_OFFLINE'];
            $hosts_view[$key]['online_image'] = 'tpl/' . $theme . '/img/' . ($vhost['online'] ? 'green2.png' : 'red2.png');


            // Passing Reference
            $this->add_misc_data($hosts_view[$key], $vhost, $ncfg);
            $this->add_glow_tag($hosts_view[$key], $vhost, $date_now, $ncfg);
            $this->add_alerts_and_warnings($hosts_view[$key], $vhost, $theme);
        }

        // Ordenar por nombre para la vista
        order($hosts_view, 'display_name');

        return $hosts_view;
    }

    /**
     * Agrega datos adicionales como fabricante, sistema operativo y tipo de sistema.
     *
     * @param array<string, mixed> $host_view
     * @param array<string, mixed> $vhost
     * @param object $ncfg
     */
    private function add_misc_data(array &$host_view, array $vhost, $ncfg): void
    {
        if (!empty($vhost['misc']['manufacture'])) {
            $manufacture = get_manufacture_data($ncfg, $vhost['misc']['manufacture']);
            if (is_array($manufacture)) {
                $host_view['manufacture_image'] = $manufacture['manufacture_image'];
                $host_view['manufacture_name'] = $manufacture['name'];
            }
        }

        if (!empty($vhost['misc']['os'])) {
            $os = get_os_data($ncfg, $vhost['misc']['os']);
            if (is_array($os)) {
                $host_view['os_image'] = $os['os_image'];
                $host_view['os_name'] = $os['name'];
            }
        }

        if (!empty($vhost['misc']['system_type'])) {
            $system_type = get_system_type_data($ncfg, $vhost['misc']['system_type']);
            if (is_array($system_type)) {
                $host_view['system_type_image'] = $system_type['system_type_image'];
                $host_view['system_type_name'] = $system_type['name'];
            }
        }
    }

    /**
     * Agrega el tag de "glow" (resaltado) a un host.
     *
     * @param array<string, mixed> $host_view
     * @param array<string, mixed> $vhost
     * @param \DateTime $date_now
     * @param object $ncfg
     */
    private function add_glow_tag(array &$host_view, array $vhost, \DateTime $date_now, $ncfg): void
    {
        $host_view['glow_tag'] = '';
        $change_time = new \DateTime($vhost['glow'], new \DateTimeZone('UTC'));
        $diff = $date_now->diff($change_time);
        $minutes_diff = ($diff->days * 24 * 60) + ($diff->h * 60) + $diff->i;

        if ($minutes_diff > 0 && $minutes_diff <= $ncfg->get('glow_time')) {
            $host_view['glow_tag'] = $vhost['online'] ? ' host-glow-green' : ' host-glow-red';
        }
    }

    /**
     * Agrega alertas y advertencias a un host.
     *
     * @param array<string, mixed> $host_view
     * @param array<string, mixed> $vhost
     * @param string $theme
     */
    private function add_alerts_and_warnings(array &$host_view, array $vhost, string $theme): void
    {
        if (empty($vhost['misc']['disable_alarms'])) {
            if ($vhost['alert']) {
                $host_view['alert_mark'] = 'tpl/' . $theme . '/img/alert-mark.png';
            }
            if ($vhost['warn']) {
                $host_view['warn_mark'] = 'tpl/' . $theme . '/img/warn-mark.png';
            }
        }
    }
}
