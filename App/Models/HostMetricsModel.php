<?php

/**
 *
 * @author diego/@/envigo.net
 * @package
 * @subpackage
 * @copyright Copyright CC BY-NC-ND 4.0 @ 2020 - 2025 Diego Garcia (diego/@/envigo.net)
 */


namespace App\Models;

class HostMetricsModel
{
    private \AppContext $ctx;

    public function __construct(\AppContext $ctx)
    {
        $this->ctx = $ctx;
    }

    /**
     *
     * @param int $hid
     * @param int $metric_type
     * @return array<string, string|int>
     */
    public function getDbMetrics(int $hid, int $metric_type): array
    {
        $db = $this->ctx->get('DBManager');

        $query = 'SELECT date, value
            FROM stats
            WHERE host_id = :hid AND
            type = :mtype
            AND date >= NOW() - INTERVAL 1 DAY
            ORDER BY date DESC;';

        $params = ['hid' => $hid, 'mtype' => $metric_type];

        $result = $db->qfetchAll($query, $params);

        if (!$result) {
            return [];
        }

        return $result;
    }
}
