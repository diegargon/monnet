<?php

namespace App\Models;

class ItemsModel {
    private \DBManager $db;

    public function __construct(\DBManager $db)
    {
        $this->db = $db;
    }

    // Obtiene todos los items de un usuario, ordenados por weight
    public function getAllByUser(int $uid): array
    {
        return $this->db->select('items', ['*'], 'uid = :uid', ['uid' => $uid], null, ' ORDER BY weight');
    }

    // Inserta un nuevo item
    public function insert(array $data): bool
    {
        return $this->db->insert('items', $data);
    }

    // Actualiza un item por id
    public function update(int $id, array $data): bool
    {
        return $this->db->update('items', $data, 'id = :id', ['id' => $id]);
    }

    // Elimina un item por id
    public function delete(int $id): bool
    {
        return $this->db->delete('items', 'id = :id', ['id' => $id]);
    }

    // Obtiene un item por id
    public function getById(int $id): ?array
    {
        return $this->db->selectOne('items', ['*'], 'id = :id', ['id' => $id]);
    }

    // Obtiene items de un usuario por tipo, ordenados por weight
    public function getByType(int $uid, string $type): array
    {
        return $this->db->select('items', ['*'], 'uid = :uid AND type = :type', ['uid' => $uid, 'type' => $type], null, ' ORDER BY weight');
    }

    // Obtiene items de un usuario por categoría
    public function getByCatID(int $uid, int $cat_id): array
    {
        return $this->db->select('items', ['*'], 'uid = :uid AND cat_id = :cat_id', ['uid' => $uid, 'cat_id' => $cat_id]);
    }
}
