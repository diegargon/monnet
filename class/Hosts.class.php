<?php

/**
 *
 *  @author diego/@/envigo.net
 *  @package
 *  @subpackage
 *  @copyright Copyright @ 2020 - 2021 Diego Garcia (diego/@/envigo.net)
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

    public function getHighLight() {
        $hosts = [];

        foreach ($this->hosts as $host) {
            if (!empty($host['highlight']) && empty($host['disable'])) {
                $hosts[] = $host;
            }
        }
        return $hosts;
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

    function getAll() {
        return $this->hosts;
    }

    private function getHostsDb() {
        $query_hosts = 'SELECT * FROM hosts';
        $results = $this->db->query($query_hosts);
        if (!$results) {
            return false;
        }
        $_hosts = $this->db->fetchAll($results);

        foreach ($_hosts as $_host) {
            $hosts = $_host;
            $hosts['disable'] = empty($_host['disable']) ? 0 : 1;
            $hosts['ports'] = json_decode($_hosts['ports'], true);
        }
    }

}
