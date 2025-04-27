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
use App\Services\LogSystemService;
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

        $hosts_filter = [];

        if ($highlight == 1 && $hosts_other == 0) {
            $hosts_filter['only_highlight'] = 1;
        }

        if ($highlight == 0 && $hosts_other == 1) {
            $hosts_filter['not_highlight'] = 1;
        }

        # Filter User Selected Networks
        $user_networks = $user->getSelectedNetworks();
        if (!empty($user_networks) && count($user_networks) > 0) {
            $hosts_filter['networks'] = $user_networks;
        }

        # Filter User Selected Categories
        $valid_cats = $user->getEnabledHostCatId();

        if (count($valid_cats) > 0) {
           $hosts_filter['cats'] = $valid_cats;
        }

        $hosts_view = $this->hostService->getFiltered($hosts_filter);

        if (!$hosts_view) {
            return [];
        }

        $hosts_view = $this->filterNetwork($hosts_view, $networks);

        return $hosts_view;
    }

    /**
     *
     * @return array<string, mixed>
     */
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

    /**
     *
     * @return array<string, mixed>
     */
    public function getTermSystemLogs(): array
    {
        $opts = [
            'limit' => $this->ncfg->get('term_max_lines'),
            'level' => $this->ncfg->get('term_system_log_level')
        ];

        return $this->logSystemService->get($opts);
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
            $total['ansible_hosts_off'] = $total['ansible_hosts'] - $total['ansible_online'];
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
    private function filterNetwork(array $hosts_view,  $networks): array
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
}
