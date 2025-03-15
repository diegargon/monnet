<?php

/**
 *
 * @author diego/@/envigo.net
 * @package
 * @subpackage
 * @copyright Copyright CC BY-NC-ND 4.0 @ 2020 - 2025 Diego Garcia (diego/@/envigo.net)
 */

namespace App\Models;

class EncryptModel
{
    private \DBManager $db;

    public function __construct(\AppContext $ctx)
    {
        $this->db = $ctx->get('DBMananger');
    }

    /**
     *
     * @param int $hid
     * @param string $vkey
     * @param string $encryptedData
     * @return bool
     */
    public function saveEncryptedData(int $hid, string $vkey, string $encryptedData): bool
    {
        $values = [
          'hid' => $hid,
          'vkey' => $vkey,
          'vvalue' => $encryptedData
        ];
        return $this->db->insert('encrypted', $values);
    }

    /**
     *
     * @param int $id
     * @return array<string, mixed>|null
     */
    public function getEncryptedDataById(int $id): ?array
    {
        $sql = "SELECT vvalue FROM encrypted WHERE id = :id";
        $params = ['id' => $id];

        $result = $this->db->qfetch($sql, $params);

        return $result;
    }

    /**
     *
     * @param int $hid
     * @return array<string, mixed>|null
     */
    public function getEncryptedDataByHostId(int $hid): ?array
    {
        $sql = "SELECT vvalue FROM encrypted WHERE hid = :hid";
        $params = ['hid' => $hid];

        $result = $this->db->qfetch($sql, $params);

        return $result;
    }
}

