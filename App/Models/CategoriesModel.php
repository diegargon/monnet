<?php

namespace App\Models;

use App\Core\DBManager;

class CategoriesModel {
    private DBManager $db;

    public function __construct(DBManager $db)
    {
        $this->db = $db;
    }

    public function getAll(): array
    {
        // Devuelve todas las filas de la tabla categories
        return $this->db->select('categories', ['*']);
    }

    public function getTypes(array $cat_types): array
    {
        // Este método solo retorna el array recibido, no requiere acceso a DB
        return $cat_types;
    }

    public function getByType(int $type): array
    {
        // Usa select con condición y parámetros
        return $this->db->select('categories', ['*'], 'cat_type = :type', ['type' => $type]);
    }

    public function getTypeByID(int $id): int|bool
    {
        // Usa selectOne para obtener una sola fila
        $row = $this->db->selectOne('categories', ['cat_type'], 'id = :id', ['id' => $id]);
        return $row ? $row['cat_type'] : false;
    }

    public function create(int $cat_type, string $value): bool
    {
        // Verifica existencia usando selectOne
        $exists = $this->db->selectOne('categories', ['cat_name'], 'cat_type = :type AND cat_name = :name', [
            'type' => $cat_type,
            'name' => $value
        ]);
        if ($exists) {
            return false;
        }
        return $this->db->insert('categories', ['cat_name' => $value, 'cat_type' => $cat_type]);
    }

    public function remove(int $id): bool
    {
        // Usa delete con condición y parámetros
        return $this->db->delete('categories', 'id = :id', ['id' => $id]);
    }

    public function updateToDefault(int $default_category, int $old_category): bool
    {
        // Usa update con condición y parámetros
        return $this->db->update(
            'items',
            ['category' => $default_category],
            'cat_id = :old',
            ['old' => $old_category]
        );
    }
}