<?php

/**
 *
 * @author diego/@/envigo.net
 * @package
 * @subpackage
 * @copyright Copyright CC BY-NC-ND 4.0 @ 2020 - 2024 Diego Garcia (diego/@/envigo.net)
 */
!defined('IN_WEB') ? exit : true;

class Hosts
{
    /** @var int */
    public int $totals = 0;
    /** @var int */
    public int $total_on = 0;
    /** @var int */
    public int $total_off = 0;
    /** @var int */
    public int $highlight_total = 0;
    /** @var int */
    public int $ansible_hosts = 0;
    /** @var int */
    public int $ansible_hosts_off = 0;
    /** @var int */
    public int $ansible_hosts_fail = 0;

    /**
     *
     * @var Database $db
     */
    private Database $db;

    /**
     *  List of field we save in json field misc.
     *  @var array<string> $misc_keys
     */
    private $misc_keys = [
            'mac_vendor',
            'manufacture',
            'system_type',
            'os',
            'owner',
            'access_type',
            'access_link',
            'timeout',
            'disable_alarms',
            'disable_email_alarms',
        ];
    /**
     * host[$id] = ['key' => 'value']
     * json field is decode and encode on load/update ($misc_keys)
     * @var array<int, array<string, mixed>> $hosts
     */
    private $hosts = [];

    /**
     * @var array<string> $lng
     */
    private array $lng = [];

    /**
     *
     * @var AppContext $ctx
     */
    private AppContext $ctx;

    /**
     *
     * @var array<int, int> $host_cat_track
     */
    private array $host_cat_track = [];

    public function __construct(AppContext $ctx)
    {
        $this->ctx = $ctx;
        $this->db = $ctx->get('Mysql');
        $this->lng = $ctx->get('lng');
        $this->setHostsDb();
    }

    /**
     *
     * @param array<string, mixed> $host
     * @return bool
     */
    public function addHost(array $host): bool
    {
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

    /**
     *
     * @return array<int, array<string, mixed>>
     */
    public function getknownEnabled(): array
    {
        /**
         * @var array<int, array<string, mixed>> $hosts
         */
        $hosts = [];

        foreach ($this->hosts as $host) {
            if (empty($host['disable'])) {
                $hosts[] = $host;
            }
        }

        return $hosts;
    }

    /**
     *
     * @param int $highligth
     * @return array<int, array<string, mixed>>
     */
    public function getHighlight(int $highligth = 1): array
    {
        $hosts = $this->getknownEnabled();
        foreach ($hosts as $khost => $vhost) {
            if ($vhost['highlight'] != $highligth) {
                unset($hosts[$khost]);
            }
        }

        return $hosts;
    }

    /**
     *
     * @return array<int, array<string, mixed>>
     */
    public function getAll(): array
    {
        return $this->hosts;
    }

    /**
     *
     * @param int $id
     * @param array<string, mixed> $values
     * @return void
     */
    public function update(int $id, array $values): void
    {
        /**
         * @var array<int, array<string, mixed>> $fvalues
         */
        $fvalues = []; //filter
        /** @var array<int, array<string, mixed>> $misc_container misc json field key/values */
        $misc_container = [];

        foreach ($values as $kvalue => $vvalue) {
            if (!empty($kvalue) && isset($vvalue)) {
                //TODO warning signs
                //Log change
                if (
                        empty($this->hosts[$id]['disable_alarms']) &&
                        ($kvalue === 'mac' || $kvalue === 'mac_vendor' || $kvalue === 'hostname') &&
                        ($this->hosts[$id][$kvalue] != $vvalue)
                ) {
                    $loghostmsg = $this->lng['L_HOST_MSG_DIFF'] . ' ( '
                            . $this->hosts[$id]['display_name'] . ' )([' . $kvalue . '])'
                            . $this->hosts[$id][$kvalue] . '->' . $vvalue;
                    Log::logHost('LOG_ALERT', $id, $loghostmsg);
                    $this->hosts[$id]['alert'] = 1;
                    $alert_msg = '';
                    if ($kvalue === 'mac') :
                        $alert_msg = 'Mac ' . $this->lng['L_HAS_CHANGED'];
                    endif;
                    if ($kvalue === 'mac_vendor') :
                        $alert_msg = 'Mac vendor ' . $this->lng['L_HAS_CHANGED'];
                    endif;
                    if ($kvalue === 'hostname') :
                        $alert_msg = 'Hostname ' . $this->lng['L_HAS_CHANGED'];
                    endif;
                    $this->hosts[$id]['alert_msg'] = $alert_msg;
                }
                //Log category change
                if ($kvalue == 'category' && $vvalue != $this->hosts[$id]['category']) {
                    Log::logHost('LOG_NOTICE', $id, 'Host ' . $this->hosts[$id]['display_name']
                        . ' change category ' . $this->hosts[$id]['category'] . '->' . $vvalue);
                    $this->host_cat_track[$this->hosts[$id]['category']]--;
                    $this->host_cat_track[$vvalue]++;
                }

                //Add the new values to the current array.
                $this->hosts[$id][$kvalue] = $vvalue;
                //misc is json field deal with it
                if (in_array($kvalue, $this->misc_keys)) {
                    $misc_container[$kvalue] = $vvalue;
                } else {
                    $fvalues[$kvalue] = $vvalue;
                }
            }
        }

        /*
         * To update the misc field (json) we add to the array the other fields
         * encode it and update all.
         */
        if (valid_array($misc_container)) {
            $host = $this->hosts[$id];
            foreach ($host as $h_key => $h_value) {
                if (
                    in_array($h_key, $this->misc_keys) &&
                    !array_key_exists($h_key, $misc_container)
                ) {
                    $misc_container[$h_key] = $h_value;
                }
            }

            $fvalues['misc'] = json_encode($misc_container);
        }
        if (valid_array($fvalues)) {
            $this->db->update('hosts', $fvalues, ['id' => ['value' => $id]], 'LIMIT 1');
        }
    }

    /**
     *
     * @param array<string, mixed> $host
     * @return void
     */
    public function insert(array $host): void
    {
        $misc_container = [];

        if (!isset($host['hostname'])) {
            $hostname = $this->getHostname($host['ip']);
            if ($hostname) {
                $host['hostname'] = $hostname;
            }
        }
        //remove keys that we store in misc json field
        foreach ($host as $h_key => $h_value) {
            if (
                in_array($h_key, $this->misc_keys) &&
                !in_array($h_key, $misc_container)
            ) {
                $misc_container[$h_key] = $h_value;
                unset($host[$h_key]);
            }
        }
        if (count($misc_container) > 0) {
            $host['misc'] = json_encode($misc_container);
        }
        $this->db->insert('hosts', $host);

        $host_id = $this->db->insertID();
        $hostlog = $this->getDisplayName($host);
        if (!empty($host['mac_vendor']) && $host['mac_vendor'] !== '-') {
            $hostlog .= ' [' . $host['mac_vendor'] . ']';
        } elseif (!empty($host['mac'])) {
            $hostlog .= ' [' . $host['mac'] . ']';
        }
        $host['id'] = $host_id;
        $host['display_name'] = $this->getDisplayName($host);
        $this->hosts[$host_id] = $host;
        $network_name = $this->ctx->get('Networks')->getNetworkNameByID($host_id);
        Log::logHost('LOG_NOTICE', $host_id, 'Found new host: '
            . $host['display_name'] . ' on network ' . $network_name);
    }

    /**
     *
     * @param int $hid
     * @return void
     */
    public function remove(int $hid): void
    {
        Log::notice('Deleted host: ' . $this->hosts[$hid]['display_name']);
        $this->db->delete('hosts', ['id' => $hid], 'LIMIT 1');
        $this->db->delete('notes', ['host_id' => $hid], 'LIMIT 1');
        $this->db->delete('stats', ['host_id' => $hid], 'LIMIT 1');
        $this->db->delete('hosts_logs', ['host_id' => $hid], 'LIMIT 1');
        unset($this->hosts[$hid]);
    }

    /**
     *
     * @param int $id
     * @return array<string, mixed>
     */
    public function getHostById(int $id): ?array
    {

        if (empty($this->hosts[$id])) {
            return null;
        }
        $host = $this->hosts[$id];
        $result = $this->db->select('notes', '*', ['id' => $host['notes_id']], 'LIMIT 1');
        $notes = $this->db->fetch($result);
        $host['notes'] = $notes['content'];
        $host['notes_date'] = $notes['update'];

        return $host;
    }

    /**
     *
     * @param string $ip
     * @return array<string>
     */
    public function getHostByIp(string $ip): ?array
    {
        foreach ($this->hosts as $host) {
            if ($host['ip'] == trim($ip)) {
                return $host;
            }
        }

        return null;
    }

    /**
     *
     * @param int $cat_id
     * @return array<int, array<string, mixed>>
     */
    public function getHostsByCat(int $cat_id): array
    {
        /**
         * @var array<int, array<string, mixed>> $hosts_by_cat
         */
        $hosts_by_cat = [];

        foreach ($this->hosts as $host) {
            if ($host['category'] == $cat_id) {
                $hosts_by_cat[] = $host;
            }
        }

        return $hosts_by_cat;
    }

    /**
     *
     * @param int $id
     * @return bool
     */
    public function catHaveHosts(int $id): bool
    {
        if (isset($this->host_cat_track[$id]) && $this->host_cat_track[$id] > 0) {
            return true;
        }

        return false;
    }

    /**
     *
     * @param string $ip
     * @return string|false
     */
    public function getHostname(string $ip): string|false
    {
        $hostname = gethostbyaddr($ip);
        if ($hostname == $ip) {
            return false;
        }
        return $hostname;
    }

    /**
     *
     * @param string $domain
     * @return string
     */
    public function getHostnameIP(string $domain): string
    {
        return gethostbyname($domain);
    }

    /**
     *
     * @param string $username
     * @param int $id
     * @return bool
     */
    public function clearHostAlarms(string $username, int $id): bool
    {
        $values = [
            'alert' => 0,
            'warn' => 0,
            'warn_port' => 0,
            'ansible_fail' => 0,
            ];
        $this->update($id, $values);
        Log::logHost('LOG_NOTICE', $id, $this->lng['L_CLEAR_ALARMS_BY'] . ': ' . $username);

        return true;
    }

    /**
     *
     * @param int $id
     * @param bool $value
     * @return bool
     */
    public function setAlarms(int $id, bool $value): bool
    {
        $this->update($id, ['disable_alarms' => $value]);

        return true;
    }

    /**
     *
     * @param int $id
     * @param string $msg
     * @return void
     */
    public function setAlarmOn(int $id, string $msg): void
    {
        $this->hosts['id']['alert'] = 1;
        $this->hosts['id']['alert_msg'] = $msg;
        $this->update($id, ['alert' => 1, 'alert_msg' => $msg]);
    }

    /**
     *
     * @param int $id
     * @param string $msg
     * @return void
     */
    public function setAnsibleAlarm(int $id, string $msg): void
    {

        $this->hosts['id']['alert'] = 1;
        $this->hosts['id']['alert_msg'] = 'Ansible Alert';
        $this->hosts['id']['ansible_fail'] = 1;
        $this->update($id, ['alert' => 1, 'alert_msg' => $msg, 'ansible_fail' => 1]);
        $this->db("INSERT INTO `ansible_msg` ('host_id', 'msg') VALUES ($id, $msg)");
    }

    /**
     *
     * @param int $id
     * @param bool $value
     * @return bool
     */
    public function setEmailAlarms(int $id, bool $value): bool
    {
        $this->update($id, ['disable_email_alarms' => $value]);

        return true;
    }

    /**
     *
     * @param array<string, mixed> $host
     * @return string
     */
    private function getDisplayName(array $host): string
    {
        if (!empty($host['title'])) {
            return $host['title'];
        } elseif (!empty($host['hostname'])) {
            return ucfirst(explode('.', $host['hostname'])[0]);
        }

        return $host['ip'];
    }

    /**
     *
     * @return bool
     */
    private function setHostsDb(): bool
    {
        $ncfg = $this->ctx->get('Config');
        $networks = $this->ctx->get('Networks');
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
            /* Misc field  fields that we keep in JSON format */
            if (!empty($this->hosts[$id]['misc'])) {
                $misc_values = json_decode($this->hosts[$id]['misc'], true);
                foreach ($misc_values as $key => $value) {
                    if (in_array($key, $this->misc_keys, true)) { //Prevent unused/old keys
                        if (is_numeric($value)) {
                            $this->hosts[$id][$key] = (int) $value;
                        } elseif (is_bool($value)) {
                            $this->hosts[$id][$key] = (bool) $value;
                        } else {
                            $this->hosts[$id][$key] = $value;
                        }
                    }
                }
            }

        if ($ncfg->get('ansible')) {
                if ($host['ansible_enabled']) {
                    $this->ansible_hosts++;
                    if (!$host['online']) :
                        $this->ansible_hosts_off++;
                    endif;
                }
                if ($host['ansible_fail']) {
                    $this->ansible_hosts_fail++;
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
