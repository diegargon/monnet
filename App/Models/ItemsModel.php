<?php
/**
 * Model for managing items table operations.
 *
 * Table: items
 * --------------------------------------------------------------------------
 * | Field      | Type           | Null | Default | Extra          |
 * |------------|----------------|------|---------|----------------|
 * | id         | int (PK)       | No   |         | AUTO_INCREMENT |
 * | uid        | int            | No   | 0       |                |
 * | cat_id     | int            | No   | 50      |                |
 * | type       | char(255)      | Yes  | NULL    |                |
 * | title      | char(255)      | No   |         |                |
 * | conf       | varchar(4096)  | No   |         |                |
 * | weight     | tinyint        | No   | 60      |                |
 * | highlight  | tinyint        | No   | 0       |                |
 * | online     | int            | No   | 0       |                |
 * --------------------------------------------------------------------------
 *
 * @author diego/@/envigo.net
 * @copyright Copyright CC BY-NC-ND 4.0 @ 2020 - 2025 Diego Garcia (diego/@/envigo.net)
 */
namespace App\Models;


use App\Core\DBManager;

class ItemsModel {
    private DBManager $db;

    /**
     * ItemsModel constructor.
     *
     * @param DBManager $db Database manager instance.
     */
    public function __construct(DBManager $db)
    {
        $this->db = $db;
    }

    /**
     * Get all items for a user, ordered by weight.
     *
     * @param int $uid User ID.
     * @return array List of items.
     */
    public function getAllByUser(int $uid): array
    {
        return $this->db->select('items', ['*'], 'uid = :uid', ['uid' => $uid], null, ' ORDER BY weight');
    }

    /**
     * Insert a new item.
     *
     * @param array $data Item data.
     * @return bool True on success, false on failure.
     */
    public function insert(array $data): bool
    {
        return $this->db->insert('items', $data);
    }

    /**
     * Update an item by its ID.
     *
     * @param int $id Item ID.
     * @param array $data Item data to update.
     * @return bool True on success, false on failure.
     */
    public function update(int $id, array $data): bool
    {
        return $this->db->update('items', $data, 'id = :id', ['id' => $id]);
    }

    /**
     * Delete an item by its ID.
     *
     * @param int $id Item ID.
     * @return bool True on success, false on failure.
     */
    public function delete(int $id): bool
    {
        return $this->db->delete('items', 'id = :id', ['id' => $id]);
    }

    /**
     * Get an item by its ID.
     *
     * @param int $id Item ID.
     * @return array|null Item data or null if not found.
     */
    public function getById(int $id): ?array
    {
        return $this->db->selectOne('items', ['*'], 'id = :id', ['id' => $id]);
    }

    /**
     * Get items by type for a user (including global items), ordered by weight.
     *
     * @param int $uid User ID.
     * @param string $type Item type.
     * @return array List of items.
     */
    public function getByType(int $uid, string $type): array
    {
        return $this->db->select(
            'items',
            ['*'],
            '(uid = :uid OR uid = 0) AND type = :type',
            ['uid' => $uid, 'type' => $type],
            null,
            ' ORDER BY weight'
        );
    }

    /**
     * Get items by category ID for a user.
     *
     * @param int $uid User ID.
     * @param int $cat_id Category ID.
     * @return array List of items.
     */
    public function getByCatID(int $uid, int $cat_id): array
    {
        return $this->db->select('items', ['*'], 'uid = :uid AND cat_id = :cat_id', ['uid' => $uid, 'cat_id' => $cat_id]);
    }
}
