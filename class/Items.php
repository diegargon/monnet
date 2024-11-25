<?php

/**
 *
 * @author diego/@/envigo.net
 * @package
 * @subpackage
 * @copyright Copyright CC BY-NC-ND 4.0 @ 2020 - 2024 Diego Garcia (diego/@/envigo.net)
 */
!defined('IN_WEB') ? exit : true;

class Items
{
    /** @var Database */
    private Database $db;

    /** @var array<int, array<string, mixed>> $items */
    private array $items = [];
    /** @var int */
    private int $uid;

    public function __construct(AppContext $ctx)
    {
        $this->db = $ctx->get('Mysql');
        $this->uid = $ctx->get('User')->getId();
        //$this->categories = $ctx->get('Categories')->getByType(2); //2:items

        $results = $this->db->select('items', '*', ['uid' => $this->uid], 'ORDER BY weight');
        $this->items = $this->db->fetchAll($results);
    }

    /**
     *
     * @param string $item_type
     * @param array<string, mixed> $item_data
     * @return bool
     */
    public function addItem(string $item_type, array $item_data): bool
    {
        if ($item_type == 'bookmarks') {
            $conf = [
                'url' => $item_data['urlip'],
                'image_type' => $item_data['image_type'],
                'image_resource' => $item_data['field_img']
            ];

            $set = [
                'uid' => $this->uid,
                'cat_id' => $item_data['cat_id'],
                'type' => 'bookmarks',
                'title' => $item_data['name'],
                'conf' => json_encode($conf),
                'weight' => $item_data['weight']
            ];

            if ($this->db->insert('items', $set)) {
                $id = $this->db->insertID();
                $this->items[$id] = $set;

                return true;
            }
        }

        return false;
    }

    /**
     *
     * @param string $item_type
     * @param array<string, mixed> $item_data
     * @return bool
     */
    public function updateItem(string $item_type, array $item_data): bool
    {
        if ($item_type == 'bookmarks') {
            $id = $item_data['id'];
            $conf = [
                'url' => $item_data['urlip'],
                'image_type' => $item_data['image_type'],
                'image_resource' => $item_data['field_img']
            ];
            $set = [
                'uid' => $this->uid,
                'cat_id' => $item_data['cat_id'],
                'type' => 'bookmarks',
                'title' => $item_data['name'],
                'conf' => json_encode($conf),
                'weight' => $item_data['weight']
            ];

            if ($this->db->update('items', $set, ['id' => $id])) {
                $set['id'] = $id;
                $this->items[$id] = $set;
                return true;
            }
        }

        return false;
    }

    /**
     *
     * @param int $id
     * @return array<string, mixed>
     */
    public function getById(int $id): array
    {
        foreach ($this->items as $item) {
            if ($item['id'] == $id) {
                $confArray = json_decode($item['conf'], true);
                if (is_array($confArray)) {
                    $item = array_merge($item, $confArray);
                    unset($item['conf']);
                }
                return $item;
            }
        }

        return [];
    }

    /**
     *
     * @param string|null $key_order
     * @param string|null $dir
     * @return array<int, array<string, mixed>>
     */
    public function getAll(?string $key_order = null, ?string $dir = 'asc'): array
    {
        if (!empty($key_order)) {
            order($this->items, $key_order, $dir);
        }

        return $this->items;
    }

    /**
     *
     * @param int $id
     * @return bool
     */
    public function remove(int $id): bool
    {
        foreach ($this->items as $item) {
            if ($item['id'] == $id && $item['uid'] == $this->uid) {
                $this->db->delete('items', ['id' => $id]);
                unset($this->items[$id]);
                return true;
            }
        }
        return false;
    }

    /**
     *
     * @param string $type
     * @param string|null $key_order
     * @param string|null $dir
     * @return array<string, mixed>
     */
    public function getByType(string $type, ?string $key_order = 'weight', ?string $dir = 'asc'): array
    {
        $result = [];
        foreach ($this->items as $item) {
            if ($item['type'] == $type) {
                $result[] = $item;
            }
        }
        order($result, $key_order, $dir);

        return $result;
    }

    /**
     *
     * @param int $category_id
     * @return array<int, array<string, mixed>>
     */
    public function getByCatID(int $category_id): array
    {
        $result = [];
        foreach ($this->items as $item) {
            if ($item['cat_id'] == $category_id) {
                $result[] = $item;
            }
        }

        return $result;
    }

    /**
     *
     * @return array<string>
     */
    public function getTypes(): array
    {
        $types = array_column($this->items, 'type');
        //To uniq
        $uniq_types = array_unique($types);
        //to index array
        $uniq_types = array_values($uniq_types);

        return $uniq_types;
    }
}
