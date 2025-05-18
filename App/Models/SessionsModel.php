<?php
/**
 *
 * @author diego/@/envigo.net
 * @copyright Copyright CC BY-NC-ND 4.0 @ 2020 - 2025 Diego Garcia (diego/@/envigo.net)
 *
*/

namespace App\Models;

use App\Core\DBManager;

class SessionsModel
{
    private DBManager $db;

    public function __construct(DBManager $db)
    {
        $this->db = $db;
    }

    public function create($data): bool
    {
        $this->db->insert('sessions', $data);

        return true;
    }

    public function update(int $userId, array $userData): bool
    {
        return $this->db->update('sessions', $userData, 'user_id = :uid', ['uid' => $userId]);
    }

    public function clearSession(array $clear_data): bool
    {
        $condition = '';
        $params = [];

        if (isset($clear_data['uid']) && is_numeric($clear_data['uid'])) {
            $params['user_id'] = $clear_data['uid'];
            $condition .= 'user_id = :user_id';
        }
        if (isset($clear_data['sid']) && is_numeric($clear_data['sid'])) {
            $params['sid'] = $clear_data['sid'];
            if (!empty($condition)) {
                $condition .= ' AND ';
            }
            $condition .= 'sid = :sid';
        }
        if (empty($params)) {
            return false;
        }

        return $this->db->delete('sessions', $condition, $params);
    }

    public function sidExists(int $uid, string $sid): bool
    {
        $query = "SELECT user_id FROM sessions WHERE user_id = :uid AND sid = :sid";

        $result = $this->db->qfetch($query, ['uid' => $uid, 'sid' => $sid]);

        return !empty($result);
    }

    public function commit(): void
    {
        $this->db->commit();
    }
}

