<?php
/**
  *
  * @author diego/@/envigo.net
  * @copyright Copyright CC BY-NC-ND 4.0 @ 2020 - 2025 Diego Garcia (diego/@/envigo.net)
*/

namespace App\Models;

class CmdStatsModel
{
    private \AppContext $ctx;
    private \DBManager $db;

    public function __construct(\AppContext $ctx)
    {
        $this->ctx = $ctx;
        $this->db = $ctx->get('DBManager');
    }

    /**
     * Inserts statistical data into the database.
     *
     * @param array<string, mixed> $data The data to insert into the stats table.
     * @throws \RuntimeException If the insertion fails.
     * @return void
     */
    public function insertStats(array $data)
    {
        try {
            $this->db->insert('stats', $data);
        } catch (\Exception $e) {
            throw new \RuntimeException("Error inserting stats: " . $e->getMessage(), 0, $e);
        }
    }
}
