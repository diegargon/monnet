<?php

                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                         
/**
 *
 *  @author diego/@/envigo.net
 *  @package
 *  @subpackage
 *  @copyright Copyright CC BY-NC-ND 4.0 @ 2020 - 2024 Diego Garcia (diego/@/envigo.net)
 */
!defined('IN_WEB') ? exit : true;

Class Networks {

    private array $networks = [];
    private array $networks_disabled = [];
    private AppCtx $ctx;

    public function __construct(AppCtx $ctx) {
        $this->ctx = $ctx;
        $this->loadNetworks($ctx->getAppDb());
    }

    public function getNetworks() {
        return $this->networks;
    }

    //replace get_network_id
    public function getIpNetwork(string $ip) {
        $ip_long = ip2long($ip);

        foreach ($this->networks as $network) {
            list($network_ip, $cidr) = explode('/', $network['network']);
            $network_ip_long = ip2long($network_ip);
            $subnet_mask = -1 << (32 - $cidr);
            $network_ip_long &= $subnet_mask;

            if (($ip_long & $subnet_mask) == $network_ip_long) {
                return $network['id'];
            }
        }

        return false;
    }

    //replace build_iplist
    function buildIpList() {

        $ip_list = [];

        foreach ($this->networks as $net) {
            if (empty($net['network']) || Filters::varNetwork($net['network']) === false) {
                Log::err("Invalid network detected " . $net['network']);
                continue;
            }
            //We will use 0.0.0.0 to allow create a INTERNET network category for add external host.
            if (str_starts_with($net['network'], "0")) {
                continue;
            }
            Log::debug("Ping networks " . array2string($net));
            $parts = explode('/', $net['network']);
            $network = $parts[0];
            $prefix = $parts[1];
            $count = pow(2, (32 - $prefix));

            // Obtener la dirección de red
            $network_long = ip2long($network);
            $network_address = long2ip($network_long & ((-1 << (32 - $prefix))));
            $network_long = ip2long($network_address);
            $broadcast_address = long2ip($network_long | ((1 << (32 - $prefix)) - 1));

            //echo "->" . $network_address . "\n";
            //echo "->" . $broadcast_address . "\n";
            //echo "->" . $network_long . "\n";
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

    private function loadNetworks(Database $db) {
        $query = $db->selectAll('networks',);
        $networks = $db->fetchAll($query);
        if (valid_array($networks)) {
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
                } else {
                    $this->networks_disabled[$id] = $fnet;
                }
            }
        }
    }
}
