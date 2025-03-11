<?php

/**
 *
 * @author diego/@/envigo.net
 * @package
 * @subpackage
 * @copyright Copyright CC BY-NC-ND 4.0 @ 2020 - 2025 Diego Garcia (diego/@/envigo.net)
 */

namespace App\Models;

class CmdHostNotesModel
{
    private \AppContext $ctx;
    private \DBManager $db;

    public function __construct(\AppContext $ctx)
    {
        $this->ctx = $ctx;
        $this->db = $ctx->get('DBManager');
    }

    /**
     * Actualiza un host by id.
     *
     * @param int $target_id El ID del host.
     * @param array<string, string|int> $data Los datos a actualizar.
     * @return bool True si se actualizó correctamente, False en caso contrario.
     */
    public function updateByID(int $target_id, array $data): bool
    {
        return $this->db->update('notes', $data, 'id = :id', ['id' => $target_id]);
    }

    public function getNotes(int $target_id): string
    {
        $condition = 'host_id = :target_id';
        $notes = $this->db->selectOne('notes', ['*'], $condition, ['target_id' => $target_id]);
        if (!empty($notes['content'])) {
            return $notes['content'];
        }
        return '';
    }
}
