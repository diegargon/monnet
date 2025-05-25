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
        $this->logSystemService = new LogSystemService($ctx);
    }

    /**
     * Muestra el informe de inventario.
     * @param array $command_values
     * @return array
     */
    public function showInventory(array $command_values): array
    {
        $hosts = $this->hostService->getAll();
        $networks = $this->networksService->getNetworks();

        $this->logSystemService->notice('Entry point: showInventory');
        // Obtener roles del sistema (id => name)
        $configService = $this->ctx->get(ConfigService::class);
        $system_roles = $configService->get('system_rol');
        $rol_map = [];
        if (is_array($system_roles)) {
            foreach ($system_roles as $role) {
                // Asegura que $role sea un array y tenga los campos requeridos
                if (is_array($role) && isset($role['id']) && isset($role['name'])) {
                    $rol_map[$role['id']] = $role['name'];
                }
            }
            $this->logSystemService->notice('Roles del sistema obtenidos: ' . json_encode($rol_map));
        } else {
            $this->logSystemService->error('Error al obtener los roles del sistema: ' . json_encode($system_roles));
        }

        // --- Formateo de hosts para la vista ---
        // Crear un mapa de VLANs por ID de red
        $vlan_map = [];
        foreach ($networks as $net) {
            $vlan_map[$net['id']] = [
                'vlan' => $net['vlan'] ?? '',
                'name' => $net['name'] ?? ''
            ];
        }
        foreach ($hosts as &$host) {
            // display_name
            if (!empty($host['title'])) {
                $host['display_name'] = $host['title'];
            } elseif (!empty($host['hostname'])) {
                $host['display_name'] = explode('.', $host['hostname'])[0];
            } else {
                $host['display_name'] = $host['ip'];
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
        }
        unset($host);
        // --- Fin formateo hosts ---

        $hosts = $this->formatHosts($hosts, $rol_map);

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
     * @return array
     */
    private function formatHosts(array $hosts, array $rol_map = []): array
    {
        $categories = $this->categoriesService->getAll();
        $cat_map = [];
        foreach ($categories as $cat) {
            $cat_map[$cat['id']] = $cat['cat_name'];
        }
        foreach ($hosts as &$host) {
            if (isset($host['category']) && isset($cat_map[$host['category']])) {
                $cat_name = $cat_map[$host['category']];
                $host['category'] = isset($this->lng[$cat_name]) ? $this->lng[$cat_name] : $cat_name;
            }
            if (isset($host['rol']) && isset($rol_map[$host['rol']])) {
                $host['rol_name'] = $rol_map[$host['rol']];
            } else {
                $host['rol_name'] = 'N/A';
            }
        }
        unset($host);
        return $hosts;
    }
}
