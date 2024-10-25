<?php

/**
 *
 *  @author diego/@/envigo.net
 *  @package
 *  @subpackage
 *  @copyright Copyright CC BY-NC-ND 4.0 @ 2020 - 2024 Diego Garcia (diego/@/envigo.net)
 */
!defined('IN_WEB') ? exit : true;

Class Hosts {

    public int $totals = 0;
    public int $total_on = 0;
    public int $total_off = 0;
    public int $highlight_total = 0;
    private Database $db;
    private $hosts = [];
    private array $lng;
    private AppCtx $ctx;
    private array $host_cat_track = [];

    public function __construct(AppCtx $ctx) {
        $this->ctx = $ctx;
        $this->db = $ctx->getAppDb();
        $this->lng = $ctx->getAppLang();
        $this->setHostsDb();
    }

    public function addHost(array $host): bool {
        if (empty($host['ip']) && !empty($host['hostname'])) {
            if (!Filters::varDomain($host['hostname'])) {
                return false;
            }
            $host['ip'] = gethostbyname($host['hostname']);
            if (!$host['ip']) {
                return false;
            }
        }

        $this->insert($host);

        return true;
    }

    public function getknownEnabled(): array {
        $hosts = [];

        foreach ($this->hosts as $host) {
            if (empty($host['disable'])) {
                $hosts[] = $host;
            }
        }

        return $hosts;
    }

    public function getHighlight(int $highligth = 1): array {
        $hosts = $this->getknownEnabled();
        foreach ($hosts as $khost => $vhost) {
            if ($vhost['highlight'] != $highligth) {
                unset($hosts[$khost]);
            }
        }

        return $hosts;
    }

    public function getAll(): array {
        return $this->hosts;
    }

    //function setMac($id, $mac) {
    //    $this->hosts[$id]['mac'] = $mac;
    //}

    public function update(int $id, array $values): void {
        $fvalues = []; //filter
        $misc_container = [];
        $misc_keys = ['mac_vendor', 'manufacture', 'system_type', 'os', 'owner', 'timeout'];

        foreach ($values as $kvalue => $vvalue) {
            if (!empty($kvalue) && isset($vvalue)) {
                //TODO warning sign
                if (
                        ($kvalue == 'mac' || $kvalue == 'mac_vendor' || $kvalue == 'hostname') &&
                        ($this->hosts[$id][$kvalue] != $vvalue)
                ) {
                    $loghostmsg = $this->lng['L_HOST_MSG_DIFF'] . ' ( ' . $this->hosts[$id]['display_name'] . ' )([' . $kvalue . '])' . $this->hosts[$id][$kvalue] . '->' . $vvalue;
                    Log::logHost('LOG_WARNING', $id, $loghostmsg);
                }
                if ($kvalue == 'category' && $vvalue != $this->hosts[$id]['category']) {
                    Log::logHost('LOG_INFO', $id, 'Host ' . $this->hosts[$id]['display_name'] . ' change category ' . $this->hosts[$id]['category'] . '->' . $vvalue);
                    $this->host_cat_track[$this->hosts[$id]['category']]--;
                    $this->host_cat_track[$vvalue]++;
                }
                $this->hosts[$id][$kvalue] = $vvalue;
                if (in_array($kvalue, $misc_keys)) {
                    $misc_container[$kvalue] = $vvalue;
                } else {
                    $fvalues[$kvalue] = $vvalue;
                }
            }
        }
        if (valid_array($misc_container)) {
            $fvalues['misc'] = json_encode($misc_container);
        }
        if (valid_array($fvalues)) {
            $this->db->update('hosts', $fvalues, ['id' => ['value' => $id]], 'LIMIT 1');
        }
    }

    public function insert(array $host): void {
        if (!isset($host['hostname'])) {
            $hostname = $this->getHostname($host['ip']);
            if ($hostname) {
                $host['hostname'] = $hostname;
            }
        }

        $this->db->insert('hosts', $host);

        $host_id = $this->db->insertID();
        $hostlog = $this->getDisplayName($host);
        if (!empty($host['mac_vendor']) && $host['mac_vendor'] !== '-') {
            $hostlog .= ' [' . $host['mac_vendor'] . ']';
        } else if (!empty($host['mac'])) {
            $hostlog .= ' [' . $host['mac'] . ']';
        }
        $host['id'] = $host_id;
        $host['display_name'] = $this->getDisplayName($host);
        $this->hosts[$host_id] = $host;
        $network_name = $this->ctx->getAppNetworks()->getNetworkNameByID($host_id);
        Log::logHost('LOG_NOTICE', $host_id, 'Found new host: ' . $host['display_name'] . ' on network ' . $network_name);
    }

    public function remove(int $hid): void {
        Log::notice('Deleted host: ' . $this->hosts[$hid]['display_name']);
        $this->db->delete('hosts', ['id' => $hid], 'LIMIT 1');
        $this->db->delete('notes', ['host_id' => $hid], 'LIMIT 1');
        $this->db->delete('stats', ['host_id' => $hid], 'LIMIT 1');
        $this->db->delete('hosts_logs', ['host_id' => $hid], 'LIMIT 1');
        unset($this->hosts[$hid]);
    }

    public function getHostById(int $id): array|false {

        if (empty($this->hosts[$id])) {
            return false;
        }
        $host = $this->hosts[$id];
        $result = $this->db->select('notes', '*', ['id' => $host['notes_id']], 'LIMIT 1');
        $notes = $this->db->fetch($result);
        $host['notes'] = $notes['content'];
        $host['notes_date'] = $notes['update'];

        return $host;
    }

    public function getHostByIp(string $ip): array|false {
        foreach ($this->hosts as $host) {
            if ($host['ip'] == trim($ip)) {
                return $host;
            }
        }

        return false;
    }

    public function getHostsByCat(int $cat_id): array|false {
        $hosts_by_cat = [];

        foreach ($this->hosts as $host) {

            if ($host['category'] == $cat_id) {
                $hosts_by_cat[] = $host;
            }
        }

        return valid_array($hosts_by_cat) ? $hosts_by_cat : false;
    }

    public function catHaveHosts($id): bool {
        if (isset($this->host_cat_track[$id]) && $this->host_cat_track[$id] > 0) {
            return true;
        }

        return false;
    }

    private function getDisplayName($host): string {
        if (!empty($host['title'])) {
            return $host['title'];
        } else if (!empty($host['hostname'])) {
            return ucfirst(explode('.', $host['hostname'])[0]);
        }
        return $host['ip'];
    }

    public function getHostname(string $ip): string|false {
        $hostname = gethostbyaddr($ip);
        if ($hostname == $ip) {
            return false;
        }
        return $hostname;
    }

    public function getHostnameIP(string $domain): string {
        return gethostbyname($domain);
    }

    private function setHostsDb(): bool {
        $networks = $this->ctx->getAppNetworks();
        $query_hosts = 'SELECT * FROM hosts';
        $results = $this->db->query($query_hosts);
        if (!$results) {
            return false;
        }
        $hosts = $this->db->fetchAll($results);
        $this->totals = count($hosts);

        foreach ($hosts as $host) {
            $id = $host['id'];
            $net_id = $host['network'];
            $network = $networks->getNetworkByID($net_id);
            if ($network !== false) {
                $host['net_cidr'] = $network['network'];
                $host['network_name'] = $network['name'];
                $host['network_vlan'] = $network['vlan'];
            } else {
                Log::warning('Host network seems not exists: ' . "[H: $id][N: $net_id]");
            }
            $host['display_name'] = ucfirst($this->getDisplayName($host));

            $this->hosts[$id] = $host;
            $host['online'] == 1 ? ++$this->total_on : ++$this->total_off;
            $host['highlight'] ? $this->highlight_total++ : null;

            $this->hosts[$id]['disable'] = empty($host['disable']) ? 0 : 1;

            //Track host categories
            if (empty($this->host_cat_track[$host['category']])) {
                $this->host_cat_track[$host['category']] = 1;
            } else {
                $this->host_cat_track[$host['category']]++;
            }
            /* Port Field JSON TODO Need rethink */
            if (!empty($this->hosts[$id]['ports'])) {
                $this->hosts[$id]['ports'] = json_decode($host['ports'], true);
            }
            /* Misc field JSON misc fields that not need a db field */
            if (!empty($this->hosts[$id]['misc'])) {
                $misc_values = json_decode($this->hosts[$id]['misc'], true);
                foreach ($misc_values as $key => $value) {
                    $this->hosts[$id][$key] = $value;
                }
            }

            if (empty($host['notes_id'])) {
                $this->db->insert('notes', ['host_id' => $host['id']]);
                $insert_id = $this->db->insertID();
                $this->db->update('hosts', ['notes_id' => $insert_id], ['id' => $host['id']]);
                $this->hosts[$host['id']]['notes_id'] = $insert_id;
            }
        }

        return true;
    }
}
