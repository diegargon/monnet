<?php
/**
 *
 * @author diego/@/envigo.net
 * @copyright Copyright CC BY-NC-ND 4.0 @ 2020 - 2025 Diego Garcia (diego/@/envigo.net)
 */

namespace App\Models;

use App\Core\DBManager;

class PrefsModel
{
    private DBManager $db;

    public function __construct(DBManager $db)
    {
        $this->db = $db;
    }

    /**
     *
     * @param int $userId
     * @return array<string, mixed>
     */
    public function loadPrefs(int $userId): array
    {
        $prefs = [];
        $sql = 'SELECT * FROM prefs WHERE uid = :uid';
        $results = $this->db->qfetchAll($sql, [':uid' => $userId]);
        foreach ($results as $pref) {
            if (!empty($pref['pref_name'])) {
                $prefs[$pref['pref_name']] = $pref['pref_value'];
            }
        }
        return $prefs;
    }

    /**
     *
     * @param int $userId
     * @param string $key
     * @return string|false
     */
    public function getPref(int $userId, string $key): string|false
    {
        $sql = 'SELECT pref_value FROM prefs WHERE uid = :uid AND pref_name = :key LIMIT 1';
        $result = $this->db->qfetch($sql, [':uid' => $userId, ':key' => $key]);
        return $result ? $result['pref_value'] : false;
    }

    /**
     *
     * @param int $userId
     * @param string $key
     * @param mixed $value
     * @return void
     */
    public function setPref(int $userId, string $key, mixed $value): void
    {
        $sql = 'SELECT pref_value FROM prefs WHERE uid = :uid AND pref_name = :key LIMIT 1';
        $result = $this->db->qfetch($sql, [':uid' => $userId, ':key' => $key]);
        if ($result) {
            if ($result['pref_value'] !== $value) {
                $this->db->update('prefs', ['pref_value' => $value], 'uid = :uid AND pref_name = :key', [':uid' => $userId, ':key' => $key]);
            }
        } else {
            $this->db->insert('prefs', [
                'uid' => $userId,
                'pref_name' => $key,
                'pref_value' => $value,
            ]);
        }
    }

    public function getUserSelNetworks(int $uid)
    {
        $query = "SELECT * FROM prefs WHERE `pref_name` LIKE 'network_select_%' AND uid = :uid";

        return $this->db->qfetchAll($query, [':uid' => $uid]);
    }
}
