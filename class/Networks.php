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
            if (empty($net['network']) || Filters::varNetwork($net['network']) === false) {
                Log::err("Invalid network detected " . $net['network']);
                continue;
            }
            /*
             * Jump 0.0.0.0 (we use for add internet host and networks without scan field
             */
            if (str_starts_with($net['network'], "0") || $net['scan'] !== 1) {
                continue;
            }
            Log::debug("Ping networks " . array2string($net));
            $parts = explode('/', $net['network']);
            $network = $parts[0];
            $prefix = (int) $parts[1];
            $count = pow(2, (32 - $prefix));

            // Obtener la dirección de red
            $network_long = ip2long($network);
            $network_address = long2ip($network_long & ((-1 << (32 - $prefix))));
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
        $query = $db->selectAll('networks',);
        $networks = $db->fetchAll($query);
        if (valid_array($networks)) {
            /**
             * prevent getNetworkByIP return the first match on a big cidr like /0
             */
            usort($networks, function ($a, $b) {
                $cidrA = (int) explode('/', $a['network'])[1];
                $cidrB = (int) explode('/', $b['network'])[1];
                return $cidrB <=> $cidrA; // Orden descendente
            });

            foreach ($networks as $net) {
                $id = (int) $net['id'];
                $fnet = [
                    'id' => $id,
                    'network' => $net['network'],
                    'name' => $net['name'],
                    'vlan' => (int) $net['vlan'],
                    'scan' => (int) $net['scan'],
                    'disable' => (int) $net['disable'],
                ];
                if ($fnet['disable'] === 0) {
                    $this->networks[$id] = $fnet;
                }
                //else {
                //    $this->networks_disabled[$id] = $fnet;
                //}
            }
        }
    }
}
