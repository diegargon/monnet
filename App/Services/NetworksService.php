<?php

/**
 *
 * @author diego/@/envigo.net
 * @copyright Copyright CC BY-NC-ND 4.0 @ 2020 - 2025 Diego Garcia (diego/@/envigo.net)
 */
namespace App\Services;

use App\Models\NetworksModel;
use App\Services\Filter;
use App\Services\HostService;

Class NetworksService
{
    /**
     * @var NetworksModel
     */
    private NetworksModel $networksModel;

    /**
     * @var bool $allNetworksLoaded
     */
    private bool $allNetworksLoaded = false;

    /**
     * @var array<int, array<string, mixed>> $networks
     */
    private array $networks = [];

    /**
     * @var \AppContext
     */
    private \AppContext $ctx;

    public function __construct(\AppContext $ctx)
    {
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

    /**
     *
     * @param int $id
     * @return bool
     */
    public function networkExists(int $id): bool
    {
        return isset($this->networks[$id]) ||
            (!$this->allNetworksLoaded && $this->networksModel->getNetworkByID($id) !== false);
    }

    /**
     *
     * @return void
     */
    private function loadAllNetworks(): void
    {
        $networks = $this->networksModel->getAllNetworks();
        if (valid_array($networks)) {
            usort($networks, fn($a, $b) =>
                ((int)explode('/', $b['network'])[1] ?? 0) <=> ((int)explode('/', $a['network'])[1] ?? 0)
            );

            foreach ($networks as $net) {
                $this->cacheNetwork($net);
            }
        }
        $this->allNetworksLoaded = true;
    }

    /**
     *
     * @param array<string, string|int> $network
     * @return void
     */
    private function cacheNetwork(array $network): void
    {
        $id = (int) $network['id'];
        $network['only_online'] = $network['only_online'] ?? 0;
        $this->networks[$id] = [
            'id' => $id,
            'network' => $network['network'],
            'name' => $network['name'],
            'vlan' => (int) $network['vlan'],
            'scan' => (int) $network['scan'],
            'weight' => (int) $network['weight'],
            'only_online' => (int) $network['only_online'],
            'disable' => (int) $network['disable'],
            'clean' => (int) $network['clean'],
            'pool' => $network['pool']
        ];
    }


    /**
     *
     * @param array<string, string|int> $set
     * @return int|bool
     */
    /* TODO RETURN UI ERROR */
    public function addNetwork(array $set): int|bool
    {
        $cidr = $set['network'] ?? '';

        if ($this->networkExistsByCIDR($cidr)) {
            \Log::error("Network already exists: " . $cidr);
            return false;
        }

        $id = $this->networksModel->addNetwork($set);
        $this->cacheNetwork(array_merge(['id' => $id], $set));

        return $id;
    }

    /**
     *
     * @param string $cidr
     * @return bool
     */
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
        $network = $this->getNetworkByID($id);
        return $network ? $network['name'] : false;
    }

    /**
     *
     * @param int $hostsPerNetwork
     * @return array<int, array<string, mixed>>|false
     */
    public function getPoolIPs(int $hostsPerNetwork = 1): array|false
    {
        if (!$this->allNetworksLoaded) {
            $this->loadAllNetworks();
        }

        $hostService = new HostService($this->ctx);

        $pool_networks = array_filter($this->networks, fn($network) =>
            (int)$network['pool'] === 1 && empty($network['disable'])
        );

        if (empty($pool_networks)) {
            return false;
        }

        $free_ips = [];

        foreach ($pool_networks as $netpool) {
            $hosts_list = $hostService->getHostsByNetworkId($netpool['id']);
            $num_hosts = count($hosts_list);

            // Get known hosts ips
            $used_ips = array_filter($hosts_list, function ($host) use ($netpool) {
                return $host['network'] == $netpool['id'];
            });
            $used_ips = array_column($used_ips, 'ip');

            // Generate Ips
            [$network_address, $cidr] = explode('/', $netpool['network']);
            $subnet_mask = 32 - (int)$cidr;
            # Exclude network and broadcast address
            $total_hosts = pow(2, $subnet_mask) - 2;
            $network_base = ip2long($network_address);
            $network_free_ips = [];

            for ($i = 1; $i < $total_hosts; $i++) {
                $current_ip = long2ip($network_base + $i);

                // if free we add to the pool
                if (!in_array($current_ip, $used_ips)) {
                    $network_free_ips[] = $current_ip;
                    if (count($network_free_ips) >= $hostsPerNetwork) {
                        break;
                    }
                }
            }

            // Calculate occupancy percentage
            $used_count = count($used_ips);
            $occupancy_percentage = ($used_count / $total_hosts) * 100;

            if (!empty($network_free_ips)) {
                $netpool['pool'] = $network_free_ips;
                $netpool['occupancy'] = round($occupancy_percentage, 2);
                $free_ips[] = $netpool;
            }
        }

        return !empty($free_ips) ? $free_ips : false;
    }

    /**
     *
     * @param string $ip
     * @return array<string, mixed>|false
     */
    public function matchNetwork(string $ip): array|false
    {
        $ip_long = ip2long($ip);

        if ($ip_long === false) {
            return false;
        }

        if (!$this->allNetworksLoaded) {
            $this->loadAllNetworks();
        }

        $defaultNetwork = false;

        foreach ($this->networks as $network) {
            [$networkAddr, $subnetMask] = explode('/', $network['network']) + [null, null];
            $subnetMask = (int)$subnetMask;
            $networkAddrLong = ip2long($networkAddr);

            if ($networkAddrLong === false || $subnetMask === null) {
                continue;
            }

            $subnetMaskLong = -1 << (32 - $subnetMask);
            if (($ip_long & $subnetMaskLong) === ($networkAddrLong & $subnetMaskLong)) { // Fix bitwise operation
                return $network;
            }

            if ($network['network'] === '0.0.0.0/0') {
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
     * Builds a list of IPs to scan based on the networks configuration.
     *
     * @return array<int, string> List of IPs to scan.
     */
    public function buildIpScanList(): array
    {
        if (!$this->allNetworksLoaded) {
            $this->loadAllNetworks();
        }

        $ip_list = [];

        foreach ($this->networks as $network) {
            if ((int)$network['disable'] === 1 || (int)$network['scan'] !== 1) {
                continue;
            }

            if (empty($network['network']) || Filter::varNetwork($network['network']) === false) {
                \Log::error("Invalid network detected: " . $network['network']);
                continue;
            }

            // Skip networks starting with "0" (used for internet hosts)
            if (strpos($network['network'], "0") === 0) {
                continue;
            }

            [$network_address, $prefix] = explode('/', $network['network']);
            $prefix = (int)$prefix;

            if (!filter_var($network_address, FILTER_VALIDATE_IP)) {
                \Log::error("Invalid IP in network configuration: " . $network['network']);
                continue;
            }

            $network_long = ip2long($network_address);
            $network_base = $network_long & ((-1 << (32 - $prefix)));
            $broadcast_address = $network_base | ((1 << (32 - $prefix)) - 1);

            // Generate IPs within the network range
            for ($i = 1; $i < ($broadcast_address - $network_base); $i++) {
                $ip = long2ip($network_base + $i);
                if ($ip !== false) {
                    $ip_list[] = $ip;
                }
            }
        }

        return $ip_list;
    }

    public function getAllNetworksWithOccupancy(): array
    {
        if (!$this->allNetworksLoaded) {
            $this->loadAllNetworks();
        }

        $hostService = new HostService($this->ctx);
        $networks_occu = [];

        foreach ($this->networks as $network) {
            $hosts_list = $hostService->getHostsByNetworkId($network['id']);
            $used_ips = array_column($hosts_list, 'ip');

            // Calcular el total de hosts disponibles
            [$network_address, $cidr] = explode('/', $network['network']);
            $subnet_mask = 32 - (int)$cidr;
            $total_hosts = pow(2, $subnet_mask) - 2;

            $used_count = count($used_ips);
            $occupancy_percentage = ($used_count / $total_hosts) * 100;

            $network['occupancy'] = round($occupancy_percentage, 2);
            $networks_occu[] = $network;
        }

        return $networks_occu;
    }
}
