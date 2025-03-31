<?php
/**
 *
 * @author diego/@/envigo.net
 * @package
 * @subpackage
 * @copyright Copyright CC BY-NC-ND 4.0 @ 2020 - 2025 Diego Garcia (diego/@/envigo.net)
 */

namespace App\Models;

class CmdStats
{
    private \AppContext $ctx;
    private \DBManager $db;

    public function __construct(\AppContext $ctx)
    {
        $this->ctx = $ctx;
        $this->db = $ctx->get('DBManager');
    }

    public function insertStats(array $data)
    {
        $this->db->insert('stats', $data);
    }
}
