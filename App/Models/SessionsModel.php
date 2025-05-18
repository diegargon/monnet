<?php
/**
 *
 * @author diego/@/envigo.net
 * @copyright Copyright CC BY-NC-ND 4.0 @ 2020 - 2025 Diego Garcia (diego/@/envigo.net)
 *
 * Tabla: sessions
 * +-------------+--------------+------+-----+---------+-------------------+
 * | Columna     | Tipo         | Nulo | Key | Extra   | Default           |
 * +-------------+--------------+------+-----+---------+-------------------+
 * | id          | int          | NO   | PRI | auto    | None              |
 * | user_id     | int          | NO   | MUL |         | None              |
 * | sid         | varchar(64)  | NO   | MUL |         | None              |
 * | ip_address  | varchar(45)  | YES  |     |         | NULL              |
 * | user_agent  | varchar(255) | YES  |     |         | NULL              |
 * | created     | datetime     | YES  |     |         | NULL              |
 * | expire      | datetime     | YES  |     |         | NULL              |
 * | last_active | datetime     | YES  |     |         | NULL              |
 * +-------------+--------------+------+-----+---------+-------------------+
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
        // Asume que la tabla tiene UNIQUE KEY (user_id, sid)
        return $this->db->upsert('sessions', $data, ['user_id', 'sid']);
    }

    /**
     * Actualiza una sesión por user_id y opcionalmente sid.
     * Si $sid es null, actualiza todas las sesiones del usuario.
     */
    public function update(int $userId, array $userData, ?string $sid = null): bool
    {
        $condition = 'user_id = :uid';
        $params = [':uid' => $userId];
        if ($sid !== null) {
            $condition .= ' AND sid = :sid';
            $params[':sid'] = $sid;
        }
        return $this->db->update('sessions', $userData, $condition, $params);
    }

    public function clearSession(array $clear_data): bool
    {
        $condition = '';
        $params = [];

        if (isset($clear_data['uid']) && is_numeric($clear_data['uid'])) {
            $params[':user_id'] = $clear_data['uid'];
            $condition .= 'user_id = :user_id';
        }
        if (isset($clear_data['sid']) && is_numeric($clear_data['sid'])) {
            $params[':sid'] = $clear_data['sid'];
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
        $result = $this->db->qfetch($query, [':uid' => $uid, ':sid' => $sid]);

        return !empty($result);
    }

    public function commit(): void
    {
        $this->db->commit();
    }
}

