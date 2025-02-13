<?php

/**
 *
 * @author diego/@/envigo.net
 * @package
 * @subpackage
 * @copyright Copyright CC BY-NC-ND 4.0 @ 2020 - 2025 Diego Garcia (diego/@/envigo.net)
 */

namespace App\Models;

class CmdAnsibleReportModel
{
    private \AppContext $ctx;

    public function __construct(\AppContext $ctx)
    {
        $this->ctx = $ctx;
    }

    public function getAnsibleReport(int $target_id)
    {
        $db = $this->ctx->get('DBManager');
        $query = "SELECT * FROM reports WHERE id = :id AND type = :type";
        $params = [
            'id' => $target_id,
            'type' => $report_type,
        ];
        return $db->fetch($query, $params);
    }

    public function delete($target_id)
    {
        $db = $this->ctx->get('DBManager');
        return $db->delete('reports', ['id' => $target_id]);
    }
}
