<?php

/**
 *
 * @author diego/@/envigo.net
 * @package
 * @subpackage
 * @copyright Copyright CC BY-NC-ND 4.0 @ 2020 - 2025 Diego Garcia (diego/@/envigo.net)
 */
namespace App\Services;

use App\Models\NetworksModel;

Class NetworksService
{
    private NetworksModel $networksModel;
    private bool $allNetworksLoaded = false;

   /**
     * @var array<int, array<string, mixed>> $networks
     */
    private array $networks = [];

    /**
     *
     * @var AppContext
     */
    private AppContext $ctx;

    public function __construct(\AppContext $ctx) {
        $this->ctx = $ctx;
        $db = $ctx->get('DBManager');
        $this->networksModel = new NetworksModel($db);
    }

    /**
     *
     * @return array<int, array<string, mixed>>
     */
    public function getNetworks(): array
    {
        if (!$this->allNetworksLoaded) {
            $this->loadAllNetworks();
        }

        return $this->networks;
    }

    /**
     *
     * @param int $id
     * @return array<string, mixed>|false
     */
    public function getNetworkByID(int $id): array|false
    {
        if (isset($this->networks[$id])) {
            return $this->networks[$id];
        }

        if (!$this->allNetworksLoaded) {
            $network = $this->networksModel->getNetworkByID($id);
            if ($network) {
                $this->cacheNetwork($network);
                return $network;
            }
            return false;
        }

        return false;
    }

    public function networkExists(int $id): bool
    {
        return isset($this->networks[$id]) ||
               (!$this->allNetworksLoaded && $this->networksModel->getNetworkByID($id) !== false);
    }

    private function loadAllNetworks(): void
    {
        $networks = $this->networksModel->getAllNetworks();
        if (valid_array($networks)) {
            usort($networks, fn($a, $b) =>
                ((int)explode('/', $b['network'])[1]) <=> ((int)explode('/', $a['network'])[1]));

            foreach ($networks as $net) {
                $this->cacheNetwork($net);
            }
        }
        $this->allNetworksLoaded = true;
    }

    private function cacheNetwork(array $network): void
    {
        $id = (int) $network['id'];
        $this->networks[$id] = [
            'id' => $id,
            'network' => $network['network'],
            'name' => $network['name'],
            'vlan' => (int) $network['vlan'],
            'scan' => (int) $network['scan'],
            'weight' => (int) $network['weight'],
            'only_online' => (int) $network['only_online'],
            'disable' => (int) $network['disable'],
            'pool' => $this->ctx->get('Config')->get('monnet_version') >= 0.44
                      ? $network['pool']
                      : null
        ];
    }


    public function addNetwork(array $set): int
    {
        $cidr = $set['network'] ?? '';

        if ($this->networkExistsByCIDR($cidr)) {
            throw new RuntimeException("La red $cidr ya existe");
        }

        $id = $this->networksModel->addNetwork($set);
        $this->cacheNetwork(array_merge(['id' => $id], $set));

        return $id;
    }

    private function networkExistsByCIDR(string $cidr): bool
    {
        foreach ($this->networks as $network) {
            if ($network['network'] === $cidr) {
                return true;
            }
        }

        return $this->networksModel->networkExistsByCIDR($cidr);
    }

    /**
     *
     * @param int $id
     * @param array<string|int,string> $set
     * @return void
     */

    public function updateNetwork(int $id, array $set): void
    {
        $this->networksModel->updateNetwork($id, $set);

        if (isset($this->networks[$id])) {
            $this->networks[$id] = array_merge($this->networks[$id], $set);
        } else {
            $network = $this->networksModel->getNetworkByID($id);
            if ($network) {
                $this->cacheNetwork($network);
            }
        }
    }

    /**
     *
     * @param int $id
     * @return bool
     */
    public function removeNetwork(int $id): bool
    {
        $this->networksModel->deleteNetwork($id);
        unset($this->networks[$id]);
        return true;
    }



    public function getNetworkIDbyIP(string $ip): int|false
    {
        $ip_long = ip2long($ip);

        if ($ip_long === false) {
            return false;
        }

        foreach ($this->networks as $network) {
            list($network_ip, $cidr) = explode('/', $network['network']);
            $cidr = (int) $cidr;
            $network_ip_long = ip2long($network_ip);
            $subnet_mask = -1 << (32 - $cidr);
            $network_ip_long &= $subnet_mask;

            if (($ip_long & $subnet_mask) == $network_ip_long) {
                return $network['id'];
            }
        }

        // TODO implementar metodo de busqueda especifico para no cargar todas.
        if (!$this->allNetworksLoaded) {
            $this->loadAllNetworks();
            return $this->getNetworkIDbyIP($ip);
        }

        return false;
    }


    /**
     *
     * @param int $id
     * @return string|false
     */
    public function getNetworkNameByID(int $id): string|false
    {
        $networks = $this->getNetworks();

        foreach ($networks as $network) {
            if ($network['id'] == $id) {
                return $network['name'];
            }
        }
        return false;
    }

    /**
     *
     * @param int $hostsPerNetwork
     * @return array<int,<string,mixed>>|false
     */
    public function getPoolIPs(int $hostsPerNetwork = 1): array|false
    {
        $networks = $this->getNetworks();
        $hosts = $this->ctx->get('Hosts');

        $pool_networks = [];

        foreach ($networks as $network) :
            if ((int) $network['pool'] === 1 && empty($network['disable'])) :
                $pool_networks[] = $network;
            endif;
        endforeach;

        if (empty($pool_networks)) :
            return false;
        endif;

        $free_ips = [];


        foreach ($pool_networks as $netpool) :
            // Get known hosts
            $hosts_list = $hosts->getHostsByNetworkId($netpool['id']);

            // Get known hosts ips
            $used_ips = array_filter($hosts_list, function ($host) use ($netpool) {
                return $host['network'] == $netpool['id'];
            });
            $used_ips = array_column($used_ips, 'ip');

            // Generate Ips
            [$network_address, $cidr] = explode('/', $netpool['network']);
            $subnet_mask = 32 - (int) $cidr;
            $total_hosts = pow(2, $subnet_mask);
            $network_base = ip2long($network_address);
            $network_free_ips = [];

            for ($i = 1; $i < $total_hosts - 1; $i++) :
                $current_ip = long2ip($network_base + $i);

                // if free we add to the pool
                if (!in_array($current_ip, $used_ips)) :
                    $network_free_ips[] = $current_ip;
                    if (count($network_free_ips) >= $hostsPerNetwork) :
                        break;
                    endif;
                endif;
            endfor;

            if (!empty($network_free_ips)) :
                $netpool['pool'] = $network_free_ips;
                $free_ips[] = $netpool;
            endif;
        endforeach;

        return !empty($free_ips) ? $free_ips : false;
    }

    /**
     *
     * @param string $ip
     * @return array<string, mixed>|false
     */
    public function matchNetwork($ip): array|false
    {
        $ip = ip2long($ip);

        $defaultNetwork = false;

        foreach ($this->networks as $network) {
            list($networkAddr, $subnetMask) = explode('/', $network['network']);
            $subnetMask = (int) $subnetMask;
            $networkAddr = ip2long($networkAddr);

            $broadcastAddr = $networkAddr | ~(pow(2, (32 - $subnetMask)) - 1);

            if (($ip & ~($broadcastAddr)) == ($networkAddr & ~($broadcastAddr))) {
                return $network;
            }

            if ($network['network'] == '0.0.0.0/0') {
                $defaultNetwork = $network;
            }
        }

        return $defaultNetwork;
    }

    /**
     *
     * @param string $ip
     * @return bool
     */
    public function isLocal(string $ip): bool
    {
        if (!filter_var($ip, FILTER_VALIDATE_IP, FILTER_FLAG_NO_PRIV_RANGE | FILTER_FLAG_NO_RES_RANGE)) {
            return true;
        }
        return false;
    }


    /**
     * Source https://stackoverflow.com/questions/15521725/php-generate-ips-list-from-ip-range/15613770
     * @return array<int, mixed>
     */
    public function buildIpScanList(): array
    {
        $ip_list = [];
        $networks = $this->getNetworks();

        foreach ($networks as $net) {
            if ($net['disable'] || $net['scan'] !== 1) {
                continue;
            }
            if (empty($net['network']) || Filters::varNetwork($net['network']) === false) {
                Log::error("Invalid network detected " . $net['network']);
                continue;
            }
            /*
             * Jump 0.0.0.0 (we use 0.* for add internet host)
             */
            if (str_starts_with($net['network'], "0")) {
                continue;
            }
            Log::debug("Ping networks " . array2string($net));
            $parts = explode('/', $net['network']);
            $network = $parts[0];
            $prefix = (int) $parts[1];
            $count = pow(2, (32 - $prefix));
            if (!filter_var($network, FILTER_VALIDATE_IP)) {
                Log::error("Invalid  ip build network for scan");
                continue;
            }
            // Obtener la dirección de red
            $network_long = ip2long($network);
            $network_address = long2ip($network_long & ((-1 << (32 - $prefix))));
            if (!$network_address) {
                return [];
            }
            $network_long = ip2long($network_address);
            $broadcast_address = long2ip($network_long | ((1 << (32 - $prefix)) - 1));

            // Calcular las direcciones IP restantes dentro de la red
            for ($i = 0; $i < $count && $i <= 255; $i++) {
                $ip = long2ip($network_long + $i);
                if ($ip != $network_address && $ip != $broadcast_address) {
                    $ip_list[] = $ip;
                }
            }
        }

        return $ip_list;
    }

}