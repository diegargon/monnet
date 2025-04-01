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
    private \DBManager $db;

    public function __construct(\AppContext $ctx)
    {
        $this->ctx = $ctx;
        $this->db = $this->ctx->get('DBManager');
    }

    /**
     *
     * @return array<string, mixed>
     */
    public function getAll(): array
    {
        $query = "SELECT * FROM hosts";
        $results = $this->db->qfetchAll($query);

        if (is_bool($results)) {
            return [];
        }

        return $results;
    }

    /**
     * Actualiza los datos de un host.
     *
     * @param int   $id   ID del host a actualizar.
     * @param array $data Datos a actualizar.
     *
     * @return bool
     */
    public function update(int $id, array $data): bool
    {
        return $this->db->update('hosts', $data, 'id = :id', ['id' => $id]);
    }
}
