<?php
/**
 * Controller for Inventory report.
 *
 * @author diego/@/envigo.net
 * @copyright Copyright CC BY-NC-ND 4.0 @ 2020 - 2025 Diego Garcia (diego/@/envigo.net)
 */
namespace App\Controllers;

use App\Core\AppContext;
use App\Core\ConfigService;
use App\Services\HostService;
use App\Services\NetworksService;
use App\Services\TemplateService;
use App\Services\CategoriesService;
use App\Services\LogSystemService;

use App\Helpers\Response;

class CmdInventoryController
{
    private AppContext $ctx;
    private HostService $hostService;
    private NetworksService $networksService;
    private TemplateService $templateService;
    private CategoriesService $categoriesService;
    private LogSystemService $logSystemService;
    private array $lng = [];

    public function __construct(AppContext $ctx)
    {
        $this->ctx = $ctx;
        $this->lng = $ctx->get('lng');
        $this->hostService = new HostService($ctx);
        $this->networksService = new NetworksService($ctx);
        $this->templateService = new TemplateService($ctx);
        $this->categoriesService = new CategoriesService($ctx);
        //$this->logSystemService = new LogSystemService($ctx);
    }

    /**
     * Shows the inventory report.
     * @param array $command_values
     * @return array
     */
    public function showInventory(array $command_values): array
    {
        $hosts = $this->hostService->getAll();
        $networks = $this->networksService->getNetworks();

        // Get system roles (id => name)
        $configService = $this->ctx->get(ConfigService::class);
        $system_roles = $configService->get('system_rol');
        $rol_map = [];
        if (is_array($system_roles)) {
            foreach ($system_roles as $role) {
                // Ensure $role is an array and has required fields
                if (is_array($role) && isset($role['id']) && isset($role['name'])) {
                    $rol_map[$role['id']] = $role['name'];
                }
            }
        }

        // Create VLAN map by network ID
        $vlan_map = [];
        foreach ($networks as $net) {
            $vlan_map[$net['id']] = [
                'vlan' => $net['vlan'] ?? '',
                'name' => $net['name'] ?? ''
            ];
        }

        // Create host map by id for fast linked lookup
        $host_id_map = [];
        foreach ($hosts as $h) {
            if (isset($h['id'])) {
                $host_id_map[$h['id']] = $h;
            }
        }

        // Format hosts
        $hosts = $this->formatHosts($hosts, $rol_map, $vlan_map, $host_id_map);

        $tdata = [
            'hosts' => $hosts,
            'networks' => $networks
        ];

        $report = $this->templateService->getTpl('inventory-report', $tdata);

        return Response::stdReturn(true, 'ok', false, [
            'command_receive' => 'showInventory',
            'response_msg' => $report,
        ]);
    }

    /**
     * Format hosts
     * @param array $hosts
     * @param array $rol_map
     * @param array $vlan_map
     * @param array $host_id_map
     * @return array
     */
    private function formatHosts(array $hosts, array $rol_map = [], array $vlan_map = [], array $host_id_map = []): array
    {
        $categories = $this->categoriesService->getAll();
        $cat_map = [];
        foreach ($categories as $cat) {
            $cat_map[$cat['id']] = $cat['cat_name'];
        }
        foreach ($hosts as &$host) {
            // display_name
            $host['display_name'] = $this->getDisplayName($host);

            // linked_name
            if (isset($host['linked']) && $host['linked'] != 0 && isset($host_id_map[$host['linked']])) {
                $host['linked_name'] = $this->getDisplayName($host_id_map[$host['linked']]);
            } else {
                $host['linked_name'] = '';
            }

            // vlan (name(vlanid))
            if (isset($host['network']) && isset($vlan_map[$host['network']])) {
                $host['vlan'] = $vlan_map[$host['network']]['name'] . ' (' . $vlan_map[$host['network']]['vlan'] . ')';
            } else {
                $host['vlan'] = '';
            }

            // last_seen_fmt
            if (!empty($host['last_seen'])) {
                try {
                    $dt = new \DateTime($host['last_seen']);
                    $host['last_seen_fmt'] = $dt->format('Y-m-d');
                } catch (\Exception $e) {
                    $host['last_seen_fmt'] = '';
                }
            } else {
                $host['last_seen_fmt'] = '';
            }

            // rol_name
            if (isset($host['rol']) && isset($rol_map[$host['rol']])) {
                $host['rol_name'] = $rol_map[$host['rol']];
            } else {
                $host['rol_name'] = 'N/A';
            }

            // category (translation)
            if (isset($host['category']) && isset($cat_map[$host['category']])) {
                $cat_name = $cat_map[$host['category']];
                $host['category'] = isset($this->lng[$cat_name]) ? $this->lng[$cat_name] : $cat_name;
            }
        }
        unset($host);
        return $hosts;
    }

    /**
     * Get the display_name of a host.
     * @param array $host
     * @return string
     */
    private function getDisplayName(array $host): string
    {
        if (!empty($host['title'])) {
            return $host['title'];
        } elseif (!empty($host['hostname'])) {
            return explode('.', $host['hostname'])[0];
        } elseif (!empty($host['ip'])) {
            return $host['ip'];
        }
        return '';
    }
}
