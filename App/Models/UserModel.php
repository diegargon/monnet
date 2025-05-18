<?php
/**
 *
 * @author diego/@/envigo.net
 * @copyright Copyright CC BY-NC-ND 4.0 @ 2020 - 2025 Diego Garcia (diego/@/envigo.net)
 *
*/

namespace App\Models;


use App\Core\DBManager;

class UserModel
{
    private DBManager $db;

    public function __construct(DBManager $db)
    {
        $this->db = $db;
    }

    public function getById(int $userId): ?array
    {
        $query = "SELECT * FROM users WHERE id = :id";
        return $this->db->qfetch($query, ['id' => $userId]);
    }

    public function getByEmail(string $email): ?array
    {
        $query = "SELECT * FROM users WHERE email = :email";
        return $this->db->qfetch($query, ['email' => $email]);
    }

    public function getByUsername(string $username): ?array
    {
        $query = "SELECT * FROM users WHERE username = :username";
        return $this->db->qfetch($query, ['username' => $username]);
    }

    public function create(array $userData): int
    {
        $userData['created'] = date('Y-m-d H:i:s');
        $this->db->insert('users', $userData);
        return $this->db->lastInsertId();
    }

    public function update(int $userId, array $userData): bool
    {
        $userData['updated'] = date('Y-m-d H:i:s');
        return $this->db->update('users', $userData, 'id = :id', ['id' => $userId]);
    }

    public function delete(int $userId): bool
    {
        return $this->db->delete('users', 'id = :id', ['id' => $userId]);
    }

    public function list(int $limit = 10, int $offset = 0): array
    {
        $query = "SELECT * FROM users LIMIT :limit OFFSET :offset";
        return $this->db->qfetchAll($query, [
            'limit' => $limit,
            'offset' => $offset
        ]);
    }

    public function count(): int
    {
        $query = "SELECT COUNT(*) as total FROM users";
        $result = $this->db->qfetch($query);
        return (int) $result['total'];
    }

}

