<?php

/**
 *
 *  @author diego/@/envigo.net
 *  @package
 *  @subpackage
 *  @copyright Copyright CC BY-NC-ND 4.0 @ 2020 - 2024 Diego Garcia (diego/@/envigo.net)
 */
!defined('IN_WEB') ? exit : true;

class Items {

    private Database $db;
    private array $categories;
    private array $cfg;
    private array $items = [];
    private int $uid;

    public function __construct(AppCtx $ctx) {
        $this->cfg = $ctx->getAppCfg();
        $this->db = $ctx->getAppDb();
        $this->uid = $ctx->getAppUser()->getId();
        $this->categories = $ctx->getAppCategories()->getByType(2); //2:items

        $results = $this->db->select('items', '*', ['uid' => $this->uid], 'ORDER BY weight');
        $this->items = $this->db->fetchAll($results);
    }

    public function addItem(string $item_type, array $item_data): bool {
        if ($item_type == 'bookmarks') {
            $conf = ['url' => $item_data['urlip'], 'image_type' => $item_data['image_type'], 'image_resource' => $item_data['field_img']];
            $set = ['uid' => $this->uid, 'cat_id' => $item_data['cat_id'], 'type' => 'bookmarks', 'title' => $item_data['name'], 'conf' => json_encode($conf), 'weight' => $item_data['weight']];
            if ($this->db->insert('items', $set)) {
                return true;
            }
        }

        return false;
    }

    public function getAll(?string $key_order = null, ?string $dir = 'asc'): array {
        if (!empty($key_order)) {
            order($this->items, $key_order, $dir);
        }

        return $this->items;
    }

    function remove(int $id): bool {
        foreach ($this->items as $item) {
            if ($item['id'] == $id && $item['uid'] == $this->uid) {
                $this->db->delete('items', ['id' => $id], 'LIMIT 1');
                unset($this->item[$id]);
                return true;
            }
        }
        return false;
    }

    public function getByType(string $type, ?string $key_order = 'weight', ?string $dir = 'asc'): array {
        $result = [];
        foreach ($this->items as $item) {
            if ($item['type'] == $type) {
                $result[] = $item;
            }
        }
        order($result, $key_order, $dir);

        return $result;
    }

    public function getByCatID($category_id): array {
        $result = [];
        foreach ($this->items as $item) {
            if ($item['cat_id'] == $category_id) {
                $result[] = $item;
            }
        }

        return $result;
    }

    public function getTypes(): array {
        $types = array_column($this->items, 'type');
        //To uniq
        $uniq_types = array_unique($types);
        //to index array
        $uniq_types = array_values($uniq_types);

        return $uniq_types;
    }
}
