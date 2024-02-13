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

    private $cfg;
    private $db;
    private $items = [];
    private $categories;

    public function __construct(array $cfg, Database $db, Categories $categories) {
        $this->cfg = $cfg;
        $this->db = $db;
        $this->categories = $categories->getByType(2); //2:items

        $results = $db->select('items', '*', null, 'ORDER BY weight');
        $this->items = $db->fetchAll($results);
    }

    public function getAll(?string $key_order = null, ?string $dir = 'asc') {
        if (!empty($key_order)) {
            order($this->items, $key_order, $dir);
        }

        return $this->items;
    }

    function remove(int $id) {
        $this->db->delete('items', ['id' => $id], 'LIMIT 1');
        unset($this->item[$id]);
    }

    public function getByType(string $type, ?string $key_order = 'weight', ?string $dir = 'asc') {
        $result = [];
        foreach ($this->items as $item) {
            if ($item['type'] == $type) {
                $result[] = $item;
            }
        }
        order($result, $key_order, $dir);

        return $result;
    }

    public function getByCatID($category_id) {
        $result = [];
        foreach ($this->items as $item) {
            if ($item['cat_id'] == $category_id) {
                $result[] = $item;
            }
        }

        return $result;
    }

    public function getTypes() {
        $types = array_column($this->items, 'type');
        //To uniq
        $uniq_types = array_unique($types);
        //to index array
        $uniq_types = array_values($uniq_types);

        return $uniq_types;
    }

    private function getCatsIDByType($type) {
        $cats_id = [];

        foreach ($this->items as $item) {
            if ($item['type'] == $type) {
                $cats_id[] = $item['cat_id'];
            }
        }
        return $cats_id;
    }

    public function getCatsByType($type) {

        $cats = [];

        $cats_id = $this->getCatsIDByType($type);

        foreach ($this->categories as $cat) {
            if (in_array($cat['id'], $cats_id)) {
                $cats[] = $cat;
            }
        }

        return $cats;
    }
}
