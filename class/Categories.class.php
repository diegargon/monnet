<?php

/**
 *
 *  @author diego/@/envigo.net
 *  @package
 *  @subpackage
 *  @copyright Copyright CC BY-NC-ND 4.0 @ 2020 - 2024 Diego Garcia (diego/@/envigo.net)
 */
!defined('IN_WEB') ? exit : true;

class Categories {

    private $cfg;
    private $db;
    private $categories = [];
    private $cat_types;

    public function __construct(array $cfg, Database $db) {
        $this->cfg = $cfg;
        $this->db = $db;

        $results = $db->select('categories', '*');
        $this->categories = $db->fetchAll($results);
        $this->cat_types = $cfg['cat_types'];
    }

    public function getAll() {

        return $this->categories;
    }

    public function getTypes() {

        return $this->cat_types;
    }

    public function getByType($type) {
        $categories = [];
        foreach ($this->categories as $cat) {
            if ($cat['cat_type'] == $type) {
                $categories[] = $cat;
            }
        }

        return empty($categories) ? false : $categories;
    }

    public function getTypeByID($id) {
        foreach ($this->categories as $cat) {
            if ($cat['id'] == $id) {
                return $cat['cat_type'];
            }
        }

        return false;
    }
}
