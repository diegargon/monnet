<?php

/**
 *
 * @author diego/@/envigo.net
 * @package
 * @subpackage
 * @copyright Copyright CC BY-NC-ND 4.0 @ 2020 - 2025 Diego Garcia (diego/@/envigo.net)
 */

namespace App\Models;

class CmdNetworkModel
{
    private \AppContext $ctx;

    public function __construct(\AppContext $ctx)
    {
        $this->ctx = $ctx;
    }
    public function removeNetwork($target_id)
    {
        $db = $this-ctx->get('Database');
        return $db->delete('networks', ['id' => $target_id]);
    }

    public function updateNetwork($target_id, $network_data)
    {
        $db = $this-ctx->get('Database');
        return $db->update('networks', $network_data, ['id' => $target_id]);
    }

    public function addNetwork($network_data)
    {
        $db = $this - ctx->get('Database');
        return $db->insert('networks', $network_data);
    }
}
