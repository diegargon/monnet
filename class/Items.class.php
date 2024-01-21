<?php

/**
 *
 *  @author diego/@/envigo.net
 *  @package
 *  @subpackage
 *  @copyright Copyright @ 2020 - 2024 Diego Garcia (diego/@/envigo.net)
 */
!defined('IN_WEB') ? exit : true;

class Items {

    private $cfg;
    private $db;
    private $items = [];

    public function __construct(array $cfg, Database $db) {
        $this->cfg = $cfg;
        $this->db = $db;

        $results = $db->select('items', '*', null, 'ORDER BY weight');
        $this->items = $db->fetchAll($results);
    }

    public function getCatAll($category) {
        $result = [];
        foreach ($this->items as $item) {
            if ($item['cat_id'] == $category) {
                $result[] = $item;
            }
        }

        return $result;
    }

    function getTypes() {
        $types = array_column($this->items, 'type');
        //To uniq
        $uniq_types = array_unique($types);
        //to index array
        $uniq_types = array_values($uniq_types);

        return $uniq_types;
    }
}
