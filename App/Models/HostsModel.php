<?php

/**
 *
 * @author diego/@/envigo.net
 * @package
 * @subpackage
 * @copyright Copyright CC BY-NC-ND 4.0 @ 2020 - 2025 Diego Garcia (diego/@/envigo.net)
 */

namespace App\Models;

class HostsModel
{
    private \AppContext $ctx;

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
    /** @var int */
    public int $agents = 0;
    /** @var int */
    public int $agents_off = 0;
    /** @var int */
    public int $agents_missing_pings = 0;
    /**  @var int */
    public int $hypervisor_rols = 0;
    /**  @var int */
    public int $alerts = 0;
    /**  @var int */
    public int $warns = 0;


    public function __construct(\AppContext $ctx)
    {
        $this->ctx = $ctx;
    }

    public function getAll() {
        $db = $this->ctx->get('DBManager');

        $query = "SELECT * FROM hosts";

        $results = $db->qfetchAll($query);

        if (is_bool($results)) {
            return [];
        }
        $this->totals = count($results);

        return $results;
    }
}
