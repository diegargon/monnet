<?php

/**
 *
 * @author diego/@/envigo.net
 * @package
 * @subpackage
 * @copyright Copyright CC BY-NC-ND 4.0 @ 2020 - 2025 Diego Garcia (diego/@/envigo.net)
 */

namespace App\Services;

class LogService {
    private \AppContext $ctx;

     public function __construct(\AppContext $ctx)
     {
         $this->ctx = $ctx;
     }

    /**
     * Obtiene los logs de un host.
     *
     * @param int $host_id El ID del host.
     * @return array Los logs del host.
     */
    public function getHostLogs($host_id) {
        $db = $this->ctx->get('DBManager');
        $query = "SELECT * FROM host_logs WHERE host_id = :host_id ORDER BY date DESC LIMIT 100";
        $params = ['host_id' => $host_id];

        return $db->fetchAll($query, $params);
    }
}
