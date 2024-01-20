<?php

/**
 *
 *  @author diego/@/envigo.net
 *  @package
 *  @subpackage
 *  @copyright Copyright @ 2020 - 2024 Diego Garcia (diego/@/envigo.net)
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

    private Database $db;
    private $hosts = [];

    public function __construct($db) {
        $this->db = $db;
        $this->getHostsDb();
    }

    function getEnabled() {
        $hosts = [];

        foreach ($this->hosts as $host) {
            if (empty($host['disable'])) {
                $hosts[] = $host;
            }
        }

        return $hosts;
    }

    function getHighlight(int $highligth = 1) {
        $hosts = $this->getEnabled();
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
        $fvalues = []; //filtered

        foreach ($values as $kvalue => $vvalue) {
            $kvalue = $this->db->escape($kvalue);
            $vvalue = $this->db->escape($vvalue);
            if (!empty($kvalue) && isset($vvalue)) {
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
    }

    function remove(int $hid) {
        $this->db->delete('hosts', ['id' => $hid], 'LIMIT 1');
        $this->db->delete('notes', ['host_id' => $hid], 'LIMIT 1');
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

    private function getHostsDb() {
        $query_hosts = 'SELECT * FROM hosts';
        $results = $this->db->query($query_hosts);
        if (!$results) {
            return false;
        }
        $_hosts = $this->db->fetchAll($results);

        foreach ($_hosts as $host) {
            $id = $host['id'];
            $this->hosts[$id] = $host;
            $this->hosts[$id]['disable'] = empty($host['disable']) ? 0 : 1;
            if (!empty($this->hosts[$id]['ports'])) {
                $this->hosts[$id]['ports'] = json_decode($host['ports'], true);
            }
            if (empty($host['note_id'])) {
                $this->db->insert('notes', ['host_id' => $host['id']]);
                $insert_id = $this->db->insertID();
                $this->db->update('hosts', ['notes_id' => $insert_id], ['id' => $host['id']]);
            }
        }
    }
}
