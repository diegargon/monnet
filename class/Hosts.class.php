<?php

/**
 *
 *  @author diego/@/envigo.net
 *  @package
 *  @subpackage
 *  @copyright Copyright CC BY-NC-ND 4.0 @ 2020 - 2024 Diego Garcia (diego/@/envigo.net)
 */
!defined('IN_WEB') ? exit : true;

/*
  ip : string 192.168.1.1
  name: string custom name
  mac: string mac
  mac_vendor: string
  disable : disable
  ports:
  n: number
  status: 0/1
  port_type: 1(tcp)/2(ucp)
  name: string, custom name
  icon: string, custom path icon.png
  latency: float


 */

Class Hosts {

    public int $totals = 0;
    public int $on = 0;
    public int $off = 0;
    public int $highlight_total = 0;
    private Database $db;
    private $hosts = [];
    private array $lng;
    private Log $log;

    public function __construct(Log $log, Database $db, array $lng) {
        $this->db = $db;
        $this->lng = $lng;
        $this->log = $log;
        $this->getHostsDb();
    }

    function getknownEnabled() {
        $hosts = [];

        foreach ($this->hosts as $host) {
            if (empty($host['disable'])) {
                $hosts[] = $host;
            }
        }

        return $hosts;
    }

    function getHighlight(int $highligth = 1) {
        $hosts = $this->getknownEnabled();
        foreach ($hosts as $khost => $vhost) {
            if ($vhost['highlight'] != $highligth) {
                unset($hosts[$khost]);
            }
        }

        return $hosts;
    }

    function getAll() {
        return $this->hosts;
    }

    //function setMac($id, $mac) {
    //    $this->hosts[$id]['mac'] = $mac;
    //}

    function update(int $id, array $values) {
        $fvalues = []; //filter

        foreach ($values as $kvalue => $vvalue) {
            if (!empty($kvalue) && isset($vvalue)) {
                //TODO warning sign
                if (
                        ($kvalue == 'mac' || $kvalue == 'mac_vendor' || $kvalue == 'hostname') &&
                        ($this->hosts[$id][$kvalue] != $vvalue)
                ) {
                    $loghostmsg = $this->lng['L_HOST_MSG_DIFF'] . ' ( ' . $this->hosts[$id]['display_name'] . ' )' . $this->hosts[$id][$kvalue] . '->' . $vvalue;
                    $this->log->logHost('LOG_WARNING', $id, $loghostmsg);
                }
                $this->hosts[$id][$kvalue] = $vvalue;
                $fvalues[$kvalue] = $vvalue;
            }
        }

        if (valid_array($fvalues)) {
            $this->db->update('hosts', $fvalues, ['id' => ['value' => $id]], 'LIMIT 1');
        }
    }

    function insert(array $host) {
        $this->db->insert('hosts', $host);
        $host_id = $this->db->insertID();
        $hostlog = $this->getDisplayName($host);
        !empty($host['mac']) ? $hostlog .= '[' . $host['mac'] . ']' : null;
        $this->hosts[$host_id] = $host;
        $this->log->logHost('LOG_NOTICE', $host_id, 'Found new host: ' . $hostlog);
    }

    function remove(int $hid) {
        $this->log->notice('Deleted host: ' . $this->hosts[$hid]['ip']);
        $this->db->delete('hosts', ['id' => $hid], 'LIMIT 1');
        $this->db->delete('notes', ['host_id' => $hid], 'LIMIT 1');
        $this->db->delete('stats', ['host_id' => $hid], 'LIMIT 1');
        $this->db->delete('hosts_logs', ['host_id' => $hid], 'LIMIT 1');
        unset($this->hosts[$hid]);
    }

    function getHostById(int $id) {

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

    function getHostByIp(string $ip) {
        foreach ($this->hosts as $host) {
            if ($host['ip'] == trim($ip)) {
                return $host;
            }
        }

        return false;
    }

    public function getHostsByCat(int $cat_id) {
        $hosts_by_cat = [];

        foreach ($this->hosts as $host) {

            if ($host['category'] == $cat_id) {
                $hosts_by_cat[] = $host;
            }
        }

        return valid_array($hosts_by_cat) ? $hosts_by_cat : false;
    }

    private function getDisplayName($host) {
        if (!empty($host['title'])) {
            return $host['title'];
        } else if (!empty($host['hostname'])) {
            return ucfirst(explode('.', $host['hostname'])[0]);
        }
        return $host['ip'];
    }

    private function getHostsDb() {
        $query_hosts = 'SELECT * FROM hosts';
        $results = $this->db->query($query_hosts);
        if (!$results) {
            return false;
        }
        $hosts = $this->db->fetchAll($results);
        $this->totals = count($hosts);

        foreach ($hosts as $host) {
            $id = $host['id'];
            $this->hosts[$id] = $host;
            $host['online'] == 1 ? ++$this->on : ++$this->off;
            $host['highlight'] ? $this->highlight_total++ : null;
            $this->hosts[$id]['display_name'] = $this->getDisplayName($host);

            $this->hosts[$id]['disable'] = empty($host['disable']) ? 0 : 1;
            if (!empty($this->hosts[$id]['ports'])) {
                $this->hosts[$id]['ports'] = json_decode($host['ports'], true);
            }
            if (empty($host['notes_id'])) {
                $this->db->insert('notes', ['host_id' => $host['id']]);
                $insert_id = $this->db->insertID();
                $this->db->update('hosts', ['notes_id' => $insert_id], ['id' => $host['id']]);
                $this->hosts[$host['id']]['notes_id'] = $insert_id;
            }
        }
    }
}
