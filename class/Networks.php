<?php

/**
 *
 * @author diego/@/envigo.net
 * @package
 * @subpackage
 * @copyright Copyright CC BY-NC-ND 4.0 @ 2020 - 2024 Diego Garcia (diego/@/envigo.net)
 */
!defined('IN_WEB') ? exit : true;

class Networks
{
    /**
     * @var array<int, array<string, mixed>> $networks
     */
    private array $networks;

    //private array $networks_disabled;

    /**
     *
     * @var AppContext
     */
    private AppContext $ctx;

    public function __construct(AppContext $ctx)
    {
        $this->ctx = $ctx;
    }

    /**
     *
     * @return array<int, array<string, mixed>>
     */
    public function getNetworks(): array
    {
        if (!isset($this->networks)) {
            $this->loadNetworks();
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
        $networks = $this->getNetworks();

        if (isset($networks[$id])) {
            return $networks[$id];
        } else {
            return false;
        }
    }

    /**
     *
     * @param string $ip
     * @return int|false
     */
    public function getNetworkIDbyIP(string $ip): int|false
    {
        $ip_long = ip2long($ip);
        $networks = $this->getNetworks();

        foreach ($networks as $network) {
            list($network_ip, $cidr) = explode('/', $network['network']);
            $cidr = (int) $cidr;
            $network_ip_long = ip2long($network_ip);
            $subnet_mask = -1 << (32 - $cidr);
            $network_ip_long &= $subnet_mask;

            if (($ip_long & $subnet_mask) == $network_ip_long) {
                return $network['id'];
            }
        }

        return false;
    }

    /**
     *
     * @param array<string, mixed> $set
     * @return void
     */
    public function addNetwork(array $set): void
    {
        $db = $this->ctx->get('Mysql');
        $db->insert('networks', $set);
        $id = $db->insertID();
        $this->networks[$id]['id'] = $id;
        foreach ($set as $key => $value) :
            $this->networks[$id][$key] = $value;
        endforeach;
    }

    /**
     *
     * @param int $id
     * @param array<string|int,string> $set
     * @return void
     */
    public function updateNetwork(int $id, array $set): void
    {
        $db = $this->ctx->get('Mysql');
        $db->upsert('networks', $set, ['id' => $id]);
        foreach ($set as $key => $value) :
            $this->networks[$id][$key] = $value;
        endforeach;
    }

    /**
     *
     * @param int $id
     * @return bool
     */
    public function removeNetwork(int $id): bool
    {
        $db = $this->ctx->get('Mysql');
        $query = "DELETE FROM networks WHERE id=$id";
        $db->query($query);
        unset($this->networks[$id]);

        return true;
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

    /**
     *
     * @return void
     */
    private function loadNetworks(): void
    {
        $db = $this->ctx->get('Mysql');
        $query = $db->selectAll('networks');
        $networks = $db->fetchAll($query);
        if (valid_array($networks)) :
            /**
             * prevent getNetworkByIP return the first match on a big cidr like /0
             */
            usort($networks, function ($a, $b) {
                $cidrA = (int) explode('/', $a['network'])[1];
                $cidrB = (int) explode('/', $b['network'])[1];
                return $cidrB <=> $cidrA; // Orden descendente
            });

            foreach ($networks as $net) :
                $id = (int) $net['id'];
                $fnet = [
                    'id' => $id,
                    'network' => $net['network'],
                    'name' => $net['name'],
                    'vlan' => (int) $net['vlan'],
                    'scan' => (int) $net['scan'],
                    'weight' => (int) $net['weight'],
                    'only_online' => (int) $net['only_online'],
                    'disable' => (int) $net['disable'],
                ];

                #temp fix for upgrade
                $cfg = $this->ctx->get('cfg');
                if ($cfg['monnet_version'] >= 0.44) {
                    $fnet['pool'] = $net['pool'];
                }

                $this->networks[$id] = $fnet;
            endforeach;
        endif;
    }
}
